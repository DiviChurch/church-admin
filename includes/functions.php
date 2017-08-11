<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly


/*
 * Matches each symbol of PHP date format standard
 * with jQuery equivalent codeword
 * @author Tristan Jahier
 */
function dateformat_PHP_to_jQueryUI($php_format)
{
    $SYMBOLS_MATCHING = array(
        // Day
        'd' => 'dd',
        'D' => 'D',
        'j' => 'd',
        'l' => 'DD',
        'N' => '',
        'S' => '',
        'w' => '',
        'z' => 'o',
        // Week
        'W' => '',
        // Month
        'F' => 'MM',
        'm' => 'mm',
        'M' => 'M',
        'n' => 'm',
        't' => '',
        // Year
        'L' => '',
        'o' => '',
        'Y' => 'yy',
        'y' => 'y',
        // Time
        'a' => '',
        'A' => '',
        'B' => '',
        'g' => '',
        'G' => '',
        'h' => '',
        'H' => '',
        'i' => '',
        's' => '',
        'u' => ''
    );
    $jqueryui_format = "";
    $escaping = false;
    for($i = 0; $i < strlen($php_format); $i++)
    {
        $char = $php_format[$i];
        if($char === '\\') // PHP date format escaping character
        {
            $i++;
            if($escaping) $jqueryui_format .= $php_format[$i];
            else $jqueryui_format .= '\'' . $php_format[$i];
            $escaping = true;
        }
        else
        {
            if($escaping) { $jqueryui_format .= "'"; $escaping = false; }
            if(isset($SYMBOLS_MATCHING[$char]))
                $jqueryui_format .= $SYMBOLS_MATCHING[$char];
            else
                $jqueryui_format .= $char;
        }
    }
    return $jqueryui_format;
}
function church_admin_date_picker($db_date,$name,$array=FALSE,$start=NULL,$end=NULL)
{
	if(empty($start))$start=date('Y');
	if(empty($end))$end=date('Y')+10;
	$out='';
	$date_format=get_option('date_format');
	$jsdate_format=dateformat_PHP_to_jQueryUI($date_format);
	//text field that can be seen
	$out.='<input type="text" name="'.$name.'x';
	if($array)$out.='[]';
	$out.='" class="'.sanitize_title($name).'x" ';
	if(!empty($db_date)&&$db_date!='0000-00-00') $out.= ' value="'.mysql2date(get_option('date_format'),$db_date).'" ';
	$out.='/>'."\r\n";
	$out.='<span class="dashicons dashicons-calendar-alt"></span>';
	//data that will be processed when form submitted
	$out.='<input type="hidden" name="'.$name;
	if($array)$out.='[]';
	$out.='" id="'.$name.'"';
	if(!empty($db_date))$out.='value="'. esc_html($db_date).'" ';
	$out.='/>';
	$out.='<script type="text/javascript">
		jQuery(document).ready(function(){
			jQuery(\'.'.sanitize_title($name).'x\').datepicker({altFormat: "yy-mm-dd",altField:"#'.$name.'",
            dateFormat : "'.$jsdate_format.'", changeYear: true ,yearRange: "'.$start.':'.$end.'"
         });
		});
		</script>';

	return $out;

}




/**
 * Array of ministries
 *
 * @param 
 * deprecated
 * 
 * @author andy_moyle
 * 
 */
function church_admin_ministries($childID=NULL){
	global $wpdb;
	$ministries=array();
	$sql='SELECT * FROM '.CA_MIN_TBL;
	$where='';
	//if(!empty($childID)) {$where=' WHERE childID ="'.intval($childID).'"';}
	//if($childID=='None')$where=' WHERE childID =0';
	$order=' ORDER BY ministry';
	$results=$wpdb->get_results($sql.$where.$order);
	if(!empty($results))
	{
		
		foreach($results as $row){$ministries[$row->ID]=$row->ministry;}
		
	}
	
	return $ministries;

}
/**
 * sets wp_mail to html type!
 *
 * Author:     Andy Moyle
 * Author URI: http://www.churchadminplugin.com
 *
 * 
 */
 function church_admin_email_type($content_type){
return 'text/html';
}


/**
 * This function initialises wp_mail with stored smtp settings
 *
 * Author:     Andy Moyle
 * Author URI: http://www.churchadminplugin.com
 *
 * 
 */
