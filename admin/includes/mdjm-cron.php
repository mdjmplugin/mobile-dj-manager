<?php

/*
* mdjm-cron.php
* 13/11/2014
* @since 0.9.3
* Executes all MDJM scheduled tasks
*/
	defined('ABSPATH') or die("Direct access to this page is disabled!!!");
	
	global $mdjm_options;

/***** ERROR CHECKS *****/
/*
* f_mdjm_cron_has_errors
* 19/11/2014
* @since 0.9.3
* Check selected task for errors with config
*/

	function f_mdjm_cron_has_errors( $task )	{
		/* Email errors */
		if( isset( $task['options']['email_client'] ) && $task['options']['email_client'] == 'Y' )	{
			/* Check template is set */
			if( isset( $task['options']['email_template'] ) && $task['options']['email_template'] == '0' )	{
				if( !$cron_errors ) $cron_errors = array();
				$error = 'Task ' . $task['name'] . ' is configured to send client emails but no email template is defined. Please check the task configuration to rectify. (Set 1)';
				array_push( $cron_errors, $error );
			}
			/* Check template exists */
			$template_query = new WP_Query( array( 'post_type' => 'email_template', 'post__in' => array( $task['options']['email_template'] ) ) );
			if ( !$template_query->have_posts() ) {
				if( !$cron_errors ) $cron_errors = array();
				$error = 'Task ' . $task['name'] . ' is configured to use an email template but the template does not exist. It may have been deleted. Please check the task configuration to rectify.';
				array_push( $cron_errors, $error );
			}
			
			/* Check subject is set */
			if( !isset( $task['options']['email_subject'] ) || $task['options']['email_subject'] == '' )	{
				if( !isset( $cron_errors ) ) $cron_errors = array();
				$error = 'Task ' . $task['name'] . ' is configured to send client emails but no email subject is defined. Please check the task configuration to rectify.';
				array_push( $cron_errors, $error );
			}
			/* Check from address is set */
			if( !isset( $task['options']['email_from'] ) || $task['options']['email_from'] == '0' )	{
				if( !isset( $cron_errors ) ) $cron_errors = array();
				$error = 'Task ' . $task['name'] . ' is configured to send client emails but you have not specified a From address. Please check the task configuration to rectify.';
				array_push( $cron_errors, $error );
			}
		}
		
		if( isset( $cron_errors ) )	{
			return $cron_errors;	
		}
		else	{
			return false;	
		}
		
	} // f_mdjm_cron_has_errors

/*
* f_mdjm_cron_email_errors
* 19/11/2014
* @since 0.9.3
* Emails admin with any task errors found
*/
	function f_mdjm_cron_email_errors( $task, $cron_errors )	{
		global $mdjm_options;
		if( $cron_errors )	{
			$to = $mdjm_options['system_email'];
			$subject = 'Task Failure Notice from ' . get_bloginfo( 'name' );
			$content = 'An error occured during the execution of on of your scheduled tasks. Please review the information below and take any remedial actions necessary to resolved.' . "\n";
			$content .= "\n";
			$content .= 'Task Name: ' . $task['name'] . "\n";
			$content .= 'Started at: ' . date( 'H:i d M Y' ) . "\n";
			$content .= 'Status: Failed' . "\n";
			$content .= "\n";
			$content .= 'Errors Reported' . "\n";
			$i = 1;
			foreach( $cron_errors as $cron_error )	{
				$content .= $i . '. ' . $cron_error . "\n";
				$i++;
			} // foreach( $cron_errors as $cron_errors )
			$content .= "\n";
			$content .= 'End of message' . "\n";
			
			wp_mail( $to, $subject, $content );
		} // if( $cron_errors )
	} // f_mdjm_cron_email_errors

/*
* f_mdjm_cron_get_fromaddr
* 17/11/2014
* @since 0.9.3
* Retrieves the from address to be used in emails
*/
	function f_mdjm_cron_get_fromaddr( $args )	{
		global $mdjm_options;
		if( isset( $args['taskinfo']['options']['email_from'] ) && $args['taskinfo']['options']['email_from'] == 'dj' )	{
			$fromaddr = 'From: ' . $args['djinfo']->display_name . ' <' . $args['djinfo']->user_email . '>' . "\r\n";
		}
		else	{
			$fromaddr = get_bloginfo( 'name' ) . ' <' . $mdjm_options['system_email'] . '>' . "\r\n";
		}
		
		return $fromaddr;
	} // f_mdjm_cron_get_fromaddr

