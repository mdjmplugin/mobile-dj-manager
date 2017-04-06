<?php
/**
 * This template is used when the current logged in client accesses the playlist page but has no events.
 *
 * @version			1.0
 * @author			Mike Howard
 * @since			1.3
 * @content_tag		{client_*}
 * @shortcodes		Not Supported
 *
 * Do not customise this file!
 * If you wish to make changes, copy this file to your theme directory /theme/mdjm-templates/playlist/playlist-noevent.php
 */
?>
<div id="client-playlist-no-events">
	<p><?php printf( __( "We haven't been able to locate your %s. Please return to our <a href='%s'>%s</a> page to see a list of your %s.", 'mobile-dj-manager' ),
		mdjm_get_label_singular(), '{application_home}', '{application_name}', mdjm_get_label_plural() ); ?></p>    
</div>