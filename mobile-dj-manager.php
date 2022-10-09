<?php

/**
 * Plugin Name: MDJM Event Management
 * Plugin URI: https://www.mobileeventsmanager.co.uk
 * Description: The flexible event management solution for WordPress - not just for DJs!
 * Version: 1.6.2
 * Date: Octboer 2022
 * Author: Mobile Events Manager <support@mobileeventsmanager.co.uk>
 * Author URI:  https://mobileeventsmanager.co.uk
 * Text Domain: mobile-dj-manager
 * Domain Path: /languages
 * License: GPL2
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Tags: Event Management, Event Planning, Event Planner, Events, DJ Event Planner, Mobile DJ
 *
 * Mobile DJ Manager is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License, version 2, as
 * published by the Free Software Foundation.
 *
 * Mobile DJ Manager is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Mobile DJ Manager; if not, see https://www.gnu.org/licenses/gpl-2.0.html
 *
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'Mobile_DJ_Manager' ) ) :
	/**
	 * Class: Mobile_DJ_Manager
	 * Description: The main MDJM class
	 */
	class Mobile_DJ_Manager {
		private static $instance;

		public $api;

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

		public $availability_db;

		public $availability_meta_db;

		/**
		 * Ensure we only have one instance of MDJM loaded into memory at any time.
		 *
		 * @since 1.3
		 * @param
		 */
		public static function instance() {
			global $mdjm, $mdjm_debug, $clientzone, $wp_version;

			if ( ! isset( self::$instance ) && ! ( self::$instance instanceof Mobile_DJ_Manager ) ) {
				self::$instance = new Mobile_DJ_Manager();

				self::$instance->setup_constants();

				add_action( 'plugins_loaded', array( __CLASS__, 'load_textdomain' ) );

				self::$instance->includes();

				$mdjm                   = new MDJM();
				self::$instance->debug  = new MDJM_Debug();
				$mdjm_debug             = self::$instance->debug; // REMOVE POST 1.3.
				self::$instance->events = new MDJM_Events();

				if ( version_compare( (float) $wp_version, '4.4', '>=' ) ) {
					self::$instance->api = new MDJM_API();
				}

				self::$instance->availability_db      = new MDJM_DB_Availability();
				self::$instance->availability_meta_db = new MDJM_DB_Availability_Meta();
				self::$instance->playlist_db          = new MDJM_DB_Playlists();
				self::$instance->playlist_meta_db     = new MDJM_DB_Playlist_Meta();

				self::$instance->content_tags = new MDJM_Content_Tags();
				self::$instance->cron         = new MDJM_Cron();
				self::$instance->emails       = new MDJM_Emails();
				self::$instance->html         = new MDJM_HTML_Elements();
				self::$instance->users        = new MDJM_Users();
				self::$instance->roles        = new MDJM_Roles();
				self::$instance->permissions  = new MDJM_Permissions();
				self::$instance->txns         = new MDJM_Transactions();

				// If we're on the front end, load the ClienZone class.
				if ( class_exists( 'ClientZone' ) ) {
					$clientzone = new ClientZone();
				}
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
			// Cloning instances of the class is forbidden.
			_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?', 'mobile-dj-manager' ), '1.3' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		}




		/**
		 * Setup plugin constants.
		 *
		 * @access private
		 * @since 1.3
		 * @return void
		 */
		private function setup_constants() {
			global $wpdb;
			define( 'MDJM_VERSION_NUM', '1.6.2' );
			define( 'MDJM_VERSION_KEY', 'mdjm_version' );
			define( 'MDJM_PLUGIN_DIR', untrailingslashit( dirname( __FILE__ ) ) );
			define( 'MDJM_PLUGIN_URL', untrailingslashit( plugins_url( '', __FILE__ ) ) );
			define( 'MDJM_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );
			define( 'MDJM_PLUGIN_FILE', __FILE__ );
			define( 'MDJM_NAME', 'Mobile DJ Manager' );

			define( 'MDJM_API_SETTINGS_KEY', 'mdjm_api_data' );

			// Tables
			define( 'MDJM_HOLIDAY_TABLE', $wpdb->prefix . 'mdjm_avail' );

		} // setup_constants

		/**
		 * Include required files.
		 *
		 * @access private
		 * @since 1.3
		 * @return void
		 */
		private function includes() {
			global $mdjm_options;

			require_once MDJM_PLUGIN_DIR . '/includes/admin/settings/register-settings.php';
			$mdjm_options = mdjm_get_settings();

			require_once MDJM_PLUGIN_DIR . '/includes/actions.php';
			require_once MDJM_PLUGIN_DIR . '/includes/ajax-functions.php';
			require_once MDJM_PLUGIN_DIR . '/includes/api/class-mdjm-api.php';
			require_once MDJM_PLUGIN_DIR . '/includes/class-mdjm-db.php';
			require_once MDJM_PLUGIN_DIR . '/includes/admin/mdjm.php';
			require_once MDJM_PLUGIN_DIR . '/includes/template-functions.php';
			require_once MDJM_PLUGIN_DIR . '/includes/class-mdjm-cache-helper.php';
			require_once MDJM_PLUGIN_DIR . '/includes/payments/actions.php';
			require_once MDJM_PLUGIN_DIR . '/includes/payments/payments.php';
			require_once MDJM_PLUGIN_DIR . '/includes/payments/process-payments.php';
			require_once MDJM_PLUGIN_DIR . '/includes/payments/template.php';
			require_once MDJM_PLUGIN_DIR . '/includes/events/class-mdjm-event.php';
			require_once MDJM_PLUGIN_DIR . '/includes/class-mdjm-html-elements.php';
			require_once MDJM_PLUGIN_DIR . '/includes/events/class-events.php';
			require_once MDJM_PLUGIN_DIR . '/includes/events/event-functions.php';
			require_once MDJM_PLUGIN_DIR . '/includes/events/event-actions.php';
			require_once MDJM_PLUGIN_DIR . '/includes/journal-functions.php';
			require_once MDJM_PLUGIN_DIR . '/includes/emails/class-mdjm-emails.php';
			require_once MDJM_PLUGIN_DIR . '/includes/emails/email-functions.php';
			require_once MDJM_PLUGIN_DIR . '/includes/availability/class-mdjm-db-availability.php';
			require_once MDJM_PLUGIN_DIR . '/includes/availability/class-mdjm-db-availability-meta.php';
			require_once MDJM_PLUGIN_DIR . '/includes/availability/class-mdjm-availability-checker.php';
			require_once MDJM_PLUGIN_DIR . '/includes/availability/availability-functions.php';
			require_once MDJM_PLUGIN_DIR . '/includes/availability/availability-actions.php';
			require_once MDJM_PLUGIN_DIR . '/includes/contract/contract-functions.php';
			require_once MDJM_PLUGIN_DIR . '/includes/contract/contract-actions.php';
			require_once MDJM_PLUGIN_DIR . '/includes/class-mdjm-travel.php';
			require_once MDJM_PLUGIN_DIR . '/includes/travel-functions.php';
			require_once MDJM_PLUGIN_DIR . '/includes/playlist/class-mdjm-db-playlists.php';
			require_once MDJM_PLUGIN_DIR . '/includes/playlist/class-mdjm-db-playlist-meta.php';
			require_once MDJM_PLUGIN_DIR . '/includes/playlist/playlist-functions.php';
			require_once MDJM_PLUGIN_DIR . '/includes/playlist/playlist-actions.php';
			require_once MDJM_PLUGIN_DIR . '/includes/venue-functions.php';
			require_once MDJM_PLUGIN_DIR . '/includes/txns/class-mdjm-txn.php';
			require_once MDJM_PLUGIN_DIR . '/includes/txns/txn-functions.php';
			require_once MDJM_PLUGIN_DIR . '/includes/txns/txn-actions.php';
			require_once MDJM_PLUGIN_DIR . '/includes/equipment/equipment-functions.php';
			require_once MDJM_PLUGIN_DIR . '/includes/misc-functions.php';
			require_once MDJM_PLUGIN_DIR . '/includes/privacy-functions.php';
			require_once MDJM_PLUGIN_DIR . '/includes/admin/pages/event-fields.php';
			require_once MDJM_PLUGIN_DIR . '/includes/admin/pages/client-fields.php';
			require_once MDJM_PLUGIN_DIR . '/includes/admin/users/class-mdjm-users.php';
			require_once MDJM_PLUGIN_DIR . '/includes/clients/client-functions.php';
			require_once MDJM_PLUGIN_DIR . '/includes/clients/class-mdjm-client.php';
			require_once MDJM_PLUGIN_DIR . '/includes/employee-functions.php';
			require_once MDJM_PLUGIN_DIR . '/includes/admin/roles/class-mdjm-roles.php';
			require_once MDJM_PLUGIN_DIR . '/includes/admin/roles/roles-functions.php';
			require_once MDJM_PLUGIN_DIR . '/includes/admin/roles/class-mdjm-permissions.php';
			require_once MDJM_PLUGIN_DIR . '/includes/admin/settings/display-settings.php';
			require_once MDJM_PLUGIN_DIR . '/includes/admin/menu.php';
			require_once MDJM_PLUGIN_DIR . '/includes/content/content-tags.php';
			require_once MDJM_PLUGIN_DIR . '/includes/mdjm-functions.php';
			require_once MDJM_PLUGIN_DIR . '/includes/clientzone-functions.php';
			require_once MDJM_PLUGIN_DIR . '/includes/login.php';
			require_once MDJM_PLUGIN_DIR . '/includes/class-mdjm-cron.php';
			require_once MDJM_PLUGIN_DIR . '/includes/scripts.php';
			require_once MDJM_PLUGIN_DIR . '/includes/post-types.php';
			require_once MDJM_PLUGIN_DIR . '/includes/formatting.php';
			require_once MDJM_PLUGIN_DIR . '/includes/widgets.php';
			require_once MDJM_PLUGIN_DIR . '/includes/class-mdjm-stats.php';
			require_once MDJM_PLUGIN_DIR . '/includes/events/class-mdjm-events-query.php';
			require_once MDJM_PLUGIN_DIR . '/includes/class-mdjm-debug.php';
			require_once MDJM_PLUGIN_DIR . '/includes/admin/transactions/mdjm-transactions.php';
			require_once MDJM_PLUGIN_DIR . '/includes/shortcodes.php';
			require_once MDJM_PLUGIN_DIR . '/includes/tasks/task-functions.php';
			require_once MDJM_PLUGIN_DIR . '/includes/plugin-compatibility.php';
			require_once MDJM_PLUGIN_DIR . '/includes/admin/contract/class-contract.php';

			// Require only if premium addons are installed.
			if ( $this->mdjm_prem_addons() ) {
				require_once MDJM_PLUGIN_DIR . '/includes/class-mdjm-license-handler.php';
			}

			if ( is_admin() ) {
				require_once MDJM_PLUGIN_DIR . '/includes/admin/admin-actions.php';
				require_once MDJM_PLUGIN_DIR . '/includes/admin/plugins.php';
				require_once MDJM_PLUGIN_DIR . '/includes/admin/communications/comms.php';
				require_once MDJM_PLUGIN_DIR . '/includes/admin/communications/comms-functions.php';
				require_once MDJM_PLUGIN_DIR . '/includes/admin/communications/contextual-help.php';
				require_once MDJM_PLUGIN_DIR . '/includes/admin/communications/metaboxes.php';
				require_once MDJM_PLUGIN_DIR . '/includes/admin/events/events.php';
				require_once MDJM_PLUGIN_DIR . '/includes/admin/events/quotes.php';
				require_once MDJM_PLUGIN_DIR . '/includes/admin/events/metaboxes.php';
				require_once MDJM_PLUGIN_DIR . '/includes/admin/events/taxonomies.php';
				require_once MDJM_PLUGIN_DIR . '/includes/admin/events/contextual-help.php';
				require_once MDJM_PLUGIN_DIR . '/includes/admin/events/playlist.php';
				require_once MDJM_PLUGIN_DIR . '/includes/admin/equipment/equipment.php';
				require_once MDJM_PLUGIN_DIR . '/includes/admin/equipment/metaboxes.php';
				require_once MDJM_PLUGIN_DIR . '/includes/admin/templates/contracts.php';
				require_once MDJM_PLUGIN_DIR . '/includes/admin/templates/emails.php';
				require_once MDJM_PLUGIN_DIR . '/includes/admin/templates/contextual-help.php';
				require_once MDJM_PLUGIN_DIR . '/includes/admin/templates/metaboxes.php';
				require_once MDJM_PLUGIN_DIR . '/includes/admin/tools.php';
				require_once MDJM_PLUGIN_DIR . '/includes/admin/transactions/txns.php';
				require_once MDJM_PLUGIN_DIR . '/includes/admin/transactions/metaboxes.php';
				require_once MDJM_PLUGIN_DIR . '/includes/admin/transactions/taxonomies.php';
				require_once MDJM_PLUGIN_DIR . '/includes/admin/venues/venues.php';
				require_once MDJM_PLUGIN_DIR . '/includes/admin/venues/metaboxes.php';
				require_once MDJM_PLUGIN_DIR . '/includes/admin/dashboard-widgets.php';
				require_once MDJM_PLUGIN_DIR . '/includes/admin/events/playlist-page.php';
				require_once MDJM_PLUGIN_DIR . '/includes/admin/events/event-actions.php';
				require_once MDJM_PLUGIN_DIR . '/includes/admin/users/employee-actions.php';
				require_once MDJM_PLUGIN_DIR . '/includes/admin/availability/availability-actions.php';
				require_once MDJM_PLUGIN_DIR . '/includes/admin/availability/availability-page.php';
				require_once MDJM_PLUGIN_DIR . '/includes/admin/tasks/task-actions.php';
				require_once MDJM_PLUGIN_DIR . '/includes/admin/tasks/tasks-page.php';
				require_once MDJM_PLUGIN_DIR . '/includes/admin/admin-notices.php';
				require_once MDJM_PLUGIN_DIR . '/includes/admin/settings/contextual-help.php';
				require_once MDJM_PLUGIN_DIR . '/includes/admin/reporting/export/export-functions.php';
				require_once MDJM_PLUGIN_DIR . '/includes/admin/reporting/reporting-functions.php';
				require_once MDJM_PLUGIN_DIR . '/includes/admin/reporting/class-mdjm-graph.php';
				require_once MDJM_PLUGIN_DIR . '/includes/admin/reporting/class-mdjm-pie-graph.php';
				require_once MDJM_PLUGIN_DIR . '/includes/admin/reporting/graphing-functions.php';
				require_once MDJM_PLUGIN_DIR . '/includes/admin/extensions.php';
				require_once MDJM_PLUGIN_DIR . '/includes/admin/upgrades/upgrade-functions.php';
				require_once MDJM_PLUGIN_DIR . '/includes/admin/upgrades/upgrades.php';
				require_once MDJM_PLUGIN_DIR . '/includes/admin/welcome.php';

			}

			require_once MDJM_PLUGIN_DIR . '/includes/install.php';

		} // includes

		/**
		 * Load the plugins text domain for translations.
		 *
		 * @since 1.3
		 * @param
		 * @return void
		 */
		public static function load_textdomain() {
			// Load the text domain for translations.
			load_plugin_textdomain(
				'mobile-dj-manager',
				false,
				dirname( plugin_basename( __FILE__ ) ) . '/languages'
			);
		} // load_textdomain

		/**
		 * Function to check for premium addons
		 *
		 * @since 1.0.4
		 */

		private function mdjm_prem_addons() {
			$addons = array(
				'mdjm-stripe-payments.php',
				'mdjm-google-calendar-sync.php',
				'mdjm-dynamic-contact-forms.php',
			);

			$get_active_plugins = (array) get_option( 'active_plugins', array() );

			if ( is_multisite() ) {
				$get_active_plugins = array_merge( $get_active_plugins, array_keys( get_site_option( 'active_sitewide_plugins', array() ) ) );
			}

			$plugin_filenames = array();

			foreach ( $get_active_plugins as $plugin ) {

				if ( false !== strpos( $plugin, '/' ) ) {
					// plugin name (dir/plugin-filename.php)
					list( , $plugin_filename ) = explode( '/', $plugin );

				} else {
					// no dir, just plugin file name
					$plugin_filename = esc_html( $plugin );
				}

				$plugin_filenames[] = $plugin_filename;
			}

			return ! empty( array_intersect( $plugin_filenames, $addons ) );
		}} // class Mobile_DJ_Manager

	endif;

	/** Mobile DJ Manager */
function MDJM() {
	return Mobile_DJ_Manager::instance();
}

	MDJM();
