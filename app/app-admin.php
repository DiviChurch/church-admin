<?php
/**
 *
 * Admin function for app
 * 
 * @author  Andy Moyle
 * @param    null
 * @return   html
 * @version  0.1
 * 
 */ 
function church_admin_app()
{

	//initialise
	global $wpdb;
	echo'<h1>Church Admin App Admin</h1>';
	
	if(!empty($_POST['licence-key']))
	{
		update_option('church_admin_app_licence',stripslashes($_POST['licence-key']));
		$licence=get_option('church_admin_app_licence');
		if(!empty($licence))
		{
			$app_home=get_option('church_admin_app_home');
	
			if(empty($app_home))
			{
				//  Initiate curl
				$ch = curl_init();
		
				curl_setopt($ch,CURLOPT_RETURNTRANSFER,TRUE);
				// Set the url
				$url='https://www.churchadminplugin.com/wp-admin/admin-ajax.php?action=ca_download_church&url='.site_url();
				church_admin_debug($url);
				curl_setopt($ch, CURLOPT_URL,$url);
				// Execute
				$result=curl_exec($ch);
				// Closing
				curl_close($ch);
				$data=json_decode($result,TRUE);
				if(!empty($data))
				{
					church_admin_debug(print_r($data,TRUE));
					update_option('church_admin_app_logo',$data['logo']);
					update_option('church_admin_app_home',$data['home']);
					update_option('church_admin_app_giving',$data['giving']);
					update_option('church_admin_app_groups',$data['groups']);
				}
			}
		}

	}
	$licence=get_option('church_admin_app_licence');
	
	if(empty($licence)||$licence!=md5('licence'.site_url()))
	{
		//no licence yet
		echo '<div id="iphone" class="alignleft"><iframe src="'.plugins_url('/app/demo/index.html',dirname(__FILE__) ).'" width=475 height=845 class="demo-app"></iframe></div>';
		echo'<form action="" method=POST><p>'.__('Enter App Licence Key','church-admin').'<input type="text" name="licence-key"/><input type="submit" class="button-primary"/></p></form>';
				
		echo'<h2>Sign up for the app!</h2><p>Why not sign up for the iOS and Android App for your church? It will connect your members to a searchable address list, rota/schedule, calendar, blog posts, giving, small groups and a Bible reading plan you can set.</p><p>Simply fill out this paypal subscription of &pound;5 a month to get your church up and running with the app.</p>';		
		echo'<p><form action="https://www.paypal.com/cgi-bin/webscr" method="post"><input type="hidden" name="cmd" value="_xclick-subscriptions"/><input type="hidden" name="custom" value="'.site_url().'"/><input type="hidden" name="currency_code" value="GBP"/><input type="hidden" name="a3" value="5.00"><input type="hidden" name="item_name" value="WP Church App Subscription for '.site_url().'"/><input type="hidden" name="business" value="support@churchadminplugin.com"/> <input type="hidden" name="src" value="1"/> <input type="hidden" name="p3" value="1"/> <input type="hidden" name="t3" value="M"/> <input type="image" name="submit"   src="https://www.paypalobjects.com/webstatic/en_US/i/btn/png/btn_subscribe_cc_147x47.png"  alt="Subscribe"/>  <img alt="" width="1" height="1" src="https://www.paypalobjects.com/en_US/i/scr/pixel.gif" ></form></p>';
				
		echo'<h3>Try out the app...</h3><p>
<a href="https://itunes.apple.com/gb/app/wp-church/id1179763413?mt=8">Install app on your iPhone now</a> and <a href="https://play.google.com/store/apps/details?id=com.churchadminplugin.wpchurch">Android</a></p>';

	}
	else
	{
		church_admin_push_notifications_settings();
		church_admin_app_content();
		church_admin_app_member_types();
		church_admin_bible_reading_plan();
		church_admin_bible_version();
		church_admin_app_logins();
	
	
	}




}

function church_admin_push_notifications_settings()
{
	if(!empty($_POST['app_login']))
	{
		update_option('church_admin_app_api_key',stripslashes($_POST['cacom_api_key']));
		update_option('church_admin_app_id',stripslashes($_POST['cacom_app_id']));
	}
	echo'<h2 class="login-toggle">'.__('Push Notifications Settings (Click to toggle view)','church-admin').'</h2>';
	echo'<div class="login" style="display:none">';
	echo'<form action="" method="POST">';
	echo'<table class="form-table">';
	echo'<tr><th scope="row">API key</th><td><textarea name="cacom_api_key">'.get_option('church_admin_app_api_key').'</textarea></td></tr>';
	echo'<tr><th scope="row">App ID</th><td><input type="text" name="cacom_app_id" value="'.get_option('church_admin_app_id').'"/></td></tr>';
	echo'<tr><td colspacing=2><input type="hidden" name="app_login" value="yes"/><input type="submit" class="button-primary" value="'.__('Save','church-admin').'"/></td></tr></table></form>';	
	echo'</div>';
	echo'<script type="text/javascript">jQuery(function(){  jQuery(".login-toggle").click(function(){jQuery(".login").toggle();  });});</script>';
}


function church_admin_app_content()
{
	echo'<h2 class="content-toggle">'.__('App Content (Click to toggle view)','church-admin').'</h2>';
	echo'<div class="app-content" style="display:none">';
	if(!empty($_POST['app_content']))
	{
		update_option('church_admin_app_home',stripslashes($_POST['home']));
		update_option('church_admin_app_giving',stripslashes($_POST['giving']));
		update_option('church_admin_app_groups',stripslashes($_POST['groups']));
		echo'<div class="notice notice-success inline"><h2>App Content saved</h2></div>';
	}
	$home=get_option('church_admin_app_home');
	$giving=get_option('church_admin_app_giving');
	$groups=get_option('church_admin_app_groups');
	echo'<form action="" method="POST">';
	echo'<table class="form-table">';
	echo'<tr style="vertical-align:top"><td><h2>Home page</h2>';
	echo'<textarea cols=60 rows=50 name="home">'.$home.'</textarea></td>';
	echo'<td><h2>Groups page</h2>';
	echo'<p>Text for before list of groups</p>';
	echo'<textarea  cols=60 rows=50 name="groups">'.$groups.'</textarea></td>';
	echo'<td><h2>Givings page</h2>';
	echo'<p>Text for before list of groups</p>';
	echo'<textarea  cols=60 rows=50  name="giving">'.$giving.'</textarea></td></tr>';	
	echo'<tr><td colspacing=3><input type="hidden" name="app_content" value="TRUE"/><input type="submit" class="button-primary" value="Save"/></td></tr></table></form>';
	echo'</div>';
	echo'<script type="text/javascript">jQuery(function(){  jQuery(".content-toggle").click(function(){jQuery(".app-content").toggle();  });});</script>';
}



