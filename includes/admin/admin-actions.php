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

		if ( isset( $_FILES ) )	{
			$_POST['FILES'] = $_FILES;
		}

		do_action( 'mdjm-' . $_POST['mdjm-action'], $_POST );

	}

	if ( isset( $_GET['mdjm-action'] ) ) {

		if ( isset( $_FILES ) )	{
			$_POST['FILES'] = $_FILES;
		}

		do_action( 'mdjm-' . $_GET['mdjm-action'], $_GET );

	}

}
add_action( 'admin_init', 'mdjm_process_actions' );

/**
 * Admin action field.
 *
 * Prints the output for a hidden form field which is required for admin post forms.
 *
 * @since	1.3
 * @param	str		$action		The action identifier
 * @param	bool	$echo		True echo's the input field, false to return as a string
 * @return	str		$input		Hidden form field string
 */
function mdjm_admin_action_field( $action, $echo = true )	{
	$name = apply_filters( 'mdjm-action_field_name', 'mdjm-action' );
	
	$input = '<input type="hidden" name="' . $name . '" id="' . $name . '" value="' . $action . '" />';
	
	if( ! empty( $echo ) )	{
		echo apply_filters( 'mdjm-action_field', $input, $action );
	}
	else	{
		return apply_filters( 'mdjm-action_field', $input, $action );
	}
	
} // mdjm_admin_action_field
