<?php
/*
 * class-contract.php
 * 03/05/2015
 * @since 2.0
 * The ClientZone Contract class
 * 
 */
	
	/* -- Build the MDJM_Contract class -- */
	if( !class_exists( 'MDJM_Contract' ) )	{
		class MDJM_Contract	{
			/*
			 * The Constructor
			 *
			 *
			 *
			 */
			public function __construct( $event )	{
				global $clientzone, $my_mdjm, $mdjm, $mdjm_posts;
				
				mdjm_page_visit( MDJM_APP . ' Contracts' );
				
				$this->event = get_post( $event );
				
				$event_client = get_post_meta( $this->event->ID, '_mdjm_event_client', true );
				
				$contract_id = get_post_meta( $this->event->ID, '_mdjm_event_contract', true );
				
				if( empty( $event_client ) )	{ 
					if( MDJM_DEBUG == true )
						$mdjm->debug_logger( 'ERROR: No client found for event', true );
						
					$clientzone->no_permission();
				}
				elseif( empty( $contract_id ) || !$mdjm_posts->post_exists( $contract_id ) )	{
					if( MDJM_DEBUG == true )
						$mdjm->debug_logger( 'ERROR: No contract found for event', true );	
						
					$clientzone->no_permission();
				}
					
				elseif( $my_mdjm['me']->ID != $event_client && !current_user_can( 'administrator' ) )	{
					if( MDJM_DEBUG == true )
						$mdjm->debug_logger( 'ERROR: Unauthorised access to contract / event', true );	
					
					$clientzone->no_permission();
				}
					
				else	{
					$this->event_contract = get_post( $contract_id );
					/* -- Are we signing? -- */
					if( isset( $_POST['submit'], $_POST['event_id'] ) && $_POST['submit'] == __( 'Sign Contract' ) )	{
						$this->sign_contract();
					}
					$this->display_contract();
				}				
			} // __construct
			
			/*
			 * Digitally sign the contract once all verifications are completed
			 *
			 *
			 *
			 */
			public function sign_contract()	{
				global $mdjm, $mdjm_posts, $my_mdjm, $clientzone, $mdjm_settings;
				
				/* -- Validate the nonce -- */
				if( !isset( $_POST['mdjm_sign_event_contract'] ) || !wp_verify_nonce( $_POST['mdjm_sign_event_contract'], 'sign_event_contract' ) )	{
					echo '<script type="text/javascript">' . "\r\n" . 
					'alert("WordPress Security Validation failed. Please try again");' . "\r\n" . 
					'history.back();' . "\r\n" . 
					'</script>' . "\r\n";
				}
				
				/* -- Check the users password is correct -- */
				$pass_cfm = wp_authenticate( $my_mdjm['me']->user_login, $_POST['sign_pass_confirm'] );
				/* -- Incorrect Password -- */
				if( is_wp_error( $pass_cfm ) )	{
					echo '<script type="text/javascript">' . "\r\n" . 
					'alert("ERROR: Your password was not entered correctly. Please try again.");' . "\r\n" . 
					'history.back();' . "\r\n" . 
					'</script>' . "\r\n";
				}
				/* -- Sign the contract -- */
				else	{
					/* -- Remove the save post hook to avoid loops -- */
					remove_action( 'save_post', array( $mdjm_posts, 'save_custom_post' ), 10, 2 );
					/* -- Create a new signed contract instance -- */
					$contract_data = array(
										'post_title'	 => 'Event Contract: ' . MDJM_EVENT_PREFIX . $this->event->ID,
										'post_author' 	 => $my_mdjm['me']->ID, // Author
										'post_type' 	 => MDJM_SIGNED_CONTRACT_POSTS,
										'post_status'	 => 'publish', // Contract Status
										'post_parent'	 => $this->event->ID,
										'ping_status'	 => 'closed',
										'comment_status' => 'closed',
										);
					
					/* -- Prepare the contract content -- */
					$content = $this->event_contract->post_content;
					$content = apply_filters( 'the_content', $content );
					$content = str_replace( ']]>', ']]&gt;', $content );
					
					/* -- Shortcode replacements -- */
					$contract_data['post_content'] = $mdjm->filter_content(
													$my_mdjm['me']->ID,
													$this->event->ID,
													$content
													);
					
					/* -- Append Signatory info -- */
					$contract_data['post_content'] .= '<hr>' . "\r\n";
					$contract_data['post_content'] .= '<p style="font-weight: bold">' . __( 'Signatory' ) . ': <span style="text-decoration: underline;">' . 
						ucfirst( $_POST['sign_first_name'] ) . ' ' . ucfirst( $_POST['sign_last_name'] ) . '</span></p>' . "\r\n";
						
					$contract_data['post_content'] .= '<p style="font-weight: bold">' . __( 'Date of Signature' ) . ': <span style="text-decoration: underline;">' . date( 'jS F Y' ) . '</span></p>' . "\r\n";
					$contract_data['post_content'] .= '<p style="font-weight: bold">' . __( 'Verification Method' ) . ': User Password Confirmation</p>' . "\r\n";
										
					/* -- Create the Signed Contract Post -- */
					$signed_contract = wp_insert_post( $contract_data, true );
					
					// Success
					if( !is_wp_error( $signed_contract ) )	{
						if( MDJM_DEBUG == true )
							$mdjm->debug_logger( 'Client event signed contract created (' . $signed_contract . ')', true );
						
						add_post_meta( $signed_contract, '_mdjm_contract_signed_name', ucfirst( $_POST['sign_first_name'] ) . ' ' . ucfirst( $_POST['sign_last_name'] ), true );
						
						/* -- Update the event -- */
						$event_meta = array(
							'_mdjm_signed_contract'				=> $signed_contract,
							'_mdjm_event_contract_approved'		=> date( 'Y-m-d H:i:s' ),
							'_mdjm_event_contract_approver'		=> ucfirst( $_POST['sign_first_name'] ) . ' ' . ucfirst( $_POST['sign_last_name'] ),
							'_mdjm_event_contract_approver_ip'	 => $_SERVER['REMOTE_ADDR'],
							'_mdjm_event_last_updated_by'		  => $my_mdjm['me']->ID,
							);
							
						/* -- Initiate actions for status change -- */
						wp_transition_post_status( 'mdjm-approved', $this->event->post_status, $this->event );
						
						/* -- Update the post status -- */
						wp_update_post( array( 'ID' => $this->event->ID, 'post_status' => 'mdjm-approved' ) );
						
						foreach( $event_meta as $event_meta_key => $event_meta_value )	{
							update_post_meta( $this->event->ID, $event_meta_key, $event_meta_value );
						}
						
						/* -- Update Journal with event updates -- */
						if( MDJM_JOURNAL == true )	{
							if( MDJM_DEBUG == true )
								$mdjm->debug_logger( '	-- Adding journal entry' );
								
							$mdjm->mdjm_events->add_journal( array(
										'user' 			=> $my_mdjm['me']->ID,
										'event'		   => $this->event->ID,
										'comment_content' => 'Contract Approval completed by ' . ucfirst( $_POST['sign_first_name'] ) . ' ' . ucfirst( $_POST['sign_last_name'] . '<br>' ),
										'comment_type' 	=> 'mdjm-journal',
										),
										array(
											'type' 		  => 'update-event',
											'visibility'	=> '2',
										) );
						}
						else	{
							if( MDJM_DEBUG == true )
								$mdjm->debug_logger( '	-- Journalling is disabled' );	
						}
						
						/* -- Email booking confirmations -- */
						$contact_client = isset( $mdjm_settings['templates']['booking_conf_to_client'] ) ? true : false;
						$contact_dj = isset( $mdjm_settings['templates']['booking_conf_to_dj'] ) ? true : false;
						$client_email = isset( $mdjm_settings['templates']['booking_conf_client'] ) ? $mdjm_settings['templates']['booking_conf_client'] : false;
						$dj_email = isset( $mdjm_settings['templates']['email_dj_confirm'] ) ? $mdjm_settings['templates']['email_dj_confirm'] : false;
						
						if( !$mdjm_posts->post_exists( $client_email ) )	{
							if( MDJM_DEBUG == true )
								$mdjm->debug_logger( 'ERROR: No email template for the contract has been found ' . __FUNCTION__, $stampit=true );
							wp_die( 'ERROR: Either no email template is defined or an error has occured. Check your Settings.' );
						}
						
						if( $contact_client == true )	{
							if( MDJM_DEBUG == true )
								$mdjm->debug_logger( 'Configured to email client with template ID ' . $client_email );
							
							if( MDJM_DEBUG == true )
								$mdjm->debug_logger( 'Generating email...' );
									
							$approval_email = $mdjm->send_email( array( 
													'content'	=> $client_email,
													'to'		 => get_post_meta( $this->event->ID, '_mdjm_event_client', true ),
													'from'	   => $mdjm_settings['templates']['booking_conf_from'] == 'dj' ? get_post_meta( $this->event->ID, '_mdjm_event_dj', true ) : 0,
													'journal'	=> 'email-client',
													'event_id'   => $this->event->ID,
													'html'	   => true,
													'cc_dj'	  => isset( $mdjm_settings['email']['bcc_dj_to_client'] ) ? true : false,
													'cc_admin'   => isset( $mdjm_settings['email']['bcc_admin_to_client'] ) ? true : false,
													'source'	 => 'Event Status to Approved',
												) );
							if( $approval_email )	{
								if( MDJM_DEBUG == true )
									 $mdjm->debug_logger( '	-- Confrmation email sent to client ' );
							}
							else	{
								if( MDJM_DEBUG == true )
									 $mdjm->debug_logger( '	ERROR: Confrmation email was not sent' );	
							}	
						}
						else	{
							if( MDJM_DEBUG == true )
								$mdjm->debug_logger( 'Not configured to email client' );	
						}
						if( $contact_dj == true )	{
							if( MDJM_DEBUG == true )
								$mdjm->debug_logger( 'Configured to email DJ with template ID ' . $dj_email );
							
							if( MDJM_DEBUG == true )
								$mdjm->debug_logger( 'Generating email...' );	
								$approval_dj_email = $mdjm->send_email( array( 
														'content'	=> $dj_email,
														'to'		 => get_post_meta( $this->event->ID, '_mdjm_event_dj', true ),
														'from'	   => 0,
														'journal'	=> 'email-dj',
														'event_id'   => $this->event->ID,
														'html'	   => true,
														'cc_dj'	  => false,
														'cc_admin'   => isset( $mdjm_settings['email']['bcc_admin_to_dj'] ) ? true : false,
														'source'	 => 'Event Status to Approved',
													) );
								if( $approval_dj_email )	{
									if( MDJM_DEBUG == true )
										 $mdjm->debug_logger( '	-- Approval email sent to DJ ' );
								}
								else	{
									if( MDJM_DEBUG == true )
										 $mdjm->debug_logger( '	ERROR: Approval email was not sent to DJ' );	
								}	
						}
						else	{
							if( MDJM_DEBUG == true )
								$mdjm->debug_logger( 'Not configured to email DJ' );	
						}
					}
					/* -- Re-add the save post hook -- */
					add_action( 'save_post', array( $mdjm_posts, 'save_custom_post' ), 10, 2 );
					
					if( MDJM_DEBUG == true )
						$mdjm->debug_logger( 'Completed client signing of contract ' . __METHOD__, true );
						
					/* -- Email admin to notify of changes -- */
					if( MDJM_NOTIFY_ADMIN == true )	{
						if( MDJM_DEBUG == true )
							$GLOBALS['mdjm_debug']->log_it( 'Sending event status change notification to admin (Contract Signed)' );
							
						$content = '<html>' . "\n" . '<body>' . "\n";
						$content .= '<p>' . sprintf( __( 'Good news... %s has just signed their event contract via %s', 'mobile-dj-manager' ), 
											'{CLIENT_FULLNAME}', MDJM_APP ) . '</p>';
											
						$content .= '<hr />' . "\n";
						$content .= '<h4><a href="' . get_edit_post_link( $this->event->ID ) . '">' . __( 'Event ID', 'mobile-dj-manager' ) . ': ' 
							. MDJM_EVENT_PREFIX . $this->event->ID . '</a></h4>' . "\n";
							
						$content .= '<p>' . "\n";
						$content .= __( 'Date', 'mobile-dj-manager' ) . ': {EVENT_DATE}<br />' . "\n";
						$content .= __( 'Type', 'mobile-dj-manager' ) . ': ' . $mdjm->mdjm_events->get_event_type( $this->event->ID ) . '<br />' . "\n";
						
						$event_stati = get_event_stati();
						
						$content .= __( 'Status', 'mobile-dj-manager' ) . ': ' . $event_stati[get_post_status( $this->event->ID )] . '<br />' . "\n";
						$content .= __( 'Client', 'mobile-dj-manager' ) . ': {CLIENT_FULLNAME}<br />' . "\n";
						$content .= __( 'Value', 'mobile-dj-manager' ) . ': {TOTAL_COST}<br />' . "\n";
						
						$deposit = get_post_meta( $this->event->ID, '_mdjm_event_deposit' );
						$deposit_status = get_post_meta( $this->event->ID, '_mdjm_event_deposit_status' );
						
						if( !empty( $deposit ) && $deposit != '0.00' )
							$body .= __( 'Deposit', 'mobile-dj-manager' ) . ': {DEPOSIT} ({DEPOSIT_STATUS})<br />' . "\n";
						
						$content .= __( 'Balance Due', 'mobile-dj-manager' ) . ': {BALANCE}</p>' . "\n";
						
						$content .= '<p>' . sprintf( __( '%sView Event%s', 'mobile-dj-manager' ),
										'<a href="=' . get_edit_post_link( $this->event->ID ) . '">',
										'</a>' )
										 . '</p>' . "\n";
						
						$content .= '</body>' . "\n" . '</html>' . "\n";
						
						$mdjm->send_email( array(
											'content'		=> $content,
											'to'			 => $mdjm_settings['email']['system_email'],
											'subject'		=> __( 'Event Contract Signed', 'mobile-dj-manager' ),
												
											'journal'		=> false,
											'event_id'	   => $this->event->ID,
											'cc_dj'		  => false,
											'cc_admin'	   => false,
											'log_comm'	   => false ) );
					}
					else
						if( MDJM_DEBUG == true )
							$GLOBALS['mdjm_debug']->log_it( 'Skipping admin notification' );

					//wp_redirect( $mdjm->get_link( MDJM_CONTRACT_PAGE ) . 'event_id=' . $this->event->ID . '&message=3&class=2' );
					?>
					<script type="text/javascript">
                    window.location.replace("<?php echo $mdjm->get_link( MDJM_CONTRACT_PAGE ) . 'event_id=' . $this->event->ID . '&message=3&class=2'; ?>");
                    </script>
                    <?php
					exit;
					
				}
			} // sign_contract
			
			/*
			 * Display the header content of the contract page
			 *
			 *
			 *
			 */
			public function contract_header()	{
				global $clientzone;
				
				if( $this->event->post_status == 'mdjm-contract' )	{
					$section = 'contract_intro';
					
					$default_text = !current_user_can( 'administrator' ) ? 
						__( '<p>Your contract is displayed below and is ready for signing.<br>' . 
						'<p>Please review its content carefully to ensure accuracy and once you are ready to do so, <a href="#sign_form">scroll to the bottom</a> of this page to confirm your acceptance of the contractual terms and digitally sign the contract.<br>' . 
						'<p>Once you have signed the contract, you will receive a further email from us.</p>' ) : 
						__( 'The Client contract is displayed below. It is not yet signed' );
				}
					
				elseif( $this->event->post_status == 'mdjm-approved' || $this->event->post_status == 'mdjm-completed' )	{
					/* -- If the contract has been signed, display success message -- */
					if( isset( $_GET['message'], $_GET['class'] ) )
						$clientzone->display_message( $_GET['message'], $_GET['class'] );	
					
					$section = 'contract_signed';
					
					$default_text = __( '<p>Your ' . ( current_user_can( 'administrator' ) ? 'clients ' : '' ) . 'signed contract is displayed below for your records.</p>' );
				}
					
				else	{
					$section = 'contract_not_ready';
					
					$default_text = !current_user_can( 'administrator' ) ? 
						__('<p>Your contract is not yet ready for signing as you have not indicated that you would like to proceed with your event. You can do this <a href="{APPLICATION_HOME}">here</a>.</p>' ) : 
						__( '<p>The client contract is not yet ready for signing as the event status has not been updated to "Awaiting Contract"</p>' );
				}
				
				echo ( !current_user_can( 'administrator' ) ? $clientzone->__text( $section, $default_text ) : $default_text );				
			} // contract_header
			
			/*
			 * Display the footer content of the contract page
			 *
			 *
			 *
			 */
			public function contract_footer()	{
				/* -- Enqueue jQuery validation -- */
				wp_enqueue_script( 'mdjm-validation' );
				
				/* -- Anchor the signing form -- */
				echo '<a id="sign_form"></a>';
				
				$signed_status = array(
									'mdjm-approved',
									'mdjm-completed'
									);
				
				if( $this->event->post_status == 'mdjm-contract' && !current_user_can( 'administrator' ) )	{
					/* -- Display the form enabling the user to digitally sign the contract -- */
					?>
					<div id="sign_contract_content">
						<form name="mdjm_sign_contract" id="mdjm_sign_contract" method="post">
						<input type="hidden" name="event_id" id="event_id" value="<?php echo $this->event->ID; ?>">
                        <?php wp_nonce_field( 'sign_event_contract', 'mdjm_sign_event_contract' ); ?>
						<div class="mdjm-contract-row">
							<div class="mdjm-contract-2column">
								<p><label for="sign_first_name" class="mdjm_label"><?php echo __( 'First Name:' ); ?></label><br>
								<input type="text" name="sign_first_name" id="sign_first_name" class="mdjm-contract-input required"></p>
							</div>
							<div class="mdjm-contract-last-2column">
								<p><label for="sign_last_name" class="mdjm_label"><?php echo __( 'Last Name:' ); ?></label><br>
								<input type="text" name="sign_last_name" id="sign_last_name" class="mdjm-contract-input required"></p>
							</div>
						</div>
						<div class="mdjm-contract-row">
							<div class="mdjm-contract-1column">
								<p><input type="checkbox" name="sign_acceptance" id="sign_acceptance" class="required">&nbsp;
								<label for="sign_acceptance" class="mdjm_label"><?php echo __( 'I hereby confirm that I have read and accept the contract and its terms' ); ?></label></p>
							</div>
						</div>
						<div class="mdjm-contract-row">
							<div class="mdjm-contract-1column">
								<p><input type="checkbox" name="sign_is_me" id="sign_is_me" class="required">&nbsp;
								<label for="sign_is_me" class="mdjm_label"><?php echo __( 'I hereby confirm that the person named within the above contract is me and that all associated details are correct' ); ?></label></p>
							</div>
						</div>
						<div class="mdjm-contract-row">
							<div class="mdjm-contract-1column">
								<p><label for="sign_pass_confirm" class="mdjm_label"><?php echo __( 'Re-Enter Your Password' ); ?></label><br>
								<input type="password" name="sign_pass_confirm" id="sign_pass_confirm" class="required"></p>
							</div>
						</div>
						<div class="mdjm-contract-row">
							<div class="mdjm-contract-1column">
								<p><input type="submit" name="submit" id="submit" value="<?php echo __( 'Sign Contract' ); ?>"></p>
							</div>
						</div>
						</form>
					</div>
					<?php		
				}
			} // contract_footer
						
			/*
			 * Display the footer content of the contract
			 * after running the content filter
			 *
			 *
			 */
			public function display_contract()	{
				global $mdjm, $clientzone, $my_mdjm, $mdjm_settings;
				
				$contract = $this->event_contract;
				
				if( $this->event->post_status == 'mdjm-approved' || $this->event->post_status == 'mdjm-completed' )	{
					$contract_id = get_post_meta( $this->event->ID, '_mdjm_signed_contract', true );
					$contract = get_post( $contract_id );
					
					$prefix = '<p class="signed">' . __( 'This contract has been signed' ) . '<br>' . "\r\n";
					$prefix .= __( 'Signed on ' . date( MDJM_SHORTDATE_FORMAT, strtotime( $contract->post_date ) ) . 
					' by ' . get_post_meta( $contract->ID, '_mdjm_contract_signed_name', true ) ) . 
					__( ' with password verification' );
					
					$ip = get_post_meta( $contract->ID, '_mdjm_event_contract_approver_ip', true );
					if( !empty( $ip ) )
						$prefix .= ' from IP address ' . $ip;
					
					$prefix .= '</p>' . "\r\n";
				}
				
				/* -- Retrieve the contract content -- */
				$content = $contract->post_content;
				$content = apply_filters( 'the_content', $content );
				$content = str_replace( ']]>', ']]&gt;', $content );
				
				/* -- Shortcode replacements -- */
				$content = $mdjm->filter_content(
									$my_mdjm['me']->ID,
									$this->event->ID,
									$content
									);
				
				/* -- Display the contract content -- */
				$this->contract_header();
				echo '<hr>' . "\r\n";
				if( !empty( $content ) )	{
					if( isset( $prefix ) )
						$clientzone->display_notice( 1, $prefix );
					print( $content );
				}
				/* -- If we have no content -- */	
				else
					wp_die( 'An error has occured. Please contact the <a href="mailto:' . $mdjm_settings['email']['system_email'] . '">website administrator</a>' );
				
				echo '<hr>' . "\r\n";	
				$this->contract_footer();
			} // display_contract
			
		} // class
		
	} // if( !class_exists( 'MDJM_Contract' ) )
	
/* -- Insantiate the MDJM_Contract class if the user is logged in-- */
	global $clientzone, $mdjm_posts;
	
	$event = !empty( $_GET['event_id'] ) ? $_GET['event_id'] : '';
	
	if( !is_user_logged_in() )
		$clientzone->login();	
				
	elseif( empty( $event ) || !$mdjm_posts->post_exists( $event ) )
		$clientzone->no_permission();	
	
	else
		$mdjm_contract = new MDJM_Contract( $event );
