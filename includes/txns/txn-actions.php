<?php
/**
 * Contains all transaction related functions called via actions
 *
 * @package		MDJM
 * @subpackage	Transactions
 * @since		1.3
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) )
	exit;

/**
 * Redirect to payments.
 *
 * @since	1.3
 * @param
 * @return	void
 */
function mdjm_goto_payments_action( $data )	{
	if( ! isset( $data['event_id'] ) )	{
		return;
	}
	
	if( ! mdjm_event_exists( $data['event_id'] ) )	{
		wp_die( 'Sorry but no event exists', 'mobile-dj-manager' );
	}
	
	wp_redirect( 
		add_query_arg( 'event_id', $data['event_id'], 
		mdjm_get_formatted_url( mdjm_get_option( 'payments_page' ) ) )
	);
	die();
} // mdjm_goto_guest_playlist
add_action( 'mdjm_goto_payments', 'mdjm_goto_payments_action' );
	
/**
 * Pay event employees.
 *
 * @since	1.3
 * @param	arr		$data	Form data from the $_GET super global.
 * @return	void
 */
function mdjm_pay_event_employees_action( $data )	{

	if( ! wp_verify_nonce( $data[ 'mdjm_nonce' ], 'pay_event_employees' ) )	{
		$message = 'nonce_fail';
	}
	
	elseif( ! isset( $data[ 'event_id' ] ) )	{
		$message = 'payment_event_missing';
	} else	{
		// Process the payment action
		$employee_id = ! empty( $data['employee_id'] ) ? $data['employee_id'] : 0;
		$event_id    = $data[ 'event_id' ];
		
		$payments = mdjm_pay_event_employees( $event_id, $employee_id );
		
		if ( ! empty( $employee_id ) && $payments )	{
			$message = 'pay_employee_success';
		} elseif( ! empty( $employee_id ) && ! $payments )	{
			$message = 'pay_employee_failed';
		} elseif( empty( $employee_id ) && ! empty( $payments['success'] ) && empty( $payments['failed'] ) )	{
			$message = 'pay_all_employees_success';
		} elseif( empty( $employee_id ) && ! empty( $payments['success'] ) && ! empty( $payments['failed'] ) )	{
			$message = 'pay_all_employees_some_success';
		} elseif( empty( $employee_id ) && empty( $payments['success'] ) && ! empty( $payments['failed'] ) )	{
			$message = 'pay_all_employees_failed';
		}
		
	}
	
	$url = remove_query_arg( array( 'mdjm_nonce', 'mdjm-action', 'employee_id', 'mdjm-message', 'event_id' ), wp_get_referer() );
	
	wp_redirect(
		add_query_arg(
			array(
				'mdjm-message' => $message
			),
			$url
		)
	);

	die();

} // mdjm_pay_event_employees_action
add_action( 'mdjm-pay_event_employees', 'mdjm_pay_event_employees_action' );
