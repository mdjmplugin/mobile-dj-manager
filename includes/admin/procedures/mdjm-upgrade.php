<?php
/*
 * mdjm-upgrade.php
 * 23/03/2015
 * @since 1.1.3
 * The upgrade procedures for MDJM
 */
class MDJM_Upgrade	{

	public function __construct()	{

		global $mdjm, $upgrade_error;
		
		// Use the WP Error Class
		$upgrade_error = new WP_Error;
		
		MDJM()->debug->log_it( '** THE MDJM UPGRADE PROCEDURE IS STARTING **', true );
		
		// Extend the script time out as we may have a lot of entries
		set_time_limit( 180 );
		
		// Ensure 1.2 procedures have run as these are major
		$this->check_1_2();
		
		// Check for additional updates and execute
		$update = get_option( 'mdjm_update_me' );
		
		if( ! empty( $update ) )	{
			$this->execute_updates();
		}
		
		if( ! wp_next_scheduled( 'mdjm_hourly_schedule' ) )		{
			wp_schedule_event( time(), 'hourly', 'mdjm_hourly_schedule' );	
		}
		
		if( ! wp_next_scheduled( 'mdjm_weekly_scheduled_events' ) )		{
			wp_schedule_event( current_time( 'timestamp' ), 'weekly', 'mdjm_weekly_scheduled_events' );
		}

	} // __construct
	
	/*
	 * Run update procedures
	 *
	 *
	 *
	 */
	function execute_updates()	{
		$version = get_option( MDJM_VERSION_KEY );
		
		if( $version < '1.2.1' )
			$this->update_to_1_2_1();
		
		if( $version < '1.2.2' )
			$this->update_to_1_2_2();
			
		if( $version < '1.2.3' )
			$this->update_to_1_2_3();
			
		if( $version < '1.2.3.1' )
			$this->update_to_1_2_3_1();
			
		if( $version < '1.2.3.2' )
			$this->update_to_1_2_3_2();
			
		if( $version < '1.2.3.3' )
			$this->update_to_1_2_3_3();
			
		if( $version < '1.2.3.4' )
			$this->update_to_1_2_3_4();
			
		if( $version < '1.2.3.5' )
			$this->update_to_1_2_3_5();
			
		if( $version < '1.2.3.6' )
			$this->update_to_1_2_3_6();
			
		if( $version < '1.2.4' )
			$this->update_to_1_2_4();
			
		if( $version < '1.2.4.1' )
			$this->update_to_1_2_4_1();
			
		if( $version < '1.2.5' )
			$this->update_to_1_2_5();
			
		if( $version < '1.2.5.1' )
			$this->update_to_1_2_5_1();
			
		if( $version < '1.2.5.2' )
			$this->update_to_1_2_5_2();
			
		if( $version < '1.2.5.3' )
			$this->update_to_1_2_5_3();
			
		if( $version < '1.2.6' )
			$this->update_to_1_2_6();
			
		if( $version < '1.2.7' )
			$this->update_to_1_2_7();
			
		if( $version < '1.2.7.1' )
			$this->update_to_1_2_7_1();
			
		if( $version < '1.2.7.2' )
			$this->update_to_1_2_7_2();
			
		if( $version < '1.2.7.3' )
			$this->update_to_1_2_7_3();
			
		if( $version < '1.3' )
			$this->update_to_1_3();
			
		if( $version < '1.3.4' )
			$this->update_to_1_3_4();
			
		if( $version < '1.3.8' )
			$this->update_to_1_3_8();
		
	} // execute_updates
	
	/*
	 * Backup the database table data to CSV file
	 *
	 * @param	arr		$tbl		The names of the tables to backup. If not set, all MDJM tables
	 *								Should include wpdb->prefix
	 *			bool	$replace	true replace existing backup files, false (default) does not
	 */
	public function db_backup( $tbl='', $replace='' )	{
		global $wpdb;
		
		if( !empty( $tbl ) && !is_array( $tbl ) )	{
			error_log( '$tbl is not an array ' . $tbl, 3, MDJM_DEBUG_LOG );
			return false;	
		}
		$replace = !empty( $replace ) ? $replace : true;
		
		$backup_dir = MDJM_PLUGIN_DIR . '/db_backups';
		
		/* -- Make sure the backup directory exists, otherwise create it -- */
		if( !file_exists( $backup_dir ) )
			mkdir( $backup_dir, 0777, true );
		
		$mdjm_tables = array(
						MDJM_PLAYLIST_TABLE,
						MDJM_HOLIDAY_TABLE,
						);
		$mdjm_desc = array(
						MDJM_PLAYLIST_TABLE		=> 'Playlist Table',
						MDJM_HOLIDAY_TABLE		=> 'Availability Table',
						);
		
		$tables = !empty( $tbl ) ? $tbl : $mdjm_tables;
		
		$file_content = '/*-------------------------------------------' . "\n" . 
						'MDJM Database Table Backup' . "\n" . 
						'MDJM Version: ' . get_option( MDJM_VERSION_KEY ) . "\n" . 
						'Date: ' . date( 'd M Y H:i:s' ) . "\n" . 
						'Table: {MDJM_TABLE} - {MDJM_DESC}' . "\n" . 
						'Total Rows: {MDJM_ROWS}' . "\n" . 
						"\n" . 
						'Support: http://mdjm.co.uk' . "\n" . 
						'         contact@mydjplanner.co.uk' . "\n" .
						'-------------------------------------------*/' . "\n";
		
		$data_id = array(
						MDJM_PLAYLIST_TABLE		=> 'id',
						MDJM_HOLIDAY_TABLE		=> 'id',
					);
		
		/* -- Loop through the tables creating the backups -- */
		foreach( $tables as $table )	{
			/* -- Error check -- */
			if( !in_array( $table, $mdjm_tables ) )	{
				error_log( $table . ' is not an MDJM table' . "\n", 3, MDJM_DEBUG_LOG );
				continue;
			}
			error_log( 'Backing up ' . $table, 3, MDJM_DEBUG_LOG );
			$backup_file = $backup_dir . '/' . $table . '_pre_1_2.sql';
			/* -- Delete existing backups -- */
			if( file_exists( $backup_file ) && empty( $replace ) )	{
				error_log( 'Backup file exists...skipping' . "\n", 3, MDJM_DEBUG_LOG );
				continue;	
			}
			if( file_exists( $backup_file ) )
				unlink( $backup_file );
			
			$file_content .= 'DROP TABLE IF EXISTS `{MDJM_TABLE}`;' . "\n";
			
			/* -- Create table query -- */
			$create = $wpdb->get_row( 'SHOW CREATE TABLE ' . $table, ARRAY_N);
			
			$file_content .= $create[1] . ';' . "\n";
			
			$results = $wpdb->get_results( "SELECT * FROM `" . $table . '`', ARRAY_N );
			
			$num_rows = $wpdb->num_rows;
			
			if( $num_rows > 0 )	{
				error_log( $num_rows . ' rows of data to export' . "\n", 3, MDJM_DEBUG_LOG );
				$vals = array(); 
				$z = 0;
									
				for( $i = 0; $i < $num_rows; $i++ )	{
					$items = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM `" . $table . "` WHERE `" . $data_id[$table] . "` = %d", $results[$i][0] ), ARRAY_N );
					$vals[$z] = '(';
					
					for( $j=0; $j < count( $items ); $j++ )	{
						if( isset( $items[$j] ) )	{
							$vals[$z] .= "'" . esc_sql( $items[$j] ) . "'";
						}
						else	{
							$vals[$z] .= 'NULL';
						}
						if( $j < ( count( $items ) -1 ) )	{
							$vals[$z] .= ',';
						}
					}
					
					$vals[$z] .= ')';
					$z++;
				}
				$file_content .= 'INSERT INTO `' . $table . '` VALUES ';      
				$file_content .= '  '.implode( ";\nINSERT INTO `" . $table . "` VALUES ", $vals ) . ";\n";
				
				$search = array( '{MDJM_TABLE}', '{MDJM_DESC}', '{MDJM_ROWS}' );
				$replace = array( $table, $mdjm_desc[$table], $num_rows );
				
				/* -- Write the file -- */
				$handle = fopen( $backup_file, 'x' );
				fwrite( $handle, str_replace( $search, $replace, $file_content ) );
				fclose( $handle );
				
				error_log( $table . ' backup complete' . "\n", 3, MDJM_DEBUG_LOG );
			}
		} // End foreach( $tables as $table )
	} // db_backup
	
