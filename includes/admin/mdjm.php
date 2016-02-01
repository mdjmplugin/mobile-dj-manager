<?php
/**
 * Class: MDJM
 * Description: The main MDJM class
 *
 */
	
	/* -- Build the MDJM class -- */
	if( !class_exists( 'MDJM' ) )	{
		class MDJM	{
			// Publicise the Events class so we can use it throughout
			public $mdjm_events;			
			/**
			 * Class constructor
			 * 
			 *
			 *
			 */
			public function __construct()	{
				global $wpdb, $pagenow, $mdjm_post_types, $mdjm_posts, $clientzone;
				
				define( 'MDJM_COMM_POSTS', 'mdjm_communication' );
				define( 'MDJM_CONTRACT_POSTS', 'contract' );
				define( 'MDJM_CUSTOM_FIELD_POSTS', 'mdjm-custom-fields' );
				define( 'MDJM_SIGNED_CONTRACT_POSTS', 'mdjm-signed-contract' );
				define( 'MDJM_EMAIL_POSTS', 'email_template' );
				define( 'MDJM_EVENT_POSTS', 'mdjm-event' );
				define( 'MDJM_QUOTE_POSTS', 'mdjm-quotes' );
				define( 'MDJM_TRANS_POSTS', 'mdjm-transaction' );
				define( 'MDJM_VENUE_POSTS', 'mdjm-venue' );
				
				$this->db_version = '2.6';
				
				/**
				 * This can be removed post 1.3
				 *
				 *
				 *
				 */
				$this->mdjm_events = new MDJM_Events(); // REMOVE
								
				$mdjm_post_types = array(
					MDJM_COMM_POSTS,
					MDJM_CONTRACT_POSTS,
					MDJM_CUSTOM_FIELD_POSTS,
					MDJM_SIGNED_CONTRACT_POSTS,
					MDJM_EMAIL_POSTS,
					MDJM_EVENT_POSTS,
					MDJM_QUOTE_POSTS,
					MDJM_TRANS_POSTS,
					MDJM_VENUE_POSTS );
												
				/* -- Hooks -- */
				add_action( 'init', array( &$this, 'mdjm_init' ) ); // init processes
				add_action( 'admin_init', array( &$this, 'mdjm_admin_init' ) ); // Admin init processes
				add_action( 'admin_enqueue_scripts', array( &$this, 'admin_enqueue' ) ); // Admin styles & scripts
				
				add_action( 'wp_loaded', array( &$this, 'wp_fully_loaded' ) ); // For when WP is loaded
				add_action( 'wp_login', array( &$this, 'last_login' ), 10, 2 ); // Login timestamp
				add_action( 'admin_notices', array( &$this, 'notices' ) ); // Hook into the admin notices hook
				add_action( 'plugins_loaded', array( &$this, 'all_plugins_loaded' ) ); // Hooks to run when plugins are loaded
			} // __construct
			
/*
 * --
 * PROCEDURES
 * --
 */				  
			/*
			 * Determine if we need to run any plugin upgrade procedures
			 * @called by all_plugins_loaded hook
			 *
			 */
			function mdjm_upgrade_check( $current_version )	{
				add_option( 'mdjm_update_me', MDJM_VERSION_NUM );
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
				/* -- Obtain the plugin settings -- */
				$this->mdjm_settings();				
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
					//$this->mdjm_role_caps();
				}
				// Release notes check
				if( get_option( 'mdjm_updated' ) == 1 && is_admin() )	{
					MDJM()->debug->log_it( '*** Redirect to release notes ***' );
					// Reset the key telling us an update occured
					update_option( 'mdjm_updated', '0' );
					
					// Redirect to the release notes after upgrade
					wp_redirect( admin_url( 'admin.php?page=mdjm-about' ) );
					exit;
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
			/* -- Database update -- */
				if( MDJM_DB_VERSION != $this->db_version )	{ // DB Update needed
					require_once( MDJM_PLUGIN_DIR . '/includes/admin/procedures/mdjm-db.php' );
					$mdjm_db = new MDJM_DB;
					$mdjm_db->update_db();
				}								
			} // all_plugins_loaded
			
/*
 *
 * WP LOADED HOOK
 *
 */
			/*
			 * Methods to run during wp loaded hook
			 *
			 *
			 *
			 */
			public function wp_fully_loaded()	{
			/* -- Check version against DB -- */
				$current_version = get_option( MDJM_VERSION_KEY );
				if( $current_version < MDJM_VERSION_NUM ) // Update needed
					$this->mdjm_upgrade_check( $current_version );

				/* -- Initiate the API listener -- */
				$this->api_listener();	
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
					require_once( MDJM_PLUGIN_DIR . '/includes/admin/settings/class-mdjm-settings.php' );
					
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
									'permissions' => get_option( MDJM_PERMISSIONS_KEY ),
									'data' 		=> get_option( MDJM_API_SETTINGS_KEY ),
									'uninst'	  => get_option( MDJM_UNINST_SETTINGS_KEY )
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
				define( 'MDJM_PLAYLIST_ENABLE', !empty( $mdjm_settings['playlist']['enable_playlists'] ) ? true : false );
				define( 'MDJM_PLAYLIST_CLOSE', isset( $mdjm_settings['playlist']['close'] ) ? $mdjm_settings['playlist']['close'] : '0' );
				define( 'MDJM_CURRENCY', isset( $mdjm_settings['payments']['currency'] ) ? mdjm_set_currency( $mdjm_settings['payments']['currency'] ) : mdjm_set_currency( 'GBP' ) );
				define( 'MDJM_PAYMENTS', ( !empty( $mdjm_settings['payments']['payment_gateway'] ) ? true : false ) );
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
				define( 'MDJM_QUOTES_PAGE', isset( $mdjm_settings['pages']['quotes_page'] ) ? $mdjm_settings['pages']['quotes_page'] : '' );
				define( 'MDJM_CUSTOM_TEXT', isset( $mdjm_settings['custom_text']['custom_client_text'] ) ? $mdjm_settings['custom_text']['custom_client_text'] : false );
				define( 'MDJM_ONLINE_QUOTES', ( !empty( $mdjm_settings['templates']['online_enquiry'] ) ? true : false ) );
				define( 'MDJM_NOTIFY_ADMIN', ( !empty( $mdjm_settings['clientzone']['status_notification'] ) ? true : false ) );
			 } // mdjm_settings
			 			
			/**
			 * Register the login action within the user meta
			 * 
			 *
			 * @since 1.1.3
			 */
			public function last_login( $user_login, $user ) {
				update_user_meta( $user->ID, 'last_login', date( 'Y-m-d H:i:s' ) );
			} // last_login

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
					$unattended = MDJM()->events->mdjm_count_event_status( 'mdjm-unattended' );
					
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
				global $mdjm_post_types, $mdjm_settings;
				
				/* -- Admin style sheet -- */
				wp_register_style( 'mdjm-admin', MDJM_PLUGIN_URL . '/assets/css/mdjm-admin.css', '', MDJM_VERSION_NUM );
				wp_enqueue_style( 'mdjm-admin' );
				
				// jQuery Validation
				wp_register_script( 'jquery-validation-plugin', 'https://ajax.aspnetcdn.com/ajax/jquery.validate/1.11.1/jquery.validate.min.js', false );
				
				/* -- Dynamics Ajax -- */
				wp_register_script( 'mdjm-dynamics', MDJM_PLUGIN_URL . '/assets/js/mdjm-dynamic.js', array( 'jquery' ), MDJM_VERSION_NUM );
				
				// Users JS for Ajax
				wp_register_script(
					'mdjm-users-js',
					MDJM_PLUGIN_URL . '/assets/js/mdjm-users.js',
					array( 'jquery' ),
					MDJM_VERSION_NUM
				);
								
				/* -- YouTube Suscribe Script -- */
				// Needs to be enqueued as and when required
				wp_register_script( 'youtube-subscribe', 'https://apis.google.com/js/platform.js' );
				
				// Custom Event Field Ordering
				if( isset( $_GET['section'] ) && $_GET['section'] == 'mdjm_custom_event_fields' )	{
					wp_enqueue_script( 'jquery-ui-sortable' );
					wp_enqueue_script( 'update-order-custom-fields', MDJM_PLUGIN_URL . '/assets/js/mdjm-order-list.js' );	
				}
				
				if( in_array( get_post_type(), $mdjm_post_types ) || ( isset( $_GET['section'] ) && $_GET['section'] == 'mdjm_custom_event_fields' ) )	{
					/* -- mdjm-posts.css: The CSS script for all custom post pages -- */
					wp_register_style( 'mdjm-posts', MDJM_PLUGIN_URL . '/assets/css/mdjm-posts.css', '', MDJM_VERSION_NUM );
					wp_enqueue_style( 'mdjm-posts' );
					
					/* -- jQuery -- */
					wp_enqueue_script( 'jquery' );
					
					/* -- jQuery Validation -- */
					wp_enqueue_script( 'jquery-validation-plugin' );
								
				/* -- Contract Templates Only -- */
					if( get_post_type() == MDJM_CONTRACT_POSTS )	{
						wp_register_script( 'mdjm-contract-val', MDJM_PLUGIN_URL . '/assets/js/mdjm-contract-post-val.js', array( 'jquery-validation-plugin' ), MDJM_VERSION_NUM );
						wp_enqueue_script( 'mdjm-contract-val' );
					}
				
				/* -- Email Templates Only -- */
					if( get_post_type() == MDJM_EMAIL_POSTS )	{
						wp_register_script( 'mdjm-email-val', MDJM_PLUGIN_URL . '/assets/js/mdjm-email-post-val.js', array( 'jquery-validation-plugin' ), MDJM_VERSION_NUM );
						wp_enqueue_script( 'mdjm-email-val' );
					}
				
				/* -- Event Posts Only -- */
					if( get_post_type() == MDJM_EVENT_POSTS )	{
						wp_register_script( 'mdjm-event-js', MDJM_PLUGIN_URL . '/assets/js/mdjm-event-post-val.js', array( 'jquery-validation-plugin' ), MDJM_VERSION_NUM );
						wp_enqueue_script( 'mdjm-event-js' );
						wp_localize_script( 'mdjm-event-js', 'event_type', array( 'ajax_url' => admin_url( 'admin-ajax.php' ) ));
						wp_localize_script( 'mdjm-event-js', 'mdjmeventcost', array( 'ajax_url' => admin_url( 'admin-ajax.php' ) ) );
						wp_localize_script( 'mdjm-event-js', 'mdjmdjpackages', array( 'ajax_url' => admin_url( 'admin-ajax.php' ) ) );
						wp_localize_script( 'mdjm-event-js', 'mdjmsetdeposit', array( 'ajax_url' => admin_url( 'admin-ajax.php' ) ) );
						wp_localize_script( 'mdjm-event-js', 'event_employee_add', array( 'ajax_url' => admin_url( 'admin-ajax.php' ) ) );
						
						wp_enqueue_script( 'mdjm-dynamics' );
						wp_localize_script( 'mdjm-dynamics', 'mdjmaddons', array( 'ajax_url' => admin_url( 'admin-ajax.php' ) ) );
					}
					
				/* -- Transaction Posts Only -- */
					if( get_post_type() == MDJM_TRANS_POSTS )	{
						wp_register_script( 'mdjm-trans-js', MDJM_PLUGIN_URL . '/assets/js/mdjm-trans-post-val.js', array( 'jquery-validation-plugin' ), MDJM_VERSION_NUM );
						wp_enqueue_script( 'mdjm-trans-js' );
						wp_localize_script( 'mdjm-trans-js', 'transaction_type', array( 'ajax_url' => admin_url( 'admin-ajax.php' ) ) );
					}
				
				/* -- Venue Posts Only -- */
					if( get_post_type() == MDJM_VENUE_POSTS )	{
						wp_register_script( 'mdjm-venue-val', MDJM_PLUGIN_URL . '/assets/js/mdjm-venue-post-val.js', array( 'jquery-validation-plugin' ), MDJM_VERSION_NUM );
						wp_enqueue_script( 'mdjm-venue-val' );
					}
				}
			} // admin_enqueue
			
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
					/* -- MDJM Email Tracking -- */
					case 'MDJM_EMAIL_RCPT':
						if( MDJM_DEBUG == true )
							MDJM()->debug->log_it( 'MDJM Email API listener activated', true );
						include( MDJM_PLUGIN_DIR . '/includes/api/mdjm-api-email-rcpt.php' );
					break;
					/* -- Default action -- */
					default:
						MDJM()->debug->log_it( 'WARNING: Rogue API request received - ' . $listener, true );
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
				require_once( MDJM_PLUGIN_DIR . '/includes/admin/mdjm-cron.php' );
				
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
			 * Send the specified message to the debug file
			 *
			 * @param       str             Required: $debug_msg    The message to log
			 *                      bool    Optional: $stampit      true to include timestamp otherwise false
			 * @return
			 * @since       1.1.3
			 * @called From back and front
			 */
			public function debug_logger( $debug_msg='', $stampit=false )   {
				if( empty( $debug_msg ) )
					return;
			   
				MDJM()->debug->log_it( $debug_msg, $stampit );
			} // debug_logger
 
			/**
			 * Provide the correct page link dependant on permalink settings
			 * DEPRECATED SINCE 1.3.
			 * MAINTAINED FOR BACKWARDS COMPATIBILITY ONLY.
			 *
			 * @param    int    $page_id    The ID of the destination page
			 * @return   str		 		The URI link to the destination with the first query string (& or ?)
			 * @since    1.1.3
			 */
			public function get_link( $page_id='', $permalink=true, $echo=false )	{
				return mdjm_get_formatted_url( $page_id='', $permalink, $echo );
			} // get_link

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
			 *						Required: content 		str			post ID or email content as str
			 *						Required: to	 		str|int		email address or user ID of recipient
			 *						Optional: subject		str			required if content is not a post ID
			 *						Optional: from			int			user ID of sender, defaults to 0 (system)
			 *						Optional: attachments	arr			files to attach
			 *						Optional: journal		str|bool	The journal entry type or false not to log this action, default to 'email-client'
			 *						Optional: event_id		int			event ID
			 *						Optional: html			bool		true sends html (Default) false plain text
			 *						Optional: cc_dj			bool		true sends copy to DJ false does not, only applicable if we have event. Default per settings
			 *						Optional: cc_admin		bool		true sends copy to Admin false does not. Default per settings
			 *						Optional: source		str			what initiated the email - i.e. Event Enquiry (Default)
			 *						Optional: filter		bool		true (Default) filters subject and content for shortcode, false does not
			 *						Optional: add_filters	arr			An array with key {SEARCH} and value $replace for additional filters to process
			 *						Optional: log_comm		bool		true (Default) logs the email, false does not
			 *
			 * @return   str	$comm_id	The communication post ID if the email was successfully sent
			 * @since    1.1.3
			 */
			function send_email( $args )	{
				global $mdjm_posts, $mdjm_settings;
				
				if( MDJM_DEBUG == true )
					MDJM()->debug->log_it( 'Starting ' . __FUNCTION__, true );
				
				/* -- Error checking -- */
				if( !is_numeric( $args['content'] ) && empty( $args['subject'] ) )	{
					if( MDJM_DEBUG == true )
						MDJM()->debug->log_it( '	ERROR: Missing `subject` argument' );
						 
					return false;
				}
				
				$content = !empty( $args['content'] ) ? $args['content'] : '';
				$to = !empty( $args['to'] ) ? $args['to'] : '';
				$subject = !is_numeric( $content ) && !empty( $args['subject'] ) ? $args['subject'] : get_the_title( $content );
				$sender = !empty( $args['from'] ) ? $args['from'] : 1;
				
				if( empty( $to ) )	{
					if( MDJM_DEBUG == true )
						 MDJM()->debug->log_it( '	ERROR: Missing `to` argument' );
						 
					return false;	
				}
						
				if( empty( $content ) )	{
					if( MDJM_DEBUG == true )
						 MDJM()->debug->log_it( '	ERROR: Missing `content` argument' );
						 
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
						 MDJM()->debug->log_it( '	ERROR: User `' . $to . '` does not exist ' );
					
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
							 MDJM()->debug->log_it( '	ERROR: Specified template `' . $content . '` does not exist' );
							 
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
				
				// Filter the email headers
				$headers = apply_filters( 'mdjm_email_headers', $headers );
				
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
				
				/* -- Additional filters -- */
				if( !empty( $args['add_filters'] ) && is_array( $args['add_filters'] ) )	{
					if( MDJM_DEBUG == true )
						MDJM()->debug->log_it( 'Additional content filtering requested...' );
					
					foreach( $args['add_filters'] as $key => $value )	{
						$search[] = $key;
						$replace[] = $value;	
					}
					
					$sub = str_replace( $search, $replace, $sub );
					$msg = str_replace( $search, $replace, $msg );
				}
				
				// File attachments
				if( !empty( $args['attachments'] ) && is_array( $args['attachments'] ) )
					$files = $args['attachments'];
				else
					$files = array();
				
				// Apply filter to attach (additional) files to an email
				$files = apply_filters( 'mdjm_attach_files_to_email', $files );
												
				/* -- Add the COMM post -- */
				if( $log_comm == true )	{
					if( !class_exists( 'MDJM_Communication' ) )
						require_once( MDJM_PLUGIN_DIR . '/includes/admin/communications/mdjm-communications.php' );
						
					$mdjm_comms = new MDJM_Communication();
				
					$comm_post = $mdjm_comms->insert_comm( array (
											'subject'	 => $sub,
											'content'	 => $msg,
											'recipient'  => !empty( $recipient ) ? $recipient->ID : '',
											'source'	 => !empty( $args['source'] ) ? $args['source'] : 'Event Enquiry',
											'event'	  	 => !empty( $event ) ? $event->ID : '',
											'author'	 => $sender,
											'attachments'=> !empty( $files ) ? $files : ''
											) );
					if( empty( $comm_post ) && MDJM_DEBUG == true )
						MDJM()->debug->log_it( 'Could not create Comm post in ' . __FUNCTION__, true );
					
					elseif( MDJM_DEBUG == true )
						MDJM()->debug->log_it( 'Comm post created with ID `' . $comm_post . '` ', true );
					}
				else	{
					if( MDJM_DEBUG == true )
						MDJM()->debug->log_it( 'Skipping communication logging by command' );	
				}
								
				/* -- Send the email to the client with stat tracking if configured -- */
				if( wp_mail( 
					$recipient->user_email,
					$sub,
					$msg . ( !empty( $comm_post ) ? $mdjm_comms->insert_stat_image( $comm_post ) : '' ),
					$headers,
					$files ) )	{
					
					/* -- Set the status of the email -- */
					if( $log_comm == true )
						$mdjm_comms->change_email_status( $comm_post, 'sent' );
					
					if( MDJM_DEBUG == true )
						MDJM()->debug->log_it( '	-- Message "' . $sub . '" successfully sent to "' . $recipient->display_name . '"' );
						
					/* -- Update Journal -- */
					if( !empty( $journal ) && !empty( $event ) )	{
						MDJM()->events->add_journal( array(
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
					$mdjm_comms->change_email_status( $comm_post, 'failed' );
					if( MDJM_DEBUG == true )
						MDJM()->debug->log_it( '	ERROR: Message "' . $sub . '" could not be sent to "' . $recipient->display_name . '"' );
						
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
						if( wp_mail( $mdjm_recipient, $sub, $msg_prefix . $msg, $headers, $files ) )	{
							if( MDJM_DEBUG == true )
								MDJM()->debug->log_it( '	-- A copy of the message "' . $sub . '" successfully sent to ' . $mdjm_recipient );
						}
						else	{
							if( MDJM_DEBUG == true )
								MDJM()->debug->log_it( '	ERROR: A copy of the message "' . $sub . '" could not be sent to ' . $mdjm_recipient );
						}
					}
				}
				if( MDJM_DEBUG == true )
					MDJM()->debug->log_it( 'Completed ' . __FUNCTION__, true );

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
					MDJM()->debug->log_it( 'Starting ' . __FUNCTION__, true );
				
				if( empty( $content ) )	{
					if( MDJM_DEBUG == true )
						 MDJM()->debug->log_it( '	ERROR: No content passed for filtering ' );
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
							MDJM()->debug->log_it( '	-- Password reset for user ' . $c->ID );
						
						$c_pw = $pass_action;
						wp_set_password( $c_pw, $c->ID );
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
					
					$eventinfo = MDJM()->events->event_detail( $e->ID );
										
					$venue_details = MDJM()->events->mdjm_get_venue_details( get_post_meta( $e->ID, '_mdjm_event_venue_id', true ), $e->ID );

				}
					
				/* -- Replacements -- */
				$pairs = array(
				/* -- General -- */
					'{ADMIN_URL}'			=> admin_url(),
					'{APPLICATION_HOME}'	 => mdjm_get_formatted_url( MDJM_HOME, false ),
					'{APPLICATION_NAME}'	 => MDJM_APP,

					'{COMPANY_NAME}'		 => MDJM_COMPANY,
					'{CONTACT_PAGE}'		 => mdjm_get_formatted_url( MDJM_CONTACT_PAGE, false ),
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
					
					'{CONTRACT_URL}'		 => ( !empty( $e ) ? mdjm_get_formatted_url( MDJM_CONTRACT_PAGE ) . 'event_id=' . $e->ID : '' ),
																		
					'{DEPOSIT}'			  => ( !empty( $eventinfo['deposit'] ) ? $eventinfo['deposit'] : '' ),
												
					'{DEPOSIT_STATUS}'	   => ( !empty( $eventinfo['deposit_status'] ) ? $eventinfo['deposit_status'] : __( 'Due' ) ),
					'{DJ_EMAIL}'		     => ( !empty( $eventinfo['dj']->user_email ) ? $eventinfo['dj']->user_email : __( 'Not Assigned' ) ),
					'{DJ_FIRSTNAME}'	     => ( !empty( $eventinfo['dj']->user_firstname ) ? $eventinfo['dj']->user_firstname : '' ),
					'{DJ_FULLNAME}'		  => ( !empty( $eventinfo['dj']->display_name ) ? $eventinfo['dj']->display_name : '' ),
					'{DJ_NOTES}'		     => ( !empty( $eventinfo['dj_notes'] ) ? $eventinfo['dj_notes'] : '' ),
					'{DJ_PRIMARY_PHONE}'     => ( !empty( $eventinfo['dj']->phone1 ) ? $eventinfo['dj']->phone1 : '' ),
					'{DJ_SETUP_DATE}'	    => ( !empty( $eventinfo['setup_date'] ) && $eventinfo['setup_date'] != 'Not Specified' ? 
						date( MDJM_SHORTDATE_FORMAT, $eventinfo['setup_date'] ) : __( 'Not Specified' ) ),
						
					'{DJ_SETUP_TIME}'	    => ( !empty( $eventinfo['setup_time'] ) ? $eventinfo['setup_time'] : '' ),
					'{END_TIME}'		     => ( !empty( $eventinfo['finish'] ) ? $eventinfo['finish'] : '' ),
					'{END_DATE}'		     => ( !empty( $eventinfo['end_date'] ) && is_numeric( $eventinfo['end_date'] ) ? 
						date( MDJM_SHORTDATE_FORMAT, $eventinfo['end_date'] ) : __( 'Not Specified' ) ),
						
					'{EVENT_DATE}'		   => ( !empty( $eventinfo['date'] ) && is_numeric( $eventinfo['date'] ) ? 
						date( 'l, jS F Y', $eventinfo['date'] ) : __( 'Not Specified' ) ),
						
					'{EVENT_DATE_SHORT}'     => ( !empty( $eventinfo['date'] ) && is_numeric( $eventinfo['date'] ) ? 
						date( MDJM_SHORTDATE_FORMAT, $eventinfo['date'] ) : __( 'Not Specified' ) ),
						
					'{EVENT_DESCRIPTION}'    => ( !empty( $eventinfo['notes'] ) ? $eventinfo['notes'] : '' ),
					'{EVENT_NAME}'		   => ( !empty( $eventinfo['name'] ) ? $eventinfo['name'] : '' ),
					'{EVENT_STATUS}'		 => ( !empty( $eventinfo['status'] ) ? $eventinfo['status'] : '' ),
					'{EVENT_TYPE}'		   => ( !empty( $eventinfo['type'] ) ? $eventinfo['type'] : '' ),
					//'{PAYMENT_AMOUNT}'	   => ( isset( $_POST['mc_gross'] ) ? display_price( $_POST['mc_gross'] ) : '' ),
					//'{PAYMENT_DATE}'		 => ( isset( $_POST['payment_date'] ) ? date( MDJM_SHORTDATE_FORMAT, strtotime( $_POST['payment_date'] ) ) : '' ),
					//'{PAYMENT_FOR}'		  => ( isset( $_POST['custom'] ) ? $_POST['custom'] : '' ),
					'{PAYMENT_URL}'		  => ( !empty( $e ) ? mdjm_get_formatted_url( MDJM_PAYMENT_PAGE ) . 'event_id=' . $e->ID : '' ),
					'{PAYMENT_HISTORY}'	  => ( !empty( $eventinfo['payment_history'] ) ? 
						$eventinfo['payment_history'] : __( 'No payments', 'mobile-dj-manager' ) ),
						
					'{PLAYLIST_CLOSE}'	   => $mdjm_settings['playlist']['close'] != 0 ? $mdjm_settings['playlist']['close'] : 'never',
					'{PLAYLIST_URL}'		 => mdjm_get_formatted_url( MDJM_PLAYLIST_PAGE, false ),
					'{QUOTES_URL}'		   => ( !empty( $e->ID ) ? mdjm_get_formatted_url( MDJM_QUOTES_PAGE, true ) . 'event_id=' . $e->ID : '' ),
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
						
					'{EVENT_PACKAGE}'		=> ( !empty( $eventinfo['package'] ) && !empty( $e->ID ) ? get_event_package( $e->ID, false ) : 'N/A' ),
					'{EVENT_PACKAGE_COST}'   => ( !empty( $e ) ? get_event_package( $e->ID, true ) : 'N/A' ),
						
					//'{AVAILABLE_ADDONS}'	 => ( !empty( $eventinfo['dj']->ID ) ? 
						//implode( "\n", get_available_addons( $eventinfo['dj']->ID, '' ) ) : implode( "\n", get_available_addons( '', '' ) ) ),
						
					//'{AVAILABLE_ADDONS_COST}' => ( !empty( $eventinfo['dj']->ID ) ? 
						//implode( "\n", get_available_addons( $eventinfo['dj']->ID, '' ) ) : implode( "\n", get_available_addons( '', '' ) ) ),
					
					'{EVENT_ADDONS}'		 => ( !empty( $eventinfo['addons'] ) ? $eventinfo['addons'] : 'N/A' ),
					'{EVENT_ADDONS_COST}'    => ( !empty( $e ) ? get_event_addons( $e->ID, true ) : 'N/A' ),
					
				);
				
				// Allow the $pairs array to be filtered
				$pairs = apply_filters(
					'mdjm_shortcode_filter_pairs',
					$pairs,
					( !empty( $e->ID ) ? $e->ID : '' ),
					( !empty( $eventinfo ) ? $eventinfo : '' ) );
				
				/* -- Create the Search/Replace Array's -- */							
				foreach( $pairs as $key => $value )	{
					$search[] = $key;
					$replace[] = $value;	
				}
				
				/* -- Return the filtered data -- */
				if( MDJM_DEBUG == true )
					MDJM()->debug->log_it( 'Completed ' . __FUNCTION__, true );
				
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
							'3'	=> array( 'updated', __( 'Field deleted successfully', 'mobile-dj-manager' ) ),
							'4'	=> array( 'updated', __( '', 'mobile-dj-manager' ) ),
							'5'	=> array( 'error', __( '', 'mobile-dj-manager' ) )
							);
				
				echo '<div id="message" class="' . ( empty( $class ) ? $message[$msg][0] : $class ) . '">' . "\r\n" . 
				'<p>' . ( empty( $class ) ? $message[$msg][1] : $msg ) . '</p>' . "\r\n" . 
				'</div>' . "\r\n";
			} // messages						
		} // MDJM class
	} // if( !class_exists )