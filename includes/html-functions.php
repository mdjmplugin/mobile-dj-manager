<?php

/**
 * Contains all HTML related functions
 *
 * @package		MDJM
 * @subpackage	HTML
 * @since		1.3
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) )
	exit;

/**
 * Show the login page.
 *
 * Displays a login form on the website front end.
 *
 * @since	1.3
 * @param	str		$redirect	The URL to which to redirect
 * @return	str		The login template contents.
 */
function mdjm_login_form( $redirect = '' )	{
	global $mdjm_login_redirect;
	
	if ( empty( $redirect ) ) {
		$redirect = mdjm_get_current_page_url();
	}

	$mdjm_login_redirect = $redirect;
	
	ob_start();

	mdjm_get_template_part( 'login', 'form' );

	return apply_filters( 'mdjm_login_form', ob_get_clean() );
} // mdjm_login_form
?>