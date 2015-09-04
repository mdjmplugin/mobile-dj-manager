<?php
/*
 * class-profile.php
 * 16/06/2015
 * @since 2.1
 * The ClientZone Profile class
 * 
 */
	
	defined('ABSPATH') or die("Direct access to this page is disabled!!!");
	
	/* -- Build the MDJM_Profile class -- */
	if( !class_exists( 'MDJM_Profile' ) )	{
		require_once( 'class-clientzone.php' );
		class MDJM_Profile extends ClientZone 	{
			/*
			 * The Constructor
			 *
			 *
			 *
			 */
			function __construct()	{
				
				mdjm_page_visit( MDJM_APP . ' Profile' );
				
				if( !is_user_logged_in() )
					parent::login();
					
				else	{
					// We need the custom fields
					$this->fields = get_option( MDJM_CLIENT_FIELDS );
					foreach( $this->fields as $key => $row )	{
						$field[$key] = $row['position'];	
					}
					// Sort the fields into a positional array
					array_multisort( $field, SORT_ASC, $this->fields );
										
					$this->profile_header();
					$this->display_profile();
					$this->profile_footer();	
				}
				
			} // __construct
			
			/*
			 * Update the users profile
			 *
			 *
			 *
			 */
			function update_profile()	{
				global $my_mdjm, $mdjm_debug;
				
				$mdjm_debug->log_it( 'Starting user profile update for user ' . $my_mdjm['me']->display_name, true );
				
				// Firstly, our security check
				if( !isset( $_POST['__mdjm_user'] ) || !wp_verify_nonce( $_POST['__mdjm_user'], 'manage_client_profile' ) )	{
					$mdjm_debug->log_it( 'Security verification failed during update. No update occured', false );
					return parent::display_message( 4, 4 );	
				}
					
				else	{
					// Set our variables for updating
					$update_fields = array ( 'ID' => $my_mdjm['me']->ID );
					$update_meta = array ();
					
					// Process the standard fields
					$update_fields['first_name'] = sanitize_text_field( ucfirst( $_POST['first_name'] ) );
					$update_fields['last_name'] = sanitize_text_field( ucfirst( $_POST['last_name'] ) );
					$update_fields['user_email'] = sanitize_email( $_POST['user_email'] );
					
					// Now the custom fields
					foreach( $this->fields as $field )	{
						if( !isset( $field['required'] ) || empty( $field['display'] ) )
							continue;
							
						if( $field['type'] == 'text' || $field['type'] == 'dropdown' )
							$update_meta[$field['id']] = ( !empty( $_POST[$field['id']] ) ? sanitize_text_field( $_POST[$field['id']] ) : '' );
								
						if( $field['type'] == 'checkbox' )
							$update_meta[$field['id']] = ( !empty( $_POST[$field['id']] ) ? $_POST[$field['id']] : '0' );
					}
					
					// Password Reset Validation if required
					if ( !empty( $_POST['new_password'] ) && $_POST['new_password'] != $_POST['new_password_confirm'] )
						$pass_error = true;

					if ( !empty( $_POST['new_password'] ) && $_POST['new_password'] == $_POST['new_password_confirm'] )
						$update_fields['user_pass'] = $_POST['new_password'];
				
					// Process field updates starting with custom fields
					foreach ( $update_meta as $meta_key => $meta_value ) {
						if( update_user_meta ( $my_mdjm['me']->ID, $meta_key, $meta_value ) )
							$mdjm_debug->log_it( 'Success: User profile field ' . $meta_key . ' updated with value ' . $meta_value, false );
							
						else
							$mdjm_debug->log_it( 'Failure: User profile field ' . $meta_key . ' could not be updated with value ' . $meta_value, false  );
					}
					
					// And now built-in fields
					$user_id = wp_update_user ( $update_fields );
					
					// If we changed the password, we need to logout
					if( isset( $update_fields['user_pass'] ) )	{
						$mdjm_debug->log_it( 'User password was changed. Logging user out', false  );
						wp_logout();
						wp_redirect( get_permalink( MDJM_PROFILE_PAGE ) );
					}		
					
					// We're done
					if( is_wp_error( $user_id ) )
						
						parent::display_notice(
												4,
												'Unable to update your profile. ' . $user_id->get_error_message()
												);
					else
						parent::display_notice(
												2,
												'Your profile has been updated successfully'
												);
					if( isset( $pass_error ) && $pass_error == true )
						parent::display_notice(
												4,
												'Unable to change your password. Check the password\'s you entered match!'
												);
				}
				
			} // update_profile
			
			/*
			 * Begin the HTML output for the profile display
			 *
			 *
			 *
			 */
			function profile_header()	{
				global $mdjm;
				
				// Are we processing an update?
				if( isset( $_POST['submit'] ) )
					$this->update_profile();
				
				// Create validation code
				$this->validation_script();
				
				$default_text = __( '<p>Please keep your details up to date as incorrect information may cause problems with your event.</p>' );
				
				echo parent::__text( 'profile_intro', $default_text );
				
				echo '<form action="' . $mdjm->get_link( MDJM_PROFILE_PAGE, false ) . '" method="post" enctype="multipart/form-data" name="mdjm-user-profile" id="mdjm-user-profile">' . "\r\n";
				// For security
				wp_nonce_field( 'manage_client_profile', '__mdjm_user' ) . "\r\n";
				 
				echo '<div id="mdjm-user-profile-container">' . "\r\n";
					echo '<div id="mdjm-user-profile-table">' . "\r\n";
					echo '<table class="mdjm-user-profile-display">' . "\r\n";
				
			} // profile_header
			
			/*
			 * End the HTML output for the profile display
			 *
			 *
			 *
			 */
			function profile_footer()	{
				// Add the submit and reset buttons
				echo '<tr>' . "\r\n";
				echo '<td><input name="submit" type="submit" value="Update Profile" /></td>' . "\r\n";
				echo '<td><input name="reset" type="reset" value="Reset Values" /></td>' . "\r\n";
				echo '</tr>' . "\r\n";
				
				// End the HTML
				echo '</table>' . "\r\n";
				echo '</div>' . "\r\n"; // End div mdjm-user-profile-table						
				echo '</div>' . "\r\n"; // End div mdjm-user-profile-container
				echo '</form>' . "\r\n";
			} // profile_footer
			
			/*
			 * Create the validation code based on field settings
			 *
			 *
			 *
			 */
			function validation_script()	{				
				?>
                <script type="text/javascript">
				jQuery(document).ready(function($){
					// Configure the field validator
					$('#mdjm-user-profile').validate(
						{
							rules:
							{
								<?php
								foreach( $this->fields as $field )	{
									if( $field['required'] == true )	{
										echo '"' . $field['id'] . '":' . "\n";
										echo '{' . "\n";
										echo 'required: true,' . "\n";
										echo '},' . "\n";
									}
								}
								?>	
							}, // End rules
							
							messages:
							{
								<?php
								foreach( $this->fields as $field )	{
									if( $field['required'] == true )	{
										echo '"' . $field['id'] . '":' . "\n";
										echo '{' . "\n";
										echo 'required: " ' . __( $field['label'] . ' is required' ) . '",' .  "\n";
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
				
			} // validation_script
			
			/*
			 * Display the client profile information
			 *
			 *
			 *
			 */
			function display_profile()	{
				global $my_mdjm;
								
				$i = 0; // Counter to ensure the table format remains correct
				$x = 1;
				
				// Loop through the fields and display those that are enabled with relevant settings
				foreach( $this->fields as $field )	{
					if( empty( $field['display'] ) )
						continue;
					
					if( $i == 0 ) // Need a table row
						echo '<tr>' . "\r\n";
					
					echo '<td>' . "\r\n";
					
					echo '<label for="' . $field['id'] . '">' . $field['label'] . '</label><br>' . "\r\n"; 
					$this->display_field( $field );
					
					echo '</td>' . "\r\n";
						
					$i++; // Increment the counter
					$x++;
					
					if( $i == 2 )	{ // Need to end table row & reset counter
						echo '</tr>' . "\r\n";
						if( $x <= count( $this->fields ) )
							$i = 0;
					}
				}
				
				// Complete any empty table cells and rows
				if( $i != 2 )
					echo '<td>&nbsp;</td>' . "\r\n" . '</tr>' . "\r\n";
					
				$this->password_fields();
				
			} // display_profile
			
			/*
			 * Display fields to allow user to change password
			 *
			 *
			 *
			 */
			function password_fields()	{
				echo '</table>' . "\r\n";
				
				$default_text =  __( '<p>To update your password, enter a new password below and confirm your new password. ' . 
									 'Leaving these fields blank will keep your current password.</p>' );
									 
				echo parent::__text( 'profile_pass_intro', $default_text );
				
				echo '<table class="mdjm-user-profile-display">' . "\r\n";
				
				// New Password
				echo '<tr>' . "\r\n";
				echo '<td><label for="new_password">' . __( 'New Password' ) . ':</label><br>' . "\r\n";
				echo '<input name="new_password" id="new_password" type="password" /></td>' . "\r\n";
				
				// Confirm Password
				echo '<td><label for="new_password_confirm">' . __( 'Confirm' ) . ':</label><br>' . "\r\n";
				echo '<input name="new_password_confirm" id="new_password_confirm" type="password" /></td>' . "\r\n";
				echo '</tr>' . "\r\n";
				
			} // password_fields
			
			/*
			 * Display the relevant input field
			 *
			 *
			 *
			 */
			function display_field( $field )	{
				global $my_mdjm;
				
				if( empty( $field ) )
					return;
					
				switch( $field['type'] )	{
					case 'text':
						echo '<input name="' . $field['id'] . '" id="' . $field['id'] . '" type="' . $field['type'] . '"'; 
						
						// Required field?
						if( !empty( $field['required'] ) )
							echo ' class="required"';
							
						echo ' value="' . ( !empty( $my_mdjm['me']->$field['id'] ) ? $my_mdjm['me']->$field['id'] : '' ) . '" />' . "\r\n";
					break;
					
					case 'dropdown':
						// Put data into an array
						$values = explode( "\r\n", $field['value'] );
						
						echo '<select name="' . $field['id'] . '" id="' . $field['id'] . '">' . "\r\n";
						
						foreach( $values as $value )	{
							echo '<option value="' . $value . '"';
							
							// Initially selected
							if( !empty( $my_mdjm['me']->$field['id'] ) )
								selected( $value, $my_mdjm['me']->$field['id'] );
							
							echo '>' . $value . '</option>' . "\r\n";	
						}
						
						echo '</select>' . "\r\n";
						
					break;
					
					case 'checkbox':
						echo '<input name="' . $field['id'] . '" id="' . $field['id'] . '" type="' . $field['type'] . '" value="' . $field['value'] . '"';
						
						// Initially checked?
						if( !empty( $my_mdjm['me']->$field['id'] ) )
							checked( $field['value'], $my_mdjm['me']->$field['id'] );
							
						echo ' />' . "\r\n";
					break;
					
				} // switch
				
			} // display_field
			
		} // class
		
	} // if( !class_exists( 'MDJM_Profile' ) )
	
/* -- Insantiate the MDJM_Profile class -- */
	$mdjm_profile = new MDJM_Profile();	
				
