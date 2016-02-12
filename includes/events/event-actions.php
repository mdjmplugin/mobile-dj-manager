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
function mdjm_goto_event_action()	{
	if( ! isset( $_GET['event_id'] ) )	{
		return;
	}
	
	if( ! mdjm_event_exists( $_GET['event_id'] ) )	{
		wp_die( 'Sorry but no event exists', 'mobile-dj-manager' );
	}
	
	wp_redirect( mdjm_get_event_uri( $_GET['event_id'] ) );
	die();
} // mdjm_goto_event
add_action( 'mdjm_goto_event', 'mdjm_goto_event_action' );