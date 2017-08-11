<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
//Address Directory Functions
//2016-09-26 Added Nickname

function church_admin_view_person($people_id=NULL)
{

	global $wpdb;
	
	$data=$wpdb->get_row('SELECT *,first_name,middle_name,prefix,last_name FROM '.CA_PEO_TBL.' WHERE people_id="'.intval($people_id).'"');
	if(!empty($data))
	{
		if(!empty($data->attachment_id))
		{//photo available
			
			echo wp_get_attachment_image( $data->attachment_id,'ca-people-thumb',NULL,array('class'=>'alignleft') );
			
		}//photo available
		$name=$data->first_name.' ';
					$middle_name=get_option('church_admin_use_middle_name');
					if(!empty($middle_name)&&!empty($data->middle_name))$name.=$data->middle_name.' ';
					$nickname=get_option('church_admin_use_nickname');
					if(!empty($nickname)&&!empty($data->nickname))$name.=' ('.$data->nickname.') ';
					$prefix=get_option('church_admin_use_prefix');
					if(!empty($prefix)&&!empty($data->prefix))		$name.=$data->prefix.' ';			
					$name.=$data->last_name;
		echo'<h2><a href="'.wp_nonce_url('admin.php?page=church_admin/index.php&amp;action=edit_people&amp;people_id='.intval($people_id),'edit_people').'">'.esc_html($name).'</a></h2><br style="clear:left"/>';
		echo'<h3>'.__('Contact Details','church-admin').'</h3>';
		echo'<table class="form-table">';
		if(!empty($data->mobile))echo'<tr><th scope="row">'.__('Mobile','church-admin').'</th><td><a href="call:'.esc_html($data->mobile).'">'.esc_html($data->mobile).'</a></td></tr>';
		if(!empty($data->email))echo'<tr><th scope="row">'.__('Email','church-admin').'</th><td><a href="call:'.esc_html($data->email).'">'.esc_html($data->email).'</a></td></tr>';
		if(!empty($data->twitter))echo'<tr><th scope="row">Twitter</th><td><a href="https://twitter.com/'.esc_html($data->twitter).'">@'.esc_html($data->twitter).'</a></td></tr>';
		if(!empty($data->facebook))echo'<tr><th scope="row">Facebook</th><td><a href="https://www.facebook.com/'.esc_html($data->facebook).'">'.esc_html($data->facebook).'</a></td></tr>';
		if(!empty($data->instagram))echo'<tr><th scope="row">Instagram</th><td><a href="https://www.instagram.com/'.esc_html($data->instagram).'">'.esc_html($data->instagram).'</a></td></tr>';
		echo'</table>';
		echo'<h3>'.__('Church Metadata','church-admin').'</h3>';
		echo'<table class="form-table">';
		//site
		if(!empty($data->site_id))$site_details=$wpdb->get_var('SELECT venue FROM '.CA_SIT_TBL.' WHERE site_id="'.intval($data->site_id).'"');
		if(!empty($site_details))echo'<tr><th scope="row">'.__('Site attended','church-admin').'</th><td>'.esc_html($site_details).'</td></tr>';
		//small groups
		$groupIDs=church_admin_get_people_meta($people_id,'smallgroup');
		
		if(!empty($groupIDs))
		{
			foreach($groupIDs AS $groupID)	$group[]=$wpdb->get_var('SELECT group_name FROM '.CA_SMG_TBL.' WHERE id="'.intval($groupID).'"');
			if(!empty($group))echo'<tr><th scope="row">'.__('Small group','church-admin').'</th><td>'.esc_html(implode(", ",$group)).'</td></tr>';
		}
		//ministries
		$mins=array();//temp stor for person'sministries
		$ministries=church_admin_ministries();
		
		$person_ministries=$wpdb->get_results('SELECT ID FROM '.CA_MET_TBL.' WHERE people_id="'.esc_sql($people_id).'" AND meta_type="ministry"');
		
		if(!empty($person_ministries))
		{
			foreach($person_ministries AS $person_ministry)$mins[]=$ministries[$person_ministry->ID];
			echo'<tr><th scope="row">'.__('Ministries','church-admin').'</th><td>'.esc_html(implode(", ",$mins)).'</td></tr>';
		}
		//hope team
		$hopeteamjobs=array();
		$hts=$wpdb->get_results('SELECT job,hope_team_id FROM '.CA_HOP_TBL);
		if(!empty($hts))
		{
			foreach($hts AS $ht){$hopeteamjobs[$ht->hope_team_id]=$ht->job;}
		}
		$jobs=$wpdb->get_results('SELECT ID FROM '.CA_MET_TBL.' WHERE people_id="'.esc_sql($people_id).'" AND meta_type="hope_team"');
		$person_jobs=array();
		if(!empty($jobs))
		{
			foreach($jobs AS $job)$personjobs[]=$hopeteamjobs[$job->ID];
			echo'<tr><th scope="row">'.__('Hope Teams','church-admin').'</th><td>'.esc_html(implode(", ",$personjobs)).'</td></tr>';
			
		}
		echo'</table>';
		
		$others=$wpdb->get_results('SELECT *,CONCAT_WS(" ",first_name,prefix,last_name) AS name FROM '.CA_PEO_TBL.' WHERE household_id="'.intval($data->household_id).'" AND people_id!="'.intval($people_id).'" ORDER BY people_order ASC');
		if(!empty($others))
		{
			echo'<h3>'.__('Others in household','church-admin').'</h3>';
			foreach($others AS $other)
			{
				echo '<p><a href="'.wp_nonce_url('admin.php?page=church_admin/index.php&amp;action=edit_people&amp;people_id='.intval($other->people_id),'edit_people').'">'.esc_html($other->name).'</a></p>';
			}
		}
		//notes
		require_once(plugin_dir_path(dirname(__FILE__)).'includes/comments.php');
		if(!empty($people_id))church_admin_show_comments('people',	$people_id);
	
	}
	
	
	
}

function church_admin_address_list($member_type_id=0)
{
    global $wpdb;
    $wpdb->query('UPDATE '.CA_PEO_TBL.' SET head_of_household=0 WHERE head_of_household=NULL');
	$member_type=church_admin_member_type_array();
	$member_type[0]=__('Complete','church-admin');
	

   
    //grab address list in order
	$sql='SELECT DISTINCT household_id FROM '.CA_PEO_TBL;
    if(!empty($member_type_id)){$sql.=' WHERE member_type_id="'.esc_sql($member_type_id).'"';}
  
    $result = $wpdb->get_var($sql);
    $items=$wpdb->num_rows;
   
    echo'<hr/><table class="form-table"><tbody><tr><th scope="row">'.__('Select different address list to view','church-admin').'</th><td><form name="address" action="admin.php?page=church_admin/index.php&amp;action=church_admin_address_list&tab=people" method="POST"><select name="member_type_id" >';
			    echo '<option value="0">'.__('All Member Type...','church-admin').'</option>';
			    foreach($member_type AS $key=>$value)
			    {
					$count=$wpdb->get_var('SELECT COUNT(people_id) FROM '.CA_PEO_TBL.' WHERE member_type_id="'.esc_sql($key).'"');
					echo '<option value="'.esc_html($key).'" >'.esc_html($value).' ('.$count.' people)</option>';
			    }
			    echo'</select><input type="submit" value="'.__('Go','church-admin').'"/></form></td></tr></tbody></table>';
    // number of total rows in the database
    require_once(plugin_dir_path(dirname(__FILE__)).'includes/pagination.class.php');
    if($items > 0)
    {
	
	$p = new pagination;
	$p->items($items);
	$p->limit(get_option('church_admin_page_limit')); // Limit entries per page
	
	$p->target("admin.php?page=church_admin/index.php&tab=people&action=church_admin_address_list&tab=people&amp;member_type_id=".$member_type_id);
	if(!isset($p->paging))$p->paging=1; 
	if(!isset($_GET[$p->paging]))$_GET[$p->paging]=1;
	$p->currentPage((int)$_GET[$p->paging]); // Gets and validates the current page
	$p->calculate(); // Calculates what to show
	$p->parameterName('paging');
	$p->adjacents(1); //No. of page away from the current page
	if(!isset($_GET['paging']))
	{
	    $p->page = 1;
	}
	else
	{
	    $p->page = intval($_GET['paging']);
	}
        //Query for limit paging
	$limit = esc_sql("LIMIT " . ($p->page - 1) * $p->limit  . ", " . $p->limit);
     
    
    //prepare WHERE clause using given Member_type_id
	$sort='last_name ASC';
	if(!empty($_GET['sort']))
	{
		switch($_GET['sort'])
		{ 
			case'date' :$sort='last_updated DESC';break;
			case'last_name':$sort='last_name ASC';break;
			default:$sort='last_name ASC';break;
		}
	}
    $sql='SELECT * FROM '.CA_PEO_TBL.' WHERE head_of_household=1' ;
    if(!empty($member_type_id))$sql.=' AND member_type_id="'.esc_sql($member_type_id).'"';
    $sql.=' ORDER BY '.$sort.' '.$limit;
   
    $results=$wpdb->get_results($sql);
   
    
    if(!empty($results))
    {
		if(empty($member_type[$member_type_id]))$member_type[$member_type_id]=__('Whole','church-admin');
		echo '<h2>'.$member_type[$member_type_id].' '.__('address list','church-admin').'</h2>';
	 	echo'<p><span class="ca-private">'.__('Households not shown publicly','church-admin').' </span></p>';
		// Pagination
    	echo '<div class="tablenav"><div class="tablenav-pages">';
    	echo $p->show();  
    	echo '</div></div>';
    	//Pagination
    	//grab address details and associate people and put in table
		echo '<table class="widefat striped"><thead><tr><th>'.__('Delete','church-admin').'</th><th><a href="admin.php?page=church_admin/index.php&action=church_admin_address_list&tab=people&member_type_id='.intval($member_type_id).'&sort=last_name">'.__('Last name','church-admin').'</a></th><th>'.__('First Name(s)','church-admin').'</th><th>'.__('Address','church-admin').'<a></th><th><a href="admin.php?page=church_admin/index.php&action=church_admin_address_list&tab=people&member_type_id='.intval($member_type_id).'&sort=date">'.__('Last Update','church-admin').'</th></tr></thead><tfoot><tr><th>'.__('Delete','church-admin').'</th><th>'.__('Last name','church-admin').'</th><th>'.__('First Name(s)','church-admin').'</th><th>'.__('Address','church-admin').'</th><th>'.__('Last Update','church-admin').'</th></tr></tfoot><tbody>';
		
	foreach($results AS $row)
	{
	    
	    //grab address
	    $add_row=$wpdb->get_row('SELECT * FROM '.CA_HOU_TBL.' WHERE household_id="'.esc_sql($row->household_id).'"');
		
	     //grab people
	    $people_results=$wpdb->get_results('SELECT * FROM '.CA_PEO_TBL.' WHERE household_id="'.esc_sql($row->household_id).'" ORDER BY people_order,people_type_id ASC,sex DESC');
	    $adults=$children=array();
	    $prefix='';
		$private='';
		$class=array();
		if(!empty($add_row->private))$class[]='ca-private';
		
	    foreach($people_results AS $people)
	    {
			if(empty($people->active))$class[]='ca-deactivated';
			if ($people->head_of_household==1)
			{
				$last_name='';
				if(!empty($people->prefix))$last_name.=$people->prefix.' ';
				$last_name.=$people->last_name;
			}
			if(empty($people->last_name))$people->last_name=__('Add Surname','church-admin');
			if(empty($people->first_name))$people->first_name=__('Add Firstname','church-admin');
			if($people->people_type_id=='1'){$adults[]='<a href="'.wp_nonce_url('admin.php?page=church_admin/index.php&tab=people&action=edit_people&amp;household_id='.intval($row->household_id).'&amp;people_id='.intval($people->people_id),'edit_people').'">'.esc_html($people->first_name).'</a>';}else{$children[]='<a href="'.wp_nonce_url('admin.php?page=church_admin/index.php&amp;action=edit_people&tab=people&household_id='.intval($row->household_id).'&amp;people_id='.intval($people->people_id),'edit_people').'">'.esc_html($people->first_name).'</a>' ;}
			if(!empty($people->prefix)){$prefix=$people->prefix.' ';}
	    }
	    
	    if(!empty($adults)){$adult=implode(" & ",$adults);}else{ $adult=__("Add Name",'church-admin');}
	    if(!empty($children)){$kids=' ('.implode(", ",$children).')';}else{$kids='';}
	    
	    $delete='<a onclick="return confirm(\''.__('Are you sure?','church-admin').'\');" href="'.wp_nonce_url('admin.php?page=church_admin/index.php&tab=people&action=delete_household&household_id='.$row->household_id,'delete_household').'">'.__('Delete','church-admin').'</a>';
	    if(empty($add_row->address))$add_row->address=__('Add Address','church-admin');
	    if(!empty($class)){$classes=' class="'.implode(" ",$class).'"';}else$classes='';
	   echo '<tr '.$classes.'><td>'.$delete.'</td><td><a  href="'.wp_nonce_url('admin.php?page=church_admin/index.php&amp;action=display_household&tab=people&household_id='.$row->household_id,'display_household').'">'.esc_html($last_name).'</a></td><td>'.$adult.' '.$kids.'</td><td><a href="'.wp_nonce_url('admin.php?page=church_admin/index.php&amp;action=display_household&tab=people&household_id='.$row->household_id,'display_household').'">'.esc_html($add_row->address).'</a></td><td>'.mysql2date('d/M/Y',$add_row->ts).'</td></tr>';
	    
	    
	}
	
	
	echo '</tbody></table>';
    echo '<div class="tablenav"><div class="tablenav-pages">';
    // Pagination
    echo '<div class="tablenav"><div class="tablenav-pages">';
    echo $p->show();  
    echo '</div></div>';
    //Pagination  
    
    }//end of items>0
    }	

    
	
    
}
 /**
 *
 * Edit Household
 * 
 * @author  Andy Moyle
 * @param    $household_id
 * @return   html
 * @version  0.1
 * 
 */
