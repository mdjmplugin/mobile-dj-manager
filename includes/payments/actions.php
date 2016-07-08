<?php
/**
 * Payment Actions
 *
 * @package     MDJM
 * @subpackage  Payments
 * @copyright   Copyright (c) 2016, Mike Howard
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.3.8
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) )
	exit;

/**
 * Loads a payment gateway via AJAX
 *
 * @since	1.3.8
 * @return	void
 */
function mdjm_load_ajax_gateway() {
	if ( isset( $_POST['mdjm_payment_mode'] ) ) {
		do_action( 'mdjm_payment_form' );
		exit();
	}
} // mdjm_load_ajax_gateway
add_action( 'wp_ajax_mdjm_load_gateway', 'mdjm_load_ajax_gateway' );
add_action( 'wp_ajax_nopriv_mdjm_load_gateway', 'mdjm_load_ajax_gateway' );
