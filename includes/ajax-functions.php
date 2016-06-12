<?php
/**
 * AJAX Functions
 *
 * Process the AJAX actions. Frontend and backend
 *
 * @package     MDJM
 * @subpackage  Functions/AJAX
 * @since       1.3
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) )
	exit;

/**
 * Get AJAX URL
 *
 * @since	1.3
 * @return	str
*/
function mdjm_get_ajax_url() {
	$scheme = defined( 'FORCE_SSL_ADMIN' ) && FORCE_SSL_ADMIN ? 'https' : 'admin';

	$current_url = mdjm_get_current_page_url();
	$ajax_url    = admin_url( 'admin-ajax.php', $scheme );

	if ( preg_match( '/^https/', $current_url ) && ! preg_match( '/^https/', $ajax_url ) ) {
		$ajax_url = preg_replace( '/^http/', 'https', $ajax_url );
	}

	return apply_filters( 'mdjm_ajax_url', $ajax_url );
} // mdjm_get_ajax_url

/**
 * Save the client fields order during drag and drop.
 *
 *
 *
 */
function save_mdjm_client_field_order()	{
	$client_fields = get_option( 'mdjm_client_fields' );
			
	foreach( $_POST['fields'] as $order => $field )	{
		$i = $order + 1;
					
		$client_fields[$field]['position'] = $i;
		
	}
	update_option( 'mdjm_client_fields', $client_fields );
	
	die();
} // save_mdjm_client_field_order
add_action( 'wp_ajax_mdjm_update_client_field_order', 'save_mdjm_client_field_order' );

/**
 * Refresh the data within the client details table.
 *
 * @since	1.3.7
 *
 */
function mdjm_refresh_client_details_ajax()	{

	$result = array();

	ob_start();
	mdjm_do_client_details_table( $_POST['client_id'], $_POST['event_id'] );
	$result['client_details'] = ob_get_contents();
	ob_get_clean();

	echo json_encode( $result );

	die();

} // mdjm_refresh_client_details
add_action( 'wp_ajax_mdjm_refresh_client_details', 'mdjm_refresh_client_details_ajax' );

/**
 * Adds a new client from the event field.
 *
 * @since	1.3.7
 * @global	arr		$_POST
 * @return	arr
 */
function mdjm_add_client_ajax()	{

	$client_id   = false;
	$client_list = '';
	$result      = array();
	$message     = array();

	if ( ! is_email( $_POST['client_email'] ) )	{
		$message[] = __( 'Email address is invalid', 'mobile-dj-manager' );
	} elseif ( email_exists( $_POST['client_email'] ) )	{
		$message[] = __( 'Email address is already in use', 'mobile-dj-manager' );
	} else	{

		$user_data = array(
			'first_name'    => $_POST['client_firstname'],
			'last_name'     => ! empty( $_POST['client_lastname'] )	? ucwords( $_POST['client_lastname'] )	: '',
			'user_email'    => $_POST['client_email'],
			'client_phone'  => ! empty( $_POST['client_phone'] )		? $_POST['client_phone']				: '',
			'client_phone2' => ! empty( $_POST['client_phone2'] )		? $_POST['client_phone2']				: ''
		);

		$client_id = mdjm_add_client( $user_data );

	}
	
	$clients = mdjm_get_clients( 'client' );
		
	if ( ! empty( $clients ) )	{
		foreach( $clients as $client )	{
			$client_list .= sprintf( '<option value="%1$s"%2$s>%3$s</option>',
				$client->ID,
				$client->ID == $client_id ? ' selected="selected"' : '',
				$client->display_name
			);
		}
	}
	
	if ( empty( $client_id ) )	{
		$result = array(
			'type'    => 'error',
			'message' => explode( "\n", $message )
		);
	} else	{

		$result = array(
			'type'        => 'success',
			'client_id'   => $client_id,
			'client_list' => $client_list
		);
	}

	echo json_encode( $result );

	die();

} // mdjm_event_add_client
add_action( 'wp_ajax_mdjm_event_add_client', 'mdjm_add_client_ajax' );

/**
 * Save the custom event fields order for clients
 *
 *
 */
function mdjm_update_custom_field_client_order()	{
			
	foreach( $_POST['clientfields'] as $order => $id )	{
		$menu = $order + 1;
		
		wp_update_post( array(
							'ID'			=> $id,
							'menu_order'	=> $menu,
							) );	
	}
	die();
} // mdjm_update_custom_field_client_order
add_action( 'wp_ajax_mdjm_update_custom_field_client_order', 'mdjm_update_custom_field_client_order' );
	
