<?php
if (!defined('ABSPATH')) {
    exit;
} // Exit if accessed directly

/************************* ADMIN PAGE **********************************
 ***********************************************************************/

add_action('admin_menu', 'rt_libro_lrq_register_admin_page');

function rt_libro_lrq_register_admin_page()
{
    add_menu_page('Libro de Reclamaciones', __('Libro de Reclamaciones', 'rt-libro'), 'administrator', 'page_libro', '', 'dashicons-book-alt', 50);
    add_submenu_page('page_libro', 'Configuraciones', __('Libro de Reclamaciones', 'rt-libro'), 'manage_options', 'libro_settings', 'rt_libro_lrq_submenu_settings_callback');
    remove_submenu_page('page_libro', 'page_libro');
}

function rt_libro_lrq_get_all_reclamos($estado)
{
    global $wpdb;
    $table_name = $wpdb->prefix . "rt_libro" ;
    $request = "SELECT * FROM $table_name where estado =" . $estado ;
    return $wpdb->get_results($request, ARRAY_A);
}

function rt_libro_lrq_get_reclamo_by_id($id)
{
    global $wpdb;
    $table_name = $wpdb->prefix . "rt_libro" ;
    $request = "SELECT * FROM $table_name where estado = 1 and libro_id =" . $id;
    $rpt = $wpdb->get_results($request, ARRAY_A);
    return $rpt[0];
}

function rt_libro_lrq_submenu_settings_home()
{
    ?>
    <div style="display:none;position:fixed;background-color:white;border:1px solid black;width:50%;max-height:80%;text-align:center;top:100px;" id="detallesolicitud">
        <button style="float:right;color:white;background-color:red;" onclick="jQuery('#detallesolicitud').hide();">X</button>
        <b><?php  _e('Detail', 'rt-libro') ?></b><br/>
        <textarea id="detallesolicitud_texto" style="width:100%;height:500px;"></textarea>    
        <div style="clear:both;"></div>
    </div>
    <h2></h2>
    <?php
    $listado = rt_libro_lrq_get_all_reclamos(1);

    if ($listado) {
        print "<table class='wc-shipping-zones widefat'>";
        print "<thead>";
        print "<tr>";
        print "<th style='text-align:center'>".__('Action', 'rt-libro')."</th>";
        print "<th style='text-align:center'>".__('Name', 'rt-libro')." </th>";
        print "<th style='text-align:center'>".__('First last name', 'rt-libro')."</th>";
        print "<th style='text-align:center'>".__('Second last name', 'rt-libro')."</th>";
        print "<th style='text-align:center'>".__('Celphone', 'rt-libro')."</th>";
        print "<th style='text-align:center'>".__('Email', 'rt-libro')."</th>";
        print "<th style='text-align:center'>".__('Product description', 'rt-libro')."</th>";
        print "<th style='text-align:center'>".__('Claim detail', 'rt-libro')."</th>";
        print "<th style='text-align:center'>".__('Clients order', 'rt-libro')."</th>";
        print "<th style='text-align:center'>".__('Reclaimed amount', 'rt-libro')."</th>";
        print "</tr>";
        print "</thead>";
        print "<tbody>";

        foreach ($listado as $row) {
            print "<tr>";
            print "<td style='text-align:center'><a href='?page=libro_settings&tab=home&ver=" . $row['libro_id'] . "'>Ver</a></td>";
            print "<td style='text-align:center'>" . $row['nombre'] . "</td>";
            print "<td style='text-align:center'>" . $row['apellido_paterno'] . "</td>";
            print "<td style='text-align:center'>" . $row['apellido_materno'] . "</td>";
            print "<td style='text-align:center'>" . $row['fono'] . "</td>";
            print "<td style='text-align:center'>" . $row['email'] . "</td>";
            print "<td style='text-align:center'><button class='button button-primary' onclick=\"jQuery('#detallesolicitud_texto').val('" . str_replace("\r", "", str_replace("\n", '\n', str_replace('"', '&quot;', str_replace("'", '\x27', $row['descripcion'])))) . "');jQuery('#detallesolicitud').show();\">Ver</button></td>";
            print "<td style='text-align:center'><button class='button button-primary' onclick=\"jQuery('#detallesolicitud_texto').val('" . str_replace("\r", "", str_replace("\n", '\n', str_replace('"', '&quot;', str_replace("'", '\x27', $row['detalle'])))) . "');jQuery('#detallesolicitud').show();\">Ver</button></td>";
            print "<td style='text-align:center'><button class='button button-primary' onclick=\"jQuery('#detallesolicitud_texto').val('" . str_replace("\r", "", str_replace("\n", '\n', str_replace('"', '&quot;', str_replace("'", '\x27', $row['pedido_cliente'])))) . "');jQuery('#detallesolicitud').show();\">Ver</button></td>";
            print "<td style='text-align:center'>" . $row['monto_reclamado'] . "</td>";
            print "</tr>";
        }
        print "</tbody>";
        print "</table>";
    }
}

