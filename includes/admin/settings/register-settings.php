<?php

/**
 * Contains all metabox functions for the mdjm-event post type
 *
 * @package		MDJM
 * @subpackage	Admin/Settings
 * @since		1.3
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) )
	exit;

/**
 * Get an option
 *
 * Looks to see if the specified setting exists, returns default if not
 *
 * @since	1.3
 * @param	str		$key		The option key to retrieve.
 * @param	str		$default	What should be returned if the option is empty.
 * @return	mixed
 */
function mdjm_get_option( $key = '', $default = false )	{
	global $mdjm_options;
	
	$value = ! empty( $mdjm_options[ $key ] ) ? $mdjm_options[ $key ] : $default;
	$value = apply_filters( 'mdjm_get_option', $value, $key, $default );
	
	return apply_filters( 'mdjm_get_option' . $key, $value, $key, $default );
} // mdjm_get_option

/**
 * Get Settings
 *
 * Retrieves all plugin settings
 *
 * @since	1.3
 * @return	arr		MDJM settings
 */
function mdjm_get_settings() {

	$settings = get_option( 'mdjm_settings' );

	if( empty( $settings ) ) {
		// Update old settings with new single option
		$main_settings         = is_array( get_option( 'mdjm_plugin_settings' ) )       ? get_option( 'mdjm_plugin_settings' )       : array();
		$email_settings        = is_array( get_option( 'mdjm_email_settings' ) )        ? get_option( 'mdjm_email_settings' )        : array();
		$templates_settings    = is_array( get_option( 'mdjm_templates_settings' ) )    ? get_option( 'mdjm_templates_settings' )    : array();
		$events_settings       = is_array( get_option( 'mdjm_event_settings' ) )        ? get_option( 'mdjm_event_settings' )        : array();
		$playlist_settings     = is_array( get_option( 'mdjm_playlist_settings' ) )     ? get_option( 'mdjm_playlist_settings' )     : array();
		$custom_text_settings  = is_array( get_option( 'mdjm_frontend_text' ) )         ? get_option( 'mdjm_frontend_text' )         : array();
		$clientzone_settings   = is_array( get_option( 'mdjm_clientzone_settings' ) )   ? get_option( 'mdjm_clientzone_settings' )   : array();
		$availability_settings = is_array( get_option( 'mdjm_availability_settings' ) ) ? get_option( 'mdjm_availability_settings' ) : array();
		$pages_settings        = is_array( get_option( 'mdjm_plugin_pages' ) )          ? get_option( 'mdjm_plugin_pages' )          : array();
		$payments_settings     = is_array( get_option( 'mdjm_payment_settings' ) )      ? get_option( 'mdjm_payment_settings' )      : array();
		$permissions_settings  = is_array( get_option( 'mdjm_plugin_permissions' ) )    ? get_option( 'mdjm_plugin_permissions' )    : array();
		$api_settings          = is_array( get_option( 'mdjm_api_data' ) )              ? get_option( 'mdjm_api_data' )              : array();
		$uninstall_settings    = is_array( get_option( 'mdjm_uninst' ) )                ? get_option( 'mdjm_uninst' )                : array();
		
		$settings = array_merge(
			$main_settings,
			$email_settings,
			$templates_settings,
			$events_settings,
			$playlist_settings,
			$custom_text_settings,
			$clientzone_settings,
			$availability_settings,
			$pages_settings,
			$payments_settings,
			$permissions_settings,
			$api_settings,
			$uninstall_settings
		);

		update_option( 'mdjm_settings', $settings );
	}
	
	return apply_filters( 'mdjm_get_settings', $settings );
} // mdjm_get_settings

