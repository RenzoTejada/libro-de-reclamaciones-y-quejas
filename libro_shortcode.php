<?php
if (!defined('ABSPATH')) {
    exit;
}
require 'vendor/autoload.php';

use Dompdf\Dompdf;


function rt_libro_lrq_grabar_libro_reclamacion($libro_data)
{
    global $wpdb; // Mantener el uso global, pero se puede considerar pasar como parámetro.
    // Agrupar las consultas para obtener los datos de departamento, provincia y distrito.
    $departamento = rt_libro_lrq_get_departamento_por_id_one($libro_data['departamento']);
    $provincia = rt_libro_lrq_get_provincia_por_id_one($libro_data['provincia']);
    $distrito = rt_libro_lrq_get_distrito_por_id_one($libro_data['distrito']);
    // Asignar los valores obtenidos a $libro_data.
    $libro_data['departamento'] = $departamento;
    $libro_data['provincia'] = $provincia;
    $libro_data['distrito'] = $distrito;
    // Insertar en la base de datos.
    $table_name = $wpdb->prefix . "rt_libro";
    if ($wpdb->insert($table_name, $libro_data)) {
        $libro_id = $wpdb->insert_id;
        $filepath = rt_libro_lrq_crear_pdf_libro_reclamacion($libro_data, $libro_id);
        rt_libro_lrq_enviar_mail_libro_reclamacion($libro_data, $libro_id, $filepath);
        return $libro_id; // Retornar el ID del libro creado.
    }
    return null; // Retornar null en caso de fallo en la inserción.
}

function rt_libro_lrq_get_type_doc($tipo_doc)
{
    $tipos_documento = [
        1 => "DNI",
        2 => "CE",
        3 => "Passport",
        4 => "RUC"
    ];
    return isset($tipos_documento[$tipo_doc]) ? $tipos_documento[$tipo_doc] : "Tipo de documento desconocido";
}

function rt_libro_lrq_crear_pdf_libro_reclamacion($libro_data, $libro_id)
{
    $dompdf = new Dompdf();
    $wp_upload_dir = wp_upload_dir();
    $upload_dir = $wp_upload_dir['basedir'] . '/libro-pdfs';
    // Asegurarse de que el directorio existe
    if (!file_exists($upload_dir)) {
        wp_mkdir_p($upload_dir);
    }
    // Usar ob_start() y ob_get_clean() de manera eficiente
    ob_start();
    include(WP_PLUGIN_DIR . '/libro-de-reclamaciones-y-quejas/template/rt-libro-pdf.php');
    $view = ob_get_clean();
    $dompdf->loadHtml($view);
    $dompdf->render();
    $pdf = $dompdf->output();
    $filename = $upload_dir . '/libro-' . $libro_id . ".pdf";
    file_put_contents($filename, $pdf);
    return $filename;
}

