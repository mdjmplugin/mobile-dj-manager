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
				
		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
		dbDelta( $events_sql );
		dbDelta( $venues_sql );
		dbDelta( $journal_sql );
		dbDelta( $playlists_sql );
		
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
		$playlist_when = 'General,First Dance,Second Dance,Last Song,Father & Bride,Mother & Son,DO NOT PLAY,Other,';
		$mdjm_init_options = array(
							'company_name' 			=> get_bloginfo( 'name' ),
							'app_name'				=> 'Client Zone',
							'time_format'           => 'H:i',
							'show_dashboard'		  => 'Y',
							'journaling'			  => 'Y',
							'multiple_dj' 			 => 'N',
							'packages' 				=> 'N',
							'event_types' 			 => $event_types,
							'enquiry_sources' 		 => $enquiry_sources,
							'default_contract' 		=> $contract_post_id,
							'id_prefix'             => 'MDJM',
							'system_email' 		    => get_bloginfo( 'admin_email' ),
							'bcc_dj_to_client' 		=> '',
							'bcc_admin_to_client' 	 => 'Y',
							'contract_to_client' 	  => '',
							'email_enquiry' 		   => $client_enquiry_post_id,
							'email_contract'		  => $client_contract_post_id,
							'email_client_confirm' 	=> $client_confirm_post_id,
							'email_dj_confirm' 		=> $dj_confirm_post_id,
							'title_as_subject'        => 'N',
							'playlist_when' 		   => $playlist_when,
							'playlist_close' 		  => '5',
							'upload_playlists' 		=> 'Y',
							'uninst_remove_db' 		=> 'N',
							'show_credits' 			=> 'Y',
							);
		$mdjm_init_pages = array(
							'app_home_page' => '',
							'contact_page' => '',
							'contracts_page' => '',
							'playlist_page' => '',
							'profile_page' => '',
							);
		$mdjm_init_permissions = array(
									'dj_see_wp_dash' => 'Y',
									'dj_add_event' => 'N',
									'dj_add_venue' => 'N',
									'dj_add_client' => 'N',
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
		
		/* Version information into the database */
		if( !get_option( 'mdjm_version' ) )	{
			add_option( 'mdjm_version', WPMDJM_VERSION_NUM );
		}
		
		/* Import the option keys */				
		add_option( WPMDJM_SETTINGS_KEY, $mdjm_init_options );
		add_option( WPMDJM_CLIENT_FIELDS, $mdjm_init_client_fields );
		add_option( 'mdjm_plugin_pages', $mdjm_init_pages );
		add_option( 'mdjm_plugin_permissions', $mdjm_init_permissions );
		add_option( 'mdjm_schedules', $mdjm_schedules );
		add_option( 'mdjm_updated', '0' );
		
		/* Add user roles */
		add_role( 'inactive_client', 'Inactive Client', array( 'read' => true ) );
		add_role( 'client', 'Client', array( 'read' => true ) );
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
			if( $current_version_mdjm == '0.9.3' )	{
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
			} // if( $current_version_mdjm == '0.9.3' )
			
/***************************************************
			 	UPGRADES FROM 0.9.4
***************************************************/
			if( $current_version_mdjm == '0.9.4' )	{
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
			} // if( $current_version_mdjm == '0.9.4' )
			
			/* Delete the template file */
			unlink( WPMDJM_PLUGIN_DIR . '/admin/includes/mdjm-templates.php' );
			
			/* Update the version number */
			update_option( 'mdjm_version', WPMDJM_VERSION_NUM );
			
			/* Make sure release notes are displayed */
			update_option( 'mdjm_updated', '1' );
			
			$message = 'Welcome to Mobile DJ Manager for WordPress version ' . WPMDJM_VERSION_NUM . '. <a href="' . admin_url( 'admin.php?page=mdjm-dashboard&updated=1' ) . '">Click here to view the release notes for this version</a>';
			
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
		if ( !current_user_can( 'edit_posts' ) && !current_user_can( 'edit_pages' ) ) {
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
	function f_mdjm_new_contracts_post_type()	{
		if( post_type_exists( 'contracts' ) )	{
			return; /* Nothing to do here */
		}
		require_once WPMDJM_PLUGIN_DIR . '/admin/admin.php';
		$lic_info = do_reg_check( 'check' );
		if( $lic_info )	{
		/* Build out the required arguments and register the post type */
			$contract_labels = array(
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
						'show_in_menu'	   => true,
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
			register_post_type( 'contract', $post_args );
		}
	} // f_mdjm_new_contracts_post_type
	
	/**
 * f_mdjm_email_template_post_type
 * Creates custom post type "email_template"
 * 
 * Called from: mobile-dj-manager.php
 * @since 1.0
*/
	function f_mdjm_email_template_post_type()	{
		if( post_type_exists( 'email_template' ) )	{
			return; /* Nothing to do here */
		}
		require_once WPMDJM_PLUGIN_DIR . '/admin/admin.php';
		$lic_info = do_reg_check( 'check' );
		if( $lic_info )	{
		/* Build out the required arguments and register the post type */
			$template_labels = array(
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
						'show_in_menu'		   => true,
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
			register_post_type( 'email_template', $post_args );
		}
	} // f_mdjm_email_template_post_type

/**
 * f_mdjm_toolbar
 * Creates custom tool bar menu structure
 * 
 * @since 1.0
*/	
	function f_mdjm_toolbar( $admin_bar )	{
		global $mdjm_options;
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
			if( current_user_can( 'manage_options' ) )
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
				'id'    => 'mdjm-clients',
				'parent' => 'mdjm',
				'title' => 'Clients',
				'href'  => admin_url( 'admin.php?page=mdjm-clients' ),
				'meta'  => array(
					'title' => __( 'Client List' ),
				),
			));
			$admin_bar->add_menu( array(
				'id'    => 'mdjm-add-client',
				'parent' => 'mdjm-clients',
				'title' => 'Add Client',
				'href'  => admin_url( 'user-new.php' ),
				'meta'  => array(
					'title' => __( 'Add New Client' ),
				),
			));
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
					'id'    => 'mdjm-contracts',
					'parent' => 'mdjm',
					'title' => 'Contracts',
					'href'  => admin_url( 'edit.php?post_type=contract' ),
					'meta'  => array(
						'title' => __( 'Contracts' ),
					),
				));
				$admin_bar->add_menu( array(
					'id'    => 'mdjm-new-contract',
					'parent' => 'mdjm-contracts',
					'title' => 'Add Contract',
					'href'  => admin_url( 'post-new.php?post_type=contract' ),
					'meta'  => array(
						'title' => __( 'New Contract' ),
					),
				));
			}
			if( current_user_can( 'manage_options' ) && isset( $mdjm_options['multiple_dj'] ) && $mdjm_options['multiple_dj'] == 'Y')
				$admin_bar->add_menu( array(
					'id'    => 'mdjm-djs',
					'parent' => 'mdjm',
					'title' => 'DJ List',
					'href'  => admin_url( 'admin.php?page=mdjm-djs' ),
					'meta'  => array(
					'title' => __( 'List of DJ\'s' ),
					),
				));
			if( current_user_can( 'manage_options' ) )	{
				$admin_bar->add_menu( array(
					'id'    => 'mdjm-email-templates',
					'parent' => 'mdjm',
					'title' => 'Email Templates',
					'href'  => admin_url( 'edit.php?post_type=email_template' ),
					'meta'  => array(
						'title' => __( 'Email Templates' ),
					),
				));
				$admin_bar->add_menu( array(
					'id'    => 'mdjm-new-email-template',
					'parent' => 'mdjm-email-templates',
					'title' => 'Add Template',
					'href'  => admin_url( 'post-new.php?post_type=email_template' ),
					'meta'  => array(
						'title' => __( 'New Email Template' ),
					),
				));
			}
			if( current_user_can( 'manage_options' ) && isset( $mdjm_options['enable_packages'] ) && $mdjm_options['enable_packages'] == 'Y' )
				$admin_bar->add_menu( array(
					'id'    => 'mdjm-equipment',
					'parent' => 'mdjm',
					'title' => 'Equipment &amp; Packages',
					'href'  => admin_url( 'admin.php?page=mdjm-packages' ),
					'meta'  => array(
						'title' => __( 'Equipment Inventory' ),
					),
				));
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
			$admin_bar->add_menu( array(
				'id'    => 'mdjm-enquiries',
				'parent' => 'mdjm-events',
				'title' => 'View Enquiries',
				'href'  => admin_url( 'admin.php?page=mdjm-events&display=enquiries' ),
				'meta'  => array(
					'title' => __( 'Outstanding Enquiries' ),
				),
			));
			$admin_bar->add_menu( array(
				'id'    => 'mdjm-venues',
				'parent' => 'mdjm',
				'title' => 'Venues',
				'href'  => admin_url( 'admin.php?page=mdjm-venues' ),
				'meta'  => array(
					'title' => __( 'MDJM Venues' ),
				),
			));
			if( current_user_can( 'manage_options' ) || dj_can( 'add_venue' ) )	{
				$admin_bar->add_menu( array(
					'id'    => 'mdjm-add-venue',
					'parent' => 'mdjm-venues',
					'title' => 'Add Venue',
					'href'  => admin_url( 'admin.php?page=mdjm-venues&action=add_venue_form' ),
					'meta'  => array(
						'title' => __( 'Add New Venue' ),
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
			echo '<p align="center" class="description">Powered by <a style="color:#F90" href="http://www.mydjplanner.co.uk" target="_blank">' . WPMDJM_NAME . '</a>, version ' . WPMDJM_VERSION_NUM . '</p>';
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
	
	add_action( 'init', 'f_mdjm_new_contracts_post_type' ); // Register the contracts post type
	
	add_action( 'init', 'f_mdjm_email_template_post_type' ); // Register the email template post type
	
	add_action( 'admin_bar_menu', 'f_mdjm_toolbar_new_content', 70 ); // MDJM New Content to admin bar
	
	add_action( 'admin_bar_menu', 'f_mdjm_toolbar', 99 ); // MDJM Toolbar menu options
	
	add_action( 'in_admin_footer', 'f_mdjm_admin_footer' ); // MDJM Admin UI footer
	
	add_action( 'wp', 'f_mdjm_scheduler_activate' ); // Activate the MDJM Scheduler hook
	
	add_action( 'hook_mdjm_hourly_schedule', 'f_mdjm_cron' ); // Run the MDJM scheduler
	
	add_action( 'init', 'f_mdjm_upload_playlist_schedule' ); // Check upload playlist schedule
 
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