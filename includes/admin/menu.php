<?php

/**
 * Menu Pages
 *
 * @package     MDJM
 * @subpackage	Admin/Pages
 * @copyright   Copyright (c) 2016, Mike Howard
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.3
 */
 
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/*
 * Builds the admin menu
 * 
 * @since	1.3
 * @param
 * @return	void
 */
function mdjm_admin_menu()	{
	
	if( ! current_user_can( 'mdjm_employee' ) )	{
		return;
	}
	
	global $mdjm_dashboard_page, $mdjm_settings_page, $mdjm_contract_template_page, $mdjm_email_template_page, 
	       $mdjm_auto_tasks_page, $mdjm_clients_page, $mdjm_comms_page, $mdjm_comms_history_page,
		   $mdjm_availability_page, $mdjm_emp_page, $mdjm_packages_page, $mdjm_reports_page, $mdjm_tools_page,
		   $mdjm_transactions_page, $mdjm_venues_page, $mdjm_playlist_page, $mdjm_custom_event_fields_page,
		   $mdjm_custom_client_fields_page, $mdjm_extensions_page;
	
	
	//$mdjm_dashboard_page	= add_submenu_page( 'edit.php?post_type=mdjm-event', __( 'Dashboard', 'mobile-dj-manager' ), __( 'Dashboard', 'mobile-dj-manager' ), 'mdjm_employee', 'mdjm-dashboard', 'mdjm_dashboard_page' );
	$mdjm_settings_page	 = add_submenu_page( 'edit.php?post_type=mdjm-event', __( 'Settings', 'mobile-dj-manager' ), __( 'Settings', 'mobile-dj-manager' ), 'manage_mdjm', 'mdjm-settings', 'mdjm_options_page' );
	
	if( mdjm_employee_can( 'manage_templates' ) )	{
		$mdjm_contract_template_page	= add_submenu_page( 'edit.php?post_type=mdjm-event', __( 'Contract Templates', 'mobile-dj-manager' ), __( 'Contract Templates', 'mobile-dj-manager' ), 'mdjm_employee', 'edit.php?post_type=contract', '' );
		$mdjm_email_template_page	   = add_submenu_page( 'edit.php?post_type=mdjm-event', __( 'Email Templates', 'mobile-dj-manager' ), __( 'Email Templates', 'mobile-dj-manager' ), 'mdjm_employee', 'edit.php?post_type=email_template', '' );
	}

	$mdjm_auto_tasks_page	= add_submenu_page( 'edit.php?post_type=mdjm-event', __( 'Automated Tasks', 'mobile-dj-manager' ), __( 'Automated Tasks', 'mobile-dj-manager' ), 'manage_mdjm', 'mdjm-tasks', 'mdjm_tasks_page' );
	
	if( mdjm_employee_can( 'view_clients_list' ) )	{
		$mdjm_clients_page = add_submenu_page( 'edit.php?post_type=mdjm-event', __( 'Clients', 'mobile-dj-manager' ), __( 'Clients', 'mobile-dj-manager' ), 'mdjm_employee', 'mdjm-clients', array( MDJM()->users, 'client_manager' ) );
	}
	
	if( mdjm_employee_can( 'send_comms' ) )	{
		$mdjm_comms_page = add_submenu_page( 'edit.php?post_type=mdjm-event', __( 'Communications', 'mobile-dj-manager' ), __( 'Communications', 'mobile-dj-manager' ), 'mdjm_employee', 'mdjm-comms', 'mdjm_comms_page' );
		
		$mdjm_comms_history_page = add_submenu_page( 'edit.php?post_type=mdjm-event', __( 'Communication History', 'mobile-dj-manager' ), '&nbsp;&nbsp;&nbsp;&mdash;&nbsp;' . __( 'History', 'mobile-dj-manager' ), 'mdjm_employee', 'edit.php?post_type=mdjm_communication' );
	}
	
	/**
	 * Placeholder for the Contact Forms menu item
	 */
	if( mdjm_is_admin() )	{
		do_action( 'mdjm_dcf_menu_items' );
	}
	
	if( mdjm_is_employer() && mdjm_employee_can( 'manage_employees' ) )	{
		$mdjm_emp_page = add_submenu_page(  'edit.php?post_type=mdjm-event', __(  'Employees', 'mobile-dj-manager' ), __(  'Employees', 'mobile-dj-manager' ), 'mdjm_employee', 'mdjm-employees', array( MDJM()->users, 'employee_manager' ) );
	}
	
	$mdjm_availability_page = add_submenu_page( 'edit.php?post_type=mdjm-event', __( 'Employee Availability', 'mobile-dj-manager' ), '&nbsp;&nbsp;&nbsp;&mdash;&nbsp;' . __( 'Availability', 'mobile-dj-manager' ), 'manage_mdjm', 'mdjm-availability', 'mdjm_employee_availability_page' );
											
	if( ( mdjm_get_option( 'enable_packages' ) ) && mdjm_employee_can( 'manage_packages' ) )	{
		$mdjm_packages_page = add_submenu_page( 'edit.php?post_type=mdjm-event', __( 'Packages', 'mobile-dj-manager' ), __( 'Packages', 'mobile-dj-manager' ), 'mdjm_package_edit_own', 'edit.php?post_type=mdjm-package', '' );
		$mdjm_addons_page = add_submenu_page( 'edit.php?post_type=mdjm-event', __( 'Addons', 'mobile-dj-manager' ), '&nbsp;&nbsp;&nbsp;&mdash;&nbsp;' . __( 'Addons', 'mobile-dj-manager' ), 'mdjm_package_edit_own', 'edit.php?post_type=mdjm-addon', '' );
	}
	
	if ( mdjm_employee_can( 'edit_txns' ) )	{									   
		$mdjm_transactions_page = add_submenu_page( 'edit.php?post_type=mdjm-event', __( 'Transactions', 'mobile-dj-manager' ), __( 'Transactions', 'mobile-dj-manager' ), 'mdjm_employee', 'edit.php?post_type=mdjm-transaction', '' );
	}
	
	if ( mdjm_employee_can( 'list_venues' ) )	{
		$mdjm_venues_page = add_submenu_page( 'edit.php?post_type=mdjm-event', __( 'Venues', 'mobile-dj-manager' ), __( 'Venues', 'mobile-dj-manager' ), 'mdjm_employee', 'edit.php?post_type=mdjm-venue', '' );
	}

	$mdjm_tools_page                = add_submenu_page( 'edit.php?post_type=mdjm-event', __( 'Tools', 'mobile-dj-manager' ), __( 'Tools', 'mobile-dj-manager' ), 'mdjm_employee', 'mdjm-tools', 'mdjm_tools_page' );
	$mdjm_reports_page              = add_submenu_page( 'edit.php?post_type=mdjm-event', __( 'Reports', 'mobile-dj-manager' ), __( 'Reports', 'mobile-dj-manager' ), 'mdjm_employee', 'mdjm-reports', 'mdjm_reports_page' );
	$mdjm_extensions_page           = add_submenu_page( 'edit.php?post_type=mdjm-event', __( 'MDJM Extensions', 'mobile-dj-manager' ),  __( 'Extensions', 'mobile-dj-manager' ), 'mdjm_employee', 'mdjm-addons', 'mdjm_extensions_page' );
	$mdjm_playlist_page             = add_submenu_page( null, __( 'Playlists', 'mobile-dj-manager' ), __( 'Playlists', 'mobile-dj-manager' ), 'mdjm_employee', 'mdjm-playlists', 'mdjm_display_event_playlist_page' );
	$mdjm_custom_event_fields_page  = add_submenu_page( null, __( 'Custom Event Fields', 'mobile-dj-manager' ), __( 'Custom Event Fields', 'mobile-dj-manager' ), 'manage_mdjm', 'mdjm-custom-event-fields', array( 'MDJM_Event_Fields', 'custom_event_field_settings' ) );
	$mdjm_custom_client_fields_page = add_submenu_page( null, __( 'Custom Client Fields', 'mobile-dj-manager' ), __( 'Custom Client Fields', 'mobile-dj-manager' ), 'manage_mdjm', 'mdjm-custom-client-fields', 'mdjm_custom_client_fields_page' );
	$mdjm_upgrades_screen           = add_submenu_page( null, __( 'MDJM Upgrades', 'mobile-dj-manager' ), __( 'MDJM Upgrades', 'mobile-dj-manager' ), 'mdjm_employee', 'mdjm-upgrades', 'mdjm_upgrades_screen' );

} // mdjm_admin_menu
add_action( 'admin_menu', 'mdjm_admin_menu', 9 );

