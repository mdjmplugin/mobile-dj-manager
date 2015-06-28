<?php
/**
 * * * * * * * * * * * * * * * MDJM * * * * * * * * * * * * * * *
 * Functions that are used mainly within the frontend
 * may also be called from the backend
 *
 *
 * @since 1.0
 *
 */
 
/****************************************************************************************************
--	GENERAL FUNCTIONS
****************************************************************************************************/

	
/**
 * f_mdjm_get_options
 * Retrieves general MDJM meta settings
 * 
 * 
 * Called from: plugin main file & made global to both
 * frontend and backend
 * @since 1.0
*/
	function f_mdjm_get_options()	{
		$mdjm_options = get_option( WPMDJM_SETTINGS_KEY );
		$mdjm_permissions = get_option( 'mdjm_plugin_permissions' );
		$mdjm_pages = get_option( 'mdjm_plugin_pages' );
		$mdjm_client_text = get_option( WPMDJM_FETEXT_SETTINGS_KEY );
		$mdjm_pp_options = get_option( 'mdjm_pp_options' );
		if( !empty( $mdjm_pages ) )	{
			foreach( $mdjm_pages as $key => $value )	{
				$mdjm_options[$key] = $value;
			}
		}
		if( !empty( $mdjm_permissions ) )	{
			foreach( $mdjm_permissions as $key => $value )	{
				$mdjm_options[$key] = $value;
			}
		}
		if( !empty( $mdjm_client_text ) )	{
			foreach( $mdjm_client_text as $key => $value )	{
				$mdjm_options[$key] = $value;
			}
		}
		if( !empty( $mdjm_pp_options ) )	{
			foreach( $mdjm_pp_options as $key => $value )	{
				$mdjm_options[$key] = $value;
			}
		}
		
		/* Make sure we have a system email value */
		if( empty( $mdjm_options['system_email'] ) )	{
			$mdjm_options['system_email'] = get_bloginfo( 'admin_email' );	
		}
		
		return $mdjm_options;
	} // f_get_mdjm_options
	
/*
* f_mdjm_client_text
* 03/12/2014
* @since 0.9.7
* Shortcode replacement for frontend client text
*/
	function f_mdjm_client_text()	{
		global $mdjm_options, $mdjm_client_text;
		
		$args = func_get_args();
		
		if( !is_user_logged_in() && isset( $args[1] ) )	{
			$eventinfo = f_mdjm_get_eventinfo_by_id( $args[1] );
			$client = get_userdata( $eventinfo->user_id );
		}
		else	{
			$client = get_userdata( get_current_user_id() );
		}
		
		$search = array(
						'{APPLICATION_HOME}',                         /* Client Home URL */
						'{APPLICATION_NAME}',                         /* Application Name */
						'{CLIENT_FIRSTNAME}',                         /* Client First Name */
						'{CLIENT_LASTNAME}',                          /* Client's last name */
						'{COMPANY_NAME}',                             /* Company Name */
						'{CONTACT_PAGE}',                             /* Contact Page */
						'{CONTRACT_PAGE}',                            /* Contract Page */
						'{PLAYLIST_PAGE}',                            /* Playlist Page */
						'{PROFILE_PAGE}',                             /* Profile Page */
					);
		$replace = array(
						get_permalink( WPMDJM_CLIENT_HOME_PAGE ),      /* {APPLICATION_HOME} */
						$mdjm_options['app_name'],                     /* {APPLICATION_NAME} */
						$client->first_name,                           /* {CLIENT_FIRSTNAME} */
						$client->last_name,                            /* {CLIENT_LASTNAME} */
						WPMDJM_CO_NAME,                                /* {COMPANY_NAME} */
						get_permalink( WPMDJM_CONTACT_PAGE ),          /* {CONTACT_PAGE} */
						get_permalink( WPMDJM_CLIENT_CONTRACT_PAGE ),  /* {CONTRACT_PAGE} */
						get_permalink( WPMDJM_CLIENT_PLAYLIST_PAGE ),  /* {PLAYLIST_PAGE} */
						get_permalink( WPMDJM_CLIENT_PROFILE_PAGE ),   /* {PROFILE_PAGE} */						
					);
					
		if( isset( $args[1] ) )	{
			$eventinfo = f_mdjm_get_eventinfo_by_id( $args[1] );
			
			/* Set the URL's */
			if ( get_option('permalink_structure') )	{
				$playlist_url = get_permalink( $mdjm_options['playlist_page'] ) . '?mdjmeventid=' . $eventinfo->event_guest_call;
			}
			else	{
				$playlist_url = get_permalink( $mdjm_options['playlist_page'] ) . '&mdjmeventid=' . $eventinfo->event_guest_call;
			}
		
			$event_search = array(
						'{EVENT_TYPE}',           /* Event Type */
						'{EVENT_DATE}',           /* Event Date (Long) */
						'{EVENT_DATE_SHORT}',     /* Event Date (Short) */
						'{START_TIME}',           /* Event Start Time */
						'{END_TIME}',             /* Event End Time */
						'{GUEST_PLAYLIST_URL}',   /* Guest Playlist URL */
						'{PLAYLIST_URL}'          /* Guest Playlist URL */
						);
			$event_replace = array(
						$eventinfo->event_type, /* {EVENT_TYPE} */
						date( 'l, jS F Y', strtotime( $eventinfo->event_date ) ),                    /* {EVENT_DATE} */
						date( 'd/m/Y', strtotime( $eventinfo->event_date ) ),                        /* {EVENT_DATE_SHORT} */
						date( $mdjm_options['time_format'], strtotime( $eventinfo->event_start ) ),  /* {START_TIME} */
						date( $mdjm_options['time_format'], strtotime( $eventinfo->event_finish ) ), /* {END_TIME} */
						$playlist_url,                                                               /* {GUEST_PLAYLIST_URL} */
						$playlist_url,                                                               /* {PLAYLIST_URL} */
						);
						
			/* We need to merge the arrays */
			$search = array_merge( $search, $event_search );
			$replace = array_merge( $replace, $event_replace );
		}
					
		echo nl2br( str_replace( $search, $replace, $mdjm_client_text[$args[0]] ) );
	}