function church_admin_bible_version()
{
	echo'<h2 class="version-toggle">'.__('Which Bible version?  (Click to toggle)','church-admin').'</h2>';
	echo'<div class="bible-version" style="display:none">';
	if(!empty($_POST['version']))
	{
		switch($_POST['version'])
		{
			case'ESV':$version='ESV';break;
			case'KJV':$version='KJV';break;
			default:$version='ESV';break;
		
		}
		update_option('church_admin_bible_version',$version);
	}
	
		$version=get_option('church_admin_bible_version');
		echo'<form action="" method="POST">';
		
		echo'<p><input type="radio" name="version" value="ESV"';
		if(empty($version)||$version=='ESV')echo ' checked="checked" ';
		echo'/> ESV</p>';
		echo'<p><input type="radio" name="version" value="KJV"';
		if(!empty($version)&&$version=='KJV')echo ' checked="checked" ';
		echo'/> KJV</p>';
		echo'<p><input type="submit" value="'.__('Save','church-admin').'" class="button-primary"/></p></form>';
		
	echo'</div>';
	echo'<script type="text/javascript">jQuery(function(){  jQuery(".version-toggle").click(function(){jQuery(".bible-version").toggle();  });});</script>';

}


/**
 *
 * Church Admin App Lout person
 * 
 * @author  Andy Moyle
 * @param    null
 * @return   
 * @version  0.1
 * 
 */
 function church_admin_logout_app($people_id)
 {
 	global $wpdb;
 	$wpdb->query('DELETE FROM '.CA_APP_TBL.' WHERE people_id="'.intval($people_id).'"');
 	church_admin_app();
 }
/**
 *
 * Church Admin App Logins
 * 
 * @author  Andy Moyle
 * @param    null
 * @return   
 * @version  0.1
 * 
 */ 
function church_admin_app_logins()
{
	echo '<h2 class="logged-toggle">'.__('Logged in App Users (Click to toggle)','church-admin').'</h2>';
	echo'<div class="app-logins" style="display:none">';
	global $wpdb;
	$sql='SELECT a.*,CONCAT_WS(" ",b.first_name,b.last_name) AS name FROM '.CA_APP_TBL.' a LEFT JOIN '.CA_PEO_TBL.' b ON a.user_id=b.user_id ORDER BY a.last_login DESC';
	
	$results=$wpdb->get_results($sql);
	if(!empty($results))
	{
		echo'<table class="widefat striped"><thead><tr><th>'.__('Logout','church-admin').'</th><th>'.__('User','church-admin').'</th><th>'.__('Last login','church-admin').'</th><th>'.__('Last Page Visited','church-admin').'</th></tr></thead><tfoot><tr><th>'.__('Logout','church-admin').'</th><th>'.__('User','church-admin').'</th><th>'.__('Last login','church-admin').'</th><th>'.__('Last Page Visited','church-admin').'</th></tr></tfoot>';
		foreach($results AS $row)
		{
			$logout='<a class="button-secondary" href="'.wp_nonce_url('admin.php?page=church_admin/index.php&amp;action=logout_app&amp;people_id='.intval($row->people_id),'logout_app').'">'.__('Logout','church-admin').'</a>';
			echo'<tr><td>'.$logout.'</td><td>'.esc_html($row->name).'</td><td>'.mysql2date(get_option('date_format').' '.get_option('time_format'),$row->last_login).'</td><td>'.esc_html($row->last_page).'</td></tr>';
		}
		echo'</tbody></table>';
	}else{echo'<p>'.__('No-one is logged in','church-admin').'</p>';}
	echo'</div>';
	echo'<script type="text/javascript">jQuery(function(){  jQuery(".logged-toggle").click(function(){jQuery(".app-logins").toggle();  });});</script>';

} 
 
/**
 *
 * Church Admin App Member Types
 * 
 * @author  Andy Moyle
 * @param    null
 * @return   
 * @version  0.1
 * 
 */ 
 function church_admin_app_member_types()
 {
 		global $wpdb;
 		$member_types=church_admin_member_type_array();
 		echo'<h2 class="member-toggle">'.__('Which Member Types are viewable on the app','church-admin').'</h2>';
 		echo'<div class="member-types" style="display:none">';
 		if(!empty($_POST['save-app-member-types']))
 		{
 			
 			$newmt=array();
 			foreach($_POST['types'] AS $key=>$value)
 			{
 				if(array_key_exists($value,$member_types))$newmt[]=intval($value);
 			}
 			
 			update_option('church_admin_app_member_types',$newmt);
 		}
 		$mt=get_option('church_admin_app_member_types');
 		
 		echo'<form action="" method="POST">';
 		foreach($member_types AS $key=>$value)
 		{
 			echo'<p><input type=checkbox value="'.intval($key).'" name="types[]" ';
 			if(!empty($mt)&&is_array($mt)&& in_array($key,$mt))echo' checked="checked" ';
 			echo'/>'.esc_html($value).'</p>';
 			
 		}
 		echo'<p><input type="hidden" name="save-app-member-types" value="yes"/><input type="submit" class="button-primary" value="'.__('Save','church-admin').'"/></p></form>';
 	echo'</div>';
	echo'<script type="text/javascript">jQuery(function(){  jQuery(".member-toggle").click(function(){jQuery(".member-types").toggle();  });});</script>';

 }
 
/**
 *
 * Bible Reading Plan
 * 
 * @author  Andy Moyle
 * @param    null
 * @return   
 * @version  0.1
 * 
 */ 
