<?php
/*
 * class-mdjm.php
 * 10/03/2015
 * @since 1.1.2
 * The main MDJM class
 */
	
	/* -- Build the MDJM class -- */
	if( !class_exists( 'MDJM' ) )	{
		class MDJM	{
			
			/* -- Publicise the Events class so we can use it throughout -- */
			public $mdjm_events;			
		 /*
		  * __construct
		  * 
		  *
		  *
		  */
			public function __construct()	{
				global $wpdb, $pagenow, $mdjm_post_types, $mdjm_posts, $clientzone;
				
				define( 'MDJM_COMM_POSTS', 'mdjm_communication' );
				define( 'MDJM_CONTACT_FORM_POSTS', 'mdjm-contact-form' );
				define( 'MDJM_CONTACT_FIELD_POSTS', 'mdjm-contact-field' );
				define( 'MDJM_CONTRACT_POSTS', 'contract' );
				define( 'MDJM_SIGNED_CONTRACT_POSTS', 'mdjm-signed-contract' );
				define( 'MDJM_EMAIL_POSTS', 'email_template' );
				define( 'MDJM_EVENT_POSTS', 'mdjm-event' );
				define( 'MDJM_TRANS_POSTS', 'mdjm-transaction' );
				define( 'MDJM_VENUE_POSTS', 'mdjm-venue' );
				
				$this->db_version = '2.6';
								
				$mdjm_post_types = array(
							MDJM_COMM_POSTS,
							MDJM_CONTACT_FORM_POSTS,
							MDJM_CONTACT_FIELD_POSTS,
							MDJM_CONTRACT_POSTS,
							MDJM_SIGNED_CONTRACT_POSTS,
							MDJM_EMAIL_POSTS,
							MDJM_EVENT_POSTS,
							MDJM_TRANS_POSTS,
							MDJM_VENUE_POSTS,
							);
				require_once( MDJM_FUNCTIONS ); // Call the main functions file
				
				/* -- Debug Class -- */
				include_once( 'class-mdjm-debug.php' );
					
				/* -- Initiate events class -- */
				if( !class_exists( 'MDJM_Events' ) )	{
					require( MDJM_PLUGIN_DIR . '/admin/includes/class/class-mdjm-events.php' );
					$this->mdjm_events = new MDJM_Events();
				}
				
				/* -- Menu Class -- */
				require_once( sprintf( "%s/admin/includes/class/class-mdjm-menu.php", MDJM_PLUGIN_DIR ) );
				$mdjm_menu = new MDJM_Menu();
				
				/* -- Hooks -- */
				add_action( 'init', array( &$this, 'mdjm_init' ) ); // // init processes
				add_action( 'admin_init', array( &$this, 'mdjm_admin_init' ) ); // Admin init processes
				add_action( 'admin_enqueue_scripts', array( &$this, 'admin_enqueue' ) ); // Admin styles & scripts
				
				add_action( 'in_admin_footer', array( &$this, 'admin_footer' ) ); // The admin footer text
				add_action( 'wp_loaded', array( &$this, 'api_listener' ) ); // The API Listener
				add_action( 'wp_login', array( &$this, 'last_login' ), 10, 2 ); // Login timestamp
				add_action( 'admin_notices', array( &$this, 'notices' ) ); // Hook into the admin notices hook
				add_action( 'plugins_loaded', array( &$this, 'all_plugins_loaded' ) ); // Hooks to run when plugins are loaded
				
				/* -- Custom Client Fields -- */
				if( $pagenow == 'user-new.php' || $pagenow == 'user-edit.php' || $pagenow == 'profile.php' )	{
					add_action( 'show_user_profile', array( &$this, 'profile_custom_fields' ) ); // User profile screen
					add_action( 'edit_user_profile', array( &$this, 'profile_custom_fields' ) ); // Edit user screen
					add_action( 'user_new_form', array( &$this, 'profile_custom_fields' ) ); // // New user screen
					
					add_action( 'user_register', array( &$this, 'save_custom_user_fields' ), 10, 1 );
					add_action ( 'personal_options_update', array( &$this, 'save_custom_user_fields' ) );
					add_action ( 'edit_user_profile_update', array( &$this, 'save_custom_user_fields' ) );
				}
				
				/* -- Post Class -- */
				require_once( sprintf( "%s/admin/includes/class/class-mdjm-posts.php", MDJM_PLUGIN_DIR ) );
				$mdjm_posts = new MDJM_Posts();
				
				/* -- Cron Class -- */
				require_once( sprintf( "%s/admin/includes/class/class-mdjm-cron.php", MDJM_PLUGIN_DIR ) );
				$mdjm_cron = new MDJM_Cron();
				add_action( 'wp', array( $mdjm_cron, 'activate' ) ); // Activate the MDJM Scheduler hook
				add_action( 'mdjm_hourly_schedule', array( $mdjm_cron, 'execute_cron' ) ); // Run the MDJM scheduler
				add_action( 'mdjm_synchronise', array( $mdjm_cron, 'synchronise' ) ); // Run the validation
				
				/* -- Remove 5 updates after 1.2 -- */
				$upgrade_date = get_option( 'mdjm_date_to_1_2' );
				if(!empty( $upgrade_date ) && time() < $upgrade_date )
					add_action( 'mdjm_import_journal_entries', array( $mdjm_cron, 'import_journal' ) ); // Run the journal importer
								
				/* -- Widgets -- */
				//require_once( sprintf( "%s/admin/includes/class/class-mdjm-widgets.php", MDJM_PLUGIN_DIR ) );
				//$mdjm_widgets = new MDJM_Widgets();
				add_action( 'widgets_init', array( &$this, 'register_widgets' ) ); // Register the MDJM Widgets
				
				$this->plugin_status = get_option( '__mydj_validation' );
								
				/* -- Client Zone Class -- */
				if( !is_admin() )	{
					require_once( sprintf( "%s/class/class-clientzone.php", MDJM_CLIENTZONE ) );
					$clientzone = new ClientZone();
				}
				
			} // __construct
/*
 * --
 * PROCEDURES
 * --
 */		
			/*
			 * mdjm_activate()
			 * 18/03/2015
			 * @since 1.1.3
			 * Procedures taken upon plugin activation
			 */
			/*public function mdjm_activate()	{
				global $mdjm;
				
				if( !get_option( 'm_d_j_m_installed' ) )	{
					error_log( '** THE MDJM INSTALLATION PROCEDURE IS STARTING **' . "\r\n", 3, MDJM_DEBUG_LOG );
				
					include( WPMDJM_PLUGIN_DIR . '/admin/includes/procedures/mdjm-install.php' );
											
					error_log( '** THE MDJM INSTALLATION PROCEDURE COMPLETED **' . "\r\n", 3, MDJM_DEBUG_LOG );
											
				} // if( !get_option( 'm_d_j_m_installation' ) )
			} // mdjm_activate*/
			  
			/*
			* mdjm_deactivate()
			* 18/03/2015
			* @since 1.1.3
			* Procedures taken upon plugin deactivation
			*/
			public function mdjm_deactivate()	{
				wp_clear_scheduled_hook( 'mdjm_hourly_schedule' );	
				wp_clear_scheduled_hook( 'mdjm_synchronise' );			
			} // mdjm_deactivate
			
			/*
			 * Determine if we need to run any plugin upgrade procedures
			 * @called by all_plugins_loaded hook
			 *
			 */
			function mdjm_upgrade_check( $current_version )	{
				add_option( 'mdjm_update_me', MDJM_VERSION_NUM );
				// Run old update procedure first if needed to ensure we do not miss key historic updates
				if( MDJM_VERSION_NUM < '1.2.3' && function_exists( 'f_mdjm_upgrade' ) )	{
					$GLOBALS['mdjm_debug']->log_it( 'Running old style update procedure due to version ' . $current_version, true );
					f_mdjm_upgrade();
				}
				if( $current_version < MDJM_VERSION_NUM )	{
					// Instantiate the update class which will execute the updates
					include_once( MDJM_PROCEDURES_DIR . '/mdjm-upgrade.php' );
					
					// Update the stored version
					update_option( MDJM_VERSION_KEY, MDJM_VERSION_NUM );
					
					// Update the updated key so we know to redirect
					update_option( MDJM_UPDATED_KEY, '1' );
				}
			} // mdjm_upgrade_check
			
			/*
			 * Force display of release notes
			 *
			 *
			 *
			 */
			function release_notes()	{
				if( !is_admin() && is_user_logged_in() )
					return;
				
				// Reset the key telling us an update occured
				update_option( 'mdjm_updated', '0' );
				
				// Redirect to the release notes
				wp_redirect( mdjm_get_admin_page( 'updated' ) . '&updated=1' );
				exit;	
			}
									
			/*
			 * Validate this instance
			 *
			 *
			 * @return		bool|arr		false if invalid, otherwise array with details
			 */
			public function _mdjm_validation( $action ='' )	{
				global $mdjm;
				
				$action = !empty( $action ) ? $action : 'set';
				
				$status = get_option( '__mydj_validation' );
				
				if( empty( $status ) )	{
					/* -- No option, no license -- */
					if( MDJM_DEBUG == true )
						$mdjm->debug_logger( 'LICENSE ERROR: No license option found', true );
					return false;
				}
				
				if( !empty( $status['expire'] ) && time() >= strtotime( $status['expire'] ) )	{
					/* -- Trial period or license expired -- */
					if( MDJM_DEBUG == true )
						$mdjm->debug_logger( 'LICENSE ERROR: Expired: ' . $status['type'] . ' ' . $status['expire'], true );
					return false;
				}
				
				/* -- Return the license state -- */
				return ( !empty( $status['expire'] ) && time() <= strtotime( $status['expire'] ) ? $status : false );
				
				/* -- Catch all and fail -- */
				if( MDJM_DEBUG == true )
					$mdjm->debug_logger( 'LICENSE ERROR: Should not reach this stage in ' . __METHOD__, true );				
				return false;
			} // _mdjm_validation

/*
 * --
 * INIT HOOK
 * --
 */
			/*
			 * mdjm_init
			 * functions called from the init hook
			 * 
			 *
			 */
	 		public function mdjm_init()	{
				/* -- Release notes check -- */
				if( get_option( 'mdjm_updated' ) == 1 )	{
					$GLOBALS['mdjm_debug']->log_it( '*** Redirect to release notes ***' );
					$this->release_notes();
				}
				/* -- Obtain the plugin settings -- */
				$this->mdjm_settings();
				
				/* -- Playlist upload scheduled task -- */
				$this->set_playlist_task();
				/* -- Remove admin toolbar for clients -- */
				$this->no_admin_for_clients();
				
			} // mdjm_init
	
/*
 * --
 * ADMIN_INIT HOOK
 * --
 */
	 		/*
			 * mdjm_admin_init
			 * functions called from the admin_init hook
			 * 
			 *
			 */
	 		public function mdjm_admin_init()	{
				/* -- Register the settings -- */
				if( get_option( MDJM_SETTINGS_KEY ) )	{
					$this->mdjm_init_settings();
					$this->mdjm_role_caps();
				}
			} // mdjm_admin_init
			
/*
 *
 * PLUGINS LOADED HOOK
 *
 */
			/*
			 * Methods to run during plugins loaded hook
			 *
			 *
			 *
			 */
			public function all_plugins_loaded()	{
			/* -- Check version against DB -- */
				$current_version = get_option( MDJM_VERSION_KEY );
				if( $current_version < MDJM_VERSION_NUM ) // Update needed
					$this->mdjm_upgrade_check( $current_version );

			/* -- Database update -- */
				if( MDJM_DB_VERSION != $this->db_version )	{ // DB Update needed
					require_once( MDJM_PROCEDURES_DIR . '/mdjm-db.php' );
					$mdjm_db = new MDJM_DB;
					$mdjm_db->update_db();
				}

				//$this->supported_version(); Closed in 1.2.3
				/* -- Validation -- */
				$this->mdjm_validate();
				
				// Check log files
				if( is_admin() )
					$GLOBALS['mdjm_debug']->log_file_check();
						
			} // all_plugins_loaded
/*
 * --
 * ADMIN_NOTICES HOOK
 * --
 */
	 		/*
			 * Functions called from the admin_notices hook
			 * 
			 * 
			 *
			 */
	 		public function notices()	{
				/* -- Unattended Events -- */
				$this->unattended_events_notice();
				
				/* -- Licensing -- */
				$this->license_warning();
			} // notices
	
/*
 * --
 * SETTINGS
 * --
 */
	 		/*
			 * mdjm_init_settings
			 * Register the plugin settings
			 * 
			 *
			 */
	 		public function mdjm_init_settings()	{
				if( !class_exists( 'MDJM_Settings' ) )
					require_once( MDJM_PLUGIN_DIR . '/admin/settings/class-mdjm-settings.php' );
					
				$this->settings = new MDJM_Settings();
			} // mdjm_init_settings
			
			/*
			 * mdjm_settings
			 * 18/03/2015
			 * @since 1.1.3
			 * Define the settings
			 */
			 public function mdjm_settings()	{
				global $mdjm_settings;
				$mdjm_settings = array(
								'main'		=> get_option( MDJM_SETTINGS_KEY ),
								'email'	   => get_option( MDJM_EMAIL_SETTINGS_KEY ),
								'templates'   => get_option( MDJM_TEMPLATES_SETTINGS_KEY ),
								'events'	  => get_option( MDJM_EVENT_SETTINGS_KEY ),
								'playlist'	=> get_option( MDJM_PLAYLIST_SETTINGS_KEY ),
								'custom_text' => get_option( MDJM_CUSTOM_TEXT_KEY ),
								'clientzone'  => get_option( MDJM_CLIENTZONE_SETTINGS_KEY ),
								'availability'=> get_option( MDJM_AVAILABILITY_SETTINGS_KEY ),
								'pages'	   => get_option( MDJM_PAGES_KEY ),
								'payments'	=> get_option( MDJM_PAYMENTS_KEY ),
								'paypal'	  => get_option( MDJM_PAYPAL_KEY ),
								'permissions' => get_option( MDJM_PERMISSIONS_KEY ),
								'uninst'	  => get_option( MDJM_UNINST_SETTINGS_KEY ),
								);
				define( 'MDJM_DJ', isset( $mdjm_settings['events']['artist'] ) ? $mdjm_settings['events']['artist'] : 'DJ' );				
				define( 'MDJM_JOURNAL', ( !empty( $mdjm_settings['events']['journaling'] ) ? true : false ) );
				define( 'MDJM_CREDITS', ( !empty( $mdjm_settings['main']['show_credits'] ) ? true : false ) );
				define( 'MDJM_TRACK_EMAILS', ( !empty( $mdjm_settings['email']['track_client_emails'] ) ? true : false ) );
				define( 'MDJM_MULTI', ( !empty( $mdjm_settings['events']['employer'] ) ? true : false ) );
				define( 'MDJM_PACKAGES', ( !empty( $mdjm_settings['events']['enable_packages'] ) ? true : false ) );
				define( 'MDJM_TIME_FORMAT', ( isset( $mdjm_settings['main']['time_format'] ) ? $mdjm_settings['main']['time_format'] : 'H:i' ) );
				define( 'MDJM_SHORTDATE_FORMAT', isset( $mdjm_settings['main']['short_date_format'] ) ? $mdjm_settings['main']['short_date_format'] : 'd/m/Y' );
				define( 'MDJM_EVENT_PREFIX', isset( $mdjm_settings['events']['event_prefix'] ) ? $mdjm_settings['events']['event_prefix'] : '' );
				define( 'MDJM_PLAYLIST_CLOSE', isset( $mdjm_settings['playlist']['close'] ) ? $mdjm_settings['playlist']['close'] : '0' );
				define( 'MDJM_CURRENCY', isset( $mdjm_settings['payments']['currency'] ) ? mdjm_set_currency( $mdjm_settings['payments']['currency'] ) : mdjm_set_currency( 'GBP' ) );
				define( 'MDJM_PAYMENTS', ( !empty( $mdjm_settings['paypal']['enable_paypal'] ) ? true : false ) );
				define( 'MDJM_DEPOSIT_LABEL', isset( $mdjm_settings['payments']['deposit_label'] ) ? $mdjm_settings['payments']['deposit_label'] : 'Deposit' );
				define( 'MDJM_BALANCE_LABEL', isset( $mdjm_settings['payments']['balance_label'] ) ? $mdjm_settings['payments']['balance_label'] : 'Balance' );
				define( 'MDJM_COMPANY', isset( $mdjm_settings['main']['company_name'] ) ? $mdjm_settings['main']['company_name'] : '' );
				define( 'MDJM_APP', isset( $mdjm_settings['clientzone']['app_name'] ) ? $mdjm_settings['clientzone']['app_name'] : '' );
				define( 'MDJM_HOME', isset( $mdjm_settings['pages']['app_home_page'] ) ? $mdjm_settings['pages']['app_home_page'] : '' );
				define( 'MDJM_CONTACT_PAGE', isset( $mdjm_settings['pages']['contact_page'] ) ? $mdjm_settings['pages']['contact_page'] : '' );
				define( 'MDJM_CONTRACT_PAGE', isset( $mdjm_settings['pages']['contracts_page'] ) ? $mdjm_settings['pages']['contracts_page'] : '' );
				define( 'MDJM_PLAYLIST_PAGE', isset( $mdjm_settings['pages']['playlist_page'] ) ? $mdjm_settings['pages']['playlist_page'] : '' );
				define( 'MDJM_PROFILE_PAGE', isset( $mdjm_settings['pages']['profile_page'] ) ? $mdjm_settings['pages']['profile_page'] : '' );
				define( 'MDJM_PAYMENT_PAGE', isset( $mdjm_settings['pages']['payments_page'] ) ? $mdjm_settings['pages']['payments_page'] : '' );
				define( 'MDJM_CUSTOM_TEXT', isset( $mdjm_settings['custom_text']['custom_client_text'] ) ? $mdjm_settings['custom_text']['custom_client_text'] : false );
			 } // mdjm_settings
			 
			 /*
			  * Grab the upload playlist setting from the options table
			  * & ensure the cron task is set correctly
			  *
			  *
			  */
			public function set_playlist_task()	{
				global $mdjm_settings;
				
				$mdjm_schedules = get_option( MDJM_SCHEDULES_KEY );
				 
				$current_setting = isset( $mdjm_schedules['upload-playlists']['active'] ) ? $mdjm_schedules['upload-playlists']['active'] : 'N';
				$required_setting = !empty( $mdjm_settings['playlist']['upload_playlists'] ) ? 'Y' : 'N';
				 
				/* -- Determine if an update is needed -- */
				if( empty( $current_setting ) || $current_setting != $required_setting )	{
						
					 $mdjm_schedules['upload-playlists']['active'] = $required_setting;
					
					/* -- Set next run time -- */
					if( $mdjm_schedules['upload-playlists']['active'] == 'Y' )
						$mdjm_schedules['upload-playlists']['nextrun'] = time();
						
					else
						$mdjm_schedules['upload-playlists']['nextrun'] = 'N/A';
				}				 
				update_option( MDJM_SCHEDULES_KEY, $mdjm_schedules );
				 
			} // set_playlist_task
			
			/**
			 * Add capabilities to user roles for custom post types
			 *
			 *
			 * @since 1.1.3
			 */
			public function mdjm_role_caps()	{
				global $mdjm_settings, $mdjm_post_types;
			
				/* -- Add the MDJM User roles -- */
				add_role( 'inactive_client', 'Inactive Client', array( 'read' => true ) );
				add_role( 'client', 'Client', array( 'read' => true ) );
				add_role( 'inactive_dj', 'Inactive DJ', array( 	'read' => true, 
														'create_users' => false,
														'edit_users' => false,
														'delete_users' => false,
														'edit_posts' => false,
														'delete_posts' => false,
														'publish_posts' => false,
														'upload_files' => true,
													) );
				add_role( 'dj', 'DJ', array( 'read' => true, 
										'create_users' => true,
										'edit_users' => true,
										'delete_users' => true,
										'edit_posts' => false,
										'delete_posts' => false,
										'publish_posts' => false,
										'upload_files' => true,
										) );
				
				$roles = array( 'client', 'dj', 'editor', 'administrator' );
				
				/* -- Loop through roles assigning capabilities -- */
				foreach( $roles as $the_role )	{ 
					
					$role = get_role( $the_role );
					
					$role->add_cap( 'read' );
					
				/* -- Admin Only (May be deprecated>) -- */
					if( $the_role == 'administrator' )
						$role->add_cap( 'manage_mdjm' );
						
				/* -- DJ Permissions -- */
					if( $the_role == 'dj' )	{
						$perms = get_option( MDJM_PERMISSIONS_KEY );
						$dj_role = get_role( 'dj' );
						$caps = array(
									'create_users',
									'edit_users',
									'delete_users',
									);
						if( current_user_can( 'dj' ) && current_user_can( 'create_users' ) )	{
							if( !isset( $perms['dj_add_client'] ) || $perms['dj_add_client'] != 'Y' )	{
								foreach( $caps as $cap )	{
									$dj_role->remove_cap( $cap );
								}
							}
						}
						if( current_user_can( 'dj' ) && !current_user_can( 'create_users' ) )	{
							if( isset( $perms['dj_add_client'] ) && $perms['dj_add_client'] == 'Y' )	{
								foreach( $caps as $cap )	{
									$dj_role->add_cap( $cap );
								}
							}
						}	
					} // if( $the_role == 'dj' )
					
				/* -- MDJM_EVENT_POSTS -- */
					$role->add_cap( 'read_mdjm_manage_event' );
					$role->add_cap( 'read_private_mdjm_manage_events' );
					$role->add_cap( 'edit_mdjm_manage_event' );
					$role->add_cap( 'edit_mdjm_manage_events' );
					$role->add_cap( 'edit_others_mdjm_manage_events' );
					$role->add_cap( 'edit_published_mdjm_manage_events' );
					if( current_user_can( 'manage_options' ) || dj_can( 'add_event' ) )	{
						$role->add_cap( 'publish_mdjm_manage_events' );
					}
					else	{
						$role->remove_cap( 'publish_mdjm_manage_events' );
					}
					if( current_user_can( 'manage_options' ) )	{
						$role->add_cap( 'delete_mdjm_manage_events' );
						$role->add_cap( 'delete_others_mdjm_manage_events' );
						$role->add_cap( 'delete_private_mdjm_manage_events' );
						$role->add_cap( 'delete_published_mdjm_manage_events' );
					}
				
				/* -- MDJM_SIGNED_CONTRACT_POSTS -- */
					$role->add_cap( 'read_mdjm_signed_contract' );
					$role->add_cap( 'publish_mdjm_signed_contracts' );
					$role->add_cap( 'edit_mdjm_signed_contract' );
					$role->add_cap( 'edit_mdjm_signed_contracts' );
					if( current_user_can( 'manage_options' ) )
						$role->add_cap( 'read_private_mdjm_signed_contracts' );
				
				/* -- MDJM_TRANS_POSTS -- */
					$role->add_cap( 'read_mdjm_manage_transaction' );
					
					if( current_user_can( 'manage_options' ) )	{
						$role->add_cap( 'read_private_mdjm_manage_transactions' );
						$role->add_cap( 'edit_mdjm_manage_transaction' );
						$role->add_cap( 'edit_mdjm_manage_transactions' );
						$role->add_cap( 'edit_others_mdjm_manage_transactions' );
						$role->add_cap( 'edit_published_mdjm_manage_transactions' );
						$role->add_cap( 'delete_mdjm_manage_transactions' );
						$role->add_cap( 'delete_others_mdjm_manage_transactions' );
						$role->add_cap( 'delete_private_mdjm_manage_transactions' );
						$role->add_cap( 'delete_published_mdjm_manage_transactions' );
						$role->add_cap( 'publish_mdjm_manage_transactions' );
					}
						
				/* -- MDJM_VENUE_POSTS -- */
					$role->add_cap( 'read_mdjm_manage_venue' );
					
					if( current_user_can( 'manage_options' ) || dj_can( 'add_venue' ) )	{
						$role->add_cap( 'read_private_mdjm_manage_venues' );
						$role->add_cap( 'edit_mdjm_manage_venue' );
						$role->add_cap( 'edit_mdjm_manage_venues' );
						$role->add_cap( 'edit_others_mdjm_manage_venues' );
						$role->add_cap( 'edit_published_mdjm_manage_venues' );
						$role->add_cap( 'publish_mdjm_manage_venues' );
					}
					else	{
						$role->remove_cap( 'read_private_mdjm_manage_venues' );
						$role->remove_cap( 'edit_mdjm_manage_venue' );
						$role->remove_cap( 'edit_mdjm_manage_venues' );
						$role->remove_cap( 'edit_others_mdjm_manage_venues' );
						$role->remove_cap( 'edit_published_mdjm_manage_venues' );
						$role->remove_cap( 'publish_mdjm_manage_venues' );
					}
					
					if( current_user_can( 'manage_options' ) )	{
						$role->add_cap( 'delete_mdjm_manage_venues' );
						$role->add_cap( 'delete_others_mdjm_manage_venues' );
						$role->add_cap( 'delete_private_mdjm_manage_venues' );
						$role->add_cap( 'delete_published_mdjm_manage_venues' );
					}
				}
			} // mdjm_role_caps
			
			/**
			 * Register the login action within the user meta
			 * 
			 *
			 * @since 1.1.3
			 */
			public function last_login( $user_login, $user ) {
				update_user_meta( $user->ID, 'last_login', date( 'Y-m-d H:i:s' ) );
			} // last_login
			
			/**
			 * Remove admin bar & do not allow admin UI for Clients
			 * Redirect to Client Zone
			 *
			 * @since 1.1.3
			 */
			public function no_admin_for_clients() {
				
				if( current_user_can( 'client' ) || current_user_can( 'inactive_client' ) )	{
					add_filter( 'show_admin_bar', '__return_false' );
					
					if( is_admin() )	{
						if( !defined( 'DOING_AJAX' ) || !DOING_AJAX )	{
							wp_redirect( $this->get_link( MDJM_HOME, false, false ) );
							exit;	
						}
					}
				}				
			} // no_admin_for_clients
			
/*
 * --
 * VALIDATION
 * --
 */
			public function mdjm_validate()	{
				$this->_mdjm_validation();
			} // mdjm_validate
			
			/*
			 * Display notice if the plugin version is EOS or nearing EOS
			 * 
			 *
			 *
			 */
			function supported_version()	{
				if( MDJM_VERSION_NUM < MDJM_UNSUPPORTED )	{
					mdjm_update_notice( 'error',
										'Your version of the Mobile DJ Manager plugin is no longer unsupported.<br />' . 
										'Upgrades are no longer be possible. You will need to remove the plugin and re-install the latest version' . 
										'from scratch' );
					
					if( MDJM_DEBUG == true )
						$GLOBALS['mdjm_debug']->log_it( 'ERROR: Plugin version (' . MDJM_VERSION_NUM . ') is EOS', true );
				
				}
				if( MDJM_VERSION_NUM <= MDJM_UNSUPPORTED_ALERT )	{
					mdjm_update_notice( 'error',
										'Your version of the Mobile DJ Manager plugin is approaching <strong>end of support</strong>.<br />' . 
										'Soon, upgrades will not be possible and you will need to remove the plugin and re-install the latest version ' . 
										'from scratch.<br />' . 
										'Functionality may also be affected. To avoid any unnecessary issues, <a href="' . 
										admin_url( 'plugins.php' ) . '">upgrade to the latest version now</a>' );
					
					if( MDJM_DEBUG == true )
						$GLOBALS['mdjm_debug']->log_it( 'ERROR: Plugin version (' . MDJM_VERSION_NUM . ') is nearing EOS', true );	
				}
				
			} // supported_version
/*
 * --
 * ADMIN NOTICES
 * --
 */
			/*
			 * Display an alert if there are outstanding unattended events
			 * and the settings dictate to do so
			 * 
			 *
			 */
			public function unattended_events_notice()	{
				global $mdjm_settings;
				
				if( current_user_can( 'administrator' ) && !empty( $mdjm_settings['events']['warn_unattended'] ) )	{
					$unattended = $this->mdjm_events->mdjm_count_event_status( 'mdjm-unattended' );
					
					if( !empty( $unattended ) && $unattended > 0 )
						mdjm_update_notice( 'update-nag', 
											'There are currently ' . $unattended . ' <a href="' . mdjm_get_admin_page( 'events', 'str' ) . 
											'&post_status=mdjm-unattended">Unattended ' . _n( 'Enquiry', 'Enquiries', $unattended ) . 
											'</a> that require your attention. ' . 
											'<a href="' . mdjm_get_admin_page( 'events', 'str' ) . '&post_status=mdjm-unattended">' . 
											'Click here to review and action ' . _n( 'this enquiry', 'these enquiries', $unattended ) . ' now</a>'
											 );
				}
			} // unattended_events_notice
			
			/*
			 * Invalid license notice
			 *
			 *
			 *
			 */
			public function license_warning()	{
				if( !$this->_mdjm_validation() )
					mdjm_update_notice( 'error',
										'Your Mobile DJ Manager license has expired. ' . 
										'Please visit <a href="' . mdjm_get_admin_page( 'mydjplanner', 'str' ) . 
										'" target="_blank">http://www.mydjplanner.co.uk</a> to renew.<br />' . 
										'Functionality will be restricted until your license is renewed' );
			} // license_warning
/*
 * --
 * WIDGETS
 * --
 */
 			/*
			 * Register the MDJM Widgets within WordPress
			 *
			 *
			 *
			 */
			public function register_widgets()	{				
				include( WPMDJM_PLUGIN_DIR . '/widgets/class-mdjm-widget.php' );
				register_widget( 'MDJM_Availability_Widget' );
				register_widget( 'MDJM_ContactForms_Widget' );
			} // register_widgets
/*
 * --
 * STYLES & SCRIPTS
 * --
 */
	 		/*
			 * admin_enqueue
			 * Register & enqueue the scripts & styles we want to use
			 * Only register those scripts we want on all pages
			 * Or those we can control
			 * Others should be called from the pages themselves
			 */
	 		public function admin_enqueue()	{
				global $mdjm_post_types;
				
				/* -- Admin style sheet -- */
				wp_register_style( 'mdjm-admin', WPMDJM_PLUGIN_URL . '/admin/includes/css/mdjm-admin.css' );
				wp_enqueue_style( 'mdjm-admin' );
				
				/* -- Dynamics Ajax -- */
				wp_register_script( 'mdjm-dynamics', WPMDJM_PLUGIN_URL . '/client-zone/includes/js/mdjm-dynamic.js', array( 'jquery' ) );
				
				/* -- Music Library Script -- */
				wp_register_script( 'mdjm-music-library', WPMDJM_PLUGIN_URL . '/admin/includes/js/mdjm-music-library.js', array( 'jquery' ) );
				
				/* -- YouTube Suscribe Script -- */
				// Needs to be enqueued as and when required
				wp_register_script( 'youtube-subscribe', 'https://apis.google.com/js/platform.js' );
				
				if( in_array( get_post_type(), $mdjm_post_types ) )	{
					/* -- mdjm-posts.css: The CSS script for all custom post pages -- */
					wp_register_style( 'mdjm-posts', WPMDJM_PLUGIN_URL . '/admin/includes/css/mdjm-posts.css' );
					wp_enqueue_style( 'mdjm-posts' );
					
					/* -- jQuery -- */
					wp_enqueue_script( 'jquery' );
					
					/* -- jQuery Validation -- */
					wp_register_script( 'jquery-validation-plugin', 'http://ajax.aspnetcdn.com/ajax/jquery.validate/1.11.1/jquery.validate.min.js', false );
					wp_enqueue_script( 'jquery-validation-plugin' );
								
				/* -- Contract Templates Only -- */
					if( get_post_type() == MDJM_CONTRACT_POSTS )	{
						wp_register_script( 'mdjm-contract-val', WPMDJM_PLUGIN_URL . '/admin/includes/js/mdjm-contract-post-val.js', array( 'jquery-validation-plugin' ) );
						wp_enqueue_script( 'mdjm-contract-val' );
					}
				
				/* -- Email Templates Only -- */
					if( get_post_type() == MDJM_EMAIL_POSTS )	{
						wp_register_script( 'mdjm-email-val', WPMDJM_PLUGIN_URL . '/admin/includes/js/mdjm-email-post-val.js', array( 'jquery-validation-plugin' ) );
						wp_enqueue_script( 'mdjm-email-val' );
					}
				
				/* -- Event Posts Only -- */
					if( get_post_type() == MDJM_EVENT_POSTS )	{
						wp_register_script( 'mdjm-event-js', WPMDJM_PLUGIN_URL . '/admin/includes/js/mdjm-event-post-val.js', array( 'jquery-validation-plugin' ) );
						wp_enqueue_script( 'mdjm-event-js' );
						wp_localize_script( 'mdjm-event-js', 'event_type', array( 'ajax_url' => admin_url( 'admin-ajax.php' ) ));
						wp_localize_script( 'mdjm-event-js', 'mdjmeventcost', array( 'ajax_url' => admin_url( 'admin-ajax.php' ) ) );
						wp_enqueue_script( 'mdjm-dynamics' );
						wp_localize_script( 'mdjm-dynamics', 'mdjmaddons', array( 'ajax_url' => admin_url( 'admin-ajax.php' ) ) );
					}
					
				/* -- Transaction Posts Only -- */
					if( get_post_type() == MDJM_TRANS_POSTS )	{
						wp_register_script( 'mdjm-trans-js', WPMDJM_PLUGIN_URL . '/admin/includes/js/mdjm-trans-post-val.js', array( 'jquery-validation-plugin' ) );
						wp_enqueue_script( 'mdjm-trans-js' );
						wp_localize_script( 'mdjm-trans-js', 'transaction_type', array( 'ajax_url' => admin_url( 'admin-ajax.php' ) ));
					}
				
				/* -- Venue Posts Only -- */
					if( get_post_type() == MDJM_VENUE_POSTS )	{
						wp_register_script( 'mdjm-venue-val', WPMDJM_PLUGIN_URL . '/admin/includes/js/mdjm-venue-post-val.js', array( 'jquery-validation-plugin' ) );
						wp_enqueue_script( 'mdjm-venue-val' );
					}
				}
				
				/* -- Contact Forms -- */
				wp_register_script( 'mdjm-colour-picker', WPMDJM_PLUGIN_URL . '/admin/includes/js/mdjm-colour-picker.js', 'jquery', '20150304', true );
				if( isset( $_GET['page'] ) && $_GET['page'] == 'mdjm-contact-forms' )	{
					/* -- Colour picker -- */
					wp_enqueue_style( 'wp-color-picker' );
					wp_enqueue_script( 'wp-color-picker' );	
					wp_enqueue_script( 'mdjm-colour-picker' );
					
					/* -- Validation -- */
					wp_register_script( 'jquery-validation-plugin', 'http://ajax.aspnetcdn.com/ajax/jquery.validate/1.11.1/jquery.validate.min.js', false );
					wp_enqueue_script( 'jquery-validation-plugin' );
					
					wp_register_script( 'mdjm-contact-form-val', WPMDJM_PLUGIN_URL . '/admin/includes/js/mdjm-contact-form-val.js', array( 'jquery-validation-plugin' ) );
					wp_enqueue_script( 'mdjm-contact-form-val' );
				}
			} // admin_enqueue
			
			/*
			 * admin_footer
			 * Displays the specified text in the WP Admin UI footer
			 * 
			 * 
			 * 
			 */
			function admin_footer() {
				global $post, $mdjm_post_types;
				
				$str = $_SERVER['QUERY_STRING'];
				$search = 'mdjm';
				$pos = strpos( $str, $search );
				
				if( $pos !== false || ( in_array( get_post_type(), $mdjm_post_types ) ) )	{
					echo '<p align="center" class="description">Powered by <a style="color:#F90" href="' . mdjm_get_admin_page( 'mydjplanner', 'str' ) . 
					'" target="_blank">' . MDJM_NAME . '</a>, version ' . MDJM_VERSION_NUM . '</p>' . "\r\n";
				}
			} // admin_footer

			/*
			 * Add the filters for the TinyMCE plugin
			 *
			 *
			 *
			 */
			public function mce_shortcode_button() {
				// Check user permissions
				if ( !current_user_can( 'edit_posts' ) && !current_user_can( 'edit_pages' ) && !current_user_can( 'dj' ) )
					return;
				
				// Check if WYSIWYG is enabled
				if ( 'true' == get_user_option( 'rich_editing' ) ) {
					add_filter( 'mce_external_plugins', array( &$this, 'add_mce_shortcode' ) );
					add_filter( 'mce_buttons', array( &$this, 'register_mce_buttons' ) );
				}
			}
			
			/*
			 * Declare the script that inserts ths MDJM Shortcodes into the content
			 * when the MDJM Shortcode button is used
			 *
			 *
			 */
			public function add_mce_shortcode( $plugin_array ) {
				
				$plugin_array['mdjm_shortcodes_btn'] = WPMDJM_PLUGIN_URL . '/admin/includes/js/mdjm-tinymce-shortcodes.js';
				return $plugin_array;
			}
			
			/*
			 * Register the MDJM Shortcode button within the TinyMCE interface
			 * 
			 *
			 *
			 */
			public function register_mce_buttons( $buttons ) {
				array_push( $buttons, 'mdjm_shortcodes_btn' );
				return $buttons;
			}
			
/*
 * --
 * API LISTENER
 * --
 */
			/**
			 * Initiate the MDJM API Listener
			 * Used for PayPal IPN and email tracking
			 *
			 * @since 1.1.1
			 */
			public function api_listener()	{
				$listener = isset( $_GET['mdjm-api'] ) ? $_GET['mdjm-api'] : '';
				
				if( empty( $listener ) )
					return;
				
				switch( $listener )	{
					/* -- PayPal IPN API -- */
					case 'MDJM_PAYPAL_GW':
						if( MDJM_DEBUG == true )
							$GLOBALS['mdjm_debug']->log_it( 'PayPal IPN API listener activated', true );
						include( MDJM_PLUGIN_DIR . '/admin/includes/api/mdjm-api-pp-ipn.php' );
					break;
					/* -- MDJM Email Tracking -- */
					case 'MDJM_EMAIL_RCPT':
						if( MDJM_DEBUG == true )
							$GLOBALS['mdjm_debug']->log_it( 'MDJM Email API listener activated', true );
						include( MDJM_PLUGIN_DIR . '/admin/includes/api/mdjm-api-email-rcpt.php' );
					break;
					/* -- Default action -- */
					default:
						$GLOBALS['mdjm_debug']->log_it( 'WARNING: Rogue API request received - ' . $listener, true );
						return;
				} // switch				
			} // api_listener
/*
 * --
 * SCHEDULED TASKS
 * --
 */
			/**
			 * scheduler_activate
			 * Register the scheduled tasks within WP
			 *
			 * @param	
			 * @return
			 * @since	1.1.3
			 * @called wp action hook
			 */
			public function scheduler_activate()	{
				if ( !wp_next_scheduled( 'hook_mdjm_hourly_schedule' ) )	{
					wp_schedule_event( time(), 'hourly', 'hook_mdjm_hourly_schedule' );
				}
			} // scheduler_activate
			
			/**
			 * cron
			 * Initiates the scheduled tasks
			 *
			 * @param	
			 * @return
			 * @since	1.1.3
			 * @called hook_mdjm_hourly_schedule action hook
			 */
			public function cron()	{
				global $mdjm_settings;
				/* Access the cron functions */
				require_once( WPMDJM_PLUGIN_DIR . '/admin/includes/mdjm-cron.php' );
				
				/* Get the scheduled tasks */
				$mdjm_schedules = get_option( MDJM_SCHEDULES_KEY );
				if( isset( $mdjm_settings['playlist']['upload_playlists'] ) )	{
					$mdjm_schedules['upload-playlists']['active'] = $mdjm_settings['playlist']['upload_playlists'];
				}
				
				foreach( $mdjm_schedules as $task )	{
					/* Only execute active tasks */
					if( $task['active'] == 'Y' )	{
						/* Check frequency and whether to run */
						if( !isset( $task['nextrun'] ) || $task['nextrun'] <= time() || $task['nextrun'] == 'Today'
							|| $task['nextrun'] == 'Next Week' || $task['nextrun'] == 'Next Month'
							|| $task['nextrun'] == 'Next Year' )	{
							$func = $task['function'];
							if( function_exists( $func ) )
								$func();
						}
					} // if( $task['active'] == 'Y' )
				} // foreach( $mdjm_schedules as $task )
			} // cron
			
/*
 * --
 * GENERAL
 * --
 */			
			/**
			 * debug_logger
			 * Send the specified message to the debug file
			 *
			 * @param	str		Required: $debug_msg 	The message to log
			 *			bool	Optional: $stampit	true to include timestamp otherwise false
			 * @return
			 * @since	1.1.3
			 * @called From back and front
			 */
			public function debug_logger( $debug_msg='', $stampit=false )	{
				if( empty( $debug_msg ) )
					return;
				
				$debug_log = $stampit == true ? date( 'd/m/Y  H:i:s' ) . ' : ' . $debug_msg : '    ' . $debug_msg;
				
				error_log( $debug_log . "\r\n", 3, MDJM_DEBUG_LOG );	
				
			} // debug_logger
			
			/**
			 * Provide the correct page link dependant on permalink settings
			 * 
			 * 
			 *
			 * @param    int    $page_id    The ID of the destination page
			 * @return   str		 		The URI link to the destination with the first query string (& or ?)
			 * @since    1.1.3
			 */
			public function get_link( $page_id='', $permalink=true, $echo=false )	{
				if( empty( $page_id ) )	{
					if( MDJM_DEBUG == true )
						 $this->debug_logger( 'Missing `page_id` argument in ' . __FUNCTION__, true );
					return false;
				}
				
				$permalink = isset( $permalink ) ? $permalink : true;
				
				$echo = isset( $echo ) ? $echo : false;
				
				if( !empty( $echo ) )
					echo get_permalink( $page_id ) . ( !empty( $permalink ) ? ( get_option( 'permalink_structure' ) ? '?' : '&amp;' ) : '' );
				
				else
					return get_permalink( $page_id ) . ( !empty( $permalink ) ? ( get_option( 'permalink_structure' ) ? '?' : '&amp;' ) : '' );
			} // get_link

/*
 * --
 * CLIENT PROFILES
 * --
 */
			/**
			 * Add the MDJM Custom User Fields to the user profile page
			 * 
			 * 
			 *
			 * @param    int    $user    The ID of the user
			 * @return
			 * @since    1.1.3
			 */
			public function profile_custom_fields( $user )	{
				global $current_screen, $user_ID, $pagenow;
								
				if( $pagenow != 'user-new.php' )
					$user_id = ( $current_screen->id == 'profile' ) ? $user_ID : $_REQUEST['user_id'];
				
				echo '<h3>Mobile DJ Manager Custom User Fields</h3>' . "\r\n" .
				'<table class="form-table">' . "\r\n";
				
				/* -- Get the custom user fields -- */
				$custom_fields = get_option( MDJM_CLIENT_FIELDS );
				
				/* -- Loop through the fields -- */
				foreach( $custom_fields as $custom_field )	{
					if( $pagenow != 'user-new.php' )
						$field_value = get_user_meta( $user_id, $custom_field['id'], true );

					/* -- Display if configured -- */
					if( $custom_field['display'] == true )	{
						echo '<tr>' . "\r\n" . 
						'<th><label for="' . $custom_field['id'] . '">' . $custom_field['label'] . '</label></th>' . "\r\n" . 
						'<td>' . "\r\n";
						
						/* -- Checkbox Field -- */
						if( $custom_field['type'] == 'checkbox' )	{
							echo '<input type="' . $custom_field['type'] . '" name="' . $custom_field['id'] . '" id="' . $custom_field['id'] . '" value="Y" ';
							if( $pagenow != 'user-new.php' )
								checked( $field_value, 'Y' );
							else
								checked ( '', '' );
							echo ' />' . "\r\n";
						}
						/* -- Select List -- */
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
						/* -- Everything else -- */
						else	{
							echo '<input type="' . $custom_field['type'] . '" name="' . $custom_field['id'] . 
							'" id="' . $custom_field['id'] . '" value="' . ( $pagenow != 'user-new.php' ? esc_attr( get_the_author_meta( $custom_field['id'], $user->ID ) ) : '' ) . 
							'" class="regular-text" />' . "\r\n";
						}
						
						/* -- Description if set -- */
						if( $custom_field['desc'] != '' )	{
							echo '<br />' . 
							'<span class="description">' . $custom_field['desc'] . '</span>' . "\r\n";
						}
						/* -- End the table row -- */
						echo '</td>' . "\r\n" . 
						'</tr>' . "\r\n";
					}
				}
				
				echo '</table>' . "\r\n";
				
			} // profile_custom_fields
			
			/**
			 * Save the MDJM Custom User Fields
			 * 
			 * 
			 *
			 * @param    int    $user_id    The ID of the user
			 * @return
			 * @since    1.1.3
			 */
			public function save_custom_user_fields( $user_id )	{
				
				$custom_fields = get_option( MDJM_CLIENT_FIELDS );
				$default_fields = get_user_by( 'id', $user_id );
				
				if( !current_user_can( 'edit_user', $user_id ) )
					return;
				
				/* -- Loop through the fields and update -- */
				foreach( $custom_fields as $custom_field )	{
					$field = $custom_field['id'];
					
					/* -- Checkbox unchecked = N -- */
					if( $custom_field['type'] == 'checkbox' && $_POST[$field] == '' )
						$_POST[$field] = 'N';
					
					/* -- Update the meta field -- */	
					if( !empty( $_POST[$field] ) )
						update_user_meta( $user_id, $field, $_POST[$field] );
					
					/* -- For new users, remove the admin bar 
						  and set the action to created -- */	
					if( $_POST['action'] == 'createuser' )	{
						update_user_option( $user_id, 'show_admin_bar_front', false );
						if( !empty( $default_fields->first_name ) && !empty( $default_fields->last_name ) )	{
							update_user_option( $user_id, 'display_name', $default_fields->first_name . ' ' . $default_fields->last_name );
						}
						$client_action = 'created';	
					}
					else	{
						$client_action = 'updated';	
					}
				}
			} // save_custom_user_fields
/*
 * --
 * EMAILS
 * --
 */
			/**
			 * Prepare and send an email
			 * 
			 * 
			 *
			 * @param    array	$args
			 *						Required: content 	str			post ID or email content as str
			 *						Required: to	 	str|int		email address or user ID of recipient
			 *						Optional: subject	str			required if content is not a post ID
			 *						Optional: from		int			user ID of sender, defaults to 0 (system)
			 *						Optional: journal	str|bool	The journal entry type or false not to log this action, default to 'email-client'
			 *						Optional: event_id	int			event ID
			 *						Optional: html		bool		true sends html (Default) false plain text
			 *						Optional: cc_dj		bool		true sends copy to DJ false does not, only applicable if we have event. Default per settings
			 *						Optional: cc_admin	bool		true sends copy to Admin false does not. Default per settings
			 *						Optional: source	str			what initiated the email - i.e. Event Enquiry (Default)
			 *						Optional: filter	bool		true (Default) filters subject and content for shortcode, false does not
			 *						Optional: log_comm	bool		true (Default) logs the email, false does not
			 *
			 * @return   str	$comm_id	The communication post ID if the email was successfully sent
			 * @since    1.1.3
			 */
			function send_email( $args )	{
				global $mdjm_posts, $mdjm_settings;
				
				if( MDJM_DEBUG == true )
					$this->debug_logger( 'Starting ' . __FUNCTION__, true );
				
				/* -- Error checking -- */
				if( !is_numeric( $args['content'] ) && empty( $args['subject'] ) )	{
					if( MDJM_DEBUG == true )
						$this->debug_logger( '	ERROR: Missing `subject` argument' );
						 
					return false;
				}
				
				$content = !empty( $args['content'] ) ? $args['content'] : '';
				$to = !empty( $args['to'] ) ? $args['to'] : '';
				$subject = !is_numeric( $content ) && !empty( $args['subject'] ) ? $args['subject'] : get_the_title( $content );
				$sender = !empty( $args['from'] ) ? $args['from'] : 1;
				
				if( empty( $to ) )	{
					if( MDJM_DEBUG == true )
						 $this->debug_logger( '	ERROR: Missing `to` argument' );
						 
					return false;	
				}
						
				if( empty( $content ) )	{
					if( MDJM_DEBUG == true )
						 $this->debug_logger( '	ERROR: Missing `content` argument' );
						 
					return false;
				}
				
				$journal = !empty( $args['journal'] ) ? $args['journal'] : 'email-client';
				
				$cc_dj = isset( $args['cc_dj'] ) ? $args['cc_dj'] : 
					( isset( $mdjm_settings['email']['bcc_dj_to_client'] ) ? true : false );
				$cc_admin = isset( $args['cc_admin'] ) ? $args['cc_admin'] : 
					( isset( $mdjm_settings['email']['bcc_admin_to_client'] ) ? true : false );
				
				$filter = isset( $args['filter'] ) ? $args['filter'] : true;
				$log_comm = isset( $args['log_comm'] ) ? $args['log_comm'] : MDJM_TRACK_EMAILS;
				
				/* -- Do we have an event? -- */
				$event = !empty( $args['event_id'] ) && is_numeric( $args['event_id'] ) ? get_post( $args['event_id'] ) : '';
				
				if( !empty( $event ) )	{
					/* -- Get the DJ -- */
					if( $cc_dj == true )	{
						$event_dj = get_post_meta( $event->ID, '_mdjm_event_dj', true );
						$dj = get_user_by( 'ID', $event_dj );
					}
				}
				
				$html = isset( $args['html'] ) ? $args['html'] : true;
				
				/* -- Set the correct values -- */
				$recipient = is_numeric( $to ) ? get_userdata( $to ) : get_user_by( 'email', $to );
					
				if( !$recipient )	{
					if( MDJM_DEBUG == true )
						 $this->debug_logger( '	ERROR: User `' . $to . '` does not exist ' );
					
					return false;
				}
				
				if( $sender != 0 )
					$sender_data = get_userdata( $sender );
				
				$from = isset( $sender_data ) ? $sender_data->display_name . ' <' . $mdjm_settings['email']['system_email'] . '>' : MDJM_COMPANY . ' <' . $mdjm_settings['email']['system_email'] . '>';
				
				/* -- Set the message content -- */
				if( is_numeric( $content ) )	{
					/* -- For a template -- */
					if( !$mdjm_posts->post_exists( $content ) )	{
						if( MDJM_DEBUG == true )
							 $this->debug_logger( '	ERROR: Specified template `' . $content . '` does not exist' );
							 
						return false;	
					}
					$p = get_post( $content );
					$message = $p->post_content;
					$message = apply_filters( 'the_content', $message );
					$message = str_replace( ']]>', ']]&gt;', $message );
					
				}
				else	{
					/* -- Not a template -- */
					$message = $content;
				}
				
				/* -- Additional copies of the email to be sent to -- */
				$copy_dj = ( $cc_dj == true && !empty( $dj ) ? $dj->user_email : false );
				$copy_admin = ( $cc_admin == true ? $mdjm_settings['email']['system_email'] : false );
					
				if( !empty( $copy_dj ) )
					$copy_to[] = $copy_dj;
				if( !empty( $copy_admin ) )
					$copy_to[] = $copy_admin;
					
				/* -- Headers -- */
				if( $html != false )	{
					$headers[] = 'MIME-Version: 1.0' . "\r\n";
					$headers[] = 'Content-type: text/html; charset=UTF-8' . "\r\n";	
				}
				$headers[] = 'From: ' . $from . '>' . "\r\n";
				if( isset( $sender_data ) )
					$headers[] = 'Reply-To: ' . $sender_data->user_email . "\r\n";
					
				$headers[] = 'X-Mailer: ' . MDJM_NAME . ' version ' . MDJM_VERSION_NUM . ' (http://www.mydjplanner.co.uk)'; 
				
				if( $filter == true )	{
					/* -- Filter the content -- */
					$msg = $this->filter_content(
												( !empty( $recipient ) ? $recipient->ID : '' ),
												( !empty( $event ) ? $event->ID : '' ),
												$message
												);
					
					/* -- Filter the subject -- */
					$sub = $this->filter_content(
												( !empty( $recipient ) ? $recipient->ID : '' ),
												( !empty( $event ) ? $event->ID : '' ),
												$subject
												);
				}
				else	{
					$msg = $message;
					$sub = $subject;	
				}
												
				/* -- Add the COMM post -- */
				if( $log_comm == true )	{
					if( !class_exists( 'MDJM_Communication' ) || !$mdjm_comms )	{
						require_once( WPMDJM_PLUGIN_DIR . '/admin/includes/class/class-mdjm-communications.php' );
						$mdjm_comms = new MDJM_Communication();	
					}
					
					remove_action( 'save_post', array( $mdjm_posts, 'save_custom_post' ), 10, 2 );
					$comm_post = $mdjm_comms->insert_comm( array (
											'subject'	=> $sub,
											'content'	=> $msg,
											'recipient'  => !empty( $recipient ) ? $recipient->ID : '',
											'source'	 => !empty( $args['source'] ) ? $args['source'] : 'Event Enquiry',
											'event'	  => !empty( $event ) ? $event->ID : '',
											'author'	 => $sender,
											) );
					add_action( 'save_post', array( $mdjm_posts, 'save_custom_post' ), 10, 2 );
					if( empty( $comm_post ) && MDJM_DEBUG == true )
						$this->debug_logger( 'Could not create Comm post in ' . __FUNCTION__, true );
					
					elseif( MDJM_DEBUG == true )
						$this->debug_logger( 'Comm post created with ID `' . $comm_post . '` ', true );
				}
				else	{
					if( MDJM_DEBUG == true )
						$this->debug_logger( 'Skipping communication logging by command' );	
				}
								
				/* -- Send the email to the client with stat tracking if configured -- */
				if( wp_mail( $recipient->user_email,
							$sub,
							$msg . ( !empty( $comm_post ) ? $mdjm_comms->insert_stat_image( $comm_post ) : '' ),
							$headers 
							) )	{
					
					/* -- Set the status of the email -- */
					if( $log_comm == true )	{
						remove_action( 'save_post', array( $mdjm_posts, 'save_custom_post' ), 10, 2 );
						$mdjm_comms->change_email_status( $comm_post, 'sent' );
						add_action( 'save_post', array( $mdjm_posts, 'save_custom_post' ), 10, 2 );
					}
					
					if( MDJM_DEBUG == true )
						$this->debug_logger( '	-- Message "' . $sub . '" successfully sent to "' . $recipient->display_name . '"' );
						
					/* -- Update Journal -- */
					if( !empty( $journal ) && !empty( $event ) )	{
						$this->mdjm_events->add_journal( array(
										'user' 			=> !empty( $sender_data->ID ) ? $sender_data->ID : '1',
										'event'		   => $event->ID,
										'comment_content' => 'Email sent to Client - ' . $sub . '<br />(' . time() . ')',
										'comment_type' 	=> 'mdjm-journal',
										),
										array(
											'type' 		  => $journal,
											'visibility'	=> '1',
										) );
					}
						
				}
				else	{
					/* -- Set the status of the email -- */
					remove_action( 'save_post', array( $mdjm_posts, 'save_custom_post' ), 10, 2 );
					$mdjm_comms->change_email_status( $comm_post, 'failed' );
					add_action( 'save_post', array( $mdjm_posts, 'save_custom_post' ), 10, 2 );
					if( MDJM_DEBUG == true )
						$this->debug_logger( '	ERROR: Message "' . $sub . '" could not be sent to "' . $recipient->display_name . '"' );
						
					return false;
				}
				
				/* -- And now send the message to anyone else who needs a copy. No tracking here -- */
				if( !empty( $copy_to ) )	{
					/* -- Prefix with status -- */
					$msg_prefix = '<hr size="1" />';
					$msg_prefix .= '<p style="font-size: 11px;">The following message was recently sent to ' . $recipient->display_name . ' via ' . MDJM_APP . '.<br />' . "\r\n";;
					$msg_prefix .= 'You are receiving a copy of this message either because you chose to do so, or the  ' . MDJM_APP . ' application settings dictate that you do so.';
					$msg_prefix .= '<br /></p>' . "\r\n";
					$msg_prefix .= '<hr size="1" />';
					
					foreach( $copy_to as $mdjm_recipient )	{
						if( wp_mail( $mdjm_recipient, $sub, $msg_prefix . $msg, $headers ) )	{
							if( MDJM_DEBUG == true )
								$this->debug_logger( '	-- A copy of the message "' . $sub . '" successfully sent to ' . $mdjm_recipient );
						}
						else	{
							if( MDJM_DEBUG == true )
								$this->debug_logger( '	ERROR: A copy of the message "' . $sub . '" could not be sent to ' . $mdjm_recipient );
						}
					}
				}
				if( MDJM_DEBUG == true )
					$this->debug_logger( 'Completed ' . __FUNCTION__, true );

				return ( isset( $comm_post ) ? $comm_post : true );
			} // send_email
			
/*
 * --
 * SHORTCODES
 * --
 */
			/**
			 * filter_content
			 * Search and replace through the $content
			 * 
			 * 
			 *
			 * @param	int			$client			Optional: the user ID of the client
			 *			int			$event			Optional: the post ID of the event
			 *			str			$content		Required: the content to be filtered
			 *			
			 * @return	str|bool					New string on success | false on fail
			 * @since	1.1.3
			 */
			public function filter_content( $client = '', $event = '', $content = '' )	{
				global $mdjm_settings;
				
				if( MDJM_DEBUG == true )
						 $this->debug_logger( 'Starting ' . __FUNCTION__, true );
				
				if( empty( $content ) )	{
					if( MDJM_DEBUG == true )
						 $this->debug_logger( '	ERROR: No content passed for filtering ' );
					return false;	
				}
				
				/* -- Setup Client Data -- */
				if( !empty( $client ) )	{
					/* -- Retrieve the user info -- */
					$c = !is_array( $client ) ? get_userdata( $client ) : $client;
					
					/* -- Password -- */
					$pass_action = get_user_meta( $c->ID, 'mdjm_pass_action', true );
					
					/* -- Reset -- */
					if( !empty( $pass_action ) )	{
						if( MDJM_DEBUG == true )
							$this->debug_logger( '	-- Password reset for user ' . $c->ID );
						
						$c_pw = $pass_action;
						wp_set_password( $c_pw, $c->ID );
						delete_user_meta( $c->ID, 'mdjm_pass_action' );
					}
					else	{
						$c_pw = 'Please <a href="' . home_url( '/wp-login.php?action=lostpassword' ) . '">click here</a> to reset your password';
					}
					
					/* -- Client Address -- */
					if( !empty( $c->address1 ) )	{
						$c_address[] = $c->address1;
						if( !empty( $c->address2 ) )
							$c_address[] = $c->address2;
						if( !empty( $c->town ) )
							$c_address[] = $c->town;
						if( !empty( $c->county ) )
							$c_address[] = $c->county;
						if( !empty( $c->county ) )
							$c_address[] = $c->postcode;
					}
				}
				/* -- Setup Event, DJ & Venue Data -- */
				if( !empty( $event ) )	{
					/* -- Retrieve the event info -- */
					
					$e = !is_array( $event ) ? get_post( $event ) : $event;
					
					$eventinfo = $this->mdjm_events->event_detail( $e->ID );
					
					$venue_details = $this->mdjm_events->mdjm_get_venue_details( get_post_meta( $e->ID, '_mdjm_event_venue_id', true ), $e->ID );

				}
					
				/* -- Replacements -- */
				$mdjm_filter = array(
				/* -- General -- */
					'{ADMIN_URL}'			=> admin_url(),
					'{APPLICATION_HOME}'	 => $this->get_link( MDJM_HOME, false ),
					'{APPLICATION_NAME}'	 => MDJM_APP,
					'{COMPANY_NAME}'		 => MDJM_COMPANY,
					'{CONTACT_PAGE}'		 => $this->get_link( MDJM_CONTACT_PAGE, false ),
					'{DDMMYYYY}'			 => date( MDJM_SHORTDATE_FORMAT ),
					'{WEBSITE_URL}'		  => home_url(),
					
				/* -- Client -- */
					'{CLIENT_FIRSTNAME}'	 => ( !empty( $c->first_name ) ? $c->first_name : '' ),
					'{CLIENT_LASTNAME}'	  => ( !empty( $c->last_name ) ? $c->last_name : '' ),
					'{CLIENT_FULLNAME}'	  => ( !empty( $c->display_name ) ? $c->display_name : '' ),
					'{CLIENT_FULL_ADDRESS}'  => ( !empty( $c_address ) ? implode( '<br />', $c_address ) : '' ),
					'{CLIENT_EMAIL}'		 => ( !empty( $c->user_email ) ? $c->user_email : '' ),
					'{CLIENT_PRIMARY_PHONE}' => ( !empty( $c->phone1 ) ? $c->phone1 : '' ),
					'{CLIENT_USERNAME}'	  => ( !empty( $c->user_login ) ? $c->user_login : '' ),
					'{CLIENT_PASSWORD}'	  => ( !empty( $c_pw ) ? $c_pw : '' ),
					
				/* -- Event, DJ & Venue -- */
					'{ADMIN_NOTES}'		  => ( !empty( $eventinfo['admin_notes'] ) ? $eventinfo['admin_notes'] : '' ),
						
					'{BALANCE}'			  => ( !empty( $eventinfo['balance'] ) ? $eventinfo['balance'] : '' ),
					
					'{CONTRACT_DATE}'	    => ( !empty( $eventinfo['contract_date'] ) ? $eventinfo['contract_date'] : date( MDJM_SHORTDATE_FORMAT ) ),
					
					'{CONTRACT_ID}'		  => ( !empty( $e ) ? $e->post_title : '' ),
					
					'{CONTRACT_URL}'		 => ( !empty( $e ) ? $this->get_link( MDJM_CONTRACT_PAGE ) . 'event_id=' . $e->ID : '' ),
																		
					'{DEPOSIT}'			  => ( !empty( $eventinfo['deposit'] ) ? display_price( $eventinfo['deposit'] ) : '' ),
												
					'{DEPOSIT_STATUS}'	   => ( !empty( $eventinfo['deposit_status'] ) ? $eventinfo['deposit_status'] : __( 'Due' ) ),
					'{DJ_EMAIL}'		     => ( !empty( $eventinfo['dj']->user_email ) ? $eventinfo['dj']->user_email : __( 'Not Assigned' ) ),
					'{DJ_FIRSTNAME}'	     => ( !empty( $eventinfo['dj']->user_firstname ) ? $eventinfo['dj']->user_firstname : '' ),
					'{DJ_FULLNAME}'		  => ( !empty( $eventinfo['dj']->display_name ) ? $eventinfo['dj']->display_name : '' ),
					'{DJ_NOTES}'		     => ( !empty( $eventinfo['notes'] ) ? $eventinfo['notes'] : '' ),
					'{DJ_PRIMARY_PHONE}'     => ( !empty( $eventinfo['dj']->phone1 ) ? $eventinfo['dj']->phone1 : '' ),
					'{DJ_SETUP_DATE}'	    => ( !empty( $eventinfo['setup_date'] ) && $eventinfo['setup_date'] != 'Not Specified' ? 
						date( MDJM_SHORTDATE_FORMAT, $eventinfo['setup_date'] ) : __( 'Not Specified' ) ),
						
					'{DJ_SETUP_TIME}'	    => ( !empty( $eventinfo['setup_time'] ) ? $eventinfo['setup_time'] : '' ),
					'{END_TIME}'		     => ( !empty( $eventinfo['finish'] ) ? $eventinfo['finish'] : '' ),
					'{EVENT_DATE}'		   => ( !empty( $eventinfo['date'] ) ? date( 'l, jS F Y', $eventinfo['date'] ) : __( 'Not Specified' ) ),
					'{EVENT_DATE_SHORT}'     => ( !empty( $eventinfo['date'] ) ? date( MDJM_SHORTDATE_FORMAT, $eventinfo['date'] ) : __( 'Not Specified' ) ),
					'{EVENT_DESCRIPTION}'    => ( !empty( $eventinfo['notes'] ) ? $eventinfo['notes'] : '' ),
					'{EVENT_NAME}'		   => ( !empty( $eventinfo['name'] ) ? $eventinfo['name'] : '' ),
					'{EVENT_TYPE}'		   => ( !empty( $eventinfo['type'] ) ? $eventinfo['type'] : '' ),
					'{PAYMENT_AMOUNT}'	   => ( isset( $_POST['mc_gross'] ) ? display_price( $_POST['mc_gross'] ) : '' ),
					'{PAYMENT_DATE}'		 => ( isset( $_POST['payment_date'] ) ? date( MDJM_SHORTDATE_FORMAT, strtotime( $_POST['payment_date'] ) ) : '' ),
					'{PAYMENT_FOR}'		  => ( isset( $_POST['custom'] ) ? $_POST['custom'] : '' ),
					'{PAYMENT_URL}'		  => ( !empty( $e ) ? $this->get_link( MDJM_PAYMENT_PAGE ) . 'event_id=' . $e->ID : '' ),
					'{PLAYLIST_CLOSE}'	   => $mdjm_settings['playlist']['close'] != 0 ? $mdjm_settings['playlist']['close'] : 'never',
					'{PLAYLIST_URL}'		 => $this->get_link( MDJM_PLAYLIST_PAGE, false ),
					'{GUEST_PLAYLIST_URL}'   => ( !empty( $eventinfo['guest_playlist'] ) ? $eventinfo['guest_playlist'] : '' ),
					'{START_TIME}'		   => ( !empty( $eventinfo['start'] ) ? $eventinfo['start'] : '' ),
					'{TOTAL_COST}'		   => ( !empty( $eventinfo['cost'] ) ? $eventinfo['cost'] : '' ),
					'{VENUE}'			    => ( !empty( $venue_details['name'] ) ? stripslashes( $venue_details['name'] ) : '' ),
							
					'{VENUE_CONTACT}'	    => ( !empty( $venue_details['venue_contact'] ) ? stripslashes( $venue_details['venue_contact'] ) : '' ),
													
					'{VENUE_DETAILS}'	    => ( !empty( $venue_details['details'] ) ? implode( '<br />', $venue_details['details'] ) : '' ),
													
					'{VENUE_EMAIL}'		  => ( !empty( $venue_details['venue_email'] ) ? stripslashes( $venue_details['venue_email'] ) : '' ),
													
					'{VENUE_FULL_ADDRESS}'   => ( !empty( $venue_details['full_address'] ) ? implode( '<br />', $venue_details['full_address'] ) : '' ),
					
					'{VENUE_NOTES}'		  => ( !empty( $venue_details['venue_information'] ) ? stripslashes( $venue_details['venue_information'] ) : '' ),
													
					'{VENUE_TELEPHONE}'	  => ( !empty( $venue_details['venue_phone'] ) ? stripslashes( $venue_details['venue_phone'] ) : '' ),
					
				/* -- Equipment Packages & Addons -- */
					'{AVAILABLE_PACKAGES}'	=> ( !empty( $eventinfo['dj']->ID ) ? 
						get_available_packages( $eventinfo['dj']->ID, false ) : get_available_packages( '', false ) ),
						
					'{AVAILABLE_PACKAGES_COST}' => ( !empty( $eventinfo['dj']->ID ) ? 
						get_available_packages( $eventinfo['dj']->ID, true ) : get_available_packages( '', true ) ),
						
					'{EVENT_PACKAGE}'		=> ( !empty( $eventinfo['package'] ) ? $eventinfo['package'] : 'N/A' ),
					'{EVENT_PACKAGE_COST}'   => ( !empty( $e ) ? get_event_package( $e->ID, true ) : 'N/A' ),
						
					//'{AVAILABLE_ADDONS}'	 => ( !empty( $eventinfo['dj']->ID ) ? 
						//implode( "\n", get_available_addons( $eventinfo['dj']->ID, '' ) ) : implode( "\n", get_available_addons( '', '' ) ) ),
						
					//'{AVAILABLE_ADDONS_COST}' => ( !empty( $eventinfo['dj']->ID ) ? 
						//implode( "\n", get_available_addons( $eventinfo['dj']->ID, '' ) ) : implode( "\n", get_available_addons( '', '' ) ) ),
					
					'{EVENT_ADDONS}'		 => ( !empty( $eventinfo['addons'] ) ? $eventinfo['addons'] : 'N/A' ),
					'{EVENT_ADDONS_COST}'    => ( !empty( $e ) ? get_event_addons( $e->ID, true ) : 'N/A' ),
					
				);
				
				/* -- Create the Search/Replace Array's -- */							
				foreach( $mdjm_filter as $key => $value )	{
					$search[] = $key;
					$replace[] = $value;	
				}
				
				/* -- Return the filtered data -- */
				if( MDJM_DEBUG == true )
					$this->debug_logger( 'Completed ' . __FUNCTION__, true );
				
				return str_replace( $search, $replace, $content );
								
			} // filter_content
			
			/*
			 * Display a message to the user within the Admin UI
			 *
			 *
			 *
			 */
			public function mdjm_messages( $class='', $msg )	{
				$message = array(
							'1'	=> array( '', ),
							'2'	=> array( '', ),
							'3'	=> array( 'updated', 'Field deleted successfully' ),
							);
				
				echo '<div id="message" class="' . ( empty( $class ) ? $message[$msg][0] : $class ) . '">' . "\r\n" . 
				'<p>' . ( empty( $class ) ? $message[$msg][1] : $msg ) . '</p>' . "\r\n" . 
				'</div>' . "\r\n";
			} // messages
						
		} // MDJM class
	} // if( !class_exists )
	