/**
 * Save the custom event fields order for events
 *
 *
 */
function mdjm_update_custom_field_event_order()	{
			
	foreach( $_POST['eventfields'] as $order => $id )	{
		$menu = $order + 1;
		
		wp_update_post( array( 'ID' => $id, 'menu_order' => $menu ) );	
	}
	die();
} // mdjm_update_custom_field_event_order
add_action( 'wp_ajax_mdjm_update_custom_field_event_order', 'mdjm_update_custom_field_event_order' );
	
/**
 * Save the custom event fields order for venues
 *
 *
 */
function mdjm_update_custom_field_venue_order()	{
			
	foreach( $_POST['venuefields'] as $order => $id )	{
		$menu = $order + 1;
		
		wp_update_post( array(
							'ID'			=> $id,
							'menu_order'	=> $menu,
							) );	
	}
	die();
} // mdjm_update_custom_field_venue_order
add_action( 'wp_ajax_mdjm_update_custom_field_venue_order', 'mdjm_update_custom_field_venue_order' );
	
/**
 * Save the event transaction
 *
 *
 */
function mdjm_save_event_transaction()	{				
	global $mdjm_event;

	$result = array();

	$mdjm_event = new MDJM_Event( $_POST['event_id'] );
	$mdjm_txn   = new MDJM_Txn();
	
	$txn_data = array(
		'post_parent'           => $_POST['event_id'],
		'post_author'           => $mdjm_event->client,
		'post_status'           => $_POST['direction'] == 'Out' ? 'mdjm-expenditure' : 'mdjm-income',
		'post_date'             => date( 'Y-m-d H:i:s', strtotime( $_POST['date'] ) )
	);

	$txn_meta = array(
		'_mdjm_txn_status'      => 'Completed',
		'_mdjm_payment_from'    => $mdjm_event->client,
		'_mdjm_txn_total'       => $_POST['amount'],
		'_mdjm_payer_firstname' => mdjm_get_client_firstname( $mdjm_event->client ),
		'_mdjm_payer_lastname'  => mdjm_get_client_lastname( $mdjm_event->client ),
		'_mdjm_payer_email'     => mdjm_get_client_email( $mdjm_event->client ),
		'_mdjm_payment_from'    => mdjm_get_client_display_name( $mdjm_event->client ),
		'_mdjm_txn_source'      => $_POST['src']
	);
	
	if ( $_POST['direction'] == 'In' )	{
		if ( ! empty( $_POST['from'] ) )	{
			$txn_meta['_mdjm_payment_from'] = sanitize_text_field( $_POST['from'] );
		} else	{
			$txn_meta['_mdjm_payment_from'] = mdjm_get_client_display_name( $mdjm_event->client );
		}
	}
	
	if ( $_POST['direction'] == 'Out' )	{
		if ( ! empty( $_POST['to'] ) )	{
			$txn_meta['_mdjm_payment_to'] = sanitize_text_field( $_POST['to'] );
		} else	{
			$txn_meta['_mdjm_payment_to'] = mdjm_get_client_display_name( $mdjm_event->client );
		}
	}

	$mdjm_txn->create( $txn_data, $txn_meta );

	if ( $mdjm_txn->ID > 0 )	{
		$result['type'] = 'success';
		mdjm_set_txn_type( $mdjm_txn->ID, $_POST['for'] );
		
		$args = array(
			'user_id'          => get_current_user_id(),
			'event_id'         => $_POST['event_id'],
			'comment_content'  => sprintf( __( '%1$s payment of %2$s received for %3$s %4$s.', 'mobile-dj-manager' ),
				$_POST['direction'] == 'In' ? __( 'Incoming', 'mobile-dj-manager' ) : __( 'Outgoing', 'mobile-dj-manager' ),
				mdjm_currency_filter( mdjm_format_amount( $_POST['amount'] ) ),
				mdjm_get_label_singular( true ),
				mdjm_get_event_contract_id( $_POST['event_id'] )
			),
			'comment_type'     => 'mdjm-journal',
			'comment_date'     => current_time( 'timestamp' )
		);
		
		mdjm_add_journal( $args );
		
		$payment_for = $mdjm_txn->get_type();
		$amount      = mdjm_currency_filter( mdjm_format_amount( $_POST['amount'] ) );

		mdjm_add_content_tag( 'payment_for', __( 'Reason for payment', 'mobile-dj-manager' ), function() use ( $payment_for ) { return $payment_for; } );

		mdjm_add_content_tag( 'payment_amount', __( 'Payment amount', 'mobile-dj-manager' ), function() use ( $amount ) { return $amount; } );

		mdjm_add_content_tag( 'payment_date', __( 'Date of payment', 'mobile-dj-manager' ), 'mdjm_content_tag_ddmmyyyy' );

		/**
		 * Allow hooks into this payment. The hook is suffixed with 'in' or 'out' depending
		 * on the payment direction. i.e. mdjm_post_add_manual_txn_in and mdjm_post_add_manual_txn_out
		 *
		 * @since	1.3.7
		 * @param	int		$event_id
		 * @param	obj		$txn_id
		 */

		do_action( 'mdjm_post_add_manual_txn_' . strtolower( $_POST['direction'] ), $_POST['event_id'], $mdjm_txn->ID );

		$result['deposit_paid'] = 'N';
		$result['balance_paid'] = 'N';

		if ( $mdjm_event->get_remaining_deposit() < 1 )	{
			mdjm_update_event_meta( $mdjm_event->ID, array( '_mdjm_event_deposit_status' => 'Paid' ) );
			$result['deposit_paid'] = 'Y';
		}
		if ( $mdjm_event->get_balance() < 1 )	{
			mdjm_update_event_meta( $mdjm_event->ID, array( '_mdjm_event_balance_status' => 'Paid' ) );
			mdjm_update_event_meta( $mdjm_event->ID, array( '_mdjm_event_deposit_status' => 'Paid' ) );
			$result['balance_paid'] = 'Y';
			$result['deposit_paid'] = 'Y';
		}

	} else	{
		$result['type'] = 'error';
		$result['msg']  = __( 'Unable to add transaction', 'mobile-dj-manager' );
	}

	ob_start();
	mdjm_do_event_txn_table( $_POST['event_id'] );
	$result['transactions'] = ob_get_contents();
	ob_get_clean();

	echo json_encode( $result );

	die();
} // save_event_transaction
add_action( 'wp_ajax_add_event_transaction', 'mdjm_save_event_transaction' );
	