function church_admin_bible_reading_plan()
{
	global $wpdb;
 		echo'<h2 class="plan-toggle">'.__('Which Bible Reading plan? (Click to toggle)','church-admin').'</h2>';
 		echo'<div class="bible-plans" style="display:none">';
	
	if(!empty($_POST['save_csv']))
	{
		if(!empty($_FILES) && $_FILES['file']['error'] == 0)
		{
			$wpdb->query('TRUNCATE TABLE '.CA_BRP_TBL);
			$plan=stripslashes($_POST['reading_plan_name']);
			update_option('church_admin_brp',$plan);
			$filename = $_FILES['file']['name'];
			$upload_dir = wp_upload_dir();
			$filedest = $upload_dir['path'] . '/' . $filename;
			if(move_uploaded_file($_FILES['file']['tmp_name'], $filedest))echo '<p>'.__('File Uploaded and saved','church-admin').'</p>';
			
			ini_set('auto_detect_line_endings',TRUE);
			$file_handle = fopen($filedest, "r");
			$ID=1;
			while (($data = fgetcsv($file_handle, 1000, ",")) !== FALSE) 
			{
				$reading=array();
				foreach($data AS $key=>$value)$reading[]=$value;
				$reading=serialize($reading);
				$wpdb->query('INSERT INTO '.CA_BRP_TBL.' (ID,readings)VALUES("'.$ID.'","'.esc_sql($reading).'")');
				$ID++;
			}
		}
	}
	else
	{
		$plan=get_option('church_admin_brp');
		if(!empty($plan)) echo'<h3>'.__('Current Bible Reading plan name','church-admin').':'. esc_html($plan).'</h3>';
		echo'<p>'.__('Import new Bible reading CSV - 365 rows day per row, comma separated passages','church-admin').'</p>';
		echo'<form action="" method="POST" enctype="multipart/form-data">';
		echo'<p><label>'.__('Reading Plan Name','church-admin').'</label><input name="reading_plan_name" type="text"/></p>';
		echo'<p><label>'.__('CSV File','church-admin').'</label><input type="file" name="file"/><input type="hidden" name="save_csv" value="yes"/></p>';
		echo'<p><input  class="button-primary" type="submit" Value="'.__('Upload','church-admin').'"/></p></form>';
	}
	echo'</div>';
	echo'<script type="text/javascript">jQuery(function(){  jQuery(".plan-toggle").click(function(){jQuery(".bible-plans").toggle();  });});</script>';

}


function church_admin_app_last_visited($page,$token)
{
	global $wpdb;
	$sql='UPDATE '.CA_APP_TBL.' SET last_page="'.esc_sql($page).'",last_login=NOW() WHERE UUID="'.esc_sql($token).'"';
	church_admin_debug($sql);
	$wpdb->query($sql);
}


/**
 *
 * Checks token
 * 
 * @author  Andy Moyle
 * @param    null
 * @return   
 * @version  0.1
 * 
 */ 
function ca_check_token()
{
		global $wpdb;
		$output=array('error'=>'login required');
		if(empty($_REQUEST['token']))
		{
			$output=array('error'=>'login required');
		}
		else
		{
			$sql='SELECT user_id FROM '.CA_APP_TBL.' WHERE UUID="'.esc_sql(stripslashes($_REQUEST['token'])).'"';
			$result=$wpdb->get_var($sql);
			if(!empty($result))
			{
				$output=array(TRUE);
				$wpdb->query('UPDATE '.CA_APP_TBL.' SET last_login=NOW() WHERE UUID="'.esc_sql(stripslashes($_REQUEST['token'])).'"');		
			}
			else
			{
				$output=array('error'=>'login required');
			}
		}		
		header('Access-Control-Max-Age: 1728000');
		header('Access-Control-Allow-Origin: *');
		header('Access-Control-Allow-Methods: *');
		header('Access-Control-Allow-Headers: Content-MD5, X-Alt-Referer');
		header('Access-Control-Allow-Credentials: true');
		echo json_encode($output);
		exit();
}
add_action("wp_ajax_ca_check_token", "ca_check_token");
add_action("wp_ajax_nopriv_ca_check_token", "ca_check_token");
/**
 *
 * Returns media
 * 
 * @author  Andy Moyle
 * @param    null
 * @return   
 * @version  0.1
 * 
 */ 

function ca_sermons()
{
		global $wpdb;
		
		if(!empty($_GET['token']))church_admin_app_last_visited(__('Sermons','church-admin'),$_GET['token']);
		$url=content_url().'/uploads/sermons/';
		$output=array();
		
		$sql='SELECT * FROM '.CA_FIL_TBL.' ORDER BY pub_date DESC LIMIT 5';
		
		$results=$wpdb->get_results($sql);
		
		if(!empty($results))
		{
			foreach($results AS $row)
			{
			
				
				$output[]=array('title'=>esc_html($row->file_title),'id'=>intval($row->file_id),'description'=>esc_html($row->file_description),'speaker'=>esc_html($row->speaker),'pub_date'=>mysql2date(get_option('date_format'),$row->pub_date),'file_url'=>esc_url($url.$row->file_name));
			}
		}
		
		
		header('Access-Control-Max-Age: 1728000');
		header('Access-Control-Allow-Origin: *');
		header('Access-Control-Allow-Methods: *');
		header('Access-Control-Allow-Headers: Content-MD5, X-Alt-Referer');
		header('Access-Control-Allow-Credentials: true');
		echo json_encode($output);
		exit();
}
add_action("wp_ajax_ca_sermons", "ca_sermons");
add_action("wp_ajax_nopriv_ca_sermons", "ca_sermons");
/**
 *
 * Returns one sermon media
 * 
 * @author  Andy Moyle
 * @param    null
 * @return   
 * @version  0.1
 * 
 */ 
function ca_sermon()
{
		global $wpdb;
		
		$url=content_url().'/uploads/sermons/';
		$output=array();
		
		$sql='SELECT * FROM '.CA_FIL_TBL.' WHERE file_id="'.intval($_REQUEST['ID']).'"';
		
		$row=$wpdb->get_row($sql);
		
		if(!empty($row))
		{
			
				$output=array('title'=>esc_html($row->file_title),'id'=>intval($row->file_id),'description'=>esc_html($row->file_description),'speaker'=>esc_html($row->speaker),'pub_date'=>mysql2date(get_option('date_format'),$row->pub_date),'file_url'=>esc_url($url.$row->file_name));
			
		}
		
		
		header('Access-Control-Max-Age: 1728000');
		header('Access-Control-Allow-Origin: *');
		header('Access-Control-Allow-Methods: *');
		header('Access-Control-Allow-Headers: Content-MD5, X-Alt-Referer');
		header('Access-Control-Allow-Credentials: true');
		echo json_encode($output);
		exit();
}
add_action("wp_ajax_ca_sermon", "ca_sermon");
add_action("wp_ajax_nopriv_ca_sermon", "ca_sermon");
/**
 *
 * Returns posts
 * 
 * @author  Andy Moyle
 * @param    null
 * @return   
 * @version  0.1
 * 
 */ 
