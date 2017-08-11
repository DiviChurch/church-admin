<?php
function church_admin_frontend_small_groups($member_type_id=1,$restricted=FALSE)
{


	//$restricted means people only see the groups they are involved with

	global $wpdb,$current_user;
	$show=TRUE;
	if($restricted)
	{	
		wp_get_current_user();
		$people_id=$wpdb->get_var('SELECT people_id FROM '.CA_PEO_TBL.' WHERE user_id="'.intval($current_user->ID).'"');
		$show=FALSE;//default no show
			
	}
	
	$out='';
	
	if(!is_array($member_type_id)){$memb=explode(',',$member_type_id);}else{$memb=$member_type_id;}
      foreach($memb AS $key=>$value){if(ctype_digit($value))  $membsql[]='a.member_type_id='.$value;}
      if(!empty($membsql)) {$memb_sql=' AND ('.implode(' || ',$membsql).')';}else{$memb_sql='';}
	//show small groups 
	$leader=array();
	
	$sql='SELECT * FROM '.CA_SMG_TBL .' ORDER BY smallgroup_order';
	$small_group=$sg=array();
	$results = $wpdb->get_results($sql);    
	if(!empty($results))
	{
		foreach($results AS $row)
		{
			if($restricted)
			{
				//check if a leader
				$leaders=maybe_unserialize($row->leadership);
				if(!empty($leaders))foreach($leaders AS $leaderlevel) if(in_array($people_id,$leaderlevel))$show=TRUE;//allowed!
				//check if a site admin
				if(current_user_can('manage_options'))$show=true;
				//check  if in group
				$check=$wpdb->get_var('SELECT people_ID FROM '.CA_MET_TBL.' WHERE ID="'.intval($row->id).'" AND people_id="'.intval($people_id).'" AND meta_type="smallgroup"');
				if($check)$show=TRUE;
			}
			if($show)
			{
				$small_group[$row->id]='';
				$out.='<h3>'.$row->group_name.'</h3>';
			
				//leaders
				//build leaders
				$ldr='';
				$hierarchy=church_admin_get_hierarchy(1);
    			krsort($hierarchy);//sort top level down
    			//who is currently leading
    			$curr_leaders=maybe_unserialize($row->leadership);
    			//need titles of leaders levels
    			$ministries=church_admin_ministries(NULL);
    			foreach($hierarchy AS $key=>$min_id)
    			{
    		
    				$out.='<p><strong>'.$ministries[$min_id].'</strong><br/>';//leader level name
    			
    				if(!empty($curr_leaders[$min_id]))
    				{
    			
    					foreach($curr_leaders[$min_id] AS $k=>$people_id)$out.=esc_html(church_admin_get_person($people_id)).'<br/>';
    				}else{$out.=__('No leaders assigned yet','church-admin').'<br/>';}
    			}
    			$out.='</p>';
			
				//grab people
				$sql='SELECT CONCAT_WS(" ", a.first_name,a.prefix,a.last_name) AS name,b.ID FROM '.CA_PEO_TBL.' a, '.CA_MET_TBL.' b  WHERE b.ID="'.esc_sql($row->id).'" AND a.people_id=b.people_id AND b.meta_type="smallgroup" '.$memb_sql.' ORDER BY a.last_name,a.first_name';
			
				$peopleresults=$wpdb->get_results($sql);
				if(!empty($peopleresults))
				{
					$out.='<p><strong>'.__('Group Members','church-admin').'</strong><br/>';
					foreach($peopleresults AS $people){$out.=esc_html($people->name).'<br/>';}
					$out.='</p>';
				}
			}//end show
		}
	
	
		
	
	}
	
	return $out;
}

?>