<?php
/**
 * Upgrade Functions
 *
 * @package     MDJM
 * @subpackage  Admin/Upgrades
 * @copyright   Copyright (c) 2016, Mike Howard
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.4
 *
 * Taken from Easy Digital Downloads.
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) )
	exit;

/**
 * Perform automatic database upgrades when necessary
 *
 * @since	1.4
 * @return	void
*/
function mdjm_do_automatic_upgrades() {

	$did_upgrade = false;
	$mdjm_version = preg_replace( '/[^0-9.].*/', '', get_option( 'mdjm_version' ) );

	// Version 1.3.8.5 was the last version to use the old update procedures which were applied automatically.
	if ( version_compare( $mdjm_version, '1.3.8.1', '<' ) )	{
		add_option( 'mdjm_update_me', MDJM_VERSION_NUM );
	
		if( $current_version < MDJM_VERSION_NUM )	{	
			include_once( MDJM_PLUGIN_DIR . '/includes/admin/procedures/mdjm-upgrade.php' );
		}
	}

	if( version_compare( $mdjm_version, '1.4', '<' ) ) {

		mdjm_v14_upgrades();

	}

	if( version_compare( $mdjm_version, '1.4.3', '<' ) ) {

		mdjm_v143_upgrades();

	}

	if( version_compare( $mdjm_version, '1.4.7', '<' ) ) {

		mdjm_v147_upgrades();

	}

	if( version_compare( $mdjm_version, MDJM_VERSION_NUM, '<' ) ) {

		// Let us know that an upgrade has happened
		$did_upgrade = true;

	}

	if( $did_upgrade ) {

		// Send to what's new page
		if ( substr_count( MDJM_VERSION_NUM, '.' ) < 2 )	{
			set_transient( '_mdjm_activation_redirect', true, 30 );
		}

		update_option( 'mdjm_version_upgraded_from', get_option( 'mdjm_version' ) );
		update_option( 'mdjm_version', preg_replace( '/[^0-9.].*/', '', MDJM_VERSION_NUM ) );

	}

} // mdjm_do_automatic_upgrades
add_action( 'admin_init', 'mdjm_do_automatic_upgrades' );

/**
 * Display a notice if an upgrade is required.
 *
 * @since	1.4
 */
function mdjm_show_upgrade_notice()	{

	if ( isset( $_GET['page'] ) && $_GET['page'] == 'mdjm-upgrades' )	{
		return;
	}

	$mdjm_version = get_option( 'mdjm_version' );

	$mdjm_version = preg_replace( '/[^0-9.].*/', '', $mdjm_version );

	// Check if there is an incomplete upgrade routine.
	$resume_upgrade = mdjm_maybe_resume_upgrade();

	if ( ! empty( $resume_upgrade ) )	{

		$resume_url = add_query_arg( $resume_upgrade, admin_url( 'index.php' ) );
		printf(
			'<div class="notice notice-error"><p>' . __( 'MDJM Event Management needs to complete an upgrade that was previously started. Click <a href="%s">here</a> to resume the upgrade.', 'mobile-dj-manager' ) . '</p></div>',
			esc_url( $resume_url )
		);

	} else {

		if ( version_compare( $mdjm_version, '1.4', '<' ) || ! mdjm_has_upgrade_completed( 'upgrade_event_packages' ) )	{
			printf(
				'<div class="notice notice-error"><p>' . __( 'MDJM Event Management needs to perform an upgrade to %s Packages and Add-ons. Click <a href="%s">here</a> to start the upgrade.', 'mobile-dj-manager' ) . '</p></div>',
				mdjm_get_label_singular( true ),
				esc_url( admin_url( 'index.php?page=mdjm-upgrades&mdjm-upgrade=upgrade_event_packages&message=1&redirect=' . mdjm_get_current_page_url() ) )
			);
		}

		if ( version_compare( $mdjm_version, '1.4.7', '<' ) || ! mdjm_has_upgrade_completed( 'upgrade_event_tasks' ) )	{
			printf(
				'<div class="notice notice-error"><p>' . __( 'MDJM Event Management needs to perform an upgrade to the %s database. Click <a href="%s">here</a> to start the upgrade.', 'mobile-dj-manager' ) . '</p></div>',
				mdjm_get_label_plural( true ),
				esc_url( admin_url( 'index.php?page=mdjm-upgrades&mdjm-upgrade=upgrade_event_tasks&message=1&redirect=' . mdjm_get_current_page_url() ) )
			);
		}

		/*
		 *  NOTICE:
		 *
		 *  When adding new upgrade notices, please be sure to put the action into the upgrades array during install:
		 *  /includes/install.php @ Appox Line 783
		 *
		 */

	}

} // mdjm_show_upgrade_notice
add_action( 'admin_notices', 'mdjm_show_upgrade_notice' );

