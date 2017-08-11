<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly


function church_admin_front_admin()
{
	
	global $church_admin_version,$wpdb, $current_user;
	$modules=get_option('church_admin_modules');
	$user_id = $current_user->ID;
	//check if address list populated
	$check=$wpdb->get_var('SELECT COUNT(*) FROM '.CA_HOU_TBL);
	if(empty($check)&&empty($_GET['action']))
	{//first run situation...
		echo'<h2>Church Admin Plugin v'.$church_admin_version.'</h2>';
		echo '<p>'.__('Welcome to the church admin plugin. The first job is to get some people into the directory...','church-admin').'</p>';
		echo'<p><a class="button-primary" href="'.wp_nonce_url('admin.php?page=church_admin/index.php&amp;action=csv-import&amp;tab=people','csv_import').'">'.__('Import Address List CSV','church-admin').'</a></p>';
		
	}//end of first run situation
	else
	{	//normal
		
	
	?>
	
     <table class="form-table"><tbody><tr><th scope="row"><h1><span class="dashicons dashicons-admin-home" ></span>Church Admin Plugin v<?php echo $church_admin_version;?></h1></th><td><a style="text-decoration:none" href="http://www.churchadminplugin.com/feed"><span class="dashicons dashicons-rss"></span></a></td><td><form  action="https://www.paypal.com/cgi-bin/webscr" method="post"><input type="hidden" name="cmd" value="_s-xclick"><input type="hidden" name="hosted_button_id" value="R7YWSEHFXEU52"><input type="image"  src="https://www.paypal.com/en_GB/i/btn/btn_donate_LG.gif" class="alignright" name="submit" alt="PayPal - The safer, easier way to pay online."><img alt=""  border="0" src="https://www.paypal.com/en_GB/i/scr/pixel.gif" width="1" height="1"></form></td><td><a class="button-secondary" href="http://www.churchadminplugin.com"><?php echo __('Support','church-admin');?></a></td><td><a class="button-secondary" href="<?php echo wp_nonce_url('admin.php?page=church_admin/index.php&amp;tab=people&action=refresh_backup','refresh_backup');?>"><?php echo __('Refresh DB Backup','church-admin');?> </a></td>
	 
	
	 <?php
	 	//backup
		$filename=get_option('church_admin_backup_filename');
		$upload_dir = wp_upload_dir();
		$path=$upload_dir['basedir'];
		if(!empty($filename))$loc=$path.'/church-admin-cache/'.$filename;
	 	if(!empty($loc) && file_exists($loc))
    	{
			echo'<td><a class="button-secondary"  target="_blank" href="'.$upload_dir['baseurl'].'/church-admin-cache/'.$filename.'">'.__('Download DB Backup','church-admin').'</a></td>';
			echo'<td><a class="button-secondary" href="'.wp_nonce_url('admin.php?page=church_admin/index.php&amp;tab=people&action=delete_backup','delete_backup').'">'.__('Delete DB Backup','church-admin').'</a></td>';
		}
		echo'<td><a class="button-secondary" target="_blank" href="'.wp_nonce_url('admin.php?page=church_admin/index.php&amp;tab=people&action=auto_backup','auto_backup').'">'.__('Auto DB Backup','church-admin').'</a></td>';
	 	echo'<td><a class="button-secondary" href="'.wp_nonce_url('admin.php?page=church_admin/index.php&action=shortcodes','shortcode').'">'.__('Shortcodes','church-admin').'</a></td>';
	 
	 ?>
	 <td>
			<div id="mc_embed_signup">
				<form action="//thegatewaychurch.us2.list-manage.com/subscribe/post?u=de873ad10bb6b43b54744b951&amp;id=848214cef0" method="post" id="mc-embedded-subscribe-form" name="mc-embedded-subscribe-form" class="validate" target="_blank" novalidate>
    			<div id="mc_embed_signup_scroll">
				<input type="email" value="" name="EMAIL" class="email" id="mce-EMAIL" placeholder="<?php echo __('Email address','church-admin');?>" required>
    			<!-- real people should not fill this in and expect good things - do not remove this or risk form bot signups-->
    			<div style="position: absolute; left: -5000px;" aria-hidden="true"><input type="text" name="b_de873ad10bb6b43b54744b951_848214cef0" tabindex="-1" value=""></div>
    			<input type="submit" value="<?php echo __('News sign up','church-admin');?>" name="subscribe" id="mc-embedded-subscribe" class="button-primary">
    			</div>
				</form>
			</div>
	</td>	
	</tr></tbody></table>

    <h2 class="nav-tab-wrapper">
			<?php 
			if(!empty($modules['People'])&& church_admin_level_check('Directory')) 
			{
				echo'<a href="admin.php?page=church_admin/index.php&amp;action=people&amp;tab=people" class="nav-tab ';
				if(isset( $_GET['tab'])&& $_GET['tab'] == 'people' )echo 'nav-tab-active' ; 
				echo'"><span class="dashicons dashicons-admin-users"></span>'. __('People','church-admin').'</a>';
			}
			if(!empty($modules['Children'])&& church_admin_level_check('Directory'))
			{
				echo'<a href="admin.php?page=church_admin/index.php&amp;action=children&amp;tab=children" class="nav-tab ';
				if(isset( $_GET['tab'])&& $_GET['tab'] == 'children' )echo 'nav-tab-active' ; 
				echo' "><span class="dashicons dashicons-admin-users"></span>'.__('Children','church-admin').'</a>';
			}
			if(!empty($modules['Classes'])&& church_admin_level_check('Directory'))
			{
				echo'<a href="admin.php?page=church_admin/index.php&amp;action=classes&amp;tab=classes" class="nav-tab ';
				if(isset( $_GET['tab'])&& $_GET['tab'] == 'classes' )echo 'nav-tab-active' ; 
				echo' "><span class="dashicons dashicons-admin-users"></span>'.__('Classes','church-admin').'</a>';
			}
			if(!empty($modules['Groups'])&& church_admin_level_check('Small Groups'))
			{
				echo'<a href="admin.php?page=church_admin/index.php&amp;action=small_groups&amp;tab=small_groups" class="nav-tab ';
				if(isset( $_GET['tab'])&& $_GET['tab'] == 'small_groups' )echo 'nav-tab-active' ; 
				echo'"><span class="dashicons dashicons-nametag"></span>'.__('Groups','church-admin').'</a>';
			}
			if(!empty($modules['Services'])&& church_admin_level_check('Small Groups'))
			{
				echo'<a href="admin.php?page=church_admin/index.php&amp;action=services&amp;tab=services" class="nav-tab ';
				if(isset( $_GET['tab'])&& $_GET['tab'] == 'services' )echo 'nav-tab-active' ; 
				echo'"><span class="dashicons dashicons-nametag"></span>'.__('Services','church-admin').'</a>';
			}
			if(!empty($modules['Sessions']) && church_admin_level_check('Sessions'))
			{
				echo'<a href="admin.php?page=church_admin/index.php&amp;action=sessions&amp;tab=sessions" class="nav-tab ';
				if(isset( $_GET['tab'])&& $_GET['tab'] == 'sessions' )echo 'nav-tab-active' ; 
				echo'"><span class="dashicons dashicons-nametag"></span>'.__('Sessions','church-admin').'</a>';
			}
			if(!empty($modules['Comms'])&& church_admin_level_check('Bulk Email'))
			{
				echo'<a href="admin.php?page=church_admin/index.php&amp;action=communication&amp;tab=communication" class="nav-tab ';
				if(isset($_GET['tab'])&& $_GET['tab'] == 'communication' )echo 'nav-tab-active' ; 
				echo' "><span class="dashicons dashicons-megaphone"></span>'.__('Comms','church-admin').'</a>';
			}
			if(!empty($modules['Rota'])&& church_admin_level_check('Rota'))
			{
				echo'<a href="admin.php?page=church_admin/index.php&amp;action=rota&amp;tab=rota" class="nav-tab ';
				if(isset($_GET['tab'])&& $_GET['tab'] == 'rota' )echo 'nav-tab-active' ; 
				echo'"><span class="dashicons dashicons-calendar"></span>'.__('Rota','church-admin').'</a>';
			}
			if(!empty($modules['Calendar'])&& church_admin_level_check('Calendar'))
			{
				echo'<a href="admin.php?page=church_admin/index.php&amp;action=calendar&amp;tab=calendar" class="nav-tab ';
				if(isset($_GET['tab'])&& $_GET['tab'] == 'calendar' )echo 'nav-tab-active' ; 
				echo ' "><span class="dashicons dashicons-calendar-alt"></span>'.__('Calendar','church-admin').'</a>';
			}
			if(!empty($modules['Facilities'])&& church_admin_level_check('Calendar'))
			{
				echo'<a href="admin.php?page=church_admin/index.php&amp;action=facilities&amp;tab=facilities" class="nav-tab ';
				if(isset($_GET['tab'])&& $_GET['tab'] == 'facilities' )echo 'nav-tab-active' ; 
				echo'"><span class="dashicons dashicons-calendar"></span>'.__('Facilities','church-admin').'</a>';
			}
			if(!empty($modules['Ministries'])&& church_admin_level_check('Directory'))
			{
				echo'<a href="admin.php?page=church_admin/index.php&amp;action=ministries&amp;tab=ministries" class="nav-tab ';
				if(isset($_GET['tab'])&& $_GET['tab'] == 'ministries' )echo 'nav-tab-active' ; 
				echo'"><span class="dashicons dashicons-clipboard"></span>'.__('Ministries','church-admin').'</a>';
			}
			if(!empty($modules['Media'])&& church_admin_level_check('Sermons'))
			{
				echo'<a href="admin.php?page=church_admin/index.php&amp;action=podcast&amp;tab=podcast" class="nav-tab ';
				if(isset($_GET['tab'])&& $_GET['tab'] == 'podcast' )echo 'nav-tab-active' ; 
				echo'"><span class="dashicons dashicons-playlist-audio"></span>'.__('Media','church-admin').'</a>';
			}
			if(!empty($modules['App'])&& church_admin_level_check('App'))
			{
				echo'<a href="admin.php?page=church_admin/index.php&amp;action=app&amp;tab=app" class="nav-tab ';
				if(isset($_GET['tab'])&& $_GET['tab'] == 'app' )echo 'nav-tab-active' ; 
				echo'"><span class="dashicons dashicons-smartphone"></span>'.__('App','church-admin').'</a>';
			}
			
				echo'<a href="admin.php?page=church_admin/index.php&amp;action=settings&amp;tab=settings" class="nav-tab ';
				if(isset($_GET['tab'])&& $_GET['tab'] == 'settings' )echo 'nav-tab-active' ; 
				echo'"><span class="dashicons dashicons-admin-tools"></span>'.__('Settings','church-admin').'</a>';
			?>
	</h2>
	
    <?php
    if(!empty($_GET['message']))echo'<div class="notice notice-success inline">'.esc_html($_GET['message']).'</div>'; 

}//end normal

} //end church_admin_front_admin

 /**
 *
 * Sessions Admin screen
 * 
 * @author  Andy Moyle
 * @param    
 * @return   
 * @version  0.965
 *
 * 
 * 
 */
 function church_admin_sessions_menu()
 {
 	require_once(plugin_dir_path(__FILE__).'/sessions.php');
 	echo church_admin_sessions();
 }
 
 /**
 *
 * Services Admin screen
 * 
 * @author  Andy Moyle
 * @param    
 * @return   
 * @version  0.945
 *
 * 
 * 
 */ 
