<?php
/**
 * This template is used to display the current users (Client) list of events.
 *
 * @version 		1.0
 * @author			Mike Howard
 * @since			1.3
 * @content_tag		{client_*}
 * @content_tag		{event_*}
 * @shortcodes		Supported
 *
 * Do not customise this file!
 * If you wish to make changes, copy this file to your theme directory /theme/templates/event-loop.php
 */

?>
	<?php do_action( 'mdjm_event_loop_before_event' ); ?>
            
    <table class="mdjm-event-overview">
        <tr>
            <th>{event_name}<br />
            	{event_date}
            </th>
        </tr>
        <tr>
            <td>{event_start} - {event_finish}</td>
        </tr>
    </table>
        
    <?php do_action( 'mdjm_event_loop_after_event' ); ?>