<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * Displays ministries.
 *
 * @param 
 * @param 
 *
 * @author andy_moyle
 * 
 */
function church_admin_ministries_list()
{
    global $wpdb;
    $ministries=$wpdb->get_results('SELECT * FROM '.CA_MIN_TBL.' ORDER BY ministry');
    
    echo'<p><a class="button-primary" href="'.wp_nonce_url('admin.php?page=church_admin/index.php&amp;action=edit_ministry&tab=ministries','edit_ministry').'">'.__('Add a ministry','church-admin').'</a> <a class="button-secondary" href="'.wp_nonce_url(site_url().'/?download=ministries_pdf','ministries_pdf').'">'.__('Ministries PDF','church-admin').'</a></p>';
    if(!empty($ministries))
    {
        echo'<table class="widefat striped"><thead><tr><th>'.__('Edit','church-admin').'</th><th>'.__('Delete','church-admin').'</th><th>'.__('Ministry (Click to view people)','church-admin').'</th><th>'.__('Safeguarding Needed','church-admin').'</th><th>'.__('Shortcode','church-admin').'</th></tr></thead><tfoot><tr><th>'.__('Edit','church-admin').'</th><th>'.__('Delete','church-admin').'</th><th>'.__('Ministry','church-admin').'</th><th>'.__('Safeguarding Needed','church-admin').'</th><th>'.__('Shortcode','church-admin').'</th></tr></tr></tfoot><tbody>';
        foreach($ministries AS $ministry)
        {
            $edit='<a href="'.wp_nonce_url('admin.php?page=church_admin/index.php&amp;action=edit_ministry&amp;tab=ministries&amp;id='.$ministry->ID,'edit_ministry').'">'.__('Edit','church-admin').'</a>';
            if($ministry->ID!=1){$delete='<a onclick="return confirm(\''.__('Are you sure?','church-admin').'\');" href="'.wp_nonce_url('admin.php?page=church_admin/index.php&amp;action=delete_ministry&amp;id='.$ministry->ID,'delete_ministry').'">'.__('Delete','church-admin').'</a>';}else{$delete=__("Can't be deleted",'church-admin');}
        $min=esc_html($ministry->ministry);
        if(!empty($ministry->parentID))
        {
        	$parent=$wpdb->get_var('SELECT ministry FROM '.CA_MIN_TBL. ' WHERE ID="'.intval($ministry->parentID).'"');
        	if(!empty($parent))$min.=' ('.__('Overseen by','church_admin').' '.esc_html($parent).')'; 
        }
        if(!empty($ministry->safeguarding)){$safe='<span class="dashicons dashicons-yes"></span>';}else{$safe='';}
        echo'<tr><td>'.$edit.'</td><td>'.$delete.'</td><td>'.esc_html($min).'</td><td>'.$safe.'</td><td>[church_admin type="ministries" ministry_id='.intval($ministry->ID).']</td></td></tr>';
            
        }
        echo'</tbody></table>';
    }

}