function church_admin_services_main()
{
	require_once(plugin_dir_path(__FILE__).'/services.php');
	require_once(plugin_dir_path(__FILE__).'/sites.php');
	
	church_admin_site_list();
	church_admin_service_list();
	church_admin_tracking();
}

function church_admin_tracking()
{
	global $wpdb;
	$days=array(1=>__('Sunday','church-admin'),2=>__('Monday','church-admin'),3=>__('Tuesday','church-admin'),4=>__('Wednesday','church-admin'),5=>__('Thursday','church-admin'),6=>__('Friday','church-admin'),7=>__('Saturday','church-admin'));
	
	echo'<h2 class="tracking-toggle">'.__('Attendance Tracking (Click to toggle view)','church-admin').'</h2>';
	echo'<div class="tracking" style="display:block">';
	echo'<p><a class="button-primary" href="'.wp_nonce_url('admin.php?page=church_admin/index.php&amp;action=edit_attendance&amp;tab=services','edit_attendance').'">'.__('Add meeting attendance','church-admin').'</a></p>';
	echo'<p><a href="admin.php?page=church_admin/index.php&amp;action=individual_attendance&amp;tab=services">'.__('Individual Attendance','church-admin').'</a></p>';
   	require_once(plugin_dir_path(__FILE__).'/attendance.php');
	church_admin_attendance_list(1,'service');
   
   //graph display
   echo'<h3>'.__('Attendance Graphs','church-admin').'</h3>';

      if(!empty($_POST['type']))
				{
					switch($_POST['type'])
					{
						case'weekly':$graphtype='weekly';break;
						case'rolling':$graphtype='rolling';break;
						default:$graphtype='weekly';break;
					}
				}else{$graphtype='weekly';}
				if(!empty($_POST['start'])){$start=$_POST['start'];}else{$start=date('Y-m-d',strtotime('-1 year'));}
				if(!empty($_POST['end'])){$end=$_POST['end'];}else{$end=date('Y-m-d');}
				if(!empty($_POST['service_id'])){$service_id=$_POST['service_id'];}else{$service_id='S/1';}
				require_once(CA_PATH.'display/graph.php');
				echo church_admin_graph($graphtype,$service_id,$start,$end,900,500);
	echo'</div>';
	echo'<script type="text/javascript">jQuery(function(){  jQuery(".tracking-toggle").click(function(){jQuery(".tracking").toggle();  });});</script>';
	
}

