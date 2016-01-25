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
				$this->add( $_POST );
				
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
		public function add( $post_data )	{
			return mdjm_add_employee( $post_data );
		} // add
		
		/**
		 * Retrieve a list of all employees
		 *
		 * @param	str|arr	$roles		Optional: The roles for which we want to retrieve the employees from.
		 *			str		$orderby	Optional: The field by which to order. Default display_name
		 *			str		$order		Optional: ASC (default) | Desc
		 *
		 * @return	$arr	$employees	or false if no employees for the specified roles
		 */
		public function get( $roles='', $orderby='display_name', $order='ASC' )	{			
			return mdjm_get_employees( $roles='', $orderby='display_name', $order='ASC' );
		} // get
				
		/**
		 * Set the role for the given employees
		 *
		 * @param	int|arr		$employees		Required: Single user ID or array of user ID's to adjust
		 *			str			$role			Required: The role ID to which the users will be moved
		 *
		 * @return	
		 */
		public function set_role( $employees, $role )	{			
			mdjm_set_employee_role( $employees, $role );
		} // set_role
		
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
			return mdjm_get_clients( $roles='', $employee='', $orderby='', $order='' );
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
