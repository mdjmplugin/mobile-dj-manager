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
		<div class="mdjm-event-heading">{event_name} - {event_date}</div>
	</div>


	<div class="mdjm-singleevent-overview">

		<div class="single-event-field half">     
			<strong> <?php _e( 'Status:', 'mobile-dj-manager' ); ?></strong><br/>
			{event_status}
		</div>

		<div class="single-event-field half">     
			<strong><?php printf( __( 'Event: ', 'mobile-dj-manager' ), mdjm_get_label_singular() ); ?></strong>
			<br/>{event_type}
		</div>

		<div class="single-event-field half">     
			<strong><?php _e( 'Event Date: ', 'mobile-dj-manager' ); ?></strong><br/>{event_date} - {start_time}
		</div>

		<div class="single-event-field half">     
			<strong>{balance_label} <?php _e( 'Remaining', 'mobile-dj-manager' ); ?>:</strong><br/> {balance}
		</div>

		<div class="single-event-button">
			<a class="mdjm-action-button mdjm-action-button-more" href="{event_url}"><?php esc_html_e( 'View Event Details', 'mobile-dj-manager' ); ?></a>
		</div>
	</div>
</div>
<?php do_action( 'mdjm_post_event_loop' ); ?>
