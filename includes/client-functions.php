<?php
/**
 * Contains all client related functions
 *
 * @package		MDJM
 * @subpackage	Users/Clients
 * @since		1.3
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) )
	exit;
	
/**
 * Retrieve a list of all clients
 *
 * @param	str|arr	$roles		Optional: The roles for which we want to retrieve the clients from.
 *			int		$employee	Optional: Only display clients of the given employee
 *			str		$orderby	Optional: The field by which to order. Default display_name
 *			str		$order		Optional: ASC (default) | Desc
 *
 * @return	$arr	$employees	or false if no employees for the specified roles
 */
function mdjm_get_clients( $roles='', $employee='', $orderby='', $order='' )	{			
	$defaults = array(
		'roles'		=> array( 'client', 'inactive_client' ),
		'employee'	 => false,
		'orderby'	  => 'display_name',
		'order'		=> 'ASC'
	);
	
	$roles = empty( $roles ) ? $defaults['roles'] : $roles;
	$employee = empty( $employee ) ? $defaults['employee'] : $employee;
	$orderby = empty( $orderby ) ? $defaults['orderby'] : $orderby;
	$order = empty( $order ) ? $defaults['order'] : $order;
	
	// We'll work with an array of roles
	if( !empty( $roles ) && !is_array( $roles ) )
		$roles = array( $roles );
	
	$all_clients = get_users( 
		array(
			'role__in'	 => $roles,
			'orderby'	  => $orderby,
			'order'		=> $order
		)
	);
	
	// If we are only quering an employee's client, we need to filter	
	if( !empty( $employee ) )	{
		foreach( $all_clients as $client )	{
			if( !MDJM()->users->is_employee_client( $client->ID, $employee ) )
				continue;
				
			$clients[] = $client;	
		}
		// No clients for employee
		if( empty( $clients ) )
			return false;
			
		$all_clients = $clients;
	}
	
	$clients = $all_clients; 
				
	return $clients;
} // mdjm_get_clients