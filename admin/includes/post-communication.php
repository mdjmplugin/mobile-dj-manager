<?php
/*
 * Meta box layout and data
 */
 	/*
	 * mdjm_communication_post_details_metabox
	 * The layout and data for the email details meta box
	 *
	 * @since 1.1.2
	 * @params: $post => array
	 */
	function mdjm_communication_post_details_metabox( $post )	{
		global $mdjm_options;
		
		$from = get_userdata( $post->post_author );
		$recipient = get_userdata( get_post_meta( $post->ID, 'recipient', true ) );
		
		?>
        <p><strong>Date</strong>: <?php echo date( $mdjm_options['time_format'] . ' ' . $mdjm_options['short_date_format'], get_post_meta( $post->ID, 'date_sent', true ) ); ?></p>
        <p><strong>From</strong>: <a href="<?php echo admin_url( '/user-edit.php?user_id=' . $from->ID ); ?>"><?php echo $from->display_name; ?></a></p>
        <p><strong>Recipient</strong>: <a href="<?php echo admin_url( '/user-edit.php?user_id=' . $recipient->ID ); ?>"><?php echo $recipient->display_name; ?></a></p>
        <p><strong>Status</strong>: <?php echo ucfirst( $post->post_status ); ?></p>
        <p><strong>Event</strong>: <a href="<?php f_mdjm_admin_page( 'events' ); ?>&action=&action=view_event_form&event_id=<?php echo get_post_meta( $post->ID, 'event', true ); ?>"><?php echo $mdjm_options['id_prefix'] . stripslashes( get_post_meta( $post->ID, 'event', true ) ); ?></a></p>
        
        <a class="button-secondary" href="<?php echo $_SERVER['HTTP_REFERER']; ?>" title="<?php _e( 'Back to List' ); ?>"><?php _e( 'Back' ); ?></a>
        
        <?php
	} // comm_post_details_metabox
	
	/*
	 * mdjm_communication_post_output_metabox
	 * Print out the email content within the Email Content meta box
	 *
	 * @since 1.1.2
	 * @params: $post => array
	 */
	function mdjm_communication_post_output_metabox( $post )	{
		echo $post->post_content;
	} // mdjm_communication_post_output_metabox
?>