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
 * f_mdjm_purchase
 * Purchase a license (displays if not registered or trial)
 *
 *
 * @since 1.0
*/
	function f_mdjm_purchase()	{
		wp_redirect( 'http://www.mydjplanner.co.uk/shop/mobile-dj-manager-for-wordpress-plugin/' );
	}
	
/*
* f_mdjm_admin_page
* 02/12/2014
* @since 0.9.5
* Outputs the desired admin page URL
*/
	function f_mdjm_admin_page( $mdjm_page )	{
		global $mdjm;
		
		$mdjm->debug_logger( 'WARNING: Use of deprecated function ' . __FUNCTION__, true );
		
		$mydjplanner = array( 'mydjplanner', 'user_guides', 'mdjm_support', 'mdjm_forums' );
		$mdjm_pages = array(
						'wp_dashboard'          => 'index.php',
						'dashboard'             => 'admin.php?page=mdjm-dashboard',
						'settings'              => 'admin.php?page=mdjm-settings',
						'clients'               => 'admin.php?page=mdjm-clients',
						'inactive_clients'      => 'admin.php?page=mdjm-clients&display=inactive_client',
						'add_client'            => 'user-new.php',
						'edit_client'           => 'user-edit.php?user_id=',
						'comms'                 => 'admin.php?page=mdjm-comms',
						'email_history'         => 'edit.php?post_type=' . MDJM_COMM_POSTS,
						'contract'              => 'edit.php?post_type=' . MDJM_CONTRACT_POSTS,
						'add_contract'          => 'post-new.php?post_type=' . MDJM_CONTRACT_POSTS,
						'djs'                   => 'admin.php?page=mdjm-djs',
						'inactive_djs'          => 'admin.php?page=mdjm-djs&display=inactive_dj',
						'email_template'        => 'edit.php?post_type=' . MDJM_EMAIL_POSTS,
						'add_email_template'    => 'post-new.php?post_type=' . MDJM_EMAIL_POSTS,
						'equipment'             => 'admin.php?page=mdjm-packages',
						'events'                => 'admin.php?page=mdjm-events',
						'add_event'             => 'admin.php?page=mdjm-events&action=add_event_form',
						'enquiries'             => 'admin.php?page=mdjm-events&status=Enquiry',
						'venues'                => 'edit.php?post_type=' . MDJM_VENUE_POSTS,
						'add_venue'             => 'post-new.php?post_type=' . MDJM_VENUE_POSTS,
						'tasks'                 => 'admin.php?page=mdjm-tasks',
						'client_text'           => 'admin.php?page=mdjm-settings&tab=client_text',
						'client_fields'         => 'admin.php?page=mdjm-settings&tab=client_fields',
						'availability'          => 'admin.php?page=mdjm-availability',
						'debugging'             => 'admin.php?page=mdjm-settings&tab=debugging',
						'contact_forms'         => 'admin.php?page=mdjm-contact-forms',
						'transactions'		  => 'admin.php?page=mdjm-transactions',
						'mydjplanner'           => 'http://www.mydjplanner.co.uk',
						'user_guides'           => 'http://www.mydjplanner.co.uk/support/user-guides',
						'mdjm_support'          => 'http://www.mydjplanner.co.uk/support',
						'mdjm_forums'           => 'http://www.mydjplanner.co.uk/forums',
						);
		if( in_array( $mdjm_page, $mydjplanner ) )	{
			echo $mdjm_pages[$mdjm_page];	
		}
		else	{
			echo admin_url( $mdjm_pages[$mdjm_page] );
		}
	}

