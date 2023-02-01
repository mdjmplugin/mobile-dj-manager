<?php
/**
 * Contains MDJM PayPal Standard
 *
 * @package     MDJM PayPal Standard
 * @subpackage  Functions
 * @since       1.3
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * MDJM PayPal Payments Processor.
 *
 * @since   1.3
 * @param   arr     $payment_data   Validated payment data
 * @return  void
 */
function mdjm_process_paypal_payment( $payment_data ) {

	if ( ! wp_verify_nonce( $payment_data['gateway_nonce'], 'mdjm-gateway' ) ) {
		wp_die( esc_html__( 'Nonce verification has failed', 'mdjm-paypal-standard' ), esc_html__( 'Error', 'mdjm-paypal-standard' ), array( 'response' => 403 ) );
	}

	$event_id   = $payment_data['event_id'];
	$txn_id     = $payment_data['txn_id'];
	$total      = mdjm_sanitize_amount( $payment_data['total'] );
	$type       = $payment_data['type'];
	$return_url = add_query_arg( 'event_id', $event_id, mdjm_get_paypal_return_url() );
	$cancel_url = add_query_arg( array(
        'event_id' => $event_id,
        'invoice'  => $txn_id,
	), mdjm_get_paypal_cancel_url() );
	$notify_url = add_query_arg( 'mdjm-listener', 'IPN', home_url( 'index.php' ) );
	$item_name  = sprintf(
		__( '%1$s ID %2$s', 'mobile-dj-manager' ), mdjm_get_label_singular(), mdjm_get_event_contract_id( $event_id )
	);

	$error = false;

	$paypal_redirect = trailingslashit( mdjm_get_paypal_redirect() ) . '?';

	// Define PayPal arguments
	$paypal_args = array(
		'business'      => mdjm_get_paypal_email(),
		'email'         => $payment_data['client_data']['email'],
		'first_name'    => $payment_data['client_data']['first_name'],
		'last_name'     => $payment_data['client_data']['last_name'],
		'invoice'       => $txn_id,
		'no_shipping'   => '1',
		'shipping'      => '0',
		'no_note'       => '1',
		'currency_code' => mdjm_get_currency(),
		'lc'            => get_locale(),
		'charset'       => get_bloginfo( 'charset' ),
		'custom'        => $type,
		'on0'           => __( 'for', 'mobile-dj-manager' ),
		'os0'           => $type,
		'amount'        => $total,
		'rm'            => '2',
		'return'        => $return_url,
		'cancel_return' => $cancel_url,
		'notify_url'    => $notify_url,
		'page_style'    => mdjm_get_paypal_page_style(),
		'cbt'           => mdjm_get_option( 'company_name' ),
		'cmd'           => '_xclick',
		'bn'            => 'MDJM_BN',
		'item_name'     => $item_name,
		'item_number'   => $event_id,
		
	);

	$paypal_args = apply_filters( 'mdjm_paypal_args', $paypal_args );

	// Build query
	$paypal_redirect .= http_build_query( $paypal_args );

	// Fix for some sites that encode the entities
	$paypal_redirect = str_replace( '&amp;', '&', $paypal_redirect );

	// Redirect to PayPal
    wp_safe_redirect( $paypal_redirect );
	exit;

} // mdjm_process_paypal_payment
add_action( 'mdjm_gateway_paypal', 'mdjm_process_paypal_payment' );

/**
 * Listens for a PayPal IPN requests and then sends to the processing function.
 *
 * @since   1.3
 * @return  void
 */
function mdjm_listen_for_paypal_ipn() {
	// Regular PayPal IPN
	if ( isset( $_GET['mdjm-listener'] ) && 'IPN' === $_GET['mdjm-listener'] ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		do_action( 'mdjm_verify_paypal_ipn' );
	}
} // mdjm_listen_for_paypal_ipn
add_action( 'init', 'mdjm_listen_for_paypal_ipn' );

/**
 * Process PayPal IPN
 *
 * @since   1.3
 * @return  void
 */