function church_admin_edit_household($household_id=NULL)
{
    global $wpdb,$church_admin_version;
	$member_type=church_admin_member_type_array();

  
	
    $member_type_id=$wpdb->get_var('SELECT member_type_id FROM '.CA_PEO_TBL.' WHERE household_id="'.esc_sql($household_id).'"  ORDER BY people_type_id ASC LIMIT 1');
    if(!empty($household_id)){$data=$wpdb->get_row('SELECT * FROM '.CA_HOU_TBL.' WHERE household_id="'.esc_sql($household_id).'"');}else{$data=NULL;}
    if(!empty($_POST['edit_household']))
    {//process form
	$private=NULL;
	if(!empty($_POST['private']))$private=1;
	$form=array();
	foreach ($_POST AS $key=>$value)$sql[$key]=esc_sql(sanitize_text_field(stripslashes($value)));
	if(!$household_id)$household_id=$wpdb->get_var('SELECT household_id FROM '.CA_HOU_TBL.' WHERE address="'.$sql['address'].'" AND lat="'.$sql['lat'].'" AND lng="'.$sql['lng'].'" AND phone="'.$sql['phone'].'"');
	if(!$household_id)
	{//insert
	    $success=$wpdb->query('INSERT INTO '.CA_HOU_TBL.' (address,lat,lng,phone,private) VALUES("'.$sql['address'].'", "'.$sql['lat'].'","'.$sql['lng'].'","'.$sql['phone'].'","'.$private.'" )');
	    $household_id=$wpdb->insert_id;
	}//end insert
	else
	{//update
	   $sql='UPDATE '.CA_HOU_TBL.' SET address="'.$sql['address'].'" , lat="'.$sql['lat'].'" , lng="'.$sql['lng'].'" , phone="'.$sql['phone'].'", private="'.$private.'" WHERE household_id="'.esc_sql($household_id).'"';
	   //echo $sql;
	   $success=$wpdb->query($sql);
	}//update
	if($success)
	{
	    echo '<div class="notice notice-success inline"><p><strong>'.__('Address saved','church-admin').' <br/><a href="./admin.php?page=church_admin/index.php&tab=people&action=church_admin_address_list&member_type_id='.$member_type_id.'">'.__('Back to Directory','church-admin').'</a></strong></td></tr></div>';
	}
	    echo'<div id="post-body" class="metabox-holder columns-2"><!-- meta box containers here -->';
		
		echo'<div class="notice notice-success inline"><p><strong>'.__('Household Edited','church-admin').' <br/>';
		if(church_admin_level_check('Directory')) echo'<a href="./admin.php?page=church_admin/index.php&tab=people&action=church_admin_address_list&member_type_id='.$member_type_id.'">'.__('Back to Directory','church-admin').'</a>';
		echo'</strong></td></tr></div>';
	
		church_admin_display_household($household_id);
	
		
    }//end process form
    else
    {//household form
	if(!empty($household_id)){$text='Edit ';}else{$text='Add ';}
	echo '<form action="" method="post">';
	//clean out old style address data
	if(!empty($data)&&is_array(maybe_unserialize($data->address)))
	{
		$data->address=implode(", ",array_filter(maybe_unserialize($data->address)));
	}
	echo church_admin_address_form($data,$error=NULL);
	//Phone
    echo '<table class="form-table"><tr><th scope="row">'.__('Phone','church-admin').'</th><td><input type="text" name="phone" ';
	if(!empty($data->phone)) echo ' value="'.esc_html($data->phone).'"';
    if(!empty($errors['phone']))echo' class="red" ';
    echo '/></td></tr>';
    if(empty($data->private))$data->private=0;
	echo'<tr><th scope="row">'.__('Private (not shown publicly)','church-admin').'</th><td><input type="checkbox" name="private" value="1" '.checked(1,$data->private).'/></td></tr>';
    
	echo'<tr><td colspan="2"><input type="hidden" name="edit_household" value="yes"/><input class="button-primary" type="submit" value="'.__('Save Address','church-admin').'&raquo;" /></td></tr></form>';
    }//end household form

	
}

 /**
 *
 * Delete Household
 * 
 * @author  Andy Moyle
 * @param    $household_id
 * @return   html
 * @version  0.1
 * 
 */
function church_admin_delete_household($household_id=NULL)
{
    //deletes household with specified household_id
    global $wpdb;

   
    //delete people meta data
    $people=$wpdb->get_results('SELECT * FROM '.CA_PEO_TBL.' WHERE household_id="'.esc_sql($household_id).'"');
    foreach($people AS $person){$member_type_id=$person->member_type_id;$wpdb->query('DELETE FROM '.CA_MET_TBL.' WHERE meta_type="ministry" AND people_id="'.esc_sql($person->people_id).'"');}
    //delete from household and people tables
    $wpdb->query('DELETE FROM '.CA_HOU_TBL.' WHERE household_id="'.esc_sql($household_id).'"');
    $wpdb->query('DELETE FROM '.CA_PEO_TBL.' WHERE household_id="'.esc_sql($household_id).'"');
    echo'<div class="notice notice-success inline"><p><strong>'.__('Household Deleted','church-admin').'</strong></td></tr></div>';
    
}


 /**
 *
 * Edit a person
 * 
 * @author  Andy Moyle
 * @param    $people_id,$household_id
 * @return   
 * @version  0.2
 *
 * 0.11 added photo upload 2012-02-24
 * 0.2 added site_id, marital status 2016-05-12
 * 
 */ 