add_action( 'phpmailer_init', 'church_admin_smtp_email' );
function church_admin_smtp_email( $phpmailer ) {

	$smtp=get_option('church_admin_smtp_settings');
	if(!empty($smtp['username'])&&!empty($smtp['host'])&&!empty($smtp['port'])&&!empty($smtp['password'])&&!empty($smtp['secure']))
	{
		// Define that we are sending with SMTP
		$phpmailer->isSMTP();

		// The hostname of the mail server
		$phpmailer->Host = $smtp['host'];//"smtp.example.com";

		// Use SMTP authentication (true|false)
		$phpmailer->SMTPAuth = $smtp['auth'];//true;

		// SMTP port number - likely to be 25, 465 or 587
		$phpmailer->Port = $smtp['port'];//"587";

		// Username to use for SMTP authentication
		$phpmailer->Username = $smtp['username'];//yourusername";

		// Password to use for SMTP authentication
		$phpmailer->Password =$smtp['password']; "yourpassword";

		// Encryption system to use - ssl or tls
		$phpmailer->SMTPSecure =$smtp['secure']; //"tls";

		$phpmailer->From = $smtp['from'];//"you@yourdomail.com";
		$phpmailer->FromName = $smtp['from_name'];//"Your Name";
	}
}
//end smtp settings for wp_mail


function church_admin_return_bytes($val) {
    $val = trim($val);
    $last = strtolower($val[strlen($val)-1]);
    switch($last) 
    {
        case 'g':
        $val *= 1024;
        case 'm':
        $val *= 1024;
        case 'k':
        $val *= 1024;
    }
    return $val;
}
function church_admin_max_file_upload_in_bytes() {
    //select maximum upload size
    $max_upload = church_admin_return_bytes(ini_get('upload_max_filesize'));
    //select post limit
    $max_post = church_admin_return_bytes(ini_get('post_max_size'));
    //select memory limit
    $memory_limit = church_admin_return_bytes(ini_get('memory_limit'));
    // return the smallest of them, this defines the real limit
    return min($max_upload, $max_post, $memory_limit)/(1024*1024);
}

function church_admin_get_id_by_shortcode($shortcode) {
	global $wpdb;

	$id = NULL;

	$sql = 'SELECT ID
		FROM ' . $wpdb->posts . '
		WHERE
			post_type = "page"
			AND post_status="publish"
			AND post_content LIKE "%' . $shortcode . '%"';

	$id = $wpdb->get_var($sql);
	return $id;
}
function church_admin_initials($people)
{
	$people=maybe_unserialize($people);
	if(!empty($people))
	{
		
		foreach($people as $id=>$peep)
		{
			if(ctype_digit($peep)){$person=church_admin_get_person($peep);}else{$person=$peep;}
			$strlen=strlen($person);
			$initials[$id]='';
			for($i=0;$i<=$strlen;$i++)
			{
				$char=substr($person,$i,1);
				if (ctype_upper($char)){$initials[$id].=$char;}
			}
		}
		
		return implode(', ',$initials);
	
	}else return '';
}
function church_admin_checkdate($date)
{
		$d=explode('-',$date);
		if(is_array($d) && count($d)==3 && checkdate($d[1],$d[2],$d[0])){return TRUE;}else{return FALSE;}
}
function church_admin_level_check($what)
{
    global $current_user;
    wp_get_current_user();
    
    $user_permissions=maybe_unserialize(get_option('church_admin_user_permissions'));
	
    $level=get_option('church_admin_levels');
	
    if(!empty($user_permissions[$what]))
    {//user permissions have been set for $what
		
		if( in_array($current_user->ID,maybe_unserialize($user_permissions[$what]))){return TRUE;}else{return FALSE;}
	}//end user permissions have been set
    elseif(!empty($level[$what]) && $level[$what]=="administrator"){return current_user_can('manage_options');}
    elseif(!empty($level[$what]) && $level[$what]=="editor"){return current_user_can('delete_others_pages');}
    elseif(!empty($level[$what]) &&$level[$what]=="author"){return current_user_can('publish_posts');}
    elseif(!empty($level[$what]) &&$level[$what]=="contributor"){return current_user_can('edit_posts');}
    elseif(!empty($level[$what]) &&$level[$what]=="subscriber"){return current_user_can('read');}
    else{ return false;}
}

function church_admin_user($ID)
{
		global $wpdb;
		$people_id=$wpdb->get_var('SELECT people_id FROM '.CA_PEO_TBL.' WHERE user_id="'.esc_sql($ID).'"');
		if(!empty($people_id)) {return $people_id;}else{return FALSE;}
}
function church_admin_collapseBoxForUser($userId, $boxId) {
    $optionName = "closedpostboxes_church-admin";
    $close = get_user_option($optionName, $userId);
    $closeIds = explode(',', $close);
    $closeIds[] = $boxId;
    $closeIds = array_unique($clodeIds); // remove duplicate Ids
    $close = implode(',', $closeIds);
    update_user_option($userId, $optionName, $close);
}



