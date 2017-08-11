<?php

function church_admin_frontend_ministries($ministry_id,$member_type_id){

	global $wpdb;
	 $ministries=church_admin_ministries('None');
	
    $out='';
    //ministry ids
    if(!empty($ministry_id)){
    	$min=explode(',',$ministry_id);
   	}else{
   		$min=array_keys($ministries);
   	}
   	//member type ids
   	$memb_sql='';
  	$membsql=$sitesql=array();
  	if($member_type_id=="#"){$memb_sql="";}
  	elseif($member_type_id!="")
  	{
  		$memb=explode(',',$member_type_id);
      	foreach($memb AS $key=>$value){if(ctype_digit($value))  $membsql[]='a.member_type_id='.$value;}
      	if(!empty($membsql)) {$memb_sql=' AND ('.implode(' || ',$membsql).')';}
	}
	
	
	
    foreach($min AS $key=>$min_id){
		$sql='SELECT a.first_name,a.last_name,a.middle_name,a.prefix, a.people_id FROM '.CA_PEO_TBL.' a, '.CA_MET_TBL.' b WHERE a.people_id=b.people_id AND b.ID="'.esc_sql($min_id).'" AND b.meta_type="ministry" '.$memb_sql.' ORDER BY a.last_name ASC';
		
		$results=$wpdb->get_results($sql);
		if(!empty($results)){
			$out.='<h2>'.esc_html($ministries[$min_id]).'</h2><p>';
			foreach($results as $row){
					//build name
					$name=$row->first_name.' ';
					$middle_name=get_option('church_admin_use_middle_name');
					if(!empty($middle_name)&&!empty($row->middle_name))$name.=$row->middle_name.' ';
					$nickname=get_option('church_admin_use_nickname');
					if(!empty($nickname)&&!empty($row->nickname))$name.='('.$row->nickname.') ';
					$prefix=get_option('church_admin_use_prefix');
					if(!empty($prefix)&&!empty($row->prefix))		$name.=$row->prefix.' ';			
					$name.=$row->last_name;
			
			
				$out.=esc_html($name).'<br/>';
			}
			$out.='</p>';
			
		}	
	}
	return $out;
}

function church_admin_frontend_ministry_list(){

	global $wpdb;
	$ministries=get_option('church_admin_ministries');
	
    $out='<h2>'.__('List of Ministries','church-admin').'</h2><p>';
    
    sort($ministries);
    foreach($ministries AS $key=>$value)
    {
    	$out.=esc_html($value).'<br/>';
    }
	$out.='</p>';
	
	
	return $out;
}