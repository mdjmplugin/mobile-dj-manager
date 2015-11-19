<?php
	defined( 'ABSPATH' ) or die( "Direct access to this page is disabled!!!" );
	
/**
 * Class Name: MDJM_Email_Template_Posts
 * Manage the Email Template posts
 *
 *
 *
 */
if( !class_exists( 'MDJM_Email_Template_Posts' ) ) :
	class MDJM_Email_Template_Posts	{
		/**
		 * Initialise
		 */
		public static function init()	{								
			add_filter( 'manage_email_template_posts_columns' , array( __CLASS__, 'email_template_post_columns' ) );
						
		} // init
		
		/**
		 * Define the columns to be displayed for Email Template posts
		 *
		 * @params	arr		$columns	Array of column names
		 *
		 * @return	arr		$columns	Filtered array of column names
		 */
		function email_template_post_columns( $columns ) {
			$columns = array(
					'cb'		=> '<input type="checkbox" />',
					'title'		=> __( 'Email Subject', 'mobile-dj-manager' ),
					'author'	=> __( 'Created By', 'mobile-dj-manager' ),
					'date'		=> __( 'Date', 'mobile-dj-manager' ) );
			
			return $columns;
		} // email_template_post_columns			
	} // class MDJM_Email_Template_Posts
endif;
	MDJM_Email_Template_Posts::init();