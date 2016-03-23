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
 * Retrieve an event.
 *
 * @since	1.3
 * @param	int		$event_id	The event ID.
 * @return	obj		$event		The event WP_Post object
 */
function mdjm_get_event( $event_id )	{
	return mdjm_get_event_by_id( $event_id );
} // mdjm_get_event

/**
 * Retrieve an event by ID.
 *
 * @param	int		$event_id	The WP post ID for the event.
 *
 * @return	mixed	$event		WP_Query object or false.
 */
function mdjm_get_event_by_id( $event_id )	{
	$event = new MDJM_Event( $event_id );
	
	return ( !empty( $event->ID ) ? $event : false );
} // mdjm_get_event_by_id

/**
 * Retrieve all of this clients events.
 *
 * @since	1.3
 * @param	int		$access_code	The access code for the event playlist.
 * @return	obj		$event_query	WP_Query object.
 */
function mdjm_get_event_by_playlist_code( $access_code )	{
	global $wpdb;
	
	$query = "SELECT `post_id`
			  AS `event_id` 
			  FROM `$wpdb->postmeta` 
			  WHERE `meta_value` = '$access_code' 
			  LIMIT 1";
					
	$result = $wpdb->get_row( $query );
	
	return ( $result ? mdjm_get_event( $result->event_id ) : false );
} // mdjm_get_event_by_playlist_code

/**
 * Determine if the event exists.
 *
 * @since	1.3
 * @param	int			$event_id		The event ID.
 * @return	obj|bool	The WP_Post object for the event if it exists, otherwise false.
 */
function mdjm_event_exists( $event_id )	{
	return mdjm_get_event_by_id( $event_id );
} // mdjm_event_exists
	
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
	
	$event = new MDJM_Event( $id );
	
	// Return the label for the status
	return $event->get_status();
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
	
	$event = new MDJM_Event( $id );
	
	// Return the label for the status
	return $event->get_type();
} // mdjm_get_event_type

/**
 * Returns the price for an event.
 *
 * @since	1.3
 * @param	int		$event_id	The event ID.
 * @return	int|str				The price of the event.
 */
function mdjm_get_event_price( $event_id )	{
	if( empty( $event_id ) )	{
		return false;
	}

	$event = new MDJM_Event( $event_id );
	return $event->get_price();
} // mdjm_get_event_price

/**
 * Returns the deposit price for an event.
 *
 * @since	1.3
 * @param	int		$event_id	The event ID.
 * @return	int|str				The deposit price of the event.
 */
function mdjm_get_event_deposit( $event_id )	{
	if( empty( $event_id ) )	{
		return false;
	}

	$event = new MDJM_Event( $event_id );
	return $event->get_deposit();
} // mdjm_get_event_deposit

/**
 * Returns the deposit status for an event.
 *
 * @since	1.3
 * @param	int		$event_id	The event ID.
 * @return	str					The deposit status of the event.
 */
function mdjm_get_event_deposit_status( $event_id )	{
	if( empty( $event_id ) )	{
		return false;
	}

	$event = new MDJM_Event( $event_id );
	return $event->get_deposit_status();
} // mdjm_get_event_deposit_status

/**
 * Returns the balance status for an event.
 *
 * @since	1.3
 * @param	int		$event_id	The event ID.
 * @return	str					The balance status of the event.
 */
function mdjm_get_event_balance_status( $event_id )	{
	if( empty( $event_id ) )	{
		return false;
	}

	$event = new MDJM_Event( $event_id );
	return $event->get_balance_status();
} // mdjm_get_event_balance_status

/**
 * Returns the balance owed for an event.
 *
 * @since	1.3
 * @param	int		$event_id	The event ID.
 * @return	int|str				The balance owed for the event.
 */
function mdjm_get_event_balance( $event_id )	{
	if( empty( $event_id ) )	{
		return false;
	}

	$event = new MDJM_Event( $event_id );
	return $event->get_balance();
} // mdjm_get_event_balance
	
/**
 * Returns the URL for an event.
 *
 * @since	1.3
 * @param	int		$event_id	The event ID.
 * @return	str		URL to Client Zone page for the event.
 */
function mdjm_get_event_uri( $event_id )	{
	return add_query_arg( 'event_id', $event_id, mdjm_get_formatted_url( mdjm_get_option( 'app_home_page' ) ) );
} // mdjm_get_event_uri

/**
 * Retrieve the duration of the event.
 *
 * Calculate the duration of the event and return in human readable format.
 *
 * @since	1.3
 * @uses	human_time_diff
 * @param	int		$event_id	The event ID.
 * @return	str		The length of the event.
 */
function mdjm_event_duration( $event_id )	{
	$start_time = get_post_meta( $event_id, '_mdjm_event_start', true );
	$start_date = get_post_meta( $event_id, '_mdjm_event_date', true );
	$end_time   = get_post_meta( $event_id, '_mdjm_event_finish', true );
	$end_date   = get_post_meta( $event_id, '_mdjm_event_end_date', true );
	
	if( ! empty( $start_time ) && ! empty( $start_date ) && ! empty( $end_time ) && ! empty( $end_time ) )	{
		$start  = strtotime( $start_time . ' ' . $start_date );
		$end    = strtotime( $end_time . ' ' . $end_date );
		
		$duration = str_replace( 'min', 'minute', human_time_diff( $start, $end ) );
		
		return apply_filters( 'mdjm_event_duration', $duration );
	}
} // mdjm_event_duration

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

/**
 * Update the event status.
 *
 * @since	1.3
 * @param	int		$event_id	The event ID.
 * @param	str		$new_status	The new event status.
 * @param	str		$old_status	The old event status.
 * @param	arr		$args		Array of data required for transition.
 * @return	void
 */
function mdjm_update_event_status( $event_id, $new_status, $old_status, $args=array() )	{
	do_action( 'mdjm_pre_event_status_change', $event_id, $new_status, $old_status, $args );
	
	do_action( "mdjm_pre_update_event_status_{$new_status}", $event_id, $old_status, $args );
	
	$func = 'mdjm_set_event_status_' . str_replace( '-', '_', $new_status );
	
	$func( $event_id, $old_status, $args );
	
	do_action( "mdjm_post_update_event_status_{$new_status}", $event_id, $old_status, $args );
	
	do_action( 'mdjm_post_event_status_change', $event_id, $new_status, $old_status, $args );
} // mdjm_update_event_status

/**
 * Update event status to mdjm-approved.
 *
 * @since	1.3
 * @param	int		$event_id	The event ID.
 * @param	str		$old_status	The old event status.
 * @param	arr		$args		Array of data required for transition.
 * @return	str		The length of the event.
 */
function mdjm_set_event_status_mdjm_approved( $event_id, $old_status, $args )	{
	remove_action( 'save_post_mdjm-event', 'mdjm_save_event_post', 10, 3 );
	
	wp_update_post(
		array( 
			'ID'			=> $event_id,
			'post_status'	=> 'mdjm-approved'
		)
	);
	
	update_post_meta(
		$event_id,
		'_mdjm_event_last_updated_by',
		( is_user_logged_in() ) ? get_current_user_id() : 1
	);
	
	add_action( 'save_post_mdjm-event', 'mdjm_save_event_post', 10, 3 );
} // mdjm_set_event_status_mdjm_approved