function church_admin_edit_people($people_id=NULL,$household_id=NULL)
{
   
   
    global $wpdb,$people_type,$ministries,$current_user;
	$member_type=church_admin_member_type_array();
	$ministries=church_admin_ministries();
	$church_admin_marital_status=array(0=>__('N/A','church-admin'),1=>__('Single','church-admin'),2=>__('Co-habiting','church-admin'),3=>__('Married','church-admin'),4=>__('Divorced','church-admin'),5=>__('Widowed','church-admin'));
	  wp_get_current_user();
	

    echo'<h2>'.__('Edit Person','church-admin').'</h2>';
	$hopeteamjobs=array();
		$hts=$wpdb->get_results('SELECT job,hope_team_id FROM '.CA_HOP_TBL);
		if(!empty($hts))
		{
		
			foreach($hts AS $ht){$hopeteamjobs[$ht->hope_team_id]=$ht->job;}
		}
	
    
    if($people_id)$data=$wpdb->get_row('SELECT * FROM '.CA_PEO_TBL.' WHERE people_id="'.esc_sql($people_id).'"');
	if(empty($data)) $data = new stdClass();
	
    if(!empty($data->household_id))$household_id=$data->household_id;
    if(!empty($_POST['edit_people']))
    {//process
    	
		if(empty($_POST['smallgroup_id']))$_POST['smallgroup_id']=NULL;
		
		if(empty($household_id))
		{
			$wpdb->query('INSERT INTO '.CA_HOU_TBL.' (lat,lng) VALUES("52.000","0.000")');
			$household_id=$wpdb->insert_id;
		}
		$sql=array();
		//sanitise multi level as some post keys are arrays
		foreach($_POST AS $key=>$value)
		{
			if(!is_array($value))$sql[$key]=esc_sql(sanitize_text_field(stripslashes($value)));
			else
			{
				$wpdb->query('INSERT INTO '.CA_HOU_TBL.' (lat,lng) VALUES("52.000","0.000")');
				$household_id=$wpdb->insert_id;
			}
			$sql=array();
			//sanitise multi level as some post keys are arrays
			foreach($_POST AS $key=>$value)
			{
				if(!is_array($value))$sql[$key]=esc_sql(sanitize_text_field(stripslashes($value)));
				else
				{
					foreach($_POST[$key] AS $key2=>$value) $sql[$key][$key2]=esc_sql(sanitize_text_field(stripslashes($value)));
				}
			}
			//handle date of birth
			if(empty($_POST['data_of_birth'])&&!empty($_POST['date_of_birth1']))$_POST['date_of_birth']=date('Y-m-d',strtotime($_POST['date_of_birth1']));
			if(!empty($_POST['date_of_birth'])&& church_admin_checkdate($_POST['date_of_birth'])){$dob=esc_sql($_POST['date_of_birth']);}else{$dob='0000-00-00';}
	
			if(!$people_id)$people_id=$wpdb->get_var('SELECT people_id FROM '.CA_PEO_TBL.' WHERE first_name="'.$sql['first_name'].'" AND nickname="'.$sql['nickname'].'" AND prefix="'.$sql['prefix'].'" AND last_name="'.$sql['last_name'].'" AND email="'.$sql['email'].'" AND mobile="'.$sql['mobile'].'" AND sex="'.$sql['sex'].'" AND people_type_id="'.$sql['people_type_id'].'" AND  member_type_id="'.$sql['member_type_id'].'" AND date_of_birth="'.$dob.'" AND household_id="'.esc_sql($household_id).'"');
		
			$member_data=array();
			foreach($member_type AS $no=>$type)
			{
				$type=str_replace(' ','_',$type);
				if(empty($_POST[$type])&&!empty($_POST[$type.'1']))$_POST[$type]=date('Y-m-d',strtotime($_POST[$type.'1']));
				if(!empty($_POST[$type]) && church_admin_checkdate($_POST[$type])){$member_data[$type]=$_POST[$type];}else{$member_data[$type]="0000-00-00";}
				//if($no==$_POST['member_type_id'] && $_POST['member_type_id']!=$data->member_type_id){$member_data[$type]=date('Y-m-d');}
				//if(!empty($_POST[$type])&&church_admin_checkdate($_POST['type'])){$member_data[$type]=$_POST[$type];}else{$member_data[$type]=NULL;}
			}
		
			$member_data=serialize($member_data);
			if(!empty($_POST['attachment_id'])){$attachment_id=intval($_POST['attachment_id']);}else{$attachment_id=intval($data->attachment_id);}
		
			if(!church_admin_level_check('Directory'))
			{//keep old values as not able to edit...
				$sql['member_type_id']=$data->member_type_id;
				if(empty($data->member_type_id))
				{
					//no current member level data so give same level as editing user!
						$sql['member_type_id']=$wpdb->get_var('SELECT member_type_id FROM '.CA_PEO_TBL.' WHERE user_id="'.esc_sql($current_user->ID).'"');
				}
				$member_data=$data->member_data;
			
			}
			if(!empty($sql['twitter']))$sql['twitter']=str_replace("@","",$sql['twitter']);
			if(!empty($_POST['prayer_chain'])){$prayer_chain=1;}else{$prayer_chain=0;}
			if(empty($sql['kidswork_override'])){$sql['kidswork_override']=NULL;}
			if(empty($sql['prefix']))$sql['prefix']='';
			if(empty($sql['middle_name']))$sql['middle_name']='';
			if(!empty($_POST['ID'])&&ctype_digit($_POST['ID'])){$sql['user_id']=$_POST['ID'];}else{$sql['user_id']='';}
			if($people_id)
			{//update
			
				$query='UPDATE '.CA_PEO_TBL.' SET nickname="'.$sql['nickname'].'",middle_name="'.$sql['middle_name'].'", facebook="'.$sql['facebook'].'",twitter="'.$sql['twitter'].'",instagram="'.$sql['instagram'].'", prayer_chain="'.$prayer_chain.'",kidswork_override="'.$sql['kidswork_override'].'", user_id="'.$sql['user_id'].'",first_name="'.$sql['first_name'].'" ,prefix="'.$sql['prefix'].'", last_name="'.$sql['last_name'].'" , email="'.$sql['email'].'" , mobile="'.$sql['mobile'].'" , sex="'.$sql['sex'].'" ,people_type_id="'.$sql['people_type_id'].'", member_type_id="'.$sql['member_type_id'].'" , date_of_birth="'.$dob.'",member_data="'.esc_sql($member_data).'", attachment_id="'.$attachment_id.'",user_id="'.$sql['user_id'].'",marital_status="'.$sql['marital_status'].'",site_id="'.$sql['site_id'].'" WHERE household_id="'.esc_sql($household_id).'" AND people_id="'.esc_sql($people_id).'"';
			
		    	$wpdb->query($query);
			
			
			}//end update
			else
			{
				$sql='INSERT INTO '.CA_PEO_TBL.' ( first_name,middle_name,nickname,prefix,last_name,email,mobile,sex,people_type_id,member_type_id,date_of_birth,household_id,member_data,attachment_id,user_id,prayer_chain,kidswork_override,marital_status,site_id,twitter,facebook,instagram) VALUES("'.$sql['first_name'].'","'.$sql['middle_name'].'","'.$sql['nickname'].'","'.$sql['prefix'].'","'.$sql['last_name'].'" , "'.$sql['email'].'" , "'.$sql['mobile'].'" , "'.$sql['sex'].'" ,"'.$sql['people_type_id'].'", "'.$sql['member_type_id'].'" , "'.$dob.'" , "'.esc_sql($household_id).'","'.esc_sql($member_data).'" ,"'.$attachment_id.'","'.$sql['user_id'].'","'.$prayer_chain.'","'.$sql['kidswork_override'].'","'.$sql['marital_status'].'","'.$sql['site_id'].'","'.$sql['twitter'].'","'.$sql['facebook'].'","'.$sql['instagram'].'")';
		
				$wpdb->query($sql);
				$people_id=$wpdb->insert_id;
			}
			church_admin_delete_people_meta(NULL,$people_id,'smallgroup');

		if(!empty($_POST['smallgroup_id']))foreach($_POST['smallgroup_id'] AS $key=>$id) 		church_admin_update_people_meta($id,$people_id,$meta_type='smallgroup');
		if(!empty($_POST['username']))
		{
			church_admin_create_user($people_id,$household_id,$_POST['username']);
		}
		//new small group
		if(!empty($_POST['group_name']))
		{
			$check=$wpdb->get_row('SELECT * FROM '.CA_SMG_TBL.' WHERE group_name="'.$sql['group_name'].'" AND whenwhere="'.$sql['when'].'" AND address="'.$sql['where'].'"');
		
			if(!empty($check))
			{//update
				$ldrs=esc_sql(serialize(array(1=>$people_id)));
				$query='UPDATE '.CA_SMG_TBL.' SET leadership="'.$ldrs.'",group_name="'.$sql['group_name'].'",whenwhere="'.$sql['when'].'" AND address="'.$sql['where'].'" WHERE id="'.esc_sql($check->id).'"';
				$wpdb->query($query);
				$sg_id=$check->id;
			}//end update
			else
			{//insert
				$leaders=esc_sql(maybe_serialize(array(1=>array(1=>$people_id))));
				$query='INSERT INTO  '.CA_SMG_TBL.' (group_name,leadership,whenwhere,address) VALUES("'.$sql['group_name'].'","'.$leaders.'","'.$sql['when'].'","'.$sql['where'].'")';
				$wpdb->query($query);
				$sg_id=$wpdb->insert_id;
			}//insert
			church_admin_update_people_meta($sg_id,$people_id,'smallgroup');
		}
		
	if(church_admin_level_check('Directory'))
	{//only authorised people
		//update meta
		
		$deleted=$wpdb->query('DELETE FROM '.CA_MET_TBL.' WHERE people_id="'.esc_sql($people_id).'" AND meta_type="ministry"');
	
		//if new small group then add small group leader to person's meta
		if(!empty($_POST['leading'])){church_admin_update_people_meta('1',$people_id,'ministry');}
		
		if(!empty($_POST['ministry']))
		{ 
			foreach($_POST['ministry'] AS $a=>$key)
			{
				if(array_key_exists($key,$ministries)){church_admin_update_people_meta($key,$people_id,'ministry');}
			}
		}
		church_admin_delete_from_hope_team($people_id);
		if(!empty($_POST['hope_team']))
		{ 
			foreach($_POST['hope_team'] AS $a=>$key)
			{
				if(array_key_exists($key,$hopeteamjobs)){church_admin_update_people_meta($key,$people_id,'hope_team');}
			}
		}
		
		
		if(!empty($_POST['new_ministry'])&&$_POST['new_ministry']!='Add a new ministry')
		{
	    
			if(!in_array(stripslashes($_POST['new_ministry']),$ministries))
			{
				$new=stripslashes($_POST['new_ministry']);
				$wpdb->query('INSERT INTO '.CA_MIN_TBL.' (ministry) VALUES("'.esc_sql($new).'")');
				$ID=$wpdb->insert_id;
				church_admin_update_people_meta($ID,$people_id,'ministry');
			}
		}
	}//only authorised people
		//end of process into db, now output...		
		
		
		
		echo'<div class="notice notice-success inline"><p><strong>'.__('Person Edited','church-admin').' <br/>';
		if(church_admin_level_check('Directory') &&!empty($sql['member_type_id'])) echo'<a href="./admin.php?page=church_admin/index.php&amp;action=church_admin_address_list&tab=people&amp;member_type_id='.$sql['member_type_id'].'">'.__('Back to Directory','church-admin').'</a>';
		echo'</strong></td></tr></div>';
		echo'<form name="ca_search" action="admin.php?page=church_admin/index.php&tab=address" method="POST"><table class="form-table"><tbody><tr><th scope="row">'.__('Search','church-admin').'</th><td><input name="church_admin_search" style="width:100px;" type="text"/><input type="submit" value="'.__('Go','church-admin').'"/></td></tr></table></form>';
		church_admin_display_household($household_id);
		
	
		
    }
    
    }//end process
    else
    {//form
	
		echo'<form action="" method="POST" enctype="multipart/form-data">';
		//first name
		echo'<table class="form-table"><tbody><tr><th scope="row">'.__('First Name','church-admin').'</th><td><input type="text" name="first_name" ';
		if(!empty($data->first_name)) echo ' value="'.esc_html($data->first_name).'" ';
		echo'/></td></tr>'."\r\n";
		//middle_name
		$use_middle_name=get_option('church_admin_use_middle_name');
        if($use_middle_name)
        {
        	echo'<tr><th scope="row">'.__('Middle Name','church-admin').'</th><td><input type="text" name="middle_name" ';
			if(!empty($data->middle_name)) echo ' value="'.esc_html($data->middle_name).'" ';
			echo'/></td></tr>'."\r\n";
		}
		//nickname
		$useNickname=get_option('church_admin_use_nickname');
	    if($useNickname){
	    	
        	echo'<tr><th scope="row">'.__('Nickname','church-admin').'</th><td><input type="text" name="nickname" ';
			if(!empty($data->nickname)) echo ' value="'.esc_html($data->nickname).'" ';
			echo'/></td></tr>'."\r\n";
	    }
		//prefix
		$use_prefix=get_option('church_admin_use_prefix');
        if($use_prefix)
        {
        	echo'<tr><th scope="row">'.__('Prefix (e.g.van der)','church-admin').'</th><td><input type="text" name="prefix" ';
			if(!empty($data->prefix)) echo ' value="'.esc_html($data->prefix).'" ';
			echo'/></td></tr>'."\r\n";
		}
		
		//last name
		echo'<tr><th scope="row">'.__('Last Name','church-admin').'</th><td><input type="text" name="last_name" ';
		if(!empty($data->last_name)) echo ' value="'.esc_html($data->last_name).'" ';
		echo'/></td></tr>'."\r\n";
		//photo
		/*
		echo'<tr><th scope="row">'.__('Photo','church-admin').'</th><td><input type="file" id="photo" name="uploadfiles" size="35" class="uploadfiles" /></td></tr>';
		if(!empty($data->attachment_id))
		{//photo available
			echo '<tr><th scope="row">Current Photo</th><td>';
			echo wp_get_attachment_image( $data->attachment_id,'ca-people-thumb' );
			echo'</td></tr>'."\r\n";
		}//photo available
		else
		{
			echo '<tr><th scope="row">&nbsp;</th><td>';
			echo '<img src="'.plugins_url('/images/default-avatar.jpg',dirname(__FILE__) ).'" width="75" height="75"/>';
			echo '</td></tr>'."\r\n";
		}
		*/
		echo'<tr><th scope="row">'.__('Photo','church-admin').'</th><td><input id="image-id" type="hidden" name="attachment_id" /><input id="upload-button" type="button" class="button" value="'.__('Upload Image','church-admin').'" /></td></tr>';
		echo '<tr><th scope="row">Current Photo</th><td>';
		if(!empty($data->attachment_id)){echo wp_get_attachment_image( $data->attachment_id,'ca-people-thumb','', array('class'=>"current-photo"));}else{echo '<img src="'.plugins_url('/images/default-avatar.jpg',dirname(__FILE__) ).'" width="75" height="75" class="current-photo"/>';}
		echo'</td></tr>'."\r\n";
		echo'<script type="text/javascript">jQuery(document).ready(function($){

  var mediaUploader;

  $("#upload-button").click(function(e) {
    e.preventDefault();
    // If the uploader object has already been created, reopen the dialog
      if (mediaUploader) {
      mediaUploader.open();
      return;
    }
    // Extend the wp.media object
    mediaUploader = wp.media.frames.file_frame = wp.media({
      title: "Choose Image",
      button: {
      text: "Choose Image"
    }, multiple: false });

    // When a file is selected, grab the URL and set it as the text fields value
    mediaUploader.on("select", function() {
      var attachment = mediaUploader.state().get("selection").first().toJSON();
      console.log(attachment);
      $("#image-id").val(attachment.id);
      console.log(attachment.sizes.thumbnail.url);
      $(".current-photo").attr("src",attachment.sizes.thumbnail.url);
      $(".current-photo").attr("srcset",null);
    });
    // Open the uploader dialog
    mediaUploader.open();
  });

});</script>';
		//email
		echo'<tr><th scope="row">'.__('Email Address','church-admin').'</th><td><input type="text" name="email" ';
		if(!empty($data->email)) echo ' value="'.esc_html($data->email).'" ';
		echo'/></td></tr>'."\r\n";
		//mobile
		echo'<tr><th scope="row">'.__('Mobile','church-admin').'</th><td><input type="text" name="mobile" ';
		if(!empty($data->mobile)) echo ' value="'.esc_html($data->mobile).'" ';
		echo'/></td></tr>'."\r\n";
		//twitter
		echo'<tr><th scope="row">Twitter</th><td>@<input type="text" name="twitter" ';
		if(!empty($data->twitter)) echo ' value="'.esc_html($data->twitter).'" ';
		echo'/></td></tr>'."\r\n";
		//facebook
		echo'<tr><th scope="row">Facebook</th><td><input type="text" name="facebook" ';
		if(!empty($data->facebook)) echo ' value="'.esc_html($data->facebook).'" ';
		echo'/></td></tr>'."\r\n";
		//instagram
		echo'<tr><th scope="row">Instagram</th><td><input type="text" name="instagram" ';
		if(!empty($data->instagram)) echo ' value="'.esc_html($data->instagram).'" ';
		echo'/></td></tr>'."\r\n";
		//sex
		$gender=get_option('church_admin_gender');
		echo'<tr><th scope="row">'.__('Gender','church-admin').'</th><td><select name="sex">';
		foreach($gender AS $key=>$value){echo '<option value="'.esc_html($key).'" '.selected($data->sex,$key,FALSE).'>'.esc_html($value).'</option>';}
		echo'</select></td></tr>'."\r\n";
		//marital status
		echo'<tr><th scope="row">'.__('Marital Status','church-admin').'</th><td><select name="marital_status">';
		$first=$option="";
		$church_admin_marital_status=array(0=>__('N/A','church-admin'),1=>__('Single','church-admin'),2=>__('Co-habiting','church-admin'),3=>__('Married','church-admin'),4=>__('Divorced','church-admin'),5=>__('Widowed','church-admin'));
		foreach($church_admin_marital_status AS $key=>$status)
		{
			if(!empty($data->marital_status) && $status==$data->marital_status){$first='<option value="'.esc_html($status).'" selected="selected" >'.esc_html($status).'</option>';}
			else{$option.='<option value="'.esc_html($status).'">'.esc_html($status).'</option>';}
		}
		echo $first.$option;
		echo '</select></td></tr>'."\r\n";
		//people_type
		echo'<tr><th scope="row">'.__('Age Range','church-admin').'</th><td><select name="people_type_id">';
		foreach($people_type AS $key=>$value)
		{
			echo'<option value="'.$key.'" ';
			if(!empty($data->people_type_id))selected($key,$data->people_type_id);
			echo'>'.$value.'</option>';
		}
		echo'</select></td></tr>'."\r\n";
		//date of birth
		if(empty($data->date_of_birth))$data->date_of_birth=NULL;
		echo'<tr><th scope="row">'.__('Date of Birth','church-admin').'</th><td>'.church_admin_date_picker($data->date_of_birth,'date_of_birth',FALSE,1910,date('Y')).'</td></tr>';
		
	if(church_admin_level_check('Directory'))
	{//only available to authorised people
	
		//site
		$sites=$wpdb->get_results('SELECT venue,site_id FROM '.CA_SIT_TBL.' ORDER BY venue ASC');
		
		echo'<tr><th scope="row">'.__('Site','church-admin').'</th><td><select name="site_id" id="site_id1" class="site_id">';
		$first=$option='';
		foreach($sites AS $site)
		{
			if(!empty($data->site_id)&& $data->site_id==$site->site_id){$first='<option value="'.intval($site->site_id).'" selected="selected">'.esc_html($site->venue).'</option>';}
			else {$option.='<option value="'.intval($site->site_id).'">'.esc_html($site->venue).'</option>';}
		}
		echo $first.$option;
		echo'</select></td></tr>'."\r\n";
		$kidswork_groups=$wpdb->get_results('SELECT * FROM '.CA_KID_TBL.' ORDER BY youngest');
		if(!empty($kidswork_groups))
		{//add an override	
			echo'<tr><th scope="row">'.__('Override kids work group','church-admin').'</th><td><select name="kidswork_override">';
			echo'<option value="" '.selected($data->kidswork_override,NULL).'>'.__('Assign by age automatically','church-admin').'</option>';
			foreach($kidswork_groups AS $kwgp)echo'<option value="'.esc_html($kwgp->id).'" '.selected($data->kidswork_override,$kwgp->id).'>'.esc_html($kwgp->group_name).'</option>';
			echo'</select></td></tr>'."\r\n";
		}
		echo'<tr><th scope="row">'.__('Current Member Type','church-admin').'</th><td><span style="display:inline-block">';
		$first=$option='';
		foreach($member_type AS $key=>$value)
		{
			echo'<input type="radio" name="member_type_id" value="'.esc_html($key).'"';
			if(!empty($data->member_type_id)&&$data->member_type_id==$key)echo' checked="checked" ';
			echo ' />'.esc_html($value).'<br/>';
	   
		}
	
		echo'</span></td></tr>'."\r\n";
	
	
		if(!empty($data->member_type_id))$prev_member_types=unserialize($data->member_data);
	
	    echo'<tr><th scope="row">'.__('Dates of Member Levels','church-admin').'</th><td><span style="display:inline-block">	';
	    foreach($member_type AS $key=>$value)
	    {
	    	if(empty($prev_member_types[$value]))$prev_member_types[$value]=NULL;
	    	if(empty($value))$value='';
	    	$safevalue=str_replace(' ','_',$value);
			echo '<span style="float:left;width:150px">'.$value.'</span>'. 			church_admin_date_picker($prev_member_types[$value],$safevalue,FALSE,1910,date('Y')).'<br/>';
			
			}
			echo'</span></td></tr>'."\r\n";
	
		echo'<tr><th scope="row">'.__('Ministries','church-admin').'</th><td><span style="display:inline-block">';
		if(!empty($ministries))
		{
			asort($ministries);
			foreach($ministries AS $key=>$value)
			{
				echo'<span style="float:left;width:150px">'.$value.'</span><input type="checkbox" name="ministry[]" value="'.esc_html($key).'" ';
				if(!empty($data->people_id))
				{
					$check=$wpdb->get_var('SELECT meta_id FROM '.CA_MET_TBL.' WHERE people_id="'.esc_sql($data->people_id).'" AND meta_type="ministry" AND ID="'.esc_sql($key).'"');
					if($check)echo ' checked="checked" ';
				}
				echo '/><br style="clear:left"/>';
			}
		}
		echo '<input type="text" name="new_ministry" placeholder="'.__('Add a new ministry','church-admin').'" /></td></tr>'."\r\n";
		//hope team
		echo'<tr><th scope="row">'.__('Hope Team','church-admin').'</th><td><span style="display:inline-block">';
		
		foreach($hopeteamjobs AS $key=>$value)
			{
				echo'<span style="float:left;width:150px">'.$value.'</span><input type="checkbox" name="hope_team[]" value="'.esc_html($key).'" ';
				if(!empty($data->people_id))
				{
					$check=$wpdb->get_var('SELECT meta_id FROM '.CA_MET_TBL.' WHERE people_id="'.esc_sql($data->people_id).'" AND meta_type="hope_team" AND ID="'.esc_sql($key).'"');
					if($check)echo ' checked="checked" ';
				}
				echo '/><br style="clear:left"/>';
			}
	}//only available to authorised people
	//small group
	
		$groups=church_admin_get_people_meta($data->people_id,'smallgroup');
		
		echo'<tr><th scope="row">'.__('Small Group','church-admin').'</th><td><span style="display:inline-block">';
		$smallgroups=$wpdb->get_results('SELECT * FROM '.CA_SMG_TBL);
		$first=$option='';
		foreach($smallgroups AS $smallgroup)
		{
			
			echo'<input type="checkbox" name="smallgroup_id[]" value="'.esc_html($smallgroup->id).'"';
			if(!empty($groups)&&in_array($smallgroup->id,$groups)) echo' checked="checked" ';
			echo'/>'.$smallgroup->group_name.'<br/>';
		}
		echo '</span></td></tr>'."\r\n";
	if(church_admin_level_check('Directory'))
	{//only authorised people to edit wordpress user or create new small groups or adjust attendance indicator
		//if(empty($data->smallgroup_attendance))$data->smallgroup_attendance=1;
		//echo'<tr><th scope="row">'.__('Small group attendance','church-admin').'</th><td><input type="radio" name="smallgroup_attendance" value="1" '.checked('1',$data->smallgroup_attendance,0).'/>'.__('Regular','church-admin').' &nbsp;<input type="radio" name="smallgroup_attendance" value="2" '.checked('2',$data->smallgroup_attendance,0).'/>'.__('Irregular','church-admin').' &nbsp; <input type="radio" name="smallgroup_attendance" value="3" '.checked('3',$data->smallgroup_attendance,0).'/>'.__('Connected','church-admin').' &nbsp;</td></tr>';
	
		echo'<tr><th scope="row">'.__('Or create new Small Group','church-admin').'</th><td><span style="display:inline-block"><span style="float:left;width:150px">Group Name</span><input type="text" name="group_name"/><br style="clear:left"/><span style="float:left;width:150px">'.__('Leader?','church-admin').'</span><input type="checkbox" name="leading" value="yes"/><br style="clear:left;"/><span style="float:left;width:150px">'.__('Where','church-admin').'</span><input type="text" name="where"/></span><br/><span style="float:left;width:150px">When</span><input type="text" name="when"/></span></td></tr>'."\r\n";
		
		if(!empty($data->user_id ))
		{
			echo'<tr><th scope="row">'.__('Wordpress User','church-admin').'</th><td><input type="hidden" name="ID" value="'.esc_html($data->user_id).'"/>';
			$user_info=get_userdata($data->user_id);
			if(!empty($user_info)){echo __('Username','church-admin').': '.$user_info->user_login.'<br/>'.__('User level','church-admin').': '.$user_info->roles['0'].'</td></tr>';}
		}	
		else
		{
			
			$sql='SELECT user_login,ID FROM '.$wpdb->prefix.'users WHERE `ID` NOT IN (SELECT user_id FROM '.CA_PEO_TBL.' WHERE user_id!=0)';
			$users=$wpdb->get_results($sql);
				
			if(!empty($users))
			{
					echo'<tr><th scope="row">'.__('Choose a Wordpress account to associate','church-admin').'</th><td><select name="ID"><option value="">'.__('Select a user...','church-admin').'</option>';
					foreach($users AS $user) echo'<option value="'.esc_html($user->ID).'">'.esc_html($user->user_login).'</option>';
					echo'</select></td></tr>';
			}
			echo'<tr><th scope="row">'.__('Or create a new Wordpress User','church-admin').'</th><td><input id="username" type="text" placeholder="'.__('Username','church-admin').'" name="username" value=""/><span id="user-result"></span></td></tr>'."\r\n";
			$nonce = wp_create_nonce("church_admin_username_check");
			echo'<script type="text/javascript">jQuery(document).ready(function($) {
			$("#username").change(function() { 
			var username=$("#username").val();
			var data = {
			"action": "church_admin_username_check",
			"username": username,
			"nonce": "'.$nonce.'"
		};

		// since 2.8 ajaxurl is always defined in the admin header and points to admin-ajax.php
		jQuery.post(ajaxurl, data, function(response) {console.log(response);
			$("#user-result").html(response);
		});
			
			});
			});</script>';
		}
		
		echo'<tr><th scope="row">'.__('Prayer Chain','church-admin').'</th><td><input type="checkbox" name="prayer_chain"';
		if(!empty($data->prayer_chain))echo ' checked="checked" ';
		echo'/></td></tr>'."\r\n";
	}//only authorised people to edit wordpress user
		echo'<tr><th scope="row"><input type="hidden" name="edit_people" value="yes"/><input class="button-primary" type="submit" value="'.__('Save Details','church-admin').'&raquo;" /></td></tr></tbody></table></form>';
    }//form
    require_once(plugin_dir_path(dirname(__FILE__)).'includes/comments.php');
	if(!empty($people_id))church_admin_show_comments('people',	$people_id);
   
}


 /**
 *
 * Delete People
 * 
 * @author  Andy Moyle
 * @param    $people_id,$household_id
 * @return   html
 * @version  0.1
 * 
 */
