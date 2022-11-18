<?php
/*
 * Plugin Name: MDJM Extension - MailChimp Subscribe
 * Description: Sync new contacts to your mailing list through MailChimp
 * Version: 1.2
 * Date: 16 September 2022
 * Author: MDJM
 * Author URI: https://mdjm.co.uk
 * Text Domain: mdjm-mailchimp-subscribe
 * Domain Path: /lang
 */

define( 'MDJM_MC_VERSION', '1.2' );

class MDJM_MC_Subscribe {
	private static $instance;
	private static $required_mdjm = '1.3.8.2';

	public static function instance() {
		// Do nothing if MDJM is not activated
		if ( ! class_exists( 'Mobile_DJ_Manager', false ) || version_compare( self::$required_mdjm, MDJM_VERSION_NUM, '>' ) ) {
			add_action( 'admin_notices', array( __CLASS__, 'notices' ) );
			return;
		}

		if ( ! isset( self::$instance ) && ! ( self::$instance instanceof MDJM_MC_Subscribe ) ) {

			self::$instance = new MDJM_MC_Subscribe();

			self::define_constants();
			self::includes();
			self::hooks();
		}

	} // __construct

	/**
	 * Define our constants
	 *
	 * @since   1.0
	 */
	public static function define_constants() {
		define( 'MDJM_MC_DIR', untrailingslashit( dirname( __FILE__ ) ) );
		define( 'MDJM_MC_BASENAME', plugin_basename( __FILE__ ) );
		define( 'MDJM_MC_FILE', __FILE__ );
		define( 'MDJM_MC_URL', untrailingslashit( plugins_url( '', __FILE__ ) ) );
	} // define_constants

	/**
	 * Calls the files that are required
	 *
	 * @since   1.0
	 */
	public static function includes() {

		if ( is_admin() ) {

			if ( class_exists( 'MDJM_License' ) ) {
				$mdjm_stripe_license = new MDJM_License( __FILE__, 'MailChimp Subscribe', MDJM_MC_VERSION, 'MDJM' );
			}
		}

		require_once MDJM_MC_DIR . '/lib/class-mdjm-mc-api.php';
		require_once MDJM_MC_DIR . '/functions.php';

	} // includes

	/**
	 * Hooks
	 *
	 * @since   1.0
	 */
	public static function hooks() {
		add_filter( 'mdjm_settings_sections_extensions', array( __CLASS__, 'register_settings_section' ) );
		add_filter( 'mdjm_settings_extensions', array( __CLASS__, 'register_settings' ) );
		add_filter( 'plugin_row_meta', array( __CLASS__, 'plugin_meta_links' ), 10, 2 );
	} // hooks

	/**
	 * Register extension settings
	 *
	 * @since   1.0
	 */
	public static function register_settings( $settings ) {

		$mc_settings = array(
			'mailchimp-subscribe' => apply_filters(
				'mdjm_mc_subscribe_settings',
				array(
					array(
						'id'   => 'mc_subscribe_settings_header',
						'name' => '<strong>' . __( 'MailChimp Settings', 'mdjm-mailchimp-subscribe' ) . '</strong>',
						'desc' => '',
						'type' => 'header',
					),
					array(
						'id'   => 'mc_api_key',
						'name' => __( 'MailChimp API Key', 'mdjm-mailchimp-subscribe' ),
						'desc' => __( 'Enter your API Key. <a href="http://admin.mailchimp.com/account/api-key-popup" target="_blank">Get your API key</a>', 'mobile-dj-manager' ),
						'type' => 'mcs_api',
					),
					array(
						'id'      => 'mc_list',
						'name'    => __( 'MailChimp List', 'mdjm-mailchimp-subscribe' ),
						'desc'    => __( 'This list will be populated after you add your MailChimp API Key above and save it.', 'mdjm-mailchimp-subscribe' ),
						'type'    => 'select',
						'options' => self::get_lists(),
					),
					array(
						'id'   => 'mc_force_refresh',
						'name' => __( 'Force MailChimp lists refresh', 'mdjm-mailchimp-subscribe' ),
						'desc' => __( "If you've added a new MailChimp list and it's not showing above, check this option and Save Settings.", 'mdjm-mailchimp-subscribe' ),
						'type' => 'checkbox',
					),
					array(
						'id'   => 'mc_auto_subscribe',
						'name' => __( 'Auto Subscribe New Clients?', 'mdjm-mailchimp-subscribe' ),
						'desc' => '',
						'type' => 'checkbox',
					),
					array(
						'id'      => 'mc_format',
						'name'    => __( 'Default Format', 'mdjm-mailchimp-subscribe' ),
						'desc'    => '',
						'type'    => 'select',
						'options' => array(
							'html' => 'HTML',
							'text' => 'Plain Text',
						),
						'std'     => 'html',
					),
					array(
						'id'   => 'mc_update',
						'name' => __( 'Update User Details?', 'mdjm-mailchimp-subscribe' ),
						'desc' => __( 'Update the users details within MailChimp if the email address already exists?', 'mdjm-mailchimp-subscribe' ),
						'type' => 'checkbox',
					),
					array(
						'id'   => 'mc_welcome',
						'name' => __( 'Send Welcome Email?', 'mdjm-mailchimp-subscribe' ),
						'desc' => '',
						'type' => 'checkbox',
					),
					array(
						'id'   => 'mc_double_optin',
						'name' => __( 'Enable Double Opt-In?', 'mdjm-mailchimp-subscribe' ),
						'desc' => __( "<a href='http://kb.mailchimp.com/article/how-does-confirmed-optin-or-double-optin-work' target='_blank'>Learn more</a>.", 'mdjm-mailchimp-subscribe' ),
						'type' => 'checkbox',
					),
				)
			),
		);

		return array_merge( $settings, $mc_settings );

	} // register_settings

