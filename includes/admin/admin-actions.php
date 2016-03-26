<?php
/**
 * Admin Actions
 *
 * @package     MDJM
 * @subpackage  Admin/Actions
 * @copyright   Copyright (c) 2016, Mike Howard
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.3
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) )
	exit;

/**
 * Processes all MDJM actions sent via POST and GET by looking for the 'mdjm-action'
 * request and running do_action() to call the function
 *
 * @since 1.0
 * @return void
 */
function mdjm_process_actions() {
	if ( isset( $_POST['mdjm-action'] ) ) {
		do_action( 'mdjm_' . $_POST['edd-action'], $_POST );
	}

	if ( isset( $_GET['mdjm-action'] ) ) {
		do_action( 'mdjm_' . $_GET['mdjm-action'], $_GET );
	}
}
add_action( 'admin_init', 'mdjm_process_actions' );
