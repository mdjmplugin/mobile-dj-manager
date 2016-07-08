<?php
/**
 * This template is used when no alternative is provided.
 *
 * @version 		1.0
 * @author			Mike Howard
 * @since			1.3.8
 * @content_tag		{client_*}
 * @content_tag		{event_*}
 * @shortcodes		Supported
 *
 * Do not customise this file!
 * If you wish to make changes, copy this file to your theme directory /theme/mdjm-templates/payments/payments-cc.php
 */
global $mdjm_event;
	$pay_now = mdjm_get_payment_button_text(); ?>

	<?php do_action( 'mdjm_pre_default_payments_form' ); ?>
    <div class="mdjm-alert mdjm-alert-error mdjm-hidden"></div>
	<p class="mdjm-default-form-text"><?php _e( "Once you have selected your Payment Amount, click $pay_now to checkout", 'mobile-dj-manager' ); ?></p>
	<?php do_action( 'mdjm_pre_default_payments_form' ); ?>