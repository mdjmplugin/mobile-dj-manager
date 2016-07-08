<?php

/**
 * Process payments.
 *
 * @package		MDJM
 * @subpackage	Functions
 * @since		1.3.8
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) )
	exit;

/**
 * Process Payment Form
 *
 * Handles the payment form process.
 *
 * @since       1.3.8
 * @return      void
 */
function mdjm_process_payment_form() {

	do_action( 'mdjm_pre_process_payment' );

	// Validate the form $_POST data
	$valid_data = mdjm_payment_form_validate_fields();

	// Allow themes and plugins to hook to errors
	do_action( 'mdjm_payment_error_checks', $valid_data, $_POST );

	// Setup purchase information
	$payment_data = array(
		'event_id'     => $valid_data['event_id'],
		'client_id'    => $valid_data['client_id'],
		'client_data'  => $valid_data['client_data'],
		'type'         => $valid_data['type'],
		'total'        => $valid_data['total'],
		'date'         => date( 'Y-m-d H:i:s', current_time( 'timestamp' ) ),
		'post_data'    => $_POST,
		'gateway'      => $valid_data['gateway'],
		'card_info'    => $valid_data['cc_info'],
		'ip'           => $valid_data['client_ip']
	);

	// Allow themes and plugins to hook before the gateway
	do_action( 'mdjm_checkout_before_gateway', $_POST, $payment_data );

	// Allow the payment data to be modified before a transaction is created
	$payment_data = apply_filters(
		'mdjm_payment_data_before_txn',
		$payment_data,
		$valid_data
	);

	$txn_id = mdjm_create_payment_txn( $payment_data );

	if ( ! empty( $txn_id ) )	{
		$payment_data['txn_id'] = $txn_id;
	}

	// Allow the payment data to be modified before it is sent to gateway
	$payment_data = apply_filters(
		'mdjm_payment_data_before_gateway',
		$payment_data,
		$valid_data
	);

	// Send info to the gateway for payment processing
	mdjm_send_to_gateway( $payment_data['gateway'], $payment_data );
	die();
} // mdjm_process_payment_form
add_action( 'mdjm_event_payment', 'mdjm_process_payment_form' );
add_action( 'wp_ajax_mdjm_event_payment', 'mdjm_process_payment_form' );
add_action( 'wp_ajax_nopriv_mdjm_event_payment', 'mdjm_process_payment_form' );

/**
 * Payment Form Validate Fields
 *
 * @since	1.3.8
 * @return	bool|arr
 */
function mdjm_payment_form_validate_fields()	{
	// Check if there is $_POST
	if ( empty( $_POST ) )	{
		return false;
	}

	$event_id = $_POST['event_id'];

	if ( empty( $event_id ) )	{
		return false;
	}

	$client_data = mdjm_get_payment_form_client( mdjm_get_event_client_id( $event_id ) );

	// Start an array to collect valid data
	$valid_data = array(
		'event_id'    => $event_id,
		'client_id'   => $client_data['id'],
		'client_data' => $client_data,
		'client_ip'   => mdjm_get_user_ip(),
		'type'        => mdjm_get_payment_type(),
		'total'       => mdjm_get_payment_total(),
		'gateway'     => mdjm_payment_form_validate_gateway(),
		'cc_info'     => mdjm_get_purchase_cc_info()
	);

	// Return collected data
	return $valid_data;
} // mdjm_payment_form_validate_fields

function mdjm_get_payment_form_client( $client_id = 0 )	{
	if ( empty( $client_id ) )	{
		return false;
	}

	$client_data = array();

	$client = get_userdata( $client_id );

	if ( $client )	{

		$client_data['id']            = isset( $client->ID )            ? $client->ID                       : '';
		$client_data['first_name']    = isset( $client->first_name )    ? ucfirst( $client->first_name )    : '';
		$client_data['last_name']     = isset( $client->last_name )     ? ucfirst( $client->last_name )     : '';
		$client_data['display_name']  = isset( $client->display_name )  ? ucwords( $client->display_name )  : '';
		$client_data['email']         = isset( $client->user_email )    ? strtolower( $client->user_email ) : '';

	}

	/**
	 * Allow gateway extensions to filter the client data.
	 *
	 * @since	1.3.8
	 */

	$client_data = apply_filters( 'mdjm_get_payment_form_client', $client_data, $client_id );

	return $client_data;

} // mdjm_get_payment_form_client

