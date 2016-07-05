<?php
/**
 * This template is used to display the credit card fields on the payment form.
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
global $mdjm_event; ?>

        <fieldset id="mdjm_cc_fields" class="mdjm-do-validate">
            <legend><?php _e( 'Debit/Credit Card Info', 'mobile-dj-manager' ); ?></legend>

			<?php if ( is_ssl() ) : ?>
                <div id="mdjm_secure_site_wrapper">
                    <span class="padlock"></span>
                    <span><?php _e( 'This is a secure SSL encrypted payment.', 'mdjm-stripe-payments' ); ?></span>
                </div>
            <?php endif; ?>

        </fieldset>
