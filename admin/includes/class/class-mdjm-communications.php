<?php
/**
 * class-mdjm-communications.php
 * 12/03/2015
 * @since 1.1.2
 * The class for MDJM communication functions
 *
 * @version 1.0
 */

	class MDJM_Communication	{
		function __construct() {
			$post_type = 'mdjm_communication';
		} // __construct
		
		/*
		 * insert_comm
		 * 12/03/2015
		 * @since 1.1.2
		 * Adds the communication data into the post table
		 *
		 * @param: $args => array
		 *			required: subject, content, recipient, source, event
		 *			optional: status, author
		 * @return: the post id
		 */
		function insert_comm( $args )	{
			if( empty( $args ) || !is_array( $args ) )
				return f_mdjm_update_notice( 'update-nag', 'The communication was not logged' );
							
			$post_args['post_title'] = isset( $args['subject'] ) ? wp_strip_all_tags( $args['subject'] ) : '';
			$post_args['post_content'] = isset( $args['content'] ) ? $args['content'] : '';
			$post_args['post_status'] = isset( $args['status'] ) ? $args['status'] : 'ready to send';
			$post_args['post_author'] = isset( $args['author'] ) ? $args['author'] : get_current_user_id();
			
			$meta_args['date_sent'] = isset( $args['date_sent'] ) ? $args['date_sent'] : time();
			$meta_args['recipient'] = isset( $args['recipient'] ) ? $args['recipient'] : '';
			$meta_args['source'] = isset( $args['source'] ) ? $args['source'] : '';
			$meta_args['event'] = isset( $args['event'] ) ? $args['event'] : '';

			if( empty( $post_args['post_title'] ) || empty( $post_args['post_content'] ) || empty( $meta_args['recipient'] ) || empty( $meta_args['source'] ) || empty( $meta_args['event'] ) )
				return f_mdjm_update_notice( 'update-nag', 'The communication was not logged' );
				
			$post_args['post_type'] = 'mdjm_communication';
			$post_args['ping_status'] = false;
			$post_args['comment_status'] = 'closed';
			
			$comm_post_id = wp_insert_post( $post_args );
			
			if( $comm_post_id )	{
				foreach( $meta_args as $meta_key => $meta_value )	{
					add_post_meta( $comm_post_id, '_' . $meta_key, $meta_value );	
				}	
			}
			return $comm_post_id;
		} // insert_comm
		
		/* insert_stat_image
		 * 13/03/2015
		 * @since 1.1.2
		 * Inserts the stat tracker image
		 * @param: $p => the post ID
		 */
		function insert_stat_image( $p )	{
			global $mdjm_options;
			
			$trackit = !empty( $mdjm_options['track_client_emails'] ) ? $mdjm_options['track_client_emails'] : '';
			
			if( empty( $p ) || empty( $trackit ) )
				return;
				
			$stat = sprintf( '<img alt="" src="'. home_url() . '/?mdjm-api=%s&post=%s&action=%s" border="0"  height="3"  width="37" />', 'MDJM_EMAIL_RCPT', $p, 'open_email' );
			
			return $stat;

		} // insert_stat_image
		
		/* track_email_open
		 * 13/03/2015
		 * @since 1.1.2
		 * Records the opening of an email
		 * @param: $p => the post ID
		 */
		function track_email_open( $p )	{
			if( empty( $p ) )
				return;
			
			/* -- Display the invisible image on screen -- */
			header( 'Cache-Control: no-store, no-cache, must-revalidate' );
			header( 'Cache-Control: post-check=0, pre-check=0', false );
			header( 'Pragma: no-cache' );
		
			if( empty( $stat_image ) )
				$stat_image = WPMDJM_PLUGIN_DIR . '/admin/images/invpicture.png';
				
			$handle = fopen( $stat_image, 'r' );
		
			if( !$handle )
				exit;

			header( 'Content-type: image/png' );
			$contents = fread( $handle, filesize( $stat_image ) );
			fclose( $handle );
			echo $contents;
			
			/* -- Update the tracking information -- */
			$open_count = get_post_meta( $p, '_open_count', true );
			$current_count = !empty( $open_count ) ? $open_count : '0';
			
			wp_update_post( array( 'ID' => $p, 'post_status' => 'opened' ) );
	
			update_post_meta( $p, '_status_change', time() );
			update_post_meta( $p, '_open_count', $current_count + 1 );

		} // track_email_open
		
		/* change_email_status
		 * 13/03/2015
		 * @since 1.1.2
		 * Changes the current status of an email
		 * @param: $p => the post ID
		 */
		function change_email_status( $p, $status )	{
			
			if( empty( $p ) || empty( $status ) )
				return;
			
			wp_update_post( array( 'ID' => $p, 'post_status' => $status ) );
			update_post_meta( $p, '_status_change', time() );
		} // change_email_status
		
	} // class
?>