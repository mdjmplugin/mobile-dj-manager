<?php
/**
 * This template is used to generate the page for the shortcode [mdjm-playlist] and is used for guests.
 *
 * @version			1.0
 * @author			Mike Howard
 * @since			1.3
 * @content_tag		client
 * @content_tag		event
 * @shortcodes		Not Supported
 *
 * Do not customise this file!
 * If you wish to make changes, copy this file to your theme directory /theme/mdjm-templates/playlist/playlist-guest.php
 */
global $mdjm_notice;
$mdjm_event_id = get_the_ID();
?>

<div id="mdjm-guest-playlist-wrapper">
	<?php do_action( 'mdjm_guest_playlist_top', $mdjm_event_id ); ?>
	
	<div id="mdjm-guest-playlist-header">
    	
        <?php do_action( 'mdjm_print_notices' ); ?>
        
    	<?php do_action( 'mdjm_guest_playlist_header_top', $mdjm_event_id ); ?>
        
        <?php if( ! empty( $mdjm_notice ) ) : echo $mdjm_notice; endif; ?>
                
        <p><?php printf( __( 'Welcome to the %s %s music playlist management system for %s %s taking place on %s.', 'mobile-dj-manager' ),
                mdjm_get_option( 'company_name' ),
				mdjm_get_option( 'app_name', __( 'Client Zone', 'mobile-dj-manager' ) ),
                "{client_fullname}'s",
				'{event_type}',
				'{event_date}' ); ?></p>
                
        <p><?php printf( __( '%s$1 has invited you to provide input for the music that will be played during their event. Simply add your selections below and %s$1 will be able to review them.', 'mobile-dj-manager' ),
                    '{client_firstname}' ); ?></p>
    
    	<?php do_action( 'mdjm_guest_playlist_header_bottom', $mdjm_event_id ); ?>
	</div><!-- end mdjm-playlist-header -->
    
	<div id="mdjm-guest-playlist-form">
    	<?php do_action( 'mdjm_guest_playlist_form_top', $mdjm_event_id ); ?>
        
        <?php if( mdjm_playlist_is_open( $mdjm_event_id ) ) : ?>
        
            <form id="mdjm-guest-playlist-form" name="mdjm-guest-playlist-form" action="" method="post">
                <?php wp_nonce_field( 'add_playlist_entry', 'mdjm_nonce', true, true ); ?>
                <?php mdjm_action_field( 'add_playlist_entry' ); ?>
                <input type="hidden" id="entry_event" name="entry_event" value="<?php echo $mdjm_event_id; ?>" />
                
                <table id="mdjm-guest-playlist-form-table">
                    <tr>
                        <td class="mdjm-guest-playlist-song-cell">
                            <label for="entry_song"><?php _e( 'Song', 'mobile-dj-manager' ); ?></label><br />
                            <input type="text" name="entry_song" id="entry_song" data-placeholder="<?php _e( 'Song Name', 'mobile-dj-manager' ); ?>" />
                        </td>
                        
                        <td class="mdjm-guest-playlist-artist-cell">
                            <label for="entry_artist"><?php _e( 'Artist', 'mobile-dj-manager' ); ?></label><br />
                            <input type="text" name="entry_artist" id="entry_artist" data-placeholder="<?php _e( 'Artist Name', 'mobile-dj-manager' ); ?>" />
                        </td>
                        
                        <td class="mdjm-guest-playlist-category-cell">
                            <label for="entry_category"><?php _e( 'Category', 'mobile-dj-manager' ); ?></label><br />
                            <?php mdjm_playlist_category_dropdown(); ?>
                        </td>
                        
                        <td class="mdjm-guest-playlist-djnotes-cell">
                            <label for="mdjm_playlist_djnotes"><?php printf( __( 'Notes for your %s', 'mobile-dj-manager' ), mdjm_get_option( 'artist', __( 'DJ', 'mobile-dj-manager' ) ) ); ?></label><br />
                            <textarea name="entry_djnotes" id="entry_djnotes" data-placeholder="<?php printf( __( 'Notes for your %s', 'mobile-dj-manager' ), mdjm_get_option( 'artist', __( 'DJ', 'mobile-dj-manager' ) ) ); ?>"></textarea>
                        </td>
                    </tr>
                    <tr>
                    	<td class="mdjm-guest-playlist-addnew-cell" colspan="4">
                            <input type="submit" name="entry_addnew" id="entry_addnew" value="<?php _e( 'Add to Playlist', 'mobile-dj-manager' ); ?>" />
                        </td>
                    </tr>
                </table>
            </form>
            
        <?php else : ?>
        	<?php do_action( 'mdjm_guest_playlist_closed', $mdjm_event_id ); ?>
            
            <p><?php printf( __( 'Sorry but the music playlist system for %s %s on %s is no longer accepting suggestions.', 'mobile-dj-manager' ),
						"{client_fullname}'s", '{event_type}', '{event_date}' ); ?></p>
            
        <?php endif; // endif( mdjm_playlist_is_open( $mdjm_event_id ) ) ?>
        
    	<?php do_action( 'mdjm_guest_playlist_form_bottom', $mdjm_event_id ); ?>
	</div><!-- end mdjm-guest-playlist-form -->
    	
    
    	
    <div id="mdjm-guest-playlist-footer">
    	<?php do_action( 'mdjm_guest_playlist_footer_top', $mdjm_event_id ); ?>
    	<?php do_action( 'mdjm_guest_playlist_footer_bottom', $mdjm_event_id ); ?>
    </div><!-- end mdjm-guest-playlist-footer -->
    
	<?php do_action( 'mdjm_guest_playlist_bottom', $mdjm_event_id ); ?>
</div><!-- end mdjm-guest-playlist-wrapper -->