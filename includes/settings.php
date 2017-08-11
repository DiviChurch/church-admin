<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly


/**
 * This function sets up which modules are displayed on the tabs, default on install is all displayed
 *
 * Author:     Andy Moyle
 * Author URI: http://www.churchadminplugin.com
 *
 *
 *	2017-01-02 Improved display, using translated strings
 * 
 */
function church_admin_modules()
{
	$modules=get_option('church_admin_modules');
	
	if(!empty($_POST['save-ca-modules']))
	{
		foreach($modules AS $mod=>$status)
		{
			if(empty($_POST[$mod])){$modules[$mod]=FALSE;}else {$modules[$mod]=TRUE;}
		}
		
		update_option('church_admin_modules',$modules);
	}
	else
	{
		echo'<h2 class="modules">'.__('Set which module tabs are visible (Click to reveal)','church-admin').'</h2>';
		echo'<div class="ca-modules" style="display:none"><form action="" method="POST">';
		echo'<table class="form-table"><tbody>';
		$toggle=TRUE;
		foreach($modules AS $mod=>$status)
		{
			//Need to be translateable, so used stored data and convert to translated display string
			switch($mod)
			{
				case'People':$display=__('People','church-admin');break;
				case'Rota':$display=__('Rota','church-admin');break;
				case'Children':$display=__('Children','church-admin');break;
				case'Comms':$display=__('Comms','church-admin');break;
				case'Groups':$display=__('Groups','church-admin');break;
				case'Calendar':$display=__('Calendar','church-admin');break;
				case'Media':$display=__('Media','church-admin');break;
				case'Facilities':$display=__('Facilities','church-admin');break;
				case'Ministries':$display=__('Ministries','church-admin');break;
				case'Services':$display=__('Services','church-admin');break;
				case'Sessions':$display=__('Sessions','church-admin');break;
				case'App':$display=__('App','church-admin');break;
				default:$display=$mod;break;
			}
			if($mod!='Podcast')
			{
				echo'<tr>';
				echo'<th scope="row">'.esc_html($display).'</th><td><input type="checkbox" value="TRUE" name="'.esc_html($mod).'" ';
				if(!empty($status)) echo' checked="checked" ';
				echo' /></td>';
				echo'</tr>';
			}
		}
		
		echo'<tr><th scope="row">&nbsp;</th><td><input type="hidden" name="save-ca-modules" value="TRUE"/><input type="submit" value="'.__('Save','church-admin').'" class="button-primary"/></td></tr>';
		echo'</tbody></table></form></div>';
		echo'<script>jQuery(document).ready(function($) {$(".modules").click(function(){$(".ca-modules").toggle();});});</script><hr/>';
	}
}

/**
 * This function is for email settings
 *
 * Author:     Andy Moyle
 * Author URI: http://www.churchadminplugin.com
 *
 * 
 */
