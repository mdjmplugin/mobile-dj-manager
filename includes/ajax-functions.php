<?php


/**
 * AJAX Functions
 *
 * Process the AJAX actions. Frontend and backend
 *
 * @package     MDJM
 * @subpackage  Functions/AJAX
 * @since       1.3
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Get AJAX URL
 *
 * @since   1.2.1
 * @return  str
 */
function mdjm_get_ajax_url() {
	$scheme = defined( 'FORCE_SSL_ADMIN' ) && FORCE_SSL_ADMIN ? 'https' : 'admin';

	$current_url = mdjm_get_current_page_url();
	$ajax_url    = admin_url( 'admin-ajax.php', $scheme );

	if ( preg_match( '/^https/', $current_url ) && ! preg_match( '/^https/', $ajax_url ) ) {
		$ajax_url = preg_replace( '/^http/', 'https', $ajax_url );
	}

	return apply_filters( 'mdjm_ajax_url', $ajax_url );
} // mdjm_get_ajax_url

/**
 * Dismiss admin notices.
 *
 * @since   1.5
 * @return  void
 */
function mdjm_ajax_dismiss_admin_notice() {

	$notice = isset( $_POST['notice'] ) ? sanitize_text_field( wp_unslash( $_POST['notice'] ) ) : null;
	mdjm_dismiss_notice( $notice );

	wp_send_json_success();

} // mdjm_ajax_dismiss_admin_notice
add_action( 'wp_ajax_mdjm_dismiss_notice', 'mdjm_ajax_dismiss_admin_notice' );

/**
 * Retrieve employee availability data for the calendar view.
 *
 * @since   1.5.6
 * @return  void
 */
function mdjm_calendar_activity_ajax() {
	$start = ! empty( $_POST['start'] ) ? sanitize_text_field( wp_unslash( $_POST['start'] ) ) : null;
	$end   = ! empty( $_POST['end'] ) ? sanitize_text_field( wp_unslash( $_POST['end'] ) ) : null;

	if ( empty( $start ) ) {
		wp_send_json_error( 'Error: Invalid data!' );
		exit;
	}

	$activity = mdjm_get_calendar_entries( $start, $end );

	wp_send_json( $activity );
} // mdjm_calendar_activity_ajax
add_action( 'wp_ajax_mdjm_calendar_activity', 'mdjm_calendar_activity_ajax' );

/**
 * Adds an employee absence entry.
 *
 * @since   1.5.6
 * @return  void
 */
function mdjm_add_employee_absence_ajax() {
	$employee_id = ! empty( $_POST['employee_id'] ) ? absint( wp_unslash( $_POST['employee_id'] ) ) : 0;
	$start_date  = ! empty( $_POST['start_date'] ) ? sanitize_text_field( wp_unslash( $_POST['start_date'] ) ) : '';
	$end_date    = ! empty( $_POST['end_date'] ) ? sanitize_text_field( wp_unslash( $_POST['end_date'] ) ) : '';
	$all_day     = ! empty( $_POST['all_day'] ) ? sanitize_text_field( wp_unslash( $_POST['all_day'] ) ) : '';
	$start_time  = ! empty( $_POST['start_time_hr'] ) && ! empty( $_POST['start_time_min'] ) ? sanitize_text_field( wp_unslash( $_POST['start_time_hr'] . ':' . $_POST['start_time_min'] ) ) : '';
	$end_time    = ! empty( $_POST['end_time_hr'] ) && ! empty( $_POST['end_time_min'] ) ? sanitize_text_field( wp_unslash( $_POST['end_time_hr'] . ':' . $_POST['end_time_min'] ) ) : '';
	$start_time .= ! empty( $_POST['start_time_period'] ) ? sanitize_text_field( wp_unslash( $_POST['start_time_period'] ) ) : '';
	$end_time   .= ! empty( $_POST['end_time_period'] ) ? sanitize_text_field( wp_unslash( $_POST['end_time_period'] ) ) : '';
	$notes       = ! empty( $_POST['notes'] ) ? sanitize_textarea_field( wp_unslash( $_POST['notes'] ) ) : '';

	$data = array(
		'employee_id' => $employee_id,
		'all_day'     => $all_day,
		'start'       => $start_date,
		'start_time'  => $start_time,
		'end'         => $end_date,
		'end_time'    => $end_time,
		'notes'       => $notes,
	);

	if ( empty( $employee_id ) || empty( $start_date ) || empty( $end_date ) ) {
		wp_send_json_error( 'Error: Invalid data!' );
		exit;
	}

	if ( mdjm_add_employee_absence( $employee_id, $data ) ) {
		$success = true;
	} else {
		$success = false;
	}

	wp_send_json(
		array(
			'success' => $success,
			'date'    => $start_date,
		)
	);

} // mdjm_add_employee_absence_ajax
add_action( 'wp_ajax_mdjm_add_employee_absence', 'mdjm_add_employee_absence_ajax' );

/**
 * Removes an employee absence entry.
 *
 * @since   1.5.6
 * return   void
 */
function mdjm_delete_employee_absence_ajax() {
	$id = isset( $_POST['id'] ) ? $_POST['id'] : null;

	if ( empty( $id ) ) {
		wp_send_json_error( 'Error: Invalid data!' );
		exit;
	}

	$deleted = mdjm_remove_employee_absence( $id );

	if ( $deleted > 0 ) {
		wp_send_json_success();
	}

	wp_send_json_error();
} // mdjm_delete_employee_absence_ajax
add_action( 'wp_ajax_mdjm_delete_employee_absence', 'mdjm_delete_employee_absence_ajax' );

/**
 * Client profile update form validation
 *
 * @since   1.5
 * @return  void
 */
function mdjm_validate_client_profile_form_ajax() {

	if ( ! check_ajax_referer( 'update_client_profile', 'mdjm_nonce', false ) ) {
		wp_send_json(
			array(
				'error' => __( 'An error occured', 'mobile-dj-manager' ),
				'field' => 'mdjm_nonce',
			)
		);
	}

	$client_id    = isset( $_POST['mdjm_client_id'] ) ? absint( $_POST['mdjm_client_id'] ) : null;
	$new_password = ! empty( $_POST['mdjm_new_password'] ) ? sanitize_text_field( wp_unslash( $_POST['mdjm_new_password'] ) ) : false;
	$core_fields  = array( 'first_name', 'last_name', 'user_email' );
	$update_args  = array( 'ID' => $client_id );
	$update_meta  = array();
	$display_name = '';

	if ( empty( $client_id ) ) {
		wp_send_json_error( 'Error: Invalid data!' );
		exit;
	}

	$client = new MDJM_Client( $client_id );
	$fields = $client->get_profile_fields();

	foreach ( $fields as $field ) {
		if ( ! empty( $field['required'] ) && empty( $_POST[ $field['id'] ] ) ) {
			wp_send_json(
				array(
					'error' => sprintf( __( '%s is a required field', 'mobile-dj-manager' ), esc_attr( $field['label'] ) ),
					'field' => $field['id'],
				)
			);
		}

		switch ( $field['id'] ) {
			case 'user_email':
				if ( ! is_email( sanitize_text_field( wp_unslash( $_POST[ $field['id'] ] ) ) ) ) {
					wp_send_json(
						array(
							'error' => sprintf( __( '%s is not a valid email address', 'mobile-dj-manager' ), esc_attr( sanitize_text_field( wp_unslash( $_POST[ $field['id'] ] ) ) ) ),
							'field' => $field['id'],
						)
					);
				}
		}

		switch ( $field['type'] ) {
			case 'text':
			case 'dropdown':
			default:
				$value = sanitize_text_field( wp_unslash( $_POST[ $field['id'] ] ) );
				if ( 'first_name' == $field['id'] || 'last_name' == $field['id'] ) {
					$value = ucfirst( trim( $value ) );

					if ( 'first_name' == $field['id'] ) {
						$display_name = ! empty( $display_name ) ? $value . ' ' . $display_name : $value;
					}

					if ( 'last_name' == $field['id'] ) {
						$display_name = ! empty( $display_name ) ? $display_name . ' ' . $value : $value;
					}
				}
				break;

			case 'checkbox':
				$value = ! empty( $_POST[ $field['id'] ] ) ? sanitize_text_field( wp_unslash( $_POST[ $field['id'] ] ) ) : 0;
				break;
		}

		if ( in_array( $field['id'], $core_fields ) ) {
			$update_args[ $field['id'] ] = $value;
		} else {
			$update_meta[ $field['id'] ] = $value;
		}
	}

	if ( $new_password ) {
		if ( empty( $_POST['mdjm_confirm_password'] ) || $_POST['mdjm_confirm_password'] != $new_password ) {
			wp_send_json(
				array(
					'error' => __( 'Passwords do not match', 'mobile-dj-manager' ),
					'field' => 'mdjm_confirm_password',
				)
			);
		}

		$update_args['user_pass'] = $new_password;
		$new_password             = true;
	}

	foreach ( $update_meta as $meta_key => $meta_value ) {
		update_user_meta( $client->ID, $meta_key, $meta_value );
	}

	$user_id = wp_update_user( $update_args );

	if ( is_wp_error( $user_id ) ) {
		wp_send_json(
			array(
				'error' => __( 'An error occured', 'mobile-dj-manager' ),
				'field' => 'mdjm_nonce',
			)
		);
	}

	if ( $new_password ) {
		wp_clear_auth_cookie();
		wp_logout();
	}
	wp_send_json( array( 'password' => $new_password ) );

} // mdjm_validate_client_profile_form_ajax
add_action( 'wp_ajax_mdjm_validate_client_profile', 'mdjm_validate_client_profile_form_ajax' );
add_action( 'wp_ajax_nopriv_mdjm_validate_client_profile', 'mdjm_validate_client_profile_form_ajax' );

