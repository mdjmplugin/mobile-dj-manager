<?php
/*
* class-mdjm-widget.php
* 28/12/2014
* @since 0.9.9
* MDJM Widgets
*/

/*
* MDJM_Availability_Widget
* 28/12/2014
* @since 0.9.9
* Display the Availability widget
*/

	class MDJM_Availability_Widget extends WP_Widget {
		/* Register the Widget */
		function __construct() {
			parent::__construct(
				'mdjm_availability_widget', /* Base ID */
				__( 'MDJM Availability Checker', 'text_domain' ), /* Name */
				array( 'description' => __( 'Enables clients to check your availability', 'text_domain' ), ) /* Args */
			);
		}
	
		/**
		 * Front-end display of widget.
		 *
		 * @see WP_Widget::widget()
		 *
		 * @param array $args     Widget arguments.
		 * @param array $instance Saved values from database.
		 */
		public function widget( $args, $instance )	{
			global $mdjm_options;
			
			echo $args['before_widget'];
			
			if ( !empty( $instance['title'] ) ) {
				echo $args['before_title'] . apply_filters( 'widget_title', $instance['title'] ). $args['after_title'];
			}
			
			/* Check for form submission & process */
			if( isset( $_POST['mdjm_avail_submit'] ) && $_POST['mdjm_avail_submit'] == $instance['submit_text'] )	{
				$mdjm_pages = get_option( 'mdjm_plugin_pages' );
				$dj_avail = f_mdjm_available( $_POST['check_date'] );
				
				if( isset( $dj_avail ) )	{
					if ( $dj_avail !== false )	{
						if( isset( $instance['available_action'] ) && $instance['available_action'] != 'text' )	{
							?>
							<script type="text/javascript">
							window.location = '<?php echo get_permalink( $instance['available_action'] ); ?>';
							</script>
							<?php
						}
					}
					else	{
						if( isset( $instance['unavailable_action'] ) && $instance['unavailable_action'] != 'text' )	{
							?>
							<script type="text/javascript">
							window.location = '<?php echo get_permalink( $instance['unavailable_action'] ); ?>';
							</script>
							<?php	
						}
					}
				} // if( isset( $dj_avail ) )
			} // if( isset( $_POST['mdjm_avail_submit'] ) ...
			
            /* We need the jQuery Calendar */
            wp_enqueue_script('jquery-ui-datepicker');
            wp_enqueue_style('jquery-ui-css', 'http://ajax.googleapis.com/ajax/libs/jqueryui/1.8.2/themes/smoothness/jquery-ui.css');
            ?>
            <script type="text/javascript">
            jQuery(document).ready(function($) {
                $('.custom_date').datepicker({
                dateFormat : '<?php f_mdjm_short_date_jquery(); ?>',
                altField  : '#check_date',
                altFormat : 'yy-mm-dd',
                firstDay: <?php echo get_option( 'start_of_week' ); ?>,
                changeYear: true,
                changeMonth: true
                });
            });
            </script>
            <form name="mdjm-availability-check" id="mdjm-availability-check" method="post">
            <p>
            <?php
			if( isset( $instance['intro'] ) && !empty( $instance['intro'] ) )	{
				if( !isset( $_POST['mdjm_avail_submit'] ) || $_POST['mdjm_avail_submit'] != $instance['submit_text'] )	{
					echo $instance['intro'] . '</p><p>';
				}
				elseif( $dj_avail !== false && $instance['available_action'] == 'text' && !empty( $instance['available_text'] ) )	{
					echo $instance['available_text'] . '</p><p>';
				}
				else	{
					echo $instance['unavailable_text'] . '</p><p>';	
				}
			}
			?>
            <label for="avail_date"><?php echo $instance['label']; ?></label>
            <input type="text" name="avail_date" id="avail_date" class="custom_date" placeholder="<?php f_mdjm_short_date_jquery(); ?>" />
            <input type="hidden" name="check_date" id="check_date" value="" /></p>
            <p<?php echo ( isset( $instance['submit_centre'] ) && $instance['submit_centre'] == 'Y' ? ' style="text-align:center"' : '' ); ?>>
            <input type="submit" name="mdjm_avail_submit" id="mdjm_avail_submit" value="<?php echo $instance['submit_text']; ?>" />
            </p>
            </form>
            <script type="text/javascript">
			jQuery(document).ready(function($){
				// Configure the field validator
				$('#mdjm-availability-check').validate(
					{
						rules:
						{
							avail_date: {
								required: true,
							},
						}, // End rules
						messages:
						{
							avail_date: {
									required: "Please enter a date",
									},
						}, // End messages
						// Classes
						errorClass: "mdjm-form-error",
						validClass: "mdjm-form-valid",
					} // End validate
				); // Close validate
			});
			</script>
            <?php
			
			echo $args['after_widget'];
		}
	
		/**
		 * Back-end widget form.
		 *
		 * @see WP_Widget::form()
		 *
		 * @param array $instance Previously saved values from database.
		 */
		public function form( $instance ) {
			$defaults = array( 
						'title'              => 'Availability Checker',
						'intro'              => 'Check my availability for your event by entering the date below',
						'label'              => 'Select Date:',
						'submit_text'        => 'Check Availability',
						'submit_centre'      => 'Y',
						'available_action'   => 'text',
						'available_text'     => 'Good news, we are available on the date you entered. Please contact us now',
						'unavailable_action' => 'text',
						'unavailable_text'   => 'Unfortunately we do not appear to be available on the date you selected. Why not try another date below...',
						
						);
			$instance = wp_parse_args( (array) $instance, $defaults );
			?>
			<p>
			<label for="<?php echo $this->get_field_id( 'title' ); ?>">Title:</label>
			<input id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" value="<?php echo $instance['title']; ?>" style="width:100%;" />
            </p>
            
            <p>
			<label for="<?php echo $this->get_field_id( 'intro' ); ?>">Intro Text:</label>
			<textarea id="<?php echo $this->get_field_id( 'intro' ); ?>" name="<?php echo $this->get_field_name( 'intro' ); ?>" style="width:100%;"><?php echo $instance['intro']; ?></textarea>
            </p>
            
            <p>
			<label for="<?php echo $this->get_field_id( 'label' ); ?>">Field Label:</label>
			<input id="<?php echo $this->get_field_id( 'label' ); ?>" name="<?php echo $this->get_field_name( 'label' ); ?>" value="<?php echo $instance['label']; ?>" style="width:100%;" />
            </p>
            
            <p>
			<label for="<?php echo $this->get_field_id( 'submit_text' ); ?>">Submit Button Label:</label>
			<input id="<?php echo $this->get_field_id( 'submit_text' ); ?>" name="<?php echo $this->get_field_name( 'submit_text' ); ?>" value="<?php echo $instance['submit_text']; ?>" style="width:100%;" />
            </p>
            
            <p>
			<label for="<?php echo $this->get_field_id( 'submit_centre' ); ?>">Centre Submit Button?</label>
			<input type="checkbox" id="<?php echo $this->get_field_id( 'submit_centre' ); ?>" name="<?php echo $this->get_field_name( 'submit_centre' ); ?>" value="Y"<?php checked( 'Y', $instance['submit_centre'] ); ?> />
            </p>
            
            <p>
			<label for="<?php echo $this->get_field_id( 'available_action' ); ?>">Redirect on Available:</label>
            <?php 
			wp_dropdown_pages( array(
									'selected'          => $instance['available_action'],
									'name'              => $this->get_field_name( 'available_action' ),
									'id'                => $this->get_field_id( 'available_action' ),
									'show_option_none'  => 'NO REDIRECT - USE TEXT',
									'option_none_value' => 'text',
									 ) );
			?>
            </p>
            
            <p>
			<label for="<?php echo $this->get_field_id( 'available_text' ); ?>">Available Text:</label>
			<textarea id="<?php echo $this->get_field_id( 'available_text' ); ?>" name="<?php echo $this->get_field_name( 'available_text' ); ?>" style="width:100%;"><?php echo $instance['available_text']; ?></textarea>
            </p>
            
            <p>
			<label for="<?php echo $this->get_field_id( 'unavailable_action' ); ?>">Redirect on Unavailable:</label>
            <?php 
			wp_dropdown_pages( array(
									'selected'          => $instance['unavailable_action'],
									'name'              => $this->get_field_name( 'unavailable_action' ),
									'id'                => $this->get_field_id( 'unavailable_action' ),
									'show_option_none'  => 'NO REDIRECT - USE TEXT',
									'option_none_value' => 'text',
									 ) );
			?>
            </p>
            
            <p>
			<label for="<?php echo $this->get_field_id( 'unavailable_text' ); ?>">Unavailable Text:</label>
			<textarea id="<?php echo $this->get_field_id( 'unavailable_text' ); ?>" name="<?php echo $this->get_field_name( 'unavailable_text' ); ?>" style="width:100%;"><?php echo $instance['unavailable_text']; ?></textarea>
            </p>
            
			<?php 
		}
	
		/**
		 * Sanitize widget form values as they are saved.
		 *
		 * @see WP_Widget::update()
		 *
		 * @param array $new_instance Values just sent to be saved.
		 * @param array $old_instance Previously saved values from database.
		 *
		 * @return array Updated safe values to be saved.
		 */
		public function update( $new_instance, $old_instance ) {
			$instance = array();
			$instance['title'] = ( !empty( $new_instance['title'] ) ) ? strip_tags( $new_instance['title'] ) : '';
			$instance['intro'] = ( !empty( $new_instance['intro'] ) ) ? strip_tags( $new_instance['intro'] ) : '';
			$instance['label'] = ( !empty( $new_instance['label'] ) ) ? strip_tags( $new_instance['label'] ) : '';
			$instance['submit_text'] = ( !empty( $new_instance['submit_text'] ) ) ? strip_tags( $new_instance['submit_text'] ) : '';
			$instance['submit_centre'] = ( !empty( $new_instance['submit_centre'] ) ) ? $new_instance['submit_centre'] : '';
			$instance['available_action'] = ( !empty( $new_instance['available_action'] ) ) ? strip_tags( $new_instance['available_action'] ) : '';
			$instance['available_text'] = ( !empty( $new_instance['available_text'] ) ) ? strip_tags( $new_instance['available_text'] ) : '';
			$instance['unavailable_action'] = ( !empty( $new_instance['unavailable_action'] ) ) ? strip_tags( $new_instance['unavailable_action'] ) : '';
			$instance['unavailable_text'] = ( !empty( $new_instance['unavailable_text'] ) ) ? strip_tags( $new_instance['unavailable_text'] ) : '';
	
			return $instance;
		}
	
	} // class MDJM_Availability_Widget

