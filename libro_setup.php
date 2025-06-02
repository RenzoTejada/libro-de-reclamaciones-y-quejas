<?php
function rt_libro_lrq_setup()
{
    $rt_libro_db_version = get_option('rt_libro_db_version');
    rt_libro_lrq_create_tables_default();
    update_option('rt_libro_db_version', VERSION_RT_LIBRO_RECLAMACIONES);
}
function rt_libro_lrq_create_tables_default()
{
    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    global $wpdb;
    $table_libro = $wpdb->prefix . "rt_libro";
    $query_rt_libro = "
        CREATE TABLE IF NOT EXISTS $table_libro (
            `libro_id` INT NOT NULL AUTO_INCREMENT,
            `nombre` VARCHAR(100) DEFAULT NULL,
            `apellido_paterno` VARCHAR(100) DEFAULT NULL,
            `apellido_materno` VARCHAR(100) DEFAULT NULL,
            `tipo_doc` TINYINT DEFAULT NULL,
            `nro_documento` VARCHAR(20) DEFAULT NULL,
            `fono` VARCHAR(20) DEFAULT NULL,
            `email` VARCHAR(100) DEFAULT NULL,
            `direccion` VARCHAR(100) DEFAULT NULL,
            `referencia` VARCHAR(100) DEFAULT NULL,
            `departamento` VARCHAR(100) DEFAULT NULL,
            `provincia` VARCHAR(100) DEFAULT NULL,
            `distrito` VARCHAR(100) DEFAULT NULL,
            `flag_menor` TINYINT DEFAULT NULL,
            `nombre_tutor` VARCHAR(100) DEFAULT NULL,
            `email_tutor` VARCHAR(100) DEFAULT NULL,
            `tipo_doc_tutor` TINYINT DEFAULT NULL,
            `numero_documento_tutor` VARCHAR(20) DEFAULT NULL,
            `tipo_reclamacion` TINYINT DEFAULT NULL,
            `tipo_consumo` TINYINT DEFAULT NULL,
            `nro_pedido` INT NOT NULL,
            `fch_reclamo` TIMESTAMP NULL DEFAULT NULL,
            `descripcion` TEXT DEFAULT NULL,
            `proveedor` VARCHAR(100) DEFAULT NULL,
            `fch_compra` TIMESTAMP NULL DEFAULT NULL,
            `fch_consumo` TIMESTAMP NULL DEFAULT NULL,
            `fch_vencimiento` TIMESTAMP NULL DEFAULT NULL,
            `detalle` TEXT DEFAULT NULL,
            `pedido_cliente` TEXT DEFAULT NULL,
            `monto_reclamado` DECIMAL(10, 2) DEFAULT NULL,
            `acepta_contenido` TINYINT DEFAULT NULL,
            `acepta_politica` TINYINT DEFAULT NULL,
            `respuesta` TEXT DEFAULT NULL,
            `estado` TINYINT DEFAULT NULL,
            `url_adjunto` VARCHAR(250) DEFAULT NULL,
            PRIMARY KEY (`libro_id`)
        ) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;";

    // Manejo de errores
    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($query_rt_libro);
    if ($wpdb->last_error) {
        error_log('Error al crear la tabla rt_libro: ' . $wpdb->last_error);
    }
}
function rt_libro_lrq_errornoubigeoperu()
{
    ?>
    <div class="error notice">
        <p><?php _e("Libro de Reclamaciones y Quejas: The module needs to have Peru's Ubigeo installed to operate correctly.", 'rt-libro'); ?></p>
    </div>
    <?php
}
