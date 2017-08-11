<?php
/*

Plugin Name: Church Admin
Plugin URI: http://www.churchadminplugin.com/
Description: A  admin system with address book, small groups, rotas, bulk email  and sms
Version: 1.0973
Author: Andy Moyle
Text Domain: church-admin


Author URI:http://www.themoyles.co.uk
License:
----------------------------------------

    
Copyright (C) 2010-2016 Andy Moyle



    This program is free software: you can redistribute it and/or modify

    it under the terms of the GNU General Public License as published by

    the Free Software Foundation, either version 3 of the License, or

    (at your option) any later version.



    This program is distributed in the hope that it will be useful,

    but WITHOUT ANY WARRANTY; without even the implied warranty of

    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the

    GNU General Public License for more details.



	http://www.gnu.org/licenses/

----------------------------------------
  ___ _   _             _ _         _                 _         _                     
 |_ _| |_( )___    __ _| | |   __ _| |__   ___  _   _| |_      | | ___  ___ _   _ ___ 
  | || __|// __|  / _` | | |  / _` | '_ \ / _ \| | | | __|  _  | |/ _ \/ __| | | / __|
  | || |_  \__ \ | (_| | | | | (_| | |_) | (_) | |_| | |_  | |_| |  __/\__ \ |_| \__ \
 |___|\__| |___/  \__,_|_|_|  \__,_|_.__/ \___/ \__,_|\__|  \___/ \___||___/\__,_|___/
                                                                                      

*/
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

	$church_admin_version = '1.0973';
	$people_type=get_option('church_admin_people_type');
    
    $level=get_option('church_admin_levels');
	if(!empty($_POST['save-ca-modules'])){require_once(plugin_dir_path(__FILE__).'includes/settings.php');church_admin_modules();}
	
	
	


/* look for register shortcode when saving a post/page */
add_action( 'save_post', 'church_admin_register_save',10,3 );
/**
 * Save post metadata when a post is saved.
 *
 * @param int $post_id The post ID.
 * @param post $post The post object.
 * @param bool $update Whether this is an existing post being updated or not.
 */
function church_admin_register_save( $post_id, $post, $update )
{
	if(has_shortcode($post->post_content,'church_admin_register')){update_option('church_admin_register',$post->ID);}
	if(has_shortcode($post->post_content,'church_admin_unsubscribe')){update_option('church_admin_unsubscribe',$post->ID);}

}

