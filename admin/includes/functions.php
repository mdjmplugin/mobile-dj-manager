<?php
/**
 * * * * * * * * * * * * * * * MDJM * * * * * * * * * * * * * * *
 * functions.php
 *
 * Admin UI functions
 *
 *
 * @since 1.0
 *
 */

/**************************************************************
-	Menu's
**************************************************************/
/**
 * f_mdjm_admin_menu
 * Create & display the admin menu
 *
 * Called by: add_action
 *
 * @since 1.0
*/
	function f_mdjm_admin_menu()	{
		global $mdjm_options;
		add_menu_page( 'Mobile DJ Manager', 'DJ Manager', 'manage_mdjm', 'mdjm-dashboard', 'f_mdjm_admin_dashboard', 'dashicons-format-audio' );
		add_submenu_page( 'mdjm-dashboard', 'Mobile DJ Manager - Dashboard', 'Dashboard', 'manage_mdjm', 'mdjm-dashboard', 'f_mdjm_admin_dashboard');
		if( current_user_can( 'manage_options' ) ) add_submenu_page( 'mdjm-dashboard', 'Mobile DJ Manager - Settings', 'Settings', 'manage_mdjm', 'mdjm-settings', 'f_mdjm_admin_settings');
		add_submenu_page( 'mdjm-dashboard', 'Mobile DJ Manager - Clients', 'Clients', 'manage_mdjm', 'mdjm-clients', 'f_mdjm_admin_clients');
		if( current_user_can( 'manage_options' ) && $mdjm_options['multiple_dj'] == 'Y' ) add_submenu_page( 'mdjm-dashboard', 'Mobile DJ Manager - DJ\'s ', 'DJ\'s' , 'manage_mdjm', 'mdjm-djs', 'f_mdjm_admin_djs');
		add_submenu_page( 'mdjm-dashboard', 'Mobile DJ Manager - Events', 'Events', 'manage_mdjm', 'mdjm-events', 'f_mdjm_admin_events');
		
	} // f_mdjm_admin_menu

/**************************************************************
-	Admin Pages
**************************************************************/	
/**
 * f_mdjm_admin_dashboard
 * Display the MDJM dashboard
 *
 *
 * @since 1.0
*/
	function f_mdjm_admin_dashboard()	{
		if ( !current_user_can( 'manage_options' ) && !current_user_can( 'manage_mdjm' ) )  {
			wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
		}
		include_once( WPMDJM_PLUGIN_DIR . '/admin/pages/dash.php' );
	} // f_mdjm_admin_dashboard

/**
 * f_mdjm_admin_settings
 * Display the MDJM settings
 *
 *
 * @since 1.0
*/
	function f_mdjm_admin_settings()	{
		global $pagenow;
		if ( !current_user_can( 'manage_options' ) && !current_user_can( 'manage_mdjm' ) )  {
			wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
		}
		include_once( WPMDJM_PLUGIN_DIR . '/admin/pages/settings-general.php' );
	} // f_mdjm_admin_settings

/**
 * f_mdjm_admin_events
 * Display the MDJM events
 *
 *
 * @since 1.0
*/
	function f_mdjm_admin_events()	{
		if ( !current_user_can( 'manage_options' ) && !current_user_can( 'manage_mdjm' ) )  {
			wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
		}
		wp_nonce_field( "mdjm-events-page" );
		include_once( WPMDJM_PLUGIN_DIR . '/admin/pages/events.php' );
	} // f_mdjm_admin_events
	
/**
 * f_mdjm_admin_clients
 * Display the MDJM clients
 *
 *
 * @since 1.0
*/
	function f_mdjm_admin_clients()	{
		if ( !current_user_can( 'manage_options' ) && !current_user_can( 'manage_mdjm' ) )  {
			wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
		}
		wp_nonce_field( "mdjm-clients-page" );
		include_once( WPMDJM_PLUGIN_DIR . '/admin/pages/clients.php' );
	} // f_mdjm_admin_clients
	
/**
 * f_mdjm_admin_djs
 * Display the MDJM DJ's
 *
 *
 * @since 1.0
*/
	function f_mdjm_admin_djs()	{
		if ( !current_user_can( 'manage_options' ) && !current_user_can( 'manage_mdjm' ) )  {
			wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
		}
		wp_nonce_field( "mdjm-djs-page" );
		include_once( WPMDJM_PLUGIN_DIR . '/admin/pages/djs.php' );
	} // f_mdjm_admin_djs
	
/**
 * f_mdjm_admin_settings_tabs
 * Display Tabs on the MDJM settings page
 *
 *
 * @since 1.0
*/
	function f_mdjm_admin_settings_tabs( $current = 'general' )	{
		$tabs = array( 
					'general' => 'General',
					'pages' => 'Pages',
					'permissions' => 'Permissions',
					'client_fields' => 'Client Fields',
				);
		echo '<div id="icon-themes" class="icon32"><br></div>';
    	echo '<h2 class="nav-tab-wrapper">';
		foreach( $tabs as $tab => $name )	{
			$class = ( $tab == $current ) ? ' nav-tab-active' : '';
			echo "<a class='nav-tab$class' href='?page=mdjm-settings&tab=$tab'>$name</a>";
	    }
		echo '</h2>';
	} // f_mdjm_admin_settings_tabs

