<?php
defined( 'ABSPATH' ) or die( "Direct access to this page is disabled!!!" );

/**
 * Contains all client and employee related functions
 *
 * @package		MDJM
 * @subpackage	Users
 * @since		1.3
 */

/**
 * Retrieve the primary event employee.
 *
 * @since		1.3
 * @param		int			$event_id	The event for which we want the employee.
 * @return		int|bool				User ID of the primary employee, or false if not set.
 */
function mdjm_get_event_primary_employee( $event_id )	{
	return get_post_meta( $event_id, '_mdjm_event_dj', true );
} // mdjm_get_event_primary_employee

/**
 * Retrieve all event employees.
 *
 * Does not return the primary employee.
 *
 * @since		1.3
 * @param		int			$event_id	The event for which we want employees.
 * @return		arr|bool				Array of event employees or false if none.
 */
function mdjm_get_event_employees( $event_id )	{
	return get_post_meta( $event_id, '_mdjm_event_employees', true );
} // mdjm_get_event_employees

/**
 * Generate a list of all event employees and output as a HTML table.
 *
 * @since		1.3
 * @param
 * @return
 */
function mdjm_list_event_employees( $event_id )	{
	$employees = mdjm_get_event_employees( $event_id );
	
	$output = '';
		
	if( empty( $employees ) )	{
		$output .= __( 'No employees assigned.', 'mobile-dj-manager' );
	}
	
	else	{
		$output .= '<table width="100%" id="mdjm-event-employees">' . "\r\n";
		$output .= '<thead>' . "\r\n";
		$output .= '<tr>' . "\r\n";
		$output .= '<th style="text-align:left; width:25%;">' . __( 'Role', 'mobile-dj-manager' ) . '</th>' . "\r\n";
		$output .= '<th style="text-align:left; width:25%;">' . __( 'Employee', 'mobile-dj-manager' ) . '</th>' . "\r\n";
		$output .= '<th style="text-align:left; width:20%;">' . __( 'Wage', 'mobile-dj-manager' ) . '</th>' . "\r\n";
		$output .= '<th style="text-align:left; width:15%;">&nbsp;</th>' . "\r\n";
		$output .= '<th style="text-align:left; width:15%;">&nbsp;</th>' . "\r\n";
		$output .= '</tr>' . "\r\n";
		$output .= '</thead>' . "\r\n";
		$output .= '<tbody>' . "\r\n";
		foreach( $employees as $employee )	{
			$details = get_userdata( $employee['id'] );
			$output .= '<tr>' . "\r\n";
				$output .= '<td style="text-align:left;">' . $employee['role'] . '</td>' . "\r\n";
				$output .= '<td style="text-align:left;">' . $details->display_name . '</td>' . "\r\n";
				$output .= '<td style="text-align:left;">';
					if( MDJM()->permissions->employee_can( 'manage_txns' ) )	{
						$output .= display_price( $employee['wage'], true );
					}
					else	{
						$output .= '&mdash;';
					}
			$output .= '</td>' . "\r\n";
		}
		$output .= '</tbody>' . "\r\n";
		$output .= '</table>' . "\r\n";
	}
	
	return $output;
} // mdjm_list_event_employees

/**
 * Retrieve the primary event employee.
 *
 * @since		1.3
 * @param		int			$event_id	Required: The event to which we're adding the employee.
 * @param		arr			$args		Required: Array of detail for the employee.
 * @return		arr|bool				All employees attached to event, or false on failure.
 */
function mdjm_add_employee_to_event( $event_id, $args )	{
	$defaults = array(
		'id'		=> '',
		'role'	  => '',
		'wage'	  => '0'
	);
	
	$data = wp_parse_args( $args, $defaults );

	// If we're missing data then we fail.
	if( empty( $data['id'] ) || empty( $data['role'] ) )	{
		return false;
	}

	$employees = mdjm_get_event_employees( $event_id );
	
	$employees[] = $data;
	
	if( ! update_post_meta( $event_id, '_mdjm_event_employees', $employees ) )	{
		return false;	
	}

	return $employees;
} // mdjm_add_employee_to_event

?>