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
 * Get Playlist Entries.
 *
 * Retrieves all playlist entries for the event.
 *
 * @since	1.3
 * @param	int		$entry_id	Entry ID
 * @return	obj		
 */
function mdjm_get_playlist_entries( $event_id, $args=array() )	{
	
	$defaults = array(
		'post_type'			=> 'mdjm-playlist',
		'posts_per_page' 	=> -1,
		'post_status'		=> 'publish',
		'post_parent'		=> $event_id,
		'orderby'			=> 'post_date',
		'order'				=> 'ASC',
		'meta_query'		=> array()
	);
	
	$args = wp_parse_args( $args, $defaults );
	
	$entries = get_posts( $args );
	
	return apply_filters( 'mdjm_get_playlist_entries', $entries, $event_id );
	
} // mdjm_get_playlist_entries

/**
 * Retrieve the data for the playlist entry.
 *
 * @since	1.3
 * @param	int		$entry_id	The playlist entry ID (post ID)
 * @return	arr		$entry_data	The playlist data.
 */
function mdjm_get_playlist_entry_data( $entry_id )	{
	$entry_data = array(
		'song'		=> get_post_meta( $entry_id, '_mdjm_playlist_entry_song', true ),
		'artist'	=> get_post_meta( $entry_id, '_mdjm_playlist_entry_artist', true ),
		'added_by'	=> get_post_meta( $entry_id, '_mdjm_playlist_entry_added_by', true ),
		'djnotes'	=> get_post_meta( $entry_id, '_mdjm_playlist_entry_djnotes', true )
	);
	
	return apply_filters( 'mdjm_get_playlist_entry_data', $entry_data );
} // mdjm_get_playlist_entry_data

/**
 * Store a playlist entry. If it exists, update it, otherwise create a new one.
 *
 * @since	1.3
 * @param	arr		$details	Playlist entry data
 * @return	bool	Whether or not the entry was created.
 */
