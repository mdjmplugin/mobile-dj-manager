<?php
/**
 * This plugin utilizes Open Source code. Details of these open source projects along with their licenses can be found below.
 * We acknowledge and are grateful to these developers for their contributions to open source.
 *
 * Project: mobile-dj-manager https://github.com/deckbooks/mobile-dj-manager
 * License: (GNU General Public License v2.0) https://github.com/deckbooks/mobile-dj-manager/blob/master/license.txt
 *
 * @author: Mike Howard, Jack Mawhinney, Dan Porter
 *
 * Admin Notices
 *
 * @package     MDJM
 * @subpackage  Admin/Notices
 * @copyright   Copyright (c) 2016, Mike Howard
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.3
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Retrieve all dismissed notices.
 *
 * @since   1.5
 * @return  array   Array of dismissed notices
 */
function mdjm_dismissed_notices() {

	global $current_user;

	$user_notices = (array) get_user_option( 'mdjm_dismissed_notices', $current_user->ID );

	return $user_notices;

} // mdjm_dismissed_notices

/**
 * Check if a specific notice has been dismissed.
 *
 * @since   1.5
 * @param   string $notice Notice to check.
 * @return  bool    Whether or not the notice has been dismissed
 */
function mdjm_is_notice_dismissed( $notice ) {

	$dismissed = mdjm_dismissed_notices();

	if ( array_key_exists( $notice, $dismissed ) ) {
		return true;
	} else {
		return false;
	}

} // mdjm_is_notice_dismissed

/**
 * Dismiss a notice.
 *
 * @since   1.5
 * @param   string $notice Notice to dismiss.
 * @return  bool|int    True on success, false on failure, meta ID if it didn't exist yet
 */
function mdjm_dismiss_notice( $notice ) {

	global $current_user;

	$new = (array) mdjm_dismissed_notices();
	$dismissed_notices = $new;

	if ( ! array_key_exists( $notice, $dismissed_notices ) ) {
		$new[ $notice ] = 'true';
	}

	$update = update_user_option( $current_user->ID, 'mdjm_dismissed_notices', $new );

	return $update;

} // mdjm_dismiss_notice

/**
 * Restore a dismissed notice.
 *
 * @since   1.5
 * @param   string $notice Notice to restore.
 * @return  bool|int    True on success, false on failure, meta ID if it didn't exist yet
 */
function mdjm_restore_notice( $notice ) {

	global $current_user;

	$dismissed_notices = (array) mdjm_dismissed_notices();

	if ( array_key_exists( $notice, $dismissed_notices ) ) {
		unset( $dismissed_notices[ $notice ] );
	}

	$update = update_user_option( $current_user->ID, 'mdjm_dismissed_notices', $dismissed_notices );

	return $update;

} // mdjm_restore_notice

/**
 * Admin Messages
 *
 * @since   1.3
 * @global  $mdjm_options   Array of all the MDJM Options
 * @return void
 */