function ca_prayer_requests()
{
	
	if(!empty($_GET['token']))church_admin_app_last_visited(__('Prayer Request','church-admin'),$_GET['token']);
	
	

	$posts_array = array();

	$args = array("post_type" => "prayer-requests", "orderby" => "date", "order" => "DESC", "post_status" => "publish", "posts_per_page" => "5");

	$posts = new WP_Query($args);
	
	if($posts->have_posts()):
		while($posts->have_posts()): 
			$posts->the_post();
            $content = get_the_content();
			$content = '<div>'.$content.'</div>';
			$content= do_shortcode($content);
            $post_array = array('title'=>get_the_title(),'content'=>$content,'date'=> get_the_date(),'ID'=>get_the_ID());
            array_push($posts_array, $post_array);
			
		endwhile;
		else:
        	echo "{'posts' = []}";
        	die();
	endif;
	
	
	header('Access-Control-Max-Age: 1728000');
	header('Access-Control-Allow-Origin: *');
	header('Access-Control-Allow-Methods: *');
	header('Access-Control-Allow-Headers: Content-MD5, X-Alt-Referer');
	header('Access-Control-Allow-Credentials: true');
	echo json_encode($posts_array);

	die();
}



add_action("wp_ajax_ca_prayer", "ca_prayer_requests");
add_action("wp_ajax_nopriv_ca_prayer", "ca_prayer_requests");
/**
 *
 * Returns posts
 * 
 * @author  Andy Moyle
 * @param    null
 * @return   
 * @version  0.1
 * 
 */ 
function ca_posts()
{
	header('Access-Control-Max-Age: 1728000');
	header('Access-Control-Allow-Origin: *');
	header('Access-Control-Allow-Methods: *');
	header('Access-Control-Allow-Headers: Content-MD5, X-Alt-Referer');
	header('Access-Control-Allow-Credentials: true');
	if(!empty($_GET['token']))church_admin_app_last_visited(__('News','church-admin'),$_GET['token']);

	$posts_array = array();

	$args = array("post_type" => "post", "orderby" => "date", "order" => "DESC", "post_status" => "publish", "posts_per_page" => "10");
	if(!empty($_GET['page']))$args['paged']=intval($_GET['page']);
	$posts = new WP_Query($args);
	
	if($posts->have_posts()):
		while($posts->have_posts()): 
			$posts->the_post();
            $post_array = array(get_the_title(), get_the_permalink(), get_the_date(), wp_get_attachment_url(get_post_thumbnail_id()),get_the_ID());
            array_push($posts_array, $post_array);
			
		endwhile;
		else:
        	echo "{'posts' = []}";
        	die();
	endif;
	
	echo json_encode($posts_array);

	die();
}



add_action("wp_ajax_ca_posts", "ca_posts");
add_action("wp_ajax_nopriv_ca_posts", "ca_posts");
/**
 *
 * Returns one post
 * 
 * @author  Andy Moyle
 * @param    null
 * @return   
 * @version  0.1
 * 
 */ 
function ca_post()
{
	header('Access-Control-Max-Age: 1728000');

	header('Access-Control-Allow-Origin: *');

	header('Access-Control-Allow-Methods: *');

	header('Access-Control-Allow-Headers: Content-MD5, X-Alt-Referer');

	header('Access-Control-Allow-Credentials: true');
	
	
	$post=get_post($_REQUEST['ID']);
	$user = get_userdata($post->post_author);
	$data=array('title'=>$post->post_title,'content'=>nl2br(do_shortcode($post->post_content)),'author'=>$user->first_name.' '.$user->last_name,'date'=>mysql2date(get_option('date_format'),$post->post_date));

	echo json_encode($data);

	die();
}



add_action("wp_ajax_ca_post", "ca_post");
add_action("wp_ajax_nopriv_ca_post", "ca_post");
/**
 *
 * Returns rota
 * 
 * @author  Andy Moyle
 * @param    null
 * @return   
 * @version  0.1
 * 
 */ 
function ca_json_rota()
{
	global $wpdb;
	if(!empty($_GET['token']))church_admin_app_last_visited(__('Rota','church-admin'),$_GET['token']);
	$output=$rota=array();
	//put chosen rota_id as first in json for dropdown
	if(!empty($_REQUEST['rota_id']))
	{
		$sql='SELECT a.rota_date, a.rota_id,b.service_name,b.service_time,c.venue FROM '.CA_ROTA_TBL.' a LEFT JOIN '.CA_SER_TBL.' b ON a.service_id=b.service_id  LEFT JOIN '.CA_SIT_TBL.' c ON b.site_id=c.site_id WHERE a.rota_id="'.intval($_REQUEST['rota_id']).'"';
		$row=$wpdb->get_row($sql);
		if(!empty($row))$rota['services'][]=array('rota_id'=>intval($row->rota_id),'detail'=>mysql2date('jS M',$row->rota_date).' '.mysql2date('G:i',$row->service_time).' '.esc_html($row->venue));
	}
	//grab next 12 meetings
	
	$sql='SELECT a.rota_date, a.rota_id,b.service_name,b.service_time,c.venue FROM '.CA_ROTA_TBL.' a LEFT JOIN '.CA_SER_TBL.' b ON a.service_id=b.service_id  LEFT JOIN '.CA_SIT_TBL.' c ON b.site_id=c.site_id WHERE a.rota_date >= CURDATE( ) GROUP BY a.service_id, a.rota_date ORDER BY rota_date ASC LIMIT 36';
	$results=$wpdb->get_results($sql);
	foreach($results AS $row)
	{
		$rota['services'][]=array('rota_id'=>intval($row->rota_id),'detail'=>mysql2date('jS M',$row->rota_date).' '.mysql2date('G:i',$row->service_time).' '.esc_html($row->venue));
	}
	
	//rota details for requested service
	if(!empty($_REQUEST['rota_id']))
	{
		$rota_id=intval($_REQUEST['rota_id']);
		$sql='SELECT a.*,b.service_name,a.rota_date FROM '.CA_ROTA_TBL.'  a,'.CA_SER_TBL.' b WHERE a.rota_id="'.$rota_id.'" AND a.service_id =b.service_id';
	}
	else
	{
	
		$sql='SELECT a.*,b.service_name,a.rota_date FROM '.CA_ROTA_TBL.'  a,'.CA_SER_TBL.' b WHERE a.rota_date>=CURDATE()  AND a.service_id =b.service_id ORDER BY rota_date ASC LIMIT 1';
	}
	$selectedService=$wpdb->get_row($sql);
	
	//workout which rota jobs are required
	$rota_tasks=$wpdb->get_results('SELECT * FROM '.CA_RST_TBL.' ORDER BY rota_order');
	$requiredRotaJobs=$rotaDates=array();
	foreach($rota_tasks AS $rota_task)
	{
		$allServiceID=maybe_unserialize($rota_task->service_id);
		if(is_array($allServiceID)&&in_array($selectedService->service_id,$allServiceID))$requiredRotaJobs[$rota_task->rota_id]=$rota_task->rota_task;
	}
	$sql='SELECT * FROM '.CA_ROTA_TBL.' WHERE service_id="'.intval($selectedService->service_id).'" AND mtg_type="service" AND rota_date>='.$selectedService->rota_date;
	$rotaDatesResults=$wpdb->get_results($sql);	
		
	foreach($requiredRotaJobs AS $rota_task_id=>$value)
	{
		$people=esc_html(church_admin_rota_people($selectedService->rota_date,$rota_task_id,$selectedService->service_id,'service'));
		if(!empty($people))$rota['tasks'][]=array('job'=>esc_html($value),'people'=>$people);
		
	}	
		
		
		

	
	$output=$rota;
	header('Access-Control-Max-Age: 1728000');
	header('Access-Control-Allow-Origin: *');
	header('Access-Control-Allow-Methods: *');
	header('Access-Control-Allow-Headers: Content-MD5, X-Alt-Referer');
	header('Access-Control-Allow-Credentials: true');
	echo json_encode($output);
	die();
}



