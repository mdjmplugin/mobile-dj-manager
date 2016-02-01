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
 * @param	$option	str		The option to retrieve
 * @param	$key	str		The option key to retrieve
 * @return	mixed
 */
function mdjm_get_option( $option ='', $key = '', $default = false )	{
	global $mdjm_options;
	
	$value = ! empty( $mdjm_options[ $option ][ $key ] ) ? $mdjm_options[ $option ][ $key ] : $default;
	$value = apply_filters( 'mdjm_get_option', $value, $option, $key, $default );
	
	return apply_filters( 'mdjm_get_option' . $key, $value, $option, $key, $default );
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
					)
				)
			)
		)
	);
} // mdjm_get_registered_settings
