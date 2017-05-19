<?php
	defined( 'ABSPATH' ) or die( "Direct access to this page is disabled!!!" );
	
/**
 * Class Name: MDJM_Permissions
 * Manage User permissions within MDJM
 *
 *
 *
 */
class MDJM_Permissions	{
	/**
	 * Class constructor
	 */
	public function __construct()	{
		// Capture form submissions
		add_action( 'init', array( &$this, 'init' ) );			
	}
	
	/**
	 * Hook into the init action to process form submissions
	 *
	 *
	 *
	 *
	 */
	public function init()	{			
		if( isset( $_POST['set-permissions'], $_POST['mdjm_set_permissions'] ) )
			$this->set_permissions();			
	} // init
	
	/**
	 * Set permissions for the given roles
	 *
	 * @since	1.3
	 * @param
	 * @return
	 */
	public function set_permissions()	{
		
		if( !isset( $_POST['employee_roles'] ) )	{
			return;
		}
		
		$fields = array( 
			'comm_permissions'     => 'mdjm_comms',
			'client_permissions'   => 'mdjm_client',
			'employee_permissions' => 'mdjm_employee',
			'event_permissions'    => 'mdjm_event',
			'package_permissions'  => 'mdjm_package',
			'quote_permissions'    => 'mdjm_quote',
			'report_permissions'   => 'mdjm_reports',
			'template_permissions' => 'mdjm_template',
			'txn_permissions'      => 'mdjm_txn',
			'venue_permissions'    => 'mdjm_venue'
		);
					
		foreach( $_POST['employee_roles'] as $_role )	{
			$role = get_role( $_role );
			
			// If the role is to become admin
			if( !empty( $_POST['manage_mdjm_' . $_role] ) )	{
				
				$role->add_cap( 'manage_mdjm' );
				$this->make_admin( $_role );
				continue;
				
			} else	{
				$role->remove_cap( 'manage_mdjm' );
			}
			
			// Every role has the MDJM Employee capability
			$role->add_cap( 'mdjm_employee' );
			
			foreach( $fields as $field => $prefix )	{
								
				$caps = empty( $_POST[$field . '_' . $_role] ) ? 
					$this->get_capabilities( $prefix . '_none' ) : 
					$this->get_capabilities( $_POST[$field . '_' . $_role]
				);
				
				foreach( $caps as $cap => $val )	{
					
					if( empty( $val ) )	{
						$role->remove_cap( $cap );
					} else	{
						$role->add_cap( $cap );
					}
					
				}
			}				
		}
		
		wp_redirect( $_SERVER['HTTP_REFERER'] . '&role_action=1&message=4' );
		exit;

	} // set_permissions
			