add_action("wp_ajax_ca_rota", "ca_json_rota");
add_action("wp_ajax_nopriv_ca_rota", "ca_json_rota");
/**
 *
 * Returns calendar
 * 
 * @author  Andy Moyle
 * @param    null
 * @return   
 * @version  0.1
 * 
 */ 
function ca_json_cal()
{
	global $wpdb;
	if(!empty($_GET['token']))church_admin_app_last_visited(__('Calendar','church-admin'),$_GET['token']);
	$output=$op=array();
	//dates
	$date=$_REQUEST['date'];
	if(!church_admin_checkdate($date)){$date=NULL;}
	$output['dates']=ca_createweeklist($date);
	
	
	//information for dates
	$now='CURDATE()';
	if(church_admin_checkdate($date))$now='"'.$date.'"';
	$sql='SELECT event_id, title,description,start_date,start_time,end_time,location FROM '.CA_DATE_TBL.' WHERE general_calendar=1 AND start_date BETWEEN '.$now.' AND DATE_ADD('.$now.', INTERVAL 7 DAY) ORDER By start_date ASC';
	
	$results=$wpdb->get_results($sql);
	if(!empty($results))
	{
		foreach($results AS $row)
		{
			$output['cal'][]=array(
							'title'=>$row->title,
							'description'=>$row->description,
							'location'=>esc_html($row->location),
							'start_date'=>mysql2date(get_option('date_format'),$row->start_date),
							'iso_date'=>esc_html($row->start_date),
							'start_time'=>mysql2date('G:i',$row->start_time),
							'end_time'=>mysql2date('G:i',$row->end_time),
							'event_id'=>intval($row->event_id)
							);
		
		}
	
	}else{$output['error']="Church Calendar is not yet set up.";}
	header('Access-Control-Max-Age: 1728000');
	header('Access-Control-Allow-Origin: *');
	header('Access-Control-Allow-Methods: *');
	header('Access-Control-Allow-Headers: Content-MD5, X-Alt-Referer');
	header('Access-Control-Allow-Credentials: true');
	echo json_encode($output);
	die();
}



add_action("wp_ajax_ca_cal", "ca_json_cal");
add_action("wp_ajax_nopriv_ca_cal", "ca_json_cal");

/**
 *
 * Returns week of list
 * 
 * @author  Andy Moyle
 * @param    null
 * @return   
 * @version  0.1
 * 
 */ 
function ca_createweeklist($date) {
	$dates=array();
	if(!empty($date)&&church_admin_checkdate($date))$dates[]=array('mysql'=>$date,'friendly'=>date('D jS M', strtotime($date)));
	// assuming your week starts  sunday

	// set start date
	// function will return the monday of the week this date is in
	// eg the monday of the week containing 1/1/2005
	// was 31/12/2004

	$startdate = ca_sundayofweek(date("j"), date("n"), date("Y"));
	
	// set end date
	// the values below use the current date

	$enddate = ca_sundayofweek(date('j',strtotime('+12 weeks')),date('n',strtotime('+12 weeks')),date('Y',strtotime('+12 weeks')));

	// $currentdate loops through each inclusive monday in the date range

	$currentdate = $startdate;

	do {

		$dates[]=array('mysql'=>date("Y-m-d", $currentdate),'friendly'=>date('D jS M', $currentdate));

		$currentdate = strtotime("12pm next Sunday", $currentdate);

	} while ($currentdate <= $enddate);
	return $dates;

}

function ca_sundayofweek($day, $month, $year) {

	// setting the time to noon avoids any daylight savings time issues

	$returndate = mktime(12, 0, 0, $month, $day, $year);

	// if the date isnt a sunday adjust it to the previous sunday

	if (date("w", $returndate) != 0) {

		$returndate = strtotime("12pm last sunday", $returndate);

	}

	return $returndate;

}
/**
 *
 * Login
 * 
 * @author  Andy Moyle
 * @param    null
 * @return   
 * @version  0.1
 * 
 */ 