/*
* f_mdjm_currency
* 15/12/2014
* @since 0.9.9
* Prints out the currency symbol
*/
	function f_mdjm_currency()	{
		global $mdjm, $mdjm_settings;
		
		$mdjm->debug_logger( 'WARNING: Use of deprecated function ' . __FUNCTION__, true );
		
		if( !isset( $mdjm_currency ) )
			include( WPMDJM_PLUGIN_DIR . '/includes/config.inc.php' );
		
		echo $mdjm_currency[$mdjm_settings['payments']['currency']];
	} // f_mdjm_currency

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
															
			/* PLAYLISTS TABLE */
			$playlists_sql = "CREATE TABLE ". MDJM_PLAYLIST_TABLE . " (
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
								
			/* AVAILABILITY TABLE */
			$holiday_sql = "CREATE TABLE ". MDJM_HOLIDAY_TABLE . " (
								id int(11) NOT NULL AUTO_INCREMENT,
								user_id int(11) NOT NULL,
								entry_id varchar(100) NOT NULL,
								date_from date NOT NULL,
								date_to date NOT NULL,
								notes text NULL,
								PRIMARY KEY  (id),
								KEY user_id (user_id)
								);";
											
			require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
			dbDelta( $playlists_sql );
			dbDelta( $holiday_sql );
		
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
		global $mdjm;
		
		$mdjm->debug_logger( 'WARNING: Use of deprecated function ' . __FUNCTION__, true );
		
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
		global $mdjm;
		
		$mdjm->debug_logger( 'WARNING: Use of deprecated function ' . __FUNCTION__, true );
		
		echo '<div id="message" class="' . $class . '">';
		echo '<p>' . $message . '</p>';
		echo '</div>';
	} // f_mdjm_update_notice

/****************************************************************************************************
--	TO DO FUNCTIONS
****************************************************************************************************/
/*
* f_mdjm_todo_list
* 19/01/2015
* @since 1.0
* Checks for actions to do and returns results
*/
	function f_mdjm_todo_list( $mdjm_args )	{
		global $mdjm_options;
		
		
		
	} // f_mdjm_todo_list

/*
* f_mdjm_event_count
* 19/01/2015
* @since 1.0
* Retrieve count of unattended enquiries
*/
	function f_mdjm_event_count( $type, $mdjm_args )	{
		global $mdjm, $wpdb;
		
		$mdjm->debug_logger( 'WARNING: Use of deprecated function ' . __FUNCTION__, true );
		
		if( !isset( $db_tbl ) )	{
			include( WPMDJM_PLUGIN_DIR . '/includes/config.inc.php' );
		}
		$event_query = "SELECT COUNT(*) FROM `" . $db_tbl['events'] . "` WHERE `contract_status` = '" . $type . "'";
		
		if( isset( $mdjm_args['dj'] ) && $mdjm_args['dj'] === true )	{
			$event_query .= " AND `event_dj` = '" . $mdjm_args['dj'] . "'";
		}
		
		if( isset( $mdjm_args['scope'] ) && $mdjm_args['scope'] == 'month' )	{
			$event_query .= " AND MONTH(event_date) = '" . date( 'm' ) . "'";
		}
		elseif( isset( $mdjm_args['scope'] ) && $mdjm_args['scope'] == 'year' )	{
			$event_query .= " AND YEAR(event_date) = '" . date( 'Y' ) . "'";
		}
		
		$event_count = $wpdb->get_var( $event_query );
		
		if( isset( $mdjm_args['print'] ) && $mdjm_args['print'] === true )	{
			echo $event_count;
		}
		else	{
			return $event_count;
		}
		
	} // f_mdjm_event_count
	
/*
* f_mdjm_unattended_enquiries
* 19/01/2015
* @since 1.0
* Retrieve count of unattended enquiries
*/
	function f_mdjm_unattended_enquiries()	{
		global $mdjm, $wpdb;
		
		$mdjm->debug_logger( 'WARNING: Use of deprecated function ' . __FUNCTION__, true );
		
		if( !isset( $db_tbl ) )	{
			include( WPMDJM_PLUGIN_DIR . '/includes/config.inc.php' );
		}
		
		$unattended = $wpdb->get_results( "SELECT COUNT(*) FROM `" . $db_tbl['events'] . "` WHERE `contract_status` = 'Unattended'" );
		
		return $unattended;
	} // f_mdjm_unattended_enquiries_count