/**
 * Process playlist submission.
 *
 * @since   1.5
 * @return  void
 */
function mdjm_submit_playlist_ajax() {

	if ( ! check_ajax_referer( 'add_playlist_entry', 'mdjm_nonce', false ) ) {
		wp_send_json(
			array(
				'error' => __( 'An error occured', 'mobile-dj-manager' ),
				'field' => 'mdjm_nonce',
			)
		);
	}

	$required_fields = array(
		'mdjm_song' => __( 'Song', 'mobile-dj-manager' ),
	);

	$required_fields = apply_filters( 'mdjm_playlist_required_fields', $required_fields );

	foreach ( $required_fields as $required_field => $field_name ) {
		if ( empty( $_POST[ $required_field ] ) ) {
			wp_send_json(
				array(
					'error' => sprintf( __( '%s is a required field', 'mobile-dj-manager' ), esc_attr( $field_name ) ),
					'field' => $required_field,
				)
			);
		}
	}

	$event    = isset( $_POST['mdjm_playlist_event'] ) ? absint( $_POST['mdjm_playlist_event'] ) : null;
	$song     = isset( $_POST['mdjm_song'] ) ? sanitize_text_field( wp_unslash( $_POST['mdjm_song'] ) ) : '';
	$artist   = isset( $_POST['mdjm_artist'] ) ? sanitize_text_field( wp_unslash( $_POST['mdjm_artist'] ) ) : '';
	$category = isset( $_POST['mdjm_category'] ) ? absint( $_POST['mdjm_category'] ) : null;
	$notes    = isset( $_POST['mdjm_notes'] ) ? sanitize_textarea_field( wp_unslash( $_POST['mdjm_notes'] ) ) : null;

	$playlist_data = array(
		'event_id' => $event,
		'song'     => $song,
		'artist'   => $artist,
		'category' => $category,
		'added_by' => get_current_user_id(),
		'notes'    => $notes,
	);

	$entry_id = mdjm_store_playlist_entry( $playlist_data );

	if ( $entry_id ) {
		$category   = get_term( $category, 'playlist-category' );
		$entry_data = mdjm_get_playlist_entry_data( $entry_id );

		ob_start(); ?>

		<div class="playlist-entry-row mdjm-playlist-entry-<?php echo esc_attr( $entry_id ); ?>">
			<div class="playlist-entry-column">
				<span class="playlist-entry"><?php echo esc_attr( $entry_data['artist'] ); ?></span>
			</div>
			<div class="playlist-entry-column">
				<span class="playlist-entry"><?php echo esc_attr( $entry_data['song'] ); ?></span>
			</div>
			<div class="playlist-entry-column">
				<span class="playlist-entry"><?php echo esc_attr( $category->name ); ?></span>
			</div>
			<div class="playlist-entry-column">
				<span class="playlist-entry">
					<?php if ( 'Guest' == $category->name ) : ?>
						<?php echo esc_attr( $entry_data['added_by'] ); ?>
					<?php elseif ( ! empty( $entry_data['djnotes'] ) ) : ?>
						<?php echo esc_attr( $entry_data['djnotes'] ); ?>
					<?php else : ?>
						<?php echo '&ndash;'; ?>
					<?php endif; ?>
				</span>
			</div>
			<div class="playlist-entry-column">
				<span class="playlist-entry">
					<a class="mdjm-delete playlist-delete-entry" data-event="<?php echo esc_attr( $event ); ?>" data-entry="<?php echo esc_attr( $entry_id ); ?>"><?php esc_html_e( 'Remove', 'mobile-dj-manager' ); ?></a>
				</span>
			</div>
		</div>

		<?php
		$row_data = ob_get_clean();

		$mdjm_event     = new MDJM_Event( $event );
		$playlist_limit = mdjm_get_event_playlist_limit( $mdjm_event->ID );
		$total_entries  = mdjm_count_playlist_entries( $mdjm_event->ID );
		$songs          = sprintf( '%d %s', $total_entries, _n( 'song', 'songs', $total_entries, 'mobile-dj-manager' ) );
		$length         = mdjm_playlist_duration( $mdjm_event->ID, $total_entries );

		if ( ! $mdjm_event->playlist_is_open() ) {
			$closed = true;
		} elseif ( $playlist_limit != 0 && $total_entries >= $playlist_limit ) {
			$closed = true;
		} else {
			$closed = false;
		}

		wp_send_json_success(
			array(
				'row_data' => $row_data,
				'closed'   => $closed,
				'songs'    => $songs,
				'length'   => $length,
				'total'    => $total_entries,
			)
		);
	}

	wp_send_json_error();
} // mdjm_submit_playlist_ajax
add_action( 'wp_ajax_mdjm_submit_playlist', 'mdjm_submit_playlist_ajax' );
add_action( 'wp_ajax_nopriv_mdjm_submit_playlist', 'mdjm_submit_playlist_ajax' );

/**
 * Remove playlist entry.
 *
 * @since   1.5
 * @return  void
 */
function mdjm_remove_playlist_entry_ajax() {
	$event_id = isset( $_POST['event_id'] ) ? absint( $_POST['event_id'] ) : 0;
	$song_id  = isset( $_POST['song_id'] ) ? absint( $_POST['song_id'] ) : 0;

	if ( empty( $event_id ) || empty( $song_id ) ) {
		wp_send_json_error( 'Error: Invalid data!' );
		exit;
	}

	if ( mdjm_remove_stored_playlist_entry( $song_id ) ) {
		$total  = mdjm_count_playlist_entries( $event_id );
		$songs  = sprintf( '%d %s', $total, _n( 'song', 'songs', $total, 'mobile-dj-manager' ) );
		$length = mdjm_playlist_duration( $event_id, $total );
		wp_send_json_success(
			array(
				'count'  => $total,
				'songs'  => $songs,
				'length' => $length,
			)
		);
	}

	wp_send_json_error();
} // mdjm_remove_playlist_entry_ajax
add_action( 'wp_ajax_mdjm_remove_playlist_entry', 'mdjm_remove_playlist_entry_ajax' );
add_action( 'wp_ajax_nopriv_mdjm_remove_playlist_entry', 'mdjm_remove_playlist_entry_ajax' );

