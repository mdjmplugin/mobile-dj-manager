<?php
/**
 * @author: Mike Howard, Jack Mawhinney, Dan Porter
 *
 * Perform actions related to timeline as received by $_GET and $_POST super globals.
 *
 * @package     MDJM
 * @subpackage  Contracts
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Redirect to timeline.
 *
 * @since   1.3
 * @param
 * @return  void
 */
function mdjm_goto_timeline_action() {
	if ( ! isset( $_GET['event_id'] ) ) {
		return;
	}

	if ( ! mdjm_event_exists( absint( wp_unslash( $_GET['event_id'] ) ) ) ) {
		wp_die( 'Sorry but we could not locate your event.', 'mobile-dj-manager' );
	}

	wp_safe_redirect(
		add_query_arg(
			'event_id',
			absint( wp_unslash( $_GET['event_id'] ) ),
			mdjm_get_formatted_url( mdjm_get_option( 'timeline_page' ) )
		)
	);
	exit;
} // mdjm_goto_timeline_action
add_action( 'mdjm_goto_timeline', 'mdjm_goto_timeline_action' ); 