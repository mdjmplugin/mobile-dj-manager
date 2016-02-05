<?php
	defined( 'ABSPATH' ) or die( "Direct access to this page is disabled!!!" );
/**
 * Plugin Name: MDJM Event Management
 * Plugin URI: http://www.mydjplanner.co.uk
 * Description: MDJM Event Management is an interface to fully manage your DJ/Events or Agency business efficiently.
 * Version: 1.2.7.5
 * Date: 26 November 2015
 * Author: My DJ Planner <contact@mydjplanner.co.uk>
 * Author URI: http://www.mydjplanner.co.uk
 * Text Domain: mobile-dj-manager
 * Domain Path: /lang
 * License: GPL2
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Tags: MDJM, MDJM Event Management, Mobile DJ Manager, DJ, Mobile DJ, DJ Planning, Event Planning, CRM, Event Planner, DJ Event Planner, DJ Agency, DJ Tool, Playlist Management, Contact Forms, Mobile Disco, Disco, Event Management, DJ Manager, DJ Management, Music, Playlist, Music Playlist
 */
/**
   MDJM Event Management is free software; you can redistribute it and/or modify
   it under the terms of the GNU General Public License, version 2, as 
   published by the Free Software Foundation.

   MDJM Event Management is distributed in the hope that it will be useful,
   but WITHOUT ANY WARRANTY; without even the implied warranty of
   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
   GNU General Public License for more details.

   You should have received a copy of the GNU General Public License
   along with MDJM Event Management; if not, see https://www.gnu.org/licenses/gpl-2.0.html
 */
/**
 * Class: Mobile_DJ_Manager
 * Description: The main MDJM class
 *
 *
 */
 
