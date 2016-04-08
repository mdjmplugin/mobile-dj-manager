<?php
	defined( 'ABSPATH' ) or die( "Direct access to this page is disabled!!!" );
	
/**
 * Class Name: MDJM_Posts
 * Manage generic post functions within the MDJM application.
 * 
 *
 *
 */
if( !class_exists( 'MDJM_Posts' ) )	:
	class MDJM_Posts	{
		
		/**
		 * The Constructor
		 */
		public function __construct()	{
			global $mdjm_post_types;

			/* -- Register actions -- */
															
			if( is_admin() )	{
			}

		} // __construct()
		
/**
* -- POST SAVES
*/
		/*
		 * save_custom_post
		 * Launched as a post is saved, or edited
		 * Calls mdjm_custom_post_save
		 *
		 */
		public function save_custom_post( $post_id, $post )	{
							
		} // save_custom_post														

/**
* -- GENERAL POST FUNCTIONS
*/
		
		/**
		 * Determines if a post, identified by the specified ID, exist
		 * within the WordPress database.
		 * 
		 *
		 * @param    int    $id    The ID of the post to check
		 * @return   bool          True if the post exists; otherwise, false.
		 * @since    1.1.1
		 */
		public function post_exists( $id )	{
			return is_string( get_post_status( $id ) );	
		} // post_exists		
	} // class
endif;