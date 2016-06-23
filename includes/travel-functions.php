<?php
/**
 * Contains all travel related functions
 *
 * @package		MDJM
 * @subpackage	Venues
 * @since		1.3.7
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) )
	exit;

/**
 * Returns the label for the selected measurement unit
 *
 * @since	1.3.7
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