/* initialise plugin */
add_action( 'plugins_loaded', 'church_admin_initialise' );
function church_admin_initialise() {
	global $level,$church_admin_version,$wpdb,$current_user;
	
	define('CA_PATH',plugin_dir_path( __FILE__));
	wp_get_current_user();
	church_admin_constants();//setup constants first
	//Version Number
	define('OLD_CHURCH_ADMIN_VERSION',get_option('church_admin_version'));
	if(OLD_CHURCH_ADMIN_VERSION!= $church_admin_version)
	{
		church_admin_backup();
		require_once(plugin_dir_path( __FILE__) .'/includes/install.php');
		church_admin_install();
	}
	
	$rota_order=ca_rota_order();
	$member_type=church_admin_member_type_array();
	if(isset($_GET['action'])&&$_GET['action']=='auto_backup'){require_once(plugin_dir_path(__FILE__).'includes/pdf_creator.php');church_admin_backup_pdf();exit();}
	if(isset($_GET['action'])&&$_GET['action']=="delete_backup"){check_admin_referer('delete_backup');church_admin_delete_backup();}
	if(isset($_GET['action'])&&$_GET['action']=="refresh_backup")	{check_admin_referer('refresh_backup');church_admin_backup();}
	load_plugin_textdomain( 'church-admin', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' ); 

	
    if(empty($level['Directory']))$level['Directory']='administrator';
    if(empty($level['Small Groups']))$level['Small Groups']='administrator';
    if(empty($level['Rota']))$level['Rota']='administrator';
    if(empty($level['Funnel'])) $level['Funnel']='administrator';
    if(empty($level['Bulk Email']))$level['Bulk Email']='administrator';
    if(empty($level['Sermons']))$level['Sermons']='administrator';
	if(empty($level['Bulk SMS']))$level['Bulk SMS']='administrator';
    if(empty($level['Calendar']))$level['Calendar']='administrator';
    if(empty($level['Attendance']))$level['Attendance']='administrator';
    if(empty($level['Member Type']))$level['Member Type']='administrator';
    if(empty($level['Service']))$level['Service']='administrator';
	if(empty($level['Prayer Chain']))$level['Prayer Chain']='administrator';
	if(empty($level['Sessions']))$level['Sessions']='administrator';
	if(empty($level['App']))$level['App']='administrator';
    update_option('church_admin_levels',$level);
    if(!empty($_POST['one_site']))$wpdb->query('UPDATE '.CA_PEO_TBL.' SET site_id="'.intval($_POST['site_id']).'"');
    //church admin app initialisation
	
	if(!empty($_GET['ca-app']))
	{
		require_once(plugin_dir_path(__FILE__).'app/app-admin.php');
		switch($_GET['ca-app'])
		{
			case'latest_media': header("Content-Type: application/json");echo church_admin_json_latest_media();exit();break;
		
		}
	}
	
	//copy rota and then redirect
	 if(!empty($_GET['page'])&&($_GET['page']=='church_admin/index.php')&&!empty($_GET['action'])&& $_GET['action']=='copy_rota_data' &&church_admin_level_check('Rota'))
	{
		require_once(plugin_dir_path(__FILE__).'includes/rota.new.php');
		church_admin_copy_rota($_GET['rotaDate1'],$_GET['rotaDate2'], $_GET['service_id'],$_GET['mtg_type']);
		$url=admin_url().'admin.php?page=church_admin%2Findex.php&action=rota&tab=rota&message=copied';
		wp_redirect( $url );
		exit;
	}
		//reset version
	 if(!empty($_GET['page'])&&($_GET['page']=='church_admin/index.php')&&!empty($_GET['action'])&& $_GET['action']=='reset_version')
	{
		check_admin_referer('reset_version');
		
		delete_option("church_admin_version");
		$url=admin_url().'admin.php?page=church_admin%2Findex.php&message=Church+Admin+Version+Reset';
		wp_redirect( $url );
		exit;
	}
		//reset version
			//upgrade rota for 1.095
	 if(!empty($_GET['page'])&&($_GET['page']=='church_admin/index.php')&&!empty($_GET['action'])&& $_GET['action']=='upgrade_rota')
	{
		check_admin_referer('upgrade_rota');
		
		delete_option("church_admin_version");
		$wpdb->query('TRUNCATE TABLE '.CA_ROTA_TBL);
		$url=admin_url().'admin.php?page=church_admin%2Findex.php&message=Rota+Table+Reset';
		wp_redirect( $url );
		exit;
	}
		//upgrade rota for 1.095
	 if(!empty($_GET['page'])&&($_GET['page']=='church_admin/index.php')&&!empty($_GET['action'])&& $_GET['action']=='clear_debug')
	{
		check_admin_referer('clear_debug');
		
		$upload_dir = wp_upload_dir();
		$debug_path=$upload_dir['basedir'].'/church-admin-cache/debug.log';
		unlink($debug_path);
		$url=admin_url().'admin.php?page=church_admin%2Findex.php&action=settings&tab=settings&message=Church+Admin+Debug+Log+has+been+deleted.';
		wp_redirect( $url );
		exit;
	}
    //save the church admin note before any display happens
    
	if(!empty($_POST['save-ca-comment']))
 	{
 		church_admin_debug('******************************'."\r\n Save Comment ".date('Y-m-d H:i:s')."\r\n");
 		$sqlsafe=array();
 		
 		if(!empty($_POST['parent_id']))$parent_id=intval($_POST['parent_id']);
 		if(empty($parent_id))$parent_id=null;
 		foreach($_POST AS $key=>$value)$sqlsafe[$key]=esc_sql(stripslashes($value));
 		if(!empty($_POST['comment_id']))
 		{
 			$sql='UPDATE '.CA_COM_TBL.' SET comment="'.$sqlsafe['comment'].'",comment_type="'.$sqlsafe['comment_type'].'",parent_id="'.$parent_id.'",author_id="'.intval($current_user->ID).'",timestamp="'.date('Y-m-d h:i:s').'" comment_id="'.intval($sqlsafe['comment_id']).'"';
 		}
 		else
 		{
 			
 			$sql='INSERT INTO '.CA_COM_TBL.' (comment,comment_type,parent_id,author_id,timestamp,ID)VALUES("'.$sqlsafe['comment'].'","'.$sqlsafe['comment_type'].'","'.$parent_id.'","'.intval($current_user->ID).'","'.date('Y-m-d h:i:s').'","'.intval($sqlsafe['ID']).'")';
 		}
 		church_admin_debug('******************************'."\r\n $sql \r\n");
 		$wpdb->query($sql);
 		if(empty($sqlsafe['comment_id']))$sqlsafe['comment_id']=$wpdb->insert_id;
 		
 		$comment=$wpdb->get_row('SELECT * FROM '.CA_COM_TBL.' WHERE comment_id="'.intval($sqlsafe['comment_id']).'"');
 		
 	}
 

}

require_once(plugin_dir_path(__FILE__) .'includes/functions.php');
require_once(plugin_dir_path(__FILE__).'includes/admin.php');
require_once(plugin_dir_path(__FILE__).'app/app-admin.php');

//add_filter('wp_mail_content_type',create_function('', 'return "text/html"; '));
add_action('activated_plugin','church_admin_save_error');
function church_admin_save_error(){
    update_option('church_admin_plugin_error',  ob_get_contents());
}
add_action('load-church-admin', 'church_admin_add_screen_meta_boxes');

//update_option('church_admin_roles',array(2=>'Elder',1=>'Small group Leader'));
$oldroles=get_option('church_admin_roles');
if(!empty($oldroles))
{
    update_option('church_admin_departments',$oldroles);
    delete_option('church_admin_roles');
}


function church_admin_constants()
{
/**
 *
 * Sets up constants for plugin
 * 
 * @author  Andy Moyle
 * @param    null
 * @return   
 * @version  0.1
 * 
 */ 
    global $wpdb;
//define DB
define('CA_ATT_TBL',$wpdb->prefix.'church_admin_attendance');
define('CA_BRP_TBL',$wpdb->prefix.'church_admin_brplan');
define('CA_APP_TBL',$wpdb->prefix.'church_admin_app');
define('CA_CP_TBL',$wpdb->prefix.'church_admin_safeguarding');
define ('CA_BIB_TBL',$wpdb->prefix.'church_admin_bible_books');
define ('CA_CAT_TBL',$wpdb->prefix.'church_admin_calendar_category');
define('CA_CLA_TBL',$wpdb->prefix.'church_admin_classes');
define('CA_COM_TBL',$wpdb->prefix.'church_admin_comments');
define('CA_DATE_TBL',$wpdb->prefix.'church_admin_calendar_date');
define ('CA_FIL_TBL',$wpdb->prefix.'church_admin_sermon_files');
define ('CA_KJV_TBL',$wpdb->prefix.'church_admin_kjv');
define('CA_EMA_TBL',$wpdb->prefix.'church_admin_email');
define('CA_EBU_TBL',$wpdb->prefix.'church_admin_email_build');
define('CA_EVE_TBL',$wpdb->prefix.'church_admin_calendar_event');
define ('CA_FAC_TBL',$wpdb->prefix.'church_admin_facilities');
define('CA_FUN_TBL',$wpdb->prefix.'church_admin_funnels');
define('CA_FP_TBL',$wpdb->prefix.'church_admin_follow_up');
define('CA_HOU_TBL',$wpdb->prefix.'church_admin_household');
define('CA_HOP_TBL',$wpdb->prefix.'church_admin_hope_team');
define('CA_IND_TBL',$wpdb->prefix.'church_admin_individual_attendance');
define('CA_KID_TBL',$wpdb->prefix.'church_admin_kidswork');
define('CA_MET_TBL',$wpdb->prefix.'church_admin_people_meta');
define('CA_MTY_TBL',$wpdb->prefix.'church_admin_member_types');
define('CA_MIN_TBL',$wpdb->prefix.'church_admin_ministries');
define('CA_PEO_TBL',$wpdb->prefix.'church_admin_people');
define('CA_ROTA_TBL',$wpdb->prefix.'church_admin_new_rota');
define('CA_ROT_TBL',$wpdb->prefix.'church_admin_rotas');
define('CA_RST_TBL',$wpdb->prefix.'church_admin_rota_settings');
define('CA_SMG_TBL',$wpdb->prefix.'church_admin_smallgroup');
define('CA_SER_TBL',$wpdb->prefix.'church_admin_services');
define('CA_SES_TBL',$wpdb->prefix.'church_admin_session');
define('CA_SMET_TBL',$wpdb->prefix.'church_admin_session_meta');
define('CA_SIT_TBL',$wpdb->prefix.'church_admin_sites');
define ('CA_SERM_TBL',$wpdb->prefix.'church_admin_sermon_series');


//define DB
define('OLD_CHURCH_ADMIN_EMAIL_CACHE',WP_PLUGIN_DIR.'/church-admin-cache/');
define('OLD_CHURCH_ADMIN_EMAIL_CACHE_URL',WP_PLUGIN_URL.'/church-admin-cache/');


define('CA_POD_URL',content_url().'/uploads/sermons/');
$upload_dir = wp_upload_dir();
if(!is_dir( $upload_dir['basedir'].'/sermons/'))
    {
        $old = umask(0);
        mkdir( $upload_dir['basedir'].'/sermons/');
        chmod($upload_dir['basedir'].'/sermons/', 0755);
        umask($old); 
        $index="<?php\r\n//nothing is good;\r\n?>";
        $fp = fopen($upload_dir['basedir'].'/sermons/'.'index.php', 'w');
        fwrite($fp, $index);
        fclose($fp);
    }
if(!is_dir($upload_dir['basedir'].'/church-admin-cache/'))
{
        $old = umask(0);
		 mkdir($upload_dir['basedir'].'/church-admin-cache/');
        chmod($upload_dir['basedir'].'/church-admin-cache/', 0755);
        umask($old); 
        $index="<?php\r\n//nothing is good;\r\n?>";
        $fp = fopen($upload_dir['basedir'].'/church-admin-cache/'.'index.php', 'w');
        fwrite($fp, $index);
        fclose($fp);
}
if(is_dir(OLD_CHURCH_ADMIN_EMAIL_CACHE))
{
    
    //grab files
    $files=scandir(OLD_CHURCH_ADMIN_EMAIL_CACHE);
    if(!empty($files))
    {
	foreach($files AS $file)
	{
	    if ($file!= "." && $file!= "..")
	    {
	        //work through files, but don't delete as old emails have link to old uris
	        $success=copy(OLD_CHURCH_ADMIN_EMAIL_CACHE.$file,plugin_dir_path( dirname(__FILE__)).'church-admin-cache/'.$file);
	        if($success)
	        {
	        	
	        	unlink(OLD_CHURCH_ADMIN_EMAIL_CACHE.$file);
	        }
	    }
	}
	//create htaccess redirect for cached emails
    
	$htaccess="\r\n RedirectPermanent /wp-content/plugins/church-admin-cache/ /wp-content/uploads/church-admin-cache/\r\n";
	// Let's make sure the file exists and is writable first.
	$htaccess_done=get_option('church_admin_htaccess');
	if (is_writable(ABSPATH.'.htaccess')&&empty($htaccess_done))
	{
    
	    if (!$handle = fopen(ABSPATH.'.htaccess', 'a')) {echo __('Cannot open file','church-admin').'  ('.ABSPATH.'.htaccess)';}
	    elseif(fwrite($handle, $htaccess) === FALSE) {echo __('Cannot write to file','church-admin').' ('.ABSPATH.'.htaccess)';}
	    else{fclose($handle);}
	    update_option('church_admin_htaccess','1');
	} 
    }
    
}

//this needs to happen very early in page call!
 if(isset($_GET['download'])){church_admin_download($_GET['download']);exit();} 
}//end constants
   
function ca_rota_order()
{
 /**
 *
 * Retrieves rota items in order
 * 
 * @author  Andy Moyle
 * @param    null
 * @return   Array, key is order
 * @version  0.1
 * 
 */ 
    global $wpdb;
    //rota_order
    $results=$wpdb->get_results('SELECT * FROM '.CA_RST_TBL.' ORDER BY rota_order ASC');
    if($results)
    {
        $rota_order=array();
        foreach($results AS $row)
        {
            $rota_order[]=$row->rota_id;
        }
    return $rota_order;
    }
    
}
	
    
    
add_filter('the_posts', 'church_admin_conditionally_add_scripts_and_styles'); // the_posts gets triggered before wp_head
function church_admin_conditionally_add_scripts_and_styles($posts){
    /**
 *
 * Add scripts and styles depending on shortcode in post/page, called using filter
 * 
 * @author  Andy Moyle
 * @param    null
 * @return   
 * @version  0.1
 * 
 */ 
	if (empty($posts)) return $posts;
	$shortcode_found = false; // use this flag to see if styles and scripts need to be enqueued
	foreach ($posts as $post) 
	{
		//set $shortcode_found with needed value
		if(stripos($post->post_content,'type=graph')!== false ||stripos($post->post_content,'type="graph"')!== false ||stripos($post->post_content,"type='graph'")!== false )$shortcode_found='graph';
		if(stripos($post->post_content,'type=podcast')!== false ||stripos($post->post_content,'type="podcast"')!== false ||stripos($post->post_content,"type='podcast'")!== false )$shortcode_found='podcast';
		if (stripos($post->post_content, '[church_admin_map') !== false )$shortcode_found='map';
		if (stripos($post->post_content, 'type=small-groups-list') !== false ||stripos($post->post_content, 'type="small-groups-list"') !== false )$shortcode_found='sgmap';
        if(stripos($post->post_content, '[church_admin_register') !== false ) $shortcode_found = 'register';
	}
 	if ($shortcode_found) 
	{
		switch($shortcode_found)
		{//enqueue correct scripts
			case'podcast':church_admin_podcast_script();break;
			case'register':church_admin_form_script();if(!isset($_POST['save'])){church_admin_map_script();}break;
			case'map':church_frontend_map_script();break;
			case'sgmap':church_admin_frontend_sg_map_script();break;
			case'graph':church_admin_frontend_graph_script();church_admin_date_picker_script();break;
		}
	}
 
	return $posts;
}

add_action('wp_head','church_admin_ajaxurl');
function church_admin_ajaxurl() 
{
	$ajax_nonce = wp_create_nonce("church_admin_mp3_play");	
	?>
	<script type="text/javascript">
		var ajaxurl = '<?php echo admin_url('admin-ajax.php'); ?>';
		var security= '<?php echo $ajax_nonce; ?>';
	</script>
	<?php
}
add_action('wp_enqueue_scripts', 'church_admin_init');
add_action('admin_enqueue_scripts', 'church_admin_init',9999);//adding withlow priority to be last to call google maps api
/**
 *
 * Initialises js scripts and css
 * 
 * @author  Andy Moyle
 * @param    null
 * @return   
 * @version  0.1
 * 
 */
function church_admin_init()
{
 
    //This function add scripts as needed
		wp_enqueue_script('common','','',NULL);
		wp_enqueue_script('wp-lists','','',NULL);
		wp_enqueue_script('postbox','','',NULL);

    ca_thumbnails();

	if(!empty($_POST['church_admin_search']))church_admin_editable_script();
	
   
	
	if(isset($_GET['action']))
	{
		switch($_GET['action'])
		{
			case 'services':church_admin_date_picker_script();church_admin_frontend_graph_script();break;
			case'church_admin_cron_email':church_admin_debug('Cron fired:'.date('Y-m-d h:i:s')."/r/n");church_admin_bulk_email();exit();break;
			case 'remove-queue':check_admin_referer('remove-queue');church_admin_remove_queue();break;
			case'church_admin_send_email':church_admin_email_script();church_admin_autocomplete_script();church_admin_date_picker_script();break;
			case'edit_resend':church_admin_email_script();church_admin_autocomplete_script();church_admin_date_picker_script();break;
			case'resend_new':church_admin_email_script();church_admin_autocomplete_script();break;
			case'resend_email':church_admin_email_script();church_admin_autocomplete_script();break;
			case'church_admin_send_sms':church_admin_email_script();church_admin_autocomplete_script();break;
			case'delete_small_group':church_admin_sg_map_script();church_admin_autocomplete_script();break;
			case'church_admin_search';church_admin_editable_script();break;
			//calendar
			case'church_admin_add_dates':church_admin_editable_script();break;
			case'church_admin_add_category':church_admin_farbtastic_script();break;
			case'church_admin_edit_category':church_admin_farbtastic_script();break;
			
			case 'small_groups':church_admin_sortable_script();church_admin_form_script();church_admin_sg_map_script();break;
			case 'edit_service':church_admin_form_script();break;
			case 'edit_site':church_admin_form_script();church_admin_map_script();break;
			case 'edit_small_group':church_admin_form_script();church_admin_sg_map_script();church_admin_map_script();church_admin_autocomplete_script();break;
			
			case'view_class':church_admin_date_picker_script();church_admin_autocomplete_script();break;
			case'church_admin_add_calendar':church_admin_date_picker_script();break;
			case'church_admin_series_event_edit':church_admin_date_picker_script();break;
			case'church_admin_single_event_edit':church_admin_date_picker_script();break;
			case'edit_attendance':church_admin_date_picker_script();break;
			case'church_admin_new_edit_calendar':church_admin_date_picker_script();break;
			case'edit_kidswork':church_admin_date_picker_script();break;
			case'individual_attendance':church_admin_date_picker_script();break;
			case'edit_class':church_admin_date_picker_script();break;
			
			case'edit_hope_team':church_admin_date_picker_script();church_admin_autocomplete_script();break;
			case'permissions':church_admin_date_picker_script();church_admin_autocomplete_script();break;
			case'edit_file':church_admin_date_picker_script();church_admin_autocomplete_script();break;
			case'file_add':church_admin_date_picker_script();church_admin_autocomplete_script();break;
			case'church_admin_member_type':church_admin_sortable_script();break;
			//rota
			case'rota';church_admin_editable_script();break;
			case'edit_rota';church_admin_editable_script();church_admin_autocomplete_script();church_admin_date_picker_script();break;
			case'list';church_admin_editable_script();break;
			case'church_admin_rota_settings_list':church_admin_sortable_script();break;
			case'church_admin_edit_rota_settings':church_admin_sortable_script();break;
			//directory
			case'church_admin_new_household':church_admin_form_script();church_admin_map_script();church_admin_date_picker_script();break;
			case'edit_household':church_admin_form_script();church_admin_map_script();break;
			case'edit_people':church_admin_date_picker_script();church_admin_media_uploader_enqueue();break;
			case'new_household':church_admin_date_picker_script();break;
			case'church_admin_permissions':church_admin_date_picker_script();church_admin_autocomplete_script();break;
			case'view_ministry':church_admin_autocomplete_script();break;
			case'church_admin_update_order': church_admin_update_order($_GET['which']);exit();break;
			case'get_people':church_admin_ajax_people();break;
		}
	}
	

}




function church_admin_media_uploader_enqueue() {
    wp_enqueue_media();
    
  }
 /**
 *
 * Registers google map api with low priority, so it happens last on enqueuing!
 * 
 * @author  Andy Moyle
 * @param    null
 * @return   
 * @version  0.1
 * 
 */
 function church_admin_google_map_api()
 {
 	
 	//fix issue caused by some "premium" themes, which call google maps w/o key on every admin page. D'uh!
 	wp_dequeue_script('avia-google-maps-api');
 	
     //now enqueue google map api with the key
     $src = 'https://maps.googleapis.com/maps/api/js';
     $key='?key='.get_option('church_admin_google_api_key');
     wp_enqueue_script( 'Google Map Script',$src.$key, array() ,FALSE, TRUE);
    
     
 }
 
 /**
 *
 * Initialises js scripts for Google graph api
 * 
 * @author  Andy Moyle
 * @param    null
 * @return   
 * @version  0.1
 * 
 */
function church_admin_frontend_graph_script()
{

	wp_enqueue_script('google-graph-api','https://www.google.com/jsapi', array( 'jquery' ) ,FALSE, FALSE);
	
}
function church_admin_podcast_script()
{			
	$ajax_nonce = wp_create_nonce("church_admin_mp3_play");			
	wp_enqueue_script('ca_podcast_audio',plugins_url('church-admin/includes/audio.min.js',dirname(__FILE__) ) , array( 'jquery' ) ,FALSE, TRUE);
	wp_enqueue_script('ca_podcast_audio_use',plugins_url('church-admin/includes/audio.use.js',dirname(__FILE__) ), array( 'jquery' ) ,FALSE, TRUE);
	wp_localize_script( 'ca_podcast_audio_use', 'ChurchAdminAjax', array('security'=>$ajax_nonce, 'ajaxurl' => admin_url( 'admin-ajax.php' ) ) );
}
function church_admin_sortable_script()
{ 
	wp_enqueue_script( 'jquery-ui-sortable' ,'','',NULL);
	wp_enqueue_script('touch-punch',plugins_url('church-admin/includes/jQuery.touchpunch.js',dirname(__FILE__) ), array( 'jquery' ) ,FALSE, TRUE);	
}
function church_admin_form_script()
{
	wp_enqueue_script('form-clone',plugins_url('church-admin/includes/jquery-formfields.js',dirname(__FILE__) ), array( 'jquery' ) ,FALSE, TRUE);
}
function church_admin_sg_map_script()
{

	church_admin_google_map_api();
	wp_enqueue_script('ca_google_map_script', plugins_url('church-admin/includes/admin_sg_maps.js',dirname(__FILE__) ), array( 'jquery' ) ,FALSE, TRUE);
}
function church_admin_frontend_sg_map_script()
{

	church_admin_google_map_api();
	wp_enqueue_script('ca_google_map_script', plugins_url('church-admin/includes/smallgroup_maps.js',dirname(__FILE__) ), array( 'jquery' ) ,FALSE, TRUE);
}
function church_admin_map_script()
{
	church_admin_google_map_api();
    wp_enqueue_script('js_map', plugins_url('church-admin/includes/maps.js',dirname(__FILE__) ), array( 'jquery' ) ,FALSE, TRUE);
}
function church_frontend_map_script()
{
	church_admin_google_map_api();
	wp_enqueue_script('js_map', plugins_url('church-admin/includes/google_maps.js',dirname(__FILE__) ), array( 'jquery' ) ,FALSE, TRUE);
}
function church_admin_autocomplete_script()
{
	wp_enqueue_script('jquery-ui-autocomplete','','',NULL);
}
function church_admin_date_picker_script()
{
	wp_enqueue_script( 'jquery-ui-datepicker','','',NULL );
	wp_enqueue_style( 'jquery.ui.theme', plugins_url('css/jquery-ui-1.8.21.custom.css',__FILE__ ) ,'',NULL);
}
function church_admin_farbtastic_script()
{
	wp_enqueue_script( 'farbtastic' ,'','',NULL);
    wp_enqueue_style('farbtastic','','',NULL);
}
function church_admin_email_script()
{
	wp_enqueue_script('jquery','','',NULL);
    wp_register_script('ca_email',  plugins_url('church-admin/includes/email.js',dirname(__FILE__) ), array( 'jquery' ) ,FALSE, TRUE);
	wp_enqueue_script('ca_email','','',NULL);
}
function church_admin_editable_script()
{
    wp_register_script('ca_editable',  plugins_url('church-admin/includes/jquery.jeditable.mini.js',dirname(__FILE__) ), array('jquery'), NULL,TRUE);
    wp_enqueue_script('ca_editable');
}




/* Thumbnails */
function ca_thumbnails()
{
        /**
 *
 * Add thumbnails for plugin use
 * 
 * @author  Andy Moyle
 * @param    null
 * @return   
 * @version  0.1
 * 
 */ 
    add_theme_support( 'post-thumbnails' );
    if ( function_exists( 'add_image_size' ) )
    {
        add_image_size('ca-people-thumb',75,75);
	add_image_size( 'ca-email-thumb', 300, 200 ); //300 pixels wide (and unlimited height)
	add_image_size('ca-120-thumb',120,90);
	add_image_size('ca-240-thumb',240,180);
    }
    
}
/* Thumbnails */
add_action( 'admin_enqueue_scripts','church_admin_public_css');
add_action('wp_enqueue_scripts','church_admin_public_css');
function church_admin_public_css(){wp_enqueue_style('Church-Admin',plugins_url('church-admin/includes/style.css',dirname(__FILE__) ),'',NULL);}
add_action('wp_head', 'church_admin_public_header');
function church_admin_public_header()
{
    global $church_admin_version;
     
    echo'<!-- church_admin v'.$church_admin_version.'-->
    <style>table.church_admin_calendar{width:';
    if(get_option('church_admin_calendar_width')){echo get_option('church_admin_calendar_width').'px}</style>';}else {echo'700px}</style>';}
    
}

//Build Admin Menus
add_action('admin_menu', 'church_admin_menus');
/**
 *
 * Admin menu
 * 
 * @author  Andy Moyle
 * @param    null
 * @return   
 * @version  0.1
 * 
 */
function church_admin_menus() 

{
 
    global $level;
	//deprecated next three lines to allow for users to edit own page;
    //$user_permissions=get_option('church_admin_user_permissions');
    //let plugin decide level of showing admin menu
    //if(!empty($user_permissions)){$level='read';}else{$level='manage_options';}
    add_menu_page('church_admin:Administration', __('Church Admin','church-admin'),  'read', 'church_admin/index.php', 'church_admin_main');
    //add_submenu_page('church_admin/index.php', __('Permissions','church-admin'), 'Permissions', 'manage_options', 'church_admin_permissions', 'church_admin_permissions');
   

}

// Admin Bar Customisation
/**
 *
 * Admin Bar Menu
 * 
 * @author  Andy Moyle
 * @param    null
 * @return   
 * @version  0.1
 * 
 */ 
function church_admin_admin_bar_render() {

 global $wp_admin_bar;
 // Add a new top level menu link
 // Here we add a customer support URL link
 $wp_admin_bar->add_menu( array('parent' => false, 'id' => 'church_admin', 'title' => __('Church Admin','church-admin'), 'href' => admin_url().'admin.php?page=church_admin/index.php' ));
 $wp_admin_bar->add_menu(array('parent' => 'church_admin','id' => 'church_admin_settings', 'title' => __('Settings','church-admin'), 'href' => admin_url().'admin.php?page=church_admin/index.php&action=church_admin_settings' ));
	//$wp_admin_bar->add_menu(array('parent' => 'church_admin','id' => 'church_admin_permissions', 'title' => __('Permissions','church-admin'), 'href' => admin_url().'admin.php?page=church_admin_permissions' ));
  $wp_admin_bar->add_menu(array('parent' => 'church_admin','id' => 'plugin_support', 'title' => __('Plugin Support','church-admin'), 'href' => 'http://www.churchadminplugin.com/support/' ));
}

// Finally we add our hook function
add_action( 'wp_before_admin_bar_render', 'church_admin_admin_bar_render' );




//main admin page function


function church_admin_main() 
{
    global $wpdb,$church_admin_version;
	echo'<div class="wrap"><!--church_admin_main-->';
	//menu at top of all admin pages
	require_once(plugin_dir_path(__FILE__).'includes/admin.php');
	church_admin_front_admin();
	
	//allow people to edit their own entry
	
	$self_edit=FALSE;
	$user_id=get_current_user_id();
	if(!empty($_GET['household_id']))$check=$wpdb->get_var('SELECT user_id FROM '.CA_PEO_TBL.' WHERE user_id="'.esc_sql($user_id).'" AND household_id="'.esc_sql($_GET['household_id']).'"');
	if(!empty($check) && $check==$user_id)$self_edit=TRUE;
	
	$id=!empty($_GET['id'])?$_GET['id']:NULL;
	$mtg_type=!empty($_GET['mtg_type'])?$_GET['mtg_type']:'service';
	$rota_date=!empty($_GET['rota_date'])?$_GET['rota_date']:NULL;
	$rota_id=!empty($_GET['rota_id'])?$_GET['rota_id']:NULL;
	$copy_id=!empty($_GET['copy_id'])?$_GET['copy_id']:NULL;
    $date_id=!empty($_GET['date_id'])?$_GET['date_id']:NULL;
    $event_id=!empty($_GET['event_id'])?$_GET['event_id']:NULL;
	$email_id=!empty($_GET['email_id'])?$_GET['email_id']:NULL;
    $people_id=!empty($_GET['people_id'])?$_GET['people_id']:NULL;
    $household_id=!empty($_GET['household_id'])?$_GET['household_id']:NULL;
    $service_id=!empty($_REQUEST['service_id'])?$_REQUEST['service_id']:NULL;
    $site_id=!empty($_REQUEST['site_id'])?$_REQUEST['site_id']:NULL;
    $attendance_id=!empty($_GET['attendance_id'])?$_GET['attendance_id']:NULL;
    $ID=!empty($_GET['ID'])?$_GET['ID']:NULL;
    $funnel_id=!empty($_GET['funnel_id'])?$_GET['funnel_id']:NULL;
    $people_type_id=isset($_GET['people_type_id'])?$_GET['people_type_id']:NULL;
    $member_type_id=isset($_REQUEST['member_type_id'])?$_REQUEST['member_type_id']:NULL;
	$facilities_id=isset($_REQUEST['facilities_id'])?$_REQUEST['facilities_id']:NULL;
    $edit_type=!empty($_REQUEST['edit_type'])?$_REQUEST['edit_type']:'single';
    $file=!empty($_GET['file'])?$_GET['file']:NULL;
	$smallgroup_id=!empty($_GET['smallgroup_id'])?$_GET['smallgroup_id']:NULL;
    if(!empty($_REQUEST['church_admin_search'])){if(church_admin_level_check('Directory')){require_once(plugin_dir_path(__FILE__).'includes/directory.php');church_admin_search($_REQUEST['church_admin_search']);}}
	elseif(isset($_GET['action']))
    {
	switch($_GET['action'])
	{
		case 'reset_readings':$wpdb->query('UPDATE '.CA_BRP_TBL.' SET passages=""');echo'Done ;-)';break;
		case 'test_email':require_once(plugin_dir_path(__FILE__).'includes/email.php');church_admin_test_email($_GET['email']);break;
		case 'app_page':require_once(plugin_dir_path(__FILE__).'app/app-admin.php');church_admin_app_post();break;
		//main menu sections
		case 'app': require_once(plugin_dir_path(__FILE__).'app/app-admin.php');church_admin_app();break;
		case 'sessions': require_once(plugin_dir_path(__FILE__).'includes/admin.php');church_admin_sessions_menu();break;
		case'shortcodes':church_admin_shortcodes_list();break;
		case'classes':if(church_admin_level_check('Directory')){require_once(plugin_dir_path(__FILE__).'includes/classes.php');church_admin_classes();}else{echo'<div class="error"><p>You don\'t have permissions</p></div>';}break;
		case'small_groups':if(church_admin_level_check('Small Groups')){ echo church_admin_smallgroups_main();}else{echo'<div class="error"><p>'.__("You don't have permissions",'church-admin').'</p></div>';}break;
		case'services':if(church_admin_level_check('Small Groups')){church_admin_services_main();}else{echo'<div class="error"><p>'.__("You don't have permissions",'church-admin').'</p></div>';}break;
		case'ministries':if(church_admin_level_check('Directory')){church_admin_ministries_menu();break;}else{echo'<div class="error"><p>'.__("You don't have permissions",'church-admin').'</p></div>';}break;
		case'people':if(church_admin_level_check('Directory')){church_admin_people_main();}else{echo'<div class="error"><p>'.__("You don't have permissions",'church-admin').'</p></div>';}break;
		case'children':if(church_admin_level_check('Directory')){church_admin_children();}else{echo'<div class="error"><p>'.__("You don't have permissions",'church-admin').'</p></div>';}break;
		case'communication':if(church_admin_level_check('Prayer Chain')){church_admin_communication();}else{echo'<div class="error"><p>'.__("You don't have permissions",'church-admin').'</p></div>';}break;
		case'rota':if(church_admin_level_check('Rota')){church_admin_rota_main($service_id);}else{echo'<div class="error"><p>'.__("You don't have permissions",'church-admin').'</p></div>';}break;
		case'tracking':if(church_admin_level_check('Attendance')){church_admin_tracking();}else{echo'<div class="error"><p>'.__("You don't have permissions",'church-admin').'</p></div>';}break;
		case 'podcast':if(church_admin_level_check('Sermons')){church_admin_podcast();}else{echo'<div class="error"><p>'.__("You don't have permissions",'church-admin').'</p></div>';}break;
		case 'settings':if(current_user_can('manage_options')){church_admin_settings_menu();}else{echo'<div class="error"><p>'.__("You don't have permissions",'church-admin').'</p></div>';}break;
		case 'calendar':if(church_admin_level_check('Calendar')){require_once(plugin_dir_path(__FILE__).'includes/calendar.php');church_admin_new_calendar(time(),$facilities_id);}else{echo'<div class="error"><p>You don\'t have permissions</p></div>';}break;
		case 'facilities':if(church_admin_level_check('Calendar')){require_once(plugin_dir_path(__FILE__).'includes/calendar.php');church_admin_facilities(time(),$facilities_id);}else{echo'<div class="error"><p>You don\'t have permissions</p></div>';}break;
		//csv import 
		case'csv-import':if(church_admin_level_check('Directory')){check_admin_referer('csv_import');require_once(plugin_dir_path(__FILE__).'includes/directory.php');church_admin_import_csv();}break;
		//classes
		case 'class':require_once(plugin_dir_path(__FILE__).'includes/classes.php');church_admin_classes();break;
		case 'edit_class':require_once(plugin_dir_path(__FILE__).'includes/classes.php');church_admin_edit_class($id);break;
		case 'delete_class':require_once(plugin_dir_path(__FILE__).'includes/classes.php');church_admin_delete_class($id);break;
		case 'view_class':require_once(plugin_dir_path(__FILE__).'includes/classes.php');church_admin_view_class($id);break;
/*************************************
*
*		KIDS WORK
*
**************************************/		
		case 'edit_kidswork':require_once(plugin_dir_path(__FILE__).'includes/kidswork.php');church_admin_edit_kidswork($id);break;
		case 'delete_kidswork':require_once(plugin_dir_path(__FILE__).'includes/kidswork.php');church_admin_delete_kidswork($id);break;
		case 'kidswork':require_once(plugin_dir_path(__FILE__).'includes/kidswork.php');church_admin_kidswork();break;
		case 'edit_safeguarding':require_once(plugin_dir_path(__FILE__).'includes/kidswork.php');church_admin_edit_safeguarding($people_id);break;
		//prayer chain 
		
		case'prayer_chain_message':if(church_admin_level_check('Prayer Chain')){require_once(plugin_dir_path(__FILE__).'includes/prayer_chain.php');church_admin_prayer_chain();}else{echo"You don't have permission to send a prayer chain message"; }break;
/*************************************
*
*		HOPETEAM
*
**************************************/
		case'hope_team_jobs':check_admin_referer('hope_team_jobs');require_once(plugin_dir_path(__FILE__).'includes/hope-team.php');church_admin_hope_team_jobs($id);break;
		case'edit_hope_team_job':check_admin_referer('hope_team_jobs');require_once(plugin_dir_path(__FILE__).'includes/hope-team.php');church_admin_edit_hope_team_job($id);break;
		case'delete_hope_team_job':check_admin_referer('delete_hope_team_jobs');require_once(plugin_dir_path(__FILE__).'includes/hope-team.php');church_admin_delete_hope_team_job($id);break;
		case'edit_hope_team':check_admin_referer('edit_hope_team');require_once(plugin_dir_path(__FILE__).'includes/hope-team.php');church_admin_edit_hope_team($id);break;
/*************************************
*
*		ERRORS
*
**************************************/
		case 'church_admin_activation_log_clear':check_admin_referer('clear_error');church_admin_activation_log_clear();break;



/*************************************
*
*		MEDIA
*
**************************************/
	    case'list_speakers':if(church_admin_level_check('Sermons')){require_once(plugin_dir_path(__FILE__).'includes/sermon-podcast.php');ca_podcast_list_speakers();}break;
            case'edit_speaker':if(church_admin_level_check('Sermons')){require_once(plugin_dir_path(__FILE__).'includes/sermon-podcast.php');ca_podcast_edit_speaker($id);}break;
            case'delete_speaker':if(church_admin_level_check('Sermons')){require_once(plugin_dir_path(__FILE__).'includes/sermon-podcast.php');ca_podcast_delete_speaker($id);}break;
            case'list_sermon_series':if(church_admin_level_check('Sermons')){require_once(plugin_dir_path(__FILE__).'includes/sermon-podcast.php');ca_podcast_list_series();}break;
            case'edit_sermon_series':if(church_admin_level_check('Sermons')){check_admin_referer('edit_sermon_series');require_once(plugin_dir_path(__FILE__).'includes/sermon-podcast.php');ca_podcast_edit_series($id);}break;
            case'delete_sermon_series':if(church_admin_level_check('Sermons')){check_admin_referer('delete_sermon_series');require_once(plugin_dir_path(__FILE__).'includes/sermon-podcast.php');ca_podcast_delete_series($id);}break;
            case'list_files':if(church_admin_level_check('Sermons')){require_once(plugin_dir_path(__FILE__).'includes/sermon-podcast.php');ca_podcast_list_files();}break;
            case'edit_file':if(church_admin_level_check('Sermons')){check_admin_referer('edit_podcast_file');require_once(plugin_dir_path(__FILE__).'includes/sermon-podcast.php');ca_podcast_edit_file($id);}break;
            case'delete_file':if(church_admin_level_check('Sermons')){check_admin_referer('delete_podcast_file');require_once(plugin_dir_path(__FILE__).'includes/sermon-podcast.php');ca_podcast_delete_file($id);}break;
            case'file_delete':if(church_admin_level_check('Sermons')){check_admin_referer('file_delete');require_once(plugin_dir_path(__FILE__).'includes/sermon-podcast.php');ca_podcast_file_delete($file);}break;
            case'file_add':if(church_admin_level_check('Sermons')){check_admin_referer('file_add');require_once(plugin_dir_path(__FILE__).'includes/sermon-podcast.php');ca_podcast_file_add($file);}break;
            case'check_files':if(church_admin_level_check('Sermons')){require_once(plugin_dir_path(__FILE__).'includes/sermon-podcast.php');ca_podcast_check_files();}break;
            case'podcast':if(church_admin_level_check('Sermons')){require_once(plugin_dir_path(__FILE__).'includes/sermon-podcast.php');if(ca_podcast_xml()){echo'<p>Podcast <a href="'.CA_POD_URL.'podcast.xml">feed</a> updated</p>';}}break;
            case'podcast_settings':if(church_admin_level_check('Sermons')){check_admin_referer('podcast_settings');require_once(plugin_dir_path(__FILE__).'includes/podcast-settings.php');ca_podcast_settings();}break;
/*************************************
*
*		COMMUNICATIONS
*
**************************************/   
		case'mailchimp_sync':if(church_admin_level_check('Directory')){require_once(plugin_dir_path(__FILE__).'includes/mailchimp.php');church_admin_mailchimp_sync();}break;         
	    case 'church_admin_send_sms':if(church_admin_level_check('Bulk SMS')){require_once(plugin_dir_path(__FILE__ ).'includes/sms.php');church_admin_send_sms();}break;
	    case 'email_list':if(church_admin_level_check('Bulk Email')){require_once(plugin_dir_path(__FILE__).'includes/email.php');church_admin_email_list();}break;
		case 'delete_email':if(church_admin_level_check('Bulk Email')){require_once(plugin_dir_path(__FILE__).'includes/email.php');church_admin_delete_email($email_id);}break;
		case 'resend_email':if(church_admin_level_check('Bulk Email')){require_once(plugin_dir_path(__FILE__).'includes/email.php');church_admin_resend($email_id);}break;
		case 'resend_new':if(church_admin_level_check('Bulk Email')){require_once(plugin_dir_path(__FILE__).'includes/email.php');church_admin_resend_new($email_id);}break;
	    case 'church_admin_send_email':if(church_admin_level_check('Bulk Email')){require_once(plugin_dir_path(__FILE__).'includes/email.php');church_admin_send_email(NULL);}break;
	    case 'edit_resend':if(church_admin_level_check('Bulk Email')){require_once(plugin_dir_path(__FILE__).'includes/email.php');church_admin_send_email($email_id);}break;
	    
	    case'church_admin_people_activity':if(church_admin_level_check('Directory')){require_once(plugin_dir_path(__FILE__).'includes/people_activity.php'); echo church_admin_recent_people_activity();}break;
/*************************************
*
*		ATTENDANCE
*
**************************************/
		case 'individual_attendance':require_once(plugin_dir_path(__FILE__).'includes/individual_attendance.php');church_admin_individual_attendance();break;
	    case 'church_admin_attendance_metrics':require_once(plugin_dir_path(__FILE__).'includes/attendance.php');church_admin_attendance_metrics($service_id);break;   
		
	    case 'church_admin_attendance_list':require_once(plugin_dir_path(__FILE__).'includes/attendance.php');church_admin_attendance_list($service_id);break;    
	    case 'edit_attendance':check_admin_referer('edit_attendance');require_once(plugin_dir_path(__FILE__).'includes/attendance.php');church_admin_edit_attendance($attendance_id);break;         
	    case 'delete_attendance':check_admin_referer('delete_attendance');require_once(plugin_dir_path(__FILE__).'includes/attendance.php');church_admin_delete_attendance($attendance_id);break;         
	   
/*************************************
*
*		MINISTRIES
*
**************************************/
	    case 'edit_ministry':check_admin_referer('edit_ministry');require_once(plugin_dir_path(__FILE__).'includes/departments.php');church_admin_edit_ministry($id);break;         
	    case 'delete_ministry':check_admin_referer('delete_ministry');require_once(plugin_dir_path(__FILE__).'includes/departments.php');church_admin_delete_ministry($id);break;         
	    case 'ministry_list':check_admin_referer('ministry_list');require_once(plugin_dir_path(__FILE__).'includes/departments.php');church_admin_ministries_list();break;         
       case 'view_ministry':check_admin_referer('view_ministry');require_once(plugin_dir_path(__FILE__).'includes/departments.php');church_admin_view_ministry($id);break;
/*************************************
*
*		FOLLOW UP 
*
**************************************/
	    case 'church_admin_funnel_list':require_once(plugin_dir_path(__FILE__).'includes/funnel.php');church_admin_funnel_list();break;         
	    case 'edit_funnel':check_admin_referer('edit_funnel');require_once(plugin_dir_path(__FILE__).'includes/funnel.php');church_admin_edit_funnel($funnel_id,$people_type_id);break;
		case 'delete_funnel':check_admin_referer('delete_funnel');require_once(plugin_dir_path(__FILE__).'includes/funnel.php');church_admin_delete_funnel($funnel_id);break;
	    case 'church_admin_assign_funnel':require_once(plugin_dir_path(__FILE__).'includes/people_activity.php');church_admin_assign_funnel();break;
	    case 'church_admin_email_follow_up_activity':check_admin_referer('email_funnels');require_once(plugin_dir_path(__FILE__).'includes/people_activity.php');church_admin_email_follow_up_activity();break;
/*************************************
*
*		MEMBER TYPE
*
**************************************/
	         case 'church_admin_member_type':require_once(plugin_dir_path(__FILE__).'includes/member_type.php');church_admin_member_type();break;         
	    case 'church_admin_edit_member_type':check_admin_referer('edit_member_type');require_once(plugin_dir_path(__FILE__).'includes/member_type.php');church_admin_edit_member_type($member_type_id);break;         
	    case 'church_admin_delete_member_type':check_admin_referer('delete_member_type');require_once(plugin_dir_path(__FILE__).'includes/member_type.php');church_admin_delete_member_type($member_type_id);break;         
	   
/*************************************
*
*		FACILITIES
*
**************************************/
	    case 'church_admin_facilities':require_once(plugin_dir_path(__FILE__).'includes/facilities.php');church_admin_facilities();break;         
	    case 'edit_facility':check_admin_referer('edit_facility');require_once(plugin_dir_path(__FILE__).'includes/facilities.php');church_admin_edit_facility($facilities_id);break;         
	    case 'delete_facility':check_admin_referer('delete_facility');require_once(plugin_dir_path(__FILE__).'includes/facilities.php');church_admin_delete_facility($facilities_id);break;   
	   
/*************************************
*
*		CALENDAR
*
**************************************/
	    case 'church_admin_new_calendar':if(church_admin_level_check('Calendar')){require_once(plugin_dir_path(__FILE__).'includes/calendar.php');church_admin_new_calendar(time(),$facilities_id);}break;
		case 'church_admin_new_edit_calendar':if(church_admin_level_check('Calendar'))
		{
			require_once(plugin_dir_path(__FILE__).'includes/calendar.php');
			
			if(substr($id,0,4)=='item'){church_admin_event_edit(substr($id,4),NULL,$edit_type,NULL,$facilities_id);}
			else{church_admin_event_edit(NULL,NULL,NULL,$id,$facilities_id);}
		}
		break;
		case 'church_admin_calendar_list':if(church_admin_level_check('Calendar')){require_once(plugin_dir_path(__FILE__).'includes/calendar.php');church_admin_calendar();}break;         
	    
	    case 'church_admin_add_category':check_admin_referer('add_category');if(church_admin_level_check('Calendar')){require_once(plugin_dir_path(__FILE__).'includes/calendar.php');church_admin_edit_category(NULL,NULL);}break;         
	    
		case 'church_admin_edit_category':check_admin_referer('edit_category');if(church_admin_level_check('Calendar')){require_once(plugin_dir_path(__FILE__).'includes/calendar.php');church_admin_edit_category($id,NULL);}break;
	    
		case 'church_admin_delete_category':check_admin_referer('delete_category');if(church_admin_level_check('Calendar')){require_once(plugin_dir_path(__FILE__).'includes/calendar.php');church_admin_delete_category($id);}break;
	    
		case 'church_admin_single_event_delete':check_admin_referer('single_event_delete');if(church_admin_level_check('Calendar')){require_once(plugin_dir_path(__FILE__).'includes/calendar.php');church_admin_single_event_delete($date_id,$event_id); }break;
	    
		case 'church_admin_series_event_delete':check_admin_referer('series_event_delete');if(church_admin_level_check('Calendar')){require_once(plugin_dir_path(__FILE__).'includes/calendar.php');church_admin_series_event_delete($event_id);}break;     
	    
		case 'church_admin_category_list':if(church_admin_level_check('Calendar'));{require_once(plugin_dir_path(__FILE__).'includes/calendar.php');church_admin_category_list();}break;    
	    
		case 'church_admin_series_event_edit':check_admin_referer('series_event_edit');if(church_admin_level_check('Calendar')){require_once(plugin_dir_path(__FILE__).'includes/calendar.php');church_admin_event_edit($date_id,$event_id,'series',NULL,NULL);}break;
	    
		case 'church_admin_single_event_edit':check_admin_referer('single_event_edit');if(church_admin_level_check('Calendar')){require_once(plugin_dir_path(__FILE__).'includes/calendar.php');church_admin_event_edit($date_id,$event_id,'single',NULL,NULL);}break;
	    
		case 'church_admin_add_calendar':if(church_admin_level_check('Calendar')){require_once(plugin_dir_path(__FILE__).'includes/calendar.php');church_admin_event_edit(NULL,NULL,NULL,NULL,NULL);}break;
		
/*************************************
*
*		DIRECTORY
*
**************************************/
	    case 'view_person':if(church_admin_level_check('Directory')){require_once(plugin_dir_path(__FILE__).'includes/directory.php');church_admin_view_person($people_id);}break;
	    case 'church_admin_move_person':if(church_admin_level_check('Directory')){require_once(plugin_dir_path(__FILE__).'includes/directory.php');church_admin_move_person($people_id);}break;
	    case 'church_admin_address_list': if(church_admin_level_check('Directory')){require_once(plugin_dir_path(__FILE__).'includes/directory.php');church_admin_address_list($member_type_id);}else{echo"<p>You don't have permission to do that";}break;
	    case 'create_users':if(church_admin_level_check('Directory')){require_once(plugin_dir_path(__FILE__).'includes/directory.php');church_admin_users();}else{echo'<p>'.__('You do not have permission to do that','church-admin').'</p>';}break;
	    case 'church_admin_create_user':check_admin_referer('create_user');if(church_admin_level_check('Directory')){require_once(plugin_dir_path(__FILE__).'includes/directory.php');church_admin_create_user($people_id,$household_id);}break;      
	    case 'church_admin_migrate_users':check_admin_referer('migrate_users');if(church_admin_level_check('Directory')){require_once(plugin_dir_path(__FILE__).'includes/directory.php');church_admin_migrate_users();}break;
	    case 'display_household':if(church_admin_level_check('Directory')||$self_edit){require_once(plugin_dir_path(__FILE__).'includes/directory.php');church_admin_display_household($household_id);}else{echo'<p>'.__('You do not have permission to do that','church-admin').'</p>';}break;
		case 'church_admin_new_household':if(church_admin_level_check('Directory')||$self_edit){require_once(plugin_dir_path(__FILE__).'includes/directory.php');church_admin_new_household();}else{echo'<p>'.__('You do not have permission to do that','church-admin').'</p>';}break;
	    case 'edit_household':if(church_admin_level_check('Directory')||$self_edit){require_once(plugin_dir_path(__FILE__).'includes/directory.php');church_admin_edit_household($household_id);}else{echo'<p>'.__('You do not have permission to do that','church-admin').'</p>';}break;
	    case 'delete_household':check_admin_referer('delete_household');if(church_admin_level_check('Directory')){require_once(plugin_dir_path(__FILE__).'includes/directory.php');church_admin_delete_household($household_id);}break;
	    case 'edit_people':if(church_admin_level_check('Directory')||$self_edit){require_once(plugin_dir_path(__FILE__).'includes/directory.php');church_admin_edit_people($people_id,$household_id);}else{echo'<p>'.__('You do not have permission to do that','church-admin').'</p>';}break;
	    case 'delete_people':if(church_admin_level_check('Directory')||$self_edit){require_once(plugin_dir_path(__FILE__).'includes/directory.php');church_admin_delete_people($people_id,$household_id);}else{echo'<p>'.__('You do not have permission to do that','church-admin').'</p>';}break;
	    case 'church_admin_search':if(wp_verify_nonce('ca_search_nonce','ca_search_nonce')){require_once(plugin_dir_path(__FILE__).'includes/directory.php');church_admin_search($_POST['ca_search']);}break;
		case'church_admin_recent_visitors': require_once(plugin_dir_path(__FILE__).'includes/recent.php');echo church_admin_recent_visitors($member_type_id);break;
	 

/*************************************
*
*		ROTA
*
**************************************/
		case	'church_admin_add_dates':if(church_admin_level_check('Rota')){require_once(plugin_dir_path(__FILE__).'includes/rota.new.php');church_admin_rota_add_three_months($service_id);}break;
	    case 'church_admin_rota_list':if(church_admin_level_check('Rota')){require_once(plugin_dir_path(__FILE__).'includes/rota.new.php');church_admin_rota_list($service_id);}break;
	    case 'rota_list':if(church_admin_level_check('Rota')){require_once(plugin_dir_path(__FILE__).'includes/rota.new.php');church_admin_rota_list($service_id);}break;
	    case 'edit_rota': 	check_admin_referer('edit_rota');
	    		if(church_admin_level_check('Rota'))
	    		{
	    			require_once(plugin_dir_path(__FILE__).'includes/rota.new.php');
	    			church_admin_edit_rota($rota_date,$mtg_type,$service_id); 
	    		} 
	    break;
	    case 'delete_rota': check_admin_referer('delete_rota');
	    		if(church_admin_level_check('Rota'))
	    		{
	    			require_once(plugin_dir_path(__FILE__).'includes/rota.new.php');
	    			church_admin_delete_rota($rota_date,$mtg_type,$_GET['service_id']);
	    		}
	    break;
	    case 'email_rota':if(church_admin_level_check('Rota')){require_once(plugin_dir_path(__FILE__).'includes/rota.new.php');church_admin_email_rota($service_id,$rota_date);}break;
	    case 'auto_email_test':church_admin_auto_email_rota($service_id);break;
/*************************************
*
*		ROTA SETTINGS
*
**************************************/
	    case 'church_admin_rota_settings_list':if(church_admin_level_check('Rota')){require_once(plugin_dir_path(__FILE__).'includes/rota_settings.php');church_admin_rota_settings_list();}break;
	    case 'church_admin_edit_rota_settings':check_admin_referer('edit_rota_settings');if(church_admin_level_check('Rota')){require_once(plugin_dir_path(__FILE__).'includes/rota_settings.php');church_admin_edit_rota_settings($id);}break;
	    case 'church_admin_delete_rota_settings':check_admin_referer('delete_rota_settings');if(church_admin_level_check('Rota')){require_once(plugin_dir_path(__FILE__).'includes/rota_settings.php');church_admin_delete_rota_settings($id);}break;
	    case 'test-cron-rota':church_admin_auto_email_rota();break;
	    case 'sms-rota':if(church_admin_level_check('Rota')){require_once(plugin_dir_path(__FILE__).'includes/rota.new.php');church_admin_sms_rota($service_id);}break;
/*************************************
*
*		VISITOR - deprecated
*
**************************************/
	    case 'church_admin_add_visitor':check_admin_referer('add_visitor');if(church_admin_level_check('Visitor')){require_once(plugin_dir_path(__FILE__).'includes/visitor.php'); church_admin_add_visitor();} break;
	    case 'church_admin_edit_visitor':check_admin_referer('edit_visitor');if(church_admin_level_check('Visitor')){church_admin_edit_visitor($id);}break;
	    case 'church_admin_delete_visitor':check_admin_referer('delete_visitor');if(church_admin_level_check('Visitor')){church_admin_delete_visitor($id);} break;
	    case 'church_admin_move_visitor':check_admin_referer('move_visitor');if(church_admin_level_check('Visitor')){church_admin_move_visitor($id);}break;
/*************************************
*
*		SMALL GROUPS
*
**************************************/
		case'remove_from_smallgroup':
			check_admin_referer('remove');
			require_once(plugin_dir_path(__FILE__).'includes/small_groups.php');  
			church_admin_remove_from_smallgroup($people_id,$smallgroup_id);
		break;
		case'whosin':check_admin_referer('whosin');if(church_admin_level_check('Small Groups')){require_once(plugin_dir_path(__FILE__).'includes/small_groups.php'); echo church_admin_whosin($id);}break;
	    case  'edit_small_group':check_admin_referer('edit_small_group');if(church_admin_level_check('Small Groups')){require_once(plugin_dir_path(__FILE__).'includes/small_groups.php'); echo church_admin_edit_small_group($id);}break;
	    case  'delete_small_group':check_admin_referer('delete_small_group');if(church_admin_level_check('Small Groups')){require_once(plugin_dir_path(__FILE__).'includes/small_groups.php'); echo church_admin_delete_small_group($id);}break;
	    case 'church_admin_small_groups':if(church_admin_level_check('Small Groups')){require_once(plugin_dir_path(__FILE__).'includes/small_groups.php'); echo church_admin_small_groups();}break;
/*************************************
*
*		SERVICES
*
**************************************/
	    case  'edit_service':check_admin_referer('edit_service');if(church_admin_level_check('Service')){require_once(plugin_dir_path(__FILE__).'includes/services.php');  church_admin_edit_service($id);}break;
	    case  'delete_service':check_admin_referer('delete_service');if(church_admin_level_check('Service')){require_once(plugin_dir_path(__FILE__).'includes/services.php'); church_admin_delete_service($id);}break;
	    case 'service_list':if(church_admin_level_check('Service')){require_once(plugin_dir_path(__FILE__).'includes/services.php'); church_admin_service_list();}break;
	    case 'delete_site':if(church_admin_level_check('Service')){require_once(plugin_dir_path(__FILE__).'includes/sites.php'); church_admin_delete_site($site_id);}break;
	    case 'edit_site':if(church_admin_level_check('Service')){require_once(plugin_dir_path(__FILE__).'includes/sites.php'); church_admin_edit_site($site_id);}break;
/*************************************
*
*		SETTINGS
*
**************************************/

		case'permissions':require_once(plugin_dir_path(__FILE__).'includes/permissions.php');church_admin_permissions();break;
		case'roles':require_once(plugin_dir_path(__FILE__).'includes/settings.php');church_admin_roles();church_admin_settings_menu();break;
	    case 'church_admin_settings':if(current_user_can('manage_options')){require_once(plugin_dir_path(__FILE__).'includes/settings.php');church_admin_general_settings();}break;    
	    case'edit_people_type':require_once(plugin_dir_path(__FILE__).'includes/settings.php');echo church_admin_edit_people_type($ID);echo church_admin_people_types_list();break;
	    case'delete_people_type':require_once(plugin_dir_path(__FILE__).'includes/settings.php');echo church_admin_delete_people_type($ID);echo church_admin_people_types_list();break;
/*************************************
*
*		DEFAULT
*
**************************************/
	   default:if(church_admin_level_check('Directory')){church_admin_people_main();}else{echo'<p>'.__("You don't have permissions for this page",'church-admin').'</p>';}break;
	   
	}
	
    }else if(church_admin_level_check('Directory')){church_admin_people_main();}else{echo'<p>'.__("You don't have permissions for this page",'church-admin').'</p>';}
   echo'</div><!-- .wrap -->';
}

function church_admin_shortcode($atts, $content = null) 
{
	//sort out true false issue where it gets evaluated as a string
   	foreach($atts AS $key=>$value)
   	{
   		if($value==='FALSE'||$value==='false')$atts[$key]=0;
   		if($value==='TRUE'||$value==='true')$atts[$key]=1;	
   	}
   	extract(shortcode_atts(array('height'=>500,'width'=>900,"pdf_font_resize"=>TRUE,"updateable"=>1,"restricted"=>0,"loggedin"=>0,"type" => 'address-list','people_types'=>'all','site_id'=>0,'days'=>30,'year'=>date('Y'),'service_id'=>1,'photo'=>NULL,'category'=>NULL,'weeks'=>4,'ministry_id'=>NULL,'member_type_id'=>1,'kids'=>1,'map'=>NULL,'series_id'=>NULL,'speaker_id'=>NULL,'file_id'=>NULL,'api_key'=>NULL,'facilities_id'=>NULL), $atts));
    church_admin_posts_logout();
    $out='';
   	
    global $wpdb;

    global $wp_query;
    if(empty($loggedin)||is_user_logged_in())
    {
    
    	$upload_dir = wp_upload_dir();
		$path=$upload_dir['basedir'].'/church-admin-cache/';
    	//look to see if church directory is o/p on a password protected page	
    	$pageinfo=get_page($wp_query->post->ID);	
    	//grab page info
    	//check to see if on a password protected page
    	if(($pageinfo->post_password!='')&&isset( $_COOKIE['wp-postpass_' . COOKIEHASH] )) 
    	{
			$text = __('Log out of password protected posts','church-admin');
		//text for link
		$link = site_url().'?church_admin_logout=posts_logout';
		$out.= '<p><a href="' . wp_nonce_url($link, 'posts logout') .'">' . $text . '</a></p>';
		//output logoutlink
    	}
    	//end of password protected page
   
    	//grab content
    	switch($type)
    	{
    	
			case 'sessions':require_once(plugin_dir_path(__FILE__).'includes/sessions.php');$out.=church_admin_sessions(NULL,NULL);break;
			case 'recent':require_once(plugin_dir_path(__FILE__).'includes/recent.php');$out.=church_admin_recent_visitors($member_type_id=1);break;
			case 'podcast':
				require_once(plugin_dir_path(__FILE__).'display/sermon-podcast.php');
				if(!empty($_GET['speaker_name'])){$speaker_name=urldecode($_GET['speaker_name']);}else{$speaker_name=NULL;}
				if(!empty($_GET['series_id'])){$series_id=urldecode($_GET['series_id']);}
	    		$out.=ca_podcast_display($series_id,$file_id,$speaker_name);
				$out = apply_filters ( 'the_content', $out );
				break;    
        	case 'calendar':
			if(empty($facilities_id))
			{
				$out.='<table><tr><td>'.__('Year Planner pdfs','church-admin').' </td><td>  <form name="guideform" action="" method="get"><select name="guidelinks" onchange="window.location=document.guideform.guidelinks.options[document.guideform.guidelinks.selectedIndex].value"> <option selected="selected" value="">-- '.__('Choose a pdf','church-admin').' --</option>';
				for($x=0;$x<5;$x++)
				{
					$y=date('Y')+$x;
		
					$out.='<option value="'.home_url().'/?download=yearplanner&amp;yearplanner='.wp_create_nonce('yearplanner').'&amp;year='.$y.'">'.$y.__('Year Planner','church-admin').'</option>';
				}
				$out.='</select></form></td></tr></table>';
			}
            

            require_once(plugin_dir_path(__FILE__).'display/calendar.php');
            $out.=church_admin_display_calendar();
        	break;
        
        	case 'names':require_once(plugin_dir_path(__FILE__).'/display/names.php');$out.=church_admin_names($member_type_id,$people_types);break;
        	case 'calendar-list':
            	require_once(plugin_dir_path(__FILE__).'/display/calendar-list.php');
        	break;
        	case 'directory':
       		 	require_once(plugin_dir_path(__FILE__).'display/directory.php');
            	$out.=church_admin_frontend_people($member_type_id,$map,$photo,$api_key,$kids,$site_id);
        	break;
        	case 'address-list':
	   			$out.='<p><a href="'.home_url().'/?download=addresslist&amp;addresslist='.wp_create_nonce('member'.$member_type_id ).'&amp;member_type_id='.$member_type_id.'">'.__('PDF version','church-admin').'</a></p>';
            	require_once(plugin_dir_path(__FILE__).'display/address-list.php');
            	$out.=church_admin_frontend_directory($member_type_id,$map,$photo,$api_key,$kids,$site_id,$updateable);
        	break;
        	case 'small-groups-list':
            	require_once(plugin_dir_path(__FILE__).'/display/small-group-list.php');
            	$out.= church_admin_small_group_list($map);
        	break;
			case 'small-groups':
            	require_once(plugin_dir_path(__FILE__).'/display/small-groups.php');
            	$out.=church_admin_frontend_small_groups($member_type_id,$restricted);
            break;
        	case 'ministries':
            	require_once(plugin_dir_path(__FILE__).'/display/ministries.php');
            	$out.=church_admin_frontend_ministries($ministry_id,$member_type_id);
            break;
            case 'my_rota':
            	require_once(plugin_dir_path(__FILE__).'/display/rota.php');
            	$out.=church_admin_my_rota();
        	break;
			case 'rota':
            	require_once(plugin_dir_path(__FILE__).'/display/rota.php');
            	if(!empty($_GET['date'])){$date=$_GET['date'];}else{$date=date('Y-m-d');}
            	$out.=church_admin_front_end_rota($service_id,4,$pdf_font_resize,$date);
        	break;
        	case 'rolling-average':
        	case 'weekly-attendance':
        	case 'monthly-attendance':
        	case 'rolling-average-attendance':
			case 'graph':
				if(empty($width))$width=900;
				if(empty($height))$height=500;
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
				require_once(plugin_dir_path(__FILE__).'display/graph.php');
				$out.=church_admin_graph($graphtype,$service_id,$start,$end,$width,$height);
			break;
			case 'birthdays':require_once(plugin_dir_path(__FILE__).'includes/birthdays.php');$out.=church_admin_frontend_birthdays($member_type_id, $days);break;
			default:
				$out.='<p><a href="'.home_url().'/?download=addresslist&amp;addresslist='.wp_create_nonce('member'.$member_type_id ).'&amp;member_type_id='.$member_type_id.'">'.__('PDF version','church-admin').'</a></p>';
        	    require_once(plugin_dir_path(__FILE__).'display/address-list.php');
         	   $out.=church_admin_frontend_directory($member_type_id,$map,$photo,$api_key,$kids,$site_id,$updateable);
       		break;
    	}
    }
    else //login required
    {
    	$out.=wp_login_form();
    }
//output content instead of shortcode!
return $out; 
}

add_shortcode('church_admin_unsubscribe','church_admin_unsubscribe');
function church_admin_unsubscribe()
{
	global $wpdb;
	$wpdb->query('UPDATE '.CA_PEO_TBL.' SET email_send=0 WHERE md5(people_id)="'.esc_sql($_GET['ca-unsub']).'"');
	$out='<p>'.__('You have been unsubscribed from emails','church-admin').'</p>';
	return $out;
}
add_shortcode('church_admin_recent','church_admin_recent');
function church_admin_recent($atts, $content = null)
{
    extract(shortcode_atts(array('month'=>1), $atts));
    require_once(plugin_dir_path(__FILE__).'includes/recent.php');church_admin_recent_display($month);
}
add_shortcode("church_admin", "church_admin_shortcode");
add_shortcode("church_admin_map","church_admin_map");
function church_admin_map($atts, $content = null) 
{
	$out='';
    extract(shortcode_atts(array('zoom'=>13,'member_type_id'=>1,'small_group'=>1,'unattached'=>0), $atts));
    global $wpdb;

    $service=$wpdb->get_row('SELECT AVG(lat) AS lat,AVG(lng) AS lng FROM '.CA_SIT_TBL);
    $out.='<div class="church-map"><script type="text/javascript">var xml_url="'.site_url().'/?download=address-xml&member_type_id='.intval($member_type_id).'&small_group='.esc_html($small_group).'&unattached='.esc_html($unattached).'&address-xml='.wp_create_nonce('address-xml').'";';
    $out.=' var lat='.esc_html($service->lat).';';
    $out.=' var lng='.esc_html($service->lng).';';
    
    $out.='jQuery(document).ready(function(){
    load(lat,lng,xml_url);});</script><div id="map"></div>';
    if(empty($small_group)){$out.='<div id="groups" style="display:none"></div>';}else{$out.='<div id="groups" ></div>';}
    $out.='</div>';
    
    return $out;
    
}
add_shortcode("church_admin_register","church_admin_register");
function church_admin_register($atts, $content = null)
{
    extract(shortcode_atts(array('email_verify'=>TRUE,'admin_email'=>TRUE,'member_type_id'=>1), $atts));
    require_once(plugin_dir_path(__FILE__).'includes/front_end_register.php');
    $out=church_admin_front_end_register();
    return $out;
}