function church_admin_settings_menu()
{
	global $wpdb;

	require_once(plugin_dir_path(__FILE__).'/settings.php');
$days=array(1=>__('Sunday','church-admin'),2=>__('Monday','church-admin'),3=>__('Tuesday','church-admin'),4=>__('Wednesday','church-admin'),5=>__('Thursday','church-admin'),6=>__('Friday','church-admin'),7=>__('Saturday','church-admin'));
	echo'<form name="ca_search" action="admin.php?page=church_admin/index.php&amp;tab=address" method="POST"><table class="form-table"><tbody><tr><th scope="row">'.__('Search','church-admin').'</th><td><input name="church_admin_search" style="width:100px;" type="text"/><input class="button-primary" type="submit" value="'.__('Go','church-admin').'"/></td></tr></table></form>';
	//errors
	$error=get_option('church_admin_plugin_error');
	if(!empty($error))
	{
		echo'<h2>Installation errors</h2>';
		echo'<p>'.__('This is what was saved as an error during activation ','church-admin').'"'.$error.'"</p>';
		echo'<p><a href="'.wp_nonce_url('admin.php?page=church_admin/index.php&amp;tab=settings&action=church_admin_activation_log_clear','clear_error').'">'.__('Clear activation errors log','church-admin').'</a></p><hr/>';
	}
	echo'<a href="'.wp_nonce_url('admin.php?page=church_admin/index.php&amp;tab=settings&action=reset_version','reset_version').'">'.__('Reset Version (may clear corrupted upgrade)','church-admin').'</a></p>';
	echo'<a href="'.wp_nonce_url('admin.php?page=church_admin/index.php&amp;tab=settings&action=upgrade_rota','upgrade_rota').'"  onclick="return confirm(\'Are you sure?\')">'.__('Upgrade Rota Database Table','church-admin').'</a></p>';
	
	//debug log display
	$upload_dir = wp_upload_dir();
	$debug_path=$upload_dir['basedir'].'/church-admin-cache/debug_log.php';
	$debug_url=content_url().'/uploads/church-admin-cache/debug_log.php';
	
	if(file_exists($debug_path))
	{
		echo'<a href="'.$debug_url.'">'.__('Debug Log','church-admin').'</a></p>';
		echo'<a href="'.wp_nonce_url('admin.php?page=church_admin/index.php&amp;tab=settings&action=clear_debug','clear_debug').'">'.__('Clear Debug Log','church-admin').'</a></p>';
	}
	//modules
	church_admin_modules();
	//People Types
	echo church_admin_people_types_list();
	
	//permissions
	church_admin_roles();
	//smtp settings
	church_admin_smtp_settings();
	church_admin_email_settings();
    
	//backup
	church_admin_backup_menu();
	//SMS settings
	church_admin_sms_settings();
	//General Settings
	church_admin_general_settings();
	

	
}
function church_admin_shortcodes_list()
{
	global $wpdb;
	//shortcodes
	echo'<h2>'.__('Shortcodes','church-admin').'</h2>';
	echo '<h3>'.__('Communications','church-admin').'</h3>';
    echo'<p><strong>[church_admin_unsubscribe]</strong>'.__(' means an unsubscribe link is added to emails and users can unsubscribe, without being deleted from the directory','church-admin').'</p>';
    //calendar
    echo'<h3>'.__('Calendar','church-admin').'</h3>';
    echo '<p>'.__('[church_admin type=calendar] shows table format calendar','church-admin').'</p>';
    echo '<p>'.__('[church_admin type=calendar-list category=1,2,3 weeks=4] shows calendar events from categories 1,2 and 3 for the next 4 weeks','church-admin').'</p>';
    $results=$wpdb->get_results("SELECT * FROM ".$wpdb->prefix."church_admin_calendar_category");
    if($results)
    {
    	echo'<table class="widefat striped"><thead><tr><th>'.__('Shortcode','church-admin').'</th><th>Category</th></tr></thead><tbody>';
        foreach($results AS $row)
        {
             $shortcode='<strong>[church_admin type=calendar-list category='.esc_html($row->cat_id).' weeks=4]</strong>';
            echo'<tr><th scope="row">'.$shortcode.'</th><td>'.sprintf(__('Calendar List by Category %1$s','church-admin'),esc_html($row->category)).'</td></tr>';
        }
        echo'</tbody></table>';
    }
	//directory
	
    echo'<h3>'.__('Directory','church-admin').'</h3>';
    echo'<p>'.__('The directory shortcode is [church_admin type=address-list member_type_id=# photo=1 map=1 site_id=0]','church-admin').'</strong></p>';
    echo'<p>'.__('photo=1 will display a thumbnail if one has been uploaded','church-admin').'</p>';
    echo'<p>'.__('site=0 will display people from all sites, or if comma separated numbers used individual sites.','church-admin').'</p>';
	echo'<p>'.__('map=1 shows a map for households where you have updated location on the google map when editing.','church-admin').' </p>';
    echo'<p>'.__('Member type can include more than one member type separated with commas e.g.:','church-admin').'<strong>[church_admin type=address-list member_type_id=1,2 map=1 photo=1]</strong></p>';
    echo'<p>'.__('kids=0 will stop children being shown','church-admin').'.</p>';
    echo'<p>'.__('loggedin=TRUE makes the page available to logged in users only','church-admin').'</p>';
    echo'<p>'.__("updateable=FALSE disables the edit link on each entry for admins and logged in user's entry",'church-admin').'</p>';
    $results=$wpdb->get_results('SELECT * FROM '.CA_MTY_TBL.' ORDER BY member_type_id');
    if($results)
    {
        echo '<p>'.__('These are your current member types','church-admin').'</p>';
        foreach($results AS $row)
        {
            echo'<p><label>'.esc_html($row->member_type).': </label>member_type_id='.intval($row->member_type_id).'</p>';
        }
    }
    echo'<h3>'.__('Names','church-admin').'</h3>';
    echo'<p>'.__('[church_admin type=names member_type_id=# people_types=#] displays just names','church-admin').'</p>';
     echo'<p>'.__('people_types can be "all","adults","teens","children" or a combination separated by a comma','church-admin').'</p>';
    echo'<h3>'.__('Media','church-admin').'</h3>';
    echo'<p><strong>[church_admin type=podcast] </strong>'.__('Lists all sermons','church-admin').'</p>';
    $results=$wpdb->get_results('SELECT * FROM '.CA_SERM_TBL);
    if($results)
    {//results
    	echo'<table class="widefat striped">';
    	echo'<thead><tr><th>'.__('Series Name','church-admin').'</th><th>'.__('Number of sermons','church-admin').'</th><th>'.__('Shortcode'.'church-admin').'</th></tr></thead><tfoot><tr><th>'.__('Series Name','church-admin').'</th><th>'.__('Number of sermons','church-admin').'</th><th>'.__('Shortcode'.'church-admin').'</th></tr></tfoot><tbody>';
    	foreach ($results AS $row)
    	{	
    		$files=$wpdb->get_var('SELECT count(*) FROM '.CA_FIL_TBL.' WHERE series_id="'.esc_sql($row->series_id).'"');
            if(!$files)$files="0";
    		echo'<tr><td>'.esc_html($row->series_name).'</td><td>'.intval($files).'</td><td>[church_admin type="podcast" series_id="'.intval($row->series_id).'"]</td></tr>';
    	}
    	echo'</tbody></table>';
    }
    //member map
    echo'<h3>'.__('Member Map','church-admin').'</h3>';
    echo'<p><strong>'.__('[church_admin_map member_type_id=# zoom=13 small_group=1]</strong> - zoom is Google map zoom level, small_group=1 for different colours for small groups, 0 for all in red','church-admin').'</p>';
    //ministries
    echo'<h3>'.__('Ministries','church-admin').'</h3>';
    echo'<p><strong>[church_admin type=ministries member_type_id=#] </strong>'.__('Lists people doing various ministries - just specify ministry_ids','church-admin').'</p>';
    $min=get_option('church_admin_ministries');
    if(!empty($min)){
    	foreach($min AS $id=>$ministry) echo'<p>'.sprintf( esc_html__( '"%1$s" has id %2$s.', 'church-admin' ), $ministry, intval($id) ).'</p>';
    								
    }
    //recent
	echo'<h3>'.__('Recent Visitors','church-admin').'</h3>';
	echo'<p><strong>[church_admin type=recent member_type_id=#] </strong>'.__('Lists your recent visitors - just specify member_types_ids','church-admin').'</p>';
    //small groups
    echo'<h3>'.__('Small groups','church-admin').'</h3>';
    echo'<p><strong>'.__("[church_admin type=small-groups-list map=1]</strong> lists all your small group's details in map form (map=1)or as a list (map=0)",'church-admin').'</p>';
    echo'<p><strong>'.__("[church_admin type=small-groups member_type_id=# ]</strong> lists all your small groups and their members for a specific member type",'church-admin').'</p>';
    echo'<p>'.__('For the small-groups shortcode you can add loggedin=TRUE and restricted=TRUE to only show groups the user is in or leading','church-admin').'</p>';
    
    //rotas
    echo'<h3>'.__('Rotas','church-admin').'</h3>';
    echo'<p><strong>'.__("[church_admin type=my_rota]</strong> shows a logged in user their rota involvement.",'church-admin').'</p>';
    echo'<p><strong>'.__("[church_admin type=rota service_id=1]</strong> lists the upcoming rota for a particular service",'church-admin').'</p>';
   				$days=array(1=>__('Sunday','church-admin'),2=>__('Monday','church-admin'),3=>__('Tuesday','church-admin'),4=>__('Wednesday','church-admin'),5=>__('Thursday','church-admin'),6=>__('Friday','church-admin'),7=>__('Saturday','church-admin'));
    $results=$wpdb->get_results('SELECT * FROM '.CA_SER_TBL.' ORDER BY service_id');
    if($results)
    {
        echo '<p>'.__('These are your current services','church-admin').'</p>';
        foreach($results AS $row)
        {
            echo'<p><label>'.esc_html($row->service_name).' on '.esc_html($days[$row->service_day]).' at '.esc_html($row->service_time).' </label>service_id='.intval($row->service_id).'</p>';
        }
    }

    //user registration
    echo'<h3>'.__('User Registration','church-admin').'</h3>';
    echo'<p><strong>[church_admin_register]</strong></p>';
    echo'<p>'.__('This shortcode allows new people to register and logged in users to update their own entry.','church-admin').'</p>';
    
    //recent activity
    echo'<h3>'.__('Recent Directory Activity','church-admin').'</h3>';
    echo'<p><strong>[church_admin_recent]</strong></p>';
    


	//Attendance
	 echo'<h3>'.__('Attendance','church-admin').'</h3>';
    echo'<p><strong>[church_admin type="graph" width=900 height=500]</strong> - '.__('Displays graph image 900x500px;','church-admin').'</p>';
    //Birthdays
	echo'<h3>'.__('Birthdays','church-admin').'</h3>';
	echo'<p><strong>'.__('[church_admin type="birthdays" member_type_id=# days=#]</strong> - Displays upcoming birthdays for the next # days for member_types_ids #','church-admin').'</p>';
	
	
}
function church_admin_podcast()
{
	require_once(plugin_dir_path(__FILE__).'/sermon-podcast.php');
	echo'<form name="ca_search" action="admin.php?page=church_admin/index.php&amp;tab=address" method="POST"><table class="form-table"><tbody><tr><th scope="row">'.__('Search','church-admin').'</th><td><input name="church_admin_search" style="width:100px;" type="text"/><input class="button-primary" type="submit" value="'.__('Go','church-admin').'"/></td></tr></table></form>';
	echo'<h2>Podcast</h2>';
	//echo '<table ><tr><td>The maximum file you can upload from the browser is '.church_admin_max_file_upload_in_bytes().'MB.<br/> Upload bigger files easily using the Church Admin Media Windows Upload Utility</td><td><form action="https://www.paypal.com/cgi-bin/webscr" method="post" target="_top"><input type="hidden" name="cmd" value="_s-xclick"><input type="hidden" name="hosted_button_id" value="7FYG7STCC5W5J"><input type="submit" value="Buy on Paypal for &pound;5GBP" class="button-primary"></form></td></tr></table>';
	echo '<p><a class="button-primary" href="'.wp_nonce_url('admin.php?page=church_admin/index.php&amp;action=edit_file&amp;tab=podcast','edit_podcast_file').'">'.__('Upload or add external mp3 File','church-admin').'</a></p>';
    echo '<p><a class="button-secondary" href="'.wp_nonce_url('admin.php?page=church_admin/index.php&amp;action=check_files&amp;tab=podcast','check_podcast_file').'">'.__('Add Already Uploaded Files','church-admin').'</a></p>';
	echo'<p><a class="button-secondary"  href="'.wp_nonce_url("admin.php?page=church_admin/index.php&amp;action=list_sermon_series&amp;tab=podcast",'list_sermon_series').'">'.__('List Sermon Series','church-admin').'</a></p>';
    echo'<p><a class="button-secondary"  href="'.wp_nonce_url("admin.php?page=church_admin/index.php&amp;action=podcast_settings&amp;tab=podcast",'podcast_settings').'">'.__('iTunes Compatible RSS Settings','church-admin').'</a></p>';
	ca_podcast_list_files();
	
}
function church_admin_children()
{
	echo'<form name="ca_search" action="admin.php?page=church_admin/index.php&amp;tab=address" method="POST"><table class="form-table"><tbody><tr><th scope="row">'.__('Search','church-admin').'</th><td><input name="church_admin_search" style="width:100px;" type="text"/><input class="button-primary" type="submit" value="'.__('Go','church-admin').'"/></td></tr></table></form>';
	
	//kidswork
	
	require_once(plugin_dir_path(__FILE__).'/kidswork.php');
	church_admin_kidswork();
	church_admin_safeguarding_main();
}
function church_admin_ministries_menu()
{

	//ministries
	echo'<form name="ca_search" action="admin.php?page=church_admin/index.php&amp;tab=address" method="POST"><table class="form-table"><tbody><tr><th scope="row">'.__('Search','church-admin').'</th><td><input name="church_admin_search" style="width:100px;" type="text"/><input class="button-primary" type="submit" value="'.__('Go','church-admin').'"/></td></tr></table></form>';
	echo'<h2>'.__('Ministries','church-admin').'</h2>';
	require_once(plugin_dir_path(__FILE__).'/departments.php');
	church_admin_ministries_list();
	
	//hope team
	echo'<hr/><h2>'.__('Hope Team','church-admin').'</h2>';
	echo'<p><a  class="button-primary" href="'.wp_nonce_url('admin.php?page=church_admin/index.php&amp;tab=ministries&action=edit_hope_team_job','hope_team_jobs').'">'.__('Add a hope team job','church-admin').'</a> <a class="button-secondary" href="'.wp_nonce_url('admin.php?page=church_admin/index.php&amp;tab=ministries&action=edit_hope_team','edit_hope_team').'">'.__('Edit who is in Hope Team','church-admin').'</a></p>';
	require_once(plugin_dir_path(__FILE__).'/hope-team.php');
	church_admin_hope_team_jobs();
	
	echo'<p><a href="'.home_url().'/?download=hope_team_pdf">'.__('Hope Team PDF','church-admin').'</a></p>';

	
}

