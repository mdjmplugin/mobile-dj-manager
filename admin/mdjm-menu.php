<?php
/*
 * class-mdjm-menu.php
 * 10/03/2015
 * @since 1.1.2
 * The main MDJM class
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
				global $mdjm, $mdjm_settings_page;
				/* -- Build out the menu structure -- */
				add_menu_page( __( 'MDJM Events' ),
													  __( 'MDJM Events', 'mobile-dj-manager' ), 
													  'manage_mdjm',
													  'mdjm-dashboard',
													  array( &$this, 'mdjm_dashboard_page' ),
													  plugins_url( 'mobile-dj-manager/admin/images/mdjm-menu-16x16.jpg' ),
													  '58.4' );
				/* -- Dashboard -- */									  
				$mdjm_dashboard_page = add_submenu_page( 'mdjm-dashboard',
														 __( 'Dashboard', 'mobile-dj-manager' ),
														 __( 'Dashboard', 'mobile-dj-manager' ),
														 'manage_mdjm',
														 'mdjm-dashboard',
														 array( &$this, 'mdjm_dashboard_page' ) );
				/* -- Settings Page -- */
				if( current_user_can( 'manage_options' ) ) 
					$mdjm_settings_page = add_submenu_page( 'mdjm-dashboard',
															__( 'Settings', 'mobile-dj-manager' ),
															__( 'Settings', 'mobile-dj-manager' ),
															'manage_mdjm',
															'mdjm-settings',
															array( $this, 'mdjm_settings_page' ) );
				/* -- Contract Templates -- */
				if( current_user_can( 'manage_options' ) )
					$mdjm_contract_template_page = add_submenu_page( 'mdjm-dashboard',
																	 __( 'Contract Templates', 'mobile-dj-manager' ),
																	 __( 'Contract Templates', 'mobile-dj-manager' ),
																	 'manage_mdjm',
																	 'edit.php?post_type=' . MDJM_CONTRACT_POSTS,
																	 '' );
				/* -- Email Templates -- */	
				if( current_user_can( 'manage_options' ) )
					$mdjm_email_template_page = add_submenu_page( 'mdjm-dashboard',
																  __( 'Email Templates', 'mobile-dj-manager' ),
																  __( 'Email Templates', 'mobile-dj-manager' ),
																  'manage_mdjm',
																  'edit.php?post_type=' . MDJM_EMAIL_POSTS,
																  '' );
				/* -- Automated Tasks -- */	
				if( current_user_can( 'manage_options' ) )
					$mdjm_auto_tasks_page = add_submenu_page( 'mdjm-dashboard',
															  __( 'Automated Tasks', 'mobile-dj-manager' ),
															  __( 'Automated Tasks', 'mobile-dj-manager' ),
															  'manage_mdjm',
															  'mdjm-tasks',
															  array( &$this, 'mdjm_auto_tasks_page' ) );
				/* -- Clients -- */
				$mdjm_clients_page = add_submenu_page( 'mdjm-dashboard',
													   __( 'Clients', 'mobile-dj-manager' ),
													   __( 'Clients', 'mobile-dj-manager' ),
													   'manage_mdjm',
													   'mdjm-clients',
													    array( &$this, 'mdjm_clients_page' ) );
				/* -- Communications Page -- */
				$mdjm_comms_page = add_submenu_page( 'mdjm-dashboard',
													 __( 'Communications', 'mobile-dj-manager' ),
													 __( 'Communications', 'mobile-dj-manager' ),
													 'manage_mdjm',
													 'mdjm-comms',
													 array( &$this, 'mdjm_comms_page' ) );
				/* -- Communication History -- */
				if( current_user_can( 'manage_options' ) ) 
					$mdjm_comms_history_page = add_submenu_page( 'mdjm-dashboard',
																 __( 'Communication History', 'mobile-dj-manager' ),
																 __( 'Communication History', 'mobile-dj-manager' ),
																 'manage_mdjm',
																 'edit.php?post_type=' . MDJM_COMM_POSTS,
																 '' );
																 
				// Placeholder for the Contact Forms menu item
				if( current_user_can( 'manage_options' ) )
					do_action( 'mdjm_dcf_menu_items' );
				/* -- DJ availability -- */
				$mdjm_availability_page = add_submenu_page( 'mdjm-dashboard',
															sprintf( __( '%s  Availability', 'mobile-dj-manager' ), MDJM_DJ ),
															sprintf( __( '%s  Availability', 'mobile-dj-manager' ), MDJM_DJ ),
															'manage_mdjm',
															'mdjm-availability',
															array( &$this, 'mdjm_dj_availability_page' ) );
				/* -- DJ's -- */
				if( MDJM_MULTI == true ) 
					$mdjm_dj_page = add_submenu_page( 'mdjm-dashboard',
													   sprintf( __(  "%s's", 'mobile-dj-manager' ), MDJM_DJ  ),
													   sprintf( __(  "%s's", 'mobile-dj-manager' ), MDJM_DJ  ),
													   'manage_mdjm',
													   'mdjm-djs',
													    array( &$this, 'mdjm_djs_page' ) );
				/* -- Equipment Packages & Add-ons -- */
				if( current_user_can( 'manage_options' ) && MDJM_PACKAGES == true )
					$mdjm_packages_page = add_submenu_page( 'mdjm-dashboard',
														    __( 'Equipment Packages', 'mobile-dj-manager' ),
														    __( 'Equipment Packages', 'mobile-dj-manager' ),
														    'manage_mdjm',
														    'mdjm-packages',
														    array( &$this, 'mdjm_packages_page' ) );
				/* -- Events -- */
				$mdjm_events_page = add_submenu_page( 'mdjm-dashboard',
													   __( 'Events', 'mobile-dj-manager' ),
													   __( 'Events', 'mobile-dj-manager' ),
													   'manage_mdjm',
													   'edit.php?post_type=' . MDJM_EVENT_POSTS,
													   '' );
				
				// Reporting
				$mdjm_reports_page = add_submenu_page(
					'mdjm-dashboard',
					__( 'Reports', 'mobile-dj-manager' ),
					__( 'Reports', 'mobile-dj-manager' ),
					'manage_options',
					admin_url( 'admin.php?page=mdjm-reports' ) );
													   
				/* -- Transactions -- */
				if( current_user_can( 'manage_options' ) && MDJM_PAYMENTS == true )
					$mdjm_transactions_page = add_submenu_page( 'mdjm-dashboard',
																__( 'Transactions', 'mobile-dj-manager' ),
																__( 'Transactions', 'mobile-dj-manager' ),
																'manage_mdjm',
																'edit.php?post_type=' . MDJM_TRANS_POSTS,
																'' );
				/* -- Venues -- */
				if( current_user_can( 'manage_options' ) || dj_can( 'add_venue' ) )
					$mdjm_venues_page = add_submenu_page( 'mdjm-dashboard',
														  __( 'Venues', 'mobile-dj-manager' ),
														  __( 'Venues', 'mobile-dj-manager' ),
														  'manage_mdjm',
														  'edit.php?post_type=' . MDJM_VENUE_POSTS,
														  '' );
														  
				// Premium Extensions
				$mdjm_addons_page = add_submenu_page( 'mdjm-dashboard',
													  __( 'Extensions', 'mobile-dj-manager' ),
													  '<span style="color: #F90;">' . __( 'Extensions' ) . '</span>',
													  'manage_options',
													  'admin.php?page=mdjm-settings&tab=addons',
													  '' );
				
				/* -- This is for the playlist, does not display on menu -- */					  
				add_submenu_page( 
					  null,
					__( 'Playlists', 'mobile-dj-manager' ),
					__( 'Playlists', 'mobile-dj-manager' ),
					'manage_mdjm',
					'mdjm-playlists',
					array( &$this, 'mdjm_playlists_page' )
				);
				/* -- This is for the about page, does not display on menu -- */					  
				add_submenu_page( 
					  null,
					__( 'About MDJM', 'mobile-dj-manager' ),
					__( 'About MDJM', 'mobile-dj-manager' ),
					'manage_options',
					'mdjm-about',
					array( &$this, 'mdjm_about_page' )
				);
			} // mdjm_menu
			
			/*
			 * menu_for_admin
			 * Remove admin menu items for non WP Admins
			 * 
			 *
			 */
			public function menu_for_admin()	{
				global $mdjm_settings;
				
				if( !current_user_can( 'administrator' ) )	{
					if( !isset( $mdjm_settings['permissions']['dj_see_wp_dash'] ) ) remove_menu_page( 'index.php' );
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
				global $mdjm;
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
				if( current_user_can( 'manage_options' ) )	{
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
					do_action( 'mdjm_admin_bar_settings_items', $admin_bar );					
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
				}
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
				if( current_user_can( 'manage_options' ) || dj_can( 'add_client' ) )	{
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
				$admin_bar->add_menu( array(
					'id'		=> 'mdjm-comms',
					'parent'	=> 'mdjm',
					'title'	 => __( 'Communications', 'mobile-dj-manager' ),
					'href'	  => admin_url( 'admin.php?page=mdjm-comms' ),
					'meta'	  => array(
						'title' => __( 'Communications', 'mobile-dj-manager' ),
					),
				) );
				if( current_user_can( 'manage_options' ) )	{
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
				if( current_user_can( 'manage_options' ) )	{
					// Filter for MDJM DCF Admin Bar Items
					do_action( 'mdjm_dcf_admin_bar_items', $admin_bar );
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
					/* -- DJ List -- */
					if( MDJM_MULTI == true )	{
						$admin_bar->add_menu( array(
							'id'		=> 'mdjm-djs',
							'parent'	=> 'mdjm',
							'title'	 => sprintf( __( '%s List', 'mobile-dj-manager' ), MDJM_DJ ),
							'href'	  => admin_url( 'admin.php?page=mdjm-djs' ),
							'meta'	  => array(
								'title' => sprintf( __(  '%s List', 'mobile-dj-manager' ), MDJM_DJ ),
							),
						) );
					}
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
					/* -- Equipment Packages & Add-ons -- */
					if( MDJM_PACKAGES == true )	{

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
				}
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
								
				if( current_user_can( 'manage_options' ) || dj_can( 'add_event' ) )	{
					$admin_bar->add_menu( array(
						'id'     => 'mdjm-add-events',
						'parent' => 'mdjm-events',
						'title'  => __( 'Create Event', 'mobile-dj-manager' ),
						'href'   => admin_url( 'post-new.php?post_type=' . MDJM_EVENT_POSTS ),
						'meta'   => array(
							'title' => __( 'Create New Event', 'mobile-dj-manager' ),
						),
					) );
				}
				/* -- Enquiries -- */
				if( current_user_can( 'manage_options' ) || dj_can( 'view_enquiry' ) )	{
					$event_status = array( 
						'mdjm-unattended' => __( 'Unattended Enquiries', 'mobile-dj-manager' ), 
						'mdjm-enquiry' => __( 'View Enquiries', 'mobile-dj-manager' ) );
						
					foreach( $event_status as $current_status => $display )	{
						$status_count = $mdjm->mdjm_events->mdjm_count_event_status( $current_status );
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
				}
				if( current_user_can( 'manage_options' ) )	{
					/* -- Event Types -- */
					$admin_bar->add_menu( array(
						'id'     => 'mdjm-event-types',
						'parent' => 'mdjm-events',
						'title'  => __( 'Event Types', 'mobile-dj-manager' ),
						'href'   => admin_url( 'edit-tags.php?taxonomy=event-types&post_type=' . MDJM_EVENT_POSTS ),
						'meta'   => array(
							'title' => __( 'View / Edit Event Types', 'mobile-dj-manager' ),
						),
					) );
					/* -- Event Quotes -- */
					if( MDJM_ONLINE_QUOTES == true )	{
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
				}
				// Reporting
				if( current_user_can( 'manage_options' ) )	{
					$admin_bar->add_menu( array(
						'id'     => 'mdjm-reports',
						'parent' => 'mdjm',
						'title'  => __( 'Reports', 'mobile-dj-manager' ),
						'href'   => admin_url( 'admin.php?page=mdjm-reports' ),
						'meta'   => array(
							'title' => __( 'MDJM Reports', 'mobile-dj-manager' ),
						),
					) );	
				}
				/* -- Transactions -- */
				if( current_user_can( 'manage_options' ) && MDJM_PAYMENTS == true )	{
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
				/* -- Venues -- */
				if( current_user_can( 'manage_options' ) || dj_can( 'add_venue' ) )	{
					$admin_bar->add_menu( array(
						'id'     => 'mdjm-venues',
						'parent' => 'mdjm',
						'title'  => __( 'Venues', 'mobile-dj-manager' ),
						'href'   => admin_url( 'edit.php?post_type=' . MDJM_VENUE_POSTS ),
						'meta'   => array(
							'title' => __( 'Venues', 'mobile-dj-manager' ),
						),
					) );
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
				$admin_bar->add_menu( array(
					'id'     => 'mdjm-extensions',
					'parent' => 'mdjm',
					'title'  => sprintf( __( '%sExtensions%s', 'mobile-dj-manager' ), '<span style="color:#F90">', '</span>' ),
					'href'   => admin_url( 'admin.php?page=mdjm-settings&tab=addons' ),
					'meta'   => array(
						'title' => __( 'MDJM Extensions', 'mobile-dj-manager' )
					),
				));
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
				if( !current_user_can( 'manage_options' ) )
			        wp_die( __( 'MDJM: This page requires Administrative priviledges.', 'mobile-dj-manager' ) );
				
				include_once( MDJM_PAGES_DIR . '/settings-scheduler.php' );
			} // mdjm_auto_tasks_page
			/*
			 * mdjm_clients_page
			 * The MDJM Client list
			 */
			public function mdjm_clients_page()	{
				if( !current_user_can( 'manage_options' ) && !current_user_can( 'manage_mdjm' ) )
					wp_die( __( 'You do not have sufficient permissions to access this page.', 'mobile-dj-manager' ) );
				
				include_once( MDJM_PAGES_DIR . '/clients.php' );	
			} // mdjm_clients_page
			/*
			 * mdjm_comms_page
			 * The MDJM Communications page
			 */			
			public function mdjm_comms_page()	{				
				include_once( MDJM_PAGES_DIR . '/comms.php' );
			} // mdjm_comms_page
			/*
			 * mdjm_dj_availability_page
			 * The MDJM DJ Availability page
			 */			
			public function mdjm_dj_availability_page()	{				
				include_once( MDJM_PAGES_DIR . '/availability.php' );
			} // mdjm_dj_availability_page
			/*
			 * mdjm_djs_page
			 * The MDJM DJ list
			 */
			public function mdjm_djs_page()	{
				if( !current_user_can( 'manage_options' ) || MDJM_MULTI != true )
					wp_die( __( 'You do not have sufficient permissions to access this page.', 'mobile-dj-manager' ) );
				
				include_once( MDJM_PAGES_DIR . '/djs.php' );	
			} // mdjm_djs_page
			/*
			 * mdjm_packages_page
			 * The MDJM DJ Availability page
			 */			
			public function mdjm_packages_page()	{
				if( !current_user_can( 'manage_options' ) )
					wp_die( __( 'MDJM: This page requires Administrative priviledges.', 'mobile-dj-manager' ) );
					
				if( !MDJM_PACKAGES )
					wp_die( sprintf( __( 'MDJM: Equipment Packages & Add-ons are not enabled. You can enable them %shere%s', 'mobile-dj-manager' ),
						'<a href="' . mdjm_get_admin_page( 'settings', 'echo' ) . '">',
						'</a>' ) );
					
				include_once( MDJM_PAGES_DIR . '/settings-packages-main.php' );
			} // mdjm_packages_page
						
	 		/*
			 * mdjm_dashboard_page
			 * The MDJM Dashboard admin page
			 */			
			public function mdjm_dashboard_page()	{
				include_once( MDJM_PAGES_DIR . '/dash.php' );
			} // mdjm_dashboard_page
			
			/*
			 * mdjm_settings_page
			 * The MDJM Settings page
			 */			
			public function mdjm_settings_page()	{
				if( !current_user_can( 'manage_options' ) )
			        wp_die( __( 'MDJM: This page requires Administrative priviledges.', 'mobile-dj-manager' ) );
				
				include_once( MDJM_PLUGIN_DIR . '/admin/settings/class-mdjm-settings-page.php' );
			} // mdjm_settings_page
						
			/*
			 * The MDJM Playlists page
			 *
			 *
			 *
			 */
			public function mdjm_playlists_page()	{
				include_once( MDJM_PAGES_DIR . '/playlists.php' );
			} // mdjm_playlists_page
			
			/*
			 * The MDJM About page displays plugin information and is generally called following an update
			 *
			 *
			 *
			 */
			public function mdjm_about_page()	{
				include_once( MDJM_PLUGIN_DIR . '/admin/includes/pages/mdjm-about.php' );
			} // mdjm_about_page
		} // class
	}