function church_admin_autocomplete($name='people',$first_id='friends',$second_id='to',$current_data=array(),$user_id=FALSE)
{
            /**
 *
 * Creates autocomplete field 
 * 
 * @author  Andy Moyle
 * @param    $name,$first_id,$second_id
 * @return   html string
 * @version  0.1
 *
 * 
 */
    $current='';        
    if(!empty($current_data))
    {
        $curr_data=maybe_unserialize($current_data);
        
        if(is_array($curr_data))
		{
			foreach($curr_data AS $key=>$value)
			{
				
				if(ctype_digit($value))
				{
						if(!$user_id)
						{//people_id
							$peoplename=church_admin_get_person($value);
						}
						else
						{//user_id
							$peoplename=church_admin_get_name_from_user($value);
						}	
				}else $peoplename=$value;
				$current.=$peoplename.', ';
			}
		}else$current=$current_data;
    }
    $out= '<input id="'.sanitize_title_with_dashes($first_id).'" class="to" placeholder="'.__('Enter names, separated by commas','church-admin').'" type="text" name="'.esc_html($name).'" value="'.esc_html($current).'"/> ';
    $ajax_nonce = wp_create_nonce( "church-admin-autocomplete" );
    $out.='<script type="text/javascript">

	jQuery(document).ready(function ($){

	$("#'.sanitize_title_with_dashes($first_id).'").autocomplete({
		source: function(req, add){
			req.action="church_admin_autocorrect";
			req.security="'.$ajax_nonce.'";
			console.log(req);
			$.getJSON("'.site_url().'/wp-admin/admin-ajax.php", req,  function(data) {  
                    
                    console.log("Response" + data);        
                    //create array for response objects  
                    var suggestions = [];  
                              
                    //process response  
                    $.each(data, function(i, val){                                
                    suggestions.push(val.name);  
                });  
                              
                //pass array to callback  
                add(suggestions);  
            });  

		},
		select: function (event, ui) {
                var terms = $("#'.sanitize_title_with_dashes($first_id).'").val().split(", ");
		// remove the current input
                terms.pop();
                console.log(terms);
		// add the selected item
                terms.push(ui.item.value);
		
                // add placeholder to get the comma-and-space at the end
                terms.push("");
                this.value = terms.join(", ");
                $("#'.sanitize_title_with_dashes($first_id).'").val(this.value);
                return false;
            },
		minLength: 3,
		
	});


});


</script>';
    return $out;
}

             /**
 *
 * Returns person's names from $people_id
 * 
 * @author  Andy Moyle
 * @param    $id
 * @return   string
 * @version  0.1
 *
 *
*/
function church_admin_get_person($id)
{

 global $wpdb;
 if(!ctype_digit($id))return $id;
    $row=$wpdb->get_row('SELECT first_name,middle_name,nickname,prefix,last_name FROM '.CA_PEO_TBL.' WHERE people_id="'.esc_sql($id).'"');
    if($row){
    			//build name
				$name=$row->first_name.' ';
					$middle_name=get_option('church_admin_use_middle_name');
					if(!empty($middle_name)&&!empty($row->middle_name))$name.=$row->middle_name.' ';
					$nickname=get_option('church_admin_use_nickname');
					if(!empty($nickname)&&!empty($row->nickname))$name.='('.$row->nickname.') ';
					$prefix=get_option('church_admin_use_prefix');
					if(!empty($prefix)&&!empty($row->prefix))		$name.=$row->prefix.' ';			
					$name.=$row->last_name;
    return esc_html($name);
    }else{return FALSE;}
}
function church_admin_get_name_from_user($id)
{
             /**
 *
 * Returns person's names from user_id
 * 
 * @author  Andy Moyle
 * @param    $id
 * @return   string
 * @version  0.1
 *
 *
*/
 global $wpdb;
 ;
    $row=$wpdb->get_row('SELECT first_name,middle_name,nickname,prefix,last_name FROM '.CA_PEO_TBL.' WHERE user_id="'.esc_sql($id).'"');
    
    if($row)
    {
    	//build name
		$name=$row->first_name.' ';
					$middle_name=get_option('church_admin_use_middle_name');
					if(!empty($middle_name)&&!empty($row->middle_name))$name.=$row->middle_name.' ';
					$nickname=get_option('church_admin_use_nickname');
					if(!empty($nickname)&&!empty($row->nickname))$name.='('.$row->nickname.') ';
					$prefix=get_option('church_admin_use_prefix');
					if(!empty($prefix)&&!empty($row->prefix))		$name.=$row->prefix.' ';			
					$name.=$row->last_name;
    	return esc_html($name);
    }else{return FALSE;}
}
 /**
 *
 * Returns peoples names from serialized array
 * 
 * @author  Andy Moyle
 * @param    $idArray
 * @return   string
 * @version  0.1
 * 
 */