function church_admin_posts_logout() 
{
    if ( isset( $_GET['church_admin_logout'] ) && ( 'posts_logout' == $_GET['church_admin_logout'] ) &&check_admin_referer( 'posts logout' )) 
    {
	setcookie( 'wp-postpass_' . COOKIEHASH, ' ', time() - 31536000, COOKIEPATH );
	wp_redirect( wp_get_referer() );
	die();
    }
}


add_action( 'init', 'church_admin_posts_logout' );

//end of logout functions

function church_admin_calendar_widget($args)
{
    global $wpdb;

    extract($args);
    $options=get_option('church_admin_widget');
    $title=$options['title'];
   
    echo $before_widget;
    if ( $title )echo $before_title . $title . $after_title;
   
    echo church_admin_calendar_widget_output($options['events'],$options['postit'],$title);
    echo $after_widget;
}
function church_admin_widget_init()
{
    wp_register_sidebar_widget('Church-Admin-Calendar','Church Admin Calendar','church_admin_calendar_widget');
    require_once(plugin_dir_path(__FILE__).'includes/calendar_widget.php');
    wp_register_widget_control('Church-Admin-Calendar','Church Admin Calendar','church_admin_widget_control');
}
add_action('init','church_admin_widget_init');

function church_admin_birthday_widget($args)
{
    global $wpdb;

    extract($args);
	$options=get_option('church_admin_birthday_widget');
	
    $title=$options['title'];
	if(empty($options['member_type_id']))$options['member_type_id']=1;
	if(empty($options['days']))$options['days']=14;
	$out=church_admin_frontend_birthdays($options['member_type_id'], $options['days']);
   if(!empty($out))
   {
		echo $before_widget;
		if (!empty( $options['title']) )echo $before_title . $options['title'] . $after_title;
		require_once(plugin_dir_path(__FILE__).'includes/birthdays.php');
		echo $out;
		echo $after_widget;
	}
}
function church_admin_birthday_widget_init()
{
    wp_register_sidebar_widget('Church Admin Birthdays','Church Admin Birthdays','church_admin_birthday_widget');
    require_once(plugin_dir_path(__FILE__).'includes/birthdays.php');
    wp_register_widget_control('Church Admin Birthdays','Church Admin Birthdays','church_admin_birthday_widget_control');
}
add_action('init','church_admin_birthday_widget_init');
function church_admin_sermons_widget($args)
{
    global $wpdb;
	church_admin_latest_sermons_scripts();

    extract($args);
    $options=get_option('church_admin_latest_sermons_widget');
    $title=$options['title'];
	$limit=$options['sermons'];
    echo $before_widget;
    if ( $title )echo $before_title . esc_html($title) . $after_title;
	require_once(plugin_dir_path(__FILE__).'includes/sermon-podcast.php');
    echo church_admin_latest_sermons_widget_output($limit,$title);
    echo $after_widget;
}
function church_admin_sermons_widget_init()
{
    wp_register_sidebar_widget('Church-Admin-Latest-Sermons','Church Admin Latest Sermons','church_admin_sermons_widget');
    require_once(plugin_dir_path(__FILE__).'includes/sermon-podcast.php');
    wp_register_widget_control('Church-Admin-Latest-Sermons','Church Admin Latest Sermons','church_admin_latest_sermons_widget_control');

	
}
function church_admin_latest_sermons_scripts()
{
	$ajax_nonce = wp_create_nonce("church_admin_mp3_play");		
	wp_enqueue_script('ca_podcast_audio',plugins_url('church-admin/includes/audio.min.js',dirname(__FILE__)),'',NULL);
	wp_enqueue_script('ca_podcast_audio_use',plugins_url('church-admin/includes/audio.use.js',dirname(__FILE__)),'',NULL);
	wp_localize_script( 'ca_podcast_audio_use', 'ChurchAdminAjax', array('security'=>$ajax_nonce, 'ajaxurl' => admin_url( 'admin-ajax.php' ) ) );

}
add_action('init','church_admin_sermons_widget_init');
function church_admin_download($file)
{
	$member_type_id=NULL;
	if(!empty($_GET['member_type_id']))$member_type_id=$_GET['member_type_id'];
	if(!empty($_GET['date'])){$date=$_GET['date'];}else{$date=date('Y-m-d');}
	if(!empty($_GET['pdf_font_resize'])){$resize=$_GET['pdf_font_resize'];}else{$resize=FALSE;}
	if(!empty($_GET['service_id'])){$service_id=intval($_GET['service_id']);}else{$service_id=1;}
	if(!empty($_GET['rota_id'])){$rota_id=$_GET['rota_id'];}else{$rota_id=NULL;}
    switch($file)
    {
		case'kidswork_pdf':require_once(plugin_dir_path(__FILE__).'includes/pdf_creator.php');church_admin_kidswork_pdf($member_type_id);break;
		//Rotas		
		case'new_horizontal_rota_pdf':require_once(plugin_dir_path(__FILE__).'includes/pdf_creator.php');church_admin_new_horiz_pdf($service_id);break;						
        case 'rotacsv':if(wp_verify_nonce($_GET['_wpnonce'],'rotacsv')){require_once(plugin_dir_path(__FILE__).'includes/rota.new.php');church_admin_rota_csv($service_id); }else{echo'<p>You can only download if coming from a valid link</p>';}break;
		case'horizontal_rota_pdf':require_once(plugin_dir_path(__FILE__).'includes/pdf_creator.php');church_admin_new_horiz_pdf($service_id,$rota_id);break;
		case'rota':if(wp_verify_nonce($_GET['_wpnonce'],'rota')){require_once(plugin_dir_path(__FILE__).'includes/pdf_creator.php');church_admin_new_rota_pdf($service_id,$resize,$date);}else{echo'<p>You can only download if coming from a valid link</p>';}break;
		
		case 'hope_team_pdf':require_once(plugin_dir_path(__FILE__).'includes/pdf_creator.php');church_admin_hope_team_pdf();break;
					
		case'ministries_pdf': 
			if(wp_verify_nonce($_GET['_wpnonce'],'ministries_pdf')){
				require_once(plugin_dir_path(__FILE__).'includes/pdf_creator.php');
				church_admin_ministry_pdf();
			}else{
				echo'<p>You can only download if coming from a valid link</p>';
			}
		break;
		case 'people-csv':
				if(wp_verify_nonce($_GET['people-csv'],'people-csv'))
				{
					require_once(plugin_dir_path(__FILE__).'includes/csv.php');
					church_admin_people_csv();
				}
				else
				{
					echo'<p>You can only download if coming from a valid link</p>';
				}
		break;
		case 'small-group-xml':
				if(wp_verify_nonce($_GET['small-group-xml'],'small-group-xml'))
				{
					require_once(plugin_dir_path(__FILE__).'includes/pdf_creator.php');
					church_admin_small_group_xml();
				}else{echo'<p>You can only download if coming from a valid link</p>';}
		break;
		case 'address-xml':
			require_once(plugin_dir_path(__FILE__).'includes/pdf_creator.php');
			church_admin_address_xml($_GET['member_type_id'],$_GET['small_group']);
		break;
        case'cron-instructions':if(wp_verify_nonce($_GET['cron-instructions'],'cron-instructions')){require_once(plugin_dir_path(__FILE__).'includes/pdf_creator.php');church_admin_cron_pdf();}else{echo'<p>You can only download if coming from a valid link</p>';}break;
		
        case'yearplanner':if(wp_verify_nonce($_GET['yearplanner'],'yearplanner')){require_once(plugin_dir_path(__FILE__).'includes/pdf_creator.php');church_admin_year_planner_pdf($_GET['year']);}else{echo'<p>You can only download if coming from a valid link</p>';}break;
		case'smallgroup':if(wp_verify_nonce($_GET['smallgroup'],'smallgroup')){require_once(plugin_dir_path(__FILE__).'includes/pdf_creator.php');church_admin_smallgroup_pdf($_GET['member_type_id'],$_GET['people_type_id']);}else{echo'<p>You can only download if coming from a valid link</p>';}break;
		
		case'addresslist':
			if(wp_verify_nonce($_GET['addresslist'],'member'.$_GET['member_type_id']))
			{
				require_once(plugin_dir_path(__FILE__).'includes/pdf_creator.php');
				church_admin_address_pdf($_GET['member_type_id']);
			}else{echo'<p>You can only download if coming from a valid link</p>';}
		break;
		
		case'vcf':
			if(wp_verify_nonce($_GET['vcf'],$_GET['id']))
			{
				require_once(plugin_dir_path(__FILE__).'includes/pdf_creator.php');
				ca_vcard($_GET['id']);
			}else{echo'<p>You can only download if coming from a valid link</p>';}
		break;
				
								case'mailinglabel':if(wp_verify_nonce($_GET['mailinglabel'],'mailinglabel')){require_once(plugin_dir_path(__FILE__).'includes/pdf_creator.php');church_admin_label_pdf($_GET['member_type_id']);}else{echo'<p>You can only download if coming from a valid link</p>';}break;

        
    }
}
function church_admin_delete_backup()
{
	$filename=get_option('church_admin_backup_filename');
	$upload_dir = wp_upload_dir();
	$path=$upload_dir['basedir']; 
	if($filename&& file_exists($path.'/church-admin-cache/'.$filename))unlink($path.'/church-admin-cache/'.$filename);
	update_option('church_admin_backup_filename',"");
}
function church_admin_backup()
{
    global $church_admin_version,$wpdb;
    $content='';
    if ($wpdb->get_var('SHOW TABLES LIKE "'.CA_ATT_TBL.'"') == CA_ATT_TBL)$content.=church_admin_datadump (CA_ATT_TBL);
    if ($wpdb->get_var('SHOW TABLES LIKE "'.CA_BIB_TBL.'"') == CA_BIB_TBL)$content.=church_admin_datadump (CA_BIB_TBL);
     if ($wpdb->get_var('SHOW TABLES LIKE "'.CA_BRP_TBL.'"') == CA_BRP_TBL)$content.=church_admin_datadump (CA_BRP_TBL);
    if ($wpdb->get_var('SHOW TABLES LIKE "'.CA_CAT_TBL.'"') == CA_CAT_TBL)$content.=church_admin_datadump (CA_CAT_TBL);
     if ($wpdb->get_var('SHOW TABLES LIKE "'.CA_CLA_TBL.'"') == CA_CAT_TBL)$content.=church_admin_datadump (CA_CLA_TBL);
     if ($wpdb->get_var('SHOW TABLES LIKE "'.CA_COM_TBL.'"') == CA_COM_TBL)$content.=church_admin_datadump (CA_COM_TBL);
     if ($wpdb->get_var('SHOW TABLES LIKE "'.CA_DATE_TBL.'"') == CA_DATE_TBL)$content.=church_admin_datadump (CA_DATE_TBL);
     if ($wpdb->get_var('SHOW TABLES LIKE "'.CA_EBU_TBL.'"') == CA_FP_TBL)$content.=church_admin_datadump (CA_EBU_TBL);
	if ($wpdb->get_var('SHOW TABLES LIKE "'.CA_EMA_TBL.'"') == CA_FP_TBL)$content.=church_admin_datadump (CA_EMA_TBL);
    if ($wpdb->get_var('SHOW TABLES LIKE "'.CA_FIL_TBL.'"') == CA_FIL_TBL)$content.=church_admin_datadump (CA_FIL_TBL);
    if ($wpdb->get_var('SHOW TABLES LIKE "'.CA_FAC_TBL.'"') == CA_FAC_TBL)$content.=church_admin_datadump (CA_FAC_TBL);
	if ($wpdb->get_var('SHOW TABLES LIKE "'.CA_FP_TBL.'"') == CA_FP_TBL)$content.=church_admin_datadump (CA_FP_TBL);
    if ($wpdb->get_var('SHOW TABLES LIKE "'.CA_FUN_TBL.'"') == CA_FUN_TBL)$content.=church_admin_datadump (CA_FUN_TBL);
    if ($wpdb->get_var('SHOW TABLES LIKE "'.CA_HOU_TBL.'"') == CA_HOU_TBL)$content.=church_admin_datadump (CA_HOU_TBL);
    if ($wpdb->get_var('SHOW TABLES LIKE "'.CA_HOP_TBL.'"') == CA_HOU_TBL)$content.=church_admin_datadump (CA_HOP_TBL);
    if ($wpdb->get_var('SHOW TABLES LIKE "'.CA_IND_TBL.'"') == CA_HOU_TBL)$content.=church_admin_datadump (CA_IND_TBL);
    if ($wpdb->get_var('SHOW TABLES LIKE "'.CA_KID_TBL.'"') == CA_HOU_TBL)$content.=church_admin_datadump (CA_KID_TBL);
    if ($wpdb->get_var('SHOW TABLES LIKE "'.CA_MET_TBL.'"') == CA_MET_TBL)$content.=church_admin_datadump (CA_MET_TBL);
    if ($wpdb->get_var('SHOW TABLES LIKE "'.CA_MTY_TBL.'"') == CA_MTY_TBL)$content.=church_admin_datadump (CA_MTY_TBL);
    if ($wpdb->get_var('SHOW TABLES LIKE "'.CA_PEO_TBL.'"') == CA_PEO_TBL)$content.=church_admin_datadump (CA_PEO_TBL);
    if ($wpdb->get_var('SHOW TABLES LIKE "'.CA_ROT_TBL.'"') == CA_ROT_TBL)$content.=church_admin_datadump (CA_ROT_TBL);
     if ($wpdb->get_var('SHOW TABLES LIKE "'.CA_ROTA_TBL.'"') == CA_ROTA_TBL)$content.=church_admin_datadump (CA_ROTA_TBL);
    if ($wpdb->get_var('SHOW TABLES LIKE "'.CA_RST_TBL.'"') == CA_RST_TBL)$content.=church_admin_datadump (CA_RST_TBL);
    if ($wpdb->get_var('SHOW TABLES LIKE "'.CA_SERM_TBL.'"') == CA_SERM_TBL)$content.=church_admin_datadump (CA_SERM_TBL);
    if ($wpdb->get_var('SHOW TABLES LIKE "'.CA_SER_TBL.'"') == CA_SER_TBL)$content.=church_admin_datadump (CA_SER_TBL);
    if ($wpdb->get_var('SHOW TABLES LIKE "'.CA_SERM_TBL.'"') == CA_SER_TBL)$content.=church_admin_datadump (CA_SERM_TBL);
    if ($wpdb->get_var('SHOW TABLES LIKE "'.CA_SMG_TBL.'"') == CA_SMG_TBL)$content.=church_admin_datadump (CA_SMG_TBL);
    if ($wpdb->get_var('SHOW TABLES LIKE "'.CA_SIT_TBL.'"') == CA_SIT_TBL)$content.=church_admin_datadump (CA_SIT_TBL);
    if ($wpdb->get_var('SHOW TABLES LIKE "'.CA_SES_TBL.'"') == CA_SES_TBL)$content.=church_admin_datadump (CA_SES_TBL);
    if ($wpdb->get_var('SHOW TABLES LIKE "'.CA_SMET_TBL.'"') == CA_SMET_TBL)$content.=church_admin_datadump (CA_SMET_TBL);
    if ($wpdb->get_var('SHOW TABLES LIKE "'.CA_MIN_TBL.'"') == CA_MIN_TBL)$content.=church_admin_datadump (CA_MIN_TBL);
     
    if(defined(OLD_CHURCH_ADMIN_VERSION))$content.='UPDATE '.$wpdb->prefix.'options SET option_value="'.OLD_CHURCH_ADMIN_VERSION.'" WHERE option_name="church_admin_version";'."\r\n";
    $sql='SELECT option_name, option_value FROM '.$wpdb->options.' WHERE `option_name` LIKE  "church%"';
    
    $options=$wpdb->get_results($sql);
    
    if(!empty($options))
    {
    	foreach($options AS $option)
    	{
    		$content.='DELETE FROM '.$wpdb->prefix.'options WHERE option_name="'.esc_sql($option->option_name).'";'."\r\n";
    		$content.='INSERT INTO  '.$wpdb->prefix.'options (option_name,option_value)VALUES("'.esc_sql($option->option_name).'","'.esc_sql($option->option_value).'");'."\r\n";
    	}
    }
	$length = 10;
	$randomString = substr(str_shuffle("0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ"), 0, $length);
	$filename=md5($randomString).'.sql.gz';
	update_option('church_admin_backup_filename',$filename);
	$upload_dir = wp_upload_dir();
	$path=$upload_dir['basedir']; 
    if(!empty($content))
    {
		$gzdata = gzencode($content);
		$loc=$path.'/church-admin-cache/'.$filename;
		$fp = fopen($loc, 'w');
		fwrite($fp, $gzdata);
		fclose($fp);
	}
	
}
function church_admin_datadump ($table) {

	global $wpdb;

	$sql="select * from `$table`";
	$tablequery = $wpdb->get_results($sql,ARRAY_N);
	$num_fields=$wpdb->num_rows +1;
	
	if(!empty($tablequery))
	{
	    
	    $result = "# Dump of $table \r\n";
	    $result .= "# Dump DATE : " . date("d-M-Y") ."\r\n";
	    
	    $increment = $num_fields+1;
	    //build table structure
	    $sql = "SHOW COLUMNS FROM `$table`";
	    $query=$wpdb->get_results($sql);
	    if(!empty($query))
	    {
		$result.="DROP TABLE IF EXISTS `$table`;\r\n CREATE TABLE IF NOT EXISTS `$table` (";
		foreach($query AS $row)
		{
		    $result.="`{$row->Field}` {$row->Type} ";
		    if(isset($row->NULL)){$result.=" NULL ";}else {$result.=" NOT NULL ";}
		    if($row->Key=='PRI'){$key=$row->Field;}
		    if(!empty($row->Default))
		    {
			if($row->Default=='CURRENT_TIMESTAMP'){$result.='default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP';}
			else {$result.=" default '".$row->Default."'";}
		    }
		    if(!empty($row->Extra)) $result.=' '.$row->Extra;
		    $result.=',';
		}
	    }
	    $result.="PRIMARY KEY (`{$key}`)) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=".$increment." ;\r\n";
	    $result.="-- \r\n -- Dumping data for table `$table`\r\n--\r\n";
	    //build insert for table
	    $result.="-- \r\n -- Dumping data for table `$table`\r\n--\r\n";
	
	    foreach($tablequery AS $row)
	    {
 
		$result .= "INSERT INTO `".$table."` VALUES(";
		for($j=0; $j<count($row); $j++) 
		{
		    $row[$j] = addslashes($row[$j]);
		    $row[$j] = str_replace("\n","\\n",$row[$j]);
		    if (isset($row[$j])) $result .= "'{$row[$j]}'" ; else $result .= "''";
		    if ($j<(count($row)-1)) $result .= ",";
		}   
		$result .= ");\r\n";
	    }
	    	return $result;
	}
}
 
