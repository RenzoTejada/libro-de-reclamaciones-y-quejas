<?php
if (!defined('ABSPATH')) { exit; }
/** Free edition: status 1 pending, 2 in review, 3 replied. Existing rows (1) remain pending. */
function rt_libro_lrq_admin_url($args = array()) {
    return add_query_arg($args, admin_url('admin.php?page=libro_settings'));
}
function rt_libro_lrq_admin_css() {
    $screen = get_current_screen();
    if (!$screen || ($screen->id !== 'dashboard' && strpos($screen->id, 'libro') === false)) { return; }
    wp_register_style('rtlr-free-admin', false, array(), '1.2.8');
    wp_enqueue_style('rtlr-free-admin');
    wp_add_inline_style('rtlr-free-admin', file_get_contents(plugin_dir_path(__FILE__) . 'css/libro_admin_dashboard.css'));
    wp_add_inline_style('rtlr-free-admin', '.rtlr-cards{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:14px;margin:20px 0}.rtlr-card{background:#fff;border:1px solid #dcdcde;border-radius:9px;padding:19px;text-decoration:none!important;color:#1d2327!important;box-shadow:0 1px 3px #0000000a}.rtlr-card strong{font-size:29px;display:block;margin:10px 0}.rtlr-card span{color:#50575e}.rtlr-list{background:#fff;border-radius:9px;border:1px solid #dcdcde;overflow-x:auto}.rtlr-list table{width:100%;border-collapse:collapse}.rtlr-list th,.rtlr-list td{text-align:left;padding:13px 12px;border-bottom:1px solid #eceef0;vertical-align:middle}.rtlr-pill{font-weight:600;border-radius:12px;padding:4px 9px;display:inline-block;background:#eaf1fb;color:#1d4b7a}.rtlr-pill.warn{background:#fff1d7;color:#825400}.rtlr-pill.danger{background:#fde8e7;color:#ad2525}.rtlr-pill.ok{background:#dff5e5;color:#176332}.rtlr-bar{display:flex;gap:12px;align-items:center;flex-wrap:wrap;margin:14px 0}.rtlr-bar input,.rtlr-bar select{min-height:34px}.rtlr-info{color:#646970}.rtlr-heading{font-size:20px;margin:22px 0 4px}@media(max-width:900px){.rtlr-cards{grid-template-columns:repeat(2,minmax(0,1fr))}}@media(max-width:480px){.rtlr-cards{grid-template-columns:1fr}}');
    wp_add_inline_style('rtlr-free-admin', '.rtlr-widget-footer{border-top:1px solid #dcdcde;padding-top:12px;margin-top:14px;color:#646970}.rtlr-addons{margin:22px 0}.rtlr-addons-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(230px,1fr));gap:18px;max-width:1200px}.rtlr-addon{background:#fff;border:1px solid #dcdcde;border-radius:10px;overflow:hidden;display:flex;flex-direction:column}.rtlr-addon img{width:100%;height:185px;object-fit:contain;background:#f6f7f7}.rtlr-addon-content{padding:16px;display:flex;flex-direction:column;flex:1}.rtlr-addon-content h3{margin:0 0 8px}.rtlr-addon-content p{flex:1;margin:0 0 15px;color:#50575e}.rtlr-brand{margin-top:22px}');
}
add_action('admin_enqueue_scripts', 'rt_libro_lrq_admin_css');
function rt_libro_lrq_holidays() {
    // Filter receives YYYY-MM-DD holidays supplied by an appropriate Peru holiday calendar.
    return (array) apply_filters('rt_libro_peru_holidays', array());
}
function rt_libro_lrq_business_days_left($received) {
    if (!$received || $received === '0000-00-00 00:00:00') { return null; }
    $tz = wp_timezone();
    try { $start = new DateTimeImmutable(substr($received,0,10).' 12:00:00', $tz); }
    catch (Exception $e) { return null; }
    $holidays=rt_libro_lrq_holidays();
    $due=$start; $days=0;
    while ($days<15) {
        $due=$due->modify('+1 day');
        if ((int)$due->format('N')<=5 && !in_array($due->format('Y-m-d'), $holidays, true)) { $days++; }
    }
    $today = new DateTimeImmutable('today',$tz);
    if ($today == $due->setTime(0,0)) { return 0; }
    $sign=$today<$due?1:-1; $cursor=$sign===1?$today:$due;
    $end=$sign===1?$due:$today; $count=0;
    for ($i=0; $cursor<$end && $i<10000; $i++) {
        $cursor=$cursor->modify('+1 day');
        if ((int)$cursor->format('N')<=5 && !in_array($cursor->format('Y-m-d'),$holidays,true)) {$count++;}
    }
    return $sign*$count;
}
function rt_libro_lrq_status_text($status) {
    return array(1=>__('Pendiente','rt-libro'),2=>__('En revisión','rt-libro'),3=>__('Respondido','rt-libro'))[(int)$status] ?? __('Pendiente','rt-libro');
}
function rt_libro_lrq_all_stats($month=null) {
    global $wpdb; $table=$wpdb->prefix.'rt_libro';
    $where='1=1'; $args=array();
    if ($month && preg_match('/^\\d{4}-(0[1-9]|1[0-2])$/',$month)) {
        $where='fch_reclamo >= %s AND fch_reclamo < %s';
        $dt=DateTimeImmutable::createFromFormat('!Y-m-d',$month.'-01');
        $args=array($month.'-01',$dt->modify('+1 month')->format('Y-m-d'));
    }
    $sql="SELECT COUNT(*) AS total, SUM(CASE WHEN estado=2 THEN 1 ELSE 0 END) AS reviewing, SUM(CASE WHEN estado=3 THEN 1 ELSE 0 END) AS answered FROM $table WHERE $where";
    return (array)$wpdb->get_row($args?$wpdb->prepare($sql,$args):$sql,ARRAY_A);
}
function rt_libro_lrq_handle_status_change() {
    if (!current_user_can('manage_options')) { wp_die(esc_html__('Sin permiso.','rt-libro')); }
    check_admin_referer('rtlr_change_status');
    global $wpdb; $id=absint($_POST['claim_id']??0); $status=absint($_POST['new_status']??0);
    if (!$id || !in_array($status,array(1,2,3),true)) { wp_die('Invalid status'); }
    $table=$wpdb->prefix.'rt_libro';
    $data=array('estado'=>$status); $format=array('%d');
    if ($status===3) { $data['rt_responded_at']=current_time('mysql');$format[]='%s'; }
    else { $data['rt_responded_at']=null;$format[]='%s'; }
    $wpdb->update($table,$data,array('libro_id'=>$id),$format,array('%d'));
    wp_safe_redirect(rt_libro_lrq_admin_url(array('ver'=>$id,'rt_updated'=>1))); exit;
}
add_action('admin_post_rtlr_change_status','rt_libro_lrq_handle_status_change');
function rt_libro_lrq_admin_widget() {
    if (current_user_can('manage_options')) {
        wp_add_dashboard_widget('rtlr_dashboard_widget', __('Libro de Reclamaciones','rt-libro'),function(){ rt_libro_lrq_render_dashboard(false); });
    }
}
add_action('wp_dashboard_setup','rt_libro_lrq_admin_widget');
function rt_libro_lrq_render_dashboard($full=true) {
    if (!current_user_can('manage_options')) {return;}
    global $wpdb; $table=$wpdb->prefix.'rt_libro';
    // Unified filters: by default no filter is applied to cards or the historical list.
    $status=$full && isset($_GET['rt_status']) ? sanitize_text_field(wp_unslash($_GET['rt_status'])) : '';
    if (!in_array($status,array('1','2','3'),true)) {$status='';}
    $search=$full && isset($_GET['rt_search']) ? sanitize_text_field(wp_unslash($_GET['rt_search'])) : '';
    $list_month=$full && isset($_GET['rt_list_month']) ? sanitize_text_field(wp_unslash($_GET['rt_list_month'])) : '';
    if ($list_month!=='' && !preg_match('/^\d{4}-(0[1-9]|1[0-2])$/',$list_month)) {$list_month='';}
    $where=array('1=1'); $args=array();
    if ($list_month!=='') {
        $start=DateTimeImmutable::createFromFormat('!Y-m-d',$list_month.'-01');
        $where[]='fch_reclamo >= %s AND fch_reclamo < %s';
        $args[]=$list_month.'-01';
        $args[]=$start->modify('+1 month')->format('Y-m-d');
    }
    if ($status!=='') {$where[]='COALESCE(estado,1) = %d';$args[]=(int)$status;}
    if ($search!=='') {
        $where[]='(nombre LIKE %s OR apellido_paterno LIKE %s OR email LIKE %s OR CAST(libro_id AS CHAR) LIKE %s)';
        $like='%'.$wpdb->esc_like($search).'%';
        $args=array_merge($args,array_fill(0,4,$like));
    }
    $cond=implode(' AND ',$where);
    $stat_sql="SELECT COUNT(*) AS total, SUM(CASE WHEN COALESCE(estado,1)=1 THEN 1 ELSE 0 END) AS pending, SUM(CASE WHEN estado=2 THEN 1 ELSE 0 END) AS reviewing, SUM(CASE WHEN estado=3 THEN 1 ELSE 0 END) AS answered FROM $table WHERE $cond";
    $stats=(array)$wpdb->get_row($args?$wpdb->prepare($stat_sql,$args):$stat_sql,ARRAY_A);
    $stats=array_map('intval',$stats);
    $total=$stats['total']??0; $pending=$stats['pending']??0; $review=$stats['reviewing']??0; $answered=$stats['answered']??0;
    echo '<div class="rtlr-admin">';
    if ($full) {
       echo '<h2 class="rtlr-heading">'.esc_html__('Panel de reclamaciones','rt-libro').'</h2>';
       echo '<p class="rtlr-info">'.esc_html__('Los contadores y el listado usan los mismos filtros. Al ingresar se muestran todos los reclamos.','rt-libro').'</p>';
    } else { echo '<p class="rtlr-info">'.esc_html__('Totales generales de reclamaciones','rt-libro').'</p>'; }
    echo '<div class="rtlr-cards">';
    foreach(array(array('total',__('Total','rt-libro'),$total),array('1',__('Pendientes','rt-libro'),$pending),array('2',__('En revisión','rt-libro'),$review),array('3',__('Respondidos','rt-libro'),$answered)) as $card) {
        $params=$full?array_filter(array('rt_list_month'=>$list_month,'rt_search'=>$search)):array();
        if ($card[0]!=='total') {$params['rt_status']=$card[0];}
        echo '<a class="rtlr-card" href="'.esc_url(rt_libro_lrq_admin_url($params)).'"><span>'.esc_html($card[1]).'</span><strong>'.(int)$card[2].'</strong><span>'.esc_html__('Ver registros →','rt-libro').'</span></a>';
    }
    echo '</div>';
    // Always evaluate unresolved claims across all months; do not hide overdue records by month filter.
    $open=$wpdb->get_results("SELECT libro_id, fch_reclamo FROM $table WHERE COALESCE(estado,1) IN (1,2) ORDER BY fch_reclamo ASC",ARRAY_A);
    $soon=0; $overdue=0;
    foreach($open as $claim) {$left=rt_libro_lrq_business_days_left($claim['fch_reclamo']);if($left!==null){if($left<0){$overdue++;}elseif($left<=3){$soon++;}}}
    if($soon||$overdue){echo '<div class="notice notice-warning inline"><p><strong>'.esc_html__('Reclamos que requieren seguimiento','rt-libro').':</strong> '.sprintf(esc_html__('%1$d vencidos y %2$d por vencer (estimación sin feriados oficiales configurados).','rt-libro'),$overdue,$soon).'</p></div>';}
    if (!$full) {
        echo '<p><a class="button button-primary" href="'.esc_url(rt_libro_lrq_admin_url()).'">'.esc_html__('Ver todos los reclamos','rt-libro').'</a></p>';
        echo '<div class="rtlr-widget-footer">'.esc_html__('Desarrollado por', 'rt-libro').' <a href="https://renzotejada.com/" target="_blank" rel="noopener noreferrer">Renzo Tejada</a>';
        if (!rt_libro_lrq_pro_active()) {
            echo ' <span aria-hidden="true">·</span> <a target="_blank" rel="noopener noreferrer" href="'.esc_url(rt_libro_lrq_marketing_url('https://renzotejada.com/plugins/libro-de-reclamaciones-y-quejas-pro/', 'dashboard_widget_pro')).'">'.esc_html__('Conoce la versión PRO', 'rt-libro').'</a>';
        }
        echo '</div></div>';
        return;
    }

    $per_page=15; $page=max(1,absint($_GET['paged']??1));
    $count=(int)$wpdb->get_var(($args ? $wpdb->prepare("SELECT COUNT(*) FROM $table WHERE $cond",$args) : "SELECT COUNT(*) FROM $table WHERE $cond"));
    $query="SELECT libro_id,nombre,apellido_paterno,email,tipo_reclamacion,fch_reclamo,estado FROM $table WHERE $cond ORDER BY fch_reclamo DESC,libro_id DESC LIMIT %d OFFSET %d";
    $rows=$wpdb->get_results($wpdb->prepare($query,array_merge($args,array($per_page,($page-1)*$per_page))),ARRAY_A);
    echo '<h2 class="rtlr-heading">'.esc_html__('Todos los reclamos','rt-libro').'</h2>';
    echo '<form method="get" class="rtlr-bar"><input type="hidden" name="page" value="libro_settings"><input type="search" name="rt_search" placeholder="'.esc_attr__('Buscar código, nombre o correo','rt-libro').'" value="'.esc_attr($search).'"><select name="rt_status"><option value="">'.esc_html__('Todos los estados','rt-libro').'</option>';
    foreach(array(1,2,3) as $id){echo '<option value="'.$id.'" '.selected((string)$id,$status,false).'>'.esc_html(rt_libro_lrq_status_text($id)).'</option>';}
    echo '</select><label>'.esc_html__('Mes del listado','rt-libro').' <input type="month" name="rt_list_month" value="'.esc_attr($list_month).'" placeholder="AAAA-MM"></label><button class="button button-primary">'.esc_html__('Aplicar filtros','rt-libro').'</button><a class="button" href="'.esc_url(rt_libro_lrq_admin_url()).'">'.esc_html__('Ver todos / Limpiar filtros','rt-libro').'</a></form>';
    echo '<p class="rtlr-info">'.sprintf(esc_html__('%d registros encontrados. Los plazos son estimados: configure feriados nacionales mediante el filtro rt_libro_peru_holidays.','rt-libro'),$count).'</p>';
    echo '<div class="rtlr-list"><table><thead><tr><th>'.esc_html__('Código / fecha','rt-libro').'</th><th>'.esc_html__('Consumidor','rt-libro').'</th><th>'.esc_html__('Tipo','rt-libro').'</th><th>'.esc_html__('Estado','rt-libro').'</th><th>'.esc_html__('Plazo estimado','rt-libro').'</th><th>'.esc_html__('Acciones','rt-libro').'</th></tr></thead><tbody>';
    foreach($rows as $r){$state=(int)$r['estado'];$left=$state===3?null:rt_libro_lrq_business_days_left($r['fch_reclamo']);
        $deadline=$state===3?__('Respondido','rt-libro'):($left===null?__('Sin fecha','rt-libro'):($left<0?sprintf(__('Vencido %d días hábiles','rt-libro'),abs($left)):($left===0?__('Vence hoy','rt-libro'):sprintf(__('Faltan %d días hábiles','rt-libro'),$left))));
        $pill=$state===3?'ok':($left!==null&&$left<0?'danger':($left!==null&&$left<=3?'warn':''));
        echo '<tr><td><strong>#'.(int)$r['libro_id'].'</strong><br><small>'.esc_html($r['fch_reclamo']).'</small></td><td>'.esc_html(trim($r['nombre'].' '.$r['apellido_paterno'])).'<br><small>'.esc_html($r['email']).'</small></td><td>'.esc_html((int)$r['tipo_reclamacion']===2?__('Queja','rt-libro'):__('Reclamo','rt-libro')).'</td><td><span class="rtlr-pill">'.esc_html(rt_libro_lrq_status_text($state)).'</span></td><td><span class="rtlr-pill '.esc_attr($pill).'">'.esc_html($deadline).'</span></td><td><a class="button" href="'.esc_url(rt_libro_lrq_admin_url(array('ver'=>$r['libro_id']))).'">'.esc_html__('Ver detalle','rt-libro').'</a></td></tr>';
    }
    if(!$rows){echo '<tr><td colspan="6">'.esc_html__('No hay reclamos para los filtros seleccionados.','rt-libro').'</td></tr>';}
    echo '</tbody></table></div>';
    if($count>$per_page){$links=paginate_links(array('base'=>add_query_arg('paged','%#%',rt_libro_lrq_admin_url(array('rt_list_month'=>$list_month,'rt_status'=>$status,'rt_search'=>$search))),'format'=>'','current'=>$page,'total'=>(int)ceil($count/$per_page),'type'=>'list'));echo '<div class="tablenav"><div class="tablenav-pages">'.wp_kses_post($links).'</div></div>';}
    echo '</div>';
}

