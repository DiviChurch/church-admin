<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
/*
 Adds attendance figures
church_admin_show_rolling_average()
church_admin_show_graph()
church_admin_add_attendance()

*/
function church_admin_attendance_list()
{
    global $wpdb;
  
    if(empty($_GET['service_id']))$_GET['service_id']='S/1';
    if(!class_exists('WP_List_Table')){require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );}
	//Prepare Table of elements
	echo'<h3>'.__('Attendance List','church-admin').'</h3>';
	echo'<form action="'.admin_url().'admin.php" method="GET">';
	echo'<input type="hidden" name="page" value="church_admin/index.php"/><input type="hidden" name="action" value="services"/><input type="hidden" name="tab" value="services"/>';
	echo'<table style="border:none">';
	echo'<tr><th scope="row">'.__('Meeting','church-admin').'</th><td>'.church_admin_att_mtg_chooser();
	echo'</td><td><input class="button-primary" type="submit" value="'.__('Choose','church-admin').'"/></td></tr></table></form>';
	
	$wp_list_table = new Church_Admin_Attendance_List();
	$wp_list_table->prepare_items();
	//Table of elements
	$wp_list_table->display();
    
     /*global $wpdb;
	 $days=array(1=>__('Sunday','church-admin'),2=>__('Monday','church-admin'),3=>__('Tuesday','church-admin'),4=>__('Wednesday','church-admin'),5=>__('Thursday','church-admin'),6=>__('Friday','church-admin'),7=>__('Saturday','church-admin'));

    //grab attendance list in order
    $items = $wpdb->get_var('SELECT COUNT(*) FROM '.CA_ATT_TBL.' WHERE service_id="'.esc_sql($service_id).'" AND mtg_type="'.esc_sql($meeting_type).'"');
    
    $limit='';
    // number of total rows in the database
    require_once(plugin_dir_path(dirname(__FILE__)).'includes/pagination.class.php');
    if($items > 0)
    {
		$p = new pagination;
		$p->items($items);
		$p->limit(get_option('church_admin_page_limit')); // Limit entries per page
		$p->target("admin.php?page=church_admin/index.php&amp;action=church_admin_attendance_list");
		if(!isset($p->paging))$p->paging=1; 
		if(!isset($_GET[$p->paging]))$_GET[$p->paging]=1;
		$p->currentPage($_GET[$p->paging]); // Gets and validates the current page
		$p->calculate(); // Calculates what to show
		$p->parameterName('paging');
		$p->adjacents(1); //No. of page away from the current page
		if(!isset($_GET['paging']))
		{
	 	   $p->page = 1;
		}
		else
		{
		    $p->page = (int)$_GET['paging'];
		}
     	   //Query for limit paging
		$limit = "LIMIT " . ($p->page - 1) * $p->limit  . ", " . $p->limit;
    } 
    
    //prepare WHERE clause using given service_id & meeting type
    $sql='SELECT * FROM '.CA_ATT_TBL.' WHERE service_id="'.esc_sql($service_id).'" AND mtg_type="'.esc_sql($meeting_type).'" ORDER BY `date` DESC '.$limit;
    $results=$wpdb->get_results($sql);
    if($results)
     {
     	switch($meeting_type)
     	{
     		default:
	   		case 'service':
	   			$sql='SELECT * FROM '.CA_SER_TBL.' WHERE service_id="'.intval($service_id).'"';
	  			$service=$wpdb->get_row($sql);
	  			$service_details=sprintf( __('%1$s on %2$s at %3$s', 'church-admin'), esc_html($service->service_name),esc_html($days[$service->service_day]),esc_html($service->service_time));
	  		break;
	  		case 'class':
	  			$sql='SELECT * FROM '.CA_CLA_TBL.' WHERE class_id="'.intval($service_id).'"';
	  			$class=$wpdb->get_row($sql);
	  			$service_details= __('Class','church-admin').' - '.esc_html($class->name);
	  		break;
	  		case 'smallgroup':
	  			$service_details__('Group','church-admin').' - '.$wpdb->get_var('SELECT group_name FROM '.CA_SMG_TBL.' WHERE id="'.intval($service_id).'"');
	  		break;
	  		
	  	}
	  
	  
	  echo'<div class="wrap church_admin"><h2>'.__('Attendance List for','church-admin').' '.$service_details.'</h2>';
	  echo'<p><a class="button-primary" href="'.wp_nonce_url('admin.php?page=church_admin/index.php&tab=tracking&action=church_admin_edit_attendance','edit_attendance').'">'.__('Add attendance','church-admin').'</a>';
	  // Pagination
	  echo '<div class="tablenav"><div class="tablenav-pages">';
	  echo $p->show();  
	  echo '</div></div>';
	  //Pagination
	  echo '<table class="widefat striped"><thead><tr><th>'.__('Edit','church-admin').'</th><th>'.__('Delete','church-admin').'</th><th>'.__('Date','church-admin').'</th><th>'.__('Adults','church-admin').'</th><th>'.__('Children','church-admin').'</th><th>'.__('Total','church-admin').'</th></tr></thead><tfoot><tr><th>'.__('Edit','church-admin').'</th><th>'.__('Delete','church-admin').'</th><th>'.__('Date','church-admin').'</th><th>'.__('Adults','church-admin').'</th><th>'.__('Children','church-admin').'</th><th>'.__('Total','church-admin').'</th></tr></tfoot><tbody>';
	  foreach($results AS $row)
	  {
	       $edit='<a href="'.wp_nonce_url('admin.php?page=church_admin/index.php&amp;action=church_admin_edit_attendance&amp;attendance_id='.$row->attendance_id,'edit_attendance').'">'.__('Edit','church-admin').'</a>';
	       $delete='<a onclick="return confirm(\''.__('Are you sure?','church-admin').'\');" href="'.wp_nonce_url('admin.php?page=church_admin/index.php&amp;action=church_admin_delete_attendance&amp;attendance_id='.$row->attendance_id,'delete_attendance').'">'.__('Delete','church-admin').'</a>';
	       $total=$row->adults+$row->children;
	       echo'<tr><td>'.$edit.'</td><td>'.$delete.'</td><td>'.mysql2date(get_option('date_format'),$row->date).'</td><td>'.esc_html($row->adults).'</td><td>'.esc_html($row->children).'</td><td>'.esc_html($total).'</td></tr>';
	  }
	  echo'</tbody></table></div>';
     }*/
}
/**
 *
 * Attendance list class
 * 
 * @author  Andy Moyle
 * @param    $attendance_id
 * @return   html string
 * @version  0.1
 *
 * 
 */
 
 class Church_Admin_Attendance_List extends WP_List_Table {

   /**
    * Constructor, we override the parent to pass our own arguments
    * We usually focus on three parameters: singular and plural labels, as well as whether the class supports AJAX.
    */
    function __construct() {
       parent::__construct( array(
      'singular'=> __('Attendance','church-admin'), //Singular label
      'plural' => __('Attendance','church-admin'), //plural label, also this well be one of the table css class
      'ajax'   => false //We won't support Ajax for this table
      ) );
    }
	/**
 	* Define the columns that are going to be used in the table
 	* @return array $columns, the array of columns to use with the table
 	*/
	function get_columns() {
   		return $columns= array(
      		'date'=>__('Date','church-admin'),
      		'adults'=>__('Adults','church-admin'),
      		'children'=>__('Children','church-admin'),
      		'rolling_adults'=>__('Rolling Adults','church-admin'),
      		'rolling_children'=>__('Rolling Children','church-admin'),
      		'attendance_id'=>'',
      		'service_id'=>''
   			);
	}
	
	
	
	/**
 	* Prepare the table with different parameters, pagination, columns and table elements
 	*/
	
	function prepare_items() {
		global $wpdb;
  		$columns = $this->get_columns();
  		$hidden = array('service_id','attendance_id');
  		
  		$this->_column_headers = array($columns, $hidden, NULL);
  		//Build Query
  		
  		if(!empty($_GET['service_id']))
  		{
  			$meeting=explode('/',$_GET['service_id']);
  			if(!empty($meeting))
  			{//meeting populated
  				switch($meeting['0'])
  				{
  					default:
  					case'S':
  						$mtg_type='service';
  					break;
  					case 'G':
  						$mtg_type='group';
  					break;
  					case 'C':
  						$mtg_type='class';
  					break;
  				}
		  		$service_id=intval($meeting['1']);	
  		}
  		else
  		{
  			$service_id=1;
  			$mtg_type='service';
  		}
  		$query = 'SELECT * FROM '.CA_ATT_TBL.' WHERE mtg_type="'.$mtg_type.'" AND service_id="'.esc_sql($service_id).'" ORDER BY `date` DESC';
  		

   			/* -- Pagination parameters -- */
        	//Number of elements in your table?
        	$totalitems = $wpdb->query($query); //return the total number of affected rows
        	//How many to display per page?
        	$perpage = 12;
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
		//Date column has the edit and delete link
	}//end prepeare items
	function column_date($item) {
  			$actions = array(	'edit'      => '<a href="'.wp_nonce_url('admin.php?page=church_admin/index.php&amp;action=edit_attendance&amp;tab=Services&amp;attendance_id='.intval($item->attendance_id),'edit_attendance').'">'.__('Edit','church-admin').'</a>', 		'delete'    => '<a href="'.wp_nonce_url('admin.php?page=church_admin/index.php&amp;action=delete_attendance&amp;tab=Services&amp;attendance_id='.intval($item->attendance_id),'delete_attendance').'">'.__('Delete','church-admin').'</a>'        );

  		return sprintf('%1$s %2$s', mysql2date(get_option('date_format'),$item->date), $this->row_actions($actions) );
}

	
	function column_default($item, $column_name){
		switch($column_name){
			
			default:
			//for debugging purposes we print out the whole array
			return $item->$column_name;
			}//end switch
		}//end column_default
	/** Text displayed when no sites stored */
	public function no_items() {
  		_e( 'No attendance data added yet', 'church-admin' );
		}//end no items
	
}//end class	


/**
 *
 * Save attendance
 * 
 * @author  Andy Moyle
 * @param    $attendance_id
 * @return   html string
 * @version  0.1
 *
 * refactored 11th April 2016 to remove multi-service bug
 * refactored 9th January 2017 to allow attanednce for services, classes and groups to be recorded
 * 
 */
function church_admin_edit_attendance($attendance_id)
{
  global $wpdb;
  $days=array(1=>__('Sunday','church-admin'),2=>__('Monday','church-admin'),3=>__('Tuesday','church-admin'),4=>__('Wednesday','church-admin'),5=>__('Thursday','church-admin'),6=>__('Friday','church-admin'),7=>__('Saturday','church-admin'));
  
  	//check services, classes or groups setup
  	$services=$wpdb->get_results('SELECT * FROM '.CA_SER_TBL);
  	$groups=$wpdb->get_results('SELECT * FROM '.CA_SMG_TBL);
	$classes=$wpdb->get_results('SELECT * FROM '.CA_CLA_TBL);
	if(empty($services) && empty($classes) && empty($groups))
	{
		echo '<p>'.__('Please set up a service, group or class first','church-admin');
	}
	else
	{//safe to proceed
	
 		if(!empty($attendance_id))$data=$wpdb->get_row('SELECT * FROM '.CA_ATT_TBL.' WHERE attendance_id="'.esc_sql($attendance_id).'"');

		if(isset($_POST['edit_att']))
		{
  			$meeting=explode('/',$_POST['service_id']);
  			if(!empty($meeting))
  			{//meeting populated
  				switch($meeting['0'])
  				{
  					default:
  					case'S':
  						$mtg_type='service';
  					break;
  					case 'G':
  						$mtg_type='group';
  					break;
  					case 'C':
  						$mtg_type='class';
  					break;
  				}
		  		$service_id=intval($meeting['1']);		
  				$form=array();
     			
     		
     			$date=date('Y-m-d',strtotime($_POST['add_date']));
     			//print_r($sql);
     			if(empty($attendance_id))$attendance_id=NULL;
     			$data=array(
     				'attendance_id'=>$attendance_id,
     				'mtg_type'=>$mtg_type,
     				'service_id'=>intval($service_id),
     				'adults'=>intval($_POST['adults']),
     				'children'=>intval($_POST['children']),
     				'date'=>$date
     			);
     			$wpdb->replace(CA_ATT_TBL,$data,'%s');
     			if(empty($attendance_id))$attendance_id=$wpdb->insert_id;
     			church_admin_refresh_rolling_average();
     			//work out rolling average from values!
				/*
     			$avesql='SELECT FORMAT(AVG(adults),0) AS rolling_adults,FORMAT(AVG(children),0) AS rolling_children FROM '.CA_ATT_TBL.' WHERE `mtg_type`="'.esc_sql($mtg_type).'" AND `service_id`="'.esc_sql($service_id).'" AND `date` >= DATE_SUB("'.esc_sql(date('Y-m-d',strtotime($_POST['add_date']))).'",INTERVAL 52 WEEK) AND `date`<= "'.esc_sql(date('Y-m-d',strtotime($_POST['add_date']))).'"';
    			$averow=$wpdb->get_row($avesql);

     			//update table with rolling average
         		$up='UPDATE '.CA_ATT_TBL.' SET rolling_adults="'.$averow->rolling_adults.'", rolling_children="'.$averow->rolling_children.'" WHERE attendance_id="'.esc_sql($attendance_id).'"';
	 			$wpdb->query($up);
				*/

     			echo '<div id="message" class="notice notice-success inline">';
     			echo '<p><strong>'.__('Attendance added','church-admin').'.</strong></p>';
     			echo '</div>';
	 		}//meeting populated
	 		else
	 		{
	 			echo '<div id="message" class="notice notice-warning inline">'.__('You did not select a meeting','church-admin').'</div>';
	 		}
	 		$_GET['service_id']=$_POST['service_id'];//make it available for the table
    		church_admin_attendance_list();

		}//end process
		else
		{//form
			echo'<h2>'.__('Attendance','church-admin').'</h2>';
			echo '<form action="" method="post" name="add_attendance" id="add_attendance">';
		
			echo'<table class="form-table">';
			echo'<tr><th scope="row">'.__('Meeting','church-admin').'</th><td>'.church_admin_att_mtg_chooser().'</td></tr>';
		//datepicker js
		if(!empty($data->date)){$date=$data->date;}else{$date=NULL;}
		echo '<tr><th scope="row">'.__('Date','church-admin').' :</th><td>'.church_admin_date_picker($date,'add_date',FALSE,'2011',date('Y',time()+60*60*24*365*10)).'</td></tr>';
		echo '<tr><th scope="row">'.__('Adults','church-admin').'</th><td><input type="text" name="adults"  ';
		if(!empty($data->adults)) echo' value="'.esc_html($data->adults).'" ';
		echo '/></td></tr>';
		echo '<tr><th scope="row"><label >'.__('Children','church-admin').'</th><td><input type="text" name="children" ';
		if(!empty($data->children)) echo' value="'.esc_html($data->children).'" ';
		echo'/><input type="hidden" name="edit_att" value="y"/></td></tr>';
		echo '<tr><td cellspacing=2><input class="button-primary" type="submit" value="'.__('Add attendance for that date','church-admin').' &raquo;" /></td></tr></table></form>';;

	}//end of attendance form
	}//end safe to proceed
}//end function


function church_admin_att_mtg_chooser()
{
	global $wpdb;
	$first=$option='';
	//which meeting
			//service
					
			//services first
			$services=$wpdb->get_results('SELECT * FROM '.CA_SER_TBL);
			if(!empty($services))
			{
				$option='';
				foreach($services AS $service)
				{
					$serviceDetail=__('Service','church-admin').' - '.esc_html($service->service_name).' '.esc_html($service->service_time);
     				if(!empty($data->mtg_type) && $data->mtg_type=='service'&&!empty($data->service_id)&& $data->service_id==$service->service_id)
     				{
	  					$first='<option value="S/'.esc_html($service->service_id).'" selected="selected">'.$serviceDetail.'</option>';
     				}
     				else
     				{
	  					$option.='<option value="S/'.esc_html($service->service_id).'" >'.$serviceDetail.'</option>';
     				}
				}
			}
			//groups
			$groups=$wpdb->get_results('SELECT * FROM '.CA_SMG_TBL);
			if(!empty($groups))
			{
				foreach($groups AS $group)		
				{
					if(!empty($data->mtg_type) && $data->mtg_type=='group'&&!empty($data->service_id)&& $data->service_id==$group->id)
					{
						$first='<option value="G/'.esc_html($service->service_id).'" selected="selected">'.__('Group','church-admin').' - '.$group->group_name.'</option>';
					}
					else
     				{
	  					$option.='<option value="G/'.esc_html($service->service_id).'" >'.__('Group','church-admin').' - '.esc_html($group->group_name).'</option>';
     				}
			
				}
			}
			//classes
			$classes=$wpdb->get_results('SELECT * FROM '.CA_CLA_TBL);
			if(!empty($classes))
			{
				foreach($classes AS $class)		
				{
					if(!empty($data->mtg_type) && $data->mtg_type=='class'&&!empty($data->service_id)&& $data->service_id==$class->class_id)
					{
						$first='<option value="C/'.esc_html($service->service_id).'" selected="selected">'.__('Class','church-admin').' - '.$group->name.'</option>';
					}
					else
     				{
	  					$option.='<option value="C/'.esc_html($service->service_id).'" >'.__('Class','church-admin').' - '.esc_html($class->name).'</option>';
     				}
			
				}
			}	
						
    		return '<select name="service_id">'.$first.$option.'</select>';
}

function church_admin_save_attendance($attendance_id=NULL,$date,$mtg_type,$mtg_id,$adults=0,$children=0)
{
	global $wpdb;
	if(empty($date))$date=date('y-m-d');
	$data=array(
     				'attendance_id'=>intval($attendance_id),
     				'date'=>$date,
     				'mtg_type'=>$mtg_type,
     				'service_id'=>intval($mtg_id),
     				'adults'=>intval($adults),
     				'children'=>intval($children)
     			);
     			$wpdb->replace(CA_ATT_TBL,$data,'%s');
}
function church_admin_delete_attendance($attendance_id)
{
     global $wpdb;
     
     $wpdb->query('DELETE FROM '.CA_ATT_TBL.' WHERE attendance_id="'.esc_sql($attendance_id).'"');
     echo'<div class="notice notice-success inline"><p>'.__('Attendance record deleted','church-admin').'</p></div>';
     //so attendance table displays right list
     $mtg=$wpdb->get_row('SELECT * FROM '.CA_ATT_TBL.' WHERE attendance_id="'.esc_sql($attendance_id).'"');
     switch($mtg->mtg_type)
     {
     	case'service':$mtg_type='S';break;
     	case'group':$mtg_type='G';break;
     	case'class':$mtg_type='C';break;
     	
     }
     $_GET['service_id']=$mtg_type.'/'.$mtg->service_id;//so attendance table displays right list
     church_admin_attendance_list();
}

function church_admin_attendance_metrics($service_id=1)
{
     global $wpdb;
	 $days=array(1=>__('Sunday','church-admin'),2=>__('Monday','church-admin'),3=>__('Tuesday','church-admin'),4=>__('Wednesday','church-admin'),5=>__('Thursday','church-admin'),6=>__('Friday','church-admin'),7=>__('Saturday','church-admin'));
     ;
     $thead='';
     if(empty($service_id))$service_id=1;
     $service=$wpdb->get_var('SELECT CONCAT_WS(" ",service_name,service_time) AS service FROM '.CA_SER_TBL.' WHERE service_id="'.esc_sql($service_id).'"');
     $first_year=$wpdb->get_var('SELECT YEAR(`date`) FROM '.CA_ATT_TBL.' WHERE service_id="'.esc_sql($service_id).'" ORDER BY `date` ASC LIMIT 1');
     $last_year=$wpdb->get_var('SELECT YEAR(`date`) FROM '.CA_ATT_TBL.' WHERE service_id="'.esc_sql($service_id).'" ORDER BY `date` DESC LIMIT 1');
    
     for($year=$first_year;$year<=$last_year;$year++){$thead.="<th>$year</th>";}
    
     $aggtable=$totaltable=$adulttable=$childtable='<table class="widefat striped"><thead><tr><th>'.__('Month','church-admin').'</th>'.$thead.'</tr></thead><tfoot><tr><th>Month</th>'.$thead.'<tr></tfoot><tbody>';
    
	  $results=$wpdb->get_results('SELECT ROUND( AVG( adults ) ) AS adults, ROUND( AVG( children ) ) AS children, YEAR( `date` ) AS year, MONTH( `date` ) AS month FROM '.CA_ATT_TBL.' WHERE service_id="'.esc_sql($service_id).'" GROUP BY YEAR( `date` ) , MONTH( `date` )');
	  
if($results) 
{	  foreach($results AS $row)
	  {
	       
	       $adults[$row->month][$row->year]=$row->adults;
	       $children[$row->month][$row->year]=$row->children;
	  }
	  
     for($month=1;$month<=12;$month++)
     {
	  $aggtable.='<tr><td>'.esc_html($month).'</td>';
	  $totaltable.='<tr><td>'.esc_html($month).'</td>';
	  $adulttable.='<tr><td>'.esc_html($month).'</td>';
	  $childtable.='<tr><td>'.esc_html($month).'</td>';
	  for($year=$first_year;$year<=$last_year;$year++)
	  {
	       if(empty($adults[$month][$year])){$adulttable.='<td>&nbsp;</td>';}else{$adulttable.='<td>'.esc_html($adults[$month][$year]).'</td>';}
	       if(empty($children[$month][$year])){$childtable.='<td>&nbsp;</td>';}else{$childtable.='<td>'.esc_html($children[$month][$year]).'</td>';}
	       if(!empty($adults[$month][$year]))$total=$adults[$month][$year]+$children[$month][$year];
	       if(!empty($adults[$month][$year]))if($adults[$month][$year]+$children[$month][$year]>0){$totaltable.='<td>'.esc_html($total).'</td>';}else{$totaltable.='<td>&nbsp;</td>';}
	       if(!empty($adults[$month][$year])&&$adults[$month][$year]+$children[$month][$year]>0){$aggtable.='<td><span class="adults">'.esc_html($adults[$month][$year]).'</span>, <span class="children">'.esc_html($children[$month][$year]).'</span> (<span class="total">'.esc_html($total).')</span></td>';}else{$aggtable.='<td>&nbsp;</td>';}
	       
	  }
	  $aggtable.='</tr>';
	  $totaltable.='</tr>';
	  $adulttable.='</tr>';
	  $childtable.='</tr>';
     }
     $aggtable.='</tbody></table>';
	  $totaltable.='</tbody></table>';
	  $adulttable.='</tbody></table>';
	  $childtable.='</tbody></table>';
}
else
{
     $totaltable=$aggtable=$childtable=$adulttable='<p>'.__('No attendance recorded yet','church-admin').'</p>';
}

     echo'<h2>'.__('Attendance Figures','church-admin').'</h2>';
     echo'<p><a href="'.wp_nonce_url('admin.php?page=church_admin/index.php&amp;action=church_admin_edit_attendance','edit_attendance').'">'.__('Add attendance','church-admin').'</a>';
     $services=$wpdb->get_results('SELECT * FROM '.CA_SER_TBL);
     
     echo'<table>';
     foreach($services AS $service)
     {
	  $sql='SELECT * FROM '.CA_ATT_TBL.' WHERE service_id="'.esc_sql($service->service_id).'"';
	  
	  $check=$wpdb->get_row($sql);
	  if($service->service_id==$service_id)$service_details=sprintf( __('%1$s on %2$s at %3$s', 'church-admin'), esc_html($service->service_name),esc_html($days[$service->service_day]),esc_html($service->service_time));
	  if($check) echo'<tr><td><a href="admin.php?page=church_admin/index.php&amp;action=church_admin_attendance_metrics&amp;service_id='.$service->service_id.'">'.sprintf(__('View attendance table for %1$s  %2$s','church-admin'),esc_html($service->service_name),esc_html($service->service_time)).'</a></td><td><a href="admin.php?page=church_admin/index.php&amp;action=church_admin_attendance_list&amp;service_id='.$service->service_id.'">'.sprintf(__('Edit week by week attendance for %1$s %2$s','church-admin'),esc_html($service->service_name),esc_html($service->service_time)).'</a></td></tr>';
     }
     echo'</table>';
     echo '<h2>'.__('Attendance Adults,Children (Total)','church-admin').' '.$service_details.'</h2>'.esc_html($aggtable);
     echo '<h2>'.__('Total Attendance for','church-admin').' '.$service_details.'</h2>'.esc_html($totaltable);
     echo '<h2>'.__('Adults Attendance for','church-admin').' '.$service_details.'</h2>'.$adulttable;
     echo '<h2>'.__('Children Attendance for','church-admin').' '.$service_details.'</h2>'.$childtable;
  
}

function church_admin_refresh_rolling_average()
{
	global $wpdb;
	$results=$wpdb->get_results('SELECT * FROM '.CA_ATT_TBL);
	if(!empty($results))
	{
		foreach($results AS $row)
		{
			$avesql='SELECT FORMAT(AVG(adults),0) AS rolling_adults,FORMAT(AVG(children),0) AS rolling_children FROM '.CA_ATT_TBL.' WHERE `mtg_type`="'.esc_sql($row->mtg_type).'" AND `service_id`="'.esc_sql($row->service_id).'" AND `date` >= DATE_SUB("'.esc_sql($row->date).'",INTERVAL 52 WEEK) AND `date`<= "'.esc_sql($row->date).'"';
    		$averow=$wpdb->get_row($avesql);
    		$wpdb->query('UPDATE '.CA_ATT_TBL.' SET rolling_adults= "'.intval($averow->rolling_adults).'",rolling_children= "'.intval($averow->rolling_children).'" WHERE attendance_id="'.intval($row->attendance_id).'"');
		}	
	}

}
?>