/****************************************************************************************************
--	USER FUNCTIONS
****************************************************************************************************/
/**
 * f_mdjm_show_user_login_form
 * Show the user login form on the frontend
 * 
 * 
 * Called from: all frontend pages
 * frontend
 * @since 1.0
*/
	function f_mdjm_show_user_login_form()	{
		global $mdjm_client_text;
		echo '<p>';
		if( isset( $mdjm_client_text['custom_client_text'] ) && $mdjm_client_text['custom_client_text'] == 'Y' )	{
			f_mdjm_client_text( 'not_logged_in' );
		}
		else	{
			echo '<p>You must be logged in to enter this area of the website. Please enter your username and password below to continue, or use the menu items above to navigate to another area of our website.</p>';
		}
		echo '</p>';
		wp_login_form();
	} // f_mdjm_show_user_login_form

/**
 * f_mdjm_add_lost_password_link
 * Show the lost password link on the login form
 * 
 * 
 * Called from: add_action
 * frontend
 * @since 1.0
*/
	/*add_action( 'login_form_middle', 'f_mdjm_add_lost_password_link' );
	function f_mdjm_add_lost_password_link() {
		return '<a href="/wp-login.php?action=lostpassword">Lost Password?</a>';
	}*/

/**
 * f_mdjm_update_user_profile
 * Validate fields & update user profile at the frontend
 * 
 * 
 * Called from: edit profile frontend
 * 
 * @since 1.0
*/
	/*function f_mdjm_update_user_profile()	{
		global $_POST, $current_user, $clientzone;
		
		$profile_update_fields = array ( 'ID' => $current_user->ID );
		$profile_update_fields_meta = array ();
		if ( isset( $_POST['first_name'] ) && !empty ( $_POST['first_name'] ) && $_POST['first_name'] != $current_user->first_name )	{
			$profile_update_fields['first_name'] = sanitize_text_field( ucfirst( $_POST['first_name'] ) );
		}
		if ( isset( $_POST['last_name'] ) && !empty ( $_POST['last_name'] ) && $_POST['last_name'] != $current_user->last_name )	{
			$profile_update_fields['last_name'] = sanitize_text_field( ucfirst( $_POST['last_name'] ) );
		}
		if ( isset( $_POST['phone1'] ) && !empty ( $_POST['phone1'] ) && $_POST['phone1'] != $current_user->phone1 )	{
			$profile_update_fields_meta['phone1'] = sanitize_text_field( $_POST['phone1'] );
		}
		if ( isset( $_POST['phone2'] ) && !empty( $_POST['phone2'] ) && $_POST['phone2'] != $current_user->phone2 )	{
			$profile_update_fields_meta['phone2'] = sanitize_text_field( $_POST['phone2'] );
		}
		if ( isset( $_POST['user_email'] ) && !empty( $_POST['user_email'] ) && $_POST['user_email'] != $current_user->user_email )	{
			$profile_update_fields['user_email'] = sanitize_email( $_POST['user_email'] );
		}
		if ( isset( $_POST['address1'] ) && !empty( $_POST['address1'] ) && $_POST['address1'] != $current_user->address1 )	{
			$profile_update_fields_meta['address1'] = sanitize_text_field( $_POST['address1'] );
		}
		if ( isset( $_POST['address2'] ) && !empty( $_POST['address2'] ) && $_POST['address2'] != $current_user->address2 )	{
			$profile_update_fields_meta['address2'] = sanitize_text_field( $_POST['address2'] );
		}
		if ( isset( $_POST['town'] ) && !empty( $_POST['town'] ) && $_POST['town'] != $current_user->town )	{
			$profile_update_fields_meta['town'] = sanitize_text_field( $_POST['town'] );
		}
		if ( isset( $_POST['county'] ) && !empty( $_POST['county'] ) && $_POST['county'] != $current_user->county )	{
			$profile_update_fields_meta['county'] = sanitize_text_field( $_POST['county'] );
		}
		if ( isset( $_POST['postcode'] ) && !empty( $_POST['postcode'] ) && $_POST['postcode'] != $current_user->postcode )	{
			$profile_update_fields_meta['postcode'] = sanitize_text_field( $_POST['postcode'] );
		}
		if ( isset( $_POST['birthday'] ) && !empty( $_POST['birthday'] ) && $_POST['birthday'] != $current_user->birthday && $_POST['birthday'] != 'empty' )	{
			$profile_update_fields_meta['birthday'] = sanitize_text_field( $_POST['birthday'] );
		}
		if ( isset( $_POST['new_password'] ) && !empty( $_POST['new_password'] ) && $_POST['new_password'] != $_POST['new_password_confirm'] )	{
			$pass_error = true;
		}
		if ( isset( $_POST['new_password'] ) && !empty( $_POST['new_password'] ) && $_POST['new_password'] == $_POST['new_password_confirm'] )	{
			$profile_update_fields['user_pass'] = $_POST['new_password'];
		}
		if( isset( $_POST['marketing'] ) )
			$profile_update_fields_meta['marketing'] = $_POST['marketing'];
		
		/* Process any custom fields that have been added */
		/*$custom_fields = get_option( WPMDJM_CLIENT_FIELDS );
		foreach ( $custom_fields as $custom_field )	{
			if( $custom_field['default'] == false && $custom_field['display'] == 'Y' )	{
				$profile_update_fields_meta[$custom_field['id']] = $_POST[$custom_field['id']];
			}
		}
		$user_update = wp_update_user ( $profile_update_fields );
		foreach ( $profile_update_fields_meta as $meta_key => $meta_value ) {
			$user_update_meta = update_user_meta ( $current_user->ID, $meta_key, $meta_value );
		}

		if( isset( $profile_update_fields['user_pass'] ) )	{
			wp_logout();
			wp_redirect( get_permalink() );
		}

		if( is_wp_error( $user_update ) ) {
			$clientzone->display_notice(
										4,
										'Unable to update your profile. ' . $user_update->get_error_message()
										);
		}
		else {
			$clientzone->display_notice(
										2,
										'Your profile has been updated successfully'
										);
		}
		if( isset( $pass_error ) && $pass_error == true )	{
			$clientzone->display_notice(
										4,
										'Unable to change your password. Check the password\'s you entered match!'
										);
		}
	} // f_mdjm_update_user_profile*/

