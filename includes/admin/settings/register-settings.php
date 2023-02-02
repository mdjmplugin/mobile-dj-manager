<?php
/**
 * @author: Mike Howard, Jack Mawhinney, Dan Porter
 *
 * MDJM Settings API
 *
 * @package     MDJM
 * @subpackage  Admin/Settings
 * @since       1.3
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Get an option
 *
 * Looks to see if the specified setting exists, returns default if not
 *
 * @since   1.3
 * @param   str $key        The option key to retrieve.
 * @param   str $default    What should be returned if the option is empty.
 * @return  mixed
 */
function mdjm_get_option( $key = '', $default = false ) {
	global $mdjm_options;

	$value = ! empty( $mdjm_options[ $key ] ) ? $mdjm_options[ $key ] : $default;
	$value = apply_filters( 'mdjm_get_option', $value, $key, $default );

	return apply_filters( "mdjm_get_option_{$key}", $value, $key, $default );
} // mdjm_get_option

/**
 * Update an option
 *
 * Updates an mdjm setting value in both the db and the global variable.
 * Warning: Passing in an empty, false or null string value will remove
 *          the key from the mdjm_options array.
 *
 * @since 1.3
 * @param string          $key The Key to update
 * @param string|bool|int $value The value to set the key to
 * @return boolean True if updated, false if not.
 */
function mdjm_update_option( $key = '', $value = false ) {

	// If no key, exit
	if ( empty( $key ) ) {
		return false;
	}

	if ( empty( $value ) ) {
		$remove_option = mdjm_delete_option( $key );
		return $remove_option;
	}

	// First let's grab the current settings
	$options = get_option( 'mdjm_settings' );

	// Let's let devs alter that value coming in
	$value = apply_filters( 'mdjm_update_option', $value, $key );
	$value = apply_filters( "mdjm_update_option_{$key}", $value );

	// Next let's try to update the value
	$options[ $key ] = $value;
	$did_update      = update_option( 'mdjm_settings', $options );

	// If it updated, let's update the global variable
	if ( $did_update ) {
		global $mdjm_options;
		$mdjm_options[ $key ] = $value;
	}

	return $did_update;

} // mdjm_update_option

/**
 * Remove an option
 *
 * Removes an mdjm setting value in both the db and the global variable.
 *
 * @since   1.3
 * @param   str $key    The Key to delete
 * @return  bool    True if updated, false if not.
 */
function mdjm_delete_option( $key = '' ) {

	// If no key, exit
	if ( empty( $key ) ) {
		return false;
	}

	// First let's grab the current settings
	$options = get_option( 'mdjm_settings' );

	// Next let's try to update the value
	if ( isset( $options[ $key ] ) ) {

		unset( $options[ $key ] );

	}

	$did_update = update_option( 'mdjm_settings', $options );

	// If it updated, let's update the global variable
	if ( $did_update ) {
		global $mdjm_options;
		$mdjm_options = $options;
	}

	return $did_update;

} // mdjm_delete_option

/**
 * Get Settings
 *
 * Retrieves all plugin settings
 *
 * @since   1.3
 * @return  arr     MDJM settings
 */
function mdjm_get_settings() {

	$settings = get_option( 'mdjm_settings' );

	if ( empty( $settings ) ) {
		// Update old settings with new single option
		$main_settings         = is_array( get_option( 'mdjm_plugin_settings' ) ) ? get_option( 'mdjm_plugin_settings' ) : array();
		$email_settings        = is_array( get_option( 'mdjm_email_settings' ) ) ? get_option( 'mdjm_email_settings' ) : array();
		$templates_settings    = is_array( get_option( 'mdjm_templates_settings' ) ) ? get_option( 'mdjm_templates_settings' ) : array();
		$events_settings       = is_array( get_option( 'mdjm_event_settings' ) ) ? get_option( 'mdjm_event_settings' ) : array();
		$playlist_settings     = is_array( get_option( 'mdjm_playlist_settings' ) ) ? get_option( 'mdjm_playlist_settings' ) : array();
		$custom_text_settings  = is_array( get_option( 'mdjm_frontend_text' ) ) ? get_option( 'mdjm_frontend_text' ) : array();
		$clientzone_settings   = is_array( get_option( 'mdjm_clientzone_settings' ) ) ? get_option( 'mdjm_clientzone_settings' ) : array();
		$availability_settings = is_array( get_option( 'mdjm_availability_settings' ) ) ? get_option( 'mdjm_availability_settings' ) : array();
		$pages_settings        = is_array( get_option( 'mdjm_plugin_pages' ) ) ? get_option( 'mdjm_plugin_pages' ) : array();
		$payments_settings     = is_array( get_option( 'mdjm_payment_settings' ) ) ? get_option( 'mdjm_payment_settings' ) : array();
		$permissions_settings  = is_array( get_option( 'mdjm_plugin_permissions' ) ) ? get_option( 'mdjm_plugin_permissions' ) : array();
		$api_settings          = is_array( get_option( 'mdjm_api_data' ) ) ? get_option( 'mdjm_api_data' ) : array();
		$ext_settings          = is_array( get_option( 'mdjm_settings_extensions' ) ) ? get_option( 'mdjm_settings_extensions' ) : array();
		$license_settings      = is_array( get_option( 'mdjm_settings_licenses' ) ) ? get_option( 'mdjm_settings_licenses' ) : array();
		$uninstall_settings    = is_array( get_option( 'mdjm_uninst' ) ) ? get_option( 'mdjm_uninst' ) : array();

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
			$ext_settings,
			$license_settings,
			$uninstall_settings
		);

		update_option( 'mdjm_settings', $settings );
	}

	return apply_filters( 'mdjm_get_settings', $settings );
} // mdjm_get_settings

/**
 * Add all settings sections and fields
 *
 * @since   1.3
 * @return  void
 */
function mdjm_register_settings() {

	if ( false == get_option( 'mdjm_settings' ) ) {
		add_option( 'mdjm_settings' );
	}

	foreach ( mdjm_get_registered_settings() as $tab => $sections ) {
		foreach ( $sections as $section => $settings ) {
			// Check for backwards compatibility
			$section_tabs = mdjm_get_settings_tab_sections( $tab );

			if ( ! is_array( $section_tabs ) || ! array_key_exists( $section, $section_tabs ) ) {
				$section  = 'main';
				$settings = $sections;
			}

			add_settings_section(
				'mdjm_settings_' . $tab . '_' . $section,
				__return_null(),
				'__return_false',
				'mdjm_settings_' . $tab . '_' . $section
			);

			foreach ( $settings as $option ) {
				// For backwards compatibility
				if ( empty( $option['id'] ) ) {
					continue;
				}

				$args = wp_parse_args(
					$option,
					array(
						'section'       => $section,
						'id'            => null,
						'desc'          => '',
						'hint'          => '',
						'name'          => '',
						'size'          => null,
						'options'       => '',
						'std'           => '',
						'min'           => null,
						'max'           => null,
						'step'          => null,
						'chosen'        => null,
						'placeholder'   => null,
						'allow_blank'   => true,
						'readonly'      => false,
						'faux'          => false,
						'tooltip_title' => false,
						'tooltip_desc'  => false,
						'field_class'   => '',
					)
				);

				$name = isset( $option['name'] ) ? $option['name'] : '';

				add_settings_field(
					'mdjm_settings[' . $option['id'] . ']',
					$args['name'],
					function_exists( 'mdjm_' . $option['type'] . '_callback' ) ? 'mdjm_' . $option['type'] . '_callback' : 'mdjm_missing_callback',
					'mdjm_settings_' . $tab . '_' . $section,
					'mdjm_settings_' . $tab . '_' . $section,
					$args
				);
			}
		}
	}

	// Creates our settings in the options table
	register_setting( 'mdjm_settings', 'mdjm_settings', 'mdjm_settings_sanitize' );
} // mdjm_register_settings
add_action( 'admin_init', 'mdjm_register_settings' );

/**
 * Retrieve the array of plugin settings
 *
 * @since   1.3
 * @return  array
 */
