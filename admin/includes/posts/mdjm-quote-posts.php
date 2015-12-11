<?php
	defined( 'ABSPATH' ) or die( "Direct access to this page is disabled!!!" );
	
/**
 * Class Name: MDJM_Quote_Posts
 * Manage the online Quote posts
 *
 *
 *
 */
if( !class_exists( 'MDJM_Quote_Posts' ) ) :
	class MDJM_Quote_Posts	{
		/**
		 * Initialise
		 */
		public static function init()	{
			add_action( 'manage_mdjm-quotes_posts_custom_column' , array( __CLASS__, 'quote_posts_custom_column' ), 10, 2 );
								
			add_filter( 'manage_mdjm-quotes_posts_columns' , array( __CLASS__, 'quote_post_columns' ) );
			
			add_filter( 'manage_edit-mdjm-quotes_sortable_columns', array( __CLASS__, 'quote_post_sortable_columns' ) );
			
			add_filter( 'bulk_actions-edit-mdjm-quotes', array( __CLASS__, 'quote_bulk_action_list' ) );
			
			add_action( 'pre_get_posts', array( __CLASS__, 'custom_quote_post_query' ) );
			
			add_filter( 'views_edit-mdjm-quotes' , array( __CLASS__, 'quote_view_filters' ) );			
		} // init
		
		/**
		 * Define the columns to be displayed for quote posts
		 *
		 * @params	arr		$columns	Array of column names
		 *
		 * @return	arr		$columns	Filtered array of column names
		 */
		public static function quote_post_columns( $columns ) {
			$columns = array(
					'cb'				=> '<input type="checkbox" />',
					'date'				=> __( 'Generated', 'mobile-dj-manager' ),
					'quote_event'		=> __( 'Event ID', 'mobile-dj-manager' ),
					'quote_client'		=> __( 'Client', 'mobile-dj-manager' ),
					'quote_value'		=> __( 'Quote Value', 'mobile-dj-manager' ),
					'quote_view_date'	=> __( 'Date Viewed', 'mobile-dj-manager' ),
					'quote_view_count'	=> __( 'View Count', 'mobile-dj-manager' ) );
			
			return $columns;
		} // quote_post_columns
		
		/**
		 * Define which columns are sortable for quote posts
		 *
		 * @params	arr		$sortable_columns	Array of event post sortable columns
		 *
		 * @return	arr		$sortable_columns	Filtered Array of event post sortable columns
		 */
		public static function quote_post_sortable_columns( $sortable_columns )	{
			$sortable_columns['quote_view_date'] = 'quote_view_date';
			$sortable_columns['quote_value'] = 'quote_value';
			
			return $sortable_columns;
		} // event_post_sortable_columns
				
		/**
		 * Define the data to be displayed in each of the custom columns for the Quotes post types
		 *
		 * @param	str		$column_name	The name of the column to display
		 *			int		$post_id		The current post ID
		 * 
		 *
		 */
		public static function quote_posts_custom_column( $column_name, $post_id )	{
			global $post;
			
			if( $column_name == 'quote_event' || $column_name == 'quote_value' )
				$parent = wp_get_post_parent_id( $post_id );
			
			switch ( $column_name ) {
				// Quote Date
				case 'date':
					echo date( 'd M Y H:i:s', strtotime( $post->post_date ) );
				break;
				
				// Event
				case 'quote_event':					
					echo ( !empty( $parent ) ? '<a href="' . admin_url( '/post.php?post=' . $parent . 
						'&action=edit' ) . '">' . MDJM_EVENT_PREFIX . $parent . '</a><br />' . 
						date( MDJM_SHORTDATE_FORMAT, strtotime( get_post_meta( $parent, '_mdjm_event_date', true ) ) ) : 
						'N/A' );
				break;
				
				// Client
				case 'quote_client':
					echo '<a href="' . admin_url( 'admin.php?page=mdjm-clients&action=view_client&client_id=' . $post->post_author ) . '">' . get_the_author() . '</a>';
				break;
				
				// Cost
				case 'quote_value':
					echo display_price( get_post_meta( $parent, '_mdjm_event_cost', true ) );
				break;
				
				// Date Viewed
				case 'quote_view_date':
					echo ( $post->post_status == 'mdjm-quote-viewed' ? 
						date( 'd M Y H:i:s', strtotime( get_post_meta( $post_id, '_mdjm_quote_viewed_date', true ) ) ) : 'N/A' );
				break;
				
				// View Count
				case 'quote_view_count':
					$count = get_post_meta( $post_id, '_mdjm_quote_viewed_count', true );
					if( empty( $count ) )
						$count = 0;
						
					echo $count . ' ' . _n( 'time', 'times', $count, 'mobile-dj-manager' );
				break;	
			} // switch
		} // quote_posts_custom_column
		
		/**
		 * Remove the edit bulk action from the quote posts list
		 *
		 * @params	arr		$actions	Array of actions
		 *
		 * @return	arr		$actions	Filtered Array of actions
		 */
		public static function quote_bulk_action_list( $actions )	{
			unset( $actions['edit'] );
			
			return $actions;
		} // quote_bulk_action_list
		
		/**
		 * Customise the post query 
		 *
		 *
		 *
		 *
		 */
		public static function custom_quote_post_query( $query )	{
			global $pagenow;
			
			if( !is_post_type_archive( MDJM_QUOTE_POSTS ) || !$query->is_main_query() || !$query->is_admin || 'edit.php' != $pagenow )
				return $query;
			
			// If the current user cannot list all quotes, lets limit the results	
			if( !MDJM()->permissions->employee_can( 'list_all_quotes' ) )	{
				global $user_ID;
				
				$events = $total = MDJM()->events->dj_events( $user_ID );
				
				foreach( $events as $event )	{
					$quote = MDJM()->events->retrieve_quote( $event->ID, 'any' );
					
					if( !empty( $quote ) )	{
						$quotes[] = $quote;	
					}
				}
				
				if( !empty( $quotes ) )
					$query->set( 'post__in', $quotes );
			}
		} // custom_quote_post_query
		
		/**
		 * Customise the view filter counts
		 *
		 * @called	views_edit-post hook
		 *
		 *
		 */
		public static function quote_view_filters( $views )	{
			// We only run this filter if the user has restrictive caps and the post type is mdjm-event
			if( MDJM()->permissions->employee_can( 'list_all_quotes' ) || !is_post_type_archive( MDJM_QUOTE_POSTS ) )
				return $views;
			
			global $user_ID;
			$events = $total = MDJM()->events->dj_events( $user_ID );
				$all = 0;
				
				foreach( $events as $event )	{
					$quote = MDJM()->events->retrieve_quote( $event->ID, 'any' );
					$quote_status = get_post_status( $quote );
					
					if( !isset( $status[$quote_status] ) )
						$status[$quote_status] = 1;
						
					else
						$status[$quote_status]++;
						
					$all++;
				}
			
			// The All filter
			$views['all'] = preg_replace( '/\(.+\)/U', '(' . $all . ')', $views['all'] ); 
									
			foreach( $status as $s => $total )	{				
				if( empty( $s ) )	{
					if( isset( $views[$s] ) )
						unset( $views[$s] );
					
					continue;
				}
					
				$views[$s] = preg_replace( '/\(.+\)/U', '(' . $total . ')', $views[$s] );	
			}
			
			// Only show the views we want
			foreach( $views as $filter => $link )	{
				if( $filter != 'all' && !array_key_exists( $filter, $status ) )
					unset( $views[$filter] );	
			}
			
			return $views;
		} // event_view_filters
			
	} // class MDJM_Quote_Posts
endif;
	MDJM_Quote_Posts::init();