function rt_libro_lrq_render_status_form($id) {
    if(!current_user_can('manage_options')||!$id){return;}
    global $wpdb;$table=$wpdb->prefix.'rt_libro';
    $row=$wpdb->get_row($wpdb->prepare("SELECT estado,rt_responded_at FROM $table WHERE libro_id=%d",$id),ARRAY_A);
    if(!$row){return;}
    echo '<div class="rtlr-list" style="max-width:650px;padding:18px;margin-top:15px"><h3>'.esc_html__('Gestión del reclamo','rt-libro').'</h3><form method="post" action="'.esc_url(admin_url('admin-post.php')).'">';
    wp_nonce_field('rtlr_change_status');
    echo '<input type="hidden" name="action" value="rtlr_change_status"><input type="hidden" name="claim_id" value="'.(int)$id.'"><label>'.esc_html__('Estado administrativo','rt-libro').' <select name="new_status">';
    foreach(array(1,2,3) as $status){echo '<option value="'.$status.'" '.selected((int)$row['estado'],$status,false).'>'.esc_html(rt_libro_lrq_status_text($status)).'</option>';}
    echo '</select></label> <button type="submit" class="button button-primary">'.esc_html__('Guardar estado','rt-libro').'</button><p class="rtlr-info">'.esc_html__('Cambiar a Respondido registra la fecha interna de respuesta. No envía correo al consumidor; confirma que la respuesta fue enviada antes de utilizarlo.','rt-libro').'</p></form></div>';
}
