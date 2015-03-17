<?php
/*
* contact-form.php
* 08/01/2015
* @since 1.0
* Display MDJM contact form
*/

	defined('ABSPATH') or die("Direct access to this page is disabled!!!");
	
/* Actions to take */
	if( !isset( $_POST['mdjm_contact_form_submission'] ) || $_POST['mdjm_contact_form_submission'] != 'submitted' )	{ // No submission
	
/* Get the form and field info */
		$mdjm_forms = get_option( 'mdjm_contact_forms' );
		$fields = $mdjm_forms[$atts['slug']]['fields'];

/* Determine the layout of the form */	
		if( isset( $atts['layout'] ) && !empty( $layout ) )	{ // Set with shortcode
			$layout = $atts['layout'];
			if( $layout != '4_column' && $layout = '2_column' && $layout != '0_column' )	{
				$layout = '0';	
			}
		}
		elseif( !empty( $mdjm_forms[$atts['slug']] ) )	{ // Use form config
			$layout = substr( $mdjm_forms[$atts['slug']]['config']['layout'], 0, 1 );
			if( $layout == 'not_set' )	{
				$layout = '0';	
			}
		}
		else	{
			$layout = '0'; // Default
		}

/* Display the Page */
		if( !class_exists( 'MDJM_ContactForm' ) ) {
			require_once( WPMDJM_PLUGIN_DIR . '/includes/class/class-mdjm-contact-form.php' );
		}
		$mdjm_contact_form = new MDJM_ContactForm();
		
		$mdjm_contact_form->mdjm_form_header( $mdjm_forms[$atts['slug']] );
		$mdjm_contact_form->mdjm_display_form( $layout, $mdjm_forms[$atts['slug']], $fields );
		
/* Print the credit if set */
		add_action( 'wp_footer', 'f_wpmdjm_print_credit' );
	} // if( !isset( $_POST['mdjm_contact_form_submission'] )
	
