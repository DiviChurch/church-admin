<?php

function church_admin_kidswork()
{
	global $wpdb;
	$out='<h1>'.__('Kids Work','church-admin').'</h1>';
	$out.='<h2 class="kidsworkpdf-toggle">'.__('Download a kids work PDF (Click to toggle)','church-admin').'</h2>';
	$out.='<div class="kidsworkpdf" style="display:none">';
	$out.= '<form name="kidswork_form" action="'.home_url().'" method="get"><input type="hidden" name="download" value="kidswork_pdf"/>';
	$out.='<table class="form-table">';
	$member_type=church_admin_member_type_array();
	foreach($member_type AS $key=>$value)
	{
		$out.='<tr><th scope="row">'.esc_html($value).'</th><td><input type="checkbox" value="'.esc_html($key).'" name="member_type_id[]"/></td></tr>';
	}
	
	$out.= '<tr><td colspacing=2>'.wp_nonce_field('kidswork','kidswork').'<input type="submit" class="button-primary" value="'.__('Download','church-admin').'"/></td></tr></table></form>';
	$out.='</div>';
	$out.='<script type="text/javascript">jQuery(function(){  jQuery(".kidsworkpdf-toggle").click(function(){jQuery(".kidsworkpdf").toggle();  });});</script>';
	
	$out.='<p><a class="button-primary" href="'.wp_nonce_url('admin.php?page=church_admin/index.php&tab=ministries&action=edit_kidswork','edit_kidswork').'">'.__('Add a kidswork age group','church-admin').'</a></p>';
	$out.='<p>'.__('The dates will go up a year on January 1st automatically.','church-admin');
	

	//autocorrect
	if(date('z')==0){$wpdb->query('UPDATE '.CA_KID_TBL.' SET youngest = youngest + INTERVAL 1 YEAR, oldest = oldest + INTERVAL 1 YEAR');}
	//get groups
	$results=$wpdb->get_results('SELECT a.*,b.ministry FROM '.CA_KID_TBL.' a  LEFT JOIN '.CA_MIN_TBL.' b ON a.department_id=b.ID ORDER BY youngest DESC');
	if(!empty($results))
	{
		
		$out.='<table class="widefat striped"><thead><tr><th>'.__('Edit','church-admin').'</th><th>'.__('Delete','church-admin').'</th><th>'.__('Group Name','church-admin').'</th><th>'.__('Led by','church-admin').'</th><th>'.__('Youngest','church-admin').'</th><th>'.__('Oldest','church-admin').'</th></tr></thead><tbody>';
		foreach($results AS $row)
		{
			$edit='<a href="'.wp_nonce_url('admin.php?page=church_admin/index.php&tab=ministries&action=edit_kidswork&id='.intval($row->id),'edit_kidswork').'">'.__('Edit','church-admin').'</a>';
			$delete='<a onclick="return confirm(\''.__('Are you sure?','church-admin').'\');" href="'.wp_nonce_url('admin.php?page=church_admin/index.php&tab=ministries&action=delete_kidswork&id='.intval($row->id),'delete_kidswork').'">'.__('Delete','church-admin').'</a>';
			$out.='<tr><td>'.$edit.'</td><td>'.$delete.'</td><td>'.esc_html($row->group_name).'</td><td>'.esc_html($row->ministry).'</td><td>'.mysql2date(get_option('date_format'),$row->youngest).'</td><td>'.mysql2date(get_option('date_format'),$row->oldest).'</td></tr>';
		}
		$out.='</table>';
	}
	
	echo $out;
}




function church_admin_delete_kidswork($id)
{
	global $wpdb;
	$wpdb->query('DELETE FROM '.CA_KID_TBL.' WHERE id="'.esc_sql($id).'"');
	echo'<div class="notice notice-success inline"><p><strong>'.__('Kidswork group deleted','church-admin').'</strong></p></div>';
		church_admin_kidswork();
}



