<?php
function ca_podcast_display($series_id=NULL,$file_id=NULL,$speaker_name=NULL)
{
/**
 *  Podcast Display
 * 
 * @author  Andy Moyle
 * @param    $event_id,$speaker_id,$file_id
 * @return   
 * @version  0.1
 * 
 */
    global $wpdb,$ca_podcast_settings;
	$upload_dir = wp_upload_dir();
	$path=$upload_dir['basedir'].'/sermons/';
	$url=content_url().'/uploads/sermons/';
	$header='<table>';
	if(!empty($series_id)||!empty($speaker_name))$header.='<tr style="vertical-align:middle">';
	//Add filter by preacher name
		$speakers=$wpdb->get_results('SELECT DISTINCT speaker AS speakers FROM '.CA_FIL_TBL);
		$preachers=array();
		foreach($speakers AS $speaker)
		{
			$pr=explode(",",church_admin_get_people(trim($speaker->speakers)));//gets list of names from each value
			
			foreach($pr AS $key=>$p)
			{
				$person=str_replace('  ',' ',trim($p));//get rid of trailing, leading and double spaces!
				if(!empty($person)&&!in_array($person,$preachers))$preachers[]=$person;
			}
		}
		
		
		asort($preachers);
		
		if(!empty($preachers))
		{
			$header.='<td><strong>'.__(' Filter by preacher','church-admin').':</strong></td><td><form action="../"><select onchange="window.open(this.options[this.selectedIndex].value,\'_top\')">';
			$header.='<option value="">'.__('Show all','church-admin').'</option>';
			foreach($preachers AS $key=>$value)
			{
				if(!empty(trim($value)))$header.='<option value="'.get_permalink().'?speaker_name='.urlencode($value).'"';
				if(!empty($_GET['speaker_name'])&& $_GET['speaker_name']==$value)$header.=' selected="selected" ';
				$header.='>'.esc_html($value).'</option>';
			}
			$header.='</select></form></td>';
		}
		//End Add filter by preacher name
		
	//Add filter by series
		
	 $series=$wpdb->get_results('SELECT * FROM '.CA_SERM_TBL);
	 if(!empty($series))
	 {
		$header.='<td><strong>'.__('Or filter by series','church-admin').':</strong></td><td><form action="../"><select name="series_id" onchange="window.open(this.options[this.selectedIndex].value,\'_top\')">';
		$header.='<option value="">'.__('Show all','church-admin').'</option>';
			foreach($series AS $serie)
			{
				$header.='<option value="'.get_permalink().'?series_id='.urlencode($serie->series_id).'" ';
				if(!empty($_GET['series_id'])&& $_GET['series_id']==$serie->series_id){$header.=' selected="selected" ';$series_name=esc_html($serie->series_name);}
				$header.='>'.esc_html($serie->series_name).'</option>';
			}
			$header.='</select></form></td>';
	 }
	$header.='</tr></table>';
	if(!empty($_GET['speaker_name']))$header.='<h2>'.esc_html(sprintf(__('Sermons by %1$s', 'church-admin' ), stripslashes($_GET['speaker_name']))).'</h2>';
	if(!empty($_GET['series_id']))$header.='<h2>'.esc_html(sprintf(__('%1$s Series', 'church-admin' ), $series_name)).'</h2>';
	
	require_once(plugin_dir_path(dirname(__FILE__)).'includes/pagination.class.php');
	$out='';
    if($file_id){return ca_display_file($file_id);}
	elseif($speaker_name)
    {//speaker_name
	$sql='SELECT file_id FROM '.CA_FIL_TBL.' WHERE speaker LIKE "%'.esc_sql($speaker_name).'%" ORDER BY pub_date DESC ';
	
	} //end speaker_id specified
    elseif($series_id)
    {//series_id specified
        $series=$wpdb->get_row('SELECT * FROM '.CA_SERM_TBL.' WHERE series_id="'.esc_sql($series_id).'"');
        $ser_header=get_option('ca_podcast_event_template');
        $ser_header.=str_replace('[SERIES_NAME]',esc_html($series->series_name),$ser_header);
        $ser_header.=str_replace('[SERIES_DESCRIPTION]',esc_html($series->series_description),$ser_header);
		$header.=$ser_header;
        $sql='SELECT file_id FROM '.CA_FIL_TBL.' WHERE series_id="'.esc_sql($series_id).'"';
    }//end series_id specified
    elseif(empty($speaker_name)&&empty($series_id))
    {//not specified
        
        $sql='SELECT file_id FROM '.CA_FIL_TBL.' ORDER BY pub_date DESC';
    }//not specified
	
		
		
	$results=$wpdb->get_results($sql);
	$items=$wpdb->num_rows;
    if($results)
    {
	
		//pagination
		$p = new pagination;
		$p->items($items);
		$p->limit(get_option('church_admin_page_limit')); // Limit entries per page
		$p->target(get_permalink());
		if(!isset($p->paging))$p->paging=1; 
		if(!isset($_GET[$p->paging]))$_GET[$p->paging]=1;
		$p->currentPage(intval($_GET[$p->paging])); // Gets and validates the current page
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
		$limit = " LIMIT " . ($p->page - 1) * $p->limit  . ", " . $p->limit;
		$pageresults=$wpdb->get_results($sql.$limit);
		 
        $out.=$header;
	
        // Pagination
    		$out.= '<div class="tablenav"><div class="tablenav-pages">';
    		$out.=  $p->getOutput();  
    		$out.= '</div></div>';
    	//Pagination 
        foreach($pageresults AS $row){$out.=ca_display_file($row->file_id);}
       // Pagination
    		$out.= '<div class="tablenav"><div class="tablenav-pages">';
    		$out.=  $p->getOutput();  
    		$out.= '</div></div>';
    	//Pagination
		
		
		
    	//end Pagination
        return $out;
    }
    else
    {
        return("<p>There are no media files uploaded yet</p>");
    }
    
}

