<?php
/*
2016-09-21 added debug info
*/

function church_admin_sessions($what=NULL,$what_id=NULL)
{
	
	global $wpdb,$current_user;

	wp_get_current_user();
	$people_id=$wpdb->get_var('SELECT people_id FROM '.CA_PEO_TBL.' WHERE user_id="'.intval($current_user->ID).'"');
	
	//church_admin_debug('******** church_admin_sessions function POST \r\n'.print_r($_POST,true));
	//church_admin_debug('******** church_admin_sessions functionGET \r\n'.print_r($_GET,true));
	$out='';
	//for now force just small groups as the session type!
	if(empty($_GET['what']))$_GET['what']='smallgroup';
	
	if(!empty($_GET['session_action']))$session_action=$_GET['session_action'];
	if(!empty($_GET['what']))$what=$args['what']=$_GET['what'];
	if(!empty($_GET['what_id']))$what_id=$args['what_id']=intval($_GET['what_id']);
	foreach($_GET AS $key=>$value){$args[$key]=$value;}
	
	if(!is_user_logged_in ())
	{//not logged in
		$out.='<h2>'.__('You must be logged in to use this feature','church-admin');
		$out.=wp_login_form();
	}
	else
	{//logged in
		
		if(!empty($what))
		{	
			switch($what)
			{
				case 'service':$what_session='service';$title=__('Services Session','church-admin');$label=__('Which service','church-admin');$sql='SELECT service_name AS name, service_id AS id FROM '.CA_SER_TBL;break;
				case'smallgroup':$what_session='smallgroup';$title=__('Small Group Session','church-admin');$label=__('Which small group','church-admin');$sql='SELECT group_name AS name, id,leadership FROM '.CA_SMG_TBL.' WHERE leadership!=""';break;
				default:$what_session='smallgroup';break;
			}
			if(empty($what_id)||!is_int($what_id))
			{//no group id
				$out.='<h2>'.$title.'</h2>';
				//begin code to only show authorised small groups
					
				$option=array();
				$results=$wpdb->get_results($sql);
				if(!empty($results))
				{//results
				
					
					foreach($results AS $row)
					{
						if(current_user_can('manage_options'))
						{
							$option[]='<option value="'.intval($row->id).'">'.esc_html($row->name).'</option>';
						}
						else
						{
							unset($leaders);
							$leaders=maybe_unserialize($row->leadership);
							if(!empty($leaders)&&is_array($leaders))
							{
								foreach($leaders AS $leaderlevel) 
								{
									if(!empty($people_id)&&in_array($people_id,$leaderlevel))	$option[]='<option value="'.intval($row->id).'">'.esc_html($row->name).'</option>';
								}
							}
						}
					}
					if(!empty($option))
					{//user leads a group
						$out.='<form action="'.$_SERVER['REQUEST_URI'].'" method="GET">';
						$out.='<table class="form-table">';
						$out.='<tr><th scope="row">'.$label.'</th><td><select name="what_id">'.implode('',array_filter($option)).'</select></td></tr>';
						foreach($args AS $key=>$value)$out.='<input type=hidden name="'.esc_html($key).'" value="'.esc_html($value).'"/>';
						$out.='<tr><td><input type="submit" class="button-primary" value="'.__('Choose','church-admin').'"/></td></tr></table></form>';
					}
					else
					{
						$out.='<p>'.__("Either you don't lead a group or you are not connected as a wordpress user to your directory entry",'church-admin').'</p>';
					
					}
				}//results
				
				else
				{//no small groups
					$out.='<p><a class="button-primary" href="'.wp_nonce_url("admin.php?page=church_admin/index.php&tab=small_groups&amp;action=edit_small_group",'edit_small_group').'">'.__('Add a small group first','church-admin').'</a></p>';
				}
			}//end no group id specified	
			else
			{//group specified
				//create main output
				$group=$wpdb->get_row('SELECT *  FROM '.CA_SMG_TBL.' WHERE id="'.intval($what_id).'"');
				if(!empty($_POST['ca_session_id']))church_admin_process_session($what,$what_id,$_POST['ca_session_id']);
				$out.='<h2>'.ucwords($group->group_name).' '.ucwords($what).' '.__('Session','church-admin').'</h2>';
				 $out.='<h3 ><a class="group-toggle">'.__('Group Details (Click to toggle)','church-admin').'</a> </h2>';
				$out.='<div class="group-details" style="display:none">';
				//show leaders
				$ldr='';
				$hierarchy=church_admin_get_hierarchy(1);
    			krsort($hierarchy);//sort top level down
    			//who is currently leading
    			$curr_leaders=maybe_unserialize($group->leadership);
    			//need titles of leaders levels
    			$ministries=church_admin_ministries(NULL);
    			foreach($hierarchy AS $key=>$min_id)
    			{
    				$ldr.='<h3>'.$ministries[$min_id].'</h3><p>';//leader level name
    				if(!empty($curr_leaders[$min_id])){foreach($curr_leaders[$min_id] AS $k=>$people_id)$ldr.=esc_html(church_admin_get_person($people_id)).'<br/>';}else{$ldr.='No leaders assigned yet<br/>';}
    		
    				$ldr.='</p>';
				}
				
				$out.=$ldr;
				//group members
				$out.='<h3>'.__('Group Members','church-admin').'</h3>';
				$people=$wpdb->get_results('SELECT CONCAT_WS(" ",a.first_name,a.prefix,a.last_name) AS name FROM '.CA_PEO_TBL.' a, '. CA_MET_TBL.' b WHERE a.people_id=b.people_id AND b.meta_type="smallgroup" AND b.ID="'.intval($what_id).'" ORDER by a.last_name');
				if(!empty($people))
				{
					$out.='<p>';
				 	foreach($people AS $person)$out.=esc_html($person->name).'<br/>';
				 	$out.='</p>';
				}			
				
				$out.='</div><script type="text/javascript">jQuery(function(){ jQuery(".group-toggle").click(function(){jQuery(".group-details").toggle(); });});</script>';
				
				
				
				$args['session_action']='new_session';
				church_admin_debug('***** just about to check $_POST[\'ca_session_id\']');
				church_admin_debug(print_r($_POST,true));
				if(!empty($_POST['ca_session_id']))
				{
					church_admin_debug('********\r\nchurch_admin_sessions\r\nca_session_id set in post\r\n');
					church_admin_debug(print_r($_POST,true));
					church_admin_debug(print_r($_GET,true));
					church_admin_debug('Calling church_admin_process_session');
					church_admin_process_session($what,$what_id);
					unset($session_action);
					church_admin_debug('********\r\nreturned from church_admin_process_session\r\n');
					if(!empty($session_action))church_admin_debug('$session_action:'. $session_action);
				}
				
				if(!empty($session_action))
				{
					switch($session_action)
					{
						case 'edit_session':$out.=church_admin_new_session($what,$what_id,intval($_REQUEST['ca_session_id']));break;
						case 'new_session':$out.=church_admin_new_session($what,$what_id,NULL);break;
						case 'delete_session':$wpdb->query('DELETE FROM '.CA_SES_TBL.' WHERE session_id="'.intval($_GET['ca_session_id']).'"');break;
					
					}
				}
				
				if(empty($session_action)||$session_action!='new_session')
				{
					church_admin_debug('Create output with no $session_action');
					$out.='<p><a class="button-primary" href="'.add_query_arg($args).'">'.__('Start new session','church-admin').'</a></p>';
					
					$out.=church_admin_session($_GET['what'],$_GET['what_id']);
				}
			}
		}//what not empty
		else
		{
			$out.='<form action="'.$_SERVER['REQUEST_URI'].'" method="GET">';
			$out.='<table class="form-table">';
			$out.='<tr><th scope="row">'.__('What kind of session?','church-admin').'</th><td><select name="what"><option value="service">'.__('Service','church-admin').'</option><option value="smallgroup">'.__('Smallgroup','church-admin').'</option></select></td></tr>';
			foreach($args AS $key=>$value)$out.='<input type=hidden name="'.esc_html($key).'" value="'.esc_html($value).'"/>';
			$out.='<tr><td><input type="submit" class="button-primary" value="'.__('Choose','church-admin').'"/></td></tr></table></form>';
			church_admin_debug('Form');
		}
		
	
	}//logged in
	return $out;
}