function church_admin_edit_kidswork($id=NULL)
{

	global $wpdb;
	
	if(!empty($_POST['save']))
	{
		$sqlsafe=array();
		foreach($_POST AS $key=>$value)$sqlsafe[$key]=esc_sql(sanitize_text_field(stripslashes($value)));
		if(empty($id))$id=$wpdb->get_var('SELECT id FROM '.CA_KID_TBL.' WHERE group_name="'.$sqlsafe['group_name'].'" AND youngest="'.$sqlsafe['youngest'].'" AND oldest="'.$sqlsafe['oldest'].'" AND department_id="'.$sqlsafe['department_id'].'"');
		if(!empty($id))
		{//update
			$wpdb->query('UPDATE '.CA_KID_TBL.' SET group_name="'.$sqlsafe['group_name'].'" , youngest="'.$sqlsafe['youngest'].'" , oldest="'.$sqlsafe['oldest'].'" , department_id="'.$sqlsafe['department_id'].'" WHERE id="'.esc_sql($id).'"');
		}
		else
		{//insert
			$wpdb->query('INSERT INTO '.CA_KID_TBL.' (group_name,youngest,oldest,department_id)VALUES("'.$sqlsafe['group_name'].'","'.$sqlsafe['youngest'].'","'.$sqlsafe['oldest'].'","'.$sqlsafe['department_id'].'" )');
		}
		echo'<div class="notice notice-success inline"><p><strong>'.__('Kidswork updated','church-admin').'</strong></p></div>';
		church_admin_kidswork();
	
	}
	else
	{
		if(!empty($id))$data=$wpdb->get_row('SELECT * FROM '.CA_KID_TBL.' WHERE id="'.esc_sql($id).'"');
		echo'<h2>'.__('Add a kids work group','church-admin').'<form action="" method="POST">';
		echo'<table class="form-table"><tbody><tr><th scope="row">'.__('Group Name','church-admin').'</th><td><input type="text" name="group_name" id="group_name" ';
		if(!empty($data->group_name)) echo'value="'.esc_html($data->group_name).'"';
		echo'/></td></tr>';
		echo'<tr><th scope="row">'.__('Youngest','church-admin').'</th><td><input type=="text" name="youngest" class="youngest" ';
		if(!empty($data->youngest)&&$data->youngest!='0000-00-00') echo ' value="'.esc_html($data->youngest).'" ';
		echo'/></td></tr>';
		echo'<script type="text/javascript">
		jQuery(document).ready(function(){
			jQuery(\'.youngest\').datepicker({
            dateFormat : "yy-mm-dd", changeYear: true ,yearRange: "1910:'.date('Y').'"
         });
		});
		</script>';
		echo'<tr><th scope="row">'.__('Oldest','church-admin').'</th><td><input type=="text" name="oldest" class="oldest" ';
		if(!empty($data->oldest)&&$data->oldest!='0000-00-00') echo ' value="'.esc_html($data->oldest).'" ';
		echo'/></p>';
		echo'<script type="text/javascript">
		jQuery(document).ready(function(){
			jQuery(\'.oldest\').datepicker({
            dateFormat : "yy-mm-dd", changeYear: true ,yearRange: "1910:'.date('Y').'"
         });
		});
		</script>';
		echo'<tr><th scope="row">'.__('Led by people from ','church-admin').'</th><td>';
   		$ministries=church_admin_ministries();
   		
		if(!empty($ministries))
		{
			echo'<select name="department_id">';
			$first=$option='';
			foreach($ministries AS $ID=>$name) 
			{
				if(!empty($data->department_id) && $data->department_id==$ID) $first='<option selected="selected" value="'.intval($ID).'">'.esc_html($name).'</option>';
				$option.='<option value="'.intval($ID).'">'.esc_html($name).'</option>';
			}
			echo $first.$option;
			echo'</select>';
		}
		echo'</td></tr>';
		echo'<tr><th scope="row">&nbsp;</th><td><input type="hidden" name="save" value="yes"/><input type="submit" value="Save" class="button-primary"/></td></tr></tbody></table></form>';
		
	}
}

/**
 * 		Safeguarding main function
 *	
 *		Looks to see if safeguarding nation and which ministries require safeguardingare set up
 * 
 *
 *
 */
