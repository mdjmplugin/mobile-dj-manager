<?php
/*
 * class-mdjm-cron.php
 * 10/03/2015
 * @since 1.1.2
 * The main MDJM class
 */
	
	/* -- Build the MDJM_Cron class -- */
	class MDJM_Cron	{		
		/*
		 * __construct
		 * 
		 *
		 *
		 */
		public function __construct()	{
			$this->schedules = get_option( MDJM_SCHEDULES_KEY );
		} // __construct
		
		/*
		 * Register the MDJM scheduled tasks
		 *
		 *
		 *
		 *
		 */
		public function activate()	{
			global $mdjm, $mdjm_settings;
			
			if( !wp_next_scheduled( 'mdjm_hourly_schedule' ) )
				wp_schedule_event( time(), 'hourly', 'mdjm_hourly_schedule' );
			
			if( wp_next_scheduled( 'mdjm_synchronise' ) )
				wp_clear_scheduled_hook( 'mdjm_synchronise' );
				
		} // activate
		
		/*
		 * Determine is a task is active and due to be executed
		 *
		 * @param	arr		$task		The array of the task to be queried
		 *
		 */
		public function task_ready( $task )	{			
			if( empty( $task ) )	{
				if( MDJM_DEBUG == true )
					$GLOBALS['mdjm_debug']->log_it( 'ERROR: No task name was passed within ' . __METHOD__, true );
				
				return false;
			}
			
			// Check for active task
			if( $task['active'] != 'Y' && $task['active'] != true )	{
				if( MDJM_DEBUG == true )
					$GLOBALS['mdjm_debug']->log_it( 'The task ' . $task['name'] . ' is not active' );
					
				return false;
			}
			
			// Check if scheduled to run
			if( !isset( $task['nextrun'] ) || $task['nextrun'] <= time() || $task['nextrun'] == 'Today'
						|| $task['nextrun'] == 'Next Week' || $task['nextrun'] == 'Next Month'
						|| $task['nextrun'] == 'Next Year' )	{
						
				return true;
			}
			else	{
				if( MDJM_DEBUG == true )
					$GLOBALS['mdjm_debug']->log_it( 'SKIPPING CRON TASK: ' . $task['name'] . '. Next due ' . date( 'd/m/Y H:i:s', $task['nextrun'] ), true );	
			}
						
			return false;	
			
		} // task_ready
		
		/*
		 * Execute the schedules tasks which are due to be run
		 *
		 *
		 *
		 */
		public function execute_cron()	{
			global $mdjm, $mdjm_settings;
			
			/* -- Make sure the upload playlist is set correctly -- */
			if( isset( $mdjm_settings['playlist']['upload_playlists'] ) )
				$this->mdjm['upload-playlists']['active'] = $mdjm_settings['playlist']['upload_playlists'];
				
			/* -- Loop through each of the scheduled tasks and execute as necessary -- */
			foreach( $this->schedules as $task )	{
				if( $task['active'] != 'Y' )
					continue;
					
				/* Only execute active tasks */
				if( $this->task_ready( $task ) )	{
					if( MDJM_DEBUG == true )
						$GLOBALS['mdjm_debug']->log_it( 'CRON TASK: ' . $task['name'] . ' is due to run (' . date( 'd/m/Y H:i:s', $task['nextrun'] ) . ')' );
					
					$func = $task['function'];
					
					// Execute the cron job
					if( method_exists( $this, $func ) )
						$this->$func();
				}
			} // foreach( $mdjm_schedules as $task )
				
		} // execute_cron
		
		/*
		 * Submit the playlist entries to the MDJM servers upon event completion
		 *
		 *
		 *
		 */
		public function submit_playlist()	{
			global $mdjm, $wpdb;
			
			if( MDJM_DEBUG == true )
				$GLOBALS['mdjm_debug']->log_it( '*** Starting the Playlist Upload ***', true );
			
			$cron_start = microtime(true);
			
			/* Retrieve playlist entries not yet transferred */
			$maxrows = 50;
			
			$query = "SELECT * FROM `" . MDJM_PLAYLIST_TABLE . "` WHERE `upload_procedure` = '0' ORDER BY `event_id` LIMIT " . $maxrows;
			$playlist = $wpdb->get_results( $query );
			$rows = $wpdb->num_rows;
			
			// No data to transfer
			if( $rows == 0 )	{
				if( MDJM_DEBUG == true )
					$GLOBALS['mdjm_debug']->log_it( 'No playlist entries to upload' );	
			}
			// We have data to process
			else	{
				$i = 0;
				$x = 0;
				if( MDJM_DEBUG == true )
					$GLOBALS['mdjm_debug']->log_it( 'Beginnning playlist upload' );
					
				/* -- Loop through each playlist record, upload to MDJM and update table row -- */
				foreach( $playlist as $record )	{
					// Get the event details to ensure it is completed
					$event_status = get_post_status( $record->event_id );
					
					// If the event is not completed, go to the next record
					if( $event_status != 'mdjm-completed' )
						$invalid = true;
					
					// Get the event post data
					$event = get_post( $record->event_id );
					
					if( !empty( $event ) )	{
						// Build the rpc call
						$event_types = wp_get_object_terms( $event->ID, 'event-types' );
						$event_type = isset( $event_types[0]->name ) ? $event_types[0]->name : 'Undefined';
						$event_date = get_post_meta( $event->ID, '_mdjm_event_date', true );
						
						$rpc = 'a=' . esc_attr( urlencode( stripslashes( $record->artist ) ) ) . '&s=' . 
							esc_attr( urlencode( stripslashes( $record->song ) ) ) . '&et=' . esc_attr( urlencode( $event_type ) ) . '&ed=' . 
							date( 'Y-m-d', strtotime( $event_date ) ) . '&da=' . $record->date_added . '&c=' . urlencode( MDJM_COMPANY ) . 
							'&url=' . urlencode( get_site_url() );
							
						if( MDJM_DEBUG == true )
							$GLOBALS['mdjm_debug']->log_it( 'Sending RPC string http://api.mydjplanner.co.uk/mdjm/pl/pl.php?' . $rpc );
						
						// Retrieve the response
						$response = wp_remote_retrieve_body( wp_remote_get( 'http://api.mydjplanner.co.uk/mdjm/pl/pl.php?' . $rpc ) );
						
						if( MDJM_DEBUG == true )
							$GLOBALS['mdjm_debug']->log_it( 'Response received ' . $response );
						
						// Update the playlist record with the timestamp of the upload
						if( $response )	{ // Success
							if( MDJM_DEBUG == true )
								$GLOBALS['mdjm_debug']->log_it( ucfirst( $record->song ) . ' by ' . ucfirst( $record->artist ) . ' successfully uploaded' );
							
							$update = $wpdb->update( 
												MDJM_PLAYLIST_TABLE,
												array( 'date_to_mdjm' => date( 'Y-m-d H:i:s', $response ),
													   'upload_procedure' => '1' ),
												array( 'id' => $record->id ) );
												
							$i++;
						} // if( $response )
						else	{ // Failrue
							if( MDJM_DEBUG == true )
								$GLOBALS['mdjm_debug']->log_it( 'ERROR: ' . ucfirst( $record->song ) . ' by ' . ucfirst( $record->artist ) . ' could not be uploaded. ' . $wpdb->print_error() );	
						}
					} // if( !empty( $event ) )
					// If no event exists update the record so we don't retry if
					else	{
						$x++;
						
						$update = $wpdb->update( 
												MDJM_PLAYLIST_TABLE,
												array( 'upload_procedure' => '1' ),
												array( 'id' => $record->id ) );	
					}
				} // End foreach
				
				if( MDJM_DEBUG == true )	{
					$GLOBALS['mdjm_debug']->log_it( $i . _n( ' record', ' records', $i ) . ' uploaded successfully' );
					if( $x > 0 )
						$GLOBALS['mdjm_debug']->log_it( $x . _n( ' record', ' records', $x ) . ' had no event posts' );
					$GLOBALS['mdjm_debug']->log_it( '*** Completed the Playlist Upload ***', true );
				}
			}
			
			$cron_end = microtime(true);
			
			// Prepare next run time
			$this->update_nextrun( 'upload-playlists' );
		} // submit_playlist
		
		/*
		 * Mark events as completed if the event date has passed
		 *
		 *
		 *
		 */
		public function complete_event()	{
			global $mdjm, $mdjm_posts, $mdjm_settings;
			
			if( MDJM_DEBUG == true )
				$GLOBALS['mdjm_debug']->log_it( '*** Starting the Complete Events task ***', true );
			
			$cron_start = microtime(true);
			
			$args = array(
						'posts_per_page'	=> -1,
						'post_type'		 => MDJM_EVENT_POSTS,
						'post_status'	   => 'mdjm-approved',
						'meta_query'		=> array(
													'key'		=> '_mdjm_event_date',
													'compare'	=> '<',
													'value'	  => date( 'Y-m-d' ),
												),
						);
									
			$events = get_posts( $args );
			
			$notify = array();
			$x = 0;
			
			if( count( $events ) > 0 )	{ // Enquiries to process
				if( MDJM_DEBUG == true )
					$GLOBALS['mdjm_debug']->log_it( count( $events ) . ' ' . _n( 'event', 'events', count( $events ) ) . ' to mark as completed' );
				
				remove_action( 'save_post', array( $mdjm_posts, 'save_custom_post' ), 10, 2 );
				
				/* -- Loop through the enquiries and update as completed -- */	
				foreach( $events as $event )	{
					$cronned = get_post_meta( $event->ID, '_mdjm_event_tasks', true );
					if( !empty( $cronned ) && $cronned != '' )
						$cron_update = json_decode( $cronned, true );
					
					if( array_key_exists( 'complete-events', $cron_update ) )	{// Task has already run for this event
						$GLOBALS['mdjm_debug']->log_it( 'This task has already run for this event (' . $event->ID . ')' );
						continue;
					}
						
					if( !is_array( $cron_update ) ) $cron_update = array();
					
					$cron_update[$this->schedules['complete-events']['slug']] = time();
					
					wp_update_post( array( 'ID' => $event->ID, 'post_status' => 'mdjm-completed' ) );
					
					update_post_meta( $event->ID, '_mdjm_event_last_updated_by', 0 );
					update_post_meta( $event->ID, '_mdjm_event_tasks', json_encode( $cron_update ) );
					
					/* -- Update Journal -- */
					if( MDJM_JOURNAL == true )	{
						if( MDJM_DEBUG == true )
							$GLOBALS['mdjm_debug']->log_it( '	-- Adding journal entry' );
								
						$mdjm->mdjm_events->add_journal( array(
									'user' 			=> 1,
									'event'		   => $event->ID,
									'comment_content' => 'Event marked as completed via Scheduled Task <br /><br />' . time(),
									'comment_type' 	=> 'mdjm-journal',
									),
									array(
										'type' 		  => 'update-event',
										'visibility'	=> '1',
									) );
					} // End if( MDJM_JOURNAL == true )
					else	{
						if( MDJM_DEBUG == true )
							$GLOBALS['mdjm_debug']->log_it( '	-- Journalling is disabled' );	
					}
					
					$notify_dj = isset( $this->schedules['complete-events']['options']['notify_dj'] ) ? $this->schedules['complete-events']['options']['notify_dj'] : '';
					$notify_admin = isset( $this->schedules['complete-events']['options']['notify_admin'] ) ? $this->schedules['complete-events']['options']['notify_admin'] : '';
					
					$client = get_post_meta( $event->ID, '_mdjm_event_client', true );
					$dj = get_post_meta( $event->ID, '_mdjm_event_dj', true );
					$event_date = get_post_meta( $event->ID, '_mdjm_event_date', true );
					
					$event_dj = !empty( $dj ) ? get_userdata( $dj ) : 'DJ not found';
					$event_client = !empty( $client ) ? get_userdata( $client ) : 'Client not found';
					
					$venue_post_id = get_post_meta( $event->ID, '_mdjm_event_venue_id', true );
					
					$event_venue = $mdjm->mdjm_events->mdjm_get_venue_details( $venue_post_id, $event->ID );	
				
					/* Prepare admin notification email data array */
					if( !empty( $notify_admin ) && $notify_admin == 'Y' )	{
						if( MDJM_DEBUG == true )
							$GLOBALS['mdjm_debug']->log_it( '	-- Admin notifications are enabled' );
							
						if( !isset( $notify['admin'] ) || !is_array( $notify['admin'] ) ) $notify['admin'] = array();
						
						$notify['admin'][$event->ID] = array(
																'id'		=> $event->ID,
																'client'	=> $event_client->display_name,
																'venue'	 => !empty( $event_venue['name'] ) ? 
																	$event_venue['name'] : 'No Venue Set',
																'djinfo'	=> $event_dj,
																'date'	  => !empty( $event_date ) ? date( "d M Y", strtotime( $event_date ) ) : 'Date not found',
																);
					} // End if( !empty( $notify_admin ) && $notify_admin == 'Y' )
					
					/* Prepare DJ notification email data array */
					if( !empty( $notify_dj ) && $notify_dj == 'Y' )	{
						if( MDJM_DEBUG == true )
							$GLOBALS['mdjm_debug']->log_it( '	-- DJ notifications are enabled' );
							
						if( !isset( $notify['dj'] ) || !is_array( $notify['dj'] ) ) $notify['dj'] = array();
						$notify['dj'][$dj] = array();
						$notify['dj'][$dj][$event->ID] = array(
																'id'		=> $event->id,
																'client'	=> $event_client->display_name,
																'venue'	 => !empty( $event_venue['name'] ) ? 
																	$event_venue['name'] : 'No Venue Set',
																'djinfo'	=> $event_dj,
																'date'	  => !empty( $event_date ) ? date( "d M Y", strtotime( $event_date ) ) : 'Date not found',
																);
							
					} // End if( !empty( $notify_dj ) && $notify_dj == 'Y' )
					
					$x++;
					
				} // End foreach
				$cron_end = microtime(true);
								
				/* -- Prepare the Admin notification email -- */
				if( !empty( $notify_admin ) && $notify_admin == 'Y' )	{
					$notify_email_args = array(
											'data'		=> $notify['admin'],
											'taskinfo'	=> $this->schedules['complete-events'],
											'start'	   => $cron_start,
											'end'		 => $cron_end,
											'total'	   => $x,
										); // $notify_email_args
										
					$mdjm->send_email( array(
											'content'	=> $this->notification_content( $notify_email_args ),
											'to'		 => $mdjm_settings['email']['system_email'],
											'subject'	=> sanitize_text_field( $this->schedules['complete-events']['options']['email_subject'] ),
											'journal'	=> false,
											'html'	   => false,
											'cc_admin'   => false,
											'cc_dj'	  => false,
											'filter'	 => false,
											'log_comm'   => false,
											) );
				}// if( !empty( $notify_admin ) && $notify_admin == 'Y' )	{
				
				/* -- Prepare the DJ notification email -- */
				if( !empty( $notify_dj ) && $notify_dj == 'Y' )	{
					foreach( $notify['dj'] as $notify_dj )	{
						foreach( $notify_dj as $dj )	{
							$notify_email_args = array(
													'data'		=> $notify_dj,
													'taskinfo'	=> $this->schedules['complete-events'],
													'start'	   => $cron_start,
													'end'		 => $cron_end,
													'total'	   => $x,
												); // $notify_email_args
																			
							$mdjm->send_email( array(
													'content'	=> $this->notification_content( $notify_email_args ),
													'to'		 => $dj->ID,
													'subject'	=> sanitize_text_field( $this->schedules['complete-events']['options']['email_subject'] ),
													'journal'	=> false,
													'html'	   => false,
													'cc_admin'   => false,
													'cc_dj'	  => false,
													'filter'   => false,
													'log_comm'   => false,
													) );
						} // foreach( $notify_dj as $dj )
					} // foreach( $notify['dj'] as $notify_dj )
				} // if( !empty( $notify_dj ) && $notify_dj == 'Y' )
				
				add_action( 'save_post', array( $mdjm_posts, 'save_custom_post' ), 10, 2 );
			} // if( count( $events ) > 0 )
			
			else	{
				if( MDJM_DEBUG == true )
					$GLOBALS['mdjm_debug']->log_it( 'No events to mark as complete' );	
			}
						
			// Prepare next run time
			$this->update_nextrun( 'complete-events' );
			
			if( MDJM_DEBUG == true )
				$GLOBALS['mdjm_debug']->log_it( '*** Completed the Complete Events task ***', true );
			
		} // complete_event
		
		/*
		 * Fail event enquiries that have been outstanding longer than the specified time
		 *
		 *
		 *
		 */
		public function fail_enquiry()	{
			global $mdjm, $mdjm_posts, $mdjm_settings;
			
			if( MDJM_DEBUG == true )
				$GLOBALS['mdjm_debug']->log_it( '*** Starting the Fail Enquiry task ***', true );
			
			$cron_start = microtime(true);
			
			$expired = date( 'Y-m-d', strtotime( "-" . $this->schedules['fail-enquiry']['options']['age'] ) );
			
			$args = array(
						'posts_per_page'	=> -1,
						'post_type'		 => MDJM_EVENT_POSTS,
						'post_status'	   => 'mdjm-enquiry',
						'date_query'		=> array(
												'before'	=> $expired,
												),
						);
			
			// Retrieve expired enquiries
			$enquiries = get_posts( $args );
			
			$notify = array();
			$x = 0;
			
			if( count( $enquiries ) > 0 )	{ // Enquiries to process
				if( MDJM_DEBUG == true )
					$GLOBALS['mdjm_debug']->log_it( count( $enquiries ) . ' ' . _n( 'enquiry', 'enquiries', count( $enquiries ) ) . ' to expire' );
				
				remove_action( 'save_post', array( $mdjm_posts, 'save_custom_post' ), 10, 2 );
				
				/* -- Loop through the enquiries and update as failed -- */	
				foreach( $enquiries as $enquiry )	{
					$cronned = get_post_meta( $event->ID, '_mdjm_event_tasks', true );
					if( !empty( $cronned ) && $cronned != '' )
						$cron_update = json_decode( $cronned, true );
					
					if( array_key_exists( 'request-deposit', $cron_update ) ) // Task has already run for this event
						continue;
						
					if( !is_array( $cron_update ) ) $cron_update = array();
					
					$cron_update[$this->schedules['fail-enquiry']['slug']] = time();
					
					wp_update_post( array( 'ID' => $enquiry->ID, 'post_status' => 'mdjm-failed' ) );
					
					update_post_meta( $enquiry->ID, '_mdjm_event_last_updated_by', 0 );
					update_post_meta( $enquiry->ID, '_mdjm_event_tasks', json_encode( $cron_update ) );
					
					/* -- Update Journal -- */
					if( MDJM_JOURNAL == true )	{
						if( MDJM_DEBUG == true )
							$GLOBALS['mdjm_debug']->log_it( '	-- Adding journal entry' );
								
						$mdjm->mdjm_events->add_journal( array(
									'user' 			=> 1,
									'event'		   => $enquiry->ID,
									'comment_content' => 'Enquiry marked as lost via Scheduled Task <br /><br />' . time(),
									'comment_type' 	=> 'mdjm-journal',
									),
									array(
										'type' 		  => 'update-event',
										'visibility'	=> '1',
									) );
					} // End if( MDJM_JOURNAL == true )
					else	{
						if( MDJM_DEBUG == true )
							$GLOBALS['mdjm_debug']->log_it( '	-- Journalling is disabled' );	
					}
					
					$notify_dj = isset( $this->schedules['fail-enquiry']['options']['notify_dj'] ) ? $this->schedules['fail-enquiry']['options']['notify_dj'] : '';
					$notify_admin = isset( $this->schedules['fail-enquiry']['options']['notify_admin'] ) ? $this->schedules['fail-enquiry']['options']['notify_admin'] : '';
					
					$client = get_post_meta( $enquiry->ID, '_mdjm_event_client', true );
					$dj = get_post_meta( $enquiry->ID, '_mdjm_event_dj', true );
					$event_date = get_post_meta( $enquiry->ID, '_mdjm_event_date', true );
					
					$event_dj = !empty( $dj ) ? get_userdata( $dj ) : 'DJ not found';
					$event_client = !empty( $client ) ? get_userdata( $client ) : 'Client not found';
					
					$venue_post_id = get_post_meta( $enquiry->ID, '_mdjm_event_venue_id', true );
					
					$event_venue = $mdjm->mdjm_events->mdjm_get_venue_details( $venue_post_id, $enquiry->ID );	
				
					/* Prepare admin notification email data array */
					if( !empty( $notify_admin ) && $notify_admin == 'Y' )	{
						if( MDJM_DEBUG == true )
							$GLOBALS['mdjm_debug']->log_it( '	-- Admin notifications are enabled' );
							
						if( !isset( $notify['admin'] ) || !is_array( $notify['admin'] ) ) $notify['admin'] = array();
						
						$notify['admin'][$enquiry->ID] = array(
																'id'		=> $enquiry->ID,
																'client'	=> $event_client->display_name,
																'venue'	 => !empty( $event_venue['name'] ) ? 
																	$event_venue['name'] : 'No Venue Set',
																'djinfo'	=> $event_dj,
																'date'	  => !empty( $event_date ) ? date( "d M Y", strtotime( $event_date ) ) : 'Date not found',
																);
					} // End if( !empty( $notify_admin ) && $notify_admin == 'Y' )
					
					/* Prepare DJ notification email data array */
					if( !empty( $notify_dj ) && $notify_dj == 'Y' )	{
						if( MDJM_DEBUG == true )
							$GLOBALS['mdjm_debug']->log_it( '	-- DJ notifications are enabled' );
							
						if( !isset( $notify['dj'] ) || !is_array( $notify['dj'] ) ) $notify['dj'] = array();
						$notify['dj'][$dj] = array();
						$notify['dj'][$dj][$enquiry->ID] = array(
																'id'		=> $enquiry->id,
																'client'	=> $event_client->display_name,
																'venue'	 => !empty( $event_venue['name'] ) ? 
																	$event_venue['name'] : 'No Venue Set',
																'djinfo'	=> $event_dj,
																'date'	  => !empty( $event_date ) ? date( "d M Y", strtotime( $event_date ) ) : 'Date not found',
																);
							
					} // End if( !empty( $notify_dj ) && $notify_dj == 'Y' )
					
					$x++;
				} // End foreach
				
				$cron_end = microtime(true);
								
				/* -- Prepare the Admin notification email -- */
				if( !empty( $notify_admin ) && $notify_admin == 'Y' )	{
					$notify_email_args = array(
											'data'		=> $notify['admin'],
											'taskinfo'	=> $this->schedules['fail-enquiry'],
											'start'	   => $cron_start,
											'end'		 => $cron_end,
											'total'	   => $x,
										); // $notify_email_args
					$content = $this->notification_content( $notify_email_args );
										
					$mdjm->send_email( array(
											'content'	=> $content,
											'to'		 => $mdjm_settings['email']['system_email'],
											'subject'	=> sanitize_text_field( $this->schedules['fail-enquiry']['options']['email_subject'] ),
											'journal'	=> false,
											'html'	   => false,
											'cc_admin'   => false,
											'cc_dj'	  => false,
											'filter'   => false,
											'log_comm'   => false,
											) );
				}// if( !empty( $notify_admin ) && $notify_admin == 'Y' )	{
				
				/* -- Prepare the DJ notification email -- */
				if( !empty( $notify_dj ) && $notify_dj == 'Y' )	{
					foreach( $notify['dj'] as $notify_dj )	{
						foreach( $notify_dj as $dj )	{
							$notify_email_args = array(
													'data'		=> $notify_dj,
													'taskinfo'	=> $this->schedules['fail-enquiry'],
													'start'	   => $cron_start,
													'end'		 => $cron_end,
													'total'	   => $x,
												); // $notify_email_args
							$content = $this->notification_content( $notify_email_args );
							
							$mdjm->send_email( array(
													'content'	=> $content,
													'to'		 => $dj->ID,
													'subject'	=> sanitize_text_field( $this->schedules['fail-enquiry']['options']['email_subject'] ),
													'journal'	=> false,
													'html'	   => false,
													'cc_admin'   => false,
													'cc_dj'	  => false,
													'filter'   => false,
													'log_comm'   => false,
													) );
						} // foreach( $notify_dj as $dj )
					} // foreach( $notify['dj'] as $notify_dj )
				} // if( !empty( $notify_dj ) && $notify_dj == 'Y' )
				
				add_action( 'save_post', array( $mdjm_posts, 'save_custom_post' ), 10, 2 );
			} // if( count( $enquiries ) > 0 )
			
			else	{
				if( MDJM_DEBUG == true )
					$GLOBALS['mdjm_debug']->log_it( 'No enquiries to process as failed' );	
			}
						
			// Prepare next run time
			$this->update_nextrun( 'fail-enquiry' );
			
			if( MDJM_DEBUG == true )
				$GLOBALS['mdjm_debug']->log_it( '*** Completed the Fail Enquiry task ***', true );
			
		} // fail_enquiry
		
		/*
		 * Request deposits from clients whose events are within the specified timeframe
		 * and where the deposit is still outstanding using defined email template
		 *
		 *
		 */
		public function request_deposit()	{
			global $mdjm, $mdjm_posts, $mdjm_settings;
			
			if( MDJM_DEBUG == true )
				$GLOBALS['mdjm_debug']->log_it( '*** Starting the Request Deposit task ***', true );
			
			$cron_start = microtime(true);
						
			$args = array(
						'posts_per_page'	=> -1,
						'post_type'		 => MDJM_EVENT_POSTS,
						'post_status'	   => 'mdjm-approved',
						'meta_query'		=> array(
												'relation'	=> 'AND',
													array(
														'key'		=> '_mdjm_event_deposit_status',
														'compare'	=> '==',
														'value'	  => 'Due',
													),
													array(
														'key'		=> '_mdjm_event_deposit',
														'value'	  => '0.00',
														'compare'	=> '>',
													),
													array(
														'key'		=> '_mdjm_event_tasks',
														'value'	  => 'request-deposit',
														'compare'	=> 'NOT IN',
													),
												),
						);
			
			// Retrieve events for which deposit is due
			$events = get_posts( $args );
			
			$notify = array();
			$x = 0;
			
			if( count( $events ) > 0 )	{ // Events to process
				if( MDJM_DEBUG == true )
					$GLOBALS['mdjm_debug']->log_it( count( $events ) . ' ' . _n( 'event', 'events', count( $events ) ) . ' where the deposit needs to be requested' );
				
				remove_action( 'save_post', array( $mdjm_posts, 'save_custom_post' ), 10, 2 );
				
				/* -- Loop through the enquiries and update as completed -- */	
				foreach( $events as $event )	{
					$cronned = get_post_meta( $event->ID, '_mdjm_event_tasks', true );
					if( !empty( $cronned ) && $cronned != '' )
						$cron_update = json_decode( $cronned, true );
					
					if( array_key_exists( 'request-deposit', $cron_update ) ) // Task has already run for this event
						continue;
						
					if( !is_array( $cron_update ) ) $cron_update = array();
					
					$cron_update[$this->schedules['request-deposit']['slug']] = time();
					
					wp_update_post( array( 'ID' => $event->ID, 'post_modified' => date( 'Y-m-d H:i:s' ) ) );
					
					update_post_meta( $event->ID, '_mdjm_event_last_updated_by', 0 );
					update_post_meta( $event->ID, '_mdjm_event_tasks', json_encode( $cron_update ) );
					
					/* -- Update Journal -- */
					if( MDJM_JOURNAL == true )	{
						if( MDJM_DEBUG == true )
							$GLOBALS['mdjm_debug']->log_it( '	-- Adding journal entry' );
								
						$mdjm->mdjm_events->add_journal( array(
									'user' 			=> 1,
									'event'		   => $event->ID,
									'comment_content' => MDJM_DEPOSIT_LABEL . ' request Scheduled Task executed<br /><br />' . time(),
									'comment_type' 	=> 'mdjm-journal',
									),
									array(
										'type' 		  => 'added-note',
										'visibility'	=> '0',
									) );
					} // End if( MDJM_JOURNAL == true )
					else	{
						if( MDJM_DEBUG == true )
							$GLOBALS['mdjm_debug']->log_it( '	-- Journalling is disabled' );	
					}
					
					$notify_dj = isset( $this->schedules['request-deposit']['options']['notify_dj'] ) ? $this->schedules['request-deposit']['options']['notify_dj'] : '';
					$notify_admin = isset( $this->schedules['request-deposit']['options']['notify_admin'] ) ? $this->schedules['request-deposit']['options']['notify_admin'] : '';
					
					$client = get_post_meta( $event->ID, '_mdjm_event_client', true );
					$dj = get_post_meta( $event->ID, '_mdjm_event_dj', true );
					$event_date = get_post_meta( $event->ID, '_mdjm_event_date', true );
					
					$event_dj = !empty( $dj ) ? get_userdata( $dj ) : 'DJ not found';
					$event_client = !empty( $client ) ? get_userdata( $client ) : 'Client not found';
					$event_deposit = get_post_meta( $event->ID, '_mdjm_event_deposit', true );
					$event_cost = get_post_meta( $event->ID, '_mdjm_event_cost', true );
					
					$venue_post_id = get_post_meta( $event->ID, '_mdjm_event_venue_id', true );
					
					$event_venue = $mdjm->mdjm_events->mdjm_get_venue_details( $venue_post_id, $event->ID );	
				
					$contact_client = ( isset( $this->schedules['request-deposit']['options']['email_client'] ) 
						&& $this->schedules['request-deposit']['options']['email_client'] == 'Y'  ? true : false );
						
					$email_template = ( isset( $this->schedules['request-deposit']['options']['email_template'] ) && 
						$mdjm_posts->post_exists( $this->schedules['request-deposit']['options']['email_template'] ) ? 
						$this->schedules['request-deposit']['options']['email_template'] : false );
					
					/* -- Client Deposit Request Email -- */
					if( !empty( $contact_client ) && !empty( $email_template ) )	{ // Email the client
						if( MDJM_DEBUG == true )
							$GLOBALS['mdjm_debug']->log_it( 'Task ' . $this->schedules['request-deposit']['name'] . ' is configured to notify client that deposit is due' );
							
						$request = $mdjm->send_email( array( 
									'content'	=> $email_template,
									'to'		 => $event_client->ID,
									'from'	   => $mdjm_settings['templates']['enquiry_from'] == 'dj' ? $event_dj->ID : 0,
									'journal'	=> 'email-client',
									'event_id'   => $event->ID,
									'html'	   => true,
									'cc_dj'	  => isset( $mdjm_settings['email']['bcc_dj_to_client'] ) ? true : false,
									'cc_admin'   => isset( $mdjm_settings['email']['bcc_admin_to_client'] ) ? true : false,
									'source'	 => __( 'Request Deposit Scheduled Task' ),
								) );
						if( $request )	{
							if( MDJM_DEBUG == true )
								 $GLOBALS['mdjm_debug']->log_it( '	-- Deposit request sent to ' . $event_client->display_name . '. ' . $request . ' ID ' );
						}
						else	{
							if( MDJM_DEBUG == true )
								 $GLOBALS['mdjm_debug']->log_it( '	ERROR: Deposit request was not sent' );
						}
					}
					else	{
						if( MDJM_DEBUG == true )
							$GLOBALS['mdjm_debug']->log_it( 'Task ' . $this->schedules['request-deposit']['name'] . ' is not configured to notify client' );	
					}
				
					/* Prepare admin notification email data array */
					if( !empty( $notify_admin ) && $notify_admin == 'Y' )	{
						if( MDJM_DEBUG == true )
							$GLOBALS['mdjm_debug']->log_it( '	-- Admin notifications are enabled' );
							
						if( !isset( $notify['admin'] ) || !is_array( $notify['admin'] ) ) $notify['admin'] = array();
						
						$notify['admin'][$event->ID] = array(
																'id'		=> $event->ID,
																'client'	=> $event_client->display_name,
																'deposit'   => !empty( $event_deposit ) ? 
																				$event_deposit : '0',
																'cost'   => !empty( $event_cost ) ? 
																				$event_cost : '0',
																'venue'	 => !empty( $event_venue['name'] ) ? 
																	$event_venue['name'] : 'No Venue Set',
																'djinfo'	=> $event_dj,
																'date'	  => !empty( $event_date ) ? date( "d M Y", strtotime( $event_date ) ) : 'Date not found',
																);
					} // End if( !empty( $notify_admin ) && $notify_admin == 'Y' )
					
					/* Prepare DJ notification email data array */
					if( !empty( $notify_dj ) && $notify_dj == 'Y' && dj_can( 'see_deposit' ) )	{
						if( MDJM_DEBUG == true )
							$GLOBALS['mdjm_debug']->log_it( '	-- DJ notifications are enabled' );
							
						if( !isset( $notify['dj'] ) || !is_array( $notify['dj'] ) ) $notify['dj'] = array();
						$notify['dj'][$dj] = array();
						$notify['dj'][$dj][$event->ID] = array(
																'id'		=> $event->id,
																'client'	=> $event_client->display_name,
																'deposit'   => !empty( $event_deposit ) ? 
																				$event_deposit : '0',
																'cost'   => !empty( $event_cost ) ? 
																				$event_cost : '0',
																'venue'	 => !empty( $event_venue['name'] ) ? 
																	$event_venue['name'] : 'No Venue Set',
																'djinfo'	=> $event_dj,
																'date'	  => !empty( $event_date ) ? date( "d M Y", strtotime( $event_date ) ) : 'Date not found',
																);
							
					} // End if( !empty( $notify_dj ) && $notify_dj == 'Y' )
					
					$x++;
					
				} // End foreach
				$cron_end = microtime(true);
								
				/* -- Prepare the Admin notification email -- */
				if( !empty( $notify_admin ) && $notify_admin == 'Y' )	{
					$notify_email_args = array(
											'data'		=> $notify['admin'],
											'taskinfo'	=> $this->schedules['request-deposit'],
											'start'	   => $cron_start,
											'end'		 => $cron_end,
											'total'	   => $x,
										); // $notify_email_args
										
					$mdjm->send_email( array(
											'content'	=> $this->notification_content( $notify_email_args ),
											'to'		 => $mdjm_settings['email']['system_email'],
											'subject'	=> MDJM_DEPOSIT_LABEL . ' Request Scheduled Task Completed - ' . MDJM_APP,
											'journal'	=> false,
											'html'	   => false,
											'cc_admin'   => false,
											'cc_dj'	  => false,
											'filter'	 => false,
											'log_comm'   => false,
											) );
				}// if( !empty( $notify_admin ) && $notify_admin == 'Y' )	{
				
				/* -- Prepare the DJ notification email -- */
				if( !empty( $notify_dj ) && $notify_dj == 'Y' && dj_can( 'see_deposit' ) )	{
					foreach( $notify['dj'] as $notify_dj )	{
						foreach( $notify_dj as $dj )	{
							$notify_email_args = array(
													'data'		=> $notify_dj,
													'taskinfo'	=> $this->schedules['request-deposit'],
													'start'	   => $cron_start,
													'end'		 => $cron_end,
													'total'	   => $x,
												); // $notify_email_args
																			
							$mdjm->send_email( array(
													'content'	=> $this->notification_content( $notify_email_args ),
													'to'		 => $dj->ID,
													'subject'	=> MDJM_DEPOSIT_LABEL . ' Request Scheduled Task Completed - ' . MDJM_APP,
													'journal'	=> false,
													'html'	   => false,
													'cc_admin'   => false,
													'cc_dj'	  => false,
													'filter'   => false,
													'log_comm'   => false,
													) );
						} // foreach( $notify_dj as $dj )
					} // foreach( $notify['dj'] as $notify_dj )
				} // if( !empty( $notify_dj ) && $notify_dj == 'Y' )
				
				add_action( 'save_post', array( $mdjm_posts, 'save_custom_post' ), 10, 2 );
			} // if( count( $events ) > 0 )
			else	{
				if( MDJM_DEBUG == true )
					$GLOBALS['mdjm_debug']->log_it( 'No deposits are due' );	
			}
						
			// Prepare next run time
			$this->update_nextrun( 'request-deposit' );
			
			if( MDJM_DEBUG == true )
				$GLOBALS['mdjm_debug']->log_it( '*** Completed the Request Deposit task ***', true );
			
		} // request_deposit
		
		/*
		 * Request balance payment from clients whose events are within the specified timeframe
		 * using defined email template
		 *
		 *
		 */
		public function balance_reminder()	{
			global $mdjm, $mdjm_posts, $mdjm_settings;
			
			if( MDJM_DEBUG == true )
				$GLOBALS['mdjm_debug']->log_it( '*** Starting the Request Balance task ***', true );
			
			$cron_start = microtime(true);
			
			/* -- Calculate the time period for which the task should run -- */
			$due_date = date( 'Y-m-d', strtotime( "-" . $this->schedules['balance-reminder']['options']['age'] ) );
						
			$args = array(
						'posts_per_page'	=> -1,
						'post_type'		 => MDJM_EVENT_POSTS,
						'post_status'	   => 'mdjm-approved',
						'meta_query'		=> array(
												'relation'	=> 'AND',
													array(
														'key'		=> '_mdjm_event_date',
														'compare'	=> '>=',
														'value'	  => $due_date,
														'type'	   => 'date',
													),
													array(
														'key'		=> '_mdjm_event_balance_status',
														'compare'	=> '==',
														'value'	  => 'Due',
													),
													array(
														'key'		=> '_mdjm_event_cost',
														'value'	  => '0.00',
														'compare'	=> '>',
													),
													array(
														'key'		=> '_mdjm_event_tasks',
														'value'	  => 'balance-reminder',
														'compare'	=> 'NOT IN',
													),
												),
						);
			
			// Retrieve events for which balance is due
			$events = get_posts( $args );
			
			$notify = array();
			$x = 0;
			
			if( count( $events ) > 0 )	{ // Events to process
				if( MDJM_DEBUG == true )
					$GLOBALS['mdjm_debug']->log_it( count( $events ) . ' ' . _n( 'event', 'events', count( $events ) ) . ' where the balance is due' );
				
				remove_action( 'save_post', array( $mdjm_posts, 'save_custom_post' ), 10, 2 );
				
				/* -- Loop through the enquiries and update as completed -- */	
				foreach( $events as $event )	{
					$cronned = get_post_meta( $event->ID, '_mdjm_event_tasks', true );
					if( !empty( $cronned ) && $cronned != '' )
						$cron_update = json_decode( $cronned, true );
					
					if( array_key_exists( 'balance-reminder', $cron_update ) ) // Task has already run for this event
						continue;
						
					if( !is_array( $cron_update ) ) $cron_update = array();
					
					$cron_update[$this->schedules['balance-reminder']['slug']] = time();
					
					wp_update_post( array( 'ID' => $event->ID, 'post_modified' => date( 'Y-m-d H:i:s' ) ) );
					
					update_post_meta( $event->ID, '_mdjm_event_last_updated_by', 0 );
					update_post_meta( $event->ID, '_mdjm_event_tasks', json_encode( $cron_update ) );
					
					/* -- Update Journal -- */
					if( MDJM_JOURNAL == true )	{
						if( MDJM_DEBUG == true )
							$GLOBALS['mdjm_debug']->log_it( '	-- Adding journal entry' );
								
						$mdjm->mdjm_events->add_journal( array(
									'user' 			=> 1,
									'event'		   => $event->ID,
									'comment_content' => MDJM_BALANCE_LABEL . ' Reminder Scheduled Task executed<br /><br />' . time(),
									'comment_type' 	=> 'mdjm-journal',
									),
									array(
										'type' 		  => 'added-note',
										'visibility'	=> '0',
									) );
					} // End if( MDJM_JOURNAL == true )
					else	{
						if( MDJM_DEBUG == true )
							$GLOBALS['mdjm_debug']->log_it( '	-- Journalling is disabled' );	
					}
					
					$notify_dj = isset( $this->schedules['balance-reminder']['options']['notify_dj'] ) ? $this->schedules['balance-reminder']['options']['notify_dj'] : '';
					$notify_admin = isset( $this->schedules['balance-reminder']['options']['notify_admin'] ) ? $this->schedules['balance-reminder']['options']['notify_admin'] : '';
					
					$client = get_post_meta( $event->ID, '_mdjm_event_client', true );
					$dj = get_post_meta( $event->ID, '_mdjm_event_dj', true );
					$event_date = get_post_meta( $event->ID, '_mdjm_event_date', true );
					
					$event_dj = !empty( $dj ) ? get_userdata( $dj ) : 'DJ not found';
					$event_client = !empty( $client ) ? get_userdata( $client ) : 'Client not found';
					$event_deposit = get_post_meta( $event->ID, '_mdjm_event_deposit', true );
					$event_cost = get_post_meta( $event->ID, '_mdjm_event_cost', true );
					
					$venue_post_id = get_post_meta( $event->ID, '_mdjm_event_venue_id', true );
					
					$event_venue = $mdjm->mdjm_events->mdjm_get_venue_details( $venue_post_id, $event->ID );	
				
					$contact_client = ( isset( $this->schedules['balance-reminder']['options']['email_client'] ) 
						&& $this->schedules['balance-reminder']['options']['email_client'] == 'Y'  ? true : false );
						
					$email_template = ( isset( $this->schedules['balance-reminder']['options']['email_template'] ) && 
						$mdjm_posts->post_exists( $this->schedules['balance-reminder']['options']['email_template'] ) ? 
						$this->schedules['balance-reminder']['options']['email_template'] : false );
					
					/* -- Client Deposit Request Email -- */
					if( !empty( $contact_client ) && !empty( $email_template ) )	{ // Email the client
						if( MDJM_DEBUG == true )
							$GLOBALS['mdjm_debug']->log_it( 'Task ' . $this->schedules['balance-reminder']['name'] . ' is configured to notify client that deposit is due' );
							
						$request = $mdjm->send_email( array( 
									'content'	=> $email_template,
									'to'		 => $event_client->ID,
									'from'	   => $mdjm_settings['templates']['enquiry_from'] == 'dj' ? $event_dj->ID : 0,
									'journal'	=> 'email-client',
									'event_id'   => $event->ID,
									'html'	   => true,
									'cc_dj'	  => isset( $mdjm_settings['email']['bcc_dj_to_client'] ) ? true : false,
									'cc_admin'   => isset( $mdjm_settings['email']['bcc_admin_to_client'] ) ? true : false,
									'source'	 => __( 'Request ' . MDJM_BALANCE_LABEL . ' Scheduled Task' ),
								) );
						if( $request )	{
							if( MDJM_DEBUG == true )
								 $GLOBALS['mdjm_debug']->log_it( '	-- Balance reminder sent to ' . $event_client->display_name . '. ' . $request . ' ID ' );
						}
						else	{
							if( MDJM_DEBUG == true )
								 $GLOBALS['mdjm_debug']->log_it( '	ERROR: Balance reminder was not sent' );
						}
					}
					else	{
						if( MDJM_DEBUG == true )
							$GLOBALS['mdjm_debug']->log_it( 'Task ' . $this->schedules['balance-reminder']['name'] . ' is not configured to notify client' );	
					}
				
					/* Prepare admin notification email data array */
					if( !empty( $notify_admin ) && $notify_admin == 'Y' )	{
						if( MDJM_DEBUG == true )
							$GLOBALS['mdjm_debug']->log_it( '	-- Admin notifications are enabled' );
							
						if( !isset( $notify['admin'] ) || !is_array( $notify['admin'] ) ) $notify['admin'] = array();
						
						$notify['admin'][$event->ID] = array(
																'id'		=> $event->ID,
																'client'	=> $event_client->display_name,
																'deposit'   => !empty( $event_deposit ) ? 
																				$event_deposit : '0',
																'cost'   => !empty( $event_cost ) ? 
																				$event_cost : '0',
																'venue'	 => !empty( $event_venue['name'] ) ? 
																	$event_venue['name'] : 'No Venue Set',
																'djinfo'	=> $event_dj,
																'date'	  => !empty( $event_date ) ? date( "d M Y", strtotime( $event_date ) ) : 'Date not found',
																);
					} // End if( !empty( $notify_admin ) && $notify_admin == 'Y' )
					
					/* Prepare DJ notification email data array */
					if( !empty( $notify_dj ) && $notify_dj == 'Y' && dj_can( 'see_deposit' ) )	{
						if( MDJM_DEBUG == true )
							$GLOBALS['mdjm_debug']->log_it( '	-- DJ notifications are enabled' );
							
						if( !isset( $notify['dj'] ) || !is_array( $notify['dj'] ) ) $notify['dj'] = array();
						$notify['dj'][$dj] = array();
						$notify['dj'][$dj][$event->ID] = array(
																'id'		=> $event->id,
																'client'	=> $event_client->display_name,
																'deposit'   => !empty( $event_deposit ) ? 
																				$event_deposit : '0',
																'cost'   => !empty( $event_cost ) ? 
																				$event_cost : '0',
																'venue'	 => !empty( $event_venue['name'] ) ? 
																	$event_venue['name'] : 'No Venue Set',
																'djinfo'	=> $event_dj,
																'date'	  => !empty( $event_date ) ? date( "d M Y", strtotime( $event_date ) ) : 'Date not found',
																);
							
					} // End if( !empty( $notify_dj ) && $notify_dj == 'Y' )
					
					$x++;
					
				} // End foreach
				$cron_end = microtime(true);
								
				/* -- Prepare the Admin notification email -- */
				if( !empty( $notify_admin ) && $notify_admin == 'Y' )	{
					$notify_email_args = array(
											'data'		=> $notify['admin'],
											'taskinfo'	=> $this->schedules['balance-reminder'],
											'start'	   => $cron_start,
											'end'		 => $cron_end,
											'total'	   => $x,
										); // $notify_email_args
										
					$mdjm->send_email( array(
											'content'	=> $this->notification_content( $notify_email_args ),
											'to'		 => $mdjm_settings['email']['system_email'],
											'subject'	=> 'Balance Reminder Scheduled Task Completed - ' . MDJM_APP,
											'journal'	=> false,
											'html'	   => false,
											'cc_admin'   => false,
											'cc_dj'	  => false,
											'filter'	 => false,
											'log_comm'   => false,
											) );
				}// if( !empty( $notify_admin ) && $notify_admin == 'Y' )	{
				
				/* -- Prepare the DJ notification email -- */
				if( !empty( $notify_dj ) && $notify_dj == 'Y' && dj_can( 'see_deposit' ) )	{
					foreach( $notify['dj'] as $notify_dj )	{
						foreach( $notify_dj as $dj )	{
							$notify_email_args = array(
													'data'		=> $notify_dj,
													'taskinfo'	=> $this->schedules['balance-reminder'],
													'start'	   => $cron_start,
													'end'		 => $cron_end,
													'total'	   => $x,
												); // $notify_email_args
																			
							$mdjm->send_email( array(
													'content'	=> $this->notification_content( $notify_email_args ),
													'to'		 => $dj->ID,
													'subject'	=> 'Balance Reminder Scheduled Task Completed - ' . MDJM_APP,
													'journal'	=> false,
													'html'	   => false,
													'cc_admin'   => false,
													'cc_dj'	  => false,
													'filter'   => false,
													'log_comm'   => false,
													) );
						} // foreach( $notify_dj as $dj )
					} // foreach( $notify['dj'] as $notify_dj )
				} // if( !empty( $notify_dj ) && $notify_dj == 'Y' )
				
				add_action( 'save_post', array( $mdjm_posts, 'save_custom_post' ), 10, 2 );
			} // if( count( $events ) > 0 )
			
			else	{
				if( MDJM_DEBUG == true )
					$GLOBALS['mdjm_debug']->log_it( 'No balances are due' );
			}
						
			// Prepare next run time
			$this->update_nextrun( 'balance-reminder' );
			
			if( MDJM_DEBUG == true )
				$GLOBALS['mdjm_debug']->log_it( '*** Completed the Balance Reminder task ***', true );
			
		} // balance_reminder
		
		/*
		 * Update the cron task following execution setting next run time
		 * 
		 * @param	str		$task	The slug of the task to update
		 * 
		 */
		public function update_nextrun( $task )	{
			$mdjm_schedules = get_option( MDJM_SCHEDULES_KEY );
			
			$mdjm_schedules[$task]['lastran'] = time();
			$time = time();
			
			if( isset( $mdjm_schedules[$task]['frequency'] ) && $mdjm_schedules[$task]['frequency'] == 'Hourly')
				$mdjm_schedules[$task]['nextrun'] = strtotime( "+1 hour", $time );
			
			elseif( isset( $mdjm_schedules[$task]['frequency'] ) && $mdjm_schedules[$task]['frequency'] == 'Daily')
				$mdjm_schedules[$task]['nextrun'] = strtotime( "+1 day", $time );
				
			elseif( isset( $mdjm_schedules[$task]['frequency'] ) && $mdjm_schedules[$task]['frequency'] == 'Twice Daily')
				$mdjm_schedules[$task]['nextrun'] = strtotime( "+12 hours", $time );
			
			elseif( isset( $mdjm_schedules[$task]['frequency'] ) && $mdjm_schedules[$task]['frequency'] == 'Weekly')
				$mdjm_schedules[$task]['nextrun'] = strtotime( "+1 week", $time );
			
			elseif( isset( $mdjm_schedules[$task]['frequency'] ) && $mdjm_schedules[$task]['frequency'] == 'Monthly')
				$mdjm_schedules[$task]['nextrun'] = strtotime( "+1 month", $time );
			
			elseif( isset( $mdjm_schedules[$task]['frequency'] ) && $mdjm_schedules[$task]['frequency'] == 'Yearly')
				$mdjm_schedules[$task]['nextrun'] = strtotime( "+1 year", $time );
			
			else /* It should not run again */
				$mdjm_schedules[$task]['nextrun'] = 'N/A';
			
			$mdjm_schedules[$task]['totalruns'] = $mdjm_schedules[$task]['totalruns'] + 1;
	
			update_option( MDJM_SCHEDULES_KEY, $mdjm_schedules );
		} // update_nextrun
		
		/*
		 * Build the notification email content
		 *
		 * @param	arr		$task		The current task array
		 * @return	str		$content	The content of the email
		 */
		public function notification_content( $task )	{
			global $mdjm, $mdjm_settings;
						
			if( empty( $task ) )	{
				if( MDJM_DEBUG == true )
					$GLOBALS['mdjm_debug']->log_it( 'ERROR: No task was parsed ' . __METHOD__ );
			}
			else	{
				if( MDJM_DEBUG == true )
					$GLOBALS['mdjm_debug']->log_it( 'Creating notification content for ' . $task['taskinfo']['name'] );	
			}
			
			/* -- Start the email content -- */
			$content = 'The ' . $task['taskinfo']['name'] . ' scheduled task from ' . MDJM_COMPANY . ' has completed. ' . "\r\n" . 
					"\r\n" . 
					'Task Start time: ' . date( 'H:i:s l, jS F Y', $task['start'] ) . "\r\n" . 
					"\r\n";
			/* Build the email content relating to the current task */
			switch( $task['taskinfo']['slug'] )	{
				case 'complete-events': // Notification content for Complete Events task
					$content .= $task['total'] . ' event(s) have been marked as completed...' . "\r\n" . 
						"\r\n" . 
						'----------------------------------------' . 
						'----------------------------------------' . "\r\n";
					/* -- List each event -- */
					foreach ( $task['data'] as $eventinfo )	{
						$content .= 'Event ID: ' . $eventinfo['id'] . "\r\n" . 
							'Date: ' . $eventinfo['date'] . "\r\n" . 
							'Venue: ' . $eventinfo['venue'] . "\r\n" . 
							'Client: ' . $eventinfo['client'] . "\r\n" . 
							'DJ: ' . $eventinfo['djinfo']->display_name . "\r\n" . 
							'Link: ' . get_edit_post_link( $eventinfo['id'], '' ) . "\r\n" . 
							'----------------------------------------' . 
							'----------------------------------------' . "\r\n";
					} // End Foreach
				break;
				
				case 'fail-enquiry':
					$content .= $task['total'] . ' enquiry(s) have been marked as lost...' . "\r\n" . 
						"\r\n" . 
						'----------------------------------------' . 
						'----------------------------------------' . "\r\n";
					foreach ( $task['data'] as $eventinfo )	{
						$content .= 'Event ID: ' . $eventinfo['id'] . "\r\n" . 
							'Date: ' . $eventinfo['date'] . "\r\n" . 
							'Client: ' . $eventinfo['client'] . "\r\n" . 
							'DJ: ' . $eventinfo['djinfo']->display_name . "\r\n" . 
							'Link: ' . get_edit_post_link( $eventinfo['id'], '' ) . "\r\n" .
							'----------------------------------------' . 
							'----------------------------------------' . "\r\n";
					} // End Foreach
				break;
				
				case 'request-deposit':
					$content .= $task['total'] . ' deposit requests ' . 
						( !empty( $task['taskinfo']['options']['email_client'] ) && $task['taskinfo']['options']['email_client'] == 'Y' ? 
						' have been sent' : ' need to be requested' ) . "\r\n" . 
						"\r\n" . 
						'----------------------------------------' . 
						'----------------------------------------' . "\r\n";
					foreach ( $task['data'] as $eventinfo )	{
						$content .= 'Event ID: ' . $eventinfo['id'] . "\r\n" . 
							'Date: ' . $eventinfo['date'] . "\r\n" . 
							'Client: ' . $eventinfo['client'] . "\r\n" . 
							'DJ: ' . $eventinfo['djinfo']->display_name . "\r\n" . 
							MDJM_DEPOSIT_LABEL . ': ' . display_price( $eventinfo['deposit'] ) . "\r\n" . 
							'Link: ' . get_edit_post_link( $eventinfo['id'], '' ) . "\r\n" .
							'----------------------------------------' . 
							'----------------------------------------' . "\r\n";
					} // End Foreach
				break;
				
				case 'balance-reminder':
					$content .= $task['total'] . ' balance requests have been sent' . "\r\n" . 
						"\r\n" . 
						'----------------------------------------' . 
						'----------------------------------------' . "\r\n";
					foreach ( $task['data'] as $eventinfo )	{
						$content .= 'Event ID: ' . $eventinfo['id'] . "\r\n" . 
							'Date: ' . $eventinfo['date'] . "\r\n" . 
							'Client: ' . $eventinfo['client'] . "\r\n" . 
							'DJ: ' . $eventinfo['djinfo']->display_name . "\r\n" . 
							MDJM_BALANCE_LABEL . ' Due: ' . display_price( $eventinfo['cost'] - $eventinfo['deposit'] ) . "\r\n" . 
							'Link: ' . get_edit_post_link( $eventinfo['id'], '' ) . "\r\n" .
							'----------------------------------------' . 
							'----------------------------------------' . "\r\n";
					} // End Foreach
				break;
				
				case 'client-feedback':
					$content .= $task['total'] . ' client feedback requests have been sent' . "\r\n" . 
						"\r\n" . 
						'----------------------------------------' . 
						'----------------------------------------' . "\r\n";
					foreach ( $task['data'] as $eventinfo )	{
						$content .= 'Event ID: ' . $eventinfo['id'] . "\r\n" . 
							'Date: ' . $eventinfo['date'] . "\r\n" . 
							'Client: ' . $eventinfo['client'] . "\r\n" . 
							'DJ: ' . $eventinfo['djinfo']->display_name . "\r\n" . 
							'----------------------------------------' . 
							'----------------------------------------' . "\r\n";
					}
				break;
			} // Switch
			/* -- Complete the email content -- */
			$content .= 'Task End time: ' . date( 'H:i:s l, jS F Y', $task['end'] ) . "\r\n" . 
						"\r\n" . 
						'This email was generated by the Mobile DJ Manager for WordPress plugin - http://www.mydjplanner.co.uk';
			
			/* -- Return the content -- */
			return $content;
		} // notification_content
		
		/*
		 * This function is deprecated since 1.2.3.4
		 * It remains purely to avoid fatal errors until a full cleanup has been performed
		 * although it should not longer be utilised
		 *
		 */
		public function synchronise()	{		
			if( MDJM_DEBUG == true )
				$GLOBALS['mdjm_debug']->log_it( 'Deprecated function in use in ' . __METHOD__, true );
			
			return;
		} // synchronise
		
		/*
		 * Import event journal entries as comments
		 *
		 *
		 *
		 */
		function import_journal()	{
			global $mdjm, $wpdb;
			
			add_filter( 'akismet_debug_log', '__return_false' );
			remove_action( 'wp_insert_comment', array( 'Akismet', 'auto_check_update_meta' ), 10, 2 );
			remove_filter( 'preprocess_comment', array( 'Akismet', 'auto_check_comment' ), 1 );
			
			$events = get_posts( array(
								'posts_per_page'	=> -1,
								'post_status'	   => 'any',
								'post_type'		 => MDJM_EVENT_POSTS,
								) );
			
			$GLOBALS['mdjm_debug']->log_it( count( $events ) . ' events found' );
								
			foreach( $events as $event )	{
				/* List event journal entries -- */
				$journal_list = $wpdb->get_results(
						"SELECT * FROM `" . MDJM_JOURNAL_TABLE . "` WHERE `event` = '" . $event->ID . "' AND `migration` IS NULL" );
				$i = 0;
				$total = count( $journal_list );				
				if( $total > 0 )	{
					$GLOBALS['mdjm_debug']->log_it( $wpdb->num_rows . ' journal entries found for event ' . $event->ID );
					foreach( $journal_list as $journal_entry )	{
						/* -- Insert the comment -- */
						$mdjm->mdjm_events->add_journal( array(
												'user'				=> $journal_entry->author,
												'event'				=> $event->ID,
												'comment_content'	=> $journal_entry->entry,
												'comment_type'		=> 'mdjm-journal',
												'comment_date'		=> date( 'Y-m-d H:i:s', $journal_entry->timestamp )
												 ),
												 array(
													 'type'				=> $journal_entry->type,
													 'visibility'		=> '1',
												 ) );
						$i++;
						$GLOBALS['mdjm_debug']->log_it( '    ' . $i . ' of ' . $total . ' entries imported' );
						$wpdb->update(
									MDJM_JOURNAL_TABLE,
									array( 'migration' => 'Completed' ),
									array( 'id' => $journal_entry->id )
									);
					}
				}
				else	{
					$GLOBALS['mdjm_debug']->log_it( 'No journal entries found for event ' . $event->ID );	
				}
			}
			add_action( 'wp_insert_comment', array( 'Akismet', 'auto_check_update_meta' ), 10, 2 );
			add_filter( 'preprocess_comment', array( 'Akismet', 'auto_check_comment' ), 1 );					
			
		} // import_journal
		
	} // class	
	