/**
 *
 * displays rota menu
 * 
 * @author  Andy Moyle
 * @param    $service_id
 * @return   html string
 * @version  0.1
 *
 * refactored 11th April 2016 to remove multi-service bug
 * 
 */
function church_admin_rota_main($service_id=1)
{
	global $days,$wpdb;
			$days=array(1=>__('Sunday','church-admin'),2=>__('Monday','church-admin'),3=>__('Tuesday','church-admin'),4=>__('Wednesday','church-admin'),5=>__('Thursday','church-admin'),6=>__('Friday','church-admin'),7=>__('Saturday','church-admin'));
	echo'<form name="ca_search" action="admin.php?page=church_admin/index.php&amp;tab=address" method="POST"><table class="form-table"><tbody><tr><th scope="row">'.__('Search','church-admin').'</th><td><input name="church_admin_search" style="width:100px;" type="text"/><input class="button-primary" type="submit" value="'.__('Go','church-admin').'"/></td></tr></table></form>';
	
	echo'<h2><a class="task-toggle">'.__('Set up tasks for the rota (Click to toggle)','church-admin').'</a></h2>';	
	echo '<div class="tasks"  style="display:none"><p><a href="'.wp_nonce_url("admin.php?page=church_admin/index.php&amp;tab=rota&amp;action=church_admin_rota_settings_list&amp;tab=rota","rota_settings_list").'">'.__('View/Edit Rota Jobs','church-admin').'</a></p>';
	echo'<p><a href="'.wp_nonce_url("admin.php?page=church_admin/index.php&amp;tab=rota&amp;action=church_admin_edit_rota_settings&amp;tab=rota",'edit_rota_settings').'" >'.__('Add more rota jobs','church-admin').'</a></p>';
	$services=$wpdb->get_results('SELECT * FROM '.CA_SER_TBL);
	if($wpdb->num_rows==1)
	{
		echo'<p><a href="'.wp_nonce_url("admin.php?page=church_admin/index.php&amp;tab=rota&amp;action=church_admin_add_dates&amp;tab=rota&service_id=".$services[0]->service_id,'edit_rota').'">'.__('Add 3 months of dates to rota','church-admin').'</a></p>';
	}
	else
	{
		echo'<form action="" method="GET">';
		echo'<p><label>'. __("Add 3 months of dates to rota",'church-admin').'</label>';
		echo'<input type="hidden" name="action" value="church_admin_add_dates"/><input type="hidden" name="tab" value="rota"/><input type="hidden" name="page" value="church_admin/index.php"/>';
		wp_nonce_field('edit_rota');
		echo'<select name="service_id">';
		foreach($services AS $service) echo'<option value="'.intval($service->service_id).'">'.esc_html($service->service_name.' on '.$days[$service->service_day].' at '.$service->service_time).'</option>';
		echo'</select><input  class="button-primary" type=submit value="'.__('Add','church-admin').'"/></p></form>';
	}
	echo'</div>';
	echo'<script type="text/javascript">jQuery(function(){  jQuery(".task-toggle").click(function(){jQuery(".tasks").toggle();  });});</script>';
	echo'<hr/><h2><a class="rotacomms-toggle">'.__('Communicate the rota (Click to toggle)','church-admin').'</a></h2>';
	echo'<div class="rotacomms"  style="display:none">';
	$rota_days=array(1=>__('Monday','church-admin'),2=>__('Tuesday','church-admin'),3=>__('Wednesday','church-admin'),4=>__('Thursday','church-admin'),5=>__('Friday','church-admin'),6=>__('Saturday','church-admin'),7=>__('Sunday','church-admin'));
	$en_rota_days=array(1=>'Monday',2=>'Tuesday',3=>'Wednesday',4=>'Thursday',5=>'Friday',6=>'Saturday',7=>'Sunday');
	
	$email_day=get_option('church_admin_email_rota_day');
	
	if(!empty($email_day)&&!empty($rota_days[$email_day])) echo'<p><strong>'.sprintf(__('This week\'s rotas are automatically emailed on %1$s, when your website is first accessed that day.','church-admin'),$rota_days[$email_day]).'</strong></p>';
	echo'<form action="" method="POST">';
	echo'<table ><tr><th scope="row">'.__('Which Service?','church-admin').'</th><td><select name="service_id">';
		foreach($services AS $service) echo'<option value="'.intval($service->service_id).'">'.esc_html($service->service_name.' on '.$days[$service->service_day].' at '.$service->service_time).'</option>';
		echo'</select>';
	echo'</td><td>'. __("Automatically email current week's rota",'church-admin').'</td><td>';
	echo'<select name="email_rota_day">';
	echo'<option value="8"'.selected( $email_day, NULL ).'>'.__('No Auto Send','church-admin').'</option>';
	echo'<option value="1"'.selected( $email_day, 1 ).'>'.__('Monday','church-admin').'</option>';
	echo'<option value="2"'.selected( $email_day, 2 ).'>'.__('Tuesday','church-admin').'</option>';
	echo'<option value="3"'.selected( $email_day, 3 ).'>'.__('Wednesday','church-admin').'</option>';
	echo'<option value="4"'.selected( $email_day, 4 ).'>'.__('Thursday','church-admin').'</option>';
	echo'<option value="5"'.selected( $email_day, 5 ).'>'.__('Friday','church-admin').'</option>';
	echo'<option value="6"'.selected( $email_day, 6 ).'>'.__('Saturday','church-admin').'</option>';
	echo'<option value="7"'.selected( $email_day, 7 ).'>'.__('Sunday','church-admin').'</option>';
	echo'</select><td></tr>';
	$message='';
	$message=get_option('church_admin_auto_rota_email_message');
	echo '<tr><th scope="row">'.__('Email message','church-admin').'</th><td colspacing=2><textarea name="auto-rota-message" class="large-text">'.$message.'</textarea></td></tr>';
	echo'<tr><td cellpsacing=2><input   class="button-primary" type="submit" value="Save"/></td></tr></table></form>';
	
    echo'<form action="'.admin_url().'" method="GET"><input type="hidden" name="page" value="church_admin/index.php"/><input type="hidden" name="action" value="email_rota"/><input type="hidden" name="tab" value="rota">';
	$services=$wpdb->get_results('SELECT * FROM '.CA_SER_TBL);
	
    echo'<table ><tr><th scope="row">'.__('Email out service rota','church-admin').'</th><td><select id="services" name="service_id">';
    echo'<option value="">'.__('Choose a service','church-admin').'...</option>';
    foreach($services AS $service)
    {
       echo'<option value="'.$service->service_id.'">'.sprintf( __('%1$s on %2$s at %3$s ', 'church-admin'), $service->service_name,$days[$service->service_day],$service->service_time).'</option>';
    }
    
   
    echo'</select></td><td><span id="dates">'.__('Choose services, then choice of dates will appear','church-admin').'</span></td><td><input  class="button-primary"  type="submit" name="submit" value="'.__('Send service rota','church-admin').'"></td></tr></table>';
    echo'</form>';
    echo'<form action="'.admin_url().'" method="GET"><input type="hidden" name="page" value="church_admin/index.php"/><input type="hidden" name="action" value="sms-rota"/><input type="hidden" name="tab" value="rota">';
	
    echo'<p><label>'.__('SMS out service rota','church-admin').'</label><select name="service_id">';
    echo'<option value="">'.__('Choose a service','church-admin').'...</option>';
    foreach($services AS $service)
    {
       echo'<option value="'.$service->service_id.'">'.sprintf( __('%1$s on %2$s at %3$s ', 'church-admin'), $service->service_name,$days[$service->service_day],$service->service_time).'</option>';
    }
    echo'</select><input  class="button-primary"  type="submit" name="submit" value="'.__('Send service rota','church-admin').'"></p>';
    echo'</form>';
    echo'</div>';
    $nonce = wp_create_nonce("church_admin_rota_dates");
   echo'<script type="text/javascript">jQuery(document).ready(function($) {
			$("#services").change(function() { 
			var service_id=$("#services").val();
			var data = {
			"action": "church_admin_rota_dates",
			"service_id":service_id,
			"nonce": "'.$nonce.'"
		};

		// since 2.8 ajaxurl is always defined in the admin header and points to admin-ajax.php
		jQuery.post(ajaxurl, data, function(response) {
			$("#dates").html(response);
		});
			
			});
			});</script>';
    
    echo'<script type="text/javascript">jQuery(function(){  jQuery(".rotacomms-toggle").click(function(){jQuery(".rotacomms").toggle();  });});</script>';
    
    echo'<hr/><h2 ><a class="pdf-toggle">'.__('Horizontal Rota PDF (Click to toggle)','church-admin').'</a> </h2>';
	echo'<div class="rota-pdf" style="display:none"><form action="'.home_url().'" method="GET"><table class="form-table"><tr><th scope="row">'.__('Select Service','church-admin').'<input type="hidden" name="download" value="horizontal_rota_pdf"/></th><td><select name="service_id">';
				
				$services=$wpdb->get_results('SELECT * FROM '.CA_SER_TBL);
			    foreach($services AS $service)
			    {	
					echo'<option value="'.$service->service_id.'">'.sprintf( __('%1$s on %2$s at %3$s', 'church-admin'),$service->service_name,$days[$service->service_day],$service->service_time).'</option>';
				}
				echo'</select></td></tr>';
				$rota_jobs=$wpdb->get_results('SELECT * FROM '.CA_RST_TBL.' ORDER BY rota_order');
				foreach($rota_jobs AS $rota_job)
				{
					echo'<tr><th scope="row">'.$rota_job->rota_task.'</th><td><input type="checkbox" name="rota_id[]" value="'.$rota_job->rota_id.'"/> '.__('Initials?','church-admin').'<input type="checkbox" name="initials[]" value="'.$rota_job->rota_id.'"/></td></tr>';
		
				}
				
				echo'<tr><td colspan="2"><input   class="button-primary" type="submit" value="'.__('Create PDF','church-admin').'"/></td></tr></table></form>';
   echo'</div><hr/>';
   echo'<script type="text/javascript">jQuery(function(){
    jQuery(".pdf-toggle").click(function(){
        jQuery(".rota-pdf").toggle();
    });
});</script>';

	require_once(plugin_dir_path(__FILE__).'/rota.new.php');
	
	echo'<h2>'.__('Rota','church-admin').'</h2>';
	if(empty($service_id))$service_id=1;
	church_admin_rota_list($service_id);
				
}

