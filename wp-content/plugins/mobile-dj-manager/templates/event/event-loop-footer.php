<?php
/**
 * This plugin utilizes Open Source code. Details of these open source projects along with their licenses can be found below.
 * We acknowledge and are grateful to these developers for their contributions to open source.
 *
 * Project: mobile-dj-manager https://github.com/deckbooks/mobile-dj-manager
 * License: (GNU General Public License v2.0) https://github.com/deckbooks/mobile-dj-manager/blob/master/license.txt
 *
 * This template is used to display the footer section during the current users (Client) list of events.
 *
 * @version 1.0
 * @author Mike Howard, Jack Mawhinney, Dan Porter
 * @content_tag {client_*}
 * @shortcodes Supported
 *
 * Do not customise this file!
 * If you wish to make changes, copy this file to your theme directory /theme/mdjm-templates/event/event-loop-footer.php
 * @package MDJM
 */

?>
<div id="mdjm-event-loop-footer">
	<?php do_action( 'mdjm_event_loop_before_footer' ); ?>

	<?php do_action( 'mdjm_event_loop_after_footer' ); ?>
</div>