/**
 *
 * Ajax - returns json array with people's names
 * Used by autocomplete 
 * @author  Andy Moyle
 * @param    null
 * @return   json array
 * @version  0.1
 * 
 */
 add_action('wp_ajax_church_admin_autocorrect', 'church_admin_ajax_people');
add_action('wp_ajax_nopriv_church_admin_autocorrect', 'church_admin_ajax_people');
function church_admin_ajax_people()
{
	check_ajax_referer( 'church-admin-autocomplete', 'security' );
    global $wpdb;
    $names=explode(", ", $_GET['term']);//put passed var into array
    $name=esc_sql(stripslashes(trim(end($names))));//grabs final value for search

    //$sql='SELECT CONCAT_WS(" ",first_name,prefix, last_name) AS name FROM '.CA_PEO_TBL.' WHERE CONCAT_WS(" ",first_name,prefix,last_name) REGEXP "^'.$name.'"';
   $sql='SELECT CONCAT_WS(" ",first_name,prefix, last_name) AS name FROM '.CA_PEO_TBL.' WHERE CONCAT_WS(" ",first_name,last_name) LIKE "%'.esc_sql($name).'%" OR CONCAT_WS(" ",first_name,prefix,last_name) LIKE "%'.esc_sql($name).'%" OR CONCAT_WS(" ",first_name,middle_name,prefix,last_name) LIKE "%'.esc_sql($name).'%" OR CONCAT_WS(" ",first_name,middle_name,last_name) LIKE "%'.esc_sql($name).'%" OR CONCAT_WS(" ",first_name,prefix,last_name) LIKE "%'.esc_sql($name).'%" OR  nickname LIKE "%'.esc_sql($name).'%" ';
    church_admin_debug("*********\r\n $sql");
    $result=$wpdb->get_results($sql);
    church_admin_debug("*********\r\n ".print_r($result,TRUE));
    if($result)
    {
        $people=array();
        foreach($result AS $row)
        {
            $people[]=array('name'=>$row->name);
        }
        
        //echo JSON to page  
    //$people = $_GET["callback"] . "(" . json_encode($people) . ")";  
    $response =json_encode($people);
    church_admin_debug("*********\r\n $response");
    echo $response; 
    }
    exit();
}
add_action('wp_ajax_ajax_rota_edit', 'church_admin_action_rota_edit');
add_action('wp_ajax_nopriv_ajax_rota_edit', 'church_admin_action_rota_edit');
function church_admin_action_rota_edit()
{
	
	check_ajax_referer('ajax_rota_edit','security',TRUE);
	global $wpdb;	
	$id=stripslashes($_POST['id']);
	$details=explode('~',$id);
	$sql='SELECT rota_id FROM '.CA_RST_TBL.' WHERE rota_task="'.esc_sql($details[0]).'"';
	
	$job_id=$wpdb->get_var($sql);
	
	$row=$wpdb->get_row('SELECT * FROM '.CA_ROT_TBL.' WHERE rota_id="'.esc_sql($details[1]).'"');
	$jobs=maybe_unserialize($row->rota_jobs);
	
	$jobs[$job_id]= church_admin_get_people_id($_POST['value']);
	
	$sql='UPDATE '.CA_ROT_TBL.' SET rota_jobs="'.esc_sql(maybe_serialize($jobs)).'" WHERE  rota_id="'.esc_sql($details[1]).'"';
	echo $_POST['value'];
	$wpdb->query($sql);
	die();
}
add_action('wp_ajax_ca_mp3_action', 'church_admin_action_callback');
add_action('wp_ajax_nopriv_ca_mp3_action', 'church_admin_action_callback');

