<?php
/**
 * This template is used to generate the page for the shortcode [mdjm-playlist].
 *
 * @version         1.1
 * @author          Mike Howard, Jack Mawhinney, Dan Porter
 * @content_tag     client
 * @content_tag     event
 * @shortcodes      Not Supported
 *
 * Do not customise this file!
 * If you wish to make changes, copy this file to your theme directory /theme/mdjm-templates/playlist/playlist-client.php
 */
global $mdjm_event;

$intro_text = sprintf(
	__( 'The %1$s playlist management system enables you to give %2$s (your %3$s) an idea of the types of songs you would like played during your %4$s on %5$s.', 'mobile-dj-manager' ),
	'{company_name}',
	'{employee_firstname}',
	'{artist_label}',
	mdjm_get_label_singular( true ),
	'{event_date}'
);

$guest_text = __( 'You can invite your guests to add their suggestions to your playlist too. They won\'t be able to see any existing entries and you will be able to filter through their suggestions and remove any you do not feel are suitable.', 'mobile-dj-manager' );

$share_options = array(
	mdjm_playlist_facebook_share( $mdjm_event->ID ),
	mdjm_playlist_twitter_share( $mdjm_event->ID ),
);

$share_text           = implode( '&nbsp;&nbsp;&nbsp;', $share_options );
$form_title           = __( 'Add Playlist Entry', 'mobile-dj-manager' );
$artist_label         = __( 'Artist', 'mobile-dj-manager' );
$artist_description   = __( 'The name of the artist who sang the song', 'mobile-dj-manager' );
$song_label           = __( 'Song', 'mobile-dj-manager' );
$song_description     = __( 'The name of the song you are adding', 'mobile-dj-manager' );
$category_label       = __( 'Category', 'mobile-dj-manager' );
$category_description = __( 'Select the category that best suits your song choice', 'mobile-dj-manager' );
$notes_label          = sprintf( __( 'Notes for your %s', 'mobile-dj-manager' ), '{artist_label}' );
$notes_description    = __( 'Is this song important to you? Want it played at a specific time? Let us know here!', 'mobile-dj-manager' );
$submit_label         = __( 'Add to Playlist', 'mobile-dj-manager' );
$playlist_limit       = mdjm_get_event_playlist_limit( $mdjm_event->ID );
$limit_reached        = sprintf( __( 'Your playlist has now reached the maximum of %d allowed songs. To add a new entry, an existing one must first be removed.', 'mobile-dj-manager' ), $playlist_limit );
$playlist_closed      = sprintf( __( 'The playlist system is now closed to allow %1$s to prepare for your %2$s. No further songs can be added at this time.', 'mobile-dj-manager' ), '{employee_firstname}', mdjm_get_label_singular( true ) );
$delete_entry         = __( 'Remove', 'mobile-dj-manager' );
$total_entries        = mdjm_count_playlist_entries( $mdjm_event->ID );
$view_playlist        = __( 'View Playlist', 'mobile-dj-manager' );
$view_playlist_class  = ' mdjm-hidden';
?>

