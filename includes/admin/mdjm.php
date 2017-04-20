<?php
/**
 * Class: MDJM
 * Description: The main MDJM class
 *
 */
	
	/* -- Build the MDJM class -- */
	if ( ! class_exists( 'MDJM' ) )	{
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
				global $wpdb, $pagenow, $mdjm_post_types, $clientzone;
								
				/**
				 * This can be removed post 1.3
				 *
				 *
				 *
				 */
				$this->mdjm_events = new MDJM_Events(); // REMOVE
								
				$mdjm_post_types = array(
					'mdjm_communication',
					'contract',
					'mdjm-custom-fields',
					'mdjm-signed-contract',
					'email_template',
					'mdjm-event',
					'mdjm-quotes',
					'mdjm-transaction',
					'mdjm-venue'
				);
												
				/* -- Hooks -- */
				add_action( 'init', array( &$this, 'mdjm_init' ) ); // init processes
				add_action( 'admin_enqueue_scripts', array( &$this, 'admin_enqueue' ) ); // Admin styles & scripts
			} // __construct
			
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
 * SETTINGS
 * --
 */			
			/*
			 * mdjm_settings
			 * 18/03/2015
			 * @since 1.1.3
			 * Define the settings
			 */
			 public function mdjm_settings()	{
				global $mdjm_settings;
				$mdjm_settings = array(
									'main'		=> get_option( 'mdjm_plugin_settings' ),
									'email'	   => get_option( 'mdjm_email_settings' ),
									'templates'   => get_option( 'mdjm_templates_settings' ),
									'events'	  => get_option( 'mdjm_event_settings' ),
									'playlist'	=> get_option( 'mdjm_playlist_settings' ),
									'custom_text' => get_option( 'mdjm_frontend_text' ),
									'clientzone'  => get_option( 'mdjm_clientzone_settings' ),
									'availability'=> get_option( 'mdjm_availability_settings' ),
									'pages'	   => get_option( 'mdjm_plugin_pages' ),
									'payments'	=> get_option( 'mdjm_payment_settings' ),
									'permissions' => get_option( 'mdjm_plugin_permissions' ),
									'data' 		=> get_option( 'mdjm_api_data' ),
									'uninst'	  => get_option( 'mdjm_uninst' )
								);
								
				define( 'MDJM_DJ', isset( $mdjm_settings['events']['artist'] ) ? $mdjm_settings['events']['artist'] : 'DJ' );				
				define( 'MDJM_JOURNAL', ( !empty( $mdjm_settings['events']['journaling'] ) ? true : false ) );
				define( 'MDJM_CREDITS', ( !empty( $mdjm_settings['main']['show_credits'] ) ? true : false ) );
				define( 'MDJM_TRACK_EMAILS', ( !empty( $mdjm_settings['email']['track_client_emails'] ) ? true : false ) );
				define( 'MDJM_MULTI', ( !empty( $mdjm_settings['events']['employer'] ) ? true : false ) );
				define( 'MDJM_PACKAGES', mdjm_packages_enabled() );
				define( 'MDJM_TIME_FORMAT', ( isset( $mdjm_settings['main']['time_format'] ) ? $mdjm_settings['main']['time_format'] : 'H:i' ) );
				define( 'MDJM_SHORTDATE_FORMAT', isset( $mdjm_settings['main']['short_date_format'] ) ? $mdjm_settings['main']['short_date_format'] : 'd/m/Y' );
				define( 'MDJM_EVENT_PREFIX', isset( $mdjm_settings['events']['event_prefix'] ) ? $mdjm_settings['events']['event_prefix'] : '' );
				define( 'MDJM_PLAYLIST_ENABLE', !empty( $mdjm_settings['playlist']['enable_playlists'] ) ? true : false );
				define( 'MDJM_PLAYLIST_CLOSE', isset( $mdjm_settings['playlist']['close'] ) ? $mdjm_settings['playlist']['close'] : '0' );
				define( 'MDJM_PAYMENTS', ( !empty( $mdjm_settings['payments']['payment_gateway'] ) ? true : false ) );
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
								
				// jQuery Validation
				wp_register_script( 'jquery-validation-plugin', 'https://ajax.aspnetcdn.com/ajax/jquery.validate/1.11.1/jquery.validate.min.js', false );
								
				// Users JS for Ajax
				wp_register_script(
					'mdjm-users-js',
					MDJM_PLUGIN_URL . '/assets/js/mdjm-users.js',
					array( 'jquery' ),
					MDJM_VERSION_NUM
				);
				
				if( in_array( get_post_type(), $mdjm_post_types ) || ( isset( $_GET['section'] ) && $_GET['section'] == 'mdjm_custom_event_fields' ) )	{
					/* -- mdjm-posts.css: The CSS script for all custom post pages -- */
					wp_register_style( 'mdjm-posts', MDJM_PLUGIN_URL . '/assets/css/mdjm-posts.css', '', MDJM_VERSION_NUM );
					wp_enqueue_style( 'mdjm-posts' );
										
					/* -- jQuery Validation -- */
					wp_enqueue_script( 'jquery-validation-plugin' );
								
				/* -- Contract Templates Only -- */
					if( get_post_type() == 'contract' )	{
						wp_register_script( 'mdjm-contract-val', MDJM_PLUGIN_URL . '/assets/js/mdjm-contract-post-val.js', array( 'jquery-validation-plugin' ), MDJM_VERSION_NUM );
						wp_enqueue_script( 'mdjm-contract-val' );
					}
				
				/* -- Email Templates Only -- */
					if( get_post_type() == 'email_template' )	{
						wp_register_script( 'mdjm-email-val', MDJM_PLUGIN_URL . '/assets/js/mdjm-email-post-val.js', array( 'jquery-validation-plugin' ), MDJM_VERSION_NUM );
						wp_enqueue_script( 'mdjm-email-val' );
					}

				/* -- Transaction Posts Only -- */
					if( get_post_type() == 'mdjm-transaction' )	{
						wp_register_script( 'mdjm-trans-js', MDJM_PLUGIN_URL . '/assets/js/mdjm-trans-post-val.js', array( 'jquery-validation-plugin' ), MDJM_VERSION_NUM );
						wp_enqueue_script( 'mdjm-trans-js' );
						wp_localize_script( 'mdjm-trans-js', 'transaction_type', array( 'ajax_url' => admin_url( 'admin-ajax.php' ) ) );
					}
				
				/* -- Venue Posts Only -- */
					if( get_post_type() == 'mdjm-venue' )	{
						wp_register_script( 'mdjm-venue-val', MDJM_PLUGIN_URL . '/assets/js/mdjm-venue-post-val.js', array( 'jquery-validation-plugin' ), MDJM_VERSION_NUM );
						wp_enqueue_script( 'mdjm-venue-val' );
					}
				}
			} // admin_enqueue
			
