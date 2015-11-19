<?php
	defined( 'ABSPATH' ) or die( "Direct access to this page is disabled!!!" );
	
/**
 * Class Name: MDJM_Venue_Posts
 * Manage the Venue posts
 *
 *
 *
 */
if( !class_exists( 'MDJM_Venue_Posts' ) ) :
	class MDJM_Venue_Posts	{
		/**
		 * Initialise
		 */
		public static function init()	{
			add_action( 'manage_mdjm-venue_posts_custom_column' , array( __CLASS__, 'venue_posts_custom_column' ), 10, 2 );
								
			add_filter( 'manage_mdjm-venue_posts_columns' , array( __CLASS__, 'venue_post_columns' ) );
			
			add_filter( 'manage_edit-mdjm-venue_sortable_columns', array( __CLASS__, 'venue_post_sortable_columns' ) );
			
			add_filter( 'bulk_actions-edit-mdjm-venue', array( __CLASS__, 'venue_bulk_action_list' ) );
		} // init
		
		/**
		 * Define the columns to be displayed for venue posts
		 *
		 * @params	arr		$columns	Array of column names
		 *
		 * @return	arr		$columns	Filtered array of column names
		 */
		function venue_post_columns( $columns ) {
			$columns = array(
					'cb'			 => '<input type="checkbox" />',
					'title' 	 	  => __( 'Venue', 'mobile-dj-manager' ),
					'contact'		=> __( 'Contact', 'mobile-dj-manager' ),
					'phone'		  => __( 'Phone', 'mobile-dj-manager' ),
					'town'		   => __( 'Town', 'mobile-dj-manager' ),
					'county'   		 => __( 'County', 'mobile-dj-manager' ),
					'event_count'	=> __( 'Events', 'mobile-dj-manager' ),
					'info'		   => __( 'Information', 'mobile-dj-manager' ),
					'details'	    => __( 'Details', 'mobile-dj-manager' ),
				);
			
			return $columns;
		} // venue_post_columns
		
		/**
		 * Define which columns are sortable for venue posts
		 *
		 * @params	arr		$sortable_columns	Array of event post sortable columns
		 *
		 * @return	arr		$sortable_columns	Filtered Array of event post sortable columns
		 */
		function venue_post_sortable_columns( $sortable_columns )	{
			$sortable_columns['town'] = 'town';
			$sortable_columns['county'] = 'county';
			
			return $sortable_columns;
		} // venue_post_sortable_columns
		
		/**
		 * Define the data to be displayed in each of the custom columns for the Event post types
		 *
		 * @param	str		$column_name	The name of the column to display
		 *			int		$post_id		The current post ID
		 * 
		 *
		 */
		function venue_posts_custom_column( $column_name, $post_id )	{				
			switch ( $column_name ) {
				case 'contact':
					echo sprintf( 
						'<a href="mailto:%s">%s</a>', 
						get_post_meta( $post_id,
						'_venue_email', true ),
						stripslashes( get_post_meta( $post_id, '_venue_contact', true ) ) );					
					break;
				
				/* -- Phone -- */
				case 'phone':
					echo get_post_meta( $post_id, '_venue_phone', true );
					break;
				
				/* -- Town -- */
				case 'town':
					echo get_post_meta( $post_id, '_venue_town', true );
					break;
					
				/* -- County -- */
				case 'county':
					echo get_post_meta( $post_id, '_venue_county', true );
					break;
				
				case 'event_count':
					$events_at_venue = get_posts( 
						array(
							'post_type'	=> MDJM_EVENT_POSTS,
							'meta_key'	 => '_mdjm_event_venue_id',
							'meta_value'   => $post_id,
							'post_status'  => array( 'mdjm-approved', 'mdjm-contract', 'mdjm-completed', 'mdjm-enquiry', 'mdjm-unattended' ) ) );
					
					echo( !empty( $events_at_venue ) ? count( $events_at_venue ) : '0' );
					break;	
				/* -- Information -- */
				case 'info':
					echo stripslashes( get_post_meta( $post_id, '_venue_information', true ) );
					break;
				
				/* -- Details -- */
				case 'details':
					$venue_terms = get_the_terms( $post_id, 'venue-details' );
					$venue_term = '';
					if( !empty( $venue_terms ) )	{
						$venue_term .= '<ul class="details">' . "\r\n";
						foreach( $venue_terms as $v_term )	{
							$venue_term .= '<li>' . $v_term->name . '</li>' . "\r\n";	
						}
						$venue_term .= '</ul>' . "\r\n";
					}
					echo ( !empty( $venue_term ) ? $venue_term : '' );
					break;
			} // switch ( $column_name )
		} // venue_posts_custom_column
		
		/**
		 * Remove the edit bulk action from the venue posts list
		 *
		 * @params	arr		$actions	Array of actions
		 *
		 * @return	arr		$actions	Filtered Array of actions
		 */
		function venue_bulk_action_list( $actions )	{
			unset( $actions['edit'] );
			
			return $actions;
		} // venue_bulk_action_list
	} // class MDJM_Venue_Posts
endif;
	MDJM_Venue_Posts::init();