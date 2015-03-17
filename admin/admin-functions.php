<?php

/**
 * * * * * * * * * * * * * * * MDJM * * * * * * * * * * * * * * *
 * This file is loaded immediately
 *
 *
 * @since 1.0
 *
 */

/****************************************************************************************************
 *	INSTALLATION & INITIALISATION
 ***************************************************************************************************/

/**
 * f_mdjm_no_admin_for_clients
 * Do not allow the Client role to see the Admin UI or Toolbar
 *
 *
 * Called from: add_action hook
 * @since 1.0
*/
	function f_mdjm_no_admin_for_clients() {
		if( !current_user_can( 'manage_options' ) )	{
			add_filter( 'show_admin_bar', '__return_false' ); // Remove Admin toolbar for non Admins
		}
		
		if( is_admin() && current_user_can( 'client' ) || current_user_can( 'inactive_client' ) && ! ( defined( 'DOING_AJAX' ) && DOING_AJAX ) ) {
			wp_redirect( get_permalink( WPMDJM_CLIENT_HOME_PAGE ) );
			exit;
		}
	}

/**
 * f_mdjm_hide_admin_toolbar
 * Hide the admin toolbar from the frontend for the client role
 *
 *
 * Called from: add_filter hook
 * @since 1.0
*/
	function f_mdjm_hide_admin_toolbar()	{
		if( current_user_can( 'client' ) || current_user_can( 'inactive_client' ))	show_admin_bar( false );
	}
	
/**
 * f_mdjm_remove_menus
 * Hide all menu options for everyone but administrators
 *
 *
 * Called from: add_action hook
 * @since 1.0
*/
	function f_mdjm_remove_menus()	{
		global $mdjm_options;
		if( !current_user_can( 'administrator' ) )	{
			if( !isset( $mdjm_options['dj_see_wp_dash'] ) || $mdjm_options['dj_see_wp_dash'] != 'Y' ) remove_menu_page( 'index.php' );
			remove_menu_page( 'profile.php' );
		}
	}
	
/**
 * f_mdjm_remove_jetpack
 * Hide Jetpack menu options for everyone but administrators
 *
 *
 * Called from: add_action hook
 * @since 1.0
*/
	function f_mdjm_remove_jetpack()	{
		if( !current_user_can( 'administrator' ) )
			remove_menu_page( 'jetpack' );
	}

/**
 * f_mdjm_caps
 * Add the required capability to the administrator
 * role so they see the plugin menu and page items
 *
 * Called from: f_mdjm_install
 * @since 1.0
*/
	function f_mdjm_caps()	{ /* Capabilities */
		$role = get_role( 'administrator' );
		$role->add_cap( 'manage_mdjm' );
	} // f_mdjm_caps

/**
 * f_mdjm_db_install
 * Creates the plugin DB tables
 *
 * Called from: register_activation_hook (mobile-dj-manager.php)
 *
 * @since 1.0
*/
	function f_mdjm_db_install()	{
		global $wpdb, $mdjm_db_version;
		include WPMDJM_PLUGIN_DIR . '/includes/config.inc.php';
		
		$charset_collate = '';

		if ( !empty( $wpdb->charset ) ) {
		  $charset_collate = "DEFAULT CHARACTER SET {$wpdb->charset}";
		}
	
		if ( !empty( $wpdb->collate ) ) {
		  $charset_collate .= " COLLATE {$wpdb->collate}";
		}
		
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
						event_package varchar(100) NOT NULL,
						event_addons varchar(100) NOT NULL,
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
						PRIMARY KEY  (event_id),
						KEY user_id (user_id),
						KEY added_by (added_by),
						KEY date_added (date_added,last_updated),
						KEY converted_by (converted_by),
						KEY referrer (referrer)
						) $charset_collate;";

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
						) $charset_collate;";
				
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
						PRIMARY KEY  (id),
						KEY client (client,event),
						KEY entry_date (timestamp,type(10)),
						KEY author (author)
						) $charset_collate;";
						
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
							PRIMARY KEY  (id),
							KEY event_id (event_id),
							KEY artist (artist),
							KEY song (song)
							) $charset_collate;";
							
		/* AVAILABILITY TABLE */
		$holiday_sql = "CREATE TABLE ". $db_tbl['holiday'] . " (
							id int(11) NOT NULL AUTO_INCREMENT,
							user_id int(11) NOT NULL,
							entry_id varchar(100) NOT NULL,
							date_from date NOT NULL,
							date_to date NOT NULL,
							notes text NULL,
							PRIMARY KEY  (id),
							KEY user_id (user_id)
							) $charset_collate;";
							
		/* TRANS TABLE */
		$trans_sql = "CREATE TABLE ". $db_tbl['trans'] . " (
						trans_id int(11) NOT NULL AUTO_INCREMENT,
						direction varchar(8) NOT NULL,
						event_id int(11) NULL DEFAULT '0',
						payment_src varchar(25) NOT NULL,
						payment_txn_id varchar(19) NULL,
						payment_date datetime NOT NULL,
						payment_type varchar(25) NOT NULL,
						payer_id varchar(25) NULL,
						payment_status varchar(25) NOT NULL,
						payer_firstname varchar(75) NULL,
						payer_lastname varchar(75) NULL,
						payer_email varchar(75) NULL,
						payment_to varchar(75) NULL,
						payment_for varchar(75) NOT NULL,
						payment_currency varchar(3) NOT NULL,
						payment_tax decimal(10,2) NULL,
						payment_gross decimal(10,2) NOT NULL,
						full_ipn text NULL,
						seen_by_admin int(11) NOT NULL DEFAULT '0',
						PRIMARY KEY  (trans_id)
					) $charset_collate;";
				
		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
		dbDelta( $events_sql );
		dbDelta( $venues_sql );
		dbDelta( $journal_sql );
		dbDelta( $playlists_sql );
		dbDelta( $holiday_sql );
		dbDelta( $trans_sql );
		
		add_option( 'mdjm_db_version', $mdjm_db_version );
	} // f_mdjm_db_install

/**
 * Installation procedure creates and populates meta fields
 * Creates the Client and DJ user roles
 *
 * Called from: register_activation_hook (mobile-dj-manager.php)
 * Calls: f_mdjm_caps
 * @since 1.0
*/
	function f_mdjm_install() {

		include WPMDJM_PLUGIN_DIR . '/admin/includes/mdjm-templates.php'; // Must be included after email templates have been imported!
		/* Import the default template contract */
		$contract_post_id = wp_insert_post( $contract_template_args );
		
		/* Import the default email templates */
		$client_enquiry_post_id = wp_insert_post( $email_enquiry_content_args );
		$client_contract_post_id = wp_insert_post( $email_contract_review_args );
		$client_confirm_post_id = wp_insert_post( $email_client_booking_confirm_args );
		$dj_confirm_post_id = wp_insert_post( $email_dj_booking_confirm_args );

		$event_types = 'Adult Birthday Party,Child Birthday Party,Wedding,Corporate Event,Other,';
		$enquiry_sources = 'Website,Google,Facebook,Email,Telephone,Other,';
		$payment_sources = 'BACS\r\nCash\r\nCheque\r\nPayPal\r\nOther,';
		$transaction_types = "Balance\r\nCertifications\r\nDeposit\r\nHardware\r\nInsurance\r\nMaintenance\r\nMusic\r\nParking\r\nPetrol\r\nSoftware\r\nVehicle";
		$playlist_when = 'General,First Dance,Second Dance,Last Song,Father & Bride,Mother & Son,DO NOT PLAY,Other,';
		$items_per_page = get_option( 'posts_per_page' );
		$mdjm_init_options = array(
							'company_name'            		=> get_bloginfo( 'name' ),
							'app_name'                		=> 'Client Zone',
							'items_per_page'          		  => $items_per_page,
							'time_format'            		 => 'H:i',
							'short_date_format'       		   => 'd/m/Y',
							'pass_length'             		 => '8',
							'currency'                		=> 'GBP',
							'show_dashboard'          		  => 'Y',
							'warn_unattended'         		 => 'Y',
							'journaling'              		  => 'Y',
							'multiple_dj'             		 => 'N',
							'packages'                		=> 'N',
							'event_types'             		 => $event_types,
							'enquiry_sources'         		 => $enquiry_sources,
							'default_contract'        		=> $contract_post_id,
							'id_prefix'               		   => 'MDJM',
							'system_email'            		=> get_bloginfo( 'admin_email' ),
							'track_client_emails'			 => 'Y',
							'bcc_dj_to_client'        		=> '',
							'bcc_admin_to_client'     		 => 'Y',
							'booking_conf_to_client' 		  => 'Y',
							'booking_conf_to_dj'     		  => 'Y',
							'contract_to_client'      		  => '',
							'email_enquiry'           		   => $client_enquiry_post_id,
							'enquiry_email_from'      		  => 'admin',
							'email_contract'          		  => $client_contract_post_id,
							'contract_email_from'     		 =>'admin',
							'email_client_confirm'    		=> $client_confirm_post_id,
							'confirm_email_from'      		  => 'admin',
							'email_dj_confirm'        		=> $dj_confirm_post_id,
							'title_as_subject'        		=> 'N',
							'playlist_when'				   => $playlist_when,
							'playlist_close'          		  => '5',
							'upload_playlists'        		=> 'Y',
							'uninst_remove_mdjm_templates'	=> 'N',
							'uninst_remove_db'        		=> 'N',
							'show_credits'            		=> 'Y',
							);
		$mdjm_init_pages = array(
							'app_home_page'                => '',
							'contact_page'                 => '',
							'contracts_page'               => '',
							'playlist_page'                => '',
							'profile_page'                 => '',
							'payments_page'				=> '',
							'availability_check_pass_page' => 'text',
							'availability_check_pass_text' => 'Good news, we are available on the date you entered. Please contact us now',
							'availability_check_fail_page' => 'text',
							'availability_check_fail_text' => 'Unfortunately we do not appear to be available on the date you selected. Why not try another date below...',
							);
		$mdjm_init_permissions = array(
									'dj_see_wp_dash'             => 'Y',
									'dj_add_event'               => 'N',
									'dj_view_enquiry'            => 'N',
									'dj_add_venue'               => 'N',
									'dj_add_client'              => 'N',
									'dj_disable_shortcode'       => array( '{ADMIN_NOTES}', '{DEPOSIT_AMOUNT}' ),
									);
		
		$mdjm_init_client_fields = array(
									'address1' => array(
													'label' => 'Address 1',
													'id' => 'address1',
													'type' => 'text',
													'value' => '',
													'checked' => false,
													'display' => 'Y',
													'desc' => '',
													'default' => true
													),
									'address2' => array(
													'label' => 'Address 2',
													'id' => 'address2',
													'type' => 'text',
													'value' => '',
													'checked' => false,
													'display' => 'Y',
													'desc' => '',
													'default' => true
													),
									'town' => array(
													'label' => 'Town / City',
													'id' => 'town',
													'type' => 'text',
													'value' => '',
													'checked' => false,
													'display' => 'Y',
													'desc' => '',
													'default' => true
													),
									'county' => array(
													'label' => 'County',
													'id' => 'county',
													'type' => 'text',
													'value' => '',
													'checked' => false,
													'display' => 'Y',
													'desc' => '',
													'default' => true
													),
									'postcode' => array(
													'label' => 'Post Code',
													'id' => 'postcode',
													'type' => 'text',
													'value' => '',
													'checked' => false,
													'display' => 'Y',
													'desc' => '',
													'default' => true
													),
									'phone1' => array(
													'label' => 'Primary Phone',
													'id' => 'phone1',
													'type' => 'text',
													'value' => '',
													'checked' => false,
													'display' => 'Y',
													'desc' => '',
													'default' => true
													),
									'phone2' => array(
													'label' => 'Alternative Phone',
													'id' => 'phone2',
													'type' => 'text',
													'value' => '',
													'checked' => false,
													'display' => 'Y',
													'desc' => '',
													'default' => true
													),
									'birthday' => array(
													'label' => 'Birthday',
													'id' => 'birthday',
													'type' => 'dropdown',
													'value' => 'January,February,March,April,May,June,July,August,September,October,November,December',
													'checked' => false,
													'display' => 'Y',
													'desc' => '',
													'default' => true
													),
									'marketing' => array(
													'label' => 'Marketing Info?',
													'id' => 'marketing',
													'type' => 'checkbox',
													'value' => 'Y',
													'checked' => ' checked',
													'display' => 'Y',
													'desc' => 'Do we add the user to the mailing list?',
													'default' => true
													),
									);
		
		$mdjm_pp_options = array(
								'pp_cfm_template'   		=> '',
								'pp_form_layout'		 => 'horizontal',
								'pp_layout'		 	  => 'Pay for:',
								'pp_tax'				 => 'N',
								'pp_tax_type'	   		=> 'percentage',
								'pp_tax_rate'	   		=> '20',
								'pp_payment_sources'	 => $payment_sources,
								'pp_transaction_types'   => $transaction_types,
								'pp_enable'		 	  => 'N',
								'pp_email'		  	   => get_bloginfo( 'admin_email' ),
								'pp_redirect'	   		=> '',
								'pp_button'		 	  => 'btn_paynow_86x21.png',
								'pp_sandbox'			 => 'N',
								'pp_sandbox_email'  	   => get_bloginfo( 'admin_email' ),
								'pp_debug'		  	   => 'N',
								'pp_receiver'	   		=> get_bloginfo( 'admin_email' ),
								'pp_inv_prefix'	 	  => $mdjm_options['id_prefix'] . '-',
								'pp_checkout_style' 	  => '',
								);
		
		/* -- Register the Venue Taxonomy & Terms -- */
		if( !get_taxonomy( 'venue-details' ) )	{
			$tax_labels[MDJM_VENUE_POSTS] = array(
							'name'              		   => _x( 'Venue Details', 'taxonomy general name' ),
							'singular_name'     		  => _x( 'Venue Detail', 'taxonomy singular name' ),
							'search_items'      		   => __( 'Search Venue Details' ),
							'all_items'         		  => __( 'All Venue Details' ),
							'edit_item'        		  => __( 'Edit Venue Detail' ),
							'update_item'       			=> __( 'Update Venue Detail' ),
							'add_new_item'      		   => __( 'Add New Venue Detail' ),
							'new_item_name'     		  => __( 'New Venue Detail' ),
							'menu_name'         		  => __( 'Venue Details' ),
							'separate_items_with_commas' => __( 'Separate venue details with commas' ),
							'choose_from_most_used'	  => __( 'Choose from the most popular Venue Details' ),
							'not_found'				  => __( 'No details found' ),
							);
			$tax_args[MDJM_VENUE_POSTS] = array(
							'hierarchical'      => true,
							'labels'            => $tax_labels[MDJM_VENUE_POSTS],
							'show_ui'           => true,
							'show_admin_column' => true,
							'query_var'         => true,
							'rewrite'           => array( 'slug' => 'venue-details' ),
						);
		
			register_taxonomy( 'venue-details', MDJM_VENUE_POSTS, $tax_args[MDJM_VENUE_POSTS] );
		}
		wp_insert_term( 'Low Ceiling', 'venue-details', array( 'description' => 'Venue has a low ceiling' ) );
		wp_insert_term( 'PAT Required', 'venue-details', array( 'description' => 'Venue requires a copy of the PAT certificate' ) );
		wp_insert_term( 'PLI Required', 'venue-details', array( 'description' => 'Venue requires proof of PLI' ) );
		wp_insert_term( 'Smoke/Fog Allowed', 'venue-details', array( 'description' => 'Venue allows the use of Smoke/Fog/Haze' ) );
		wp_insert_term( 'Sound Limiter', 'venue-details', array( 'description' => 'Venue has a sound limiter' ) );
		wp_insert_term( 'Via Stairs', 'venue-details', array( 'description' => 'Access to this Venue is via stairs' ) );

		/* Version information into the database */
		if( !get_option( 'mdjm_version' ) )	{
			add_option( 'mdjm_version', WPMDJM_VERSION_NUM );
		}
		
		/* Import the option keys */				
		add_option( WPMDJM_SETTINGS_KEY, $mdjm_init_options );
		add_option( WPMDJM_CLIENT_FIELDS, $mdjm_init_client_fields );
		add_option( 'mdjm_plugin_pages', $mdjm_init_pages );
		add_option( 'mdjm_plugin_permissions', $mdjm_init_permissions );
		add_option( WPMDJM_FETEXT_SETTINGS_KEY, $mdjm_init_client_text );
		add_option( 'mdjm_schedules', $mdjm_schedules );
		add_option( 'mdjm_pp_options', $mdjm_pp_options );
		add_option( 'mdjm_debug', '0' );
		add_option( 'mdjm_updated', '0' );
		
		/* Add user roles */
		add_role( 'inactive_client', 'Inactive Client', array( 'read' => true ) );
		add_role( 'client', 'Client', array( 'read' => true ) );
		add_role( 'inactive_dj', 'Inactive DJ', array( 	'read' => true, 
														 'manage_mdjm' => false,
														 'create_users' => false,
														 'edit_users' => false,
														 'delete_users' => false
													) );
		add_role( 'dj', 'DJ', array( 'read' => true, 
									 'manage_mdjm' => true,
									 'create_users' => true,
									 'edit_users' => true,
									 'delete_users' => true
								) );
		
		/* Configure the app */
		require_once( WPMDJM_PLUGIN_DIR . '/admin/includes/functions.php' );
		if( !get_option( 'm_d_j_m_has_initiated' ) )	{
			set_transient( 'mdjm_is_trial', 'XXXX|' . date( 'Y-m-d' ) . '|' . date( 'Y-m-d', strtotime( "+30 days" ) ), 30 * DAY_IN_SECONDS );
			if( get_option( 'has_been_set' ) )
				delete_option( 'has_been_set' );
			add_option( 'm_d_j_m_has_initiated', time() );
		}
		do_reg_check( 'set' );
	} // f_mdjm_install

