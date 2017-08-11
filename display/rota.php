<?php


/**
 *
 *  PDF using new rota table and sized to fit
 * 
 * @author  Andy Moyle
 * @param    $lengths, $fontSize
 * @return   array(orientation,font_size,widths)
 * @version  0.1
 * 
 */
function church_admin_front_end_rota($service_id=NULL,$limit=4,$pdfFontResize=TRUE,$date)
{
	global $wpdb;
	$days=array(1=>__('Sunday','church-admin'),2=>__('Monday','church-admin'),3=>__('Tuesday','church-admin'),4=>__('Wednesday','church-admin'),5=>__('Thursday','church-admin'),6=>__('Friday','church-admin'),7=>__('Saturday','church-admin'));
	$out='';
	//check for service_id
	if(!empty($_GET['service_id']))$service_id=intval($_GET['service_id']);
    if(empty($service_id))
    {
    	$services=$wpdb->get_results('SELECT * FROM '.CA_SER_TBL);
    	if($wpdb->num_rows==1)
    	{//only one service
			$service_id=intval($services[0]->service_id);
    	}//only one service
    	else
    	{//choose service
	
			$out.='<form action="" method="POST">';
			$out.='<p><label>'.__('Which Service?','church-admin').'</label><select name="service_id">';
			foreach($services AS $service)
			{
				$out.='<option value="'.intval($service->service_id).'">'.esc_html($service->service_name).' '.__('on','church-admin').' '.esc_html($days[$service->service_day]).' '.__('at','church-admin').' '.esc_html($service->service_time).' '.esc_html($service->venue).'</option>';
			}
			$out.='</select><input type="submit" name="choose_service" value="'.__('Choose service','church-admin').' &raquo;" /></p></form></div>';
    	}//choose service
    }
    if($service_id)
    {
	
		//get required rota tasks
	
		$rota_tasks=$wpdb->get_results('SELECT * FROM '.CA_RST_TBL.' ORDER BY rota_order');
		$requiredRotaJobs=$rotaDates=array();
		foreach($rota_tasks AS $rota_task)
		{
			$allServiceID=maybe_unserialize($rota_task->service_id);
			if(is_array($allServiceID)&&in_array($service_id,$allServiceID))$requiredRotaJobs[$rota_task->rota_id]=$rota_task->rota_task;
		}
		//get next four weeks of rota_jobs for each rota task
		//first grab next four weeks of services
		if(!empty($_POST['start_date'])&&church_admin_checkdate($_POST['start_date'])){$date='"'.esc_sql($_POST['start_date']).'"';}else{$date='CURDATE()';}
		$sql='SELECT * FROM '.CA_ROTA_TBL.' WHERE service_id="'.intval($service_id).'" AND mtg_type="service" AND rota_date>='.$date.' GROUP BY rota_date LIMIT '.$limit;
				$rotaDatesResults=$wpdb->get_results($sql);
		foreach($rotaDatesResults AS $rotaDatesRow)$rotaDates[]=$rotaDatesRow->rota_date;
		//grab people for each job and each date and populate $rota array
		$rota=array();
	
		foreach($rotaDatesResults AS $rotaDateRow)
		{
			//work through each row's column to find longest value
			foreach($requiredRotaJobs AS $rota_task_id=>$value)
			{
				$rota[$rota_task_id][$rotaDateRow->rota_date]=esc_html(church_admin_rota_people($rotaDateRow->rota_date,$rota_task_id,$service_id,'service'));
			
			}
		}

	
	
		//Title
		$service=$wpdb->get_row('SELECT * FROM '.CA_SER_TBL.' WHERE service_id="'.intval($service_id).'"');
		
		$out.='<h3>'.__('Rota for','church-admin').' '.esc_html($service->service_name).' '.__('on','church-admin').' '.esc_html($days[$service->service_day]).' '.__('at','church-admin').' '.esc_html($service->service_time).' '.esc_html($service->venue).'</h3>';
		$out.='<p><form action="" method="POST"><label>'.__('Choose Month','church-admin').'</label><select name="start_date">';
		for($x=0;$x<=12;$x++)
		{
			$out.='<option value="'.date('Y-m-d',strtotime($x.' month')).'"';
			if(!empty($_POST['start_date']) && $_POST['start_date']==date('Y-m-d',strtotime($x.' month')))$out.=' selected="selected" ';
			$out.='>'.date('M Y',strtotime($x.' month')).'</option>';
		}
		$out.='</select><input class="button-primary" type="submit" value="'.__('Choose','church-admin').'"/></form></p>';
		$out.='<p><a href="'.wp_nonce_url(site_url().'?download=rotacsv&amp;service_id='.intval($service_id),'rotacsv').'">'.__('Download Rota csv','church-admin').'</a></p>';
		if(!empty($_POST['start_date'])&&church_admin_checkdate($_POST['start_date'])){$url_date=$_POST['start_date'];}else{$url_date=date('Y-m-d');}
		$out.='<p><a href="'.wp_nonce_url(site_url().'?download=rota&amp;date='.$url_date.'&amp;service_id='.intval($service_id).'&amp;pdf_font_resize='.$pdfFontResize,'rota').'">'.__('Download Service Rota PDF','church-admin').'</a></p>';
		//table header
		$out.='<table class="church_admin">';
		$thead='<tr><th>'.__('Jobs','church-admin').'</th>';
	
		foreach($rotaDates AS $key=>$rota_date)
		{
		
			$thead.='<th>'.mysql2date(get_option('date_format'),$rota_date).'</th>';
		}
		$out.='<thead>'.$thead.'</thead><tfoot>'.$thead.'</tfoot><tbody>';
		//table data
	
		foreach($rota AS $rota_task_id=>$data)
		{
			//1st column is job
			$out.='<tr><th scope="row">'.esc_html($requiredRotaJobs[$rota_task_id]).'</th>';
			//rest of columns for that row
			foreach($data AS$date=>$value)
			{
				$out.='<td>'.esc_html($value).'</td>';
			}
			$out.='</tr>';
		}
		$out.='</tbody></table>';
	}
	return $out;
}

