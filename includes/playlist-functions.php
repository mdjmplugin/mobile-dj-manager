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
 * Retrieves the playlist entries for an event grouped by category.
 *
 * @since	1.3
 * @param	int		$event_id	The event ID.
 * @return	obj		$playlist	Array of all playlist entries.
 */
function mdjm_get_playlist_by_category( $event_id )	{
	global $wpdb;
	
	$playlist   = array();
	$categories = mdjm_get_event_playlist_categories( $event_id );
	
	if( ! $categories )	{
		return false;
	}
	
	// Place all playlist entries into an array grouped by the category
	foreach( $categories as $cat )	{
		$category       = $cat->category;
		
		$query          = "SELECT * 
					       FROM `" . MDJM_PLAYLIST_TABLE . "` 
					       WHERE `event_id` = '$event_id' 
					       AND `play_when` = '$category'";
					
		$songs         = $wpdb->get_results( $query );
		
		$playlist[ $category ] = $songs;
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
 * Retrieve the number of songs in an event playlist.
 *
 * If a category is provided, count only the songs within that category.
 *
 * @since	1.3
 * @param	int		$event_id	Required: The event ID.
 * @param	str		$category	Optional: Count only songs in the category, or count all if empty.
 * @return	int		Number of songs in the playlist.
 */
function mdjm_count_songs( $event_id, $category='' )	{
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
} // mdjm_count_songs

/**
 * Retrieve the length of the event playlist.
 *
 * Calculate the approximate length of the event playlist and return in human readable format.
 *
 * @since	1.3
 * @param	int		$event_id		The event ID.
 * @param	int		$songs			Number of songs in playlist.
 * @param	int		$song_length	Average length of a song in seconds.
 * @return	str		The length of the event playlist.
 */
function mdjm_playlist_length( $event_id='', $songs='', $song_length=180 )	{
	if( empty( $songs ) )	{
		$songs = mdjm_count_songs( $event_id );
	}
	
	$length_of_playlist = ( $songs * $song_length );
	$start_time         = current_time( 'timestamp' );
	$end_time           = strtotime( '+' . ( $song_length * $songs ) . ' seconds', current_time( 'timestamp' ) );
	
	$length = str_replace( 'min', 'minute', human_time_diff( $start_time, $end_time ) );
	
	return apply_filters( 'mdjm_playlist_length', $length, $event_id, $songs, $song_length ); 
} // mdjm_playlist_length

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
		'name'		=> 'mdjm_playlist_category',
		'id'		=> 'mdjm_playlist_category',
		'class'		=> ''
	);
	
	$settings = wp_parse_args( $args, $defaults );
	$class    = '';
	
	if( ! empty( $args['class'] ) )	{
		$class = ' class="' . $args['class'] . '"';
	}
	
	$categories = mdjm_get_playlist_categories();
		
	$output = '<select name="' . $settings['name'] . '" id="' . $settings['name'] . '"' . $class . '>' . "\r\n";
	
	foreach( $categories as $category )	{
		$output .= '<option value="' . $category . '">' . $category . '</option>' . "\r\n";
	}
	
	$output .= '</select>' . "\r\n";
	
	if( ! empty( $echo ) )	{
		echo $output;
	}
	else	{
		return $output;
	}
	
} // mdjm_playlist_category_dropdown