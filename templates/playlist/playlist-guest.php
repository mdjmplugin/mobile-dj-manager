<?php
/**
 * This template is used to generate the page for the shortcode [mdjm-playlist] and is used for guests.
 *
 * @version			1.1
 * @author			Mike Howard
 * @since			1.5
 * @content_tag		client
 * @content_tag		event
 * @shortcodes		Not Supported
 *
 * Do not customise this file!
 * If you wish to make changes, copy this file to your theme directory /theme/mdjm-templates/playlist/playlist-guest.php
 */

// These global vars must remain
global $mdjm_event;

$intro_text = sprintf(
	__( 'Welcome to the %s %s music playlist management system for %s %s taking place on %s.', 'mobile-dj-manager' ),
	'{company_name}',
	'{application_name}',
	"{client_fullname}'s",
	'{event_type}',
	'{event_date}'
);

$lead_in_text = sprintf(
	__( '%1$s has invited you to provide input for the music that will be played during their %2$s. Simply add your selections below and %1$s will be able to review them.', 'mobile-dj-manager' ),
	'{client_firstname}',
	mdjm_get_label_singular( true )
);

$playlist_closed = sprintf(
	__( 'The playlist for this %s is now closed and not accepting suggestions', 'mobile-dj-manager' ),
	mdjm_get_label_singular( true )
);

$limit_reached = sprintf(
	__( 'The playlist for this %s is full and not accepting suggestions', 'mobile-dj-manager' ),
	mdjm_get_label_singular( true )
);

$form_title         = sprintf( __( '%s %s Playlist', 'mobile-dj-manager' ), "{client_firstname}'s", '{event_type}' );
$existing_entries   = __( "Here's what you've added so far...", 'mobile-dj-manager' );
$name_label         = __( 'Name', 'mobile-dj-manager' );
$name_description   = sprintf( __( 'So %s knows who added this song', 'mobile-dj-manager' ), '{client_firstname}' );
$artist_label       = __( 'Artist', 'mobile-dj-manager' );
$artist_description = __( 'The name of the artist who sang the song', 'mobile-dj-manager' );
$song_label         = __( 'Song', 'mobile-dj-manager' );
$song_description   = __( 'The name of the song you are suggesting', 'mobile-dj-manager' );
$submit_label       = __( 'Suggest Song', 'mobile-dj-manager' );

?>
<div id="mdjm_guest_playlist_wrap">
	<?php do_action( 'mdjm_print_notices' ); ?>
	<div id="mdjm_guest_playlist_form_wrap" class="mdjm_clearfix">
        <?php do_action( 'mdjm_before_guest_playlist_form' ); ?>

        <p><?php echo esc_attr( $intro_text ); ?></p>
		<p><?php echo esc_attr( $lead_in_text ); ?></p>

		<?php if ( $mdjm_event->playlist_is_open() ) : ?>
            <?php $event_playlist_limit = mdjm_get_event_playlist_limit( $mdjm_event->ID ); ?>
            <?php $entries_in_playlist  = mdjm_count_playlist_entries( $mdjm_event->ID ); ?>

            <?php if ( $entries_in_playlist < $event_playlist_limit || $event_playlist_limit == 0 ) : ?> 

                <form id="mdjm_guest_playlist_form" class="mdjm_form" method="post">
                    <?php wp_nonce_field( 'add_guest_playlist_entry', 'mdjm_nonce', true, true ); ?>
                    <input type="hidden" id="mdjm_playlist_event" name="mdjm_playlist_event" value="<?php echo $mdjm_event->ID; ?>" />
                    <input type="hidden" id="action" name="action" value="mdjm_submit_guest_playlist" />

                    <div class="mdjm-alert mdjm-alert-error mdjm-hidden"></div>
                    <div class="mdjm-alert mdjm-alert-success mdjm-hidden"></div>

					<?php do_action( 'mdjm_guest_playlist_form_top' ); ?>

                    <fieldset id="mdjm_guest_playlist_form_fields">
                        <legend><?php echo esc_attr( $form_title ); ?></legend>

						<?php do_action( 'mdjm_guest_playlist_before_entries' ); ?>
                        <div id="guest-playlist-entries" class="mdjm-hidden">
                            <p><?php echo esc_attr( $existing_entries ); ?></p>
                            <div class="guest-playlist-entry-row">
                                <div class="guest-playlist-entry-column">
                                    <span class="guest-playlist-entry-heading"><?php echo $artist_label; ?></span>
                                </div>
                                <div class="guest-playlist-entry-column">
                                    <span class="guest-playlist-entry-heading"><?php echo $song_label; ?></span>
                                </div>
                            </div>
                        </div>

						<div id="mdjm-guest-playlist-input-fields">
                            <p class="mdjm_guest_name_field">
                                <label for="mdjm_guest_name">
                                    <?php echo esc_attr( $name_label ); ?> <span class="mdjm-required-indicator">*</span>
                                </label>
                                <span class="mdjm-description"><?php echo esc_html( $name_description ); ?></span>

                                <input type="text" name="mdjm_guest_name" id="mdjm-guest-name" class="mdjm-input" />
                            </p>

                            <p class="mdjm_guest_artist_field">
                                <label for="mdjm_guest_artist">
                                    <?php echo esc_attr( $artist_label ); ?>
                                </label>
                                <span class="mdjm-description"><?php echo esc_html( $artist_description ); ?></span>

                                <input type="text" name="mdjm_guest_artist" id="mdjm-guest-artist" class="mdjm-input" />
                            </p>

                            <p class="mdjm_guest_song_field">
                                <label for="mdjm_guest_song">
                                    <?php echo esc_attr( $song_label ); ?> <span class="mdjm-required-indicator">*</span>
                                </label>
                                <span class="mdjm-description"><?php echo esc_html( $song_description ); ?></span>

                                <input type="text" name="mdjm_guest_song" id="mdjm-guest-song" class="mdjm-input" />
                            </p>

							<?php do_action( 'mdjm_guest_playlist_form_after_fields' ); ?>

                            <input class="button" name="entry_guest_submit" id="entry_guest_submit" type="submit" value="<?php echo esc_attr( $submit_label ); ?>" />

							<?php do_action( 'mdjm_guest_playlist_form_after_submit' ); ?>
                        </div>

                    </fieldset>

					<?php do_action( 'mdjm_guest_playlist_form_bottom' ); ?>

                </form>

				<?php do_action( 'mdjm_after_guest_playlist_form' ); ?>

			<?php else : ?>
        	    <div class="mdjm-alert mdjm-alert-info"><?php echo esc_attr( $limit_reached ); ?></div>
			<?php endif; ?>

		<?php else : ?>
			<?php do_action( 'mdjm_guest_playlist_closed', $mdjm_event->ID ); ?>
       		<div class="mdjm-alert mdjm-alert-info"><?php echo esc_attr( $playlist_closed ); ?></div>
		<?php endif; ?>

    </div><!--end #mdjm_guest_playlist_form_wrap-->
</div><!-- end of #mdjm_guest_playlist_wrap -->
