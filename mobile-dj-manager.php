<?php
	defined( 'ABSPATH' ) or die( "Direct access to this page is disabled!!!" );
/**
 * Plugin Name: Mobile DJ Manager
 * Description: Mobile DJ Manager is an interface allowing mobile DJ's and businesses to manage their events and employees as well as interact with their clients easily. Automating many of your day to day tasks, Mobile DJ Manager for WordPress is the ultimate tool for any Mobile DJ Business.
 * Version: 1.2.5.2
 * Date: 22 October 2015
 * Author: My DJ Planner <contact@mydjplanner.co.uk>
 * Author URI: http://www.mydjplanner.co.uk
 * Text Domain: mobile-dj-manager
*/

/*  Copyright 2014  Mobile DJ Manager  (email : contact@mydjplanner.co.uk)
    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as 
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

	You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/
/**
 * Class: Mobile_DJ_Manager
 * Description: The main MDJM class
 *
 *
 */
 
if ( ! class_exists( 'Mobile_DJ_Manager' ) ) :

	class Mobile_DJ_Manager	{
		
		private static $instance;
		
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
			
				include( MDJM_PLUGIN_DIR . '/admin/includes/procedures/mdjm-install.php' );
										
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
		public function mdjm_plugins_loaded()	{
			// Load the text domain for translations
			load_plugin_textdomain( 
				'mobile-dj-manager',
				false, 
				dirname( plugin_basename(__FILE__) ) . '/lang/' );
		} // mdjm_plugins_loaded
		
		/**
		 * Let's ensure we only have one instance of MDJM loaded into memory at any time
		 *
		 *
		 *
		 *
		 */
		public static function instance()	{
			global $mdjm, $mdjm_posts, $clientzone;
			
			self::$instance = new Mobile_DJ_Manager();
			
			self::$instance->mdjm_constants();
			self::$instance->mdjm_includes();
			
			add_action( 'plugins_loaded', array( __CLASS__, 'mdjm_plugins_loaded' ) );
			
			add_action( 'wp_dashboard_setup', 'f_mdjm_add_wp_dashboard_widgets' );	
			
			$mdjm			  			= new MDJM();
			$mdjm_posts				  = new MDJM_Posts();
			self::$instance->cron		= new MDJM_Cron();
			self::$instance->menu		= new MDJM_Menu();
			
			// If we're on the front end, load the ClienZone class
			if( class_exists( 'ClientZone' ) )
				$clientzone = new ClientZone();
		} // instance
		
		/**
		 * Define constants
		 *
		 *
		 *
		 */
		public function mdjm_constants()	{
			global $wpdb;
			define( 'MDJM_VERSION_NUM', '1.2.5.1' );
			define( 'MDJM_VERSION_KEY', 'mdjm_version');
			define( 'MDJM_PLUGIN_DIR', untrailingslashit( dirname( __FILE__ ) ) );
			define( 'MDJM_PLUGIN_URL', untrailingslashit( plugins_url( '', __FILE__ ) ) );
			define( 'MDJM_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );
			define( 'MDJM_NAME', 'MDJM Event Management' );
			define( 'MDJM_REQUIRED_WP_VERSION', '3.9' );
			
			define( 'MDJM_PAGES_DIR', MDJM_PLUGIN_DIR . '/admin/pages' );
			define( 'MDJM_PROCEDURES_DIR', MDJM_PLUGIN_DIR . '/admin/includes/procedures' );
			define( 'MDJM_FUNCTIONS', MDJM_PLUGIN_DIR . '/includes/mdjm-functions.php' );
			define( 'MDJM_CLIENTZONE', MDJM_PLUGIN_DIR . '/client-zone' );
			
			// Option Keys
			define( 'MDJM_SETTINGS_KEY', 'mdjm_plugin_settings' );
			define( 'MDJM_EMAIL_SETTINGS_KEY', 'mdjm_email_settings' );
			define( 'MDJM_TEMPLATES_SETTINGS_KEY', 'mdjm_templates_settings' );
			define( 'MDJM_EVENT_SETTINGS_KEY', 'mdjm_event_settings' );
			define( 'MDJM_PLAYLIST_SETTINGS_KEY', 'mdjm_playlist_settings' );
			define( 'MDJM_CLIENTZONE_SETTINGS_KEY', 'mdjm_clientzone_settings' );
			define( 'MDJM_CLIENT_FIELDS', 'mdjm_client_fields' );
			define( 'MDJM_CUSTOM_TEXT_KEY', 'mdjm_frontend_text' );
			define( 'MDJM_PAGES_KEY', 'mdjm_plugin_pages' );
			define( 'MDJM_PAYMENTS_KEY', 'mdjm_payment_settings' );
			define( 'MDJM_PERMISSIONS_KEY', 'mdjm_plugin_permissions' );
			define( 'MDJM_AVAILABILITY_SETTINGS_KEY', 'mdjm_availability_settings' );
			define( 'MDJM_SCHEDULES_KEY', 'mdjm_schedules' );
			define( 'MDJM_UPDATED_KEY', 'mdjm_updated' );
			define( 'MDJM_API_SETTINGS_KEY', 'mdjm_api_data' );
			define( 'MDJM_DEBUG_SETTINGS_KEY', 'mdjm_debug_settings' );
			define( 'MDJM_DB_VERSION_KEY', 'mdjm_db_version' );
			define( 'MDJM_DB_VERSION', get_option( MDJM_DB_VERSION_KEY ) );
			define( 'MDJM_UNINST_SETTINGS_KEY', 'mdjm_uninst' );
			
			// Tables
			define( 'MDJM_PLAYLIST_TABLE', $wpdb->prefix . 'mdjm_playlists' );
			define( 'MDJM_HOLIDAY_TABLE', $wpdb->prefix . 'mdjm_avail' );
			
			/** 
			 * Deprecated tables since 1.2. Kept here to support updates from older versions.
			 * Remove these at version 1.3.
			 *
			 */
			define( 'MDJM_EVENTS_TABLE', $wpdb->prefix . 'mdjm_events' ); // Deprecated since 1.2
			define( 'MDJM_TRANSACTION_TABLE', $wpdb->prefix . 'mdjm_trans' ); // Deprecated since 1.2
			define( 'MDJM_JOURNAL_TABLE', $wpdb->prefix . 'mdjm_journal' ); // Deprecated since 1.2
		} // mdjm_constants
				
		/**
		 * Controls which files to include
		 *
		 *
		 *
		 */
		public function mdjm_includes()	{
			require_once( MDJM_PLUGIN_DIR . '/admin/includes/class/class-mdjm.php' );
			
			require_once( MDJM_PLUGIN_DIR . '/admin/includes/class/class-mdjm-posts.php' );
			
			require_once( MDJM_PLUGIN_DIR . '/admin/includes/class/class-mdjm-menu.php' );
			
			require_once( MDJM_FUNCTIONS ); // Call the main functions file
			
			require_once( MDJM_PLUGIN_DIR . '/includes/functions.php' ); // THIS CAN BE DEPRECATED SOON
			
			require_once( MDJM_PLUGIN_DIR . '/admin/includes/class/class-mdjm-cron.php' ); // Scheduler
			
			require_once( MDJM_CLIENTZONE . '/includes/mdjm-dynamic.php' ); // Dynamic Ajax functions
			
			require_once( MDJM_PLUGIN_DIR . '/widgets/class-mdjm-widget.php' ); // Widgets
			
			require_once( MDJM_PLUGIN_DIR . '/admin/includes/class/class-mdjm-debug.php' ); // Debug class
			
			if( is_admin() )	{ // Required for admin only
				require_once( MDJM_PLUGIN_DIR . '/admin/includes/core.php' ); // Plugin settings
				require_once( MDJM_PLUGIN_DIR . '/admin/includes/process-ajax.php' ); // Ajax functions backend
				require_once( MDJM_PLUGIN_DIR . '/admin/includes/widgets.php' ); // WP Dashboard Widgets
			}
			else // Required for front end only
				require_once( MDJM_CLIENTZONE . '/class/class-clientzone.php' );
		} // mdjm_includes
	} //class  Mobile_DJ_Manager
	
endif;

	function MDJM()	{
		return Mobile_DJ_Manager::instance();
	}
	
	register_activation_hook( __FILE__, array( 'Mobile_DJ_Manager', 'mdjm_activate' ) );
	register_deactivation_hook( __FILE__, array( 'Mobile_DJ_Manager', 'mdjm_deactivate' ) );
	
	MDJM();