/**
 * f_mdjm_upgrade
 * Determine if any upgrade procedures are required
 * 
 * Called from: add_action
 * @since 1.0
*/	
	function f_mdjm_upgrade()	{
		if( !get_option( 'mdjm_version' ) )	{ // Add application version to the DB if not already there
			add_option( 'mdjm_version', '0.9.2' ); // Add the previous version to which this upgrade proc was introduced
		}
		if( !get_option( 'mdjm_updated' ) )	{ // Add the option to show we've updated 
			add_option( 'mdjm_updated', '1' );	
		}
		
		$current_version_mdjm = get_option( 'mdjm_version' );
		if( WPMDJM_VERSION_NUM > $current_version_mdjm )	{ // We have some upgrades to perform
/***************************************************
			 	UPGRADES FROM 0.9.2 OR LESS
***************************************************/
			if( $current_version_mdjm <= '0.9.2' )	{
				$mdjm_options = get_option( WPMDJM_SETTINGS_KEY );
				/* USER ROLES */
				/* Create the Inactive Client role */
				add_role( 'inactive_client', 'Inactive Client', array( 'read' => true ) );
				/* Import email templates as posts whilst considering customisations */
				/* Set the args */
				$template_args = array(
								'post_title'	=> '',
								'post_content'  => '',
								'post_status'   => 'publish',
								'post_type'	 => 'email_template',
								'post_author'   => 1,
								);
				/* Retrieve template content */
				$template_enquiry = get_option( 'mdjm_plugin_email_template_enquiry' );
				$template_contract = get_option( 'mdjm_plugin_email_template_contract_review' );
				$template_client_booking = get_option( 'mdjm_plugin_email_template_client_booking_confirm' );
				$template_dj_booking = get_option( 'mdjm_plugin_email_template_dj_booking_confirm' );
				
				/**** IMPORTS ****/
				/* Client Enquiry */
				$template = nl2br( stripslashes( $template_enquiry ) );
				$template_args['post_title'] = 'Client Enquiry';
				$template_args['post_content'] = $template;
				$client_enquiry_post_id = wp_insert_post( $template_args );
				
				/* Client Contract Review */
				$template = nl2br( stripslashes( $template_contract ) );
				$template_args['post_title'] = 'Client Contract Review';
				$template_args['post_content'] = $template;
				$client_contract_post_id = wp_insert_post( $template_args );
				
				/* Client Booking Confirmation */
				$template = nl2br( stripslashes( $template_client_booking ) );
				$template_args['post_title'] = 'Client Booking Confirmation';
				$template_args['post_content'] = $template;
				$client_confirm_post_id = wp_insert_post( $template_args );
				
				/* DJ Booking Confirmation */
				$template = nl2br( stripslashes( $template_dj_booking ) );
				$template_args['post_title'] = 'DJ Booking Confirmation';
				$template_args['post_content'] = $template;
				$dj_confirm_post_id = wp_insert_post( $template_args );
				
				$mdjm_options['email_enquiry'] = $client_enquiry_post_id;
				$mdjm_options['email_contract'] = $client_contract_post_id;
				$mdjm_options['email_client_confirm'] = $client_confirm_post_id;
				$mdjm_options['email_dj_confirm'] = $dj_confirm_post_id;
				
				/* Activate the playlist upload */
				$mdjm_options['upload_playlists'] = 'Y';
				
				/* Update the options */				
				update_option( WPMDJM_SETTINGS_KEY, $mdjm_options );
				
				/**** SCHEDULES ****/
				$mdjm_schedules = get_option( 'mdjm_schedules' );
				if( !$mdjm_schedules ) include WPMDJM_PLUGIN_DIR . '/admin/includes/mdjm-templates.php';
				add_option( 'mdjm_schedules', $mdjm_schedules );
			} // if( $current_version_mdjm <= '0.9.2' )
			
/***************************************************
			 	UPGRADES FROM 0.9.3
***************************************************/
			if( $current_version_mdjm <= '0.9.3' )	{
				$mdjm_options = get_option( WPMDJM_SETTINGS_KEY );
				
				/* Remove the email template option keys that became deprecated in 0.9.3 */
				delete_option( 'mdjm_plugin_email_template_enquiry' );
				delete_option( 'mdjm_plugin_email_template_contract_review' );
				delete_option( 'mdjm_plugin_email_template_client_booking_confirm' );
				delete_option( 'mdjm_plugin_email_template_dj_booking_confirm' );
				
				/* Add system email option */
				$mdjm_options['system_email'] = get_bloginfo( 'admin_email' );
				
				/* Update the options */
				update_option( WPMDJM_SETTINGS_KEY, $mdjm_options );
			} // if( $current_version_mdjm <= '0.9.3' )
			
/***************************************************
			 	UPGRADES FROM 0.9.4
***************************************************/
			if( $current_version_mdjm <= '0.9.4' )	{
				$mdjm_options = get_option( WPMDJM_SETTINGS_KEY );
				$mdjm_schedules = get_option( 'mdjm_schedules' );
				
				/* Add Contract Prefix Option */
				$mdjm_options['id_prefix'] = 'MDJM';
				
				/* Add time format option */
				$mdjm_options['time_format'] = 'H:i';
				
				/* Correct the Schedule Copleted Tasks Subject Line */
				$mdjm_schedules['complete-events']['options']['email_subject'] = 'Task "Complete Events" Complete - ' . $mdjm_options['app_name'];
				$mdjm_schedules['fail-enquiry']['options']['email_subject'] = 'Task "Fail Enquiry" Complete - ' . $mdjm_options['app_name'];
				
				/* Update the options */
				update_option( WPMDJM_SETTINGS_KEY, $mdjm_options );
				update_option( 'mdjm_schedules', $mdjm_schedules );
			} // if( $current_version_mdjm <= '0.9.4' )
			
/***************************************************
			 	UPGRADES FROM 0.9.5
***************************************************/
			if( $current_version_mdjm <= '0.9.5' )	{
				// No procedures
			} // if( $current_version_mdjm == '0.9.5' )
			
/***************************************************
			 	UPGRADES FROM 0.9.6
***************************************************/
			if( $current_version_mdjm <= '0.9.6' )	{
				$mdjm_options = get_option( WPMDJM_SETTINGS_KEY );
				$mdjm_permissions = get_option( 'mdjm_plugin_permissions' );
				
				/* Set the new password length option */
				$mdjm_options['pass_length'] = '8';
				/* Update the Client Text option */
				$mdjm_options['custom_client_text'] = 'N';
				
				/* Create the Client Text Options for front end */
				$mdjm_frontend_text = array(
										'custom_client_text'  => 'N',
										'not_logged_in'       => 'You must be logged in to enter this area of the website. Please enter your username and password below to continue, or use the menu items above to navigate to another area of our website.',
										'home_welcome'        => 'Hello {CLIENT_FIRSTNAME} and welcome to the <a href="{APPLICATION_HOME}">{COMPANY_NAME}</a> {APPLICATION_NAME}.',
										'home_noevents'       => 'You currently have no upcoming events. Please <a title="Contact {COMPANY_NAME}" href="{CONTACT_PAGE}">contact me</a> now to start planning your next disco.',
										'home_notactive'      => 'The selected event is no longer active. <a href="{CONTACT_PAGE}" title="Begin planning your next event with us">Contact us now</a> begin planning your next event.',
										
										);
				
				/* Update Permissions */
				$mdjm_permissions['dj_disable_shortcode'] = array( '{ADMIN_NOTES}', '{DEPOSIT_AMOUNT}' );
				if( isset( $mdjm_permissions['dj_add_event'] ) && $mdjm_permissions['dj_add_event'] == 'Y' )	{
					$mdjm_permissions['dj_view_enquiry'] = 'Y';
				}
				else	{
					$mdjm_permissions['dj_view_enquiry'] = 'N';	
				}
				
				/* Update the options */
				update_option( WPMDJM_SETTINGS_KEY, $mdjm_options );
				update_option( 'mdjm_plugin_permissions', $mdjm_permissions );
				
				/* Add the front end text option */
				add_option( WPMDJM_FETEXT_SETTINGS_KEY, $mdjm_frontend_text );
			} // if( $current_version_mdjm == '0.9.6' )
			
/***************************************************
			 	UPGRADES FROM 0.9.7
***************************************************/
			if( $current_version_mdjm <= '0.9.7' )	{
				// No procedures
			} // if( $current_version_mdjm == '0.9.7' )
			
/***************************************************
			 	UPGRADES FROM 0.9.8
***************************************************/
			if( $current_version_mdjm <= '0.9.8' )	{
				$mdjm_options = get_option( WPMDJM_SETTINGS_KEY );
				$mdjm_pages = get_option( 'mdjm_plugin_pages' );
				$mdjm_frontend_text = get_option( WPMDJM_FETEXT_SETTINGS_KEY );
				
				/* Add currency option */
				$mdjm_options['currency'] = 'GBP';
				$mdjm_options['enquiry_email_from'] = 'admin';
				$mdjm_options['contract_email_from'] = 'admin';
				$mdjm_options['confirm_email_from'] = 'admin';
				$mdjm_options['short_date_format'] = 'd/m/Y';
				
				/* Add Availability Page Options */
				$mdjm_pages['availability_check_pass_page'] = $mdjm_pages['contact_page'];
				$mdjm_pages['availability_check_pass_text'] = 'Good news, we are available on the date you entered. Please contact us now';
				$mdjm_pages['availability_check_fail_page'] = 'text';
				$mdjm_pages['availability_check_fail_text'] = 'Unfortunately we do not appear to be available on the date you selected. Why not try another date below...';
				
				/* Add Frontend Text options */
				$mdjm_frontend_text['custom_client_text']  = 'N';
				$mdjm_frontend_text['not_logged_in'] = 'You must be logged in to enter this area of the website. Please enter your username and password below to continue, or use the menu items above to navigate to another area of our website.';
				$mdjm_frontend_text['home_welcome'] = 'Hello {CLIENT_FIRSTNAME} and welcome to the <a href="{APPLICATION_HOME}">{COMPANY_NAME}</a> {APPLICATION_NAME}.';
				$mdjm_frontend_text['home_noevents'] = 'You currently have no upcoming events. Please <a title="Contact {COMPANY_NAME}" href="{CONTACT_PAGE}">contact me</a> now to start planning your next disco.';
				$mdjm_frontend_text['home_notactive'] = 'The selected event is no longer active. <a href="{CONTACT_PAGE}" title="Begin planning your next event with us">Contact us now</a> begin planning your next event.';
				$mdjm_frontend_text['playlist_welcome'] = 'Welcome to the {COMPANY_NAME} event playlist management system.';
				$mdjm_frontend_text['playlist_intro'] = 'Use this tool to let your DJ know the songs that you would like played (or perhaps not played) during your event on <strong> {EVENT_DATE}</strong>.' . "\r\n\r\n" . 'Invite your friends to add their song choices to this playlist too by sending them your unique event URL - <a href="{GUEST_PLAYLIST_URL}" target="_blank">{GUEST_PLAYLIST_URL}</a>.' . "\r\n\r\n" . 'You can view and remove any songs added by your guests below.';
				$mdjm_frontend_text['playlist_edit'] = 'You are currently editing the playlist for your event on {EVENT_DATE}. To edit the playlist for one of your other events, return to the <a href="{APPLICATION_HOME}">{APPLICATION_NAME} home page</a> and select Edit Playlist from the drop down list displayed next to the event for which you want to edit the playlist.';
				$mdjm_frontend_text['playlist_closed'] = '<strong>Additions to your playlist are disabled as your event is taking place soon</strong>';
				$mdjm_frontend_text['playlist_noevent'] = 'You do not have any confirmed events with us. The Playlist is only available once you have confirmed your event and signed your contract.' . "\r\n\r\n" . 'To begin planning your next event with us, please <a href="{CONTACT_PAGE}">contact us now</a>';
				$mdjm_frontend_text['playlist_guest_welcome'] = 'Welcome to the {COMPANY_NAME} playlist management system.';
				$mdjm_frontend_text['playlist_guest_intro'] = 'You are adding songs to the playlist for {CLIENT_FIRSTNAME} {CLIENT_LASTNAME}\'s event on {EVENT_DATE}.' . "\r\n\r\n" . 'Add your playlist requests in the form below. All fields are required.';
				$mdjm_frontend_text['playlist_guest_closed'] = 'This playlist is currently closed. No songs can be added at this time.';
				
				/* Update Options */
				update_option( WPMDJM_SETTINGS_KEY, $mdjm_options );
				update_option( 'mdjm_plugin_pages', $mdjm_pages );
				update_option( WPMDJM_FETEXT_SETTINGS_KEY, $mdjm_frontend_text );
				
				/* Add debug option */
				add_option( 'mdjm_debug', '0' );
			} // if( $current_version_mdjm <= '0.9.8' )

/***************************************************
			 	UPGRADES FROM 0.9.9
***************************************************/
			if( $current_version_mdjm <= '0.9.9' )	{
				/* Get needed options */
				$mdjm_options = get_option( WPMDJM_SETTINGS_KEY );
				$mdjm_frontend_text = get_option( WPMDJM_FETEXT_SETTINGS_KEY );
				
				/* Set new options */
				$mdjm_options['booking_conf_to_client'] = 'Y';
				$mdjm_options['booking_conf_to_dj'] = 'Y';
				
				/* Set new client dialogue options */
				$mdjm_frontend_text['warn_incomplete_profile'] = 'Y';
				
				/* Update Options */
				update_option( WPMDJM_SETTINGS_KEY, $mdjm_options );
				update_option( WPMDJM_FETEXT_SETTINGS_KEY, $mdjm_frontend_text );
			} // if( $current_version_mdjm <= '0.9.9' )

/***************************************************
			 	UPGRADES FROM 0.9.9.1
***************************************************/
			if( $current_version_mdjm <= '0.9.9.1' )	{
				/* Get needed options */
				$mdjm_options = get_option( WPMDJM_SETTINGS_KEY );
				
				/* Set new options */
				$items = get_option( 'posts_per_page' );
				$mdjm_options['items_per_page'] = $items;
								
				/* Update Options */
				update_option( WPMDJM_SETTINGS_KEY, $mdjm_options );
			} // if( $current_version_mdjm <= '0.9.9.1' )
			
/***************************************************
			 	UPGRADES FROM 0.9.9.2
***************************************************/
/* Same procedures as 0.9.9.1 */
			if( $current_version_mdjm <= '0.9.9.2' )	{
				/* Get needed options */
				$mdjm_options = get_option( WPMDJM_SETTINGS_KEY );
				
				/* Set new options */
				$items = get_option( 'posts_per_page' );
				$mdjm_options['items_per_page'] = $items;
								
				/* Update Options */
				update_option( WPMDJM_SETTINGS_KEY, $mdjm_options );
			} // if( $current_version_mdjm <= '0.9.9.2' )
			
/***************************************************
			 	UPGRADES FROM 0.9.9.3
***************************************************/
			if( $current_version_mdjm <= '0.9.9.3' )	{
				// No procedures
			} // if( $current_version_mdjm == '0.9.9.3' )

/***************************************************
			 	UPGRADES FROM 0.9.9.4
***************************************************/
			if( $current_version_mdjm <= '0.9.9.4' )	{
				$mdjm_options = get_option( WPMDJM_SETTINGS_KEY );
				
				/* Cleanup previous typo */
				if( isset( $mdjm_options['boooking_conf_to_client'] ) )	{
					unset( $mdjm_options['boooking_conf_to_client'] );	
				}
				if( isset( $mdjm_options['boooking_conf_to_dj'] ) )	{
					unset( $mdjm_options['boooking_conf_to_dj'] );	
				}
				
				update_option( WPMDJM_SETTINGS_KEY, $mdjm_options );
			} // if( $current_version_mdjm <= '0.9.9.4' )
			
/***************************************************
			 	UPGRADES FROM 0.9.9.5
***************************************************/
			if( $current_version_mdjm <= '0.9.9.5' )	{
				$mdjm_options = get_option( WPMDJM_SETTINGS_KEY );
				
				/* Cleanup previous typo */
				if( isset( $mdjm_options['boooking_conf_to_client'] ) )	{
					unset( $mdjm_options['boooking_conf_to_client'] );	
				}
				if( isset( $mdjm_options['boooking_conf_to_dj'] ) )	{
					unset( $mdjm_options['boooking_conf_to_dj'] );	
				}
				
				/* Inactive DJ Role */
				add_role( 
						'inactive_dj',
						'Inactive DJ',
						array( 	 
							'read'         => true, 
							'manage_mdjm'  => false,
							'create_users' => false,
							'edit_users'   => false,
							'delete_users' => false
							)
						);
				
				update_option( WPMDJM_SETTINGS_KEY, $mdjm_options );
			} // if( $current_version_mdjm <= '0.9.9.5' )

/***************************************************
			 	UPGRADES FROM 0.9.9.6
***************************************************/			
			if( $current_version_mdjm <= '0.9.9.6' )	{
				$mdjm_options = get_option( WPMDJM_SETTINGS_KEY );
				
				/* Add new options */
				$mdjm_options['warn_unattended'] = 'Y';
				
				/* Update options */
				update_option( WPMDJM_SETTINGS_KEY, $mdjm_options );
			} // if( $current_version_mdjm <= '0.9.9.6' )
			
/***************************************************
			 	UPGRADES FROM 0.9.9.7
***************************************************/			
			if( $current_version_mdjm <= '0.9.9.7' )	{
				/* No Actions */
			} // if( $current_version_mdjm <= '0.9.9.7' )
			
/***************************************************
			 	UPGRADES FROM 0.9.9.8
***************************************************/			
			if( $current_version_mdjm <= '0.9.9.8' )	{
				$mdjm_options = get_option( WPMDJM_SETTINGS_KEY );
				
				/* Add new options */
				$mdjm_options['warn_unattended'] = 'Y';
				
				/* Update options */
				update_option( WPMDJM_SETTINGS_KEY, $mdjm_options );
			} // if( $current_version_mdjm <= '0.9.9.8' )
			
/***************************************************
			 	UPGRADES FROM 1.0
***************************************************/			
			if( $current_version_mdjm <= '1.0' )	{
				$mdjm_options = get_option( WPMDJM_SETTINGS_KEY );
				$mdjm_init_pages = get_option( 'mdjm_plugin_pages' );
				$mdjm_frontend_text = get_option( WPMDJM_FETEXT_SETTINGS_KEY );
								
				/* Set new PayPal options */
				$mdjm_pp_options = array(
								'pp_cfm_template'   => '',
								'pp_form_layout'	=> 'horizontal',
								'pp_label'		  => 'Make a Payment Towards:',
								'pp_tax'			=> 'N',
								'pp_tax_type'	   => 'percentage',
								'pp_tax_rate'	   => '20',
								'pp_enable'		 => 'N',
								'pp_email'		  => $mdjm_options['system_email'],
								'pp_redirect'	   => '',
								'pp_button'		 => 'btn_paynow_SM.gif',
								'pp_sandbox'		=> 'N',
								'pp_sandbox_email'  => $mdjm_options['system_email'],
								'pp_debug'		  => 'Y',
								'pp_receiver'	   => $mdjm_options['system_email'],
								//'pp_inv_prefix'	 => $mdjm_options['id_prefix'] . '-',
								'pp_checkout_style' => '',
								);
				/* Update Pages Options */
				$mdjm_init_pages['payments_page'] = '';
				
				/* Update Client Text Options */
				$mdjm_frontend_text['deposit_label'] = 'Deposit';
				$mdjm_frontend_text['balance_label'] = 'Balance';
				$mdjm_frontend_text['payment_welcome'] = 'Paying for your event is easy as we accept secure online payments via PayPal.' . "\r\n\r\n" . 'PayPal accept all major credit cards and you do not need to be a PayPal member to process your payment to us';
				
				$mdjm_frontend_text['payment_intro'] = 'Select the payment you wish to make from the drop down list below and click the <strong>Pay Now</strong> button to be redirected to <a title="PayPal" href="https://www.paypal.com" target="_blank">PayPal\'s</a> secure website where you can complete your payment.' . "\r\n\r\n" . 'Upon completion, you can return to the {COMPANY_NAME} website. You will also receive an email as soon as your payment completes.';
				
				$mdjm_frontend_text['payment_complete'] = 'Thank you, your payment has completed successfully.' . "\r\n\r\n" . 'You will shortly receive an email from us (remember to check your junk email folder) confirming the payment and detailing next steps for your event.' . "\r\n\r\n" . '<strong>Please note</strong> that it can take a few minutes for our systems to be updated by <a title="PayPal" href="https://www.paypal.com" target="_blank">PayPal</a>, and therefore your payment may not have registered below as yet. Once you receive the payment confirmation email from us, the payment will be updated on our systems.' . "\r\n\r\n" . '<a href="{APPLICATION_HOME}">Click here</a> to return to the <a href="{APPLICATION_HOME}">{APPLICATION_NAME}</a> home page.';
				
				$mdjm_frontend_text['payment_cancel'] = 'Your payment has been cancelled.' . "\r\n\r\n" . 'To process your payment, please follow the steps below.';
				
				$mdjm_frontend_text['payment_not_due'] = 'There are no payments outstanding for this event. If you believe this is an error, please <a href="{CONTACT_PAGE}">contact us</a>.' . "\r\n\r\n" . 'Otherwise, <a href="{APPLICATION_HOME}">Click here</a> return to the <a href="{APPLICATION_HOME}">{APPLICATION_NAME}</a> home page.';
				
				$mdjm_frontend_text['payment_noevent'] = 'No event has been selected for payment. <a href="{APPLICATION_HOME}">Click here</a> return to the <a href="{APPLICATION_HOME}">{APPLICATION_NAME}</a> home page.';
				
				$mdjm_frontend_text['payment_noaccess'] = 'We\'re sorry but you do not have permission to access this page. If you believe this is an error, please <a href="{CONTACT_PAGE}">contact us</a>..' . "\r\n\r\n" . 'Otherwise, <a href="{APPLICATION_HOME}">Click here</a> return to the <a href="{APPLICATION_HOME}">{APPLICATION_NAME}</a> home page.';
				
				/* Add / Update Options */
				add_option( 'mdjm_pp_options', $mdjm_pp_options );
				update_option( WPMDJM_SETTINGS_KEY, $mdjm_options );
				update_option( 'mdjm_plugin_pages', $mdjm_init_pages );
				update_option( WPMDJM_FETEXT_SETTINGS_KEY, $mdjm_frontend_text );
			} // if( $current_version_mdjm <= '1.0' )
			
/***************************************************
			 	UPGRADES FROM 1.1
***************************************************/			
			if( $current_version_mdjm <= '1.1' )	{
				$mdjm_options = get_option( WPMDJM_SETTINGS_KEY );
				$mdjm_pp_options = get_option( 'mdjm_pp_options' );
				
				/* -- Add the new payment options -- */
				$mdjm_pp_options['pp_payment_sources'] = "BACS\r\nCash\r\nCheque\r\nPayPal\r\nOther";
				$mdjm_pp_options['pp_transaction_types'] = "Certifications\r\nHardware\r\nInsurance\r\nMaintenance\r\nMusic\r\nParking\r\nPetrol\r\nSoftware\r\nVehicle";

				/* Add the Uninstall Contract/Email Templates Option */
				$mdjm_options['uninst_remove_mdjm_templates'] = 'N';
				
				/* Add / Update Options */
				update_option( 'mdjm_pp_options', $mdjm_pp_options );
				update_option( WPMDJM_SETTINGS_KEY, $mdjm_options );
			} // if( $current_version_mdjm <= '1.1' )
			
/***************************************************
			 	UPGRADES FROM 1.1.1
***************************************************/			
			if( $current_version_mdjm <= '1.1.1' )	{
				$mdjm_options = get_option( WPMDJM_SETTINGS_KEY );

				/* Add the Email Tracking Option */
				$mdjm_options['track_client_emails'] = 'Y';
				
				/* -- Copy the venues -- */
				$venue_count = wp_count_posts( MDJM_VENUE_POSTS )->publish;
		
				if( !$venue_count || $venue_count == 0 )	{
					$venueinfo = f_mdjm_get_venueinfo();
					
					if( !class_exists( 'MDJM_Events' ) )	{
						require( WPMDJM_PLUGIN_DIR . '/admin/includes/class/class-events.php' );	
					}
					$mdjm_events = new MDJM_Events();
					
					foreach( $venueinfo as $venue )	{
						$venue_data['name'] = !empty( $venue->venue_name ) ? $venue->venue_name : '';
						$venue_meta['venue_contact'] = !empty( $venue->venue_contact ) ? $venue->venue_contact : '';
						$venue_meta['venue_phone'] = !empty( $venue->venue_phone ) ? $venue->venue_phone : '';
						$venue_meta['venue_email'] = !empty( $venue->venue_email ) ? $venue->venue_email : '';
						$venue_meta['venue_address1'] = !empty( $venue->venue_address1 ) ? $venue->venue_address1 : '';
						$venue_meta['venue_address2'] = !empty( $venue->venue_address2 ) ? $venue->venue_address2 : '';
						$venue_meta['venue_town'] = !empty( $venue->venue_town ) ? $venue->venue_town : '';
						$venue_meta['venue_county'] = !empty( $venue->venue_county ) ? $venue->venue_county : '';
						$venue_meta['venue_postcode'] = !empty( $venue->venue_postcode ) ? $venue->venue_postcode : '';
						$venue_meta['venue_information'] = !empty( $venue->venue_information ) ? $venue->venue_information : '';
						
						$mdjm_events->mdjm_add_venue( $venue_data, $venue_meta );
					}
				}
				/* Add / Update Options */
				update_option( WPMDJM_SETTINGS_KEY, $mdjm_options );
			} // if( $current_version_mdjm <= '1.1.1' )
			
/***************************************************
THESE SETTINGS APPLY TO ALL UPDATES - DO NOT ADJUST
***************************************************/
			
			/* Delete the template file */
			unlink( WPMDJM_PLUGIN_DIR . '/admin/includes/mdjm-templates.php' );
			
			/* Update the version number */
			update_option( 'mdjm_version', WPMDJM_VERSION_NUM );
			
			/* Make sure release notes are displayed */
			update_option( 'mdjm_updated', '1' );
			
			/* Re-check Validility */
			do_reg_check( 'set' );
			
			$message = 'Welcome to Mobile DJ Manager for WordPress version ' . WPMDJM_VERSION_NUM . '. Click on one of the Mobile DJ Manager menu items to view the release notes.';
			
			f_mdjm_update_notice( 'updated', $message );
			
		} // if( WPMDJM_VERSION_NUM > $current_version_mdjm )
	} // f_mdjm_upgrade

