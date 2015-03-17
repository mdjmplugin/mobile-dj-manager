<?php
/*
* class-mdjm-contact-form.php
* 12/02/2015
* @since 1.0
* MDJM Contact Form
*/

	class MDJM_ContactForm {
		/*
		* mdjm_form_header
		* 12/02/2015
		* @since 1.0
		* Displays the header content for the form
		*/
		function mdjm_form_header( $form )	{
			wp_enqueue_script('jquery-ui-datepicker');
			wp_enqueue_style('jquery-ui-css', 'http://ajax.googleapis.com/ajax/libs/jqueryui/1.8.2/themes/smoothness/jquery-ui.css');
			?>
			<style>
			.mdjm-form-error {
				color: <?php if( !empty( $form['config']['error_text_color'] ) ) { echo $form['config']['error_text_color']; } else { echo '#FF0000'; } ?>;
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
			if( isset( $_GET['mdjm_avail'], $_GET['mdjm_avail_date'] ) && $_GET['mdjm_avail'] == 1 )	{
				$mdjm_pages = get_option( 'mdjm_plugin_pages' );
				if( isset( $mdjm_pages['availability_check_pass_text'] ) && !empty( $mdjm_pages['availability_check_pass_text'] ) )	{	
				$search = array( '{EVENT_DATE}', '{EVENT_DATE_SHORT}' );
				$replace = array( date( 'l, jS F Y', strtotime( $_GET['mdjm_avail_date'] ) ), date( 'd/m/Y', strtotime( $_GET['mdjm_avail_date'] ) ),  );
					echo '<p>' . nl2br( html_entity_decode( stripcslashes( str_replace( $search, $replace, $mdjm_pages['availability_check_pass_text'] ) ) ) ) . '</p>';
				}
			}
		} // mdjm_form_header
		
		/*
		* jq_validate
		* 12/02/2015
		* @since 1.0
		* Validation for the contact form
		*/
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
									echo 'required: "' . str_replace( '{FIELD_NAME}', $field['name'], $form['config']['required_field_text'] ) . '",' .  "\n";
									if( $field['type'] == 'email' )	{
										echo $field['type'] . ': "Please enter a valid email address",' . "\n";
									}
									if( $field['type'] == 'url' )	{
										echo $field['type'] . ': "Please enter a valid URL",' . "\n";
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
		} // jq_validate
		
		/*
		* mdjm_display_form
		* 12/02/2015
		* @since 1.0
		* Displays the form content
		*/
		function mdjm_display_form( $layout, $form, $fields )	{
			global $mdjm_options;
			
			$columns = $layout;
			$i = 0;
			echo "\n" . '<!-- Start of MDJM Contact Form -->' . "\n";
			echo '<form name="mdjm-' . $form['slug'] . '" id="mdjm-' . $form['slug'] . '" method="post" action="">' . "\n";
			echo '<input type="hidden" name="mdjm_contact_form_submission" id="mdjm_contact_form_submission" value="submitted" />' . "\n";
			echo '<input type="hidden" name="mdjm_contact_form_slug" id="mdjm_contact_form_slug" value="' . $form['slug'] . '" />' . "\n";
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
				if( $field['type'] == 'text' || $field['type'] == 'email' || $field['type'] == 'tel' || $field['type'] == 'url' )	{
					echo '<input type="' . $field['type'] . '" name="' . $field['slug'] . '" id="' . $field['slug'] . '"';
					if( isset( $field['config']['placeholder'] ) && !empty( $field['config']['placeholder'] ) )	{
						echo ' placeholder="' . $field['config']['placeholder'] . '"';
					}
					if( !empty( $field['config']['input_class'] ) )	{
						echo ' class="' . $field['config']['input_class'] . '"';
					}
					if( !empty( $field['config']['width'] ) )	{
						echo ' size="' . $field['config']['width'] . '"';
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
					if( isset( $field['config']['mapping'], $_GET['mdjm_avail_date'] ) && $field['config']['mapping'] == 'event_date'  )	{
						echo ' value="' . date( $mdjm_options['short_date_format'], strtotime( $_GET['mdjm_avail_date'] ) ) . '"';
					}
					if( isset( $field['config']['required'] ) && $field['config']['required'] == 'Y' )	{
						echo ' required';
					}
					echo ' />' . "\n";
					if( isset( $field['config']['datepicker'] ) && $field['config']['datepicker'] == 'Y' )	{
						echo '<input type="hidden" name="the_event_date" id="the_event_date" value="" />' . "\n";
					}
				}
				/* Time Field */
				elseif( $field['type'] == 'time' )	{
					echo '<input type="hidden" name="' . $field['slug'] . '" value="Y" />' . "\n";
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
						$h = '00';
						$x = '23';
					}
					else	{
						$h = '1';
						$x = '12';	
					}
					while( $h <= $x )	{
						echo '<option value="' . $h . '">' . $h . '</option>' . "\n";
						$h++;
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
					echo ' value="' . $field['config']['checked_value'] . '" />' . "\n";
				}
				/* Textarea Fields */
				if( $field['type'] == 'textarea' )	{
					echo '<textarea name="' . $field['slug'] . '" id="' . $field['slug'] . '"';
					if( isset( $field['config']['placeholder'] ) && !empty( $field['config']['placeholder'] ) )	{
						echo ' placeholder="' . $field['config']['placeholder'] . '"';
					}
					if( !empty( $field['config']['width'] ) )	{
						echo ' cols="' . $field['config']['width'] . '"';
					}
					if( !empty( $field['config']['height'] ) )	{
						echo ' rows="' . $field['config']['height'] . '"';
					}
					if( !empty( $field['config']['input_class'] ) )	{
						echo ' class="' . $field['config']['input_class'] . '"';
					}
					if( isset( $field['config']['required'] ) && $field['config']['required'] == 'Y' )	{
						echo ' required';
					}
					echo '></textarea>';
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
			$this->jq_validate( $form, $fields );
			echo '</form>' . "\n";
			echo '<!-- End of MDJM Contact Form -->' . "\n";
		} // mdjm_display_form
		
		/*
		* mdjm_create_event
		* 13/02/2015
		* @since 1.0
		* Create the Unattended Enquiry
		*/
		function mdjm_create_event( $user_id, $event_update, $form_data )	{
			global $mdjm_options, $wpdb;
			
			if( !isset( $db_tbl ) )	{
				include(  WPMDJM_PLUGIN_DIR . '/includes/config.inc.php' );	
			}
			
			$event_update['event_id'] = '';
			$event_update['user_id'] = $user_id;
			$event_update['event_date'] = $form_data['the_event_date'];
			if( isset( $mdjm_options['multiple_dj'] ) && $mdjm_options['multiple_dj'] == 'Y' )	{
				$event_update['event_dj'] = 0;
			}
			else	{
				$event_update['event_dj'] = 1;	
			}
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
			
			return $event_id;
		} // mdjm_create_event
		
		/*
		* mdjm_client_email
		* 13/02/2015
		* @since 1.0
		* Send the Client email
		*/
		function mdjm_client_email( $form, $client_email, $client_form_detail, $user_id, $event_id )	{
			global $mdjm_options;
			
			$client_email_headers = array();
			$client_email_headers[] = 'MIME-Version: 1.0';
			$client_email_headers[] = 'Content-type: text/html; charset=UTF-8';
			$client_email_headers[] = 'From: ' . $form['config']['email_from_name'] . ' <' . $form['config']['email_from'] . '>';
			if( isset( $form['config']['send_template'] ) && ! empty( $form['config']['send_template'] ) )	{
				$template_query = new WP_Query( array( 'post_type' => 'email_template', 'post__in' => array( $form['config']['send_template'] ) ) );
				if ( $template_query->have_posts() ) {
					while ( $template_query->have_posts() ) {
						$template_query->the_post();
						/* Check if we are using the post title as the subject */
						if( isset( $mdjm_options['title_as_subject'] ) && $mdjm_options['title_as_subject'] == 'Y' )	{
							$subject = get_the_title();
						}
						else	{
							$subject = 'Your message to ' . $mdjm_options['company_name'];
						}
						$client_email_body = get_the_content();
						$client_email_body = apply_filters( 'the_content', $client_email_body );
						$client_email_body = str_replace(']]>', ']]&gt;', $client_email_body);
						$client_email_content = '<html><body>';
						$client_email_content .= $client_email_body;
						$client_email_content .= '</body></html>';
					}
				}
								
				wp_mail( $client_email, $subject, $client_email_content, $client_email_headers );
			}
			else	{
				wp_mail( $client_email, $subject, $client_form_detail, $client_email_headers );
			}
			
		} // mdjm_client_email
		
	} // class MDJM_ContactForm
?>