function church_admin_smallgroups_main()
{
    $member_type=church_admin_member_type_array();
	echo'<form name="ca_search" action="admin.php?page=church_admin/index.php&amp;tab=address" method="POST"><table class="form-table"><tbody><tr><th scope="row">'.__('Search','church-admin').'</th><td><input name="church_admin_search" style="width:100px;" type="text"/><input   class="button-primary" type="submit" value="'.__('Go','church-admin').'"/></td></tr></table></form>';
	
	require_once(plugin_dir_path(__FILE__).'/small_groups.php');
	echo church_admin_small_groups();
	
    
}
function church_admin_communication()
{
	echo'<form name="ca_search" action="admin.php?page=church_admin/index.php&amp;tab=address" method="POST"><table class="form-table"><tbody><tr><th scope="row">'.__('Search','church-admin').'</th><td><input name="church_admin_search" style="width:100px;" type="text"/><input class="button-primary" type="submit" value="'.__('Go','church-admin').'"/></td></tr></table></form>';
    echo'<p><a href="admin.php?page=church_admin/index.php&amp;action=church_admin_send_sms&amp;tab=communication">'.__('Send Bulk SMS','church-admin').'</a></p>';
    echo'<p><a href="admin.php?page=church_admin/index.php&amp;action=church_admin_send_email&amp;tab=communication">'.__('Send Bulk Email','church-admin').'</a></p>';
	echo'<p><a href="admin.php?page=church_admin/index.php&amp;action=mailchimp_sync&amp;tab=communication">'.__('Sync Mailchimp Account','church-admin').'</a></p>';
	echo'<p><a href="'.wp_nonce_url('admin.php?page=church_admin/index.php&amp;action=prayer_chain_message&amp;tab=communication','prayer_chain_message').'">'.__('Send Prayer Chain Message','church-admin').'</a></p>';
	//echo'<p><a href="'.wp_nonce_url('admin.php?page=church_admin/index.php&amp;action=test_email&amp;tab=communication','test_email').'">Send a test email to "'.get_option('admin_email').'" with debug info</a></p>';
	echo'<p><form action="admin.php" method=GET><input type="hidden" name="page" value="church_admin/index.php"/><input type=hidden name=action value="test_email"/>';
	echo wp_nonce_field('test_email');
	echo'<input type="text" name="email" value="'.get_option('admin_email').'"/><input type=submit value="'.__('Send test email with debug information','church-admin').'" class="button-primary"/></form></p>';
	require_once(plugin_dir_path(__FILE__).'/email.php');
	church_admin_email_list();
}

