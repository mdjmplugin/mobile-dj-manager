<?php
/**
 * This template is used to display the PayPal fields on the payment form.
 *
 * @version         1.0
 * @author          Mike Howard
 * @since           1.3
 * @content_tag     client
 * @content_tag     event
 * @shortcodes      Not Supported
 *
 * Do not customise this file!
 * If you wish to make changes, copy this file to your theme directory /theme/mdjm-templates/payments/payments-paypal.php
 */
global $mdjm_event;
	$pay_now = mdjm_get_payment_button_text(); ?>

	<?php do_action( 'mdjm_pre_paypal_payments_form' ); ?>
    <div class="mdjm-alert mdjm-alert-error mdjm-hidden"></div>
	<p class="mdjm-paypal-form-text"><?php esc_html_e( "Once you have selected your Payment Amount, click $pay_now to checkout with PayPal", 'mdjm-paypal-standard' ); ?></p>
	<?php do_action( 'mdjm_post_paypal_payments_form' ); ?>
