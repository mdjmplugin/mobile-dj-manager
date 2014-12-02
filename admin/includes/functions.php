<?php

/**
 *************** MDJM ***************
 * functions.php
 *
 * Admin UI functions
 *
 * @since 1.0
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
		global $mdjm_options, $mdjm_help;
		add_menu_page( 'Mobile DJ Manager', 'DJ Manager', 'manage_mdjm', 'mdjm-dashboard', 'f_mdjm_admin_dashboard', plugins_url( 'mobile-dj-manager/admin/images/mdjm-icon-20x20.jpg' ), '58.4' );

		add_submenu_page( 'mdjm-dashboard', 'Mobile DJ Manager - Dashboard', 'Dashboard', 'manage_mdjm', 'mdjm-dashboard', 'f_mdjm_admin_dashboard');

		if( current_user_can( 'manage_options' ) ) add_submenu_page( 'mdjm-dashboard', 'Mobile DJ Manager - Settings', 'Settings', 'manage_mdjm', 'mdjm-settings', 'f_mdjm_admin_settings');

		add_submenu_page( 'mdjm-dashboard', 'Mobile DJ Manager - Clients', 'Clients', 'manage_mdjm', 'mdjm-clients', 'f_mdjm_admin_clients');
		
		add_submenu_page( 'mdjm-dashboard', 'Mobile DJ Manager - Communications', 'Communications', 'manage_mdjm', 'mdjm-comms', 'f_mdjm_admin_comms');

		if( current_user_can( 'manage_options' ) && isset( $mdjm_options['multiple_dj'] ) && $mdjm_options['multiple_dj'] == 'Y' ) add_submenu_page( 'mdjm-dashboard', 'Mobile DJ Manager - DJ\'s ', 'DJ\'s' , 'manage_mdjm', 'mdjm-djs', 'f_mdjm_admin_djs');
		
		if( current_user_can( 'manage_options' ) && isset( $mdjm_options['enable_packages'] ) && $mdjm_options['enable_packages'] == 'Y' ) add_submenu_page( 'mdjm-dashboard', 'Mobile DJ Manager - Packages', 'Equipment Packages', 'manage_mdjm', 'mdjm-packages', 'f_mdjm_admin_packages');

		add_submenu_page( 'mdjm-dashboard', 'Mobile DJ Manager - Events', 'Events', 'manage_mdjm', 'mdjm-events', 'f_mdjm_admin_events');
		
		add_submenu_page( 'mdjm-dashboard', 'Mobile DJ Manager - Venues', 'Venues', 'manage_mdjm', 'mdjm-venues', 'f_mdjm_admin_venues');
		
		if( !do_reg_check( 'check' ) && current_user_can( 'manage_options' ) )	{
			add_submenu_page( 'mdjm-dashboard', 'Mobile DJ Manager - Licensing', '<font style="color:#F90">Buy License</font>', 'manage_mdjm', 'mdjm-license', 'f_mdjm_purchase');	
		}
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
 * Display the MDJM events list
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
 * f_mdjm_admin_venues
 * Display the MDJM venues list
 *
 *
 * @since 1.0
*/
	function f_mdjm_admin_venues()	{
		if( !current_user_can( 'manage_options' ) && !current_user_can( 'manage_mdjm' ) )  {
			wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
		}
		wp_nonce_field( "mdjm-venues-page" );
		include_once( WPMDJM_PLUGIN_DIR . '/admin/pages/venues.php' );
	} // f_mdjm_admin_venues

/**
 * f_mdjm_purchase
 * Purchase a license (displays if not registered or trial)
 *
 *
 * @since 1.0
*/
	function f_mdjm_purchase()	{
		wp_redirect( 'http://www.mydjplanner.co.uk/shop/mobile-dj-manager-for-wordpress-plugin/' );
	}

/**
 * f_mdjm_admin_clients
 * Display the MDJM clients
 *
 *
 * @since 1.0
*/
	function f_mdjm_admin_clients()	{
		if( !current_user_can( 'manage_options' ) && !current_user_can( 'manage_mdjm' ) )  {
			wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
		}
		wp_nonce_field( "mdjm-clients-page" );
		include_once( WPMDJM_PLUGIN_DIR . '/admin/pages/clients.php' );
	} // f_mdjm_admin_clients
/**
 * f_mdjm_admin_comms
 * Display the MDJM communications page
 *
 *
 * @since 1.0
*/

	function f_mdjm_admin_comms()	{
		if( !current_user_can( 'manage_options' ) && !current_user_can( 'manage_mdjm' ) )  {
			wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
		}
		wp_nonce_field( "mdjm-comms-page" );
		include_once( WPMDJM_PLUGIN_DIR . '/admin/pages/comms.php' );
	} // f_mdjm_admin_comms

/**
 * f_mdjm_admin_djs
 * Display the MDJM DJ's
 *
 *
 * @since 1.0
*/
	function f_mdjm_admin_djs()	{
		if( !current_user_can( 'manage_options' ) && !current_user_can( 'manage_mdjm' ) )  {
			wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
		}
		wp_nonce_field( "mdjm-djs-page" );
		include_once( WPMDJM_PLUGIN_DIR . '/admin/pages/djs.php' );
	} // f_mdjm_admin_djs

/**
 * f_mdjm_admin_equipment
 * Display the MDJM Equipment Settings Page
 *
 *
 * @since 1.0
*/
	function f_mdjm_admin_packages()	{
		if( !current_user_can( 'manage_options' ) )  {
			wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
		}
		wp_nonce_field( "mdjm-djs-packages" );
		include_once( WPMDJM_PLUGIN_DIR . '/admin/pages/settings-packages-main.php' );
	} // f_mdjm_admin_packages

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
	