function rt_libro_lrq_enviar_mail_libro_reclamacion($libro_data, $libro_id, $filename)
{
    // Email para el usuario y admin
    $message_user = __('Dear:', 'rt-libro') . " " ." {$libro_data['nombre']}  {$libro_data['apellido_paterno']} ,\r\n\r\n".__('Thank you very much for leaving us your opinion about our services.', 'rt-libro')."\r\n\r\n".__('Your claim has been successfully received.', 'rt-libro');
    $message_user .= "\r\n\r\n".__('Nro', 'rt-libro') .":  00". $libro_id;
    $message_user .= "\r\n\r\n".__('Name', 'rt-libro') .": ". $libro_data['nombre'];
    $message_user .= "\r\n\r\n".__('First Lastname', 'rt-libro') .": ". $libro_data['apellido_paterno'];
    $message_user .= "\r\n\r\n".__('Second Lastname', 'rt-libro') .": ". $libro_data['apellido_materno'];
    $message_user .= "\r\n\r\n".__('Type of documentation', 'rt-libro') .": ". rt_libro_lrq_get_type_doc($libro_data['tipo_doc']);
    $message_user .= "\r\n\r\n".__('Documentation number', 'rt-libro') .": ". $libro_data['nro_documento'];
    $message_user .= "\r\n\r\n".__('Celphone', 'rt-libro') .": ". $libro_data['fono'];
    $message_user .= "\r\n\r\n".__('Department', 'rt-libro') .": ". $libro_data['departamento'];
    $message_user .= "\r\n\r\n".__('Province', 'rt-libro') .": ". $libro_data['provincia'];
    $message_user .= "\r\n\r\n".__('District', 'rt-libro') .": ". $libro_data['distrito'];
    $message_user .= "\r\n\r\n".__('Address', 'rt-libro') .": ". $libro_data['direccion'];
    $message_user .= "\r\n\r\n".__('Reference', 'rt-libro') .": ". $libro_data['referencia'];
    $message_user .= "\r\n\r\n".__('Email', 'rt-libro') .": ". $libro_data['email'];
    $message_user .= "\r\n\r\n".__('Are you a minor?', 'rt-libro') .": ". ($libro_data['flag_menor'] == '1' ? __('Yes', 'rt-libro') : __('No', 'rt-libro'));
    if($libro_data['flag_menor']){
        $message_user .= "\r\n\r\n".__('Name of tutor', 'rt-libro') .": ". $libro_data['nombre_tutor'];
        $message_user .= "\r\n\r\n".__('Email of tutor', 'rt-libro') .": ". $libro_data['email_tutor'];
        $message_user .= "\r\n\r\n".__('Type of documentation of tutor', 'rt-libro') .": ". rt_libro_lrq_get_type_doc($libro_data['tipo_doc_tutor']);
        $message_user .= "\r\n\r\n".__('Number of document of tutor', 'rt-libro') .": ". $libro_data['numero_documento_tutor'];
    }
    $message_user .= "\r\n\r\n".__('Claim Type', 'rt-libro') .": ". ($libro_data['tipo_reclamacion'] == 1 ? __('Claim', 'rt-libro') : __('Complain', 'rt-libro'));
    $message_user .= "\r\n\r\n".__('Type of consumption', 'rt-libro') .": ". ($libro_data['tipo_consumo']==1 ? __('Product', 'rt-libro') : __('Service', 'rt-libro'));
    $message_user .= "\r\n\r\n".__('Order No.', 'rt-libro') .": ". $libro_data['nro_pedido'];
    $message_user .= "\r\n\r\n".__('Claim / complaint date', 'rt-libro') .": ". date("d/m/Y", strtotime($libro_data['fch_reclamo']));
    $message_user .= "\r\n\r\n".__('Provider', 'rt-libro') .": ". $libro_data['proveedor'];
    $message_user .= "\r\n\r\n".__('Reclaimed amount', 'rt-libro') .": ". $libro_data['monto_reclamado'];
    $message_user .= "\r\n\r\n".__('Description of the product or service', 'rt-libro') .": ". $libro_data['descripcion'];
    $message_user .= "\r\n\r\n".__('Date of purchase', 'rt-libro') .": ". date("d/m/Y", strtotime($libro_data['fch_compra']));
    $message_user .= "\r\n\r\n".__('Date of Consumption', 'rt-libro') .": ". date("d/m/Y", strtotime($libro_data['fch_consumo']));
    $message_user .= "\r\n\r\n".__('Expiration date', 'rt-libro') .": " . date("d/m/Y", strtotime($libro_data['fch_vencimiento']));
    $message_user .= "\r\n\r\n".__('Detail of the Claim / Complaint, as indicated by the client', 'rt-libro') .": ". $libro_data['detalle'];
    $message_user .= "\r\n\r\n".__('Client order', 'rt-libro') .": ". $libro_data['pedido_cliente'];

    $message_user .= "\r\n\r\n".__('Atte.', 'rt-libro')." \r\n\r\n". get_option('blogname');
    $headers[] = __('From: ', 'rt-libro') .' '. get_option('blogname') . ' <'.get_option('admin_email').'>' . "\r\n";
    $headers[] = 'Cc: <'.get_option('admin_email').'>' . "\r\n";
    wp_mail($libro_data['email'], __('We have received your claim', 'rt-libro'), $message_user, $headers,$filename);
}