/**
 * f_mdjm_update_db_check
 * Checks if DB Updates are required
 * 
 * Called from: add_action
 * @since 1.0
*/	
	function f_mdjm_update_db_check() {
		global $mdjm_db_version;
		if ( get_option( 'mdjm_db_version' ) != $mdjm_db_version ) {
			f_mdjm_db_update();
		}
	} // f_mdjm_update_db_check

/**
 * f_mdjm_init
 * Regularly called named constants
 * 
 * Called from: mobile-dj-manager.php
 * @since 1.0
*/
	function f_mdjm_init()	{
		require_once WPMDJM_PLUGIN_DIR . '/includes/functions.php';
		$mdjm_options = f_mdjm_get_options();
		define( 'WPMDJM_CLIENT_FIELDS', 'mdjm_client_fields' );
		define( 'WPMDJM_CREDITS', $mdjm_options['show_credits'] );
		define( 'WPMDJM_CO_NAME', $mdjm_options['company_name'] );
		define( 'WPMDJM_APP_NAME', $mdjm_options['app_name'] );
		define( 'WPDJM_JOURNAL', $mdjm_options['journaling'] );
		define( 'WPMDJM_CLIENT_HOME_PAGE', $mdjm_options['app_home_page'] );
		define( 'WPMDJM_CONTACT_PAGE', $mdjm_options['contact_page'] );
		define( 'WPMDJM_CLIENT_CONTRACT_PAGE', $mdjm_options['contracts_page'] );
		define( 'WPMDJM_CLIENT_PLAYLIST_PAGE', $mdjm_options['playlist_page'] );
		define( 'WPMDJM_CLIENT_PROFILE_PAGE', $mdjm_options['profile_page'] );
		define( 'WPMDJM_CLIENT_PAYMENT_PAGE', $mdjm_options['payments_page'] );
		
		return $mdjm_options;
	} // f_mdjm_init

