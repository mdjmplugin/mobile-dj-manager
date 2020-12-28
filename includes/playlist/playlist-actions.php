<?php
/**
 * Contains all playlist related functions called via actions executed on the front end
 *
 * @package     MDJM
 * @subpackage  Playlists
 * @since       1.3
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Redirect to playlist.
 *
 * @since   1.3
 * @param
 * @return  void
 */
function mdjm_goto_playlist_action( $data ) {
	if ( ! isset( $data['event_id'] ) ) {
		return;
	}

	if ( ! mdjm_event_exists( $data['event_id'] ) ) {
		wp_die( 'Sorry but no event exists', 'mobile-dj-manager' );
	}

	wp_safe_redirect(
		add_query_arg( 'event_id', $data['event_id'],
		mdjm_get_formatted_url( mdjm_get_option( 'playlist_page' ) ) )
	);
	exit;
} // mdjm_goto_guest_playlist
add_action( 'mdjm_goto_playlist', 'mdjm_goto_playlist_action' );

/**
 * Redirect to guest playlist.
 *
 * @since   1.3
 * @param
 * @return  void
 */
function mdjm_goto_guest_playlist_action( $data ) {
	if ( ! isset( $data['playlist'] ) ) {
		return;
	}

	$event = mdjm_get_event_by_playlist_code( $data['playlist'] );

	if ( ! $event ) {
		wp_die( 'Sorry but no event exists', 'mobile-dj-manager' );
	}

	wp_safe_redirect(
		add_query_arg( 'guest_playlist', $data['playlist'],
		mdjm_get_formatted_url( mdjm_get_option( 'playlist_page' ) ) )
	);
	exit;
} // mdjm_goto_guest_playlist
add_action( 'mdjm_goto_guest_playlist', 'mdjm_goto_guest_playlist_action' );

/**
 * Redirect to playlist.
 *
 * Catches incorrect redirects and forwards to the playlist page.
 * @see https://github.com/mdjm/mobile-dj-manager/issues/101
 *
 * @since   1.3.7
 * @param
 * @return  void
 */
function mdjm_correct_guest_playlist_url_action() {
	if ( ! isset( $_GET['guest_playlist'] ) ) {
		return;
	}

	if ( ! is_page( mdjm_get_option( 'playlist_page' ) ) ) {
		wp_safe_redirect(
			add_query_arg( 'guest_playlist', sanitize_text_field( wp_unslash( $_GET['guest_playlist'] ) ),
			mdjm_get_formatted_url( mdjm_get_option( 'playlist_page' ) ) )
		);
		exit;
	}
} // mdjm_correct_guest_playlist_url_action
add_action( 'template_redirect', 'mdjm_correct_guest_playlist_url_action' );

/**
 * Sets the flag to notify clients when a guest entry is added
 *
 * @since   1.5
 * @param   int     $entry_id   The playlist entry ID
 * @param   int     $event_id   The event ID
 * @return  void
 */
function mdjm_event_playlist_set_guest_notification_action( $entry_id, $event_id ) {
    if ( mdjm_is_task_active( 'playlist-notification' ) ) {
        update_post_meta( $event_id, '_mdjm_playlist_client_notify', '1', true );
    }
} // mdjm_event_playlist_set_guest_notification_action
add_action( 'mdjm_insert_guest_playlist_entry', 'mdjm_event_playlist_set_guest_notification_action', 10, 2 );
