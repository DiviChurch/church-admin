<?php

function church_admin_cron_pdf()
{
    //setup pdf
    require_once(plugin_dir_path(dirname(__FILE__)).'includes/fpdf.php');
    $pdf=new FPDF();
    $pdf->AddPage('P','A4');
    $pdf->SetFont('Arial','B',24);
    $text=__('How to set up Bulk Email Queuing','church-admin');
    $pdf->Cell(0,10,$text,0,2,'L');
    if (PHP_OS=='Linux')
    {
    $phppath='/usr/local/bin/php -f ';
	
    $cronpath=plugin_dir_path(dirname(__FILE__)).'includes/cronemail.php';
   
	update_option('church_admin_cron_path',$cronpath);
	$command=$phppath.$cronpath;
    
    
    $pdf->SetFont('Arial','',8);
    $text="Instructions for Linux servers and cpanel.\r\nLog into Cpanel which should be ".get_bloginfo('url')."/cpanel using your username and password. \r\nOne of the options will be Cron Jobs which is usually in 'Advanced Tools' at the bottom of the screen. Click on 'Standard' Experience level. that will bring up something like this... ";
    
    $pdf->MultiCell(0, 10, $text,0,'L' );
 
    $pdf->Image(plugin_dir_path( dirname(__FILE__) ).'images/cron-job1.jpg','10','65','','','jpg','');
    $pdf->SetXY(10,180);
    $text="In the common settings option - select 'Once an Hour'. \r\nIn 'Command to run' put this:\r\n".$command."\r\n and then click Add Cron Job. Job Done. Don't forget to test it by sending an email to yourself at a few minutes before the hour! ";
    $pdf->MultiCell(0, 10, $text,0,'L' );
    }
    else
    {
         $pdf->SetFont('Arial','',10);
        $text=__("Unfortunately setting up queuing for email using cron is nonon Linux servers. Please go back to Communication settings and enable the wp-cron option for scheduling sending of queued emails",'church-admin');
        $pdf->MultiCell(0, 10, $text );
    }
    $pdf->Output();
    

}
function church_admin_backup_pdf()
{
    //setup pdf
    require_once(plugin_dir_path(dirname(__FILE__)).'includes/fpdf.php');
    $pdf=new FPDF();
    $pdf->AddPage('P','A4');
    $pdf->SetFont('Arial','B',24);
    $text=__('How to set up auto backup','church-admin');
    $pdf->Cell(0,10,$text,0,2,'L');
    if (TRUE)//PHP_OS=='Linux')
    {
    $phppath='/usr/local/bin/php -f ';
	
    $cronpath=plugin_dir_path(dirname(__FILE__)).'includes/cronbackup.php';
   
	update_option('church_admin_cron_path',$cronpath);
	$command=$phppath.$cronpath;
    
    
    $pdf->SetFont('Arial','',8);
    $text="Instructions for Linux servers and cpanel.\r\nLog into Cpanel which should be ".get_bloginfo('url')."/cpanel using your username and password. \r\nOne of the options will be Cron Jobs which is usually in 'Advanced Tools' at the bottom of the screen. Click on 'Standard' Experience level. that will bring up something like this... ";
    
    $pdf->MultiCell(0, 10, $text,0,'L' );
 
    $pdf->Image(plugin_dir_path( dirname(__FILE__) ).'images/cron-job1.jpg','10','65','','','jpg','');
    $pdf->SetXY(10,180);
    $text="In the common settings option - select 'Every Day' or every week. \r\nIn 'Command to run' put this:\r\n".$command."\r\n and then click Add Cron Job. Job Done.\r\n The backups are in the wp-content/uploads/church-admin-cache directory- with random filenames.sgl.gz \r\nPick the right date and restore through phpmyadmin import feature!";
    $pdf->MultiCell(0, 10, $text,0,'L' );
    }
    else
    {
         $pdf->SetFont('Arial','',10);
        $text=__("Unfortunately setting up queuing for backup using cron is not possible with non Linux servers. Please go back to Communication settings and enable the wp-cron option for scheduling sending of queued emails",'church-admin');
        $pdf->MultiCell(0, 10, $text );
    }
    $pdf->Output();
    

}
function church_admin_smallgroup_pdf($member_type_id,$people_type_id)
{
    global $wpdb,$people_type;
	$member_type=church_admin_member_type_array();
	require_once(plugin_dir_path(dirname(__FILE__)).'includes/fpdf.php');

	$smallgroups=$groupnames=array();
	$leader=array();
	//get groups
	$results=$wpdb->get_results('SELECT group_name,id FROM '.CA_SMG_TBL);
	if(!empty($results))
	{
		foreach($results AS $row){$smallgroups[$row->id]=array();$groupnames[$row->id]=iconv('UTF-8', 'ISO-8859-1',$row->group_name);}
	
		//grab people
		//handle people_type_id
		$ptype_sql='';
		if(!empty($people_type_id))
		{
			if(!is_array($people_type_id)){$ptype=explode(',',$people_type_id);}else{$ptype=$people_type_id;}
			
			foreach($ptype AS $key=>$value){if(ctype_digit($value))  $ptypesql[]='a.people_type_id='.$value;}
			if(!empty($ptypesql)) {$ptype_sql=' AND ('.implode(' OR ',$ptypesql).')';}else{$ptype_sql=' ';}
		}
		//handle member_type_id
		$memb_sql='';
		if($member_type_id!=0)
		{
			if(!is_array($member_type_id)){$memb=explode(',',$member_type_id);}else{$memb=$member_type_id;}
			foreach($memb AS $key=>$value){if(ctype_digit($value))  $membsql[]='a.member_type_id='.$value;}
			if(!empty($membsql)) {$memb_sql=' AND ('.implode(' OR ',$membsql).')';}
		}
		//build query of people
		$sql='SELECT DISTINCT CONCAT_WS(" ",a.first_name,a.prefix, a.last_name) AS name,b.ID,c.group_name FROM '.CA_PEO_TBL.' a, '.CA_MET_TBL.' b, '.CA_SMG_TBL.' c WHERE a.people_id=b.people_id AND b.ID=c.id AND b.meta_type="smallgroup" '.
		$memb_sql.$ptype_sql.'  ORDER BY a.last_name ';
		church_admin_debug("********************\r\npdf_creator.php#115\r\n$sql");
		$peopleresults = $wpdb->get_results($sql);
		$count=$wpdb->num_rows;
		
		$gp=0;
		
		foreach ($peopleresults as $people) 
		{
			$people->name=stripslashes($people->name);
			
			if(!empty($people->name))$smallgroups[$people->ID][]=iconv('UTF-8', 'ISO-8859-1',$people->name);
			
		}
		$noofgroups=$wpdb->get_row('SELECT COUNT(id) AS no FROM '.CA_SMG_TBL);
		$counter=$noofgroups->no;
		$pdf=new FPDF();
		$pageno=0;
		$x=10;
		$y=20;
		$w=1;
		$width=55;
		$pdf->AddPage('L',get_option('church_admin_pdf_size'));
		$pdf->SetFont('Arial','B',16);
	
		$whichtype=$whichptype=array();
		foreach($memb AS $key=>$value) $whichtype[]=$member_type[$value];//list of member types for title
		$people_type=get_option('church_admin_people_type');
		
		foreach($ptype AS $key=>$value) $whichptype[]=$people_type[$value];
		$text=implode(", ",$whichtype).' '.__('Small Group List','church-admin').' '.date(get_option('date_format')).'  '.$count.' '.implode(", ",$whichptype);
		$pdf->Cell(0,10,$text,0,2,'C');
		$pageno+=1;



	foreach($groupnames AS $z=>$groupname)
	{
		$text='';
		if($w==6)
		{
			$pdf->SetFont('Arial','B',16);
			$pdf->AddPage('L',get_option('church_admin_pdf_size'));
			
			$whichtype=array();
			foreach($memb AS $key=>$value)$whichtype[]=$member_type[$value];
			$text=implode(", ",$whichtype).' '.__('Small Group List','church-admin').' '.date(get_option('date_format')).'  '.$count.' '.implode(", ",$whichptype);
			$pdf->Cell(0,10,$text,0,2,'C');
			$x=10;
			$y=20;
			$w=1;
		}
		$newx=$x+(($w-1)*$width);
		if($pageno>1) {$newx=$x+(($z-($pageno*5))*$width);}
		$pdf->SetXY($newx,$y);
		$pdf->SetFont('Arial','B',10);
		$pdf->Cell($width,8,iconv('UTF-8', 'ISO-8859-1',$groupname),1,1,'C');
		$pdf->SetFont('Arial','',10);
		$pdf->SetXY($newx,$y+8);
		if(!empty($smallgroups[$z]))
		{
			$pdf->SetFont('Arial','B',10);
			$pdf->SetFont('Arial','',10);
			$text='';
			for($a=0;$a<count($smallgroups[$z]);$a++)
			{
				$b=$a+1;
				if(!empty($smallgroups[$z][$a]))$text.=$b.') '.$smallgroups[$z][$a]."\n";
			}
			$pdf->MultiCell($width,5,$text."\n",1);
			
			$pdf->SetX($newx);
		}
		
		$pdf->Cell($width,0,"",'LB',2,'L');
		$w++;
	}
	$pdf->Output();
}
}