/**************************************************************
-	DATABASE
**************************************************************/	
/**
 * f_mdjm_db_update
 * Performs an update on the DB Tables after a plugin upgrade (if needed)
 *
 * Called from: 
 *
 * @since 1.0
*/
	function f_mdjm_db_update()	{
		global $wpdb, $mdjm_db_version;
		$current_db_ver = get_option( 'mdjm_db_version' );
		
		if ( $current_db_ver != $mdjm_db_version ) {
		
			include( WPMDJM_PLUGIN_DIR . '/includes/config.inc.php' );
		
			/* EVENTS TABLE */
			$events_sql = "CREATE TABLE ". $db_tbl['events'] . " (
							event_id int(11) NOT NULL AUTO_INCREMENT,
							user_id int(11) NOT NULL,
							event_date date NOT NULL,
							event_dj int(11) NOT NULL,
							event_type varchar(255) NOT NULL,
							event_start time NOT NULL,
							event_finish time NOT NULL,
							event_description text NOT NULL,
							event_guest_call varchar(9) NOT NULL,
							booking_date date DEFAULT NULL,
							contract_status varchar(255) NOT NULL,
							contract int(11) NOT NULL,
							contract_approved_date varchar(255) NOT NULL,
							contract_approver varchar(255) NOT NULL,
							cost decimal(10,2) NOT NULL,
							deposit decimal(10,2) NOT NULL,
							deposit_status enum('Due','Paid','Waivered') NOT NULL,
							venue varchar(255) NOT NULL,
							venue_contact varchar(255) DEFAULT NULL,
							venue_addr1 varchar(255) DEFAULT NULL,
							venue_addr2 varchar(255) NOT NULL,
							venue_city varchar(255) NOT NULL,
							venue_state varchar(255) NOT NULL,
							venue_zip varchar(255) NOT NULL,
							venue_phone varchar(255) DEFAULT NULL,
							venue_email varchar(255) DEFAULT NULL,
							added_by int(11) NOT NULL,
							date_added datetime NOT NULL,
							referrer varchar(255) NOT NULL,
							converted_by int(11) NOT NULL,
							date_converted datetime NOT NULL,
							last_updated_by int(11) NOT NULL,
							last_updated datetime NOT NULL,
							PRIMARY KEY  (event_id),
							KEY user_id (user_id),
							KEY added_by (added_by),
							KEY date_added (date_added,last_updated),
							KEY converted_by (converted_by),
							KEY referrer (referrer)
							) $charset_collate;";
					
			/* JOURNAL TABLE */
			$journal_sql = "CREATE TABLE ". $db_tbl['journal'] . " (
							id int(11) NOT NULL AUTO_INCREMENT,
							client int(11) NOT NULL,
							event int(11) NOT NULL,
							timestamp varchar(255) NOT NULL,
							author varchar(255) NOT NULL,
							type varchar(255) NOT NULL,
							source varchar(255) NOT NULL,
							entry text NOT NULL,
							PRIMARY KEY  (id),
							KEY client (client,event),
							KEY entry_date (timestamp,type),
							KEY author (author)
							) $charset_collate;";
							
			/* PLAYLISTS TABLE */
			$playlists_sql = "CREATE TABLE web_wp_mdjm_playlists (
								id int(11) NOT NULL AUTO_INCREMENT,
								event_id int(11) NOT NULL,
								artist varchar(255) NOT NULL,
								song varchar(255) NOT NULL,
								play_when varchar(255) NOT NULL,
								info text NOT NULL,
								added_by varchar(255) NOT NULL,
								date_added date NOT NULL,
								PRIMARY KEY  (id),
								KEY event_id (event_id),
								KEY artist (artist,song)
								) $charset_collate;";
			
			require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
			dbDelta( $events_sql );
			dbDelta( $journal_sql );
			dbDelta( $playlists_sql );
		
			update_option( "mdjm_db_version", $mdjm_db_version );
		}	
	} // f_mdjm_db_update
	
/**************************************************************
-	STATUS MESSAGES
**************************************************************/
/**
 * f_mdjm_admin_update_notice
 * Display status messages when forms are updated
 *
 *
 * @since 1.0
*/
	function f_mdjm_admin_update_notice( $message_no )	{
		if ( $message_no == 0 )	{ // Success
			$class = "updated";
			$message = "Your settings have been saved successfully";
		}
		else	{ // Failure
			$class = "error";
			$message = "Sorry there was an issue and your settings could not be saved. Please try again.";
		}
		?>
		<div class="<?php echo $class; ?>">
		<p><?php _e( $message, 'my-text-domain' ); ?></p>
		</div>
		<?php
	} // f_mdjm_admin_update_notice
	
/****************************************************************************************************
--	EVENT FUNCTIONS
****************************************************************************************************/
/**
 * f_mdjm_admin_add_event
 * Add a new event (enquiry) to the DB
 *
 *
 * @since 1.0
*/
	function f_mdjm_add_event( $event )	{
		global $wpdb;
		require_once( WPMDJM_PLUGIN_DIR . '/includes/config.inc.php' );
		require_once( WPMDJM_PLUGIN_DIR . '/includes/functions.php' );
		
		$event_date = explode( '/', $event['event_date'] );
		$event_date = $event_date[2] . '-' . $event_date[1] . '-' . $event_date[0];
		$str = f_mdjm_generate_playlist_ref();
		
		if ( $wpdb->insert( $db_tbl['events'],
											array(
												'event_id' =>	'',
												'user_id' => $event['user_id'],
												'event_date' => $event_date,
												'event_dj' => $event['event_dj'],
												'event_type' => sanitize_text_field( $event['event_type'] ),
												'event_start' => $event['event_start'],
												'event_finish' => $event['event_finish'],
												'event_description' => $event['event_description'],
												'event_guest_call' => $str,
												'contract_status' => 'Enquiry',
												'contract' => $event['contract'],
												'cost' => $event['cost'],
												'deposit' => $event['deposit'],
												'deposit_status' => sanitize_text_field( $event['deposit_status'] ),
												'venue' => sanitize_text_field( $event['venue'] ),
												'venue_contact' => sanitize_text_field( $event['venue_contact'] ),
												'venue_addr1' => sanitize_text_field( $event['venue_addr1'] ),
												'venue_addr2' => sanitize_text_field( $event['venue_addr2'] ),
												'venue_city' => sanitize_text_field( $event['venue_city'] ),
												'venue_state' => sanitize_text_field( $event['venue_state'] ),
												'venue_zip' => sanitize_text_field( strtoupper( $event['venue_zip'] ) ),
												'venue_phone' => $event['venue_phone'],
												'venue_email' => sanitize_email( $event['venue_email'] ),
												'added_by' => get_current_user_id(),
												'date_added' => date( 'Y-m-d H:i:s' ),
												'referrer' => sanitize_text_field( $event['enquiry_source'] ),
												'last_updated_by' => get_current_user_id(),
												'last_updated' => date( 'Y-m-d H:i:s' )
											) ) )	{
			$message = 'A new event on ' . date( "l, jS F Y", strtotime( $event_date ) ) . ' has been successfully created';			
			$clientinfo = get_userdata( $event['user_id'] );
			$id = $wpdb->insert_id;
			$j_args = array (
						'client' => $event['user_id'],
						'event' => $wpdb->insert_id,
						'author' => get_current_user_id(),
						'type' => 'Add Event Enquiry',
						'source' => 'Admin',
						'entry' => 'The event has been created'
						);
			if( WPDJM_JOURNAL == 'Y' ) f_mdjm_do_journal( $j_args );
			?>
			<div id="message" class="updated">
			<p><?php _e( $message ) ?></p>
			</div>
            <?php
			if( $event['email_enquiry'] == 'Y' )	{
				$eventinfo = f_mdjm_get_eventinfo_by_id( $id );
				$email_headers = f_mdjm_client_email_headers( $eventinfo );
				$info = f_mdjm_prepare_email( $eventinfo, $type='enquiry' );
				if ( wp_mail( $clientinfo->user_email, 'DJ Enquiry', $info['content'], $email_headers ) ) 	{
					$message = 'Event quotation email successfully sent to client';
					$j_args = array (
						'client' => $event['user_id'],
						'event' => $id,
						'author' => get_current_user_id(),
						'type' => 'Email Client',
						'source' => 'Admin',
						'entry' => 'Quote emailed to client'
						);
					if( WPDJM_JOURNAL == 'Y' ) f_mdjm_do_journal( $j_args );
					?>
                    <div id="message" class="updated">
                    <p><?php _e( $message ) ?></p>
                    </div>
                    <?php
				}
				else	{
					wp_die( $clientinfo->user_email . '<br />DJ Enquiry<br />' . $info['content'] . '<br />' . $email_headers );
				}
			}
		}
		else	{
			die( $wpdb->print_error() );	
		}
	} // f_mdjm_admin_add_event

