<?php
	require_once WPMDJM_PLUGIN_DIR . '/admin/includes/functions.php';
	include_once( WPMDJM_PLUGIN_DIR . '/includes/config.inc.php' );
/**************************************************************
-	Display the admin menu
**************************************************************/
	//add_action( 'admin_menu', 'f_mdjm_admin_menu' );
	
/**************************************************************
-	Initialise the admin sections and fields
**************************************************************/
	function f_mdjm_settings_init()	{
		global $mdjm_options;
		/*$admin_settings_field = array(  
										// General Settings
										'company_name',
										'app_name',
										'artist',
										'items_per_page',
										'time_format',
										'short_date_format',
										'pass_length',
										'currency',
										'show_dashboard',
										'warn_unattended',
										'journaling',
										'multiple_dj',
										'enable_packages',
										//'event_types',
										'enquiry_sources',
										'default_contract',
										'id_prefix',
										'system_email',
										'track_client_emails',
										'bcc_dj_to_client',
										'bcc_admin_to_client',
										'booking_conf_to_client',
										'booking_conf_to_dj',
										'contract_to_client',
										'email_enquiry',
										'enquiry_email_from',
										'email_contract',
										'contract_email_from',
										'email_client_confirm',
										'confirm_email_from',
										'email_dj_confirm',
										'unavailable_email_template',
										'confirm_email_from',
										'title_as_subject',
										'playlist_when',
										'playlist_close',
										'upload_playlists',
										'uninst_remove_mdjm_templates',
										'uninst_remove_db',
										'show_credits',
										
										// Pages Settings
										'app_home_page',
										'contact_page',
										'contracts_page',
										'playlist_page',
										'profile_page',
										'payments_page',
										'availability_check_pass_page',
										'availability_check_pass_text',
										'availability_check_fail_page',
										'availability_check_fail_text',
										
										// Permissions Settings
										'dj_see_wp_dash',
										'dj_add_client',
										'dj_add_event',
										'dj_view_enquiry',
										'dj_add_venue',
										'dj_see_deposit',
										'dj_disable_shortcode',
										'dj_disable_template',
										
										// Client Dialogue Settings
										'deposit_label',
										'balance_label',
										'warn_incomplete_profile',
										'custom_client_text',
										'not_logged_in',
										'home_welcome',
										'home_noevents',
										'home_notactive',
										'contract_intro',
										'contract_not_ready',
										'contract_signed',
										'contract_sign_success',
										'playlist_welcome',
										'playlist_intro',
										'playlist_edit',
										'playlist_closed',
										'playlist_noevent',
										'playlist_guest_welcome',
										'playlist_guest_intro',
										'playlist_guest_closed',
										'payment_welcome',
										'payment_intro',
										'payment_complete',
										'payment_cancel',
										'payment_not_due',
										'payment_noevent',
										'payment_noaccess',
										
										// Payments Settings
										'pp_cfm_template',
										'pp_manual_cfm_template',
										'pp_default_method',
										'pp_form_layout',
										'pp_label',
										'pp_enable_tax',
										'pp_tax_type',
										'pp_tax_rate',
										'pp_payment_sources',
										//'pp_transaction_types',
										'pp_enable',
										'pp_email',
										'pp_redirect',
										'pp_cancel',
										'pp_button',
										'pp_sandbox',
										'pp_sandbox_email',
										'pp_debug',
										'pp_receiver',
										//'pp_inv_prefix',
										'pp_checkout_style',
										
									);
		foreach( $admin_settings_field as $admin_setting_field_key )	{
			if( !isset( $mdjm_options[$admin_setting_field_key] ) ) $mdjm_options[$admin_setting_field_key] = 'N';
		}
		$admin_fields = array();*/

/* GENERAL TAB */
		/* "mdjm_" is auto added as prefix to section */
		/* "_settings" is auto added as suffix to section */
		/*$admin_fields['company_name'] = array(
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
									'text' => 'Default is <code>Client Zone</code>',
									'desc' => 'Choose your own name for the application. It\'s recommended you give the top level menu item linking to the application the same name.',
									'section' => 'general',
									'page' => 'settings',
									); // app_name
									
		$admin_fields['artist'] = array(
									'display' => 'Refer to Performers as?',
									'key' => 'mdjm_plugin_settings',
									'type' => 'text',
									'class' => 'regular-text',
									'value' => $mdjm_options['artist'],
									'text' => 'Default is <code>DJ</code>',
									'desc' => 'Change the name of your performers here as necessary. Useful if you are not a DJ business',
									'section' => 'general',
									'page' => 'settings',
									); // artist
									
		$admin_fields['items_per_page'] = array(
									'display' => 'Items per Page',
									'key' => 'mdjm_plugin_settings',
									'type' => 'custom_dropdown',
									'class' => 'small-text',
									'value' => $mdjm_options['items_per_page'],
									'text' => '',
									'desc' => 'The number of items you want to list per page in event/client/DJ/Venue view',
									'custom_args' => array (
														'name' =>  'mdjm_plugin_settings[items_per_page]',
														'sort_order' => '',
														'selected' => $mdjm_options['items_per_page'],
														'list_type' => 'defined',
														'list_values' => array( '10' => '10',
																				'25' => '25',
																				'50' => '50',
																				'100' => '100',
																			),
														),
									'section' => 'general',
									'page' => 'settings',
									); // items_per_page
									
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
									
		$admin_fields['short_date_format'] = array(
									'display' => 'Short Date Format',
									'key' => 'mdjm_plugin_settings',
									'type' => 'custom_dropdown',
									'class' => 'regular-text',
									'value' => $mdjm_options['short_date_format'],
									'text' => '',
									'desc' => 'Select the format in which you want short dates displayed. Applies to both admin and client pages',
									'custom_args' => array (
														'name' =>  'mdjm_plugin_settings[short_date_format]',
														'sort_order' => '',
														'selected' => $mdjm_options['short_date_format'],
														'list_type' => 'defined',
														'list_values' => array( 'd/m/Y' => date( 'd/m/Y' ) . ' - d/m/Y',
																				'm/d/Y' => date( 'm/d/Y' ) . ' - m/d/Y',
																				'Y/m/d' => date( 'Y/m/d' ) . ' - Y/m/d',
																				'd-m-Y' => date( 'd-m-Y' ) . ' - d-m-Y',
																				'm-d-Y' => date( 'm-d-Y' ) . ' - m-d-Y',
																				'Y-m-d' => date( 'Y-m-d' ) . ' - Y-m-d', ),
														),
									'section' => 'general',
									'page' => 'settings',
									); // short_date_format*/
									
		/*$admin_fields['pass_length'] = array(
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
									'page' => 'clients',
									); // pass_length*/
									
		/*$admin_fields['currency'] = array(
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
									); // currency*/
		
		/*$admin_fields['show_dashboard'] = array(
									'display' => 'Show Dashboard Widget?',
									'key' => 'mdjm_plugin_settings',
									'type' => 'checkbox',
									'class' => 'code',
									'value' => $mdjm_options['show_dashboard'],
									'text' => '',
									'desc' => 'Displays the MDJM widget on the main Wordpress Admin Dashboard',
									'section' => 'general',
									'page' => 'settings',
									); // show_dashboard*/
									
		/*$admin_fields['warn_unattended'] = array(
									'display' => 'New Enquiry Notification?',
									'key' => 'mdjm_plugin_settings',
									'type' => 'checkbox',
									'class' => 'code',
									'value' => $mdjm_options['warn_unattended'],
									'text' => '',
									'desc' => 'Displays a notification message at the top of the Admin pages to Administrators if there are outstanding Unattended Enquiries',
									'section' => 'general',
									'page' => 'settings',
									); // warn_unattended*/
																		
		/*$admin_fields['enquiry_sources'] = array(
									'display' => 'Enquiry Sources',
									'key' => 'mdjm_plugin_settings',
									'type' => 'textarea',
									'class' => 'all-options',
									'value' => str_replace( ",", "\n", $mdjm_options['enquiry_sources'] ),
									'text' => '',
									'desc' => 'Enter possible sources of enquiries. One per line',
									'section' => 'general',
									'page' => 'settings',
									); // enquiry_sources*/
									
		/*$admin_fields['default_contract'] = array(
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
									); // id_prefix*/
									
		/*$admin_fields['system_email'] = array(
									'display' => 'Default Email Address',
									'key' => 'mdjm_plugin_settings',
									'type' => 'email',
									'class' => 'regular-text',
									'value' => $mdjm_options['system_email'],
									'text' => 'Defaults to the E-mail Address set within <a href="' . admin_url( 'options-general.php' ) . '">WordPress Settings > General</a>',
									'desc' => 'The email address you want generic emails from MDJM to come from',
									'section' => 'email',
									'page' => 'settings',
									); // system_email*/
									
		/*$admin_fields['track_client_emails'] = array(
									'display' => 'Track Client Emails?',
									'key' => 'mdjm_plugin_settings',
									'type' => 'checkbox',
									'class' => 'code',
									'value' => $mdjm_options['track_client_emails'],
									'text' => 'If selected you can determine if your emails have been opened',
									'desc' => '<code>Note</code>: not all email clients will support this',
									'section' => 'email',
									'page' => 'settings',
									); // track_client_emails*/
		
		/*$admin_fields['bcc_dj_to_client'] = array(
									'display' => 'Copy DJ in Client Emails?',
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
									'display' => 'Copy Admin in Client Emails?',
									'key' => 'mdjm_plugin_settings',
									'type' => 'checkbox',
									'class' => 'code',
									'value' => $mdjm_options['bcc_admin_to_client'],
									'text' => '',
									'desc' => 'Blind copy the WP admin in emails to client',
									'section' => 'email',
									'page' => 'settings',
									); // bcc_admin_to_client*/
									
		/*$admin_fields['booking_conf_to_client'] = array(
									'display' => 'Booking Confirmation to client?',
									'key' => 'mdjm_plugin_settings',
									'type' => 'checkbox',
									'class' => 'code',
									'value' => $mdjm_options['booking_conf_to_client'],
									'text' => '',
									'desc' => 'Email client with selected template when booking is confirmed i.e. contract accepted, or status changed to Approved',
									'section' => 'email',
									'page' => 'settings',
									); // booking_conf_to_client*/
									
		/*$admin_fields['booking_conf_to_dj'] = array(
									'display' => 'Booking Confirmation to DJ?',
									'key' => 'mdjm_plugin_settings',
									'type' => 'checkbox',
									'class' => 'code',
									'value' => $mdjm_options['booking_conf_to_dj'],
									'text' => '',
									'desc' => 'Email DJ with selected template when booking is confirmed i.e. contract accepted, or status changed to Approved',
									'section' => 'email',
									'page' => 'settings',
									); // booking_conf_to_dj*/
		
		/*$admin_fields['contract_to_client'] = array(
									'display' => 'Contract link to client?',
									'key' => 'mdjm_plugin_settings',
									'type' => 'checkbox',
									'class' => 'code',
									'value' => $mdjm_options['contract_to_client'],
									'text' => '',
									'desc' => 'Email client with contract details when an enquiry is converted and the event status changes to Pending',
									'section' => 'email',
									'page' => 'settings',
									); // contract_to_client*/
									
		/*$admin_fields['email_enquiry'] = array(
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
									); // email_enquiry*/
									
		/*$admin_fields['enquiry_email_from'] = array(
									'display' => 'Send Quote\'s From',
									'key' => 'mdjm_plugin_settings',
									'type' => 'custom_dropdown',
									'class' => 'regular-text',
									'value' => $mdjm_options['enquiry_email_from'],
									'text' => '',
									'desc' => 'Select Admin to have quotes emailed from the address specified in the <code>Default Email Address</code> or DJ for the from address to be the DJ\'s email address',
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
									); // enquiry_email_from*/
									
		/*$admin_fields['email_contract'] = array(
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
									); // email_contract*/
									
		/*$admin_fields['contract_email_from'] = array(
									'display' => 'Send Contract Email From',
									'key' => 'mdjm_plugin_settings',
									'type' => 'custom_dropdown',
									'class' => 'regular-text',
									'value' => $mdjm_options['contract_email_from'],
									'text' => '',
									'desc' => 'Select Admin to have contracts emailed from the address specified in the <code>Default Email Address</code> or DJ for the from address to be the DJ\'s email address',
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
									); // contract_email_from*/
									
		/*$admin_fields['email_client_confirm'] = array(
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
									'desc' => 'Select Admin to have client booking confirmations emailed from the address specified in the <code>Default Email Address</code> or DJ for the from address to be the DJ\'s email address',
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
									); // contract_email_from*/
									
		/*$admin_fields['email_dj_confirm'] = array(
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
									); // email_dj_confirm*/
									
		/*$admin_fields['unavailable_email_template'] = array(
									'display' => 'Unavailability Email Template',
									'key' => 'mdjm_plugin_settings',
									'type' => 'custom_dropdown',
									'class' => 'regular-text',
									'value' => $mdjm_options['unavailable_email_template'],
									'text' => '<a href="' . admin_url() . 'post-new.php?post_type=email_template" class="add-new-h2">Add New</a>',
									'desc' => 'Select an email template to be used when replying unavailable to enquiries',
									'custom_args' => array (
														'name' =>  'mdjm_plugin_settings[unavailable_email_template]',
														'sort_order' => 'ASC',
														'selected' => $mdjm_options['unavailable_email_template'],
														'list_type' => 'email_template'
														),
									'section' => 'email',
									'page' => 'settings',
									); // unavailable_email_template*/
									
		/*$admin_fields['title_as_subject'] = array(
									'display' => 'Template Title is Subject?',
									'key' => 'mdjm_plugin_settings',
									'type' => 'checkbox',
									'class' => 'code',
									'value' => $mdjm_options['title_as_subject'],
									'text' => '',
									'desc' => 'Use your Email Template\'s title as the subject for the templates specified above',
									'section' => 'email',
									'page' => 'settings',
									); // title_as_subject*/
									
		/*$admin_fields['journaling'] = array(
									'display' => 'Enable Journaling?',
									'key' => 'mdjm_plugin_settings',
									'type' => 'checkbox',
									'class' => 'code',
									'value' => $mdjm_options['journaling'],
									'text' => '',
									'desc' => 'Log and track all client &amp; event actions (recommended)',
									'section' => 'general',
									'page' => 'settings',
									); // journaling*/
									
		/*$admin_fields['multiple_dj'] = array(
									'display' => 'Multiple DJ\'s',
									'key' => 'mdjm_plugin_settings',
									'type' => 'checkbox',
									'class' => 'code',
									'value' => $mdjm_options['multiple_dj'],
									'text' => '',
									'desc' => 'Check this if you employ DJ\'s',
									'section' => 'general',
									'page' => 'settings',
									)*/; // multiple_dj
									
		/*$admin_fields['enable_packages'] = array(
									'display' => 'Equipment Packages',
									'key' => 'mdjm_plugin_settings',
									'type' => 'checkbox',
									'class' => 'code',
									'value' => $mdjm_options['enable_packages'],
									'text' => '',
									'desc' => 'Check this to enable Equipment Packages & Inventories',
									'section' => 'general',
									'page' => 'settings',
									); // enable_packages*/
									
		/*$admin_fields['playlist_when'] = array(
									'display' => 'Playlist Song Options',
									'key' => 'mdjm_plugin_settings',
									'type' => 'textarea',
									'class' => 'all-options',
									'value' => str_replace( ",", "\n", $mdjm_options['playlist_when'] ),
									'text' => '',
									'desc' => 'The options clients can select for when songs are to be played when adding to the playlist. One per line.',
									'section' => 'playlist',
									'page' => 'settings',
									); // playlist_when*/
									
		/*$admin_fields['playlist_close'] = array(
									'display' => 'Close the Playlist',
									'key' => 'mdjm_plugin_settings',
									'type' => 'text',
									'class' => 'small-text',
									'value' => $mdjm_options['playlist_close'],
									'text' => 'Enter 0 to never close',
									'desc' => 'Days before the event should the playlist be closed.',
									'section' => 'playlist',
									'page' => 'settings',
									); // playlist_close*/
									
		/*$admin_fields['uninst_remove_mdjm_templates'] = array(
									'display' => 'Remove Templates?',
									'key' => 'mdjm_plugin_settings',
									'type' => 'checkbox',
									'class' => 'code',
									'value' => $mdjm_options['uninst_remove_mdjm_templates'],
									'text' => '',
									'desc' => 'Do you want to remove the Contract and Email Templates associated with the Mobile DJ Manager plugin? They will be sent to trash, not permanently deleted',
									'section' => 'uninstall',
									'page' => 'settings',
									); // uninst_remove_mdjm_templates
		
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
									); // upload_playlists*/
		
/* PAGES TAB */
		/*$admin_fields['app_home_page'] = array(
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
									
		$admin_fields['payments_page'] = array(
									'display' => 'Payments Page',
									'key' => 'mdjm_plugin_pages',
									'type' => 'custom_dropdown',
									'class' => 'regular-text',
									'value' => $mdjm_options['payments_page'],
									'text' => '<a href="' . admin_url() . 'post-new.php?post_type=page" class="add-new-h2">Add New</a>',
									'desc' => 'Select your website\'s payments page - the one where you added the shortcode <code>[MDJM page=Payments]</code>',
									'custom_args' => array (
														'name' =>  'mdjm_plugin_pages[payments_page]',
														'sort_order' => 'ASC',
														'selected' => $mdjm_options['payments_page'],
														'list_type' => 'page'
														),
									'section' => 'pages',
									'page' => 'pages',
									); // payments_page*/
									
		/*$admin_fields['availability_check_pass_page'] = array(
									'display' => 'Available Redirect Page',
									'key' => 'mdjm_plugin_pages',
									'type' => 'custom_dropdown',
									'class' => 'regular-text',
									'value' => $mdjm_options['availability_check_pass_page'],
									'text' => '<a href="' . admin_url() . 'post-new.php?post_type=page" class="add-new-h2">Add New</a>',
									'desc' => 'Select a page to which users should be directed when an availability check is successful',
									'custom_args' => array (
														'name' =>  'mdjm_plugin_pages[availability_check_pass_page]',
														'sort_order' => 'ASC',
														'selected' => $mdjm_options['availability_check_pass_page'],
														'list_type' => 'page',
														'show_option_none' => 'NO REDIRECT - USE TEXT',
														'option_none_value' => 'text',
														),
									'section' => 'availability',
									'page' => 'pages',
									); // availability_check_pass_page
									
		$admin_fields['availability_check_pass_text'] = array(
									'display' => 'Available Text',
									'key' => 'mdjm_plugin_pages',
									'type' => 'mce_textarea',
									'class' => '',
									'value' => $mdjm_options['availability_check_pass_text'],
									'text' => '',
									'desc' => 'Text to be displayed when you are available - Only displayed if <code>NO REDIRECT - USE TEXT</code> is selected above, unless you are redirecting to an MDJM Contact Form. Valid shortcodes <code>{EVENT_DATE}</code> &amp; <code>{EVENT_DATE_SHORT}</code>',
									'custom_args' => array (
														'name' =>  'mdjm_plugin_pages[availability_check_pass_text]',
														'sort_order' => 'ASC',
														'selected' => $mdjm_options['availability_check_pass_text'],
														'list_type' => '',
														'mce_settings' => array(
																			'textarea_rows' => 6,
																			'media_buttons' => false,
																			'textarea_name' => 'mdjm_plugin_pages[availability_check_pass_text]',
																			'teeny'         => false,
																			),
														),
									'section' => 'availability',
									'page' => 'pages',
									); // availability_check_pass_text
									
		$admin_fields['availability_check_fail_page'] = array(
									'display' => 'Not Available Redirect Page',
									'key' => 'mdjm_plugin_pages',
									'type' => 'custom_dropdown',
									'class' => 'regular-text',
									'value' => $mdjm_options['availability_check_fail_page'],
									'text' => '<a href="' . admin_url() . 'post-new.php?post_type=page" class="add-new-h2">Add New</a>',
									'desc' => 'Select a page to which users should be directed when an availability check is not successful',
									'custom_args' => array (
														'name' =>  'mdjm_plugin_pages[availability_check_fail_page]',
														'sort_order' => 'ASC',
														'selected' => $mdjm_options['availability_check_fail_page'],
														'list_type' => 'page',
														'show_option_none' => 'NO REDIRECT - USE TEXT',
														'option_none_value' => 'text',
														),
									'section' => 'availability',
									'page' => 'pages',
									); // availability_check_fail_page
									
		$admin_fields['availability_check_fail_text'] = array(
									'display' => 'Unavailable Text',
									'key' => 'mdjm_plugin_pages',
									'type' => 'mce_textarea',
									'class' => '',
									'value' => $mdjm_options['availability_check_fail_text'],
									'text' => '',
									'desc' => 'Text to be displayed when you are not available - Only displayed if <code>NO REDIRECT - USE TEXT</code> is selected above. Valid shortcodes <code>{EVENT_DATE}</code> &amp; <code>{EVENT_DATE_SHORT}</code>',
									'custom_args' => array (
														'name' =>  'mdjm_plugin_pages[availability_check_fail_text]',
														'sort_order' => 'ASC',
														'selected' => $mdjm_options['availability_check_fail_text'],
														'list_type' => '',
														'mce_settings' => array(
																			'textarea_rows' => 6,
																			'media_buttons' => false,
																			'textarea_name' => 'mdjm_plugin_pages[availability_check_fail_text]',
																			'teeny'         => false,
																			),
														),
									'section' => 'availability',
									'page' => 'pages',
									); // availability_check_pass_text

/* PERMISSIONS TAB */
		/*$admin_fields['dj_see_wp_dash'] = array(
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
									'display' => 'Disabled Templates for DJ\'s',
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
														'list_type' => 'templates',
														),
									'section' => 'templates',
									'page' => 'permissions',
									); // dj_disable_template*/
									
/* CLIENT DIALOGUE TAB */
		/*$admin_fields['deposit_label'] = array(
									'display' => 'Label for Deposit:',
									'key' => 'mdjm_frontend_text',
									'type' => 'text',
									'class' => 'regular-text',
									'value' => $mdjm_options['deposit_label'],
									'text' => 'Default is <code>Deposit</code>',
									'desc' => 'If you don\'t use the word <code>Deposit</code>, you can change it here. Many prefer the term <code>Booking Fee</code>. Whatever you enter will be visible to all users',
									'section' => 'general',
									'page' => 'client-text',
									); // deposit_label
									
		$admin_fields['balance_label'] = array(
									'display' => 'Label for Balance:',
									'key' => 'mdjm_frontend_text',
									'type' => 'text',
									'class' => 'regular-text',
									'value' => $mdjm_options['balance_label'],
									'text' => 'Default is <code>Balance</code>',
									'desc' => 'If you don\'t use the word <code>Balance</code>, you can change it here. Whatever you enter will be visible to all users',
									'section' => 'general',
									'page' => 'client-text',
									); // balance_label*/
									
		/*$admin_fields['warn_incomplete_profile'] = array(
									'display' => 'Incomplete Profile Warning?',
									'key' => 'mdjm_frontend_text',
									'type' => 'checkbox',
									'class' => 'code',
									'value' => $mdjm_options['warn_incomplete_profile'],
									'text' => '',
									'desc' => 'Display notice to Clients when they login if their Profile is incomplete?',
									'section' => 'client_zone',
									'page' => 'client-text',
									); // warn_incomplete_profile*/
									
		/*$admin_fields['custom_client_text'] = array(
									'display' => 'Enable Customised Text?',
									'key' => 'mdjm_frontend_text',
									'type' => 'checkbox',
									'class' => 'code',
									'value' => $mdjm_options['custom_client_text'],
									'text' => '',
									'desc' => 'Use custom text on Client front end web pages',
									'section' => 'client_zone',
									'page' => 'client-text',
									); // custom_client_text
									
		$admin_fields['not_logged_in'] = array(
									'display' => 'Not Logged In:',
									'key' => 'mdjm_frontend_text',
									'type' => 'mce_textarea',
									'class' => '',
									'value' => $mdjm_options['not_logged_in'],
									'text' => '',
									'desc' => 'Text displayed with login fields if Client is not logged in',
									'custom_args' => array (
														'name' =>  'mdjm_frontend_text[not_logged_in]',
														'sort_order' => '',
														'selected' => '',
														'list_type' => '',
														'mce_settings' => array(
																			'textarea_rows' => 6,
																			'media_buttons' => false,
																			'textarea_name' => 'mdjm_frontend_text[not_logged_in]',
																			'teeny'         => false,
																			),
														),
									'section' => 'client_zone',
									'page' => 'client-text',
									); // not_logged_in
									
		$admin_fields['home_welcome'] = array(
									'display' => 'Welcome Text:',
									'key' => 'mdjm_frontend_text',
									'type' => 'mce_textarea',
									'class' => '',
									'value' => $mdjm_options['home_welcome'],
									'text' => '',
									'desc' => 'Welcome text displayed on the home page',
									'custom_args' => array (
														'name' =>  'mdjm_frontend_text[home_welcome]',
														'sort_order' => '',
														'selected' => '',
														'list_type' => '',
														'mce_settings' => array(
																			'textarea_rows' => 6,
																			'media_buttons' => false,
																			'textarea_name' => 'mdjm_frontend_text[home_welcome]',
																			'teeny'         => false,
																			),
														),
									'section' => 'home_page',
									'page' => 'client-text',
									); // home_welcome
									
		$admin_fields['home_noevents'] = array(
									'display' => 'No Events:',
									'key' => 'mdjm_frontend_text',
									'type' => 'mce_textarea',
									'class' => '',
									'value' => $mdjm_options['home_noevents'],
									'text' => '',
									'desc' => 'Text displayed on client home page if the client has no events in the system',
									'custom_args' => array (
														'name' =>  'mdjm_frontend_text[home_noevents]',
														'sort_order' => '',
														'selected' => '',
														'list_type' => '',
														'mce_settings' => array(
																			'textarea_rows' => 6,
																			'media_buttons' => false,
																			'textarea_name' => 'mdjm_frontend_text[home_noevents]',
																			'teeny'         => false,
																			),
														),
									'section' => 'home_page',
									'page' => 'client-text',
									); // home_noevents
									
		$admin_fields['home_notactive'] = array(
									'display' => 'Event Not Active:',
									'key' => 'mdjm_frontend_text',
									'type' => 'mce_textarea',
									'class' => '',
									'value' => $mdjm_options['home_notactive'],
									'text' => '',
									'desc' => 'Text displayed on client event review screen if the selected event is not active',
									'custom_args' => array (
														'name' =>  'mdjm_frontend_text[home_notactive]',
														'sort_order' => '',
														'selected' => '',
														'list_type' => '',
														'mce_settings' => array(
																			'textarea_rows' => 6,
																			'media_buttons' => false,
																			'textarea_name' => 'mdjm_frontend_text[home_notactive]',
																			'teeny'         => false,
																			),
														),
									'section' => 'home_page',
									'page' => 'client-text',
									); // home_notactive*/
									
		/*$admin_fields['contract_intro'] = array(
									'display' => 'Contract Sign Intro:',
									'key' => 'mdjm_frontend_text',
									'type' => 'mce_textarea',
									'class' => '',
									'value' => $mdjm_options['contract_intro'],
									'text' => '',
									'desc' => 'Text displayed as intro on contract signing page',
									'custom_args' => array (
														'name' =>  'mdjm_frontend_text[contract_intro]',
														'sort_order' => '',
														'selected' => '',
														'list_type' => '',
														'mce_settings' => array(
																			'textarea_rows' => 6,
																			'media_buttons' => false,
																			'textarea_name' => 'mdjm_frontend_text[contract_intro]',
																			'teeny'         => false,
																			),
														),
									'section' => 'contract_page',
									'page' => 'client-text',
									); // contract_intro
									
		$admin_fields['contract_not_ready'] = array(
									'display' => 'Contract Not Ready:',
									'key' => 'mdjm_frontend_text',
									'type' => 'mce_textarea',
									'class' => '',
									'value' => $mdjm_options['contract_not_ready'],
									'text' => '',
									'desc' => 'Text displayed if Contract is not ready for signing (i.e. Event Status is not <code>Awaiting Contract</code>',
									'custom_args' => array (
														'name' =>  'mdjm_frontend_text[contract_not_ready]',
														'sort_order' => '',
														'selected' => '',
														'list_type' => '',
														'mce_settings' => array(
																			'textarea_rows' => 6,
																			'media_buttons' => false,
																			'textarea_name' => 'mdjm_frontend_text[contract_not_ready]',
																			'teeny'         => false,
																			),
														),
									'section' => 'contract_page',
									'page' => 'client-text',
									); // contract_not_ready
		
		$admin_fields['contract_signed'] = array(
									'display' => 'Contract Already Signed:',
									'key' => 'mdjm_frontend_text',
									'type' => 'mce_textarea',
									'class' => '',
									'value' => $mdjm_options['contract_signed'],
									'text' => '',
									'desc' => 'Text displayed if the contract is already signed',
									'custom_args' => array (
														'name' =>  'mdjm_frontend_text[contract_signed]',
														'sort_order' => '',
														'selected' => '',
														'list_type' => '',
														'mce_settings' => array(
																			'textarea_rows' => 6,
																			'media_buttons' => false,
																			'textarea_name' => 'mdjm_frontend_text[contract_signed]',
																			'teeny'         => false,
																			),
														),
									'section' => 'contract_page',
									'page' => 'client-text',
									); // contract_signed
									
		$admin_fields['contract_sign_success'] = array(
									'display' => 'Contract Sign Success:',
									'key' => 'mdjm_frontend_text',
									'type' => 'mce_textarea',
									'class' => '',
									'value' => $mdjm_options['contract_sign_success'],
									'text' => '',
									'desc' => 'Text displayed after successfull signing of contract',
									'custom_args' => array (
														'name' =>  'mdjm_frontend_text[contract_sign_success]',
														'sort_order' => '',
														'selected' => '',
														'list_type' => '',
														'mce_settings' => array(
																			'textarea_rows' => 6,
																			'media_buttons' => false,
																			'textarea_name' => 'mdjm_frontend_text[contract_sign_success]',
																			'teeny'         => false,
																			),
														),
									'section' => 'contract_page',
									'page' => 'client-text',
									); // contract_signed*/
									
		/*$admin_fields['playlist_welcome'] = array(
									'display' => 'Playlist Welcome:',
									'key' => 'mdjm_frontend_text',
									'type' => 'mce_textarea',
									'class' => '',
									'value' => $mdjm_options['playlist_welcome'],
									'text' => '',
									'desc' => 'Welcome text displayed to logged in users on the Playlist page',
									'custom_args' => array (
														'name' =>  'mdjm_frontend_text[playlist_welcome]',
														'sort_order' => '',
														'selected' => '',
														'list_type' => '',
														'mce_settings' => array(
																			'textarea_rows' => 6,
																			'media_buttons' => false,
																			'textarea_name' => 'mdjm_frontend_text[playlist_welcome]',
																			'teeny'         => false,
																			),
														),
									'section' => 'playlist_page',
									'page' => 'client-text',
									); // playlist_welcome
									
		$admin_fields['playlist_intro'] = array(
									'display' => 'Playlist Intro:',
									'key' => 'mdjm_frontend_text',
									'type' => 'mce_textarea',
									'class' => '',
									'value' => $mdjm_options['playlist_intro'],
									'text' => '',
									'desc' => 'Introduction text displayed on playlist page to logged in users',
									'custom_args' => array (
														'name' =>  'mdjm_frontend_text[playlist_intro]',
														'sort_order' => '',
														'selected' => '',
														'list_type' => '',
														'mce_settings' => array(
																			'textarea_rows' => 6,
																			'media_buttons' => false,
																			'textarea_name' => 'mdjm_frontend_text[playlist_intro]',
																			'teeny'         => false,
																			),
														),
									'section' => 'playlist_page',
									'page' => 'client-text',
									); // playlist_intro
									
		$admin_fields['playlist_edit'] = array(
									'display' => 'Editing Playlist:',
									'key' => 'mdjm_frontend_text',
									'type' => 'mce_textarea',
									'class' => '',
									'value' => $mdjm_options['playlist_edit'],
									'text' => '',
									'desc' => 'Text displayed to logged in user when editing an event playlist',
									'custom_args' => array (
														'name' =>  'mdjm_frontend_text[playlist_edit]',
														'sort_order' => '',
														'selected' => '',
														'list_type' => '',
														'mce_settings' => array(
																			'textarea_rows' => 6,
																			'media_buttons' => false,
																			'textarea_name' => 'mdjm_frontend_text[playlist_edit]',
																			'teeny'         => false,
																			),
														),
									'section' => 'playlist_page',
									'page' => 'client-text',
									); // playlist_edit
									
		$admin_fields['playlist_closed'] = array(
									'display' => 'Playlist Closed:',
									'key' => 'mdjm_frontend_text',
									'type' => 'mce_textarea',
									'class' => '',
									'value' => $mdjm_options['playlist_closed'],
									'text' => '',
									'desc' => 'Text displayed to logged in user when playlist is closed',
									'custom_args' => array (
														'name' =>  'mdjm_frontend_text[playlist_closed]',
														'sort_order' => '',
														'selected' => '',
														'list_type' => '',
														'mce_settings' => array(
																			'textarea_rows' => 6,
																			'media_buttons' => false,
																			'textarea_name' => 'mdjm_frontend_text[playlist_closed]',
																			'teeny'         => false,
																			),
														),
									'section' => 'playlist_page',
									'page' => 'client-text',
									); // playlist_closed
									
		$admin_fields['playlist_noevent'] = array(
									'display' => 'No Active Events:',
									'key' => 'mdjm_frontend_text',
									'type' => 'mce_textarea',
									'class' => '',
									'value' => $mdjm_options['playlist_noevent'],
									'text' => '',
									'desc' => 'Text displayed to logged in users who have no active events',
									'custom_args' => array (
														'name' =>  'mdjm_frontend_text[playlist_noevent]',
														'sort_order' => '',
														'selected' => '',
														'list_type' => '',
														'mce_settings' => array(
																			'textarea_rows' => 6,
																			'media_buttons' => false,
																			'textarea_name' => 'mdjm_frontend_text[playlist_noevent]',
																			'teeny'         => false,
																			),
														),
									'section' => 'playlist_page',
									'page' => 'client-text',
									); // playlist_noevent
									
		$admin_fields['playlist_guest_welcome'] = array(
									'display' => 'Guest Welcome:',
									'key' => 'mdjm_frontend_text',
									'type' => 'mce_textarea',
									'class' => '',
									'value' => $mdjm_options['playlist_guest_welcome'],
									'text' => '',
									'desc' => 'Welcome text displayed to guests',
									'custom_args' => array (
														'name' =>  'mdjm_frontend_text[playlist_guest_welcome]',
														'sort_order' => '',
														'selected' => '',
														'list_type' => '',
														'mce_settings' => array(
																			'textarea_rows' => 6,
																			'media_buttons' => false,
																			'textarea_name' => 'mdjm_frontend_text[playlist_guest_welcome]',
																			'teeny'         => false,
																			),
														),
									'section' => 'playlist_page',
									'page' => 'client-text',
									); // playlist_guest_welcome
									
		$admin_fields['playlist_guest_intro'] = array(
									'display' => 'Guest Intro:',
									'key' => 'mdjm_frontend_text',
									'type' => 'mce_textarea',
									'class' => '',
									'value' => $mdjm_options['playlist_guest_intro'],
									'text' => '',
									'desc' => 'Introduction text displayed on playlist page to guests',
									'custom_args' => array (
														'name' =>  'mdjm_frontend_text[playlist_guest_intro]',
														'sort_order' => '',
														'selected' => '',
														'list_type' => '',
														'mce_settings' => array(
																			'textarea_rows' => 6,
																			'media_buttons' => false,
																			'textarea_name' => 'mdjm_frontend_text[playlist_guest_intro]',
																			'teeny'         => false,
																			),
														),
									'section' => 'playlist_page',
									'page' => 'client-text',
									); // playlist_guest_intro
									
		$admin_fields['playlist_guest_closed'] = array(
									'display' => 'Guest Playlist Closed:',
									'key' => 'mdjm_frontend_text',
									'type' => 'mce_textarea',
									'class' => '',
									'value' => $mdjm_options['playlist_guest_closed'],
									'text' => '',
									'desc' => 'Text displayed to guests when playlist is closed',
									'custom_args' => array (
														'name' =>  'mdjm_frontend_text[playlist_guest_closed]',
														'sort_order' => '',
														'selected' => '',
														'list_type' => '',
														'mce_settings' => array(
																			'textarea_rows' => 6,
																			'media_buttons' => false,
																			'textarea_name' => 'mdjm_frontend_text[playlist_guest_closed]',
																			'teeny'         => false,
																			),
														),
									'section' => 'playlist_page',
									'page' => 'client-text',
									); // playlist_guest_closed*/
									
		/*$admin_fields['payment_welcome'] = array(
									'display' => 'Payment Welcome:',
									'key' => 'mdjm_frontend_text',
									'type' => 'mce_textarea',
									'class' => '',
									'value' => $mdjm_options['payment_welcome'],
									'text' => '',
									'desc' => 'Welcome text displayed to Clients when they arrive at the Payments page',
									'custom_args' => array (
														'name' =>  'mdjm_frontend_text[payment_welcome]',
														'sort_order' => '',
														'selected' => '',
														'list_type' => '',
														'mce_settings' => array(
																			'textarea_rows' => 6,
																			'media_buttons' => false,
																			'textarea_name' => 'mdjm_frontend_text[payment_welcome]',
																			'teeny'         => false,
																			),
														),
									'section' => 'payment_page',
									'page' => 'client-text',
									); // payment_welcome
									
		$admin_fields['payment_intro'] = array(
									'display' => 'Payment Intro:',
									'key' => 'mdjm_frontend_text',
									'type' => 'mce_textarea',
									'class' => '',
									'value' => $mdjm_options['payment_intro'],
									'text' => '',
									'desc' => 'Intro text displayed to Clients when they arrive at the Payments page',
									'custom_args' => array (
														'name' =>  'mdjm_frontend_text[payment_intro]',
														'sort_order' => '',
														'selected' => '',
														'list_type' => '',
														'mce_settings' => array(
																			'textarea_rows' => 6,
																			'media_buttons' => false,
																			'textarea_name' => 'mdjm_frontend_text[payment_intro]',
																			'teeny'         => false,
																			),
														),
									'section' => 'payment_page',
									'page' => 'client-text',
									); // payment_intro
									
		$admin_fields['payment_complete'] = array(
									'display' => 'Payment Completed:',
									'key' => 'mdjm_frontend_text',
									'type' => 'mce_textarea',
									'class' => '',
									'value' => $mdjm_options['payment_complete'],
									'text' => '',
									'desc' => 'Text displayed to Clients when they complete payment and return to your payments page from PayPal',
									'custom_args' => array (
														'name' =>  'mdjm_frontend_text[payment_complete]',
														'sort_order' => '',
														'selected' => '',
														'list_type' => '',
														'mce_settings' => array(
																			'textarea_rows' => 6,
																			'media_buttons' => false,
																			'textarea_name' => 'mdjm_frontend_text[payment_complete]',
																			'teeny'         => false,
																			),
														),
									'section' => 'payment_page',
									'page' => 'client-text',
									); // payment_complete
									
		$admin_fields['payment_cancel'] = array(
									'display' => 'Payment Cancelled:',
									'key' => 'mdjm_frontend_text',
									'type' => 'mce_textarea',
									'class' => '',
									'value' => $mdjm_options['payment_cancel'],
									'text' => '',
									'desc' => 'Text displayed to Clients when they cancel their payment and return to your payments page from PayPal',
									'custom_args' => array (
														'name' =>  'mdjm_frontend_text[payment_cancel]',
														'sort_order' => '',
														'selected' => '',
														'list_type' => '',
														'mce_settings' => array(
																			'textarea_rows' => 6,
																			'media_buttons' => false,
																			'textarea_name' => 'mdjm_frontend_text[payment_cancel]',
																			'teeny'         => false,
																			),
														),
									'section' => 'payment_page',
									'page' => 'client-text',
									); // payment_cancel
									
		$admin_fields['payment_not_due'] = array(
									'display' => 'Payment Not Due:',
									'key' => 'mdjm_frontend_text',
									'type' => 'mce_textarea',
									'class' => '',
									'value' => $mdjm_options['payment_not_due'],
									'text' => '',
									'desc' => 'Text displayed to clients when they land on the payments page but the no payments are due',
									'custom_args' => array (
														'name' =>  'mdjm_frontend_text[payment_not_due]',
														'sort_order' => '',
														'selected' => '',
														'list_type' => '',
														'mce_settings' => array(
																			'textarea_rows' => 6,
																			'media_buttons' => false,
																			'textarea_name' => 'mdjm_frontend_text[payment_not_due]',
																			'teeny'         => false,
																			),
														),
									'section' => 'payment_page',
									'page' => 'client-text',
									); // payment_not_due
									
		$admin_fields['payment_noevent'] = array(
									'display' => 'Payment No Event:',
									'key' => 'mdjm_frontend_text',
									'type' => 'mce_textarea',
									'class' => '',
									'value' => $mdjm_options['payment_noevent'],
									'text' => '',
									'desc' => 'Text displayed to clients when they land on the payments page without an event (unlikely)',
									'custom_args' => array (
														'name' =>  'mdjm_frontend_text[payment_noevent]',
														'sort_order' => '',
														'selected' => '',
														'list_type' => '',
														'mce_settings' => array(
																			'textarea_rows' => 6,
																			'media_buttons' => false,
																			'textarea_name' => 'mdjm_frontend_text[payment_noevent]',
																			'teeny'         => false,
																			),
														),
									'section' => 'payment_page',
									'page' => 'client-text',
									); // payment_noevent
									
		$admin_fields['payment_noaccess'] = array(
									'display' => 'Payment No Permission:',
									'key' => 'mdjm_frontend_text',
									'type' => 'mce_textarea',
									'class' => '',
									'value' => $mdjm_options['payment_noaccess'],
									'text' => '',
									'desc' => 'Text displayed to clients when they land on the payments page but the specified event is not theirs (very unlikely)',
									'custom_args' => array (
														'name' =>  'mdjm_frontend_text[payment_noaccess]',
														'sort_order' => '',
														'selected' => '',
														'list_type' => '',
														'mce_settings' => array(
																			'textarea_rows' => 6,
																			'media_buttons' => false,
																			'textarea_name' => 'mdjm_frontend_text[payment_noaccess]',
																			'teeny'         => false,
																			),
														),
									'section' => 'payment_page',
									'page' => 'client-text',
									); // payment_noaccess*/
									
/* PAYMENTS TAB */

		/*$admin_fields['pp_cfm_template'] = array(
									'display' => 'Payment Received Template:',
									'key' => 'mdjm_pp_options',
									'type' => 'custom_dropdown',
									'class' => 'regular-text',
									'value' => $mdjm_options['pp_cfm_template'],
									'text' => '<a href="' . admin_url() . 'post-new.php?post_type=email_template" class="add-new-h2">Add New</a>',
									'desc' => 'Select an email template to be sent to clients when confirming receipt of a payment. <a href="www.mydjplanner.co.uk/shortcodes/" target="_blank">Shortcodes</a> can be used.',
									'custom_args' => array (
														'name' =>  'mdjm_pp_options[pp_cfm_template]',
														'sort_order' => 'ASC',
														'selected' => $mdjm_options['pp_cfm_template'],
														'list_type' => 'email_template'
														),
									'section' => 'payments',
									'page' => 'payments',
									); // pp_cfm_template
									
		$admin_fields['pp_manual_cfm_template'] = array(
									'display' => 'Manual Payment Template:',
									'key' => 'mdjm_pp_options',
									'type' => 'custom_dropdown',
									'class' => 'regular-text',
									'value' => $mdjm_options['pp_manual_cfm_template'],
									'text' => '<a href="' . admin_url() . 'post-new.php?post_type=email_template" class="add-new-h2">Add New</a>',
									'desc' => 'Select an email template to be sent to clients when you manually mark an event payment as received. <a href="www.mydjplanner.co.uk/shortcodes/" target="_blank">Shortcodes</a> can be used.',
									'custom_args' => array (
														'name' =>  'mdjm_pp_options[pp_manual_cfm_template]',
														'sort_order' => 'ASC',
														'selected' => $mdjm_options['pp_manual_cfm_template'],
														'list_type' => 'email_template'
														),
									'section' => 'payments',
									'page' => 'payments',
									); // pp_manual_cfm_template*/
		
		/*$trans_sources = get_transaction_source();
		foreach( $trans_sources as $source )	{
			$sources[$source] = $source;	
		}
		$admin_fields['pp_default_method'] = array(
									'display' => 'Default Payment Type:',
									'key' => 'mdjm_pp_options',
									'type' => 'custom_dropdown',
									'class' => 'regular-text',
									'value' => $mdjm_options['pp_default_method'],
									'text' => '',
									'desc' => 'What is the default method of payment? i.e. if you select an event ' . MDJM_BALANCE_LABEL . ' as paid how should we log it?',
									'custom_args' => array (
														'name' =>  'mdjm_pp_options[pp_default_method]',
														'sort_order' => '',
														'selected' => $mdjm_options['pp_default_method'],
														'list_type' => 'defined',
														'list_values' => $sources,
														),
									'section' => 'payments',
									'page' => 'payments',
									); // pp_default_method
									
		$admin_fields['pp_form_layout'] = array(
									'display' => 'Form Layout:',
									'key' => 'mdjm_pp_options',
									'type' => 'custom_dropdown',
									'class' => 'regular-text',
									'value' => $mdjm_options['pp_form_layout'],
									'text' => '',
									'desc' => 'How do you want the payment form displayed on your page?',
									'custom_args' => array (
														'name' =>  'mdjm_pp_options[pp_form_layout]',
														'sort_order' => '',
														'selected' => $mdjm_options['pp_form_layout'],
														'list_type' => 'defined',
														'list_values' => array( 'horizontal' => 'Horizontal',
																				'vertical' => 'Vertical',
																			),
														),
									'section' => 'payments',
									'page' => 'payments',
									); // pp_form_layout
									
		$admin_fields['pp_label'] = array(
									'display' => 'Payment Label:',
									'key' => 'mdjm_pp_options',
									'type' => 'text',
									'class' => 'regular-text',
									'value' => $mdjm_options['pp_label'],
									'text' => 'Default is <code>Make a Payment Towards:</code>',
									'desc' => 'Display name of the label shown to users to select the payment they wish to make',
									'section' => 'payments',
									'page' => 'payments',
									); // pp_label
									
		$admin_fields['pp_enable_tax'] = array(
									'display' => 'Enable Taxes?',
									'key' => 'mdjm_pp_options',
									'type' => 'checkbox',
									'class' => 'code',
									'value' => $mdjm_options['pp_enable_tax'],
									'text' => '',
									'desc' => 'Enable if you need to add taxes to online payments',
									'section' => 'payments',
									'page' => 'payments',
									); // pp_enable_tax
									
		$admin_fields['pp_tax_type'] = array(
									'display' => 'Apply Tax As:',
									'key' => 'mdjm_pp_options',
									'type' => 'custom_dropdown',
									'class' => 'regular-text',
									'value' => $mdjm_options['pp_tax_type'],
									'text' => '',
									'desc' => 'How do you apply tax?',
									'custom_args' => array (
														'name' =>  'mdjm_pp_options[pp_tax_type]',
														'sort_order' => '',
														'selected' => $mdjm_options['pp_tax_type'],
														'list_type' => 'defined',
														'list_values' => array( 'percentage' => '% of total',
																				'fixed' => 'Fixed rate',
																			),
														),
									'section' => 'payments',
									'page' => 'payments',
									); // pp_tax_type
									
		$admin_fields['pp_tax_rate'] = array(
									'display' => 'Tax Rate:',
									'key' => 'mdjm_pp_options',
									'type' => 'text',
									'class' => 'small-text',
									'value' => $mdjm_options['pp_tax_rate'],
									'text' => 'Do not enter a currency or percentage symbol',
									'desc' => 'If you apply tax based on a fixed percentage (i.e. VAT) enter the value (i.e 20). For fixed rates, enter the amount in the format 0.00. Taxes will only be applied during checkout',
									'section' => 'payments',
									'page' => 'payments',
									); // pp_tax_rate
		
		$admin_fields['pp_payment_sources'] = array(
									'display' => 'Payment Types:',
									'key' => 'mdjm_pp_options',
									'type' => 'textarea',
									'class' => 'all-options',
									'value' => $mdjm_options['pp_payment_sources'],
									'text' => '',
									'desc' => 'Enter methods of payment. First entry will be the default',
									'section' => 'payments',
									'page' => 'payments',
									); // pp_payment_sources*/
																		
		/*$admin_fields['pp_enable'] = array(
									'display' => 'Enable PayPal?',
									'key' => 'mdjm_pp_options',
									'type' => 'checkbox',
									'class' => 'code',
									'value' => $mdjm_options['pp_enable'],
									'text' => '',
									'desc' => 'Enables the use of PayPal standard for client payment collections',
									'section' => 'paypal',
									'page' => 'payments',
									); // pp_enable
									
		$admin_fields['pp_email'] = array(
									'display' => 'PayPal Email:',
									'key' => 'mdjm_pp_options',
									'type' => 'text',
									'class' => 'regular-text',
									'value' => $mdjm_options['pp_email'],
									'text' => '',
									'desc' => 'Your registered PayPal email address is needed before you can take payments via your website',
									'section' => 'paypal',
									'page' => 'payments',
									); // pp_email
									
		$admin_fields['pp_redirect'] = array(
									'display' => 'Redirect Successful Payment To:',
									'key' => 'mdjm_pp_options',
									'type' => 'custom_dropdown',
									'class' => 'regular-text',
									'value' => $mdjm_options['pp_redirect'],
									'text' => '<code>Current Page</code> is the page the Client initiated the payment from',
									'desc' => 'Where do you want your Client redirected to once Payment has completed?',
									'custom_args' => array (
														'name' =>  'mdjm_pp_options[pp_redirect]',
														'sort_order' => 'ASC',
														'selected' => $mdjm_options['pp_redirect'],
														'list_type' => 'page',
														'show_option_none' => 'Current Page',
														'option_none_value' => $mdjm_options['payments_page'],
														'id' => 'pp_redirect',
														),
									'section' => 'paypal',
									'page' => 'payments',
									); // pp_redirect
									
		$admin_fields['pp_cancel'] = array(
									'display' => 'Redirect Cancelled Payment To:',
									'key' => 'mdjm_pp_options',
									'type' => 'custom_dropdown',
									'class' => 'regular-text',
									'value' => $mdjm_options['pp_cancel'],
									'text' => '<code>Current Page</code> is the page the Client initiated the payment from',
									'desc' => 'Where do you want your Client redirected to if they cancel the payment?',
									'custom_args' => array (
														'name' =>  'mdjm_pp_options[pp_cancel]',
														'sort_order' => 'ASC',
														'selected' => $mdjm_options['pp_cancel'],
														'list_type' => 'page',
														'show_option_none' => 'Current Page',
														'option_none_value' => $mdjm_options['payments_page'],
														'id' => 'pp_cancel',
														),
									'section' => 'paypal',
									'page' => 'payments',
									); // pp_cancel
									
		$admin_fields['pp_button'] = array(
									'display' => 'Payment Button:',
									'key' => 'mdjm_pp_options',
									'type' => 'pp_button_radio',
									'class' => '',
									'value' => $mdjm_options['pp_button'],
									'text' => '',
									'desc' => '',
									'custom_args' => array (
														'name' 	  => 'mdjm_pp_options[pp_button]',
														'selected'  => $mdjm_options['pp_button'],
														'values'	=> array(
																			'btn_paynowCC_LG.gif',
																			'btn_paynow_LG.gif',
																			'btn_paynow_SM.gif',
																			),
														),
									'section' => 'paypal',
									'page' => 'payments',
									); // pp_button
									
		$admin_fields['pp_sandbox'] = array(
									'display' => 'PayPal Sandbox?',
									'key' => 'mdjm_pp_options',
									'type' => 'checkbox',
									'class' => 'code',
									'value' => $mdjm_options['pp_sandbox'],
									'text' => '',
									'desc' => 'Enable only to test payments. You can sign up for a developer account <a href="https://developer.paypal.com/" target="_blank">here</a>.',
									'section' => 'paypal',
									'page' => 'payments',
									); // pp_sandbox
									
		$admin_fields['pp_sandbox_email'] = array(
									'display' => 'PayPal Sandbox Email:',
									'key' => 'mdjm_pp_options',
									'type' => 'text',
									'class' => 'regular-text',
									'value' => $mdjm_options['pp_sandbox_email'],
									'text' => '',
									'desc' => 'If using PayPal Sandbox, enter your sandbox "Facilitator" email here. If not set, your normal PayPal email will be used',
									'section' => 'paypal',
									'page' => 'payments',
									); // pp_sandbox_email
									
		$admin_fields['pp_debug'] = array(
									'display' => 'Debug?',
									'key' => 'mdjm_pp_options',
									'type' => 'checkbox',
									'class' => 'code',
									'value' => $mdjm_options['pp_debug'],
									'text' => 'Enable logging',
									'desc' => 'Enable to capture logs for PayPal - will be stored at <code>' . WPMDJM_PLUGIN_DIR . '/mdjm-pp-ipn-debug.log</code>',
									'section' => 'paypal',
									'page' => 'payments',
									); // pp_debug
									
		$admin_fields['pp_receiver'] = array(
									'display' => 'Receiver Email Address:',
									'key' => 'mdjm_pp_options',
									'type' => 'text',
									'class' => 'regular-text',
									'value' => $mdjm_options['pp_receiver'],
									'text' => '',
									'desc' => 'This address is used for <a href="https://www.paypal.com/uk/cgi-bin/webscr?cmd=p/acc/ipn-info-outside" target="_blank" title="Instant Payment Notification (IPN)">PayPal IPN validation</a>. It should be your <strong><code>primary</code></strong> PayPal email address',
									'section' => 'paypal_adv',
									'page' => 'payments',
									); // pp_receiver
																		
		$admin_fields['pp_checkout_style'] = array(
									'display' => 'Checkout Page Style:',
									'key' => 'mdjm_pp_options',
									'type' => 'text',
									'class' => 'regular-text',
									'value' => $mdjm_options['pp_checkout_style'],
									'text' => '',
									'desc' => 'If you have created a custom <a href="https://www.paypal.com/customize" target="_blank" title="PayPal\'s Custom Payment Pages: An Overview">PayPal Checkout Page</a>, enter it\'s ID here to use it',
									'section' => 'paypal_adv',
									'page' => 'payments',
									); // pp_checkout_style*/
		/*
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
		add_settings_section( 'mdjm_availability_settings',
							  'Availability Checker<hr />',
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
		add_settings_section( 'mdjm_client_zone_settings',
							  WPMDJM_APP_NAME . ' <hr />',
							  'f_mdjm_desc',
							  'mdjm-client-text'
							);
		add_settings_section( 'mdjm_home_page_settings',
							  'Home Page <hr />',
							  'f_mdjm_desc',
							  'mdjm-client-text'
							);
		add_settings_section( 'mdjm_contract_page_settings',
							  'Contract Page <hr />',
							  'f_mdjm_desc',
							  'mdjm-client-text'
							  );
		add_settings_section( 'mdjm_playlist_page_settings',
							  'Playlist Page <hr />',
							  'f_mdjm_desc',
							  'mdjm-client-text'
							);
		add_settings_section( 'mdjm_payment_page_settings',
							  'Payment Page <hr />',
							  'f_mdjm_desc',
							  'mdjm-client-text'
							);
		add_settings_section( 'mdjm_payments_settings',
							  '',
							  'f_mdjm_desc',
							  'mdjm-payments'
							);
		add_settings_section( 'mdjm_paypal_settings',
							  'PayPal Settings <hr />',
							  'f_mdjm_desc',
							  'mdjm-payments'
							);
		add_settings_section( 'mdjm_paypal_adv_settings',
							  'PayPal Advanced Settings <hr />',
							  'f_mdjm_desc',
							  'mdjm-payments'
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
		}*/ // foreach
	} // f_mdjm_settings_init
	
	//add_action( 'admin_init', 'f_mdjm_settings_init' );