function church_admin_view_ministry($id)
{
		echo'<p><a href="'.wp_nonce_url("admin.php?page=church_admin/index.php&amp;action=ministry_list",'ministry_list').'">'.__('Ministry List','church-admin').'</a></p>';
		global $wpdb;
		$ministries=church_admin_ministries();
		$sql='SELECT CONCAT_WS(" ",a.first_name,a.last_name) AS name, a.people_id FROM '.CA_PEO_TBL.' a, '.CA_MET_TBL.' b WHERE a.people_id=b.people_id AND b.ID="'.esc_sql($id).'" AND b.meta_type="ministry" ORDER BY a.last_name ASC';
		
		$results=$wpdb->get_results($sql);
		if(!empty($_POST))
		{
			//delete people from that ministry
			if(!empty($_POST['remove'])){
			
				foreach($_POST['remove'] AS $key=>$value) $wpdb->query('DELETE FROM '.CA_MET_TBL.' WHERE meta_type="ministry" AND ID="'.esc_sql($id).'" AND people_id="'.esc_sql($value).'"');
			}
			//add people to ministry
			$peoples_id=maybe_unserialize(church_admin_get_people_id($_POST['people']));
			if(!empty($peoples_id)) {
					foreach($peoples_id AS $key=>$people_id){
						
						$sql='INSERT INTO '.CA_MET_TBL.' (people_id,ID,meta_type)VALUES("'.esc_sql($people_id).'","'.esc_sql($id).'","ministry")';
						$wpdb->query($sql);
					}
				}
		
		}	
	$results=$wpdb->get_results('SELECT CONCAT_WS(" ",a.first_name,a.last_name) AS name, a.people_id FROM '.CA_PEO_TBL.' a, '.CA_MET_TBL.' b WHERE a.people_id=b.people_id AND b.ID="'.esc_sql($id).'" AND b.meta_type="ministry" ORDER BY a.last_name,a.first_name ASC');
		
			echo '<h2>'.sprintf(__('Viewing who is in "%1s" ministry','church-admin'),esc_html($ministries[$id])).'</h2><form action="" method="POST">';
			if(!empty($results))
			{//ministry contains people
				echo'<table class="widefat striped" ><thead><tr><th>'.__('Remove','church-admin').'</th><th>'.__('Person','church-admin').'</th></tr></thead><tbody>';
				foreach($results AS $row)
				{
					$delete='<input type="checkbox" value="'.esc_html($row->people_id).'" name="remove[]"/>';
					echo'<tr><td>'.$delete.'</td><td>'.esc_html($row->name).'</td></tr>';
				}
				echo'</table>';
			}//ministry contains people
			echo'<p>'.church_admin_autocomplete('people','friends','to',NULL).'</p>';
			echo'<p><label>'.__('Add people','church-admin').'</label><input type="hidden" name="view_ministries" value="yes"/><input type="submit" value="'.__('Update','church-admin').'"/></p></form>';
		
		
		require_once(plugin_dir_path(dirname(__FILE__)).'includes/comments.php');
		if(!empty($id))church_admin_show_comments('ministry',	$id);
}
function church_admin_delete_ministry($id)
{
    global $wpdb;
	$wpdb->query(' DELETE FROM '.CA_MIN_TBL.' WHERE ID="'.intval($id).'"');
    //delete ministry from people
    $wpdb->query('DELETE FROM '.CA_MET_TBL.' WHERE ID="'.esc_sql($id).'" AND meta_type="ministry"');
    echo'<div class="notice notice-success inline"><p>'.__('Ministries Deleted','church-admin').'</p></div>';
    church_admin_ministries_list();
}