	/**
	 * Defines all of the required capabilities for the requested capability.
	 *
	 * @since	1.3
	 * @param	str		$cap		The capability being requested
	 * @return	arr		$caps		The required capabilities that the requested capability needs or false if they cannot be calculated
	 */
	public function get_capabilities( $cap )	{
		
		switch( $cap )	{
			/**
			 * Clients
			 */
			case 'mdjm_client_none':
				$caps = array( 'mdjm_client_edit' => false, 'mdjm_client_edit_own' => false );
				break;
				
			case 'mdjm_client_edit_own':
				$caps = array( 'mdjm_client_edit' => false, 'mdjm_client_edit_own' => true );
				break;
				
			case 'mdjm_client_edit':
				$caps = array( 'mdjm_client_edit' => true, 'mdjm_client_edit_own' => true );
				break;
			/**
			 * Communications
			 */
			case 'mdjm_comms_none':
				$caps = array( 
					'mdjm_comms_send' => false, 'edit_mdjm_comms' => false, 'edit_others_mdjm_comms' => false,
					'publish_mdjm_comms' => false, 'read_private_mdjm_comms' => false, 
					'edit_published_mdjm_comms' => false, 'delete_mdjm_comms' => false,
					'delete_others_mdjm_comms' => false, 'delete_private_mdjm_comms' => false,
					'delete_published_mdjm_comms' => false, 'edit_private_mdjm_comms' => false
				);
				break;
				
			case 'mdjm_comms_send':
				$caps = array( 
					'mdjm_comms_send' => true, 'edit_mdjm_comms' => true, 'edit_others_mdjm_comms' => true,
					'publish_mdjm_comms' => true, 'read_private_mdjm_comms' => true, 
					'edit_published_mdjm_comms' => true, 'delete_mdjm_comms' => true,
					'delete_others_mdjm_comms' => true, 'delete_private_mdjm_comms' => true,
					'delete_published_mdjm_comms' => true, 'edit_private_mdjm_comms' => true
				);
				break;
				
			/**
			 * Employees
			 */
			case 'mdjm_employee_none':
				$caps = array( 'mdjm_employee_edit' => false );
				break;
				
			case 'mdjm_employee_edit':
				$caps = array( 'mdjm_employee_edit' => true );
				break;
			/**
			 * Events
			 */
			case 'mdjm_event_none':
				$caps = array( 
					'mdjm_event_read' => false, 'mdjm_event_read_own' => false, 'mdjm_event_edit' => false,
					'mdjm_event_edit_own' => false, 'publish_mdjm_events' => false, 'edit_mdjm_events' => false,
					'edit_others_mdjm_events' => false, 'delete_mdjm_events' => false, 'delete_others_mdjm_events' => false,
					'read_private_mdjm_events' => false
				);
				break;
				
			case 'mdjm_event_read_own':
				$caps = array( 
					'mdjm_event_read' => false, 'mdjm_event_read_own' => true, 'mdjm_event_edit' => false,
					'mdjm_event_edit_own' => false, 'publish_mdjm_events' => false, 'edit_mdjm_events' => true,
					'edit_others_mdjm_events' => true, 'delete_mdjm_events' => false, 'delete_others_mdjm_events' => false,
					'read_private_mdjm_events' => true
				);
				break;
				
			case 'mdjm_event_read':
				$caps = array( 
					'mdjm_event_read' => true, 'mdjm_event_read_own' => true, 'mdjm_event_edit' => false,
					'mdjm_event_edit_own' => false, 'publish_mdjm_events' => false, 'edit_mdjm_events' => true,
					'edit_others_mdjm_events' => true, 'delete_mdjm_events' => false, 'delete_others_mdjm_events' => false,
					'read_private_mdjm_events' => true
				);
				break;
				
			case 'mdjm_event_edit_own':
				$caps = array( 
					'mdjm_event_read' => false, 'mdjm_event_read_own' => true, 'mdjm_event_edit' => false,
					'mdjm_event_edit_own' => true, 'publish_mdjm_events' => true, 'edit_mdjm_events' => true,
					'edit_others_mdjm_events' => true, 'delete_mdjm_events' => false, 'delete_others_mdjm_events' => false,
					'read_private_mdjm_events' => true
				);
				break;
				
			case 'mdjm_event_edit':
				$caps = array( 
					'mdjm_event_read' => true, 'mdjm_event_read_own' => true, 'mdjm_event_edit' => true,
					'mdjm_event_edit_own' => true, 'publish_mdjm_events' => true, 'edit_mdjm_events' => true,
					'edit_others_mdjm_events' => true, 'delete_mdjm_events' => true, 'delete_others_mdjm_events' => true,
					'read_private_mdjm_events' => true
				);
				break;
			/**
			 * Packages
			 */
			case 'mdjm_package_none':
				$caps = array(
					'mdjm_package_edit_own' => true, 'mdjm_package_edit' => false,
					'publish_mdjm_packages' => false, 'edit_mdjm_packages' => false,
					'edit_others_mdjm_packages' => false, 'delete_mdjm_packages' => false,
					'delete_others_mdjm_packages' => false, 'read_private_mdjm_packages' => false
				);
				break;
				
			case 'mdjm_package_edit_own':
				$caps = array(
					'mdjm_package_edit_own' => true, 'mdjm_package_edit' => false,
					'publish_mdjm_packages' => true, 'edit_mdjm_packages' => true,
					'edit_others_mdjm_packages' => false, 'delete_mdjm_packages' => false,
					'delete_others_mdjm_packages' => false, 'read_private_mdjm_packages' => false
				);
				break;
	
			case 'mdjm_package_edit':
				$caps = array(
					'mdjm_package_edit_own' => true, 'mdjm_package_edit' => true,
					'publish_mdjm_packages' => true, 'edit_mdjm_packages' => true,
					'edit_others_mdjm_packages' => true, 'delete_mdjm_packages' => true,
					'delete_others_mdjm_packages' => true, 'read_private_mdjm_packages' => true
				);
				break;
			/**
			 * Quotes
			 */
			case 'mdjm_quote_none':
				$caps = array( 
					'mdjm_quote_view_own' => false, 'mdjm_quote_view' => false, 'edit_mdjm_quotes' => false,
					'edit_others_mdjm_quotes' => false, 'publish_mdjm_quotes' => false, 
					'read_private_mdjm_quotes' => false, 'edit_published_mdjm_quotes' => false,
					'edit_private_mdjm_quotes' => false, 'delete_mdjm_quotes' => false, 'delete_others_mdjm_quotes' => false,
					'delete_private_mdjm_quotes' => false, 'delete_published_mdjm_quotes' => false
				);
				break;
	
			case 'mdjm_quote_view_own':
				$caps = array( 
					'mdjm_quote_view_own' => true, 'mdjm_quote_view' => false, 'edit_mdjm_quotes' => true,
					'edit_others_mdjm_quotes' => false, 'publish_mdjm_quotes' => false, 
					'read_private_mdjm_quotes' => false, 'edit_published_mdjm_quotes' => false,
					'edit_private_mdjm_quotes' => false, 'delete_mdjm_quotes' => false, 'delete_others_mdjm_quotes' => false,
					'delete_private_mdjm_quotes' => false, 'delete_published_mdjm_quotes' => false
				);
				break;
	
			case 'mdjm_quote_view':
				$caps = array( 
					'mdjm_quote_view_own' => true, 'mdjm_quote_view' => true, 'edit_mdjm_quotes' => true,
					'edit_others_mdjm_quotes' => true, 'publish_mdjm_quotes' => true, 
					'read_private_mdjm_quotes' => true, 'edit_published_mdjm_quotes' => true,
					'edit_private_mdjm_quotes' => true, 'delete_mdjm_quotes' => true, 'delete_others_mdjm_quotes' => true,
					'delete_private_mdjm_quotes' => true, 'delete_published_mdjm_quotes' => true
				);
				break;
			/**
			 * Reports
			 */
			case 'mdjm_reports_none':
				$caps = array( 
					'view_event_reports' => false
				);
				break;
			case 'mdjm_reports_run':
				$caps = array( 
					'view_event_reports' => true
				);
				break;
			/**
			 * Templates
			 */
			case 'mdjm_template_none':
				$caps = array( 
					'mdjm_template_edit' => false, 'edit_mdjm_templates' => false,
					'edit_others_mdjm_templates' => false, 'publish_mdjm_templates' => false, 'read_private_mdjm_templates' => false,
					'edit_published_mdjm_templates' => false, 'edit_private_mdjm_templates' => false, 'delete_mdjm_templates' => false,
					'delete_others_mdjm_templates' => false, 'delete_private_mdjm_templates' => false,
					'delete_published_mdjm_templates' => false
				);
				break;
		
			case 'mdjm_template_edit':
				$caps = array( 
					'mdjm_template_edit' => true, 'edit_mdjm_templates' => true,
					'edit_others_mdjm_templates' => true, 'publish_mdjm_templates' => true, 'read_private_mdjm_templates' => true,
					'edit_published_mdjm_templates' => true, 'edit_private_mdjm_templates' => true, 'delete_mdjm_templates' => true,
					'delete_others_mdjm_templates' => true, 'delete_private_mdjm_templates' => true,
					'delete_published_mdjm_templates' => true
				);
				break;
			/**
			 * Transactions
			 */
			case 'mdjm_txn_none':
				$caps = array( 
					'mdjm_txn_edit' => false, 'edit_mdjm_txns' => false, 'edit_others_mdjm_txns' => false,
					'publish_mdjm_txns' => false,  'read_private_mdjm_txns' => false, 'edit_published_mdjm_txns' => false,
					'edit_private_mdjm_txns' => false, 'delete_mdjm_txns' => false, 'delete_others_mdjm_txns' => false,
					'delete_private_mdjm_txns' => false, 'delete_published_mdjm_txns' => false
				);
				break;
		
			case 'mdjm_txn_edit':
				$caps = array( 
					'mdjm_txn_edit' => true, 'edit_mdjm_txns' => true, 'edit_others_mdjm_txns' => true, 
					'publish_mdjm_txns' => true, 'read_private_mdjm_txns' => true, 'edit_published_mdjm_txns' => true,
					'edit_private_mdjm_txns' => true, 'delete_mdjm_txns' => true, 'delete_others_mdjm_txns' => true,
					'delete_private_mdjm_txns' => true, 'delete_published_mdjm_txns' => true
				);
				break;
			/**
			 * Venues
			 */
			case 'mdjm_venue_none':
				$caps = array( 
					'mdjm_venue_read' => false, 'mdjm_venue_edit' => false, 'edit_mdjm_venues' => false,
					'edit_others_mdjm_venues' => false, 'publish_mdjm_venues' => false, 'read_private_mdjm_venues' => false,
					'edit_published_mdjm_venues' => false, 'edit_private_mdjm_venues' => false, 'delete_mdjm_venues' => false,
					'delete_others_mdjm_venues' => false, 'delete_private_mdjm_venues' => false,
					'delete_published_mdjm_venues' => false
				);
				break;
	
			case 'mdjm_venue_read':
				$caps = array( 
					'mdjm_venue_read' => true, 'mdjm_venue_edit' => false, 'edit_mdjm_venues' => true,
					'edit_others_mdjm_venues' => true, 'publish_mdjm_venues' => false, 'read_private_mdjm_venues' => true,
					'edit_published_mdjm_venues' => true, 'edit_private_mdjm_venues' => true, 'delete_mdjm_venues' => false,
					'delete_others_mdjm_venues' => false, 'delete_private_mdjm_venues' => false,
					'delete_published_mdjm_venues' => false
				);
				break;
		
			case 'mdjm_venue_edit':
				$caps = array( 
					'mdjm_venue_read' => true, 'mdjm_venue_edit' => true, 'edit_mdjm_venues' => true,
					'edit_others_mdjm_venues' => true, 'publish_mdjm_venues' => true, 'read_private_mdjm_venues' => true,
					'edit_published_mdjm_venues' => true, 'edit_private_mdjm_venues' => true, 'delete_mdjm_venues' => true,
					'delete_others_mdjm_venues' => true, 'delete_private_mdjm_venues' => true,
					'delete_published_mdjm_venues' => true
				);
				break;
			
			default:
				return false;
				break;
				
		}
		
		return !empty( $caps ) ? $caps : false;
	} // get_capabilities
	