/**
 * Triggers all upgrade functions.
 *
 * This function is usually triggered via AJAX.
 *
 * @since	1.4
 * @return	void
*/
function mdjm_trigger_upgrades() {

	if( ! mdjm_employee_can( 'manage_mdjm' ) ) {
		wp_die( __( 'You do not have permission to do perform MDJM upgrades', 'mobile-dj-manager' ), __( 'Error', 'mobile-dj-manager' ), array( 'response' => 403 ) );
	}

	update_option( 'mdjm_version', MDJM_VERSION_NUM );

	if ( DOING_AJAX )	{
		die( 'complete' ); // Let AJAX know that the upgrade is complete
	}

} // mdjm_trigger_upgrades
add_action( 'wp_ajax_mdjm_trigger_upgrades', 'mdjm_trigger_upgrades' );

/**
 * For use when doing 'stepped' upgrade routines, to see if we need to start somewhere in the middle.
 *
 * @since	1.4
 * @return	mixed	When nothing to resume returns false, otherwise starts the upgrade where it left off.
 */
function mdjm_maybe_resume_upgrade() {

	$doing_upgrade = get_option( 'mdjm_doing_upgrade', false );

	if ( empty( $doing_upgrade ) ) {
		return false;
	}

	return $doing_upgrade;

} // mdjm_maybe_resume_upgrade

/**
 * Adds an upgrade action to the completed upgrades array.
 *
 * @since	1.4
 * @param	str		$upgrade_action		The action to add to the copmleted upgrades array.
 * @return	bool	If the function was successfully added.
 */
function mdjm_set_upgrade_complete( $upgrade_action = '' ) {

	if ( empty( $upgrade_action ) ) {
		return false;
	}

	$completed_upgrades   = mdjm_get_completed_upgrades();
	$completed_upgrades[] = $upgrade_action;

	// Remove any blanks, and only show uniques
	$completed_upgrades = array_unique( array_values( $completed_upgrades ) );

	return update_option( 'mdjm_completed_upgrades', $completed_upgrades );
} // mdjm_set_upgrade_complete

/**
 * Migrates event packages and addons from the options table to posts.
 *
 * @since	1.4
 * @return	void
 */