function church_admin_get_people($idArray)
{

    global $wpdb;
    $ids=maybe_unserialize($idArray);
    if(!is_array($ids))return $ids;
    if(!empty($ids))
    {
        $names=array();
        foreach($ids AS $key=>$id)
        {
            if(ctype_digit($id))
            {//is int
                $row=$wpdb->get_row('SELECT first_name,middle_name,nickname,prefix,last_name FROM '.CA_PEO_TBL.' WHERE people_id="'.esc_sql($id).'"');
                if(!empty($row))
                {
                	$name=$row->first_name.' ';
					$middle_name=get_option('church_admin_use_middle_name');
					if(!empty($middle_name)&&!empty($row->middle_name))$name.=$row->middle_name.' ';
					$nickname=get_option('church_admin_use_nickname');
					if(!empty($nickname)&&!empty($row->nickname))$name.='('.$row->nickname.') ';
					$prefix=get_option('church_admin_use_prefix');
					if(!empty($prefix)&&!empty($row->prefix))		$name.=$row->prefix.' ';			
					$name.=$row->last_name;
                	$names[]=$name;
                }
            }//end is int
            else
            {//is text
                $names[]=$id;
            }//end is text
        }
        return implode(", ", array_filter($names));
    }
    else
    return " ";
}

function church_admin_get_people_id($name)
{
        /**
 *
 * Returns serialized array of people_id if $name is in DB
 * 
 * @author  Andy Moyle
 * @param    $name
 * @return   serialized array
 * @version  0.1
 * 
 */
    global $wpdb;    
    $names=explode(',',$name);
    
    $people_ids=array();
    if(!empty($names))
    {
        foreach($names AS $key=>$value)
        {
			$value=trim($value);
            if(!empty($value))
            {//only look if a name stored!
                $sql='SELECT people_id FROM '.CA_PEO_TBL.' WHERE CONCAT_WS(" ",first_name,last_name) LIKE "'.esc_sql($value).'" OR CONCAT_WS(" ",first_name,prefix,last_name) LIKE "'.esc_sql($value).'" OR CONCAT_WS(" ",first_name,middle_name,prefix,last_name) LIKE "'.esc_sql($value).'" OR CONCAT_WS(" ",first_name,middle_name,last_name) LIKE "'.esc_sql($value).'" OR CONCAT_WS(" ",first_name,prefix,last_name) LIKE "'.esc_sql($value).'" OR  nickname LIKE "'.esc_sql($value).'" LIMIT 1';
                
                $result=$wpdb->get_var($sql);
                if($result){$people_ids[]=$result;}else{$people_ids[]=$value;}
            }
        }
    }
    return maybe_serialize(array_filter($people_ids));
}
function church_admin_get_user_id($name)
{
        /**
 *
 * Returns serialized array of user_id if $name is in DB
 * 
 * @author  Andy Moyle
 * @param    $name
 * @return   serialized array
 * @version  0.1
 * 
 */
    global $wpdb;    
    $names=explode(',',$name);
    
    $user_ids=array();
    if(!empty($names))
    {
        foreach($names AS $key=>$value)
        {
			$value=trim($value);
            if(!empty($value))
            {//only look if a name stored!
                $sql='SELECT people_id FROM '.CA_PEO_TBL.' WHERE CONCAT_WS(" ",first_name,prefix,last_name) LIKE "'.esc_sql($value).'"OR CONCAT_WS(" ",first_name,middle_name,prefix,last_name) LIKE "'.esc_sql($value).'" OR CONCAT_WS(" ",first_name,middle_name,last_name) LIKE "'.esc_sql($value).'" OR CONCAT_WS(" ",first_name,prefix,last_name) LIKE "'.esc_sql($value).'" OR  nickname LIKE "'.esc_sql($value).'" LIMIT 1';
                $result=$wpdb->get_var($sql);
                if($result){$user_ids[]=$result;}else
				{
					echo '<p>'.esc_html($value).' is not stored by Church Admin as Wordpress User. ';
					$people_id=$wpdb->get_var('SELECT people_id FROM '.CA_PEO_TBL.' WHERE CONCAT_WS(" ",first_name,last_name) REGEXP "^'.esc_sql($value).'" LIMIT 1');
					if(!empty($people_id))echo'Please <a href="'.wp_nonce_url('admin.php?page=church_admin/index.php&amp;action=church_admin_edit_people&amp;people_id='.$people_id,'edit_people').'">edit</a> entry to connect/create site user account.';
					echo'</p>';
				}
            }
        }
    }
    if(!empty($user_ids)){ return maybe_serialize(array_filter($user_ids));}else{return NULL;}
}
function church_admin_get_one_id($name)
{
	global $wpdb;
	$sql='SELECT people_id FROM '.CA_PEO_TBL.' WHERE first_name LIKE "'.esc_sql($name).'" OR last_name LIKE "'.esc_sql($name).'" OR CONCAT_WS(" ",first_name,last_name) LIKE "'.esc_sql($name).'" OR CONCAT_WS(" ",first_name,prefix,last_name) LIKE "'.esc_sql($name).'" OR CONCAT_WS(" ",first_name,middle_name,prefix,last_name) LIKE "'.esc_sql($name).'" OR CONCAT_WS(" ",first_name,middle_name,last_name) LIKE "'.esc_sql($name).'" OR CONCAT_WS(" ",first_name,prefix,last_name) LIKE "'.esc_sql($name).'" OR  nickname LIKE "'.esc_sql($name).'" LIMIT 1';
    $result=$wpdb->get_var($sql);
	if(!empty($result)){return $result;}else{return $name;}
}


