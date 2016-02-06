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
?>
<div id="mdjm-playlist-wrapper">

	<div id="mdjm-playlist-header">
    
    <p><?php printf( __( 'The %s playlist management system enables you to give %s (your %s) an idea of the types of songs you would like played during your event on %s.', 'mobile-dj-manager' ),
			mdjm_get_option( 'company_name' ),
			'{dj_firstname}',
			mdjm_get_option( 'artist', __( 'DJ', 'mobile-dj-manager' ) ),
			'{event_date}' ); ?></p>
            
    <p><?php printf( __( "Don't forget that you can invite your guests to add their suggestions to your playlist too. They won't be able to see any existing entries, and you will be able to filter through their suggestions if you do not feel they are suitable. Just tell them to visit <a href='%s'>%s</a> to get started.", 'mobile-dj-manager' ),
				'{guest_playlist_url}',
				'{guest_playlist_url}' ); ?></p>
    
    </div><!-- end mdjm-playlist-header -->
    
    <div id="mdjm-playlist-footer">
    
    </div><!-- end mdjm-playlist-footer -->

</div>