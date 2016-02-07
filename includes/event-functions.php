<?php
/**
 * Contains all event related functions
 *
 * @package		MDJM
 * @subpackage	Events
 * @since		1.3
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) )
	exit;

/**
 * Retrieve an event by ID.
 *
 * @param	int		$event_id	The WP post ID for the event.
 *
 * @return	mixed	$event		WP_Query object or false.
 */
function mdjm_get_event_by_id( $event_id )	{
	$args = array(
				'p'            => $event_id,
				'post_type'    => 'mdjm-event',
				'post_status'  => 'any'
			);
	
	$event_query = new WP_Query( $args );
	
	return $event_query;
} // mdjm_get_event_by_id

/**
 * Retrieve all of this clients events.
 *
 * @since	1.3
 * @param	int		$access_code	The access code for the event playlist.
 * @return	obj		$event_query	WP_Query object.
 */
function mdjm_get_event_by_playlist_code( $access_code )	{
	$args = apply_filters( 'mdjm_get_event_by_playlist_code_args',
		array(
			'post_type'        => 'mdjm-event',
			'post_status'      => 'any',
			'posts_per_page'   => 1,
			'meta_query'       => array(
				array(
					'key'      => '_mdjm_event_playlist_access',
					'value'    => $access_code
				),
			)
		)
	);
	
	$event_query = new WP_Query( $args );
	
	return $event_query;
} // mdjm_get_event_by_playlist_code
	
/**
 * Returns an array of event post status.
 *
 * @since	1.3
 * @param
 * @return	arr		Array of event status'. Key = post_status value = MDJM Event status
 */
function mdjm_all_event_status()	{
	$post_status = array(
		'mdjm-unattended',
		'mdjm-enquiry',
		'mdjm-approved',
		'mdjm-contract',
		'mdjm-completed',
		'mdjm-cancelled',
		'mdjm-rejected',
		'mdjm-failed'
	);
		
	foreach( $post_status as $status )	{
		$mdjm_status[ $status ] = get_post_status_object( $status )->label;
	}
	
	// Sort alphabetically
	asort( $mdjm_status );
	
	return $mdjm_status;
} // mdjm_all_event_status

/**
 * Returns an array of active event post statuses.
 *
 * @since	1.3
 * @param
 * @return	arr		Array of active event status'.
 */
function mdjm_active_event_statuses()	{
	$event_status = apply_filters( 'mdjm_active_event_statuses',
		array(
			'mdjm-approved',
			'mdjm-contract',
			'mdjm-enquiry'
		)
	);
	
	// Sort alphabetically
	asort( $event_status );
	
	return $event_status;
} // mdjm_active_event_statuses

/**
 * Return the event status label for given event ID.
 *
 * @since	1.3
 * @param	int		$event_id	Optional: ID of the current event. If not set, check for global $post and $post_id.
 * @return	str		Label for current event status.
 */
function mdjm_get_event_status( $event_id='' )	{
	global $post, $post_id;
	
	if( !empty( $event_id ) )	{
		$id = $event_id;
	}
	elseif( !empty( $post_id ) )	{
		$id = $post_id;
	}
	elseif( !empty( $post ) )	{
		$id = $post->ID;
	}
	else	{
		$id = '';
	}
	
	$return = '';
	
	// Current event status
	if( !empty( $id ) )	{
		$status = get_post_status( $id );
		$return = get_post_status_object( $status )->label;
	}
	
	// Return the label for the status
	return $return;
} // mdjm_get_event_status

/**
 * Return the event type label for given event ID.
 *
 * @since	1.3
 * @param	int		$event_id	Optional: ID of the current event. If not set, check for global $post and $post_id.
 * @return	str		Label for current event type.
 */
function mdjm_get_event_type( $event_id='' )	{
	global $post, $post_id;
	
	if( !empty( $event_id ) )	{
		$id = $event_id;
	}
	elseif( !empty( $post_id ) )	{
		$id = $post_id;
	}
	elseif( !empty( $post ) )	{
		$id = $post->ID;
	}
	else	{
		$id = '';
	}
	
	$return = __( 'No event type set', 'mobile-dj-manager' );
	
	// Event type
	if( !empty( $id ) )	{
		$types = wp_get_object_terms( $event_id, 'event-types' );
		
		if( !empty( $types ) )	{
			$return = $types[0]->name;
		}
	}
	
	// Return the label for the status
	return $return;
} // mdjm_get_event_type
	
/**
 * Returns the URL for an event.
 *
 * @since	1.3
 * @param	int		$event_id	The event ID.
 * @return	str		URL to Client Zone page for the event.
 */
function mdjm_get_event_uri( $event_id )	{
	return mdjm_get_formatted_url( mdjm_get_option( 'app_home_page' ) ) . 'event_id=' . $event_id;
} // mdjm_get_event_uri

/**
 * Retrieve the length of the event.
 *
 * Calculate the length of the event and return in human readable format.
 *
 * @since	1.3
 * @uses	human_time_diff
 * @param	int		$event_id	The event ID.
 * @return	str		The length of the event.
 */
function mdjm_event_length( $event_id )	{
	$start_time = get_post_meta( $event_id, '_mdjm_event_start', true );
	$start_date = get_post_meta( $event_id, '_mdjm_event_date', true );
	$end_time   = get_post_meta( $event_id, '_mdjm_event_finish', true );
	$end_date   = get_post_meta( $event_id, '_mdjm_event_end_date', true );
	
	if( ! empty( $start_time ) && ! empty( $start_date ) && ! empty( $end_time ) && ! empty( $end_time ) )	{
		$start  = strtotime( $start_time . ' ' . $start_date );
		$end    = strtotime( $end_time . ' ' . $end_date );
		
		$length = str_replace( 'min', 'minute', human_time_diff( $start, $end ) );
		
		return apply_filters( 'mdjm_event_length', $length );
	}
} // mdjm_event_length

/**
 * Calculate time to event.
 *
 * Calculate the length of time until the event starts.
 *
 * @since	1.3
 * @uses	human_time_diff
 * @param	int		$event_id	The event ID.
 * @return	str		The length of the event.
 */
function mdjm_time_until_event( $event_id )	{
	$start_time = get_post_meta( $event_id, '_mdjm_event_start', true );
	$start_date = get_post_meta( $event_id, '_mdjm_event_date', true );
	
	if( ! empty( $start_time ) && ! empty( $start_date ) )	{
		$start  = strtotime( $start_time . ' ' . $start_date );
		$end    = strtotime( $end_time . ' ' . $end_date );
		
		$length = str_replace( 'min', 'minute', human_time_diff( $start, $end ) );
		
		return apply_filters( 'mdjm_time_until_event', $length );
	}
} // mdjm_time_until_event