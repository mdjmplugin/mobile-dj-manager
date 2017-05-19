<?php
/**
 * Contains all employee related functions
 *
 * @package		MDJM
 * @subpackage	Users/Employees
 * @since		1.3
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) )
	exit;
	
/**
 * Whether or not we're running in a multi employee environment.
 *
 * @since	1.3
 * @param
 * @return	bool		True if multi employee, otherwise false
 */
function mdjm_is_employer()	{
	return mdjm_get_option( 'employer', false );
} // mdjm_is_employer

/**
 * Whether or not the employee has the priviledge.
 *
 * @since	1.3
 * @param	str		$role		The role to check
 * @param	int		$user_id	The user ID of the employee
 * @return	bool	True if multi employee, otherwise false
 */
function mdjm_employee_can( $role, $user_id = '' )	{
	return MDJM()->permissions->employee_can( $role, $user_id );
} // mdjm_employee_can

/**
 * Whether or not the user is an employee.
 *
 * @since	1.3
 * @param	int		$user_id	The ID of the user to check
 * @return	bool	True if an employee, otherwise false
 */
function mdjm_is_employee( $user_id = '' )	{
	
	$user_id = ! empty( $user_id ) ? $user_id : get_current_user_id();
	
	return user_can( $user_id, 'mdjm_employee' );
} // mdjm_is_employee

/**
 * Whether or not the user is an MDJM Admin.
 *
 * @since	1.3
 * @param	int		$user_id	The ID of the user to check
 * @return	bool	True if an admin, otherwise false
 */
function mdjm_is_admin( $user_id = '' )	{
	
	$user_id = ! empty( $user_id ) ? $user_id : get_current_user_id();
	
	return user_can( $user_id, 'manage_mdjm' );
} // mdjm_is_admin

/**
 * Display a dropdown select list with all employees. The label must be handled seperately.
 *
 * @param	arr		$args			Settings for the dropdown. See $defaults
 *									'role' (str|arr)		Optional: Only display employees with the given role. Default empty (all).
 *									'name' (str)			Optional: The name of the input. Defaults to '_mdjm_employees'
 *									'id' (str)				Optional: ID for the field (uses name if not present)
 *									'class' (str)			Optional: Class of the input field
 *									'selected' (str)		Optional: Initially selected option
 *									'first_entry' (str)		Optional: First entry to be displayed (default none)
 *									'first_entry_val' (str)	Optional: First entry value. Only valid if first_entry is set
 *									'multiple' (bool)		Optional: Whether multiple options can be selected
 *									'group' (bool)			Optional: True to group employees by role
 *									'structure' (bool)		Optional: True outputs the <select> tags, false just the <options>
 *									'exclude' (int|arr)		Optional: Employee ID's to exclude
 *									'echo' (bool)           Optional: Echo the HTML output (default) or false to return as $output
 *
 * @return	str		$output			The HTML output for the dropdown list
 */
function mdjm_employee_dropdown( $args='' )	{
	global $wp_roles;
	
	// Define the default args for the dropdown
	$defaults = array(
		'role'                => '',
		'name'                => '_mdjm_employees',
		'id'				  => '', // Uses name if not set
		'class'               => '',
		'selected'            => '',
		'first_entry'         => '',
		'first_entry_val'     => '0',
		'multiple'			=> false,
		'group'               => false,
		'structure'           => true,
		'exclude'			 => false,
		'echo'                => true
	);
	
	// Merge default args with those passed to function
	$args = wp_parse_args( $args, $defaults );
	
	$args['id'] = ! empty( $args['id'] ) ? $args['id'] : $args['name'];
	
	if( !empty( $args['exclude'] ) && !is_array( $args['exclude'] ) )
		$args['exclude'] = array( $args['exclude'] );
	
	// We'll store the output here
	$output = '';
	
	// Start the structure
	if( !empty( $args['structure'] ) )	{
		$output .= '<select name="' . $args['name'];
		if( !empty( $args['multiple'] ) ) 
			$output .= '[]';
			
		$output .= '"	id="' . $args['id'] . '"';
		
		if( !empty( $args['class'] ) )
			$output .= ' class="' . $args['class'] . '"';
			
		if( !empty( $args['multiple'] ) )
			$output .= ' multiple="multiple"';
			
		$output .= '>' . "\r\n";
	}
	
	$employees = mdjm_get_employees( $args['role'] );
	
	if( empty( $employees ) )	{
		$output .= '<option value="">' . __( 'No employees found', 'mobile-dj-manager' ) . '</option>' . "\r\n";
	}
	else	{
		if( !empty( $args['first_entry'] ) )	{
			$output .= '<option value="' .  $args['first_entry_val'] . '">'; 
			$output .= $args['first_entry'] . '</option>' . "\r\n";
			
		}
		$results = new stdClass();
		$results->role = array();
		foreach( $employees as $employee )	{
			if( $employee->roles[0] == 'administrator' && ! empty( $employee->roles[1] ) )	{
				$employee->roles[0] = $employee->roles[1];
			} else	{
				$employee->roles[0] = 'dj';
			}
			
			if( !empty( $args['exclude'] ) && in_array( $employee->ID, $args['exclude'] ) )
				continue;
			
			$results->role[$employee->roles[0]][] = $employee;
		}
		// Loop through the roles and employees to create the output
		foreach( $results->role as $role => $userobj )	{
			if( !empty( $args['group'] ) )
				$output .= '<optgroup label="' . translate_user_role( $wp_roles->roles[$role]['name'] ) . '">' . "\r\n";
			
			foreach( $userobj as $user )	{
				$output .= '<option value="' . $user->ID . '"';
				
				if( !empty( $args['selected'] ) && $user->ID == $args['selected'] )
					$output .= ' selected="selected"';
				
				$output .= '>' . $user->display_name . '</option>' . "\r\n";	
			}
			
			if( !empty( $args['group'] ) )
				$output .= '</optgroup>' . "\r\n";
		}
	}
	
	// End the structure
	if( $args['structure'] == true )
		$output .= '</select>' . "\r\n";
	
	if( !empty( $args['echo'] ) )
		echo $output;
		
	else
		return $output;
} // mdjm_employee_dropdown

