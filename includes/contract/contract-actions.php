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
 * Redirect to contract.
 *
 * @since	1.3
 * @param
 * @return	void
 */
function mdjm_goto_contract_action()	{
	if( ! isset( $_GET['event_id'] ) )	{
		return;
	}
	
	if( ! mdjm_event_exists( $_GET['event_id'] ) )	{
		wp_die( 'Sorry but we could not locate your event.', 'mobile-dj-manager' );
	}
	
	wp_redirect( 
		add_query_arg( 'event_id', $_GET['event_id'], 
		mdjm_get_formatted_url( mdjm_get_option( 'contracts_page' ) ) )
	);
	die();
} // mdjm_goto_contract_action
add_action( 'mdjm_goto_contract', 'mdjm_goto_contract_action' );

/**
 * Sign the contract.
 *
 * @since	1.3
 * @param
 * @return
 */
function mdjm_sign_event_contract_action( $data )	{
	// Check the password is correct
	$user = wp_get_current_user();
	
	$password_confirmation = wp_authenticate( $user->user_login, $data['mdjm_verify_password'] );

	$data['mdjm_accept_terms']   = ! empty( $data['mdjm_accept_terms'] )   ? $data['mdjm_accept_terms']   : false;
	$data['mdjm_confirm_client'] = ! empty( $data['mdjm_confirm_client'] ) ? $data['mdjm_confirm_client'] : false;

	if( is_wp_error( $password_confirmation ) )	{
		$message = 'password_error';
	} elseif( ! wp_verify_nonce( $data[ 'mdjm_nonce' ], 'sign_contract' ) )	{
		$message = 'nonce_fail';
	} else	{
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
								'mdjm_message' => 'contract_data_missing'
							),
							mdjm_get_formatted_url( mdjm_get_option( 'contracts_page' ) )
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
		
		if( mdjm_sign_event_contract( $data['event_id'], $posted ) )	{
			$message = 'contract_signed';
		}
		else	{
			$message = 'contract_not_signed';
		}
	}
	
	wp_redirect(
		add_query_arg(
			array(
				'event_id'	 => $data['event_id'],
				'mdjm_message' => $message
			),
			mdjm_get_formatted_url( mdjm_get_option( 'contracts_page' ) )
		)
	);
	die();
	
}
add_action( 'mdjm_sign_event_contract', 'mdjm_sign_event_contract_action' );

/**
 * Displays the signed contract for review.
 *
 * @since	1.3.6
 * @param	int		$event_id	The event ID.
 * @return	void
 */
function mdjm_review_signed_contract()	{

	if( empty( $_GET['mdjm_action'] ) ) {
		return;
	}

	if( 'review_contract' !== $_GET['mdjm_action'] ) {
		return;
	}

	if ( ! mdjm_employee_can( 'manage_events' ) )	{
		return;
	}

	$mdjm_event = new MDJM_Event( $_GET['event_id'] );

	if ( ! mdjm_is_admin() )	{
		if ( ! array_key_exists( get_current_user_id(), $mdjm_event->get_all_employees() ) )	{
			return;
		}
	}
	
	if ( ! $mdjm_event->get_contract_status() )	{
		printf( __( 'The contract for this %s is not signed', 'mobile-dj-manager' ), mdjm_get_label_singular() );
		exit;
	}

	$contract_id = $mdjm_event->get_contract();

	if ( empty( $contract_id ) )	{
		return;
	}

	echo mdjm_show_contract( $contract_id, $mdjm_event );

	exit;

} // mdjm_review_signed_contract
add_action( 'template_redirect', 'mdjm_review_signed_contract' );
