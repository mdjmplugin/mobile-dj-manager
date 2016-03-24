<?php

/**
 * Formatting functions to taking care of properly formatted numbers, URLs and such
 *
 * @package		MDJM
 * @subpackage	Functions/Formatting
 * @since		1.3
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) )
	exit;

/**
 * Sanitize Amount
 *
 * Returns a sanitized amount by stripping out thousands separators.
 *
 * @since	1.3
 * @param	str		$amount		Price amount to format
 * @return	str		$amount		Newly sanitized amount
 */
function mdjm_sanitize_amount( $amount ) {
	$is_negative   = false;
	$thousands_sep = mdjm_get_option( 'thousands_separator', ',' );
	$decimal_sep   = mdjm_get_option( 'decimal', '.' );

	// Sanitize the amount
	if ( $decimal_sep == ',' && false !== ( $found = strpos( $amount, $decimal_sep ) ) ) {
		if ( ( $thousands_sep == '.' || $thousands_sep == ' ' ) && false !== ( $found = strpos( $amount, $thousands_sep ) ) ) {
			$amount = str_replace( $thousands_sep, '', $amount );
		} elseif( empty( $thousands_sep ) && false !== ( $found = strpos( $amount, '.' ) ) ) {
			$amount = str_replace( '.', '', $amount );
		}

		$amount = str_replace( $decimal_sep, '.', $amount );
	} elseif( $thousands_sep == ',' && false !== ( $found = strpos( $amount, $thousands_sep ) ) ) {
		$amount = str_replace( $thousands_sep, '', $amount );
	}

	if( $amount < 0 ) {
		$is_negative = true;
	}

	$amount   = preg_replace( '/[^0-9\.]/', '', $amount );
	$decimals = apply_filters( 'mdjm_sanitize_amount_decimals', 2, $amount );
	$amount   = number_format( (double) $amount, $decimals, '.', '' );

	if( $is_negative ) {
		$amount *= -1;
	}

	return apply_filters( 'mdjm_sanitize_amount', $amount );
} // mdjm_sanitize_amount
	
/**
 * Returns a nicely formatted amount.
 *
 * @since 1.3
 *
 * @param	str		$amount		Price amount to format
 * @param	str		$decimals	Whether or not to use decimals.  Useful when set to false for non-currency numbers.
 *
 * @return	str		$amount		Newly formatted amount or Price Not Available
 */
function mdjm_format_amount( $amount, $decimals = true, $thousands = true ) {
	$thousands_sep = mdjm_get_option( 'thousands_seperator', ',' );
	$decimal_sep   = mdjm_get_option( 'decimal', '.' );

	// Format the amount
	if ( $decimal_sep == ',' && false !== ( $sep_found = strpos( $amount, $decimal_sep ) ) ) {
		$whole = substr( $amount, 0, $sep_found );
		$part = substr( $amount, $sep_found + 1, ( strlen( $amount ) - 1 ) );
		$amount = $whole . '.' . $part;
	}

	// Strip , from the amount (if set as the thousands separator)
	if ( $thousands_sep == ',' && false !== ( $found = strpos( $amount, $thousands_sep ) ) ) {
		$amount = str_replace( ',', '', $amount );
	}

	// Strip ' ' from the amount (if set as the thousands separator)
	if ( $thousands_sep == ' ' && false !== ( $found = strpos( $amount, $thousands_sep ) ) ) {
		$amount = str_replace( ' ', '', $amount );
	}

	if ( empty( $amount ) ) {
		$amount = 0;
	}

	$decimals		 = apply_filters( 'mdjm_format_amount_decimals', $decimals ? 2 : 0, $amount );
	$thousands_sep	= apply_filters( 'mdjm_format_amount_thousands', $thousands ? $thousands_sep : '', $amount );
	
	$formatted = number_format( $amount, $decimals, $decimal_sep, $thousands_sep );

	return apply_filters( 'mdjm_format_amount', $formatted, $amount, $decimals, $decimal_sep, $thousands_sep );
} // mdjm_format_amount

/**
 * Formats the currency display
 *
 * @since	1.3
 * @param	str		$price		Price
 * @return	arr		$currency	Currencies displayed correctly
 */