function mdjm_v14_upgrades()	{
	global $wpdb;

	if( ! mdjm_employee_can( 'manage_mdjm' ) ) {
		wp_die( __( 'You do not have permission to do perform MDJM upgrades', 'mobile-dj-manager' ), __( 'Error', 'mobile-dj-manager' ), array( 'response' => 403 ) );
	}

	ignore_user_abort( true );

	if ( ! mdjm_is_func_disabled( 'set_time_limit' ) && ! ini_get( 'safe_mode' ) ) {
		@set_time_limit( 0 );
	}

	// Drop the deprecated Playlists table
	$results = $wpdb->get_results( "SHOW TABLES LIKE '" . $wpdb->prefix . 'mdjm_playlists' . "'" );
	if( $results )	{
		$wpdb->query( 'DROP TABLE IF EXISTS ' . $wpdb->prefix . 'mdjm_playlists' );
	}

	$items             = array();
	$packages          = array();
	$existing_items    = get_option( 'mdjm_equipment', array() );
	$existing_cats     = get_option( 'mdjm_cats' );
	$existing_packages = get_option( 'mdjm_packages', array() );

	$convert_addons = get_option( 'mdjm_upgrade_v14_import_addons' );

	if ( ! $convert_addons )	{

		if ( $existing_items )	{ // If addons exist we need to convert them

			foreach( $existing_items as $slug => $existing_item )	{

				$employees = array( 'all' );

				if ( ! empty( $existing_item[8] ) )	{
					$employees = explode( ',', $existing_item[8] );
				}

				if ( $existing_cats && ! term_exists( $existing_cats[ $existing_item[5] ], 'addon-category' ) )	{
					wp_insert_term( $existing_cats[ $existing_item[5] ], 'addon-category', array( 'slug' => $existing_item[5] ) );

					$category = get_term_by( 'name', $existing_cats[ $existing_item[5] ], 'addon-category' );
				}

				$args = array(
					'post_type'     => 'mdjm-addon',
					'post_content'  => ! empty( $existing_item[4] ) ? stripslashes( $existing_item[4] ) : '',
					'post_title'    => $existing_item[0],
					'post_status'   => 'publish',
					'post_name'     => stripslashes( $slug ),
					'post_category' => isset( $category ) ? array( $category->term_id ) : '',
					'meta_input'    => array(
						'_addon_employees'        => $employees,
						'_addon_event_types'      => array( 'all' ),
						'_addon_restrict_date'    => false,
						'_addon_months'           => array(),
						'_addon_price'            => ! empty( $existing_item[7] ) ? mdjm_format_amount( $existing_item[7] ) : mdjm_format_amount( '0' ),
						'_addon_variable_pricing' => false,
						'_addon_variable_prices'  => false
					)
				);

				$addon_id = wp_insert_post( $args );

				if ( $addon_id )	{
					if( ! empty( $category ) )	{
						mdjm_set_addon_category( $addon_id, $category->term_id );
					}

					$items[ $slug ] = $addon_id; // Store each addon's slug and new post ID for use later
				}
		
			}

		}

	}

	// Log addon upgrade procedure as completed
	set_transient( 'mdjm_upgrade_v14_import_addons', $items, 30 * DAY_IN_SECONDS );

	$convert_packages = get_option( 'mdjm_upgrade_v14_import_packages' );

	if ( ! $convert_packages )	{
		
		foreach ( $existing_packages as $slug => $existing_package )	{

			$addons    = array();
			$equipment = array();
			$employees = array( 'all' );

			if ( ! empty( $existing_package['djs'] ) )	{
				$employees = explode( ',', $existing_package['djs'] );
			}

			if ( ! empty( $existing_package['equipment'] ) )	{
				$equipment = explode( ',', $existing_package['equipment'] );
			}

			if ( ! empty( $equipment ) )	{
				foreach( $equipment as $item )	{
					if ( ! empty( $items[ $item ] ) )	{
						$addons[] = $items[ $item ];
					}
				}
			}

			$args = array(
				'post_type'     => 'mdjm-package',
				'post_content'  => ! empty( $existing_package['desc'] ) ? stripslashes( $existing_package['desc'] ) : '',
				'post_title'    => ! empty( $existing_package['name'] ) ? stripslashes( $existing_package['name'] ) : '',
				'post_status'   => 'publish',
				'post_name'     => stripslashes( $slug ),
				'meta_input'    => array(
					'_package_employees'        => $employees,
					'_package_event_types'      => array( 'all' ),
					'_package_restrict_date'    => false,
					'_package_months'           => array(),
					'_package_items'            => array_unique( $addons ),
					'_package_price'            => ! empty( $existing_package['cost'] ) ? mdjm_format_amount( $existing_package['cost'] ) : mdjm_format_amount( '0' ),
					'_package_variable_pricing' => false,
					'_package_variable_prices'  => false
				)
			);

			$package_id = wp_insert_post( $args );

			if ( $package_id )	{
				$packages[ $slug ] = $package_id; // Store each addon's slug and new post ID for use later
			}

		}

	}

	// Clear the permalinks
	flush_rewrite_rules( false );

	// Log package upgrade procedure as completed
	set_transient( 'mdjm_upgrade_v14_import_packages', $packages, 30 * DAY_IN_SECONDS );
	update_option( 'mdjm_version', preg_replace( '/[^0-9.].*/', '', MDJM_VERSION_NUM ) );
} // mdjm_v14_upgrades