	/*
	 * Update the database tables as required
	 *
	 *
	 *
	 */
	public function update_db()	{
		global $wpdb, $mdjm;
		
		if ( get_option( 'mdjm_db_version' ) == $mdjm->db_version )	{
			MDJM()->debug->log_it( 'No database update is required' );
			return;
		}
		
		MDJM()->debug->log_it( 'Starting database upgrade procedures', true );														
		
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
							
		/* PLAYLISTS LIBRARY TABLE */
		$music_library_sql = "CREATE TABLE ". MDJM_PLAYLIST_LIBRARY_TABLE . " (
							id int(11) NOT NULL AUTO_INCREMENT,
							library varchar(255) NULL,
							library_slug varchar(255) NOT NULL,
							song varchar(255) NOT NULL,
							artist varchar(255) NOT NULL,
							album varchar(255) NULL,
							genre varchar(255) NULL,
							year varchar(10) NULL,
							comments text NULL,
							rating varchar(10) NULL,
							dj int(11) NOT NULL,
							date_added date NULL,
							PRIMARY KEY  (id),
							KEY library (library),
							KEY song (song),
							KEY artist (artist),
							KEY year (year),
							KEY genre (genre),
							KEY dj (dj)
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
		dbDelta( $music_library_sql );
		dbDelta( $holiday_sql );
	
		update_option( 'mdjm_db_version', $mdjm->db_version );
		MDJM()->debug->log_it( 'Completed database upgrade procedures', true );
	} // update_db
	
	/*
	 * Updates < 1.2
	 * Run the update procedures for versions less than 1.2
	 *
	 *
	 */
	public function update_to_1_2()	{
		global $mdjm;
		
		set_time_limit( 180 );
		
		MDJM()->debug->log_it( 'Starting version 1.2 upgrade procedures', true );
		
		$this->db_backup();
		
		$this->update_db();
		
		$this->set_mdjm_taxonomies(); // Set the taxonomies as they are not loaded yet
		
		add_option( 'mdjm_migrate_event_types', '1' );
		$this->migrate_event_types_1_2(); // Import event types as terms
		
		add_option( 'mdjm_migrate_events', '1' );
		$this->migrate_events_1_2(); // Import events as posts
		
		add_option( 'mdjm_migrate_transaction_types', '1' );
		$this->migrate_transaction_types_1_2(); // Import Transaction types as terms
		
		add_option( 'mdjm_migrate_transactions', '1' );
		$this->migrate_transactions_1_2(); // Import transactions as posts
		
		add_option( 'mdjm_update_client_fields', '1' );
		$this->update_client_fields_1_2(); // Add the option to make fields required
		
		add_option( 'mdjm_migrate_contact_forms', '1' );
		$this->migrate_contact_forms_1_2(); // Import contact forms and fields as posts
		
		add_option( 'mdjm_migrate_cron_tasks', '1' );
		$this->migrate_cron_tasks_1_2(); // Update functions for cron tasks
		
		add_option( 'mdjm_update_scheduler', '1' );
		$this->update_scheduler_1_2(); // Remove previous scheduled tasks
		
		add_option( 'mdjm_update_options', '1' );
		$this->update_options_1_2(); // Update the plugin options
										
		MDJM()->debug->log_it( 'Completed version 1.2 upgrade procedures', true );
		
	} // update_to_1_2
	
	/*
	 * Update the plugin setting options
	 *
	 *
	 *
	 */
	public function update_options_1_2()	{
		global $mdjm;
		
		if( !get_option( 'mdjm_update_options' ) )
			return;
		
		MDJM()->debug->log_it( '*** UPDATING PLUGIN SETTINGS ***', true );
		
		$mdjm_settings = array(
							'main'		=> get_option( 'mdjm_plugin_settings' ),
							'payments'	=> get_option( 'mdjm_payment_settings' ),
							);
		// Main settings updates
		$mdjm_settings['main']['artist'] = 'DJ';
		unset( $mdjm_settings['main']['event_types'] );
		
		// Payment settings
		$mdjm_settings['payments']['pp_manual_cfm_template'] = $mdjm_settings['payments']['pp_cfm_template'];
		$mdjm_settings['payments']['pp_default_method'] = 'Cash';
		unset( $mdjm_settings['payments']['pp_transaction_types'] );
		
		update_option( 'mdjm_plugin_settings', $mdjm_settings['main'] );
		update_option( 'mdjm_payment_settings', $mdjm_settings['payments'] );
		
		/* -- We add this option for the journal migrations -- */
		add_option( 'mdjm_date_to_1_2', strtotime( "+3 day" ) );
		
		delete_option( 'mdjm_update_options' );
		
		MDJM()->debug->log_it( '*** COMPLETED UPDATING PLUGIN SETTINGS ***', true );
		
	} // update_options_1_2
	
