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
 * Retrieve all dates within the given range
 *
 * @since   1.5.6
 * @param	string	$start		The start date Y-m-d
 * @param	string	$end		The end date Y-m-d
 * @return  array   Array of all dates between two given dates
 */
function mdjm_get_all_dates_in_range( $start, $end )	{
    $start = \DateTime::createFromFormat( 'Y-m-d', $start );
    $end   = \DateTime::createFromFormat( 'Y-m-d', $end );

    $range = new \DatePeriod( $start, new \DateInterval( 'P1D' ), $end->modify( '+1 day' ) );

    return $range;
} // mdjm_get_all_dates_in_range

/**
 * Add an employee absence entry.
 *
 * @since   1.5.6
 * @param   int     $employee_id    Employee user ID
 * @param   array   $data           Array of absence data
 * @return  bool    True on success, otherwise false
 */
function mdjm_add_employee_absence( $employee_id, $data )    {
    $employee_id = absint( $employee_id );

    if ( empty( $employee_id ) || ! mdjm_is_employee( $employee_id ) )   {
        return false;
    }

    $args                = array();
    $args['employee_id'] = $employee_id;
    $args['group_id']    = isset( $data['group_id'] )  ? $data['group_id']    : md5( $employee_id . '_' . mdjm_generate_random_string() );
    $args['from_date']   = isset( $data['from_date'] ) ? $data['from_date']   : '';
    $args['to_date']     = isset( $data['to_date'] )   ? $data['to_date']     : '';
    $args['notes']       = isset( $data['notes'] )     ? sanitize_textarea_field( $data['notes'] )        : '';

    $args = apply_filters( 'mdjm_add_employee_absence_args', $args, $employee_id, $data );

    do_action( 'mdjm_before_add_employee_absence', $args, $data );

    $absence_range = mdjm_get_all_dates_in_range( $args['from_date'], $args['to_date'] );

    foreach( $absence_range as $date )	{
        $args['from_date'] = $date->format( 'Y-m-d' );
        $return = MDJM()->availability_db->add( $args );

        if ( ! $return )    {
            return false;
        }
    }

    do_action( 'mdjm_add_employee_absence', $args, $data, $return );
    do_action( 'mdjm_add_employee_absence_' . $employee_id, $args, $data, $return );

    return $return;
} // mdjm_add_employee_absence

/**
 * Remove en employee absence entry.
 *
 * @since   1.5.6
 * @param   string     $group_id
 * @return  int        The number of rows deleted or false
 */
function mdjm_remove_employee_absence( $group_id )  {
    do_action( 'mdjm_before_remove_employee_absence', $group_id );

    $deleted = MDJM()->availability_db->delete( $group_id );

    do_action( 'mdjm_remove_employee_absence', $group_id, $deleted );

    return $deleted;
} // mdjm_remove_employee_absence
 

/**
 * Perform the availability lookup.
 *
 * @since	1.3
 * @param	str			$date		The requested date.
 * @param	int|arr		$employees	The employees to check.
 * @param	str|arradd		$roles		The employee roles to check.
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