/* -- Constants -- */
	define( 'MDJM_NAME', 'Mobile DJ Manager for Wordpress');
	define( 'MDJM_VERSION_NUM', '1.2.3.1' );
	define( 'MDJM_UNSUPPORTED', '1.0' );
	define( 'MDJM_UNSUPPORTED_ALERT', '0.9.9.8' );
	define( 'MDJM_REQUIRED_WP_VERSION', '3.9' );
	define( 'MDJM_PLUGIN_NAME', trim( dirname( MDJM_PLUGIN_BASENAME ), '/' ) );
	
	/* -- Files & DIRs -- */
	//define( 'MDJM_PLUGIN_DIR', untrailingslashit( dirname( __FILE__ ) ) );
	define( 'MDJM_PLUGIN_URL', untrailingslashit( plugins_url( '', __FILE__ ) ) );
	define( 'MDJM_PAGES_DIR', MDJM_PLUGIN_DIR . '/admin/pages' );
	define( 'MDJM_PROCEDURES_DIR', MDJM_PLUGIN_DIR . '/admin/includes/procedures' );
	define( 'MDJM_FUNCTIONS', MDJM_PLUGIN_DIR . '/includes/mdjm-functions.php' );
	define( 'MDJM_CLIENTZONE', MDJM_PLUGIN_DIR . '/client-zone' );
	
	/* -- Option Keys -- */
	define( 'MDJM_VERSION_KEY', 'mdjm_version');
	define( 'MDJM_SETTINGS_KEY', 'mdjm_plugin_settings' );
	define( 'MDJM_EMAIL_SETTINGS_KEY', 'mdjm_email_settings' );
	define( 'MDJM_TEMPLATES_SETTINGS_KEY', 'mdjm_templates_settings' );
	define( 'MDJM_EVENT_SETTINGS_KEY', 'mdjm_event_settings' );
	define( 'MDJM_PLAYLIST_SETTINGS_KEY', 'mdjm_playlist_settings' );
	define( 'MDJM_CLIENTZONE_SETTINGS_KEY', 'mdjm_clientzone_settings' );
	define( 'MDJM_CLIENT_FIELDS', 'mdjm_client_fields' );
	define( 'MDJM_CUSTOM_TEXT_KEY', 'mdjm_frontend_text' );
	define( 'MDJM_PAGES_KEY', 'mdjm_plugin_pages' );
	define( 'MDJM_PAYMENTS_KEY', 'mdjm_payment_settings' );
	define( 'MDJM_PAYPAL_KEY', 'mdjm_paypal_settings' );
	define( 'MDJM_PERMISSIONS_KEY', 'mdjm_plugin_permissions' );
	define( 'MDJM_AVAILABILITY_SETTINGS_KEY', 'mdjm_availability_settings' );
	define( 'MDJM_SCHEDULES_KEY', 'mdjm_schedules' );
	define( 'MDJM_UPDATED_KEY', 'mdjm_updated' );
	define( 'MDJM_DEBUG_SETTINGS_KEY', 'mdjm_debug_settings' );
	define( 'MDJM_DB_VERSION_KEY', 'mdjm_db_version' );
	define( 'MDJM_DB_VERSION', get_option( MDJM_DB_VERSION_KEY ) );
	define( 'MDJM_UNINST_SETTINGS_KEY', 'mdjm_uninst' );
	
	/* -- Tables -- */
	define( 'MDJM_EVENTS_TABLE', $wpdb->prefix . 'mdjm_events' );
	define( 'MDJM_PLAYLIST_TABLE', $wpdb->prefix . 'mdjm_playlists' );
	define( 'MDJM_MUSIC_LIBRARY_TABLE', $wpdb->prefix . 'mdjm_music_library' );
	define( 'MDJM_TRANSACTION_TABLE', $wpdb->prefix . 'mdjm_trans' );
	define( 'MDJM_JOURNAL_TABLE', $wpdb->prefix . 'mdjm_journal' );
	define( 'MDJM_HOLIDAY_TABLE', $wpdb->prefix . 'mdjm_avail' );
	
/* -- Insantiate the class & register the activation/deactivation hooks -- */
	if( class_exists( 'MDJM' ) )	{
		/* -- Instantiate the plugin class -- */
		$mdjm = new MDJM();
		/* -- Activate and Deactivate hooks -- */
		//register_activation_hook(__FILE__, array( $mdjm, 'mdjm_activate' ) );
		//register_deactivation_hook(__FILE__, array( $mdjm, 'mdjm_deactivate' ) );
	}
/* -- Additional Functions -- */
	if( is_admin() )	{
		include( MDJM_PLUGIN_DIR . '/admin/includes/core.php' ); // Interact with WP core
		include( MDJM_PLUGIN_DIR . '/admin/includes/process-ajax.php' ); // Ajax functions backend
	}
	include( MDJM_CLIENTZONE . '/includes/mdjm-dynamic.php' ); // Ajax functions, front & backend
?>