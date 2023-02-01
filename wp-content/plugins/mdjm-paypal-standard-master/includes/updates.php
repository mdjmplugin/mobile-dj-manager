<?php
/**
 * Updates Function
 *
 * @package     MDJM PayPal Standard
 * @subpackage  Functions
 * @copyright   Copyright (c) 2016, Mike Howard
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.3
*/

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Determine if any updates are required.
 * If so, execute them/
 *
 * @since   1.3
 */
function mdjm_paypal_updates() {
	
	$current_version = get_option( 'mdjm_paypal_std_version' );
	
	if ( $current_version < MDJM_PAYPAL_STD_VERSION ) {           
		mdjm_run_paypal_updates( $current_version );
	} else {
		return;
	}

} // mdjm_paypal_updates
add_action( 'init', 'mdjm_paypal_updates' );


/**
 * Run update procedures.
 *
 *
 *
 *
 */
function mdjm_run_paypal_updates( $current_version ) {
	
} // mdjm_run_paypal_updates
