 <?php
 
 //2016-01-03 Begun migration to use Wordpress native list tables, with all the core features!
 
 class Church_Admin_Site_List extends WP_List_Table {

   /**
    * Constructor, we override the parent to pass our own arguments
    * We usually focus on three parameters: singular and plural labels, as well as whether the class supports AJAX.
    */
    function __construct() {
       parent::__construct( array(
      'singular'=> __('Site','church-admin'), //Singular label
      'plural' => __('Sites','church-admin'), //plural label, also this well be one of the table css class
      'ajax'   => false //We won't support Ajax for this table
      ) );
    }
	/**
 	* Define the columns that are going to be used in the table
 	* @return array $columns, the array of columns to use with the table
 	*/
	function get_columns() {
   		return $columns= array(
      		'venue'=>__('Venue','church-admin'),
      		'address'=>__('Address','church-admin'),
      		'site_id'=>'',
      		'lat'=>'',
      		'lng'=>''
   			);
	}
	
	/**
 	* Prepare the table with different parameters, pagination, columns and table elements
 	*/
	
	function prepare_items() {
		global $wpdb;
  		$columns = $this->get_columns();
  		$hidden = array('site_id','lat','lng');
  		$sortable = array(
      		'venue'=>array('venue',TRUE),
      		'address'=>array('address',TRUE)
   		);
  		$this->_column_headers = array($columns, $hidden, $sortable);
  		//Build Query
  		$query = 'SELECT * FROM '.CA_SIT_TBL;
  		/* -- Ordering parameters -- */
       		//Parameters that are going to be used to order the result
       		$orderby = !empty($_GET["orderby"]) ? esc_sql($_GET["orderby"]) : 'ASC';
       		$order = !empty($_GET["order"]) ? esc_sql($_GET["order"]) : '';
       		if(!empty($orderby) & !empty($order)){ $query.=' ORDER BY '.$orderby.' '.$order; }

   			/* -- Pagination parameters -- */
        	//Number of elements in your table?
        	$totalitems = $wpdb->query($query); //return the total number of affected rows
        	//How many to display per page?
        	$perpage = 5;
        	//Which page is this?
        	$paged = !empty($_GET["paged"]) ? esc_sql($_GET["paged"]) : '';
        	//Page Number
        	if(empty($paged) || !is_numeric($paged) || $paged<=0 ){ $paged=1; }
        	//How many pages do we have in total?
        	$totalpages = ceil($totalitems/$perpage);
        	//adjust the query to take pagination into account
       		if(!empty($paged) && !empty($perpage)){
          		$offset=($paged-1)*$perpage;
         		$query.=' LIMIT '.(int)$offset.','.(int)$perpage;
       		}

   			/* -- Register the pagination -- */
      		$this->set_pagination_args( array(
         		"total_items" => $totalitems,
         		"total_pages" => $totalpages,
         		"per_page" => $perpage,
      		) );
      		//The pagination links are automatically built according to those parameters
  		
  		
  		
  		/* -- Fetch the items -- */
      	$this->items = $wpdb->get_results($query);
  		
}
	//Venue column has the edit and delete links
	function column_venue($item) {
  			$actions = array(	'edit'      => '<a href="'.wp_nonce_url('admin.php?page=church_admin/index.php&amp;action=edit_site&amp;tab=Services&amp;site_id='.intval($item->site_id),'edit_site').'">'.__('Edit','church-admin').'</a>', 		'delete'    => '<a href="'.wp_nonce_url('admin.php?page=church_admin/index.php&amp;action=delete_site&amp;tab=Services&amp;site_id='.intval($item->site_id),'delete_site').'">'.__('Delete','church-admin').'</a>'        );

  		return sprintf('%1$s %2$s', $item->venue, $this->row_actions($actions) );
}
//address column is just ordinary display
	function column_address( $item ) {
  	
      return $item->address;
    
	}
	
	function column_default($item, $column_name){
		switch($column_name){
			
			
			default:
			//for debugging purposes we print out the whole array
			return print_r($item, true);
		}
	}
	/** Text displayed when no sites stored */
public function no_items() {
  		_e( 'No sites added yet', 'church-admin' );
	}
}
 
 /**
 *
 * Site list
 * 
 * @author  Andy Moyle
 * @param    
 * @return   
 * @version  1.088
 *	
 * Using wordpress native table class now
 * 
 */ 