function mdjm_get_registered_settings() {

	$absence_tip = sprintf( __( 'Absence: %s', 'mobile-dj-manager' ), '{employee_name}' );

	$absence_content  = sprintf( __( 'From: %s', 'mobile-dj-manager' ), '{start}' ) . PHP_EOL;
	$absence_content .= sprintf( __( 'To: %s', 'mobile-dj-manager' ), '{end}' ) . PHP_EOL;
	$absence_content .= '{notes}';

	$event_title = '{event_type} ({event_status})';

	$event_tip_title = mdjm_get_label_singular() . ' {contract_id} - {event_type}';

	$event_content  = sprintf( __( 'Status: %s', 'mobile-dj-manager' ), '{event_status}' ) . PHP_EOL;
	$event_content .= sprintf( __( 'Date: %s', 'mobile-dj-manager' ), '{event_date}' ) . PHP_EOL;
	$event_content .= sprintf( __( 'Start: %s', 'mobile-dj-manager' ), '{start_time}' ) . PHP_EOL;
	$event_content .= sprintf( __( 'Finish: %s', 'mobile-dj-manager' ), '{end_time}' ) . PHP_EOL;
	$event_content .= sprintf( __( 'Setup: %s', 'mobile-dj-manager' ), '{dj_setup_time}' ) . PHP_EOL;
	$event_content .= sprintf( __( 'Cost: %s', 'mobile-dj-manager' ), '{total_cost}' ) . PHP_EOL;
	$event_content .= sprintf( __( 'Employees: %s', 'mobile-dj-manager' ), '{event_employees}' ) . PHP_EOL;

	/**
	 * 'Whitelisted' MDJM settings, filters are provided for each settings
	 * section to allow extensions and other plugins to add their own settings
	 */
	$mdjm_settings = array(
		/** General Settings */
		'general'          => apply_filters(
			'mdjm_settings_general',
			array(
				'main'      => array(
					'general_settings'    => array(
						'id'   => 'general_settings',
						'name' => '<h3>' . __( 'General Settings', 'mobile-dj-manager' ) . '</h3>',
						'desc' => '',
						'type' => 'header',
					),
					'company_name'        => array(
						'id'   => 'company_name',
						'name' => __( 'Company Name', 'mobile-dj-manager' ),
						'desc' => __( 'Your company name.', 'mobile-dj-manager' ),
						'type' => 'text',
						'std'  => get_bloginfo( 'name' ),
					),
					'time_format'         => array(
						'id'      => 'time_format',
						'name'    => __( 'Time Format', 'mobile-dj-manager' ),
						'desc'    => sprintf( __( 'Select the format in which you want your %s times displayed. Applies to both admin and client pages', 'mobile-dj-manager' ), mdjm_get_label_singular( true ) ),
						'type'    => 'select',
						'options' => array(
							'g:i A' => date( 'g:i A', current_time( 'timestamp' ) ),
							'H:i'   => date( 'H:i', current_time( 'timestamp' ) ),
						),
						'std'     => 'H:i',
					),
					'short_date_format'   => array(
						'id'      => 'short_date_format',
						'name'    => __( 'Short Date Format', 'mobile-dj-manager' ),
						'desc'    => __( 'Select the format in which you want short dates displayed. Applies to both admin and client pages', 'mobile-dj-manager' ),
						'type'    => 'select',
						'options' => array(
							'd/m/Y' => date( 'd/m/Y' ) . ' - d/m/Y',
							'm/d/Y' => date( 'm/d/Y' ) . ' - m/d/Y',
							'Y/m/d' => date( 'Y/m/d' ) . ' - Y/m/d',
							'd-m-Y' => date( 'd-m-Y' ) . ' - d-m-Y',
							'm-d-Y' => date( 'm-d-Y' ) . ' - m-d-Y',
							'Y-m-d' => date( 'Y-m-d' ) . ' - Y-m-d',
						),
						'std'     => 'd/m/Y',
					),
					'unload_fontawesome'  => array(
						'id'   => 'unload_fontawesome',
						'name' => __( 'Unload Font Awesome?', 'mobile-dj-manager' ),
						'desc' => sprintf( 'MDJM loads the latest Font Awesome, which can sometimes cause issues with older versions. Check this to stop loading Font Awesome in the plugin. <strong>Please use with caution, may cause unexpected results</strong>' ),
						'type' => 'checkbox',
					),
					'show_credits'        => array(
						'id'   => 'show_credits',
						'name' => __( 'Display Credits?', 'mobile-dj-manager' ),
						'desc' => sprintf(
							__(
								'Whether or not to display the %sPowered by ' .
								'%s, version %s%s text at the footer of the %s application pages.',
								'mobile-dj-manager'
							),
							'<span class="mdjm-admin-footer">',
							MDJM_NAME,
							MDJM_VERSION_NUM,
							'</span>',
							mdjm_get_application_name()
						),
						'type' => 'checkbox',
					),
					'remove_on_uninstall' => array(
						'id'   => 'remove_on_uninstall',
						'name' => __( 'Remove Data on Uninstall?', 'mobile-dj-manager' ),
						'desc' => __( 'Check this box if you would like MDJM to completely remove all of its data when the plugin is deleted.', 'mobile-dj-manager' ),
						'type' => 'checkbox',
					),
				),
				'debugging' => array(
					'debugging_settings' => array(
						'id'   => 'debugging_settings',
						'name' => '<h3>' . __( 'Debugging MDJM', 'mobile-dj-manager' ) . '</h3>',
						'desc' => '',
						'type' => 'header',
					),
					'enable_debugging'   => array(
						'id'   => 'enable_debugging',
						'name' => __( 'Enable Debugging', 'mobile-dj-manager' ),
						'desc' => __( 'Only enable if MDJM Support have asked you to do so. Performance may be impacted', 'mobile-dj-manager' ),
						'type' => 'checkbox',
					),
					'debug_log_size'     => array(
						'id'   => 'debug_log_size',
						'name' => __( 'Maximum Log File Size', 'mobile-dj-manager' ),
						'hint' => sprintf( __( 'MB %1$sDefault is 2 (MB)%2$s', 'mobile-dj-manager' ), '<code>', '</code>' ),
						'desc' => __(
							'The max size in Megabytes to allow your log files to grow to before you receive a warning (if configured below)',
							'mobile-dj-manager'
						),
						'type' => 'text',
						'size' => 'small',
						'std'  => '2',
					),
					'debug_warn'         => array(
						'id'   => 'debug_warn',
						'name' => __( 'Display Warning if Over Size', 'mobile-dj-manager' ),
						'desc' => __( 'Will display notice and allow removal and recreation of log files', 'mobile-dj-manager' ),
						'type' => 'checkbox',
						'std'  => '1',
					),
					'debug_auto_purge'   => array(
						'id'   => 'debug_auto_purge',
						'name' => __( 'Auto Purge Log Files', 'mobile-dj-manager' ),
						'desc' => __( 'If selected, log files will be auto-purged when they reach the value of <code>Maximum Log File Size</code>', 'mobile-dj-manager' ),
						'type' => 'checkbox',
					),
				),
			)
),
/** Events Settings */
'events'           => apply_filters(
	'mdjm_settings_events',
	array(
		'main'     => array(
			'event_settings'      => array(
				'id'   => 'event_settings',
				'name' => '<h3>' . sprintf( __( '%s Settings', 'mobile-dj-manager' ), mdjm_get_label_singular() ) . '</h3>',
				'desc' => '',
				'type' => 'header',
			),
			'event_prefix'        => array(
				'id'   => 'event_prefix',
				'name' => sprintf( __( '%s Prefix', 'mobile-dj-manager' ), mdjm_get_label_singular() ),
				'desc' => sprintf( __( 'The prefix you enter here will be added to each unique %s, contract and invoice ID', 'mobile-dj-manager' ), mdjm_get_label_singular( true ) ),
				'type' => 'text',
				'size' => 'small',
			),
			'show_active_only'    => array(
				'id'   => 'show_active_only',
				'name' => sprintf( __( 'Hide Inactive %s?', 'mobile-dj-manager' ), mdjm_get_label_plural() ),
				'desc' => sprintf( __( 'Select to include only active %1$s within the <code>All</code> view on the %1$s screen.', 'mobile-dj-manager' ), mdjm_get_label_plural( true ) ),
				'type' => 'checkbox',
			),
			'employer'            => array(
				'id'   => 'employer',
				'name' => __( 'I am an Employer', 'mobile-dj-manager' ),
				'desc' => __( 'Check if you employ staff other than yourself.', 'mobile-dj-manager' ),
				'type' => 'checkbox',
			),
			'artist'              => array(
				'id'   => 'artist',
				'name' => __( 'Refer to Performers as', 'mobile-dj-manager' ),
				'hint' => '<code>' . __( 'Default is DJ', 'mobile-dj-manager' ) . '</code>',
				'desc' => __( 'Change the name of your performers here as necessary.', 'mobile-dj-manager' ),
				'type' => 'text',
				'size' => 'regular',
				'std'  => __( 'DJ', 'mobile-dj-manager' ),
			),
			'setup_time'          => array(
				'id'   => 'setup_time',
				'name' => __( 'Setup Time', 'mobile-dj-manager' ),
				'desc' => sprintf(
					__( 'Enter the time in minutes before an %1$s starts that you normally setup to auto define the %2$s setup time and date. <code>0</code> to disable.', 'mobile-dj-manager' ),
					mdjm_get_label_singular( true ),
					mdjm_get_option( 'artist' )
				),
				'type' => 'number',
				'size' => 'small',
				'step' => '15',
				'std'  => 0,
			),
			'default_contract'    => array(
				'id'      => 'default_contract',
				'name'    => __( 'Default Contract', 'mobile-dj-manager' ),
				'desc'    => sprintf( __( 'Select the default contract for your %1$s. Can be changed per %2$s', 'mobile-dj-manager' ), mdjm_get_label_plural( true ), mdjm_get_label_singular( true ) ),
				'type'    => 'select',
				'options' => mdjm_list_templates( 'contract' ),
			),
			'warn_unattended'     => array(
				'id'   => 'warn_unattended',
				'name' => __( 'New Enquiry Notification', 'mobile-dj-manager' ),
				'desc' => __( 'Displays a notification message at the top of the Admin pages to Administrators if there are outstanding Unattended Enquiries.', 'mobile-dj-manager' ),
				'type' => 'checkbox',
				'std'  => '1',
			),
			'events_order_by'     => array(
				'id'      => 'events_order_by',
				'name'    => __( 'Default Order By', 'mobile-dj-manager' ),
				'desc'    => sprintf( __( 'Select how you want to see %1$s ordered within the %2$s admin list', 'mobile-dj-manager' ), mdjm_get_label_plural( true ), mdjm_get_label_singular( true ) ),
				'type'    => 'select',
				'options' => array(
					'ID'         => __( 'Contract ID', 'mobile-dj-manager' ),
					'post_date'  => __( 'Creation Date', 'mobile-dj-manager' ),
					'event_date' => sprintf( __( '%s Date', 'mobile-dj-manager' ), mdjm_get_label_singular() ),
					'value'      => __( 'Total Cost', 'mobile-dj-manager' ),
				),
				'std'     => 'event_date',
			),
			'events_order'        => array(
				'id'      => 'events_order',
				'name'    => __( 'Default Order', 'mobile-dj-manager' ),
				'desc'    => '',
				'type'    => 'select',
				'options' => array(
					'ASC'  => __( 'Ascending', 'mobile-dj-manager' ),
					'DESC' => __( 'Descending', 'mobile-dj-manager' ),
				),
				'std'     => 'DESC',
			),
			'set_client_inactive' => array(
				'id'   => 'set_client_inactive',
				'name' => __( 'Set Client Inactive?', 'mobile-dj-manager' ),
				'desc' => sprintf( __( 'Set a client to inactive when their %1$s is cancelled, rejected or marked as a failed enquiry and they have no other upcoming %2$s.', 'mobile-dj-manager' ), mdjm_get_label_singular( true ), mdjm_get_label_plural( true ) ),
				'type' => 'checkbox',
				'std'  => '1',
			),
			'journaling'          => array(
				'id'   => 'journaling',
				'name' => __( 'Enable Journaling?', 'mobile-dj-manager' ),
				'desc' => sprintf( __( 'Log and track all client &amp; %s actions (recommended).', 'mobile-dj-manager' ), mdjm_get_label_singular() ),
				'type' => 'checkbox',
				'std'  => '1',
			),
		),
'playlist' => array(
	'playlist_settings' => array(
		'id'   => 'playlist_settings',
		'name' => '<h3>' . __( 'Playlist Settings', 'mobile-dj-manager' ) . '</h3>',
		'desc' => '',
		'type' => 'header',
	),
	'enable_playlists'  => array(
		'id'   => 'enable_playlists',
		'name' => sprintf( __( 'Enable %s Playlists by Default?', 'mobile-dj-manager' ), mdjm_get_label_singular() ),
		'desc' => sprintf( __( 'Check to enable Client Playlist features by default. Can be overridden per %s.', 'mobile-dj-manager' ), mdjm_get_label_singular( true ) ),
		'type' => 'checkbox',
		'std'  => '1',
	),
	'playlist_limit'    => array(
		'id'   => 'playlist_limit',
		'name' => __( 'Playlist Limit?', 'mobile-dj-manager' ),
		'desc' => sprintf( __( 'Set the global limit for the number of entries a playlist can contain or enter <code>0</code> for no limit. Can be overridden per %s', 'mobile-dj-manager' ), mdjm_get_label_singular( true ) ),
		'type' => 'number',
		'size' => 'small',
		'std'  => '0',
	),
	'close'             => array(
		'id'   => 'close',
		'name' => __( 'Close the Playlist', 'mobile-dj-manager' ),
		'hint' => sprintf(
			__( 'Enter %1$s0%2$s to never close.', 'mobile-dj-manager' ),
			'<code>',
			'</code>'
		),
		'desc' => sprintf( __( 'Number of days before %s that the playlist should close to new entries.', 'mobile-dj-manager' ), mdjm_get_label_singular( true ) ),
		'type' => 'text',
		'size' => 'small',
		'std'  => '5',
	),
),
				// Packages & Addons
'packages' => array(
	'package_settings'         => array(
		'id'   => 'package_settings',
		'name' => '<h3>' . __( 'Package &amp; Addon Settings', 'mobile-dj-manager' ) . '</h3>',
		'desc' => '',
		'type' => 'header',
	),
	'enable_packages'          => array(
		'id'   => 'enable_packages',
		'name' => __( 'Enable Packages', 'mobile-dj-manager' ),
		'desc' => __( 'Check this to enable Equipment Packages & Add-ons.', 'mobile-dj-manager' ),
		'type' => 'checkbox',
	),
	'package_excerpt_length'   => array(
		'id'   => 'package_excerpt_length',
		'name' => __( 'Description Length', 'mobile-dj-manager' ),
		'desc' => __( 'The maximum number of characters for the package/addon description.', 'mobile-dj-manager' ),
		'hint' => __( 'Entering <code>0</code> will render the full exceprt if it exists, otherwise the description', 'mobile-dj-manager' ),
		'type' => 'number',
		'size' => 'small',
		'step' => '5',
		'std'  => '55',
	),
	'package_contact_btn'      => array(
		'id'   => 'package_contact_btn',
		'name' => __( 'Add Contact Button?', 'mobile-dj-manager' ),
		'hint' => sprintf( __( 'Select to auto add a contact button to the end of package/addons post content. The link will redirect to the <code>Contact Page</code>page as defined in <a href="%s">Pages</a>', 'mobile-dj-manager' ), admin_url( 'admin.php?page=mdjm-settings&tab=client_zone&section=pages' ) ),
		'desc' => sprintf( __( 'If you use the <a href="%s" target="_blank">Dynamic Contact Forms</a> add-on, the package/addon will be auto selected on page load', 'mobile-dj-manager' ), 'http://mdjm.co.uk/products/dynamic-contact-forms/' ),
		'type' => 'checkbox',
	),
	'package_contact_btn_text' => array(
		'id'   => 'package_contact_btn_text',
		'name' => __( 'Text for Contact Button', 'mobile-dj-manager' ),
		'desc' => '',
		'type' => 'text',
		'std'  => __( 'Enquire Now', 'mobile-dj-manager' ),
	),
),
				// Travel
'travel'   => array(
	'travel_settings'     => array(
		'id'   => 'travel_settings',
		'name' => '<h3>' . __( 'Travel Settings', 'mobile-dj-manager' ) . '</h3>',
		'desc' => '',
		'type' => 'header',
	),
	'travel_add_cost'     => array(
		'id'   => 'travel_add_cost',
		'name' => __( 'Add Travel Cost to Price?', 'mobile-dj-manager' ),
		'desc' => sprintf( __( 'If selected, the travel cost will be added to the overall %s cost', 'mobile-dj-manager' ), mdjm_get_label_singular( true ) ),
		'type' => 'checkbox',
	),
	'travel_primary'      => array(
		'id'   => 'travel_primary',
		'name' => __( 'Primary Post/Zip Code', 'mobile-dj-manager' ),
		mdjm_get_label_singular(),
		'desc' => __( 'When the primary employee has no address in their profile, this post code will be used to calculate the distance to the venue.', 'mobile-dj-manager' ),
		'type' => 'text',
		'std'  => mdjm_get_employee_post_code( 1 ),
	),
	'travel_status'       => array(
		'id'      => 'travel_status',
		'name'    => sprintf( __( '%s Status', 'mobile-dj-manager' ), mdjm_get_label_singular() ),
		'desc'    => sprintf( __( "CTRL (cmd on MAC) + Click to select which %s status' can have travel costs updated.", 'mobile-dj-manager' ), mdjm_get_label_singular( true ) ),
		'type'    => 'multiple_select',
		'options' => mdjm_all_event_status(),
		'std'     => array( 'mdjm-unattended', 'mdjm-enquiry', 'mdjm-contract' ),
	),
	'travel_units'        => array(
		'id'      => 'travel_units',
		'name'    => __( 'Calculate in?', 'mobile-dj-manager' ),
		'desc'    => '',
		'type'    => 'select',
		'options' => array(
			'imperial' => __( 'Miles', 'mobile-dj-manager' ),
			'metric'   => __( 'Kilometers', 'mobile-dj-manager' ),
		),
		'std'     => 'imperial',
	),
	'cost_per_unit'       => array(
		'id'   => 'cost_per_unit',
		'name' => sprintf( __( 'Cost per %s', 'mobile-dj-manager' ), mdjm_travel_unit_label() ),
		'desc' => __( 'Enter the cost per mile that should be calculated. i.e. 0.45', 'mobile-dj-manager' ),
		'type' => 'text',
		'size' => 'small',
		'std'  => '0.45',
	),
	'travel_cost_round'   => array(
		'id'      => 'travel_cost_round',
		'name'    => __( 'Round Cost', 'mobile-dj-manager' ),
		'desc'    => __( 'Do you want to round costs up or down?', 'mobile-dj-manager' ),
		'type'    => 'select',
		'options' => array(
			false  => __( 'No', 'mobile-dj-manager' ),
			'up'   => __( 'Up', 'mobile-dj-manager' ),
			'down' => __( 'Down', 'mobile-dj-manager' ),
		),
		'std'     => 'up',
	),
	'travel_round_to'     => array(
		'id'   => 'travel_round_to',
		'name' => __( 'Round to Nearest', 'mobile-dj-manager' ),
		'hint' => mdjm_get_currency() . ' i.e. 5',
		'type' => 'number',
		'size' => 'small',
		'std'  => '5',
	),
	'travel_min_distance' => array(
		'id'   => 'travel_min_distance',
		'name' => __( "Don't add if below", 'mobile-dj-manager' ),
		'hint' => mdjm_travel_unit_label( false, true ),
		'type' => 'number',
		'size' => 'small',
		'std'  => '30',
	),
),
)
),
/** Email Settings */
'emails'           => apply_filters(
	'mdjm_settings_emails',
	array(
		'main'      => array(
			'email_settings'                => array(
				'id'   => 'email_settings',
				'name' => '<h3>' . __( 'Email Settings', 'mobile-dj-manager' ) . '</h3>',
				'desc' => '',
				'type' => 'header',
			),
			'system_email'                  => array(
				'id'   => 'system_email',
				'name' => __( 'Default From Address', 'mobile-dj-manager' ),
				'desc' => __( 'The email address you want generic emails from MDJM to come from.', 'mobile-dj-manager' ),
				'type' => 'text',
				'size' => 'regular',
				'std'  => get_bloginfo( 'admin_email' ),
			),
			'comms_show_active_events_only' => array(
				'id'   => 'comms_show_active_events_only',
				'name' => __( 'Communicate Active Events Only', 'mobile-dj-manager' ),
				'desc' => __( "Check to only retrieve a client's/employee's active events on the communication page.", 'mobile-dj-manager' ),
				'type' => 'checkbox',
			),
			'track_client_emails'           => array(
				'id'   => 'track_client_emails',
				'name' => __( 'Track Client Emails?', 'mobile-dj-manager' ),
				'desc' => __( 'Some email clients may not support this feature.', 'mobile-dj-manager' ),
				'type' => 'checkbox',
				'std'  => '1',
			),
			'bcc_dj_to_client'              => array(
				'id'   => 'bcc_dj_to_client',
				'name' => sprintf( __( 'Copy %s in Client Emails?', 'mobile-dj-manager' ), mdjm_get_option( 'artist', __( 'DJ', 'mobile-dj-manager' ) ) ),
				'desc' => sprintf(
					__( 'Send a copy of client emails to the %1$s primary %2$s', 'mobile-dj-manager' ),
					mdjm_get_label_plural( true ),
					mdjm_get_option( 'artist', __( 'DJ', 'mobile-dj-manager' ) )
				),

				'type' => 'checkbox',
			),
			'bcc_admin_to_client'           => array(
				'id'   => 'bcc_admin_to_client',
				'name' => __( 'Copy Admin in Client Emails?', 'mobile-dj-manager' ),
				'desc' => sprintf(
					__( 'Send a copy of client emails to %1$sDefault From Address%2$s', 'mobile-dj-manager' ),
					'<code>',
					'</code>'
				),
				'type' => 'checkbox',
				'std'  => '1',
			),
		),
		'templates' => array(
			'quote_templates'        => array(
				'id'   => 'quote_templates',
				'name' => '<h3>' . __( 'Quote Template Settings', 'mobile-dj-manager' ) . '</h3>',
				'desc' => '',
				'type' => 'header',
			),
			'enquiry'                => array(
				'id'      => 'enquiry',
				'name'    => __( 'Email Quote Template', 'mobile-dj-manager' ),
				'desc'    => __( 'This is the default template used when sending quotes via email to clients', 'mobile-dj-manager' ),
				'type'    => 'select',
				'chosen'  => true,
				'options' => mdjm_list_templates( 'email_template' ),
			),
			'online_enquiry'         => array(
				'id'      => 'online_enquiry',
				'name'    => __( 'Online Quote Template', 'mobile-dj-manager' ),
				'desc'    => sprintf(
					__( 'This is the default template used for clients viewing quotes online via the %s.', 'mobile-dj-manager' ),
					mdjm_get_application_name()
				),
				'type'    => 'select',
				'chosen'  => true,
				'options' => mdjm_list_templates( 'email_template', true ),
			),
			'unavailable'            => array(
				'id'      => 'unavailable',
				'name'    => __( 'Unavailability Template', 'mobile-dj-manager' ),
				'desc'    => sprintf( __( 'This is the default template used when responding to enquiries that you are unavailable for the %s', 'mobile-dj-manager' ), mdjm_get_label_singular( true ) ),
				'type'    => 'select',
				'chosen'  => true,
				'options' => mdjm_list_templates( 'email_template' ),
			),
			'enquiry_from'           => array(
				'id'      => 'enquiry_from',
				'name'    => __( 'Emails From?', 'mobile-dj-manager' ),
				'desc'    => __( 'Who should enquiries and unavailability emails to be sent by?', 'mobile-dj-manager' ),
				'type'    => 'select',
				'options' => array(
					'admin' => __( 'Admin', 'mobile-dj-manager' ),
					'dj'    => mdjm_get_option( 'artist', __( 'Primary Employee', 'mobile-dj-manager' ) ),
				),
				'chosen'  => true,
				'std'     => 'admin',
			),
			'awaitingdeposit'        => array(
				'id'      => 'awaitingdeposit',
				'name'    => sprintf( __( 'Awaiting %s Template', 'mobile-dj-manager' ), mdjm_get_deposit_label() ),
				'desc'    => sprintf( __( 'Select an email template to be used when sending the %s reminder to the client', 'mobile-dj-manager' ), mdjm_get_deposit_label() ),
				'type'    => 'select',
				'chosen'  => true,
				'options' => mdjm_list_templates( 'email_template' ),
			),
			'contract_templates'     => array(
				'id'   => 'contract_templates',
				'name' => '<h3>' . __( 'Awaiting Contract Template Settings', 'mobile-dj-manager' ) . '</h3>',
				'desc' => '',
				'type' => 'header',
			),
			'contract_to_client'     => array(
				'id'   => 'contract_to_client',
				'name' => __( 'Contract Notification Email?', 'mobile-dj-manager' ),
				'desc' => sprintf( __( 'Do you want to auto send an email to the client when their %s changes to the <em>Awaiting Contract<em> status?', 'mobile-dj-manager' ), mdjm_get_label_singular( true ) ),
				'type' => 'checkbox',
				'std'  => '1',
			),
			'contract'               => array(
				'id'      => 'contract',
				'name'    => __( 'Contract Template', 'mobile-dj-manager' ),
				'desc'    => sprintf( __( 'Only applies if %1$sContract Notification Email%2$s is enabled', 'mobile-dj-manager' ), '<em>', '</em>' ),
				'type'    => 'select',
				'chosen'  => true,
				'options' => mdjm_list_templates( 'email_template' ),
			),
			'contract_from'          => array(
				'id'      => 'contract_from',
				'name'    => __( 'Emails From?', 'mobile-dj-manager' ),
				'desc'    => __( 'Who should contract notification emails to be sent by?', 'mobile-dj-manager' ),
				'type'    => 'select',
				'options' => array(
					'admin' => __( 'Admin', 'mobile-dj-manager' ),
					'dj'    => mdjm_get_option( 'artist', __( 'Primary Employee', 'mobile-dj-manager' ) ),
				),
				'std'     => 'admin',
			),
			'booking_conf_templates' => array(
				'id'   => 'booking_conf_templates',
				'name' => '<h3>' . __( 'Booking Confirmation Template Settings', 'mobile-dj-manager' ) . '</h3>',
				'desc' => '',
				'type' => 'header',
			),
			'booking_conf_to_client' => array(
				'id'   => 'booking_conf_to_client',
				'name' => __( 'Booking Confirmation to Client', 'mobile-dj-manager' ),
				'desc' => __( 'Email client with selected template when booking is confirmed i.e. contract accepted, or status changed to Approved', 'mobile-dj-manager' ),
				'type' => 'checkbox',
				'std'  => '1',
			),
			'booking_conf_client'    => array(
				'id'      => 'booking_conf_client',
				'name'    => __( 'Client Booking Confirmation Template', 'mobile-dj-manager' ),
				'desc'    => __( 'Select an email template to be used when sending the Booking Confirmation to Clients', 'mobile-dj-manager' ),
				'type'    => 'select',
				'chosen'  => true,
				'options' => mdjm_list_templates( 'email_template' ),
			),
			'booking_conf_from'      => array(
				'id'      => 'booking_conf_from',
				'name'    => __( 'Emails From?', 'mobile-dj-manager' ),
				'desc'    => __( 'Who should booking confirmation emails to be sent by?', 'mobile-dj-manager' ),
				'type'    => 'select',
				'options' => array(
					'admin' => __( 'Admin', 'mobile-dj-manager' ),
					'dj'    => mdjm_get_option( 'artist', __( 'Primary Employee', 'mobile-dj-manager' ) ),
				),
				'std'     => 'admin',
			),
			'booking_conf_to_dj'     => array(
				'id'   => 'booking_conf_to_dj',
				'name' => __( 'Booking Confirmation to Employee?', 'mobile-dj-manager' ),
				'desc' => sprintf(
					__( 'Email %1$s primary %2$s with selected template when booking is confirmed i.e. contract accepted, or status changed to Approved', 'mobile-dj-manager' ),
					mdjm_get_label_plural( true ),
					mdjm_get_option( 'artist', __( 'DJ', 'mobile-dj-manager' ) )
				),
				'type' => 'checkbox',
			),
			'email_dj_confirm'       => array(
				'id'      => 'email_dj_confirm',
				'name'    => sprintf( __( '%s Booking Confirmation Template', 'mobile-dj-manager' ), mdjm_get_option( 'artist', __( 'DJ', 'mobile-dj-manager' ) ) ),
				'desc'    => sprintf( __( 'Select an email template to be used when sending the Booking Confirmation to %1$s primary %2$s', 'mobile-dj-manager' ), mdjm_get_label_plural( true ), mdjm_get_option( 'artist', __( 'DJ', 'mobile-dj-manager' ) ) ),
				'type'    => 'select',
				'chosen'  => true,
				'options' => mdjm_list_templates( 'email_template' ),
			),
		),
)
),
/** Client Portal Settings */
'client_zone'      => apply_filters(
	'mdjm_settings_client_zone',
	array(
		'main'         => array(
			'client_zone_settings'       => array(
				'id'   => 'client_zone_settings',
				'name' => '<h3>' . sprintf( __( '%s Settings', 'mobile-dj-manager' ), mdjm_get_application_name() ) . '</h3>',
				'desc' => '',
				'type' => 'header',
			),
			'app_name'                   => array(
				'id'   => 'app_name',
				'name' => __( 'Application Name', 'mobile-dj-manager' ),
				'hint' => sprintf(
					__( 'Default is %1$sClient Portal%2$s.', 'mobile-dj-manager' ),
					'<code>',
					'</code>'
				),
				'desc' => __( 'Choose your own name for the application.', 'mobile-dj-manager' ),
				'type' => 'text',
				'size' => 'regular',
				'std'  => __( 'Client Portal', 'mobile-dj-manager' ),
			),
			'client_settings'            => array(
				'id'   => 'client_settings',
				'name' => '<h3>' . __( 'Client Settings', 'mobile-dj-manager' ) . '</h3>',
				'desc' => '',
				'type' => 'header',
			),
			'pass_length'                => array(
				'id'      => 'pass_length',
				'name'    => __( 'Default Password Length', 'mobile-dj-manager' ),
				'desc'    => sprintf( __( 'If opting to generate or reset a user password during %s creation, how many characters should the password be?', 'mobile-dj-manager' ), mdjm_get_label_singular( true ) ),
				'type'    => 'select',
				'options' => array(
					'5'  => '5',
					'6'  => '6',
					'7'  => '7',
					'8'  => '8',
					'9'  => '9',
					'10' => '10',
					'11' => '11',
					'12' => '12',
				),
				'std'     => '8',
			),
			'complex_passwords'          => array(
				'id'   => 'complex_passwords',
				'name' => __( 'Use Complex Passwords?', 'mobile-dj-manager' ),
				'desc' => __( 'Generated passwords will contain <em>special</em> characters such as <code>!@#$%^&*()</code> as well as letters and numbers', 'mobile-dj-manager' ),
				'type' => 'checkbox',
				'std'  => '1',
			),
			'notify_profile'             => array(
				'id'   => 'notify_profile',
				'name' => __( 'Incomplete Profile Warning?', 'mobile-dj-manager' ),
				'desc' => __( 'Display notice to Clients when they login if their Profile is incomplete? (i.e. Required field is empty)', 'mobile-dj-manager' ),
				'type' => 'checkbox',
			),
			'client_zone_event_settings' => array(
				'id'   => 'client_zone_event_settings',
				'name' => '<h3>' . sprintf( __( '%s Settings', 'mobile-dj-manager' ), mdjm_get_label_singular() ) . '</h3>',
				'desc' => '',
				'type' => 'header',
			),
			'package_prices'             => array(
				'id'   => 'package_prices',
				'name' => __( 'Display Package Price?', 'mobile-dj-manager' ),
				'desc' => sprintf(
					__( 'Select to display %1$s package &amp; Add-on prices within hover text within the %2$s', 'mobile-dj-manager' ),
					mdjm_get_label_singular( true ),
					mdjm_get_application_name()
				),
				'type' => 'checkbox',
			),
		),
		'styles'       => array(
			'client_zone_styles'   => array(
				'id'   => 'client_zone_styles',
				'name' => '<h3>' . __( 'Styling', 'mobile-dj-manager' ) . '</h3>',
				'desc' => '',
				'type' => 'header',
			),
			'action_button_colour' => array(
				'id'      => 'action_button_colour',
				'name'    => __( 'Action Button Colour', 'mobile-dj-manager' ),
				'desc'    => sprintf( __( 'Select your preferred colour for the %s action buttons', 'mobile-dj-manager' ), mdjm_get_label_singular( true ) ),
				'type'    => 'color',
				'default' => '#0000ff',
				'std'	  => '#0000ff',
			),
			'action_button_font_colour' => array(
				'id'      => 'action_button_font_colour',
				'name'    => __( 'Action Button Font Colour', 'mobile-dj-manager' ),
				'desc'    => sprintf( __( 'Select your preferred colour for the %s action buttons font', 'mobile-dj-manager' ), mdjm_get_label_singular( true ) ),
				'type'    => 'color',
				'default' => '#ffffff',
				'std'	  => '#ffffff',
			),
		),
		'pages'        => array(
			'page_settings'  => array(
				'id'   => 'page_settings',
				'name' => '<h3>' . __( 'Page Settings', 'mobile-dj-manager' ) . '</h3>',
				'desc' => '',
				'type' => 'header',
			),
			'app_home_page'  => array(
				'id'      => 'app_home_page',
				'name'    => mdjm_get_application_name() . ' ' . __( 'Home Page', 'mobile-dj-manager' ),
				'desc'    => sprintf(
					__( 'Select the home page for the %1$s application. Needs to contain the shortcode %2$s[mdjm-home]%3$s', 'mobile-dj-manager' ),
					mdjm_get_application_name(),
					'<code>',
					'</code>'
				),
				'type'    => 'select',
				'options' => mdjm_list_pages(),
			),
			'quotes_page'    => array(
				'id'      => 'quotes_page',
				'name'    => __( 'Online Quotes Page', 'mobile-dj-manager' ),
				'desc'    => sprintf( __( 'Select the page to use for online %s quotes. Needs to contain the shortcode <code>[mdjm-quote]</code>', 'mobile-dj-manager' ), mdjm_get_label_singular( true ) ),
				'type'    => 'select',
				'options' => mdjm_list_pages(),
			),
			'contact_page'   => array(
				'id'      => 'contact_page',
				'name'    => __( 'Contact Page', 'mobile-dj-manager' ),
				'desc'    => __( "Select your website's contact page so we can correctly direct visitors.", 'mobile-dj-manager' ),
				'type'    => 'select',
				'options' => mdjm_list_pages(),
			),
			'contracts_page' => array(
				'id'      => 'contracts_page',
				'name'    => __( 'Contracts Page', 'mobile-dj-manager' ),
				'desc'    => sprintf(
					__( "Select your website's contracts page. Needs to contain the shortcode %1\$s[mdjm-contract]%2\$s", 'mobile-dj-manager' ),
					'<code>',
					'</code>'
				),
				'type'    => 'select',
				'options' => mdjm_list_pages(),
			),
			'compliance_page' => array(
				'id'	  => 'compliance_page',
				'name'	  => __('Compliance Docs Page', 'mobile-dj-manager'),
				'desc'    => sprintf(
					__( "Select your website's compliance documents page. Needs to contain the shortcode %1\$s[mdjm-compliance]%2\$s", 'mobile-dj-manager' ),
					'<code>',
					'</code>'
				),
				'type'    => 'select',
				'options' => mdjm_list_pages(),
			),
			'payments_page'  => array(
				'id'      => 'payments_page',
				'name'    => __( 'Payments Page', 'mobile-dj-manager' ),
				'desc'    => sprintf(
					__( "Select your website's payments page. Needs to contain the shortcode %1\$s[mdjm-payments]%2\$s", 'mobile-dj-manager' ),
					'<code>',
					'</code>'
				),
				'type'    => 'select',
				'options' => mdjm_list_pages(),
			),
			'playlist_page'  => array(
				'id'      => 'playlist_page',
				'name'    => __( 'Playlist Page', 'mobile-dj-manager' ),
				'desc'    => sprintf(
					__( "Select your website's playlist page. Needs to contain the shortcode %1\$s[mdjm-playlist]%2\$s", 'mobile-dj-manager' ),
					'<code>',
					'</code>'
				),
				'type'    => 'select',
				'options' => mdjm_list_pages(),
			),
			'profile_page'   => array(
				'id'      => 'profile_page',
				'name'    => __( 'Profile Page', 'mobile-dj-manager' ),
				'desc'    => sprintf(
					__( "Select your website's profile page. Needs to contain the shortcode %1\$s[mdjm-profile]%2\$s", 'mobile-dj-manager' ),
					'<code>',
					'</code>'
				),
				'type'    => 'select',
				'options' => mdjm_list_pages(),
			),
		),
		'availability' => array(
			'availability_settings'        => array(
				'id'   => 'availability_settings',
				'name' => '<h3>' . __( 'Availability Settings', 'mobile-dj-manager' ) . '</h3>',
				'desc' => '',
				'type' => 'header',
			),
			'availability_status'          => array(
				'id'      => 'availability_status',
				'name'    => __( 'Unavailable Statuses', 'mobile-dj-manager' ),
				'desc'    => sprintf( __( "CTRL (cmd on MAC) + Click to select %s status' that you want availability checker to report as unavailable", 'mobile-dj-manager' ), mdjm_get_label_singular( true ) ),
				'type'    => 'multiple_select',
				'options' => mdjm_all_event_status(),
				'std'     => mdjm_active_event_statuses(),
			),
			'availability_roles'           => array(
				'id'      => 'availability_roles',
				'name'    => __( 'Employee Roles', 'mobile-dj-manager' ),
				'desc'    => __( 'CTRL (cmd on MAC) + Click to select employee roles that need to be available', 'mobile-dj-manager' ),
				'type'    => 'multiple_select',
				'options' => mdjm_get_roles(),
				'std'     => array( 'dj' ),
			),
			'avail_ajax'                   => array(
				'id'   => 'avail_ajax',
				'name' => __( 'Use Ajax?', 'mobile-dj-manager' ),
				'desc' => __( 'Perform checks without page refresh', 'mobile-dj-manager' ),
				'type' => 'checkbox',
				'std'  => '1',
			),
			'availability_check_pass_page' => array(
				'id'      => 'availability_check_pass_page',
				'name'    => __( 'Available Redirect Page', 'mobile-dj-manager' ),
				'desc'    => __( 'Select a page to which users should be directed when an availability check is successful', 'mobile-dj-manager' ),
				'type'    => 'select',
				'options' => mdjm_list_pages( array( 'text' => __( 'NO REDIRECT - USE TEXT', 'mobile-dj-manager' ) ) ),
				'std'     => 'text',
			),
			'availability_check_pass_text' => array(
				'id'   => 'availability_check_pass_text',
				'name' => __( 'Available Text', 'mobile-dj-manager' ),
				'desc' => __( 'Text to be displayed when you are available - Only displayed if <code>NO REDIRECT - USE TEXT</code> is selected above, unless you are redirecting to an MDJM Contact Form. Valid shortcodes <code>{event_date}</code> &amp; <code>{event_date_short}</code>', 'mobile-dj-manager' ),
				'type' => 'rich_editor',
				'std'  => __( 'Good news, we are available on the date you entered. Please contact us now', 'mobile-dj-manager' ),
			),
			'availability_check_fail_page' => array(
				'id'      => 'availability_check_fail_page',
				'name'    => __( 'Unavailable Redirect Page', 'mobile-dj-manager' ),
				'desc'    => __( 'Select a page to which users should be directed when an availability check is not successful', 'mobile-dj-manager' ),
				'type'    => 'select',
				'options' => mdjm_list_pages( array( 'text' => __( 'NO REDIRECT - USE TEXT', 'mobile-dj-manager' ) ) ),
				'std'     => 'text',
			),
			'availability_check_fail_text' => array(
				'id'   => 'availability_check_fail_text',
				'name' => __( 'Unavailable Text', 'mobile-dj-manager' ),
				'desc' => __( 'Text to be displayed when you are not available - Only displayed if <code>NO REDIRECT - USE TEXT</code> is selected above. Valid shortcodes <code>{event_date}</code> &amp; <code>{event_date_short}</code>', 'mobile-dj-manager' ),
				'type' => 'rich_editor',
				'std'  => __( 'Unfortunately we do not appear to be available on the date you selected. Why not try another date below...', 'mobile-dj-manager' ),
			),
		),
	)
),
/** Payment Settings */
'payments'         => apply_filters(
	'mdjm_settings_payments',
	array(
		'main'                  => array(
			'gateway_settings'       => array(
				'id'   => 'gateway_settings',
				'name' => '<h3>' . __( 'Gateway Settings', 'mobile-dj-manager' ) . '</h3>',
				'desc' => '',
				'type' => 'header',
			),
			'gateways'               => array(
				'id'      => 'gateways',
				'name'    => __( 'Payment Gateways', 'mobile-dj-manager' ),
				'desc'    => __( 'Choose the payment gateways you want to enable.', 'mobile-dj-manager' ),
				'type'    => 'gateways',
				'options' => mdjm_get_payment_gateways(),
			),
			'payment_gateway'        => array(
				'id'      => 'payment_gateway',
				'name'    => __( 'Default Gateway', 'mobile-dj-manager' ),
				'desc'    => __( 'This gateway will be loaded automatically with the payments page.', 'mobile-dj-manager' ),
				'type'    => 'gateway_select',
				'options' => mdjm_get_payment_gateways(),
			),
			'currency_settings'      => array(
				'id'   => 'currency_settings',
				'name' => '<h3>' . __( 'Currency Settings', 'mobile-dj-manager' ) . '</h3>',
				'desc' => '',
				'type' => 'header',
			),
			'currency'               => array(
				'id'      => 'currency',
				'name'    => __( 'Currency', 'mobile-dj-manager' ),
				'desc'    => '',
				'type'    => 'select',
				'options' => mdjm_get_currencies(),
			),
			'currency_format'        => array(
				'id'      => 'currency_format',
				'name'    => __( 'Currency Position', 'mobile-dj-manager' ),
				'desc'    => __( 'Where to display the currency symbol.', 'mobile-dj-manager' ),
				'type'    => 'select',
				'options' => array(
					'before'            => __( 'before price', 'mobile-dj-manager' ),
					'after'             => __( 'after price', 'mobile-dj-manager' ),
					'before with space' => __( 'before price with space', 'mobile-dj-manager' ),
					'after with space'  => __( 'after price with space', 'mobile-dj-manager' ),
				),
			),
			'decimal'                => array(
				'id'   => 'decimal',
				'name' => __( 'Decimal Separator', 'mobile-dj-manager' ),
				'desc' => __( 'The symbol to separate decimal points. (Usually . or ,)', 'mobile-dj-manager' ),
				'type' => 'text',
				'size' => 'small',
				'std'  => '.',
			),
			'thousands_seperator'    => array(
				'id'   => 'thousands_seperator',
				'name' => __( 'Thousands Separator', 'mobile-dj-manager' ),
				'desc' => '',
				'type' => 'text',
				'size' => 'small',
				'std'  => ',',
			),
			'deposit_settings'       => array(
				'id'   => 'deposit_settings',
				'name' => '<h3>' . sprintf( __( '%s Settings', 'mobile-dj-manager' ), mdjm_get_deposit_label() ) . '</h3>',
				'desc' => '',
				'type' => 'header',
			),
			'deposit_type'           => array(
				'id'      => 'deposit_type',
				'name'    => mdjm_get_deposit_label() . "s " . __( 'are', 'mobile-dj-manager' ),
				'desc'    => sprintf(
					__( 'If you require ' . mdjm_get_deposit_label() . ' payments for your %s, how should they be calculated?', 'mobile-dj-manager' ),
					mdjm_get_label_plural( true )
				),
				'type'    => 'select',
				'options' => array(
					'0'          => 'Not required',
					'percentage' => '% ' . sprintf( __( 'of %s value', 'mobile-dj-manager' ), mdjm_get_label_singular( true ) ),
					'fixed'      => __( 'Fixed price', 'mobile-dj-manager' ),
				),
			),
			'deposit_amount'         => array(
				'id'   => 'deposit_amount',
				'name' => mdjm_get_deposit_label() . ' ' . __( 'Amount', 'mobile-dj-manager' ),
				'desc' => sprintf(
					__( "If your %1\$s's are a percentage enter the value (i.e 20). For fixed prices, enter the amount in the format %2\$s", 'mobile-dj-manager' ),
					mdjm_get_deposit_label(),
					mdjm_format_amount( '0' )
				),
				'type' => 'text',
				'size' => 'small',
			),
			'deposit_before_confirm' => array(
				'id'   => 'deposit_before_confirm',
				'name' => sprintf( __( 'Wait for %s', 'mobile-dj-manager' ), mdjm_get_deposit_label() ),
				'desc' => sprintf(
					__( 'Wait for %s to be paid before the booking confirmation is issued', 'mobile-dj-manager' ),
					mdjm_get_deposit_label()
				),
				'type' => 'checkbox',
			),
			'payment_form_settings'  => array(
				'id'   => 'payment_form_settings',
				'name' => '<h3>' . __( 'Payment Form Settings', 'mobile-dj-manager' ) . '</h3>',
				'desc' => '',
				'type' => 'header',
			),
			'payment_label'          => array(
				'id'   => 'payment_label',
				'name' => __( 'Payment Label', 'mobile-dj-manager' ),
				'desc' => __( 'Display name of the label shown to clients to select the payment they wish to make.', 'mobile-dj-manager' ),
				'type' => 'text',
				'size' => 'regular',
				'std'  => __( 'Make a Payment Towards', 'mobile-dj-manager' ),
			),
			'other_amount_label'     => array(
				'id'   => 'other_amount_label',
				'name' => __( 'Label for Other Amount', 'mobile-dj-manager' ),
				'desc' => __( 'Enter your desired label for the other amount radio button.', 'mobile-dj-manager' ),
				'type' => 'text',
				'size' => 'regular',
				'std'  => __( 'Other Amount', 'mobile-dj-manager' ),
			),
			'payment_button'         => array(
				'id'   => 'payment_button',
				'name' => __( 'Payment Button Text', 'mobile-dj-manager' ),
				'desc' => __( 'The text you want to appear on the Payment Form submit button.', 'mobile-dj-manager' ),
				'type' => 'text',
				'size' => 'regular',
				'std'  => __( 'Pay Now', 'mobile-dj-manager' ),
			),
			'other_amount_default'   => array(
				'id'   => 'other_amount_default',
				'name' => __( 'Default', 'mobile-dj-manager' ) . ' ' . mdjm_get_option( 'other_amount_label', __( 'Other Amount', 'mobile-dj-manager' ) ),
				'desc' => sprintf(
					__( 'Enter the default amount to be used in the %s field.', 'mobile-dj-manager' ),
					mdjm_get_option( 'other_amount_label', __( 'Other Amount', 'mobile-dj-manager' ) )
				),
				'type' => 'text',
				'size' => 'small',
				'std'  => '50.00',
			),
			'tax_settings'           => array(
				'id'   => 'tax_settings',
				'name' => '<h3>' . __( 'Tax Settings', 'mobile-dj-manager' ) . '</h3>',
				'desc' => '',
				'type' => 'header',
			),
			'enable_tax'             => array(
				'id'   => 'enable_tax',
				'name' => __( 'Enable Taxes?', 'mobile-dj-manager' ),
				'desc' => __( 'Enable if you need to add taxes to online payments', 'mobile-dj-manager' ),
				'type' => 'checkbox',
			),
			'tax_type'               => array(
				'id'      => 'tax_type',
				'name'    => __( 'Apply Tax As', 'mobile-dj-manager' ),
				'desc'    => __( 'How do you apply tax?', 'mobile-dj-manager' ),
				'type'    => 'select',
				'options' => array(
					'percentage' => __( '% of total', 'mobile-dj-manager' ),
					'fixed'      => __( 'Fixed rate', 'mobile-dj-manager' ),
				),
				'std'     => 'percentage',
			),
			'tax_rate'               => array(
				'id'   => 'tax_rate',
				'name' => __( 'Tax Rate', 'mobile-dj-manager' ),
				'desc' => __( 'If you apply tax based on a fixed percentage (i.e. VAT) enter the value (i.e 20). For fixed rates, enter the amount in the format 0.00. Taxes will only be applied during checkout.', 'mobile-dj-manager' ),
				'type' => 'text',
				'size' => 'small',
				'std'  => '20',
			),
			'payment_types'          => array(
				'id'   => 'payment_types',
				'name' => '<h3>' . __( 'Payment Types', 'mobile-dj-manager' ) . '</h3>',
				'desc' => '',
				'type' => 'header',
			),
			'payment_sources'        => array(
				'id'   => 'payment_sources',
				'name' => __( 'Payment Types', 'mobile-dj-manager' ),
				'desc' => __( 'Enter methods of payment.', 'mobile-dj-manager' ),
				'type' => 'textarea',
				'std'  => __( 'BACS', 'mobile-dj-manager' ) . "\r\n" .
				__( 'Cash', 'mobile-dj-manager' ) . "\r\n" .
				__( 'Cheque', 'mobile-dj-manager' ) . "\r\n" .
				__( 'PayPal', 'mobile-dj-manager' ) . "\r\n" .
				__( 'PayFast', 'mobile-dj-manager' ) . "\r\n" .
				__( 'Stripe', 'mobile-dj-manager' ) . "\r\n" .
				__( 'Other', 'mobile-dj-manager' ),
			),
			'default_type'           => array(
				'id'      => 'default_type',
				'name'    => __( 'Default Payment Type', 'mobile-dj-manager' ),
				'desc'    => sprintf(
					__( 'What is the default method of payment? i.e. if you select an %1$s %2$s as paid how should we log it?', 'mobile-dj-manager' ),
					mdjm_get_label_singular( true ),
					mdjm_get_balance_label()
				),
				'type'    => 'select',
				'options' => mdjm_list_txn_sources(),
			),
		),
				// Employee Payment Settings
'employee_payments' => array(
	'employee_payment_settings'  => array(
		'id'   => 'employee_payment_settings',
		'name' => '<h3>' . __( 'Employee Payment Settings', 'mobile-dj-manager' ) . '</h3>',
		'desc' => '',
		'type' => 'header',
	),
	'enable_employee_payments'   => array(
		'id'   => 'enable_employee_payments',
		'name' => __( 'Enable Employee Payments', 'mobile-dj-manager' ),
		'desc' => sprintf( __( 'Enable this option to be able to record employee wage payments for %s.', 'mobile-dj-manager' ), mdjm_get_label_plural() ),
		'type' => 'checkbox',
	),
	'employee_pay_status'        => array(
		'id'      => 'employee_pay_status',
		'name'    => __( 'Payment Statuses', 'mobile-dj-manager' ),
		'desc'    => sprintf( __( "CTRL (cmd on MAC) + Click to select %s status' that an event must be at before employee payments can be made.", 'mobile-dj-manager' ), mdjm_get_label_singular( true ) ),
		'type'    => 'multiple_select',
		'options' => mdjm_all_event_status(),
		'std'     => array( 'mdjm-completed' ),
	),
	'employee_auto_pay_complete' => array(
		'id'   => 'employee_auto_pay_complete',
		'name' => sprintf( __( 'Pay when %s Completes', 'mobile-dj-manager' ), mdjm_get_label_singular() ),
		'desc' => sprintf( __( 'Enable this option to automatically pay employees once an %s completes.', 'mobile-dj-manager' ), mdjm_get_label_singular() ),
		'type' => 'checkbox',
	),
),
				// Receipts
'receipts'          => array(
	'payment_conf_templates'      => array(
		'id'   => 'payment_conf_templates',
		'name' => '<h3>' . __( 'Payment Receipts', 'mobile-dj-manager' ) . '</h3>',
		'desc' => '',
		'type' => 'header',
	),
	'payment_cfm_template'        => array(
		'id'      => 'payment_cfm_template',
		'name'    => __( 'Gateway Payment Receipt', 'mobile-dj-manager' ),
		'desc'    => __( 'Select an email template to be sent as a receipt to clients when a gateway payment is received.', 'mobile-dj-manager' ),
		'type'    => 'select',
		'options' => mdjm_list_templates( 'email_template', true ),
	),
	'manual_payment_cfm_template' => array(
		'id'      => 'manual_payment_cfm_template',
		'name'    => __( 'Manual Payment Receipt', 'mobile-dj-manager' ),
		'desc'    => __( 'Select an email template to be sent as a receipt to clients when you manually log a payment.', 'mobile-dj-manager' ),
		'type'    => 'select',
		'options' => mdjm_list_templates( 'email_template', true ),
	),
),
				// Bank Details
'bank-details'          => array(
	'bank_detail_header'      => array(
		'id'   => 'bank_detail_header',
		'name' => '<h3>' . __( 'Bank Details', '' ) . '</h3>',
		'desc' => 'Enter your bank details to display to your client.',
		'type' => 'header',
	),
	'enable_bacs'   => array(
		'id'   => 'enable_bacs',
		'name' => __( 'Show BACS on Payment Page', 'mobile-dj-manager' ),
		'desc' => __( 'Enable this option to show clients your BACS details on their payment page.', 'mobile-dj-manager' ),
		'type' => 'checkbox',
	),
	'bacs_info'        => array(
		'id'   => 'bacs_info',
		'name' => __( 'BACS Payment Information', 'mobile-dj-manager' ),
		'desc' => __( 'Enter a brief message to show to clients', 'mobile-dj-manager' ),
		'type' => 'text',
		'std'  => __( 'Please let me know via email if you pay through BACS', 'mobile-dj-manager' ),
	),
	'bank_detail_name'        => array(
		'id'      => 'bank_detail_name',
		'name'    => __( 'Account Name', 'mobile-dj-manager' ),
		'desc'    => __( 'Type your bank account name above', 'mobile-dj-manager' ),
		'type'    => 'text',
	),
	'bank_detail_sortcode'        => array(
		'id'      => 'bank_detail_sortcode',
		'name'    => __( 'Sort Code', 'mobile-dj-manager' ),
		'desc'    => __( 'Type your bank account Sort Code above', 'mobile-dj-manager' ),
		'type'    => 'text',
		'size'  => '6',
	),
	'bank_detail_accnumber'        => array(
		'id'      => 'bank_detail_accnumber',
		'name'    => __( 'Account Number', 'mobile-dj-manager' ),
		'desc'    => __( 'Type your bank account number above', 'mobile-dj-manager' ),
		'type'    => 'text',
		'size'	  => '8',
	),
),
)
),
/** Compliance Settings */
'terms_compliance' => apply_filters(
	'mdjm_settings_terms_compliance',
	array(
		'privacy'          => array(
			'privacy_settings'             => array(
				'id'   => 'privacy_settings',
				'name' => '<h3>' . __( 'Agreement Settings', 'mobile-dj-manager' ) . '</h3>',
				'type' => 'header',
			),
			'show_agree_to_privacy_policy' => array(
				'id'   => 'show_agree_to_privacy_policy',
				'name' => __( 'Agree to Privacy Policy?', 'mobile-dj-manager' ),
				'desc' => __( 'Check this to enforce acceptance of your privacy policy on a submission page that users must agree to before proceeding.', 'mobile-dj-manager' ),
				'type' => 'checkbox',
				'std'  => false,
			),
			'agree_privacy_label'          => array(
				'id'   => 'agree_privacy_label',
				'name' => __( 'Agree to Privacy Policy Label', 'mobile-dj-manager' ),
				'desc' => sprintf( __( 'Label shown next to the agree to Privacy Policy checkbox. This text will link to your defined <a href="%s">privacy policy</a>.', 'mobile-dj-manager' ), esc_attr( admin_url( 'privacy.php' ) ) ),
				'type' => 'text',
				'size' => 'regular',
			),
			'agree_privacy_descripton'     => array(
				'id'   => 'agree_privacy_descripton',
				'name' => __( 'Agree to Privacy Policy Description', 'mobile-dj-manager' ),
				'desc' => __( 'Description shown under the agree to Privacy Policy field. Leave blank for none', 'mobile-dj-manager' ),
				'type' => 'text',
				'size' => 'regular',
			),
			'show_agree_policy_type'       => array(
				'id'      => 'show_agree_policy_type',
				'name'    => __( 'Display Privacy Policy in', 'mobile-dj-manager' ),
				'type'    => 'select',
				'options' => array(
					'blank'    => __( 'New Page', 'mobile-dj-manager' ),
					'thickbox' => __( 'Thickbox', 'mobile-dj-manager' ),
				),
				'std'     => 'thickbox',
			),
		),
		'terms_conditions' => array(
			'terms_settings'          => array(
				'id'   => 'terms_settings',
				'name' => '<h3>' . __( 'Agreement Settings', 'mobile-dj-manager' ) . '</h3>',
				'type' => 'header',
			),
			'show_agree_to_terms'     => array(
				'id'   => 'show_agree_to_terms',
				'name' => __( 'Agree to Terms', 'mobile-dj-manager' ),
				'desc' => __( 'Check this to show an agree to terms on the submission page that users must agree to before submitting.', 'mobile-dj-manager' ),
				'type' => 'checkbox',
				'std'  => false,
			),
			'agree_terms_label'       => array(
				'id'   => 'agree_terms_label',
				'name' => __( 'Agree to Terms Label', 'mobile-dj-manager' ),
				'desc' => __( 'Label shown next to the agree to terms checkbox.', 'mobile-dj-manager' ),
				'type' => 'text',
				'size' => 'regular',
				'std'  => __( 'I have read and agree to the terms and conditions', 'mobile-dj-manager' ),
			),
			'agree_terms_description' => array(
				'id'   => 'agree_terms_description',
				'name' => __( 'Agree to Terms Description', 'mobile-dj-manager' ),
				'desc' => __( 'Description shown under the Agree to Terms field. Leave blank for none', 'mobile-dj-manager' ),
				'type' => 'text',
				'size' => 'regular',
			),
			'agree_terms_heading'     => array(
				'id'   => 'agree_terms_heading',
				'name' => __( 'Terms Heading', 'mobile-dj-manager' ),
				'desc' => __( 'Heading for the agree to terms thickbox.', 'mobile-dj-manager' ),
				'type' => 'text',
				'size' => 'regular',
				'std'  => sprintf(
					__( 'Terms and Conditions for %s', 'mobile-dj-manager' ),
					mdjm_get_label_plural()
				),
			),
			'agree_terms_text'        => array(
				'id'   => 'agree_terms_text',
				'name' => __( 'Agreement Text', 'mobile-dj-manager' ),
				'desc' => __( 'If Agree to Terms is checked, enter the agreement terms here.', 'mobile-dj-manager' ),
				'type' => 'rich_editor',
			),
		),
		'compliancedocs' => array(
			'pli_settings'          => array(
				'id'   => 'pli_settings',
				'name' => '<h3>' . __( 'Public Liability Insurance', 'mobile-dj-manager' ) . '</h3>',
				'type' => 'header',
			),
			'enable_pli'   => array(
				'id'   => 'enable_pli',
				'name' => __( 'Show Public Liability Insurance Certificate', 'mobile-dj-manager' ),
				'desc' => __( 'Enable this option to show clients your Public Liability Insurance Certificate.', 'mobile-dj-manager' ),
				'type' => 'checkbox',
			),
			'pli_cert_link'   => array(
				'id'    => 'pli_cert_link',
				'name'  => __( 'PLI Certificate Location', 'mobile-dj-manager' ),
				'desc'  => __( 'Enter the URL from the Media repository for your PLI Certificate', 'mobile-dj-manager'),
				'type'  => 'text',
			),
			'pat_settings'          => array(
				'id'   => 'pat_settings',
				'name' => '<h3>' . __( 'Portable Appliance Test', 'mobile-dj-manager' ) . '</h3>',
				'type' => 'header',
			),
			'enable_pat'   => array(
				'id'   => 'enable_pat',
				'name' => __( 'Show Portable Appliance Testing Certificate', 'mobile-dj-manager' ),
				'desc' => __( 'Enable this option to show clients your Portable Appliance Testing Certificate.', 'mobile-dj-manager' ),
				'type' => 'checkbox',
			),
			'pat_cert_link'   => array(
				'id'    => 'pat_cert_link',
				'name'  => __( 'PAT Certificate Location', 'mobile-dj-manager' ),
				'desc'  => __( 'Enter the URL from the Media repository for your PAT Certificate', 'mobile-dj-manager'),
				'type'  => 'text',
			),
		),
	)
),
/** Extension Settings */
'extensions'       => apply_filters(
	'mdjm_settings_extensions',
	array()
),
'licenses'         => apply_filters(
	'mdjm_settings_licenses',
	array()
),
'calendar'             => apply_filters(
	'mdjm_settings_calendar',
	array(
		'calendar' => array(
			'calendar_settings'            => array(
				'id'   => 'calendar_settings',
				'name' => '<h3>' . __( 'Calendar Settings', 'mobile-dj-manager' ) . '</h3>',
				'type' => 'header',
			),
			'availability_view'            => array(
				'id'      => 'availability_view',
				'name'    => __( 'Default Availability View', 'mobile-dj-manager' ),
				'desc'    => __( 'Select the default calendar view on the availability page.', 'mobile-dj-manager' ),
				'type'    => 'select',
				'chosen'  => true,
				'options' => mdjm_get_calendar_views(),
				'std'     => 'month',
			),
			'availability_view'            => array(
				'id'      => 'availability_view',
				'name'    => __( 'Default Availability View', 'mobile-dj-manager' ),
				'desc'    => __( 'Select the default calendar view on the availability page.', 'mobile-dj-manager' ),
				'type'    => 'select',
				'chosen'  => true,
				'options' => mdjm_get_calendar_views(),
				'std'     => 'month',
			),
			'remove_absences_on_delete'    => array(
				'id'   => 'remove_absences_on_delete',
				'name' => __( 'Delete Absence with Employee?', 'mobile-dj-manager' ),
				'desc' => __( 'If enabled, all absences associated with an employee will be removed when an employee is removed.', 'mobile-dj-manager' ),
				'type' => 'checkbox',
				'std'  => '1',
			),
			'calendar_absence_title'       => array(
				'id'   => 'calendar_absence_title',
				'name' => __( 'Absence Title', 'mobile-dj-manager' ),
				'desc' => sprintf(
					__( 'Title for the absence. The following tags can be used: %s', 'mobile-dj-manager' ),
					mdjm_display_absence_content_tags()
				),
				'type' => 'text',
				'std'  => '{employee_name}',
			),
			'calendar_absence_tip_title'   => array(
				'id'   => 'calendar_absence_tip_title',
				'name' => __( 'Absence Tip Title', 'mobile-dj-manager' ),
				'desc' => sprintf(
					__( 'Title for the absence tip. Tips are visible after clicking an entry. The following tags can be used: %s', 'mobile-dj-manager' ),
					mdjm_display_absence_content_tags()
				),
				'type' => 'text',
				'std'  => $absence_tip,
			),
			'calendar_absence_tip_content' => array(
				'id'   => 'calendar_absence_tip_content',
				'name' => __( 'Absence Tip Content', 'mobile-dj-manager' ),
				'desc' => sprintf(
					__( 'Content for the absence tip. Tips are visible after clicking an entry. The following tags can be used: %s', 'mobile-dj-manager' ),
					mdjm_display_absence_content_tags()
				),
				'type' => 'textarea',
				'std'  => $absence_content,
			),
			'absence_border_color'         => array(
				'id'      => 'absence_border_color',
				'name'    => __( 'Absence Border', 'mobile-dj-manager' ),
				'desc'    => __( 'Select the border color of absence entries.', 'mobile-dj-manager' ),
				'type'    => 'color',
				'default' => '#cccccc',
				'std'     => '#cccccc',
			),
			'absence_text_color'           => array(
				'id'      => 'absence_text_color',
				'name'    => __( 'Absence Text', 'mobile-dj-manager' ),
				'desc'    => __( 'Select the text color of absence entries.', 'mobile-dj-manager' ),
				'type'    => 'color',
				'default' => '#555555',
				'std'     => '#555555',
			),
			'calendar_event_title'         => array(
				'id'   => 'calendar_event_title',
				'name' => sprintf(
					__( '%s Title', 'mobile-dj-manager' ),
					mdjm_get_label_singular()
				),
				'desc' => sprintf(
					__( 'Title for the %1$s. The following tags can be used: %2$s', 'mobile-dj-manager' ),
					mdjm_get_label_singular(),
					mdjm_display_absence_content_tags()
				),
				'type' => 'text',
				'std'  => $event_title,
			),
			'calendar_event_tip_title'     => array(
				'id'   => 'calendar_event_tip_title',
				'name' => sprintf(
					__( '%s Tip Title', 'mobile-dj-manager' ),
					mdjm_get_label_singular()
				),
				'desc' => sprintf(
					__( 'Title for the %1$s tip. Tips are visible after clicking an entry. The following tags can be used: %2$s', 'mobile-dj-manager' ),
					mdjm_get_label_singular(),
					mdjm_display_absence_content_tags()
				),
				'type' => 'text',
				'std'  => $event_tip_title,
			),
			'calendar_event_tip_content'   => array(
				'id'   => 'calendar_event_tip_content',
				'name' => sprintf(
					__( '%s Tip Content', 'mobile-dj-manager' ),
					mdjm_get_label_singular()
				),
				'desc' => sprintf(
					__( 'Content for the %1$s tip. Tips are visible after clicking an entry. The following tags can be used: %2$s', 'mobile-dj-manager' ),
					mdjm_get_label_singular(),
					mdjm_display_absence_content_tags()
				),
				'type' => 'textarea',
				'std'  => $event_content,
			),
			'event_background_color'       => array(
				'id'      => 'event_background_color',
				'name'    => sprintf( __( '%s Background', 'mobile-dj-manager' ), mdjm_get_label_singular() ),
				'desc'    => sprintf( __( 'Select the background color of %s entries.', 'mobile-dj-manager' ), mdjm_get_label_singular( true ) ),
				'type'    => 'color',
				'default' => '#2ea2cc',
				'std'     => '#2ea2cc',
			),
			'event_border_color'           => array(
				'id'      => 'event_border_color',
				'name'    => sprintf( __( '%s Border', 'mobile-dj-manager' ), mdjm_get_label_singular() ),
				'desc'    => sprintf( __( 'Select the border color of %s entries.', 'mobile-dj-manager' ), mdjm_get_label_singular( true ) ),
				'type'    => 'color',
				'default' => '#0074a2',
				'std'     => '#0074a2',
			),
			'event_text_color'             => array(
				'id'      => 'event_text_color',
				'name'    => sprintf( __( '%s Text', 'mobile-dj-manager' ), mdjm_get_label_singular() ),
				'desc'    => sprintf( __( 'Select the text color of %s entries.', 'mobile-dj-manager' ), mdjm_get_label_singular( true ) ),
				'type'    => 'color',
				'default' => '#ffffff',
				'std'     => '#ffffff',
			),
		),
)
),
);

return apply_filters( 'mdjm_registered_settings', $mdjm_settings );
} // mdjm_get_registered_settings