function church_admin_safeguarding_main()
{
	
	$out='<h1 class="safeguarding-toggle">'.__('Safeguarding (Click to toggle)','church-admin').'</h1>';
	$out.='<script type="text/javascript">jQuery(function(){  jQuery(".safeguarding-toggle").click(function(){jQuery(".safeguarding").toggle();  });});</script>';
	$out.='<div class="safeguarding" >';
	$out.=church_admin_safeguarding_nation();
	$nation=get_option('church_admin_safeguarding_nation');
	if(!empty($nation))$out.=church_admin_safeguarding_ministries();
	if(!empty($nation))$out.=church_admin_safeguarding_list();
	$out.='</div>';
	$out.='<script type="text/javascript">jQuery(function(){  jQuery(".safeguarding-toggle").click(function(){jQuery(".safeguarding").toggle();  });});</script>';
	
	
	
	echo $out;

}
/**
 * Choose which nation's safeguarding to follow
 *
 * 
 */
function church_admin_safeguarding_nation()
{
	
	if(!empty($_POST['save-nation']))
	{
		switch($_POST['save-nation'])
		{
			case 'Australia':$nation='Australia';break;
			case 'United Kingdom':$nation='United Kingdom';break;
		}
		if(!empty($nation))update_option('church_admin_safeguarding_nation',$nation);
		echo'<div class="notice notice-success">'.__('Nation saved','church-admin').'</div>';
	}
	$nation=get_option('church_admin_safeguarding_nation');
	if(empty($nation)){$safe_red='style="color:red" ';}else{$safe_red='';}
	$out='<h2 class="safe-nation-toggle" '.$safe_red.' >'.__("Choose which nation's safeguarding standards to use (Click to toggle)",'church-admin').'</h2>';
	$out.='<div class="safe-nation" style="display:none">';
	$out.='<p><a href="https://www.churchadminplugin.com/contact-us">'.__('Please let me know if your nation has child protection or safeguarding requirements for people working with children and vulnerable adults.','church-admin').'</a></p>';
	
	$nation=get_option('church_admin_safeguarding_nation');
	$out.='<form action="" method="POST">';
	$out.='<table class="form-table">';
	//Australia
	$out.='<tr><th scope="row">Australia</th><td><input type="radio" name="save-nation" value="Australia" ';
	if(!empty($nation) && $nation=='Australia')$out.=' checked="checked" ';
	$out.='/></td></tr>';
	//Australia
	$out.='<tr><th scope="row">United Kingdom</th><td><input type="radio" name="save-nation" value="United Kingdom" ';
	if(!empty($nation) && $nation=='United Kingdom')$out.=' checked="checked" ';
	$out.='/></td></tr>';
	$out.='<tr><td colspan="2"><input type="submit" name="submit" class="button-primary" value="'.__('Save','church-admin').'"/></td></tr>';
	$out.='</table></form>';
	$out.='</div>';
	$out.='<script type="text/javascript">jQuery(function(){  jQuery(".safe-nation-toggle").click(function(){jQuery(".safe-nation").toggle();  });});</script>';
	
	return $out;
}
/**
 * Adds people in safeguarding required ministries to the safeeguarding table
 *
 *
 */
function church_admin_populate_safeguarding()
{
	global $wpdb;
	$ministries=church_admin_safeguarded_ministries();
	
	if(empty($ministries))
	{
		$out='<p><a class="button-primary" href="'.wp_nonce_url('admin.php?page=church_admin/index.php&amp;action=edit_ministry&tab=ministries','edit_ministry').'">'.__('Please set up some ministries first','church-admin').'</a></p>'; 
	}
	else
	{//safe to proceed ministries available to choose
		$results=array();
		foreach($ministries AS $key=>$ministry_id)
		{
			$results=church_admin_people_meta($ministry_id,NULL,'ministry');
			
		
			//$results is $wpdb object with data array(people_id,ID)
			foreach($results AS $row)
			{
				$department_id=array();
				//check to see if people_id is in CP table
				$peopleData=$wpdb->get_row('SELECT people_id,department_id FROM '.CA_CP_TBL.' WHERE people_id="'.intval($row->people_id).'"');
				if(!empty($peopleData))$department_id=maybe_unserialize($peopleData->department_id);

				//put the ministry id ID into array
				if(!in_array($row->ID,$department_id))$department_id[]=$row->ID;
				$serializedDepartmentArray=serialize($department_id);
				if(empty($peopleData->people_id))
				{
					//create new record
					$wpdb->query('INSERT INTO '.CA_CP_TBL.' (people_id,department_id)VALUES("'.intval($row->people_id).'","'.esc_sql($serializedDepartmentArray).'")');
				}
				else
				{
					$wpdb->query('UPDATE '.CA_CP_TBL.' SET department_id="'.esc_sql($serializedDepartmentArray).'" WHERE people_id="'.intval($row->people_id).'"');
				}
			}
		}
	}//safe to proceeed, ministries available
}
/**
 * Safeguarding required ministries people list table
 *
 * @return $out
 *
 */