function mdjm_process_paypal_ipn() {
	// Check the request method is POST
	if ( isset( $_SERVER['REQUEST_METHOD'] ) && 'POST' !== $_SERVER['REQUEST_METHOD'] ) {
		return;
	}

	// Set initial post data to empty string
	$post_data = '';

	// Fallback just in case post_max_size is lower than needed
	if ( ini_get( 'allow_url_fopen' ) ) {
		$post_data = file_get_contents( 'php://input' );
	} else {
		// If allow_url_fopen is not enabled, then make sure that post_max_size is large enough
		ini_set( 'post_max_size', '12M' );
	}
	// Start the encoded data collection with notification command
	$encoded_data = 'cmd=_notify-validate';

	// Get current arg separator
	$arg_separator = mdjm_get_php_arg_separator_output();

	// Verify there is a post_data
	if ( $post_data || strlen( $post_data ) > 0 ) {
		// Append the data
		$encoded_data .= $arg_separator . $post_data;
	} else {
		// Check if POST is empty
		if ( empty( $_POST ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing
			// Nothing to do
			return;
		} else {
			// Loop through each POST
			foreach ( $_POST as $key => $value ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing
				// Encode the value and append the data
				$encoded_data .= $arg_separator . "$key=" . rawurlencode( $value );
			}
		}
	}

	// Convert collected post data to an array
	parse_str( $encoded_data, $encoded_data_array );

	foreach ( $encoded_data_array as $key => $value ) {

		if ( false !== strpos( $key, 'amp;' ) ) {
			$new_key = str_replace( '&amp;', '&', $key );
			$new_key = str_replace( 'amp;', '&', $new_key );

			unset( $encoded_data_array[ $key ] );
			$encoded_data_array[ $new_key ] = $value;
		}   
	}

	// Get the PayPal redirect uri
	$paypal_redirect = mdjm_get_paypal_redirect( true );

	// Validate the IPN
	$remote_post_vars = array(
		'method'      => 'POST',
		'timeout'     => 45,
		'redirection' => 5,
		'httpversion' => '1.1',
		'blocking'    => true,
		'headers'     => array(
			'host'         => 'www.paypal.com',
			'connection'   => 'close',
			'content-type' => 'application/x-www-form-urlencoded',
			'post'         => '/cgi-bin/webscr HTTP/1.1',

		),
		'sslverify'   => false,
		'body'        => $encoded_data_array,
	);

	// Get response
	$api_response = wp_remote_post( mdjm_get_paypal_redirect( true ), $remote_post_vars );

	if ( is_wp_error( $api_response ) ) {
		mdjm_record_gateway_log( sprintf( __( 'IPN Error: Invalid IPN verification response. IPN data: %s', 'mobile-dj-manager' ), wp_json_encode( $api_response ) ) );
		return; // Something went wrong
	}

	// Check if $post_data_array has been populated
	if ( ! is_array( $encoded_data_array ) && ! empty( $encoded_data_array ) ) {
		return;
	}

	do_action( 'mdjm_complete_paypal_payment', $encoded_data_array );
	exit;

}
add_action( 'mdjm_verify_paypal_ipn', 'mdjm_process_paypal_ipn' );

/**
 * Redirect to the PayPal success page when returning from successful PayPal checkout.
 *
 * @since   1.3
 * @param   arr     $data   $_GET super global
 * @return  void
 */
function mdjm_redirect_paypal_success_page( $data ) {

	if ( ! isset( $data['event_id'] ) ) {
		return;
	}

	$page = mdjm_get_option( 'paypal_redirect_success', false );

	if ( ! $page ) {
		$page = mdjm_get_option( 'payments_page' );
	}

    wp_safe_redirect(
		add_query_arg( 
			array(
				'event_id'     => $data['event_id'],
				'mdjm_message' => 'paypal_success',
			),
			mdjm_get_formatted_url( $page )
        )
	);
	exit();

} // mdjm_redirect_paypal_success_page
add_action( 'mdjm_paypal_return_complete', 'mdjm_redirect_paypal_success_page' );

/**
 * Redirect to the PayPal success page when returning from cancelled PayPal checkout.
 *
 * @since   1.3
 * @param   arr     $data   $_GET super global
 * @return  void
 */
function mdjm_redirect_paypal_cancel_page( $data ) {

	if ( ! isset( $data['event_id'] ) ) {
		return;
	}

	$page = mdjm_get_option( 'paypal_redirect_cancel', false );

	if ( ! $page ) {
		$page = mdjm_get_option( 'payments_page' );
	}

	if ( isset( $data['invoice'] ) ) {
		mdjm_update_txn_status( $data['invoice'], 'Cancelled' );
	}

    wp_safe_redirect(
		add_query_arg( 
			array(
				'event_id'     => $data['event_id'],
				'mdjm_message' => 'paypal_cancelled',
				'payment-mode' => 'paypal',
			),
			mdjm_get_formatted_url( $page )
        )
	);
	exit();

} // mdjm_redirect_paypal_cancel_page
add_action( 'mdjm_paypal_return_cancel', 'mdjm_redirect_paypal_cancel_page' );
