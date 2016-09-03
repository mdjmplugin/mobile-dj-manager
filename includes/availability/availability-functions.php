<?php
/**
 * Contains all availability checker related functions
 *
 * @package		MDJM
 * @subpackage	Availability
 * @since		1.3
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) )
	exit;
	
/**
 * Perform the availability lookup.
 *
 * @since	1.3
 * @param	str			$date		The requested date.
 * @param	int|arr		$employees	The employees to check.
 * @param	str|arr		$roles		The employee roles to check.
 * @return	arr|bool				Array of available employees or roles, or false if not available.
 */
function mdjm_do_availability_check( $date, $employees='', $roles='', $status='' )	{

	$check = new MDJM_Availability_Checker( $date, $employees, $roles, $status );
	
	$check->check_availability();
	
	return $check->result;
	
} // mdjm_do_availability_check

/**
 * Determine if an employee is working on the given date.
 *
 * @since	1.3
 * @param	str			$date		The date
 * @param	int			$employee	The employee ID
 * @param	str|arr		$status		The employee ID
 * @return	bool		True if the employee is working, otherwise false.
 */
function mdjm_employee_is_working( $date, $employee_id='', $status='' )	{	
	
	if ( empty( $employee_id ) && is_user_logged_in() )	{
		$employee_id = get_current_user_id();
	}
	
	if ( empty( $employee_id ) )	{
		wp_die( __( 'Ooops, an error occured.', 'mobile-dj-manager' ) );
	}
	
	if ( empty( $status ) )	{
		$status = mdjm_get_option( 'availability_status', 'any' );
	}
	
	$event = mdjm_get_events(
		array(
			'post_status'    => $status,
			'posts_per_page' => 1,
			'meta_key'       => '_mdjm_event_date',
			'meta_value'     => date( 'Y-m-d', $date ),
			'meta_query'     => array(
				'relation' => 'OR',
				array(
					'key'     => '_mdjm_event_dj',
					'value'   => $employee_id,
					'compare' => '=',
					'type'    => 'NUMERIC'
				),
				array(
					'key'     => '_mdjm_event_employees',
					'value'   => sprintf( ':"%s";', $employee_id ),
					'compare' => 'LIKE'
				)
			)
		)
	);
	
	$event = apply_filters( 'mdjm_employee_is_working', $event, $date, $employee_id );
	
	if ( $event )	{
		return true;
	}
	
	return false;
} // mdjm_employee_is_working

/**
 * Determine if an employee is on vacaion the given date.
 *
 * @since	1.3
 * @param	str		$date		The date
 * @param	int		$employee	The employee
 * @return	bool	True if the employee is on vacation, otherwise false.
 */
function mdjm_employee_is_on_vacation( $date, $employee_id = '' )	{
	global $wpdb;
	
	if( empty( $employee_id ) )	{
		$employee_id = get_current_user_id();
	}
	
	$date = date( 'Y-m-d', $date );
	
	$query = "
		SELECT COUNT(*) FROM 
		" . $wpdb->prefix . "mdjm_avail 
		WHERE DATE(date_from) = '$date' 
		AND `user_id` = '$employee_id'
	";
			  			  
	$result = $wpdb->get_var( $query );
		
	$result = apply_filters( 'mdjm_employee_is_on_vacation', $result, $date, $employee_id );
	
	if( $result )	{
		return true;
	}

	return false;
} // mdjm_employee_is_on_vacation
