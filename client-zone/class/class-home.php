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
				global $clientzone_loaded, $clientzone, $my_mdjm, $mdjm_settings;
				
				define( 'MDJM_EDIT_EVENT', ( !empty( $mdjm_settings['clientzone']['update_event'] ) ? true : false ) );
				define( 'MDJM_EDIT_EVENT_DISABLE', ( !empty( $mdjm_settings['clientzone']['edit_event_stop'] ) ? 
					$mdjm_settings['clientzone']['edit_event_stop'] : '5' ) );
				
				if( isset( $_GET['message'], $_GET['class'] ) )
					$clientzone->display_message( $_GET['message'], $_GET['class'] );
				
				if( isset( $_GET['action'] ) )	{
					if( $_GET['action'] == 'view_event' )
						$this->single_event();
						
					elseif( $_GET['action'] == 'edit_event_detail' )	{
						wp_enqueue_script( 'mdjm-dynamics' );
						wp_localize_script( 'mdjm-dynamics', 'mdjmaddons', array( 'ajax_url' => admin_url( 'admin-ajax.php' ) ) );
						$this->edit_event_form( $_GET['event_id'] );
					}
						
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
				
				if( isset( $_POST['submit'] ) && $_POST['submit'] == 'Submit Changes' )
					$this->update_event();
				
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
									echo '<th colspan="4"><span style="text-decoration: underline;">' . __( 'Event Details' ) . '</span>&nbsp;&nbsp;&nbsp;' . 
									$this->edit_event_link( 'edit', $event->ID ) . '</th>' . "\r\n";
								echo '</tr>' . "\r\n";
								
								echo '<tr>' . "\r\n";
									echo '<th style="width: 20%;">' . __( 'Event Name:' ) . '</th>' . "\r\n";
									echo '<td colspan="3">' . ( !empty( $eventinfo['name'] ) ? 
										esc_attr( $eventinfo['name'] ) : $eventinfo['type'] ) . '</span>&nbsp;&nbsp;&nbsp;' . 
										$this->edit_event_link( 'change', $event->ID ) . '</td>' . "\r\n";
								echo '</tr>' . "\r\n";
								echo '<tr>' . "\r\n";
									echo '<th style="width: 15%;">' . __( 'Status:' ) . '</th>' . "\r\n";
									echo '<td style="width: 35%;">' . __( get_post_status_object( $event->post_status )->label ) . '</td>' . "\r\n";
									echo '<th style="width: 15%;">' . __( 'Your ' . MDJM_DJ . ':' ) . '</th>' . "\r\n";
									echo '<td style="width: 35%;">' . ( !empty( $eventinfo['dj']->display_name ) ? 
										$eventinfo['dj']->display_name : $eventinfo['dj'] ) . '</td>' . "\r\n";
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
									echo '<th style="width: 15%;">' . __( 'Package:' ) . '</th>' . "\r\n";
									echo '<td style="width: 35%;">' . 
										( !empty( $eventinfo['package'] ) ? '<a title="' . ( !empty( $eventinfo['package']['desc'] ) ? 
										$eventinfo['package']['desc'] : '' ) . ' - ' . display_price( $eventinfo['package']['cost'] ) . '">' . 
										$eventinfo['package']['name'] . '</a>' : 'None' ) 
										. '</td>' . "\r\n";
									echo '<th style="width: 15%;">' . __( 'Addons:' ) . '</th>' . "\r\n";
									echo '<td style="width: 35%;">';
									if( !empty( $eventinfo['addons'] ) )	{
										$i = 1;
										foreach( $eventinfo['addons'] as $addon )	{
											$item = get_addon_details( $addon );
											echo '<a title="' . ( !empty( $item['desc'] ) ? $item['desc'] : '' ) . ' - ' 
												. display_price( $item['cost'] ) . '">' . $item['name'] . '</a>';
											echo ( $i < count( $eventinfo['addons'] ) ? '<br />' : '' );
											$i++;	
										}
									}
									else	{
										echo 'None';	
									}
										'</td>' . "\r\n";
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
									echo '<th colspan="4"><span style="text-decoration: underline;">' . __( 'Your Contact Details' ) . '</span>&nbsp;&nbsp;&nbsp;<a href="' . 
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
			
			/*
			 * Display form to enable client to edit event
			 *
			 *
			 *
			 */
			function edit_event_form( $event_id )	{
				global $post, $mdjm;
				
				$post = get_post( $event_id );
				
				$eventinfo = $mdjm->mdjm_events->event_detail( $post->ID );
				
				$existing_event_type = wp_get_object_terms( $post->ID, 'event-types' );
								
				echo '<form name="edit_client_event" id="edit_client_event" method="post" action="' . $mdjm->get_link( MDJM_HOME, true ) . 
					'action=view_event&event_id=' . $event_id . '">' . "\r\n";
				
				echo '<input type="hidden" name="event_id" id="event_id" value="' . $post->ID . '" />' . "\r\n";
				echo '<input type="hidden" name="event_dj" id="event_dj" value="' . ( isset( $eventinfo['dj']->ID ) ? $eventinfo['dj']->ID : '0' ) . '" />' . "\r\n";
				wp_nonce_field( 'manage_event', '__mdjm_event' );
				echo '<div id="mdjm-event-container">' . "\r\n";
					echo '<div id="mdjm-event-table">' . "\r\n";
						echo '<table id="mdjm-event-listing">' . "\r\n";
							echo '<tr>' . "\r\n";
								echo '<th colspan="4"><span style="text-decoration: underline;">' . __( 'Event Details' ) . '</th>' . "\r\n";
							echo '</tr>' . "\r\n";
							
							echo '<tr>' . "\r\n";
								echo '<th style="width: 20%;"><label for="_mdjm_event_name">' . __( 'Event Name:' ) . '</label></th>' . "\r\n";
								echo '<td colspan="3"><input type="text" name="_mdjm_event_name" id="_mdjm_event_name" value="' 
									. ( !empty( $eventinfo['name'] ) ? 
									esc_attr( $eventinfo['name'] ) : $eventinfo['type'] ) . '" /></td>' . "\r\n";
							echo '</tr>' . "\r\n";
							echo '<tr>' . "\r\n";
								echo '<th style="width: 20%;"><label for="display_event_date">' . __( 'Event Date:' ) . '</label></th>' . "\r\n";
								echo '<td style="width: 30%;"><input type="text" class="mdjm_date required" name="display_event_date" id="display_event_date" ' . 
                				'value="' . ( get_post_meta( $post->ID, '_mdjm_event_date', true ) ? 
								date( MDJM_SHORTDATE_FORMAT, $eventinfo['date'] ) : '' ) . '" disabled="disabled" />' . 
								'<input type="hidden" name="_mdjm_event_date" id="_mdjm_event_date" value="' . ( !empty( $eventinfo['date'] ) ? 
								date( MDJM_SHORTDATE_FORMAT, $eventinfo['date'] ) : '' ) . '" /></td>' . "\r\n";
								
								echo '<th style="width: 20%;"><label for="mdjm_event_type">' . __( 'Event Type:' ) . '</label></th>' . "\r\n";
								echo '<td style="width: 30%;">';
								wp_dropdown_categories( array( 'taxonomy'		  => 'event-types',
															   'hide_empty'		=> 0,
															   'name'			  => 'mdjm_event_type',
															   'id' 				=> 'mdjm_event_type',
															   'selected'		  => ( isset( $existing_event_type[0]->term_id ) ? $existing_event_type[0]->term_id : '' ),
															   'orderby' 		   => 'name',
															   'hierarchical' 	  => 0,
															   'class'			 => 'mdjm-meta required',
                                                ) );
								'</td>' . "\r\n";
							echo '</tr>' . "\r\n";
							
							echo '<tr>' . "\r\n";
								echo '<th style="width: 20%;"><label for="event_start_hr">' . __( 'Start Time:' ) . '</label></th>' . "\r\n";
								echo '<td style="width: 30%;">';
								echo '<select name="event_start_hr" id="event_start_hr">' . "\r\n";
								$minutes = array( '00', '15', '30', '45' );
								if( MDJM_TIME_FORMAT == 'H:i' )	{
									$i = '00';
									$x = '23';
									$comp = 'H';
								}
								else	{
									$i = '1';
									$x = '12';
									$comp = 'g';	
								}
								while( $i <= $x )	{
									if( $i != 0 && $i < 10 && $comp == 'H' )
										$i = '0' . $i;
									echo '<option value="' . $i . '"';
									selected( date( $comp, strtotime( $eventinfo['start'] ) ), $i );
									echo '>' . $i . '</option>' . "\r\n";
									$i++;
								}
								echo '</select>' . "\r\n";
                				echo '<select name="event_start_min" id="event_start_min">' . "\r\n";
								foreach( $minutes as $minute )	{
									echo '<option value="' . $minute . '"';
									selected( date( 'i', strtotime( $eventinfo['start'] ) ), $minute );
									echo '>' . $minute . '</option>' . "\r\n";
								}
								echo '</select>' . "\r\n";
								
								if( MDJM_TIME_FORMAT != 'H:i' )	{
									echo '&nbsp;<select name="event_start_period" id="event_start_period">' . "\r\n";
									echo '<option value="AM"';
									selected( date( 'A', strtotime( $eventinfo['start'] ) ), 'AM' );
									echo '>AM</option>' . "\r\n";
									echo '<option value="PM"';
									selected( date( 'A', strtotime( $eventinfo['start'] ) ), 'PM' );
									echo '>PM</option>' . "\r\n";
									echo '</select>' . "\r\n";
								}
								
								echo '</td>' . "\r\n";
								echo '<th style="width: 20%;"><label for="event_finish_hr">' . __( 'End Time:' ) . '</label></th>' . "\r\n";
								echo '<td style="width: 30%;">';
								echo '<select name="event_finish_hr" id="event_finish_hr">' . "\r\n";
								
								$minutes = array( '00', '15', '30', '45' );
								if( MDJM_TIME_FORMAT == 'H:i' )	{
									$i = '00';
									$x = '23';
									$comp = 'H';
								}
								else	{
									$i = '1';
									$x = '12';
									$comp = 'g';	
								}
								while( $i <= $x )	{
									if( $i != 0 && $i < 10 && $comp == 'H' )
										$i = '0' . $i;
									echo '<option value="' . $i . '"';
									selected( date( $comp, strtotime( $eventinfo['finish'] ) ), $i );
									echo '>' . $i . '</option>' . "\r\n";
									$i++;
								}
								
								echo '</select>' . "\r\n";
								echo '<select name="event_finish_min" id="event_finish_min">' . "\r\n";
								foreach( $minutes as $minute )	{
									echo '<option value="' . $minute . '"';
									selected( date( 'i', strtotime( $eventinfo['finish'] ) ), $minute );
									echo '>' . $minute . '</option>' . "\r\n";
								}
								echo '</select>' . "\r\n";
								
								if( MDJM_TIME_FORMAT != 'H:i' )	{
									echo '&nbsp;<select name="event_finish_period" id="event_finish_period">' . "\r\n";
									echo '<option value="AM"';
									selected( date( 'A', strtotime( $eventinfo['finish'] ) ), 'AM' );
									echo '>AM</option>' . "\r\n";
									echo '<option value="PM"';
									selected( date( 'A', strtotime( $eventinfo['finish'] ) ), 'PM' );
									echo '>PM</option>' . "\r\n";
									echo '</select>' . "\r\n";
								}
								
								echo '</td>' . "\r\n";
							echo '</tr>' . "\r\n";
							
							if( MDJM_PACKAGES == true )	{
								echo '<tr>' . "\r\n";
									echo '<th style="width: 20%;"><label for="_mdjm_event_package">' . __( 'Package:' ) . '</label></th>' . "\r\n";
									echo '<td style="width: 30%;">' . mdjm_package_dropdown( array( 
																								'selected'	=> !empty( $eventinfo['package']['slug'] ) ? 
																												 $eventinfo['package']['slug'] : '',
																								'dj'		  => ( $eventinfo['dj'] != 'Not Assigned' ?
																												 $eventinfo['dj']->ID : '' )
																								) );
									
									
	
	
									echo '</td>' . "\r\n";
									echo '<th style="width: 20%;"><label for="_mdjm_event_addons">' . __( 'Addons:' ) . '</label></th>' . "\r\n";
									echo '<td style="width: 30%;">' . mdjm_addons_dropdown( array( 
																								'name'		=> 'event_addons',
																								'selected'	=> !empty( $eventinfo['addons'] ) ?
																												$eventinfo['addons'] : '',
																								'dj'		  => $eventinfo['dj'] != 'Not Assigned' ?
																												 $eventinfo['dj']->ID : '',
																								'package'	 => !empty( $eventinfo['package']['slug'] ) ? 
																												 $eventinfo['package']['slug'] : '',
																								) );
									
									
	
	
									echo '</td>' . "\r\n";
								echo '</tr>' . "\r\n";
							}
							
							echo '<tr>' . "\r\n";
								echo '<th style="width: 20%;"><label for="_mdjm_event_notes">' . __( 'Notes:' ) . '</label></th>' . "\r\n";
								echo '<td colspan="3"><textarea name="_mdjm_event_notes" id="_mdjm_event_notes" cols="50" rows="5">' . 
								esc_attr( $eventinfo['notes'] ) . '</textarea></td>' . "\r\n";
							echo '</tr>' . "\r\n";
							
							echo '<tr>' . "\r\n";
								echo '<th style="width: 20%;"><label for="mdjm_reason">' . __( 'Reason for Changes:' ) . '</label></th>' . "\r\n";
								echo '<td colspan="3"><textarea name="mdjm_reason" id="mdjm_reason" cols="50" rows="5" placeholder="' . __( 'If you are making any changes to your event, please enter the reason here' ) . '"></textarea></td>' . "\r\n";
							echo '</tr>' . "\r\n";
							
							echo '<tr>' . "\r\n";
								echo '<td colspan="2"><input type="submit" name="submit" id="submit" value="Submit Changes" /></td>' . "\r\n";
								echo '<td colspan="2"><button type="reset" onclick="location.href=\'' . $mdjm->get_link( MDJM_HOME, true ) . 
								'action=view_event&amp;event_id=' . $post->ID . '\'">' . __( 'Cancel Changes' ) . '</button></td>' . "\r\n";
							echo '</tr>' . "\r\n";
							
							echo '</table>' . "\r\n";
						echo '</div>' . "\r\n"; // End div mdjm-event-table						
					echo '</div>' . "\r\n"; // End div mdjm-event-container	
				echo '</form>' . "\r\n";

			} // edit_event_form
			
			/*
			 * Update the event once the client submits the event updates
			 *
			 *
			 *
			 */
			function update_event()	{
				global $mdjm, $mdjm_posts, $post, $my_mdjm, $clientzone;
				
				$post = get_post( $_POST['event_id'] );
				
				$eventinfo = $mdjm->mdjm_events->event_detail( $post->ID );
				
				if( MDJM_DEBUG == true )
					$GLOBALS['mdjm_debug']->log_it( 'Event ID ' . $post->ID . ' is being updated by ' . $eventinfo['client']->display_name, true );
								
				// Prepare the meta data
				$event_data['_mdjm_event_last_updated_by'] = $my_mdjm['me']->ID;
				
				$event_data['_mdjm_event_name'] = !empty( $_POST['_mdjm_event_name'] ) ? 
					sanitize_text_field( $_POST['_mdjm_event_name'] ) : get_term( $_POST['mdjm_event_type'], 'event-types' )->name;
				
				$event_data['_mdjm_event_package'] = !empty( $_POST['_mdjm_event_package'] ) ? 
					$_POST['_mdjm_event_package'] : '';
				
				$event_data['_mdjm_event_addons'] = !empty( $_POST['event_addons'] ) ? 
					$_POST['event_addons'] : '';
				
				$event_data['_mdjm_event_notes'] = !empty( $_POST['_mdjm_event_notes'] ) ? 
					sanitize_text_field( $_POST['_mdjm_event_notes'] ) : '';
				
				/* -- Assign the event type -- */
				$existing_event_type = wp_get_object_terms( $post->ID, 'event-types' );
				if( !isset( $existing_event_type[0] ) || $existing_event_type[0]->term_id != $_POST['mdjm_event_type'] )	{
					$field_updates[] = 'Event Type changed from ' . $existing_event_type[0]->name . ' to ' . get_term( $_POST['mdjm_event_type'], 'event-types' )->name;
					$mdjm->mdjm_events->mdjm_assign_event_type( $_POST['mdjm_event_type'] );
				}
					
				// Event Times
				if( date( 'H:i', strtotime( $_POST['event_start_hr'] . ':' . $_POST['event_start_min'] ) ) != $eventinfo['start'] )	{
					if( MDJM_DEBUG == true )
						$GLOBALS['mdjm_debug']->log_it( 'Event start time updating to ' . $_POST['event_start_hr'] . ':' . $_POST['event_start_min'] );
						
					$event_data['_mdjm_event_start'] = MDJM_TIME_FORMAT == 'H:i' ? 
						date( 'H:i:s', strtotime( $_POST['event_start_hr'] . ':' . $_POST['event_start_min'] ) ) : 
						date( 'H:i:s', strtotime( $_POST['event_start_hr'] . ':' . $_POST['event_start_min'] . 
						isset( $_POST['event_start_period'] ) ? $_POST['event_start_period'] : '' ) );	
				}
				if( date( 'H:i', strtotime( $_POST['event_finish_hr'] . ':' . $_POST['event_finish_min'] ) ) != $eventinfo['finish'] )	{
					if( MDJM_DEBUG == true )
						$GLOBALS['mdjm_debug']->log_it( 'Event finish time updating to ' . $_POST['event_finish_hr'] . ':' . $_POST['event_finish_min'] );
						
					$event_data['_mdjm_event_finish'] = MDJM_TIME_FORMAT == 'H:i' ? 
						date( 'H:i:s', strtotime( $_POST['event_finish_hr'] . ':' . $_POST['event_finish_min'] ) ) : 
						date( 'H:i:s', strtotime( $_POST['event_finish_hr'] . ':' . $_POST['event_finish_min'] . 
						isset( $_POST['event_finish_period'] ) ? $_POST['event_finish_period'] : '' ) );	
				}
									
				remove_action( 'save_post', array( $mdjm_posts, 'save_custom_post' ), 10, 2 );
				// Update event
				wp_update_post( array( 'ID' => $post->ID ) );
				
				// Update meta
				foreach( $event_data as $event_meta_key => $event_meta_value )	{
					/* -- If we have a value and the key did not exist previously, add it -- */
					if ( !empty( $event_meta_value ) && '' == get_post_meta( $post->ID, $event_meta_key, true ) )	{
						add_post_meta( $post->ID, $event_meta_key, $event_meta_value );
						$field_updates[] = 'Field ' . $event_meta_key . ' added: ' . is_array( $event_meta_value ) ? implode( '<br />', $event_meta_value ) : $event_meta_value;
					}
					/* -- If a value existed, but has changed, update it -- */
					elseif ( !empty( $event_meta_value ) && $event_meta_value != get_post_meta( $post->ID, $event_meta_key, true ) )	{
						update_post_meta( $post->ID, $event_meta_key, $event_meta_value );
						$field_updates[] = 'Field ' . $event_meta_key . ' updated: ' . get_post_meta( $post->ID, $event_meta_key, true ) . ' replaced with ' . $event_meta_value;
					}
						
					/* If there is no new meta value but an old value exists, delete it. */
					elseif ( '' == $event_meta_value && get_post_meta( $post->ID, $event_meta_key, true ) )	{
						delete_post_meta( $post->ID, $event_meta_key, $event_meta_value );
						$field_updates[] = 'Field ' . $event_meta_key . ' updated: ' . get_post_meta( $post->ID, $event_meta_key, true ) . ' removed';
					}
					
					// Log changes to debug file
					if( MDJM_DEBUG == true && !empty( $field_updates ) )
						$GLOBALS['mdjm_debug']->log_it( 'Event Updates Completed     ' . "\r\n" . '| ' .
							implode( "\r\n" . '     | ', $field_updates ) );
					
				}
				
				add_action( 'save_post', array( $mdjm_posts, 'save_custom_post' ), 10, 2 );
				
				// Update journal
				$mdjm->mdjm_events->add_journal( array(
										'user' 			=> $my_mdjm['me']->ID,
										'event'		   => $post->ID,
										'comment_content' => $my_mdjm['me']->display_name . ' updated event - ' . $post->ID . '<br />(' . time() . ')',
										'comment_type' 	=> 'mdjm-journal',
										),
										array(
											'type' 		  => 'update-event',
											'visibility'	=> '1',
										) );
				
				$clientzone->display_notice( '2', 'Your event details have been updated successfully' );
				
			} // update_event
			
			/*
			 * If the client is allowed to edit their event details, provide a link
			 *
			 * @param		str		$text		The text to display for the link
			 *				str		$event_id	The ID of the event
			 * @return		str		Prints the link
			 */
			function edit_event_link( $text, $event_id )	{
				global $mdjm;
				
				if( MDJM_EDIT_EVENT != true )
					return;
				
				$date = get_post_meta( $event_id, '_mdjm_event_date', true );
				
				if( time() > ( $date - ( MDJM_EDIT_EVENT_DISABLE * DAY_IN_SECONDS ) ) )	{
					return '<a href="' . $mdjm->get_link( MDJM_HOME, true ) . 'action=edit_event_detail&event_id=' . $event_id . '">' . 
					$text . '</a>';
				}
					
				else
					return;
				
			} // edit_event_link
			
		} // class ClientZone_Home
		
/* -- Insantiate the ClientZone_Home class if the user is logged in-- */
	global $clientzone;
	
	if( !is_user_logged_in() )
		$clientzone->login();
		
	else
		$mdjm_home = new ClientZone_Home();