/**
 * Retrieve the array of plugin settings
 *
 * @since	1.3
 * @return	array
*/
function mdjm_get_registered_settings()	{
	/**
	 * 'Whitelisted' EDD settings, filters are provided for each settings
	 * section to allow extensions and other plugins to add their own settings
	 */
	$mdjm_settings = array(
		/** General Settings */
		'general' => apply_filters( 'mdjm_settings_general',
			array(
				'main' => array(
					'general_settings' => array(
						'id'          => 'general_settings',
						'name'        => '<h3>' . __( 'General Settings', 'mobile-dj-manager' ) . '</h3>',
						'desc'        => '',
						'type'        => 'header',
					),
					'company_name'     => array(
						'id'          => 'company_name',
						'name'        => __( 'Company Name', 'mobile-dj-manager' ),
						'desc'        => __( 'Your company name.', 'mobile-dj-manager' ),
						'type'        => 'text',
						'std'         => get_bloginfo( 'name' )
					),
					'time_format'      => array(
						'id'      => 'time_format',
						'name'    => __( 'Time Format', 'mobile-dj-manager' ),
						'desc'    => __( 'Select the format in which you want your event times displayed. Applies to both admin and client pages', 'mobile-dj-manager' ),
						'type'    => 'select',
						'options' => array(
							'g:i A'	=> date( 'g:i A', current_time( 'timestamp' ) ),
							'H:i'      => date( 'H:i', current_time( 'timestamp' ) ),
						),
						'std'     => 'H:i'
					),
					'short_date_format'      => array(
						'id'      => 'short_date_format',
						'name'    => __( 'Short Date Format', 'mobile-dj-manager' ),
						'desc'    => __( 'Select the format in which you want short dates displayed. Applies to both admin and client pages', 'mobile-dj-manager' ),
						'type'    => 'select',
						'options' => array(
							'd/m/Y'     => date( 'd/m/Y' ) . ' - d/m/Y',
							'm/d/Y'     => date( 'm/d/Y' ) . ' - m/d/Y',
							'Y/m/d'     => date( 'Y/m/d' ) . ' - Y/m/d',
							'd-m-Y'     => date( 'd-m-Y' ) . ' - d-m-Y',
							'm-d-Y'     => date( 'm-d-Y' ) . ' - m-d-Y',
							'Y-m-d'     => date( 'Y-m-d' ) . ' - Y-m-d'
						),
						'std'     => 'd/m/Y'
					),
					'show_credits'         => array(
						'id'      => 'show_credits',
						'name'    => __( 'Display Credits?', 'mobile-dj-manager' ),
						'desc'    => sprintf( __( 'Whether or not to display the %sPowered by ' . 
										'%s, version %s%s text at the footer of the %s application pages.', 'mobile-dj-manager' ), 
										'<span class="mdjm-admin-footer"', MDJM_NAME, MDJM_VERSION_NUM, '</span>', mdjm_get_option( 'app_name', __( 'Client Zone', 'mobile-dj-manager' ) ) ),
						'type'    => 'checkbox',
					)
				),
				'debugging' => array(
					'debugging_settings'   => array(
						'id'          => 'debugging_settings',
						'name'        => '<h3>' . __( 'Debugging MDJM', 'mobile-dj-manager' ) . '</h3>',
						'desc'        => '',
						'type'        => 'header',
					),
					'enable_debugging'     => array(
						'id'          => 'enable_debugging',
						'name'        => __( 'Enable Debugging', 'mobile-dj-manager' ),
						'desc'        => __( 'Only enable if MDJM Support have asked you to do so. Performance may be impacted', 'mobile-dj-manager' ),
						'type'        => 'checkbox',
					),
					'log_size'             => array(
						'id'          => 'log_size',
						'name'        => __( 'Maximum Log File Size', 'mobile-dj-manager' ),
						'text'        => sprintf( __( 'MB %sDefault is 2 (MB)%s', 'mobile-dj-manager' ), '<code>', '</code>' ),
						'desc'        => __( 'The max size in Megabytes to allow your log files to grow to before you receive a warning (if configured below)', 
										'mobile-dj-manager' ),
						'type'        => 'text',
						'size'        => 'small',
						'std'         => '2'
					),
					'warn'                 => array(
						'id'          => 'warn',
						'name'        => __( 'Display Warning if Over Size', 'mobile-dj-manager' ),
						'desc'        => __( 'Will display notice and allow removal and recreation of log files', 'mobile-dj-manager' ),
						'type'        => 'checkbox',
						'std'         => '1'
					),
					'auto_purge'           => array(
						'id'          => 'auto_purge',
						'name'        => __( 'Auto Purge Log Files', 'mobile-dj-manager' ),
						'desc'        => sprintf( __( 'If selected, log files will be auto-purged when they reach the value of %sMaximum Log File Size%s',
										'mobile-dj-manager' ), '<code>', '</code>' ),
						'type'        => 'checkbox'
					)
				),
				'uninstall' => array(
					'uninst_settings'   => array(
						'id'          => 'uninst_settings',
						'name'        => '<h3>' . __( 'Uninstallation Settings', 'mobile-dj-manager' ) . '</h3>',
						'desc'        => '',
						'type'        => 'header',
					),
					'uninst_remove_db'  => array(
						'id'          => 'uninst_remove_db',
						'name'        => __( 'Remove Database Tables', 'mobile-dj-manager' ),
						'desc'        =>  __( 'Should the database tables and data be removed when uninstalling the plugin? ' . 
										'Cannot be recovered unless you or your host have a backup solution in place and a recent backup.', 'mobile-dj-manager' ),
						'type'        => 'checkbox'
					),
					'uninst_remove_mdjm_posts'  => array(
						'id'          => 'uninst_remove_mdjm_posts',
						'name'        => __( 'Remove Data?', 'mobile-dj-manager' ),
						'desc'        =>  __( 'Do you want to remove all MDJM pages', 'mobile-dj-manager' ),
						'type'        => 'checkbox'
					),
					'uninst_remove_mdjm_pages'  => array(
						'id'          => 'uninst_remove_mdjm_pages',
						'name'        => __( 'Remove Pages?', 'mobile-dj-manager' ),
						'desc'        => __( 'Do you want to remove all MDJM pages?', 'mobile-dj-manager' ),
						'type'        => 'checkbox'
					),
					'uninst_remove_users'  => array(
						'id'          => 'uninst_remove_users',
						'name'        => __( 'Remove Employees and Clients?', 'mobile-dj-manager' ),
						'desc'        => __( 'If selected, all users who are defined as clients or employees will be removed.', 'mobile-dj-manager' ),
						'type'        => 'checkbox'
					)
				)
			)
		),
		/** Events Settings */
		'events' => apply_filters( 'mdjm_settings_events',
			array(
				'main' => array(
					'event_settings'  => array(
						'id'         => 'event_settings',
						'name'       => '<h3>' . __( 'Event Settings', 'mobile-dj-manager' ) . '</h3>',
						'desc'       => '',
						'type'       => 'header'
					),
					'event_prefix'     => array(
						'id'          => 'event_prefix',
						'name'        => __( 'Event Prefix', 'mobile-dj-manager' ),
						'desc'        => __( 'The prefix you enter here will be added to each unique event, contract and invoice ID', 'mobile-dj-manager' ),
						'type'        => 'text',
						'size'        => 'small'
					),
					'employer'         => array(
						'id'          => 'employer',
						'name'        =>  __( 'I am an Employer', 'mobile-dj-manager' ),
						'desc'        => __( 'Check if you employ staff other than yourself.', 'mobile-dj-manager' ),
						'type'        => 'checkbox'
					),
					'artist'           => array(
						'id'          => 'artist',
						'name'        => __( 'Refer to Performers as', 'mobile-dj-manager' ),
						'desc'        => __( 'Change the name of your performers here as necessary.', 'mobile-dj-manager' ),
						'type'        => 'text',
						'size'        => 'regular',
						'std'		 => 'DJ'
					),
					'enable_packages'  => array(
						'id'          => 'enable_packages',
						'name'        => __( 'Enable Packages', 'mobile-dj-manager' ),
						'desc'        => __( 'Check this to enable Equipment Packages & Inventories.', 'mobile-dj-manager' ),
						'type'        => 'checkbox'
					),
					'default_contract' => array(
						'id'          => 'default_contract',
						'name'        => __( 'Time Format', 'mobile-dj-manager' ),
						'desc'        => __( 'Select the format in which you want your event times displayed. Applies to both admin and client pages', 'mobile-dj-manager' ),
						'type'        => 'select',
						'options' => mdjm_list_templates( 'contract' )
					),
					'warn_unattended'  => array(
						'id'          => 'warn_unattended',
						'name'        => __( 'New Enquiry Notification', 'mobile-dj-manager' ),
						'desc'        => __( 'Displays a notification message at the top of the Admin pages to Administrators if there are outstanding Unattended Enquiries.', 'mobile-dj-manager' ),
						'type'        => 'checkbox'
					),
					'enquiry_sources'  => array(
						'id'          => 'enquiry_sources',
						'name'        => __( 'Enquiry Sources', 'mobile-dj-manager' ),
						'desc'        => __( 'Enter possible sources of enquiries. One per line', 'mobile-dj-manager' ),
						'type'        => 'textarea'
					),
					'journaling'       => array(
						'id'          => 'journaling',
						'name'        => __( 'Enable Journaling?', 'mobile-dj-manager' ),
						'desc'        =>__( 'Log and track all client &amp; event actions (recommended).', 'mobile-dj-manager' ),
						'type'        => 'checkbox',
						'std'		 => '1'
					)
				),
				'playlist' => array(
					'playlist_settings' => array(
						'id'          => 'playlist_settings',
						'name'        => '<h3>' . __( 'Playlist Settings', 'mobile-dj-manager' ) . '</h3>',
						'desc'        => '',
						'type'        => 'header'
					),
					'enable_playlists' => array(
						'id'          => 'enable_playlists',
						'name'        => __( 'Enable Event Playlists by Default?', 'mobile-dj-manager' ),
						'desc'        => __( 'Check to enable Client Playlist features by default. Can be overridden per event.', 'mobile-dj-manager' ),
						'type'        => 'checkbox'
					),
					'close'           => array(
						'id'          => 'close',
						'name'        => __( 'Close the Playlist', 'mobile-dj-manager' ),
						'desc'        => sprintf( __( 'Enter %s0%s to never close.', 'mobile-dj-manager' ),
											'<code>',
											'</code>'
										),
						'type'        => 'text',
						'size'        => 'small',
						'std'		 => '5'
					),
					'playlist_cats'    => array(
						'id'          => 'playlist_cats',
						'name'        => __( 'Playlist Song Categories', 'mobile-dj-manager' ),
						'desc'        => __( 'The options clients can select for when songs are to be played when adding to the playlist. One per line.', 'mobile-dj-manager' ),
						'type'        => 'textarea'
					),
					'upload_playlists' => array(
						'id'          => 'upload_playlists',
						'name'        => __( 'Upload Playlists?', 'mobile-dj-manager' ),
						'desc'        => __( 'With this option checked, your playlist information will occasionally be transmitted back to the MDJM servers ' . 
										'to help build an information library. The consolidated list of playlist songs will be freely shared. ' . 
										'Only song, artist and the event type information is transmitted.', 'mobile-dj-manager' ),
						'type'        => 'checkbox'
					)
				)
			)
		),
		/** Events Settings */
		'emails' => apply_filters( 'mdjm_settings_emails',
			array(
				'main' => array(
					'email_settings'   => array(
						'id'          => 'email_settings',
						'name'        => '<h3>' . __( 'Email Settings', 'mobile-dj-manager' ) . '</h3>',
						'desc'        => '',
						'type'        => 'header'
					),
					'system_email'     => array(
						'id'          => 'system_email',
						'name'        => __( 'Default From Address', 'mobile-dj-manager' ),
						'desc'        => __( 'The email address you want generic emails from MDJM to come from.', 'mobile-dj-manager' ),
						'type'        => 'text',
						'size'        => 'regular',
						'std'         => get_bloginfo( 'admin_email' )
					),
					'track_client_emails' => array(
						'id'          => 'track_client_emails',
						'name'        => __( 'Track Client Emails?', 'mobile-dj-manager' ),
						'desc'        => sprintf( __( '%sNote%s: not all email clients will support this', 'mobile-dj-manager' ),
											'<code>',
											'</code>'
										),
											
						'type'        => 'checkbox'
					),
					'bcc_dj_to_client' => array(
						'id'          => 'bcc_dj_to_client',
						'name'        => sprintf( __( 'Copy %s in Client Emails?', 'mobile-dj-manager' ), mdjm_get_option( 'artist', __( 'DJ', 'mobile-dj-manager' ) ) ),
						'desc'        => sprintf( __( 'Send a copy of client emails to the events primary %s', 'mobile-dj-manager' ),
											mdjm_get_option( 'artist', __( 'DJ', 'mobile-dj-manager' ) )
										),
											
						'type'        => 'checkbox'
					),
					'bcc_admin_to_client' => array(
						'id'          => 'bcc_admin_to_client',
						'name'        => __( 'Copy Admin in Client Emails?', 'mobile-dj-manager' ),
						'desc'        => sprintf( __( 'Send a copy of client emails to %sDefault From Address%s', 'mobile-dj-manager' ),
											'<code>',
											'</code>'
										),
						'type'        => 'checkbox'
					)
				),
				'templates' => array(
					'quote_templates'   => array(
						'id'          => 'quote_templates',
						'name'        => '<h3>' . __( 'Quote Template Settings', 'mobile-dj-manager' ) . '</h3>',
						'desc'        => '',
						'type'        => 'header'
					),
					'enquiry'          => array(
						'id'          => 'enquiry',
						'name'        => __( 'Quote Template', 'mobile-dj-manager' ),
						'desc'        => __( 'This is the default template used when sending quotes via email to clients', 'mobile-dj-manager' ),
						'type'        => 'select',
						'options'     => mdjm_list_templates( 'email_template' )
					),
					'online_enquiry'   => array(
						'id'          => 'online_enquiry',
						'name'        => __( 'Online Quote Template', 'mobile-dj-manager' ),
						'desc'        => sprintf( __( 'This is the default template used for clients viewing quotes online via the %s.', 'mobile-dj-manager' ), 
											mdjm_get_option( 'app_name', __( 'Client Zone', 'mobile-dj-manager' ) ) ),
						'type'        => 'select',
						'options'     => array_merge( 
											array( '0' => __( 'Disable Online Quotes', 'mobile-dj-manager' ) ),
											mdjm_list_templates( 'email_template' ) )
					),
					'unavailable'      => array(
						'id'          => 'unavailable',
						'name'        => __( 'Unavailability Template', 'mobile-dj-manager' ),
						'desc'        => __( 'This is the default template used when responding to enquiries that you are unavailable for the event', 'mobile-dj-manager' ),
						'type'        => 'select',
						'options'     => mdjm_list_templates( 'email_template' )
					),
					'enquiry_from'     => array(
						'id'          => 'enquiry_from',
						'name'        => __( 'Emails From?', 'mobile-dj-manager' ),
						'desc'        => __( 'Who should enquiries and unavailability emails to be sent by?', 'mobile-dj-manager' ),
						'type'        => 'select',
						'options'     => array(
							'admin'   => __( 'Admin', 'mobile-dj-manager' ),
							'dj'      => mdjm_get_option( 'artist', __( 'DJ', 'mobile-dj-manager' ) )
						)
					),
					'contract_templates' => array(
						'id'          => 'contract_templates',
						'name'        => '<h3>' . __( 'Awaiting Contract Template Settings', 'mobile-dj-manager' ) . '</h3>',
						'desc'        => '',
						'type'        => 'header'
					),
					'contract_to_client' => array(
						'id'          => 'contract_to_client',
						'name'        => __( 'Contract Notification Email?', 'mobile-dj-manager' ),
						'desc'        => sprintf( __( 'Do you want to auto send an email to the client when their event changes to the %sAwaiting Contract%s status?', 'mobile-dj-manager' ), '<em>', '</em>' ),
						'type'        => 'checkbox'
					),
					'contract'         => array(
						'id'          => 'contract',
						'name'        => __( 'Contract Template', 'mobile-dj-manager' ),
						'desc'        => sprintf( __( 'Only applies if %sContract Notification Email%s is enabled', 'mobile-dj-manager' ), '<em>', '</em>' ),
						'type'        => 'select',
						'options'     => mdjm_list_templates( 'email_template' )
					),
					'contract_from'    => array(
						'id'          => 'contract_from',
						'name'        => __( 'Emails From?', 'mobile-dj-manager' ),
						'desc'        => __( 'Who should contract notification emails to be sent by?', 'mobile-dj-manager' ),
						'type'        => 'select',
						'options'     => array(
							'admin'   => __( 'Admin', 'mobile-dj-manager' ),
							'dj'      => mdjm_get_option( 'artist', __( 'DJ', 'mobile-dj-manager' ) )
						)
					),
					'booking_conf_templates' => array(
						'id'          => 'booking_conf_templates',
						'name'        => '<h3>' . __( 'Booking Confirmation Template Settings', 'mobile-dj-manager' ) . '</h3>',
						'desc'        => '',
						'type'        => 'header'
					),
					'booking_conf_to_client' => array(
						'id'          => 'booking_conf_to_client',
						'name'        => __( 'Booking Confirmation to Client', 'mobile-dj-manager' ),
						'desc'        => __( 'Email client with selected template when booking is confirmed i.e. contract accepted, or status changed to Approved', 'mobile-dj-manager' ),
						'type'        => 'checkbox'
					),
					'booking_conf_client' => array(
						'id'          => 'booking_conf_client',
						'name'        => __( 'Client Booking Confirmation Template', 'mobile-dj-manager' ),
						'desc'        => __( 'Select an email template to be used when sending the Booking Confirmation to Clients', 'mobile-dj-manager' ),
						'type'        => 'select',
						'options'     => mdjm_list_templates( 'email_template' )
					),
					'booking_conf_from' => array(
						'id'          => 'booking_conf_from',
						'name'        => __( 'Emails From?', 'mobile-dj-manager' ),
						'desc'        => __( 'Who should booking confirmation emails to be sent by?', 'mobile-dj-manager' ),
						'type'        => 'select',
						'options'     => array(
							'admin'   => __( 'Admin', 'mobile-dj-manager' ),
							'dj'      => mdjm_get_option( 'artist', __( 'DJ', 'mobile-dj-manager' ) )
						)
					),
					'booking_conf_to_dj' => array(
						'id'          => 'booking_conf_to_dj',
						'name'        => __( 'Booking Confirmation to Employee?', 'mobile-dj-manager' ),
						'desc'        => sprintf( __( 'Email events primary %s with selected template when booking is confirmed i.e. contract accepted, or status changed to Approved', 'mobile-dj-manager' ), mdjm_get_option( 'artist', __( 'DJ', 'mobile-dj-manager' ) ) ),
						'type'        => 'checkbox'
					),
					'email_dj_confirm' => array(
						'id'          => 'email_dj_confirm',
						'name'        => sprintf( __( '%s Booking Confirmation Template', 'mobile-dj-manager' ), mdjm_get_option( 'artist', __( 'DJ', 'mobile-dj-manager' ) ) ),
						'desc'        => sprintf( __( 'Select an email template to be used when sending the Booking Confirmation to events primary %s', 'mobile-dj-manager' ), mdjm_get_option( 'artist', __( 'DJ', 'mobile-dj-manager' ) ) ),
						'type'        => 'select',
						'options'     => mdjm_list_templates( 'email_template' )
					),
					'payment_cfm_template' => array(
						'id'          => 'payment_cfm_template',
						'name'        => __( 'Payment Received Template', 'mobile-dj-manager' ),
						'desc'        => __( 'Select an email template to be sent to clients when confirming receipt of a payment', 'mobile-dj-manager' ),
						'type'        => 'select',
						'options'     => mdjm_list_templates( 'email_template' )
					),
					'manual_payment_cfm_template' => array(
						'id'          => 'manual_payment_cfm_template',
						'name'        => __( 'Manual Payment Template', 'mobile-dj-manager' ),
						'desc'        => __( 'Select an email template to be sent to clients when you manually mark an event payment as received', 'mobile-dj-manager' ),
						'type'        => 'select',
						'options'     => mdjm_list_templates( 'email_template' )
					),
				)
			)
		)
	);
} // mdjm_get_registered_settings

/**
 * Return a list of templates for use as dropdown options within a select list.
 *
 * @since	1.3
 * @param	str		$post_type	Optional: 'contract' or 'email_template'. If omitted, fetch both.
 * @return	arr		Array of templates, id => title.
 */
function mdjm_list_templates( $post_type=array( 'contract', 'email_template' ) )	{
	$template_posts = get_posts(
		array(
			'post_type'        => $post_type,
			'post_status'      => 'publish',
			'posts_per_page'   => -1,
			'orderby'          => 'post_title',
			'order'            => 'ASC'
		)
	);
	
	$templates = array();
	
	foreach( $template_posts as $template )	{
		$templates[ $template->ID ] = get_the_title( $template->ID );	
	}
	
	return $templates;
} // mdjm_list_templates