/**
 * Add a new event type
 * Initiated from the Event Post screen
 *
 */
function add_event_type()	{
	global $mdjm;
	
	MDJM()->debug->log_it( 'Adding ' . $_POST['type'] . ' new Event Type from Event Post form', true );
		
	$args = array( 
				'taxonomy'			=> 'event-types',
				'hide_empty' 		  => 0,
				'name' 				=> 'mdjm_event_type',
				'id' 				=> 'mdjm_event_type',
				'orderby' 			 => 'name',
				'hierarchical' 		=> 0,
				'show_option_none' 	=> __( 'Select Event Type' ),
				'class'			   => 'mdjm-meta required',
				'echo'				=> 0,
			);
			
	/* -- Validate that we have an Event Type to add -- */
	if( empty( $_POST['type'] ) )	{
		$result['type'] = 'Error';
		$result['msg'] = 'Please enter a name for the new Event Type';
	}
	/* -- Add the new Event Type (term) -- */
	else	{
		$term = wp_insert_term( $_POST['type'], 'event-types' );
		if( is_array( $term ) )	{
			$result['type'] = 'success';
		}
		else	{
			$result['type'] = 'error';
		}
	}
	
	MDJM()->debug->log_it( 'Completed adding ' . $_POST['type'] . ' new Event Type from Event Post form', true );
	
	$args['selected'] = $result['type'] == 'success' ? $term['term_id'] : $_POST['current'];
	
	$result['event_types'] = wp_dropdown_categories( $args );
	
	$result = json_encode($result);
	echo $result;
	
	die();
} // add_event_type
add_action( 'wp_ajax_add_event_type', 'add_event_type' );
	
/**
 * Add a new transaction type
 * Initiated from the Transaction Post screen
 *
 */