/**
 * Settings Sanitization
 *
 * Adds a settings error (for the updated message)
 * At some point this will validate input
 *
 * @since   1.3
 *
 * @param   arr $input  The value inputted in the field
 *
 * @return  str     $input  Sanitizied value
 */
function mdjm_settings_sanitize( $input = array() ) {
	global $mdjm_options;

	if ( empty( $_POST['_wp_http_referer'] ) ) {
		return $input;
	}

	parse_str( sanitize_text_field( wp_unslash( $_POST['_wp_http_referer'] ) ), $referrer );

	$settings = mdjm_get_registered_settings();
	$tab      = isset( $referrer['tab'] ) ? $referrer['tab'] : 'general';
	$section  = isset( $referrer['section'] ) ? $referrer['section'] : 'main';

	$input          = $input ? $input : array();
	$legacy_inputs  = apply_filters( 'mdjm_settings_' . $tab . '_sanitize', $input ); // Check for extensions that aren't using new sections
	$section_inputs = apply_filters( 'mdjm_settings_' . $tab . '-' . $section . '_sanitize', $input );

	$input = array_merge( $legacy_inputs, $section_inputs );

	// Loop through each setting being saved and pass it through a sanitization filter
	foreach ( $input as $key => $value ) {
		// Get the setting type (checkbox, select, etc)
		$type = isset( $settings[ $tab ][ $key ]['type'] ) ? $settings[ $tab ][ $key ]['type'] : false;
		$field_type = isset( $settings[ $tab ][ $section ][$key]['type'] ) ? $settings[ $tab ][ $section ][$key]['type'] : false;

		if ( $type ) {
			// Field type specific filter
			$input[ $key ] = apply_filters( 'mdjm_settings_sanitize_' . $type, $value, $key );
		}

		if( "text" === $field_type){
			// if field type is text
			$input[ $key ] = apply_filters( 'mdjm_settings_sanitize_text', $value, $key );
		}
		
		// General filter
		$input[ $key ] = apply_filters( 'mdjm_settings_sanitize', $input[ $key ], $key );
	}

	// Loop through the whitelist and unset any that are empty for the tab being saved
	$main_settings    = $section == 'main' ? $settings[ $tab ] : array(); // Check for extensions that aren't using new sections
	$section_settings = ! empty( $settings[ $tab ][ $section ] ) ? $settings[ $tab ][ $section ] : array();

	$found_settings = array_merge( $main_settings, $section_settings );

	if ( ! empty( $found_settings ) ) {
		foreach ( $found_settings as $key => $value ) {

			// Settings used to have numeric keys, now they have keys that match the option ID. This ensures both methods work
			if ( is_numeric( $key ) ) {
				$key = $value['id'];
			}

			if ( empty( $input[ $key ] ) ) {
				unset( $mdjm_options[ $key ] );
			}
		}
	}

	// Merge our new settings with the existing
	$output = array_merge( $mdjm_options, $input );

	add_settings_error( 'mdjm-notices', '', __( 'Settings updated.', 'mobile-dj-manager' ), 'updated' );

	return $output;
} // mdjm_settings_sanitize

