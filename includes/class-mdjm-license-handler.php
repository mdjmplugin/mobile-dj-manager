<?php
/**
 * License handler for MDJM Event Management
 *
 * This class should simplify the process of adding license information
 * to new MDJM extensions.
 *
 * @version	1.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) )
	exit;

if ( ! class_exists( 'MDJM_License' ) ) :

	/**
	 * MDJM_License Class
	 */
	class MDJM_License {
		private $file;
		private $license;
		private $item_name;
		private $item_id;
		private $item_shortname;
		private $version;
		private $author;
		private $api_url = 'http://mdjm.co.uk/edd-sl-api/';
	
		/**
		 * Class constructor
		 *
		 * @param string  $_file
		 * @param string  $_item_name
		 * @param string  $_version
		 * @param string  $_author
		 * @param string  $_optname
		 * @param string  $_api_url
		 */
		function __construct( $_file, $_item, $_version, $_author, $_optname = null, $_api_url = null ) {
	
			$this->file           = $_file;
	
			if( is_numeric( $_item ) ) {
				$this->item_id    = absint( $_item );
			} else {
				$this->item_name  = $_item;
			}
	
			$this->item_shortname = 'mdjm_' . preg_replace( '/[^a-zA-Z0-9_\s]/', '', str_replace( ' ', '_', strtolower( $this->item_name ) ) );
			$this->version        = $_version;
			$this->license        = trim( mdjm_get_option( $this->item_shortname . '_license_key', '' ) );
			$this->author         = $_author;
			$this->api_url        = is_null( $_api_url ) ? $this->api_url : $_api_url;
	
			/**
			 * Allows for backwards compatibility with old license options,
			 * i.e. if the plugins had license key fields previously, the license
			 * handler will automatically pick these up and use those in lieu of the
			 * user having to reactive their license.
			 */
			if ( ! empty( $_optname ) ) {
				$opt = mdjm_get_option( $_optname, false );
	
				if( isset( $opt ) && empty( $this->license ) ) {
					$this->license = trim( $opt );
				}
			}
	
			// Setup hooks
			$this->includes();
			$this->hooks();
			//$this->auto_updater();
		} // __construct
	
		/**
		 * Include the updater class
		 *
		 * @access	private
		 * @return	void
		 */
		private function includes() {
			if ( ! class_exists( 'MDJM_Plugin_Updater' ) )	{
				require_once 'MDJM_Plugin_Updater.php';
			}
		} // includes
	
		/**
		 * Setup hooks
		 *
		 * @access  private
		 * @return  void
		 */
		private function hooks() {
	
			// Register settings
			add_filter( 'mdjm_settings_licenses', array( $this, 'settings' ), 1 );
			
			// Display help text at the top of the Licenses tab
			add_action( 'mdjm_settings_tab_top', array( $this, 'license_help_text' ) );
	
			// Activate license key on settings save
			add_action( 'admin_init', array( $this, 'activate_license' ) );
	
			// Deactivate license key
			add_action( 'admin_init', array( $this, 'deactivate_license' ) );
			
			// Check that license is valid once per week
			add_action( 'mdjm_weekly_scheduled_events', array( $this, 'weekly_license_check' ) );
	
			// Updater
			add_action( 'admin_init', array( $this, 'auto_updater' ), 0 );
	
			add_action( 'admin_notices', array( $this, 'notices' ) );
			
			add_action( 'in_plugin_update_message-' . plugin_basename( $this->file ), array( $this, 'plugin_row_license_missing' ), 10, 2 );

		} // hooks
	
		/**
		 * Auto updater
		 *
		 * @access  private
		 * @return  void
		 */
		public function auto_updater() {
	
			$args = array(
				'version'   => $this->version,
				'license'   => $this->license,
				'author'    => $this->author
			);

			if( ! empty( $this->item_id ) ) {
				$args['item_id']   = $this->item_id;
			} else {
				$args['item_name'] = $this->item_name;
			}

			// Setup the updater
			$mdjm_updater = new MDJM_Plugin_Updater(
				$this->api_url,
				$this->file,
				$args
			);

		} // auto_updater
	
		/**
		 * Add license field to settings
		 *
		 * @access  public
		 * @param array   $settings
		 * @return  array
		 */
		public function settings( $settings ) {
			$mdjm_license_settings = array(
				array(
					'id'      => $this->item_shortname . '_license_key',
					'name'    => sprintf( __( '%1$s License Key', 'mobile-dj-manager' ), $this->item_name ),
					'desc'    => '',
					'type'    => 'license_key',
					'options' => array( 'is_valid_license_option' => $this->item_shortname . '_license_active' ),
					'size'    => 'regular'
				)
			);
	
			return array_merge( $settings, $mdjm_license_settings );
		} // settings
		
		/**
		 * Display help text at the top of the Licenses tag
		 *
		 * @access  public
		 * @since   1.3
		 * @param   string   $active_tab
		 * @return  void
		 */
		public function license_help_text( $active_tab = '' ) {
	
			static $has_ran;
	
			if( 'licenses' !== $active_tab ) {
				return;
			}
	
			if( ! empty( $has_ran ) ) {
				return;
			}
	
			echo '<p>' . sprintf(
				__( 'Enter your add-on license keys here to receive updates for purchased extensions. If your license key has expired, please <a href="%s" target="_blank" title="License renewal FAQ">renew your license</a>.', 'mobile-dj-manager' ),
				'http://mdjm.co.uk/docs/renewing-add-on-licenses/'
			) . '</p>';
	
			$has_ran = true;
	
		} // license_help_text
	
		/**
		 * Activate the license key
		 *
		 * @access  public
		 * @return  void
		 */
		public function activate_license() {
	
			if ( ! isset( $_POST['mdjm_settings'] ) ) {
				return;
			}
	
			if ( ! isset( $_POST['mdjm_settings'][ $this->item_shortname . '_license_key'] ) ) {
				return;
			}
	
			foreach( $_POST as $key => $value ) {
				if( false !== strpos( $key, 'license_key_deactivate' ) ) {
					// Don't activate a key when deactivating a different key
					return;
				}
			}
	
			if( ! wp_verify_nonce( $_REQUEST[ $this->item_shortname . '_license_key-nonce'], $this->item_shortname . '_license_key-nonce' ) ) {
				wp_die( __( 'Nonce verification failed', 'mobile-dj-manager' ), __( 'Error', 'mobile-dj-manager' ), array( 'response' => 403 ) );
	
			}
	
			if( ! mdjm_is_admin() ) {
				return;
			}
	
			if ( 'valid' === get_option( $this->item_shortname . '_license_active' ) ) {
				return;
			}
	
			$license = sanitize_text_field( $_POST['mdjm_settings'][ $this->item_shortname . '_license_key'] );
	
			if( empty( $license ) ) {
				return;
			}
	
			// Data to send to the API
			$api_params = array(
				'edd_action' => 'activate_license',
				'license'    => $license,
				'item_name'  => urlencode( $this->item_name ),
				'url'        => home_url()
			);
	
			// Call the API
			$response = wp_remote_post(
				$this->api_url,
				array(
					'timeout'   => 15,
					'sslverify' => false,
					'body'      => $api_params
				)
			);
	
			// Make sure there are no errors
			if ( is_wp_error( $response ) ) {
				return;
			}
	
			// Tell WordPress to look for updates
			set_site_transient( 'update_plugins', null );
	
			// Decode license data
			$license_data = json_decode( wp_remote_retrieve_body( $response ) );
	
			update_option( $this->item_shortname . '_license_active', $license_data );
	
			if( ! (bool) $license_data->success ) {
				set_transient( 'mdjm_license_error', $license_data, 1000 );
			} else {
				delete_transient( 'mdjm_license_error' );
			}
		} // activate_license
	
		/**
		 * Deactivate the license key
		 *
		 * @access  public
		 * @return  void
		 */
		public function deactivate_license() {
	
			if ( ! isset( $_POST['mdjm_settings'] ) )
				return;
	
			if ( ! isset( $_POST['mdjm_settings'][ $this->item_shortname . '_license_key'] ) )
				return;
	
			if( ! wp_verify_nonce( $_REQUEST[ $this->item_shortname . '_license_key-nonce'], $this->item_shortname . '_license_key-nonce' ) ) {
	
				wp_die( __( 'Nonce verification failed', 'mobile-dj-manager' ), __( 'Error', 'mobile-dj-manager' ), array( 'response' => 403 ) );
	
			}
	
			if( ! mdjm_is_admin() ) {
				return;
			}
	
			// Run on deactivate button press
			if ( isset( $_POST[ $this->item_shortname . '_license_key_deactivate'] ) ) {
	
				// Data to send to the API
				$api_params = array(
					'edd_action' => 'deactivate_license',
					'license'    => $this->license,
					'item_name'  => urlencode( $this->item_name ),
					'url'        => home_url()
				);
	
				// Call the API
				$response = wp_remote_post(
					$this->api_url,
					array(
						'timeout'   => 15,
						'sslverify' => false,
						'body'      => $api_params
					)
				);
	
				// Make sure there are no errors
				if ( is_wp_error( $response ) ) {
					return;
				}
	
				// Decode the license data
				$license_data = json_decode( wp_remote_retrieve_body( $response ) );
	
				delete_option( $this->item_shortname . '_license_active' );
	
				if( ! (bool) $license_data->success ) {
					set_transient( 'mdjm_license_error', $license_data, 1000 );
				} else {
					delete_transient( 'mdjm_license_error' );
				}
			}
		} // deactivate_license
		
		/**
		 * Check if license key is valid once per week
		 *
		 * @access  public
		 * @since   2.5
		 * @return  void
		 */
		public function weekly_license_check() {
	
			if( ! empty( $_POST['mdjm_settings'] ) ) {
				return; // Don't fire when saving settings
			}
	
			if( empty( $this->license ) ) {
				return;
			}
	
			// Data to send in our API request
			$api_params = array(
				'edd_action' => 'check_license',
				'license' 	=> $this->license,
				'item_name'  => urlencode( $this->item_name ),
				'url'        => home_url()
			);
	
			// Call the API
			$response = wp_remote_post(
				$this->api_url,
				array(
					'timeout'   => 15,
					'sslverify' => false,
					'body'      => $api_params
				)
			);
	
			// Make sure the response came back okay
			if ( is_wp_error( $response ) ) {
				return false;
			}
	
			$license_data = json_decode( wp_remote_retrieve_body( $response ) );
	
			update_option( $this->item_shortname . '_license_active', $license_data );
	
		} // weekly_license_check
	
		/**
		 * Admin notices for errors
		 *
		 * @access  public
		 * @return  void
		 */
		public function notices() {
	
			static $showed_invalid_message;

			if( empty( $this->license ) ) {
				return;
			}
	
			if( ! mdjm_employee_can( 'manage_mdjm' ) ) {
				return;
			}
	
			$messages = array();
	
			$license = get_option( $this->item_shortname . '_license_active' );
	
			if( is_object( $license ) && 'valid' !== $license->license && empty( $showed_invalid_message ) ) {
	
				if( empty( $_GET['tab'] ) || 'licenses' !== $_GET['tab'] ) {
	
					$messages[] = sprintf(
						__( 'You have invalid or expired license keys for MDJM Event Management. Please go to the <a href="%s" title="Go to Licenses page">Licenses page</a> to correct this issue.', 'easy-digital-downloads' ),
						admin_url( 'edit.php?post_type=mdjm-event&page=mdjm-settings&tab=licenses' )
					);
	
					$showed_invalid_message = true;
	
				}
	
			}
	
			if( ! empty( $messages ) ) {
	
				foreach( $messages as $message ) {
	
					echo '<div class="error">';
						echo '<p>' . $message . '</p>';
					echo '</div>';
	
				}
	
			}
	
		} // notices
		
		/**
		 * Displays message inline on plugin row that the license key is missing
		 *
		 * @access  public
		 * @since   1.3
		 * @return  void
		 */
		public function plugin_row_license_missing( $plugin_data, $version_info ) {
	
			static $showed_imissing_key_message;
	
			$license = get_option( $this->item_shortname . '_license_active' );
	
			if( ( ! is_object( $license ) || 'valid' !== $license->license ) && empty( $showed_imissing_key_message[ $this->item_shortname ] ) ) {
	
				echo '&nbsp;<strong><a href="' . esc_url( admin_url( 'edit.php?post_type=mdjm-event&page=mdjm-settings&tab=licenses' ) ) . '">' . __( 'Enter a valid license key for automatic updates.', 'mobile-dj-manager' ) . '</a></strong>';
				$showed_imissing_key_message[ $this->item_shortname ] = true;
			}
	
		} // plugin_row_license_missing

	} // class MDJM_License
endif; // end class_exists check
