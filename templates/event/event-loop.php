<?php
/**
 * This template is used to display the current users (Client) list of events.
 *
 * @version 		1.0
 * @author			Mike Howard
 * @since			1.3
 * @content_tag		client
 * @content_tag		event
 * @shortcodes		Supported
 *
 * Do not customise this file!
 * If you wish to make changes, copy this file to your theme directory /theme/mdjm-templates/event/event-loop.php
 */

?>
<?php do_action( 'mdjm_event_loop_before_event' ); ?>
<div id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
	<table class="mdjm-event-overview">
		<tr>
			<th class="mdjm-event-heading">{event_name}<br />
				{event_date}
			</th>
            <th class="mdjm-event-heading right-align"><?php _e( 'ID:', 'mobile-dj-manager' ); ?> {contract_id}<br />
				<?php _e( 'Status:', 'mobile-dj-manager' ); ?> {event_status}
            </th>
		</tr>
		<tr>
			<td><span class="mdjm-event-label"><?php _e( 'Time', 'mobile-dj-manager' ); ?></span><br />
				{start_time} - {end_time}
			</td>
            <td rowspan="3" class="top-align"><span class="mdjm-event-label"><?php _e( 'Venue', 'mobile-dj-manager' ); ?></span><br />
				{venue}
                <br />
                {venue_full_address}
			</td>
		</tr>
        <tr>
        	<td><span class="mdjm-event-label"><?php _e( 'Event Type', 'mobile-dj-manager' ); ?></span><br />
				{event_type}
			</td>
        </tr>
        <tr>
        	<td><span class="mdjm-event-label"><?php _e( 'Cost Summary', 'mobile-dj-manager' ); ?></span><br />
				<?php _e( 'Total Cost:', 'mobile-dj-manager' ); ?> {total_cost}<br />
                <?php echo mdjm_get_option( 'deposit_label', __( 'Deposit', 'mobile-dj-manager' ) ) ?>: {deposit} ({deposit_status})<br />
                <?php echo mdjm_get_option( 'balance_label', __( 'Balance Due', 'mobile-dj-manager' ) ) ?> <?php _e( 'Remaining', 'mobile-dj-manager' ); ?>: {balance}
			</td>
        </tr>
	</table>
</div>    
<?php do_action( 'mdjm_event_loop_after_event' ); ?>