/***** EVENT TASKS *****/
/*
* f_mdjm_cron_complete_event
* 15/11/2014
* @since 0.9.3
* Complete events that are in an approved
* state but where the event date has passed
*/
	function f_mdjm_cron_complete_event()	{
		global $mdjm_options, $wpdb;
		
		if( !isset( $db_tbl ) )
			include( WPMDJM_PLUGIN_DIR . '/includes/config.inc.php' );
		
		$mdjm_schedules = get_option( 'mdjm_schedules' );
		
		/* Check for errors */
		$cron_errors = f_mdjm_cron_has_errors( $mdjm_schedules['complete-events'] );
		
		/* If there are errors notify admin and abort */
		if( $cron_errors )	{
			f_mdjm_cron_email_errors( $mdjm_schedules['complete-events'], $cron_errors );
			// Email errors to admin
			return; // Abort
		}
		
		if( $mdjm_schedules['complete-events']['active'] == 'Y' 
			&& $mdjm_schedules['complete-events']['nextrun'] <= time() )	{
			
			$cron_start = microtime(true);
			$event_query = "SELECT * FROM `" . $db_tbl['events'] . "` WHERE `contract_status` = 'Approved' AND `event_date` <= DATE_ADD(NOW(), INTERVAL " . $mdjm_schedules['complete-events']['options']['age'] . ")";
			$eventlist = $wpdb->get_results( $event_query );
			$notify = array();
			$x = 0;
			if( $eventlist )	{ // We have results
				if( !is_array( $eventlist ) ) $eventlist = array( $eventlist );
				/*Loop through results */
				foreach( $eventlist as $event )	{
					if( $event->cronned != '' )	{
						$cron_update = json_decode( $event->cronned, TRUE );
					}
					if( !is_array( $cron_update ) ) $cron_update = array();
					$cron_update[$mdjm_schedules['complete-events']['slug']] = time();
					$update_args = array(
								'contract_status' => 'Completed',
								'last_updated_by' => '0',
								'last_updated' 	=> date( 'Y-m-d H:i:s' ),
								'cronned'		 => json_encode( $cron_update ),
							);
					$complete_event = $wpdb->update( $db_tbl['events'], $update_args, array( 'event_id' => $event->event_id ) );
					if( WPDJM_JOURNAL == 'Y' ) 	{
						$eventinfo = f_mdjm_get_eventinfo_by_id( $event->event_id );
						$clientinfo = get_userdata( $eventinfo->user_id );
						$j_args = array (
									'client' => $event->user_id,
									'event' => $event->event_id,
									'author' => get_current_user_id(),
									'type' => 'Complete Event',
									'source' => 'System',
									'entry' => 'Event ID ' . $event->event_id . ' has been marked as completed'
								);
						f_mdjm_do_journal( $j_args );
					} // End if( WPDJM_JOURNAL == 'Y' )
					/* Get the DJ Data */
					if( isset( $mdjm_schedules['complete-events']['options']['notify_admin'] ) && $mdjm_schedules['complete-events']['options']['notify_admin'] == 'Y' ||
						isset( $mdjm_schedules['complete-events']['options']['notify_dj'] ) && $mdjm_schedules['complete-events']['options']['notify_dj'] == 'Y' )
							$djinfo = get_userdata( $event->event_dj );
						
					/* Create admin email data array */
					if( isset( $mdjm_schedules['complete-events']['options']['notify_admin'] ) && $mdjm_schedules['complete-events']['options']['notify_admin'] == Y )	{
						if( !is_array( $notify['admin'] ) ) $notify['admin'] = array();
						$notify['admin'][$event->event_id] = array(
																'id'		=> $event->event_id,
																'client'	=> $clientinfo->display_name,
																'venue'	 => $event->venue,
																'djinfo'	=> $djinfo,
																'date'	  => date( "d M Y", strtotime( $event->event_date ) ),
																);
					} // End Admin email data array
					
					/* Create DJ email data array */
					if( isset( $mdjm_schedules['complete-events']['options']['notify_dj'] ) && $mdjm_schedules['complete-events']['options']['notify_dj'] == 'Y' )	{
						if( !is_array( $notify['dj'] ) ) $notify['dj'] = array();
						$notify['dj'][$event->event_dj] = array();
						$notify['dj'][$event->event_dj][$event->event_id] = array(
																'id'		=> $event->event_id,
																'client'	=> $clientinfo->display_name,
																'venue'	 => $event->venue,
																'djinfo'	=> $djinfo,
																'date'	  => date( "d M Y", strtotime( $event->event_date ) ),
																);
					} // End DJ email data array
					
					$x++;
				} // End foreach( $eventlist as $event )
				$cron_end = microtime(true);
				
				// Admin notification emails
				if( isset( $mdjm_schedules['complete-events']['options']['notify_admin'] ) && $mdjm_schedules['complete-events']['options']['notify_admin'] == 'Y' )	{
					$cron_email_args = array(
											'type'		=> 'notify',
											'to'		  => $mdjm_options['system_email'],
											'subject'	 => $mdjm_schedules['complete-events']['options']['email_subject'],
											'template'	=> $mdjm_schedules['complete-events']['options']['email_template'],
											'djinfo'	  => $djinfo,
											'data'		=> $notify['admin'],
											'taskinfo'	=> $mdjm_schedules['complete-events'],
											'start'	   => $cron_start,
											'end'		 => $cron_end,
											'total'		  => $x,
										); // $cron_email_args
					f_mdjm_cron_email( $cron_email_args );
				}// if( $mdjm_schedules['complete-events']['options']['notify_admin'] == Y )
				
				// DJ notification emails
				if( isset( $mdjm_schedules['complete-events']['options']['notify_dj'] ) && $mdjm_schedules['complete-events']['options']['notify_dj'] == Y )	{
					foreach( $notify['dj'] as $notify_dj )	{
						foreach( $notify_dj as $dj )	{
							$cron_email_args = array(
													'type'		=> 'notify',
													'to'			=> $djinfo->user_email,
													'subject'	 => $mdjm_schedules['complete-events']['options']['email_subject'],
													'template'	  => $mdjm_schedules['complete-events']['options']['email_template'],
													'djinfo'		=> $djinfo,
													'data'		  => $notify_dj,
													'taskinfo'	  => $mdjm_schedules['complete-events'],
													'total'		  => $x,
												); // $cron_email_args
												
							f_mdjm_cron_email( $cron_email_args );
						} // foreach( $notify_dj as $event )
						
					} // foreach( $notify['dj'] as $dj )
				
				}// if( $mdjm_schedules['complete-events']['options']['notify_dj'] == Y )
				
			} // End if( $eventlist )
			
			// Update task with run times
			f_mdjm_cronjob_update( $mdjm_schedules['complete-events']['slug'] );
			
		} // End if( $mdjm_schedules['complete-events']['active'] == Y )
		
	} // f_mdjm_cron_complete_event
	