/**
 * Sanitize text fields
 *
 * @since   1.3
 * @param   arr $input  The field value
 * @return  str     $input  Sanitizied value
 */
function mdjm_sanitize_text_field( $input ) {
	return sanitize_text_field( trim( $input ) );
} // mdjm_sanitize_text_field
add_filter( 'mdjm_settings_sanitize_text', 'mdjm_sanitize_text_field' );

/**
 * Sanitize HTML Class Names
 *
 * @since   1.0
 * @param   str|arr $class  HTML Class Name(s)
 * @return  str         $class
 */
function mdjm_sanitize_html_class( $class = '' ) {

	if ( is_string( $class ) ) {
		$class = sanitize_html_class( $class );
	} elseif ( is_array( $class ) ) {
		$class = array_values( array_map( 'sanitize_html_class', $class ) );
		$class = implode( ' ', array_unique( $class ) );
	}

	return $class;

} // mdjm_sanitize_html_class

/**
 * Retrieve settings tabs
 *
 * @since   1.3
 * @return  arr     $tabs
 */
function mdjm_get_settings_tabs() {

	$settings = mdjm_get_registered_settings();

	$tabs                     = array();
	$tabs['general']          = __( 'General', 'mobile-dj-manager' );
	$tabs['events']           = sprintf( __( '%s', 'mobile-dj-manager' ), mdjm_get_label_plural() );
	$tabs['emails']           = __( 'Emails &amp; Templates', 'mobile-dj-manager' );
	$tabs['client_zone']      = mdjm_get_application_name();
	$tabs['payments']         = __( 'Payments', 'mobile-dj-manager' );
	$tabs['terms_compliance'] = __( 'Compliance', 'mobile-dj-manager' );

	if ( ! empty( $settings['extensions'] ) ) {
		$tabs['extensions'] = __( 'Extensions', 'mobile-dj-manager' );
	}
	if ( ! empty( $settings['licenses'] ) ) {
		$tabs['licenses'] = __( 'Licenses', 'mobile-dj-manager' );
	}

	$tabs['calendar'] = __( 'Calendar', 'mobile-dj-manager' );

	return apply_filters( 'mdjm_settings_tabs', $tabs );
} // mdjm_get_settings_tabs