function mdjm_admin_notices() {
	global $mdjm_options;

	// Unattended events.
	if ( mdjm_employee_can( 'manage_all_events' ) && ( mdjm_get_option( 'warn_unattended' ) ) ) {
		$unattended = mdjm_event_count( 'mdjm-unattended' );

		if ( ! empty( $unattended ) && $unattended > 0 ) {
			echo '<div class="notice notice-info is-dismissible">';
			echo '<p>' .
					sprintf(
						/* translators: %s is the link to the Unattended Enquiries area. */
						__( 'You have unattended enquiries. <a href="%s">Click here</a> to manage.', 'mobile-dj-manager' ), // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
						admin_url( 'edit.php?post_type=mdjm-event&post_status=mdjm-unattended' ) // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
					) . '</p>';
			echo '</div>';
		}
	}

	// Settings.
	if ( isset( $_GET['mdjm-message'] ) && 'upgrade-completed' === $_GET['mdjm-message'] ) {
		add_settings_error(
			'mdjm-notices',
			'mdjm-upgraded',
			__( 'Mobile DJ Manager has been upgraded successfully.', 'mobile-dj-manager' ),
			'updated'
		);
	}

	// Availability.
	if ( isset( $_GET['mdjm-message'] ) && 'absence-added' === $_GET['mdjm-message'] ) {
		add_settings_error(
			'mdjm-notices',
			'mdjm-absence-added',
			__( 'Absence added.', 'mobile-dj-manager' ),
			'updated'
		);
	}

	if ( isset( $_GET['mdjm-message'] ) && 'absence-fail' === $_GET['mdjm-message'] ) {
		add_settings_error(
			'mdjm-notices',
			'mdjm-absence-fail',
			__( 'Absence could not be added.', 'mobile-dj-manager' ),
			'error'
		);
	}

	if ( isset( $_GET['mdjm-message'] ) && 'absence-removed' === $_GET['mdjm-message'] ) {
		add_settings_error(
			'mdjm-notices',
			'mdjm-absence-deleted',
			__( 'Absence deleted.', 'mobile-dj-manager' ),
			'updated'
		);
	}

	if ( isset( $_GET['mdjm-message'] ) && 'absence-delete-fail' === $_GET['mdjm-message'] ) {
		add_settings_error(
			'mdjm-notices',
			'mdjm-absence-remove-fail',
			__( 'Absence could not be deleted.', 'mobile-dj-manager' ),
			'error'
		);
	}

	if ( isset( $_GET['mdjm-message'] ) && 'song_added' === $_GET['mdjm-message'] ) {
		add_settings_error(
			'mdjm-notices',
			'mdjm-added-song',
			__( 'Entry added to playlist.', 'mobile-dj-manager' ),
			'updated'
		);
	}

	if ( isset( $_GET['mdjm-message'] ) && 'adding_song_failed' === $_GET['mdjm-message'] ) {
		add_settings_error(
			'mdjm-notices',
			'mdjm-adding-song-failed',
			__( 'Could not add entry to playlist.', 'mobile-dj-manager' ),
			'error'
		);
	}

	if ( isset( $_GET['mdjm-message'] ) && 'song_removed' === $_GET['mdjm-message'] ) {
		add_settings_error(
			'mdjm-notices',
			'mdjm-removed-song',
			__( 'The selected songs were removed.', 'mobile-dj-manager' ),
			'updated'
		);
	}

	if ( isset( $_GET['mdjm-message'] ) && 'song_remove_failed' === $_GET['mdjm-message'] ) {
		add_settings_error(
			'mdjm-notices',
			'mdjm-remove-faled',
			__( 'The songs count not be removed.', 'mobile-dj-manager' ),
			'error'
		);
	}

	if ( isset( $_GET['mdjm-message'] ) && 'security_failed' === $_GET['mdjm-message'] ) {
		add_settings_error(
			'mdjm-notices',
			'mdjm-security-failed',
			__( 'Security verification failed. Action not completed.', 'mobile-dj-manager' ),
			'error'
		);
	}

	if ( isset( $_GET['mdjm-message'] ) && 'playlist_emailed' === $_GET['mdjm-message'] ) {
		add_settings_error(
			'mdjm-notices',
			'mdjm-playlist-emailed',
			__( 'The event details were emailed successfully.', 'mobile-dj-manager' ),
			'updated'
		);
	}

	if ( isset( $_GET['mdjm-message'] ) && 'playlist_email_failed' === $_GET['mdjm-message'] ) {
		add_settings_error(
			'mdjm-notices',
			'mdjm-playlist-email-failed',
			__( 'The event details could not be emailed. Check there is an email address associated to the event staff.', 'mobile-dj-manager' ),
			'error'
		);
	}

	if ( isset( $_GET['mdjm-message'] ) && 'employee_added' === $_GET['mdjm-message'] ) {
		add_settings_error(
			'mdjm-notices',
			'mdjm-employee_added',
			__( 'Employee added.', 'mobile-dj-manager' ),
			'updated'
		);
	}
	if ( isset( $_GET['mdjm-message'] ) && 'employee_add_failed' === $_GET['mdjm-message'] ) {
		add_settings_error(
			'mdjm-notices',
			'mdjm-employee_add-failed',
			__( 'Could not add employee.', 'mobile-dj-manager' ),
			'updated'
		);
	}
	if ( isset( $_GET['mdjm-message'] ) && 'employee_info_missing' === $_GET['mdjm-message'] ) {
		add_settings_error(
			'mdjm-notices',
			'mdjm-employee_info-missing',
			__( 'Insufficient information to create employee.', 'mobile-dj-manager' ),
			'error'
		);
	}
	if ( isset( $_GET['mdjm-message'] ) && 'comm_missing_content' === $_GET['mdjm-message'] ) {
		add_settings_error(
			'mdjm-notices',
			'mdjm-comm_content-missing',
			__( 'Not all required fields have been completed.', 'mobile-dj-manager' ),
			'error'
		);
	}
	if ( isset( $_GET['mdjm-message'] ) && 'comm_sent' === $_GET['mdjm-message'] ) {
		add_settings_error(
			'mdjm-notices',
			'mdjm-comm_sent',
			__( 'Email sent successfully.', 'mobile-dj-manager' ),
			'updated'
		);
	}
	if ( isset( $_GET['mdjm-message'] ) && 'comm_not_sent' === $_GET['mdjm-message'] ) {
		add_settings_error(
			'mdjm-notices',
			'mdjm-comm_not_sent',
			__( 'Email not sent.', 'mobile-dj-manager' ),
			'error'
		);
	}
	if ( isset( $_GET['mdjm-action'] ) && 'get_event_availability' === $_GET['mdjm-action'] ) {

		if ( ! isset( $_GET['mdjm_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_GET['mdjm_nonce'], 'get_event_availability' ) ) ) ) {
			return;
		} elseif ( ! isset( $_GET['event_id'] ) ) {
			return;
		} else {

			$date = get_post_meta( absint( wp_unslash( $_GET['event_id'] ) ), '_mdjm_event_date', true );

			$result = mdjm_do_availability_check( $date );

			if ( ! empty( $result['available'] ) ) {

				echo '<ul>';

				foreach ( $result['available'] as $employee_id ) {
					echo '<li>';
						printf(
							/* translators: %2 employee %s plural */
							__( '<a href="%1$s" title="Assign &amp; Respond to Enquiry">Assign %2$s &amp; respond to enquiry</a>', 'mobile-dj-manager' ), // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
							add_query_arg( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
								'primary_employee',
								$employee_id,
								get_edit_post_link( absint( wp_unslash( $_GET['event_id'] ) ) )
							),
							mdjm_get_employee_display_name( $employee_id ) // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
						);
					echo '</li>';
				}

				echo '</ul>';

				echo '<div class="notice notice-info is-dismissible">';
					echo '<p>';
					printf(
						/* translators: %1: number of employees $s plural %2: contract ID %3: date $d %4 */
						esc_html__( 'You have %1$d employees available to work %2$s %3$s on %4$s.', 'mobile-dj-manager' ),
						count( $result['available'] ),
						esc_html( mdjm_get_label_singular( true ) ),
						esc_html( mdjm_get_event_contract_id( absint( wp_unslash( $_GET['event_id'] ) ) ) ),
						esc_html( mdjm_get_event_long_date( absint( wp_unslash( $_GET['event_id'] ) ) ) )
					);
					echo '</p>';
				echo '</div>';

			} else {

				echo '<div class="notice notice-error is-dismissible">';
					echo '<p>';
					printf(
						/* translators: %1 event type %2 contract ID %3 long date $s plural */
						esc_html__( 'There are no employees available to work %1$s %2$s on %3$s', 'mobile-dj-manager' ),
						esc_html( mdjm_get_label_singular( true ) ),
						esc_html( mdjm_get_event_contract_id( absint( wp_unslash( $_GET['event_id'] ) ) ) ),
						esc_html( mdjm_get_event_long_date( absint( wp_unslash( $_GET['event_id'] ) ) ) )
					);
					echo '</p>';
				echo '</div>';

			}
		}
	}
	if ( isset( $_GET['mdjm-message'] ) && 'payment_event_missing' === $_GET['mdjm-message'] ) {
		add_settings_error(
			'mdjm-notices',
			'mdjm-payment_event_missing',
			__( 'Event not identified.', 'mobile-dj-manager' ),
			'error'
		);
	}
	if ( isset( $_GET['mdjm-message'] ) && 'pay_employee_failed' === $_GET['mdjm-message'] ) {
		add_settings_error(
			'mdjm-notices',
			'mdjm-payment_employee_failed',
			__( 'Unable to make payment to employee.', 'mobile-dj-manager' ),
			'error'
		);
	}
	if ( isset( $_GET['mdjm-message'] ) && 'pay_all_employees_failed' === $_GET['mdjm-message'] ) {
		add_settings_error(
			'mdjm-notices',
			'mdjm-payment_employees_failed',
			__( 'Unable to make payment to employees.', 'mobile-dj-manager' ),
			'error'
		);
	}
	if ( isset( $_GET['mdjm-message'] ) && 'pay_all_employees_some_success' === $_GET['mdjm-message'] ) {
		add_settings_error(
			'mdjm-notices',
			'mdjm-payment_all_employees_some_success',
			__( 'Not all employees could be paid.', 'mobile-dj-manager' ),
			'notice-info'
		);
	}
	if ( isset( $_GET['mdjm-message'] ) && 'pay_employee_success' === $_GET['mdjm-message'] ) {
		add_settings_error(
			'mdjm-notices',
			'mdjm-payment_employeee_success',
			__( 'Employee successfully paid.', 'mobile-dj-manager' ),
			'updated'
		);
	}
	if ( isset( $_GET['mdjm-message'] ) && 'pay_all_employees_success' === $_GET['mdjm-message'] ) {
		add_settings_error(
			'mdjm-notices',
			'mdjm-payment_all_employeees_success',
			__( 'Employees successfully paid.', 'mobile-dj-manager' ),
			'updated'
		);
	}
	if ( isset( $_GET['mdjm-message'] ) && 'unattended_enquiries_rejected_success' === $_GET['mdjm-message'] ) {
		add_settings_error(
			'mdjm-notices',
			'mdjm-unattended_enquiries_rejected_success',
			sprintf(
				/* translators: %1 event type %2 contract id %3 event id $s plural */
				_n( '%1$s %2$s successfully rejected.', '%1$s %3$s successfully rejected.', isset( $_GET['mdjm-count'] ) ? absint( wp_unslash( $_GET['mdjm-count'] ) ) : 0, 'mobile-dj-manager' ),
				isset( $_GET['mdjm-count'] ) ? absint( wp_unslash( $_GET['mdjm-count'] ) ) : 0,
				esc_html( mdjm_get_label_singular() ),
				esc_html( mdjm_get_label_plural() )
			),
			'updated'
		);
	}
	if ( isset( $_GET['mdjm-message'] ) && 'unattended_enquiries_rejected_failed' === $_GET['mdjm-message'] ) {
		add_settings_error(
			'mdjm-notices',
			'mdjm-unattended_enquiries_rejected_failed',
			__( 'Errors were encountered.', 'mobile-dj-manager' ),
			'error'
		);
	}
	if ( isset( $_GET['mdjm-message'] ) && 'api-key-generated' === $_GET['mdjm-message'] ) {
		add_settings_error(
			'mdjm-notices',
			'mdjm-api-key-generated',
			__( 'API keys generated.', 'mobile-dj-manager' ),
			'updated'
		);
	}
	if ( isset( $_GET['mdjm-message'] ) && 'api-key-regenerated' === $_GET['mdjm-message'] ) {
		add_settings_error(
			'mdjm-notices',
			'mdjm-api-key-regenerated',
			__( 'API keys re-generated.', 'mobile-dj-manager' ),
			'updated'
		);
	}
	if ( isset( $_GET['mdjm-message'] ) && 'api-key-revoked' === $_GET['mdjm-message'] ) {
		add_settings_error(
			'mdjm-notices',
			'mdjm-api-key-revoked',
			__( 'API keys revoked.', 'mobile-dj-manager' ),
			'updated'
		);
	}
	if ( isset( $_GET['mdjm-message'] ) && 'api-key-failed' === $_GET['mdjm-message'] ) {
		add_settings_error(
			'mdjm-notices',
			'mdjm-api-key-failed',
			__( 'Generating API keys failed.', 'mobile-dj-manager' ),
			'error'
		);
	}
	if ( isset( $_GET['mdjm-message'] ) && 'task-status-updated' === $_GET['mdjm-message'] ) {
		add_settings_error(
			'mdjm-notices',
			'mdjm-task-status-updated',
			__( 'Task status updated.', 'mobile-dj-manager' ),
			'updated'
		);
	}
	if ( isset( $_GET['mdjm-message'] ) && 'task-status-update-failed' === $_GET['mdjm-message'] ) {
		add_settings_error(
			'mdjm-notices',
			'mdjm-task-status-update-failed',
			__( 'Task status could not be updated.', 'mobile-dj-manager' ),
			'error'
		);
	}
	if ( isset( $_GET['mdjm-message'] ) && 'task-run' === $_GET['mdjm-message'] ) {
		add_settings_error(
			'mdjm-notices',
			'mdjm-task-run',
			__( 'Task executed successfully.', 'mobile-dj-manager' ),
			'updated'
		);
	}
	if ( isset( $_GET['mdjm-message'] ) && 'task-run-failed' === $_GET['mdjm-message'] ) {
		add_settings_error(
			'mdjm-notices',
			'mdjm-run-failed',
			__( 'Task could not be executed.', 'mobile-dj-manager' ),
			'error'
		);
	}
	if ( isset( $_GET['mdjm-message'] ) && 'task-updated' === $_GET['mdjm-message'] ) {
		add_settings_error(
			'mdjm-notices',
			'mdjm-task-updated',
			__( 'Task updated.', 'mobile-dj-manager' ),
			'updated'
		);
	}
	if ( isset( $_GET['mdjm-message'] ) && 'task-update-failed' === $_GET['mdjm-message'] ) {
		add_settings_error(
			'mdjm-notices',
			'mdjm-update-failed',
			__( 'Task update failed.', 'mobile-dj-manager' ),
			'error'
		);
	}
	if ( isset( $_GET['mdjm-message'] ) && 'settings-imported' === $_GET['mdjm-message'] ) {
		add_settings_error(
			'mdjm-notices',
			'mdjm-settings-imported',
			__( 'Settings sucessfully imported.', 'mobile-dj-manager' ),
			'updated'
		);
	}

	settings_errors( 'mdjm-notices' );

} // mdjm_admin_messages
add_action( 'admin_notices', 'mdjm_admin_notices' );

/**
 * Admin WP Rating Request Notice
 *
 * @since   1.5
 * @return  void
 */
function mdjm_admin_wp_5star_rating_notice() {
	ob_start(); ?>

	<div class="updated notice notice-mdjm-dismiss is-dismissible" data-notice="mdjm_request_wp_5star_rating">
		<p>
			<strong><?php esc_html_e( 'Thank you for using Mobile DJ Manager!', 'mobile-dj-manager' ); ?></strong>
		</p>
		<p>
			<?php
			printf(
				/* translators: $1 WordPress link  $s plural */
				__( 'Would you <strong>please</strong> do us a favour and leave a 5 star rating on WordPress.org? It only takes a minute and it <strong>really helps</strong> our developers to continue to work on great new features and functionality. <a href="%1$s" target="_blank">Yes, most definitely!</a>', 'mobile-dj-manager' ), // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				'https://wordpress.org/support/plugin/mobile-dj-manager/reviews/'
			);
			?>
		</p>
			<p>
			<?php
			printf(
				__( 'If you haven&#39;t already, please check out our <a href="https://www.mdjm.co.uk/Add-Ons">Add Ons</a> to further enhance Mobile DJ Manager.', 'mobile-dj-manager' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			?>
		</p>
			<p>
			<?php
			printf(
				__( 'You can also join our Mobile DJ Manager User Group on <a href="https://www.facebook.com/groups/mdjmusers">Facebook</a>. Don&#39;t forget to Like our <a href="https://www.facebook.com/mdjmplugin">Facebook Page</a> too.', 'mobile-dj-manager' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped 
			?>
		</p>
	</div>

	<?php
	echo ob_get_clean(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
} // mdjm_admin_wp_5star_rating_notice

/**
 * Request 5 star rating after 1 event has been completed.
 *
 * After 1 completed event we ask the admin for a 5 star rating on WordPress.org
 *
 * @since   1.3
 * @return  void
 */
function mdjm_request_wp_5star_rating() {

	global $typenow, $pagenow;

	$allowed_types = array(
		'mdjm-event',
		'mdjm-package',
		'mdjm-addon',
		'mdjm_communication',
		'contract',
		'email_template',
		'mdjm-playlist',
		'mdjm-transaction',
		'mdjm-venue',
	);
	$allowed_pages = array( 'edit.php', 'post.php', 'post-new.php', 'index.php', 'plugins.php' );

	if ( ! current_user_can( 'administrator' ) ) {
		return;
	}

	if ( ! in_array( $typenow, $allowed_types ) && ! in_array( $pagenow, $allowed_pages ) ) {
		return;
	}

	if ( mdjm_is_notice_dismissed( 'mdjm_request_wp_5star_rating' ) ) {
		return;
	}

	global $wpdb;

	$completed_events = $wpdb->get_var(
		$wpdb->prepare(
			"
			SELECT COUNT(*)
			FROM $wpdb->posts
			WHERE `post_type` = %s
			AND `post_status` = %s
		",
			'mdjm-event',
			'mdjm-approved'
		)
	);

	if ( $completed_events >= 1 ) {
		add_action( 'admin_notices', 'mdjm_admin_wp_5star_rating_notice' );
	}

} // mdjm_request_wp_5star_rating
add_action( 'plugins_loaded', 'mdjm_request_wp_5star_rating' );


/**
 * Implements mdjm_admin_wp_addons().
 *
 * Description Invites user to look at additional addons.
 */
function mdjm_admin_wp_addons() {
	ob_start();
	?>

	<div class="updated notice notice-mdjm-dismiss is-dismissible" data-notice="mdjm_request_wp_addons">
		<p>
			<strong><?php esc_html_e( 'Thank you for installing Mobile DJ Manager!', 'mobile-dj-manager' ); ?></strong>
		</p>
	</div>

	<?php
	echo ob_get_clean(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
} // mdjm_admin_wp_addons

/**
 * Link to visit Addons pages.
 *
 * @since   1.3
 * @return  void
 */
function mdjm_request_wp_addons() {

	global $typenow, $pagenow;

	$allowed_types = array(
		'mdjm-event',
		'mdjm-package',
		'mdjm-addon',
		'mdjm_communication',
		'contract',
		'email_template',
		'mdjm-playlist',
		'mdjm-transaction',
		'mdjm-venue',
	);
	$allowed_pages = array( 'edit.php', 'post.php', 'post-new.php', 'index.php', 'plugins.php' );

	if ( ! current_user_can( 'administrator' ) ) {
		return;
	}

	if ( ! in_array( $typenow, $allowed_types ) && ! in_array( $pagenow, $allowed_pages ) ) {
		return;
	}

	if ( mdjm_is_notice_dismissed( 'mdjm_request_wp_addons' ) ) {
		return;
	}

	global $wpdb;

	$addon_events = $wpdb->get_var(
		$wpdb->prepare(
			"
			SELECT COUNT(*)
			FROM $wpdb->posts
			WHERE `post_type` = %s
			AND `post_status` = %s
		",
			'mdjm-event',
			'mdjm-enquiry'
		)
	);

	if ( $addon_events >= 0 ) {
		add_action( 'admin_notices', 'mdjm_admin_wp_addons' );
	}

} // mdjm_request_wp_addons
add_action( 'plugins_loaded', 'mdjm_request_wp_addons' );