/**
 * f_mdjm_deactivate
 * Determines actions for when plugin is deactivated
 * 
 * Called from: register_deactivation_hook (mobile-dj-manager.php)
 * @since 1.0 (although unused)
*/
	function f_mdjm_deactivate() {
		/* Remove the scheduled tasks */
		wp_clear_scheduled_hook( time(), 'hourly', 'hook_mdjm_hourly_schedule' );
	} // f_mdjm_deactivate

/**
 * add_action_links
 * Creates additional links next to the plugin detail within
 * the plugins admin UI so admins can go direct
 * 
 * Called from: mobile-dj-manager.php
 * @since 1.0
*/
	function add_action_links( $links ) {
		$mdjm_plugin_links = array(
			'<a href="' . admin_url( 'admin.php?page=mdjm-dashboard' ) . '">Dashboard</a>',
			'<a href="' . admin_url( 'admin.php?page=mdjm-settings' ) . '">Settings</a>',
		);
		return array_merge( $mdjm_plugin_links, $links );
	}  // add_action_links

/**
 * mdjm_plugin_meta
 * Add Support link to the plugin meta row
 * 
 * 
 * Called from: mobile-dj-manager.php
 * @since 1.0
*/
	function mdjm_plugin_meta( $links, $file ) {
		if( strpos( $file, 'mobile-dj-manager.php' ) !== false ) {
			$lic_info = do_reg_check ('check' );
			$mdjm_links = array( '<a href="http://www.mydjplanner.co.uk/support/" target="_blank">Support</a>' );
			if( !$lic_info || $lic_info[0] == 'XXXX' )
				$mdjm_links[] = '<a href="http://www.mydjplanner.co.uk/shop/mobile-dj-manager-for-wordpress-plugin/" target="_blank">Buy Now</a>';
			$new_links = array(
						'<a href="http://www.mydjplanner.co.uk/support/" target="_blank">Support</a>'
					);
			$links = array_merge( $links, $mdjm_links );
		}
		return $links;
	}