function church_admin_update_order($which='member_type')
{
    global $wpdb;
    if(isset($_POST['order']))
    {
        switch($which)
        {
			case'facilities':$tb=CA_FAC_TBL;$field='facilities_order';$id='facility_id';break;
            case'member_type':$tb=CA_MTY_TBL;$field='member_type_order';$id='member_type_id';break;
            case'rota_settings':$tb=CA_RST_TBL;$field='rota_order';$id='rota_id';break;
            case'small_groups':$tb=CA_SMG_TBL;$field='smallgroup_order';$id='id';break;
			case'people':$tb=CA_PEO_TBL;$field='people_order';$id='people_id';break;
        }
        $order=explode(",",$_POST['order']);
        foreach($order AS $order=>$row_id)
        {
            $member_type_order++;
            $head=='';
            if($which=='people')
            {
            	if($order==0){$head=', head_of_household=1';}else{$head=', head_of_household=0';}
            }
            $sql='UPDATE '.$tb.' SET '.$field.'="'.esc_sql($order).'" '.$head.' WHERE '.$id.'="'.esc_sql($row_id).'"';
            church_admin_debug($sql);
            $wpdb->query($sql);
        }
    }
}
function church_admin_member_type_array()
{
    global $wpdb;
    $member_type=array();
    $results=$wpdb->get_results('SELECT * FROM '.CA_MTY_TBL.' ORDER BY member_type_order ASC');
    foreach($results AS $row)
    {
        $member_type[$row->member_type_id]=$row->member_type;
    }
    return($member_type);
}

/**
* This function deletes a person from a hope team 
*
* @author     	andymoyle
* @param		$people_id,$meta_type
* @return		array
*
*/
function church_admin_delete_from_hope_team($people_id)
{
  global $wpdb;
  ;
  $wpdb->query('DELETE FROM '.CA_MET_TBL.' WHERE people_id="'.esc_sql($people_id).'" AND meta_type="hope_team" ');
  
}


function church_admin_get_hierarchy($ID)
{
		$rand=rand();
  		church_admin_leadership_hierarchy($ID,$rand);
    	$hierarchy=get_option('church_admin_leadership_hierarchy'.$rand);
    	delete_option('church_admin_leadership_hierarchy'.$rand);
    	return $hierarchy;
}

function church_admin_leadership_hierarchy($ID,$rand)
{
	global $wpdb;
	$hierarchy=get_option('church_admin_leadership_hierarchy'.$rand);
	if(empty($hierarchy)||(is_array($hierarchy)&&!in_array($ID,$hierarchy))){$hierarchy=array(1=>$ID);update_option('church_admin_leadership_hierarchy'.$rand,$hierarchy);}
	$sql='SELECT parentID FROM '.CA_MIN_TBL.' WHERE ID="'.esc_sql($ID).'"';
	
	$nextlevel=$wpdb->get_var($sql);
	if(empty($nextlevel)) 
	{
		
	 	return $hierarchy;
	}
	else
	{
		$hierarchy[]=(int)$nextlevel;
		
		update_option('church_admin_leadership_hierarchy'.$rand,$hierarchy);
		church_admin_leadership_hierarchy($nextlevel,$rand);
	}
}
/**
* This function updates a people meta 
*
* @author     	andymoyle
* @param		$people_id,$meta_type,$ID
* @return		array
*
*/
function church_admin_update_people_meta($ID,$people_id,$meta_type='ministry')
{
  global $wpdb;
  ;
  $id=$wpdb->get_var('SELECT meta_id FROM '.CA_MET_TBL.' WHERE people_id="'.esc_sql($people_id).'" AND meta_type="'.esc_sql($meta_type).'" AND ID="'.esc_sql($ID).'"');
  if(!$id)
  {
  	$sql='INSERT INTO '.CA_MET_TBL.'(people_id,ID,meta_type,meta_date) VALUES("'.esc_sql($people_id).'","'.esc_sql($ID).'","'.esc_sql($meta_type).'","'.date('y-m-d').'")';
  	//echo $sql;
  		$wpdb->query($sql);
  	}
  
}