	/**
	 * Registers the new MailChimp Subscribe license options section
	 * *
	 *
	 * @since       1.0
	 * @param       $sections array the existing plugin sections
	 * @return      array
	 */
	public static function register_settings_section( $sections ) {
		$sections['mailchimp-subscribe'] = __( 'MailChimp Subscribe', 'mdjm-mailchimp-subscribe' );

		return $sections;
	} // register_settings_section

	/**
	 * Subscribe Client to MailChimp
	 *
	 * @since   1.0
	 */
	public static function subscribe_client( $client_data ) {

		if ( ! empty( $post['mdjm_mc_susbscribe'] ) && $post['mdjm_mc_susbscribe'] == '1' ) {

			try {

				$list_id    = mdjm_get_option( 'mc_list' );
				$email      = $user_info['email'];
				$merge_vars = array(
					'FNAME' => $user_info['first_name'],
					'LNAME' => $user_info['last_name'],
				);

				if ( mdjm_get_option( 'mc_double_optin', false ) ) {
					$double_optin = true;
				} else {
					$double_optin = false;
				}
				$api = new MDJM_MC_API();

				$retval = $api->listSubscribe( $listId, $email, $merge_vars, $email_type = 'html', $double_optin );

			} catch ( Exception $e ) {
			}
		}

	} // subscribe_client

	/**
	 * Get List from MailChimp
	 *
	 * @since   1.0
	 */
	public static function get_lists() {

		$mailchimp_lists = unserialize( get_transient( 'mdjm_mailchimp_mailinglist' ) );

		if ( empty( $mailchimp_lists ) || mdjm_get_option( 'mc_force_refresh' ) ) {

			$mailchimp_lists = array();
			$list_id         = mdjm_get_option( 'mc_list' );

			$mdjm_mc = new MDJM_MC_API();

			$lists = $mdjm_mc->get_lists();

			if ( $mdjm_mc->has_error() ) {
				$mailchimp_lists['false'] = __( 'Unable to load MailChimp lists, check your API Key.', 'mdjm-mailchimp-subscribe' );
			} else {

				if ( ! $lists ) {
					$mailchimp_lists['false'] = __( 'You have not created any lists at MailChimp', 'mdjm-mailchimp-subscribe' );
					return $mailchimp_lists;
				}

				foreach ( $lists as $list ) {
					$mailchimp_lists[ $list->id ] = $list->name;
				}

				set_transient( 'mdjm_mailchimp_mailinglist', serialize( $mailchimp_lists ), 86400 );
				mdjm_update_option( 'mc_force_refresh', false );
			}
		}

		return $mailchimp_lists;

	} // get_lists

	/**
	 * Display a notice if MDJM not active or at required version.
	 *
	 * @since   1.0
	 */
	public static function notices() {

		if ( ! defined( 'MDJM_VERSION_NUM' ) ) {
			$message = sprintf( __( 'MDJM MailChimp Subscribe requires that MDJM Event Management must be installed and activated.', 'mdjm-mailchimp-subscribe' ) );
		} else {
			$message = sprintf( __( 'MDJM MailChimp Subscribe requires MDJM Event Management version %s and higher. Update MDJM to use MailChimp Subscribe', 'mdjm-mailchimp-subscribe' ), self::$required_mdjm );
		}

		echo '<div class="notice notice-error is-dismissible">';
		echo '<p>' . $message . '</p>';
		echo '</div>';

	} // notices

	/**
	 * Add links to the plugin row meta
	 *
	 * @since   1.0
	 * @param   arr $links  Plugin meta links
	 * @return  arr     $links  Plugin meta links
	 */
	public static function plugin_meta_links( $links, $file ) {

		$plugin = plugin_basename( __FILE__ );

		if ( $plugin == $file ) {
			return array_merge(
				$links,
				array(
					'<a href="https://mdjm.co.uk/support/" target="_blank">' . __( 'Documentation' ) . '</a>',
					'<a href="https://mdjm.co.uk/extensions/" target="_blank">' . __( 'More Extensions' ) . '</a>', 
					'<a href="edit.php?post_type=mdjm-event&page=mdjm-settings&tab=extensions&section=mailchimp-subscribe">' . __( 'Settings' ) . '</a>', 
				)
			);
		}
		return $links;
	} // plugin_meta_links

} // MDJM_MC_Subscribe

function MDJM_MC_Subscribe() {
	return MDJM_MC_Subscribe::instance();
} // MDJM_MC_Subscribe
add_action( 'plugins_loaded', 'MDJM_MC_Subscribe' );

/**
 * Install procedures.
 *
 * Runs outside the singleton.
 *
 * @since   1.0
 */
function mdjm_mc_activate() {

	if ( ! class_exists( 'Mobile_DJ_Manager', false ) ) {
		return;
	}

	$current_version = get_option( 'mdjm_mc_version' );
	if ( $current_version ) {
		return;
	}

	update_option( 'mdjm_mc_version', MDJM_MC_VERSION );

} // mdjm_mc_activate
register_activation_hook( __FILE__, 'mdjm_mc_activate' );