function church_admin_session($what,$what_id)
{
	
	global $wpdb,$current_user;
	wp_get_current_user();

	$args=array('what'=>$what,'what_id'=>$what_id);
	foreach($_GET AS $key=>$value){$args[$key]=$value;}
	$out='';
	
	
	
	$show=FALSE;
	if($what=='smallgroup')
	{
		$people_id=$wpdb->get_var('SELECT people_id FROM '.CA_PEO_TBL.' WHERE user_id="'.intval($current_user->ID).'"');
		
		$leaders=maybe_unserialize($wpdb->get_var('SELECT leadership FROM '.CA_SMG_TBL.' WHERE id="'.intval($what_id).'"'));
		
		foreach($leaders AS $leaderlevel) if(in_array($people_id,$leaderlevel))$show=TRUE;
		if(current_user_can('manage_options'))$show=true;
	}
	
	if($show)
	{
	
		$sql='SELECT * FROM '.CA_SES_TBL.' WHERE what="'.esc_sql($what).'" AND what_id="'.intval($what_id).'" ';
		
		$sql.='ORDER BY start_time DESC';
		$results=$wpdb->get_results($sql);
		if(!empty($results))
		{
			$out.='<table class="widefat striped"><thead><tr><th>'.__('Edit','church-admin').'</th><th>'.__('Delete','church-admin').'</th><th>'.__('Date','church-admin').'</th><th>'.__('Attendance','church-admin').'</th><th>'.__('Notes','church-admin').'</th><th>'.__('Edited by','church-admin').'</th></tr></thead><tfoot><tr><th>'.__('Edit','church-admin').'</th><th>'.__('Delete','church-admin').'</th><th>'.__('Date','church-admin').'</th><th>'.__('Attendance','church-admin').'</th><th>'.__('Notes','church-admin').'</th><th>'.__('Edited by','church-admin').'</th></tr></tfoot><tbody>';
			foreach($results AS $row)
			{
				$args['ca_session_id']=intval($row->session_id);
				$args['session_action']='edit_session';
				$edit='<p><a href="'.add_query_arg($args).'" class="button-primary" value="edit_session"/>'.__('Edit session','church-admin').'</a></p>';
				$args['session_action']='delete_session';
				$delete='<p><a onclick="return confirm(\''.__('Are you sure?','church-admin').'\')" href="'.add_query_arg($args).'" class="button-primary" value="delete_session"/>'.__('Delete session','church-admin').'</a></p></p>';
				
				$date=mysql2date(get_option('date_format').' '.get_option('time_format'),$row->start_time);
				$attended=$wpdb->get_var('SELECT COUNT(people_id) FROM '.CA_SMET_TBL.' WHERE session_id="'.intval($row->session_id).'" AND meta_value="attended" ');
				$not_attended=$wpdb->get_var('SELECT COUNT(people_id) FROM '.CA_SMET_TBL.' WHERE session_id="'.intval($row->session_id).'" AND meta_value="not_attended" ');
				$phoned=$wpdb->get_var('SELECT COUNT(people_id) FROM '.CA_SMET_TBL.' WHERE session_id="'.intval($row->session_id).'" AND meta_value="phoned" ');
				$attendance=$attended.' '.__('attended','church-admin').'<br/>'.$not_attended.' '.__(' not attended','church-admin').'<br/>'.$phoned.' '.__('phoned','church-admin').'<br/>';
				
				$edited_by='';
				if(!empty($row->user_id)&&is_array(maybe_unserialize($row->user_id)))foreach(unserialize($row->user_id) AS $time=>$person_id) $edited_by.= mysql2date(get_option('date_format').' '.get_option('time_format'),$time).': '.church_admin_get_person($person_id).'<br/>';
				$notes=$row->notes;
				$out.='<tr><td>'.$edit.'</td><td>'.$delete.'</td><td>'.$date.'</td><td>'.$attendance.'</td><td>'.$notes.'</td><td>'.$edited_by.'</td></tr>';
			}
			$out.='</tbody></table>';
		}
	}else{$out.='<p>'.__('You don\'t have access to this group','church-admin');}
		return $out;
	
}

