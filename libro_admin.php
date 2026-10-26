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
    $request = $wpdb->prepare("SELECT * FROM $table_name WHERE libro_id = %d", $id);
    return $wpdb->get_row($request, ARRAY_A);
}
function rt_libro_lrq_submenu_settings_home() {
    rt_libro_lrq_render_dashboard(true);
}
function rt_libro_lrq_submenu_settings_ver() {
    $id = isset($_GET['ver']) ? absint($_GET['ver']) : 0;
    $reclamo = $id ? rt_libro_lrq_get_reclamo_by_id($id) : null;
    echo '<div class="rtlr-detail">';
    echo '<p><a class="button" href="' . esc_url(admin_url('admin.php?page=libro_settings&tab=home')) . '">← ' . esc_html__('Volver al listado', 'rt-libro') . '</a></p>';
    if (!$reclamo) { echo '<div class="notice notice-warning inline"><p>' . esc_html__('No se encontró el reclamo.', 'rt-libro') . '</p></div></div>'; return; }
    $status = isset($reclamo['estado']) ? (int) $reclamo['estado'] : 1;
    $status_text = function_exists('rt_libro_lrq_status_text') ? rt_libro_lrq_status_text($status) : (string)$status;
    echo '<div class="rtlr-detail-header"><div><span class="rtlr-eyebrow">' . esc_html__('Detalle del registro', 'rt-libro') . '</span><h2>' . esc_html__('Reclamo / Queja', 'rt-libro') . ' #' . esc_html($id) . '</h2><p>' . esc_html($reclamo['fch_reclamo'] ?: '') . '</p></div><span class="rtlr-pill">' . esc_html($status_text) . '</span></div>';
    $sections = array(
      __('Datos del consumidor', 'rt-libro') => array(
        __('Nombre', 'rt-libro') => trim(($reclamo['nombre'] ?? '') . ' ' . ($reclamo['apellido_paterno'] ?? '') . ' ' . ($reclamo['apellido_materno'] ?? '')),
        __('Documento', 'rt-libro') => rt_libro_lrq_get_tipo_documentacion($reclamo['tipo_doc']) . ' · ' . ($reclamo['nro_documento'] ?? ''),
        __('Celular', 'rt-libro') => $reclamo['fono'] ?? '', __('Correo electrónico', 'rt-libro') => $reclamo['email'] ?? '',
        __('Dirección', 'rt-libro') => $reclamo['direccion'] ?? '', __('Referencia', 'rt-libro') => $reclamo['referencia'] ?? '',
        __('Ubicación', 'rt-libro') => implode(' / ', array_filter(array($reclamo['departamento'] ?? '', $reclamo['provincia'] ?? '', $reclamo['distrito'] ?? ''))),
      ),
      __('Información del reclamo', 'rt-libro') => array(
        __('Tipo de reclamación', 'rt-libro') => rt_libro_lrq_get_tipo_reclamacion($reclamo['tipo_reclamacion']),
        __('Producto o servicio', 'rt-libro') => rt_libro_lrq_get_tipo_consumo($reclamo['tipo_consumo']),
        __('Número de pedido', 'rt-libro') => $reclamo['nro_pedido'] ?? '',
        __('Proveedor', 'rt-libro') => $reclamo['proveedor'] ?? '',
        __('Monto reclamado', 'rt-libro') => $reclamo['monto_reclamado'] ?? '',
        __('Fecha de compra', 'rt-libro') => $reclamo['fch_compra'] ?? '',
        __('Fecha de consumo', 'rt-libro') => $reclamo['fch_consumo'] ?? '',
        __('Fecha de vencimiento', 'rt-libro') => $reclamo['fch_vencimiento'] ?? '',
      ),
    );
    echo '<div class="rtlr-detail-grid">';
    foreach ($sections as $title => $fields) {
        echo '<section class="rtlr-panel"><h3>' . esc_html($title) . '</h3><dl class="rtlr-detail-fields">';
        foreach ($fields as $label => $value) {
            if ($value === null || trim((string)$value) === '') { continue; }
            echo '<div><dt>' . esc_html($label) . '</dt><dd>' . esc_html($value) . '</dd></div>';
        }
        echo '</dl></section>';
    }
    echo '</div>';
    if (!empty($reclamo['flag_menor'])) {
        echo '<section class="rtlr-panel"><h3>' . esc_html__('Datos del tutor', 'rt-libro') . '</h3><dl class="rtlr-detail-fields">';
        foreach (array(__('Nombre', 'rt-libro') => $reclamo['nombre_tutor'] ?? '', __('Correo', 'rt-libro') => $reclamo['email_tutor'] ?? '', __('Documento', 'rt-libro') => ($reclamo['numero_documento_tutor'] ?? '')) as $label => $value) { echo '<div><dt>'.esc_html($label).'</dt><dd>'.esc_html($value).'</dd></div>'; }
        echo '</dl></section>';
    }
    echo '<section class="rtlr-panel"><h3>' . esc_html__('Descripción y solicitud del consumidor', 'rt-libro') . '</h3>';
    foreach (array(__('Descripción del producto o servicio', 'rt-libro') => $reclamo['descripcion'] ?? '', __('Detalle del reclamo', 'rt-libro') => $reclamo['detalle'] ?? '', __('Pedido del consumidor', 'rt-libro') => $reclamo['pedido_cliente'] ?? '') as $label => $value) {
        if (!trim((string)$value)) { continue; }
        echo '<div class="rtlr-detail-long"><strong>'.esc_html($label).'</strong><p>'.nl2br(esc_html($value)).'</p></div>';
    }
    echo '</section><section class="rtlr-panel"><h3>' . esc_html__('Declaraciones registradas', 'rt-libro') . '</h3><dl class="rtlr-detail-fields"><div><dt>'.esc_html__('Aceptó declaración', 'rt-libro').'</dt><dd>'.(!empty($reclamo['acepta_contenido']) ? esc_html__('Sí', 'rt-libro') : esc_html__('No', 'rt-libro')).'</dd></div><div><dt>'.esc_html__('Aceptó privacidad', 'rt-libro').'</dt><dd>'.(!empty($reclamo['acepta_politica']) ? esc_html__('Sí', 'rt-libro') : esc_html__('No', 'rt-libro')).'</dd></div></dl></section>';
    echo '</div>';
}
function rt_libro_lrq_submenu_settings_general() {
    $rt_libro_draft = null;
    if (isset($_POST["btn_guardar_setting"]) && current_user_can('manage_options')) {
        check_admin_referer('rt_libro_save_settings', 'rt_libro_settings_nonce');
        $posted = wp_unslash($_POST);
        $raw_company = isset($posted['rt_company']) && is_array($posted['rt_company']) ? $posted['rt_company'] : array();
        $company = array();
        foreach (array('trade_name', 'legal_name', 'address', 'phone') as $key) {
            $company[$key] = isset($raw_company[$key]) && is_string($raw_company[$key]) ? sanitize_text_field($raw_company[$key]) : '';
        }
        // Do not silently strip invalid characters before deciding whether to accept the RUC.
        $raw_ruc = isset($raw_company['ruc']) && is_string($raw_company['ruc']) ? trim($raw_company['ruc']) : '';
        $company['ruc'] = $raw_ruc;
        $company['email'] = isset($raw_company['email']) && is_string($raw_company['email']) ? trim($raw_company['email']) : '';
        $company['logo_id'] = isset($raw_company['logo_id']) ? absint($raw_company['logo_id']) : 0;
        $company['visible'] = !empty($raw_company['visible']) ? 'yes' : 'no';
        $company['show_logo'] = !empty($raw_company['show_logo']) ? 'yes' : 'no';
        $recipient_raw = isset($posted['email_libro']) && is_string($posted['email_libro']) ? trim($posted['email_libro']) : '';
        $form_mode = isset($posted['rt_libro_form_mode']) ? sanitize_key($posted['rt_libro_form_mode']) : '';
        $location_mode = isset($posted['rt_libro_location_mode']) ? sanitize_key($posted['rt_libro_location_mode']) : '';
        $rt_libro_draft = array(
            'company' => $company,
            'notification_email' => $recipient_raw,
            'form_mode' => $form_mode,
            'location_mode' => $location_mode,
            'page' => isset($posted['page_libro']) ? absint($posted['page_libro']) : 0,
            'url' => isset($posted['url_libro']) ? (string)$posted['url_libro'] : '',
            'consent_text' => isset($posted['rt_libro_consent_text']) ? (string)$posted['rt_libro_consent_text'] : '',
            'privacy_url' => isset($posted['rt_libro_consent_privacy_url']) ? (string)$posted['rt_libro_consent_privacy_url'] : '',
            'cookies_url' => isset($posted['rt_libro_consent_cookies_url']) ? (string)$posted['rt_libro_consent_cookies_url'] : '',
            'new_tab' => !empty($posted['rt_libro_consent_new_tab']) ? 'yes' : 'no',
        );
        $errors = array();
        if ($recipient_raw === '' || preg_match('/[,;\s<>]/', $recipient_raw) || !is_email($recipient_raw)) {
            $errors['email_libro'] = __('Ingresa una sola dirección de correo electrónico válida.', 'rt-libro');
        }
        if ($raw_ruc !== '' && !preg_match('/^[0-9]{11}$/D', $raw_ruc)) {
            $errors['rt_company_ruc'] = __('El RUC debe contener exactamente 11 dígitos o estar vacío.', 'rt-libro');
        }
        if ($company['email'] !== '' && (preg_match('/[,;\s<>]/', $company['email']) || !is_email($company['email']))) {
            $errors['rt_company_email'] = __('Ingresa un único correo de atención válido.', 'rt-libro');
        }
        // Validate every supplied company field without discarding other posted values.
        foreach (array('trade_name' => 2, 'legal_name' => 3, 'address' => 8) as $key => $minimum) {
            $value = trim($company[$key]);
            if ($value !== '' && (function_exists('mb_strlen') ? mb_strlen($value) : strlen($value)) < $minimum) {
                $errors['rt_company_' . $key] = sprintf(__('El campo %s debe contener al menos %d caracteres.', 'rt-libro'), $key, $minimum);
            }
        }
        if ($company['legal_name'] !== '' && !preg_match('/\p{L}/u', $company['legal_name'])) {
            $errors['rt_company_legal_name'] = __('La razón social debe contener al menos una letra.', 'rt-libro');
        }
        if ($company['phone'] !== '') {
            $digits = preg_replace('/\D+/', '', $company['phone']);
            if (!preg_match('/^[+0-9() .-]+$/', $company['phone']) || strlen($digits) < 6 || strlen($digits) > 15) {
                $errors['rt_company_phone'] = __('El teléfono debe contener entre 6 y 15 dígitos y un formato válido.', 'rt-libro');
            }
        }
        if ($company['visible'] === 'yes') {
            foreach (array('legal_name' => __('razón social', 'rt-libro'), 'ruc' => 'RUC', 'address' => __('dirección', 'rt-libro')) as $key => $label) {
                if (trim($company[$key]) === '') {
                    $errors['rt_company_' . $key] = sprintf(__('Para mostrar el bloque, completa %s.', 'rt-libro'), $label);
                }
            }
        }
        if ($company['logo_id'] && !wp_attachment_is_image($company['logo_id'])) {
            $errors['rt_company_logo_id'] = __('Selecciona un archivo de imagen válido para el logo.', 'rt-libro');
        }
        if (!in_array($form_mode, array('complete', 'simple'), true) || !in_array($location_mode, array('ubigeo', 'manual'), true)) {
            $errors['rt_libro_form_mode'] = __('Selecciona un tipo de formulario y una ubicación válidos.', 'rt-libro');
        }
        if ($errors) {
            foreach ($errors as $field => $message) {
                add_settings_error('rt_libro_settings', $field, $message, 'error');
            }
            add_settings_error('rt_libro_settings', 'unsaved_draft', __('No se guardó ningún cambio. Corrige los campos indicados; todos los datos escritos siguen disponibles.', 'rt-libro'), 'warning');
        } else {
            $company['email'] = sanitize_email($company['email']);
            update_option('rt_libro_company_details', $company);
            update_option('rt_libro_notification_email', sanitize_email($recipient_raw));
            update_option('rt_libro_form_mode', $form_mode);
            update_option('rt_libro_location_mode', $location_mode);
            update_option('rt_libro_setup_completed', 'yes');
            update_option('libro_setting_page', $rt_libro_draft['page']);
            update_option('libro_setting_url', esc_url_raw($rt_libro_draft['url']));
            update_option('rt_libro_consent_text', sanitize_textarea_field($rt_libro_draft['consent_text']));
            update_option('rt_libro_consent_privacy_url', esc_url_raw($rt_libro_draft['privacy_url']));
            update_option('rt_libro_consent_cookies_url', esc_url_raw($rt_libro_draft['cookies_url']));
            update_option('rt_libro_consent_new_tab', $rt_libro_draft['new_tab']);
            $rt_libro_draft = null;
            add_settings_error('rt_libro_settings', 'saved', __('Configuración guardada correctamente.', 'rt-libro'), 'updated');
        }
    }
    $page_libro_id = get_option('libro_setting_page');
    $page_libro_url = get_option('libro_setting_url');
    $notification_email = get_option('rt_libro_notification_email', '');
    if (!is_email($notification_email)) { $notification_email = get_option('admin_email'); }
    // Preserve submitted values in the same request whenever validation fails.
    if ($rt_libro_draft !== null) {
        $page_libro_id = $rt_libro_draft['page'];
        $page_libro_url = $rt_libro_draft['url'];
        $notification_email = $rt_libro_draft['notification_email'];
    }
    settings_errors('rt_libro_settings');
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
    <div class="rtlr-settings"><div class="rtlr-settings-head"><h2><?php _e('Configuración general', 'rt-libro') ?></h2><p><?php _e('Gestiona la página pública y los enlaces de las políticas del formulario.', 'rt-libro') ?></p></div>
    <form method="post" id="form_ajuste_libro" action="">
        <?php wp_nonce_field('rt_libro_save_settings', 'rt_libro_settings_nonce'); ?>
        <?php $company = $rt_libro_draft !== null ? $rt_libro_draft['company'] : get_option('rt_libro_company_details', array()); if (!is_array($company)) { $company = array(); } ?>
        <div class="rtlr-panel rtlr-settings-panel"><h3><?php esc_html_e('Datos de la empresa', 'rt-libro'); ?></h3>
          <p class="description"><?php esc_html_e('La identificación del establecimiento es independiente del nombre del sitio. El bloque público está oculto inicialmente y solo aparece cuando lo actives.', 'rt-libro'); ?></p>
          <table class="form-table"><tbody>
            <tr><th scope="row"><?php esc_html_e('Visibilidad', 'rt-libro'); ?></th><td><label><input type="checkbox" name="rt_company[visible]" value="1" <?php checked(isset($company['visible']) ? $company['visible'] : 'no', 'yes'); ?>> <?php esc_html_e('Mostrar los datos de la empresa encima del formulario público', 'rt-libro'); ?></label></td></tr>
            <tr><th scope="row"><?php esc_html_e('Logo', 'rt-libro'); ?></th><td>
              <input type="hidden" id="rt_company_logo_id" name="rt_company[logo_id]" value="<?php echo esc_attr(isset($company['logo_id']) ? absint($company['logo_id']) : 0); ?>">
              <div id="rt_company_logo_preview"><?php if (!empty($company['logo_id'])) { echo wp_get_attachment_image(absint($company['logo_id']), 'thumbnail', false, array('style' => 'max-width:160px;height:auto;')); } ?></div>
              <button type="button" class="button" id="rt_company_logo_choose"><?php esc_html_e('Seleccionar logo', 'rt-libro'); ?></button> <button type="button" class="button" id="rt_company_logo_remove"><?php esc_html_e('Quitar logo', 'rt-libro'); ?></button>
              <p><label><input type="checkbox" name="rt_company[show_logo]" value="1" <?php checked(isset($company['show_logo']) ? $company['show_logo'] : 'yes', 'yes'); ?>> <?php esc_html_e('Mostrar logo cuando el bloque esté activo', 'rt-libro'); ?></label></p>
            </td></tr>
            <?php foreach (array('trade_name' => 'Nombre comercial', 'legal_name' => 'Razón social', 'ruc' => 'RUC', 'address' => 'Dirección del establecimiento', 'phone' => 'Teléfono', 'email' => 'Correo de atención') as $key => $label): ?>
            <tr><th scope="row"><label for="rt_company_<?php echo esc_attr($key); ?>"><?php echo esc_html__($label, 'rt-libro'); ?></label></th><td>
            <?php if ($key === 'address'): ?><textarea rows="2" class="large-text" style="max-width:600px" id="rt_company_<?php echo esc_attr($key); ?>" name="rt_company[<?php echo esc_attr($key); ?>]"><?php echo esc_textarea(isset($company[$key]) ? $company[$key] : ''); ?></textarea>
            <?php else: ?><input class="regular-text" style="max-width:420px;width:100%" type="<?php echo $key === 'email' ? 'email' : 'text'; ?>" id="rt_company_<?php echo esc_attr($key); ?>" name="rt_company[<?php echo esc_attr($key); ?>]" value="<?php echo esc_attr(isset($company[$key]) ? $company[$key] : ''); ?>" <?php echo $key === 'ruc' ? 'inputmode="numeric" maxlength="11" pattern="[0-9]{11}" title="El RUC debe tener exactamente 11 dígitos"' : ''; ?>><?php endif; ?>
            <?php if (!empty($errors['rt_company_' . $key])): ?><p class="description" style="color:#b32d2e" role="alert"><?php echo esc_html($errors['rt_company_' . $key]); ?></p><?php endif; ?>
            </td></tr><?php endforeach; ?>
          </tbody></table>
          <p class="description"><?php esc_html_e('Ocultar el bloque no borra estos datos. Comprueba los datos legales con tu asesor; los campos guardados podrán incorporarse a las constancias.', 'rt-libro'); ?></p>
        </div>
        <script>
        jQuery(function($){
          var form=document.getElementById('form_ajuste_libro');
          var ruc=document.getElementById('rt_company_ruc');
          function validateRuc(){
             var v=ruc.value.trim();
             ruc.setCustomValidity(v && !/^[0-9]{11}$/.test(v) ? 'El RUC debe tener exactamente 11 dígitos o estar vacío.' : '');
          }
          if(ruc){ruc.addEventListener('input',function(){validateRuc();validateCompany();});ruc.addEventListener('blur',function(){validateRuc();validateCompany();});}
          var businessFields = ['trade_name','legal_name','address','phone'];
          var visible = form.querySelector('[name="rt_company[visible]"]');
          function validateCompany(){
            businessFields.forEach(function(key){
              var el=document.getElementById('rt_company_'+key); if(!el)return;
              var val=el.value.trim(), min=key==='trade_name'?2:key==='legal_name'?3:key==='address'?8:0, msg='';
              if(val && min && val.length<min) msg='Introduce al menos '+min+' caracteres.';
              if(key==='legal_name' && val && !/[^\W\d_]/u.test(val)) msg='La razón social debe incluir letras.';
              if(key==='phone' && val){var digits=val.replace(/\D/g,'');if(!/^[+0-9() .-]+$/.test(val)||digits.length<6||digits.length>15)msg='Introduce entre 6 y 15 dígitos.';}
              if(visible && visible.checked && ['legal_name','address'].includes(key) && !val) msg='Necesario para mostrar los datos de empresa.';
              el.setCustomValidity(msg);
            });
            if(visible&&visible.checked&&ruc&&!ruc.value.trim())ruc.setCustomValidity('El RUC es necesario para mostrar el bloque de empresa.');
          }
          businessFields.forEach(function(key){var el=document.getElementById('rt_company_'+key);if(el){el.addEventListener('input',validateCompany);el.addEventListener('blur',validateCompany);}});
          if(visible)visible.addEventListener('change',function(){validateRuc();validateCompany();});

          if(form){form.addEventListener('submit',function(e){validateRuc(); validateCompany(); if(!form.checkValidity()){e.preventDefault();form.reportValidity();}});}
        });
        jQuery(function($){$('#rt_company_logo_choose').on('click',function(e){e.preventDefault();if(!window.wp||!wp.media)return;var frame=wp.media({title:'Seleccionar logo de empresa',button:{text:'Utilizar logo'},multiple:false,library:{type:'image'}});frame.on('select',function(){var obj=frame.state().get('selection').first().toJSON();$('#rt_company_logo_id').val(obj.id);$('#rt_company_logo_preview').empty().append($('<img>',{src:(obj.sizes&&obj.sizes.thumbnail?obj.sizes.thumbnail.url:obj.url),alt:'',css:{maxWidth:'160px',height:'auto'}}));});frame.open();});$('#rt_company_logo_remove').on('click',function(e){e.preventDefault();$('#rt_company_logo_id').val('0');$('#rt_company_logo_preview').empty();});});
        </script>
        <div class="rtlr-panel rtlr-settings-panel"><h3><?php esc_html_e('Configuración del formulario', 'rt-libro'); ?></h3>
          <p class="description"><?php esc_html_e('Elige cómo presentar el formulario y la ubicación. Tus preferencias se conservarán al actualizar.', 'rt-libro'); ?></p>
          <table class="form-table"><tbody><tr><th scope="row"><?php esc_html_e('Tipo de formulario', 'rt-libro'); ?></th><td>
          <fieldset><label><input type="radio" name="rt_libro_form_mode" value="complete" <?php checked($rt_libro_draft !== null ? $rt_libro_draft['form_mode'] : get_option('rt_libro_form_mode', 'complete'), 'complete'); ?>> <?php esc_html_e('Completo (mantiene los campos tradicionales)', 'rt-libro'); ?></label><br>
          <label><input type="radio" name="rt_libro_form_mode" value="simple" <?php checked($rt_libro_draft !== null ? $rt_libro_draft['form_mode'] : get_option('rt_libro_form_mode', 'complete'), 'simple'); ?>> <?php esc_html_e('Simplificado (oculta referencias y fechas complementarias)', 'rt-libro'); ?></label></fieldset></td></tr>
          <tr><th scope="row"><?php esc_html_e('Ubicación del consumidor', 'rt-libro'); ?></th><td>
          <fieldset><label><input type="radio" name="rt_libro_location_mode" value="ubigeo" <?php checked($rt_libro_draft !== null ? $rt_libro_draft['location_mode'] : get_option('rt_libro_location_mode', 'ubigeo'), 'ubigeo'); ?>> <?php esc_html_e('Ubigeo Perú: listas desplegables', 'rt-libro'); ?></label><br>
          <label><input type="radio" name="rt_libro_location_mode" value="manual" <?php checked($rt_libro_draft !== null ? $rt_libro_draft['location_mode'] : get_option('rt_libro_location_mode', 'ubigeo'), 'manual'); ?>> <?php esc_html_e('Manual: solo dirección completa (sin campos de departamento, provincia ni distrito)', 'rt-libro'); ?></label></fieldset>
          <p class="description"><?php esc_html_e('El modo manual no requiere Ubigeo Perú. Para usar listas necesitas tener ese plugin activo.', 'rt-libro'); ?>
          <a href="<?php echo esc_url(admin_url('plugin-install.php?s=ubigeo-peru&tab=search&type=term')); ?>"><?php esc_html_e('Buscar e instalar Ubigeo Perú', 'rt-libro'); ?></a></p>
          </td></tr></tbody></table></div>
        <div class="rtlr-panel rtlr-settings-panel"><h3>Publicación y notificaciones</h3><table class="form-table">
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
                    <input name="email_libro" type="email" id="email_libro" value="<?php echo esc_attr($notification_email); ?>" required maxlength="254" autocomplete="email" style="width:100%;max-width:360px;" pattern="[^\s,;]+@[^\s,;]+\.[^\s,;]+">
                    <p class="description"><?php _e('A este único correo se enviará la copia de cada reclamo o queja. No afecta al correo administrador de WordPress.', 'rt-libro') ?></p>
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
            </tbody></table></div><div class="rtlr-panel rtlr-settings-panel"><h3>Texto de aceptación de políticas</h3><p class="description">Personaliza la frase y los enlaces sin modificar el idioma elegido por tu tienda.</p><table class="form-table"><tbody>
            <tr>
                <th scope="row"><label for="rt_libro_consent_text">Texto de aceptación de políticas</label></th>
                <td>
                    <textarea id="rt_libro_consent_text" name="rt_libro_consent_text" class="large-text" rows="3"><?php echo esc_textarea($rt_libro_draft !== null ? $rt_libro_draft['consent_text'] : rt_libro_lrq_get_consent_text()); ?></textarea>
                    <p class="description">Escribe una sola frase. Usa [privacy]texto del enlace[/privacy] y [cookies]texto del enlace[/cookies]. Puedes escribir en español, inglés o el idioma de tu tienda; no traducimos el texto que guardes.</p>
                    <p><label for="rt_libro_consent_privacy_url">URL de privacidad</label><br>
                    <input type="url" class="regular-text" id="rt_libro_consent_privacy_url" name="rt_libro_consent_privacy_url" value="<?php echo esc_attr(($rt_libro_draft !== null ? $rt_libro_draft['privacy_url'] : get_option('rt_libro_consent_privacy_url', get_option('libro_setting_url', '')))); ?>" placeholder="/politica-de-privacidad/"></p>
                    <p><label for="rt_libro_consent_cookies_url">URL de cookies</label><br>
                    <input type="url" class="regular-text" id="rt_libro_consent_cookies_url" name="rt_libro_consent_cookies_url" value="<?php echo esc_attr(($rt_libro_draft !== null ? $rt_libro_draft['cookies_url'] : get_option('rt_libro_consent_cookies_url', ''))); ?>" placeholder="/politica-de-cookies/"></p>
                    <p><label><input type="checkbox" name="rt_libro_consent_new_tab" value="1" <?php checked($rt_libro_draft !== null ? $rt_libro_draft['new_tab'] : get_option('rt_libro_consent_new_tab', 'yes'), 'yes'); ?>> Abrir enlaces externos en otra pestaña</label></p>
                    <strong>Vista previa</strong>
                    <div id="rt_libro_consent_preview" style="padding:12px;margin-top:8px;background:#f6f7f7;line-height:1.6;max-width:760px;overflow-wrap:anywhere;"></div>
                    <script>
                    (function () {
                        var txt = document.getElementById('rt_libro_consent_text');
                        var preview = document.getElementById('rt_libro_consent_preview');
                        function render() {
                            preview.replaceChildren();
                            var value = txt.value, re = /\[(privacy|cookies)\]([\s\S]*?)\[\/\1\]/g, last = 0, match;
                            while ((match = re.exec(value)) !== null) {
                                preview.appendChild(document.createTextNode(value.slice(last, match.index)));
                                var link = document.createElement('a');
                                link.textContent = match[2];
                                var url = document.getElementById(match[1] === 'privacy' ? 'rt_libro_consent_privacy_url' : 'rt_libro_consent_cookies_url').value;
                                if (url) { link.href = url; link.target = '_blank'; link.rel = 'noopener noreferrer'; preview.appendChild(link); }
                                else { preview.appendChild(document.createTextNode(match[2])); }
                                last = re.lastIndex;
                            }
                            preview.appendChild(document.createTextNode(value.slice(last)));
                        }
                        ['rt_libro_consent_text','rt_libro_consent_privacy_url','rt_libro_consent_cookies_url'].forEach(function(id){ document.getElementById(id).addEventListener('input',render); });
                        render();
                    })();
                    </script>
                </td>
            </tr>
            </tbody>
        </table>
        <p class="submit">
            <?php
            submit_button(__('Guardar cambios', 'rt-libro'), 'button button-primary', 'btn_guardar_setting', true);
            ?>
        </p>
    </form></div>
    <?php
}
function rt_libro_lrq_submenu_settings_callback() {
    if (current_user_can('manage_options')) {
        ?>
        <div class="wrap woocommerce">
            <h1 class="screen-reader-text"><?php esc_html_e('Libro de Reclamaciones y Quejas', 'rt-libro'); ?></h1>
            <div class="rtlr-admin-hero" aria-label="<?php echo esc_attr__('Libro de Reclamaciones y Quejas — Renzo Tejada', 'rt-libro'); ?>">
                <img src="<?php echo esc_url(plugins_url('image/libro-admin-banner.jpg', __FILE__)); ?>" alt="<?php echo esc_attr__('Libro de Reclamaciones y Quejas por Renzo Tejada', 'rt-libro'); ?>" loading="eager" width="1500" height="500" />
            </div>
            <h2 class="nav-tab-wrapper">
                <a href="?page=libro_settings" class="nav-tab <?php echo (empty($_REQUEST['tab']) || $_REQUEST['tab'] == "home") ? "nav-tab-active" : ""; ?>"><?php _e('Lista', 'rt-libro') ?></a>
                <a href="?page=libro_settings&tab=setting" class="nav-tab <?php echo (isset($_REQUEST['tab']) && $_REQUEST['tab'] == "setting") ? "nav-tab-active" : ""; ?>"><?php _e('Configuración', 'rt-libro') ?></a>
                <a href="<?php echo esc_url(admin_url('admin.php?page=libro_settings&tab=addons')); ?>" class="nav-tab <?php echo (isset($_GET['tab']) && $_GET['tab'] === 'addons') ? 'nav-tab-active' : ''; ?>"><?php esc_html_e('Addons', 'rt-libro'); ?></a>
            </h2>
            <?php
            if (empty($_REQUEST['tab']) || $_REQUEST['tab'] == "home") {
                if (isset($_REQUEST['ver'])) {
                    rt_libro_lrq_submenu_settings_ver();
                    rt_libro_lrq_render_status_form(absint($_REQUEST['ver']));
                } else {
                    rt_libro_lrq_submenu_settings_home();
                }
            } elseif (isset($_REQUEST['tab']) && $_REQUEST['tab'] == "setting") {
                rt_libro_lrq_submenu_settings_general();
            } elseif (isset($_GET['tab']) && $_GET['tab'] === 'addons') {
                rt_libro_lrq_render_addons();
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