/**
 * f_mdjm_edit_event
 * Update existing event info
 *
 * @param $event_id (event id)
 * @return true : false
 *
 * @since 1.0
*/
	function f_mdjm_edit_event( $event_updates )	{
		global $wpdb, $mdjm_options;
		require_once( WPMDJM_PLUGIN_DIR . '/includes/config.inc.php' );
		
		$updated_fields = array();
		$event_date = explode( '/', $event_updates['event_date'] );
		$contract_approved_date = explode( '/', $event_updates['contract_approved_date'] );
		$event_updates['event_date'] = $event_date[2] . '-' . $event_date[1] . '-' . $event_date[0];
		$event_updates['contract_approved_date'] = $contract_approved_date[2] . '-' . $contract_approved_date[1] . '-' . $contract_approved_date[0];
		$event_updates['last_updated_by'] = get_current_user_id();
		$event_updates['last_updated'] = date( 'Y-m-d H:i:s' );
		/* Which fields need updating? */
		$eventinfo = f_mdjm_get_eventinfo_by_id( $event_updates['event_id'] );
		if( $eventinfo )	{ // Update
			if( $eventinfo->contract_status != 'Approved' && $event_updates['contract_status'] == 'Approved' ) $now_approved = true;
			if( $eventinfo->contract_status != 'Pending' && $event_updates['contract_status'] == 'Pending' ) $now_pending = true;
			foreach( $event_updates as $key => $value )	{
				if( $key != 'event_id' && $key != 'action' && $key != '_wpnonce' && $key != '_wp_http_referer' && $key != 'submit' )	{
					if( $value != $eventinfo->$key )	{
						$updated_fields[$key] = $value;
						$wpdb->update( $db_tbl['events'], 
							array( $key => $updated_fields[$key] ), 
							array( 'event_id' => $eventinfo->event_id ) );
					}
				}
			}
			$message = 'Event ID ' . $eventinfo->event_id . ' taking place on ' . date( "l, jS F Y", strtotime( $eventinfo->event_date ) ) . ' has been successfully updated';			
			$clientinfo = get_userdata( $eventinfo->user_id );
			$j_args = array (
						'client' => $eventinfo->user_id,
						'event' => $eventinfo->event_id,
						'author' => get_current_user_id(),
						'type' => 'Update Event',
						'source' => 'Admin',
						'entry' => 'The event has been updated'
						);
			if( WPDJM_JOURNAL == 'Y' ) f_mdjm_do_journal( $j_args );
			?>
            <div id="message" class="updated">
			<p><?php _e( $message ) ?></p>
            </div>
            <?php
			if( $now_pending && $mdjm_options['contract_to_client'] == 'Y' )	{
				$email_headers = f_mdjm_client_email_headers( $eventinfo );
				$info = f_mdjm_prepare_email( $eventinfo, $type='contract_review' );
				if ( wp_mail( $info['client']->user_email, 'Your DJ Booking', $info['content'], $email_headers ) ) 	{
					$message = 'Contract email sent to client';
					$j_args = array (
						'client' => $eventinfo->user_id,
						'event' => $eventinfo->event_id,
						'author' => get_current_user_id(),
						'type' => 'Email Client',
						'source' => 'Admin',
						'entry' => 'Contract Review email sent to client'
						);
					if( WPDJM_JOURNAL == 'Y' ) f_mdjm_do_journal( $j_args );
					?>
                    <div id="message" class="updated">
                    <p><?php _e( $message ) ?></p>
                    </div>
                    <?php
				}
				else	{
					$message .= 'Unable to contract review confirmation email to client';
					?>
                    <div id="message" class="error">
                    <p><?php _e( $message ) ?></p>
                    </div>
                    <?php
				}
			}
			if( $now_approved )	{
				$email_headers = f_mdjm_client_email_headers( $eventinfo );
				$info = f_mdjm_prepare_email( $eventinfo, $type='client_booking_confirm' );
				if ( wp_mail( $info['client']->user_email, 'Booking Confirmation', $info['content'], $email_headers ) ) 	{
					$message = 'Booking confirmation email sent to client';
					$j_args = array (
						'client' => $eventinfo->user_id,
						'event' => $eventinfo->event_id,
						'author' => get_current_user_id(),
						'type' => 'Email Client',
						'source' => 'Admin',
						'entry' => 'Booking confirmation email sent to client'
						);
					if( WPDJM_JOURNAL == 'Y' ) f_mdjm_do_journal( $j_args );
					?>
                    <div id="message" class="updated">
                    <p><?php _e( $message ) ?></p>
                    </div>
                    <?php
					$email_headers = f_mdjm_dj_email_headers( $eventinfo->event_dj );
					$info = f_mdjm_prepare_email( $eventinfo, $type='dj_booking_confirm' );
					wp_mail( $info['dj'], 'DJ Booking Confirmed', $info['content'], $email_headers );
				}
				else	{
					$message .= 'Unable to send booking confirmation email to client';
					?>
                    <div id="message" class="error">
                    <p><?php _e( $message ) ?></p>
                    </div>
                    <?php
				}
			}
		}
		else	{ // No event
			?>
			<div id="message" class="error">
			<p><?php _e( 'No information was changed' ) ?></p>
			</div>
            <?php
		}
	}

