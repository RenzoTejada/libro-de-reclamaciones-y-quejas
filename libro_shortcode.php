<?php
if (!defined('ABSPATH')) {
    exit;
}
require 'vendor/autoload.php';

use Dompdf\Dompdf;


function rt_libro_lrq_grabar_libro_reclamacion($libro_data)
{
    global $wpdb; // Mantener el uso global, pero se puede considerar pasar como parámetro.
    // In manual mode the submitted values are names, not Ubigeo IDs.
    $location_mode = get_option('rt_libro_location_mode', 'ubigeo');
    if ($location_mode === 'manual' || get_option('rt_libro_form_mode', 'complete') === 'simple') {
        $libro_data['departamento'] = '';
        $libro_data['provincia'] = '';
        $libro_data['distrito'] = '';
    } elseif ($location_mode === 'manual' || !function_exists('rt_libro_lrq_get_departamento_por_id_one')) {
        foreach (array('departamento', 'provincia', 'distrito') as $location_key) {
            $libro_data[$location_key] = sanitize_text_field($libro_data[$location_key]);
        }
    } else {
        $libro_data['departamento'] = rt_libro_lrq_get_departamento_por_id_one($libro_data['departamento']);
        $libro_data['provincia'] = rt_libro_lrq_get_provincia_por_id_one($libro_data['provincia']);
        $libro_data['distrito'] = rt_libro_lrq_get_distrito_por_id_one($libro_data['distrito']);
    }
    // Insertar en la base de datos.
    $table_name = $wpdb->prefix . "rt_libro";
    if ($wpdb->insert($table_name, $libro_data)) {
        $libro_id = $wpdb->insert_id;
        $filepath = rt_libro_lrq_crear_pdf_libro_reclamacion($libro_data, $libro_id);
        try {
            rt_libro_lrq_enviar_mail_libro_reclamacion($libro_data, $libro_id, $filepath);
        } finally {
            // No conservar archivos con datos personales después de enviarlos.
            if (is_string($filepath) && is_file($filepath)) { @unlink($filepath); }
        }
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
    // El PDF contiene datos personales: nunca publicarlo en uploads ni usar un ID correlativo como nombre.
    // Solo se necesita como adjunto durante el envío de correo.
    $temp_dir = rtrim(sys_get_temp_dir(), '/\\');
    $filename = $temp_dir . '/reclamacion-' . bin2hex(random_bytes(20)) . '.pdf';
    ob_start();
    include __DIR__ . '/template/rt-libro-pdf.php';
    $view = ob_get_clean();
    $dompdf->loadHtml($view, 'UTF-8');
    $dompdf->setPaper('A4', 'portrait');
    $dompdf->render();
    if (file_put_contents($filename, $dompdf->output(), LOCK_EX) === false) {
        return false;
    }
    @chmod($filename, 0600);
    return $filename;
}

/** Email HTML de confirmación; etiquetas y datos siempre escapados. */
function rt_libro_lrq_email_rows($rows) {
    $html = '';
    foreach ($rows as $label => $value) {
        if ($value === '' || $value === null) { continue; }
        $html .= '<tr><td style="padding:9px 12px;border-bottom:1px solid #e7eaf0;color:#596579;font-size:13px;vertical-align:top;width:43%;">' . esc_html($label) . '</td>';
        $html .= '<td style="padding:9px 12px;border-bottom:1px solid #e7eaf0;color:#202020;font-size:13px;font-weight:600;vertical-align:top;overflow-wrap:anywhere;">' . nl2br(esc_html((string) $value)) . '</td></tr>';
    }
    return $html;
}

function rt_libro_lrq_email_section($title, $rows) {
    $body = rt_libro_lrq_email_rows($rows);
    if ($body === '') { return ''; }
    return '<tr><td style="padding:22px 25px 6px;"><h2 style="margin:0;color:#1d2939;font-size:16px;line-height:24px;">' . esc_html($title) . '</h2></td></tr>'
        . '<tr><td style="padding:8px 25px 0;"><table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%" style="border-collapse:collapse;">' . $body . '</table></td></tr>';
}

function rt_libro_lrq_enviar_mail_libro_reclamacion($libro_data, $libro_id, $filename)
{
    $v = static function ($key) use ($libro_data) { return isset($libro_data[$key]) ? (string) $libro_data[$key] : ''; };
    $company = get_option('rt_libro_company_details', array());
    if (!is_array($company)) { $company = array(); }
    $company_rows = array();
    foreach (array('trade_name' => __('Nombre comercial', 'rt-libro'), 'legal_name' => __('Razón social', 'rt-libro'), 'ruc' => 'RUC', 'address' => __('Dirección del establecimiento', 'rt-libro'), 'phone' => __('Teléfono', 'rt-libro')) as $key => $label) {
        if (!empty($company[$key])) { $company_rows[$label] = $company[$key]; }
    }
    // Utilizar el logo configurado para el establecimiento. Se incrusta como CID
    // para no depender de imágenes remotas ni exponer rutas del servidor.
    $email_logo_path = '';
    if (!empty($company['logo_id']) && (!isset($company['show_logo']) || $company['show_logo'] === 'yes')) {
        $candidate = get_attached_file(absint($company['logo_id']));
        if (is_string($candidate) && is_readable($candidate) && is_file($candidate) && filesize($candidate) < 1048576) {
            $logo_type = wp_check_filetype($candidate);
            if (in_array($logo_type['type'], array('image/jpeg', 'image/png'), true)) {
                $email_logo_path = $candidate;
            }
        }
    }
    $email_company_name = !empty($company['trade_name']) ? $company['trade_name'] : (!empty($company['legal_name']) ? $company['legal_name'] : get_bloginfo('name'));
    $consumer = array(
        __('Name', 'rt-libro') => $v('nombre'),
        __('First Lastname', 'rt-libro') => $v('apellido_paterno'),
        __('Second Lastname', 'rt-libro') => $v('apellido_materno'),
        __('Type of documentation', 'rt-libro') => rt_libro_lrq_get_type_doc($v('tipo_doc')),
        __('Documentation number', 'rt-libro') => $v('nro_documento'),
        __('Celphone', 'rt-libro') => $v('fono'),
        __('Department', 'rt-libro') => $v('departamento'),
        __('Province', 'rt-libro') => $v('provincia'),
        __('District', 'rt-libro') => $v('distrito'),
        __('Address', 'rt-libro') => $v('direccion'),
        __('Reference', 'rt-libro') => $v('referencia'),
        __('Email', 'rt-libro') => $v('email'),
        __('Are you a minor?', 'rt-libro') => $v('flag_menor') === '1' ? __('Yes', 'rt-libro') : __('No', 'rt-libro'),
    );
    if ($v('flag_menor') === '1') {
        $consumer[__('Name of tutor', 'rt-libro')] = $v('nombre_tutor');
        $consumer[__('Email of tutor', 'rt-libro')] = $v('email_tutor');
        $consumer[__('Type of documentation of tutor', 'rt-libro')] = rt_libro_lrq_get_type_doc($v('tipo_doc_tutor'));
        $consumer[__('Number of document of tutor', 'rt-libro')] = $v('numero_documento_tutor');
    }
    $format_date = static function($value) { return $value && strtotime($value) ? date_i18n('d/m/Y', strtotime($value)) : ''; };
    $claim = array(
        __('Claim Type', 'rt-libro') => $v('tipo_reclamacion') === '1' ? __('Claim', 'rt-libro') : __('Complain', 'rt-libro'),
        __('Type of consumption', 'rt-libro') => $v('tipo_consumo') === '1' ? __('Product', 'rt-libro') : __('Service', 'rt-libro'),
        __('Order No.', 'rt-libro') => $v('nro_pedido'),
        __('Claim / complaint date', 'rt-libro') => $format_date($v('fch_reclamo')),
        __('Provider', 'rt-libro') => $v('proveedor'),
        __('Reclaimed amount', 'rt-libro') => $v('monto_reclamado'),
        __('Description of the product or service', 'rt-libro') => $v('descripcion'),
        __('Date of purchase', 'rt-libro') => $format_date($v('fch_compra')),
        __('Date of Consumption', 'rt-libro') => $format_date($v('fch_consumo')),
        __('Expiration date', 'rt-libro') => $format_date($v('fch_vencimiento')),
        __('Detail of the Claim / Complaint, as indicated by the client', 'rt-libro') => $v('detalle'),
        __('Client order', 'rt-libro') => $v('pedido_cliente'),
    );
    // Los consentimientos ya se guardan en la tabla; presentar el estado real,
    // sin inferir aceptación cuando no se marcó la casilla.
    $affirmative = __('Yes', 'rt-libro');
    $negative = __('No', 'rt-libro');
    $declaration_text = __('I declare that I am the owner of the service and I accept the content of this form by stating under an Affidavit the veracity of the facts described.', 'rt-libro');
    // El texto de políticas puede personalizarse en los ajustes del plugin.
    // Quitar únicamente los tokens de enlaces para mostrar la declaración legible.
    $policy_text = rt_libro_lrq_get_consent_text();
    $policy_text = preg_replace('/\[(\/)?(?:privacy|cookies)\]/i', '', $policy_text);
    $policy_text = wp_strip_all_tags($policy_text);
    $consent_rows = array(
        __('Declaración jurada', 'rt-libro') => $declaration_text . ' — ' . ($v('acepta_contenido') === '1' ? $affirmative : $negative),
        __('Política de privacidad y cookies', 'rt-libro') => $policy_text . ' — ' . ($v('acepta_politica') === '1' ? $affirmative : $negative),
    );
    $name = trim($v('nombre') . ' ' . $v('apellido_paterno'));
    $site_name = get_option('blogname');
    $number = '00' . absint($libro_id);
    $sections = rt_libro_lrq_email_section('Datos del establecimiento', $company_rows)
        . rt_libro_lrq_email_section('Datos del consumidor', $consumer)
        . rt_libro_lrq_email_section('Detalle del reclamo o queja', $claim)
        . rt_libro_lrq_email_section('Declaraciones y autorizaciones', $consent_rows);
    $message_user = '<!doctype html><html lang="es"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1"></head>'
        . '<body style="margin:0;padding:0;background:#f6f6f6;font-family:Arial,Helvetica,sans-serif;color:#202020;">'
        . '<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="background:#f6f6f6;"><tr><td align="center" style="padding:30px 12px;">'
        . '<table role="presentation" width="600" cellpadding="0" cellspacing="0" border="0" style="width:100%;max-width:600px;background:#ffffff;border:1px solid #e3e8ef;border-radius:10px;border-collapse:separate;">'
        . '<tr><td style="padding:20px 25px;border-bottom:1px solid #e2e2e2;background:#ffffff;">'
        . '<table role="presentation" cellspacing="0" cellpadding="0" border="0"><tr>' . ($email_logo_path !== '' ? '<td style="vertical-align:middle;padding-right:14px;"><img src="cid:rt-libro-company-logo" alt="" width="64" style="display:block;max-width:64px;max-height:64px;width:auto;height:auto;border:0;" /></td>' : '') . '<td style="vertical-align:middle;font-size:18px;font-weight:700;color:#222222;">' . esc_html($email_company_name) . '</td></tr></table></td></tr>'
        . '<tr><td style="padding:25px;background:#f6f6f6;border-bottom:1px solid #e2e2e2;">'
        . '<p style="margin:0 0 10px;font-size:13px;font-weight:700;color:#333333;">✓ REGISTRO CONFIRMADO</p>'
        . '<h1 style="margin:0 0 12px;font-size:23px;line-height:30px;color:#202020;">Hemos recibido tu reclamo o queja</h1>'
        . '<p style="margin:0 0 12px;color:#505050;font-size:14px;line-height:22px;">' . esc_html(sprintf('Estimado/a %s, tu solicitud fue registrada correctamente. Adjuntamos una copia en PDF para tus archivos.', $name)) . '</p>'
        . '<p style="margin:0;color:#505050;font-size:13px;">Número de registro</p>'
        . '<p style="margin:3px 0 0;font-size:25px;font-weight:700;color:#202020;">N.º ' . esc_html($number) . '</p></td></tr>'
        . $sections
        . '<tr><td style="padding:25px;color:#536070;font-size:13px;line-height:21px;">Gracias por comunicarte con nosotros.<br><strong>' . esc_html($site_name) . '</strong></td></tr>'
        . '</table><p style="max-width:600px;margin:16px 0 0;font-size:12px;color:#6a7686;text-align:center;">Este correo es la constancia de recepción de tu reclamo o queja.</p>'
        . '</td></tr></table></body></html>';

    $headers = array('Content-Type: text/html; charset=UTF-8');
    $admin_email = sanitize_email(get_option('admin_email'));
    if (is_email($admin_email)) {
        $headers[] = 'From: ' . sanitize_text_field($site_name) . ' <' . $admin_email . '>';
    }
    // Free mantiene un único destinatario interno por CC.
    $notification_email = get_option('rt_libro_notification_email', '');
    if (!is_email($notification_email)) { $notification_email = $admin_email; }
    if (is_email($notification_email)) { $headers[] = 'Cc: ' . sanitize_email($notification_email); }
    $attachments = is_string($filename) && is_file($filename) ? array($filename) : array();
    $attach_brand_image = null;
    if ($email_logo_path !== '') {
        $attach_brand_image = static function ($phpmailer) use ($email_logo_path) {
            $mime = wp_check_filetype($email_logo_path);
            $phpmailer->addEmbeddedImage($email_logo_path, 'rt-libro-company-logo', basename($email_logo_path), 'base64', $mime['type']);
        };
        add_action('phpmailer_init', $attach_brand_image);
    }
    try {
        wp_mail(sanitize_email($v('email')), __('We have received your claim', 'rt-libro'), $message_user, $headers, $attachments);
    } finally {
        if ($attach_brand_image !== null) {
            remove_action('phpmailer_init', $attach_brand_image);
        }
    }
}

function rt_libro_lrq_view_page()
{
    static $scripts_enqueued = false; // Variable estática para evitar registrar scripts múltiples veces
    $html = '';
    if (!is_admin()) {
        if (!$scripts_enqueued) {
            wp_register_script('libro_script_validate', plugins_url('js/jquery.validate.min.js', __FILE__), array('jquery'), '1.10', true);
            wp_enqueue_script('libro_script_validate');
            wp_register_style('libro_script_admin', plugins_url('css/libro_admin.css', __FILE__), array(), '1.3.21');
            wp_enqueue_style('libro_script_admin');
            wp_register_script('libro_script_admin', plugins_url('js/libro_script_admin.js', __FILE__), array(), '0.0.2');
            wp_enqueue_script('libro_script_admin');
            $scripts_enqueued = true; // Marcamos que los scripts ya han sido encolados
        }
        $rpt = 3;
        $libro_id = null;
        if (isset($_GET['libro_recibido'], $_GET['libro_token'])) {
            global $wpdb;
            $success_id = absint($_GET['libro_recibido']);
            $success_token = sanitize_text_field(wp_unslash($_GET['libro_token']));
            if ($success_id && preg_match('/^[a-f0-9]{32}$/', $success_token)) {
                $table = $wpdb->prefix . 'rt_libro';
                $verified = $wpdb->get_var($wpdb->prepare(
                    "SELECT libro_id FROM $table WHERE libro_id = %d AND submission_token = %s",
                    $success_id,
                    $success_token
                ));
                if ($verified) {
                    $libro_id = $success_id;
                }
            }
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

/** Handle claims before HTML is sent, so refresh never re-submits a POST. */
function rt_libro_lrq_process_submission() {
    if (is_admin() || strtoupper($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST' || !isset($_POST['guardar_libro_reclamacion'])) {
        return;
    }
    if (empty($_POST['rt_libro_nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['rt_libro_nonce'])), 'rt_libro_submit')) {
        wp_die(esc_html__('Formulario caducado. Recarga la página e inténtalo nuevamente.', 'rt-libro'), '', ['response' => 403]);
    }
    $token = isset($_POST['rt_libro_token']) ? sanitize_text_field(wp_unslash($_POST['rt_libro_token'])) : '';
    if (!preg_match('/^[a-f0-9]{32}$/', $token)) {
        wp_die(esc_html__('Identificador de formulario inválido.', 'rt-libro'), '', ['response' => 400]);
    }
    $fields = [
        'nombre' => 'nombres', 'apellido_paterno' => 'paterno', 'apellido_materno' => 'materno',
        'tipo_doc' => 'tipo_doc', 'nro_documento' => 'nro_doc', 'fono' => 'cel',
        'email' => 'correo', 'direccion' => 'direccion', 'referencia' => 'referencia',
        'departamento' => 'dep', 'provincia' => 'prov', 'distrito' => 'dist',
        'flag_menor' => 'flag_menor', 'nombre_tutor' => 'nombre_tutor',
        'email_tutor' => 'correo_tutor', 'tipo_doc_tutor' => 'tipo_doc_tutor',
        'numero_documento_tutor' => 'nro_doc_tutor', 'tipo_reclamacion' => 'tipo_reclamo',
        'tipo_consumo' => 'tipo_consumo', 'nro_pedido' => 'nro_pedido',
        'descripcion' => 'descripcion', 'proveedor' => 'proveedor',
        'fch_compra' => 'fch_compra', 'fch_consumo' => 'fch_consumo',
        'fch_vencimiento' => 'fch_vencimiento', 'detalle' => 'detalle_reclamo',
        'pedido_cliente' => 'pedido_cliente', 'monto_reclamado' => 'monto_reclamado',
        'acepta_contenido' => 'acepto', 'acepta_politica' => 'politica'
    ];
    $libro_data = [];
    foreach ($fields as $column => $field) {
        $value = isset($_POST[$field]) && is_scalar($_POST[$field]) ? wp_unslash($_POST[$field]) : '';
        $libro_data[$column] = in_array($column, ['descripcion', 'detalle', 'pedido_cliente'], true)
            ? sanitize_textarea_field($value)
            : sanitize_text_field($value);
    }
    $libro_data['email'] = sanitize_email($libro_data['email']);
    $libro_data['email_tutor'] = sanitize_email($libro_data['email_tutor']);
    $libro_data['fch_reclamo'] = current_time('Y-m-d');
    $libro_data['estado'] = 1;
    $libro_data['submission_token'] = $token;
    // The unique DB constraint is authoritative even for two simultaneous requests.
    global $wpdb;
    $table = $wpdb->prefix . 'rt_libro';
    $existing = $wpdb->get_var($wpdb->prepare("SELECT libro_id FROM $table WHERE submission_token = %s", $token));
    $id = $existing ? absint($existing) : rt_libro_lrq_grabar_libro_reclamacion($libro_data);
    if (!$id) {
        // If another request won the DB insert race, use its original ID.
        $id = absint($wpdb->get_var($wpdb->prepare("SELECT libro_id FROM $table WHERE submission_token = %s", $token)));
    }
    if (!$id) {
        wp_die(esc_html__('No se pudo registrar el reclamo. Inténtalo nuevamente.', 'rt-libro'), '', ['response' => 500]);
    }
    $url = add_query_arg(['libro_recibido' => $id, 'libro_token' => $token], get_permalink());
    wp_safe_redirect($url, 303);
    exit;
}
add_action('template_redirect', 'rt_libro_lrq_process_submission', 0);

add_shortcode('libro_page', 'rt_libro_lrq_view_page');

/** Default consent follows the site's locale until an admin supplies custom wording. */
function rt_libro_lrq_get_consent_text() {
    $stored = get_option('rt_libro_consent_text', null);
    if (is_string($stored)) {
        return $stored;
    }
    // Existing installs keep their original translated sentence until edited.
    return __('I have read and accept the Privacy and Security Policy and the Cookies Policy.', 'rt-libro');
}

/** Render plain text and two optional link tokens without allowing HTML injection. */
function rt_libro_lrq_render_consent($text) {
    // Backward compatibility: preserve the original single policy link before
    // the administrator first saves a custom token-based consent statement.
    if (get_option('rt_libro_consent_text', null) === null) {
        $legacy_url = get_option('libro_setting_url', '');
        return $legacy_url !== ''
            ? '<a href="' . esc_url($legacy_url) . '">' . esc_html($text) . '</a>'
            : esc_html($text);
    }
    $urls = array(
        'privacy' => get_option('rt_libro_consent_privacy_url', get_option('libro_setting_url', '')),
        'cookies' => get_option('rt_libro_consent_cookies_url', ''),
    );
    $output = '';
    $offset = 0;
    $matches = array();
    preg_match_all('/\[(privacy|cookies)\]([\s\S]*?)\[\/\1\]/', $text, $matches, PREG_OFFSET_CAPTURE);
    foreach ($matches[0] as $i => $match) {
        $output .= esc_html(substr($text, $offset, $match[1] - $offset));
        $key = $matches[1][$i][0];
        $label = $matches[2][$i][0];
        $raw_url = isset($urls[$key]) ? $urls[$key] : '';
        if ($raw_url !== '') {
            $url = esc_url($raw_url);
            $host = wp_parse_url($url, PHP_URL_HOST);
            $site_host = wp_parse_url(home_url(), PHP_URL_HOST);
            $external = $host && strtolower($host) !== strtolower((string)$site_host);
            $new_tab = $external && get_option('rt_libro_consent_new_tab', 'yes') === 'yes';
            $output .= '<a href="' . $url . '"' . ($new_tab ? ' target="_blank" rel="noopener noreferrer"' : '') . '>' . esc_html($label) . '</a>';
        } else {
            $output .= esc_html($label);
        }
        $offset = $match[1] + strlen($match[0]);
    }
    $output .= esc_html(substr($text, $offset));
    return nl2br($output);
}

function rt_libro_lrq_html_form_libro_reclamacion($libro_id, $rpt)
{
    $location_mode = get_option('rt_libro_location_mode', 'ubigeo');
    $simple = get_option('rt_libro_form_mode', 'complete') === 'simple';
    $manual = $location_mode === 'manual' || !function_exists('rt_libro_lrq_get_departamento_front');
    $departamentos = $manual ? array() : rt_libro_lrq_get_departamento_front();
    $page_libro_url = get_option('libro_setting_url') ?: '#';
    $today = date("d/m/Y");
    $html_parts = [];
    if ($simple || $manual) {
        $html_parts[] = '<style>' . ($simple ? '.rt-libro-extra-field{display:none!important}' : '.rt-libro-hidden-manual{display:none!important}') . '</style>';
    }
    $html_parts[] = '<form id="rt_form_libro" action="" method="post">
        ' . wp_nonce_field('rt_libro_submit', 'rt_libro_nonce', true, false) . '
        <input type="hidden" name="rt_libro_token" value="' . esc_attr(bin2hex(random_bytes(16))) . '">
        <div id="responsive-form" class="clearfix">';
    if ($libro_id) {
        $registration_number = '00' . absint($libro_id);
        $html_parts[] = '<div class="form-row-libro rtlr-confirmation-row">
                            <div class="column-full">
                                <div class="rtlr-confirmation" role="status" aria-live="polite">
                                    <span class="rtlr-confirmation-icon" aria-hidden="true">&#10003;</span>
                                    <div class="rtlr-confirmation-content">
                                        <strong class="rtlr-confirmation-title">' . esc_html__('¡Tu reclamo o queja fue registrado correctamente!', 'rt-libro') . '</strong>
                                        <p class="rtlr-confirmation-description">' . esc_html__('Hemos recibido tu solicitud. Conserva este número para identificar tu registro.', 'rt-libro') . '</p>
                                        <div class="rtlr-confirmation-reference"><span>' . esc_html__('Número de registro', 'rt-libro') . '</span> <strong>N.º ' . esc_html($registration_number) . '</strong></div>
                                    </div>
                                </div>
                            </div>
                          </div>';
    } elseif ($rpt == 0) {
        $html_parts[] = '<div class="form-row-libro">
                            <div class="column-full" style="text-align: center;">'.__('Your claim / complaint was NOT registered', 'rt-libro').'</div>
                        </div>';
    }
    $company = get_option('rt_libro_company_details', array());
    if (is_array($company) && isset($company['visible']) && $company['visible'] === 'yes') {
        $company_html = '<section class="rt-libro-company">';
        if (!empty($company['logo_id']) && (!isset($company['show_logo']) || $company['show_logo'] === 'yes')) {
            $company_html .= wp_get_attachment_image(absint($company['logo_id']), 'medium', false, array('class'=>'rt-libro-company-logo'));
        }
        $company_html .= '<div class="rt-libro-company-details">';
        foreach (array('trade_name'=>'', 'legal_name'=>__('Razón social', 'rt-libro'), 'ruc'=>'RUC', 'address'=>__('Dirección', 'rt-libro'), 'phone'=>__('Teléfono', 'rt-libro')) as $key=>$label) {
            if (!empty($company[$key])) {
                $company_html .= '<div>' . ($label ? '<strong>'.esc_html($label).':</strong> ' : '<strong>') . esc_html($company[$key]) . ($label ? '' : '</strong>') . '</div>';
            }
        }
        $company_html .= '</div></section>';
        $html_parts[] = $company_html;
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
                <div class="column-half"><label class="rt-libro-field-label" for="tipo_doc">'.__('Type of documentation', 'rt-libro').' <b class="alert">*</b></label>
                    <select id="tipo_doc" name="tipo_doc" class="required" >
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
            </div>';
    if ($manual) {
        // La dirección contiene la ubicación completa; no se piden campos separados.
    } else {
        $html_parts[] = '
            <div class="form-row-libro">
                <div class="column-half"><label class="rt-libro-field-label" for="dep">'.__('Department', 'rt-libro').' <b class="alert">*</b></label>
                    <select id="dep" name="dep" class="required" >
                        <option value="">'.__('Select of department', 'rt-libro').'</option>';
    foreach ($departamentos as $depa) {
        $html_parts[] = '<option value="' . $depa['idDepa'] . '">' . $depa['departamento'] . '</option>';
    }
    $html_parts[] = ' </select>
                </div>
                <div class="column-half"><label class="rt-libro-field-label" for="prov">'.__('Province', 'rt-libro').' <b class="alert">*</b></label>
                    <select id="prov" name="prov" class="required">
                        <option value="">'.__('Select of province', 'rt-libro').'</option>
                    </select>
                </div>
                <div class="column-half"> <label class="rt-libro-field-label" for="dist">'.__('District', 'rt-libro').' <b class="alert">*</b></label>
                    <select id="dist" name="dist" class="required" >
                        <option value="">'.__('Select of district', 'rt-libro').'</option>
                    </select>
                </div>
            </div>';
    }
    $html_parts[] = '
            <div class="form-row-libro">
                <div class="column-half '.(($simple || $manual) ? 'rt-libro-address-wide' : '').'">'.__('Address', 'rt-libro').' <b class="alert">*</b>
                    <input type="text" name="direccion" value="" size="40" placeholder="'.__('Address', 'rt-libro').'" class="required" >
                </div>
                 <div class="column-half rt-libro-extra-field'.($manual ? ' rt-libro-hidden-manual' : '').'">'.__('Reference', 'rt-libro').'
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
                <div class="column-two"><label class="rt-libro-field-label" for="tipo_doc_tutor">'.__('Type of documentation', 'rt-libro').'</label>
                    <select id="tipo_doc_tutor" name="tipo_doc_tutor" >
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
                <div class="column-half"><label class="rt-libro-field-label" for="tipo_reclamo">'.__('Claim Type', 'rt-libro').' <b class="alert">*</b></label>
                    <select id="tipo_reclamo" name="tipo_reclamo" class="required">
                        <option value="">'.__('Claim Type', 'rt-libro').'</option>
                        <option value="1">'.__('Claim', 'rt-libro').' (1)</option>
                        <option value="2">'.__('Complain', 'rt-libro').'(2)</option>
                    </select>
                </div>
                <div class="column-half"><label class="rt-libro-field-label" for="tipo_consumo">'.__('Type of consumption', 'rt-libro').' <b class="alert">*</b></label>
                    <select id="tipo_consumo" name="tipo_consumo" class="required">
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
            <div class="form-row-libro rt-libro-extra-field">
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
                    <label class="rt-libro-consent"><input type="checkbox" name="politica" value="1"> <span class="rt-libro-consent-text">'.rt_libro_lrq_render_consent(rt_libro_lrq_get_consent_text()).'</span></label>
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

