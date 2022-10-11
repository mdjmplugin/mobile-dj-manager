<?php
/**
 * This plugin utilizes Open Source code. Details of these open source projects along with their licenses can be found below.
 * We acknowledge and are grateful to these developers for their contributions to open source.
 *
 * Project: mobile-dj-manager https://github.com/deckbooks/mobile-dj-manager
 * License: (GNU General Public License v2.0) https://github.com/deckbooks/mobile-dj-manager/blob/master/license.txt
 * This template is used when the current logged in client accesses the playlist page but has no events.
 *
 * @version         1.1
 * @author          Mike Howard, Jack Mawhinney, Dan Porter
 * @content_tag     {client_*}
 * @shortcodes      Not Supported
 *
 * Do not customise this file!
 * If you wish to make changes, copy this file to your theme directory /theme/mdjm-templates/playlist/playlist-noevent.php
 */

$notice = sprintf(
	__( "We haven't been able to locate your %1\$s. Please return to our <a href='%2\$s'>%3\$s</a> page to see a list of your %4\$s.", 'mobile-dj-manager' ), // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	esc_html( mdjm_get_label_singular( true ) ),
	'{application_home}',
	'{application_name}',
	esc_html( mdjm_get_label_plural( true ) )
);
?>
<div id="client-playlist-no-events">
	<p><?php echo $notice; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></p>
</div>