/****************************************************************************************************
--	GENERAL FUNCTIONS
****************************************************************************************************/
/*
* f_mdjm_all_dates_in_range
* 21/12/2014
* @since 0.9.9
* Returns all dates between 2 given dates as an array
*/
	function f_mdjm_all_dates_in_range( $from_date, $to_date )	{
		$from_date = \DateTime::createFromFormat( 'Y-m-d', $from_date );
		$to_date = \DateTime::createFromFormat( 'Y-m-d', $to_date );
		return new \DatePeriod(
			$from_date,
			new \DateInterval( 'P1D' ),
			$to_date->modify( '+1 day' )
		);
	} // f_mdjm_all_dates_in_range
	
/*
* f_mdjm_short_date_jquery
* 27/12/2014
* @since 0.9.9
* Sets the correct display date for the jQuery Date Picker
*/
	function f_mdjm_short_date_jquery()	{
		global $mdjm, $mdjm_options;
		
		$mdjm->debug_logger( 'WARNING: Use of deprecated function ' . __FUNCTION__, true );
		
		if( !isset( $mdjm_options['short_date_format'] ) || empty( $mdjm_options['short_date_format'] ) )	{
			$short_date_format = 'd/m/Y';
		}
		if( $mdjm_options['short_date_format'] == 'd/m/Y' )	{
			$short_date_format = 'dd/mm/yy';
		}
		elseif( $mdjm_options['short_date_format'] == 'm/d/Y' )	{
			$short_date_format = 'mm/dd/yy';	
		}
		elseif( $mdjm_options['short_date_format'] == 'Y/m/d' )	{
			$short_date_format = 'yy/mm/dd';	
		}
		elseif( $mdjm_options['short_date_format'] == 'd-m-Y' )	{
			$short_date_format = 'dd-mm-yy';
		}
		elseif( $mdjm_options['short_date_format'] == 'm-d-Y' )	{
			$short_date_format = 'mm-dd-yy';	
		}
		elseif( $mdjm_options['short_date_format'] == 'Y-m-d' )	{
			$short_date_format = 'yy-mm-dd';	
		}
		
		echo $short_date_format;
		
	} // f_mdjm_short_date_jquery

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
 * f_mdjm_deposit_paid
 * Mark Deposit as paid
 *
 * @param $event_id (event id)
 * @return true : false
 *
 * @since 1.0
*/
	function f_mdjm_deposit_paid( $id )	{
		global $mdjm, $wpdb;
		
		$mdjm->debug_logger( 'WARNING: Use of deprecated function ' . __FUNCTION__, true );
		
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
			'entry' => 'The deposit of ' . f_mdjm_currency() . $eventinfo->deposit . ' has been paid'
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
		global $mdjm, $wpdb;
		
		$mdjm->debug_logger( 'WARNING: Use of deprecated function ' . __FUNCTION__, true );
		
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
			'entry' => 'The balance of ' . f_mdjm_currency() . $eventinfo->cost - $eventinfo->deposit . ' has been paid'
			);
		if( WPDJM_JOURNAL == 'Y' ) f_mdjm_do_journal( $j_args );
		
	} // f_mdjm_balance_paid

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
		global $mdjm, $wpdb;
		
		$mdjm->debug_logger( 'WARNING: Use of deprecated function ' . __FUNCTION__, true );
		
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
		global $mdjm, $wpdb;
		
		$mdjm->debug_logger( 'WARNING: Use of deprecated function ' . __FUNCTION__, true );
		
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
		global $mdjm, $wpdb, $mdjm_options;
		
		$mdjm->debug_logger( 'WARNING: Use of deprecated function ' . __FUNCTION__, true );
		
		include( WPMDJM_PLUGIN_DIR . '/includes/config.inc.php' );
		
		$info['all_events'] = $wpdb->get_var( "SELECT COUNT(*) FROM `".$db_tbl['events']."` WHERE `event_dj` = '".$dj."' AND `contract_status` != 'Failed Enquiry' AND `contract_status` != 'Cancelled'" );

		$info['active_events'] = $wpdb->get_var( "SELECT COUNT(*) FROM `".$db_tbl['events']."` WHERE `event_dj` = '".$dj."' AND `event_date` >= DATE(NOW()) AND `contract_status` != 'Cancelled' AND `contract_status` != 'Completed' AND `contract_status` != 'Enquiry' AND `contract_status` != 'Failed Enquiry'" );

		$info['enquiries'] = $wpdb->get_var( "SELECT COUNT(*) FROM `".$db_tbl['events']."` WHERE `event_dj` = '".$dj."' AND `contract_status` = 'Enquiry' AND `event_date` >= curdate()" );

		$next_event = $wpdb->get_row( "SELECT * FROM `".$db_tbl['events']."` WHERE `event_dj` = '".$dj."' AND `event_date` >= curdate() AND `contract_status` != 'Cancelled' AND `contract_status` != 'Completed' AND `contract_status` != 'Enquiry' AND `contract_status` != 'Failed Enquiry' ORDER BY `event_date` ASC LIMIT 1" );
		
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
		global $mdjm, $wpdb;
		
		$mdjm->debug_logger( 'WARNING: Use of deprecated function ' . __FUNCTION__, true );
		
		if( !isset( $db_tbl ) )
			include( WPMDJM_PLUGIN_DIR . '/includes/config.inc.php' );
			
		$query = "SELECT * FROM " . $db_tbl['events'] . " WHERE `event_dj` = " . $dj_id . " ORDER BY `contract_status`, `event_date`";
		$dj_events = $wpdb->get_results( $query );
		
		return $dj_events;
	} // f_mdjm_get_dj_events
	
