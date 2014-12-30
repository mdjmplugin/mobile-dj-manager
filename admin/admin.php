<?php
	require_once WPMDJM_PLUGIN_DIR . '/admin/includes/functions.php';
	include_once( WPMDJM_PLUGIN_DIR . '/includes/config.inc.php' );
/**************************************************************
-	Display the admin menu
**************************************************************/
	add_action( 'admin_menu', 'f_mdjm_admin_menu' );
	
/**************************************************************
-	Initialise the admin sections and fields
**************************************************************/
	function f_mdjm_settings_init()	{
		global $mdjm_options;
		$admin_settings_field = array(  'company_name',
										'app_name',
										'time_format',
										'pass_length',
										'currency',
										'show_dashboard',
										'journaling',
										'multiple_dj',
										'enable_packages',
										'event_types',
										'enquiry_sources',
										'default_contract',
										'id_prefix',
										'system_email',
										'bcc_dj_to_client',
										'bcc_admin_to_client',
										'contract_to_client',
										'email_enquiry',
										'enquiry_email_from',
										'email_contract',
										'contract_email_from',
										'email_client_confirm',
										'confirm_email_from',
										'email_dj_confirm',
										'title_as_subject',
										'playlist_when',
										'playlist_close',
										'upload_playlists',
										'uninst_remove_db',
										'show_credits',
										'app_home_page',
										'contact_page',
										'contracts_page',
										'playlist_page',
										'profile_page',
										'dj_see_wp_dash',
										'dj_add_client',
										'dj_add_event',
										'dj_view_enquiry',
										'dj_add_venue',
										'dj_see_deposit',
										'dj_disable_shortcode',
										'dj_disable_template',
										'custom_client_text',
										'not_logged_in',
										'home_welcome',
										'home_noevents',
										'home_notactive',
										'playlist_welcome',
										'playlist_intro',
										'playlist_edit',
										'playlist_closed',
										'playlist_noevent',
										'playlist_guest_welcome',
										'playlist_guest_intro',
										'playlist_guest_closed',
									);
		foreach( $admin_settings_field as $admin_setting_field_key )	{
			if( !isset( $mdjm_options[$admin_setting_field_key] ) ) $mdjm_options[$admin_setting_field_key] = 'N';
		}
		$admin_fields = array();

/* GENERAL TAB */
		/* "mdjm_" is auto added as prefix to section */
		/* "_settings" is auto added as suffix to section */
		$admin_fields['company_name'] = array(
									'display' => 'Company Name',
									'key' => 'mdjm_plugin_settings',
									'type' => 'text',
									'class' => 'regular-text',
									'value' => $mdjm_options['company_name'],
									'text' => '',
									'desc' => 'Enter your company name',
									'section' => 'general',
									'page' => 'settings',
									); // company_name
																
		$admin_fields['app_name'] = array(
									'display' => 'Application Name',
									'key' => 'mdjm_plugin_settings',
									'type' => 'text',
									'class' => 'regular-text',
									'value' => $mdjm_options['app_name'],
									'text' => 'Default is <strong>Client Zone</strong>',
									'desc' => 'Choose your own name for the application. It\'s recommended you give the top level menu item linking to the application the same name.',
									'section' => 'general',
									'page' => 'settings',
									); // app_name
									
		$admin_fields['time_format'] = array(
									'display' => 'Display Time as?',
									'key' => 'mdjm_plugin_settings',
									'type' => 'custom_dropdown',
									'class' => 'regular-text',
									'value' => $mdjm_options['time_format'],
									'text' => '',
									'desc' => 'Select the format in which you want your event times displayed. Applies to both admin and client pages',
									'custom_args' => array (
														'name' =>  'mdjm_plugin_settings[time_format]',
														'sort_order' => '',
														'selected' => $mdjm_options['time_format'],
														'list_type' => 'defined',
														'list_values' => array( 'g:i A' => date( 'g:i A' ),
																				'H:i' => date( 'H:i' ) ),
														),
									'section' => 'general',
									'page' => 'settings',
									); // time_format
									
		$admin_fields['pass_length'] = array(
									'display' => 'Default Password Length',
									'key' => 'mdjm_plugin_settings',
									'type' => 'custom_dropdown',
									'class' => 'small-text',
									'value' => $mdjm_options['pass_length'],
									'text' => '',
									'desc' => 'If opting to generate a user password during event creation, how many characters should the password be?',
									'custom_args' => array (
														'name' =>  'mdjm_plugin_settings[pass_length]',
														'sort_order' => '',
														'selected' => $mdjm_options['pass_length'],
														'list_type' => 'defined',
														'list_values' => array( '5'  => '5',
																				'6'  => '6',
																				'7'  => '7',
																				'8'  => '8',
																				'9'  => '9',
																				'10' => '10',
																				'11' => '11',
																				'12' => '12', ),
														),
									'section' => 'general',
									'page' => 'settings',
									); // pass_length
									
		$admin_fields['currency'] = array(
									'display' => 'Currency',
									'key' => 'mdjm_plugin_settings',
									'type' => 'custom_dropdown',
									'class' => 'small-text',
									'value' => $mdjm_options['currency'],
									'text' => '',
									'desc' => '',
									'custom_args' => array (
														'name' =>  'mdjm_plugin_settings[currency]',
														'sort_order' => '',
														'selected' => $mdjm_options['currency'],
														'list_type' => 'defined',
														'list_values' => array( 'EUR' => '&euro;',
																				'GBP' => '&pound;',
																				'USD' => '$', ),
														
																		
														),
									'section' => 'general',
									'page' => 'settings',
									); // pass_length
		
		$admin_fields['show_dashboard'] = array(
									'display' => 'Show Dashboard Widget?',
									'key' => 'mdjm_plugin_settings',
									'type' => 'checkbox',
									'class' => 'code',
									'value' => $mdjm_options['show_dashboard'],
									'text' => '',
									'desc' => 'Displays the MDJM widget on the main Wordpress Admin Dashboard',
									'section' => 'general',
									'page' => 'settings',
									); // show_dashboard
									
		$admin_fields['event_types'] = array(
									'display' => 'Event Types',
									'key' => 'mdjm_plugin_settings',
									'type' => 'textarea',
									'class' => 'all-options',
									'value' => str_replace( ",", "\n", $mdjm_options['event_types'] ),
									'text' => '',
									'desc' => 'The types of events that you provide. One per line.',
									'section' => 'general',
									'page' => 'settings',
									); // event_types
									
		$admin_fields['enquiry_sources'] = array(
									'display' => 'Enquiry Sources',
									'key' => 'mdjm_plugin_settings',
									'type' => 'textarea',
									'class' => 'all-options',
									'value' => str_replace( ",", "\n", $mdjm_options['enquiry_sources'] ),
									'text' => '',
									'desc' => 'Enter possible sources of enquiries. One per line',
									'section' => 'general',
									'page' => 'settings',
									); // enquiry_sources
									
		$admin_fields['default_contract'] = array(
									'display' => 'Default Client Contract',
									'key' => 'mdjm_plugin_settings',
									'type' => 'custom_dropdown',
									'class' => 'regular-text',
									'value' => $mdjm_options['default_contract'],
									'text' => '<a href="' . admin_url() . 'post-new.php?post_type=contract" class="add-new-h2">Add New</a>',
									'desc' => 'Select the client contract you want to use as default. This can be changed per event.',
									'custom_args' => array (
														'name' =>  'mdjm_plugin_settings[default_contract]',
														'sort_order' => 'ASC',
														'selected' => $mdjm_options['default_contract'],
														'list_type' => 'contract'
														),
									'section' => 'general',
									'page' => 'settings',
									); // default_contract
		
		$admin_fields['id_prefix'] = array(
									'display' => 'Contract / Invoice Prefix',
									'key' => 'mdjm_plugin_settings',
									'type' => 'text',
									'class' => 'regular-text',
									'value' => $mdjm_options['id_prefix'],
									'text' => '',
									'desc' => 'Contracts &amp; Invoices are assigned the unique event ID by default. If you wish to prefix this number, enter the prefix here',
									'section' => 'general',
									'page' => 'settings',
									); // id_prefix
									
		$admin_fields['system_email'] = array(
									'display' => 'Default Email Address',
									'key' => 'mdjm_plugin_settings',
									'type' => 'email',
									'class' => 'regular-text',
									'value' => $mdjm_options['system_email'],
									'text' => 'Defaults to the E-mail Address set within <a href="' . admin_url( 'options-general.php' ) . '">WordPress Settings > General</a>',
									'desc' => 'The email address you want generic emails from MDJM to come from',
									'section' => 'email',
									'page' => 'settings',
									); // system_email
									
		$admin_fields['bcc_dj_to_client'] = array(
									'display' => 'Copy DJ in Client Emails',
									'key' => 'mdjm_plugin_settings',
									'type' => 'checkbox',
									'class' => 'code',
									'value' => $mdjm_options['bcc_dj_to_client'],
									'text' => '',
									'desc' => 'Blind copy DJ in emails to client',
									'section' => 'email',
									'page' => 'settings',
									); // bcc_dj_to_client
									
		$admin_fields['bcc_admin_to_client'] = array(
									'display' => 'Copy Admin in Client Emails',
									'key' => 'mdjm_plugin_settings',
									'type' => 'checkbox',
									'class' => 'code',
									'value' => $mdjm_options['bcc_admin_to_client'],
									'text' => '',
									'desc' => 'Blind copy the WP admin in emails to client',
									'section' => 'email',
									'page' => 'settings',
									); // bcc_admin_to_client
									
		$admin_fields['contract_to_client'] = array(
									'display' => 'Email contract link to client?',
									'key' => 'mdjm_plugin_settings',
									'type' => 'checkbox',
									'class' => 'code',
									'value' => $mdjm_options['contract_to_client'],
									'text' => '',
									'desc' => 'Email client with contract details when an enquiry is converted and the event status changes to Pending',
									'section' => 'email',
									'page' => 'settings',
									); // contract_to_client
									
		$admin_fields['email_enquiry'] = array(
									'display' => 'Event Quote Template',
									'key' => 'mdjm_plugin_settings',
									'type' => 'custom_dropdown',
									'class' => 'regular-text',
									'value' => $mdjm_options['email_enquiry'],
									'text' => '<a href="' . admin_url() . 'post-new.php?post_type=email_template" class="add-new-h2">Add New</a>',
									'desc' => 'Select an email template to be used when sending quotes to clients',
									'custom_args' => array (
														'name' =>  'mdjm_plugin_settings[email_enquiry]',
														'sort_order' => 'ASC',
														'selected' => $mdjm_options['email_enquiry'],
														'list_type' => 'email_template'
														),
									'section' => 'email',
									'page' => 'settings',
									); // email_enquiry
									
		$admin_fields['enquiry_email_from'] = array(
									'display' => 'Send Quote\'s From',
									'key' => 'mdjm_plugin_settings',
									'type' => 'custom_dropdown',
									'class' => 'regular-text',
									'value' => $mdjm_options['enquiry_email_from'],
									'text' => '',
									'desc' => 'Select Admin to have quotes emailed from the address specified in the <strong>Default Email Address</strong> or DJ for the from address to be the DJ\'s email address',
									'custom_args' => array (
														'name' =>  'mdjm_plugin_settings[enquiry_email_from]',
														'sort_order' => 'ASC',
														'selected' => $mdjm_options['enquiry_email_from'],
														'list_type' => 'defined',
														'list_values' => array( 'admin' => 'Admin',
																				'dj'    => 'Event DJ', ),
														),
									'section' => 'email',
									'page' => 'settings',
									); // enquiry_email_from
									
		$admin_fields['email_contract'] = array(
									'display' => 'Contract Template',
									'key' => 'mdjm_plugin_settings',
									'type' => 'custom_dropdown',
									'class' => 'regular-text',
									'value' => $mdjm_options['email_contract'],
									'text' => '<a href="' . admin_url() . 'post-new.php?post_type=email_template" class="add-new-h2">Add New</a>',
									'desc' => 'Select an email template to be used when sending the Contract to clients',
									'custom_args' => array (
														'name' =>  'mdjm_plugin_settings[email_contract]',
														'sort_order' => 'ASC',
														'selected' => $mdjm_options['email_contract'],
														'list_type' => 'email_template'
														),
									'section' => 'email',
									'page' => 'settings',
									); // email_contract
									
		$admin_fields['contract_email_from'] = array(
									'display' => 'Send Contract Email From',
									'key' => 'mdjm_plugin_settings',
									'type' => 'custom_dropdown',
									'class' => 'regular-text',
									'value' => $mdjm_options['contract_email_from'],
									'text' => '',
									'desc' => 'Select Admin to have contracts emailed from the address specified in the <strong>Default Email Address</strong> or DJ for the from address to be the DJ\'s email address',
									'custom_args' => array (
														'name' =>  'mdjm_plugin_settings[contract_email_from]',
														'sort_order' => 'ASC',
														'selected' => $mdjm_options['contract_email_from'],
														'list_type' => 'defined',
														'list_values' => array( 'admin' => 'Admin',
																				'dj'    => 'Event DJ', ),
														),
									'section' => 'email',
									'page' => 'settings',
									); // contract_email_from
									
		$admin_fields['email_client_confirm'] = array(
									'display' => 'Client Booking Confirmation Template',
									'key' => 'mdjm_plugin_settings',
									'type' => 'custom_dropdown',
									'class' => 'regular-text',
									'value' => $mdjm_options['email_client_confirm'],
									'text' => '<a href="' . admin_url() . 'post-new.php?post_type=email_template" class="add-new-h2">Add New</a>',
									'desc' => 'Select an email template to be used when sending the Booking Confirmation to Clients',
									'custom_args' => array (
														'name' =>  'mdjm_plugin_settings[email_client_confirm]',
														'sort_order' => 'ASC',
														'selected' => $mdjm_options['email_client_confirm'],
														'list_type' => 'email_template'
														),
									'section' => 'email',
									'page' => 'settings',
									); // email_client_confirm
									
		$admin_fields['confirm_email_from'] = array(
									'display' => 'Send Booking Confirmation From',
									'key' => 'mdjm_plugin_settings',
									'type' => 'custom_dropdown',
									'class' => 'regular-text',
									'value' => $mdjm_options['confirm_email_from'],
									'text' => '',
									'desc' => 'Select Admin to have client booking confirmations emailed from the address specified in the <strong>Default Email Address</strong> or DJ for the from address to be the DJ\'s email address',
									'custom_args' => array (
														'name' =>  'mdjm_plugin_settings[confirm_email_from]',
														'sort_order' => 'ASC',
														'selected' => $mdjm_options['confirm_email_from'],
														'list_type' => 'defined',
														'list_values' => array( 'admin' => 'Admin',
																				'dj'    => 'Event DJ', ),
														),
									'section' => 'email',
									'page' => 'settings',
									); // contract_email_from
									
		$admin_fields['email_dj_confirm'] = array(
									'display' => 'DJ Booking Confirmation Template',
									'key' => 'mdjm_plugin_settings',
									'type' => 'custom_dropdown',
									'class' => 'regular-text',
									'value' => $mdjm_options['email_dj_confirm'],
									'text' => '<a href="' . admin_url() . 'post-new.php?post_type=email_template" class="add-new-h2">Add New</a>',
									'desc' => 'Select an email template to be used when sending the Booking Confirmation to DJ\'s',
									'custom_args' => array (
														'name' =>  'mdjm_plugin_settings[email_dj_confirm]',
														'sort_order' => 'ASC',
														'selected' => $mdjm_options['email_dj_confirm'],
														'list_type' => 'email_template'
														),
									'section' => 'email',
									'page' => 'settings',
									); // email_dj_confirm
									
		$admin_fields['title_as_subject'] = array(
									'display' => 'Template Title is Subject?',
									'key' => 'mdjm_plugin_settings',
									'type' => 'checkbox',
									'class' => 'code',
									'value' => $mdjm_options['title_as_subject'],
									'text' => '',
									'desc' => 'Use your Email Template\'s title as the subject for the templates specified above',
									'section' => 'email',
									'page' => 'settings',
									); // title_as_subject
									
		$admin_fields['journaling'] = array(
									'display' => 'Enable Journaling?',
									'key' => 'mdjm_plugin_settings',
									'type' => 'checkbox',
									'class' => 'code',
									'value' => $mdjm_options['journaling'],
									'text' => '',
									'desc' => 'Log and track all client &amp; event actions (recommended)',
									'section' => 'general',
									'page' => 'settings',
									); // journaling
									
		$admin_fields['multiple_dj'] = array(
									'display' => 'Multiple DJ\'s',
									'key' => 'mdjm_plugin_settings',
									'type' => 'checkbox',
									'class' => 'code',
									'value' => $mdjm_options['multiple_dj'],
									'text' => '',
									'desc' => 'Check this if you employ DJ\'s',
									'section' => 'general',
									'page' => 'settings',
									); // multiple_dj
									
		$admin_fields['enable_packages'] = array(
									'display' => 'Equipment Packages',
									'key' => 'mdjm_plugin_settings',
									'type' => 'checkbox',
									'class' => 'code',
									'value' => $mdjm_options['enable_packages'],
									'text' => '',
									'desc' => 'Check this to enable Equipment Packages & Inventories',
									'section' => 'general',
									'page' => 'settings',
									); // enable_packages
									
		$admin_fields['playlist_when'] = array(
									'display' => 'Playlist Song Options',
									'key' => 'mdjm_plugin_settings',
									'type' => 'textarea',
									'class' => 'all-options',
									'value' => str_replace( ",", "\n", $mdjm_options['playlist_when'] ),
									'text' => '',
									'desc' => 'The options clients can select for when songs are to be played when adding to the playlist. One per line.',
									'section' => 'playlist',
									'page' => 'settings',
									); // playlist_when
									
		$admin_fields['playlist_close'] = array(
									'display' => 'Close the Playlist',
									'key' => 'mdjm_plugin_settings',
									'type' => 'text',
									'class' => 'small-text',
									'value' => $mdjm_options['playlist_close'],
									'text' => 'Enter 0 to never close',
									'desc' => 'Days before the event should the playlist be closed.',
									'section' => 'playlist',
									'page' => 'settings',
									); // playlist_close
									
		$admin_fields['uninst_remove_db'] = array(
									'display' => 'Remove Database Tables?',
									'key' => 'mdjm_plugin_settings',
									'type' => 'checkbox',
									'class' => 'code',
									'value' => $mdjm_options['uninst_remove_db'],
									'text' => '',
									'desc' => 'Should the database tables and data be removed when uninstalling the plugin? Cannot be recovered unless you or your host have a backup solution in place.',
									'section' => 'uninstall',
									'page' => 'settings',
									); // uninst_remove_db
									
		$admin_fields['show_credits'] = array(
									'display' => 'Display Credits?',
									'key' => 'mdjm_plugin_settings',
									'type' => 'checkbox',
									'class' => 'code',
									'value' => $mdjm_options['show_credits'],
									'text' => '',
									'desc' => 'Whether or not to display the <font size="-1"; color="#F90">Powered by ' . WPMDJM_NAME . ', version ' . WPMDJM_VERSION_NUM . '</font> text at the footer of the application pages.',
									'custom_args' => array (
														'name' =>  '',
														'sort_order' => '',
														'selected' => '',
														'list_type' => ''
														),
									'section' => 'credits',
									'page' => 'settings',
									); // show_credits
									
		$admin_fields['upload_playlists'] = array(
									'display' => 'Upload Playlists?',
									'key' => 'mdjm_plugin_settings',
									'type' => 'checkbox',
									'class' => 'code',
									'value' => $mdjm_options['upload_playlists'],
									'text' => '',
									'desc' => 'With this option checked, your playlist information may occasionally be transmitted back to the MDJM authors to help build an information library. The consolidated list of playlist songs will be freely shared. Only song, artist and the event type information is transmitted.',
									'custom_args' => array (
														'name' =>  '',
														'sort_order' => '',
														'selected' => '',
														'list_type' => ''
														),
									'section' => 'playlist',
									'page' => 'settings',
									); // upload_playlists
		
/* PAGES TAB */
		$admin_fields['app_home_page'] = array(
									'display' => WPMDJM_APP_NAME . ' Home Page',
									'key' => 'mdjm_plugin_pages',
									'type' => 'custom_dropdown',
									'class' => 'regular-text',
									'value' => $mdjm_options['app_home_page'],
									'text' => '<a href="' . admin_url() . 'post-new.php?post_type=page" class="add-new-h2">Add New</a>',
									'desc' => 'Select the home page for the ' . WPMDJM_APP_NAME . ' application  - the one where you added the shortcode <code>[MDJM page=Home]</code>',
									'custom_args' => array (
														'name' =>  'mdjm_plugin_pages[app_home_page]',
														'sort_order' => 'ASC',
														'selected' => $mdjm_options['app_home_page'],
														'list_type' => 'page'
														),
									'section' => 'pages',
									'page' => 'pages',
									); // app_home_page

		$admin_fields['contact_page'] = array(
									'display' => 'Contact Page',
									'key' => 'mdjm_plugin_pages',
									'type' => 'custom_dropdown',
									'class' => 'regular-text',
									'value' => $mdjm_options['contact_page'],
									'text' => '<a href="' . admin_url() . 'post-new.php?post_type=page" class="add-new-h2">Add New</a>',
									'desc' => 'Select your website\'s contact page so we can correctly direct visitors.',
									'custom_args' => array (
														'name' =>  'mdjm_plugin_pages[contact_page]',
														'sort_order' => 'ASC',
														'selected' => $mdjm_options['contact_page'],
														'list_type' => 'page'
														),
									'section' => 'pages',
									'page' => 'pages',
									); // contact_page
									
		$admin_fields['contracts_page'] = array(
									'display' => 'Contracts Page',
									'key' => 'mdjm_plugin_pages',
									'type' => 'custom_dropdown',
									'class' => 'regular-text',
									'value' => $mdjm_options['contracts_page'],
									'text' => '<a href="' . admin_url() . 'post-new.php?post_type=page" class="add-new-h2">Add New</a>',
									'desc' => 'Select your website\'s contracts page - the one where you added the shortcode <code>[MDJM page=Contract]</code>',
									'custom_args' => array (
														'name' =>  'mdjm_plugin_pages[contracts_page]',
														'sort_order' => 'ASC',
														'selected' => $mdjm_options['contracts_page'],
														'list_type' => 'page'
														),
									'section' => 'pages',
									'page' => 'pages',
									); // contracts_page
									
		$admin_fields['playlist_page'] = array(
									'display' => 'Playlist Page',
									'key' => 'mdjm_plugin_pages',
									'type' => 'custom_dropdown',
									'class' => 'regular-text',
									'value' => $mdjm_options['playlist_page'],
									'text' => '<a href="' . admin_url() . 'post-new.php?post_type=page" class="add-new-h2">Add New</a>',
									'desc' => 'Select your website\'s playlist page - the one where you added the shortcode <code>[MDJM page=Playlist]</code>',
									'custom_args' => array (
														'name' =>  'mdjm_plugin_pages[playlist_page]',
														'sort_order' => 'ASC',
														'selected' => $mdjm_options['playlist_page'],
														'list_type' => 'page'
														),
									'section' => 'pages',
									'page' => 'pages',
									); // playlist_page
									
		$admin_fields['profile_page'] = array(
									'display' => 'Profile Page',
									'key' => 'mdjm_plugin_pages',
									'type' => 'custom_dropdown',
									'class' => 'regular-text',
									'value' => $mdjm_options['profile_page'],
									'text' => '<a href="' . admin_url() . 'post-new.php?post_type=page" class="add-new-h2">Add New</a>',
									'desc' => 'Select your website\'s profile page - the one where you added the shortcode <code>[MDJM page=Profile]</code>',
									'custom_args' => array (
														'name' =>  'mdjm_plugin_pages[profile_page]',
														'sort_order' => 'ASC',
														'selected' => $mdjm_options['profile_page'],
														'list_type' => 'page'
														),
									'section' => 'pages',
									'page' => 'pages',
									); // profile_page

/* PERMISSIONS TAB */
		$admin_fields['dj_see_wp_dash'] = array(
									'display' => 'DJ\'s see WP Dashboard?',
									'key' => 'mdjm_plugin_permissions',
									'type' => 'checkbox',
									'class' => 'code',
									'value' => $mdjm_options['dj_see_wp_dash'],
									'text' => 'If checked your DJ\'s will be able to see the main WordPress Dashboard page',
									'desc' => '',
									'custom_args' => array (
														'name' =>  '',
														'sort_order' => '',
														'selected' => '',
														'list_type' => ''
														),
									'section' => 'permissions',
									'page' => 'permissions',
									); // dj_see_wp_dash

		$admin_fields['dj_add_client'] = array(
									'display' => 'DJ\'s can Add New Clients?',
									'key' => 'mdjm_plugin_permissions',
									'type' => 'checkbox',
									'class' => 'code',
									'value' => $mdjm_options['dj_add_client'],
									'text' => '',
									'desc' => '',
									'custom_args' => array (
														'name' =>  '',
														'sort_order' => '',
														'selected' => '',
														'list_type' => ''
														),
									'section' => 'permissions',
									'page' => 'permissions',
									); // dj_add_client
									
		$admin_fields['dj_add_event'] = array(
									'display' => 'DJ\'s Can Add New Events?',
									'key' => 'mdjm_plugin_permissions',
									'type' => 'checkbox',
									'class' => 'code',
									'value' => $mdjm_options['dj_add_event'],
									'text' => '',
									'desc' => '',
									'custom_args' => array (
														'name' =>  '',
														'sort_order' => '',
														'selected' => '',
														'list_type' => ''
														),
									'section' => 'permissions',
									'page' => 'permissions',
									); // dj_add_event
									
		$admin_fields['dj_view_enquiry'] = array(
									'display' => 'DJ\'s Can View Enquiries',
									'key' => 'mdjm_plugin_permissions',
									'type' => 'checkbox',
									'class' => 'code',
									'value' => $mdjm_options['dj_view_enquiry'],
									'text' => '',
									'desc' => '',
									'custom_args' => array (
														'name' =>  '',
														'sort_order' => '',
														'selected' => '',
														'list_type' => ''
														),
									'section' => 'permissions',
									'page' => 'permissions',
									); // dj_view_enquiry
									
		$admin_fields['dj_add_venue'] = array(
									'display' => 'DJ\'s Can Add New Venues?',
									'key' => 'mdjm_plugin_permissions',
									'type' => 'checkbox',
									'class' => 'code',
									'value' => $mdjm_options['dj_add_venue'],
									'text' => '',
									'desc' => '',
									'custom_args' => array (
														'name' =>  '',
														'sort_order' => '',
														'selected' => '',
														'list_type' => ''
														),
									'section' => 'permissions',
									'page' => 'permissions',
									); // dj_add_venue
									
		$admin_fields['dj_see_deposit'] = array(
									'display' => 'DJ\'s Can See Deposit Info?',
									'key' => 'mdjm_plugin_permissions',
									'type' => 'checkbox',
									'class' => 'code',
									'value' => $mdjm_options['dj_see_deposit'],
									'text' => '',
									'desc' => '',
									'custom_args' => array (
														'name' =>  '',
														'sort_order' => '',
														'selected' => '',
														'list_type' => ''
														),
									'section' => 'permissions',
									'page' => 'permissions',
									); // dj_see_deposit
									
		$admin_fields['dj_disable_shortcode'] = array(
									'display' => 'Disabled Shortcodes for DJ\'s',
									'key' => 'mdjm_plugin_permissions',
									'type' => 'multiple_select',
									'class' => 'code',
									'value' => $mdjm_options['dj_disable_shortcode'],
									'text' => 'CTRL (cmd on MAC) + Click to select multiple Shortcode entries that DJ\'s cannot use',
									'desc' => '<a href="http://www.mydjplanner.co.uk/shortcodes/" target="_blank">Full list of Shortcodes</a>',
									'custom_args' => array (
														'name' =>  'mdjm_plugin_permissions[dj_disable_shortcode][]',
														'sort_order' => '',
														'selected' => $mdjm_options['dj_disable_shortcode'],
														'list_type' => 'shortcodes',
														),
									'section' => 'shortcodes',
									'page' => 'permissions',
									); // dj_disable_shortcode
									
		$admin_fields['dj_disable_template'] = array(
									'display' => 'Disabled Email Templates for DJ\'s',
									'key' => 'mdjm_plugin_permissions',
									'type' => 'multiple_select',
									'class' => 'code',
									'value' => $mdjm_options['dj_disable_template'],
									'text' => 'CTRL (cmd on MAC) + Click to select multiple Template entries that DJ\'s cannot use',
									'desc' => '',
									'custom_args' => array (
														'name' =>  'mdjm_plugin_permissions[dj_disable_template][]',
														'sort_order' => '',
														'selected' => $mdjm_options['dj_disable_template'],
														'list_type' => 'email_templates',
														),
									'section' => 'templates',
									'page' => 'permissions',
									); // dj_disable_template
									
/* CLIENT TEXT TAB */
		$admin_fields['custom_client_text'] = array(
									'display' => 'Enable Customised Text?',
									'key' => 'mdjm_frontend_text',
									'type' => 'checkbox',
									'class' => 'code',
									'value' => $mdjm_options['custom_client_text'],
									'text' => '',
									'desc' => 'Use custom text on Client front end web pages',
									'section' => 'general',
									'page' => 'client-text',
									); // custom_client_text
									
		$admin_fields['not_logged_in'] = array(
									'display' => 'Not Logged In:',
									'key' => 'mdjm_frontend_text',
									'type' => 'textarea',
									'class' => '',
									'value' => $mdjm_options['not_logged_in'],
									'text' => '',
									'desc' => 'Text displayed with login fields if Client is not logged in',
									'section' => 'login',
									'page' => 'client-text',
									); // not_logged_in
									
		$admin_fields['home_welcome'] = array(
									'display' => 'Welcome Text:',
									'key' => 'mdjm_frontend_text',
									'type' => 'textarea',
									'class' => '',
									'value' => esc_attr( $mdjm_options['home_welcome'] ),
									'text' => '',
									'desc' => 'Welcome text displayed on the home page',
									'section' => 'home_page',
									'page' => 'client-text',
									); // home_welcome
									
		$admin_fields['home_noevents'] = array(
									'display' => 'No Events:',
									'key' => 'mdjm_frontend_text',
									'type' => 'textarea',
									'class' => '',
									'value' => $mdjm_options['home_noevents'],
									'text' => '',
									'desc' => 'Text displayed on client home page if the client has no events in the system',
									'section' => 'home_page',
									'page' => 'client-text',
									); // home_noevents
									
		$admin_fields['home_notactive'] = array(
									'display' => 'Event Not Active:',
									'key' => 'mdjm_frontend_text',
									'type' => 'textarea',
									'class' => '',
									'value' => $mdjm_options['home_notactive'],
									'text' => '',
									'desc' => 'Text displayed on client event review screen if the selected event is not active',
									'section' => 'home_page',
									'page' => 'client-text',
									); // home_notactive
									
		$admin_fields['playlist_welcome'] = array(
									'display' => 'Playlist Welcome:',
									'key' => 'mdjm_frontend_text',
									'type' => 'textarea',
									'class' => '',
									'value' => $mdjm_options['playlist_welcome'],
									'text' => '',
									'desc' => 'Welcome text displayed to logged in users on the Playlist page',
									'section' => 'playlist_page',
									'page' => 'client-text',
									); // playlist_welcome
									
		$admin_fields['playlist_intro'] = array(
									'display' => 'Playlist Intro:',
									'key' => 'mdjm_frontend_text',
									'type' => 'textarea',
									'class' => '',
									'value' => $mdjm_options['playlist_intro'],
									'text' => '',
									'desc' => 'Introduction text displayed on playlist page to logged in users',
									'section' => 'playlist_page',
									'page' => 'client-text',
									); // playlist_intro
									
		$admin_fields['playlist_edit'] = array(
									'display' => 'Editing Playlist:',
									'key' => 'mdjm_frontend_text',
									'type' => 'textarea',
									'class' => '',
									'value' => $mdjm_options['playlist_edit'],
									'text' => '',
									'desc' => 'Text displayed to logged in user when editing an event playlist',
									'section' => 'playlist_page',
									'page' => 'client-text',
									); // playlist_edit
									
		$admin_fields['playlist_closed'] = array(
									'display' => 'Playlist Closed:',
									'key' => 'mdjm_frontend_text',
									'type' => 'textarea',
									'class' => '',
									'value' => $mdjm_options['playlist_closed'],
									'text' => '',
									'desc' => 'Text displayed to logged in user when playlist is closed',
									'section' => 'playlist_page',
									'page' => 'client-text',
									); // playlist_closed
									
		$admin_fields['playlist_noevent'] = array(
									'display' => 'No Active Events:',
									'key' => 'mdjm_frontend_text',
									'type' => 'textarea',
									'class' => '',
									'value' => $mdjm_options['playlist_noevent'],
									'text' => '',
									'desc' => 'Text displayed to logged in users who have no active events',
									'section' => 'playlist_page',
									'page' => 'client-text',
									); // playlist_noevent
									
		$admin_fields['playlist_guest_welcome'] = array(
									'display' => 'Guest Welcome:',
									'key' => 'mdjm_frontend_text',
									'type' => 'textarea',
									'class' => '',
									'value' => $mdjm_options['playlist_guest_welcome'],
									'text' => '',
									'desc' => 'Welcome text displayed to guests',
									'section' => 'playlist_page',
									'page' => 'client-text',
									); // playlist_guest_welcome
									
		$admin_fields['playlist_guest_intro'] = array(
									'display' => 'Guest Intro:',
									'key' => 'mdjm_frontend_text',
									'type' => 'textarea',
									'class' => '',
									'value' => $mdjm_options['playlist_guest_intro'],
									'text' => '',
									'desc' => 'Introduction text displayed on playlist page to guests',
									'section' => 'playlist_page',
									'page' => 'client-text',
									); // playlist_guest_intro
									
		$admin_fields['playlist_guest_closed'] = array(
									'display' => 'Guest Playlist Closed:',
									'key' => 'mdjm_frontend_text',
									'type' => 'textarea',
									'class' => '',
									'value' => $mdjm_options['playlist_guest_closed'],
									'text' => '',
									'desc' => 'Text displayed to guests when playlist is closed',
									'section' => 'playlist_page',
									'page' => 'client-text',
									); // playlist_guest_closed
		
		add_settings_section( 'mdjm_general_settings',
							  '',
							  'f_mdjm_desc',
							  'mdjm-settings'
							);
		add_settings_section( 'mdjm_email_settings',
							  'Email Options <hr />',
							  'f_mdjm_desc',
							  'mdjm-settings'
							);
		add_settings_section( 'mdjm_playlist_settings',
							  'Playlist Options <hr />',
							  'f_mdjm_desc',
							  'mdjm-settings'
							);
		add_settings_section( 'mdjm_uninstall_settings',
							  'Uninstall Options <hr />',
							  'f_mdjm_desc',
							  'mdjm-settings'
							);
		add_settings_section( 'mdjm_credits_settings',
							  'Credits &amp; Feedback <hr />',
							  'f_mdjm_desc',
							  'mdjm-settings'
							);
		add_settings_section( 'mdjm_pages_settings',
							  '',
							  'f_mdjm_desc',
							  'mdjm-pages'
							);
		add_settings_section( 'mdjm_permissions_settings',
							  '',
							  'f_mdjm_desc',
							  'mdjm-permissions'
							);
		add_settings_section( 'mdjm_shortcodes_settings',
							  'Shortcodes <hr />',
							  'f_mdjm_desc',
							  'mdjm-permissions'
							);
		add_settings_section( 'mdjm_templates_settings',
							  'Templates <hr />',
							  'f_mdjm_desc',
							  'mdjm-permissions'
							);
		add_settings_section( 'mdjm_general_settings',
							  '',
							  'f_mdjm_desc',
							  'mdjm-client-text'
							);
		add_settings_section( 'mdjm_login_settings',
							  '',
							  'f_mdjm_desc',
							  'mdjm-client-text'
							);
		add_settings_section( 'mdjm_home_page_settings',
							  'Home Page <hr />',
							  'f_mdjm_desc',
							  'mdjm-client-text'
							);
		add_settings_section( 'mdjm_playlist_page_settings',
							  'Playlist Page <hr />',
							  'f_mdjm_desc',
							  'mdjm-client-text'
							);
		
		foreach( $admin_settings_field as $settings_field )	{
			if( isset( $admin_fields[$settings_field]['custom_args'] ) && !empty( $admin_fields[$settings_field]['custom_args'] ) ) $custom_args = $admin_fields[$settings_field]['custom_args'];
			else $custom_args = '';
			add_settings_field( $settings_field,
							'<label for="' . $settings_field . '">' . $admin_fields[$settings_field]['display'] . '</label>',
							'f_mdjm_general_settings_callback',
							'mdjm-' . $admin_fields[$settings_field]['page'],
							'mdjm_' . $admin_fields[$settings_field]['section'] . '_settings',
							array( 
								'field' => $settings_field,
								'label for' => $settings_field,
								'key' => $admin_fields[$settings_field]['key'],
								'type' => $admin_fields[$settings_field]['type'],
								'class' => $admin_fields[$settings_field]['class'],
								'value' => $admin_fields[$settings_field]['value'],
								'text' => $admin_fields[$settings_field]['text'],
								'desc' => $admin_fields[$settings_field]['desc'],
								'custom_args' => $custom_args
								)
						);
		} // foreach
		register_setting( 'mdjm-settings', WPMDJM_SETTINGS_KEY );
		register_setting( 'mdjm-permissions', 'mdjm_plugin_permissions' );
		register_setting( 'mdjm-pages', 'mdjm_plugin_pages' );
		register_setting( 'mdjm-client-text', WPMDJM_FETEXT_SETTINGS_KEY );
	} // f_mdjm_settings_init
	
	add_action( 'admin_init', 'f_mdjm_settings_init' );