/**
 * f_mdjm_convert_event
 * Convert selected events to bookings
 *
 * @param $event (event id)
 * @return
 *
 * @since 1.0
*/
	function f_mdjm_convert_event( $event_id )	{
		global $wpdb;
		include( WPMDJM_PLUGIN_DIR . '/includes/config.inc.php' );
		$update = array(
						'contract_status' => 'Pending',
						'converted_by' => get_current_user_id(),
						'date_converted' => date( 'Y-m-d H:i:s' ),
						'last_updated_by' => get_current_user_id(),
						'last_updated' => date( 'Y-m-d H:i:s' )
					);
		$eventinfo = f_mdjm_get_eventinfo_by_id( $event_id );
		$convert_event = $wpdb->update( $db_tbl['events'], $update, array( 'event_id' => $event_id ) );
		$clientinfo = get_userdata( $eventinfo->user_id );
		$j_args = array (
					'client' => $eventinfo->user_id,
					'event' => $event_id,
					'author' => get_current_user_id(),
					'type' => 'Update Event',
					'source' => 'Admin',
					'entry' => 'The Event has been converted from an enquiry'
				);
		if( WPDJM_JOURNAL == 'Y' ) f_mdjm_do_journal( $j_args );
		
		$message = 'The selected enquiry has been successfully converted to a booking';
		echo '<div id="message" class="updated">';
		echo '<p>' . _e( $message ) . '</p>';
		echo '</div>';
		$email_headers = f_mdjm_client_email_headers( $eventinfo );
		$info = f_mdjm_prepare_email( $eventinfo, $type='contract_review' );
		if ( wp_mail( $info['client']->user_email, 'Your DJ Booking', $info['content'], $email_headers ) ) 	{
			$message = 'Contract review email sent to client';
			$j_args = array (
				'client' => $eventinfo->user_id,
				'event' => $eventinfo->event_id,
				'author' => get_current_user_id(),
				'type' => 'Email Client',
				'source' => 'Admin',
				'entry' => 'Contract Review email sent to client'
				);
			if( WPDJM_JOURNAL == 'Y' ) f_mdjm_do_journal( $j_args );
			?>
			<div id="message" class="updated">
			<p><?php _e( $message ) ?></p>
			</div>
			<?php
		}
		else	{
			$message .= 'Unable to send contract review email to client';
			?>
			<div id="message" class="error">
			<p><?php _e( $message ) ?></p>
			</div>
			<?php
		}
	}
	
/**
 * f_mdjm_cancel_event
 * Cancel selected events
 *
 * @param $event (event id)
 * @return
 *
 * @since 1.0
*/
	function f_mdjm_cancel_event( $event_id )	{
		global $wpdb;
		include( WPMDJM_PLUGIN_DIR . '/includes/config.inc.php' );
		$update = array(
						'contract_status' => 'Cancelled',
						'last_updated_by' => get_current_user_id(),
						'last_updated' => date( 'Y-m-d H:i:s' )
					);
		if( is_array( $event ) )	{
			foreach( $event as $event_id )	{
			$eventinfo = f_mdjm_get_eventinfo_by_id( $event_id );
			$update_event = $wpdb->update( $db_tbl['events'], $update, array( 'event_id' => $event_id ) );
			$clientinfo = get_userdata( $eventinfo->user_id );
			$j_args = array (
						'client' => $eventinfo->user_id,
						'event' => $event_id,
						'author' => get_current_user_id(),
						'type' => 'Cancel Event',
						'source' => 'Admin',
						'entry' => 'Event ID ' . $event_id . ' has been cancelled'
					);
			if( WPDJM_JOURNAL == 'Y' ) f_mdjm_do_journal( $j_args );
			}
		}
		else	{
			$eventinfo = f_mdjm_get_eventinfo_by_id( $event_id );
			$update_event = $wpdb->update( $db_tbl['events'], $update, array( 'event_id' => $event ) );
			$clientinfo = get_userdata( $eventinfo->user_id );
			$j_args = array (
						'client' => $eventinfo->user_id,
						'event' => $event,
						'author' => get_current_user_id(),
						'type' => 'Cancel Event',
						'source' => 'Admin',
						'entry' => 'Event ID ' . $event . ' has been cancelled'
					);
			if( WPDJM_JOURNAL == 'Y' ) f_mdjm_do_journal( $j_args );
		}
		$message = 'The selected events have been successfully cancelled';
		?>
		<div id="message" class="updated">
		<p><?php _e( $message ) ?></p>
		</div>
        <?php
	}
	
/**
 * f_mdjm_complete_event
 * Complete selected events
 *
 * @param $event (event id)
 * @return
 *
 * @since 1.0
*/
	function f_mdjm_complete_event( $event_id )	{
		global $wpdb;
		include( WPMDJM_PLUGIN_DIR . '/includes/config.inc.php' );
		$update = array(
						'contract_status' => 'Completed',
						'last_updated_by' => get_current_user_id(),
						'last_updated' => date( 'Y-m-d H:i:s' )
					);
		if( is_array( $event ) )	{
			foreach( $event as $event_id )	{
				$eventinfo = f_mdjm_get_eventinfo_by_id( $event_id );
				$update_event = $wpdb->update( $db_tbl['events'], $update, array( 'event_id' => $event_id ) );
				$clientinfo = get_userdata( $eventinfo->user_id );
				$j_args = array (
							'client' => $eventinfo->user_id,
							'event' => $event_id,
							'author' => get_current_user_id(),
							'type' => 'Update Event',
							'source' => 'Admin',
							'entry' => 'Event ID ' . $event_id . ' has been marked as completed'
						);
				if( WPDJM_JOURNAL == 'Y' ) f_mdjm_do_journal( $j_args );
			}
		}
		else	{
			$eventinfo = f_mdjm_get_eventinfo_by_id( $event_id );
				$update_event = $wpdb->update( $db_tbl['events'], $update, array( 'event_id' => $event ) );
				$clientinfo = get_userdata( $eventinfo->user_id );
				$j_args = array (
							'client' => $eventinfo->user_id,
							'event' => $event,
							'author' => get_current_user_id(),
							'type' => 'Update Event',
							'source' => 'Admin',
							'entry' => 'Event ID ' . $event . ' has been marked as completed'
						);
				if( WPDJM_JOURNAL == 'Y' ) f_mdjm_do_journal( $j_args );
		}
		$message = 'The selected events have been successfully marked as completed';
		?>
		<div id="message" class="updated">
		<p><?php _e( $message ) ?></p>
		</div>
        <?php
	}
	