function church_admin_address_pdf($member_type_id=1)
{

//update 2014-03-19 to allow for multiple surnames
//;update 2016-12-13 Left join grabbing household ids
	//initilaise pdf
	require_once(plugin_dir_path(dirname(__FILE__)).'includes/fpdf.php');
	class PDF extends FPDF
	{
		function Header()
		{
			$this->SetXY(10,10);
			$this->SetFont('Arial','B',18);
			$title=get_option('blogname').' '.__('Address List','church-admin').' '.date(get_option('date_format'));
			$this->Cell(0,8,$title,0,1,'C');
			$this->Ln(5);
		}
	}
	$pdf = new PDF();
	$pdf->SetAutoPageBreak(1,10);
	$pdf->AddPage('P',get_option('church_admin_pdf_size'));
	

  global $wpdb;
//address book cache
$memb_sql='';
  	if($member_type_id!=0)
  	{
  		$memb=explode(',',$member_type_id);
      	foreach($memb AS $key=>$value){if(ctype_digit($value))  $membsql[]='a.member_type_id='.$value;}
      	if(!empty($membsql)) {$memb_sql=' WHERE ('.implode(' || ',$membsql).')';}
	}
$sql='SELECT a.household_id,b.private FROM '.CA_PEO_TBL.' a LEFT JOIN '.CA_HOU_TBL.' b on a.household_id=b.household_id AND b.private=0 '.$memb_sql.'  GROUP BY a.household_id ORDER BY a.last_name ASC ';
  $results=$wpdb->get_results($sql);
church_admin_debug($wpdb->num_rows);
  $counter=1;
    $addresses=array();
	foreach($results AS $ordered_row)
	{
		$y=$pdf->GetY();
		
		$address=$wpdb->get_row('SELECT * FROM '.CA_HOU_TBL.' WHERE household_id="'.esc_sql($ordered_row->household_id).'"');
		$people_results=$wpdb->get_results('SELECT * FROM '.CA_PEO_TBL.' WHERE household_id="'.esc_sql($ordered_row->household_id).'" ORDER BY people_type_id ASC,sex DESC');
		$adults=$children=$emails=$mobiles=$photos=array();
		$last_name='';
		$x=0;
		foreach($people_results AS $people)
		{
			if($people->people_type_id=='1')
			{	
				if(!empty($people->prefix)){$prefix=$people->prefix.' ';}else{$prefix='';}
				$last_name=$prefix.$people->last_name;
				$adults[$last_name][]=$people->first_name;
				if(!empty($people->email)&&$people->email!=end($emails)) $emails[$people->first_name]=$people->email;
				if(!empty($people->mobile)&&$people->mobile!=end($mobiles))$mobiles[$people->first_name]=$people->mobile;
				if(!empty($people->attachment_id))$photos[$people->first_name]=$people->attachment_id;
				$x++;
			}
			else
			{
				$children[]=$people->first_name;
				if(!empty($people->attachment_id))$photos[$people->first_name]=$people->attachment_id;
			}
	  
		}
		//create output
		array_filter($adults);$adultline=array();
		foreach($adults as $lastname=>$firstnames){$adultline[]=implode(" & ",$firstnames).' '.$lastname;}
		//address name of adults in household
		$pdf->SetFont('Arial','B',10);
		$pdf->Cell(0,5,iconv('UTF-8', 'ISO-8859-1',implode(" & ",$adultline)),0,1,'L');
		$pdf->SetFont('Arial','',10);
		//children
		if(!empty($children))$pdf->Cell(0,5,iconv('UTF-8', 'ISO-8859-1',implode(", ",$children)),0,1,'L');
		//address if stored
		if(!empty($address->address)){$pdf->Cell(0,5,iconv('UTF-8', 'ISO-8859-1',$address->address),0,1,'L');}
		//emails
		if (!empty($emails))
		{	
			array_unique($emails);
			if(count($emails)<2 && $x<=1)
			{
				$pdf->Cell(0,5,iconv('UTF-8', 'ISO-8859-1',esc_html(end($emails))),0,1,'L',FALSE,'mailto:'.end($emails));
			}
			else
			{//more than one email in household
				$text=array();
				foreach($emails AS $name=>$email)
				{
					$content=$name.': '.$email;
					if($email!=end($emails))$content.=', ';
					$width=$pdf->GetStringWidth($content);
					$pdf->Cell($width,5,iconv('UTF-8', 'ISO-8859-1',$content),0,0,'L',FALSE,'mailto:'.$email);
					
				}
				$pdf->Ln();
				//$pdf->Cell(0,5,iconv('UTF-8', 'ISO-8859-1',implode(', ',$text)),0,1,'L',FALSE,'mailto:'.end($emails));
			}
		}
		if ($address->phone)$pdf->Cell(0,5,iconv('UTF-8', 'ISO-8859-1',$address->phone),0,1,'L',FALSE,'tel:'.$address->phone);
		if (!empty($mobiles))
		{	
			array_unique($mobiles);
			if(count($mobiles)<2 && $x<=1)
			{
				$pdf->Cell(0,5,iconv('UTF-8', 'ISO-8859-1',esc_html(end($mobiles))),0,0,'L',FALSE,'tel:'.end($mobiles));
			}
			else
			{//more than one mobile in household
				$text=array();
				foreach($mobiles AS $name=>$mobile)
				{
					$content=$name.': '.$mobile;
					if($mobile!=end($mobiles))$content.=', ';
					$width=$pdf->GetStringWidth($content);
					$pdf->Cell($width,5,iconv('UTF-8', 'ISO-8859-1',$content),0,0,'L',FALSE,'tel:'.$mobile);
				}
				
			}
			$pdf->Ln(5);
		}
	$pdf->Ln(5);
    }
   
 
$pdf->Output();


}

