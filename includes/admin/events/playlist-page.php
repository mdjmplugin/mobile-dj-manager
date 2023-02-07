<?php
/**
 * @author: Mike Howard, Jack Mawhinney, Dan Porter
 *
 * Contains all admin playlist functions
 *
 * @package     MDJM
 * @subpackage  Admin/Events
 * @since       1.3
 */

/**
 * Display the event playlist page.
 *
 * @since   1.3
 * @param
 * @return  str     The event playlist page content.
 */
function mdjm_display_event_playlist_page() {

	if ( ! isset( $_GET['event_id'] ) ) {
		wp_die(
			'<h1>' . esc_html__( 'Event not found', 'mobile-dj-manager' ) . '</h1>' .
				'<p>' . esc_html__( 'We are unable to find a playlist for this event.', 'mobile-dj-manager' ) . '</p>',
			404
		);
	}

	if ( ! mdjm_employee_can( 'read_events' ) && mdjm_employee_working_event( wp_unslash( $_GET['event_id'] ) ) ) {
		wp_die(
			'<h1>' . esc_html__( 'Cheatin&#8217; uh?', 'mobile-dj-manager' ) . '</h1>' .
			'<p>' . esc_html__( 'You do not have permission to view this playlist.', 'mobile-dj-manager' ) . '</p>',
			403
		);
	}

	if ( ! class_exists( 'MDJM_PlayList_Table' ) ) {
		require_once MDJM_PLUGIN_DIR . '/includes/admin/events/class-mdjm-playlist-table.php';
	}

	$playlist_obj = new MDJM_PlayList_Table();

	?>
	<div class="wrap">
		<h1><?php printf( esc_html__( 'Event Details for %1$s %2$s', 'mobile-dj-manager' ), esc_html( mdjm_get_label_singular() ), esc_html( mdjm_get_event_contract_id( absint( wp_unslash( $_GET['event_id'] ) ) ) ) ); ?></h1>

		<form method="post">
			<?php
			$playlist_obj->prepare_items();
			$playlist_obj->display_header();

			if ( count( $playlist_obj->items ) >= 0 ) {
				$playlist_obj->views();
			}

			$playlist_obj->display();
			$playlist_obj->entry_form();
			?>
		</form>
		<br class="clear">
	</div>
	<?php
} // mdjm_display_event_playlist_page

/**
 * Format the playlist results for emailing/printing.
 *
 * @since   1.3
 * @param   int  $event_id       The event ID to retrieve the playlist for.
 * @param   str  $orderby        Which field to order the playlist entries by.
 * @param   str  $order          Order ASC or DESC.
 * @param   int  $repeat_headers Repeat the table headers after this many rows.
 * @param   bool $hide_empty     If displaying by category do we hide empty categories?
 * @return  str     $results        Output of playlist entries.
 */