/****************************************************************
* CONTACT FORM WIDGET
****************************************************************/	
/*
* MDJM_ContactForms_Widget
* 08/01/2015
* @since 1.0
* Displays the contact form
*/
	class MDJM_ContactForms_Widget extends WP_Widget {
		/* Register the Widget */
		function __construct() {
			parent::__construct(
				'mdjm_contact_form_widget', /* Base ID */
				__( 'MDJM Contact Form', 'text_domain' ), /* Name */
				array( 'description' => __( 'Displays the specified contact form', 'text_domain' ), ) /* Args */
			);
		}
	
		/**
		 * Front-end display of widget.
		 *
		 * @see WP_Widget::widget()
		 *
		 * @param array $args     Widget arguments.
		 * @param array $instance Saved values from database.
		 */
		public function widget( $args, $instance )	{
			global $wpdb, $mdjm_options;
			
			echo $args['before_widget'];
			if ( !empty( $instance['title'] ) ) {
				echo $args['before_title'] . apply_filters( 'widget_title', $instance['title'] ). $args['after_title'];
			}
			
			if( !class_exists( 'MDJM_ContactForm' ) ) {
				require_once( WPMDJM_PLUGIN_DIR . '/includes/class/class-mdjm-contact-form.php' );
			}
			
			$mdjm_contact_form = new MDJM_ContactForm();
			
			if( isset( $_POST['mdjm_contact_form_submission'] ) && $_POST['mdjm_contact_form_submission'] == 'submitted' )	{	
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
				$email_body .= '<p><a href="'. admin_url( 'admin.php?page=mdjm-events&status=Unattended' ) . '">View Your Outstanding Enquiries</a></p>';
				$email_body .= '<hr />';
				$email_body .= '<p style="font-size:10px;">Form submitted at ' . date( $mdjm_options['time_format'] ) . ' on ' . date( 'l jS F Y' ) . '</p>';
				$email_body .= '<p style="font-size:9px; color:#F90; text-align:center">Powered by <a style="font-size:9px; color:#F90" href="http://www.mydjplanner.co.uk" target="_blank">' . WPMDJM_NAME . '</a>, version ' . WPMDJM_VERSION_NUM . '</p>';
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
						$mdjm_contact_form->mdjm_create_event( $user_id, $event_update, $_POST );
					}
					
					/* Send the client email if set */
						if( isset( $form['config']['copy_sender'] ) && $form['config']['copy_sender'] == 'Y' )	{
							$mdjm_contact_form->mdjm_client_email( $form, $client_email, $client_form_detail );
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
					}
				} // if( wp_mail )
				/* Error sending email */
				else	{
					?>
					<p style="color:<?php if( !empty( $form['config']['error_text_color'] ) ) { echo $form['config']['error_text_color']; } else { echo 'FF0000'; } ?>;">Sorry there was an error processing the contact form. Please try again.</p>
					<?php
				}
			} // if( isset( $_POST['mdjm_contact_form_submission'] )
			else	{
				$mdjm_forms = get_option( 'mdjm_contact_forms' );
				$layout = 0;
				
				if( !isset( $mdjm_forms[$instance['contact_form']] ) )	{
					echo '<p>ERROR: The selected form does not exist</p>';
				}
				else	{
					$fields = $mdjm_forms[$instance['contact_form']]['fields'];
					if( !empty( $instance['intro'] ) )	{
						echo '<p>' . $instance['intro'] . '</p>';
					}
					$mdjm_contact_form = new MDJM_ContactForm();
					
					$mdjm_contact_form->mdjm_form_header( $mdjm_forms[$instance['contact_form']] );
					$mdjm_contact_form->mdjm_display_form( '0',  $mdjm_forms[$instance['contact_form']], $fields );
				}
			}
			echo $args['after_widget'];
		}
	
		/**
		 * Back-end widget form.
		 *
		 * @see WP_Widget::form()
		 *
		 * @param array $instance Previously saved values from database.
		 */
		public function form( $instance ) {
			global $mdjm_options;
			$mdjm_forms = get_option( 'mdjm_contact_forms' );
			$defaults = array( 
						'title'              => $mdjm_options['company_name'] . ' Contact Form',
						'intro'              => 'Contact us using the form below',
						'contact_form'       => '--- Select a Form ---',						
						);
			$instance = wp_parse_args( (array) $instance, $defaults );
			?>
			<p>
			<label for="<?php echo $this->get_field_id( 'title' ); ?>">Title:</label>
			<input id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" value="<?php echo $instance['title']; ?>" style="width:100%;" />
            </p>
            
            <p>
			<label for="<?php echo $this->get_field_id( 'intro' ); ?>">Intro Text:</label>
			<textarea id="<?php echo $this->get_field_id( 'intro' ); ?>" name="<?php echo $this->get_field_name( 'intro' ); ?>" style="width:100%;"><?php echo $instance['intro']; ?></textarea>
            </p>
            
            <p>
			<label for="<?php echo $this->get_field_id( 'contact_form' ); ?>">Show Contact Form:</label>
			<select id="<?php echo $this->get_field_id( 'contact_form' ); ?>" name="<?php echo $this->get_field_name( 'contact_form' ); ?>" style="width:100%;">
            <?php
            if( !isset( $instance['contact_form'] ) || empty( $instance['contact_form'] ) )	{
				?><option value="">--- Select a Form ---</option><?php	
			}
            foreach( $mdjm_forms as $form )	{
				?><option value="<?php echo $form['slug']; ?>"<?php selected( $instance['contact_form'], $form['slug'] ); ?>><?php echo $form['name']; ?></option><?php
			}
			?>
            </select>
            </p>
			<?php 
		}
	
		/**
		 * Sanitize widget form values as they are saved.
		 *
		 * @see WP_Widget::update()
		 *
		 * @param array $new_instance Values just sent to be saved.
		 * @param array $old_instance Previously saved values from database.
		 *
		 * @return array Updated safe values to be saved.
		 */
		public function update( $new_instance, $old_instance ) {
			$instance = array();
			$instance['title'] = ( !empty( $new_instance['title'] ) ) ? strip_tags( $new_instance['title'] ) : '';
			$instance['intro'] = ( !empty( $new_instance['intro'] ) ) ? strip_tags( $new_instance['intro'] ) : '';
			$instance['contact_form'] = ( !empty( $new_instance['contact_form'] ) ) ? strip_tags( $new_instance['contact_form'] ) : '';
	
			return $instance;
		}
	
	} // class MDJM_ContactForms_Widget
?>