<?php

function rt_libro_lrq_setup()
{
    $rt_libro_db_version = get_option('rt_libro_db_version');
    rt_libro_lrq_create_tables_default();
    update_option('rt_libro_db_version', Version_RT_Libro_Reclamaciones);
}

function rt_libro_lrq_create_tables_default()
{
    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    global $wpdb;
    
    $table_libro = $wpdb->prefix . "rt_libro";
    $query_rt_libro = "
        CREATE TABLE IF NOT EXISTS $table_libro (
            `libro_id` int(11) NOT NULL AUTO_INCREMENT,
            `nombre` varchar(100) DEFAULT NULL,
            `apellido_paterno` varchar(100) DEFAULT NULL,
            `apellido_materno` varchar(100) DEFAULT NULL,
            `tipo_doc` int(5) DEFAULT NULL,
            `nro_documento` int(11) DEFAULT NULL,
            `fono` int(11) DEFAULT NULL,
            `email` varchar(100) DEFAULT NULL,
            `direccion` varchar(100) DEFAULT NULL,
            `referencia` varchar(100) DEFAULT NULL,
            `departamento` varchar(100) DEFAULT NULL,
            `provincia` varchar(100) DEFAULT NULL,
            `distrito` varchar(100) DEFAULT NULL,
            `flag_menor` int(5) DEFAULT NULL,
            `nombre_tutor` varchar(100) DEFAULT NULL,
            `email_tutor` varchar(100) DEFAULT NULL,
            `tipo_doc_tutor` int(5) DEFAULT NULL,
            `numero_documento_tutor` int(11) DEFAULT NULL,
            `tipo_reclamacion` int(5) DEFAULT NULL,
            `tipo_consumo` int(5) DEFAULT NULL,
            `nro_pedido` int(11) NOT NULL,
            `fch_reclamo` timestamp NULL DEFAULT NULL,
            `descripcion` varchar(100) DEFAULT NULL,
            `proveedor` varchar(100) DEFAULT NULL,
            `fch_compra` timestamp NULL DEFAULT NULL,
            `fch_consumo` timestamp NULL DEFAULT NULL,
            `fch_vencimiento` timestamp NULL DEFAULT NULL,
            `detalle` text,
            `pedido_cliente` text,
            `monto_reclamado` int(11) DEFAULT NULL,
            `acepta_contenido` int(5) DEFAULT NULL,
            `acepta_politica` int(5) DEFAULT NULL,
            `respuesta` text,
            `estado` int(5) DEFAULT NULL,
            `url_adjunto` varchar(250) DEFAULT NULL,
            PRIMARY KEY (`libro_id`)
        ) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;";
    dbDelta($query_rt_libro);
    
}

function rt_libro_lrq_errornoubigeoperu()
{
    ?>
    <div class="error notice">
        <p><?php _e("Libro de Reclamaciones y Quejas: The module needs to have Peru's Ubigeo installed to operate correctly.", 'rt-libro'); ?></p>
    </div>
    <?php
}
