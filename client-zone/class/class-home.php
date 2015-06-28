<?php
/*
 * class-home.php
 * 19/05/2015
 * @since 2.0
 * The ClientZone home page class
 * 
 */
	
	defined( 'ABSPATH' ) or die( 'Direct access to this page is disabled!!!' );
	
	/* -- Build the ClientZone class -- */
		class ClientZone_Home	{
						
		 /*
		  * __construct
		  * defines the params used within the class
		  *
		  *
		  */
			public function __construct()	{
				global $clientzone_loaded, $clientzone, $my_mdjm;
				
				if( isset( $_GET['message'], $_GET['class'] ) )
					$clientzone->display_message( $_GET['message'], $_GET['class'] );
				
				if( isset( $_GET['action'] ) )	{
					if( $_GET['action'] == 'view_event' )
						$this->single_event();
						
					else // Process actions
						$this->process_event_action();
				}
				else
					$this->display_events();
				
			} // __construct
			
			/*
			 * Return the possible actions for the given event via a drop down menu
			 *
			 * @param	int		$id			The event id is required or $post global var must be set
			 *			str		$status		The event status is required or $post global var must be set					
			 *
			 */
			public function event_actions_dropdown( $id='', $status='' )	{
				global $post, $mdjm;
				
				if( empty( $post ) && empty( $id ) )	{
					if( MDJM_DEBUG == true )
						$mdjm->debug_logger( 'ERROR: No global $post variable is set and no $id was parsed in ' . __METHOD__, true );
				}
				
				if( empty( $post ) && empty( $status ) )	{
					if( MDJM_DEBUG == true )
						$mdjm->debug_logger( 'ERROR: No global $post variable is set and no $status was parsed in ' . __METHOD__, true );
				}
				
				$event_id = !empty( $post ) ? $post->ID : $id;
				$event_status = !empty( $post ) ? $post->post_status : $status;
				
				// The link to view event details is always available
				$selections['view_event'] = 'View Details';
				
				switch( $event_status )	{
					case 'mdjm-unattended':
						$selections['cancel_event'] = __( 'Cancel Event' );
					break;
					case 'mdjm-enquiry':
						$selections['accept_enquiry'] = __( 'Book Event' );
						$selections['cancel_event'] = __( 'Cancel Event' );
					break;
					case 'mdjm-contract':
						$selections['sign_contract'] = __( 'Sign Contract' );
						$selections['cancel_event'] = __( 'Cancel Event' );
					break;
					case 'mdjm-approved':
						// Payment Option
						$balance_status = get_post_meta( $event_id, '_mdjm_event_balance_status', true );
						$deposit_status = get_post_meta( $event_id, '_mdjm_event_deposit_status', true );
						if( $deposit_status != 'Paid' || $balance_status != 'Paid' )
							$selections['make_payment'] = __( 'Make a Payment' );
					break;
				} // End switch
				
				$actions = '<select name="event_action_' . $event_id . '" id="event_action_' . $event_id . '">' . "\r\n" . 
						   '<option value="">--- Select Action ---</option>' . "\r\n";
				
				foreach( $selections as $key => $value )	{
					$actions .= '<option value="' . $key . '">' . $value . '</option>' . "\r\n";	
				}
				
				$actions .= '</select>' . "\r\n";
				
				return $actions;
				
			} // event_actions_dropdown
			
			/*
			 * Display the action buttons for the given event
			 *
			 * @param	int		$id			The event id is required or $post global var must be set
			 *			str		$status		The event status is required or $post global var must be set					
			 *
			 */
			public function display_action_buttons( $id='', $status='' )	{
				global $post, $mdjm;
				
				if( empty( $post ) && empty( $id ) )	{
					if( MDJM_DEBUG == true )
						$mdjm->debug_logger( 'ERROR: No global $post variable is set and no $id was parsed in ' . __METHOD__, true );
				}
				
				if( empty( $post ) && empty( $status ) )	{
					if( MDJM_DEBUG == true )
						$mdjm->debug_logger( 'ERROR: No global $post variable is set and no $status was parsed in ' . __METHOD__, true );
				}
				
				$event_id = !empty( $id ) ? $id : $post->ID;
				$event_status = !empty( $id ) ? $status : $post->post_status;
				
				switch( $event_status )	{
					case 'mdjm-enquiry':
						$actions[] = '<button type="reset" onclick="location.href=\'' . wp_nonce_url( $mdjm->get_link( MDJM_HOME, true ) . 
							'action=accept_enquiry&amp;event_id=' . $event_id, 'book_event', '__mdjm_verify' ) . '\'">' . __( 'Book this Event' ) . '</button>';
					break;
					case 'mdjm-contract':
						$actions[] = '<button type="reset" onclick="location.href=\'' . wp_nonce_url( $mdjm->get_link( MDJM_CONTRACT_PAGE, true ) . 
							'event_id=' . $event_id, 'sign_contract', '__mdjm_verify' ) . '\'">' . __( 'Review &amp; Approve Contract' ) . '</button>';
					break;
					case 'mdjm-approved':
						$actions[] = '<button type="reset" onclick="location.href=\'' . wp_nonce_url( $mdjm->get_link( MDJM_CONTRACT_PAGE, true ) . 
							'event_id=' . $event_id, 'view_contract', '__mdjm_verify' ) . '\'">' . __( 'View Contract' ) . '</button>';
					break;
				} // switch
				
				if( MDJM_PAYMENTS == true )	{
					if( get_post_meta( $event_id, '_mdjm_deposit_status', true ) != 'Paid' || get_post_meta( $event_id, '_mdjm_balance_status', true ) != 'Paid' )
						$actions[] = '<button type="reset" onclick="location.href=\'' . wp_nonce_url( $mdjm->get_link( MDJM_PAYMENT_PAGE, true ) . 
							'event_id=' . $event_id, 'make_payment', '__mdjm_verify' ) . '\'">' . __( 'Make a Payment' ) . '</button>';
				}
				
				if( $event_status == 'mdjm-approved' || $event_status == 'mdjm-contract' )
					$actions[] = '<button type="reset" onclick="location.href=\'' . wp_nonce_url( $mdjm->get_link( MDJM_PLAYLIST_PAGE, true ) . 
							'event_id=' . $event_id, 'manage_playlist', '__mdjm_verify' ) . '\'">' . __( 'Manage Playlist' ) . '</button>';
							
				$actions[] = '<button type="reset" onclick="location.href=\'' . wp_nonce_url( $mdjm->get_link( MDJM_PROFILE_PAGE, false ), 'manage_profile', '__mdjm_verify' ) . 
							 '\'">' . __( 'Update Profile' ) . '</button>';
							 
				$actions[] = '<button type="reset" onclick="location.href=\'' . $mdjm->get_link( MDJM_CONTACT_PAGE, false ) . 
							 '\'">' . __( 'Book Another Event' ) . '</button>';
							 
				$columns = 3; // Maximum column width
				$i = 1; // Counter for the current column
				$x = 1; // Counter for the total number of actions
				
				echo '<div id="mdjm-actions-table">' . "\r\n";
					echo '<table id="mdjm-event-actions">' . "\r\n";
					
					foreach( $actions as $action )	{
						if( $i == 1 )
							echo '<tr>' . "\r\n";
						
						echo '<td>' . $action . '</td>' . "\r\n";
							
						if( $i == $columns )
							echo '</tr>' . "\r\n";
						
						$i++;
						if( $i == 4 )
							$i = 1;
						
						$x++;
					}
					/* -- Create additional rows if required -- */
					if( $i != 1 )	{
						while( $i <= 3 )	{
							echo '<td>&nbsp;</td>' . "\r\n";
							$i++;	
						}
					}
					echo '</tr>' . "\r\n";
					
					echo '</table>' . "\r\n";
				echo '</div>' . "\r\n";
				
			} // display_action_buttons
			
			/*
			 * Event action controller
			 * Initiate the associated event action
			 *
			 *
			 */
			public function process_event_action()	{
				global $mdjm, $clientzone;
				
				$action = !empty( $_GET['action'] ) ? $_GET['action'] : '';
				$event_id = !empty( $_GET['event_id'] ) ? $_GET['event_id'] : '';
				
				if( empty( $action ) || empty( $event_id ) )	{
					if( MDJM_DEBUG == true )
						$mdjm->debug_logger( 'ERROR: Unable to process event action as ' . ( empty( $action ) ? 'the action is missing' : 
							'the event ID is missing' ) . 'in . ' . __METHOD__, true );
				}
				
				/* -- If we pass the checks we can continue -- */
				if( $action == 'accept_enquiry' )	{
					$clientzone->accept_enquiry( get_post( $event_id ) );
					$this->single_event();
				}
				
				
			} // process_event_action
			
			/*
			 * Display a list of the clients events
			 *
			 *
			 *
			 */
			public function display_events()	{
				global $clientzone, $mdjm, $my_mdjm, $post;
				
				// Intro text
				echo $clientzone->__text( 
									'home_welcome', 
					 				'<p>Hello ' . $my_mdjm['me']->first_name . ' and welcome to the <a href="' . site_url() . '">' . MDJM_COMPANY . 
									'</a> ' . MDJM_APP . '.</p>' );
				
				// If there are no events
				if( empty( $my_mdjm['active'] ) )	{
					echo $clientzone->__text( 
										'home_noevents',
										'<p>You currently have no upcoming events. Please <a title="Contact ' . get_bloginfo( 'name' ) .
										'" href="' . $mdjm->get_link( MDJM_CONTACT_PAGE, false ) . '">contact me</a> now to start planning your next event.</p>' );
				}
				// Single active event
				elseif( count( $my_mdjm['active'] ) == 1 )	{
					$this->single_event();
				}
				// Multiple active events
				else	{					
					$this->multi_events();
				}
				
			} // events_page
			
			/*
			 * Display for multiple client events
			 *
			 *
			 *
			 */
			public function multi_events()	{
				global $clientzone, $mdjm, $my_mdjm, $post;
				
				// Incomplete Profile warning
				if( !$clientzone->client_profile_complete( $my_mdjm['me']->ID ) && $clientzone->warn_profile() )
					$clientzone->display_notice( 3, 
												 'Your <a href="' . $mdjm->get_link( MDJM_PROFILE_PAGE, false ) . 
												 '">profile</a> appears to be incomplete. Please <a href="' . $mdjm->get_link( MDJM_PROFILE_PAGE, false ) . 
												 '">click here</a> to update it now. Incorrect <a href="' . $mdjm->get_link( MDJM_PROFILE_PAGE, false ) . 
												 '">profile</a> information can cause problems with your booking.' );
				
				echo '<p>' . __( 'Click on an event date below to begin managing that event.' ) . '</p>' . "\r\n";
						
				echo '<div id="mdjm_multi_event_listing">' . "\r\n";
					echo '<table id="mdjm-events-list">' . "\r\n";
						echo '<tr>' . "\r\n";
							echo '<th style="width: 30%;">' . __( 'Date' ) . '</th>' . "\r\n";
							echo '<th style="width: 30%;">' . __( 'Status' ) . '</th>' . "\r\n";
							echo '<th>' . __( 'Cost' ) . '</th>' . "\r\n";
						echo '</tr>' . "\r\n";
					
						/* -- List out each event with possible actions -- */
						foreach( $my_mdjm['active'] as $post )	{
							setup_postdata( $post );
							if( $post->post_status == 'mdjm-unattended' )
								continue; // We don't display unattended events here
							$eventinfo = $mdjm->mdjm_events->event_detail( $post->ID ); // The event details
							echo '<tr>' . "\r\n";
								echo '<td><a href="' . $mdjm->get_link( MDJM_HOME, true ) . 'action=view_event&amp;event_id=' . $post->ID . '">' . 
									date( MDJM_SHORTDATE_FORMAT, $eventinfo['date'] ) . '</a></td>' . "\r\n";
									
								echo '<td>' . get_post_status_object( $post->post_status )->label . '</td>' . "\r\n";
								echo '<td>' . $eventinfo['cost'] . '</td>' . "\r\n";
							echo '</tr>' . "\r\n";
						}
						wp_reset_postdata();
					echo '</table>' . "\r\n";
				echo '</div>' . "\r\n";
			} // multi_events
			
			/*
			 * Display the details of the single event
			 * This can be used for clients with single or multiple events
			 *
			 *
			 */
			public function single_event()	{
				global $clientzone, $mdjm, $my_mdjm, $mdjm_settings, $post;
				
				$event = isset( $_GET['event_id'] ) ? get_post( $_GET['event_id'] ) : $my_mdjm['next'][0];	
				
				$post = $event;
				
				if( !$mdjm->mdjm_events->is_my_event( $event->ID ) )	{
					if( MDJM_DEBUG == true )
						$mdjm->debug_logger( 'ERROR: ' . get_current_user_id() . ' is attempting to access event ID ' . $event->ID . 
											 ' which is not theirs. In ' . __METHOD__, true );
											 
					wp_die( $clientzone->display_message( 9, 5 ), 'Event Ownership Error' );
				}
				
				$eventinfo = $mdjm->mdjm_events->event_detail( $event->ID );
				
				$expired = array( 'mdjm-failed', 'mdjm-cancelled', 'mdjm-completed' );
				
				// Event not active
				if( in_array( $event->post_status, $expired ) )	{
					echo $clientzone->__text( 
					'home_notactive', 
					'<p>The selected event is no longer active. <a href="' . $mdjm->get_link( MDJM_CONTACT_PAGE, false ) . 
					'" title="Begin planning your next event with us">Contact us now</a> begin planning your next event.</p>' 
					);
				}
				// Display the current event with options
				else	{
					echo '<div id="mdjm_event_updated"></div>';
					echo '<p>' . __( 'Below are the details of your upcoming event on ' . date( 'l, jS F Y', $eventinfo['date'] ) . '.' ) . '</p>' . "\r\n";
					echo '<p>If any of the event details are incorrect, please <a href="mailto:' . $mdjm_settings['email']['system_email'] . 
						'">contact me now</a>.</p>' . "\r\n";
						
					// Incomplete Profile warning
					if( !$clientzone->client_profile_complete( $my_mdjm['me']->ID ) && $clientzone->warn_profile() )
						$clientzone->display_notice( 3, 
													 'Your <a href="' . $mdjm->get_link( MDJM_PROFILE_PAGE, false ) . 
													 '">profile</a> appears to be incomplete. Please <a href="' . $mdjm->get_link( MDJM_PROFILE_PAGE, false ) . 
													 '">click here</a> to update it now. Incorrect <a href="' . $mdjm->get_link( MDJM_PROFILE_PAGE, false ) . 
													 '">profile</a> information can cause problems with your booking.' );
					
					
					// Display the event details
					echo '<form name="mdjm_event" id="mdjm_event" method="post">' . "\r\n";
					echo '<input type="hidden" name="event_id" id="event_id" value="' . $event->ID . '" />' . "\r\n";
					wp_nonce_field( 'manage_client_event', '__mdjm_event' );
					$this->display_action_buttons( $event->ID, $event->post_status );
					echo '<div id="mdjm-event-container">' . "\r\n";
						echo '<div id="mdjm-event-table">' . "\r\n";
							echo '<table id="mdjm-event-listing">' . "\r\n";
								echo '<tr>' . "\r\n";
									echo '<th style="width: 15%;">' . __( 'Status:' ) . '</th>' . "\r\n";
									echo '<td style="width: 35%;">' . __( get_post_status_object( $event->post_status )->label ) . '</td>' . "\r\n";
									echo '<th style="width: 15%;">' . __( 'Your ' . MDJM_DJ . ':' ) . '</th>' . "\r\n";
									echo '<td style="width: 35%;">' . $eventinfo['dj']->display_name . '</td>' . "\r\n";
								echo '</tr>' . "\r\n";
								echo '<tr>' . "\r\n";
									echo '<th style="width: 15%;">' . __( 'Event Date:' ) . '</th>' . "\r\n";
									echo '<td style="width: 35%;">' . date( MDJM_SHORTDATE_FORMAT, $eventinfo['date'] ) . '</td>' . "\r\n";
									echo '<th style="width: 15%;">' . __( 'Event Type:' ) . '</th>' . "\r\n";
									echo '<td style="width: 35%;">' . __( $eventinfo['type'] ) . '</td>' . "\r\n";
								echo '</tr>' . "\r\n";
								echo '<tr>' . "\r\n";
									echo '<th style="width: 15%;">' . __( 'Start Time:' ) . '</th>' . "\r\n";
									echo '<td style="width: 35%;">' . $eventinfo['start'] . '</td>' . "\r\n";
									echo '<th style="width: 15%;">' . __( 'End Time:' ) . '</th>' . "\r\n";
									echo '<td style="width: 35%;">' . $eventinfo['finish'] . '</td>' . "\r\n";
								echo '</tr>' . "\r\n";
								echo '<tr>' . "\r\n";
									echo '<th style="width: 15%;">' . __( 'Total Cost:' ) . '</th>' . "\r\n";
									echo '<td style="width: 35%;">' . $eventinfo['cost'] . '</td>' . "\r\n";
									echo '<th style="width: 15%;">' . __( MDJM_DEPOSIT_LABEL ) . ':</th>' . "\r\n";
									echo '<td style="width: 35%;">' . $eventinfo['deposit'] . ' (' . __( $eventinfo['deposit_status'] ) . ')</td>' . "\r\n";
								echo '</tr>' . "\r\n";
								echo '<tr>' . "\r\n";
									echo '<th style="width: 15%;">' . __( MDJM_BALANCE_LABEL ) . ':</th>' . "\r\n";
									echo '<td colspan="3">' . $eventinfo['balance'] . ' (' . __( $eventinfo['balance_status'] ) . ')</td>' . "\r\n";
								echo '</tr>' . "\r\n";
								
								if( !empty( $eventinfo['notes'] ) )	{
									echo '<tr>' . "\r\n";
										echo '<th style="width: 15%;">' . __( 'Information:' ) . '</th>' . "\r\n";
										echo '<td colspan="3">' . stripslashes( $eventinfo['notes'] ) . '</td>' . "\r\n";
									echo '</tr>' . "\r\n";	
								}
								
								echo '<tr>' . "\r\n";
									echo '<td colspan="4">&nbsp;</td>' . "\r\n";
								echo '</tr>' . "\r\n";
								echo '<tr>' . "\r\n";
									echo '<th colspan="4"><span style="text-decoration: underline;">' . __( 'Client Contact Details' ) . '</span>&nbsp;&nbsp;&nbsp;<a href="' . 
									$mdjm->get_link( MDJM_PROFILE_PAGE, false ) . '">' . 
									__( 'edit' ) . '</a></th>' . "\r\n";
								echo '</tr>' . "\r\n";
								
								echo '<tr>' . "\r\n";
									echo '<th style="width: 15%;">' . __( 'Name:' ) . '</th>' . "\r\n";
									echo '<td style="width: 35%;">' . $my_mdjm['me']->display_name . '</td>' . "\r\n";
									echo '<th style="width: 15%;">' . __( 'Phone:' ) . '</th>' . "\r\n";
									echo '<td style="width: 35%;">' . $my_mdjm['me']->phone1 . ' ' . ( !empty( $my_mdjm['me']->phone2 )
																								? $my_mdjm['me']->phone2 : '' ) . '</td>' . "\r\n";
								echo '</tr>' . "\r\n";
								echo '<tr>' . "\r\n";
									echo '<th style="width: 15%;">' . __( 'Email:' ) . '</th>' . "\r\n";
									echo '<td style="width: 35%;">' . $my_mdjm['me']->user_email . '</td>' . "\r\n";
									echo '<th style="width: 15%;">' . __( 'Address:' ) . '</th>' . "\r\n";
									echo '<td style="width: 35%;">' . $clientzone->get_client_address() . '</td>' . "\r\n";
								echo '</tr>' . "\r\n";
								
								echo '<tr>' . "\r\n";
									echo '<td colspan="4">&nbsp;</td>' . "\r\n";
								echo '</tr>' . "\r\n";
								echo '<tr>' . "\r\n";
									echo '<th colspan="4"><span style="text-decoration: underline;">' . __( 'Venue Details' ) . '</span></th>' . "\r\n";
								echo '</tr>' . "\r\n";
								
								$venue_details = $mdjm->mdjm_events->mdjm_get_venue_details( get_post_meta( $event->ID, '_mdjm_event_venue_id', true ), $event->ID );
																
								echo '<tr>' . "\r\n";
									echo '<th style="width: 15%;">' . __( 'Venue:' ) . '</th>' . "\r\n";
									echo '<td style="width: 35%;">' . stripslashes( $venue_details['name'] ) . '</td>' . "\r\n";
									echo '<th style="width: 15%;">' . __( 'Address:' ) . '</th>' . "\r\n";
									echo '<td style="width: 35%;">' . implode( '<br />', $venue_details['full_address'] ) . '</td>' . "\r\n";
								echo '</tr>' . "\r\n";
								
							echo '</table>' . "\r\n";
						echo '</div>' . "\r\n"; // End div mdjm-event-table						
					echo '</div>' . "\r\n"; // End div mdjm-event-container
					echo '</form>' . "\r\n";
					
				} // foreach( $posts as $post )
			} // single_event
			
		} // class ClientZone_Home
		
/* -- Insantiate the ClientZone_Home class if the user is logged in-- */
	global $clientzone;
	
	if( !is_user_logged_in() )
		$clientzone->login();
		
	else
		$mdjm_home = new ClientZone_Home();