/*
* f_mdjm_cron_fail_enquiry
* 15/11/2014
* @since 0.9.3
* Fails enquiries that have been outstanding
* for the specified (configured) amount of time
*/
	function f_mdjm_cron_fail_enquiry()	{
		global $mdjm_options, $wpdb;
		
		if( !isset( $db_tbl) )
			include( WPMDJM_PLUGIN_DIR . '/includes/config.inc.php' );
		
		$mdjm_schedules = get_option( 'mdjm_schedules' );
		
		/* Check for errors */
		$cron_errors = f_mdjm_cron_has_errors( $mdjm_schedules['fail-enquiry'] );
		
		/* If there are errors notify admin and abort */
		if( $cron_errors )	{
			f_mdjm_cron_email_errors( $mdjm_schedules['fail-enquiry'], $cron_errors );
			// Email errors to admin
			return; // Abort
		}
		
		if( $mdjm_schedules['fail-enquiry']['active'] == 'Y'
			&& $mdjm_schedules['fail-enquiry']['nextrun'] <= time() )	{
			
			$cron_start = microtime(true);
			$event_query = "SELECT * FROM " . $db_tbl['events'] . " WHERE `contract_status` = 'Enquiry' AND `date_added` <= DATE_SUB(NOW(), INTERVAL " . $mdjm_schedules['fail-enquiry']['options']['age'] . ")";
			$eventlist = $wpdb->get_results( $event_query );
			$notify = array();
			$x = 0;
			if( $eventlist )	{ // We have results
				foreach( $eventlist as $event )	{
					if( $event->cronned != '' )	{
						$cron_update = json_decode( $event->cronned, TRUE );
					}
					if( !is_array( $cron_update ) ) $cron_update = array();
					$cron_update[$mdjm_schedules['fail-enquiry']['slug']] = time();
					$update_args = array(
								'contract_status' => 'Failed Enquiry',
								'last_updated_by' => '0',
								'last_updated' 	=> date( 'Y-m-d H:i:s' ),
								'cronned'		 => json_encode( $cron_update ),
							);
					$fail_enquiry = $wpdb->update( $db_tbl['events'], $update_args, array( 'event_id' => $event->event_id ) );
					if( WPDJM_JOURNAL == 'Y' ) 	{
						$eventinfo = f_mdjm_get_eventinfo_by_id( $event->event_id );
						$clientinfo = get_userdata( $event->user_id );
						$j_args = array (
									'client' => $event->user_id,
									'event' => $event->event_id,
									'author' => get_current_user_id(),
									'type' => 'Fail Enquiry',
									'source' => 'System',
									'entry' => 'Event ID ' . $event->event_id . ' has been marked as lost'
								);
						f_mdjm_do_journal( $j_args );
					} // End if( WPDJM_JOURNAL == 'Y' )
					
					/* Get the DJ Data */
					if( isset( $mdjm_schedules['fail-enquiry']['options']['notify_admin'] ) && 
						$mdjm_schedules['fail-enquiry']['options']['notify_admin'] == 'Y' ||
						isset( $mdjm_schedules['fail-enquiry']['options']['notify_dj'] ) &&
						$mdjm_schedules['fail-enquiry']['options']['notify_dj'] == 'Y' )
							$djinfo = get_userdata( $event->event_dj );
						
					/* Create admin email data array */
					if( isset( $mdjm_schedules['fail-enquiry']['options']['notify_admin'] ) && $mdjm_schedules['fail-enquiry']['options']['notify_admin'] == 'Y' )	{
						if( !is_array( $notify['admin'] ) ) $notify['admin'] = array();
						$notify['admin'][$event->event_id] = array(
																'id'		=> $event->event_id,
																'client'	=> $clientinfo->display_name,
																'venue'	 => $event->venue,
																'djinfo'	=> $djinfo,
																'date'	  => date( "d M Y", strtotime( $event->event_date ) ),
																);
					} // End Admin email data array
					
					/* Create DJ email data array */
					if( isset( $mdjm_schedules['fail-enquiry']['options']['notify_dj'] ) && $mdjm_schedules['fail-enquiry']['options']['notify_dj'] == 'Y' )	{
						if( !is_array( $notify['dj'] ) ) $notify['dj'] = array();
						$notify['dj'][$event->event_dj] = array();
						$notify['dj'][$event->event_dj][$event->event_id] = array(
																'id'		=> $event->event_id,
																'client'	=> $clientinfo->display_name,
																'venue'	 => $event->venue,
																'djinfo'	=> $djinfo,
																'date'	  => date( "d M Y", strtotime( $event->event_date ) ),
																);
					} // End DJ email data array
					
					$x++;
				} // foreach( $eventlist as $event )
				
				$cron_end = microtime(true);
				
				// Admin notification emails
				if( isset( $mdjm_schedules['fail-enquiry']['options']['notify_admin'] ) && $mdjm_schedules['fail-enquiry']['options']['notify_admin'] == 'Y' )	{
					$cron_email_args = array(
											'type'		=> 'notify',
											'to'		  => $mdjm_options['system_email'],
											'subject'	 => $mdjm_schedules['fail-enquiry']['options']['email_subject'],
											'template'	  => $mdjm_schedules['fail-enquiry']['options']['email_template'],
											'djinfo'	  => $djinfo,
											'data'		  => $notify['admin'],
											'taskinfo'	  => $mdjm_schedules['fail-enquiry'],
											'start'	      => $cron_start,
											'end'		  => $cron_end,
											'total'		  => $x,
										); // $cron_email_args
					f_mdjm_cron_email( $cron_email_args );
				}// if( $mdjm_schedules['fail-enquiry']['options']['notify_admin'] == Y )
				
				// DJ notification emails
				if( isset( $mdjm_schedules['fail-enquiry']['options']['notify_dj'] ) && $mdjm_schedules['fail-enquiry']['options']['notify_dj'] == 'Y' )	{
					foreach( $notify['dj'] as $notify_dj )	{
						foreach( $notify_dj as $dj )	{
							$cron_email_args = array(
													'type'		=> 'notify',
													'to'		  => $djinfo->user_email,
													'subject'	 => $mdjm_schedules['fail-enquiry']['options']['email_subject'],
													'template'	  => $mdjm_schedules['fail-enquiry']['options']['email_template'],
													'djinfo'	  => $djinfo,
													'data'		  => $notify_dj,
													'taskinfo'	  => $mdjm_schedules['fail-enquiry'],
													'total'		  => $x,
												); // $cron_email_args
												
							f_mdjm_cron_email( $cron_email_args );
						} // foreach( $notify_dj as $event )
						
					} // foreach( $notify['dj'] as $dj )
				
				}// if( $mdjm_schedules['fail-enquiry']['options']['notify_dj'] == Y )
				
			} // if( $eventlist )
			
			// Update task with run times
			f_mdjm_cronjob_update( $mdjm_schedules['fail-enquiry']['slug'] );
				
		} // if( $mdjm_schedules['fail-enquiry']['active'] == 'Y' ...
	} // f_mdjm_cron_fail_enquiry