function church_admin_label_pdf($member_type_id=0)
{
global $wpdb;

//grab addresses
//get alphabetic order
$memb_sql='';
  	if($member_type_id!=0)
  	{
  		$memb=$member_type_id;
      	foreach($memb AS $key=>$value){if(ctype_digit($value))  $membsql[]='member_type_id='.$value;}
      	if(!empty($membsql)) {$memb_sql=' WHERE ('.implode(' || ',$membsql).')';}
	}
$sql='SELECT household_id FROM '.CA_PEO_TBL.$memb_sql.' GROUP BY last_name ORDER BY last_name';
$results = $wpdb->get_results($sql);
if($results)
{
    require_once('PDF_Label.php');
    $pdflabel = new PDFLabel(get_option('church_admin_label'), 'mm', 1, 2);
    //$pdflabel->Open();
    $pdflabel->SetFont('Arial','B',10);
    $pdflabel->AddPage();
    $counter=1;
    $addresses=array();
    foreach ($results as $row) 
    {
	
	$add='';
	$address_row=$wpdb->get_row('SELECT * FROM '.CA_HOU_TBL.' WHERE household_id="'.esc_sql($row->household_id).'"');
	$address=iconv('UTF-8', 'ISO-8859-1',$address_row->address);
	if(!empty($address))
	{
	    $people_results=$wpdb->get_results('SELECT * FROM '.CA_PEO_TBL.' WHERE household_id="'.esc_sql($row->household_id).'" ORDER BY people_type_id ASC,sex DESC');
	    $adults=array();
	    foreach($people_results AS $people)
	    {
	      if($people->people_type_id=='1')
	      {
	        $last_name=iconv('UTF-8', 'ISO-8859-1',$people->last_name);
	        $adults[]=iconv('UTF-8', 'ISO-8859-1',$people->first_name);
	    }
	    }	
	    
	    $add=html_entity_decode(implode(" & ",$adults))." ".$last_name."\n".str_replace(",",",\n",$address);
	    
	    $pdflabel->Add_Label($add);
	}
    }
    //start of cache mailing labels!
   
   
$pdflabel->Output();

//end of mailing labels
}
}


function ca_vcard($id)
{
  global $wpdb;

    $query='SELECT * FROM '.CA_HOU_TBL.' WHERE household_id="'.esc_sql($id).'"';
	
	$add_row = $wpdb->get_row($query);
    $address=$add_row->address;
    $phone=$add_row->phone;
    $people_results=$wpdb->get_results('SELECT * FROM '.CA_PEO_TBL.' WHERE household_id="'.esc_sql($id).'" ORDER BY people_type_id ASC,sex DESC');
    $adults=$children=$emails=$mobiles=array();
      foreach($people_results AS $people)
	{
	  if($people->people_type_id=='1')
	  {
	    $last_name=$people->last_name;
	    $adults[]=$people->first_name;
	    if($people->email!=end($emails)) $emails[]=$people->email;
	    if($people->mobile!=end($mobiles))$mobiles[]=$people->mobile;
		
	  }
	  else
	  {
	    $children[]=$people->first_name;
	  }
	  if(!empty($people->attachment_id))
		{
			$photo=wp_get_attachment_image_src( $people->attachment_id, 'ca-people-thumb',0,$attr );
			
		}
	}
  //prepare vcard
require_once(plugin_dir_path(dirname(__FILE__)).'includes/vcf.php');
$v = new vCard();
if(!empty($add_row->phone))$v->setPhoneNumber($add_row->phone, "PREF;HOME;VOICE");
if(!empty($mobiles))$v->setPhoneNumber("{$mobiles['0']}", "CELL;VOICE");
$v->setName("{$last_name}", implode(" & ",$adults), "", "");

$v->setAddress('',$add_row->address,'','','','','','HOME;POSTAL' );
$v->setEmail("{$emails['0']}");

if(!empty($children)){$v->setNote("Children: ".implode(", ",$children));}
if(!empty($photo))
{
	
	$t=exif_imagetype($photo['0']); 		
	switch($t)
		{
			case 1:$type='GIF';break;
			case 2:$type='JPG';break;
			
		}
	if(!empty($type))$v->setPhoto($type,$photo[0]);
}

$output = $v->getVCard();
$filename=$last_name.'.vcf';


    header("Cache-Control: public");
    header("Content-Description: File Transfer");
    header("Content-Disposition: attachment; filename=$filename");
    header("Content-Type: text/x-vcard");
    header("Content-Transfer-Encoding: binary");

   echo $output;

}
function church_admin_year_planner_pdf($initial_year)
{
    if(empty($initial_year))$initial_year==date('Y');
    global $wpdb;

//check cache admin exists
$upload_dir = wp_upload_dir();
$dir=$upload_dir['basedir'].'/church-admin-cache/';


//initialise pdf
require_once(plugin_dir_path(dirname(__FILE__)).'includes/fpdf.php');
$pdf=new FPDF();
$pdf->AddPage('L','A4');

$pageno=0;
$x=10;
$y=5;
//Title
$pdf->SetXY($x,$y);
$pdf->SetFont('Arial','B',18);
$title=get_option('blogname');
$pdf->Cell(0,8,$title,0,0,'C');
$pdf->SetFont('Arial','B',10);

//Get initial Values
$initial_month='01';
if(empty($initial_year))$initial_year=date('Y');
$month=0;
$days=array('Sun','Mon','Tues','Weds','Thurs','Fri','Sat');
$row=0;
$current=time();
$this_month = (int)date("m",$current);
$this_year = date( "Y",$current );

for($quarter=0;$quarter<=3;$quarter++)
{
for($column=0;$column<=2;$column++)
{//print one of the three columns of months
    $x=10+($column*80);//position column
    $y=15+(44*$quarter);
    $pdf->SetXY($x,$y);
    $this_month=date('m',strtotime($initial_year.'-'.$initial_month.'-01 + '.$month.' month'));
    $this_year=date('Y',strtotime($initial_year.'-'.$initial_month.'-01 + '.$month.' month'));
    // find out the number of days in the month
    $numdaysinmonth = cal_days_in_month( CAL_GREGORIAN, $this_month, $this_year );
    // create a calendar object
    $jd = cal_to_jd( CAL_GREGORIAN, $this_month,date( 1 ), $this_year );
    // get the start day as an int (0 = Sunday, 1 = Monday, etc)
    $startday = jddayofweek( $jd , 0 );
    // get the month as a name
    $monthname = jdmonthname( $jd, 1 );
    $month++;//increment month for next iteration
    $pdf->SetFont('Arial','B',10);
    $pdf->Cell(70,7,$monthname.' '.$this_year,0,0,'C');
    //position to top left corner of calendar month 
    $y+=7;
    $pdf->SetXY($x,$y);
    $pdf->SetFont('Arial','',8);
    //print daylegend
    for($legend=0;$legend<=6;$legend++)
    {
        $pdf->Cell(10,5,$days[$legend],1,0,'C');
    }
    $y+=5;
    $pdf->SetXY($x,$y);
    for($monthrow=0;$monthrow<=5;$monthrow++)
    {//print 6 weeks
        
        for($day=0;$day<=6;$day++)
        {
            if($monthrow==0 && $day==$startday)$counter=1;//month has started
            if($monthrow==0 && $day<$startday)
            {
                //empty cells before start of month, so fill with grey colour
                $pdf->SetFillColor('192','192','192');
                $pdf->Cell(10,5,'',1,0,'L',TRUE);
            }
            else
            {
                //during month so category background
                $sql='SELECT a.bgcolor FROM '.CA_CAT_TBL.' a, '.CA_DATE_TBL.' b WHERE b.year_planner="1" AND a.cat_id=b.cat_id AND b.start_date="'.$this_year.'-'.$this_month.'-'.sprintf('%02d',$counter).'" LIMIT 1';
				
				$bgcolor=$wpdb->get_var($sql);
                if(!empty($bgcolor))
                {
                    $colour=html2rgb($bgcolor);
                    $pdf->SetFillColor($colour[0],$colour[1],$colour[2]);
                }
                else
                {
                    $pdf->SetFillColor(255,255,255);
                }
                
                 if($counter <= $numdaysinmonth)
                {
                    //duringmonth so print a date
                    $pdf->Cell(10,5,$counter,1,0,'L',TRUE);
                    $counter++;
                }
                else
                {
                //end of month, so back to grey background
                $pdf->SetFillColor('192','192','192');
                $pdf->Cell(10,5,'',1,0,'C',TRUE);
                }
            }
            
           
            
        }
        $y+=5;
        
        $pdf->SetXY($x,$y);
    }
    
}//end of column
}//end row

//Build key
$x=250;
$y=23;
 $pdf->SetFont('Arial','B',10);
$result=$wpdb->get_results("SELECT * FROM ".$wpdb->prefix."church_admin_calendar_category");
foreach ($result AS $row)
{
    
    $pdf->SetXY($x,$y);
    $colour=html2rgb($row->bgcolor);
    $pdf->SetFillColor($colour[0],$colour[1],$colour[2]);
    $pdf->Cell(15,5,' ',0,0,'L',1);
    $pdf->SetFillColor(255,255,255);
    $pdf->Cell(15,5,$row->category,0,0,'L');
    $pdf->SetXY($x,$y);
    $pdf->Cell(45,5,'',1);
    $y+=6;
}
$pdf->Output();

}


