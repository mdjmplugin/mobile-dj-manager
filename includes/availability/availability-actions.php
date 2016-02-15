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
function mdjm_availability_check( $data )	{
	if( ! isset( $data['mdjm_enquiry_date_widget'] ) )	{
		$message = 62;
	}
	
	else	{
		if( mdjm_perform_availability_lookup( $data['mdjm_enquiry_date_widget'] ) )	{
			$message = 60;
		}
		else	{
			$message = 61;
		}
	}
	
	die();
} // mdjm_availability_check
add_action( 'mdjm_availability_check_widget', 'mdjm_availability_check' );