function church_admin_delete_people($people_id=NULL,$household_id)
{
    //deletes person with specified people_id
    global $wpdb;

	$data=$wpdb->get_row('SELECT * FROM '.CA_PEO_TBL.' WHERE people_id="'.esc_sql($people_id).'" ');
	
	
	if(!empty($data->head_of_household))
	{//need to reassign head of household
		$message= sprintf( esc_html__( '%1$s was head of household','church-admin'),$data->first_name.' '.$data->last_name).'<br/>';
		//look for another adult
		$next_person=$wpdb->get_row('SELECT * FROM '.CA_PEO_TBL.' WHERE household_id="'.intval($household_id).'" AND people_type_id=1 AND people_id!="'.intval($people_id).'" LIMIT 1');
		if(!empty($next_person))$message.=sprintf( esc_html__( 'Head of household reassigned to %1$s','church-admin'),$next_person->first_name.' '.$next_person->last_name).'<br/>';
		//no adult, find someone!
		if(empty($next_person->people_id))$next_person=$wpdb->get_row('SELECT * from '.CA_PEO_TBL.' WHERE household_id="'.intval($household_id).'"  AND people_id!="'.intval($people_id).'" AND people_type_id=1 LIMIT 1');
		if(!empty($next_person))$message.=sprintf( esc_html__( 'Head of household reassigned to %1$s','church-admin'),$next_person->first_name.' '.$next_person->last_name).'<br/>';else{$message='';}
		//set new head of hosuehold
		if(!empty($next_person->people_id))
		{
			$sql='UPDATE '.CA_PEO_TBL.' SET head_of_household=1 WHERE people_id="'.intval($next_person->people_id).'"';
			$wpdb->query($sql);	
		}
	}
    $wpdb->query('DELETE FROM '.CA_PEO_TBL.' WHERE people_id="'.esc_sql($people_id).'" ');
    $message.=sprintf( esc_html__( '%1$s has been deleted','church-admin'),$data->first_name.' '.$data->last_name);
    $count=$wpdb->get_var('SELECT COUNT(people_id) FROM '.CA_PEO_TBL.' WHERE household_id ="'.esc_sql($household_id).'" ');
    if(empty($count))
    {
    	$wpdb->query('DELETE FROM '.CA_PEO_TBL.' WHERE household_id="'.esc_sql($household_id).'" ');
    	$message=__('Household Deleted','church-admin');
    }
    $wpdb->query('DELETE FROM '.CA_MET_TBL.' WHERE people_id="'.esc_sql($people_id).'" AND meta_type="ministry"');
    echo'<div class="notice notice-success inline"><p><strong>'.$message.'</strong></td></tr></div>';
	
	if(!empty($count)){church_admin_display_household($household_id);}else{church_admin_people_main();}
 
    
}
 /**
 *
 * Address form
 * 
 * @author  Andy Moyle
 * @param    $data, $error
 * @return   html
 * @version  0.1
 * 
 */
function church_admin_address_form($data,$error)
{
    //echos form contents where $data is object of address data and $error is array of errors if applicable
    if(empty($data))$data=(object)'';
    $out='';
    if(!empty($errors))$out.='<p>'.__('There were some errors marked in red','church-admin').'</p>';
    
    if(!empty($data->lat) && !empty($data->lat))
    {//initial data for position already available    
    	$out.='<script type="text/javascript"> var beginLat ='.esc_html($data->lat);
		$out.= '; var beginLng ='.esc_html($data->lng);
    	$out.=';</script>';
    }else
    {//use HTML5 geolocation to roughly position where user is
    	$out.='<script type="text/javascript">';
    	$out.='if (navigator.geolocation) {
          navigator.geolocation.getCurrentPosition(function(position) {
               beginLat = position.coords.latitude;
            	beginLng = position.coords.longitude;
            });}
            else{ 
             	var beginLat = 51.50351129583287;
            	var  beginLng = -0.148193359375;
            };
    
            
            </script>';
    }
   
    $out.= '<table class="form-table"><tbody><tr><th scope="row">'.__('Address','church-admin').'</th><td><input style="width:100%" type="text" id="address" name="address" ';
    if(!empty($data->address)) $out.=' value="'.esc_html($data->address).'" ';
    if(!empty($error['address'])) $out.= ' class="red" ';
    $out.= '/></td></tr>';
    if(!isset($data->lng))$data->lng='51.50351129583287';
    if(!isset($data->lat))$data->lat='-0.148193359375';
    $out.= '<tr><th scope="row"><a href="#" id="geocode_address">'.__('Please click here to update map location','church-admin').'...</a></th><td><span id="finalise" >'.__('Once you have updated your address, this map will show roughly where your address is.','church-admin').'</span><input type="hidden" name="lat" id="lat" value="'.$data->lat.'"/><input type="hidden" name="lng" id="lng" value="'.$data->lng.'"/></td></tr><tr><td colspan="2"><div id="map" style="width:500px;height:300px"></div></td></tr>';
    $out.='</tbody></table>';
	
    return $out;
    
}
 /**
 *
 * Display household
 * 
 * @author  Andy Moyle
 * @param    $household_id
 * @return   html
 * @version  0.1
 * 
 */