/**
* This function produces an array of meta_id for people_id 
*
* @author     	andymoyle
* @param		$people_id,$meta_type
* @return		FALSE or array
*
*/
function church_admin_get_people_meta($people_id,$meta_type='smallgroup'){
  global $wpdb;
  $out=array();
  ;
  $results=$wpdb->get_results('SELECT ID FROM '.CA_MET_TBL.' WHERE people_id="'.esc_sql($people_id).'" AND meta_type="'.esc_sql($meta_type).'"');
  if(empty($results)){return FALSE;}
  else
  {
  	foreach($results AS $row)$out[]=$row->ID;
  	return $out;
  }
}

function church_admin_people_meta($ID=NULL,$people_id=NULL,$meta_type=NULL)
{
	global $wpdb;
	$sql='SELECT * FROM '.CA_MET_TBL.' WHERE ';
	$where=array();
	if(!empty($ID)) $where[]= 'ID="'.intval($ID).'" ';
	if(!empty($people_id))$where[]=' people_id="'.intval($people_id).'"';
	if(!empty($meta_type))$where[]=' meta_type="'.esc_sql($meta_type).'"';
	$query=$sql.implode(' AND ',$where);

	$results=$wpdb->get_results($query);
	return $results;
}

/**
* This function deletes a meta data for a given people_id or meta ID
*
* @author     	andymoyle
* @param		$people_id,$meta_type
* @return		
*
*/
function church_admin_delete_people_meta($ID=NULL,$people_id,$meta_type=NULL)
{
	global $wpdb;
	if($ID){
		$wpdb->query('DELETE FROM '.CA_MET_TBL.' WHERE people_id="'.esc_sql($people_id).'" AND meta_type="'.esc_sql($meta_type).'" AND ID="'.esc_sql($ID).'"');
	}else{
		$wpdb->query('DELETE FROM '.CA_MET_TBL.' WHERE people_id="'.esc_sql($people_id).'" AND meta_type="'.esc_sql($meta_type).'"');
	}
}

function strip_only($str, $tags) {
    //this functions strips some tages, but not all
    if(!is_array($tags)) {
        $tags = (strpos($str, '>') !== false ? explode('>', str_replace('<', '', $tags)) : array($tags));
        if(end($tags) == '') array_pop($tags);
    }
    foreach($tags as $tag) $str = preg_replace('#</?'.$tag.'[^>]*>#is', '', $str);
    return $str;
}

function checkDateFormat($date)
{
  //match the format of the date
  if (preg_match ("/^([0-9]{4})-([0-9]{2})-([0-9]{2})$/", $date, $parts))
  {
    //check weather the date is valid of not
        if(checkdate($parts[2],$parts[3],$parts[1]))
          return true;
        else
         return false;
  }
  else
    return false;
}


function QueueEmail($to,$subject,$message,$copy,$from_name,$from_email,$attachment,$schedule=NULL)
{
    global $wpdb;

    $sqlsafe=array();
    $sqlsafe['to']=esc_sql($to);
    $sqlsafe['from_name']=esc_sql($from_name);
    $sqlsafe['from_email']=esc_sql($from_email);
    $sqlsafe['subject']=esc_sql($subject);    
    $sqlsafe['message']=esc_sql($message);
    $sqlsafe['attachment']=esc_sql(maybe_serialize($attachment));
	$sqlsafe['schedule']=esc_sql($schedule);
    $sqlsafe['copy']=esc_sql(maybe_unserialize($copy));
    $sql='INSERT INTO '.CA_EMA_TBL.' (recipient,from_name,from_email,copy,subject,message,attachment,schedule)VALUES("'.$sqlsafe['to'].'","'.$sqlsafe['from_name'].'","'.$sqlsafe['from_email'].'","'.$sqlsafe['copy'].'","'.$sqlsafe['subject'].'","'.$sqlsafe['message'].'","'.$sqlsafe['attachment'].'","'.$sqlsafe['schedule'].'")';
	
	$result=$wpdb->query($sql);

    if($result) {return $wpdb->insert_id;}else{return FALSE;}
}

if(!function_exists('set_html_content_type')){function set_html_content_type() {return 'text/html';}}

function church_admin_plays($file_id)
{
	global $wpdb;
	$plays=$wpdb->get_var('SELECT plays FROM '.CA_FIL_TBL.' WHERE file_id="'.esc_sql($file_id).'"');
	return $plays;
}