/**
 * Payment Form Validate Gateway
 *
 * @since	1.3.8
 * @return	string
 */
function mdjm_payment_form_validate_gateway() {
	$gateway = mdjm_get_default_gateway();

	// Check if a gateway value is present
	if ( ! empty( $_REQUEST['mdjm_gateway'] ) ) {
		$gateway = sanitize_text_field( $_REQUEST['mdjm_gateway'] );
	}

	return $gateway;
} // mdjm_payment_form_validate_gateway

/**
 * Payment form payment type.
 *
 * @since	1.3.8
 * @return	str
 */
function mdjm_get_payment_type()	{
	$type     = $_POST['mdjm_payment_amount'];

	if ( 'deposit' == $type )	{
		$type  = mdjm_get_deposit_label();
	} elseif( 'balance' == $type )	{
		$type  = mdjm_get_balance_label();
	} else	{
		$type  = mdjm_get_other_amount_label();
	}

	return $type;

} // mdjm_get_payment_type

/**
 * Payment form total price.
 *
 * @since	1.3.8
 * @return	str
 */
function mdjm_get_payment_total()	{
	$event_id = $_POST['event_id'];
	$type     = $_POST['mdjm_payment_amount'];
	$total    = false;

	$mdjm_event = new MDJM_Event( $event_id );

	if ( 'deposit' == $type )	{
		$type  = mdjm_get_deposit_label();
		$total = $mdjm_event->get_remaining_deposit();
	} elseif( 'balance' == $type )	{
		$type  = mdjm_get_balance_label();
		$total = $mdjm_event->get_balance();
	} else	{
		$type  = mdjm_get_other_amount_label();
		$total = ! empty( $_POST['part_payment'] ) ? $_POST['part_payment'] : false;
	}

	return $total;

} // mdjm_get_payment_total

/**
 * Get Credit Card Info
 *
 * @since	1.3.8
 * @return	arr
 */
function mdjm_get_purchase_cc_info() {
	$cc_info = array();
	$cc_info['card_name']      = isset( $_POST['card_name'] )       ? sanitize_text_field( $_POST['card_name'] )       : '';
	$cc_info['card_number']    = isset( $_POST['card_number'] )     ? sanitize_text_field( $_POST['card_number'] )     : '';
	$cc_info['card_cvc']       = isset( $_POST['card_cvc'] )        ? sanitize_text_field( $_POST['card_cvc'] )        : '';
	$cc_info['card_exp_month'] = isset( $_POST['card_exp_month'] )  ? sanitize_text_field( $_POST['card_exp_month'] )  : '';
	$cc_info['card_exp_year']  = isset( $_POST['card_exp_year'] )   ? sanitize_text_field( $_POST['card_exp_year'] )   : '';
	$cc_info['card_address']   = isset( $_POST['card_address'] )    ? sanitize_text_field( $_POST['card_address'] )    : '';
	$cc_info['card_address_2'] = isset( $_POST['card_address_2'] )  ? sanitize_text_field( $_POST['card_address_2'] )  : '';
	$cc_info['card_city']      = isset( $_POST['card_city'] )       ? sanitize_text_field( $_POST['card_city'] )       : '';
	$cc_info['card_state']     = isset( $_POST['card_state'] )      ? sanitize_text_field( $_POST['card_state'] )      : '';
	$cc_info['card_country']   = isset( $_POST['billing_country'] ) ? sanitize_text_field( $_POST['billing_country'] ) : '';
	$cc_info['card_zip']       = isset( $_POST['card_zip'] )        ? sanitize_text_field( $_POST['card_zip'] )        : '';

	return $cc_info;
} // mdjm_get_purchase_cc_info
