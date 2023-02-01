<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Plugin Name: MDJM Extension - PayPal Standard
 * Description: Accept event payments via the Client Zone with PayPal Standard.
 * Version: 1.5.0
 * Date: 21st March 2021
 * Author: MDJM <info@mdjm.co.uk>
 * Author URI: http://mdjm.co.uk
 * Requires: MDJM Event Management version 1.3 or higher
 */
define( 'MDJM_PAYPAL_STD_VERSION', '1.5.0' );
class MDJM_PayPal_Std {
	private static $instance;
	private static $required_mdjm = '1.3.8';
	
	public static function instance() {
		
		// Do nothing if MDJM is not activated
		if ( ! class_exists( 'Mobile_DJ_Manager', false ) || version_compare( self::$required_mdjm, MDJM_VERSION_NUM, '>' ) ) {
			add_action( 'admin_notices', array( __CLASS__, 'notices' ) );
			return;
		}
		
		if ( ! isset( self::$instance ) && ! ( self::$instance instanceof MDJM_PayPal_Std ) ) {                  
			self::$instance = new MDJM_PayPal_Std();
			
			self::define_constants();
			self::includes();
			self::hooks();
		}
			
	} // __construct
	
	/**
	 * Define constants.
	 *
	 * @since   0.1
	 */
	public static function define_constants() {
		define( 'MDJM_PAYPAL_STD_DIR', untrailingslashit( dirname( __FILE__ ) ) );
		define( 'MDJM_PAYPAL_STD_BASENAME', plugin_basename( __FILE__ ) );
		define( 'MDJM_PAYPAL_STD_URL', untrailingslashit( plugins_url( '', __FILE__ ) ) );
		define( 'MDJM_PAYPAY_STD_PLUGIN_FILE', __FILE__ );
	} // define_constants
	
	/**
	 * Include files.
	 *
	 * @since   0.1
	 */
	public static function includes() {
		include_once MDJM_PAYPAL_STD_DIR . '/includes/settings.php';
		include_once MDJM_PAYPAL_STD_DIR . '/includes/actions.php';
		include_once MDJM_PAYPAL_STD_DIR . '/includes/functions.php';
		include_once MDJM_PAYPAL_STD_DIR . '/includes/shortcodes.php';

		if ( is_admin() ) {
			include_once MDJM_PAYPAL_STD_DIR . '/includes/updates.php';
		}

	} // includes
	
