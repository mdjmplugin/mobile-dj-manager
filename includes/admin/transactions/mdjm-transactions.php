<?php
/**
 * mdjm-transactions.php
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
		 * Retrieve the transactions for the specified event
		 *
		 * @param	int		$event_id		The post ID for the event we're querying
		 * @return	arr		$transactions	An array of all transaction posts associated with the event
		 */
		public function get_event_transactions( $event_id )	{
			global $mdjm;
			
			if( empty( $event_id ) )	{
				if( MDJM_DEBUG == true )
					MDJM()->debug->log_it( 'ERROR: No event ID provided in ' . __METHOD__, true );
				
				return false;
			}
			
			$transactions = get_posts( array(
										'post_type'	  => 'mdjm-transaction',
										'post_parent'	=> $event_id,
										'post_status'	=> 'any',
										'posts_per_page' => -1,
										'orderby'		=> 'post_date',
										'order'		  => 'ASC',
									) );
			return $transactions;
			
		} // get_event_transactions
		
		/**
		 * List the event transactions for client view
		 *
		 * @params	int			$eventID	Required. Post ID of the event
		 *			str			$display	Optional: 'list' to list each payment date, amount and reason
		 *
		 * @return	str						'No Payments Received' if no txns, otherwise txn detail as specified by $display
		 */
		function list_event_transactions( $eventID, $display = 'list' )	{
			$txns = $this->get_transactions( $eventID, 'mdjm-income', 'Completed', 'obj' );
			
			if( empty( $txns ) )
				return __( 'No Payments Found', 'mobile-dj-manager' );
			
			$i = 1;
			switch( $display )	{
				case 'list':
					foreach( $txns as $txn )	{
						$types = wp_get_object_terms( $txn->ID, 'transaction-types' );
			
						$txn_type = !is_wp_error( $types ) && !empty( $types ) ? $types[0]->name : '';
						
						$output = mdjm_currency_filter( mdjm_sanitize_amount( get_post_meta( $txn->ID, '_mdjm_txn_total', true ) ) );
						$output .=  ' ';
						$output .=  'on ' . date( MDJM_SHORTDATE_FORMAT, strtotime( $txn->post_date ) ) . ' (' . $txn_type . ')';
						
						if( $i < count( $txns ) )
							$output .= '<br />';
						
						$i++;	
					}
				break;	
			}
			return $output;
			
		} // list_event_transactions
		
		/*
		 * Retrieve transactions based on the parameters given
		 *
		 * @param	int		$event_id		Required: The post ID of the event to query
		 *			str		$direction		Optional: in|out|any (default)
		 *			str		$status			Optional: (default) Completed|Cancelled|Pending
		 *			str		$return			Optional: (default) value|transactions (obj arr)
		 *
		 */
		function get_transactions( $event_id, $direction='any', $status='Completed', $return='value' )	{
						
			$total = '0.00';
			
			$transactions = get_posts( array(
										'post_type'	  => 'mdjm-transaction',
										'post_parent'	=> $event_id,
										'post_status'	=> $direction,
										'posts_per_page' => -1,
										'orderby'		=> 'post_date',
										'order'		  => 'ASC',
										'meta_key'	   => '_mdjm_txn_status',										
										'meta_query'	 => array(
											'key'		=> '_mdjm_txn_status',
											'value'  	  => $status,
											'compare'	=> '=' ) ) );
											
			if( $transactions )	{
				foreach( $transactions as $txn )	{
					$total += get_post_meta( $txn->ID, '_mdjm_txn_total', true );
				}
			}
			
			return ( $return == 'value' ? $total : $transactions );
		} // get_transactions
		
		/*
		 * Add a new transaction
		 *
		 * @param	arr		$data		Required: An array of the post data to insert
		 *			arr		$meta		Optional: An array of meta data to add for the post
		 *			str		$direction	Optional: The direction of the transaction. Default to In
		 *			arr		$source		Optional: The term to associated with the transaction - i.e. the source of the payment (PayPal etc)
		 * @return	int|bool			Post ID on successful insertion, or false on failure
		 */
		function add_transaction( $data, $meta='', $direction='in', $type='' )	{
			global $mdjm_settings;
			
			if( MDJM_DEBUG == true )
				MDJM()->debug->log_it( 'Beginning transaction add procedure in ' . __METHOD__, true );
			
			// Define the post status
			$txn_status = ( strtolower( $direction ) == 'in' ? 'mdjm-income' : 'mdjm-expenditure' );
			
			// Define the source of payment
			if( !empty( $type ) )	{
				//$txn_type = get_term_by( 'name', $type, 'transaction-types' );
				if( MDJM_DEBUG == true )
					MDJM()->debug->log_it( 'term found with ID ' . $type, true );
			}
			
			/* -- Get the new post ID -- */
			if( !function_exists( 'get_default_post_to_edit' ) )
				require_once( ABSPATH . '/wp-admin/includes/post.php' );
			
			$trans_post = get_default_post_to_edit( 'mdjm-transaction', true );
			
			$trans_data['ID'] = MDJM_EVENT_PREFIX . $trans_post->ID;
			$trans_data['post_title'] = MDJM_EVENT_PREFIX . $trans_post->ID;
			$trans_data['post_status'] = $txn_status;
			$trans_data['post_date'] = ( !empty( $data['post_date'] ) ? $data['post_date'] : date( 'Y-m-d H:i:s', current_time( 'timestamp' ) ) );
			$trans_data['edit_date'] = true;
			
			$trans_data['post_author'] = ( !empty( $data['post_author'] ) ? $data['post_author'] : 1 );
			$trans_data['post_type'] = 'mdjm-transaction';
			$trans_data['post_category'] = ( !empty( $type ) ? array( $type ) : '' );
			$trans_data['post_parent'] = ( !empty( $data['post_parent'] ) ? $data['post_parent'] : '' );
			$trans_data['post_modified'] = date( 'Y-m-d H:i:s', current_time( 'timestamp' ) );
			
			$trans_meta['_mdjm_txn_status'] = ( !empty( $meta['_mdjm_txn_status'] ) ? $meta['_mdjm_txn_status'] : 'Completed' );
			$trans_meta['_mdjm_txn_source'] = ( !empty( $meta['_mdjm_txn_source'] ) ? $meta['_mdjm_txn_source'] : $mdjm_settings['payments']['default_type'] );
			$trans_meta['_mdjm_gw_txn_id'] = ( !empty( $meta['_mdjm_gw_txn_id'] ) ? $meta['_mdjm_gw_txn_id'] : '' );
			$trans_meta['_mdjm_payer_firstname'] = ( !empty( $meta['_mdjm_payer_firstname'] ) ? 
				sanitize_text_field( ucfirst( stripslashes( $meta['_mdjm_payer_firstname'] ) ) ) : '' );
													
			$trans_meta['_mdjm_payer_lastname'] = ( !empty( $meta['_mdjm_payer_lastname'] ) ? 
				sanitize_text_field( ucfirst( stripslashes( $meta['_mdjm_payer_lastname'] ) ) ) : '' );
													
			$trans_meta['_mdjm_payer_email'] = ( !empty( $meta['_mdjm_payer_email'] ) ? 
				strtolower( $meta['_mdjm_payer_email'] ) : '' );
			
			$trans_meta['_mdjm_txn_net'] = ( !empty( $meta['_mdjm_txn_net'] ) ? 
													mdjm_format_amount( $meta['_mdjm_txn_net'] ) : '0.00' );
															
			$trans_meta['_mdjm_txn_currency'] = ( !empty( $meta['_mdjm_txn_currency'] ) ? 
				strtoupper( $meta['_mdjm_txn_currency'] ) : $mdjm_settings['payments']['currency'] );
													
			$trans_meta['_mdjm_txn_fee'] = ( !empty( $meta['_mdjm_txn_fee'] ) ? 
													mdjm_format_amount( $meta['_mdjm_txn_fee'] ) : '0.00' );
													
			$trans_meta['_mdjm_txn_total'] = ( !empty( $meta['_mdjm_txn_total'] ) ? 
													mdjm_format_amount( $meta['_mdjm_txn_total'] ) : '0.00' );
													
			$trans_meta['_mdjm_payment_to'] = ( !empty( $meta['_mdjm_payment_to'] ) ? 
													$meta['_mdjm_payment_to'] : '' );
													
			$trans_meta['_mdjm_payment_from'] = ( !empty( $meta['_mdjm_payment_from'] ) ? 
													$meta['_mdjm_payment_from'] : '' );	
			
			// Add the transaction	
			$txn_id = wp_update_post( $trans_data );
			if( !empty( $txn_id ) )	{
				if( MDJM_DEBUG == true )
					MDJM()->debug->log_it( 'Added transaction with ID: ' . $txn_id );
									
				// Set the transaction source (term)
				if( !empty( $type ) )	{
					wp_set_post_terms( $txn_id, $type, 'transaction-types' );
						if( MDJM_DEBUG == true )
							MDJM()->debug->log_it( 'Assigning transaction source ID ' . $type );
				}
					
				// Add the meta data
				if( !empty( $trans_meta ) && is_array( $trans_meta ) )	{
					foreach( $trans_meta as $key => $value )	{
						if( add_post_meta( $txn_id, $key, $value ) )	{
							if( MDJM_DEBUG == true )
								MDJM()->debug->log_it( 'Meta key ' . $key . ' added with value ' . $value );
						}
						else	{
							if( MDJM_DEBUG == true )
								MDJM()->debug->log_it( 'Failed to add Meta key ' . $key . ' with value ' . $value );	
						}
					}
				}
			}
			else	{
				if( MDJM_DEBUG == true )
					MDJM()->debug->log_it( 'Failed to add transaction' );
					
				return false;
			}
			
			return $txn_id;
		} // add_transaction
	} // class MDJM_Transactions
