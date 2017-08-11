<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

//define('EMAIL_TEST',TRUE);


//2014-02-24 fixed encoding error


function church_admin_test_email($email)
{
	add_filter( 'wp_mail_from_name', function( $from ) {return "My website";});
	add_filter( 'wp_mail_from', function( $email ) {return "support@churchadminplugin.com";});
	add_filter('wp_mail_content_type','church_admin_email_type');
	$message='<p>Test email from the church admin plugin</p>';
	if(!empty($email)&&is_email($email)){$to=$email;}else{$to=get_option('admin_email');}
	if(wp_mail($to,'Church Admin Test Email',$message)){echo'Success';};
	echo'<pre>';
	print_r($GLOBALS['phpmailer']);
	echo'</pre>';
	remove_filter('wp_mail_content_type','church_admin_email_type');
}


function church_admin_delete_email($email_id)
{
	global $wpdb;
	$row=$wpdb->get_row('SELECT * FROM '.CA_EBU_TBL.' WHERE email_id="'.esc_sql($email_id).'"');
	if(!empty($data->filename))$paths=maybe_unserialize($data->filename);
	if(!empty($paths)){foreach($paths AS $key=>$value) unlink($value);}
	$wpdb->query('DELETE FROM '.CA_EBU_TBL.' WHERE email_id="'.esc_sql($email_id).'"');
	echo'<div class="notice notice-success inline">'.__('Email deleted','church-admin').'</div>';
	church_admin_email_list();
}
/**
 * Church Email list
 *
 * Author:     Andy Moyle
 * Author URI: http://www.churchadminplugin.com
 *
 * 
 */
function church_admin_email_list()
{
	global $wpdb;
	$items=$wpdb->get_var('SELECT COUNT(*) FROM '.CA_EBU_TBL.' WHERE recipients!=""' );
	require_once(plugin_dir_path(dirname(__FILE__)).'includes/pagination.class.php');
    if($items > 0)
    {
	
	$p = new pagination;
	$p->items($items);
	$p->limit(get_option('church_admin_page_limit')); // Limit entries per page
	$p->target("admin.php?page=church_admin/index.php&tab=communications&action=email_list");
	if(!isset($p->paging))$p->paging=1; 
	if(!isset($_GET[$p->paging]))$_GET[$p->paging]=1;
	$p->currentPage((int)$_GET[$p->paging]); // Gets and validates the current page
	$p->calculate(); // Calculates what to show
	$p->parameterName('paging');
	$p->adjacents(1); //No. of page away from the current page
	if(!isset($_GET['paging']))
	{
	    $p->page = 1;
	}
	else
	{
	    $p->page = intval($_GET['paging']);
	}
        //Query for limit paging
	$limit = esc_sql("LIMIT " . ($p->page - 1) * $p->limit  . ", " . $p->limit);
    $result=$wpdb->get_results('SELECT * FROM '.CA_EBU_TBL.' WHERE recipients!="" ORDER BY send_date DESC '.$limit );
	if(!empty($result))
	{
		echo'<h2>'.__('Sent Emails','church-admin').'</h2><table class="widefat striped"><thead><tr><th>'.__('Delete','church-admin').'</th><th>'.__('Date','church-admin').'</th><th>'.__('Number of recipients','church-admin').'</th><th>'.__('Subject','church-admin').'</th><th>'.__('Excerpt','church-admin').'</th><th>'.__('Resend','church-admin').'?</th><th>'.__('Resend to new recipients','church-admin').'</th><th>'.__('Edit and resend','church-admin').'</th></tr></thead><tfoot><tr><th>'.__('Delete','church-admin').'</th><th>'.__('Date','church-admin').'</th><th>'.__('Number of recipients','church-admin').'</th><th>'.__('Subject','church-admin').'</th><th>'.__('Excerpt','church-admin').'</th><th>'.__('Resend','church-admin').'?</th><th>'.__('Resend to new recipients','church-admin').'</th><th>'.__('Edit and resend','church-admin').'</th></tr></tfoot><tbody>';
		foreach($result AS $row)
		{
			$startsAt = strpos($row->message, "<!--salutation-->") + strlen("{FINDME}");
			$endsAt = strpos($row->message, "<!--News,events-->", $startsAt);
			$message = strip_tags(substr($row->message, $startsAt+17, $endsAt - $startsAt));
			$message=substr($message,0,500);
			$delete='<a onclick="return confirm(\''.__('Are you sure?','church-admin').'\');" href="'.wp_nonce_url('admin.php?page=church_admin/index.php&tab=communications&action=delete_email&email_id='.intval($row->email_id),'delete_email').'">Delete</a>';
			$resend='<a onclick="return confirm(\''.__('Are you sure?','church-admin').'\');" href="'.wp_nonce_url('admin.php?page=church_admin/index.php&tab=communications&action=resend_email&email_id='.intval($row->email_id),'resend_email').'">Resend to previous recipients</a>';
			$new='<a href="'.wp_nonce_url('admin.php?page=church_admin/index.php&tab=communications&action=resend_new&email_id='.intval($row->email_id),'resend_new').'">'.__('Resend to new recipients','church-admin').'</a>';
			$reedit='<a href="'.wp_nonce_url('admin.php?page=church_admin/index.php&tab=communications&action=edit_resend&email_id='.intval($row->email_id),'resend_new').'">'.__('Edit and send','church-admin').'</a>';
			
			echo'<tr><td>'.$delete.'</td><td>'.mysql2date(get_option('date_format'),$row->send_date).'</td><td>'.count(maybe_unserialize($row->recipients)).'</td><td>'.$row->subject.'</td><td>'.$message.'</td><td>'.$resend.'</td><td>'.$new.'</td><td>'.$reedit.'</td></tr>';
		}
	}
	}
}
/**
 * Send email function
 *
 * Author:     Andy Moyle
 * Author URI: http://www.churchadminplugin.com
 *
 * 
 */