function church_admin_dateCheck($date, $yearepsilon=5000) { // inputs format is "yyyy-mm-dd" ONLY !
if (count($datebits=explode('-',$date))!=3) return false;
$year = intval($datebits[0]);
$month = intval($datebits[1]);
$day = intval($datebits[2]);
if ((abs($year-date('Y'))>$yearepsilon) || // year outside given range
($month<1) || ($month>12) || ($day<1) ||
(($month==2) && ($day>28+(!($year%4))-(!($year%100))+(!($year%400)))) ||
($day>30+(($month>7)^($month&1)))) return false; // date out of range
if( checkdate($month,$day,$year)) {return ($year.'-'.sprintf("%02d", $month).'-'.sprintf("%02d", $day));}else{return FALSE;}
}

/**************************************************************************************************************************************************
*
*
*  Check if logged in user can do what is wanted
* param ID - ID of person about to be edited/deleted or ID of ministry
* admins can do anything
*
*
*
*
***************************************************************************************************************************************************/
function church_admin_user_can($ID,$meta_type='smallgroup')
{
	$can=FALSE;
	global $current_user;
	wp_get_current_user();
	$user_people_id=$wpdb->get_var('SELECT people_id FROM '.CA_PEO_TBL.' WHERE user_id="'.intval($current_user->ID).'"');
	
	//administrator
	if(current_user_can('manage_options')) return TRUE;
	
	//if current user is the passed ID
	if($user_people_id==$ID)return TRUE;	
	
	if($meta_type=='smallgroup')
	{
		//check if $ID is in a group led or overseen
		$sgID=$wpdb->get_var('SELECT ID FROM '.CA_MET_TBL.' WHERE people_id="'.intval($ID).'" AND meta_key="smallgroup"');
		if(!empty($sgID))
		{
			$leaders=maybe_unserialize($wpdb->get_var('SELECT leadership FROM '.CA_SMG_TBL.' WHERE id="'.intval($sgID).'"'));
			if(is_array($leaders))
			{
				foreach($leaders AS $leaderlevel) 
				{
					if(in_array($user_people_id,$leaderlevel)) return TRUE;
				}
			}
		}
	}
	else
	{//ministry
	//see if ministry has a parent
		$parentID=$wpdb->get_var('SELECT parentID FROM '.CA_MIN_TBL.' WHERE ID="'.intval($ID).'"');
		if(empty($parentID)) return FALSE;
		if(parent($ID)){return TRUE;}
		function parent($ID)
		{ 
			$check=$wpdb->get_var('SELECT meta_id FROM '.CA_MET_TBL.' WHERE ID="'.intval($ID).'" AND people_id="'.intval($user_people_id).'" AND meta_type="ministry"');
			if(!empty($check)) return TRUE;
			$next_level=$wpdb->get_var('SELECT parentID FROM '.CA_MIN_TBL.' WHERE ID="'.intval($parentID).'"');
			if(!empty($next_level))
			{
				if(parent($next_level)){ return TRUE;}else return FALSE;
			}
			else return FALSE;
		}
		//see if user is in that parent ministry
		
	}
	return FALSE;
}


