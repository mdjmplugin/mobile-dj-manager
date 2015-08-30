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
				add_action( 'admin_menu', array( &$this, 'mdjm_menu_init' ) ); // Admin menu
				add_action( 'admin_bar_menu', array( &$this, 'mdjm_toolbar' ), 99 ); // Admin bar menu
				add_action( 'jetpack_admin_menu', array( &$this, 'remove_jetpack' ) ); // Remove Jetpack for non-Admins
			} // __construct
			
			/*
			 * mdjm_menu_init
			 * Initialise the MDJM Admin menu
			 * 
			 *
			 */
	 		public function mdjm_menu_init()	{
				$this->mdjm_menu(); // The menu structure
				$this->menu_for_admin(); // Remove menu items for DJ's & Clients				
			} // mdjm_menu_init
			
			/*
			 * mdjm_menu
			 * Build the MDJM Admin menu
			 * 
			 *
			 */
	 		public function mdjm_menu()	{
				global $mdjm, $mdjm_settings_page;
				/* -- Build out the menu structure -- */
				add_menu_page( __( 'Mobile DJ Manager' ),
													  __( 'DJ Manager' ), 
													  'manage_mdjm',
													  'mdjm-dashboard',
													  array( &$this, 'mdjm_dashboard_page' ),
													  plugins_url( 'mobile-dj-manager/admin/images/mdjm-icon-20x20.jpg' ),
													  '58.4' );
				/* -- Dashboard -- */									  
				$mdjm_dashboard_page = add_submenu_page( 'mdjm-dashboard',
														 __( 'Dashboard' ),
														 __( 'Dashboard' ),
														 'manage_mdjm',
														 'mdjm-dashboard',
														 array( &$this, 'mdjm_dashboard_page' ) );
				/* -- Settings Page -- */
				if( current_user_can( 'manage_options' ) ) 
					$mdjm_settings_page = add_submenu_page( 'mdjm-dashboard',
															__( 'Settings' ),
															__( 'Settings' ),
															'manage_mdjm',
															'mdjm-settings',
															array( $this, 'mdjm_settings_page' ) );
				/* -- Contract Templates -- */
				if( current_user_can( 'manage_options' ) )
					$mdjm_contract_template_page = add_submenu_page( 'mdjm-dashboard',
																	 __( 'Contract Templates' ),
																	 __( 'Contract Templates' ),
																	 'manage_mdjm',
																	 'edit.php?post_type=' . MDJM_CONTRACT_POSTS,
																	 '' );
				/* -- Email Templates -- */	
				if( current_user_can( 'manage_options' ) )
					$mdjm_email_template_page = add_submenu_page( 'mdjm-dashboard',
																  __( 'Email Templates' ),
																  __( 'Email Templates' ),
																  'manage_mdjm',
																  'edit.php?post_type=' . MDJM_EMAIL_POSTS,
																  '' );
				/* -- Automated Tasks -- */	
				if( current_user_can( 'manage_options' ) )
					$mdjm_auto_tasks_page = add_submenu_page( 'mdjm-dashboard',
															  __( 'Automated Tasks' ),
															  __( 'Automated Tasks' ),
															  'manage_mdjm',
															  'mdjm-tasks',
															  array( &$this, 'mdjm_auto_tasks_page' ) );
				/* -- Clients -- */
				$mdjm_clients_page = add_submenu_page( 'mdjm-dashboard',
													   __( 'Clients' ),
													   __( 'Clients' ),
													   'manage_mdjm',
													   'mdjm-clients',
													    array( &$this, 'mdjm_clients_page' ) );
				/* -- Communications Page -- */
				$mdjm_comms_page = add_submenu_page( 'mdjm-dashboard',
													 __( 'Communications' ),
													 __( 'Communications' ),
													 'manage_mdjm',
													 'mdjm-comms',
													 array( &$this, 'mdjm_comms_page' ) );
				/* -- Communication History -- */
				if( current_user_can( 'manage_options' ) ) 
					$mdjm_comms_history_page = add_submenu_page( 'mdjm-dashboard',
																 __( 'Communication History' ),
																 __( 'Communication History' ),
																 'manage_mdjm',
																 'edit.php?post_type=' . MDJM_COMM_POSTS,
																 '' );
				/* -- Contact Forms -- */
				if( current_user_can( 'manage_options' ) )
					$mdjm_contact_forms_page = add_submenu_page( 'mdjm-dashboard',
																 __( 'Contact Forms' ),
																 __( 'Contact Forms' ),
																 'manage_mdjm',
																 'mdjm-contact-forms',
																 array( &$this, 'mdjm_contact_forms_page' ) );
				/* -- DJ availability -- */
				$mdjm_availability_page = add_submenu_page( 'mdjm-dashboard',
															__( ' ' . MDJM_DJ . ' Availability' ),
															__( ' ' . MDJM_DJ . ' Availability' ),
															'manage_mdjm',
															'mdjm-availability',
															array( &$this, 'mdjm_dj_availability_page' ) );
				/* -- DJ's -- */
				if( MDJM_MULTI == true ) 
					$mdjm_dj_page = add_submenu_page( 'mdjm-dashboard',
													   __(  ' ' . MDJM_DJ . ' \'s' ),
													   __(  ' ' . MDJM_DJ . ' \'s' ),
													   'manage_mdjm',
													   'mdjm-djs',
													    array( &$this, 'mdjm_djs_page' ) );
				/* -- Equipment Packages & Add-ons -- */
				if( current_user_can( 'manage_options' ) && MDJM_PACKAGES == true )
					$mdjm_packages_page = add_submenu_page( 'mdjm-dashboard',
														    __( 'Equipment Packages' ),
														    __( 'Equipment Packages' ),
														    'manage_mdjm',
														    'mdjm-packages',
														    array( &$this, 'mdjm_packages_page' ) );
				/* -- Events -- */
				$mdjm_events_page = add_submenu_page( 'mdjm-dashboard',
													   __( 'Events' ),
													   __( 'Events' ),
													   'manage_mdjm',
													   'edit.php?post_type=' . MDJM_EVENT_POSTS,
													   '' );
													   
				/* -- Music Library -- */
				/*if( current_user_can( 'administrator' ) || dj_can( 'upload_music' ) )
					$mdjm_music_page = add_submenu_page( 'mdjm-dashboard',
														   __( 'Music Library' ),
														   __( 'Music Library' ),
														   'manage_mdjm',
														   'mdjm-music',
															array( &$this, 'mdjm_music_page' ) );*/
													   
				/* -- Transactions -- */
				if( current_user_can( 'manage_options' ) && MDJM_PAYMENTS == true )
					$mdjm_transactions_page = add_submenu_page( 'mdjm-dashboard',
																__( 'Transactions' ),
																__( 'Transactions' ),
																'manage_mdjm',
																'edit.php?post_type=' . MDJM_TRANS_POSTS,
																'' );
				/* -- Venues -- */
				if( current_user_can( 'manage_options' ) || dj_can( 'add_venue' ) )
					$mdjm_venues_page = add_submenu_page( 'mdjm-dashboard',
														  __( 'Venues' ),
														  __( 'Venues' ),
														  'manage_mdjm',
														  'edit.php?post_type=' . MDJM_VENUE_POSTS,
														  '' );
				if( current_user_can( 'manage_options' ) && !$mdjm->_mdjm_validation( 'check' ) )
					add_submenu_page( 'mdjm-dashboard',
									  __( 'Licensing' ),
									  '<span style="color:#F90">' . __( 'Buy License' ) . '</span>',
									  'manage_mdjm',
									  'mdjm-license',
									  'mdjm_purchase' );
				
				/* -- This is for the playlist, does not display on menu -- */					  
				add_submenu_page( 
					  null,
					__( 'Playlists' ),
					__( 'Playlists' ),
					'manage_mdjm',
					'mdjm-playlists',
					array( &$this, 'mdjm_playlists_page' )
				);
				/* -- This is for the updated page, does not display on menu -- */					  
				add_submenu_page( 
					  null,
					__( 'Updated' ),
					__( 'Updated' ),
					'manage_mdjm',
					'mdjm-updated',
					array( &$this, 'mdjm_updated_page' )
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
					'title'	 => __( 'Mobile DJ Manager' ),
					'href'	  => admin_url( 'admin.php?page=mdjm-dashboard' ),
					'meta'	  => array(
						'title' => __( 'Mobile DJ Manager' ),            
					),
				) );
				/* -- Dashboard -- */
				$admin_bar->add_menu( array(
					'id'		=> 'mdjm-dashboard',
					'parent'	=> 'mdjm',
					'title'	 => __( 'Dashboard' ),
					'href'	  => admin_url( 'admin.php?page=mdjm-dashboard' ),
					'meta'	  => array(
						'title' => __( 'MDJM Dashboard' ),
					),
				) );
				/* -- Settings -- */
				if( current_user_can( 'manage_options' ) )	{
					$admin_bar->add_menu( array(
						'id'		=> 'mdjm-settings',
						'parent'	=> 'mdjm',
						'title'	 => __( 'Settings' ),
						'href'	  => admin_url( 'admin.php?page=mdjm-settings' ),
						'meta'	  => array(
							'title' => __( 'MDJM Settings' ),
						),
					) );
					$admin_bar->add_menu( array(
						'id'		=> 'mdjm-settings-general',
						'parent'	=> 'mdjm-settings',
						'title'	 => __( 'General' ),
						'href'	  => admin_url( 'admin.php?page=mdjm-settings&tab=general' ),
						'meta'	  => array(
							'title' => __( 'MDJM General Settings' ),
						),
					) );
					$admin_bar->add_menu( array(
						'id'		=> 'mdjm-settings-events',
						'parent'	=> 'mdjm-settings',
						'title'	 => __( 'Events' ),
						'href'	  => admin_url( 'admin.php?page=mdjm-settings&tab=events' ),
						'meta'	  => array(
							'title' => __( 'MDJM Event Settings' ),
						),
					) );
					$admin_bar->add_menu( array(
						'id'		=> 'mdjm-settings-permissions',
						'parent'	=> 'mdjm-settings',
						'title'	 => __( 'Permissions' ),
						'href'	  => admin_url( 'admin.php?page=mdjm-settings&tab=general&section=mdjm_app_permissions' ),
						'meta'	  => array(
							'title' => __( 'MDJM Permission Settings' ),
						),
					) );
					$admin_bar->add_menu( array(
						'id'		=> 'mdjm-settings-emails',
						'parent'	=> 'mdjm-settings',
						'title'	 => sprintf( __( 'Email %s Template Settings', 'mobile-dj-manager' ), '&amp;' ),
						'href'	  => admin_url( 'admin.php?page=mdjm-settings&tab=emails' ),
						'meta'	  => array(
							'title' => sprintf( __( 'MDJM Email %s Settings', 'mobile-dj-manager' ), '&amp;' ),
						),
					) );
					$admin_bar->add_menu( array(
						'id'		=> 'mdjm-settings-client-zone',
						'parent'	=> 'mdjm-settings',
						'title'	 => __( MDJM_APP . ' Settings' ),
						'href'	  => admin_url( 'admin.php?page=mdjm-settings&tab=client-zone' ),
						'meta'	  => array(
							'title' => __( 'MDJM ' . MDJM_APP . ' Settings' ),
						),
					) );
					$admin_bar->add_menu( array(
						'id'		=> 'mdjm-settings-payments',
						'parent'	=> 'mdjm-settings',
						'title'	 => __( 'Payment Settings' ),
						'href'	  => admin_url( 'admin.php?page=mdjm-settings&tab=payments' ),
						'meta'	  => array(
							'title' => __( 'MDJM Payment Settings' ),
						),
					) );
				/* -- Automated Tasks -- */
					$admin_bar->add_menu( array(
						'id'		=> 'mdjm-tasks',
						'parent'	=> 'mdjm',
						'title'	 => __( 'Automated Tasks' ),
						'href'	  => admin_url( 'admin.php?page=mdjm-tasks' ),
						'meta'	  => array(
							'title' => __( 'Automated Tasks' ),
						),
					) );
				}
				/* -- DJ Availability -- */
				$admin_bar->add_menu( array(
					'id'		=> 'mdjm-availability',
					'parent'	=> 'mdjm',
					'title'	 => __(  ' ' . MDJM_DJ . ' Availability' ),
					'href'	  => admin_url( 'admin.php?page=mdjm-availability' ),
					'meta'	  => array(
						'title' => __(  ' ' . MDJM_DJ . ' Availability' ),
					),
				) );
				/* -- Clients -- */
				$admin_bar->add_menu( array(
					'id'		=> 'mdjm-clients',
					'parent'	=> 'mdjm',
					'title'	 => __( 'Clients' ),
					'href'	  => admin_url( 'admin.php?page=mdjm-clients' ),
					'meta'	  => array(
						'title' => __( 'Clients' ),
					),
				) );
				if( current_user_can( 'manage_options' ) || dj_can( 'add_client' ) )	{
					$admin_bar->add_menu( array(
						'id'		=> 'mdjm-add-client',
						'parent'	=> 'mdjm-clients',
						'title'	 => __( 'Add Client' ),
						'href'	  => admin_url( 'user-new.php' ),
						'meta'	  => array(
							'title' => __( 'Add New Client' ),
						),
					)) ;
				}
				/* -- Communications -- */
				$admin_bar->add_menu( array(
					'id'		=> 'mdjm-comms',
					'parent'	=> 'mdjm',
					'title'	 => __( 'Communications' ),
					'href'	  => admin_url( 'admin.php?page=mdjm-comms' ),
					'meta'	  => array(
						'title' => __( 'Communications' ),
					),
				) );
				if( current_user_can( 'manage_options' ) )	{
					$admin_bar->add_menu( array(
						'id'		=> 'edit.php?post_type=' . MDJM_COMM_POSTS,
						'parent'	=> 'mdjm-comms',
						'title'	 => __( 'Communication History' ),
						'href'	  => admin_url( 'edit.php?post_type=' . MDJM_COMM_POSTS ),
						'meta'	  => array(
							'title' => __( 'Communication History' ),
						),
					) );
				}
				/* -- Contact Forms -- */
				if( current_user_can( 'manage_options' ) )	{
					$admin_bar->add_menu( array(
						'id'		=> 'mdjm-contact-forms',
						'parent' 	=> 'mdjm',
						'title' 	 => __( 'Contact Forms' ),
						'href'  	  => admin_url( 'admin.php?page=mdjm-contact-forms' ),
						'meta'  	  => array(
							'title' => __( 'Contact Forms' ),
						),
					) );
					$admin_bar->add_menu( array(
						'id'		=> 'mdjm-new-contact-form',
						'parent'	=> 'mdjm-contact-forms',
						'title'	 => __( 'New Contact Form' ),
						'href'	  => admin_url( 'admin.php?page=mdjm-contact-forms&action=show_add_contact_form' ),
						'meta'	  => array(
							'title' => __( 'New Contact Form' ),
						),
					) );
					/* -- Contract Templates -- */
					$admin_bar->add_menu( array(
						'id'		=> 'mdjm-contracts',
						'parent'	=> 'mdjm',
						'title'	 => __( 'Contract Templates' ),
						'href'	  => admin_url( 'edit.php?post_type=' . MDJM_CONTRACT_POSTS ),
						'meta'	  => array(
							'title' => __( 'Contract Templates' ),
						),
					) );
					$admin_bar->add_menu( array(
						'id'		=> 'mdjm-new-contract',
						'parent'	=> 'mdjm-contracts',
						'title'	 => __( 'Add Contract Template' ),
						'href'	  => admin_url( 'post-new.php?post_type=' . MDJM_CONTRACT_POSTS ),
						'meta'	  => array(
							'title' => __( 'New Contract Template' ),
						),
					) );
					/* -- DJ List -- */
					if( MDJM_MULTI == true )	{
						$admin_bar->add_menu( array(
							'id'		=> 'mdjm-djs',
							'parent'	=> 'mdjm',
							'title'	 => __(  ' ' . MDJM_DJ . ' List' ),
							'href'	  => admin_url( 'admin.php?page=mdjm-djs' ),
							'meta'	  => array(
								'title' => __(  ' ' . MDJM_DJ . ' List' ),
							),
						) );
					}
					/* -- Email Templates -- */
					$admin_bar->add_menu( array(
						'id'		=> 'mdjm-email-templates',
						'parent'	=> 'mdjm',
						'title'	 => __( 'Email Templates' ),
						'href'	  => admin_url( 'edit.php?post_type=' . MDJM_EMAIL_POSTS ),
						'meta'	  => array(
							'title' => __( 'Email Templates' ),
						),
					) );
					$admin_bar->add_menu( array(
						'id'		=> 'mdjm-new-email-template',
						'parent'	=> 'mdjm-email-templates',
						'title'	 => __( 'Add Template' ),
						'href'	  => admin_url( 'post-new.php?post_type=' . MDJM_EMAIL_POSTS ),
						'meta'	  => array(
							'title' => __( 'New Email Template' ),
						),
					) );
					/* -- Equipment Packages & Add-ons -- */
					if( MDJM_PACKAGES == true )	{

						$admin_bar->add_menu( array(
							'id'		=> 'mdjm-equipment',
							'parent'	=> 'mdjm',
							'title'	 => __( 'Equipment &amp; Packages' ),
							'href'	  => admin_url( 'admin.php?page=mdjm-packages' ),
							'meta'	  => array(
								'title' => __( 'Equipment &amp; Packages' ),
							),
						) );
					}
				}
				/* -- Events -- */
				$admin_bar->add_menu( array(
					'id'    	=> 'mdjm-events',
					'parent' 	=> 'mdjm',
					'title' 	 => __( 'Events' ),
					'href'  	  => admin_url( 'edit.php?post_type=' . MDJM_EVENT_POSTS ),
					'meta'  	  => array(
						'title' => __( 'MDJM Events' ),
					),
				) );
				
				/* -- Music Library -- */
				/*if( current_user_can( 'administrator' ) || dj_can( 'upload_music' ) )	{
					$admin_bar->add_menu( array(
						'id'    	=> 'mdjm-music',
						'parent' 	=> 'mdjm',
						'title' 	 => __( 'Music Library' ),
						'href'  	  => admin_url( 'admin.php?page=mdjm-music' ),
						'meta'  	  => array(
							'title' => __( 'Music Library' ),
						),
					) );
				}*/
				
				if( current_user_can( 'manage_options' ) || dj_can( 'add_event' ) )	{
					$admin_bar->add_menu( array(
						'id'     => 'mdjm-add-events',
						'parent' => 'mdjm-events',
						'title'  => 'Create Event',
						'href'   => admin_url( 'post-new.php?post_type=' . MDJM_EVENT_POSTS ),
						'meta'   => array(
							'title' => __( 'Create New Event' ),
						),
					) );
				}
				/* -- Enquiries -- */
				if( current_user_can( 'manage_options' ) || dj_can( 'view_enquiry' ) )	{
					$event_status = array( 'mdjm-unattended' => 'Unattended Enquiries', 'mdjm-enquiry' => 'View Enquiries' );
					foreach( $event_status as $current_status => $display )	{
						$status_count = $mdjm->mdjm_events->mdjm_count_event_status( $current_status );
						if( !$status_count )
							continue;
							
						$admin_bar->add_menu( array(
							'id'     => 'mdjm-' . str_replace( ' ', '-', strtolower( $display ) ),
							'parent' => 'mdjm-events',
							'title'  => __( $display . ' (' . $status_count . ')' ),
							'href'   => admin_url( 'edit.php?post_status=' . $current_status . ' &post_type=' . MDJM_EVENT_POSTS ),
							'meta'   => array(
								'title' => __( $display ),
							),
						) );
					}
				}
				if( current_user_can( 'manage_options' ) )	{
					/* -- Event Types -- */
					$admin_bar->add_menu( array(
						'id'     => 'mdjm-event-types',
						'parent' => 'mdjm-events',
						'title'  => __( 'Event Types' ),
						'href'   => admin_url( 'edit-tags.php?taxonomy=event-types&post_type=' . MDJM_EVENT_POSTS ),
						'meta'   => array(
							'title' => __( 'View / Edit Event Types' ),
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
				/* -- Transactions -- */
				if( current_user_can( 'manage_options' ) && MDJM_PAYMENTS == true )	{
					$admin_bar->add_menu( array(
						'id'     => 'mdjm-transactions',
						'parent' => 'mdjm',
						'title'  => __( 'Transactions' ),
						'href'   => 'edit.php?post_type=' . MDJM_TRANS_POSTS,
						'meta'   => array(
							'title' => __( 'MDJM Transactions' ),
						),
					) );
					$admin_bar->add_menu( array(
						'id'     => 'mdjm-add-transaction',
						'parent' => 'mdjm-transactions',
						'title'  => __( 'Add Transaction' ),
						'href'   => admin_url( 'post-new.php?post_type=' . MDJM_TRANS_POSTS ),
						'meta'   => array(
							'title' => __( 'Add Transaction' ),
						),
					) );
					/* -- Transaction Types -- */
					$admin_bar->add_menu( array(
						'id'     => 'mdjm-transaction-types',
						'parent' => 'mdjm-transactions',
						'title'  => __( 'Transaction Types' ),
						'href'   => admin_url( 'edit-tags.php?taxonomy=transaction-types&post_type' . MDJM_TRANS_POSTS ),
						'meta'   => array(
							'title' => __( 'View / Edit Transaction Types' ),
						),
					) );
				}
				/* -- Venues -- */
				if( current_user_can( 'manage_options' ) || dj_can( 'add_venue' ) )	{
					$admin_bar->add_menu( array(
						'id'     => 'mdjm-venues',
						'parent' => 'mdjm',
						'title'  => __( 'Venues' ),
						'href'   => admin_url( 'edit.php?post_type=' . MDJM_VENUE_POSTS ),
						'meta'   => array(
							'title' => __( 'Venues' ),
						),
					) );
					$admin_bar->add_menu( array(
						'id'     => 'mdjm-add-venue',
						'parent' => 'mdjm-venues',
						'title'  => __( 'Add Venue' ),
						'href'   => admin_url( 'post-new.php?post_type=' . MDJM_VENUE_POSTS ),
						'meta'   => array(
							'title' => __( 'Add New Venue' ),
						),
					) );
					$admin_bar->add_menu( array(
						'id'     => 'mdjm-venue-details',
						'parent' => 'mdjm-venues',
						'title'  => __( 'Venue Details' ),
						'href'   => admin_url( 'edit-tags.php?taxonomy=venue-details&post_type=' . MDJM_VENUE_POSTS ),
						'meta'   => array(
							'title' => __( 'View / Edit Venue Details' ),
						),
					) );
				}
				/* -- My DJ Planner Links -- */
				$admin_bar->add_menu( array(
					'id'     => 'mdjm-user-guides',
					'parent' => 'mdjm',
					'title'  => '<span style="color:#F90">' . __( 'User Guides' ) . '</span>',
					'href'   => 'http://www.mydjplanner.co.uk/support/user-guides/',
					'meta'   => array(
						'title' => __( 'MDJM User Guides' ),
						'target' => '_blank'
					),
				));
				$admin_bar->add_menu( array(
					'id'     => 'mdjm-support',
					'parent' => 'mdjm',
					'title'  => '<span style="color:#F90">' . __( 'Support' ) . '</span>',
					'href'   => 'http://www.mydjplanner.co.uk/support/',
					'meta'   => array(
						'title' => __( 'MDJM Support Forums' ),
						'target' => '_blank'
					),
				));
				if( !$mdjm->_mdjm_validation( 'check' ) && current_user_can( 'manage_options' ) )	{
					$admin_bar->add_menu( array(
						'id'     => 'mdjm-purchase',
						'parent' => 'mdjm',
						'title'  => '<span style="color:#F90">' . __( 'Buy License' ) . '</span>',
						'href'   => 'http://www.mydjplanner.co.uk/shop/mobile-dj-manager-for-wordpress-plugin/',
						'meta'   => array(
							'title' => __( 'Buy Mobile Dj Manager License' ),
							'target' => '_blank'
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
				if( !current_user_can( 'manage_options' ) )
			        wp_die( __( 'MDJM: This page requires Administrative priviledges.' ) );
				
				include_once( MDJM_PAGES_DIR . '/settings-scheduler.php' );
			} // mdjm_auto_tasks_page
			/*
			 * mdjm_clients_page
			 * The MDJM Client list
			 */
			public function mdjm_clients_page()	{
				if( !current_user_can( 'manage_options' ) && !current_user_can( 'manage_mdjm' ) )
					wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
				
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
			 * mdjm_contact_forms_page
			 * The MDJM Contact Forms page
			 */			
			public function mdjm_contact_forms_page()	{
				if( !current_user_can( 'manage_options' ) )
			        wp_die( __( 'MDJM: This page requires Administrative priviledges.' ) );
				
				include_once( MDJM_PLUGIN_DIR . '/admin/includes/class/class-mdjm-contactforms.php' );
			} // mdjm_contact_forms_page
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
					wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
				
				include_once( MDJM_PAGES_DIR . '/djs.php' );	
			} // mdjm_djs_page
			/*
			 * mdjm_packages_page
			 * The MDJM DJ Availability page
			 */			
			public function mdjm_packages_page()	{
				if( !current_user_can( 'manage_options' ) )
					wp_die( __( 'MDJM: This page requires Administrative priviledges.' ) );
					
				if( !MDJM_PACKAGES )
					wp_die( __( 'MDJM: Equipment Packages & Add-ons are not enabled. You can enable them <a href="' . mdjm_get_admin_page( 'settings', 'echo' ) . '"></a>' ) );
					
				include_once( MDJM_PAGES_DIR . '/settings-packages-main.php' );
			} // mdjm_packages_page
			
			/*
			 * mdjm_music_page
			 * The MDJM Music Library admin page
			 */			
			public function mdjm_music_page()	{
				include_once( MDJM_PLUGIN_DIR . '/admin/includes/class/class-mdjm-music-library.php' );
			} // mdjm_dashboard_page
			
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
			        wp_die( __( 'MDJM: This page requires Administrative priviledges.' ) );
				
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
			 * The MDJM Updated page
			 *
			 *
			 *
			 */
			public function mdjm_updated_page()	{
				include_once( MDJM_PAGES_DIR . '/updated.php' );
			} // mdjm_playlists_page
		} // class
	}