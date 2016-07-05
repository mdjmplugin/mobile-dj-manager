<?php

/**
 * Contains payment functions.
 *
 * @package		MDJM
 * @subpackage	Functions
 * @since		1.3.8
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) )
	exit;

/**
 * Whether or not a payment is in progress.
 *
 * @since	1.3.8
 * @param	bool	$ssl	True if SSL required, otherwise false.
 * @return	bool	True if a payment is in progress, otherwise false.
 */
function mdjm_is_payment( $ssl = false )	{

	$is_payment = is_page( mdjm_get_option( 'payments_page' ) );

	if ( isset( $_GET['mdjm_action'] ) && 'process_payment' == $_GET['mdjm_action'] )	{
		$is_payment == true;
	}

	if ( $ssl && ! is_ssl() )	{
		$is_payment = false;
	}

	return apply_filters( 'mdjm_is_payment', $is_payment, $ssl );

} // mdjm_is_payment

/**
 * Returns a list of all available gateways.
 *
 * @since	1.3.8
 * @return	arr		$gateways	All the available gateways
 */
function mdjm_get_payment_gateways() {

	$gateways = array(
		'disabled' => array(
			'admin_label'   => __( 'Disabled', 'mobile-dj-manager' ),
			'payment_label' => __( 'Disabled', 'mobile-dj-manager' )
		)
	);

	return apply_filters( 'mdjm_payment_gateways', $gateways );
} // mdjm_get_payment_gateways

/**
 * Returns a list of all enabled gateways.
 *
 * @since	1.3.8
 * @param	bool	$sort			If true, the default gateway will be first
 * @return	arr		$gateway_list	All the available gateways
 */
function mdjm_get_enabled_payment_gateways( $sort = false ) {
	$gateways = mdjm_get_payment_gateways();
	$enabled  = (array) mdjm_get_option( 'gateways', false );

	$gateway_list = array();

	foreach ( $gateways as $key => $gateway ) {
		if ( isset( $enabled[ $key ] ) && $enabled[ $key ] == 1 ) {
			$gateway_list[ $key ] = $gateway;
		}
	}

	if ( true === $sort ) {
		// Reorder our gateways so the default is first
		$default_gateway_id = mdjm_get_default_gateway();

		if( mdjm_is_gateway_active( $default_gateway_id ) ) {

			$default_gateway    = array( $default_gateway_id => $gateway_list[ $default_gateway_id ] );
			unset( $gateway_list[ $default_gateway_id ] );

			$gateway_list = array_merge( $default_gateway, $gateway_list );

		}

	}

	return apply_filters( 'mdjm_enabled_payment_gateways', $gateway_list );
} // mdjm_get_enabled_payment_gateways

/**
 * Checks whether a specified gateway is activated.
 *
 * @since	1.3.8
 * @param	str		$gateway	Name of the gateway to check for
 * @return	bool	true if enabled, false otherwise
 */
function mdjm_is_gateway_active( $gateway ) {
	$gateways = mdjm_get_enabled_payment_gateways();
	$ret = array_key_exists( $gateway, $gateways );
	return apply_filters( 'mdjm_is_gateway_active', $ret, $gateway, $gateways );
} // mdjm_is_gateway_active

/**
 * Gets the default payment gateway selected from the MDJM Settings
 *
 * @since	1.3.8
 * @return	str		Gateway ID
 */
function mdjm_get_default_gateway() {
	$default = mdjm_get_option( 'payment_gateway', 'disabled' );

	if( ! mdjm_is_gateway_active( $default ) ) {
		$gateways = mdjm_get_enabled_payment_gateways();
		$gateways = array_keys( $gateways );
		$default  = reset( $gateways );
	}

	return apply_filters( 'mdjm_default_gateway', $default );
} // mdjm_get_default_gateway

/**
 * Returns the admin label for the specified gateway
 *
 * @since	1.3.8
 * @param	str		$gateway	Name of the gateway to retrieve a label for
 * @return	str		Gateway admin label
 */
function mdjm_get_gateway_admin_label( $gateway ) {
	$gateways = mdjm_get_payment_gateways();
	$label    = isset( $gateways[ $gateway ] ) ? $gateways[ $gateway ]['admin_label'] : $gateway;
	$payment  = isset( $_GET['id'] ) ? absint( $_GET['id'] ) : false;

	return apply_filters( 'mdjm_gateway_admin_label', $label, $gateway );
} // mdjm_get_gateway_admin_label

/**
 * Returns the payment label for the specified gateway
 *
 * @since	1.3.8
 * @param	str		$gateway	Name of the gateway to retrieve a label for
 * @return	str		Checkout label for the gateway
 */
