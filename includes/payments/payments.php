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
 * Whether or not there is a gateway.
 *
 * @since	1.3.8
 * @param
 * @return	bool	True if there is a gateway, otherwise false.
 */
function mdjm_has_gateway()	{

	$enabled_gateways = mdjm_get_enabled_payment_gateways();

	if ( ! empty( $enabled_gateways ) && count( $enabled_gateways ) >= 1 )	{
		return true;
	}

	return false;

} // mdjm_has_gateway

/**
 * Removes gateway receipt email setting if no gateways are enabled.
 *
 * @since	1.3.8
 * @param	$mdjm_settings	arr		MDJM Settings array.
 * @return	$mdjm_settings	arr		MDJM Settings array.
 */
function mdjm_filter_gateway_receipt_setting( $mdjm_settings )	{

	// Remove gateway receipt template if no gateway is enabled.
	$enabled_gateways = mdjm_get_enabled_payment_gateways();

	if ( empty( $enabled_gateways ) || count( $enabled_gateways ) < 1 )	{
		unset( $mdjm_settings['payments']['receipts']['payment_cfm_template'] );
	}

	return $mdjm_settings;

} // mdjm_filter_gateway_receipt_setting
add_filter( 'mdjm_registered_settings', 'mdjm_filter_gateway_receipt_setting' );

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

	$gateway_label = mdjm_get_gateway_payment_label( $payment_data['gateway'] );
	$event_id      = $payment_data['event_id'];

	do_action( 'mdjm_create_payment_before_txn', $payment_data );

	$mdjm_txn = new MDJM_Txn();
	
	$mdjm_txn->create(
		array(
			'post_title'  => sprintf( __( '%s payment for %s', 'mobile-dj-manager' ), $gateway_label, $event_id ),
			'post_status' => 'mdjm-income',
			'post_author' => 1,
			'post_parent' => $event_id
		),
		array(
			'_mdjm_txn_source'      => $gateway_label,
			'_mdjm_txn_gateway'     => $payment_data['gateway'],
			'_mdjm_txn_status'      => 'Pending',
			'_mdjm_payment_from'    => $payment_data['client_id'],
			'_mdjm_txn_total'       => $payment_data['total'],
			'_mdjm_payer_firstname' => $payment_data['client_data']['first_name'],
			'_mdjm_payer_lastname'  => $payment_data['client_data']['last_name'],
			'_mdjm_payer_email'     => $payment_data['client_data']['email'],
			'_mdjm_payer_ip'        => $payment_data['ip'],
			'_mdjm_payment_from'    => $payment_data['client_data']['display_name']
		)
	);

	mdjm_set_txn_type( $mdjm_txn->ID, mdjm_get_txn_cat_id( 'name', $payment_data['type'] ) );

	do_action( 'mdjm_create_payment_after_txn', $mdjm_txn->ID, $payment_data );

	return $mdjm_txn->ID;

} // mdjm_create_payment_txn

/**
 * Completes the transaction record for the event payment
 * using data provided within the gateway response.
 *
 * @since	1.3.8
 * @param	$gateway_data	arr		Transaction data from gateway.
 * @return	void
 */
