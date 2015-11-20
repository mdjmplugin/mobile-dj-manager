<?php
	defined( 'ABSPATH' ) or die( "Direct access to this page is disabled!!!" );
	
/**
 * Class Name: MDJM_Contract_Posts
 * Manage the Contract posts
 *
 *
 *
 */
if( !class_exists( 'MDJM_Contract_Posts' ) ) :
	class MDJM_Contract_Posts	{
		/**
		 * Initialise
		 */
		public static function init()	{
			add_action( 'manage_contract_posts_custom_column' , array( __CLASS__, 'contract_posts_custom_column' ), 10, 2 );
								
			add_filter( 'manage_contract_posts_columns' , array( __CLASS__, 'contract_post_columns' ) );
						
		} // init
		
		/**
		 * Define the columns to be displayed for contract posts
		 *
		 * @params	arr		$columns	Array of column names
		 *
		 * @return	arr		$columns	Filtered array of column names
		 */
		function contract_post_columns( $columns ) {
			$columns = array(
					'cb'			   => '<input type="checkbox" />',
					'title' 			=> __( 'Contract Name', 'mobile-dj-manager' ),
					'event_default'	=> __( 'Is Default?', 'mobile-dj-manager' ),
					'assigned'		 => __( 'Assigned To', 'mobile-dj-manager' ),
					'author'		   => __( 'Created By', 'mobile-dj-manager' ),
					'date' 			 => __( 'Date', 'mobile-dj-manager' ) );
			
			return $columns;
		} // contract_post_columns
				
		/**
		 * Define the data to be displayed in each of the custom columns for the Contract post types
		 *
		 * @param	str		$column_name	The name of the column to display
		 *			int		$post_id		The current post ID
		 * 
		 *
		 */
		function contract_posts_custom_column( $column_name, $post_id )	{
			switch ( $column_name ) {
				// Is Default?
				case 'event_default':
					echo ( $post_id == $GLOBALS['mdjm_settings']['events']['default_contract'] ? 
						'<span style="color: green; font-weight: bold;">' . __( 'Yes', 'mobile-dj-manager' ) . '</span>' : __( 'No', 'mobile-dj-manager' ) );
					break;
				// Assigned To
				case 'assigned':
					$contract_events = get_posts(
						array(
							'post_type'		=> MDJM_EVENT_POSTS,
							'posts_per_page'   => -1,
							'meta_key'	 	 => '_mdjm_event_contract',
							'meta_value'   	   => $post_id,
							'post_status'  	  => 'any',
							)
						);
					
					$total = count( $contract_events );
					echo $total . ' ' . _n( 'Event', 'Events', $total, 'mobile-dj-manager' );
					break;	
			} // switch
		} // contract_posts_custom_column		
	} // class MDJM_Contract_Posts
endif;
	MDJM_Contract_Posts::init();