/***** CLIENT TASKS *****/
/*
* f_mdjm_cron_request_deposit
* 17/11/2014
* @since 0.9.3
* Searches approved events with no deposit
* paid and emails client to request payment
*/
	function f_mdjm_cron_request_deposit()	{
		global $mdjm_options, $wpdb;
		
		if( !isset( $db_tbl) )
			include( WPMDJM_PLUGIN_DIR . '/includes/config.inc.php' );
		
		$mdjm_schedules = get_option( 'mdjm_schedules' );
		
		/* Check for errors */
		$cron_errors = f_mdjm_cron_has_errors( $mdjm_schedules['request-deposit'] );
		
		/* If there are errors notify admin and abort */
		if( $cron_errors )	{
			f_mdjm_cron_email_errors( $mdjm_schedules['request-deposit'], $cron_errors );
			// Email errors to admin
			return; // Abort
		}
		
		if( $mdjm_schedules['request-deposit']['active'] == 'Y' 
			&& $mdjm_schedules['request-deposit']['nextrun'] <= time() )	{
			
			$cron_start = microtime(true);
			
			/* Grab all records that meet the criteria */
			$event_query = "SELECT * FROM " . $db_tbl['events'] . " WHERE `contract_status` = 'Approved' AND `deposit` > '0' AND `deposit_status` != 'Paid' AND `event_date` > DATE(NOW()) AND `contract_approved_date` < NOW() - INTERVAL " . $mdjm_schedules['request-deposit']['options']['age'];
			$eventlist = $wpdb->get_results( $event_query );
			$num_rows = $wpdb->num_rows;
			
			if( $eventlist )	{
				$notify = array();
				$x = 0;
				
				/* Loop through the events */
				foreach( $eventlist as $event )	{
					/* Only continue for events that have not had this task run already */
					if( $event->cronned != '' && array_key_exists( 'request-deposit', json_decode( $event->cronned, TRUE ) ) )	{
						f_mdjm_cronjob_update( $mdjm_schedules['request-deposit']['slug'] );
						return; // No need to run
					} // if( $event->cronned != ''...
					else	{
						/* Run the task */
						$eventinfo = f_mdjm_get_eventinfo_by_id( $event->event_id );
						$clientinfo = get_userdata( $event->user_id );
						$djinfo = get_userdata( $event->event_dj );
						
						/* Send the email */
						$cron_email_args = array(
											'type'		  => 'client',
											'to'			=> $clientinfo->user_email,
											'subject'	   => $mdjm_schedules['request-deposit']['options']['email_subject'],
											'djinfo'	 	=> $djinfo,
											'eventinfo'	 => $event,
											'clientinfo'	=> $clientinfo,
											'taskinfo'	  => $mdjm_schedules['request-deposit'],
										); // $cron_email_args
						f_mdjm_cron_email( $cron_email_args );
						
						/* Update the DB record to show task has run */
						if( $event->cronned != '' )	{
							$cron_update = json_decode( $event->cronned, TRUE );
						}
						if( !is_array( $cron_update ) ) $cron_update = array();
						$cron_update[$mdjm_schedules['request-deposit']['slug']] = time();
						$update_args = array(
									'last_updated_by' => '0',
									'last_updated' 	=> date( 'Y-m-d H:i:s' ),
									'cronned'		 => json_encode( $cron_update ),
								);
						$update_enquiry = $wpdb->update( $db_tbl['events'], $update_args, array( 'event_id' => $event->event_id ) );
						
						/* Update the Journal record */
						if( WPDJM_JOURNAL == 'Y' ) 	{
							$eventinfo = f_mdjm_get_eventinfo_by_id( $event->event_id );
							$clientinfo = get_userdata( $event->user_id );
							$j_args = array (
										'client' => $event->user_id,
										'event'  => $event->event_id,
										'author' => get_current_user_id(),
										'type'   => 'Request Deposit',
										'source' => 'System',
										'entry'  => 'Email requesting deposit payment sent to client'
									);
							f_mdjm_do_journal( $j_args );
						} // End if( WPDJM_JOURNAL == 'Y' )
						
						/* Create admin email data array */
						if( isset( $mdjm_schedules['request-deposit']['options']['notify_admin'] ) && $mdjm_schedules['request-deposit']['options']['notify_admin'] == 'Y' )	{
							if( !is_array( $notify['admin'] ) ) $notify['admin'] = array();
							$notify['admin'][$event->event_id] = array(
																	'id'		=> $event->event_id,
																	'client'	=> $clientinfo->display_name,
																	'venue'	 => $event->venue,
																	'deposit'   => $event->deposit,
																	'djinfo'	=> $djinfo,
																	'date'	  => date( "d M Y", strtotime( $event->event_date ) ),
																	);
						} // End Admin email data array
						
						/* Create DJ email data array */
						if( isset( $mdjm_schedules['request-deposit']['options']['notify_dj'] ) && $mdjm_schedules['request-deposit']['options']['notify_dj'] == 'Y' )	{
							if( !is_array( $notify['dj'] ) ) $notify['dj'] = array();
							$notify['dj'][$event->event_dj] = array();
							$notify['dj'][$event->event_dj][$event->event_id] = array(
																	'id'		=> $event->event_id,
																	'client'	=> $clientinfo->display_name,
																	'venue'	 => $event->venue,
																	'deposit'   => $event->deposit,
																	'djinfo'	=> $djinfo,
																	'date'	  => date( "d M Y", strtotime( $event->event_date ) ),
																	);
						} // End DJ email data array
						
					} // else
					
					$x++;
					
				} // foreach( $eventlist as $event )
			
				$cron_end = microtime(true);
			
				// Admin notification emails
				if( isset( $mdjm_schedules['request-deposit']['options']['notify_admin'] ) && $mdjm_schedules['request-deposit']['options']['notify_admin'] == 'Y' )	{
					$cron_email_args = array(
											'type'		=> 'notify',
											'to'		  => $mdjm_options['system_email'],
											'subject'     => 'Task Request Deposit Completed ' . $mdjm_options['company_name'],
											'template'	=> $mdjm_schedules['request-deposit']['options']['email_template'],
											'djinfo'	  => $djinfo,
											'data'		=> $notify['admin'],
											'taskinfo'	=> $mdjm_schedules['request-deposit'],
											'start'	    => $cron_start,
											'end'		  => $cron_end,
											'total'		=> $x,
										); // $cron_email_args
					f_mdjm_cron_email( $cron_email_args );
				}// if( $mdjm_schedules['request-desposit']['options']['notify_admin'] == Y )
				
				// DJ notification emails
				if( isset( $mdjm_schedules['request-deposit']['options']['notify_dj'] ) && $mdjm_schedules['request-deposit']['options']['notify_dj'] == 'Y' )	{
					foreach( $notify['dj'] as $notify_dj )	{
						foreach( $notify_dj as $dj )	{
							$cron_email_args = array(
													'type'		  => 'notify',
													'to'		    => $djinfo->user_email,
													'subject'  	   => 'Task Request Deposit Completed ' . $mdjm_options['company_name'],
													'template'	  => $mdjm_schedules['request-deposit']['options']['email_template'],
													'djinfo'	    => $djinfo,
													'data'		  => $notify_dj,
													'taskinfo'	  => $mdjm_schedules['request-deposit'],
													'total'		 => $x,
												); // $cron_email_args
												
							f_mdjm_cron_email( $cron_email_args );
						} // foreach( $notify_dj as $event )
						
					} // foreach( $notify['dj'] as $dj )
				
				}// if( $mdjm_schedules['request-deposit']['options']['notify_dj'] == Y )
				
			} // if( $eventlist )
			
			/* Update the cron task with run times */
			f_mdjm_cronjob_update( $mdjm_schedules['request-deposit']['slug'] );
			
		} // if( $mdjm_schedules['request-deposit']['active'] == 'Y'...
	} // f_mdjm_cron_request_deposit