function church_admin_safeguarding_list()
{
	global $wpdb;
	//make sure list is populated
	church_admin_populate_safeguarding();
	$nation=get_option('church_admin_safeguarding_nation');
	$out='<h2>'.__('People in Safeguarding required ministries','church-admin').' ('.esc_html($nation).')</h2>';
	
	$sql='SELECT CONCAT_WS(" ",a.first_name,a.prefix,a.last_name) AS name,b.* FROM '.CA_PEO_TBL.' a, '.CA_CP_TBL.' b WHERE b.people_id=a.people_id ORDER BY a.last_name,a.first_name';
	$results=$wpdb->get_results($sql);
	
	if(!empty($results))
	{//not empty
	
		switch($nation)
		{
			case'Australia': $out.=church_admin_australia_safeguarding_table($results);break;
			case'United Kingdom': $out.=church_admin_uk_safeguarding_table($results);break;
			default:$out.=church_admin_australia_safeguarding_table($results);break;
		}
		
	}//end not empty
	else
	{//no results
		$out.='<p>'.__('No people in safeguarding required ministries yet.','church-admin').'</p>';
	}//end no results	
	
	return $out;
}
/**
 * Returns people table for United Kingdom
 *
 * @return $out array
 *
 */
function church_admin_uk_safeguarding_table($results)
{
		$CAministries=church_admin_ministries();
		$out='<table class="widefat striped">
		<thead>
			<tr>
				<th>Edit</th>
				<th>'.__('Name','church-admin').'</th>
				<th>'.__('Position','church-admin').'</th>
				<th>'.__('Volunteer/Paid','church-admin').'</th>
				<th>'.__('Start Date','church-admin').'</th>
				<th>'.__('Status and Action','church-admin').'</th>
				<th>'.'DBS Number'.'</th>
				<th>'.'DBS Date?'.'</th>
				<th>'.__('Review Date','church-admin').'</th>
			</tr>
		</thead>
		<tfoot>
			<tr>
				<th>Edit</th>
				<th>'.__('Name','church-admin').'</th>
				<th>'.__('Position','church-admin').'</th>
				<th>'.__('Volunteer/Paid','church-admin').'</th>
				<th>'.__('Start Date','church-admin').'</th>
				<th>'.__('Status and Action','church-admin').'</th>
				<th>'.'DBS Number'.'</th>
				<th>'.'DBS Date?'.'</th>
				<th>'.__('Review Date','church-admin').'</th>
			</tr>
		</foot>
		<tbody>';
		foreach($results AS $row)
		{//build table
			$edit='<a href="'.wp_nonce_url('admin.php?page=church_admin/index.php&amp;action=edit_safeguarding&amp;people_id='.intval($row->people_id),'edit_safeguarding').'">'.__('Edit','church-admin').'</a>';
			$ministries=unserialize($row->department_id);
			$mins=array();
			foreach($ministries AS $key=>$ministry_id) $mins[]=$CAministries[$ministry_id];
			$out.='
			<tr>
				<td>'.$edit.'</td>
				<td>'.esc_html($row->name).'</td>
				<td>'.esc_html(implode(", ",$mins)).'</td>
				<td>'.esc_html($row->employment_status).'</td>
				<td>'.mysql2date(get_option('date_format'),$row->start_date).'</td>
				<td>'.esc_html($row->status).'</td>
				<td>'.esc_html($row->DBS).'</td>
				<td>'.mysql2date(get_option('date_format'),$row->DBS_date).'</td>
				<td>'.mysql2date(get_option('date_format'),$row->review_date).'</td>
			</tr>';
		}//build table
		$out.='</tbody></table>';
		
		return $out;
}