function ca_login()
{
	global $wpdb;

	$creds = array();
	$creds['user_login'] = $_GET["username"];
	$creds['user_password'] = $_GET["password"];
	$user = wp_signon( $creds, false );
	
	if (empty($user->ID))
	{
		church_admin_debug('Login username/password combo failed');
		church_admin_debug($user->get_error_message());
		$op=array('error'=>'login required');
		
	}else
	{
		$sql='SELECT app_id FROM '.CA_APP_TBL.' WHERE UUID="'.esc_sql(stripslashes($_GET['UUID'])).'"';
		church_admin_debug($sql);
		$check=$wpdb->get_var($sql);
		if($check)
		{
			//update
			$wpdb->query('UPDATE '.CA_APP_TBL.' SET last_login="'.date('Y-m-d h:i:s').'" WHERE UUID="'.esc_sql(stripslashes($_GET['UUID'])).'"');
			
		}
		else
		{	
			//store hashed UUID to use as token along with people_id, user_id
			$sql='INSERT INTO '.CA_APP_TBL.' (UUID,user_id,last_login)VALUES("'.esc_sql(stripslashes($_GET['UUID'])).'","'.$user->ID.'","'.date('Y-m-d h:i:s').'")';
			church_admin_debug($sql);
			$wpdb->query($sql);
		}
		$op=array('login'=>true);
	}
	header('Access-Control-Max-Age: 1728000');
	header('Access-Control-Allow-Origin: *');
	header('Access-Control-Allow-Methods: *');
	header('Access-Control-Allow-Headers: Content-MD5, X-Alt-Referer');
	header('Access-Control-Allow-Credentials: true');
	echo json_encode($op);
	die();
}

add_action("wp_ajax_ca_login", "ca_login");
add_action("wp_ajax_nopriv_ca_login", "ca_login");


function ca_search()
{
	global $wpdb;
	if(!empty($_GET['token']))church_admin_app_last_visited(__('Address List','church-admin'),$_GET['token']);
	$output=array();
	//check token first
	if(empty($_GET['token']))
	{
		$output=array('error'=>'login required');
		church_admin_debug('No token sent');
	}
	else
	{
		$sql='SELECT user_id FROM '.CA_APP_TBL.' WHERE UUID="'.esc_sql(stripslashes($_GET['token'])).'"';
		$result=$wpdb->get_var($sql);
		if(empty($result))
		{
			church_admin_debug('Not logged in');
			$output=array('error'=>'login required');
		}
		else
		{
			$s=esc_sql(stripslashes($_GET['search']));
			$mt=get_option('church_admin_app_member_types');
			if(empty($mt))$mt=array(1);
			foreach($mt AS $key=>$type){$mtsql[]='a.member_type_id='.intval($type);}
			//adjust member_type_id section
			$sql='SELECT a.*,b.address,b.phone FROM '.CA_PEO_TBL.' a LEFT JOIN '.CA_HOU_TBL.' b ON b.household_id=a.household_id WHERE a.household_id=b.household_id AND ('.implode('||',$mtsql).')AND  (CONCAT_WS(" ",a.first_name,a.last_name) LIKE("%'.$s.'%")||CONCAT_WS(" ",a.first_name,a.middle_name,a.last_name) LIKE("%'.$s.'%")||a.nickname LIKE("%'.$s.'%")||a.first_name LIKE("%'.$s.'%")||a.middle_name LIKE("%'.$s.'%")||a.last_name LIKE("%'.$s.'%")||a.email LIKE("%'.$s.'%")||a.mobile LIKE("%'.$s.'%")||b.address LIKE("%'.$s.'%")||b.phone LIKE("%'.$s.'%"))  ORDER BY a.last_name,a.people_order,a.first_name';
    	
    		$results=$wpdb->get_results($sql);
	
			if(!empty($results))
			{
				foreach($results AS $row)
				{
					if(empty($row->phone))$row->phone='';
					if(empty($row->mobile))$row->mobile='';
					$address=implode(', ',$row->address);
$output[]=array('id'=>intval($row->people_id),'first_name'=>esc_html($row->first_name),'last_name'=>esc_html($row->last_name),'name'=>esc_html($row->first_name).' '.esc_html($row->last_name),'email'=>esc_html($row->email),'mobile'=>esc_html($row->mobile),'phone'=>esc_html($row->phone),'address'=>esc_html($row->address),'streetAddress'=>$address[0],'locality'=>$address[1],'region'=>$address[2],'postalCode'=>$address[3]);
				}
			}
			else{$output=array('error'=>'No results');}
		}
		
	}
	header('Access-Control-Max-Age: 1728000');
	header('Access-Control-Allow-Origin: *');
	header('Access-Control-Allow-Methods: *');
	header('Access-Control-Allow-Headers: Content-MD5, X-Alt-Referer');
	header('Access-Control-Allow-Credentials: true');
	echo json_encode($output);
		die();
}
add_action("wp_ajax_ca_search", "ca_search");
add_action("wp_ajax_nopriv_ca_search", "ca_search");


function ca_groups()
{
	global $wpdb;
	if(!empty($_GET['token']))church_admin_app_last_visited(__('Groups','church-admin'),$_GET['token']);
	$sql='SELECT * FROM '.CA_SMG_TBL.' WHERE id!=1';
	$results = $wpdb->get_results($sql);  
	if(!empty($results))
	{
		foreach ($results as $row) 
		{$output[]=array('name'=>esc_html($row->group_name),'whenwhere'=>esc_html($row->whenwhere),'address'=>esc_html($row->address),'lat'=>$row->lat,'lng'=>$row->lng);}
		
	}else 
	{
		$output=array('error'=>__('No small groups yet','church-admin'));
		
	}
	header('Access-Control-Max-Age: 1728000');
	header('Access-Control-Allow-Origin: *');
	header('Access-Control-Allow-Methods: *');
	header('Access-Control-Allow-Headers: Content-MD5, X-Alt-Referer');
	header('Access-Control-Allow-Credentials: true');
	echo json_encode($output);
	die();
}
add_action("wp_ajax_ca_groups", "ca_groups");
add_action("wp_ajax_nopriv_ca_groups", "ca_groups");

function ca_forgotten_password()
{
		$login = trim($_GET['user_login']);
		$user_data = get_user_by('login', $login);
		if(empty($user_data)){$output=array('error'=>'<p>User details not found, please try again</p>');}
		else
		{
			// Redefining user_login ensures we return the right case in the email.
			$user_login = $user_data->user_login;
			$user_email = $user_data->user_email;
			$key = get_password_reset_key( $user_data );
			$message = 'Someone has requested a password reset for the following account at '. "\r\n\r\n";
			$message .= network_home_url( '/' ) . "\r\n\r\n";
			$message .= sprintf(__('Username: %s'), $user_login) . "\r\n\r\n";
			$message .= 'If this was a mistake, just ignore this email and nothing will happen.' . "\r\n\r\n";
			$message .= 'To reset your password, visit the following address:' . "\r\n\r\n";
			$message .= '<' . site_url("wp-login.php?action=rp&key=$key&login=" . rawurlencode($user_login), 'login') . '>'."\r\n";
			/*
			 * The blogname option is escaped with esc_html on the way into the database
			 * in sanitize_option we want to reverse this for the plain text arena of emails.
			 */
			$blogname = wp_specialchars_decode(get_option('blogname'), ENT_QUOTES);
			$title = sprintf( __('[%s] Password Reset'), $blogname );
			$message = apply_filters( 'retrieve_password_message', $message, $key, $user_login, $user_data );
			
			if ( $message && wp_mail( $user_email, wp_specialchars_decode( $title ), $message ) ){	$output=array('message'=>'<p>Password email has been sent to your registered email address</p>');}
			else{$output=array('error'=>'<p>Password reset email failed to send. Please try again.</p>');}
		}
		header('Access-Control-Max-Age: 1728000');
		header('Access-Control-Allow-Origin: *');
		header('Access-Control-Allow-Methods: *');
		header('Access-Control-Allow-Headers: Content-MD5, X-Alt-Referer');
		header('Access-Control-Allow-Credentials: true');
		echo json_encode($output);
		die();
}
add_action("wp_ajax_ca_forgotten_password", "ca_forgotten_password");
add_action("wp_ajax_nopriv_ca_forgotten_password", "ca_forgotten_password");