function church_admin_action_callback() {
	$nonce = $_POST['data']['security'];
 	if ( ! wp_verify_nonce( $nonce, 'church_admin_mp3_play' ) )die('busted');

	global $wpdb;
	$file_id = esc_sql($_POST['data']['file_id']);
	$sql='UPDATE '.CA_FIL_TBL.' SET plays = plays+1 WHERE file_id = "'.$file_id.'"';
	$wpdb->query($sql);
	$plays=$wpdb->get_var('SELECT plays FROM '.CA_FIL_TBL.' WHERE file_id = "'.$file_id.'"');
	
	echo $plays;
	die();
} 
 function church_admin_activation_log_clear(){delete_option('church_admin_plugin_error');church_admin_front_admin();} 



// Add a new interval of a week
// See http://codex.wordpress.org/Plugin_API/Filter_Reference/cron_schedules
add_filter( 'cron_schedules', 'church_admin_add_weekly_cron_schedule' );
function church_admin_add_weekly_cron_schedule( $schedules ) {
    $schedules['weekly'] = array(
        'interval' => 604800, // 1 week in seconds
        'display'  => __( 'Once Weekly' ),
    );
 
    return $schedules;
}
if(!empty($_POST['email_rota_day']))
{
	$service_id=intval($_POST['service_id']);
	$args=array('service_id'=>intval($service_id));
	$en_rota_days=array(1=>'Monday',2=>'Tuesday',3=>'Wednesday',4=>'Thursday',5=>'Friday',6=>'Saturday',7=>'Sunday');
	$email_day=(int)$_POST['email_rota_day'];
	$message=stripslashes($_POST['auto-rota-message']);
	update_option('church_admin_auto_rota_email_message',$message);
	if($email_day==8){delete_option('church_admin_email_rota_day');wp_clear_scheduled_hook('church_admin_cron_email_rota');}
	else{
		update_option('church_admin_email_rota_day',$email_day);
		$first_run = strtotime($en_rota_days[$email_day]);
		wp_schedule_event($first_run, 'weekly','church_admin_cron_email_rota',$args);
		
	}
	
}
add_action('church_admin_cron_email_rota','church_admin_auto_email_rota');
   /**
 *
 * Cron email rota
 * 
 * @author  Andy Moyle
 * @param    $service_id
 * @return   string
 * @version  0.1
 * 
 */
