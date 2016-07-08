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

	ob_start();
		echo '<div id="mdjm_payment_wrap">';
			do_action( 'mdjm_print_notices' );
			echo '<p class="head-nav"><a href="' . mdjm_get_event_uri( $mdjm_event->ID ) . '">' . __( 'Back to Event', 'mobile-dj-manager' ) . '</a></p>';
?>
			<div id="mdjm_payments_form_wrap" class="mdjm_clearfix">
				<?php do_action( 'mdjm_before_purchase_form' ); ?>
				<form id="mdjm_payment_form" class="mdjm_form" action="" method="POST" autocomplete="off">
                    <input type="hidden" name="event_id" id="mdjm-event-id" value="<?php echo $mdjm_event->ID; ?>" />
					<?php
					mdjm_payment_items();
					/**
					 * Hooks in at the top of the payment form
					 *
					 * @since	1.3.8
					 */
					do_action( 'mdjm_payment_form_top' );

					if ( mdjm_show_gateways() ) {
						do_action( 'mdjm_payment_mode_select'  );
					} else {
						do_action( 'mdjm_payment_form' );
					}

					/**
					 * Hooks in at the bottom of the checkout form
					 *
					 * @since 1.0
					 */
					do_action( 'mdjm_checkout_form_bottom' )
					?>
				</form>
				<?php do_action( 'mdjm_after_purchase_form' ); ?>
			</div><!--end #mdjm_payments_form_wrap-->
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
	do_action( 'mdjm_before_payment_items' );
		echo '<div id="mdjm_payment_items_wrap">';
			mdjm_get_template_part( 'payments', 'items' );
		echo '</div>';
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
        	<legend><?php _e( 'Select Payment Method', 'mobile-dj-manager' ); ?></legend>
			<?php do_action( 'mdjm_payment_mode_before_gateways_wrap' ); ?>
			<div id="mdjm-payment-mode-wrap">
				<?php

				do_action( 'mdjm_payment_mode_before_gateways' );

				foreach ( $gateways as $gateway_id => $gateway )	{

					$checked = checked( $gateway_id, mdjm_get_default_gateway(), false );
					$checked_class = $checked ? ' mdjm-gateway-option-selected' : '';
					echo '<label for="mdjm-gateway-' . esc_attr( $gateway_id ) . '" class="mdjm-gateway-option' . $checked_class . '" id="mdjm-gateway-option-' . esc_attr( $gateway_id ) . '">';
						echo '<input type="radio" name="payment-mode" class="mdjm-gateway" id="mdjm-gateway-' . esc_attr( $gateway_id ) . '" value="' . esc_attr( $gateway_id ) . '"' . $checked . '>' . esc_html( $gateway['payment_label'] );
					echo '</label>';
				}

				do_action( 'mdjm_payment_mode_after_gateways' );

				?>
			</div>
			<?php do_action( 'mdjm_payment_mode_after_gateways_wrap' ); ?>
		</fieldset>
	<div id="mdjm_payment_form_wrap"></div><!-- the fields are loaded into this-->
	<?php do_action( 'mdjm_payment_mode_bottom' );
} // mdjm_payment_mode_select
add_action( 'mdjm_payment_mode_select', 'mdjm_payment_mode_select' );

/**
 * Renders the Payment Form, hooks are provided to add to the payment form.
 * The default Payment Form rendered displays a list of the enabled payment
 * gateways and a credit card info form if credit cards are enabled.
 *
 * @since	1.3.8
 * @return	str
 */
function mdjm_show_payment_form()	{

	$payment_mode = mdjm_get_chosen_gateway();

	/**
	 * Hooks in at the top of the purchase form
	 *
	 * @since	1.3.8
	 */
	do_action( 'mdjm_payment_form_top' );

	do_action( 'mdjm_payment_form_after_user_info' );

	/**
	 * Hooks in before Credit Card Form
	 *
	 * @since	1.3.8
	 */
	do_action( 'mdjm_payment_form_before_cc_form' );


	// Load the credit card form and allow gateways to load their own if they wish
	if ( has_action( 'mdjm_' . $payment_mode . '_cc_form' ) ) {
		do_action( 'mdjm_' . $payment_mode . '_cc_form' );
	} else {
		do_action( 'mdjm_cc_form' );
	}


	/**
	 * Hooks in after Credit Card Form
	 *
	 * @since	1.3.8
	 */
	do_action( 'mdjm_payment_form_after_cc_form' );

	/**
	 * Hooks in at the bottom of the payment form
	 *
	 * @since	1.3.8
	 */
	do_action( 'mdjm_payment_form_bottom' );

} // mdjm_show_payment_form
add_action( 'mdjm_payment_form', 'mdjm_show_payment_form' );

/**
 * Renders the credit card info form.
 *
 * @since	1.3.8
 * @return	void
 */
function mdjm_get_cc_form() {
	ob_start(); ?>

	<?php do_action( 'mdjm_before_cc_fields' ); ?>

		<?php mdjm_get_template_part( 'payments', 'cc' ); ?>

	<?php
	do_action( 'mdjm_after_cc_fields' );

	echo ob_get_clean();
} // mdjm_get_cc_form
add_action( 'mdjm_cc_form', 'mdjm_get_cc_form' );

/**
 * Renders the Payment Submit section
 *
 * @since	1.3.8
 * @return	void
 */
function mdjm_payment_submit() {

	if ( ! mdjm_has_gateway() )	{
		return;
	}

	ob_start(); ?>

	<fieldset id="mdjm_payment_submit">
		<?php do_action( 'mdjm_payment_form_before_submit' ); ?>

		<?php mdjm_payment_hidden_fields(); ?>

		<input type="submit" name="mdjm_payment_submit" id="mdjm-payment-submit" value="<?php echo mdjm_get_payment_button_text(); ?>" />

		<?php do_action( 'mdjm_payment_form_after_submit' ); ?>

	</fieldset>
	<?php echo ob_get_clean();
} // mdjm_payment_submit
add_action( 'mdjm_payment_form_after_cc_form', 'mdjm_payment_submit', 9999 );

/**
 * Renders the hidden Payment fields
 *
 * @since	1.3.8
 * @return	void
 */
function mdjm_payment_hidden_fields() {
?>
	<?php mdjm_action_field( 'event_payment' ); ?>
	<input type="hidden" name="mdjm_gateway" id="mdjm_gateway" value="<?php echo mdjm_get_chosen_gateway(); ?>" />
<?php
} // mdjm_payment_hidden_fields

/**
 * Renders an alert if no gateways are defined.
 *
 * @since	1.3.8
 * @return	void
 */
function mdjm_no_gateway_notice()	{

	if ( mdjm_has_gateway() )	{
		return;
	}

	ob_start();

	$notice = __( 'A gateway must be installed and enabled within MDJM Event Management before payments can be processed.', 'mobile-dj-manager' );
	?>
    <div class="mdjm-alert mdjm-alert-error"><?php echo $notice; ?></div>

	<?php echo ob_get_clean();

} // mdjm_alert_no_gateway
add_action( 'mdjm_before_payment_items', 'mdjm_no_gateway_notice' );
