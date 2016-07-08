<?php

/**
 * Run the update procedures.
 *
 * @version	1.3.8
 * @param
 * @return.
 */
function mdjm_run_update_138()	{

	$payment_label = __( 'Pay Now', 'mobile-dj-manager' );
	$gateway       = mdjm_get_option( 'payment_gateway', false );

	if ( ! empty( $gateway ) )	{
		if ( $gateway == 'paypal' )	{
			$button_text = mdjm_get_option( 'mdjm_pg_paypal_button_text' );
		}
		if ( $gateway == 'payfast' )	{
			$button_text = mdjm_get_option( 'mdjm_pg_payfast_button_text' );
		}

		if ( ! empty( $button_text ) )	{
			$payment_label = sanitize_text_field( $button_text );
		}
	}

	mdjm_delete_option( 'payment_gateway' );

	mdjm_update_option( 'payment_gateway', $gateway );
	mdjm_update_option( 'gateways', array( $gateway => '1' ) );
	mdjm_update_option( 'payment_button', $payment_label );

} // mdjm_run_update_138
add_action( 'init', 'mdjm_run_update_138', 15 );