function church_admin_auto_email_rota($service_id)
{
    global $wpdb;
    
  	if(empty($service_id))return FALSE;	
  	
		$days=array(1=>__('Sunday','church-admin'),2=>__('Monday','church-admin'),3=>__('Tuesday','church-admin'),4=>__('Wednesday','church-admin'),5=>__('Thursday','church-admin'),6=>__('Friday','church-admin'),7=>__('Saturday','church-admin'));
		//get required task for service_id
		$rota_tasks=$wpdb->get_results('SELECT * FROM '.CA_RST_TBL.' ORDER BY rota_order');
		$requiredRotaJobs=$rotaDates=array();
		foreach($rota_tasks AS $rota_task)
		{
			$allServiceID=maybe_unserialize($rota_task->service_id);
			if(is_array($allServiceID)&&in_array($service_id,$allServiceID))$requiredRotaJobs[$rota_task->rota_id]=$rota_task->rota_task;
		}
		
		//next service
		$sql='SELECT rota_date FROM '.CA_ROTA_TBL.' WHERE service_id="'.intval($service_id).'" AND mtg_type="service" AND rota_date>=CURDATE() GROUP BY rota_date ORDER by rota_date ASC  LIMIT 1';
		
		$rota_date=$wpdb->get_var($sql);
		//all jobs from next service
		$sql='SELECT * FROM '.CA_ROTA_TBL.' WHERE service_id="'.intval($service_id).'" AND mtg_type="service" AND rota_date="'.esc_sql($rota_date).'"';
		
		$results=$wpdb->get_results($sql);
		
		$allPeople=array();//array of all people involved in service
		$rotaTable='';
		foreach($results AS $row)
		{
			$people=church_admin_rota_people_array($row->rota_date,$row->rota_task_id,$service_id,'service');
			foreach($people AS $people_id=>$name)if(ctype_digit($people_id)&&!in_array($people_id,$allPeople))$allPeople[$people_id]=$name;
			$rotaTable.='<tr><td>'.$requiredRotaJobs[$row->rota_task_id].'</td><td>'.esc_html(implode(", ",$people)).'</td></tr>';
		
		}
		//Title
		$service=$wpdb->get_row('SELECT * FROM '.CA_SER_TBL.' WHERE service_id="'.intval($service_id).'"');
		
		$title='<h3>'.__('Rota for','church-admin').' '.esc_html($service->service_name).' '.__('on','church-admin').' '.esc_html($days[$service->service_day]).' '.__('at','church-admin').' '.esc_html($service->service_time).' '.esc_html($service->venue).'</h3>';
		$message=get_option('church_admin_auto_rota_email_message');
		$out=$title.$message.'<table>'.$rotaTable.'</table>';
		
		church_admin_debug("Cron email of rota ".date('Y-m-d h:i:s')."\r\n".$out);
		$allPeople=array_filter($allPeople);
		
		foreach($allPeople AS $ID=>$name)
		{
			$email=$wpdb->get_var('SELECT email FROM '.CA_PEO_TBL.' WHERE people_id="'.intval($ID).'"');
			if(!empty($email))
			{
				
				add_filter('wp_mail_content_type','church_admin_email_type');
				add_filter( 'wp_mail_from_name', 'church_admin_from_name');
				add_filter( 'wp_mail_from', 'church_admin_from_email');
				if(!wp_mail($email,strip_tags($title),$out)){church_admin_debug("Cron email failure\r\n".$_GLOBALS['phpmailer']->ErrorInfo);}
				remove_filter('wp_mail_content_type','church_admin_email_type');
			}
		}
		exit();
}
function church_admin_from_name( $from ) {if(!empty($_POST['from_name'])){return esc_html(stripslashes($_POST['from_name']));}else return get_option('blogname');}
function church_admin_from_email( $email ) {if(!empty($_POST['from_email'])){return esc_html(stripslashes($_POST['from_email']));}else return get_option('admin_email');}
function church_admin_debug($message)
{
	$upload_dir = wp_upload_dir();
	$debug_path=$upload_dir['basedir'].'/church-admin-cache/';
	if(file_exists($debug_path.'debug.log'))unlink($debug_path.'debug.log');
	if(!file_exists($debug_path.'debug_log.php'))
	{
		
		$text="<?php exit('God is good and you are not, because you are acting like a hacker.'); \r\n // Nothing is good! ";
		$fp = fopen($debug_path.'debug_log.php', 'w');
		fwrite($fp, $text."\r\n");
	}
	if(empty($fp))$fp = fopen($debug_path.'debug_log.php', 'a');
    fwrite($fp, $message."\r\n");
    fclose($fp);
} 