/**
 * f_mdjm_fail_enquiry
 * Mark selected events as Failed Enquiry
 *
 * @param $event (event id)
 * @return
 *
 * @since 1.0
*/
	function f_mdjm_fail_enquiry( $event_id )	{
		global $wpdb;
		include( WPMDJM_PLUGIN_DIR . '/includes/config.inc.php' );
		$update = array(
						'contract_status' => 'Failed Enquiry',
						'last_updated_by' => get_current_user_id(),
						'last_updated' => date( 'Y-m-d H:i:s' )
					);
		if( is_array( $event ) )	{
			foreach( $event as $event_id )	{
			$eventinfo = f_mdjm_get_eventinfo_by_id( $event_id );
			$update_event = $wpdb->update( $db_tbl['events'], $update, array( 'event_id' => $event_id ) );
			$clientinfo = get_userdata( $eventinfo->user_id );
			$j_args = array (
						'client' => $eventinfo->user_id,
						'event' => $event_id,
						'author' => get_current_user_id(),
						'type' => 'Fail Enquiry',
						'source' => 'Admin',
						'entry' => 'Enquiry marked as lost'
					);
			if( WPDJM_JOURNAL == 'Y' ) f_mdjm_do_journal( $j_args );
			}
		}
		else	{
			$eventinfo = f_mdjm_get_eventinfo_by_id( $event_id );
			$update_event = $wpdb->update( $db_tbl['events'], $update, array( 'event_id' => $event ) );
			$clientinfo = get_userdata( $eventinfo->user_id );
			$j_args = array (
						'client' => $eventinfo->user_id,
						'event' => $event,
						'author' => get_current_user_id(),
						'type' => 'Fail Enquiry',
						'source' => 'Admin',
						'entry' => 'Enquiry marked as lost'
					);
			if( WPDJM_JOURNAL == 'Y' ) f_mdjm_do_journal( $j_args );
		}
		$message = 'The selected enquiries have been marked as lost';
		?>
		<div id="message" class="updated">
		<p><?php _e( $message ) ?></p>
		</div>
        <?php
	}

/**
 * f_mdjm_client_get_events
 * Retrieve all client events
 *
 * @param $client (user id)
 * @return array
 *
 * @since 1.0
*/
	function f_mdjm_client_get_events( $client )	{
		global $wpdb;
		include( WPMDJM_PLUGIN_DIR . '/includes/config.inc.php' );
		
		$info['num_rows'] = $wpdb->get_var( "SELECT COUNT(*) FROM `".$db_tbl['events']."` WHERE `user_id` = '".$client."'" );
		$next_event = $wpdb->get_row( "SELECT * FROM `".$db_tbl['events']."` WHERE `user_id` = '".$client."' AND `event_date` >= curdate() ORDER BY `event_date` ASC LIMIT 1" );
		
		$info['next_event'] = 'N/A';
		if( $wpdb->num_rows > 0 )	{
			$info['next_event'] = date( "jS F Y", strtotime( $next_event->event_date ) );
		}
		$info['event_id'] = $next_event->event_id;
		return $info;
	} // f_mdjm_client_get_events
	
/**
 * f_mdjm_get_eventinfo_by_id
 * Retrieve individual event detail by id 
 *
 * @param $event_id (event id)
 * @return array
 *
 * @since 1.0
*/
	function f_mdjm_get_eventinfo_by_id( $event_id )	{
		global $wpdb;
		include( WPMDJM_PLUGIN_DIR . '/includes/config.inc.php' );
		$eventinfo = $wpdb->get_row( 'SELECT * FROM `' . $db_tbl['events'] . '` WHERE `event_id` = ' . $event_id );
		return $eventinfo;
	} // f_mdjm_get_eventinfo_by_id

/**
 * f_mdjm_dj_get_events
 * Retrieve all events for DJ 
 *
 * @param $dj (user id)
 * @return array
 *
 * @since 1.0
*/
	function f_mdjm_dj_get_events( $dj )	{
		global $wpdb;
		include( WPMDJM_PLUGIN_DIR . '/includes/config.inc.php' );
		
		$info['all_events'] = $wpdb->get_var( "SELECT COUNT(*) FROM `".$db_tbl['events']."` WHERE `event_dj` = '".$dj."'" );
		$info['active_events'] = $wpdb->get_var( "SELECT COUNT(*) FROM `".$db_tbl['events']."` WHERE `event_dj` = '".$dj."' AND `event_date` >= DATE(NOW()) AND `contract_status` != 'Cancelled' AND `contract_status` != 'Completed' AND `contract_status` != 'Enquiry'" );
		$info['enquiries'] = $wpdb->get_var( "SELECT COUNT(*) FROM `".$db_tbl['events']."` WHERE `event_dj` = '".$dj."' AND `contract_status` = 'Enquiry' AND `event_date` >= curdate()" );
		$next_event = $wpdb->get_row( "SELECT * FROM `".$db_tbl['events']."` WHERE `event_dj` = '".$dj."' AND `event_date` >= curdate() AND `contract_status` != 'Cancelled' AND `contract_status` != 'Completed' AND `contract_status` != 'Enquiry' ORDER BY `event_date` ASC LIMIT 1" );
		
		$info['next_event'] = 'N/A';
		if( $wpdb->num_rows > 0 )	{
			$info['next_event'] = date( "jS F Y", strtotime( $next_event->event_date ) );
			$info['next_event_start'] = date( "H:i", strtotime( $next_event->event_start ) );
			$info['num_rows'] = $wpdb->num_rows;
		}
		$info['event_id'] = $next_event->event_id;
		$info['event_type'] = $next_event->event_type;
		return $info;
	} // f_mdjm_dj_get_events