function church_admin_email_settings()
{
	$email_display='display:none';
	if(!empty($_POST['save-email-settings']))
	{
		if(!empty($_POST['quantity'])){update_option('church_admin_bulk_email',$_POST['quantity']);}else{delete_option('church_admin_bulk_email');}
		if(!empty($_POST['cron']))
		{
			update_option('church_admin_cron',$_POST['cron']);
			switch($_POST['cron'])
			{
				case'wp-cron':	
								wp_clear_scheduled_hook('church_admin_bulk_email');
								add_action('church_admin_bulk_email','church_admin_bulk_email');
								$timestamp=time();
								wp_schedule_event($timestamp, 'hourly', 'church_admin_bulk_email');
				break;
				case 'cron':
								wp_clear_scheduled_hook('church_admin_bulk_email');
								echo'<p><a  target="_blank" href="'.site_url().'/?download=cron-instructions&amp;cron-instructions='.wp_create_nonce('cron-instructions').'">'.__('PDF Instructions for email cron setup','church-admin').'</a></td></tr>';
				break;
				default:
								wp_clear_scheduled_hook('church_admin_bulk_email');
								update_option('church_admin_cron','immediate');
	      	    break;
			}
		}
		else{delete_option('church_admin_cron');}
		echo'<div class="notice notice-success inline">'.__('Email Settings Saved','church-admin').'</div>';
		$email_display='display:block';
	}
	
		echo'<h2 class="email">'.__('Email Settings (Click to reveal)','church-admin').'</h2>';
		echo'<div class="ca-email" style="'.$email_display.'">';
	
        echo'<form action="" method="post">';
		echo'<table class="form-table">';
		echo'<tr><th scope="row">'.__('Send Emails Immediately','church-admin').'</th><td><input type="radio" class="immediate"  name="cron" value="immediate" ';
		if (get_option('church_admin_cron')=='immediate') echo 'checked="checked"';
		echo'/> '.__("Use this option if your hosting company doesn't limit how many emails you can send an hour",'church-admin').'</td></tr>';
		
		echo'<tr><th scope="row">'.__('I want to use cron','church-admin').':</th><td><input type="radio"  class="cron" name="cron" value="cron" ';
        if (get_option('church_admin_cron')=='cron') echo 'checked="checked"';
        echo'/>'.__('Use this option if you are on a Linux server and are limited how many emails you can send an hour','church-admin').'</td></tr>';
		echo'<tr><th scope="row">'.__('I want to use wp-cron:','church-admin').'</th><td><input type="radio" class="wp-cron" name="cron" value="wp-cron"';
        if (get_option('church_admin_cron')=='wp-cron') echo 'checked="checked"';
        echo'/>'.__('Use this option if you are on a Windows server and are limited how many emails you can send an hour','church-admin').'</td></tr>';
		echo '<tr class="limited" style="display:none"><th scope="row">'.__('Max emails per hour? (required)','church-admin').'</th><td><input type="text" name="quantity" value="'.get_option('church_admin_bulk_email').'"/></td></tr></td></tr>';
		echo'<tr><th scope="row">&nbsp;</th><td><input type="hidden" name="save-email-settings" value="TRUE"/><input class="button-primary" type="submit" value="'.__('Save','church-admin').'"/></td></tr>';
		echo'</tbody></table></form></div>';
		echo'<script>jQuery(document).ready(function($) {
			$(".email").click(function(){$(".ca-email").toggle();});
			$(".immediate").click(function(){$(".limited").hide();});
			$(".cron").click(function(){$(".limited").show();});
			$(".wp-cron").click(function(){$(".limited").show();});
		});</script><hr/>';
		
}

/**
 * This function sets up smtp settings for wp_mail()
 *
 * Author:     Andy Moyle
 * Author URI: http://www.churchadminplugin.com
 *
 * 
 */