	/**
	 * Hooks
	 *
	 * @since   0.1
	 */
	public static function hooks() {
		add_filter( 'allowed_redirect_hosts', array( __CLASS__, 'whitelist_paypal_domains_for_redirect' ) );
		add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), array( __CLASS__, 'plugin_action_links' ) );      
		add_filter( 'plugin_row_meta', array( __CLASS__, 'plugin_meta_links' ), 10, 2 );    
		add_filter( 'mdjm_template_paths', array( __CLASS__, 'set_template_path' ) );
		add_filter( 'mdjm_messages', array( __CLASS__, 'messages' ) );
		
		/**
		 * PayPal Remove CC Form
		 *
		 * PayPal Standard does not need a CC form, so remove it.
		 *
		 * @access  private
		 * @since   1.3
		 */
		add_action( 'mdjm_paypal_cc_form', '__return_false' );
		add_action( 'mdjm_paypal_cc_form', array( __CLASS__, 'paypal_form' ) );
		
	} // hooks

	/**
	 * Allow PayPal domains for redirect.
	 *
	 * @since 1.5.0
	 * @param array $domains Whitelisted domains for `wp_safe_redirect`
	 * @return array $domains Whitelisted domains for `wp_safe_redirect`
	 */
	public function whitelist_paypal_domains_for_redirect( $domains ) {
		$domains[] = 'www.paypal.com';
		$domains[] = 'paypal.com';
		$domains[] = 'www.sandbox.paypal.com';
		$domains[] = 'sandbox.paypal.com';
		return $domains;
	}

	/**
	 * Create a template path to check for Payment template locations
	 *
	 * @since 1.3
	 * @return mixed|void
	 */
	public static function set_template_path( $file_paths ) {
	
		$file_paths[20] = MDJM_PAYPAL_STD_DIR . '/templates';
	
		return $file_paths;
	} // set_template_path

	/**
	 * Render the payment form for PayPal.
	 *
	 * @since   1.3
	 * @return  str
	 */
	public static function paypal_form() {
		?>

		<?php do_action( 'mdjm_paypal_before_fields' ); ?>

            <?php mdjm_get_template_part( 'payments', 'paypal' ); ?>

        <?php
        do_action( 'mdjm_paypal_after_fields' );
	} // paypal_form

	/**
	 * Display a notice if MDJM not active or at required version.
	 *
	 * @since   1.3
	 */
	public static function notices() {

		if ( ! defined( 'MDJM_VERSION_NUM' ) ) {
			$message = sprintf( __( 'MDJM PayPal Standard requires that MDJM Event Management must be installed and activated.', 'mdjm-paypal-standard' ) );
		} else {
			$message = sprintf( __( 'MDJM PayPal Standard requires MDJM Event Management version %s and higher. Upgrade MDJM to use this add-on.', 'mdjm-paypal-standard' ), self::$required_mdjm );
		}
		
		echo '<div class="notice notice-error is-dismissible">';
		echo '<p>' . esc_html( $message ) . '</p>';
		echo '</div>';

	} // notices

	/**
	 * Add PayPal messages to the MDJM messages array.
	 *
	 * @since   1.3
	 * @param   arr     $messages   Existing MDJM messages
	 * @return  arr     $messages
	 */
	public static function messages( $messages ) {
		$messages['paypal_success'] = array(
			'class'   => 'success',
			'title'   => __( 'Thank you. Your payment has completed successfully', 'mdjm-paypal-standard' ),
			'message' => __( 'You will shortly receive an email from us (remember to check your junk email folder) confirming the payment and detailing next steps for your event', 'mdjm-paypal-standard' ) .
				'<br /><br />' .
				sprintf( 
                    __( 'Please note that it can take a few minutes for our systems to be updated and therefore your payment may not have registered below as yet. Once you receive the payment confirmation email from us, the payment will be updated on our systems', 'mdjm-paypal-standard' )
				),
		);

		$messages['paypal_cancelled'] = array(
			'class'   => 'warn',
			'title'   => __( 'Your payment has been cancelled', 'mdjm-paypal-standard' ),
			'message' => __( 'To process your payment again, please follow the steps below', 'mdjm-paypal-standard' ),
		);

		return $messages;
	} // messages

	/**
	 * Add the Settings 'action' link to the plugin screen.
	 *
	 * @since   1.3
	 */
	public static function plugin_action_links( $links ) {
		$paypal_links = array(
			'<a href="' . admin_url( 'admin.php?page=mdjm-settings' .
			'&tab=payments' ) . '">' . __( 'Payment Settings', 'mobile-dj-manager' ) . '</a>',
		);

		return array_merge( $links, $paypal_links );
	} // plugin_action_links
	
	/**
	 * Add links to the plugin row meta.
	 *
	 * @since   1.3
	 */
	public static function plugin_meta_links( $links, $file ) {
		
		$plugin = plugin_basename( __FILE__ );

		if ( $file === $plugin ) {

			$extra_links[] = '<a href="https://mdjm.co.uk/donate/?mtm_campaign=donate&mtm_kwd=mdjm-paypal-standard" target="_blank">' . __( 'Donate', 'mdjm-paypal-standard' ) . '</a>';

			return array_merge( $links, $extra_links );
		}

		return $links;
	} // plugin_meta_links
	
} // MDJM_PayPal_Std

function MDJM_PayPal_Std() { // phpcs:ignore WordPress.NamingConventions.ValidFunctionName.FunctionNameInvalid
	return MDJM_PayPal_Std::instance();
} // MDJM_PayPal_Std
add_action( 'plugins_loaded', 'MDJM_PayPal_Std' );

/**
 * Execute the install procedures
 *
 * @since   1.3
 * @param
 * @return  void
 */
function mdjm_paypal_run_install() {

	global $mdjm_options;

	$current_version = get_option( 'mdjm_paypal_std_version' );
	if ( $current_version ) {
		return;
	}

	$paypal_options = array(
		'mdjm_pg_paypal_paypal_email'     => 'paypal_email',
		'mdjm_pg_paypal_checkout_style'   => 'paypal_page_style',
		'mdjm_pg_paypal_redirect_success' => 'paypal_redirect_success',
		'mdjm_pg_paypal_redirect_cancel'  => 'paypal_redirect_cancel',
		'mdjm_pg_paypal_enable_sandbox'   => 'paypal_enable_sandbox',
		'mdjm_pg_paypal_sandbox_email'    => 'paypal_sandbox_email',
	);

	if ( $mdjm_options ) {
		
		foreach ( $paypal_options as $old => $new ) {
			if ( isset( $mdjm_options[ $old ] ) ) {
				mdjm_update_option( $new, $mdjm_options[ $old ] );
				mdjm_delete_option( $old );
			}
		}   
	}

	add_option( 'mdjm_paypal_std_version', MDJM_PAYPAL_STD_VERSION );
	
} // mdjm_paypal_run_install
register_activation_hook( __FILE__, 'mdjm_paypal_run_install' );
