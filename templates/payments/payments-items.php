<?php
/**
 * This template is used to display the items which can be paid for on the payment form.
 *
 * @version 		1.0
 * @author			Mike Howard
 * @since			1.3.8
 * @content_tag		{client_*}
 * @content_tag		{event_*}
 * @shortcodes		Supported
 *
 * Do not customise this file!
 * If you wish to make changes, copy this file to your theme directory /theme/mdjm-templates/payments/payments-items.php
 */
global $mdjm_event;
$deposit_disabled = '';
if ( 'Paid' == $mdjm_event->get_deposit_status() )	{
	$deposit_disabled = ' disabled = "true"';
}

$balance_disabled = '';
if ( 'Paid' == $mdjm_event->get_balance_status() )	{
	$balance_disabled = ' disabled = "true"';
}
$other_amount_style = ' style="display: none;"';
if ( ! empty( $balance_disabled ) && ! empty( $deposit_disabled ) )	{
	$other_amount_style = '';
}
?>
<fieldset>
	<legend><?php _e( 'Select Payment Amount', 'mobile-dj-manager' ); ?></legend>
    <p class="mdjm-payment-amount">
        <input type="radio" name="mdjm_payment_amount" id="mdjm-payment-deposit" value="deposit"<?php echo $deposit_disabled; ?> /> <?php echo esc_html( mdjm_get_deposit_label() ); ?> &ndash; <?php echo mdjm_currency_filter( mdjm_format_amount( $mdjm_event->get_remaining_deposit() ) ); ?><br />
    
        <input type="radio" name="mdjm_payment_amount" id="mdjm-payment-balance" value="balance"<?php echo $balance_disabled; ?> /> <?php echo esc_html( mdjm_get_balance_label() ); ?> &ndash; <?php echo mdjm_currency_filter( mdjm_format_amount( $mdjm_event->get_balance() ) ); ?><br />
    
        <input type="radio" name="mdjm_payment_amount" id="mdjm-payment-part" value="part_payment" /> <?php echo mdjm_get_other_amount_label(); ?> <span id="mdjm-payment-custom"<?php echo $other_amount_style; ?>><?php echo mdjm_currency_symbol(); ?>
        <input type="text" class="mdjm_other_amount_input" name="part_payment" placeholder="0.00" value="<?php echo mdjm_sanitize_amount( mdjm_get_option( 'other_amount_default', true, false ) ); ?>" /></span>
    <span class="mdjm-description"><?php printf( __( 'To pay a custom amount, select %s and enter the value into the text field.', 'mobile-dj-manager' ), mdjm_get_other_amount_label() ); ?></span>
    </p>
</fieldset>