/**
 * Adds a new employee and assigns the role
 *
 * @param	arr		$post_data
 *						'first_name'	Required: The first name of the employee.
 *						'last_name'		Required: The last name of the employee.
 *						'user_email'	Required: The email address of the employee.
 *						'employee_role' Required: The role that the employee should be assigned.
 *
 * @return	void
 */
function mdjm_add_employee( $post_data )	{
	
	if( empty( $post_data['first_name'] ) || empty( $post_data['last_name'] ) || empty( $post_data['user_email'] ) || empty( $post_data['employee_role'] ) )	{
		return false;
	}
	
	// We don't need to execute the hooks for user saves
	remove_action( 'user_register', array( 'MDJM_Users', 'save_custom_user_fields' ), 10, 1 );
	remove_action( 'personal_options_update', array( 'MDJM_Users', 'save_custom_user_fields' ) );
	remove_action( 'edit_user_profile_update', array( 'MDJM_Users', 'save_custom_user_fields' ) );
	
	// Default employee settings
	$userdata = array(
		'user_email'            => $post_data['user_email'],
		'user_login'            => $post_data['user_email'],
		'user_pass'		     => wp_generate_password( $GLOBALS['mdjm_settings']['clientzone']['pass_length'] ),
		'first_name'            => ucfirst( $post_data['first_name'] ),
		'last_name'             => ucfirst( $post_data['last_name'] ),
		'display_name'          => ucfirst( $post_data['first_name'] ) . ' ' . ucfirst( $post_data['last_name'] ),
		'role'                  => $post_data['employee_role'],
		'show_admin_bar_front'  => false
	);
	
	/**
	 * Insert the new employee into the DB.
	 * Fire a hook on the way to allow filtering of the $default_userdata array.
	 */
	$userdata = apply_filters( 'mdjm_new_employee_data', $userdata );
	
	$user_id = wp_insert_user( $userdata );
	
	// Re-add our custom user save hooks
	add_action( 'user_register', array( 'MDJM_Users', 'save_custom_user_fields' ), 10, 1 );
	add_action( 'personal_options_update', array( 'MDJM_Users', 'save_custom_user_fields' ) );
	add_action( 'edit_user_profile_update', array( 'MDJM_Users', 'save_custom_user_fields' ) );
	
	// Success
	if( !is_wp_error( $user_id ) )	{
		if( MDJM_DEBUG == true )	{
			MDJM()->debug->log_it( 
				'Adding employee ' . ucfirst( $post_data['first_name'] ) . ' ' . ucfirst( $post_data['last_name'] ) . ' with user ID ' . $user_id,
				true
			);
		}
		
		mdjm_send_employee_welcome_email( $user_id, $userdata );
		
		return true;
	}
	else	{
		if( MDJM_DEBUG == true )
			MDJM()->debug->log_it( 'ERROR: Unable to add employee. ' . $user_id->get_error_message(), true );
			
		return false;
	}			
} // mdjm_add_employee

/**
 * Send a welcome email to a new employee.
 *
 * @since	1.3
 * @param	int		$user_id	The new user ID
 * @param	arr		$userdata	Array of new user data
 * @return	void
 */
function mdjm_send_employee_welcome_email( $user_id, $userdata )	{
	
	global $wp_roles;
	
	$subject = sprintf( __( 'Your Employee Details from %s', 'mobile-dj-manager' ), mdjm_get_option( 'company_name' ) );
	$subject = apply_filters( 'mdjm_new_employee_subject', $subject, $user_id, $userdata );
	
	$message =  '<p>' . sprintf( __( 'Hello %s,', 'mobile-dj-manager' ), $userdata['first_name'] ) . '</p>' . "\r\n" .
				
				'<p>' . sprintf( 
							__( 'Your user account on the <a href="%s">%s website</a> is now ready for use.', 'mobile-dj-manager' ),
							get_bloginfo( 'url' ),
							mdjm_get_option( 'company_name' )
						) . '</p>' . "\r\n" .
				
				'<hr />' . "\r\n" .
				
				'<p>' . sprintf( __( '<strong>Username</strong>: %s', 'mobile-dj-manager' ), $userdata['user_login'] ) .
						'<br />' . "\r\n" .
						sprintf( __( '<strong>Password</strong>: %s', 'mobile-dj-manager' ), $userdata['user_pass'] ) .
						'<br />' . "\r\n" .
						sprintf( __( '<strong>Employee Role</strong>: %s', 'mobile-dj-manager' ), translate_user_role( $wp_roles->roles[ $userdata['role'] ]['name'] ) ) .
						'<br />' . "\r\n" .
						sprintf( __( '<strong>Login URL</strong>: <a href="%1$s">%1$s</a>', 'mobile-dj-manager' ), admin_url() ) .
				'</p>' . "\r\n" .
				
				'<p>' . __( 'Thanks', 'mobile-dj-manager' ) . 
						'<br />' . "\r\n" .
						mdjm_get_option( 'company_name' ) .
				'</p>';
	
	$message = apply_filters( 'mdjm_new_employee_message', $message, $user_id, $userdata );
					
	$email_args = apply_filters( 'mdjm_new_employee_email',
		array(
			'to_email'		=> $userdata['user_email'],
			'subject'		=> $subject,
			'track'			=> false,
			'message'		=> $message
		)
	);
	
	mdjm_send_email_content( $email_args );
	
} // mdjm_send_employee_welcome_email

