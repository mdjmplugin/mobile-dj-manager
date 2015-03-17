<?php
/*
* mdjm-api-pp-ipn.php
* 17/02/2015
* @since 1.1
* The IPN listener for PayPal payments confirms payments and takes appropriate actions
*/
	global $mdjm_options, $wpdb, $mdjm_client_text;

	$mdjm_pp_options = get_option( 'mdjm_pp_options' );
	
	$pp_debug = 0;
	$pp_sandbox = 0;
	
	// If debugging is enabled, we'll capture IPN requests into the specified log file for review
	
	if( isset( $mdjm_pp_options['pp_debug'] ) && $mdjm_pp_options['pp_debug'] == 'Y' )	{
		$pp_debug = 1;
	}
	if( isset( $mdjm_pp_options['pp_sandbox'] ) && $mdjm_pp_options['pp_sandbox'] == 'Y' )	{
		$pp_sandbox = 1;
	}
	define( 'PP_DEBUG', $pp_debug );
	
	define( 'PP_LOG_FILE', WPMDJM_PLUGIN_DIR . '/admin/includes/api/api-log/mdjm-pp-ipn-debug.log' );
	
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
	
	$pp_email = $mdjm_pp_options['pp_receiver'];
	
	// Now post the IPN data back to PayPal to validate the IPN request is real
	if( USE_SANDBOX == true ) {
		error_log( date( '[' . $mdjm_options['short_date_format'] . ' ' . $mdjm_options['time_format'] . '] ' ). "Using Sandbox" . PHP_EOL, 3, PP_LOG_FILE );
		$paypal_url = "https://www.sandbox.paypal.com/cgi-bin/webscr";
		
		if( isset( $pp_options['pp_sandbox_email'] ) && !empty( $pp_options['pp_sandbox_email'] ) )	{
			$pp_email = $mdjm_pp_options['pp_sandbox_email'];
		}
	} 
	else {
		error_log( date( '[' . $mdjm_options['short_date_format'] . ' ' . $mdjm_options['time_format'] . '] ' ). "Using PayPal Live" . PHP_EOL, 3, PP_LOG_FILE );
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
			error_log( date( '[' . $mdjm_options['short_date_format'] . ' ' . $mdjm_options['time_format'] . '] ' ) . "Can't connect to PayPal to validate IPN message: " . curl_error( $ch ) . PHP_EOL, 3, PP_LOG_FILE );
		}
		curl_close( $ch );
		exit;
	}
	else { // Log the entire HTTP response if debug is switched on.
		if( PP_DEBUG == true ) {
			error_log( date( '[' . $mdjm_options['short_date_format'] . ' ' . $mdjm_options['time_format'] . '] ' ) . "HTTP request of validation request:". curl_getinfo( $ch, CURLINFO_HEADER_OUT ) ." for IPN payload: $req" . PHP_EOL, 3, PP_LOG_FILE );
			error_log( date( '[' . $mdjm_options['short_date_format'] . ' ' . $mdjm_options['time_format'] . '] ' ) . "HTTP response of validation request: $res" . PHP_EOL, 3, PP_LOG_FILE );
		}
		curl_close( $ch );
	}
	
	// Now inspect the IPN validation result and take appropriate actions
	$tokens = explode( "\r\n\r\n", trim( $res ) );
	$res = trim( end( $tokens ) );
	
	// PayPal response VERIFIED
	if( strcmp( $res, "VERIFIED" ) == 0 ) {	
		if( PP_DEBUG == true ) {
			error_log( date( '[' . $mdjm_options['short_date_format'] . ' ' . $mdjm_options['time_format'] . '] ' ) . "Verified IPN: $req ". PHP_EOL, 3, PP_LOG_FILE );
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
		if( $_POST['mc_currency'] != $mdjm_options['currency'] ) {
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
			$eventinfo = f_mdjm_get_eventinfo_by_id( $_POST['item_number'] );
			
			// No event
			if( !$eventinfo )	{
				if( PP_DEBUG == true ) {
					error_log( 'WARNING: No Event could be found for the item number: ' . $_POST['item_number'] . PHP_EOL, 3, PP_LOG_FILE );
				}
				$errmsg .= 'WARNING: No Event could be found for the item number: ' . $_POST['item_number'] . "\n";
			}
			else	{
				if( PP_DEBUG == true ) {
					error_log( 'PASS: Event found' . PHP_EOL, 3, PP_LOG_FILE );
				}
			}
			
			// Check the payment total is correct
			$balance = $eventinfo->cost; // Total event cost
			$deposit = $eventinfo->deposit; // Deposit associated to event
			if( $eventinfo->deposit_status == 'Paid' )	{
				$balance = $balance - $eventinfo->deposit; // If the deposit is paid, deduct from the balance
			}
			
			// Include the taxes
			if( isset( $pp_options['pp_enable_tax'], $pp_options['pp_tax_type'], $pp_options['pp_tax_rate'] ) && $pp_options['pp_enable_tax'] == 'Y' )	{
				// Add tax rate as percent to deposit & balance
				if( $mdjm_pp_options['pp_tax_type'] == 'percentage' )	{
					$deposit *= '1.' . $mdjm_pp_options['pp_tax_rate'];
					$balance *= '1.' . $mdjm_pp_options['pp_tax_rate'];
				}
				// Add tax rate as fixed value to deposit & balance
				else	{
					$deposit += $mdjm_pp_options['pp_tax_rate'];
					$balance += $mdjm_pp_options['pp_tax_rate'];	
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
			
		$txn_id = mysql_real_escape_string( $_POST['txn_id'] );
		
		$query = "SELECT * FROM `" . $db_tbl['trans'] . "` WHERE `payment_txn_id` = '" . $txn_id . "'";
		$r = $wpdb->get_results( $query );
		
		// DB Error
		if( $r ) {
			error_log( 'CRITICAL: DB Error ' . print_r( $r ) . PHP_EOL, 3, PP_LOG_FILE );
			error_log( $wpdb->print_error() );
			exit(0);
		}
		else	{
			if( PP_DEBUG == true ) {
				error_log( 'PASS: Database connection established' . PHP_EOL, 3, PP_LOG_FILE );
			}
		}
		
		$query = "SELECT COUNT(*) FROM `" . $db_tbl['trans'] . "` WHERE `payment_txn_id` = '" . $txn_id . "'";
		$result = $wpdb->get_var( $query );
					
		// Transaction ID exists
		if( $result > '0' )	{
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
				error_log( date( '[' . $mdjm_options['short_date_format'] . ' ' . $mdjm_options['time_format'] . '] ' ). $errmsg . PHP_EOL, 3, PP_LOG_FILE );
			}
			$body = 'IPN failed fraud checks: ' . "\n" . $errmsg . "\n\n";
			wp_mail( $mdjm_options['system_email'], 'IPN Fraud Warning', $body );
		}
		// If validation succeeds, we can process the order
		else	{
			// Update the transaction database
			$update_trans_query = $wpdb->insert( $db_tbl['trans'],
													array(
														'trans_id'		  => '',
														'direction'		 => 'In',
														'event_id'		  => $_POST['item_number'],
														'payment_src'	   => 'PayPal',
														'payment_txn_id'	=> $_POST['txn_id'],
														'payment_date'	  => date( 'Y-m-d H:i:s', strtotime( $_POST['payment_date'] ) ),
														'payment_type'	  => $_POST['payment_type'],
														'payer_id'	  	  => $_POST['payer_id'],
														'payment_status'	=> $_POST['payment_status'],
														'payer_firstname'   => stripslashes( $_POST['first_name'] ),
														'payer_lastname'	=> stripslashes( $_POST['last_name'] ),
														'payer_email'	   => $_POST['payer_email'],
														'payment_for'	   => stripslashes( $_POST['custom'] ),
														'payment_currency'  => $_POST['mc_currency'],
														'payment_tax'	   => $_POST['tax'],
														'payment_gross'	 => $_POST['mc_gross'],
														'full_ipn'		  => json_encode( $_POST ),
														'seen_by_admin'	 => 0,
														) );
			
			// Update the Events database
			$update_fields = array(
								'last_updated_by'    => 0,
								'last_updated'       => date( 'Y-m-d H:i:s' )
								);
			
			if( $_POST['custom'] == $mdjm_client_text['deposit_label'] )	{
				$update_fields['deposit_status'] = 'Paid';	
			}
			if( $_POST['custom'] == $mdjm_client_text['balance_label'] )	{
				$update_fields['balance_status'] = 'Paid';	
			}
			
			$update_events_query = $wpdb->update( $db_tbl['events'], 
												 $update_fields,
												array( 'event_id' => $_POST['item_number'] )
												);	
			
			// Update the Journal
			$clientinfo = get_userdata( $eventinfo->user_id );
			$j_args = array (
				'client'	=> $eventinfo->user_id,
				'event'		=> $_POST['item_number'],
				'author'	=> 0,
				'type'		=> 'Payment',
				'source'	=> 'System',
				'entry'		=> $_POST['custom'] . ' paid (' . $mdjm_currency[$mdjm_options['currency']] . $_POST['mc_gross'] . ')',
				);
			if( WPDJM_JOURNAL == 'Y' ) f_mdjm_do_journal( $j_args );
			
			// Email the Client confirming payment
			$email_headers = f_mdjm_client_email_headers( $eventinfo, 'admin' );
			$type = array( 'type' => 'custom', 'id' => $mdjm_pp_options['pp_cfm_template'], 'subject' => true );
			$info = f_mdjm_prepare_email( $eventinfo, $type );
			
			if( isset( $info['subject'] ) && !empty( $info['subject'] ) && isset( $mdjm_options['title_as_subject'] ) && $mdjm_options['title_as_subject'] == 'Y' )	{
				$subject = $info['subject'];	
			}
			else	{
				$subject = 'Event Payment Confirmation';	
			}
			
			$pp_content_search = array( '{PAYMENT_AMOUNT}', '{PAYMENT_DATE}', '{PAYMENT_FOR}' );
			$pp_content_replace = array( 
									$mdjm_currency[$mdjm_options['currency']] . $_POST['mc_gross'],
									date( $mdjm_options['short_date_format'], strtotime( $_POST['payment_date'] ) ),
									$_POST['custom'],
									);
			
			$subject = str_replace( $pp_content_search, $pp_content_replace, $subject );
			$info['content'] = str_replace( $pp_content_search, $pp_content_replace, $info['content'] );
			
			/* -- Insert the communication post */
			if( !class_exists( 'MDJM_Communication' ) )
				require_once( WPMDJM_PLUGIN_DIR . '/admin/includes/class/class-mdjm-communications.php' );
				
			$mdjm_comms = new MDJM_Communication();
			$p = $mdjm_comms->insert_comm( array (
												'subject'	=> wp_strip_all_tags( $subject ),
												'content'	=> $info['content'],
												'recipient'  => $eventinfo->user_id,
												'source'	 => 'Payment Confirmation',
												'event'	  => $eventinfo->event_id,
												'author'	 => '1',
												) );
			$info['content'] .= $mdjm_comms->insert_stat_image( $p );
			
			if( wp_mail( $clientinfo->user_email, $subject, $info['content'], $email_headers ) )	{
				$mdjm_comms->change_email_status( $p, 'sent' );
				
				if( PP_DEBUG == true ) {
					error_log( 'PASS: Email confirmation sent to client' . PHP_EOL, 3, PP_LOG_FILE );
				}	
			}
			else	{
				if( PP_DEBUG == true ) {
					error_log( 'WARNING: Email confirmation could not be sent to client' . PHP_EOL, 3, PP_LOG_FILE );
				}	
			}
			
			$j_args = array (
				'client'	=> $eventinfo->user_id,
				'event'		=> $_POST['item_number'],
				'author'	=> 0,
				'type'		=> 'Email Client',
				'source'	=> 'System',
				'entry'		=> 'Payment confirmation emailed to client'
				);
			if( WPDJM_JOURNAL == 'Y' ) f_mdjm_do_journal( $j_args );
			
			// Email the Admin confirming payment
			$email_headers = 'MIME-Version: 1.0' . "\r\n";
			$email_headers .= 'Content-type: text/html; charset=UTF-8' . "\r\n";
			$email_headers .= 'From: ' . $mdjm_options['company_name'] . ' <' . $mdjm_options['system_email'] . '>' . "\r\n";
			
			$subject = 'Payment received for Event ' . $_POST['item_number'];
			$body = '<html>' . "\n" . '<body>' . "\n";
			$body .= '<p>Hi there,</p>' . "\n";
			$body .= '<p>A payment has just been received...</p>' . "\n";
			$body .= '<hr />' . "\n";
			$body .= '<h4>Event ID: ' . $_POST['item_number'] . '<br />' . "\n";
			$body .= 'Event Date: ' . date( 'l, jS F Y', strtotime( $eventinfo->event_date ) ) . '<br />' . "\n";
			$body .= 'Event Status: ' . $eventinfo->contract_status . '<br />' . "\n";
			$body .= 'Client: ' . $clientinfo->display_name . '<br />' . "\n";
			$body .= 'Payment: ' . $mdjm_currency[$mdjm_options['currency']] . $_POST['mc_gross'] . ' (' . $_POST['custom'] . ')' . '<br />' . "\n";
			$body .= 'Payment by: ' . $_POST['first_name'] . ' ' . $_POST['last_name'] .' (' . $_POST['payer_email'] . ')</h4>' . "\n";
			$body .= 'Payment Date/Time: ' . date( $mdjm_options['short_date_format'] . 'H:i:s', strtotime( $_POST['payment_date'] ) ) . '</h4>' . "\n";
			$body .= '<hr />' . "\n";
			$body .= '<p>Regards<br />' . "\n";
			$body .= WPMDJM_CO_NAME . '</p>' . "\n";
			$body .= '</body>' . "\n" . '</html>' . "\n";
			
			if( wp_mail( $mdjm_options['system_email'], $subject, $body, $email_headers ) )	{
				if( PP_DEBUG == true ) {
					error_log( 'PASS: Payment confirmation sent to admin' . PHP_EOL, 3, PP_LOG_FILE );
				}	
			}
			else	{
				if( PP_DEBUG == true ) {
					error_log( 'WARNING: Payment confirmation could not be sent to admin' . PHP_EOL, 3, PP_LOG_FILE );
				}		
			}
		}
		if( PP_DEBUG == true ) {
			error_log( '--- End of Transaction ---' . PHP_EOL, 3, PP_LOG_FILE );
			error_log( '' . PHP_EOL, 3, PP_LOG_FILE );
		}
	}
	// PayPal response INVALID 
	elseif( strcmp ( $res, "INVALID" ) == 0 ) {
		// Log for manual investigation
		// Add business logic here which deals with invalid IPN messages
		if( PP_DEBUG == true ) {
			error_log( date( '[' . $mdjm_options['short_date_format'] . ' ' . $mdjm_options['time_format'] . '] ' ). "Invalid IPN: $req" . PHP_EOL, 3, PP_LOG_FILE );
		}
	}

?>