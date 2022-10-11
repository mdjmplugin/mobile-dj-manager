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
 * Process employee actions
 *
 * @package     MDJM
 * @subpackage  Users
 * @since       1.3
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Add a new employee
 *
 * @since   1.3
 * @param   arr $data   $_POST super global
 * @return  void
 */
function mdjm_add_employee_action( $data ) {

	if ( ! wp_verify_nonce( $data['mdjm_nonce'], 'add_employee' ) ) {
		$message = 'security_failed';
	} else {
		if ( empty( $data['first_name'] ) || empty( $data['last_name'] ) || empty( $data['user_email'] ) || ! is_email( $data['user_email'] ) || empty( $data['employee_role'] ) ) {
			$message = 'employee_info_missing';
		} elseif ( mdjm_add_employee( $data ) ) {
			$message = 'employee_added';
		} else {
			$message = 'employee_add_failed';
		}
	}

	$url = remove_query_arg( array( 'mdjm-action', 'mdjm_nonce' ) );

	wp_safe_redirect(
		add_query_arg(
			array(
				'mdjm-message' => $message,
			),
			$url
		)
	);
	exit;
} // mdjm_add_employee_action
add_action( 'mdjm-add_employee', 'mdjm_add_employee_action' );
