<?php
/*
 * class-mdjm.php
 * 10/03/2015
 * @since 1.1.2
 * The main MDJM class
 */
	class MDJM	{
	 /*
	  * __construct
	  * defines the params used within the class
	  *
	  *
	  */
		function __construct()	{
			global $wpdb, $mdjm_post_types, $db_tables;
			/* -- Constants -- */
			define( 'MDJM_NAME', 'Mobile DJ Manager for Wordpress');
			define( 'MDJM_VERSION_KEY', 'version');
			define( 'MDJM_VERSION_NUM', '1.1.2' );
			define( 'MDJM_REQUIRED_WP_VERSION', '3.9' );
			define( 'MDJM_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );
			define( 'MDJM_PLUGIN_NAME', trim( dirname( WPMDJM_PLUGIN_BASENAME ), '/' ) );
			define( 'MDJM_SETTINGS_KEY', 'mdjm_plugin_settings' );
			define( 'MDJM_FETEXT_SETTINGS_KEY', 'mdjm_frontend_text' );
			define( 'MDJM_PAYMENTS_KEY', 'mdjm_pp_options' );
			define( 'MDJM_FUNCTIONS', MDJM_PLUGIN_DIR . '/includes/mdjm-functions.php' );
			
			if( is_admin() )	{
				define( 'MDJM_COMM_POSTS', 'mdjm_communication' );
				define( 'MDJM_CONTRACT_POSTS', 'contract' );
				define( 'MDJM_EMAIL_POSTS', 'email_template' );
				define( 'MDJM_VENUE_POSTS', 'mdjm-venue' );
				
				$mdjm_post_types = array(
							MDJM_COMM_POSTS,
							MDJM_CONTRACT_POSTS,
							MDJM_EMAIL_POSTS,
							MDJM_VENUE_POSTS,
							);
				$db_tables = array(
							'events'        => $wpdb->prefix . 'mdjm_events',
							'playlists'     => $wpdb->prefix . 'mdjm_playlists',
							'journal'       => $wpdb->prefix . 'mdjm_journal',
							'venues'        => $wpdb->prefix . 'mdjm_venues',
							'holiday'       => $wpdb->prefix . 'mdjm_avail',
							'trans'		 => $wpdb->prefix . 'mdjm_trans',
								);
			}
			require_once( MDJM_FUNCTIONS ); // Call the main functions file
						
			/* -- This is our custom post save hook. Needs $_POST['mdjm_update_custom_post'] == 'mdjm_update' -- */
			if( !empty( $_POST['mdjm_update_custom_post'] ) && $_POST['mdjm_update_custom_post'] == 'mdjm_update' )
				add_action( 'save_post', array( $this, 'save_custom_post' ), 10, 2 );
		} // __construct
/*
 * -- Post Methods --
 */
 		/*
		 * set_post_types
		 * Launches various actions to configure custom post types
		 * taxonomies and terms
		 *
		 */			
	  	function set_post_types()	{
			include( WPMDJM_PLUGIN_DIR . '/admin/includes/posts.php' );	
		} // set_post_types
		
		/*
		 * save_custom_post
		 * Launched as a post is saved, or edited
		 * Calls mdjm_custom_post_metabox_save
		 *
		 */
		function save_custom_post( $post_id, $post )	{
			global $mdjm_post_types;
			
			if( !in_array( $post->post_type, $mdjm_post_types ) )	{
				return;
			}
				
			require( WPMDJM_PLUGIN_DIR . '/admin/includes/metabox.php' );
			mdjm_custom_post_metabox_save( $post_id, $post );
		} // save_custom_post
		
		
	} // class
?>