function church_admin_people_main()
{
    global $people_type;
	$member_type=church_admin_member_type_array();
    		global $wpdb;
    		
    		
    		
    		
	$check=$wpdb->get_var('SELECT COUNT(*) FROM '.CA_HOU_TBL);
	if(!empty($check))
	{
		echo'<form name="ca_search" action="admin.php?page=church_admin/index.php&amp;tab=address" method="POST"><table class="form-table"><tbody><tr><th scope="row">'.__('Search','church-admin').'</th><td><input name="church_admin_search" style="width:100px;" type="text"/><input  class="button-primary"  type="submit" value="'.__('Go','church-admin').'"/></td></tr></table></form>';
   
		echo'<h2 class="sections-toggle">'.__('People','church-admin').'</h2>';
		echo '<p><a class="button-primary" href="'.wp_nonce_url('admin.php?page=church_admin/index.php&amp;tab=address&action=church_admin_new_household','new_household').'">'.__('Add a Household','church-admin').'</a> </p>';
		
		echo'<p><a class="button-secondary" href="'.wp_nonce_url('admin.php?page=church_admin/index.php&amp;action=csv-import&amp;tab=people','csv_import').'">'.__('Import CSV','church-admin').'</a></p>';
			echo'<p><a class="button-secondary" href="'.wp_nonce_url('admin.php?page=church_admin/index.php&amp;action=create_users&amp;tab=people','create_users').'">'.__('Create user accounts ','church-admin').'</a></p>';
		require_once(plugin_dir_path(__FILE__).'/filter.php');
    	church_admin_directory_filter(TRUE);
		
		//echo'<script type="text/javascript">jQuery(function(){  jQuery(".sections-toggle").click(function(){jQuery(".sections").toggle();  });});</script>';
			
				//select member type address list to view.
			    echo'<hr/><table class="form-table"><tbody><tr><th scope="row">'.__('Select a directory to view','church-admin').'</th><td><form name="address" action="admin.php?page=church_admin/index.php&amp;action=church_admin_address_list" method="POST"><select name="member_type_id" >';
			    echo '<option value="0">'.__('All Member Types','church-admin').'</option>';
			    foreach($member_type AS $key=>$value)
			    {
					$count=$wpdb->get_var('SELECT COUNT(people_id) FROM '.CA_PEO_TBL.' WHERE member_type_id="'.esc_sql($key).'"');
					echo '<option value="'.esc_html($key).'" >'.esc_html($value).' ('.$count.' people)</option>';
			    }
			    echo'</select><input  class="button-primary"  type="submit" value="'.__('Go','church-admin').'"/></form></td></tr></tbody></table>';
			    
			    
			    //CSV
			    echo'<h2 class="csv-toggle">'.__('Download a csv of people (Click to toggle view)','church-admin').'</h2>';
			    echo'<div class="csv" style="display:none">';
			    echo'<form action="'.home_url().'" method="get">';
				echo wp_nonce_field('people-csv','people-csv');
				require_once(plugin_dir_path(dirname(__FILE__) ).'/includes/filter.php');
				church_admin_directory_filter(FALSE);
				/*echo'<h3>'.__('Choose a member type','church-admin').'</h3>';
				echo '<input type="checkbox" name="member_type_id" value="0"/>'.__('Whole Address List - overrides other choices!','church-admin').'<br/>';
			    foreach($member_type AS $key=>$value)
				{
						echo '<input type="checkbox" name="member_type_id[]" value="'.esc_html($key).'"/>'.esc_html($value).'<br/>';
				}
				foreach($people_type AS $key=>$value)
				{
						echo '<input type="checkbox" name="people_type_id[]" value="'.esc_html($key).'"/> '.esc_html($value).'<br/>';
				}
				echo'<h3>'.__('Specify one gender only','church-admin').'</h3>';
				$gender=get_option('church_admin_gender');
				foreach($gender AS $key=>$value)
				{
					echo'<input type="checkbox" name="sex[]" value="'.esc_html($key).'" />'.esc_html($value).'<br/>';
				}
				echo'<h3>'.__('Other options','church-admin').'</h3>';
				echo'<input type="checkbox" name="address" value="1" /> Include Address<br/>';
				echo'<input type="checkbox" name="small_group" value="1" /> Include Small Group<br/>';*/
				echo'<br style="clear:left"/>';
				echo'<input type="hidden" name="download" value="people-csv"/><input class="button-primary" type="submit" value="'.__('Download','church-admin').'"/></p>';
			    echo'</form></td></tr></tbody></table></div>';
			    echo'<script type="text/javascript">jQuery(function(){  jQuery(".csv-toggle").click(function(){jQuery(".csv").toggle();  });});</script>';
			    //people activity
			    echo'<h2 class="recent-toggle">'.__('Recent People Activity (Click to toggle view)','church-admin').'</h2>';
			    echo'<div class="recent" style="display:none">';
			    require_once(plugin_dir_path( dirname(__FILE__) ).'/includes/people_activity.php');
				church_admin_recent_people_activity();
				echo'</div>';
				 echo'<script type="text/javascript">jQuery(function(){  jQuery(".recent-toggle").click(function(){jQuery(".recent").toggle();  });});</script>';
    //people in directory
	//member types
	 echo'<h2 class="member-toggle" >'.__('Member Types  (Click to toggle view)','church-admin').'</h2>';
	 echo'<div class="member" style="display:none">';
	 echo'<p><a class="button-primary" href="'.wp_nonce_url("admin.php?page=church_admin/index.php&amp;action=church_admin_edit_member_type",'edit_member_type').'">'.__('Add a member Type','church-admin').'</a></p>';
	require_once(plugin_dir_path(__FILE__).'/member_type.php');
	church_admin_member_type();

	echo'</div>';
	echo'<script type="text/javascript">jQuery(function(){  jQuery(".member-toggle").click(function(){jQuery(".member").toggle();  });});</script>';
	
	//birthdays
	echo'<h2 class="birthdays-toggle" >'.__('Birthdays in next 31days  (Click to toggle view)','church-admin').'</h2>';
	echo'<div class="birthdays" style="display:none">';

	echo church_admin_frontend_birthdays(0, 31);
	echo'</div>';
	echo'<script type="text/javascript">jQuery(function(){  jQuery(".birthdays-toggle").click(function(){jQuery(".birthdays").toggle();  });});</script>';
	
	//follow up
	echo'<h2 class="followup-toggle">'.__('Follow Up (Click to toggle view)','church-admin').'</h2>';
	echo'<div class="followup" style="display:none">';
	require_once(plugin_dir_path(__FILE__).'funnel.php');
	church_admin_funnel_list();
	echo'</div>';
	echo'<script type="text/javascript">jQuery(function(){  jQuery(".followup-toggle").click(function(){jQuery(".followup").toggle();  });});</script>';
	echo'<h2 class="classes-toggle">'.__('Classes (Click to toggle view)','church-admin').'</h2>';
	echo'<div class="classes" style="display:none">';
	
	require_once(plugin_dir_path(__FILE__).'classes.php');
	church_admin_classes();
	echo'</div>';
	echo'<script type="text/javascript">jQuery(function(){  jQuery(".classes-toggle").click(function(){jQuery(".classes").toggle();  });});</script>';
	
	}
	else
	{
		echo '<p><a class="button-primary" href="'.wp_nonce_url('admin.php?page=church_admin/index.php&amp;tab=address&action=church_admin_new_household','new_household').'">'.__('Add a Household','church-admin').'</a> </p>';
		
		echo'<p><a class="button-secondary" href="'.wp_nonce_url('admin.php?page=church_admin/index.php&amp;action=csv-import&amp;tab=people','csv_import').'">Import CSV</a></p>';
	}
}

?>