/****************************************************************************************************
--	EVENT FUNCTIONS
****************************************************************************************************/
/**
 * f_mdjm_get_eventinfo
 * Retrieve event info for current user
 * 
 * 
 * Called from: frontend application pages
 * 
 * @since 1.0
*/
	function f_mdjm_get_eventinfo( $db_tbl, $current_user )	{
		global $wpdb;
		$eventinfo = $wpdb->get_row("SELECT * FROM `".$db_tbl['events']."` WHERE `user_id` = '".$current_user->ID."' AND `event_date` >= DATE(NOW()) AND `contract_status` = 'Approved'");
		
		return $eventinfo;
	} // f_mdjm_get_eventinfo

/*
* f_mdjm_get_client_events
* 25/11/2014
* @since 0.9.4
* Retrieves all client events regardless of status
*/
	function f_mdjm_get_client_events( $db_tbl, $current_user )	{
		global $wpdb;
		
		$event_query = "SELECT * FROM `" . $db_tbl['events'] . "` WHERE `user_id` = '" . $current_user->ID . "' ORDER BY `event_date` DESC";
		
		$eventinfo = $wpdb->get_results( $event_query );
		
		return $eventinfo;
	} // f_mdjm_get_client_events
	
/*
* f_mdjm_get_client_next_event
* 26/11/2014
* @since 0.9.4
* Retrieves the clients next approved event
*/
	function f_mdjm_get_client_next_event()	{
		global $wpdb;
		
		if( !isset( $db_tbl ) )
			include( WPMDJM_PLUGIN_DIR . '/includes/config.inc.php' );
		
		$event_query = "SELECT * FROM `" . $db_tbl['events'] . "` WHERE `user_id` = '" . get_current_user_id() . "' AND `contract_status` = 'Approved' ORDER BY `event_date` DESC LIMIT 1";
		
		$next_event = $wpdb->get_row( $event_query );
		
		return $next_event;
	} // f_mdjm_get_client_events

/*
* f_mdjm_total_client_events_by_status
* 26/11/2014
* @since 0.9.4
* Retrieves the total number of client events by given status
*/

	function f_mdjm_total_client_events_by_status( $status )	{
		global $wpdb;
		
		if( !isset( $db_tbl ) )
			include( WPMDJM_PLUGIN_DIR . '/includes/config.inc.php' );
			
		$event_query = "SELECT COUNT(*) FROM `" . $db_tbl['events'] . "` WHERE `user_id` = '" . get_current_user_id() . "' AND `contract_status` = '" .$status . "'";
		
		$total_events = $wpdb->get_var( $event_query );
		
		return $total_events;
		
	} // f_mdjm_total_client_events_by_status

