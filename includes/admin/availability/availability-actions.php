<?php
/**
 * Contains all admin availability related functions
 *
 * @package		MDJM
 * @subpackage	Availability
 * @since		1.5.6
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) )
	exit;

/**
 * Insert a new absence entry.
 *
 * @since	1.5.6
 * @param	array	$form_data	$_POST form data.
 * @return	void
 */
function mdjm_add_employee_absence_action( $form_data )	{
    if ( ! isset( $form_data['mdjm_nonce'] ) || ! wp_verify_nonce( $form_data[ 'mdjm_nonce' ], 'add_employee_absence' ) )  {
        wp_die( __( 'Security failure', 'mobile-dj-manager' ) );
    }

    $employee_id = ! empty( $form_data['employee_id'] ) ? absint( $form_data['employee_id'] ) : 0;

    $data['employee_id'] = $employee_id;
    $data['group_id']    = md5( $employee_id . '_' . mdjm_generate_random_string() );
    $data['from_date']   = isset( $form_data['from_date'] ) ? $form_data['from_date'] : '';
    $data['to_date']     = isset( $form_data['to_date'] )   ? $form_data['to_date']   : '';
    $data['notes']       = isset( $form_data['notes'] )     ? sanitize_textarea_field( $form_data['notes'] )        : '';

    if ( mdjm_add_employee_absence( $employee_id, $data ) ) {
        $message = 'absence-added';
    } else  {
        $message = 'absence-add-fail';
    }

    $return_url = add_query_arg( array(
        'post_type'    => 'mdjm-event',
        'page'         => 'mdjm-availability',
        'mdjm-message' => $message
    ), admin_url( 'edit.php' ) );

    wp_safe_redirect( $return_url );
    die();
} // mdjm_add_employee_absence_action
add_action( 'mdjm-add_employee_absence', 'mdjm_add_employee_absence_action' );

/**
 * Remove an absence entry.
 *
 * @since	1.5.6
 * @param	array	$data	$_GET data.
 * @return	void
 */
function mdjm_remove_employee_absence_action( $data )	{
    if ( ! isset( $data['mdjm_nonce'] ) || ! wp_verify_nonce( $data[ 'mdjm_nonce' ], 'remove_employee_absence' ) )  {
        wp_die( __( 'Security failure', 'mobile-dj-manager' ) );
    }

    $employee_id = ! empty( $form_data['employee_id'] ) ? absint( $form_data['employee_id'] ) : 0;

    $data['employee_id'] = $employee_id;
    $data['group_id']    = md5( $employee_id . '_' . mdjm_generate_random_string() );
    $data['from_date']   = isset( $form_data['from_date'] ) ? $form_data['from_date'] : '';
    $data['to_date']     = isset( $form_data['to_date'] )   ? $form_data['to_date']   : '';
    $data['notes']       = isset( $form_data['notes'] )     ? sanitize_textarea_field( $form_data['notes'] )        : '';

    if ( mdjm_remove_employee_absence( $data['group'] ) ) {
        $message = 'absence-removed';
    } else  {
        $message = 'absence-remove-fail';
    }

    $return_url = add_query_arg( array(
        'post_type'    => 'mdjm-event',
        'page'         => 'mdjm-availability',
        'mdjm-message' => $message
    ), admin_url( 'edit.php' ) );

    wp_safe_redirect( $return_url );
    die();
} // mdjm_remove_employee_absence_action
add_action( 'mdjm-remove_employee_absence', 'mdjm_remove_employee_absence_action' );
