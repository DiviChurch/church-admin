<?php

function church_admin_graph($type='weekly',$meet='S/1',$start,$end,$width,$height)
{
	
	global $wpdb,$post;
	if(empty($start))$start=date('Y-m-d',strtotime('- 1 year'));
	if(empty($end))$end=date('Y-m-d');
	$out='';
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
	
		$meeting=explode('/',$meet);
		
  			if(!empty($meeting)&&is_array($meeting))
  			{//meeting populated
  				switch($meeting[0])
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
		  		$service_id=intval($meeting[1]);	
	 		}
	 		else{$mtg_type='service';$service_id=1;}
	 	$out.='<form action="" method="POST">';
	 	$out.='<p><label>'.__('Meeting','church-admin').'</label><select name="service_id">';
		$first='';
			//services first
			$services=$wpdb->get_results('SELECT * FROM '.CA_SER_TBL);
			if(!empty($services))
			{
				$option='';
				foreach($services AS $service)
				{
					$serviceDetail=__('Service','church-admin').' - '.esc_html($service->service_name).' '.esc_html($service->service_time);
     				if(!empty($mtg_type) && $mtg_type=='service'&&!empty($service_id)&& $service_id==$service->service_id)
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
			if(!empty($groups))
			{
				foreach($groups AS $group)		
				{
					if(!empty($mtg_type) && $mtg_type=='group'&&!empty($service_id)&& $service_id==$group->id)
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
			if(!empty($classes))
			{
				foreach($classes AS $class)		
				{
					if(!empty($mtg_type) && $mtg_type=='class'&&!empty($service_id)&& $service_id==$class->class_id)
					{
						$first='<option value="C/'.esc_html($service->service_id).'" selected="selected">'.__('Class','church-admin').' - '.$group->name.'</option>';
					}
					else
     				{
	  					$option.='<option value="C/'.esc_html($service->service_id).'" >'.__('Class','church-admin').' - '.esc_html($class->name).'</option>';
     				}
			
				}
			}	
						
    		$out.= $first.$option.'</select></p>';
	 $out.='<p><input type="radio" name="type" value="weekly" ';
	 if($type=='weekly') $out.=' checked="checked"';
	 $out.='/> '.__('Weekly Attendance Graph','church-admin').'</p>';
	  $out.='<p><input type="radio" name="type" value="rolling" ';
	 if($type=='rolling') $out.=' checked="checked"';
	 $out.='/> '.__('Rolling Average Attendance Graph','church-admin').'</p>';
	 $out.='<p>'.__('Start Date','church-admin').': '.church_admin_date_picker($start,'start',NULL,date('Y')-10,date('Y')).'</p>';
	 $out.='<p>'.__('End Date','church-admin').': '.church_admin_date_picker($end,'end',NULL,date('Y')-10,date('Y')).'</p>';
	 $out.='<p><input type="submit" value="'.__('Show','church-admin').'"/></p></form>';
	 
	 
	 //build graph
	 
	 //grab attendanc data
	$sql='SELECT * FROM '.CA_ATT_TBL.' WHERE service_id="'.(int)$service_id.'" AND mtg_type="'.esc_sql($mtg_type).'" AND `date` BETWEEN "'.esc_sql($start).'" AND "'.esc_sql($end).'" ORDER BY `date` ASC';
	
	 $results=$wpdb->get_results($sql);
	if(!empty($results))
	{
		$data=array();
	 	foreach($results AS $row)
	 	{
	 		$total=(int)$row->adults+$row->children;
	 		$rolling_total= $row->rolling_adults + $row->rolling_children;
	 		if($type=='weekly')$data[]='["'.mysql2date('d M Y',$row->date).'",'.$total.','.(int)$row->adults.','.(int)$row->children.']';
	 		if($type=='rolling')$data[]='["'.mysql2date('d M Y',$row->date).'",'.$rolling_total.','.(int)$row->rolling_adults.','.(int)$row->rolling_children.']';
	 	}
	 	$out.='<script>// Load the Visualization API.
    	google.load("visualization", "1", {"packages":["line"]});
     
    	// Set a callback to run when the Google Visualization API is loaded.
    	google.setOnLoadCallback(drawChart);
     	function drawChart() {

      		var data = new google.visualization.DataTable();
      		data.addColumn("string", "'.__('Date','church-admin').'");
      		data.addColumn("number", "'.__('Total','church-admin').'");
      		data.addColumn("number", "'.__('Adults','church-admin').'");
      		data.addColumn("number",  "'.__('Children','church-admin').'");
      
      		data.addRows(['.implode(',',$data).']);

      		var options = {
        	chart: {
          		title: "'.__('Attendance Graph','church-admin').'"
        	},
        	width: '.$width.',
        	height: '.$height.'
      	};

      	var chart = new google.charts.Line(document.getElementById("chart_div"));

      	chart.draw(data, options);
    
    	}</script>';
			$out.='<div id="chart_div"></div>';
		
		
	}else{$out.='<p>'.__('No attendance data','church-admin').'</p>';}
	}//safe to proceed
	
	return $out;
}

