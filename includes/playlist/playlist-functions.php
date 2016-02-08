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
 * Get Playlist Entry
 *
 * Retrieves a complete entry by entry ID.
 *
 * @since	1.3
 * @param	int	$entry_id	Entry ID
 * @return	arr
 */
function mdjm_get_playlist_entry( $entry_id = 0 ) {

	if( empty( $entry_id ) ) {
		return false;
	}

	$entry = get_post( $entry_id );

	if ( get_post_type( $entry_id ) != 'mdjm-playlist' ) {
		return false;
	}

	return $entry;
} // mdjm_get_playlist_entry

/**
 * Store a playlist entry. If it exists, update it, otherwise create a new one.
 *
 * @since	1.3
 * @param	arr		$details	Playlist entry data
 * @return	bool	Whether or not the entry was created.
 */
function mdjm_store_playlist_entry( $details )	{
	$meta = array(
		'event'     => isset( $details['entry_event'] )       ? $details['entry_event']       : '',
		'song'      => isset( $details['entry_song'] )        ? $details['entry_song']        : '',
		'artist'    => isset( $details['entry_artist'] )      ? $details['entry_artist']      : '',
		'added_by'  => isset( $details['entry_added_by'] )    ? $details['entry_added_by']    : get_current_user_id(),
	);
	
	$category = isset( $details['entry_category'] ) ? $details['entry_category'] : '';

	// Add the playlist entry
	$meta = apply_filters( 'mdjm_insert_playlist_entry', $meta );

	do_action( 'mdjm_insert_playlist_entry_before', $meta );

	$title = sprintf( __( 'Event ID: %s %s %s', 'mobile-dj-manager' ),
				mdjm_get_option( 'event_prefix', '' ) . $meta['event'],
				$meta['song'],
				$meta['artist'] );

	$entry_id = wp_insert_post(
		array(
			'post_type'   => 'mdjm-playlist',
			'post_title'  => $title,
			'post_status' => 'publish',
			'post_parent' => $meta['event']
		)
	);
	
	if( ! empty( $category ) )	{
		wp_set_object_terms( $entry_id, $category, 'mdjm-playlist-category', false );
	}

	foreach( $meta as $key => $value ) {
		update_post_meta( $entry_id, '_mdjm_playlist_entry' . $key, $value );
	}

	do_action( 'mdjm_insert_playlist_entry_after', $meta, $entry_id );

	// Playlist entry added
	return $entry_id;
} // mdjm_store_playlist_entry

/**
 * Remove a playlist entry.
 *
 * @since	1.3
 * @param	arr		$entry_id	Playlist entry id
 * @return	bool	Whether or not the entry was removed.
 */
function mdjm_remove_stored_playlist_entry( $entry_id )	{
	// Process actions before removing song.
	do_action( 'mdjm_delete_playlist_entry_before', $data );
	
	$entry = wp_delete_post( $entry_id, true );
	
	if( $entry )	{
		// Process actions after removing song.
		do_action( 'mdjm_delete_playlist_entry_after', $entry );
		
		$class   = 'success';
		$title   = __( 'Done', 'mobile-dj-manager' );
		$message =  __( 'The entry was removed.', 'mobile-dj-manager' );
	}
	
	else	{
		$class   = 'error';
		$title   = __( 'Error', 'mobile-dj-manager' );
		$message =  __( 'Unable to delete entry.', 'mobile-dj-manager' );
		
		$entry = false;
	}

	$mdjm_notice = mdjm_display_notice( $class, $title, $message );
} // mdjm_remove_stored_playlist_entry

/**
 * Checks to see if a playlist entry already exists.
 *
 * @since	1.3
 * @param	int		$entry_id	Entry ID
 * @return	bool
 */
function mdjm_playlist_entry_exists( $entry_id ) {
	if ( mdjm_get_playlist_entry(  $entry_id ) ) {
		return true;
	}

	return false;
}// mdjm_entry_exists
 
/**
 * Retrieves the playlist entries for an event grouped by category.
 *
 * @since	1.3
 * @param	int		$event_id	The event ID.
 * @return	obj		$playlist	Array of all playlist entries.
 */
function mdjm_get_playlist_by_category( $event_id )	{
	$categories = mdjm_get_event_playlist_categories( $event_id );
	
	if( ! $categories )	{
		return false;
	}
	
	// Place all playlist entries into an array grouped by the category
	foreach( $categories as $cat )	{
		$category = $cat->category;
		
		$entries = get_posts(
			array(
				'post_type'	=> 'mdjm_playlist',
				'post_parent'  => $event_id,
				'post_status'  => 'publish'
			)
		);
					
		if( ! $entries )	{
			continue;
		}
		
		foreach( $entries as $entry )	{
			$playlist[ $category ] = $entries;
		}
	}
	
	return $playlist;
} // mdjm_get_playlist_by_category

/**
 * Retrieves the categories for entries within the event playlist.
 *
 * Performs a search through the playlist database table for the given event to determine all song categories used.
 *
 * @since	1.3
 * @param	int		$event_id	The event ID.
 * @return	obj		Object array of all unique playlist categories.
 */