function church_admin_adjust_brightness($hex, $steps) {
    // Steps should be between -255 and 255. Negative = darker, positive = lighter
    $steps = max(-255, min(255, $steps));

    // Normalize into a six character long hex string
    $hex = str_replace('#', '', $hex);
    if (strlen($hex) == 3) {
        $hex = str_repeat(substr($hex,0,1), 2).str_repeat(substr($hex,1,1), 2).str_repeat(substr($hex,2,1), 2);
    }

    // Split into three parts: R, G and B
    $color_parts = str_split($hex, 2);
    $return = '#';

    foreach ($color_parts as $color) {
        $color   = hexdec($color); // Convert to decimal
        $color   = max(0,min(255,$color + $steps)); // Adjust color
        $return .= str_pad(dechex($color), 2, '0', STR_PAD_LEFT); // Make two char hex code
    }

    return $return;
}
 /**
 *
 * Replace rota entry
 * 
 * @author  Andy Moyle
 * @param    $people_id,$date,$mtg_type,$service_id,$rota_task_id
 * @return   BOOL
 * @version  0.1
 * 
 */
 function church_admin_update_rota_entry($rota_task_id,$date,$people_id,$mtg_type,$service_id)
 {
 	global $wpdb;
 	$table=CA_ROTA_TBL;
 	$data=array(
 			'rota_task_id'=>$rota_task_id,
 			'people_id'=>$people_id,
 			'mtg_type'=>$mtg_type,
 			'service_id'=>$service_id,
 			'rota_date'=>$date
 	);
 	
 	$format=array(
 			'%d',
 			'%s',
 			'%s',
 			'%d',
 			'%s'
 	);
 	
 	if(empty($rota_id))
 	{
 		$wpdb->insert($table,$data,$format);
 	}
 	else
 	{
 		$where=array('rota_id'=>$rota_id);
 		$wpdb->update( $table, $data, $where, $format  ); 

	}
 
 
 }
  /**
 *
 * Grab array of people_ids for particular ministry_id 
 * 
 * @author  Andy Moyle
 * @param    $ministry_id
 * @return   array($people_id=>$name)
 * @version  0.1
 * 
 */
 function church_admin_ministry_people_array($ministry_id)
 {
 	global $wpdb;
 	$out=array();
 	$results=$wpdb->get_results('SELECT CONCAT_WS(" ",a.first_name,a.last_name) AS name, b.people_id AS people_id FROM '.CA_PEO_TBL.' a, '.CA_MET_TBL.' b WHERE a.people_id=b.people_id AND b.meta_type="ministry" AND b.ID="'.intval($ministry_id).'" ');
 	if(!empty($results))
 	{
 		foreach($results AS $row)$out[$row->people_id]=$row->name;
 	}
 	
 	return $out;
 }
  /**
 *
 * Grab array of people_ids for particular rota_task_id and event
 * 
 * @author  Andy Moyle
 * @param    $rota_date,$rota_taks_id,$service_id,$mtg_type
 * @return   array($people_id=>$name)
 * @version  0.1
 * 
 */
 function church_admin_rota_people_array($rota_date,$rota_task_id,$service_id,$mtg_type)
 {
 	global $wpdb;
 	$out=array();
 	$results=$wpdb->get_results('SELECT * FROM '.CA_ROTA_TBL.' WHERE rota_task_id="'.intval($rota_task_id).'" AND mtg_type="'.esc_sql($mtg_type).'" AND service_id="'.intval($service_id).'" AND rota_date="'.esc_sql($rota_date).'"');
 	if(!empty($results))
 	{
 		foreach($results AS $row)$out[$row->people_id]=church_admin_get_person($row->people_id);
 	}
 	return $out;
 }
   /**
 *
 * Grab comma separated list of people for particular rota_taks_id and event
 * 
 * @author  Andy Moyle
 * @param    $rota_date,$rota_taks_id,$service_id,$mtg_type
 * @return   string
 * @version  0.1
 * 
 */
 function church_admin_rota_people($rota_date,$rota_task_id,$service_id,$mtg_type)
 {
 	return implode(", ",church_admin_rota_people_array($rota_date,$rota_task_id,$service_id,$mtg_type));
 }
 
 
 
/**
 *
 * Works out font size and orientation for data
 * 
 * @author  Andy Moyle
 * @param    $lengths, $fontSize
 * @return   array(orientation,font_size,widths)
 * @version  0.1
 * 
 */
function church_admin_pdf_settings($lengths,$fontSize=10)
{
	//M is max width letter and at 1pt Arial will take up 0.35mm approx, will allow 3mm either side
	$colWidth=array();
	foreach($lengths AS $key=>$length)$colWidth[$key]=($length*$fontSize*0.2);
	$pdfSettings=array('font_size'=>$fontSize,'widths'=>$colWidth);
	//find total width and check it is less than width of page
	$tableWidth=array_sum($colWidth);
	$pdfSize=get_option('church_admin_pdf_size');
	
	switch($pdfSize)
	{
		case 'A4':
					if(($tableWidth)<190)$pdfSettings['orientation']='P';
					elseif($tableWidth<277)$pdfSettings['orientation']='L';
					else{return FALSE;}
		break;
		case 'Letter': 
					if(($tableWidth)<195)$pdfSettings['orientation']='P';
					elseif($tableWidth<259)$pdfSettings['orientation']='L';
					else{return FALSE;}
		break;
		case 'Legal':
					if(($tableWidth)<200)$pdfSettings['orientation']='P';
					elseif($tableWidth<346)$pdfSettings['orientation']='L';
					else{return FALSE;}
		break;
	}
	return $pdfSettings;
	
} 
 
     function church_admin_api_checker($url) {
        $curl = curl_init($url);

        //don't fetch the actual page, you only want to check the connection is ok
        curl_setopt($curl, CURLOPT_NOBODY, true);

        //do request
        $result = curl_exec($curl);

        $ret = false;

        //if request did not fail
        if ($result !== false) {
            //if request was ok, check response code
            $statusCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);  
       		$ret=$statusCode;     
        }
        curl_close($curl);

       return $statusCode;
    }
 
 
?>