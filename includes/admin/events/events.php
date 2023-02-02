<?php
/**
 * @author: Mike Howard, Jack Mawhinney, Dan Porter
 *
 * Manages Event posts admin screen and queries.
 *
 * @since   0.5
 * @package MDJM
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Define the columns to be displayed for event posts.
 *
 * @since   0.5
 * @param   arr $columns    Array of column names.
 * @return  arr     $columns    Filtered array of column names
 */
function mdjm_event_post_columns( $columns ) {

	$columns = array(
		'cb'           => '<input type="checkbox" />',
		'event_date'   => __( 'Date', 'mobile-dj-manager' ),
		/* translators: %s Event Events placeholder */
		'event_id'     => sprintf( __( '%s ID', 'mobile-dj-manager' ), mdjm_get_label_singular() ),
		'client'       => __( 'Client', 'mobile-dj-manager' ),
		'employees'    => __( 'Employees', 'mobile-dj-manager' ),
		'event_status' => __( 'Status', 'mobile-dj-manager' ),
		/* translators: %s Event Events placeholder */
		'event_type'   => sprintf( __( '%s type', 'mobile-dj-manager' ), mdjm_get_label_singular() ),
		'event_name'   => __( 'Name', 'mobile-dj-manager' ),
		'value'        => __( 'Value', 'mobile-dj-manager' ),
		'balance'      => __( 'Due', 'mobile-dj-manager' ),
		'playlist'     => __( 'Event Details', 'mobile-dj-manager' ),
		'journal'      => __( 'Journal', 'mobile-dj-manager' ),
		'venue'        => __( 'Venue', 'mobile-dj-manager' ),
	);

	if ( ! mdjm_employee_can( 'manage_all_events' ) && isset( $columns['cb'] ) ) {
		unset( $columns['cb'] );
		unset( $columns['journal'] );
	}

	if ( ! mdjm_employee_can( 'edit_txns' ) ) {
		unset( $columns['value'] );
		unset( $columns['balance'] );
	}

	return $columns;
} // mdjm_event_post_columns
add_filter( 'manage_mdjm-event_posts_columns', 'mdjm_event_post_columns' );

/**
 * Define the event post columns hidden by default
 *
 * @since   1.4.7.3
 * @param   arr       $hidden     An array of columns hidden by default.
 * @param   WP_Screen $screen     WP_Screen object of the current screen.
 */
function mdjm_event_post_hidden_columns( $hidden, $screen ) {

	if ( 'edit-mdjm-event' === $screen->id ) {
		$hidden[] = 'event_name';
	}

	return $hidden;
} // mdjm_event_post_hidden_columns
add_filter( 'default_hidden_columns', 'mdjm_event_post_hidden_columns', 10, 2 );

/**
 * Define which columns are sortable for event posts.
 *
 * @since   0.7
 * @param   arr $sortable_columns   Array of event post sortable columns.
 * @return  arr     $sortable_columns   Filtered Array of event post sortable columns
 */
function mdjm_event_post_sortable_columns( $sortable_columns ) {
	$sortable_columns['event_date']   = 'event_date';
	$sortable_columns['value']        = 'value';
	$sortable_columns['event_status'] = 'event_status';

	return $sortable_columns;
} // mdjm_event_post_sortable_columns
add_filter( 'manage_edit-mdjm-event_sortable_columns', 'mdjm_event_post_sortable_columns' );

/**
 * Define the data to be displayed in each of the custom columns for the Transaction post types
 *
 * @since   0.9
 * @param   str $column_name    The name of the column to display.
 * @param   int $post_id        The current post ID.
 */
