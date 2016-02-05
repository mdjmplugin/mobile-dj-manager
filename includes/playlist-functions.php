<?php
/**
 * Contains all playlist related functions
 *
 * @package		MDJM
 * @subpackage	Playlists
 * @since		1.3
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) )
	exit;

/**
 * Determine if this event has playlists enabled.
 *
 * @since	1.3
 * @param	int		$event_id	The event ID.
 * @return	bool	True if the playlist is open, false if not.
 */
function mdjm_playlist_is_enabled( $event_id )	{
	
	if ( 'Y' == get_post_meta( $event_id, '_mdjm_event_playlist', true ) )	{
		return true;
	}
		
	return false;
} // mdjm_playlist_is_enabled
	
/**
 * Returns the status of the event playlist.
 *
 * @since	1.3
 * @param	int		$event_id	The event ID.
 * @return	bool	True if the playlist is open, false if not.
 */
function mdjm_playlist_is_open( $event_id )	{
	// Playlist disabled for this event
	if( ! mdjm_playlist_is_enabled( $event_id ) )	{
		return false;
	}
	
	// Playlist never closes
	if( mdjm_get_option( 'close', '0' ) == 0 )	{
		return true;
	}
		
	return time() > ( $date - ( MDJM_PLAYLIST_CLOSE * DAY_IN_SECONDS ) ) ? false : true;
} // mdjm_playlist_is_open