if( !class_exists( 'Mobile_DJ_Manager' ) ) :
	class Mobile_DJ_Manager	{
		private static $instance;
		
		public $events;
		
		public $posts;
		
		public $cron;
		
		public $users;
		
		public $roles;
		
		public $permissions;
		
		public $menu;
		
		public $txns;
		
		public $content_tags;
		/**
		 * Run during plugin activation. Check for existance of version key and execute install procedures
		 * if it does not exist. Otherwise simply return.
		 * We write everything to error_log and specify the MDJM Debug file
		 * to capture all errors in case of support being needed
		 *
		 *
		 */
		public static function mdjm_activate()	{
			if( !get_option( 'mdjm_version' ) )	{
				error_log( '** THE MDJM INSTALLATION PROCEDURE IS STARTING **' . "\r\n", 3, MDJM_DEBUG_LOG );
			
				include( MDJM_PLUGIN_DIR . '/includes/admin/procedures/mdjm-install.php' );
										
				error_log( '** THE MDJM INSTALLATION PROCEDURE COMPLETED **' . "\r\n", 3, MDJM_DEBUG_LOG );
			}
			wp_schedule_event( time(), 'hourly', 'mdjm_hourly_schedule' );
		} // mdjm_activate
		
		/**
		 * Run during plugin deactivation.
		 * 
		 * 
		 * 
		 *
		 *
		 */
		public static function mdjm_deactivate()	{
			wp_clear_scheduled_hook( 'mdjm_hourly_schedule' );
		} // mdjm_activate
		
		/**
		 * Execute actions during 'plugins_loaded' hook
		 *
		 *
		 *
		 */
		public static function mdjm_plugins_loaded()	{
			// Load the text domain for translations
			load_plugin_textdomain( 
				'mobile-dj-manager',
				false, 
				dirname( plugin_basename(__FILE__) ) . '/lang/'
			);
		} // mdjm_plugins_loaded
		
		/**
		 * Let's ensure we only have one instance of MDJM loaded into memory at any time
		 *
		 *
		 *
		 * @return The one true Mobile_DJ_Manager
		 */
		public static function instance()	{
			global $mdjm, $mdjm_debug, $mdjm_posts, $clientzone;
			
			if( !isset( self::$instance ) && !( self::$instance instanceof Mobile_DJ_Manager ) ) {
				self::$instance = new Mobile_DJ_Manager;
				
				self::$instance->setup_constants();
				
				add_action( 'plugins_loaded', array( __CLASS__, 'mdjm_plugins_loaded' ) );
				
				add_action( 'wp_dashboard_setup', 'f_mdjm_add_wp_dashboard_widgets' );	
				
				self::$instance->includes();
				$mdjm			  				  = new MDJM();
				self::$instance->debug			 = new MDJM_Debug();
				$mdjm_debug						= self::$instance->debug; // REMOVE POST 1.3
				self::$instance->events			= new MDJM_Events();
				$mdjm_posts						= new MDJM_Posts(); // REMOVE POST 1.3
				self::$instance->posts			 = new MDJM_Post_Types();
				self::$instance->content_tags	  = new MDJM_Content_Tags();
				self::$instance->cron			  = new MDJM_Cron();
				self::$instance->users			 = new MDJM_Users();
				self::$instance->roles			 = new MDJM_Roles();
				self::$instance->permissions	   = new MDJM_Permissions();
				self::$instance->menu			  = new MDJM_Menu();
				self::$instance->txns			  = new MDJM_Transactions();
				
				// If we're on the front end, load the ClienZone class
				if( class_exists( 'ClientZone' ) )
					$clientzone = new ClientZone();
			}
			
			return self::$instance;
		} // instance
		
		/**
		 * Throw error on object clone
		 *
		 * The whole idea of the singleton design pattern is that there is a single
		 * object therefore, we don't want the object to be cloned.
		 *
		 * @since 1.3
		 * @access protected
		 * @return void
		 */
		public function __clone() {
			// Cloning instances of the class is forbidden
			_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?', 'mobile-dj-manager' ), '1.3' );
		}
		
		/**
		 * Setup plugin constants
		 *
		 * @access	private
		 * @since	1.3
		 * @return	void
		 */
		private function setup_constants()	{
			global $wpdb;
			define( 'MDJM_VERSION_NUM', '1.2.7.5' );
			define( 'MDJM_VERSION_KEY', 'mdjm_version');
			define( 'MDJM_PLUGIN_DIR', untrailingslashit( dirname( __FILE__ ) ) );
			define( 'MDJM_PLUGIN_URL', untrailingslashit( plugins_url( '', __FILE__ ) ) );
			define( 'MDJM_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );
			define( 'MDJM_NAME', 'MDJM Event Management' );
			
			define( 'MDJM_CLIENTZONE', MDJM_PLUGIN_DIR . '/client-zone' );
			
			define( 'MDJM_DB_VERSION', get_option( 'mdjm_db_version' ) );
			
			define( 'MDJM_API_SETTINGS_KEY', 'mdjm_api_data' );
			
			// Tables
			define( 'MDJM_PLAYLIST_TABLE', $wpdb->prefix . 'mdjm_playlists' );
			define( 'MDJM_HOLIDAY_TABLE', $wpdb->prefix . 'mdjm_avail' );
			
		} // mdjm_constants
				
		/**
		 * Include required files
		 *
		 * @access	private
		 * @since	1.3
		 * @return	void
		 */
		private function includes()	{
			global $mdjm_options;

			require_once( MDJM_PLUGIN_DIR . '/includes/admin/settings/register-settings.php' );
			$mdjm_options = mdjm_get_settings();
			
			require_once( MDJM_PLUGIN_DIR . '/includes/actions.php' );
			
			require_once( MDJM_PLUGIN_DIR . '/includes/ajax-functions.php' );
			require_once( MDJM_PLUGIN_DIR . '/includes/admin/mdjm.php' );
			require_once( MDJM_PLUGIN_DIR . '/includes/template-functions.php' );
			require_once( MDJM_PLUGIN_DIR . '/includes/events/class-events.php' );
			require_once( MDJM_PLUGIN_DIR . '/includes/event-functions.php' );
			require_once( MDJM_PLUGIN_DIR . '/includes/playlist-functions.php' );
			require_once( MDJM_PLUGIN_DIR . '/includes/venue-functions.php' );
			require_once( MDJM_PLUGIN_DIR . '/includes/equipment/equipment-functions.php' );
			require_once( MDJM_PLUGIN_DIR . '/includes/misc-functions.php' );
			require_once( MDJM_PLUGIN_DIR . '/includes/admin/posts/mdjm-posts.php' );
			require_once( MDJM_PLUGIN_DIR . '/includes/admin/posts/mdjm-post-types.php' );
			require_once( MDJM_PLUGIN_DIR . '/includes/admin/pages/mdjm-custom-fields.php' );
			require_once( MDJM_PLUGIN_DIR . '/includes/admin/users/class-mdjm-users.php' );
			require_once( MDJM_PLUGIN_DIR . '/includes/client-functions.php' );
			require_once( MDJM_PLUGIN_DIR . '/includes/employee-functions.php' );
			require_once( MDJM_PLUGIN_DIR . '/includes/admin/roles/class-mdjm-roles.php' );
			require_once( MDJM_PLUGIN_DIR . '/includes/admin/roles/roles-functions.php' );
			require_once( MDJM_PLUGIN_DIR . '/includes/admin/permissions/mdjm-permissions.php' );
			require_once( MDJM_PLUGIN_DIR . '/includes/admin/settings/display-settings.php' );
			require_once( MDJM_PLUGIN_DIR . '/includes/admin/mdjm-menu.php' );
			require_once( MDJM_PLUGIN_DIR . '/includes/content/content-tags.php' );
			require_once( MDJM_PLUGIN_DIR . '/includes/mdjm-functions.php' ); // Call the main functions file
			require_once( MDJM_PLUGIN_DIR . '/includes/functions.php' ); // THIS CAN BE DEPRECATED SOON
			require_once( MDJM_PLUGIN_DIR . '/includes/html-functions.php' );
			require_once( MDJM_PLUGIN_DIR . '/includes/clientzone-functions.php' );
			require_once( MDJM_PLUGIN_DIR . '/includes/admin/mdjm-cron.php' ); // Scheduler
			require_once( MDJM_CLIENTZONE . '/includes/mdjm-dynamic.php' ); // Dynamic Ajax functions
			require_once( MDJM_PLUGIN_DIR . '/widgets/class-mdjm-widget.php' ); // Widgets
			require_once( MDJM_PLUGIN_DIR . '/includes/admin/debug/mdjm-debug.php' ); // Debug class
			require_once( MDJM_PLUGIN_DIR . '/includes/admin/transactions/mdjm-transactions.php' ); // Transaction class
			require_once( MDJM_PLUGIN_DIR . '/includes/shortcodes.php' ); // Shortcodes
			
			if( is_admin() )	{ // Required for admin only
				require_once( MDJM_PLUGIN_DIR . '/includes/admin/core.php' ); // Plugin settings
				require_once( MDJM_PLUGIN_DIR . '/includes/admin/events/metaboxes.php' );
				require_once( MDJM_PLUGIN_DIR . '/includes/admin/equipment/metaboxes.php' );
				require_once( MDJM_PLUGIN_DIR . '/includes/admin/widgets.php' ); // WP Dashboard Widgets
				require_once( MDJM_PLUGIN_DIR . '/includes/admin/mdjm-functions-admin.php' ); // Admin only functions
				require_once( MDJM_PLUGIN_DIR . '/includes/admin/formatting/mdjm-formatting.php' );
			}
			else	{ // Required for front end only
				require_once( MDJM_CLIENTZONE . '/pages/mdjm-clientzone.php' );
			}
		} // mdjm_includes
	} //class  Mobile_DJ_Manager
	
endif;

	function MDJM()	{
		return Mobile_DJ_Manager::instance();
	}

	register_activation_hook( __FILE__, array( 'Mobile_DJ_Manager', 'mdjm_activate' ) );
	register_deactivation_hook( __FILE__, array( 'Mobile_DJ_Manager', 'mdjm_deactivate' ) );

	MDJM();