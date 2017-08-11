<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly




 /**
 *
 * Delete rota settings
 * 
 * @author  Andy Moyle
 * @param    $id
 * @return   html
 * @version  0.1
 * 
 */
function church_admin_delete_rota_settings($id)
{
    global $wpdb;
    $wpdb->query("DELETE FROM ".$wpdb->prefix."church_admin_rota_settings WHERE rota_id='".esc_sql(intval($id))."'");
    $wpdb->query('DELETE FROM '.CA_ROTA_TBL.'  WHERE rota_task_id="'.intval($id).'"');
    church_admin_rota_settings_list();
}


 /**
 *
 * church_admin_edit_rota_settings
 * 
 * @author  Andy Moyle
 * @param    $id
 * @return   html
 * @version  0.1
 * 
 */
function church_admin_edit_rota_settings($id=NULL)
{
	global $wpdb,$departments;
	$wpdb->show_errors();
	if(isset($_POST['rota_task'])&&check_admin_referer('edit_rota_settings'))
	{
    	
    	$rota_task=esc_sql(stripslashes($_POST['rota_task']));
		$services=array();
	
		if(!empty($_POST['service_id'])){foreach($_POST['service_id'] AS $key=>$value){$services[]=(int)$value;}}else{$services=array('0'=>'1');}
    	//if(!empty($_POST['autocomplete'])){$autocomplete=1;}else{$autocomplete="0";}
		if(!empty($_POST['initials'])){$initials=1;}else{$initials=0;}
		$ministry=array();
		if(!empty($_POST['ministry_id']))foreach($_POST['ministry_id'] AS $key=>$ministry_id)$ministry[$key]=intval($ministry_id);
		if(!empty($ministry)){$newMinistries=maybe_serialize($ministry);}else{$newMinistries=serialize(array());}
    	if(!$id)
    	{//insert
        	$id=$wpdb->get_var('SELECT rota_id FROM '.CA_RST_TBL.' WHERE rota_task="'.$rota_task.'"' );
        	if(!$id)
        	{
				$rota_order=$wpdb->get_var('SELECT MAX(rota_order) FROM '.CA_RST_TBL)+1;
			
            	$sql='INSERT INTO '.CA_RST_TBL.' (rota_task,autocomplete,initials,rota_order,service_id,ministries) VALUES("'.$rota_task.'","1","'.$initials.'","'.esc_sql($rota_order).'","'.esc_sql(serialize($services)).'","'.esc_sql($newMinistries).'")';
			
            	$wpdb->query($sql);
            	$job_id=$wpdb->insert_id;
    
            	
            	if(!empty($job_id)){echo'<div id="message" class="notice notice-success inline"><p><strong>'.__('Rota Job Added','church-admin').'</strong></p></div>';}else{{echo'<div id="message" class="notice notice-success inline"><p><strong>'.__('Rota Job failed to save','church-admin').'</strong></p></div>';}}
            	church_admin_rota_settings_list();
        	}else
        	{
            	$sql='UPDATE '.CA_RST_TBL.' SET rota_task="'.esc_sql(stripslashes($_POST['rota_task'])).'",service_id="'.esc_sql(serialize($services)).'",autocomplete="1",initials="'.$initials.'" WHERE rota_id="'.esc_sql($id).'"';
            
           	 $wpdb->query($sql);
            	echo'<div id="message" class="notice notice-success inline"><p><strong>'.__('Rota Job Updated','church-admin').'</strong></p></div>';
            
            	church_admin_rota_settings_list();  
        	}
    	}//insert
    	else
    	{//update
        	$sql='UPDATE '.CA_RST_TBL.' SET rota_task="'.esc_sql(stripslashes($_POST['rota_task'])).'",service_id="'.esc_sql(serialize($services)).'",autocomplete="1",initials="'.$initials.'",ministries="'.esc_sql($newMinistries).'" WHERE rota_id="'.esc_sql($id).'"';
        
        	$wpdb->query($sql);
        	echo'<div id="message" class="notice notice-success inline"><p><strong>'.__('Rota Job Updated','church-admin').'</strong></p></div>';
        
        	church_admin_rota_settings_list();
   	 	}//update
	}
	else
	{
		echo'<h1>'.__('Set up Rotas','church-admin').'</h1><h2>'.__('Edit a Rota Job','church-admin').'</h2><form action="" method="post">';
		if ( function_exists('wp_nonce_field') ) wp_nonce_field('edit_rota_settings');
		$rota_task=$wpdb->get_row("SELECT * FROM ".$wpdb->prefix."church_admin_rota_settings WHERE rota_id='".esc_sql($id)."'");
		echo'<table class="form-table"><tbody><tr><th scope="row">'.__('Rota Job','church-admin').':</th><td><input type="text" name="rota_task" ';
		if(!empty($rota_task->rota_task)) echo'value="'.esc_html($rota_task->rota_task).'"';
		echo'/></td></tr>';
		/*
		echo'<tr><th scope="row">'.__('Use Autocomplete','church-admin').'</th><td><input type="checkbox" name="autocomplete" value="1"';
		if(!empty($rota_task->autocomplete)&&$rota_task->autocomplete>0) echo' checked="checked" ';
		echo'/></td></tr>';
		*/
		echo'<tr><th scope="row">'.__('Use Initials','church-admin').'</th><td><input type="checkbox" name="initials" value="1"';
		if(!empty($rota_task->initials)&&$rota_task->initials>0) echo' checked="checked" ';
		echo'/></td></tr>';
		echo'<tr><th scope="row">'.__('Which Services need this task?','church-admin').'</th><td>';
		if(!empty($rota_task->service_id))$current_services=unserialize($rota_task->service_id);
		$services=$wpdb->get_results('SELECT * FROM '.CA_SER_TBL);
		if(!empty($services))
		{
			$ser=array();
			foreach($services AS $service)
			{
				echo'<input type="checkbox" name="service_id[]" value="'.intval($service->service_id).'" ';
				if(!empty($current_services)&&!empty($service->service_id)&&is_array($current_services) && (in_array($service->service_id,$current_services))) echo' checked="checked" ';
				echo'/>'.esc_html($service->service_name).'<br/>';
			}
		}
		echo'</td></tr>';	
		//which ministries make up this rota job
		echo '<tr><th scope="row">'.__('Choose which ministries do this job','church-admin').'</th><td>';
		$ministries=church_admin_ministries();
		if(!empty($ministries))
		{
			foreach($ministries AS $id=>$ministry)
			{
				echo'<input type="checkbox" name="ministry_id[]" value="'.intval($id).'"';
				if(!empty($rota_task->ministries))
				{
					$currMinistries=maybe_unserialize($rota_task->ministries);
					if(in_array($id,$currMinistries))echo' checked="checked" ';
				}
				echo '/> '.esc_html($ministry).'<br/>';
			}
		}	
		else{echo__('To use this feature,please set up some ministries first','church-admin');}
		echo'</td></tr>';	
		echo'<tr><th scope="row"><input type="submit" name="edit_rota_setting" value="'.__('Save Rota Job','church-admin').' &raquo;" class="button-primary"/></td></tr></table></form>';
	}
}
 /**
 *
 * church_admin-_rota_settings_list
 * 
 * @author  Andy Moyle
 * @param    
 * @return   html
 * @version  0.1
 * 
 */
