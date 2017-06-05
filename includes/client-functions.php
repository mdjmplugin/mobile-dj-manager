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
 * @return	$arr	$clients	or false if no clients for the specified roles
 */
function mdjm_get_clients( $roles = array( 'client', 'inactive_client' ), $employee = false, $orderby = 'display_name', $order = 'ASC' )	{
		
	// We'll work with an array of roles
	if( ! empty( $roles ) && ! is_array( $roles ) )	{
		$roles = array( $roles );
	}

	$client_args = apply_filters( 'mdjm_get_clients_args', array(
		'role__in'  => $roles,
		'orderby'   => $orderby,
		'order'     => $order
	) );

	$all_clients = get_users( $client_args );
	
	// If we are only quering an employee's client, we need to filter	
	if( ! empty( $employee ) )	{
		foreach( $all_clients as $client )	{
			
			if( ! MDJM()->users->is_employee_client( $client->ID, $employee ) )	{
				continue;
			}
				
			$clients[] = $client;

		}

		// No clients for employee
		if( empty( $clients ) )	{
			return false;
		}
			
		$all_clients = $clients;
	}
	
	$clients = $all_clients; 
				
	return $clients;
} // mdjm_get_clients

/**
 * Returns a count of clients.
 *
 * @since	1.4
 * @param	bool	$inactive		True to include inactive clients, false to ignore.
 * @return	int		Client count.
 */
function mdjm_client_count( $inactive = true )	{
	$roles = array( 'client' );

	if ( $inactive )	{
		$roles[] = 'inactive_client';
	}

	$args = array(
		'role__in'    => $roles,
		'count_total' => true
	);

	$clients = new WP_User_Query( $args );

	return $clients->get_total();

} // mdjm_client_count

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
 * Adds a new client.
 *
 * We assume that $data is passed from the $_POST super global but $user_data can be passed.
 *
 * @since	1.3
 * @param	arr			$user_data	Array of client data. See $defaults.
 * @return	int|false	$user_id	User ID of the new client or false on failure.
 */
function mdjm_add_client( $user_data = array() )	{

	$first_name = ( ! empty( $_POST['client_firstname'] ) ? ucwords( $_POST['client_firstname'] ) : '' );
	$last_name  = ( ! empty( $_POST['client_lastname'] )  ? ucwords( $_POST['client_lastname'] )  : '' );
	$email      = ( ! empty( $_POST['client_email'] )     ? $_POST['client_email']                : '' );
	$phone      = ( ! empty( $_POST['client_phone'] )     ? $_POST['client_phone']                : '' );

	$defaults = array(
		'first_name'   => $first_name,
		'last_name'    => $last_name,
		'user_email'   => $email,
		'user_pass'    => wp_generate_password( mdjm_get_option( 'pass_length' ) ),
		'role'         => 'client',
		'client_phone' => $phone
	);

	$defaults['display_name'] = $defaults['first_name'] . ' ' . $defaults['last_name'];
	$defaults['nickname']     = $defaults['display_name'];
	$defaults['user_login']   = is_email( $defaults['user_email'] );

	$user_data = wp_parse_args( $user_data, $defaults );

	do_action( 'mdjm_pre_add_client' );

	$user_id = wp_insert_user( $user_data );

	if ( is_wp_error( $user_id ) )	{

		if ( MDJM_DEBUG == true )	{
			MDJM()->debug->log_it( 'Error creating user: ' . $user_id->get_error_message(), true );
		}

		return false;
	}

	$user_meta = array(
		'first_name'           => $user_data['first_name'],
		'last_name'            => $user_data['last_name'],
		'show_admin_bar_front' => false,
		'marketing'            => 'Y',
		'phone1'               => isset( $user_data['client_phone'] )  ? $user_data['client_phone']  : '',
		'phone2'               => isset( $user_data['client_phone2'] ) ? $user_data['client_phone2'] : ''
	);

	$user_meta = apply_filters( 'mdjm_add_client_meta_data', $user_meta );

	foreach( $user_meta as $key => $value )	{
		update_user_meta( $user_id, $key, $value );
	}

	do_action( 'mdjm_post_add_client', $user_id );

	return $user_id;

} // mdjm_add_client

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
	
	$events = mdjm_get_events( $args );
	
	return $events;
} // mdjm_get_client_events