function rt_libro_lrq_submenu_settings_ver()
{
    $id = sanitize_text_field($_REQUEST['ver']); ?>
     <div style="display:none;position:fixed;background-color:white;border:1px solid black;width:50%;max-height:80%;text-align:center;top:100px;" id="detallesolicitud">
        <button style="float:right;color:white;background-color:red;" onclick="jQuery('#detallesolicitud').hide();">X</button>
        <b><?php  _e('Detail', 'rt-libro') ?></b><br/>
        <textarea id="detallesolicitud_texto" style="width:100%;height:500px;"></textarea>    
        <div style="clear:both;"></div>
    </div>
    <ul class="subsubsub">
        <li>
            <a href="<?php echo admin_url('admin.php?page=libro_settings&tab=home') ?>"><?php echo 'Regresar' ?></a> 
        </li>
    </ul>
    <br class="clear">
    <h2><?php  _e('Claim / Complaint Nro', 'rt-libro') ?> : <?php echo $id ?></h2>
    <?php $reclamo = rt_libro_lrq_get_reclamo_by_id($id); ?>
    <div class="wrap">
            <table class="form-table" role="presentation">
                <tbody>
                    <tr>
                        <th scope="row"><?php _e('Name', 'rt-libro') ?></th>
                        <td>
                            <fieldset>
                                <label><?php echo $reclamo['nombre'] ?></label>
                            </fieldset>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php _e('First last name', 'rt-libro') ?></th>
                        <td>
                            <fieldset>
                                <label><?php echo $reclamo['apellido_paterno'] ?></label>
                            </fieldset>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php _e('Second last name', 'rt-libro') ?></th>
                        <td>
                            <fieldset>
                                <label><?php echo $reclamo['apellido_materno'] ?></label>
                            </fieldset>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php _e('Type of documentation', 'rt-libro') ?></th>
                        <td>
                            <fieldset>
                                <label><?php echo rt_libro_lrq_get_tipo_documentacion($reclamo['tipo_doc']); ?></label>
                            </fieldset>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php _e('Documentation number', 'rt-libro') ?></th>
                        <td>
                            <fieldset>
                                <label><?php echo $reclamo['nro_documento']?></label>
                            </fieldset>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php _e('Celphone', 'rt-libro') ?></th>
                        <td>
                            <fieldset>
                                <label><?php echo $reclamo['fono'] ?></label>
                            </fieldset>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php _e('Email', 'rt-libro') ?></th>
                        <td>
                            <fieldset>
                                <label><?php echo $reclamo['email'] ?></label>
                            </fieldset>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php _e('Department', 'rt-libro') ?></th>
                        <td>
                            <fieldset>
                                <label><?php echo $reclamo['departamento'] ?></label>
                            </fieldset>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php _e('Province', 'rt-libro') ?></th>
                        <td>
                            <fieldset>
                                <label><?php echo $reclamo['provincia'] ?></label>
                            </fieldset>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php _e('District', 'rt-libro') ?></th>
                        <td>
                            <fieldset>
                                <label><?php echo $reclamo['distrito'] ?></label>
                            </fieldset>
                        </td>
                    </tr>
                    <?php if ($reclamo['flag_menor']) { ?>
                        <tr>
                            <th scope="row"><?php _e('Name of tutor', 'rt-libro') ?></th>
                            <td>
                                <fieldset>
                                    <label><?php echo $reclamo['nombre_tutor'] ?></label>
                                </fieldset>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><?php _e('Email of tutor', 'rt-libro') ?></th>
                            <td>
                                <fieldset>
                                    <label><?php echo $reclamo['email_tutor'] ?></label>
                                </fieldset>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><?php _e('Type of documentation', 'rt-libro') ?></th>
                            <td>
                                <fieldset>
                                    <label><?php echo rt_libro_lrq_get_tipo_documentacion($reclamo['tipo_doc_tutor']); ?></label>
                                </fieldset>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><?php _e('Documentation number', 'rt-libro') ?></th>
                            <td>
                                <fieldset>
                                    <label><?php echo $reclamo['numero_documento_tutor'] ?></label>
                                </fieldset>
                            </td>
                        </tr>
                    <?php } ?>
                    <tr>
                        <th scope="row"><?php _e('Claim type', 'rt-libro') ?></th>
                        <td>
                            <fieldset>
                                <label><?php echo rt_libro_lrq_get_tipo_reclamacion($reclamo['tipo_reclamacion']) ?></label>
                            </fieldset>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php _e('Type of consumption', 'rt-libro') ?></th>
                        <td>
                            <fieldset>
                                <label><?php echo rt_libro_lrq_get_tipo_consumo($reclamo['tipo_consumo']) ?></label>
                            </fieldset>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php _e('Order number', 'rt-libro') ?></th>
                        <td>
                            <fieldset>
                                <label><?php echo $reclamo['nro_pedido'] ?></label>
                            </fieldset>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php _e('Claim date', 'rt-libro') ?></th>
                        <td>
                            <fieldset>
                                <label><?php echo $reclamo['fch_reclamo'] ?></label>
                            </fieldset>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php _e('Product description', 'rt-libro') ?></th>
                        <td>
                            <textarea rows="10" cols="100" readonly><?php echo $reclamo['descripcion'] ?></textarea>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php _e('Provider', 'rt-libro') ?></th>
                        <td>
                            <fieldset>
                                <label><?php echo $reclamo['proveedor'] ?></label>
                            </fieldset>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php _e('Date of purchase', 'rt-libro') ?></th>
                        <td>
                            <fieldset>
                                <label><?php echo $reclamo['fch_compra'] ?></label>
                            </fieldset>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php _e('Consumption date', 'rt-libro') ?></th>
                        <td>
                            <fieldset>
                                <label><?php echo $reclamo['fch_consumo'] ?></label>
                            </fieldset>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php _e('Expiration date', 'rt-libro') ?></th>
                        <td>
                            <fieldset>
                                <label><?php echo $reclamo['fch_vencimiento'] ?></label>
                            </fieldset>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php _e('Claim detail', 'rt-libro') ?></th>
                        <td>
                            <textarea rows="10" cols="100" readonly><?php echo $reclamo['detalle'] ?></textarea>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php _e('Client order', 'rt-libro') ?></th>
                        <td>
                            <textarea rows="10" cols="100" readonly><?php echo $reclamo['pedido_cliente'] ?></textarea>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php _e('Reclaimed amount', 'rt-libro') ?></th>
                        <td>
                            <fieldset>
                                <label><?php echo $reclamo['monto_reclamado'] ?></label>
                            </fieldset>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php echo ($reclamo['acepta_contenido']) ? 'SI' : 'NO'; ?> <?php _e('I accept the content', 'rt-libro') ?>. </th>
                        <th scope="row"><?php echo ($reclamo['acepta_politica']) ? 'SI' : 'NO'; ?> <?php _e('I accept the Privacy and Security Policy', 'rt-libro') ?>.</th>
                    </tr>
                </tbody>
            </table>
    </div>
    <?php
}