function mdjm_update_payment_from_gateway( $gateway_data )	{

	$txn_data   = apply_filters(
		'mdjm_update_gateway_payment_data',
		array(
			'ID'            => $gateway_data['txn_id'],
			'post_title'    => mdjm_get_option( 'event_prefix' ) . $gateway_data['txn_id'],
			'post_name'     => mdjm_get_option( 'event_prefix' ) . $gateway_data['txn_id'],
			'post_status'   => 'mdjm-income',
			'post_date'     => $gateway_data['date'],
			'edit_date'     => true,
			'post_author'   => mdjm_get_event_client_id( $gateway_data['event_id'] ),
			'post_type'     => 'mdjm-transaction',
			'post_parent'   => $gateway_data['event_id'],
			'post_modified' => current_time( 'mysql' )
		)
	);

	$txn_meta = apply_filters(
		'mdjm_update_gateway_payment_meta',
		array(
			'_mdjm_txn_status'      => $gateway_data['status'],
			'_mdjm_txn_gw_id'       => $gateway_data['gw_id'],
			'_mdjm_txn_currency'	=> $gateway_data['currency'],
			'_mdjm_txn_gw_response' => $gateway_data['data'],
			'_mdjm_txn_net'         => isset( $gateway_data['fee'] )             ? $gateway_data['total'] - $gateway_data['fee'] : '0.00',
			'_mdjm_txn_fee'         => isset( $gateway_data['fee'] )             ? $gateway_data['fee']                          : '0.00',
			'_mdjm_txn_gw_message'  => isset( $gateway_data['message'] )         ? $gateway_data['message']                      : '',
			'_mdjm_txn_card_type'   => isset( $gateway_data['card_type'] )       ? $gateway_data['card_type']                    : '',
			'_mdjm_txn_env'         => isset( $gateway_data['live'] )            ? $gateway_data['live']                         : '',
			'_mdjm_txn_gw_invoice'  => isset( $gateway_data['gw_invoice'] )      ? $gateway_data['gw_invoice']                   : '',
			'_mdjm_txn_gw_billing'  => isset( $gateway_data['billing_address'] ) ? $gateway_data['billing_address']              : ''
		)
	);

	remove_action( 'save_post_mdjm-transaction', 'mdjm_save_txn_post', 10, 3 );

	do_action( 'mdjm_before_update_payment_from_gateway', $gateway_data, $txn_data, $txn_meta );

	wp_update_post( $txn_data );

	mdjm_update_txn_meta( $gateway_data['txn_id'], $txn_meta );

	do_action( 'mdjm_after_update_payment_from_gateway', $gateway_data, $txn_data, $txn_meta );

	add_action( 'save_post_mdjm-transaction', 'mdjm_save_txn_post', 10, 3 );

} // mdjm_complete_event_txn_payment
add_action( 'mdjm_complete_event_payment_txn', 'mdjm_update_payment_from_gateway' );

/**
 * Records the merchant fee transaction.
 *
 * @since	1.0
 * @param	arr		$gateway_data	Transaction data received from the gateway.
 * @return	void
 */
function mdjm_create_merchant_fee_txn( $gateway_data )	{

	if ( isset( $gateway_data['gateway'] ) )	{
		$gateway = mdjm_get_gateway_payment_label( $gateway_data['gateway'] );
	} else	{
		$gateway = mdjm_get_gateway_payment_label( mdjm_get_default_gateway() );
	}

	if ( ! isset( $gateway_data['fee'] ) || $gateway_data['fee'] < '0.01' )	{
		return;
	}

	$txn_data = apply_filters(
		'mdjm_merchant_fee_transaction_data',
		array(
			'post_author' => mdjm_get_event_client_id( $gateway_data['event_id'] ),
			'post_type'   => 'mdjm-transaction',
			'post_title'  => sprintf( __( '%s Merchant Fee for Transaction %s', 'mobile-dj-manager' ),
				$gateway,
				$gateway_data['txn_id']
			),
			'post_status' => 'mdjm-expenditure',
			'post_parent' => $gateway_data['event_id']
		)
	);
	
	$txn_meta = apply_filters(
		'mdjm_merchant_fee_transaction_meta',
		array(
			'_mdjm_txn_status'   => 'Completed',
			'_mdjm_txn_source'   => $gateway,
			'_mdjm_txn_currency' => $gateway_data['currency'],
			'_mdjm_txn_total'    => $gateway_data['fee'],
			'_mdjm_payment_to'   => $gateway
		)
	);

	do_action( 'mdjm_before_create_merchant_fee', $gateway_data, $txn_data, $txn_meta );

	$mdjm_txn = new MDJM_Txn();

	$mdjm_txn->create(
		$txn_data,
		$txn_meta
	);
	
	$merchant_fee_id = $mdjm_txn->ID;
	
	if ( ! empty( $merchant_fee_id ) )	{
		mdjm_set_txn_type( $mdjm_txn->ID, mdjm_get_txn_cat_id( 'slug', 'mdjm-merchant-fees' ) );

		// Update the incoming transaction meta to include the merchant txn ID.
		mdjm_update_txn_meta( $gateway_data['txn_id'], array( '_mdjm_merchant_fee_txn_id' => $merchant_fee_id ) );
	}

	do_action( 'mdjm_after_create_merchant_fee', $merchant_fee_id, $gateway_data );

} // mdjm_create_merchant_fee_txn
add_action ( 'mdjm_after_update_payment_from_gateway', 'mdjm_create_merchant_fee_txn' );