/**
 * Upgrade event packages from slugs to new post ID's.
 *
 * @since	1.4
 * @return	void
 */
function mdjm_v14_upgrade_event_packages()	{

	global $wpdb;

	ignore_user_abort( true );

	if ( ! mdjm_is_func_disabled( 'set_time_limit' ) && ! ini_get( 'safe_mode' ) ) {
		@set_time_limit( 0 );
	}

	$items    = get_transient( 'mdjm_upgrade_v14_import_addons' );
	$packages = get_transient( 'mdjm_upgrade_v14_import_packages' );

	$number   = 50;
	$step     = isset( $_GET['step'] )     ? absint( $_GET['step'] ) : 1;
	$offset   = $step == 1                 ? 0                       : ( $step - 1 ) * $number;
	$redirect = isset( $_GET['redirect'] ) ? $_GET['redirect']       : admin_url( 'edit.php?post_type=mdjm-event' );
	$message  = isset( $_GET['message'] )  ? 'upgrade-completed'     : '';

	if ( $step < 2 ) {
		// Check if we have any events before moving on
		$sql = "SELECT ID FROM $wpdb->posts WHERE post_type = 'mdjm-event' LIMIT 1";
		$has_events = $wpdb->get_col( $sql );

		if ( empty( $has_events ) || ( 'false' === $items && 'false' === $packages ) ) {
			// We had no events, addons, or packages, just complete
			update_option( 'mdjm_version', preg_replace( '/[^0-9.].*/', '', MDJM_VERSION_NUM ) );
			mdjm_set_upgrade_complete( 'upgrade_event_packages' );
			delete_option( 'mdjm_doing_upgrade' );
			wp_redirect( $redirect );
			exit;
		}
	}

	$total = isset( $_GET['total'] ) ? absint( $_GET['total'] ) : false;
	if ( empty( $total ) || $total <= 1 ) {
		$total_sql = "SELECT COUNT(post_id) as total_events FROM $wpdb->postmeta WHERE meta_value != '' AND (meta_key = '_mdjm_event_package' OR meta_key = '_mdjm_event_addons')";
		$results   = $wpdb->get_row( $total_sql, 0 );

		$total     = $results->total_events;
	}

	$event_ids = $wpdb->get_col( $wpdb->prepare( 
		"
			SELECT post_id 
			FROM $wpdb->postmeta 
			WHERE meta_value != '' 
			AND (
				meta_key = %s 
				OR 
				meta_key = %s
			) 
			ORDER BY post_id DESC LIMIT %d,%d;
		", 
		'_mdjm_event_package', '_mdjm_event_addons', $offset, $number
	) );

	if( $event_ids )	{
		foreach( $event_ids as $event_id )	{
			$addons = array();

			$current_package = get_post_meta( $event_id, '_mdjm_event_package', true );
			$current_items   = get_post_meta( $event_id, '_mdjm_event_addons', true );

			if ( ! empty( $current_package ) )	{
				update_post_meta( $event_id, '_mdjm_event_package_pre_v14', $current_package );
				if ( array_key_exists( $current_package, $packages ) )	{
					update_post_meta( $event_id, '_mdjm_event_package', $packages[ $current_package ] );
				}
			}

			if ( ! empty( $current_items ) && is_array( $current_items ) )	{
				update_post_meta( $event_id, '_mdjm_event_addons_pre_v14', $current_items );
				$addons = array();

				foreach ( $current_items as $current_item )	{
					if ( array_key_exists( $current_item, $items ) )	{
						$addons[] = $items[ $current_item ];
					}
				}

				if ( ! empty( $addons ) )	{
					update_post_meta( $event_id, '_mdjm_event_addons', $addons );
				}

			}
		}

		// Events with packages/addons found so upgrade them
		$step++;
		$redirect = add_query_arg( array(
			'page'         => 'mdjm-upgrades',
			'mdjm-upgrade' => 'upgrade_event_packages',
			'step'         => $step,
			'number'       => $number,
			'total'        => $total,
			'redirect'     => $redirect,
			'message'      => $message
		), admin_url( 'index.php' ) );

		wp_redirect( $redirect );
		exit;

	} else {
		// No more events found, finish up
		mdjm_set_upgrade_complete( 'upgrade_event_packages' );
		delete_option( 'mdjm_doing_upgrade' );

		$url = add_query_arg( array(
			'mdjm-message' => $message
		), $redirect );

		wp_redirect( $url );
		exit;
	}

} // mdjm_v14_upgrade_event_packages
add_action( 'mdjm-upgrade_event_packages', 'mdjm_v14_upgrade_event_packages' );

