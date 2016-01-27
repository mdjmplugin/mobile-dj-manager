<?php
/**
 * Contains all venue related functions
 *
 * @package		MDJM
 * @subpackage	Venues
 * @since		1.3
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) )
	exit;

/**
 * Retrieve all venue meta data for the given event
 *
 * @since	1.3
 * @param	int		$id				Required: The post ID of the venue or the event.
 * @param	str		$field			Optional: The meta field to retrieve. Default to all (empty).
 * @return	arr		Array of all venue data
 */
function mdjm_get_event_venue_meta( $id, $field='' )	{
	$prefix = 'mdjm-venue' == get_post_type( $id ) ? '' : '_mdjm_event';
	
	switch( $field )	{
		case 'address' :
			$return[] = get_post_meta( $id, $prefix . '_venue_address1', true );
			$return[] = get_post_meta( $id, $prefix . '_venue_address2', true );
			$return[] = get_post_meta( $id, $prefix . '_venue_town', true );
			$return[] = get_post_meta( $id, $prefix . '_venue_county', true );
			$return[] = get_post_meta( $id, $prefix . '_venue_postcode', true );
		break;
		
		case 'contact' :
			$return = get_post_meta( $id, $prefix . '_venue_contact', true );
		break;
		
		case 'details' :
			$return = mdjm_get_venue_details( $id );
		break;
		
		case 'email' :
			$return = get_post_meta( $id, $prefix . '_venue_email', true );
		break;
		
		case 'name' :
			$return = empty( $prefix ) ? get_post_title( $id ) : get_post_meta( $id, $prefix . '_venue_name', true );
		break;
		
		case 'notes' :
			$return = get_post_meta( $id, $prefix . '_venue_information', true );
		break;
		
		case 'phone' :
			$return = get_post_meta( $id, $prefix . '_venue_phone', true );
		break;
		
		default :
			
		break;
	}
	
	return $return;
} // mdjm_get_event_venue_meta

/**
 * Retrieve all details for the given venue.
 *
 * @since	1.3
 * @param	int		$venue_id		Required: The post ID of the venue.
 * @return	arr		Array of all venue detail labels.
 */
function mdjm_get_venue_details( $venue_id )	{
	$details = wp_get_object_terms( $venue_id, 'venue-details' );
	
	foreach( $details as $detail ) 	{
		$venue_details[] = $detail->name;
	}
	
	return $venue_details;
} // mdjm_get_venue_details
	
?>