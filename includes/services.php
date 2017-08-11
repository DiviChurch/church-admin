<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly









/**
 *
 * displays list of services
 * 
 * @author  Andy Moyle
 * @param    NULL
 * @return   html 
 * @version  0.945
 *
 *	2016-05-12 Added sites
 * 
 */
function church_admin_service_list()
{
    global $wpdb;
	echo'<h2>'.__('Services','church-admin').'</h2>';
	echo'<p><a  class="button-primary" href="'.wp_nonce_url('admin.php?page=church_admin/index.php&amp;action=edit_service','edit_service').'">'.__('Add a service','church-admin').'</a></p>';
		$days=array(1=>__('Sunday','church-admin'),2=>__('Monday','church-admin'),3=>__('Tuesday','church-admin'),4=>__('Wednesday','church-admin'),5=>__('Thursday','church-admin'),6=>__('Friday','church-admin'),7=>__('Saturday','church-admin'),8=>__('Not Specified','church-admin'));
    echo'<table class="widefat striped"><thead><tr><th>'.__('Edit','church-admin').'</th><th>'.__('Delete','church-admin').'</th><th>'.__('Service Name','church-admin').'</th><th>'.__('Day','church-admin').'</th><th>'.__('Time','church-admin').'</th><th>'.__('Site','church-admin').'</th></tr></thead><tfoot><tr><th>'.__('Edit','church-admin').'</th><th>'.__('Delete','church-admin').'</th><th>'.__('Service Name','church-admin').'</th><th>'.__('Day','church-admin').'</th><th>'.__('Time','church-admin').'</th><th>'.__('Site','church-admin').'</th></tr></tfoot><tbody>';
    
    $sql='SELECT a.*,b.venue AS site FROM '.CA_SER_TBL.' a ,'.CA_SIT_TBL.' b WHERE a.site_id=b.site_id';
    $results=$wpdb->get_results($sql);
    if($results)
    {
        foreach($results AS $row)
        {
           $edit='<a href="'.wp_nonce_url('admin.php?page=church_admin/index.php&amp;action=edit_service&amp;id='.intval($row->service_id),'edit_service').'">Edit</a>';
           $delete='<a onclick="return confirm(\''.__('Are you sure?','church-admin').'\');" href="'.wp_nonce_url('admin.php?page=church_admin/index.php&amp;action=delete_service&amp;id='.intval($row->service_id),'delete_service').'">Delete</a>';
			$site= '<a href="'.wp_nonce_url('admin.php?page=church_admin/index.php&amp;action=edit_site&amp;id='.intval($row->site_id),'edit_site').'">'.esc_html($row->site).'</a>';          echo'<tr><td>'.$edit.'</td><td>'.$delete.'</td><td>'.esc_html($row->service_name).'</td>';
			echo'<td>'.esc_html($days[$row->service_day]).'</td>';
			echo'<td>'.esc_html($row->service_time).'</td><td>'.$site.'</td></tr>';
        }
        echo'</tbody></table>';
    }
    
}


/**
 *
 * delete a service
 * 
 * @author  Andy Moyle
 * @param    $service_id
 * @return   html 
 * @version  0.1
 *
 * 
 */
function church_admin_delete_service($service_id)
{
	global $wpdb;
	if(!empty($_POST['confirm_delete']))
	{
		$wpdb->query('DELETE FROM '.CA_SER_TBL.' WHERE service_id="'.esc_sql(intval($service_id)).'"');
		$wpdb->query('DELETE FROM '.CA_ROT_TBL.' WHERE service_id="'.esc_sql(intval($service_id)).'"');
		echo'<div class="notice notice-success inline"><p>'.__('Service deleted','church-admin').'</p></div>';
        church_admin_services_main();
      
	}
	else
	{
		echo'<form action="" method="POST"><p><label>'.__('Are you sure?','church-admin').'</label><input type="hidden" name="confirm_delete" value="yes"/><input class="button-primary" type="submit" value="'.__('Yes','church-admin').'"/></p></form>';
	}

}


/**
 *
 * edit a service
 * 
 * @author  Andy Moyle
 * @param    $service_id
 * @return   html 
 * @version  0.1
 *
 * 
 */