/**
 * f_mdjm_last_login
 * Log the users last login timestamp
 * 
 * 
 * Called from: add_action hook
 * @since 1.0
*/	
	function f_mdjm_last_login( $user_login, $user ) {
		update_user_meta( $user->ID, 'last_login', date( 'Y-m-d H:i:s' ) );
	}
	
	function mdjm_mce_shortcode_button() {
		// check user permissions
		if ( !current_user_can( 'edit_posts' ) && !current_user_can( 'edit_pages' ) && !current_user_can( 'dj' ) ) {
			return;
		}
		// check if WYSIWYG is enabled
		if ( 'true' == get_user_option( 'rich_editing' ) ) {
			add_filter( 'mce_external_plugins', 'f_mdjm_add_tinymce_plugin' );
			add_filter( 'mce_buttons', 'f_mdjm_register_mce_shortcode_button' );
		}
	}
	add_action('admin_head', 'mdjm_mce_shortcode_button');
	
	// Declare script for new button
	function f_mdjm_add_tinymce_plugin( $plugin_array ) {
		$plugin_array['my_mce_button'] = WPMDJM_PLUGIN_URL . '/admin/includes/js/mdjm-tinymce-shortcodes.js';
		return $plugin_array;
	}
	
	// Register new button in the editor
	function f_mdjm_register_mce_shortcode_button( $buttons ) {
		array_push( $buttons, 'my_mce_button' );
		return $buttons;
	}

/**
 * f_mdjm_new_contracts_post_type
 * Creates custom post type "contracts"
 * 
 * Called from: mobile-dj-manager.php
 * @since 1.0
*/
	/*function f_mdjm_new_contracts_post_type()	{
		if( post_type_exists( 'contracts' ) )	{
			return; /* Nothing to do here */
		/*}
		require_once WPMDJM_PLUGIN_DIR . '/admin/admin.php';
		$lic_info = do_reg_check( 'check' );
		if( $lic_info )	{
		/* Build out the required arguments and register the post type */
			/*$contract_labels = array(
						'name'               => 'MDJM Contracts',
						'singular_name'      => 'MDJM Contract',
						'menu_name'          => 'Contracts',
						'name_admin_bar'     => 'Contract',
						'add_new'            => 'Add Contract',
						'add_new_item'       => 'Add New Contract',
						'new_item'           => 'New Contract',
						'edit_item'          => 'Edit Contract',
						'view_item'          => 'View Contract',
						'all_items'          => 'All Contracts',
						'search_items'       => 'Search Contracts',
						'not_found'          => 'No contracts found.',
						'not_found_in_trash' => 'No contracts found in Trash.',
					);
			$post_args = array(
						'labels'			 => $contract_labels,
						'description'		=> 'Contracts used by the MDJM plugin',
						'public'			 => true,
						'publicly_queryable' => true,
						'show_ui'			=> true,
						'show_in_menu'	   => 'edit.php?post_type=contract',
						'query_var'		  => true,
						'rewrite'            => array( 'slug' => 'contract' ),
						'capability_type'    => 'post',
						'has_archive'        => true,
						'hierarchical'       => false,
						'menu_position'      => 5,
						'supports'           => array( 'title', 'editor', 'author', 'revisions' ),
						'menu_icon'		  => plugins_url( 'mobile-dj-manager/admin/images/mdjm-icon-20x20.jpg' )
						);
			/* Now register the new post type */
			/*register_post_type( 'contract', $post_args );
		}
	} // f_mdjm_new_contracts_post_type*/
	
/**
 * f_mdjm_email_template_post_type
 * Creates custom post type "email_template"
 * 
 * Called from: mobile-dj-manager.php
 * @since 1.0
*/
	/*function f_mdjm_email_template_post_type()	{
		if( post_type_exists( 'email_template' ) )	{
			return; /* Nothing to do here */
		/*}
		require_once WPMDJM_PLUGIN_DIR . '/admin/admin.php';
		$lic_info = do_reg_check( 'check' );
		if( $lic_info )	{
		/* Build out the required arguments and register the post type */
			/*$template_labels = array(
						'name'               => 'MDJM Email Templates',
						'singular_name'      => 'MDJM Email Template',
						'menu_name'          => 'Email Templates',
						'name_admin_bar'     => 'Email Template',
						'add_new'            => 'Add Template',
						'add_new_item'       => 'Add New Template',
						'new_item'           => 'New Template',
						'edit_item'          => 'Edit Template',
						'view_item'          => 'View Template',
						'all_items'          => 'All Templates',
						'search_items'       => 'Search Templates',
						'not_found'          => 'No templates found.',
						'not_found_in_trash' => 'No templates found in Trash.',
					);
			$post_args = array(
						'labels'			 	 => $template_labels,
						'description'			=> 'Email Templates used by the Mobile DJ Manager for WordPress plugin',
						'public'			 	 => false,
						'exclude_from_search'	=> false,
						'publicly_queryable' 	 => true,
						'show_ui'				=> true,
						'show_in_menu'		   => 'edit.php?post_type=email_template',
						'show_in_admin_bar'	  => true,
						'query_var'		 	  => true,
						'rewrite'            	=> array( 'slug' => 'email-template' ),
						'capability_type'    	=> 'post',
						'has_archive'        	=> true,
						'hierarchical'       	   => false,
						'menu_position'     	  => 5,
						'supports'			   => array( 'title', 'editor', 'author', 'revisions' ),
						'menu_icon'			  => plugins_url( 'mobile-dj-manager/admin/images/mdjm-icon-20x20.jpg' )
						);
			/* Now register the new post type */
			/*register_post_type( 'email_template', $post_args );
		}
	} // f_mdjm_email_template_post_type*/
	
	/**
	 * f_mdjm_communication_post_type
	 * Creates custom post type "mdjm_communication"
	 * 
	 * Called from: mobile-dj-manager.php
	 * @since 1.0
	*/
	/*function f_mdjm_communication_post_type()	{
		if( post_type_exists( 'mdjm_communication' ) )	{
			return; /* Nothing to do here */
		/*}
		require_once WPMDJM_PLUGIN_DIR . '/admin/admin.php';
		$lic_info = do_reg_check( 'check' );
		if( $lic_info )	{
		/* Build out the required arguments and register the post type */
			/*$template_labels = array(
						'name'               => 'Email History',
						'singular_name'      => 'Email History',
						'menu_name'          => 'Email History',
						'name_admin_bar'     => 'Email History',
						'add_new'            => 'Add Communication',
						'add_new_item'       => 'Add New Communication',
						'new_item'           => 'New Communication',
						'edit_item'          => 'Review Email',
						'view_item'          => 'View Email',
						'all_items'          => 'All Emails',
						'search_items'       => 'Search Emails',
						'not_found'          => 'No Emails found.',
						'not_found_in_trash' => 'No Emails found in Trash.',
					);
			$post_args = array(
						'labels'			 	 => $template_labels,
						'description'			=> 'Communication used by the Mobile DJ Manager for WordPress plugin',
						'public'			 	 => false,
						'exclude_from_search'	=> false,
						'publicly_queryable' 	 => true,
						'show_ui'				=> true,
						'show_in_menu'		   => 'edit.php?post_type=mdjm_communication',
						'show_in_admin_bar'	  => false,
						'rewrite' 			  => array( 'slug' => 'mdjm-communications'),
						'query_var'		 	  => true,
						'capability_type'    	=> 'post',
						//'capabilities'		   => array( 'read', 'edit', 'delete' ),
						'has_archive'        	=> true,
						'hierarchical'       	   => false,
						'menu_position'     	  => 5,
						'supports'			   => array( 'title' ),
						'menu_icon'			  => plugins_url( 'mobile-dj-manager/admin/images/mdjm-icon-20x20.jpg' )
						);
			/* Now register the new post type */
			/*register_post_type( 'mdjm_communication', $post_args );
			
			/* And now register the possible status' */
			/*register_post_status( 'ready to send', array(
									'label'                     => _x( 'Ready to Send', 'post' ),
									'public'                    => true,
									'exclude_from_search'       => false,
									'show_in_admin_all_list'    => true,
									'show_in_admin_status_list' => true,
									'label_count'               => _n_noop( 'Ready to Send <span class="count">(%s)</span>', 'Ready to Send <span class="count">(%s)</span>' ),
								) );
			register_post_status( 'sent', array(
									'label'                     => _x( 'Sent', 'post' ),
									'public'                    => true,
									'exclude_from_search'       => false,
									'show_in_admin_all_list'    => true,
									'show_in_admin_status_list' => true,
									'label_count'               => _n_noop( 'Sent <span class="count">(%s)</span>', 'Sent <span class="count">(%s)</span>' ),
								) );
			register_post_status( 'opened', array(
									'label'                     => _x( 'Opened', 'post' ),
									'public'                    => true,
									'exclude_from_search'       => false,
									'show_in_admin_all_list'    => true,
									'show_in_admin_status_list' => true,
									'label_count'               => _n_noop( 'Opened <span class="count">(%s)</span>', 'Opened <span class="count">(%s)</span>' ),
								) );
		}
	} // f_mdjm_communication_post_type*/
	
	/*function set_communication_post_columns( $columns ) {
			return array(
				'cb'			   => '<input type="checkbox" />',
				'date_sent' 		=> __( 'Date Sent' ),
				'title' 	 		=> __( 'Email Subject' ),
				'author'		   => __( 'From' ),
				'recipient' 		=> __( 'Recipient' ),
				'current_status'   => __( 'Status' ),
				'source'		   => __( 'Source' ),
			);
	} // set_communication_post_columns
	
	/*function set_venue_post_columns( $columns ) {
			return array(
				'cb'			   => '<input type="checkbox" />',
				'title' 	 		=> __( 'Venue' ),
				'contact'		  => __( 'Contact' ),
				'phone'		    => __( 'Phone' ),
				'town' 			 => __( 'Town' ),
				'county'   		   => __( 'County' ),
				'info'		     => __( 'Information' ),
			);
	} // set_communication_post_columns */
		
	/*function custom_post_column_action( $column ) {
		global $post, $mdjm_options;
		
		if( $post->post_type == 'mdjm_communication' )	{
			switch ( $column ) {
				case 'date_sent':
					echo date( $mdjm_options['time_format'] . ' ' . $mdjm_options['short_date_format'], get_post_meta( $post->ID, 'date_sent', true ) );
					break;
				case 'recipient':
					$client = get_userdata( get_post_meta( $post->ID, 'recipient', true ) );
					if( $client )	{
						echo $client->display_name;
					}
					else	{
						echo get_post_meta( $post->ID, 'recipient' );	
					}
					break;
				case 'current_status':
					$count = get_post_meta( $post->ID, 'open_count', true );
					$last_change = $post->post_modified;
					
					$change_date = !empty( $last_change ) && $post->post_status == 'opened' ? date( $mdjm_options['time_format'] . ' ' . $mdjm_options['short_date_format'], strtotime( $last_change ) ) : '';
					$open_count = !empty( $count ) && $post->post_status == 'opened' ? ' (' . $count . ')' : '';
					
					echo ucwords( $post->post_status ) . ' ' . $change_date . $open_count;
					break;
				case 'source':
					echo stripslashes( get_post_meta( $post->ID, 'source', true ) );
					break;
			}
		}
		elseif( $post->post_type == 'mdjm-venue' )	{
			switch ( $column ) {
				case 'title':
					echo $post->post_title;
					break;
				case 'contact':
					if( get_post_meta( $post->ID, 'venue_email', true ) )
						echo '<a href="mailto:' . get_post_meta( $post->ID, 'venue_email', true ) . '">';
						
					echo stripslashes( get_post_meta( $post->ID, 'venue_contact', true ) );
					
					if( get_post_meta( $post->ID, 'venue_email', true ) )
						echo '</a>';
					break;
				case 'phone':
					$phone = get_post_meta( $post->ID, 'venue_phone', true );
					echo !empty( $phone ) ? $phone : '';
					break;

				case 'town':
					$town = get_post_meta( $post->ID, 'venue_town', true );
					echo !empty( $town ) ? stripslashes( $town ) : '';
					break;
				case 'county':
					$county = get_post_meta( $post->ID, 'venue_county', true );
					echo !empty( $county ) ? stripslashes( $county ) : '';
					break;
				case 'info':
					$info = get_post_meta( $post->ID, 'venue_info', true );
					echo !empty( $info ) ? stripslashes( $info ) : '';
					echo $info;
					break;
			}
		}
	} // custom_post_column_action */
	
	/*function custom_post_row_actions( $actions, $post ) {
		if( $post->post_type == 'mdjm_communication' )
			return $actions = array();
		
		elseif( $post->post_type == 'mdjm-venue' )	{
			if( isset( $actions['view'] ) )
				unset( $actions['view'] );
			
			if( isset( $actions['inline hide-if-no-js'] ) )
				unset( $actions['inline hide-if-no-js'] );
		}
		
		return $actions; 
	} // custom_post_row_actions */
	
	/*function remove_publish_box()	{
		remove_meta_box( 'submitdiv', 'mdjm_communication', 'side' );
	} // custom_post_row_actions*/
	
	/*function remove_add_new() {
		if( 'mdjm_communication' == get_post_type() )	{
			echo '<style type="text/css">
				#favorite-actions {display:none;}
				.add-new-h2{display:none;}
			</style>';
		}
		if( 'mdjm-venue' == get_post_type() )	{
			?>
			<style>
			#misc-publishing-actions, #minor-publishing-actions {
				display:none;
		  }
            </style>
            <?php
		}
	} // custom_post_row_actions*/
	
    /*function mdjm_communication_custom_bulk_actions( $actions ){
			unset( $actions['edit'] );
			return $actions;
    }*/
	
	/*function rename_publish_button( $translation, $text )	{
		if( 'mdjm-venue' == get_post_type() )	{
			if( $text == 'Publish' )
				return 'Save Venue';	
		}
		return $translation;
	}*/
	
	// Register the Metabox
	/*function f_mdjm_email_meta_box( $post ) {
		//add_meta_box( 'mdjm-email-review', __( 'Email Content', 'textdomain' ), 'f_mdjm_email_review', 'mdjm_communication', 'normal', 'high' );
		//add_meta_box( 'mdjm-email-back', __( 'Details', 'textdomain' ), 'f_mdjm_email_details', 'mdjm_communication', 'side', 'high' );
		
		add_meta_box( 'mdjm-add-venue', __( 'Venue Details', 'textdomain' ), 'f_mdjm_venue_metabox', 'mdjm-venue', 'normal', 'high' );
	}*/
	
	/*function f_mdjm_email_review( $post )	{
		echo $post->post_content;
	}*/
	
	/*function f_mdjm_email_details( $post )	{
		global $mdjm_options;
		$from = get_userdata( $post->post_author );
		$recipient = get_userdata( get_post_meta( $post->ID, 'recipient', true ) );
		?>
        <p><strong>Date</strong>: <?php echo date( $mdjm_options['time_format'] . ' ' . $mdjm_options['short_date_format'], get_post_meta( $post->ID, 'date_sent', true ) ); ?></p>
        <p><strong>From</strong>: <a href="<?php echo admin_url( '/user-edit.php?user_id=' . $from->ID ); ?>"><?php echo $from->display_name; ?></a></p>
        <p><strong>Recipient</strong>: <a href="<?php echo admin_url( '/user-edit.php?user_id=' . $recipient->ID ); ?>"><?php echo $recipient->display_name; ?></a></p>
        <p><strong>Status</strong>: <?php echo ucfirst( $post->post_status ); ?></p>
        <p><strong>Event</strong>: <a href="<?php f_mdjm_admin_page( 'events' ); ?>&action=&action=view_event_form&event_id=<?php echo get_post_meta( $post->ID, 'event', true ); ?>"><?php echo $mdjm_options['id_prefix'] . stripslashes( get_post_meta( $post->ID, 'event', true ) ); ?></a></p>
        <a class="button-secondary" href="<?php echo $_SERVER['HTTP_REFERER']; ?>" title="<?php _e( 'Back to List' ); ?>"><?php _e( 'Back' ); ?></a>
        <?php
	}*/
		
	/*function f_mdjm_venues_meta()	{
		remove_meta_box( 'submitdiv', 'mdjm-venue', 'side' );
		add_meta_box( 'submitdiv', __( 'Save Venue' ), 'post_submit_meta_box', 'mdjm-venue', 'side', 'high' );
	} // custom_post_row_actions*/
	
	/*function f_mdjm_venue_metabox()	{
        ?>
        <p><label for="venue_contact">Venue Contact: </label><input type="text" name="venue_contact" id="venue_contact" class="regular-text" /></p>
        
        
        
        <?php
	}*/

