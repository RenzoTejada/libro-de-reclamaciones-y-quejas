<?php
if (!defined('ABSPATH')) {
    exit;
} // Exit if accessed directly
/************************* ADMIN PAGE **********************************
 ***********************************************************************/
add_action('admin_menu', 'rt_libro_lrq_register_admin_page');
function rt_libro_lrq_register_admin_page() {
    add_menu_page(
        __('Libro de Reclamaciones', 'rt-libro'),
        __('Libro de Reclamaciones', 'rt-libro'),
        'administrator',
        'page_libro',
        '',
        'dashicons-book-alt',
        50
    );
    add_submenu_page(
        'page_libro',
        __('Configuraciones', 'rt-libro'),
        __('Libro de Reclamaciones', 'rt-libro'),
        'manage_options',
        'libro_settings',
        'rt_libro_lrq_submenu_settings_callback'
    );
    remove_submenu_page('page_libro', 'page_libro');
}
function rt_libro_lrq_get_all_reclamos($estado) {
    global $wpdb;
    $table_name = $wpdb->prefix . "rt_libro";
    $request = $wpdb->prepare("SELECT * FROM $table_name WHERE estado = %d", $estado);
    return $wpdb->get_results($request, ARRAY_A);
}
function rt_libro_lrq_get_reclamo_by_id($id) {
    global $wpdb;
    $table_name = $wpdb->prefix . "rt_libro";
    $request = $wpdb->prepare("SELECT * FROM $table_name WHERE estado = 1 AND libro_id = %d", $id);
    return $wpdb->get_row($request, ARRAY_A);
}
function rt_libro_lrq_submenu_settings_home() {
    ?>
    <div style="display:none;position:fixed;background-color:white;border:1px solid black;width:50%;max-height:80%;text-align:center;top:100px;" id="detallesolicitud">
        <button style="float:right;color:white;background-color:red;" onclick="jQuery('#detallesolicitud').hide();">X</button>
        <b><?php _e('Detail', 'rt-libro') ?></b><br/>
        <textarea id="detallesolicitud_texto" style="width:100%;height:500px;"></textarea>
        <div style="clear:both;"></div>
    </div>
    <h2></h2>
    <?php
    $listado = rt_libro_lrq_get_all_reclamos(1);
    $wp_upload_dir = wp_upload_dir();
    $upload_dir = $wp_upload_dir['basedir'] . '/libro-pdfs/libro-';
    $upload_url = $wp_upload_dir['baseurl'] . '/libro-pdfs/libro-';
    if ($listado) {
        echo "<table class='wc-shipping-zones widefat'>";
        echo "<thead>";
        echo "<tr>";
        echo "<th style='text-align:center'>".__('Action', 'rt-libro')."</th>";
        echo "<th style='text-align:center'>".__('Name', 'rt-libro')."</th>";
        echo "<th style='text-align:center'>".__('First last name', 'rt-libro')."</th>";
        echo "<th style='text-align:center'>".__('Second last name', 'rt-libro')."</th>";
        echo "<th style='text-align:center'>".__('Celphone', 'rt-libro')."</th>";
        echo "<th style='text-align:center'>".__('Email', 'rt-libro')."</th>";
        echo "<th style='text-align:center'>".__('Product description', 'rt-libro')."</th>";
        echo "<th style='text-align:center'>".__('Claim detail', 'rt-libro')."</th>";
        echo "<th style='text-align:center'>".__('Clients order', 'rt-libro')."</th>";
        echo "<th style='text-align:center'>".__('Reclaimed amount', 'rt-libro')."</th>";
        echo "<th style='text-align:center'>".__('PDF', 'rt-libro')."</th>";
        echo "</tr>";
        echo "</thead>";
        echo "<tbody>";
        foreach ($listado as $row) {
            echo "<tr>";
            echo "<td style='text-align:center'><a href='?page=libro_settings&tab=home&ver=" . esc_attr($row['libro_id']) . "'>Ver</a></td>";
            echo "<td style='text-align:center'>" . esc_html($row['nombre']) . "</td>";
            echo "<td style='text-align:center'>" . esc_html($row['apellido_paterno']) . "</td>";
            echo "<td style='text-align:center'>" . esc_html($row['apellido_materno']) . "</td>";
            echo "<td style='text-align:center'>" . esc_html($row['fono']) . "</td>";
            echo "<td style='text-align:center'>" . esc_html($row['email']) . "</td>";
            echo "<td style='text-align:center'><button class='button button-primary' onclick=\"jQuery('#detallesolicitud_texto').val('" . esc_js(str_replace(array("\r", "\n"), array('', '\n'), $row['descripcion'])) . "');jQuery('#detallesolicitud').show();\">Ver</button></td>";
            echo "<td style='text-align:center'><button class='button button-primary' onclick=\"jQuery('#detallesolicitud_texto').val('" . esc_js(str_replace(array("\r", "\n"), array('', '\n'), $row['detalle'])) . "');jQuery('#detallesolicitud').show();\">Ver</button></td>";
            echo "<td style='text-align:center'><button class='button button-primary' onclick=\"jQuery('#detallesolicitud_texto').val('" . esc_js(str_replace(array("\r", "\n"), array('', '\n'), $row['pedido_cliente'])) . "');jQuery('#detallesolicitud').show();\">Ver</button></td>";
            echo "<td style='text-align:center'>" . esc_html($row['monto_reclamado']) . "</td>";
            if (file_exists($upload_dir . $row['libro_id'] .'.pdf')) {
                echo "<td style='text-align:center'><a href='" . esc_url($upload_url . $row['libro_id'] . ".pdf") . "' target='_blank'>PDF</a></td>";
            } else {
                echo "<td style='text-align:center'></td>";
            }
            echo "</tr>";
        }
        echo "</tbody>";
        echo "</table>";
    }
}
function rt_libro_lrq_submenu_settings_ver() {
    $id = sanitize_text_field($_REQUEST['ver']); ?>
    <div style="display:none;position:fixed;background-color:white;border:1px solid black;width:50%;max-height:80%;text-align:center;top:100px;" id="detallesolicitud">
        <button style="float:right;color:white;background-color:red;" onclick="jQuery('#detallesolicitud').hide();">X</button>
        <b><?php _e('Detail', 'rt-libro') ?></b><br/>
        <textarea id="detallesolicitud_texto" style="width:100%;height:500px;"></textarea>
        <div style="clear:both;"></div>
    </div>
    <ul class="subsubsub">
        <li>
            <a href="<?php echo esc_url(admin_url('admin.php?page=libro_settings&tab=home')) ?>"><?php echo 'Regresar' ?></a>
        </li>
    </ul>
    <br class="clear">
    <h2><?php _e('Claim / Complaint Nro', 'rt-libro') ?> : <?php echo esc_html($id) ?></h2>
    <?php $reclamo = rt_libro_lrq_get_reclamo_by_id($id); ?>
    <div class="wrap">
        <table class="form-table" role="presentation">
            <tbody>
            <?php
            // Generar filas de la tabla para los detalles del reclamo
            $fields = [
                'Nombre' => $reclamo['nombre'],
                'Primer apellido' => $reclamo['apellido_paterno'],
                'Segundo apellido' => $reclamo['apellido_materno'],
                'Tipo de documentación' => rt_libro_lrq_get_tipo_documentacion($reclamo['tipo_doc']),
                'Número de documentación' => $reclamo['nro_documento'],
                'Celular' => $reclamo['fono'],
                'Email' => $reclamo['email'],
                'Departamento' => $reclamo['departamento'],
                'Provincia' => $reclamo['provincia'],
                'Distrito' => $reclamo['distrito'],
                'Tipo de reclamación' => rt_libro_lrq_get_tipo_reclamacion($reclamo['tipo_reclamacion']),
                'Tipo de consumo' => rt_libro_lrq_get_tipo_consumo($reclamo['tipo_consumo']),
                'Número de pedido' => $reclamo['nro_pedido'],
                'Fecha de reclamo' => $reclamo['fch_reclamo'],
                'Descripción del producto' => $reclamo['descripcion'],
                'Proveedor' => $reclamo['proveedor'],
                'Fecha de compra' => $reclamo['fch_compra'],
                'Fecha de consumo' => $reclamo['fch_consumo'],
                'Fecha de vencimiento' => $reclamo['fch_vencimiento'],
                'Detalle de reclamo' => $reclamo['detalle'],
                'Pedido del cliente' => $reclamo['pedido_cliente'],
                'Monto reclamado' => $reclamo['monto_reclamado'],
            ];

            foreach ($fields as $label => $value) {
                echo "<tr>
                            <th scope='row'>" . __( $label, 'rt-libro' ) . "</th>
                            <td><fieldset><label>" . esc_html($value) . "</label></fieldset></td>
                          </tr>";
            }

            if ($reclamo['flag_menor']) {
                echo "<tr>
                            <th scope='row'>" . __('Nombre del tutor', 'rt-libro') . "</th>
                            <td><fieldset><label>" . esc_html($reclamo['nombre_tutor']) . "</label></fieldset></td>
                          </tr>
                          <tr>
                            <th scope='row'>" . __('Email del tutor', 'rt-libro') . "</th>
                            <td><fieldset><label>" . esc_html($reclamo['email_tutor']) . "</label></fieldset></td>
                          </tr>
                          <tr>
                            <th scope='row'>" . __('Tipo de documentación', 'rt-libro') . "</th>
                            <td><fieldset><label>" . esc_html(rt_libro_lrq_get_tipo_documentacion($reclamo['tipo_doc_tutor'])) . "</label></fieldset></td>
                          </tr>
                          <tr>
                            <th scope='row'>" . __('Número de documentación', 'rt-libro') . "</th>
                            <td><fieldset><label>" . esc_html($reclamo['numero_documento_tutor']) . "</label></fieldset></td>
                          </tr>";
            }

            echo "<tr>
                        <th scope='row'>" . __('Acepto el contenido', 'rt-libro') . "</th>
                        <td>" . ($reclamo['acepta_contenido'] ? 'SI' : 'NO') . "</td>
                      </tr>
                      <tr>
                        <th scope='row'>" . __('Acepto la política de privacidad', 'rt-libro') . "</th>
                        <td>" . ($reclamo['acepta_politica'] ? 'SI' : 'NO') . "</td>
                      </tr>";
            ?>
            </tbody>
        </table>
    </div>
    <?php
}
function rt_libro_lrq_submenu_settings_general() {
    if (isset($_POST["btn_guardar_setting"])) {
        update_option('libro_setting_page', sanitize_text_field($_POST["page_libro"]));
        update_option('libro_setting_url', sanitize_text_field($_POST["url_libro"]));
    }
    $page_libro_id = get_option('libro_setting_page');
    $page_libro_url = get_option('libro_setting_url');
    ?>
    <script>
        function copiarShortcode(id_elemento) {
            var aux = document.createElement("input");
            aux.setAttribute("value", document.getElementById(id_elemento).innerHTML);
            document.body.appendChild(aux);
            aux.select();
            document.execCommand("copy");
            document.body.removeChild(aux);
        }
    </script>
    <h2><?php _e('Configuración General', 'rt-libro') ?></h2>
    <form method="post" id="form_ajuste_libro" action="" novalidate="novalidate">
        <table class="form-table">
            <tbody>
            <tr>
                <th scope="row"><label><?php _e('Agrega el siguiente shortcode', 'rt-libro') ?></label></th>
                <td>
                    <p id="short_copy">[libro_page]</p> <br>
                    <input type="button" onclick="copiarShortcode('short_copy')" value="<?php _e('Copiar', 'rt-libro') ?>" />
                </td>
            </tr>
            <tr>
                <th scope="row"><label><?php _e('Dirección de correo electrónico de administración', 'rt-libro') ?></label></th>
                <td>
                    <input name="email_libro" type="email" id="email_libro" value="<?php echo esc_attr(get_option('admin_email')); ?>" disabled="">
                    <p class="description"><?php _e('Esta dirección se utiliza para notificaciones de reclamos y/o quejas.', 'rt-libro') ?></p>
                </td>
            </tr>
            <tr>
                <th scope="row"><label for="start_of_week"><?php _e('Selecciona la página para el libro de quejas', 'rt-libro') ?></label></th>
                <td>
                    <?php
                    $args = array(
                        'sort_order' => 'asc',
                        'sort_column' => 'post_title',
                        'hierarchical' => 1,
                        'post_type' => 'page',
                        'post_status' => 'publish'
                    );
                    $all_page = get_pages($args); ?>
                    <select name="page_libro" id="page_libro">
                        <option value=""><?php _e('Selecciona página', 'rt-libro') ?></option>
                        <?php foreach ($all_page as $page) {
                            $selected = ($page->ID == $page_libro_id) ? 'selected' : ''; ?>
                            <option value="<?php echo esc_attr($page->ID) ?>" <?php echo $selected; ?>><?php echo esc_html($page->post_title) ?></option>
                        <?php } ?>
                    </select>
                </td>
            </tr>
            <tr>
                <th scope="row"><label><?php _e('URL de la Política de Privacidad y Seguridad y Política de Cookies', 'rt-libro') ?></label></th>
                <td>
                    <input name="url_libro" type="text" id="url_libro" value="<?php echo esc_attr($page_libro_url) ?>" class="regular-text ltr">
                    <p class="description"><?php _e('Esta dirección se utiliza para notificaciones de reclamos y/o quejas.', 'rt-libro') ?></p>
                </td>
            </tr>
            </tbody>
        </table>
        <p class="submit">
            <?php
            submit_button(__('Guardar cambios', 'rt-libro'), 'button button-primary', 'btn_guardar_setting', true);
            ?>
        </p>
    </form>
    <?php
}
function rt_libro_lrq_submenu_settings_callback() {
    if (current_user_can('manage_options')) {
        ?>
        <div class="wrap woocommerce">
            <div style="background-color:#87b43e;"></div>
            <h1><?php _e('Libro de Reclamaciones y Quejas', 'rt-libro') ?></h1>
            <hr>
            <h2 class="nav-tab-wrapper">
                <a href="?page=libro_settings" class="nav-tab <?php echo (empty($_REQUEST['tab']) || $_REQUEST['tab'] == "home") ? "nav-tab-active" : ""; ?>"><?php _e('Lista', 'rt-libro') ?></a>
                <a href="?page=libro_settings&tab=setting" class="nav-tab <?php echo (isset($_REQUEST['tab']) && $_REQUEST['tab'] == "setting") ? "nav-tab-active" : ""; ?>"><?php _e('Configuración', 'rt-libro') ?></a>
            </h2>
            <?php
            if (empty($_REQUEST['tab']) || $_REQUEST['tab'] == "home") {
                if (isset($_REQUEST['ver'])) {
                    rt_libro_lrq_submenu_settings_ver();
                } else {
                    rt_libro_lrq_submenu_settings_home();
                }
            } elseif (isset($_REQUEST['tab']) && $_REQUEST['tab'] == "setting") {
                rt_libro_lrq_submenu_settings_general();
            }
            ?>
        </div>
        <?php
    }
}
function rt_libro_lrq_get_tipo_documentacion($id_tipo) {
    $tipos = [
        1 => __('DNI', 'rt-libro'),
        2 => __('CE', 'rt-libro'),
        3 => __('Pasaporte', 'rt-libro'),
        4 => __('RUC', 'rt-libro'),
    ];
    return isset($tipos[$id_tipo]) ? $tipos[$id_tipo] : '';
}
function rt_libro_lrq_get_tipo_reclamacion($id_tipo) {
    $tipos = [
        1 => __('Reclamo', 'rt-libro'),
        2 => __('Queja', 'rt-libro'),
    ];
    return isset($tipos[$id_tipo]) ? $tipos[$id_tipo] : '';
}
function rt_libro_lrq_get_tipo_consumo($id_tipo) {
    $tipos = [
        1 => __('Producto', 'rt-libro'),
        2 => __('Servicio', 'rt-libro'),
    ];
    return isset($tipos[$id_tipo]) ? $tipos[$id_tipo] : '';
}
function rt_libro_lrq_ubigeo_peru_plugin_libro_enabled() {
    return in_array('ubigeo-peru/ubigeo-peru.php', (array) get_option('active_plugins', array()));
}