function church_admin_send_email($email_id=NULL)
{
	global $wpdb;
	$member_type=church_admin_member_type_array();
	$gender=get_option('church_admin_gender');
	
	if(!empty($email_id))$data=$wpdb->get_row('SELECT * FROM '.CA_EBU_TBL.' WHERE email_id="'.esc_sql($email_id).'"');
	if(!empty($_POST['send-email']))
	{
		
		//handle uploaded attachments
		$upload_dir = wp_upload_dir();
		$attachments=array();
		if  ($_FILES['userfile1']['size']>0)
		{
			$attachments['1'] = $upload_dir['basedir'].'/church-admin-cache/'.$_FILES['userfile1']['name'];
			$tmpName  = $_FILES['userfile1']['tmp_name'];
			move_uploaded_file($tmpName,$attachments['1']);
		}
		if  ($_FILES['userfile2']['size']>0)
		{
			$attachments['2'] = $upload_dir['basedir'].'/church-admin-cache/'.$_FILES['userfile2']['name'];
			$tmpName  = $_FILES['userfile2']['tmp_name'];
			move_uploaded_file($tmpName,$attachments['2']);
		}
		if  ($_FILES['userfile3']['size']>0)
		{
			$attachments['3'] = $upload_dir['basedir'].'/church-admin-cache/'.$_FILES['userfile3']['name'];
			$tmpName  = $_FILES['userfile3']['tmp_name'];
			move_uploaded_file($tmpName,$attachments['3']);
		}

		//handle template & message
		$message=file_get_contents(plugin_dir_path(dirname(__FILE__)).'includes/email_template.html');
		$entered_message=stripslashes(mb_convert_encoding(nl2br($_POST['message']), 'HTML-ENTITIES', 'UTF-8'));
		$message=str_replace('[intro]',$entered_message,$message);
		//sort image floating
		$message=str_replace('class="alignleft','style="float:left;margin:5px;" class="',$message);
		$message=str_replace('class="alignright','style="float:right;margin:5px;" class="',$message);
		$message=str_replace('class="aligncenter','style="  display: block;  margin-left: auto;  margin-right: auto;" class="',$message);
		$message=str_replace('<ol>','<ol style="margin-left:5px;">',$message);
		$message=str_replace('<ul>','<ul style="margin-left:5px;">',$message);
		$message=str_replace('[subject]',$_POST['subject'],$message);//add subject
		if(get_option('church_admin_feedburner'))
		{
			$RSS='&nbsp;<a href="http://feedburner.google.com/fb/a/mailverify?uri='.get_option('church_admin_feedburner').'&amp;loc=en_US">Subscribe to '.get_option('blogname').' blog by Email</a>';
		}
		$message=str_replace('[RSS]',$RSS,$message);
		//twitter url
		if(get_option('church_admin_twitter')){$twitter='<a href="http://twitter.com/#!/'.get_option('church_admin_twitter').'" style="text_decoration:none" title="Follow us on Twitter">Twitter</a>&nbsp; ';}else{$twitter='';}
		$message=str_replace('[TWITTER]',$twitter,$message);
		//facebook url
		if(get_option('church_admin_facebook')){$facebook='<a href="'.get_option('church_admin_facebook').'" style="text_decoration:none" title="Follow us on Facebook">Facebook</a> &nbsp;';}else{$facebook='';}
		$message=str_replace('[FACEBOOK]',$facebook,$message);
		$message=str_replace('[BLOGINFO]','<a href="'.get_bloginfo('url').'">'.get_bloginfo('url').'</a>',$message);
		$message=str_replace('[HEADER_IMAGE]','<img class="header_image" src="'.get_option('church_admin_email_image').'" alt="" >',$message);
		//copyright year
		$message=str_replace('[year]',date('Y'),$message);
		$filename='Email-'.date('Y-m-d-H-i-s').'.html';
		$message=str_replace('[cache]','<p style="font-size:smaller;text-align:center;margin:0 auto;">'.__('Having trouble reading this?','church-admin').' - <a href="'.content_url('/uploads/church-admin-cache/'.$filename).'">'.__('view in your web browser','church-admin').'</a></p>',$message);
		
		$handle=fopen($upload_dir['basedir'].'/church-admin-cache/'.$filename,"w")OR DIE("Couldn't open");
		fwrite($handle,$message);  
		fclose($handle);
		$sqlsafe['message']=esc_sql($message);//make message sqlsafe!
		//save build message
		$email_id=$wpdb->get_var('SELECT email_id FROM '.CA_EBU_TBL.' WHERE subject="'.esc_sql(stripslashes($_POST['subject'])).'" AND message="'.esc_sql($message).'" AND from_email="'.esc_sql(stripslashes($_POST['from_email'])).'" AND from_name="'.esc_sql(stripslashes($_POST['from_name'])).'" AND filename="'.esc_sql(maybe_serialize($attachments)).'"');
		if($email_id)
		{//update
			$wpdb->query('UPDATE '.CA_EBU_TBL.' SET subject="'.esc_sql(stripslashes($_POST['subject'])).'",message="'.esc_sql($message).'", from_email="'.esc_sql(stripslashes($_POST['from_email'])).'" ,from_name="'.esc_sql(stripslashes($_POST['from_name'])).'",filename="'.esc_sql(maybe_serialize($attachments)).'" WHERE email_id="'.esc_sql($email_id).'"');
		}//end update
		else
		{//insert
			$sql='INSERT INTO '.CA_EBU_TBL.' (subject,message,from_email,from_name,send_date,filename,content) VALUES("'.esc_sql(stripslashes($_POST['subject'])).'","'.esc_sql($message).'","'.esc_sql(stripslashes($_POST['from_email'])).'","'.esc_sql(stripslashes($_POST['from_name'])).'","'.date('Y-m-d').'","'.esc_sql(maybe_serialize($attachments)).'","'.esc_sql($entered_message).'")';
			$wpdb->query($sql);
			$email_id=$wpdb->insert_id;
		}//insert    
		//when to send!
		$schedule=NULL;
		if(!empty($_POST['send_date']))
		{
			$check=church_admin_dateCheck($_POST['send_date'], 5000);
			if($check){$schedule=esc_sql($_POST['send_date']);}
		}
		if(!empty($schedule))
		{
			//set up wp_cron
			if( !wp_next_scheduled('church_admin_bulk_email'))wp_schedule_event(time(), 'hourly', 'church_admin_bulk_email');
		}
		//find recipients
		
	if(!empty($_POST['recipients']))
	{
				$names=array();
				$ids=maybe_unserialize(church_admin_get_people_id(stripslashes($_POST['recipients'])));
				
				foreach($ids AS $value){$names[]='people_id = "'.esc_sql($value).'"';}
				$sql='SELECT  email,first_name FROM '.CA_PEO_TBL.' WHERE email!="" AND '.implode(' OR ',$names).' AND email_send=1 GROUP BY email';
	}
	else
	{	
		$group_by='';
	$member_types=$genders=$people_types=$sites=$smallgroups=$ministries=array();
	$maritalSQL=$genderSQL=$memberSQL=$peopleSQL=$smallgroupsSQL=$ministriesSQL=$filteredby=array();
	
	foreach($_POST['check'] AS $key=>$data)
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
	//create clauses for different
	$church_admin_marital_status=array(0=>__('N/A','church-admin'),1=>__('Single','church-admin'),2=>__('Co-habiting','church-admin'),3=>__('Married','church-admin'),4=>__('Divorced','church-admin'),5=>__('Widowed','church-admin'));
	if(!empty($marital)&&is_array($marital))
	{
		foreach($church_admin_marital_status AS $key=>$status)
		{
			if(in_array(sanitize_title($status),$marital))$maritalSQL[]='a.marital_status="'.$status.'"';
		}
	}
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
		}	
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
						$ministriesSQL[]='(c.ID="'.intval($min->ID).'" AND c.ID=g.ID AND c.meta_type="ministry" AND c.people_id=a.people_id)';
						$filteredby[]=$min->ministry;
					}
				}
			}
		}	
	}//end smallgroups
	$other=$tbls='';
	 $group_by=' GROUP BY a.people_id ';
	 $columns=array('a.people_id','a.household_id','a.first_name','a.last_name','a.people_type_id','a.email','a.mobile','a.sex','b.phone','b.address');
	$tables=array(CA_PEO_TBL.' a',CA_HOU_TBL.' b');
	$table_header=array(__('Edit','church-admin'),__('Delete','church-admin'),__('Name','church-admin'),__('People Type','church-admin.'),__('Phone','church-admin'),__('Mobile','church-admin'),__('Email','church-admin'),__('Address','church-admin'));
	if(!empty($genderSQL)) 		$other.=' AND ('. implode(" OR ",$genderSQL).')';
	if(!empty($peopleSQL)) 		$other.=' AND ('. implode(" OR ",$peopleSQL).')';
	if(!empty($maritalSQL)) 	$other.=' AND ('. implode(" OR ",$maritalSQL).')';
	if(!empty($sitesSQL)) 		{
									$other.=' AND ('. implode(" OR ",$sitesSQL).') AND a.site_id=d.site_id';
									$tables[]=CA_SIT_TBL.' d';
									$columns[]='d.venue';
								}
	if(!empty($smallgroupsSQL)) {
									$other.=' AND ('. implode(" OR ",$smallgroupsSQL).') AND c.ID=e.id';
									$columns[]='e.group_name';
									$tables[]=CA_MET_TBL.' c'; 
									$tables[]=CA_SMG_TBL.' e';
								}
	if(!empty($memberSQL)) 		{
									$other.=' AND ('. implode(" OR ",$memberSQL).')';
									$columns[]='f.member_type';
									$tables[]=CA_MTY_TBL.' f';
								}
	if(!empty($ministriesSQL)) 	{
									$other.=' AND ('. implode(" OR",$ministriesSQL).')';
									$columns[]=', g.ministry ';
									$tables['g']=CA_MIN_TBL.' g';
									$tables['c']=CA_MET_TBL.' c';
									}
	
	foreach($tables AS $letter=>$table)$tbls.=', '.$table.' '.$letter;
	
	$sql='SELECT a.first_name, a.email, a.people_id FROM '.implode(", ",array_filter($tables)).' WHERE a.household_id=b.household_id '.$other.' GROUP BY a.email  ORDER BY a.last_name';
}

	//church_admin_debug($sql);	
	//end build recipients sql	
	
	$unsub=get_option('church_admin_unsubscribe');
	update_option('church_admin_from_name',stripslashes($_POST['from_name']));
	update_option('church_admin_from_email',stripslashes($_POST['from_email']));	
		$results=$wpdb->get_results($sql);
		$emails=array();
		if($results)
		{
			foreach($results AS $row)
			{
				//add unsub link
				$unsubLink='<a href="'.get_permalink($unsub).'/?ca_unsub='.md5($row->people_id).'">'.__('Unsubscribe','church-admin').'</a>';
				if(!empty($unsub))$message=str_replace('<!--Unsubscribe-->',$unsubLink,$message);
				
				if(!empty($schedule)||get_option('church_admin_cron')!='immediate')
                {
                	
					$emails[]=$row->email;
					if(defined('EMAIL_TEST')){church_admin_debug($row->email."\r\n");echo'<p>'.esc_html($row->email).' would have got it</p>';}
					elseif(QueueEmail($row->email,esc_html(stripslashes($_POST['subject'])),str_replace("<!--salutation-->",__('Dear','church-admin').' '.esc_html($row->first_name).',',$message),'',esc_html(stripslashes($_POST['from_name'])),esc_html(stripslashes($_POST['from_email'])),$attachments,$schedule)) echo'<p>'.esc_html($row->email).' queued</p>';
				}
				else
				{
					$email=$row->from_email;
					$from=$row->from_name;
						add_filter( 'wp_mail_from_name','church_admin_from_name' );
						add_filter( 'wp_mail_from', 'church_admin_from_email');
					
					add_filter('wp_mail_content_type','church_admin_email_type');
					if(!empty($row->email))
					{
						//use native wordpress
						$emails[]=$row->email;
						$headers="From: ".esc_html(stripslashes($_POST['from_name']))." <".esc_html(stripslashes($_POST['from_email'])).">\n";
						if(defined('EMAIL_TEST')){church_admin_debug($row->email."\r\n");echo'<p>'.esc_html($row->email).' would have got it</p>';}
						elseif(wp_mail($row->email,esc_html(stripslashes($_POST['subject'])),str_replace('<!--salutation-->',__('Dear','church-admin').' '.$row->first_name.',',$message),$headers,$attachments)){echo'<p>'.esc_html($row->email).' sent immediately</p>';}
						else {echo $GLOBALS['phpmailer']->ErrorInfo;}
					} 
					remove_filter('wp_mail_content_type','church_admin_email_type');
				}
			}
			$wpdb->query('UPDATE '.CA_EBU_TBL.' SET recipients="'.esc_sql(maybe_serialize($emails)).'" WHERE email_id="'.esc_sql($email_id).'"');
		}else{echo '<div class="error fade">'.__('No email addresses found','church-admin').'</p>';}
		//send or queue
		
		
		
	}
	else
	{
	
		
		echo'<div class="wrap"><h2>'.__('Bulk Email','church-admin').'</h2>';
		if(!function_exists('mb_convert_encoding')) echo'<p style="color:red">'.__('Email sending will not work, because your hosting company needs to enable PHP Multibyte Support / mbstring','church-admin').'</p>';
		echo'<form action="" enctype="multipart/form-data" method="post" >';
		church_admin_recipients();
		//subject
		echo'<table class="form-table">	';
		echo'<tr><th scope="row">'.__('Subject','church-admin').'</th><td><input type="text" name="subject" ';
		if(!empty($data->subject)) echo ' value="'.esc_html($data->subject).'"';
		echo'/></td></tr>';
		
		echo '<tr><th scope="row" colspan=2><span id="me" style="text-decoration:underline">'.__('Use me as from name and email values','church-admin').'</span></th></tr>';
		$current_user = wp_get_current_user();
		$user=$wpdb->get_row('SELECT CONCAT_WS(" ",first_name,middle_name,prefix,last_name) AS name, email FROM '.CA_PEO_TBL.' WHERE user_id="'.intval($current_user->ID).'"');
		echo'<script type="text/javascript">
		jQuery(document).ready(function($){  
			$("#me").click(function() {
				$("#from_name").val("'.esc_html($user->name).'");
				$("#from_email").val("'.esc_html($user->email).'");
			});
		});
		</script>';
		echo'<tr><th scope="row">'.__('From name','church-admin').'</th><td><input type="text" id="from_name" name="from_name"  ';
		$from_name=get_option('church_admin_from_name');
		if(!empty($from_name)) echo ' value="'.esc_html($from_name).'"';
		echo'/></td></tr>';
		echo'<tr><th scope="row">'.__('From email','church-admin').'</th><td><input type="text" id="from_email" name="from_email"  ';
		$from_email=get_option('church_admin_from_email');
		if(!empty($from_email)) echo ' value="'.esc_html($from_email).'"';
		echo'/></td></tr>';
		//attachments
		echo'<tr><th scope="row">'.__('Attachment','church-admin').' 1 (max 500KB):</th><td><input type="file" name="userfile1"/></td></tr>';
		echo'<tr><th scope="row">'.__('Attachment','church-admin').' 2 (max 500KB):</th><td><input type="file" name="userfile2"/></td></tr>';
		echo'<tr><th scope="row">'.__('Attachment','church-admin').' 3 (max 500KB):</th><td><input type="file" name="userfile3"/></td></tr>';
		echo'</tbody></table>';
		$content = '';
		$editor_id = 'message';
		echo'<div id="poststuff">';
		$content='';
		if(!empty($data->content))$content=$data->content;
		wp_editor($content,'message');
		echo'</div>';
		echo'<table class="form-table"><tbody>';
		echo'<tr><th scope="row">Send now </th><td><input type=checkbox id="now" name=schedule value="now" checked="checked"/></td></tr>';
		echo'<tr><th scope="row">Or schedule?</th><td><input name="send_date" id="send_date" type="text"  /></td></tr>';
		echo'<script type="text/javascript">jQuery(document).ready(function($){   $(\'#send_date\').datepicker({
            dateFormat : "'."yy-mm-dd".'", changeYear: true ,yearRange: "2015:'.date('Y',time()+60*60*24*365*10).'"
         });
		 $("#send_date").change(function(){$("#now").prop( "checked", false );});
			});</script>';
		echo'<tr><th scope="row">&nbsp;</th><td><input type="hidden" name="send-email"  value="TRUE"/><input class="button-primary" type="submit" value="'.__('Send','church-admin').'" disabled="disabled"/></td></tr>';

		echo'</tbody></table></form></div>';
	}
	
}

 /**
 *
 * Recipients form element
 * 
 * @author  Andy Moyle
 * @param    
 * @return   
 * @version  0.945
 *
 * 
 * 
 */ 