function church_admin_display_household($household_id)
{
    global $wpdb,$people_type;
	$member_type=church_admin_member_type_array();
    
    $ministries=church_admin_ministries();
    $add_row=$wpdb->get_row('SELECT * FROM '.CA_HOU_TBL.' WHERE household_id="'.esc_sql($household_id).'"');
    if(empty($add_row))$add_row=new stdClass();
    if($add_row)
    {//address stored
	if(!empty($addrow->private)) echo'<p><span class="ca-private">'.__('This household is private and not shown publicly','church-admin').'</span></p>';
	if(!empty($add_row->address))
	{ 
		//old style <v0.554
		if(is_array(maybe_unserialize($add_row->address))) $address=implode(', ',array_filter(maybe_unserialize($add_row->address)));
		//>v0.553
		else{$address=$add_row->address;}
	}else{$address='Add Address';}
	 echo'<script type="text/javascript"> var beginLat =';
    if(!empty($data->lat)) {echo $data->lat;}else {echo '51.50351129583287';}
$out.= '; var beginLng =';
    if(!empty($data->lng)) {echo $data->lng;}else {echo'-0.148193359375';}
    echo';</script>';
	if(empty($add_row->lng)){$add_row->lng='-0.148193359375';}
	if(empty($add_row->lat)){$add_row->lat='51.50351129583287';}
	$key=get_option('church_admin_google_api_key');
	$staticMapUrl='http://maps.google.com/maps/api/staticmap?key='.$key.'&amp;center='.$add_row->lat.','.$add_row->lng.'&zoom=15&markers='.$add_row->lat.','.$add_row->lng.'&size=500x300';
	$status=church_admin_api_checker($staticMapUrl);
	if(empty($status)){$map=__('Google Maps API not working','church-admin');}
	elseif($status==403){$map=__('One of the parameters of your Google MAP API request is wrong','church_admin');}
	elseif($status==400){$map=__('Your Google Map API key is missing, wrong, or not enable fro Static maps','church_admin');}
	else {$map='<img src="'.$staticMapUrl.'" alt="'.$address.'"/>';}
	
	
	echo'<h2>'.__('Household Details','church-admin').'</h2>';
	echo'<table class="form-table"><tbody><tr><th scope="row">'.__('Select different address list to view','church-admin').'</th><td><form name="address" action="admin.php?page=church_admin/index.php&amp;action=church_admin_address_list&tab=people" method="POST"><select name="member_type_id" >';
			   echo '<option value="0">'.__('All Member Type...','church-admin').'</option>';
			    foreach($member_type AS $key=>$value)
			    {
					$count=$wpdb->get_var('SELECT COUNT(people_id) FROM '.CA_PEO_TBL.' WHERE member_type_id="'.esc_sql($key).'"');
					echo '<option value="'.esc_html($key).'" >'.esc_html($value).' ('.$count.' people)</option>';
			    }
			    echo'</select><input type="submit" value="'.__('Go','church-admin').'"/></form></td></tr></tbody></table>';
	//grab people
	$people=$wpdb->get_results('SELECT * FROM '.CA_PEO_TBL.' WHERE household_id="'.esc_sql($household_id).'" ORDER BY people_order ASC,people_type_id ASC,date_of_birth ASC,sex DESC');
	if($people)
	{//are people
	    echo'<p><a class="button-primary" href="'.wp_nonce_url('admin.php?page=church_admin/index.php&amp;action=edit_people&amp;household_id='.$household_id,'edit_people').'">'.__('Add someone','church-admin').'</a></td></tr>';
		echo '<p>'.__('You can drag and drop to sort people display order (First person is head of household)','church-admin').'</td></tr>';
		if(church_admin_level_check('Directory'))
		{
			echo'<table id="sortable" class="widefat striped"><thead><tr><th>'.__('Edit','church-admin').'</th><th>'.__('Delete','church-admin').'</th><th>'.__('Picture','church-admin').'</th><th>'.__('Name','church-admin').'</th><th>'.__('Sex','church-admin').'</th><th>'.__('Person type','church-admin').'</th><th>'.__('Member Level','church-admin').'</th><th>'.__('Site','church-admin').'</th><th>'.__('Ministries','church-admin').'</th><th>'.__('Hope Team','church-admin').'</th><th>'.__('Email','church-admin').'</th><th>'.__('Mobile','church-admin').'</th><th>'.__('Move to different household','church-admin').'</th><th>'.__('WP user','church-admin').'</th></tr></thead><tfoot><tr><th>'.__('Edit','church-admin').'</th><th>'.__('Delete','church-admin').'</th><th>'.__('Picture','church-admin').'</th><th>'.__('Name','church-admin').'</th><th>'.__('Sex','church-admin').'</th><th>'.__('Person type','church-admin').'</th><th>'.__('Member Level','church-admin').'</th><th>'.__('Site','church-admin').'</th><th>'.__('Ministries','church-admin').'</th><th>'.__('Hope Team','church-admin').'</th><th>'.__('Email','church-admin').'</th><th>'.__('Mobile','church-admin').'</th><th>'.__('Move to different household','church-admin').'</th><th>'.__('WP user','church-admin').'</th></tr></tfoot><tbody  class="content">';
		}
		else
		{
			echo'<table id="sortable" class="widefat striped"><thead><tr><th>'.__('Edit','church-admin').'</th><th>'.__('Delete','church-admin').'</th><th>'.__('Picture','church-admin').'</th><th>'.__('Name','church-admin').'</th><th>'.__('Sex','church-admin').'</th><th>'.__('Person type','church-admin').'</th><th>'.__('Member Level','church-admin').'</th><th>'.__('Site','church-admin').'</th><th>'.__('Ministries','church-admin').'</th><th>'.__('Hope Team','church-admin').'</th><th>'.__('Email','church-admin').'</th><th>'.__('Mobile','church-admin').'</th></tr></thead><tfoot><tr><th>'.__('Edit','church-admin').'</th><th>'.__('Delete','church-admin').'</th><th>'.__('Picture','church-admin').'</th><th>'.__('Name','church-admin').'</th><th>'.__('Sex','church-admin').'</th><th>'.__('Person type','church-admin').'</th><th>'.__('Member Level','church-admin').'</th><th>'.__('Site','church-admin').'</th><th>'.__('Ministries','church-admin').'</th><th>'.__('Hope Team','church-admin').'</th><th>'.__('Email','church-admin').'</th><th>'.__('Mobile','church-admin').'</th></tr></tfoot><tbody  class="content">';
	
		}
	    foreach ($people AS $person)
	    {
			$gender=get_option('church_admin_gender');
			
			$sex=$gender[$person->sex];
			//ministries
		$result=$wpdb->get_results('SELECT * FROM '.CA_MET_TBL.' WHERE people_id="'.$person->people_id.'" AND meta_type="ministry"');
		$site=$wpdb->get_var('SELECT venue FROM '.CA_SIT_TBL.' WHERE site_id="'.esc_sql($person->site_id).'"');
		$ministry=array();
		foreach($result AS $row)
		{
				if(!empty($ministries[$row->ID]))$ministry[]=$ministries[$row->ID];
		}
		asort($ministry);
		//hopeteam
		$hopeteamjobs=array();
		$hts=$wpdb->get_results('SELECT job,hope_team_id FROM '.CA_HOP_TBL);
		if(!empty($hts))
		{
		
			foreach($hts AS $ht){$hopeteamjobs[$ht->hope_team_id]=$ht->job;}
		}
		
		$result=$wpdb->get_results('SELECT * FROM '.CA_MET_TBL.' WHERE people_id="'.$person->people_id.'" AND meta_type="hope_team"');
		$hopeteam=array();
		foreach($result AS $row)
		{
				if(!empty($hopeteamjobs[$row->ID]))$hopeteam[]=$hopeteamjobs[$row->ID];
		}
		asort($hopeteam);
		if($person->user_id)
		{
		    $user_info=get_userdata($person->user_id);
		    if(!empty($user_info))$person_user= $user_info->user_login.'<br/>('.church_admin_get_capabilities($person->user_id).')';
		}
		else
		{
		    $person_user='<p><a href="'.wp_nonce_url('admin.php?page=church_admin/index.php&amp;action=church_admin_create_user&amp;people_id='.$person->people_id.'&amp;household_id='.$person->household_id,'create_user').'">'.__('Create WP User','church-admin').'</a></td></tr>';
		}
		if(!empty($person->attachment_id))
		{//photo available
		    $photo= wp_get_attachment_image( $person->attachment_id,'ca-people-thumb' );
		}//photo available
		else
		{
		    $photo= '<img src="'.plugins_url('images/default-avatar.jpg',dirname(__FILE__) ) .'" width="75" height="75"/>';
		}
		if(!empty($person->prefix)){$prefix=$person->prefix.' ';}else{$prefix='';}
		$useNickname=get_option('church_admin_use_nickname');
	    if($useNickname){$nickname=' ('.$person->nickname.') ';}else{$nickname='';}
		echo'<tr class="sortable-row" id="'.esc_html($person->people_id).'"><td><a href="'.wp_nonce_url('admin.php?page=church_admin/index.php&amp;action=edit_people&amp;people_id='.$person->people_id.'&amp;household_id='.$household_id,'edit_people').'">'.__('Edit','church-admin').'</a></td><td><a onclick="return confirm(\'Are you sure you want to delete '.esc_html($person->first_name).' '.esc_html($prefix).esc_html($person->last_name).'?\');" href="'.wp_nonce_url('admin.php?page=church_admin/index.php&amp;action=delete_people&amp;household_id='.$household_id.'&amp;people_id='.$person->people_id.'&amp;household_id='.$household_id,'delete_people').'">'.__('Delete','church-admin').'</a></td><td>'.$photo.'</td><td><a href="'.wp_nonce_url('admin.php?page=church_admin/index.php&amp;action=view_person&amp;people_id='.$person->people_id,'view_person').'">'.esc_html($person->first_name).' '.$nickname.esc_html($prefix).esc_html($person->last_name).'</a></td><td>'.$sex.'</td><td>'.$people_type[$person->people_type_id].'</td><td>'.esc_html($member_type[$person->member_type_id]).'</td><td>'.esc_html($site).'</td><td>'.implode(',<br/>',$ministry).'</td><td>'.implode(',<br/>',$hopeteam).'</td><td>';
		if(is_email($person->email)){echo '<a href="'.esc_url('mailto:'.$person->email).'">'.esc_html($person->email).'</a>';}else{echo esc_html($person->email);}
		echo '</td><td>'.esc_html($person->mobile).'</td>';
		if(church_admin_level_check('Directory'))
		{//only Directory level users gets these columns!
			echo '<td><a href="'.wp_nonce_url('admin.php?page=church_admin/index.php&amp;action=church_admin_move_person&amp;people_id='.$person->people_id,'move_person').'">Move</a></td>';
			if(!empty($person_user)){echo'<td>'.$person_user.'</td>';}else{echo'<td>&nbsp;</td>';}
			
		}
		echo'</tr>';
	    }
	    echo'</tbody></table>';
		   echo '
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
            url: "admin.php?page=church_admin/index.php&action=church_admin_update_order&which=people",
            type: "post",
            data:  Order,
            error: function() {
                console.log("theres an error with AJAX");
            },
            success: function() {
                console.log("Saved.");
            }
        });}
});
$("#sortable tbody.content").disableSelection();
});

   
   
    </script>
';
		require_once(plugin_dir_path(dirname(__FILE__)).'includes/comments.php');
		church_admin_show_comments('household',	$household_id);
	}//end are people
	else
	{//no people
	    echo'<p>'.__('There are no people stored in that household yet','church-admin').'</td></tr>';
	    echo'<p><a href="'.wp_nonce_url('admin.php?page=church_admin/index.php&amp;action=edit_people&amp;household_id='.$household_id,'edit_people').'">'.__('Add someone','church-admin').'</a></td></tr>';
	}//no people
	//end grab people
	if(!empty($add_row->phone))echo'<tr><th scope="row">'.__('Homephone','church-admin').' </th><td>'.esc_html($add_row->phone).'</td></tr>';
	echo '<p><a href="'.wp_nonce_url('admin.php?page=church_admin/index.php&amp;action=edit_household&amp;household_id='.$household_id,'edit_household').'">'.__('Edit Address','church-admin').'</a>: '.esc_html($address).'</p>';
	
	echo'<p>'.$map.'</p>';
	
    }//end address stored
    else
    {
	echo'<div class="notice notice-success inline"><p><strong>'.__('No Household found','church-admin').'</strong></td></tr></div>';
	
    }
}

function church_admin_migrate_users()
{
    global $wpdb;
    $results=$wpdb->get_results('SELECT ID FROM '.$wpdb->users);
    if($results)
    {
	foreach($results AS $row)
	{
	    $check=$wpdb->get_row('SELECT * FROM '.CA_PEO_TBL.' WHERE user_id="'.esc_sql($row->ID).'"');
	    if(!$check)
	    {
		$user_info=get_userdata($row->ID);
		$address='';
		$wpdb->query('INSERT INTO '.CA_HOU_TBL.'(member_type_id,address)VALUES("1","'.$address.'")');
		$household_id=$wpdb->insert_id;
		$wpdb->query('INSERT INTO '.CA_PEO_TBL.' (first_name,last_name,email,household_id,user_id,member_type_id,people_type_id,smallgroup_id,sex) VALUES("'.$user_info->first_name.'","'.$user_info->last_name.'","'.$user_info->user_email.'","'.$household_id.'","'.$row->ID.'","1","1","0","1")');
	    }
	}
	
	echo'<div class="notice notice-success inline"><p><strong>'.__('Wordpress Users migrated','church-admin').'</strong></td></tr></div>';
    }
   
    church_admin_address_list();
}
 /**
 *
 * Move person
 * 
 * @author  Andy Moyle
 * @param    $people_id
 * @return   html
 * @version  0.1
 * 
 */
function church_admin_move_person($people_id)
{
    global $wpdb;
        $data=$wpdb->get_row('SELECT * FROM '.CA_PEO_TBL.' WHERE people_id="'.esc_sql($people_id).'"');
    $message='';
    if(!empty($data))
    {
    	
		if(!empty($_POST['move_person']))
		{
			//handle if person being moved is head of household
			if(!empty($data->head_of_household))
			{//need to reassign head of household
				$message.= sprintf( esc_html__( '%1$s was head of household','church-admin'),$data->first_name.' '.$data->last_name).'<br/>';
				//look for another adult
				$next_person=$wpdb->get_row('SELECT * FROM '.CA_PEO_TBL.' WHERE household_id="'.intval($data->household_id).'" AND people_type_id=1 AND people_id!="'.intval($people_id).'" LIMIT 1');
				if(!empty($next_person))$message.=sprintf( esc_html__( 'Head of household reassigned to %1$s','church-admin'),$next_person->first_name.' '.$next_person->last_name).'<br/>';
				//no adult, find someone!
				if(empty($next_person->people_id))$next_person=$wpdb->get_row('SELECT * from '.CA_PEO_TBL.' WHERE household_id="'.intval($data->household_id).'"  AND people_id!="'.intval($people_id).'" AND people_type_id=1 LIMIT 1');
				if(!empty($next_person))$message.=sprintf( esc_html__( 'Head of household reassigned to %1$s','church-admin'),$next_person->first_name.' '.$next_person->last_name).'<br/>';else{$message='';}
				//set new head of hosuehold
				if(!empty($next_person->people_id))
				{
					$sql='UPDATE '.CA_PEO_TBL.' SET head_of_household=1 WHERE people_id="'.intval($next_person->people_id).'"';
					$wpdb->query($sql);	
				}
				//stop them being head of household!
				$wpdb->query('UPDATE '.CA_PEO_TBL.' SET head_of_household=0 WHERE people_id="'.esc_sql($people_id).'"');
			}
		
	    	if(!empty($_POST['create']))
			{
				$sql='INSERT INTO '.CA_HOU_TBL.' ( address,lat,lng,phone ) SELECT address,lat,lng,phone FROM '.CA_HOU_TBL.' WHERE household_id="'.esc_sql($data->household_id).'";';
			
				$wpdb->query($sql);
				$household_id=$wpdb->insert_id;
				$wpdb->query('UPDATE '.CA_PEO_TBL.' SET household_id="'.esc_sql($household_id).'" WHERE people_id="'.esc_sql($people_id).'"');
				$message.=sprintf( esc_html__( '%1$s has been moved to a new household with teh same address','church-admin'),$data->first_name.' '.$data->last_name);
				echo'<div class="notice notice-success inline"><p><strong>'.$message.'</strong></td></tr></div>';
			
			}
			else
			{
				//remove household entry if only one person was in it.
				$no=$wpdb->get_var('SELECT COUNT(people_id) FROM '.CA_PEO_TBL.' WHERE household_id="'.esc_sql($data->household_id).'"');
				if($no==1)$wpdb->query('DELETE FROM '.CA_HOU_TBL.' WHERE household_id="'.esc_sql($data->household_id).'"');
				//move the person to the new household
				$wpdb->query('UPDATE '.CA_PEO_TBL.' SET household_id="'.esc_sql($_POST['household_id']).'" WHERE people_id="'.esc_sql($people_id).'"');
				$message.=sprintf( esc_html__( '%1$s has been moved','church-admin'),$data->first_name.' '.$data->last_name);
				echo'<div class="notice notice-success inline"><p><strong>'.$message.'</strong></td></tr></div>';
				$household_id=(int)$_POST['household_id'];
			}
	    	church_admin_display_household($household_id);
	   
		}
		else
		{
	   		echo'<div class="wrap"><h2>Move '.esc_html($data->first_name).' '.esc_html($data->last_name).'</h2>';
	    
	    	$results=$wpdb->get_results('SELECT a.last_name,a.first_name, a.household_id,b.member_type FROM '.CA_PEO_TBL.' a, '.CA_MTY_TBL.' b WHERE b.member_type_id=a.member_type_id GROUP BY a.household_id,a.last_name ORDER BY a.last_name');
	    	if(!empty($results))
	    	{
				echo'<form action="" method="post">';
				echo'<tr><th scope="row">'.__('Create a new household with same address','church-admin').'</th><td><input type="checkbox" name="create" value="yes"/></td></tr>';
				echo'<tr><th scope="row">'.__('Move to household','church-admin').'</th><td><select name="household_id"><option value="">'.__('Select a new household...','church-admin').'</option>';
				foreach($results AS $row)
				{
		    		echo'<option value="'.esc_html($row->household_id).'">'.esc_html($row->last_name).', '.esc_html($row->first_name).' '.'('.$row->member_type.')</option>';
				}
				echo'</select></td></tr>';
				echo'<p><input type="hidden" name="move_person" value="yes"/><input type="submit" class="button-primary" value="'.__('Move person','church-admin').'"/></td></tr>';
				echo'</form></div>';
	    	}
		}
    }else{echo'<div class="notice notice-warning inline"><h2>'.__("Oh No! Couldn't find the person you want to move",'church-admin').'</h2></div>';}
}
 /**
 *
 * Create user for all people with email address
 * 
 * @author  Andy Moyle
 * @param    $people_id
 * @return   html
 * @version  0.1
 * 
 */
 function church_admin_users()
 {
 		global $wpdb;
 		echo'<h2>'.__('Create user accounts for every one with an email address','church-admin').'</h2>';
 		if(!empty($_POST['create_users']))
 		{
 			foreach($_POST['member_type_id'] AS $key=>$member_type_id)
 			{
 				$sql='SELECT CONCAT(first_name,last_name) AS username,people_id,household_id FROM '.CA_PEO_TBL.' WHERE member_type_id="'.intval($member_type_id).'"  AND user_id=0 AND email!=""';
				$results=$wpdb->get_results($sql);
				if(!empty($results))
				{
					foreach($results AS $row)church_admin_create_user($row->people_id,$row->household_id,$row->username);
				}

			}
			echo'<div class="notice notice-sucess inline"><h2>'.__('Users created','church-admin').'</h2</div>';
 		}
 		else
 		{
 			echo'<form action="" method="POST">';
 			
 			$member_type=church_admin_member_type_array();
 			foreach($member_type AS $key=>$value)
			{
				echo'<p><input type="checkbox" name="member_type_id[]" value="'.esc_html($key).'" />'.esc_html($value).'</p>';
	   
			}
			echo'<p><input type="hidden" name="create_users" value="yes"/><input type="submit" class="button-primary" value="'.__('Create users','church-admin').'"/></p></form>';
 		}
 
 }
 
 /**
 *
 * Create user
 * 
 * @author  Andy Moyle
 * @param    $people_id,$household_id,$username
 * @return   html
 * @version  0.1
 * 
 */