function church_admin_site_list()
{
		
	global $wpdb;
	
	echo'<h2>'.__('Sites','church-admin').'</h2>';
	echo'<p><a  class="button-primary" href="'.wp_nonce_url('admin.php?page=church_admin/index.php&amp;action=edit_site','edit_site').'">'.__('Add a site','church-admin').'</a></p>';
	 
	 //Our class extends the WP_List_Table class, so we need to make sure that it's there
	if(!class_exists('WP_List_Table')){require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );}
	//Prepare Table of elements

	$wp_list_table = new Church_Admin_Site_List();
	$wp_list_table->prepare_items();
	//Table of elements
	$wp_list_table->display();

}




 /**
 *
 * Delete site
 * 
 * @author  Andy Moyle
 * @param    site_id
 * @return   
 * @version  0.945
 *
 * 
 * 
 */ 
 function church_admin_delete_site($site_id)
 {
 
 	global $wpdb;
 	$wpdb->query('DELETE FROM '.CA_SIT_TBL.' WHERE site_id="'.esc_sql($site_id).'"');
 	$wpdb->query('DELETE FROM '.CA_SER_TBL.' WHERE site_id="'.esc_sql($site_id).'"');
 	$new_site_id=$wpdb->get_var('SELECT site_id FROM '.CA_SIT_TBL.' ORDER BY site_id ASC');
 	$wpdb->query('UPDATE '.CA_PEO_TBL.' SET site_id="'.$new_site_id.'" WHERE site_id="'.esc_sql($site_id).'"');
 	echo'<div class="notice notice-success inline">Site Deleted</div>';
 
 	church_admin_services_main();
 }
 
 
 /**
 *
 * Add/Edit site
 * 
 * @author  Andy Moyle
 * @param    site_id
 * @return   
 * @version  0.945
 *
 * 
 * 
 */ 
 function church_admin_edit_site($site_id=NULL)
 {
 
 	global $wpdb;
 	
 	 echo'<h2>'.__('Add/Edit Site','church-admin').'</h2>';
	
    if($site_id)$data=$wpdb->get_row('SELECT * FROM '.CA_SIT_TBL.' WHERE site_id="'.esc_sql(intval($site_id)).'"');
    if(isset($_POST['edit_site']))
    {
     
        $form=array();
        foreach($_POST AS $key=>$value)$form[$key]=sanitize_text_field(stripslashes($value));
        
       
        if(!$site_id)$site_id=$wpdb->get_var('SELECT site_id FROM '.CA_SIT_TBL.' WHERE venue="'.esc_sql($form['venue']).'" AND address="'.esc_sql($form['address']).'" AND lat="'.esc_sql($form['lat']).'" AND lng="'.esc_sql($form['lng']).'"');
        if($site_id)
        {//update
            $sql='UPDATE '.CA_SIT_TBL.' SET  venue="'.esc_sql($form['venue']).'" , address="'.esc_sql($form['address']).'" , lat="'.esc_sql($form['lat']).'" , lng="'.esc_sql($form['lng']).'" WHERE site_id="'.esc_sql(intval($site_id)).'"';
           
            $wpdb->query($sql);
        }//update
        else
        {//insert
           $wpdb->query('INSERT INTO '.CA_SIT_TBL.' (venue,address,lat,lng) VALUES ("'.esc_sql($form['venue']).'","'.esc_sql($form['address']).'","'.esc_sql($form['lat']).'","'.esc_sql($form['lng']).'")'); 
        }//insert
        echo'<div class="notice notice-success inline"><p>'.__('Site saved','church-admin').'</p></div>';
        church_admin_services_main();
       
    }
    else
    {
      
       echo'<form action="'.$_SERVER['REQUEST_URI'].'" method="post">';
       
       echo'<table class="form-table"><tr><th scope="row">'.__('Service Venue','church-admin').'</th><td><input type="text" name="venue" ';
       if(!empty($data->venue))echo' value="'.esc_html($data->venue).'" ';
       echo'/></td></tr></table>';
       require_once(plugin_dir_path(dirname(__FILE__)).'includes/directory.php');
       if(empty($data))$data=new stdClass();
	   
	   echo church_admin_address_form($data,NULL);
       echo'<tr><th scope="row">&nbsp;</th><td><input type="hidden" name="edit_site" value="yes"/><input class="button-primary"  type="submit" value="'.__('Save Site','church-admin').'&raquo;" /></td></tr></table></form>';
    }
 }