/*
* f_mdjm_event_is_active
* 08/12/2014
* @since 0.9.7
* Checks if an event is active. True if so, false if not
*/
	function f_mdjm_event_is_active( $eventinfo )	{
		global $mdjm, $mdjm_options;
		
		$mdjm->debug_logger( 'WARNING: Use of deprecated function ' . __FUNCTION__, true );
		
		if( $eventinfo->contract_status != 'Completed' && $eventinfo->contract_status != 'Cancelled' )	{
			if( !isset( $mdjm_options['dj_view_enquiry'] ) && $mdjm_options['dj_view_enquiry'] != 'Y' )	{
				if( $eventinfo->contract_status == 'Enquiry' && $eventinfo->contract_status != 'Failed Enquiry' )	{
					return false;	
				}
			}
			return true;
		}
		else	{
			return false;	
		}
	} // f_mdjm_event_is_active

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
		global $mdjm;
		
		$mdjm->debug_logger( 'WARNING: Use of deprecated function ' . __FUNCTION__, true );
		
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
		global $mdjm, $wpdb;
		
		$mdjm->debug_logger( 'WARNING: Use of deprecated function ' . __FUNCTION__, true );
		
		if( !isset( $db_tbl ) )
			include( WPMDJM_PLUGIN_DIR . '/includes/config.inc.php' );
		
		$pl_query = "SELECT COUNT(*) FROM `". $db_tbl['playlists'] . "` WHERE `date_to_mdjm` != '' AND `date_to_mdjm` IS NOT NULL";
		$pl_result = $wpdb->get_var( $pl_query );
		
		echo $pl_result;
		
	} // f_mdjm_count_playlist_records
	
/*
* f_mdjm_delete_from_playlist
* 25/02/2015
* @since 1.1
* Delete the specified song from playlist
*/
	function f_mdjm_delete_from_playlist( $pl )	{
		global $mdjm, $wpdb;
		
		$mdjm->debug_logger( 'WARNING: Use of deprecated function ' . __FUNCTION__, true );
		
		if( !isset( $db_tbl ) )	{
			include( WPMDJM_PLUGIN_DIR . '/includes/config.inc.php' );
		}
		$wpdb->delete( $db_tbl['playlists'], array( 'id' => $pl ) );
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
		global $mdjm, $mdjm_options;
		
		$mdjm->debug_logger( 'WARNING: Use of deprecated function ' . __FUNCTION__, true );
		
		$admin_arg = array( 'role' => 'administrator',
							'orderby' => 'display_name',
							'order' => 'ASC'
						);
		$admin = get_users( $admin_arg );
		
		if( isset( $mdjm_options['multiple_dj'] ) && $mdjm_options['multiple_dj'] == 'Y' )	{
			$dj_arg = array(	'role' => 'dj',
								'orderby' => 'display_name',
								'order' => 'ASC'
							);
			$dj = get_users( $dj_arg );
			$djs = array_merge( $admin, $dj );
		}
		else	{
			$djs = $admin;	
		}
		
		return $djs;
	} // f_mdjm_get_djs
	
