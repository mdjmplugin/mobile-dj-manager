<?php
/**
 * mdjm-clientzone.php
 * 08/04/2015
 * @since 1.1.3
 * The ClientZone class
 * Also acts as the controller for all front end activity
 */
	defined( 'ABSPATH' ) or die( 'Direct access to this page is disabled!!!' );
	
	/* -- Build the ClientZone class -- */
	if( !class_exists( 'ClientZone' ) )	{
		class ClientZone	{
						
		 /**
		  * __construct
		  * defines the params used within the class
		  *
		  *
		  */
			public function __construct()	{
				global $clientzone_loaded, $my_mdjm, $mdjm_settings;
								
				$clientzone_loaded = true;
																				
				/* -- Hooks -- */
				add_action( 'wp_enqueue_scripts', array( &$this, 'client_zone_enqueue' ) ); // Styles & Scripts
				add_action( 'wp_loaded', array( &$this, 'my_events' ) ); // Current users events
				add_action( 'init', array( &$this, 'init' ) ); // Init actions
			} // __construct
			
			/**
			 * Actions during the init hook
			 *
			 * @params
			 *
			 * @return
			 */
			function init()	{
				$this->includes();
			} // init
			
			/**
			 * Call files for inclusion
			 *
			 * @params
			 *
			 * @return
			 */
			function includes()	{
				require_once( MDJM_CLIENTZONE . '/includes/mdjm-availability.php' );
			} // includes
/*
 * --
 * CLIENT EVENT ACTIONS
 * --
 */
			/**
			 * my_events
			 * Retrieve all of the currently logged in users events, and details
			 * and store within the global $my_mdjm array
			 *
			 * @param	int		$client		Optional: ID of user. Default to current user
			 * @return	arr		$my_mdjm	Array of event and user data
			 */
			public function my_events( $client='' )	{
				global $current_user, $my_mdjm, $mdjm;
				
				/* -- No user, no data -- */
				if( !is_user_logged_in() )
					return;
							
				$c = !empty( $client ) ? $client : $current_user->ID;
				
				$event_stati = mdjm_all_event_status();
				
				$my_mdjm = array();
				
				$my_mdjm['me'] = get_userdata( $c );
				
				if( is_dj() || current_user_can( 'administrator' ) )	{
					$my_mdjm['next'] = MDJM()->events->next_event( '', 'dj' );
					$my_mdjm['active'] = MDJM()->events->active_events( '', 'dj' );
				}
					
				else	{
					$my_mdjm['next'] = MDJM()->events->next_event();
					$my_mdjm['active'] = MDJM()->events->active_events();
				}
			} // my_events
			
			/**
			 * accept_enquiry
			 * Complete actions when client books event via Client Zone
			 * The Client must be logged in
			 *
			 * @param		arr		$event	The event post object
			 * @return		bool			True upon success, otherwise false
			 * @since		2.0
			 * 
			 */
			public function accept_enquiry( $post )	{
				global $mdjm, $my_mdjm, $mdjm_settings;
				
				if( empty( $post ) )	{
					if( MDJM_DEBUG == true )
						MDJM()->debug->log_it( 'ERROR: No event object was provided in ' . __METHOD__, true );
						
					?>
					<script type="text/javascript">
                    window.location.replace("<?php echo mdjm_get_formatted_url( MDJM_HOME ) . 'action=view_event&event_id=' . $post->ID . '&message=2&class=4'; ?>");
                    </script>
                    <?php
					exit;
				}
				if( get_current_user_id() != get_post_meta( $post->ID, '_mdjm_event_client', true ) )	{
					if( MDJM_DEBUG == true )
						MDJM()->debug->log_it( 'ERROR: User ' . get_current_user_id() . ' is not authorised to accept this enquiry (' . $post->ID . ') in ' . __METHOD__, true );
						
					?>
					<script type="text/javascript">
                    window.location.replace("<?php echo mdjm_get_formatted_url( MDJM_HOME ) . 'action=view_event&event_id=' . $post->ID . '&message=6&class=4'; ?>");
                    </script>
                    <?php
					exit;
				}
				
				/* -- Security verification -- */
				if( !isset( $_GET['__mdjm_verify'] ) || !wp_verify_nonce( $_GET['__mdjm_verify'], 'book_event' ) )	{
					if( MDJM_DEBUG == true )
						MDJM()->debug->log_it( 'ERROR: User ' . get_current_user_id() . ' is not authorised to accept this enquiry (' . $post->ID . ') in ' . __METHOD__, true );
					
					?>
					<script type="text/javascript">
                    window.location.replace("<?php echo mdjm_get_formatted_url( MDJM_HOME ) . 'action=view_event&event_id=' . $post->ID . '&message=6&class=4'; ?>");
                    </script>
                    <?php
					exit;
				}
				
				/* -- Prepare the meta data -- */
				$meta_update = array(
							'_mdjm_event_last_updated_by'		=> $my_mdjm['me']->ID,
							'_mdjm_event_enquiry_accepted'	   => date( 'Y-m-d H:i:s' ),
							'_mdjm_event_enquiry_accepted_by'	=> $my_mdjm['me']->ID,
							);
							
				/* -- Remove the save post hook to avoid loops -- */
				remove_action( 'save_post_mdjm-event', 'mdjm_save_event_post', 10, 3 );
				
				/* -- Initiate actions for status change -- */
				wp_transition_post_status( 'mdjm-contract', $post->post_status, $post );
				
				/* -- Update the post status -- */
				wp_update_post( array( 'ID' => $post->ID, 'post_status' => 'mdjm-contract' ) );
				
				/* -- Update the post meta -- */
				foreach( $meta_update as $event_meta_key => $event_meta_value )	{
					update_post_meta( $post->ID, $event_meta_key, $event_meta_value );
					$field_updates[] = 'Field ' . $event_meta_key . ' updated with ' . $event_meta_value;
				}						
				
				/* -- Update Journal with event updates -- */
				if( MDJM_JOURNAL == true )	{
					if( MDJM_DEBUG == true )
						MDJM()->debug->log_it( '	-- Adding journal entry' );
						
					mdjm_add_journal(
						array(
							'user_id'         => $my_mdjm['me']->ID,
							'event_id'        => $post->ID,
							'comment_content' => 'Enquiry accepted by ' . $my_mdjm['me']->display_name
						),
						array(
							'type'       => 'update-event',
							'visibility' => '2'
						)
					);
				}
				else	{
					if( MDJM_DEBUG == true )
						MDJM()->debug->log_it( '	-- Journalling is disabled' );	
				}
				
				/* -- Email Contract Link -- */
				$contact_client = !empty( $mdjm_settings['templates']['contract_to_client'] ) ? true : false;
				$client_email = isset( $mdjm_settings['templates']['contract'] ) ? $mdjm_settings['templates']['contract'] : false;
				
				if( !is_string( get_post_status( $client_email ) ) )	{
					if( MDJM_DEBUG == true )
						MDJM()->debug->log_it( 'ERROR: No email template for the contract link has been found ' . __FUNCTION__, $stampit=true );
					wp_die( 'ERROR: Either no email template is defined or an error has occured. Check your Settings.' );
				}
				
				if( $contact_client == true )	{
					if( MDJM_DEBUG == true )
						MDJM()->debug->log_it( 'Configured to email client with template ID ' . $client_email );
					
					if( MDJM_DEBUG == true )
						MDJM()->debug->log_it( 'Generating email...' );
					
					$email_args = array( 
						'content'	=> $client_email,
						'to'		 => get_post_meta( $post->ID, '_mdjm_event_client', true ),
						'from'	   => $mdjm_settings['templates']['contract_from'] == 'dj' ? get_post_meta( $post->ID, '_mdjm_event_dj', true ) : 0,
						'journal'	=> 'email-client',
						'event_id'   => $post->ID,
						'html'	   => true,
						'cc_dj'	  => isset( $mdjm_settings['email']['bcc_dj_to_client'] ) ? true : false,
						'cc_admin'   => isset( $mdjm_settings['email']['bcc_admin_to_client'] ) ? true : false,
						'source'	 => 'Event Enquiry Accepted via ' . MDJM_APP );
					
					// Filter the email args
					$email_args = apply_filters( 'mdjm_contract_email_args', $email_args );
					
					// Send the email
					$contract_email = $mdjm->send_email( $email_args );
					
					if( $contract_email )	{
						if( MDJM_DEBUG == true )
							 MDJM()->debug->log_it( '	-- Contract link email sent to client ' );
					}
					else	{
						if( MDJM_DEBUG == true )
							 MDJM()->debug->log_it( '	ERROR: Contract link email was not sent' );	
					}	
				}
				else	{
					if( MDJM_DEBUG == true )
						MDJM()->debug->log_it( 'Not configured to email client' );	
				}
				
				/* -- Re-add the save post hook -- */
				add_action( 'save_post_mdjm-event', 'mdjm_save_event_post', 10, 3 );
				
				/* -- Email admin to notify of changes -- */
				if( MDJM_NOTIFY_ADMIN == true )	{
					if( MDJM_DEBUG == true )
						MDJM()->debug->log_it( 'Sending event status change notification to admin' );
						
					$content = '<html>' . "\n" . '<body>' . "\n";
					$content .= '<p>' . sprintf( __( 'Good news... %s has just accepted their event quotation via %s', 'mobile-dj-manager' ), 
										'{CLIENT_FULLNAME}', MDJM_APP ) . '</p>';
										
					$content .= '<hr />' . "\n";
					$content .= '<h4><a href="' . get_edit_post_link( $post->ID ) . '">' . __( 'Event ID', 'mobile-dj-manager' ) . ': ' 
						. MDJM_EVENT_PREFIX . $post->ID . '</a></h4>' . "\n";
						
					$content .= '<p>' . "\n";
					$content .= __( 'Date', 'mobile-dj-manager' ) . ': {EVENT_DATE}<br />' . "\n";
					$content .= __( 'Type', 'mobile-dj-manager' ) . ': ' . MDJM()->events->get_event_type( $post->ID ) . '<br />' . "\n";
					
					$event_stati = mdjm_all_event_status();
					
					$content .= __( 'Status', 'mobile-dj-manager' ) . ': ' . $event_stati[get_post_status( $post->ID )] . '<br />' . "\n";
					$content .= __( 'Client', 'mobile-dj-manager' ) . ': {CLIENT_FULLNAME}<br />' . "\n";
					$content .= __( 'Value', 'mobile-dj-manager' ) . ': {TOTAL_COST}<br />' . "\n";
					
					$deposit = get_post_meta( $post->ID, '_mdjm_event_deposit' );
					$deposit_status = get_post_meta( $post->ID, '_mdjm_event_deposit_status' );
					
					if( !empty( $deposit ) && $deposit != '0.00' )
						$content .= __( 'Deposit', 'mobile-dj-manager' ) . ': {DEPOSIT} ({DEPOSIT_STATUS})<br />' . "\n";
					
					$content .= __( 'Balance Due', 'mobile-dj-manager' ) . ': {BALANCE}</p>' . "\n";
					
					$content .= '<p>' . sprintf( __( '%sView Event%s', 'mobile-dj-manager' ),
									'<a href="=' . get_edit_post_link( $post->ID ) . '">',
									'</a>' )
									 . '</p>' . "\n";
					
					$content .= '</body>' . "\n" . '</html>' . "\n";
					
					$mdjm->send_email( array(
										'content'		=> $content,
										'to'			 => $mdjm_settings['email']['system_email'],
										'subject'		=> __( 'Event Quotation Accepted', 'mobile-dj-manager' ),
											
										'journal'		=> false,
										'event_id'	   => $post->ID,
										'cc_dj'		  => false,
										'cc_admin'	   => false,
										'log_comm'	   => false ) );
				}
				else
					if( MDJM_DEBUG == true )
						MDJM()->debug->log_it( 'Skipping admin notification' );
				
				if( MDJM_DEBUG == true )
					MDJM()->debug->log_it( 'Completed enquiry acceptance via ' . MDJM_APP . ' in ' .  __METHOD__, true );
					
				$this->display_message( 1, 2 );
			} // accept_enquiry
			
/*
 * --
 * CLIENT PROFILE ACTIONS
 * --
 */
			/**
			 * Check for completness of the Client's profile and if configured
			 * to do so, print an error if the profile is not completed
			 *
			 * @param	int		$client_id	The user ID of the profile to check
			 * @return	bool	true if the profile is complete, otherwise false
			 */
			public function client_profile_complete( $client_id )	{
				global $mdjm, $my_mdjm;
				
				if( empty( $client_id ) )	{
					if( MDJM_DEBUG == true )
						MDJM()->debug->log_it( 'ERROR: No client ID was parsed for checking in ' . __METHOD__, true );
					return false;	
				}
				
				$client_data = get_userdata( $client_id );
				
				/* -- Check the fields marked as required within Custom Client Fields -- */
				$custom_fields = get_option( 'mdjm_client_fields' );
				foreach( $custom_fields as $field )	{
					if( !empty( $field['required'] ) )
						$required_fields[] = $field['id'];
				}
									
				foreach( $required_fields as $required_field )	{
					if( empty( $my_mdjm['me']->$required_field ) )
						return false;	
				}
				
				return true;
				
			} // check_client_profile
			
			/**
			 * Retrieve and print the given client's address
			 *
			 * @param	int		$client_id	The ID of the client
			 *					if not set, uses value of $my_mdjm
			 *
			 */
			public function get_client_address( $client_id='' )	{
				global $my_mdjm;
				
				$client = empty( $client_id ) ? $my_mdjm['me'] : get_userdata( $client_id );
				
				if( !empty( $client->address1 ) )	{
					$client_address[] = stripslashes( $client->address1 );
					if( !empty( $client->address2 ) )
						$client_address[] = stripslashes( $client->address2 );
					if( !empty( $client->town ) )
						$client_address[] = stripslashes( $client->town );
					if( !empty( $client->county ) )
						$client_address[] = stripslashes( $client->county );
					if( !empty( $client->county ) )
						$client_address[] = stripslashes( $client->postcode );
				}
				
				return ( !empty( $client_address ) ? implode( '<br />', $client_address ) : __( 'No Address', 'mobile-dj-manager' ) );
				
			} // get_client_address
			
			/**
			 * Whether or not to display a client profile warning
			 *
			 *
			 * @ return		bool	True if a warning should be displayed
			 */
			public function warn_profile()	{
				global $mdjm_settings;
				
				return ( !empty( $mdjm_settings['clientzone']['notify_profile'] ) ? true : false );
				
			} // warn_profile
/*
 * --
 * STYLES & SCRIPTS
 * --
 */
			/**
			 * client_zone_enqueue
			 * Register & enqueue the scripts & styles we want to use
			 * Only register those scripts we want on all pages
			 * Or those we can control
			 * Others should be called from the pages themselves
			 */
			public function client_zone_enqueue()	{
				
				wp_register_style( 'mobile-dj-manager', MDJM_PLUGIN_URL . '/assets/css/mdjm-styles.css', '', MDJM_VERSION_NUM );
				
				/* -- Dynamics Ajax -- */
				wp_register_script( 'mdjm-dynamics', MDJM_PLUGIN_URL . '/assets/js/mdjm-dynamic.js', array( 'jquery' ), MDJM_VERSION_NUM );
								
				wp_enqueue_style( 'mobile-dj-manager');
				
			} // client_zone_enqueue
			
			/**
			 * Display given message on the screen
			 * 
			 * @param	int		$type	The type of message to display
			 *							1 = informative (Default)
			 *							2 = success
			 *							3 = warning
			 *							4 = error
			 *							5 = validation
			 *
			 *			str		$msg	The message content
			 *
			 */
			public function display_notice( $type, $msg )	{
				global $mdjm;
				
				$type = !empty( $type ) ? $type : 1;
				
				/* -- If no message was parsed, log it and return -- */
				if( empty( $msg ) )	{
					if( MDJM_DEBUG == true )
						MDJM()->debug->log_it( 'Instruction to display message could not be fulfilled as no message present in ' . __METHOD__, true);
						
					return;
				}
				$class = array(
							1	=> 'mdjm-info',
							2	=> 'mdjm-success',
							3	=> 'mdjm-warning',
							4	=> 'mdjm-error',
							5	=> 'mdjm-validation',
							);
				
				echo '<div class="' . $class[$type] . '">' . __( $msg, 'mobile-dj-manager' ) . '</div>' . "\r\n";
				
			} // display_notice
			
			/**
			 * Display the specified message upon the web page
			 *
			 * @param	int		$_GET['message']	The array key of the message
			 *
			 */
			public function display_message( $msg, $class )	{
				global $mdjm, $mdjm_settings;
				
				$mdjm_messages = array(
									1	=> 'Thank you. Your event has been updated and your contract has been issued.' . 
										 ( !empty( $mdjm_settings['templates']['contract_to_client'] ) ? 
										 '<br />You will receive confirmation via email shortly.' : '' ),
										 
									2	=> 'An error has occured whilst confirming your event. Please <a href="' . mdjm_get_formatted_url( MDJM_CONTACT_PAGE, false ) . 
										'">contact me for assistance</a>',
										
									3	=> $this->__text( 'contract_sign_success', 'Thank you. Your contract has been successfully signed and your event is now <strong>confirmed</strong>.<br />' . 
											'A confirmation email is on it\'s way to you' ),
									4	=> 'Security verification failed. We could not update your profile at this time',
									5	=> 'Security verification failed. We could not update the playlist at this time',
									6	=> 'Security verification failed. We could not update your event at this time',
									9	=> 'This event does not belong to you.<br />' . 
											'<a href="' . mdjm_get_formatted_url( MDJM_HOME, false ) . '">' . MDJM_COMPANY . ' ' . MDJM_APP . ' Home Page</a>',
									);
									
				$this->display_notice( $class, $mdjm_messages[$msg] );
									
			} // display_message
			
/*
 * --
 * LOGIN & CUSTOM TEXT
 * --
 */
			/**
			 * login
			 * Display the login screen
			 *
			 * @param
			 * @return				Login screen text and form
			 * @since		1.1.3
			 * @called
			 */
			public function login()	{
				global $mdjm_settings;
				
				$default_text = '<p>You must be logged in to enter this area of the website. ' .
						   		'Please enter your username and password below to continue, or use the menu items above to navigate to another area of our website.</p>' . 
								"\r\n";
				
				$the_text = ( MDJM_CUSTOM_TEXT == true ) ? $this->custom_text( 'not_logged_in' ) : $default_text;
					
				echo $the_text;

				wp_login_form();		
			} // login
			
			/**
			 *
			 *
			 *
			 *
			 */
			public function no_permission()	{
				global $mdjm, $mdjm_settings;
				
				echo '<p>' . __( 'ERROR: You do not have permission to view this page.', 'mobile-dj-manager' ) . '</p>' . "\r\n" . 
				'<p>' . __( 'Please contact the <a href="mailto:' . $mdjm_settings['email']['system_email'] . 
				'">website administrator</a> or <a href="' . mdjm_get_formatted_url( MDJM_HOME ) . '">' . 
				'Click here to return to the ' . MDJM_COMPANY . ' ' . MDJM_APP . ' home page.', 'mobile-dj-manager' ) . '</p>';
			
			} // no_permission

			/**
			 * custom_text
			 * Display the custom text
			 *
			 * @param		str		The custom text key to display
			 * @return		str		$the_text	Content of the custom text setting
			 * @since		1.1.3
			 * @called
			 */
			public function custom_text( $key='' )	{
				global $my_mdjm, $mdjm, $mdjm_settings, $post;
				
				if( empty( $key ) )
					return;
				
				$client = is_user_logged_in() ? $my_mdjm['me']->ID : 
					( !empty( $post ) ? get_post_meta( $post->ID, '_mdjm_event_client', true ) : '' );
				
				$event = !empty( $post ) ? $post->ID : '';
				
				$the_text = $mdjm->filter_content( $client, $event, ( !empty( $mdjm_settings['custom_text'][$key] ) 
					? '<p>' . $mdjm_settings['custom_text'][$key] . '</p>' : '' ) );
				
				return nl2br( $the_text ) . "\r\n";
			} // custom_text
			
			/**
			 * Print out the text for the page from the given arguments
			 *
			 * @param:		str		$section		Required:	The section for which we are printing
			 *						$default_text	Optional:	The default text to be displayed if custom text is not enabled/set
			 *										If empty str provided, log error and print custom text
			 * 
			 */
			public function __text( $section, $default_text='' )	{
				global $mdjm, $mdjm_settings;
				
				if( empty( $section ) )	{
					if( MDJM_DEBUG == true )
						MDJM()->debug->log_it( 'ERROR: No page section was parsed. ' . __METHOD__, true );
						
					wp_die( 'An error has occured. Please <a href="mailto:' . $mdjm_settings['email']['system_email'] . '">' . 
						'Contact Us</a> for assistance' );	
				}
									
				$text = ( MDJM_CUSTOM_TEXT == true || empty( $default_text ) 
					? $this->custom_text( $section )
					: $default_text );
					
				return $text;
			} // __text
		} // class
	} // if( !class_exists( 'ClientZone' ) )