/**
 * Retrieve the client's next event.
 *
 * @since	1.3
 * @param	int	$client_id	The user ID for the client.
 * @return	WP_Post object for clients next event, or false
 */
function mdjm_get_clients_next_event( $client_id = '' )	{
	
	$client_id = ! empty( $client_id ) ? $client_id : get_current_user_id();

	$args = array(
		'post_status'     => array( 'mdjm-approved', 'mdjm-contract', 'mdjm-enquiry', 'mdjm-unattended' ),
		'posts_per_page'  => 1,
		'meta_key'        => '_mdjm_event_date',
		'meta_query'      => array(
			'relation'    => 'AND',
			array( 
				'key'     => '_mdjm_event_client',
				'value'   => $client_id
			),
			array(
				'key'     => '_mdjm_event_date',
				'value'   => date( 'Y-m-d' ),
				'compare' => '>=',
				'type'    => 'date',
			)
		),
		'orderby'         => 'meta_value',
		'order'           => 'ASC'
	);
				
	$next_event = mdjm_get_events( $args );
		
	return apply_filters( 'mdjm_get_clients_next_event', $next_event );	
	
} // mdjm_get_clients_next_event

/**
 * Check whether the user is a client.
 *
 * @since	1.3
 * @param	int		$client_id	The ID of the user to check.
 * @return	bool	True if user has the client role, or false.
 */
function mdjm_user_is_client( $client_id )	{
	if( mdjm_get_client_events( $client_id ) )	{
		return true;
	}
	
	return false;
} // mdjm_user_is_client

/**
 * Determine if a client is active.
 *
 * @since	1.3.7
 * @param	int		$client_id	The client user ID
 * @return	bool	True if active, otherwise false.
 */
function mdjm_is_client_active( $client_id )	{

	$return = false;
	$user   = get_userdata( $client_id );

	if ( $user )	{
		if ( ! in_array( 'inactive_client', $user->roles ) )	{
			return true;
		}
	}

	return apply_filters( 'mdjm_is_client_active', $return, $client_id );

} // mdjm_is_client_active

/**
 * Activate a client a client if needed.
 *
 * @since	1.3.7
 * @param	int		$event_id	The Event ID
 * @return	void
 */
function mdjm_maybe_activate_client( $event_id )	{

	$client_id = mdjm_get_event_client_id( $event_id );

	if ( ! empty( $client_id ) )	{
		if ( ! mdjm_is_client_active( $client_id ) )	{
			mdjm_update_client_status( $client_id );
		}
	}

} // mdjm_maybe_activate_client
add_action( 'mdjm_pre_update_event_status_mdjm-unattended', 'mdjm_maybe_activate_client' );
add_action( 'mdjm_pre_update_event_status_mdjm-enquiry', 'mdjm_maybe_activate_client' );
add_action( 'mdjm_pre_update_event_status_mdjm-contract', 'mdjm_maybe_activate_client' );
add_action( 'mdjm_pre_update_event_status_mdjm-approved', 'mdjm_maybe_activate_client' );
add_action( 'mdjm_pre_update_event_status_mdjm-completed', 'mdjm_maybe_activate_client' );

/**
 * Updates a clients status.
 *
 * @since	1.3.7
 * @param	int		$client_id	The client user ID
 * @param	str		$status		'active' or 'inactive'
 * @return	void
 */
function mdjm_update_client_status( $client_id, $status = 'active' )	{

	if ( $status == 'inactive' )	{
		$role = 'inactive_client';
	} else	{
		$role = 'client';
	}

	$user = new WP_User( $client_id );
					
	$user->set_role( $role );

} // mdjm_update_client_status

/**
 * Listen for event status changes and update the client status.
 *
 * @since	1.3.7
 * @param	bool	$result		True if the event status change was successful, false if not
 * @param	int		$event_id
 * @return	void
 */
function mdjm_set_client_status_inactive( $result, $event_id )	{

	if ( ! mdjm_get_option( 'set_client_inactive' ) )	{
		return;
	}

	if ( ! $result )	{
		return;
	}
	
	$client_id = mdjm_get_event_client_id( $event_id );
	
	if ( empty( $client_id ) )	{
		return;
	}
	
	$next_event = mdjm_get_clients_next_event( $client_id );
	
	if ( ! $next_event )	{
		mdjm_update_client_status( $client_id, 'inactive' );
	}

} // mdjm_set_client_status_inactive
add_action( 'mdjm_post_update_event_status_mdjm-cancelled', 'mdjm_set_client_status_inactive', 10, 2 );
add_action( 'mdjm_post_update_event_status_mdjm-failed', 'mdjm_set_client_status_inactive', 10, 2 );
add_action( 'mdjm_post_update_event_status_mdjm-rejected', 'mdjm_set_client_status_inactive', 10, 2 );