function add_transaction_type()	{
	global $mdjm;
	
	MDJM()->debug->log_it( 'Adding ' . $_POST['type'] . ' new Transaction Type from Transaction Post form', true );
		
	$args = array( 
				'taxonomy'			=> 'transaction-types',
				'hide_empty' 		  => 0,
				'name' 				=> 'mdjm_transaction_type',
				'id' 				=> 'mdjm_transaction_type',
				'orderby' 			 => 'name',
				'hierarchical' 		=> 0,
				'show_option_none' 	=> __( 'Select Transaction Type' ),
				'class'			   => ' required',
				'echo'				=> 0,
			);
			
	/* -- Validate that we have a Transaction Type to add -- */
	if( empty( $_POST['type'] ) )	{
		$result['type'] = 'Error';
		$result['msg'] = 'Please enter a name for the new Transaction Type';
	}
	/* -- Add the new Event Type (term) -- */
	else	{
		$term = wp_insert_term( $_POST['type'], 'transaction-types' );
		if( is_array( $term ) )	{
			$result['type'] = 'success';
		}
		else	{
			$result['type'] = 'error';
		}
	}
	
	MDJM()->debug->log_it( 'Completed adding ' . $_POST['type'] . ' new Transaction Type from Transaction Post form', true );
	
	$args['selected'] = $result['type'] == 'success' ? $term['term_id'] : $_POST['current'];
	
	$result['transaction_types'] = wp_dropdown_categories( $args );
	
	$result = json_encode($result);
	echo $result;
	
	die();
} // add_transaction_type
add_action( 'wp_ajax_add_transaction_type', 'add_transaction_type' );
	
/**
 * Update the event cost as the package changes
 *
 *
 *
 */
function update_event_cost_from_package()	{
	$current_package = get_post_meta( $_POST['event_id'], '_mdjm_event_package', true );
	$current_addons = get_post_meta( $_POST['event_id'], '_mdjm_event_addons', true );
	$packages = get_option( 'mdjm_packages' );
	$equipment = get_option( 'mdjm_equipment' );
	
	$event_cost = get_post_meta( $_POST['event_id'], '_mdjm_event_cost', true );
	
	if( !empty( $event_cost ) )
		$base_cost = !empty( $packages[$current_package]['cost'] ) ? (float)$event_cost - (float)$packages[$current_package]['cost'] : (float)$event_cost;
		
	else
		$base_cost = '0.00';
	
	if( !empty( $current_addons ) )	{
		foreach( $current_addons as $item )	{
			$base_cost = $base_cost - (float)$equipment[$item][7];	
		}
	}
	
	if( !empty( $packages[$_POST['package']]['cost'] ) )
		$cost = $base_cost + (float)$packages[$_POST['package']]['cost'];
	else
		$cost = $base_cost;
	
	if( !empty( $cost ) )	{
		$result['type'] = 'success';
		$result['cost'] = mdjm_sanitize_amount( (float)$cost );	
	}
	else	{
		$result['type'] = 'success';
		$result['cost'] = mdjm_format_amount( 0 );
	}
	
	$result = json_encode( $result );
	echo $result;
	
	die();
	
} // update_event_cost_from_package
add_action( 'wp_ajax_update_event_cost_from_package', 'update_event_cost_from_package' );
	
/**
 * Update the event cost as the addons change
 *
 *
 *
 */
function update_event_cost_from_addons()	{
	$current_package = get_post_meta( $_POST['event_id'], '_mdjm_event_package', true );
	$current_addons = get_post_meta( $_POST['event_id'], '_mdjm_event_addons', true );
	$packages = get_option( 'mdjm_packages' );
	$equipment = get_option( 'mdjm_equipment' );
	
	$event_cost = get_post_meta( $_POST['event_id'], '_mdjm_event_cost', true );
			
	if( !empty( $event_cost ) )
		$base_cost = !empty( $packages[$current_package]['cost'] ) ? (float)$event_cost - (float)$packages[$current_package]['cost'] : (float)$event_cost;
		
	else
		$base_cost = '0.00';
	
	if( !empty( $current_addons ) )	{
		foreach( $current_addons as $item )	{
			$base_cost = $base_cost - (float)$equipment[$item][7];	
		}
	}
	
	if( !empty( $packages[$_POST['package']]['cost'] ) )
		$cost = $base_cost + (float)$packages[$_POST['package']]['cost'];
	else
		$cost = $base_cost;
		
	if( !empty( $_POST['addons'] ) )	{
		foreach( $_POST['addons'] as $item )	{
			if( !empty( $equipment[$item][7] ) )
				$cost += (float)$equipment[$item][7];
		}
	}
	
	if( !empty( $cost ) )	{
		$result['type'] = 'success';
		$result['cost'] = mdjm_sanitize_amount( (float)$cost );	
	}
	else	{
		$result['type'] = 'success';
		$result['cost'] = mdjm_format_amount( 0 );
	}
	
	$result = json_encode( $result );
	echo $result;
	
	die();
	
} // update_event_cost_from_addons
add_action( 'wp_ajax_update_event_cost_from_addons', 'update_event_cost_from_addons' );

