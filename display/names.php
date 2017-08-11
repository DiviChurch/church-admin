<?php

/**
* This function displays a name list 
*
* @author     	andymoyle
* @param		$member_type_id,$people_types
* @return		$out
*
*/

function church_admin_names($member_type_id,$people_type_id)
{
	global $wpdb;
	$out='';
	//work out member_type_id
	$memb=explode(',',$member_type_id);
    foreach($memb AS $key=>$value){if(ctype_digit($value))  $membsql[]='member_type_id='.$value;}
    if(!empty($membsql)) {$memb_sql=' ('.implode(' || ',$membsql).')';}else{$memb_sql='';}
    //work out people_type_id
    $people=explode(',',$people_types);
    $peoplesql=array();
    foreach($people AS $key=>$value)
    {
    	switch(strtolower($value))  
    	{
    		case 'all':$peoplesql=array(1,2,3);break;
    		case 'adults':$peoplesql[]='people_type_id=1';break;
    		case '1':$peoplesql[]='people_type_id=1';break;
    		case 'teens':$peoplesql[]='people_type_id=3';break;
    		case '3':$peoplesql[]='people_type_id=3';break;
    		case 'children':$peoplesql[]='people_type_id=2';break;
    		case '2':$peoplesql[]='people_type_id=2';break;
    	}
    }
    if(!empty($peoplesql)) {$people_sql=' ('.implode(' || ',$peoplesql).')';}else{$people_sql='';}
    $where=1;
    if(!empty($memb_sql) )$where=$memb_sql;
    if(!empty($people_sql))
    {
    	if(!empty($memb_sql))$where .=' AND ';
    	$where.=$people_sql;
    }
    $sql='SELECT first_name,middle_name,nickname,prefix,last_name FROM '.CA_PEO_TBL.' WHERE '.$where.' ORDER BY last_name';
    $results=$wpdb->get_results($sql);
    
    if(!empty($results))
    {
    	foreach($results AS $people)
    	{
    		//build first part of name
			$name=$people->first_name;
			$middle_name=get_option('church_admin_use_middle_name');
			if(!empty($middle_name)&&!empty($people->middle_name))$name.=' '.$people->middle_name.' ';
			$nickname=get_option('church_admin_use_nickname');
			if(!empty($nickname)&&!empty($people->nickname))$name.=' ('.$people->nickname.') ';
			//last name
			$prefix=get_option('church_admin_use_prefix');
			if(!empty($prefix) &&!empty($row->prefix))	$prefix=$people->prefix.' ';			
			$last_name=esc_html($prefix.$people->last_name);
    		
    		
    		$out.=esc_html($name.' '.$last_name).'<br/>';
    	}
    }
    return $out;

}