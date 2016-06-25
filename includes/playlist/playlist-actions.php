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
 * Redirect to playlist.
 *
 * @since	1.3
 * @param
 * @return	void
 */
function mdjm_goto_playlist_action( $data )	{
	if( ! isset( $data['event_id'] ) )	{
		return;
	}
	
	if( ! mdjm_event_exists( $data['event_id'] ) )	{
		wp_die( 'Sorry but no event exists', 'mobile-dj-manager' );
	}
	
	wp_redirect( 
		add_query_arg( 'event_id', $data['event_id'], 
		mdjm_get_formatted_url( mdjm_get_option( 'playlist_page' ) ) )
	);
	die();
} // mdjm_goto_guest_playlist
add_action( 'mdjm_goto_playlist', 'mdjm_goto_playlist_action' );

/**
 * Redirect to guest playlist.
 *
 * @since	1.3
 * @param
 * @return	void
 */
function mdjm_goto_guest_playlist_action( $data )	{
	if( ! isset( $data['playlist'] ) )	{
		return;
	}
	
	$event = mdjm_get_event_by_playlist_code( $data['playlist'] );
	
	if( ! $event )	{
		wp_die( 'Sorry but no event exists', 'mobile-dj-manager' );
	}
	
	wp_redirect( 
		add_query_arg( 'guest_playlist', $data['playlist'], 
		mdjm_get_formatted_url( mdjm_get_option( 'playlist_page' ) ) )
	);
	die();
} // mdjm_goto_guest_playlist
add_action( 'mdjm_goto_guest_playlist', 'mdjm_goto_guest_playlist_action' );

/**
 * Redirect to playlist.
 *
 * Catches incorrect redirects and forwards to the playlist page.
 * @see https://github.com/mdjm/mobile-dj-manager/issues/101
 *
 * @since	1.3.7
 * @param
 * @return	void
 */
function mdjm_correct_guest_playlist_url_action()	{
	if( ! isset( $_GET['guest_playlist'] ) )	{
		return;
	}

	if ( ! is_page( mdjm_get_option( 'playlist_page' ) ) )	{
		wp_redirect( 
			add_query_arg( 'guest_playlist', $_GET['guest_playlist'], 
			mdjm_get_formatted_url( mdjm_get_option( 'playlist_page' ) ) )
		);
		die();
	}
} // mdjm_correct_guest_playlist_url_action
add_action( 'template_redirect', 'mdjm_correct_guest_playlist_url_action' );
	
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
		$message = 'nonce_fail';
	}
	
	elseif( ! isset( $data[ 'entry_song' ], $data[ 'entry_artist' ] ) )	{
		$message = 'playlist_data_missing';
	}
	
	else	{
		// Setup the playlist entry details
		$posted = array();
	
		foreach ( $data as $key => $value ) {
	
			if( $key != 'mdjm_nonce' && $key != 'mdjm_action' && $key != 'mdjm_redirect' && $key != 'entry_addnew' ) {
				if( is_string( $value ) || is_int( $value ) )	{
					$posted[ $key ] = strip_tags( addslashes( $value ) );
	
				}
				elseif( is_array( $value ) )	{
					$posted[ $key ] = array_map( 'absint', $value );
				}
			}
		}
		
		if( mdjm_store_playlist_entry( $posted ) )	{
			$message = 'playlist_added';
		}
		else	{
			$message = 'playlist_not_added';
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
		$message = 'playlist_not_selected';
	}
	
	elseif( ! wp_verify_nonce( $data['mdjm_nonce'], 'remove_playlist_entry' ) )	{
		$message = 'nonce_fail';
	}
	
	else	{
		if( mdjm_remove_stored_playlist_entry( $data['id'] ) )	{
			$message = 'playlist_removed';
		}
		else	{
			$message = 'playlist_not_removed';
		}
	}
	
	wp_redirect( 
		add_query_arg( 
			array(
				'event_id'	  => $data['event_id'],
				'mdjm_message'  => $message
			),
			mdjm_get_formatted_url( mdjm_get_option( 'playlist_page' ) )
		)
	);
	die();
} // mdjm_remove_playlist_entry
add_action( 'mdjm_remove_playlist_entry', 'mdjm_remove_playlist_entry_action' );

/**
 * Add a song from a guest to the playlist.
 *
 * Add a new entry from a guest to the event playlist.
 *
 * @since	1.3
 * @param	arr		$data	Form data from the $_POST super global.
 * @return	void
 */
function mdjm_add_guest_playlist_entry_action( $data )	{	
	if( ! wp_verify_nonce( $data[ 'mdjm_nonce' ], 'add_guest_playlist_entry' ) )	{
		$message = 'nonce_fail';
	}
	
	elseif( ! isset( $data[ 'entry_guest_firstname' ], $data[ 'entry_guest_lastname' ], $data[ 'entry_guest_song' ], $data[ 'entry_guest_artist' ] ) )	{
		$message = 'playlist_guest_data_missing';
	}
	
	else	{
		// Setup the playlist entry details
		$posted = array();
	
		foreach ( $data as $key => $value ) {
			if( $key != 'mdjm_nonce' && $key != 'mdjm_action' && $key != 'mdjm_redirect' && $key != 'entry_guest_addnew' ) {
				if( is_string( $value ) || is_int( $value ) )	{
					$posted[ $key ] = strip_tags( addslashes( $value ) );
	
				}
				elseif( is_array( $value ) )	{
					$posted[ $key ] = array_map( 'absint', $value );
				}
			}
		}
		
		$entry = mdjm_store_guest_playlist_entry( $posted );
		
		if( $entry )	{
			$message = 'playlist_guest_added';
		}
		else	{
			$message = 'playlist_guest_error';
		}
	}
	
	wp_redirect( add_query_arg( 'mdjm_message', $message, mdjm_guest_playlist_url( $data['entry_event'] ) )	);
	
	die();
	
} // mdjm_add_guest_playlist_entry
add_action( 'mdjm_add_guest_playlist_entry', 'mdjm_add_guest_playlist_entry_action' );

/**
 * Print the playlist.
 *
 * @since	1.3
 * @param	arr		$data	Form data from the $_POST super global.
 * @return	void
 */
function mdjm_print_playlist_action( $data )	{
	if( ! wp_verify_nonce( $data[ 'mdjm_nonce' ], 'print_playlist' ) )	{
		$message = 'nonce_fail';
	}
	
	elseif( ! isset( $data[ 'event_id' ] ) )	{
		$message = 'playlist_data_missing';
	}
	
	else	{
		// Setup the playlist entry details
		$posted = array();
	
		foreach ( $data as $key => $value ) {
	
			if( $key != 'mdjm_nonce' && $key != 'mdjm_action' && $key != 'mdjm_redirect' && $key != 'entry_addnew' ) {
				if( is_string( $value ) || is_int( $value ) )	{
					$posted[ $key ] = strip_tags( addslashes( $value ) );
	
				}
				elseif( is_array( $value ) )	{
					$posted[ $key ] = array_map( 'absint', $value );
				}
			}
		}
		
		if( mdjm_store_playlist_entry( $posted ) )	{
			$message = 'playlist_added';
		}
		else	{
			$message = 'playlist_not_added';
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