/**
 * f_mdjm_toolbar
 * Creates custom tool bar menu structure
 * 
 * @since 1.0
*/	
	function f_mdjm_toolbar( $admin_bar )	{
		global $mdjm_options;
		
		$mdjm_pp_options = get_option( 'mdjm_pp_options' );
		
		if( current_user_can( 'manage_mdjm' ) || current_user_can( 'administrator' ) )	{
			$admin_bar->add_menu( array(
				'id'    => 'mdjm',
				'title' => 'Mobile DJ Manager',
				'href'  => admin_url( 'admin.php?page=mdjm-dashboard' ),
				'meta'  => array(
					'title' => __( 'Mobile DJ Manager' ),            
				),
			));
			$admin_bar->add_menu( array(
				'id'    => 'mdjm-dashboard',
				'parent' => 'mdjm',
				'title' => 'Dashboard',
				'href'  => admin_url( 'admin.php?page=mdjm-dashboard' ),
				'meta'  => array(
					'title' => __( 'MDJM Dashboard' ),
				),
			));
			if( current_user_can( 'manage_options' ) )	{
				$admin_bar->add_menu( array(
					'id'    => 'mdjm-settings',
					'parent' => 'mdjm',
					'title' => 'Settings',
					'href'  => admin_url( 'admin.php?page=mdjm-settings' ),
					'meta'  => array(
						'title' => __( 'MDJM Settings' ),
					),
				));
				$admin_bar->add_menu( array(
					'id'    => 'mdjm-settings-general',
					'parent' => 'mdjm-settings',
					'title' => 'General',
					'href'  => admin_url( 'admin.php?page=mdjm-settings&tab=general' ),
					'meta'  => array(
						'title' => __( 'MDJM General Settings' ),
					),
				));
				$admin_bar->add_menu( array(
					'id'    => 'mdjm-settings-pages',
					'parent' => 'mdjm-settings',
					'title' => 'Pages',
					'href'  => admin_url( 'admin.php?page=mdjm-settings&tab=pages' ),
					'meta'  => array(
						'title' => __( 'MDJM Pages Settings' ),
					),
				));
				$admin_bar->add_menu( array(
					'id'    => 'mdjm-settings-permissions',
					'parent' => 'mdjm-settings',
					'title' => 'Permissions',
					'href'  => admin_url( 'admin.php?page=mdjm-settings&tab=permissions' ),
					'meta'  => array(
						'title' => __( 'MDJM Permission Settings' ),
					),
				));
				$admin_bar->add_menu( array(
					'id'    => 'mdjm-settings-client-text',
					'parent' => 'mdjm-settings',
					'title' => 'Client Dialogue',
					'href'  => admin_url( 'admin.php?page=mdjm-settings&tab=client_text' ),
					'meta'  => array(
						'title' => __( 'MDJM Client Text Settings' ),
					),
				));
				$admin_bar->add_menu( array(
					'id'    => 'mdjm-settings-client-fields',
					'parent' => 'mdjm-settings',
					'title' => 'Client Fields',
					'href'  => admin_url( 'admin.php?page=mdjm-settings&tab=client_fields' ),
					'meta'  => array(
						'title' => __( 'MDJM Client Field Settings' ),
					),
				));
				if( isset( $mdjm_pp_options['pp_enable'] ) && $mdjm_pp_options['pp_enable'] == 'Y' )	{
					$admin_bar->add_menu( array(
						'id'    => 'mdjm-settings-payments',
						'parent' => 'mdjm-settings',
						'title' => 'Payments',
						'href'  => admin_url( 'admin.php?page=mdjm-settings&tab=payments' ),
						'meta'  => array(
							'title' => __( 'MDJM Online Payment Settings' ),
						),
					));
				}
				$admin_bar->add_menu( array(
					'id'    => 'mdjm-settings-debugging',
					'parent' => 'mdjm-settings',
					'title' => '<font style="color:#F90">Debugging</font>',
					'href'  => admin_url( 'admin.php?page=mdjm-settings&tab=debugging' ),
					'meta'  => array(
						'title' => __( 'MDJM Debug Settings' ),
					),
				));
				$admin_bar->add_menu( array(
					'id'    => 'mdjm-tasks',
					'parent' => 'mdjm',
					'title' => 'Automated Tasks',
					'href'  => admin_url( 'admin.php?page=mdjm-tasks' ),
					'meta'  => array(
						'title' => __( 'Automated Tasks' ),
					),
				));
			}
			$admin_bar->add_menu( array(
					'id'    => 'mdjm-availability',
					'parent' => 'mdjm',
					'title' => 'Availability',
					'href'  => admin_url( 'admin.php?page=mdjm-availability' ),
					'meta'  => array(
						'title' => __( 'DJ Availability' ),
					),
				));
			$admin_bar->add_menu( array(
				'id'    => 'mdjm-clients',
				'parent' => 'mdjm',
				'title' => 'Clients',
				'href'  => admin_url( 'admin.php?page=mdjm-clients' ),
				'meta'  => array(
					'title' => __( 'Client List' ),
				),
			));
			if( current_user_can( 'manage_options' ) || dj_can( 'add_client' ) )	{
				$admin_bar->add_menu( array(
					'id'    => 'mdjm-add-client',
					'parent' => 'mdjm-clients',
					'title' => 'Add Client',
					'href'  => admin_url( 'user-new.php' ),
					'meta'  => array(
						'title' => __( 'Add New Client' ),
					),
				));
			}
			$admin_bar->add_menu( array(
				'id'    => 'mdjm-comms',
				'parent' => 'mdjm',
				'title' => 'Communications',
				'href'  => admin_url( 'admin.php?page=mdjm-comms' ),
				'meta'  => array(
					'title' => __( 'Communications' ),
				),
			));
			if( current_user_can( 'manage_options' ) )	{
				$admin_bar->add_menu( array(
					'id'    => 'edit.php?post_type=' . MDJM_COMM_POSTS,
					'parent' => 'mdjm-comms',
					'title' => 'Email History',
					'href'  => admin_url( 'edit.php?post_type=' . MDJM_COMM_POSTS ),
					'meta'  => array(
						'title' => __( 'Email History' ),
					),
				));
			}
			if( current_user_can( 'manage_options' ) )	{
				$admin_bar->add_menu( array(
					'id'    => 'mdjm-contact-forms',
					'parent' => 'mdjm',
					'title' => 'Contact Forms',
					'href'  => admin_url( 'admin.php?page=mdjm-contact-forms' ),
					'meta'  => array(
						'title' => __( 'Contact Forms' ),
					),
				));
				$admin_bar->add_menu( array(
					'id'    => 'mdjm-new-contact-form',
					'parent' => 'mdjm-contact-forms',
					'title' => 'Add Contact Form',
					'href'  => admin_url( 'admin.php?page=mdjm-contact-forms&action=show_add_contact_form' ),
					'meta'  => array(
						'title' => __( 'New Contact Form' ),
					),
				));
			}
 			if( current_user_can( 'manage_options' ) )	{
				$admin_bar->add_menu( array(
					'id'    => 'mdjm-contracts',
					'parent' => 'mdjm',
					'title' => 'Contract Templates',
					'href'  => admin_url( 'edit.php?post_type=' . MDJM_CONTRACT_POSTS ),
					'meta'  => array(
						'title' => __( 'Contract Templates' ),
					),
				));
				$admin_bar->add_menu( array(
					'id'    => 'mdjm-new-contract',
					'parent' => 'mdjm-contracts',
					'title' => 'Add Contract Template',
					'href'  => admin_url( 'post-new.php?post_type=' . MDJM_CONTRACT_POSTS ),
					'meta'  => array(
						'title' => __( 'New Contract Template' ),
					),
				));
			}
			if( current_user_can( 'manage_options' ) && isset( $mdjm_options['multiple_dj'] ) && $mdjm_options['multiple_dj'] == 'Y' )	{
				$admin_bar->add_menu( array(
					'id'    => 'mdjm-djs',
					'parent' => 'mdjm',
					'title' => 'DJ List',
					'href'  => admin_url( 'admin.php?page=mdjm-djs' ),
					'meta'  => array(
					'title' => __( 'List of DJ\'s' ),
					),
				));
			}
			if( current_user_can( 'manage_options' ) )	{
				$admin_bar->add_menu( array(
					'id'    => 'mdjm-email-templates',
					'parent' => 'mdjm',
					'title' => 'Email Templates',
					'href'  => admin_url( 'edit.php?post_type=' . MDJM_EMAIL_POSTS ),
					'meta'  => array(
						'title' => __( 'Email Templates' ),
					),
				));
				$admin_bar->add_menu( array(
					'id'    => 'mdjm-new-email-template',
					'parent' => 'mdjm-email-templates',
					'title' => 'Add Template',
					'href'  => admin_url( 'post-new.php?post_type=' . MDJM_EMAIL_POSTS ),
					'meta'  => array(
						'title' => __( 'New Email Template' ),
					),
				));
			}
			if( current_user_can( 'manage_options' ) && isset( $mdjm_options['enable_packages'] ) && $mdjm_options['enable_packages'] == 'Y' )	{
				$admin_bar->add_menu( array(
					'id'    => 'mdjm-equipment',
					'parent' => 'mdjm',
					'title' => 'Equipment &amp; Packages',
					'href'  => admin_url( 'admin.php?page=mdjm-packages' ),
					'meta'  => array(
						'title' => __( 'Equipment Inventory' ),
					),
				));
			}
			$admin_bar->add_menu( array(
				'id'    => 'mdjm-events',
				'parent' => 'mdjm',
				'title' => 'Events',
				'href'  => admin_url( 'admin.php?page=mdjm-events' ),
				'meta'  => array(
					'title' => __( 'MDJM Events' ),
				),
			));
			if( current_user_can( 'manage_options' ) || dj_can( 'add_event' ) )	{
				$admin_bar->add_menu( array(
					'id'    => 'mdjm-add-events',
					'parent' => 'mdjm-events',
					'title' => 'Create Event',
					'href'  => admin_url( 'admin.php?page=mdjm-events&action=add_event_form' ),
					'meta'  => array(
						'title' => __( 'Create New Event' ),
					),
				));
			}
			if( current_user_can( 'manage_options' ) || dj_can( 'view_enquiry' ) )	{
				$event_status = array( 'Unattended' => 'Unattended Enquiries', 'Enquiry' => 'View Enquiries' );
				foreach( $event_status as $current_status => $display )	{
					$count_event = f_mdjm_get_eventinfo_by_status( $current_status );
					if( count( $count_event ) > 0 )	{
						$admin_bar->add_menu( array(
							'id'    => 'mdjm-' . str_replace( ' ', '-', strtolower( $display ) ),
							'parent' => 'mdjm-events',
							'title' => $display . ' (' . count( $count_event ) . ')',
							'href'  => admin_url( 'admin.php?page=mdjm-events&status=' . $current_status ),
							'meta'  => array(
								'title' => __( $display ),
							),
						));
					}
				}
			}
			if( current_user_can( 'manage_options' ) || dj_can( 'add_venue' ) )	{
				$admin_bar->add_menu( array(
					'id'    => 'mdjm-venues',
					'parent' => 'mdjm',
					'title' => 'Venues',
					'href'  => admin_url( 'edit.php?post_type=' . MDJM_VENUE_POSTS ),
					'meta'  => array(
						'title' => __( 'Venues' ),
					),
				));
				$admin_bar->add_menu( array(
					'id'    => 'mdjm-add-venue',
					'parent' => 'mdjm-venues',
					'title' => 'Add Venue',
					'href'  => admin_url( 'post-new.php?post_type=' . MDJM_VENUE_POSTS ),
					'meta'  => array(
						'title' => __( 'Add New Venue' ),
					),
				));
				$admin_bar->add_menu( array(
					'id'    => 'mdjm-venue-details',
					'parent' => 'mdjm-venues',
					'title' => 'Venue Details',
					'href'  => admin_url( 'edit-tags.php?taxonomy=venue-details&post_type=' . MDJM_VENUE_POSTS ),
					'meta'  => array(
						'title' => __( 'View / Edit Venue Details' ),
					),
				));
			}
			if( current_user_can( 'manage_options' ) && isset( $mdjm_pp_options['pp_enable'] ) && $mdjm_pp_options['pp_enable'] == 'Y' )	{
				$admin_bar->add_menu( array(
				'id'    => 'mdjm-transactions',
				'parent' => 'mdjm',
				'title' => 'Transactions',
				'href'  => admin_url( 'admin.php?page=mdjm-transactions' ),
				'meta'  => array(
					'title' => __( 'MDJM Transactions' ),
				),
			));
			}
			$admin_bar->add_menu( array(
				'id'    => 'mdjm-user-guides',
				'parent' => 'mdjm',
				'title' => '<font style="color:#F90">User Guides</font>',
				'href'  => 'http://www.mydjplanner.co.uk/support/user-guides/',
				'meta'  => array(
					'title' => __( 'MDJM User Guides' ),
					'target' => '_blank'
				),
			));
			$admin_bar->add_menu( array(
				'id'    => 'mdjm-support',
				'parent' => 'mdjm',
				'title' => '<font style="color:#F90">Support</font>',
				'href'  => 'http://www.mydjplanner.co.uk/support/',
				'meta'  => array(
					'title' => __( 'MDJM Support Forums' ),
					'target' => '_blank'
				),
			));
			if( !do_reg_check( 'check' ) && current_user_can( 'manage_options' ) )	{
				$admin_bar->add_menu( array(
				'id'    => 'mdjm-purchase',
				'parent' => 'mdjm',
				'title' => '<font style="color:#F90">Buy License</font>',
				'href'  => 'http://www.mydjplanner.co.uk/shop/mobile-dj-manager-for-wordpress-plugin/',
				'meta'  => array(
					'title' => __( 'Buy Mobile Dj Manager License' ),
					'target' => '_blank'
				),
			));	
			}
		}
	} // f_mdjm_toolbar

