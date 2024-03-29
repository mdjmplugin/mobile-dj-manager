<?php
/**
 * This template is used when no alternative is provided.
 *
 * @version         1.2
 * @author          Mike Howard, Jack Mawhinney, Dan Porter
 * @content_tag     {client_*}
 * @content_tag     {event_*}
 * @shortcodes      Supported
 *
 * Do not customise this file!
 * If you wish to make changes, copy this file to your theme directory /theme/mdjm-templates/payments/payments-cc.php
 */
global $mdjm_event;
	$pay_now = mdjm_get_option( 'payment_button' ); 

if( isset( $_POST['payment_event_id'] ) ){
    $event_id = esc_html( $_POST['payment_event_id'] );
} elseif( isset( $_GET['event_id'] ) ){
    $event_id = esc_html( $_GET['event_id'] );
} else {
   $event_id = printf( esc_html__( 'Please use a recognisable reference.', 'mobile-dj-manager' ) );
}?>

	<?php do_action( 'mdjm_pre_default_payments_form' ); ?>
	<div class="mdjm-alert mdjm-alert-error mdjm-hidden"></div>
	<?php do_action( 'mdjm_after_default_payments_form' ); ?>