function church_admin_create_user($people_id,$household_id,$username=NULL)
{
    global $wpdb;
    if(!$people_id)
    {
	echo"<p>'.__('Nobody was specified','church-admin').'</td></tr>";
    }
    else
    {//people_id
	
	$user=$wpdb->get_row('SELECT * FROM '.CA_PEO_TBL.' WHERE people_id="'.esc_sql($people_id).'"');
	if(empty($user))
	{
	    echo'<div class="notice notice-success inline">'.__("That people record doesn't exist",'church-admin').'</td></tr></div>';
	}
	else
	{//user exits in plugin db
	    $user_id=email_exists($user->email);
	    if(!empty($user_id) && $user->user_id==$user_id)
	    {//wp user exists and is in plugin db
			echo'<div class="notice notice-success inline">'.__('User already created','church-admin').'</td></tr></div>';
			church_admin_display_household($household_id);
	    }
	    elseif(!empty($user_id) && $user->user_id!=$user_id)
	    {//wp user exists, update plugin
			$wpdb->query('UPDATE '.CA_PEO_TBL.' SET user_id="'.esc_sql($user_id).'" WHERE people_id="'.esc_sql($people_id).'"');
			echo'<div class="notice notice-success inline">'.__('User updated','church-admin').'</td></tr></div>';
		
	    }
	    else
	    {//wp user needs creating!
		//create unique username
		if(empty($username))$username=strtolower(str_replace(' ','',$user->first_name).str_replace(' ','',$user->middle_name).str_replace(' ','',$user->last_name));
		$x='';
		while(username_exists( $username.$x ))
		{
		    $x+=1;
		}
		$random_password = wp_generate_password( $length=12, $include_standard_special_chars=false );
		$user_id = wp_create_user( $username.$x, $random_password, $user->email );
		
		$message='<p>'.__('The web team at','church-admin').' <a href="'.site_url().'">'.site_url().'</a>'.__('have just created a user login for you','church-admin').'</p>';
		$message.='<p>'.__('Your username is','church-admin').' <strong>'.esc_html($username.$x).'</strong></td></tr>';
		$message.='<p>'.__('Your password is','church-admin').' <strong>'.$random_password.'</strong></td></tr>';
		$app=get_option('church_admin_app_licence');
		if(!empty($app))$message.='<p>We also have an app you can download for <a href="http://www.tinyurl.com/androidChurchApp">Android</a> and <a href="http://www.tinyurl.com/iOSChurchApp">iOS</a>. You can use your username and password for the directory on it!</p>';
		echo '<div class="notice notice-success inline">'.__('User created with username','church-admin').' <strong>'.esc_html($username.$x).'</strong>,'.__('password','church-admin').': <strong>'.$random_password.'</strong> '.__('and this message was queued to them','church-admin').'<br/>'.esc_html($message);
		$headers=array();
		$headers[] = 'From: Web team at '.site_url() .'<'.get_option('admin_email').'>';
		$headers[] = 'Cc: Web team at '.site_url() .'<'.get_option('admin_email').'>';
		add_filter('wp_mail_content_type','church_admin_email_type');
		if(wp_mail($user->email,'Login for '.site_url(),$message,$headers))
		{
		    echo'<strong>'.__('Email sent successfully','church-admin').'</strong></div>';
		}
		else
		{
		    echo'<strong>'.__('Email NOT sent successfully','church-admin').'</strong></div>';
		}
		remove_filter('wp_mail_content_type','church_admin_email_type');
		$wpdb->query('UPDATE '.CA_PEO_TBL.' SET user_id="'.esc_sql($user_id).'" WHERE people_id="'.esc_sql($people_id).'"');
		
	    }//wp user needs creating!
    
	   
	    
	}//user exits in plugin db
    
    
    }//people_id
}//function church_admin_create_user
function church_admin_get_capabilities($id)
{
    if(empty($id))return FALSE;
    $user_info=get_userdata($id);
    if(empty($user_info))return FALSE;
    $cap=$user_info->roles;
    
	if (in_array('subscriber',$cap))return 'Subscriber';
	if (in_array('author',$cap))return 'Author';
	if (in_array('editor',$cap))return  'Editor';
	if (in_array('administrator',$cap)) return 'Administrator';
	return FALSE;
}
 /**
 *
 * Search
 * 
 * @author  Andy Moyle
 * @param    
 * @return   html
 * @version  0.1
 * 
 */
function church_admin_search($search)
{
    global $wpdb,$rota_order;
    echo '<p><a class="button-primary" href="'.wp_nonce_url('admin.php?page=church_admin/index.php&amp;tab=address&action=church_admin_new_household','new_household').'">'.__('Add a Household','church-admin').'</a> </p>';
	echo'<form name="ca_search" action="admin.php?page=church_admin/index.php&tab=address" method="POST"><table class="form-table"><tbody><tr><th scope="row">'.__('Search','church-admin').'</th><td><input name="church_admin_search" style="width:200px;" type="text"/><input type="submit" value="'.__('Go','church-admin').'"/></td></tr></table></form>';
    $s=esc_sql(stripslashes($search));
    //try searching first name, last name, email, mobile separately
	$sql='SELECT DISTINCT household_id FROM '.CA_PEO_TBL.' WHERE CONCAT_WS(" ",first_name,last_name) LIKE("%'.$s.'%")||CONCAT_WS(" ",first_name,prefix,last_name) LIKE("%'.$s.'%")||first_name LIKE("%'.$s.'%")||last_name LIKE("%'.$s.'%")||nickname LIKE("%'.$s.'%")||email LIKE("%'.$s.'%")||mobile LIKE("%'.$s.'%")';
    $results=$wpdb->get_results($sql);
    if(!$results)
    {//try address
		$sql='SELECT DISTINCT household_id FROM '.CA_HOU_TBL.' WHERE address LIKE("%'.$s.'%")||phone LIKE("%'.$s.'%")';
		$results=$wpdb->get_results($sql);
    }
	
    if($results)
    {
	    
	    echo '<h2>'.__('Address List Results','church-admin').' for "'.esc_html($search).'"</h2><table class="widefat striped"><thead><tr><th>'.__('Delete','church-admin').'</th><th>'.__('Last name','church-admin').'</th><th>'.__('First Name(s)','church-admin').'</th><th>'.__('Address','church-admin').'</th><th>'.__('Last Update','church-admin').'</th></tr></thead><tfoot><tr><th>'.__('Delete','church-admin').'</th><th>'.__('Last name','church-admin').'</th><th>'.__('First Name(s)','church-admin').'</th><th>'.__('Address','church-admin').'</th><th>'.__('Last Update','church-admin').'</th></tr></tfoot><tbody>';
		foreach($results AS $row)
		{
	    
	    //grab address
	    $add_row=$wpdb->get_row('SELECT * FROM '.CA_HOU_TBL.' WHERE household_id="'.esc_sql($row->household_id).'"');
	     //grab people
	    $people_results=$wpdb->get_results('SELECT first_name,middle_name,nickname,last_name,people_type_id,people_id FROM '.CA_PEO_TBL.' WHERE household_id="'.esc_sql($row->household_id).'" ORDER BY people_type_id ASC,sex DESC');
	    $adults=$children=array();
	    foreach($people_results AS $people)
	    {
	    	$useNickname=get_option('church_admin_use_nickname');
	    	if($useNickname&&!empty($row->nickname)){$nickname='('.$row->nickname.')';}else{$nickname=NULL;}
	    	$name=array_filter(array($people->first_name,$people->middle_name,$nickname));
		if($people->people_type_id=='1')
		{
			$last_name='';
			if(!empty($row->prefix))$last_name.=$people->prefix.' ';
			$last_name.=$people->last_name; 
			$adults[]='<a href="'.wp_nonce_url('admin.php?page=church_admin/index.php&amp;action=edit_people&amp;household_id='.$row->household_id.'&amp;people_id='.$people->people_id,'edit_people').'">'.esc_html(implode(' ',$name)).'</a>';
		}
		else
		{
			$children[]='<a href="'.wp_nonce_url('admin.php?page=church_admin/index.php&amp;action=edit_people&amp;household_id='.$row->household_id.'&amp;people_id='.$people->people_id,'edit_people').'">'.esc_html(implode(' ',$name)).'</a>' ;}
		
	    }
	    $adult=implode(" & ",$adults);
	    if(!empty($children)){$kids=' ('.implode(", ",$children).')';}else{$kids='';}
	    $add='';
		if(!empty($add_row->address)){$add=esc_html($add_row->address);}else{$add='&nbsp;';}
	    if(!empty($add_row->ts)){$ts=$add_row->ts;}else{$ts=date('Y-m-d');}
	    if(!empty($add)){$address='<a href="'.wp_nonce_url('admin.php?page=church_admin/index.php&amp;action=edit_household&amp;household_id='.$row->household_id,'edit_household').'">'.esc_html($add).'</a>';}else{$address='<a href="'.wp_nonce_url('admin.php?page=church_admin/index.php&amp;action=edit_household&amp;household_id='.$row->household_id,'edit_household').'">Add Address</a>';}
	    
	    $delete='<a onclick="return confirm(\''.__('Are you sure?','church-admin').'\');" href="'.wp_nonce_url('admin.php?page=church_admin/index.php&amp;action=delete_household&amp;household_id='.$row->household_id,'delete_household').'">'.__('Delete Household','church-admin').'</a>';
	    echo '<tr><td>'.$delete.'</td><td><a href="'.wp_nonce_url('admin.php?page=church_admin/index.php&amp;action=display_household&amp;household_id='.$row->household_id,'display_household').'">'.$last_name.'</a></td><td>'.$adult.' '.$kids.'</td><td>'.$address.'</td><td>'.mysql2date('d/M/Y',$ts).'</td></tr>';
	    
	    
		}
		echo '</tbody></table>';
	
	
	
    }//directory results
	else{echo'<p>"'.esc_html($search).'" '.__('not found in directories','church-admin').'.</p>';}
	$people_id=church_admin_get_one_id($search);
	$serial='s:'.strlen($people_id).':"'.$people_id.'";';
	$serviceResults=$wpdb->get_results('SELECT * FROM '.CA_SER_TBL);
	if(!empty($serviceResults))
	{
		$services=array();
		foreach($serviceResults AS $serviceRow)$services[$serviceRow->service_id]=$serviceRow->service_name;
	}
	//search rota
	$sql = 'SELECT * FROM '.CA_ROT_TBL.' WHERE rota_jobs LIKE  "%'.esc_sql($serial).'%" AND rota_date>="'.date('Y-m-d').'"';
	$dateResults=$wpdb->get_results($sql);
	if(!empty($dateResults))
	{
			$allRotaJobs=$wpdb->get_results('SELECT * FROM '.CA_RST_TBL.'  ORDER by rota_order');
			//extract ids and name of rota job for current service
			$rotaJobs=array();
			if(!empty($allRotaJobs))
			{
			
				foreach($allRotaJobs AS $eachRotaJob)
				{
					$rotaJobs[$eachRotaJob->rota_id]=$eachRotaJob->rota_task;
				}
				//we now have an array $rotaJobs that contains id as key and name of job as value
			
			}
			echo'<p>'.__('Click a table cell to edit it','church-admin').'</p>';
			
			$thead='<tr><th>'.__('Edit','church-admin').'</th><th>'.__('Delete','church-admin').'</th><th width="100">'.__('Date','church-admin').'</th>';
			foreach($rotaJobs AS $id=>$value)$thead.='<th>'.esc_html($value).'</th>';
			$thead.='</tr>';
			//output table header
			echo '<table class="widefat striped"><thead>'.$thead.'</thead><tfoot>'.$thead.'</tfoot><tbody>';
			$date_options='';
			foreach($dateResults AS $dateRow)
			{
				//form data for copy cell
				$date_options.='<option value="'.intval($dateRow->rota_id).'">'.mysql2date(get_option('date_format'),$dateRow->rota_date).'</option>';
				//links
				$edit_url='admin.php?page=church_admin/index.php&tab=rota&action=church_admin_edit_rota&id='.intval($dateRow->rota_id);
				$delete_url='admin.php?page=church_admin/index.php&tab=rota&action=church_admin_delete_rota&id='.intval($dateRow->rota_id);
				//current rota'd people
				$currentData=maybe_unserialize($dateRow->rota_jobs);
				if(empty($currentData))
				{
					//if no people stored for any jobs yet, form an empty array using keys from $rotaJobs
					$currentData=array();
					foreach($rotaJobs AS $key=>$value)$currentData[$key]=__('Click to edit','church-admin');
				}
				//start row
				echo '<tr><td><a href="'.wp_nonce_url($edit_url, 'edit_rota').'">'.__('Edit','church-admin').'</a></td>';
				echo'<td><a onclick="return confirm(\''.__('Are you sure?','church-admin').'\');" href="'.wp_nonce_url($delete_url, 'delete_rota').'">'.__('Delete','church-admin').'</a></td>';
				echo'<td>'.mysql2date('jS M Y',$dateRow->rota_date).'<br/>'.$services[$dateRow->service_id].'</td>';
				foreach($rotaJobs AS $id=>$value)
				{
					$people='';
					if(!empty($currentData[$id]))$people=church_admin_get_people($currentData[$id]);
					
					echo'<td class="edit" id="'.esc_html($value).'~'.intval($dateRow->rota_id).'">'.esc_html($people).'</td>';
				}
				echo'</tr>';
			}
			echo'</tbody></table>';
			echo'<script type="text/javascript">jQuery(document).ready(function($) {$(".edit").editable(ajaxurl,{submitdata: {action: "ajax_rota_edit",security:"'.wp_create_nonce('ajax_rota_edit').'"},onblur : "submit"}); });</script>';
			

	}else{echo'<p>'.esc_html($search).' '.__('not found in rotas','church-admin').'</p>';}
	//search podcast
	$upload_dir = wp_upload_dir();
	$path=$upload_dir['basedir'].'/sermons/';
	$url=content_url().'/uploads/sermons/';
	$results=$wpdb->get_results('SELECT * FROM '.CA_FIL_TBL.' WHERE file_title LIKE "%'.$s.'%" OR file_description LIKE "%'.$s.'%" OR speaker LIKE "%'.esc_sql($serial).'%" OR speaker LIKE "%'.$s.'%" ORDER BY pub_date DESC');
	if(!empty($results))
	{
		echo '<h2>'.__('Sermon Podcast Results for ','church-admin').'"'.esc_html($search).'"</h2>';
		$table='<table class="widefat striped"><thead><tr><th>'.__('Edit','church-admin').'</th><th>'.__('Delete','church-admin').'</th><th>'.__('Publ. Date','church-admin').'</th><th>'.__('Title','church-admin').'</th><th>'.__('Speakers','church-admin').'</th><th>'.__('Mp3 File','church-admin').'</th></th><th>'.__('File Okay?','church-admin').'</th><th>'.__('Length','church-admin').'</th><th>'.__('Media','church-admin').'</th><th>'.__('Transcript','church-admin').'</th><th>'.__('Event','church-admin').'</th><th>'.__('Shortcode','church-admin').'</th></tr></thead>'."\r\n".'<tfoot><tr><th>'.__('Edit','church-admin').'</th><th>'.__('Delete','church-admin').'</th><th>'.__('Publ. Date','church-admin').'</th><th>'.__('Title','church-admin').'</th><th>'.__('Speakers','church-admin').'</th><th>'.__('Mp3 File','church-admin').'</th></th><th>'.__('File Okay?','church-admin').'</th><th>'.__('Length','church-admin').'</th><th>'.__('Media','church-admin').'</th><th>'.__('Transcript','church-admin').'</th><th>'.__('Event','church-admin').'</th><th>'.__('Shortcode','church-admin').'</th></tr></tfoot>'."\r\n".'<tbody>';
        foreach($results AS $row)
        {
            if(file_exists(plugin_dir_path( $path.$row->file_name))){$okay='<img src="'.plugins_url('images/green.png',dirname(__FILE__) ) .'" width="32" height="32"/>';}else{$okay='<img src="'.plugins_url('images/red.png',dirname(__FILE__) ) .'" width="32" height="32"/>';}
            $edit='<a href="'.wp_nonce_url('admin.php?page=church_admin/index.php&amp;action=edit_file&amp;id='.$row->file_id,'edit_podcast_file').'">Edit</a>';
            $delete='<a onclick="return confirm(\''.__('Are you sure?','church-admin').'\');" href="'.wp_nonce_url('admin.php?page=church_admin/index.php&amp;action=delete_file&amp;id='.$row->file_id,'delete_podcast_file').'">'.__('Delete','church-admin').'</a>';
            $series_name=$wpdb->get_var('SELECT series_name FROM '.CA_SERM_TBL.' WHERE series_id="'.esc_sql($row->series_id).'"');
            if(!empty($row->file_name)&&file_exists($path.$row->file_name)){$file='<a href="'.$url.esc_url($row->file_name).'">'.esc_html($row->file_name).'</a>';$okay='<img src="'.plugins_url('images/green.png',dirname(__FILE__) ) .'"/>';}
			elseif(!empty($row->external_file)){$file='<a href="'.esc_url($row->external_file).'">'.esc_html($row->external_file).'</a>';$okay='<img src="'.plugins_url('images/green.png',dirname(__FILE__) ) .'"/>';}
			else{$file='&nbsp;';$okay='<img src="'.plugins_url('images/red.png',dirname(__FILE__) ).'"/>';}
            $table.='<tr><td>'.$edit.'</td><td>'.$delete.'</td><td>'.date(get_option('date_format'),strtotime($row->pub_date)).'</td><td>'.esc_html($row->file_title).'</td><td>'.esc_html(church_admin_get_people($row->speaker)).'</td><td>'.$file.'</td><td>'.$okay.'</td><td>'.esc_html($row->length).'</td><td>'.$row->video_url.'</td>';
            if(file_exists($path.$row->transcript)){$table.='<td><a href="'.esc_url($url.$row->transcript).'">'.esc_html($row->transcript).'</a></td>';}else{$table.='<td>&nbsp;</td>';}
            $table.='<td>'.esc_html($series_name).'</td><td>[church_admin type="podcast" file_id="'.intval($row->file_id).'"]</td></tr>';
        }
        
        $table.='</tbody></table>';
        echo $table;
	}else{echo'<p>'.esc_html($search).' '.__('not found in sermon podcasts','church-admin').'</p>';}
	//search calendar
	
}


 /**
 *
 * Import CSV
 * 
 * @author  Andy Moyle
 * @param    
 * @return   html
 * @version  0.1
 * 
 */
