<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

function church_admin_front_end_register($email_verify=TRUE,$admin_email=TRUE,$member_type_id=1)
{
	        /**
 *
 * Front End Registration
 * 
 * @author  Andy Moyle
 * @param    $email_verify,$admin_email
 * @return   
 * @version  0.3
 *
 * 0.2 fixed address save
 * 0.3 added recaptcha service
 * 
 */
    global $wpdb,$people_type;
    $user = wp_get_current_user();
    //only use $_GET['household_id'] if logged in user is from that household or an admin!
    if(!empty($_GET['household_id'])&& !is_user_logged_in ())
    {
    	$out='<p>'.__('Please login to edit your entry','church-admin').'</p>';
    	$out.=wp_login_form(array('echo'=>FALSE));
    }else{
    
    $check=$wpdb->get_var('SELECT household_id FROM '.CA_PEO_TBL.' WHERE people_id="'.esc_sql($user->ID).'"');
	if((!empty($check)&&($_GET['household_id'])&&$check==$_GET['household_id'])){$household_id=intval($_GET['household_id']);}else{$household_id=NULL;}

    if(!ctype_digit($member_type_id))$member_type_id=1;
    
    
    $out='';
    

    if(!empty($_POST['save'])  && wp_verify_nonce($_POST['church_admin_register'], 'church_admin_register')   )//add verify nonce
    {//process
      	if(empty($_POST['ItsAllAboutJesus'])){$out.='<h2>'.__('Sorry you look like a spammer, please go back and tick the box to prove you are not!','church-admin').'</p>';}  
		else
		{	
			$form=$sql=array();
			foreach ($_POST AS $key=>$value)$form[$key]=stripslashes_deep($value);
	
			
			if(empty($household_id))$household_id=$wpdb->get_var('SELECT household_id FROM '.CA_HOU_TBL.' WHERE address="'.esc_sql(sanitize_text_field($form['address'])).'" AND lat="'.esc_sql(sanitize_text_field($form['lat'])).'" AND lng="'.esc_sql(sanitize_text_field($form['lng'])).'" AND phone="'.esc_sql(sanitize_text_field($form['phone'])).'"');
			if(empty($form['address']))$household_id=NULL;
			if(empty($household_id))
			{//insert
	    			$success=$wpdb->query('INSERT INTO '.CA_HOU_TBL.' (address,lat,lng,phone) VALUES("'.esc_sql(sanitize_text_field($form['address'])).'", "'.esc_sql(sanitize_text_field($form['lat'])).'","'.esc_sql(sanitize_text_field($form['lng'])).'","'.esc_sql(sanitize_text_field($form['phone'])).'" )');
	    			$household_id=$wpdb->insert_id;
			}//end insert
			else
			{//update
	   		$success=$wpdb->query('UPDATE '.CA_HOU_TBL.' SET address="'.esc_sql(sanitize_text_field($form['address'])).'" , lat="'.esc_sql(sanitize_text_field($form['lat'])).'" , lng="'.esc_sql(sanitize_text_field($form['lng'])).'" , phone="'.esc_sql(sanitize_text_field($form['phone'])).'" WHERE household_id="'.esc_sql($household_id).'"');
			}//update
	
			$sql=array();
	
	
        	for($x=0;$x<count($_POST['first_name']);$x++)
        	{
				$y=$x+1;
            	$sex=sanitize_text_field($_POST['sex'][$x]);
            	if($x==0){$head=1;}else{$head=0;}
            	if(!empty($_POST['first_name'][$x])){$first_name=sanitize_text_field($form['first_name'][$x]);}else{$first_name='';}
				if(!empty($_POST['prefix'][$x])){$prefix=sanitize_text_field($form['prefix'][$x]);}else{$prefix='';}
      	      	if(!empty($_POST['last_name'][$x])){$last_name=sanitize_text_field($form['last_name'][$x]);}else{$last_name='';}
            	if(!empty($_POST['mobile'][$x])){$mobile=sanitize_text_field($form['mobile'][$x]);}else{$mobile='';}
            	if(!empty($_POST['email'][$x])){$email=sanitize_text_field($form['email'][$x]);}else{$email='';}
            	if(!empty($_POST['people_type_id'][$x])){$people_type_id=sanitize_text_field($form['people_type_id'][$x]);}else{$people_type_id='';}
            	//only add or update if first_name and last_name are populated
            	if(!empty($first_name)&&!empty($last_name))
            	{
            
            		//check to see if $people_id is connected to household_id
            		$check=false;
            		if(!empty($_POST['people_id'][$x])&&!empty($household_id))$check=$wpdb->get_var('SELECT people_id FROM '.CA_PEO_TBL.' WHERE household_id="'.esc_sql($household_id).'" AND people_id="'.esc_sql((int)$_POST['people_id'][$x]).'"'); 
            		if(!$check||empty($household_id)) 
            		{
            			$query='INSERT INTO '.CA_PEO_TBL.' (first_name,prefix,last_name,mobile,email,sex,household_id,people_type_id,member_type_id,head_of_household) VALUES("'.esc_sql($first_name).'","'.esc_sql($prefix).'","'.esc_sql($last_name).'", "'.esc_sql($mobile).'","'.esc_sql($email).'", "'.$sex.'","'.esc_sql($household_id).'","'.esc_sql((int)$people_type_id).'", "'.$member_type_id.'","'.$head.'")';
            			$wpdb->query($query);
            			$people_id=$wpdb->insert_id;
            	
            		}else
            		{
            
            			$wpdb->query('UPDATE '.CA_PEO_TBL.' SET first_name="'.esc_sql($first_name).'",prefix="'.esc_sql($prefix).'", last_name="'.esc_sql($last_name).'", mobile="'.esc_sql($mobile).'", email="'.esc_sql($email).'", sex="'.$sex.'",household_id="'.esc_sql($household_id).'", people_type_id="'.esc_sql((int)$people_type_id).'",member_type_id="'.$member_type_id.'" WHERE people_id="'.esc_sql($people_id).'"');
            
            		}//update
        	 		if(!empty($_POST['small_group_id'][$x]))church_admin_update_people_meta(intval($_POST['small_group_id'][$x]),$people_id,'smallgroup');
        		}
        
    		}//add or update people
        
        
        
        	if($admin_email)
        	{
            	$message='<p>'.__('A new household has registered on','church-admin').' '.site_url().'</p><p>'.__('Please','church-admin').'  <a href="'.site_url().'/wp-admin/admin.php?page=church_admin/index.php&action=church_admin_recent_activity&tab=people">'.__('check them out','church-admin').'.</a></p>';
            	add_filter('wp_mail_content_type',create_function('', 'return "text/html"; '));
            	wp_mail(get_option('admin_email'),__('New site registration','church-admin'),$message);
        	}
        	$out.='<p>'.__('Thank you for registering on the site','church-admin').'</p>';
        }//end not a spammer
    }//end process
    else
    {//form
    
    	$out.='<div class="church_admin"><h2>'.__('Registration','church-admin').'</h2>';
     	$out.='<form action="" method="post"><input type="hidden" name="save" value="yes"/>';
        $out.=wp_nonce_field('church_admin_register','church_admin_register',TRUE,FALSE);
        
        
    	if(!empty($household_id)){
    		//get_people if $houshold_id
    		$people=$wpdb->get_results('SELECT * FROM '.CA_PEO_TBL.' WHERE household_id="'.esc_sql($household_id).'"');
    	}
    	$x=1;
    	if(!empty($household_id)&&!empty($people)){//editing an entry
    		
    		foreach($people AS $person){
    			
    			
        		$out.='<div class="clonedInput" id="input'.$x.'">';
        		$out.='<input type=hidden value="'.$person->people_id.'" name="people_id[]" id="people_id'.$x.'"/>';
        		$out.='<p><label>'.__('First Name','church-admin').'</label><input type="text" class="first_name" id="first_name'.$x.'" name="first_name[]" ';
        		if(!empty($person->first_name))$out.='value="'.esc_html($person->first_name).'"';
        		$out.='/></p>';
        		$out.='<p><label>'.__('Prefix','church-admin').'</label><input type="text" class="prefix" id="prefix'.$x.'" name="prefix[]"  ';
        		if(!empty($person->prefix))$out.='value="'.esc_html($person->prefix).'"';
        		$out.='/></p>';
        		$out.='<p><label>'.__('Last Name','church-admin').'</label><input type="text" class="last_name" id="last_name'.$x.'" name="last_name[]" ';
        		if(!empty($person->last_name))$out.='value="'.esc_html($person->last_name).'"';
        		$out.='/></p>';
        		$out.='<p><label>'.__('Mobile','church-admin').'</label><input type="text" class="mobile" id="mobile'.$x.'" name="mobile[]" ';
        		if(!empty($person->mobile))$out.='value="'.esc_html($person->mobile).'"';
        		$out.='/></p>';
        		$out.='<p><label>'.__('Small Group','church-admin').'</label><select name="small_group_id[]" id="small_group'.$x.'" class="small_group">';
		$smallgroups=$wpdb->get_results('SELECT * FROM '.CA_SMG_TBL);
		
		foreach($smallgroups AS $smallgroup)
		{
			
			echo'<option value="'.intval($smallgroup->id).'">'.esc_html($smallgroup->group_name).'</option>';
		}
		echo '</select></p>';
		//site
		$sites=$wpdb->get_results('SELECT venue,site_id FROM '.CA_SIT_TBL.' ORDER BY venue ASC');
		echo'<tr><th scope="row">'.__('Site','church-admin').'</th><td><select name="site_id[]" id="site_id1" class="site_id">';
		foreach($sites AS $site)
		{
			echo'<option value="'.intval($site->site_id).'">'.esc_html($site->venue).'<option>';
		}
		echo'</select></td></tr>';
        		$out.='<p><label>'.__('Person type','church-admin').'</label><select name="people_type_id[]" id="people_type'.$x.'" class="people_type_id">';
        		foreach($people_type AS $id=>$type){$out.='<option value="'.$id.'" '.selected($id,$person->people_type_id,FALSE).'>'.$type.'</option>';}
        		$out.='</select></p>';
        		$out.='<p><label>'.__('Email','church-admin').'</label><input type="text" class="email" id="email'.$x.'" name="email[]" ';
        		if(!empty($person->email))$out.='value="'.esc_html($person->email).'"';
        		$out.='/></p>';
        		$gender=get_option('church_admin_gender');
				$out.='<p><label>'.__('Gender','church-admin').'</label><select name="sex[]" class="sex" id="sex'.$x.'">';
		
				foreach($gender AS $key=>$value){$out.= '<option value="'.esc_html($key).'" '.selected($key,$person->sex,FALSE).'>'.esc_html($value).'</option>';}
				$out.='</select></p>';
				$out.='</div>';
    			$x++;
    		}//end of pre-populated people
    	}
    		
    	
    		
        	$out.='<div class="clonedInput" id="input'.$x.'">';
        	$out.='<input type=hidden value="0" name="people_id[]" id="people_id[]"/>';
        	$out.='<p><label>'.__('First Name','church-admin').'</label><input type="text" class="first_name" id="first_name1" name="first_name[]"/></p>';
        	$out.='<p><label>'.__('Prefix','church-admin').'</label><input type="text" class="prefix" id="prefix1" name="prefix[]" /></p>';
        	$out.='<p><label>'.__('Last Name','church-admin').'</label><input type="text" class="last_name" id="last_name1" name="last_name[]"/></p>';
        	$out.='<p><label>'.__('Mobile','church-admin').'</label><input type="text" class="mobile" id="mobile1" name="mobile[]"/></p>';
        	$out.='<p><label>'.__('Person type','church-admin').'</label><select name="people_type_id[]" id="people_type1" class="people_type">';
        	foreach($people_type AS $id=>$type){$out.='<option value="'.$id.'">'.$type.'</option>';}
        	$out.='</select></p>';
        	$out.='<p><label>'.__('Email','church-admin').'</label><input type="text" class="email" id="email1" name="email[]"/></p>';
        	$gender=get_option('church_admin_gender');
			$out.='<p><label>'.__('Gender','church-admin').'</label><select name="sex[]" class="sex" id="sex1">';
		
			foreach($gender AS $key=>$value){$out.= '<option value="'.esc_html($key).'">'.esc_html($value).'</option>';}
			$out.='</select></p>';
		
        	$out.='</div>';
       
        $out.='<p id="jquerybuttons"><input type="button" id="btnAdd" value="'.__('Add another person','church-admin').'" /><input type="button" id="btnDel" value="'.__('Remove person','church-admin').'" /></p>';;
        if(!empty($household_id)){$data=$wpdb->get_row('SELECT * FROM '.CA_HOU_TBL.' WHERE household_id="'.esc_sql($household_id).'"');}else{$data=NULL;}
        $out.='<table class="form-table"><tbody><tr><th scope="row">'.__('Phone','church-admin').'</th><td><input name="phone" type="text" ';
        if(!empty($person->mobile))$out.='value="'.esc_html($data->phone).'"';
        $out.='/></td></tr>';
        $out.='<div class="ItsAllAboutJesus"></div>';
        $out.='</tbody></table>';
        
        require_once(plugin_dir_path(dirname(__FILE__)).'includes/directory.php');
        $out.= church_admin_address_form($data,NULL);
        
        
        
		$out.='<div class="clear"></div>';
        $out.= '<p><input type="submit" value="'.__('Register','church-admin').'"/></form></div>';
        $out.= '<script>jQuery(document).ready(function($) {
    var content=\'<p>'.__('Check box if you are not a spammer','church-admin').'<input type="checkbox" name="ItsAllAboutJesus" value="yes"/></p>\'
    $(".ItsAllAboutJesus").html(content);
});</script>';
    }//form
    }//logged in or not $_GET['household_id']
    return $out;
}

?>