/*
* f_mdjm_get_inactive_djs
* 18/01/2015
* @since 0.9.9.6
* Generate list of all inactive DJ's
*/
	function f_mdjm_get_inactive_djs()	{
		global $mdjm, $mdjm_options;
		
		$mdjm->debug_logger( 'WARNING: Use of deprecated function ' . __FUNCTION__, true );		
		
		$dj_arg = array(	'role' => 'inactive_dj',
							'orderby' => 'display_name',
							'order' => 'ASC'
						);
		$inactive_djs = get_users( $dj_arg );

		return $inactive_djs;
	} // f_mdjm_get_inactive_djs
	
/**
 * f_mdjm_dj_working_today
 * Check for DJ's booked today
 *
 * @return $dj_event_results
 *
 * @since 1.0
*/		
	function f_mdjm_dj_working_today()	{
		global $mdjm, $wpdb;
		
		$mdjm->debug_logger( 'WARNING: Use of deprecated function ' . __FUNCTION__, true );
		
		include( WPMDJM_PLUGIN_DIR . '/includes/config.inc.php' );
		$dj_event_query = "SELECT * FROM `" . $db_tbl['events'] . "` WHERE `event_dj` != '" . get_current_user_id() . "' AND DATE(event_date) = CURDATE() AND `contract_status` = 'Approved'";
		$dj_event_results = $wpdb->get_results( $dj_event_query );
	
		return $dj_event_results;
	} // f_mdjm_dj_working_today

/*
* f_mdjm_dj_is_working
* 19/12/2014
* @since 0.9.9
* Checks if DJ is working on given date
*/
	function f_mdjm_dj_is_working( $dj, $check_date )	{
		global $mdjm, $wpdb;
		
		$mdjm->debug_logger( 'WARNING: Use of deprecated function ' . __FUNCTION__, true );
		
		if( !isset( $db_tbl ) )
			include( WPMDJM_PLUGIN_DIR . '/includes/config.inc.php' );
			
		$dj_work_query = "SELECT * FROM " . $db_tbl['events'] . " WHERE `event_dj` = '" . $dj . "' AND DATE(event_date) = '" . $check_date . "' AND `contract_status` = 'Approved'";
		$dj_work_result = $wpdb->get_results( $dj_work_query );
		
		if( $dj_work_result )	{ // DJ is working
			return true;	
		}
		else	{
			return false;	
		}
	} // f_mdjm_dj_is_working
	
/*
* f_mdjm_available
* 19/12/2014
* @since 0.9.9
* Checks for availability on given date
*/
	function f_mdjm_available()	{
		global $wpdb, $mdjm, $mdjm_options;
		
		$mdjm->debug_logger( 'WARNING: Use of deprecated function ' . __FUNCTION__, true );
		
		$args = func_get_args();
		
		if( func_num_args() == 1 )	{ // Assume no DJ passed
			/* Get list of DJ's */
			$djs = f_mdjm_get_djs();
		}
		else	{
			$djs = get_userdata( $args[1] );
		}
		
		$dj_count = count( $djs );
		$available_dj = array();
		$can_work = array();
		/* Check holiday table */
		foreach( $djs as $dj )	{
			if( !f_mdjm_dj_on_holiday( $dj->ID, $args[0] ) )	{
				$dj_count = $dj_count - 1;
			}
			else	{
				$available_dj[] = $dj->ID;	
			}
		}
		if( $dj_count > 0 )	{
			/* Check if DJ is working */
			foreach( $available_dj as $dj )	{
				if( f_mdjm_dj_is_working( $dj, $args[0] ) )	{
					$dj_count = $dj_count - 1;
				}
				else	{
					$can_work[] = $dj;
				}
			}
		}
		if( count( $can_work ) == '0' || empty( $can_work[0] ) )	{ // No DJ's available
			return false;	
		}
		else	{
			return $can_work;	
		}
	} // f_mdjm_availabile
		
