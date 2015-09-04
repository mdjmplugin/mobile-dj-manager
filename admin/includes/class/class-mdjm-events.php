<?php
	class MDJM_Events	{
		public function __construct()	{
			
		} // __construct

/*
 * Client functions
 */
		/*
		 * mdjm_add_client
		 * Add new client
		 * 
		 * @param: 
		 * @return: $client_id : false on fail
		 */
		public function mdjm_add_client()	{
			global $mdjm_settings;
			
			/* -- Validation -- */
			if( empty( $_POST['client_firstname'] ) || empty( $_POST['client_email'] ) )
				return $mdjm_error = 'Client data is missing';
									
			/* -- Define the client data -- */
			$clientdata['first_name'] = ucwords( $_POST['client_firstname'] );
			$clientdata['last_name'] = !empty( $_POST['client_lastname'] ) ? ucwords( $_POST['client_lastname'] ) : '';
			$clientdata['display_name'] = $clientdata['first_name'] . ' ' . $clientdata['last_name'];
			$clientdata['nice_name'] = $clientdata['first_name'];
			
			$clientdata['user_email'] = $_POST['client_email'];
			$clientdata['user_login'] = $clientdata['user_email'];
			$clientdata['user_pass'] = wp_generate_password( $mdjm_settings['clientzone']['pass_length'] );
			$clientdata['role'] = 'client';
			
			/* -- Create the user -- */
			$client_id = wp_insert_user( $clientdata );
			
			if( is_wp_error( $client_id ) )
				return 'Error adding client. ' . $client_id->get_error_message();
			
			/* -- Update client meta fields -- */
			wp_update_user( array( 'ID' => $client_id, 'show_admin_bar_front' => false ) );
			
			/* -- Update custom meta fields -- */
			/* -- Marketing -- */
			update_user_meta( $client_id, 'marketing', 'Y' );
			
			/* -- Primary Phone -- */
			if( !empty( $_POST['client_phone'] ) )
				 update_user_meta( $client_id, 'phone1', $_POST['client_phone'] );
			
			return $client_id;
		} // mdjm_add_client

		/**
		 * Retrieve list of clients by specified role
		 *
		 * @param:
		 *			$role		str		The role to retrieve
		 *			$orderby	str		The field to order by
		 *			$order		str		The order
		 * @return:	$clients	arr		Array of clients
		 *
		 */
		public function get_clients( $role='', $orderby='', $order='' )	{
			/* -- Define the defaults -- */
			$role = !empty( $role ) ? $role : 'client';
			$orderby = !empty( $orderby ) ? $orderby : 'display_name';
			$order = !empty( $order ) ? $order : 'ASC';
			
			$args = array(
						'role' => $role,
						'orderby' => $orderby,
						'order' => $order
						);
			$clients = get_users( $args );
			
			return $clients;	
		} // get_clients
		
		/**
		 * Determine if the given client belongs to the currently logged in user
		 * If no event is specified, true will be returned if the DJ has (or will) performed
		 * for the client at any time
		 *
		 * @param:
		 *			$client		int		The user_ID of the client
		 *			$event		int		(Optional) The event ID to query
		 *			
		 * @return:				bool	True if client belongs to logged in user, otherwise false
		 *
		 */
		public function is_my_client( $client='', $event='' )	{
			global $mdjm, $current_user;
			
			if( empty( $client ) )	{
				if( MDJM_DEBUG == true )
					$mdjm->debug_logger( 'ERROR: No client was provided. ' . __FUNCTION__, true );
				
				return false;	
			}
			
			$args = array(
					'post_type' 		=> MDJM_EVENT_POSTS,
					'post_status'	  => 'any',
					'posts_per_page'   => 1,
					'meta_key'		 => '_mdjm_event_date',
					'meta_query'	   => array(
											'relation'   => 'AND',
											array( 
											'key'		=> '_mdjm_event_dj',
											'value'  	  => $current_user->ID,
											'compare'	=> '=',
											),
											array(
											'key'		=> '_mdjm_event_client',
											'value'  	  => $client,
											'compare'	=> '=',
											),
										),
					'orderby'		  => 'meta_value_num',
					'order' 			=> 'ASC',
					);
					
			if( empty( $event ) )
				return ( count( get_posts( $args ) ) == 1 ? true : false );
				
			$the_event = get_post( $event );
			if( empty( $the_event ) )
				return false;
				
			return ( get_post_meta( $the_event->ID, '_mdjm_event_dj', true ) == $current_user->ID ) ? true : false;
			
		} // is_my_client
		
		/**
		 * Retrieve list of client events
		 *
		 * @param:
		 *			$client		int			The user ID of the client
		 *			$orderby	str			The field to order by
		 *			$order		str			The order
		 *			$status		arr			array of status' to check. Default to any
		 * @return:	$events		arr | bool	Array of client's events or false if none
		 *
		 */
		public function client_events( $client='', $orderby='', $order='', $status='' )	{
			global $mdjm;
			
			if( empty( $client ) )	{
				if( MDJM_DEBUG == true )
					$mdjm->debug_logger( 'ERROR: No client was provided. ' . __FUNCTION__, true );
					
				return false;
			}
			
			$status = !empty( $status ) ? $status : 'any';
			$orderby = !empty( $orderby ) ? $orderby : '_mdjm_event_date';
			$order = !empty( $order ) ? $order : 'DESC';
			
			$args = array(
						'post_type' 		=> MDJM_EVENT_POSTS,
						'post_status'	  => $status,
						'posts_per_page'   => -1,
						'meta_key'		 => $orderby,
						'meta_query'	   => array(
												'key'		=> '_mdjm_event_client',
												'value'  	  => $client,
												'compare'	=> '==',
												),
						'orderby'		  => 'meta_value_num',
						'order' 			=> $order,
						);
						
			$events = get_posts( $args );
			
			return $events;	
		} // client_events

/*
 * DJ Functions
 */
		/**
		 * Retrieve list of DJ events
		 *
		 * @param:
		 *			$dj			int			The user ID of the DJ. Default to current user. Arrays supported
		 *			$orderby	str			The field to order by
		 *			$order		str			The order
		 *			$status		arr			array of status' to check. Default to any
		 *			$date		str			The date to query (Y-m-d). Ignored if empty
		 * @return:	$events		arr | bool	Array of DJ's events or false if none
		 *
		 */
		public function dj_events( $dj='', $orderby='', $order='', $status='', $date='' )	{
			global $mdjm, $current_user;
						
			$dj = !empty( $dj ) ? $dj : $current_user->ID;
			$status = !empty( $status ) ? $status : 'any';
			$date = !empty( $date ) ? $date : false;
			$orderby = !empty( $orderby ) ? $orderby : '_mdjm_event_date';
			$order = !empty( $order ) ? $order : 'DESC';
			
			$num_order = array( '_mdjm_event_date', '_mdjm_event_dj', '_mdjm_event_client' );
			
			$args = array(
						'post_type' 		=> MDJM_EVENT_POSTS,
						'post_status'	  => $status,
						'posts_per_page'   => -1,
						'meta_key'		 => $orderby,
						'orderby'		  => ( in_array( $orderby, $num_order ) ? 'meta_value_num' : 'meta_value' ),
						'order' 			=> $order,
						);
			if( empty( $date ) )	{
				$args['meta_query'] = array(
											'key'		=> '_mdjm_event_dj',
											'value'  	  => $dj,
											'compare'	=> '==',
											);
			}
			else	{
				$args['meta_query'] = array(
										'relation' => 'AND',
										array(
											'key'		=> '_mdjm_event_dj',
											'value'  	  => $dj,
											'compare'	=> ( is_array( $dj ) ? 'IN' : '=' ),
											),
										array(
											'key'		=> '_mdjm_event_date',
											'value'  	  => $date,
											'compare'	=> '=',
											'type'	   => 'date',
											),
										);
			}
						
			$events = get_posts( $args );
			
			return ( $events ) ? $events : false;	
		} // dj_events
		
		/**
		 * Check if the DJ is working today
		 *
		 * @param:
		 *			$dj			int			The user ID of the DJ. Default to current user
		 *			
		 * @return:				arr | bool	Object array if working, otherwise false
		 *
		 */
		public function working_today( $dj='' )	{
			global $mdjm, $current_user;
						
			$dj = !empty( $dj ) ?  $dj : $current_user->ID;
			
			$args = array(
						'post_type' 		=> MDJM_EVENT_POSTS,
						'post_status'	  	=> 'mdjm-approved',
						'posts_per_page'   	=> -1,
						'meta_key'		 	=> '_mdjm_event_date',
						'meta_query'	   	=> array(
												'relation'	=> 'AND',
												array( 
													'key'		=> '_mdjm_event_date',
													'value'  	=> date( 'Y-m-d' ),
													'compare'	=> '==',
													),
												array( 
													'key'		=> '_mdjm_event_dj',
													'value'  	=> $dj,
													'compare'	=> '==',
													),
												),
						'orderby'		  	=> 'meta_value',
						'order' 			=> 'DESC',
						);
						
			$events = get_posts( $args );
			
			if( count( $events ) > 0 )
				return $events;
			
			return false;	
		} // working_today

/*
 * Event functions
 */	
		/*
		 * mdjm_event_by
		 * Retrieve event details by given field
		 * 
		 * @param: 
		 *			$field	str					The field to query
		 *			$data	str|int|arr|bool	The data to match within the given field
		 *
		 * @return: $event_details => arr
		 */
		public function mdjm_event_by( $field, $data )	{
			global $wpdb;
					
			if( empty( $field ) || empty( $data ) )
				return;
			
			switch( $field )	{
				case 'ID': // A general event post ID lookup
					return get_post( $data );
				break;
				case 'playlist': // Lookup by playlist guest access string
					$event_details = get_posts( array(
							'posts_per_page'	=> 1,
							'post_type'		 => MDJM_EVENT_POSTS,
							'post_status'	   => array( 'mdjm-approved', 'mdjm-contract', 'mdjm-enquiry', 'mdjm-unattended' ),
							'meta_key'		  => '_mdjm_event_date',
							'meta_query'		=> array(
													'key' 	  => '_mdjm_event_playlist_access',
													'value'	=> $data,
													'compare'  => '=',
														),
							) );
					return get_post( $event_details[0]->ID );
				break;
			}
						
			return false;
		} // mdjm_event_by
		
		/* List employee bookings by given date
		 * Only checks for the "active" statuses
		 *
		 * @param		int		$dj		User ID of DJ to check, default to all
		 *				str		$date	The date to check (Y-m-d)
		 * @return
		 */
		public function employee_bookings( $dj='', $date='' )	{
			$date = !empty( $date ) ? $date : date( 'Y-m-d' );
			$dj = !empty( $dj ) ? $dj : mdjm_get_djs();
			
			if( is_array( $dj ) )	{
				foreach( $dj as $employee )	{
					$user[] = $employee->ID;
				}
			}
			else	{
				$user[] = $dj;
			}
			
			$status = array( 'mdjm-enquiry', 'mdjm-contract', 'mdjm-approved' );
			
			$events = $this->dj_events( $user, '_mdjm_event_dj', 'ASC', $status, $date );
			
			return $events;
		}
		
		/**
		 * Get specified users next event
		 *
		 * @param:
		 *			$user		str		The user to query for. Default to current user
		 *			$user_type	str		Client of DJ (lowercase). Default to client
		 *			
		 * @return:	$next_event	arr		Object array of next event
		 *
		 */
		public function next_event( $user='', $user_type='' )	{
			global $current_user;
			
			/* -- Define the defaults -- */
			$user = !empty( $user ) ? $user : $current_user->ID;
			
			$user_type = !empty( $user_type ) ? $user_type : 'client';
			
			$args = array(
						'post_type' 		=> MDJM_EVENT_POSTS,
						'post_status'	  => array( 'mdjm-approved', 'mdjm-contract', 'mdjm-enquiry', 'mdjm-unattended' ),
						'posts_per_page'   => 1,
						'meta_key'		 => '_mdjm_event_date',
						'meta_query'	   => array(
												'relation'	=> 'AND',
												array( 
												'key'		=> '_mdjm_event_' . $user_type,
												'value'  	  => $user,
												'compare'	=> '=',
												),
												array(
												'key'		=> '_mdjm_event_date',
												'value'  	  => date( 'Y-m-d' ),
												'compare'	=> '>=',
												'type'	   => 'date',
												),
											),
						'orderby'		  => 'meta_value',
						'order' 			=> 'ASC',
						);
						
			$next_event = get_posts( $args );
				
			return $next_event;	
		} // next_event
		
		/**
		 * Get specified users active events in date order
		 *
		 * @param:
		 *			$user		str		The user to query for. Default to current user
		 *			$user_type	str		Client of DJ (lowercase). Default to client
		 *			
		 * @return:	$next_event	arr		Array of active events
		 *
		 */
		public function active_events( $user='', $user_type='' )	{
			global $current_user;
			
			/* -- Define the defaults -- */
			$user = !empty( $user ) ? $user : $current_user->ID;
			
			$user_type = !empty( $user_type ) ? $user_type : 'client';
			
			$args = array(
						'post_type' 		=> MDJM_EVENT_POSTS,
						'post_status'	  => array( 'mdjm-approved', 'mdjm-contract', 'mdjm-enquiry', 'mdjm-unattended' ),
						'posts_per_page'   => -1,
						'meta_key'		 => '_mdjm_event_date',
						'meta_query'	   => array(
												'relation'	=> 'AND',
												array( 
												'key'		=> '_mdjm_event_' . $user_type,
												'value'  	  => $user,
												'compare'	=> '=',
												),
												array(
												'key'		=> '_mdjm_event_date',
												'value'  	  => date( 'Y-m-d' ),
												'compare'	=> '>=',
												'type'	   => 'date',
												),
											),
						'orderby'		  => 'meta_value_num',
						'order' 			=> 'ASC',
						);
						
			$active_events = get_posts( $args );
				
			return $active_events;	
		} // active_events
		
		/*
		 * Retrieve all event status counts for DJ's & Employees to be display
		 * within the dashboarc
		 *
		 * @param	str		$type		'dj', 'client' or empty for 'employer'
		 *			int		$user_id	ID of the user to retrieve for
		 * @return	$event_details	arr		An array of the requested information
		 */
		public function count_events_by_status( $type='', $user_id='' )	{			
			if( !class_exists( 'MDJM_Dashboard' ) )
				require_once( MDJM_PLUGIN_DIR . '/admin/includes/class/class-mdjm-dashboard.php' );
				
			$mdjm_dash = new MDJM_Dashboard();
			
			$type = !empty( $type ) ? $type : '';
			
			$user_id = !empty( $user_id ) ? $user_id : get_current_user_id();
			
			/* -- Build the array -- */
			$event_count['enquiry_month'] = count( $mdjm_dash->all_events_by_status( 
																				'mdjm-enquiry',
																				'month',
																				$type,
																				$user_id ) );
			$event_count['enquiry_year'] = count( $mdjm_dash->all_events_by_status( 
																				'mdjm-enquiry',
																				'year',
																				$type,
																				$user_id ) );
			$event_count['unattended_month'] = count( $mdjm_dash->all_events_by_status( 
																				'mdjm-unattended',
																				'month',
																				$type,
																				$user_id ) );
			$event_count['unattended_year'] = count( $mdjm_dash->all_events_by_status( 
																				'mdjm-unattended',
																				'year',
																				$type,
																				$user_id ) );
			$event_count['active_month'] = count( $mdjm_dash->all_events_by_status( array(
																				'mdjm-enquiry',
																				'mdjm-unattended',
																				'mdjm-contract',
																				'mdjm-approved',
																				 ),
																				 'month',
																				 $type,
																				 $user_id ) );
			$event_count['active_year'] = count( $mdjm_dash->all_events_by_status( array(
																				'mdjm-enquiry',
																				'mdjm-unattended',
																				'mdjm-contract',
																				'mdjm-approved',
																				 ),
																				 'year',
																				 $type,
																				 $user_id ) );
			$event_count['completed_month'] = count( $mdjm_dash->all_events_by_status( 
																				'mdjm-completed',
																				'month',
																				$type,
																				$user_id ) );
			$event_count['completed_year'] = count( $mdjm_dash->all_events_by_status( 
																				'mdjm-completed',
																				'year',
																				$type,
																				$user_id ) );
			$event_count['lost_month'] = count( $mdjm_dash->all_events_by_status( 
																				'mdjm-lost',
																				'month',
																				$type,
																				$user_id ) );
			$event_count['lost_year'] = count( $mdjm_dash->all_events_by_status( 
																				'mdjm-lost',
																				'year',
																				$type,
																				$user_id ) );
			return $event_count;
		} // count_events_by_status
		
		/**
		 * Get specified users completed events in date order
		 *
		 * @param:
		 *			$user		str		The user to query for. Default to current user
		 *			$user_type	str		Client of DJ (lowercase). Default to client
		 *			
		 * @return:	$completed_events	arr		Array of historic events
		 *
		 */
		public function completed_events( $user='', $user_type='' )	{
			global $current_user;
			
			/* -- Define the defaults -- */
			$user = !empty( $user ) ? $user : $current_user->ID;
			
			$user_type = !empty( $user_type ) ? $user_type : 'client';
			
			$args = array(
						'post_type' 		=> MDJM_EVENT_POSTS,
						'post_status'	  => 'mdjm-completed',
						'posts_per_page'   => -1,
						'meta_key'		 => '_mdjm_event_date',
						'meta_query'	   => array(
												'relation'	=> 'AND',
												array( 
												'key'		=> '_mdjm_event_' . $user_type,
												'value'  	  => $user,
												'compare'	=> '=',
												),
												array(
												'key'		=> '_mdjm_event_date',
												'value'  	  => date( 'Y-m-d' ),
												'compare'	=> '<=',
												'type'	   => 'date',
												),
											),
						'orderby'		  => 'meta_value_num',
						'order' 			=> 'ASC',
						);
						
			$completed_events = get_posts( $args );
				
			return $completed_events;	
		} // completed_events
		
		/*
		 * Get the current events key details and place them into an array
		 *
		 * @param 	int		$post_id	The event's ID
		 * @return	obj		$eventinfo	The event meta information
		 */
		public function event_detail( $post_id )	{
			global $mdjm, $mdjm_posts;
			
			if( empty( $post_id ) || !$mdjm_posts->post_exists( $post_id ) )
				return;
				
			if( !class_exists( 'MDJM_Transactions' ) )
				require_once( MDJM_PLUGIN_DIR . '/admin/includes/class/class-mdjm-transactions.php' );
			
			$mdjm_transactions = new MDJM_Transactions();
			
			$name = get_post_meta( $post_id, '_mdjm_event_name', true );
			$date = get_post_meta( $post_id, '_mdjm_event_date', true );
			$client = get_post_meta( $post_id, '_mdjm_event_client', true );
			$dj = get_post_meta( $post_id, '_mdjm_event_dj', true );
			$cost = get_post_meta( $post_id, '_mdjm_event_cost', true );
			$deposit = get_post_meta( $post_id, '_mdjm_event_deposit', true );
			$deposit_status = get_post_meta( $post_id, '_mdjm_event_deposit_status', true );
			$paid = $mdjm_transactions->get_transactions( $post_id, $direction='in' );
			$balance_status = get_post_meta( $post_id, '_mdjm_event_balance_status', true );
			$start = get_post_meta( $post_id, '_mdjm_event_start', true );
			$finish = get_post_meta( $post_id, '_mdjm_event_finish', true );
			$setup_date = get_post_meta( $post_id, '_mdjm_event_djsetup', true );
			$setup_time = get_post_meta( $post_id, '_mdjm_event_djsetup_time', true );
			$contract = get_post_meta( $post_id, '_mdjm_event_contract', true );
			$contract_date = get_post_meta( $post_id, '_mdjm_event_contract_approved', true );
			$signed_contract = get_post_meta( $post_id, '_mdjm_signed_contract', true );
			$notes = get_post_meta( $post_id, '_mdjm_event_notes', true );
			$admin_notes = get_post_meta( $post_id, '_mdjm_event_admin_notes', true );
			$package = get_post_meta( $post_id, '_mdjm_event_package', true );
			$addons = get_post_meta( $post_id, '_mdjm_event_addons', true );
			$online_quote = get_post_meta( $post_id, '_mdjm_online_quote', true );
			$guest_playlist = get_post_meta( $post_id, '_mdjm_event_playlist_access', true );
			
			$eventinfo = array(
							// Event name
							'name'				=> ( !empty( $name ) ? $name : '' ),
							// Event date
							'date'				=> ( !empty( $date ) && is_int( strtotime( $date ) ) ? 
								strtotime( $date ) : __( 'Not Specified', 'mobile-dj-manager' ) ),
							// Client details as object array
							'client'			  => ( !empty( $client ) ? get_userdata( $client ) : '' ),
							// DJ details as object array
							'dj'				  => ( !empty( $dj ) ? get_userdata( $dj ) : __( 'Not Assigned' ) ),
							// Event Start
							'start'			   => ( !empty( $start ) ? date( MDJM_TIME_FORMAT, strtotime( $start ) ) : __( 'Not Specified' ) ),
							// Event Finish
							'finish'			  => ( !empty( $finish ) ? date( MDJM_TIME_FORMAT, strtotime( $finish ) ) : __( 'Not Specified' ) ),
							// DJ Setup date
							'setup_date'		  => ( !empty( $setup_date ) ? strtotime( $setup_date ) : __( 'Not Specified' ) ),
							// DJ Setup time
							'setup_time'		  => ( !empty( $setup_time ) ? date( MDJM_TIME_FORMAT, strtotime( $setup_time ) ) : __( 'Not Specified' ) ),
							// Total cost
							'cost'				=> ( !empty( $cost ) ? display_price( $cost ) : __( 'Not Specified' ) ),
							// Deposit fee
							'deposit'			 => ( !empty( $deposit ) ? display_price( $deposit ) : '0.00' ),
							// Balance remaining
							'balance'			 => ( !empty( $paid ) && $paid != '0.00' && !empty( $cost ) ? 
								display_price( ( $cost - $paid ) ) : display_price( $cost ) ),
								
							// Deposit status
							'deposit_status'	  => ( !empty( $deposit_status ) ? $deposit_status : __( 'Due' ) ),
							// Balanace status
							'balance_status'	  => ( !empty( $balance_status ) ? $balance_status : __( 'Due' ) ),
							// Event type
							'type'				=> $this->get_event_type( $post_id ),
							// Online Quote
							'online_quote'		=> ( MDJM_ONLINE_QUOTES == true && !empty( $online_quote ) ? $online_quote : '' ),
							// Contract template
							'contract'			=> ( !empty( $contract ) ? $contract : '' ),
							// Date contract signed
							'contract_date'	   => ( !empty( $contract_date ) ? date( MDJM_SHORTDATE_FORMAT, strtotime( $contract_date ) ) : 
								date( MDJM_SHORTDATE_FORMAT ) ),
								
							// Signed contract post ID
							'signed_contract'	 => ( !empty( $signed_contract ) ? $signed_contract : '' ),
							// Event notes
							'notes'		   	   => ( !empty( $notes ) ? $notes : '' ),
							// Admin notes
							'admin_notes'		 => ( !empty( $admin_notes ) ? $admin_notes : '' ),
							// Event package
							'package'			 => ( !empty( $package ) ? $package : '' ),
							// Event addons as array
							'addons'			  => ( !empty( $addons ) ? implode( "\n", $addons ) : '' ),
							// Guest playlist URL
							'guest_playlist'	  => ( !empty( $guest_playlist ) ? 
								$mdjm->get_link( MDJM_PLAYLIST_PAGE ) . 'mdjmeventid=' . $guest_playlist : '' ),
							);
			
			return $eventinfo;
		} // event_detail
		
		/*
		 * mdjm_count_event_status
		 * Retrieve the number of events by given status
		 * 
		 * @param: $event_status
		 * @return: $status_count
		 */
		public function mdjm_count_event_status( $event_status )	{
			global $wpdb;
			
			if( empty( $event_status ) )
				return;
			
			$status_count = wp_count_posts( MDJM_EVENT_POSTS )->$event_status;
			
			return $status_count;
		} // mdjm_count_event_status
		
		/*
		 * Get the event type for the given event
		 *
		 * @param	int		$id		The event ID. Only required of global $post not set
		 *
		 */
		public function get_event_type( $id='' )	{
			global $post, $mdjm;
			
			if( empty( $post ) && empty( $id ) )	{
				if( MDJM_DEBUG == true )
					$mdjm->debug_logger( 'ERROR: No global $post variable is set and no $id was parsed in ' . __METHOD__, true );
			}
			
			$event_id = !empty( $id ) ? $id : $post->ID;
			
			$types = wp_get_object_terms( $event_id, 'event-types' );
			
			if( !is_wp_error( $types ) && !empty( $types ) )
				return $types[0]->name;
				
			else	{
				if( MDJM_DEBUG == true )
					$mdjm->debug_logger( 'ERROR: Could not find the term.', true );
					
				return __( 'No Event Type Set' );	
			}
			
		} // get_event_type
				
		/*
		 * mdjm_assign_event_type
		 * Assign the selected event type (taxonomy) to the the event (post)
		 *
		 * @params:		int		$event_type
		 *				
		 * @return: 	bool	true : false
		 */
		public function mdjm_assign_event_type( $event_type )	{
			global $post;
			if( empty( $post->ID ) || empty( $event_type ) )
				return false;
			 
			$set_event_type = wp_set_post_terms( $post->ID, $event_type, 'event-types' );
			if ( is_wp_error( $set_event_type ) )
				return false;
			else 
				return true;
		} // mdjm_assign_event_type
		
		/*
		 * get_event_types
		 * Retrieve all possible event types (taxonomy)
		 *
		 * @params:		$hide_empty		bool	false (default) to show all, true only those in use
		 *				
		 * @return: 	$event_types	arr		Event type objects
		 */
		public function get_event_types( $hide_empty=false )	{
			$hide_empty = $hide_empty == false ? 0 : 1;
			$event_types = get_categories( array(
											'type'		=> MDJM_EVENT_POSTS,
											'taxonomy'	=> 'event-types',
											'order_by'	=> 'name',
											'order'	   => 'ASC',
											'hide_empty'  => $hide_empty,
											) );
			return $event_types;
		} // get_event_types
		
		/**
		 * Actions taken once an event is set to the Unattended Enquiry Status
		 *
		 * @param    int	post_id	The event (post) ID
		 *			 obj	post
		 *
		 * @return   
		 * @since    1.1.3
		 */
		public function status_unattended( $post_id, $post, $event_data, $field_updates )	{
			global $mdjm, $mdjm_settings;
						
			if( MDJM_DEBUG == true )
				$mdjm->debug_logger( '*** Starting Unattended Enquiry procedures ***' . "\r\n", true );
						
		/* -- Permission Check -- */
			if( !current_user_can( 'administrator' ) || dj_can( 'dj_add_event' ) )
				return $post_id;	
			
										
		/* -- Update Journal with event creation -- */
			if( MDJM_JOURNAL == true )	{
				if( MDJM_DEBUG == true )
					$mdjm->debug_logger( '	-- Adding journal entry' );
					
				$this->add_journal( array(
							'user' 			=> get_current_user_id(),
							'comment_content' => 'Event created via Admin <br /><br />' .
												 ( isset( $field_updates ) ? implode( '<br />', $field_updates ) : '' ) . '<br />(' . time() . ')',
							'comment_type' 	=> 'mdjm-journal',
							),
							array(
								'type' 		  => 'create-event',
								'visibility'	=> '1',
							) );
			}
			else	{
				if( MDJM_DEBUG == true )
					$mdjm->debug_logger( '	-- Journalling is disabled' );	
			}
			
			if( MDJM_DEBUG == true )
				$mdjm->debug_logger( '*** Completed Unattended Enquiry procedures ***' . "\r\n", true );
						
		} // status_unattended
		
		/**
		 * Actions taken once an event is set to the Enquiry Status
		 *
		 * @param    int	post_id	The event (post) ID
		 *			 obj	post
		 *
		 * @return   
		 * @since    1.1.3
		 */
		public function status_enquiry( $post_id, $post, $event_data, $field_updates )	{
			global $mdjm, $mdjm_settings;
						
			if( MDJM_DEBUG == true )
				$mdjm->debug_logger( '*** Starting New Enquiry procedures ***' . "\r\n", true );
						
		/* -- Permission Check -- */
			if( !current_user_can( 'administrator' ) || dj_can( 'dj_add_event' ) )
				return $post_id;	
										
		/* -- Update Journal with event creation -- */
			if( MDJM_JOURNAL == true )	{
				if( MDJM_DEBUG == true )
					$mdjm->debug_logger( '	-- Adding journal entry' );
					
				$this->add_journal( array(
							'user' 			=> get_current_user_id(),
							'comment_content' => 'Event created via Admin <br /><br />' .
												 ( isset( $field_updates ) ? implode( '<br />', $field_updates ) : '' ) . '<br />(' . time() . ')',
							'comment_type' 	=> 'mdjm-journal',
							),
							array(
								'type' 		  => 'create-event',
								'visibility'	=> '1',
							) );
			}
			else	{
				if( MDJM_DEBUG == true )
					$mdjm->debug_logger( '	-- Journalling is disabled' );	
			}
			
			/* -- Generate online quote if configured -- */
			if( MDJM_ONLINE_QUOTES == true && !empty( $_POST['_mdjm_online_quote'] ) )	{
				if( MDJM_DEBUG == true )
					$GLOBALS['mdjm_debug']->log_it( '	-- Generating event quote for event ' . $post_id );
				
				// Determine if a post already exists for the event quote
				$quote_post = $this->retrieve_quote( $post_id );
				
				// Retrieve the post content. If one exists we'll use that, otherwise get the template
				$quote_template = get_post( $_POST['_mdjm_online_quote'] );
				
				// Make sure we have the template and create or update the quote post
				if( is_object( $quote_template ) )	{					
					/* -- Retrieve the quote content -- */
					$content = $quote_template->post_content;
					$content = apply_filters( 'the_content', $content );
					$content = str_replace( ']]>', ']]&gt;', $content );
					/* -- Shortcode replacements -- */
					$content = $mdjm->filter_content(
										$event_data['_mdjm_event_client'],
										$post_id,
										$content
										);
					
					// If no quote post exists for this event, we'll be creating one
					if( empty( $quote_post ) )	{
						if( MDJM_DEBUG == true )
							$GLOBALS['mdjm_debug']->log_it( '	-- Creating new event quote' );
							
						$post_args['post_title'] = 'Quote ' . MDJM_EVENT_PREFIX . $post_id;
						$post_args['post_content'] = $content;
						$post_args['post_type'] = MDJM_QUOTE_POSTS;
						$post_args['post_status'] = 'mdjm-quote-generated';
						$post_args['post_author'] = ( !empty( $event_data['_mdjm_event_client'] ) ? $event_data['_mdjm_event_client'] : get_current_user_id() );
						$post_args['post_parent'] = $post_id;
						
						// Create the quotation post
						$quote_post_id = wp_insert_post( $post_args );
						
						if( !empty( $quote_post_id ) )	{
							if( MDJM_DEBUG == true )
								$GLOBALS['mdjm_debug']->log_it( '	-- Quotation generated ' . $quote_post_id );							
						}
					}
					else	{ // We have an existing quote so update it
						if( MDJM_DEBUG == true )
							$GLOBALS['mdjm_debug']->log_it( '	-- Updating existing event quote' );
						
						wp_update_post( array( 
											'ID' 			  => $quote_post,
											'post_content'	=> $content,
											'post_status'	 => 'mdjm-quote-generated',
											'post_date'	   => current_time( 'mysql' ),
											'edit_date'	   => true ) );
						
						/* -- Reset the meta keys for date viewed and view count -- */
						if( MDJM_DEBUG == true )
							$GLOBALS['mdjm_debug']->log_it( '	-- Removing existing meta keys' );
												
						delete_post_meta( $quote_post, '_mdjm_quote_viewed_date' );
						delete_post_meta( $quote_post, '_mdjm_quote_viewed_count' );
					}
				} // if( is_object( $quote_template ) )				
			}
			
			/* -- Send emails as required -- */
			if( isset( $_POST['mdjm_email_enquiry'] ) )	{
				if( MDJM_DEBUG == true )
					$mdjm->debug_logger( '	-- Generating Email' );
					
				$quote = $mdjm->send_email( array( 
									'content'	=> !empty( $_POST['mdjm_email_template'] ) ? $_POST['mdjm_email_template'] : $mdjm_settings['templates']['enquiry'],
									'to'		 => $event_data['_mdjm_event_client'],
									'from'	   => $mdjm_settings['templates']['enquiry_from'] == 'dj' ? $_POST['_mdjm_event_dj'] : 0,
									'journal'	=> 'email-client',
									'event_id'   => $post_id,
									'html'	   => true,
									'cc_dj'	  => isset( $mdjm_settings['email']['bcc_dj_to_client'] ) ? true : false,
									'cc_admin'   => isset( $mdjm_settings['email']['bcc_admin_to_client'] ) ? true : false,
									'source'	 => 'Event Enquiry',
								) );
				if( $quote )	{
					if( MDJM_DEBUG == true )
						 $mdjm->debug_logger( '	-- Client quote sent. ' . $quote . ' ID ' );
				}
				else	{
					if( MDJM_DEBUG == true )
						 $mdjm->debug_logger( '	ERROR: Client quote was not sent' );
				}
			}
			else	{
				if( MDJM_DEBUG == true )
					$mdjm->debug_logger( '	-- No email requested' );		
			}
			
			if( MDJM_DEBUG == true )
				$mdjm->debug_logger( '*** Completed New Enquiry procedures ***' . "\r\n", true );
						
		} // status_enquiry
		
		/**
		 * Actions taken once an event is set to the Awaiting Contract Status
		 *
		 * @param    int	post_id	The event (post) ID
		 *			 obj	post
		 *
		 * @return   
		 * @since    1.1.3
		 */
		public function status_contract( $post_id, $post, $event_data, $field_updates )	{
			global $mdjm_settings, $mdjm, $mdjm_posts;
			
			if( $_POST['original_post_status'] == 'mdjm-contract' )
				return;
			
			if( $post->post_type != MDJM_EVENT_POSTS )
				return;
			
			if( empty( $post_id ) || empty( $post ) )
				return;
			
			$event_stati = get_event_stati();
			
			if( MDJM_DEBUG == true )
				$mdjm->debug_logger( 'Event status transition to ' . $event_stati[$_POST['mdjm_event_status']] . ' starting', $stampit=true );
				
			/* -- Email the contract to the client is required -- */
			$contact_client = isset( $mdjm_settings['templates']['contract_to_client'] ) ? true : false;
			$contract_email = isset( $mdjm_settings['templates']['contract'] ) ? $mdjm_settings['templates']['contract'] : false;
			
			if( !$mdjm_posts->post_exists( $contract_email ) )	{
				if( MDJM_DEBUG == true )
					$mdjm->debug_logger( 'ERROR: No email template for the contract has been found ' . __FUNCTION__, $stampit=true );
				wp_die( 'ERROR: Either no email template is defined or an error has occured. Check your Settings.' );
			}
			
			if( $contact_client == true )	{
				if( MDJM_DEBUG == true )
					$mdjm->debug_logger( 'Configured to email client with template ID ' . $contract_email );
				
				if( MDJM_DEBUG == true )
					$mdjm->debug_logger( 'Generating email...' );	
					$contract_email = $mdjm->send_email( array( 
											'content'	=> $contract_email,
											'to'		 => get_post_meta( $post_id, '_mdjm_event_client', true ),
											'from'	   => $mdjm_settings['templates']['contract_from'] == 'dj' ? get_post_meta( $post_id, '_mdjm_event_dj', true ) : 0,
											'journal'	=> 'email-client',
											'event_id'   => $post_id,
											'html'	   => true,
											'cc_dj'	  => isset( $mdjm_settings['email']['bcc_dj_to_client'] ) ? true : false,
											'cc_admin'   => isset( $mdjm_settings['email']['bcc_admin_to_client'] ) ? true : false,
											'source'	 => 'Event Status to Awaiting Contract',
										) );
					if( $contract_email )	{
						if( MDJM_DEBUG == true )
							 $mdjm->debug_logger( '	-- Contract email sent to client ' );
					}
					else	{
						if( MDJM_DEBUG == true )
							 $mdjm->debug_logger( '	ERROR: Contract email was not sent' );	
					}	
			}
			else	{
				if( MDJM_DEBUG == true )
					$mdjm->debug_logger( 'Not configured to email client' );	
			}
			
		} // status_contract
		
		/**
		 * Actions taken once an event is set to the Approved Status
		 *
		 * @param    int	post_id	The event (post) ID
		 *			 obj	post
		 *
		 * @return   
		 * @since    1.1.3
		 */
		public function status_approved( $post_id, $post, $event_data, $field_updates )	{
			global $mdjm_settings, $mdjm, $mdjm_posts;
			
			if( $_POST['original_post_status'] == 'mdjm-approved' )
				return;
			
			if( $post->post_type != MDJM_EVENT_POSTS )
				return;
			
			if( empty( $post_id ) || empty( $post ) )
				return;
			
			$event_stati = get_event_stati();
			
			if( MDJM_DEBUG == true )
				$mdjm->debug_logger( 'Event status transition to ' . $event_stati[$_POST['mdjm_event_status']] . ' starting', $stampit=true );
				
			/* -- Email the confirmation to the client & DJ if required -- */
			$contact_client = isset( $mdjm_settings['templates']['booking_conf_to_client'] ) ? true : false;
			$contact_dj = isset( $mdjm_settings['templates']['booking_conf_to_dj'] ) ? true : false;
			$client_email = isset( $mdjm_settings['templates']['booking_conf_client'] ) ? $mdjm_settings['main']['booking_conf_client'] : false;
			$dj_email = isset( $mdjm_settings['templates']['booking_conf_dj'] ) ? $mdjm_settings['templates']['booking_conf_dj'] : false;
			
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
										'to'		 => get_post_meta( $post_id, '_mdjm_event_client', true ),
										'from'	   => $mdjm_settings['templates']['booking_conf_from'] == 'dj' ? get_post_meta( $post_id, '_mdjm_event_dj', true ) : 0,
										'journal'	=> 'email-client',
										'event_id'   => $post_id,
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
											'to'		 => get_post_meta( $post_id, '_mdjm_event_dj', true ),
											'from'	   => 0,
											'journal'	=> 'email-dj',
											'event_id'   => $post_id,
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
		} // status_approved
		
		/*
		 * Set given event as rejected
		 *
		 * @param	int		$event_id	Required: The event post ID
		 *			int		$user		Optional: The user rejecting the event. Default current user
		 *			str		$reason		Optional: The reason for rejection. Default Unavailability
		 *			
		 * @return	bool	$result		true if successfully processed, false if not
		 */
		public function reject_event( $event_id, $user='', $reason='' )	{
			global $mdjm, $mdjm_posts, $current_user;
			
			/* -- Validation -- */
			if( empty( $event_id ) )	{
				if( MDJM_DEBUG == true )
					$mdjm->debug_logger( 'ERROR: Cannot reject an event without an Event ID in ' . __METHOD__, true );
					
				return false;	
			}
			
			$event = get_post( $event_id );
			
			$user = !empty( $user ) ? $user : $current_user->ID;
			$reason = !empty( $reason ) ? stripslashes( $reason ) : __( 'Unavailable' );
			
			$meta_update = array(
				'_mdjm_event_last_updated_by'		=> $user,
				'_mdjm_event_rejected'				=> date( 'Y-m-d H:i:s' ),
				'_mdjm_event_rejected_by'			=> $user,
				);

			/* -- Prevent loops whilst updating -- */
			remove_action( 'save_post', array( $mdjm_posts, 'save_custom_post' ), 10, 2 );
			
			/* -- Initiate actions for status change -- */
			wp_transition_post_status( 'mdjm-rejected', $event->post_status, $event );
			
			/* -- Update the post status -- */
			if( wp_update_post( array( 'ID' => $event_id, 'post_status' => 'mdjm-rejected' ) ) )
				$result = true;
				
			else
				$result = false;
			
			/* -- Update the post meta -- */
			foreach( $meta_update as $event_meta_key => $event_meta_value )	{
				update_post_meta( $event->ID, $event_meta_key, $event_meta_value );
			}
			
			/* -- Update Journal with event updates -- */
			if( MDJM_JOURNAL == true )	{
				if( MDJM_DEBUG == true )
					$mdjm->debug_logger( '	-- Adding journal entry' );
					
				$this->add_journal( array(
							'user'				=> $user,
							'event'				=> $event->ID,
							'comment_content'	=> 'Event rejected',
							'comment_type'		=> 'mdjm-journal',
							),
							array(
								'type'			=> 'reject-event',
								'visibility'	=> '0',
							) );
			}
			else	{
				if( MDJM_DEBUG == true )
					$mdjm->debug_logger( '	-- Journalling is disabled' );	
			}
			
			add_action( 'save_post', array( $mdjm_posts, 'save_custom_post' ), 10, 2 );
			
			return $result;
		} // reject_event

		/**
		 * Check if the current post belongs to the logged in user
		 * 
		 * 
		 *
		 * @param    int	event_id	The event (post) to query
		 *
		 * @return   bool	true if the event belongs to the user, otherwise false
		 * @since    1.1.3
		 */
		public function is_my_event( $event_id )	{
			if( empty( $event_id ) )
				return false;
			
			$type = 'client';
			
			if( current_user_can( 'administrator' ) || is_dj() )
				$type = 'dj';
				
			if( get_current_user_id() == get_post_meta( $event_id, '_mdjm_event_' . $type, true ) )
				return true;
				
			return false;
		} // is_my_event
		
		/*
		 * Retrieve event quotation post
		 *
		 * @param	int			event_id	Required: The ID of the event for which to search
		 * 			str|arr		$status		Optional: The status of the quote to retrieve. Default to 'any'
		 * @return	int|bool				The quote post ID or if none exists, false
		 */
		function retrieve_quote( $event_id, $status='any' )	{
			$quote = get_posts( array( 
										'numberposts'		=> 1,
										'post_parent'		=> $event_id,
										'post_status'		=> $status,
										'post_type'		  => MDJM_QUOTE_POSTS ) );
										
			if( empty( $quote ) )
				return false;
				
			if( MDJM_DEBUG == true )
				$GLOBALS['mdjm_debug']->log_it( 'Returning quote ID ' . $quote[0]->ID );
			
			return ( !empty( $quote ) ? $quote[0]->ID : false );			
		} // retrieve_quote
		
		/*
		 * Check the status of the playlist
		 *
		 * @param	str		$date	The date (timestamp) of the event
		 * @return	bool			true if open, otherwise false
		 */
		public function playlist_status( $date )	{				
			/* Playlist never closes */
			if( MDJM_PLAYLIST_CLOSE == 0 )
				return true;
				
			return ( time() > ( $date - ( MDJM_PLAYLIST_CLOSE * DAY_IN_SECONDS ) ) ?
				false : true );
				
		} // playlist_status
		
		/*
		 * Generate an event playlist reference to be used by guests
		 *
		 * @param
		 * @return		str		$pl_ref		The unique playlist reference
		 */
		public function playlist_ref()	{
			$pl_ref = substr( str_shuffle( "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789" ), 0, 9 );
			
			return $pl_ref;
		} // playlist_ref
		
		/*
		 * Retrieve the playlist for the current event grouped by category
		 *
		 * @param	int			$event		Required: The event ID
		 * @return	arr|bool	$entries	false if no records or array of songs by category
		 */
		function get_playlist_by_cat( $event )	{
			global $wpdb;
						
			$categories = $wpdb->get_results( "SELECT DISTINCT play_when as cat 
				FROM `" . MDJM_PLAYLIST_TABLE . "` WHERE `event_id` = '" . $event . "' ORDER BY `play_when`" );
			
			if( $categories )	{
				foreach( $categories as $category )	{
					$songs = $wpdb->get_results( "SELECT * FROM `" . MDJM_PLAYLIST_TABLE . "` 
						WHERE `event_id` = '" . $event . "' AND `play_when` = '" . $category->cat . "'" );
						
					$entries[$category->cat] = $songs;	
				}
				
				return $entries;
			}
			else
				return false;
		} // get_playlist_by_cat
		
		/*
		 * Count the songs in the playlist for the given event
		 *
		 * @param	int			$event		Required: The event ID
		 * @return	int|bool	$count		The number of records or false if none
		 */
		function count_playlist_entries( $event )	{
			global $wpdb;
			
			$count = $wpdb->get_var( "SELECT COUNT(*) FROM " . MDJM_PLAYLIST_TABLE . 
														" WHERE `event_id` = " . $event );
			
			return ( !empty( $count ) ? $count : '0' );
		} // count_playlist_entries
		
		/*
		 * Return the count of records that have been uploaded to the MDJM servers
		 *
		 *
		 */
		function count_playlist_uploaded()	{
			global $wpdb;
						
			$query = "SELECT COUNT(*) FROM `". MDJM_PLAYLIST_TABLE . "` WHERE `upload_procedure` = '1'";
			$result = $wpdb->get_var( $query );
			
			echo $result;
			
		} // count_playlist_uploaded

/*
 * Venue functions
 */		
		/*
		 * mdjm_get_venues
		 * Pull all venues from the database
		 * 
		 * return: $venues => array
		 */
		public function mdjm_get_venues()	{
			$venues = get_posts( array(
									'post_type'	=> MDJM_VENUE_POSTS,
									'orderby'	  => 'title',
									'order'		=> 'ASC',
									)
								);
			
			return $venues;
		}
		
		/*
		 * mdjm_add_venue
		 * Add new Venue
		 * 
		 * params: $venue_data => array() the venue name
		 		   $venue_meta => array() Venue meta data
		 * return: $venue_post_id = the post_id
		 */
		public function mdjm_add_venue( $venue_data, $venue_meta )	{
			error_log( 'Adding Venue', 3, MDJM_DEBUG_LOG );
			if( !current_user_can( 'administrator' ) && !dj_can( 'add_venue' ) )
				return;
			
			error_log( 'Adding Venue', 3, MDJM_DEBUG_LOG );
			if( empty( $venue_data ) || !is_array( $venue_data ) || empty( $venue_meta ) || !is_array( $venue_meta ) )
				return;	
			
			error_log( 'Adding Venue', 3, MDJM_DEBUG_LOG );
			/* -- Insert the Venue -- */
			$post_data['post_title'] = !empty( $venue_data['venue_name'] ) ? $venue_data['venue_name'] : '';
			$post_data['post_content'] = '';
			$post_data['post_type'] = MDJM_VENUE_POSTS;
			$post_data['post_author'] = get_current_user_id();
			$post_data['post_status'] = 'publish';
			$post_data['ping_status'] = 'closed';
			$post_data['comment_status'] = 'closed';
			
			error_log( 'Adding Venue', 3, MDJM_DEBUG_LOG );
			
			$venue_post_id = wp_insert_post( $post_data );
			
			/* -- And the meta -- */
			if( $venue_post_id )	{
				foreach( $venue_meta as $meta_key => $meta_value )	{					
					if( !empty( $meta_value ) && $meta_key != 'venue_name' )
						add_post_meta( $venue_post_id, '_' . $meta_key, $meta_value );	
				}	
			}
			return $venue_post_id;
		} // mdjm_add_venue
		
		/*
		 * mdjm_get_venue_details
		 * Retrieve all venue meta
		 * 
		 * @param: venue_post_id
		 * @return: $venue_meta => array
		 */
		function mdjm_get_venue_details( $venue_post_id='', $event_id='' )	{
			
			if( empty( $venue_post_id ) && empty( $event_id ) )
				return;
			
			/* -- No post means we use the event database */
			if( false === get_post_status( $venue_post_id ) || !is_numeric( $venue_post_id ) )	{
				$event_details = $this->mdjm_event_by( 'ID', $event_id );
				if( !$event_details )
					return;
				
				$venue_details['name'] = get_post_meta( $event_id, '_mdjm_event_venue_name', true );
				$venue_details['venue_contact'] = get_post_meta( $event_id, '_mdjm_event_venue_contact', true );
				$venue_details['venue_phone'] = get_post_meta( $event_id, '_mdjm_event_venue_phone', true );
				$venue_details['venue_email'] = get_post_meta( $event_id, '_mdjm_event_venue_email', true );
				$venue_details['venue_address1'] = get_post_meta( $event_id, '_mdjm_event_venue_address1', true );
				$venue_details['venue_address2'] = get_post_meta( $event_id, '_mdjm_event_venue_address2', true );
				$venue_details['venue_town'] = get_post_meta( $event_id, '_mdjm_event_venue_town', true );
				$venue_details['venue_county'] = get_post_meta( $event_id, '_mdjm_event_venue_county', true );
				$venue_details['venue_postcode'] = get_post_meta( $event_id, '_mdjm_event_venue_postcode', true );
			}
			/* -- The venue post exists -- */
			else	{
				$venue_keys = array(
							'_venue_contact',
							'_venue_phone',
							'_venue_email',
							'_venue_address1',
							'_venue_address2',
							'_venue_town',
							'_venue_county',
							'_venue_postcode',
							'_venue_information',
							);
				$venue_name = get_the_title( $venue_post_id );
				$all_meta = get_post_meta( $venue_post_id );
				if( empty( $all_meta ) )
					return;
					
				$venue_details['name'] = ( !empty( $venue_name ) ? $venue_name : '' );
				foreach( $venue_keys as $key )	{
					$venue_details[substr( $key, 1 )] = !empty( $all_meta[$key][0] ) ? $all_meta[$key][0] : '';
				}
				
				// Venue details
				$details = wp_get_object_terms( $venue_post_id, 'venue-details' );
				
				foreach( $details as $detail )	{
					$venue_details['details'][] = $detail->name;	
				}				
			}
			// Full address
			$venue_details['full_address'][] = !empty( $venue_details['venue_address1'] ) ? $venue_details['venue_address1'] : '';
			$venue_details['full_address'][] = !empty( $venue_details['venue_address2'] ) ? $venue_details['venue_address2'] : '';
			$venue_details['full_address'][] = !empty( $venue_details['venue_town'] ) ? $venue_details['venue_town'] : '';
			$venue_details['full_address'][] = !empty( $venue_details['venue_county'] ) ? $venue_details['venue_county'] : '';
			$venue_details['full_address'][] = !empty( $venue_details['venue_postcode'] ) ? $venue_details['venue_postcode'] : '';
			$venue_details['full_address'][] = !empty( $venue_details['venue_contact'] ) ? $venue_details['venue_contact'] : '';
			$venue_details['full_address'][] = !empty( $venue_details['venue_phone'] ) ? $venue_details['venue_phone'] : '';
			$venue_details['full_address'][] = !empty( $venue_details['venue_email'] ) ? $venue_details['venue_email'] : '';
				
			return $venue_details;
		} // mdjm_get_venue_details
	
/*
 * Journal functions
 */
		/*
		 * add_journal
		 * Adds a new journal entry (comment)
		 * 
		 * @param: 	$data			arr	
		 			user			int		Required: user id of journal author
					event			int		Optional: the event (post ID) Required if not in wp loop
					comment_content	str		Required: the comment content
					comment_type	str		Optional: The comment type
												* possible values	mdjm-journal
					comment_date	str		Optional: The date of the comment strtotime
		 			$meta => array 
								type => the type of action taken to initiate the entry **Required
									-- possible values:
											'create-event'
											'update-event'
											'email-client'
											'email-dj'
											'email-admin'
											'added-note'
								visibility => 0 (admin) | 1 (dj) | 2 (client)
								to => int user message to
								is_read => 0 | 1
								notify => int - user to notify
		 * @return: comment_id | false
		 */
		public function add_journal( $data, $meta )	{
			global $mdjm, $post;
			
			if( MDJM_JOURNAL != true )	{
				if( MDJM_DEBUG == true )
						$mdjm->debug_logger( 'ERROR:	Instructed to Journal whilst Journalling is disabled' );
	
				return;	
			}
			
			if( empty( $data['comment_content'] ) )	{
				if( MDJM_DEBUG == true )
						$mdjm->debug_logger( 'ERROR: Missing Comment Contents in ' . __FUNCTION__, true );
				
				return false;
			}
			if( empty( $meta['type'] ) )	{
				if( MDJM_DEBUG == true )
						$mdjm->debug_logger( 'ERROR: Missing Comment Type in ' . __FUNCTION__, true );
				
				return false;
			}
			
			/* -- Disable Comment Flood Prevention -- */
			add_filter( 'comment_flood_filter', '__return_false' );
				
			$event_id = !empty( $data['event'] ) ? $data['event'] : $post->ID;
			if( empty( $event_id ) )	{
				if( MDJM_DEBUG == true )
						$mdjm->debug_logger( 'ERROR: Missing event id in ' . __FUNCTION__, true );
				
				return false;
			}
			
			/* -- Set the content -- */
			if( isset( $data['user'] ) )
				$commenter = get_userdata( $data['user'] );
			else
				$commenter = 'mdjm';
							
			$comment_data = array(
							'comment_post_ID'		=> (int) $event_id,
							'comment_author' 	 	 => ( $commenter != 'mdjm' ? $commenter->display_name : '' ),
							'comment_author_email'   => ( $commenter != 'mdjm' ? $commenter->user_email : '' ),
							'comment_author_IP'	  => ( !empty( $_SERVER['REMOTE_ADDR'] ) ? preg_replace( '/[^0-9a-fA-F:., ]/', '',$_SERVER['REMOTE_ADDR'] ) : '' ),
							'comment_agent'		  => ( isset( $_SERVER['HTTP_USER_AGENT'] ) ? substr( $_SERVER['HTTP_USER_AGENT'], 0, 254 ) : '' ),
							'comment_author_url'	 => ( $commenter != 'mdjm' ? ( !empty( $commenter->user_url ) ? $commenter->user_url : '' ) : '' ),
							'comment_content'		=>  $data['comment_content'] . ' (' . time() . ')',
							'comment_type'		   => ( !empty( $data['comment_type'] ) ? $data['comment_type'] : 'mdjm-journal' ),
							'comment_date'		   => ( !empty( $data['comment_date'] ) ? $data['comment_date'] : current_time( 'mysql' ) ),
							'user_id'				=> ( $commenter != 'mdjm' ? $commenter->ID : '0' ),
							'comment_parent'		 => 0,
							'comment_approved'	   => 1,
							);
							
			// Filter the comment data before inserting
			$comment_data = apply_filters( 'preprocess_comment', $comment_data );
			
			$comment_data = wp_filter_comment( $comment_data );
			
			/* -- Disable comment duplication check filter -- */
			remove_filter( 'commentdata','comment_duplicate_trigger' );
			
			/* -- Insert the entry -- */
			$comment_id = wp_insert_comment( $comment_data );
			
			if( empty( $comment_id ) )
				return false;
			
			/* -- Set the meta -- */
			$comment_meta = array(
								'mdjm_type'		  => $meta['type'],
								'mdjm_visibility'	=> ( !empty( $meta['visibility'] ) ? $meta['visibility'] : '0' ),
								'mdjm_notify'		=> ( !empty( $meta['notify'] ) ? $meta['notify'] : '' ),
								'mdjm_to'			=> ( !empty( $meta['to'] ) ? $meta['to'] : '' ),
								'mdjm_isread'		=> ( !empty( $meta['isread'] ) ? $meta['isread'] : '' ),
								);
			
			/* -- Insert the meta -- */	
			foreach( $comment_meta as $key => $value )	{
				if( !empty( $value ) )
					add_comment_meta( $comment_id, $key, $value, false );	
			}
			
			/* -- Enable comment filter -- */
			add_filter( 'commentdata', 'comment_duplicate_trigger' );
			
			return $comment_id;	
		} // add_journal
	} // class
?>