function mdjm_event_posts_custom_column( $column_name, $post_id ) {
	global $post;

	if ( mdjm_employee_can( 'edit_txns' ) && 'value' === $column_name ) {
		$value = mdjm_get_event_price( $post_id );
	}

	switch ( $column_name ) {
		// Event Date.
		case 'event_date':
			if ( mdjm_employee_can( 'read_events' ) ) {
				echo '<strong><a href="' . esc_url( admin_url( 'post.php?post=' . $post_id . '&action=edit' ) ) . '">' . esc_html( date( 'd M Y', strtotime( get_post_meta( $post_id, '_mdjm_event_date', true ) ) ) ) . '</a>';
			} else {
				echo '<strong>' . esc_html( date( 'd M Y', strtotime( get_post_meta( $post_id, '_mdjm_event_date', true ) ) ) ) . '</strong>';
			}
			break;

		case 'event_id':
			echo '<strong><a href="' . esc_url( admin_url( 'post.php?post=' . $post_id . '&action=edit' ) ) . '">' . esc_html( mdjm_get_event_contract_id( $post_id ) ) . '</a>';
			break;

		// Client.
		case 'client':
			$client = get_userdata( get_post_meta( $post->ID, '_mdjm_event_client', true ) );

			if ( ! empty( $client ) ) {
				if ( mdjm_employee_can( 'send_comms' ) ) {
					printf(
						'<a href="%s">%s</a>',
						esc_url(
							add_query_arg(
								array(
									'recipient' => $client->ID,
									'event_id'  => $post_id,
								),
								admin_url( 'admin.php?page=mdjm-comms' )
							)
						),
						esc_html( $client->display_name )
					);
				} else {
					echo esc_html( $client->display_name );
				}
			} else {
				echo '<span class="mdjm-form-error">';
				esc_html_e( 'Not Assigned', 'mobile-dj-manager' );
				echo '</span>';
			}
			break;

		// Employees.
		case 'employees':
			global $wp_roles;

			$primary   = get_userdata( mdjm_get_event_primary_employee( $post->ID ) );
			$employees = mdjm_get_event_employees_data( $post->ID );

			if ( ! empty( $primary ) ) {

				if ( mdjm_employee_can( 'send_comms' ) ) {
					printf(
						'<a href="%s" title="%s">%s</a>',
						esc_url(
							add_query_arg(
								array(
									'recipient' => $primary->ID,
									'event_id'  => $post_id,
								),
								admin_url( 'admin.php?page=mdjm-comms' )
							)
						),
						esc_html( mdjm_get_option( 'artist', __( 'DJ', 'mobile-dj-manager' ) ) ),
						esc_html( $primary->display_name )
					);
				} else {
					echo '<a title="' . esc_attr( mdjm_get_option( 'artist', __( 'DJ', 'mobile-dj-manager' ) ) ) . '">' . esc_html( $primary->display_name ) . '</a>';
				}
			} else {
				echo '<span class="mdjm-form-error">';
				esc_html_e( 'Not Assigned', 'mobile-dj-manager' );
				echo '</span>';
			}

			if ( ! empty( $employees ) ) {
				echo '<br />';
				$i = 1;

				foreach ( $employees as $employee ) {

					echo '<em>';

					if ( mdjm_employee_can( 'send_comms' ) ) {
						printf(
							'<a href="%s" title="%s">%s</a>',
							esc_url(
								add_query_arg(
									array(
										'recipient' => $employee['id'],
										'event_id'  => $post_id,
									),
									admin_url( 'admin.php?page=mdjm-comms' )
								)
							),
							esc_html( translate_user_role( $wp_roles->roles[ $employee['role'] ]['name'] ) ),
							esc_html( mdjm_get_employee_display_name( $employee['id'] ) )
						);
					} else {
						echo '<a title="' . esc_html( translate_user_role( $wp_roles->roles[ $employee['role'] ]['name'] ) ) . '">' . esc_html( mdjm_get_employee_display_name( $employee['id'] ) ) . '</a>';
					}

					echo '</em>';

					if ( count( $employees ) !== $i ) {
						echo '<br />';
					}
				}
			}

			break;

		// Status.
		case 'event_status':
			echo esc_html( get_post_status_object( $post->post_status )->label );
			break;

		// Event Type.
		case 'event_type':
			$event_types = get_the_terms( $post_id, 'event-types' );
			if ( is_array( $event_types ) ) {
				foreach ( $event_types as $key => $event_type ) {
					$event_types[ $key ] = esc_html( $event_type->name );
				}
				echo implode( '<br/>', $event_types ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			}
			break;

		// Event Name.
		case 'event_name':
			echo esc_html( mdjm_get_event_name( $post_id ) );
			break;

		// Value.
		case 'value':
			if ( mdjm_employee_can( 'edit_txns' ) ) {
				if ( ! empty( $value ) && '0.00' !== $value ) {

					echo esc_html( mdjm_currency_filter( mdjm_format_amount( $value ) ) );
					echo '<br />';

				} else {
					echo '<span class="mdjm-form-error">' . esc_html( mdjm_currency_filter( mdjm_format_amount( '0.00' ) ) ) . '</span>';
				}
			} else {
				echo '&mdash;';
			}
			break;

		// Balance.
		case 'balance':
			if ( mdjm_employee_can( 'edit_txns' ) ) {

				echo esc_html( mdjm_currency_filter( mdjm_format_amount( mdjm_get_event_balance( $post_id ) ) ) );

				echo '<br />';

				$deposit_status = mdjm_get_event_deposit_status( $post_id );

				if ( 'Paid' === mdjm_get_event_deposit_status( $post_id ) ) {
					printf(
						/* translators: %1 Deposit %2 amount */
						__( '<i title="%1$s %2$s paid" class="far fa-check-square" aria-hidden="true">', 'mobile-dj-manager' ), // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
						esc_html( mdjm_currency_filter( mdjm_format_amount( mdjm_get_event_deposit( $post_id ) ) ) ),
						esc_html( mdjm_get_deposit_label() )
					);
				}
			} else {
				echo '&mdash;';
			}
			break;

		// Playlist.
		case 'playlist':
			if ( mdjm_employee_can( 'read_events' ) ) {
				$total = mdjm_count_playlist_entries( $post_id );

				echo '<a href="' . esc_url( mdjm_get_admin_page( 'playlists' ) . $post_id ) . '">' .
					esc_html( _n( 'Details', 'Details', $total, 'mobile-dj-manager' ) ) . '</a>' . "\r\n";
			} else {
				echo '&mdash;';
			}
			break;

		// Journal.
		case 'journal':
			if ( mdjm_employee_can( 'read_events_all' ) ) {
				$total = wp_count_comments( $post_id )->approved;
				echo '<a href="' . esc_url( admin_url( '/edit-comments.php?p=' . $post_id ) ) . '">' .
					esc_html( $total ) . ' ' .
					esc_html( _n( 'Entry', 'Entries', $total, 'mobile-dj-manager' ) ) .
					'</a>' . "\r\n";
			} else {
				echo '&mdash;';
			}
			break;

			/*
			 * Display venue name on events page
			 * copyright Dan Porter, Jack Mawhinney
			 *
			 */
		// Venue Name.
		case 'venue':
			$mdjm_event = new MDJM_Event( $post_id );
			echo esc_html( mdjm_get_event_venue_meta( $mdjm_event->get_venue_id(), 'name' ) );
			break;

	} // switch

} // mdjm_event_posts_custom_column
add_action( 'manage_mdjm-event_posts_custom_column', 'mdjm_event_posts_custom_column', 10, 2 );

/**
 * Remove the edit bulk action from the event posts list.
 *
 * @since   1.0
 * @param   arr $actions    Array of actions.
 * @return  arr     $actions    Filtered Array of actions
 */
function mdjm_event_bulk_action_list( $actions ) {
	unset( $actions['edit'] );

	return $actions;
} // mdjm_event_bulk_action_list
add_filter( 'bulk_actions-edit-mdjm-event', 'mdjm_event_bulk_action_list' );

/**
 * Adds custom bulk actions.
 *
 * @since   1.3
 */
function mdjm_event_add_reject_bulk_actions() {

	global $post;

	$current_status = isset( $_GET['post_status'] ) ? sanitize_text_field( wp_unslash( $_GET['post_status'] ) ) : false; // phpcs:ignore WordPress.Security.NonceVerification

	if ( 'mdjm-unattended' !== $current_status || 'mdjm-event' !== get_post_type() ) { // phpcs:ignore WordPress.Security.NonceVerification
		return;
	}

	?>
	<script type="text/javascript">
	jQuery(document).ready(function() {
		jQuery('<option>').val('reject_enquiry').text('<?php esc_attr_e( 'Reject', 'mobile-dj-manager' ); ?>').appendTo("select[name='action']");
		jQuery('<option>').val('reject_enquiry').text('<?php esc_attr_e( 'Reject', 'mobile-dj-manager' ); ?>').appendTo("select[name='action2']");
	});
	</script>
	<?php

} // mdjm_event_add_custom_bulk_actions
add_action( 'admin_footer-edit.php', 'mdjm_event_add_reject_bulk_actions' );

/**
 * Process reject enquiry bulk action requests.
 *
 * @since   1.3
 */
function mdjm_event_instant_reject() {

	if ( ! isset( $_REQUEST['post_status'] ) || 'mdjm-unattended' !== $_REQUEST['post_status'] || isset( $_REQUEST['mdjm-message'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification
		return;
	}

	if ( isset( $_REQUEST['action'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification
		$action = sanitize_text_field( wp_unslash( $_REQUEST['action'] ) ); // phpcs:ignore WordPress.Security.NonceVerification
	} elseif ( isset( $_REQUEST['action2'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification
		$action = sanitize_text_field( wp_unslash( $_REQUEST['action2'] ) ); // phpcs:ignore WordPress.Security.NonceVerification
	} else {
		$action = '';
	}

	if ( empty( $action ) || 'reject_enquiry' !== $action || empty( $_REQUEST['post'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification
		return;
	}

	if ( ! mdjm_employee_can( 'manage_all_events' ) ) {
		return;
	}

	$args    = array( 'reject_reason' => __( 'No reason specified', 'mobile-dj-manager' ) );
	$message = 'unattended_enquiries_rejected_success';

	$i = 0;

	foreach ( wp_unslash( $_REQUEST['post'] ) // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
			as $event_id ) { // phpcs:ignore WordPress.Security.NonceVerification
		if ( ! mdjm_update_event_status( $event_id, 'mdjm-rejected', get_post_status( $event_id ), $args ) ) {
			$message = 'unattended_enquiries_rejected_failed';
		} else {
			$i++;
		}
	}

	$url = admin_url( 'edit.php?post_status=mdjm-unattended&post_type=mdjm-event&paged=1' );

	wp_safe_redirect(
		add_query_arg(
			array(
				'mdjm-message' => $message,
				'mdjm-count'   => $i,
			),
			$url
		)
	);
	exit;

} // mdjm_event_instant_reject
add_action( 'load-edit.php', 'mdjm_event_instant_reject' );

/**
 * Add the filter dropdowns to the event post list.
 *
 * @since   1.0
 */
function mdjm_event_post_filter_list() {

	if ( ! isset( $_GET['post_type'] ) || 'mdjm-event' !== $_GET['post_type'] ) { // phpcs:ignore WordPress.Security.NonceVerification
		return;
	}

	mdjm_event_date_filter_dropdown();
	mdjm_event_type_filter_dropdown();

	if ( mdjm_is_employer() && mdjm_employee_can( 'manage_employees' ) ) {
		mdjm_event_employee_filter_dropdown();
	}

	if ( mdjm_employee_can( 'list_all_clients' ) ) {
		mdjm_event_client_filter_dropdown();
	}

} // mdjm_event_post_filter_list
add_action( 'restrict_manage_posts', 'mdjm_event_post_filter_list' );

/**
 * Display the filter drop down list to enable user to select and filter event by month/year.
 *
 * @since   1.0
 */
function mdjm_event_date_filter_dropdown() {
	global $wpdb, $wp_locale;

	$month_query = 'SELECT DISTINCT YEAR( meta_value ) as year, MONTH( meta_value ) as month
		FROM `' . $wpdb->postmeta . "` WHERE `meta_key` = '_mdjm_event_date'";

	$months = $wpdb->get_results( $month_query );

	$month_count = count( $months );

	if ( ! $month_count || ( 1 === $month_count && 0 === $months[0]->month ) ) {
		return;
	}

	$m = isset( $_GET['mdjm_filter_date'] ) ? (int) $_GET['mdjm_filter_date'] : 0; // phpcs:ignore WordPress.Security.NonceVerification

	?>
	<label for="filter-by-date" class="screen-reader-text">Filter by Date</label>
	<select name="mdjm_filter_date" id="filter-by-date">
		<option value="0"><?php esc_attr_e( 'All Dates', 'mobile-dj-manager' ); ?></option>
	<?php
	foreach ( $months as $arc_row ) {
		if ( 0 === $arc_row->year ) {
			continue;
		}

		$month = zeroise( $arc_row->month, 2 );
		$year  = $arc_row->year;

		printf(
			"<option %s value='%s'>%s</option>\r\n",
			selected( $m, $year . $month, false ),
			esc_attr( $arc_row->year . $month ),
			sprintf(
				/* translators: %1 month name, %2: year */
				esc_html__( '%1$s %2$d', 'mobile-dj-manager' ),
				esc_attr( $wp_locale->get_month( $month ) ),
				esc_attr( $year )
			)
		);
	}
	?>
	</select>
	<?php
} // mdjm_event_date_filter_dropdown

/**
 * Display the filter drop down list to enable user to select and filter event by type.
 *
 * @since   1.0
 */
function mdjm_event_type_filter_dropdown() {

	$event_types = get_categories(
		array(
			'type'       => 'mdjm-event',
			'taxonomy'   => 'event-types',
			'pad_counts' => false,
			'hide_empty' => true,
			'orderby'    => 'name',
		)
	);

	$current = isset( $_GET['mdjm_filter_type'] ) ? sanitize_text_field( wp_unslash( $_GET['mdjm_filter_type'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification

	?>

	<?php if ( $event_types ) : ?>
		<select name="mdjm_filter_type">
			<?php /* translators: %s Event Events */ ?>
			<option value=""><?php printf( esc_html__( 'All %s Types', 'mobile-dj-manager' ), esc_html( mdjm_get_label_singular() ) ); ?></option>
			<?php foreach ( $event_types as $event_type ) : ?>
				<option value="<?php echo esc_attr( $event_type->term_id ); ?>"<?php selected( $event_type->term_id, $current ); ?>><?php echo esc_html( $event_type->name ); ?> (<?php echo esc_html( $event_type->category_count ); ?>)</option>
			<?php endforeach; ?>
		</select>

		<?php
	endif;

} // mdjm_event_type_filter_dropdown

/**
 * Display the filter drop down list to enable user to select and filter event by Employee.
 *
 * @since   1.0
 * @return  str     Outputs the dropdown for the employee filter
 */
function mdjm_event_employee_filter_dropdown() {

	$employees      = mdjm_get_employees();
	$employee_count = count( $employees );

	if ( ! $employee_count || 1 === $employee_count ) {
		return;
	}

	?>
	<label for="filter-by-employee" class="screen-reader-text"><?php esc_html_e( 'Filter by Employee', 'mobile-dj-manager' ); ?></label>

	<?php
	mdjm_employee_dropdown(
		array(
			'name'            => 'mdjm_filter_employee',
			'id'              => 'filter-by-employee',
			'selected'        => isset( $_GET['mdjm_filter_employee'] ) ? wp_unslash( $_GET['mdjm_filter_employee'] ) : 0, // phpcs:ignore WordPress.Security.NonceVerification
			'first_entry'     => __( 'All Employees', 'mobile-dj-manager' ),
			'first_entry_val' => 0,
			'group'           => true,
			'structure'       => true,
			'echo'            => true,
		)
	);

} // mdjm_event_employee_filter_dropdown

/**
 * Display the filter drop down list to enable user to select and filter event by Client.
 *
 * @since   1.0
 */
function mdjm_event_client_filter_dropdown() {

	$roles    = array( 'client', 'inactive_client' );
	$employee = ! mdjm_employee_can( 'read_events_all' ) ? get_current_user_id() : false;

	$all_clients = mdjm_get_clients( $roles, $employee );

	if ( ! $all_clients || 1 === count( $all_clients ) ) {
		return;
	}

	$selected = isset( $_GET['mdjm_filter_client'] ) ? (int) $_GET['mdjm_filter_client'] : 0; // phpcs:ignore WordPress.Security.NonceVerification

	foreach ( $all_clients as $_client ) {
		$client_events = mdjm_get_client_events( $_client->ID );

		if ( $client_events ) {
			$clients[ $_client->ID ] = $_client->display_name;
		}
	}

	if ( empty( $clients ) ) {
		return;
	}

	?>
	<label for="filter-by-client" class="screen-reader-text">Filter by <?php esc_html_e( 'Client', 'mobile-dj-manager' ); ?></label>
	<select name="mdjm_filter_client" id="mdjm_filter_client-by-dj">
		<option value="0"<?php selected( $selected, 0, false ); ?>><?php esc_attr_e( "All Client's", 'mobile-dj-manager' ); ?></option>
	<?php
	foreach ( $clients as $ID => $display_name ) {

		if ( empty( $display_name ) ) {
			continue;
		}

		printf(
			"<option %s value='%s'>%s</option>\n",
			selected( $selected, $ID, false ),
			esc_attr( $ID ),
			esc_attr( $display_name )
		);
	}
	?>
	</select>
	<?php
} // mdjm_event_client_filter_dropdown

/**
 * Customise the view filter counts
 *
 * @since   1.0
 * @param   arr $views      Array of views.
 * @return  arr     $views      Filtered Array of views
 */
function mdjm_event_view_filters( $views ) {

	$active_only = mdjm_get_option( 'show_active_only' );

	if ( 'mdjm-event' !== get_post_type() || ! $active_only ) {
		return $views;
	}

	$args = array();
	if ( ! empty( $_GET['mdjm_filter_employee'] ) || ! mdjm_employee_can( 'read_events_all' ) ) { // phpcs:ignore WordPress.Security.NonceVerification
		$args['employee'] = get_current_user_id();
	}

	$all_statuses      = mdjm_all_event_status_keys();
	$inactive_statuses = mdjm_inactive_event_status_keys();
	$num_posts         = mdjm_count_events( $args );
	$count             = 0;

	if ( ! empty( $num_posts ) ) {
		foreach ( $num_posts as $status => $status_count ) {
			if ( ! empty( $num_posts->$status ) && in_array( $status, $all_statuses ) ) {
				$views[ $status ] = preg_replace( '/\(.+\)/U', '(' . number_format_i18n( $num_posts->$status ) . ')', $views[ $status ] );
			}

			if ( ! in_array( $status, $inactive_statuses ) ) {
				$count += $status_count;
			}
		}
	}

	$views['all'] = preg_replace( '/\(.+\)/U', '(' . number_format_i18n( $count ) . ')', $views['all'] );

	if ( $active_only ) {
		$search = __( 'All', 'mobile-dj-manager' );
		/* translators: %s Event or Events */
		$replace      = sprintf( __( 'Active %s', 'mobile-dj-manager' ), mdjm_get_label_plural() );
		$views['all'] = str_replace( $search, $replace, $views['all'] );
	}

	foreach ( $views as $status => $link ) {
		if ( 'all' !== $status && ! in_array( $status, $all_statuses ) ) {
			unset( $views[ $status ] );
		}
	}

	return apply_filters( 'mdjm_event_views', $views );
} // mdjm_event_view_filters
add_filter( 'views_edit-mdjm-event', 'mdjm_event_view_filters' );

/**
 * Customise the post row actions on the event edit screen.
 *
 * @since   1.0
 * @param   arr $actions    Current post row actions.
 * @param   obj $post       The WP_Post post object.
 */
function mdjm_event_post_row_actions( $actions, $post ) {

	if ( 'mdjm-event' !== $post->post_type ) {
		return $actions;
	}

	if ( isset( $actions['trash'] ) ) {
		unset( $actions['trash'] );
	}
	if ( isset( $actions['view'] ) ) {
		unset( $actions['view'] );
	}
	if ( isset( $actions['edit'] ) && 'mdjm-unattended' === $post->post_status ) {
		unset( $actions['edit'] );
	}
	if ( isset( $actions['inline hide-if-no-js'] ) ) {
		unset( $actions['inline hide-if-no-js'] );
	}

	// Unattended events have additional actions to allow one-click responses.
	$url = remove_query_arg( array( 'mdjm-action', 'event_id' ) );

	if ( 'mdjm-unattended' === $post->post_status ) {

		// Quote for event.
		$actions['quote'] = sprintf(
			/* translators: %s URL to quote */
			__( '<a href="%s">Quote</a>', 'mobile-dj-manager' ),
			admin_url( 'post.php?post=' . $post->ID . '&action=edit&mdjm_action=respond' )
		);

		// Check availability.
		$actions['availability'] = sprintf(
			/* translators: %s Availability URL */
			__( '<a href="%s">Availability</a>', 'mobile-dj-manager' ),
			add_query_arg(
				array(
					'mdjm-action' => 'get_event_availability',
					'event_id'    => $post->ID,
				),
				wp_nonce_url( $url, 'get_event_availability', 'mdjm_nonce' )
			)
		);

		// Respond Unavailable.
		$actions['respond_unavailable'] = sprintf(
			/* translators: %s Unavailable URL */
			__( '<span class="trash"><a href="%s">Unavailable</a></span>', 'mobile-dj-manager' ),
			add_query_arg(
				array(
					'recipient'   => mdjm_get_client_id( $post->ID ),
					'template'    => mdjm_get_option( 'unavailable' ),
					'event_id'    => $post->ID,
					'mdjm-action' => 'respond_unavailable',
				),
				admin_url( 'admin.php?page=mdjm-comms' )
			)
		);

	}

	return $actions;
} // mdjm_event_post_row_actions
add_filter( 'post_row_actions', 'mdjm_event_post_row_actions', 10, 2 );

/**
 * Output the event post title hidden field.
 *
 * @since   1.0
 * @param   obj $post The WP_Post post object.
 */
function mdjm_event_set_post_title( $post ) {

	if ( 'mdjm-event' !== $post->post_type ) {
		return;
	}

	?>
	<input type="hidden" name="post_title" value="<?php echo esc_attr( mdjm_get_event_contract_id( $post->ID ) ); ?>" id="title" />
	<?php

} // mdjm_event_set_post_title
add_action( 'edit_form_after_title', 'mdjm_event_set_post_title' );

/**
 * Output the event name field.
 *
 * @since   1.5
 * @param   obj $post The WP_Post post object.
 */
function mdjm_output_event_name_field( $post ) {

	if ( 'mdjm-event' !== $post->post_type ) {
		return;
	}

	$value = esc_attr( mdjm_get_event_name( $post->ID ) );
	/* translators: %s App name */
	$placeholder = sprintf( __( 'Optional: Display name in %s', 'mobile-dj-manager' ), mdjm_get_option( 'app_name', __( 'Client Portal', 'mobile-dj-manager' ) ) );

	?>
	<div id="titlediv">
		<div id="titlewrap">
			<input type="text" name="_mdjm_event_name" id="_mdjm_event_name" autocomplete="off" value="<?php echo esc_attr( $value ); ?>" placeholder="<?php echo esc_attr( $placeholder ); ?>" />
		</div>
	</div>
<br/>
	<?php
	if ( mdjm_employee_can( 'read_events' ) ) {
		echo '<a class="button-primary" href="' . esc_url( mdjm_get_admin_page( 'playlists' ) . $post->ID ) . '">' .
			/* translators: %s Event or Events */
		sprintf( esc_html__( 'Print/Email %s Details', 'mobile-dj-manager' ), esc_html( mdjm_get_label_singular() ) ), "</a>\r\n";
	} else {
		echo '&mdash;';
	}
	
	$client = get_userdata( get_post_meta( $post->ID, '_mdjm_event_client', true ) );
if(isset($client->ID)):
		echo '<a class="button-primary" href="' . esc_url( mdjm_get_admin_page( 'comms' ) . '&recipient=' . $client->ID . '&event_id=' . $post->ID ) . '">' .
		/* translators: %s Event or Events */
		sprintf( esc_html__( 'Email Client', 'mobile-dj-manager' ), esc_html( mdjm_get_label_singular() ) ), "</a>\r\n";
endif;
	?>
	<?php
} // mdjm_output_event_name_field
add_action( 'edit_form_after_title', 'mdjm_output_event_name_field' ); 

/**
 * Rename the Publish and Update post buttons for events
 *
 * @since   1.3
 * @param   str $translation    The current button text translation.
 * @param   str $text           The text translation for the button.
 * @return  str     $translation    The filtererd text translation
 */
function mdjm_event_rename_publish_button( $translation, $text ) {

	global $post;

	if ( ! isset( $post ) || 'mdjm-event' !== $post->post_type ) {
		return $translation;
	}

	$event_statuses = mdjm_all_event_status();

	if ( 'Publish' === $text && isset( $event_statuses[ $post->post_status ] ) ) {
		return __( 'Update Event', 'mobile-dj-manager' );
	} elseif ( 'Publish' === $text ) {
		return __( 'Create Event', 'mobile-dj-manager' );
	} elseif ( 'Update' === $text ) {
		return __( 'Update Event', 'mobile-dj-manager' );
	} else {
		return $translation;
	}

} // mdjm_event_rename_publish_button
add_filter( 'gettext', 'mdjm_event_rename_publish_button', 10, 2 );

/**
 * Highlight unattended events rows within event post listings
 *
 * @since   1.3
 */
function mdjm_event_highlight_unattended_event_rows() {

	global $post;

	if ( ! isset( $post ) || 'mdjm-event' !== $post->post_type ) {
		return;
	}

	// Allow the colour to be filtered.
	$row_colour = apply_filters( 'mdjm_unattended_event_row_colour', '#FFEBE8' );

	?>
	<style>
	/* Color by post Status */
	.status-mdjm-unattended	{
		background: <?php echo esc_attr( $row_colour ); ?> !important;
	}
	</style>
	<?php

} // mdjm_event_highlight_unattended_event_rows
add_action( 'admin_footer', 'mdjm_event_highlight_unattended_event_rows' );

/**
 * Remove the default date filter from the edit post screen since we store event dates in a meta key.
 *
 * @since   1.3
 */
function mdjm_event_remove_date_filter() {

	if ( ! isset( $_GET['post_type'] ) || 'mdjm-event' !== $_GET['post_type'] ) { // phpcs:ignore WordPress.Security.NonceVerification
		return;
	}

	add_filter( 'months_dropdown_results', '__return_empty_array' );

} // mdjm_event_remove_date_filter
add_action( 'admin_head', 'mdjm_event_remove_date_filter' );

/**
 * Order posts.
 *
 * @since   1.3
 * @param   obj $query      The WP_Query object.
 * @return  void
 */
function mdjm_event_post_order( $query ) {

	if ( ! is_admin() || 'mdjm-event' !== $query->get( 'post_type' ) ) {
		return;
	}

	$orderby = '' === $query->get( 'orderby' ) ? mdjm_get_option( 'events_order_by', 'event_date' ) : $query->get( 'orderby' );
	$order   = '' === $query->get( 'order' ) ? mdjm_get_option( 'events_order', 'event_date' ) : $query->get( 'order' );

	switch ( $orderby ) {
		case 'ID':
			$query->set( 'orderby', 'ID' );
			$query->set( 'order', $order );
			break;

		case 'post_date':
			$query->set( 'orderby', 'post_date' );
			$query->set( 'order', $order );
			break;

		case 'event_date':
		default:
			$query->set( 'meta_key', '_mdjm_event_date' );
			$query->set( 'orderby', 'meta_value' );
			$query->set( 'order', $order );
			break;

		case 'title':
			$query->set( 'orderby', 'ID' );
			$query->set( 'order', $order );
			break;

		case 'value':
			$query->set( 'meta_key', '_mdjm_event_cost' );
			$query->set( 'orderby', 'meta_value_num' );
			$query->set( 'order', $order );
			break;
	}

} // mdjm_event_post_order
add_action( 'pre_get_posts', 'mdjm_event_post_order' );

/**
 * Hook into pre_get_posts and limit employees events if their permissions are not full.
 *
 * @since   1.0
 * @param   arr $query      The WP_Query.
 * @return  void
 */
function mdjm_limit_results_to_employee_events( $query ) {

	if ( ! is_admin() || 'mdjm-event' !== $query->get( 'post_type' ) || mdjm_employee_can( 'read_events_all' ) ) {
		return;
	}

	global $user_ID;

	$query->set(
		'meta_query',
		array(
			'relation' => 'AND',
			array(
				'relation' => 'OR',
				array(
					'key'     => '_mdjm_event_dj',
					'value'   => $user_ID,
					'compare' => '==',
				),
				array(
					'key'     => '_mdjm_event_employees',
					'value'   => sprintf( ':"%s";', $user_ID ),
					'compare' => 'LIKE',
				),
			),
		)
	);

} // mdjm_limit_results_to_employee_events
add_action( 'pre_get_posts', 'mdjm_limit_results_to_employee_events' );

/**
 * Hide inactive events from the 'all' events list.
 *
 * @since   1.0
 * @param   obj $query  The WP_Query.
 * @return  void
 */
function mdjm_hide_inactive_events( $query ) {

	if ( ! is_admin() || ! $query->is_main_query() || 'mdjm-event' !== $query->get( 'post_type' ) ) {
		return;
	}

	if ( ! mdjm_get_option( 'show_active_only', false ) ) {
		return;
	}

	if ( isset( $_GET['post_status'] ) && 'all' !== $_GET['post_status'] ) { // phpcs:ignore WordPress.Security.NonceVerification
		return;
	}

	$active_statuses   = mdjm_all_event_status_keys();
	$inactive_statuses = mdjm_inactive_event_status_keys();

	foreach ( $inactive_statuses as $inactive_status ) {
		if ( ( $key = array_search( $inactive_status, $active_statuses ) ) !== false ) {
			unset( $active_statuses[ $key ] );
		}
	}

	$active_events = mdjm_get_events(
		array(
			'post_status' => $active_statuses,
			'fields'      => 'ids',
			'number'      => -1,
		)
	);

	if ( $active_events ) {
		$query->set( 'post__in', $active_events );
	}

} // mdjm_hide_inactive_events
add_action( 'pre_get_posts', 'mdjm_hide_inactive_events' );

/**
 * Adjust the query when the events are filtered.
 *
 * @since   1.3
 * @param   arr $query      The WP_Query.
 * @return  void
 */
function mdjm_event_post_filtered( $query ) {

	global $pagenow;

	$post_type   = isset( $_GET['post_type'] ) ? sanitize_text_field( wp_unslash( $_GET['post_type'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification
	$post_status = isset( $_GET['post_status'] ) ? sanitize_text_field( wp_unslash( $_GET['post_status'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification

	if ( 'edit.php' !== $pagenow || 'mdjm-event' !== $post_type || ! is_admin() ) {
		return;
	}

	if ( ! isset( $_GET['filter_action'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification
		return;
	}

	// Filter by selected date.
	if ( ! empty( $_GET['mdjm_filter_date'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification

		// Create the date start and end range.
		$start = date( 'Y-m-d', strtotime( substr( sanitize_text_field( wp_unslash( $_GET['mdjm_filter_date'] ) ), 0, 4 ) . '-' . substr( sanitize_text_field( wp_unslash( $_GET['mdjm_filter_date'] ) ), -2 ) . '-01' ) ); // phpcs:ignore WordPress.Security.NonceVerification
		$end   = date( 'Y-m-t', strtotime( $start ) ); // phpcs:ignore WordPress.Security.NonceVerification

		$query->query_vars['meta_query'] = array(
			array(
				'key'     => '_mdjm_event_date',
				'value'   => array( $start, $end ),
				'compare' => 'BETWEEN',
			),
		);

	}

	// Filter by event type.
	if ( ! empty( $_GET['mdjm_filter_type'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification

		$type = isset( $_GET['mdjm_filter_type'] ) ? absint( $_GET['mdjm_filter_type'] ) : 0; // phpcs:ignore WordPress.Security.NonceVerification

		if ( 0 !== $type ) {
			$query->set(
				'tax_query',
				array(
					array(
						'taxonomy' => 'event-types',
						'field'    => 'term_id',
						'terms'    => $type,
					),
				)
			);
		}
	}

	// Filter by selected employee.
	if ( ! empty( $_GET['mdjm_filter_employee'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification

		$query->query_vars['meta_query'] = array(
			'relation' => 'OR',
			array(
				'key'   => '_mdjm_event_dj',
				'value' => sanitize_text_field( wp_unslash( $_GET['mdjm_filter_employee'] ) ), // phpcs:ignore WordPress.Security.NonceVerification
			),
			array(
				'key'     => '_mdjm_event_employees',
				'value'   => sprintf( ':"%s";', sanitize_text_field( wp_unslash( $_GET['mdjm_filter_employee'] ) ) ), // phpcs:ignore WordPress.Security.NonceVerification
				'compare' => 'LIKE',
			),
		);

	}

	// Filter by selected client.
	if ( ! empty( $_GET['mdjm_filter_client'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification

		$query->query_vars['meta_query'] = array(
			array(
				'key'   => '_mdjm_event_client',
				'value' => absint( wp_unslash( $_GET['mdjm_filter_client'] ) ), // phpcs:ignore WordPress.Security.NonceVerification
			),
		);

	}

	// Filter by selected venue.
	if ( ! empty( $_GET['mdjm_filter_venue'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification

		$query->query_vars['meta_query'] = array(
			array(
				'key'   => '_mdjm_event_venue_id',
				'value' => absint( wp_unslash( $_GET['mdjm_filter_venue'] ) ), // phpcs:ignore WordPress.Security.NonceVerification
			),
		);

	}

	if ( ! empty( $post_status ) ) {
		$query->set( 'post_status', $post_status );
	}

} // mdjm_event_post_filtered
add_filter( 'parse_query', 'mdjm_event_post_filtered' );

/**
 * Customise the event post query during a search so that clients and employees are included in results.
 *
 * @since   1.0
 * @param   arr $query      The WP_Query.
 * @return  void
 */
function mdjm_event_post_search( $query ) {
	global $pagenow;

	if ( ! is_admin() || 'mdjm-event' !== $query->get( 'post_type' ) || ! $query->is_search() || 'edit.php' !== $pagenow ) {
		return;
	}

	// If searching it's only useful if we include clients and employees.
	$users = new WP_User_Query(
		array(
			'search'         => sanitize_text_field( wp_unslash( empty( $_GET['s'] ) ) ), // phpcs:ignore WordPress.Security.NonceVerification
			'search_columns' => array(
				'user_login',
				'user_email',
				'user_nicename',
				'display_name',
			),
		)
	); // WP_User_Query.

	$user_results = $users->get_results();

	// Loop through WP_User_Query search looking for events where user is client or employee.
	if ( ! empty( $user_results ) ) {

		foreach ( $user_results as $user ) {

			$results = get_posts(
				array(
					'post_type'      => 'mdjm-event',
					'post_status'    => 'any',
					'posts_per_page' => -1,
					'meta_query'     => array(
						'relation' => 'OR',
						array(
							'key'   => '_mdjm_event_dj',
							'value' => $user->ID,
							'type'  => 'NUMERIC',
						),
						array(
							'key'   => '_mdjm_event_client',
							'value' => $user->ID,
							'type'  => 'NUMERIC',
						),
						array(
							'key'     => '_mdjm_event_employees',
							'value'   => sprintf( ':"%s";', $user->ID ),
							'compare' => 'LIKE',
						),
					),
				)
			); // get_posts.

			if ( ! empty( $results ) ) {

				foreach ( $results as $result ) {

					$events[] = $result->ID;

				}
			}
		}

		if ( ! empty( $events ) ) {

			$query->set( 'post__in', $events );
			$query->set( 'post_status', array( 'mdjm-unattended', 'mdjm-enquiry', 'mdjm-contract', 'mdjm-approved', 'mdjm-failed', 'mdjm-rejected', 'mdjm-completed' ) );

		}
	}

} // mdjm_event_post_search
add_action( 'pre_get_posts', 'mdjm_event_post_search' );

/**
 * Map the meta capabilities
 *
 * @since   1.3
 * @param   arr $caps       The users actual capabilities.
 * @param   str $cap        The capability name.
 * @param   int $user_id    The user ID.
 * @param   arr $args       Adds the context to the cap. Typically the object ID.
 */
function mdjm_event_map_meta_cap( $caps, $cap, $user_id, $args ) {

	// If editing, deleting, or reading an event, get the post and post type object.
	if ( 'edit_mdjm_event' === $cap || 'delete_mdjm_event' === $cap || 'read_mdjm_event' === $cap || 'publish_mdjm_event' === $cap ) {

		$post = get_post( $args[0] );

		if ( empty( $post ) ) {
			return $caps;
		}

		$post_type = get_post_type_object( $post->post_type );

		// Set an empty array for the caps.
		$caps = array();

	}

	// If editing a event, assign the required capability.
	if ( 'edit_mdjm_event' === $cap ) {

		if ( in_array( $user_id, mdjm_get_event_employees( $post->ID ) ) ) {
			$caps[] = $post_type->cap->edit_posts;
		} else {
			$caps[] = $post_type->cap->edit_others_posts;
		}
	}

	// If deleting a event, assign the required capability.
	elseif ( 'delete_mdjm_event' === $cap ) {

		if ( in_array( $user_id, mdjm_get_event_employees( $post->ID ) ) ) {
			$caps[] = $post_type->cap->delete_posts;
		} else {
			$caps[] = $post_type->cap->delete_others_posts;
		}
	}

	// If reading a private event, assign the required capability.
	elseif ( 'read_mdjm_event' === $cap ) {

		if ( 'private' !== $post->post_status ) {
			$caps[] = 'read';
		} elseif ( in_array( $user_id, mdjm_get_event_employees( $post->ID ) ) ) {
			$caps[] = 'read';
		} else {
			$caps[] = $post_type->cap->read_private_posts;
		}
	}

	// Return the capabilities required by the user.
	return $caps;

} // mdjm_event_map_meta_cap
add_filter( 'map_meta_cap', 'mdjm_event_map_meta_cap', 10, 4 );

/**
 * Save the meta data for the event
 *
 * @since   0.7
 * @param   int  $post_id        The current event post ID.
 * @param   obj  $post           The current event post object (WP_Post).
 * @param   bool $update         Whether this is an existing post being updated or not.
 *
 * @return  void
 */
function mdjm_save_event_post( $post_id, $post, $update ) {

	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}

	if ( 'trash' === $post->post_status ) {
		return;
	}

	if ( empty( $update ) ) {
		return;
	}

	// Permission Check.
	if ( ! mdjm_employee_can( 'manage_events' ) ) {
		MDJM()->debug->log_it( sprintf( 'PERMISSION ERROR: User %s is not allowed to edit events', get_current_user_id() ) );

		return;
	}

	// Remove the save post action to avoid loops.
	remove_action( 'save_post_mdjm-event', 'mdjm_save_event_post', 10, 3 );

	// Fire our pre-save hook.
	do_action( 'mdjm_pre_event_save', $post_id, $post, $update );

	$debug[] = 'Starting Event Save';

	// Get current meta data for the post so we can track changes within the journal.
	$current_meta = get_post_meta( $post_id );

	/**
	 * Get the Client ID and store it in the event data array.
	 * If a client has been selected from the dropdown, we simply use that ID.
	 * If adding a new client, call the method and use the returned user ID.
	 */
	$event_data['_mdjm_event_client'] = isset( $_POST['client_name'] ) && sanitize_text_field( wp_unslash( $_POST['client_name'] ) ) !== 'add_new' ? sanitize_text_field( wp_unslash( $_POST['client_name'] ) ) : mdjm_add_client(); // phpcs:ignore WordPress.Security.NonceVerification

	/**
	 * For new events we fire the 'mdjm_add_new_event' action
	 */
	if ( empty( $update ) ) {
		do_action( 'mdjm_create_new_event', $post );
	}

	/**
	 * If the client is flagged to have their password reset, set the flag.
	 * The flag will be checked and processed during the content tag filtering process.
	 */
	if ( ! empty( $_POST['mdjm_reset_pw'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification

		$debug[] = sprintf( 'Client %s flagged for password reset', $event_data['_mdjm_event_client'] );

		update_user_meta( $event_data['_mdjm_event_client'], 'mdjm_pass_action', true );
	}

	/**
	* Determine the Venue ID if an existing venue was selected.
	* Otherwise, determine if we're using the client's address or adding a manual venue address
	*/
	if ( isset( $_POST['venue_id'] ) && 'manual' !== $_POST['venue_id'] && 'client' !== $_POST['venue_id'] ) { // phpcs:ignore WordPress.Security.NonceVerification
		$event_data['_mdjm_event_venue_id'] = sanitize_text_field( wp_unslash( $_POST['venue_id'] ) ); // phpcs:ignore WordPress.Security.NonceVerification
	} elseif ( ! empty( $_POST['_mdjm_event_venue_id'] ) && 'client' === $_POST['_mdjm_event_venue_id'] ) { // phpcs:ignore WordPress.Security.NonceVerification
		$event_data['_mdjm_event_venue_id'] = 'client';
	} else {
		$event_data['_mdjm_event_venue_id'] = 'manual';
	}

	/**
	 * If the option was selected to save the venue, prepare the post and post meta data
	 * for the venue.
	 */
	if ( 'manual' === $_POST['venue_id'] && ! empty( $_POST['save_venue'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification

		foreach ( $_POST as $venue_key => $venue_value ) { // phpcs:ignore WordPress.Security.NonceVerification

			if ( substr( $venue_key, 0, 6 ) === 'venue_' ) {

				$venue_meta[ $venue_key ] = $venue_value;

				if ( 'venue_postcode' === $venue_key && ! empty( $venue_value ) ) {
					$venue_meta[ $venue_key ] = strtoupper( $venue_value );
				} elseif ( 'venue_email' === $venue_key && ! empty( $venue_value ) ) {
					$venue_meta[ $venue_key ] = sanitize_email( $venue_value );
				} else {
					$venue_meta[ $venue_key ] = sanitize_text_field( ucwords( $venue_value ) );
				}
			}
		}

		// Create the new venue.
		$event_data['_mdjm_event_venue_id'] = mdjm_add_venue( isset( $_POST['venue_name'] ) ? sanitize_text_field( wp_unslash( $_POST['venue_name'] ) ) : 'N/A', $venue_meta ); // phpcs:ignore WordPress.Security.NonceVerification

	}

	// The venue is set to manual or client for this event so store the values in event post meta data.
	else {
		// Manual venue address entry.
		if ( 'client' !== $_POST['venue_id'] ) { // phpcs:ignore WordPress.Security.NonceVerification

			$event_data['_mdjm_event_venue_name']     = ucwords( sanitize_text_field( wp_unslash( $_POST['venue_name'] ) ) ); // phpcs:ignore WordPress.Security.NonceVerification
			$event_data['_mdjm_event_venue_contact']  = isset( $_POST['venue_contact'] ) ? ucwords( sanitize_text_field( wp_unslash( $_POST['venue_contact'] ) ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification
			$event_data['_mdjm_event_venue_phone']    = isset( $_POST['venue_phone'] ) ? ucwords( sanitize_text_field( wp_unslash( $_POST['venue_phone'] ) ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification
			$event_data['_mdjm_event_venue_email']    = isset( $_POST['venue_email'] ) ? strtolower( sanitize_email( wp_unslash( $_POST['venue_email'] ) ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification
			$event_data['_mdjm_event_venue_address1'] = isset( $_POST['venue_address1'] ) ? ucwords( sanitize_text_field( wp_unslash( $_POST['venue_address1'] ) ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification
			$event_data['_mdjm_event_venue_address2'] = isset( $_POST['venue_address2'] ) ? ucwords( sanitize_text_field( wp_unslash( $_POST['venue_address2'] ) ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification
			$event_data['_mdjm_event_venue_town']     = isset( $_POST['venue_town'] ) ? ucwords( sanitize_text_field( wp_unslash( $_POST['venue_town'] ) ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification
			$event_data['_mdjm_event_venue_county']   = isset( $_POST['venue_county'] ) ? ucwords( sanitize_text_field( wp_unslash( $_POST['venue_county'] ) ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification
			$event_data['_mdjm_event_venue_postcode'] = isset( $_POST['venue_postcode'] ) ? strtoupper( sanitize_text_field( wp_unslash( $_POST['venue_postcode'] ) ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification

		} else { // Using clients address.

			$client_data = get_userdata( $event_data['_mdjm_event_client'] );

			$event_data['_mdjm_event_venue_name'] = __( 'Client Address', 'mobile-dj-manager' );

			$event_data['_mdjm_event_venue_contact'] = sprintf(
				'%s %s',
				! empty( $client_data->first_name ) ? sanitize_text_field( $client_data->first_name ) : '',
				! empty( $client_data->last_name ) ? sanitize_text_field( $client_data->last_name ) : ''
			);

			$event_data['_mdjm_event_venue_phone']    = ! empty( $client_data->phone1 ) ? $client_data->phone1 : '';
			$event_data['_mdjm_event_venue_email']    = ! empty( $client_data->user_email ) ? $client_data->user_email : '';
			$event_data['_mdjm_event_venue_address1'] = ! empty( $client_data->address1 ) ? $client_data->address1 : '';
			$event_data['_mdjm_event_venue_address2'] = ! empty( $client_data->address2 ) ? $client_data->address2 : '';
			$event_data['_mdjm_event_venue_town']     = ! empty( $client_data->town ) ? $client_data->town : '';
			$event_data['_mdjm_event_venue_county']   = ! empty( $client_data->county ) ? $client_data->county : '';
			$event_data['_mdjm_event_venue_postcode'] = ! empty( $client_data->postcode ) ? $client_data->postcode : '';

		}
	}

	/**
	 * Travel data
	 */
	$travel_fields = mdjm_get_event_travel_fields();

	foreach ( $travel_fields as $travel_field ) {
		$field = 'travel_' . $travel_field;

		$travel_data[ $travel_field ] = ! empty( $_POST[ $field ] ) ? sanitize_text_field( wp_unslash( $_POST[ $field ] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification

		if ( 'cost' === $travel_field && ! empty( $_POST[ $field ] ) ) { // phpcs:ignore WordPress.Security.NonceVerification
			$travel_data[ $travel_field ] = mdjm_sanitize_amount( sanitize_text_field( wp_unslash( $_POST[ $field ] ) ) ); // phpcs:ignore WordPress.Security.NonceVerification
		}
	}

	$event_data['_mdjm_event_travel_data'] = $travel_data;

	/**
	 * Prepare the remaining event meta data.
	 */
	$event_data['_mdjm_event_last_updated_by'] = get_current_user_id();
	if ( ! get_post_meta( $post_id, '_mdjm_event_tasks', true ) ) {
		$event_data['_mdjm_event_tasks'] = array();
	}

	/**
	 * Event name.
	 * If no name is defined, use the event type.
	 * Allow filtering of the event name with the `mdjm_event_name` filter.
	 */
	if ( empty( $_POST['_mdjm_event_name'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification
		$_POST['_mdjm_event_name'] = get_term( sanitize_text_field( wp_unslash( empty( $_POST['mdjm_event_type'] ) ) ), 'event-types' )->name; // phpcs:ignore WordPress.Security.NonceVerification
	}

	$_POST['_mdjm_event_name'] = apply_filters( 'mdjm_event_name', sanitize_text_field( wp_unslash( $_POST['_mdjm_event_name'] ) ), $post_id ); // phpcs:ignore WordPress.Security.NonceVerification

	// Generate the playlist reference for guest access.
	if ( empty( $update ) || empty( $current_meta['_mdjm_event_playlist_access'][0] ) ) {
		$event_data['_mdjm_event_playlist_access'] = mdjm_generate_playlist_guest_code();
	}

	// Set whether or not the playlist is enabled for the event.
	$event_data['_mdjm_event_playlist'] = ! empty( $_POST['enable_playlist'] ) ? sanitize_text_field( wp_unslash( $_POST['enable_playlist'] ) ) : 'N'; // phpcs:ignore WordPress.Security.NonceVerification

	/**
	 * All the remaining custom meta fields are prefixed with '_mdjm_event_'.
	 * Loop through all $_POST data and put all event meta fields into the $event_data array
	 */
	foreach ( $_POST as $key => $value ) { // phpcs:ignore WordPress.Security.NonceVerification

		if ( substr( $key, 0, 12 ) === '_mdjm_event_' ) {
			$cost_keys = array(
				'_mdjm_event_dj_wage',
				'_mdjm_event_package_cost',
				'_mdjm_event_addons_cost',
				'_mdjm_event_travel_cost',
				'_mdjm_event_additional_cost',
				'_mdjm_event_discount',
				'_mdjm_event_deposit',
				'_mdjm_event_cost',
			);
			if ( in_array( $key, $cost_keys ) ) {
				$value = mdjm_sanitize_amount( $value );
			}

			$event_data[ $key ] = $value;

		}
	}

	/**
	 * We store all times in H:i:s but the user may prefer a different format so we
	 * determine their time format setting and adjust to H:i:s for saving.
	 */
	if ( mdjm_get_option( 'time_format', 'H:i' ) === 'H:i' ) { // 24 Hr

		$event_data['_mdjm_event_start']        = date(
			'H:i:s',
			strtotime( // phpcs:ignore WordPress.Security.NonceVerification
				sanitize_text_field( wp_unslash( $_POST['event_start_hr'] ) ) . ':' . sanitize_text_field( wp_unslash( $_POST['event_start_min'] ) ) // phpcs:ignore WordPress.Security.NonceVerification
			)
		); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotValidated
		$event_data['_mdjm_event_finish']       = date(
			'H:i:s',
			strtotime( // phpcs:ignore WordPress.Security.NonceVerification
				sanitize_text_field( wp_unslash( $_POST['event_finish_hr'] ) ) . ':' . sanitize_text_field( wp_unslash( $_POST['event_finish_min'] ) ) // phpcs:ignore WordPress.Security.NonceVerification
			)
		); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotValidated
		$event_data['_mdjm_event_djsetup_time'] = date(
			'H:i:s',
			strtotime( // phpcs:ignore WordPress.Security.NonceVerification
				sanitize_text_field( wp_unslash( $_POST['dj_setup_hr'] ) ) . ':' . sanitize_text_field( wp_unslash( $_POST['dj_setup_min'] ) ) // phpcs:ignore WordPress.Security.NonceVerification
			)
		); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotValidated
	} else { // 12 hr
		$event_data['_mdjm_event_start']        = date(
			'H:i:s',
			strtotime( // phpcs:ignore WordPress.Security.NonceVerification
				sanitize_text_field( wp_unslash( $_POST['event_start_hr'] ) ) . ':' . sanitize_text_field( wp_unslash( $_POST['event_start_min'] ) ) . sanitize_text_field( wp_unslash( $_POST['event_start_period'] ) ) // phpcs:ignore WordPress.Security.NonceVerification
			)
		); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotValidated
		$event_data['_mdjm_event_finish']       = date(
			'H:i:s',
			strtotime( // phpcs:ignore WordPress.Security.NonceVerification
				sanitize_text_field( wp_unslash( $_POST['event_finish_hr'] ) ) . ':' . sanitize_text_field( wp_unslash( $_POST['event_finish_min'] ) ) . sanitize_text_field( wp_unslash( $_POST['event_finish_period'] ) ) // phpcs:ignore WordPress.Security.NonceVerification
			)
		); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotValidated
		$event_data['_mdjm_event_djsetup_time'] = date(
			'H:i:s',
			strtotime( // phpcs:ignore WordPress.Security.NonceVerification
				sanitize_text_field( wp_unslash( $_POST['dj_setup_hr'] ) ) . ':' . sanitize_text_field( wp_unslash( $_POST['dj_setup_min'] ) ) . sanitize_text_field( wp_unslash( $_POST['dj_setup_period'] ) ) // phpcs:ignore WordPress.Security.NonceVerification
			)
		); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotValidated
	}

	if ( empty( $_POST['_mdjm_event_djsetup'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification
		$event_data['_mdjm_event_djsetup'] = sanitize_text_field( wp_unslash( $_POST['_mdjm_event_date'] ) ); // phpcs:ignore WordPress.Security.NonceVerification
	}

	/**
	 * Set the event end date.
	 * If a value is set from the field, use it otherwise determine fom start/finish time
	 * If the finish time is less than the start time, assume following day.
	 */
	if ( empty( $event_data['_mdjm_event_end_date'] ) ) {
		if ( date( 'H', strtotime( $event_data['_mdjm_event_finish'] ) ) > date( 'H', strtotime( $event_data['_mdjm_event_start'] ) ) ) {
			$event_data['_mdjm_event_end_date'] = sanitize_text_field( wp_unslash( $_POST['_mdjm_event_date'] ) ); // phpcs:ignore WordPress.Security.NonceVerification
		} else { // End date is following day.
			$event_data['_mdjm_event_end_date'] = date( 'Y-m-d', strtotime( '+1 day', strtotime( sanitize_text_field( wp_unslash( $_POST['_mdjm_event_date'] ) ) ) ) ); // phpcs:ignore WordPress.Security.NonceVerification
		}
	}

	/**
	 * Determine the state of the Deposit & Balance payments.
	 */
	$event_data['_mdjm_event_deposit_status'] = ! empty( $_POST['deposit_paid'] ) ? sanitize_text_field( wp_unslash( $_POST['deposit_paid'] ) ) : 'Due'; // phpcs:ignore WordPress.Security.NonceVerification
	$event_data['_mdjm_event_balance_status'] = ! empty( $_POST['balance_paid'] ) ? sanitize_text_field( wp_unslash( $_POST['balance_paid'] ) ) : 'Due'; // phpcs:ignore WordPress.Security.NonceVerification

	$deposit_payment = ( 'Paid' === $event_data['_mdjm_event_deposit_status'] && 'Paid' !== $current_meta['_mdjm_event_deposit_status'][0] ) ? true : false; // phpcs:ignore WordPress.Security.NonceVerification
	$balance_payment = ( 'Paid' === $event_data['_mdjm_event_balance_status'] && 'Paid' !== $current_meta['_mdjm_event_balance_status'][0] ) ? true : false; // phpcs:ignore WordPress.Security.NonceVerification

	// Add-Ons.
	if ( mdjm_packages_enabled() ) {
		$event_data['_mdjm_event_addons'] = ! empty( $_POST['event_addons'] ) ? array_map( 'sanitize_text_field', wp_unslash( $_POST['event_addons'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification
	}

	// Assign the event type.
	$existing_event_type = wp_get_object_terms( $post_id, 'event-types' );

	mdjm_set_event_type( $post_id, (int) sanitize_text_field( wp_unslash( $_POST['mdjm_event_type'] ) ) ); // phpcs:ignore WordPress.Security.NonceVerification

	// Assign the enquiry source.
	mdjm_set_enquiry_source( $post_id, (int) sanitize_text_field( wp_unslash( $_POST['mdjm_enquiry_source'] ) ) ); // phpcs:ignore WordPress.Security.NonceVerification

	/**
	 * Update the event post meta data
	 */
	$debug[] = 'Beginning Meta Updates';

	mdjm_update_event_meta( $post_id, $event_data );

	$debug[] = 'Meta Updates Completed';

	if ( true === $deposit_payment || true === $balance_payment ) {

		if ( true === $balance_payment ) {
			unset( $event_data['_mdjm_event_balance_status'] );
			unset( $event_data['_mdjm_event_deposit_status'] );
			mdjm_mark_event_balance_paid( $post_id );
		} else {
			unset( $event_data['_mdjm_event_deposit_status'] );
			mdjm_mark_event_deposit_paid( $post_id );
		}
	}

	// Set the event status & initiate tasks based on the status.
	if ( sanitize_text_field( wp_unslash( $_POST['original_post_status'] ) ) !== sanitize_text_field( wp_unslash( $_POST['mdjm_event_status'] ) ) ) { // phpcs:ignore WordPress.Security.NonceVerification

		mdjm_update_event_status(
			$post_id,
			sanitize_text_field( wp_unslash( $_POST['mdjm_event_status'] ) ), // phpcs:ignore WordPress.Security.NonceVerification
			sanitize_text_field( wp_unslash( $_POST['original_post_status'] ) ), // phpcs:ignore WordPress.Security.NonceVerification
			array(
				'client_notices' => empty( $_POST['mdjm_block_emails'] ) ? true : false, // phpcs:ignore WordPress.Security.NonceVerification
				'email_template' => ! empty( $_POST['mdjm_email_template'] ) ? sanitize_text_field( wp_unslash( $_POST['mdjm_email_template'] ) ) : false, // phpcs:ignore WordPress.Security.NonceVerification
				'quote_template' => ! empty( $_POST['mdjm_online_quote'] ) ? sanitize_text_field( wp_unslash( $_POST['mdjm_online_quote'] ) ) : false, // phpcs:ignore WordPress.Security.NonceVerification
			)
		);

	} else { // Event status is un-changed so just log the changes to the journal.

		mdjm_add_journal(
			array(
				'user_id'         => get_current_user_id(),
				'event_id'        => $post_id,
				'comment_content' => sprintf(
					'%s %s via Admin',
					mdjm_get_label_singular(),
					empty( $update ) ? 'created' : 'updated'
				),
			),
			array(
				'type'       => 'update-event',
				'visibility' => '2',
			)
		);

	}

	// Fire the save event hook.
	do_action( 'mdjm_save_event', $post, sanitize_text_field( wp_unslash( $_POST['mdjm_event_status'] ) ) ); // phpcs:ignore WordPress.Security.NonceVerification

	// Fire our post save hook.
	do_action( 'mdjm_after_event_save', $post_id, $post, $update );

	// Re-add the save post action to avoid loops.
	add_action( 'save_post_mdjm-event', 'mdjm_save_event_post', 10, 3 );

	$debug[] = sprintf( 'Completed Event Save for event %s', $post_id );

	if ( ! empty( $debug ) && MDJM_DEBUG === true ) {

		$true = true;

		foreach ( $debug as $log ) {
			MDJM()->debug->log_it( $log, $true );
			$true = false;
		}
	}

} // mdjm_save_event_post
add_action( 'save_post_mdjm-event', 'mdjm_save_event_post', 10, 3 );

/**
 * Customise the messages associated with managing event posts
 *
 * @since   1.3
 * @param   arr $messages   The current messages.
 * @return  arr     $messages   Filtered messages
 */
function mdjm_event_post_messages( $messages ) {

	global $post;

	if ( 'mdjm-event' !== $post->post_type ) {
		return $messages;
	}

	$url1 = '<a href="' . admin_url( 'edit.php?post_type=mdjm-event' ) . '">';
	$url2 = mdjm_get_label_singular();
	$url3 = mdjm_get_label_plural();
	$url4 = '</a>';

	$messages['mdjm-event'] = array(
		0 => '', // Unused. Messages start at index 1.
		/* translators: all translate to URLs */
		1 => sprintf( __( '%2$s updated. %1$s%3$s List%4$s.', 'mobile-dj-manager' ), $url1, $url2, $url3, $url4 ),
		/* translators: all translate to URLs */
		4 => sprintf( __( '%2$s updated. %1$s%3$s List%4$s.', 'mobile-dj-manager' ), $url1, $url2, $url3, $url4 ),
		/* translators: all translate to URLs */
		6 => sprintf( __( '%2$s created. %1$s%3$s List%4$s.', 'mobile-dj-manager' ), $url1, $url2, $url3, $url4 ),
		/* translators: all translate to URLs */
		7 => sprintf( __( '%2$s saved. %1$s%3$s List%4$s.', 'mobile-dj-manager' ), $url1, $url2, $url3, $url4 ),
		/* translators: all translate to URLs */
		8 => sprintf( __( '%2$s submitted. %1$s%3$s List%4$s.', 'mobile-dj-manager' ), $url1, $url2, $url3, $url4 ),
	);

	return apply_filters( 'mdjm_event_post_messages', $messages );

} // mdjm_event_post_messages
add_filter( 'post_updated_messages', 'mdjm_event_post_messages' );