/*
* f_mdjm_cron_balance_reminder
* 22/11/2014
* @since 0.9.3
* Searches events taking place within specified
* timeframe and emails client to request payment
*/
	function f_mdjm_cron_balance_reminder()	{
		global $mdjm_options, $wpdb;
		
		if( !isset( $db_tbl) )
			include( WPMDJM_PLUGIN_DIR . '/includes/config.inc.php' );
		
		$mdjm_schedules = get_option( 'mdjm_schedules' );
		
		/* Check for errors */
		$cron_errors = f_mdjm_cron_has_errors( $mdjm_schedules['balance-reminder'] );
		
		/* If there are errors notify admin and abort */
		if( $cron_errors )	{
			f_mdjm_cron_email_errors( $mdjm_schedules['balance-reminder'], $cron_errors );
			// Email errors to admin
			return; // Abort
		}
		
		if( isset( $mdjm_schedules['balance-reminder']['active'] ) && $mdjm_schedules['balance-reminder']['active'] == 'Y' 
			&& $mdjm_schedules['balance-reminder']['nextrun'] <= time() )	{
			
			$cron_start = microtime(true);
			
			/* Grab all records that meet the criteria */
			$event_query = "SELECT * FROM " . $db_tbl['events'] . " WHERE (`contract_status` = 'Approved' AND `balance_status` = 'Due' AND `event_date` > DATE(NOW() - INTERVAL " . $mdjm_schedules['balance-reminder']['options']['age'] . ")) OR (`contract_status` = 'Approved' AND `balance_status` = '' AND `event_date` > DATE(NOW() - INTERVAL " . $mdjm_schedules['balance-reminder']['options']['age'] . "))";
			$eventlist = $wpdb->get_results( $event_query );
			$num_rows = $wpdb->num_rows;
			
			if( $eventlist )	{
				$notify = array();
				$x = 0;
				
				/* Loop through the events */
				foreach( $eventlist as $event )	{
					/* Only continue for events that have not had this task run already */
					if( $event->cronned != '' && array_key_exists( 'balance-reminder', json_decode( $event->cronned, TRUE ) ) )	{
						f_mdjm_cronjob_update( $mdjm_schedules['balance-reminder']['slug'] );
						return; // No need to run
					} // if( $event->cronned != ''...
					else	{
						/* Run the task */
						$eventinfo = f_mdjm_get_eventinfo_by_id( $event->event_id );
						$clientinfo = get_userdata( $event->user_id );
						$djinfo = get_userdata( $event->event_dj );
						
						/* Send the email */
						$cron_email_args = array(
											'type'		  => 'client',
											'to'			=> $clientinfo->user_email,
											'subject'	   => $mdjm_schedules['balance-reminder']['options']['email_subject'],
											'djinfo'	 	=> $djinfo,
											'eventinfo'	 => $event,
											'clientinfo'	=> $clientinfo,
											'taskinfo'	  => $mdjm_schedules['balance-reminder'],
										); // $cron_email_args
						f_mdjm_cron_email( $cron_email_args );
						
						/* Update the DB record to show task has run */
						if( $event->cronned != '' )	{
							$cron_update = json_decode( $event->cronned, TRUE );
						}
						if( !is_array( $cron_update ) ) $cron_update = array();
						$cron_update[$mdjm_schedules['balance-reminder']['slug']] = time();
						$update_args = array(
									'last_updated_by' => '0',
									'last_updated' 	=> date( 'Y-m-d H:i:s' ),
									'cronned'		 => json_encode( $cron_update ),
									);
						$update_enquiry = $wpdb->update( $db_tbl['events'], $update_args, array( 'event_id' => $event->event_id ) );
						
						/* Update the Journal record */
						if( WPDJM_JOURNAL == 'Y' ) 	{
							$eventinfo = f_mdjm_get_eventinfo_by_id( $event->event_id );
							$clientinfo = get_userdata( $event->user_id );
							$j_args = array (
										'client' => $event->user_id,
										'event'  => $event->event_id,
										'author' => get_current_user_id(),
										'type'   => 'Balance Reminder',
										'source' => 'System',
										'entry'  => 'Email requesting balance payment sent to client'
									);
							f_mdjm_do_journal( $j_args );
						} // End if( WPDJM_JOURNAL == 'Y' )
						
						/* Create admin email data array */
						if( isset( $mdjm_schedules['balance-reminder']['options']['notify_admin'] ) && $mdjm_schedules['balance-reminder']['options']['notify_admin'] == 'Y' )	{
							if( !is_array( $notify['admin'] ) ) $notify['admin'] = array();
							$notify['admin'][$event->event_id] = array(
																	'id'		=> $event->event_id,
																	'client'	=> $clientinfo->display_name,
																	'venue'	 => $event->venue,
																	'balance'   => $event->cost - $event->deposit,
																	'djinfo'	=> $djinfo,
																	'date'	  => date( "d M Y", strtotime( $event->event_date ) ),
																	);
						} // End Admin email data array
						
						/* Create DJ email data array */
						if( isset( $mdjm_schedules['balance-reminder']['options']['notify_dj'] ) && $mdjm_schedules['balance-reminder']['options']['notify_dj'] == 'Y' )	{
							if( !is_array( $notify['dj'] ) ) $notify['dj'] = array();
							$notify['dj'][$event->event_dj] = array();
							$notify['dj'][$event->event_dj][$event->event_id] = array(
																	'id'		=> $event->event_id,
																	'client'	=> $clientinfo->display_name,
																	'venue'	 => $event->venue,
																	'balance'   => $event->cost - $event->deposit,
																	'djinfo'	=> $djinfo,
																	'date'	  => date( "d M Y", strtotime( $event->event_date ) ),
																	);
						} // End DJ email data array
						
					} // else
					
					$x++;
					
				} // foreach( $eventlist as $event )
			
				$cron_end = microtime(true);
			
				// Admin notification emails
				if( isset( $mdjm_schedules['balance-reminder']['options']['notify_admin'] ) && $mdjm_schedules['balance-reminder']['options']['notify_admin'] == 'Y' )	{
					$cron_email_args = array(
											'type'		=> 'notify',
											'to'		  => $mdjm_options['system_email'],
											'subject'     => 'Task Balance Reminder Completed ' . $mdjm_options['company_name'],
											'template'	=> $mdjm_schedules['balance-reminder']['options']['email_template'],
											'djinfo'	  => $djinfo,
											'data'		=> $notify['admin'],
											'taskinfo'	=> $mdjm_schedules['balance-reminder'],
											'start'	    => $cron_start,
											'end'		  => $cron_end,
											'total'		=> $x,
										); // $cron_email_args
					f_mdjm_cron_email( $cron_email_args );
				}// if( $mdjm_schedules['balance-reminder']['options']['notify_admin'] == Y )
				
				// DJ notification emails
				if( isset( $mdjm_schedules['balance-reminder']['options']['notify_dj'] ) && $mdjm_schedules['balance-reminder']['options']['notify_dj'] == 'Y' )	{
					foreach( $notify['dj'] as $notify_dj )	{
						foreach( $notify_dj as $dj )	{
							$cron_email_args = array(
													'type'		  => 'notify',
													'to'		    => $djinfo->user_email,
													'subject'  	   => 'Task Request Balance Reminder Completed ' . $mdjm_options['company_name'],
													'template'	  => $mdjm_schedules['balance-reminder']['options']['email_template'],
													'djinfo'	    => $djinfo,
													'data'		  => $notify_dj,
													'taskinfo'	  => $mdjm_schedules['balance-reminder'],
													'total'		 => $x,
												); // $cron_email_args
												
							f_mdjm_cron_email( $cron_email_args );
						} // foreach( $notify_dj as $event )
						
					} // foreach( $notify['dj'] as $dj )
				
				}// if( $mdjm_schedules['balance-reminder']['options']['notify_dj'] == Y )
				
			} // if( $eventlist )
			
			/* Update the cron task with run times */
			f_mdjm_cronjob_update( $mdjm_schedules['balance-reminder']['slug'] );
			
		} // if( $mdjm_schedules['balance-reminder']['active'] == 'Y'...
	} // f_mdjm_cron_balance_reminder