/**
 * Returns people table for Australia
 *
 * @return $out array
 *
 */
function church_admin_australia_safeguarding_table($results)
{
		$CAministries=church_admin_ministries();
		$out='<table class="widefat striped">
		<thead>
			<tr>
				<th>Edit</th>
				<th>'.__('Name','church-admin').'</th>
				<th>'.__('Position','church-admin').'</th>
				<th>'.__('Volunteer/Paid','church-admin').'</th>
				<th>'.__('Start Date','church-admin').'</th>
				<th>'.'CRW category'.'</th>
				<th>'.__('Exemption Applied - Why?','church-admin').'</th>
				<th>'.__('Status and Action','church-admin').'</th>
				<th>'.__('Receipt number (if appl.)','church-admin').'</th>
				<th>'.'WWC Card Number'.'</th>
				<th>'.__('Expiry Date','church-admin').'</th>
				<th>'.__('Review Date','church-admin').'</th>
				<th>'.__('Validation Dates','church-admin').'</th>
			</tr>
		</thead>
		<tfoot>
			<tr>
				<th>Edit</th>
				<th>'.__('Name','church-admin').'</th>
				<th>'.__('Position','church-admin').'</th>
				<th>'.__('Volunteer/Paid','church-admin').'</th>
				<th>'.__('Start Date','church-admin').'</th>
				<th>'.'CRW category'.'</th>
				<th>'.__('Exemption Applied - Why?','church-admin').'</th>
				<th>'.__('Status and Action','church-admin').'</th>
				<th>'.__('Receipt number (if appl.)','church-admin').'</th>
				<th>'.'WWC Card Number'.'</th>
				<th>'.__('Expiry Date','church-admin').'</th>
				<th>'.__('Review Date','church-admin').'</th>
				<th>'.__('Validation Dates','church-admin').'</th>
			</tr>
		</foot>
		<tbody>';
		foreach($results AS $row)
		{//build table
			$edit='<a href="'.wp_nonce_url('admin.php?page=church_admin/index.php&amp;action=edit_safeguarding&amp;people_id='.intval($row->people_id),'edit_safeguarding').'">'.__('Edit','church-admin').'</a>';
			$ministries=unserialize($row->department_id);
			$mins=array();
			foreach($ministries AS $key=>$ministry_id) $mins[]=$CAministries[$ministry_id];
			$out.='
			<tr>
				<td>'.$edit.'</td>
				<td>'.esc_html($row->name).'</td>
				<td>'.esc_html(implode(", ",$mins)).'</td>
				<td>'.esc_html($row->employment_status).'</td>
				<td>'.mysql2date(get_option('date_format'),$row->start_date).'</td>
				<td>'.esc_html($row->CRW_cat).'</td>
				<td>'.esc_html($row->exemptions).'</td>
				<td>'.esc_html($row->status).'</td>
				<td>'.esc_html($row->receipt).'</td>
				<td>'.esc_html($row->WWC_card).'</td>
				<td>'.mysql2date(get_option('date_format'),$row->WWC_expiry).'</td>
				<td>'.mysql2date(get_option('date_format'),$row->review_date).'</td>
				<td>'.mysql2date(get_option('date_format'),$row->validation_date).'</td>
			</tr>';
		}//build table
		$out.='</tbody></table>';
		
		return $out;
}

/**
 * Returns array of safeguarding required ministries.
 *
 * @return $out array
 *
 */
function church_admin_safeguarded_ministries()
{
	global $wpdb;
	$results=$wpdb->get_results('SELECT ID FROM '.CA_MIN_TBL.' WHERE safeguarding=1');
	if(!empty($results))
	{
		$out=array();
		foreach($results AS $row)$out[]=$row->ID;
		return $out;
	}
	else return FALSE;
}