function rt_libro_lrq_view_page()
{
    static $scripts_enqueued = false; // Variable estática para evitar registrar scripts múltiples veces
    $html = '';
    if (!is_admin()) {
        if (!$scripts_enqueued) {
            wp_register_script('libro_script_validate', plugins_url('js/jquery.validate.min.js', __FILE__), array('jquery'), '1.10', true);
            wp_enqueue_script('libro_script_validate');
            wp_register_style('libro_script_admin', plugins_url('css/libro_admin.css', __FILE__), array(), '0.0.4');
            wp_enqueue_style('libro_script_admin');
            wp_register_script('libro_script_admin', plugins_url('js/libro_script_admin.js', __FILE__), array(), '0.0.2');
            wp_enqueue_script('libro_script_admin');
            $scripts_enqueued = true; // Marcamos que los scripts ya han sido encolados
        }
        $rpt = 3; // Se puede considerar pasar esto como parámetro o devolverlo
        $libro_id = null; // Inicializamos la variable
        if (isset($_POST['guardar_libro_reclamacion'])) {
            $libro_data = array_map('sanitize_text_field', array(
                'nombre' => $_POST['nombres'],
                'apellido_paterno' => $_POST['paterno'],
                'apellido_materno' => $_POST['materno'],
                'tipo_doc' => $_POST['tipo_doc'],
                'nro_documento' => $_POST['nro_doc'],
                'fono' => $_POST['cel'],
                'email' => sanitize_email($_POST['correo']),
                'direccion' => $_POST['direccion'],
                'referencia' => $_POST['referencia'],
                'departamento' => $_POST['dep'],
                'provincia' => $_POST['prov'],
                'distrito' => $_POST['dist'],
                'flag_menor' => $_POST['flag_menor'],
                'nombre_tutor' => $_POST['nombre_tutor'],
                'email_tutor' => $_POST['correo_tutor'],
                'tipo_doc_tutor' => $_POST['tipo_doc_tutor'],
                'numero_documento_tutor' => $_POST['nro_doc_tutor'],
                'tipo_reclamacion' => $_POST['tipo_reclamo'],
                'tipo_consumo' => $_POST['tipo_consumo'],
                'nro_pedido' => $_POST['nro_pedido'],
                'fch_reclamo' => date("Y-m-d"), // Solo se llama una vez
                'descripcion' => $_POST['descripcion'],
                'proveedor' => $_POST['proveedor'],
                'fch_compra' => $_POST['fch_compra'],
                'fch_consumo' => $_POST['fch_consumo'],
                'fch_vencimiento' => $_POST['fch_vencimiento'],
                'detalle' => $_POST['detalle_reclamo'],
                'pedido_cliente' => $_POST['pedido_cliente'],
                'monto_reclamado' => $_POST['monto_reclamado'],
                'acepta_contenido' => $_POST['acepto'],
                'acepta_politica' => isset($_POST['politica']) ? $_POST['politica'] : '0',
                'estado' => 1,
            ));
            $libro_id = rt_libro_lrq_grabar_libro_reclamacion($libro_data);
        }
        $html .= '
        <div class="wrapper claim-wong center">
            <div class="content">
        ';
        $html .= '<section class="libro-content">';
        $html .= rt_libro_lrq_html_form_libro_reclamacion($libro_id, $rpt);
        $html .= '</section>';
        $html .= '</div>';
        $html .= '</div>';
    }
    return $html;
}

add_shortcode('libro_page', 'rt_libro_lrq_view_page');

