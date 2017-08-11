<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

function church_admin_sms($mobile,$message)
{
		$username=get_option('church_admin_sms_username');
		$password=get_option('church_admin_sms_password');
		$sender=get_option('church_admin_sms_reply'); 
		$sms_type=get_option('church_admin_bulksms');
		$url = $sms_type.'/submission/send_sms/2/2.0';
		$mobile=str_replace(' ','',$mobile);
		$country=get_option('church_admin_sms_iso');
		$sendmobile=$country.$mobile;//non UK
		if($country==44 && !empty($mobile) && $mobile{0}=='0')//uk remove preceding 0
		{
             $sendmobile=$country.substr($mobile, 1); 
        }
        echo $sendmobile;
        church_admin_debug("***********\r\nMobile\r\n".$sendmobile);
		$post_body = seven_bit_sms( $username, $password, $message, $sendmobile );
		if(!defined('SMS_test')){$result = send_message( $post_body, $url, $port );}else{$result='Debug'.print_r($mobile,TRUE);}
		return $result;
}



function church_admin_send_sms()
{
    global $wpdb;
	$username=get_option('church_admin_sms_username');
	$password=get_option('church_admin_sms_password');
	$sender=get_option('church_admin_sms_reply');    
	$sms_type=get_option('church_admin_bulksms');
	if(empty($username)||empty($password)||empty($sender)||empty($sms_type))
	{
		echo'<h2>Please setup your Bulksms account settings first</h2>';
	}
	else
	{
	$member_type=church_admin_member_type_array();

    //check to see if directory is populated!
    $check=$wpdb->get_var('SELECT COUNT(people_id) FROM '.CA_PEO_TBL);
    if(empty($check)||$check<1)
    {
	echo'<div class="notice notice-success inline">';
	echo'<p><strong>You need some people in the directory before you can use this Bulk SMS service</strong></p>';
	echo '<p><a href="'.wp_nonce_url('admin.php?page=church_admin/index.php&amp;action=church_admin_edit_household','edit_household').'">'.__('Add a Household','church-admin').'</a></p>';
	echo'</div>';
    }
    else
    {//people stored in directory
    
	if(isset($_POST['counttxt'])&& check_admin_referer('church admin send sms'))
	{
	    echo'<div id="message" class="notice notice-success inline">';
	    
	    $port = 80;    
		//change port is ssl
		if (strpos($sms_type, 'https://') !== false) {$port=443;}
    
	    //grab recipients
		if(!empty($_POST['recipients']))
	{
				$names=array();
				$ids=maybe_unserialize(church_admin_get_people_id(stripslashes($_POST['recipients'])));
				
				foreach($ids AS $value){$names[]='people_id = "'.esc_sql($value).'"';}
				$sql='SELECT  mobile,first_name FROM '.CA_PEO_TBL.' WHERE email!="" AND '.implode(' OR ',$names).' AND email_send=1 GROUP BY email';
	}
	else
	{	
		$group_by='';
	$member_types=$genders=$people_types=$sites=$smallgroups=$ministries=array();
	$maritalSQL=$genderSQL=$memberSQL=$peopleSQL=$smallgroupsSQL=$ministriesSQL=$filteredby=array();
	
	foreach($_POST['check'] AS $key=>$data)
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
	$church_admin_marital_status=array(0=>__('N/A','church-admin'),1=>__('Single','church-admin'),2=>__('Co-habiting','church-admin'),3=>__('Married','church-admin'),4=>__('Divorced','church-admin'),5=>__('Widowed','church-admin'));
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
	 $columns=array('a.people_id','a.household_id','a.first_name','a.last_name','a.people_type_id','a.email','a.mobile','a.sex','b.phone','b.address');
	$tables=array(CA_PEO_TBL.' a',CA_HOU_TBL.' b');
	$table_header=array(__('Edit','church-admin'),__('Delete','church-admin'),__('Name','church-admin'),__('People Type','church-admin.'),__('Phone','church-admin'),__('Mobile','church-admin'),__('Email','church-admin'),__('Address','church-admin'));
	if(!empty($genderSQL)) 		$other.=' AND ('. implode(" OR ",$genderSQL).')';
	if(!empty($peopleSQL)) 		$other.=' AND ('. implode(" OR ",$peopleSQL).')';
	if(!empty($maritalSQL)) 	$other.=' AND ('. implode(" OR ",$maritalSQL).')';
	if(!empty($sitesSQL)) 		{
									$other.=' AND ('. implode(" OR ",$sitesSQL).') AND a.site_id=d.site_id';
									$tables[]=CA_SIT_TBL.' d';
									$columns[]='d.venue';
								}
	if(!empty($smallgroupsSQL)) {
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
	
	$sql='SELECT a.first_name, a.mobile AS mobile, a.people_id FROM '.implode(", ",array_filter($tables)).' WHERE a.household_id=b.household_id '.$other.' AND a.mobile!="" GROUP BY a.mobile  ORDER BY a.last_name';
}

		$results=$wpdb->get_results($sql);
		
	    $mobiles=array();

	    
            
            foreach ($results AS $row)
                {
                    $mobile=str_replace(' ','',$row->mobile);
                    $country=get_option('church_admin_sms_iso');
					$sendmobile=$country.$mobile;//non UK
					if($country==44 && !empty($mobile) && $mobile{0}=='0')//uk remove preceding 0
					{
             			$sendmobile=$country.substr($mobile, 1); 
        			}
                    church_admin_debug($sendmobile);
                    if(!empty($sendmobile))$mobiles[]=$sendmobile;
		}    
		$mobiles=array_unique($mobiles);
		$needed=count($mobiles);
	    echo"$needed credits required<br/>";   

	    $msisdn = implode(',',$mobiles);     
	    $message = stripslashes($_POST['counttxt']);
	    
		
		$url = $sms_type.'/submission/send_sms/2/2.0';
		$post_body = seven_bit_sms( $username, $password, $message, $msisdn );
		$result = send_message( $post_body, $url, $port );
		if( $result['success'] ) 
		{
			print_ln( formatted_server_response( $result ) );
		}
		else {
		print_ln( formatted_server_response( $result ) );
		}
	   
	}//end send sms 
	else
	{
	church_admin_send_sms_form();  
	}
    }//people stored in directory
    }//setting saved so okay
}

function church_admin_send_sms_form()
{
    global $wpdb;
	$member_type=church_admin_member_type_array();
echo'
<script type="text/javascript">
/* <![CDATA[ */

function counterUpdate(opt_countedTextBox, opt_countBody, opt_maxSize) {
        var countedTextBox = opt_countedTextBox ? opt_countedTextBox : "counttxt";
        var countBody = opt_countBody ? opt_countBody : "countBody";
        var maxSize = opt_maxSize ? opt_maxSize : 1024;

        var field = document.getElementById(countedTextBox);

        if (field && field.value.length >= maxSize) {
                field.value = field.value.substring(0, maxSize);
        }
        var txtField = document.getElementById(countBody);
                if (txtField) {  
                txtField.innerHTML = field.value.length;
        }
}
/* ]]> */

</script>
<h1>Send a text message</h1>
<form action="" method="post" name="SMS" id="SMS">
<p><label>Message <span id="countBody">&nbsp;&nbsp;0</span>/160 characters</label><textarea class="sms" id="counttxt" rows="4" cols="50" name="counttxt"  onkeyup="counterUpdate(\'counttxt\', \'countBody\',\'160\');"></textarea></p>
 
';
if ( function_exists('wp_nonce_field') )wp_nonce_field('church admin send sms');
	echo'<h2>Choose recipients...</h2>';
	$smsoremail='mobile';
	$member_type=church_admin_member_type_array();
	echo'<p><label>'.__('Type in recipient names, separated by a comma (filters will be ignored)','church-admin').'</label>'.church_admin_autocomplete('recipients','friends','to','').'</p>';
	echo'<p>'.__('Or use the filters below','church-admin').'<span id="filtered-response">'.__('Everyone will get this, unless you add some filters','church-admin').'</span></p>';
	require_once(plugin_dir_path(__FILE__).'/filter.php');
    church_admin_directory_filter(FALSE);
   
    $nonce = wp_create_nonce("church_admin_filter");
    echo'<script type="text/javascript">
		jQuery(document).ready(function($) {
		
	//handle send button disabled while no selections
     $(\':input[type="submit"]\').prop(\'disabled\', true);
     $(\'input[type="text"]\').keyup(function() {
        if($(this).val() != "") {
           $(\':input[type="submit"]\').prop(\'disabled\', false);
           $("#filtered-response").html("");
        }
     });

		
		
			$(".all").on("change", function(){
				var id = this.id;
			
				$("input."+id).prop("checked", !$("."+id).prop("checked"))
			});
		   $("#filters1").on("change", function(){
				
      			var category_list = [];
      			$("#filters1 :input:checked").each(function(){
        			console.log("FIRED");
        			$(\':input[type="submit"]\').prop(\'disabled\', false);
        			
        			var category = $(this).val();
        			category_list.push(category);
        			
        		});
      		
      			
      			var data = {
				"action": "church_admin_filter_email",
				"type":"'.$smsoremail.'",
				"data": category_list,
				"nonce": "'.$nonce.'"
				};
	$("#filtered-response").html(\'<p style="text-align:center"><img src="'.admin_url().'/images/wpspin_light-2x.gif"/></p>\');
		// since 2.8 ajaxurl is always defined in the admin header and points to admin-ajax.php
		jQuery.post(ajaxurl, data, function(response) {
			$("#filtered-response").html("<h3>"+response+"</h3>");
			$(\':input[type="submit"]\').prop(\'disabled\', false);
		});
			});
		});
	</script>
	';
    
//end of choose recipients


	echo'<p><br style="clear:left"/><input class="button-primary" type="submit" name="submitted" value="Send Message"/></p></form>';  
}




function print_ln($content) {
  if (isset($_SERVER["SERVER_NAME"])) {
    print_r( $content)."<br />";
  }
  else {
    print_r($content)."\n";
  }
}

function formatted_server_response( $result ) {
  $this_result = "";
	if(defined('SMS_test')) return $result;
  if ($result['success']) {
    $this_result .= "Success: batch ID " .$result['api_batch_id']. "API message: ".$result['api_message']. "\nFull details " .$result['details'];
  }
  else {
    $this_result .= "Fatal error: HTTP status " .$result['http_status_code']. ", API status " .$result['api_status_code']. " API message " .$result['api_message']. " full details " .$result['details'];

    if ($result['transient_error']) {
      $this_result .=  "This is a transient error - you should retry it in a production environment";
    }
  }
  return $this_result;
}

function send_message ( $post_body, $url, $port ) {
  /*
  * Do not supply $post_fields directly as an argument to CURLOPT_POSTFIELDS,
  * despite what the PHP documentation suggests: cUrl will turn it into in a
  * multipart formpost, which is not supported:
  */

  $ch = curl_init( );
  curl_setopt ( $ch, CURLOPT_URL, $url );
  curl_setopt ( $ch, CURLOPT_PORT, $port );
  curl_setopt ( $ch, CURLOPT_POST, 1 );
  curl_setopt ( $ch, CURLOPT_RETURNTRANSFER, 1 );
  curl_setopt ( $ch, CURLOPT_POSTFIELDS, $post_body );
  // Allowing cUrl funtions 20 second to execute
  curl_setopt ( $ch, CURLOPT_TIMEOUT, 20 );
  // Waiting 20 seconds while trying to connect
  curl_setopt ( $ch, CURLOPT_CONNECTTIMEOUT, 20 );

  $response_string = curl_exec( $ch );
  $curl_info = curl_getinfo( $ch );

  $sms_result = array();
  $sms_result['success'] = 0;
  $sms_result['details'] = '';
  $sms_result['transient_error'] = 0;
  $sms_result['http_status_code'] = $curl_info['http_code'];
  $sms_result['api_status_code'] = '';
  $sms_result['api_message'] = '';
  $sms_result['api_batch_id'] = '';

  if ( $response_string == FALSE ) {
    $sms_result['details'] .= "cURL error: " . curl_error( $ch ) . "\n";
  } elseif ( $curl_info[ 'http_code' ] != 200 ) {
    $sms_result['transient_error'] = 1;
    $sms_result['details'] .= "Error: non-200 HTTP status code: " . $curl_info[ 'http_code' ] . "\n";
  }
  else {
    $sms_result['details'] .= "Response from server: $response_string\n";
    $api_result = explode( '|', $response_string );
    $status_code = $api_result[0];
    $sms_result['api_status_code'] = $status_code;
    $sms_result['api_message'] = $api_result[1];
    if ( count( $api_result ) != 3 ) {
      $sms_result['details'] .= "Error: could not parse valid return data from server.\n" . count( $api_result );
    } else {
      if ($status_code == '0') {
        $sms_result['success'] = 1;
        $sms_result['api_batch_id'] = $api_result[2];
        $sms_result['details'] .= "Message sent - batch ID $api_result[2]\n";
      }
      else if ($status_code == '1') {
        # Success: scheduled for later sending.
        $sms_result['success'] = 1;
        $sms_result['api_batch_id'] = $api_result[2];
      }
      else {
        $sms_result['details'] .= "Error sending: status code [$api_result[0]] description [$api_result[1]]\n";
      }





    }
  }
  curl_close( $ch );

  return $sms_result;
}

function seven_bit_sms ( $username, $password, $message, $msisdn ) {
  $post_fields = array (
  'username' => $username,
  'password' => $password,
  'message'  => character_resolve( $message ),
  'msisdn'   => $msisdn,
  'allow_concat_text_sms' => 0, # Change to 1 to enable long messages
  'concat_text_sms_max_parts' => 2
  );

  return make_post_body($post_fields);
}

function unicode_sms ( $username, $password, $message, $msisdn ) {
  $post_fields = array (
  'username' => $username,
  'password' => $password,
  'message'  => string_to_utf16_hex( $message ),
  'msisdn'   => $msisdn,
  'dca'      => '16bit'
  );

  return make_post_body($post_fields);
}

function eight_bit_sms( $username, $password, $message, $msisdn ) {
  $post_fields = array (
  'username' => $username,
  'password' => $password,
  'message'  => $message,
  'msisdn'   => $msisdn,
  'dca'      => '8bit'
  );

  return make_post_body($post_fields);

}

function make_post_body($post_fields) {
  $stop_dup_id = make_stop_dup_id();
  if ($stop_dup_id > 0) {
    $post_fields['stop_dup_id'] = make_stop_dup_id();
  }
  $post_body = '';
  foreach( $post_fields as $key => $value ) {
    $post_body .= urlencode( $key ).'='.urlencode( $value ).'&';
  }
  $post_body = rtrim( $post_body,'&' );

  return $post_body;
}

function character_resolve($body) {
  $special_chrs = array(
  'Δ'=>'0xD0', 'Φ'=>'0xDE', 'Γ'=>'0xAC', 'Λ'=>'0xC2', 'Ω'=>'0xDB',
  'Π'=>'0xBA', 'Ψ'=>'0xDD', 'Σ'=>'0xCA', 'Θ'=>'0xD4', 'Ξ'=>'0xB1',
  '¡'=>'0xA1', '£'=>'0xA3', '¤'=>'0xA4', '¥'=>'0xA5', '§'=>'0xA7',
  '¿'=>'0xBF', 'Ä'=>'0xC4', 'Å'=>'0xC5', 'Æ'=>'0xC6', 'Ç'=>'0xC7',
  'É'=>'0xC9', 'Ñ'=>'0xD1', 'Ö'=>'0xD6', 'Ø'=>'0xD8', 'Ü'=>'0xDC',
  'ß'=>'0xDF', 'à'=>'0xE0', 'ä'=>'0xE4', 'å'=>'0xE5', 'æ'=>'0xE6',
  'è'=>'0xE8', 'é'=>'0xE9', 'ì'=>'0xEC', 'ñ'=>'0xF1', 'ò'=>'0xF2',
  'ö'=>'0xF6', 'ø'=>'0xF8', 'ù'=>'0xF9', 'ü'=>'0xFC',
  );

  $ret_msg = '';
  if( mb_detect_encoding($body, 'UTF-8') != 'UTF-8' ) {
    $body = utf8_encode($body);
  }
  for ( $i = 0; $i < mb_strlen( $body, 'UTF-8' ); $i++ ) {
    $c = mb_substr( $body, $i, 1, 'UTF-8' );
    if( isset( $special_chrs[ $c ] ) ) {
      $ret_msg .= chr( $special_chrs[ $c ] );
    }
    else {
      $ret_msg .= $c;
    }
  }
  return $ret_msg;
}

/*
* Unique ID to eliminate duplicates in case of network timeouts - see
* EAPI documentation for more. You may want to use a database primary
* key. Warning: sending two different messages with the same
* ID will result in the second being ignored!
*
* Don't use a timestamp - for instance, your application may be able
* to generate multiple messages with the same ID within a second, or
* part thereof.
*
* You can't simply use an incrementing counter, if there's a chance that
* the counter will be reset.
*/
function make_stop_dup_id() {
  return 0;
}

function string_to_utf16_hex( $string ) {
  return bin2hex(mb_convert_encoding($string, "UTF-16", "UTF-8"));
}

function xml_to_wbxml( $msg_body ) {

  $wbxmlfile = 'xml2wbxml_'.md5(uniqid(time())).'.wbxml';
  $xmlfile = 'xml2wbxml_'.md5(uniqid(time())).'.xml';

  //create temp file
  $fp = fopen($xmlfile, 'w+');

  fwrite($fp, $msg_body);
  fclose($fp);

  //convert temp file
  exec(xml2wbxml.' -v 1.2 -o '.$wbxmlfile.' '.$xmlfile.' 2>/dev/null');
  if(!file_exists($wbxmlfile)) {
    print_ln('Fatal error: xml2wbxml conversion failed');
    return false;
  }

  $wbxml = trim(file_get_contents($wbxmlfile));

  //remove temp files
  unlink($xmlfile);
  unlink($wbxmlfile);
  return $wbxml;
}

?>