/**
 * f_mdjm_toolbar_new_content
 * Adss new content links to the admin bar
 * 
 * @since 1.0
*/	
	function f_mdjm_toolbar_new_content( $admin_bar )	{
		if( current_user_can( 'manage_options' ) || dj_can( 'add_event' ) )	{
			$admin_bar->add_menu( array(
						'id'    => 'mdjm-add-events-new',
						'parent' => 'new-content',
						'title' => 'Event',
						'href'  => admin_url( 'admin.php?page=mdjm-events&action=add_event_form' ),
						'meta'  => array(
							'title' => __( 'Create New Event' ),
						),
					));
		}
	}

/**
 * f_mdjm_admin_footer
 * Adds footer text to MDJM Admin pages
 * 
 * @since 1.0
*/	
	function f_mdjm_admin_footer() {
		$str = $_SERVER['QUERY_STRING'];
		$search = 'page=mdjm';
		$pos = strpos( $str, $search );
		if( $pos !== false )
			?>
			<p align="center" class="description">Powered by <a style="color:#F90" href="<?php f_mdjm_admin_page( 'mydjplanner' ); ?>" target="_blank"><?php echo WPMDJM_NAME; ?></a>, version <?php echo WPMDJM_VERSION_NUM; ?></p>
            <p align="center">If you like our plugin, please show your support by leaving us a <a style="color:#F90" href="https://wordpress.org/support/view/plugin-reviews/mobile-dj-manager?filter=5#postform" target="_blank">&#9733;&#9733;&#9733;&#9733;&#9733;</a> review at <a href="https://wordpress.org/support/view/plugin-reviews/mobile-dj-manager?filter=5#postform" target="_blank">WordPress.org</a>. Thank you from the <a style="color:#F90" href="<?php f_mdjm_admin_page( 'mydjplanner' ); ?>" target="_blank">My DJ Planner</a></span> Team.</p>
            <?php
	} // f_mdjm_admin_footer
	
/*
* f_mdjm_has_updated
* 23/11/2014
* @since 0.9.3
* Checks for upgrade and displays the upgrade notice
*/
	function f_mdjm_has_updated()	{
		$updated = get_option( 'mdjm_updated' );
		if( $updated && $updated == '1' )	{
			$_GET['updated'] = '1';
			include( WPMDJM_PLUGIN_DIR .  '/admin/pages/updated.php' );
			exit;
		}
	} // f_mdjm_has_updated

/*
* f_mdjm_scheduler_activate
* 14/11/2014
* @since 0.9.3
* Configure the WP cron tasks for MDJM
*/
	function f_mdjm_scheduler_activate()	{
		if ( !wp_next_scheduled( 'hook_mdjm_hourly_schedule' ) )	{
			wp_schedule_event( time(), 'hourly', 'hook_mdjm_hourly_schedule' );
		}
	} // f_mdjm_scheduler_activate
	
/*
* f_mdjm_cron
* 14/11/2014
* @since 0.9.3
* Runs the MDJM scheduled tasks
*/
	function f_mdjm_cron()	{
		global $mdjm_options;
		/* Access the cron functions */
		require_once( WPMDJM_PLUGIN_DIR . '/admin/includes/mdjm-cron.php' );
		
		/* Get the scheduled tasks */
		$mdjm_schedules = get_option( 'mdjm_schedules' );
		if( isset( $mdjm_options['upload_playlists'] ) )	{
			$mdjm_schedules['upload-playlists']['active'] = $mdjm_options['upload_playlists'];
		}
		
		foreach( $mdjm_schedules as $task )	{
			/* Only execute active tasks */
			if( $task['active'] == 'Y' )	{
				/* Check frequency and whether to run */
				if( !isset( $task['nextrun'] ) || $task['nextrun'] <= time() || $task['nextrun'] == 'Today'
					|| $task['nextrun'] == 'Next Week' || $task['nextrun'] == 'Next Month'
					|| $task['nextrun'] == 'Next Year' )	{
					$func = $task['function'];
					if( function_exists( $func ) )
						$func();
				}
			} // if( $task['active'] == 'Y' )
		} // foreach( $mdjm_schedules as $task )
	} // f_mdjm_cron

/*
* f_mdjm_upload_playlist_schedule
* 23/11/2014
* @since 0.9.3
* Ensures the upload playlist schedule is set correctly
*/
	function f_mdjm_upload_playlist_schedule()	{
		global $mdjm_options;
		$mdjm_schedules = get_option( 'mdjm_schedules' );
		
		if( !isset( $mdjm_schedules['upload-playlists']['active'] ) || $mdjm_schedules['upload-playlists']['active'] != $mdjm_options['upload_playlists'] )	{
			$mdjm_schedules['upload-playlists']['active'] = $mdjm_options['upload_playlists'];
			if( $mdjm_schedules['upload-playlists']['active'] == 'Y' )	{
				$mdjm_schedules['upload-playlists']['nextrun'] = time();
			}
			else	{
				$mdjm_schedules['upload-playlists']['nextrun'] = 'N/A';
			}
			update_option( 'mdjm_schedules', $mdjm_schedules );
		}
	} // f_mdjm_upload_playlist_schedule
	
/*
* f_mdjm_set_role
* 05/12/2014
* @since 0.9.7
* Set the correct permission levels for DJ's
*/
	function f_mdjm_set_role()	{
		$perms = get_option( 'mdjm_plugin_permissions' );
		$dj_role = get_role( 'dj' );
		$caps = array(
					'create_users',
					'edit_users',
					'delete_users',
					);
		if( current_user_can( 'dj' ) && current_user_can( 'create_users' ) )	{
			if( !isset( $perms['dj_add_client'] ) || $perms['dj_add_client'] != 'Y' )	{
				foreach( $caps as $cap )	{
					$dj_role->remove_cap( $cap );
				}
			}
		}
		if( current_user_can( 'dj' ) && !current_user_can( 'create_users' ) )	{
			if( isset( $perms['dj_add_client'] ) || $perms['dj_add_client'] == 'Y' )	{
				foreach( $caps as $cap )	{
					$dj_role->add_cap( $cap );
				}
			}
		}
		
	} // f_mdjm_set_role
	