/*
 * Builds the admin toolbar
 * 
 * @since	1.3
 * @param
 * @return	void
 */
function mdjm_admin_toolbar( $admin_bar )	{
	
	if( ! current_user_can( 'mdjm_employee' ) )	{
		return;
	}

	// Build out the toolbar menu structure
	$admin_bar->add_menu( array(
		'id'		=> 'mdjm',
		'title'	 => sprintf( __( 'MDJM %s', 'mobile-dj-manager' ), mdjm_get_label_plural() ),
		'href'	  => mdjm_employee_can( 'read_events' ) ? admin_url( 'edit.php?post_type=mdjm-event' ) : '#',
		'meta'	  => array(
			'title' => __( 'MDJM Event Management', 'mobile-dj-manager' ),            
		),
	) );
	if( mdjm_employee_can( 'read_events' ) )	{
		// Events
		$admin_bar->add_menu( array(
			'id'    	=> 'mdjm-events',
			'parent' 	=> 'mdjm',
			'title' 	 => mdjm_get_label_plural(),
			'href'  	  => admin_url( 'edit.php?post_type=mdjm-event' ),
			'meta'  	  => array(
				'title' =>sprintf( __( 'MDJM %s', 'mobile-dj-manager' ), mdjm_get_label_plural() ),
			),
		) );
	}
	if( mdjm_employee_can( 'manage_all_events' ) )	{
		$admin_bar->add_menu( array(
			'id'     => 'mdjm-add-events',
			'parent' => 'mdjm-events',
			'title'  => sprintf( __( 'Create %s', 'mobile-dj-manager' ), mdjm_get_label_singular() ),
			'href'   => admin_url( 'post-new.php?post_type=mdjm-event' ),
			'meta'   => array(
				'title' => sprintf( __( 'Create New %s', 'mobile-dj-manager' ), mdjm_get_label_singular() ),
			),
		) );
		// Enquiries
		$event_status = array( 
			'mdjm-unattended' => __( 'Unattended Enquiries', 'mobile-dj-manager' ), 
			'mdjm-enquiry' => __( 'View Enquiries', 'mobile-dj-manager' ) );
			
		foreach( $event_status as $current_status => $display )	{
			$status_count = MDJM()->events->mdjm_count_event_status( $current_status );
			if( !$status_count )
				continue;
				
			$admin_bar->add_menu( array(
				'id'     => 'mdjm-' . str_replace( ' ', '-', strtolower( $display ) ),
				'parent' => 'mdjm-events',
				'title'  => $display . ' (' . $status_count . ')',
				'href'   => admin_url( 'edit.php?post_status=' . $current_status . '&post_type=mdjm-event' ),
				'meta'   => array(
					'title' => $display,
				),
			) );
		}
		// Event Types
		$admin_bar->add_menu( array(
			'id'     => 'mdjm-event-types',
			'parent' => 'mdjm-events',
			'title'  =>sprintf( __( '%s Types', 'mobile-dj-manager' ), mdjm_get_label_singular() ),
			'href'   => admin_url( 'edit-tags.php?taxonomy=event-types&post_type=mdjm-event' ),
			'meta'   => array(
				'title' => sprintf( __( 'Manage %s Types', 'mobile-dj-manager' ), mdjm_get_label_singular() ),
			),
		) );
		
		// Playlist Categories
		$admin_bar->add_menu( array(
			'id'     => 'mdjm-playlist-cats',
			'parent' => 'mdjm-events',
			'title'  => __( 'Playlist Categories', 'mobile-dj-manager' ),
			'href'   => admin_url( 'edit-tags.php?taxonomy=playlist-category&post_type=mdjm-playlist' ),
			'meta'   => array(
				'title' => __( 'Manage Playlist Categories', 'mobile-dj-manager' ),
			),
		) );
		
		// Enquiry Sources
		$admin_bar->add_menu( array(
			'id'     => 'mdjm-enquiry-sources',
			'parent' => 'mdjm-events',
			'title'  => __( 'Enquiry Sources', 'mobile-dj-manager' ),
			'href'   => admin_url( 'edit-tags.php?taxonomy=enquiry-source&post_type=mdjm-event' ),
			'meta'   => array(
				'title' => __( 'Manage Enquiry Sources', 'mobile-dj-manager' ),
			),
		) );
	}
	// Dashboard
	/*$admin_bar->add_menu( array(
		'id'		=> 'mdjm-dashboard',
		'parent'	=> 'mdjm',
		'title'	 => __( 'Dashboard', 'mobile-dj-manager' ),
		'href'	  => admin_url( 'admin.php?page=mdjm-dashboard' ),
		'meta'	  => array(
			'title' => __( 'MDJM Dashboard', 'mobile-dj-manager' ),
		),
	) ); */
	// Settings
	if( mdjm_is_admin() )	{
		$admin_bar->add_menu( array(
			'id'		=> 'mdjm-settings',
			'parent'	=> 'mdjm',
			'title'	 => __( 'Settings', 'mobile-dj-manager' ),
			'href'	  => admin_url( 'admin.php?page=mdjm-settings' ),
			'meta'	  => array(
				'title' => __( 'MDJM Settings', 'mobile-dj-manager' ),
			),
		) );
		$admin_bar->add_menu( array(
			'id'		=> 'mdjm-settings-general',
			'parent'	=> 'mdjm-settings',
			'title'	 => __( 'General', 'mobile-dj-manager' ),
			'href'	  => admin_url( 'admin.php?page=mdjm-settings&tab=general' ),
			'meta'	  => array(
				'title' => __( 'MDJM General Settings', 'mobile-dj-manager' ),
			),
		) );
		$admin_bar->add_menu( array(
			'id'		=> 'mdjm-settings-events',
			'parent'	=> 'mdjm-settings',
			'title'	 => mdjm_get_label_plural(),
			'href'	  => admin_url( 'admin.php?page=mdjm-settings&tab=events' ),
			'meta'	  => array(
				'title' => __( 'MDJM Event Settings', 'mobile-dj-manager' ),
			),
		) );
		$admin_bar->add_menu( array(
			'id'		=> 'mdjm-settings-permissions',
			'parent'	=> 'mdjm-settings',
			'title'	 => __( 'Permissions', 'mobile-dj-manager' ),
			'href'	  => admin_url( 'admin.php?page=mdjm-settings&tab=general&section=mdjm_app_permissions' ),
			'meta'	  => array(
				'title' => __( 'MDJM Permission Settings', 'mobile-dj-manager' ),
			),
		) );
		$admin_bar->add_menu( array(
			'id'		=> 'mdjm-settings-emails',
			'parent'	=> 'mdjm-settings',
			'title'	 => sprintf( __( 'Email %s Template Settings', 'mobile-dj-manager' ), '&amp;' ),
			'href'	  => admin_url( 'admin.php?page=mdjm-settings&tab=emails' ),
			'meta'	  => array(
				'title' => sprintf( __( 'MDJM Email %s Template Settings', 'mobile-dj-manager' ), '&amp;' ),
			),
		) );
		$admin_bar->add_menu( array(
			'id'		=> 'mdjm-settings-client-zone',
			'parent'	=> 'mdjm-settings',
			'title'	 => sprintf( 
							__( '%s Settings', 'mobile-dj-manager' ), 
							mdjm_get_option( 'app_name', __( 'Client Zone', 'mobile-dj-manager' ) )
						),
			'href'	  => admin_url( 'admin.php?page=mdjm-settings&tab=client_zone' ),
			'meta'	  => array(
				'title'	 => sprintf( 
								__( '%s Settings', 'mobile-dj-manager' ), 
								mdjm_get_option( 'app_name', __( 'Client Zone', 'mobile-dj-manager' ) )
							),
			)
		) );
		$admin_bar->add_menu( array(
			'id'		=> 'mdjm-settings-payments',
			'parent'	=> 'mdjm-settings',
			'title'	 => __( 'Payment Settings', 'mobile-dj-manager' ),
			'href'	  => admin_url( 'admin.php?page=mdjm-settings&tab=payments' ),
			'meta'	  => array(
				'title' => __( 'MDJM Payment Settings', 'mobile-dj-manager' ),
			),
		) );
	}
	do_action( 'mdjm_admin_bar_settings_items', $admin_bar );
	if( mdjm_is_employer() && mdjm_employee_can( 'manage_employees' ) )	{
		// Employees
		$admin_bar->add_menu( array(
			'id'		=> 'mdjm-employees',
			'parent'	=> 'mdjm',
			'title'	 => __( 'Employees', 'mobile-dj-manager' ),
			'href'	  => admin_url( 'admin.php?page=mdjm-employees' ),
			'meta'	  => array(
				'title' => __(  'Employees', 'mobile-dj-manager' ),
			),
		) );
	}
	if( mdjm_is_admin() )	{
		// Employee Availability
		$admin_bar->add_menu( array(
			'id'		=> 'mdjm-availability',
			'parent'	=> mdjm_is_employer() ? 'mdjm-employees' : 'mdjm',
			'title'	 => __(  'Employee Availability', 'mobile-dj-manager' ),
			'href'	  => admin_url( 'admin.php?page=mdjm-availability' ),
			'meta'	  => array(
				'title' => __(  'Employee Availability', 'mobile-dj-manager' ),
			),
		) );
	// Automated Tasks
		$admin_bar->add_menu( array(
			'id'		=> 'mdjm-tasks',
			'parent'	=> 'mdjm',
			'title'	 => __( 'Automated Tasks', 'mobile-dj-manager' ),
			'href'	  => admin_url( 'admin.php?page=mdjm-tasks' ),
			'meta'	  => array(
				'title' => __( 'Automated Tasks', 'mobile-dj-manager' ),
			),
		) );
	}
	if( mdjm_employee_can( 'view_clients_list' ) )	{
		// Clients
		$admin_bar->add_menu( array(
			'id'		=> 'mdjm-clients',
			'parent'	=> 'mdjm',
			'title'	 => __( 'Clients', 'mobile-dj-manager' ),
			'href'	  => admin_url( 'admin.php?page=mdjm-clients' ),
			'meta'	  => array(
				'title' => __( 'Clients', 'mobile-dj-manager' ),
			),
		) );
	}
	if( mdjm_employee_can( 'list_all_clients' ) )	{
		$admin_bar->add_menu( array(
			'id'		=> 'mdjm-add-client',
			'parent'	=> 'mdjm-clients',
			'title'	 => __( 'Add Client', 'mobile-dj-manager' ),
			'href'	  => admin_url( 'user-new.php' ),
			'meta'	  => array(
				'title' => __( 'Add New Client', 'mobile-dj-manager' ),
			),
		) );
		$admin_bar->add_menu( array(
			'id'		=> 'mdjm-custom-client-fields',
			'parent'	=> 'mdjm-clients',
			'title'	 => __( 'Custom Client Fields', 'mobile-dj-manager' ),
			'href'	  => admin_url( 'admin.php?page=mdjm-custom-client-fields' ),
			'meta'	  => array(
				'title' => __( 'Custom Client Field', 'mobile-dj-manager' ),
			),
		) );
	}
	// Communications
	if( mdjm_employee_can( 'send_comms' ) )	{
		$admin_bar->add_menu( array(
			'id'		=> 'mdjm-comms',
			'parent'	=> 'mdjm',
			'title'	 => __( 'Communications', 'mobile-dj-manager' ),
			'href'	  => admin_url( 'admin.php?page=mdjm-comms' ),
			'meta'	  => array(
				'title' => __( 'Communications', 'mobile-dj-manager' ),
			),
		) );
		$admin_bar->add_menu( array(
			'id'		=> 'edit.php?post_type=mdjm_communication',
			'parent'	=> 'mdjm-comms',
			'title'	 => __( 'Communication History', 'mobile-dj-manager' ),
			'href'	  => admin_url( 'edit.php?post_type=mdjm_communication' ),
			'meta'	  => array(
				'title' => __( 'Communication History', 'mobile-dj-manager' ),
			),
		) );
	}
	// Filter for MDJM DCF Admin Bar Items
	do_action( 'mdjm_dcf_admin_bar_items', $admin_bar );
	if( mdjm_employee_can( 'manage_templates' ) )	{
		// Contract Templates
		$admin_bar->add_menu( array(
			'id'		=> 'mdjm-contracts',
			'parent'	=> 'mdjm',
			'title'	 => __( 'Contract Templates', 'mobile-dj-manager' ),
			'href'	  => admin_url( 'edit.php?post_type=contract' ),
			'meta'	  => array(
				'title' => __( 'Contract Templates', 'mobile-dj-manager' ),
			),
		) );
		$admin_bar->add_menu( array(
			'id'		=> 'mdjm-new-contract',
			'parent'	=> 'mdjm-contracts',
			'title'	 => __( 'Add Contract Template', 'mobile-dj-manager' ),
			'href'	  => admin_url( 'post-new.php?post_type=contract' ),
			'meta'	  => array(
				'title' => __( 'New Contract Template', 'mobile-dj-manager' ),
			),
		) );
	}
	if( mdjm_employee_can( 'manage_templates' ) )	{
		// Email Templates
		$admin_bar->add_menu( array(
			'id'		=> 'mdjm-email-templates',
			'parent'	=> 'mdjm',
			'title'	 => __( 'Email Templates', 'mobile-dj-manager' ),
			'href'	  => admin_url( 'edit.php?post_type=email_template' ),
			'meta'	  => array(
				'title' => __( 'Email Templates', 'mobile-dj-manager' ),
			),
		) );
		$admin_bar->add_menu( array(
			'id'		=> 'mdjm-new-email-template',
			'parent'	=> 'mdjm-email-templates',
			'title'	 => __( 'Add Template', 'mobile-dj-manager' ),
			'href'	  => admin_url( 'post-new.php?post_type=email_template' ),
			'meta'	  => array(
				'title' => __( 'New Email Template', 'mobile-dj-manager' ),
			),
		) );
	}
	// Equipment Packages & Add-ons
	if( mdjm_packages_enabled() && mdjm_employee_can( 'manage_packages' ) )	{
		$admin_bar->add_menu( array(
			'id'     => 'mdjm-packages',
			'parent' => 'mdjm',
			'title'  => __( 'Packages', 'mobile-dj-manager' ),
			'href'   => admin_url( 'edit.php?post_type=mdjm-package' ),
			'meta'   => array(
				'title' => __( 'Packages', 'mobile-dj-manager' ),
			),
		) );
		$admin_bar->add_menu( array(
			'id'     => 'mdjm-package-cats',
			'parent' => 'mdjm-packages',
			'title'  => __( 'Package Categories', 'mobile-dj-manager' ),
			'href'   => admin_url( 'edit-tags.php?taxonomy=package-category&post_type=mdjm-package' ),
			'meta'   => array(
				'title' => __( 'Package Categories', 'mobile-dj-manager' ),
			),
		) );
		$admin_bar->add_menu( array(
			'id'     => 'mdjm-addons',
			'parent' => 'mdjm-packages',
			'title'  => __( 'Add-ons', 'mobile-dj-manager' ),
			'href'   => admin_url( 'edit.php?post_type=mdjm-addon' ),
			'meta'   => array(
				'title' => __( 'Add-ons', 'mobile-dj-manager' ),
			),
		) );
		$admin_bar->add_menu( array(
			'id'     => 'mdjm-addon-cats',
			'parent' => 'mdjm-packages',
			'title'  => __( 'Addon Categories', 'mobile-dj-manager' ),
			'href'   => admin_url( 'edit-tags.php?taxonomy=addon-category&post_type=mdjm-addon' ),
			'meta'   => array(
				'title' => __( 'Addon Categories', 'mobile-dj-manager' ),
			),
		) );
	}

	// Custom Event Fields
	if( mdjm_is_admin() )	{
		$admin_bar->add_menu( array(
			'id'     => 'mdjm-event-fields',
			'parent' => 'mdjm-events',
			'title'  => sprintf( __( 'Custom %s Fields', 'mobile-dj-manager' ), mdjm_get_label_singular() ),
			'href'   => admin_url( 'admin.php?page=mdjm-custom-event-fields' ),
			'meta'   => array(
				'title' => sprintf( __( 'Manage Custom %s Fields', 'mobile-dj-manager' ), mdjm_get_label_singular() ),
			)
		) );
	}
	// Event Quotes
	if( mdjm_get_option( 'online_enquiry', false ) && mdjm_employee_can( 'list_own_quotes' ) )	{
		$admin_bar->add_menu( array(
			'id'     => 'mdjm-event-quotes',
			'parent' => 'mdjm-events',
			'title'  => sprintf( __( '%s Quotes', 'mobile-dj-manager' ), mdjm_get_label_singular() ),
			'href'   => admin_url( 'edit.php?post_type=mdjm-quotes' ),
			'meta'   => array(
				'title' => sprintf( __( 'View %s Quotes', 'mobile-dj-manager' ), mdjm_get_label_singular() ),
			),
	) );	
	}
	// Reporting
	/*if( current_user_can( 'manage_options' ) )	{
		$admin_bar->add_menu( array(
			'id'     => 'mdjm-reports',
			'parent' => 'mdjm',
			'title'  => __( 'Reports', 'mobile-dj-manager' ),
			'href'   => admin_url( 'admin.php?page=mdjm-reports' ),
			'meta'   => array(
				'title' => __( 'MDJM Reports', 'mobile-dj-manager' ),
			),
		) );	
	}*/
	if( mdjm_employee_can( 'edit_txns' ) )	{
	// Transactions
		$admin_bar->add_menu( array(
			'id'     => 'mdjm-transactions',
			'parent' => 'mdjm',
			'title'  => __( 'Transactions', 'mobile-dj-manager' ),
			'href'   => 'edit.php?post_type=mdjm-transaction',
			'meta'   => array(
				'title' => __( 'MDJM Transactions', 'mobile-dj-manager' ),
			),
		) );
		$admin_bar->add_menu( array(
			'id'     => 'mdjm-add-transaction',
			'parent' => 'mdjm-transactions',
			'title'  => __( 'Add Transaction', 'mobile-dj-manager' ),
			'href'   => admin_url( 'post-new.php?post_type=mdjm-transaction' ),
			'meta'   => array(
				'title' => __( 'Add Transaction', 'mobile-dj-manager' ),
			),
		) );
		// Transaction Types
		$admin_bar->add_menu( array(
			'id'     => 'mdjm-transaction-types',
			'parent' => 'mdjm-transactions',
			'title'  => __( 'Transaction Types', 'mobile-dj-manager' ),
			'href'   => admin_url( 'edit-tags.php?taxonomy=transaction-types&post_type=mdjm-transaction' ),
			'meta'   => array(
				'title' => __( 'View / Edit Transaction Types', 'mobile-dj-manager' ),
			),
		) );
	}
	if( mdjm_employee_can( 'list_venues' ) )	{
		// Venues
		$admin_bar->add_menu( array(
			'id'     => 'mdjm-venues',
			'parent' => 'mdjm',
			'title'  => __( 'Venues', 'mobile-dj-manager' ),
			'href'   => admin_url( 'edit.php?post_type=mdjm-venue' ),
			'meta'   => array(
				'title' => __( 'Venues', 'mobile-dj-manager' ),
			),
		) );
		if( mdjm_employee_can( 'add_venues' ) )	{
			$admin_bar->add_menu( array(
				'id'     => 'mdjm-add-venue',
				'parent' => 'mdjm-venues',
				'title'  => __( 'Add Venue', 'mobile-dj-manager' ),
				'href'   => admin_url( 'post-new.php?post_type=mdjm-venue' ),
				'meta'   => array(
					'title' => __( 'Add New Venue', 'mobile-dj-manager' ),
				),
			) );
			$admin_bar->add_menu( array(
				'id'     => 'mdjm-venue-details',
				'parent' => 'mdjm-venues',
				'title'  => __( 'Venue Details', 'mobile-dj-manager' ),
				'href'   => admin_url( 'edit-tags.php?taxonomy=venue-details&post_type=mdjm-venue' ),
				'meta'   => array(
					'title' => __( 'View / Edit Venue Details', 'mobile-dj-manager' ),
				),
			) );
		}
	}
	// MDJM Links
	$admin_bar->add_menu( array(
		'id'     => 'mdjm-user-guides',
		'parent' => 'mdjm',
		'title'  => sprintf( __( '%sDocumentation%s', 'mobile-dj-manager' ), '<span style="color:#F90">', '</span>' ),
		'href'   => 'http://mdjm.co.uk/support/',
		'meta'   => array(
			'title' => __( 'Documentation', 'mobile-dj-manager' ),
			'target' => '_blank'
		),
	));
	$admin_bar->add_menu( array(
		'id'     => 'mdjm-support',
		'parent' => 'mdjm',
		'title'  => sprintf( __( '%sSupport%s', 'mobile-dj-manager' ), '<span style="color:#F90">', '</span>' ),
		'href'   => 'http://www.mydjplanner.co.uk/forums/',
		'meta'   => array(
			'title' => __( 'MDJM Support Forums', 'mobile-dj-manager' ),
			'target' => '_blank'
		),
	));
} // mdjm_admin_toolbar
add_action( 'admin_bar_menu', 'mdjm_admin_toolbar', 99 );

function mdjm_clients_page()	{
	include_once( MDJM_PLUGIN_DIR . '/includes/admin/pages/clients.php' );	
} // mdjm_clients_page

function mdjm_comms_page_old()	{
	include_once( MDJM_PLUGIN_DIR . '/includes/admin/pages/comms.php' );
} // mdjm_comms_page_old

function mdjm_employee_availability_page()	{				
	include_once( MDJM_PLUGIN_DIR . '/includes/admin/pages/availability.php' );
} // mdjm_employee_availability_page
						
function mdjm_dashboard_page()	{
	include_once( MDJM_PLUGIN_DIR . '/includes/admin/pages/dash.php' );
} // mdjm_dashboard_page

function mdjm_custom_client_fields_page()	{
	new MDJM_ClientFields();
} // mdjm_dashboard_page