/*
 * --
 * GENERAL
 * --
 */			
 
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
				global $mdjm_settings;
				
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
					if( is_string( get_post_status( $content ) ) )	{
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
					
				$headers[] = 'X-Mailer: ' . MDJM_NAME . ' version ' . MDJM_VERSION_NUM . ' (http://mdjm.co.uk)'; 
				
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
						mdjm_add_journal(
							array(
								'user_id'         => ! empty( $sender_data->ID ) ? $sender_data->ID : '1',
								'event_id'        => $event->ID,
								'comment_content' => 'Email sent to Client - ' . $sub . '<br />(' . time() . ')'
							),
							array(
								'type'       => $journal,
								'visibility' => '1'
							)
						);
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
					
					// Client password reset action
					$c_pw = sprintf( 
						__( 'Please <a href="%s">click here</a> to reset your password', 'mobile-dj-manager' ),
						home_url( '/wp-login.php?action=lostpassword' ) );
					
					$reset = get_user_meta( $c->ID, 'mdjm_pass_action', true );
					
					if( ! empty( $reset ) )	{
						if( MDJM_DEBUG == true )
							MDJM()->debug->log_it( '	-- Password reset for user ' . $c->ID );
						
						$reset = wp_generate_password( mdjm_get_option( 'pass_length', 8 ), mdjm_get_option( 'complex_passwords', true ) );
						
						wp_set_password( $reset, $c->ID );
						
						$c_pw = $reset;
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
												
					'{DEPOSIT_STATUS}'	   => ( !empty( $eventinfo['deposit_status'] ) ? $eventinfo['deposit_status'] : __( 'Due', 'mobile-dj-manager' ) ),
					'{DJ_EMAIL}'		     => ( !empty( $eventinfo['dj']->user_email ) ? $eventinfo['dj']->user_email : __( 'Not Assigned', 'mobile-dj-manager' ) ),
					'{DJ_FIRSTNAME}'	     => ( !empty( $eventinfo['dj']->user_firstname ) ? $eventinfo['dj']->user_firstname : '' ),
					'{DJ_FULLNAME}'		  => ( !empty( $eventinfo['dj']->display_name ) ? $eventinfo['dj']->display_name : '' ),
					'{DJ_NOTES}'		     => ( !empty( $eventinfo['dj_notes'] ) ? $eventinfo['dj_notes'] : '' ),
					'{DJ_PRIMARY_PHONE}'     => ( !empty( $eventinfo['dj']->phone1 ) ? $eventinfo['dj']->phone1 : '' ),
					'{DJ_SETUP_DATE}'	    => ( !empty( $eventinfo['setup_date'] ) && $eventinfo['setup_date'] != 'Not Specified' ? 
						date( MDJM_SHORTDATE_FORMAT, $eventinfo['setup_date'] ) : __( 'Not Specified', 'mobile-dj-manager' ) ),
						
					'{DJ_SETUP_TIME}'	    => ( !empty( $eventinfo['setup_time'] ) ? $eventinfo['setup_time'] : '' ),
					'{END_TIME}'		     => ( !empty( $eventinfo['finish'] ) ? $eventinfo['finish'] : '' ),
					'{END_DATE}'		     => ( !empty( $eventinfo['end_date'] ) && is_numeric( $eventinfo['end_date'] ) ? 
						date( MDJM_SHORTDATE_FORMAT, $eventinfo['end_date'] ) : __( 'Not Specified', 'mobile-dj-manager' ) ),
						
					'{EVENT_DATE}'		   => ( !empty( $eventinfo['date'] ) && is_numeric( $eventinfo['date'] ) ? 
						date( 'l, jS F Y', $eventinfo['date'] ) : __( 'Not Specified', 'mobile-dj-manager' ) ),
						
					'{EVENT_DATE_SHORT}'     => ( !empty( $eventinfo['date'] ) && is_numeric( $eventinfo['date'] ) ? 
						date( MDJM_SHORTDATE_FORMAT, $eventinfo['date'] ) : __( 'Not Specified', 'mobile-dj-manager' ) ),
						
					'{EVENT_DESCRIPTION}'    => ( !empty( $eventinfo['notes'] ) ? $eventinfo['notes'] : '' ),
					'{EVENT_NAME}'		   => ( !empty( $eventinfo['name'] ) ? $eventinfo['name'] : '' ),
					'{EVENT_STATUS}'		 => ( !empty( $eventinfo['status'] ) ? $eventinfo['status'] : '' ),
					'{EVENT_TYPE}'		   => ( !empty( $eventinfo['type'] ) ? $eventinfo['type'] : '' ),
					//'{PAYMENT_AMOUNT}'	   => ( isset( $_POST['mc_gross'] ) ? mdjm_currency_filter( mdjm_sanitize_amount( $_POST['mc_gross'] ) ) : '' ),
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