function ca_my_group()
{

	global $wpdb;

	$output=array();
	//check token first
	if(empty($_GET['token']))
	{
		$output=array('error'=>'login required');
		church_admin_debug('No token sent');
	}
	else
	{
		church_admin_debug('My Group - token present');
		$sql='SELECT user_id FROM '.CA_APP_TBL.' WHERE UUID="'.esc_sql(stripslashes($_GET['token'])).'"';
		
		$userID=$wpdb->get_var($sql);
		if(empty($userID))
		{
			
			$output=array('error'=>'login required');
		}
		else
		{
			//get group ID
			$groupID=$wpdb->get_var('SELECT a.ID FROM '.CA_MET_TBL.' a, '.CA_PEO_TBL.' b WHERE a.meta_type="smallgroup" AND b.user_ID="'.intval($userID).'" and a.people_id=b.people_id');
			
			if(!empty($groupID)&&groupID!=1)
			{
				//person is in a group
				//get group name
				$groupDetails=$wpdb->get_row('SELECT * FROM '.CA_SMG_TBL.' WHERE id="'.intval($groupID).'"');
			
				$output['group_name']=esc_html($groupDetails->group_name);
				$output['when_where']=esc_html($groupDetails->whenwhere.' '.$groupDetails->address);
				//get group members
				$mt=get_option('church_admin_app_member_types');
				if(empty($mt))$mt=array(1);
				foreach($mt AS $key=>$type){$mtsql[]='a.member_type_id='.intval($type);}
				$sql='SELECT a.*,b.address,b.phone FROM '.CA_PEO_TBL.' a, '.CA_HOU_TBL.' b, '.CA_MET_TBL.' c WHERE ('.implode('||',$mtsql).') AND a.household_id=b.household_id AND a.people_id=c.people_id AND c.meta_type="smallgroup" AND c.ID="'.intval($groupID).'"  ORDER BY a.last_name,a.people_order,a.first_name';
    	
    		$results=$wpdb->get_results($sql);
	
			if(!empty($results))
			{
				foreach($results AS $row)
				{
					if(empty($row->phone))$row->phone='';
					if(empty($row->mobile))$row->mobile='';
					$address=implode(', ',$row->address);
$output['people'][]=array('id'=>intval($row->people_id),'first_name'=>esc_html($row->first_name),'last_name'=>esc_html($row->last_name),'name'=>esc_html($row->first_name).' '.esc_html($row->last_name),'email'=>esc_html($row->email),'mobile'=>esc_html($row->mobile),'phone'=>esc_html($row->phone),'address'=>esc_html($row->address),'streetAddress'=>$address[0],'locality'=>$address[1],'region'=>$address[2],'postalCode'=>$address[3]);
				}
			}
			}
			
			else{$output=array('error'=>'No results');}
		}
		
	}
	header('Access-Control-Max-Age: 1728000');
	header('Access-Control-Allow-Origin: *');
	header('Access-Control-Allow-Methods: *');
	header('Access-Control-Allow-Headers: Content-MD5, X-Alt-Referer');
	header('Access-Control-Allow-Credentials: true');
	echo json_encode($output);
		die();
}
add_action("wp_ajax_ca_my_group", "ca_my_group");
add_action("wp_ajax_nopriv_ca_my_group", "ca_my_group");

function ca_which_group()
{
	global $wpdb;
	if(empty($_GET['token']))
	{
		$output=array('error'=>'login required');
		church_admin_debug('No token sent');
	}
	else
	{
		church_admin_debug('My Group - token present');
		$sql='SELECT user_id FROM '.CA_APP_TBL.' WHERE UUID="'.esc_sql(stripslashes($_GET['token'])).'"';
		
		$userID=$wpdb->get_var($sql);
		if(empty($userID))
		{
			
			$output=array('error'=>'login required');
		}
		else
		{
			$peopleID=$wpdb->get_var('SELECT a.people_id FROM '.CA_PEO_TBL.' a,'.CA_APP_TBL.' b WHERE a.user_id=b.user_id AND b.UUID="'.esc_sql(stripslashes($_GET['token'])).'"');
			$groupID=$wpdb->get_var('SELECT a.ID FROM '.CA_MET_TBL.' a, '.CA_PEO_TBL.' b WHERE a.meta_type="smallgroup" AND b.people_ID="'.intval($peopleID).'" and a.people_id=b.people_id');
			$groupName=$wpdb->get_var('SELECT group_name FROM '.CA_SMG_TBL.' WHERE id="'.intval($groupID).'"');
			$output=array('groupID'=>$groupID,'peopleID'=>$peopleID,'groupName'=>$groupName);
		}
	}	
	header('Access-Control-Max-Age: 1728000');
	header('Access-Control-Allow-Origin: *');
	header('Access-Control-Allow-Methods: *');
	header('Access-Control-Allow-Headers: Content-MD5, X-Alt-Referer');
	header('Access-Control-Allow-Credentials: true');
	echo json_encode($output);
		die();
}
add_action("wp_ajax_ca_which_group", "ca_which_group");
add_action("wp_ajax_nopriv_ca_which_group", "ca_which_group");


