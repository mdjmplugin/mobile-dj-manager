<?php
	defined( 'ABSPATH' ) or die( "Direct access to this page is disabled!!!" );
/**
 * Plugin Name: MDJM Event Management
 * Plugin URI: http://mdjm.co.uk
 * Description: MDJM Event Management is an interface to fully manage your DJ/Events or Agency business efficiently.
 * Version: 1.3.7.6
 * Date: 25 June 2015
 * Author: Mike Howard <mike@mdjm.co.uk>
 * Author URI: http://mdjm.co.uk
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
 
if( ! class_exists( 'Mobile_DJ_Manager' ) ) :
	class Mobile_DJ_Manager	{
		private static $instance;
		
		public $content_tags;
		
		public $cron;
		
		public $debug;
		
		public $emails;
		
		public $events;
		
		public $html;
				
		public $permissions;
		
		public $roles;
		
		public $txns;
		
		public $users;
		
		/**
		 * Ensure we only have one instance of MDJM loaded into memory at any time.
		 *
		 * @since	1.3
		 * @param
		 * @return	The one true Mobile_DJ_Manager
		 */
		public static function instance()	{
			global $mdjm, $mdjm_debug, $clientzone;
			
			if( !isset( self::$instance ) && !( self::$instance instanceof Mobile_DJ_Manager ) ) {
				self::$instance = new Mobile_DJ_Manager;
				
				self::$instance->setup_constants();

				add_action( 'init', array( __CLASS__, 'update' ) );
				add_action( 'plugins_loaded', array( __CLASS__, 'load_textdomain' ) );
				
				self::$instance->includes();
				$mdjm                           = new MDJM();
				self::$instance->debug          = new MDJM_Debug();
				$mdjm_debug                     = self::$instance->debug; // REMOVE POST 1.3
				self::$instance->events         = new MDJM_Events();
				self::$instance->content_tags   = new MDJM_Content_Tags();
				self::$instance->cron           = new MDJM_Cron();
				self::$instance->emails         = new MDJM_Emails();
				self::$instance->html           = new MDJM_HTML_Elements();
				self::$instance->users          = new MDJM_Users();
				self::$instance->roles          = new MDJM_Roles();
				self::$instance->permissions    = new MDJM_Permissions();
				self::$instance->txns           = new MDJM_Transactions();
				
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
		 * Setup plugin constants.
		 *
		 * @access	private
		 * @since	1.3
		 * @return	void
		 */
		private function setup_constants()	{
			global $wpdb;
			define( 'MDJM_VERSION_NUM', '1.3.7.6' );
			define( 'MDJM_VERSION_KEY', 'mdjm_version');
			define( 'MDJM_PLUGIN_DIR', untrailingslashit( dirname( __FILE__ ) ) );
			define( 'MDJM_PLUGIN_URL', untrailingslashit( plugins_url( '', __FILE__ ) ) );
			define( 'MDJM_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );
			define( 'MDJM_PLUGIN_FILE', __FILE__ );
			define( 'MDJM_NAME', 'MDJM Event Management' );
			
			define( 'MDJM_CLIENTZONE', MDJM_PLUGIN_DIR . '/client-zone' );
			define( 'MDJM_CLIENT_FIELDS', 'mdjm_client_fields' );
			
			define( 'MDJM_DB_VERSION', get_option( 'mdjm_db_version' ) );
			
			define( 'MDJM_API_SETTINGS_KEY', 'mdjm_api_data' );
			
			// Tables
			define( 'MDJM_PLAYLIST_TABLE', $wpdb->prefix . 'mdjm_playlists' );
			define( 'MDJM_HOLIDAY_TABLE', $wpdb->prefix . 'mdjm_avail' );
			
		} // setup_constants
				
		/**
		 * Include required files.
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
			
			if( file_exists( MDJM_PLUGIN_DIR . '/includes/deprecated-functions.php' ) )	{
				require_once( MDJM_PLUGIN_DIR . '/includes/deprecated-functions.php' );
			}
			
			require_once( MDJM_PLUGIN_DIR . '/includes/ajax-functions.php' );
			require_once( MDJM_PLUGIN_DIR . '/includes/admin/mdjm.php' );
			require_once( MDJM_PLUGIN_DIR . '/includes/class-mdjm-license-handler.php' );
			require_once( MDJM_PLUGIN_DIR . '/includes/template-functions.php' );
			require_once( MDJM_PLUGIN_DIR . '/includes/events/class-mdjm-event.php' );
			require_once( MDJM_PLUGIN_DIR . '/includes/class-mdjm-html-elements.php' );
			require_once( MDJM_PLUGIN_DIR . '/includes/events/class-events.php' );
			require_once( MDJM_PLUGIN_DIR . '/includes/events/event-functions.php' );
			require_once( MDJM_PLUGIN_DIR . '/includes/events/event-actions.php' );
			require_once( MDJM_PLUGIN_DIR . '/includes/journal-functions.php' );
			require_once( MDJM_PLUGIN_DIR . '/includes/emails/class-mdjm-emails.php' );
			require_once( MDJM_PLUGIN_DIR . '/includes/emails/email-functions.php' );
			require_once( MDJM_PLUGIN_DIR . '/includes/availability/class-mdjm-availability-checker.php' );
			require_once( MDJM_PLUGIN_DIR . '/includes/availability/availability-functions.php' );
			require_once( MDJM_PLUGIN_DIR . '/includes/availability/availability-actions.php' );
			require_once( MDJM_PLUGIN_DIR . '/includes/contract/contract-functions.php' );
			require_once( MDJM_PLUGIN_DIR . '/includes/contract/contract-actions.php' );
			require_once( MDJM_PLUGIN_DIR . '/includes/travel-functions.php' );
			require_once( MDJM_PLUGIN_DIR . '/includes/playlist/playlist-functions.php' );
			require_once( MDJM_PLUGIN_DIR . '/includes/playlist/playlist-actions.php' );
			require_once( MDJM_PLUGIN_DIR . '/includes/venue-functions.php' );
			require_once( MDJM_PLUGIN_DIR . '/includes/txns/class-mdjm-txn.php' );
			require_once( MDJM_PLUGIN_DIR . '/includes/txns/txn-functions.php' );
			require_once( MDJM_PLUGIN_DIR . '/includes/txns/txn-actions.php' );
			require_once( MDJM_PLUGIN_DIR . '/includes/equipment/equipment-functions.php' );
			require_once( MDJM_PLUGIN_DIR . '/includes/misc-functions.php' );
			require_once( MDJM_PLUGIN_DIR . '/includes/admin/pages/event-fields.php' );
			require_once( MDJM_PLUGIN_DIR . '/includes/admin/pages/client-fields.php' );
			require_once( MDJM_PLUGIN_DIR . '/includes/admin/users/class-mdjm-users.php' );
			require_once( MDJM_PLUGIN_DIR . '/includes/client-functions.php' );
			require_once( MDJM_PLUGIN_DIR . '/includes/employee-functions.php' );
			require_once( MDJM_PLUGIN_DIR . '/includes/admin/roles/class-mdjm-roles.php' );
			require_once( MDJM_PLUGIN_DIR . '/includes/admin/roles/roles-functions.php' );
			require_once( MDJM_PLUGIN_DIR . '/includes/admin/roles/class-mdjm-permissions.php' );
			require_once( MDJM_PLUGIN_DIR . '/includes/admin/settings/display-settings.php' );
			require_once( MDJM_PLUGIN_DIR . '/includes/admin/menu.php' );
			require_once( MDJM_PLUGIN_DIR . '/includes/content/content-tags.php' );
			require_once( MDJM_PLUGIN_DIR . '/includes/mdjm-functions.php' );
			require_once( MDJM_PLUGIN_DIR . '/includes/functions.php' ); // THIS CAN BE DEPRECATED SOON
			require_once( MDJM_PLUGIN_DIR . '/includes/clientzone-functions.php' );
			require_once( MDJM_PLUGIN_DIR . '/includes/login.php' );
			require_once( MDJM_PLUGIN_DIR . '/includes/class-mdjm-cron.php' );
			require_once( MDJM_PLUGIN_DIR . '/includes/scripts.php' );
			require_once( MDJM_PLUGIN_DIR . '/includes/post-types.php' );
			require_once( MDJM_PLUGIN_DIR . '/includes/formatting.php' );
			require_once( MDJM_CLIENTZONE . '/includes/mdjm-dynamic.php' );
			require_once( MDJM_PLUGIN_DIR . '/includes/widgets.php' );
			require_once( MDJM_PLUGIN_DIR . '/includes/class-mdjm-stats.php' );
			require_once( MDJM_PLUGIN_DIR . '/includes/class-mdjm-debug.php' );
			require_once( MDJM_PLUGIN_DIR . '/includes/admin/transactions/mdjm-transactions.php' );
			require_once( MDJM_PLUGIN_DIR . '/includes/shortcodes.php' );
			
			if( is_admin() )	{
				require_once( MDJM_PLUGIN_DIR . '/includes/admin/admin-actions.php' );
				require_once( MDJM_PLUGIN_DIR . '/includes/admin/plugins.php' );
				require_once( MDJM_PLUGIN_DIR . '/includes/admin/communications/comms.php' );
				require_once( MDJM_PLUGIN_DIR . '/includes/admin/communications/comms-functions.php' );
				require_once( MDJM_PLUGIN_DIR . '/includes/admin/communications/contextual-help.php' );
				require_once( MDJM_PLUGIN_DIR . '/includes/admin/communications/metaboxes.php' );
				require_once( MDJM_PLUGIN_DIR . '/includes/admin/events/events.php' );
				require_once( MDJM_PLUGIN_DIR . '/includes/admin/events/quotes.php' );
				require_once( MDJM_PLUGIN_DIR . '/includes/admin/events/metaboxes.php' );
				require_once( MDJM_PLUGIN_DIR . '/includes/admin/events/taxonomies.php' );
				require_once( MDJM_PLUGIN_DIR . '/includes/admin/events/contextual-help.php' );
				require_once( MDJM_PLUGIN_DIR . '/includes/admin/equipment/metaboxes.php' );
				require_once( MDJM_PLUGIN_DIR . '/includes/admin/equipment/packages.php' );
				require_once( MDJM_PLUGIN_DIR . '/includes/admin/templates/contracts.php' );
				require_once( MDJM_PLUGIN_DIR . '/includes/admin/templates/emails.php' );
				require_once( MDJM_PLUGIN_DIR . '/includes/admin/templates/contextual-help.php' );
				require_once( MDJM_PLUGIN_DIR . '/includes/admin/templates/metaboxes.php' );
				require_once( MDJM_PLUGIN_DIR . '/includes/admin/transactions/txns.php' );
				require_once( MDJM_PLUGIN_DIR . '/includes/admin/transactions/metaboxes.php' );
				require_once( MDJM_PLUGIN_DIR . '/includes/admin/transactions/taxonomies.php' );
				require_once( MDJM_PLUGIN_DIR . '/includes/admin/venues/venues.php' );
				require_once( MDJM_PLUGIN_DIR . '/includes/admin/venues/metaboxes.php' );
				require_once( MDJM_PLUGIN_DIR . '/includes/admin/dashboard-widgets.php' );
				require_once( MDJM_PLUGIN_DIR . '/includes/admin/events/playlist-page.php' );
				require_once( MDJM_PLUGIN_DIR . '/includes/admin/events/event-actions.php' );
				require_once( MDJM_PLUGIN_DIR . '/includes/admin/users/employee-actions.php' );
				require_once( MDJM_PLUGIN_DIR . '/includes/admin/admin-notices.php' );
				require_once( MDJM_PLUGIN_DIR . '/includes/admin/settings/contextual-help.php' );
				require_once( MDJM_PLUGIN_DIR . '/includes/admin/welcome.php' );
				
			}
			else	{ // Required for front end only
				require_once( MDJM_CLIENTZONE . '/pages/mdjm-clientzone.php' );
			}
			
			require_once( MDJM_PLUGIN_DIR . '/includes/install.php' );
			
		} // includes
		
		/**
		 * Load the plugins text domain for translations.
		 *
		 * @since	1.3
		 * @param
		 * @return	void
		 */
		public static function load_textdomain()	{
			// Load the text domain for translations
			load_plugin_textdomain( 
				'mobile-dj-manager',
				false, 
				dirname( plugin_basename(__FILE__) ) . '/lang/'
			);
		} // load_textdomain
		
		/*
		 * Determine if we need to run any plugin upgrade procedures
		 *
		 * @since	1.3
		 * @param
		 * @return	void
		 */
		public static function update()	{

			$current_version = get_option( MDJM_VERSION_KEY );
				
			if( $current_version < MDJM_VERSION_NUM )	{

				add_option( 'mdjm_update_me', MDJM_VERSION_NUM );
	
				if( $current_version < MDJM_VERSION_NUM )	{
	
					// Instantiate the update class which will execute the updates
					include_once( MDJM_PLUGIN_DIR . '/includes/admin/procedures/mdjm-upgrade.php' );
	
					// Update the stored version
					update_option( MDJM_VERSION_KEY, MDJM_VERSION_NUM );
	
					// Update the updated key so we know to redirect
					update_option( 'mdjm_updated', '1' );
	
				}
				
			}

		} // update
		
	}
	
endif;

	function MDJM()	{
		return Mobile_DJ_Manager::instance();
	}

	MDJM();