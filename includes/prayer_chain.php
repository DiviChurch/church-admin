<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
function church_admin_prayer_chain()
{
/**
 *
 * Send prayer chain message by sms or email
 * 
 * @author  Andy Moyle
 * @param    null
 * @return   
 * @version  0.1
 * 
 */ 

	global $wpdb,$current_user;

 	$current_user=wp_get_current_user();
	$user=$wpdb->get_row('SELECT CONCAT_WS(" ",first_name,prefix,last_name) AS name, email FROM '.CA_PEO_TBL.' WHERE user_id="'.intval($current_user->ID).'"');
	$username=get_option('church_admin_sms_username');
	$password=get_option('church_admin_sms_password');
	$sender=get_option('church_admin_sms_reply'); 
	$port = 80;  
	echo '<h1>'.__('Prayer Chain','church-admin').' </h1>';
	
	if(!empty($_POST['add_to_prayer_chain']))
	{
		$id=church_admin_get_people_id($_POST['name']);
		if(!empty($id))
		{
			$ids=maybe_unserialize($id);
			foreach($ids as $key=>$value)
			{
				$wpdb->query('UPDATE '.CA_PEO_TBL.' set prayer_chain=1 WHERE people_id="'.esc_sql(intval($value)).'"');
				echo'<div class="notice notice-success inline"><p><strong>'.esc_html(sprintf(__('%1$s added to prayer chain','church-admin'),church_admin_get_person($value))).' </strong></p></div>';
			}
			
		}
	}
	if(!empty($_POST['delete_from_prayer_chain']))
	{
		$id=church_admin_get_people_id($_POST['name']);
		if(!empty($id))
		{
			$ids=maybe_unserialize($id);
			foreach($ids as $key=>$value)
			{
				$wpdb->query('UPDATE '.CA_PEO_TBL.' set prayer_chain=0 WHERE people_id="'.esc_sql(intval($value)).'"');
				echo'<div class="notice notice-success inline"><p>'.esc_html(sprintf(__('%1$s taken off prayer chain','church-admin'),$_POST['name'])).'<strong></p></div>';
			}
			
		}
	}
	$results=$wpdb->get_results('SELECT CONCAT_WS(" ", first_name,last_name) AS name FROM '.CA_PEO_TBL.' WHERE prayer_chain=1 ORDER BY last_name,first_name');
	if(!empty($results))
	{
		echo'<h2>'.__('The prayer chain is made up of...','church-admin').'</h2><p>';
		foreach($results AS $row) echo esc_html($row->name).', ';
		echo'</p>';
	}
	$count=$wpdb->get_var('SELECT COUNT(prayer_chain) FROM '.CA_PEO_TBL.' WHERE prayer_chain=1');
	if(!empty($_POST['send_prayer_message']))
	{
		$sql='SELECT  DISTINCT mobile,email,CONCAT_WS(" ",first_name,last_name) AS name FROM '.CA_PEO_TBL.' WHERE  prayer_chain=1';
		$results=$wpdb->get_results($sql);
			if(!empty($_POST['counttxt'])&&!empty($username))
			{//send sms
				require_once(plugin_dir_path(dirname(__FILE__)).'includes/sms.php');
				if(!empty($results))
				{
					$mobiles=array();
					foreach ($results AS $row)
					{
						$mobile=str_replace(' ','',$row->mobile);
						//if starts with 0 replace with 44
						if(!empty($mobile)&&$mobile{0}=='0')
						{
							$row->mobile=get_option('church_admin_sms_iso').substr($mobile, 1); 
						}
						if(!empty($mobile))$mobiles[]=$row->mobile;
					}    
					$mobiles=array_unique($mobiles);
					$msisdn = implode(',',$mobiles);
					$message = stripslashes($_POST['counttxt']);
					
					$sms_type=get_option('church_admin_bulksms');
					
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
					
				}
				else
				{
					echo '<p>'.__('Nobody is on the prayer chain yet','church-admin').'</p>';
				}
				
			
			}//end sms
			if(!empty($_POST['message']))
			{
				$message=stripslashes($_POST['message']);
				if(!empty($results))
				{
					
					foreach($results AS $row)
					{
						if(get_option('church_admin_cron')!='immediate')
                		{
					
							if(QueueEmail($row->email,__('Prayer Chain Request','church-admin'),$message,NULL,$user->name,$user->email,'')) echo'<p>'.esc_html(sprintf(__('%1$s queued','church-admin'),$row->email)).'</p>';
						}
						else
						{
							add_filter('wp_mail_content_type','church_admin_email_type');
							if(!empty($row->email))
							{
								//use native wordpress
						
								$headers="From: ".esc_html($user->name)." <".esc_html($user->email).">\n";
						
								if(wp_mail($row->email,'Prayer Chain Request',$message,$headers,NULL)){echo'<p>'.esc_html(sprintf(__('%1$s sent immediately','church-admin'),$row->email)).'</p>';}
								else {echo $GLOBALS['phpmailer']->ErrorInfo;}
							} 
							remove_filter('wp_mail_content_type','church_admin_email_type');
						}
					}
				}
		
			}
	}
	else
	{
			
			echo '<form action="" method="POST"><p><label>Add to Chain</label><input type="text" name="name" placeholder="'.__('Name','church-admin').'"/><input type="hidden" name="add_to_prayer_chain"
			value="yes"/><input type="submit" value="'.__('Add','church-admin').'"/></form></p>';
			echo'';
			echo '<form action="" method="POST"><p><label>'.__('Take off Chain','church-admin').'</label><input type="text" name="name" placeholder="'.__('Name','church-admin').'"/><input type="hidden" name="delete_from_prayer_chain" value="yes"/><input type="submit" value="'.__('Take off','church-admin').'"/></form></p>';
			echo '<h2>'.esc_html(sprintf(__('Send a message to  %1$s people in the chain','church-admin'),$count)).'</h2><form action="" method="POST">';
			if(!empty($username)) echo '<h3>'.__('Text Message','church-admin').' <span id="countBody">&nbsp;&nbsp;0</span>/140 characters</h3><p><textarea class="sms" id="counttxt" rows="4" cols="50" name="counttxt"  onkeyup="counterUpdate(\'counttxt\', \'countBody\',\'140\');"></textarea></p>';
			echo'<h3>'.__('And/Or Email version','church-admin').'</h3>';
			wp_editor('','message');
			if(!empty($username)) echo '<script type="text/javascript">
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
</script>';
echo'<p><input type="hidden" name="send_prayer_message" value="yes"/><input type="submit" class="button-primary" name="Submit" value="'.__('Send message','church-admin').'"/></p></form>';


	
	}
	

}
?>