function church_admin_new_session($what,$what_id,$ca_session_id=NULL)
{
	church_admin_debug('Firing church_admin_new_session');
	global $wpdb,$current_user;

	wp_get_current_user();
	$out='<h2>'.__('New session','church-admin').'</h2>'; // Edit by Jostein 14.03.2017
	if(empty($ca_session_id))
	{ 
		$wpdb->query('INSERT INTO '.CA_SES_TBL.' (what,what_id,start_time)VALUES("'.esc_sql($what).'","'.esc_sql($what_id).'","'.date('Y-m-d H:i:s').'")');
		$ca_session_id=$wpdb->insert_id;
	}
	$data=$wpdb->get_row('SELECT * FROM '.CA_SES_TBL.' WHERE session_id="'.intval($ca_session_id).'"');
	$out.='<p>'.__('Session started at','church-admin').' '.date(get_option('date_format').' '.get_option('time_format')).'</p>';
	$out.='<form action="'.$_SERVER['REQUEST_URI'].'" method="POST"><input type="hidden" name="what" value="'.esc_html($what).'"/><input type="hidden" name="what_id" value="'.intval($what_id).'"/><input type="hidden" name="ca_session_id" value="'.intval($ca_session_id).'"/>';
	$out.='<table class="form-table striped">';
	if($what='smallgroup')
	{
		$out.='<table >';
		$out.='<tr><th scope="row">'.__('Event Type','church-admin').'</th><td><select name="event_type"><option value="'.__('Contact group','church-admin').'">'.__('Contact group','church-admin').'</option><option value="'.__('Bible Study','church-admin').'">'.__('Bible Study','church-admin').'</option><option value="'.__('Small group meeting','church-admin').'">'.__('Small Group meeting','church-admin').'</option><option value="'.__('Social','church-admin').'">'.__('Social','church-admin').'</option></select></td></tr></table>';
		
		//attendance section
		$results=$wpdb->get_results('SELECT b.active, b.people_id,b.mobile,c.phone, b.first_name,b.nickname,b.prefix,b.last_name  FROM '.CA_MET_TBL.' a, '.CA_PEO_TBL.' b  LEFT JOIN '.CA_HOU_TBL.' c ON b.household_id=c.household_id WHERE a.ID="'.esc_sql($what_id).'" AND a.people_id=b.people_id AND a.meta_type="smallgroup"  ORDER BY b.last_name');
		if(!empty($results))
		{
			$out.='<table class="widefat fixed striped"><thead><tr><th>'.__('Name','church-admin').'</th><th>'.__('Mobile','church-admin').'</th><th>'.__('Phone','church-admin').'</th><th>'.__('Attended group','church-admin').'</th><th>'.__('Did not attend group','church-admin').'</th><th>'.__('Answered Phone call','church-admin').'</th><th>'.__('Delete from group','church-admin').'</th></tr></thead><tbody>';
			$x=0;
			foreach($results AS $row)
			{
				$person=$wpdb->get_row('SELECT * FROM '.CA_SMET_TBL.' WHERE session_id="'.intval($ca_session_id).'" AND people_id="'.intval($row->people_id).'"');
				if($row->active==0){$class=' class="ca-deactivated" ';}else{$class='';}
				$out.='<tr '.$class.'><th scope="row">';
				//build name
				$name=$row->first_name;
				$middle_name=get_option('church_admin_use_middle_name');
				if(!empty($middle_name)&&!empty($row->middle_name))$name.=' '.$row->middle_name.' ';
				$nickname=get_option('church_admin_use_nickname');
				if(!empty($nickname))$name.=' ('.$row->nickname.') ';
				$prefix=get_option('church_admin_use_prefix');
				if($prefix)	$name.=$row->prefix.' ';			
				$name.=$row->last_name;
				$out.=esc_html($name).'</th><td>'.esc_html($row->mobile).'</td><td>'.esc_html($row->phone).'</td>';
				$out.='<td><input title="'.__('Attended group','church-admin').'" type="radio" name="a'.intval($row->people_id).'" ';
				if(!empty($person->meta_value) && $person->meta_value=='attended') $out.=' checked="checked" ';
				$out.=' value="attended"/></td>';
				$out.='<td><input type="radio" name="a'.intval($row->people_id).'" value="not_attended" ';
				if(!empty($person->meta_value) && $person->meta_value=='not_attended') $out.=' checked="checked" ';
				$out.=' title="'.__('Did not attend group','church-admin').'"/></td>';
				$out.='<td><input type="radio" name="a'.intval($row->people_id).'" value="phoned" ';
				if(!empty($person->meta_value) && $person->meta_value=='phoned') $out.=' checked="checked" ';
				$out.='title="'.__('Answered Phone call','church-admin').'"/></td>';
				$out.='<td><input type="radio" name="a'.intval($row->people_id).'" value="delete" title="'.__('Delete from group','church-admin').'"/></td></tr>';
			}
			
		}
	}
	$out.='<tr><th scope"row">'.__('Notes','church-admin').'</th><td colspan="6"><textarea style="width:99%;height:200px" name="notes">';
	if(!empty($data->notes))$out.=esc_html($data->notes);
	$out.='</textarea></td></tr>';
	$out.='<tr><td colspan="7"><input type="submit" onclick="return confirm(\''.__('Are you sure?','church-admin').'\');"  class="button-primary" value="'.__('Save','church-admin').'"/></td></tr></tbody></table></form>';
	return $out;
}