/**
 * Completes an event payment process.
 *
 * @since	1.3.8
 * @param	arr		$txn_data	Transaction data.
 * @return	void
 */
function mdjm_complete_event_payment( $txn_data )	{

	// Allow filtering of the transaction data.
	$txn_data = apply_filters( 'mdjm_complete_event_payment_data', $txn_data );

	$event_id = $txn_data['event_id'];

	// Allow actions before we update
	do_action( 'mdjm_before_complete_event_payment', $txn_data );

	// The transaction updates are hooked into this
	do_action( 'mdjm_complete_event_payment_txn', $txn_data );

	do_action( 'mdjm_before_send_gateway_receipt', $txn_data );

	if ( isset( $txn_data['gateway'] ) && has_action( 'mdjm_send_' . $txn_data['gateway'] . '_gateway_receipt' ) ) {
		do_action( 'mdjm_send_' . $txn_data['gateway'] . '_gateway_receipt', $txn_data['event_id'] );
	} else {
		do_action( 'mdjm_send_gateway_receipt', $txn_data['event_id'] );
	}

	do_action( 'mdjm_after_send_gateway_receipt', $txn_data );

	do_action( 'mdjm_after_complete_event_payment', $txn_data );

} // mdjm_complete_event_payment

/**
 * Register the {payment_for} content tag for use within receipt emails.
 *
 * @since	1.3.8
 * @param	obj		$mdjm_txn		The transaction object.
 * @return	void
 */
function mdjm_register_payment_for_content_tag( $txn_data )	{

	$txn_id = $txn_data['txn_id'];

	$type = mdjm_get_txn_type( $txn_id );

	if( $type == mdjm_get_deposit_label() )	{
		$payment_for = 'mdjm_content_tag_deposit_label';
	} elseif( $type == mdjm_get_balance_label() )	{
		$payment_for = 'mdjm_content_tag_balance_label';
	} else	{
		$payment_for = 'mdjm_content_tag_part_payment_label';
	}

	mdjm_add_content_tag( 'payment_for', __( 'Reason for payment', 'mobile-dj-manager' ), $payment_for );

} // mdjm_register_payment_for_content_tag
add_action( 'mdjm_before_send_gateway_receipt', 'mdjm_register_payment_for_content_tag' );

/**
 * Register the {payment_amount} content tag for use within receipt emails.
 *
 * @requires PHP version 5.4 due to use of anonymous functions.
 *
 * @since	1.3.8
 * @param	obj		$mdjm_txn		The transaction object.
 * @return	void
 */
function mdjm_register_payment_amount_content_tag( $txn_data )	{

	if ( version_compare( phpversion(), '5.4', '<' ) )	{
		return;
	}

	$txn_id = $txn_data['txn_id'];

	mdjm_add_content_tag( 'payment_amount', __( 'Payment amount', 'mobile-dj-manager' ), function() use ( $txn_id ) { return mdjm_currency_filter( mdjm_format_amount( mdjm_get_txn_price( $txn_id ) ) ); } );

} // mdjm_register_payment_amount_content_tag
add_action( 'mdjm_before_send_gateway_receipt', 'mdjm_register_payment_amount_content_tag' );

