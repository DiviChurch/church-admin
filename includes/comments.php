<?php

/**
 *
 * Comment form
 * 
 * @author  Andy Moyle
 * @param    null
 * @return   
 * @version  0.1
 * 
 */ 
 function church_admin_comment($comment_type='people',$comment_id=NULL,$parent_id=NULL,$ID)
 {
 
 	global $wpdb,$current_user;

 	wp_get_current_user();
 	

 		if(!empty($comment_id))$data=$wpdb->get_row('SELECT * FROM '.CA_COM_TBL.' WHERE comment_id="'.intval($comment_id).'"');
 	
 		echo'<form action="'.$_SERVER['REQUEST_URI'].'" method="POST">';
 		$placeholder='placeholder="'.__('Leave a note','church-admin').'"';
 		if(!empty($parent_id)){$placeholder='placeholder="'.__('Leave a reply','church-admin').'"';}
 		echo'<p><textarea class="ca-comment" name="comment" '.$placeholder.'>';
 		if(!empty($data->comment)) echo esc_html($data->comment);
 		echo '</textarea></p>';
 		if(!empty($ID))echo'<input type="hidden" name="ID" value="'.intval($ID).'"/>';
 		if(!empty($comment_type))echo'<input type="hidden" name="comment_type" value="'.esc_html($comment_type).'"/>';
 		if(!empty($parent_id))echo'<input type="hidden" name="parent_id" value="'.intval($parent_id).'"/>';
 		if(!empty($comment_id))echo'<input type="hidden" name="comment_id" value="'.intval($comment_id).'"/>';
 		echo'<p><input type="hidden" name="save-ca-comment" value="yes"/><input class="button-primary" type="submit" value="'.__('Save Note','church-admin').'&raquo;" /></p>';
 		echo'</form><hr/>';

 	
 }
 
 /**
 *
 * Show Comments
 * 
 * @author  Andy Moyle
 * @param   $comment_type,$ID
 * @return   
 * @version  0.1
 *
 * 2016-11-08 Changed query to LEFT JOIN to make work 
 *
 */ 
 
 function church_admin_show_comments($comment_type,$ID)
 {
 	global $wpdb;

 	echo'<div class="ca-comments">';
 	echo'<h3>'.__('Notes','church-admin').'</h3>';
 	//$sql='SELECT a.*, CONCAT_WS(" ",b.first_name,b.prefix,b.last_name) AS name FROM '.CA_COM_TBL.' a, '.CA_PEO_TBL.' b WHERE a.ID="'.esc_sql($ID).'" AND a.comment_type="'.esc_sql($comment_type).'" AND a.parent_id=0 AND a.author_id=b.user_id ORDER BY timestamp ASC';
 	//need to left join for some reason
 	$sql='SELECT a.*, CONCAT_WS(" ",b.first_name,b.prefix,b.last_name) AS name FROM '.CA_COM_TBL.' a LEFT JOIN '.CA_PEO_TBL.' b ON a.author_id=b.user_id WHERE a.ID="'.esc_sql($ID).'" AND a.comment_type="smallgroup" ORDER BY timestamp ASC';
 	
 	$comments=$wpdb->get_results($sql);
 	
 	if(!empty($comments))
 	{
 		
 		foreach($comments AS $comment)
 		{
 		
 			church_admin_show_comment($comment);
 		}	
 		
 	}
 	church_admin_comment($comment_type,NULL,NULL,$ID);
 	echo'</div><!--ca-comments-->';
 }
 	
 	
 function church_admin_show_comment($comment)
 {
 	global $wpdb;

 	echo'<div class="ca-comment ';
 	if(!empty($comment->parent_id)){ echo 'ca-reply';}
 	echo'" id="comment-'.$comment->comment_id.'">';
 	if(empty($comment->name))$comment->name=$wpdb->get_var('SELECT user_login FROM '.$wpdb->users.' WHERE ID="'.intval($comment->author_id).'"');
 	echo'<p class="ca-comment-meta">'.get_avatar( $comment->author_id,'50').__('Posted by','church-admin').' '.esc_html($comment->name).' '.__('on','church-admin').' '.mysql2date(get_option('date_format'),$comment->timestamp).' <span class="note-delete" data-tab="'.$comment->comment_id.'" ><span style="color:red" class="dashicons dashicons-no"></span>Delete</span> </p>';
 	echo'<p class="ca_comment_content">'.esc_html($comment->comment).'</p>';
 	$replies=$wpdb->get_results('SELECT * FROM '.CA_COM_TBL.' WHERE parent_id="'.intval($comment->comment_id).'" ORDER BY timestamp DESC');
 	if(!empty($replies))
 	{
 		foreach($replies AS $reply)
 		{
 			church_admin_show_comment($reply);
 		}
 	}
 	
 	echo '<p class="ca-comment-reply" id="comment'.$comment->comment_id.'">'.__('Reply (Click to toggle)','church-admin').'</p>';
 	echo'<div id="reply'.$comment->comment_id.'" style="display:none">';
 	church_admin_comment($comment->comment_type,NULL,$comment->comment_id,$comment->ID);
 	echo'</div>';
 	$nonce = wp_create_nonce("church_admin_delete_note");
 	echo'<script>jQuery(document).ready(function($) {
 	$("#comment'.$comment->comment_id.'").click(function(){$("#reply'.$comment->comment_id.'").toggle();});
 	$(".note-delete").on("click",function()
 	{
 		var note_id=$(this).attr("data-tab");
 		
 		var data = {
			"action": "church_admin_note_delete",
			"note_id": note_id,
			"nonce":"'.$nonce.'"
		};

		// since 2.8 ajaxurl is always defined in the admin header and points to admin-ajax.php
		jQuery.post(ajaxurl, data, function(response) {
			if(response)
			{
				var id="#comment-"+note_id;
				console.log(id);
				$(id).hide();
			}
		});
 	});
 	});</script>';
 	echo'</div>';
 }				