register_deactivation_hook(__FILE__, 'church_admin_deactivation');

function church_admin_deactivation_deactivation() {
	wp_clear_scheduled_hook('church_admin_bulk_email');
}
add_action('church_admin_bulk_email','church_admin_bulk_email');
function church_admin_bulk_email()
{
	
	global $wpdb;

	$max_email=get_option('church_admin_bulk_email');
	
	if(empty($max_email))$max_email=100;
	$sql='SELECT * FROM '.CA_EMA_TBL.' WHERE schedule="0000-00-00" OR schedule <=DATE(NOW()) LIMIT 0,'.$max_email;
	
	$result=$wpdb->get_results($sql);
	
	if(!empty($result))
	{
		foreach($result AS $row)
		{
			$headers="From: ".$row->from_name." <".$row->from_email.">\n";
			add_filter('wp_mail_content_type','church_admin_email_type');
			$email=$row->from_email;
			$from=$row->from_name;
			add_filter( 'wp_mail_from_name', 'church_admin_from_name');
			add_filter( 'wp_mail_from', 'church_admin_from_email');
			if(wp_mail($row->recipient,$row->subject,$row->message,$headers,unserialize($row->attachment)))
			{
				
				$wpdb->query('DELETE FROM '.CA_EMA_TBL.' WHERE email_id="'.esc_sql($row->email_id).'"');
			}else {church_admin_debug( $_GLOBALS['phpmailer']->ErrorInfo);}
			remove_filter('wp_mail_content_type','church_admin_email_type');
		}
	}
}

