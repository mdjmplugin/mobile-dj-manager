<?php
/**
 * Contains all travel related functions
 *
 * @package		MDJM
 * @subpackage	Venues
 * @since		1.3.8
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) )
	exit;

/**
 * Calculate the travel distance
 *
 * @since	1.3.8
 * @param	int|obj		$event	The event ID or the event MDJM_Event class object.
 * @return	str			The distance to the event venue or an empty string
 */
function mdjm_travel_get_distance( $event )	{

	if ( ! is_object( $event ) )	{
		$mdjm_event = new MDJM_Event( $event );
	} else	{
		$mdjm_event = $event;
	}

	$start       = mdjm_travel_get_start( $mdjm_event );
	$destination = mdjm_travel_get_destination( $mdjm_event );

	if ( empty( $start ) || empty( $destination ) )	{
		return;
	}

	$query = mdjm_travel_build_url( $start, $destination );

	$response = wp_remote_get( $query );

	wp_die( var_export( $response, true ) );

} // mdjm_travel_get_distance

/**
 * Build the URL to retrieve the distance.
 *
 * @since	1.3.8
 * @param	str			$start			The travel start address.
 * @return	str			$destination	The travel destination address.
 */
function mdjm_travel_build_url( $start, $destination )	{

	$api_key = mdjm_travel_get_api_key();
	$prefix  = 'https://maps.googleapis.com/maps/api/distancematrix/json';
	$mode    = 'driving';
	$units   = mdjm_get_option( 'travel_units' );

	$url = add_query_arg( array(
		'units'        => $units,
		'origins'      => urlencode( $start ),
		'destinations' => urlencode( $destination ),
		'mode'         => $mode,
		//'key'          => $api_key
		),
		$prefix
	);

	return apply_filters( 'mdjm_travel_build_url', $url );

} // mdjm_travel_build_url

/**
 * Retrieve the Google API key.
 *
 * @since	1.3.8
 * @param
 * @return	str			The API key.
 */
function mdjm_travel_get_api_key()	{
	return '617372114575-g846rsgcm715pkmhkokrho9c75ii3cne.apps.googleusercontent.com';
} // mdjm_travel_get_api_key

/**
 * Retrieves the travel starting point.
 *
 * @since	1.3.8
 * @param	int|obj		$event	The event ID or the event MDJM_Event class object.
 * @return	str
 */
function mdjm_travel_get_start( $event )	{

	if ( ! is_object( $event ) )	{
		$mdjm_event = new MDJM_Event( $event );
	} else	{
		$mdjm_event = $event;
	}

	$start = mdjm_get_employee_address( $mdjm_event->get_employee() );

	if ( empty( $start ) )	{
		$start = mdjm_get_option( 'travel_primary' );
	}

	if ( empty( $start ) )	{
		return;
	}

	if ( is_array( $start ) )	{
		$start = implode( ',', $start );
	}

	return apply_filters( 'mdjm_travel_get_start', $start );

} // mdjm_travel_get_start

/**
 * Retrieves the travel destination address.
 *
 * @since	1.3.8
 * @param	int|obj		$event	The event ID or the event MDJM_Event class object.
 * @return	str
 */
function mdjm_travel_get_destination( $event )	{

	if ( ! is_object( $event ) )	{
		$mdjm_event = new MDJM_Event( $event );
	} else	{
		$mdjm_event = $event;
	}

	$destination = mdjm_get_event_venue_meta( $mdjm_event->get_venue_id(), 'address' );

	if ( ! $destination )	{
		return;
	}

	if ( is_array( $destination ) )	{
		$destination = implode( ',', $destination );
	}

	return apply_filters( 'mdjm_travel_get_destination', $destination );

} // mdjm_travel_get_destination

/**
 * Returns the label for the selected measurement unit
 *
 * @since	1.3.8
 * @param
 * @return	str
 */
function mdjm_travel_unit_label( $singular = true, $lowercase = false )	{
	$units = array(
		'singular' => array(
			'imperial' => 'Mile',
			'metric'   => 'Kilometer'
		),
		'plural'   => array(
			'imperial' => 'Miles',
			'metric'   => 'Kilometers'
		)
	);

	$type = 'singular';

	if ( ! $singular )	{
		$type = 'plural';
	}

	$return = $units[ $type ][ mdjm_get_option( 'travel_units', 'imperial' ) ];

	if ( $lowercase )	{
		$return = strtolower( $return );
	}

	return apply_filters( 'mdjm_travel_unit_label', $return );

} // mdjm_travel_unit_label
