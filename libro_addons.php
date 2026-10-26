<?php
if (!defined('ABSPATH')) { exit; }
function rt_libro_lrq_render_addons() {
    if (!current_user_can('manage_options')) { return; }
    $products = array(
        array('title' => __('Libro de Reclamaciones PRO', 'rt-libro'), 'desc' => __('Funciones avanzadas para la gestión de reclamaciones.', 'rt-libro'), 'img' => 'libro-de-reclamaciones-y-quejas-pro.jpg', 'url' => 'https://renzotejada.com/plugins/libro-de-reclamaciones-y-quejas-pro/', 'slug' => 'libro_pro'),
        array('title' => __('Costo de Envío de Ubigeo Perú', 'rt-libro'), 'desc' => __('Configura costos de envío según la ubicación en WooCommerce.', 'rt-libro'), 'img' => 'costo-de-envio-de-ubigeo-peru.jpg', 'url' => 'https://renzotejada.com/plugins/costo-de-envio-de-ubigeo-de-peru-para-woocommerce/', 'slug' => 'costo_envio'),
        array('title' => __('WooYape para WooCommerce', 'rt-libro'), 'desc' => __('Integración de pagos con Yape.', 'rt-libro'), 'img' => 'plugin-wooyape.jpg', 'url' => 'https://renzotejada.com/plugins/wooyape-para-woocommerce/', 'slug' => 'wooyape'),
        array('title' => __('WooPlin para WooCommerce', 'rt-libro'), 'desc' => __('Integración de pagos con Plin.', 'rt-libro'), 'img' => 'plugin-wooplin.jpg', 'url' => 'https://renzotejada.com/plugins/wooplin-para-woocommerce/', 'slug' => 'wooplin'),
        array('title' => __('Comprobante de Pago Perú', 'rt-libro'), 'desc' => __('Opciones de comprobantes en WooCommerce.', 'rt-libro'), 'img' => 'comprobante-de-pago-woo.jpg', 'url' => 'https://renzotejada.com/plugins/comprobante-de-pago-peru-para-woocommerce/', 'slug' => 'comprobante'),
        array('title' => __('Tipo Documento Perú', 'rt-libro'), 'desc' => __('Selecciona documentos de identidad en el checkout.', 'rt-libro'), 'img' => 'tipo-de-documento-woo.jpg', 'url' => 'https://renzotejada.com/plugins/tipo-de-documento-peru-para-woocooomerce/', 'slug' => 'tipo_documento'),
    );
    echo '<div class="rtlr-addons"><h2>' . esc_html__('Más plugins de Renzo Tejada', 'rt-libro') . '</h2><p>' . esc_html__('Complementos y herramientas para WordPress.', 'rt-libro') . '</p><div class="rtlr-addons-grid">';
    foreach ($products as $product) {
        if ($product['slug'] === 'libro_pro' && rt_libro_lrq_pro_active()) { continue; }
        $url = rt_libro_lrq_marketing_url($product['url'], 'addons_' . $product['slug'], $product['slug'] === 'libro_pro' ? 'libro_reclamaciones_free' : 'libro_free_addons');
        echo '<article class="rtlr-addon"><img loading="lazy" src="' . esc_url(plugins_url('image/addons/' . $product['img'], __FILE__)) . '" alt="' . esc_attr($product['title']) . '"><div class="rtlr-addon-content"><h3>' . esc_html($product['title']) . '</h3><p>' . esc_html($product['desc']) . '</p><a class="button button-secondary" target="_blank" rel="noopener noreferrer" href="' . esc_url($url) . '">' . esc_html__('Ver plugin', 'rt-libro') . '</a></div></article>';
    }
    echo '</div><p class="rtlr-brand">' . esc_html__('Desarrollado por', 'rt-libro') . ' <a href="https://renzotejada.com/" target="_blank" rel="noopener noreferrer">Renzo Tejada</a></p></div>';
}