function html2rgb($color)
{
    if ($color[0] == '#')
        $color = substr($color, 1);

    if (strlen($color) == 6)
        list($r, $g, $b) = array($color[0].$color[1],
                                 $color[2].$color[3],
                                 $color[4].$color[5]);
    elseif (strlen($color) == 3)
        list($r, $g, $b) = array($color[0].$color[0], $color[1].$color[1], $color[2].$color[2]);
    else
        return false;

    $r = hexdec($r); $g = hexdec($g); $b = hexdec($b);

    return array($r, $g, $b);
}



function church_admin_horiz_pdf($service_id)
{
	global $wpdb;
	if(!empty($_GET['rota_id']))
	{
		$service=$wpdb->get_var('SELECT service_name FROM '.CA_SER_TBL.' WHERE service_id="'.intval($service_id).'"');
		
		require_once(plugin_dir_path(dirname(__FILE__)).'includes/fpdf.php');
		$pdf=new FPDF();
		$rota_jobs=$initials=array();
		foreach($_GET['rota_id'] AS $key=>$value){$rota_jobs[]=' rota_id="'.$value.'" ';}
		if(!empty($_GET['initials']))foreach($_GET['initials'] AS $key=>$value){$initials[$value]=TRUE;}
		$rota_tasks=$wpdb->get_results('SELECT * FROM '.CA_RST_TBL.' WHERE ('.implode(" OR ",$rota_jobs).') ORDER BY rota_order');
		$wanted_jobs=array();
		foreach($rota_tasks AS $rota_task){$wanted_jobs[$rota_task->rota_id]=$rota_task->rota_task;}
		$sql='SELECT * FROM '.CA_ROT_TBL.' WHERE rota_date>CURDATE() And service_id="'.intval($service_id).'" ORDER BY rota_date ASC';
		
			$rota_results=$wpdb->get_results($sql);
			
			if(!empty($rota_results))
			{
				$pdf->AddPage('L',get_option('church_admin_pdf_size'));
				
				$pdf->SetFont('Arial','',16);
				
				$text=__('Rota','church-admin').' '.$service;
				;
				$pdf->Cell(0,10,$text,0,2,'C');
				$pdf->SetFont('Arial','B',10);
				//job titles
				$pdf->Cell(30,7,__('Date','church-admin'),1,0,'C');
				//find longest text in each job
				
				foreach($wanted_jobs AS $key=>$value)
				{
					if($value=='Sermon Title'){$w=90;}else{$w=45;}
					
					$pdf->Cell($w,7,$value,1,0,'C');
				}	
				$pdf->Ln();
				$pdf->SetFont('Arial','',10);
				foreach($rota_results AS $rota_row)
				{
					//date
					$pdf->Cell(30,7,mysql2date(get_option('date_format'),$rota_row->rota_date),1,0,'C');
					$jobs_for_day=maybe_unserialize($rota_row->rota_jobs);
					foreach($wanted_jobs AS $key=>$value)
					{
						$people=church_admin_get_people($jobs_for_day[$key]);
						if(!empty($initials[$key]))
						{
							$ppl=iconv('UTF-8', 'ISO-8859-1',church_admin_initials($jobs_for_day[$key]));
						}
						else
						{
							$ppl=iconv('UTF-8', 'ISO-8859-1',$people);
						}
						if($value=='Sermon Title'){$w=90;}else{$w=45;}
						$pdf->Cell($w,7,$ppl,1,0,'L',NULL,NULL);
					}
					$pdf->Ln();
				}
			
			}
		
		
	$pdf->Output();
	}
	

}
function church_admin_rota_pdf($service_id=1)
{
    
    global $wpdb;
	$days=array(1=>__('Sunday','church-admin'),2=>__('Monday','church-admin'),3=>__('Tuesday','church-admin'),4=>__('Wednesday','church-admin'),5=>__('Thursday','church-admin'),6=>__('Friday','church-admin'),7=>__('Saturday','church-admin'));

	$percent=array();
	$headers=array();
	require_once(plugin_dir_path(dirname(__FILE__)).'includes/fpdf.php');
	
	$pdf=new FPDF();
	
	
		//Grab Service details
	$service=$wpdb->get_row('SELECT * FROM '.CA_SER_TBL.' WHERE service_id="'.esc_sql($service_id).'"');
	//Main rota
	$jobs=array();
	$rota_tasks=$wpdb->get_results('SELECT * FROM '.CA_RST_TBL.' ORDER BY rota_order');
	for($months=0;$months<=2;$months++)
	{
		
		$sql='SELECT * FROM '.CA_ROT_TBL.' WHERE YEAR(rota_date) = YEAR(CURRENT_DATE + INTERVAL '.$months.' MONTH) AND MONTH(rota_date) = MONTH(CURRENT_DATE + INTERVAL '.$months.' MONTH) AND service_id="'.intval($service_id).'" ORDER BY rota_date ASC';
		
		$rota_results=$wpdb->get_results($sql);
		if(!empty($rota_results))
		{
			$pdf->AddPage('L',get_option('church_admin_pdf_size'));
			$pdf->AddFont('Verdana','','verdana.php');
			$pdf->SetFont('Verdana','',16);
			//php has a plus 1 month bug where current day is larger than next months last date eg 31st May will not get June for next month
			//use Mysql to get next month which doesn't have the bug.
			$month_shown=$wpdb->get_var('SELECT MONTHNAME(CURRENT_DATE + INTERVAL '.$months.' MONTH)');
			$text=sprintf( __('Who is doing what in %1$s at %2$s on %3$s at %4$s ', 'church-admin'), $month_shown,$service->service_name,$days[$service->service_day],$service->service_time);
			//$text=__('Sunday Rota produced','church-admin').date("d-m-Y");
			$pdf->Cell(0,10,$text,0,2,'C');
			$pdf->SetFont('Arial','B',12);
			//left hand column shows
			//grab this months services
			//Top left cell empty!
			$pdf->Cell(45,7,'',1,0,'C');
			$jobs=array();
			foreach($rota_results AS $rota_row)
			{
			
				//Output date
				$pdf->SetFont('Arial','B',8);
				$pdf->Cell(45,7,mysql2date(get_option('date_format'),$rota_row->rota_date),1,0,'C',0);
				//put that service's jobs in an array with date and job_id for key
				$jobs_for_day=maybe_unserialize($rota_row->rota_jobs);
				
				foreach($jobs_for_day AS $job_key=>$job_who) 
				{
					
					$jobs[$job_key][$rota_row->rota_date]=maybe_unserialize($job_who);
					
				}
					
			}
		
	
			//grab rota order
			$order=$wpdb->get_results('SELECT * FROM '.CA_RST_TBL.' ORDER BY rota_order');
			$x=0;
	
			foreach($order AS $rota_job)
			{
				//1st Column
				$pdf->Ln(7);//line break
				$pdf->SetFont('Arial','B',6);
				$pdf->Cell(45,7,$rota_job->rota_task,1,0,'C',0);
				//that job for each date
		
				$pdf->SetFont('Arial','',6);
				
				foreach((array)$jobs[$rota_job->rota_id] AS $date=>$people)
				{
			
					if($x %2 == 0){$pdf->SetFillColor(200,200,200);$fill=1;}else{$fill=0;}
			
					if(!empty($rota_job->initials)){$ppl=iconv('UTF-8', 'ISO-8859-1',church_admin_initials($people));}else{$ppl=iconv('UTF-8', 'ISO-8859-1',church_admin_get_people($people));}
					$pdf->Cell(45,7,$ppl,1,0,'C',$fill);
					$x++;
				}
				$x=0;
			}
		}
	}	
	$pdf->Output();
	
}