/**
 * Set all MDJM Journal Entry comment_type columns to mdjm-journal.
 *
 * @since	1.4.3
 * @return	void
 */
function mdjm_v143_upgrades()	{
	global $wpdb;

	if( ! mdjm_employee_can( 'manage_mdjm' ) ) {
		wp_die( __( 'You do not have permission to do perform MDJM upgrades', 'mobile-dj-manager' ), __( 'Error', 'mobile-dj-manager' ), array( 'response' => 403 ) );
	}

	ignore_user_abort( true );

	if ( ! mdjm_is_func_disabled( 'set_time_limit' ) && ! ini_get( 'safe_mode' ) ) {
		@set_time_limit( 0 );
	}

	// Set comment type on journal entries
	$wpdb->update(
		$wpdb->comments,
		array( 'comment_type' => 'mdjm-journal' ),
		array( 'comment_type' => 'update-event' )
	);

	// Sanitize client field IDs
	$client_fields = get_option( 'mdjm_client_fields' );

	if ( $client_fields )	{
		foreach( $client_fields as $field_id => $client_field )	{
			if ( ! empty( $client_field['default'] ) )	{
				continue;
			}
	
			$client_fields[ $field_id ]['id'] = sanitize_title_with_dashes( $client_field['label'], '', 'save' );
		}

		update_option( 'mdjm_client_fields', $client_fields );
	}

} // mdjm_v143_upgrades

/**
 * Update schedules.
 *
 * @since	1.4.7
 * @return	void
 */
function mdjm_v147_upgrades()	{
	if ( ! mdjm_employee_can( 'manage_mdjm' ) ) {
		wp_die( __( 'You do not have permission to do perform MDJM upgrades', 'mobile-dj-manager' ), __( 'Error', 'mobile-dj-manager' ), array( 'response' => 403 ) );
	}

	ignore_user_abort( true );

	if ( ! mdjm_is_func_disabled( 'set_time_limit' ) && ! ini_get( 'safe_mode' ) ) {
		@set_time_limit( 0 );
	}

	// Update schedules
	$tasks       = get_option( 'mdjm_schedules' );
	$email_tasks = array(
		'request-deposit',
		'balance-reminder'
	);

	if ( $tasks )	{

		foreach( $tasks as $slug => $task )	{
			$active       = false;
			$lastrun      = false;
			$default      = false;

			if ( ! empty( $task['active'] ) && 'Y' == $task['active'] )	{
				$active = true;
			}

			if ( ! empty( $task['lastran'] ) && 'Never' != $task['lastran'] )	{
				$lastrun = $task['lastran'];
			}

			if ( ! empty( $task['default'] ) && 'Y' == $task['default'] )	{
				$default = true;
			} else	{
				$default = false;
			}

			$tasks[ $slug ]['active']                  = $active;
			$tasks[ $slug ]['lastran']                 = $lastrun;
			$tasks[ $slug ]['default']                 = $default;
			$tasks[ $slug ]['last_result']             = false;

			unset( $tasks[ $slug ]['options']['email_client'] );
			unset( $tasks[ $slug ]['options']['notify_admin'] );
			unset( $tasks[ $slug ]['options']['notify_dj'] );

			if ( ! in_array( $slug, $email_tasks ) )	{
				unset( $tasks[ $slug ]['options']['email_template'] );
				unset( $tasks[ $slug ]['options']['email_subject'] );
				unset( $tasks[ $slug ]['options']['email_from'] );
			} else	{
				if ( isset( $tasks[ $slug ]['options']['email_from'] ) && 'dj' == $tasks[ $slug ]['options']['email_from'] )	{
					$tasks[ $slug ]['options']['email_from'] = 'employee';
				} else	{
					$tasks[ $slug ]['options']['email_from'] = 'admin';
				}
			}

		}

		update_option( 'mdjm_schedules', $tasks );

	}

	wp_clear_scheduled_hook( 'mdjm_hourly_schedule' );

	delete_option( 'mdjm_uninst' );

} // mdjm_v147_upgrades