//add donate link on config page
add_filter( 'plugin_row_meta', 'church_admin_plugin_meta_links', 10, 2 );
function church_admin_plugin_meta_links( $links, $file ) {
	$plugin = plugin_basename(__FILE__);
	// create link
	if ( $file == $plugin ) {
		return array_merge(
			$links,
			array( '<a href="http://www.churchadminplugin.com/support">Support</a>','<a href="https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=7WWB7SQCRLUJ4">Donate</a>' )
		);
	}
	return $links;
}
/****************************
*
*
* Ajax operations
*
*
*****************************/
add_action('wp_ajax_church_admin_rota_dates','church_admin_ajax_rota_dates');
function church_admin_ajax_rota_dates()
{
	global $wpdb;
	//check_admin_referer('church_admin_rota_dates','nonce');
	$sql='SELECT rota_date FROM '.CA_ROTA_TBL.' WHERE mtg_type="service" AND service_id="'.intval($_REQUEST['service_id']).'" AND rota_date>=CURDATE() GROUP BY rota_date ORDER BY rota_date ASC LIMIT 12';
	
	$results=$wpdb->get_results($sql);
	if(!empty($results))
	{
		$out='<select name="rota_date">';
		foreach($results AS $row)
		{
			$out.='<option value="'.esc_html($row->rota_date).'">'.mysql2date(get_option('date_format'),$row->rota_date).'</option>';
		}
		$out.='</select>';
	
	}else{$out=__('No dates yet, create some first!','church-admin');}
		echo $out;
	exit();
}
add_action('wp_ajax_church_admin_username_check','church_admin_username_check');
function church_admin_username_check()
{
	check_admin_referer('church_admin_username_check','nonce');
	
	if(username_exists(stripslashes($_POST['user_name']))){echo'<span class="dashicons dashicons-no" style="color:red"></span>';}else{echo'<span style="color:green" class="dashicons dashicons-yes"></span>';}
	exit();
}
add_action( 'wp_ajax_church_admin_filter', 'church_admin_filter_callback' );

function church_admin_filter_callback() {
	check_admin_referer('church_admin_filter','nonce');
	require_once(plugin_dir_path(__FILE__).'includes/filter.php');
	church_admin_filter_process();
	exit();
}
add_action( 'wp_ajax_church_admin_filter_email', 'church_admin_filter_email_callback' );

function church_admin_filter_email_callback() {

	check_admin_referer('church_admin_filter','nonce');
	require_once(plugin_dir_path(__FILE__).'includes/filter.php');
	echo church_admin_filter_email_count($_POST['type']);
	exit();
}
/**
 *
 * Saves dismiss click for app subscribe dismiss click
 * 
 * @author  Andy Moyle
 * @param    null
 * @return   html
 * @version  0.1
 * 
 */ 
add_action( 'wp_ajax_church_admin_app_nag_dismiss', 'church_admin_app_nag_dismiss' );
function church_admin_app_nag_dismiss() {

	update_option('church_admin_app_nag_dismiss','1');
	exit();
}

/**
 *
 * Saves dismiss click for cron update dismiss click
 * 
 * @author  Andy Moyle
 * @param    null
 * @return   html
 * @version  0.1
 * 
 */ 
add_action( 'wp_ajax_church_admin_cron_nag_dismiss', 'church_admin_cron_nag_dismiss' );
function church_admin_cron_nag_dismiss() {

	update_option('church_admin_cron_nag_dismiss','1');
	exit();
}


add_action( 'wp_ajax_church_admin_people_activate', 'church_admin_people_activate_callback' );

function church_admin_people_activate_callback() {

	check_admin_referer('church_admin_people_activate','nonce');
	global $wpdb;
	$sql='UPDATE '.CA_PEO_TBL.' SET active = !active WHERE people_id="'.intval($_POST['people_id']).'"';
	
	$wpdb->query($sql);	
	$status=$wpdb->get_var('SELECT active FROM '.CA_PEO_TBL.' WHERE people_id="'.intval($_POST['people_id']).'"');
	echo $status;
	exit();
}

add_action( 'wp_ajax_church_admin_note_delete', 'church_admin_note_delete_callback' );

function church_admin_note_delete_callback() {

	check_admin_referer('church_admin_delete_note','nonce');
	global $wpdb;
	$sql='DELETE FROM '.CA_COM_TBL.'  WHERE comment_id="'.intval($_POST['note_id']).'"';
	$wpdb->query($sql);	
	$sql='DELETE FROM '.CA_COM_TBL.'  WHERE parent_id="'.intval($_POST['note_id']).'"';
	$wpdb->query($sql);	
	echo TRUE;
	exit();
}



function ca_prayer_create_posttype() {
$labels = array(
		'name'                => _x( 'Prayer Requests', 'Post Type General Name', 'church-admin' ),
		'singular_name'       => _x( 'Prayer Request', 'Post Type Singular Name', 'church-admin' ),
		'menu_name'           => __( 'Prayer Requests', 'church-admin' ),
		'parent_item_colon'   => __( 'Parent Prayer Request', 'church-admin' ),
		'all_items'           => __( 'All Prayer Request', 'church-admin' ),
		'view_item'           => __( 'View Prayer Request', 'church-admin' ),
		'add_new_item'        => __( 'Add New Prayer Request', 'church-admin' ),
		'add_new'             => __( 'Add New', 'church-admin' ),
		'edit_item'           => __( 'Edit Prayer Request', 'church-admin' ),
		'update_item'         => __( 'Update Prayer Request', 'church-admin' ),
		'search_items'        => __( 'Search Prayer Request', 'church-admin' ),
		'not_found'           => __( 'Not Found', 'church-admin' ),
		'not_found_in_trash'  => __( 'Not found in Trash', 'church-admin' ),
	);
	
	register_post_type( 'prayer-requests',
	// CPT Options
		array(
			'labels' => $labels,
			'public' => true,
			'exclude_from_search'=>false,
			'has_archive' => true,
			'publicly_queryable'=>true,
			'show_ui'=>true,
			'supports' => array( 'thumbnail','title','editor' )
		)
	);
}

add_action( 'init', 'ca_prayer_create_posttype' );

//create prayer archive template in current theme if not existing!
/**
 *
 * Send out Prayer Request Post to the prayer chain
 * 
 * @author  Andy Moyle
 * @param    null
 * @return   html
 * @version  0.1
 * 
 */ 
 
 
add_action( 'transition_post_status', 'church_admin_prayer_request_email', 10, 3 );
  
function church_admin_prayer_request_email( $new_status, $old_status, $post ) {
	global $wpdb;
	// If this is just a revision, don't send the email.
	 if ( 'publish' !== $new_status or 'publish' === $old_status
        or 'prayer-requests' !== get_post_type( $post ) )
        return;
        church_admin_debug('prayer request published');
	//app 
		$api_key=get_option('church_admin_app_api_key');
		$app_id=get_option('church_admin_app_id');
	if(!empty($api_key))
	{// prep the bundle
		 $url = 'https://fcm.googleapis.com/fcm/send';

   		$headers = array
		(
			'Authorization: key=' . $api_key,
			'Content-Type: application/json'
		);
		
		$data=array(
		
				"notification"=>array(
						"title"=>"Church App",
						"body"=>"New Prayer Request - ".$post->post_title,
						"sound"=>"default",
						"click_action"=>"FCM_PLUGIN_ACTIVITY",
						"icon"=>"fcm_push_icon",
						"content_available"=> true
				),
  				"data"=>array(
  					"title"=>"Church App",
					"body"=>"New Prayer Request - ".$post->post_title,
  				),
  				"to"=>"/topics/church".$app_id,
  				
    			//"priority"=>"high",
    			//"restricted_package_name"=>""
			);
		
		
		$ch = curl_init ();
    	curl_setopt ( $ch, CURLOPT_URL, $url );
    	curl_setopt ( $ch, CURLOPT_POST, true );
    	curl_setopt ( $ch, CURLOPT_HTTPHEADER, $headers );
    	curl_setopt ( $ch, CURLOPT_RETURNTRANSFER, true );
    	curl_setopt ( $ch, CURLOPT_POSTFIELDS, json_encode($data) );

    	$result = curl_exec ( $ch );
    	//echo $result;
    	curl_close ( $ch );
	}
	//prayer chain emails
	$post_title = get_the_title( $post->ID );
	$post_url = get_permalink( $post->ID );
	
	$title=__('Church Prayer request','church-admin');
	$content_post = get_post($post->ID);
	$content = $content_post->post_content;
	$content = apply_filters('the_content', $content);
	$content = str_replace(']]>', ']]&gt;', $content);
	$current_user=wp_get_current_user();
	$user=$wpdb->get_row('SELECT CONCAT_WS(" ",first_name,prefix,last_name) AS name, email FROM '.CA_PEO_TBL.' WHERE user_id="'.intval($current_user->ID).'"');
	//prepare send
	$sql='SELECT  DISTINCT email,CONCAT_WS(" ",first_name,last_name) AS name FROM '.CA_PEO_TBL.' WHERE  prayer_chain=1 AND email!=""';
	$results=$wpdb->get_results($sql);
	foreach($results AS $row)
	{
		if(get_option('church_admin_cron')!='immediate')
        {
			QueueEmail($row->email, $title,'<h2>'.$title.'</h2>'.$content,NULL,$user->name,$user->email,'');
			church_admin_debug("Prayer chain to ".$row->email);
		}
		else
		{
			add_filter('wp_mail_content_type','church_admin_email_type');
			add_filter( 'wp_mail_from_name', 'church_admin_from_name');
			add_filter( 'wp_mail_from', 'church_admin_from_email');
			if(!wp_mail($row->email,$title,'<h2>'.$title.'</h2>'.$content)){church_admin_debug("Prayer Chain email failure\r\n");}else{church_admin_debug("Prayer chain to ".$row->email);}
			remove_filter('wp_mail_content_type','church_admin_email_type');
		}
	}	
}

?>