function church_admin_safeguarding_ministries()
{
	global $wpdb;
	
	
	
	
	$ministries=$wpdb->get_results('SELECT * FROM '.CA_MIN_TBL.' ORDER BY ministry');
	if(!empty($_POST['safemin']))
	{
	
		foreach($ministries AS $ministry)
		{
			$postedTitle=sanitize_title($ministry->ministry);
			if(!empty($_POST[$postedTitle]))
			{
				$wpdb->query('UPDATE '.CA_MIN_TBL.' SET safeguarding=1 WHERE ID="'.intval($ministry->ID).'"'); 
			}
			else
			{
				$wpdb->query('UPDATE '.CA_MIN_TBL.' SET safeguarding=0 WHERE ID="'.intval($ministry->ID).'"'); 
			}
		}	
	}
	$out='<h2 class="safeguardingministries-toggle">'.__('Which ministries require safeguarding? (click to toggle)','church-admin').'</h2>';
	$out.='<script type="text/javascript">jQuery(function(){  jQuery(".safeguardingministries-toggle").click(function(){jQuery(".safeguardingministries").toggle();  });});</script>';
	$out.='<div class="safeguardingministries" ';
	if(empty($_POST['safemin']))$out.=' style="display:none" ';
	$out.='>';

	$ministries=$wpdb->get_results('SELECT * FROM '.CA_MIN_TBL.' ORDER BY ministry');
	if(!empty($ministries))
	{
		$out.='<form action="" method="POST">';
		$out.='<table class="form-table">';
		foreach($ministries AS $ministry)
		{
			$out.='<tr><th scope="row">'.esc_html($ministry->ministry).'</th><td><input type="checkbox" name="'.esc_html(sanitize_title($ministry->ministry)).'" value=1';
			if(!empty($ministry->safeguarding))$out.=' checked="checked" ';
			$out.='/></td></tr>';
		}
		$out.='<tr><td colspan="2"><input type="hidden" name="safemin" value=1/><input type="submit" name="submit" class="button-primary" value="'.__('Save','church-admin').'"/></td></tr>';
		$out.='</table></form>';
	}
	else{$out.='<p>'.__('Please set up some ministries first','church-admin').'</p>';}
	$out.='</div>';	
	return $out;
}