function ca_display_file($file_id=NULL)
{
    /**
 *  Display file from template
 * 
 * @author  Andy Moyle
 * @param    $file_id
 * @return   
 * @version  0.1
 * 
 */
    global $wpdb,$ca_podcast_settings;
	$upload_dir = wp_upload_dir();
	$path=$upload_dir['basedir'].'/sermons/';
	$url=content_url().'/uploads/sermons/';
    if(!$file_id)return("<p>There is no file to display</p>");
    $template=get_option('ca_podcast_file_template');
    $sql='SELECT a.*,b.* FROM '.CA_FIL_TBL.' a, '.CA_SERM_TBL.' b WHERE a.series_id=b.series_id AND a.file_id="'.esc_sql($file_id).'"';
    
    $data=$wpdb->get_row($sql);
    
    if($data)
    {
		$data->speaker_name=$data->speaker;
        $template=str_replace('[VIDEO_URL]',"\r\n".esc_url($data->video_url)."\r\n",$template);
		$template=str_replace('[FILE_TITLE]',esc_html($data->file_title),$template);
		$template=str_replace('[FILE_ID]',esc_html($data->file_id),$template);
		$template=str_replace('[FILE_DATE]',mysql2date(get_option('date_format'),$data->pub_date),$template);
        
		$template=str_replace('[FILE_PLAYS]','Played: <span class="plays'.intval($data->file_id).'">'.esc_html(church_admin_plays($data->file_id)).'</span> times',$template);
		
        if(!empty($data->file_name) && file_exists($path.$data->file_name))
		{
			
			$template=str_replace('[FILE_NAME]','<p><audio class="sermonmp3" id="'.esc_html($data->file_id).'" src="'.esc_url($url.$data->file_name).'" preload="none"></audio></p>',$template);
			$template=str_replace('[FILE_URI]',esc_url($url.$data->file_name),$template);
			$template=str_replace('[FILE_DOWNLOAD]','<a href="'.esc_url($url.$data->file_name).'" title="'.esc_html($data->file_title).'">'.strtoupper(esc_html($data->file_title)).'</a>',$template);
		}
		elseif(!empty($data->external_file))
		{
			$template=str_replace('[FILE_NAME]','<p><audio class="sermonmp3" id="'.esc_html($data->file_id).'" src="'.esc_url($data->external_file).'" preload="none"></audio></p>',$template);
			$template=str_replace('[FILE_URI]',$data->external_file,$template);
			$template=str_replace('[FILE_DOWNLOAD]','<a href="'.esc_url($data->external_file).'" title="'.esc_html($data->file_title).'">'.strtoupper(esc_html($data->file_title)).'</a>',$template);
		}
        if(file_exists($path.$data->transcript))
        {
			$template=str_replace('[TRANSCRIPT]','<a href="'.esc_url($url.$data->transcript).'" title="'.esc_html($data->transcript).'">'.esc_html($data->transcript).'</a>',$template);
        
		}
		else
		{
			$template=str_replace('[TRANSCRIPT]','',$template);
        
		}	
        $template=str_replace('[FILE_DESCRIPTION]',esc_html($data->file_description),$template);
        $template=str_replace('[SERIES_NAME]','<a href="'.esc_url(get_permalink().'?series_id='.$data->series_id).'">'.esc_html($data->series_name).'</a>',$template);
        $template=str_replace('[SPEAKER_NAME]','<a href="'.esc_url(get_permalink().'?speaker_name='.urlencode($data->speaker_name)).'">'.esc_html($data->speaker_name).'</a>',$template);
        //$template=str_replace('[SPEAKER_DESCRIPTION]',$data->speaker_description,$template);
      
		return $template;
    }
    else
    {
        return "";
    }
    
}

?>