	/*
	 * migrate_events
	 * Import all events from custom DB table to Event posts
	 * Adjust playlists, and transactions
	 * 
	 * @upgrade -> 1.1.3
	 */
	public function migrate_events_1_2()	{
		global $mdjm, $mdjm_settings, $wpdb;
		
		if( !get_option( 'mdjm_migrate_events' ) )
			return;
		
		add_filter( 'akismet_debug_log', '__return_false' );
		
						/* -- Old Status => New Status -- */
		$status_map = array(
						'Approved'			=> 'mdjm-approved',
						'Cancelled'			=> 'mdjm-cancelled',
						'Completed'			=> 'mdjm-completed',
						'Enquiry'			=> 'mdjm-enquiry',
						'Failed Enquiry'	=> 'mdjm-lost',
						'Pending'			=> 'mdjm-contract',
						'Unattended'		=> 'mdjm-unattended',
						);
		
		MDJM()->debug->log_it( '*** STARTING EVENT IMPORT ***', true );
		
		$event_list = $wpdb->get_results(
								'SELECT * FROM `' . $wpdb->prefix . 'mdjm_events`'
									);
		
		if( !$event_list )	{
			MDJM()->debug->log_it( 'NO EVENTS FOUND' );
		}
		else	{
			remove_action( 'save_post_mdjm-event', 'mdjm_save_event_post', 10, 3 );
			remove_action( 'wp_insert_comment', array( 'Akismet', 'auto_check_update_meta' ), 10, 2 );
			remove_filter( 'preprocess_comment', array( 'Akismet', 'auto_check_comment' ), 1 );
			MDJM()->debug->log_it( '--' . count( $event_list ) . _n( ' event found', ' events found', count( $event_list ) ) );
			foreach( $event_list as $event )	{					
				$event_type = get_term_by( 'name', $event->event_type, 'event-types' );
				
				/* -- Remap the fields -- */
				$event_data['post_title'] = MDJM_EVENT_PREFIX . $event->event_id;
				$event_data['post_status'] = $status_map[$event->contract_status];
				$event_data['post_date'] = ( strtotime( $event->date_added ) != 0 ? 
					date( 'Y-m-d H:i:s', strtotime( $event->date_added ) ) : date( 'Y-m-d H:i:s' ) );
					
				$event_data['post_author'] = $event->added_by;
				$event_data['post_type'] = MDJM_EVENT_POSTS;
				$event_data['post_category'] = array( $event_type->term_id );
				$event_data['post_modified'] = !empty( $event->last_updated ) ? 
							date( 'Y-m-d H:i:s', strtotime( $event->last_updated ) ) : date( 'Y-m-d H:i:s' );
				
				$event_meta['_mdjm_event_client'] = $event->user_id;
				$event_meta['_mdjm_event_date'] = date( 'Y-m-d', strtotime( $event->event_date ) );
				$event_meta['_mdjm_event_dj'] = $event->event_dj;
				$event_meta['_mdjm_event_start'] = $event->event_start;
				$event_meta['_mdjm_event_finish'] = $event->event_finish;
				$event_meta['_mdjm_event_notes'] = !empty( $event->event_description ) ? 
														$event->event_description : '';
														
				$event_meta['_mdjm_event_guest_pl'] = !empty( $event->event_guest_call ) ? 
														$event->event_guest_call : '';
														
				$event_meta['_mdjm_booking_date'] = !empty( $event->booking_date ) ? 
														date( 'Y-m-d', strtotime( $event->booking_date ) ) : '';
														
				$event_meta['_mdjm_event_contract'] = $event->contract;
				$event_meta['_mdjm_event_contract_approved'] = !empty( $event->contract_approved_date ) ? 
														date( 'Y-m-d', strtotime( $event->contract_approved_date ) ) : '';
														
				$event_meta['_mdjm_event_contract_approver'] = !empty( $event->contract_approver ) ? 
																$event->contract_approver : '';
				
				$event_meta['_mdjm_event_cost'] = mdjm_format_amount( $event->cost );
				$event_meta['_mdjm_event_deposit'] = mdjm_format_amount( $event->deposit );
				$event_meta['_mdjm_event_deposit_status'] = !empty( $event->deposit_status ) ? 
															$event->deposit_status : 'Due';
															
				$event_meta['_mdjm_event_deposit_status'] = !empty( $event->balance_status ) ? 
															$event->balance_status : 'Due';
				if( !empty( $event->venue ) && is_numeric( $event->venue ) )	{
					$event_meta['_mdjm_event_venue_id'] = !empty( $event->venue ) && is_numeric( $event->venue ) ? 
															$event->venue : '';
				}
				else	{
					$event_meta['_mdjm_event_venue_name'] = !empty( $event->venue_name ) ? 
								sanitize_text_field( ucwords( $event->venue_name ) ) : '';
								
					$event_meta['_mdjm_event_venue_contact'] = !empty( $event->venue_contact ) ? 
								sanitize_text_field( ucwords( $event->venue_contact ) ) : '';
								
					$event_meta['_mdjm_event_venue_phone'] = !empty( $event->venue_phone ) ? 
								sanitize_text_field( ucwords( $event->venue_phone ) ) : '';
								
					$event_meta['_mdjm_event_venue_email'] = !empty( $event->venue_email ) ? 
								sanitize_text_field( strtolower( $event->venue_email ) ) : '';
								
					$event_meta['_mdjm_event_venue_address1'] = !empty( $event->venue_addr1 ) ? 
								sanitize_text_field( ucwords( $event->venue_addr1 ) ) : '';
								
					$event_meta['_mdjm_event_venue_address2'] = !empty( $event->venue_addr2 ) ? 
								sanitize_text_field( ucwords( $event->venue_addr2 ) ) : '';
								
					$event_meta['_mdjm_event_venue_town'] = !empty( $event->venue_city ) ? 
								sanitize_text_field( ucwords( $event->venue_city ) ) : '';
								
					$event_meta['_mdjm_event_venue_county'] = !empty( $event->venue_state ) ? 
								sanitize_text_field( ucwords( $event->venue_state ) ) : '';
								
					$event_meta['_mdjm_event_venue_postcode'] = !empty( $event->venue_zip ) ? 
								sanitize_text_field( strtoupper( $event->venue_zip ) ) : '';
				}
				$event_meta['_mdjm_event_enquiry_source'] = !empty( $event->referrer ) ? $event->referrer : '';
				$event_meta['_mdjm_event_converted_by'] = !empty( $event->converted_by ) ? $event->converted_by : '';
				$event_meta['_mdjm_event_date_converted'] = !empty( $event->date_converted ) ? 
							date( 'Y-m-d', strtotime( $event->date_converted ) ) : '';
							
				$event_meta['_mdjm_event_last_updated_by'] = !empty( $event->last_updated_by ) ? 
							$event->last_updated_by : '';
															
				$event_meta['_mdjm_event_package'] = !empty( $event->event_package ) ? $event->event_package : '';
				$event_meta['_mdjm_event_addons'] = !empty( $event->event_addons ) ? $event->event_addons : '';
				$event_meta['_mdjm_event_tasks'] = !empty( $event->cronned ) ? $event->cronned : '';
				$event_meta['_mdjm_event_djsetup'] = strtotime( $event->dj_setup_date ) != 0 ? 
							date( 'Y-m-d', strtotime( $event->dj_setup_date ) ) : '';
				$event_meta['_mdjm_event_djsetup_time'] = strtotime( $event->dj_setup_time ) != 0 ? 
							date( 'H:i:s', strtotime( $event->dj_setup_time ) ) : '';
							
				/* -- Create the event post -- */
				$event_id = wp_insert_post( $event_data );
				
				/* -- If we have errors, make sure they are logged so we can support -- */
				if( is_wp_error( $event_id ) )	{
					MDJM()->debug->log_it( ' ERROR: Event ID: ' . $event_id . ' | ' . $event_id->get_error_message() );
				}
				
				/* -- Import the event -- */
				elseif( !empty( $event_id ) )	{
					set_time_limit( 180 );
					MDJM()->debug->log_it( 'Event ' . $event->event_id . ' successfully imported as ' . $event_id );
					wp_update_post( array( 'ID' => $event_id, 'post_title' => MDJM_EVENT_PREFIX . $event_id ) );
					
					/* -- Set the Event Type -- */
					wp_set_post_terms( $event_id, $event_type->term_id, 'event-types' );
					
					/* -- Add the event meta -- */
					foreach( $event_meta as $event_meta_key => $event_meta_value )	{
						if( $event_meta_key == '_mdjm_event_cost' || $event_meta_key == '_mdjm_event_deposit' )
							$event_meta_value = mdjm_format_amount( (float)$event_meta_value );
						
						if( $event_meta_key == 'venue_postcode' && !empty( $event_meta_value ) )
							$event_meta_value = strtoupper( $event_meta_value );
							
						if( $event_meta_key == 'venue_email' && !empty( $event_meta_value ) )
							$event_meta_value = strtolower( $event_meta_value );
												
						if( $event_meta_key == '_mdjm_event_package' )
							$event_meta_value = sanitize_text_field( strtolower( $event_meta_value ) );	
							
						elseif( $event_meta_key == '_mdjm_event_addons' )
							$event_meta_value = $event_meta_value;
							
						elseif( !strpos( $event_meta_key, 'notes' ) )
							$event_meta_value = sanitize_text_field( ucwords( $event_meta_value ) );
							
						else
							$event_meta_value = sanitize_text_field( ucfirst( $event_meta_value ) );
							
						add_post_meta( $event_id, $event_meta_key, $event_meta_value );	
					}
					
					/* -- Update playlist entries -- */
					MDJM()->debug->log_it( 'Updating Playlist' );
					$playlist_update = $wpdb->update( MDJM_PLAYLIST_TABLE, 
													  array( 'event_id' => $event_id ),
													  array( 'event_id' => $event->event_id ) );
					MDJM()->debug->log_it( $playlist_update . _n( ' entry ', ' entries ', $playlist_update ) . 'updated' );
					
					/* -- Update Transactions -- */
					MDJM()->debug->log_it( 'Updating Transactions' );
					$trans_update = $wpdb->update( $wpdb->prefix . 'mdjm_trans', 
													  array( 'event_id' => $event_id ),
													  array( 'event_id' => $event->event_id ) );
					MDJM()->debug->log_it( $trans_update . _n( ' entry ', ' entries ', $trans_update ) . 'updated' );
											
					/* -- Update Comm Posts -- */
					MDJM()->debug->log_it( 'Updating Communications' );
					$i = 0;
					$comms = get_posts( array(
								'post_type'		 => MDJM_COMM_POSTS,
								'meta_key'	 	  => '_event',
								'meta_value'   		=> $event->event_id,
								'order_by'  		  => 'post_date',
								'order'			 => 'DESC',
								'posts_per_page'	=> '3',
								'post_status'	 => 'any',
								
								) );
					foreach( $comms as $comm )	{
						if( update_post_meta( $comm->ID, '_event', $event_id ) )
							$i++;
					}
					MDJM()->debug->log_it( $i . _n( ' entry ', ' entries ', $i ) . 'updated' );
				}
				else	{
					MDJM()->debug->log_it( 'ERROR: Event ' . $event_id . ' was not imported' );	
				}
			}
		}
		
		add_action( 'save_post_mdjm-event', 'mdjm_save_event_post', 10, 3 );
		add_action( 'wp_insert_comment', array( 'Akismet', 'auto_check_update_meta' ), 10, 2 );
		add_filter( 'preprocess_comment', array( 'Akismet', 'auto_check_comment' ), 1 );
		
		delete_option( 'mdjm_migrate_events' );
		
		MDJM()->debug->log_it( '*** COMPLETED EVENT IMPORT ***', true );
		
	} // migrate_events
	
