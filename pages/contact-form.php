<?php
/*
* contact-form.php
* 08/01/2015
* @since 1.0
* Display MDJM contact form
*/

	defined('ABSPATH') or die("Direct access to this page is disabled!!!");
	
/* Define the functions */
/* Header */
	function f_mdjm_contact_header( $form )	{
		wp_enqueue_script('jquery-ui-datepicker');
		wp_enqueue_style('jquery-ui-css', 'http://ajax.googleapis.com/ajax/libs/jqueryui/1.8.2/themes/smoothness/jquery-ui.css');
		?>
        <style>
		.mdjm-form-error {
			color: #<?php if( !empty( $form['config']['error_text_color'] ) ) { echo $form['config']['error_text_color']; } else { echo 'FF0000'; } ?>;
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
		jQuery(document).ready(function($) {
			$('.mdjm_date').datepicker({
			dateFormat : '<?php f_mdjm_short_date_jquery(); ?>',
			altField  : '#the_event_date',
			altFormat : 'yy-mm-dd',
			firstDay: <?php echo get_option( 'start_of_week' ); ?>,
			changeYear: true,
			changeMonth: true
			});
		});
		</script>
        <?php
	}
	
/* Validation */
	function jq_validate( $form, $fields )	{
		?>
        <script type="text/javascript">
        jQuery(document).ready(function($){
			// Configure the field validator
            $('#<?php echo 'mdjm-' . $form['slug']; ?>').validate(
				{
					rules:
					{
						<?php
						foreach( $fields as $field )	{
							if( isset( $field['config']['required'] ) && $field['config']['required'] == 'Y' )	{
								echo $field['slug'] . ':' . "\n";
								echo '{' . "\n";
								echo 'required: true,' . "\n";
								if( $field['type'] == 'email' || $field['type'] == 'url' || $field['type'] == 'date' )	{
									echo $field['type'] . ': true,' . "\n";
								}
								echo '},' . "\n";
							}
						}
						?>	
					}, // End rules
					messages:
					{
						<?php
						foreach( $fields as $field )	{
							if( isset( $field['config']['required'] ) && $field['config']['required'] == 'Y' )	{
								echo $field['slug'] . ':' . "\n";
								echo '{' . "\n";
								echo 'required: "<br />' . str_replace( '{FIELD_NAME}', $field['name'], $form['config']['required_field_text'] ) . '",' .  "\n";
								if( $field['type'] == 'email' )	{
									echo $field['type'] . ': "<br />Please enter a valid email address",' . "\n";
								}
								if( $field['type'] == 'url' )	{
									echo $field['type'] . ': "<br />Please enter a valid URL",' . "\n";
								}
								echo '},' . "\n";	
							}
						}
						?>	
					}, // End messages
					// Classes
					errorClass: "mdjm-form-error",
					validClass: "mdjm-form-valid",
					focusInvalid: false,
				} // End validate
			); // Close validate
        });
		</script>
        <?php	
	}

/* f_mdjm_contact_form */
	function f_mdjm_contact_form( $layout, $form, $fields )	{
		global $mdjm_options;
			
		$columns = $layout;
		$i = 0;
		echo "\n" . '<!-- Start of MDJM Contact Form -->' . "\n";
		echo '<form name="mdjm-' . $form['slug'] . '" id="mdjm-' . $form['slug'] . '" method="post" action="">' . "\n";
		echo '<input type="hidden" name="mdjm_contact_form_submission" id="mdjm_contact_form_submission" value="submitted">' . "\n";
		echo '<input type="hidden" name="mdjm_contact_form_slug" id="mdjm_contact_form_slug" value="' . $form['slug'] . '">' . "\n";
		if( $layout != 0 )	{
			echo '<table width="100%" border="0" cellspacing="0" cellpadding="0">' . "\n";
		}
		foreach( $fields as $field )	{
/* New row if required */
			if( $layout == 0 )	{
				echo '<p';
				
				if( isset( $field['config']['label_class'] ) && !empty( $field['config']['label_class'] ) )	{
					echo ' class="' . $field['config']['label_class'] . '"';	
				}
				
				echo '>';
			}
			elseif( $i == 0 )	{
				echo '<tr>' . "\n";
			}
/* The label */
			if( $layout != 0 )	{
				echo '<td';
				if( isset( $form['config']['row_height'] ) && !empty( $form['config']['row_height'] ) )	{
					echo ' height="' . $form['config']['row_height'] . '"';	
				}
				if( isset( $field['config']['label_class'] ) && !empty( $field['config']['label_class'] ) )	{
					echo ' class="' . $field['config']['label_class'] . '"';	
				}
				echo '>';
			}
			
			if( $field['type'] != 'submit' )	{
				echo '<label for="' . $field['slug'] . '">' . $field['name'] . '</label>';
			}
			
			if( $layout == 0 )	{
				echo "\n" . '<br />' . "\n";
			}
			else	{
				echo '</td>' . "\n";	
			}
			
			$i++;
						
/* The field */
			if( $layout != 0 )	{
				echo '<td>';
			}
			/* Text / Email / Telephone Fields / URL */
			if( $field['type'] == 'text' || $field['type'] == 'email' || $field['type'] == 'telephone' || $field['type'] == 'url' )	{
				echo '<input type="' . $field['type'] . '" name="' . $field['slug'] . '" id="' . $field['slug'] . '"';
				if( isset( $field['config']['placeholder'] ) && !empty( $field['config']['placeholder'] ) )	{
					echo ' placeholder="' . $field['config']['placeholder'] . '"';
				}
				if( !empty( $field['config']['input_class'] ) )	{
					echo ' class="' . $field['config']['input_class'] . '"';
				}
				if( isset( $field['config']['required'] ) && $field['config']['required'] == 'Y' )	{
					echo ' required';
				}
				echo ' />';
			}
			/* Date Field */
			elseif( $field['type'] == 'date' )	{
				echo '<input type="text" name="' . $field['slug'] . '" id="' . $field['slug'] . '"';
				if( isset( $field['config']['datepicker'] ) && $field['config']['datepicker'] == 'Y' )	{
					echo ' class="mdjm_date"';
				}
				elseif( !empty( $field['config']['input_class'] ) )	{
					echo ' class="' . $field['config']['class'] . '"';
				}
				if( isset( $field['config']['required'] ) && $field['config']['required'] == 'Y' )	{
					echo ' required';
				}
				echo ' />';
				if( isset( $field['config']['datepicker'] ) && $field['config']['datepicker'] == 'Y' )	{
					echo '<input type="hidden" name="the_event_date" id="the_event_date" value="" />';
				}
			}
			/* Time Field */
			elseif( $field['type'] == 'time' )	{
				echo '<select name="' . $field['slug'] . '_hr" id="' . $field['slug'] . '_hr"';
				if( !empty( $field['config']['input_class'] ) )	{
					echo ' class="' . $field['config']['input_class'] . '"';
				}
				if( isset( $field['config']['required'] ) && $field['config']['required'] == 'Y' )	{
					echo ' required';
				}
				echo '>' . "\n";
				
				$minutes = array( '00', '15', '30', '45' );
				if( $mdjm_options['time_format'] == 'H:i' )	{
					$i = '00';
					$x = '23';
				}
				else	{
					$i = '1';
					$x = '12';	
				}
				while( $i <= $x )	{
					echo '<option value="' . $i . '">' . $i . '</option>' . "\n";
					$i++;
				}
				echo '</select>' . "\n";
				
				echo '&nbsp;<select name="' . $field['slug'] . '_min" id="' . $field['slug'] . '_min"';
				if( !empty( $field['config']['input_class'] ) )	{
					echo ' class="' . $field['config']['input_class'] . '"';
				}
				if( isset( $field['config']['required'] ) && $field['config']['required'] == 'Y' )	{
					echo ' required';
				}
				echo '>' . "\n";
				
				foreach( $minutes as $minute )	{
					echo '<option value="' . $minute . '">' . $minute . '</option>' . "\n";
				}
                echo '</select>' . "\n";
				
				if( $mdjm_options['time_format'] != 'H:i' )	{
					echo '&nbsp;<select name="' . $field['slug'] . '_period" id="' . $field['slug'] . '_period"';
					if( !empty( $field['config']['input_class'] ) )	{
					echo ' class="' . $field['config']['input_class'] . '"';
					}
					if( isset( $field['config']['required'] ) && $field['config']['required'] == 'Y' )	{
						echo ' required';
					}
					echo '>' . "\n";
					echo '<option value="AM">AM</option>' . "\n";
					echo '<option value="PM">PM</option>' . "\n";
					echo '</select>' . "\n";
				}
				
			}
			/* Select / Event List Fields */
			elseif( $field['type'] == 'select' || $field['type'] == 'select_multi' || $field['type'] == 'event_list' )	{
				echo '<select name="' . $field['slug'] . '" id="' . $field['slug'] . '"';
				if( $field['type'] == 'select_multi' )	{
					echo ' multiple="multiple"';
				}
				if( !empty( $field['config']['input_class'] ) )	{
					echo ' class="' . $field['config']['input_class'] . '"';
				}
				if( isset( $field['config']['required'] ) && $field['config']['required'] == 'Y' )	{
					echo ' required';
				}
				echo '>' . "\n";
				
				if( $field['type'] == 'event_list' )	{
					$options = explode( "\n", $mdjm_options['event_types'] );
					asort( $options );
					if( isset( $field['config']['event_list_first_entry'] ) && !empty( $field['config']['event_list_first_entry'] ) )	{
						array_unshift( $options, $field['config']['event_list_first_entry'] );
					}
				}
				else	{
					$options = explode( "\n", $field['config']['options'] );
				}
				foreach( $options as $option )	{
					echo '<option value="' . $option . '">' . $option . '</option>' . "\n";
				}
				
				echo '</select>' . "\n";
			}
			/* Checkbox Field */
			elseif( $field['type'] == 'checkbox' )	{
				echo '<input type="checkbox" name="' . $field['slug'] . '" id="' . $field['slug'] . '"';
				if( isset( $field['config']['is_checked'] ) && $field['config']['is_checked'] == 'Y' )	{
					echo ' checked="checked"';
				}
				if( !empty( $field['config']['input_class'] ) )	{
					echo ' class="' . $field['config']['input_class'] . '"';
				}
				echo ' value="' . $field['config']['checked_value'] . '" />';
			}
			/* Textarea Fields */
			if( $field['type'] == 'textarea' )	{
				echo '<textarea name="' . $field['slug'] . '" id="' . $field['slug'] . '"';
				if( isset( $field['config']['placeholder'] ) && !empty( $field['config']['placeholder'] ) )	{
					echo ' placeholder="' . $field['config']['placeholder'] . '"';
				}
				if( !empty( $field['config']['input_class'] ) )	{
					echo ' class="' . $field['config']['input_class'] . '"';
				}
				if( isset( $field['config']['required'] ) && $field['config']['required'] == 'Y' )	{
					echo ' required';
				}
				echo ' ></textarea>';
			}
			/* CAPTCHA Field */
			if( $field['type'] == 'captcha' )	{
				$captcha_instance = new ReallySimpleCaptcha();
				$captcha_word = $captcha_instance->generate_random_word();
				$captcha_prefix = mt_rand();
				
				echo '<input type="text" name="' . $field['slug'] . '" id="' . $field['slug'] . '"';
				if( isset( $field['config']['placeholder'] ) && !empty( $field['config']['placeholder'] ) )	{
					echo ' placeholder="' . $field['config']['placeholder'] . '"';
				}
				if( !empty( $field['config']['input_class'] ) )	{
					echo ' class="' . $field['config']['input_class'] . '"';
				}
				if( isset( $field['config']['required'] ) && $field['config']['required'] == 'Y' )	{
					echo ' required';
				}
				echo ' />&nbsp;&nbsp;<img src="' . plugins_url( 'really-simple-captcha/tmp/' . $captcha_instance->generate_image( $captcha_prefix, $captcha_word ) ) . '" />' . "\n";
				echo '<input type="hidden" name="mdjm_captcha_prefix" id="mdjm_captcha_prefix" value="' . $captcha_prefix . '" />';
			}
			/* End the column */
			if( $layout == 0 )	{
				echo '</p>' . "\n";	
			}
			else	{
				echo '</td>' . "\n";
			}
/* End of row */
			$i++;
			if( $layout != 0 && $i == $columns )	{
				echo '</tr>' . "\n";
				$i = 0;
			}
			/* Submit Button */
			if( $field['type'] == 'submit' )	{
				$submit = $field;
			}
		} // foreach( $fields as $field )
		
/* Excessive columns */
		if( $layout != 0 && $i != 0 )	{
			 while( $i < $columns ) {
				  echo '<td>&nbsp;</td>' . "\n";
				  $i++;
			 }
			 echo '</tr>' . "\n";
		}
		/* Submit Button */
		if( isset( $submit ) && !empty( $submit ) )	{
			if( $layout == 0 )	{
				echo '<p';
				if( !empty( $submit['config']['submit_align'] ) )	{
					echo ' align="' . $submit['config']['submit_align'] . '"';	
				}
				echo '>';
			}
			else	{
				echo '<tr>' . "\n";
				echo '<td colspan="' . $layout . '"';
				if( !empty( $submit['config']['submit_align'] ) )	{
					echo ' align="' . $submit['config']['submit_align'] . '"';	
				}
				if( isset( $field['config']['row_height'] ) && !empty( $field['config']['row_height'] ) )	{
					echo ' height="' . $field['config']['row_height'] . '"';	
				}
				echo '>';	
			}
			echo '<input type="submit" "name="' . $submit['slug'] . '" id="' . $submit['slug'] . '" value="' . $submit['name'] . '"';
			if( !empty( $field['config']['input_class'] ) )	{
					echo ' class="' . $field['config']['input_class'] . '"';
				}
			echo ' />';
			if( $layout == 0 )	{
				echo '</p>' . "\n";
			}
			else	{
				echo '</td>' . "\n";
				echo '</tr>' . "\n";
			}
		}
		if( $layout != 0 )	{
			echo '</table>' . "\n";
		}
		/* Add the validation */
		jq_validate( $form, $fields );
		echo '</form>' . "\n";
		echo '<!-- End of MDJM Contact Form -->' . "\n";
	} // f_mdjm_contact_form

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
		f_mdjm_contact_header( $mdjm_forms[$atts['slug']] );
		f_mdjm_contact_form( $layout, $mdjm_forms[$atts['slug']], $fields );
		
/* Print the credit if set */
		add_action( 'wp_footer', 'f_wpmdjm_print_credit' );
	} // if( !isset( $_POST['mdjm_contact_form_submission'] )
	