function church_admin_edit_ministry($ID)
{
global $wpdb;

   $ministries=church_admin_ministries();
   

   if(empty($ministries)) $ministries=array();
    if(isset($_POST['edit_ministry']))
    {//process
        $dep_name=sanitize_text_field(stripslashes($_POST['ministry_name']));
        $overseer=sanitize_text_field(stripslashes($_POST['overseer']));
        if($ID)
        {//update current ministry name
            $wpdb->query('UPDATE '.CA_MIN_TBL.' SET ministry="'.esc_sql($dep_name).'"  WHERE ID="'.esc_sql($ID).'"');
            echo '<div class="notice notice-success inline"><p>'.__('Ministries Updated','church-admin').'</p></div>';
        }        
        elseif(!in_array($dep_name,$ministries))
        {//add new one if unique
            $wpdb->query('INSERT INTO '.CA_MIN_TBL.' (ministry) VALUES("'.esc_sql($dep_name).'")');
            echo '<div class="notice notice-success inline"><p>'.__('Ministries Updated','church-admin').'</p></div>';
            $ID=$wpdb->insert_id;
        }
        else
        {//not unique or update, so ignore!
           echo '<div class="notice notice-success inline"><p>'.__('Ministries Unchanged','church-admin').'</p></div>'; 
        }
        if(!empty($_POST['safeguarding']))
        {
        	$wpdb->query('UPDATE '.CA_MIN_TBL.' SET   safeguarding="1" WHERE  ID="'.intval($ID).'"');
        }
        else
        {
        	$wpdb->query('UPDATE '.CA_MIN_TBL.' SET   safeguarding="0" WHERE  ID="'.intval($ID).'"');
        }
        if(!empty($_POST['parent_id']))
        {
        	$wpdb->query('UPDATE '.CA_MIN_TBL.' SET   parentID="'.intval($_POST['parent_id']).'" WHERE  ID="'.intval($ID).'"');
        }
        if(!empty($_POST['overseer']))
        {
        	$check=$wpdb->get_var('SELECT ID FROM '.CA_MIN_TBL.' WHERE ministry="'.esc_sql($overseer).'"');
        	if($check)
        	{//update
        		$sql='UPDATE '.CA_MIN_TBL.' SET parent_ID="'.intval($check).'" WHERE ID="'.intval($ID).'"';
        		echo $sql;
        		$wpdb->query($sql);
        		echo '<div class="notice notice-success inline"><p>'.$overseer.' '.__('updated','church-admin').'</p></div>'; 
        	}
        	else
        	{
        		$sql='INSERT INTO '.CA_MIN_TBL.' (ministry) VALUES("'.esc_sql($overseer).'",)';
        		
        		$wpdb->query($sql);
        		$parentID=$wpdb->insert_id;
        		$wpdb->query('UPDATE '.CA_MIN_TBL.' SET parentID="'.intval($parentID).'" WHERE ID"'.intval($ID).'"');
        		echo '<div class="notice notice-success inline"><p>'.__('Overseer added','church-admin').'</p></div>'; 
        	}
        
        }
        //v1.06 add in extra people
       	$wpdb->query('DELETE FROM '.CA_MET_TBL.' WHERE meta_type="ministry" AND ID ="'.intval($ID).'"');
        if(!empty($_POST['people']))$people_ids=explode(",",stripslashes($_POST['people']));
       
        if(!empty($people_ids))
        {
        	foreach($people_ids AS $key=>$name)
        	{
				$people_id=	church_admin_get_one_id(trim($name));
        		
        		if(!empty($people_id))church_admin_update_people_meta($ID,$people_id,'ministry');
        	}
        }      
        church_admin_ministries_list();
        
    }//end process
    else
    {//form

        echo'<h2>';
        if($ID)
        {
        	$which= __('Update','church-admin').' ';
        	$data=$wpdb->get_row('SELECT * FROM '.CA_MIN_TBL.' WHERE ID="'.intval($ID).'"');
        	if($data->parentID)$parent=$wpdb->get_var('SELECT ministry FROM '.CA_MIN_TBL.' WHERE ID="'.intval($data->parentID).'"');
        }else {$which=  __('Add','church-admin').' ';}
        echo $which .__('Ministry','church-admin').'</h2>';
        echo'<form action="" method="post"><table class="form-table">';
        echo'<tr><th scope="row">'.__('Ministry Name','church-admin').'</td><td><input type="text" name="ministry_name" ';
        if($ID) echo ' value="'.esc_html($ministries[$ID]).'" ';
        echo'/></td></tr>';
        echo'<tr><th scope="row">'.__('Safeguarding needed?','church-admin').'</td><td><input type="checkbox" name="safeguarding" value=1';
        if(!empty($data->safeguarding)) echo' checked="checked" ';
        echo'/></td></tr>';
        echo'<tr><th scope="row">'.__('Parent Ministry','church-admin') .'</td><td><input type="text" name="overseer"  ';
        echo'/></td></tr>';
        
        echo'<tr><th scope="row">'.__('Or choose a parent ministry','church-admin') .'</td><td>';
        echo'<select name="parent_id">';
        
        if(!empty($parent)&&!empty($parentID))echo'<option value="'.intval($data->parentID).'" selected="selected">'.esc_html($parent).'</option>';
        echo'<option value="">'.__('None','church-admin').'</option>';
        foreach($ministries AS $id=>$min)
        	{
        		if((!empty($ID)&&$ID!=$id) ||empty($ID)) echo'<option value="'.intval($id).'">'.esc_html($min).'</option>';
        	}
        echo'</select></td></tr>';
       	
       	if(!empty($ID))$results=$wpdb->get_results('SELECT people_id FROM '. CA_MET_TBL.' WHERE meta_type="ministry" AND ID="'.intval($ID).'"');
       	if(!empty($results))
       	{
       		foreach($results AS $row)$current_ldrs[]=$row->people_id;
       	}
       	if(!empty($current_ldrs)){$current=church_admin_get_people($current_ldrs);}else{$current='';}
    		echo'<tr class="ca-types autocomplete"><th scope=row>'.__('People in ministry','church-admin').'</th><td>'.church_admin_autocomplete("people","friends","to",$current).'</td></tr>';
        echo'</table>';
        
        echo'<p class="submit"><input type="hidden" name="edit_ministry" value="yes"/><input class="button-primary" type="submit" value="'.__('Save Ministry','church-admin').'&raquo;" /></p></form>';
        
        
        
    }//end form
    
        require_once(plugin_dir_path(dirname(__FILE__)).'includes/comments.php');
		if(!empty($id))church_admin_show_comments('ministry',	$ID);
}
?>