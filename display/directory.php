<?php

function church_admin_frontend_people($member_type_id=0,$map=NULL,$photo=NULL,$api_key=NULL,$kids=TRUE,$site_id=0)
{
	global $wpdb;
	$api_key=get_option('church_admin_google_api_key');
  	
  	$out='';
  	
  	$memb_sql='';
  	$membsql=$sitesql=array();
  	if($member_type_id!=0)
  	{
  		$memb=explode(',',$member_type_id);
      	foreach($memb AS $key=>$value){if(ctype_digit($value))  $membsql[]='a.member_type_id='.$value;}
      	if(!empty($membsql)) {$memb_sql=' ('.implode(' || ',$membsql).')';}
	}
	$site_sql='';
	if($site_id!=0)
  	{
  		$sites=explode(',',$site_id);
      	foreach($sites AS $key=>$value){if(ctype_digit($value))  $sitesql[]='site_id='.$value;}
      	if(!empty($sitesql)) {$site_sql=' ('.implode(' || ',$sitesql).')';}
	}
	//build query adding relevant member_types and sites
      $sql='SELECT a.*,b.* FROM '.CA_PEO_TBL.' a, '.CA_HOU_TBL.' b WHERE b.private=0 AND a.household_id=b.household_id ';
	  if(!empty($memb_sql)||!empty($site_sql)) $sql.=' AND ';
	  $sql.=$memb_sql;
	  if(!empty($memb_sql)&&!empty($site_sql))$sql.=' AND ';
	  $sql.=$site_sql;
	  $sql.='   ORDER BY last_name ASC ';
	  //execute query...
      $results=$wpdb->get_results($sql);
      $items=$wpdb->num_rows;
      
      // number of total rows in the database
      require_once(plugin_dir_path(dirname(__FILE__)).'includes/pagination.class.php');
      if($items > 0)
      {
	  	$p = new pagination;
	  	$p->items($items);
	  	$p->limit(get_option('church_admin_page_limit')); // Limit entries per page
	  
	  	$p->target(get_permalink());
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
	  	    $p->page = $_GET['paging'];
	  	}
	  	//Query for limit paging
	  	$limit = "LIMIT " . ($p->page - 1) * $p->limit  . ", " . $p->limit;
	  
	  
	  	// Pagination
		$out.= '<div class="tablenav"><div class="tablenav-pages">';
        $out.= $p->getOutput();  
        $out.= '</div></div>';
     	 //Pagination
      }
      $thead=array(__('Name','church-admin'),__('Email','church-admin'),__('Phone','church-admin'),__('Mobile','church-admin'),__('Address','church-admin'));
      $out.='<table class="striped"><thead><tr><th>'.implode("</th><th>",$thead).'</th></tr></thead><tfoot><tr><th>'.implode("</th><th>",$thead).'</th></tr></tfoot><tbody>';
      $results=$wpdb->get_results($sql.$limit);
      foreach($results AS $row)
      {
      		if($row->active)
      		{
      			$name=array_filter(array($row->first_name,$row->middle_name,$row->prefix,$row->last_name));
      			$out.='<tr><td>'.esc_html(implode(" ",$name)).'</td><td><a href="mailto:'.$row->email.'">'.esc_html($row->email).'</a></td><td><a href="call:'.$row->mobile.'">'.esc_html($row->mobile).'</a></td><td><a href="call:'.$row->phone.'">'.esc_html($row->phone).'</a></td><td>'.esc_html($row->address).'</td></tr>';
      		}
      }
      $out.='</tbody></table>';
      return $out;
    
}