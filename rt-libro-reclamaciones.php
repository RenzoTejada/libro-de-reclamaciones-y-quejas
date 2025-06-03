<?php
/**
 * Plugin Name:       Libro de Reclamaciones y Quejas
 * Plugin URI:        https://renzotejada.com/libro-de-reclamaciones-y-quejas/
 * Description:       El libro de reclamaciones en línea permite a los consumidores registrar quejas sobre productos o servicios adquiridos.
 * Version:           1.2
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
function rt_libro_add_plugin_page_settings_link($links) {
    $settings_link = '<a href="' . admin_url('admin.php?page=libro_settings') . '">' . __('Settings', 'rt-libro') . '</a>';
    return array_merge([$settings_link], $links);
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
if (!rt_libro_lrq_ubigeo_peru_plugin_libro_enabled()) {
    add_action('admin_notices', 'rt_libro_lrq_errornoubigeoperu');
}