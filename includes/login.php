<?php
/**
 * Login / Register Functions
 *
 * @package     MDJM
 * @subpackage  Functions/Login
 * @copyright   Copyright (c) 2016, Mike Howard
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.3
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Login Form
 *
 * @since	1.3
 * @global	$post
 * @param	str		$redirect	Redirect page URL
 * @return	str		Login form
 */
function mdjm_login_form( $redirect = '' )	{
	global $mdjm_login_redirect;
	
	if ( empty( $redirect ) ) {
		$redirect = mdjm_do_content_tags( '{application_home}' );
	}

	$mdjm_login_redirect = remove_query_arg( 'mdjm_message', $redirect );
	
	ob_start();

	mdjm_get_template_part( 'login', 'form' );
	
	$output = ob_get_clean();
	$output = mdjm_do_content_tags( $output ); 

	return apply_filters( 'mdjm_login_form', $output );
} // mdjm_login_form

/**
 * Process Login Form
 *
 * @since	1.3
 * @param	arr		$data	Data sent from the login form
 * @return void
 */
function mdjm_process_login_form( $data ) {
	if ( wp_verify_nonce( $data['mdjm_login_nonce'], 'mdjm-login-nonce' ) ) {
		$user_data = get_user_by( 'login', $data['mdjm_user_login'] );

		if ( ! $user_data ) {
			$user_data = get_user_by( 'email', $data['mdjm_user_login'] );
		}

		if ( $user_data ) {

			$user_ID = $user_data->ID;
			$user_email = $user_data->user_email;
			if ( wp_check_password( $data['mdjm_user_pass'], $user_data->user_pass, $user_data->ID ) ) {
				mdjm_log_user_in( $user_data->ID, $data['mdjm_user_login'], $data['mdjm_user_pass'] );
			} else {
				$message = 'password_incorrect';
			}

		} else {

			$message = 'username_incorrect';

		}

		if ( ! empty( $message ) )	{
			$url = remove_query_arg( 'mdjm_message' );
			wp_redirect( add_query_arg( 'mdjm_message', $message, $url ) );
			die();
		}

		$redirect = apply_filters( 'mdjm_login_redirect', $data['mdjm_redirect'], $user_ID );
		wp_redirect( $redirect );
		die();

	}
} // mdjm_process_login_form
add_action( 'mdjm_user_login', 'mdjm_process_login_form' );

/**
 * Log User In
 *
 * @since	1.3
 * @param	int		$user_id	User ID
 * @param	str		$user_login Username
 * @param	str		$user_pass	Password
 * @return	void
 */
function mdjm_log_user_in( $user_id, $user_login, $user_pass ) {

	if ( $user_id < 1 )	{
		return;
	}

	wp_set_auth_cookie( $user_id );
	wp_set_current_user( $user_id, $user_login );
	do_action( 'wp_login', $user_login, get_userdata( $user_id ) );
	do_action( 'mdjm_log_user_in', $user_id, $user_login, $user_pass );

} // mdjm_log_user_in