/**
 * Retrieve a list of all employees
 *
 * @param	str|arr	$roles		Optional: The roles for which we want to retrieve the employees from.
 *			str		$orderby	Optional: The field by which to order. Default display_name
 *			str		$order		Optional: ASC (default) | Desc
 *
 * @return	$arr	$employees	or false if no employees for the specified roles
 */
function mdjm_get_employees( $roles='', $orderby='display_name', $order='ASC' )	{			
	// We'll work with an array of roles
	if( ! empty( $roles ) && ! is_array( $roles ) )
		$roles = array( $roles );
				
	// Define the default query	
	if( empty( $roles ) )
		$roles = mdjm_get_roles();
				
	// This array will store our employees
	$employees = array();
				
	// Create and execute the WP_User_Query for each role	
	foreach( $roles as $role_id => $role_name )	{
		$args = array(
			'role'    => is_numeric( $role_id ) ? $role_name : $role_id,
			'orderby' => $orderby,
			'order'   => $order
		);
									
		// Execute the query
		$employee_query = new WP_User_Query( $args );
		
		// Merge the results into our $employees array
		$results = $employee_query->get_results();
		
		$employees = array_merge( $employees, $results );
		$employees = array_unique( $employees, SORT_REGULAR );
	}
	
	return $employees;
} // mdjm_get_employees

/**
 * Returns a count of employees.
 *
 * @since	1.4
 * @return	int		Employee count.
 */
function mdjm_employee_count()	{

	$mdjm_roles = mdjm_get_roles();
	$roles      = array();

	foreach ( $mdjm_roles as $role_id => $role_name )	{
		$roles[] = $role_id;
	}

	$args = array(
		'role__in'    => $roles,
		'count_total' => true
	);

	$employees = new WP_User_Query( $args );

	return $employees->get_total();

} // mdjm_employee_count

/**
 * Retrieve the primary event employee.
 *
 * @since		1.3
 * @param		int			$event_id	The event for which we want the employee.
 * @return		int|bool				User ID of the primary employee, or false if not set.
 */
