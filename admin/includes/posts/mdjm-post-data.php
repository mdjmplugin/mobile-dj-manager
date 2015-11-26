<?php
	defined( 'ABSPATH' ) or die( "Direct access to this page is disabled!!!" );
	
/**
 * Class Name: MDJM_Post_Data
 * Description: Set custom post type columns and data
 *
 *
 */
if( !class_exists( 'MDJM_Post_Data' ) ) :
	class MDJM_Post_Data	{
		/**
		 * Hook in methods.
		 */
		public static function init()	{
			add_filter( 'manage_mdjm_hosted_posts_columns' , array( __CLASS__, 'add_columns' ) );
			add_action( 'manage_posts_custom_column' , array( __CLASS__, 'post_columns_data' ), 10, 1 );	
			
			add_filter( 'post_row_actions',
						array( __CLASS__, 'post_row_actions' ), 
						10, 2 );
						
			add_action( 'post_edit_form_tag' , array( __CLASS__, 'form_enctype' ) );
						
			// Save post actions
			add_action( 'save_post', array( __CLASS__, 'save_plugin' ), 10, 2 );
			
			// Delete post actions
			//add_action( 'delete_post', array( __CLASS__, 'mhp_delete_post' ) );
			
			add_action( 'admin_print_footer_scripts', array( __CLASS__, 'mhp_add_quicktags' ) );
		} // init
	} // MDJM_Post_Data
endif;

	MDJM_Post_Data::init();