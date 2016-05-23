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
 * Catch incoming API calls
 *
 *
 * @since		1.3
 * @remove		1.5
 * @replacement	mdjm_get_actions
 */
function mdjm_api_listener()	{

	$listener = isset( $_GET['mdjm-api'] ) ? $_GET['mdjm-api'] : '';
				
	if( empty( $listener ) )	{
		return;
	}
	
	switch( $listener )	{

		case 'MDJM_EMAIL_RCPT':

			_deprecated_function( __FUNCTION__, '1.3', 'mdjm_api_listener()' );

			$data['tracker_id'] = ! empty( $_GET['post'] ) ? $_GET['post'] : '';

			do_action( 'mdjm_track_open_email', $data );

		break;
		
		default:
			return;

	} // switch	

} // mdjm_api_listener
add_action( 'wp_loaded', 'mdjm_api_listener' );

/**
 * Format the date for the datepicker script
 *
 *
 * @since		1.3
 * @remove		1.5
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
 * @remove		1.5
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

/*
 * Displays the price in the selected format per settings
 * basically determining where the currency symbol is displayed
 *
 * @param	str		$amount		The price to to display
 * 			bool	$symbol		true to display currency symbol (default)
 * @return	str					The formatted price with currency symbol
 * @since	1.3
 * @remove	1.5
 */
function display_price( $amount, $symbol=true )	{
	_deprecated_function( __FUNCTION__, '1.3', 'display_price()' );
	
	global $mdjm_settings;
	
	if( empty( $amount ) || !is_numeric( $amount ) )
		$amount = '0.00';
	
	$symbol = ( isset( $symbol ) ? $symbol : true );
	
	$dec = $mdjm_settings['payments']['decimal'];
	$tho = $mdjm_settings['payments']['thousands_seperator'];
	
	// Currency before price
	if( $mdjm_settings['payments']['currency_format'] == 'before' )
		return ( !empty( $symbol ) ? mdjm_currency_symbol() : '' ) . number_format( $amount, 2, $dec, $tho );
	
	// Currency before price with space
	elseif( $mdjm_settings['payments']['currency_format'] == 'before with space' )
		return ( !empty( $symbol ) ? mdjm_currency_symbol() . ' ' : '' ) . number_format( $amount, 2, $dec, $tho );
		
	// Currency after price
	elseif( $mdjm_settings['payments']['currency_format'] == 'after' )
		return number_format( $amount, 2, $dec, $tho ) . ( !empty( $symbol ) ? mdjm_currency_symbol() : '' );
		
	// Currency after price with space
	elseif( $mdjm_settings['payments']['currency_format'] == 'after with space' )
		return number_format( $amount, 2, $dec, $tho ) . ' ' . ( !empty( $symbol ) ? mdjm_currency_symbol() : '' );
	
	// Default	
	return ( !empty( $symbol ) ? mdjm_currency_symbol() : '' ) . number_format( $amount, 2, $dec, $tho );
	
} // display_price

/*
 * Determine the event deposit value based upon event cost and
 * payment settings
 *
 * @param	str		$cost	Current cost of event
 * @return	str		The amount of deposit to apply.
 * @since	1.3
 * @remove	1.5
 */
function get_deposit( $cost='' )	{
	
	_deprecated_function( __FUNCTION__, '1.3', 'mdjm_calculate_deposit()' );
		
	// If no event cost is provided then we return 0
	if( empty( $cost ) )	{
		$deposit = '0.00';
	}
	
	// If we don't need a deposit per settings, return 0
	if( ! mdjm_get_option( 'deposit_type' ) )
		$deposit = '0.00';
	
	// Set fixed deposit amount
	elseif( mdjm_get_option( 'deposit_type' ) == 'fixed' )
		$deposit = number_format( mdjm_get_option( 'deposit_amount' ), 2 );
	
	// Set deposit based on % of total cost
	elseif( mdjm_get_option( 'deposit_type' ) == 'percentage' )	{
		$percentage = mdjm_get_option( 'deposit_amount' ); // The % to apply
		
		$deposit = ( !empty( $cost ) && $cost > 0 ? round( $percentage * ( $cost / 100 ), 2 ) : '0.00' );
	}
	
	return $deposit;
	
} // get_deposit