/****************************************************************************************************
--	PLAYLIST FUNCTIONS
****************************************************************************************************/	
/**
 * f_mdjm_generate_playlist_ref
 * Generate the unique playlist id 
 *
 * @return string
 *
 * @since 1.0
*/
	function f_mdjm_generate_playlist_ref()	{
		$str = substr( str_shuffle( "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789" ), 0, 9 );
		return $str;
	} // f_mdjm_generate_playlist_ref

/****************************************************************************************************
--	DJ FUNCTIONS
****************************************************************************************************/
/**
 * f_mdjm_get_djs
 * Generate list of all DJ's
 *
 * @return array
 *
 * @since 1.0
*/
	function f_mdjm_get_djs()	{
		$admin_arg = array( 'role' => 'administrator',
							'orderby' => 'display_name',
							'order' => 'ASC'
						);
		$admin = get_users( $admin_arg );
		
		$dj_arg = array(	'role' => 'dj',
							'orderby' => 'display_name',
							'order' => 'ASC'
						);
		$dj = get_users( $dj_arg );
		$djs = array_merge( $admin, $dj );
		
		return $djs;
	} // f_mdjm_get_djs

/**
 * dj_can
 * Check DJ permissions
 *
 * @parse $permission
 * @return true : false
 *
 * @since 1.0
*/	
	function dj_can( $permission )	{
		global $mdjm_options;
		if ( $mdjm_options['dj_' . $permission] == 'Y' ) return true;
		else return false;
	} //dj_can

/**
 * is_dj
 * Check if current user is a DJ
 *
 * @return true : false
 *
 * @since 1.0
*/		
	function is_dj()	{
		if( current_user_can( 'dj' ) ) return true; else return false;
	} // is_dj
	
/**
 * f_mdjm_dj_working_today
 * Check for DJ's booked today
 *
 * @return $dj_event_results
 *
 * @since 1.0
*/		
	function f_mdjm_dj_working_today()	{
		global $wpdb;
		include( WPMDJM_PLUGIN_DIR . '/includes/config.inc.php' );
		$dj_event_query = "SELECT * FROM `" . $db_tbl['events'] . "` WHERE `event_dj` != '" . get_current_user_id() . "' AND DATE(event_date) = CURDATE()";
		$dj_event_results = $wpdb->get_results( $dj_event_query );
		
		return $dj_event_results;
	} // f_mdjm_dj_working_today
	
/****************************************************************************************************
--	CLIENT FUNCTIONS
****************************************************************************************************/
/**
 * f_mdjm_client_is_mine
 * Determine if client is current users
 *
 * @param $client_id (user id of client)
 * @return true : false
 *
 * @since 1.0
*/
	function f_mdjm_client_is_mine( $client_id )	{
		global $wpdb;
		include( WPMDJM_PLUGIN_DIR . '/includes/config.inc.php' );
		$client_query = $wpdb->get_var( "SELECT COUNT(*) FROM `" . $db_tbl['events'] . "` WHERE `user_id` = '" . $client_id . "' AND `event_dj` = '" . get_current_user_id() . "'" );
		if( $client_query == 0 ) return false;
		return true;
	}
	
/**
 * f_mdjm_get_clients
 * Generate list of all Client's
 *
 * @param $orderby, $order
 * @return array
 *
 * @since 1.0
*/
	function f_mdjm_get_clients( $orderby, $order )	{
		$arg = array(	'role' => 'client',
						'orderby' => $orderby,
						'order' => $order
						);
		$clients = get_users( $arg );
		return $clients;
	} // f_mdjm_get_djs

/**
 * f_mdjm_edit_own_client_only
 * Restricts client edits
 *
 *
 * @since 1.0
*/	
	function f_mdjm_edit_own_client_only() {
		if( !empty( $_GET ) && !current_user_can( 'administrator' ) && !f_mdjm_client_is_mine( $_GET['user_id'] ) ) {
			wp_die( 'You can only edit clients for whom you have, or will, DJ for. <a href="' . admin_url() . 'admin.php?page=mdjm-clients">Click here to return to your Clients List</a>' );
		}
	} // f_mdjm_edit_own_client_only
	
/****************************************************************************************************
--	EMAIL FUNCTIONS
****************************************************************************************************/
/**
 * f_mdjm_client_email_headers
 * Generate email headers for email to client
 *
 * 
 *
 * @since 1.0
*/
	function f_mdjm_client_email_headers( $event )	{
		global $mdjm_options;
		if( !empty( $event->event_dj ) ) $dj = get_userdata( $event->event_dj );
		
		$email_headers = 'MIME-Version: 1.0'  . "\r\n";
		$email_headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
		if( $event->contract_status == 'Enquiry' || $event->contract_status == 'Pending' )	{ /* Enquiries & Pending come from site admin */
			$email_headers .= 'From: ' . $dj->display_name . ' <' . get_bloginfo( 'admin_email' ) . '>' . "\r\n";
		}
		else	{ /* Everything else from the DJ */
			$email_headers .= 'From: ' . $dj->display_name . ' <bookings' . substr( get_bloginfo( 'admin_email' ), strpos( get_bloginfo( 'admin_email' ), "@" ) + 1 ) . '>' . "\r\n";
			$email_headers .= 'Reply-To: ' . $dj->user_email . "\r\n";
		}
		if( $mdjm_options['bcc_admin_to_client'] || $mdjm_options['bcc_dj_to_client'] )	{
			$email_headers .= 'Bcc: ';
			if( $mdjm_options['bcc_dj_to_client'] )
				$email_headers .= $dj->user_email;

			if( $mdjm_options['bcc_admin_to_client'] && $mdjm_options['bcc_dj_to_client'] )
				$email_headers .= ', ';

			if( $mdjm_options['bcc_dj_to_client'] )
				get_bloginfo( 'admin_email' );

			if( $mdjm_options['bcc_admin_to_client'] || $mdjm_options['bcc_dj_to_client'] )
				$email_headers .= "\r\n";
		}
		return $email_headers;
	} // f_mdjm_client_email_headers

