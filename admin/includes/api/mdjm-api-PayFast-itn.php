<?php
/*
 * The PayFast ITN integration receives payment notifications from PayFast
 * and updates the MDJM plugin with transaction details
 *
 *
 */
	if( !class_exists( 'MDJM_PayFast_ITN' ) )	{
		class MDJM_PayFast_ITN	{
			function __construct()	{
				global $mdjm_settings;
				
				$this->payfast_settings = $mdjm_settings['payfast'];
				$this->payment_settings = $mdjm_settings['payments'];
				
				/* -- General -- */
				define( 'PF_LOG', ( !empty( $this->payfast_settings['payfast_debug'] ) ? true : false ) );
				define( 'PF_LOG_FILE', MDJM_PLUGIN_DIR . '/admin/includes/api/api-log/mdjm-payfast-itn-debug.log' );
				
				define( 'USER_AGENT', 'Mozilla/4.0 (compatible; MSIE 5.01; Windows NT 5.0)' ); // User agent for cURL
				
				/* -- PayFast Messages -- */
				// Errors
				define( 'PF_ERR_AMOUNT_MISMATCH', 'Amount mismatch' );
				define( 'PF_ERR_BAD_SOURCE_IP', 'Bad source IP address' );
				define( 'PF_ERR_CONNECT_FAILED', 'Failed to connect to PayFast' );
				define( 'PF_ERR_BAD_ACCESS', 'Bad access of page' );
				define( 'PF_ERR_INVALID_SIGNATURE', 'Security signature mismatch' );
				define( 'PF_ERR_CURL_ERROR', 'An error occurred executing cURL' );
				define( 'PF_ERR_INVALID_DATA', 'The data received is invalid' );
				define( 'PF_ERR_UKNOWN', 'Unkown error occurred' );
				
				// General
				define( 'PF_MSG_OK', 'Payment was successful' );
				define( 'PF_MSG_FAILED', 'Payment has failed' );
				
				$this->initialise();
				
				$this->PayFast_log( 'Starting PayFast ITN procedures', true );
				
				if( !empty( $this->payfast_settings['enable_pf_sandbox'] ) )
					$this->PayFast_log( '--- Running in Sandbox Mode --' );
				
				if( !$this->pf_error )
					$this->data_dump();
					
				if( !$this->pf_error )
					$this->validate_PayFast_hosts();
					
				if( !$this->pf_error )
					$this->data_validation();
					
				if( !$this->pf_error )
					$this->get_server_data();
					
				/* -- Log the output -- */
				if( !empty( $this->pf_error ) )	{
					$this->PayFast_log( "\n\n" . 'ERRORS WERE ENCOUNTERED' );
					$this->PayFast_log( $this->output );
				}
				
				/* -- End the log -- */
				$this->PayFast_log( $this->mdjm_output );
				$this->PayFast_log( 'Completed PayFast ITN procedures', true );
				
				
			} // __construct
			
			/*
			 * Submit messages to debug file
			 *
			 *
			 *
			 */			
			function PayFast_log( $msg, $stampit=false )	{
				if( PF_LOG === false )
					return;
					
				$debug_log = ( $stampit == true ? date( 'd/m/Y  H:i:s', current_time( 'timestamp' ) ) . ' : ' . $msg : '    ' . $msg );
				
				error_log( $debug_log . "\r\n", 3, PF_LOG_FILE );	
			}
			
			/*
			 * Initialise the settings for the class and transaction
			 *
			 *
			 *
			 */
			function initialise()	{
			/* -- Notify PayFast that we have received information -- */
				header( 'HTTP/1.0 200 OK' );
				flush();
				
			/* -- Variables -- */
				$this->pf_error = false;
				$this->pf_error_msg = '';
				$this->output = '';
				$this->pfParamString = '';
				$this->pf_host = ( !empty( $this->payfast_settings['enable_pf_sandbox'] ) ? 'sandbox.payfast.co.za' : 'www.payfast.co.za' );
				$this->merchant_id = ( !empty( $this->payfast_settings['enable_pf_sandbox'] ) ? 
					$this->payfast_settings['merchant_id'] : $this->payfast_settings['sandbox_merchant_id'] );
			} // initialise
			
			/*
			 * Grab the submitted data and calculate the security signature
			 *
			 *
			 *
			 */
			function data_dump()	{
				$this->output = 'Posted Variables:' . "\r\n";
				
				// Remove extra slashes in the data
				foreach( $_POST as $key => $value )	{
					$pf_data[$key] = stripslashes( $value );	
				}
				
				// Dump the submitted variables and calculate security signature
				foreach( $pf_data as $key => $value )	{
				   if( $key != 'signature' )
					 $this->pfParamString .= $key . '='. urlencode( $value ) . '&';
				}
				
				// Remove the last '&' from the parameter string
				$this->pfParamString = substr( $this->pfParamString, 0, -1 );
				$pfTempParamString = $this->pfParamString;
				
				// If a passphrase has been set in the PayFast Settings, then it needs to be included in the signature string.
				$passPhrase = false; // Disable this as not in use
				if( !empty( $passPhrase ) )
					$pfTempParamString .= '&passphrase='.urlencode( $passPhrase );
				
				$signature = md5( $pfTempParamString );
			 	
				$this->pf_result = ( $_POST['signature'] == $signature );
			 
				$this->output .= 'Security Signature:' . "\n\n";
				$this->output .= '- posted     = ' . $_POST['signature'] . "\n";
				$this->output .= '- calculated = ' . $signature . "\n";
				$this->output .= '- result     = ' . ( $this->pf_result ? 'SUCCESS' : 'FAILURE' ) ."\n";
			} // data_dump
			
			/*
			 * Verify the source IP Address by way of a lookup, IP -> DNS name
			 *
			 *
			 *
			 */
			function validate_PayFast_hosts()	{
				$authorised_hosts = array( // DNS names of authorised hosts
								'www.payfast.co.za',
								'sandbox.payfast.co.za',
								'w1w.payfast.co.za',
								'w2w.payfast.co.za' );
								
				$valid_ips = array();
				
				foreach( $authorised_hosts as $payfast_hostname )	{
					$ips = gethostbynamel( $payfast_hostname );
					
					if( $ips !== false )
						$valid_ips = array_merge( $valid_ips, $ips );	
				}
				
				/* -- Remove any duplicate addresses -- */
				$valid_ips = array_unique( $valid_ips );
				
				/* -- If we have a bad address log it -- */
				if( !in_array( $_SERVER['REMOTE_ADDR'], $valid_ips ) )	{
					$this->pf_error = true;
					$this->pf_error_msg = PF_ERR_BAD_SOURCE_IP;
				}
			} // validate_PayFast_hosts
			
			/*
			 * Connect to the PayFast server and validate the data we have received
			 *
			 *
			 *
			 */
			function data_validation()	{
			/* -- Primarily try to use cURL -- */
				if( function_exists( 'curl_init' ) )	{
					$this->output .= "\n\n" . 'Using cURL' . "\n\n";
					
					/* -- Configure cURL options -- */
					$ch = curl_init();
					
					$curl_options = array(
						// Base options
						CURLOPT_USERAGENT => USER_AGENT, // Set user agent
						CURLOPT_RETURNTRANSFER => true,  // Return output as string rather than outputting it
						CURLOPT_HEADER => false,         // Don't include header in output
						CURLOPT_SSL_VERIFYHOST => true,
						CURLOPT_SSL_VERIFYPEER => false,
			 
						// Standard settings
						CURLOPT_URL => 'https://'. $this->pf_host . '/eng/query/validate',
						CURLOPT_POST => true,
						CURLOPT_POSTFIELDS => $this->pfParamString,
					);
					curl_setopt_array( $ch, $curl_options );
					
					/* -- Execute cURL -- */
					$this->pf_result = curl_exec( $ch );
					curl_close( $ch );
			 
					if( $this->pf_result === false )	{ // We have a cURL error, log it
						$this->pf_error = true;
						$this->pf_error_msg = PF_ERR_CURL_ERROR;
					}
				} // if( function_exists( 'curl_init' ) )
				
				/* -- If cURL is not available we can use fsockopen as a backup -- */
				else	{
					$this->output .= "\n\n" . 'Using fsockopen' . "\n\n";
					
					/* -- Header consruction -- */
					$header = 'POST /eng/query/validate HTTP/1.0' . "\r\n";
					$header .= 'Host: ' . $this->pf_host . "\r\n";
					$header .= 'Content-Type: application/x-www-form-urlencoded' . "\r\n";
					$header .= 'Content-Length: ' . strlen( $this->pfParamString ) . "\r\n\r\n";
					
					/* -- Establish server connection -- */
					$socket = fsockopen( 'ssl://'. $this->pf_host, 443, $errno, $errstr, 10 );
					
					/* -- Send the command -- */
					fputs( $socket, $header . $this->pfParamString );
					
					/* -- Read server response -- */
					$this->pf_result = '';
					$header_done = false;
					
					while( !feof( $socket ) )	{
						$line = fgets( $socket, 1024 );
			 
						/* -- Check if we are finished reading the header yet -- */
						if( strcmp( $line, "\r\n" ) == 0 ) // yes
							$header_done = true;
						
						// If header has been processed
						elseif( $header_done )
							// Read the main response
							$this->pf_result .= $line;
					} // while( !feof( $socket ) )
				} // else	
			} // data_validation
			
			/*
			 * Read the data from the server and parse to debugging
			 *
			 *
			 *
			 */
			function get_server_data()	{
				$lines = explode( "\n", $this->pf_result );
     
				$this->output .= "\n" . 'Validate response from server:' . "\n";
			 
				foreach( $lines as $line )
					$this->output .= $line ."\n";
				
				/* -- Determine VALID of INVALID response -- */
				$this->pf_result = trim( $lines[0] );
     
				$this->output .= "\n" . 'Result = ' . $this->pf_result;
			 
				// If the transaction was valid we can process for MDJM
				if( strcmp( $this->pf_result, 'VALID' ) == 0 )	{
					$txn = (object) $_POST;
					
					/* -- Run through the MDJM procedures to log the transaction -- */
					$this->process_txn( $txn );
				}
				// If the transaction was NOT valid
				else	{
					$this->pf_error = true;
					$this->pf_error_msg = PF_ERR_INVALID_DATA;
				}
			} // get_server_data
			
			/*
			 * Process the transaction updating the transaction, event and sending relevant emails
			 *
			 *
			 */
			function process_txn( $txn )	{
				global $mdjm_posts;
				$major_error = $this->mdjm_validate( $txn );
					
				/* -- We only continue here if there are no hard stop errors -- */
				if( !empty( $major_error ) )	{
					/* -- Remove the save post hook to avoid loops -- */
					remove_action( 'save_post', array( $mdjm_posts, 'save_custom_post' ), 10, 2 );
					
					$txn_post = $this->create_transaction( $txn );
					
					if( !empty( $txn_post ) )
						$this->update_event( $txn );
						$email_to_client = $this->client_email( $txn );
						$this->admin_email( $txn, $email_to_client );
						
					/* -- Re-Add the save post hook -- */
					add_action( 'save_post', array( $mdjm_posts, 'save_custom_post' ), 10, 2 );
					
				}
				else	{
					/* -- Email error report -- */
					if( !empty( $this->pf_error ) || !empty( $this->major_error ) )
						$GLOBALS['mdjm']->send_email(
												array( 
													'content'		=> $this->output . $this->mdjm_output,
													'to'			=> $GLOBALS['mdjm_settings']['email']['system_email'],
													'subject'		=> __( 'PayFast Transaction ITN Error Log',
																				'mobile-dj-manager' ),
													'journal'		=> false,
													'html'			=> false,
													'cc_admin'		=> false,
													'filter'		=> false,
													'log_comm'		=> false ) );
				}
			} // process_txn
			
			/*
			 * MDJM Validation checks
			 *
			 *
			 *
			 */
			function mdjm_validate( $txn )	{
				global $mdjm, $mdjm_posts;
				
				/* -- First we'll check for major errors that require the script to be halted -- */
				$major_error = false;
				$this->mdjm_output = '' . "\n";
				
				$this->mdjm_output .= '-------------------------------------------' . "\n";
				$this->mdjm_output .= '	STARTING MDJM VALIDATION PROCEDURES		' . "\n";
				$this->mdjm_output .= '-------------------------------------------' . "\n";
				
				$this->mdjm_output .= ( $txn->payment_status == 'COMPLETE' ? 'PASS: ' : 'FAIL: ' ) . 
								'Payment Status: ' . $txn->payment_status . "\n";
				
				if( $txn->payment_status != 'COMPLETE' )
					$major_error = true;
				
				$this->mdjm_output .= ( $txn->merchant_id == $this->merchant_id ? 'PASS: ' : 'FAIL: ' ) . 
								'Merchant ID: ' . $txn->merchant_id . "\n";
				
				if( $txn->merchant_id != $this->payfast_settings['merchant_id'] )
					$major_error = true;
				
				$this->mdjm_output .= ( !empty( $txn->amount_gross ) ? 'PASS: ' : 'FAIL: ' ) . 
								'Gross Payment: ' . $txn->amount_gross . "\n";
				
				if( empty( $txn->amount_gross ) )
					$major_error = true;
				
				/* -- Associated Event ID -- */	
				$this->mdjm_output .= ( !empty( $txn->custom_int1 ) && $mdjm_posts->post_exists( $txn->custom_int1 ) ? 'PASS: ' : 'FAIL: ' ) . 
								'Event: ' . $txn->custom_int1 . ' Exists' . "\n";
				
				if( empty( $txn->custom_int1 ) )
					$major_error = true;
					
				/* -- What is being paid for? -- */	
				$this->mdjm_output .= ( !empty( $txn->custom_str1 ) ? 'PASS: ' : 'FAIL: ' ) . 
								'Payment for: ' . $txn->custom_str1 . "\n";
				
				if( empty( $txn->custom_str1 ) )
					$major_error = true;
					
				/* -- Ensure the PayFast Txn ID does not already exist -- */
				$txns = get_posts( array(
									'post_type'		=> MDJM_TRANS_POSTS,
									'post_status'	=> 'any',
									'meta_key'		=> '_mdjm_gw_txn_id',
									'meta_value'	=> $txn->pf_payment_id ) );
							
				$this->mdjm_output .= ( !empty( $txn->custom_str1 ) ? 'PASS: ' : 'FAIL: ' ) . 
								'Unique Gateway Transaction ID: ' . $txn->pf_payment_id . "\n";
				
				if( !empty( $txns ) && count( $txns ) > '0' )
					$major_error = true;
								
				$this->mdjm_output .= '-------------------------------------------' . "\n";
				$this->mdjm_output .= '		MDJM VALIDATION PROCEDURES ' . ( $major_error == true ? 'FAILED' : 'PASSED' ) . "\n";
				$this->mdjm_output .= '-------------------------------------------' . "\n";
				
				return $major_error;
			} // mdjm_validate
			
			/*
			 * Create the transaction post
			 *
			 *
			 *
			 */
			function create_transaction( $txn )	{
				$this->mdjm_output .= '--- Beginning transaction post creation' . "\n";
								
				$trans_type = get_term_by( 'name', stripslashes( $txn->custom_str1 ), 'transaction-types' );
				
				/* -- Set the data for the transaction post -- */
				$trans_data = array(
								'ID'			=> $txn->m_payment_id,
								'post_title'	=> MDJM_EVENT_PREFIX . $txn->m_payment_id,
								'post_status' 	=> 'mdjm-income',
								'post_date'		=> date( 'Y-m-d H:i:s', current_time( 'timestamp' ) ),
								'edit_date'		=> true,
								'post_author'	=> get_post_meta( $txn->custom_int1, '_mdjm_event_client', true ),
								'post_type'		=> MDJM_TRANS_POSTS,
								'post_category'	=> ( !empty( $trans_type ) ? array( $trans_type->term_id ) : '' ),
								'post_parent'	=> $txn->custom_int1,
								'post_modified'	=> date( 'Y-m-d H:i:s', current_time( 'timestamp' ) ) );
				
				/* -- Now set the post metadata -- */
				$trans_meta = array(
								'_mdjm_txn_status'		=> ( $txn->payment_status == 'COMPLETE' ? 'Completed' : $txn->payment_status ),				
								'_mdjm_txn_source'		=> 'PayFast',
								'_mdjm_gw_txn_id'		=> $txn->pf_payment_id,
								'_mdjm_payer_firstname'	=> ( !empty( $txn->name_first ) ? 
														sanitize_text_field( ucfirst( stripslashes( $txn->name_first ) ) ) : '' ),
														
								'_mdjm_payer_lastname'	=> ( !empty( $txn->name_last ) ? 
														sanitize_text_field( ucfirst( stripslashes( $txn->name_last ) ) ) : '' ),
														
								'_mdjm_payer_email'		=> ( !empty( $txn->email_address ) && is_email( $txn->email_address ) ? 
														strtolower( $txn->email_address ) : '' ),
														
								'_mdjm_payment_from'		=> ( !empty( $txn->name_first ) ? 
														sanitize_text_field( ucfirst( stripslashes( $txn->name_first ) ) ) : '' ) . ' ' . 
														( !empty( $txn->name_last ) ? 
															sanitize_text_field( ucfirst( stripslashes( $txn->name_last ) ) ) : '' ),
				
								'_mdjm_txn_net'			=> ( !empty( $txn->amount_net ) ? 
														number_format( $txn->amount_net, 2 ) : '0.00' ),
																
								'_mdjm_txn_currency'	=> 'ZAR',
														
								'_mdjm_txn_fee'			=> ( !empty( $txn->amount_fee ) ? 
														number_format( substr( $txn->amount_fee, 1 ), 2 ) : '0.00' ),
														
								'_mdjm_txn_total'		=> ( !empty( $txn->amount_gross ) ? 
														number_format( $txn->amount_gross, 2 ) : '0.00' ),
														
								'_mdjm_txn_gw_response'	=> json_encode( $_POST ) );
												
				/* -- Create the transaction post -- */
				$trans_update = wp_update_post( $trans_data );
				
				if( $trans_update == 0 )
					$this->mdjm_output .= 'ERROR: Unable to add Transaction post data' . "\n";
				
				else	{
					$this->mdjm_output .= 'PASS: Transaction post data added successfully ' . $trans_update . "\n";
				
				/* -- Set the Transaction Type -- */
				if( !empty( $trans_type ) )
					wp_set_post_terms( $trans_update, $trans_type->term_id, 'transaction-types' );
					
					/* -- Now add the post meta -- */
					foreach( $trans_meta as $trans_meta_key => $trans_meta_value )	{
						if( add_post_meta( $trans_update, $trans_meta_key, $trans_meta_value ) )
							$this->mdjm_output .= 'PASS: ' . $trans_meta_key . ' transaction data added successfully ' . 
								' with value ' .$trans_meta_value . "\n";
								
						else
							$this->mdjm_output .= 'FAIL: ' . $trans_meta_key . ' transaction data with value ' .
								$trans_meta_value . ' could not be added' . "\n";
					}
				}
				
				if( !empty( $txn->amount_fee ) )	{
					/* -- Now add a new transaction for any merchant fees -- */
					if( !class_exists( 'MDJM_Transactions' ) )
						require_once( MDJM_PLUGIN_DIR . '/admin/includes/class/class-mdjm-transactions.php' );
				
					$mdjm_txns = new MDJM_Transactions();
					
					$txn_fee_data = array(
										'post_author' 	=> get_post_meta( $txn->custom_int1, '_mdjm_event_client', true ),
										'post_type'		=> MDJM_TRANS_POSTS,
										'post_category'	=> ( !empty( $trans_type ) ? array( $trans_type->term_id ) : '' ),
										'post_parent'	=> $txn->custom_int1 );
					$txn_fee_meta = array(
										'_mdjm_txn_status'		=> 'Completed',
										'_mdjm_txn_source'		=> __( 'PayFast', 'mobile-dj-manager' ),
										'_mdjm_txn_currency'	=> 'ZAR',
										'_mdjm_txn_total'		=> number_format( substr( $txn->amount_fee, 1 ), 2 ),
										'_mdjm_payment_to'		=> 'PayFast' );
					
					$type = get_term_by( 'name', __( 'Merchant Fees', 'mobile-dj-manager' ), 'transaction-types' );
					
					$txn_fee_id = $mdjm_txns->add_transaction( 
													$txn_fee_data,
													$txn_fee_meta,
													'out',
													$type->term_id );
							
					if( !empty( $txn_fee_id ) )
						$this->mdjm_output .= 'Transaction created for merchant fee' . "\r\n";
						
					else
						$this->mdjm_output .= 'Could not create transaction for merchant fee' . "\r\n";	
				}
				
				$this->mdjm_output .= '--- Completed transaction post creation' . "\n";
				
				return $trans_update;
			} // create_transaction
			
			/*
			 * Update the Event as required
			 *
			 * @param	int		$event_id	ID of the event
			 *			objarr	$txn		The post data from the transaction
			 */
			function update_event( $txn )	{
				$update_fields = array(
								'_mdjm_event_last_updated_by'    => 0 );
				
				/* -- If the balance or deposit is paid, log it -- */				
				if( $txn->custom_str1 == MDJM_DEPOSIT_LABEL )
					$update_fields['_mdjm_event_deposit_status'] = 'Paid';	
			
				if( $txn->custom_str1 == MDJM_BALANCE_LABEL )
					$update_fields['_mdjm_event_balance_status'] = 'Paid';
					
				/* -- Update the Event postmeta -- */
				$field_updates = '';
				foreach( $update_fields as $event_meta_key => $event_meta_value )	{
					if( update_post_meta( $txn->custom_int1, $event_meta_key, $event_meta_value ) )
						$field_updates .= 'PASS: Field ' . $event_meta_key . ' updated with value ' . $event_meta_value . "\r\n";
					else
						$field_updates .= 'FAIL: Field ' . $event_meta_key . ' could not be updated with value ' . $event_meta_value . "\r\n";
				}
				$this->mdjm_output .= $field_updates . "\n";
				
				/* -- Update Journal with event updates -- */
				if( MDJM_JOURNAL == true )	{
					if( MDJM_DEBUG == true )
						$GLOBALS['mdjm_debug']->log_it( '	-- Adding journal entry' );
						
					$GLOBALS['mdjm']->mdjm_events->add_journal( array(
								'user' 			=> get_post_meta( $txn->custom_int1, '_mdjm_event_client', true ),
								'event'		   => $txn->custom_int1,
								'comment_content' => $txn->custom_str1 . ' of ' . display_price( $txn->amount_gross, true ) . ' received via PayFast for event',
								'comment_type' 	=> 'mdjm-journal',
								),
								array(
									'type' 		  => 'update-event',
									'visibility'	=> '2',
								) );
				}
				else	{
					if( MDJM_DEBUG == true )
						$GLOBALS['mdjm_debug']->log_it( '	-- Journalling is disabled' );	
				}
			} // update_event
			
			/*
			 * Email the client re the transaction
			 *
			 *
			 *
			 */
			function client_email( $txn )	{
				global $mdjm_settings;
				
				if( MDJM_DEBUG == true )
					$GLOBALS['mdjm_debug']->log_it( 'Generating payment receipt email...' );
						
				$payment_rcvd_email = $GLOBALS['mdjm']->send_email( array( 
										'content'	=> $mdjm_settings['templates']['payment_cfm_template'],
										'to'		 => get_post_meta( $txn->custom_int1, '_mdjm_event_client', true ),
										'from'	   => 0,
										'journal'	=> 'email-client',
										'event_id'   => $txn->custom_int1,
										'html'	   => true,
										'cc_dj'	  => false,
										'cc_admin'   => !empty( $mdjm_settings['email']['bcc_admin_to_client'] ) ? true : false,
										'source'	 => 'Automated payment received',
										'add_filters'=>	array(
																'{PAYMENT_FOR}'	=> $txn->custom_str1,
																'{PAYMENT_AMOUNT}' => display_price( $txn->amount_gross, true ),
																'{PAYMENT_DATE}'   => date( MDJM_SHORTDATE_FORMAT, current_time( 'timestamp' ) ) )
									) );
				if( $payment_rcvd_email )	{
					if( MDJM_DEBUG == true )
						 $GLOBALS['mdjm_debug']->log_it( '	-- Payment receipt email sent to client' );
						 
					$this->mdjm_output .= 'Payment receipt email sent to client' . "\r\n";
				}
				else	{
					if( MDJM_DEBUG == true )
						 $GLOBALS['mdjm_debug']->log_it( '	ERROR: Payment receipt email was not sent' );
						 
					$this->mdjm_output .= 'Payment receipt email was not sent to client' . "\r\n";	
				}
				return $payment_rcvd_email;
			} // client_email
			
			/*
			 * Email the admin re the transaction
			 *
			 *
			 *
			 */
			function admin_email( $txn, $client_email )	{
				global $mdjm_posts, $mdjm_settings;
				
				$subject = __( 'Payment received for Event', 'mobile-dj-manager' ) . ' ' . MDJM_EVENT_PREFIX . $txn->custom_int1;
				$body = '<html>' . "\n" . '<body>' . "\n";
				$body .= '<p>' . __( 'Hi there', 'mobile-dj-manager' ) . ',</p>' . "\n";
				$body .= '<p>' . __( 'A payment has just been from PayFast via the Mobile DJ Manager for WordPress plugin...', 'mobile-dj-manager' ) . '...</p>' . "\n";
				$body .= '<hr />' . "\n";
				$body .= '<h4><a href="' . get_edit_post_link( $txn->custom_int1 ) . '">' . __( 'Event ID', 'mobile-dj-manager' ) . ': ' 
					. MDJM_EVENT_PREFIX . $txn->custom_int1 . '</a></h4>' . "\n";
				$body .= '<p>' . "\n";
				$body .= __( 'Date', 'mobile-dj-manager' ) . ': {EVENT_DATE}<br />' . "\n";
				
				$event_stati = get_event_stati();
				
				$body .= __( 'Status', 'mobile-dj-manager' ) . ': ' . $event_stati[get_post_status( $txn->custom_int1 )] . '<br />' . "\n";
				$body .= __( 'Client', 'mobile-dj-manager' ) . ': {CLIENT_FULLNAME}<br />' . "\n";
				$body .= __( 'Payment Date/Time', 'mobile-dj-manager' ) . ': ' . date( MDJM_SHORTDATE_FORMAT . ' H:i:s', current_time( 'timestamp' ) ) . '<br />' . "\n";
				$body .= __( 'For', 'mobile-dj-manager' ) . ': ' . $txn->custom_str1 . '<br />' . "\n";
				$body .= __( 'Amount', 'mobile-dj-manager' ) . ': ' . display_price( $txn->amount_gross, true ) . '<br />' . "\n";
				
				if( !empty( $txn->amount_fee ) )
					$body .= '<span style="color: red;">' . __( 'Transaction Fee', 'mobile-dj-manager' ) . ': ' . 
						display_price( $txn->amount_fee, true ) . '</span><br />' . "\n";
					
				$body .= '<strong>' . __( 'Total Received', 'mobile-dj-manager' ) . ': ' . 
					display_price( $txn->amount_net ) . '</strong><br />' . "\n";
				
				$body .= __( 'Outstanding Event Balance', 'mobile-dj-manager' ) . ': {BALANCE}</p>' . "\n";
				
				if( !empty( $client_email ) && $mdjm_posts->post_exists( $client_email ) )
					$body .= '<p>' . sprintf( __( 'The client was emailed in reference to this payment. %sClick here%s to review their email', 'mobile-dj-manager' ),
								'<a href="' . get_edit_post_link( $client_email ) . '">',
								'</a>' ) . '</p>' . "\n";
								
				else
					$body .= '<p>' . __( 'The client was not emailed in reference to this payment', 'mobile-dj-manager' ) . '</p>' . "\n";
				
				$body .= '<hr />' . "\n";
				$body .= '<p>' . __( 'Regards', 'mobile-dj-manager' ) . '<br />' . "\n";
				$body .= MDJM_COMPANY . '</p>' . "\n";
				$body .= '</body>' . "\n";
				$body .= '</html>' . "\n";
				
				/* -- Fire the email with filtering -- */
				$GLOBALS['mdjm']->send_email( array( 
												'content'	=> $body,
												'to'		 => $mdjm_settings['email']['system_email'],
												'subject'	=> $subject,
												'journal'	=> false,
												'event_id'   => $txn->custom_int1,
												'cc_dj'	  => false,
												'cc_admin'   => false,
												'source'	 => 'Automated Payment Received',
												'log_comm'   => false ) );
													
			} // admin_email
			
		} // class MDJM_PayFast
	} // if( !class_exists( 'MDJM_PayFast_ITN' ) )	
	
	if( class_exists( 'MDJM_PayFast_ITN' ) )	{
		/* -- Instantiate the plugin class -- */
		$mdjm_PayFast = new MDJM_PayFast_ITN();
	}