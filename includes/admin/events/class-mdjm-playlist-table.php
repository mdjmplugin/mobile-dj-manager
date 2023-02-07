<?php
/**
 * @author: Mike Howard, Jack Mawhinney, Dan Porter
 *
 * MDJM Playlist Table
 *
 * Displays an events playlist entries within the Admin UI
 *
 * @package     MDJM
 * @subpackage  Admin/Events/Playlist
 * @copyright   Copyright (c) 2016, Mike Howard
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.3
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
	require_once ABSPATH . $plugin_dir . 'includes/admin/events/playlist-page.php';
}

/**
 * MDJM_Playlist_Table Class
 *
 * @since   1.3
 */
class MDJM_PlayList_Table extends WP_List_Table {

	public function __construct() {

		parent::__construct(
			array(
				'singular' => 'mdjm_playlist_entry', // Singular label.
				'plural'   => 'mdjm_playlist_entries', // Plural label, also this well be one of the table css class.
				'ajax'     => false, // We won't support Ajax for this table.
			)
		);

	}

	/**
	 * Retrieve the entries for this playlist.
	 *
	 * @since   1.3
	 * @param   int $per_page       The number of items to display per page.
	 * @param   int $page_num       The current page number.
	 * @return  arr     The array of data to display within the table.
	 */
	private function get_entries( $per_page = -1, $page_num = 1 ) {

		if ( ! isset( $_GET['event_id'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification
			return false;
		}

		$result = array();

		$mdjm_event = new MDJM_Event( absint( wp_unslash( $_GET['event_id'] ) ) ); // phpcs:ignore WordPress.Security.NonceVerification

		$orderby = isset( $_GET['orderby'] ) ? sanitize_text_field( wp_unslash( $_GET['orderby'] ) ) : 'category'; // phpcs:ignore WordPress.Security.NonceVerification
		$order   = isset( $_GET['order'] ) ? sanitize_text_field( wp_unslash( $_GET['order'] ) ) : 'ASC'; // phpcs:ignore WordPress.Security.NonceVerification

		if ( 'category' === $orderby ) { // phpcs:ignore WordPress.Security.NonceVerification

			$args = array(
				'orderby'    => 'name',
				'order'      => $order,
				'hide_empty' => true,
			);

			$playlist = mdjm_get_playlist_by_category( $mdjm_event->ID, $args );

			if ( $playlist ) {

				foreach ( $playlist as $cat => $entries ) {

					foreach ( $entries as $entry ) {

						$entry_data = mdjm_get_playlist_entry_data( $entry->ID );

						$result[] = array(
							'ID'       => $entry->ID,
							'event'    => $mdjm_event->ID,
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
		} else {
			$args = array(
				'orderby'  => 'date' === $orderby ? 'post_date' : 'meta_value',
				'order'    => $order,
				'meta_key' => 'date' === $orderby ? '' : '_mdjm_playlist_entry_' . $orderby,
			);

			$entries = mdjm_get_playlist_entries( $mdjm_event->ID, $args );

			if ( $entries ) {
				foreach ( $entries as $entry ) {
					$entry_data = mdjm_get_playlist_entry_data( $entry->ID );

					$categories = wp_get_object_terms( $entry->ID, 'playlist-category' );

					if ( ! empty( $categories ) ) {
						$category = $categories[0]->name;
					}

					$result[] = array(
						'ID'       => $entry->ID,
						'event'    => $mdjm_event->ID,
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

		return apply_filters( 'mdjm_list_event_playlist', $result, $mdjm_event->ID );

	} // get_entries

	/**
	 * Delete entries from the playlist.
	 *
	 * @since   1.3
	 * @param   int $id     The playlist entry ID.
	 */
	public function delete_entry( $id ) {
		mdjm_remove_stored_playlist_entry( $id );
	} // delete_entry

	/**
	 * Count entries in the playlist.
	 *
	 * @since   1.3
	 * @param   int $event_id   The event ID.
	 */
	public function count_entries( $event_id ) {
		return mdjm_count_playlist_entries( $event_id );
	} // count_entries

	/**
	 * Text displayed when their are no entries.
	 *
	 * @since   1.3
	 */
	public function no_items() {
		esc_html_e( 'No entries in this playlist.', 'mobile-dj-manager' );
	}

	/**
	 * Default display for columns that do not have a method defined.
	 *
	 * @since   1.3
	 * @param   str $item           The playlist data.
	 * @param   str $column_name    The table column name.
	 */
	public function column_default( $item, $column_name ) {

		switch ( $column_name ) {
			case 'ID':
			case 'song':
			case 'artist':
			case 'category':
			case 'notes':
			case 'added_by':
			case 'date':
				return $item[ $column_name ];

			default:
				return print_r( $item, true ); // Show the whole array for troubleshooting purposes.
		}

	} // column_default

	/**
	 * Data for the song column.
	 *
	 * @since   1.3
	 * @param   arr $item       The current item.
	 */
	public function column_song( $item ) {

		$title = '<strong>' . $item['song'] . '</strong>';

		$url = add_query_arg(
			array(
				'mdjm-action' => 'delete_song',
				'id'          => absint( $item['ID'] ),
				'mdjm_nonce'  => wp_create_nonce( 'remove_playlist_entry' ),
			)
		);
		/* translators: %s URL Code */
		$actions['delete'] = mdjm_employee_can( 'manage_events' ) ? sprintf( __( '<a href="%s">Delete</a>', 'mobile-dj-manager' ), $url ) : '';

		return $title . $this->row_actions( $actions );

	} // column_song

	/**
	 * Data for the added_by column.
	 *
	 * @since   1.3
	 * @param   arr $item       The current item.
	 * @return  str
	 */
	public function column_added_by( $item ) {

		if ( is_numeric( $item['added_by'] ) ) {
			$user = get_userdata( $item['added_by'] );

			$name = $user->display_name;
		} else {
			$name = $item['added_by'];
		}

		return $name;

	} // column_added_by

	/**
	 * Render the checkbox column.
	 *
	 * @since   1.3
	 * @param   arr $item       The current item.
	 * @return  str
	 */
	public function column_cb( $item ) {
		return sprintf( '<input type="checkbox" name="mdjm-playlist-bulk-delete[]" value="%s" />', $item['ID'] );
	} // column_song

	/**
	 * Define the table columns.
	 *
	 * @since   1.3
	 * @return  arr     $columns    Array of columns.
	 */
	public function get_columns() {

		$columns = array(
			'cb'       => '<input type="checkbox" />',
			'song'     => __( 'Song', 'mobile-dj-manager' ),
			'artist'   => __( 'Artist', 'mobile-dj-manager' ),
			'category' => __( 'Category', 'mobile-dj-manager' ),
			'notes'    => __( 'Notes', 'mobile-dj-manager' ),
			'added_by' => __( 'Added By', 'mobile-dj-manager' ),
			'date'     => __( 'Date Added', 'mobile-dj-manager' ),
		);

		if ( ! mdjm_employee_can( 'manage_events' ) ) {
			unset( $columns['cb'] );
		}

		return $columns;

	} // column_song

	/**
	 * Define which table columns are sortable.
	 *
	 * @since   1.3
	 * @return  arr     $sortable_columns   Array of sortable columns.
	 */
	public function get_sortable_columns() {

		$sortable_columns = array(
			'song'     => array( 'song', false ),
			'artist'   => array( 'artist', false ),
			'category' => array( 'category', true ),
			'added_by' => array( 'added_by', false ),
			'date'     => array( 'date', false ),
		);

		return $sortable_columns;

	} // get_sortable_columns

	/**
	 * Define the available bulk actions.
	 *
	 * @since   1.3
	 * @return  arr     $actions    Array of bulk actions.
	 */
	public function get_bulk_actions() {

		$actions = array();

		if ( mdjm_employee_can( 'manage_events' ) ) {
			$actions['bulk-delete'] = 'Delete';
		}

		return $actions;

	} // get_bulk_actions

	/**
	 * Define the category views.
	 *
	 * @since   1.3
	 * @return  arr     $views      Category views.
	 */
	public function get_views() {

		if ( ! isset( $_GET['event_id'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification
			return array();
		}

		$views   = array();
		$current = ( ! empty( $_GET['view_cat'] ) ? sanitize_text_field( wp_unslash( $_GET['view_cat'] ) ) : 'all' ); // phpcs:ignore WordPress.Security.NonceVerification

		$categories = mdjm_get_playlist_categories( absint( wp_unslash( $_GET['event_id'] ) ) ); // phpcs:ignore WordPress.Security.NonceVerification

		if ( $categories ) {
			$class   = ( 'all' === $current ? ' class="current"' : '' );
			$all_url = remove_query_arg( 'view_cat' );

			$views['all'] = sprintf(
				/* translators: %1 URL */
				__( '<a href="%1$s" %2$s >All</a>', 'mobile-dj-manager' ),
				$all_url,
				$class
			) .
							'<span class="count">' . mdjm_count_playlist_entries( absint( wp_unslash( $_GET['event_id'] ) ) ) . '</span>'; // phpcs:ignore WordPress.Security.NonceVerification

			foreach ( $categories as $category ) {

				$count = mdjm_count_playlist_entries( absint( wp_unslash( $_GET['event_id'] ) ), $category->name ); // phpcs:ignore WordPress.Security.NonceVerification

				if ( $count > 0 ) {

					$view_url = add_query_arg( 'view_cat', $category->name );
					$class    = ( $category->name === $current ? ' class="current"' : '' );

					$views[ $category->name ] = '<a href="' . $view_url . '" ' . $class . ' >' . $category->name . '</a>' .
								'<span class="count">(' . $count . ')</span>';

				}
			}
		}

		return $views;

	} // get_views

	/**
	 * Displays the playlist details.
	 *
	 * @since   1.3
	 */
	public function display_header() {

		if ( ! isset( $_GET['event_id'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification
			exit;
		}

		?>
		<p>
		<?php
		printf( /* translators: %s Client details */
			__( '<strong><u>Client</strong>:</u> %s', 'mobile-dj-manager' ), // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped 
			esc_html( mdjm_get_client_display_name( mdjm_get_event_client_id( absint( wp_unslash( $_GET['event_id'] ) ) ) ) ) // phpcs:ignore WordPress.Security.NonceVerification 
		);
		?>
		<br />
		<?php
		printf( /* translators: %s Client details */
			__( '<strong><u>Client Phone Number</strong>:</u> %s', 'mobile-dj-manager' ), // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped 
			esc_html( mdjm_get_client_phone( mdjm_get_event_client_id( absint( wp_unslash( $_GET['event_id'] ) ) ) ) ) // phpcs:ignore WordPress.Security.NonceVerification
		);
		?>
		<br />
		<?php
		printf(  /* translators: %s Event Date */
			__( '<strong><u>Date</strong>:</u> %s', 'mobile-dj-manager' ), // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped 
			esc_html( mdjm_get_event_long_date( absint( wp_unslash( $_GET['event_id'] ) ) ) ) // phpcs:ignore WordPress.Security.NonceVerification 
		);
		?>
		<br />
		<?php
		printf(
			__( '<strong><u>Event Timings</strong>:</u> ', 'mobile-dj-manager' ) // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped 
			. esc_html( mdjm_content_tag_start_time( absint( wp_unslash( $_GET['event_id'] ) ) ) ) // phpcs:ignore WordPress.Security.NonceVerification
			. ' - '
			. esc_html( mdjm_content_tag_end_time( absint( wp_unslash( $_GET['event_id'] ) ) ) ) // phpcs:ignore WordPress.Security.NonceVerification
		);
		?>
		<br />
		<?php
		printf( /* translators: %s Event type */
			__( '<strong><u>Type</strong>:</u> %s', 'mobile-dj-manager' ), // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped 
			esc_html( mdjm_get_event_type( absint( wp_unslash( $_GET['event_id'] ) ) ) ) // phpcs:ignore WordPress.Security.NonceVerification
		);
		?>
		<br />
		<?php
		printf( /* translators: %s Venue */
			__( '<strong><u>Venue</strong>:</u> %s', 'mobile-dj-manager' ), // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped 
			esc_html( mdjm_content_tag_venue( absint( wp_unslash( $_GET['event_id'] ) ) ) ) //  phpcs:ignore WordPress.Security.NonceVerification
		);
		?>
		<br />
		<?php
		printf( /* translators: %s Employee */
			__( '<strong><u>Primary Employee</strong>:</u> %s', 'mobile-dj-manager' ), // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped 
			esc_html( mdjm_get_employee_display_name( mdjm_get_event_primary_employee_id( absint( wp_unslash( $_GET['event_id'] ) ) ) ) ) // phpcs:ignore WordPress.Security.NonceVerification 
		);
		?>
		<br /></p>
		<p>
		<?php
		printf( /* translators: %s Package selected */
			__( '<strong><u>Package</strong>:</u><br/> %s', 'mobile-dj-manager' ), // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped 
			esc_html( mdjm_get_package_name( mdjm_get_event_package( absint( wp_unslash( $_GET['event_id'] ) ) ) ) ) // phpcs:ignore WordPress.Security.NonceVerification 
		);
		?>
		<br />
		<?php
		printf( /* translators: %s Addons selected */
			__( '<strong><u>Addons Selected</strong>:</u><br /> %s', 'mobile-dj-manager' ), // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped 
			esc_html( mdjm_content_tag_event_addons( absint( wp_unslash( $_GET['event_id'] ) ) ) ) // phpcs:ignore WordPress.Security.NonceVerification 
		);
		?>
		<br /></p>
		<p>
		<?php
		printf( /* translators: %s Number of Songs */
			__( '<strong>Total Songs</strong>: %s', 'mobile-dj-manager' ), // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped 
			count( $this->items )  // phpcs:ignore WordPress.Security.NonceVerification 
		);
		?>
		<br />
		<?php
		printf( /* translators: %% Playlist status */
			__( '<strong>Current Playlist Status</strong>: %s', 'mobile-dj-manager' ), // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped 
			mdjm_playlist_is_open( absint( wp_unslash( $_GET['event_id'] ) ) ) ? esc_html__( 'Open', 'mobile-dj-manager' ) : esc_html__( 'Closed', 'mobile-dj-manager' ) // phpcs:ignore WordPress.Security.NonceVerification 
		);
		?>
		<br />
		<?php
		printf( /* translators: %% Playlist status */
			__( '<strong>Guest Playlist URL</strong>: %s', 'mobile-dj-manager' ), // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped 
			esc_html( mdjm_content_tag_guest_playlist_url( absint( wp_unslash( $_GET['event_id'] ) ) ) ) // phpcs:ignore WordPress.Security.NonceVerification 
		);
		?>
		</p>

		<?php
		if ( $this->count_entries( absint( wp_unslash( $_GET['event_id'] ) ) ) >= 0 ) : // phpcs:ignore WordPress.Security.NonceVerification 
			?>

			<p>
			<form method="post" target="_blank">
				<?php mdjm_admin_action_field( 'print_playlist' ); ?>
				<input type="hidden" name="print_playlist_event_id" id="print_playlist_event_id" value="<?php echo esc_attr( absint( wp_unslash( $_GET['event_id'] ) ) ); ?>" /> <?php // phpcs:ignore WordPress.Security.NonceVerification ?>
				<?php wp_nonce_field( 'print_playlist_entry', 'mdjm_nonce', true, true ); ?>
				<?php submit_button( 'Print the Details', 'primary small', 'submit_print_pl', false ); ?>
				<?php esc_html_e( ' and order Playlist by', 'mobile-dj-manager' ); ?> <select name="print_order_by" id="print_order_by">
				<option value="date" selected="selected"><?php esc_attr_e( 'Date Added', 'mobile-dj-manager' ); ?></option>
				<option value="artist"><?php esc_attr_e( 'Artist Name', 'mobile-dj-manager' ); ?></option>
				<option value="song"><?php esc_attr_e( 'Song Name', 'mobile-dj-manager' ); ?></option>
				<option value="category"><?php esc_attr_e( 'Category', 'mobile-dj-manager' ); ?></option>
				</select> <?php esc_html_e( ' Repeat headers after', 'mobile-dj-manager' ); ?> <input type="text" name="print_repeat_headers" id="print_repeat_headers" class="small-text" value="0" /> <?php esc_html_e( 'rows', 'mobile-dj-manager' ); ?> <code><?php esc_html_e( 'Enter 0 for no repeat of headers', 'mobile-dj-manager' ); ?></code>
			</form>

			<form method="post">
				<?php mdjm_admin_action_field( 'email_playlist' ); ?>
				<?php wp_nonce_field( 'email_playlist_entry', 'mdjm_nonce', true, true ); ?>
				<input type="hidden" name="email_playlist_event_id" id="email_playlist_event_id" value="<?php echo esc_attr( absint( wp_unslash( $_GET['event_id'] ) ) ); ?>" /> <?php // phpcs:ignore WordPress.Security.NonceVerification ?>
				<?php submit_button( 'Email the Details', 'primary small', 'submit_email_pl', false ); ?>
				<?php esc_html_e( ' and order Playlist by', 'mobile-dj-manager' ); ?> <select name="email_order_by" id="email_order_by">
				<option value="date" selected="selected"><?php esc_attr_e( 'Date Added', 'mobile-dj-manager' ); ?></option>
				<option value="artist"><?php esc_attr_e( 'Artist Name', 'mobile-dj-manager' ); ?></option>
				<option value="song"><?php esc_attr_e( 'Song Name', 'mobile-dj-manager' ); ?></option>
				<option value="category"><?php esc_attr_e( 'Category', 'mobile-dj-manager' ); ?></option>
				</select> <?php esc_html_e( ' Repeat headers after', 'mobile-dj-manager' ); ?> <input type="text" name="repeat_headers" id="repeat_headers" class="small-text" value="0" /> <?php esc_html_e( 'rows', 'mobile-dj-manager' ); ?> <code><?php esc_html_e( 'Enter 0 for no repeat of headers', 'mobile-dj-manager' ); ?></code>
			</form>
			</p>
			<?php
		endif;
	} // display_header

	/**
	 * Outputs the form for adding an entry
	 *
	 * @access  public
	 * @since   1.4
	 * @return  void
	 */
	public function entry_form() {

		if ( ! isset( $_GET['event_id'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification 
			exit;
		}

		if ( mdjm_employee_can('manage_event') ) { ?>
		<h3><?php esc_html_e( 'Add Entry to Playlist', 'mobile-dj-manager' ); ?></h3>
		<form id="mdjm-playlist-form" name="mdjm-playlist-form" action="" method="post">
			<?php wp_nonce_field( 'add_playlist_entry', 'mdjm_nonce', true, true ); ?>
			<?php mdjm_admin_action_field( 'add_playlist_entry' ); ?>
			<input type="hidden" id="event_id" name="event_id" value="<?php echo esc_attr( absint( wp_unslash( $_GET['event_id'] ) ) ); ?>" /> <?php // phpcs:ignore WordPress.Security.NonceVerification ?>
			<input type="hidden" id="added_by" name="added_by" value="<?php echo esc_attr( mdjm_get_event_client_id( absint( wp_unslash( $_GET['event_id'] ) ) ) ); ?>" /> <?php // phpcs:ignore WordPress.Security.NonceVerification ?>
			<table id="mdjm-playlist-form-table">
				<tr>
					<td>
						<label for="song"><?php esc_html_e( 'Song', 'mobile-dj-manager' ); ?></label><br />
						<?php
						echo MDJM()->html->text( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
							array(
								'name' => 'song',
								'type' => 'text',
							)
						);
						?>
					</td>

					<td class="mdjm-playlist-artist-cell">
						<label for="artist"><?php esc_html_e( 'Artist', 'mobile-dj-manager' ); ?></label><br />
						<?php
						echo MDJM()->html->text( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
							array(
								'name' => 'artist',
								'type' => 'text',
							)
						);
						?>
					</td>

					<td class="mdjm-playlist-category-cell">
						<label for="category"><?php esc_html_e( 'Category', 'mobile-dj-manager' ); ?></label><br />
						<?php $playlist_categories = mdjm_get_playlist_categories(); ?>
						<?php $options = array(); ?>
						<?php foreach ( $playlist_categories as $playlist_category ) : ?>
							<?php $options[ $playlist_category->term_id ] = $playlist_category->name; ?>
						<?php endforeach; ?>
						<?php
						echo MDJM()->html->select( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
							array(
								'options'  => $options,
								'name'     => 'category',
								'selected' => mdjm_get_option( 'playlist_default_cat', 0 ), // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
							)
						);
						?>
					</td>

				</tr>
				<tr>

					<td class="mdjm-playlist-djnotes-cell" colspan="3">
						<label for="notes"><?php printf( esc_html__( 'Notes', 'mobile-dj-manager' ), '{artist_label}' ); ?></label><br />
						<?php
						echo MDJM()->html->textarea( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
							array(
								'name' => 'notes',
							)
						);
						?>
					</td>
				</tr>
			</table>
			<?php
			submit_button(
				esc_html__( 'Add to Playlist', 'mobile-dj-manager' ),
				'primary'
			);
			?>
		</form>
		<?php }
	} // entry_form

	/**
	 * Prepare the table columns, pagination and data for the table
	 */
	public function prepare_items() {

		if ( ! isset( $_GET['event_id'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification 
			exit;
		}

		$columns               = $this->get_columns(); // Retrieve table columns.
		$hidden                = array(); // Which fields are hidden.
		$sortable              = $this->get_sortable_columns(); // Which fields can be sorted by.
		$this->_column_headers = array( $columns, $hidden, $sortable );

		$per_page     = $this->get_items_per_page( 'entries_per_page', 5 );
		$current_page = $this->get_pagenum();
		$total_items  = $this->count_entries( absint( wp_verify_nonce( sanitize_text_field( wp_unslash( $_GET['event_id'] ) ) ) ) );

		$this->set_pagination_args(
			array(
				'total_items' => $total_items, // We have to calculate the total number of items.
				'per_page'    => $per_page, // We have to determine how many items to show on a page.
			)
		);

		$this->items = $this->get_entries( $per_page, $current_page );

	} // prepare_items

} // MDJM_PlayList_Table