	/*
	 * set_mdjm_taxonomies
	 * 23/03/2015
	 * @since 1.1.3
	 * Creates the MDJM post taxonomies
	 */
	public function set_mdjm_taxonomies()	{
		global $mdjm;
		
		if( !get_taxonomy( 'event-types' ) )	{
			$tax_labels['mdjm-event'] = array(
							'name'              		   => _x( 'Event Type', 'taxonomy general name' ),
							'singular_name'     		  => _x( 'Event Type', 'taxonomy singular name' ),
							'search_items'      		   => __( 'Search Event Types' ),
							'all_items'         		  => __( 'All Event Types' ),
							'edit_item'        		  => __( 'Edit Event Type' ),
							'update_item'       			=> __( 'Update Event Type' ),
							'add_new_item'      		   => __( 'Add New Event Type' ),
							'new_item_name'     		  => __( 'New Event Type' ),
							'menu_name'         		  => __( 'Event Types' ),
							'separate_items_with_commas' => NULL,
							'choose_from_most_used'	  => __( 'Choose from the most popular Event Types' ),
							'not_found'				  => __( 'No event types found' ),
							);
			$tax_args['mdjm-event'] = array(
							'hierarchical'      	   => true,
							'labels'            	 => $tax_labels['mdjm-event'],
							'show_ui'           		=> true,
							'show_admin_column' 	  => false,
							'query_var'         	  => true,
							'rewrite'           		=> array( 'slug' => 'event-types' ),
							'update_count_callback'      => '_update_generic_term_count',
						);
			register_taxonomy( 'event-types', 'mdjm-event', $tax_args['mdjm-event'] );
		}

		/* -- Transaction Types -- */
		if( !get_taxonomy( 'transaction-types' ) )	{
			$tax_labels['mdjm-transaction'] = array(
							'name'              		   => _x( 'Transaction Type', 'taxonomy general name' ),
							'singular_name'     		  => _x( 'Transaction Type', 'taxonomy singular name' ),
							'search_items'      		   => __( 'Search Transaction Types' ),
							'all_items'         		  => __( 'All Transaction Types' ),
							'edit_item'        		  => __( 'Edit Transaction Type' ),
							'update_item'       			=> __( 'Update Transaction Type' ),
							'add_new_item'      		   => __( 'Add New Transaction Type' ),
							'new_item_name'     		  => __( 'New Transaction Type' ),
							'menu_name'         		  => __( 'Transaction Types' ),
							'separate_items_with_commas' => NULL,
							'choose_from_most_used'	  => __( 'Choose from the most popular Transaction Types' ),
							'not_found'				  => __( 'No transaction types found' ),
							);
			$tax_args[MDJM_TRANS_POSTS] = array(
							'hierarchical'      	   => true,
							'labels'            	 => $tax_labels['mdjm-transaction'],
							'show_ui'           		=> true,
							'show_admin_column' 	  => false,
							'query_var'         	  => true,
							'rewrite'           		=> array( 'slug' => 'transaction-types' ),
							'update_count_callback'      => '_update_generic_term_count',
						);
			register_taxonomy( 'transaction-types', 'mdjm-transaction', $tax_args['mdjm-transaction'] );
		}
	/* -- Venue Details -- */
		if( !get_taxonomy( 'venue-details' ) )	{
			$tax_labels['mdjm-venue'] = array(
							'name'              		   => _x( 'Venue Details', 'taxonomy general name' ),
							'singular_name'     		  => _x( 'Venue Detail', 'taxonomy singular name' ),
							'search_items'      		   => __( 'Search Venue Details' ),
							'all_items'         		  => __( 'All Venue Details' ),
							'edit_item'        		  => __( 'Edit Venue Detail' ),
							'update_item'       			=> __( 'Update Venue Detail' ),
							'add_new_item'      		   => __( 'Add New Venue Detail' ),
							'new_item_name'     		  => __( 'New Venue Detail' ),
							'menu_name'         		  => __( 'Venue Details' ),
							'separate_items_with_commas' => NULL,
							'choose_from_most_used'	  => __( 'Choose from the most popular Venue Details' ),
							'not_found'				  => __( 'No details found' ),
							);
			$tax_args[MDJM_VENUE_POSTS] = array(
							'hierarchical'      => true,
							'labels'            => $tax_labels['mdjm-venue'],
							'show_ui'           => true,
							'show_admin_column' => true,
							'query_var'         => true,
							'rewrite'           => array( 'slug' => 'venue-details' ),
						);
			register_taxonomy( 'venue-details', 'mdjm-venue', $tax_args['mdjm-venue'] );
		}
	} // set_mdjm_taxonomies
			
