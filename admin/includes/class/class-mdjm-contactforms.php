<?php
/*
 * class-mdjm-contactforms.php
 * 22/04/2015
 * @since 1.1.3
 * The Contact Form
 */
	defined( 'ABSPATH' ) or die( "Direct access to this page is disabled!!!" );
	
	if ( !current_user_can( 'manage_options' ) ) 
		wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
	
	/* -- Build the Contact Form class -- */
	if( !class_exists( 'MDJM_ContactForms' ) )	{
		class MDJM_ContactForms	{
			/*
			 * The Contact Form constructor
			 *
			 *
			 */
			public function __construct()	{
				/* -- Create a new form with defaults -- */	
				if( isset( $_POST['submit'], $_POST['form_name'] ) && $_POST['submit'] == 'Begin Creating Form' )
					$this->create_form( $_POST['form_name'] );
				
				/* -- Save config -- */
				if( isset( $_POST['submit'], $_POST['form_id'] ) && $_POST['submit'] == 'Save Config' )
					$this->save_form_config();
				
				/* -- Form fields for a new form -- */
				if( isset( $_GET['action'] ) && $_GET['action'] == 'new_form' )
					$this->new_form_name();
				
				/* -- Add and/or Edit Fields -- */
				if( isset( $_POST['submit'], $_POST['form_id'] ) && $_POST['submit'] == 'Edit Field' || 
					isset( $_POST['submit'], $_POST['form_id'] ) && $_POST['submit'] == 'Add Field' )
					$this->manage_field();
				
				/* -- Delete Field -- */
				if( isset( $_GET['mdjm_action'], $_GET['field_id'], $_GET['form_id'] ) && $_GET['mdjm_action'] == 'delete_field' )
					$this->delete_field( $_GET['field_id'], $_GET['form_id'] );
				
				/* -- Delete form -- */
				if( isset( $_GET['action'], $_GET['form_id'] ) && $_GET['action'] == 'delete_form' )
					$this->delete_form( $_GET['form_id'] );
				
				/* -- Display form list -- */	
				if( !isset( $_GET['action'] ) || ( $_GET['action'] != 'edit_contact_form' && $_GET['action'] != 'new_form' ) )
					$this->list_forms();
					
				/* -- Edit form fields & configuration -- */
				if( isset( $_GET['action'], $_GET['form_id'] ) && $_GET['action'] == 'edit_contact_form' )
					$this->edit_form( $_GET['form_id'] );
			} // __construct
			
/*
 *
 * Data Methods
 *
 */
			
			/*
			 * Retrieve the forms
			 *
			 * @param
			 * @return		arr		$contact_forms	Array of contact form posts
			 *
			 */
			public function get_forms()	{
				$contact_forms = get_posts( array(
									'posts_per_page'	=> -1,
									'post_type'		 => MDJM_CONTACT_FORM_POSTS,
									'post_status'	   => 'publish',
									'order_by'		  => 'post_title',
									'order'			 => 'ASC',
									) );
									
				return $contact_forms;
			} // get_forms
			
			/*
			 * Retrieve the fields for the form
			 *
			 * @param		int		$form			The form post ID
			 * @return		arr		$form_fields	Array of contact form field posts
			 *
			 */
			public function get_fields( $form_id )	{
				$form_fields = get_posts( array(
											'posts_per_page'	=> -1,
											'post_type'		 => MDJM_CONTACT_FIELD_POSTS,
											'post_parent'	   => $form_id,
											'post_status'  	   => 'publish',
											'orderby'		  => 'menu_order',
											'order'			 => 'ASC',
											) );
				return $form_fields;
			} // get_fields
			
			/*
			 * Delete the given form
			 *
			 * @param		int		$form_id	The ID of the form to delete
			 * @return		none
			 *
			 */
			public function delete_form( $form_id )	{
				global $mdjm;
				if( empty( $form_id ) )	{
					if( MDJM_DEBUG == true )
						$mdjm->debug_logger( 'No form ID passed for deletion! ' . __METHOD__ );
					return mdjm_update_notice( 'error', 'No form to delete' );
				}
				
				$form_fields = $this->get_fields( $form_id );
				
				$delete = wp_delete_post( $form_id, true );
				if( $delete != false )	{
					if( MDJM_DEBUG == true )
						$mdjm->debug_logger( 'Contact form ' . esc_attr( $delete->post_title ) . ' has been deleted', true );
											
					foreach( $form_fields as $field )	{
						wp_delete_post( $field->ID, true );	
					}
					
					return mdjm_update_notice( 'updated', 'The Contact Form <strong>' . esc_attr( $delete->post_title ) . '</strong> has been successfully deleted' );
				}
					
				else
					return mdjm_update_notice( 'error', 'Could not delete the contact form' );
				
			} // delete_form
			
			/*
			 * Add or Edit fields within the form
			 *
			 * @param		none	Uses global $_POST vars
			 *				
			 * @return		none
			 *
			 */
			public function manage_field()	{
				global $mdjm, $mdjm_posts;
				
				/* -- Are we editing a field? -- */
				$edit_field = false;
				if( !empty( $_POST['field_id'] ) && $mdjm_posts->post_exists( $_POST['field_id'] ) )
					$edit_field = true;
					
				/* -- Validation -- */
				if( empty( $_POST['field_name'] ) )
					mdjm_update_notice( 'error', 'ERROR: No field name entered' );
				
				elseif( empty( $_POST['field_type'] ) )
					mdjm_update_notice( 'error', 'ERROR: No field type selected' );
					
				elseif( $_POST['field_type'] == 'select' && empty( $_POST['select_options'] ) )
					mdjm_update_notice( 'error', 'ERROR: When choosing a Select List field, you must enter some Selectable Options' );
					
				elseif( $_POST['field_type'] == 'select_multi' && empty( $_POST['select_options'] ) )
					mdjm_update_notice( 'error', 'ERROR: When choosing a Select List field, you must enter some Selectable Options' );
					
				elseif( $_POST['field_type'] == 'captcha' && !is_plugin_active( 'really-simple-captcha/really-simple-captcha.php' ) )
					mdjm_update_notice( 'error', 'ERROR: The CAPTCHA field type requires that you have the <strong>Really Simple CAPTCHA</strong> plugin installed and activated. <a href="' . admin_url( 'plugin-install.php?tab=search&s=really+simple+captcha' ) . '"> Download &amp; install the plugin here</a>' );
					
				/* -- Add or edit the field -- */
				else	{
					remove_action( 'save_post', array( $mdjm_posts, 'save_custom_post' ), 10, 2 );
					
					/* -- Prepare the data -- */
					$field_meta['type'] = sanitize_text_field( $_POST['field_type'] );
					
					/* Classes */
					if( isset( $_POST['label_class'] ) && !empty( $_POST['label_class'] ) )
						$field_meta['config']['label_class'] = sanitize_text_field( $_POST['label_class'] );
						
					if( isset( $_POST['input_class'] ) && !empty( $_POST['input_class'] ) )
						$field_meta['config']['input_class'] = sanitize_text_field( $_POST['input_class'] );
						
					/* Size */
					if( isset( $_POST['width'] ) && !empty( $_POST['width'] ) )
						$field_meta['config']['width'] = sanitize_text_field( $_POST['width'] );
					
					if( isset( $_POST['height'] ) && !empty( $_POST['height'] ) )
						$field_meta['config']['height'] = sanitize_text_field( $_POST['height'] );
					
					/* Field Mapping */
					$field_meta['config']['mapping'] = $_POST['mapping'];
					if( $_POST['field_type'] == 'email' )
						$field_meta['config']['mapping'] = 'user_email';
					
					/* Date Fields */
					if( $_POST['field_type'] == 'date' && isset( $_POST['datepicker'] ) && $_POST['datepicker'] == 'Y' )	
						$field_meta['config']['datepicker'] = 'Y';
						
					/* Checkbox Fields */
					if( $_POST['field_type'] == 'checkbox' )	{
						if( isset( $_POST['is_checked'] ) && $_POST['is_checked'] == 'Y' )	{
							$field_meta['config']['is_checked'] = 'Y';
						}
						if( isset( $_POST['checked_value'] ) && !empty( $_POST['checked_value'] ) )	{
							$field_meta['config']['checked_value'] = sanitize_text_field( $_POST['checked_value'] );
						}
						else	{
							$field_meta['config']['checked_value'] = 'Y';	
						}
					}
					
					/* Select List Fields */
					if( $_POST['field_type'] == 'select' || $_POST['field_type'] == 'select_multi' )
						$field_meta['config']['options'] = $_POST['select_options'];
						
					/* Event List First Entry */
					if( $_POST['field_type'] == 'event_list' && !empty( $_POST['event_list_first_entry'] ) )
						$field_meta['config']['event_list_first_entry'] = sanitize_text_field( $_POST['event_list_first_entry'] );
						
					/* Required Field */
					if( $field_meta['type'] == 'captcha' || $field_meta['type'] == 'submit' )
						$field_meta['config']['required'] = 'Y';
					if( isset( $_POST['required'] ) && $_POST['required'] == 'Y' )
						$field_meta['config']['required'] = 'Y';
						
					/* Placeholder Text */
					if( isset( $_POST['placeholder'] ) && !empty( $_POST['placeholder'] ) )
						$field_meta['config']['placeholder'] = sanitize_text_field( $_POST['placeholder'] );
						
					/* Submit Button */
					if( isset( $_POST['submit_align'] ) && $_POST['submit_align'] != '' )
						$field_meta['config']['submit_align'] = $_POST['submit_align'];
					
					/* -- Field position -- */
					if( $field_meta['type'] == 'captcha' )
						$order = 98;
					elseif( $field_meta['type'] == 'submit' )
						$order = 99;
					else
						$order = $_POST['position'];
					
					/* -- Existing Field Edit -- */
					if( $edit_field == true )	{
						$field = get_post( $_POST['field_id'] );
						
						/* -- Update the field name if necessary -- */
						if( $_POST['field_name'] != $field->post_title )	{
							wp_update_post( array( 
												'ID' => $_POST['field_id'],
												'post_title' => $_POST['field_name']
												) );
						}
						/* -- And the meta -- */
						update_post_meta( $_POST['field_id'], '_mdjm_field_config', $field_meta );
						mdjm_update_notice( 'updated', 'The <strong>' . sanitize_text_field( $_POST['field_name'] ) . '</strong> field updated' );
					}
					/* -- New Field -- */
					else	{
						$field_args = array(
									'post_title'	=> sanitize_text_field( $_POST['field_name'] ), // Field name
									'post_type'		=> MDJM_CONTACT_FIELD_POSTS,
									'post_status'	=> 'publish',
									'post_parent'	=> $_POST['form_id'],
									'menu_order'	=> $order,
									);
						$field_id = wp_insert_post( $field_args );
						
						/* -- New Field Meta -- */
						add_post_meta( $field_id, '_mdjm_field_config', $field_meta, true );
						mdjm_update_notice( 'updated', 'The <strong>' . sanitize_text_field( $_POST['field_name'] ) . '</strong> field added' );
					}
					add_action( 'save_post', array( $mdjm_posts, 'save_custom_post' ), 10, 2 );		
				}
			} // manage_field
			
			/*
			 * Delete the given field
			 *
			 * @param		int		$field_id	The ID of the form field to delete
			 *				int		$form_id	The ID of the form to which the field belongs
			 * @return		none
			 *
			 */
			public function delete_field( $field_id, $form_id )	{
				global $mdjm;
				
				if( empty( $field_id ) )	{
					if( MDJM_DEBUG == true )
						$mdjm->debug_logger( 'No field ID passed for deletion! ' . __METHOD__ );
					return mdjm_update_notice( 'error', 'No field to delete' );
				}
				
				$delete = wp_delete_post( $field_id, true );
				if( $delete != false )	{
					if( MDJM_DEBUG == true )
						$mdjm->debug_logger( 'Contact Form Field ' . esc_attr( $delete->post_title ) . ' has been deleted', true );
					
					wp_redirect( admin_url( 'admin.php?page=mdjm-contact-forms&action=edit_contact_form&form_id=' . $form_id . '&mdjm_message=3' ) );
					exit;
				}
					
				else
					return mdjm_update_notice( 'error', 'Could not delete the field' );
				
			} // delete_field

/*
 *
 * Save Methods
 *
 */
			/*
			 * Save the form configuration
			 *
			 * @param		int		$form_id		The ID of the form
			 * @return		none
			 *
			 */
			public function save_form_config()	{
				global $mdjm_posts, $mdjm_settings;
				
				/* -- Validation -- */
				if( empty( $_POST['email_from'] ) )	{
					mdjm_update_notice( 'error', 'ERROR: Configuration needs a From email address' );
				}
				if( empty( $_POST['email_from_name'] ) )	{
					mdjm_update_notice( 'error', 'ERROR: Configuration needs a From email display name' );
				}
				if( empty( $_POST['email_to'] ) )	{
					mdjm_update_notice( 'error', 'ERROR: Configuration needs a To email address' );
				}
				elseif( !filter_var( $_POST['email_from'], FILTER_VALIDATE_EMAIL ) ) {
					mdjm_update_notice( 'error', 'ERROR: Invalid From email address format' );
				}
				elseif( !filter_var( $_POST['email_to'], FILTER_VALIDATE_EMAIL ) ) {
					mdjm_update_notice( 'error', 'ERROR: Invalid To email address format' );
				}
				elseif( empty( $_POST['email_subject'] ) )	{
					mdjm_update_notice( 'error', 'ERROR: Enter a subject to be used in the email' );	
				}
				elseif( empty( $_POST['required_field_text'] ) )	{
					mdjm_update_notice( 'error', 'ERROR: Enter an error message to be displayed if a required field is not populated' );
				}
				elseif( isset( $_POST['display_message'] ) && $_POST['display_message'] == 'Y' && empty( $_POST['display_message_text'] ) )	{
					mdjm_update_notice( 'error', 'ERROR: You selected <strong>Display Message</strong> but did not enter a message to display to the user' );	
				}
				/* -- Continue with saving the config (post meta) -- */
				else	{
					/* -- Get the form post data as an array -- */
					$form = get_post( $_POST['form_id'] );
					
					/* -- Get the form meta data as an array -- */
					$form_meta = get_post_meta( $_POST['form_id'], '_mdjm_contact_form_config', true );
					
					$form_meta['email_from'] = sanitize_text_field( $_POST['email_from'] );
					$form_meta['email_from_name'] = sanitize_text_field( $_POST['email_from_name'] );
					$form_meta['email_to'] = sanitize_text_field( $_POST['email_to'] );
					$form_meta['email_subject'] = sanitize_text_field( $_POST['email_subject'] );
					
					if( !empty( $_POST['reply_to'] ) )
						$form_meta['reply_to'] = $_POST['reply_to'];
					
					if( !empty( $_POST['copy_sender'] ) )	
						$form_meta['copy_sender'] = $_POST['copy_sender'];
					
					if( !empty( $_POST['create_enquiry'] ) )
						$form_meta['create_enquiry'] = $_POST['create_enquiry'];
					else
						$form_meta['create_enquiry'] = false;
						
					if( !empty( $_POST['send_template'] ) && is_numeric( $_POST['send_template'] ) )
						$form_meta['send_template'] = $_POST['send_template'];
						
					if( isset( $_POST['update_user'] ) && $_POST['update_user'] == 'Y' )	{
						$form_meta['update_user'] = $_POST['update_user'];
					}
					else	{
						$form_meta['update_user'] = false;	
					}
					if( isset( $_POST['redirect'] ) && is_numeric( $_POST['redirect'] ) )	{
						$form_meta['redirect'] = $_POST['redirect'];
					}
					else	{
						$form_meta['redirect'] = false;	
					}
					if( isset( $_POST['display_message'] ) && $_POST['display_message'] == 'Y' )	{
						$form_meta['display_message'] = $_POST['display_message'];
						$form_meta['display_message_text'] = htmlentities( stripslashes( $_POST['display_message_text'] ) );
					}
					else	{
						$form_meta['display_message'] = false;	
					}
					
					$form_meta['required_field_text'] = sanitize_text_field( $_POST['required_field_text'] );
					$form_meta['required_asterix'] = !empty( $_POST['required_asterix'] ) ? true : false;
					
					$form_meta['error_text_color'] = ( empty( $_POST['error_text_color'] ) 
						? '#FF0000' : sanitize_text_field( $_POST['error_text_color'] ) );
						
					$form_meta['layout'] = ( $_POST['layout'] == 'not_set' ? '0_column' : $_POST['layout'] );
					
					if( !empty( $_POST['row_height'] ) && is_numeric( $_POST['row_height'] ) )
						$form_meta['row_height'] = $_POST['row_height'];
					
					/* -- Save the data -- */
					remove_action( 'save_post', array( $mdjm_posts, 'save_custom_post' ), 10, 2 );
					
					if( $_POST['form_name'] != $form->post_title )	{
						wp_update_post( array( 
											'ID' => $_POST['form_id'],
											'post_title' => $_POST['form_name']
											) );
					}
					
					if( update_post_meta( $_POST['form_id'], '_mdjm_contact_form_config', $form_meta ) )
						mdjm_update_notice( 'updated', 'Form Configuration Saved Successfully' );
						
					add_action( 'save_post', array( $mdjm_posts, 'save_custom_post' ), 10, 2 );
				}
			} // save_form_config

/*
 *
 * Display Methods
 *
 */
			/*
			 * Display the current contact form list
			 *
			 *
			 */
			public function list_forms()	{
				global $mdjm;
				?>
                <div class="wrap">
                <div id="icon-themes" class="icon32"></div>
                <h2>Contact Forms <a href="<?php echo admin_url( 'admin.php?page=mdjm-contact-forms&action=new_form' ); ?>" class="add-new-h2">Add New</a></h2>
                <hr />
                <table class="widefat" width="100%">
                <thead>
                <th class="row-title">Form Name</th>
                <th class="row-title">Shortcode</th>
                <th class="row-title">Fields</th>
                <th class="row-title">Action</th>
                </thead>
                <?php
                $forms = $this->get_forms();
                
                // No forms to display
                if( !$forms )	{
                    echo '<tr class="form-invalid">' . "\r\n" . 
                    '<td colspan="4">No Contact Forms exist yet. <a href="' . admin_url( 'admin.php?page=mdjm-contact-forms&action=new_form' ) . 
					'">Add one now</a></td>' . "\r\n" . 
                    '</tr>' . "\r\n";
                }
                // Display the forms
                else	{
                    $i = 0;
                    foreach( $forms as $form )	{
                        echo '<tr' . ( $i == 0 ? ' class="alternate"' : '' ) . '>' . "\r\n" . 
                        '<td><a href="' . admin_url( 'admin.php?page=mdjm-contact-forms&action=edit_contact_form&form_id=' . $form->ID ) . '"><strong>' 
						. esc_attr( $form->post_title ) . '</strong></a></td>' . "\r\n" .
                        '<td><code>[MDJM page="Contact Form" slug="' . $form->post_name . '"]</code></td>' . "\r\n" . 
                        '<td>' . count( $this->get_fields( $form->ID ) ) . '</td>' . "\r\n" . 
						( $mdjm->_mdjm_validation( 'check' ) ? 
                        '<td><a href="' . admin_url( 'admin.php?page=mdjm-contact-forms&action=edit_contact_form&form_id=' . $form->ID ) . 
                        '" class="button button-primary button-small">Edit</a>&nbsp;&nbsp;&nbsp;<a href="' . admin_url( 'admin.php?page=mdjm-contact-forms&action=delete_form&form_id=' . $form->ID ) . 
                        '" class="button button-secondary button-small">Delete</a></td>' . "\r\n"
						: '<td><a style="color:#a00" target="_blank" href="' . mdjm_get_admin_page( 'mydjplanner', 'str' ) . '">License Expired</a></td>' . "\r\n" ) . 
                        '</tr>' . "\r\n";
						$i++;
						if( $i == 2 )
							$i = 0;
                    }
                }
                ?>
                <tfoot>
                <th class="row-title">Form Name</th>
                <th class="row-title">Shortcode</th>
                <th class="row-title">Fields</th>
                <th class="row-title">Action</th>
                </tfoot>
                </table>
                </div>
                <?php
			} // list_forms
			/*
			 * Display the form to create a new contact form
			 *
			 * @param	none
			 * @return	none
			 */
			function new_form_name()	{
				global $mdjm;
				/* -- jQuery Validation -- */
				?>
				<script type="text/javascript">
				jQuery().ready(function()	{
					jQuery("#add_contact_form").validate(	{
						
						/* -- Classes -- */
						errorClass: "mdjm-form-error",
						validClass: "mdjm-form-valid",
						focusInvalid: false,
						
						/* -- Rules -- */
						rules:	{
						}, // End rules
						
						messages:	{
							form_name: " Enter a name for your new contact form",			
						}
						
					} ); // Validate
				} ); // function
				</script>
                <div class="wrap">
                <div id="icon-themes" class="icon32"></div>
                <h2>Add Contact Form</h2>
                <hr />
                <form name="add_contact_form" id="add_contact_form" method="post" action="<?php mdjm_get_admin_page( 'contact_forms' ); ?>">
                <input type="hidden" name="mdjm_action" id="mdjm_action" value="new_contact_form" />
                <table class="form-table">
                <tr>
                <th scope="row-title">Form Name:</th>
                <td><input type="text" name="form_name" id="form_name" class="regular-text required" value="<?php echo ( isset( $_POST['form_name'] ) ? $_POST['form_name'] : '' ); ?>" /></td>
                </tr>
                <tr>
                <td>&nbsp;</td>
                <td>
				<?php
				if( $mdjm->_mdjm_validation( 'check' ) )
					submit_button( 'Begin Creating Form', 'primary', 'submit', false, '' );
				else
					echo '<a style="color:#a00" target="_blank" href="' . mdjm_get_admin_page( 'mydjplanner', 'str' ) . '">License Expired</a>';
				?>
                </td>
                </tr>
                </table>
                </form>
                </div>
                <?php
			} // new_form_name
			
			/*
			 * Apply default configuration to the form
			 *
			 * @param	$form_id		int		The ID form to which the data should be applied
			 * @return					bool	True on success, otherwise false
			 *
			 */
			public function set_form_defaults( $form_id )	{
				global $mdjm;
				
				if( empty( $form_id ) )	{
					if( MDJM_DEBUG == true )
						$mdjm->debug_logger( 'ERROR: No contact form ID was provided in ' . __METHOD__ );
						
					return false;
				}
				
				$form_config['email_from'] = $mdjm_settings['email']['system_email'];
				$form_config['email_from_name'] = sanitize_text_field( MDJM_COMPANY );
				$form_config['email_to'] = $mdjm_settings['email']['system_email'];
				$form_config['reply_to'] = 'Y';
				$form_config['email_subject'] = sanitize_text_field( get_the_title( $form_id ) . ' form submission from ' . MDJM_COMPANY . ' website' );
				$form_config['copy_sender'] = 'N';
				$form_config['update_user'] = 'Y';
				$form_config['required_asterix'] = '1';
				$form_config['required_field_text'] = sanitize_text_field( '{FIELD_NAME} is a required field. Please try again.' );
				$form_config['error_text_color'] = sanitize_text_field( '#FF0000' );
				$form_config['layout'] = sanitize_text_field( '0_column' );
				
				if( add_post_meta( $form_id, '_mdjm_contact_form_config', $form_config, true ) 
					|| update_post_meta( $form_id, '_mdjm_contact_form_config', $form_config ) )	{
				
					if( MDJM_DEBUG == true )
						$mdjm->debug_logger( 'Form configuration defaults loaded for contact form ' . get_the_title( $form_id ) );
						
					return true;		
				}
				else	{
					if( MDJM_DEBUG == true )
						$mdjm->debug_logger( 'ERROR: Form configuration defaults could not be saved for ' . get_the_title( $form_id ) );
					return false;
				}
			} // set_form_defaults
			
			/*
			 * Create a new form with the name submitted
			 * and apply the default settings to the configuration
			 * 
			 *
			 * @param	$form_name		str		The name of the form as submitted by the user
			 * @return	$form_id		int		The post ID of the new form
			 *							bool	false on failure
			 *
			 */
			public function create_form( $form_name )	{
				global $mdjm, $mdjm_posts;
				
				if( empty( $form_name ) )	{
					if( MDJM_DEBUG == true )
						$mdjm->debug_logger( 'ERROR: No contact form name was provided in ' . __METHOD__ );
						
					return false;
				}
				
				/* -- Un-hook our save post actions -- */
				remove_action( 'save_post', array( $mdjm_posts, 'save_custom_post' ), 10, 2 );
				
				/* -- Create the new contact form -- */
				$post_args = array(
								'post_title'	    => sanitize_text_field( $form_name ), // Form name
								'post_type'		 => MDJM_CONTACT_FORM_POSTS,
								'post_status'	   => 'publish',
								'ping_status'	   => 'closed',
								'comment_status'	=> 'closed',
								);
								
				/* -- Insert the parent post & meta -- */
				$form_id = wp_insert_post( $post_args );
				
				if( $form_id )	{ // Success
					if( MDJM_DEBUG == true )
						$mdjm->debug_logger( 'Form ' . $form_name . ' created with ID ' . $form_id );
						
					/* -- Now add the form configuration meta -- */
					$defaults = $this->set_form_defaults( $form_id );
					if( $defaults == true )	{
						if( MDJM_DEBUG == true )
							$mdjm->debug_logger( 'Form ' . $form_name . ' configuration saved successfully' );	
					}
					else	{
						if( MDJM_DEBUG == true )
							$mdjm->debug_logger( 'ERROR: ' . $form_name . ' configuration could not be saved' );	
					}
				}
				else	{ // Failure
					if( MDJM_DEBUG == true )
						$mdjm->debug_logger( 'ERROR: Form ' . $form_name . ' could not be created' );
						
					mdjm_update_notice( 'error', 'The Contact Form could not be created' );
					$form_id = false;
				}
						
				/* -- Re-hook our save post actions -- */
				add_action( 'save_post', array( $mdjm_posts, 'save_custom_post' ), 10, 2 );
				
				if( $form_id != false )	{
					wp_redirect( admin_url( 'admin.php?page=mdjm-contact-forms&action=edit_contact_form&form_id=' . $form_id ) );
					exit;
				}
				
				return $form_id;
			} // create_form
 
 			/*
			 * Display the form fields & configuration for editing
			 *
			 * @param		int		$form_id	The ID of the form for editing
			 * 
			 *
			 */
			public function edit_form( $form_id )	{
				global $mdjm, $mdjm_settings;
				
				/* -- Messages -- */
				if( isset( $_GET['mdjm_message'] ) && !empty( $_GET['mdjm_message'] ) )
					$mdjm->mdjm_messages( '', $_GET['mdjm_message'] );
					
				/* -- Enable drag & drop -- */
				$this->drag_n_drop();
				
				/* -- Get the form & field data -- */
				$form = get_post( $form_id );
				$fields = $this->get_fields( $form_id );
				$config = get_post_meta( $form_id, '_mdjm_contact_form_config', true );
				
				/* -- Marry Field type values to nice names -- */
				$field_types = array(
									'text'         => 'Text Field',
									'date'         => 'Date Field',
									'time'         => 'Time Field',
									'email'        => 'Email Field',
									'select'       => 'Select List',
									'select_multi' => 'Select List (Multiple Select)',
									'event_list'   => 'Event Type List',
									'checkbox'     => 'Checkbox',
									'textarea'     => 'Textarea',
									'tel'          => 'Telephone Number',
									'url'          => 'URL',
									'captcha'      => 'CAPTCHA',
									'submit'       => 'Submit Button',
									);
				
				/* -- Marry up field types to Mappings */					
				$client_fields = get_option( MDJM_CLIENT_FIELDS );
				
				foreach( $client_fields as $client_field )	{				
					if( $client_field['display'] == true )	{
						$mappings_client[$client_field['id']] = 'Client ' . $client_field['label'];
					}
				}

				$mappings_event = array(
									'_mdjm_event_date'			=> 'Event Date',
									'mdjm_event_type'				=> 'Event Type',
									'_mdjm_event_start'			=> 'Event Start',
									'_mdjm_event_finish'		=> 'Event End',
									'_mdjm_event_notes'			=> 'Event Description',
									'_mdjm_event_venue_name'	=> 'Event Venue Name',
									'_mdjm_event_venue_town'	=> 'Event Venue Town/City',
									'_mdjm_event_venue_county'	=> 'Event County (State)'
									);
									
				$mappings = array_merge( $mappings_client, $mappings_event );
				
				/* Editing field */
				if( isset( $_GET['edit'], $_GET['field_id'] ) && $_GET['edit'] == 'Y' && !isset( $_POST['Edit Field'] ) )	{
					$e = get_post( $_GET['field_id'] );
					$e_meta = get_post_meta( $e->ID, '_mdjm_field_config', true );
					
				}
				?>
				<script type="text/javascript">
				function showDiv(elem){
					if(elem.value == 'text' || elem.value == 'textarea' || elem.value == 'tel' || elem.value == 'email' || elem.value == 'url')	{
						document.getElementById('placeholder_row').style.display = "block";
					}
					else	{
						document.getElementById('placeholder_row').style.display = "none";   
					}
					if(elem.value == 'text' || elem.value == 'textarea' )	{
						document.getElementById('width_row').style.display = "block";
					}
					else	{
						document.getElementById('width_row').style.display = "none";   
					}
					if(elem.value == 'textarea' )	{
						document.getElementById('height_row').style.display = "block";
					}
					else	{
						document.getElementById('height_row').style.display = "none";   
					}
					if(elem.value == 'date')	{
						document.getElementById('datepicker_row').style.display = "block";
					}
					else	{
						document.getElementById('datepicker_row').style.display = "none";   
					}
					if(elem.value == 'checkbox')	{
						document.getElementById('checkbox_row').style.display = "block";
					}
					else	{
						document.getElementById('checkbox_row').style.display = "none";   
					}
					if(elem.value == 'select' || elem.value == 'select_multi')	{
						document.getElementById('select_options_row').style.display = "block";
					}
					else	{
						document.getElementById('select_options_row').style.display = "none";   
					}
					if(elem.value == 'event_list')	{
						document.getElementById('event_list_first_entry_row').style.display = "block";
					}
					else	{
						document.getElementById('event_list_first_entry_row').style.display = "none";   
					}
					if(elem.value == 'submit')	{
						document.getElementById('align_submit_row').style.display = "block";
					}
					else	{
						document.getElementById('align_submit_row').style.display = "none";   
					}
				}
				function showExample(opt){
					if(opt.value == '4_column')	{
						document.getElementById('4_column_example').style.display = "block";
						document.getElementById('no_example').style.display = "none";
					}
					else	{
						document.getElementById('4_column_example').style.display = "none";   
					}
					if(opt.value == '2_column')	{
						document.getElementById('2_column_example').style.display = "block";
						document.getElementById('no_example').style.display = "none";
					}
					else	{
						document.getElementById('2_column_example').style.display = "none";
						document.getElementById('no_example').style.display = "none";
					}
					if(opt.value == '0_column')	{
						document.getElementById('0_column_example').style.display = "block";
						document.getElementById('no_example').style.display = "none";
					}
					else	{
						document.getElementById('0_column_example').style.display = "none";   
					}
					if(opt.value == 'not_set')	{
						document.getElementById('no_example').style.display = "block";
						document.getElementById('0_column_example').style.display = "none";
						document.getElementById('2_column_example').style.display = "none";
						document.getElementById('4_column_example').style.display = "none";
					}
					else	{
						document.getElementById('no_example').style.display = "none";
					}
				}
				function showDisplayText(){
					if (display_message.checked == 1){
						document.getElementById('success_message_row').style.display = "block";
					}
					else{
						document.getElementById('success_message_row').style.display = "none";	
					}
				}
				</script>
				<div class="wrap">
				<div id="icon-themes" class="icon32"></div>
				<h2><?php echo esc_attr( $form->post_title ); ?></h2>
				<hr />
				<table width="100%">
				<tr valign="top">
				<td width="65%">
				<form name="edit_form_fields" id="edit_form_fields" method="post" action="<?php echo admin_url( 'admin.php?page=mdjm-contact-forms' ) . '&action&action=edit_contact_form&form_id=' . $_GET['form_id']; ?>">
				<input type="hidden" name="form_id" id="form_id" value="<?php echo $form_id; ?>" />
				<table class="widefat<?php echo( !empty( $fields ) ? ' mdjm-list-item' : '' ); ?>">
				<thead>
				<th class="row-title"><?php echo __( 'Label' ); ?></th>
				<th class="row-title"><?php echo __( 'Field Type' ); ?></th>
				<th class="row-title"><?php echo __( 'Settings' ); ?></th>
				<th class="row-title">&nbsp;</th>
				</thead>
				<?php
				if( !empty( $fields ) )	{
					$i = 0;
					$position = 0;
					foreach( $fields as $field )	{
						if( isset( $f_config ) && $f_config['type'] != 'submit' && $f_config['type'] != 'captcha' )
							$position++;
							
						$f_config = get_post_meta( $field->ID, '_mdjm_field_config', true );
						if( $f_config['type'] == 'captcha' && !is_plugin_active( 'really-simple-captcha/really-simple-captcha.php' ) )	{
							mdjm_update_notice( 'error', 'ERROR: The CAPTCHA field type requires that you have the <strong>Really Simple CAPTCHA</strong> plugin installed and activated. <a href="' . admin_url( 'plugin-install.php?tab=search&s=really+simple+captcha' ) . '"> Download &amp; install the plugin here</a>' );	
						}
						?>
						<tr id="<?php echo 'fields_' . $field->ID; ?>"
						<?php 
							if( $f_config['type'] == 'captcha' && !is_plugin_active( 'really-simple-captcha/really-simple-captcha.php' ) ) 
								echo ' class="form-invalid" title="You do not have the Really Simple CAPTCHA plugin installed. This field will not work"'; 
								
							if( $f_config['type'] != 'submit' && $f_config['type'] != 'captcha' )
								echo ' class="' . ( $i == 0 ? 'alternate mdjm-list-item ' : 'mdjm-list-item' ) . '"'; ?>>
						<td><?php echo esc_attr( $field->post_title ); ?></th>
						<td><?php echo $field_types[$f_config['type']]; ?></td>
						<td><?php $this->form_icons( $f_config ); ?></td>                
						<td valign="middle"><a href="<?php echo admin_url( 'admin.php?page=mdjm-contact-forms&action=edit_contact_form&form_id=' . $form->ID . '&edit=Y&field_id=' . $field->ID ); ?>" class="button button-primary button-small">Edit</a>
							&nbsp;&nbsp;&nbsp;
						<?php submit_button( 'Delete', 'secondary small', 'submit', false, array( 
							'formmethod'	=> 'post',
							'formaction'	=> mdjm_get_admin_page( 'contact_forms' ) . '&mdjm_action=delete_field&field_id=' . $field->ID . '&form_id=' . $form->ID
							) ); ?>
						</td>
						</tr>
						<?php	
						$i++;
						if( $i == 2 )
							$i = 0;
						/* Only one email/event list/captcha/submit field type allowed */
						if( !isset( $_GET['edit'] ) || $_GET['edit'] != 'Y' )	{
							if( $f_config['type'] == 'email' || $f_config['type'] == 'event_list' || $f_config['type'] == 'captcha' || $f_config['type'] == 'submit' )	{
								unset( $field_types[$f_config['type']] );	
							}
						}
						/* If mapping in use, do not display again */
						if( isset( $f_config['config']['mapping'] ) && !empty( $f_config['config']['mapping'] ) && !isset( $_GET['edit'] )	 )	{
							unset( $mappings[$f_config['config']['mapping']] );
						}
					}
				}
				else	{
				?>
					<tr class="form-invalid">
					<td colspan="4">No fields have been added to this form yet</th>
					</tr>
					<?php
				}
				?>
				<tfoot>
				<th class="row-title"><?php echo __( 'Label' ); ?></th>
				<th class="row-title"><?php echo __( 'Field Type' ); ?></th>
				<th class="row-title"><?php echo __( 'Settings' ); ?></th>
				<th class="row-title">&nbsp;</th>
				</tfoot>
				</table>
				</td>
				<td valign="top">
		<?php /* Create Field Options */ ?>
				<?php 
				if( isset( $_GET['edit'], $_GET['field_id'] ) && $_GET['edit'] != 'Y' )	{
					echo '<input type="hidden" name="field_to_edit" id="field_to_edit" value="' . $_GET['field_id'] . '" />';
				}
				?>
				<table class="widefat" class="alternate">
				<input type="hidden" name="position" id="position" value="<?php echo ( isset( $position ) ? $position : '0' ); ?>" />
				<?php
				echo ( isset( $_GET['edit'], $_GET['field_id'] ) && $_GET['edit'] == 'Y' ? 
				'<input type="hidden" name="field_id" value="' . $_GET['field_id'] . '" />' . "\r\n" : '' );
				?>
				<tr>
				<td colspan="2" style="font-size:14px; font-weight:bold"><?php echo ( !isset( $_GET['edit'], $_GET['field_id'] ) || $_GET['edit'] != 'Y' ? 'Create Fields' : 'Edit ' . esc_attr( $e->post_title ) . ' Field' ); ?></td>
				</tr>
				<tr class="alternate">
				<td colspan="2"><p>Label:<br />
				&nbsp;&nbsp;&nbsp;<input type="text" name="field_name" id="field_name" class="required"<?php echo ( !empty( $e ) ? ' value="' . stripslashes( $e->post_title ) . '"' : '' ); ?> /></p>
				<p>Type:<br />
				&nbsp;&nbsp;&nbsp;<select name="field_type" id="field_type" onchange="showDiv(this)">
				<option value="">Select Field Type</option>
				<?php
					foreach( $field_types as $field_label => $field_name )	{
						if( isset( $e ) )	{
							?><option value="<?php echo $field_label; ?>"<?php selected( $field_label, $e_meta['type'] ); ?>><?php echo $field_name; ?></option><?php
						}
						elseif( isset( $_POST['field_type'] ) && !empty( $_POST['field_type'] ) )	{
							?><option value="<?php echo $field_label; ?>"<?php selected( $field_label, $_POST['field_type'] ); ?>><?php echo $field_name; ?></option><?php
						}
						else	{
							?><option value="<?php echo $field_label; ?>"><?php echo $field_name; ?></option><?php
						}
					}
				?>
				</select></p>
		
		<?php
		/*********************************
				HIDDEN DIVS
		*********************************/
		?>
				
		<?php /* Placeholder */ 
			$placeholder_types = array( 'text', 'textarea', 'email', 'url' );
		?>
				<div id="placeholder_row" style="display: <?php echo ( !empty( $e_meta ) && in_array( $e_meta['type'], $placeholder_types ) ? 'block;' : 'none;' ); ?> font-size:10px">
				<p>Placeholder text:&nbsp;&nbsp;&nbsp;<input type="text" name="placeholder" id="placeholder" class="regular-text" placeholder="(optional) Placeholder text is displayed like this"
					<?php echo( !empty( $e_meta['config']['placeholder'] ) ? ' value="' . esc_attr( $e_meta['config']['placeholder'] ) . '"' : '' ); ?> /></p>
				</div>
		<?php /* End Placeholder */ ?>
		
		<?php /* Width */ 
			$width_types = array( 'text', 'textarea' );
		?>
				<div id="width_row" style="display: <?php echo( !empty( $e_meta ) && in_array( $e_meta['type'], $width_types ) ? 'block;' : 'none;' ); ?> font-size:10px">
				<p>Field Width: (optional)&nbsp;&nbsp;&nbsp;<input type="text" name="width" id="width" class="small-text"
				<?php echo( !empty( $e_meta['config']['width'] ) ? ' value="' . esc_attr( $e_meta['config']['width'] ) . '"' : '' ); ?> /></p>
				</div>
		<?php /* End Width */ ?>
		
		<?php /* Height */ ?>
				<div id="height_row" style="display: <?php echo( !empty( $e_meta ) && $e_meta['type'] == 'textarea' ? 'block;' : 'none;' ); ?> font-size:10px">
				<p>Field Height: (optional)&nbsp;&nbsp;&nbsp;<input type="text" name="height" id="height" class="small-text"
				<?php echo( !empty( $e_meta['config']['height'] ) ? ' value="' . esc_attr( $e_meta['config']['height'] ) . '"' : '' ); ?> /></p>
				</div>
		<?php /* End Height */ ?>
		
		<?php /* Datepicker */ ?>
				<div id="datepicker_row" style="display: <?php echo( !empty( $e_meta ) && $e_meta['type']['date'] ? 'block;' : 'none;' ); ?> font-size:10px">
				<p>Use Datepicker?&nbsp;&nbsp;&nbsp;<input type="checkbox" name="datepicker" id="datepicker" value="Y" 
				<?php if( isset( $e_meta['config']['datepicker'] ) ) { checked( $e_meta['config']['datepicker'], 'Y' ); } else echo 'checked="checked"'; ?> /></p>
				</div>
		<?php /* End Datepicker */ ?>
		
		<?php /* Checkbox Options */ ?>
				<div id="checkbox_row" style="display: <?php echo( !empty( $e_meta ) && $e_meta['type'] == 'checkbox' ? 'block;' : 'none;' ); ?> font-size:10px">
				<p>Checked Value:<br />
				&nbsp;&nbsp;&nbsp;<input type="text" name="checked_value" id="checked_value" class="small-text" placeholder="Y"
				<?php echo( !empty( $e_meta['config']['checked_value'] ) ? ' value="' . $e_meta['config']['checked_value'] . '"' : '' ); ?> /></p>
				<p>Checked?&nbsp;&nbsp;&nbsp;<input type="checkbox" name="is_checked" id="is_checked" value="Y"
				<?php if( !empty( $e_meta['type'] ) && $e_meta['type'] == 'checkbox' ) { checked( 'Y', $e_meta['config']['is_checked'] ); } ?> /></p>
				</div>
		<?php /* End Checkbox Options */ ?>
		
		<?php /* Select Options */
			$select_types = array( 'select', 'select_multi' );
		?>
				<div id="select_options_row" style="display: <?php echo( !empty( $e_meta ) && in_array( $e_meta['type'], $select_types ) ? 'block;' : 'none;' ); ?> font-size:10px">
				<p>Selectable Options:<br />
				&nbsp;&nbsp;&nbsp;<textarea name="select_options" id="select_options" class="all-options" rows="5" placeholder="One per line">
				<?php echo( !empty( $e_meta['config']['options'] ) ? $e_meta['config']['options'] : '' ); ?></textarea></p>
				</div>
		<?php /* End Select Options */ ?>
		
		<?php /* Event List First Entry */ ?>
				<div id="event_list_first_entry_row" style="display: <?php echo( !empty( $e_meta ) && $e_meta['type'] == 'event_list' ? 'block;' : 'none;' ); ?> font-size:10px">
				<p>Event List First Entry:<br />
				&nbsp;&nbsp;&nbsp;<input type="text" name="event_list_first_entry" id="event_list_first_entry" class="regular-text" placeholder="i.e. Select Event Type"
				<?php echo( !empty( $e_meta['config']['event_list_first_entry'] ) ? ' value="' . esc_attr( $e_meta['config']['event_list_first_entry'] ) . '"' : '' ); ?> /></p>
				</div>
		<?php /* End Event List First Entry */ ?>
		
		<?php /* Submit Align */ ?>
				<div id="align_submit_row" style="display: <?php echo ( !empty( $e_meta ) && $e_meta['type'] == 'submit' ? 'block;' : 'none;' ); ?> font-size:10px">
				<p>Submit Button Alignment:<br />
				&nbsp;&nbsp;&nbsp;<select name="submit_align" id="submit_align">
				<option value=""<?php if( !empty( $e_meta['type']['config']['submit_align'] ) ) selected( $e_meta['type']['config']['submit_align'], '' ); ?>>None</option>
				<option value="left"<?php if( !empty( $e_meta['type']['config']['submit_align'] ) ) selected( $e_meta['type']['config']['submit_align'], 'left' ); ?>>Left</option>
				<option value="center"<?php if( !empty( $e_meta['type']['config']['submit_align'] ) ) selected( $e_meta['type']['config']['submit_align'], 'center' ); ?>>Centre</option>
				<option value="right"<?php if( !empty( $e_meta['type']['config']['submit_align'] ) ) selected( $e_meta['type']['config']['submit_align'], 'right' ); ?>>Right</option>
				</select></p>
				</div>
		<?php /* End Submit Align */ ?>
		
		<?php
		/*********************************
				END OF HIDDEN DIVS
		*********************************/
				if( isset( $_GET['edit'], $_GET['field_id'] ) && $_GET['edit'] == 'Y' )	{
					$selected = true;
					$req = ( !empty( $e_meta['config']['required'] ) ? $e_meta['config']['required'] : 'N' );
				}
				elseif( isset( $_POST['required'] ) && $_POST['required'] == 'Y' )	{
					$selected = true;
					$req = $_POST['required'];
				}
				else	{
					$selected = false;	
				}
		?>
				<p>Required?&nbsp;&nbsp;&nbsp;<input type="checkbox" name="required" id="required" value="Y"<?php if( $selected ) checked( 'Y', $req ); ?> /></p>
				<p>Label CSS Class: (optional)<br />
				&nbsp;&nbsp;&nbsp;<input type="text" name="label_class" id="label_class"
					<?php echo ( isset( $_GET['edit'], $_GET['field_id'] ) && $_GET['edit'] == 'Y' && !empty( $e_meta['config']['label_class'] ) 
					? ' value="' . esc_attr( $e_meta['config']['label_class'] ) . '"' : '' ); ?> /></p>
				<p>Input Field CSS Class: (optional)<br />
				&nbsp;&nbsp;&nbsp;<input type="text" name="input_class" id="input_class"
					<?php echo( isset( $_GET['edit'], $_GET['field_id'] ) && $_GET['edit'] == 'Y' && !empty( $e_meta['config']['input_class'] ) 
					? ' value="' . esc_attr( $e_meta['config']['input_class'] ) . '"' : '' ); ?> /></p>
				 <p>Map to Field:<br />&nbsp;&nbsp;&nbsp;
				 <select name="mapping" id="mapping">
				 <option value="none">No Mapping</option>
				 <?php
				 foreach( $mappings as $mapping => $mapping_name )	{
					?><option value="<?php echo $mapping; ?>"
						<?php 
						if( isset( $_GET['edit'], $_GET['field_id'] ) && $_GET['edit'] == 'Y' && !empty( $e_meta['config']['mapping'] ) ) { 
							selected( $mapping, $e_meta['config']['mapping'] );
						}
						?>><?php echo $mapping_name; ?></option><?php
				 }
				 ?>
				 </select>
				</td>
				</tr>
				<tr class="alternate">
				<td colspan="2">&nbsp;&nbsp;&nbsp;
				<?php
				if( !isset( $_GET['edit'], $_GET['field_id'] ) || $_GET['edit'] != 'Y' ) { 
					if( $mdjm->_mdjm_validation( 'check' ) )
						submit_button( 'Add Field', 'primary small', 'submit', false, '' );
					else
						echo '<a style="color:#a00" target="_blank" href="' . mdjm_get_admin_page( 'mydjplanner', 'str' ) . '">License Expired</a>';
				}
				elseif( $mdjm->_mdjm_validation( 'check' ) )	{
					submit_button( 'Edit Field', 'primary small', 'submit', false, '' ); 
					?>
					&nbsp;&nbsp;&nbsp;<a class="button button-secondary button-small" href="<?php echo admin_url( 'admin.php?page=mdjm-contact-forms&action=edit_contact_form&form_id=' . $form->ID ); ?>" class="add-new-h2">Cancel</a>
					<?php
				}
				else
					echo '<a style="color:#a00" target="_blank" href="' . mdjm_get_admin_page( 'mydjplanner', 'str' ) . '">License Expired</a>';
				?>
				</td>
		<?php /* End Create Field Options */ ?>
		
		<?php /* Example display */ ?>
				<tr>
				<td style="font-size:14px; font-weight:bold">Layout Example</td>
				</tr>
				<tr class="alternate">
				<td>
				
				<?php /* No Example */ ?>
				<div id="no_example" style="display: <?php echo ( !isset( $config['layout'] ) ? 'block;' : 'none;' ); ?> font-size:10px">
				<table>
				<tr>
				<td colspan="4">No display type is set</td>
				</tr>
				</table>
				</div>
				<?php /* End No Example */ ?>
				
				<?php /* 4 Column Example */ ?>
				<div id="4_column_example" style="display: <?php echo ( isset( $config['layout'] ) && $config['layout'] == '4_column' ? 'block;' : 'none;' ); ?> font-size:10px">
				<table>
				<tr>
				<td style="font-size:12px">First Name:</td>
				<td><input type="text" value="John" style="font-size:12px" /></td>
				<td style="font-size:12px">Last Name:</td>
				<td><input type="text" value="Smith" style="font-size:12px" /></td>
				</tr>
				<tr>
				<td style="font-size:12px">Email:</td>
				<td><input type="email" value="John@domain.com" style="font-size:12px" /></td>
				<td style="font-size:12px">Telephone:</td>
				<td><input type="tel" value="01234 567890" style="font-size:12px" /></td>
				</tr>
				<tr>
				<td colspan="4"><input type="button" value="Submit" style="font-size:12px" class="button-primary" /></td>
				</tr>
				<tr>
				<td style="font-size:10px" colspan="4">Ignore Font and form input size &amp; styling as these will match your theme's css</td>
				</tr>
				</table>
				</div>
				<?php /* End 4 Column Example */ ?>
				
				<?php /* 2 Column Example */ ?>
				<div id="2_column_example" style="display: <?php echo ( isset( $config['layout'] ) && $config['layout'] == '2_column' ? 'block;' : 'none;' ); ?> font-size:10px">
				<table>
				<tr>
				<td style="font-size:12px">First Name:</td>
				<td ><input type="text" value="John" style="font-size:12px" /></td>
				</tr>
				<tr>
				<td style="font-size:12px">Last Name:</td>
				<td><input type="text" value="Smith" style="font-size:12px" /></td>
				</tr>
				<tr>
				<td style="font-size:12px">Email:</td>
				<td><input type="email" value="John@domain.com" style="font-size:12px" /></td>
				</tr>
				<tr>
				<td style="font-size:12px">Telephone:</td>
				<td><input type="tel" value="01234 567890" style="font-size:12px" /></td>
				</tr>
				<tr>
				<td colspan="2"><input type="button" value="Submit" style="font-size:12px" class="button-primary" /></td>
				</tr>
				<tr>
				<td style="font-size:10px" colspan="2">Ignore Font and form input size &amp; styling as these will match your theme's css</td>
				</tr>
				</table>
				</div>
				<?php /* End 2 Column Example */ ?>
				
				<?php /* 0 Column Example */ ?>
				<div id="0_column_example" style="display: <?php echo ( isset( $config['layout'] ) && $config['layout'] == '0_column' ? 'block;' : 'none;' ); ?> font-size:10px">
				<p style="font-size:12px">First Name:<br />
				<input type="text" value="John" style="font-size:12px" /></p>
				<p style="font-size:12px">Last Name:<br />
				<input type="text" value="Smith" style="font-size:12px" /></p>
				<p style="font-size:12px">Email:<br />
				<input type="email" value="John@domain.com" style="font-size:12px" /></p>
				<p style="font-size:12px">Telephone:<br />
				<input type="tel" value="01234 567890" style="font-size:12px" /></p>
				<p style="font-size:10px"><input type="button" value="Submit" style="font-size:12px" class="button-primary" /></p>
				<p style="font-size:10px">Ignore Font and form input size &amp; styling as these will match your theme's css</p>
				</div>
				<?php /* End 0 Column Example */ ?>
				
				</td>
				</tr>
				</table>
				</td>
				</tr>
				</table>
				</form>
		<?php /* End Example Display */ ?>
		<?php /* Configuration Options */ ?>
				<hr />
				<h2>Configuration</h2>
				<form name="form_config" id="form_config" method="post" action="<?php echo admin_url( 'admin.php?page=mdjm-contact-forms' ) . '&action&action=edit_contact_form&form_id=' . $form->ID; ?>">
				<input type="hidden" name="form_id" id="form_id" value="<?php echo $form->ID; ?>" />
				<table class="form-table">
				<tr>
				<th scope="row"><label for="form_name">Form Name:</label></th>
				<td><input type="text" name="form_name" id="form_name" class="regular-text" value="<?php echo esc_attr( $form->post_title ); ?>" /> <span class="description">The name of the Contact Form</span></td>
				</tr>
				<tr>
				<th scope="row"><label for="email_from_name">Email From Name:</label></th>
				<td><input type="text" name="email_from_name" id="email_from_name" class="regular-text" value="<?php echo ( isset( $config['email_from_name'] ) ? esc_attr( $config['email_from_name'] ) : esc_attr( WPMDJM_CO_NAME ) ); ?>" /> <span class="description">The display name you want to use in the email From field</span></td>
				</tr>
				<tr>
				<th scope="row"><label for="email_from">Email From Address:</label></th>
				<td><input type="email" name="email_from" id="email_from" class="regular-text" value="<?php echo ( isset( $config['email_from'] ) ? $config['email_from'] : $mdjm_settings['email']['system_email'] ); ?>" /> <span class="description">The email address that the email should be sent from. Should be a valid address</span></td>
				</tr>
				<tr>
				<th scope="row"><label for="email_to">Email To:</label></th>
				<td><input type="email" name="email_to" id="email_to" class="regular-text" value="<?php echo ( isset( $config['email_to'] ) ? $config['email_to'] : $mdjm_settings['email']['system_email'] ); ?>" /> <span class="description">The email address to which the enquiry should be sent</span></td>
				</tr>
				<tr>
				<th scope="row"><label for="email_subject">Email Subject:</label></th>
				<td><input type="text" name="email_subject" id="email_subject" class="regular-text" value="<?php echo ( isset( $config['email_subject'] ) ? esc_attr( $config['email_subject'] ) : esc_attr( $form->post_title . ' form submission from ' . MDJM_COMPANY . ' website' ) ); ?>" /> <span class="description">The subject to be used in the email</span></td>
				</tr>
				<tr>
				<th scope="row"><label for="reply_to">Reply to sender?</label></th>
				<td>
				<?php
				if( isset( $config['reply_to'] ) && $config['reply_to'] == 'Y' )	{
					$check = 'Y';
				}
				elseif ( $config['reply_to'] != 'Y' )	{
					$check = 'N';	
				}
				else	{
					$check = 'N';	
				}
				?>
				<input type="checkbox" name="reply_to" id="reply_to" value="Y"<?php checked( 'Y', $check ); ?> /> <span class="description">Do you want to be able to reply to the sender by clicking Reply within the email?</span></td>
				</tr>
				<tr>
				<th scope="row"><label for="copy_sender">Copy Sender?</label></th>
				<td><input type="checkbox" name="copy_sender" id="copy_sender" value="Y"<?php if( isset( $config['copy_sender'] ) ) checked( 'Y', $config['copy_sender'] ); ?> /> <span class="description">Send a copy of the message to the sender. If you select a template below, they will receive the template. Otherwise, they will receive a copy of their form</span></td>
				</tr>
				<th scope="row">On Submit:</th>
				<td>
				<table class="form-table">
				<tr>
				<th scope="row"><label for="create_enquiry">Create Enquiry?</label></th>
				<td><input type="checkbox" name="create_enquiry" id="create_enquiry" value="Y"<?php if( isset( $config['create_enquiry'] ) ) checked( 'Y', $config['create_enquiry'] ); ?> /> <span class="description">Creates a new event enquiry</span></td>
				</tr>
				<?php
				$email_templates = get_posts( array( 	
												'post_type' => MDJM_EMAIL_POSTS,
												'orderby' => 'post_title',
												'order' => 'ASC',
												'numberposts' => -1,
											) );
				?>
				<tr>
				<th scope="row"><label for="send_template">Reply with Template?</label></th>
				<td><select name="send_template" id="send_template">
				<option value=""<?php echo ( empty( $config['send_template'] ) ? ' selected="selected"' : '' ); ?>>No</option>
				<?php
				if( $email_templates ) {
					foreach( $email_templates as $email_template ) {
						echo '<option value="' . $email_template->ID . '"';
						if( !empty( $config['send_template'] ) )
							selected( $email_template->ID, $config['send_template'] );
						echo '>' . esc_attr( $email_template->post_title ) . '</option>' . "\r\n";
					}
				}
				?>
				</select> <span class="description"> Select a template if you want an instant response to the client to be generated on form submission. <strong>No Shortcodes</strong></span></td>
				</tr>
				<th scope="row"><label for="update_user">Update Existing Users?</label></th>
				<td><input type="checkbox" name="update_user" id="update_user" value="Y"<?php if( isset( $config['update_user'] ) ) checked( 'Y', $config['update_user'] ); ?> /> <span class="description">If the user exists (based on email address) update their information with any mapped fields</span></td>
				</tr>
				<tr>
				<th scope="row"><label for="redirect">Redirect User?</label></th>
				<?php
				$args = array(
							'name'              => 'redirect',
							'id'                => 'redirect',
							'sort_order'        => 'ASC',
							'post_type'         => 'page',
							'show_option_none'  => 'No Redirect',
							'option_none_value' => 'no_redirect', 
							);
				if( isset( $config['redirect'] ) )	{
					$args['selected'] = $config['redirect'];
				}
				?>
				<td><?php wp_dropdown_pages( $args ); ?> <span class="description">Redirects user to selected page on successful form submission. Overides <span class="code">Display Message</span></span></td>
				</tr>
				<tr>
				<th scope="row"><label for="display_message">Display Message?</label></th>
				<td><input type="checkbox" name="display_message" id="display_message" value="Y" onclick="showDisplayText()"<?php if( isset( $config['display_message'] ) ) checked( 'Y', $config['display_message'] ); ?> /> <span class="description">Text to be displayed to the user when the form is successfully submitted. Only valid if <span class="code">Redirect User</span> is not selected</span></td>
				</tr>
				</table>
		<?php
		/*********************************
				HIDDEN DIVS
		*********************************/
		?>
				
		<?php /* Display Message */ ?>
				<?php
				if( isset( $config['display_message'] ) && $config['display_message'] == 'Y' )	{
					$display = 'block';
				}
				else	{
					$display = 'none';	
				}
				?>
				<div id="success_message_row" style="display: <?php echo $display; ?>; font-size:10px">
				<?php
				$mce_settings = array(
									'textarea_rows' => 6,
									'media_buttons' => false,
									'textarea_name' => 'display_message_text',
									'teeny'         => false,
									);
				$content = '';
				if( isset( $config['display_message_text'] ) )	{
					$content = $config['display_message_text'];	
				}
				?>
				<table class="form-table">
				<tr>
				<th scope="row"><label for="display_message_text">Message:</label></th>
				<td><?php wp_editor( html_entity_decode( $content ), 'display_message_text', $mce_settings ); ?></td>
				</tr>
				</table>
				</div>
		<?php /* End Display Message */ ?>       
				</td>
				</tr>
                <tr>
                <th scope="row"><label for="required_asterix">Indicate Required Field?</label></th>
                <td><input type="checkbox" name="required_asterix" id="required_asterix" value="1" 
					<?php if( !empty( $config['required_asterix'] ) ) echo ' selected="selected"'; ?> 
                    /> <span class="description">Select to display a <span style="color: red; font-weight: bold;">*</span> next to a required field</span></td>
                </tr>
				<tr>
				<th scope="row"><label for="required_field_text">Error Message:</label></th>
				<?php
				if( !empty( $config['required_field_text'] ) )	{
					$value = $config['required_field_text'];
				}
				elseif( isset( $_POST['required_field_text'] ) )	{
					$value = $_POST['required_field_text'];
				}
				else	{
					$value = '{FIELD_NAME} is a required field. Please try again.';
				}
				?>
				<td><input type="text" name="required_field_text" id="required_field_text" class="regular-text" value="<?php echo esc_attr( $value ); ?>" /> <span class="description">Text to be displayed if a required field is not completed. Use the <span class="code">{FIELD_NAME}</span> shortcode to output the missing field name</span></td>
				</tr>
				<tr>
				<th scope="row"><label for="error_text_color">Error Text Colour:</label></th>
				<?php
				if( !empty( $config['error_text_color'] ) )	{
					$value = $config['error_text_color'];
				}
				elseif( isset( $_POST['error_text_color'] ) )	{
					$value = $_POST['error_text_color'];
				}
				else	{
					$value = '#FF0000';
				}
				?>
				<td><input type="text" name="error_text_color" id="error_text_color" class="mdjm-colour-field" data-default-color="#FF0000" value="<?php echo $value; ?>" /><div id="colorpicker"></div> <span class="description">The colour in which error message text should be displayed. Default is <span class="code">#FF0000</span> <font style="color:#FF0000">(Red)</font></span></td>
				</tr>
				<tr>
				<th scope="row"><label for="layout">Form Layout:</label></th>
				<td><select name="layout" id="layout" onchange="showExample(this)" />
				<option value="not_set"<?php if( isset( $config['layout'] ) ) selected( 'not_set', $config['layout'] ); ?>>Not Set</option>
				<option value="4_column"<?php if( isset( $config['layout'] ) ) selected( '4_column', $config['layout'] ); ?>>4 Column Table</option>
				<option value="2_column"<?php if( isset( $config['layout'] ) ) selected( '2_column', $config['layout'] ); ?>>2 Column Table</option>
				<option value="0_column"<?php if( isset( $config['layout'] ) ) selected( '0_column', $config['layout'] ); ?>>No Table</option>
				</select> <span class="description">Select how you want the form to be displayed on your page. <span class="code">Not Set</span> will default to <span class="code">4 Column Table</span> layout</span>
				</td>
				</tr>
				<tr>
				<th scope="row"><label for="row_height">Table Row Height:</label></th>
				<td><input type="text" name="row_height" id="row_height" class="small-text" value="<?php echo ( !empty( $config['row_height'] ) ? $config['row_height'] : '' ); ?>" /> <span class="description">Adjust the table row height as required (optional - applies to table layout only)</span></td>
				</tr>
				<tr>
				<td colspan="2">
				<?php
				if( $mdjm->_mdjm_validation( 'check' ) )
					submit_button( 'Save Config', 'primary', 'submit', false, '' );
				else
					echo '<a style="color:#a00" target="_blank" href="' . mdjm_get_admin_page( 'mydjplanner', 'str' ) . '">License Expired</a>';
				?>
                </td>
				</tr>
				</table>
				</form>
		<?php /* End Configuration Options */ ?>
				</div>
				<?php
				
			} // edit_form
 
			/*
			 * Display the form field icons
			 *
			 * @param		arr		$field		The field config as an array
			 * @return		none
			 *
			 */
			public function form_icons( $field )	{
				global $mdjm;
				
				$dir = WPMDJM_PLUGIN_URL . '/admin/images/contact-form-icons';
				
				$mappings_default = array(
								'first_name'           		 => 'Client First Name',
								'last_name'            	  => 'Client Last Name',
								'user_email'           		 => 'Client Email Address',
								'phone1'               		 => 'Client Telephone',
								'user_pass'            	  => 'Client Password',
								'_mdjm_event_date'     	   => 'Event Date',
								'mdjm_event_type'      		=> 'Event Type',
								'_mdjm_event_start'    	  => 'Event Start',
								'_mdjm_event_finish'   		 => 'Event End',
								'_mdjm_event_notes'    	  => 'Event Description',
								'_mdjm_dj_list'			  => 'DJ List',
								'_mdjm_event_venue_name'     => 'Event Venue Name',
								'_mdjm_event_venue_town'     => 'Event Venue Town/City',
								'_mdjm_event_venue_county'   => 'Event County (State)'
								);
				
				$mappings_custom = array();				
				$client_fields = get_option( MDJM_CLIENT_FIELDS );
				foreach( $client_fields as $client_field )	{
					if( !empty( $client_field['display'] ) )	{
						$mappings_custom[$client_field['id']] = 'Client ' . $client_field['label'];
					}
				}
				
				$mappings = array_merge( $mappings_default, $mappings_custom );
				
				if( isset( $field['config']['required'] ) && $field['config']['required'] == 'Y' )	{
					?><img src="<?php echo $dir; ?>/req_field.jpg" width="14" height="14" alt="Required Field" title="Required Field" />&nbsp;<?php
				}
				else	{
					echo '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';	
				}
				if( isset( $field['config']['datepicker'] ) && $field['config']['datepicker'] == 'Y' )	{
					?><img src="<?php echo $dir; ?>/datepicker.jpg" width="14" height="14" alt="Datepicker" title="Datepicker" />&nbsp;<?php
				}
				else	{
					echo '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';	
				}
				if( $field['type'] == 'select' || $field['type'] == 'select_multi' || $field['type'] == 'event_list' )	{
					if( $field['type'] == 'event_list' )	{
						$opt = '';
						if( !empty( $field['config']['event_list_first_entry'] ) )	{
							$opt .= $field['config']['event_list_first_entry'] . "\r\n";
						}
						$event_types = $mdjm->mdjm_events->get_event_types();
						foreach( $event_types as $event_type )	{
							$opt .= stripslashes( $event_type->name ) . "\r\n";
						}
					}
					elseif( $field['type'] == 'dj_list' )	{
						$opt = '';
						if( !empty( $field['config']['dj_list_first_entry'] ) )	{
							$opt .= $field['config']['dj_list_first_entry'] . "\r\n";
						}
						$djs = f_mdjm_get_djs();
						asort( $djs );
						foreach( $djs as $dj )	{
							$opt .= $dj;	
						}
					}
					else	{
						$opt = $field['config']['options'];
					}
					?><img src="<?php echo $dir; ?>/select_list.jpg" width="14" height="14" alt="Select List Options" title="<?php echo $opt; ?>" />&nbsp;<?php
				}
				else	{
					echo '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';	
				}
				if( isset( $field['config']['mapping'] ) && $field['config']['mapping'] != 'none' )	{
					?><img src="<?php echo $dir; ?>/mapping.jpg" width="14" height="14" alt="Maps to <?php echo $mappings[$field['config']['mapping']]; ?>" title="Maps to <?php echo $mappings[$field['config']['mapping']]; ?>" />&nbsp;<?php
				}
				else	{
					echo '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';	
				}
				if( isset( $field['type'] ) && $field['type'] == 'captcha' )	{
					?><img src="<?php echo $dir; ?>/captcha.jpg" width="14" height="14" alt="CAPTCHA Validation Field" title="CAPTCHA Validation Field" />&nbsp;<?php
				}
				else	{
					echo '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';	
				}		
			} // form_icons
			
			/*
			 * Enqueue scripts for drag and drop functionality
			 *
			 * @param		none
			 * @return		none
			 *
			 */
			public function drag_n_drop()	{
				wp_enqueue_script( 'jquery-ui-sortable' );
				wp_enqueue_script( 'update-order', WPMDJM_PLUGIN_URL . '/admin/includes/js/mdjm-order-list.js' );
			} // drag_n_drop
			
		} // class
	}

/*
 *
 * INSIANTIATE THE CLASS
 *
 */	
 	if( isset( $_GET['upgrade'] ) && $_GET['upgrade'] == 'Y' )	{
		if( !class_exists( 'MDJM_Upgrade' ) )	{
			include( MDJM_PLUGIN_DIR . '/admin/includes/procedures/mdjm-upgrade.php' );
			$mdjm_upgrade = new MDJM_Upgrade();
		}
		$mdjm_upgrade->migrate_cron_tasks();
		exit;	
	}
	
	/* -- The page & actions -- */
	if( class_exists( 'MDJM_ContactForms' ) )	{	
		/* -- Instantiate the plugin class -- */
		$mdjm_contact_forms = new MDJM_ContactForms();
	}	
	?>