/*
* f_mdjm_admin_page
* 02/12/2014
* @since 0.9.5
* Outputs the desired admin page URL
*/
	function f_mdjm_admin_page( $mdjm_page )	{
		$mdjm_pages = array(
						'dashboard'             => 'admin.php?page=mdjm-dashboard',
						'settings'              => 'admin.php?page=mdjm-settings',
						'clients'               => 'admin.php?page=mdjm-clients',
						'add_client'            => 'user-new.php',
						'edit_client'           => 'user-edit.php?user_id=',
						'comms'                 => 'admin.php?page=mdjm-comms',
						'contract'              => 'edit.php?post_type=contract',
						'add_contract'          => 'post-new.php?post_type=contract',
						'djs'                   => 'admin.php?page=mdjm-djs',
						'djs'                   => 'admin.php?page=mdjm-djs',
						'email_template'        => 'edit.php?post_type=email_template',
						'add_email_template'    => 'post-new.php?post_type=email_template',
						'equipment'             => 'admin.php?page=mdjm-packages',
						'events'                => 'admin.php?page=mdjm-events',
						'add_event'             => 'admin.php?page=mdjm-events&action=add_event_form',
						'enquiries'             => 'admin.php?page=mdjm-events&display=enquiries',
						'venues'                => 'admin.php?page=mdjm-venues',
						'add_venue'             => 'admin.php?page=mdjm-events&action=add_venue_form',
						);
		echo admin_url( $mdjm_pages[$mdjm_page] );
	}

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
		
		if( $current_db_ver != $mdjm_db_version ) {
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
							event_package varchar(100) DEFAULT NULL,
							event_addons varchar(100) DEFAULT NULL,
							event_guest_call varchar(9) NOT NULL,
							booking_date date DEFAULT NULL,
							contract_status varchar(255) NOT NULL,
							contract int(11) NOT NULL,
							contract_approved_date varchar(255) NOT NULL,
							contract_approver varchar(255) NOT NULL,
							cost decimal(10,2) NOT NULL,
							deposit decimal(10,2) NOT NULL,
							deposit_status varchar(50) NOT NULL,
							balance_status varchar(50) NOT NULL,
							venue varchar(255) NOT NULL,
							venue_contact varchar(255) DEFAULT NULL,
							venue_addr1 varchar(255) DEFAULT NULL,
							venue_addr2 varchar(255) NOT NULL,
							venue_city varchar(255) NOT NULL,
							venue_state varchar(255) NOT NULL,
							venue_zip varchar(255) NOT NULL,
							venue_phone varchar(255) DEFAULT NULL,
							venue_email varchar(255) DEFAULT NULL,
							dj_setup_time time NULL,
							dj_setup_date date NOT NULL,
							dj_notes text NOT NULL,
							admin_notes text NOT NULL,
							added_by int(11) NOT NULL,
							date_added datetime NOT NULL,
							referrer varchar(255) NOT NULL,
							converted_by int(11) NOT NULL,
							date_converted datetime NOT NULL,
							last_updated_by int(11) NOT NULL,
							last_updated datetime NOT NULL,
							cronned text NOT NULL,
							PRIMARY KEY  (event_id)
							);";
			
			/* VENUES TABLE */
			$venues_sql = "CREATE TABLE ". $db_tbl['venues'] . " (
							venue_id smallint(6) NOT NULL AUTO_INCREMENT,
							venue_name varchar(255) NOT NULL,
							venue_address1 varchar(255) NOT NULL,
							venue_address2 varchar(255) NOT NULL,
							venue_town varchar(255) NOT NULL,
							venue_county varchar(255) NOT NULL,
							venue_postcode varchar(255) NOT NULL,
							venue_contact varchar(255) NOT NULL,
							venue_phone varchar(255) NOT NULL,
							venue_email varchar(255) NOT NULL,
							venue_information longtext NOT NULL,
							PRIMARY KEY  (venue_id)
							);";
			
			/* JOURNAL TABLE */
			$journal_sql = "CREATE TABLE ". $db_tbl['journal'] . " (
							id int(11) NOT NULL AUTO_INCREMENT,
							client int(11) NOT NULL,
							event int(11) NOT NULL,
							timestamp varchar(255) NOT NULL,
							author int(11) NOT NULL,
							type varchar(255) NOT NULL,
							source varchar(255) NOT NULL,
							entry text NOT NULL,
							PRIMARY KEY  (id)
							);";
							
			/* PLAYLISTS TABLE */
			$playlists_sql = "CREATE TABLE ". $db_tbl['playlists'] . " (
								id int(11) NOT NULL AUTO_INCREMENT,
								event_id int(11) NOT NULL,
								artist varchar(255) NOT NULL,
								song varchar(255) NOT NULL,
								play_when varchar(255) NOT NULL,
								info text NOT NULL,
								added_by varchar(255) NOT NULL,
								date_added date NOT NULL,
								date_to_mdjm datetime NULL,
								PRIMARY KEY  (id)
								);";
			
			require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
			dbDelta( $events_sql );
			dbDelta( $venues_sql );
			dbDelta( $journal_sql );
			dbDelta( $playlists_sql );
		
			update_option( 'mdjm_db_version', $mdjm_db_version );
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
		<div id="message" class="<?php echo $class; ?>">
		<p><?php _e( $message, 'my-text-domain' ); ?></p>
		</div>
		<?php
	} // f_mdjm_admin_update_notice