function church_admin_smtp_settings()
{
	
	$smtp_display='display:none';
	if(!empty($_POST['save-smtp-settings']))
	{
		foreach($_POST AS $key=>$value)
		{
			if(!empty($_POST[$key])){$settings[$key]=stripslashes($value);}
		}
		if(!empty($settings))
		{
			update_option('church_admin_smtp_settings',$settings);
			echo'<div class="notice notice-success inline"><p>'.__('SMTP Settings saved','church-admin').'</p></div>';
		}
		else
		{
			delete_option('church_admin_smtp_settings');
			echo'<div class="notice notice-success inline"><p>'.__('SMTP Settings deleted','church-admin').'</p></div>';
		}
		$smtp_display='display:block';
	}

		$settings=get_option('church_admin_smtp_settings');
		echo'<h2 class="smtp">'.__('Use your own smtp server settings for sending email from the website (Click to reveal)','church-admin').'</h2>';
		echo'<div class="ca-smtp" style="'.$smtp_display.'"><p>'.__('Leave blank and save to delete current settings','church-admin').'</p>';
		echo'<p>'.__('Using these settings changes the way Wordpress sends email across your whole site, to using your smtp server','church-admin').'</p>';
		echo'<p><strong>GMAIL smtp no longer works with the settings below, unless you set your Google account to allow less secure apps at <a href="https://www.google.com/settings/security/lesssecureapps">https://www.google.com/settings/security/lesssecureapps</a>, which of course has implications.</strong></p><p>On of which, is that you have to type in your gmail password in WordPress which is visible to all admin users.</p><p><strong>Only do that if you understand the risks! There is a great new plugin that uses Gmails more secure authentication standard <a href="https://wordpress.org/plugins/gmail-smtp/">https://wordpress.org/plugins/gmail-smtp/</a></strong></p>';
		echo'<form action="" method="POST">';
		echo'<table class="form-table"><tbody>';
		
		echo'<tr><th scope="row">SMTP Host</th><td><input type="text" name="host" placeholder="smtp.gmail.com" ';
		if(!empty($settings['host'])) echo ' value="'.esc_html($settings['host']).'" ';
		echo'/></td></tr>';
		echo'<tr><th scope="row">SMTP Authorisation required?</th><td><input type="checkbox" name="auth" value="TRUE" ';
		if(!empty($settings['auth'])) echo ' checked="checked" ';
		echo'/></td></tr>';
		echo'<tr><th scope="row">SMTP Port</th><td><input type="text" name="port" placeholder="465" ';
		if(!empty($settings['port'])) echo ' value="'.esc_html($settings['port']).'" ';
		echo'/></td></tr>';
		echo'<tr><th scope="row">SMTP Username</th><td><input type="text" name="username" placeholder="yourname@gmail.com" ';
		if(!empty($settings['username'])) echo ' value="'.esc_html($settings['username']).'" ';
		echo'/></td></tr>';
		echo'<tr><th scope="row">SMTP Password</th><td><input type="text" name="password" placeholder="password" ';
		if(!empty($settings['password'])) echo ' value="'.esc_html($settings['password']).'" ';
		echo'/></td></tr>';
		echo'<tr><th scope="row">SMTP Security</th><td><select  name="secure"> ';
		if(!empty($settings['secure'])) echo ' <option value="'.esc_html($settings['secure']).'">'.esc_html($settings['secure']).'</option> ';
		echo'<option value="ssl">SSL</option><option value="tls">TLS</option><option value="">None</option>';
		echo'</select></td></tr>';
		echo'<tr><th scope="row">SMTP From Email</th><td><input type="text" name="from" placeholder="yourname@gmail.com" ';
		if(!empty($settings['from'])) echo ' value="'.esc_html($settings['from']).'" ';
		echo'/></td></tr>';
		echo'<tr><th scope="row">SMTP From Name</th><td><input type="text" name="from_name" placeholder="'.__('Your name','church-admin').'" ';
		if(!empty($settings['from_name'])) echo ' value="'.esc_html($settings['from_name']).'" ';
		echo'/></td></tr>';
		echo'<tr><th scope="row">&nbsp;</th><td><input type="hidden" name="save-smtp-settings" value="TRUE"/><input type="submit" value="'.__('Save','church-admin').'"/></td></tr>';
		echo'</tbody></table></form></div><hr/>';
		echo'<script>jQuery(document).ready(function($) {$(".smtp").click(function(){$(".ca-smtp").toggle();});});</script>';

}


/**
 * This function sets up roles
 *
 * Author:     Andy Moyle
 * Author URI: http://www.churchadminplugin.com
 *
 * 
 */