	/*
	 * migrate_event_types
	 * Import the event types from options to terms for use with Event posts
	 * 
	 * @upgrade -> 1.1.3
	 */
	public function migrate_event_types_1_2()	{
		global $mdjm, $mdjm_settings, $upgrade_error;
		
		if( !get_option( 'mdjm_migrate_event_types' ) )
			return;
		
		/* -- Get the existing event types from our settings -- */
		$event_types = explode( "\r\n", $mdjm_settings['main']['event_types'] ); 
		asort( $event_types );
		
		/* -- Import each type as a term -- */
		foreach( $event_types as $event_type )	{
			wp_insert_term( $event_type, 'event-types' );
			
			if( is_wp_error( $event_type ) )	{
				foreach( $upgrade_error->get_error_messages() as $error )	{
					MDJM()->debug->log_it( 'ERROR: ' . $error );
				}
			}
			else	{
				MDJM()->debug->log_it( 'SUCCESS: ' .  $event_type . ' term created' );
			}
		}
		delete_option( 'mdjm_migrate_event_types' );
	} // migrate_event_types
			
	/*
	 * migrate_transaction_types
	 * Import the transaction types from options to terms
	 * 
	 * @upgrade -> 1.1.3
	 */
	public function migrate_transaction_types_1_2()	{
		global $mdjm, $mdjm_settings, $upgrade_error;
		
		if( !get_option( 'mdjm_migrate_transaction_types' ) )
			return;
		
		/* -- Get the existing event types from our settings -- */
		$trans_types = explode( "\n", $mdjm_settings['payments']['pp_transaction_types'] );
		asort( $trans_types );
		
		/* -- Import each type as a term -- */
		foreach( $trans_types as $trans_type )	{
			wp_insert_term( $trans_type, 'transaction-types' );
			
			if( is_wp_error( $form_error ) )	{
				foreach( $upgrade_error->get_error_messages() as $error )	{
					MDJM()->debug->log_it( 'ERROR: ' . $error );
				}
			}
			else	{
				MDJM()->debug->log_it( 'SUCCESS: ' .  $trans_type . ' term created' );
			}
		}
		wp_insert_term( mdjm_get_deposit_label(), 'transaction-types' );
		wp_insert_term( mdjm_get_balance_label(), 'transaction-types' );
		
		delete_option( 'mdjm_migrate_transaction_types' );
	} // migrate_transaction_types
	
	/*
	 * migrate_transactions
	 * Import the transactions as posts
	 * 
	 * @upgrade -> 1.1.3
	 */
	public function migrate_transactions_1_2()	{
		global $mdjm, $wpdb;
		
		if( !get_option( 'mdjm_migrate_transactions' ) )
			return;
		
		MDJM()->debug->log_it( '*** STARTING TRANSACTION IMPORT ***', true );
					
		$trans_list = $wpdb->get_results(
								'SELECT * FROM `' . $wpdb->prefix . 'mdjm_trans`'
									);
		
		if( !$trans_list )	{
			MDJM()->debug->log_it( 'NO TRANSACTIONS FOUND' );
		}
		else	{
			remove_action( 'save_post_mdjm-transaction', 'mdjm_save_txn_post', 10, 3 );
			MDJM()->debug->log_it( '--' . count( $trans_list ) . _n( ' transaction found', ' transactions found', count( $trans_list ) ) );
			
			foreach( $trans_list as $transaction )	{					
				$trans_type = get_term_by( 'name', $transaction->payment_for, 'transaction-types' );
				
				/* -- Create default post (auto-draft) so we can use the ID etc -- */
				require_once( ABSPATH . 'wp-admin/includes/post.php' );
				$trans_post = get_default_post_to_edit( MDJM_TRANS_POSTS, true );
				
				$trans_id = $trans_post->ID;
				
				/* -- Remap the fields -- */
				$trans_data['ID'] = $trans_id;
				$trans_data['post_title'] = MDJM_EVENT_PREFIX . $trans_id;
				$trans_data['post_status'] = ( $transaction->direction == 'Out' ? 'mdjm-expenditure' : 'mdjm-income' );
				$trans_data['post_date'] = date( 'Y-m-d H:i:s', strtotime( $transaction->payment_date ) );
				$trans_data['edit_date'] = true;
					
				$trans_data['post_author'] = get_post_meta( $transaction->event_id, '_mdjm_event_client', true );
				$trans_data['post_type'] = MDJM_TRANS_POSTS;
				$trans_data['post_category'] = array( $trans_type->term_id );
				$trans_data['post_parent'] = $transaction->event_id;
				$trans_data['post_modified'] = strtotime( $transaction->payment_date );
				
				$trans_meta['_mdjm_txn_status'] = sanitize_text_field( $transaction->payment_status );
				$trans_meta['_mdjm_txn_source'] = sanitize_text_field( $transaction->payment_src );
				$trans_meta['_mdjm_paypal_txn_id'] = sanitize_text_field( $transaction->payment_txn_id );
				$trans_meta['_mdjm_txn_type'] = sanitize_text_field( $transaction->payment_type );
				$trans_meta['_mdjm_txn_paypal_payer_id'] = sanitize_text_field( $transaction->payer_id );
				$trans_meta['_mdjm_payer_firstname'] = !empty( $transaction->payer_firstname ) ? 
														sanitize_text_field( ucfirst( $transaction->payer_firstname ) ) : '';
														
				$trans_meta['_mdjm_payer_lastname'] = !empty( $transaction->payer_lastname ) ? 
														sanitize_text_field( ucfirst( $transaction->payer_lastname ) ) : '';
														
				$trans_meta['_mdjm_payer_email'] = is_email( $transaction->payer_email ) ? 
														strtolower( $transaction->payer_email ) : '';
														
				$trans_meta['_mdjm_payment_to'] = !empty( $transaction->payment_to ) ? sanitize_text_field( $transaction->payment_to ) : '';
				
				$trans_meta['_mdjm_txn_currency'] = !empty( $transaction->payment_currency ) ? 
														sanitize_text_field( $transaction->payment_currency ) : '';
														
				$trans_meta['_mdjm_txn_tax'] = !empty( $transaction->payment_tax ) ? 
														$transaction->payment_currency : '0.00';
														
				$trans_meta['_mdjm_txn_total'] = !empty( $transaction->payment_gross ) ? 
														$transaction->payment_gross : '0.00';
														
				$trans_meta['_mdjm_txn_paypal_ipn'] = !empty( $transaction->full_ipn ) ? 
																$transaction->full_ipn : '';
												
				/* -- Create the transaction post -- */
				wp_update_post( $trans_data );
				
				/* -- If we have errors, make sure they are logged so we can support -- */
				if( is_wp_error( $trans_id ) )	{
					MDJM()->debug->log_it( ' ERROR: Transaction ID: ' . $trans_id . ' | ' . $trans_id->get_error_message() );
				}
				
				/* -- Import the transaction -- */
				elseif( !empty( $trans_id ) )	{
					MDJM()->debug->log_it( 'Transaction ' . $transaction->trans_id . ' successfully imported as ' . $trans_id );						
					/* -- Set the Transaction Type -- */
					wp_set_post_terms( $trans_id, $trans_type->term_id, 'transaction-types' );
					
					/* -- Add the transaction meta -- */
					foreach( $trans_meta as $trans_meta_key => $trans_meta_value )	{
						add_post_meta( $trans_id, $trans_meta_key, $trans_meta_value );	
					}
				}
				else	{
					MDJM()->debug->log_it( 'ERROR: Transaction ' . $trans_id . ' was not imported' );	
				}
			}
		}
		add_action( 'save_post_mdjm-transaction', 'mdjm_save_txn_post', 10, 3 );
		
		delete_option( 'mdjm_migrate_transactions' );
		
		MDJM()->debug->log_it( '*** COMPLETED TRANSACTION IMPORT ***', true );
	} // migrate_transactions
	
