<?php
	defined( 'ABSPATH' ) or die( "Direct access to this page is disabled!!!" );
/*
 * Define the menu and toolbar settings for MDJM
 * 
 * 
 *
 */
	/* -- Build the MDJM_Menu class -- */
	if( !class_exists( 'MDJM_Menu' ) )	{
		class MDJM_Menu	{			
			/*
			 * __construct
			 * 
			 *
			 *
			 */
			public function __construct()	{
				add_action( 'admin_menu', array( &$this, 'mdjm_menu' ) ); // Admin menu
				add_action( 'admin_menu', array( &$this, 'menu_for_admin' ) ); // Remove Jetback for non-admins
				add_action( 'admin_bar_menu', array( &$this, 'mdjm_toolbar' ), 99 ); // Admin bar menu
				add_action( 'jetpack_admin_menu', array( &$this, 'remove_jetpack' ) ); // Remove Jetpack for non-Admins
			} // __construct
			
			/*
			 * mdjm_menu
			 * Build the MDJM Admin menu
			 * 
			 *
			 */
	 		public function mdjm_menu()	{
				if( !current_user_can( 'mdjm_employee' ) )
					return;
				
				global $mdjm_settings_page;
				
				// Build out the menu structure
				$mdjm_dahboard_page         = add_menu_page( 
					__( 'MDJM Events' ),
					__( 'MDJM Events', 'mobile-dj-manager' ), 
					'mdjm_employee',
					'mdjm-dashboard',
					array( &$this, 'mdjm_dashboard_page' ),
					plugins_url( 'mobile-dj-manager/assets/images/mdjm-menu-16x16.jpg' ),
					'58.4'
				);
				// Dashboard
				$mdjm_dashboard_page = add_submenu_page(
					'mdjm-dashboard',
					__( 'Dashboard', 'mobile-dj-manager' ),
					__( 'Dashboard', 'mobile-dj-manager' ),
					'mdjm_employee',
					'mdjm-dashboard',
					array( &$this, 'mdjm_dashboard_page' )
				);
				// Settings Page
				$mdjm_settings_page = add_submenu_page(
					'mdjm-dashboard',
					__( 'Settings', 'mobile-dj-manager' ),
					__( 'Settings', 'mobile-dj-manager' ),
					'manage_mdjm',
					'mdjm-settings',
					array( $this, 'mdjm_settings_page' )
				);
				// Contract Templates
				if( MDJM()->permissions->employee_can( 'manage_templates' ) )	{
					$mdjm_contract_template_page = add_submenu_page(
						'mdjm-dashboard',
						__( 'Contract Templates', 'mobile-dj-manager' ),
						__( 'Contract Templates', 'mobile-dj-manager' ),
						'mdjm_employee',
						'edit.php?post_type=' . MDJM_CONTRACT_POSTS,
						''
					);
					// Email Templates
					$mdjm_email_template_page = add_submenu_page(
						'mdjm-dashboard',
						__( 'Email Templates', 'mobile-dj-manager' ),
						__( 'Email Templates', 'mobile-dj-manager' ),
						'mdjm_employee',
						'edit.php?post_type=' . MDJM_EMAIL_POSTS,
						''
					);
				}
				// Automated Tasks
				$mdjm_auto_tasks_page = add_submenu_page(
					'mdjm-dashboard',
					__( 'Automated Tasks', 'mobile-dj-manager' ),
					__( 'Automated Tasks', 'mobile-dj-manager' ),
					'manage_mdjm',
					'mdjm-tasks',
					array( &$this, 'mdjm_auto_tasks_page' )
				);
				// Clients
				if( MDJM()->permissions->employee_can( 'view_clients_list' ) )	{
					$mdjm_clients_page = add_submenu_page(
						'mdjm-dashboard',
						__( 'Clients', 'mobile-dj-manager' ),
						__( 'Clients', 'mobile-dj-manager' ),
						'mdjm_client_edit_own',
						'mdjm-clients',
						array( MDJM()->users, 'client_manager' )
					);
				}
				// Communications Page
				if( MDJM()->permissions->employee_can( 'send_comms' ) )	{
					$mdjm_comms_page = add_submenu_page( 
						'mdjm-dashboard',
						__( 'Communications', 'mobile-dj-manager' ),
						__( 'Communications', 'mobile-dj-manager' ),
						'mdjm_comms_send',
						'mdjm-comms',
						array( &$this, 'mdjm_comms_page' )
					);
				}
				/**
				 * Placeholder for the Contact Forms menu item
				 */
				if( current_user_can( 'manage_options' ) )
					do_action( 'mdjm_dcf_menu_items' );
				
				// DJ availability
				$mdjm_availability_page = add_submenu_page(
					'mdjm-dashboard',
					sprintf( __( '%s  Availability', 'mobile-dj-manager' ), MDJM_DJ ),
					sprintf( __( '%s  Availability', 'mobile-dj-manager' ), MDJM_DJ ),
					'manage_mdjm',
					'mdjm-availability',
					array( &$this, 'mdjm_dj_availability_page' )
				);
														
				// Employees
				if( MDJM_MULTI == true )	{
					$mdjm_emp_page = add_submenu_page( 
						'mdjm-dashboard',
						__(  'Employees', 'mobile-dj-manager' ),
						__(  'Employees', 'mobile-dj-manager' ),
						'mdjm_employee_edit',
						'mdjm-employees',
						array( MDJM()->users, 'employee_manager' )
					);
				}
														
				// Equipment Packages & Add-ons
				if( MDJM_PACKAGES == true )	{
					$mdjm_packages_page = add_submenu_page(
						'mdjm-dashboard',
						__( 'Equipment Packages', 'mobile-dj-manager' ),
						__( 'Equipment Packages', 'mobile-dj-manager' ),
						'mdjm_package_edit_own',
						'mdjm-packages',
						array( &$this, 'mdjm_packages_page' )
					);
				}
				// Events
				$mdjm_events_page = add_submenu_page(
					'mdjm-dashboard',
					__( 'Events', 'mobile-dj-manager' ),
					__( 'Events', 'mobile-dj-manager' ),
					'mdjm_event_read_own',
					'edit.php?post_type=' . MDJM_EVENT_POSTS,
					''
				);
				
				// Reporting
				/*$mdjm_reports_page = add_submenu_page(
					'mdjm-dashboard',
					__( 'Reports', 'mobile-dj-manager' ),
					__( 'Reports', 'mobile-dj-manager' ),
					'mdjm_employee',
					admin_url( 'admin.php?page=mdjm-reports' ) );*/
													   
				// Transactions
				if( MDJM_PAYMENTS == true )	{
					$mdjm_transactions_page = add_submenu_page(
						'mdjm-dashboard',
						__( 'Transactions', 'mobile-dj-manager' ),
						__( 'Transactions', 'mobile-dj-manager' ),
						'mdjm_txn_edit',
						'edit.php?post_type=' . MDJM_TRANS_POSTS,
						''
					);
				}
				// Venues
				$mdjm_venues_page = add_submenu_page(
					'mdjm-dashboard',
					__( 'Venues', 'mobile-dj-manager' ),
					__( 'Venues', 'mobile-dj-manager' ),
					'mdjm_venue_read',
					'edit.php?post_type=' . MDJM_VENUE_POSTS,
					''
				);
														  
				// Premium Extensions
				$mdjm_addons_page = add_submenu_page(
					'mdjm-dashboard',
					__( 'Extensions', 'mobile-dj-manager' ),
					'<span style="color: #F90;">' . __( 'Extensions' ) . '</span>',
					'manage_mdjm',
					'admin.php?page=mdjm-settings&tab=addons',
					''
				);
								
				// This is for the playlist, does not display on menu
				add_submenu_page( 
					  null,
					__( 'Playlists', 'mobile-dj-manager' ),
					__( 'Playlists', 'mobile-dj-manager' ),
					'mdjm_event_edit_own',
					'mdjm-playlists',
					array( &$this, 'mdjm_playlists_page' )
				);
				
				// This is for the about page, does not display on menu			  
				add_submenu_page( 
					  null,
					__( 'About MDJM', 'mobile-dj-manager' ),
					__( 'About MDJM', 'mobile-dj-manager' ),
					'mdjm_employee',
					'mdjm-about',
					array( &$this, 'mdjm_about_page' )
				);
			} // mdjm_menu
			
			/*
			 * Remove admin menu items for non WP Admins
			 *
			 * 
			 *
			 */
			public function menu_for_admin()	{
				global $mdjm_settings;
				
				if( !current_user_can( 'administrator' ) )	{
					if( !isset( $mdjm_settings['permissions']['dj_see_wp_dash'] ) ) 
						remove_menu_page( 'index.php' );
					
					remove_menu_page( 'profile.php' );
				}	
			} // menu_for_admin
			
			/*
			 * mdjm_toolbar
			 * Build the MDJM Admin toolbar
			 * 
			 *
			 */
	 		public function mdjm_toolbar( $admin_bar )	{
				if( !current_user_can( 'mdjm_employee' ) )
					return;
			
				/* -- Build out the toolbar menu structure -- */
				$admin_bar->add_menu( array(
					'id'		=> 'mdjm',
					'title'	 => __( 'MDJM Events', 'mobile-dj-manager' ),
					'href'	  => admin_url( 'admin.php?page=mdjm-dashboard' ),
					'meta'	  => array(
						'title' => __( 'MDJM Event Management', 'mobile-dj-manager' ),            
					),
				) );
				/* -- Dashboard -- */
				$admin_bar->add_menu( array(
					'id'		=> 'mdjm-dashboard',
					'parent'	=> 'mdjm',
					'title'	 => __( 'Dashboard', 'mobile-dj-manager' ),
					'href'	  => admin_url( 'admin.php?page=mdjm-dashboard' ),
					'meta'	  => array(
						'title' => __( 'MDJM Dashboard', 'mobile-dj-manager' ),
					),
				) );
				/* -- Settings -- */
				if( current_user_can( 'manage_mdjm' ) )	{
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
						'title'	 => __( 'Events', 'mobile-dj-manager' ),
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
						'title'	 => sprintf( __( '%s Settings', 'mobile-dj-manager' ), MDJM_APP ),
						'href'	  => admin_url( 'admin.php?page=mdjm-settings&tab=client-zone' ),
						'meta'	  => array(
							'title' => sprintf( __( '%s Settings', 'mobile-dj-manager' ), MDJM_APP )
						),
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
				if( current_user_can( 'manage_mdjm' ) )	{				
				/* -- Automated Tasks -- */
					$admin_bar->add_menu( array(
						'id'		=> 'mdjm-tasks',
						'parent'	=> 'mdjm',
						'title'	 => __( 'Automated Tasks', 'mobile-dj-manager' ),
						'href'	  => admin_url( 'admin.php?page=mdjm-tasks' ),
						'meta'	  => array(
							'title' => __( 'Automated Tasks', 'mobile-dj-manager' ),
						),
					) );

					/* -- DJ Availability -- */
					$admin_bar->add_menu( array(
						'id'		=> 'mdjm-availability',
						'parent'	=> 'mdjm',
						'title'	 => sprintf( __(  '%s Availability', 'mobile-dj-manager' ), MDJM_DJ ),
						'href'	  => admin_url( 'admin.php?page=mdjm-availability' ),
						'meta'	  => array(
							'title' => sprintf( __(  '%s Availability', 'mobile-dj-manager' ), MDJM_DJ ),
						),
					) );
				}
				if( MDJM()->permissions->employee_can( 'view_clients_list' ) )	{
					/* -- Clients -- */
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
				if( MDJM()->permissions->employee_can( 'list_all_clients' ) )	{
					$admin_bar->add_menu( array(
						'id'		=> 'mdjm-add-client',
						'parent'	=> 'mdjm-clients',
						'title'	 => __( 'Add Client', 'mobile-dj-manager' ),
						'href'	  => admin_url( 'user-new.php' ),
						'meta'	  => array(
							'title' => __( 'Add New Client', 'mobile-dj-manager' ),
						),
					)) ;
				}
				/* -- Communications -- */
				if( MDJM()->permissions->employee_can( 'send_comms' ) )	{
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
						'id'		=> 'edit.php?post_type=' . MDJM_COMM_POSTS,
						'parent'	=> 'mdjm-comms',
						'title'	 => __( 'Communication History', 'mobile-dj-manager' ),
						'href'	  => admin_url( 'edit.php?post_type=' . MDJM_COMM_POSTS ),
						'meta'	  => array(
							'title' => __( 'Communication History', 'mobile-dj-manager' ),
						),
					) );
				}
				// Filter for MDJM DCF Admin Bar Items
				do_action( 'mdjm_dcf_admin_bar_items', $admin_bar );
				if( MDJM()->permissions->employee_can( 'manage_templates' ) )	{
					/* -- Contract Templates -- */
					$admin_bar->add_menu( array(
						'id'		=> 'mdjm-contracts',
						'parent'	=> 'mdjm',
						'title'	 => __( 'Contract Templates', 'mobile-dj-manager' ),
						'href'	  => admin_url( 'edit.php?post_type=' . MDJM_CONTRACT_POSTS ),
						'meta'	  => array(
							'title' => __( 'Contract Templates', 'mobile-dj-manager' ),
						),
					) );
					$admin_bar->add_menu( array(
						'id'		=> 'mdjm-new-contract',
						'parent'	=> 'mdjm-contracts',
						'title'	 => __( 'Add Contract Template', 'mobile-dj-manager' ),
						'href'	  => admin_url( 'post-new.php?post_type=' . MDJM_CONTRACT_POSTS ),
						'meta'	  => array(
							'title' => __( 'New Contract Template', 'mobile-dj-manager' ),
						),
					) );
				}
				if( MDJM_MULTI == true && MDJM()->permissions->employee_can( 'manage_employees' ) )	{
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
				if( MDJM()->permissions->employee_can( 'manage_templates' ) )	{
					/* -- Email Templates -- */
					$admin_bar->add_menu( array(
						'id'		=> 'mdjm-email-templates',
						'parent'	=> 'mdjm',
						'title'	 => __( 'Email Templates', 'mobile-dj-manager' ),
						'href'	  => admin_url( 'edit.php?post_type=' . MDJM_EMAIL_POSTS ),
						'meta'	  => array(
							'title' => __( 'Email Templates', 'mobile-dj-manager' ),
						),
					) );
					$admin_bar->add_menu( array(
						'id'		=> 'mdjm-new-email-template',
						'parent'	=> 'mdjm-email-templates',
						'title'	 => __( 'Add Template', 'mobile-dj-manager' ),
						'href'	  => admin_url( 'post-new.php?post_type=' . MDJM_EMAIL_POSTS ),
						'meta'	  => array(
							'title' => __( 'New Email Template', 'mobile-dj-manager' ),
						),
					) );
				}
				/* -- Equipment Packages & Add-ons -- */
				if( MDJM_PACKAGES == true && MDJM()->permissions->employee_can( 'manage_packages' ) )	{
					$admin_bar->add_menu( array(
						'id'		=> 'mdjm-equipment',
						'parent'	=> 'mdjm',
						'title'	 => sprintf( __( 'Equipment %s Packages', 'mobile-dj-manager' ), '&amp;' ),
						'href'	  => admin_url( 'admin.php?page=mdjm-packages' ),
						'meta'	  => array(
							'title' => sprintf( __( 'Equipment %s Packages', 'mobile-dj-manager' ), '&amp;' ),
						),
					) );
				}
				if( MDJM()->permissions->employee_can( 'read_events' ) )	{
					/* -- Events -- */
					$admin_bar->add_menu( array(
						'id'    	=> 'mdjm-events',
						'parent' 	=> 'mdjm',
						'title' 	 => __( 'Events', 'mobile-dj-manager' ),
						'href'  	  => admin_url( 'edit.php?post_type=' . MDJM_EVENT_POSTS ),
						'meta'  	  => array(
							'title' => __( 'MDJM Events', 'mobile-dj-manager' ),
						),
					) );
				}
								
				if( MDJM()->permissions->employee_can( 'manage_all_events' ) )	{
					$admin_bar->add_menu( array(
						'id'     => 'mdjm-add-events',
						'parent' => 'mdjm-events',
						'title'  => __( 'Create Event', 'mobile-dj-manager' ),
						'href'   => admin_url( 'post-new.php?post_type=' . MDJM_EVENT_POSTS ),
						'meta'   => array(
							'title' => __( 'Create New Event', 'mobile-dj-manager' ),
						),
					) );
					/* -- Enquiries -- */
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
							'href'   => admin_url( 'edit.php?post_status=' . $current_status . ' &post_type=' . MDJM_EVENT_POSTS ),
							'meta'   => array(
								'title' => $display,
							),
						) );
					}
					// Event Types
					$admin_bar->add_menu( array(
						'id'     => 'mdjm-event-types',
						'parent' => 'mdjm-events',
						'title'  => __( 'Event Types', 'mobile-dj-manager' ),
						'href'   => admin_url( 'edit-tags.php?taxonomy=event-types&post_type=' . MDJM_EVENT_POSTS ),
						'meta'   => array(
							'title' => __( 'View / Edit Event Types', 'mobile-dj-manager' ),
						),
					) );
				}
				// Custom Event Fields
				if( current_user_can( 'manage_mdjm' ) )	{
					$admin_bar->add_menu( array(
						'id'     => 'mdjm-event-fields',
						'parent' => 'mdjm-events',
						'title'  => __( 'Custom Event Fields', 'mobile-dj-manager' ),
						'href'   => admin_url( 'admin.php?page=mdjm-settings&tab=events&section=mdjm_custom_event_fields' ),
						'meta'   => array(
							'title' => __( 'Manage Custom Event Fields', 'mobile-dj-manager' ),
						)
					) );
				}
				// Event Quotes
				if( MDJM_ONLINE_QUOTES == true && MDJM()->permissions->employee_can( 'list_own_quotes' ) )	{
					$admin_bar->add_menu( array(
						'id'     => 'mdjm-event-quotes',
						'parent' => 'mdjm-events',
						'title'  => __( 'Event Quotes', 'mobile-dj-manager' ),
						'href'   => admin_url( 'edit.php?post_type=' . MDJM_QUOTE_POSTS ),
						'meta'   => array(
							'title' => __( 'View Event Quotes', 'mobile-dj-manager' ),
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
				if( MDJM()->permissions->employee_can( 'edit_txns' ) )	{
				/* -- Transactions -- */
					$admin_bar->add_menu( array(
						'id'     => 'mdjm-transactions',
						'parent' => 'mdjm',
						'title'  => __( 'Transactions', 'mobile-dj-manager' ),
						'href'   => 'edit.php?post_type=' . MDJM_TRANS_POSTS,
						'meta'   => array(
							'title' => __( 'MDJM Transactions', 'mobile-dj-manager' ),
						),
					) );
					$admin_bar->add_menu( array(
						'id'     => 'mdjm-add-transaction',
						'parent' => 'mdjm-transactions',
						'title'  => __( 'Add Transaction', 'mobile-dj-manager' ),
						'href'   => admin_url( 'post-new.php?post_type=' . MDJM_TRANS_POSTS ),
						'meta'   => array(
							'title' => __( 'Add Transaction', 'mobile-dj-manager' ),
						),
					) );
					/* -- Transaction Types -- */
					$admin_bar->add_menu( array(
						'id'     => 'mdjm-transaction-types',
						'parent' => 'mdjm-transactions',
						'title'  => __( 'Transaction Types', 'mobile-dj-manager' ),
						'href'   => admin_url( 'edit-tags.php?taxonomy=transaction-types&post_type=' . MDJM_TRANS_POSTS ),
						'meta'   => array(
							'title' => __( 'View / Edit Transaction Types', 'mobile-dj-manager' ),
						),
					) );
				}
				if( MDJM()->permissions->employee_can( 'list_venues' ) )	{
					/* -- Venues -- */
					$admin_bar->add_menu( array(
						'id'     => 'mdjm-venues',
						'parent' => 'mdjm',
						'title'  => __( 'Venues', 'mobile-dj-manager' ),
						'href'   => admin_url( 'edit.php?post_type=' . MDJM_VENUE_POSTS ),
						'meta'   => array(
							'title' => __( 'Venues', 'mobile-dj-manager' ),
						),
					) );
					if( MDJM()->permissions->employee_can( 'add_venues' ) )	{
						$admin_bar->add_menu( array(
							'id'     => 'mdjm-add-venue',
							'parent' => 'mdjm-venues',
							'title'  => __( 'Add Venue', 'mobile-dj-manager' ),
							'href'   => admin_url( 'post-new.php?post_type=' . MDJM_VENUE_POSTS ),
							'meta'   => array(
								'title' => __( 'Add New Venue', 'mobile-dj-manager' ),
							),
						) );
						$admin_bar->add_menu( array(
							'id'     => 'mdjm-venue-details',
							'parent' => 'mdjm-venues',
							'title'  => __( 'Venue Details', 'mobile-dj-manager' ),
							'href'   => admin_url( 'edit-tags.php?taxonomy=venue-details&post_type=' . MDJM_VENUE_POSTS ),
							'meta'   => array(
								'title' => __( 'View / Edit Venue Details', 'mobile-dj-manager' ),
							),
						) );
					}
				}
				/* -- My DJ Planner Links -- */
				$admin_bar->add_menu( array(
					'id'     => 'mdjm-user-guides',
					'parent' => 'mdjm',
					'title'  => sprintf( __( '%sUser Guides%s', 'mobile-dj-manager' ), '<span style="color:#F90">', '</span>' ),
					'href'   => 'http://www.mydjplanner.co.uk/support/user-guides/',
					'meta'   => array(
						'title' => __( 'MDJM User Guides', 'mobile-dj-manager' ),
						'target' => '_blank'
					),
				));
				$admin_bar->add_menu( array(
					'id'     => 'mdjm-support',
					'parent' => 'mdjm',
					'title'  => sprintf( __( '%sSupport%s', 'mobile-dj-manager' ), '<span style="color:#F90">', '</span>' ),
					'href'   => 'http://www.mydjplanner.co.uk/support/',
					'meta'   => array(
						'title' => __( 'MDJM Support Forums', 'mobile-dj-manager' ),
						'target' => '_blank'
					),
				));
				if( current_user_can( 'manage_mdjm' ) )	{
					$admin_bar->add_menu( array(
						'id'     => 'mdjm-extensions',
						'parent' => 'mdjm',
						'title'  => sprintf( __( '%sExtensions%s', 'mobile-dj-manager' ), '<span style="color:#F90">', '</span>' ),
						'href'   => admin_url( 'admin.php?page=mdjm-settings&tab=addons' ),
						'meta'   => array(
							'title' => __( 'MDJM Extensions', 'mobile-dj-manager' )
						),
					));
				}
			} // mdjm_toolbar
			
			/*
			 * remove_jetpack
			 * Remove JetPack for non WP Admins
			 * 
			 *
			 */
			public function remove_jetpack()	{
				if( !current_user_can( 'administrator' ) )
					remove_menu_page( 'jetpack' );
			} // remove_jetpack
			
/*
 * --
 * ADMIN PAGES
 * --
 */
	 		/*
			 * mdjm_auto_tasks_page
			 * The MDJM Automated Tasks page
			 */			
			public function mdjm_auto_tasks_page()	{				
				include_once( '/includes/admin/pages/settings-scheduler.php' );
			} // mdjm_auto_tasks_page
			/*
			 * mdjm_clients_page
			 * The MDJM Client list
			 */
			public function mdjm_clients_page()	{
				include_once( '/includes/admin/pages/clients.php' );	
			} // mdjm_clients_page
			/*
			 * mdjm_comms_page
			 * The MDJM Communications page
			 */			
			public function mdjm_comms_page()	{
				include_once( '/includes/admin/pages/comms.php' );
			} // mdjm_comms_page
			/*
			 * mdjm_dj_availability_page
			 * The MDJM DJ Availability page
			 */			
			public function mdjm_dj_availability_page()	{				
				include_once( '/includes/admin/pages/availability.php' );
			} // mdjm_dj_availability_page
			/*
			 * mdjm_packages_page
			 * The MDJM DJ Availability page
			 */			
			public function mdjm_packages_page()	{					
				if( !MDJM_PACKAGES )	{
					wp_die(
						'<h1>' . __( 'Ooops!' ) . '</h1>' .
						'<p>' . 
						sprintf( 
							__( 'Equipment Packages & Add-ons are not enabled. You can enable them %shere%s', 'mobile-dj-manager' ),
							'<a href="' . mdjm_get_admin_page( 'settings', 'echo' ) . '</p>'
						),
						403
					);
				}
					
				include_once( '/includes/admin/pages/settings-packages-main.php' );
			} // mdjm_packages_page
						
	 		/*
			 * mdjm_dashboard_page
			 * The MDJM Dashboard admin page
			 */			
			public function mdjm_dashboard_page()	{
				include_once( '/includes/admin/pages/dash.php' );
			} // mdjm_dashboard_page
			
			/*
			 * mdjm_settings_page
			 * The MDJM Settings page
			 */			
			public function mdjm_settings_page()	{
				include_once( MDJM_PLUGIN_DIR . '/includes/admin/settings/class-mdjm-settings-page.php' );
			} // mdjm_settings_page
						
			/*
			 * The MDJM Playlists page
			 *
			 *
			 *
			 */
			public function mdjm_playlists_page()	{
				include_once( '/includes/admin/pages/playlists.php' );
			} // mdjm_playlists_page
			
			/*
			 * The MDJM About page displays plugin information and is generally called following an update
			 *
			 *
			 *
			 */
			public function mdjm_about_page()	{
				include_once( '/includes/admin/pages/mdjm-about.php' );
			} // mdjm_about_page
		} // class
	}