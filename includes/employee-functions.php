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
 * @param	$role		The role to check
 * @param	$int		Optional: The user ID of the employee
 * @return	bool		True if multi employee, otherwise false
 */
function mdjm_employee_can( $role, $user_id='' )	{
	return MDJM()->permissions->employee_can( $role, $user_id );
} // mdjm_employee_can

/**
 * Display a dropdown select list with all employees. The label must be handled seperately.
 *
 * @param	arr		$args			Settings for the dropdown
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
		'class'               => '',
		'selected'            => '',
		'first_entry'         => '',
		'first_entry_val'     => '0',
		'multiple'			  => false,
		'group'               => false,
		'structure'           => true,
		'exclude'			  => false,
		'echo'                => true
	);
	
	// Merge default args with those passed to function
	$args = wp_parse_args( $args, $defaults );
	
	$args['id'] = isset( $args['id'] ) ? $args['id'] : $args['name'];
	
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
			if( $employee->roles[0] == 'administrator' )
				$employee->roles[0] = 'dj';
			
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
		wp_redirect( $_SERVER['HTTP_REFERER'] . '&user_action=1&message=5' );
		exit;
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
		
		wp_redirect( $_SERVER['HTTP_REFERER'] . '&user_action=1&message=1' );
		exit;
	}
	else	{
		if( MDJM_DEBUG == true )
			MDJM()->debug->log_it( 'ERROR: Unable to add employee. ' . $user_id->get_error_message(), true );
			
		wp_redirect( $_SERVER['HTTP_REFERER'] . '&user_action=1&message=6' );
		exit;
	}			
} // mdjm_add_employee

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
		$roles = mdjm_get_roles( false );
				
	// This array will store our employees
	$employees = array();
				
	// Create and execute the WP_User_Query for each role	
	foreach( $roles as $role_id => $role_name )	{
		$args = array(
			'role'		 => is_numeric( $role_id ) ? $role_name : $role_id,
			'orderby'	 => $orderby,
			'order'		 => $order
		);
							
		if( !is_numeric( $role_id ) && $role_id == 'administrator' )	{
			$args['meta_key'] = '_mdjm_event_staff';
			$args['meta_value'] = true;
		}
		
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
 * Retrieve an employees first name.
 *
 * @since	1.3
 * @param	int		$user_id	The ID of the user to check.
 * @return	str		The first name of the employee.
 */
function mdjm_get_employee_firstname( $user_id )	{
	if( empty( $user_id ) )	{
		return false;
	}
	
	$employee = get_userdata( $user_id );
	
	if( $employee && ! empty( $employee->first_name ) )	{
		$first_name = $employee->first_name;
	} else	{
		$first_name = __( 'First name not set', 'mobile-dj-manager' );
	}
	
	return apply_filters( 'mdjm_get_employee_firstname', $first_name, $user_id );
} // mdjm_get_employee_firstname

/**
 * Retrieve an employees last name.
 *
 * @since	1.3
 * @param	int		$user_id	The ID of the user to check.
 * @return	str		The last name of the employee.
 */
function mdjm_get_employee_lastname( $user_id )	{
	if( empty( $user_id ) )	{
		return false;
	}
	
	$employee = get_userdata( $user_id );
	
	if( $employee && ! empty( $employee->last_name ) )	{
		$last_name = $employee->last_name;
	} else	{
		$last_name = __( 'Last name not set', 'mobile-dj-manager' );
	}
	
	return apply_filters( 'mdjm_get_employee_lastname', $last_name, $user_id );
} // mdjm_get_employee_lastname

/**
 * Retrieve an employees display name.
 *
 * @since	1.3
 * @param	int		$user_id	The ID of the user to check.
 * @return	str		The display name of the employee.
 */
function mdjm_get_employee_display_name( $user_id )	{
	if( empty( $user_id ) )	{
		return false;
	}
	
	$employee = get_userdata( $user_id );
	
	if( $employee && ! empty( $employee->display_name ) )	{
		$display_name = $employee->display_name;
	} else	{
		$display_name = __( 'Display name not set', 'mobile-dj-manager' );
	}
	
	return apply_filters( 'mdjm_get_employee_display_name', $display_name, $user_id );
} // mdjm_get_employee_display_name

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
					if( mdjm_employee_can( 'manage_txns' ) )	{
						$output .= mdjm_currency_filter( mdjm_sanitize_amount( $employee['wage'] ) );
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

/**
 * Set the role for the given employees
 *
 * @param	int|arr		$employees		Required: Single user ID or array of user ID's to adjust
 *			str			$role			Required: The role ID to which the users will be moved
 *
 * @return	
 */
function mdjm_set_employee_role( $employees, $role )	{			
	if( !is_array( $employees ) )
		$employees = array( $employees );
	
	foreach( $employees as $employee )	{
		// Fetch the WP_User object of our user.
		$user = new WP_User( $employee );
		
		if( !empty( $user ) )	{
			if( MDJM_DEBUG == true )
				MDJM()->debug->log_it( 'Updating user role for ' . $employee . ' to ' . $role, true );	
		}
		
		// Replace the current role with specified role
		$user->set_role( $role );
	}
} // mdjm_set_employee_role