function church_admin_process_session($what,$what_id)
{
	church_admin_debug('************** \r\nFiring church_admin_process_session\r\n');
	global $wpdb,$current_user;

	wp_get_current_user();
	$people_id=$wpdb->get_var('SELECT people_id FROM '.CA_PEO_TBL.' WHERE user_id="'.intval($current_user->ID).'"');
	if(empty($people_id))$people_id=$current_user->user_firstname.' '.$current_user->user_lastname;
	if(empty($people_id))$people_id=$current_user->email;
	$ca_session_id=(int)$_POST['ca_session_id'];
	//handle attendance
	$results=$wpdb->get_results('SELECT b.people_id FROM '.CA_MET_TBL.' a, '.CA_PEO_TBL.' b  WHERE  a.ID="'.esc_sql($what_id).'" AND a.people_id=b.people_id AND a.meta_type="smallgroup"');
	if(!empty($results))
	{
		$attendedCount=0;
		foreach($results AS $row)
		{
			$sql='';
			if(!empty($_POST['a'.$row->people_id]))
			{
				switch($_POST['a'.$row->people_id])
				{
					case 'attended': 
						$check=$wpdb->get_var('SELECT ID FROM '.CA_SMET_TBL.' WHERE people_id="'.intval($row->people_id).'" AND session_id="'.esc_sql($ca_session_id).'"');
						if($check){$sql='UPDATE `'.CA_SMET_TBL.'` SET meta_value="attended" WHERE people_id="'.intval($row->people_id).'" AND session_id="'.esc_sql($ca_session_id).'"'; }
						else{$sql='INSERT INTO '.CA_SMET_TBL.' (`meta_value`,`session_id`,`people_id`)VALUES("attended","'.esc_sql($ca_session_id).'","'.intval($row->people_id).'")';}
						$attendedCount++;
					break;
					case 'not_attended':
						$check=$wpdb->get_var('SELECT ID FROM '.CA_SMET_TBL.' WHERE people_id="'.intval($row->people_id).'" AND session_id="'.esc_sql($ca_session_id).'"');
						if($check){$sql='UPDATE `'.CA_SMET_TBL.'` SET meta_value="not_attended" WHERE people_id="'.intval($row->people_id).'" AND session_id="'.esc_sql($ca_session_id).'"'; }
						else{$sql='INSERT INTO '.CA_SMET_TBL.' (`meta_value`,`session_id`,`people_id`)VALUES("not_attended","'.esc_sql($ca_session_id).'","'.intval($row->people_id).'")';}
					break;
					case 'phoned': 
						$check=$wpdb->get_var('SELECT ID FROM '.CA_SMET_TBL.' WHERE people_id="'.intval($row->people_id).'" AND session_id="'.esc_sql($ca_session_id).'"');
						if($check){$sql='UPDATE `'.CA_SMET_TBL.'` SET meta_value="phoned" WHERE people_id="'.intval($row->people_id).'" AND session_id="'.esc_sql($ca_session_id).'"'; }
						else{$sql='INSERT INTO '.CA_SMET_TBL.' (`meta_value`,`session_id`,`people_id`)VALUES("phoned","'.esc_sql($ca_session_id).'","'.intval($row->people_id).'")';}
					break;
					case 'delete': $sql='UPDATE `'.CA_MET_TBL.'` SET ID="1" WHERE people_id="'.intval($row->people_id).'" AND meta_type="smallgroup"';break;
				}
			}
			church_admin_debug($sql.'\r\n');
			if(!empty($sql))$wpdb->query($sql);
		}
	}
	require_once(plugin_dir_path(__FILE__).'attendance.php');
	church_admin_save_attendance(NULL,date('Y-m-d'),'group',$what_id,$attendedCount,0);
	
	//handle notes
	$users=maybe_unserialize($wpdb->get_var('SELECT user_id FROM '.CA_SES_TBL.' WHERE session_id="'.esc_sql($ca_session_id).'"'));
	if(empty($users)||!is_array($users))$users=array();
	$users[date('Y-m-d H:i:s')]=$people_id;
	
	$notes=esc_sql(stripslashes(nl2br($_POST['notes'])));
	$type='';
	$type=esc_sql(sanitize_text_field($_POST['event_type']));
	
	$sql='UPDATE '.CA_SES_TBL.' SET event_type="'.$type.'", notes="'.$notes.'", user_id="'.esc_sql(serialize($users)).'", end_time="'.date('Y-m-d H:i:s').'" WHERE session_id="'.esc_sql($ca_session_id).'"';
	church_admin_debug($sql.'\r\n');
	$wpdb->query($sql);
	church_admin_debug('Finished church_admin_process_session \r\n');
}