function church_admin_roles()
{
	global $wpdb;
	$roles_display='display:none';
	$levels=get_option('church_admin_levels');
	$available_levels=get_option($wpdb->prefix.'user_roles');
	
	if(!empty($_POST['save-permissions']))
	{
		$form=$newlevels=array();
		foreach($_POST AS $key=>$value)$form[str_replace('_',' ',$key)]=stripslashes($value);
		foreach($form AS $key=>$value)  $newlevels[substr($key,5)]=$value;
		
		foreach($levels AS $key=>$value)
		{
			if(!empty($newlevels[$key])&&array_key_exists($newlevels[$key],$available_levels))$levels[$key]=$newlevels[$key];
	    }
	   
		update_option('church_admin_levels',$levels);
		echo'<div class="notice notice-success inline"><p>'.__('Roles Updated','church-admin').'</p></div>';
		$roles_display='display:block';
	}

	$levels=get_option('church_admin_levels');
	$available_levels=get_option($wpdb->prefix.'user_roles');		
		echo'<h2 class="roles">'.__('Permissions (Click to reveal)','church-admin').'</h2>';
		echo'<div class="ca-roles" style="'.$roles_display.'"><p>'.__('You can either set individuals  or allow roles like admin/editor/subscriber to have permission for various tasks','church-admin').'</p>';
		echo'<p><a href="admin.php?page=church_admin/index.php&amp;tab=settings&amp;action=permissions">'.__('Set individual permissions','church-admin').'</a></p>';
		
		echo'<form action="admin.php?page=church_admin/index.php&amp;action=roles&tab=settings" method="POST">';
		echo'<table class="form-table"><tbody>';
		foreach($levels AS $key=>$value)
		{
			
			//Need to be translateable, so used stored data and convert to translated display string
			switch($key)
			{
				case'People':$display=__('People','church-admin');break;
				case'Rota':$display=__('Rota','church-admin');break;
				case'Children':$display=__('Children','church-admin');break;
				case'Comms':$display=__('Comms','church-admin');break;
				case'Groups':$display=__('Groups','church-admin');break;
				case'Calendar':$display=__('Calendar','church-admin');break;
				case'Media':$display=__('Media','church-admin');break;
				case'Facilities':$display=__('Facilities','church-admin');break;
				case'Ministries':$display=__('Ministries','church-admin');break;
				case'Service':$display=__('Service','church-admin');break;
				case'Sessions':$display=__('Sessions','church-admin');break;
				case'Member Type':$display=__('Member Type','church-admin');break;
				case'Sermons':$display=__('Sermons','church-admin');break;
				case'Prayer Chain':$display=__('Prayer Chain','church-admin');break;
				case'Attendance':$display=__('Attendance','church-admin');break;
				case'Bulk SMS':$display=__('Bulk SMS','church-admin');break;
				case'App':$display=__('App','church-admin');break;
				case'Bulk Email':$display=__('Bulk Email','church-admin');break;
				case'Visitor':$display=__('Visitor','church-admin');break;
				case'Funnel':$display=__('Funnel','church-admin');break;
				default:$display=$key;break;
			}
			echo'<tr><th scope="row">'.$display.'</th><td><select name="level'.$key.'">';
			echo'<option value="'.$value.'" selected="selected">'.$value.'</option>';
			foreach($available_levels AS $avail_key=>$avail_value)echo'<option value="'.$avail_key.'">'.$avail_key.'</option>';
			echo'</select></td></tr>';
		}
		echo'<tr><th scope="row">&nbsp;</th><td><input type="hidden" name="save-permissions" value="TRUE"/><input type="submit" class="button-primary" value="'.__('Save','church-admin').'"/></td></tr>';
		echo'</tbody></table></form></div><hr/>';
		echo'<script>jQuery(document).ready(function($) {$(".roles").click(function(){$(".ca-roles").toggle();});});</script>';
	
}

/**
 * This function is the menu for backup
 *
 * Author:     Andy Moyle
 * Author URI: http://www.churchadminplugin.com
 *
 * 
 */