/**
 * f_mdjm_get_event_by_id
 * Retrieve event info by given ID
 * 
 * 
 * Called from: frontend application pages
 * 
 * @since 1.0
*/
	function f_mdjm_get_event_by_id( $db_tbl, $event_id )	{
		global $wpdb;
		
		$eventinfo = $wpdb->get_row("SELECT * FROM `" . $db_tbl['events'] . "` WHERE `event_id` = '" . $event_id . "'");
		
		return $eventinfo;
	} // f_mdjm_get_event_by_id
	
/*
* f_mdjm_client_event_by_id
* 26/11/2014
* @since 0.9.4
* Retrieve client event info by id
*/
	function f_mdjm_client_event_by_id( $event_id )	{
					
		$event_query = "SELECT * FROM `" . $db_tbl['events'] . "` WHERE `event_id` = '" . $event_id . "'";
		$eventinfo = $wpdb->get_row( $event_query );
		
		return $eventinfo;	
	} // f_mdjm_client_event_by_id
	
/**
* f_mdjm_get_guest_eventinfo
* 04/10/2014
* @since 0.8
* Retrieve event info guest visitors
*/
	function f_mdjm_get_guest_eventinfo( $event_id )	{		
		
		$eventinfo = get_posts( array(
							'posts_per_page'	=> -1,
							'post_type'			=> MDJM_EVENT_POSTS,
							'meta_key'			=> '_mdjm_event_playlist_access',
							'meta_value'		=> $event_id
							) );
		
		return $eventinfo[0];
	} // f_mdjm_get_guest_eventinfo
	
