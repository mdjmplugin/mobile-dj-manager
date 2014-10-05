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
		
		if( is_admin() && current_user_can( 'client' ) && ! ( defined( 'DOING_AJAX' ) && DOING_AJAX ) ) {
			wp_redirect( home_url() );
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
		if( current_user_can( 'client' ) )	show_admin_bar( false );
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
			if( $mdjm_options['dj_see_wp_dash'] != 'Y' ) remove_menu_page( 'index.php' );
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
		$playlists_sql = "CREATE TABLE ". $db_tbl['playlists'] . " (
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
		$event_types = 'Adult Birthday Party,Child Birthday Party,Wedding,Corporate Event,Other,';
		$playlist_when = 'General,First Dance,Second Dance,Last Song,Father & Bride,Mother & Son,DO NOT PLAY,Other,';
		$mdjm_init_options = array(
							'company_name' => get_bloginfo( 'name' ),
							'app_name' => 'Client Zone',
							'show_dashboard' => 'N',
							'journaling' => 'Y',
							'multiple_dj' => 'N',
							'event_types' => $event_types,
							'bcc_dj_to_client' => '',
							'bcc_admin_to_client' => '',
							'contract_to_client' => '',
							'playlist_when' => $playlist_when,
							'playlist_close' => '5',
							'upload_playlists' => '',
							'uninst_remove_db' => 'N',
							'show_credits' => 'Y',
							);
		$mdjm_init_pages = array(
							'app_home_page' => '',
							'contact_page' => '',
							'contracts_page' => '',
							'playlist_page' => '',
							);
		$mdjm_init_permissions = array(
									'dj_see_wp_dash' => 'N',
									'dj_add_event' => 'N',
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
		
		/* Import the default email templates */
		if( !get_option( WPMDJM_SETTINGS_KEY ) )	{
			include WPMDJM_PLUGIN_DIR . '/admin/includes/mdjm-templates.php';
			add_option( 'mdjm_plugin_email_template_enquiry', $email_enquiry_content );
			add_option( 'mdjm_plugin_email_template_contract_review', $email_contract_review );
			add_option( 'mdjm_plugin_email_template_client_booking_confirm', $email_client_booking_confirm );
			add_option( 'mdjm_plugin_email_template_dj_booking_confirm', $email_dj_booking_confirm );
			
			/* Import the default template contract */
			wp_insert_post( $contract_template_args );
		}
		
		/* Import the option keys */				
		add_option( WPMDJM_SETTINGS_KEY, $mdjm_init_options );
		add_option( WPMDJM_CLIENT_FIELDS, $mdjm_init_client_fields );
		add_option( 'mdjm_plugin_pages', $mdjm_init_pages );
		add_option( 'mdjm_plugin_permissions', $mdjm_init_permissions );
		
		add_role( 'client', 'Client', array( 'read' => true ) );
		add_role( 'dj', 'DJ', array( 'read' => true, 
									 'manage_mdjm' => true,
									 'create_users' => true, 
									 'edit_users' => true,
									 'delete_users' => true
								) );
		require_once( WPMDJM_PLUGIN_DIR . '/admin/includes/functions.php' );
		if( !get_option( 'has_been_set' ) )	{
			set_transient( 'mdjm_is_beta', 'XXXX|' . date( 'Y-m-d' ) . '|' . date( 'Y-m-d', strtotime( "+30 days" ) ), 30 * DAY_IN_SECONDS );
			add_option( 'has_been_set', time() );
		}
		do_reg_check( 'set' );
	} // f_mdjm_install

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
		define( 'WPMDJM_CONTACT_PAGE', $mdjm_options['contact_page'] );
		
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
			$mdjm_links = array( '<a href="http://www.mdjm.co.uk/forums/" target="_blank">Support</a>' );
			if( !$lic_info || $lic_info[0] == 'XXXX' )
				$mdjm_links[] = '<a href="http://www.mdjm.co.uk/forums/" target="_blank">Buy Now</a>';
			$new_links = array(
						'<a href="http://www.mdjm.co.uk/forums/" target="_blank">Support</a>'
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
						'menu_name'          => 'MDJM Contracts',
						'name_admin_bar'     => 'Contract',
						'add_new'            => 'Add New',
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
						'menu_position'      => null,
						'supports'           => array( 'title', 'editor', 'author' ),
						'menu_icon'		  => 'dashicons-welcome-write-blog'
						);
			/* Now register the new post type */
			register_post_type( 'contract', $post_args );
		}
	}
	
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