/****************************************************************************************************
--	AVAILABILITY FUNCTIONS
****************************************************************************************************/
/**
* f_mdjm_add_holiday
* 15/12/2014
* @since 0.9.9
* Insert holiday record into DB
*/
	function f_mdjm_add_holiday( $args )	{
		global $wpdb;
		if( !isset( $db_tbl ) )
			include( WPMDJM_PLUGIN_DIR . '/includes/config.inc.php' );
		
		$date_range = f_mdjm_all_dates_in_range( $args['from_date'], $args['to_date'] );
		foreach( $date_range as $the_date )	{
			if ( $wpdb->insert( $db_tbl['holiday'],
										array(
											'id'	     => '',
											'user_id'    => $args['employee'],
											'entry_id'   => get_current_user_id() . '_' . time(),
											'date_from'  => $the_date->format( 'Y-m-d' ),
											'date_to'  	 => $args['to_date'],
											'notes'      => $args['notes'],
										) ) )	{

								
			}
		}
		f_mdjm_update_notice( 'updated', 'The entry was added successfully' );	
	} // f_mdjm_add_holiday
	
/**
* f_mdjm_remove_holiday
* 12/03/2014
* @since 1.1.2
* Remove holiday record from DB
*/
	function f_mdjm_remove_holiday( $entry )	{
		global $wpdb;
		if( empty( $entry ) )	{
			return f_mdjm_update_notice( 'error', 'Could not remove entry' );	
		}
		if( !isset( $db_tbl ) )
			include( WPMDJM_PLUGIN_DIR . '/includes/config.inc.php' );
		
		if ( $wpdb->delete( $db_tbl['holiday'], array( 'id' => $entry, ) ) )	{
			f_mdjm_update_notice( 'updated', 'The entry was <strong>deleted</strong> successfully' );					
		}
		else	{
			f_mdjm_update_notice( 'error', 'Could not remove entry' );	
		}
	} // f_mdjm_add_holiday

/**
* f_mdjm_dj_on_holiday
* 19/12/2014
* @since 0.9.9
* Checks if DJ is on holiday on given day
*/	
	function f_mdjm_dj_on_holiday( $dj, $check_date )	{
		global $mdjm, $wpdb;
		
		$mdjm->debug_logger( 'WARNING: Use of deprecated function ' . __FUNCTION__, true );
		
		if( !isset( $db_tbl ) )
			include( WPMDJM_PLUGIN_DIR . '/includes/config.inc.php' );
			
		$hol_query = "SELECT * FROM " . $db_tbl['holiday'] . " WHERE `user_id` = '" . $dj . "' AND DATE(date_from) = '" . $check_date . "'";
		
		$hol_result = $wpdb->get_results( $hol_query );
		
		if( $hol_result )	{
			return false;	
		}
		else	{
			return true;	
		}
		
	} // dj_on_holiday
	
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
		global $mdjm, $wpdb;
		
		$mdjm->debug_logger( 'WARNING: Use of deprecated function ' . __FUNCTION__, true );
		
		include( WPMDJM_PLUGIN_DIR . '/includes/config.inc.php' );
		$client_query = $wpdb->get_var( "SELECT COUNT(*) FROM `" . $db_tbl['events'] . "` WHERE `user_id` = '" . $client_id . "' AND `event_dj` = '" . get_current_user_id() . "'" );
		
		if( $client_query != '0' )	{
			return true;
		}
		else	{
			return false;
		}
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
		global $mdjm;
		
		$mdjm->debug_logger( 'WARNING: Use of deprecated function ' . __FUNCTION__, true );
		
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
		global $mdjm;
		
		$mdjm->debug_logger( 'WARNING: Use of deprecated function ' . __FUNCTION__, true );
		
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
		global $mdjm, $wpdb;
		
		$mdjm->debug_logger( 'WARNING: Use of deprecated function ' . __FUNCTION__, true );
		
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
	