function mdjm_format_playlist_content( $event_id, $orderby = 'category', $order = 'ASC', $hide_empty = true, $repeat_headers = 0 ) {
	global $current_user;

	$mdjm_event = mdjm_get_event( $event_id );

	// Obtain results ordered by category
	if ( $orderby == 'category' ) {

		$playlist = mdjm_get_playlist_by_category( $event_id, array( 'hide_empty' => $hide_empty ) );

		if ( $playlist ) {

			foreach ( $playlist as $cat => $entries ) {

				foreach ( $entries as $entry ) {

					$entry_data = mdjm_get_playlist_entry_data( $entry->ID );

					$results[] = array(
						'ID'       => $entry->ID,
						'event'    => $event_id,
						'artist'   => stripslashes( $entry_data['artist'] ),
						'song'     => stripslashes( $entry_data['song'] ),
						'added_by' => stripslashes( $entry_data['added_by'] ),
						'category' => $cat,
						'notes'    => stripslashes( $entry_data['djnotes'] ),
						'date'     => mdjm_format_short_date( $entry->post_date ),
					);

				}
			}
		}
	}
	// Obtain results ordered by another field.
	else {

		$args = array(
			'orderby'  => $orderby == 'date' ? 'post_date' : 'meta_value',
			'order'    => $order,
			'meta_key' => $orderby == 'date' ? '' : '_mdjm_playlist_entry_' . $orderby,
		);

		$entries = mdjm_get_playlist_entries( $event_id, $args );

		if ( $entries ) {
			foreach ( $entries as $entry ) {
				$entry_data = mdjm_get_playlist_entry_data( $entry->ID );

				$categories = wp_get_object_terms( $entry->ID, 'playlist-category' );

				if ( ! empty( $categories ) ) {
					$category = $categories[0]->name;
				}

				$results[] = array(
					'ID'       => $entry->ID,
					'event'    => $event_id,
					'artist'   => stripslashes( $entry_data['artist'] ),
					'song'     => stripslashes( $entry_data['song'] ),
					'added_by' => stripslashes( $entry_data['added_by'] ),
					'category' => ! empty( $category ) ? $category : '',
					'notes'    => stripslashes( $entry_data['djnotes'] ),
					'date'     => mdjm_format_short_date( $entry->post_date ),
				);
			}
		}
	}

	// Build out the formatted display
	if ( ! empty( $results ) ) {

		$i = 0;

		$output = '<h1>' . '<u>' . __( 'Event Details for ', 'mobile-dj-manager' ) . mdjm_content_tag_event_name( $mdjm_event->ID ) . '</u>' . '</h1>';

		// Show Core Details about the event

		$output .= '<div id="h2">' . __( 'Core Details', 'mobile-dj-manager' ) . '</div>';

		$output .= '<p><b>' . __( 'Client Name', 'mobile-dj-manager' ) . ': ' . '</b>' . mdjm_get_client_display_name( $mdjm_event->client ) . '<br />' . '</p>';

		$output .= '<p><b>' . __( 'Client Phone Number', 'mobile-dj-manager' ) . ': ' . '</b>' . mdjm_get_client_phone( $mdjm_event->client ) . '<br />' . '</p>';

		$output .= '<p><b>' . __( 'Event Date', 'mobile-dj-manager' ) . ': ' . '</b>' . mdjm_get_event_long_date( $mdjm_event->ID ) . '<br />' . '</p>';

		$output .= '<p><b>' . __( 'Event Type', 'mobile-dj-manager' ) . ': ' . '</b>' . mdjm_get_event_type( $mdjm_event->ID ) . '<br />' . '</p>';

		$output .= '<p><b>' . __( 'Event Timings', 'mobile-dj-manager' ) . ': ' . '</b>' . mdjm_content_tag_start_time( $mdjm_event->ID ) . ' - ' . mdjm_content_tag_end_time( $mdjm_event->ID ) . '<br />' . '</p>';

		$output .= '<p><b>' . __( 'Notes for the Artiste', 'mobile-dj-manager' ) . ': ' . '</b>' . mdjm_content_tag_dj_notes( $mdjm_event->ID ) . '<br />' . '</p>';

		$output .= '<p><b>' . __( 'Client Notes', 'mobile-dj-manager' ) . ': ' . '</b>' . mdjm_content_tag_client_notes( $mdjm_event->ID ) . '<br />' . '</p>';

		// Shows the Venue Details

		$output .= '<div id="h2">' . __( 'Venue Details', 'mobile-dj-manager' ) . '</div>';

		$output .= '<p><b>' . __( 'Venue Name', 'mobile-dj-manager' ) . ': ' . '</b>' . mdjm_content_tag_venue( $mdjm_event->ID ) . '<br />' . '</p>';

		$output .= '<p><b>' . __( 'Venue Address', 'mobile-dj-manager' ) . ': ' . '</b><br />' . mdjm_content_tag_venue_full_address( $mdjm_event->ID ) . '<br />' . '</p>';

		$output .= '<p><b>' . __( 'Venue Details', 'mobile-dj-manager' ) . ': ' . '</b><br />' . mdjm_content_tag_venue_details( $mdjm_event->ID ) . '<br />' . '</p>';

		$output .= '<p><b>' . __( 'Venue Notes', 'mobile-dj-manager' ) . ': ' . '</b>' . mdjm_content_tag_venue_notes( $mdjm_event->ID ) . '<br />' . '</p>';

		// Shows the Packages and Addons

		$output .= '<div id="h2">' . __( 'Packages & Add-Ons', 'mobile-dj-manager' ) . '</div>';

		$output .= '<p><div id="h3">' . __( 'Package selected', 'mobile-dj-manager' ) . '</div></p>';

		$output .= '<p>' . mdjm_content_tag_event_package( $mdjm_event->ID ) . '</p>';

		$output .= '<p><div id="h3">' . __( 'Add-ons Selected', 'mobile-dj-manager' ) . '</div></p>';

		$output .= '<p>' . mdjm_content_tag_event_addons( $mdjm_event->ID ) . '</p>';

		// Shows the Playlist

		$output .= '<div id="h2">' . __( 'Playlist', 'mobile-dj-manager' ) . '</div>';
		$output .= '<p>' . __( 'Songs in Playlist', 'mobile-dj-manager' ) . ': ' . count( $results ) . '<br />' . "\n" . '</p>';

		$headers = '<tr style="height: 30px">' . "\n" .
						'<td style="width: 15%"><strong>' . __( 'Song', 'mobile-dj-manager' ) . '</strong></td>' . "\n" .
						'<td style="width: 15%"><strong>' . __( 'Artist', 'mobile-dj-manager' ) . '</strong></td>' . "\n" .
						'<td style="width: 15%"><strong>' . __( 'Category', 'mobile-dj-manager' ) . '</strong></td>' . "\n" .
						'<td style="width: 30%"><strong>' . __( 'Notes', 'mobile-dj-manager' ) . '</strong></td>' . "\n" .
						'<td style="width: 15%"><strong>' . __( 'Added By', 'mobile-dj-manager' ) . '</strong></td>' . "\n" .
					'</tr>' . "\n";

		$output .= '<table width="100%" border="0" cellpadding="0" cellspacing="0">' . "\n";

		$output .= $headers;

		foreach ( $results as $result ) {
			if ( $repeat_headers = 0 && $i == $repeat_headers ) {
				$output .= '<tr>' . "\n" .
								'<td colspan="5">&nbsp;</td>' . "\n" .
							'</tr>' . "\n" .
							$headers;
				$i       = 0;
			}

			if ( is_numeric( $result['added_by'] ) ) {
				$user = get_userdata( $result['added_by'] );

				$name = $user->display_name;
			} else {
				$name = $result['added_by'];
			}

			$output .= '<tr>' . "\n" .
							'<td>' . stripslashes( $result['song'] ) . '</td>' . "\n" .
							'<td>' . stripslashes( $result['artist'] ) . '</td>' . "\n" .
							'<td>' . stripslashes( $result['category'] ) . '</td>' . "\n" .
							'<td>' . stripslashes( $result['notes'] ) . '</td>' . "\n" .
							'<td>' . stripslashes( $name ) . '</td>' . "\n" .
						'</tr>' . "\n";

			$i++;
		}

		$output .= '</table>' . "\n";

	} else {
		$output = '<h1>' . '<u>' . __( 'Event Details for ', 'mobile-dj-manager' ) . mdjm_content_tag_event_name( $mdjm_event->ID ) . '</u>' . '</h1>';

		// Copyright Jack Mawhinney & Dan Porter

		$output .= '<div id="h2">' . __( 'Core Details', 'mobile-dj-manager' ) . '</div>';

		$output .= '<p><b>' . __( 'Client Name', 'mobile-dj-manager' ) . ': ' . '</b>' . mdjm_get_client_display_name( $mdjm_event->client ) . '<br />' . '</p>';

		$output .= '<p><b>' . __( 'Client Phone Number', 'mobile-dj-manager' ) . ': ' . '</b>' . mdjm_get_client_phone( $mdjm_event->client ) . '<br />' . '</p>';

		$output .= '<p><b>' . __( 'Event Date', 'mobile-dj-manager' ) . ': ' . '</b>' . mdjm_get_event_long_date( $mdjm_event->ID ) . '<br />' . '</p>';

		$output .= '<p><b>' . __( 'Event Type', 'mobile-dj-manager' ) . ': ' . '</b>' . mdjm_get_event_type( $mdjm_event->ID ) . '<br />' . '</p>';

		$output .= '<p><b>' . __( 'Event Timings', 'mobile-dj-manager' ) . ': ' . '</b>' . mdjm_content_tag_start_time( $mdjm_event->ID ) . ' - ' . mdjm_content_tag_end_time( $mdjm_event->ID ) . '<br />' . '</p>';

		$output .= '<p><b>' . __( 'Notes for the Artiste', 'mobile-dj-manager' ) . ': ' . '</b>' . mdjm_content_tag_dj_notes( $mdjm_event->ID ) . '<br />' . '</p>';

		$output .= '<p><b>' . __( 'Client Notes', 'mobile-dj-manager' ) . ': ' . '</b>' . mdjm_content_tag_client_notes( $mdjm_event->ID ) . '<br />' . '</p>';

		// Shows the Venue Details. Copyright Jack Mawhinney & Dan Porter

		$output .= '<div id="h2">' . __( 'Venue Details', 'mobile-dj-manager' ) . '</div>';

		$output .= '<p><b>' . __( 'Venue Name', 'mobile-dj-manager' ) . ': ' . '</b>' . mdjm_content_tag_venue( $mdjm_event->ID ) . '<br />' . '</p>';

		$output .= '<p><b>' . __( 'Venue Address', 'mobile-dj-manager' ) . ': ' . '</b><br />' . mdjm_content_tag_venue_full_address( $mdjm_event->ID ) . '<br />' . '</p>';

		$output .= '<p><b>' . __( 'Venue Details', 'mobile-dj-manager' ) . ': ' . '</b><br />' . mdjm_content_tag_venue_details( $mdjm_event->ID ) . '<br />' . '</p>';

		$output .= '<p><b>' . __( 'Venue Notes', 'mobile-dj-manager' ) . ': ' . '</b>' . mdjm_content_tag_venue_notes( $mdjm_event->ID ) . '<br />' . '</p>';

		// Shows the Packages and Addons. Copyright Jack Mawhinney & Dan Porter

		$output .= '<div id="h2">' . __( 'Packages & Add-Ons', 'mobile-dj-manager' ) . '</div>';

		$output .= '<p><div id="h3">' . __( 'Package selected', 'mobile-dj-manager' ) . '</div></p>';

		$output .= '<p>' . mdjm_content_tag_event_package( $mdjm_event->ID ) . '</p>';

		$output .= '<p><div id="h3">' . __( 'Add-ons Selected', 'mobile-dj-manager' ) . '</div></p>';

		$output .= '<p>' . mdjm_content_tag_event_addons( $mdjm_event->ID ) . '</p>';

		$output .= '<div id="h2">' . __( 'Playlist', 'mobile-dj-manager' ) . '</div>';
		$output .= '<p>' . __( 'There aren&#39t any songs in the playlist :(', 'mobile-dj-manager' ) . '</p>' . "\n";

	}

	return $output;
} // mdjm_format_playlist_content
