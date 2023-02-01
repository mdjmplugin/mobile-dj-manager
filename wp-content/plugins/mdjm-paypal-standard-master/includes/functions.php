<?php
/**
 * Contains MDJM PayPal Standard functions
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
 * Whether or not PayPal Standard is in test mode.
 *
 * @since   1.3
 * @return  bool
 */
function mdjm_paypal_is_test() {
	return mdjm_get_option( 'paypal_enable_sandbox', false );
} // mdjm_paypal_is_test

/**
 * Retrieve PayPal Email address.
 *
 * @since   1.3
 * @return  str
 */
function mdjm_get_paypal_email() {

	if ( mdjm_paypal_is_test() ) {
		$paypal_email = mdjm_get_option( 'paypal_sandbox_email', false );
	} else {
		$paypal_email = mdjm_get_option( 'paypal_email', false );
	}

	if ( ! $paypal_email ) {
		$paypal_email = get_option( 'admin_email' );
	}

	return $paypal_email;

} // mdjm_get_paypal_email

/**
 * Retrieve PayPal Return URL.
 *
 * @since   1.3
 * @return  str
 */
function mdjm_get_paypal_return_url() {

	$home_url = home_url();

	$return_url = add_query_arg(
		array(
			'mdjm_action' => 'paypal_return_complete',
		),
		trailingslashit( $home_url )
	);

	return $return_url;

} // mdjm_get_paypal_return_url

/**
 * Retrieve PayPal Cancel URL.
 *
 * @since   1.3
 * @return  str
 */
function mdjm_get_paypal_cancel_url() {

	$home_url = home_url();

	$cancel_url = add_query_arg(
		array(
			'mdjm_action' => 'paypal_return_cancel',
		),
		trailingslashit( $home_url )
	);

	return $cancel_url;

} // mdjm_get_paypal_cancel_url

/**
 * Retrieve PayPal Checkout Page Style.
 *
 * @since   1.3
 * @return  str
 */
function mdjm_get_paypal_page_style() {

	$page_style = mdjm_get_option( 'paypal_page_style', false );

	if ( ! $page_style ) {
		$page_style = 'paypal';
	}

	return $page_style;
} // mdjm_get_paypal_page_style

/**
 * Setup Taxes for PayPal.
 *
 * @since   1.3
 * @return  str
 */
function mdjm_set_paypal_taxes( $paypal_args ) {

	$enable_tax = mdjm_get_option( 'enable_tax', false );
	$tax_rate   = mdjm_get_option( 'tax_rate', false );

	if ( $enable_tax && $tax_rate ) {
		$tax_type = mdjm_get_option( 'tax_type', false );

		if ( $tax_type ) {
			if ( 'percentage' === $tax_type ) {
				$tax = 'tax_rate';
			} else {
				$tax = 'tax';
			}
			$paypal_args[ $tax ] = $tax_type;
		}   
	}

	return $paypal_args;

} // mdjm_set_paypal_taxes
add_filter( 'mdjm_paypal_args', 'mdjm_set_paypal_taxes' );

/**
 * Get PayPal Redirect URL
 *
 * @since   1.3
 * @param   bool    $ssl_check  Is SSL?
 * @return  str
 */
function mdjm_get_paypal_redirect( $ssl_check = false ) {

	$protocol = 'http://';

	if ( is_ssl() || ! $ssl_check ) {
		$protocol = 'https://';
	}

	// Check the current payment mode
	if ( mdjm_paypal_is_test() ) {
		// Test mode
		$paypal_uri = $protocol . 'www.sandbox.paypal.com/cgi-bin/webscr';
	} else {
		// Live mode
		$paypal_uri = $protocol . 'www.paypal.com/cgi-bin/webscr';
	}

	return apply_filters( 'mdjm_paypal_uri', $paypal_uri );

} // mdjm_get_paypal_redirect

/**
 * Complete the PayPal Event Payment once verified.
 *
 * @since   1.3
 * @param   arr     $data   Data retrieved via IPN.
 * @return  void
 */
function mdjm_paypal_complete_payment( $data ) {

	if ( ! isset( $data['payment_status'] ) || 'Completed' !== $data['payment_status'] ) {
		return;
	}

	$txn_id         = $data['invoice'];
	$event_id       = $data['item_number'];
	$mdjm_txn       = new MDJM_Txn( $txn_id );
	$currency_code  = strtoupper( $data['mc_currency'] );
	$business_email = isset( $data['business'] ) && is_email( $data['business'] ) ? trim( $data['business'] ) : trim( $data['receiver_email'] );

	// Verify payment recipient
	if ( strcasecmp( $business_email, trim( mdjm_get_paypal_email() ) ) !== 0 ) {

		mdjm_record_gateway_log( __( 'IPN Error', 'mobile-dj-manager' ) . sprintf( __( 'Invalid business email in IPN response. IPN data: %s', 'mobile-dj-manager' ), wp_json_encode( $data ) ) );

		mdjm_update_txn_meta(
			$txn_id,
			array(
				'_mdjm_txn_status'         => 'Failed',
				'_mdjm_txn_failure_reason' => __( 'Payment failed due to invalid PayPal business email.', 'mobile-dj-manager' ),
			)
		);
		return;
	}

	// Verify payment currency
	if ( $currency_code !== $mdjm_txn->currency ) {

		mdjm_record_gateway_log( __( 'IPN Error', 'mobile-dj-manager' ) . sprintf( __( 'Invalid currency in IPN response. IPN data: %s', 'mobile-dj-manager' ), wp_json_encode( $data ) ) );

		mdjm_update_txn_meta(
			$txn_id,
			array(
				'_mdjm_txn_status'         => 'Failed',
				'_mdjm_txn_failure_reason' => __( 'Payment failed due to invalid currency in PayPal IPN.', 'mobile-dj-manager' ),
			)
		);

		return;
	}

	// Generate the transaction data to complete the process
	$txn_data = array(
		'event_id'        => $event_id,
		'client_id'       => mdjm_get_event_client_id( $event_id ),
		'txn_id'          => $txn_id,
		'type'            => $data['custom'],
		'total'           => $data['mc_gross'],
		'status'          => $data['payment_status'],
		'gateway'         => 'paypal',
		'gw_invoice'      => '',
		'gw_id'           => $data['txn_id'],
		'billing_address' => '',
		'data'            => $data,
		'currency'        => $currency_code,
		'fee'             => $data['mc_fee'],
		'date'            => wp_date( 'Y-m-d H:i:s', strtotime( $data['payment_date'] ) ),
		'card_type'       => '',
		'live'            => empty( $data['test_ipn'] ) ? true : false,
	);

	mdjm_complete_event_payment( $txn_data );

} // mdjm_paypal_complete_payment
add_action( 'mdjm_complete_paypal_payment', 'mdjm_paypal_complete_payment' );