/*
* f_mdjm_cron_client_feedback
* 20/11/2014
* @since 0.9.3
* If enabled, send feedback request to clients after event completed
*/
	function f_mdjm_cron_client_feedback()	{
		global $mdjm_options, $wpdb;
		
		if( !isset( $db_tbl) )
			include( WPMDJM_PLUGIN_DIR . '/includes/config.inc.php' );
		
		$mdjm_schedules = get_option( 'mdjm_schedules' );
		
		/* Check for errors */
		$cron_errors = f_mdjm_cron_has_errors( $mdjm_schedules['client-feedback'] );
		
		/* If there are errors notify admin and abort */
		if( $cron_errors )	{
			f_mdjm_cron_email_errors( $mdjm_schedules['client-feedback'], $cron_errors );
			// Email errors to admin
			return; // Abort
		}
		
		if( $mdjm_schedules['client-feedback']['active'] == 'Y'
			&& $mdjm_schedules['client-feedback']['nextrun'] <= time() )	{
			
			$cron_start = microtime(true);
			$event_query = "SELECT * FROM " . $db_tbl['events'] . " WHERE `contract_status` = 'Completed' AND DATE_ADD(`event_date`, INTERVAL " . $mdjm_schedules['client-feedback']['options']['age'] . ") <= NOW() AND `event_date` > NOW() - INTERVAL 1 MONTH";
			$eventlist = $wpdb->get_results( $event_query );
			$num_rows = $wpdb->num_rows;
			
			if( $eventlist )	{
				$notify = array();
				$x = 0;
				
				/* Loop through the events */
				foreach( $eventlist as $event )	{
					/* Only continue for events that have not had this task run already */
					if( $event->cronned != '' && array_key_exists( 'client-feedback', json_decode( $event->cronned, TRUE ) ) )	{
						f_mdjm_cronjob_update( $mdjm_schedules['balance-reminder']['slug'] );
						return; // No need to run
					} // if( $event->cronned != ''...
					else	{
						/* Run the task */
						$eventinfo = f_mdjm_get_eventinfo_by_id( $event->event_id );
						$clientinfo = get_userdata( $event->user_id );
						$djinfo = get_userdata( $event->event_dj );
						
						/* Send the email */
						$cron_email_args = array(
											'type'		  => 'client',
											'to'			=> $clientinfo->user_email,
											'subject'	   => $mdjm_schedules['client-feedback']['options']['email_subject'],
											'djinfo'	 	=> $djinfo,
											'eventinfo'	 => $event,
											'clientinfo'	=> $clientinfo,
											'taskinfo'	  => $mdjm_schedules['client-feedback'],
										); // $cron_email_args
						f_mdjm_cron_email( $cron_email_args );
						
						/* Update the DB record to show task has run */
						if( $event->cronned != '' )	{
							$cron_update = json_decode( $event->cronned, TRUE );
						}
						if( !is_array( $cron_update ) ) $cron_update = array();
						$cron_update[$mdjm_schedules['client-feedback']['slug']] = time();
						$update_args = array(
									'last_updated_by' => '0',
									'last_updated' 	=> date( 'Y-m-d H:i:s' ),
									'cronned'		 => json_encode( $cron_update ),
								);
						$update_enquiry = $wpdb->update( $db_tbl['events'], $update_args, array( 'event_id' => $event->event_id ) );
						
						/* Update the Journal record */
						if( WPDJM_JOURNAL == 'Y' ) 	{
							$eventinfo = f_mdjm_get_eventinfo_by_id( $event->event_id );
							$clientinfo = get_userdata( $event->user_id );
							$j_args = array (
										'client' => $event->user_id,
										'event'  => $event->event_id,
										'author' => get_current_user_id(),
										'type'   => 'Client Feedback',
										'source' => 'System',
										'entry'  => 'Client Feedback request sent to client'
									);
							f_mdjm_do_journal( $j_args );
						} // End if( WPDJM_JOURNAL == 'Y' )
						
						/* Create admin email data array */
						if( isset( $mdjm_schedules['client-feedback']['options']['notify_admin'] ) && $mdjm_schedules['client-feedback']['options']['notify_admin'] == 'Y' )	{
							if( !is_array( $notify['admin'] ) ) $notify['admin'] = array();
							$notify['admin'][$event->event_id] = array(
																	'id'		=> $event->event_id,
																	'client'	=> $clientinfo->display_name,
																	'venue'	 => $event->venue,
																	'deposit'   => $event->deposit,
																	'djinfo'	=> $djinfo,
																	'date'	  => date( "d M Y", strtotime( $event->event_date ) ),
																	);
						} // End Admin email data array
						
						/* Create DJ email data array */
						if( isset( $mdjm_schedules['client-feedback']['options']['notify_dj'] ) && $mdjm_schedules['client-feedback']['options']['notify_dj'] == 'Y' )	{
							if( !is_array( $notify['dj'] ) ) $notify['dj'] = array();
							$notify['dj'][$event->event_dj] = array();
							$notify['dj'][$event->event_dj][$event->event_id] = array(
																	'id'		=> $event->event_id,
																	'client'	=> $clientinfo->display_name,
																	'venue'	 => $event->venue,
																	'deposit'   => $event->deposit,
																	'djinfo'	=> $djinfo,
																	'date'	  => date( "d M Y", strtotime( $event->event_date ) ),
																	);
						} // End DJ email data array
						
					} // else
					
					$x++;
					
				} // foreach( $eventlist as $event )
			
				$cron_end = microtime(true);
				
				// Admin notification emails
				if( isset( $mdjm_schedules['client-feedback']['options']['notify_admin'] ) && $mdjm_schedules['client-feedback']['options']['notify_admin'] == 'Y' )	{
					$cron_email_args = array(
											'type'		 => 'notify',
											'to'		   => $mdjm_options['system_email'],
											'subject'      => 'Task "Client Feedback" Completed ' . $mdjm_options['company_name'],
											'template'	 => $mdjm_schedules['client-feedback']['options']['email_template'],
											'djinfo'	   => $djinfo,
											'data'		 => $notify['admin'],
											'taskinfo'	 => $mdjm_schedules['client-feedback'],
											'start'	    => $cron_start,
											'end'		  => $cron_end,
											'total'		=> $x,
										); // $cron_email_args
					f_mdjm_cron_email( $cron_email_args );
				}// if( $mdjm_schedules['client-feedback']['options']['notify_admin'] == Y )
				
				// DJ notification emails
				if( isset( $mdjm_schedules['client-feedback']['options']['notify_dj'] ) && $mdjm_schedules['client-feedback']['options']['notify_dj'] == 'Y' )	{
					foreach( $notify['dj'] as $notify_dj )	{
						foreach( $notify_dj as $dj )	{
							$cron_email_args = array(
													'type'		  => 'notify',
													'to'		    => $djinfo->user_email,
													'subject'  	   => 'Task "Client Feedback" Completed ' . $mdjm_options['company_name'],
													'template'	  => $mdjm_schedules['client-feedback']['options']['email_template'],
													'djinfo'	    => $djinfo,
													'data'		  => $notify_dj,
													'taskinfo'	  => $mdjm_schedules['client-feedback'],
													'total'		 => $x,
												); // $cron_email_args
												
							f_mdjm_cron_email( $cron_email_args );
						} // foreach( $notify_dj as $event )
						
					} // foreach( $notify['dj'] as $dj )
				
				}// if( $mdjm_schedules['client-feedback']['options']['notify_dj'] == Y )
				
			} // if( $eventlist )
			
			/* Update the cron task with run times */
			f_mdjm_cronjob_update( $mdjm_schedules['client-feedback']['slug'] );
			
		} // if( $mdjm_schedules['client-feedback']['active'] == 'Y'...
	} // f_mdjm_cron_client_feedback
	