/**************************************************************
-	Callbacks for sections, fields & validation
**************************************************************/	
	/*function f_mdjm_desc()	{
		// Intentionally blank
	} // f_mdjm_desc*/

/* Validate the fields */	
	/*function f_mdjm_validate_settings( $input )	{
		$valid = array();
		/* Check for incomplete fields */
		/*if( !isset( $input['company_name'] ) || empty( $input['company_name'] ) )	{
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
	/*function f_mdjm_general_settings_callback( $args )	{
		global $mdjm_options;
		if( isset( $args['type'] ) && $args['type'] == 'custom_dropdown' )	{
			if( $args['custom_args']['list_type'] == 'page' )	{
				wp_dropdown_pages( $args['custom_args'] );
			}
			elseif( $args['custom_args']['list_type'] == 'contract' )	{
				echo '<select name="' . $args['key'] . '[' . $args['field'] . ']" id="' . $args['field'] . '">';
				$contract_args = array(
									'posts_per_page' => -1,
									'post_type' => 'contract',
									'post_status' => 'publish',
									'orderby' => 'name',
									'order' => 'ASC',
									);
				$contract_templates = get_posts( $contract_args );
				foreach( $contract_templates as $template )	{
					echo '<option value="' . $template->ID . '"';
					if( $mdjm_options['default_contract'] == $template->ID )
						echo ' selected="selected"';	
					echo '>' . get_the_title( $template->ID ) . '</option>' . "\n";
				}
				echo '</select>';
			}
			elseif( $args['custom_args']['list_type'] == 'email_template' )	{
				echo '<select name="' . $args['key'] . '[' . $args['field'] . ']" id="' . $args['field'] . '">';
				
				/* -- This is for Manual Payment Template -- */
				/*if( $args['field'] == 'pp_manual_cfm_template' )	{
					echo '<option value="0"';
					if( empty( $mdjm_options[$args['field']] ) || $mdjm_options[$args['field']] == 0 )
						echo ' selected="selected"';
					echo '>Do Not Email</option>' . "\r\n";
					
					echo '<option value="' . $mdjm_options['pp_cfm_template'] . '">Use Payment Received Template</option>' . "\r\n";
				}
				
				$email_args = array(
									'posts_per_page' => -1,
									'post_type' => 'email_template',
									'post_status' => 'publish',
									'orderby' => 'name',
									'order' => 'ASC',
									);
				$email_templates = get_posts( $email_args );
				foreach( $email_templates as $email_template )	{
					echo '<option value="' . $email_template->ID . '"';
					if( $mdjm_options[$args['field']] == $email_template->ID )
						echo ' selected="selected"';
						
					echo '>' . get_the_title( $email_template->ID ) . '</option>' . "\n";
				}
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
			elseif( $args['custom_args']['list_type'] == 'templates' )		{
				echo '<select size="8" name="' . $args['key'] . '[' . $args['field'] . '][]" id="' . $args['field'] . '" multiple="multiple">';
				$email_args = array(
									'post_type' => 'email_template',
									'orderby' => 'name',
									'order' => 'ASC',
									);
				$email_query = new WP_Query( $email_args );
				if ( $email_query->have_posts() ) {
					?><option value="email_templates" disabled>--- EMAIL TEMPLATES ---</option><?php
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
				$contract_args = array(
									'post_type' => 'contract',
									'orderby' => 'name',
									'order' => 'ASC',
									);
				$contract_query = new WP_Query( $contract_args );
				if ( $contract_query->have_posts() ) {
					?><option value="contracts" disabled>--- CONTRACT TEMPLATES ---</option><?php
					while ( $contract_query->have_posts() ) {
						$contract_query->the_post();
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
		elseif( $args['type'] == 'mce_textarea' )	{
			wp_editor( $args['value'], $args['field'], $args['custom_args']['mce_settings'] );
		}
		elseif( $args['type'] == 'checkbox' )	{
			if( !do_reg_check( 'check' ) && $args['field'] == 'show_credits' )	{
				echo '<input name="' . $args['key'] . '[' . $args['field'] . ']" id="' . $args['field'] . '" type="' . $args['type'] . '" value="Y" class="' . $args['class']  . '" ' . 
				'checked="checked" disabled="disabled" />' . "\r\n" . 
				'<input type="hidden" name="' . $args['key'] . '[' . $args['field'] . ']" id="' . $args['field'] . '" value="Y" />' . "\r\n";
			}
			else	{
				echo '<input name="' . $args['key'] . '[' . $args['field'] . ']" id="' . $args['field'] . '" type="' . $args['type'] . '" value="Y" class="' . $args['class']  . '" ' . 
				checked( $args['value'], 'Y', false ) . ' />';
			}
		}
		elseif( $args['type'] == 'pp_button_radio' )	{
			$i = 0;
			foreach( $args['custom_args']['values'] as $radio )	{
				echo '<label>' . "\n";
				echo '<input type="radio" name="' . $args['key'] . '[' . $args['field'] . ']" value="' . $radio . '" id="' . $radio . '" ' . checked( $args['value'], $radio, false ) . ' />' . "\n";
				echo '<img src="https://www.paypalobjects.com/en_GB/i/btn/' . $radio . '">';
				echo '</label>' . "\n";
				$i++;
				if( $i != count( $args['custom_args']['values'] ) )	{
					echo '<br />' . "\n";	
				}
			}
		}
		else	{
			echo '<input name="' . $args['key'] . '[' . $args['field'] . ']" id="' . $args['field'] . '" type="' . $args['type'] . '" class="' . $args['class'] . '" value="' . $args['value'] . '" />';
		}
		if( isset( $args['text'] ) && !empty( $args['text'] ) ) echo '<label for="' . $args['field'] . '"> ' . $args['text'] . '</label>';
		if( !do_reg_check( 'check' ) && $args['field'] == 'show_credits' ) echo '<label for="' . $args['field'] . '"> This setting cannot be changed as your license has expired</label>';
		if( isset( $args['desc'] ) && !empty( $args['desc'] ) ) echo '<p class="description">' . $args['desc'] . '</p>';
	} // f_mdjm_general_settings_callback

/*
* do_reg_check
* 04/10/2014
* @since 0.8
* Checks license status and returns the result
*/	
	// This function is deprecated //
	function do_reg_check( $action )	{
		global $mdjm;
		
		$mdjm->debug_logger( 'DEPRECATED function in use ' . __FUNCTION__, true );
		
		return $mdjm->_mdjm_validation( $action );
	}	
?>