/**
 * Process guest playlist submission.
 *
 * @since   1.5
 * @return  void
 */
function mdjm_submit_guest_playlist_ajax() {

	if ( ! check_ajax_referer( 'add_guest_playlist_entry', 'mdjm_nonce', false ) ) {
		wp_send_json(
			array(
				'error' => esc_html__( 'An error occured', 'mobile-dj-manager' ),
				'field' => 'mdjm_nonce',
			)
		);
	}

	$required_fields = array(
		'mdjm_guest_name' => esc_html__( 'Name', 'mobile-dj-manager' ),
		'mdjm_guest_song' => esc_html__( 'Song', 'mobile-dj-manager' ),
	);

	$required_fields = apply_filters( 'mdjm_guest_playlist_required_fields', $required_fields );

	foreach ( $required_fields as $required_field => $field_name ) {
		if ( empty( $_POST[ $required_field ] ) ) {
			wp_send_json(
				array(
					'error' => sprintf( __( '%s is a required field', 'mobile-dj-manager' ), esc_attr( $field_name ) ),
					'field' => $required_field,
				)
			);
		}
	}

	$song   = isset( $_POST['mdjm_guest_song'] ) ? sanitize_text_field( wp_unslash( $_POST['mdjm_guest_song'] ) ) : '';
	$artist = isset( $_POST['mdjm_guest_artist'] ) ? sanitize_text_field( wp_unslash( $_POST['mdjm_guest_artist'] ) ) : '';
	$guest  = isset( $_POST['mdjm_guest_name'] ) ? ucwords( sanitize_text_field( wp_unslash( $_POST['mdjm_guest_name'] ) ) ) : '';
	$event  = isset( $_POST['mdjm_playlist_event'] ) ? absint( $_POST['mdjm_playlist_event'] ) : 0;
	$closed = false;

	$playlist_data = array(
		'mdjm_guest_song'     => $song,
		'mdjm_guest_artist'   => $artist,
		'mdjm_guest_name'     => $guest,
		'mdjm_playlist_event' => $event,
	);

	$entry_id = mdjm_store_guest_playlist_entry( $playlist_data );

	if ( $entry_id ) {
		ob_start();
		?>
		<div class="guest-playlist-entry-row mdjm-playlist-entry-<?php echo esc_attr( $entry_id ); ?>">
			<div class="guest-playlist-entry-column">
				<span class="guest-playlist-entry"><?php echo esc_html( wp_unslash( $artist ) ); ?></span>
			</div>
			<div class="guest-playlist-entry-column">
				<span class="guest-playlist-entry"><?php echo esc_html( wp_unslash( $song ) ); ?></span>
			</div>
			<div class="guest-playlist-entry-column">
				<span class="playlist-entry">
					<a class="mdjm-delete guest-playlist-delete-entry" data-event="<?php echo esc_attr( $event ); ?>" data-entry="<?php echo esc_attr( $entry_id ); ?>"><?php esc_html_e( 'Remove', 'mobile-dj-manager' ); ?></a>
				</span>
			</div>
		</div>
		<?php
		$entry = ob_get_clean();

		$event_playlist_limit = mdjm_get_event_playlist_limit( $event );
		$entries_in_playlist  = mdjm_count_playlist_entries( $event );

		if ( $event_playlist_limit != 0 && $entries_in_playlist >= $event_playlist_limit ) {
			$closed = true;
		}

		wp_send_json(
			array(
				'entry'  => $entry,
				'closed' => $closed,
			)
		);
	}

} // mdjm_submit_guest_playlist_ajax
add_action( 'wp_ajax_mdjm_submit_guest_playlist', 'mdjm_submit_guest_playlist_ajax' );
add_action( 'wp_ajax_nopriv_mdjm_submit_guest_playlist', 'mdjm_submit_guest_playlist_ajax' );

/**
 * Remove guest playlist entry.
 *
 * @since   1.5
 * @return  void
 */
function mdjm_remove_guest_playlist_entry_ajax() {
	$event_id = isset( $_POST['event_id'] ) ? absint( $_POST['event_id'] ) : 0;
	$song_id  = isset( $_POST['song_id'] ) ? absint( $_POST['song_id'] ) : 0;

	if ( empty( $event_id ) || empty( $song_id ) ) {
		wp_send_json_error( 'Error: Invalid data!' );
		exit;
	}

	if ( mdjm_remove_stored_playlist_entry( $song_id ) ) {
		wp_send_json_success();
	}

	wp_send_json_error();
} // mdjm_remove_guest_playlist_entry_ajax
add_action( 'wp_ajax_mdjm_remove_guest_playlist_entry', 'mdjm_remove_guest_playlist_entry_ajax' );
add_action( 'wp_ajax_nopriv_mdjm_remove_guest_playlist_entry', 'mdjm_remove_guest_playlist_entry_ajax' );

/**
 * Save the client fields order during drag and drop.
 */
function mdjm_save_client_field_order_ajax() {
	$client_fields = get_option( 'mdjm_client_fields' );

	if ( isset( $_POST['fields'] ) ) {
		foreach ( wp_unslash( $_POST['fields'] ) as $order => $field ) { // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
			$i = $order + 1;

			$client_fields[ $field ]['position'] = $i;

		}
		update_option( 'mdjm_client_fields', $client_fields );
	}

	exit;
} // mdjm_save_client_field_order_ajax
add_action( 'wp_ajax_mdjm_update_client_field_order', 'mdjm_save_client_field_order_ajax' );

/**
 * Refresh the data within the client details table.
 *
 * @since   1.2.1.7
 */
function mdjm_refresh_client_details_ajax() {

	$result = array();

	$client_id = isset( $_POST['client_id'] ) ? sanitize_text_field( wp_unslash( $_POST['client_id'] ) ) : 0;
	$event_id  = isset( $_POST['event_id'] ) ? sanitize_text_field( wp_unslash( $_POST['event_id'] ) ) : 0;

	ob_start();
	mdjm_do_client_details_table( $client_id, $event_id );
	$result['client_details'] = ob_get_contents();
	ob_get_clean();

	echo json_encode( $result );

	exit;

} // mdjm_refresh_client_details_ajax
add_action( 'wp_ajax_mdjm_refresh_client_details', 'mdjm_refresh_client_details_ajax' );

/**
 * Adds a new client from the event field.
 *
 * @since   1.2.1.7
 * @global  arr     $_POST
 */
