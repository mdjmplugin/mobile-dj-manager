<?php

/**
 * Perform actions related to contracts as received by $_GET and $_POST super globals.
 *
 * @package		MDJM
 * @subpackage	Contracts
 * @since		1.3
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) )
	exit;

/**
 * Sign the contract.
 *
 * @since	1.3
 * @param
 * @return
 */
function mdjm_sign_event_contract_action( $data )	{	
	if( ! wp_verify_nonce( $data[ 'mdjm_nonce' ], 'sign_contract' ) )	{
		$message = 99;
	}
	
	else	{
		// Setup the signed contract details
		$posted = array();
	
		foreach ( $data as $key => $value ) {
			if ( $key != 'mdjm_nonce' && $key != 'mdjm_action' && $key != 'mdjm_redirect' && $key != 'mdjm_submit_sign_contract' ) {
				// All fields are required
				if( empty( $value ) )	{
					wp_redirect(
						add_query_arg(
							array(
								'event_id'	 => $data['event_id'],
								'mdjm_message' => 12
							),
							mdjm_get_formatted_url( mdjm_get_option( 'contract_page' ) )
						)
					);
					die();
				}
				elseif ( is_string( $value ) || is_int( $value ) ) {
					$posted[ $key ] = strip_tags( addslashes( $value ) );
				}
				elseif ( is_array( $value ) ) {
					$posted[ $key ] = array_map( 'absint', $value );
				}
			}
		}
		
		if( mdjm_sign_event_contract( $posted ) )	{
			$message = 10;
		}
		else	{
			$message = 11;
		}
	}
	
	wp_redirect(
		add_query_arg(
			array(
				'event_id'	 => $data['event_id'],
				'mdjm_message' => $message
			),
			mdjm_get_formatted_url( mdjm_get_option( 'contract_page' ) )
		)
	);
	die();
	
}
add_action( 'mdjm_sign_event_contract', 'mdjm_sign_event_contract_action' );