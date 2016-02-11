<?php
/**
 * Contains all playlist related functions called via actions executed on the front end
 *
 * @package		MDJM
 * @subpackage	Playlists
 * @since		1.3
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) )
	exit;

/**
 * Redirect to guest playlist.
 *
 * @since	1.3
 * @param
 * @return	void
 */
function mdjm_goto_guest_playlist_action()	{
	if( ! isset( $_GET['playlist'] ) )	{
		return;
	}
	
	$event = mdjm_get_event_by_playlist_code( $_GET['playlist'] );
	
	if( ! $event )	{
		wp_die( 'Sorry but no event exists', 'mobile-dj-manager' );
	}
	
	wp_redirect( 
		add_query_arg( 'guest_playlist', $_GET['playlist'], 
		mdjm_get_formatted_url( mdjm_get_option( 'playlist_page' ) ) )
	);
	die();
} // mdjm_goto_guest_playlist
add_action( 'mdjm_goto_guest_playlist', 'mdjm_goto_guest_playlist_action' );
	
/**
 * Add a song to the playlist.
 *
 * Add a new entry to the event playlist.
 *
 * @since	1.3
 * @param	arr		$data	Form data from the $_POST super global.
 * @return	void
 */
function mdjm_add_playlist_entry_action( $data )	{
	if( ! wp_verify_nonce( $data[ 'mdjm_nonce' ], 'add_playlist_entry' ) )	{
		$message = 99;
	}
	
	elseif( ! isset( $data[ 'mdjm_playlist_song' ], $data[ 'mdjm_playlist_artist' ] ) )	{
		$message = 22;
	}
	
	else	{
		// Setup the playlist entry details
		$posted = array();
	
		foreach ( $data as $key => $value ) {
	
			if ( $key != 'mdjm_nonce' && $key != 'mdjm_action' && $key != 'mdjm_redirect' && $key != 'entry_addnew' ) {
	
				if ( is_string( $value ) || is_int( $value ) ) {
	
					$posted[ $key ] = strip_tags( addslashes( $value ) );
	
				} elseif ( is_array( $value ) ) {
	
					$posted[ $key ] = array_map( 'absint', $value );
	
				}
			}
		}
		
		if( mdjm_store_playlist_entry( $posted ) )	{
			$message = 20;
		}
		else	{
			$message = 21;
		}
	}
	
	wp_redirect(
		add_query_arg(
			array(
				'event_id'	 => $data['entry_event'],
				'mdjm_message' => $message
			),
			mdjm_get_formatted_url( mdjm_get_option( 'playlist_page' ) )
		)
	);
	die();
} // mdjm_add_playlist_entry
add_action( 'mdjm_add_playlist_entry', 'mdjm_add_playlist_entry_action' );

/**
 * Remove a song.
 *
 * Remove a new song from the event playlist.
 *
 * @since	1.3
 * @param	int		$entry_id	DB entry ID.
 * @return	int		true if successfull, false if not.
 */
function mdjm_remove_playlist_entry_action( $data )	{
	if( ! isset( $data['id'] ) )	{
		$message = 25;
	}
	
	elseif( ! wp_verify_nonce( $data['mdjm_nonce'], 'remove_playlist_entry' ) )	{
		$message = 99;
	}
	
	else	{
		if( mdjm_remove_stored_playlist_entry( $data['id'] ) )	{
			$message = 23;
		}
		else	{
			$message =24;
		}
	}
	
	wp_redirect( 
		add_query_arg( 
			array(
				'event_id'	  => $data['event_id'],
				'mdjm-message'  => $message
			),
			mdjm_get_formatted_url( mdjm_get_option( 'playlist_page' ) )
		)
	);
	die();
} // mdjm_remove_playlist_entry
add_action( 'mdjm_remove_playlist_entry', 'mdjm_remove_playlist_entry_action' );