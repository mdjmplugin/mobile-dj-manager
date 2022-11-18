<?php
/**
 * This template is used to display the items which can be paid for on the payment form.
 *
 * @version         1.0
 * @author          Mike Howard, Jack Mawhinney, Dan Porter
 * @content_tag     {client_*}
 * @content_tag     {event_*}
 * @shortcodes      Supported
 *
 * Do not customise this file!
 * If you wish to make changes, copy this file to your theme directory /theme/mdjm-templates/payments/payments-items.php
 */
require_once MDJM_PLUGIN_DIR . '/includes/admin/settings/register-settings.php';
require_once MDJM_PLUGIN_DIR . '/includes/admin/events/playlist-page.php';

global $mdjm_event;
$deposit_disabled = '';
if ( 'Paid' == $mdjm_event->get_deposit_status() ) {
	$deposit_disabled = ' disabled = "true"';
}

$balance_disabled = '';
if ( 'Paid' == $mdjm_event->get_balance_status() ) {
	$balance_disabled = ' disabled = "true"';
}
$other_amount_style = ' style="display: none;"';
if ( ! empty( $balance_disabled ) && ! empty( $deposit_disabled ) ) {
	$other_amount_style = '';
}

if ( empty( $deposit_disabled ) ) {
	$selected = 'deposit';
} elseif ( empty( $balance_disabled ) ) {
	$selected = 'balance';
} else {
	$selected = 'part_payment';
}

?>
<fieldset id="mdjm-payment-value">
	<legend><?php esc_html_e( 'Select Payment Amount', 'mobile-dj-manager' ); ?></legend>
	<p class="mdjm-payment-amount">
		<input type="radio" name="mdjm_payment_amount" data-amount="<?php echo $mdjm_event->get_remaining_deposit();?>" id="mdjm-payment-deposit" value="deposit"<?php echo $deposit_disabled; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?><?php checked( $selected, 'deposit' ); ?> /> <?php echo esc_html( mdjm_get_deposit_label() ); ?> &ndash; <?php echo mdjm_currency_filter( mdjm_format_amount( $mdjm_event->get_remaining_deposit() ) ); ?><br />

		<input type="radio" name="mdjm_payment_amount" data-amount="<?php echo $mdjm_event->get_balance();?>" id="mdjm-payment-balance" value="balance"<?php echo $balance_disabled; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?><?php checked( $selected, 'balance' ); ?> /> <?php echo esc_html( mdjm_get_balance_label() ); ?> &ndash; <?php echo mdjm_currency_filter( mdjm_format_amount( $mdjm_event->get_balance() ) ); ?><br />

		<input type="radio" name="mdjm_payment_amount" id="mdjm-payment-part" min="1" max="1000000" step="any" data-amount="50.00" value="part_payment"<?php checked( $selected, 'part_payment' ); ?> /> <?php echo esc_html( mdjm_get_other_amount_label() ); ?> <span id="mdjm-payment-custom"<?php echo $other_amount_style; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>><?php echo esc_html( mdjm_currency_symbol() ); ?><input type="number" class="mdjm_other_amount_input mdjm-input" name="part_payment" id="part-payment" value="50.00"/></span>
   <span class="mdjm-description"><?php printf( esc_html__( 'To pay a custom amount, select %s and enter the value into the text field.', 'mobile-dj-manager' ), esc_html( mdjm_get_other_amount_label() ) ); ?></span><br/>
 </p>
</fieldset>
<?php if ( mdjm_get_option( 'enable_bacs') === "1" ){?>
  <fieldset id="mdjm-payment-value">
    <legend><?php printf( esc_html__( 'Pay by BACS', 'mobile-dj-manager' ) ); ?></legend>

    <?php if ( mdjm_get_option( 'bacs_info' ) ){?>
      <small><?php echo esc_html( mdjm_get_option( 'bacs_info') ) ?></small>
    <?php }?> <br />

    <span style="font-weight: bold"><?php printf( esc_html__( 'Account Name: ' ) ); ?></span><?php printf( esc_html( mdjm_get_option( 'bank_detail_name', '' ) ) ); ?><br/>
    <span style="font-weight: bold"><?php printf( esc_html__( 'Sort Code: ' ) ); ?></span><?php printf( esc_html( mdjm_get_option( 'bank_detail_sortcode', '' ) ) ); ?><br/>
    <span style="font-weight: bold"><?php printf( esc_html__( 'Account Number: ' ) ); ?></span><?php printf( esc_html( mdjm_get_option( 'bank_detail_accnumber', '' ) ) ); ?><br/>
    <span style="font-weight: bold"><?php printf( esc_html__( 'Reference: ' ) ) ?></span><?php printf( esc_html( mdjm_get_event_contract_id( $mdjm_event->ID ) ) )?></span> 


  </span>
</fieldset>
<?php } ?>

<script>
	jQuery(document).ready(function($){
    $('input#part-payment').on('input',function(){
     var part_val = $(this).val();
     $('#mdjm-payment-part').attr('data-amount', part_val);
   });
  });
</script>