	/*
	 * update_client_fields
	 * Add the new required option to Client Fields
	 * 
	 * @upgrade -> 1.1.3
	 */
	public function update_client_fields_1_2()	{
		global $mdjm;
		
		if( !get_option( 'mdjm_update_client_fields' ) )
			return;
		
		MDJM()->debug->log_it( 'Starting client field updates' );
		$client_fields = get_option( 'mdjm_client_fields' );
		
		$required_fields = array( 'address1', 'town', 'county', 'postcode', 'phone1' );
		
		foreach( $required_fields as $field )	{
			$client_fields[$field]['required'] = 'Y';
			$client_fields[$field]['value'] = '';
		}
		/* -- Fix value for remaining fields -- */
		$client_fields['address2']['value'] = '';
		$client_fields['phone2']['value'] = '';
		
		if( update_option( 'mdjm_client_fields', $client_fields ) )	{
			MDJM()->debug->log_it( 'SUCCESS: completed client field updates' );
			return true;
		}
		else	{
			MDJM()->debug->log_it( 'ERROR: client field updates failed' );
			return false;
		}
		
		delete_option( 'mdjm_update_client_fields' );
	} // update_client_fields
	
	/*
	 * migrate_contact_forms
	 * Import contact forms and settings as posts
	 *
	 * 
	 * @upgrade -> 1.1.3
	 */
	public function migrate_contact_forms_1_2()	{
		global $mdjm;
		
		if( !get_option( 'mdjm_migrate_contact_forms' ) )
			return;
		
		MDJM()->debug->log_it( '*** STARTING CONTACT FORM IMPORT ***', true );
		
		/* -- Retrieve the forms -- */
		$forms = get_option( 'mdjm_contact_forms' );
		
		foreach( $forms as $form )	{
			$fields = $form['fields'];
			$post_args = array(
							'post_name'		 => sanitize_text_field( $form['slug'] ), // This is the 'slug'
							'post_title'	    => sanitize_text_field( $form['name'] ), // Form name
							'post_type'		 => MDJM_CONTACT_FORM_POSTS,
							'post_status'	   => 'publish',
							'ping_status'	   => 'closed',
							'comment_status'	=> 'closed',
							);
							
			/* -- Insert the parent post & meta -- */
			$contact_form_id = wp_insert_post( $post_args );
			
			MDJM()->debug->log_it( 'Form ' . $form['name'] . ' created with ID ' . $contact_form_id );
			
			if( !empty( $form['config']['error_text_color'] ) && substr( $form['config']['error_text_color'], 0, 1 ) != '#' )
				$form['config']['error_text_color'] = '#' . $form['config']['error_text_color'];
			
			add_post_meta( $contact_form_id, '_mdjm_contact_form_config', $form['config'], true );
			
			/* -- Now create the child posts (fields) -- */
			$i = 1; // Positional counter
			foreach( $fields as $field )	{
				/* -- Determine field order -- */
				if( $field['type'] == 'captcha' )
					$order = 98;
				elseif( $field['type'] == 'submit' )
					$order = 99;
				else
					$order = $i;
					
				$field_args = array(
								'post_name'		 => sanitize_text_field( $field['slug'] ), // This is the 'slug'
								'post_title'	    => sanitize_text_field( $field['name'] ), // Field name
								'post_type'		 => MDJM_CONTACT_FIELD_POSTS,
								'post_status'	   => 'publish',
								'post_parent'	   => $contact_form_id,
								'menu_order'		=> $order,
								);
								
				/* -- Remap the mappings -- */
				$mappings = array(
								'first_name'           => 'first_name',
								'last_name'            => 'last_name',
								'user_email'           => 'user_email',
								'event_date'           => '_mdjm_event_date',
								'event_type'           => 'mdjm_event_type',
								'event_start'          => '_mdjm_event_start',
								'event_finish'         => '_mdjm_event_finish',
								'event_description'    => '_mdjm_event_notes',
								'venue'                => '_mdjm_event_venue_name',
								'venue_city'           => '_mdjm_event_venue_town',
								'venue_state'          => '_mdjm_event_venue_county'
								);
								
				if( !empty( $field['config']['mapping'] ) && isset( $mappings[$field['config']['mapping']] ) )
					$field['config']['mapping'] = $mappings[$field['config']['mapping']];
								
				/* -- Insert the child (field) post & meta -- */
				$field_id = wp_insert_post( $field_args );
				
				add_post_meta( $field_id, '_mdjm_field_config', $field, true );
							
				$i++;
			}
			
		}
		MDJM()->debug->log_it( '*** COMPLETED CONTACT FORM IMPORT ***', true );
		
		delete_option( 'mdjm_migrate_contact_forms' );
	} // migrate_contact_forms
	