/**
 * Register the {payment_date} content tag for use within receipt emails.
 *
 * @requires PHP version 5.4 due to use of anonymous functions.
 *
 * @since	1.3.8
 * @param	obj		$mdjm_txn		The transaction object.
 * @return	void
 */
function mdjm_register_payment_date_content_tag( $txn_data )	{

	if ( version_compare( phpversion(), '5.4', '<' ) )	{
		return;
	}

	$txn_id = $txn_data['txn_id'];

	mdjm_add_content_tag( 'payment_date', __( 'Date of payment', 'mobile-dj-manager' ), function() use ( $txn_id ) { return mdjm_get_txn_date( $txn_id ); } );

} // mdjm_register_payment_date_content_tag
add_action( 'mdjm_before_send_gateway_receipt', 'mdjm_register_payment_date_content_tag' );

/**
 * Send admin notice of payment.
 *
 * @since	1.3.8
 * @param
 * @return	void
 */
function mdjm_admin_payment_notice( $txn_data )	{

	if ( isset( $txn_data['gateway'] ) )	{
		$gateway = mdjm_get_gateway_admin_label( $txn_data['gateway'] );
	} else	{
		$gateway = mdjm_get_gateway_admin_label( mdjm_get_default_gateway() );
	}

	$subject = sprintf( __( '%s Payment received via %s', 'mobile-dj-manager' ), mdjm_get_label_singular(), $gateway );
	$subject = apply_filters( 'mdjm_admin_payment_notice_subject', $subject );

	$content  = '<!DOCTYPE html>' . "\n";
	$content .= '<html>' . "\n" . '<body>' . "\n";
	$content .= '<p>' . __( 'Hi there', 'mobile-dj-manager' ) . ',</p>' . "\n";
	$content .= '<p>' . __( 'A payment has just been received via MDJM Event Management', 'mobile-dj-manager' ) . '</p>' . "\n";
	$content .= '<hr />' . "\n";
	$content .= '<h4>' . sprintf( __( '%s ID', 'mobile-dj-manager' ), mdjm_get_label_singular() ) . ': ' . mdjm_get_event_contract_id( $txn_data['event_id'] ) . '</a></h4>' . "\n";
	$content .= '<p>' . "\n";
	$content .= __( 'Date', 'mobile-dj-manager' ) . ': {event_date}<br />' . "\n";
			
	$content .= __( 'Status', 'mobile-dj-manager' ) . ': {event_status}<br />' . "\n";
	$content .= __( 'Client', 'mobile-dj-manager' ) . ': {client_fullname}<br />' . "\n";
	$content .= __( 'Payment Date', 'mobile-dj-manager' ) . ': {payment_date}<br />' . "\n";
																		
	$content .= __( 'For', 'mobile-dj-manager' ) . ': {payment_for}<br />' . "\n";
	$content .= __( 'Amount', 'mobile-dj-manager' ) . ': {payment_amount}<br />' . "\n";
	$content .= __( 'Merchant', 'mobile-dj-manager' ) . ': ' . $gateway . '<br />' . "\n";

	if( ! empty( $txn_data['fee'] ) )	{

		$content .= __( 'Transaction Fee', 'mobile-dj-manager' ) . ': ' . mdjm_currency_filter( mdjm_format_amount( $txn_data['fee'] ) ) . '</span><br />' . "\n";
		
		$content .= '<strong>' . __( 'Total Received', 'mobile-dj-manager' ) . ': ' . 
			mdjm_currency_filter( mdjm_format_amount( $txn_data['total'] - $txn_data['fee'] ) ) . '</strong><br />' . "\n";

	}
	
	$content .= __( 'Outstanding Balance', 'mobile-dj-manager' ) . ': {balance}</p>' . "\n";
	$content .= sprintf( __( '<a href="%s">View %s</a>', 'mobile-dj-manager' ), admin_url( 'post.php?post=' . $txn_data['event_id'] . '&action=edit' ), mdjm_get_label_singular() ) . '</p>' . "\n";
	
	$content .= '<hr />' . "\n";
	$content .= '<p>' . __( 'Regards', 'mobile-dj-manager' ) . '<br />' . "\n";
	$content .= '{company_name}</p>' . "\n";
	$content .= '</body>' . "\n";
	$content .= '</html>' . "\n";

	$content = apply_filters( 'mdjm_admin_payment_notice_content', $content );

	mdjm_send_email_content(
		array(
			'to_email'       => mdjm_get_option( 'system_email' ),
			'from_name'      => mdjm_get_option( 'company_name' ),
			'from_email'     => mdjm_get_option( 'system_email' ),
			'event_id'       => $txn_data['event_id'],
			'client_id'      => mdjm_get_event_client_id( $txn_data['event_id'] ),
			'subject'        => $subject,
			'message'        => $content,
			'copy_to'        => 'disable',
			'source'         => __( 'Automated Payment Received', 'mobile-dj-manager' )
		)
	);

} // mdjm_admin_payment_notice
add_action( 'mdjm_after_send_gateway_receipt', 'mdjm_admin_payment_notice' );

