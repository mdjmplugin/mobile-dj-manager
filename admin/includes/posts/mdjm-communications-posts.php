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
			
			add_filter( 'manage_mdjm_communication_posts_columns' , array( __CLASS__, 'mdjm_communication_post_columns' ) );
			add_filter( 'bulk_actions-edit-mdjm_communication', array( __CLASS__, 'mdjm_communication_bulk_action_list' ) ); // Remove Edit from Bulk Actions
			
		} // init
		
		/**
		 * Define the post columns for the communication posts
		 *
		 *
		 *
		 *
		 */
		function mdjm_communication_post_columns( $columns )	{
			$columns = array(
				'cb'			   => '<input type="checkbox" />',
				'date_sent' 		=> __( 'Date Sent', 'mobile-dj-manager' ),
				'title' 	 		=> __( 'Email Subject', 'mobile-dj-manager' ),
				'from'		   	 => __( 'From', 'mobile-dj-manager' ),
				'recipient' 		=> __( 'Recipient', 'mobile-dj-manager' ),
				'event'			=> __( 'Associated Event', 'mobile-dj-manager' ),
				'current_status'   => __( 'Status', 'mobile-dj-manager' ),
				'source'		   => __( 'Source', 'mobile-dj-manager' ) );
				
			return $columns;
		} // mdjm_communication_post_columns
		
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
		
		/**
		 * Adjust the options available within the Bulk Actions drop down.
		 * Remove the Edit option and return the remaining options
		 *
		 *
		 */
		function mdjm_communication_bulk_action_list( $actions )	{
			unset( $actions['edit'] );
			return $actions;
		} // mdjm_communication_bulk_action_list
				
	} // MDJM_Comms_Posts
endif;
	MDJM_Comms_Posts::init();