/***** CUSTOM TASKS *****/

/***** APPLICATION TASKS *****/
/*
* f_mdjm_cronjob_update
* 13/11/2014
* @since 0.9.3
* Update tasks meta data once completed
*/
	function f_mdjm_cronjob_update( $task )	{
		$mdjm_schedules = get_option( 'mdjm_schedules' );
		
		$mdjm_schedules[$task]['lastran'] = time();
		$time = time();
		
		if( isset( $mdjm_schedules[$task]['frequency'] ) && $mdjm_schedules[$task]['frequency'] == 'Hourly')	{
			$mdjm_schedules[$task]['nextrun'] = strtotime( "+1 hour", $time );
		}
		elseif( isset( $mdjm_schedules[$task]['frequency'] ) && $mdjm_schedules[$task]['frequency'] == 'Daily')	{
			$mdjm_schedules[$task]['nextrun'] = strtotime( "+1 day", $time );
		}
		elseif( isset( $mdjm_schedules[$task]['frequency'] ) && $mdjm_schedules[$task]['frequency'] == 'Weekly')	{
			$mdjm_schedules[$task]['nextrun'] = strtotime( "+1 week", $time );
		}
		elseif( isset( $mdjm_schedules[$task]['frequency'] ) && $mdjm_schedules[$task]['frequency'] == 'Monthly')	{
			$mdjm_schedules[$task]['nextrun'] = strtotime( "+1 month", $time );
		}
		elseif( isset( $mdjm_schedules[$task]['frequency'] ) && $mdjm_schedules[$task]['frequency'] == 'Yearly')	{
			$mdjm_schedules[$task]['nextrun'] = strtotime( "+1 year", $time );
		}
		else	{ /* It should not run again */
			$mdjm_schedules[$task]['nextrun'] = 'N/A';
		}
		$mdjm_schedules[$task]['totalruns'] = $mdjm_schedules[$task]['totalruns'] + 1;

		update_option( 'mdjm_schedules', $mdjm_schedules );
	} // f_mdjm_cronjob_update

/*
* f_mdjm_cron_email
* 13/11/2014
* @since 0.9.3
* Sends task emails
*/
	function f_mdjm_cron_email( $cron_email_args )	{
		global $mdjm_options;
		
		if( !isset( $db_tbl) )
			include( WPMDJM_PLUGIN_DIR . '/includes/config.inc.php' );
		
		if( $cron_email_args['type'] == 'client' )	{
			$headers = f_mdjm_cron_get_fromaddr( $cron_email_args );
			if( $cron_email_args['taskinfo']['options']['email_from'] == 'dj' )	{
				$headers .= 'Reply-To: ' . $cron_email_args['djinfo']->user_email . "\r\n";
			}
			if( isset( $cron_email_args['taskinfo']['options']['email_client'] ) && $cron_email_args['taskinfo']['options']['email_client'] == 'Y' )	{
				if( $mdjm_options['bcc_dj_to_client'] == 'Y' || $mdjm_options['bcc_dj_to_client'] == 'Y' )	{
					$headers .= 'Bcc: ';
				}
				if( $mdjm_options['bcc_dj_to_client'] == 'Y' )	{
					$headers .= $cron_email_args['djinfo']->user_email . '>' . "\r\n";
				}
				if( $mdjm_options['bcc_dj_to_client'] == 'Y' && $mdjm_options['bcc_dj_to_client'] == 'Y' )	{
					$headers .= ', ';
				}
		
				if( $mdjm_options['bcc_admin_to_client'] == 'Y' )	{
					if( $cron_email_args['taskinfo']['slug'] != 'complete-events'
						&& $cron_email_args['taskinfo']['slug'] != 'fail-enquiry' )	{
						$headers .= $mdjm_options['system_email'] . "\r\n";	
					}
				}
			}
		}
		else	{
			$headers = 'From: ' . get_bloginfo( 'name' ) . ' <' . $mdjm_options['system_email'] . '>' . "\r\n";
		}
		/* No email template */
		if( $cron_email_args['type'] == 'notify' )	{ // No email template
			/* Set the content */
			$content = 'The ' . $cron_email_args['taskinfo']['name'] . ' scheduled task from ' . $mdjm_options['company_name'] . ' has completed. ' . "\n";
			$content .= "\n";
			$content .= 'Task Start time: ' . date( 'H:i:s l, jS F Y', $cron_email_args['start'] ) . "\n";
			$content .= "\n";
			
			/* Build the email content */
			if( $cron_email_args['taskinfo']['slug'] == 'complete-events' )	{
				$content .= $cron_email_args['total'] . ' event(s) have been marked as completed...' . "\n";
				$content .= "\n";
				$content .= '----------------------------------------';
				$content .= '----------------------------------------' . "\n";
					foreach ( $cron_email_args['data'] as $eventinfo )	{
						$content .= 'Event ID: ' . $eventinfo['id'] . "\n";
						$content .= 'Date: ' . $eventinfo['date'] . "\n";
						$content .= 'Venue: ' . $eventinfo['venue'] . "\n";
						$content .= 'Client: ' . $eventinfo['client'] . "\n";
						$content .= 'DJ: ' . $cron_email_args['djinfo']->display_name . "\n";
						$content .= 'Link: ' . admin_url( 'admin.php?page=mdjm-events&action=view_event_form&event_id=' . $eventinfo['id'] ) . "\n";
						$content .= '----------------------------------------';
						$content .= '----------------------------------------' . "\n";
					}
			}
			elseif( $cron_email_args['taskinfo']['slug'] == 'fail-enquiry' )	{
				$content .= $cron_email_args['total'] . ' enquiry(s) have been marked as lost...' . "\n";
				$content .= "\n";
				$content .= '----------------------------------------';
				$content .= '----------------------------------------' . "\n";
					foreach ( $cron_email_args['data'] as $eventinfo )	{
						$content .= 'Event ID: ' . $eventinfo['id'] . "\n";
						$content .= 'Date: ' . $eventinfo['date'] . "\n";
						$content .= 'Client: ' . $eventinfo['client'] . "\n";
						$content .= 'DJ: ' . $cron_email_args['djinfo']->display_name . "\n";
						$content .= 'Link: ' . admin_url( 'admin.php?page=mdjm-events&action=view_event_form&event_id=' . $eventinfo['id'] ) . "\n";
						$content .= '----------------------------------------';
						$content .= '----------------------------------------' . "\n";
					}
			}
			elseif( $cron_email_args['taskinfo']['slug'] == 'request-deposit' )	{
				$content .= $cron_email_args['total'] . ' deposit requests have been sent' . "\n";
				$content .= "\n";
				$content .= '----------------------------------------';
				$content .= '----------------------------------------' . "\n";
					foreach ( $cron_email_args['data'] as $eventinfo )	{
						$content .= 'Event ID: ' . $eventinfo['id'] . "\n";
						$content .= 'Date: ' . $eventinfo['date'] . "\n";
						$content .= 'Client: ' . $eventinfo['client'] . "\n";
						$content .= 'DJ: ' . $cron_email_args['djinfo']->display_name . "\n";
						$content .= 'Deposit: ' . $mdjm_currency[$mdjm_options['currency']] . $eventinfo['deposit'] . "\n";
						$content .= '----------------------------------------';
						$content .= '----------------------------------------' . "\n";
					}
			}
			elseif( $cron_email_args['taskinfo']['slug'] == 'balance-reminder' )	{
				$content .= $cron_email_args['total'] . ' balance requests have been sent' . "\n";
				$content .= "\n";
				$content .= '----------------------------------------';
				$content .= '----------------------------------------' . "\n";
					foreach ( $cron_email_args['data'] as $eventinfo )	{
						$content .= 'Event ID: ' . $eventinfo['id'] . "\n";
						$content .= 'Date: ' . $eventinfo['date'] . "\n";
						$content .= 'Client: ' . $eventinfo['client'] . "\n";
						$content .= 'DJ: ' . $cron_email_args['djinfo']->display_name . "\n";
						$content .= 'Balance Due: ' . $mdjm_currency[$mdjm_options['currency']] . $eventinfo['cost'] - $eventinfo['deposit'] . "\n";
						$content .= '----------------------------------------';
						$content .= '----------------------------------------' . "\n";
					}
			}
			elseif( $cron_email_args['taskinfo']['slug'] == 'client-feedback' )	{
				$content .= $cron_email_args['total'] . ' client feedback requests have been sent' . "\n";
				$content .= "\n";
				$content .= '----------------------------------------';
				$content .= '----------------------------------------' . "\n";
					foreach ( $cron_email_args['data'] as $eventinfo )	{
						$content .= 'Event ID: ' . $eventinfo['id'] . "\n";
						$content .= 'Date: ' . $eventinfo['date'] . "\n";
						$content .= 'Client: ' . $eventinfo['client'] . "\n";
						$content .= 'DJ: ' . $cron_email_args['djinfo']->display_name . "\n";
						$content .= '----------------------------------------';
						$content .= '----------------------------------------' . "\n";
					}
			}
			$content .= 'Task End time: ' . date( 'H:i:s l, jS F Y', $cron_email_args['end'] ) . "\n";
		}
		/* Use an email template */
		else	{
			/* Required vars for shortcode */
			$eventinfo = f_mdjm_get_eventinfo_by_id( $cron_email_args['eventinfo']->event_id );
			$type = array( 'type' => 'custom', 'id' => $cron_email_args['taskinfo']['options']['email_template'] );
			$info = f_mdjm_prepare_email( $eventinfo, $type );
			$content = $info['content'];
			
			if( !class_exists( 'MDJM_Communication' ) )
				require_once( WPMDJM_PLUGIN_DIR . '/admin/includes/class/class-mdjm-communications.php' );
			
			/* -- Insert the communication post */	
			$mdjm_comms = new MDJM_Communication();
			
			$p = $mdjm_comms->insert_comm( array (
										'subject'	=> wp_strip_all_tags( $cron_email_args['subject'] ),
										'content'	=> $content . '</body></html>',
										'recipient'  => $cron_email_args['to'],
										'source'	 => 'Automated Task',
										'event'	  => $cron_email_args['eventinfo']->event_id,
										) );
										
			$content .= $mdjm_comms->insert_stat_image( $p );
			$content .= "</body>\r\n</html>\r\n";
			
			/* Set HTML Type */
			$headers .= 'MIME-Version: 1.0'  . "\r\n";
			$headers .= 'Content-type: text/html; charset=UTF-8' . "\r\n";
			
			/* Required for shortcodes */
			include( WPMDJM_PLUGIN_DIR . '/admin/includes/config.inc.php' );
		}
		
		/* Fire the email */
		if( wp_mail( $cron_email_args['to'], stripslashes( $cron_email_args['subject'] ), $content, $headers ) )	{
			$mdjm_comms->change_email_status( $p, 'sent' );	
		}
		
	} // f_mdjm_cron_email
	
