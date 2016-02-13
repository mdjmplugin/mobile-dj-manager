<?php

/**
 * Contains deprecated functions.
 *
 * @package		MDJM
 * @subpackage	Functions
 * @since		1.3
 *
 * All functions should call _deprecated_function( $function, $version, $replacement = null ).
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) )
	exit;

/**
 * Format the date for the datepicker script
 *
 *
 * @since		1.3
 * @replacement	mdjm_format_datepicker_date
 */
function mdjm_jquery_short_date()	{	
	_deprecated_function( __FUNCTION__, '1.3', 'mdjm_format_datepicker_date()' );
			
	return mdjm_format_datepicker_date();
} // mdjm_jquery_short_date

/**
 * Insert the datepicker jQuery code
 * 
 *	@since: 1.1.3
 *	@called:
 *	@params 	$args =>array
 *			 	[0] = class name
 *			 	[1] = alternative field name (hidden)
 *				[2] = maximum # days from today which can be selected
 *				[3] = minimum # days past today which can be selected
 *
 *	@defaults	[0] = mdjm_date
 *				[1] = _mdjm_event_date
 *				[2] none
 *
 * @since		1.3
 * @replacement	mdjm_insert_datepicker
 */
function mdjm_jquery_datepicker_script( $args='' )	{
	_deprecated_function( __FUNCTION__, '1.3', 'mdjm_insert_datepicker()' );
	
	$class = !empty ( $args[0] ) ? $args[0] : 'mdjm_date';
	$altfield = !empty( $args[1] ) ? $args[1] : '_mdjm_event_date';
	$maxdate = !empty( $args[2] ) ? $args[2] : '';
	$mindate = !empty( $args[3] ) ? $args[3] : '';
	
	return mdjm_insert_datepicker(
		array(
			'class'		=> $class,
			'altfield'	=> $altfield,
			'mindate'	=> $mindate,
			'maxdate'	=> $maxdate
		)
	);
} // mdjm_jquery_datepicker_script