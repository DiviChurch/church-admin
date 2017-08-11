<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly



/**
 *
 * Outputs small group list
 * 
 * @author  Andy Moyle
 * @param   
 * @return  html 
 * @version  0.1
 *
 * 2016-11-07 restrict showing small groups to admins and people assigned to leadership hierarchy
 * 
 */ 

function church_admin_small_groups()
{
	//function to output small group list	
	global $wpdb,$current_user,$people_type;
	wp_get_current_user();
	$people_id=$wpdb->get_var('SELECT people_id FROM '.CA_PEO_TBL.' WHERE user_id="'.intval($current_user->ID).'"');
	$args=array();
	foreach($_GET AS $key=>$value){$args[$key]=$value;}
	
	

	$out='<h1>'.__('Small Groups','church-admin').'</h1>';
	//Add a small group
	$out.='<p><a class="button-primary" href="'.wp_nonce_url("admin.php?page=church_admin/index.php&tab=small_groups&amp;action=edit_small_group",'edit_small_group').'">'.__('Add a small group','church-admin').'</a></p>';
	//unassigned people count
	$out.=church_admin_unassigned_count();
	/****************************
	* pdf form
	*
	*****************************/
	$out.='<h2 class="smallgrouppdf-toggle">'.__('Download Small Groups PDF (Click to toggle)','church-admin').'</h2>';
	$out.='<div class="smallgrouppdf" style="display:none">';
	$out.= '<form name="smallgroup_form" action="'.home_url().'" method="get"><table class="form-table"><input type="hidden" name="download" value="smallgroup"/>';
	$out.='<tr><th scope="row">'.__('Age Range','church-admin').'</th><td>';
		foreach($people_type AS $key=>$value)
		{
			$out.='<input type="checkbox" name="people_type_id[]" value="'.intval($key).'" />'.esc_html($value).'<br/>';
		}
		$out.='</td></tr>'."\r\n";
	$member_type=church_admin_member_type_array();
	foreach($member_type AS $key=>$value)
	{
		$out.='<tr><th scope="row">'.esc_html($value).'</th><td><input type="checkbox" value="'.esc_html($key).'" name="member_type_id[]"/></td></tr>';
	}
	$out.= '<tr><td colspacing=2>'.wp_nonce_field('smallgroup','smallgroup').'<input type="submit" class="button-primary" value="'.__('Download','church-admin').'"/></td></tr></table></form>';
	$out.='</div>';
	$out.='<script type="text/javascript">jQuery(function(){  jQuery(".smallgrouppdf-toggle").click(function(){jQuery(".smallgrouppdf").toggle();  });});</script>';
	
	if(!empty($_GET['message']))$out.='<div class="updated"><p>'.esc_html(urldecode($_GET['message'])).'</p></div>';
	
	//map of small groups
	$row=$wpdb->get_row('SELECT AVG(lat) AS lat,AVG(lng) AS lng FROM '.CA_SIT_TBL);
	if(!empty($row))
	{
		$out.='<script type="text/javascript">var xml_url="'.site_url().'/?download=small-group-xml&small-group-xml='.wp_create_nonce('small-group-xml').'";';
		$out.=' var lat='.esc_html($row->lat).';';
		$out.=' var lng='.esc_html($row->lng).';';
		$out.='jQuery(document).ready(function(){sgload(lat,lng,xml_url);});</script><div id="admin_map"></div><div id="groups" ></div><div class="clear"></div>';
	}
	//list
	
	
	//table of groups
		$out.=__('Drag and Drop to change row display order','church-admin');
		$out.='<table  id="sortable" class="widefat striped"><thead><tr><th>'.__('Edit','church-admin').'</th><th>'.__('Delete','church-admin').'</th><th>'.__('Group Name','church-admin').'</th><th>'.__('Leaders','church-admin').'</th><th>'.__('When','church-admin').'</th></tr></thead><tfoot><tr><th>'.__('Edit','church-admin').'</th><th>'.__('Delete','church-admin').'</th><th>'.__('Group Name','church-admin').'</th><th>'.__('Leaders','church-admin').'</th><th>'.__('When','church-admin').'</th></tr></tfoot><tbody class="content">';
		//grab small group information
		$sg_sql = 'SELECT * FROM '.CA_SMG_TBL.' ORDER BY smallgroup_order';
		$sg_results = $wpdb->get_results($sg_sql);
		foreach ($sg_results as $sg_row) 
		{
			$show=FALSE;//default no show
			$leaders=maybe_unserialize($sg_row->leadership);
			if(!empty($leaders))
			{
				foreach($leaders AS $leaderlevel) if(in_array($people_id,$leaderlevel))$show=TRUE;//allowed!
			}
			if(current_user_can('manage_options'))$show=true;
		
			if($show)	
			{//only build row if user allowed to see it	
				//build leaders
				$ldr='';
				$hierarchy=church_admin_get_hierarchy(1);
    			krsort($hierarchy);
    			//who is currently leading
    			$curr_leaders=maybe_unserialize($sg_row->leadership);
    			//need titles of leaders levels
    			$ministries=church_admin_ministries(NULL);
    			foreach($hierarchy AS $key=>$min_id)
    			{
    				$ldr.='<p><strong>'.$ministries[$min_id].'</strong><br/>';//leader level name
    				if(!empty($curr_leaders[$min_id])){foreach($curr_leaders[$min_id] AS $k=>$people_id)$ldr.=esc_html(church_admin_get_person($people_id)).'<br/>';}else{$ldr.='No leaders assigned yet<br/>';}
    			}
    			$ldr.='</p>';
				if(empty($ldr))$ldr='No leaders assigned yet';
				$edit_url='admin.php?page=church_admin/index.php&action=edit_small_group&tab=small_groups&amp;id='.$sg_row->id;
				$delete_url='admin.php?page=church_admin/index.php&action=delete_small_group&tab=small_groups&amp;id='.$sg_row->id;
        		if($sg_row->id!=1)
				{
					$out.='<tr class="sortable-row" id="'.$sg_row->id.'"><td><a href="'.wp_nonce_url($edit_url, 'edit_small_group').'">'.__('Edit','church-admin').'</a></td><td><a onclick="return confirm(\''.__('Are you sure?','church-admin').'\');" href="'.wp_nonce_url($delete_url, 'delete_small_group').'">'.__('Delete','church-admin').'</a></td><td><a title="'.__('Who is in this group?','church-admin').'" href="'.wp_nonce_url('admin.php?page=church_admin/index.php&amp;action=whosin&amp;id='.intval($sg_row->id),'whosin').'">'.esc_html(stripslashes($sg_row->group_name)).'</a></td><td>'.$ldr.'</td><td>'.esc_html(stripslashes($sg_row->whenwhere)).'</td></tr>';
				}
				else
				{
					$out.='<tr class="sortable-row" id="'.intval($sg_row->id).'"><td>&nbsp;</td><td>&nbsp;</td><td>'.esc_html(stripslashes($sg_row->group_name)).'</td><td>&nbsp;</td><td>'.esc_html(stripslashes($sg_row->whenwhere)).'</td></tr>';
       			}
       		}//only build row if user is allowed to see it
		} 
		$out.="</tbody></table>";
	$out.= '
    <script type="text/javascript">
  
 jQuery(document).ready(function($) {
 
    var fixHelper = function(e,ui){
            ui.children().each(function() {
                $(this).width($(this).width());
            });
            return ui;
        };
    var sortable = $("#sortable tbody.content").sortable({
    helper: fixHelper,
    stop: function(event, ui) {
        //create an array with the new order
        
       
				var Order = "order="+$(this).sortable(\'toArray\').toString();

       
        
        $.ajax({
            url: "admin.php?page=church_admin/index.php&action=church_admin_update_order&which=small_groups",
            type: "post",
            data:  Order,
            error: function() {
                console.log("theres an error with AJAX");
            },
            success: function() {
                
            }
        });}
});
$("#sortable tbody.content").disableSelection();
});

   
   
    </script>
';
	
	return $out;	
}
//end of small group information function







function church_admin_remove_from_smallgroup($people_id,$ID)
{
	global $wpdb;
	$name=$wpdb->get_var('SELECT CONCAT_WS(" ",first_name,prefix,last_name) FROM '.CA_PEO_TBL.' WHERE people_id="'.esc_sql($people_id).'"');
	if(!empty($name))
	{
		church_admin_delete_people_meta($ID,$people_id,'smallgroup');
		church_admin_update_people_meta(1, $people_id,'smallgroup');
		echo'<div class="notice notice-success inline">'.$name.' '.__('has been removed from group and put in unattached group','church-admin').'</div>';
	}
	church_admin_whosin($ID);
}

function church_admin_whosin($id)
{
	//2016-11-07 added ability to restrict to leaders over that group and admins

	global $wpdb,$current_user;
	wp_get_current_user();
	$people_id=$wpdb->get_var('SELECT people_id FROM '.CA_PEO_TBL.' WHERE user_id="'.intval($current_user->ID).'"');
	$attendance=array('1'=>'Regular','2'=>'Irregular','3'=>'Connected');
	
	$out='';
	$group=$wpdb->get_row('SELECT * FROM  '.CA_SMG_TBL.' WHERE id="'.esc_sql(intval($id)).'"');
	if(!empty($group))
	{
		$show=FALSE;//default no show
		$leaders=maybe_unserialize($group->leadership);
		foreach($leaders AS $leaderlevel) if(in_array($people_id,$leaderlevel))$show=TRUE;//allowed!
		if(current_user_can('manage_options'))$show=true;
		
		if($show)	
		{
		
			//group details
			$out.=sprintf( '<h2>%1$s %2$s %3$s</h2>', __( 'Who is in', 'church-admin' ),esc_html($group->group_name),__('group','church-admin') );
			$out.='';
			$out.='<table class="form-table"><tbody>';
			$out.='<tr><th scope="row">'.__('Leader(s)','church-admin').':</th><td>';
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
			$out.='</td><td rowspan=3><img class="alignleft" src="http://maps.google.com/maps/api/staticmap?center='.esc_html($group->lat).','.esc_html($group->lng).'&zoom=13&markers='.esc_html($group->lat).','.esc_html($group->lng).'&size=200x200"/></td></tr>';
			$out.='<tr><th scope="row">'.__('Meeting','church-admin').':</th><td>'.esc_html($group->whenwhere).'</td></tr>';
			$out.='<tr><th scope="row">'.__('Venue','church-admin').':</th><td>'.esc_html($group->address).'</td></tr>';
			$out.='</tbody></table>';
			//grab group ids of people in group
			$sql='SELECT a.people_id,b.first_name,b.prefix,b.last_name,b.prefix,b.nickname,b.smallgroup_attendance,b.email,b.mobile FROM '.CA_MET_TBL.' a, '.CA_PEO_TBL.' b WHERE a.ID="'.esc_sql($id).'" AND a. meta_type="smallgroup" AND a.people_id=b.people_id';
			$peopleresults = $wpdb->get_results($sql);
			if(!empty($peopleresults))
			{
			
		
				$out.='<table class="widefat striped">';
				$out.='<thead><tr><th>'.__('Remove from Group','church-admin').'</th><th>'.__('Name','church-admin').'</th><th>'.__('Attendance','church-admin').'</th><th>'.__('Email','church-admin').'</th><th>'.__('Mobile','church-admin').'</th></tr></thead><tfoot><tr><th>'.__('Remove from Group','church-admin').'</th><th>'.__('Name','church-admin').'</th><th>'.__('Attendance','church-admin').'</th><th>'.__('Email','church-admin').'</th><th>'.__('Mobile','church-admin').'</th></tr></tfoot><tbody>';
				foreach($peopleresults AS $row)
				{
					
					//build name
					$name=$row->first_name;
					$middle_name=get_option('church_admin_use_middle_name');
					if(!empty($middle_name)&&!empty($row->middle_name))$name.=' '.$row->middle_name.' ';
					$nickname=get_option('church_admin_use_nickname');
					if(!empty($nickname)&&!empty($row->nickname))$name.=' ('.$row->nickname.') ';
					$prefix=get_option('church_admin_use_prefix');
					if($prefix)	$name.=$row->prefix.' ';			
					$name.=$row->last_name;
					$remove='<a onclick="return confirm(\''.__('Are you sure?','church-admin').'\');" href="'.wp_nonce_url('admin.php?page=church_admin/index.php&action=remove_from_smallgroup&tab=small_groups&amp;smallgroup_id='.$id.'&amp;people_id='.$row->people_id,'remove').'">'.__('Remove','church-admin').'</a>';
						$out.='<tr><td>'.$remove.'</td><td>'.esc_html($name).'</td><td>'.$attendance[$row->smallgroup_attendance].'</td><td><a href="mailto:'.esc_html($row->email).'">'.esc_html($row->email).'</a></td><td><a href="call:'.$row->email.'">'.esc_html($row->mobile).'</td></tr>';
					
				}
				$out.='</tbody></table>';
			}
		}
		else{$out.=__('You are not allowed to see this group','church-admin');}
	echo $out;
	require_once(plugin_dir_path(dirname(__FILE__)).'includes/comments.php');
	if($show)church_admin_show_comments('smallgroup',$id);
	}

}

 

/**
 *
 * Delete small group
 * 
 * @author  Andy Moyle
 * @param    $id
 * @return   
 * @version  0.1
 *
 * 
 * 
 */ 
function church_admin_delete_small_group($id)
{
    global $wpdb;
    
	$sql='DELETE FROM '.CA_SMG_TBL.' WHERE id="'.esc_sql($_GET['id']).'"';
	$wpdb->query($sql);
	$out='<div class="wrap church_admin"><div id="message" class="notice notice-success inline"><p><strong>'.__('Small Group Deleted','church-admin').'</strong></p></div>';
	$out.= church_admin_small_groups();
    return $out;   
}


/**
 *
 * Edit small group
 * 
 * @author  Andy Moyle
 * @param    $id
 * @return   
 * @version  0.1
 *
 * v1.071 - changed leadership to the more efficient autocomplete 
 * 
 */

function church_admin_edit_small_group($id)
{
    global $wpdb;
    $out='';
	//current poeple in group
	if(!empty($id))
		{//find who is in group
			$sql='SELECT CONCAT_WS(" ",a.first_name,a.prefix,a.last_name) AS name FROM '.CA_PEO_TBL.' a ,'.CA_MET_TBL.' b where a.people_id=b.people_id AND  b.meta_type="smallgroup" AND b.ID="'.esc_sql($id).'"  ORDER BY a.last_name ';
		
			$result=$wpdb->get_results($sql);	
			if(!empty($result))
			{
				$people=array();
				foreach($result AS $row)$currentPeople[]=esc_html($row->name);
				$displayCurrentPeople=implode(', ',$currentPeople);
			}
			else $displayCurrentPeople='';
		}
		else{$displayCurrentPeople='';}
	
	
	
	
    $hierarchy=church_admin_get_hierarchy(1);//leadership hierarchy for small groups.
    krsort($hierarchy);//sort top level down
    $ministries=church_admin_ministries(NULL);
    if(isset($_POST['edit_small_group']))
    {
		
		$form=array();
		foreach($_POST AS $key=>$value)$form[$key]=sanitize_text_field(stripslashes_deep($value));
		$leaders=array();
		//handle leaders and coaches etc
		foreach($hierarchy AS $key=>$min_id)
		{
			if(!empty($_POST['a'.$min_id]))
			{//done form autocomplete, so convert to an array
				$autocompleted=explode(',',$_POST['a'.$min_id]);//string with entered names
				
				foreach($autocompleted AS $x=>$name)
				{
					$p_id=church_admin_get_one_id(trim($name));//get the people_id
					
					if(!empty($p_id))
					{
						$leaders[$min_id][]=$p_id;//add to leadership array for query
						//update people_meta table
						church_admin_update_people_meta($min_id,$p_id,'ministry');//update person as leader at that level
					}
				}
			}
			
			
		}
		
		//check to see if processed
		if(!$id)$id=$wpdb->get_var('SELECT id FROM '.CA_SMG_TBL.' WHERE leadership="'.esc_sql(serialize($leaders)).'" AND whenwhere="'.esc_sql($form['whenwhere']).'" AND group_name="'.esc_sql($form['group_name']).'" AND lat="'.esc_sql($form['lat']).'" AND lng="'.esc_sql($form['lng']).'" AND address="'.esc_sql($form['address']).'"');
		if($id)
		{//update
			$sql='UPDATE '.CA_SMG_TBL.' SET lat="'.esc_sql($form['lat']).'",lng="'.esc_sql($form['lng']).'",leadership="'.esc_sql(serialize($leaders)).'",address="'.esc_sql($form['address']).'",group_name="'.esc_sql($form['group_name']).'",whenwhere="'.esc_sql($form['whenwhere']).'" WHERE id="'.esc_sql(intval($id)).'"';
		
			$wpdb->query($sql);
   
		}//end update
		else
		{//insert
			$sql='INSERT INTO  '.CA_SMG_TBL.' (group_name,whenwhere,address,lat,lng,leadership) VALUES("'.esc_sql($form['group_name']).'","'.esc_sql($form['whenwhere']).'","'.esc_sql($form['address']).'","'.esc_sql($form['lat']).'","'.esc_sql($form['lng']).'","'.esc_sql(serialize($leaders)).'")';
			
			$wpdb->query($sql);
			$id=$wpdb->insert_id;
		}//insert
		//add people to group
		if(!empty($_POST['people']))
		{
			
			//find ids of people entered
			
			$people_ids=maybe_unserialize(church_admin_get_people_id(stripslashes($_POST['people'])));
			

			if(!empty($people_ids))
			{
				
				foreach($people_ids AS $key=>$person_id)
				{
					if(ctype_digit($person_id))
					{
						church_admin_delete_people_meta(NULL,$person_id,'smallgroup');
						church_admin_update_people_meta($id,$person_id,'smallgroup');
					}
				}
			}
			//anyone who was in group and has now been deleted gets put in unattached group
			if(!empty($currentPeople))
			{
				foreach($currentPeople AS $key=>$people_id)
				{
					$check=church_admin_get_people_meta($people_id,'smallgroup');//look to see if in any group
					if(empty($check))church_admin_update_people_meta(1,$people_id,'smallgroup');//put them in group 1 if not
				}
			}
		}
		
		$out.='<div class="wrap church_admin"><div id="message" class="notice notice-success inline"><p><strong>'.__('Small Group Edited','church-admin').'</strong></p></div>';
		$out.=church_admin_small_groups();
    }
    else
    {
    	
		$data=$wpdb->get_row('SELECT * FROM '.CA_SMG_TBL.' WHERE id="'.esc_sql($id).'"');
		if(empty($data))$data=new stdClass();
		
	    $out.='<h2>'.__('Add/Edit Small Group','church-admin').'</h2><form action="" method="post">';
	    $out.='<table class="form-table"><tbody><tr><th scope="row">'.__('Small group name','church-admin').'</th><td><input type="text" name="group_name"';
	    if(!empty($data->group_name)) $out.= ' value="'.esc_html($data->group_name).'" ';
	    $out.='/></td></tr>';
	    $out.='<tr><th scope="row">'.__('When','church-admin').'</th><td><input type="text" name="whenwhere"';
	    if(!empty($data->whenwhere)) $out.= ' value="'.esc_html($data->whenwhere).'" ';
	    $out.='/></td></tr>';
	    
		$out.='<script type="text/javascript"> var beginLat =';
		if(!empty($data->lat)) {$out.= esc_html($data->lat);}else {$data->lat='51.50351129583287';$out.= '51.50351129583287';}
		$out.= '; var beginLng =';
		if(!empty($data->lng)) {$out.= esc_html($data->lng);}else {$data->lng='-0.148193359375';$out.='-0.148193359375';}
		$out.=';';
		//$out.='jQuery(document).ready(function(){sgload(lat,lng,xml_url);});';
	    $out.='</script>';
		$out.='<tr><th scope="row">'.__('Address','church-admin').'</th><td><input type="text" id="address" name="address"';
	    if(!empty($data->address)) $out.= ' value="'.esc_html($data->address).'" ';
	    $out.='/></td></tr>';
		$out.= '<tr><th scope="row"><a href="#" id="geocode_address">'.__('Please click here to update map location','church-admin').'...</a></th><td><span id="finalise" >'.__('Once you have updated your address, this map will show roughly where your address is.','church-admin').'</span><input type="hidden" name="lat" id="lat" value="'.esc_html($data->lat).'"/><input type="hidden" name="lng" id="lng" value="'.esc_html($data->lng).'"/><div id="map" style="width:500px;height:300px"></div></td></tr>';
		
  		//leadership section
  		
    	if(!empty($data->leadership))$curr_leaders=maybe_unserialize($data->leadership);
    	
    	foreach($hierarchy AS $key=>$min_id)
    	{
    		if(!empty($curr_leaders[$min_id])){$current=church_admin_get_people($curr_leaders[$min_id]);}else{$current='';}
    		$out.='<tr class="ca-types autocomplete"><th scope=row>'.__('Leaders for','church-admin').' "' .esc_html($ministries[$min_id]).'"</th><td>'.church_admin_autocomplete('a'.intval($min_id),'a'.intval($min_id),'a'.intval($min_id),$current).'</td></tr>';
    		
    	}
	    
	    
			
		$out.='<tr class="ca-types autocomplete"><th scope=row>'.__('Add some people to the group','church-admin').'</th><td>'.church_admin_autocomplete('people','friends','to',$displayCurrentPeople).'</td></tr>'; 
		$out.='<tr><th scope="row">&nbsp;</th><td><input class="button-primary" type="submit" name="edit_small_group" value="'.__('Save Small Group','church-admin').' &raquo;" /></td></tr></tbody></table></form>';
	
    }
    return $out;
}

 /**
 *
 * Count of unassigned people
 * 
 * @author  Andy Moyle
 * @param   
 * @return  $out 
 * @version  0.1
 *
 * 
 * 
 */ 
function church_admin_unassigned_count()
{
	global $wpdb;
	church_admin_groups_cleanup();
	$out='<h2 class="unassigned-toggle">'.__('People in small groups cleanup (Click to Toggle)','church-admin').'</h2>';
	$out.='<div class="unassigned" style="display:none">';
		//work out how many people not assigned.
		$unassignedCount=$doubleAssigned=0;
		$peopleUnassigned=$peopleDoubleAssigned='';
		$people=$wpdb->get_results('SELECT * FROM '.CA_PEO_TBL);
		if(!empty($people))
		{
			foreach($people AS $person)
			{
				$inGroup=church_admin_get_people_meta($person->people_id,'smallgroup');
				if(empty($inGroup))
				{//not in a group
					$unassignedCount+=1;
					church_admin_update_people_meta(1,$person->people_id,'smallgroup');
				}
				if($wpdb->num_rows>1)
				{
					$doubleAssigned+=1;
					$peopleDoubleAssigned.='<p><a href="'.wp_nonce_url('admin.php?page=church_admin/index.php&amp;action=edit_people&amp;people_id='.intval($person->people_id),'edit_people').'">'.esc_html($person->first_name .' '.$person->last_name).' '.__('is in more than one small group','church-admin').'</a></p>';
				}
		
			}
		}
	
		if($unassignedCount>0)$out.=sprintf( '<p>'.esc_html(__( 'There were %d unassigned people in your entire address list (They are now in "unattached")', 'church-admin' )).'</p>', $unassignedCount );
		if($doubleAssigned>0)$out.=sprintf( '<p>'.esc_html(__( 'There are %d people who are in more than one small group at once in your entire address list.', 'church-admin' )).'</p>', $doubleAssigned );
		if(!empty($peopleUnassigned))$out.=$peopleUnassigned;
		if(!empty($peopleDoubleAssigned))$out.=$peopleDoubleAssigned;
	$out.='</div>';	
	$out.='<script type="text/javascript">jQuery(function(){  jQuery(".unassigned-toggle").click(function(){jQuery(".unassigned").toggle();  });});</script>';
	
	
	return $out;
}

 /**
 *
 * Cleanup of groups in people_meta table
 * 
 * @author  Andy Moyle
 * @param   
 * @return  $out 
 * @version  0.1
 *
 * 
 * 
 */ 
function church_admin_groups_cleanup()
{
	global $wpdb;
	$smg=array();//all the small groups
	
	$groups=$wpdb->get_results('SELECT id FROM '.CA_SMG_TBL);
	if(!empty($groups))
	{
		foreach($groups AS $group) $smg[]=intval($group->id);
	}
	$groupsInMetaTable=$wpdb->get_results('SELECT ID FROM '.CA_MET_TBL.' GROUP BY ID');
	if(!empty($groupsInMetaTable))
	{
		foreach($groupsInMetaTable AS $groupInMetaTable) 
		{
			if(!in_array($groupInMetaTable->ID,$smg))
			{
				$wpdb->query('DELETE FROM '.CA_MET_TBL.' WHERE ID="'.intval($groupInMetaTable->ID).'" AND meta_type="smallgroup"');
				
			}
		}
	}

}	
	
	
?>