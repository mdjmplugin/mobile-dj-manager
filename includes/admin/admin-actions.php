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

function mh_test()	{
	
	$date = date( 'Y-m-d', '1530639000' );
	$av   = new MDJM_Availability_Checker( $date );
	$av->availability_check();

    $employees_query = array();
    $all = array();
    $employees       = mdjm_get_employees();

    foreach( $employees as $employee )	{
        if ( is_object( $employee ) )	{
            $all[] = $employee->ID;
        } else	{
            $all[] = $employee;
        }
    }

    foreach( $all as $employee_id )    {
        $employees_query[] = array(
            'key'     => '_mdjm_event_employees',
            'value'   => sprintf( ':"%s";', $employee_id ),
            'compare' => 'LIKE'
        );
    }

    $events = 
        array(
            'post_status'    => 'any',
            'posts_per_page' => 100,
            'meta_query'     => array(
                'relation' => 'AND',
                array(
                    'key'     => '_mdjm_event_date',
                    'value'   => date( 'Y-m-d', time() )
                ),
                array(
                    'relation' => 'OR',
                    array(
                        'key'     => '_mdjm_event_dj',
                        'value'   => implode( ',', $all ),
                        'compare' => 'IN'
                    ),
                    $employees_query
                )
            )
    );
    error_log( var_export( $events, true ) );
}
//add_action( 'admin_init', 'mh_test' );

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