function church_admin_small_group_xml()
{
	global $wpdb;
	$results=$wpdb->get_results('SELECT * FROM '.CA_SMG_TBL.' WHERE lat!="" AND lng!=""');
	if(!empty($results))
	{
		$color_def = array
	('1'=>"FF0000",'2'=>"00FF00",'3'=>"0000FF",'4'=>"FFF000",'5'=>"00FFFF",'6'=>"FF00FF",'7'=>"CCCCCC",	8  => "FF7F00",	9  => "7F7F7F",	10 => "BFBFBF",	11 => "007F00",
		12 => "7FFF00",	13 => "00007F",	14 => "7F0000",	15 => "7F4000",
		16 => "FF9933",	17 => "007F7F",	18 => "7F007F",	19 => "007F7F",
		20 => "7F00FF",	21 => "3399CC",	22 => "CCFFCC",	23 => "006633",
		24 => "FF0033",	25 => "B21919",	26 => "993300",	27 => "CC9933",
		28 => "999933",	29 => "FFFFBF",	30 => "FFFF7F",31  => "000000"
	);
		
		header("Content-type: text/xml;charset=utf-8");
		echo '<markers>';
		foreach($results AS $row)
		{

			// Iterate through the rows, printing XML nodes for each

			// ADD TO XML DOCUMENT NODE
				echo '<marker ';
				echo 'pinColor="'.$color_def[$row->id].'" ';
				echo 'lat="' . $row->lat . '" ';
				echo 'lng="' . $row->lng . '" ';
				echo 'smallgroup_name="'.htmlentities($row->group_name).'" ';
				echo 'address="'.htmlentities($row->address).'" ';
				echo 'when="'.htmlentities($row->whenwhere).'" ';
				echo 'smallgroup_id="'.$row->id.'" ';
				echo '/>';
		}
		// End XML file
		echo '</markers>';
				
	}
}


/**
* This function produces a xml of people in various categories
*
* @author     	andymoyle
* @param		$member_type_id comma separated,$small_group BOOL
* @return		pdf
*
*/
function church_admin_address_xml($member_type_id=1,$small_group=1)
{
    global $wpdb;

	$markers='<markers>';
    $color_def = array(	'1'=>"FF0000",'2'=>"00FF00",'3'=>"0000FF",'4'=>"FFF000",'5'=>"00FFFF",'6'=>"FF00FF",'7'=>"CCCCCC",'8'  => "FF7F00",	
	9  => "7F7F7F",	10 => "BFBFBF",	11 => "007F00",
		12 => "7FFF00",	13 => "00007F",	14 => "7F0000",	15 => "7F4000",
		16 => "999933",	17 => "007F7F",	18 => "7F007F",	19 => "007F7F",
		20 => "7F00FF",	21 => "3399CC",	22 => "CCFFCC",	23 => "006633",
		24 => "000033",	25 => "B21919",	26 => "993300",	27 => "CC9933",
		28 => "FF9933",	29 => "FFFFBF",	30 => "FFFF7F",31  => "000000"
	);
	//grab relevant households
	$memb_sql='';
  	if($member_type_id!=0)
  	{
  		$memb=explode(',',$member_type_id);
      	foreach($memb AS $key=>$value){if(ctype_digit($value))  $membsql[]='member_type_id='.$value;}
      	if(!empty($membsql)) {$memb_sql=' WHERE ('.implode(' || ',$membsql).')';}
	}
	$sql='SELECT household_id FROM '.CA_PEO_TBL.$memb_sql.'  GROUP BY household_id ORDER BY last_name ASC ';
	$results=$wpdb->get_results($sql);
    if(!empty($results))
	{
		foreach($results AS $row)
		{
			$address=$wpdb->get_row('SELECT * FROM '.CA_HOU_TBL.' WHERE household_id="'.esc_sql($row->household_id).'"');
			$sql='SELECT a.* FROM '.CA_PEO_TBL.' a  WHERE a.household_id="'.esc_sql($row->household_id).'" ORDER BY a.people_order, a.people_type_id ASC,sex DESC';
			$people_results=$wpdb->get_results($sql);
			
			$adults=$children=$emails=$mobiles=$photos=array();
			$last_name='';
			$x=0;
			$markers.= '<marker ';
			foreach($people_results AS $people)
			{
				
				if($people->people_type_id=='1')
				{	
					if(!empty($people->prefix)){$prefix=$people->prefix.' ';}else{$prefix='';}
					$last_name=$prefix.$people->last_name;
					$adults[$last_name][]=$people->first_name;
					$smallgroup_id=church_admin_get_people_meta($people->people_id,'smallgroup');
					if(!empty($smallgroup_id[0]))$smallgroup=$wpdb->get_row('SELECT * FROM '.CA_SMG_TBL.' WHERE id="'.$smallgroup_id[0].'"');
							//small group data for marker
							
							if(!empty($smallgroup))
							{
								if(empty($smallgroup->group_name))$smallgroup->group_name=' ';
								if(empty($smallgroup->address))$smallgroup->address=' ';
								if(empty($smallgroup->whenwhere))$smallgroup->whenwhere=' ';
								$sg=array();
								$sg[]=  'pinColor="'.$color_def[$smallgroup->id].'" ';
								$sg[]= 'smallgroup_id="'.$smallgroup->id.'" ';
								$sg[]= 'smallgroup_name="'.htmlentities($smallgroup->group_name).'" ';
								$sg[]=  'small_group_address="'.htmlentities($smallgroup->address).'" ';
								$sg[]=  'when="'.htmlentities($smallgroup->whenwhere).'" ';
							}
							else
							{$sg=array();
								$sg[]= 'pinColor="FF0000" ';
							}		
					$x++;
				}
				else
				{
					if(!empty($people->prefix)){$prefix=$people->prefix.' ';}else{$prefix='';}
					$last_name=$prefix.$people->last_name;
					$children[$last_name][]=$people->first_name;
				
				}
				
			}
			$markers.=implode(" ",$sg);
			//address data for marker
			$markers.= 'lat="' . $address->lat . '" ';
			$markers.= 'lng="' . $address->lng . '" ';
			$markers.= 'address="'. $address->address.'" ';
			
			//people data
			array_filter($adults);
			$adultline=array();
			//the join statement makes sure the array is imploded like this ",,,&"  
			//http://stackoverflow.com/questions/8586141/implode-array-with-and-add-and-before-last-item 
			foreach($adults as $lastname=>$firstnames){$adultline[]=join(' &amp; ', array_filter(array_merge(array(join(', ', array_slice($firstnames, 0, -1))), array_slice($firstnames, -1)))).' '.$lastname;}
			$markers.='adults_names="'.implode(" &amp; ",$adultline). '" ';
			array_filter($children);
			$childrenline=array();
			foreach($children as $lastname=>$firstnames){$childrenline[]=join(' &amp; ', array_filter(array_merge(array(join(', ', array_slice($firstnames, 0, -1))), array_slice($firstnames, -1)))).' '.$lastname;}
			$markers.='childrens_names="'.implode(" &amp; ",$childrenline). '" ';
			$markers.= '/>';
		}
		$markers.='</markers>';
		header("Content-type: text/xml;charset=utf-8");
		echo $markers;
	}
   
    exit();    
}