function mdjm_add_client_ajax() {

	$client_id   = false;
	$client_list = '';
	$result      = array();
	$message     = array();

	$client_email = isset( $_POST['client_email'] ) ? sanitize_email( wp_unslash( $_POST['client_email'] ) ) : '';

	if ( ! is_email( $client_email ) ) {
		$message = __( 'Email address is invalid', 'mobile-dj-manager' );
	} elseif ( email_exists( $client_email ) ) {
		$message = __( 'Email address is already in use', 'mobile-dj-manager' );
	} else {

		$user_data = array(
			'first_name'      => ! empty( $_POST['client_firstname'] ) ? ucwords( sanitize_text_field( wp_unslash( $_POST['client_firstname'] ) ) ) : '',
			'last_name'       => ! empty( $_POST['client_lastname'] ) ? ucwords( sanitize_text_field( wp_unslash( $_POST['client_lastname'] ) ) ) : '',
			'user_email'      => strtolower( $client_email ),
			'client_phone'    => ! empty( $_POST['client_phone'] ) ? sanitize_text_field( wp_unslash( $_POST['client_phone'] ) ) : '',
			'client_phone2'   => ! empty( $_POST['client_phone2'] ) ? sanitize_text_field( wp_unslash( $_POST['client_phone2'] ) ) : '',
			'client_address1' => ! empty( $_POST['client_address1'] ) ? sanitize_text_field( wp_unslash( $_POST['client_address1'] ) ) : '',
			'client_address2' => ! empty( $_POST['client_address2'] ) ? sanitize_text_field( wp_unslash( $_POST['client_address2'] ) ) : '',
			'client_town'     => ! empty( $_POST['client_town'] ) ? sanitize_text_field( wp_unslash( $_POST['client_town'] ) ) : '',
			'client_county'   => ! empty( $_POST['client_county'] ) ? sanitize_text_field( wp_unslash( $_POST['client_county'] ) ) : '',
			'client_postcode' => ! empty( $_POST['client_postcode'] ) ? sanitize_text_field( wp_unslash( $_POST['client_postcode'] ) ) : '',
		);

		$user_data = apply_filters( 'mdjm_event_new_client_data', $user_data );

		$client_id = mdjm_add_client( $user_data );

	}

	$clients = mdjm_get_clients( 'client' );

	if ( ! empty( $clients ) ) {
		foreach ( $clients as $client ) {
			$client_list .= sprintf(
				'<option value="%1$s"%2$s>%3$s</option>',
				$client->ID,
				$client->ID == $client_id ? ' selected="selected"' : '',
				$client->display_name
			);
		}
	}

	if ( empty( $client_id ) ) {
		$result = array(
			'type'    => 'error',
			'message' => explode( "\n", $message ),
		);
	} else {
		$result = array(
			'type'        => 'success',
			'client_id'   => $client_id,
			'client_list' => $client_list,
		);
		do_action( 'mdjm_after_add_new_client', $user_data );
	}

	echo json_encode( $result );

	exit;

} // mdjm_add_client_ajax
add_action( 'wp_ajax_mdjm_event_add_client', 'mdjm_add_client_ajax' );

/**
 * Refresh the data within the venue details table.
 *
 * @since   1.2.1.7
 */
function mdjm_refresh_venue_details_ajax() {

	$venue_id = isset( $_POST['venue_id'] ) ? absint( wp_unslash( $_POST['venue_id'] ) ) : 0;
	$event_id = isset( $_POST['event_id'] ) ? absint( wp_unslash( $_POST['event_id'] ) ) : 0;

	if ( empty( $venue_id ) || empty( $event_id ) ) {
		wp_send_json_error( 'Error: Invalid data!' );
		exit;
	}

	wp_send_json_success(
		array( 'venue' => mdjm_do_venue_details_table( $venue_id, $event_id ) )
	);

} // mdjm_refresh_venue_details_ajax
add_action( 'wp_ajax_mdjm_refresh_venue_details', 'mdjm_refresh_venue_details_ajax' );

/**
 * Sets the venue address as the client address.
 *
 * @since   1.4
 */
function mdjm_set_client_venue_ajax() {

	$client_id = isset( $_POST['client_id'] ) ? absint( wp_unslash( $_POST['client_id'] ) ) : 0;

	if ( empty( $client_id ) ) {
		wp_send_json_error( 'Error: Invalid data!' );
		exit;
	}

	$response = array();

	$client = get_userdata( $client_id );

	if ( $client ) {
		if ( ! empty( $client->address1 ) ) {
			$response['address1'] = wp_unslash( $client->address1 );
		}
		if ( ! empty( $client->address2 ) ) {
			$response['address2'] = wp_unslash( $client->address2 );
		}
		if ( ! empty( $client->town ) ) {
			$response['town'] = wp_unslash( $client->town );
		}
		if ( ! empty( $client->county ) ) {
			$response['county'] = wp_unslash( $client->county );
		}
		if ( ! empty( $client->postcode ) ) {
			$response['postcode'] = wp_unslash( $client->postcode );
		}
	}

	$response['type'] = 'success';

	wp_send_json( $response );

} // mdjm_use_client_address
add_action( 'wp_ajax_mdjm_set_client_venue', 'mdjm_set_client_venue_ajax' );

/**
 * Adds a new venue from the events screen.
 *
 * @since   1.2.1.7
 */
function mdjm_add_venue_ajax() {

	$venue_id   = false;
	$venue_list = '';
	$result     = array();
	$venue_name = '';
	$venue_meta = array();

	foreach ( $_POST as $key => $value ) {
		if ( $key == 'action' ) {
			continue;
		} elseif ( $key == 'venue_name' ) {
			$venue_name = $value;
		} else {
			$venue_meta[ $key ] = strip_tags( addslashes( $value ) );
		}
	}

	$venue_id = mdjm_add_venue( $venue_name, $venue_meta );

	$venues = mdjm_get_venues();

	$venue_list .= '<option value="manual">' . __( '  - Enter Manually - ', 'mobile-dj-manager' ) . '</option>' . "\r\n";
	$venue_list .= '<option value="client">' . __( '  - Use Client Address - ', 'mobile-dj-manager' ) . '</option>' . "\r\n";

	if ( ! empty( $venues ) ) {
		foreach ( $venues as $venue ) {
			$venue_list .= sprintf(
				'<option value="%1$s"%2$s>%3$s</option>',
				$venue->ID,
				$venue->ID == $venue_id ? ' selected="selected"' : '',
				$venue->post_title
			);
		}
	}

	if ( empty( $venue_id ) ) {
		$result = array(
			'type'    => 'error',
			'message' => __( 'Unable to add venue', 'mobile-dj-manager' ),
		);
	} else {
		$result = array(
			'type'       => 'success',
			'venue_id'   => $venue_id,
			'venue_list' => $venue_list,
		);

	}

	echo json_encode( $result );

	exit;

} // mdjm_add_venue_ajax
add_action( 'wp_ajax_mdjm_add_venue', 'mdjm_add_venue_ajax' );

/**
 * Refresh the travel data for an event.
 *
 * @since   1.4
 */
function mdjm_update_event_travel_data_ajax() {
	$employee_id = isset( $_POST['employee_id'] ) ? absint( wp_unslash( $_POST['employee_id'] ) ) : 0;
	$dest        = isset( $_POST['venue'] ) ? wp_unslash( $_POST['venue'] ) : ''; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
	$dest        = maybe_unserialize( $dest );

	if ( empty( $des ) ) {
		wp_send_json_error( 'Error: Invalid data!' );
		exit;
	}

	$mdjm_travel = new MDJM_Travel();

	if ( ! empty( $employee_id ) ) {
		$mdjm_travel->__set( 'start_address', $mdjm_travel->get_employee_address( $employee_id ) );
	}

	$mdjm_travel->set_destination( $dest );
	$mdjm_travel->get_travel_data();

	if ( ! empty( $mdjm_travel->data ) ) {
		$travel_cost = $mdjm_travel->get_cost();
		$response    = array(
			'type'           => 'success',
			'distance'       => mdjm_format_distance( $mdjm_travel->data['distance'], false, true ),
			'time'           => mdjm_seconds_to_time( $mdjm_travel->data['duration'] ),
			'cost'           => ! empty( $travel_cost ) ? mdjm_currency_filter( mdjm_format_amount( $travel_cost ) ) : mdjm_currency_filter( mdjm_format_amount( 0 ) ),
			'directions_url' => $mdjm_travel->get_directions_url(),
			'raw_cost'       => $travel_cost,
		);
	} else {
		$response = array( 'type' => 'error' );
	}

	wp_send_json( $response );
} // mdjm_update_event_travel_data_ajax
add_action( 'wp_ajax_mdjm_update_travel_data', 'mdjm_update_event_travel_data_ajax' );

/**
 * Save the custom event fields order
 *
 * @since   1.2.1.7
 */
function mdjm_order_custom_event_fields_ajax() {

	if ( isset( $_POST['customfields'] ) ) {
		foreach ( wp_unslash( $_POST['customfields'] ) as $order => $id ) { // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
			$order++;

			wp_update_post(
				array(
					'ID'         => $id,
					'menu_order' => $order,
				)
			);
		}
	}

	exit;

} // mdjm_order_custom_event_field_ajax
add_action( 'wp_ajax_order_custom_event_fields', 'mdjm_order_custom_event_fields_ajax' );