function church_admin_my_rota()
{

	global $wpdb;$current_user;
	$current_user = wp_get_current_user();
	
	$out='<h2>'.__('My rota','church-admin').'</h2>';
	
	if(empty($current_user->ID)	)
	{
		$out.='<p>'.__('You must be logged in','church-admin').'</p>';
		$out.=wp_login_form(array('echo' => false));
		
	}
	else
	{
		$people_id=$wpdb->get_var('SELECT people_id FROM '.CA_PEO_TBL.'  WHERE user_id="'.intval($current_user->ID).'"');
		if(empty($people_id))
		{
			$out='<p>'.__('Your login needs to be connected to someone in the Church Directory','church-admin').'</p>';
		}
		else
		{
			
			$sql='SELECT a.service_name,a.service_time, b.rota_task,c.rota_date FROM '.CA_SER_TBL.' a, '.CA_RST_TBL.' b, '.CA_ROTA_TBL.' c WHERE a.service_id=c.service_id AND c.mtg_type="service" AND c.rota_task_id=b.rota_id  AND c.people_id="'.intval($people_id).'" AND c.rota_date>=CURDATE()';
			
			$results=$wpdb->get_results($sql);
			if(!empty($results))
			{
				$out.='<table class="table table-bordered table-striped">';
				foreach($results AS  $row)
				{
					$out.='<tr><th scope="row">'.mysql2date(get_option('date_format'),$row->rota_date).' '.esc_html($row->service_name.' '.$row->service_time).'</th><td>'.esc_html($row->rota_task).'</td></tr>';
				}
				$out.='</table>';
			}
		
		}
	}
	return $out;
}
?>