	/**
	 * Determine if the currently logged in employee user has the relevant permissions to perform the action/view the page
	 *
	 * @since	1.3
	 * @param	str		$action			Required: The action being performed
	 * @param	int		$user_id		Optional: The ID of the user to query. Default current user
	 * @return	bool	$granted		true|false
	 */
	public function employee_can( $action, $user_id='' )	{
		if( empty( $user_id ) )	{
			$user = wp_get_current_user();
		} else	{
			$user = get_user_by( 'id', $user_id );
		}
		
		// MDJM Admins can do everything
		if( mdjm_is_admin( $user->ID ) )	{
			return true;
		}
		
		// Non employees can't do anything	
		if( ! mdjm_is_employee( $user->ID ) )	{
			return false;
		}
			
		switch( $action )	{
			
			case 'view_clients_list':
				$allowed_roles = array( 'mdjm_client_edit', 'mdjm_client_edit_own' );
				break;
			
			case 'list_all_clients':
				$allowed_roles = array( 'mdjm_client_edit' );
				break;
		
			case 'manage_employees':
				$allowed_roles = array( 'mdjm_employee_edit' );
				break;
		
			case 'read_events':
				$allowed_roles = array( 'mdjm_event_read', 'mdjm_event_read_own', 'mdjm_event_edit', 'mdjm_event_edit_own' );
				break;
	
			case 'read_events_all':
				$allowed_roles = array( 'mdjm_event_read', 'mdjm_event_edit' );
				break;
	
			case 'manage_events':
				$allowed_roles = array( 'mdjm_event_edit', 'mdjm_event_edit_own' );
				break;
	
			case 'manage_all_events':
				$allowed_roles = array( 'mdjm_event_edit' );
				break;
	
			case 'manage_packages':
				$allowed_roles = array( 'mdjm_package_edit', 'mdjm_package_edit_own' );
				break;
	
			case 'manage_templates':
				$allowed_roles = array( 'mdjm_template_edit' );
				break;
	
			case 'edit_txns':
				$allowed_roles = array( 'mdjm_txn_edit' );
				break;
	
			case 'list_all_quotes':
				$allowed_roles = array( 'mdjm_quote_view' );
				break;
	
			case 'list_own_quotes':
				$allowed_roles = array( 'mdjm_quote_view_own', 'mdjm_quote_view' );
				break;
	
			case 'list_venues':
				$allowed_roles = array( 'mdjm_venue_read', 'mdjm_venue_edit' );
				break;
	
			case 'add_venues':
				$allowed_roles = array( 'mdjm_venue_edit' );
				break;
	
			case 'send_comms':
				$allowed_roles = array( 'mdjm_comms_send' );
				break;

			case 'run_reports':
				$allowed_roles = array( 'view_event_reports' );
			default:
				return false;
				break;
		} // switch
		
		if( empty( $allowed_roles ) )	{
			return false;
		}
			
		foreach( $allowed_roles as $allowed )	{
			if( user_can( $user->ID, $allowed ) )	{
				return true;
			}
		}
								
		return false;
	} // employee_can
	