/*
* f_mdjm_set_client_role()
* 01/01/2015
* @since 1.0
* Updates the client's role within WordPress
*/
	function f_mdjm_set_client_role( $clients, $role )	{
		global $mdjm;
		
		$mdjm->debug_logger( 'WARNING: Use of deprecated function ' . __FUNCTION__, true );
		
		/* $clients must be an array */
		if( !is_array( $clients ) ) $clients = array( $clients );
		$user_count = count( $clients );
		$role_name = array(
						'client'          => 'Active',
						'inactive_client' => 'Inactive',
						'dj'              => 'Active',
						'inactive_dj'     => 'Inactive',
						);
		if( $user_count == 1 )	{
			$user = 'user';
		}
		else	{
			$user = 'users';
		}
		$i = 0;
		foreach( $clients as $client )	{
			$user_id = wp_update_user( array( 'ID' => $client, 'role' => $role ) );
			
			if ( is_wp_error( $user_id ) )	{ /* Action Failed */
				$user_error = true;
			}
			else	{ /* Action Succeeded */
				$i++;
			}
		}
		if( $user_error && $i == 0 )	{
			$class = 'error';
			$message = 'ERROR: ' . $i . ' users were set as ' . $role_name[$role] . '.<br />Contact <a href="http://www.mydjplanner.co.uk/forums/forum/bugs/" target="_blank" title="Report this bug">MDJM Support</a> with details of any errors that are displayed on your screen.';
		}
		elseif( $user_error && $i < $user_count )	{
			$class = 'update-nag';
			$message = 'WARNING: Some errors occured and only ' . $i . ' out of ' . $user_count . ' ' . $user . ' were set as ' . $role_name[$role] . '.';
		}
		else	{
			$class = 'updated';
			$message = $i . ' ' . $user . ' successfully marked as ' . $role_name[$role] . '.';	
		}
		f_mdjm_update_notice( $class, $message );
		
	} // f_mdjm_set_client_role

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
	function f_mdjm_client_email_headers( $event, $email_from )	{
		global $mdjm_options;
		if( !empty( $event->event_dj ) ) $dj = get_userdata( $event->event_dj );

		$email_headers = 'MIME-Version: 1.0' . "\r\n";
		$email_headers .= 'Content-type: text/html; charset=UTF-8' . "\r\n";
		
		/* Who should email be sent from? */
		if( $email_from == 'dj' && !empty( $event->event_dj ) )	{ // DJ
			$email_headers .= 'From: ' . $dj->display_name . ' <' . $dj->user_email . '>' . "\r\n";
		}
		else	{ // Admin
			$email_headers .= 'From: ' . $mdjm_options['company_name'] . ' <' . $mdjm_options['system_email'] . '>' . "\r\n";
		}
		
		if( isset( $mdjm_options['bcc_admin_to_client'] ) && $mdjm_options['bcc_admin_to_client'] == 'Y'
			|| isset( $mdjm_options['bcc_dj_to_client'] ) && $mdjm_options['bcc_dj_to_client'] == 'Y' )	{
			
			if( isset( $mdjm_options['bcc_dj_to_client'] ) && $mdjm_options['bcc_dj_to_client'] == 'Y' )
				$bcc[] = $dj->user_email;

			if( isset( $mdjm_options['bcc_admin_to_client'] ) && $mdjm_options['bcc_admin_to_client'] == 'Y' )
				$bcc[] = $mdjm_options['system_email'];
			
			$email_headers .= 'Bcc: ' . implode( ",", $bcc ) . "\r\n";
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
		
		/* All Shortcode vars need to be set by now */
		include( WPMDJM_PLUGIN_DIR . '/admin/includes/config.inc.php' );
		$template_query = new WP_Query( array( 'post_type' => 'email_template', 'post__in' => array( $template_id ) ) );
		if ( $template_query->have_posts() ) {
			while ( $template_query->have_posts() ) {
				$template_query->the_post();
				/* Check if we are using the post title as the subject */
				if( isset( $mdjm_options['title_as_subject'] ) && $mdjm_options['title_as_subject'] == 'Y' && isset( $type['subject'] ) )	{
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