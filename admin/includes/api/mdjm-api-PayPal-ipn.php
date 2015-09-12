<?php
/**
 * The PayPal IPN integration receives payment notifications from PayPal
 * and updates the MDJM plugin with transaction details
 *
 *
 */
	if( !class_exists( 'MDJM_PayPal_IPN' ) )	{
		class MDJM_PayPal_IPN	{
			function __construct()	{
				global $mdjm_settings;
				
				$this->paypal_settings = $mdjm_settings['paypal'];
				$this->payment_settings = $mdjm_settings['payments'];
				
				/** -- General -- */
				define( 'PP_LOG', ( !empty( $this->paypal_settings['paypal_debug'] ) ? true : false ) );
				define( 'PP_LOG_FILE', MDJM_PLUGIN_DIR . '/admin/includes/api/api-log/mdjm-paypal-ipn-debug.log' );
												
				$this->initialise();
				
				$this->PayPal_log( 'Starting PayPal IPN procedures', true );
				
				if( !empty( $this->paypal_settings['enable_sandbox'] ) )
					$this->PayPal_log( '--- Running in Sandbox Mode --' );
				
				if( !$this->pp_error )
					$this->data_dump();
										
				if( !$this->pp_error )
					$this->data_validation();
					
				if( !$this->pp_error )
					$this->get_server_data();
					
				/** -- Log the output -- */
				if( !empty( $this->pp_error ) )	{
					$this->PayPal_log( "\n\n" . 'ERRORS WERE ENCOUNTERED' );
					$this->PayPal_log( $this->output );
				}
				
				/** -- End the log -- */
				$this->PayPal_log( $this->mdjm_output );
				$this->PayPal_log( 'Completed PayPal IPN procedures', true );
			} // __construct
			
			/**
			 * Submit messages to debug file
			 *
			 *
			 *
			 */			
			function PayPal_log( $msg, $stampit=false )	{
				if( PP_LOG === false )
					return;
					
				$debug_log = ( $stampit == true ? date( 'd/m/Y  H:i:s', current_time( 'timestamp' ) ) . ' : ' . $msg : '    ' . $msg );
				
				error_log( $debug_log . "\r\n", 3, PP_LOG_FILE );	
			}
			
			/**
			 * Initialise the settings for the class and transaction
			 *
			 *
			 *
			 */
			function initialise()	{
			/** -- Notify PayPal that we have received information -- */
				header( 'HTTP/1.0 200 OK' );
				flush();
				
			/** -- Variables -- */
				$this->pp_error = false;
				$this->output = '';
				$this->pp_host = ( !empty( $this->paypal_settings['enable_sandbox'] ) ? 
					'www.sandbox.paypal.com' : 'www.paypal.com' );
					
				$this->receiver_email = ( !empty( $this->paypal_settings['enable_sandbox'] ) ? 
					$this->paypal_settings['sandbox_email'] : $this->paypal_settings['receiver_email'] );
					
			} // initialise
			
			/**
			 * Grab the submitted data and format
			 *
			 *
			 *
			 */
			function data_dump()	{
				$raw_data = file_get_contents( 'php://input' );
				$raw_data_array = explode( '&', $raw_data );
				$data_array = array();
				
				foreach( $raw_data_array as $keyval ) {
					$keyval = explode( '=', $keyval );
					
					if( count( $keyval ) == 2 )
						$data_array[$keyval[0]] = urldecode( $keyval[1] );
				}
				
				$this->req = 'cmd=_notify-validate';
				if( function_exists( 'get_magic_quotes_gpc' ) )
					$get_magic_quotes_exists = true;
				
				foreach( $data_array as $key => $value ) {
					if( $get_magic_quotes_exists == true && get_magic_quotes_gpc() == 1 )
						$value = urlencode( stripslashes( $value ) );

					else
						$value = urlencode( $value );

					$this->req .= "&$key=$value";
				}
			} // data_dump
						
			/**
			 * Connect to the PayPal server and validate the data we have received
			 *
			 *
			 *
			 */
			function data_validation()	{
			/** -- Use cURL -- */
				$this->output .= "\n\n" . 'Using cURL' . "\n\n";
				
				/** -- Configure cURL connection and options -- */
				$ch = curl_init();
				
				$curl_options = array(
					CURLOPT_HTTP_VERSION	=> CURL_HTTP_VERSION_1_1,
					CURLOPT_POST			=> true,
					CURLOPT_RETURNTRANSFER  => true,
					CURLOPT_POSTFIELDS	  => $this->req,
					CURLOPT_SSL_VERIFYPEER  => true,
					CURLOPT_SSL_VERIFYHOST  => 2,
					CURLOPT_FORBID_REUSE    => true,
					CURLOPT_CONNECTTIMEOUT  => 30,
					CURLOPT_HEADER		  => ( PP_LOG ),
					CURLOPT_HEADER		  => ( PP_LOG ),
					CURLOPT_HTTPHEADER	  => array( 'Connection: Close' ),
					// Standard settings
					CURLOPT_URL => 'https://'. $this->pp_host . '/cgi-bin/webscr' );
					
				curl_setopt_array( $ch, $curl_options );
	
				/** -- Execute cURL -- */
				$this->pp_result = curl_exec( $ch );
				
				if( curl_errno( $ch ) != 0 )	{ // cURL error
					$this->output .= 'Can\'t connect to PayPal to validate IPN message: ' . curl_error( $ch );
					
					$this->pp_error = true;
				}
				else { // Log the entire HTTP response if debug is switched on.
					$this->output .= 'HTTP request of validation request: ' . 
						curl_getinfo( $ch, CURLINFO_HEADER_OUT ) . ' for IPN payload: ' . $this->req;
						
					$this->output .= 'HTTP response of validation request: ' . $this->pp_result;
				}
				
				curl_close( $ch );
		 
				if( $this->pp_result === false )	{ // We have a cURL error, log it
					$this->pp_error = true;
					$this->pp_error_msg = 'An error occurred executing cURL';
				}
			} // data_validation
			
			/**
			 * Read the data from the server and parse to debugging
			 *
			 *
			 *
			 */
			function get_server_data()	{
				// Inspect the IPN validation result and take appropriate actions
				$tokens = explode( "\r\n\r\n", trim( $this->pp_result ) );
				$this->pp_result = trim( end( $tokens ) );
				
				// If the transaction was valid we can process for MDJM
				if( strcmp( $this->pp_result, "VERIFIED" ) == 0 ) {	
					$this->PayPal_log( 'Verified IPN: ' . $this->req );
					
					$txn = (object) $_POST;
					
					/* -- Run through the MDJM procedures to log the transaction -- */
					$this->process_txn( $txn );
				
				}
				// If the transaction was NOT valid
				else	{
					$this->pp_error = true;
					$this->pp_error_msg = 'The data received is invalid';
				}
			} // get_server_data
			
			/**
			 * Process the transaction updating the transaction, event and sending relevant emails
			 *
			 *
			 */
			function process_txn( $txn )	{
				global $mdjm_posts;
				$major_error = $this->mdjm_validate( $txn );
					
				/** -- We only continue here if there are no hard stop errors -- */
				if( !empty( $major_error ) )	{
					/** -- Remove the save post hook to avoid loops -- */
					remove_action( 'save_post', array( $mdjm_posts, 'save_custom_post' ), 10, 2 );
					
					$txn_post = $this->create_transaction( $txn );
					
					if( !empty( $txn_post ) )
						$this->update_event( $txn );
						$email_to_client = $this->client_email( $txn );
						$this->admin_email( $txn, $email_to_client );
						
					/** -- Re-Add the save post hook -- */
					add_action( 'save_post', array( $mdjm_posts, 'save_custom_post' ), 10, 2 );
					
				}
				else	{
					/** -- Email error report -- */
					if( !empty( $this->pp_error ) || !empty( $this->major_error ) )
						$GLOBALS['mdjm']->send_email(
												array( 
													'content'		=> $this->output . $this->mdjm_output,
													'to'			=> $GLOBALS['mdjm_settings']['email']['system_email'],
													'subject'		=> __( 'PayPal Transaction IPN Error Log',
																				'mobile-dj-manager' ),
													'journal'		=> false,
													'html'			=> false,
													'cc_admin'		=> false,
													'filter'		=> false,
													'log_comm'		=> false ) );
				}
			} // process_txn
			
			/**
			 * MDJM Validation checks
			 *
			 *
			 *
			 */
			function mdjm_validate( $txn )	{
				global $mdjm, $mdjm_posts;
				
				/** -- First we'll check for major errors that require the script to be halted -- */
				$major_error = false;
				$this->mdjm_output = '' . "\n";
				
				$this->mdjm_output .= '-------------------------------------------' . "\n";
				$this->mdjm_output .= '	STARTING MDJM VALIDATION PROCEDURES		' . "\n";
				$this->mdjm_output .= '-------------------------------------------' . "\n";
				
				$this->mdjm_output .= ( $txn->payment_status == 'Completed' ? 'PASS: ' : 'FAIL: ' ) . 
								'Payment Status: ' . $txn->payment_status . "\n";
				
				if( $txn->payment_status != 'COMPLETE' )
					$major_error = true;
				
				$this->mdjm_output .= ( $txn->receiver_email == $this->receiver_email ? 'PASS: ' : 'FAIL: ' ) . 
								'Receiver Email: ' . $txn->receiver_email . "\n";
				
				if( $txn->receiver_email != $this->paypal_settings['receiver_email'] )
					$major_error = true;
				
				$this->mdjm_output .= ( !empty( $txn->mc_gross ) ? 'PASS: ' : 'FAIL: ' ) . 
								'Gross Payment: ' . $txn->mc_gross . "\n";
				
				if( empty( $txn->mc_gross ) )
					$major_error = true;
				
				/** -- Associated Event ID -- */	
				$this->mdjm_output .= ( !empty( $txn->item_number ) && $mdjm_posts->post_exists( $txn->item_number ) ? 'PASS: ' : 'FAIL: ' ) . 
								'Event: ' . $txn->item_number . ' Exists' . "\n";
				
				if( empty( $txn->item_number ) )
					$major_error = true;
					
				/** -- What is being paid for? -- */	
				$this->mdjm_output .= ( !empty( $txn->custom ) ? 'PASS: ' : 'FAIL: ' ) . 
								'Payment for: ' . $txn->custom . "\n";
				
				if( empty( $txn->custom ) )
					$major_error = true;
					
				/** -- Ensure the PayPal Txn ID does not already exist -- */
				$txns = get_posts( array(
									'post_type'		=> MDJM_TRANS_POSTS,
									'post_status'	=> 'any',
									'meta_key'		=> '_mdjm_gw_txn_id',
									'meta_value'	=> $txn->invoice ) );
							
				$this->mdjm_output .= ( !empty( $txn->invoice ) ? 'PASS: ' : 'FAIL: ' ) . 
								'Unique Gateway Transaction ID: ' . $txn->invoice . "\n";
				
				if( !empty( $txns ) && count( $txns ) > '0' )
					$major_error = true;
								
				$this->mdjm_output .= '-------------------------------------------' . "\n";
				$this->mdjm_output .= '		MDJM VALIDATION PROCEDURES ' . ( $major_error == true ? 'FAILED' : 'PASSED' ) . "\n";
				$this->mdjm_output .= '-------------------------------------------' . "\n";
				
				return $major_error;
			} // mdjm_validate
			
			/**
			 * Create the transaction post
			 *
			 *
			 *
			 */
			function create_transaction( $txn )	{
				$this->mdjm_output .= '--- Beginning transaction post creation' . "\n";
								
				$trans_type = get_term_by( 'name', stripslashes( $txn->custom ), 'transaction-types' );
				
				/** -- Set the data for the transaction post -- */
				$trans_data = array(
								'ID'			=> $txn->invoice,
								'post_title'	=> MDJM_EVENT_PREFIX . $txn->invoice,
								'post_status' 	=> 'mdjm-income',
								'post_date'		=> date( 'Y-m-d H:i:s', ( !empty( $txn->payment_date ) ? strtotime( $txn->payment_date ) : current_time( 'timestamp' ) ) ),
								'edit_date'		=> true,
								'post_author'	=> get_post_meta( $txn->item_number, '_mdjm_event_client', true ),
								'post_type'		=> MDJM_TRANS_POSTS,
								'post_category'	=> ( !empty( $trans_type ) ? array( $trans_type->term_id ) : '' ),
								'post_parent'	=> $txn->item_number,
								'post_modified'	=> date( 'Y-m-d H:i:s', current_time( 'timestamp' ) ) );
				
				/** -- Now set the post metadata -- */
				$trans_meta = array(
								'_mdjm_txn_status'		=> $txn->payment_status,
								'_mdjm_txn_source'		=> 'PayPal',
								'_mdjm_gw_txn_id'		=> $txn->txn_id,
								'_mdjm_payer_firstname'	=> ( !empty( $txn->first_name ) ? 
														sanitize_text_field( ucfirst( stripslashes( $txn->first_name ) ) ) : '' ),
														
								'_mdjm_payer_lastname'	=> ( !empty( $txn->last_name ) ? 
														sanitize_text_field( ucfirst( stripslashes( $txn->last_name ) ) ) : '' ),
														
								'_mdjm_payer_email'		=> ( !empty( $txn->payer_email ) && is_email( $txn->payer_email ) ? 
														strtolower( $txn->payer_email ) : '' ),
														
								'_mdjm_payment_from'		=> ( !empty( $txn->first_name ) ? 
														sanitize_text_field( ucfirst( stripslashes( $txn->first_name ) ) ) : '' ) . ' ' . 
														( !empty( $txn->last_name ) ? 
															sanitize_text_field( ucfirst( stripslashes( $txn->last_name ) ) ) : '' ),
				
								'_mdjm_txn_net'			=> ( !empty( $txn->mc_fee ) ? 
														number_format( ( $txn->mc_gross - $txn->mc_fee ), 2 ) : '0.00' ),
																
								'_mdjm_txn_currency'	=> 'ZAR',
														
								'_mdjm_txn_fee'			=> ( !empty( $txn->mc_fee ) ? 
														number_format( substr( $txn->mc_fee, 1 ), 2 ) : '0.00' ),
														
								'_mdjm_txn_total'		=> ( !empty( $txn->mc_gross ) ? 
														number_format( $txn->mc_gross, 2 ) : '0.00' ),
														
								'_mdjm_txn_gw_response'	=> json_encode( $_POST ) );
												
				/** -- Create the transaction post -- */
				$trans_update = wp_update_post( $trans_data );
				
				if( $trans_update == 0 )
					$this->mdjm_output .= 'ERROR: Unable to add Transaction post data' . "\n";
				
				else	{
					$this->mdjm_output .= 'PASS: Transaction post data added successfully ' . $trans_update . "\n";
				
				/** -- Set the Transaction Type -- */
				if( !empty( $trans_type ) )
					wp_set_post_terms( $trans_update, $trans_type->term_id, 'transaction-types' );
					
					/** -- Now add the post meta -- */
					foreach( $trans_meta as $trans_meta_key => $trans_meta_value )	{
						if( add_post_meta( $trans_update, $trans_meta_key, $trans_meta_value ) )
							$this->mdjm_output .= 'PASS: ' . $trans_meta_key . ' transaction data added successfully ' . 
								' with value ' .$trans_meta_value . "\n";
								
						else
							$this->mdjm_output .= 'FAIL: ' . $trans_meta_key . ' transaction data with value ' .
								$trans_meta_value . ' could not be added' . "\n";
					}
				}
				
				if( !empty( $txn->mc_fee ) )	{
					/** -- Now add a new transaction for any merchant fees -- */
					if( !class_exists( 'MDJM_Transactions' ) )
						require_once( MDJM_PLUGIN_DIR . '/admin/includes/class/class-mdjm-transactions.php' );
				
					$mdjm_txns = new MDJM_Transactions();
					
					$txn_fee_data = array(
										'post_author' 	=> get_post_meta( $txn->custom, '_mdjm_event_client', true ),
										'post_type'		=> MDJM_TRANS_POSTS,
										'post_category'	=> ( !empty( $trans_type ) ? array( $trans_type->term_id ) : '' ),
										'post_parent'	=> $txn->item_number );
					$txn_fee_meta = array(
										'_mdjm_txn_status'		=> 'Completed',
										'_mdjm_txn_source'		=> __( 'PayPal', 'mobile-dj-manager' ),
										'_mdjm_txn_currency'	=> 'ZAR',
										'_mdjm_txn_total'		=> number_format( substr( $txn->mc_fee, 1 ), 2 ),
										'_mdjm_payment_to'		=> 'PayPal' );
					
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
			
			/**
			 * Update the Event as required
			 *
			 * @param	int		$event_id	ID of the event
			 *			objarr	$txn		The post data from the transaction
			 */
			function update_event( $txn )	{
				$update_fields = array(
								'_mdjm_event_last_updated_by'    => 0 );
				
				/** -- If the balance or deposit is paid, log it -- */				
				if( $txn->custom == MDJM_DEPOSIT_LABEL )
					$update_fields['_mdjm_event_deposit_status'] = 'Paid';	
			
				if( $txn->custom == MDJM_BALANCE_LABEL )
					$update_fields['_mdjm_event_balance_status'] = 'Paid';
					
				/** -- Update the Event postmeta -- */
				$field_updates = '';
				foreach( $update_fields as $event_meta_key => $event_meta_value )	{
					if( update_post_meta( $txn->item_number, $event_meta_key, $event_meta_value ) )
						$field_updates .= 'PASS: Field ' . $event_meta_key . ' updated with value ' . $event_meta_value . "\r\n";
					else
						$field_updates .= 'FAIL: Field ' . $event_meta_key . ' could not be updated with value ' . $event_meta_value . "\r\n";
				}
				$this->mdjm_output .= $field_updates . "\n";
				
				/** -- Update Journal with event updates -- */
				if( MDJM_JOURNAL == true )	{
					if( MDJM_DEBUG == true )
						$GLOBALS['mdjm_debug']->log_it( '	-- Adding journal entry' );
						
					$GLOBALS['mdjm']->mdjm_events->add_journal( array(
								'user' 			=> get_post_meta( $txn->item_number, '_mdjm_event_client', true ),
								'event'		   => $txn->item_number,
								'comment_content' => $txn->custom . ' of ' . display_price( $txn->mc_gross, true ) . ' received via PayPal for event',
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
			
			/**
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
										'to'		 => get_post_meta( $txn->item_number, '_mdjm_event_client', true ),
										'from'	   => 0,
										'journal'	=> 'email-client',
										'event_id'   => $txn->item_number,
										'html'	   => true,
										'cc_dj'	  => false,
										'cc_admin'   => !empty( $mdjm_settings['email']['bcc_admin_to_client'] ) ? true : false,
										'source'	 => 'Automated payment received',
										'add_filters'=>	array(
																'{PAYMENT_FOR}'	=> $txn->custom,
																'{PAYMENT_AMOUNT}' => display_price( $txn->mc_gross, true ),
																'{PAYMENT_DATE}'   => date( MDJM_SHORTDATE_FORMAT, 
																	( !empty( $txn->payment_date ) ? 
																	strtotime( $txn->payment_date ) : current_time( 'timestamp' ) ) ) ) ) );
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
			
			/**
			 * Email the admin re the transaction
			 *
			 *
			 *
			 */
			function admin_email( $txn, $client_email )	{
				global $mdjm_posts, $mdjm_settings;
				
				$subject = __( 'Payment received for Event', 'mobile-dj-manager' ) . ' ' . MDJM_EVENT_PREFIX . $txn->item_number;
				$body = '<html>' . "\n" . '<body>' . "\n";
				$body .= '<p>' . __( 'Hi there', 'mobile-dj-manager' ) . ',</p>' . "\n";
				$body .= '<p>' . __( 'A payment has just been from PayFast via the Mobile DJ Manager for WordPress plugin...', 'mobile-dj-manager' ) . '...</p>' . "\n";
				$body .= '<hr />' . "\n";
				$body .= '<h4><a href="' . get_edit_post_link( $txn->item_number ) . '">' . __( 'Event ID', 'mobile-dj-manager' ) . ': ' 
					. MDJM_EVENT_PREFIX . $txn->item_number . '</a></h4>' . "\n";
				$body .= '<p>' . "\n";
				$body .= __( 'Date', 'mobile-dj-manager' ) . ': {EVENT_DATE}<br />' . "\n";
				
				$event_stati = get_event_stati();
				
				$body .= __( 'Status', 'mobile-dj-manager' ) . ': ' . $event_stati[get_post_status( $txn->item_number )] . '<br />' . "\n";
				$body .= __( 'Client', 'mobile-dj-manager' ) . ': {CLIENT_FULLNAME}<br />' . "\n";
				$body .= __( 'Payment Date/Time', 'mobile-dj-manager' ) . ': ' . date( MDJM_SHORTDATE_FORMAT . ' H:i:s', 
																					( !empty( $txn->payment_date ) ? 
																					strtotime( $txn->payment_date ) : 
																					current_time( 'timestamp' ) ) ) . '<br />' . "\n";
																					
				$body .= __( 'For', 'mobile-dj-manager' ) . ': ' . $txn->custom . '<br />' . "\n";
				$body .= __( 'Amount', 'mobile-dj-manager' ) . ': ' . display_price( $txn->mc_gross, true ) . '<br />' . "\n";
				
				if( !empty( $txn->mc_fee ) )
					$body .= '<span style="color: red;">' . __( 'Transaction Fee', 'mobile-dj-manager' ) . ': ' . 
						display_price( $txn->mc_fee, true ) . '</span><br />' . "\n";
					
				$body .= '<strong>' . __( 'Total Received', 'mobile-dj-manager' ) . ': ' . 
					display_price( ( !empty( $txn->mc_fee ) ? ( $txn->mc_gross - $txn->mc_fee ) : $txn->mc_gross ) ) . '</strong><br />' . "\n";
				
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
				
				/** -- Fire the email with filtering -- */
				$GLOBALS['mdjm']->send_email( array( 
												'content'	=> $body,
												'to'		 => $mdjm_settings['email']['system_email'],
												'subject'	=> $subject,
												'journal'	=> false,
												'event_id'   => $txn->item_number,
												'cc_dj'	  => false,
												'cc_admin'   => false,
												'source'	 => 'Automated Payment Received',
												'log_comm'   => false ) );
													
			} // admin_email
			
		} // class MDJM_PayPal_IPN
	} // if( !class_exists( 'MDJM_PayPal_IPN' ) )	
	
	if( class_exists( 'MDJM_PayPal_IPN' ) )	{
		/** -- Instantiate the plugin class -- */
		$mdjm_PayPal = new MDJM_PayPal_IPN();
	}