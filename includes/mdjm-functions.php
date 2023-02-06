<?php


/*
 * mdjm-functions.php
 * 17/03/2015
 * Contains all main MDJM functions used in front & back end
 *
 */

/*
 * START GENERAL FUNCTIONS
 */
	/*
	 * Return the admin URL for the given page
	 *
	 *
	 *
	 * @params 	STR		$mdjm_page	Required: The page for which we want the URL
	 * 			str		$action		Optional: Whether to return as string (Default) or echo the URL.
	 * @returns $mdjm_page - str or echo
	 */
function mdjm_get_admin_page( $mdjm_page, $action = 'str' ) {
	if ( empty( $mdjm_page ) ) {
		return;
	}

	$mobiledjmanager = array( 'mobiledjmanager', 'user_guides', 'mdjm_support', 'mdjm_forums' );
	$mdjm_pages          = array(
		'wp_dashboard'        => 'index.php',
		'dashboard'           => 'admin.php?page=mdjm-dashboard',
		'settings'            => 'admin.php?page=mdjm-settings',
		'payment_settings'    => 'admin.php?page=mdjm-settings&tab=payments',
		'clientzone_settings' => 'admin.php?page=mdjm-settings&tab=client-zone',
		'clients'             => 'admin.php?page=mdjm-clients',
		'employees'           => 'admin.php?page=mdjm-employees',
		'permissions'         => 'admin.php?page=mdjm-employees&tab=permissions',
		'inactive_clients'    => 'admin.php?page=mdjm-clients&display=inactive_client',
		'add_client'          => 'user-new.php',
		'edit_client'         => 'user-edit.php?user_id=',
		'comms'               => 'admin.php?page=mdjm-comms',
		'email_history'       => 'edit.php?post_type=mdjm_communication',
		'contract'            => 'edit.php?post_type=contract',
		'signed_contract'     => 'edit.php?post_type=mdjm-signed-contract',
		'add_contract'        => 'post-new.php?post_type=contract',
		'djs'                 => 'admin.php?page=mdjm-djs',
		'inactive_djs'        => 'admin.php?page=mdjm-djs&display=inactive_dj',
		'email_template'      => 'edit.php?post_type=email_template',
		'add_email_template'  => 'post-new.php?post_type=email_template',
		'equipment'           => 'admin.php?page=mdjm-packages',
		'events'              => 'edit.php?post_type=mdjm-event',
		'add_event'           => 'post-new.php?post_type=mdjm-event',
		'enquiries'           => 'edit.php?post_status=mdjm-enquiry&post_type=mdjm-event',
		'unattended'          => 'edit.php?post_status=mdjm-unattended&post_type=mdjm-event',
		'awaitingdeposit'     => 'edit.php?post_status=mdjm0awaitingdeposit&post_type=mdjm-event',
		'playlists'           => 'admin.php?page=mdjm-playlists&event_id=',
		'custom_event_fields' => 'admin.php?page=mdjm-custom-event-fields',
		'venues'              => 'edit.php?post_type=mdjm-venue',
		'add_venue'           => 'post-new.php?post_type=mdjm-venue',
		'tasks'               => 'admin.php?page=mdjm-tasks',
		'client_text'         => 'admin.php?page=mdjm-settings&tab=client-zone&section=mdjm_app_text',
		'client_fields'       => 'admin.php?page=mdjm-custom-client-fields',
		'availability'        => 'admin.php?page=mdjm-availability',
		'debugging'           => 'admin.php?page=mdjm-settings&tab=general&section=mdjm_app_debugging',
		'contact_forms'       => 'admin.php?page=mdjm-contact-forms',
		'transactions'        => 'edit.php?post_type=mdjm-transaction',
		'updated'             => 'admin.php?page=mdjm-updated',
		'about'               => 'admin.php?page=mdjm-about',
		'mobiledjmanager'	  => 'http://mdjm.co.uk',
		'user_guides'         => 'http://mdjm.co.uk/support',
		'mdjm_support'        => 'http://mdjm.co.uk/support',
		'mdjm_forums'         => 'http://mdjm.co.uk/support',
	);
	if ( in_array( $mdjm_page, $mobiledjmanager ) ) {
		$mdjm_page = $mdjm_pages[ $mdjm_page ];
	} else {
		$mdjm_page = admin_url( $mdjm_pages[ $mdjm_page ] );
	}
	if ( $action == 'str' ) {
		return $mdjm_page;
	} else {
		echo esc_url( $mdjm_page );
		return;
	}
} // mdjm_get_admin_page

	/*
	 * Display update notice within Admin UI
	 *
	 * @param	str		$class		Required: The admin notice class - updated | update-nag | error
	 *			str		$message	Required: Translated notice message
	 *			bool	$dismiss	Optional: true will make the notice dismissable. Default false.
	 *
	 */
function mdjm_update_notice( $class, $message, $dismiss = '' ) {
	$dismiss = ( ! empty( $dismiss ) ? ' notice is-dismissible' : '' );

	echo '<div id="message" class="' . esc_attr( $class ) . esc_attr( $dismiss ) . '">';
	echo '<p>' . esc_html__( $message, 'mobile-dj-manager' ) . '</p>';
	echo '</div>';
} // mdjm_update_notice

/**
 * -- START CUSTOM FIELD FUNCTIONS
 */
	/**
	 * Retrieve all custom fields for the relevant section of the event
	 *
	 * @param   str $section    Optional: The section for which to retrieve the fields. If empty retrieve all
	 *      str     $orderby    Optional. Which field to order by. Default to menu order
	 *      str     $order      Optional. ASC or DESC. Default ASC
	 *      int     $limit      Optional: The number of results to return. Default -1 (all)
	 *
	 * @return  arr     $fields     The custom event fields
	 */
function mdjm_get_custom_fields( $section = '', $orderby = 'menu_order', $order = 'ASC', $limit = -1 ) {
	// Retrieve fields for given $section and return as object
	if ( ! empty( $section ) ) {

		$custom_fields = new WP_Query(
			array(
				'posts_per_page' => $limit,
				'post_type'      => 'mdjm-custom-fields',
				'post_status'    => 'publish',
				'meta_query'     => array(
					'field_clause' => array(
						'key'   => '_mdjm_field_section',
						'value' => $section,
					),
				),
				'orderby'        => array(
					'field_clause' => $order,
					$orderby       => $order,
				),
				'order'          => $order,
			)
		);

	} else { // Retrieve fields for all custom event fields return as object

		$custom_fields = new WP_Query(
			array(
				'posts_per_page' => $limit,
				'post_type'      => 'mdjm-custom-fields',
				'post_status'    => 'publish',
				'meta_query'     => array(
					'field_clause' => array(
						'key' => '_mdjm_field_section',
					),
				),
				'orderby'        => array(
					'field_clause' => $order,
					$orderby       => $order,
				),
				'order'          => $order,
			)
		);

	}

	return $custom_fields;
} // mdjm_get_custom_fields

/**
 * -- END CUSTOM FIELD FUNCTIONS
 */