/**
 * Retrieve settings tabs sections
 *
 * @since   1.3
 * @return  arr     $section
 */
function mdjm_get_settings_tab_sections( $tab = false ) {
	$tabs     = false;
	$sections = mdjm_get_registered_settings_sections();

	if ( $tab && ! empty( $sections[ $tab ] ) ) {
		$tabs = $sections[ $tab ];
	} elseif ( $tab ) {
		$tabs = false;
	}

	return $tabs;
} // mdjm_get_settings_tab_sections

/**
 * Get the settings sections for each tab
 * Uses a static to avoid running the filters on every request to this function
 *
 * @since   1.3
 * @return  arr     Array of tabs and sections
 */
function mdjm_get_registered_settings_sections() {
	static $sections = false;

	if ( false !== $sections ) {
		return $sections;
	}

	$sections = array(
		'general'          => apply_filters(
			'mdjm_settings_sections_general',
			array(
				'main'      => __( 'Application Settings', 'mobile-dj-manager' ),
				'debugging' => __( 'Debug Settings', 'mobile-dj-manager' ),
			)
		),
		'events'           => apply_filters(
			'mdjm_settings_sections_gateways',
			array(
				'main'     => sprintf( __( '%s Settings', 'mobile-dj-manager' ), mdjm_get_label_singular() ),
				'playlist' => __( 'Playlist Settings', 'mobile-dj-manager' ),
				'packages' => __( 'Packages &amp; Add-ons', 'mobile-dj-manager' ),
				'travel'   => __( 'Travel Settings', 'mobile-dj-manager' ),
			)
		),
		'emails'           => apply_filters(
			'mdjm_settings_sections_emails',
			array(
				'main'      => __( 'General Email Settings', 'mobile-dj-manager' ),
				'templates' => sprintf( __( '%s Templates', 'mobile-dj-manager' ), mdjm_get_label_singular() ),
			)
		),
		'client_zone'      => apply_filters(
			'mdjm_settings_sections_styles',
			array(
				'main'         => sprintf( __( '%s Settings', 'mobile-dj-manager' ), mdjm_get_application_name() ),
				'styles'       => __( 'Styles', 'mobile-dj-manager' ),
				'pages'        => __( 'Pages', 'mobile-dj-manager' ),
				'availability' => __( 'Availability Checker', 'mobile-dj-manager' ),
			)
		),
		'payments'         => apply_filters(
			'mdjm_settings_sections_payments',
			array(
				'main'              => __( 'Payment Settings', 'mobile-dj-manager' ),
				'employee_payments' => __( 'Employee Payments', 'mobile-dj-manager' ),
				'receipts'          => __( 'Receipts', 'mobile-dj-manager' ),
				'bank-details'		=> __( 'Bank Details', 'mobile-dj-manager'),
			)
		),
		'terms_compliance' => apply_filters(
			'mdjm_settings_sections_terms_compliance',
			array(
				'privacy'          => __( 'Privacy Policy', 'mobile-dj-manager' ),
				'terms_conditions' => __( 'Terms and Conditions', 'mobile-dj-manager' ),
				'compliancedocs'  => __( 'Compliance Documents', 'mobile-dj-manager' ),
			)
		),
		'extensions'       => apply_filters(
			'mdjm_settings_sections_extensions',
			array(
				'main' => __( 'Main', 'mobile-dj-manager' ),
			)
		),
		'licenses'         => apply_filters( 'mdjm_settings_sections_licenses', array() ),
		'calendar'             => apply_filters(
			'mdjm_settings_sections_calendar',
			array(
				'calendar' => __( 'Availability Calendar', 'mobile-dj-manager' ),
			)
		),
	);

	$sections = apply_filters( 'mdjm_settings_sections', $sections );

	return $sections;
} // mdjm_get_registered_settings_sections

