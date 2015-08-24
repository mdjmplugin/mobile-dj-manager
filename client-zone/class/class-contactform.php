<?php
/*
 * class-clientzone.php
 * 08/04/2015
 * @since 1.1.3
 * The ClientZone class
 * Also acts as the controller for all front end activity
 */
	
	/* -- Build the ClientZone class -- */
	if( !class_exists( 'MDJM_ContactForm' ) )	{
		class MDJM_ContactForm	{
		 /*
		  * __construct
		  * defines the params used within the class
		  *
		  *
		  */
			public function __construct( $args='' )	{
				$this->create_event = false;
				$this->create_user = false;
				/* -- Retrieve the form to be used within the class -- */
				$this->form = get_page_by_path( $args['slug'], '', MDJM_CONTACT_FORM_POSTS );
				$this->form_config = get_post_meta( $this->form->ID, '_mdjm_contact_form_config', true );
				
				/* -- Retrieve the form fields -- */
				$this->form_fields = get_posts( array(
											'posts_per_page'	=> -1,
											'post_type'		 => MDJM_CONTACT_FIELD_POSTS,
											'post_parent'	   => $this->form->ID,
											'post_status'  	   => 'publish',
											'orderby'		   => 'menu_order',
											'order'			 => 'ASC',
											) );				
				
				/* -- Determine the layout for the form -- */
				// Default layout
				$layout = 0;
				
				// Layout set by arguments or parameters
				$this->layout = substr( ( isset( $args['layout'] ) && !empty( $args['layout'] ) ? $args['layout'] : 
					( !empty( $this->form_config['layout'] ) ? $this->form_config['layout'] : $layout ) ), 0, 1 );
				
				/* -- Form submitted -- */
				if( isset( $_POST['mdjm_contact_form_submission'] ) && $_POST['mdjm_contact_form_submission'] == 'submitted' )
					$submission = $this->submit_form();
				
				/* -- Display the form to the end user -- */
				$this->show_form();
												
			} // __construct
			
			/*
			 * Get the field data
			 *
			 * @param	int		$field_id		The post ID of the field
			 * @return	arr		$field			Object Array of field post data
			 */
			public function field_data( $field_id )	{
				
				$field = get_post( $field_id );
				
				return $field;
				
			} // field_data
			
			/*
			 * Get the field configuration
			 *
			 * @param	int		$field_id		The post ID of the field
			 * @return	arr		$field_config	Array of field meta data
			 */
			public function field_settings( $field_id )	{
				
				$field_config = get_post_meta( $field_id, '_mdjm_field_config', true );
				
				return $field_config;
				
			} // field_settings
			
			/*
			 * Write the header content for the contact form
			 *
			 *
			 *
			 */
			public function form_header()	{
				global $mdjm_settings;
				
				wp_enqueue_script( 'jquery-ui-datepicker' );
				wp_enqueue_style( 'jquery-ui-css', 'http://ajax.googleapis.com/ajax/libs/jqueryui/1.8.2/themes/smoothness/jquery-ui.css' );
				?>
				<style>
				.mdjm-form-error {
					color: <?php echo( !empty( $this->form_config['error_text_color'] ) ? $this->form_config['error_text_color'] : '#FF0000' ); ?>;
				}
				input.mdjm-form-error {
					border: solid 1px #FF0000;
					color: #000000;
				}
				.mdjm-form-valid {
					color: #000000;
				}
				</style>
				<script type="text/javascript">
				<?php mdjm_jquery_datepicker_script( array(  ) ); ?>
				</script>
				<?php
				/* -- Availability Check -- */
				if( isset( $_GET['mdjm_avail'], $_GET['mdjm_avail_date'] ) && $_GET['mdjm_avail'] == 1 )	{
					if( !empty( $mdjm_settings['availability']['availability_check_pass_text'] ) )	{	
						$search = array( '{EVENT_DATE}', '{EVENT_DATE_SHORT}' );
						$replace = array( date( 'l, jS F Y', strtotime( $_GET['mdjm_avail_date'] ) ), date( MDJM_SHORTDATE_FORMAT, strtotime( $_GET['mdjm_avail_date'] ) ) );
						echo '<p>' . 
						nl2br( html_entity_decode( stripcslashes( str_replace( $search, $replace, $mdjm_settings['availability']['availability_check_pass_text'] ) ) ) ) . 
						'</p>' . "\r\n";
					}
				}
			} // form_header
			
			/*
			* form_validation
			* Validation for the contact form
			*
			*
			*/
			public function form_validation()	{
				
				// Create an array of required fields
				$is_required = array();			
				foreach( $this->form_fields as $field )	{
					$field = $this->field_data( $field->ID );
					$field_settings = $this->field_settings( $field->ID );
					$contact_fields[$field->ID] = array( 
													'id'		=> $field->ID,
													'slug'	  => $field->post_name,
													'name'	  => $field->post_title,
													'type'	  => $field_settings['type']
													);
					if( isset( $field_settings['config']['required'] ) && $field_settings['config']['required'] == 'Y' )
						$is_required[] = $field->ID;
				}
				?>
				<script type="text/javascript">
				jQuery(document).ready(function($){
					// Configure the field validator
					$('#<?php echo 'mdjm-' . $this->form->post_name; ?>').validate(
						{
							rules:
							{
								<?php
								/*foreach( $this->form_fields as $field )	{
									$field = $this->field_data( $field->ID );
									$field_settings = $this->field_settings( $field->ID );
									if( isset( $field_settings['config']['required'] ) && $field_settings['config']['required'] == 'Y' )	{
										echo '"' . $field->post_name . '":' . "\n";
										echo '{' . "\n";
										echo 'required: true';
										if( $field_settings['type'] == 'email' || $field_settings['type'] == 'url' )	{
											echo ',' . "\n" . $field_settings['type'] . ': true' . "\n";
										}
										echo '}' . ( $i < $required_fields ? ',' : '' ) . "\n";
									}
								}*/
								$i = 1;
								foreach( $contact_fields as $contact_field )	{
									if( in_array( $contact_field['id'], $is_required ) )	{
										echo '"' . $contact_field['slug'] . '":' . "\r\n";
										echo '{' . "\r\n";
										echo 'required: true';
										if( $contact_field['type'] == 'email' || $contact_field['type'] == 'url' )
											echo ',' . "\n" . $contact_field['type'] . ': true' . "\r\n";
										echo '}' . ( $i < count( $is_required ) ? ',' : '' ) . "\r\n";
										$i++;
									}
								}
								?>	
							}, // End rules
							messages:
							{
								<?php
								$i = 1;
								foreach( $contact_fields as $contact_field )	{
									if( in_array( $contact_field['id'], $is_required ) )	{
										echo '"' . $contact_field['slug'] . '":' . "\n";
										echo '{' . "\n";
										echo 'required: " ' . str_replace( '{FIELD_NAME}', $contact_field['name'], $this->form_config['required_field_text'] ) . '"' .  "\r\n";
										if( $contact_field['type'] == 'email' )
											echo ',' . "\r\n" . $contact_field['type'] . ': "Please enter a valid email address"' . "\r\n";
										
										if( $contact_field['type'] == 'url' )
											echo ',' . "\r\n" . $contact_field['type'] . ': "Please enter a valid URL"' . "\r\n";
										
										echo '}' . ( $i < count( $is_required ) ? ',' : '' ) . "\r\n";
										$i++;
									}
								}
								/*foreach( $this->form_fields as $field )	{
									$field = $this->field_data( $field->ID );
									$field_settings = $this->field_settings( $field->ID );
									if( isset( $field_settings['config']['required'] ) && $field_settings['config']['required'] == 'Y' )	{
										echo '"' . $field->post_name . '":' . "\n";
										echo '{' . "\n";
										echo 'required: " ' . str_replace( '{FIELD_NAME}', $field->post_title, $this->form_config['required_field_text'] ) . '",' .  "\n";
										if( $field_settings['type'] == 'email' )	{
											echo $field_settings['type'] . ': "Please enter a valid email address",' . "\n";
										}
										if( $field_settings['type'] == 'url' )	{
											echo $field_settings['type'] . ': "Please enter a valid URL",' . "\n";
										}
										echo '},' . "\n";	
									}
								}*/
								?>	
							}, // End messages
							
							// Classes
							errorClass: "mdjm-form-error",
							validClass: "mdjm-form-valid",
							focusInvalid: false
							
						} // End validate
					); // Close validate
				});
				</script>
				<?php	
			} // jq_validate
			
			/*
			 * Handle Contact Form Submission
			 *
			 *
			 *
			 */
			public function submit_form()	{
				global $mdjm;
				
				if( MDJM_DEBUG == true )
					$mdjm->debug_logger( 'Contact Form Submission', true );
				
				/* -- Grab & Format the date -- */
				if( !empty( $_POST['_mdjm_event_date'] ) )
					$this->event_date = $_POST['_mdjm_event_date'];
				
								
				/* -- Start Email Headers -- */
				$this->admin_email_headers = array();
				$this->admin_email_headers[] = 'MIME-Version: 1.0';
				$this->admin_email_headers[] = 'Content-type: text/html; charset=UTF-8';
				$this->admin_email_headers[] = 'From: ' . $this->form_config['email_from_name'] . ' <' . $this->form_config['email_from'] . '>';
				
				/* -- Start Email Content -- */
				$this->admin_email_body = '<html><body>';
				
				/* -- Perform validation, set Mappings & email content -- */
				foreach( $this->form_fields as $field )	{
					$field_data = $this->field_data( $field->ID );
					$field_settings = $this->field_settings( $field->ID );
					
					// Perform validation
					$this->validate( $field_data, $field_settings );
					// Perform mappings
					$this->prepare_field_mappings( $field_data, $field_settings );
					// Email Content
					$this->content_email( $field_data, $field_settings );
				}
				
				/* End the Email Content */
				$this->admin_email_body .= '<p><a href="'. admin_url( 'edit.php?post_status=mdjm-unattended&post_type=mdjm-event' ) . 
					'">View Your Outstanding Enquiries</a></p>' . "\r\n" . 
					'<hr />' . "\r\n" . 
					'<p style="font-size:10px;">Form submitted at ' . date( MDJM_TIME_FORMAT ) . ' on ' . date( 'l jS F Y' ) . '</p>' . "\r\n" . 
					'<p align="center" style="font-size:9px; color:#F90">Powered by <a style="font-size:9px; color:#F90" href="http://www.mydjplanner.co.uk" target="_blank">' . 
					MDJM_COMPANY . '</a> version ' . MDJM_VERSION_NUM . '</p>' . "\r\n" . 
					'</body></html>' . "\r\n";
				
				/* -- Email form input to admin (and client if set) -- */
				if( wp_mail( $this->form_config['email_to'], $this->form_config['email_subject'], $this->admin_email_body, $this->admin_email_headers ) )	{
					
					/* -- User Actions -- */
					if( !empty( $this->create_user ) && !empty( $this->user_meta ) && !empty( $this->client_email ) )
						$this->user_id = email_exists( $this->client_email ); // Does this user exist? If so, store their ID
						
					$this->manage_user(); // Update or create the user
						
					/* -- Event Enquiry Creation -- */
					if( isset( $this->form_config['create_enquiry'], $this->event_update, $_POST['_mdjm_event_date'], $this->user_id ) && 
						$this->form_config['create_enquiry'] == 'Y' )	{
						
						if( MDJM_DEBUG == true )
							$mdjm->debug_logger( 'Configured to create Enquiry' );
						$this->create_enquiry();
					}
					else	{
						if( MDJM_DEBUG == true )
							$mdjm->debug_logger( 'Not Configured to create Enquiry' );	
					}
					
					/* -- Form Content to client if configured -- */
					if( isset( $this->form_config['copy_sender'], $this->client_form_detail ) && $this->form_config['copy_sender'] == 'Y' )	{
						$mdjm->send_email( array( 
									'content'	=> !empty( $this->form_config['send_template'] ) ? $this->form_config['send_template'] : $this->client_form_detail,
									'to'		 => $this->user_id,
									'subject'	=> !empty( $this->form_config['send_template'] ) ? '' : 'Your message to ' . MDJM_COMPANY,
									'event_id'   => !empty( $this->event_id ) ? $this->event_id : '',
									'journal'	=> true,
									'html'	   => true,
									'cc_dj'	  => false,
									'cc_admin'   => false,
									'source'	 => 'Contact Form Submission',
								) );	
					}
					
					/* -- Success, redirect user -- */
					if( !empty( $this->form_config['redirect'] ) && $this->form_config['redirect'] != 'no_redirect' )	{
						wp_redirect( $mdjm->get_link( $this->form_config['redirect'] ) );
						exit;
					}
					/* -- Success, no redirect, display message -- */
					else	{
						/* Application Shortcodes */
						$search = array( '{ADMIN_URL}', '{APPLICATION_HOME}', '{APPLICATION_NAME}', '{COMPANY_NAME}', '{WEBSITE_URL}' );
						$replace = array( admin_url(), home_url(), MDJM_APP, MDJM_COMPANY, home_url() );
						
						echo '<p>' . nl2br( html_entity_decode( stripcslashes( str_replace( $search, $replace, $this->form_config['display_message_text'] ) ) ) ) . '</p>';
					}
					
				} // if( wp_mail(...)
				
			}  // submit_form
			
			/*
			 * PHP & CAPTCHA validation for the submitted form
			 *
			 * @param		arr		$field			The object array of the field post
			 * 				arr		$field_settings	The object array of the field post meta
			 *
			 */
			public function validate( $field, $field_settings )	{
				if( $field_settings['type'] == 'captcha' )	{
					if( !isset( $_POST['mdjm_captcha_prefix'] ) || empty( $_POST['mdjm_captcha_prefix'] ) )	{
						$captcha_success = false;
					}
					else	{
						$captcha_instance = new ReallySimpleCaptcha();
						$captcha_success = $captcha_instance->check( $_POST['mdjm_captcha_prefix'], $_POST[$field->post_name] );
						if( $captcha_success === false )	{
							$captcha_instance->remove( $_POST['mdjm_captcha_prefix'] );
							?>
							<script type="text/javascript">
							alert("Invalid value entered for <?php echo $field->post_title; ?> entered. Please try again.");
							history.back();
							</script>
							<?php
							exit;
						}
						else	{
							$captcha_instance->remove( $_POST['mdjm_captcha_prefix'] );
						}	
					}
				}
			} // validate
			
			/*
			 * Map fields to users & events as configured
			 *
			 * @param		arr		$field			The object array of the field post
			 * 				arr		$field_settings	The object array of the field post meta
			 *
			 */
			public function prepare_field_mappings( $field, $field_settings )	{
				$client_fields = array( 'first_name', 'last_name', 'user_email', 'phone1', 'user_pass' );
				
				$custom_client_fields = get_option( MDJM_CLIENT_FIELDS );
				foreach( $custom_client_fields as $custom_client_field )	{
					if( $custom_client_field['display'] == 'Y' )	{
						$client_fields[] = $custom_client_field['id'];
					}
				}
				
				$event_fields = array( '_mdjm_event_date', 
									   'mdjm_event_type',
									   '_mdjm_event_package',
									   '_mdjm_event_addons',
									   '_mdjm_event_start',
									   '_mdjm_event_finish',
									   '_mdjm_event_notes',
									   '_mdjm_event_venue_id',
									   '_mdjm_event_venue_name',
									   '_mdjm_event_venue_town',
									   '_mdjm_event_venue_county' );
									   
				if( !empty( $field_settings['config']['mapping'] ) && !empty( $_POST[$field->post_name] ) )	{
					/* Client Mappings */
					if( in_array( $field_settings['config']['mapping'], $client_fields ) )	{
						$this->create_user = true;
						if( !isset( $this->user_meta ) || !is_array( $this->user_meta ) )
							$this->user_meta = array();

						$this->user_meta[$field_settings['config']['mapping']] = $_POST[$field->post_name];
					}
					
					/* -- Event Mappings -- */
					if( in_array( $field_settings['config']['mapping'], $event_fields ) )	{
						
						$this->create_event = true;
						
						if( !isset( $this->event_update ) || !is_array( $this->event_update ) )
							$this->event_update = array();
												
						if( $field_settings['type'] == 'time' )	{
							$this->event_update[$field_settings['config']['mapping']] = ( MDJM_TIME_FORMAT == 'H:i' ? 
								date( 'H:i:s', strtotime( $_POST[$field->post_name . '_hr'] . ':' . $_POST[$field->post_name . '_min'] ) ) : 
								date( 'H:i:s', strtotime( $_POST[$field->post_name . '_hr'] . ':' . $_POST[$field->post_name . '_min'] . isset( $_POST[$field->post_name . '_period'] ) 
									? $_POST[$field->post_name . '_period'] : '' ) ) );
						}
						else	{
							$this->event_update[$field_settings['config']['mapping']] = $_POST[$field->post_name];	
						}
					}
				}
			} // prepare_field_mappings
			
			/*
			 * Prepare the form content as an email to the MDJM Admin
			 *
			 * @param		arr		$field			The object array of the field post
			 * 				arr		$field_settings	The object array of the field post meta
			 *
			 */
			public function content_email( $field, $field_settings )	{
				
				/* -- Final Email Headers -- */
				if( $field_settings['type'] == 'email' )	{
					if( isset( $this->form_config['reply_to'] ) && $this->form_config['reply_to'] == 'Y' )	{
						$this->admin_email_headers[] = 'Reply-To: ' . $_POST[$field->post_name];
						$this->client_email = $_POST[$field->post_name];
					}
				}
				
				/* -- More Email Content -- */
				if( $field_settings['type'] != 'submit' && $field_settings['type'] != 'captcha' && !empty( $_POST[$field->post_name] ) )	{
					
					/* DJ Availability Check */
					if( $field_settings['type'] == 'date' && isset( $field_settings['config']['datepicker'] ) && $field_settings['config']['datepicker'] == 'Y' )	{
						$dj_avail = dj_available( '', $_POST['_mdjm_event_date'] );
						if( !empty( $dj_avail['available'] ) )	{
							$avail_message = count( $dj_avail['available'] ) . ' ' . MDJM_DJ . _n( '', '\'s', $dj_avail['available'] ) . ' available';	
						}
						else	{
							$avail_message = 'No DJ\'s available';	
						}
					}
					
					/* -- Time formatting -- */
					if( $field_settings['type'] == 'time' )	{
						if( MDJM_TIME_FORMAT == 'H:i' )	{
							$time = $_POST[$field->post_name . '_hr'] . ':' . $_POST[$field->post_name . '_min'];
							$_POST[$field->post_name] = date( 'H:i:s', strtotime( $time ) );						
						}
						else	{
							$time = $_POST[$field->post_name . '_hr'] . ':' . $_POST[$field->post_name . '_min'] . $_POST[$field->post_name . '_period'];
							$_POST[$field->post_name] = date( 'g:i A', strtotime( $time ) );					
						}
					}
					
					/* -- Event Type -- */
					if( $field_settings['type'] == 'event_list' && $_POST[$field->post_name] != '0' )	{
						$term = get_term( $_POST[$field->post_name], 'event-types' );
						$_POST[$field->post_name] = $term->name;
					}
					/* -- Addons -- */
					if( $field_settings['type'] == 'addons_list' )	{
						$_POST[$field->post_name] = implode( "\n", $_POST[$field->post_name] );	
					}
					/* -- Venue -- */
					if( $field_settings['type'] == 'venue_list' )	{
						if( !empty( $_POST[$field->post_name] ) && $_POST[$field->post_name] == '0' )	{
							$venue = ( !empty( $_POST[$field->post_name] ) ? 
								$GLOBALS['mdjm']->mdjm_events->mdjm_get_venue_details( $_POST[$field->post_name] ) : 'Not specified' );
							
							$_POST[$field->post_name] = ( $venue != 'Not specified' ? 
								( !empty( $venue['name'] ) ? stripslashes( $venue['name'] ) . '<br />' : '' ) . 
								( !empty( $venue['venue_address1'] ) ? stripslashes( $venue['venue_address1'] ) . '<br />' : '' ) . 
								( !empty( $venue['venue_address2'] ) ? stripslashes( $venue['venue_address2'] ) . '<br />' : '' ) . 
								( !empty( $venue['venue_town'] ) ? stripslashes( $venue['venue_town'] ) . '<br />' : '' ) . 
								( !empty( $venue['venue_county'] ) ? stripslashes( $venue['venue_county'] ) . '<br />' : '' ) . 
								( !empty( $venue['venue_postcode'] ) ? stripslashes( $venue['venue_postcode'] ) . '<br />' : '' ) : $venue );
						}
						else	{
							unset( $_POST[$field->post_name] );
						}
					}
					
					/* -- Finalise the email content -- */
					$this->admin_email_body .= '<p><span style="font-weight:bold">' . $field->post_title . '</span><br />';
					$this->admin_email_body .= nl2br( html_entity_decode( stripcslashes( $_POST[$field->post_name] ) ) );
					
					/* -- Client see's no more -- */
					$this->client_form_detail = $this->admin_email_body;
					
					/* -- Add availability result to email content -- */
					if( $field_settings['type'] == 'date' && isset( $avail_message ) && !empty( $avail_message ) )
						$this->admin_email_body .= ' (' . $avail_message . ')';
				}
			} // content_email
			
			/*
			 * Manage user updates & creation
			 *
			 * @param		
			 * 
			 *
			 */
			public function manage_user()	{
				global $mdjm_settings;
				
				/* -- Existing users should have their data updated where possible -- */
				if( !empty( $this->user_id ) )	{
					$client_field_array = array( 'ID' => $this->user_id );
					if( isset( $this->form_config['update_user'], $this->form_config['create_enquiry'] ) 
						&& $this->form_config['update_user'] == 'Y' && $this->form_config['create_enquiry'] == 'Y' )	{
							
						$userdata = get_userdata( $this->user_id ); // Get the users data
						
						/* -- Loop through fields and set user first, last & display names -- */
						foreach( $this->user_meta as $key => $value )	{
							if( $key == 'first_name' && $value != $userdata->first_name )	{
								$client_field_array['user_nicename'] = ucfirst( sanitize_text_field( $value ) );
								$client_field_array['display_name'] = ucfirst( sanitize_text_field( $value ) );
							}
							if( $key == 'last_name' && $value != $userdata->last_name )	{
								if( isset( $client_field_array['display_name'] ) )	{
									$client_field_array['last_name'] = ucfirst( sanitize_text_field( $value ) );
									$client_field_array['display_name'] = $client_field_array['first_name'] . ' ' . $client_field_array['last_name'];
								}
							}
							if( $key == 'phone1' && $value != $userdata->phone1 )
								update_user_meta( $this->user_id, $key, $value );	
							
							if( $key == 'marketing' && $value != $userdata->marketing )
								update_user_meta( $this->user_id, $key, $value );	
							
							$client_field_array[$key] = $value;
						} // foreach( $this->user_meta as $key => $value )	
						
						/* -- Now update the user details -- */
						wp_update_user( $client_field_array );
						
					} // if( isset( $this->form_config['update_user'], $this->form_config['create_enquiry'] )
				} // if( !empty( $this->user_id ) )	
				
				/* -- We need to create the user, but only if we are creating an event enquiry -- */
				else	{
					if( isset( $this->form_config['create_enquiry'] ) && $this->form_config['create_enquiry'] == 'Y' )	{
						$this->user_id = username_exists( $this->client_email ); // Check if username exists
						if( !$this->user_id && $this->user_id == false )	{
							// Generate a password to user
							$random_password = wp_generate_password( $mdjm_settings['clientzone']['pass_length'] );
							
							// Create a new user and store their ID
							$this->user_id = wp_create_user( $this->client_email, $random_password, $this->client_email );
							
							// Begin setting user data for updates
							$client_field_array = array( 'ID' => $this->user_id, 'role' => 'client', 'show_admin_bar_front' => 'false' );
							
							update_user_meta( $this->user_id, 'marketing', 'Y' ); // Marketing set to yes as default
							
							/* -- Loop through user data and prepare updates -- */
							foreach( $this->user_meta as $key => $value )	{
								if( $key == 'first_name' )	{
									$value = ucfirst( $value );
									$client_field_array['user_nicename'] = sanitize_text_field( $value );
									$client_field_array['display_name'] = sanitize_text_field( $value );
								}
								if( $key == 'last_name' )	{
									$value = ucfirst( $value );
									if( isset( $client_field_array['display_name'] ) )	{
										$client_field_array['display_name'] = $client_field_array['display_name'] . ' ' . sanitize_text_field( $value );
									}
								}
								if( $key == 'phone1' )
									update_user_meta( $this->user_id, $key, $value );
										
								if( $key == 'marketing' )
									update_user_meta( $this->user_id, $key, $value );	
								
								$client_field_array[$key] = $value;
							}
							
							/* -- Update the user -- */
							wp_update_user( $client_field_array );
						} // if( !$this->user_id && $user_exists == false )
					} // if( isset( $this->form_config['create_enquiry'] )
				} // else
			} // manage_user
			
			/*
			 * Create an unattended enquiry from the Contact Form data received
			 *
			 * @param		
			 * 
			 *
			 */
			public function create_enquiry()	{
				global $mdjm, $mdjm_posts;
				
				if( MDJM_DEBUG == true )
					$mdjm->debug_logger( 'Creating Unattended Enquiry from Contact Form Submission' );
				
				if( empty( $this->event_update ) )	{
					if( MDJM_DEBUG == true )
						$mdjm->debug_logger( '	-- No updates passed for event data' );
					return;	
				}
				
				// Set initial event cost
				$event_cost = 0;
				
				/* -- Event Date -- */
				if( !empty( $this->event_date ) )
					$this->event_update['_mdjm_event_date'] = $this->event_date;
					
				/* -- Set the DJ if we are not in Multi DJ Mode -- */
				if( MDJM_MULTI != true )
					$this->event_update['_mdjm_event_dj'] = 1;
				
				/* -- Remove the Save Post action hook -- */
				remove_action( 'save_post', array( $mdjm_posts, 'save_custom_post' ), 10, 2 );
				
				/* -- Set additional meta fields -- */
				$this->event_update['_mdjm_event_client'] = $this->user_id;
				$this->event_update['_mdjm_event_last_updated_by'] = $this->user_id;
				$this->event_update['_mdjm_event_enquiry_source'] = 'Website';
				$this->event_update['_mdjm_playlist_access'] = $mdjm->mdjm_events->playlist_ref();
				
				/* -- Create default post (auto-draft) so we can use the ID etc -- */
				require_once( ABSPATH . 'wp-admin/includes/post.php' );
				$event_post = get_default_post_to_edit( MDJM_EVENT_POSTS, true );
				
				$this->event_id = $event_post->ID;
				
				/* -- Set the post data -- */
				$event_post->post_title = MDJM_EVENT_PREFIX . $this->event_id; // Event ID
				$event_post->post_name = $event_post->post_title; // Event ID
				$event_post->post_author = 1; // Author
				$event_post->post_status = 'mdjm-unattended'; // Event Status
				
				/* -- Update the post with the new data -- */
				wp_update_post( $event_post );
				
				/* -- Now loop through the field updates to add post meta and set Event Type -- */
				if( MDJM_DEBUG == true )
					 $mdjm->debug_logger( '	-- Beginning Meta Updates' );
				foreach( $this->event_update as $meta_key => $meta_value )	{
										
					// Set the event type
					if( $meta_key == 'mdjm_event_type' )	{
						if( MDJM_DEBUG == true )
							 $mdjm->debug_logger( '	-- Assigning Event Type' );
						wp_set_post_terms( $this->event_id, $meta_value, 'event-types' );
						add_post_meta( $this->event_id, '_mdjm_event_name', get_term( $meta_value, 'event-types' )->name, true );
					}
					
					// Add the post meta
					else	{
						add_post_meta( $this->event_id, $meta_key, $meta_value, true );
						
						// If we have an event package and/or addons, set the event cost
						if( $meta_key == '_mdjm_event_package' )	{
							$packages = get_option( 'mdjm_packages' );
							
							$event_cost += $packages[$meta_value]['cost'];
							if( MDJM_DEBUG == true )
								$mdjm->debug_logger( '	-- Cost of ' . $meta_value . ' package is ' . $packages[$meta_value]['cost'] );
						}
						if( $meta_key == '_mdjm_event_addons' )	{
							foreach( $meta_value as $addon )	{
								$equipment = get_option( 'mdjm_equipment' );
								$event_cost += $equipment[$addon][7];
								if( MDJM_DEBUG == true )
									$mdjm->debug_logger( '	-- Cost of ' . $addon . ' addon is ' . $equipment[$addon][7] );
							}
						}
					}
				}
				// Apply the cost of packages and addons if we have it
				if( $event_cost != 0 )	{
					add_post_meta( $this->event_id, 
								   '_mdjm_event_cost', 
								   number_format( $event_cost, 2 ), true );
					if( MDJM_DEBUG == true )
							$mdjm->debug_logger( '	-- Total Event Cost is ' . $event_cost );
				}
				if( MDJM_DEBUG == true )
					$mdjm->debug_logger( '	-- Meta Updates Completed' );
				
				if( MDJM_DEBUG == true )
					$mdjm->debug_logger( 'Finished Creating Unattended Enquiry from Contact Form Submission. Event ID ' . $this->event_id );
				
				/* -- Add a Journal Entry -- */
				if( MDJM_JOURNAL == true )	{
					if( MDJM_DEBUG == true )
						$mdjm->debug_logger( '	-- Adding journal entry' );
						
						$mdjm->mdjm_events->add_journal( array(
									'user' 			=> $this->user_id,
									'event'		   => $this->event_id,
									'comment_content' => 'Event created via Contact Form submission (' . $this->form->post_title . ')',
									'comment_type' 	=> 'mdjm-journal',
									),
									array(
										'type' 		  => 'create-event',
										'visibility'	=> '1',
									) );
				}
				else	{
					if( MDJM_DEBUG == true )
						$mdjm->debug_logger( '	-- Journalling is disabled' );	
				}
								
				/* -- Add the Save Post action hook -- */
				add_action( 'save_post', array( $mdjm_posts, 'save_custom_post' ), 10, 2 );
			} // create_enquiry
			
			/*
			 * Display the contact form content
			 *
			 *
			 *
			 */
			public function show_form()	{
				global $mdjm, $mdjm_settings;
				
				// If this counter reaches 2 then we need the Addons to dynamically update
				$packages = 0;
				
				$this->form_header();
				
				$columns = $this->layout;
				$i = 0;
								
				echo "\r\n" . '<!-- Start of MDJM Contact Form -->' . "\r\n" . 
				'<form name="mdjm-' . $this->form->post_name . '" id="mdjm-' . $this->form->post_name . '" method="post">' . "\r\n" . 
				'<input type="hidden" name="mdjm_contact_form_submission" id="mdjm_contact_form_submission" value="submitted" />' . "\r\n" . 
				'<input type="hidden" name="mdjm_contact_form" id="mdjm_contact_form" value="' . $this->form->ID . '" />';
				
				/* -- Start table layout if required -- */
				if( $this->layout != 0 )
					echo '<table width="100%" border="0" cellspacing="0" cellpadding="0">' . "\r\n";
				
				/* -- Begin field loop for display -- */
				foreach( $this->form_fields as $field )	{
					/* -- Get the field data -- */
					$field = $this->field_data( $field->ID );
					$field_settings = $this->field_settings( $field->ID );
					
				/* -- Start the layout -- */
					// No table
					if( $this->layout == 0 )	{
						echo '<p' . 
						( !empty( $field_settings['config']['label_class'] ) ? ' class="' . $field_settings['config']['label_class'] . '"' : '' ) . 
						'>';
					}
					// Table needs a row
					elseif ( $i == 0 )	{
						echo '<tr>' . "\r\n";
					}
					
					// Start the Table cell if needed
					if( $this->layout != 0 )	{
						echo '<td';
						
						echo ( !empty( $field_settings['config']['row_height'] ) ? ' height="' . $field_settings['config']['row_height'] . '"' : '' );
						echo ( !empty( $field_settings['config']['label_class'] ) ? ' class="' . $field_settings['config']['label_class'] . '"' : '' ); 
						
						echo '>';
					}
					
				/* -- Display the field label -- */
					echo ( $field_settings['type'] != 'submit' ? '<label for="' . $field->post_name . '">' . esc_attr( $field->post_title ) . '</label>' : '' ) . 
					( !empty( $this->form_config['required_asterix'] ) 
						&& !empty( $field_settings['config']['required'] ) 
						&& $field_settings['type'] != 'submit' ? '<span style="color: red; font-weight: bold;">*</span>' : '' );
	
					echo ( $this->layout == 0 && $field_settings['type'] != 'submit' ) ? "\r\n" . '<br />' . "\r\n" : '';
					
					$i++;
				
				/* -- Display the input field -- */
					echo ( $this->layout != 0 ? '<td>' : '' ); // Table cell
				
				/* Text / Email / Telephone Fields / URL */
					if( $field_settings['type'] == 'text' || $field_settings['type'] == 'email' || $field_settings['type'] == 'tel' || $field_settings['type'] == 'url' )	{
						echo '<input type="' . $field_settings['type'] . '" name="' . $field->post_name . '" id="' . $field->post_name . '"' . 
						
						( !empty( $field_settings['config']['placeholder'] ) ? ' placeholder="' . esc_attr( $field_settings['config']['placeholder'] ) . '"' : '' ) . 
						
						( !empty( $field_settings['config']['input_class'] ) ? ' class="' . esc_attr( $field_settings['config']['input_class'] ) . '"' : '' ) . 
						
						( !empty( $field_settings['config']['width'] ) ? ' size="' . $field_settings['config']['width'] . '"' : '' ) . 
						
						( !empty( $field_settings['config']['required'] ) && $field_settings['config']['required'] == 'Y' ? ' required' : '' ) . 
						
						' />';
					} // Text / Email / Telephone / URL
				
				/* Date Field */
					elseif( $field_settings['type'] == 'date' )	{
						echo '<input type="text" name="' . $field->post_name . '" id="' . $field->post_name . '"';
						
						if( isset( $field_settings['config']['datepicker'] ) && $field_settings['config']['datepicker'] == 'Y' )	{
							echo ' class="mdjm_date"';
						}
						elseif( !empty( $field_settings['config']['input_class'] ) )	{
							echo ' class="' . esc_attr( $field_settings['config']['class'] ) . '"';
						}
						
						echo ( isset( $field_settings['config']['mapping'], $_GET['mdjm_avail_date'] ) && $field_settings['config']['mapping'] == 'event_date' ? 
							' value="' . date( MDJM_SHORTDATE_FORMAT, strtotime( $_GET['mdjm_avail_date'] ) ) . '"' : '' ) . 
						
						( isset( $field_settings['config']['required'] ) && $field_settings['config']['required'] == 'Y' ? ' required' : '' ) . 
						
						' />' . "\r\n";
						
						if( isset( $field_settings['config']['datepicker'] ) && $field_settings['config']['datepicker'] == 'Y' )
							echo '<input type="hidden" name="_mdjm_event_date" id="_mdjm_event_date" value="" />' . "\n";
					} // Date Field
					
				/* Time Field */
					elseif( $field_settings['type'] == 'time' )	{
						echo '<input type="hidden" name="' . $field->post_name . '" value="Y" />' . "\r\n" . 
						'<select name="' . $field->post_name . '_hr" id="' . $field->post_name . '_hr"' . 
						
						( !empty( $field_settings['config']['input_class'] ) ? ' class="' . $field_settings['config']['input_class'] . '"' : '' ) . 
						
						( isset( $field_settings['config']['required'] ) && $field_settings['config']['required'] == 'Y' ? ' required' : '' ) . 
						
						'>' . "\r\n";
						
						$minutes = array( '00', '15', '30', '45' );
						if( MDJM_TIME_FORMAT == 'H:i' )	{
							$h = '00';
							$x = '23';
						}
						else	{
							$h = '1';
							$x = '12';	
						}
						while( $h <= $x )	{
							echo '<option value="' . $h . '">' . $h . '</option>' . "\r\n";
							$h++;
						}
						echo '</select>' . "\r\n";
						
						echo '&nbsp;<select name="' . $field->post_name . '_min" id="' . $field->post_name . '_min"' . 
						
						( !empty( $field_settings['config']['input_class'] ) ? ' class="' . $field_settings['config']['input_class'] . '"' : '' ) . 
						
						( isset( $field_settings['config']['required'] ) && $field_settings['config']['required'] == 'Y' ? ' required' : '' ) . 
						
						'>' . "\r\n";
						
						foreach( $minutes as $minute )	{
							echo '<option value="' . $minute . '">' . $minute . '</option>' . "\n";
						}
						echo '</select>' . "\n";
						
						if( MDJM_TIME_FORMAT != 'H:i' )	{
							echo '&nbsp;<select name="' . $field->post_name . '_period" id="' . $field->post_name . '_period"' . 
							
							( !empty( $field_settings['config']['input_class'] ) ? ' class="' . $field_settings['config']['input_class'] . '"' : '' ) . 
							
							( isset( $field_settings['config']['required'] ) && $field['config']['required'] == 'Y' ? ' required' : '' ) . 
							
							'>' . "\r\n" . 
							'<option value="AM">AM</option>' . "\r\n" . 
							'<option value="PM">PM</option>' . "\r\n" . 
							'</select>' . "\r\n";
						}
					} // Time Field
					
				/* Select / Event / Package / Addons / Venue List Fields */
					elseif( $field_settings['type'] == 'select' 
							|| $field_settings['type'] == 'select_multi' 
							|| $field_settings['type'] == 'event_list'
							|| $field_settings['type'] == 'package_list'
							|| $field_settings['type'] == 'addons_list'
							|| $field_settings['type'] == 'venue_list' )	{
						
						if( $field_settings['type'] != 'package_list' && $field_settings['type'] != 'addons_list' )	{
							echo '<select name="' . $field->post_name . '" id="' . $field->post_name . '"' . 
							
							( $field_settings['type'] == 'select_multi' ? ' multiple="multiple"' : '' ) . 
							
							( !empty( $field_settings['config']['input_class'] ) ? ' class="' . $field_settings['config']['input_class'] . '"' : '' ) . 
							
							( isset( $field_settings['config']['required'] ) && $field_settings['config']['required'] == 'Y' ? ' required' : '' ) . 
							
							'>' . "\r\n";
						}
						
						// Event List
						if( $field_settings['type'] == 'event_list' )	{
							$event_types = $mdjm->mdjm_events->get_event_types();
							
							$first_entry = !empty( $field_settings['config']['event_list_first_entry'] ) ? $field_settings['config']['event_list_first_entry'] : '';
							
							if( !empty( $first_entry ) )
								echo '<option value="0">' . esc_attr( $first_entry ) . '</option>' . "\n";
								
							foreach( $event_types as $type )	{
								echo '<option value="' . $type->term_id . '">' . esc_attr( $type->name ) . '</option>' . "\n";
							}
						}
						// Package List
						elseif( $field_settings['type'] == 'package_list' )	{
							$selected_package = false;
														
							if( isset( $_GET['selected_package'] ) && !empty( $_GET['selected_package'] ) && MDJM_PACKAGES == true )	{
								$desired_package = $_GET['selected_package'];
								$all_packages = get_option( 'mdjm_packages' );
								foreach( $all_packages as $mdjm_package )	{
									if( $mdjm_package['name'] == $desired_package )
										$selected_package = $mdjm_package['slug'];
								}
							}
								
							elseif( !empty( $field_settings['config']['package_list_selected'] ) )
								$selected_package = $field_settings['config']['package_list_selected'];
							
							else
								$selected_package = false;							
							
							$packages++;
							$package_field = $field->post_name; // For the dynamic updating of addons
							
							$package_settings['name'] = $field->post_name;
							$package_settings['id'] = $field->post_name;
							$package_settings['class'] = ( !empty( $field_settings['config']['input_class'] ) ? 
								' class="' . $field_settings['config']['input_class'] . '"' : '' );
							$package_settings['first_entry'] = ( !empty( $field_settings['config']['package_list_first_entry'] ) ? 
								$field_settings['config']['package_list_first_entry'] : '' );
							$package_settings['title'] = true;
							$package_settings['selected'] = ( !empty( $selected_package ) ? $selected_package : '' );
							
							echo mdjm_package_dropdown( $package_settings );
						}
						// Addons List
						elseif( $field_settings['type'] == 'addons_list' )	{
							$packages++;
							$addons_field = $field->post_name; // For the dynamic updating of addons
							
							$addons_settings['name'] = $field->post_name;
							$addons_settings['id'] = $field->post_name;
							$addons_settings['class'] = ( !empty( $field_settings['config']['input_class'] ) ? 
								' class="' . $field_settings['config']['input_class'] . '"' : '' );
							$addons_settings['title'] = true;
							$addons_settings['package'] = ( !empty( $selected_package ) ? $selected_package : '' );
								
							echo mdjm_addons_dropdown( $addons_settings );
						}
						// Venue List
						elseif( $field_settings['type'] == 'venue_list' )	{
							$venues = $mdjm->mdjm_events->mdjm_get_venues();
							
							$first_entry = !empty( $field_settings['config']['venue_list_first_entry'] ) ? $field_settings['config']['venue_list_first_entry'] : '';
							
							if( !empty( $first_entry ) )
								echo '<option value="0">' . esc_attr( $first_entry ) . '</option>' . "\n";
								
							foreach( $venues as $venue )	{
								$venue_details = $mdjm->mdjm_events->mdjm_get_venue_details( $venue->ID );
								echo '<option value="' . $venue->ID . '"' . 
									( !empty( $venue_details['name'] ) || !empty( $venue_details['venue_address1'] ) || !empty( $venue_details['venue_town'] ) ? 
										' title="' . ( !empty( $venue_details['name'] ) ? $venue_details['name'] . ', ' : '' ) . 
										( !empty( $venue_details['venue_address1'] ) ? $venue_details['venue_address1'] . ', ' : '' ) . 
										( !empty( $venue_details['venue_town'] ) ? $venue_details['venue_town'] : '' ) . '"' : '' ) . 
									'>' . esc_attr( $venue_details['name'] ) .  ( !empty( $venue_details['venue_town'] ) ? 
										' (' . $venue_details['venue_town'] . ')' : '' ) . 
									'</option>' . "\n";
							}
						}
						else	{
							$options = explode( "\n", $field_settings['config']['options'] );
							foreach( $options as $option )	{
								echo '<option value="' . $option . '">' . esc_attr( $option ) . '</option>' . "\n";
							}
						}
						
						if( $field_settings['type'] != 'package_list' && $field_settings['type'] != 'addons_list' )	{
							echo '</select>' . "\n";
						}
					} // Select / Event List
				
				/* Checkbox Field */
					elseif( $field_settings['type'] == 'checkbox' )	{
						echo '<input type="checkbox" name="' . $field->post_name . '" id="' . $field->post_name . '"' . 
						
						( isset( $field_settings['config']['is_checked'] ) && $field_settings['config']['is_checked'] == 'Y' ? ' checked="checked"' : '' ) . 
						
						( !empty( $field_settings['config']['input_class'] ) ? ' class="' . $field_settings['config']['input_class'] . '"' : '' ) . 
						
						' value="' . $field_settings['config']['checked_value'] . '" />' . "\r\n";
					}
					
				/* Textarea Fields */
					if( $field_settings['type'] == 'textarea' )	{
						echo '<textarea name="' . $field->post_name . '" id="' . $field->post_name . '"' . 
						
						( !empty( $field_settings['config']['placeholder'] ) ? ' placeholder="' . $field_settings['config']['placeholder'] . '"' : '' ) . 
						
						( !empty( $field_settings['config']['width'] ) ? ' cols="' . $field_settings['config']['width'] . '"' : '' ) . 
						
						( !empty( $field_settings['config']['height'] ) ? ' rows="' . $field_settings['config']['height'] . '"' : '' ) . 
						
						( !empty( $field_settings['config']['input_class'] ) ? ' class="' . $field_settings['config']['input_class'] . '"' : '' ) . 
						
						( isset( $field_settings['config']['required'] ) && $field_settings['config']['required'] == 'Y' ? ' required' : '' ) . 
						
						'></textarea>' . "\r\n";
					}
					
				/* CAPTCHA Field */
					if( $field_settings['type'] == 'captcha' )	{
						$captcha_instance = new ReallySimpleCaptcha();
						$captcha_word = $captcha_instance->generate_random_word();
						$captcha_prefix = mt_rand();
						
						echo '<input type="text" name="' . $field->post_name . '" id="' . $field->post_name . '"' . 
						( !empty( $field_settings['config']['placeholder'] ) ? ' placeholder="' . $field_settings['config']['placeholder'] . '"' : '' ) . 
						
						( !empty( $field_settings['config']['input_class'] ) ? ' class="' . $field['config']['input_class'] . '"' : '' ) . 
						
						( isset( $field_settings['config']['required'] ) && $field_settings['config']['required'] == 'Y' ? ' required' : '' ) . 
						
						' />' . "\r\n" . '&nbsp;&nbsp;' . "\r\n" . 
						'<img src="' . plugins_url( 'really-simple-captcha/tmp/' . $captcha_instance->generate_image( $captcha_prefix, $captcha_word ) ) . '" alt="CAPTCHA" />' . "\r\n" . 
						'<input type="hidden" name="mdjm_captcha_prefix" id="mdjm_captcha_prefix" value="' . $captcha_prefix . '" />' . "\r\n";
					} // CAPTCHA
					
					/* End the column */
					echo ( $this->layout == 0 ? '</p>' . "\r\n" : '</td>' . "\r\n" );
					
					/* End of row */
					$i++;
					if( $this->layout != 0 && $i == $columns )	{
						echo '</tr>' . "\n";
						$i = 0;
					}
					/* Prepare the Submit Button */
					if( $field_settings['type'] == 'submit' )
						$submit = $field_settings;
				} // foreach( $form_fields as $field )
				
				// Add dynamic updating of addons if needed
				if( $packages == 2 )
					$this->dynamic_addons( $package_field, $addons_field );
				
				/* -- Excessive Columns -- */
				if( $this->layout != 0 && $i != 0 )	{
					 while( $i < $columns ) {
						  echo '<td>&nbsp;</td>' . "\n";
						  $i++;
					 }
					 echo '</tr>' . "\n";
				}
				
				/* -- Display the Submit Button -- */
				/* Submit Button */
				if( !empty( $submit ) )	{
					if( $this->layout == 0 )	{
						echo '<p' . 
						( !empty( $submit['config']['submit_align'] ) ? ' align="' . $submit['config']['submit_align'] . '"' : '' ) . 
						
						'>';
					}
					else	{
						echo '<tr>' . "\r\n" . 
						'<td colspan="' . $this->layout . '"' . 
						( !empty( $submit['config']['submit_align'] ) ? ' align="' . $submit['config']['submit_align'] . '"' : '' ) . 	
						
						( !empty( $submit['config']['row_height'] ) ? ' height="' . $submit['config']['row_height'] . '"' : '' ) . 	
						
						'>';	
					}
					
					echo '<input type="submit" name="' . $field->post_name . '" id="' . $field->post_name . '" value="' . esc_attr( $field->post_title ) . '"' . 
					( !empty( $submit['config']['input_class'] ) ? ' class="' . $submit['config']['input_class'] . '"' : '' ) . 
					
					' />';
					
					echo ( $this->layout == 0 ) ? '</p>' . "\r\n" : '</td>' . "\r\n" . '</tr>' . "\r\n";
				}
				
				/* -- End the table -- */
				echo ( $this->layout != 0 ? '</table>' . "\r\n" : '' );
				
				/* Add the validation */
				$this->form_validation();
				
				echo '</form>' . "\r\n" . 
				'<!-- End of MDJM Contact Form -->' . "\r\n";	
			} // show_form
			
			/*
			 * Add jQuery to dynamically update the addons list
			 *
			 *
			 *
			 */
			function dynamic_addons( $package_field, $addons_field )	{
				?>
				<script type="text/javascript"> 
				jQuery(document).ready(function($) 	{
					$('#<?php echo $package_field; ?>').on('change', '', function()	{
						
						var package = $("#<?php echo $package_field; ?> option:selected").val();
						var addons = $("#<?php echo $addons_field; ?>");
						$.ajax({
							type: "POST",
							dataType: "json",
							url: "<?php echo admin_url( 'admin-ajax.php' ); ?>",
							data: {
								package : package,
								addons_field : <?php echo $addons_field; ?>,
								action : "mdjm_update_contact_form_addon_options"
							},
							beforeSend: function()	{
								$("#<?php echo $addons_field; ?>").addClass( "mdjm-updating" );
								$("#<?php echo $addons_field; ?>").fadeTo("slow", 0.5);
							},
							success: function(response)	{
								if(response.type == "success") {
									addons.empty(); // Remove existing options
									addons.append(response.addons);
									$("#<?php echo $addons_field; ?>").fadeTo("slow", 1);
									
									$("#<?php echo $addons_field; ?>").removeClass( "mdjm-updating" );
								}
								else	{
									alert(response.msg);
									$("#<?php echo $addons_field; ?>").fadeTo("slow", 1);
									$("#<?php echo $addons_field; ?>").removeClass( "mdjm-updating" );
								}
							}
						});
					});
				});
				</script>
                <?php
			} // dynamic_addons
			
		} // class
		
		}// if( !class_exists( 'MDJM_ContactForm' ) )
	
	
/* -- Insantiate the MDJM_ContactForm class -- */
	$mdjm_contactform = new MDJM_ContactForm( $atts );
	
	
	