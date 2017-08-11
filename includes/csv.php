<?php

/**
 *
 * outputs address list csv according to filters
 * 
 * @author  Andy Moyle
 * @param    
 * @return   application/octet-stream
 * @version  1.03
 *
 * rewritten 7th July 2016 to use filters from filter.php
 * refactored 11th April 2016 to remove multi-service bug
 * 
 */
function church_admin_people_csv()
{
	global $wpdb;
	;
	$out='';
	$group_by='';
	$member_types=$genders=$people_types=$sites=$smallgroups=$ministries=array();
	$genderSQL=$maritalSQL=$memberSQL=$peopleSQL=$smallgroupsSQL=$ministriesSQL=$filteredby=array();
	
	foreach($_GET['check'] AS $key=>$data)
	{
		//extract posted data
		$temp=explode('/',$data);
		switch($temp[0])
		{
			case 'ma': 	$marital[]=stripslashes($temp[1]);			break;
			case 'ge': 	$genders[]=stripslashes($temp[1]);			break;
			case 'mt': 	$member_types[]=stripslashes($temp[1]);		break;
			case 'pe':	$people_types[]=stripslashes($temp[1]);		break;
			case 'si':	$sites[]=stripslashes($temp[1]);			break;
			case 'gp':	$smallgroups[]=stripslashes($temp[1]);		break;
			case 'mi':	$ministries[]=stripslashes($temp[1]);		break;
		}
	}
	//create clauses for different
	//marital
	$church_admin_marital_status=array(0=>__('N/A','church-admin'),1=>__('Single','church-admin'),2=>__('Co-habiting','church-admin'),3=>__('Married','church-admin'),4=>__('Divorced','church-admin'),5=>__('Widowed','church-admin'));
	if(!empty($marital)&&is_array($marital))
	{
		foreach($church_admin_marital_status AS $key=>$status)
		{
			if(in_array(sanitize_title($status),$marital))$maritalSQL[]='a.marital_status="'.$status.'"';
		}
	}
	//gender
	if(!empty($genders))
	{
	
		$sex=get_option('church_admin_gender');
		foreach($sex AS $key=>$gender)
		{
			
			if(in_array(sanitize_title($gender),$genders))
			{
				$genderSQL[]='(a.sex="'.intval($key).'")';
				$filteredby[]=$gender;
			}
		}	

	}
	
	//end gender section
	//member types
	if(!empty($member_types)&&is_array($member_types))			
	{	
		
		$allmembers=$wpdb->get_results('SELECT * FROM '.CA_MTY_TBL);
		
		if(!empty($allmembers))
		{
			foreach($allmembers AS $onetype)
			{
				//print_r($onetype);
				if(in_array(sanitize_title($onetype->member_type),$member_types))
				{
					$memberSQL[]='(a.member_type_id="'.$onetype->member_type_id.'" ANd a.member_type_id=f.member_type_id)';
					$filteredby[]=$onetype->member_type;
				}
			}
		}
	}//end member_types
	
	//people types
	$ptypes=get_option('church_admin_people_type');
	if(!empty($people_types))
	{
	
		if(!in_array('all',$people_types))//only do if all not selected
		{
			$ptypes=get_option('church_admin_people_type');
			
			foreach($ptypes AS $key=>$ptype)
			{
				if(in_array(sanitize_title($ptype),$people_types))
				{
					$peopleSQL[]='(a.people_type_id="'.intval($key).'")';
					$filteredby[]=$ptype;
				}
			}	
		}
	}//end people type section
	
	//sites
	
	if(!empty($sites)&&is_array($sites))			
	{	
		if(!in_array('all',$sites))//only do if all not selected
		{
			$campuses=$wpdb->get_results('SELECT * FROM '.CA_SIT_TBL);
			
			if(!empty($campuses))
			{
				foreach($campuses AS $campus)
				{
					if(in_array(sanitize_title($campus->venue),$sites))
					{
						$sitesSQL[]='(a.site_id="'.intval($campus->site_id).'")';
						$filteredby[]=$campus->venue;
					}
				}
			}
		}	
	}//end sites
	
	//small groups
	
	if(!empty($smallgroups)&&is_array($smallgroups))			
	{	
		
		if(!in_array('all',$smallgroups))//only do if all not selected
		{
			$sgs=$wpdb->get_results('SELECT * FROM '.CA_SMG_TBL);
			if(!empty($sgs))
			{
				foreach($sgs AS $sg)
				{
					
					if(in_array(sanitize_title($sg->group_name),$smallgroups))
					{
						$smallgroupsSQL[]='(c.ID="'.intval($sg->id).'" AND c.meta_type="smallgroup" AND c.people_id=a.people_id)';
						$filteredby[]=$sg->group_name;
					}
				}
			}
		}
		if(in_array('no-group',$smallgroups))
		{
			 $smallgroupsSQL=array('a.people_id NOT IN (SELECT people_id FROM '.CA_MET_TBL.' WHERE meta_type="smallgroup")');
			 $group_by=' GROUP BY a.people_id ';	}	
	}//end smallgroups

	
	//ministries
	if(!empty($ministries)&&is_array($ministries))			
	{	
		if(!in_array('all',$ministries))//only do if all not selected
		{
			$mins=$wpdb->get_results('SELECT * FROM '.CA_MIN_TBL);
			
			if(!empty($mins))
			{
				foreach($mins AS $min)
				{
					if(in_array(sanitize_title($min->ministry),$ministries))
					{
						$ministriesSQL[]='(c.ID="'.intval($min->ID).'" AND c.meta_type="ministry" AND c.people_id=a.people_id)';
						$filteredby[]=$min->ministry;
					}
				}
			}
		}	
	}//end smallgroups
	$other=$tbls='';
	$columns=array('a.first_name','a.middle_name','a.prefix','a.last_name','a.date_of_birth','a.people_id','a.household_id','a.people_type_id','a.marital_status','a.email','a.mobile','a.sex','b.phone','b.address');
	$tables=array(CA_PEO_TBL.' a',CA_HOU_TBL.' b');
	$table_header=array(__('First Name','church-admin'),__('Last Name','church-admin'),__('Date of Birth','church-admin'),__('People Type','church-admin.'),__('Marital Status','church-admin'),__('Phone','church-admin'),__('Mobile','church-admin'),__('Email','church-admin'),__('Address','church-admin'));
	if(!empty($genderSQL)) 		$other.=' AND ('. implode(" OR ",$genderSQL).')';
	if(!empty($maritalSQL))		$other.=' AND ('. implode(" OR ",$maritalSQL).')';
	if(!empty($peopleSQL)) 		$other.=' AND ('. implode(" OR ",$peopleSQL).')';
	if(!empty($sitesSQL)) 		{
									$other.=' AND ('. implode(" OR ",$sitesSQL).') AND a.site_id=d.site_id';
									$tables[]=CA_SIT_TBL.' d';
									$columns[]='d.venue';
									$table_header[]=__('Site','church-admin');
								}
	if(!empty($smallgroupsSQL)) 	{
									$other.=' AND ('. implode(" OR ",$smallgroupsSQL).') AND c.ID=e.id';
									$columns[]='e.group_name';
									$tables[]=CA_MET_TBL.' c'; 
									$tables[]=CA_SMG_TBL.' e';
									$table_header[]=__('Small Group','church-admin');
								}
	if(!empty($memberSQL)) 		{
									$other.=' AND ('. implode(" OR ",$memberSQL).')';
									$columns[]='f.member_type';
									$tables[]=CA_MTY_TBL.' f';
								}
	if(!empty($ministriesSQL)) 	{$other.=' AND ('. implode(" OR",$ministriesSQL).')';$columns[]=', g.ministry ';$tables['g']=CA_MIN_TBL;$tables['c']=CA_MET_TBL;$table_header[]=__('Ministries','church-admin');}
	
	foreach($tables AS $letter=>$table)$tbls.=', '.$table.' '.$letter;
	$church_admin_marital_status=array(0=>__('N/A','church-admin'),1=>__('Single','church-admin'),2=>__('Co-habiting','church-admin'),3=>__('Married','church-admin'),4=>__('Divorced','church-admin'),5=>__('Widowed','church-admin'));
	$sql='SELECT '.implode(", ",$columns).' FROM '.implode(", ",array_filter($tables)).' WHERE a.household_id=b.household_id '.$other.' '.$group_by.' ORDER BY a.last_name';

	$results=$wpdb->get_results($sql);
	
	if(!empty($results))
	{
	
		$csv='"'.implode('","',$table_header).'"'."\r\n";
		foreach($results AS $row)
		{
			if(!empty($row->first_name)){$csv.='"'.iconv('UTF-8', 'ISO-8859-1',$row->first_name).'",';}else $csv.='"",';
			if(!empty($row->last_name)){$csv.='"'.iconv('UTF-8', 'ISO-8859-1',$row->last_name).'",';}else $csv.='"",';
			if(!empty($row->date_of_birth)){$csv.='"'.iconv('UTF-8', 'ISO-8859-1',$row->date_of_birth).'",';}else $csv.='"",';
			if(!empty($ptypes[$row->people_type_id])){$csv.='"'.iconv('UTF-8', 'ISO-8859-1',$ptypes[$row->people_type_id]).'",';}else $csv.='"",';
			if(!empty($row->marital_status)){$csv.='"'.iconv('UTF-8', 'ISO-8859-1',$row->marital_status).'",';}else $csv.='"'.__('N/A','church-admin').'",';
			if(!empty($row->phone)){$csv.='"'.iconv('UTF-8', 'ISO-8859-1',$row->phone).'",';}else $csv.='"",';
			if(!empty($row->mobile)){$csv.='"'.iconv('UTF-8', 'ISO-8859-1',$row->mobile).'",';}else $csv.='"",';
			if(!empty($row->email)){$csv.='"'.$row->email.'",';}else $csv.='"",';
			if(!empty($row->address)){$csv.='"'.iconv('UTF-8', 'ISO-8859-1',$row->address).'",';}else $csv.='"",';
			if(!empty($row->venue)){$csv.='"'.iconv('UTF-8', 'ISO-8859-1',$row->venue).'",';}else $csv.='"",';
			if(!empty($row->group_name)){$csv.='"'.iconv('UTF-8', 'ISO-8859-1',$row->group_name).'",';}else $csv.='"",';
			$csv.="\r\n";
		}
		
	header('Content-Description: File Transfer');
	header('Content-Type: application/octet-stream');
	header('Content-Disposition: attachment; filename="filtered-address-list.csv"');
	header('Content-Transfer-Encoding: binary');
	header('Expires: 0');
	header('Cache-Control: must-revalidate');
	header('Pragma: public');
	header("Content-Disposition: attachment; filename=\"filtered-address-list.csv\"");
	echo $csv;
	}
	exit();
		
	
	
	
	
	
	
	/*if(!empty($add))$address=' LEFT JOIN '.CA_HOU_TBL.' ON '.CA_PEO_TBL.'.household_id='.CA_HOU_TBL.'.household_id ';
	if(!empty($sg))$sg=' LEFT JOIN '.CA_SMG_TBL.' ON '.CA_PEO_TBL.'.smallgroup_id='.CA_SMG_TBL.'.id ';
	if(!empty($sex))foreach($sex AS $key=>$value)$gender[]=CA_PEO_TBL.'.sex="'.$value.'"';
	if(!empty($gender)){$genders=' WHERE ('.implode(' || ',$gender).') ';}else{$genders=' WHERE  ('.CA_PEO_TBL.'.sex=1 || '.CA_PEO_TBL.'.sex =0) ';}

	
	if(!empty($people_type_id))foreach($people_type_id AS $key=>$value){if(ctype_digit($value))$peoplesql[]=CA_PEO_TBL.'.people_type_id='.$value;}
	if(!empty($peoplesql)) {$people_sql=' AND ('.implode(' || ',$peoplesql).') ';}else{$people_sql='';}
	
	if(!empty($member_type_id))foreach($member_type_id AS $key=>$value){if(ctype_digit($value))$membsql[]=CA_PEO_TBL.'.member_type_id='.$value;}
	
	if(!empty($membsql)) {$memb_sql=' AND ('.implode(' || ',$membsql).') ';}else{$memb_sql='';}
	if(isset($member_type_id)&&$member_type_id==0)$memb_sql='';
	$sql='SELECT '.CA_PEO_TBL.'.*';
	if(!empty($sg)) $sql.=','.CA_SMG_TBL.'.group_name';
	if(!empty($add))$sql.=','.CA_HOU_TBL.'.address ';
	$sql.=' FROM '.CA_PEO_TBL.$address.$sg.$genders.$people_sql.$memb_sql.'  ORDER BY last_name';
	
	$results = $wpdb->get_results($sql);
	if($results)
	{
		$csv="First Name, Last Name, Email, Mobile";
		if(!empty($add))$csv.=',Address';
		if(!empty($sg))$csv.=',Small Group';
		$csv.="\r\n";
		foreach($results AS $row)
		{
			
			$csv.='"'.iconv('UTF-8', 'ISO-8859-1',$row->first_name).'","';
			if(!empty($row->prefix))$csv.=iconv('UTF-8', 'ISO-8859-1',$row->prefix).' ';
			$csv.=iconv('UTF-8', 'ISO-8859-1',$row->last_name).'","'.iconv('UTF-8', 'ISO-8859-1',$row->email).'","'.$row->mobile.'"';
			if(!empty($add))$csv.=',"'.iconv('UTF-8', 'ISO-8859-1',$row->address).'"';
			if(!empty($sg))$csv.=',"'.iconv('UTF-8', 'ISO-8859-1',$row->group_name).'"';
			$csv.="\r\n";
		}
		
		    header('Content-Description: File Transfer');
			header('Content-Type: application/octet-stream');
			header('Content-Disposition: attachment; filename='.basename($file));
			header('Content-Transfer-Encoding: binary');
			header('Expires: 0');
			header('Cache-Control: must-revalidate');
			header('Pragma: public');
			header("Content-Disposition: attachment; filename=\"people.csv\"");
			echo $csv;
			exit();
	}	*/	
}


?>