/**
 * f_mdjm_update_notice
 * Display status messages when forms are updated
 *
 *
 * @since 1.0
*/
	function f_mdjm_update_notice( $class, $message )	{
		echo '<div id="message" class="' . $class . '">';
		echo '<p>' . $message . '</p>';
		echo '</div>';
	} // f_mdjm_update_notice

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
		global $wpdb, $mdjm_options;
		require_once( WPMDJM_PLUGIN_DIR . '/includes/config.inc.php' );
		require_once( WPMDJM_PLUGIN_DIR . '/includes/functions.php' );

		$event_date = explode( '/', $event['event_date'] );
		$event_date = $event_date[2] . '-' . $event_date[1] . '-' . $event_date[0];
		if( isset( $event['dj_setup_date'] ) && $event['dj_setup_date'] != '' )	{
			$dj_setup_date = explode( '/', $event['dj_setup_date'] );
			$dj_setup_date = $dj_setup_date[2] . '-' . $dj_setup_date[1] . '-' . $dj_setup_date[0];
		}
		$str = f_mdjm_generate_playlist_ref();
		if( !isset( $event['deposit_status'] ) || $event['deposit_status'] == '' ) $event['deposit_status'] = 'Due';
		if( !isset( $event['balance_status'] ) || $event['balance_status'] == '' ) $event['balance_status'] = 'Due';
		
		if( $mdjm_options['time_format'] == 'H:i' )	{
			$start_time = date( 'H:i:s', strtotime( $event['event_start_hr'] . ':' . $event['event_start_min'] ) );
			$end_time = date( 'H:i:s', strtotime( $event['event_finish_hr'] . ':' . $event['event_finish_min'] ) );
			
			$setup_time = date( 'H:i:s', strtotime( $event['dj_setup_hr'] . ':' . $event['dj_setup_min'] ) );
		}
		else	{
			$start_time = date( 'H:i:s', strtotime( $event['event_start_hr'] . ':' . $event['event_start_min'] . $event['event_start_period'] ) );
			$end_time = date( 'H:i:s', strtotime( $event['event_finish_hr'] . ':' . $event['event_finish_min'] . $event['event_finish_period'] ) );
			
			$setup_time = date( 'H:i:s', strtotime( $event['dj_setup_hr'] . ':' . $event['dj_setup_min'] . $event['dj_setup_period'] ) );
		}
		
		/* If a venue was selected use it */
		if( $event['event_venue'] != '' && $event['event_venue'] != 'manual' )	{
			$venueinfo = f_mdjm_get_venue_by_id( $event['event_venue'] );
			
			$event['venue'] = $venueinfo->venue_name;
			$event['venue_addr1'] = $venueinfo->venue_address1;
			$event['venue_addr2'] = $venueinfo->venue_address2;
			$event['venue_city'] = $venueinfo->venue_town;
			$event['venue_state'] = $venueinfo->venue_county;
			$event['venue_zip'] = $venueinfo->venue_postcode;
			$event['venue_contact'] = $venueinfo->venue_contact;
			$event['venue_phone'] = $venueinfo->venue_phone;
			$event['venue_email'] = $venueinfo->venue_email;
		}

		if( $wpdb->insert( $db_tbl['events'],
										array(
											'event_id'           => '',
											'user_id'            => $event['user_id'],
											'event_date'         => $event_date,
											'event_dj'           => $event['event_dj'],
											'event_type'         => sanitize_text_field( $event['event_type'] ),
											'event_start'        => $start_time,
											'event_finish'       => $end_time,
											'event_description'  => $event['event_description'],
											'event_package'      => $event['event_package'],
											'event_addons'       => $event['event_addons'],
											'event_guest_call'   => $str,
											'contract_status'    => 'Enquiry',
											'contract'           => $event['contract'],
											'cost'               => $event['total_cost'],
											'deposit'            => $event['deposit'],
											'deposit_status'     => $event['deposit_status'],
											'balance_status'     => $event['balance_status'],
											'venue'              => sanitize_text_field( $event['venue'] ),
											'venue_contact'      => sanitize_text_field( $event['venue_contact'] ),
											'venue_addr1'        => sanitize_text_field( $event['venue_addr1'] ),
											'venue_addr2'        => sanitize_text_field( $event['venue_addr2'] ),
											'venue_city'         => sanitize_text_field( $event['venue_city'] ),
											'venue_state'        => sanitize_text_field( $event['venue_state'] ),
											'venue_zip'          => sanitize_text_field( strtoupper( $event['venue_zip'] ) ),
											'venue_phone'        => $event['venue_phone'],
											'venue_email'        => sanitize_email( $event['venue_email'] ),
											'dj_setup_time'      => $setup_time,
											'dj_setup_date'      => $dj_setup_date,
											'dj_notes'           => $event['dj_notes'],
											'admin_notes'        => $event['admin_notes'],
											'added_by'           => get_current_user_id(),
											'date_added'         => date( 'Y-m-d H:i:s' ),
											'referrer'           => sanitize_text_field( $event['enquiry_source'] ),
											'last_updated_by'    => get_current_user_id(),
											'last_updated'       => date( 'Y-m-d H:i:s' )
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
			if( isset( $event['email_enquiry'] ) && $event['email_enquiry'] == 'Y' )	{
				$eventinfo = f_mdjm_get_eventinfo_by_id( $id );
				$email_headers = f_mdjm_client_email_headers( $eventinfo );
				$info = f_mdjm_prepare_email( $eventinfo, $type='email_enquiry' );
				if( isset( $info['subject'] ) && !empty( $info['subject'] ) && isset( $mdjm_options['title_as_subject'] ) && $mdjm_options['title_as_subject'] == 'Y' )	{
					$subject = $info['subject'];	
				}
				else	{
					$subject = 'DJ Enquiry';	
				}
				if( wp_mail( $clientinfo->user_email, $subject, $info['content'], $email_headers ) ) 	{
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
			if( isset( $event['deposit_status'] ) && $event['deposit_status'] == 'Paid' )
				f_mdjm_deposit_paid( $id );
				
			if( isset( $event['balance_status'] ) && $event['balance_status'] == 'Paid' )
				f_mdjm_deposit_paid( $id );
			
			/* Add venue */	
			if( isset( $event['save_venue'] ) && $event['save_venue'] == 'Y' )	{
				if( $wpdb->insert( $db_tbl['venues'],
											array(
												'venue_id'	       => '',
												'venue_name' => sanitize_text_field( $event['venue'] ),
												'venue_contact' => sanitize_text_field( $event['venue_contact'] ),
												'venue_address1' => sanitize_text_field( $event['venue_addr1'] ),
												'venue_address2' => sanitize_text_field( $event['venue_addr2'] ),
												'venue_town' => sanitize_text_field( $event['venue_city'] ),
												'venue_county' => sanitize_text_field( $event['venue_state'] ),
												'venue_postcode' => sanitize_text_field( strtoupper( $event['venue_zip'] ) ),
												'venue_phone' => $event['venue_phone'],
												'venue_email' => sanitize_email( $event['venue_email'] ),
												'venue_information'  => '',
											) ) )	{
					$class = 'updated';
					$message = 'The venue <strong>' . $event['venue'] . '</strong> was added to the database successfully';
					f_mdjm_update_notice( $class, $message );
				}
			}
					
		}
		else	{
			die( $wpdb->print_error() );	
		}
	} // f_mdjm_add_event

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
		
		// Do not update package or addon details as these are handled seperately
		unset( $event_updates['event_package'], $event_updates['event_addons'] );
		
		$updated_fields = array();
		$event_date = explode( '/', $event_updates['event_date'] );
		$setup_date = explode( '/', $event_updates['dj_setup_date'] );
		if( !empty( $event_updates['contract_approved_date'] ) )	{
			$contract_approved_date = explode( '/', $event_updates['contract_approved_date'] );
			$event_updates['contract_approved_date'] = $contract_approved_date[2] . '-' . $contract_approved_date[1] . '-' . $contract_approved_date[0];
		}
		else	{
			unset( $event_updates['contract_approved_date'] );	
		}
		
		if( $mdjm_options['time_format'] == 'H:i' )	{
			$event_updates['event_start'] = date( 'H:i:s', strtotime( $event_updates['event_start_hr'] . ':' . $event_updates['event_start_min'] ) );
			$event_updates['event_finish'] = date( 'H:i:s', strtotime( $event_updates['event_finish_hr'] . ':' . $event_updates['event_finish_min'] ) );
			$event_updates['dj_setup_time'] = date( 'H:i:s', strtotime( $event_updates['dj_setup_hr'] . ':' . $event_updates['dj_setup_min'] ) );
			
			unset( $event_updates['event_start_hr'], $event_updates['event_start_min'], $event_updates['event_finish_hr'], $event_updates['event_finish_min'], $event_updates['dj_setup_hr'], $event_updates['dj_setup_min'] );
		}
		else	{
			$event_updates['event_start'] = date( 'H:i:s', strtotime( $event_updates['event_start_hr'] . ':' . $event_updates['event_start_min'] . $event_updates['event_start_period'] ) );
			$event_updates['event_finish'] = date( 'H:i:s', strtotime( $event_updates['event_finish_hr'] . ':' . $event_updates['event_finish_min'] . $event_updates['event_finish_period'] ) );
			$event_updates['dj_setup_time'] = date( 'H:i:s', strtotime( $event_updates['dj_setup_hr'] . ':' . $event_updates['dj_setup_min'] . $event_updates['dj_setup_period'] ) );
			
			unset( $event_updates['event_start_hr'], $event_updates['event_start_min'], $event_updates['event_start_period'], $event_updates['event_finish_hr'], $event_updates['event_finish_min'], $event_updates['event_finish_period'], $event_updates['dj_setup_hr'], $event_updates['dj_setup_min'], $event_updates['dj_setup_period'] );
		}
		
		$event_updates['event_date'] = $event_date[2] . '-' . $event_date[1] . '-' . $event_date[0];
		$event_updates['dj_setup_date'] = $setup_date[2] . '-' . $setup_date[1] . '-' . $setup_date[0];
		$event_updates['last_updated_by'] = get_current_user_id();
		$event_updates['last_updated'] = date( 'Y-m-d H:i:s' );
		
		if( !isset( $event_updates['deposit_status'] ) || $event_updates['deposit_status'] == '' )
			$event_updates['deposit_status'] = 'Due';
			
		if( !isset( $event_updates['balance_status'] ) || $event_updates['balance_status'] == '' )
			$event_updates['balance_status'] = 'Due';

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
			if( isset( $now_pending ) && $mdjm_options['contract_to_client'] == 'Y' )	{
				$email_headers = f_mdjm_client_email_headers( $eventinfo );
				$info = f_mdjm_prepare_email( $eventinfo, $type='email_contract' );
				if( isset( $info['subject'] ) && !empty( $info['subject'] ) && isset( $mdjm_options['title_as_subject'] ) && $mdjm_options['title_as_subject'] == 'Y' )	{
					$subject = $info['subject'];	
				}
				else	{
					$subject = 'Your DJ Booking';	
				}
				if( wp_mail( $info['client']->user_email, $subject, $info['content'], $email_headers ) ) 	{
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
			if( isset( $now_approved ) )	{
				$email_headers = f_mdjm_client_email_headers( $eventinfo );
				$info = f_mdjm_prepare_email( $eventinfo, $type='email_client_confirm' );
				if( isset( $info['subject'] ) && !empty( $info['subject'] ) && isset( $mdjm_options['title_as_subject'] ) && $mdjm_options['title_as_subject'] == 'Y' )	{
					$subject = $info['subject'];	
				}
				else	{
					$subject = 'Booking Confirmation';	
				}
				if ( wp_mail( $info['client']->user_email, $subject, $info['content'], $email_headers ) ) 	{
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
					$info = f_mdjm_prepare_email( $eventinfo, $type='email_dj_confirm' );
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
			if( isset( $event_updates['deposit_status'] ) && $event_updates['deposit_status'] == 'Paid' && $event_updates['deposit_status'] != $eventinfo->deposit_status )	{
				f_mdjm_deposit_paid( $eventinfo->event_id );
			}
			if( isset( $event_updates['balance_status'] ) && $event_updates['balance_status'] == 'Paid' && $event_updates['balance_status'] != $eventinfo->balance_status )	{
				f_mdjm_balance_paid( $eventinfo->event_id );
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
 * f_mdjm_update_event_package
 * Update existing event package
 *
 * @param $event_id (event id), $event_cost, $event_package
 *
 * @since 1.0
*/
	function f_mdjm_update_event_package( $event_id, $event_cost, $event_package, $client )	{
		global $wpdb;
		include( WPMDJM_PLUGIN_DIR . '/includes/config.inc.php' );
		$update = array(
						'cost' => $event_cost,
						'event_package' => $event_package,
						'event_addons' => '',
						'last_updated_by' => get_current_user_id(),
						'last_updated' => date( 'Y-m-d H:i:s' )
					);
		$update_event = $wpdb->update( $db_tbl['events'], $update, array( 'event_id' => $event_id ) );
		$j_args = array (
						'client' => $client,
						'event' => $event_id,
						'author' => get_current_user_id(),
						'type' => 'Update Event',
						'source' => 'Admin',
						'entry' => 'The package has been updated'
					);
		if( WPDJM_JOURNAL == 'Y' ) f_mdjm_do_journal( $j_args );
		
	} // f_mdjm_update_event_package
	
/**
 * f_mdjm_update_event_addons
 * Update existing event addons
 *
 * @param $event_id (event id), $event_cost, $event_addons, $client
 *
 * @since 1.0
*/
	function f_mdjm_update_event_addons( $event_id, $event_cost, $event_addons, $client )	{
		global $wpdb;
		include( WPMDJM_PLUGIN_DIR . '/includes/config.inc.php' );
		$update = array(
						'cost' => $event_cost,
						'event_addons' => $event_addons,
						'last_updated_by' => get_current_user_id(),
						'last_updated' => date( 'Y-m-d H:i:s' )
					);
		$update_event = $wpdb->update( $db_tbl['events'], $update, array( 'event_id' => $event_id ) );
		$j_args = array (
						'client' => $client,
						'event' => $event_id,
						'author' => get_current_user_id(),
						'type' => 'Update Event',
						'source' => 'Admin',
						'entry' => 'The event add-ons have been updated'
					);
		if( WPDJM_JOURNAL == 'Y' ) f_mdjm_do_journal( $j_args );
		
	} // f_mdjm_update_event_addons

/**
 * f_mdjm_convert_event
 * Convert selected events to bookings
 *
 * @param $event (event id)
 * @return
 *
 * @since 1.0
*/
	function f_mdjm_convert_event( $event )	{
		global $wpdb, $mdjm_options;
		include( WPMDJM_PLUGIN_DIR . '/includes/config.inc.php' );
		$update = array(
						'contract_status' => 'Pending',
						'converted_by' => get_current_user_id(),
						'date_converted' => date( 'Y-m-d H:i:s' ),
						'last_updated_by' => get_current_user_id(),
						'last_updated' => date( 'Y-m-d H:i:s' )
					);
		if( !is_array( $event ) )
			$event = array( $event );	
		foreach( $event as $event_id )	{
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
			
			$class = 'updated';
			$message = 'The selected enquiry has been successfully converted to a booking';
			f_mdjm_update_notice( $class, $message );
			
			$email_headers = f_mdjm_client_email_headers( $eventinfo );
			$info = f_mdjm_prepare_email( $eventinfo, $type='email_contract' );
			if( isset( $info['subject'] ) && !empty( $info['subject'] ) && isset( $mdjm_options['title_as_subject'] ) && $mdjm_options['title_as_subject'] == 'Y' )	{
					$subject = $info['subject'];	
				}
				else	{
					$subject = 'Your DJ Booking';	
				}
			if ( wp_mail( $info['client']->user_email, $subject, $info['content'], $email_headers ) ) 	{
				$class = 'updated';
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
				f_mdjm_update_notice( $class, $message );
			}
			else	{
				$class = 'error';
				$message = 'Unable to send contract review email to client';
				f_mdjm_update_notice( $class, $message );
			}
		}
		f_mdjm_render_events_table();
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

	function f_mdjm_cancel_event( $event )	{
		global $wpdb;
		include( WPMDJM_PLUGIN_DIR . '/includes/config.inc.php' );
		$update = array(
						'contract_status' => 'Cancelled',
						'last_updated_by' => get_current_user_id(),
						'last_updated' => date( 'Y-m-d H:i:s' )
					);
		if( !is_array( $event ) )
			$event = array( $event );	
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
		$class = 'updated';
		$message = 'The selected events have been successfully cancelled';
		f_mdjm_update_notice( $class, $message );
		f_mdjm_render_events_table();
	} // f_mdjm_cancel_event
	
/**
 * f_mdjm_recover_event
 * Recover selected events (Failed Enquiries)
 *
 * @param $event (event id)
 * @return
 *
 * @since 1.0
*/

	function f_mdjm_recover_event( $event )	{
		global $wpdb;
		include( WPMDJM_PLUGIN_DIR . '/includes/config.inc.php' );
		$update = array(
						'contract_status' => 'Enquiry',
						'last_updated_by' => get_current_user_id(),
						'last_updated' => date( 'Y-m-d H:i:s' )
					);
		if( !is_array( $event ) )
			$event = array( $event );	
		foreach( $event as $event_id )	{
		$eventinfo = f_mdjm_get_eventinfo_by_id( $event_id );
		$update_event = $wpdb->update( $db_tbl['events'], $update, array( 'event_id' => $event_id ) );
		$clientinfo = get_userdata( $eventinfo->user_id );
		$j_args = array (
					'client' => $eventinfo->user_id,
					'event' => $event_id,
					'author' => get_current_user_id(),
					'type' => 'Recover Event',
					'source' => 'Admin',
					'entry' => 'Event ID ' . $event_id . ' has been recovered'
				);
		if( WPDJM_JOURNAL == 'Y' ) f_mdjm_do_journal( $j_args );
		}
		$class = 'updated';
		$message = 'The selected events have been successfully recovered';
		f_mdjm_update_notice( $class, $message );
		f_mdjm_render_events_table();
	} // f_mdjm_recover_event

/**

 * f_mdjm_complete_event
 * Complete selected events
 *
 * @param $event (event id)
 * @return
 *
 * @since 1.0
*/
	function f_mdjm_complete_event( $event )	{
		global $wpdb;
		include( WPMDJM_PLUGIN_DIR . '/includes/config.inc.php' );
		$update = array(
						'contract_status' => 'Completed',
						'last_updated_by' => get_current_user_id(),
						'last_updated' => date( 'Y-m-d H:i:s' )
					);
		if( !is_array( $event ) )
			$event = array( $event );
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
		$class = 'updated';
		$message = 'The selected events have been successfully marked as completed';
		f_mdjm_update_notice( $class, $message );
		f_mdjm_render_events_table();
	} // f_mdjm_complete_event

/**

 * f_mdjm_fail_enquiry
 * Mark selected events as Failed Enquiry
 *
 * @param $event (event id)
 * @return
 *
 * @since 1.0
*/
	function f_mdjm_fail_enquiry( $event )	{
		global $wpdb;
		include( WPMDJM_PLUGIN_DIR . '/includes/config.inc.php' );
		$update = array(
						'contract_status' => 'Failed Enquiry',
						'last_updated_by' => get_current_user_id(),
						'last_updated' => date( 'Y-m-d H:i:s' )
					);
		if( !is_array( $event ) )
			$event = array( $event );
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
		$class = 'updated';
		$message = 'The selected enquiries have been marked as lost';
		f_mdjm_update_notice( $class, $message );
		f_mdjm_render_events_table();
	} // f_mdjm_fail_enquiry

/**
 * f_mdjm_deposit_paid
 * Mark Deposit as paid
 *
 * @param $event_id (event id)
 * @return true : false
 *
 * @since 1.0
*/
	function f_mdjm_deposit_paid( $id )	{
		global $wpdb;
		include( WPMDJM_PLUGIN_DIR . '/includes/config.inc.php' );
		$eventinfo = f_mdjm_get_eventinfo_by_id( $id );
		$update = array(
							'deposit_status' => 'Paid',
							'last_updated_by' => get_current_user_id(),
							'last_updated' => date( 'Y-m-d H:i:s' ),
					);
		$wpdb->update( $db_tbl['events'], $update, array( 'event_id' => $id ) );
					
		$j_args = array (
			'client' => $eventinfo->user_id,
			'event' => $id,
			'author' => get_current_user_id(),
			'type' => 'Deposit Paid',
			'source' => 'Admin',
			'entry' => 'The deposit of &pound;' . $eventinfo->deposit . ' has been paid'
			);
		if( WPDJM_JOURNAL == 'Y' ) f_mdjm_do_journal( $j_args );
		
	} // f_mdjm_deposit_paid

/*
* f_mdjm_balance_paid
* 23/11/2014
* @since 0.9.3
* Mark the balance as paid
*/
	function f_mdjm_balance_paid( $id )	{
		global $wpdb;
		include( WPMDJM_PLUGIN_DIR . '/includes/config.inc.php' );
		$eventinfo = f_mdjm_get_eventinfo_by_id( $id );
		$update = array(
							'balance_status' => 'Paid',
							'last_updated_by' => get_current_user_id(),
							'last_updated' => date( 'Y-m-d H:i:s' ),
					);
		$wpdb->update( $db_tbl['events'], $update, array( 'event_id' => $id ) );
					
		$j_args = array (
			'client' => $eventinfo->user_id,
			'event' => $id,
			'author' => get_current_user_id(),
			'type' => 'Balance Paid',
			'source' => 'Admin',
			'entry' => 'The balance of &pound;' . $eventinfo->cost - $eventinfo->deposit . ' has been paid'
			);
		if( WPDJM_JOURNAL == 'Y' ) f_mdjm_do_journal( $j_args );
		
	} // f_mdjm_balance_paid

/*
* f_mdjm_admin_get_client_events
* 25/11/2014
* @since 0.9.4
* Retrieves all client events regardless of status
*/
	function f_mdjm_admin_get_client_events( $client_id )	{
		global $wpdb;
		
		if( !isset( $db_tbl ) )
			include( WPMDJM_PLUGIN_DIR . '/includes/config.inc.php' );
		
		$event_query = "SELECT * FROM `" . $db_tbl['events'] . "` WHERE `user_id` = '" . $client_id . "' ORDER BY `contract_status`, `event_date` DESC";
		
		$eventinfo = $wpdb->get_results( $event_query );
		
		return $eventinfo;
	} // f_mdjm_admin_get_client_events

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
		
		$info['num_rows'] = $wpdb->get_var( "SELECT COUNT(*) FROM `" . $db_tbl['events' ]. "` WHERE `user_id` = '" . $client . "' AND `contract_status` != 'Cancelled' AND `contract_status` != 'Failed Enquiry'" );

		$next_event = $wpdb->get_row( "SELECT * FROM `".$db_tbl['events']."` WHERE `user_id` = '".$client."' AND `contract_status` = 'Approved' OR `contract_status` = 'Pending' AND `event_date` >= curdate() ORDER BY `event_date` ASC LIMIT 1" );

		$info['next_event'] = 'N/A';

		if( $next_event->num_rows > 0 )	{
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
* f_mdjm_get_eventinfo_by_status
* 13/11/2014
* Since 0.9.3
* Retrieve events detail by status 
*/
	function f_mdjm_get_eventinfo_by_status( $status )	{
		global $wpdb;
		include( WPMDJM_PLUGIN_DIR . '/includes/config.inc.php' );
		
		$event_query = "SELECT * FROM `" . $db_tbl['events'] . "` WHERE `contract_status` = '" . $status . "'";
		$eventinfo = $wpdb->get_results( $event_query );
		return $eventinfo;
	} // f_mdjm_get_eventinfo_by_status

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
		global $wpdb, $mdjm_options;
		include( WPMDJM_PLUGIN_DIR . '/includes/config.inc.php' );
		
		$info['all_events'] = $wpdb->get_var( "SELECT COUNT(*) FROM `".$db_tbl['events']."` WHERE `event_dj` = '".$dj."' AND `contract_status` != 'Failed Enquiry' AND `contract_status` != 'Cancelled'" );

		$info['active_events'] = $wpdb->get_var( "SELECT COUNT(*) FROM `".$db_tbl['events']."` WHERE `event_dj` = '".$dj."' AND `event_date` >= DATE(NOW()) AND `contract_status` != 'Cancelled' AND `contract_status` != 'Completed' AND `contract_status` != 'Enquiry' AND `contract_status` != 'Failed Enquiry'" );

		$info['enquiries'] = $wpdb->get_var( "SELECT COUNT(*) FROM `".$db_tbl['events']."` WHERE `event_dj` = '".$dj."' AND `contract_status` = 'Enquiry' AND `event_date` >= curdate()" );

		$next_event = $wpdb->get_row( "SELECT * FROM `".$db_tbl['events']."` WHERE `event_dj` = '".$dj."' AND `event_date` >= curdate() AND `contract_status` != 'Cancelled' AND `contract_status` != 'Completed' AND `contract_status` != 'Enquiry' ORDER BY `event_date` ASC LIMIT 1" );
		
		$info['next_event'] = 'N/A';
		if( $wpdb->num_rows > 0 )	{
			$info['next_event'] = date( "jS F Y", strtotime( $next_event->event_date ) );
			$info['next_event_start'] = date( $mdjm_options['time_format'], strtotime( $next_event->event_start ) );
			$info['num_rows'] = $wpdb->num_rows;
		}
		$info['event_id'] = $next_event->event_id;
		$info['event_type'] = $next_event->event_type;
		return $info;
	} // f_mdjm_dj_get_events
	
/*
* f_mdjm_get_dj_events
* 01/12/2014
* @since 0.9.6
* Returns all DJ eventinfo as array
*/
	function f_mdjm_get_dj_events( $dj_id )	{
		global $wpdb;
		
		if( !isset( $db_tbl ) )
			include( WPMDJM_PLUGIN_DIR . '/includes/config.inc.php' );
			
		$query = "SELECT * FROM " . $db_tbl['events'] . " WHERE `event_dj` = " . $dj_id . " ORDER BY `contract_status`, `event_date`";
		$dj_events = $wpdb->get_results( $query );
		
		return $dj_events;
	} // f_mdjm_get_dj_events

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
	
/*
* f_mdjm_count_playlist_records
* 25/11/2014
* @since 0.9.4
* Prints the number of playlist records in the specified state
*/
	function f_mdjm_count_playlist_records_uploaded()	{
		global $wpdb;
		include_once( WPMDJM_PLUGIN_DIR . '/includes/config.inc.php' );
		
		$pl_query = "SELECT COUNT(*) FROM `". $db_tbl['playlists'] . "` WHERE `date_to_mdjm` != '' AND `date_to_mdjm` IS NOT NULL";
		$pl_result = $wpdb->get_var( $pl_query );
		
		echo $pl_result;
		
	} // f_mdjm_count_playlist_records

/****************************************************************************************************
--	VENUE FUNCTIONS
****************************************************************************************************/
/**
 * f_mdjm_get_venue_by_id
 * Retrieve venue detail by id 
 *
 * @param $venue_id (venue id)
 * @return array
 *
 * @since 1.0
*/
	function f_mdjm_get_venue_by_id( $venue_id )	{
		global $wpdb;
		include( WPMDJM_PLUGIN_DIR . '/includes/config.inc.php' );
		$venueinfo = $wpdb->get_row( 'SELECT * FROM `' . $db_tbl['venues'] . '` WHERE `venue_id` = ' . $venue_id );
		return $venueinfo;
	} // f_mdjm_get_venue_by_id

/**
 * f_mdjm_admin_add_venue
 * Add a new venue to the DB
 *
 *
 * @since 1.0
*/
	function f_mdjm_add_venue( $venue )	{
		global $wpdb;
		
		require_once( WPMDJM_PLUGIN_DIR . '/includes/config.inc.php' );
		
		if( !isset( $venue['venue_name'] ) || empty( $venue['venue_name'] ) )	{
			f_mdjm_update_notice( 'error', 'ERROR: You must enter a Venue name' );
			return;
		}
		else	{
			if ( $wpdb->insert( $db_tbl['venues'],
											array(
												'venue_id'	       => '',
												'venue_name'	  	 => sanitize_text_field( $venue['venue_name'] ),
												'venue_address1'  	 => sanitize_text_field( $venue['venue_address1'] ),
												'venue_address2'  	 => sanitize_text_field( $venue['venue_address2'] ),
												'venue_town'	  	 => sanitize_text_field( $venue['venue_town'] ),
												'venue_county'	   => sanitize_text_field( $venue['venue_county'] ),
												'venue_postcode'  	 => sanitize_text_field( $venue['venue_postcode'] ),
												'venue_contact'   	  => sanitize_text_field( $venue['venue_contact'] ),
												'venue_phone'	 	=> sanitize_text_field( $venue['venue_phone'] ),
												'venue_email'		=> sanitize_email( $venue['venue_email'] ),
												'venue_information'  => $venue['venue_information'],
											) ) )	{
				wp_redirect( admin_url( 'admin.php?page=mdjm-venues&updated=1' ) );
			}
			else	{
				f_mdjm_update_notice( 'error', 'ERROR: ' . $wpdb->print_error() );	
			}
		}
	} // f_mdjm_add_venue
	
/**
 * f_mdjm_edit_venue
 * Update existing venue info
 *
 * @param $venue_id (venue id)
 * @return true : false
 *
 * @since 1.0
*/
	function f_mdjm_edit_venue( $venue_updates )	{
		global $wpdb, $mdjm_options;
		require_once( WPMDJM_PLUGIN_DIR . '/includes/config.inc.php' );
		
		$updated_fields = array();
		
		/* Which fields need updating? */
		$venueinfo = f_mdjm_get_venue_by_id( $venue_updates['venue_id'] );
		if( $venueinfo )	{ // Update
			foreach( $venue_updates as $key => $value )	{
				if( $key != 'venuet_id' && $key != 'action' && $key != '_wpnonce' && $key != '_wp_http_referer' && $key != 'submit' )	{
					if( $value != $venuetinfo->$key )	{
						$updated_fields[$key] = $value;
						$wpdb->update( $db_tbl['venues'], 
							array( $key => $updated_fields[$key] ), 
							array( 'venue_id' => $venueinfo->venue_id ) );
					}
				}
			}
			wp_redirect( admin_url( 'admin.php?page=mdjm-venues&updated=2' ) );
		}
		else	{
			f_mdjm_update_notice( 'error', 'ERROR: ' . $wpdb->print_error() );	
		}
	} // f_mdjm_edit_venue
	
/**
 * f_mdjm_delete_venue
 * Delete venue from database
 *
 * @param $venue_id (venue id)
 * @return true : false
 *
 * @since 1.0
*/
	function f_mdjm_delete_venue( $venues )	{
		global $wpdb;
		require_once( WPMDJM_PLUGIN_DIR . '/includes/config.inc.php' );
		if( !is_array( $venues ) ) $venues = array( $venues );
		foreach( $venues as $venue )	{
			$wpdb->delete( $db_tbl['venues'], array( 'venue_id' => $venue ) );
		}
		wp_redirect( admin_url( 'admin.php?page=mdjm-venues&updated=3' ) );
	} // f_mdjm_delete_venue
	
/**
 * f_mdjm_venue_options
 * Print out venue options for drop down
 *
 *
 * @since 1.0
*/
	function f_mdjm_get_venueinfo()	{
		global $wpdb;
		require_once( WPMDJM_PLUGIN_DIR . '/includes/config.inc.php' );	
		$venueinfo = $wpdb->get_results( "SELECT * FROM `".$db_tbl['venues']."` ORDER BY 'venue_name' ASC" );
		return $venueinfo;
	}

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
		if( isset( $mdjm_options['dj_' . $permission] ) && $mdjm_options['dj_' . $permission] == 'Y' ) return true;
		else return false;
	} // dj_can

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
	function f_mdjm_get_clients( $role, $orderby, $order )	{
		$arg = array(	'role' => $role,
						'orderby' => $orderby,
						'order' => $order
						);
		$clients = get_users( $arg );
		return $clients;
	} // f_mdjm_get_clients

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
	
/*
* f_mdjm_is_client
* 02/12/2014
* @since 0.9.5
* Checks whether a user is a client or not by given ID
*/
	function f_mdjm_is_client( $id )	{
		global $wpdb;
		
		if( !isset( $db_tbl ) )
			include( WPMDJM_PLUGIN_DIR . '/includes/config.inc.php' );
		
		$event_count = $wpdb->get_var( "SELECT COUNT(*) FROM " . $db_tbl['events'] . " WHERE `user_id` = " . $id );
		
		if( $event_count > 0 )	{
			return true;	
		}
		else	{
			return false;	
		}
	} // f_mdjm_is_client

/****************************************************************************************************
--	CONTRACT FUNCTIONS
****************************************************************************************************/

/**
 * f_mdjm_get_contractinfo
 * Returns contract data
 *
 * @param $id
 * @return object array
 *
 * @since 1.0
*/
	function f_mdjm_get_contractinfo( $id )	{
		$contractinfo = get_post( $id );
		return $contractinfo;
	} // f_mdjm_get_contractinfo

	

/****************************************************************************************************
--	EMAIL FUNCTIONS
****************************************************************************************************/

/**
 * f_mdjm_client_email_headers
 * Generate email headers for email to client
 *
 * 
 * @since 1.0
*/
	function f_mdjm_client_email_headers( $event )	{
		global $mdjm_options;
		if( !empty( $event->event_dj ) ) $dj = get_userdata( $event->event_dj );

		$email_headers = 'MIME-Version: 1.0' . "\r\n";
		$email_headers .= 'Content-type: text/html; charset=UTF-8' . "\r\n";
		if( $event->contract_status == 'Enquiry' || $event->contract_status == 'Pending' )	{ /* Enquiries & Pending come from site admin */
			$email_headers .= 'From: ' . $dj->display_name . ' <' . $mdjm_options['system_email'] . '>' . "\r\n";
		}
		else	{ /* Everything else from the DJ */
			$email_headers .= 'From: ' . $dj->display_name . ' <bookings' . substr( $mdjm_options['system_email'], strpos( $mdjm_options['system_email'], "@" ) + 1 ) . '>' . "\r\n";
			$email_headers .= 'Reply-To: ' . $dj->user_email . "\r\n";
		}
		if( isset( $mdjm_options['bcc_admin_to_client'] ) && $mdjm_options['bcc_admin_to_client'] == 'Y'
			|| isset( $mdjm_options['bcc_dj_to_client'] ) && $mdjm_options['bcc_dj_to_client'] == 'Y' )	{
			$email_headers .= 'Bcc: ';
			if( isset( $mdjm_options['bcc_dj_to_client'] ) && $mdjm_options['bcc_dj_to_client'] == 'Y' )
				$email_headers .= $dj->user_email;

			if( isset( $mdjm_options['bcc_admin_to_client'] ) && $mdjm_options['bcc_admin_to_client'] == 'Y'
				&& isset( $mdjm_options['bcc_dj_to_client'] ) && $mdjm_options['bcc_dj_to_client'] == 'Y' )
				$email_headers .= ', ';

			if( isset( $mdjm_options['bcc_admin_to_client'] ) && $mdjm_options['bcc_admin_to_client'] == 'Y' )
				$email_headers .= $mdjm_options['system_email'];
				
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
		if( is_array( $type ) )	{ // No template id passed so get it
			$template_id = $type['id'];
		}
		else	{
			$template_id = $mdjm_options[$type];
		}
		
		include( WPMDJM_PLUGIN_DIR . '/admin/includes/config.inc.php' );
		$template_query = new WP_Query( array( 'post_type' => 'email_template', 'post__in' => array( $template_id ) ) );
		if ( $template_query->have_posts() ) {
			while ( $template_query->have_posts() ) {
				$template_query->the_post();
				/* Check if we are using the post title as the subject */
				if( isset( $mdjm_options['title_as_subject'] ) && $mdjm_options['title_as_subject'] == 'Y' && !is_array( $type ) )	{
					$subject = get_the_title();
				}
				$content = get_the_content();
				$content = apply_filters( 'the_content', $content );
				$content = str_replace(']]>', ']]&gt;', $content);
			}
		}
		$info['subject'] = str_replace( $shortcode_content_search, $shortcode_content_replace, $subject );
		$info['content'] = '<html><body>';
		$info['content'] .= str_replace( $shortcode_content_search, $shortcode_content_replace, $content );
		$info['content'] .= '</body></html>';
		
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
		$email_headers .= 'Content-type: text/html; charset=UTF-8' . "\r\n";
		$email_headers .= 'From: ' . $mdjm_options['company_name'] . ' <' . $mdjm_options['system_email'] . '>' . "\r\n";
		
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
		
		// DJ Active Events
		$dash_dj['month_active_events'] = $wpdb->get_var( "SELECT COUNT(*) FROM `".$db_tbl['events']."` WHERE `event_dj` = '" .get_current_user_id(). "' AND monthname(event_date) = '" . date( 'F' ) . "' AND `contract_status` = 'Approved'" );
		
		// DJ Active Enquiries for Month
		$dash_dj['month_enquiries'] = $wpdb->get_var( "SELECT COUNT(*) FROM `".$db_tbl['events']."` WHERE `event_dj` = '" .get_current_user_id(). "' AND monthname(event_date) = '" . date( 'F' ) . "' AND `contract_status` = 'Enquiry'" );
		
		// DJ Enquiries lost this month
		$dash_dj['lost_month_enquiries'] = $wpdb->get_var( "SELECT COUNT(*) FROM `".$db_tbl['events']."` WHERE `event_dj` = '" .get_current_user_id(). "' AND monthname(event_date) = '" . date( 'F' ) . "' AND `contract_status` = 'Failed Enquiry'" );
		
		// DJ Events completed this month
		$dash_dj['month_completed'] = $wpdb->get_var( "SELECT COUNT(*) FROM `".$db_tbl['events']."` WHERE `event_dj` = '" .get_current_user_id(). "' AND monthname(event_date) = '" . date( 'F' ) . "' AND `contract_status` = 'Completed'" );
		
		// DJ Total enquiries for the year
		$dash_dj['year_enquiries'] = $wpdb->get_var( "SELECT COUNT(*) FROM `".$db_tbl['events']."` WHERE `event_dj` = '" .get_current_user_id(). "' AND year(event_date) = '" . date( 'Y' ) . "' AND `contract_status` = 'Enquiry'" );
		
		// DJ Enquiries lost this year
		$dash_dj['lost_year_enquiries'] = $wpdb->get_var( "SELECT COUNT(*) FROM `".$db_tbl['events']."` WHERE `event_dj` = '" .get_current_user_id(). "' AND year(event_date) = '" . date( 'Y' ) . "' AND `contract_status` = 'Failed Enquiry'" );
		
		// DJ Events completed this year
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
		
		// Employer's active events for month
		$dash_emp['month_active_events'] = $wpdb->get_var( "SELECT COUNT(*) FROM `".$db_tbl['events']."` WHERE monthname(event_date) = '" . date( 'F' ) . "' AND `contract_status` = 'Approved'" );
		
		// Employers enquiries for month
		$dash_emp['month_enquiries'] = $wpdb->get_var( "SELECT COUNT(*) FROM `".$db_tbl['events']."` WHERE monthname(event_date) = '" . date( 'F' ) . "' AND `contract_status` = 'Enquiry'" );
		
		// Employers lost enquiries for month
		$dash_emp['lost_month_enquiries'] = $wpdb->get_var( "SELECT COUNT(*) FROM `".$db_tbl['events']."` WHERE monthname(event_date) = '" . date( 'F' ) . "' AND `contract_status` = 'Failed Enquiry'" );
		
		// Employers events completed for month
		$dash_emp['month_completed'] = $wpdb->get_var( "SELECT COUNT(*) FROM `".$db_tbl['events']."` WHERE monthname(event_date) = '" . date( 'F' ) . "' AND `contract_status` = 'Completed'" );
		
		// Total employer enquiries this year
		$dash_emp['year_enquiries'] = $wpdb->get_var( "SELECT COUNT(*) FROM `".$db_tbl['events']."` WHERE year(event_date) = '" . date( 'Y' ) . "' AND `contract_status` = 'Enquiry'" );
		
		// Total employer lost enquiries this year
		$dash_emp['lost_year_enquiries'] = $wpdb->get_var( "SELECT COUNT(*) FROM `".$db_tbl['events']."` WHERE year(event_date) = '" . date( 'Y' ) . "' AND `contract_status` = 'Failed Enquiry'" );
		
		// Employer events completed this year
		$dash_emp['year_completed'] = $wpdb->get_var( "SELECT COUNT(*) FROM `".$db_tbl['events']."` WHERE year(event_date) = '" . date( 'Y' ) . "' AND `contract_status` = 'Completed'" );
		
		// Employer potential earnings for month
		$dash_emp['potential_month_earn'] = $wpdb->get_var( "SELECT SUM(cost) FROM `".$db_tbl['events']."` WHERE monthname(event_date) = '" . date( 'F' ) . "' AND `contract_status` != 'Cancelled' AND `contract_status` != 'Completed' AND `contract_status` != 'Failed Enquiry'" );
		
		// Employer actual earnings for month
		$dash_emp['month_earn'] = $wpdb->get_var( "SELECT SUM(cost) FROM `".$db_tbl['events']."` WHERE monthname(event_date) = '" . date( 'F' ) . "' AND `contract_status` = 'Completed'" );
		
		// Employer potential earnings for year
		$dash_emp['potential_year_earn'] = $wpdb->get_var( "SELECT SUM(cost) FROM `".$db_tbl['events']."` WHERE year(event_date) = '" . date( 'Y' ) . "' AND `contract_status` != 'Cancelled' AND `contract_status` != 'Failed Enquiry'" );
		
		// Employer actual earnings for year
		$dash_emp['year_earn'] = $wpdb->get_var( "SELECT SUM(cost) FROM `".$db_tbl['events']."` WHERE year(event_date) = '" . date( 'Y' ) . "' AND `contract_status` = 'Completed'" );
		
		return $dash_emp;
	} // f_mdjm_dashboard_dj_overview
?>