function rt_libro_lrq_submenu_settings_general()
{
    if (isset($_POST["btn_guardar_setting"])) {
        $page_libro_id = get_option('libro_setting_page');
        $page_libro_url = get_option('libro_setting_url');
        if ($page_libro_id) {
            update_option('libro_setting_page', sanitize_text_field($_POST["page_libro"]));
        } else {
            add_option('libro_setting_page', sanitize_text_field($_POST["page_libro"]));
            $page_libro_id = get_option('libro_setting_page');
            if(!$page_libro_id){
                update_option('libro_setting_page', sanitize_text_field($_POST["page_libro"]));
            }
        }
        if ($page_libro_url) {
            update_option('libro_setting_url', sanitize_text_field($_POST["url_libro"]));
        } else {
            add_option('libro_setting_url', sanitize_text_field($_POST["url_libro"]));
            $page_libro_url = get_option('libro_setting_url');
            if(!$page_libro_url){
                update_option('libro_setting_url', sanitize_text_field($_POST["url_libro"]));
            }
        }
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
    <h2><?php _e('General setting', 'rt-libro') ?></h2>
    <form method="post" id="form_ajuste_libro" action="" novalidate="novalidate">
        <table class="form-table" >
            <tbody>
                <tr>
                    <th scope="row">
                        <label><?php _e('Add the following shortcode', 'rt-libro') ?></label></th>
                    <td>
                        <p id="short_copy">[libro_page]</p> <br>
                        <input type="button" onclick="copiarShortcode('short_copy')" value="<?php _e('Copy', 'rt-libro') ?>" /> 
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <label><?php _e('Administration email address', 'rt-libro') ?></label></th>
                    <td>
                        <input name="email_libro" type="email" id="email_libro" value="<?php echo get_option('admin_email'); ?>" disabled="">
                        <p class="description"><?php _e('This address is used for notifications of claims and / or complaints.', 'rt-libro') ?> </p>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="start_of_week"><?php _e('Choose page for complaint book', 'rt-libro') ?></label></th>
                    <td>
                        <?php
                        $args = array(
                            'sort_order' => 'asc',
                            'sort_column' => 'post_title',
                            'hierarchical' => 1,
                            'exclude' => '',
                            'include' => '',
                            'meta_key' => '',
                            'meta_value' => '',
                            'authors' => '',
                            'child_of' => 0,
                            'parent' => -1,
                            'exclude_tree' => '',
                            'number' => '',
                            'offset' => 0,
                            'post_type' => 'page',
                            'post_status' => 'publish'
                        );
    $all_page = get_pages($args); ?>
                        <select name="page_libro" id="page_libro">
                            <option value=""><?php _e('Select page', 'rt-libro') ?></option>
                            <?php foreach ($all_page as $page) {
        $selected = ($page->ID == $page_libro_id) ? 'selected' : ''; ?>
                                <option value="<?php echo $page->ID ?>" <?php echo $selected; ?>><?php echo $page->post_title ?></option>
                            <?php
    } ?>
                        </select>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <label><?php _e('Url of Privacy and Security Policy and Cookies Policy', 'rt-libro') ?></label></th>
                    <td>
                        <input name="url_libro" type="text" id="url_libro" value="<?php echo $page_libro_url ?>" class="regular-text ltr">
                        <p class="description"><?php _e('This address is used for notifications of claims and / or complaints.', 'rt-libro') ?> </p>
                    </td>
                </tr>
            </tbody>
        </table>
        <p class="submit">
            <?php
            $attributes = array('id' => 'btn_guardar_setting');
    submit_button(__('Save changes', 'rt-libro'), 'button button-primary', 'btn_guardar_setting', true, $attributes); ?>
        </p>
    </form>
    <?php
}

function rt_libro_lrq_submenu_settings_callback()
{
    if(current_user_can( 'manage_options' )){
    ?>
    <div class="wrap woocommerce" >
        <div style="background-color:#87b43e;">
        </div>
        <h1><?php _e('Claims and Complaints Book', 'rt-libro') ?></h1>
        <hr>

        <h2 class="nav-tab-wrapper">
            <a href="?page=libro_settings&tab=home" class="nav-tab <?php
            if ((!isset($_REQUEST['tab'])) || ($_REQUEST['tab'] == "home")) {
                print " nav-tab-active";
            } ?>"><?php _e('List', 'rt-libro') ?></a>
            <a href="?page=libro_settings&tab=setting" class="nav-tab <?php
            if (($_REQUEST['tab'] == "setting")) {
                print " nav-tab-active";
            } ?>"><?php _e('Setting', 'rt-libro') ?></a>
        </h2>
        <?php
        if ((!isset($_REQUEST['tab'])) || ($_REQUEST['tab'] == "home")) {
            if (isset($_REQUEST['ver']) > 0) {
                rt_libro_lrq_submenu_settings_ver();
            } else {
                rt_libro_lrq_submenu_settings_home();
            }
        }
    if (isset($_REQUEST['tab']) == "setting") {
        rt_libro_lrq_submenu_settings_general();
    } ?>
    </div>
    <?php
    }
}

function rt_libro_lrq_get_tipo_documentacion($id_tipo)
{
    switch ($id_tipo) {
        case 1:
            $name = __('DNI', 'rt-libro');
            break;
        case 2:
            $name = __('CE', 'rt-libro');
            break;
        case 3:
            $name = __('Passport', 'rt-libro');
            break;
        case 4:
            $name = __('RUC', 'rt-libro');
            break;
    }
    
    return $name;
}

function rt_libro_lrq_get_tipo_reclamacion($id_tipo)
{
    switch ($id_tipo) {
        case 1:
            $name = __('Claim', 'rt-libro');
            break;
        case 2:
            $name = __('Complain', 'rt-libro');
            break;
    }
    return $name;
}


function rt_libro_lrq_get_tipo_consumo($id_tipo)
{
    switch ($id_tipo) {
        case 1:
            $name = __('Product', 'rt-libro');
            break;
        case 2:
            $name = __('Service', 'rt-libro');
            break;
    }
    return $name;
}

function rt_libro_lrq_ubigeo_peru_plugin_libro_enabled()
{
    if (in_array('ubigeo-peru/ubigeo-peru.php', (array) get_option('active_plugins', array()))) {
        return true;
    }
    return false;
}