function mdjm_get_event_primary_employee( $event_id )	{
	return mdjm_get_event_primary_employee_id( $event_id );
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
 * Retrieve all event employees data.
 *
 * Does not return the primary employees data.
 *
 * @since		1.3
 * @param		int			$event_id	The event for which we want employees data.
 * @return		arr|bool				Array of event employees data or false if none.
 */
function mdjm_get_event_employees_data( $event_id )	{
	return get_post_meta( $event_id, '_mdjm_event_employees_data', true );
} // mdjm_get_event_employees_data

/**
 * Retrieve an employees first name.
 *
 * @since	1.3
 * @param	int		$user_id	The ID of the user to check.
 * @return	str		The first name of the employee.
 */
function mdjm_get_employee_firstname( $user_id )	{
	$first_name = '';
	$employee   = get_userdata( $user_id );

	if( $employee && ! empty( $employee->first_name ) )	{
		$first_name = ucwords( $employee->first_name );
	}
	
	return apply_filters( 'mdjm_employee_firstname', $first_name, $user_id );
} // mdjm_get_employee_firstname

/**
 * Retrieve an employees last name.
 *
 * @since	1.3
 * @param	int		$user_id	The ID of the user to check.
 * @return	str		The last name of the employee.
 */
function mdjm_get_employee_lastname( $user_id )	{
	$last_name = '';
	$employee  = get_userdata( $user_id );
	
	if( $employee && ! empty( $employee->last_name ) )	{
		$last_name = ucwords( $employee->last_name );
	}
	
	return apply_filters( 'mdjm_employee_lastname', $last_name, $user_id );
} // mdjm_get_employee_lastname

/**
 * Retrieve an employees display name.
 *
 * @since	1.3
 * @param	int		$user_id	The ID of the user to check.
 * @return	str		The display name of the employee.
 */
function mdjm_get_employee_display_name( $user_id = '' )	{
	if( empty( $user_id ) )	{
		return false;
	}

	$display_name = '';
	$employee     = get_userdata( $user_id );
	
	if( $employee && ! empty( $employee->display_name ) )	{
		$display_name = ucwords( $employee->display_name );
	}
	
	return apply_filters( 'mdjm_employee_display_name', $display_name, $user_id );
} // mdjm_get_employee_display_name

/**
 * Retrieve an employees phone number.
 *
 * @since	1.3
 * @param	int		$user_id	The ID of the user to check.
 * @return	str		The phone number of the employee.
 */
function mdjm_get_employee_phone( $user_id )	{
	$phone  = '';
	$employee = get_userdata( $user_id );
	
	if( $employee && ! empty( $employee->phone1 ) )	{
		$phone = $employee->phone1;
	}
	
	return apply_filters( 'mdjm_employee_phone', $phone, $user_id );
} // mdjm_get_employee_phone

/**
 * Retrieve an employees alternative phone number.
 *
 * @since	1.3.8.4
 * @param	int		$user_id	The ID of the user to check.
 * @return	str		The alternative phone number of the employee.
 */
function mdjm_get_employee_alt_phone( $user_id )	{
	$alt_phone = get_user_meta( $user_id, 'phone2', true );
	
	return apply_filters( 'mdjm_employee_alt_phone', $alt_phone, $user_id );
} // mdjm_get_employee_alt_phone

/**
 * Retrieve an employees post code.
 *
 * @since	1.3
 * @param	int		$user_id	The ID of the user to check.
 * @return	str		The display name of the employee.
 */
function mdjm_get_employee_post_code( $user_id = '' )	{
	if( empty( $user_id ) )	{
		return false;
	}
	
	$employee = get_userdata( $user_id );
	$postcode = '';

	if( $employee && ! empty( $employee->postcode ) )	{
		$postcode = stripslashes( $employee->postcode );
	}
	
	return apply_filters( 'mdjm_get_employee_post_code', $postcode, $user_id );
} // mdjm_get_employee_post_code

/**
 * Retrieve an employees address.
 *
 * @since	1.3
 * @param	int		$user_id	The ID of the user to check.
 * @return	str		The address of the employee.
 */
function mdjm_get_employee_address( $user_id = '' )	{
	if( empty( $user_id ) )	{
		return false;
	}
	
	$employee = get_userdata( $user_id );
	$address  = array();

	if ( ! empty( $employee->address1 ) )	{
		$address[] = stripslashes( $employee->address1 );
	}
	if ( ! empty( $employee->address2 ) )	{
		$address[] = stripslashes( $employee->address2 );
	}
	if ( ! empty( $employee->town ) )	{
		$address[] = stripslashes( $employee->town );
	}
	if ( ! empty( $employee->county ) )	{
		$address[] = stripslashes( $employee->county );
	}
	if ( ! empty( $employee->postcode ) )	{
		$address[] = stripslashes( $employee->postcode );
	}

	return apply_filters( 'mdjm_get_employee_address', $address, $user_id );
} // mdjm_get_employee_address

/**
 * Retrieve an employees email address.
 *
 * @since	1.3
 * @param	int		$user_id	The ID of the user to check.
 * @return	str		The email address of the employee.
 */
function mdjm_get_employee_email( $user_id )	{
	if( empty( $user_id ) )	{
		return false;
	}
	
	$employee = get_userdata( $user_id );
	
	if( $employee && ! empty( $employee->user_email ) )	{
		$email = $employee->user_email;
	} else	{
		$email = __( 'Email address not set', 'mobile-dj-manager' );
	}
	
	return apply_filters( 'mdjm_get_employee_email', $email, $user_id );
} // mdjm_get_employee_email

/**
 * Generate a list of all event employees and output as a HTML table.
 *
 * @since		1.3
 * @param
 * @return
 */
function mdjm_do_event_employees_list_table( $event_id )	{
	global $wp_roles;
	
	$employees = mdjm_get_event_employees_data( $event_id );
		
	if( ! $employees )	{
		return;
	}

	?>
    <table class="widefat mdjm_event_employee_list">
        <thead>
            <tr>
                <th style="text-align:left; width:25%;"><?php _e( 'Role', 'mobile-dj-manager' ); ?></th>
                <th style="text-align:left; width:25%;"><?php _e( 'Name', 'mobile-dj-manager' ); ?></th>
                <th style="text-align:left; width:20%;"><?php _e( 'Wage', 'mobile-dj-manager' ); ?></th>
                <th style="text-align:left;"><?php _e( 'Status', 'mobile-dj-manager' ); ?></th>

            </tr>
        </thead>

        <tbody>
            <?php foreach( $employees as $employee ) : ?>

                <tr class="mdjm_field_wrapper">
                    <td><?php echo translate_user_role( $wp_roles->roles[ $employee['role'] ]['name'] ); ?></td>
                    <td><?php echo mdjm_get_employee_display_name( $employee['id'] ); ?></td>
                    <td>
                        <?php if ( mdjm_get_option( 'enable_employee_payments' ) && mdjm_employee_can( 'manage_txns' ) ) : ?>
                            <?php echo mdjm_currency_filter( mdjm_sanitize_amount( $employee['wage'] ) ); ?>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php if ( mdjm_employee_can( 'mdjm_event_edit' ) ) : ?>
                            <?php if ( 'paid' != mdjm_get_employees_event_payment_status( $event_id, $employee['id'] ) ) : ?>

                                <?php printf( __( '<a class="button button-secondary button-small remove_event_employee" style="margin: 6px 0 10px;" data-employee_id="%1$d" id="remove-employee-%1$d">Remove</a>', 'mobile-dj-manager' ), $employee['id'] ); ?>


                            <?php elseif ( mdjm_get_option( 'enable_employee_payments' ) ) : ?>

                                <?php printf( __( '<a href="%s">Paid</a>', 'mobile-dj-manager' ), ! empty( $employee['txn_id'] ) ? get_edit_post_link( $employee['txn_id'] ) : '' ); ?>

                            <?php endif; ?>
                        <?php endif; ?>
                    </td>
                </tr>

            <?php endforeach; ?>
        </tbody>

    </table>

	<?php

} // mdjm_do_event_employees_list_table

/**
 * Add an employee event employee.
 *
 * @since		1.3
 * @param		int			$event_id	Required: The event to which we're adding the employee.
 * @param		arr			$args		Required: Array of detail for the employee.
 * @return		arr|bool				All employees attached to event, or false on failure.
 */
function mdjm_add_employee_to_event( $event_id, $args )	{
	
	$defaults = array(
		'id'             => '',
		'role'           => '',
		'wage'           => '0',
		'payment_status' => 'unpaid'
	);
	
	$data = wp_parse_args( $args, $defaults );
	
	$data['wage'] = $data['wage'];

	// If we're missing data then we fail.
	if( empty( $data['id'] ) || empty( $data['role'] ) )	{
		return false;
	}

	$employees         = mdjm_get_event_employees( $event_id );
	$employees_data    = mdjm_get_event_employees_data( $event_id );
	
	if ( empty( $employees ) )	{
		$employees = array();
	}
	
	$mdjm_txn = new MDJM_Txn();
	
	$mdjm_txn->create(
		array(
			'post_title'     => sprintf( __( 'Wage payment to %s for %d', 'mobile-dj-manager' ), mdjm_get_employee_display_name( $data['id'] ), $event_id ),
			'post_status'    => 'mdjm-expenditure',
			'post_author'    => 1,
			'post_parent'    => $event_id
		),
		array(
			'_mdjm_txn_status'    => 'Pending',
			'_mdjm_payment_to'    => $data['id'],
			'_mdjm_txn_total'     => $data['wage']
		)
	);
	
	if ( ! empty( $mdjm_txn ) )	{
		$data['txn_id'] = $mdjm_txn->ID;
	}
	
	mdjm_set_txn_type( $mdjm_txn->ID, mdjm_get_txn_cat_id( 'slug', 'mdjm-employee-wages' ) );
	
	array_push( $employees, $data['id'] );
	$employees_data[ $data['id'] ] = $data;
	
	if( update_post_meta( $event_id, '_mdjm_event_employees', $employees ) && update_post_meta( $event_id, '_mdjm_event_employees_data', $employees_data ) )	{
		return true;
	} else	{
		return false;
	}
	
} // mdjm_add_employee_to_event

/**
 * Remove an employee from an event.
 *
 * @since		1.3
 * @param		int			$employee_id	The employee user ID.
 * @param		int			$event_id		The event to which we're adding the employee.
 * @return		void
 */
function mdjm_remove_employee_from_event( $employee_id, $event_id )	{
	
	$employees      = mdjm_get_event_employees( $event_id );
	$employees_data = mdjm_get_event_employees_data( $event_id );
	
	if ( empty( $employees ) )	{
		$employees = array();
	}
	
	if( ! empty( $employees ) )	{
		foreach( $employees as $key => $employee )	{
			if( $employee == $employee_id )	{
				unset( $employees[ $key ] );
			}
		}
		
		update_post_meta( $event_id, '_mdjm_event_employees', $employees );
	}
	
	if ( ! empty( $employees_data ) )	{
		
		remove_action( 'save_post_mdjm-transaction', 'mdjm_save_txn_post', 10, 3 );
		
		foreach( $employees_data as $key => $employee_data )	{
			if( $employee_data['id'] == $employee_id )	{
				if ( ! empty( $employee_data['txn_id'] ) )	{
					wp_delete_post( $employee_data['txn_id'] );
				}
				unset( $employees_data[ $key ] );
			}
		}

		add_action( 'save_post_mdjm-transaction', 'mdjm_save_txn_post', 10, 3 );
		update_post_meta( $event_id, '_mdjm_event_employees_data', $employees_data );

	}
	
} // mdjm_remove_employee_from_event

/**
 * Set the role for the given employees
 *
 * @param	int|arr		$employees		Required: Single user ID or array of user ID's to adjust
 *			str			$role			Required: The role ID to which the users will be moved
 *
 * @return	
 */
function mdjm_set_employee_role( $employees, $role )	{
				
	if( !is_array( $employees ) )	{
		$employees = array( $employees );
	}
	
	foreach( $employees as $employee )	{
		
		// Fetch the WP_User object of our user.
		$user = new WP_User( $employee );
		
		if( ! empty( $user ) )	{
			MDJM()->debug->log_it( sprintf( __( 'Updating user role for %d to $s', 'mobile-dj-manager' ), $employee, $role ), true );	
		}
		
		// Replace the current role with specified role
		$user->set_role( $role );
		
	}
	
} // mdjm_set_employee_role

/**
 * Retrieve the employees events
 *
 * @since	1.3
 * @param	int		$employee_id	The employees user ID. Uses current user ID if not value is passed.
 * @param	arr		$args			Array of possible arguments. See $defaults.
 * @return	mixed	$events			False if no events, otherwise an object array of all employees events.
 */
function mdjm_get_employee_events( $employee_id = '', $args = array() )	{
	
	$employee_id = ! empty( $employee_id ) ? $employee_id : get_current_user_id();
	
	$employee_query	= array(
		'relation' => 'OR',
		array( 
			'key'     => '_mdjm_event_dj',
			'value'   => $employee_id,
			'compare' => '=',
			'type'    => 'numeric'
		),
		array(
			'key'     => '_mdjm_event_employees',
			'value'   => sprintf( ':"%s";', $employee_id ),
			'compare' => 'LIKE'
		)
	);
	
	$defaults = array(
		'post_type'      => 'mdjm-event',
		'post_status'    => 'any',
		'posts_per_page' => -1,
		'meta_query'     => $employee_query,
		'date'           => false, // Required if checking for events on a specific date. Parse an array if querying a date range
		'date_compare'   => '='
	);
		
	$args = wp_parse_args( $args, $defaults );
	
	$order_by_num = array( '_mdjm_event_date', '_mdjm_event_dj', '_mdjm_event_client' );
	
	if ( ! empty( $args['date'] ) )	{
		$date_query = array(
			'relation' => 'AND',
			array(
				'key'     => '_mdjm_event_date',
				'value'   => $args['date'],
				'type'    => 'DATE',
				'compare' => $args['date_compare']
			)
		);
		
		$args['meta_query'] = array_merge( $employee_query, $date_query );
		
	}
	
	// We don't need the date args any longer
	unset( $args['date'], $args['date_compare'] );
	
	$events = get_posts( $args );
	
	// Return the results
	if ( $events )	{
		return $events;
	} else	{
		return false;
	}
	
} // mdjm_get_employee_events

/**
 * Get the count of an employees events.
 *
 * @since	1.3
 * @param	int		$employee_id	The employees user ID. Uses current user ID if not value is passed.
 * @param	arr		$args			Array of possible arguments. See mdjm_get_employee_events()->$defaults.
 * @return	mixed	$events			False if no events, otherwise an object array of all employees events.
 */
function mdjm_count_employee_events( $employee_id = '', $args = array() )	{
	
	$count = 0;
		
	$events = mdjm_get_employee_events( $employee_id, $args );
	
	if ( $events )	{
		$count = count( $events );
	}
	
	return $count;
	
} // mdjm_count_employee_events

/**
 * Get the employees next event.
 *
 * @since	1.3
 * @param	int		$employee_id	The employees user ID. Uses current user ID if not value is passed.
 * @param	arr		$args			Array of possible arguments. See mdjm_get_employee_events()->$defaults.
 * @return	mixed	$event			False if no events, otherwise an object array of the next event.
 */
function mdjm_get_employees_next_event( $employee_id = '' )	{
	
	$args = array(
		'post_status'		=> mdjm_active_event_statuses(),
		'posts_per_page'	=> 1,
		'meta_key'			=> '_mdjm_event_date',
		'meta_value'		=> date( 'Y-m-d' ),
		'meta_compare'		=> '>=',
		'orderby'			=> 'meta_value',
		'order'				=> 'ASC'
	);
	
	$next_event = mdjm_get_employee_events( $employee_id, $args );
	
	if ( $next_event )	{
		return $next_event[0];
	} else	{
		false;
	}
	
} // mdjm_get_employees_next_event

/**
 * Determine if an employee is working a specific event
 *
 * @since	1.3
 * @param	int		$event_id		The event ID
 * @param	int		$employee_id	The employee user ID
 * @return	bool	True if working the event, otherwise false
 */
function mdjm_employee_working_event( $event_id, $employee_id = '' )	{
	
	$event_employees = mdjm_get_event_employees( $event_id );
	
	$employee_id = ! empty( $employee_id ) ? $employee_id : get_current_user_id();
	
	if ( ! $event_employees || ! in_array( $employee_id, $event_employees ) )	{
		return false;
	} else	{
		return true;
	}
	
} // mdjm_employee_working_event

/**
 * Retrieve a list of the employee's clients.
 *
 * @since	1.3
 * @param	int			$employee		The user ID of the employee.
 * @param	bool		$active_only	True to only query active clients, false for all.
 * @param	str			$return			Return resultset as WP_User OBJECTS or ARRAY of user ID's
 * @return	arr|bool	Array of client user ID's or 
 */
function mdjm_get_employee_clients( $employee_id = '', $active_only = true, $return = 'OBJECT' )	{
	
	$employee_id         = ! empty( $employee_id ) ? $employee_id : get_current_user_id();
	$args['post_status'] = ! empty( $active_only ) ? 'any' : mdjm_active_event_statuses();
	
	// If we only want active events set an extra check for the event date.
	/*if ( ! empty( $active_only ) )	{
		$args['date']         = date( 'Y-m-d');
		$args['date_compare'] = '>=';
	}*/
	
	$events = mdjm_get_employee_events( $employee_id, $args );
	
	if ( ! $events )	{
		return false;
	}
	
	$clients = array();
	
	// Loop through the events and retrieve the client.
	foreach ( $events as $event )	{
		$clients[] = mdjm_get_event_client_id( $event->ID );
	}
	
	if ( empty( $clients ) )	{
		return false;
	}
	
	$clients = array_unique( $clients );
	$clients = apply_filters( 'mdjm_get_employee_clients', $clients, $employee_id );
	
	if ( $return != 'ARRAY' )	{
		
		foreach ( $clients as $client )	{
			$client_objects[] = get_userdata( $client );
		}
		
		$clients = $client_objects;
		
	}
	
	return $clients;
	
} // mdjm_get_employee_clients

/**
 * Get an employees wage for an event.
 *
 * @since	1.3
 * @param	int		$event_id		The event ID
 * @param	int		$employee_id	The employee user ID
 * @return	str		Employees wage for the event.
 */
function mdjm_get_employees_event_wage( $event_id, $employee_id = '' )	{
	
	$employee_id = ! empty ( $employee_id ) ? $employee_id : get_current_user_id();
	
	$wage = 0;
	
	if ( $employee_id == mdjm_get_event_primary_employee( $event_id ) )	{
		
		$wage = get_post_meta( $event_id, '_mdjm_event_dj_wage', true );
	
	} else	{
		
		$employees_data = mdjm_get_event_employees_data( $event_id );

		if ( $employees_data )	{

			foreach( $employees_data as $employee_data )	{
				
				if ( $employee_data['id'] == $employee_id )	{
					
					if ( ! empty ( $employee_data['wage'] ) )	{
						$wage = $employee_data['wage'];
					}
					
				}

			}

		}
	
	}
	
	return $wage;
	
} // mdjm_get_employees_event_wage

/**
 * Checks if event employees have been paid in full.
 *
 * @since	1.3
 * @param	int		$event_id		The event ID.
 * @param	int		$employee_id	User ID of employee to check
 * @return	bool	True if all employees, or selected employee have been paid.
 *					False if one employee, or the selected employee has not been paid.
 *					If no employees are assigned, a true value is returned.
 */
function mdjm_event_employees_paid( $event_id, $employee_id = '' )	{

	if ( ! mdjm_get_option( 'enable_employee_payments' ) )	{
		return false;
	}

	$employees = mdjm_get_all_event_employees( $event_id );
		
	if ( empty( $employees ) )	{
		return true;
	}
	
	if ( ! empty( $employee_id ) )	{

		if ( $employees[ $employee_id ]['payment_status'] != 'paid' && 'Completed' != get_post_meta( $employees[ $employee_id ]['txn_id'], '_mdjm_txn_status', true ) )	{
			return false;
		}

	} else	{
		
		foreach( $employees as $employee )	{
			if ( $employee['payment_status'] != 'paid' && 'Completed' != get_post_meta( $employee['txn_id'], '_mdjm_txn_status', true ) )	{
				return false;
			}
		}
		
	}
	
	return true;
	
} // mdjm_event_employees_paid

/**
 * Whether or not an employee has been paid for an event.
 *
 * @since	1.3
 * @param	int		$event_id		The event ID
 * @param	int		$employee_id	The employee user ID
 * @return	bool	True if paid, otherwise false.
 */
function mdjm_get_employees_event_payment_status( $event_id, $employee_id = '' )	{
	
	$employee_id = ! empty ( $employee_id ) ? $employee_id : get_current_user_id();
	
	$payment_status = 'unpaid';
	
	if ( $employee_id == mdjm_get_event_primary_employee( $event_id ) )	{
		
		$payment_data   = get_post_meta( $event_id, '_mdjm_event_dj_payment_status', true );
		
		$payment_status = empty( $payment_data ) ? 'unpaid' : $payment_data['payment_status'];
	
	} else	{
		
		$employees_data = mdjm_get_event_employees_data( $event_id );
		
		if ( ! empty( $employees_data ) )	{
		
			foreach( $employees_data as $employee_data )	{
				
				if ( $employee_data['id'] == $employee_id )	{
					
					$payment_status = $employee_data['payment_status'];
					
				}
				
			}
			
		}
	
	}
	
	return $payment_status;
	
} // mdjm_get_employees_event_payment_status

/**
 * Mark an event employee as paid.
 *
 * @since	1.3
 * @param	int		$employee_id	User ID of employee
 * @param	int		$event_id		Event ID
 * @param	int		$txn_id			The transaction ID associated with this payment.
 * @return	bool	True if payment data updated for event employee, otherwise false.
 */
function mdjm_set_employee_paid( $employee_id, $event_id, $txn_id = '' )	{
	
	global $wp_roles;
	
	if ( ! mdjm_get_option( 'enable_employee_payments' ) )	{
		return;
	}
	
	if ( ! mdjm_is_employee( $employee_id ) )	{

		return false;
	}
	
	$return = false;
	
	if ( $employee_id == mdjm_get_event_primary_employee( $event_id ) )	{

		/**
		 *
		 * Hook fires before marking event employee as paid.
		 *
		 * @since	1.3
		 * @param	int	$event_id	The event ID.
		 */
		do_action( "mdjm_pre_mdjm_set_employee_paid_{$employee_id}", $event_id );
		
		$role    = 'dj';
		$payment = mdjm_get_txn_price( $txn_id );
		
		$payment_data = get_post_meta( $event_id, '_mdjm_event_dj_payment_status', true );
		
		$payment_data['payment_status'] = mdjm_get_employees_event_wage( $event_id, $employee_id ) > $payment ? 'part-paid' : 'paid';
		$payment_data['payment_date']   = current_time( 'mysql' );
		$payment_data['txn_id']         = $txn_id;
		$payment_data['payment_amount'] = $payment;

		$payment_update = update_post_meta( $event_id, '_mdjm_event_dj_payment_status', $payment_data );
			
		if ( ! empty( $payment_update ) )	{
			
			MDJM()->debug->log_it( sprintf( '%s successfully paid %s for Event %d',
				mdjm_get_employee_display_name( $employee_id ), mdjm_currency_filter( mdjm_get_txn_price( $txn_id ) ), $event_id ) );
			
			$return = true;
			
		} else	{
			MDJM()->debug->log_it( sprintf( 'Unable to pay %s for Event %d', mdjm_get_employee_display_name( $employee_id ), $event_id ) );

			$return = false;
		}

	} else	{
	
		$payment_data = get_post_meta( $event_id, '_mdjm_event_employees_data', true );
		
		if ( ! mdjm_employee_working_event( $event_id, $employee_id ) )	{
	
			MDJM()->debug->log_it( 'Employee not working this event' );
			return false;
	
		} else	{
			
			/**
			 *
			 * Hook fires before marking event employee as paid.
			 *
			 * @since	1.3
			 * @param	int	$event_id	The event ID.
			 */
			do_action( "mdjm_pre_mdjm_set_employee_paid_{$employee_id}", $event_id );
			
			$role    = $payment_data[ $employee_id ]['role'];
			$payment = mdjm_get_txn_price( $payment_data[ $employee_id ]['txn_id'] );
			
			$payment_data[ $employee_id ]['payment_status'] = mdjm_get_employees_event_wage( $event_id, $employee_id ) > $payment ? 'part-paid' : 'paid';
			$payment_data[ $employee_id ]['payment_date']   = current_time( 'mysql' );
			$payment_data[ $employee_id ]['payment_amount'] = $payment;
			
			$payment_update = mdjm_update_txn_meta( $payment_data[ $employee_id ]['txn_id'], array( '_mdjm_txn_status' => 'Completed' ) );

			if ( ! empty( $payment_update ) )	{
				$payment_update = update_post_meta( $event_id, '_mdjm_event_employees_data', $payment_data );
			}
			
			if ( ! empty( $payment_update ) )	{
				
				MDJM()->debug->log_it( sprintf( '%s successfully paid %s for Event %d',
					mdjm_get_employee_display_name( $employee_id ), mdjm_currency_filter( mdjm_get_txn_price( $txn_id ) ), $event_id ) );
				
				$return = true;
				
			} else	{
				
				MDJM()->debug->log_it( sprintf( 'Unable to pay %s for Event %d', mdjm_get_employee_display_name( $employee_id ), $event_id ) );
				
				$return = false;
	
			}
			
		}
			
	}
	
	if ( ! empty( $return ) )	{
		
		$journal_args = array(
			'user_id'          => 1,
			'event_id'         => $event_id,
			'comment_content'  => sprintf( __( 'Employee %s paid %s for their role as %s', 'mobile-dj-manager' ),
				mdjm_get_employee_display_name( $employee_id ), $payment, translate_user_role( $wp_roles->roles[ $role ]['name'] ) )
		);
		
		$journal_meta = array(
			'mdjm_visibility'   => ! empty( $meta['visibility'] ) ? $meta['visibility'] : '2'
		);
		
		mdjm_add_journal( $journal_args, $journal_meta );
		
		/**
		 *
		 * Hook fires after successfully marking event employee as paid.
		 *
		 * @since	1.3
		 * @param	int	$event_id	The event ID.
		 * @param	int	$txn_id		The transaction ID associated with the payment
		 */
		do_action( "mdjm_post_mdjm_set_employee_paid_{$employee_id}", $event_id, $txn_id );
	}
	
	return $return;
	
} // mdjm_set_employee_paid

/**
 * Log the primary employees payment settings and update if employee or wage changes.
 *
 * @since	1.3
 * @param	int			$event_id	Event ID.
 * @param	arr			$old_meta	Old meta values from before event save.
 * @param	arr			$new_meta	New meta values after event save.
 * @return	void
 */
function mdjm_manage_primary_employee_payment_status( $event_id, $old_meta, $new_meta )	{

	if ( ! mdjm_get_option( 'enable_employee_payments' ) )	{
		return;
	}

	$mdjm_event = new MDJM_Event( $event_id );
	
	$employee_id    = $mdjm_event->get_employee();
	
	if ( empty( $employee_id ) )	{
		return;
	}
	
	$payment_amount = mdjm_get_employees_event_wage( $event_id, $employee_id );
	$payment_status = get_post_meta( $event_id, '_mdjm_event_dj_payment_status', true );
	
	if ( empty( $payment_status ) )	{
		
		if ( empty( $payment_amount ) || $payment_amount < 1 )	{
			return;
		}

		$mdjm_txn = new MDJM_Txn();

		$mdjm_txn->create(
			array(
				'post_title'     => sprintf( __( 'Wage payment to %s for %d', 'mobile-dj-manager' ), mdjm_get_employee_display_name( $employee_id ), $event_id ),
				'post_status'    => 'mdjm-expenditure',
				'post_author'    => 1,
				'post_parent'    => $event_id
			),
			array(
				'_mdjm_txn_status'    => 'Pending',
				'_mdjm_payment_to'    => $employee_id,
				'_mdjm_txn_total'     => $payment_amount
			)
		);

		if ( ! empty( $mdjm_txn ) )	{
			$data['txn_id'] = $mdjm_txn->ID;
		}

		mdjm_set_txn_type( $mdjm_txn->ID, mdjm_get_txn_cat_id( 'slug', 'mdjm-employee-wages' ) );
		
		$payment_data = array(
			'payment_status' => 'unpaid',
			'payment_date'   => '',
			'txn_id'         => $mdjm_txn->ID,
			'payment_amount' => ''
		);
		
		update_post_meta( $event_id, '_mdjm_event_dj_payment_status', $payment_data );
		
	} else	{
				
		if ( $payment_status['payment_status'] == 'paid' )	{
			return;
		}
		
		if ( in_array( $mdjm_event->post_status, array( 'mdjm-cancelled', 'mdjm-rejected', 'mdjm-failed' ) ) )	{
			update_post_meta( $mdjm_txn->ID, '_mdjm_txn_status', 'Cancelled' );
		}
		
		$mdjm_txn = new MDJM_Txn( $payment_status['txn_id'] );
		
		if ( $mdjm_txn->recipient_id != $employee_id )	{
			update_post_meta( $mdjm_txn->ID, '_mdjm_payment_to', $employee_id );
		}
		
		if ( $payment_amount != $mdjm_txn->price )	{
			update_post_meta( $mdjm_txn->ID, '_mdjm_txn_total', $payment_amount );
		}
		
	}
	
} // mdjm_manage_primary_employee_payment_status
add_action( 'mdjm_primary_employee_payment_status', 'mdjm_manage_primary_employee_payment_status', 10, 3 );