/**
 * Update the available list of packages and addons when selected event DJ changes
 *
 *
 *
 */
function mdjm_update_dj_package_options()	{
	$dj = $_POST['dj'];
	$event_package = ( !empty( $_POST['package'] ) ? $_POST['package'] : '' );
	$event_addons = ( !empty( $_POST['addons'] ) ? $_POST['addons'] : '' );
	
	$packages = mdjm_package_dropdown( array(
		'name'			=> '_mdjm_event_package',
		'dj'			  => !empty( $dj ) ? $dj : '',
		'selected'		=> !empty( $event_package ) ? $event_package : '',
		'first_entry'	 => 'No Package',
		'first_entry_val' => '0'
		),
		false
	);
	
	
	$addons = mdjm_addons_dropdown( array( 
		'name'		=> 'event_addons',
		'dj'		=> !empty( $dj ) ? $dj : '',
		'package'	=> !empty( $event_package ) ? $event_package : '',
		//'selected'	=> !empty( $event_addons ) ? $event_addons : '',
		),
		false
	);
			
	if( !empty( $addons ) || !empty( $packages ) )	{
		$result['type'] = 'success';
	}
	else	{
		$result['type'] = 'error';
		$result['msg'] = 'No packages or addons available';
	}
	
	if( !empty( $packages ) )
		$result['packages'] = $packages;
		
	else
		$result['packages'] = 'No Packages Available';
		
	if( !empty( $addons ) && $packages != '<option value="0">No Packages Available</option>' )
		$result['addons'] = $addons;
		
	else
		$result['addons'] = 'No Addons Available';
	
	$result = json_encode( $result );
	echo $result;
	
	die();
} // mdjm_update_dj_package_options
add_action( 'wp_ajax_mdjm_update_dj_package_options', 'mdjm_update_dj_package_options' );

/**
 * Update the event deposit amount based upon the event cost
 * and the payment settings
 *
 *
 */
function mdjm_update_event_deposit()	{
	$event_cost = $_POST['current_cost'];
	
	$deposit = mdjm_calculate_deposit( $event_cost );
			
	if( ! empty( $deposit ) )	{
		$result['type'] = 'success';
		$result['deposit'] = mdjm_sanitize_amount( $deposit );
	}
	else	{
		$result['type'] = 'error';
		$result['msg'] = 'Unable to calculate deposit';
	}
	
	$result = json_encode( $result );
	echo $result;
	
	die();
} // mdjm_update_event_deposit
add_action( 'wp_ajax_update_event_deposit', 'mdjm_update_event_deposit' );
	
/**
 * Add an employee to the event.
 *
 * @since	1.3
 *
 * @param
 * @return
 */
function mdjm_ajax_add_employee_to_event()	{
	
	$args = array(
		'id'              => isset( $_POST['employee_id'] )      ? $_POST['employee_id']      : '',
		'role'            => isset( $_POST['employee_role'] )    ? $_POST['employee_role']	: '',
		'wage'            => isset( $_POST['employee_wage'] )    ? $_POST['employee_wage']	: '',
		'payment_status'  => 'unpaid'
	);
	
	if( ! mdjm_add_employee_to_event( $_POST['event_id'], $args ) )	{
		
		$result['type'] = 'error';
		$result['msg'] = __( 'Unable to add employee', 'mobile-dj-manager' );
	
	} else	{
		
		$result['type'] = 'success';
	
	}
	
	$result['employees'] = mdjm_list_event_employees( $_POST['event_id'] );
		
	$result = json_encode( $result );
	
	echo $result;
	
	die();

} // mdjm_ajax_add_employee_to_event
add_action( 'wp_ajax_add_employee_to_event', 'mdjm_ajax_add_employee_to_event' );

/**
 * Remove an employee from the event.
 *
 * @since	1.3
 *
 * @param
 * @return
 */
function mdjm_ajax_remove_employee_from_event()	{
	
	mdjm_remove_employee_from_event( $_POST['employee_id'], $_POST['event_id'] );
		
	$result['type'] = 'success';
	$result['employees'] = mdjm_list_event_employees( $_POST['event_id'] );
			
	$result = json_encode( $result );
	
	echo $result;
	
	die();

} // mdjm_ajax_remove_employee_from_event
add_action( 'wp_ajax_remove_employee_from_event', 'mdjm_ajax_remove_employee_from_event' );

