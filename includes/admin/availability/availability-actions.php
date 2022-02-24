<?php
/**
 * This plugin utilizes Open Source code. Details of these open source projects along with their licenses can be found below.
 * We acknowledge and are grateful to these developers for their contributions to open source.
 *
 * Project: mobile-dj-manager https://github.com/deckbooks/mobile-dj-manager
 * License: (GNU General Public License v2.0) https://github.com/deckbooks/mobile-dj-manager/blob/master/license.txt
 *
 * @author: Mike Howard, Jack Mawhinney, Dan Porter
 *
 * Contains all admin availability related functions
 *
 * @package     MDJM
 * @subpackage  Availability
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Performs an employee availability check.
 *
 * @since   1.5.6
 * @param   array $data   $_POST data.
 * @return  void
 */
function mdjm_employee_availability_check_action( $data ) {
	if ( ! isset( $data['mdjm_nonce'] ) || ! wp_verify_nonce( $data['mdjm_nonce'], 'employee_availability_check' ) ) {
		wp_die( esc_html__( 'Security failure', 'mobile-dj-manager' ) );
	}

	if ( ! empty( $data['check_date'] ) ) {

	}

	$return_url = add_query_arg(
		array(
			'post_type'    => 'mdjm-event',
			'page'         => 'mdjm-availability',
			'mdjm-message' => $message,
		),
		admin_url( 'edit.php' )
	);

	wp_safe_redirect( $return_url );
	exit;
} // mdjm_employee_availability_check_action
add_action( 'mdjm_employee_availability_lookup', 'mdjm_employee_availability_check_action' );