function ca_bible_readings()
{
	global $wpdb;
	if(!empty($_GET['token']))church_admin_app_last_visited(__('Bible Reading','church-admin'),$_GET['token']);
	//bible readings ID starts at 1 date('z') returns 0 for Jan 1
	$ID=date('z',strtotime('Today'))+1;
	//v1.1.0 of the app sends $_GET['date'] to get date, still need to add 1 though!
	if(!empty($_GET['date'])) $ID=date('z' , strtotime($_GET['date']) )+1;
	
	$sql='SELECT * FROM '.CA_BRP_TBL.' WHERE ID="'.$ID.'"';
	church_admin_debug($sql);
	$data=$wpdb->get_row($sql);
	
	//first time access
	if(empty($data->passages))
	{
		$passages=array();
		$readings=maybe_unserialize($data->readings);
		foreach($readings AS $key=>$value)
		{
			
  			$passage = urlencode($value);
  			$version=get_option('church_admin_bible_version');
  			if(empty($version))
  			{
  				$version='ESV';
  				update_option('church_admin_bible_version',$version);
  			}
  			switch($version)
  			{
  				case'ESV':
  					$options = "include-passage-references=false&include-footnotes=false";
  					$url = "http://www.esvapi.org/v2/rest/passageQuery?key=IP&passage=$passage&$options";
  					$ch = curl_init($url); 
  					curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 
  					$response = curl_exec($ch);
  					curl_close($ch);
  					$passages[$key]='<h2 data-target="'.$key.'" class="passage-toggle">'.$value.'</h2><div class="bible-text" id="passage'.$key.'">'.$response.'</div>';
  			
  				break;
  				case'KJV':
  					$url='https://bible-api.com/'.$passage.'?translation=kjv';
  					$ch = curl_init($url); 
  					curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 
  					$response = json_decode(curl_exec($ch),true);
  					
  					curl_close($ch);
  					$oldChapter='';
  					$out='<p>';
  					foreach($response['verses']AS $verses)
  					{
  						$chapter=$verses['chapter'];
  						//only outpt chapter number on new chapter
  			 			if($chapter!=$oldChapter){$out.='<span style="font-size:larger">'.$verses['chapter'].':'.$verses['verse'].'</span> ';}
  			 			else{$out.='<span style="font-size:smaller">'.$verses['verse'].'</span> ';}
  			 			//output scripture text
  			 			$out.=$verses['text'].'<br/>';
  			 			$oldChapter=$chapter;
  					}
  					$out.='</p>';
  					$passages[$key]='<h2 data-target="'.$key.'" class="passage-toggle">'.$value.'</h2><div class="bible-text" id="passage'.$key.'">'.$out.'</div>';
  				break;
  			}
  			
		}
		$sqlpassages=esc_sql(maybe_serialize($passages));
		
		$sql='UPDATE '.CA_BRP_TBL.' SET passages="'.$sqlpassages.'" WHERE ID="'.intval($ID).'"';
		
		$wpdb->query($sql);	
		
		
		$output=$passages;	
	}
	else $output=maybe_unserialize($data->passages);
	
	header('Access-Control-Max-Age: 1728000');
	header('Access-Control-Allow-Origin: *');
	header('Access-Control-Allow-Methods: *');
	header('Access-Control-Allow-Headers: Content-MD5, X-Alt-Referer');
	header('Access-Control-Allow-Credentials: true');
	echo json_encode($output);
	die();
	
}
add_action("wp_ajax_ca_bible_readings", "ca_bible_readings");
add_action("wp_ajax_nopriv_ca_bible_readings", "ca_bible_readings");


function ca_app_my_rota()
{


	global $wpdb;
	if(empty($_GET['token']))
	{
		$output=array('error'=>'login required');
		church_admin_debug('No token sent');
	}
	else
	{
		$people=$wpdb->get_row('SELECT CONCAT_WS(" ",a.first_name,a.last_name) AS name, a.people_id FROM '.CA_PEO_TBL.' a, '.CA_APP_TBL.' b WHERE a.user_id=b.user_id AND b.UUID="'.esc_sql(stripslashes($_GET['token'])).'"');
		
		if(empty($people->people_id))
		{
			$output=array('error'=>"Your user identity is not connected to a church user profile.");
			
		}
		else
		{
			
			$sql='SELECT a.service_name,a.service_time, b.rota_task,c.rota_date,a.service_id FROM '.CA_SER_TBL.' a, '.CA_RST_TBL.' b, '.CA_ROTA_TBL.' c WHERE a.service_id=c.service_id AND c.mtg_type="service" AND c.rota_task_id=b.rota_id  AND c.people_id="'.intval($people->people_id).'" AND c.rota_date>=CURDATE() ORDER BY c.rota_date ASC';
			church_admin_debug($sql);
			$results=$wpdb->get_results($sql);
			if(!empty($results))
			{
				$task=$output=array();
				foreach($results AS  $row)
				{
					
					$service=esc_html($row->service_name.' '.$row->service_time);
					$date=mysql2date(get_option('date_format'),$row->rota_date);
					$task[$row->rota_date][]=array('date'=>$date,'job'=>esc_html($row->rota_task).' - '.esc_html($row->service_name.' '.$row->service_time));
				}
				foreach($task AS $date=>$values)$output[]=$values;
			}
		
		}
	}
	
	

	header('Access-Control-Max-Age: 1728000');
	header('Access-Control-Allow-Origin: *');
	header('Access-Control-Allow-Methods: *');
	header('Access-Control-Allow-Headers: Content-MD5, X-Alt-Referer');
	header('Access-Control-Allow-Credentials: true');
	echo json_encode($output);
	die();

}
add_action("wp_ajax_ca_my_rota", "ca_app_my_rota");
add_action("wp_ajax_nopriv_ca_my_rota", "ca_app_my_rota");

function ca_home()
{
	$home=get_option('church_admin_app_home');
	$giving=get_option('church_admin_app_giving');
	$groups=get_option('church_admin_app_groups');
	$logo=get_option('church_admin_app_logo');
	$church_id=get_option('church_admin_app_id');
	$output=array('home'=>$home,'giving'=>$giving,'groups'=>$groups,'logo'=>$logo,'church_id'=>$church_id);
	header('Access-Control-Max-Age: 1728000');
	header('Access-Control-Allow-Origin: *');
	header('Access-Control-Allow-Methods: *');
	header('Access-Control-Allow-Headers: Content-MD5, X-Alt-Referer');
	header('Access-Control-Allow-Credentials: true');
	echo json_encode($output);
	die();
	
}
add_action("wp_ajax_ca_home", "ca_home");
add_action("wp_ajax_nopriv_ca_home", "ca_home");
?>