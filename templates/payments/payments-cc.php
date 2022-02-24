<?php
/**
 * This plugin utilizes Open Source code. Details of these open source projects along with their licenses can be found below.
 * We acknowledge and are grateful to these developers for their contributions to open source.
 *
 * Project: mobile-dj-manager https://github.com/deckbooks/mobile-dj-manager
 * License: (GNU General Public License v2.0) https://github.com/deckbooks/mobile-dj-manager/blob/master/license.txt
 *
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
	<p class="mdjm-default-form-text"><?php esc_html_e( "Once you have selected your Payment Amount, click $pay_now to checkout", 'mobile-dj-manager' ); ?></p>
	<?php do_action( 'mdjm_after_default_payments_form' ); ?>