/**
 * f_mdjm_prepare_email
 * Set email recipient and content
 *
 * @param $client_id (user id of client)
 * @return array
 *
 * @since 1.0
*/
	function f_mdjm_prepare_email( $eventinfo, $type )	{
		global $mdjm_options;

		$info['client'] = get_userdata( $eventinfo->user_id );
		$dj = get_userdata( $eventinfo->event_dj );
		
		$info['dj'] = $dj->user_email;
		
		include( WPMDJM_PLUGIN_DIR . '/admin/includes/config.inc.php' );
		$email_content = nl2br( html_entity_decode( stripcslashes( get_option( 'mdjm_plugin_email_template_' . $type ) ) ) );
		
		$info['content'] = str_replace( $shortcode_content_search, $shortcode_content_replace, $email_content );
		
		return $info;
	} // f_mdjm_prepare_email
	
/**
 * f_mdjm_dj_email_headers
 * Generate email headers for email to DJ
 *
 * 
 *
 * @since 1.0
*/
	function f_mdjm_dj_email_headers( $event_dj )	{
		global $mdjm_options;
		
		$dj = get_userdata( $event_dj );
		
		$email_headers = 'MIME-Version: 1.0'  . "\r\n";
		$email_headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
		$email_headers .= 'From: ' . $mdjm_options['company_name'] . ' <' . get_bloginfo( 'admin_email' ) . '>' . "\r\n";
		
		return $email_headers;
	} // f_mdjm_dj_email_headers
	
/****************************************************************************************************
--	DASHBOARD FUNCTIONS
****************************************************************************************************/
/**
 * f_mdjm_dashboard_dj_overview
 * Generate the DJ overview for the MDJM Dashboiard
 *
 * @return array $overview
 *
 * @since 1.0
*/
	function f_mdjm_dashboard_dj_overview()	{
		global $wpdb;
		include( WPMDJM_PLUGIN_DIR . '/includes/config.inc.php' );
		
		$dash_dj['month_active_events'] = $wpdb->get_var( "SELECT COUNT(*) FROM `".$db_tbl['events']."` WHERE `event_dj` = '" .get_current_user_id(). "' AND monthname(event_date) = '" . date( 'F' ) . "' AND `contract_status` != 'Cancelled' AND `contract_status` != 'Completed' AND `contract_status` != 'Enquiry'" );
		
		$dash_dj['month_enquiries'] = $wpdb->get_var( "SELECT COUNT(*) FROM `".$db_tbl['events']."` WHERE `event_dj` = '" .get_current_user_id(). "' AND monthname(event_date) = '" . date( 'F' ) . "' AND `contract_status` = 'Enquiry'" );
		
		$dash_dj['lost_month_enquiries'] = $wpdb->get_var( "SELECT COUNT(*) FROM `".$db_tbl['events']."` WHERE `event_dj` = '" .get_current_user_id(). "' AND monthname(event_date) = '" . date( 'F' ) . "' AND `contract_status` = 'Failed Enquiry'" );
		
		$dash_dj['month_completed'] = $wpdb->get_var( "SELECT COUNT(*) FROM `".$db_tbl['events']."` WHERE `event_dj` = '" .get_current_user_id(). "' AND monthname(event_date) = '" . date( 'F' ) . "' AND `contract_status` = 'Completed'" );
		
		$dash_dj['year_enquiries'] = $wpdb->get_var( "SELECT COUNT(*) FROM `".$db_tbl['events']."` WHERE `event_dj` = '" .get_current_user_id(). "' AND year(event_date) = '" . date( 'Y' ) . "' AND `contract_status` = 'Enquiry'" );
		
		$dash_dj['lost_year_enquiries'] = $wpdb->get_var( "SELECT COUNT(*) FROM `".$db_tbl['events']."` WHERE `event_dj` = '" .get_current_user_id(). "' AND year(event_date) = '" . date( 'Y' ) . "' AND `contract_status` = 'Failed Enquiry'" );
		
		$dash_dj['year_completed'] = $wpdb->get_var( "SELECT COUNT(*) FROM `".$db_tbl['events']."` WHERE `event_dj` = '" .get_current_user_id(). "' AND year(event_date) = '" . date( 'Y' ) . "' AND `contract_status` = 'Completed'" );
		
		if( current_user_can( 'administrator' ) || dj_can( 'see_deposit' ) )	{
			$dash_dj['potential_month_earn'] = $wpdb->get_var( "SELECT SUM(cost) FROM `".$db_tbl['events']."` WHERE `event_dj` = '" .get_current_user_id(). "' AND monthname(event_date) = '" . date( 'F' ) . "' AND `contract_status` != 'Cancelled' AND `contract_status` != 'Completed' AND `contract_status` != 'Failed Enquiry'" );
			
			$dash_dj['month_earn'] = $wpdb->get_var( "SELECT SUM(cost) FROM `".$db_tbl['events']."` WHERE `event_dj` = '" .get_current_user_id(). "' AND monthname(event_date) = '" . date( 'F' ) . "' AND `contract_status` = 'Completed'" );
			
			$dash_dj['potential_year_earn'] = $wpdb->get_var( "SELECT SUM(cost) FROM `".$db_tbl['events']."` WHERE `event_dj` = '" .get_current_user_id(). "' AND year(event_date) = '" . date( 'Y' ) . "' AND `contract_status` != 'Cancelled' AND `contract_status` != 'Failed Enquiry'" );
			
			$dash_dj['year_earn'] = $wpdb->get_var( "SELECT SUM(cost) FROM `".$db_tbl['events']."` WHERE `event_dj` = '" .get_current_user_id(). "' AND year(event_date) = '" . date( 'Y' ) . "' AND `contract_status` = 'Completed'" );
		}
		else	{ // We take away the deposit amount from their earnings
			$dj_potential_month = $wpdb->get_var( "SELECT SUM(cost) FROM `".$db_tbl['events']."` WHERE `event_dj` = '" .get_current_user_id(). "' AND monthname(event_date) = '" . date( 'F' ) . "' AND `contract_status` != 'Cancelled' AND `contract_status` != 'Completed' AND `contract_status` != 'Failed Enquiry'" );
			$dj_deposit_potential_month = $wpdb->get_var( "SELECT SUM(deposit) FROM `".$db_tbl['events']."` WHERE `event_dj` = '" .get_current_user_id(). "' AND monthname(event_date) = '" . date( 'F' ) . "' AND `contract_status` != 'Cancelled' AND `contract_status` != 'Completed' AND `contract_status` != 'Failed Enquiry'" );
			
			$dash_dj['potential_month_earn'] = $dj_potential_month - $dj_deposit_potential_month;
			
			$dj_month = $wpdb->get_var( "SELECT SUM(cost) FROM `".$db_tbl['events']."` WHERE `event_dj` = '" .get_current_user_id(). "' AND monthname(event_date) = '" . date( 'F' ) . "' AND `contract_status` = 'Completed'" );
			$dj_deposit_month = $wpdb->get_var( "SELECT SUM(deposit) FROM `".$db_tbl['events']."` WHERE `event_dj` = '" .get_current_user_id(). "' AND monthname(event_date) = '" . date( 'F' ) . "' AND `contract_status` = 'Completed'" );
			
			$dash_dj['month_earn'] = $dj_month - $dj_deposit_month;
			
			$dj_potential_year = $wpdb->get_var( "SELECT SUM(cost) FROM `".$db_tbl['events']."` WHERE `event_dj` = '" .get_current_user_id(). "' AND year(event_date) = '" . date( 'Y' ) . "' AND `contract_status` != 'Cancelled' AND `contract_status` != 'Failed Enquiry'" );
			
			$dj_deposit_potential_year = $wpdb->get_var( "SELECT SUM(deposit) FROM `".$db_tbl['events']."` WHERE `event_dj` = '" .get_current_user_id(). "' AND year(event_date) = '" . date( 'Y' ) . "' AND `contract_status` != 'Cancelled' AND `contract_status` != 'Failed Enquiry'" );
			
			$dash_dj['potential_year_earn'] = $dj_potential_year - $dj_deposit_potential_year;
			
			$dj_year = $wpdb->get_var( "SELECT SUM(cost) FROM `".$db_tbl['events']."` WHERE `event_dj` = '" .get_current_user_id(). "' AND year(event_date) = '" . date( 'Y' ) . "' AND `contract_status` = 'Completed'" );
			
			$dj_deposit_year = $wpdb->get_var( "SELECT SUM(deposit) FROM `".$db_tbl['events']."` WHERE `event_dj` = '" .get_current_user_id(). "' AND year(event_date) = '" . date( 'Y' ) . "' AND `contract_status` = 'Completed'" );
			
			$dash_dj['year_earn'] = $dj_year - $dj_deposit_year;
			
		}
		
		return $dash_dj;
	} // f_mdjm_dashboard_dj_overview
	
