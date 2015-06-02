<?php
/**
 * class-mdjm-transactions.php
 * 25/02/2015
 * @since 1.1
 * The class for MDJM transactions
 *
 * @version 1.0
 */

	class MDJM_Transactions	{
		
		/*
		 * Constructor
		 *
		 *
		 *
		 */
		public function __construct() {
			
		} // __construct
		
		/*
		 * Display the transactions for the event
		 *
		 *
		 * @param	int		$event_id		The ID of the event
		 *
		 */
		public function show_event_transactions( $event_id )	{
			
			$event_transactions = get_posts( array(
											'post_type'	  => MDJM_TRANS_POSTS,
											'post_parent'	=> $event_id,
											'post_status'	=> 'any',
											'posts_per_page' => -1,
											'orderby'		=> 'post_status',
										) );
			$transactions = '';
										
			if( count( $event_transactions ) == 0 )	{
				$transactions .= '<p>' . __( 'No transactions exist for this event yet.' ) . '</p>' . "\r\n";	
			}
			else	{
				/* -- Running Totals -- */
				$total_in = '0.00';
				$total_out = '0.00';
				
				$transactions .= '<table width="50%">' . "\r\n";
				$transactions .= '<thead>' . "\r\n";
				$transactions .= '<tr>' . "\r\n";
				$transactions .= '<th width="25%" align="left">Date</th>' . "\r\n";
				$transactions .= '<th width="25" align="left">In</th>' . "\r\n";
				$transactions .= '<th width="25%" align="left">Out</th>' . "\r\n";
				$transactions .= '<th width="25%" align="left">Details</th>' . "\r\n";
				$transactions .= '</tr>' . "\r\n";
				$transactions .= '</thead>' . "\r\n";
				$transactions .= '<tbody>' . "\r\n";
				
				foreach( $event_transactions as $transaction )	{
					$transactions .= '<tr>' . "\r\n";
					$transactions .= '<td><a href="' . get_edit_post_link( $transaction->ID ) . '">' . date( MDJM_SHORTDATE_FORMAT, strtotime( $transaction->post_date ) ) . '</a></td>' . "\r\n";
					
					$transactions .= '<td>';
					if ( $transaction->post_status == 'mdjm-income' )	{
						$transactions .= MDJM_CURRENCY . get_post_meta( $transaction->ID, '_mdjm_txn_total', true );
						$total_in += get_post_meta( $transaction->ID, '_mdjm_txn_total', true );
					}
					else	{
						$transactions .= '&ndash;';	
					}
					$transactions .= '</td>' . "\r\n";
					
					$transactions .= '<td>';
					if ( $transaction->post_status == 'mdjm-expenditure' )	{
						$transactions .= MDJM_CURRENCY . get_post_meta( $transaction->ID, '_mdjm_txn_total', true );
						$total_out += get_post_meta( $transaction->ID, '_mdjm_txn_total', true );
					}
					else	{
						$transactions .= '&ndash;';	
					}
					$transactions .= '</td>' . "\r\n";
					
					$trans_types = get_the_terms( $transaction->ID, 'transaction-types' );
					if( is_array( $trans_types ) )	{
						foreach( $trans_types as $key => $trans_type ) {
							$trans_types[$key] = $trans_type->name;
						}
						$transactions .= '<td>' . implode( "<br/>", $trans_types ) . '</td>' . "\r\n";
					}				
					$transactions .= '</tr>' . "\r\n";
				}
				$transactions .= '</tbody>' . "\r\n";
				$transactions .= '<tfoot>' . "\r\n";
				$transactions .= '<tr class="border_top">' . "\r\n";
				$transactions .= '<th width="25%" align="left">&nbsp;</th>' . "\r\n";
				$transactions .= '<th width="25%" align="left">' . MDJM_CURRENCY . number_format( $total_in, 2 ) . '</th>' . "\r\n";
				$transactions .= '<th width="25%" align="left">' . MDJM_CURRENCY . number_format( $total_out, 2 ) . '</th>' . "\r\n";
				$transactions .= '<th width="25%" align="left">Earnings: ' . MDJM_CURRENCY . number_format( $total_in - $total_out, 2 ) . '</th>' . "\r\n";
				$transactions .= '</tr>' . "\r\n";
				$transactions .= '</tfoot>' . "\r\n";
				$transactions .= '</table>' . "\r\n";
			}
			return $transactions;
		} // show_event_transactions
		
		/*
		 * Add and process manual event payments. i.e. if the admin marks
		 * deposit or balance as paid via the event admin page
		 *
		 * @param	str		$type		The payment type (Deposit or Balance)
		 *			int		$event_id	The Event ID to which payment is associated
		 *
		 */
		public function manual_event_payment( $type, $event_id )	{
			global $mdjm, $mdjm_posts, $mdjm_settings;
			
			remove_action( 'save_post', array( $mdjm_posts, 'save_custom_post' ), 10, 2 );
			
			// Add the transaction
			/* -- Create default post (auto-draft) so we can use the ID etc -- */
			require_once( ABSPATH . 'wp-admin/includes/post.php' );
			$trans_post = get_default_post_to_edit( MDJM_TRANS_POSTS, true );
			
			$trans_id = $trans_post->ID;
			$trans_type = get_term_by( 'name', $type, 'transaction-types' );
			
			// Event info
			$eventinfo = $mdjm->mdjm_events->event_detail( $event_id );

			/* -- Post Data -- */
			$trans_data['ID'] = $trans_id;
			$trans_data['post_title'] = MDJM_EVENT_PREFIX . $trans_id;
			$trans_data['post_status'] = 'mdjm-income';
				
			$trans_data['post_author'] = $user;
			$trans_data['post_type'] = MDJM_TRANS_POSTS;
			$trans_data['post_category'] = ( $type == MDJM_BALANCE_LABEL ? array( MDJM_BALANCE_LABEL ) : array( MDJM_DEPOSIT_LABEL ) );
			$trans_data['post_parent'] = $event_id;
			
			/* -- Post Meta -- */
			$trans_meta['_mdjm_txn_status'] = 'Completed';
			$trans_meta['_mdjm_txn_source'] = $mdjm_settings['payments']['pp_default_method'];
			$trans_meta['_mdjm_txn_total'] = ( $type == MDJM_BALANCE_LABEL ? str_replace( MDJM_CURRENCY, '', $eventinfo['balance'] ) 
				: str_replace( MDJM_CURRENCY, '', $eventinfo['deposit'] ) );
					
			$trans_meta['_mdjm_payer_firstname'] = !empty( $eventinfo['client']->first_name ) ? $eventinfo['client']->first_name : '';
													
			$trans_meta['_mdjm_payer_lastname'] = !empty( $eventinfo['client']->last_name ) ? $eventinfo['client']->last_name : '';
													
			$trans_meta['_mdjm_payer_email'] = !empty( $eventinfo['client']->user_email ) ? $eventinfo['client']->user_email : '';
			
			$trans_meta['_mdjm_payment_from'] = $eventinfo['client']->display_name;
																				
			$trans_meta['_mdjm_txn_currency'] = $mdjm_settings['main']['currency'];
			
			/* -- Create the transaction post -- */
			wp_update_post( $trans_data );
			
			/* -- Set the transaction Type -- */													
			wp_set_post_terms( $trans_id, $trans_type->term_id, 'transaction-types' );
			
			/* -- Add the meta data -- */
			foreach( $trans_meta as $trans_meta_key => $trans_meta_value )	{
				add_post_meta( $trans_id, $trans_meta_key, $trans_meta_value );	
			}
			
			$mdjm->debug_logger( 'Event Transaction procedure complete' );
			
			// Email client with defined template
			if( !empty( $mdjm_settings['payments']['pp_manual_cfm_template'] ) && $mdjm_settings['payments']['pp_manual_cfm_template'] != 0 )	{
				$mdjm->debug_logger( 'Configured to email client with payment receipt confirmation' );
												
				$filters = array(
							'{PAYMENT_FOR}'		=> ( $type == MDJM_BALANCE_LABEL ? MDJM_BALANCE_LABEL : MDJM_DEPOSIT_LABEL ),
							'{PAYMENT_AMOUNT}'	 => ( $type == MDJM_BALANCE_LABEL ? $eventinfo['balance'] : $eventinfo['deposit'] ),
							'{PAYMENT_DATE}'	   => date( MDJM_SHORTDATE_FORMAT ),
							);
							
				/* -- Get the template and perform the {PAYMENT_*) filter replacements -- */
				$template = get_post( $mdjm_settings['payments']['pp_manual_cfm_template'] );
				$message = $template->post_content;
				$message = apply_filters( 'the_content', $message );
				$message = str_replace( ']]>', ']]&gt;', $message );
				
				foreach( $filters as $key => $value )	{
					$search[] = $key;
					$replace[] = $value;	
				}
				
				$confirm_payment = $mdjm->send_email( array(
													'content'	=> str_replace( $search, $replace, $message ),
													'to'		 => $eventinfo['client']->ID,
													'subject'	=> str_replace( $search, $replace, get_the_title( $template->ID ) ),
													'event_id'   => $event_id,
													'html'	   => true,
													'source'	 => 'Event Admin',
													) );
				
			}
			else	{
				$mdjm->debug_logger( 'Skipping email as no template is defined for manual payment in ' . __METHOD__ );
			}
			add_action( 'save_post', array( $mdjm_posts, 'save_custom_post' ), 10, 2 );
			
		} // manual_event_payment
				
		/*
		 * Add event transaction record
		 *
		 * @param	int		$user		The ID of the user making the entry
		 *
		 */
		public function add_event_transaction( $user='' )	{
			global $mdjm, $mdjm_posts, $mdjm_settings, $current_user;
			
			/* -- Validation -- */
			if( empty( $_POST['amount'] ) )	{
				$result['type'] = 'error';
				$result['msg'] = 'You need to enter an Amount for the transaction';
			}
			elseif( empty( $_POST['date'] ) )	{
				$result['type'] = 'error';
				$result['msg'] = 'You need to enter a Date for the transaction';
			}
			elseif( empty( $_POST['for'] ) )	{
				$result['type'] = 'error';
				$result['msg'] = 'You need to select the Details for the transaction';
			}
			elseif( empty( $_POST['src'] ) )	{
				$result['type'] = 'error';
				$result['msg'] = 'You need to select the Source of the transaction';
			}
			/*elseif( empty( $_POST['tstatus'] ) )	{
				$result['type'] = 'error';
				$result['msg'] = 'You need to select confirm the status of the transaction';
			}*/
			
			else	{
				$mdjm->debug_logger( 'Starting the Add Transaction procedure', true );
				
				remove_action( 'save_post', array( $mdjm_posts, 'save_custom_post' ), 10, 2 );
				
				/* -- Who is adding the entry? -- */
				$user = !empty( $user ) ? $user : $current_user->ID;
				
				/* -- Create default post (auto-draft) so we can use the ID etc -- */
				require_once( ABSPATH . 'wp-admin/includes/post.php' );
				$trans_post = get_default_post_to_edit( MDJM_TRANS_POSTS, true );
				
				$trans_id = $trans_post->ID;
				$trans_type = get_term( $_POST['for'], 'transaction-types' );
				
				$trans_date = $_POST['date'];
							
				/* -- Post Data -- */
				$trans_data['ID'] = $trans_id;
				$trans_data['post_title'] = MDJM_EVENT_PREFIX . $trans_id;
				$trans_data['post_status'] = ( $_POST['direction'] == 'Out' ? 'mdjm-expenditure' : 'mdjm-income' );
				$trans_data['post_date'] = date( 'Y-m-d H:i:s', strtotime( $trans_date ) );
				$trans_data['edit_date'] = true;
					
				$trans_data['post_author'] = $user;
				$trans_data['post_type'] = MDJM_TRANS_POSTS;
				$trans_data['post_category'] = array( $_POST['for'] );
				$trans_data['post_parent'] = $_POST['event_id'];
				$trans_data['post_modified'] = time();
				
				/* -- Post Meta -- */
				$trans_meta['_mdjm_txn_status'] = 'Completed';
				$trans_meta['_mdjm_txn_source'] = $_POST['src'];
				$trans_meta['_mdjm_txn_total'] = number_format( $_POST['amount'], 2 );
				
				if( $_POST['direction'] == 'In' && empty( $_POST['from'] ) )	{
					$client = get_userdata( $_POST['client'] );	
				
					$trans_meta['_mdjm_payer_firstname'] = !empty( $client->first_name ) ? $client->first_name : '';
															
					$trans_meta['_mdjm_payer_lastname'] = !empty( $client->last_name ) ? $client->last_name : '';
															
					$trans_meta['_mdjm_payer_email'] = !empty( $client->user_email ) ? $client->user_email : '';
					
					$trans_meta['_mdjm_payment_from'] = $client->display_name;
															
				}
				if( $_POST['direction'] == 'In' && !empty( $_POST['from'] ) )	{
					$trans_meta['_mdjm_payment_from'] = sanizitize_text_field( $_POST['from'] );
				}
				
				if( $_POST['direction'] == 'Out' && empty( $_POST['to'] ) )	{
					$client = get_userdata( $_POST['client'] );	
				
					$trans_meta['_mdjm_payer_firstname'] = !empty( $client->first_name ) ? $client->first_name : '';
															
					$trans_meta['_mdjm_payer_lastname'] = !empty( $client->last_name ) ? $client->last_name : '';
															
					$trans_meta['_mdjm_payer_email'] = empty( $client->user_email ) ? $client->user_email : '';
					
					$trans_meta['_mdjm_payment_to'] = $client->display_name;
															
				}
				if( $_POST['direction'] == 'In' && !empty( $_POST['to'] ) )	{
					$trans_meta['_mdjm_payment_to'] = sanizitize_text_field( $_POST['to'] );
				}
							
				$trans_meta['_mdjm_txn_currency'] = $mdjm_settings['main']['currency'];
				
				/* -- Create the transaction post -- */
				wp_update_post( $trans_data );
				
				/* -- Set the transaction Type -- */													
				wp_set_post_terms( $trans_id, $trans_type->term_id, 'transaction-types' );
				
				/* -- Add the meta data -- */
				foreach( $trans_meta as $trans_meta_key => $trans_meta_value )	{
					add_post_meta( $trans_id, $trans_meta_key, $trans_meta_value );	
				}
				
				add_action( 'save_post', array( $mdjm_posts, 'save_custom_post' ), 10, 2 );
				
				$mdjm->debug_logger( 'Completed the Add Transaction procedure', true );
				$result['type'] = 'success';
			}
			$result['transactions'] = $this->show_event_transactions( $_POST['event_id'] );
			$result = json_encode($result);
			echo $result;
		} // add_event_transaction
		
		/*
		 * Retrieve the transactions for the specified event
		 *
		 * @param	int		$event_id		The post ID for the event we're querying
		 * @return	arr		$transactions	An array of all transaction posts associated with the event
		 */
		public function get_event_transactions( $event_id )	{
			global $mdjm;
			
			if( empty( $event_id ) )	{
				if( MDJM_DEBUG == true )
					$mdjm->debug_logger( 'ERROR: No event ID provided in ' . __METHOD__, true );
				
				return false;
			}
			
			$transactions = get_posts( array(
										'post_type'	  => MDJM_TRANS_POSTS,
										'post_parent'	=> $event_id,
										'post_status'	=> 'any',
										'posts_per_page' => -1,
										'orderby'		=> 'post_date',
										'order'		  => 'ASC',
									) );
			return $transactions;
			
		} // get_event_transactions
		
		/*
		 * Retrieve the event earnings for the employer or specified DJ
		 *
		 * @param	int		$event_id		The ID of the event
		 * @return	arr		$transactions
		 */
		public function get_earnings( $event_id )	{
			global $mdjm;
			
			if( empty( $event_id ) )	{
				if( MDJM_DEBUG == true )
					$mdjm->debug_logger( 'ERROR: No event ID provided in ' . __METHOD__, true );
				return false;
			}
									
			$event_transactions = $this->get_event_transactions( $event_id );
			
			$transactions['in'] = '0.00';
			$transactions['out'] = '0.00';
			$transactions['event'] = get_post_meta( $event_id, '_mdjm_event_cost', true );
			
			foreach( $event_transactions as $transaction )	{
				$status = get_post_meta( $transaction->ID, '_mdjm_txn_status', true );
				
				if( empty( $status ) || $status == 'Completed' )	{
					$key = $transaction->post_status == 'mdjm-income' ? 'in' : 'out';
					
					$transactions[$key] += get_post_meta( $transaction->ID, '_mdjm_txn_total', true );
				}
			}
			
			$transactions['earnings'] = $transactions['in'] - $transactions['out'];
			$transactions['profit'] = $transactions['event'] - $transactions['profit'];
			
			return $transactions;
		} // get_earnings
		
	} // class MDJM_Transactions
 
?>