/**
 * Return a list of templates for use as dropdown options within a select list.
 *
 * @since   1.3
 * @param   str $post_type  Optional: 'contract' or 'email_template'. If omitted, fetch both.
 * @return  arr     Array of templates, id => title.
 */
function mdjm_list_templates( $post_type = array( 'contract', 'email_template' ), $show_none = false ) {
	$template_posts = get_posts(
		array(
			'post_type'      => $post_type,
			'post_status'    => 'publish',
			'posts_per_page' => -1,
			'orderby'        => 'post_title',
			'order'          => 'ASC',
		)
	);

	$templates = array();

	if ( ! empty( $show_none ) ) {
		$templates[0] = __( 'None', 'mobile-dj-manager' );
	}

	foreach ( $template_posts as $template ) {
		$templates[ $template->ID ] = $template->post_title;
	}

	return $templates;
} // mdjm_list_templates

/**
 * Retrieve a list of all published pages
 *
 * On large sites this can be expensive, so only load if on the settings page or $force is set to true
 *
 * @since   1.3
 * @param   arr  $first          The first option in the list.
 * @param   bool $force          Force the pages to be loaded even if not on settings
 * @return  arr     $pages_options  An array of the pages
 */
function mdjm_list_pages( $first = array(), $force = false ) {

	$pages_options = array( '' => '' ); // Blank option

	if ( ( ! isset( $_GET['page'] ) || 'mdjm-settings' != $_GET['page'] ) && ! $force ) {
		return $pages_options;
	}

	$pages = get_pages();

	if ( ! empty( $first ) && is_array( $first ) ) {
		foreach ( $first as $key => $value ) {
			$pages_options[ $key ] = $value;
		}
	}

	if ( $pages ) {
		foreach ( $pages as $page ) {
			$pages_options[ $page->ID ] = $page->post_title;
		}
	}

	return $pages_options;
} // mdjm_list_pages

/**
 * Retrieve a list of all transaction sources
 *
 * @since   1.3
 * @param   bool
 * @return  arr     $txn_sources    An array of transaction sources
 */
