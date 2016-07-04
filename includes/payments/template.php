<?php

/**
 * Payment Form.
 *
 * @package		MDJM
 * @subpackage	Payments
 * @since		1.3.8
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) )
	exit;

/**
 * Get Payment Form
 *
 * @since	1.3.8
 * @return	str
 */
function mdjm_payment_form()	{
	global $mdjm_event;

	$payment_mode = mdjm_get_chosen_gateway();
echo $payment_mode;
	ob_start();
		echo '<div id="mdjm_payment_wrap">';
			do_action( 'mdjm_print_notices' );
			echo '<p class="head-nav"><a href="' . mdjm_get_event_uri( $mdjm_event->ID ) . '">' . __( 'Back to Event', 'mobile-dj-manager' ) . '</a></p>';
			mdjm_payment_items();
?>
			<div id="mdjm_payment_form_wrap" class="mdjm_clearfix">
				<?php do_action( 'mdjm_before_purchase_form' ); ?>
				<form id="mdjm_payment_form" class="mdjm_form" action="" method="POST" autocomplete="off">
					<?php
					/**
					 * Hooks in at the top of the payment form
					 *
					 * @since	1.3.8
					 */
					do_action( 'mdjm_payment_form_top' );

					//if ( mdjm_show_gateways() ) {
						do_action( 'mdjm_payment_mode_select'  );
					//} else {
					//	do_action( 'mdjm_payment_form' );
					//}

					/**
					 * Hooks in at the bottom of the checkout form
					 *
					 * @since 1.0
					 */
					do_action( 'mdjm_checkout_form_bottom' )
					?>
				</form>
				<?php do_action( 'mdjm_after_purchase_form' ); ?>
			</div><!--end #mdjm_payment_form_wrap-->
<?php
		echo '</div><!--end #mdjm_payment_wrap-->';

	return ob_get_clean();

} // mdjm_payment_form

/**
 * Display the items that the client can pay for.
 *
 * @since	1.3.8
 * @return	void
 */
function mdjm_payment_items()	{
	global $mdjm_event;

	do_action( 'mdjm_before_payment_items' );
	echo '<form id="mdjm_payment_items_form" method="post" autocomplete="off" class="mdjm_form">';
		echo '<div id="mdjm_payment_wrap">';
			mdjm_get_template_part( 'payments' );
		echo '</div>';
	echo '</form>';
	do_action( 'mdjm_after_payment_items' );
} // mdjm_payment_items

/**
 * Renders the payment mode form by getting all the enabled payment gateways and
 * outputting them as radio buttons for the user to choose the payment gateway. If
 * a default payment gateway has been chosen from the MDJM Settings, it will be
 * automatically selected.
 *
 * @since 	1.3.8
 * @return	void
 */
function mdjm_payment_mode_select() {
	$gateways = mdjm_get_enabled_payment_gateways( true );
	$page_URL = mdjm_get_current_page_url();
	do_action( 'mdjm_payment_mode_top' ); ?>
		<fieldset id="mdjm_payment_mode_select">
			<?php do_action( 'mdjm_payment_mode_before_gateways_wrap' ); ?>
			<div id="mdjm-payment-mode-wrap">
				<span class="mdjm-payment-mode-label"><?php _e( 'Select Payment Method', 'mobile-dj-manager' ); ?></span><br/>
				<?php

				do_action( 'mdjm_payment_mode_before_gateways' );

				foreach ( $gateways as $gateway_id => $gateway )	{

					$checked = checked( $gateway_id, mdjm_get_default_gateway(), false );
					$checked_class = $checked ? ' mdjm-gateway-option-selected' : '';
					echo '<label for="mdjm-gateway-' . esc_attr( $gateway_id ) . '" class="mdjm-gateway-option' . $checked_class . '" id="mdjm-gateway-option-' . esc_attr( $gateway_id ) . '">';
						echo '<input type="radio" name="payment-mode" class="mdjm-gateway" id="mdjm-gateway-' . esc_attr( $gateway_id ) . '" value="' . esc_attr( $gateway_id ) . '"' . $checked . '>' . esc_html( $gateway['checkout_label'] );
					echo '</label>';
				}

				do_action( 'mdjm_payment_mode_after_gateways' );

				?>
			</div>
			<?php do_action( 'mdjm_payment_mode_after_gateways_wrap' ); ?>
		</fieldset>
		<fieldset id="mdjm_payment_mode_submit" class="mdjm-no-js">
			<p id="mdjm-next-submit-wrap">
				<?php echo mdjm_checkout_button_next(); ?>
			</p>
		</fieldset>
	<div id="mdjm_payment_form_wrap"></div><!-- the fields are loaded into this-->
	<?php do_action('mdjm_payment_mode_bottom');
} // mdjm_payment_mode_select
add_action( 'mdjm_payment_mode_select', 'mdjm_payment_mode_select' );