function rt_libro_lrq_html_form_libro_reclamacion($libro_id, $rpt)
{
    $departamentos = rt_libro_lrq_get_departamento_front();
    $page_libro_url = get_option('libro_setting_url') ?: '#';
    $today = date("d/m/Y");
    $html_parts = [];
    $html_parts[] = '<form id="rt_form_libro" action="" method="post">
        <div id="responsive-form" class="clearfix">';
    if ($libro_id) {
        $html_parts[] = '<div class="form-row-libro">
                            <div class="column-full" style="text-align: center;">'.__('Your claim / complaint was registered:', 'rt-libro').'</div>
                            <div class="column-full" style="text-align: center;">N°: 00'.$libro_id.'</div>
                          </div>';
    } elseif ($rpt == 0) {
        $html_parts[] = '<div class="form-row-libro">
                            <div class="column-full" style="text-align: center;">'.__('Your claim / complaint was NOT registered', 'rt-libro').'</div>
                        </div>';
    }
    $html_parts[] = '
            <div class="form-row-libro">
                <div class="column-full"><h2 class="title">'.__('Complaining Consumer Identification', 'rt-libro').' 
                <b class="alert" style="font-size: 10px">* '.__('Required data', 'rt-libro').'</b></h2> </div>
            </div>
            <div class="form-row-libro">
                <div class="column-half">'.__('Name', 'rt-libro').' <b class="alert">*</b>
                    <input type="text" name="nombres" value="" size="40" class="required" placeholder="'.__('Name', 'rt-libro').'" >
                </div>
                <div class="column-half">'.__('First Lastname', 'rt-libro').' <b class="alert">*</b>
                    <input type="text" name="paterno" value="" size="40" class="required" placeholder="'.__('First Lastname', 'rt-libro').'" >
                </div>
                <div class="column-half">'.__('Second Lastname', 'rt-libro').' <b class="alert">*</b>
                    <input type="text" name="materno" value="" size="40" class="required" placeholder="'.__('Second Lastname', 'rt-libro').'" >
                </div>
            </div>
            <div class="form-row-libro">
                <div class="column-half">'.__('Type of documentation', 'rt-libro').' <b class="alert">*</b>
                    <select id="tipo_doc" name="tipo_doc" tabindex="-1" aria-hidden="true" class="required" >
                        <option value="">'.__('Select of documentation', 'rt-libro').'</option>
                        <option value="1">'.__('DNI', 'rt-libro').'</option>
                        <option value="2">'.__('CE', 'rt-libro').'</option>
                        <option value="3">'.__('Passport', 'rt-libro').'</option>
                        <option value="4">'.__('RUC', 'rt-libro').'</option>
                    </select>
                </div>
                <div class="column-half">'.__('Documentation number', 'rt-libro').' <b class="alert">*</b>
                    <input type="text" name="nro_doc" value="" size="40" placeholder="'.__('Documentation number', 'rt-libro').' " class="required" >
                </div>
                <div class="column-half">'.__('Celphone', 'rt-libro').' <b class="alert">*</b>
                    <input type="text" name="cel" value="" size="40" placeholder="'.__('Celphone', 'rt-libro').'" class="required" >
                </div>
            </div>
            <div class="form-row-libro">
                <div class="column-half">'.__('Department', 'rt-libro').' <b class="alert">*</b>
                    <select id="dep" name="dep" tabindex="-1" aria-hidden="true" class="required" >
                        <option value="">'.__('Select of department', 'rt-libro').'</option>';
    foreach ($departamentos as $depa) {
        $html_parts[] = '<option value="' . $depa['idDepa'] . '">' . $depa['departamento'] . '</option>';
    }
    $html_parts[] = ' </select>
                </div>
                <div class="column-half">'.__('Province', 'rt-libro').' <b class="alert">*</b>
                    <select id="prov" name="prov" tabindex="-1" aria-hidden="true" class="required">
                        <option value="">'.__('Select of province', 'rt-libro').'</option>
                    </select>
                </div>
                <div class="column-half"> '.__('District', 'rt-libro').' <b class="alert">*</b>
                    <select id="dist" name="dist" tabindex="-1" aria-hidden="true" class="required" >
                        <option value="">'.__('Select of district', 'rt-libro').'</option>
                    </select>
                </div>
            </div>
            <div class="form-row-libro">
                <div class="column-half">'.__('Address', 'rt-libro').' <b class="alert">*</b>
                    <input type="text" name="direccion" value="" size="40" placeholder="'.__('Address', 'rt-libro').'" class="required" >
                </div>
                 <div class="column-half">'.__('Reference', 'rt-libro').'
                    <input type="text" name="referencia" value="" size="40" id="referencia" placeholder="'.__('Reference', 'rt-libro').'" >
                </div>
                 <div class="column-half">'.__('Email', 'rt-libro').' <b class="alert">*</b>
                    <input type="text" name="correo" value="" size="40" placeholder="'.__('Email', 'rt-libro').'"  class="required">
                </div>
            </div>
            <div class="form-row-libro">
            <div class="column-full" style="text-align: center;"><br></div>
            </div>
            <div class="form-row-libro">
                <div class="column-half"> '.__('Are you a minor?', 'rt-libro').'
                </div>
                <div class="column-half">'.__('Yes', 'rt-libro').'
                    <input type="radio" id="si" class="edad" name="flag_menor"  value="1">
                </div>
                <div class="column-half">'.__('No', 'rt-libro').'
                    <input type="radio" id="no" class="edad" name="flag_menor" value="0">
                </div>
            </div>
            <div class="form-row-libro" id="title_tutor" style="display: none;" >
                <div class="column-full" style="text-align: center;"><h2 class="title">'.__('Father / Mother / Tutor', 'rt-libro').'</h2> </div>
            </div>
            <div class="form-row-libro" id="datos_tutor" style="display: none;" >
                <div class="column-two">'.__('Name', 'rt-libro').' 
                    <input type="text" name="nombre_tutor" value="" size="40" placeholder="'.__('Name', 'rt-libro').' " >
                </div>
                <div class="column-two">'.__('Email', 'rt-libro').' 
                    <input type="text" name="correo_tutor" value="" size="40" placeholder="'.__('Email', 'rt-libro').' " >
                </div>
            </div>
            <div class="form-row-libro" id="doc_tutor" style="display: none;" >
                <div class="column-two">'.__('Type of documentation', 'rt-libro').' 
                    <select id="tipo_doc_tutor" name="tipo_doc_tutor" tabindex="-1" aria-hidden="true" >
                        <option value="">'.__('Select of documentation', 'rt-libro').'</option>
                        <option value="1">'.__('DNI', 'rt-libro').'</option>
                        <option value="2">'.__('CE', 'rt-libro').'</option>
                        <option value="3">'.__('Passport', 'rt-libro').'</option>
                        <option value="4">'.__('RUC', 'rt-libro').'</option>
                    </select>
                </div>
                <div class="column-two">'.__('Number of document', 'rt-libro').' 
                    <input type="text" name="nro_doc_tutor" value="" size="40" placeholder="'.__('Number of document', 'rt-libro').'" >
                </div>
            </div>
            <div class="form-row-libro">
            <div class="column-full" style="text-align: center;"><br></div>
            </div>
            <div class="form-row-libro">
                <div class="column-full"><h2 class="title"> '.__('Detail of the Claim and Consumer Order', 'rt-libro').' <b class="alert" style="font-size: 9px">* '.__('Required data ', 'rt-libro').'</b></h2></div>
            </div>
            <div class="form-row-libro">
                <div class="column-half">'.__('Claim Type', 'rt-libro').' <b class="alert">*</b>
                    <select id="tipo_reclamo" name="tipo_reclamo" tabindex="-1" aria-hidden="true" class="required">
                        <option value="">'.__('Claim Type', 'rt-libro').'</option>
                        <option value="1">'.__('Claim', 'rt-libro').' (1)</option>
                        <option value="2">'.__('Complain', 'rt-libro').'(2)</option>
                    </select>
                </div>
                <div class="column-half">'.__('Type of consumption', 'rt-libro').' <b class="alert">*</b>
                    <select id="tipo_consumo" name="tipo_consumo" tabindex="-1" aria-hidden="true" class="required">
                        <option value="">'.__('Type of consumption', 'rt-libro').'</option>
                        <option value="1">'.__('Product', 'rt-libro').'</option>
                        <option value="2">'.__('Service', 'rt-libro').'</option>
                    </select>
                </div>
                <div class="column-half">'.__('Order No.', 'rt-libro').' <b class="alert">*</b>
                    <input type="text" name="nro_pedido" value="" size="40" placeholder="Nº Pedido" class="required">
                </div>
            </div>
            <div class="form-row-libro">
                <div class="column-half">'.__('Claim / complaint date', 'rt-libro').'
                    <input type="text" name="fch_reclamo" value="'.$today.'" size="40" readonly>
                </div>
                <div class="column-half">'.__('Provider', 'rt-libro').'
                    <input type="text" name="proveedor" value="" size="40" placeholder="'.__('Provider', 'rt-libro').'" >
                </div>
                <div class="column-half">'.__('Reclaimed amount', 'rt-libro').' (S/.) 
                    <input type="text" name="monto_reclamado" value="" size="40" placeholder="'.__('Reclaimed amount', 'rt-libro').'" >
                </div>
            </div>
            <div class="form-row-libro">
                <div class="column-full" style="text-align: center;">
                    '.__('Description of the product or service', 'rt-libro').' <b class="alert">*</b>
                    <textarea name="descripcion" class="required"></textarea>
                </div>
            </div>
            <div class="form-row-libro">
                <div class="column-half">'.__('Date of purchase', 'rt-libro').'
                    <input type="date" name="fch_compra" value="" size="40" placeholder="00/00/0000" >
                </div>
                <div class="column-half">'.__('Date of Consumption', 'rt-libro').'
                    <input type="date" name="fch_consumo" value="" size="40" placeholder="00/00/0000" >
                </div>
                <div class="column-half">'.__('Expiration date', 'rt-libro').'
                    <input type="date" name="fch_vencimiento" value="" size="40" placeholder="00/00/0000" >
                </div>
            </div>
            <div class="form-row-libro">
                <div class="column-full" style="text-align: center;">
                   '.__('Detail of the Claim / Complaint, as indicated by the client', 'rt-libro').': <b class="alert">*</b>
                    <textarea name="detalle_reclamo" class="required" ></textarea>
                </div>
            </div>
            <div class="form-row-libro">
                <div class="column-full" style="text-align: center;">
                    '.__('Client order', 'rt-libro').': <b class="alert">*</b>
                    <textarea name="pedido_cliente"  class="required"></textarea>
                </div>
            </div>
            <div class="form-row-libro">
                <div class="column-full" style="">
                   <b class="alert">(1)</b>  <strong style="color:#333333">'.__('Claim', 'rt-libro').':<strong><i style="color:#7d7d7d"> '.__('Disagreement related to products and / or services.', 'rt-libro').'</i><br>
                   <b class="alert">(2)</b>  <strong style="color:#333333">'.__('Complain', 'rt-libro').':<strong><i style="color:#7d7d7d">'.__('Disagreement not related to products and / or services; or, discomfort or dissatisfaction with the attention to the public.', 'rt-libro').' </i><br>
                </div>
            </div>
            <div class="form-row-libro">
                <div class="column-two">
                <input type="checkbox" name="acepto" value="1">
                    '.__('I declare that I am the owner of the service and I accept the content of this form by stating under an Affidavit the veracity of the facts described.', 'rt-libro').'
                </div>
                <div class="column-two" style="font-size:10px">
                <b class="alert">*</b> '.__('The formulation of the claim does not preclude resorting to other means of dispute resolution nor is it a prerequisite for filing a complaint with Indecopi.', 'rt-libro').' <br>
                <b class="alert">*</b> '.__('The provider must respond to the claim within a period of no more than fifteen (15) calendar days, being able to extend the period up to fifteen days.', 'rt-libro').'<br>
                <b class="alert">*</b> '.__('By signing this document, the client authorizes to be contacted after the claim has been dealt with in order to evaluate the quality and satisfaction with the claims service process.', 'rt-libro').' 
                </div>
            </div>
            <div class="form-row-libro">
                <div class="column-full">
                    <input type="checkbox" name="politica" value="1">
                    <a href="'.$page_libro_url.'" target="_black">'.__('I have read and accept the Privacy and Security Policy and the Cookies Policy.', 'rt-libro').'</a>
                </div>
            </div>
            <div class="form-row-libro">
                <div class="column-full" style="text-align: center;">
                    <input type="submit" id="guardar_libro_reclamacion" name="guardar_libro_reclamacion" value="'.__('Send', 'rt-libro').'">
                </div>
            </div>
        </div>
    </form>';
    return implode('', $html_parts);
}