function mdjm_get_event_playlist_categories( $event_id )	{
	global $wpdb;
	
	$query   = "SELECT 
				DISTINCT play_when 
				as category 
				FROM `" . MDJM_PLAYLIST_TABLE . "` 
				WHERE `event_id` = '$event_id' 
				ORDER BY `play_when`";
				
	return $wpdb->get_results( $query );
} // mdjm_get_event_playlist_categories

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
	
	$close = mdjm_get_option( 'close', false );
	
	// Playlist never closes
	if( empty( $close ) )	{
		return true;
	}
		
	return time() > ( $date - ( $close * DAY_IN_SECONDS ) ) ? false : true;
} // mdjm_playlist_is_open

/**
 * Retrieve the number of entries in an event playlist.
 *
 * If a category is provided, count only the entries within that category.
 *
 * @since	1.3
 * @param	int		$event_id	Required: The event ID.
 * @param	str		$category	Optional: Count only songs in the category, or count all if empty.
 * @return	int		Number of songs in the playlist.
 */
function mdjm_count_playlist_entries( $event_id, $category='' )	{
	global $wpdb;
	
	$cat_only = '';
	
	if( ! empty( $category ) )	{
		$cat_only .= "AND `play_when` = '$category'";
	}
	
	$query = "SELECT COUNT(*)
			  FROM `" . MDJM_PLAYLIST_TABLE . "` 
			  WHERE `event_id` = '$event_id' 
			  $cat_only";		  
			  
	$count = $wpdb->get_var( $query );
	
	return ( ! empty( $count ) ? $count : 0 );
} // mdjm_count_playlist_entries

/**
 * Retrieve the duration of the event playlist.
 *
 * Calculate the approximate length of the event playlist and return in human readable format.
 *
 * @since	1.3
 * @param	int		$event_id		The event ID.
 * @param	int		$songs			Number of songs in playlist.
 * @param	int		$song_duration	Average length of a song in seconds.
 * @return	str		The length of the event playlist.
 */
function mdjm_playlist_duration( $event_id='', $songs='', $song_duration=180 )	{
	if( empty( $songs ) )	{
		$songs = mdjm_count_playlist_entries( $event_id );
	}
	
	$start_time         = current_time( 'timestamp' );
	$end_time           = strtotime( '+' . ( $song_duration * $songs ) . ' seconds', current_time( 'timestamp' ) );
	
	$duration = str_replace( 'min', 'minute', human_time_diff( $start_time, $end_time ) );
	
	return apply_filters( 'mdjm_playlist_duration', $duration, $event_id, $songs, $song_duration ); 
} // mdjm_playlist_duration

/**
 * Returns the URL for the events guest playlist.
 *
 * @since	1.3
 * @param	int		$event_id	The event ID.
 * @return	str		URL to access the guest playlist.
 */
function mdjm_guest_playlist_url( $event_id )	{
	$access_code = get_post_meta( $event_id, '_mdjm_event_playlist_access', true );
	
	if( empty( $access_code ) )	{
		$url = '';
	}
	
	else	{
		$url = mdjm_get_formatted_url( mdjm_get_option( 'playlist_page' ), true ) . 'guest_playlist=' . $access_code;
	}
	
	return $url;
} // mdjm_guest_playlist_url

/**
 * Retrieve all playlist categories.
 *
 * @since	1.3
 * @param
 * @return	arr|bool	Array of categories, or false if none.
 */
function mdjm_get_playlist_categories()	{
	if( ! empty( mdjm_get_option( 'playlist_cats', false ) ) )	{
		return explode( "\r\n", mdjm_get_option( 'playlist_cats' ) );
	}
	else	{
		return false;
	}
} // mdjm_get_playlist_categories

/**
 * Creates a select input field which contains all playlist categories.
 *
 * @since	1.3
 * @param	arr		$args	Override the defaults for the select field. See $defaults within the function.
 * @param	bool	$echo	True to echo the output, false to return as a string.
 * @return	str		HTML output for the select field.
 */	
function mdjm_playlist_category_dropdown( $args='', $echo=true )	{
	$defaults = array(
		'show_option_all'    => '',
		'show_option_none'   => '',
		'option_none_value'  => '-1',
		'orderby'            => 'name', 
		'order'              => 'ASC',
		'show_count'         => 0,
		'hide_empty'         => 0, 
		'child_of'           => 0,
		'exclude'            => '',
		'echo'               => 0,
		'selected'           => 0,
		'hierarchical'       => 0, 
		'name'               => 'entry_category',
		'id'                 => 'entry_category',
		'class'              => '',
		'taxonomy'           => 'mdjm-playlist-category',
		'hide_if_empty'      => false,
		'value_field'	    => 'term_id'
	);
	
	$settings = wp_parse_args( $args, $defaults );

	$category_dropdown = wp_dropdown_categories( $settings );
	
	if( ! empty( $echo ) )	{
		echo $category_dropdown;
	}
	else	{
		return $category_dropdown;
	}
	
} // mdjm_playlist_category_dropdown