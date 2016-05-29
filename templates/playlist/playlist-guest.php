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

// These global vars must remain
global $mdjm_event;
?>

<div id="mdjm-guest-playlist-wrapper">
	<?php do_action( 'mdjm_guest_playlist_top', $mdjm_event->ID ); ?>
	
	<div id="mdjm-guest-playlist-header">
    	
        <?php do_action( 'mdjm_print_notices' ); ?>
        
    	<?php do_action( 'mdjm_guest_playlist_header_top', $mdjm_event->ID ); ?>
                        
        <p><?php printf( __( 'Welcome to the %s %s music playlist management system for %s %s taking place on %s.', 'mobile-dj-manager' ),
                '{company_name}',
				'{application_name}',
                "{client_fullname}'s",
				'{event_type}',
				'{event_date}' ); ?></p>
                
        <p><?php printf( __( '%1$s has invited you to provide input for the music that will be played during their %2$s. Simply add your selections below and %1$s will be able to review them.', 'mobile-dj-manager' ),
                    '{client_firstname}',
					mdjm_get_label_singular( true ) ); ?></p>
    
    	<?php do_action( 'mdjm_guest_playlist_header_bottom', $mdjm_event->ID ); ?>
	</div><!-- end mdjm-playlist-header -->
    
	<div id="mdjm-guest-playlist-form">
    	<?php do_action( 'mdjm_guest_playlist_form_top', $mdjm_event->ID ); ?>
        
        <?php if( $mdjm_event->playlist_is_open() ) : ?>
        
            <form id="mdjm-guest-playlist-form" name="mdjm-guest-playlist-form" action="" method="post">
                <?php wp_nonce_field( 'add_guest_playlist_entry', 'mdjm_nonce', true, true ); ?>
                <?php mdjm_action_field( 'add_guest_playlist_entry' ); ?>
                <input type="hidden" id="entry_event" name="entry_event" value="<?php echo $mdjm_event->ID; ?>" />
                
                <table id="mdjm-guest-playlist-form-table">
                    <tr>
                        <td class="mdjm-guest-playlist-firstname-cell">
                            <label for="entry_guest_firstname"><?php _e( 'First Name', 'mobile-dj-manager' ); ?></label><br />
                            <input type="text" name="entry_guest_firstname" id="entry_guest_firstname" data-placeholder="<?php _e( 'First Name', 'mobile-dj-manager' ); ?>" placeholder="<?php if( ! empty( $mdjm_guest['firstname'] ) ) : echo $mdjm_guest['firstname']; endif; ?>" required />
                        </td>
                        
                        <td class="mdjm-guest-playlist-lastname-cell">
                            <label for="entry_guest_lastname"><?php _e( 'Last Name', 'mobile-dj-manager' ); ?></label><br />
                            <input type="text" name="entry_guest_lastname" id="entry_guest_lastname" data-placeholder="<?php _e( 'Last Name', 'mobile-dj-manager' ); ?>" placeholder="<?php if( ! empty( $mdjm_guest['lastname'] ) ) : echo $mdjm_guest['lastname']; endif; ?>" required />
                        </td>
                        
                        <td class="mdjm-guest-playlist-category-cell">
                            <label for="entry_guest_song"><?php _e( 'Song', 'mobile-dj-manager' ); ?></label><br />
                            <input type="text" name="entry_guest_song" id="entry_guest_song" data-placeholder="<?php _e( 'Song', 'mobile-dj-manager' ); ?>" />
                        </td>
                        
                        <td class="mdjm-guest-playlist-djnotes-cell">
                            <label for="entry_guest_artist"><?php _e( 'Artist', 'mobile-dj-manager' ); ?></label><br />
                            <input type="text" name="entry_guest_artist" id="entry_guest_artist" data-placeholder="<?php _e( 'Artist', 'mobile-dj-manager' ); ?>" />
                        </td>
                    </tr>
                    <tr>
                    	<td class="mdjm-guest-playlist-addnew-cell" colspan="4">
                            <input type="submit" name="entry_guest_addnew" id="entry_guest_addnew" value="<?php _e( 'Suggest Song', 'mobile-dj-manager' ); ?>" />
                        </td>
                    </tr>
                </table>
            </form>
            
        <?php else : ?>
        	<?php do_action( 'mdjm_guest_playlist_closed', $mdjm_event->ID ); ?>
            
            <p><?php printf( __( 'Sorry but the music playlist system for %s %s on %s is no longer accepting suggestions.', 'mobile-dj-manager' ),
						"{client_fullname}'s", '{event_type}', '{event_date}' ); ?></p>
            
        <?php endif; // endif( mdjm_playlist_is_open( $mdjm_event->ID ) ) ?>
        
    	<?php do_action( 'mdjm_guest_playlist_form_bottom', $mdjm_event->ID ); ?>
	</div><!-- end mdjm-guest-playlist-form -->
    	
    
    	
    <div id="mdjm-guest-playlist-footer">
    	<?php do_action( 'mdjm_guest_playlist_footer_top', $mdjm_event->ID ); ?>
    	<?php do_action( 'mdjm_guest_playlist_footer_bottom', $mdjm_event->ID ); ?>
    </div><!-- end mdjm-guest-playlist-footer -->
    
	<?php do_action( 'mdjm_guest_playlist_bottom', $mdjm_event->ID ); ?>
</div><!-- end mdjm-guest-playlist-wrapper -->