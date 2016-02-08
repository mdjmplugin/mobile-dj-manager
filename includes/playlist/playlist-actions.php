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
 * Add a song to the playlist.
 *
 * Add a new entry to the event playlist.
 *
 * @since	1.3
 * @param	arr		$data	Form data from the $_POST super global.
 * @return	void
 */
function mdjm_add_playlist_entry( $data )	{
	global $wpdb, $mdjm_notice;
	
	if( ! wp_verify_nonce( $data[ 'mdjm_nonce' ], 'add_playlist_entry' ) )	{
		$class   = 'error';
		$title   = __( 'Error', 'mobile-dj-manager' );
		$message =  __( 'Security verification failed.', 'mobile-dj-manager' );
		
		$return = false;
	}
	
	elseif( ! isset( $data[ 'mdjm_playlist_song' ], $data[ 'mdjm_playlist_artist' ] ) )	{
		$class   = 'error';
		$title   = __( 'Error', 'mobile-dj-manager' );
		$message =  __( 'Please provide at least a song and an artist.', 'mobile-dj-manager' );
		
		$return = false;
	}
	
	// Setup the discount code details
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
		$mdjm_notice = mdjm_display_notice(
			$class   = 'success',
			$title   = __( 'Added', 'mobile-dj-manager' ),
			$message =  __( 'Playlist entry added.', 'mobile-dj-manager' )
		);
		
		return $return;
	}
	else	{
		$mdjm_notice = mdjm_display_notice();
		return false;
	}
} // mdjm_add_playlist_entry
add_action( 'mdjm_add_playlist_entry', 'mdjm_add_playlist_entry' );

/**
 * Remove a song.
 *
 * Remove a new song from the event playlist.
 *
 * @since	1.3
 * @param	int		$entry_id	DB entry ID.
 * @return	int		true if successfull, false if not.
 */
function mdjm_remove_playlist_entry( $data )	{
	global $wpdb, $mdjm_notice;
	
	if( ! isset( $data['id'] ) )	{
		$class   = 'notice';
		$title   = __( 'Ooops!', 'mobile-dj-manager' );
		$message =  __( 'No track was specified.', 'mobile-dj-manager' );
		
		$return = false;
	}
	
	elseif( ! wp_verify_nonce( $data['mdjm_nonce'], 'remove_playlist_entry' ) )	{
		$class   = 'error';
		$title   = __( 'Error', 'mobile-dj-manager' );
		$message =  __( 'Security verification failed.', 'mobile-dj-manager' );
		
		$return = false;
	}
	
	else	{
		$return = mdjm_remove_stored_playlist_entry( $data['id'] );
	}
	
	return $return;
} // mdjm_remove_playlist_entry
add_action( 'mdjm_remove_playlist_entry', 'mdjm_remove_playlist_entry' );