/**************************************************************
-	Callbacks for sections, fields & validation
**************************************************************/	
	function f_mdjm_desc()	{
		// Intentionally blank
	} // f_mdjm_desc

/* Validate the fields */	
	function f_mdjm_validate_settings( $input )	{
		$valid = array();
		/* Check for incomplete fields */
		if( !isset( $input['company_name'] ) || empty( $input['company_name'] ) )	{
			add_settings_error(
				'mdjm_company_name',
				'mdjm_company_name_texterror',
				'Company Name cannot be empty',
				'error'
			);
		}
		
		
		$valid['company_name'] = sanitize_text_field( $input['company_name'] );

		if( $valid['company_name'] != $input['company_name'] ) {
			add_settings_error(
				'mdjm_company_name',           // setting title
				'mdjm_company_name_texterror',            // error ID
				'Inavlid entry for Company Name',   // error message
				'error'                        // type of message
			);		
	}
		return $valid;
	} // f_mdjm_validate_settings

/* Process the fields to be displayed */
	function f_mdjm_general_settings_callback( $args )	{
		global $mdjm_options;
		if( isset( $args['type'] ) && $args['type'] == 'custom_dropdown' )	{
			if( $args['custom_args']['list_type'] == 'page' )	{
				wp_dropdown_pages( $args['custom_args'] );
			}
			elseif( $args['custom_args']['list_type'] == 'contract' )	{
				echo '<select name="' . $args['key'] . '[' . $args['field'] . ']" id="' . $args['field'] . '">';
				$contract_args = array(
									'post_type' => 'contract',
									'orderby' => 'name',
									'order' => 'ASC',
									);
				$contract_query = new WP_Query( $contract_args );
				if ( $contract_query->have_posts() ) {
					while ( $contract_query->have_posts() ) {
						$contract_query->the_post();
						echo '<option value="' . get_the_id() . '"';
						if( $mdjm_options['default_contract'] == get_the_id() )	{
							echo ' selected="selected"';	
						}
						echo '>' . get_the_title() . '</option>' . "\n";	
					}
				}
				wp_reset_postdata();
				echo '</select>';
			}
			elseif( $args['custom_args']['list_type'] == 'email_template' )	{
				echo '<select name="' . $args['key'] . '[' . $args['field'] . ']" id="' . $args['field'] . '">';
				$email_args = array(
									'post_type' => 'email_template',
									'orderby' => 'name',
									'order' => 'ASC',
									);
				$email_query = new WP_Query( $email_args );
				if ( $email_query->have_posts() ) {
					while ( $email_query->have_posts() ) {
						$email_query->the_post();
						echo '<option value="' . get_the_id() . '"';
						if( $mdjm_options[$args['field']] == get_the_id() )	{
							echo ' selected="selected"';	
						}
						echo '>' . get_the_title() . '</option>' . "\n";	
					}
				}
				wp_reset_postdata();
				echo '</select>';
			}
			elseif( $args['custom_args']['list_type'] == 'defined' )		{
				echo '<select name="' . $args['key'] . '[' . $args['field'] . ']" id="' . $args['field'] . '">';
				foreach( $args['custom_args']['list_values'] as $s_key => $s_value )	{
					echo '<option value="' . $s_key . '"';
					if( $mdjm_options[$args['field']] == $s_key )	{
						echo ' selected="selected"';	
					}
					echo '>' . $s_value . '</option>' . "\n";
				}
				echo '</select>';
			}
		}
		elseif( isset( $args['type'] ) && $args['type'] == 'multiple_select' )	{
			if( $args['custom_args']['list_type'] == 'shortcodes' )		{
				echo '<select size="8" name="' . $args['key'] . '[' . $args['field'] . '][]" id="' . $args['field'] . '" multiple="multiple">';
				include( WPMDJM_PLUGIN_DIR . '/admin/includes/config.inc.php' );
				foreach( $shortcode_content_search as $shortcode )	{
					echo '<option value="' . $shortcode . '"';
					if( in_array( $shortcode, $mdjm_options['dj_disable_shortcode'] ) )	{
						echo ' selected="selected"';	
					}
					echo '>' . $shortcode . '</option>';
				}
				echo '</select>';	
			}
			elseif( $args['custom_args']['list_type'] == 'email_templates' )		{
				echo '<select size="8" name="' . $args['key'] . '[' . $args['field'] . '][]" id="' . $args['field'] . '" multiple="multiple">';
				$email_args = array(
									'post_type' => 'email_template',
									'orderby' => 'name',
									'order' => 'ASC',
									);
				$email_query = new WP_Query( $email_args );
				if ( $email_query->have_posts() ) {
					while ( $email_query->have_posts() ) {
						$email_query->the_post();
						echo '<option value="' . get_the_id() . '"';
						if( in_array( get_the_id(), $mdjm_options['dj_disable_template'] ) )	{
							echo ' selected="selected"';	
						}
						echo '>' . get_the_title() . '</option>' . "\n";	
					}
				}
				wp_reset_postdata();
				echo '</select>';	
			}
		}
		elseif( $args['type'] == 'textarea' )	{
			echo '<textarea id="' . $args['field'] . '" name="' . $args['key'] . '[' . $args['field'] . ']" cols="80" rows="6" class="' . $args['class'] . '">' . $args['value'] . '</textarea>';
		}
		elseif( $args['type'] == 'checkbox' )	{
			echo '<input name="' . $args['key'] . '[' . $args['field'] . ']" id="' . $args['field'] . '" type="' . $args['type'] . '" value="Y" class="' . $args['class']  . '" ' . 
			checked( $args['value'], 'Y', false ) . ' />';
		}
		else	{
			echo '<input name="' . $args['key'] . '[' . $args['field'] . ']" id="' . $args['field'] . '" type="' . $args['type'] . '" class="' . $args['class'] . '" value="' . $args['value'] . '" />';
		}
		if( isset( $args['text'] ) && !empty( $args['text'] ) ) echo '<label for="' . $args['field'] . '"> ' . $args['text'] . '</label>';
		if( isset( $args['desc'] ) && !empty( $args['desc'] ) ) echo '<p class="description">' . $args['desc'] . '</p>';
	} // f_mdjm_general_settings_callback

	function f_mdjm_app_validate()	{
		$lic_check = do_reg_check( 'check' );
		if( !$lic_check )	{
			echo '<div class="error">';
			echo '<p>Your Mobile DJ Manager license has expired. Please visit <a href="http://www.mydjplanner.co.uk" target="_blank">http://www.mydjplanner.co.uk</a> to renew.</p>';
			echo '<p>Functionality will be restricted until your license is renewed.</p>';
			echo '</div>';
		}
	}
	add_action( 'admin_notices', 'f_mdjm_app_validate' );
	
	/* Validation */
	function f_mdjm_reg_init()	{ /* Update the status if required */
		include WPMDJM_PLUGIN_DIR . '/admin/includes/config.inc.php';
		if( false === ( $reg_check = get_transient( $t_query[0] ) ) )
			do_reg_check( 'set' );
	}

