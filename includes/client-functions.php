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

/**
 * Retrieve the client ID from the event
 *
 * @since	1.3
 * @param	int		$event_id	The event ID.
 * @return	$arr	$employees	or false if no employees for the specified roles
 */
function mdjm_get_client_id( $event_id )	{
	return mdjm_get_event_client_id( $event_id );
} // mdjm_get_client_id

/**
 * Retrieve all of this clients events.
 *
 * @param	int		$client_id	Optional: The WP userID of the client. Default to current user.
 *			str|arr	$status		Optional: Status of events that should be returned. Default any.
 *			str		$orderby	Optional: The field by which to order. Default event date.
 *			str		$order		Optional: DESC (default) | ASC
 *
 * @return	mixed	$events		WP_Post objects or false.
 */
function mdjm_get_client_events( $client_id='', $status='any', $orderby='event_date', $order='ASC' )	{
	$args = apply_filters( 'mdjm_get_client_events_args',
		array(
			'post_type'        => 'mdjm-event',
			'post_status'      => $status,
			'posts_per_page'   => -1,
			'meta_key'         => '_mdjm_' . $orderby,
			'orderby'          => 'meta_value_num',
			'order'            => $order,
			'meta_query'       => array(
				array(
					'key'      => '_mdjm_event_client',
					'value'    => !empty( $client_id ) ? $client_id : get_current_user_id(),
					'compare'  => 'IN',
				),
			)
		)
	);
	
	$events = get_posts( $args );
	
	return $events;
} // mdjm_get_client_events

/**
 * Check whether the user is a client.
 *
 * @since	1.3
 * @param	int		$user_id	The ID of the user to check.
 * @return	bool	True if user has the client role, or false.
 */
function mdjm_user_is_client( $user_id )	{
	if( user_can( 'client', $user_id ) || user_can( 'inactive_client', $user_id ) )	{
		return true;
	}
	
	return false;
} // mdjm_user_is_client

/**
 * Retrieve a clients first name.
 *
 * @since	1.3
 * @param	int		$user_id	The ID of the user to check.
 * @return	str		The first name of the client.
 */
function mdjm_get_client_firstname( $user_id )	{
	if( empty( $user_id ) )	{
		return false;
	}
	
	$client = get_userdata( $user_id );
	
	if( $client && ! empty( $client->first_name ) )	{
		$first_name = $client->first_name;
	} else	{
		$first_name = __( 'First name not set', 'mobile-dj-manager' );
	}
	
	return apply_filters( 'mdjm_get_client_firstname', $first_name, $user_id );
} // mdjm_get_client_firstname

/**
 * Retrieve a clients last name.
 *
 * @since	1.3
 * @param	int		$user_id	The ID of the user to check.
 * @return	str		The last name of the client.
 */
function mdjm_get_client_lastname( $user_id )	{
	if( empty( $user_id ) )	{
		return false;
	}
	
	$client = get_userdata( $user_id );
	
	if( $client && ! empty( $client->last_name ) )	{
		$last_name = $client->last_name;
	} else	{
		$last_name = __( 'Last name not set', 'mobile-dj-manager' );
	}
	
	return apply_filters( 'mdjm_get_client_lastname', $last_name, $user_id );
} // mdjm_get_client_lastname

/**
 * Retrieve a clients display name.
 *
 * @since	1.3
 * @param	int		$user_id	The ID of the user to check.
 * @return	str		The display name of the client.
 */
function mdjm_get_client_display_name( $user_id )	{
	if( empty( $user_id ) )	{
		return false;
	}
	
	$client = get_userdata( $user_id );
	
	if( $client && ! empty( $client->display_name ) )	{
		$display_name = $client->display_name;
	} else	{
		$display_name = __( 'Display name not set', 'mobile-dj-manager' );
	}
	
	return apply_filters( 'mdjm_get_client_display_name', $display_name, $user_id );
} // mdjm_get_client_display_name

/**
 * Retrieve a clients email address.
 *
 * @since	1.3
 * @param	int		$user_id	The ID of the user to check.
 * @return	str		The first name of the client.
 */
function mdjm_get_client_email( $user_id )	{
	if( empty( $user_id ) )	{
		return false;
	}
	
	$client = get_userdata( $user_id );
	
	if( $client && ! empty( $client->user_email ) )	{
		$email = $client->user_email;
	} else	{
		$email = __( 'Email address not set', 'mobile-dj-manager' );
	}
	
	return apply_filters( 'mdjm_get_client_email', $email, $user_id );
} // mdjm_get_client_email