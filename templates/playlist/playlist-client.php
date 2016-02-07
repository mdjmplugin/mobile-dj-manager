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
 * If you wish to make changes, copy this file to your theme directory /theme/mdjm-templates/playlist/playlist.php
 */
$mdjm_event_id = get_the_ID();
?>
<div id="mdjm-playlist-wrapper">
	<?php do_action( 'mdjm_playlist_top', $mdjm_event_id ); ?>
	
	<div id="mdjm-playlist-header">
    	<?php do_action( 'mdjm_playlist_header_top', $mdjm_event_id ); ?>
        
        <p class="head-nav"><a href="<?php echo mdjm_get_event_uri( $mdjm_event_id ); ?>"><?php  _e( 'Back to Event', 'mobile-dj-manager' ); ?></a></p>
        
        <p><?php printf( __( 'The %s playlist management system enables you to give %s (your %s) an idea of the types of songs you would like played during your event on %s.', 'mobile-dj-manager' ),
                mdjm_get_option( 'company_name' ),
                '{dj_firstname}',
                mdjm_get_option( 'artist', __( 'DJ', 'mobile-dj-manager' ) ),
                '{event_date}' ); ?></p>
                
        <p><?php printf( __( "Don't forget that you can invite your guests to add their suggestions to your playlist too. They won't be able to see any existing entries, and you will be able to filter through their suggestions if you do not feel they are suitable. Just tell them to visit <a href='%s'>%s</a> to get started.", 'mobile-dj-manager' ),
                    '{guest_playlist_url}',
                    '{guest_playlist_url}' ); ?></p>
    
    	<?php do_action( 'mdjm_playlist_header_bottom', $mdjm_event_id ); ?>
	</div><!-- end mdjm-playlist-header -->
    
	<div id="mdjm-playlist-form">
    	<?php do_action( 'mdjm_playlist_form_top', $mdjm_event_id ); ?>
        
        <?php if( mdjm_playlist_is_open( $mdjm_event_id ) ) : ?>
        
            <form id="mdjm-playlist-form" name="mdjm-playlist-form" action="" method="post">
                <?php wp_nonce_field( 'add_song', 'mdjm_nonce', true, true ); ?>
                <?php mdjm_action_field( 'add_song' ); ?>
                
                <table id="mdjm-playlist-form-table">
                    <tr>
                        <td class="mdjm-playlist-song-cell">
                            <label for="mdjm_playlist_song"><?php _e( 'Song', 'mobile-dj-manager' ); ?></label><br />
                            <input type="text" name="mdjm_playlist_song" id="mdjm_playlist_song" data-placeholder="<?php _e( 'Song Name', 'mobile-dj-manager' ); ?>" />
                        </td>
                        
                        <td class="mdjm-playlist-artist-cell">
                            <label for="mdjm_playlist_artist"><?php _e( 'Artist', 'mobile-dj-manager' ); ?></label><br />
                            <input type="text" name="mdjm_playlist_artist" id="mdjm_playlist_artist" data-placeholder="<?php _e( 'Artist Name', 'mobile-dj-manager' ); ?>" />
                        </td>
                        
                        <td class="mdjm-playlist-category-cell">
                            <label for="mdjm_playlist_category"><?php _e( 'Category', 'mobile-dj-manager' ); ?></label><br />
                            <?php mdjm_playlist_category_dropdown(); ?>
                        </td>
                        
                        <td class="mdjm-playlist-djnotes-cell">
                            <label for="mdjm_playlist_djnotes"><?php printf( __( 'Notes for your %s', 'mobile-dj-manager' ), mdjm_get_option( 'artist', __( 'DJ', 'mobile-dj-manager' ) ) ); ?></label><br />
                            <textarea name="mdjm_playlist_djnotes" id="mdjm_playlist_djnotes" data-placeholder="<?php printf( __( 'Notes for your %s', 'mobile-dj-manager' ), mdjm_get_option( 'artist', __( 'DJ', 'mobile-dj-manager' ) ) ); ?>"></textarea>
                        </td>
                    </tr>
                </table>
            </form>
            
        <?php else : ?>
        	<?php do_action( 'mdjm_playlist_closed', $mdjm_event_id ); ?>
            
            <p><?php printf( __( 'The playlist for this event is currently closed to allow %s to prepare for your event. Existing playlist entries are displayed below.', 'mobile-dj-manager' ), '{dj_firstname}' ); ?></p>
            
        <?php endif; // endif( mdjm_playlist_is_open( $mdjm_event_id ) ) ?>
        
    	<?php do_action( 'mdjm_playlist_form_bottom', $mdjm_event_id ); ?>
	</div><!-- end mdjm-playlist-form -->
    
    <div id="mdjm-playlist-entries">
    	<?php do_action( 'mdjm_playlist_entries_top', $mdjm_event_id ); ?>
    	
		<?php $playlist = mdjm_get_playlist_by_category( $mdjm_event_id ); ?>
        
        <?php if( $playlist ) : ?>
        	<?php $songs_in_playlist = mdjm_count_songs( $mdjm_event_id ); ?>
        	<p><?php printf( __( 'Your playlist currently consists of %d %s and is approximately %s long. Your event is %s long.', 'mobile-dj-manager' ),
					$songs_in_playlist,
					_n( 'track', 'tracks', $songs_in_playlist, 'mobile-dj-manager' ),
					mdjm_playlist_length( $mdjm_event_id, $songs_in_playlist ),
					mdjm_event_length( $mdjm_event_id ) ); ?></p>
        
        	<?php foreach( $playlist as $category => $songs ) : ?>
            	
				<?php $songs_in_category = mdjm_count_songs( $mdjm_event_id, $category ); ?>
                
                <div class="row">
                
                    <div class="category"><?php echo $category; ?> <span class="cat-count">(<?php echo $songs_in_category; ?> <?php echo _n( 'song', 'songs', $songs_in_category, 'mobile-dj-manager' ); ?>)</span></div>
                    
                </div>
                
                <?php foreach( $songs as $entry ) : ?>
                
                	<div class="row mdjm-playlist-entry mdjm-playlist-entry-<?php echo $entry->id; ?>">
                    	<div class="mdjm-playlist-song col"><?php echo $entry->song; ?></div>
                        
                        <div class="mdjm-playlist-artist col"><?php echo $entry->song; ?></div>
                        
                        <div class="mdjm-playlist-info col">
							<?php if( $category == 'Guest Added' ) : ?>
                            
                            	<?php echo $entry->added_by; ?>
                            
							<?php else : ?>
								
								<?php if( ! empty( $entry->info ) ) : ?>
									
									<?php echo stripslashes( $entry->info ); ?>
                                
								<?php else : ?>
                                	
									<?php echo '&ndash;'; ?>
								
								<?php endif; // endif( ! empty( $entry->info ) ) ?>
                            
							<?php endif; // endif( $category == 'Guest Added' ) ?>
                        </div>
                        
                        <div class="mdjm-playlist-remove last"><a href="<?php echo wp_nonce_url( mdjm_get_formatted_url( mdjm_get_option( 'playlist_page' ) ) . 'mdjm_action=remove_playlist_song&song_id=' . $entry->id, 'remove_song', 'mdjm_nonce' ); ?>"><?php _e( 'Remove' ); ?></a>
                    </div>
                </div>
                
                <?php endforeach; // end foreach( $songs as $entry ) ?>
                
            <?php endforeach; // end foreach( $playlist as $entry ) ?>
        	
        <?php endif; // endif( $playlist ) ?>
    	
        <?php do_action( 'mdjm_playlist_entries_bottom', $mdjm_event_id ); ?>
    </div><!-- end mdjm-playlist-entries -->
    
    	
    <div id="mdjm-playlist-footer">
    	<?php do_action( 'mdjm_playlist_footer_top', $mdjm_event_id ); ?>
    	<?php do_action( 'mdjm_playlist_footer_bottom', $mdjm_event_id ); ?>
    </div><!-- end mdjm-playlist-footer -->
    
	<?php do_action( 'mdjm_playlist_bottom', $mdjm_event_id ); ?>
</div><!-- end mdjm-playlist-wrapper -->