/*
* do_reg_check
* 04/10/2014
* @since 0.8
* Checks license status and returns the result
*/	
	function do_reg_check( $t_action )	{
		include WPMDJM_PLUGIN_DIR . '/admin/includes/config.inc.php';
		if( $t_action == 'set' )	{
			set_transient( $t_query[0], wp_remote_retrieve_body( wp_remote_get( $t_query[1] . $t_query[2] . $t_query[3] . $t_query[4] ) ), DAY_IN_SECONDS / 2 );
		}
		
		if( $t_action == 'check' )	{
			$reg_check = get_transient( $t_query[0] ); /* Get the value */
			
			if( $reg_check !== false && $reg_check != 'License key is invalid' )	{
				$lic = explode( '|', $reg_check );
				if( time() <= strtotime( $lic[2] ) )	{
					$lic_info = $lic;
				}
				else	{
					$is_trial = get_transient( 'mdjm_is_trial' );
					if( $is_trial !== false )	{
						$lic = explode( '|', $is_trial );
						if( time() <= strtotime( $lic[2] ) )	{
							$lic_info = $lic;
						}
						else	{
							$lic_info === false;	
						}
					}
				}
			}
			else	{ // Trans was false
				$is_trial = get_transient( 'mdjm_is_trial' );
				if( $is_trial !== false )	{
					$lic = explode( '|', $is_trial );
					if( time() <= strtotime( $lic[2] ) )	{
						$lic_info = $lic;
					}
					else	{
						$lic_info === false;	
					}
				}
				
				else	{ 
					$lic_info === false;
				}
			}
			return $lic_info;
		}
	}	
?>
