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
 * Returns the checkout label for the specified gateway
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
