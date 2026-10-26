<?php
if (!defined('ABSPATH')) { exit; }
$company = get_option('rt_libro_company_details', array());
if (!is_array($company)) { $company = array(); }
$val = static function ($key) use ($libro_data) { return esc_html(isset($libro_data[$key]) ? (string)$libro_data[$key] : ''); };
$cval = static function ($key) use ($company) { return esc_html(isset($company[$key]) ? (string)$company[$key] : ''); };
$date = !empty($libro_data['fch_reclamo']) && strtotime($libro_data['fch_reclamo']) ? date('d/m/Y', strtotime($libro_data['fch_reclamo'])) : '';
$logo = '';
if (!empty($company['logo_id']) && (!isset($company['show_logo']) || $company['show_logo'] === 'yes')) {
    $logo_path = get_attached_file(absint($company['logo_id']));
    if ($logo_path && is_readable($logo_path) && filesize($logo_path) < 1048576) {
        $mime = wp_check_filetype($logo_path);
        if (in_array($mime['type'], array('image/png','image/jpeg'), true)) {
            $bytes = file_get_contents($logo_path);
            if ($bytes !== false) { $logo = 'data:' . $mime['type'] . ';base64,' . base64_encode($bytes); }
        }
    }
}
$rows = static function ($items) {
    foreach ($items as $label => $value) {
        if ($value === '' || $value === null) { continue; }
        echo '<tr><td class="field-label">' . esc_html($label) . '</td><td class="field-value">' . nl2br(esc_html((string)$value)) . '</td></tr>';
    }
};
?>
<!DOCTYPE html><html lang="es"><head><meta charset="UTF-8"><style>
@page { margin: 26px 32px 30px; }
body { font-family: DejaVu Sans, sans-serif; color:#202020; font-size:9px; margin:0; }
table { border-collapse: collapse; width:100%; }
.header { border-bottom:2px solid #383838; padding-bottom:10px; margin-bottom:14px; }
.header td { vertical-align:middle; }
.logo-cell { width:82px; }
.logo-cell img { max-width:73px; max-height:65px; }
.company-name { font-size:13px; font-weight:bold; color:#171717; margin-bottom:4px; }
.company-details { color:#555555; font-size:8px; line-height:1.5; }
.company-details strong { color:#202020; }
.document-title { font-size:16px; font-weight:bold; color:#171717; margin:0 0 3px; }
.document-subtitle { color:#626262; font-size:8px; }
.identity { margin:12px 0 13px; background:#f3f3f3; border:1px solid #dedede; }
.identity td { padding:10px 12px; }
.identity-label { text-transform:uppercase; font-size:7px; color:#666666; letter-spacing:0.6px; }
.identity-number { font-size:15px; font-weight:bold; color:#181818; }
.section { margin-top:11px; page-break-inside:avoid; }
.section-heading { background:#ededed; color:#242424; padding:7px 9px; font-weight:bold; font-size:10px; }
.data td { border-bottom:1px solid #dddddd; padding:6px 9px; vertical-align:top; line-height:1.4; }
.data .field-label { width:35%; color:#555555; }
.data .field-value { font-weight:bold; word-wrap:break-word; }
.description { padding:7px 9px; border:1px solid #dddddd; border-top:none; line-height:1.45; }
.description .desc-label { font-weight:bold; color:#444444; font-size:8px; margin-bottom:4px; }
.notice { margin-top:12px; padding:9px 10px; background:#f6f6f6; color:#555555; font-size:7.4px; line-height:1.55; }
.signatures { margin-top:17px; font-size:8px; color:#555555; }
.signatures td { width:50%; text-align:center; padding-top:18px; }
.small { font-size:7.3px; color:#555555; }
</style></head><body>
<div class="header"><table><tr>
<?php if ($logo): ?><td class="logo-cell"><img src="<?php echo esc_attr($logo); ?>" alt=""></td><?php endif; ?>
<td><div class="company-name"><?php echo $cval('trade_name') ?: $cval('legal_name') ?: esc_html(get_bloginfo('name')); ?></div>
<div class="company-details">
<?php if ($cval('legal_name')): ?><strong>Razón social:</strong> <?php echo $cval('legal_name'); ?> &nbsp; <?php endif; ?>
<?php if ($cval('ruc')): ?><strong>RUC:</strong> <?php echo $cval('ruc'); ?><br><?php endif; ?>
<?php if ($cval('address')): ?><strong>Dirección:</strong> <?php echo $cval('address'); ?><br><?php endif; ?>
<?php if ($cval('phone')): ?><strong>Teléfono:</strong> <?php echo $cval('phone'); ?><?php endif; ?>
</div></td></tr></table></div>
<div class="document-title">Libro de Reclamaciones</div><div class="document-subtitle">Hoja de reclamación / queja · Constancia de registro</div>
<table class="identity"><tr><td><div class="identity-label">Número de registro</div><div class="identity-number">N.º <?php echo esc_html(str_pad((string)$libro_id, 5, '0', STR_PAD_LEFT)); ?></div></td><td><div class="identity-label">Fecha de registro</div><strong><?php echo esc_html($date); ?></strong></td><td><div class="identity-label">Tipo</div><strong><?php echo ((string)($libro_data['tipo_reclamacion'] ?? '') === '1') ? 'Reclamo' : 'Queja'; ?></strong></td></tr></table>
<div class="section"><div class="section-heading">1. Identificación del consumidor reclamante</div><table class="data">
<?php $rows(array(
'Nombre completo' => trim(($libro_data['nombre'] ?? '') . ' ' . ($libro_data['apellido_paterno'] ?? '') . ' ' . ($libro_data['apellido_materno'] ?? '')),
'Documento' => rt_libro_lrq_get_type_doc($libro_data['tipo_doc'] ?? '') . ' · ' . ($libro_data['nro_documento'] ?? ''),
'Teléfono' => $libro_data['fono'] ?? '', 'Correo electrónico' => $libro_data['email'] ?? '',
'Dirección' => $libro_data['direccion'] ?? '', 'Referencia' => $libro_data['referencia'] ?? '',
'Departamento' => $libro_data['departamento'] ?? '', 'Provincia' => $libro_data['provincia'] ?? '', 'Distrito' => $libro_data['distrito'] ?? '',
'¿Es menor de edad?' => ($libro_data['flag_menor'] ?? '') === '1' ? 'Sí' : 'No',
)); ?>
<?php if (($libro_data['flag_menor'] ?? '') === '1'): $rows(array('Padre, madre o tutor' => $libro_data['nombre_tutor'] ?? '', 'Correo del tutor' => $libro_data['email_tutor'] ?? '', 'Documento del tutor' => rt_libro_lrq_get_type_doc($libro_data['tipo_doc_tutor'] ?? '') . ' · ' . ($libro_data['numero_documento_tutor'] ?? ''))); endif; ?>
</table></div>
<div class="section"><div class="section-heading">2. Detalle del reclamo o queja</div><table class="data">
<?php $rows(array(
'Tipo de reclamo' => (($libro_data['tipo_reclamacion'] ?? '') == '1' ? 'Reclamo' : 'Queja'),
'Tipo de consumo' => (($libro_data['tipo_consumo'] ?? '') == '1' ? 'Producto' : 'Servicio'),
'Número de pedido' => $libro_data['nro_pedido'] ?? '', 'Proveedor' => $libro_data['proveedor'] ?? '',
'Monto reclamado (S/)' => $libro_data['monto_reclamado'] ?? '',
'Fecha de compra' => $libro_data['fch_compra'] ?? '', 'Fecha de consumo' => $libro_data['fch_consumo'] ?? '', 'Fecha de vencimiento' => $libro_data['fch_vencimiento'] ?? '',
)); ?></table>
<?php foreach (array('Descripción del producto o servicio'=>'descripcion','Detalle del reclamo o queja'=>'detalle','Pedido del consumidor'=>'pedido_cliente') as $label=>$key): ?><div class="description"><div class="desc-label"><?php echo esc_html($label); ?></div><?php echo nl2br($val($key)); ?></div><?php endforeach; ?>
</div>
<div class="section"><div class="section-heading">3. Observaciones y acciones adoptadas por el proveedor</div><div class="description"><span class="small">Fecha de comunicación de la respuesta:</span> ____________________<div style="height:28px"></div><span class="small">Detalle / respuesta:</span> ___________________________________________________________</div></div>
<div class="signatures"><table><tr><td>________________________________<br>Firma del consumidor (si corresponde)</td><td>________________________________<br>Representante del proveedor (si corresponde)</td></tr></table></div>
<div class="notice"><strong>Información importante:</strong> Reclamo: disconformidad relacionada con los productos o servicios. Queja: disconformidad no relacionada con ellos o malestar respecto a la atención al público.<br>La formulación del reclamo no impide acudir a otras vías de solución de controversias ni es requisito previo para interponer una denuncia ante INDECOPI. El proveedor deberá responder en un plazo máximo de quince (15) días hábiles improrrogables.</div>
</body></html>
