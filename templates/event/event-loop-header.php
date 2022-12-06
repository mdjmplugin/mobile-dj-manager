<?php
/**
 * This template is used to display the header section during the current users (Client) list of events.
 *
 * @version 1.0
 * @author Mike Howard, Jack Mawhinney, Dan Porter
 * @content_tag {client_*}
 * @shortcodes Supported
 *
 * Do not customise this file!
 * If you wish to make changes, copy this file to your theme directory /theme/mdjm-templates/event/event-loop-header.php
 * @package is the package type
 */

?>
<div id="mdjm-event-loop-header">
	<?php do_action( 'mdjm_event_loop_before_header' ); ?>
	<p>
	<?php
	printf(
		esc_html( __( 'Hello %1$s and welcome to the %2$s %3$s.', 'mobile-dj-manager' ) ),
		'{client_firstname}',
		'{company_name}',
		'{application_name}'
	);
	?>
	</p>
	<p><?php printf( esc_html__( 'The %s you have scheduled with us are listed below...', 'mobile-dj-manager' ), esc_html__( mdjm_get_label_plural( true ) ) ); ?></p>
	<?php do_action( 'mdjm_event_loop_after_header' ); ?>
</div>
