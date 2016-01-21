<?php
	defined( 'ABSPATH' ) or die( "Direct access to this page is disabled!!!" );
	
/**
 * Class Name: MDJM_Users
 * Manage Users within MDJM
 *
 *
 *
 */
if( !class_exists( 'MDJM_Users' ) ) : 
	class MDJM_Users	{
		/*
		 * Class constructor
		 */
		public function __construct()	{
			// Capture form submissions
			add_action( 'init', array( &$this, 'init' ) );
						
			// Display custom user fields
			add_action( 'show_user_profile', array( &$this, 'profile_custom_fields' ) ); // User profile screen
			add_action( 'edit_user_profile', array( &$this, 'profile_custom_fields' ) ); // Edit user screen
			add_action( 'user_new_form', array( &$this, 'profile_custom_fields' ) ); // // New user screen
			
			// Save custom user fields
			add_action( 'user_register', array( &$this, 'save_custom_user_fields' ), 10, 1 );
			add_action( 'personal_options_update', array( &$this, 'save_custom_user_fields' ) );
			add_action( 'edit_user_profile_update', array( &$this, 'save_custom_user_fields' ) );
			
			// Display admin notices
			add_action( 'admin_notices', array( &$this, 'messages' ) );
		}
		
		/**
		 * Hook into the init action to process form submissions
		 *
		 *
		 *
		 *
		 */
		public function init()	{
			if( isset( $_POST['mdjm-add-employee'] ) )
				$this->add_employee( $_POST );
				
			$this->remove_client_admin();
		} // init
		
		/**
		 * Display admin notices to the user
		 *
		 *
		 *
		 *
		 */
		public function messages()	{
			if( !isset( $_GET['page'] ) || $_GET['page'] != 'mdjm-employees' || empty( $_GET['user_action'] ) || empty( $_GET['message'] ) )
				return;
			
			$messages = array(
				1 => array( 'updated', __( 'Employee added.', 'mobile-dj-manager' ) ),
				2 => array( 'updated', __( 'Employees deleted.', 'mobile-dj-manager' ) ),
				3 => array( 'updated', __( '', 'mobile-dj-manager' ) ),
				4 => array( 'updated', __( '', 'mobile-dj-manager' ) ),
				5 => array( 'error', __( 'Insufficient information to create employee.', 'mobile-dj-manager' ) ),
				6 => array( 'error', __( 'Unable to create employee.', 'mobile-dj-manager' ) ),
				7 => array( 'updated', __( '', 'mobile-dj-manager' ) ) );
				
			mdjm_update_notice( $messages[$_GET['message']][0], $messages[$_GET['message']][1], true );
		} // messages
				
		/**
		 * Present the employee management interface
		 * @called MDJM_Menu class
		 *
		 * @param
		 * 
		 * @return
		 */
		public static function employee_manager()	{
			wp_enqueue_script( 'mdjm-users-js' );
						
			include( 'mdjm-employee-manager.php' );
		} // employee_manager
		
		/**
		 * Present the client management interface
		 * @called: MDJM_Menu class
		 *
		 * @param
		 * 
		 * @return
		 */
		public static function client_manager()	{
			include( 'mdjm-client-manager.php' );
		} // client_manager
		
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
		function add_employee( $post_data )	{
			if( empty( $post_data['first_name'] ) || empty( $post_data['last_name'] ) || empty( $post_data['user_email'] ) || empty( $post_data['employee_role'] ) )	{
				wp_redirect( $_SERVER['HTTP_REFERER'] . '&user_action=1&message=5' );
				exit;
			}
			
			// We don't need to execute the hooks for user saves
			remove_action( 'user_register', array( &$this, 'save_custom_user_fields' ), 10, 1 );
			remove_action( 'personal_options_update', array( &$this, 'save_custom_user_fields' ) );
			remove_action( 'edit_user_profile_update', array( &$this, 'save_custom_user_fields' ) );
			
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
			add_action( 'user_register', array( &$this, 'save_custom_user_fields' ), 10, 1 );
			add_action( 'personal_options_update', array( &$this, 'save_custom_user_fields' ) );
			add_action( 'edit_user_profile_update', array( &$this, 'save_custom_user_fields' ) );
			
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
		} // add_employee
		
		/**
		 * Retrieve a list of all employees
		 *
		 * @param	str|arr	$roles		Optional: The roles for which we want to retrieve the employees from.
		 *			str		$orderby	Optional: The field by which to order. Default display_name
		 *			str		$order		Optional: ASC (default) | Desc
		 *
		 * @return	$arr	$employees	or false if no employees for the specified roles
		 */
		public function get_employees( $roles='', $orderby='display_name', $order='ASC' )	{			
			// We'll work with an array of roles
			if( !empty( $roles ) && !is_array( $roles ) )
				$roles = array( $roles );
						
			// Define the default query	
			if( empty( $roles ) )
				$roles = MDJM()->roles->get_roles();
						
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
		} // get_employees
		
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
		public function employee_dropdown( $args='' )	{
			global $wp_roles;
			
			// Define the default args for the dropdown
			$defaults = array(
				'role'                => '',
				'name'                => '_mdjm_employees',
				'class'               => '',
				'selected'            => '',
				'first_entry'         => '',
				'first_entry_val'     => '',
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
			
			$employees = $this->get_employees( $args['role'] );
			
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
		} // employee_dropdown
				
		/**
		 * Set the role for the given employees
		 *
		 * @param	int|arr		$employees		Required: Single user ID or array of user ID's to adjust
		 *			str			$role			Required: The role ID to which the users will be moved
		 *
		 * @return	
		 */
		public function set_employee_role( $employees, $role )	{			
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
		} // set_employee_role
		
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
		public function get_clients( $roles='', $employee='', $orderby='', $order='' )	{			
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
					if( !$this->is_employee_client( $client->ID, $employee ) )
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
		} // get_clients
		
		/**
		 * Determine if the given client belongs to the given employee
		 * If no event is specified, true will be returned if the Employee has (or will) performed
		 * for the client at any time
		 *
		 * @params	$client		int		Required: The user_ID of the client
		 *			$employee	int		Optional: The user_ID of the employee. Uses current user if not specified.
		 *			$event		int		Optional: The event ID to query
		 *			
		 * @return				bool	True if client belongs to employee, otherwise false
		 *
		 */
		public function is_employee_client( $client, $employee='', $event='' )	{
			global $current_user;
			
			$args = array(
					'post_type' 		=> MDJM_EVENT_POSTS,
					'post_status'	  => 'any',
					'posts_per_page'   => 1,
					'meta_key'		 => '_mdjm_event_date',
					'meta_query'	   => array(
											'relation'   => 'AND',
											array( 
											'key'		=> '_mdjm_event_dj',
											'value'  	  => !empty( $employee ) ? $employee : $current_user->ID,
											'compare'	=> '=',
											),
											array(
											'key'		=> '_mdjm_event_client',
											'value'  	  => $client,
											'compare'	=> '=',
											),
										),
					'orderby'		  => 'meta_value_num',
					'order' 			=> 'ASC',
					);
					
			if( empty( $event ) )
				return ( count( get_posts( $args ) ) == 1 ? true : false );
				
			$the_event = get_post( $event );
			
			// No events found return false
			if( empty( $the_event ) )
				return false;
				
			return ( get_post_meta( $the_event->ID, '_mdjm_event_dj', true ) == $current_user->ID ) ? true : false;
		} // is_employee_client
				
		/**
		 * Add the MDJM Custom User Fields to the user profile page
		 * 
		 * 
		 *
		 * @param    int    $user    The ID of the user
		 * 
		 * @return
		 */
		public function profile_custom_fields( $user )	{
			global $current_screen, $user_ID, $pagenow;
							
			if( $pagenow != 'user-new.php' )
				$user_id = ( $current_screen->id == 'profile' ) ? $user_ID : $_REQUEST['user_id'];
			
			do_action( 'mdjm_user_fields_before_mdjm', $user );
			
			echo '<h3>Mobile DJ Manager Custom User Fields</h3>' . "\r\n";
			echo '<table class="form-table">' . "\r\n";
			
			// Is event staff checkbox for WP admins
			if( isset( $user->ID ) && user_can( $user->ID, 'administrator' ) )	{
				if( $user->ID != get_current_user_id() )	{
					echo '<tr>' . "\r\n";
					echo '<th><label for="_mdjm_event_staff">' . sprintf( __( '%s Event Staff?', 'mobile-dj-manager' ), MDJM_COMPANY ) . '</label></th>' . "\r\n";
					echo '<td>' . "\r\n";
					echo '<input type="checkbox" name="_mdjm_event_staff" id="_mdjm_event_staff" value="1"';
						checked ( get_user_meta( $user->ID, '_mdjm_event_staff', true ), true );
					echo ' />' . "\r\n";
					echo '</td>' . "\r\n";
					echo '</tr>' . "\r\n";
				}
				else	{
					echo '<input type="hidden" name="_mdjm_event_staff" id="_mdjm_event_staff" value="';
					echo get_user_meta( $user->ID, '_mdjm_event_staff', true );
					echo '" />' . "\r\n";
				}
			}
			
			// Get the custom user fields
			$custom_fields = get_option( MDJM_CLIENT_FIELDS );
			
			// Loop through the fields
			foreach( $custom_fields as $custom_field )	{
				if( $pagenow != 'user-new.php' )
					$field_value = get_user_meta( $user_id, $custom_field['id'], true );

				// Display if configured
				if( $custom_field['display'] == true && $custom_field['id'] != 'first_name' && $custom_field['id'] != 'last_name' && $custom_field['id'] != 'user_email' )	{
					echo '<tr>' . "\r\n" . 
					'<th><label for="' . $custom_field['id'] . '">' . $custom_field['label'] . '</label></th>' . "\r\n" . 
					'<td>' . "\r\n";
					
					// Checkbox Field
					if( $custom_field['type'] == 'checkbox' )	{
						echo '<input type="' . $custom_field['type'] . '" name="' . $custom_field['id'] . '" id="' . $custom_field['id'] . '" value="Y" ';
						if( $pagenow != 'user-new.php' )
							checked( $field_value, 'Y' );
						else
							checked ( '', '' );
						echo ' />' . "\r\n";
					}
					// Select List
					elseif( $custom_field['type'] == 'dropdown' )	{
						echo '<select name="' . $custom_field['id'] . '" id="' . $custom_field['id'] . '">';
						
						$option_data = explode( "\r\n", $custom_field['value'] );
						
						echo '<option value="empty"';
						if( $pagenow == 'user-new.php' || empty( $field_value ) || $field_value == 'empty' ) echo ' selected';
						echo '></option>' . "\r\n";
						
						foreach( $option_data as $option )	{
							echo '<option value="' . $option . '"';
							if( $pagenow != 'user-new.php' )
								selected( $option, $field_value );
							echo '>' . $option . '</option>' . "\r\n";
						}
						
						echo '<select/>';
					}
					// Everything else
					else	{
						echo '<input type="' . $custom_field['type'] . '" name="' . $custom_field['id'] . 
						'" id="' . $custom_field['id'] . '" value="' . ( $pagenow != 'user-new.php' ? esc_attr( get_the_author_meta( $custom_field['id'], $user->ID ) ) : '' ) . 
						'" class="regular-text" />' . "\r\n";
					}
					
					// Description if set
					if( $custom_field['desc'] != '' )	{
						echo '<br />' . 
						'<span class="description">' . $custom_field['desc'] . '</span>' . "\r\n";
					}
					// End the table row
					echo '</td>' . "\r\n" . 
					'</tr>' . "\r\n";
				}
			}
			
			echo '</table>' . "\r\n";
			
			do_action( 'mdjm_user_fields_after_mdjm', $user );
		} // profile_custom_fields
		
		/**
		 * Save the MDJM Custom User Fields
		 * 
		 * 
		 *
		 * @param    int    $user_id    The ID of the user
		 * 
		 * @return
		 */
		public function save_custom_user_fields( $user_id )	{
			$custom_fields = get_option( MDJM_CLIENT_FIELDS );
			$default_fields = get_user_by( 'id', $user_id );
			
			if( !current_user_can( 'edit_user', $user_id ) )
				return;
			
			if( user_can( $user_id, 'administrator' ) )	{
				if( !empty( $_POST['_mdjm_event_staff'] ) )	{
					update_user_meta( $user_id, '_mdjm_event_staff', true );
					$default_fields->add_cap( 'mdjm_employee' );
					$default_fields->add_cap( 'manage_mdjm' );
				}
					
				else	{
					delete_user_meta( $user_id, '_mdjm_event_staff' );
					$default_fields->remove_cap( 'manage_mdjm' );
					$default_fields->remove_cap( 'mdjm_employee' );
				}
			}
			
			// Loop through the fields and update
			foreach( $custom_fields as $custom_field )	{
				$field = $custom_field['id'];
				
				// Checkbox unchecked = N
				if( $custom_field['type'] == 'checkbox' && empty( $_POST[$field] ) )
					$_POST[$field] = 'N';
				
				// Update the users meta data
				if( !empty( $_POST[$field] ) )
					update_user_meta( $user_id, $field, $_POST[$field] );
				
				/**
				 * For new users, remove the admin bar 
				 * and set the action to created
				 */
				if( $_POST['action'] == 'createuser' )	{
					update_user_option( $user_id, 'show_admin_bar_front', false );
					if( !empty( $default_fields->first_name ) && !empty( $default_fields->last_name ) )	{
						update_user_option( $user_id, 'display_name', $default_fields->first_name . ' ' . $default_fields->last_name );
					}
					$client_action = 'created';	
				}
				else
					$client_action = 'updated';
			}
		} // save_custom_user_fields
		
		/**
		 * Remove admin bar & do not allow admin UI for Clients.
		 * Redirect to Client Zone.
		 *
		 * @called	init
		 *
		 * @params 
		 *
		 * @return	void
		 */
		public function remove_client_admin() {
			if( current_user_can( 'client' ) || current_user_can( 'inactive_client' ) )	{
				add_filter( 'show_admin_bar', '__return_false' );
				
				if( is_admin() )	{
					if( !defined( 'DOING_AJAX' ) || !DOING_AJAX )	{
						wp_redirect( $GLOBALS['mdjm']->get_link( MDJM_HOME, false, false ) );
						exit;	
					}
				}
			}				
		} // remove_client_admin
		
		/**
		 * Prepare a user for password reset.
		 *
		 * @called
		 *
		 * @params	int		$user_id	Required: xThe ID of the user who needs preparing.
		 *
		 * @return	bool				True on success, otherwise false.
		 */
		public function prepare_user_pass_reset( $user_id )	{
			if( MDJM_DEBUG == true )
				MDJM()->debug->log_it( 'Preparing user ' . $user_id . ' for password reset' );
				
			$reset =	update_user_meta(
							$user_id,
							'mdjm_pass_action',
							wp_generate_password( $GLOBALS['mdjm_settings']['clientzone']['pass_length'] )
						);
			
			if( MDJM_DEBUG == true )
				MDJM()->debug->log_it( 'Password preparation ' . !empty( $reset ) ? 'success' : 'fail' );
			
			return $reset;
		} // prepare_user_pass_reset
	} // class MDJM_Users
endif;
