<?php
/*
* mdjm-api-pp-ipn.php
* 17/02/2015
* @since 1.1
* The IPN listener for PayPal payments confirms payments and takes appropriate actions
*/
	global $mdjm, $mdjm_settings, $mdjm_posts, $wpdb;
	
	$pp_debug = 0;
	$pp_sandbox = 0;
	
	// If debugging is enabled, we'll capture IPN requests into the specified log file for review
	
	if( isset( $mdjm_settings['paypal']['paypal_debug'] ) )
		$pp_debug = 1;

	if( isset( $mdjm_settings['paypal']['enable_sandbox'] ) )
		$pp_sandbox = 1;

	define( 'PP_DEBUG', $pp_debug );
	
	define( 'PP_LOG_FILE', MDJM_PLUGIN_DIR . '/admin/includes/api/api-log/mdjm-pp-ipn-debug.log' );
	
	define( 'USE_SANDBOX', $pp_sandbox );
	
	// Read the POST data received
	$raw_post_data = file_get_contents( 'php://input' );
	$raw_post_array = explode( '&', $raw_post_data );
	$my_post = array();
	
	foreach( $raw_post_array as $keyval ) {
		$keyval = explode( '=', $keyval );
		if( count( $keyval ) == 2 )
			$my_post[$keyval[0]] = urldecode( $keyval[1] );
	}
	
	$req = 'cmd=_notify-validate';
	if( function_exists( 'get_magic_quotes_gpc' ) ) {
		$get_magic_quotes_exists = true;
	}
	
	foreach( $my_post as $key => $value ) {
		if( $get_magic_quotes_exists == true && get_magic_quotes_gpc() == 1 ) {
			$value = urlencode( stripslashes( $value ) );
		} 
		else {
			$value = urlencode( $value );
		}
		$req .= "&$key=$value";
	}
	
	$pp_email = $mdjm_settings['paypal']['receiver_email'];
	
	// Now post the IPN data back to PayPal to validate the IPN request is real
	if( USE_SANDBOX == true ) {
		error_log( date( '[' . MDJM_SHORTDATE_FORMAT . ' ' . MDJM_TIME_FORMAT . '] ' ). "Using Sandbox" . PHP_EOL, 3, PP_LOG_FILE );
		$paypal_url = "https://www.sandbox.paypal.com/cgi-bin/webscr";
		
		if( isset( $mdjm_settings['paypal']['sandbox_email'] ) && !empty( $mdjm_settings['paypal']['sandbox_email'] ) )	{
			$pp_email = $mdjm_settings['paypal']['sandbox_email'];
		}
	} 
	else {
		error_log( date( '[' . MDJM_SHORTDATE_FORMAT . ' ' . MDJM_TIME_FORMAT . '] ' ). "Using PayPal Live" . PHP_EOL, 3, PP_LOG_FILE );
		$paypal_url = "https://www.paypal.com/cgi-bin/webscr";
	}
	$ch = curl_init( $paypal_url );
	if( $ch == FALSE ) {
		return FALSE;
	}
	curl_setopt( $ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1 );
	curl_setopt( $ch, CURLOPT_POST, 1 );
	curl_setopt( $ch, CURLOPT_RETURNTRANSFER,1 );
	curl_setopt( $ch, CURLOPT_POSTFIELDS, $req );
	curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, 1 );
	curl_setopt( $ch, CURLOPT_SSL_VERIFYHOST, 2 );
	curl_setopt( $ch, CURLOPT_FORBID_REUSE, 1 );
	
	if( PP_DEBUG == true ) {
		curl_setopt( $ch, CURLOPT_HEADER, 1 );
		curl_setopt( $ch, CURLINFO_HEADER_OUT, 1 );
	}
	
	// Set a Timeout value
	curl_setopt( $ch, CURLOPT_CONNECTTIMEOUT, 30 );
	curl_setopt( $ch, CURLOPT_HTTPHEADER, array( 'Connection: Close' ) );
	
	$res = curl_exec($ch);
	if( curl_errno( $ch ) != 0 )	{ // cURL error
		if( PP_DEBUG == true ) {	
			error_log( date( '[' . MDJM_SHORTDATE_FORMAT . ' ' . MDJM_TIME_FORMAT . '] ' ) . "Can't connect to PayPal to validate IPN message: " . curl_error( $ch ) . PHP_EOL, 3, PP_LOG_FILE );
		}
		curl_close( $ch );
		exit;
	}
	else { // Log the entire HTTP response if debug is switched on.
		if( PP_DEBUG == true ) {
			error_log( date( '[' . MDJM_SHORTDATE_FORMAT . ' ' . MDJM_TIME_FORMAT . '] ' ) . "HTTP request of validation request:". curl_getinfo( $ch, CURLINFO_HEADER_OUT ) ." for IPN payload: $req" . PHP_EOL, 3, PP_LOG_FILE );
			error_log( date( '[' . MDJM_SHORTDATE_FORMAT . ' ' . MDJM_TIME_FORMAT . '] ' ) . "HTTP response of validation request: $res" . PHP_EOL, 3, PP_LOG_FILE );
		}
		curl_close( $ch );
	}
	
	// Now inspect the IPN validation result and take appropriate actions
	$tokens = explode( "\r\n\r\n", trim( $res ) );
	$res = trim( end( $tokens ) );
	
	// PayPal response VERIFIED
	if( strcmp( $res, "VERIFIED" ) == 0 ) {	
		if( PP_DEBUG == true ) {
			error_log( date( '[' . MDJM_SHORTDATE_FORMAT . ' ' . MDJM_TIME_FORMAT . '] ' ) . "Verified IPN: $req ". PHP_EOL, 3, PP_LOG_FILE );
			error_log( PHP_EOL . "Starting MDJM Validation ". PHP_EOL, 3, PP_LOG_FILE );
		}
		
		// We only process completed transactions
		if( $_POST['payment_status'] != 'Completed' ) {
			if( PP_DEBUG == true ) {
				error_log( 'CRITICAL: Incorrect payment status ' . $_POST['payment_status'] . PHP_EOL, 3, PP_LOG_FILE );
			}
			exit(0); 
		}
		else	{
			if( PP_DEBUG == true ) {
				error_log( 'PASS: Payment Status: ' . $_POST['payment_status'] . PHP_EOL, 3, PP_LOG_FILE );
			}
		}
		
		// Seller email must match configured receiver email
		if( $_POST['receiver_email'] != $pp_email ) {
			if( PP_DEBUG == true ) {
				error_log( 'WARNING: Receiver_email does not match: ' . $_POST['receiver_email'] . PHP_EOL, 3, PP_LOG_FILE );
			}
			$errmsg .= 'WARNING: Receiver_email does not match: ' . $_POST['receiver_email'] . "\n";
		}
		else	{
			if( PP_DEBUG == true ) {
				error_log( 'PASS: Receiver_email matches: ' . $_POST['receiver_email'] . PHP_EOL, 3, PP_LOG_FILE );
			}
		}
		
		// We need to know what is being paid for
		if( empty( $_POST['custom'] ) ) {
			if( PP_DEBUG == true ) {
				error_log( 'WARNING: We don\'t know what is being paid for : ' . $_POST['custom'] .  PHP_EOL, 3, PP_LOG_FILE );
			}
			$errmsg .= 'WARNING: We don\'t know what is being paid for : ' . $_POST['custom'] . "\n";
		}
		else	{
			if( PP_DEBUG == true ) {
				error_log( 'PASS: ' . $_POST['receiver_email'] . ' is being paid for' . PHP_EOL, 3, PP_LOG_FILE );
			}
		}
		
		// Make sure the currency is correct
		if( $_POST['mc_currency'] != $mdjm_settings['payments']['currency'] ) {
			if( PP_DEBUG == true ) {
				error_log( 'WARNING: Currency does not match: ' . $_POST['mc_currency'] . PHP_EOL, 3, PP_LOG_FILE );
			}
			$errmsg .= 'WARNING: Currency does not match: ' . $_POST['mc_currency'] . "\n";
		}
		else	{
			if( PP_DEBUG == true ) {
				error_log( 'PASS: Currency matches: ' . $_POST['mc_currency'] . PHP_EOL, 3, PP_LOG_FILE );
			}
		}
		
		// Make sure we have an Event ID (item_no)
		if( empty( $_POST['item_number'] ) ) {
			if( PP_DEBUG == true ) {
				error_log( 'WARNING: No item number (event id) received' . PHP_EOL, 3, PP_LOG_FILE );
			}
			$errmsg .= 'WARNING: No item number (event id) received' . "\n";
		}
		else	{
			if( PP_DEBUG == true ) {
				error_log( 'PASS: Event ID received: ' . $_POST['item_number'] . PHP_EOL, 3, PP_LOG_FILE );
			}
			// Make sure the Event ID exists
			if( !$mdjm_posts->post_exists( $_POST['item_number'] ) )	{
				if( PP_DEBUG == true ) {
					error_log( 'WARNING: No Event could be found for the item number: ' . $_POST['item_number'] . PHP_EOL, 3, PP_LOG_FILE );
				}
				$errmsg .= 'WARNING: No Event could be found for the item number: ' . $_POST['item_number'] . "\n";
			}
			else	{
				if( PP_DEBUG == true ) {
					$post = get_post( $_POST['item_number'] );
					error_log( 'PASS: Event found' . PHP_EOL, 3, PP_LOG_FILE );
				}
			}
			
			// Check the payment total is correct
			$balance = get_post_meta( $post->ID, '_mdjm_event_cost', true ); // Total event cost
			$deposit = get_post_meta( $post->ID, '_mdjm_event_deposit', true ); // Deposit associated to event
			if( get_post_meta( $post->ID, '_mdjm_event_deposit_status', true ) == 'Paid' )	{
				$balance = $balance - $deposit; // If the deposit is paid, deduct from the balance
			}
			
			// Include the taxes
			if( isset( $mdjm_settings['payments']['enable_tax'], $mdjm_settings['payments']['tax_type'], $mdjm_settings['payments']['tax_rate'] ) )	{
					
				// Add tax rate as percent to deposit & balance
				if( $mdjm_settings['payments']['tax_type'] == 'percentage' )	{
					$deposit *= '1.' . $mdjm_settings['payments']['tax_rate'];
					$balance *= '1.' . $mdjm_settings['payments']['tax_rate'];
				}
				// Add tax rate as fixed value to deposit & balance
				else	{
					$deposit += $mdjm_settings['payments']['tax_rate'];
					$balance += $mdjm_settings['payments']['tax_rate'];	
				}
			}
			
			// Validate the amount
			if( $_POST['mc_gross'] != number_format( $deposit, 2 ) && $_POST['mc_gross'] != number_format( $balance, 2 ) )	{
				if( PP_DEBUG == true ) {
					error_log( 'WARNING: Incorrect payment amount for ' . $_POST['custom'] . ': ' . $_POST['mc_gross'] . PHP_EOL, 3, PP_LOG_FILE );
				}
				$errmsg .= 'WARNING: Incorrect payment amount for ' . $_POST['custom'] . ': ' . $_POST['mc_gross'] . "\n";
			}
			else	{
			if( PP_DEBUG == true ) {
					error_log( 'PASS: Payment amount is correct: ' . $_POST['mc_gross'] . PHP_EOL, 3, PP_LOG_FILE );
				}
			}
		}
		
		// Remaining checks need info from the MDJM DB
		if( !isset( $db_tbl ) ) { 
			include( WPMDJM_PLUGIN_DIR . '/includes/config.inc.php' );
		}
		
		$txns = get_posts( array(
							'post_type'	=> MDJM_TRANS_POSTS,
							'post_status'  => 'any',
							'meta_key'	 => '_mdjm_paypal_txn_id',
							'meta_value'   => $_POST['txn_id'],
							) );
			
		// DB Error
		if( $txns ) {
			error_log( 'CRITICAL: DB Error ' . print_r( $txns ) . PHP_EOL, 3, PP_LOG_FILE );
			exit(0);
		}
		else	{
			if( PP_DEBUG == true ) {
				error_log( 'PASS: Database connection established' . PHP_EOL, 3, PP_LOG_FILE );
			}
		}
							
		// Transaction ID exists
		if( !empty( $txns ) && count( $txns ) > '0' )	{
			$errmsg .= 'Transaction ID has already been processed: ' . $_POST['txn_id'] . "\n";
		}
		else	{
			if( PP_DEBUG == true ) {
				error_log( 'PASS: No duplicate transaction ID found: ' . $_POST['txn_id'] . PHP_EOL, 3, PP_LOG_FILE );
			}
		}
		
		// If validation has failed anywhere we need to check it so we'll send via email
		if( !empty( $errmsg ) ) {
			if( PP_DEBUG == true ) {
				error_log( date( '[' . MDJM_SHORTDATE_FORMAT . ' ' . MDJM_TIME_FORMAT . '] ' ). $errmsg . PHP_EOL, 3, PP_LOG_FILE );
			}
			$body = 'IPN failed fraud checks: ' . "\n" . $errmsg . "\n\n";
			wp_mail( $mdjm_settings['email']['system_email'], 'IPN Fraud Warning', $body );
		}
		// If validation succeeds, we can process the order
		else	{
			/* -- Remove the save post hook to avoid loops -- */
			remove_action( 'save_post', array( $mdjm_posts, 'save_custom_post' ), 10, 2 );
			/* -- Create default post (auto-draft) so we can use the ID etc -- */
			require_once( ABSPATH . 'wp-admin/includes/post.php' );
			$trans_post = get_default_post_to_edit( MDJM_TRANS_POSTS, true );
			
			$trans_id = $trans_post->ID;
			
			$trans_type = get_term_by( 'name', stripslashes( $_POST['custom'] ), 'transaction-types' );
			
			/* -- Remap the fields -- */
			$trans_data['ID'] = $trans_id;
			$trans_data['post_title'] = MDJM_EVENT_PREFIX . $trans_id;
			$trans_data['post_status'] = 'mdjm-income';
			$trans_data['post_date'] = date( 'Y-m-d H:i:s', strtotime( $_POST['payment_date'] ) );
			$trans_data['edit_date'] = true;
				
			$trans_data['post_author'] = get_post_meta( $transaction->event_id, '_mdjm_event_client', true );
			$trans_data['post_type'] = MDJM_TRANS_POSTS;
			$trans_data['post_category'] = array( $trans_type->term_id );
			$trans_data['post_parent'] = $_POST['item_number'];
			$trans_data['post_modified'] = date( 'Y-m-d H:i:s', strtotime( $_POST['payment_date'] ) );
			
			$trans_meta['_mdjm_txn_status'] = $_POST['payment_status'];
			$trans_meta['_mdjm_txn_source'] = 'PayPal';
			$trans_meta['_mdjm_paypal_txn_id'] = $_POST['txn_id'];
			$trans_meta['_mdjm_txn_type'] = sanitize_text_field( $_POST['payment_type'] );
			$trans_meta['_mdjm_txn_paypal_payer_id'] = sanitize_text_field( $_POST['payer_id'] );
			$trans_meta['_mdjm_payer_firstname'] = !empty( $_POST['first_name'] ) ? 
													sanitize_text_field( ucfirst( stripslashes( $_POST['first_name'] ) ) ) : '';
													
			$trans_meta['_mdjm_payer_lastname'] = !empty( $_POST['last_name'] ) ? 
													sanitize_text_field( ucfirst( stripslashes( $_POST['last_name'] ) ) ) : '';
													
			$trans_meta['_mdjm_payer_email'] = is_email( $_POST['payer_email'] ) ? 
													strtolower( $_POST['payer_email'] ) : '';
															
			$trans_meta['_mdjm_txn_currency'] = !empty( $_POST['mc_currency'] ) ? 
													sanitize_text_field( $_POST['mc_currency'] ) : '';
													
			$trans_meta['_mdjm_txn_tax'] = !empty( $_POST['tax'] ) ? 
													$_POST['tax'] : '0.00';
													
			$trans_meta['_mdjm_txn_total'] = !empty( $_POST['mc_gross'] ) ? 
													$_POST['mc_gross'] : '0.00';
													
			$trans_meta['_mdjm_txn_paypal_ipn'] = json_encode( $_POST );
			
			/* -- Create the transaction post -- */
			wp_update_post( $trans_data );
			
			/* -- Set the Transaction Type -- */
			wp_set_post_terms( $trans_id, $trans_type->term_id, 'transaction-types' );
			
			/* -- Add the transaction meta -- */
			foreach( $trans_meta as $trans_meta_key => $trans_meta_value )	{
				add_post_meta( $trans_id, $trans_meta_key, $trans_meta_value );	
			}
			
			// Update the Event meta
			if( MDJM_DEBUG == true )
				 $mdjm->debug_logger( '	-- Beginning Meta Updates' );
			$update_fields = array(
								'_mdjm_event_last_updated_by'    => 0,
								);
			
			if( $_POST['custom'] == MDJM_DEPOSIT_LABEL )
				$update_fields['_mdjm_event_deposit_status'] = 'Paid';	
			
			if( $_POST['custom'] == MDJM_BALANCE_LABEL )
				$update_fields['_mdjm_event_balance_status'] = 'Paid';	
			
			foreach( $update_fields as $event_meta_key => $event_meta_value )	{
				$current_meta = get_post_meta( $post->ID, $event_meta_key, true );
				update_post_meta( $post->ID, $event_meta_key, $event_meta_value );
				$field_updates[] = 'Field ' . $event_meta_key . ' updated: ' . $current_meta . ' changed to ' . $event_meta_value;
			}
			if( MDJM_DEBUG == true )
				$mdjm->debug_logger( '	-- Meta Updates Completed     ' . "\r\n" . '| ' .
					implode( "\r\n" . '     | ', $field_updates ) );
							
			// Update the Journal
			
			/* -- Update Journal with event updates -- */
			if( MDJM_JOURNAL == true )	{
				if( MDJM_DEBUG == true )
					$mdjm->debug_logger( '	-- Adding journal entry' );
					
				$mdjm->mdjm_events->add_journal( array(
							'user' 			=> get_post_meta( $post->ID, '_mdjm_event_client', true ),
							'event'		   => $post->ID,
							'comment_content' => 'Payment of ' . display_price( $_POST['mc_gross'] ) . 'received via PayPal for event ' . $_POST['custom'],
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
			
			/* -- Email client confirming receipt of payment -- */
			if( MDJM_DEBUG == true )
				$mdjm->debug_logger( 'Generating email...' );
					
			$payment_rcvd_email = $mdjm->send_email( array( 
									'content'	=> $mdjm_settings['templates']['payment_cfm_template'],
									'to'		 => get_post_meta( $post->ID, '_mdjm_event_client', true ),
									'from'	   => 0,
									'journal'	=> 'email-client',
									'event_id'   => $post->ID,
									'html'	   => true,
									'cc_dj'	  => false,
									'cc_admin'   => isset( $mdjm_settings['email']['bcc_admin_to_client'] ) ? true : false,
									'source'	 => display_price( $_POST['mc_gross'] ) . ' ' . $_POST['custom'] . ' payment received',
								) );
			if( $payment_rcvd_email )	{
				if( MDJM_DEBUG == true )
					 $mdjm->debug_logger( '	-- Confrmation email sent to client ' );
			}
			else	{
				if( MDJM_DEBUG == true )
					 $mdjm->debug_logger( '	ERROR: Confrmation email was not sent' );	
			}
			
			/* -- Email the Admin confirming payment -- */			
			$email_headers = 'MIME-Version: 1.0' . "\r\n";
			$email_headers .= 'Content-type: text/html; charset=UTF-8' . "\r\n";
			$email_headers .= 'From: ' . MDJM_COMPANY . ' <' . $mdjm_settings['email']['system_email'] . '>' . "\r\n";
			
			$subject = 'Payment received for Event ' . $_POST['item_number'];
			$body = '<html>' . "\n" . '<body>' . "\n";
			$body .= '<p>Hi there,</p>' . "\n";
			$body .= '<p>A payment has just been received...</p>' . "\n";
			$body .= '<hr />' . "\n";
			$body .= '<h4><a href="' . get_edit_post_link( $_POST['item_number'] ) . '">Event ID: ' 
				. $_POST['item_number'] . '</a><br />' . "\n";
			$body .= 'Event Date: ' . date( 'l, jS F Y', strtotime( get_post_meta( $post->ID, '_mdjm_event_date', true ) ) ) . '<br />' . "\n";
			
			$event_stati = get_event_stati();
			
			$body .= 'Event Status: ' . $event_stati[$post->post_status] . '<br />' . "\n";
			$body .= 'Client: ' . get_userdata( get_post_meta( $post->ID, '_mdjm_event_client', true ) )->display_name . '<br />' . "\n";
			$body .= 'Payment: ' . display_price( $_POST['mc_gross'] ) . ' (' . $_POST['custom'] . ')' . '<br />' . "\n";
			$body .= 'Payment by: ' . $_POST['first_name'] . ' ' . $_POST['last_name'] .' (' . $_POST['payer_email'] . ')</h4>' . "\n";
			$body .= 'Payment Date/Time: ' . date( MDJM_SHORTDATE_FORMAT . ' H:i:s', strtotime( $_POST['payment_date'] ) ) . '</h4>' . "\n";
			$body .= '<hr />' . "\n";
			$body .= '<p>Regards<br />' . "\n";
			$body .= MDJM_COMPANY . '</p>' . "\n";
			$body .= '</body>' . "\n" . '</html>' . "\n";
			
			if( wp_mail( $mdjm_settings['email']['system_email'], $subject, $body, $email_headers ) )	{
				if( PP_DEBUG == true )
					error_log( 'PASS: Payment confirmation sent to admin' . PHP_EOL, 3, PP_LOG_FILE );
			}
			else	{
				if( PP_DEBUG == true )
					error_log( 'WARNING: Payment confirmation could not be sent to admin' . PHP_EOL, 3, PP_LOG_FILE );
			}
		}
		if( PP_DEBUG == true ) {
			error_log( '--- End of Transaction ---' . PHP_EOL, 3, PP_LOG_FILE );
			error_log( '' . PHP_EOL, 3, PP_LOG_FILE );
		}
		/* -- Re-Add the save post hook -- */
		add_action( 'save_post', array( $mdjm_posts, 'save_custom_post' ), 10, 2 );
	}
	// PayPal response INVALID 
	elseif( strcmp ( $res, "INVALID" ) == 0 ) {
		// Log for manual investigation
		// Add business logic here which deals with invalid IPN messages
		if( PP_DEBUG == true ) {
			error_log( date( '[' . MDJM_SHORTDATE_FORMAT . ' ' . MDJM_TIME_FORMAT . '] ' ). "Invalid IPN: $req" . PHP_EOL, 3, PP_LOG_FILE );
		}
	}
?>