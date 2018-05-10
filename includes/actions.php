<?php
/**
 * Front-end Actions
 *
 * @package     MDJM
 * @subpackage  Functions
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.3
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) )
	exit;

/**
 * Hooks MDJM actions, when present in the $_GET superglobal. Every mdjm_action
 * present in $_GET is called using WordPress's do_action function. These
 * functions are called on init.
 *
 * @since	1.3
 * @return	void
*/
function mdjm_get_actions() {
	if ( isset( $_GET['mdjm_action'] ) ) {
		do_action( 'mdjm_' . $_GET['mdjm_action'], $_GET );
	}
} // mdjm_get_actions
add_action( 'init', 'mdjm_get_actions' );

/**
 * Hooks MDJM actions, when present in the $_POST superglobal. Every mdjm_action
 * present in $_POST is called using WordPress's do_action function. These
 * functions are called on init.
 *
 * @since	1.3
 * @return	void
*/
function mdjm_post_actions() {
	if ( isset( $_POST['mdjm_action'] ) ) {
		do_action( 'mdjm_' . $_POST['mdjm_action'], $_POST );
	}
} // mdjm_post_actions
add_action( 'init', 'mdjm_post_actions' );

/**
 * Action field.
 *
 * Prints the output for a hidden form field which is required for post forms.
 *
 * @since	1.3
 * @param	str		$action		The action identifier
 * @param	bool	$echo		True echo's the input field, false to return as a string
 * @return	str		$input		Hidden form field string
 */
function mdjm_action_field( $action, $echo = true )	{
	$name = apply_filters( 'mdjm_action_field_name', 'mdjm_action' );
	
	$input = '<input type="hidden" name="' . $name . '" id="' . $name . '" value="' . $action . '" />';
	
	if( ! empty( $echo ) )	{
		echo apply_filters( 'mdjm_action_field', $input, $action );
	}
	else	{
		return apply_filters( 'mdjm_action_field', $input, $action );
	}
	
} // mdjm_action_field