/**
* This function produces a pdf of people in each ministry
*
* @author     	andymoyle
* @param		none
* @return		pdf
*
*/
function church_admin_ministry_pdf()
{
	global $wpdb;
	$ministries=$ministry_names=array();
	$results=$wpdb->get_results('SELECT ministry,ID FROM '.CA_MIN_TBL.' ORDER BY ministry ASC');
	foreach($results AS $row)$ministry_names[intval($row->ID)]=$row->ministry;
	
	foreach($ministry_names AS $key=>$ministry_name)
	{
			$sql='SELECT CONCAT_WS(" ",a.first_name,a.prefix,a.last_name) AS name FROM '.CA_PEO_TBL.' a, '.CA_MET_TBL.' b WHERE b.meta_type="ministry" AND a.people_id=b.people_id AND b.ID="'.esc_sql($key).'" ORDER BY a.last_name';
			$ministries[$ministry_name]=array();
			$people=$wpdb->get_results($sql);
			if(!empty($people))
			{
				foreach($people AS $person) {$ministries[$ministry_name][]=$person->name;}
			}
	
	}
	
	require_once(plugin_dir_path(dirname(__FILE__)).'includes/fpdf.php');
	$pdf=new FPDF();
	$pdf->AddPage('L',get_option('church_admin_pdf_size'));
	
	$pdf->SetFont('Arial','B',12);
	$pdf->Cell(0,10,__('Ministries','church-admin'),0,0,'C');
	$pdf->SetFont('Arial','',10);
	$i=1;
	$x=15;
	$y=25;
	ksort($ministries);
	foreach($ministries AS $min_name=>$people)
	{	
		if(empty($people))$people=array(0=>__('No-one yet','church-admin'));
		if($i>6)
		{
			$pdf->AddPage('L',get_option('church_admin_pdf_size'));$x=15;$x=25;$i=1;
			
			$pdf->SetFont('Arial','B',12);
			$pdf->Cell(0,6,__('Ministries','church-admin'),0,0,'C');
			
		}
		$pdf->SetXY($x,25);
		//ministry name
		$pdf->SetFont('Arial','B',10);
		$pdf->Cell(40,6,$min_name,1,0,'C');
		$pdf->SetXY($x,31);
		//ministry people
		$pdf->SetFont('Arial','',10);
		$pdf->MultiCell(40,6,iconv('UTF-8', 'ISO-8859-1',implode("\n",$people)),1,'L');
		
		$i++;
		$x+=40;
		$y=30;
		$pdf->SetXY($x,$y);
	}
	$pdf->Output();
}




/**
* This function produces a pdf of people in each hope team
*
* @author     	andymoyle
* @param		none
* @return		pdf
*
*/
function church_admin_hope_team_pdf()
{
	global $wpdb;
	$hope_teams=$wpdb->get_results('SELECT * FROM '.CA_HOP_TBL);
	$hope_team_jobs=array();
	
	foreach($hope_teams AS $hope_team)
	{
			$sql='SELECT CONCAT_WS(" ",a.first_name,a.prefix,a.last_name) AS name , mobile, email FROM '.CA_PEO_TBL.' a, '.CA_MET_TBL.' b WHERE b.meta_type="hope_team" AND a.people_id=b.people_id AND b.ID="'.esc_sql($hope_team->hope_team_id).'" ORDER BY a.last_name';
			
			$people=$wpdb->get_results($sql);
			if(!empty($people))
			{
				foreach($people AS $person) {$hope_team_jobs[$hope_team->job][]=$person->name.' '.$person->mobile.' '.$person->email;}
			}
	
	}
	
	require_once(plugin_dir_path(dirname(__FILE__)).'includes/fpdf.php');
	$pdf=new FPDF();
	$pdf->AddPage('P',get_option('church_admin_pdf_size'));
	
	$pdf->SetFont('Arial','B',12);
	$pdf->Cell(0,10,__('Hope Team','church-admin'),0,1,'C');
	$pdf->SetFont('Arial','',10);
	$i=1;
	
	ksort($hope_team_jobs);
	foreach($hope_team_jobs AS $min_name=>$people)
	{	
		
		//ministry name
		$pdf->SetFont('Arial','B',8);
		$pdf->Cell(0,6,$min_name,1,1,'C');
		
		//ministry people
		$pdf->SetFont('Arial','',10);
		$pdf->MultiCell(0,6,iconv('UTF-8', 'ISO-8859-1',implode("\n",$people)),1,'L');
		$pdf->Ln(5);
		
		
		
	}
	$pdf->Output();
}

/**
 *
 * Kids work pdf
 * 
 * @author  Andy Moyle
 * @param   Array $member_type_id 
 * @return  pdf
 * @version  0.2
 * 
 * 2017-01-10 - corrected sql to make override work properly
 */ 