function church_admin_import_csv()
{
		global $wpdb;
			$church_admin_marital_status=array(0=>__('N/A','church-admin'),1=>__('Single','church-admin'),2=>__('Co-habiting','church-admin'),3=>__('Married','church-admin'),4=>__('Divorced','church-admin'),5=>__('Widowed','church-admin'));
	if(!empty($_POST['process']))
	{ 
		echo'<p>'.__('Processing','church-admin').'</p>';
		if(!empty($_POST['overwrite']))
		{
			$wpdb->query('TRUNCATE TABLE '.CA_PEO_TBL);
			$wpdb->query('TRUNCATE TABLE '.CA_HOU_TBL);
			$wpdb->query('TRUNCATE TABLE '.CA_MET_TBL);
			update_option('church_admin_gender',array(1=>__('Male','church-admin'),0=>__('Female','church-admin')));
			echo'<p>'.__('Tables truncated','church-admin').'</p>';
		}
		
		foreach($_POST AS $key=>$value)
		{
			if(substr($key,0,6)=='column') 
			{
				$column=substr($key,6);
				switch($value)
				{
					case'first_name':$first_name=$column;break;
					case'middle_name':$middle_name=$column;break;
					case'nickname':$nickname=$column;break;
					case'prefix':$prefix=$column;break;
					case'last_name':$last_name=$column;break;
					case'sex':$sex=$column;break;
					case'marital_status':$marital_status=$column;break;
					case'date_of_birth':$date_of_birth=$column;break;
					case'email':$email=$column;break;
					case'mobile':$mobile=$column;break;
					case'phone':$phone=$column;break;
					case'address':$address=$column;break;
					case'street_address':$street_address=$column;break;
					case'city':$city=$column;break;
					case'state':$state=$column;break;
					case'zip_code':$zipcode=$column;break;
					case'small_group':$small_group=$column;break;
					case'member_type':$member_type=$column;break;
					
				}
				
			}
			
		}
		ini_set('auto_detect_line_endings',TRUE);
		if (($handle = fopen($_POST['path'], "r")) !== FALSE) 
		{
			echo'<p>'.__('Begin file Processing','church-admin').'</p>';
			$header=fgetcsv($handle, '', ",");
			echo'<p>'.__('Got header','church-admin').'</p>';
			while (($data = fgetcsv($handle, 0, ",")) !== FALSE) 
			{
				$head_of_household=1;//reset to 1each time. Set to 0 if address already stored, which implies head already stored.
				//household
				$household_id=NULL;
				$add='';
				if(!empty($address)&&!empty($data[$address]))
				{
					$ad=array(sanitize_text_field($data[$address]));
					if(!empty($data['city']))$ad[]=sanitize_text_field($data[$city]);
					if(!empty($data['state']))$ad[]=sanitize_text_field($data[$state]);
					if(!empty($data['zipcode']))$ad[]=sanitize_text_field($data[$zipcode]);
					$add=implode(',',$ad);
					
				}
				
				if(!empty($phone)&&!empty($data[$phone])){$ph=sanitize_text_field($data[$phone]);}else{$ph=NULL;}
				$sql='SELECT household_id FROM '.CA_HOU_TBL.' WHERE address="'.esc_sql($add).'" AND phone="'.esc_sql($ph).'"';
				$household_id=$wpdb->get_var($sql);
				if(empty($household_id))
				{//insert
					$sql='INSERT INTO '.CA_HOU_TBL.' (address,phone)VALUES("'.esc_sql($add).'","'.esc_sql($ph).'")';
				
					$wpdb->query($sql);
					$household_id=$wpdb->insert_id;
				}else
				{
					$head_of_household=0;//person stored for that household already
				}
				//member type
				if(!empty($member_type))
				{
					$mt=sanitize_text_field($data[$member_type]);
					$member_type_id=$wpdb->get_var('SELECT member_type_id FROM '.CA_MTY_TBL.' WHERE member_type="'.esc_sql($mt).'"');
					if(empty($member_type_id))
					{
						$wpdb->query('INSERT INTO '.CA_MTY_TBL.' (member_type)VALUES("'.esc_sql($mt).'")');
						$member_type_id=$wpdb->insert_id;
					}
				}else
				{
					$member_type_id=1;
					$check=$wpdb->get_var('SELECT member_type_id FROM '.CA_MTY_TBL.' WHERE member_type_id=1' );
					if(!$check)
					{
						$wpdb->query('INSERT INTO '.CA_MTY_TBL.' (member_type)VALUES("'.__('Member','church-admin').'")');
						$member_type_id=$wpdb->insert_id;
					}
				}
				//people
				//gender
				$gender=get_option('church_admin_gender');
				if(!empty($sex)&&!empty($data[$sex]))
				{
					$malefemale=array_search($data[$sex],$gender);
					if(empty($sex))
					{
						$gender[]=sanitize_text_field($data[$sex]);
						update_option('church_admin_gender',$gender);
					}
					$malefemale=(int)array_search($data[$sex],$gender);
				}else $malefemale=1;
				if(!empty($date_of_birth)&&!empty($data[$date_of_birth]))
				{
					$dob=date('Y-m-d',strtotime($data[$date_of_birth]));
					if(empty($dob)) $dob='0000-00-00';
				}else{$dob='0000-00-00';}
					
				$church_admin_marital_status=array(
				0=>__('N/A','church-admin'),
				1=>__('Single','church-admin'),
				2=>__('Co-habiting','church-admin'),
				3=>__('Married','church-admin'),
				4=>__('Divorced','church-admin'),
				5=>__('Widowed','church-admin')
				);			if(!isset($marital_status)||!(in_array($data[$marital_status],$church_admin_marital_status))){$data[$marital_status]=__('N/A','church-admin');}else{$data[$marital_status]=$data[$marital_status];}
				if(!isset($first_name)||empty($data[$first_name])){$data['first_name']=NULL;}else{$data['first_name']=$data[$first_name];}
				if(!isset($middle_name)||empty($data[$middle_name])){$data['middle_name']=NULL;}else{$data['middle_name']=$data[$middle_name];}
				if(!isset($nickname)||empty($data[$nickname])){$data['nickname']=NULL;}else{$data['nickname']=$data[$nickname];}
				if(!isset($prefix)||empty($data[$prefix])){$data['prefix']=NULL;}else{$data['prefix']=$data[$prefix];}
				if(!isset($last_name)||empty($data[$last_name])){$data['last_name']=NULL;}else{$data['last_name']=$data[$last_name];}
				if(!isset($mobile)||empty($data[$mobile])){$data['mobile']=NULL;}else{$data['mobile']=$data[$mobile];}
				if(!isset($email)||empty($data[$email])){$data['email']=NULL;}else{$data['email']=$data[$email];}
				
				$sql='INSERT INTO '.CA_PEO_TBL.' (first_name,middle_name,nickname,prefix,last_name,email,mobile,sex,date_of_birth,member_type_id,household_id,people_type_id,facebook,twitter,instagram,head_of_household,marital_status)VALUES("'.esc_sql(sanitize_text_field($data['first_name'])).'","'.esc_sql(sanitize_text_field($data['middle_name'])).'","'.esc_sql(sanitize_text_field($data['nickname'])).'","'.esc_sql(sanitize_text_field($data['prefix'])).'","'.esc_sql(sanitize_text_field($data['last_name'])).'","'.esc_sql(sanitize_text_field($data['email'])).'","'.esc_sql(sanitize_text_field($data['mobile'])).'","'.$malefemale.'","'.$dob.'","'.esc_sql($member_type_id).'","'.esc_sql($household_id).'","1","","","","'.$head_of_household.'","'.esc_sql(sanitize_text_field($data['marital_status'])).'")';
				$wpdb->query($sql);
				echo '<p>'.__('Added','church-admin').' '.sanitize_text_field($data[$first_name]).' '.sanitize_text_field($data[$last_name]).'</p>';
				
		
			}
			echo'<p>'.__('Finished file Processing','church-admin').'</p>';
		}
		fclose($handle);
		
		
	}
	elseif(!empty($_POST['save_csv']))
	{
		if(!empty($_FILES) && $_FILES['file']['error'] == 0)
		{
			$filename = $_FILES['file']['name'];
			$upload_dir = wp_upload_dir();
			$filedest = $upload_dir['path'] . '/' . $filename;
			if(move_uploaded_file($_FILES['file']['tmp_name'], $filedest))echo '<p>'.__('File Uploaded and saved','church-admin').'</p>';
			
			ini_set('auto_detect_line_endings',TRUE);
			$file_handle = fopen($filedest, "r");
			$header=fgetcsv($file_handle, '', ",");
			 
			
			
			echo'<form  action="" method="post"><table >';
			echo'<input type="hidden" name="path" value="'.$filedest.'"/><input type="hidden" name="process" value="yes"/>';
			if(!empty($_POST['overwrite']))echo'<input type="hidden" name="overwrite" value="yes"/>';
			echo'<tr><th scope="row">'.__('Your Header','church-admin').'</th><th scope="row">'.__('Maps to','church-admin').'</th></tr>';
			foreach($header AS $key=>$value)
			{
				echo'<tr><th scope="row">'.esc_html($value).'</th><td>';
				echo'<select name="column'.$key.'">';
				echo'<option name="unused">'.__('Unused','church-admin').'</option>';
				echo'<option value="first_name">'.__('First Name','church-admin').'</option>';
				echo'<option value="middle_name">'.__('Middle Name','church-admin').'</option>';
				echo'<option value="nickname">'.__('Nickname','church-admin').'</option>';
				echo'<option value="prefix">'.__('Prefix','church-admin').'</option>';				
				echo'<option value="last_name">'.__('Last Name','church-admin').'</option>';
				echo'<option value="sex">'.__('Gender','church-admin').'</option>';
				echo'<option value="marital_status">'.__('Marital Status','church-admin').'</option>';
				echo'<option value="date_of_birth">'.__('Date of Birth','church-admin').'</option>';
				echo'<option value="email">'.__('Email Address','church-admin').'</option>';
				echo'<option value="mobile">'.__('Mobile','church-admin').'</option>';
				echo'<option value="phone">'.__('Home phone','church-admin').'</option>';
				echo'<option value="address">'.__('Address','church-admin').'</option>';
				echo'<option value="city">'.__('City','church-admin').'</option>';
				echo'<option value="state">'.__('State','church-admin').'</option>';
				echo'<option value="zip_code">'.__('Zip Code','church-admin').'</option>';
				echo'<option value="small_group">'.__('Small Group','church-admin').'</option>';
				echo'<option value="member_type">'.__('Member Type','church-admin').'</option>';
				echo'</select>';
				echo'</td></tr>';
			}
			echo'<tr><td colspan="2"><input type="submit" class="button" value="'.__('Save','church-admin').'"/></td></tr></table></form>';
		}
	}
	else
	{
		echo'<h2>'.__('Import csv - please save spreadsheet as a CSV file before uploading!','church-admin').'</h2>';
		echo'<form action="" method="POST" enctype="multipart/form-data">';
		echo'<p><label>'.__('CSV File with 1st row as headers','church-admin').'</label><input type="file" name="file"/><input type="hidden" name="save_csv" value="yes"/></p>';
		echo'<p><label>'.__('Overwrite current address details?','church-admin').'</label><input type="checkbox" name="overwrite" value="yes"/></p>';
		echo'<p><input  class="button-primary" type="submit" Value="'.__('Upload','church-admin').'"/></p></form>';
	}
}
/**
 * add new household.
 *
 * @param 
 * @param html display new household
 *
 * @author andy_moyle
 * 
 */
