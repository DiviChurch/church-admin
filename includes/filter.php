<?php

function church_admin_directory_filter($JSUse=TRUE)
{
	global $wpdb;
	$church_admin_marital_status=array(0=>__('N/A','church-admin'),1=>__('Single','church-admin'),2=>__('Co-habiting','church-admin'),3=>__('Married','church-admin'),4=>__('Divorced','church-admin'),5=>__('Widowed','church-admin'));
	if($JSUse)echo'<h2>'.__('Filtered Address List','church-admin').'</h2>';
	 
	if($JSUse)echo'<p><strong>'.__('Use the checkboxes to filter the address list you will see','church-admin').'</strong></p>';
	if($JSUse){echo'<div id="filters">';}else{echo'<div id="filters1">';}
	if($JSUse){$class='category';}else{$class='no-filter';}
	//gender
	$genders=get_option('church_admin_gender');
	echo'<div class="filterblock"><label>'.__('Gender','church-admin').'</label>';
	foreach($genders AS $key=>$gender)echo'<p><input type="checkbox" name="check[]" class="'.$class.' gender" value="ge/'.sanitize_title($gender).'" />'.esc_html($gender).'</p>';
	echo'</div>';
	//people types
	$people_types=get_option('church_admin_people_type');
	if(!empty($people_types))
	{
		echo'<div class="filterblock"><label>'.__('People Types','church-admin').'</label>';
		echo'<p><input type="checkbox" name="check[]" class="all '.$class.'" id="people" value="all"/><strong>'.__('All','church-admin').'</strong></p>';
		foreach($people_types AS $key=>$people_type)echo'<p><input type="checkbox" name="check[]" class="'.$class.' people" value="pe/'.sanitize_title($people_type).'" />'.esc_html($people_type).'</p>';
		echo'</div>';
	}
	
	//marital status
	echo'<div class="filterblock"><label>'.__('Marital Status','church-admin').'</label>';
	foreach($church_admin_marital_status AS $key=>$status){echo'<input  type="checkbox" name="check[]" class="'.$class.' marital" value="ma/'.sanitize_title($status).'" />'.esc_html($status).'</p>';}
	echo'</div>';
	//Sites
	$results=$wpdb->get_results('SELECT venue FROM '.CA_SIT_TBL.' ORDER BY venue ASC');
	if(!empty($results))
	{
		echo'<div class="filterblock"><label>'.__('Sites','church-admin').'</label>';
		echo'<p><input type="checkbox" name="check[]" class="all '.$class.'" id="sites" value="all"/><strong>'.__('All','church-admin').'</strong></p>';
		
		foreach($results AS $row)
		{
			
			echo'<p><input type="checkbox" name="check[]" class="'.$class.' sites" value="si/'.sanitize_title($row->venue).'" />'.esc_html($row->venue).'</p>';
			
		}
				echo'</div>';
	}
	//Member Types
	$results=$wpdb->get_results('SELECT member_type FROM '.CA_MTY_TBL.' ORDER BY member_type_order ASC');
	if(!empty($results))
	{
		echo'<div class="filterblock"><label>'.__('Member Types','church-admin').'</label>';
		echo'<p><input type="checkbox" name="check[]" class="all '.$class.'" id="member" value="all"/><strong>'.__('All','church-admin').'</strong></p>';
		foreach($results AS $mt)echo'<p><input  type="checkbox" name="check[]" class="'.$class.' member" value="mt/'.sanitize_title($mt->member_type).'" />'.esc_html($mt->member_type).'</p>';
		echo'</div>';
	}
	//Small Groups
	$results=$wpdb->get_results('SELECT group_name FROM '.CA_SMG_TBL.' ORDER BY group_name ASC');
	if(!empty($results))
	{
		echo'<div class="filterblock"><label>'.__('Small Groups','church-admin').'</label>';
		echo'<p><input type="checkbox" name="check[]" class="all '.$class.'" id="groups" value="all"/><strong>'.__('All','church-admin').'</strong></p>';
		echo'<p><input type="checkbox"name="check[]" class="'.$class.'" value="gp/no-group"/><strong>'.__('Not in a group (overrides other small group selections)','church-admin').'</strong></p>';
		echo'</p>';
		$x=0;
		foreach($results AS $row)
		{
			if($x==0) echo '<p>';
			echo'<span ><input type="checkbox" name="check[]" class="'.$class.' groups" value="gp/'.sanitize_title($row->group_name).'" />'.esc_html($row->group_name).'</span>';
			$x++;
			if($x==3){echo'<p>';$x=0;}
		}
				echo'</div>';
	}

	//Ministries
	$results=$wpdb->get_results('SELECT ministry FROM '.CA_MIN_TBL.' ORDER BY ministry ASC');
	if(!empty($results))
	{
		echo'<div class="filterblock"><label>'.__('Ministries','church-admin').'</label>';
		echo'<p><input type="checkbox" name="check[]" class="all '.$class.'" id="ministries" value="all"/><strong>'.__('All','church-admin').'</strong></p>';
		
		$x=0;
		foreach($results AS $row)
		{
			if($x==0) echo '<p>';
			echo'<span ><input type="checkbox" name="check[]" class="'.$class.' ministries" value="mi/'.sanitize_title($row->ministry).'" />'.esc_html($row->ministry).'</span>';
			$x++;
			if($x==3){echo'<p>';$x=0;}
		}
				echo'</div>';
	}
	/*
	//Small Group Attendance
	echo'<div class="filterblock"><label>'.__('Small Group Attendance (sessions)','church-admin').'</label>';
	echo'<p><span><input type="checkbox" name="check[]" class="'.$class.'"  value="at/attended"/>'.__('Attended','church-admin').'</span><span><input type="radio" name="time" class="'.$class.'"  value="ti/1week"/>'.__('Last week','church-admin').'</span></p>';
	echo'<p><span><input type="checkbox" name="check[]" class="'.$class.'"  value="at/not_attended"/>'.__('Not Attended','church-admin').'</span><span><input type="radio" name="time" class="'.$class.'"  value="ti/2week"/>'.__('Last fortnight','church-admin').'</span></p>';
	echo'<p><span><input type="checkbox" name="check[]" class="'.$class.'"  value="at/phoned"/>'.__('Phoned','church-admin').'</span><span><input type="radio" name="time" class="'.$class.'"  value="ti/month"/>'.__('Last month','church-admin').'</span></p>';
	echo'</div>';
	*/
	
	echo '</div>';
	$nonce = wp_create_nonce("church_admin_filter");
	if($JSUse)echo'
	
	<script type="text/javascript">
		jQuery(document).ready(function($) {
			$(".all").on("change", function(){
				var id = this.id;
				
				$("input."+id).prop("checked", !$("."+id).prop("checked"))
			});
		   $(".category").on("change", function(){

      			var category_list = [];
      			$("#filters :input:checked").each(function(){
        			
        			
        			var category = $(this).val();
        			category_list.push(category);
        			
        		});
      			
      			var data = {
				"action": "church_admin_filter",
				"data": category_list,
				"nonce": "'.$nonce.'"
				};
	$("#filtered-response").html(\'<p style="text-align:center"><img src="'.admin_url().'/images/wpspin_light-2x.gif"/></p>\');
		// since 2.8 ajaxurl is always defined in the admin header and points to admin-ajax.php
		jQuery.post(ajaxurl, data, function(response) {
			$("#filtered-response").html(response);
		});
			});
		});
	</script>
	<div id="filtered-response"></div>
	';

}



function church_admin_filter_process()
{
	//if changes made here also update email.php
	
	global $wpdb;
	$church_admin_marital_status=array(0=>__('N/A','church-admin'),1=>__('Single','church-admin'),2=>__('Co-habiting','church-admin'),3=>__('Married','church-admin'),4=>__('Divorced','church-admin'),5=>__('Widowed','church-admin'));
	$out='';
	$group_by='';
	$member_types=$genders=$people_types=$sites=$smallgroups=$ministries=array();
	$marritalSQL=$genderSQL=$memberSQL=$peopleSQL=$smallgroupsSQL=$ministriesSQL=$filteredby=array();
	foreach($_POST['data'] AS $key=>$data)
	{
		//extract posted data
		$temp=explode('/',$data);
		switch($temp[0])
		{
			case 'ma': $marital[]=stripslashes($temp[1]);			break;
			case 'ge': 	$genders[]=stripslashes($temp[1]);			break;
			case 'mt': 	$member_types[]=stripslashes($temp[1]);		break;
			case 'pe':	$people_types[]=stripslashes($temp[1]);		break;
			case 'si':	$sites[]=stripslashes($temp[1]);			break;
			case 'gp':	$smallgroups[]=stripslashes($temp[1]);		break;
			case 'mi':	$ministries[]=stripslashes($temp[1]);		break;
		}
	}
	//create clauses for different
	if(!empty($marital)&&is_array($marital))
	{
		foreach($church_admin_marital_status AS $key=>$status)
		{
			if(in_array(sanitize_title($status),$marital))$maritalSQL[]='a.marital_status="'.$status.'"';
		}
	}
	if(!empty($genders))
	{
	
		$sex=get_option('church_admin_gender');
		foreach($sex AS $key=>$gender)
		{
			
			if(in_array(sanitize_title($gender),$genders))
			{
				$genderSQL[]='(a.sex="'.intval($key).'")';
				$filteredby[]=$gender;
			}
		}	

	}
	
	//end gender section
	//member types
	if(!empty($member_types)&&is_array($member_types))			
	{	
		
		$allmembers=$wpdb->get_results('SELECT * FROM '.CA_MTY_TBL);
		
		if(!empty($allmembers))
		{
			foreach($allmembers AS $onetype)
			{
				//print_r($onetype);
				if(in_array(sanitize_title($onetype->member_type),$member_types))
				{
					$memberSQL[]='(a.member_type_id="'.$onetype->member_type_id.'" AND a.member_type_id=f.member_type_id)';
					$filteredby[]=$onetype->member_type;
				}
			}
		}
	}//end member_types
	
	//people types
	$ptypes=get_option('church_admin_people_type');
	if(!empty($people_types))
	{
	
		if(!in_array('all',$people_types))//only do if all not selected
		{
			$ptypes=get_option('church_admin_people_type');
			
			foreach($ptypes AS $key=>$ptype)
			{
				if(in_array(sanitize_title($ptype),$people_types))
				{
					$peopleSQL[]='(a.people_type_id="'.intval($key).'")';
					$filteredby[]=$ptype;
				}
			}	
		}
	}//end people type section
	
	//sites
	
	if(!empty($sites)&&is_array($sites))			
	{	
		if(!in_array('all',$sites))//only do if all not selected
		{
			$campuses=$wpdb->get_results('SELECT * FROM '.CA_SIT_TBL);
			
			if(!empty($campuses))
			{
				foreach($campuses AS $campus)
				{
					if(in_array(sanitize_title($campus->venue),$sites))
					{
						$sitesSQL[]='(a.site_id="'.intval($campus->site_id).'")';
						$filteredby[]=$campus->venue;
					}
				}
			}
		}	
	}//end sites
	
	//small groups
	
	if(!empty($smallgroups)&&is_array($smallgroups))			
	{	
		
		if(!in_array('all',$smallgroups))//only do if all not selected
		{
			$sgs=$wpdb->get_results('SELECT * FROM '.CA_SMG_TBL);
			if(!empty($sgs))
			{
				foreach($sgs AS $sg)
				{
					
					if(in_array(sanitize_title($sg->group_name),$smallgroups))
					{
						$smallgroupsSQL[]='(c.ID="'.intval($sg->id).'" AND c.meta_type="smallgroup" AND c.people_id=a.people_id)';
						$filteredby[]=$sg->group_name;
					}
				}
			}
		}
		if(in_array('no-group',$smallgroups))
		{
			 $smallgroupsSQL=array('a.people_id NOT IN (SELECT people_id FROM '.CA_MET_TBL.' WHERE meta_type="smallgroup")');
				}	
	}//end smallgroups

	
	//ministries
	if(!empty($ministries)&&is_array($ministries))			
	{	
		if(!in_array('all',$ministries))//only do if all not selected
		{
			$mins=$wpdb->get_results('SELECT * FROM '.CA_MIN_TBL);
			
			if(!empty($mins))
			{
				foreach($mins AS $min)
				{
					if(in_array(sanitize_title($min->ministry),$ministries))
					{
						$ministriesSQL[]='(c.ID="'.intval($min->ID).'" AND c.ID=g.ID AND c.meta_type="ministry" AND c.people_id=a.people_id)';
						$filteredby[]=$min->ministry;
					}
				}
			}
		}	
	}//end smallgroups
	$other=$tbls='';
	 $group_by=' GROUP BY a.people_id ';
	 $columns=array('a.people_id','a.household_id','a.first_name','a.middle_name','a.last_name','a.people_type_id','a.email','a.mobile','a.sex','b.phone','b.address','b.private','a.active','a.marital_status');
	$tables=array(CA_PEO_TBL.' a',CA_HOU_TBL.' b');
	$table_header=array(__('Edit','church-admin'),__('Delete','church-admin'),__('Activate','church-admin'),__('Name','church-admin'),__('People Type','church-admin'),__('Phone','church-admin'),__('Mobile','church-admin'),__('Email','church-admin'),__('Address','church-admin'));
	if(!empty($maritalSQL))		$other.=' AND ('. implode(" OR ",$maritalSQL).')';
	if(!empty($genderSQL)) 		$other.=' AND ('. implode(" OR ",$genderSQL).')';
	if(!empty($peopleSQL)) 		$other.=' AND ('. implode(" OR ",$peopleSQL).')';
	if(!empty($sitesSQL)) 		{
									$other.=' AND ('. implode(" OR ",$sitesSQL).') AND a.site_id=d.site_id';
									$tables[]=CA_SIT_TBL.' d';
									$columns[]='d.venue';
								}
	if(!empty($smallgroupsSQL)) 	{
									$other.=' AND ('. implode(" OR ",$smallgroupsSQL).') AND c.ID=e.id';
									$columns[]='e.group_name';
									$tables[]=CA_MET_TBL.' c'; 
									$tables[]=CA_SMG_TBL.' e';
								}
	if(!empty($memberSQL)) 		{
									$other.=' AND ('. implode(" OR ",$memberSQL).')';
									$columns[]='f.member_type';
									$tables[]=CA_MTY_TBL.' f';
								}
	if(!empty($ministriesSQL)) 	{
									$other.=' AND ('. implode(" OR",$ministriesSQL).')';
									$columns[]='g.ministry ';
									$tables['g']=CA_MIN_TBL.' g';
									$tables['c']=CA_MET_TBL.' c';
								}
	
	foreach($tables AS $letter=>$table)$tbls.=', '.$table.' '.$letter;
	
	$sql='SELECT '.implode(", ",$columns).' FROM '.implode(", ",array_filter($tables)).' WHERE a.household_id=b.household_id '.$other.' '.$group_by.' ORDER BY a.last_name';

	$results=$wpdb->get_results($sql);
	echo'<h2>'.__('Filtered by: ','church-admin').implode(', ',$filteredby).'</h2>';
	if(!empty($results))
	{
	
		echo'<table class="widefat striped">';
		echo'<thead><tr><th>'.implode('</th><th>',$table_header).'</th></tr></thead><tbody>';
		foreach($results AS $row)
		{
			$class=array();
			if(!empty($row->private))$class[]='ca-private';
			if(empty($row->active))$class[]='ca-deactivated';
			if(!empty($class)){$classes=' class="'.implode(" ",$class).'"';}else$classes='';
			echo '<tr '.$classes.' id="row'.intval($row->people_id).'"><td><a href="'.wp_nonce_url('admin.php?page=church_admin/index.php&amp;action=edit_people&amp;people_id='.$row->people_id.'&amp;household_id='.$row->household_id,'edit_people').'">'.__('Edit','church-admin').'</a></td>';
			echo'<td><a href="'.wp_nonce_url('admin.php?page=church_admin/index.php&amp;action=delete_people&amp;people_id='.$row->people_id.'&amp;household_id='.$row->household_id,'edit_people').'">'.__('Delete','church-admin').'</a></td>';
			if(!empty($row->active)){$activate=__('Deactivate','church-admin');}else{$activate=__('Activate','church-admin');}
			echo'<td class="activate" id="'.intval($row->people_id).'">'.$activate.'</td>';
			$name=array_filter(array($row->first_name,$row->middle_name,$row->prefix,$row->last_name));
			echo'<td>'.esc_html(implode(' ',$name)).'</td>';
			echo'<td>'.esc_html($ptypes[$row->people_type_id]).'</td>';
			echo'<td>'.esc_html($row->phone).'</td>';
			echo'<td>'.esc_html($row->mobile).'</td>';
			if(!empty($row->email)){echo'<td><a href="mailto:'.$row->email.'">'.esc_html($row->email).'</a></td>';}else{echo'<td>&nbsp;</td>';}
			echo'<td>'.esc_html($row->address).'</td></tr>';	
		
		}
		echo'</tbody></table>';
		//jQuery for processing activate/deactivate peopl
		$nonce = wp_create_nonce("church_admin_people_activate");
		echo'
	
	<script type="text/javascript">
		jQuery(document).ready(function($) {
			$(".activate").on("click", function(){
				var id = this.id;
			
      			var data = {
				"action": "church_admin_people_activate",
				"people_id": id,
				"nonce": "'.$nonce.'"
				};
	
		// since 2.8 ajaxurl is always defined in the admin header and points to admin-ajax.php
		jQuery.post(ajaxurl, data, function(response) {
		
			if(response==1){
					$("#row"+id).removeClass("ca-deactivated");
					$("#"+id).html("Deactivate");
				}else{
					$("#row"+id).addClass("ca-deactivated");
					$("#"+id).html("Activate");
				}
		});
			});
		});
	</script>
	
	';

	}else{echo'<p>'.__('Your filters produced no results. Please try again.','church-admin').'</p>';}
	
		
}


function church_admin_filter_email_count($type)
{
	//if changes made here also update email.php
	
	global $wpdb;
	$church_admin_marital_status=array(0=>__('N/A','church-admin'),1=>__('Single','church-admin'),2=>__('Co-habiting','church-admin'),3=>__('Married','church-admin'),4=>__('Divorced','church-admin'),5=>__('Widowed','church-admin'));
	$out='';
	$group_by='';
	$member_types=$genders=$people_types=$sites=$smallgroups=$ministries=array();
	$maritalSQL=$genderSQL=$memberSQL=$peopleSQL=$smallgroupsSQL=$ministriesSQL=$filteredby=array();
	foreach($_POST['data'] AS $key=>$data)
	{
		//extract posted data
		$temp=explode('/',$data);
		switch($temp[0])
		{
			case 'ma': 	$marital[]=stripslashes($temp[1]);			break;
			case 'ge': 	$genders[]=stripslashes($temp[1]);			break;
			case 'mt': 	$member_types[]=stripslashes($temp[1]);		break;
			case 'pe':	$people_types[]=stripslashes($temp[1]);		break;
			case 'si':	$sites[]=stripslashes($temp[1]);			break;
			case 'gp':	$smallgroups[]=stripslashes($temp[1]);		break;
			case 'mi':	$ministries[]=stripslashes($temp[1]);		break;
		}
	}
	//create clauses for different
	//create clauses for different
	if(!empty($marital)&&is_array($marital))
	{
		foreach($church_admin_marital_status AS $key=>$status)
		{
			if(in_array(sanitize_title($status),$marital))$maritalSQL[]='a.marital_status="'.$status.'"';
		}
	}
	if(!empty($genders))
	{
	
		$sex=get_option('church_admin_gender');
		foreach($sex AS $key=>$gender)
		{
			
			if(in_array(sanitize_title($gender),$genders))
			{
				$genderSQL[]='(a.sex="'.intval($key).'")';
				$filteredby[]=$gender;
			}
		}	

	}
	
	//end gender section
	//member types
	if(!empty($member_types)&&is_array($member_types))			
	{	
		
		$allmembers=$wpdb->get_results('SELECT * FROM '.CA_MTY_TBL);
		
		if(!empty($allmembers))
		{
			foreach($allmembers AS $onetype)
			{
				//print_r($onetype);
				if(in_array(sanitize_title($onetype->member_type),$member_types))
				{
					$memberSQL[]='(a.member_type_id="'.$onetype->member_type_id.'" ANd a.member_type_id=f.member_type_id)';
					$filteredby[]=$onetype->member_type;
				}
			}
		}
	}//end member_types
	
	//people types
	$ptypes=get_option('church_admin_people_type');
	if(!empty($people_types))
	{
	
		if(!in_array('all',$people_types))//only do if all not selected
		{
			$ptypes=get_option('church_admin_people_type');
			
			foreach($ptypes AS $key=>$ptype)
			{
				if(in_array(sanitize_title($ptype),$people_types))
				{
					$peopleSQL[]='(a.people_type_id="'.intval($key).'")';
					$filteredby[]=$ptype;
				}
			}	
		}
	}//end people type section
	
	//sites
	
	if(!empty($sites)&&is_array($sites))			
	{	
		if(!in_array('all',$sites))//only do if all not selected
		{
			$campuses=$wpdb->get_results('SELECT * FROM '.CA_SIT_TBL);
			
			if(!empty($campuses))
			{
				foreach($campuses AS $campus)
				{
					if(in_array(sanitize_title($campus->venue),$sites))
					{
						$sitesSQL[]='(a.site_id="'.intval($campus->site_id).'")';
						$filteredby[]=$campus->venue;
					}
				}
			}
		}	
	}//end sites
	
	//small groups
	
	if(!empty($smallgroups)&&is_array($smallgroups))			
	{	
		
		if(!in_array('all',$smallgroups))//only do if all not selected
		{
			$sgs=$wpdb->get_results('SELECT * FROM '.CA_SMG_TBL);
			if(!empty($sgs))
			{
				foreach($sgs AS $sg)
				{
					
					if(in_array(sanitize_title($sg->group_name),$smallgroups))
					{
						$smallgroupsSQL[]='(c.ID="'.intval($sg->id).'" AND c.meta_type="smallgroup" AND c.people_id=a.people_id)';
						$filteredby[]=$sg->group_name;
					}
				}
			}
		}
		if(in_array('no-group',$smallgroups))
		{
			 $smallgroupsSQL=array('a.people_id NOT IN (SELECT people_id FROM '.CA_MET_TBL.' WHERE meta_type="smallgroup")');
			 $group_by=' GROUP BY a.people_id ';	}	
	}//end smallgroups

	
	//ministries
	if(!empty($ministries)&&is_array($ministries))			
	{	
		if(!in_array('all',$ministries))//only do if all not selected
		{
			$mins=$wpdb->get_results('SELECT * FROM '.CA_MIN_TBL);
			
			if(!empty($mins))
			{
				foreach($mins AS $min)
				{
					if(in_array(sanitize_title($min->ministry),$ministries))
					{
						$ministriesSQL[]='(c.ID="'.intval($min->ID).'" AND c.ID=g.ID AND c.meta_type="ministry" AND c.people_id=a.people_id)';
						$filteredby[]=$min->ministry;
					}
				}
			}
		}	
	}//end smallgroups
	$other=$tbls='';
	 $group_by=' GROUP BY a.people_id ';
	 $columns=array('a.people_id','a.household_id','a.first_name','a.last_name','a.people_type_id','a.email','a.mobile','a.sex','b.phone','b.address');
	$tables=array(CA_PEO_TBL.' a',CA_HOU_TBL.' b');
	$table_header=array(__('Edit','church-admin'),__('Delete','church-admin'),__('Name','church-admin'),__('People Type','church-admin'),__('Phone','church-admin'),__('Mobile','church-admin'),__('Email','church-admin'),__('Address','church-admin'));
	if(!empty($genderSQL)) 		$other.=' AND ('. implode(" OR ",$genderSQL).')';
	if(!empty($peopleSQL)) 		$other.=' AND ('. implode(" OR ",$peopleSQL).')';
	if(!empty($maritalSQL)) 		$other.=' AND ('. implode(" OR ",$maritalSQL).')';
	if(!empty($sitesSQL)) 		{
									$other.=' AND ('. implode(" OR ",$sitesSQL).') AND a.site_id=d.site_id';
									$tables[]=CA_SIT_TBL.' d';
									$columns[]='d.venue';
								}
	if(!empty($smallgroupsSQL)) 	{
									$other.=' AND ('. implode(" OR ",$smallgroupsSQL).') AND c.ID=e.id';
									$columns[]='e.group_name';
									$tables[]=CA_MET_TBL.' c'; 
									$tables[]=CA_SMG_TBL.' e';
								}
	if(!empty($memberSQL)) 		{
									$other.=' AND ('. implode(" OR ",$memberSQL).')';
									$columns[]='f.member_type';
									$tables[]=CA_MTY_TBL.' f';
								}
	if(!empty($ministriesSQL)) 	{
									$other.=' AND ('. implode(" OR",$ministriesSQL).')';
									$columns[]=', g.ministry ';
									$tables['g']=CA_MIN_TBL.' g';
									$tables['c']=CA_MET_TBL.' c';
									}
	
	foreach($tables AS $letter=>$table)$tbls.=', '.$table.' '.$letter;
	if(!empty($type) && $type=='sms'){$query='a.mobile';}else{$query='a.email';}
	$sql='SELECT '.$query.', a.people_id FROM '.implode(", ",array_filter($tables)).' WHERE a.household_id=b.household_id '.$other.' AND '.$query.' !="" GROUP BY '.$query.'  ORDER BY a.last_name';

	$result=$wpdb->get_results($sql);
	$count=$wpdb->num_rows;
	if(empty($count))$count=0;
	if($type=='email'){return '<strong>'.$count.' email addresses</strong>';}else{return '<strong>'.$count.' mobile numbers</strong>';}
	}
?>