function church_admin_kidswork_pdf($member_type_id)
{
	global $wpdb;

	$kidsworkGroups=$wpdb->get_results('SELECT * FROM '.CA_KID_TBL.' ORDER BY youngest DESC');
	$memb_sql='';
  	if($member_type_id!=0)
  	{
  		if(!is_array($member_type_id)){$memb=explode(',',$member_type_id);}else{$memb=$member_type_id;}
      	foreach($memb AS $key=>$value){if(ctype_digit($value))  $membsql[]='member_type_id='.$value;}
      	if(!empty($membsql)) {$memb_sql=' ('.implode(' OR ',$membsql).')';}
	}
    
	$member_type=church_admin_member_type_array();
	require_once(plugin_dir_path(dirname(__FILE__)).'includes/fpdf.php');
	//cache small group pdf

	$kidsworkgroups=$groupnames=array();
	$count=0;
	$leader=array();
	
	$count=$noofgroups=0;
	//get groups

	if(!empty($kidsworkGroups))
	{
		foreach($kidsworkGroups AS $row)
		{
			$noofgroups++;
			$groupname[$row->id]=iconv('UTF-8', 'ISO-8859-1',$row->group_name);//title first
			//corrected sql 2017-01-10 to make sure override works properly!
			$sql='SELECT CONCAT_WS(" ",first_name,last_name) AS name,kidswork_override FROM '.CA_PEO_TBL.' WHERE '.$memb_sql.' AND (kidswork_override="'.esc_sql($row->id).'" OR ((date_of_birth<"'.$row->youngest.'" AND date_of_birth>"'.$row->oldest.'") AND kidswork_override=0 )) ORDER BY last_name ';
			church_admin_debug("******************\r\n Kidwsork pdf\r\n $sql");
			$peopleresults = $wpdb->get_results($sql);
			if(!empty($peopleresults))
			{
				$colCount=1;
				foreach($peopleresults AS $people) 
				{
					$kidsworkgroups[$row->id][]=$colCount.') '.$people->name;
					$colCount++;//column count
					$count++;//total count for title area
				}
			}
		}
	}
	
	
	
	$counter=$noofgroups;

	$pdf=new FPDF();
	$pageno=0;
	$x=10;
	$y=20;
	$w=1;
	$width=55;
	$pdf->AddPage('L',get_option('church_admin_pdf_size'));
	$pdf->SetFont('Arial','B',16);
	
	$whichtype=array();
	
	$text=implode(", ",$whichtype).' '.__('Kidswork Group List','church-admin').' '.date(get_option('date_format')).'  '.$count.' '.__('people','church-admin');
	$pdf->Cell(0,10,$text,0,2,'C');
	$pageno+=1;



	foreach($groupname AS $id=>$groupname)
	{
		$text='';
		if($w==6)
		{
			$pdf->SetFont('Arial','B',16);
			$pdf->AddPage('L',get_option('church_admin_pdf_size'));
			
			$whichtype=array();
			foreach($memb AS $key=>$value)$whichtype[]=$member_type[$value];
			$text=implode(", ",$whichtype).' '.__('Kidswork Group List','church-admin').' '.date(get_option('date_format')).'  '.$count.' '.__('people','church-admin');
			$pdf->Cell(0,10,$text,0,2,'C');
			$x=10;
			$y=20;
			$w=1;
		}
		$newx=$x+(($w-1)*$width);
		if($pageno>1) {$newx=$x+(($z-($pageno*5))*$width);}
		$pdf->SetXY($newx,$y);
		$pdf->SetFont('Arial','B',10);
		$pdf->Cell($width,8,iconv('UTF-8', 'ISO-8859-1',$groupname),1,1,'C');
		$pdf->SetFont('Arial','',10);
		$pdf->SetXY($newx,$y+8);
		
			
			$pdf->SetFont('Arial','',10);
			$text='';
			if(!empty($kidsworkgroups[$id]))$text=implode("\n",$kidsworkgroups[$id]);
			$pdf->MultiCell($width,5,$text."\n",'LRB');
			
			$pdf->SetX($newx);
	
		
		$pdf->Cell($width,0,"",'LB',2,'L');
		$w++;
	}
	$pdf->Output();
}

/**
 *
 * Horizontal PDF using new rota table and sized to fit
 * 
 * @author  Andy Moyle
 * @param    $lengths, $fontSize
 * @return   array(orientation,font_size,widths)
 * @version  0.1
 * 
 */
function church_admin_new_horiz_pdf($service_id,$jobs)
{
	global $wpdb;
	
	//get required rota tasks
	$requiredRotaJobs=$rotaDates=array();
	$rota_tasks=$wpdb->get_results('SELECT * FROM '.CA_RST_TBL.' ORDER BY rota_order');
	if(empty($jobs))
	{
	
		
		foreach($rota_tasks AS $rota_task)
		{
			$allServiceID=maybe_unserialize($rota_task->service_id);
			if(is_array($allServiceID)&&in_array($service_id,$allServiceID))$requiredRotaJobs[$rota_task->rota_id]=$rota_task->rota_task;
		}
	}
	else
	{
		foreach($rota_tasks AS $rota_task)
		{
			if(is_array($jobs)&&in_array($rota_task->rota_id,$jobs))$requiredRotaJobs[$rota_task->rota_id]=$rota_task->rota_task;
		}
	}
	
	//get next twelve weeks of rota_jobs foreach rota task
	//first grab next twelve weeks of services
	$rotaDatesResults=$wpdb->get_results('SELECT * FROM '.CA_ROTA_TBL.' WHERE service_id="'.intval($service_id).'" AND mtg_type="service" AND rota_date>=CURDATE() GROUP BY rota_date LIMIT 24');
	//grab people for each job and each date and populate $rota array
	$rota=array();
	$lengths=array('date'=>0);//array to find max length rota column
	foreach($rotaDatesResults AS $rotaDateRow)
	{
		//longest date length
		$dateLength=strlen(mysql2date(get_option('date_format'),$rotaDateRow->rota_date));
		if($dateLength>$lengths['date'])$lengths['date']=$dateLength;
		//work through each row's column to find longest value
		foreach($requiredRotaJobs AS $rota_task_id=>$value)
		{
			$rota[$rotaDateRow->rota_date][$rota_task_id]=esc_html(church_admin_rota_people($rotaDateRow->rota_date,$rota_task_id,$service_id,'service'));
			$colWidth=strlen($rota[$rotaDateRow->rota_date][$rota_task_id]);
			if(empty($lengths[$rota_task_id])||$colWidth>$lengths[$rota_task_id]) $lengths[$rota_task_id]=$colWidth;
		}
	}
	
	
	//create pdf
	require_once(plugin_dir_path(dirname(__FILE__)).'includes/fpdf.php');
	
	$fontSize=12;
	$pdf_settings=church_admin_pdf_settings($lengths,$fontSize);
	//while(!church_admin_pdf_settings($lengths,$fontSize)&&$fontSize>=8){$fontSize--;$pdf_settings=church_admin_pdf_settings($lengths,$fontSize);}
	//print_r($pdf_settings);
	if(empty($pdf_settings))
	{
		
		$pdf=new FPDF('P','mm',get_option('church_admin_pdf_size'));
		$pdf->AddPage('P',get_option('church_admin_pdf_size'));
		$pdf->SetFont('Arial','',16);
		$service=$wpdb->get_var('SELECT service_name FROM '.CA_SER_TBL.' WHERE service_id="'.intval($service_id).'"');
		$pdf->SetFont('Arial','',16);
		$text=__('Rota','church-admin').' '.$service;
		$pdf->Cell(0,10,$text,0,2,'C');
		$pdf->Cell(200,7,__('You have more data than can fit on a page','church-admin'),0,0,'C');
		$pdf->Output();
		exit();
		
	}
		
		$pdf=new FPDF($pdf_settings['orientation'],'mm',get_option('church_admin_pdf_size'));
		//initialise pdf
		$pdf->AddPage($pdf_settings['orientation'],get_option('church_admin_pdf_size'));
	
	
	
		//Title
		$service=$wpdb->get_var('SELECT service_name FROM '.CA_SER_TBL.' WHERE service_id="'.intval($service_id).'"');
		$pdf->SetFont('Arial','',16);
		$text=__('Rota','church-admin').' '.$service;
		$pdf->Cell(0,10,$text,0,2,'C');
				
		//Begin table			
		$pdf->SetFont('Arial','B',$pdf_settings['font_size']);
		//table header
		$pdf->Cell($pdf_settings['widths']['date'],7,__('Date','church-admin'),1,0,'C');
		foreach($requiredRotaJobs AS $rota_task_id=>$rota_task)
		{
			$pdf->Cell($pdf_settings['widths'][$rota_task_id],7,esc_html($rota_task),1,0,'C');
		}
		$pdf->Ln();
		//table data
		foreach($rota AS $date=>$data)
		{
			//1st column is date
			$pdf->Cell($pdf_settings['widths']['date'],7,mysql2date(get_option('date_format'),$date),1,0,'C');
			//rest of columns for that row
			foreach($data AS $key=>$value)
			{
				$pdf->Cell($pdf_settings['widths'][$key],7,$value,1,0,'C');
			}
			$pdf->Ln();
		}
		$pdf->Output();
	
}