/*
* f_mdjm_change_event_status
* 26/11/2014
* @since 0.9.4
* Updates the event status
*/
	function f_mdjm_change_event_status( $status, $event_id )	{
		global $wpdb, $mdjm_options;
		include( WPMDJM_PLUGIN_DIR . '/includes/config.inc.php' );
		
		if( $status == 'Pending' )	{
			$update_args = array(
						'contract_status'    => $status,
						'converted_by' => get_current_user_id(),
						'date_converted' => date( 'Y-m-d H:i:s' ),	
						'last_updated_by'    => get_current_user_id(),
						'last_updated'       => date( 'Y-m-d H:i:s' )
					);
			$j_entry = 'The Event has been converted from an enquiry';
		}
		if( $status == 'Approved' )	{
			$update_args = array(
						'contract_status'    => $status,
						'last_updated_by'    => get_current_user_id(),
						'last_updated'       => date( 'Y-m-d H:i:s' )
					);
			$j_entry = 'The Contract has been signed';
		}
		if( $status == 'Cancelled' )	{
			$update_args = array(
						'contract_status'    => $status,
						'last_updated_by'    => get_current_user_id(),
						'last_updated'       => date( 'Y-m-d H:i:s' )
					);
			$j_entry = 'The Event has been cancelled';
		}
		
		/* Update the event in the database */
		$update_event = $wpdb->update( $db_tbl['events'], $update_args, array( 'event_id' => $event_id ) );
		$j_args = array (
						'client' => get_current_user_id(),
						'event' => $event_id,
						'author' => get_current_user_id(),
						'type' => 'Update Event',
						'source' => 'Admin',
						'entry' => $j_entry,
					);
		if( WPDJM_JOURNAL == 'Y' ) f_mdjm_do_journal( $j_args );
		
		/* Email client if required */
		if( $status == 'Pending' || $status == 'Approved' )	{
			
			$eventinfo = f_mdjm_get_eventinfo_by_id( $event_id );
			$clientinfo = get_userdata( $eventinfo->user_id );
			
			if( $status == 'Pending' )	{
				$message = 'Thank you. Your event has been updated and your contract has been issued.';
				if( isset( $mdjm_options['contract_to_client'] ) && $mdjm_options['contract_to_client'] == 'Y' )	{
					$message .= 'You will receive confirmation via email shortly.';
					$type = 'email_contract';
					$set_from = $mdjm_options['contract_email_from'];
					$subject = 'Your DJ Booking';
					$j_entry = 'Contract Review email sent to client';
					
					$email_headers = f_mdjm_client_email_headers( $eventinfo, $set_from );
					$info = f_mdjm_prepare_email( $eventinfo, $type );
					
					/* -- Insert the communication post */
					if( !class_exists( 'MDJM_Communication' ) )
						require_once( WPMDJM_PLUGIN_DIR . '/admin/includes/class/class-mdjm-communications.php' );
						
					$mdjm_comms = new MDJM_Communication();
					
					$p = $mdjm_comms->insert_comm( array (
												'subject'	=> wp_strip_all_tags( $subject ),
												'content'	=> $info['content'],
												'recipient'  => $eventinfo->user_id,
												'source'	 => $mdjm_options['app_name'],
												'event'	  => $eventinfo->event_id,
												'author'	 => get_current_user_id(),
												) );
					
					$info['content'] .= $mdjm_comms->insert_stat_image( $p );
					$info['content'] .= "</body>\r\n</html>\r\n";
					
					if( wp_mail( $info['client']->user_email, $subject, $info['content'], $email_headers ) ) 	{
						$j_args = array (
							'client' => $eventinfo->user_id,
							'event' => $eventinfo->event_id,
							'author' => get_current_user_id(),
							'type' => 'Email Client',
							'source' => 'Admin',
							'entry' => $j_entry,
							);
						if( WPDJM_JOURNAL == 'Y' ) f_mdjm_do_journal( $j_args );
						$mdjm_comms->change_email_status( $p, 'sent' );
					}
				}
			}
			if( $status == 'Approved' )	{
				$message = 'Thank you. Your event is now confirmed.';
				if( isset( $mdjm_options['booking_conf_to_client'] ) && $mdjm_options['booking_conf_to_client'] == 'Y' )	{
					$message .= ' You will receive confirmation via email shortly.';
					$type = 'email_client_confirm';
					$set_from = $mdjm_options['confirm_email_from'];
					$subject = 'DJ Booking Confirmation';
					$j_entry = 'Booking confirmation email sent to client';
					
					$email_headers = f_mdjm_client_email_headers( $eventinfo, $set_from );
					$info = f_mdjm_prepare_email( $eventinfo, $type );
					/* -- Insert the communication post */
					if( !class_exists( 'MDJM_Communication' ) )
						require_once( WPMDJM_PLUGIN_DIR . '/admin/includes/class/class-mdjm-communications.php' );
						
					$mdjm_comms = new MDJM_Communication();
					$p = $mdjm_comms->insert_comm( array (
												'subject'	=> wp_strip_all_tags( $subject ),
												'content'	=> $info['content'],
												'recipient'  => $eventinfo->user_id,
												'source'	 => $mdjm_options['app_name'],
												'event'	  => $eventinfo->event_id,
												'author'	 => get_current_user_id(),
												) );
												
					$info['content'] .= $mdjm_comms->insert_stat_image( $p );
					$info['content'] .= "</body>\r\n</html>\r\n";
					if( wp_mail( $info['client']->user_email, $subject, $info['content'], $email_headers ) ) 	{
						$j_args = array (
							'client' => $eventinfo->user_id,
							'event' => $eventinfo->event_id,
							'author' => get_current_user_id(),
							'type' => 'Email Client',
							'source' => 'Admin',
							'entry' => $j_entry,
							);
						if( WPDJM_JOURNAL == 'Y' ) f_mdjm_do_journal( $j_args );
						$mdjm_comms->change_email_status( $p, 'sent' );
					}
				}
			}
		}
		if( $status == 'Approved' )	{
			if( isset( $mdjm_options['booking_conf_to_dj'] ) && $mdjm_options['booking_conf_to_dj'] == 'Y' )	{
				$email_headers = f_mdjm_dj_email_headers( $eventinfo->event_dj );
				$info = f_mdjm_prepare_email( $eventinfo, $type='email_dj_confirm' );
				
				/* -- Insert the communication post */
				if( !class_exists( 'MDJM_Communication' ) )
					require_once( WPMDJM_PLUGIN_DIR . '/admin/includes/class/class-mdjm-communications.php' );
					
				$mdjm_comms = new MDJM_Communication();
				$p = $mdjm_comms->insert_comm( array (
													'subject'	=> wp_strip_all_tags( 'DJ Booking Confirmed' ),
													'content'	=> $info['content'],
													'recipient'  => $eventinfo->event_dj,
													'source'	 => $mdjm_options['app_name'],
													'event'	  => $eventinfo->event_id,
													'author'	 => get_current_user_id(),
													) );
				
				if( wp_mail( $info['dj'], 'DJ Booking Confirmed', $info['content'], $email_headers ) )	{
					$mdjm_comms->change_email_status( $p, 'sent' );
				}
			}
		}
		
		echo '<p><strong>' . $message . '</strong></p>';
	} // f_mdjm_change_event_status

/****************************************************************************************************
--	CLIENT FUNCTIONS
****************************************************************************************************/

/*
* f_mdjm_profile_complete
* 25/11/2014
* @since 0.9.4
* Checks for completness of the client profile
* @param: client WP user ID
* @return: true : false
*/
	/*function f_mdjm_profile_complete( $client_id )	{
		$client_data = get_userdata( $client_id );
		/* No data = false */
		/*if( !$client_data )
			return false;
		$required = array(
						'first_name',
						'last_name',
						'user_email',
						'phone1',
						'address1',
						'town',
						'county',
						'postcode',
						);
		foreach( $required as $meta )	{
			if( !isset( $client_data->$meta ) || empty( $client_data->$meta ) )	{
				return false;	
			}
		}
		return true;
	} // f_mdjm_profile_complete*/