/**
 * Retrieve a clients login.
 *
 * @since	1.3.8.4
 * @param	int		$user_id	The ID of the user to check.
 * @return	str		The login ID of the client.
 */
function mdjm_get_client_login( $user_id )	{
	$login  = '';
	$client = get_userdata( $user_id );
	
	if( $client && ! empty( $client->user_login ) )	{
		$login = $client->user_login;
	}
	
	return apply_filters( 'mdjm_client_login', $login, $user_id );
} // mdjm_get_client_login

/**
 * Retrieve a clients first name.
 *
 * @since	1.3
 * @param	int		$user_id	The ID of the user to check.
 * @return	str		The first name of the client.
 */
function mdjm_get_client_firstname( $user_id )	{
	$first_name = '';
	$client     = get_userdata( $user_id );
	
	if( $client && ! empty( $client->first_name ) )	{
		$first_name = ucwords( $client->first_name );
	}
	
	return apply_filters( 'mdjm_client_firstname', $first_name, $user_id );
} // mdjm_get_client_firstname

/**
 * Retrieve a clients last name.
 *
 * @since	1.3
 * @param	int		$user_id	The ID of the user to check.
 * @return	str		The last name of the client.
 */
function mdjm_get_client_lastname( $user_id )	{
	$last_name = '';
	$client    = get_userdata( $user_id );
	
	if( $client && ! empty( $client->last_name ) )	{
		$last_name = ucwords( $client->last_name );
	}
	
	return apply_filters( 'mdjm_client_lastname', $last_name, $user_id );
} // mdjm_get_client_lastname

/**
 * Retrieve a clients display name.
 *
 * @since	1.3
 * @param	int		$user_id	The ID of the user to check.
 * @return	str		The display name of the client.
 */
function mdjm_get_client_display_name( $user_id )	{
	$display_name = '';
	$client       = get_userdata( $user_id );
	
	if( $client && ! empty( $client->display_name ) )	{
		$display_name = ucwords( $client->display_name );
	}
	
	return apply_filters( 'mdjm_client_display_name', $display_name, $user_id );
} // mdjm_get_client_display_name

/**
 * Retrieve a clients email address.
 *
 * @since	1.3
 * @param	int		$user_id	The ID of the user to check.
 * @return	str		The email address of the client.
 */
function mdjm_get_client_email( $user_id )	{
	$client = get_userdata( $user_id );
	
	if( $client && ! empty( $client->user_email ) )	{
		$email = strtolower( $client->user_email );
	} else	{
		$email = '';
	}
	
	return apply_filters( 'mdjm_client_email', $email, $user_id );
} // mdjm_get_client_email

/**
 * Retrieve a clients address.
 *
 * @since	1.4
 * @param	int		$client_id	The ID of the client to check.
 * @return	arr		The address of the client.
 */
function mdjm_get_client_address( $client_id )	{
	
	$client  = get_userdata( $client_id );
	$address = array();

	if ( ! empty( $client->address1 ) )	{
		$address[] = stripslashes( $client->address1 );
	}
	if ( ! empty( $client->address2 ) )	{
		$address[] = stripslashes( $client->address2 );
	}
	if ( ! empty( $client->town ) )	{
		$address[] = stripslashes( $client->town );
	}
	if ( ! empty( $client->county ) )	{
		$address[] = stripslashes( $client->county );
	}
	if ( ! empty( $client->postcode ) )	{
		$address[] = stripslashes( $client->postcode );
	}

	return apply_filters( 'mdjm_get_client_address', $address, $client_id );
} // mdjm_get_client_address

/**
 * Retrieve the full address of the client.
 *
 * @since	1.3.7
 * @param	int		The client ID.
 * @return	str		The address of the client.
 */