function mdjm_currency_filter( $price = '', $currency = '' ) {
	if( empty( $currency ) ) {

		$currency = mdjm_get_currency();

	}

	$position = mdjm_get_option( 'currency_format', 'before' );

	$negative = $price < 0;

	if( $negative ) {
		$price = substr( $price, 1 ); // Remove proceeding "-" -
	}

	$symbol = mdjm_currency_symbol( $currency );

	if ( $position == 'before' ):
		switch ( $currency ):
			case "GBP" :
			case "BRL" :
			case "EUR" :
			case "USD" :
			case "AUD" :
			case "CAD" :
			case "HKD" :
			case "MXN" :
			case "NZD" :
			case "SGD" :
			case "JPY" :
				$formatted = $symbol . $price;
				break;
			default :
				$formatted = $currency . ' ' . $price;
				break;
		endswitch;
		$formatted = apply_filters( 'mdjm_' . strtolower( $currency ) . '_currency_filter_before', $formatted, $currency, $price );
	else :
		switch ( $currency ) :
			case "GBP" :
			case "BRL" :
			case "EUR" :
			case "USD" :
			case "AUD" :
			case "CAD" :
			case "HKD" :
			case "MXN" :
			case "SGD" :
			case "JPY" :
				$formatted = $price . $symbol;
				break;
			default :
				$formatted = $price . ' ' . $currency;
				break;
		endswitch;
		$formatted = apply_filters( 'mdjm_' . strtolower( $currency ) . '_currency_filter_after', $formatted, $currency, $price );
	endif;

	if( $negative ) {
		// Prepend the mins sign before the currency sign
		$formatted = '-' . $formatted;
	}

	return $formatted;
} // mdjm_currency_filter

/**
 * Set the number of decimal places per currency
 *
 * @since	1.3
 * @param	int		$decimals	Number of decimal places
 * @return	int		$decimals
*/
function mdjm_currency_decimal_filter( $decimals = 2 ) {

	$currency = mdjm_get_currency();

	switch ( $currency ) {
		case 'RIAL' :
		case 'JPY' :
		case 'TWD' :
		case 'HUF' :

			$decimals = 0;
			break;
	}

	return apply_filters( 'mdjm_currency_decimal_count', $decimals, $currency );
} // mdjm_currency_decimal_filter
add_filter( 'mdjm_sanitize_amount_decimals', 'mdjm_currency_decimal_filter' );
add_filter( 'mdjm_format_amount_decimals', 'mdjm_currency_decimal_filter' );

/**
 * Set the shortdate format for the given date
 *
 * @since	1.3
 * @param	int		$date		Date to format
 * @return	int		$date
*/
function mdjm_format_short_date( $date='' )	{
	
	if ( empty( $date ) )	{
		$date = current_time( 'timestamp' );
	}
	
	if( ( (string) (int) $date === $date ) && ( $date <= PHP_INT_MAX ) && ( date >= ~PHP_INT_MAX ) )	{
		$short_date = date( mdjm_get_option( 'short_date_format', 'd/m/Y' ), $date );
	} else	{
		$short_date = date( mdjm_get_option( 'short_date_format', 'd/m/Y' ), strtotime( $date ) );
	}
		
	return apply_filters( 'mdjm_format_short_date', $short_date, $date ); 
} // mdjm_format_short_date

/**
 * Generate an MDJM URL based upon the sites permalink settings.
 *
 * @since	1.3
 * @param	int		$page_id	Required: The WordPress page ID.
 * @param	bool	$permalink	Optional: Whether or not to include the permalink structure.
 * @param	bool	$echo		Optional: True to echo the URL or false (default) to return it.
 * @return	str		HTML formatted URL.
 */
function mdjm_get_formatted_url( $page_id, $permalink=true, $echo=false )	{
	// The URL
	$return = get_permalink( $page_id );
	
	if( !empty( $permalink ) )	{
		if( get_option( 'permalink_structure', false ) )	{
			$return .= '?';
		}
		else	{
			$return .= '&amp;';	
		}
	}
	
	if( !empty( $echo ) )	{
		echo $return;	
	}
	else	{
		return $return;
	}
} // mdjm_get_formatted_url