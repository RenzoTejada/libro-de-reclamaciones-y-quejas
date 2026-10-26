<?php
/**
 * Plugin Name:       Libro de Reclamaciones y Quejas
 * Plugin URI:        https://renzotejada.com/plugins/libro-de-reclamaciones-y-quejas-pro/
 * Description:       El libro de reclamaciones en línea permite a los consumidores registrar quejas sobre productos o servicios adquiridos.
 * Version:           1.3.21
 * Author:            Renzo Tejada
 * Author URI:        https://renzotejada.com/
 * License:           GNU General Public License v3.0
 * License URI:       http://www.gnu.org/licenses/gpl-3.0.html
 * Text Domain:       rt-libro
 * Domain Path:       /languages
 *
 * Requires at least: 6.8
 * Requires PHP:      7.4
 * WC requires at least: 2.6
 * WC tested up to:   9.9.0
 */
if (!defined('ABSPATH')) {
    exit; // Salir si se accede directamente
}

/**
 * Activation safety: do not load the Free implementation when a conflicting
 * PRO edition is active. Both editions currently declare identical PHP
 * functions, so activation must stop BEFORE any Free includes are loaded.
 */
function rt_libro_free_find_active_pro_plugin() {
    if (!function_exists('get_plugins')) {
        require_once ABSPATH . 'wp-admin/includes/plugin.php';
    }
    $installed = get_plugins();
    $active = (array) get_option('active_plugins', array());
    if (is_multisite()) {
        $active = array_merge($active, array_keys((array) get_site_option('active_sitewide_plugins', array())));
    }
    foreach (array_unique($active) as $plugin_file) {
        $data = isset($installed[$plugin_file]) ? $installed[$plugin_file] : null;
        if (!$data) {
            continue;
        }
        $name = isset($data['Name']) ? (string) $data['Name'] : '';
        // Match actual PRO edition, including the uploaded customized PRO.
        if (stripos($name, 'Libro de Reclamaciones') !== false && preg_match('/\bPRO\b/i', $name)) {
            return $plugin_file;
        }
    }
    return false;
}