/****************************************************************************************************
--	CONTRACT FUNCTIONS
****************************************************************************************************/
/**
 * f_mdjm_client_approve_contract
 * Mark the contract as signed
 * 
 * 
 * Called from: frontend contract page
 * 
 * @since 1.0
*/
	/*function f_mdjm_client_approve_contract( $eventinfo, $input )	{
		global $wpdb;
		
		if( !isset( $eventinfo ) || !isset( $input ) )	{
			wp_die( 'An error has occured. Please contact the <a href="mailto:' . $mdjm_options['system_email'] . '">website administrator</a><br />' . $wpdb->print_error() );
		}
		
		if( !isset( $db_tbl ) )
			include( WPMDJM_PLUGIN_DIR . '/includes/config.inc.php' );
		
		$approver = get_user_by( 'display_name', $input['approver'] );
		if( !isset( $input['deposit'] ) || $input['deposit'] == '' || empty( $input['deposit'] ) )	{
			$input['deposit'] = 'Due';
		}
		$update = array(  
							'contract_status' => 'Approved',
							'contract_approved_date' => date( 'Y-m-d' ),
							'contract_approver' => $input['approver'],
							'deposit_status' => $input['deposit'],
							'last_updated_by' => get_current_user_id(),
							'last_updated' => date( 'Y-m-d H:i:s' ),
						);
		if( $wpdb->update( $db_tbl['events'], $update, array( 'event_id' => $eventinfo->event_id ) ) )	{
			$j_args = array (
					'client' => $eventinfo->user_id,
					'event' => $eventinfo->event_id,
					'author' => get_current_user_id(),
					'type' => 'Update Event',
					'source' => 'Website',
					'entry' => 'The Event contract has been signed'
				);
			if( WPDJM_JOURNAL == 'Y' ) f_mdjm_do_journal( $j_args );
			echo '<p><strong>You have successfully signed the contract. Thank you.</strong></p>';										
		}
		else	{
			wp_die( 'An error has occured. Please contact the <a href="mailto:' . $mdjm_options['system_email'] . '">website administrator</a><br />' . $wpdb->print_error() );
		}
	} // f_mdjm_client_approve_contract*/

/****************************************************************************************************
--	DJ FUNCTIONS
****************************************************************************************************/
/**
 * f_mdjm_get_dj_info
 * Retrieve dj info from event
 * 
 * 
 * Called from: frontend
 * 
 * @since 1.0
*/
	function f_mdjm_get_djinfo( $db_tbl, $eventinfo )	{
		global $wpdb;
		$djinfo = get_user_by( 'id', $eventinfo->event_dj );
		return $djinfo;
	} // f_mdjm_get_djinfo

/****************************************************************************************************
--	PLAYLIST FUNCTIONS
****************************************************************************************************/
/*
* f_mdjm_is_playlist_open
* 26/11/2014
* @since 0.9.4
* Checks whether the playlist is open
* return: true : false
*/
	function f_mdjm_is_playlist_open( $event_date )	{
		global $mdjm_settings;
		
		/* Playlist never closes */
		if( empty( $mdjm_settings['playlist']['playlist_close'] ) || $mdjm_settings['playlist']['playlist_close'] == 0 )	{
			return true;	
		}
		else	{
			$pl_close = strtotime( $event_date ) - ( $mdjm_settings['playlist']['playlist_close'] * DAY_IN_SECONDS );
			if( time() > $pl_close ) 	{
				return false; // Closed
			}
			else	{
				return true; // Open
			}
		}
		
	} // f_mdjm_is_playlist_open

/**
 * f_mdjm_get_playlist
 * Retrieve playlist entries for event
 * 
 * 
 * Called from: frontend
 * 
 * @since 1.0
*/
	function f_mdjm_get_playlist( $event_id )	{
		global $wpdb;
		
		if( !isset( $db_tbl ) )
			include( WPMDJM_PLUGIN_DIR . '/includes/config.inc.php' );
			
		$playlist_query = "SELECT * FROM `"  .$db_tbl['playlists'] . "` WHERE `event_id` = '" . $event_id . "' ORDER BY `play_when`, `artist`";
		$playlist_result = $wpdb->get_results( $playlist_query );
		
		return $playlist_result;
	} // f_mdjm_get_playlist

/**
 * f_mdjm_remove_playlistsong
 * Delete song from playlist
 * 
 * 
 * Called from: playlist
 * 
 * @since 1.0
*/	
	function f_mdjm_remove_playlistsong( $song_id )	{
		global $mdjm, $clientzone, $wpdb;
				
		$songinfo = $wpdb->get_row( "SELECT * FROM " . MDJM_PLAYLIST_TABLE . " WHERE `id` = '" . $song_id . "'");
		$eventinfo = get_post( $songinfo->event_id );
		
		$playlist_remove = $wpdb->delete( MDJM_PLAYLIST_TABLE, array( 'id' => $song_id ) );	
			if( $playlist_remove > 0 )	{
				$clientzone->display_notice( 2, 'The song ' . $songinfo->song . ' by ' . $songinfo->artist  . ' has been successfully removed from the playlist' );
			}
		if( MDJM_JOURNAL == true )	{
				$mdjm->mdjm_events->add_journal( array(
										'user'				=> get_post_meta( $eventinfo->ID, '_mdjm_event_client', true ),
										'event'				=> $songinfo->event_id,
										'comment_content'	=> 'Song ' . $songinfo->song . ' by ' . $songinfo->artist  . ' removed from playlist',
										'comment_type'		=> 'mdjm-journal',
													),
												 array(
										'type'			=> 'update-event',
										'visibility'	=> '2',
												 	)
												);
		}
	} // f_mdjm_remove_playlistsong