/* Form submitted */
	else	{
		if( !class_exists( 'MDJM_ContactForm' ) ) {
			require_once( WPMDJM_PLUGIN_DIR . '/includes/class/class-mdjm-contact-form.php' );
		}
		$mdjm_contact_form = new MDJM_ContactForm();
		
		if( isset( $_POST['mdjm_contact_form_submission'] ) && $_POST['mdjm_contact_form_submission'] == 'submitted' )	{
			global $mdjm_options, $wpdb;
			
			$mdjm_forms = get_option( 'mdjm_contact_forms' );
			$form = $mdjm_forms[$_POST['mdjm_contact_form_slug']];
			
			$fields = $form['fields'];
			$create_event = false;
			$create_user = false;
			
			$client_fields = array( 'first_name', 'last_name', 'user_email', 'phone1', 'user_pass' );
			$event_fields = array( 'event_date', 'event_type', 'event_start', 'event_finish', 'event_description',
								'venue', 'venue_city', 'venue_state' );
								
			/* Set the Initial Email Headers */
			$email_headers = array();
			$email_headers[] = 'MIME-Version: 1.0';
			$email_headers[] = 'Content-type: text/html; charset=UTF-8';
			$email_headers[] = 'From: ' . $form['config']['email_from_name'] . ' <' . $form['config']['email_from'] . '>';
			
			$email_body = '<html><body>';
			
			/* PHP Validation & Initial Config */
			foreach( $fields as $field )	{
				if( $field['type'] == 'captcha' )	{
					if( !isset( $_POST['mdjm_captcha_prefix'] ) || empty( $_POST['mdjm_captcha_prefix'] ) )	{
						$captcha_success = false;
					}
					else	{
						$captcha_instance = new ReallySimpleCaptcha();
						$captcha_success = $captcha_instance->check( $_POST['mdjm_captcha_prefix'], $_POST[$field['slug']] );
						if( $captcha_success === false )	{
							$captcha_instance->remove( $_POST['mdjm_captcha_prefix'] );
							?>
							<script type="text/javascript">
							alert("Invalid value entered for <?php echo $field['name']; ?> entered. Please try again.");
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
			}
			
			foreach( $fields as $field )	{
				/* Mappings */
				if( isset( $field['config']['mapping'] ) && !empty( $field['config']['mapping'] ) && !empty( $_POST[$field['slug']] ) )	{
					/* Client Mappings */
					if( in_array( $field['config']['mapping'], $client_fields ) )	{
						$create_user = true;
						if( !isset( $user_meta ) || !is_array( $user_meta ) )	{
							$user_meta = array();
						}
						$user_meta[$field['config']['mapping']] = $_POST[$field['slug']];
					}
					/* Event Mappings */
					if( in_array( $field['config']['mapping'], $event_fields ) )	{
						$create_event = true;
						if( !isset( $event_update ) || !is_array( $event_update ) )	{
							$event_update = array();
						}
						if( $field['type'] == 'time' )	{
							if( $mdjm_options['time_format'] == 'H:i' )	{
								$time = $_POST[$field['slug'] . '_hr'] . ':' . $_POST[$field['slug'] . '_min'];
								$event_update[$field['config']['mapping']] = date( 'H:i:s', strtotime( $time ) );
							}
							else	{
								$time = $_POST[$field['slug'] . '_hr'] . ':' . $_POST[$field['slug'] . '_min'] . $_POST[$field['slug'] . '_period'];
								$event_update[$field['config']['mapping']] = date( 'H:i:s', strtotime( $time ) );
							}
						}
						else	{
							$event_update[$field['config']['mapping']] = $_POST[$field['slug']];	
						}
					}
				}
				/* Email Headers */
				if( $field['type'] == 'email' )	{
					if( isset( $form['config']['reply_to'] ) && $form['config']['reply_to'] == 'Y' )	{
						$email_headers[] = 'Reply-To: ' . $_POST[$field['slug']];
						$client_email = $_POST[$field['slug']];
					}
				}
				/* Email body */
				if( $field['type'] != 'submit' && $field['type'] != 'captcha' && !empty( $_POST[$field['slug']] ) )	{
					/* DJ Availability */
					if( $field['type'] == 'date' && isset( $field['config']['datepicker'] ) && $field['config']['datepicker'] == 'Y' )	{
						$dj_avail = f_mdjm_available( $_POST['the_event_date'] );
						if( $dj_avail !== false )	{
							if( count( $dj_avail ) != 1 )	{
								$avail_message = count( $dj_avail ) . ' DJ\'s available';
							}
							else	{
								$avail_message = count( $dj_avail ) . ' DJ available';
							}
						}
						else	{
							$avail_message = 'No DJ\'s available';	
						}
					}
					if( $field['type'] == 'time' )	{
						if( $mdjm_options['time_format'] == 'H:i' )	{
							$time = $_POST[$field['slug'] . '_hr'] . ':' . $_POST[$field['slug'] . '_min'];
							$_POST[$field['slug']] = date( 'H:i:s', strtotime( $time ) );						
						}
						else	{
							$time = $_POST[$field['slug'] . '_hr'] . ':' . $_POST[$field['slug'] . '_min'] . $_POST[$field['slug'] . '_period'];
							$_POST[$field['slug']] = date( 'g:i A', strtotime( $time ) );					
						}
					}
					
					$email_body .= '<p><span style="font-weight:bold">' . $field['name'] . '</span><br />';
					$email_body .= nl2br( html_entity_decode( stripcslashes( $_POST[$field['slug']] ) ) );
					$client_form_detail = $email_body;
					if( $field['type'] == 'date' && isset( $avail_message ) && !empty( $avail_message ) )	{
						$email_body .= ' (' . $avail_message . ')';
					}
					echo '</p>';
				}
			} // End foreach( $fields as $field )
			
			/* End the email body */
			$email_body .= '<p><a href="'. admin_url( 'admin.php?page=mdjm-events&status=Unattended&orderby=contract_status&order=desc' ) . '">View Your Outstanding Enquiries</a></p>';
			$email_body .= '<hr />';
			$email_body .= '<p style="font-size:10px;">Form submitted at ' . date( $mdjm_options['time_format'] ) . ' on ' . date( 'l jS F Y' ) . '</p>';
			$email_body .= '<p align="center" style="font-size:9px; color:#F90">Powered by <a style="font-size:9px; color:#F90" href="http://www.mydjplanner.co.uk" target="_blank">' . WPMDJM_NAME . '</a> version ' . WPMDJM_VERSION_NUM . '</p>';
			$email_body .= '</body></html>';
			
			/* Send the email */
			if( wp_mail( $form['config']['email_to'], $form['config']['email_subject'], $email_body, $email_headers ) )	{
			
				/* User actions (mappings) */
				if( isset( $create_user, $user_meta ) && $create_user && !empty( $client_email ) )	{
					/* Check if user exists */
					$user_id = email_exists( $client_email );
					if( $user_id )	{ // Existing user
						$client_field_array = array( 'ID' => $user_id );
						if( isset( $form['config']['update_user'], $form['config']['create_enquiry'] ) && $form['config']['update_user'] == 'Y' && $form['config']['create_enquiry'] == 'Y' )	{
							$userdata = get_userdata( $user_id );
							foreach( $user_meta as $key => $value )	{
								if( $key == 'first_name' && $value != $userdata->first_name )	{
											$client_field_array['user_nicename'] = sanitize_text_field( $value );
											$client_field_array['display_name'] = sanitize_text_field( $value );
										}
										if( $key == 'last_name' && $value != $userdata->last_name )	{
											if( isset( $client_field_array['display_name'] ) )	{
												$client_field_array['display_name'] = $client_field_array['display_name'] . ' ' . sanitize_text_field( $value );
											}
										}
										if( $key == 'phone1' && $value != $userdata->phone1 )	{
											update_user_meta( $user_id, $key, $value );	
										}
										if( $key == 'marketing' && $value != $userdata->marketing )	{
											update_user_meta( $user_id, $key, $value );	
										}
										$client_field_array[$key] = $value;
							} // foreach( $user_meta as $key => $value )	
								 wp_update_user( $client_field_array );
						} // if( isset( $form['config']['update_user']...
					} // if( $user_id )
					else	{ // New user
						if( isset( $form['config']['create_enquiry'] ) && $form['config']['create_enquiry'] == 'Y' )	{
							$user_id = username_exists( $client_email ); // Check if username exists
							if( !$user_id && $user_id == false )	{
								$random_password = wp_generate_password( $mdjm_options['pass_length'] );
								$user_id = wp_create_user( $client_email, $random_password, $client_email );
								$client_field_array = array( 'ID' => $user_id, 'role' => 'client', 'show_admin_bar_front' => 'false' );
								update_user_meta( $user_id, 'marketing', 'Y' );
								foreach( $user_meta as $key => $value )	{
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
									if( $key == 'phone1' )	{
										update_user_meta( $user_id, $key, $value );	
									}
									if( $key == 'marketing' )	{
										update_user_meta( $user_id, $key, $value );	
									}
									$client_field_array[$key] = $value;
								}
								wp_update_user( $client_field_array );
							} // if( !$user_id && $user_exists == false )
						} // if( isset( $form['config']['create_enquiry'] )
					} // else
				} // if( isset( $create_user, $user_meta )
				
				/* Event actions (mappings) */
				if( isset( $form['config']['create_enquiry'], $create_event, $event_update, $_POST['the_event_date'], $user_id ) && $create_event === true && $form['config']['create_enquiry'] == 'Y' )	{
					$event_id = $mdjm_contact_form->mdjm_create_event( $user_id, $event_update, $_POST );
				}
				
				/* Send the client email if set */
					if( isset( $form['config']['copy_sender'] ) && $form['config']['copy_sender'] == 'Y' )	{
						$mdjm_contact_form->mdjm_client_email( $form, $client_email, $client_form_detail, $user_id, $event_id );
					}
				
				/* Redirect the user */
				if( isset( $form['config']['redirect'] ) && $form['config']['redirect'] != 'no_redirect' && $form['config']['redirect'] !== false )	{
					?>
					<script type="text/javascript">
					window.location = '<?php echo get_permalink( $form['config']['redirect'] ); ?>';
					</script>
					<?php
				}
				/* Display message */
				else	{
					/* Application Shortcodes */
					$search = array( '{ADMIN_URL}', '{APPLICATION_HOME}', '{APPLICATION_NAME}', '{COMPANY_NAME}', '{WEBSITE_URL}' );
					$replace = array( admin_url(), home_url(), $mdjm_options['app_name'], $mdjm_options['company_name'], home_url() );
					
					if( !empty( $form ) )	{ // Use form config
						$layout = substr( $form['config']['layout'], 0, 1 );
						if( $layout == 'not_set' )	{
							$layout = '0';	
						}
					}
					else	{
						$layout = '0'; // Default
					}
					
					echo '<p>' . nl2br( html_entity_decode( stripcslashes( str_replace( $search, $replace, $form['config']['display_message_text'] ) ) ) ) . '</p>';
					/* Display the page */
					$mdjm_contact_form->mdjm_form_header( $form );
					$mdjm_contact_form->mdjm_display_form( $layout, $form, $fields );
			
			/* Print the credit if set */
					add_action( 'wp_footer', 'f_wpmdjm_print_credit' );
				}
			} // if( wp_mail )
			/* Error sending email */
			else	{
				$mdjm_contact_form->mdjm_form_header( $form );
				?>
				<p style="color: <?php if( !empty( $form['config']['error_text_color'] ) ) { echo $form['config']['error_text_color']; } else { echo '#FF0000'; } ?>;">Sorry there was an error processing the contact form. Please try again.</p>
                <?php
				$mdjm_contact_form->mdjm_form_header( $form );
		
		/* Print the credit if set */
				add_action( 'wp_footer', 'f_wpmdjm_print_credit' );
			}
		}
		/* Unset the $_POST vars to stop the Captcha erroring */
		unset( $_POST );
	}
	
?>