/*
* f_mdjm_cron_upload_playlists
* 17/11/2014
* @since 0.9.3
* Upload playlist entries to MDJM Servers
*/
	function f_mdjm_cron_upload_playlists()	{
		global $mdjm_options, $wpdb;
		
		if( !isset( $db_tbl ) )	
			include( WPMDJM_PLUGIN_DIR . '/includes/config.inc.php' );
		
		$mdjm_schedules = get_option( 'mdjm_schedules' );
		if( isset( $mdjm_options['upload_playlists'] ) && $mdjm_options['upload_playlists'] == 'Y' 
			&& $mdjm_schedules['upload-playlists']['nextrun'] <= time() )	{

			$cron_start = microtime(true);
		
		// Update the cron job with run times - Limit # jobs per schedule
		
		/* Retrieve plsylist entries not yet transferred */
			$maxrows = 50;
			$pl_query = "SELECT * FROM `" . $db_tbl['playlists'] . "` WHERE `date_to_mdjm` IS NULL OR `date_to_mdjm` = '' ORDER BY `event_id` LIMIT " . $maxrows;
			$playlist = $wpdb->get_results( $pl_query );
			$pl_rows = $wpdb->num_rows;
			if( $pl_rows > 0 )	{ /* We have data to transfer */
				foreach( $playlist as $entry )	{
					/* Get event details - event must be completed */
					$event = $wpdb->get_row( "SELECT * FROM `" . $db_tbl['events'] . "` WHERE `event_id` = '" . $entry->event_id . "' AND `contract_status` = 'Completed'" );
					if( $event != NULL )	{ // We have a result, proceed
						$rpc = 'a=' . urlencode( $entry->artist ) . '&s=' . urlencode( $entry->song ) . '&et=' . urlencode( $event->event_type ) . '&ed=' . $event->event_date . '&da=' . $entry->date_added . '&c=' . urlencode( $mdjm_options['company_name'] ) . '&url=' . urlencode( get_site_url() );
						
						$pl_response = wp_remote_retrieve_body( wp_remote_get( 'http://api.mydjplanner.co.uk/mdjm/pl/pl.php?' . $rpc ) );
						/* Timestamp the playlist record */
						if( $pl_response )	{
							$update_args = array( 'date_to_mdjm' => $pl_response );
							$pl_update = $wpdb->update( 
												$db_tbl['playlists'],
												array( 'date_to_mdjm' => date( 'Y-m-d H:i:s', $pl_response ) ),
												array( 'id' => $entry->id ) );
						} // if( $pl_response )
						
					} // if( $wpdb->num_rows == 1 )
					
				} // foreach( $playlist as $entry )
				
			} // if( $pl_rows->num_rows > 0 )
			
			$cron_end = microtime(true);
			
			// Update task with run times
			f_mdjm_cronjob_update( $mdjm_schedules['upload-playlists']['slug'] );
			
		} // if( $mdjm_options['upload_playlists']...
		
	} // f_mdjm_cron_upload_playlists

?>