/**
 * Update the email content field with the selected template.
 *
 *
 * @since	1.3
 * @param
 * @return
 */
function mdjm_ajax_set_email_content()	{
		
	if ( empty( $_POST['template'] ) )	{
		$result['type'] = 'success';
		$result['updated_content'] = '';
	} else	{
		$content = mdjm_get_email_template_content( $_POST['template'] );
		
		if ( ! $content )	{
			$result['type'] = 'error';
			$result['msg']  = __( 'Unable to retrieve template content', 'mobile-dj-manager' );
		} else	{
			$result['type']            = 'success';
			$result['updated_content'] = $content;
			$result['updated_subject'] = html_entity_decode( get_the_title( $_POST['template'] ) );
		}
	}
	
	$result = json_encode( $result );
	
	echo $result;
	
	die();
	
} // mdjm_set_email_content
add_action( 'wp_ajax_mdjm_set_email_content', 'mdjm_ajax_set_email_content' );

/**
 * Update the email content field with the selected template.
 *
 *
 * @since	1.3
 * @param
 * @return
 */
function mdjm_ajax_user_events_dropdown()	{

	$result['event_list'] = '<option value="0">' . __( 'Select an Event', 'mobile-dj-manager' ) . '</option>';
	
	if ( ! empty( $_POST['recipient'] ) )	{
		
		$statuses = 'any';
		
		if ( mdjm_is_employee( $_POST['recipient'] ) )	{

			if ( mdjm_get_option( 'comms_show_active_events_only' ) )	{
				$statuses = array( 'post_status' => mdjm_active_event_statuses() );
			}

			$events = mdjm_get_employee_events( $_POST['recipient'], $statuses );

		} else	{

			if ( mdjm_get_option( 'comms_show_active_events_only' ) )	{
				$statuses = mdjm_active_event_statuses();
			}

			$events = mdjm_get_client_events( $_POST['recipient'], $statuses );
		}
		
		if ( $events )	{
			foreach ( $events as $event )	{
				
				$result['event_list'] .= '<option value="' . $event->ID . '">';
				$result['event_list'] .= mdjm_get_event_date( $event->ID ) . ' ';
				$result['event_list'] .= __( 'from', 'mobile-dj-manager' ) . ' ';
				$result['event_list'] .= mdjm_get_event_start( $event->ID ) . ' ';
				$result['event_list'] .= '(' . mdjm_get_event_status( $event->ID ) . ')';
				$result['event_list'] .= '</option>';
			}
		}
		
	}
	
	$result['type'] = 'success';
	$result = json_encode( $result );
	
	echo $result;
	
	die();
	
} // mdjm_ajax_client_events_dropdown
add_action( 'wp_ajax_mdjm_user_events_dropdown', 'mdjm_ajax_user_events_dropdown' );

/**
 * Check the availability status for the given date
 *
 * @since	1.3
 * @param	Global $_POST
 * @return	arr
 */
function mdjm_ajax_do_availability_check()	{
	
	$date = $_POST['check_date'];
	
	$avail_text   = ! empty( $_POST['avail_text'] )   ? $_POST['avail_text']   : mdjm_get_option( 'availability_check_pass_text' );
	$unavail_text = ! empty( $_POST['unavail_text'] ) ? $_POST['unavail_text'] : mdjm_get_option( 'availability_check_fail_text' );
	$search       = array( '{EVENT_DATE}', '{EVENT_DATE_SHORT}' );
	$replace      = array( date( 'l, jS F Y', strtotime( $date ) ), mdjm_format_short_date( $date ) );
	
	
	
	$result = mdjm_do_availability_check( $date );
	
	if( ! empty( $result['available'] ) )	{

		$result['result']  = 'available';
		$result['message'] = str_replace( $search, $replace, $avail_text );
		$result['message'] = mdjm_do_content_tags( $result['message'] );

	} else	{

		$result['result'] = 'unavailable';
		$result['message'] = str_replace( $search, $replace, $unavail_text );
		$result['message'] = mdjm_do_content_tags( $result['message'] );

	}
	
	echo json_encode( $result );
	
	die();
} // mdjm_ajax_do_availability_check
add_action( 'wp_ajax_mdjm_do_availability_check', 'mdjm_ajax_do_availability_check' );
add_action( 'wp_ajax_nopriv_mdjm_do_availability_check', 'mdjm_ajax_do_availability_check' );