/**
 * f_mdjm_dashboard_employer_overview
 * Generate the Employer overview for the MDJM Dashboiard
 *
 * @return array $overview
 *
 * @since 1.0
*/
	function f_mdjm_dashboard_employee_overview()	{
		global $wpdb;
		include( WPMDJM_PLUGIN_DIR . '/includes/config.inc.php' );
		
		$dash_emp['month_active_events'] = $wpdb->get_var( "SELECT COUNT(*) FROM `".$db_tbl['events']."` WHERE monthname(event_date) = '" . date( 'F' ) . "' AND `contract_status` != 'Cancelled' AND `contract_status` != 'Completed' AND `contract_status` != 'Enquiry'" );
		
		$dash_emp['month_enquiries'] = $wpdb->get_var( "SELECT COUNT(*) FROM `".$db_tbl['events']."` WHERE monthname(event_date) = '" . date( 'F' ) . "' AND `contract_status` = 'Enquiry'" );
		
		$dash_emp['lost_month_enquiries'] = $wpdb->get_var( "SELECT COUNT(*) FROM `".$db_tbl['events']."` WHERE monthname(event_date) = '" . date( 'F' ) . "' AND `contract_status` = 'Failed Enquiry'" );
		
		$dash_emp['month_completed'] = $wpdb->get_var( "SELECT COUNT(*) FROM `".$db_tbl['events']."` WHERE monthname(event_date) = '" . date( 'F' ) . "' AND `contract_status` = 'Completed'" );
		
		$dash_emp['year_enquiries'] = $wpdb->get_var( "SELECT COUNT(*) FROM `".$db_tbl['events']."` WHERE year(event_date) = '" . date( 'Y' ) . "' AND `contract_status` = 'Enquiry'" );
		
		$dash_emp['lost_year_enquiries'] = $wpdb->get_var( "SELECT COUNT(*) FROM `".$db_tbl['events']."` WHERE year(event_date) = '" . date( 'Y' ) . "' AND `contract_status` = 'Failed Enquiry'" );
		
		$dash_emp['year_completed'] = $wpdb->get_var( "SELECT COUNT(*) FROM `".$db_tbl['events']."` WHERE year(event_date) = '" . date( 'Y' ) . "' AND `contract_status` = 'Completed'" );
		
		$dash_emp['potential_month_earn'] = $wpdb->get_var( "SELECT SUM(cost) FROM `".$db_tbl['events']."` WHERE monthname(event_date) = '" . date( 'F' ) . "' AND `contract_status` != 'Cancelled' AND `contract_status` != 'Completed' AND `contract_status` != 'Failed Enquiry'" );
		
		$dash_emp['month_earn'] = $wpdb->get_var( "SELECT SUM(cost) FROM `".$db_tbl['events']."` WHERE monthname(event_date) = '" . date( 'F' ) . "' AND `contract_status` = 'Completed'" );
		
		$dash_emp['potential_year_earn'] = $wpdb->get_var( "SELECT SUM(cost) FROM `".$db_tbl['events']."` WHERE year(event_date) = '" . date( 'Y' ) . "' AND `contract_status` != 'Cancelled' AND `contract_status` != 'Failed Enquiry'" );
		
		$dash_emp['year_earn'] = $wpdb->get_var( "SELECT SUM(cost) FROM `".$db_tbl['events']."` WHERE year(event_date) = '" . date( 'Y' ) . "' AND `contract_status` = 'Completed'" );
		
		return $dash_emp;
	} // f_mdjm_dashboard_dj_overview
?>