/**
 * f_mdjm_add_playlistsong
 * Add song to playlist
 * 
 * 
 * Called from: playlist
 * 
 * @since 1.0
*/
	function f_mdjm_add_playlistsong( $playlist_array )	{
		global $mdjm, $clientzone, $wpdb;
		// Form validation
		if ( empty ( $playlist_array['playlist_artist'] ) || empty ( $playlist_array['playlist_song'] ) )	{
			print("<p style=\"color:#FF0000\">ERROR: You need to complete both the Artist and Song fields.</p>\n");
		}
		elseif ( isset ( $_POST['first_name'] ) && empty ( $_POST ['first_name'] ) )	{
			print("<p style=\"color:#FF0000\">ERROR: Please enter your first name.</p>\n");	
		}
		elseif ( isset ( $_POST['last_name'] ) && empty ( $_POST ['last_name'] ) )	{
			print("<p style=\"color:#FF0000\">ERROR: Please enter your last name.</p>\n");	
		}
		elseif ( $playlist_array['playlist_when'] == "Other" && empty ( $playlist_array['playlist_info'] ) )	{
			print("<p style=\"color:#FF0000\">ERROR: As you selected \"Other\" from the When to Play field, you must enter some additional information into the Info field.</p>\n");
		}
		else	{ // Insert the record			
			if( isset( $playlist_array['first_name'], $playlist_array['last_name'] ) )	{
				$playlist_array['added_by'] = $playlist_array['first_name'] . ' ' . $playlist_array['last_name'];	
			}
			
			if( !isset( $playlist_array['playlist_when'] ) || $playlist_array['playlist_when'] == '' ) $playlist_array['playlist_when'] = 'General';
			if( $wpdb->insert( MDJM_PLAYLIST_TABLE,
												array(
													'id' =>	'',
													'event_id' => $playlist_array['event_id'],
													'artist' => $playlist_array['playlist_artist'],
													'song' => $playlist_array['playlist_song'],
													'play_when' => $playlist_array['playlist_when'],
													'info' => $playlist_array['playlist_info'],
													'added_by' => $playlist_array['added_by'],
													'date_added' => date( 'Y-m-d' ),
												) ) ) {
				$c_msg = 'The song has been successfully added to your playlist';
				if( !is_user_logged_in() ) $c_msg = 'Thank you. The song ' . $playlist_array['playlist_song'] . ' by ' . $playlist_array['playlist_artist'] . ' has been successfully added to the playlist.';
				$clientzone->display_notice( 2, $c_msg );
			}
			else	{
				$clientzone->display_notice( 4, $wpdb->print_error() );	
			}

			if( MDJM_JOURNAL == true )	{
				$mdjm->mdjm_events->add_journal( array(
										'user'				=> $playlist_array['client_id'],
										'event'				=> $playlist_array['event_id'],
										'comment_content'	=> 'Song added to playlist by ' . $playlist_array['added_by'],
										'comment_type'		=> 'mdjm-journal',
													),
												 array(
										'type'			=> 'update-event',
										'visibility'	=> '2',
												 	)
												);
			}
		}
	} // f_mdjm_add_playlistsong
	
/****************************************************************************************************
--	Contact	--
****************************************************************************************************/
/**
 * f_mdjm_contact
 * Create link for email contact
 * 
 * 
 * Called from: playlist
 * 
 * @since 1.0
*/
	function f_mdjm_contact( $subject )	{
		print("<a href=\"mailto:". $mdjm_options['system_email'] ."?subject=".$subject."\">". $mdjm_options['system_email'] ."</a>");
	} // f_mdjm_contact
	
/****************************************************************************************************
--	JOURNALING
****************************************************************************************************/
/**
 * f_mdjm_do_journal
 * Update the journal entry
 * 
 * 
 * Called from: frontend & backend
 * 
 * @since 1.0
*/
	function f_mdjm_do_journal( $args )	{
		global $wpdb;
		
		$args['id'] = '';
		$args['timestamp'] = time();
		if( !$wpdb->insert( MDJM_JOURNAL_TABLE, $args ) ) die( $wpdb->print_error() );	
	} // f_mdjm_do_journal

