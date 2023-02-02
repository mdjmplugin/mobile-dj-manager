<?php
/**
 * This template is used to display the details of a single event to the client.
 *
 * @version     1.0.1
 * @author Mike Howard, Jack Mawhinney, Dan Porter
 * @content_tag   {client_*}
 * @content_tag   {event_*}
 * @shortcodes    Supported
 *
 * Do not customise this file!
 * If you wish to make changes, copy this file to your theme directory /theme/mdjm-templates/event/event-single.php
 */

global $mdjm_event;
?>
<?php do_action( 'mdjm_pre_event_detail', $mdjm_event->ID, $mdjm_event ); ?>
<div id="post-<?php echo $mdjm_event->ID; ?>" class="mdjm-s-event mdjm-<?php echo $mdjm_event->post_status; ?>">
  
  <?php do_action( 'mdjm_print_notices'); ?>

  <p>
  <?php
	printf(
		__( 'Details of your %1$s taking place on %2$s are shown below.', 'mobile-dj-manager' ),
		mdjm_get_label_singular( true ),
		'{event_date}'
	);
	?>
  </p>

  <p>
  <?php
	printf(
		__( 'Please confirm the details displayed are correct or <a href="%s">contact us</a> with any adjustments.', 'mobile-dj-manager' ),
		'{contact_page}'
	);
	?>
  </p>

  <?php
	/**
	 * Display event action buttons
	 */
	?>
  <div class="mdjm-action-btn-container">{event_action_buttons}</div>

  <?php
	/**
	 * Display event details
	 */
	?>
  <?php do_action( 'mdjm_pre_event_details', $mdjm_event->ID, $mdjm_event ); ?>
<br/><br/><br/>
	 <table class="table-full">
		<tr><div class="mdjm-event-heading">{event_name} - {event_date}</tr></div><br/>
		<tr>
			<td><div class="table-column"><strong>Status: </strong> {event_status}</td></div>
			<td><div class="table-column"><strong><?php printf(__('Function:', 'mobile-dj-manager'), mdjm_get_label_singular() ); ?></strong> {event_type}</td></div>
		</tr>
		<tr>
			<td><div class="table-column"><strong>Start Time: </strong> {start_time}</td></div>
			<td><div class="table-column"><strong>End Time: </strong> {end_time}</td></div>
		</tr>
		<tr class="table-row-full">
			<td colspan="2"><div class="table-header">Package Details</div></td>
		</tr>
			<td colspan="2"><div class="table-column">{event_package}</td></div>
		</tr>
		<tr class="table-row-full">
			<td colspan="2"><div class="table-header">Add-Ons Selected</div></td>
		</tr>
		</tr>
			<td colspan="2"><div class="table-column">{event_addons}</td></div>
		</tr>
		<tr class="table-row-full">
			<td colspan="2"><div class="table-header">Pricing</div></td>
		</tr>
		</tr>
			<td colspan="2"><div class="table-column"><strong>Total Cost: </strong>{total_cost}<br />
				<strong>{deposit_label}: </strong> {deposit} ({deposit_status})<br />
				<strong>{balance_label} Remaining: </strong>{balance}</td></div>
		</tr>
		<tr class="table-row-full">
			<td colspan="2"><div class="table-header">Your Details</div></td>
		</tr>
		<tr>
			<td>
				<div class="table-column"><strong>Name: </strong>{client_fullname}<br />
				<strong>Phone Number: </strong> {client_primary_phone} <br />
				<strong>Email Address: </strong>{client_email}</div><br />
			</td>
			<td>
				<div class="table-column"><strong>Address: <br/></strong>{client_full_address}</div>
			</td>
		</tr>
		<tr class="table-row-full">
			<td colspan="2"><div class="table-header">Venue Details</div></td>
		</tr>
		<tr>
			<td>
				<div class="table-column"><strong>Venue Name: </strong>{venue}<br />
			</td>
			<td>
				<div class="table-column"><strong>Address: <br/></strong>{venue_full_address}</div>
			</td>
		</tr>
		<tr class="table-row-full">
			<td colspan="2"><div class="table-header">Function Notes</div></td>
		</tr>
		<tr>
			<td colspan="2">
				<div class="table-column">{client_notes}</div>
			</td>
		</tr>
	</table>
  <?php do_action( 'mdjm_post_event_details', $mdjm_event->ID, $mdjm_event ); ?>
