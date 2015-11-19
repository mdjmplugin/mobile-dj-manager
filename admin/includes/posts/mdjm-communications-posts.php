<?php
	defined( 'ABSPATH' ) or die( "Direct access to this page is disabled!!!" );
	
/**
 * Class Name: MDJM_Comms_Posts
 * Manage the Communication posts
 *
 *
 *
 */
if( !class_exists( 'MDJM_Comms_Posts' ) ) : 
	class MDJM_Comms_Posts	{
		/**
		 * Initialise
		 */
		public static function init()	{
			add_action( 'manage_mdjm_communication_posts_custom_column' , array( __CLASS__, 'mdjm_communication_posts_custom_column' ), 10, 2 );
		} // init
		
		
		
		/**
		 * Define the data to be displayed in each of the custom columns for the Communications post types
		 *
		 * @param	str		$column_name	The name of the column to display
		 *			int		$post_id		The current post ID
		 * 
		 *
		 */
		function mdjm_communication_posts_custom_column( $column_name, $post_id )	{
			global $post;
			
			switch( $column_name ) {
				// Date Sent
				case 'date_sent':
					echo date( MDJM_TIME_FORMAT . ' ' . MDJM_SHORTDATE_FORMAT, get_post_meta( $post_id, '_date_sent', true ) );
					break;
				
				// From	
				case 'from':
					if( $author = get_userdata( $post->post_author ) )	{
						echo sprintf( '<a href="' . admin_url( 'user-edit.php?user_id=%s' ) . '">%s</a>', $author->ID, ucwords( $author->display_name ) );
					}
					else	{
						echo get_post_meta( $post_id, '_recipient' );	
					}
					break;
				
				// Recipient
				case 'recipient':
					if( $client = get_userdata( get_post_meta( $post_id, '_recipient', true ) ) )	{
						echo sprintf( '<a href="' . admin_url( 'user-edit.php?user_id=%s' ) . '">%s</a>', $client->ID, ucwords( $client->display_name ) );
					}
					else	{
						echo get_post_meta( $post_id, '_recipient' );	
					}
					break;
					
				// Associated Event
				case 'event':
					$event = get_post_meta( $post_id, '_event', true );
					
					echo ( !empty( $event ) ? '<a href="'. get_edit_post_link( $event ) . '">' . MDJM_EVENT_PREFIX . $event . '</a>' : 'N/A' );
					
					break;
				
				// Status
				case 'current_status':							
					$change_date = !empty( $post->post_modified ) && $post->post_status == 'opened' ? date( MDJM_TIME_FORMAT . ' ' . MDJM_SHORTDATE_FORMAT, strtotime( $post->post_modified ) ) : '';
					$open_count = !empty( $count ) && $post->post_status == 'opened' ? ' (' . $count . ')' : '';
					
					echo ucwords( $post->post_status ) . ' ' . 
					( !empty( $post->post_modified ) && $post->post_status == 'opened' ? 
					date( MDJM_TIME_FORMAT . ' ' . MDJM_SHORTDATE_FORMAT, strtotime( $post->post_modified ) ) : '' );
					break;
				
				// Source
				case 'source':
					echo stripslashes( get_post_meta( $post_id, '_source', true ) );
					break;
			} // switch( $column_name	)
		} // mdjm_communication_posts_custom_column
	} // MDJM_Comms_Posts
endif;
	MDJM_Comms_Posts::init();