function mdjm_store_playlist_entry( $details )	{
	$meta = array(
		'song'      => isset( $details['entry_song'] )      ? $details['entry_song']		: '',
		'artist'    => isset( $details['entry_artist'] )	? $details['entry_artist']		: '',
		'added_by'  => isset( $details['entry_addedby'] )   ? $details['entry_addedby']	  : get_current_user_id(),
		'djnotes'   => isset( $details['entry_djnotes'] )   ? $details['entry_djnotes']	 : '',
		'to_mdjm'   => '',
		'uploaded'  => false,
	);
	
	(int)$term   = isset( $details['entry_category'] ) 	? $details['entry_category']	: '';
	$event_id	= isset( $details['entry_event'] )		? $details['entry_event']       : '';
	
	// Add the playlist entry
	$meta = apply_filters( 'mdjm_insert_playlist_entry', $meta );

	do_action( 'mdjm_insert_playlist_entry_before', $meta );

	$title = sprintf( __( 'Event ID: %s %s %s', 'mobile-dj-manager' ),
				mdjm_get_option( 'event_prefix', '' ) . $event_id,
				$meta['song'],
				$meta['artist'] );

	$entry_id = wp_insert_post(
		array(
			'post_type'     => 'mdjm-playlist',
			'post_title'    => $title,
			'post_author'	=> 1,
			'post_status'   => 'publish',
			'post_parent'   => $event_id,
			'post_category' => array( $term )
		)
	);
	
	if( ! empty( $term ) )	{
		mdjm_set_playlist_entry_category( $entry_id, $term );
	}

	foreach( $meta as $key => $value ) {
		update_post_meta( $entry_id, '_mdjm_playlist_entry_' . $key, $value );
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
	// Process actions before removing entry.
	do_action( 'mdjm_delete_playlist_entry_before', $entry_id );
	
	$entry = wp_delete_post( $entry_id, true );
	
	if( $entry )	{
		// Process actions after removing song.
		do_action( 'mdjm_delete_playlist_entry_after', $entry );		
	}
	else	{
		$entry = false;
	}
	
	return $entry;
} // mdjm_remove_stored_playlist_entry

/**
 * Store a guest playlist entry.
 *
 * @since	1.3
 * @param	arr		$details	Playlist entry data
 * @return	bool	Whether or not the entry was created.
 */
function mdjm_store_guest_playlist_entry( $details )	{
	$meta = array(
		'song'      => isset( $details['entry_guest_song'] )      ? $details['entry_guest_song']		: '',
		'artist'    => isset( $details['entry_guest_artist'] )	? $details['entry_guest_artist']		: '',
		'added_by'  => ucwords( $details['entry_guest_firstname'] . ' ' . $details['entry_guest_lastname'] ),
		'to_mdjm'   => '',
		'uploaded'  => false,
	);
	
	$guest_term = get_term_by( 'name', 'Guest', 'playlist-category' );
	
	if( ! empty( $guest_term ) )	{
		(int)$term   = $guest_term->term_id;
	}
	
	$event_id	= $details['entry_event'];
	
	// Add the playlist entry
	$meta = apply_filters( 'mdjm_insert_guest_playlist_entry', $meta );

	do_action( 'mdjm_insert_guest_playlist_entry_before', $meta );

	$title = sprintf( __( 'Event ID: %s %s %s', 'mobile-dj-manager' ),
				mdjm_get_option( 'event_prefix', '' ) . $event_id,
				$meta['song'],
				$meta['artist'] );

	$entry_id = wp_insert_post(
		array(
			'post_type'     => 'mdjm-playlist',
			'post_title'    => $title,
			'post_author'	=> 1,
			'post_status'   => 'publish',
			'post_parent'   => $event_id,
			'post_category' => array( $term )
		)
	);
	
	if( ! empty( $term ) )	{
		mdjm_set_playlist_entry_category( $entry_id, $term );
	}

	foreach( $meta as $key => $value ) {
		update_post_meta( $entry_id, '_mdjm_playlist_entry_' . $key, $value );
	}

	do_action( 'mdjm_insert_guest_playlist_entry_after', $meta, $entry_id );

	// Playlist entry added
	return $entry_id;
} // mdjm_store_guest_playlist_entry

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
 * Set the playlist entry category.
 *
 * @since	1.3
 * @param	int		$event_id	The event ID.
 * @param	int		$term_id	The category term ID.
 * @return	bool	True on success, otherwise false.
 */
function mdjm_set_playlist_entry_category( $event_id, $term_id )	{
	$set_entry_type = wp_set_post_terms( $event_id, $term_id, 'playlist-category' );
	
	if ( is_wp_error( $set_entry_type ) )	{
		return false;
	}
	else	{ 
		return true;
	}
} // mdjm_set_playlist_entry_category
 
/**
 * Retrieves the playlist entries for an event grouped by category.
 *
 * @since	1.3
 * @param	int		$event_id	The event ID.
 * @param	arr		$args		See codex get_terms
 * @return	obj		$playlist	Array of all playlist entries.
 */
function mdjm_get_playlist_by_category( $event_id, $args=array() )	{
	
	$defaults = array(
		'orderby'		=> 'name',
		'order'			=> 'ASC',
		'hide_empty'	=> false
	);
	
	$terms    = mdjm_get_event_playlist_categories( $event_id, $args );
	$playlist = array();
	
	if( ! $terms )	{
		return false;
	}
	
	// Place all playlist entries into an array grouped by the category
	foreach( $terms as $term => $data )	{
		$entries = get_posts(
			array(
				'post_type'			=> 'mdjm-playlist',
				'posts_per_page' 	=> -1,
				'post_parent'  		=> $event_id,
				'post_status'  		=> 'publish',
				'tax_query'			=> array(
					array(
						'taxonomy' => 'playlist-category',
						'field'    => 'name',
						'terms'    => $term
					)
				)
			)
		);
					
		if( ! $entries )	{
			continue;
		}
		
		foreach( $entries as $entry )	{
			$playlist[ $term ] = $entries;
		}
	}
	
	return $playlist;
} // mdjm_get_playlist_by_category

/**
 * Retrieves the categories for entries within the event playlist.
 *
 * Performs a search through the playlist database table for the given event to determine all song categories used within that playlist.
 *
 * @since	1.3
 * @param	int		$event_id	The event ID.
 * @param	arr		$args		See codex get_terms
 * @return	arr		Array of all unique playlist categories.
 */
function mdjm_get_event_playlist_categories( $event_id, $args=array() )	{
	$terms		= mdjm_get_playlist_categories( $args );
	$categories = array();
	
	if( ! $terms )	{
		return false;
	}
	
	// Loop through categories and retrieve entries within each category.
	// Place each entry into the $category array
	foreach( $terms as $term )	{
		$categories[ $term->name ] = get_posts(
			array(
				'post_type'			=> 'mdjm-playlist',
				'posts_per_page' 	=> 1,
				'post_status'		=> 'publish',
				'post_parent'		=> $event_id,
				'tax_query'			=> array(
					array(
						'taxonomy' => 'playlist-category',
						'field'    => 'term_id',
						'terms'    => $term->term_id
					)
				)
			)
		);
	}
		
	return $categories;
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
	
	$date = get_post_meta( $event_id, '_mdjm_event_date', true );
			
	return time() > ( strtotime( $date ) - ( $close * DAY_IN_SECONDS ) ) ? false : true;
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
function mdjm_count_playlist_entries( $event_id, $category = false )	{
	$entry_query = array(
		'post_type'	  => 'mdjm-playlist',
		'post_status'	=> 'publish',
		'post_parent'	=> $event_id,
		'posts_per_page' => -1
	);
	
	if( ! empty( $category ) )	{
		$tax_query = array(
			'tax_query' => array(
				array(
					'taxonomy' => 'playlist-category',
					'field'    => 'name',
					'terms'    => $category
				)
			)
		);
	}
	
	$query = $entry_query;
	
	if( isset( $tax_query ) )	{
		$query = array_merge( $entry_query, $tax_query );
	}
	
	$entries = new WP_Query( $query );

	return ( $entries ? $entries->post_count : 0 );
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
 * @param	arr			$args		See codex get_terms
 * @return	obj|bool	Array of categories, or false if none.
 */
function mdjm_get_playlist_categories( $args=array() )	{
		
	$terms = get_terms( 'playlist-category', $args );
	
	if ( ! empty( $terms ) && ! is_wp_error( $terms ) )	{
		return $terms;
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
		'selected'           => mdjm_get_option( 'playlist_default_cat', 0 ),
		'hierarchical'       => 0, 
		'name'               => 'entry_category',
		'id'                 => 'entry_category',
		'class'              => '',
		'taxonomy'           => 'playlist-category',
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

/**
 * Set the playlist guest access code.
 *
 * @since	1.3
 * @param
 * @return	str		The guest playlist access code.
 */
function mdjm_generate_playlist_guest_code()	{
	$code = substr( str_shuffle( "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789" ), 0, 9 );
	
	return apply_filters( 'mdjm_generate_playlist_guest_code', $code );
} // mdjm_generate_playlist_guest_code

/**
 * Retrieve playlist entries yet to be uploaded to MDJM.
 *
 * @since	1.3
 * @param
 * @return	obj		$entries	WP_Query results of entries to upload.
 */
function mdjm_get_playlist_entries_to_upload()	{
	
	$args = array(
		'post_type'			=> 'mdjm-playlist',
		'post_status'		=> 'publish',
		'posts_per_page'	=> -1,
		'meta_query'		=> array(
			array(
				'key'			=> '_mdjm_playlist_entry_uploaded',
				'value'			=> true,
				'compare'		=> '!=',
				'type'			=> 'NUMERIC'
			)
		)
	);
	
	$args = apply_filters( 'mdjm_get_playlist_entries_to_upload', $args );
	
	$entries = get_posts( $args );
	
	if ( ! $entries )	{
		return false;
	} else	{
		return $entries;
	}
	
} // mdjm_get_playlist_entries_to_upload

/**
 * Prepare the playlist entries for upload.
 *
 * @since	1.3
 * @param
 * @return	arr		Array of data to upload.
 */
function mdjm_prepare_playlist_upload_data()	{
	
	$entries = mdjm_get_playlist_entries_to_upload();
	
	if ( ! $entries )	{
		return false;
	}
	
	$uploads = array();
	$i = 0;
	
	foreach( $entries as $entry )	{
		
		if( 'mdjm-completed' != get_post_status( $entry->post_parent ) )	{
			continue;
		} else	{
			
			$mdjm_event = new MDJM_event( $entry->post_parent );
			
			if ( ! $mdjm_event )	{
				continue;
			}
			
			$uploads[ $entry->ID ] = array(
				'date_added'	=> date( 'Y-m-d', strtotime( $entry->post_date ) ),
				'event_date'	=> date( 'Y-m-d', strtotime( $mdjm_event->date ) ),
				'event_type'	=> esc_attr( urlencode( $mdjm_event->get_type() ) ),
				'song'			=> esc_attr( urlencode( stripslashes( get_post_meta( $entry->ID, '_mdjm_playlist_entry_song', true ) ) ) ),
				'artist'		=> esc_attr( urlencode( stripslashes( get_post_meta( $entry->ID, '_mdjm_playlist_entry_artist', true ) ) ) )
			);
			
			$i++;
			
			if ( $i == 50 )	{
				return $uploads;
			}
		}
		
	}
	
	return $uploads;
	
} // mdjm_prepare_playlist_upload_data

/**
 * Process the playlist upload to MDJM.
 *
 * @since	1.3
 * @param
 * @return	void
 */
function mdjm_process_playlist_upload()	{
	
	$entries = mdjm_prepare_playlist_upload_data();
	
	if ( empty( $entries ) )	{
		MDJM()->debug->log_it( __( 'There are no playlist entries to upload' ) );
		
		return;
	}
	
	$data = array(
		'url'		=> urlencode( get_site_url() ),
		'company'	=> urlencode( mdjm_get_option( 'company_name', get_bloginfo( 'name' ) ) )
	);
	
	$debug[] = sprintf( __( '%d playlist entries to upload', 'mobile-dj-manager' ), count( $entries ) );
	
	$i = 1;
	
	foreach( $entries as $id => $entry )	{
		
		$entry_data = array_merge( $entry, $data );
		
		$rpc = 'http://www.mydjplanner.co.uk/?mdjm-api=MDJM_PLAYLIST';
		
		foreach( $entry_data as $key => $value )	{
			$rpc .= '&' . $key . '=' . $value;
		}
		
		$response = wp_remote_retrieve_body( wp_remote_get( $rpc ) );
		
		if( $response )	{
			
			$debug[] = sprintf( __( '%s by %s successfully uploaded.', 'mobile-dj-manager' ), $entry_data['song'], $entry_data['artist'] );
			
			update_post_meta( $id, '_mdjm_playlist_entry_to_mdjm', current_time( 'mysql' ) );
			update_post_meta( $id, '_mdjm_playlist_entry_uploaded', true );
			
		} else	{
			$debug[] = sprintf( __( '%s by %s could not be uploaded.', 'mobile-dj-manager' ), $entry_data['song'], $entry_data['artist'] );
		}
		
		$i++;
		
	}
	
	$debug[] = sprintf( __( '%d out of %d entries successfully uploaded.', 'mobile-dj-manager' ), $i, count( $entries ) );
	
	if ( ! empty( $debug ) )	{
		
		foreach( $debug as $log )	{
			MDJM()->debug->log_it( $log, false );
		}
		
	}
	
} // mdjm_process_playlist_upload

/**
 * Return the number of playlist entries pending upload to MDJM
 *
 * @since	1.3
 * @param
 * @return	int		The total number of entries pending upload.
 */
function mdjm_get_pending_upload_playlist_entry_count()	{
	
	$entries = mdjm_get_playlist_entries_to_upload();
	
	$count = 0;
	
	if ( $entries )	{
		$count = count( $entries );
	}
	
	return $count;
	
} // mdjm_get_pending_upload_playlist_entry_count

/**
 * Return the number of playlist entries uploaded to MDJM
 *
 * @since	1.3
 * @param
 * @return	int		The total number of entries uploaded.
 */
function mdjm_get_uploaded_playlist_entry_count()	{
	
	$args = array(
		'post_type'			=> 'mdjm-playlist',
		'post_status'		=> 'publish',
		'posts_per_page'	=> -1,
		'meta_query'		=> array(
			array(
				'key'			=> '_mdjm_playlist_entry_uploaded',
				'value'			=> true,
				'compare'		=> '=',
				'type'			=> 'NUMERIC'
			)
		)
	);
	
	$entries = get_posts( $args );
	
	$count = 0;
	
	if ( $entries )	{
		$count = count( $entries );
	}
	
	return $count;
	
} // mdjm_get_uploaded_playlist_entry_count