function mdjm_list_txn_sources() {
	$sources = mdjm_get_txn_source();

	foreach ( $sources as $source ) {
		$txn_sources[ $source ] = $source;
	}

	return $txn_sources;
} // mdjm_list_txn_sources

/**
 * Header Callback
 *
 * Renders the header.
 *
 * @since   1.3
 * @param   arr $args   Arguments passed by the setting
 * @return  void
 */
function mdjm_header_callback( $args ) {
	echo '';
} // mdjm_header_callback

/**
 * Checkbox Callback
 *
 * Renders checkboxes.
 *
 * @since   1.3
 * @param   arr $args   Arguments passed by the setting
 * @global  $mdjm_options   Array of all the MDJM Options
 * @return  void
 */
function mdjm_checkbox_callback( $args ) {
	global $mdjm_options;

	if ( isset( $args['faux'] ) && true === $args['faux'] ) {
		$name = '';
	} else {
		$name = 'name="mdjm_settings[' . $args['id'] . ']"';
	}

	$checked = isset( $mdjm_options[ $args['id'] ] ) ? checked( 1, $mdjm_options[ $args['id'] ], false ) : '';
	$html    = '<input type="checkbox" id="mdjm_settings[' . $args['id'] . ']"' . $name . ' value="1" ' . $checked . '/>';
	$html   .= '<label for="mdjm_settings[' . $args['id'] . ']"> ' . $args['hint'] . '</label>';
	$html   .= '<p class="description"><label for="mdjm_settings[' . $args['id'] . ']"> ' . $args['desc'] . '</label></p>';

	echo $html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
} // mdjm_checkbox_callback

/**
 * Multicheck Callback
 *
 * Renders multiple checkboxes.
 *
 * @since   1.3
 * @param   arr $args   Arguments passed by the setting
 * @global  $mdjm_options   Array of all the MDJM Options
 * @return  void
 */
function mdjm_multicheck_callback( $args ) {
	global $mdjm_options;

	if ( ! empty( $args['options'] ) ) {
		foreach ( $args['options'] as $key => $option ) :
			if ( isset( $mdjm_options[ $args['id'] ][ $key ] ) ) {
				$enabled = $option;
			} else {
				$enabled = null;
			}

			echo '<input name="mdjm_settings[' . esc_attr( $args['id'] ) . '][' . esc_attr( $key ) . ']" id="mdjm_settings[' . esc_attr( $args['id'] ) . '][' . esc_attr( $key ) . ']" type="checkbox" value="' . esc_attr( $option ) . '" ' . checked( $option, $enabled, false ) . '/>&nbsp;';
			echo '<label for="mdjm_settings[' . esc_attr( $args['id'] ) . '][' . esc_attr( $key ) . ']">' . esc_html( $option ) . '</label><br/>';
		endforeach;
		echo '<p class="description">' . esc_html( $args['desc'] ) . '</p>';
	}
} // mdjm_multicheck_callback

/**
 * Radio Callback
 *
 * Renders radio boxes.
 *
 * @since   1.3
 * @param   arr $args   Arguments passed by the setting
 * @global  $mdjm_options   Array of all the MDJM Options
 * @return  void
 */
function mdjm_radio_callback( $args ) {
	global $mdjm_options;

	foreach ( $args['options'] as $key => $option ) :
		$checked = false;

		if ( isset( $mdjm_options[ $args['id'] ] ) && $mdjm_options[ $args['id'] ] == $key ) {
			$checked = true;
		} elseif ( isset( $args['std'] ) && $args['std'] == $key && ! isset( $mdjm_options[ $args['id'] ] ) ) {
			$checked = true;
		}

		echo '<input name="mdjm_settings[' . esc_attr( $args['id'] ) . ']"" id="mdjm_settings[' . esc_attr( $args['id'] ) . '][' . esc_attr( $key ) . ']" type="radio" value="' . esc_attr( $key ) . '" ' . checked( true, $checked, false ) . '/>&nbsp;';
		echo '<label for="mdjm_settings[' . esc_attr( $args['id'] ) . '][' . esc_attr( $key ) . ']">' . esc_html( $option ) . '</label><br/>';
	endforeach;

	echo '<p class="description">' . esc_html( $args['desc'] ) . '</p>';
} // mdjm_radio_callback

/**
 * Gateways Callback
 *
 * Renders gateways fields.
 *
 * @since   1.8
 * @param   arr $args   Arguments passed by the setting
 * @global  $mdjm_options   Array of all the MDJM Options
 * @return  void
 */