/**
 * Updates an event once a payment is completed.
 *
 * @since	1.3.8
 * @param	arr		$txn_data	Transaction data from gateway.
 * @return	void
 */
function mdjm_update_event_after_payment( $txn_data )	{

	$type = mdjm_get_txn_type( $txn_data['txn_id'] );

	if( $type == mdjm_get_deposit_label() )	{
		$meta['_mdjm_event_deposit_status'] = 'Paid';
	} else if( $type == mdjm_get_balance_label() )	{
		$meta['_mdjm_event_deposit_status'] = 'Paid';
		$meta['_mdjm_event_balance_status'] = 'Paid';
	} else	{
		if ( mdjm_get_event_remaining_deposit( $txn_data['event_id'] ) < 1 )	{
			$meta['_mdjm_event_deposit_status'] = 'Paid';
		}
		if ( mdjm_get_event_balance( $txn_data['event_id'] ) < 1 )	{
			$meta['_mdjm_event_deposit_status'] = 'Paid';
			$meta['_mdjm_event_balance_status'] = 'Paid';
		}
	}

	mdjm_update_event_meta( $txn_data['event_id'], $meta );
	
	// Update the journal
	mdjm_add_journal( 
		array(
			'user_id'         => $txn_data['client_id'],
			'event_id'        => $txn_data['event_id'],
			'comment_content' => sprintf( __( '%s of %s received via %s', 'mobile-dj-manager' ),
				$type,
				mdjm_currency_filter( mdjm_format_amount( $txn_data['total'] ) ),
				mdjm_get_gateway_admin_label( $txn_data['gateway'] )
			),
			'comment_type'    => 'mdjm-journal',
		)
	);

} // mdjm_update_event_after_payment
add_action( 'mdjm_after_update_payment_from_gateway', 'mdjm_update_event_after_payment', 11 );

/**
 * Write to the gateway log file.
 *
 * @since	1.3.8
 * @param	str		$msg		The message to be logged.
 * @param	bool	$stampit	True to log with date/time.
 * @return	void
 */
function mdjm_record_gateway_log( $msg, $stampit = false )	{
		
	$debug_log = $stampit == true ? date( 'd/m/Y  H:i:s', current_time( 'timestamp' ) ) . ' : ' . $msg : '    ' . $msg;
	
	error_log( $debug_log . "\r\n", 3, MDJM_PLUGIN_DIR, '/includes/payments/gateway-logs.log' );

} // mdjm_record_gateway_log

/**
 * Register the log file for core MDJM debugging class
 *
 * @since	1.0
 * @param	arr		$files		Log files.
 * @return	arr		$files		Filtered log files.
 */
function mdjm_payments_register_logs( $files )	{
	
	$files['MDJM Payment Gateways'] = array( MDJM_PLUGIN_DIR, '/includes/payments/gateway-logs.log' );
	
	return $files;
} // mdjm_payments_register_logs
add_filter( 'mdjm_log_files', 'mdjm_payments_register_logs' );