/**
 *
 *  PDF using new rota table and sized to fit
 * 
 * @author  Andy Moyle
 * @param    $lengths, $fontSize
 * @return   array(orientation,font_size,widths)
 * @version  0.1
 * 
 */
function church_admin_new_rota_pdf($service_id,$resize=FALSE,$date)
{
	global $wpdb;
	if(empty($resize))$resize=FALSE;
	if(!empty($date)&&church_admin_checkdate($date)){$date='"'.esc_sql($date).'"';}else{$date='CURDATE()';}
	$rota_tasks=$wpdb->get_results('SELECT * FROM '.CA_RST_TBL.' ORDER BY rota_order');
	$requiredRotaJobs=$rotaDates=array();
	foreach($rota_tasks AS $rota_task)
	{
		$allServiceID=maybe_unserialize($rota_task->service_id);
		if(is_array($allServiceID)&&in_array($service_id,$allServiceID))$requiredRotaJobs[$rota_task->rota_id]=$rota_task->rota_task;
	}
	
	//get next four weeks of rota_jobs for each rota task
	//first grab next four weeks of services
	$rotaDatesResults=$wpdb->get_results('SELECT * FROM '.CA_ROTA_TBL.' WHERE service_id="'.intval($service_id).'" AND mtg_type="service" AND rota_date>='.$date.' GROUP BY rota_date LIMIT 4');
	foreach($rotaDatesResults AS $rotaDatesRow)$rotaDates[]=$rotaDatesRow->rota_date;
	//grab people for each job and each date and populate $rota array
	$rota=array();
	$lengths=array();//array to find max length rota column
	
	//max length job titles
	$jobLength=0;
	foreach($requiredRotaJobs AS$id=>$job)
	{
		if($jobLength<strlen($job))$jobLength=strlen($job);
	}
	$lengths['jobs']=$jobLength;
	//longest data column
	foreach($rotaDatesResults AS $rotaDateRow)
	{
		//work through each row's column to find longest value
		foreach($requiredRotaJobs AS $rota_task_id=>$value)
		{
			$rota[$rota_task_id][$rotaDateRow->rota_date]=esc_html(church_admin_rota_people($rotaDateRow->rota_date,$rota_task_id,$service_id,'service'));
			$colWidth=strlen($rota[$rota_task_id][$rotaDateRow->rota_date]);
			if(empty($lengths[$rotaDateRow->rota_date])||$colWidth>$lengths[$rotaDateRow->rota_date]) $lengths[$rotaDateRow->rota_date]=$colWidth;
		}
	}

	
	//create pdf
	require_once(plugin_dir_path(dirname(__FILE__)).'includes/fpdf.php');
	if($resize)
	{
		$fontSize=10;
		$pdf_settings=church_admin_pdf_settings($lengths,$fontSize);
		while(empty($pdf_settings))
		{
			$fontSize--;
			$pdf_settings=church_admin_pdf_settings($lengths,$fontSize);
		}
	}
	else
	{//no rezize;
		$pdf_settings=array('font_size'=>10,'orientation'=>'L');
		//set equal col widths 
		$pdfSize=get_option('church_admin_pdf_size');
	
		switch($pdfSize)
		{
			case 'A4': $width=50;
					
			break;
			case 'Letter': $width=50;
			break;
			case 'Legal':
					$width=70;
			break;
		}
		$colWidth=array();
		foreach($lengths AS $key=>$length)$colWidth[$key]=$width;
		$pdf_settings['widths']=$colWidth;
	}//end no resize
	
	$pdf=new FPDF($pdf_settings['orientation'],'mm',get_option('church_admin_pdf_size'));
	//initialise pdf
	$pdf->AddPage($pdf_settings['orientation'],get_option('church_admin_pdf_size'));
	//Title
	$service=$wpdb->get_var('SELECT service_name FROM '.CA_SER_TBL.' WHERE service_id="'.intval($service_id).'"');
	$pdf->SetFont('Arial','',16);
	$text=__('Rota','church-admin').' '.$service;
	$pdf->Cell(0,10,$text,0,2,'C');
				
	//Begin table			
	$pdf->SetFont('Arial','B',$pdf_settings['font_size']);
	//table header
	$pdf->Cell($pdf_settings['widths']['jobs'],7,__('Jobs','church-admin'),1,0,'C');
	
	foreach($rotaDates AS $key=>$rota_date)
	{
		
		$pdf->Cell($pdf_settings['widths'][$rota_date],7,mysql2date(get_option('date_format'),$rota_date),1,0,'C');
	}
	$pdf->Ln();
	//table data
	$pdf->SetFont('Arial','',$pdf_settings['font_size']);
	foreach($rota AS $rota_task_id=>$data)
	{
		if($resize)
		{
			//1st column is job
			
			$pdf->Cell($pdf_settings['widths']['jobs'],7,$requiredRotaJobs[$rota_task_id],1,0,'C');
			//rest of columns for that row
			foreach($data AS$date=>$value)
			{
				$pdf->SetFont('Arial','',$pdf_settings['font_size']);
				$pdf->Cell($pdf_settings['widths'][$date],7,$value,1,0,'C');
			}
			$pdf->Ln();
		}
		else
		{
			//first work out maximum cell height and set that as $height;
			$height=7;
			foreach($data AS$date=>$value)
			{
				$new_height=$pdf->GetMultiCellHeight($pdf_settings['widths'][$date],7,$value,'LTR','C');
				if($new_height>$height)$height=$new_height;
			}
		
			//1st column is job
		
			$pdf->Cell($pdf_settings['widths']['jobs'],$height,$requiredRotaJobs[$rota_task_id],1,0,'C');
			//rest of columns for that row
			foreach($data AS$date=>$value)
			{
				$pdf->SetFont('Arial','',$pdf_settings['font_size']);
				//if more than one line set height to one line height
				if($pdf->GetMultiCellHeight($pdf_settings['widths'][$date],7,$value,'LTR','L')>7){$h=7;}else{$h=$height;}
				$pdf->MultiCell($pdf_settings['widths'][$date],$h,$value,'1','L');
			}
			
			$pdf->Ln($height);
		}
	}
	$pdf->Output();
}


?>