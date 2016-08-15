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
 * Process availability check from shortcode.
 *
 * @since	1.3
 * @param	arr		$data	$_POST form data.
 * @return	void
 */
function mdjm_availability_check_action( $data )	{
		
	if ( ! isset( $data['availability_check_date'] ) )	{
		$message = 'missing_date';
	} else	{
		$result = mdjm_do_availability_check( $data['availability_check_date'] );
				
		if ( ! empty( $result['available'] ) )	{
			$message = 'available';
		} else	{
			$message = 'not_available';
		}
	}
	
	$url = remove_query_arg( array( 'mdjm_avail_date', 'mdjm_message' ) );
	
	wp_redirect(
		add_query_arg(
			array(
				'mdjm_avail_date' => $data['availability_check_date'],
				'mdjm_message'    => $message
			),
			$url
		)
	);
	
	die();

} // mdjm_availability_check_action
add_action( 'mdjm_do_availability_check', 'mdjm_availability_check_action' );