/*
* f_mdjm_debug_notice
* 18/12/2014
* @since 0.9.9
* Displays notice if debugging is enabled
*/
	function f_mdjm_debug_notice()	{
		if( get_option( 'mdjm_debug' ) == '1' )	{
			f_mdjm_update_notice( 'error', 'Debugging is enabled for Mobile DJ Manager for WordPress.<br />We do not recommend this setting being turned on unless you have been asked to do so by the Support team' );
		}	
	} // f_mdjm_debug_notice
	
/*
* f_mdjm_unattended_event_notice
* 19/01/2015
* @since 1.0
* Displays notice if there are unattended events
*/
	function f_mdjm_unattended_event_notice()	{
		global $mdjm_options;
		
		if( current_user_can( 'administrator' ) && isset( $mdjm_options['warn_unattended'] ) && $mdjm_options['warn_unattended'] == 'Y' )	{
			$unattended = f_mdjm_event_count( 'Unattended', '' );
		}
		if( isset( $unattended ) && $unattended > 0 )	{
			if( $unattended == 1 )	{
				$message = 'There is currently ' . $unattended . ' <a href="' . admin_url( 'admin.php?page=mdjm-events&status=Unattended&orderby=event_date&order=desc' ) . '">Unattended Enquiry</a> that requires your attention. <a href="' . admin_url( 'admin.php?page=mdjm-events&status=Unattended&orderby=event_date&order=desc' ) . '">Click here to review and action this Enquiry now</a>';
			}
			else	{
				$message = 'There are currently ' . $unattended . ' <a href="' . admin_url( 'admin.php?page=mdjm-events&status=Unattended&orderby=event_date&order=desc' ) . '">Unattended Enquiries</a> that require your attention. <a href="' . admin_url( 'admin.php?page=mdjm-events&status=Unattended&orderby=event_date&order=desc' ) . '">Click here to review and action these Enquiries now</a>';	
			}
			f_mdjm_update_notice( 'update-nag', $message );
		}	
	} // f_mdjm_unattended_event_notice
	
/*
* f_mdjm_register_widgets
* 28/12/2014
* @since 0.9.9
* Registers all plugin widgets
*/
	function f_mdjm_register_widgets()	{
		include( WPMDJM_PLUGIN_DIR . '/widgets/class-mdjm-widget.php' );
		register_widget( 'MDJM_Availability_Widget' );
		register_widget( 'MDJM_ContactForms_Widget' );
	} // f_mdjm_register_widgets
	
/*
* f_mdjm_enqueue
* 13/01/2015
* @since 1.0
* Register and enqueue jQuery scripts
*/
	function f_mdjm_enqueue()	{
		wp_register_style( 'mobile-dj-manager', WPMDJM_PLUGIN_URL . '/includes/css/mdjm-styles.css' );
		wp_register_script( 'google-hosted-jquery', '//ajax.googleapis.com/ajax/libs/jquery/1.10.2/jquery.min.js', false );
		wp_register_script( 'jquery-validation-plugin', 'http://ajax.aspnetcdn.com/ajax/jquery.validate/1.11.1/jquery.validate.min.js', array( 'google-hosted-jquery' ) );
		
		wp_enqueue_script( 'jquery' );
		wp_enqueue_style( 'mobile-dj-manager');
		wp_enqueue_script( 'google-hosted-jquery');
		wp_enqueue_script( 'jquery-validation-plugin');
		
	} // f_mdjm_enqueue
	
	
/*
* f_mdjm_admin_enqueue
* 13/01/2015
* @since 1.0
* Register and enqueue jQuery scripts
*/
	function f_mdjm_admin_enqueue()	{
		wp_register_style( 'mobile-dj-manager-admin', WPMDJM_PLUGIN_URL . '/admin/includes/css/mdjm-admin.css' );
		wp_register_script( 'jquery-validation-plugin', 'http://ajax.aspnetcdn.com/ajax/jquery.validate/1.11.1/jquery.validate.min.js', array( 'google-hosted-jquery' ) );
		wp_register_script( 'mdjm-colour-picker', WPMDJM_PLUGIN_URL . '/admin/includes/js/mdjm-colour-picker.js', 'jquery', '20150304', true );
		
		wp_enqueue_style( 'mobile-dj-manager-admin' );
		wp_enqueue_script( 'jquery' );
		wp_enqueue_script( 'jquery-validation-plugin' );
		
		if( isset( $_GET['page'] ) && $_GET['page'] == 'mdjm-contact-forms' )	{
			wp_enqueue_style( 'wp-color-picker' );
			wp_enqueue_script( 'wp-color-picker' );	
			wp_enqueue_script( 'mdjm-colour-picker' );
		}
		
	} // f_mdjm_admin_enqueue

/*
* f_mdjm_template_messages
* 04/03/2015
* @since 1.1.1
* Customised messages for contract / email template updates
*/
	/*function f_mdjm_template_messages( $messages )	{
		global $post;
		
		$post_id = $post->ID;
		$post_type = get_post_type( $post_id );
		
		if( $post_type === 'email_template' )	{
			$singular = 'Email Template';
		}
		elseif( $post_type === 'contract' )	{
			$singular = 'Contract Template';
		}
		else	{
			return;	
		}

        $messages[$post_type] = array(
                0 => '', // Unused. Messages start at index 1.
                1 => sprintf( __( '%s updated. <a href="%s" target="_blank">Preview %s</a>' ), $singular, esc_url( get_permalink( $post_id ) ), 'Template' ),
                2 => __( 'Custom field updated.', 'mdjm' ),
                3 => __( 'Custom field deleted.', 'mdjm' ),
                4 => sprintf( __( '%s updated.', 'mdjm' ), $singular ),
                5 => isset( $_GET['revision']) ? sprintf( __('%2$s restored to revision from %1$s', 'maxson' ), wp_post_revision_title( (int) $_GET['revision'], false ), $singular ) : false,
                6 => sprintf( __( '%s published. <a href="%s">Preview %s</a>'), $singular, esc_url( get_permalink( $post_id ) ), 'Template' ),
                7 => sprintf( __( '%s saved.', 'mdjm' ), esc_attr( $singular ) ),
                8 => sprintf( __( '%s submitted. <a href="%s" target="_blank">Preview %s</a>'), $singular, esc_url( add_query_arg( 'preview', 'true', get_permalink( $post_id ) ) ), 'Template' ),
                9 => sprintf( __( '%s scheduled for: <strong>%s</strong>. <a href="%s" target="_blank">Preview %s</a>' ), $singular, date_i18n( __( 'M j, Y @ G:i' ), strtotime( $post->post_date ) ), esc_url( get_permalink( $post_id ) ), 'Template' ),
                10 => sprintf( __( '%s draft updated. <a href="%s" target="_blank">Preview %s</a>'), $singular, esc_url( add_query_arg( 'preview', 'true', get_permalink( $post_id ) ) ), 'Template' )
        );

        return $messages;
	} // f_mdjm_template_messages*/

/*
* f_mdjm_api_listener
* 17/02/2015
* @since 1.1
* The API Listener
*/
	function f_mdjm_api_listener()	{
		$listener = isset( $_GET['mdjm-api'] ) ? $_GET['mdjm-api'] : '';
		
		if( empty( $listener ) )
			return;
		
		elseif( $listener == 'MDJM_PAYPAL_GW' )	{
			include( WPMDJM_PLUGIN_DIR . '/admin/includes/api/mdjm-api-pp-ipn.php' );	
		}
		elseif( $listener == 'MDJM_EMAIL_RCPT' )	{
			include( WPMDJM_PLUGIN_DIR . '/admin/includes/api/mdjm-api-email-rcpt.php' );	
		}
		else	{
			return;	
		}
		
	} // f_mdjm_api_listener
	
/****************************************************************************************************
 *	ACTIONS & HOOKS
 ***************************************************************************************************/
  /**
 * Actions & filters customising the Admin UI
 *
 * 
 * 
 * @since 1.0
*/
	add_action( 'admin_init', 'f_mdjm_caps' ); // Give Admins the correct capabilities for the application
	
	add_action( 'plugins_loaded', 'f_mdjm_update_db_check' ); // Check if a DB update is needed

 	add_action( 'wp_login', 'f_mdjm_last_login', 10, 2 ); // Create last_login meta field and update
	
	add_action( 'init', 'f_mdjm_no_admin_for_clients' ); // Disable Admin UI for clients

	add_action( 'admin_menu', 'f_mdjm_remove_menus' ); // Remove menus from non-Admins
	
	add_action( 'jetpack_admin_menu', 'f_mdjm_remove_jetpack' ); // Remove Jetpack from non-Admins
	
	//add_action( 'init', 'f_mdjm_new_contracts_post_type' ); // Register the contracts post type
	
	//add_action( 'init', 'f_mdjm_email_template_post_type' ); // Register the email template post type
	
	//add_action( 'init', 'f_mdjm_communication_post_type' ); // Register the communication post type
		
	//add_filter( 'manage_mdjm_communication_posts_columns' , 'set_communication_post_columns' );
	
	//add_filter( 'manage_mdjm-venue_posts_columns' , 'set_venue_post_columns' );
	
	//add_action( 'manage_posts_custom_column', 'custom_post_column_action', 10, 1 );
	
	//add_filter( 'post_row_actions', 'custom_post_row_actions', 10, 2 );
	
	//add_filter( 'bulk_actions-edit-mdjm_communication', 'mdjm_communication_custom_bulk_actions' );
	
	//add_action( 'admin_menu', 'remove_publish_box' );
	
	//add_action( 'admin_head', 'remove_add_new');
	
	//add_action( 'add_meta_boxes', 'f_mdjm_email_meta_box' );
	
	//add_filter( 'gettext', 'rename_publish_button', 10, 2 );
		
	add_action( 'admin_init', 'f_mdjm_set_role' ); // Set correct user permissions
	
	add_action( 'admin_bar_menu', 'f_mdjm_toolbar_new_content', 70 ); // MDJM New Content to admin bar
	
	add_action( 'admin_bar_menu', 'f_mdjm_toolbar', 99 ); // MDJM Toolbar menu options
	
	add_action( 'in_admin_footer', 'f_mdjm_admin_footer' ); // MDJM Admin UI footer
	
	add_action( 'wp', 'f_mdjm_scheduler_activate' ); // Activate the MDJM Scheduler hook
	
	add_action( 'hook_mdjm_hourly_schedule', 'f_mdjm_cron' ); // Run the MDJM scheduler
	
	add_action( 'init', 'f_mdjm_upload_playlist_schedule' ); // Check upload playlist schedule
	
	add_action( 'admin_notices', 'f_mdjm_debug_notice' ); // Display notice if debugging enabled
	
	add_action( 'admin_notices', 'f_mdjm_unattended_event_notice' ); // Display notice if there are outstanding unattended enquiries
	
	add_action( 'widgets_init', 'f_mdjm_register_widgets' ); // Register the MDJM Widgets
	
	add_action( 'wp_enqueue_scripts', 'f_mdjm_enqueue' ); // Enqueue sytles and scripts in the frontend
	
	add_action( 'admin_enqueue_scripts', 'f_mdjm_admin_enqueue' ); // Enqueue sytles and scripts in the Admin UI
	
	add_action( 'wp_loaded', 'f_mdjm_api_listener' ); // The API listener
	
	//add_filter( 'post_updated_messages', 'f_mdjm_template_messages' );
 
 /**
 * Actions for custom user fields
 *
 * 
 * 
 * @since 1.0
*/
 	global $pagenow;
 	if( $pagenow == 'user-new.php' || $pagenow == 'user-edit.php' || $pagenow == 'profile.php' )	{
		require_once( WPMDJM_PLUGIN_DIR . '/admin/includes/custom-user.php' );
	
		add_action( 'admin_init', 'f_mdjm_edit_own_client_only', 1 );
	
		add_action( 'show_user_profile', 'f_mdjm_edit_profile_custom_fields' );
		add_action( 'edit_user_profile', 'f_mdjm_edit_profile_custom_fields' );
		
		add_action( 'user_new_form', 'f_mdjm_show_custom_user_field_registration' );
		add_action( 'user_register', 'f_mdjm_save_custom_user_fields', 10, 1 );
		
		add_action ( 'personal_options_update', 'f_mdjm_save_custom_user_fields' );
		add_action ( 'edit_user_profile_update', 'f_mdjm_save_custom_user_fields' );
	}
	
?>