function church_admin_backup_menu()
{
	$filename=get_option('church_admin_backup_filename');
	echo'<h2 class="backup">'.__('Backup (Click to reveal)','church-admin').'</h2>';
	echo'<div class="ca-backup" style="display:none">';
	echo'<p><a class="button-primary" href="'.wp_nonce_url('admin.php?page=church_admin/index.php&tab=settings&action=refresh_backup','refresh_backup').'">'.__('Refresh Church Admin DB Backup','church-admin').' </a></p>';
	$upload_dir = wp_upload_dir();
	$path=$upload_dir['basedir'];
	if(!empty($filename))$loc=$path.'/church-admin-cache/'.$filename;
	if(!empty($loc)&&file_exists($loc))
    {
		
		echo'<p><a href="'.$upload_dir['baseurl'].'/church-admin-cache/'.$filename.'">'.__('Download Church Admin DB Backup - For recent Updates, it will be for old version','church-admin').'</a></p>';
		echo'<p><a href="'.wp_nonce_url('admin.php?page=church_admin/index.php&tab=settings&action=delete_backup','delete_backup').'">'.__('Delete Church Admin DB Backup - Sensible after download!','church-admin').'</a></p>';
		
    }
	echo'</div>';
	echo'<script>jQuery(document).ready(function($) {$(".backup").click(function(){$(".ca-backup").toggle();});});</script><hr/>';
}

/**
 * This function sets up Bulk SMS
 *
 * Author:     Andy Moyle
 * Author URI: http://www.churchadminplugin.com
 *
 * 
 */
function church_admin_sms_settings()
{
	$sms_display='display:none';
	if(!empty($_POST['save-sms-settings']))
	{
		update_option('church_admin_sms_username',$_POST['sms_username']);
        update_option('church_admin_sms_password',$_POST['sms_password']);
        update_option('church_admin_sms_reply',$_POST['sms_reply']);
        update_option('church_admin_sms_iso',$_POST['sms_iso']);
		update_option('church_admin_bulksms',$_POST['sms_type']);
		//send test message
		$port = 80; 
		$url = get_option('church_admin_bulksms').'/submission/send_sms/2/2.0';
	    $ch = curl_init();
	    curl_setopt($ch, CURLOPT_URL, $url);
	    curl_setopt ($ch, CURLOPT_PORT, $port);
	    curl_setopt ($ch, CURLOPT_POST, 1);
	    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	    $post_body = '';
		$message='Bulk SMS Test message';
		$msisdn=get_option('church_admin_sms_reply');
	    $post_fields = array('username' => get_option('church_admin_sms_username'),'password' => get_option('church_admin_sms_password'),'message' => $message,'msisdn' => $msisdn,'sender' => get_option('church_admin_sms_reply'));
	    foreach($post_fields as $key=>$value)
	    {
		$post_body .= urlencode($key).'='.urlencode($value).'&';
	    }
	    $post_body = rtrim($post_body,'&');

	    # Do not supply $post_fields directly as an argument to CURLOPT_POSTFIELDS,
	    # despite what the PHP documentation suggests: cUrl will turn it into in a
	    # multipart formpost, which is not supported:
	    curl_setopt ($ch, CURLOPT_POSTFIELDS, $post_body);
	    $response_string = curl_exec($ch);
	    $curl_info = curl_getinfo($ch);
	    if ($response_string == FALSE)
	    {
	        print "cURL error: ".curl_error($ch)."\n";
	    }
	    elseif ($curl_info['http_code'] != 200)
	    {
	            print "Error: non-200 HTTP status code: ".$curl_info['http_code']."\n";
	    }
	    else
	    {
	        print "Response from server:";
	        $result = explode('\|', $response_string);
	        if (count($result) != 3)
	        {
	            print "Error: could not parse valid return data from server.\n".count($result);
	        }
	        else
	        {
	    	if ($result[0] == '0')
                {
		    print "Message sent\n";
		}
		else
                {
                    print "Error sending: status code [$result[0]] description [$result[1]]\n";
		}
	    }
	    }
	    curl_close($ch);
	    echo"</p></div>";
		$sms_display='display:block';
		echo'<div class="notice notice-success inline"><p>'.__('SMS Settings updated','church-admin').'</p></div>';
	}
	
		echo'<h2 class="sms-settings">'.__('Bulk SMS Settings (Click to toggle)','church-admin').'</h2>';
		echo'<div class="ca-sms" style="'.$sms_display.'">';
		echo'<p>'.__('Set up an account with','church-admin').' <a href="http://bulksms.com">http://bulksms.com</a> '.__('Prices start at 3.9p (GBP) per sms','church-admin').'</td></tr>';
		echo'<p>'.__('Once you have registered fill out the form below','church-admin').'</td></tr>';
		$sms_type=get_option('church_admin_bulksms');
		echo'<p>'.__('Please login into your bulksms.com account and find out your EAPI url from the ','church-admin').'<a href="https://www2.bulksms.com/home/profile/">'.__('profile page','church-admin').'</a></p>';
		echo'<form action="" method="POST"><table class="form-table"><tbody><tr><th scope="row">Bulksms EAPI url</th><td><input type="text" name="sms_type" ';
		if(!empty($sms_type)){ echo' value="'.esc_html($sms_type).'" ';}
		echo'/></td></tr>';
		echo'<tr><th scope="row" >'.__('SMS username','church-admin').'</th><td><input type="text" name="sms_username" value="'.get_option('church_admin_sms_username').'" /></td></tr>';
		echo'<tr><th scope="row">'.__('SMS password','church-admin').'</th><td><input type="text" name="sms_password" value="'.get_option('church_admin_sms_password').'" /></td></tr>';
		echo'<tr><th scope="row" >'.__('SMS reply eg:447777123456','church-admin').'</th><td><input type="text" name="sms_reply" value="'.get_option('church_admin_sms_reply').'" /></td></tr>';
		echo'<tr><th scope="row" >'.__('Country code eg 44','church-admin').'</th><td><input type="text" name="sms_iso" value="'.get_option('church_admin_sms_iso').'" /></td></tr>';
		echo'<tr><th scope="row" >&nbsp;</th><td><input type="hidden" name="save-sms-settings" value="1"/><input class="button-primary" type="submit"  value="'.__('Save Settings','church-admin').' &raquo;" /></td></tr></tbody></table></form>';
		echo'</div>';
		echo'<script>jQuery(document).ready(function($) {$(".sms-settings").click(function(){$(".ca-sms").toggle();});});</script><hr/>';
	
}