function church_admin_edit_service($id)
{
    global $wpdb;
	$days=array(1=>__('Sunday','church-admin'),2=>__('Monday','church-admin'),3=>__('Tuesday','church-admin'),4=>__('Wednesday','church-admin'),5=>__('Thursday','church-admin'),6=>__('Friday','church-admin'),7=>__('Saturday','church-admin'),8=>__('Not Specified','church-admin'));
    if($id)$data=$wpdb->get_row('SELECT * FROM '.CA_SER_TBL.' WHERE service_id="'.esc_sql(intval($id)).'"');
    if(isset($_POST['service']))
    {
        
        $form=array();
        foreach($_POST AS $key=>$value)$form[$key]=sanitize_text_field(stripslashes($value));
        //deal with new site
        if(!empty($form['site_name']))
        {
        	$site_id=$wpdb->get_var('SELECT site_id FROM '.CA_SIT_TBL.' WHERE venue="'.esc_sql($form['site_name']).'"');
        	if(empty($check)){$site_id=$wpdb->query('Insert INTO '.CA_SIT_TBL.' (venue)VALUES("'.esc_sql($form['site_name']).'")');}
        	$form['site_id']=$wpdb->insert_id;
        }
       
        if(!$id)$id=$wpdb->get_var('SELECT service_id FROM '.CA_SER_TBL.' WHERE site_id="'.esc_sql($form['site_id']).'" AND service_day="'.esc_sql($form['service_day']).'" AND service_day="'.esc_sql($form['service_day']).'" AND service_time="'.esc_sql($form['service_time']).'" ');
        if($id)
        {//update
            $sql='UPDATE '.CA_SER_TBL.' SET service_name="'.esc_sql($form['service_name']).'" , service_day="'.esc_sql($form['service_day']).'" , service_time="'.esc_sql($form['service_time']).'" , site_id="'.esc_sql($form['site_id']).'" WHERE service_id="'.esc_sql(intval($id)).'"';
            
            $wpdb->query($sql);
        }//update
        else
        {//insert
           $wpdb->query('INSERT INTO '.CA_SER_TBL.' (service_name,service_day,service_time,site_id) VALUES ("'.esc_sql($form['service_name']).'","'.esc_sql($form['service_day']).'","'.esc_sql($form['service_time']).'","'.esc_sql($form['site_id']).'")'); 
        }//insert
        echo'<div class="notice notice-success inline"><p>'.__('Service saved','church-admin').'</p></div>';
        church_admin_services_main();
        
    }
    else
    {
       echo'<div class="wrap"><h2>'.__('Service','church-admin').'</h2>';
       echo'<form action="" method="post">';
       echo'<table class="form-table"><tbody><tr><th scope="row">'.__('Service Name','church-admin').'</th><td><input type="text" name="service_name" ';
       if(!empty($data->service_name))echo' value="'.esc_html($data->service_name).'" ';
       echo'/></td></tr>';
       echo'<tr><th scope="row">'.__('Service Day','church-admin').'</th><td><select name="service_day"> ';
       foreach($days AS $key=>$value)
       {
         echo'<option value="'.intval($key).'"';
         if(!empty($data->service_day))selected($key,$data->service_day);
         echo '>'.esc_html($value).'</option>';
       }
       echo'</select></td></tr>';
       echo'<tr><th scope="row">'.__('Service Time','church-admin').'</th><td><input type="text" name="service_time" ';
       if(!empty($data->service_time))echo' value="'.esc_html($data->service_time).'" ';
       echo'/></td></tr>';
       echo'<tr><th scope="row">'.__('Site','church-admin').'</th><td><select name="site_id">';
       $sites=$wpdb->get_results('SELECT * FROM '.CA_SIT_TBL);
       $first=$option='';
       foreach($sites AS $site)
       {
       		if(!empty($data->site_id)&&$site->site_id==$data->site_id){$first='<option selected=selected value="'.intval($site->site_id).'">'.esc_html($site->venue).'</option>';}
       		$option.='<option value="'.intval($site->site_id).'">'.esc_html($site->venue).'</option>';
       }
       echo$first.$option;
       echo'<select></td></tr>';
       echo'<tr><th scope="row">'.__('Or add a site','church-admin').'</th><td><input type="text" name="site_name"/></td></tr>';
       echo'<tr><th scope="row">&nbsp;</th><td><input type="hidden" name="service" value="yes"/><input class="button-primary"  type="submit" value="'.__('Save Service','church-admin').'&raquo;" /></td></tr></tbody></table></form>';
    }
}
?>