	/*
	 * migrate_cron_tasks
	 * Adjust cron task functions
	 *
	 * 
	 * @upgrade -> 1.1.3
	 */
	public function migrate_cron_tasks_1_2()	{
		global $mdjm;
		
		if( !get_option( 'mdjm_migrate_cron_tasks' ) )
			return;
		
		MDJM()->debug->log_it( '*** STARTING CRON TASK ADJUSTMENTS ***', true );
		
		$mdjm_schedules = get_option( 'mdjm_schedules' );
		
		MDJM()->debug->log_it( 'Updating Completed Events' );
		$mdjm_schedules['complete-events']['function'] = 'complete_event';
		
		MDJM()->debug->log_it( 'Updating Request Deposit' );
		$mdjm_schedules['request-deposit']['function'] = 'request_deposit';
		
		MDJM()->debug->log_it( 'Updating Balance Reminder' );
		$mdjm_schedules['balance-reminder']['function'] = 'balance_reminder';
		
		MDJM()->debug->log_it( 'Updating Fail Enquiry' );
		$mdjm_schedules['fail-enquiry']['function'] = 'fail_enquiry';
		
		MDJM()->debug->log_it( 'Updating Client Feedback' );
		$mdjm_schedules['client-feedback']['function'] = 'request_feedback';
					
		MDJM()->debug->log_it( 'Updating Upload Playlist' );
		$mdjm_schedules['upload-playlists']['function'] = 'submit_playlist';
								
		update_option( 'mdjm_schedules', $mdjm_schedules );
		
		MDJM()->debug->log_it( '*** COMPLETED CRON TASK ADJUSTMENTS ***', true );
		
		delete_option( 'mdjm_migrate_cron_tasks' );
		
	} // migrate_cron_tasks
	
	/*
	 * Update the scheduled tasks
	 *
	 *
	 *
	 *
	 */
	public function update_scheduler_1_2()	{
		
		if( !get_option( 'mdjm_update_scheduler' ) )
			return;
		
		wp_clear_scheduled_hook( 'hook_mdjm_hourly_schedule' );
		
		delete_option( 'mdjm_update_scheduler' );
	} // update_scheduler
			
	/*
	 * Check that 1.2 procedures have run
	 *
	 *
	 *
	 */
	function check_1_2()	{
		/* -- Complete the upgrade procedures for version 1.2 -- */
		$update_status = get_option( 'mdjm_update' );
		
		if( !empty( $update_status ) && $update_status == '1.2' )	{
			$this->update_to_1_2();
			
			delete_option( 'mdjm_update' );
		}
	} // update_to_1_2
	
	/*
	 * Execute upgrade for version 1.2.1
	 *
	 *
	 *
	 */
	function update_to_1_2_1()	{
		
		MDJM()->debug->log_it( 'UPDATING to 1.2.1', true );
		
		include_once( 'update_to_1.2.1.php' );
		
		delete_option( 'mdjm_update_me' );
		
		MDJM()->debug->log_it( 'COMPLETED update to 1.2.1', true );
	} // update_to_1_2_1
	
	/*
	 * Execute upgrade for version 1.2.2
	 *
	 *
	 *
	 */
	function update_to_1_2_2()	{
		
		MDJM()->debug->log_it( 'UPDATING to 1.2.2', true );
		
		include_once( 'update_to_1.2.2.php' );
		
		delete_option( 'mdjm_update_me' );
		
		MDJM()->debug->log_it( 'COMPLETED update to 1.2.2', true );
	} // update_to_1_2_2
	
	/*
	 * Execute upgrade for version 1.2.3
	 *
	 *
	 *
	 */
	function update_to_1_2_3()	{
		
		MDJM()->debug->log_it( 'UPDATING to 1.2.3', true );
		
		include_once( 'update_to_1.2.3.php' );
		
		delete_option( 'mdjm_update_me' );
		
		MDJM()->debug->log_it( 'COMPLETED update to 1.2.3', true );
	} // update_to_1_2_3
	
	/*
	 * Execute upgrade for version 1.2.3.1
	 *
	 *
	 *
	 */
	function update_to_1_2_3_1()	{
		
		MDJM()->debug->log_it( 'UPDATING to 1.2.3.1', true );
		
		include_once( 'update_to_1.2.3.1.php' );
		
		delete_option( 'mdjm_update_me' );
		
		MDJM()->debug->log_it( 'COMPLETED update to 1.2.3.1', true );
	} // update_to_1_2_3_1
	
	/*
	 * Execute upgrade for version 1.2.3.2
	 *
	 *
	 *
	 */
	function update_to_1_2_3_2()	{
		
		MDJM()->debug->log_it( 'UPDATING to 1.2.3.2', true );
		
		include_once( 'update_to_1.2.3.2.php' );
		
		delete_option( 'mdjm_update_me' );
		
		MDJM()->debug->log_it( 'COMPLETED update to 1.2.3.2', true );
	} // update_to_1_2_3_2
	
	/*
	 * Execute upgrade for version 1.2.3.3
	 *
	 *
	 *
	 */
	function update_to_1_2_3_3()	{
		
		MDJM()->debug->log_it( 'UPDATING to 1.2.3.3', true );
		
		include_once( 'update_to_1.2.3.3.php' );
		
		delete_option( 'mdjm_update_me' );
		
		MDJM()->debug->log_it( 'COMPLETED update to 1.2.3.3', true );
	} // update_to_1_2_3_3
	
	/*
	 * Execute upgrade for version 1.2.3.4
	 *
	 *
	 *
	 */
	function update_to_1_2_3_4()	{
		
		MDJM()->debug->log_it( 'UPDATING to 1.2.3.4', true );
		
		include_once( 'update_to_1.2.3.4.php' );
		
		delete_option( 'mdjm_update_me' );
		
		MDJM()->debug->log_it( 'COMPLETED update to 1.2.3.4', true );
	} // update_to_1_2_3_4
	
	/*
	 * Execute upgrade for version 1.2.3.5
	 *
	 *
	 *
	 */
	function update_to_1_2_3_5()	{
		
		MDJM()->debug->log_it( 'UPDATING to 1.2.3.5', true );
		
		include_once( 'update_to_1.2.3.5.php' );
		
		delete_option( 'mdjm_update_me' );
		
		MDJM()->debug->log_it( 'COMPLETED update to 1.2.3.5', true );
	} // update_to_1_2_3_5
	
	/*
	 * Execute upgrade for version 1.2.3.6
	 *
	 *
	 *
	 */
	function update_to_1_2_3_6()	{
		
		MDJM()->debug->log_it( 'UPDATING to 1.2.3.6', true );
		
		include_once( 'update_to_1.2.3.6.php' );
		
		delete_option( 'mdjm_update_me' );
		
		MDJM()->debug->log_it( 'COMPLETED update to 1.2.3.6', true );
	} // update_to_1_2_3_6
	