	/**
	 * Make the current role a full admin
	 *
	 * @since	1.3
	 * @param	int|str		$where		The user ID or role name to which the permission
	 *
	 * @return	void
	 */
	public function make_admin( $where, $remove = false )	{
					
		$caps = array(
			// MDJM Admin
			'manage_mdjm' => true,
			
			// Clients
			'mdjm_client_edit' => true, 'mdjm_client_edit_own' => true,
			
			// Employees
			'mdjm_employee_edit' => true,
			
			// Packages
			'mdjm_package_edit_own' => true, 'mdjm_package_edit' => true,
			'publish_mdjm_packages' => true, 'edit_mdjm_packages' => true,
			'edit_others_mdjm_packages' => true, 'delete_mdjm_packages' => true,
			'delete_others_mdjm_packages' => true, 'read_private_mdjm_packages' => true,

			// Comm posts
			'mdjm_comms_send' => true, 'edit_mdjm_comms' => true, 'edit_others_mdjm_comms' => true,
			'publish_mdjm_comms' => true, 'read_private_mdjm_comms' => true, 
			'edit_published_mdjm_comms' => true, 'delete_mdjm_comms' => true,
			'delete_others_mdjm_comms' => true, 'delete_private_mdjm_comms' => true,
			'delete_published_mdjm_comms' => true, 'edit_private_mdjm_comms' => true,
			
			// Event posts
			'mdjm_event_read' => true, 'mdjm_event_read_own' => true, 'mdjm_event_edit' => true,
			'mdjm_event_edit_own' => true, 'publish_mdjm_events' => true, 'edit_mdjm_events' => true,
			'edit_others_mdjm_events' => true, 'delete_mdjm_events' => true, 'delete_others_mdjm_events' => true,
			'read_private_mdjm_events' => true,
			
			// Quote posts
			'mdjm_quote_view_own' => true, 'mdjm_quote_view' => true, 'edit_mdjm_quotes' => true,
			'edit_others_mdjm_quotes' => true, 'publish_mdjm_quotes' => true, 
			'read_private_mdjm_quotes' => true, 'edit_published_mdjm_quotes' => true,
			'edit_private_mdjm_quotes' => true, 'delete_mdjm_quotes' => true, 'delete_others_mdjm_quotes' => true,
			'delete_private_mdjm_quotes' => true, 'delete_published_mdjm_quotes' => true,

			// Reports
			'view_event_reports' => true,

			// Templates
			'mdjm_template_edit' => true, 'edit_mdjm_templates' => true,
			'edit_others_mdjm_templates' => true, 'publish_mdjm_templates' => true, 'read_private_mdjm_templates' => true,
			'edit_published_mdjm_templates' => true, 'edit_private_mdjm_templates' => true, 'delete_mdjm_templates' => true,
			'delete_others_mdjm_templates' => true, 'delete_private_mdjm_templates' => true,
			'delete_published_mdjm_templates' => true,
			
			// Transaction posts
			'mdjm_txn_edit' => true, 'edit_mdjm_txns' => true, 'edit_others_mdjm_txns' => true, 'publish_mdjm_txns' => true,
			'read_private_mdjm_txns' => true, 'edit_published_mdjm_txns' => true, 'edit_private_mdjm_txns' => true,
			'delete_mdjm_txns' => true, 'delete_others_mdjm_txns' => true, 'delete_private_mdjm_txns' => true,
			'delete_published_mdjm_txns' => true,
			
			// Venue posts
			'mdjm_venue_read' => true, 'mdjm_venue_edit' => true, 'edit_mdjm_venues' => true,
			'edit_others_mdjm_venues' => true, 'publish_mdjm_venues' => true, 'read_private_mdjm_venues' => true,
			'edit_published_mdjm_venues' => true, 'edit_private_mdjm_venues' => true, 'delete_mdjm_venues' => true,
			'delete_others_mdjm_venues' => true, 'delete_private_mdjm_venues' => true,
			'delete_published_mdjm_venues' => true
		);
		
		$role = ( is_numeric( $where ) ? new WP_User( $where ) : get_role( $where ) );
		
		// Fire a filter to enable default capabilities to be manipulated
		$caps = apply_filters( 'mdjm_all_caps', $caps );
		
		foreach( $caps as $cap => $set )	{
			
			if ( ! empty( $remove ) )	{
				$role->remove_cap( $cap );
			} elseif ( ! empty( $set ) )	{
				$role->add_cap( $cap );
			} else	{
				$role->remove_cap( $cap );
			}
		}
				
	} // make_admin
	
} // MDJM_Permissions