function mdjm_get_gateway_payment_label( $gateway ) {
	$gateways = mdjm_get_payment_gateways();
	$label    = isset( $gateways[ $gateway ] ) ? $gateways[ $gateway ]['payment_label'] : $gateway;

	return apply_filters( 'mdjm_gateway_payment_label', $label, $gateway );
} // mdjm_get_gateway_payment_label

/**
 * Determines what the currently selected gateway is
 *
 * @since	1.3.8
 * @return	str		$enabled_gateway	The slug of the gateway
 */
function mdjm_get_chosen_gateway() {
	$gateways = mdjm_get_enabled_payment_gateways();
	$chosen   = isset( $_REQUEST['payment-mode'] ) ? $_REQUEST['payment-mode'] : false;

	if ( false !== $chosen ) {
		$chosen = preg_replace('/[^a-zA-Z0-9-_]+/', '', $chosen );
	}

	if ( ! empty ( $chosen ) ) {
		$enabled_gateway = urldecode( $chosen );
	} elseif( count( $gateways ) >= 1 && ! $chosen ) {
		foreach ( $gateways as $gateway_id => $gateway )	{
			$enabled_gateway = $gateway_id;
		}
	} else {
		$enabled_gateway = mdjm_get_default_gateway();
	}

	return apply_filters( 'mdjm_chosen_gateway', $enabled_gateway );
} // mdjm_get_chosen_gateway

/**
 * Sends all the payment data to the specified gateway
 *
 * @since	1.3.8
 * @param	str		$gateway		Name of the gateway
 * @param	arr		$payment_data	All the payment data to be sent to the gateway
 * @return void
*/
function mdjm_send_to_gateway( $gateway, $payment_data ) {

	$payment_data['gateway_nonce'] = wp_create_nonce( 'mdjm-gateway' );

	// $gateway must match the ID used when registering the gateway
	do_action( 'mdjm_gateway_' . $gateway, $payment_data );
} // mdjm_send_to_gateway

/**
 * Determines if the gateway menu should be shown
 *
 * @since	1.3.8
 * @return	bool	$show_gateways	Whether or not to show the gateways
 */
function mdjm_show_gateways() {
	$gateways = mdjm_get_enabled_payment_gateways();
	$show_gateways = false;

	$chosen_gateway = isset( $_GET['payment-mode'] ) ? preg_replace('/[^a-zA-Z0-9-_]+/', '', $_GET['payment-mode'] ) : false;

	if ( count( $gateways ) > 1 && empty( $chosen_gateway ) ) {
		$show_gateways = true;
	}

	return apply_filters( 'mdjm_show_gateways', $show_gateways );
} // mdjm_show_gateways

/**
 * Returns the text for the payment button.
 *
 * @since	1.3.8
 * @return	str		Button text
 */
function mdjm_get_payment_button_text()	{
	$button_text = mdjm_get_option( 'payment_button', __( 'Pay Now', 'mobile-dj-manager' ) );

	$button_text = esc_attr( apply_filters( 'mdjm_get_payment_button_text', $button_text ) );

	return $button_text;

} // mdjm_get_payment_button_text

/**
 * Generates a transaction for a new payment during processing.
 *
 * The transaction status will be set to Pending.
 * Payment gateways should update this txn once payment is verified.
 *
 * @since	1.3.8
 * @param	arr		$payment_data	Array of data collected from payment form validation.
 * @return	int		Transaction ID	ID of the newly created transaction.
 */
function mdjm_create_payment_txn( $payment_data )	{

	$gateway_label = mdjm_get_gateway_admin_label( $payment_data['gateway'] );

	do_action( 'mdjm_create_payment_before_txn', $payment_data );

	$mdjm_txn = new MDJM_Txn();
	
	$mdjm_txn->create(
		array(
			'post_title'  => sprintf( __( '%s payment for %s', 'mdjm-stripe-payments' ), $gateway_label, $event_id ),
			'post_status' => 'mdjm-income',
			'post_author' => 1,
			'post_parent' => $payment_data['event_id']
		),
		array(
			'_mdjm_txn_source'      => $gateway_label,
			'_mdjm_txn_status'      => 'Pending',
			'_mdjm_payment_from'    => $payment_data['client_id'],
			'_mdjm_txn_total'       => $payment_data['total'],
			'_mdjm_payer_firstname' => $payment_data['client_data']['first_name'],
			'_mdjm_payer_lastname'  => $payment_data['client_data']['last_name'],
			'_mdjm_payer_email'     => $payment_data['client_data']['email'],
			'_mdjm_payer_ip'        => $payment_data['client_ip'],
			'_mdjm_payment_from'    => $payment_data['client_data']['display_name']
		)
	);

	mdjm_set_txn_type( $payment_data['event_id'], mdjm_get_txn_cat_id( 'name', $payment_data['type'] ) );

	do_action( 'mdjm_create_payment_after_txn', $mdjm_txn->ID, $payment_data );

	return $mdjm_txn->ID;

} // mdjm_create_payment_txn
