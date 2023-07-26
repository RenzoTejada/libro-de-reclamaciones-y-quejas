<?php

/**
 *
 * @link              https://renzotejada.com/
 * @package           Libro de Reclamaciones y Quejas
 *
 * @wordpress-plugin
 * Plugin Name:     Libro de Reclamaciones y Quejas
 * Plugin URI:      https://renzotejada.com/libro-de-reclamaciones-y-quejas/
 * Description:     Online complaints book is a document through which a consumer can record a complaint regarding a product or service that he has purchased.
 * Version:         0.1.8
 * Author:          Renzo Tejada
 * Author URI:      https://renzotejada.com/
 * License:         GNU General Public License v3.0
 * License URI:     http://www.gnu.org/licenses/gpl-3.0.html
 * Text Domain:     rt-libro
 * Domain Path:     /language
 * Requires at least: 5.6
 * Requires PHP:      5.6.20
 * WC tested up to:   7.2.2
 * WC requires at least: 2.6
 */
if (!defined('ABSPATH')) {
    exit;
} // Exit if accessed directly

$plugin_libro_version = get_file_data(__FILE__, array('Version' => 'Version'), false);

define('Version_RT_Libro_Reclamaciones', $plugin_libro_version['Version']);

function rt_libro_lrq_load_textdomain()
{
    load_plugin_textdomain('rt-libro', false, basename(dirname(__FILE__)) . '/language/');
}

add_action('init', 'rt_libro_lrq_load_textdomain');


add_filter('plugin_action_links_' . plugin_basename(__FILE__), 'rt_libro_lrq_add_plugin_page_settings_link');

add_action('wp_ajax_rt_libro_load_provincias_front', 'rt_libro_load_provincias_front');
add_action('wp_ajax_nopriv_rt_libro_load_provincias_front', 'rt_libro_load_provincias_front');
add_action('wp_ajax_rt_libro_load_distrito_front', 'rt_libro_load_distrito_front');
add_action('wp_ajax_nopriv_rt_libro_load_distrito_front', 'rt_libro_load_distrito_front');

function rt_libro_lrq_add_plugin_page_settings_link($links)
{
    $links2[] = '<a href="' .
            admin_url('admin.php?page=libro_settings') .
             '">' .  __('Settings', 'rt-libro') . '</a>';

    $links = array_merge($links2, $links);

    return $links;
}

add_action('wp_head', 'rt_libro_lrq_reclamaciones_ajaxurl');
function rt_libro_lrq_reclamaciones_ajaxurl()
{
    echo '<script type="text/javascript">
           var ajaxurl = "' . admin_url('admin-ajax.php') . '";
         </script>';
}

/*
 * SETUP
 */
require dirname(__FILE__) . "/libro_setup.php";

register_activation_hook(__FILE__, 'rt_libro_lrq_setup');

/*
 * ADMIN
 */
require dirname(__FILE__) . "/libro_admin.php";

/*
 * SHORTCODE
 */
require dirname(__FILE__) . "/libro_shortcode.php";

if (rt_libro_lrq_ubigeo_peru_plugin_libro_enabled()) {
} else {
    add_action('admin_notices', 'rt_libro_lrq_errornoubigeoperu');
}