function mdjm_get_client_full_address( $client_id )	{

	$address = mdjm_get_client_address( $client_id );
	
	$address = apply_filters( 'mdjm_client_full_address', $address );
	
	return is_array( $address ) ? implode( '<br />', $address ) : '';
} // mdjm_get_client_full_address

/**
 * Retrieve a clients phone number.
 *
 * @since	1.3
 * @param	int		$user_id	The ID of the user to check.
 * @return	str		The phone number of the client.
 */
function mdjm_get_client_phone( $user_id )	{
	$phone  = '';
	$client = get_userdata( $user_id );
	
	if( $client && ! empty( $client->phone1 ) )	{
		$phone = $client->phone1;
	}
	
	return apply_filters( 'mdjm_client_phone', $phone, $user_id );
} // mdjm_get_client_phone

/**
 * Retrieve a clients alternative phone number.
 *
 * @since	1.3.8.4
 * @param	int		$user_id	The ID of the user to check.
 * @return	str		The alternative phone number of the client.
 */
function mdjm_get_client_alt_phone( $user_id )	{
	$alt_phone = get_user_meta( $user_id, 'phone2', true );
	
	return apply_filters( 'mdjm_client_alt_phone', $alt_phone, $user_id );
} // mdjm_get_client_alt_phone

/**
 * Retrieve a clients last login timestamp.
 *
 * @since	1.3
 * @param	int		$client_id	The ID of the user to check.
 * @return	str		The phone number of the client.
 */
function mdjm_get_client_last_login( $client_id )	{
	
	$client = get_userdata( $client_id );
	
	if( $client && ! empty( $client->last_login ) )	{
		$login = $client->last_login;
	} else	{
		$login = __( 'Never', 'mobile-dj-manager' );
	}
	
	return apply_filters( 'mdjm_client_last_login', $login, $client_id );
} // mdjm_get_client_last_login

/**
 * Retrieve the client fields.
 *
 * @since	1.3
 * @param
 * @return	arr|bool	Array of client fields or false.
 */
function mdjm_get_client_fields()	{
	
	$client_fields = get_option( 'mdjm_client_fields' );
	
	if ( ! empty( $client_fields ) )	{
		return $client_fields;
	} else	{
		return false;
	}
		
} // mdjm_get_client_fields

/**
 * Output the clients details.
 *
 * @since	1.3.7
 * @param	int		$client_id	Client user ID
 * @return	str
 */
function mdjm_do_client_details_table( $client_id, $event_id = 0 )	{

	$client = get_userdata( $client_id );
	
	if ( ! $client )	{
		return;
	}

	?>
    <div id="mdjm-event-client-details" class="mdjm-hidden">
        <table class="widefat mdjm_event_client_details mdjm_form_fields">
        	<thead>
            	<tr>
                	<th colspan="3"><?php printf( __( 'Contact Details for %s', 'mobile-dj-manager' ), $client->display_name ); ?> 
                    	<span class="description">(<a href="<?php echo add_query_arg( array( 'user_id' => $client_id ), admin_url( 'user-edit.php' ) ); ?>"><?php _e( 'edit', 'mobile-dj-manager' ); ?></a>)</span></th>
                </tr>
            </thead>
            <tbody>
            	<tr>
                	<td><i class="fa fa-phone" aria-hidden="true" title="<?php _e( 'Phone', 'mobile-dj-manager' ); ?>"></i>
                    <?php echo $client->phone1; echo '' != $client->phone2 ? ' / ' . $client->phone2 : '' ?></td>

                	<td rowspan="3"><?php echo mdjm_get_client_full_address( $client->ID ); ?></td>
           		</tr>
                
                <tr>
					<td><i class="fa fa-envelope-o" aria-hidden="true" title="<?php _e( 'Email', 'mobile-dj-manager' ); ?>"></i>
                    <a href="<?php echo add_query_arg( array( 'recipient' => $client->ID, 'event_id'  => $event_id ), admin_url( 'admin.php?page=mdjm-comms' ) ); ?>"><?php echo $client->user_email; ?></a></td>
				</tr>

				<tr>
                	<td><i class="fa fa-sign-in" aria-hidden="true" title="<?php _e( 'Last Login', 'mobile-dj-manager' ); ?>"></i>
                    <?php echo mdjm_get_client_last_login( $client_id ); ?></td>                  	
           		</tr>
            </tbody>
        </table>
    </div>

    <?php

} // mdjm_do_client_details_table
