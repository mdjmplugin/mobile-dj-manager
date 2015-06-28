<?php

	/*
	 * Full list of shortcodes available
	 * within the application
	 *
	 *
	 */					
	$all_shortcodes = array(
					'{ADMIN_URL}', '{APPLICATION_HOME}', '{APPLICATION_NAME}', '{COMPANY_NAME}',
					'{CONTACT_PAGE}', '{DDMMYYYY}', '{WEBSITE_URL}',
				/* -- Client -- */
					'{CLIENT_FIRSTNAME}', '{CLIENT_LASTNAME}', '{CLIENT_FULLNAME}', '{CLIENT_FULL_ADDRESS}',
					'{CLIENT_EMAIL}', '{CLIENT_PRIMARY_PHONE}', '{CLIENT_USERNAME}', '{CLIENT_PASSWORD}',
				/* -- Event, DJ & Venue -- */
					'{ADMIN_NOTES}', '{BALANCE}', '{CONTRACT_DATE}', '{CONTRACT_ID}', '{CONTRACT_URL}',
					'{DEPOSIT}', '{DEPOSIT_STATUS}', '{DJ_EMAIL}', '{DJ_FIRSTNAME}', '{DJ_FULLNAME}',
					'{DJ_NOTES}', '{DJ_PRIMARY_PHONE}', '{DJ_SETUP_DATE}', '{DJ_SETUP_TIME}', '{END_TIME}',
					'{EVENT_DATE}', '{EVENT_DATE_SHORT}', '{EVENT_DESCRIPTION}', '{EVENT_TYPE}', '{PAYMENT_AMOUNT}',
					'{PAYMENT_DATE}', '{PAYMENT_FOR}', '{PAYMENT_URL}', '{PLAYLIST_CLOSE}', '{PLAYLIST_URL}',
					'{START_TIME}', '{TOTAL_COST}', '{VENUE}', '{VENUE_CONTACT}', '{VENUE_DETAILS}', '{VENUE_EMAIL}',
					'{VENUE_FULL_ADDRESS}', '{VENUE_NOTES}', '{VENUE_TELEPHONE}',
					);
					
	$payment_sources = get_transaction_source();
	foreach( $payment_sources as $source )	{
		$sources[$source] = $source;	
	}

	/*
	 * Possible values for setting sections
	 *
	 *
	 */
	$all_sections = array(
					'mdjm_general_settings' => array(
							'title' 	=> __( 'General MDJM Settings' ) . '<hr />',
							'page'	 => 'mdjm-settings',
							),
					'mdjm_permissions_settings' => array(
							'title'	=> __( 'MDJM Permissions' ) . '<hr />',
							'page'	 => 'mdjm-permissions',
							),
					'mdjm_debugging_settings' => array(
							'title'	=> __( 'MDJM Debugging' ) . '<hr />',
							'page'	 => 'mdjm-debugging',
							),
					'mdjm_debugging_files_settings' => array(
							'title'	=> __( 'Log Files' ) . '<hr />',
							'page'	 => 'mdjm-debugging-files',
							),
					'mdjm_uninstall_settings' => array(
							'title'	=> __( 'MDJM Plugin Removal Settings' ) . '<hr />',
							'page'	 => 'mdjm-uninstall',
							),
					'mdjm_event_settings' => array(
							'title' 	=> __( 'Event Settings' ) . '<hr />',
							'page'	 => 'mdjm-events',
							),
					'mdjm_playlist_settings' => array(
							'title' 	=> __( 'Playlist Settings' ) . '<hr />',
							'page'	 => 'mdjm-playlists',
							),
					'mdjm_email_settings' => array(
							'title' 	=> __( 'Email Settings' ) . '<hr />',
							'page'	 => 'mdjm-email',
							),
					'mdjm_enquiry_templates_settings' => array(
							'title' 	=> __( 'Quote Templates' ) . '<hr />',
							'page'	 => 'mdjm-email-templates',
							),
					'mdjm_contract_templates_settings' => array(
							'title' 	=> __( 'Awaiting Contract Templates' ) . '<hr />',
							'page'	 => 'mdjm-email-templates',
							),
					'mdjm_confirmation_templates_settings' => array(
							'title' 	=> __( 'Booking Confirmation Templates' ) . '<hr />',
							'page'	 => 'mdjm-email-templates',
							),
					'mdjm_payment_templates_settings' => array(
							'title' 	=> __( 'Payment Confirmation Templates' ) . '<hr />',
							'page'	 => 'mdjm-email-templates',
							),
					'mdjm_clientzone_general_settings' => array(
							'title' 	=> __( MDJM_APP . ' General Settings' ) . '<hr />',
							'page'	 => 'mdjm-clientzone',
							),
					'mdjm_clientzone_client_settings' => array(
							'title' 	=> __( 'Client Settings' ) . '<hr />',
							'page'	 => 'mdjm-clientzone',
							),
					'mdjm_clientzone_page_settings' => array(
							'title' 	=> __( 'Pages' ) . '<hr />',
							'page'	 => 'mdjm-clientzone-page',
							),
					'mdjm_clientzone_text_settings' => array(
							'title' 	=> '',
							'page'	 => 'mdjm-clientzone-text',
							),
					'mdjm_clientzone_text_general_settings' => array(
							'title' 	=> __( 'General Text' ) . '<hr />',
							'page'	 => 'mdjm-clientzone-text',
							),
					'mdjm_clientzone_text_home_settings' => array(
							'title' 	=> __( 'Home Page Text' ) . '<hr />',
							'page'	 => 'mdjm-clientzone-text',
							),
					'mdjm_clientzone_text_profile_settings' => array(
							'title' 	=> __( 'Profile Page Text' ) . '<hr />',
							'page'	 => 'mdjm-clientzone-text',
							),
					'mdjm_clientzone_text_contract_settings' => array(
							'title' 	=> __( 'Contracts Page Text' ) . '<hr />',
							'page'	 => 'mdjm-clientzone-text',
							),
					'mdjm_clientzone_text_playlist_settings' => array(
							'title' 	=> __( 'Playlists Page Text' ) . '<hr />',
							'page'	 => 'mdjm-clientzone-text',
							),
					'mdjm_clientzone_text_payment_settings' => array(
							'title' 	=> __( 'Payments Page Text' ) . '<hr />',
							'page'	 => 'mdjm-clientzone-text',
							),
					'mdjm_clientzone_availability_settings' => array(
							'title' 	=> __( 'Availability Settings' ) . '<hr />',
							'page'	 => 'mdjm-availability',
							),
					'mdjm_payment_settings' => array(
							'title' 	=> __( 'Payments Settings' ) . '<hr />',
							'page'	 => 'mdjm-payments',
							),
					'mdjm_paypal_settings' => array(
							'title' 	=> __( 'PayPal Configuration' ) . '<hr />',
							'page'	 => 'mdjm-paypal',
							),
					'mdjm_paypal_advanced_settings' => array(
							'title' 	=> __( 'PayPal Advanced Configuration' ) . '<hr />',
							'page'	 => 'mdjm-paypal',
							),
						);

	/*
	 * This array holds all settings and their config / data
	 *
	 * 
	 */
	$all_settings = array( 
					/* -- General Settings -- */
							'company_name' => array(
									'label' => 'Company Name:',
									'key' => MDJM_SETTINGS_KEY,
									'value'	=> MDJM_COMPANY,
									'type' => 'text',
									'class' => 'regular-text',
									'text' => '',
									'desc' => 'Enter your company name',
									'section' => 'general',
									'page' => 'settings',
									),
									
							'items_per_page' => array(
									'label' => 'Items per Page:',
									'key' => MDJM_SETTINGS_KEY,
									'value'	=> ( !empty( $mdjm_settings['main']['items_per_page'] ) ? $mdjm_settings['main']['items_per_page'] : '' ),
									'type' => 'custom_dropdown',
									'class' => 'small-text',
									'text' => '',
									'desc' => 'The number of items you want to list per page in event/client/DJ/Venue view',
									'custom_args' => array (
														'sort_order' => '',
														'list_type' => 'defined',
														'list_values' => array( '10' => '10',
																				'25' => '25',
																				'50' => '50',
																				'100' => '100',
																			),
														),
									'section' => 'general',
									'page' => 'settings',
									),
									
							'time_format' => array(
									'label' => 'Display Time as?',
									'key' => MDJM_SETTINGS_KEY,
									'value'	=> MDJM_TIME_FORMAT,
									'type' => 'custom_dropdown',
									'text' => '',
									'desc' => 'Select the format in which you want your event times displayed. Applies to both admin and client pages',
									'custom_args' => array (
														'list_type' => 'defined',
														'list_values' => array( 'g:i A'	=> get_date_from_gmt( '', 'g:i A' ),
																				'H:i'	  => get_date_from_gmt( '', 'H:i' ) ),
														),
									'section' => 'general',
									'page' => 'settings',
									),
									
							'short_date_format' => array(
									'label' => 'Short Date Format:',
									'key' => MDJM_SETTINGS_KEY,
									'type' => 'custom_dropdown',
									'value' => MDJM_SHORTDATE_FORMAT,
									'text' => '',
									'desc' => 'Select the format in which you want short dates displayed. Applies to both admin and client pages',
									'custom_args' => array (
														'sort_order' => '',
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
									),
							
							'show_dashboard' => array(
									'label' => 'Show Dashboard Widget?',
									'key' => MDJM_SETTINGS_KEY,
									'type' => 'checkbox',
									'value' => ( !empty( $mdjm_settings['main']['show_dashboard'] ) ? '1' : '0' ),
									'text' => '',
									'desc' => 'Displays the MDJM widget on the main Wordpress Admin Dashboard',
									'section' => 'general',
									'page' => 'settings',
									),
									
							'show_credits' => array(
									'label' => 'Display Credits?',
									'key' => MDJM_SETTINGS_KEY,
									'type' => 'checkbox',
									'value' => ( !empty( $mdjm_settings['main']['show_credits'] ) ? '1' : '0' ),
									'text' => '',
									'desc' => 'Whether or not to display the <font size="-1"; color="#F90">Powered by ' . 
										MDJM_NAME . ', version ' . MDJM_VERSION_NUM . '</font> text at the footer of the ' . 
										MDJM_APP . ' application pages.',
									'section' => 'general',
									'page' => 'settings',
									),
									
					/* -- Permissions -- */
							'dj_see_wp_dash' => array(
									'label' => MDJM_DJ . '\'s see WP Dashboard?',
									'key' => MDJM_PERMISSIONS_KEY,
									'type' => 'checkbox',
									'value' => ( !empty( $mdjm_settings['permissions']['dj_see_wp_dash'] ) ? '1' : '0' ),
									'text' => 'If checked your ' . MDJM_DJ . '\'s will be able to see the main WordPress Dashboard page',
									'desc' => '',
									'section' => 'permissions',
									'page' => 'permissions',
									),

							'dj_add_client' => array(
									'label' => MDJM_DJ . '\'s can Add New Clients?',
									'key' => MDJM_PERMISSIONS_KEY,
									'type' => 'checkbox',
									'value' => ( !empty( $mdjm_settings['permissions']['dj_add_client'] ) ? '1' : '0' ),
									'text' => '',
									'desc' => '',
									'section' => 'permissions',
									'page' => 'permissions',
									),
									
							'dj_add_event' => array(
									'label' => MDJM_DJ . '\'s Can Add New Events?',
									'key' => MDJM_PERMISSIONS_KEY,
									'type' => 'checkbox',
									'value' => ( !empty( $mdjm_settings['permissions']['dj_add_event'] ) ? '1' : '0' ),
									'text' => '',
									'desc' => '',
									'section' => 'permissions',
									'page' => 'permissions',
									),
								
							'dj_view_enquiry' => array(
									'label' => MDJM_DJ . '\'s Can View Enquiries',
									'key' => MDJM_PERMISSIONS_KEY,
									'type' => 'checkbox',
									'value' => ( !empty( $mdjm_settings['permissions']['dj_view_enquiry'] ) ? '1' : '0' ),
									'text' => '',
									'desc' => '',
									'section' => 'permissions',
									'page' => 'permissions',
									),
									
							'dj_add_venue' => array(
									'label' => MDJM_DJ . '\'s Can Add New Venues?',
									'key' => MDJM_PERMISSIONS_KEY,
									'type' => 'checkbox',
									'value' => ( !empty( $mdjm_settings['permissions']['dj_add_venue'] ) ? '1' : '0' ),
									'text' => '',
									'desc' => '',
									'section' => 'permissions',
									'page' => 'permissions',
									),
									
							'dj_see_deposit' => array(
									'label' => MDJM_DJ . '\'s Can See Deposit Info?',
									'key' => MDJM_PERMISSIONS_KEY,
									'type' => 'checkbox',
									'value' => ( !empty( $mdjm_settings['permissions']['dj_see_deposit'] ) ? '1' : '0' ),
									'text' => '',
									'desc' => '',
									'section' => 'permissions',
									'page' => 'permissions',
									),
									
							'dj_disable_shortcode' => array(
									'label' => 'Disabled Shortcodes for ' . MDJM_DJ . '\'s:',
									'key' => MDJM_PERMISSIONS_KEY,
									'type' => 'multiple_select',
									'value' => ( !empty( $mdjm_settings['permissions']['dj_disable_shortcode'] ) ? $mdjm_settings['permissions']['dj_disable_shortcode'] : '' ),
									'text' => '<a href="' . mdjm_get_admin_page( 'mydjplanner' ) . 
										'" target="_blank">Full list of Shortcodes</a>',
									'desc' => 'CTRL (cmd on MAC) + Click to select multiple Shortcode entries that ' . 
										MDJM_DJ . '\'s cannot use',
									'size' => 8,
									'custom_args' => array (
													'list_type' => 'shortcode',
													'list_values' => $all_shortcodes,
													),
									'section' => 'permissions',
									'page' => 'permissions',
									),
									
							'dj_disable_template' => array(
									'label' => 'Disabled Templates for ' . MDJM_DJ . '\'s:',
									'key' => MDJM_PERMISSIONS_KEY,
									'type' => 'multiple_select',
									'value' => ( !empty( $mdjm_settings['permissions']['dj_disable_template'] ) ? $mdjm_settings['permissions']['dj_disable_template'] : '' ),
									'text' => '',
									'desc' => 'CTRL (cmd on MAC) + Click to select multiple Template entries that ' . MDJM_DJ . '\'s cannot use',
									'size' => 8,
									'custom_args' => array (
														'list_type' => 'templates',
														),
									'section' => 'permissions',
									'page' => 'permissions',
									),
					/* -- Debugging -- */
							MDJM_DEBUG_KEY => array(
									'label' => 'Enable Debugging?',
									'type' => 'checkbox',
									'value' => ( MDJM_DEBUG == true ? '1' : '0' ),
									'text' => '',
									'desc' => '',
									'section' => 'debugging',
									'page' => 'debugging',
									),
									
							'log_size' => array(
									'label'  => 'Maximum Log File Size:',
									'key'	=> MDJM_DEBUG_SETTINGS_KEY,
									'type' => 'text',
									'class' => 'small-text',
									'value' => ( !empty( $mdjm_debug->settings['log_size'] ) ? $mdjm_debug->settings['log_size'] : '' ),
									'text' => 'MB <code>Default is 2 (MB)</code>',
									'desc' => 'The max size in Megabytes to allow your log files to grow to before you receive a warning (if configured below)',
									'section' => 'debugging_files',
									'page' => 'debugging-files',
									),
									
							'warn' => array(
									'label'  => 'Display Warning if Over Size?',
									'key'	=> MDJM_DEBUG_SETTINGS_KEY,
									'type' => 'checkbox',
									'class' => 'small-text',
									'value' => ( !empty( $mdjm_debug->settings['warn'] ) ? '1' : '' ),
									'text' => '',
									'desc' => 'Will display notice and allow removal and recreation of log files',
									'section' => 'debugging_files',
									'page' => 'debugging-files',
									),
									
							'auto_purge' => array(
									'label'  => 'Auto Purge Log Files?',
									'key'	=> MDJM_DEBUG_SETTINGS_KEY,
									'type' => 'checkbox',
									'value' => ( !empty( $mdjm_debug->settings['auto_purge'] ) ? '1' : '' ),
									'text' => '',
									'desc' => 'If selected, log files will be auto-purged when they reach the value of <code>Maximum Log File Size</code>',
									'section' => 'debugging_files',
									'page' => 'debugging-files',
									),
									
					/* -- Uninstallation -- */
							'uninst_remove_db' => array(
									'label' => 'Remove Database Tables?',
									'key' => MDJM_UNINST_SETTINGS_KEY,
									'type' => 'checkbox',
									'value' => ( !empty( $mdjm_settings['uninst']['uninst_remove_db'] ) ? $mdjm_settings['uninst']['uninst_remove_db'] : '' ),
									'text' => '',
									'desc' => 'Should the database tables and data be removed when uninstalling the plugin? ' . 
										'Cannot be recovered unless you or your host have a backup solution in place and a recent backup.',
									'section' => 'uninstall',
									'page' => 'uninstall',
									),
									
							'uninst_remove_mdjm_templates' => array(
									'label' => 'Remove Templates?',
									'key' => MDJM_UNINST_SETTINGS_KEY,
									'type' => 'checkbox',
									'value' => ( !empty( $mdjm_settings['uninst']['uninst_remove_mdjm_templates'] ) ? $mdjm_settings['uninst']['uninst_remove_mdjm_templates'] : '' ),
									'text' => '',
									'desc' => 'Do you want to remove the Contract and Email Templates associated with Mobile DJ Manager when uninstalling plugin? ' . 
										'They will be sent to trash, not permanently deleted',
									'section' => 'uninstall',
									'page' => 'uninstall',
									),
					
					/* -- Event Settings -- */
							'event_prefix' => array(
									'label' => 'Event Prefix:',
									'key' => MDJM_EVENT_SETTINGS_KEY,
									'type' => 'text',
									'class' => 'small-text',
									'value' => MDJM_EVENT_PREFIX,
									'text' => '',
									'desc' => 'The prefix you enter here will be added to each unique event, contract and invoice ID. ',
									'section' => 'event',
									'page' => 'events',
									),
							
							'employer' => array(
									'label' => 'I am an Employer?',
									'key' => MDJM_EVENT_SETTINGS_KEY,
									'type' => 'checkbox',
									'value' => ( !empty( $mdjm_settings['events']['employer'] ) ? '1' : '' ),
									'text' => '',
									'desc' => 'Check this if you employ other staff (i.e. ' . MDJM_DJ . '\'s)',
									'section' => 'event',
									'page' => 'events',
									),
									
							'artist' => array(
									'label' => 'Refer to Performers as?',
									'key' => MDJM_EVENT_SETTINGS_KEY,
									'value'	=> MDJM_DJ,
									'type' => 'text',
									'class' => 'regular-text',
									'text' => 'Default is <code>DJ</code>',
									'desc' => 'Change the name of your performers here as necessary. Useful if you are not a DJ business',
									'section' => 'general',
									'section' => 'event',
									'page'  => 'events',
									),
									
							'enable_packages' => array(
									'label' => 'Enable Packages?',
									'key' => MDJM_EVENT_SETTINGS_KEY,
									'type' => 'checkbox',
									'value' => ( MDJM_PACKAGES == true ? '1' : '' ),
									'text' => '',
									'desc' => 'Check this to enable Equipment Packages & Inventories',
									'section' => 'event',
									'page' => 'events',
									),
									
							'default_contract' => array(
									'label' => 'Default Client Contract',
									'key' => MDJM_EVENT_SETTINGS_KEY,
									'type' => 'custom_dropdown',
									'class' => 'regular-text',
									'value' => ( !empty( $mdjm_settings['events']['default_contract'] ) ? $mdjm_settings['events']['default_contract'] : '' ),
									'text' => '<a href="' . admin_url() . 'post-new.php?post_type=contract" class="add-new-h2">Add New</a>',
									'desc' => 'Select the client contract you want to use as default. This can be changed per event.',
									'custom_args' => array (
														'sort_order' => 'ASC',
														'list_type' => 'contract'
														),
									'section' => 'event',
									'page' => 'events',
									),
									
							'warn_unattended' => array(
									'label' => 'New Enquiry Notification?',
									'key' => MDJM_EVENT_SETTINGS_KEY,
									'type' => 'checkbox',
									'value' => ( !empty( $mdjm_settings['events']['warn_unattended'] ) ? $mdjm_settings['events']['warn_unattended'] : '' ),
									'text' => '',
									'desc' => 'Displays a notification message at the top of the Admin pages to Administrators if there are outstanding Unattended Enquiries',
									'section' => 'event',
									'page' => 'events',
									),
									
							'enquiry_sources' => array(
									'label' => 'Enquiry Sources',
									'key' => MDJM_EVENT_SETTINGS_KEY,
									'type' => 'textarea',
									'class' => 'all-options',
									'value' => $mdjm_settings['events']['enquiry_sources'],
									'text' => '',
									'desc' => 'Enter possible sources of enquiries. One per line',
									'section' => 'event',
									'page' => 'events',
									),
									
							'journaling' => array(
									'label' => 'Enable Journaling?',
									'key' => MDJM_EVENT_SETTINGS_KEY,
									'type' => 'checkbox',
									'value' => ( !empty( $mdjm_settings['events']['journaling'] ) ? '1' : '' ),
									'text' => '',
									'desc' => 'Log and track all client &amp; event actions (recommended)',
									'section' => 'event',
									'page' => 'events',
									),
									
					/* -- Playlist Settings -- */
							'close' => array(
									'label' => 'Close the Playlist',
									'key' => MDJM_PLAYLIST_SETTINGS_KEY,
									'type' => 'text',
									'class' => 'small-text',
									'value' => MDJM_PLAYLIST_CLOSE,
									'text' => 'Days before the event should the playlist be closed',
									'desc' => 'Enter <code>0</code> to never close',
									'section' => 'playlist',
									'page' => 'playlists',
									),
									
							'playlist_cats' => array(
									'label' => 'Playlist Song Categories',
									'key' => MDJM_PLAYLIST_SETTINGS_KEY,
									'type' => 'textarea',
									'class' => 'all-options',
									'value' => str_replace( ",", "\n", $mdjm_settings['playlist']['playlist_cats'] ),
									'text' => '',
									'desc' => 'The options clients can select for when songs are to be played when adding to the playlist. One per line.',
									'section' => 'playlist',
									'page' => 'playlists',
									),
									
							'upload_playlists' => array(
									'label' => 'Upload Playlists?',
									'key' => MDJM_PLAYLIST_SETTINGS_KEY,
									'type' => 'checkbox',
									'value' => ( !empty( $mdjm_settings['playlist']['upload_playlists'] ) ? '1' : '0' ),
									'text' => '',
									'desc' => 'With this option checked, your playlist information will occasionally be transmitted back to the MDJM servers ' . 
										'to help build an information library. The consolidated list of playlist songs will be freely shared. ' . 
										'Only song, artist and the event type information is transmitted.',
									'section' => 'playlist',
									'page' => 'playlists',
									),
									
					/* -- Email Settings -- */
							'system_email' => array(
									'label' => 'Default Email Address',
									'key' => MDJM_EMAIL_SETTINGS_KEY,
									'type' => 'email',
									'class' => 'regular-text',
									'value' => ( !empty( $mdjm_settings['email']['system_email'] ) ? $mdjm_settings['email']['system_email'] : '' ),
									'text' => 'Defaults to the E-mail Address set within <a href="' . 
									admin_url( 'options-general.php' ) . '">WordPress Settings > General</a>',
									'desc' => 'The email address you want generic emails from MDJM to come from',
									'section' => 'email',
									'page' => 'email',
									),
									
							'track_client_emails' => array(
									'label' => 'Track Client Emails?',
									'key' => MDJM_EMAIL_SETTINGS_KEY,
									'type' => 'checkbox',
									'value' => ( !empty( $mdjm_settings['email']['track_client_emails'] ) ? '1' : '' ),
									'text' => 'If selected you can determine if emails you send have been opened ' . 
									'witihin the <a href="' . mdjm_get_admin_page( 'comms' ) . '">Communicatiion ' . 
									'history page</a>',
									'desc' => '<code>Note</code>: not all email clients will support this',
									'section' => 'email',
									'page' => 'email',
									),
									
							'bcc_dj_to_client' => array(
									'label' => 'Copy DJ in Client Emails?',
									'key' => MDJM_EMAIL_SETTINGS_KEY,
									'type' => 'checkbox',
									'value' => ( !empty( $mdjm_settings['email']['bcc_dj_to_client'] ) ? '1' : '' ),
									'text' => '',
									'desc' => 'Send a copy of client emails to ' . MDJM_DJ,
									'section' => 'email',
									'page' => 'email',
									),
									
							'bcc_admin_to_client' => array(
									'label' => 'Copy Admin in Client Emails?',
									'key' => MDJM_EMAIL_SETTINGS_KEY,
									'type' => 'checkbox',
									'value' => ( !empty( $mdjm_settings['email']['bcc_admin_to_client'] ) ? '1' : '' ),
									'text' => '',
									'desc' => 'Send a copy of client emails to Admin',
									'section' => 'email',
									'page' => 'email',
									),
							
							'enquiry' => array(
									'label' => 'Quote Template:',
									'key' => MDJM_TEMPLATES_SETTINGS_KEY,
									'type' => 'custom_dropdown',
									'value' => ( !empty( $mdjm_settings['templates']['enquiry'] ) ? $mdjm_settings['templates']['enquiry'] : '' ),
									'text' => '<a href="' . admin_url() . 'post-new.php?post_type=email_template" class="add-new-h2">Add New</a>',
									'desc' => 'This is the default template used when sending quotes to clients',
									'custom_args' => array (
														'name' =>  MDJM_TEMPLATES_SETTINGS_KEY . '[enquiry]',
														'sort_order' => 'ASC',
														'list_type' => 'email_template'
														),
									'section' => 'enquiry_templates',
									'page' => 'email-templates',
									),
									
							'unavailable' => array(
									'label' => 'Unavailability Template:',
									'key' => MDJM_TEMPLATES_SETTINGS_KEY,
									'type' => 'custom_dropdown',
									'class' => 'regular-text',
									'value' => ( !empty( $mdjm_settings['templates']['unavailable'] ) ? $mdjm_settings['templates']['unavailable'] : '' ),
									'text' => '<a href="' . admin_url() . 'post-new.php?post_type=email_template" class="add-new-h2">Add New</a>',
									'desc' => 'This is the default template used when respoding to enquiries that you are unavailable for the event',
									'custom_args' => array (
														'name' =>  MDJM_TEMPLATES_SETTINGS_KEY . '[unavailable_email_template]',
														'sort_order' => 'ASC',
														'list_type' => 'email_template'
														),
									'section' => 'enquiry_templates',
									'page' => 'email-templates',
									),
							
							'enquiry_from' => array(
									'label' => 'Emails From?',
									'key' => MDJM_TEMPLATES_SETTINGS_KEY,
									'type' => 'custom_dropdown',
									'class' => 'regular-text',
									'value' => ( !empty( $mdjm_settings['templates']['enquiry_from'] ) ? $mdjm_settings['templates']['enquiry_from'] : '' ),
									'text' => '',
									'desc' => 'Who do you want enquiries and unavailability emails to be sent by?',
									'custom_args' => array (
														'name' =>  MDJM_TEMPLATES_SETTINGS_KEY . '[enquiry_email_from]',
														'sort_order' => 'ASC',
														'list_type' => 'defined',
														'list_values' => array( 'admin' => 'Admin',
																				'dj'    => 'Event DJ', ),
														),
									'section' => 'enquiry_templates',
									'page' => 'email-templates',
									),
							
							'contract_to_client' => array(
									'label' => 'Contract Notification Email?',
									'key' => MDJM_TEMPLATES_SETTINGS_KEY,
									'type' => 'checkbox',
									'value' => ( !empty( $mdjm_settings['templates']['contract_to_client'] ) ? '1' : '' ),
									'text' => '',
									'desc' => 'Do you want to auto send an email to the client when their event changes to the <code>Awaiting Contract</code> status?',
									'section' => 'contract_templates',
									'page' => 'email-templates',
									),
							'contract' => array(
									'label' => 'Contract Template:',
									'key' => MDJM_TEMPLATES_SETTINGS_KEY,
									'type' => 'custom_dropdown',
									'class' => 'regular-text',
									'value' => ( !empty( $mdjm_settings['templates']['contract'] ) ? $mdjm_settings['templates']['contract'] : '' ),
									'text' => '<a href="' . admin_url() . 'post-new.php?post_type=email_template" class="add-new-h2">Add New</a>',
									'desc' => 'Only applies if <code>Awaiting Contract Email</code> is enabled',
									'custom_args' => array (
														'name' =>  MDJM_TEMPLATES_SETTINGS_KEY . '[email_contract]',
														'sort_order' => 'ASC',
														'list_type' => 'email_template'
														),
									'section' => 'contract_templates',
									'page' => 'email-templates',
									),
									
							'contract_from' => array(
									'label' => 'Emails From?',
									'key' => MDJM_TEMPLATES_SETTINGS_KEY,
									'type' => 'custom_dropdown',
									'class' => 'regular-text',
									'value' => ( !empty( $mdjm_settings['templates']['contract_from'] ) ? $mdjm_settings['templates']['contract_from'] : '' ),
									'text' => '',
									'desc' => 'Who do you want these emails to be sent by?',
									'custom_args' => array (
														'name' =>  MDJM_TEMPLATES_SETTINGS_KEY . '[contract_from]',
														'sort_order' => 'ASC',
														'list_type' => 'defined',
														'list_values' => array( 'admin' => 'Admin',
																				'dj'    => 'Event DJ', ),
														),
									'section' => 'contract_templates',
									'page' => 'email-templates',
									),
									
							'booking_conf_to_client' => array(
									'label' => 'Booking Confirmation to client?',
									'key' => MDJM_TEMPLATES_SETTINGS_KEY,
									'type' => 'checkbox',
									'value' => ( !empty( $mdjm_settings['templates']['booking_conf_to_client'] ) ? $mdjm_settings['templates']['booking_conf_to_client'] : '' ),
									'text' => '',
									'desc' => 'Email client with selected template when booking is confirmed i.e. contract accepted, or status changed to Approved',
									'section' => 'confirmation_templates',
									'page' => 'email-templates',
									),
									
							'booking_conf_client' => array(
									'label' => 'Client Booking Confirmation Template',
									'key' => MDJM_TEMPLATES_SETTINGS_KEY,
									'type' => 'custom_dropdown',
									'class' => 'regular-text',
									'value' => ( !empty( $mdjm_settings['templates']['booking_conf_client'] ) ? $mdjm_settings['templates']['booking_conf_client'] : '' ),
									'text' => '<a href="' . admin_url() . 'post-new.php?post_type=email_template" class="add-new-h2">Add New</a>',
									'desc' => 'Select an email template to be used when sending the Booking Confirmation to Clients',
									'custom_args' => array (
														'name' =>  MDJM_TEMPLATES_SETTINGS_KEY . '[email_client_confirm]',
														'sort_order' => 'ASC',
														'list_type' => 'email_template'
														),
									'section' => 'confirmation_templates',
									'page' => 'email-templates',
									),
									
							'booking_conf_from' => array(
									'label' => 'Send Booking Confirmation From',
									'key' => MDJM_TEMPLATES_SETTINGS_KEY,
									'type' => 'custom_dropdown',
									'class' => 'regular-text',
									'value' => ( !empty( $mdjm_settings['templates']['booking_conf_from'] ) ? $mdjm_settings['templates']['booking_conf_from'] : '' ),
									'text' => '',
									'desc' => 'Select Admin to have client booking confirmations emailed from the address specified in the <code>Default Email Address</code> or DJ for the from address to be the DJ\'s email address',
									'custom_args' => array (
														'name' =>  MDJM_TEMPLATES_SETTINGS_KEY . '[confirm_email_from]',
														'sort_order' => 'ASC',
														'list_type' => 'defined',
														'list_values' => array( 'admin' => 'Admin',
																				'dj'    => 'Event DJ', ),
														),
									'section' => 'confirmation_templates',
									'page' => 'email-templates',
									),
									
							'booking_conf_to_dj' => array(
									'label' => 'Booking Confirmation to ' . MDJM_DJ . '?',
									'key' => MDJM_TEMPLATES_SETTINGS_KEY,
									'type' => 'checkbox',
									'value' => ( !empty( $mdjm_settings['templates']['booking_conf_to_dj'] ) ? $mdjm_settings['templates']['booking_conf_to_dj'] : '' ),
									'text' => '',
									'desc' => 'Email ' . MDJM_DJ . ' with selected template when booking is confirmed i.e. contract accepted, or status changed to Approved',
									'section' => 'confirmation_templates',
									'page' => 'email-templates',
									),
									
							'email_dj_confirm' => array(
									'label' => 'DJ Booking Confirmation Template',
									'key' => MDJM_TEMPLATES_SETTINGS_KEY,
									'type' => 'custom_dropdown',
									'class' => 'regular-text',
									'value' => ( !empty( $mdjm_settings['templates']['email_dj_confirm'] ) ? $mdjm_settings['templates']['email_dj_confirm'] : '' ),
									'text' => '<a href="' . admin_url() . 'post-new.php?post_type=email_template" class="add-new-h2">Add New</a>',
									'desc' => 'Select an email template to be used when sending the Booking Confirmation to ' . MDJM_DJ . '\'s',
									'custom_args' => array (
														'name' =>  MDJM_TEMPLATES_SETTINGS_KEY . '[email_dj_confirm]',
														'sort_order' => 'ASC',
														'list_type' => 'email_template'
														),
									'section' => 'confirmation_templates',
									'page' => 'email-templates',
									),
									
							'payment_cfm_template' => array(
									'label' => 'Payment Received Template:',
									'key' => MDJM_TEMPLATES_SETTINGS_KEY,
									'type' => 'custom_dropdown',
									'class' => 'regular-text',
									'value' => ( !empty( $mdjm_settings['templates']['payment_cfm_template'] ) ? $mdjm_settings['templates']['payment_cfm_template'] : '' ),
									'text' => '<a href="' . admin_url() . 'post-new.php?post_type=email_template" class="add-new-h2">Add New</a>',
									'desc' => 'Select an email template to be sent to clients when confirming receipt of a payment. <a href="www.mydjplanner.co.uk/shortcodes/" target="_blank">Shortcodes</a> can be used.',
									'custom_args' => array (
														'name' =>  MDJM_TEMPLATES_SETTINGS_KEY . '[payment_cfm_template]',
														'sort_order' => 'ASC',
														'list_type' => 'email_template'
														),
									'section' => 'payment_templates',
									'page' => 'email-templates',
									),
									
							'manual_payment_cfm_template' => array(
									'label' => 'Manual Payment Template:',
									'key' => MDJM_TEMPLATES_SETTINGS_KEY,
									'type' => 'custom_dropdown',
									'class' => 'regular-text',
									'value' => ( !empty( $mdjm_settings['templates']['manual_payment_cfm_template'] ) ? $mdjm_settings['templates']['manual_payment_cfm_template'] : '' ),
									'text' => '<a href="' . admin_url() . 'post-new.php?post_type=email_template" class="add-new-h2">Add New</a>',
									'desc' => 'Select an email template to be sent to clients when you manually mark an event payment as received. <a href="www.mydjplanner.co.uk/shortcodes/" target="_blank">Shortcodes</a> can be used.',
									'custom_args' => array (
														'name' =>  MDJM_TEMPLATES_SETTINGS_KEY . '[manual_payment_cfm_template]',
														'sort_order' => 'ASC',
														'list_type' => 'email_template',
														'first_entry' => array( '0', 'Do Not Email' ),
														),
									'section' => 'payment_templates',
									'page' => 'email-templates',
									),
							
					/* -- Client Zone -- */		
							'app_name' => array(
									'label' => 'Application Name:',
									'key' => MDJM_CLIENTZONE_SETTINGS_KEY,
									'value'	=> MDJM_APP,
									'type' => 'text',
									'class' => 'regular-text',
									'text' => 'Default is <code>Client Zone</code>',
									'desc' => 'Choose your own name for the application. It\'s recommended you give the top level menu item linking to the application the same name.',
									'section' => 'clientzone_general',
									'page' => 'clientzone',
									),
									
							'pass_length' => array(
									'label' => 'Default Password Length',
									'key' => MDJM_CLIENTZONE_SETTINGS_KEY,
									'type' => 'custom_dropdown',
									'class' => 'small-text',
									'value' => $mdjm_settings['clientzone']['pass_length'],
									'text' => '',
									'desc' => 'If opting to generate or reset a user password during event creation, how many characters should the password be?',
									'custom_args' => array (
														'name' =>  'mdjm_plugin_settings[pass_length]',
														'sort_order' => '',
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
									'section' => 'clientzone_client',
									'page' => 'clientzone',
									),
									
							'notify_profile' => array(
									'label' => 'Incomplete Profile Warning?',
									'key' => MDJM_CLIENTZONE_SETTINGS_KEY,
									'type' => 'checkbox',
									'value' => ( !empty( $mdjm_settings['clientzone']['notify_profile'] ) ? '1' : '' ),
									'text' => '',
									'desc' => 'Display notice to Clients when they login if their Profile is incomplete? (i.e. Required field is empty)',
									'section' => 'clientzone_client',
									'page' => 'clientzone',
									),
									
					/* -- Pages -- */
							'app_home_page' => array(
									'label' => MDJM_APP . ' Home Page',
									'key' => MDJM_PAGES_KEY,
									'type' => 'custom_dropdown',
									'class' => 'regular-text',
									'value' => MDJM_HOME,
									'text' => '<a href="' . admin_url() . 'post-new.php?post_type=page" class="add-new-h2">Add New</a>',
									'desc' => 'Select the home page for the ' . MDJM_APP . ' application  - the one where you added the shortcode <code>[MDJM page=Home]</code>',
									'custom_args' => array (
														'name' =>  MDJM_PAGES_KEY . '[app_home_page]',
														'selected' => MDJM_HOME,
														'sort_order' => 'ASC',
														'list_type' => 'page'
														),
									'section' => 'clientzone_page',
									'page' => 'clientzone-page',
									),

							'contact_page' => array(
									'label' => 'Contact Page',
									'key' => MDJM_PAGES_KEY,
									'type' => 'custom_dropdown',
									'class' => 'regular-text',
									'value' => MDJM_CONTACT_PAGE,
									'text' => '<a href="' . admin_url() . 'post-new.php?post_type=page" class="add-new-h2">Add New</a>',
									'desc' => 'Select your website\'s contact page so we can correctly direct visitors.',
									'custom_args' => array (
														'name' =>  MDJM_PAGES_KEY . '[contact_page]',
														'selected' => MDJM_CONTACT_PAGE,
														'sort_order' => 'ASC',
														'list_type' => 'page'
														),
									'section' => 'clientzone_page',
									'page' => 'clientzone-page',
									),
									
							'contracts_page' => array(
									'label' => 'Contracts Page',
									'key' => MDJM_PAGES_KEY,
									'type' => 'custom_dropdown',
									'class' => 'regular-text',
									'value' => MDJM_CONTRACT_PAGE,
									'text' => '<a href="' . admin_url() . 'post-new.php?post_type=page" class="add-new-h2">Add New</a>',
									'desc' => 'Select your website\'s contracts page - the one where you added the shortcode <code>[MDJM page=Contract]</code>',
									'custom_args' => array (
														'name' =>  MDJM_PAGES_KEY . '[contracts_page]',
														'selected' => MDJM_CONTRACT_PAGE,
														'sort_order' => 'ASC',
														'list_type' => 'page'
														),
									'section' => 'clientzone_page',
									'page' => 'clientzone-page',
									),
							
							'payments_page' => array(
									'label' => 'Payments Page',
									'key' => MDJM_PAGES_KEY,
									'type' => 'custom_dropdown',
									'class' => 'regular-text',
									'value' => MDJM_PAYMENT_PAGE,
									'text' => '<a href="' . admin_url() . 'post-new.php?post_type=page" class="add-new-h2">Add New</a>',
									'desc' => 'Select your website\'s payments page - the one where you added the shortcode <code>[MDJM page=Payments]</code>',
									'custom_args' => array (
														'name' =>  MDJM_PAGES_KEY . '[payments_page]',
														'selected' => MDJM_PAYMENT_PAGE,
														'sort_order' => 'ASC',
														'list_type' => 'page'
														),
									'section' => 'clientzone_page',
									'page' => 'clientzone-page',
									),
									
							'playlist_page' => array(
									'label' => 'Playlist Page',
									'key' => MDJM_PAGES_KEY,
									'type' => 'custom_dropdown',
									'class' => 'regular-text',
									'value' => MDJM_PLAYLIST_PAGE,
									'text' => '<a href="' . admin_url() . 'post-new.php?post_type=page" class="add-new-h2">Add New</a>',
									'desc' => 'Select your website\'s playlist page - the one where you added the shortcode <code>[MDJM page=Playlist]</code>',
									'custom_args' => array (
														'name' =>  MDJM_PAGES_KEY . '[playlist_page]',
														'selected' => MDJM_PLAYLIST_PAGE,
														'sort_order' => 'ASC',
														'list_type' => 'page'
														),
									'section' => 'clientzone_page',
									'page' => 'clientzone-page',
									),
									
							'profile_page' => array(
									'label' => 'Profile Page',
									'key' => MDJM_PAGES_KEY,
									'type' => 'custom_dropdown',
									'class' => 'regular-text',
									'value' => MDJM_PROFILE_PAGE,
									'text' => '<a href="' . admin_url() . 'post-new.php?post_type=page" class="add-new-h2">Add New</a>',
									'desc' => 'Select your website\'s profile page - the one where you added the shortcode <code>[MDJM page=Profile]</code>',
									'custom_args' => array (
														'name' =>  MDJM_PAGES_KEY . '[profile_page]',
														'selected' => MDJM_PROFILE_PAGE,
														'sort_order' => 'ASC',
														'list_type' => 'page'
														),
									'section' => 'clientzone_page',
									'page' => 'clientzone-page',
									),
																		
					/* -- Custom Text -- */
							'custom_client_text' => array(
									'label' => 'Enable Customised Text?',
									'key' => MDJM_CUSTOM_TEXT_KEY,
									'type' => 'checkbox',
									'value' => ( !empty( $mdjm_settings['custom_text']['custom_client_text'] ) ? '1' : '0' ),
									'text' => '',
									'desc' => 'Use custom text on Client front end web pages',
									'section' => 'clientzone_text',
									'page' => 'clientzone-text',
									),
									
							'not_logged_in' => array(
									'label' => 'Not Logged In:',
									'key' => MDJM_CUSTOM_TEXT_KEY,
									'type' => 'mce_textarea',
									'value' => $mdjm_settings['custom_text']['not_logged_in'],
									'text' => '',
									'desc' => 'Text displayed with login fields if Client is not logged in',
									'custom_args' => array (
														'name' =>  MDJM_CUSTOM_TEXT_KEY . '[not_logged_in]',
														'mce_settings' => array(
																			'textarea_rows' => 6,
																			'media_buttons' => false,
																			'textarea_name' => MDJM_CUSTOM_TEXT_KEY . '[not_logged_in]',
																			'teeny'         => false,
																			),
														),
									'section' => 'clientzone_text_general',
									'page' => 'clientzone-text',
									),
									
							'home_welcome' => array(
									'label' => 'Welcome Text:',
									'key' => MDJM_CUSTOM_TEXT_KEY,
									'type' => 'mce_textarea',
									'value' => $mdjm_settings['custom_text']['home_welcome'],
									'text' => '',
									'desc' => 'Welcome text displayed on the home page',
									'custom_args' => array (
														'name' =>  'mdjm_frontend_text[home_welcome]',
														'mce_settings' => array(
																			'textarea_rows' => 6,
																			'media_buttons' => false,
																			'textarea_name' => MDJM_CUSTOM_TEXT_KEY . '[home_welcome]',
																			'teeny'         => false,
																			),
														),
									'section' => 'clientzone_text_home',
									'page' => 'clientzone-text',
									),
									
							'home_noevents' => array(
									'label' => 'No Events:',
									'key' => MDJM_CUSTOM_TEXT_KEY,
									'type' => 'mce_textarea',
									'value' => $mdjm_settings['custom_text']['home_noevents'],
									'text' => '',
									'desc' => 'Text displayed on client home page if the client has no events in the system',
									'custom_args' => array (
														'name' =>  MDJM_CUSTOM_TEXT_KEY . '[home_noevents]',
														'mce_settings' => array(
																			'textarea_rows' => 6,
																			'media_buttons' => false,
																			'textarea_name' => MDJM_CUSTOM_TEXT_KEY . '[home_noevents]',
																			'teeny'         => false,
																			),
														),
									'section' => 'clientzone_text_home',
									'page' => 'clientzone-text',
									),
									
						'home_notactive' => array(
									'label' => 'Event Not Active:',
									'key' => MDJM_CUSTOM_TEXT_KEY,
									'type' => 'mce_textarea',
									'class' => '',
									'value' => $mdjm_settings['custom_text']['home_notactive'],
									'text' => '',
									'desc' => 'Text displayed on client event review screen if the selected event is not active',
									'custom_args' => array (
														'name' =>  MDJM_CUSTOM_TEXT_KEY . '[home_notactive]',
														'mce_settings' => array(
																			'textarea_rows' => 6,
																			'media_buttons' => false,
																			'textarea_name' => MDJM_CUSTOM_TEXT_KEY . '[home_notactive]',
																			'teeny'         => false,
																			),
														),
									'section' => 'clientzone_text_home',
									'page' => 'clientzone-text',
									),
									
						'profile_intro' => array(
									'label' => 'Profile Intro:',
									'key' => MDJM_CUSTOM_TEXT_KEY,
									'type' => 'mce_textarea',
									'class' => '',
									'value' => $mdjm_settings['custom_text']['profile_intro'],
									'text' => '',
									'desc' => 'Introductory text displayed on client profile page',
									'custom_args' => array (
														'name' =>  MDJM_CUSTOM_TEXT_KEY . '[profile_intro]',
														'mce_settings' => array(
																			'textarea_rows' => 6,
																			'media_buttons' => false,
																			'textarea_name' => MDJM_CUSTOM_TEXT_KEY . '[profile_intro]',
																			'teeny'         => false,
																			),
														),
									'section' => 'clientzone_text_profile',
									'page' => 'clientzone-text',
									),
									
						'profile_pass_intro' => array(
									'label' => 'Password Change Intro:',
									'key' => MDJM_CUSTOM_TEXT_KEY,
									'type' => 'mce_textarea',
									'class' => '',
									'value' => $mdjm_settings['custom_text']['profile_pass_intro'],
									'text' => '',
									'desc' => 'Introductory text displayed on client profile page',
									'custom_args' => array (
														'name' =>  MDJM_CUSTOM_TEXT_KEY . '[profile_pass_intro]',
														'mce_settings' => array(
																			'textarea_rows' => 6,
																			'media_buttons' => false,
																			'textarea_name' => MDJM_CUSTOM_TEXT_KEY . '[profile_pass_intro]',
																			'teeny'         => false,
																			),
														),
									'section' => 'clientzone_text_profile',
									'page' => 'clientzone-text',
									),
									
						'contract_intro' => array(
									'label' => 'Contract Sign Intro:',
									'key' => MDJM_CUSTOM_TEXT_KEY,
									'type' => 'mce_textarea',
									'class' => '',
									'value' => $mdjm_settings['custom_text']['contract_intro'],
									'text' => '',
									'desc' => 'Text displayed as intro on contract signing page',
									'custom_args' => array (
														'name' =>  MDJM_CUSTOM_TEXT_KEY . '[contract_intro]',
														'mce_settings' => array(
																			'textarea_rows' => 6,
																			'media_buttons' => false,
																			'textarea_name' => MDJM_CUSTOM_TEXT_KEY . '[contract_intro]',
																			'teeny'         => false,
																			),
														),
									'section' => 'clientzone_text_contract',
									'page' => 'clientzone-text',
									),
									
						'contract_not_ready' => array(
									'label' => 'Contract Not Ready:',
									'key' => MDJM_CUSTOM_TEXT_KEY,
									'type' => 'mce_textarea',
									'class' => '',
									'value' => $mdjm_settings['custom_text']['contract_not_ready'],
									'text' => '',
									'desc' => 'Text displayed if Contract is not ready for signing (i.e. Event Status is not <code>Awaiting Contract</code>',
									'custom_args' => array (
														'name' =>  MDJM_CUSTOM_TEXT_KEY . '[contract_not_ready]',
														'mce_settings' => array(
																			'textarea_rows' => 6,
																			'media_buttons' => false,
																			'textarea_name' => MDJM_CUSTOM_TEXT_KEY . '[contract_not_ready]',
																			'teeny'         => false,
																			),
														),
									'section' => 'clientzone_text_contract',
									'page' => 'clientzone-text',
									),
		
						'contract_signed' => array(
									'label' => 'Contract Already Signed:',
									'key' => MDJM_CUSTOM_TEXT_KEY,
									'type' => 'mce_textarea',
									'class' => '',
									'value' => $mdjm_settings['custom_text']['contract_signed'],
									'text' => '',
									'desc' => 'Text displayed if the contract is already signed',
									'custom_args' => array (
														'name' =>  MDJM_CUSTOM_TEXT_KEY . '[contract_signed]',
														'mce_settings' => array(
																			'textarea_rows' => 6,
																			'media_buttons' => false,
																			'textarea_name' => MDJM_CUSTOM_TEXT_KEY . '[contract_signed]',
																			'teeny'         => false,
																			),
														),
									'section' => 'clientzone_text_contract',
									'page' => 'clientzone-text',
									),
									
						'contract_sign_success' => array(
									'label' => 'Contract Sign Success:',
									'key' => MDJM_CUSTOM_TEXT_KEY,
									'type' => 'mce_textarea',
									'class' => '',
									'value' => $mdjm_settings['custom_text']['contract_sign_success'],
									'text' => '',
									'desc' => 'Text displayed after successfull signing of contract',
									'custom_args' => array (
														'name' =>  MDJM_CUSTOM_TEXT_KEY . '[contract_sign_success]',
														'mce_settings' => array(
																			'textarea_rows' => 6,
																			'media_buttons' => false,
																			'textarea_name' => MDJM_CUSTOM_TEXT_KEY . '[contract_sign_success]',
																			'teeny'         => false,
																			),
														),
									'section' => 'clientzone_text_contract',
									'page' => 'clientzone-text',
									),
									
							'playlist_welcome' => array(
									'label' => 'Playlist Welcome:',
									'key' => MDJM_CUSTOM_TEXT_KEY,
									'type' => 'mce_textarea',
									'class' => '',
									'value' => $mdjm_settings['custom_text']['playlist_welcome'],
									'text' => '',
									'desc' => 'Welcome text displayed to logged in users on the Playlist page',
									'custom_args' => array (
														'name' =>  MDJM_CUSTOM_TEXT_KEY . '[playlist_welcome]',
														'mce_settings' => array(
															'textarea_rows' => 6,
															'media_buttons' => false,
															'textarea_name' => MDJM_CUSTOM_TEXT_KEY . '[playlist_welcome]',
															'teeny'         => false,
															),
														),
									'section' => 'clientzone_text_playlist',
									'page' => 'clientzone-text',
									),
									
							'playlist_intro' => array(
									'label' => 'Playlist Intro:',
									'key' => MDJM_CUSTOM_TEXT_KEY,
									'type' => 'mce_textarea',
									'class' => '',
									'value' => $mdjm_settings['custom_text']['playlist_intro'],
									'text' => '',
									'desc' => 'Introduction text displayed on playlist page to logged in users',
									'custom_args' => array (
														'name' =>  MDJM_CUSTOM_TEXT_KEY . '[playlist_intro]',
														'mce_settings' => array(
															'textarea_rows' => 6,
															'media_buttons' => false,
															'textarea_name' => MDJM_CUSTOM_TEXT_KEY . '[playlist_intro]',
															'teeny'         => false,
															),
														),
									'section' => 'clientzone_text_playlist',
									'page' => 'clientzone-text',
									),
									
							'playlist_edit' => array(
									'label' => 'Editing Playlist:',
									'key' => MDJM_CUSTOM_TEXT_KEY,
									'type' => 'mce_textarea',
									'class' => '',
									'value' => $mdjm_settings['custom_text']['playlist_edit'],
									'text' => '',
									'desc' => 'Text displayed to logged in user when editing an event playlist',
									'custom_args' => array (
														'name' =>  MDJM_CUSTOM_TEXT_KEY . '[playlist_edit]',
														'mce_settings' => array(
															'textarea_rows' => 6,
															'media_buttons' => false,
															'textarea_name' => MDJM_CUSTOM_TEXT_KEY . '[playlist_edit]',
															'teeny'         => false,
															),
														),
									'section' => 'clientzone_text_playlist',
									'page' => 'clientzone-text',
									),
									
							'playlist_closed' => array(
									'label' => 'Playlist Closed:',
									'key' => MDJM_CUSTOM_TEXT_KEY,
									'type' => 'mce_textarea',
									'class' => '',
									'value' => $mdjm_settings['custom_text']['playlist_closed'],
									'text' => '',
									'desc' => 'Text displayed to logged in user when playlist is closed',
									'custom_args' => array (
														'name' =>  MDJM_CUSTOM_TEXT_KEY . '[playlist_closed]',
														'mce_settings' => array(
															'textarea_rows' => 6,
															'media_buttons' => false,
															'textarea_name' => MDJM_CUSTOM_TEXT_KEY . '[playlist_closed]',
															'teeny'         => false,
															),
														),
									'section' => 'clientzone_text_playlist',
									'page' => 'clientzone-text',
									),
									
							'playlist_noevent' => array(
									'label' => 'No Active Events:',
									'key' => MDJM_CUSTOM_TEXT_KEY,
									'type' => 'mce_textarea',
									'class' => '',
									'value' => $mdjm_settings['custom_text']['playlist_noevent'],
									'text' => '',
									'desc' => 'Text displayed to logged in users who have no active events',
									'custom_args' => array (
														'name' =>  MDJM_CUSTOM_TEXT_KEY . '[playlist_noevent]',
														'mce_settings' => array(
															'textarea_rows' => 6,
															'media_buttons' => false,
															'textarea_name' => MDJM_CUSTOM_TEXT_KEY . '[playlist_noevent]',
															'teeny'         => false,
															),
														),
									'section' => 'clientzone_text_playlist',
									'page' => 'clientzone-text',
									),
									
							'playlist_guest_welcome' => array(
									'label' => 'Guest Welcome:',
									'key' => MDJM_CUSTOM_TEXT_KEY,
									'type' => 'mce_textarea',
									'class' => '',
									'value' => $mdjm_settings['custom_text']['playlist_guest_welcome'],
									'text' => '',
									'desc' => 'Welcome text displayed to guests',
									'custom_args' => array (
														'name' =>  MDJM_CUSTOM_TEXT_KEY . '[playlist_guest_welcome]',
														'mce_settings' => array(
															'textarea_rows' => 6,
															'media_buttons' => false,
															'textarea_name' => MDJM_CUSTOM_TEXT_KEY . '[playlist_guest_welcome]',
															'teeny'         => false,
															),
														),
									'section' => 'clientzone_text_playlist',
									'page' => 'clientzone-text',
									),
									
							'playlist_guest_intro' => array(
									'label' => 'Guest Intro:',
									'key' => MDJM_CUSTOM_TEXT_KEY,
									'type' => 'mce_textarea',
									'class' => '',
									'value' => $mdjm_settings['custom_text']['playlist_guest_intro'],
									'text' => '',
									'desc' => 'Introduction text displayed on playlist page to guests',
									'custom_args' => array (
														'name' =>  MDJM_CUSTOM_TEXT_KEY . '[playlist_guest_intro]',
														'mce_settings' => array(
															'textarea_rows' => 6,
															'media_buttons' => false,
															'textarea_name' => MDJM_CUSTOM_TEXT_KEY . '[playlist_guest_intro]',
															'teeny'         => false,
															),
														),
									'section' => 'clientzone_text_playlist',
									'page' => 'clientzone-text',
									),
									
							'playlist_guest_closed' => array(
									'label' => 'Guest Playlist Closed:',
									'key' => MDJM_CUSTOM_TEXT_KEY,
									'type' => 'mce_textarea',
									'class' => '',
									'value' => $mdjm_settings['custom_text']['playlist_guest_closed'],
									'text' => '',
									'desc' => 'Text displayed to guests when playlist is closed',
									'custom_args' => array (
														'name' =>  MDJM_CUSTOM_TEXT_KEY . '[playlist_guest_closed]',
														'sort_order' => '',
														'selected' => '',
														'list_type' => '',
														'mce_settings' => array(
															'textarea_rows' => 6,
															'media_buttons' => false,
															'textarea_name' => MDJM_CUSTOM_TEXT_KEY . '[playlist_guest_closed]',
															'teeny'         => false,
															),
														),
									'section' => 'clientzone_text_playlist',
									'page' => 'clientzone-text',
									),
									
							'payment_welcome' => array(
									'label' => 'Payment Welcome:',
									'key' => MDJM_CUSTOM_TEXT_KEY,
									'type' => 'mce_textarea',
									'class' => '',
									'value' => $mdjm_settings['custom_text']['payment_welcome'],
									'text' => '',
									'desc' => 'Welcome text displayed to Clients when they arrive at the Payments page',
									'custom_args' => array (
														'name' =>  MDJM_CUSTOM_TEXT_KEY . '[payment_welcome]',
														'sort_order' => '',
														'selected' => '',
														'list_type' => '',
														'mce_settings' => array(
															'textarea_rows' => 6,
															'media_buttons' => false,
															'textarea_name' => MDJM_CUSTOM_TEXT_KEY . '[payment_welcome]',
															'teeny'         => false,
															),
										),
									'section' => 'clientzone_text_payment',
									'page' => 'clientzone-text',
									),
									
							'payment_intro' => array(
									'label' => 'Payment Intro:',
									'key' => MDJM_CUSTOM_TEXT_KEY,
									'type' => 'mce_textarea',
									'class' => '',
									'value' => $mdjm_settings['custom_text']['payment_intro'],
									'text' => '',
									'desc' => 'Intro text displayed to Clients when they arrive at the Payments page',
									'custom_args' => array (
														'name' =>  MDJM_CUSTOM_TEXT_KEY . '[payment_intro]',
														'sort_order' => '',
														'selected' => '',
														'list_type' => '',
														'mce_settings' => array(
															'textarea_rows' => 6,
															'media_buttons' => false,
															'textarea_name' => MDJM_CUSTOM_TEXT_KEY . '[payment_intro]',
															'teeny'         => false,
															),
														),
									'section' => 'clientzone_text_payment',
									'page' => 'clientzone-text',
									),
									
							'payment_complete' => array(
									'label' => 'Payment Completed:',
									'key' => MDJM_CUSTOM_TEXT_KEY,
									'type' => 'mce_textarea',
									'class' => '',
									'value' => $mdjm_settings['custom_text']['payment_complete'],
									'text' => '',
									'desc' => 'Text displayed to Clients when they complete payment and return to your payments page from PayPal',
									'custom_args' => array (
														'name' =>  MDJM_CUSTOM_TEXT_KEY . '[payment_complete]',
														'sort_order' => '',
														'selected' => '',
														'list_type' => '',
														'mce_settings' => array(
															'textarea_rows' => 6,
															'media_buttons' => false,
															'textarea_name' => MDJM_CUSTOM_TEXT_KEY . '[payment_complete]',
															'teeny'         => false,
															),
														),
									'section' => 'clientzone_text_payment',
									'page' => 'clientzone-text',
									),
									
							'payment_cancel' => array(
									'label' => 'Payment Cancelled:',
									'key' => MDJM_CUSTOM_TEXT_KEY,
									'type' => 'mce_textarea',
									'class' => '',
									'value' => $mdjm_settings['custom_text']['payment_cancel'],
									'text' => '',
									'desc' => 'Text displayed to Clients when they cancel their payment and return to your payments page from PayPal',
									'custom_args' => array (
														'name' =>  MDJM_CUSTOM_TEXT_KEY . '[payment_cancel]',
														'sort_order' => '',
														'selected' => '',
														'list_type' => '',
														'mce_settings' => array(
															'textarea_rows' => 6,
															'media_buttons' => false,
															'textarea_name' => MDJM_CUSTOM_TEXT_KEY . '[payment_cancel]',
															'teeny'         => false,
															),
														),
									'section' => 'clientzone_text_payment',
									'page' => 'clientzone-text',
									),
									
							'payment_not_due' => array(
									'label' => 'Payment Not Due:',
									'key' => MDJM_CUSTOM_TEXT_KEY,
									'type' => 'mce_textarea',
									'class' => '',
									'value' => $mdjm_settings['custom_text']['payment_not_due'],
									'text' => '',
									'desc' => 'Text displayed to clients when they land on the payments page but the no payments are due',
									'custom_args' => array (
														'name' =>  MDJM_CUSTOM_TEXT_KEY . '[payment_not_due]',
														'sort_order' => '',
														'selected' => '',
														'list_type' => '',
														'mce_settings' => array(
															'textarea_rows' => 6,
															'media_buttons' => false,
															'textarea_name' => MDJM_CUSTOM_TEXT_KEY . '[payment_not_due]',
															'teeny'         => false,
															),
														),
									'section' => 'clientzone_text_payment',
									'page' => 'clientzone-text',
									),
									
							'payment_noevent' => array(
									'label' => 'Payment No Event:',
									'key' => MDJM_CUSTOM_TEXT_KEY,
									'type' => 'mce_textarea',
									'class' => '',
									'value' => $mdjm_settings['custom_text']['payment_noevent'],
									'text' => '',
									'desc' => 'Text displayed to clients when they land on the payments page without an event (unlikely)',
									'custom_args' => array (
														'name' =>  MDJM_CUSTOM_TEXT_KEY . '[payment_noevent]',
														'sort_order' => '',
														'selected' => '',
														'list_type' => '',
														'mce_settings' => array(
															'textarea_rows' => 6,
															'media_buttons' => false,
															'textarea_name' => MDJM_CUSTOM_TEXT_KEY . '[payment_noevent]',
															'teeny'         => false,
															),
														),
									'section' => 'clientzone_text_payment',
									'page' => 'clientzone-text',
									),
									
							'payment_noaccess' => array(
									'label' => 'Payment No Permission:',
									'key' => MDJM_CUSTOM_TEXT_KEY,
									'type' => 'mce_textarea',
									'class' => '',
									'value' => $mdjm_settings['custom_text']['payment_noaccess'],
									'text' => '',
									'desc' => 'Text displayed to clients when they land on the payments page but the specified event is not theirs (very unlikely)',
									'custom_args' => array (
														'name' =>  MDJM_CUSTOM_TEXT_KEY . '[payment_noaccess]',
														'sort_order' => '',
														'selected' => '',
														'list_type' => '',
														'mce_settings' => array(
															'textarea_rows' => 6,
															'media_buttons' => false,
															'textarea_name' => MDJM_CUSTOM_TEXT_KEY . '[payment_noaccess]',
															'teeny'         => false,
															),
														),
									'section' => 'clientzone_text_payment',
									'page' => 'clientzone-text',
									),
					
					/* -- Availability Settings -- */
							'availability_check_pass_page' => array(
									'label' => 'Available Redirect Page',
									'key' => MDJM_AVAILABILITY_SETTINGS_KEY,
									'type' => 'custom_dropdown',
									'class' => 'regular-text',
									'value' => $mdjm_settings['availability']['availability_check_pass_page'],
									'text' => '<a href="' . admin_url() . 'post-new.php?post_type=page" class="add-new-h2">Add New</a>',
									'desc' => 'Select a page to which users should be directed when an availability check is successful',
									'custom_args' => array (
														'name' =>  MDJM_AVAILABILITY_SETTINGS_KEY . '[availability_check_pass_page]',
														'sort_order' => 'ASC',
														'selected' => $mdjm_settings['availability']['availability_check_pass_page'],
														'list_type' => 'page',
														'show_option_none' => 'NO REDIRECT - USE TEXT',
														'option_none_value' => 'text',
														),
									'section' => 'clientzone_availability',
									'page' => 'availability',
									),
									
							'availability_check_pass_text' => array(
									'label' => 'Available Text',
									'key' => MDJM_AVAILABILITY_SETTINGS_KEY,
									'type' => 'mce_textarea',
									'class' => '',
									'value' => $mdjm_settings['availability']['availability_check_pass_text'],
									'text' => '',
									'desc' => 'Text to be displayed when you are available - Only displayed if <code>NO REDIRECT - USE TEXT</code> is selected above, unless you are redirecting to an MDJM Contact Form. Valid shortcodes <code>{EVENT_DATE}</code> &amp; <code>{EVENT_DATE_SHORT}</code>',
									'custom_args' => array (
														'name' =>  'mdjm_plugin_pages[availability_check_pass_text]',
														'mce_settings' => array(
																			'textarea_rows' => 6,
																			'media_buttons' => false,
																			'textarea_name' => MDJM_AVAILABILITY_SETTINGS_KEY . '[availability_check_pass_text]',
																			'teeny'         => false,
																			),
														),
									'section' => 'clientzone_availability',
									'page' => 'availability',
									),
									
							'availability_check_fail_page' => array(
									'label' => 'Not Available Redirect Page',
									'key' => MDJM_AVAILABILITY_SETTINGS_KEY,
									'type' => 'custom_dropdown',
									'class' => 'regular-text',
									'value' => $mdjm_settings['availability']['availability_check_fail_page'],
									'text' => '<a href="' . admin_url() . 'post-new.php?post_type=page" class="add-new-h2">Add New</a>',
									'desc' => 'Select a page to which users should be directed when an availability check is not successful',
									'custom_args' => array (
														'name' =>  MDJM_AVAILABILITY_SETTINGS_KEY . '[availability_check_fail_page]',
														'sort_order' => 'ASC',
														'selected' => $mdjm_settings['availability']['availability_check_fail_page'],
														'list_type' => 'page',
														'show_option_none' => 'NO REDIRECT - USE TEXT',
														'option_none_value' => 'text',
														),
									'section' => 'clientzone_availability',
									'page' => 'availability',
									),
									
							'availability_check_fail_text' => array(
									'label' => 'Unavailable Text',
									'key' => MDJM_AVAILABILITY_SETTINGS_KEY,
									'type' => 'mce_textarea',
									'class' => '',
									'value' => $mdjm_settings['availability']['availability_check_fail_text'],
									'text' => '',
									'desc' => 'Text to be displayed when you are not available - Only displayed if <code>NO REDIRECT - USE TEXT</code> is selected above. Valid shortcodes <code>{EVENT_DATE}</code> &amp; <code>{EVENT_DATE_SHORT}</code>',
									'custom_args' => array (
														'name' =>  MDJM_AVAILABILITY_SETTINGS_KEY . '[availability_check_fail_text]',
														'sort_order' => 'ASC',
														'selected' => $mdjm_settings['availability']['availability_check_fail_text'],
														'list_type' => '',
														'mce_settings' => array(
																			'textarea_rows' => 6,
																			'media_buttons' => false,
																			'textarea_name' => MDJM_AVAILABILITY_SETTINGS_KEY . '[availability_check_fail_text]',
																			'teeny'         => false,
																			),
														),
									'section' => 'clientzone_availability',
									'page' => 'availability',
									),
										
					/* -- Payment Settings -- */
							'currency' => array(
									'label' => 'Currency:',
									'key' => MDJM_PAYMENTS_KEY,
									'type' => 'custom_dropdown',
									'class' => 'small-text',
									'value' => $mdjm_settings['payments']['currency'],
									'text' => '',
									'desc' => '',
									'custom_args' => array (
														'name' =>  MDJM_PAYMENTS_KEY . '[currency]',
														'sort_order' => '',
														'list_type' => 'defined',
														'list_values' => array( 'EUR' => '&euro;',
																				'GBP' => '&pound;',
																				'USD' => '$',
																				'BRL' => '&#x52;&#x24;',
																				'CHF' => 'CHF',
																				'CZK' => '&#x4b;&#x10d;',
																				'DKK' => 'kr',
																				'TRL' => '&#x20a4;'),
														
																		
														),
									'section' => 'payment',
									'page' => 'payments',
									),
									
							'currency_format' => array(
									'label' => 'Display Currency Symbol',
									'key' => MDJM_PAYMENTS_KEY,
									'type' => 'custom_dropdown',
									'value' => $mdjm_settings['payments']['currency_format'],
									'text' => '',
									'desc' => '',
									'custom_args' => array (
														'name' =>  MDJM_PAYMENTS_KEY . '[currency_format]',
														'sort_order' => '',
														'list_type' => 'defined',
														'list_values' => array( 'before' => 'before price',
																				'after' => 'after price',
																				'before with space' => 'before price with space',
																				'after with space' => 'after price with space'),
														),
									'section' => 'payment',
									'page' => 'payments',
									),
									
							'decimal' => array(
									'label' => 'Decimal Point:',
									'key' => MDJM_PAYMENTS_KEY,
									'type' => 'custom_dropdown',
									'value' => $mdjm_settings['payments']['decimal'],
									'text' => '',
									'desc' => '',
									'custom_args' => array (
														'name' =>  MDJM_PAYMENTS_KEY . '[decimal]',
														'sort_order' => '',
														'list_type' => 'defined',
														'list_values' => array( '.' 		=> '. (' . __( 'dot' ) . ')',
																				',' 		=> ', (' . __( 'comma' ) . ')',
																				'&ndash;'  => '&ndash; (' . __( 'dash' ) . ')'),
														),
									'section' => 'payment',
									'page' => 'payments',
									),
							'thousands_seperator' => array(
									'label' => 'Thousands Seperator:',
									'key' => MDJM_PAYMENTS_KEY,
									'type' => 'custom_dropdown',
									'value' => $mdjm_settings['payments']['thousands_seperator'],
									'text' => '',
									'desc' => '',
									'custom_args' => array (
														'name' =>  MDJM_PAYMENTS_KEY . '[thousands_seperator]',
														'sort_order' => '',
														'list_type' => 'defined',
														'list_values' => array( '.' 		=> '. (' . __( 'dot' ) . ')',
																				',' 		=> ', (' . __( 'comma' ) . ')',)
														),
									'section' => 'payment',
									'page' => 'payments',
									),
							
							'deposit_label' => array(
									'label' => 'Label for Deposit:',
									'key' => MDJM_PAYMENTS_KEY,
									'type' => 'text',
									'class' => 'regular-text',
									'value' => MDJM_DEPOSIT_LABEL,
									'text' => 'Default is <code>Deposit</code>',
									'desc' => 'If you don\'t use the word <code>Deposit</code>, you can change it here. Many prefer the term <code>Booking Fee</code>. ' . 
										'Whatever you enter will be visible to all users',
									'section' => 'payment',
									'page' => 'payments',
									),
									
							'balance_label' => array(
									'label' => 'Label for Balance:',
									'key' => MDJM_PAYMENTS_KEY,
									'type' => 'text',
									'class' => 'regular-text',
									'value' => MDJM_BALANCE_LABEL,
									'text' => 'Default is <code>Balance</code>',
									'desc' => 'If you don\'t use the word <code>Balance</code>, you can change it here. Whatever you enter will be visible to all users',
									'section' => 'payment',
									'page' => 'payments',
									),
									
							'default_type' => array(
									'label' => 'Default Payment Type:',
									'key' => MDJM_PAYMENTS_KEY,
									'type' => 'custom_dropdown',
									'value' => $mdjm_settings['payments']['default_type'],
									'text' => '',
									'desc' => 'What is the default method of payment? i.e. if you select an event ' . MDJM_BALANCE_LABEL . ' as paid how should we log it?',
									'custom_args' => array (
														'name' =>  MDJM_PAYMENTS_KEY . '[default_type]',
														'sort_order' => '',
														'list_type' => 'defined',
														'list_values' => $sources,
														),
									'section' => 'payment',
									'page' => 'payments',
									),
									
							'form_layout' => array(
									'label' => 'Form Layout:',
									'key' => MDJM_PAYMENTS_KEY,
									'type' => 'custom_dropdown',
									'value' => $mdjm_settings['payments']['form_layout'],
									'text' => '',
									'desc' => 'How do you want the payment form displayed on your page?',
									'custom_args' => array (
														'name' =>  MDJM_PAYMENTS_KEY . '[form_layout]',
														'sort_order' => '',
														'list_type' => 'defined',
														'list_values' => array( 'horizontal' => 'Horizontal',
																				'vertical' => 'Vertical',
																			),
														),
									'section' => 'payment',
									'page' => 'payments',
									),
									
							'payment_label' => array(
									'label' => 'Payment Label:',
									'key' => MDJM_PAYMENTS_KEY,
									'type' => 'text',
									'class' => 'regular-text',
									'value' => ( !empty( $mdjm_settings['payments']['payment_label'] ) ? $mdjm_settings['payments']['payment_label'] : '' ),
									'text' => 'Default is <code>Make a Payment Towards:</code>',
									'desc' => 'Display name of the label shown to users to select the payment they wish to make',
									'section' => 'payment',
									'page' => 'payments',
									),
									
							'enable_tax' => array(
									'label' => 'Enable Taxes?',
									'key' => MDJM_PAYMENTS_KEY,
									'type' => 'checkbox',
									'value' => ( !empty( $mdjm_settings['payments']['enable_tax'] ) ? '1' : '0' ),
									'text' => '',
									'desc' => 'Enable if you need to add taxes to online payments',
									'section' => 'payment',
									'page' => 'payments',
									),
									
							'tax_type' => array(
									'label' => 'Apply Tax As:',
									'key' => MDJM_PAYMENTS_KEY,
									'type' => 'custom_dropdown',
									'class' => 'regular-text',
									'value' => $mdjm_settings['payments']['tax_type'],
									'text' => '',
									'desc' => 'How do you apply tax?',
									'custom_args' => array (
														'name' =>  MDJM_PAYMENTS_KEY . '[pp_tax_type]',
														'sort_order' => '',
														'list_type' => 'defined',
														'list_values' => array( 'percentage' => '% of total',
																				'fixed' => 'Fixed rate',
																			),
														),
									'section' => 'payment',
									'page' => 'payments',
									),
									
							'tax_rate' => array(
									'label' => 'Tax Rate:',
									'key' => MDJM_PAYMENTS_KEY,
									'type' => 'text',
									'class' => 'small-text',
									'value' => ( !empty( $mdjm_settings['payments']['tax_rate'] ) ? $mdjm_settings['payments']['tax_rate'] : '' ),
									'text' => 'Do not enter a currency or percentage symbol',
									'desc' => 'If you apply tax based on a fixed percentage (i.e. VAT) enter the value (i.e 20). For fixed rates, enter the amount in the format 0.00. Taxes will only be applied during checkout',
									'section' => 'payment',
									'page' => 'payments',
									),
		
							'payment_sources' => array(
									'label' => 'Payment Types:',
									'key' => MDJM_PAYMENTS_KEY,
									'type' => 'textarea',
									'class' => 'all-options',
									'value' => $mdjm_settings['payments']['payment_sources'],
									'text' => '',
									'desc' => 'Enter methods of payment. First entry will be the default',
									'section' => 'payment',
									'page' => 'payments',
									),
									
							'enable_paypal' => array(
									'label' => 'Enable PayPal?',
									'key' => MDJM_PAYPAL_KEY,
									'type' => 'checkbox',
									'value' => ( !empty( $mdjm_settings['paypal']['enable'] ) ? '1' : '0' ),
									'text' => '',
									'desc' => 'Enables the use of PayPal standard for client payment collections',
									'section' => 'paypal',
									'page' => 'paypal',
									),
									
							'paypal_email' => array(
									'label' => 'PayPal Email:',
									'key' => MDJM_PAYPAL_KEY,
									'type' => 'email',
									'class' => 'regular-text',
									'value' => $mdjm_settings['paypal']['paypal_email'],
									'text' => '',
									'desc' => 'Your registered PayPal email address is needed before you can take payments via your website',
									'section' => 'paypal',
									'page' => 'paypal',
									),
									
							'redirect_success' => array(
									'label' => 'Redirect Successful Payment To:',
									'key' => MDJM_PAYPAL_KEY,
									'type' => 'custom_dropdown',
									'class' => 'regular-text',
									'value' => $mdjm_settings['paypal']['redirect_success'],
									'text' => '<code>Current Page</code> is the page the Client initiated the payment from',
									'desc' => 'Where do you want your Client redirected to once Payment has completed?',
									'custom_args' => array (
														'name' =>  MDJM_PAYPAL_KEY . '[redirect_success]',
														'sort_order' => 'ASC',
														'selected' => $mdjm_settings['paypal']['redirect_success'],
														'list_type' => 'page',
														'show_option_none' => 'Current Page',
														'option_none_value' => $mdjm_settings['pages']['payments_page'],
														'id' => 'redirect_success',
														),
									'section' => 'paypal',
									'page' => 'paypal',
									),
									
							'redirect_cancel' => array(
									'label' => 'Redirect Cancelled Payment To:',
									'key' => MDJM_PAYPAL_KEY,
									'type' => 'custom_dropdown',
									'class' => 'regular-text',
									'value' => $mdjm_settings['paypal']['redirect_cancel'],
									'text' => '<code>Current Page</code> is the page the Client initiated the payment from',
									'desc' => 'Where do you want your Client redirected to if they cancel the payment?',
									'custom_args' => array (
														'name' =>  MDJM_PAYPAL_KEY . '[redirect_cancel]',
														'sort_order' => 'ASC',
														'selected' => $mdjm_settings['paypal']['redirect_cancel'],
														'list_type' => 'page',
														'show_option_none' => 'Current Page',
														'option_none_value' => $mdjm_settings['pages']['payments_page'],
														'id' => 'redirect_cancel',
														),
									'section' => 'paypal',
									'page' => 'paypal',
									),
									
							'paypal_button' => array(
									'label' => 'Payment Button:',
									'key' => MDJM_PAYPAL_KEY,
									'type' => 'radio',
									'value' => $mdjm_settings['paypal']['paypal_button'],
									'text' => '',
									'desc' => '',
									'custom_args' => array (
														'name' 	  => MDJM_PAYPAL_KEY . '[paypal_button]',
														'values'	=> array(
																			'btn_paynowCC_LG.gif',
																			'btn_paynow_LG.gif',
																			'btn_paynow_SM.gif',
																			),
														),
									'section' => 'paypal',
									'page' => 'paypal',
									),
									
							'enable_sandbox' => array(
									'label' => 'PayPal Sandbox?',
									'key' => MDJM_PAYPAL_KEY,
									'type' => 'checkbox',
									'value' => ( !empty( $mdjm_settings['paypal']['enable_sandbox'] ) ? '1' : '0' ),
									'text' => '',
									'desc' => 'Enable only to test payments. You can sign up for a developer account <a href="https://developer.paypal.com/" target="_blank">here</a>.',
									'section' => 'paypal',
									'page' => 'paypal',
									),
									
							'sandbox_email' => array(
									'label' => 'PayPal Sandbox Email:',
									'key' => MDJM_PAYPAL_KEY,
									'type' => 'email',
									'class' => 'regular-text',
									'value' => $mdjm_settings['paypal']['sandbox_email'],
									'text' => '',
									'desc' => 'If using PayPal Sandbox, enter your sandbox "Facilitator" email here. If not set, your normal PayPal email will be used',
									'section' => 'paypal',
									'page' => 'paypal',
									),
									
							'paypal_debug' => array(
									'label' => 'Debug?',
									'key' => MDJM_PAYPAL_KEY,
									'type' => 'checkbox',
									'value' => ( !empty( $mdjm_settings['paypal']['paypal_debug'] ) ? '1' : '0' ),
									'text' => 'Enable logging',
									'desc' => 'Enable to capture logs for PayPal - will be stored at <code>' . MDJM_PLUGIN_DIR . 
										'/includes/api/api-log/mdjm-pp-ipn-debug.log</code>',
									'section' => 'paypal_advanced',
									'page' => 'paypal',
									),
									
							'receiver_email' => array(
									'label' => 'Receiver Email Address:',
									'key' => MDJM_PAYPAL_KEY,
									'type' => 'email',
									'class' => 'regular-text',
									'value' => $mdjm_settings['paypal']['receiver_email'],
									'text' => '',
									'desc' => 'This address is used for <a href="https://www.paypal.com/uk/cgi-bin/webscr?cmd=p/acc/ipn-info-outside" ' . 
										'target="_blank" title="Instant Payment Notification (IPN)">PayPal IPN validation</a>. It should be your ' . 
										'<strong><code>primary</code></strong> PayPal email address',
									'section' => 'paypal_advanced',
									'page' => 'paypal',
									),
																		
							'checkout_style' => array(
									'label' => 'Checkout Page Style:',
									'key' => MDJM_PAYPAL_KEY,
									'type' => 'text',
									'class' => 'regular-text',
									'value' => ( !empty( $mdjm_settings['paypal']['checkout_style'] ) ? $mdjm_settings['paypal']['checkout_style'] : '' ),
									'text' => '',
									'desc' => 'If you have created a custom <a href="https://www.paypal.com/customize" target="_blank" title="PayPal\'s Custom Payment Pages: An Overview">PayPal Checkout Page</a>, enter it\'s ID here to use it',
									'section' => 'paypal_advanced',
									'page' => 'paypal',
									)
									
							);		
?>