/**
 * Save the event transaction
 */
function mdjm_save_event_transaction_ajax() {
	global $mdjm_event;

	$result        = array();
	$event_id      = isset( $_POST['event_id'] ) ? absint( wp_unslash( $_POST['event_id'] ) ) : 0;
	$txn_direction = isset( $_POST['direction'] ) ? sanitize_text_field( wp_unslash( $_POST['direction'] ) ) : '';
	$txn_date      = isset( $_POST['date'] ) ? sanitize_text_field( wp_unslash( $_POST['date'] ) ) : '';
	$txn_amount    = isset( $_POST['amount'] ) ? sanitize_text_field( wp_unslash( $_POST['amount'] ) ) : '';
	$txn_source    = isset( $_POST['src'] ) ? sanitize_text_field( wp_unslash( $_POST['src'] ) ) : '';
	$txn_for       = isset( $_POST['for'] ) ? sanitize_text_field( wp_unslash( $_POST['for'] ) ) : '';

	if ( empty( $event_id ) ) {
		wp_send_json_error( 'Error: Invalid data!' );
		exit;
	}

	$mdjm_event = new MDJM_Event( $event_id );
	$mdjm_txn   = new MDJM_Txn();

	$txn_data = array(
		'post_parent' => $event_id,
		'post_author' => $mdjm_event->client,
		'post_status' => $txn_direction == 'Out' ? 'mdjm-expenditure' : 'mdjm-income',
		'post_date'   => date( 'Y-m-d H:i:s', strtotime( $txn_date ) ),
	);

	$txn_meta = array(
		'_mdjm_txn_status'      => 'Completed',
		'_mdjm_payment_from'    => $mdjm_event->client,
		'_mdjm_txn_total'       => $txn_amount,
		'_mdjm_payer_firstname' => mdjm_get_client_firstname( $mdjm_event->client ),
		'_mdjm_payer_lastname'  => mdjm_get_client_lastname( $mdjm_event->client ),
		'_mdjm_payer_email'     => mdjm_get_client_email( $mdjm_event->client ),
		'_mdjm_payment_from'    => mdjm_get_client_display_name( $mdjm_event->client ),
		'_mdjm_txn_source'      => $txn_source,
	);

	if ( $_POST['direction'] == 'In' ) {
		if ( ! empty( $_POST['from'] ) ) {
			$txn_meta['_mdjm_payment_from'] = sanitize_text_field( wp_unslash( $_POST['from'] ) );
		} else {
			$txn_meta['_mdjm_payment_from'] = mdjm_get_client_display_name( $mdjm_event->client );
		}
	}

	if ( $_POST['direction'] == 'Out' ) {
		if ( ! empty( $_POST['to'] ) ) {
			$txn_meta['_mdjm_payment_to'] = sanitize_text_field( wp_unslash( $_POST['to'] ) );
		} else {
			$txn_meta['_mdjm_payment_to'] = mdjm_get_client_display_name( $mdjm_event->client );
		}
	}

	$mdjm_txn->create( $txn_data, $txn_meta );

	if ( $mdjm_txn->ID > 0 ) {
		$result['type'] = 'success';
		mdjm_set_txn_type( $mdjm_txn->ID, $txn_for );

		$args = array(
			'user_id'         => get_current_user_id(),
			'event_id'        => $event_id,
			'comment_content' => sprintf(
				__( '%1$s payment of %2$s received for %3$s %4$s.', 'mobile-dj-manager' ),
				$_POST['direction'] == 'In' ? __( 'Incoming', 'mobile-dj-manager' ) : __( 'Outgoing', 'mobile-dj-manager' ),
				mdjm_currency_filter( mdjm_format_amount( sanitize_text_field( wp_unslash( $_POST['amount'] ) ) ) ),
				mdjm_get_label_singular( true ),
				mdjm_get_event_contract_id( $event_id )
			),
		);

		mdjm_add_journal( $args );

		// Email overide
		if ( empty( $_POST['send_notice'] ) && mdjm_get_option( 'manual_payment_cfm_template' ) ) {
			$manual_email_template = mdjm_get_option( 'manual_payment_cfm_template' );
			mdjm_update_option( 'manual_payment_cfm_template', 0 );
		}

		$payment_for = $mdjm_txn->get_type();
		$amount      = mdjm_currency_filter( mdjm_format_amount( sanitize_text_field( wp_unslash( $_POST['amount'] ) ) ) );

		mdjm_add_content_tag(
			'payment_for',
			__( 'Reason for payment', 'mobile-dj-manager' ),
			function() use ( $payment_for ) {
				return $payment_for;
			}
		);

		mdjm_add_content_tag(
			'payment_amount',
			__( 'Payment amount', 'mobile-dj-manager' ),
			function() use ( $amount ) {
				return $amount;
			}
		);

		mdjm_add_content_tag( 'payment_date', __( 'Date of payment', 'mobile-dj-manager' ), 'mdjm_content_tag_ddmmyyyy' );

		/**
		 * Allow hooks into this payment. The hook is suffixed with 'in' or 'out' depending
		 * on the payment direction. i.e. mdjm_post_add_manual_txn_in and mdjm_post_add_manual_txn_out
		 *
		 * @since   1.2.1.7
		 * @param   int     $event_id
		 * @param   obj     $txn_id
		 */
		do_action( 'mdjm_post_add_manual_txn_' . strtolower( $txn_direction ), $event_id, $mdjm_txn->ID );

		// Email overide
		if ( empty( $_POST['send_notice'] ) && isset( $manual_email_template ) ) {
			mdjm_update_option( 'manual_payment_cfm_template', $manual_email_template );
		}

		$result['deposit_paid'] = 'N';
		$result['balance_paid'] = 'N';

		if ( $mdjm_event->get_remaining_deposit() < 1 ) {
			mdjm_update_event_meta( $mdjm_event->ID, array( '_mdjm_event_deposit_status' => 'Paid' ) );
			$result['deposit_paid'] = 'Y';

			// Update event status if contract signed & we wait for deposit paid before confirming booking
			if ( mdjm_require_deposit_before_confirming() && $mdjm_event->get_contract_status() ) {

				mdjm_update_event_status(
					$mdjm_event->ID,
					'mdjm-approved',
					$mdjm_event->post_status,
					array( 'client_notices' => mdjm_get_option( 'booking_conf_to_client' ) )
				);

			}
		}

		if ( $mdjm_event->get_balance() < 1 ) {
			mdjm_update_event_meta( $mdjm_event->ID, array( '_mdjm_event_balance_status' => 'Paid' ) );
			mdjm_update_event_meta( $mdjm_event->ID, array( '_mdjm_event_deposit_status' => 'Paid' ) );
			$result['balance_paid'] = 'Y';
			$result['deposit_paid'] = 'Y';
		}
	} else {
		$result['type'] = 'error';
		$result['msg']  = __( 'Unable to add transaction', 'mobile-dj-manager' );
	}

	$result['event_status'] = get_post_status( $mdjm_event->ID );

	ob_start();
	mdjm_do_event_txn_table( $event_id );
	$result['transactions'] = ob_get_contents();
	ob_get_clean();

	echo json_encode( $result );

	exit;
} // mdjm_save_event_transaction_ajax
add_action( 'wp_ajax_add_event_transaction', 'mdjm_save_event_transaction_ajax' );

/**
 * Add a new event type
 * Initiated from the Event Post screen
 */