/**
 * Ensure all events have the _mdjm_event_tasks meta key.
 *
 * @since	1.4.7
 * @return	void
 */
function mdjm_v147_upgrade_event_tasks()	{

	global $wpdb;

	ignore_user_abort( true );

	if ( ! mdjm_is_func_disabled( 'set_time_limit' ) && ! ini_get( 'safe_mode' ) ) {
		@set_time_limit( 0 );
	}

	$number   = 20;
	$step     = isset( $_GET['step'] )     ? absint( $_GET['step'] ) : 1;
	$offset   = $step == 1                 ? 0                       : ( $step - 1 ) * $number;
	$redirect = isset( $_GET['redirect'] ) ? $_GET['redirect']       : admin_url( 'edit.php?post_type=mdjm-event' );
	$message  = isset( $_GET['message'] )  ? 'upgrade-completed'     : '';

	if ( $step < 2 ) {
		// Check if we have any events before moving on
		$sql = "SELECT ID FROM $wpdb->posts WHERE post_type = 'mdjm-event' LIMIT 1";
		$has_events = $wpdb->get_col( $sql );

		if ( empty( $has_events ) ) {
			// We had no events, just complete
			update_option( 'mdjm_version', preg_replace( '/[^0-9.].*/', '', MDJM_VERSION_NUM ) );
			mdjm_set_upgrade_complete( 'upgrade_event_tasks' );
			delete_option( 'mdjm_doing_upgrade' );
			wp_redirect( $redirect );
			exit;
		}
	}

	$total = isset( $_GET['total'] ) ? absint( $_GET['total'] ) : false;
	if ( empty( $total ) || $total <= 1 ) {
		$total_sql = "SELECT COUNT(ID) as total_events FROM $wpdb->posts WHERE post_type = 'mdjm-event'";
		$results   = $wpdb->get_row( $total_sql, 0 );

		$total     = $results->total_events;
	}

	$event_ids = $wpdb->get_col( $wpdb->prepare( 
		"
			SELECT ID 
			FROM $wpdb->posts 
			WHERE post_type = 'mdjm-event'
			ORDER BY ID DESC LIMIT %d,%d;
		", 
		$offset, $number
	) );

	if ( $event_ids )	{
		foreach( $event_ids as $event_id )	{

            $tasks = array();
            $event = new MDJM_Event( $event_id );

            if ( 'mdjm-completed' == $event->post_status )   {
                $tasks['complete-events'] = current_time( 'timestamp' );
            }

            if ( 'mdjm-failed' == $event->post_status )   {
                $tasks['fail-enquiry'] = current_time( 'timestamp' );
            }

            if ( 'Paid' == $event->get_deposit_status() )   {
                $tasks['request-deposit'] = current_time( 'timestamp' );
            }

            if ( 'Paid' == $event->get_balance_status() )   {
                $tasks['balance-reminder'] = current_time( 'timestamp' );
            }

			add_post_meta( $event_id, '_mdjm_event_tasks', $tasks, true );
		}

		// Events found so upgrade them
		$step++;
		$redirect = add_query_arg( array(
			'page'         => 'mdjm-upgrades',
			'mdjm-upgrade' => 'upgrade_event_tasks',
			'step'         => $step,
			'number'       => $number,
			'total'        => $total,
			'redirect'     => $redirect,
			'message'      => $message
		), admin_url( 'index.php' ) );

		wp_redirect( $redirect );
		exit;

	} else {
		// No more events found, finish up
		mdjm_set_upgrade_complete( 'upgrade_event_tasks' );
		delete_option( 'mdjm_doing_upgrade' );

		$url = add_query_arg( array(
			'mdjm-message' => 'upgrade-completed'
		), $redirect );

		wp_redirect( $url );
		exit;
	}

} // mdjm_v147_upgrade_event_tasks
add_action( 'mdjm-upgrade_event_tasks', 'mdjm_v147_upgrade_event_tasks' );
