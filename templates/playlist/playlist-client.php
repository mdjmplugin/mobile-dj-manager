<?php
/**
 * This template is used to generate the page for the shortcode [mdjm-playlist].
 *
 * @version			1.0
 * @author			Mike Howard
 * @since			1.3
 * @content_tag		client
 * @content_tag		event
 * @shortcodes		Not Supported
 *
 * Do not customise this file!
 * If you wish to make changes, copy this file to your theme directory /theme/mdjm-templates/playlist/playlist-client.php
 */
global $mdjm_event;
?>

<div id="mdjm-playlist-wrapper">
	<?php do_action( 'mdjm_playlist_top', $mdjm_event->ID ); ?>
	
	<div id="mdjm-playlist-header">
    	
        <?php do_action( 'mdjm_print_notices' ); ?>
        
    	<?php do_action( 'mdjm_playlist_header_top', $mdjm_event->ID ); ?>
        
        <p class="head-nav"><a href="{event_url}"><?php  printf( __( 'Back to %s', 'mobile-dj-manager' ), mdjm_get_label_singular() ); ?></a></p>
        
        <p><?php printf( __( 'The %s playlist management system enables you to give %s (your %s) an idea of the types of songs you would like played during your %s on %s.', 'mobile-dj-manager' ),
                '{company_name}',
                '{dj_firstname}',
                '{artist_label}',
				mdjm_get_label_singular( true ),
                '{event_date}' ); ?></p>
                
        <p><?php printf( __( "Don't forget that you can invite your guests to add their suggestions to your playlist too. They won't be able to see any existing entries, and you will be able to filter through their suggestions if you do not feel they are suitable. Just tell them to visit <a href='%s'>%s</a> to get started.", 'mobile-dj-manager' ),
                    '{guest_playlist_url}', '{guest_playlist_url}' ); ?></p>
    
    	<?php do_action( 'mdjm_playlist_header_bottom', $mdjm_event->ID ); ?>
	</div><!-- end mdjm-playlist-header -->
    
	<div id="mdjm-playlist-form">
    	<?php do_action( 'mdjm_playlist_form_top', $mdjm_event->ID ); ?>
        
        <?php if( $mdjm_event->playlist_is_open() ) : ?>
        
            <form id="mdjm-playlist-form" name="mdjm-playlist-form" action="" method="post">
                <?php wp_nonce_field( 'add_playlist_entry', 'mdjm_nonce', true, true ); ?>
                <?php mdjm_action_field( 'add_playlist_entry' ); ?>
                <input type="hidden" id="entry_event" name="entry_event" value="<?php echo $mdjm_event->ID; ?>" />
                
                <table id="mdjm-playlist-form-table">
                    <tr>
                        <td class="mdjm-playlist-song-cell">
                            <label for="entry_song"><?php _e( 'Song', 'mobile-dj-manager' ); ?></label><br />
                            <input type="text" name="entry_song" id="entry_song" data-placeholder="<?php _e( 'Song Name', 'mobile-dj-manager' ); ?>" required />
                        </td>
                        
                        <td class="mdjm-playlist-artist-cell">
                            <label for="entry_artist"><?php _e( 'Artist', 'mobile-dj-manager' ); ?></label><br />
                            <input type="text" name="entry_artist" id="entry_artist" data-placeholder="<?php _e( 'Artist Name', 'mobile-dj-manager' ); ?>" required />
                        </td>
                        
                        <td class="mdjm-playlist-category-cell">
                            <label for="entry_category"><?php _e( 'Category', 'mobile-dj-manager' ); ?></label><br />
                            <?php mdjm_playlist_category_dropdown(); ?>
                        </td>
                        
                        <td class="mdjm-playlist-djnotes-cell">
                            <label for="mdjm_playlist_djnotes"><?php printf( __( 'Notes for your %s', 'mobile-dj-manager' ), '{artist_label}' ); ?></label><br />
                            <textarea name="entry_djnotes" id="entry_djnotes" data-placeholder="<?php printf( __( 'Notes for your %s', 'mobile-dj-manager' ), '{artist_label}' ); ?>"></textarea>
                        </td>
                    </tr>
                    <tr>
                    	<td class="mdjm-playlist-addnew-cell" colspan="4">
                            <input type="submit" name="entry_addnew" id="entry_addnew" value="<?php _e( 'Add to Playlist', 'mobile-dj-manager' ); ?>" />
                        </td>
                    </tr>
                </table>
            </form>
            
        <?php else : ?>
        <style type="text/css">
		.mdjm-playlist-remove	{
			display: none;
		}
		</style>
        	<?php do_action( 'mdjm_playlist_closed', $mdjm_event->ID ); ?>
            
            <p><?php printf( __( 'The playlist for this %s is currently closed to allow %s to prepare for your event. Existing playlist entries are displayed below.', 'mobile-dj-manager' ), mdjm_get_label_singular( true ), '{dj_firstname}' ); ?></p>
            
        <?php endif; // endif( mdjm_playlist_is_open( $mdjm_event->ID ) ) ?>
        
    	<?php do_action( 'mdjm_playlist_form_bottom', $mdjm_event->ID ); ?>
	</div><!-- end mdjm-playlist-form -->
    	
	<?php $playlist = mdjm_get_playlist_by_category( $mdjm_event->ID ); ?>
    
    <?php if( $playlist ) : ?>
    	 <div id="mdjm-playlist-entries">
        	<?php do_action( 'mdjm_playlist_entries_top', $mdjm_event->ID ); ?>
            
        	<?php $entries_in_playlist = mdjm_count_playlist_entries( $mdjm_event->ID ); ?>
        	<p><?php printf( __( 'Your playlist currently consists of %d %s and is approximately %s long. Your %s is %s long.', 'mobile-dj-manager' ),
					$entries_in_playlist,
					_n( 'track', 'tracks', $entries_in_playlist, 'mobile-dj-manager' ),
					'{playlist_duration}',
					mdjm_get_label_singular(),
					'{event_duration}' ); ?></p>
        
        	<?php foreach( $playlist as $category => $entries ) : ?>
            	
				<?php $entries_in_category = mdjm_count_playlist_entries( $mdjm_event->ID, $category ); ?>
                
                <div class="row">
                
                    <div class="category"><?php echo $category; ?> <span class="cat-count">(<?php echo $entries_in_category; ?> <?php echo _n( 'entry', 'entries', $entries_in_category, 'mobile-dj-manager' ); ?> | <?php echo mdjm_playlist_duration( $mdjm_event->ID, $entries_in_category ); ?>)</span></div>
                    
                </div>
                
                <?php foreach( $entries as $entry ) : ?>
                	
                    <?php $entry_data = mdjm_get_playlist_entry_data( $entry->ID ); ?>
                    
                	<div class="row mdjm-playlist-entry mdjm-playlist-entry-<?php echo $entry->id; ?>">
                    	<div class="mdjm-playlist-song col"><?php echo $entry_data['song']; ?></div>
                        
                        <div class="mdjm-playlist-artist col"><?php echo $entry_data['artist']; ?></div>
                        
                        <div class="mdjm-playlist-info col">
							<?php if( $category == 'Guest' ) : ?>
                            
                            	<?php echo stripslashes( $entry_data['added_by'] ); ?>
                            
							<?php elseif( ! empty( $entry_data['djnotes'] ) ) : ?>
									
								<?php echo stripslashes( $entry_data['djnotes'] ); ?>
                                
							<?php else : ?>
                                	
								<?php echo '&ndash;'; ?>
								
							<?php endif; // endif( $category == 'Guest Added' ) ?>
                        </div>
                        
                        <div class="mdjm-playlist-remove last"><a href="<?php echo wp_nonce_url( mdjm_get_formatted_url( mdjm_get_option( 'playlist_page' ) ) . 'mdjm_action=remove_playlist_entry&id=' . $entry->ID . '&event_id=' . $mdjm_event->ID, 'remove_playlist_entry', 'mdjm_nonce' ); ?>"><?php _e( 'Remove', 'mobile-dj-manager' ); ?></a>
                    </div>
                </div>
                
                <?php endforeach; // end foreach( $entries as $entry ) ?>
                
            <?php endforeach; // end foreach( $playlist as $entry ) ?>
        
        </div><!-- end mdjm-playlist-entries -->
	
	<?php endif; // endif( $playlist ) ?>
    	
	<?php do_action( 'mdjm_playlist_entries_bottom', $mdjm_event->ID ); ?>
    
    	
    <div id="mdjm-playlist-footer">
    	<?php do_action( 'mdjm_playlist_footer_top', $mdjm_event->ID ); ?>
    	<?php do_action( 'mdjm_playlist_footer_bottom', $mdjm_event->ID ); ?>
    </div><!-- end mdjm-playlist-footer -->
    
	<?php do_action( 'mdjm_playlist_bottom', $mdjm_event->ID ); ?>
</div><!-- end mdjm-playlist-wrapper -->