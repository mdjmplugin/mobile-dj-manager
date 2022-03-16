<?php
/**
 * This plugin utilizes Open Source code. Details of these open source projects along with their licenses can be found below.
 * We acknowledge and are grateful to these developers for their contributions to open source.
 *
 * Project: mobile-dj-manager https://github.com/deckbooks/mobile-dj-manager
 * License: (GNU General Public License v2.0) https://github.com/deckbooks/mobile-dj-manager/blob/master/license.txt
 * This template is used to display the current users (Client) list of events.
 *
 * @version 1.0
 * @author Mike Howard, Jack Mawhinney, Dan Porter
 * @content_tag client
 * @content_tag event
 * @shortcodes Supported
 *
 * Do not customise this file!
 * If you wish to make changes, copy this file to your theme directory /theme/mdjm-templates/event/event-loop.php
 * @package is the package type
 */

global $mdjm_event;
?>
<?php do_action( 'mdjm_pre_event_loop' ); ?>
<div id="post-<?php echo esc_attr( $mdjm_event->ID ); ?>" class="event-loop <?php echo esc_attr( $mdjm_event->post_status ); ?>">
	<div class="single-event-field full">
		
<table class="table-full">
		<tr><div class="mdjm-event-heading">{event_name} - {event_date}</tr></div><br/>
		<tr>
			<td><div class="table-column"><strong>Status: </strong> {event_status}</td></div>
			<td><div class="table-column"><strong><?php printf(__('Function:', 'mobile-dj-manager'), mdjm_get_label_singular() ); ?></strong> {event_type}</td></div>
		</tr>
		<tr>
			<td><div class="table-column"><strong>Start Date: </strong>{event_date} - {start_time}</td></div>
			<td><div class="table-column"><strong>Remaining Balance: </strong> {balance}</td></div>
		</tr>
		<tr class="table-row-full">
			<td colspan="2"><div class="table-header"><a class="table-header-text" href="{event_url}">View Event Details</a></div></td>
		</tr>
	</table>

</div>
<?php do_action( 'mdjm_post_event_loop' ); ?>
