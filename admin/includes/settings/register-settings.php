<?php

/**
 * Contains all metabox functions for the mdjm-event post type
 *
 * @package		MDJM
 * @subpackage	Admin/Settings
 * @since		1.3
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) )
	exit;

/**
 * Get an option
 *
 * Looks to see if the specified setting exists, returns default if not
 *
 * @since	1.3
 * @param	$option	str		The option to retrieve
 * @param	$key	str		The option key to retrieve
 * @return	mixed
 */
function mdjm_get_option( $option ='', $key = '', $default = false )	{
	global $mdjm_options;
	
	$value = ! empty( $mdjm_options[ $option ][ $key ] ) ? $mdjm_options[ $option ][ $key ] : $default;
	$value = apply_filters( 'mdjm_get_option', $value, $option, $key, $default );
	
	return apply_filters( 'mdjm_get_option' . $key, $value, $option, $key, $default );
} // mdjm_get_option