<div id="mdjm_playlist_wrap">
	<?php do_action( 'mdjm_print_notices' ); ?>
	<?php do_action( 'mdjm_playlist_top', $mdjm_event->ID ); ?>
	<div id="mdjm_playlist_form_wrap" class="mdjm_clearfix">
		<?php do_action( 'mdjm_before_playlist_form' ); ?>

		<p class="head-nav"><a href="{event_url}"><?php printf( esc_html__( 'Back to %s', 'mobile-dj-manager' ), esc_html( mdjm_get_label_singular() ) ); ?></a></p>

		<p><?php echo esc_attr( $intro_text ); ?></p>
		<p><?php echo esc_attr( $guest_text ); ?></p>
		<p class="mdjm_playlist_share"><?php echo $share_text; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></p>

		<?php if ( $mdjm_event->playlist_is_open() ) : ?>

			<?php if ( $total_entries < $playlist_limit || $playlist_limit == 0 ) : ?>
				<form id="mdjm_playlist_form" class="mdjm_form" method="post">
					<?php wp_nonce_field( 'add_playlist_entry', 'mdjm_nonce', true, true ); ?>
					<input type="hidden" id="mdjm_playlist_event" name="mdjm_playlist_event" value="<?php echo esc_attr( $mdjm_event->ID ); ?>" />
					<input type="hidden" id="action" name="action" value="mdjm_submit_playlist" />

					<div class="mdjm-alert mdjm-alert-error mdjm-hidden"></div>

					<?php do_action( 'mdjm_playlist_form_top' ); ?>

					<fieldset id="mdjm_playlist_form_fields">
						<legend><?php echo esc_attr( $form_title ); ?></legend>

						<?php if ( $total_entries >= 0 ) : ?>
							<?php $view_playlist_class = ''; ?>
						<?php endif; ?>
						<p class="view_current_playlist<?php echo esc_attr( $view_playlist_class ); ?>">
							<a class="mdjm-scroller" href="#client-playlist-entries"><?php echo esc_html( $view_playlist ); ?></a>
						</p>

						<div class="mdjm-alert mdjm-alert-success mdjm-hidden"></div>

						<div id="mdjm-playlist-input-fields">
							<p class="mdjm_artist_field">
								<label for="mdjm_artist">
									<?php echo esc_attr( $artist_label ); ?>
								</label>
								<span class="mdjm-description"><?php echo esc_html( $artist_description ); ?></span>

								<input type="text" name="mdjm_artist" id="mdjm_artist" class="mdjm-input" />
							</p>

							<p class="mdjm_song_field">
								<label for="mdjm_song">
									<?php echo esc_attr( $song_label ); ?> <span class="mdjm-required-indicator">*</span>
								</label>
								<span class="mdjm-description"><?php echo esc_html( $song_description ); ?></span>

								<input type="text" name="mdjm_song" id="mdjm_song" class="mdjm-input" />
							</p>

							<p class="mdjm_category_field">
								<label for="mdjm_category">
									<?php echo esc_attr( $category_label ); ?>
								</label>
								<span class="mdjm-description"><?php echo esc_html( $category_description ); ?></span>

								<?php echo mdjm_playlist_category_dropdown(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
							</p>

							<p class="mdjm_notes_field">
								<label for="mdjm_notes">
									<?php echo esc_attr( $notes_label ); ?>
								</label>
								<span class="mdjm-description"><?php echo esc_html( $notes_description ); ?></span>

								<textarea name="mdjm_notes" id="mdjm_notes" class="mdjm-input"></textarea>
							</p>

							<?php do_action( 'mdjm_playlist_form_after_fields' ); ?>

							<input class="button" name="playlist_entry_submit" id="playlist_entry_submit" type="submit" value="<?php echo esc_attr( $submit_label ); ?>" />

							<?php do_action( 'mdjm_playlist_form_after_submit' ); ?>
						</div>

					</fieldset>

					<?php do_action( 'mdjm_playlist_form_bottom' ); ?>

				</form>

				<?php do_action( 'mdjm_after_guest_playlist_form' ); ?>

			<?php else : ?>
				<div class="mdjm-alert mdjm-alert-info"><?php echo esc_attr( $limit_reached ); ?></div>
			<?php endif; ?>

		<?php else : ?>
			<?php do_action( 'mdjm_playlist_closed', $mdjm_event->ID ); ?>
			   <div class="mdjm-alert mdjm-alert-info"><?php echo esc_html( $playlist_closed ); ?></div>
		<?php endif; ?>

	</div><!--end #mdjm_playlist_form_wrap-->

	<?php do_action( 'mdjm_playlist_before_entries' ); ?>

	<?php
	$playlist_entries = mdjm_get_playlist_by_category( $mdjm_event->ID );
	$entries_class    = $playlist_entries ? '' : ' class="mdjm-hidden"';
	$your_playlist    = __( 'Your Current Playlist', 'mobile-dj-manager' );
	?>

	<div id="playlist-entries"<?php echo $entries_class; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>

		<a id="client-playlist-entries"></a>
		<h5><?php echo esc_html( $your_playlist ); ?></h5>

		<p>
		<?php
		printf(
			__( 'Your playlist currently consists of <span class="song-count">%1$d %2$s</span> and is approximately <span class="playlist-length">%3$s</span> long. Your %4$s is scheduled for %5$s.', 'mobile-dj-manager' ), // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			esc_html( $total_entries ),
			esc_html( _n( 'song', 'songs', esc_html( $total_entries ), 'mobile-dj-manager' ) ),
			'{playlist_duration}',
			esc_html( mdjm_get_label_singular( true ) ),
			'{event_duration}'
		);
		?>
		</p>

		<div class="playlist-entry-row-headings">
			<div class="playlist-entry-column">
				<span class="playlist-entry-heading"><?php echo esc_html( $artist_label ); ?></span>
			</div>
			<div class="playlist-entry-column">
				<span class="playlist-entry-heading"><?php echo esc_html( $song_label ); ?></span>
			</div>
			<div class="playlist-entry-column">
				<span class="playlist-entry-heading"><?php echo esc_html( $category_label ); ?></span>
			</div>
			<div class="playlist-entry-column">
				<span class="playlist-entry-heading"><?php esc_html_e( 'Notes', 'mobile-dj-manager' ); ?></span>
			</div>
			<div class="playlist-entry-column">
				<span class="playlist-entry-heading"></span>
			</div>
		</div>

		<?php foreach ( $playlist_entries as $category => $category_entries ) : ?>

			<?php foreach ( $category_entries as $entry ) : ?>
				<?php $entry_data = mdjm_get_playlist_entry_data( $entry->ID ); ?>

				<div class="playlist-entry-row mdjm-playlist-entry-<?php echo esc_attr( $entry->ID ); ?>">
					<div class="playlist-entry-column">
						<span class="playlist-entry"><?php echo esc_attr( $entry_data['artist'] ); ?></span>
					</div>
					<div class="playlist-entry-column">
						<span class="playlist-entry"><?php echo esc_attr( $entry_data['song'] ); ?></span>
					</div>
					<div class="playlist-entry-column">
						<span class="playlist-entry"><?php echo esc_attr( $category ); ?></span>
					</div>
					<div class="playlist-entry-column">
						<span class="playlist-entry">
							<?php if ( 'Guest' == $category ) : ?>
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
							<a class="mdjm-delete playlist-delete-entry" data-event="<?php echo esc_attr( $mdjm_event->ID ); ?>" data-entry="<?php echo esc_attr( $entry->ID ); ?>"><?php echo esc_html( $delete_entry ); ?></a>
						</span>
					</div>
				</div>
			<?php endforeach; ?>

		<?php endforeach; ?>

	</div>

</div><!-- end of #mdjm_playlist_wrap -->