$rt_libro_free_conflicting_pro = rt_libro_free_find_active_pro_plugin();
if ($rt_libro_free_conflicting_pro) {
    // Run during the attempted activation, after WordPress includes this file.
    register_activation_hook(__FILE__, function () use ($rt_libro_free_conflicting_pro) {
        if (!current_user_can('activate_plugins')) {
            wp_die(esc_html__('No tienes permisos para activar plugins.', 'rt-libro'));
        }
        $network_active = is_multisite() && is_plugin_active_for_network($rt_libro_free_conflicting_pro);
        $base = $network_active ? network_admin_url('plugins.php') : admin_url('plugins.php');
        $deactivate_url = wp_nonce_url(
            add_query_arg(array('action' => 'deactivate', 'plugin' => $rt_libro_free_conflicting_pro), $base),
            'deactivate-plugin_' . $rt_libro_free_conflicting_pro
        );
        $html = '<h2>Libro de Reclamaciones PRO está activo</h2>';
        $html .= '<p>Por seguridad, Free y PRO no pueden activarse al mismo tiempo. Para usar Free, desactiva primero la edición PRO. Los registros y configuraciones no se eliminarán mediante este bloqueo.</p>';
        if (!$network_active && current_user_can('deactivate_plugin', $rt_libro_free_conflicting_pro)) {
            $html .= '<p><a class="button button-primary" href="' . esc_url($deactivate_url) . '">Desactivar PRO</a></p>';
        } else {
            $html .= '<p>Desactiva la edición PRO desde la administración de red y vuelve a activar Free.</p>';
        }
        $html .= '<p><a href="' . esc_url($base) . '">Volver a Plugins instalados</a>. Después, pulsa Activar en la edición Free.</p>';
        wp_die(wp_kses_post($html), 'Activación bloqueada para evitar conflictos', array('response' => 409, 'back_link' => false));
    });
    // If both were activated previously, suppress Free's shared functions.
    // This cannot protect against an already loaded PRO file; guard PRO too.
    return;
}
// Definir versión del plugin
$plugin_libro_version = get_file_data(__FILE__, array('Version' => 'Version'), false);
define('VERSION_RT_LIBRO_RECLAMACIONES', $plugin_libro_version['Version']);
// Declarar compatibilidad con tablas de pedidos personalizadas
add_action('before_woocommerce_init', function() {
    if (class_exists(\Automattic\WooCommerce\Utilities\FeaturesUtil::class)) {
        \Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility('custom_order_tables', __FILE__, true);
    }
});
// Cargar traducciones del plugin
function rt_libro_load_textdomain() {
    load_plugin_textdomain('rt-libro', false, basename(dirname(__FILE__)) . '/language/');
}
add_action('init', 'rt_libro_load_textdomain');
// Agregar enlace a la página de configuración del plugin
add_filter('plugin_action_links_' . plugin_basename(__FILE__), 'rt_libro_add_plugin_page_settings_link');
add_action('wp_ajax_rt_libro_load_provincias_front', 'rt_libro_load_provincias_front');
add_action('wp_ajax_nopriv_rt_libro_load_provincias_front', 'rt_libro_load_provincias_front');
add_action('wp_ajax_rt_libro_load_distrito_front', 'rt_libro_load_distrito_front');
add_action('wp_ajax_nopriv_rt_libro_load_distrito_front', 'rt_libro_load_distrito_front');
/** Detect active PRO edition by its WordPress plugin header, without hardcoded paths. */
function rt_libro_lrq_pro_active() {
    if (!function_exists('get_plugins')) {
        require_once ABSPATH . 'wp-admin/includes/plugin.php';
    }
    $active = (array) get_option('active_plugins', array());
    if (is_multisite()) {
        $network = array_keys((array) get_site_option('active_sitewide_plugins', array()));
        $active = array_merge($active, $network);
    }
    $plugins = get_plugins();
    foreach ($active as $plugin_file) {
        if ($plugin_file === plugin_basename(__FILE__)) { continue; }
        $name = isset($plugins[$plugin_file]['Name']) ? $plugins[$plugin_file]['Name'] : '';
        if (stripos($name, 'Libro de Reclamaciones') !== false && preg_match('/\bPRO\b/i', $name)) {
            return true;
        }
    }
    return false;
}
function rt_libro_lrq_marketing_url($base, $position, $campaign = 'libro_reclamaciones_free') {
    return add_query_arg(array(
        'utm_source' => 'wp_plugin_libro_free',
        'utm_medium' => 'plugin',
        'utm_campaign' => $campaign,
        'utm_content' => $position,
    ), $base);
}
function rt_libro_add_plugin_page_settings_link($links) {
    $settings_link = '<a href="' . esc_url(admin_url('admin.php?page=libro_settings')) . '">' . esc_html__('Settings', 'rt-libro') . '</a>';
    $extra = array($settings_link);
    if (!rt_libro_lrq_pro_active()) {
        $url = rt_libro_lrq_marketing_url('https://renzotejada.com/plugins/libro-de-reclamaciones-y-quejas-pro/', 'plugins_list_pro');
        $extra[] = '<a href="' . esc_url($url) . '" target="_blank" rel="noopener noreferrer" style="color:#b06d00;font-weight:600;">' . esc_html__('Versión PRO', 'rt-libro') . '</a>';
    }
    return array_merge($extra, $links);
}
// Configurar AJAX URL
add_action('wp_head', 'rt_libro_reclamaciones_ajaxurl');
function rt_libro_reclamaciones_ajaxurl() {
    echo '<script type="text/javascript">var ajaxurl = "' . admin_url('admin-ajax.php') . '";</script>';
}
// Cargar archivos de configuración y administración
require dirname(__FILE__) . "/libro_setup.php";
register_activation_hook(__FILE__, 'rt_libro_lrq_setup');
require dirname(__FILE__) . "/libro_admin.php";
require dirname(__FILE__) . "/libro_shortcode.php";
// Verificar si el plugin de ubigeo está habilitado
if (get_option('rt_libro_location_mode', 'ubigeo') === 'ubigeo' && !rt_libro_lrq_ubigeo_peru_plugin_libro_enabled()) {
    add_action('admin_notices', 'rt_libro_lrq_errornoubigeoperu');
}
// Update the schema for existing installations without reactivation.
add_action('init', 'rt_libro_lrq_maybe_upgrade_database', 5);

require_once __DIR__ . "/libro_dashboard.php";
require_once __DIR__ . "/libro_addons.php";

add_action('admin_enqueue_scripts', function($hook) { if (isset($_GET['page']) && strpos(sanitize_key(wp_unslash($_GET['page'])), 'libro') !== false) { wp_enqueue_media(); } });
