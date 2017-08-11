<?php


$catsql=array();

  	if($category==""){$cat_sql="";}
  	elseif($category!="")
  	{
  		
  		$cats=explode(',',$category);
      	foreach($cats AS $key=>$value){if(ctype_digit($value))  $catsql[]='a.cat_id='.intval($value);}
      	if(!empty($catsql)) {$cat_sql=' AND ('.implode(' || ',$catsql).')';}
	}
if(isset($_POST['ca_month']) && isset($_POST['ca_year'])){ $current=mktime(12,0,0,$_POST['ca_month'],14,$_POST['ca_year']);}else{$current=time();}

$thismonth = (int)date("m",$current);
$thisyear = date( "Y",$current );
$sqlnow="$thisyear-$thismonth-01";
$actualyear=date("Y");
$next = strtotime("+1 month",$current);
$previous = strtotime("-1 month",$current);
$now=date("M Y",$current);
$sqlfirst=date('Y-m-01',$current);
$sqllast=date('Y-m-t',$current);

if(empty($weeks))$weeks=4;
$sqlnext=date("Y-m-d",strtotime($sqlnow." + ".$weeks." weeks"));
   // find out the number of days in the month
$numdaysinmonth = cal_days_in_month( CAL_GREGORIAN, $thismonth, $thisyear );
// create a calendar object
$jd = cal_to_jd( CAL_GREGORIAN, $thismonth,date( 1 ), $thisyear );

$sql='SELECT a.*, b.* FROM '.CA_DATE_TBL.' a,'.CA_CAT_TBL.' b WHERE a.cat_id=b.cat_id  AND a.start_date BETWEEN CAST("'.$sqlnow.'" AS DATE) AND CAST("'.$sqlnext.'" AS DATE) '.$cat_sql.' ORDER BY a.start_date,a.start_time';
	

    
$result=$wpdb->get_results($sql);

$out.='<table><tr><td>';
if($now==date('M Y')){$out.='&nbsp;';}else{$out.='<form action="'.get_permalink().'" name="previous" method="post"><input type="hidden" name="ca_month" value="'.date('m',strtotime("$now -1 month")).'"/><input type="hidden" name="ca_year" value="'.date('Y',strtotime("$now -1 month")).'"/><input class="calendar-date-switcher" type="submit" value="'.__('Previous','church-admin').'" /></form>';}
$out.='</td>
                    <td ><h2>'.esc_html($now).'</h2></td>
                    <td ><form action="'.get_permalink().'" method="post"><input type="hidden" name="ca_month" value="'.date('m',strtotime($now.' +1 month')).'"/><input type="hidden" name="ca_year" value="'.date('Y',strtotime($now.' +1 month')).'"/><input type="submit" class="calendar-date-switcher" value="'.__('Next','church-admin').'"/></form></td>
                
                
</tr></table><table>';
$out.='<tr><td width="150">'.__('Date','church-admin').'</td><td width="150">'.__('Time','church-admin').'</td><td width="400" >'.__('Event','church-admin').'</td></tr>';
foreach($result AS $row)
{
    if($row->start_time=='00:00:00' && $row->end_time=='23:59:00')
    {//all day
    	 $out.="<tr><td>".mysql2date(get_option('date_format'),$row->start_date)."</td><td>".__('All Day','church-admin')."</td><td><strong>".esc_html(stripslashes($row->title))."</strong><br> ".esc_html(stripslashes($row->description))."</td></tr>";

    }else 
    {//not an all day
        $out.="<tr><td>".mysql2date(get_option('date_format'),$row->start_date)."</td><td>".mysql2date(get_option('time_format'),$row->start_time)." - ".mysql2date(get_option('time_format'),$row->end_time)."</td><td><strong>".esc_html(stripslashes($row->title))."</strong><br> ".esc_html(stripslashes($row->description))."</td></tr>";
	}
}
$out.="</table>";
	
?>