function church_admin_new_household()
{

//2016-04-14 Allow duplicate entries
//v1.05 add middle name

	global $wpdb,$people_type;
	$church_admin_marital_status=array(0=>__('N/A','church-admin'),1=>__('Single','church-admin'),2=>__('Co-habiting','church-admin'),3=>__('Married','church-admin'),4=>__('Divorced','church-admin'),5=>__('Widowed','church-admin'));
	$member_type=church_admin_member_type_array();
	$people_type=get_option('church_admin_people_type');
	if(!empty($_POST['save']))
	{//process

		$form=$sql=array();
		foreach ($_POST AS $key=>$value)$form[$key]=stripslashes_deep($value);
		
			$success=$wpdb->query('INSERT INTO '.CA_HOU_TBL.' (address,lat,lng,phone) VALUES("'.esc_sql(sanitize_text_field($form['address'])).'", "'.esc_sql(sanitize_text_field($form['lat'])).'","'.esc_sql(sanitize_text_field($form['lng'])).'","'.esc_sql(sanitize_text_field($form['phone'])).'" )');
			$household_id=$wpdb->insert_id;
		
		$sql=array();
        for($x=0;$x<count($_POST['first_name']);$x++)
        {
			$y=$x+1;
			if($x==0){$head_of_household=1;}else{$head_of_household=0;}
            if(isset($_POST['sex'][$x])){$sex=(int)($form['sex'][$x]);}else{$sex='1';}//needs to be isset to allow for value of 0
            if(!empty($_POST['first_name'][$x])){$first_name=sanitize_text_field($form['first_name'][$x]);}else{$first_name='';}
            if(!empty($_POST['middle_name'][$x])){$middle_name=sanitize_text_field($form['middle_name'][$x]);}else{$middle_name='';}
            if(!empty($_POST['nickname'][$x])){$nickname=sanitize_text_field($form['nickname'][$x]);}else{$nickname='';}
			if(!empty($_POST['prefix'][$x])){$prefix=sanitize_text_field($form['prefix'][$x]);}else{$prefix='';}
            if(!empty($_POST['last_name'][$x])){$last_name=sanitize_text_field($form['last_name'][$x]);}else{$last_name='';}
            if(!empty($_POST['mobile'][$x])){$mobile=sanitize_text_field($form['mobile'][$x]);}else{$mobile='';}
            if(!empty($_POST['twitter'][$x])){$twitter=sanitize_text_field($form['twitter'][$x]);}else{$twitter='';}
            if(!empty($_POST['facebook'][$x])){$facebook=sanitize_text_field($form['facebook'][$x]);}else{$facebook='';}
            if(!empty($_POST['instagram'][$x])){$instagram=sanitize_text_field($form['instagram'][$x]);}else{$instagram='';}
            if(!empty($_POST['email'][$x])){$email=sanitize_text_field($form['email'][$x]);}else{$email='';}
            if(!empty($_POST['people_type_id'][$x])){$people_type_id=intval($form['people_type_id'][$x]);}else{$people_type_id='';}
            if(!empty($_POST['site_id'][$x])){$site_id=intval($form['site_id'][$x]);}else{$site_id='';}
			if(!empty($_POST['member_type_id'][$x])){$member_type_id=(int)($form['member_type_id'][$x]);}else{$member_type_id=1;}
			
			if(!empty($_POST['date_of_birth'][$x])&& church_admin_checkdate($_POST['date_of_birth'][$x])){$dob=esc_sql($_POST['date_of_birth'][$x]);}else{$dob='0000-00-00';}
			if(empty($_POST['date_of_birth'][$x])&!empty($_POST['date_of_birthx'][$x]))$dob=date('Y-m-d',strtotime($_POST['date_of_birthx'][$x]));
			if(!empty($_POST['marital_status'][$x])){$marital_status=sanitize_text_field($form['marital_status'][$x]);}else{$marital_status='';}
			
			$sql= 'INSERT INTO '.CA_PEO_TBL.' (first_name,middle_name,nickname,prefix,last_name,head_of_household,date_of_birth,mobile,email,sex,household_id,people_type_id,member_type_id,marital_status,site_id,facebook,twitter,instagram) VALUES ("'.esc_sql($first_name).'","'.esc_sql($middle_name).'","'.esc_sql($nickname).'","'.esc_sql($prefix).'","'.esc_sql($last_name).'","'.$head_of_household.'","'.$dob.'","'.esc_sql($mobile).'","'.esc_sql($email).'","'.$sex.'","'.esc_sql($household_id).'","'.esc_sql((int)$people_type_id).'","'.$member_type_id.'","'.esc_sql($marital_status).'","'.esc_sql($site_id).'","'.esc_sql($facebook).'","'.esc_sql($twitter).'","'.esc_sql($instagram).'")';
           
            $wpdb->query($sql);
            $people_id=$wpdb->insert_id;
            church_admin_update_people_meta(intval($_POST['small_group_id'][$x]),$people_id,'smallgroup');
            
        }
		
        
        echo '<div class="notice notice-success inline"><p>'.__('Household Added','church-admin').'</p></div>';
		church_admin_display_household($household_id);
    }//end process
	else
	{
		echo '<h2>'.__('Add new household','church-admin').'</h2>';
		//echo'<p>'.__('This section is now much simpler. You can edit individual people later with more details like small group, ministries and date of birth','church-admin').'</p>';
        echo '<form action="" method="post"><input type="hidden" name="save" value="yes"/>';
        echo '<div class="clonedInput" id="input1">';
		echo'<h3>'.__('Person','church-admin').'</h3><table class="form-table">';
        echo '<tr><th scope="row">'.__('First Name','church-admin').'</th><td><input type="text" class="first_name" id="first_name1" name="first_name[]"/></td></tr>';
        $middle_name=get_option('church_admin_use_middle_name');
        if($middle_name)echo '<tr><th scope="row">'.__('Middle Name','church-admin').'</th><td><input type="text" class="middle_name" id="middle_name1" name="middle_name[]" /></td></tr>';
        $nickname=get_option('church_admin_use_nickname');
        if($nickname)echo '<tr><th scope="row">'.__('Nickname','church-admin').'</th><td><input type="text" class="nickname" id="nickname1" name="nickname[]" /></td></tr>';
        $use_prefix=get_option('church_admin_use_prefix');
        
        if($use_prefix)echo '<tr><th scope="row">'.__('Prefix (e.g.van der)','church-admin').'</th><td><input type="text" class="prefix" id="prefix1" name="prefix[]" /></td></tr>';
        echo '<tr><th scope="row">'.__('Last Name','church-admin').'</th><td><input type="text" class="last_name" id="last_name1" name="last_name[]"/></td></tr>';
        echo '<tr><th scope="row">'.__('Date of birth (yyyy-mm-dd)','church-admin').'</th><td>'.church_admin_date_picker(NULL,'date_of_birth',TRUE,1910,date('Y')).'</td></tr>';
       
        echo '<tr><th scope="row">'.__('Mobile','church-admin').'</th><td><input type="text" class="mobile" id="mobile1" name="mobile[]"/></td></tr>';
        //twitter
		echo'<tr><th scope="row">Twitter</th><td>@<input type="text" name="twitter[]" class="twitter" id="twitter1" /></td></tr>'."\r\n";
		//facebook
		echo'<tr><th scope="row">Facebook</th><td><input type="text" name="facebook[]" class="facebook" id="facebook" /></td></tr>'."\r\n";
		//instagram
		echo'<tr><th scope="row">Instagram</th><td><input type="text" name="instagram[]" class="instagram" id="instagram1"/></td></tr>'."\r\n";
        echo '<tr><th scope="row">'.__('Person type','church-admin').'</th><td><select name="people_type_id[]" id="people_type1" class="people_type_id">';
        foreach($people_type AS $id=>$type){echo '<option value="'.$id.'">'.$type.'</option>';}
        echo '</select></td></tr>';
		 echo '<tr><th scope="row">'.__('Member type','church-admin').'</th><td><select name="member_type_id[]" id="member_type1" class="member_type_id">';
        foreach($member_type AS $id=>$type){echo '<option value="'.$id.'">'.$type.'</option>';}
        echo '</select></td></tr>';
        //small group
	
		//$groups=church_admin_get_people_meta($data->people_id,'smallgroup');
		
		echo'<tr><th scope="row">'.__('Small Group','church-admin').'</th><td><select name="small_group_id[]" id="small_group1" class="small_group">';
		$smallgroups=$wpdb->get_results('SELECT * FROM '.CA_SMG_TBL);
		
		foreach($smallgroups AS $smallgroup)
		{
			
			echo'<option value="'.intval($smallgroup->id).'">'.esc_html($smallgroup->group_name).'</option>';
		}
		echo '</select></td></tr>';
		//site
		$sites=$wpdb->get_results('SELECT venue,site_id FROM '.CA_SIT_TBL.' ORDER BY venue ASC');
		echo'<tr><th scope="row">'.__('Site','church-admin').'</th><td><select name="site_id[]" id="site_id1" class="site_id">';
		foreach($sites AS $site)
		{
			echo'<option value="'.intval($site->site_id).'">'.esc_html($site->venue).'<option>';
		}
		echo'</select></td></tr>';
		
        echo '<tr><th scope="row">'.__('Email','church-admin').'</th><td><input type="text" class="email" id="email1" name="email[]"/></td></tr>';
        //gender
        $gender=get_option('church_admin_gender');
		echo '<tr><th scope="row">'.__('Gender','church-admin').'</th><td><select name="sex[]" class="sex" id="sex1">';
		
		foreach($gender AS $key=>$value){echo  '<option value="'.esc_html($key).'">'.esc_html($value).'</option>';}
		echo '</select></td></tr>';
		echo '<tr><th scope="row">'.__('Marital Status','church-admin').'</th><td><select name="maritalstatus[]" class="marital_status" id="marital_status">';
		//marital status
		foreach($church_admin_marital_status AS $key=>$status){echo '<option value="'.esc_html($status).'">'.esc_html($status).'</option>';}
		echo '</select></td></tr>';
		echo'</table></div>';
    
        
        echo '<p id="jquerybuttons"><input type="button" id="btnAdd" value="'.__('Add another person','church-admin').'" /><input type="button" id="btnDel" value="'.__('Remove person','church-admin').'" /></p>';;
        
         echo'<script type="text/javascript">
         		jQuery("body").on("focus",".date_of_birth", function(){
         		
    			jQuery(this).datepicker({dateFormat : "yy-mm-dd", changeYear: true ,yearRange: "1910:'.date('Y').'"});
			});</script>';
			
			
        echo '<p><label>'.__('Phone','church-admin').'</label><input name="phone" type="text"/></p>';
        echo church_admin_address_form(NULL,NULL);
       
		echo'<p><label>'.__('Private (not shown publicly)','church-admin').'</label><input type="checkbox" name="private" value="1" /></p>'; 
        echo  '<p><input  class="button-primary" type="submit" value="'.__('Save','church-admin').'"/></form>';
        
    }//form
		
	
}




?>