/* Form submitted */
	else	{
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
			
			$email_body = '';
			
			/* PHP Validation & Initial Config */
			foreach( $fields as $field )	{
				if( $field['type'] == 'captcha' )	{
					if( !isset( $_POST['mdjm_captcha_prefix'] ) || empty( $_POST['mdjm_captcha_prefix'] ) )	{
						$captcha_success = false;
					}
					else	{
						$captcha_instance = new ReallySimpleCaptcha();
						$captcha_success = $captcha_instance->check( $_POST['mdjm_captcha_prefix'], $_POST[$field['slug']] );
						$captcha_instance->remove( $_POST['mdjm_captcha_prefix'] );
					}
					
					if( !$captcha_success )	{
						?>
                        <script type="text/javascript">
						alert("Invalid value entered for <?php echo $field['name']; ?> entered. Please try again.");
						history.back();
						</script>
                        <?php
						exit;
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
							}
							else	{
								$time = $_POST[$field['slug'] . '_hr'] . ':' . $_POST[$field['slug'] . '_min'] . $_POST[$field['slug'] . '_period'];					
							}
							$event_update[$field['config']['mapping']] = date( 'H:i:s', strtotime( $time ) );
						}
						else	{
							$event_update[$field['config']['mapping']] = $_POST[$field['slug']];	
						}
					}
				}
				/* Email Headers */
				if( $field['type'] == 'email' )	{
					if( isset( $form['config']['copy_sender'] ) && $form['config']['copy_sender'] == 'Y' )	{
						$email_headers[] = 'Cc: ' . $_POST[$field['slug']];	
					}
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
						}
						else	{
							$time = $_POST[$field['slug'] . '_hr'] . ':' . $_POST[$field['slug'] . '_min'] . $_POST[$field['slug'] . '_period'];					
						}
						$_POST[$field['slug']] = date( 'H:i:s', strtotime( $time ) );
					}
					
					$email_body .= '<p><span style="font-weight:bold">' . $field['name'] . '</span><br />';
					$email_body .= nl2br( html_entity_decode( stripcslashes( $_POST[$field['slug']] ) ) );
					if( $field['type'] == 'date' && isset( $avail_message ) && !empty( $avail_message ) )	{
						$email_body .= ' (' . $avail_message . ')';
					}
					echo '</p>';
				}
			} // End foreach( $fields as $field )
			
			/* End the email body */
			$email_body .= '<p><a href="'. admin_url( 'admin.php?page=mdjm-events&display=enquiries' ) . '">View Your Outstanding Enquiries</a></p>';
			$email_body .= '<hr />';
			$email_body .= '<p style="font-size:10px;">Form submitted at ' . date( $mdjm_options['time_format'] ) . ' on ' . date( 'l jS F Y' ) . '</p>';
			$email_body .= '<p align="center" style="font-size:9px; color:#F90">Powered by <a style="font-size:9px; color:#F90" href="http://www.mydjplanner.co.uk" target="_blank">' . WPMDJM_NAME . '</a>, version ' . WPMDJM_VERSION_NUM . '</p>';
			
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
										$client_field_array['user_nicename'] = sanitize_text_field( $value );
										$client_field_array['display_name'] = sanitize_text_field( $value );
									}
									if( $key == 'last_name' )	{
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
					if( !isset( $db_tbl ) )	{
						include(  WPMDJM_PLUGIN_DIR . '/includes/config.inc.php' );	
					}
					$event_update['event_id'] = '';
					$event_update['user_id'] = $user_id;
					$event_update['event_date'] = $_POST['the_event_date'];
					$event_update['event_dj'] = 0;
					$event_update['contract_status'] = 'Unattended';
					$event_update['contract']= $mdjm_options['default_contract'];
					$event_update['added_by'] = 0;
					$event_update['date_added'] = date( 'Y-m-d H:i:s' );
					$event_update['referrer'] = 'Website';
					$event_update['last_updated_by'] = 0;
					$event_update['last_updated'] = date( 'Y-m-d H:i:s' );
					
					$wpdb->insert( $db_tbl['events'], $event_update );
					$event_id = $wpdb->insert_id;
					
					/* Journal Entry */
					$j_args = array (
						'client' => $user_id,
						'event' => $event_id,
						'author' => 0,
						'type' => 'Create Enquiry',
						'source' => 'Website',
						'entry' => 'Unattended enquiry created'
						);
					if( WPDJM_JOURNAL == 'Y' ) f_mdjm_do_journal( $j_args );
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
					f_mdjm_contact_header( $form );
					f_mdjm_contact_form( $layout, $form, $fields );
			
			/* Print the credit if set */
					add_action( 'wp_footer', 'f_wpmdjm_print_credit' );
				}
			} // if( wp_mail )
			/* Error sending email */
			else	{
				f_mdjm_contact_header( $form );
				?>
				<p style="color:<?php if( !empty( $form['config']['error_text_color'] ) ) { echo $form['config']['error_text_color']; } else { echo 'FF0000'; } ?>;">Sorry there was an error processing the contact form. Please try again.</p>
                <?php
				f_mdjm_contact_form( $layout, $form, $fields );
		
		/* Print the credit if set */
				add_action( 'wp_footer', 'f_wpmdjm_print_credit' );
			}
		}
	}
	
?>