<?php
/**
 * This template is used to display the BACS fields on the payment form.
 *
 * @author          Dan Porter, Jack Mawhinney
 * @since           1.0
 * @content_tag     client
 * @content_tag     event
 * @shortcodes      Not Supported
 * @package         mobile-dj-manager
 *
 * Do not customise this file!
 * If you wish to make changes, copy this file to your theme directory /theme/mdjm-templates/payments/payments-bacs.php
 */
if( isset( $_POST['payment_event_id'] ) ){
    $event_id = esc_html( $_POST['payment_event_id'] );
} elseif( isset( $_GET['event_id'] ) ){
    $event_id = esc_html( $_GET['event_id'] );
} else {
   $event_id = printf( esc_html__( 'Please use a recognisable reference.', 'mobile-dj-manager' ) );
}
?>
<fieldset id="mdjm-payment-value">
    <legend><?php printf( esc_html__( 'Bank Details', 'mobile-dj-manager' ) ); ?></legend><br />
    <span style="font-weight: bold"><?php printf( esc_html__( 'Account Name: ' ) ); ?></span><?php printf( esc_html( mdjm_get_option( 'bank_detail_name', '' ) ) ); ?><br/>
    <span style="font-weight: bold"><?php printf( esc_html__( 'Sort Code: ' ) ); ?></span><?php printf( esc_html( mdjm_get_option( 'bank_detail_sortcode', '' ) ) ); ?><br/>
    <span style="font-weight: bold"><?php printf( esc_html__( 'Account Number: ' ) ); ?></span><?php printf( esc_html( mdjm_get_option( 'bank_detail_accnumber', '' ) ) ); ?><br/>
    <span style="font-weight: bold"><?php printf( esc_html__( 'Reference: ' ) ) ?></span><?php printf( esc_html( mdjm_get_event_contract_id( $event_id ) ) )?></span> 


</span>
</fieldset>