function church_admin_recipients($type='email')
{
	global $wpdb;
	if(!empty($type) && $type=='sms'){$smsoremail='mobile';}else{$smsoremail='email';}
	$member_type=church_admin_member_type_array();
	echo'<p><label>'.__('Type in recipient names, separated by a comma (filters will be ignored)','church-admin').'</label>'.church_admin_autocomplete('recipients','friends','to','').'</p>';
	echo'<p>'.__('Or use the filters below','church-admin').'<span id="filtered-response">'.__('Everyone will get this, unless you add some filters','church-admin').'</span></p>';
	require_once(plugin_dir_path(__FILE__).'/filter.php');
    church_admin_directory_filter(FALSE);
   
    $nonce = wp_create_nonce("church_admin_filter");
    echo'<script type="text/javascript">
		jQuery(document).ready(function($) {
		
	//handle send button disabled while no selections
     $(\':input[type="submit"]\').prop(\'disabled\', true);
     $(\'input[type="text"]\').keyup(function() {
        if($(this).val() != "") {
           $(\':input[type="submit"]\').prop(\'disabled\', false);
           $("#filtered-response").html("");
        }
     });

		
		
			$(".all").on("change", function(){
				var id = this.id;
			
				$("input."+id).prop("checked", !$("."+id).prop("checked"))
			});
		   $(".category").on("change", function(){
				
      			var category_list = [];
      			$("#filters1 :input:checked").each(function(){
        			
        			
        			var category = $(this).val();
        			category_list.push(category);
        			
        		});
      		
      			
      			var data = {
				"action": "church_admin_filter_email",
				"type":"'.$smsoremail.'",
				"data": category_list,
				"nonce": "'.$nonce.'"
				};
	$("#filtered-response").html(\'<p style="text-align:center"><img src="'.admin_url().'/images/wpspin_light-2x.gif"/></p>\');
		// since 2.8 ajaxurl is always defined in the admin header and points to admin-ajax.php
		jQuery.post(ajaxurl, data, function(response) {
			$("#filtered-response").html("<h3>"+response+"</h3>");
			$(\':input[type="submit"]\').prop(\'disabled\', false);
		});
			});
		});
	</script>
	';
}	

function getTweetUrl($url, $text)

{

$maxTitleLength = 120 ;

if (strlen($text) > $maxTitleLength) {

$text = substr($text, 0, ($maxTitleLength-3)).'...';

}

$text=str_replace('"','',$text);

$outputurl='http://twitter.com/share?wrap_links=true&amp;url='.urlencode($url).'&amp;text='.urlencode($text);

$output='<a href="http://twitter.com/share" class="twitter-share-button" data-url="'.$outputurl.'" data-text="'.$text.'" data-count="horizontal">Tweet</a>';

return $output;

}

function church_admin_resend($email_id)
{
	global $wpdb;
	$email=$wpdb->get_row('SELECT * FROM '.CA_EBU_TBL.' WHERE email_id="'.esc_sql($email_id).'"');
	
	if(!empty($email))
	{
		$addresses=maybe_unserialize($email->recipients);
		foreach($addresses AS $key=>$emailadd)
			{
				if(!empty($schedule)||get_option('church_admin_cron')!='immediate')
                {
					
					if(QueueEmail($emailadd,esc_html($email->subject),$email->message,'',$email->from_name,$email->from_email,$email->filename,$email->schedule)) echo'<p>'.esc_html($emailadd).' queued</p>';
				}
				else
				{
					add_filter('wp_mail_content_type','church_admin_email_type');
					if(!empty($emailadd))
					{
						//use native wordpress
						
						$headers="From: ".esc_html($email->from_name)." <".esc_html($email->from_email).">\n";
						
						if(wp_mail($emailadd,$email->subject,$email->message,$headers,$email->filename)){echo'<p>'.esc_html($emailadd).' sent immediately</p>';}
						else {echo $GLOBALS['phpmailer']->ErrorInfo;}
					} 
					remove_filter('wp_mail_content_type','church_admin_email_type');
				}
			}
	

	}
}

function church_admin_resend_new($email_id)
{
 	global $wpdb;
	echo'<h2>'.__('Resending email to new recipients','church-admin').'</h2>';
	//get the original email
	$email=$wpdb->get_row('SELECT * FROM '.CA_EBU_TBL.' WHERE email_id="'.esc_sql($email_id).'"');
	//process sending
	if(!empty($_POST['resend_new']))
	{
		
		//find recipients
		switch($_POST['type'])
		{
			case 'gender':
				$sql='SELECT email, first_name FROM '.CA_PEO_TBL.' WHERE email!="" AND sex="'.esc_sql($_POST['sex']).'"';
			break;
			case 'site':
				$sql='SELECT email, first_name FROM '.CA_PEO_TBL.' WHERE email!="" AND site_id="'.intval($_POST['site_id']).'"';
			break;
			case 'autocomplete': 
				$names=array();
				$ids=maybe_unserialize(church_admin_get_people_id(stripslashes($_POST['recipients'])));
				
				foreach($ids AS $value){$names[]='people_id = "'.esc_sql($value).'"';}
				$sql='SELECT  email,first_name FROM '.CA_PEO_TBL.' WHERE email!="" AND '.implode(' OR ',$names);
				
			break;
			case 'smallgroup':
				$sql='SELECT DISTINCT a.email,a.first_name FROM '.CA_PEO_TBL.' a,'.CA_MET_TBL.' b WHERE a.email!="" AND b.meta_type="smallgroup"  AND b.ID="'.esc_sql($_POST['group_id']).'" AND a.people_id=b.people_id';
			break;
			case 'member_types':
				 $w=array();
				$where='(';
				foreach($_POST['member_type'] AS $key=>$value)if(array_key_exists($value,$member_type))$w[]=' member_type_id='.$value.' ';
				$where.=implode("||",$w).')';
				$sql='SELECT email, first_name FROM '.CA_PEO_TBL.' WHERE email!="" AND "'.$where;
			break;
			case 'individuals':
				$names=array();
				foreach ($_POST['person'] AS $value){$names[]='people_id = "'.esc_sql($value).'"';}
				$sql='SELECT  email,first_name FROM '.CA_PEO_TBL.' WHERE email!="" AND '.implode(' OR ',$names);
			break;
			case 'ministries':
				foreach($_POST['role_id'] AS $key=>$value)$r[]='b.ID='.$value;
				$sql='SELECT  a.email,a.first_name FROM '.CA_PEO_TBL.' a,'.CA_MET_TBL.' b WHERE b.meta_type="ministry" AND b.people_id=a.people_id AND a.email!="" AND ('.implode( " || ",$r).')' ;
			break;
			case 'hope_team':
				foreach($_POST['hope_team_id'] AS $key=>$value)$r[]='b.ID='.$value;
				$sql='SELECT  a.email,a.first_name FROM '.CA_PEO_TBL.' a,'.CA_MET_TBL.' b WHERE b.meta_type="hope_team" AND b.people_id=a.people_id AND a.email!="" AND ('.implode( " || ",$r).')' ;
			break;	
		}
	
		$results=$wpdb->get_results($sql);
		$emails=array();
		if($results)
		{
			foreach($results AS $row)
			{
				if(get_option('church_admin_cron')!='immediate')
                {
					$emails[]=$row->email;
					if(QueueEmail($row->email,esc_html($email->subject),str_replace("<!--salutation-->",__('Dear','church-admin').' '.esc_html($row->first_name).',',$email->message),'',esc_html($email->from_name),esc_html($email->from_email),$email->attachments,$schedule)) echo'<p>'.esc_html($row->email).' queued</p>';
				}
				else
				{
					add_filter('wp_mail_content_type','church_admin_email_type');
					if(!empty($row->email))
					{
						//use native wordpress
						$emails[]=$row->email;
						$headers="From: ".esc_html($row->first_name)." <".esc_html($email->from_email).">\n";
						
						if(wp_mail($row->email,esc_html($email->subject),str_replace('<!--salutation-->',__('Dear','church-admin').' '.$row->first_name.',',$email->message),$headers,$email->attachments)){echo'<p>'.esc_html($row->email).' sent immediately</p>';}
						else {echo $GLOBALS['phpmailer']->ErrorInfo;}
					} 
					remove_filter('wp_mail_content_type','church_admin_email_type');
				}
			}
			
		}else{echo '<div class="error fade">'.__('No email addresses found','church-admin').'</p>';}
	}
	else
	{//form to choose and send
		echo'<h2>'.__('Choose recipients to resend to','church-admin').'</h2>';
		echo'<form action="" method="POST">';
		echo church_admin_recipients();
		echo'<table class="form-table"><tr><th scope="row">&nbsp;</th><td><input type="hidden" name="resend_new" value="TRUE"/><input class="button-primary" type="submit" value="'.__('Send','church-admin').'"/></td></tr>';
		echo'</tbody></table></form>';
	}
}