function mdjm_add_event_type_ajax() {

	$current = isset( $_POST['current'] ) ? sanitize_text_field( wp_unslash( $_POST['current'] ) ) : '';

	if ( empty( $_POST['type'] ) ) {
		$msg = __( 'Enter a name for the new Event Type', 'mobile-dj-manager' );
		wp_send_json_error(
			array(
				'selected' => $current,
				'msg'      => $msg,
			)
		);
	} else {
		$term = wp_insert_term( sanitize_text_field( wp_unslash( $_POST['type'] ) ), 'event-types' );

		if ( ! is_wp_error( $term ) ) {
			$msg = 'success';
		} else {
			error_log( $term->get_error_message() );
		}
	}

	$selected   = $msg == 'success' ? $term['term_id'] : $current;
	$categories = get_terms( 'event-types', array( 'hide_empty' => false ) );
	$options    = array();
	$output     = '';

	foreach ( $categories as $category ) {
		$options[ absint( $category->term_id ) ] = esc_html( $category->name );
	}

	foreach ( $options as $key => $option ) {
		$selected = selected( $term['term_id'], $key, false );

		$output .= '<option value="' . esc_attr( $key ) . '"' . $selected . '>' . esc_html( $option ) . '</option>' . "\r\n";
	}
	wp_send_json_success(
		array(
			'event_types' => $output,
			'msg'         => 'success',
		)
	);

	die();

} // mdjm_add_event_type_ajax
add_action( 'wp_ajax_add_event_type', 'mdjm_add_event_type_ajax' );

/**
 * Execute single event tasks.
 *
 * @since   1.5
 */
function mdjm_execute_event_task_ajax() {
	$task_id  = isset( $_POST['task'] ) ? sanitize_text_field( wp_unslash( $_POST['task'] ) ) : '';
	$event_id = isset( $_POST['event_id'] ) ? absint( $_POST['event_id'] ) : 0;

	if ( empty( $task_id ) || empty( $event_id ) ) {
		wp_send_json_error();
	}

	$tasks           = mdjm_get_tasks_for_event( $event_id );
	$result          = mdjm_run_single_event_task( $event_id, $task_id );
	$mdjm_event      = new MDJM_Event( $event_id );
	$completed_tasks = $mdjm_event->get_tasks();
	$tasks_history   = array();

	if ( ! $result ) {
		wp_send_json_error();
	}

	foreach ( $completed_tasks as $task_slug => $run_time ) {
		if ( ! array_key_exists( $task_slug, $tasks ) ) {
			continue;
		}

		$tasks_history[] = sprintf(
			'%s: %s',
			mdjm_get_task_name( $task_slug ),
			date( mdjm_get_option( 'short_date_format' ), $run_time )
		);
	}

	$task_history = '<span class="task-history-items">' . implode( '<br>', $tasks_history ) . '</span>';

	wp_send_json_success(
		array(
			'status'  => $mdjm_event->post_status,
			'history' => $task_history,
		)
	);
} // mdjm_execute_event_task_ajax
add_action( 'wp_ajax_mdjm_execute_event_task', 'mdjm_execute_event_task_ajax' );

/**
 * Add a new transaction type
 * Initiated from the Transaction Post screen
 */
function mdjm_add_transaction_type_ajax() {
	global $mdjm;

	$type    = isset( $_POST['type'] ) ? sanitize_text_field( wp_unslash( $_POST['type'] ) ) : '';
	$current = isset( $_POST['current'] ) ? sanitize_text_field( wp_unslash( $_POST['current'] ) ) : '';

	MDJM()->debug->log_it( 'Adding ' . $type . ' new Transaction Type from Transaction Post form', true );

	$args = array(
		'taxonomy'         => 'transaction-types',
		'hide_empty'       => 0,
		'name'             => 'mdjm_transaction_type',
		'id'               => 'mdjm_transaction_type',
		'orderby'          => 'name',
		'hierarchical'     => 0,
		'show_option_none' => __( 'Select Transaction Type', 'mobile-dj-manager' ),
		'class'            => ' required',
		'echo'             => 0,
	);

	/* -- Validate that we have a Transaction Type to add -- */
	if ( empty( $type ) ) {
		$result['type'] = 'Error';
		$result['msg']  = 'Please enter a name for the new Transaction Type';
	} else {
		$term = wp_insert_term( $type, 'transaction-types' );
		if ( is_array( $term ) ) {
			$result['type'] = 'success';
		} else {
			$result['type'] = 'error';
		}
	}

	MDJM()->debug->log_it( 'Completed adding ' . $type . ' new Transaction Type from Transaction Post form', true );

	$args['selected'] = $result['type'] == 'success' ? $term['term_id'] : $current;

	$result['transaction_types'] = wp_dropdown_categories( $args );

	$result = json_encode( $result );
	echo $result; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

	exit;
} // mdjm_add_transaction_type_ajax
add_action( 'wp_ajax_add_transaction_type', 'mdjm_add_transaction_type_ajax' );

/**
 * Determine the event setup time
 *
 * @since   1.5
 * @return  void
 */
function mdjm_event_setup_time_ajax() {
	$time_format = mdjm_get_option( 'time_format' );
	$start_time  = isset( $_POST['time'] ) ? sanitize_text_field( wp_unslash( $_POST['time'] ) ) : '';
	$event_date  = isset( $_POST['date'] ) ? sanitize_text_field( wp_unslash( $_POST['date'] ) ) : '';
	$date        = new DateTime( $event_date . ' ' . $start_time );
	$timestamp   = $date->format( 'U' );

	$setup_time = $timestamp - ( (int) mdjm_get_option( 'setup_time' ) * 60 );

	$hour     = 'H:i' == $time_format ? date( 'H', $setup_time ) : date( 'g', $setup_time );
	$minute   = date( 'i', $setup_time );
	$meridiem = 'H:i' == $time_format ? '' : date( 'A', $setup_time );

	wp_send_json_success(
		array(
			'hour'       => $hour,
			'minute'     => $minute,
			'meridiem'   => $meridiem,
			'date'       => date( mdjm_get_option( 'short_date_format' ), $setup_time ),
			'datepicker' => date( 'Y-m-d', $setup_time ),
		)
	);
} // mdjm_event_setup_time_ajax
add_action( 'wp_ajax_mdjm_event_setup_time', 'mdjm_event_setup_time_ajax' );

/**
 * Calculate the event cost as event elements change
 *
 * @since   1.0
 * @return  void
 */
