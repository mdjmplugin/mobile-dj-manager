<?php
/**
 * This plugin utilizes Open Source code. Details of these open source projects along with their licenses can be found below.
 * We acknowledge and are grateful to these developers for their contributions to open source.
 *
 * Project: mobile-dj-manager https://github.com/deckbooks/mobile-dj-manager
 * License: (GNU General Public License v2.0) https://github.com/deckbooks/mobile-dj-manager/blob/master/license.txt
 *
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
  
  <?php do_action( 'mdjm_print_notices' ); ?>

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

  <div id="mdjm-singleevent-details">
	<div class="single-event-field full">
	  <div class="mdjm-event-heading">{event_name} - {event_date}</div>
	</div>


	<div class="mdjm-singleevent-overview">

	  <div class="single-event-field half">     
		<strong> <?php _e( 'Status:', 'mobile-dj-manager' ); ?></strong> {event_status}
	  </div>

	  <div class="single-event-field half">     
		<strong><?php printf( __( 'Function: ', 'mobile-dj-manager' ), mdjm_get_label_singular() ); ?></strong> {event_type}
	  </div>

	  <div class="single-event-field half">     
		<strong><?php _e( 'Function Starts: ', 'mobile-dj-manager' ); ?></strong> {start_time}
	  </div>
	  <div class="single-event-field half">     
		<strong><?php _e( 'Function Ends: ', 'mobile-dj-manager' ); ?></strong> {end_time} ({end_date})
	  </div>
		
	<div class="single-event-field full">     
		<div class="mdjm-heading">Package Details</div>
	  </div>
		
	<div class="single-event-field full">     
		<strong><?php _e( '', 'mobile-dj-manager' ); ?></strong> {event_package}
		<strong><?php _e( 'Invoice Link:', 'mobile-dj-manager' ); ?></strong> {mdjm_cf_married_name}
	  </div>
			
		<div class="single-event-field full">     
		<div class="mdjm-heading">Addons Selected</div>
	  </div>
		
	  <div class="single-event-field full">     
		<strong><?php _e( '', 'mobile-dj-manager' ); ?></strong> {event_addons}
	  </div>

	<div class="single-event-field full">     
		<div class="mdjm-heading">Pricing</div>
	  </div>

	  <div class="single-event-field half">     
		<strong><?php _e( 'Total Cost:', 'mobile-dj-manager' ); ?></strong> {total_cost}<br />
		<strong>{deposit_label}:</strong> {deposit} ({deposit_status})<br />
		<strong>{balance_label} <?php _e( 'Remaining', 'mobile-dj-manager' ); ?>:</strong> {balance}
	  </div>

	  <div class="single-event-field full">     
		<div class="mdjm-heading"><?php _e( 'Your Details', 'mobile-dj-manager' ); ?></div>
	  </div>

	  <div class="single-event-field half">     
		<strong><?php _e( 'Your Name: ', 'mobile-dj-manager' ); ?></strong> {client_fullname}
	  </div>

	  <div class="single-event-field half">     
		<strong><?php _e( 'Phone:', 'mobile-dj-manager' ); ?></strong> {client_primary_phone}
	  </div>

	  <div class="single-event-field half">     
		<strong><?php _e( 'Email: ', 'mobile-dj-manager' ); ?></strong> {client_email}
	  </div>

	  <div class="single-event-field half">     
		<strong><?php _e( 'Address: ', 'mobile-dj-manager' ); ?></strong> {client_full_address}
	  </div>

	  <div class="single-event-field full">     
		<div class="mdjm-heading"><?php _e( 'Venue Details', 'mobile-dj-manager' ); ?></div>
	  </div>

	  <div class="single-event-field half">     
		<strong><?php _e( 'Venue: ', 'mobile-dj-manager' ); ?></strong> {venue}
	  </div>

	  <div class="single-event-field half">     
		<strong><?php _e( 'Address: ', 'mobile-dj-manager' ); ?></strong> {venue_full_address}
	  </div>

	  <div class="single-event-field full">     
		<div class="mdjm-heading"><?php _e( 'Function Notes', 'mobile-dj-manager' ); ?></div>
		{client_notes}
	  </div>

	</div>

  </div>
  <?php do_action( 'mdjm_post_event_details', $mdjm_event->ID, $mdjm_event ); ?>