/**
 * This function general settings
 *
 * Author:     Andy Moyle
 * Author URI: http://www.churchadminplugin.com
 *
 * 
 */
 
 function church_admin_general_settings()
 {
	$display='display:none';
	if(!empty($_POST['save-general-settings']))	
	{
		update_option('church_admin_pdf_size',$_POST['pdf_size']);
		if(empty($_POST['use_prefix'])){update_option('church_admin_use_prefix',FALSE);}else{update_option('church_admin_use_prefix',TRUE);}
		if(empty($_POST['use_middle'])){update_option('church_admin_use_middle_name',FALSE);}else{update_option('church_admin_use_middle_name',TRUE);}
		if(empty($_POST['use_nickname'])){update_option('church_admin_use_nickname',FALSE);}else{update_option('church_admin_use_nickname',TRUE);}
		if(!empty($_POST['google_api']))update_option('church_admin_google_api_key',$_POST['google_api']);
		if(!empty($_POST['pagination']))update_option('church_admin_pagination_limit',intval($_POST['pagination']));
		 if(isset($_POST['church_admin_calendar_width']) && ctype_digit($_POST['calendar_width']))update_option('church_admin_calendar_width',$_POST['calendar_width']);	
		if(isset($_POST['church_admin_label']))
		{
		switch($_POST['church_admin_label'])
		{
			case 'L7163': $option='L7163';break;
			case '5160': $option='5160';break;
			case '5161': $option='5161';break;
			case '5162': $option='5162';break;
			case '5163': $option='5163';break;
			case '5164': $option='5164';break;
			case '8600': $option='8600';break;
			case '3422': $option='3422';break;
			default :$option='L7163';break;
		}
		update_option('church_admin_label',$option);
		}else{delete_option('church_admin_label');}
		echo'<div class="notice notice-success inline"><p>'.__('General Settings updated','church-admin').'</p></div>';
		$display='display:block';
	}
	

		echo'<h2 class="general-settings">'.__('General Settings (Click to toggle)','church-admin').'</h2>';
		echo'<div class="ca-general" style="'.$display.'">';
		echo'<form action="" method="POST"><table class="form-table"><tbody>';
		echo'<tr><th scope="row">'.__('Use prefix for names','church-admin').'</th><td><input type="checkbox" name="use_prefix" value="TRUE" ';
		$prefix=get_option('church_admin_use_prefix');
		if($prefix) echo ' checked="checked" ';
		echo '/></td></tr>';
		echo'<tr><th scope="row">'.__('Use middle name for names','church-admin').'</th><td><input type="checkbox" name="use_middle" value="TRUE" ';
		$middle=get_option('church_admin_use_middle_name');
		if($middle) echo ' checked="checked"';
		echo '/></td></tr>';
		echo'<tr><th scope="row">'.__('Add nickname for names','church-admin').'</th><td><input type="checkbox" name="use_nickname" value="TRUE" ';
		$nickname=get_option('church_admin_use_nickname');
		if($nickname)echo ' checked="checked"';
		echo '/></td></tr>';		
		echo'<tr><th scope="row">'.__('Google Maps API key','church-admin').'</th><td><input type="text" name="google_api" value="'.get_option('church_admin_google_api_key').'"/></td></tr>';
		
		echo'<tr><th scope="row">'.__('Directory Records per page','church-admin').'</th><td><input type="text" name="pagination" value="'.get_option('church_admin_pagination_limit').'"/></td></tr>';
		echo '<tr><th scope="row">'.__('Calendar width in pixels','church-admin').'</th><td><input type="text" name="calendar_width" value="'.get_option('church_admin_calendar_width').'"/></td></tr>';
		echo '<tr><th scope="row">'.__('PDF Page Size','church-admin').'</th><td><select name="pdf_size">';
		$pdf_size=get_option('church_admin_pdf_size');
		echo'<option value="A4" '.selected($pdf_size,'A4').'>A4</option>';
		echo'<option value="Letter" '.selected($pdf_size,'Letter').'>Letter</option>';
		echo'<option value="Legal" '.selected($pdf_size,'Legal').'>Legal</option>';
		echo'</select></td></tr>';
		echo '<tr><th scope="row">Avery &#174; Label</th><td><select name="church_admin_label">';

		$l=get_option('church_admin_label');
		echo'<option value="L7163"';
		if($l=='L7163') echo' selected="selected" ';
		echo'>L7163</option>';
		echo'<option value="5160"';
		if($l=='5160') echo' selected="selected" ';
		echo'>5160</option>';
		echo'<option value="5161';
		if($l=='5161') echo' selected="selected" ';
		echo'>5161</option>';
		echo'<option value="5162"';
		if($l=='5162') echo' selected="selected" ';
		echo'>5162</option>';
		echo'<option value="5163"';
		if($l=='5163') echo' selected="selected" ';
		echo'>5163</option>';
		echo'<option value="5164"';
		if($l=='5164') echo' selected="selected" ';
		echo'>5164</option>';
		echo'<option value="8600"';
		if($l=='8600') echo' selected="selected" ';
		echo'>8600</option>';
		echo'<option value="3422"';
		if($l=='3422') echo' selected="selected" ';
		echo'>3422</option></select></td></tr>';
		
		echo'<tr><th scope="row" >&nbsp;</th><td><input type="hidden" name="save-general-settings" value="1"/><input class="button-primary" type="submit"  value="'.__('Save Settings','church-admin').' &raquo;" /></td></tr></tbody></table></form>';
		echo'<script>jQuery(document).ready(function($) {$(".general-settings").click(function(){$(".ca-general").toggle();});});</script><hr/>';

	
	 
	 
 }
 
 /**
 * This function lists people types
 *
 * Author:     Andy Moyle
 * Author URI: http://www.churchadminplugin.com
 * from v1.05
 * 
 */
 
 function church_admin_people_types_list()
 {
 
 	$out='<h2 class="ptype-settings">'.__('People Types (Click to toggle)','church-admin').'</h2>';
	$out.='<div class="ca-ptype" style="display:none">';
 	
 	$out.='<p>'.__('If you add people types to "Adult", "Child"/"Teen" the shortcode [church_admin type="address-list"] will not work as expected! Use [church_admin type="directory"] instead','church-admin').'</p>';
 	$out.='<p><a href="'.wp_nonce_url('admin.php?page=church_admin/index.php&tab=settings&action=edit_people_type','edit_people_type').'">'.__('Add People Type','church-admin').'</a></p>';
	$thead='<tr><th>'.__('Edit','church-admin').'</th><th>'.__('Delete','church-admin').'</th><th>'.__('People Type','church-admin').'</th></tr>';	
 	$out.='<table class="widefat"><thead>'.$thead.'</thead><tfoot>'.$thead.'</tfoot><tbody>';
	$people_types=get_option('church_admin_people_type');
 	foreach($people_types AS $ID=>$type)
 	{
 		if($ID>2){$edit='<a href="'.wp_nonce_url('admin.php?page=church_admin/index.php&tab=settings&action=edit_people_type&amp;ID='.intval($ID),'edit_people_type').'">'.__('Edit','church-admin').'</a>';}else{$edit='&nbsp;';}
 		if($ID>2){$delete='<a href="'.wp_nonce_url('admin.php?page=church_admin/index.php&tab=settings&action=delete_people_type&amp;ID='.intval($ID),'delete_people_type').'">'.__('Delete','church-admin').'</a>';}else{$delete='&nbsp;';}
 		$out.='<tr><td>'.$edit.'</td><td>'.$delete.'</td><td>'.esc_html($type).'</td></tr>';
 	}
	$out.='</tbody></table>'; 
	$out.='</div>';
	$out.='<script>jQuery(document).ready(function($) {$(".ptype-settings").click(function(){$(".ca-ptype").toggle();});});</script><hr/>';
 	return $out;
 }
 
 function church_admin_edit_people_type($ID)
 {
 		$people_types=get_option('church_admin_people_type');
 		
 		if(!empty($_POST['people_type']))
 		{
 			$out='<div class="notice notice-success inline">'.__('People type saved','church-admin').'</div>';
 			$new=stripslashes($_POST['people_type']);
 			if(!empty($ID))$people_types[$ID]=$new;
 			if(empty($ID)&& !empty($new)&&!in_array($new,$people_types))$people_types[]=$new;
 			update_option('church_admin_people_type',$people_types);
 		}
			$people_types=get_option('church_admin_people_type');
 			$out='<h2>'.__('Edit People Type','church-admin').'</h2>';
 			$out.='<form action="" method="POST"><p><label>'.__('People Type','church-admin').'</label><input type="text" name="people_type"';
 			if(!empty($ID)&&!empty($people_types[$ID]))$out.=' value="'.esc_html($people_types[$ID]).'" ';
 			$out.='/><input type="submit" class="button-primary" value="'.__('Save','church-admin').'"/></p></form>';

 		return $out;
 }
 
  function church_admin_delete_people_type($ID)
 {
 		$people_types=get_option('church_admin_people_type');
 		unset($people_types[$ID]);
 		update_option('church_admin_people_type',$people_types);
 		$out='<div class="notice notice-success inline">'.__('People type deleted','church-admin').'</div>';
 		return $out;
 }		