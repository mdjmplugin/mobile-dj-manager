<?php
/**
 * Contains all availability checker related functions called via actions executed on the front end
 *
 * @package		MDJM
 * @subpackage	Availability Checker
 * @since		1.3
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) )
	exit;

/**
 * Process availability check from widget.
 *
 * @since	1.3
 * @param
 * @return	void
 */
function mdjm_availability_check_action( $data )	{
	
	$widget = '';
	
	if( $data['mdjm_action'] == 'availability_check_widget' )	{
		$widget = '_widget';
	}
	
	if( ! isset( $data[ 'mdjm_enquiry_date' . $widget ] ) )	{
		$message = 'missing_date';
	}
	
	else	{
		$result = mdjm_do_availability_check( $data[ 'mdjm_enquiry_date' . $widget ] );
				
		if( !empty( $result['available'] ) )	{
			$message = 'available';
		}
		else	{
			$message = 'not_available';
		}
	}
	
	print_r( $result );
	
	die();
} // mdjm_availability_check_action
add_action( 'mdjm_availability_check_widget', 'mdjm_availability_check_action' );