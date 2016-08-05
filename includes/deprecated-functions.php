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

/**
 * Write to the gateway log file.
 *
 * @since	1.3.8
 * @param	str		$msg		The message to be logged.
 * @param	bool	$stampit	True to log with date/time.
 * @remove	1.6
 */
function mdjm_payments_write( $msg, $stampit = false )	{
	_deprecated_function( __FUNCTION__, '1.3.8', 'mdjm_record_gateway_log()' );
	
	return mdjm_record_gateway_log( $msg, $stampit = false );
	
} // mdjm_payments_write

/*
 * Get the addons available
 *
 * @since	1.4
 * @param	int		$employee	The user ID of the Employee.
 * @param	str		$package	The slug of a package where the package contents need to be excluded.
 * @param	int		$event_id	Event ID to check if the add-on is already assigned.
 * @return	arr		Array of available addons and their details.
 */
function get_available_addons( $employee = '', $package = '', $event_id = '' )	{

	_deprecated_function( __FUNCTION__, '1.4', 'mdjm_get_available_addons()' );

	$addons  = array();
	$_addons = mdjm_get_available_addons( array(
		'employee' => $employee,
		'event_id' => $event_id,
		'package'  => $package
	) );

	if ( $_addons )	{
		foreach( $_addons as $addon )	{
			$terms = get_the_terms( $addon->ID, 'addon-category' );
			$addons[ $addon->post_name ]['cat']  = $terms[0];
			$addons[ $addon->post_name ]['slug'] = $addon->post_name;
			$addons[ $addon->post_name ]['name'] = $addon->post_title;
			$addons[ $addon->post_name ]['cost'] = mdjm_get_addon_price( $addon->ID );
			$addons[ $addon->post_name ]['desc'] = mdjm_get_addon_excerpt( $addon->ID );
		}
	}
									
	return $addons;
			
} // get_available_addons

/**
 * Get the package information
 *
 * @since	1.4
 * @param	int		$dj			Optional: The user ID of the DJ
 * @return
 */
function get_available_packages( $dj='', $price=false )	{
	
	_deprecated_function( __FUNCTION__, '1.3.8', 'mdjm_get_available_packages()' );
	
	// All packages
	$packages  = array();
	$_packages = mdjm_get_available_packages( array(
		'employee' => $dj
	) );

	if ( $_packages )	{
		foreach( $_packages as $package )	{
			$terms = get_the_terms( $package->ID, 'package-category' );
			$packages[ $package->post_name ]['cat']  = $terms[0];
			$packages[ $package->post_name ]['slug'] = $package->post_name;
			$packages[ $package->post_name ]['name'] = $package->post_title;
			$packages[ $package->post_name ]['cost'] = mdjm_get_package_price( $package->ID );
			$packages[ $package->post_name ]['desc'] = mdjm_get_package_excerpt( $package->ID );
		}
	}

	return $packages;
			
} // get_available_packages

/**
 * Get the package information for the given event
 *
 * @param	int		$event_id	The event ID
 * @param	bool	$price		True to include the package price.
 * @return	str
 */
function get_event_package( $event_id, $price=false )	{

	$return        = __( 'No package is assigned to this event', 'mobile-dj-manager' );
	$package_price = '';

	$event_package = mdjm_get_event_package( $event_id );
	
	if( ! empty( $event_package ) )	{

		$return = mdjm_get_package_name( $event_id );
	
		if ( ! empty( $price ) )	{
			$return .= ' ' . mdjm_currency_filter( mdjm_format_amount( mdjm_get_package_price( $event_package ) ) );
		}

	}

	return $return;

} // get_event_package

/**
 * Get the description of the package for the event.
 *
 * @param	int			$event_id	The event ID
 * @return	str
 */
function get_event_package_description( $event_id )	{

	$return = '';

	$package_id = mdjm_get_event_package( $event_id );

	if ( ! empty( $package_id ) )	{
		$return = mdjm_get_package_excerpt( $package_id );
	}
	
	// Event package
	$event_package = get_post_meta( $event_id, '_mdjm_event_package', true );
	
	return $return;
		
} // get_event_package_description

/**
 * Retrieve the package from the given slug
 *
 * @since	1.4
 * @param	str			$slug		The slug to search for
 * @return	obj|bool	$packages	The package details
 */
function mdjm_get_package_by_slug( $slug )	{
	return mdjm_get_package_by( 'slug', $slug );
} // mdjm_get_package_by_slug

/**
 * Retrieve the package by name
 *
 * @since	1.4
 * @param	str			$name		The name to search for
 * @return	obj|bool	$packages	The package details
 */
function mdjm_get_package_by_name( $name )	{
	return mdjm_get_package_by( 'name', $name );
} // mdjm_get_package_by_name

/**
 * Retrieve the cost of a package.
 *
 * @since	1.4
 * @param	str		$slug	The slug identifier for the package.
 * @return	int		The cost of the package.
 */
function mdjm_get_package_cost( $slug )	{
	$package = mdjm_get_package_by( 'slug', $slug );

	if ( $package )	{
		return mdjm_format_amount( mdjm_get_package_price( $package->ID ) );
	}

} // mdjm_get_package_cost

/**
 * Retrieve the package name by it's slug.
 *
 * @since	1.4
 * @param	str		$slug		Slug name of the package
 * @return	str		$package	The display name of the package	
 */
function get_package_name( $slug )	{	

	$return = false;

	$package = mdjm_get_package_by( 'slug', $slug );

	if ( $package )	{
		$return = $package->post_title;
	}
	
	return $package;
	
} // get_package_name

/**
 * Get the add-on information for the given event
 *
 * @since	1.4
 * @param	int			$event_id	The event ID
 * @param	bool		$price		True to include the add-on price.
 * @return	str			$addons		Array with add-ons details, or false if no add-ons assigned
 */
function get_event_addons( $event_id, $price=false )	{									
	return mdjm_list_event_addons( $event_id, $price );
} // get_event_addons

/**
 * Retrieve the cost of an addon.
 *
 * @since	1.4
 * @param	str		$slug	The slug identifier for the addon.
 * @return	int		The cost of the addon.
 */
function mdjm_get_addon_cost( $slug )	{

	$addon = mdjm_get_addon_by( 'slug', $slug );

	if ( $addon )	{
		return mdjm_format_amount( mdjm_get_addon_price( $addon->ID ) );
	}

} // mdjm_get_addon_cost
