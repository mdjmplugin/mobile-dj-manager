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
            <p<?php if( isset( $instance['submit_centre'] ) && $instance['submit_centre'] == 'Y' ) echo ' align="center"'; ?>>
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
			global $mdjm_options;
			
			$mdjm_forms = get_option( 'mdjm_contact_forms' );
			$layout = 0;
			
			echo $args['before_widget'];
			
			if ( !empty( $instance['title'] ) ) {
				echo $args['before_title'] . apply_filters( 'widget_title', $instance['title'] ). $args['after_title'];
			}
			
			if( !isset( $mdjm_forms[$instance['contact_form']] ) )	{
				echo '<p>ERROR: The selected form does not exist</p>';
			}
			else	{
				$form = $mdjm_forms[$instance['contact_form']];
				$fields = $mdjm_forms[$instance['contact_form']]['fields'];
				if( !empty( $instance['intro'] ) )	{
					echo '<p>' . $instance['intro'] . '</p>';
				}
				wp_enqueue_script('jquery-ui-datepicker');
				wp_enqueue_style('jquery-ui-css', 'http://ajax.googleapis.com/ajax/libs/jqueryui/1.8.2/themes/smoothness/jquery-ui.css');
				?>
				<style>
				.mdjm-form-error {
					color: #<?php if( !empty( $form['config']['error_text_color'] ) ) { echo $form['config']['error_text_color']; } else echo 'FF0000'; ?>;
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
						
						if( !empty( $field['config']['input_class'] ) )	{
							echo ' class="' . $field['config']['input_class'] . '"';
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
							echo '<option value="' . str_replace( "\r\n", "", $option ) . '">' . str_replace( "\r\n", "", $option ) . '</option>' . "\n";
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
					echo '<input type="submit" "name="' . $field['slug'] . '" id="' . $field['slug'] . '" value="' . $field['name'] . '"';
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
				echo '</form>' . "\n";
				echo '<!-- End of MDJM Contact Form -->' . "\n";	
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