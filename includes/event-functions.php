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
			'mdjm-completed',
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
		$types = wp_get_object_terms( $event_id, 'event-types', $args );
		
		if( !empty( $types ) )	{
			$return = $types[0]->name;
		}
	}
	
	// Return the label for the status
	return $return;
} // mdjm_get_event_type
?>