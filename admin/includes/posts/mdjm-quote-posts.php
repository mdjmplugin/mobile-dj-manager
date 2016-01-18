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
			
	} // class MDJM_Quote_Posts
endif;
	MDJM_Quote_Posts::init();