/****************************************************************************************************
--	AVAILABILITY
****************************************************************************************************/
/**
* f_mdjm_availability_form
* 27/12/2014
* @since 0.9.9
* Displays the availability checker form
*/
	function f_mdjm_availability_form( $args )	{
		global $mdjm, $mdjm_settings;
		
		if( isset( $_POST['mdjm_avail_submit'] ) && !empty( $_POST['mdjm_avail_submit'] ) )	{
			$dj_avail = dj_available( '', $_POST['check_date'] );
			
			if( isset( $dj_avail ) )	{
				if( !empty( $dj_avail['available'] ) )	{
					if( isset( $mdjm_settings['availability']['availability_check_pass_page'] ) && $mdjm_settings['availability']['availability_check_pass_page'] != 'text' )	{
						?>
						<script type="text/javascript">
						window.location = '<?php echo $mdjm->get_link( $mdjm_settings['availability']['availability_check_pass_page'], true ) . 'mdjm_avail=1&mdjm_avail_date=' . $_POST['check_date']; ?>';
						</script>
                        <p>Please wait...</p>
						<?php
						exit;
					}
				}
				else	{
					if( isset( $mdjm_settings['availability']['availability_check_fail_page'] ) && $mdjm_settings['availability']['availability_check_fail_page'] != 'text' )	{
						?>
						<script type="text/javascript">
						window.location = '<?php echo $mdjm->get_link( $mdjm_settings['availability']['availability_check_fail_page'], false ); ?>';
						</script>
						<?php
						exit;
					}	
				}
			} // if( isset( $dj_avail ) )
		}
		
		/* We need the jQuery Calendar */
		wp_enqueue_script('jquery-ui-datepicker');
		wp_enqueue_style('jquery-ui-css', 'http://ajax.googleapis.com/ajax/libs/jqueryui/1.8.2/themes/smoothness/jquery-ui.css');
		?>
		<script type="text/javascript">
		<?php
		mdjm_jquery_datepicker_script( array( 'custom_date', 'check_date' ) );
		?>
        </script>
        <?php
		/* Create the table */
		?>
        <!-- Start of MDJM Availability Checker -->
        <form name="mdjm-availability-check" id="mdjm-availability-check" method="post">
        <?php
        if( isset( $_POST['mdjm_avail_submit'] ) && !empty( $_POST['mdjm_avail_submit'] ) )	{
			$search = array( '{EVENT_DATE}', '{EVENT_DATE_SHORT}' );
			$replace = array( date( 'l, jS F Y', strtotime( $_POST['check_date'] ) ), 
							date( MDJM_SHORTDATE_FORMAT, strtotime( $_POST['check_date'] ) ) );
			if( !empty( $dj_avail['available'] ) && $mdjm_settings['availability']['availability_check_pass_page'] == 'text' && !empty( $mdjm_settings['availability']['availability_check_pass_page'] ) )	{
				echo '<p>' . str_replace( $search,
										  $replace,
										  $mdjm_settings['availability']['availability_check_pass_text'] ) . '</p>';
			}
			if( empty( $dj_avail['available'] ) && $mdjm_settings['availability']['availability_check_fail_page'] == 'text' && !empty( $mdjm_settings['availability']['availability_check_fail_page'] ) )	{
				echo '<p>' . str_replace( $search,
										  $replace,
										  $mdjm_settings['availability']['availability_check_fail_text'] ) . '</p>';
			}
			
		}
		?>
        <p>
        <?php
        if( !isset( $args['label'] ) || empty( $args['label'] ) )	{
			echo 'Select Date:';
			if( isset( $args['label_wrap'] ) && $args['label_wrap'] == 'true' )	{
				echo '<br />';	
			}
		}
		else	{
			echo $args['label'];
			if( isset( $args['label_wrap'] ) && $args['label_wrap'] == 'true' )	{
				echo '<br />';	
			}	
		}
		if( !isset( $args['submit_text'] ) || empty( $args['submit_text'] ) )	{
			$submit_text = 'Check Date';
		}
		else	{
			$submit_text = $args['submit_text'];
		}
		?>
        <input type="text" name="avail_date" id="avail_date" class="custom_date" placeholder="<?php mdjm_jquery_short_date(); ?>" required />
        <?php
		if( isset( $args['field_wrap'] ) && $args['field_wrap'] == 'true' )	{
				echo '<br />';	
			}
		?>
        <input type="hidden" name="check_date" id="check_date" />
		
        <input type="submit" name="mdjm_avail_submit" id="mdjm_avail_submit" value="<?php echo $submit_text; ?>" />
        </form>
        <script type="text/javascript">
        jQuery(document).ready(function($){
			// Configure the field validator
            $('#mdjm-availability-check').validate(
				{
					rules:
					{
						avail_date: {
							required: true,
						},
					}, // End rules
					messages:
					{
						avail_date: {
								required: "Please enter a date",
								},
					}, // End messages
					// Classes
					errorClass: "mdjm-form-error",
					validClass: "mdjm-form-valid",
				} // End validate
			); // Close validate
        });
		</script>
        <!-- End of MDJM Availability Checker -->
        <?php
	} // f_mdjm_availability_form
?>