	/*
	 * Execute upgrade for version 1.2.4
	 *
	 *
	 *
	 */
	function update_to_1_2_4()	{
		
		MDJM()->debug->log_it( 'UPDATING to 1.2.4', true );
		
		include_once( 'update_to_1.2.4.php' );
		
		delete_option( 'mdjm_update_me' );
		
		MDJM()->debug->log_it( 'COMPLETED update to 1.2.4', true );
	} // update_to_1_2_4
	
	/*
	 * Execute upgrade for version 1.2.4.1
	 *
	 *
	 *
	 */
	function update_to_1_2_4_1()	{
		
		MDJM()->debug->log_it( 'UPDATING to 1.2.4.1', true );
		
		include_once( 'update_to_1.2.4.1.php' );
		
		delete_option( 'mdjm_update_me' );
		
		MDJM()->debug->log_it( 'COMPLETED update to 1.2.4.1', true );
	} // update_to_1_2_4_1
	
	/*
	 * Execute upgrade for version 1.2.5
	 *
	 *
	 *
	 */
	function update_to_1_2_5()	{
		
		MDJM()->debug->log_it( 'UPDATING to 1.2.5', true );
		
		include_once( 'update_to_1.2.5.php' );
		
		delete_option( 'mdjm_update_me' );
		
		MDJM()->debug->log_it( 'COMPLETED update to 1.2.5', true );
	} // update_to_1_2_5
	
	/*
	 * Execute upgrade for version 1.2.5.1
	 *
	 *
	 *
	 */
	function update_to_1_2_5_1()	{
		
		MDJM()->debug->log_it( 'UPDATING to 1.2.5.1', true );
		
		include_once( 'update_to_1.2.5.1.php' );
		
		delete_option( 'mdjm_update_me' );
		
		MDJM()->debug->log_it( 'COMPLETED update to 1.2.5.1', true );
	} // update_to_1_2_5_1
	
	/*
	 * Execute upgrade for version 1.2.5.2
	 *
	 *
	 *
	 */
	function update_to_1_2_5_2()	{
		
		MDJM()->debug->log_it( 'UPDATING to 1.2.5.2', true );
		
		include_once( 'update_to_1.2.5.2.php' );
		
		delete_option( 'mdjm_update_me' );
		
		MDJM()->debug->log_it( 'COMPLETED update to 1.2.5.2', true );
	} // update_to_1_2_5_2
	
	/*
	 * Execute upgrade for version 1.2.5.3
	 *
	 *
	 *
	 */
	function update_to_1_2_5_3()	{
		
		MDJM()->debug->log_it( 'UPDATING to 1.2.5.3', true );
		
		include_once( 'update_to_1.2.5.3.php' );
		
		delete_option( 'mdjm_update_me' );
		
		MDJM()->debug->log_it( 'COMPLETED update to 1.2.5.3', true );
	} // update_to_1_2_5_3
	
	/*
	 * Execute upgrade for version 1.2.6
	 *
	 *
	 *
	 */
	function update_to_1_2_6()	{
		
		MDJM()->debug->log_it( 'UPDATING to 1.2.6', true );
		
		include_once( 'update_to_1.2.6.php' );
		
		delete_option( 'mdjm_update_me' );
		
		MDJM()->debug->log_it( 'COMPLETED update to 1.2.6', true );
	} // update_to_1_2_6
	
	/*
	 * Execute upgrade for version 1.2.7
	 *
	 *
	 *
	 */
	function update_to_1_2_7()	{
		
		MDJM()->debug->log_it( 'UPDATING to 1.2.7', true );
		
		include_once( 'update_to_1.2.7.php' );
		
		delete_option( 'mdjm_update_me' );
		
		MDJM()->debug->log_it( 'COMPLETED update to 1.2.7', true );
	} // update_to_1_2_7
	
	/*
	 * Execute upgrade for version 1.2.7.1
	 *
	 *
	 *
	 */
	function update_to_1_2_7_1()	{
		
		MDJM()->debug->log_it( 'UPDATING to 1.2.7.1', true );
		
		include_once( 'update_to_1.2.7.1.php' );
		
		delete_option( 'mdjm_update_me' );
		
		MDJM()->debug->log_it( 'COMPLETED update to 1.2.7.1', true );
	} // update_to_1_2_7_1
	
	/*
	 * Execute upgrade for version 1.2.7.2
	 *
	 *
	 *
	 */
	function update_to_1_2_7_2()	{
		
		MDJM()->debug->log_it( 'UPDATING to 1.2.7.2', true );
		
		include_once( 'update_to_1.2.7.2.php' );
		
		delete_option( 'mdjm_update_me' );
		
		MDJM()->debug->log_it( 'COMPLETED update to 1.2.7.2', true );
	} // update_to_1_2_7_2
	
	/*
	 * Execute upgrade for version 1.2.7.3
	 *
	 *
	 *
	 */
	function update_to_1_2_7_3()	{
		
		MDJM()->debug->log_it( 'UPDATING to 1.2.7.3', true );
		
		include_once( 'update_to_1.2.7.3.php' );
		
		delete_option( 'mdjm_update_me' );
		
		MDJM()->debug->log_it( 'COMPLETED update to 1.2.7.3', true );
	} // update_to_1_2_7_3
	
	/*
	 * Execute upgrade for version 1.3
	 *
	 *
	 *
	 */
	function update_to_1_3()	{
		
		MDJM()->debug->log_it( 'UPDATING to 1.3', true );
		
		include_once( 'update_to_1.3.php' );
		
		delete_option( 'mdjm_update_me' );
		
		MDJM()->debug->log_it( 'COMPLETED update to 1.3', true );
	} // update_to_1_3
	
	/*
	 * Execute upgrade for version 1.3.4
	 *
	 *
	 *
	 */
	function update_to_1_3_4()	{
		
		MDJM()->debug->log_it( 'UPDATING to 1.3.4', true );
		
		include_once( 'update_to_1.3.4.php' );
		
		delete_option( 'mdjm_update_me' );
		
		MDJM()->debug->log_it( 'COMPLETED update to 1.3.4', true );
	} // update_to_1_3
	
	/*
	 * Execute upgrade for version 1.3.8
	 *
	 *
	 *
	 */
	function update_to_1_3_8()	{
		
		MDJM()->debug->log_it( 'UPDATING to 1.3.8', true );
		
		include_once( 'update_to_1.3.8.php' );
		
		delete_option( 'mdjm_update_me' );
		
		MDJM()->debug->log_it( 'COMPLETED update to 1.3.8', true );
	} // update_to_1_8
	
} // class
	
function mdjm_update_checker()	{
	$current_version = get_option( MDJM_VERSION_KEY );
	
	if( $current_version < MDJM_VERSION_NUM )	{
		add_option( 'mdjm_update_me', MDJM_VERSION_NUM );
		
		$mdjm_upgrade = new MDJM_Upgrade();
		
		update_option( MDJM_VERSION_KEY, MDJM_VERSION_NUM );
				
		// Update the updated key so we know to redirect
		update_option( 'mdjm_updated', '1' );
	}
	
} // mdjm_update_checker

mdjm_update_checker();