function mdjm_update_event_cost_ajax() {

	$event_id = isset( $_POST['event_id'] ) ? absint( wp_unslash( $_POST['event_id'] ) ) : null;

	if ( empty( $event_id ) ) {
		wp_send_json_error( 'Error: Invalid data!' );
		exit;
	}

	$mdjm_event  = new MDJM_Event( $event_id );
	$mdjm_travel = new MDJM_Travel();

	$event_cost    = $mdjm_event->price;
	$event_date    = $event_date = ! empty( $_POST['event_date'] ) ? sanitize_text_field( wp_unslash( $_POST['event_date'] ) ) : null;
	$base_cost     = '0.00';
	$package       = $mdjm_event->get_package();
	$package_price = $package ? mdjm_get_package_price( $package, $event_date ) : '0.00';
	$addons        = $mdjm_event->get_addons();
	$travel_data   = $mdjm_event->get_travel_data();
	$employee_id   = isset( $_POST['employee_id'] ) ? sanitize_text_field( wp_unslash( $_POST['employee_id'] ) ) : 0;
	$dest          = isset( $_POST['venue'] ) ? wp_unslash( $_POST['venue'] ) : ''; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
	$dest          = maybe_unserialize( $dest );
	$package_cost  = 0;
	$addons_cost   = 0;
	$travel_cost   = 0;
	$additional    = ! empty( $_POST['additional'] ) ? (float) $_POST['additional'] : 0;
	$discount      = ! empty( $_POST['discount'] ) ? (float) $_POST['discount'] : 0;

	if ( $event_cost ) {
		$event_cost = (float) $event_cost;
		$base_cost  = ( $package_price ) ? $event_cost - $package_price : $event_cost;
	}

	if ( $package ) {
		$base_cost = $event_cost - $package_price;
	}

	if ( $addons ) {
		foreach ( $addons as $addon ) {
			$addon_price = mdjm_get_addon_price( $addon, $event_date );
			$base_cost   = $base_cost - (float) $addon_price;
		}
	}

	if ( $travel_data && ! empty( $travel_data['cost'] ) ) {
		$base_cost = $base_cost - (float) $travel_data['cost'];
	}

	$base_cost = $base_cost - $additional;
	$base_cost = $base_cost + $discount;

	$new_package = ! empty( $_POST['package'] ) ? absint( wp_unslash( $_POST['package'] ) ) : false;
	$new_addons  = ! empty( $_POST['addons'] ) ? wp_unslash( $_POST['addons'] ) : false; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized

	$cost = $base_cost;

	if ( $new_package ) {
		$package_cost = (float) mdjm_get_package_price( $new_package, $event_date );
	}

	if ( $new_addons ) {
		foreach ( $new_addons as $new_addon ) {
			$addons_cost += (float) mdjm_get_addon_price( absint( $new_addon ), $event_date );
		}
	}

	if ( $mdjm_travel->add_travel_cost ) {
		if ( ! empty( $employee_id ) ) {
			$mdjm_travel->__set( 'start_address', $mdjm_travel->get_employee_address( $employee_id ) );
		}

		$mdjm_travel->set_destination( $dest );
		$mdjm_travel->get_travel_data();

		$new_travel = ! empty( $mdjm_travel->data ) ? $mdjm_travel->get_cost() : false;

		if ( $new_travel && (float) preg_replace( '/[^0-9.]*/', '', $mdjm_travel->data['distance'] ) >= mdjm_get_option( 'travel_min_distance' ) ) {
			$travel_cost = (float) $new_travel;
		}
	}

	$cost += $package_cost;
	$cost += $addons_cost;
	$cost += $travel_cost;
	$cost += $additional;
	// $cost -= $discount;

	if ( ! empty( $cost ) ) {
		$result['type'] = 'success';
		$result['cost'] = mdjm_sanitize_amount( (float) $cost );
	} else {
		$result['type'] = 'success';
		$result['cost'] = mdjm_sanitize_amount( 0 );
	}

	$result['package_cost']    = mdjm_sanitize_amount( $package_cost );
	$result['addons_cost']     = mdjm_sanitize_amount( $addons_cost );
	$result['travel_cost']     = mdjm_sanitize_amount( $travel_cost );
	$result['additional_cost'] = mdjm_sanitize_amount( $additional );
	$result['discount']        = mdjm_sanitize_amount( $discount );

	wp_send_json( $result );

} // mdjm_update_event_cost_ajax
add_action( 'wp_ajax_mdjm_update_event_cost', 'mdjm_update_event_cost_ajax' );

/**
 * Update the available list of packages and addons when the primary event employee
 * the event type, or the event date changes.
 *
 * @since   1.0
 * @return  void
 */
