<?php
/**
 * Process event actions
 *
 * @package		MDJM
 * @subpackage	Events
 * @since		1.3
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) )
	exit;

/**
 * Redirect to event.
 *
 * @since	1.3
 * @param
 * @return	void
 */
function mdjm_goto_event_action( $data )	{
	
	if( ! isset( $data['event_id'] ) )	{
		return;
	}
	
	if( ! mdjm_event_exists( $data['event_id'] ) )	{
		wp_die( 'Sorry but no event exists', 'mobile-dj-manager' );
	}
	
	wp_redirect( mdjm_get_event_uri( $data['event_id'] ) );
	die();
	
} // mdjm_goto_event
add_action( 'mdjm_goto_event', 'mdjm_goto_event_action' );

/**
 * Accept an enquiry from the Client Zone.
 *
 * @since	1.3
 * @param	arr		$data	Passed from the super global $_POST
 * @return	void
 */
function mdjm_event_accept_enquiry_action( $data )	{
	
	if( ! wp_verify_nonce( $data[ 'mdjm_nonce' ], 'accept_enquiry' ) )	{
		
		$message = 'nonce_fail';
		
	} elseif( ! isset( $data[ 'event_id' ] ) )	{
		
		$message = 'missing_event';
		
	} else	{
		
		if( mdjm_accept_enquiry( $data ) )	{
			$message = 'enquiry_accepted';
		} else	{
			$message = 'enquiry_accept_fail';
		}
		
	}
	
	wp_redirect( add_query_arg( 'mdjm_message', $message, mdjm_get_event_uri( $data['event_id'] ) ) );
	
	die();
	
} // mdjm_event_accept_enquiry_action
add_action( 'mdjm_accept_enquiry', 'mdjm_event_accept_enquiry_action' );