function church_admin_edit_safeguarding($people_id)
{
	global $wpdb;
	$wpdb->show_errors;
	$nation=get_option('church_admin_safeguarding_nation');
	
	switch($nation)
	{
		case 'Australia':	
			$status=array('Holds current WWC Card','Application lodged','Interim negative notice','Negative notice','Application Withdrawn');
		break;
		case 'United Kingdom':
			$status=array('DBS Applied for','DBS Clear','DBS not clear');
		break;
	}
	$ministries=church_admin_ministries();
	$safeguardedMinistries=church_admin_safeguarded_ministries();
	
	$out='';
	switch($nation)
	{
	 	case 'Australia':
	 	
	 		$fields=array(
	 			
	 			'department_id'=>__('Ministry','church_admin'),
	 			'employment_status'=>__('Employment Status','church-admin'),
	 			'start_date'=>__('Start date','church-admin'),
	 			'CRW_cat'=>'CRW Cat',
	 			'exemptions'=>__('Exemptions','church-admin'),
	 			'status'=>__('Status and Action','church-admin'),
	 			'receipt'=>__('Receipt','church-admin'),
	 			'WWC_card'=>'WWC Card',
	 			'WWC_expiry'=>'WWC Expiry',
	 			'review_date'=>__('Review Date','church-admin'),
	 			'validation_date'=>__('Validation Date','church-admin'),
	 		);
	 	break;
	 	case 'United Kingdom':
	 	
	 		$fields=array(
	 			
	 			'department_id'=>__('Ministry','church_admin'),
	 			'employment_status'=>__('Employment Status','church-admin'),
	 			'status'=>__('Status and Action','church-admin'),
	 			'start_date'=>__('Start date','church-admin'),
	 			'DBS'=>'DBS Number',
	 			'DBS_date'=>'DBS date',
	 			'review_date'=>__('Review Date','church-admin')
			);
		break;
	}
	
	if(!empty($_POST['save-person']))
	{//process
	
		$where=array('people_id'=>$people_id);
		$data=array();
		foreach($fields AS $col=>$title)
		{
			if(!empty($_POST[$col]))
			{
				if($col=='department_id')
				{	
					//delete all safeguarded ministries for that person
					foreach($safeguardedMinistries AS $key=>$ID)church_admin_delete_people_meta($ID,$people_id,'ministry');
					$dep=array();
					foreach($_POST['department_id'] AS $key=>$value)
					{
						$dep[]=$value;

						//re-add the saved ones!
						if(!empty($value)){church_admin_update_people_meta($value,$people_id,'ministry');}
					}
					$data['department_id']=serialize($dep);//serialised array value for department_id field
				}
				else $data[$col]=stripslashes($_POST[$col]);
			}
		}
		
		$wpdb->update( CA_CP_TBL, $data, $where, '%s', NULL );
		$out.='<div class="notice notice-success inline">'.__('Record Updated','church-admin').'</div>';
		$out.=church_admin_safeguarding_list();
	}//end process
	else
	{
		$sql='SELECT CONCAT_WS(" ",a.first_name,a.prefix,a.last_name) AS name,b.* FROM '.CA_PEO_TBL.' a, '.CA_CP_TBL.' b WHERE b.people_id=a.people_id AND a.people_id="'.intval($people_id).'"';
		$row=$wpdb->get_row($sql);
		$out.='<h2>'.__('Edit "Working With Childen" data for ','church-admin').esc_html($row->name).'</h2>';
		$out.='<form action="" method="POST"><table class="form-table">';
		foreach($fields AS $col=>$title)
		{
			if($col!='people_id')
			{
				$out.='<tr><th scope="row">'.$title.'</th><td>';
				//handle different types of fields
				switch($col)
				{
					case 'start_date':
					case 'WWC_expiry':
					case 'review_date':
					case 'validation_date':
					case 'DBS_date':
						$out.=church_admin_date_picker($row->$col,$col,FALSE,date('Y',strtotime('-20 years')),NULL);
					break;
					case 'employment_status':
						$out.='<p><input type="radio" name="employment_status" value="Volunteer" ';
						if(!empty($row->employment_status)&&$row->employment_status==__('Volunteer','church_admin'))$out.=' checked="checked" ';
						$out.='/> <label>'.__('Volunteer','church-admin').'</label></p>';
						$out.='<p><input type="radio" name="employment_status" value="Paid" ';
						if(!empty($row->employment_status)&&$row->employment_status==__('Paid','church_admin'))$out.=' checked="checked" ';
						$out.='/> <label>'.__('Paid','church-admin').'</label></p>';
					break;
					case 'status':
						foreach($status AS $key=>$value)
						{
							$out.='<p><input type="radio" name="status" value="'.esc_html($value).'"';
							if(!empty($row->status)&&$row->status==$value)$out.=' checked="checked" ';
							$out.='/> <label>'.esc_html($value).'</label></p>';
						}
					break;
					case 'department_id':
						$department_id=maybe_unserialize($row->department_id);
						
						$safe_ministries=church_admin_safeguarded_ministries();
						
						foreach($safe_ministries AS $key=>$id)
						{
							$out.='<p><input type="checkbox" name="department_id[]" value="'.intval($id).'"';
							if(in_array($id,$department_id))$out.=' checked="checked" ';
							$out.='/> <label>'.$ministries[$id].'</label></p>';
						}
					break;
					default:
						$out.='<input type="text" name="'.$col.'" ';
						if(!empty($row->$col))$out.=' value="'.esc_html($row->$col).'" ';
						$out.='/>';
					break;
						
				}			
				$out.='</td></tr>';
			}
		}
		$out.='<tr><td colspacing=2><input type="hidden" name="save-person" value=yes/><input type="submit" class="button-primary" name="submit" value="'.__('Save','church-admin').'"/>';
		$out.='</table></form>';
	}
	echo $out;
}

?>