function mdjm_refresh_event_package_options_ajax() {

	$employee        = ( ! empty( $_POST['employee'] ) ? sanitize_text_field( wp_unslash( $_POST['employee'] ) ) : '' );
	$current_package = ( ! empty( $_POST['package'] ) ? sanitize_text_field( wp_unslash( $_POST['package'] ) ) : '' );
	$current_addons  = ( ! empty( $_POST['addons'] ) ? wp_unslash( $_POST['addons'] ) : '' ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
	$event_type      = ( ! empty( $_POST['event_type'] ) ? sanitize_text_field( wp_unslash( $_POST['event_type'] ) ) : '' );
	$event_date      = ( ! empty( $_POST['event_date'] ) ? sanitize_text_field( wp_unslash( $_POST['event_date'] ) ) : '' );

	$packages = MDJM()->html->packages_dropdown(
		array(
			'selected'     => $current_package,
			'chosen'       => true,
			'employee'     => $employee,
			'event_type'   => $event_type,
			'event_date'   => $event_date,
			'options_only' => true,
			'blank_first'  => true,
			'data'         => array(),
		)
	);

	$selected_addons = ! empty( $current_addons ) ? $current_addons : array();

	$addons = MDJM()->html->addons_dropdown(
		array(
			'selected'         => $selected_addons,
			'show_option_none' => false,
			'show_option_all'  => false,
			'employee'         => $employee,
			'package'          => $current_package,
			'event_type'       => $event_type,
			'event_date'       => $event_date,
			'cost'             => true,
			'placeholder'      => __( 'Select Add-ons', 'mobile-dj-manager' ),
			'chosen'           => true,
			'options_only'     => true,
			'blank_first'      => true,
			'data'             => array(),
		)
	);

	if ( ! empty( $addons ) || ! empty( $packages ) ) {
		$result['type'] = 'success';
	} else {
		$result['type'] = 'error';
		$result['msg']  = __( 'No packages or addons available', 'mobile-dj-manager' );
	}

	if ( ! empty( $packages ) ) {
		$result['packages'] = $packages;
	} else {
		$result['packages'] = __( 'No Packages Available', 'mobile-dj-manager' );
	}

	if ( ! empty( $addons ) && $packages != '<option value="0">' . __( 'No Packages Available', 'mobile-dj-manager' ) . '</option>' ) {
		$result['addons'] = $addons;
	} else {
		$result['addons'] = __( 'No Addons Available', 'mobile-dj-manager' );
	}

	echo json_encode( $result );

	exit;

} // mdjm_refresh_event_package_options_ajax
add_action( 'wp_ajax_refresh_event_package_options', 'mdjm_refresh_event_package_options_ajax' );

/**
 * Update the event deposit amount based upon the event cost
 * and the payment settings.
 *
 * @since   1.0
 * @return  void
 */
function mdjm_update_event_deposit_ajax() {

	$event_cost = isset( $_POST['current_cost'] ) ? sanitize_text_field( wp_unslash( $_POST['current_cost'] ) ) : 0;

	$deposit = mdjm_calculate_deposit( $event_cost );

	if ( ! empty( $deposit ) ) {
		$result['type']    = 'success';
		$result['deposit'] = mdjm_sanitize_amount( $deposit );
	} else {
		$result['type'] = 'error';
		$result['msg']  = 'Unable to calculate deposit';
	}

	$result = json_encode( $result );
	echo $result; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

	exit;

} // mdjm_update_event_deposit_ajax
add_action( 'wp_ajax_update_event_deposit', 'mdjm_update_event_deposit_ajax' );

/**
 * Add an employee to the event.
 *
 * @since   1.2.1
 * @return  void
 */
function mdjm_add_employee_to_event_ajax() {

	$event_id = isset( $_POST['event_id'] ) ? absint( wp_unslash( $_POST['event_id'] ) ) : 0;

	$args = array(
		'id'             => isset( $_POST['employee_id'] ) ? sanitize_text_field( wp_unslash( $_POST['employee_id'] ) ) : '',
		'role'           => isset( $_POST['employee_role'] ) ? sanitize_text_field( wp_unslash( $_POST['employee_role'] ) ) : '',
		'wage'           => isset( $_POST['employee_wage'] ) ? sanitize_text_field( wp_unslash( $_POST['employee_wage'] ) ) : '',
		'payment_status' => 'unpaid',
	);

	if ( ! mdjm_add_employee_to_event( $event_id, $args ) ) {

		$result['type'] = 'error';
		$result['msg']  = __( 'Unable to add employee', 'mobile-dj-manager' );

	} else {
		$result['type'] = 'success';
	}

	ob_start();
	mdjm_do_event_employees_list_table( $event_id );
	$result['employees'] = ob_get_contents();
	ob_get_clean();

	echo json_encode( $result ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

	exit;

} // mdjm_add_employee_to_event_ajax
add_action( 'wp_ajax_add_employee_to_event', 'mdjm_add_employee_to_event_ajax' );

/**
 * Remove an employee from the event.
 *
 * @since   1.2.1
 * @return  void
 */
function mdjm_remove_employee_from_event_ajax() {

	$event_id    = isset( $_POST['event_id'] ) ? absint( wp_unslash( $_POST['event_id'] ) ) : 0;
	$employee_id = isset( $_POST['employee_id'] ) ? absint( wp_unslash( $_POST['employee_id'] ) ) : 0;

	mdjm_remove_employee_from_event( $employee_id, $event_id );

	$result['type'] = 'success';

	ob_start();
	mdjm_do_event_employees_list_table( $event_id );
	$result['employees'] = ob_get_contents();
	ob_get_clean();

	echo json_encode( $result ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

	exit;

} // mdjm_remove_employee_from_event_ajax
add_action( 'wp_ajax_remove_employee_from_event', 'mdjm_remove_employee_from_event_ajax' );

/**
 * Retrieve the title of a template
 *
 * @since   1.4.7
 * @return  str
 */
function mdjm_mdjm_get_template_title_ajax() {
	$title           = isset( $_POST['template'] ) ? get_the_title( sanitize_text_field( wp_unslash( $_POST['template'] ) ) ) : '';
	$result['title'] = $title;
	echo json_encode( $result ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

	exit;
} // mdjm_mdjm_get_template_title_ajax
add_action( 'wp_ajax_mdjm_get_template_title', 'mdjm_mdjm_get_template_title_ajax' );

/**
 * Update the email content field with the selected template.
 *
 * @since   1.2.1
 * @return  void
 */
function mdjm_set_email_content_ajax() {

	if ( empty( $_POST['template'] ) ) {
		$result['type']            = 'success';
		$result['updated_content'] = '';
	} else {
		$content = mdjm_get_email_template_content( sanitize_text_field( wp_unslash( $_POST['template'] ) ) );

		if ( ! $content ) {
			$result['type'] = 'error';
			$result['msg']  = __( 'Unable to retrieve template content', 'mobile-dj-manager' );
		} else {
			$result['type']            = 'success';
			$result['updated_content'] = $content;
			$result['updated_subject'] = html_entity_decode( get_the_title( sanitize_text_field( wp_unslash( $_POST['template'] ) ) ) );
		}
	}

	$result = json_encode( $result );

	echo $result; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

	exit;

} // mdjm_set_email_content_ajax
add_action( 'wp_ajax_mdjm_set_email_content', 'mdjm_set_email_content_ajax' );

/**
 * Update the email content field with the selected template.
 *
 * @since   1.2.1
 * @return  void
 */
function mdjm_user_events_dropdown_ajax() {

	$result['event_list'] = '<option value="0">' . __( 'Select an Event', 'mobile-dj-manager' ) . '</option>';

	if ( ! empty( $_POST['recipient'] ) ) {

		$statuses = 'any';

		if ( mdjm_is_employee( absint( wp_unslash( $_POST['recipient'] ) ) ) ) {

			if ( mdjm_get_option( 'comms_show_active_events_only' ) ) {
				$statuses = array( 'post_status' => mdjm_active_event_statuses() );
			}

			$events = mdjm_get_employee_events( absint( wp_unslash( $_POST['recipient'] ) ), $statuses );

		} else {

			if ( mdjm_get_option( 'comms_show_active_events_only' ) ) {
				$statuses = mdjm_active_event_statuses();
			}

			$events = mdjm_get_client_events( absint( wp_unslash( $_POST['recipient'] ) ), $statuses );
		}

		if ( $events ) {
			foreach ( $events as $event ) {
				$result['event_list'] .= '<option value="' . $event->ID . '">';
				$result['event_list'] .= mdjm_get_event_date( $event->ID ) . ' ';
				$result['event_list'] .= __( 'from', 'mobile-dj-manager' ) . ' ';
				$result['event_list'] .= mdjm_get_event_start( $event->ID ) . ' ';
				$result['event_list'] .= '(' . mdjm_get_event_status( $event->ID ) . ')';
				$result['event_list'] .= '</option>';
			}
		}
	}

	$result['type'] = 'success';
	$result         = json_encode( $result );

	echo $result; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

	exit;

} // mdjm_user_events_dropdown_ajax
add_action( 'wp_ajax_mdjm_user_events_dropdown', 'mdjm_user_events_dropdown_ajax' );

/**
 * Refresh the addons options when the package selection is updated.
 *
 * @since   1.2.1.7
 * @return  void
 */
function mdjm_refresh_event_addon_options_ajax() {

	$package    = isset( $_POST['package'] ) ? sanitize_text_field( wp_unslash( $_POST['package'] ) ) : 0;
	$employee   = ( isset( $_POST['employee'] ) ? sanitize_text_field( wp_unslash( $_POST['employee'] ) ) : false );
	$selected   = ( ! empty( $_POST['selected'] ) ? wp_unslash( $_POST['selected'] ) : array() ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
	$event_type = ( ! empty( $_POST['event_type'] ) ? sanitize_text_field( wp_unslash( $_POST['event_type'] ) ) : '' );
	$event_date = ( ! empty( $_POST['event_date'] ) ? sanitize_text_field( wp_unslash( $_POST['event_date'] ) ) : '' );

	$addons = MDJM()->html->addons_dropdown(
		array(
			'selected'         => $selected,
			'show_option_none' => false,
			'show_option_all'  => false,
			'employee'         => $employee,
			'event_type'       => $event_type,
			'event_date'       => $event_date,
			'package'          => $package,
			'cost'             => true,
			'placeholder'      => __( 'Select Add-ons', 'mobile-dj-manager' ),
			'chosen'           => true,
			'options_only'     => true,
			'data'             => array(),
		)
	);

	$result['type'] = 'success';

	if ( ! empty( $addons ) ) {
		$result['addons'] = $addons;
	} else {
		$result['addons'] = '<option value="0" disabled="disabled">' . __( 'No addons available', 'mobile-dj-manager' ) . '</option>';
	}

	echo json_encode( $result );

	exit;

} // mdjm_refresh_event_addon_options_ajax
add_action( 'wp_ajax_refresh_event_addon_options', 'mdjm_refresh_event_addon_options_ajax' );
add_action( 'wp_ajax_nopriv_refresh_event_addon_options', 'mdjm_refresh_event_addon_options_ajax' );

/**
 * Check the availability status for the given date
 *
 * @since   1.2.1
 * @param   Global $_POST
 * @return  arr
 */
function mdjm_do_availability_check_ajax() {

	$date       = isset( $_POST['check_date'] ) ? sanitize_text_field( wp_unslash( $_POST['check_date'] ) ) : '';
	$employees  = isset( $_POST['employees'] ) ? wp_unslash( $_POST['employees'] ) : false; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
	$roles      = isset( $_POST['roles'] ) ? wp_unslash( $_POST['roles'] ) : false; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
	$short_date = mdjm_format_short_date( $date );
	$result     = mdjm_do_availability_check( $date, $employees, $roles );

	$available_text   = mdjm_get_option( 'availability_check_pass_text' );
	$unavailable_text = mdjm_get_option( 'availability_check_fail_text' );
	$search           = array( '{EVENT_DATE}', '{EVENT_DATE_SHORT}' );
	$replace          = array( date( 'l, jS F Y', strtotime( $date ) ), mdjm_format_short_date( $date ) );

	if ( ! empty( $result['available'] ) ) {
		$result['result']       = 'available';
		$result['notice_class'] = 'updated';
		$message                = str_ireplace( $search, $replace, $available_text );

		$result['message'] = mdjm_do_content_tags( $message );

	} else {
		$result['result']       = 'unavailable';
		$result['notice_class'] = 'error';
		$message                = str_ireplace( $search, $replace, $unavailable_text );

		$result['message'] = mdjm_do_content_tags( $message );
	}

	wp_send_json( $result );
} // mdjm_do_availability_check_ajax
add_action( 'wp_ajax_mdjm_do_availability_check', 'mdjm_do_availability_check_ajax' );
add_action( 'wp_ajax_nopriv_mdjm_do_availability_check', 'mdjm_do_availability_check_ajax' );