function mdjm_gateways_callback( $args ) {

	$mdjm_option = mdjm_get_option( $args['id'] );

	$html = '';

	foreach ( $args['options'] as $key => $option ) {
		if ( $key == 'disabled' ) {
			continue;
		}
		if ( isset( $mdjm_option[ $key ] ) ) {
			$enabled = '1';
		} else {
			$enabled = null;
		}

		$html .= '<input name="mdjm_settings[' . esc_attr( $args['id'] ) . '][' . mdjm_sanitize_key( $key ) . ']" id="mdjm_settings[' . mdjm_sanitize_key( $args['id'] ) . '][' . mdjm_sanitize_key( $key ) . ']" type="checkbox" value="1" ' . checked( '1', $enabled, false ) . '/>&nbsp;';
		$html .= '<label for="mdjm_settings[' . mdjm_sanitize_key( $args['id'] ) . '][' . mdjm_sanitize_key( $key ) . ']">' . esc_html( $option['admin_label'] ) . '</label><br/>';
	}

	echo apply_filters( 'mdjm_after_setting_output', $html, $args ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

} // mdjm_gateways_callback

/**
 * Gateways Callback (drop down)
 *
 * Renders gateways select menu
 *
 * @since   1.8
 * @param   arr $args   Arguments passed by the setting
 * @global  $mdjm_options   Array of all the MDJM Options
 * @return  void
 */
function mdjm_gateway_select_callback( $args ) {
	$mdjm_option = mdjm_get_option( $args['id'] );

	$html = '';

	$html .= '<select name="mdjm_settings[' . mdjm_sanitize_key( $args['id'] ) . ']" id="mdjm_settings[' . mdjm_sanitize_key( $args['id'] ) . ']">';

	foreach ( $args['options'] as $key => $option ) {

		$selected = isset( $mdjm_option ) ? selected( $key, $mdjm_option, false ) : '';
		$html    .= '<option value="' . mdjm_sanitize_key( $key ) . '"' . $selected . '>' . esc_html( $option['admin_label'] ) . '</option>';

	}

	$html .= '</select>';
	$html .= '<label for="mdjm_settings[' . mdjm_sanitize_key( $args['id'] ) . ']"> ' . wp_kses_post( $args['desc'] ) . '</label>';

	echo apply_filters( 'mdjm_after_setting_output', $html, $args ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

} // mdjm_gateway_select_callback

/**
 * Text Callback
 *
 * Renders text fields.
 *
 * @since   1.3
 * @param   arr $args   Arguments passed by the setting
 * @global  $mdjm_options   Array of all the MDJM Options
 * @return  void
 */
function mdjm_text_callback( $args ) {
	global $mdjm_options;

	if ( isset( $mdjm_options[ $args['id'] ] ) ) {
		$value = $mdjm_options[ $args['id'] ];
	} else {
		$value = isset( $args['std'] ) ? $args['std'] : '';
	}

	if ( isset( $args['faux'] ) && true === $args['faux'] ) {
		$args['readonly'] = true;
		$value            = isset( $args['std'] ) ? $args['std'] : '';
		$name             = '';
	} else {
		$name = 'name="mdjm_settings[' . $args['id'] . ']"';
	}

	$readonly = $args['readonly'] === true ? ' readonly="readonly"' : '';
	$size     = ( isset( $args['size'] ) && ! is_null( $args['size'] ) ) ? $args['size'] : 'regular';
	$html     = '<input type="text" class="' . $size . '-text" id="mdjm_settings[' . $args['id'] . ']"' . $name . ' value="' . esc_attr( stripslashes( $value ) ) . '"' . $readonly . '/>';
	$html    .= '<label for="mdjm_settings[' . $args['id'] . ']"> ' . $args['hint'] . '</label>';
	$html    .= '<p class="description"><label for="mdjm_settings[' . $args['id'] . ']"> ' . $args['desc'] . '</label></p>';

	echo $html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
} // mdjm_text_callback

/**
 * Number Callback
 *
 * Renders number fields.
 *
 * @since   1.3
 * @param   arr $args   Arguments passed by the setting
 * @global  $mdjm_options       Array of all the MDJM Options
 * @return  void
 */
function mdjm_number_callback( $args ) {
	global $mdjm_options;

	if ( isset( $mdjm_options[ $args['id'] ] ) ) {
		$value = $mdjm_options[ $args['id'] ];
	} else {
		$value = isset( $args['std'] ) ? $args['std'] : '';
	}

	if ( isset( $args['faux'] ) && true === $args['faux'] ) {
		$args['readonly'] = true;
		$value            = isset( $args['std'] ) ? $args['std'] : '';
		$name             = '';
	} else {
		$name = 'name="mdjm_settings[' . $args['id'] . ']"';
	}

	$max  = isset( $args['max'] ) ? $args['max'] : 999999;
	$min  = isset( $args['min'] ) ? $args['min'] : 0;
	$step = isset( $args['step'] ) ? $args['step'] : 1;

	$size  = ( isset( $args['size'] ) && ! is_null( $args['size'] ) ) ? $args['size'] : 'regular';
	$html  = '<input type="number" step="' . esc_attr( $step ) . '" max="' . esc_attr( $max ) . '" min="' . esc_attr( $min ) . '" class="' . $size . '-text" id="mdjm_settings[' . $args['id'] . ']" ' . $name . ' value="' . esc_attr( stripslashes( $value ) ) . '"/>';
	$html .= '<label for="mdjm_settings[' . $args['id'] . ']"> ' . $args['hint'] . '</label>';
	$html .= '<p class="description"><label for="mdjm_settings[' . $args['id'] . ']"> ' . $args['desc'] . '</label></p>';

	echo $html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
} // mdjm_number_callback

/**
 * Textarea Callback
 *
 * Renders textarea fields.
 *
 * @since   1.3
 * @param   arr $args   Arguments passed by the setting
 * @global  $mdjm_options   Array of all the MDJM Options
 * @return  void
 */
function mdjm_textarea_callback( $args ) {
	global $mdjm_options;

	if ( isset( $mdjm_options[ $args['id'] ] ) ) {
		$value = $mdjm_options[ $args['id'] ];
	} else {
		$value = isset( $args['std'] ) ? $args['std'] : '';
	}

	$html  = '<textarea class="large-text" cols="50" rows="5" id="mdjm_settings[' . $args['id'] . ']" name="mdjm_settings[' . $args['id'] . ']">' . esc_textarea( stripslashes( $value ) ) . '</textarea>';
	$html .= '<label for="mdjm_settings[' . $args['id'] . ']"> ' . $args['hint'] . '</label>';
	$html .= '<p class="description"><label for="mdjm_settings[' . $args['id'] . ']"> ' . $args['desc'] . '</label></p>';

	echo $html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
} // mdjm_textarea_callback

/**
 * Password Callback
 *
 * Renders password fields.
 *
 * @since   1.3
 * @param   arr $args   Arguments passed by the setting
 * @global  $mdjm_options   Array of all the MDJM Options
 * @return  void
 */
function mdjm_password_callback( $args ) {
	global $mdjm_options;

	if ( isset( $mdjm_options[ $args['id'] ] ) ) {
		$value = $mdjm_options[ $args['id'] ];
	} else {
		$value = isset( $args['std'] ) ? $args['std'] : '';
	}

	$readonly = $args['readonly'] === true ? ' readonly="readonly"' : '';
	$size     = ( isset( $args['size'] ) && ! is_null( $args['size'] ) ) ? $args['size'] : 'regular';
	$html     = '<input type="password" class="' . $size . '-text" id="mdjm_settings[' . $args['id'] . ']" name="mdjm_settings[' . $args['id'] . ']" value="' . esc_attr( $value ) . '"' . $readonly . '/>';
	$html    .= '<label for="mdjm_settings[' . $args['id'] . ']"> ' . $args['hint'] . '</label>';
	$html    .= '<p class="description"><label for="mdjm_settings[' . $args['id'] . ']"> ' . $args['desc'] . '</label></p>';

	echo $html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
} // mdjm_password_callback

/**
 * Missing Callback
 *
 * If a function is missing for settings callbacks alert the user.
 *
 * @since   1.3
 * @param   arr $args   Arguments passed by the setting
 * @return  void
 */
function mdjm_missing_callback( $args ) {
	printf(
		esc_html__( 'The callback function used for the %s setting is missing.', 'mobile-dj-manager' ),
		'<strong>' . esc_html( $args['id'] ) . '</strong>'
	);
} // mdjm_missing_callback

/**
 * Select Callback
 *
 * Renders select fields.
 *
 * @since   1.3
 * @param   arr $args Arguments passed by the setting
 * @global  $mdjm_options   Array of all the MDJM Options
 * @return  void
 */
function mdjm_select_callback( $args ) {
	global $mdjm_options;

	if ( isset( $mdjm_options[ $args['id'] ] ) ) {
		$value = $mdjm_options[ $args['id'] ];
	} else {
		$value = isset( $args['std'] ) ? $args['std'] : '';
	}

	if ( isset( $args['placeholder'] ) ) {
		$placeholder = $args['placeholder'];
	} else {
		$placeholder = '';
	}

	if ( isset( $args['chosen'] ) ) {
		$chosen = 'class="mdjm-chosen"';
	} else {
		$chosen = '';
	}

	$html = '<select id="mdjm_settings[' . $args['id'] . ']" name="mdjm_settings[' . $args['id'] . ']" ' . $chosen . 'data-placeholder="' . $placeholder . '" />';

	foreach ( $args['options'] as $option => $name ) {
		$selected = selected( $option, $value, false );
		$html    .= '<option value="' . $option . '" ' . $selected . '>' . $name . '</option>';
	}

	$html .= '</select>';
	$html .= '<label for="mdjm_settings[' . $args['id'] . ']"> ' . $args['hint'] . '</label>';
	$html .= '<p class="description"><label for="mdjm_settings[' . $args['id'] . ']"> ' . $args['desc'] . '</label></p>';

	echo $html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
} // mdjm_select_callback

/**
 * Multiple Select Callback
 *
 * Renders multiple select fields.
 *
 * @since   1.3
 * @param   arr $args Arguments passed by the setting
 * @global  $mdjm_options   Array of all the MDJM Options
 * @return  void
 */
function mdjm_multiple_select_callback( $args ) {
	global $mdjm_options;

	if ( isset( $mdjm_options[ $args['id'] ] ) ) {
		$value = $mdjm_options[ $args['id'] ];
	} else {
		$value = isset( $args['std'] ) ? $args['std'] : '';
	}

	if ( isset( $args['placeholder'] ) ) {
		$placeholder = $args['placeholder'];
	} else {
		$placeholder = '';
	}

	if ( isset( $args['chosen'] ) ) {
		$chosen = 'class="mdjm-chosen"';
	} else {
		$chosen = '';
	}

	$html = '<select id="mdjm_settings[' . $args['id'] . ']" name="mdjm_settings[' . $args['id'] . '][]" ' . $chosen . 'data-placeholder="' . $placeholder . '" multiple="multiple" />';

	foreach ( $args['options'] as $option => $name ) {
		$selected = ! empty( $value ) && in_array( $option, $value ) ? 'selected="selected"' : '';
		$html    .= '<option value="' . $option . '" ' . $selected . '>' . $name . '</option>';
	}

	$html .= '</select>';
	$html .= '<label for="mdjm_settings[' . $args['id'] . ']"> ' . $args['hint'] . '</label>';
	$html .= '<p class="description"><label for="mdjm_settings[' . $args['id'] . ']"> ' . $args['desc'] . '</label></p>';

	echo $html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
} // mdjm_multiple_select_callback

/**
 * Color select Callback
 *
 * Renders color select fields.
 *
 * @since   1.3
 * @param   arr $args Arguments passed by the setting
 * @global  $mdjm_options   Array of all the MDJM Options
 * @return  void
 */
function mdjm_color_select_callback( $args ) {
	global $mdjm_options;

	if ( isset( $mdjm_options[ $args['id'] ] ) ) {
		$value = $mdjm_options[ $args['id'] ];
	} else {
		$value = isset( $args['std'] ) ? $args['std'] : '';
	}

	$html = '<select id="mdjm_settings[' . $args['id'] . ']" name="mdjm_settings[' . $args['id'] . ']"/>';

	foreach ( $args['options'] as $option => $color ) {
		$selected = selected( $option, $value, false );
		$html    .= '<option value="' . $option . '" ' . $selected . '>' . $color['label'] . '</option>';
	}

	$html .= '</select>';
	$html .= '<label for="mdjm_settings[' . $args['id'] . ']"> ' . $args['hint'] . '</label>';
	$html .= '<p class="description"><label for="mdjm_settings[' . $args['id'] . ']"> ' . $args['desc'] . '</label></p>';

	echo $html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
} // mdjm_color_select_callback

/**
 * Rich Editor Callback
 *
 * Renders rich editor fields.
 *
 * @since   1.3
 * @param   arr $args Arguments passed by the setting
 * @global  $mdjm_options   Array of all the MDJM Options
 * @global  $wp_version     WordPress Version
 */
function mdjm_rich_editor_callback( $args ) {
	global $mdjm_options, $wp_version;

	if ( isset( $mdjm_options[ $args['id'] ] ) ) {
		$value = $mdjm_options[ $args['id'] ];

		if ( empty( $args['allow_blank'] ) && empty( $value ) ) {
			$value = isset( $args['std'] ) ? $args['std'] : '';
		}
	} else {
		$value = isset( $args['std'] ) ? $args['std'] : '';
	}

	$rows = isset( $args['size'] ) ? $args['size'] : 20;

	if ( $wp_version >= 3.3 && function_exists( 'wp_editor' ) ) {
		ob_start();
		wp_editor(
			stripslashes( $value ),
			'mdjm_settings_' . $args['id'],
			array(
				'textarea_name' => 'mdjm_settings[' . esc_attr( $args['id'] ) . ']',
				'textarea_rows' => $rows,
			)
		);
		$html = ob_get_clean();
	} else {
		$html = '<textarea class="large-text" rows="10" id="mdjm_settings[' . $args['id'] . ']" name="mdjm_settings[' . esc_attr( $args['id'] ) . ']">' . esc_textarea( stripslashes( $value ) ) . '</textarea>';
	}

	$html .= '<p class="description"<label for="mdjm_settings[' . $args['id'] . ']"> ' . $args['desc'] . '</label></p>';

	echo $html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
} // mdjm_rich_editor_callback

/**
 * Upload Callback
 *
 * Renders upload fields.
 *
 * @since   1.3
 * @param   arr $args   Arguments passed by the setting
 * @global $mdjm_options    Array of all the MDJM Options
 * @return void
 */
function mdjm_upload_callback( $args ) {
	global $mdjm_options;

	if ( isset( $mdjm_options[ $args['id'] ] ) ) {
		$value = $mdjm_options[ $args['id'] ];
	} else {
		$value = isset( $args['std'] ) ? $args['std'] : '';
	}

	$size  = ( isset( $args['size'] ) && ! is_null( $args['size'] ) ) ? $args['size'] : 'regular';
	$html  = '<input type="text" class="' . $size . '-text" id="mdjm_settings[' . $args['id'] . ']" name="mdjm_settings[' . $args['id'] . ']" value="' . esc_attr( stripslashes( $value ) ) . '"/>';
	$html .= '<span>&nbsp;<input type="button" class="mdjm_settings_upload_button button-secondary" value="' . __( 'Upload File', 'mobile-dj-manager' ) . '"/></span>';
	$html .= '<label for="mdjm_settings[' . $args['id'] . ']"> ' . $args['hint'] . '</label>';
	$html .= '<p class="description"><label for="mdjm_settings[' . $args['id'] . ']"> ' . $args['desc'] . '</label></p>';

	echo $html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
} // mdjm_upload_callback

/**
 * Color picker Callback
 *
 * Renders color picker fields.
 *
 * @since   1.3
 * @param   arr $args       Arguments passed by the setting
 * @global  $mdjm_options   Array of all the MDJM Options
 * @return void
 */
function mdjm_color_callback( $args ) {
	global $mdjm_options;

	if ( isset( $mdjm_options[ $args['id'] ] ) ) {
		$value = $mdjm_options[ $args['id'] ];
	} else {
		$value = isset( $args['std'] ) ? $args['std'] : '';
	}

	$default = isset( $args['std'] ) ? $args['std'] : '';

	$size  = ( isset( $args['size'] ) && ! is_null( $args['size'] ) ) ? $args['size'] : 'regular';
	$html  = '<input type="text" class="mdjm-color-picker" id="mdjm_settings[' . $args['id'] . ']" name="mdjm_settings[' . $args['id'] . ']" value="' . esc_attr( $value ) . '" data-default-color="' . esc_attr( $default ) . '" />';
	$html .= '<label for="mdjm_settings[' . $args['id'] . ']"> ' . $args['hint'] . '</label>';
	$html .= '<p class="description"><label for="mdjm_settings[' . $args['id'] . ']"> ' . $args['desc'] . '</label></p>';

	echo $html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
} // mdjm_color_callback

/**
 * Descriptive text callback.
 *
 * Renders descriptive text onto the settings field.
 *
 * @since   1.3
 * @param   arr $args   Arguments passed by the setting
 * @return  void
 */
function mdjm_descriptive_text_callback( $args ) {
	echo wp_kses_post( $args['desc'] );
} // mdjm_descriptive_text_callback

/**
 * Registers the license field callback for Software Licensing
 *
 * @since   1.0
 * @param   arr     $args   Arguments passed by the setting
 * @global  $mdjm_options   Array of all the MDJM options
 * @return void
 */
if ( ! function_exists( 'mdjm_license_key_callback' ) ) {
	function mdjm_license_key_callback( $args ) {

		$mdjm_option = mdjm_get_option( $args['id'] );

		$messages = array();
		$license  = get_option( $args['options']['is_valid_license_option'] );

		if ( $mdjm_option ) {
			$value = $mdjm_option;
		} else {
			$value = isset( $args['std'] ) ? $args['std'] : '';
		}

		if ( ! empty( $license ) && is_object( $license ) ) {

			// activate_license 'invalid' on anything other than valid, so if there was an error capture it
			if ( false === $license->success ) {

				switch ( $license->error ) {

					case 'expired':
					$class      = 'expired';
					$messages[] = sprintf(
						__( 'Your license key expired on %1$s. Please <a href="%2$s" target="_blank" title="Renew your license key">renew your license key</a>.', 'mobile-dj-manager' ),
						date_i18n( get_option( 'date_format' ), strtotime( $license->expires, current_time( 'timestamp' ) ) ),
						'http://mobile-dj-manager.com/checkout/?edd_license_key=' . $value
					);

					$license_status = 'license-' . $class . '-notice';

					break;

					case 'revoked':
					$class      = 'error';
					$messages[] = sprintf(
						__( 'Your license key has been disabled. Please <a href="%s" target="_blank">contact support</a> for more information.', 'mobile-dj-manager' ),
						'https://mobile-dj-manager.com/support'
					);

					$license_status = 'license-' . $class . '-notice';

					break;

					case 'missing':
					$class      = 'error';
					$messages[] = sprintf(
						__( 'Invalid license. Please <a href="%s" target="_blank" title="Visit account page">visit your account page</a> and verify it.', 'mobile-dj-manager' ),
						'https://mdjm.co.uk/your-account'
					);

					$license_status = 'license-' . $class . '-notice';

					break;

					case 'invalid':
					case 'site_inactive':
					$class      = 'error';
					$messages[] = sprintf(
						__( 'Your %1$s is not active for this URL. Please <a href="%2$s" target="_blank" title="Visit account page">visit your account page</a> to manage your license key URLs.', 'mobile-dj-manager' ),
						$args['name'],
						'https://mdjm.co.uk/your-account'
					);

					$license_status = 'license-' . $class . '-notice';

					break;

					case 'item_name_mismatch':
					$class      = 'error';
					$messages[] = sprintf( __( 'This appears to be an invalid license key for %s.', 'mobile-dj-manager' ), $args['name'] );

					$license_status = 'license-' . $class . '-notice';

					break;

					case 'no_activations_left':
					$class      = 'error';
					$messages[] = sprintf( __( 'Your license key has reached its activation limit. <a href="%s">View possible upgrades</a> now.', 'mobile-dj-manager' ), 'https://mdjm.co.uk/your-account/' );

					$license_status = 'license-' . $class . '-notice';

					break;

					case 'license_not_activable':
					$class      = 'error';
					$messages[] = __( 'The key you entered belongs to a bundle, please use the product specific license key.', 'mobile-dj-manager' );

					$license_status = 'license-' . $class . '-notice';
					break;

					default:
					$class      = 'error';
					$error      = ! empty( $license->error ) ? $license->error : __( 'unknown_error', 'mobile-dj-manager' );
					$messages[] = sprintf( __( 'There was an error with this license key: %1$s. Please <a href="%2$s">contact our support team</a>.', 'mobile-dj-manager' ), $error, 'https://mdjm.co.uk/support' );

					$license_status = 'license-' . $class . '-notice';
					break;

				}
			} else {

				switch ( $license->license ) {

					case 'valid':
					default:
					$class = 'valid';

					$now        = current_time( 'timestamp' );
					$expiration = strtotime( $license->expires, current_time( 'timestamp' ) );

					if ( 'lifetime' === $license->expires ) {

						$messages[] = __( 'License key never expires.', 'mobile-dj-manager' );

						$license_status = 'license-lifetime-notice';

					} elseif ( $expiration > $now && $expiration - $now < ( DAY_IN_SECONDS * 30 ) ) {

						$messages[] = sprintf(
							__( 'Your license key expires soon! It expires on %1$s. <a href="%2$s" target="_blank" title="Renew license">Renew your license key</a>.', 'mobile-dj-manager' ),
							date_i18n( get_option( 'date_format' ), strtotime( $license->expires, current_time( 'timestamp' ) ) ),
							'https://mdjm.co.uk/checkout/?edd_license_key=' . $value
						);

						$license_status = 'license-expires-soon-notice';

					} else {

						$messages[] = sprintf(
							__( 'Your license key expires on %s.', 'mobile-dj-manager' ),
							date_i18n( get_option( 'date_format' ), strtotime( $license->expires, current_time( 'timestamp' ) ) )
						);

						$license_status = 'license-expiration-date-notice';

					}

					break;

				}
			}
		} else {
			$class = 'empty';

			$messages[] = sprintf(
				__( 'To receive updates, please enter your valid %s license key.', 'mobile-dj-manager' ),
				$args['name']
			);

			$license_status = null;
		}

		$class .= ' ' . mdjm_sanitize_html_class( $args['field_class'] );

		$size = ( isset( $args['size'] ) && ! is_null( $args['size'] ) ) ? $args['size'] : 'regular';
		$html = '<input type="text" class="' . sanitize_html_class( $size ) . '-text" id="mdjm_settings[' . mdjm_sanitize_key( $args['id'] ) . ']" name="mdjm_settings[' . mdjm_sanitize_key( $args['id'] ) . ']" value="' . esc_attr( $value ) . '"/>';

		if ( ( is_object( $license ) && 'valid' == $license->license ) || 'valid' == $license ) {
			$html .= '<input type="submit" class="button-secondary" name="' . $args['id'] . '_deactivate" value="' . __( 'Deactivate License', 'mobile-dj-manager' ) . '"/>';
		}

		$html .= '<label for="mdjm_settings[' . mdjm_sanitize_key( $args['id'] ) . ']"> ' . wp_kses_post( $args['desc'] ) . '</label>';

		if ( ! empty( $messages ) ) {
			foreach ( $messages as $message ) {

				$html     .= '<div class="mdjm-license-data mdjm-license-' . $class . ' ' . $license_status . '">';
				$html .= '<p>' . $message . '</p>';
				$html     .= '</div>';

			}
		}

		wp_nonce_field( mdjm_sanitize_key( $args['id'] ) . '-nonce', mdjm_sanitize_key( $args['id'] ) . '-nonce' );

		echo $html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}
} // mdjm_license_key_callback

/**
 * Hook Callback
 *
 * Adds a do_action() hook in place of the field
 *
 * @since   1.3
 * @param   arr $args   Arguments passed by the setting
 * @return  void
 */
function mdjm_hook_callback( $args ) {
	do_action( 'mdjm_' . $args['id'], $args );
} // mdjm_hook_callback

/**
 * Set manage_mdjm as the cap required to save MDJM settings pages
 *
 * @since   1.3
 * @return  str     Capability required
 */
function mdjm_set_settings_cap() {
	return 'manage_mdjm';
} // mdjm_set_settings_cap
add_filter( 'option_page_capability_mdjm_settings', 'mdjm_set_settings_cap' );