function church_admin_rota_settings_list()
{
    //outputs the list of rota jobs
global$wpdb;
$allMinistries=church_admin_ministries();
echo '<h2>'.__('Rota Jobs','church-admin').'</h2>';
echo '<p><a href="'.wp_nonce_url("admin.php?page=church_admin/index.php&amp;action=church_admin_edit_rota_settings",'edit_rota_settings').'">'.__('Add a rota job','church-admin').'</a></p>';
echo'<p>'.__('Rota tasks can be sorted by drag and drop, for use in other parts of the plugin.','church-admin').'</p>';
$rota_results=$wpdb->get_results('SELECT * FROM '.CA_RST_TBL.' ORDER BY rota_order ASC');
if(!empty($rota_results))
{
       echo '<table class="widefat striped" id="sortable"><thead><tr><th>'.__('Edit','church-admin').'</th><th>'.__('Delete','church-admin').'</th><th>'.__('Rota Task','church-admin').'</th><th>'.__('Which Services?','church-admin').'</th><th>'.__('Initials?','church-admin').'</th><th>'.__('Ministries','church-admin').'</th></tr></thead><tfoot><tr><th>'.__('Edit','church-admin').'</th><th>'.__('Delete','church-admin').'</th><th>'.__('Rota Task','church-admin').'</th><th>'.__('Which Services?','church-admin').'</th><th>'.__('Initials?','church-admin').'</th><th>'.__('Ministries','church-admin').'</th></tr></tfoot><tbody  class="content">';
    foreach($rota_results AS $rota_row)
    {
        $rota_edit_url='admin.php?page=church_admin/index.php&action=church_admin_edit_rota_settings&id='.$rota_row->rota_id;
        $rota_delete_url='admin.php?page=church_admin/index.php&action=church_admin_delete_rota_settings&id='.$rota_row->rota_id;
        
		if(!empty($rota_row->initials)){$initials=__('Yes','church-admin');}else{$initials=__('No','church-admin');}
		//services
		$ser=array();
		$services=maybe_unserialize($rota_row->service_id);
		foreach($services AS $key=>$value){$ser[]=$wpdb->get_var('SELECT service_name FROM '.CA_SER_TBL .' WHERE service_id="'.esc_sql($value).'"');}
        //ministries
        $ministries=array();
        if(!empty($rota_row->ministries))
        {
        	$rowMinistries=maybe_unserialize($rota_row->ministries);
        	if(!empty($rowMinistries) && is_array($rowMinistries))
        	{
        		foreach($rowMinistries AS $key=>$ministry_id)$ministries[]=$allMinistries[$ministry_id];
        	}	
        }
        echo '<tr class="sortable-row" id="'.$rota_row->rota_id.'"><td><a href="'.wp_nonce_url($rota_edit_url, 'edit_rota_settings').'">Edit</a></td><td><a href="'.wp_nonce_url(        $rota_delete_url, 'delete_rota_settings').'">Delete</a></td><td>'.esc_html(stripslashes($rota_row->rota_task)).'</td><td>'.implode('<br/>',$ser).'</td><td>'.esc_html($initials).'</td><td>'.esc_html(implode(", ",$ministries)).'</td></tr>';
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

        console.log(Order);
        
        $.ajax({
            url: "admin.php?page=church_admin/index.php&action=church_admin_update_order&which=rota_settings",
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
}
}

?>