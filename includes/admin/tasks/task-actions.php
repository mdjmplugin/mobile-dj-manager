<?php
/**
 * Process event actions
 *
 * @package		MDJM
 * @subpackage	Events
 * @since		1.3
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) )
	exit;

/**
 * Activates the given task.
 *
 * @since	1.4.7
 * @return	void
 */
function mdjm_activate_task_action( $data )	{
	if ( empty( $data['id'] ) )	{
		return;
	}

	if ( mdjm_task_set_active_status( $data['id'] ) )	{
		$message = 'task-status-updated';
	} else	{
		$message = 'task-status-update-failed';
	}

	$redirect = add_query_arg( array(
		'post_type'    => 'mdjm-event',
		'page'         => 'mdjm-tasks',
		'mdjm-message' => $message
	), admin_url( 'edit.php' ) );

	wp_safe_redirect( $redirect );
	die();

} // mdjm_activate_task_action
add_action( 'mdjm-activate_task', 'mdjm_activate_task_action' );

/**
 * Deactivates the given task.
 *
 * @since	1.4.7
 * @return	void
 */
function mdjm_deactivate_task_action( $data )	{
	if ( empty( $data['id'] ) )	{
		return;
	}

	if ( mdjm_task_set_active_status( $data['id'], false ) )	{
		$message = 'task-status-updated';
	} else	{
		$message = 'task-status-update-failed';
	}

	$redirect = add_query_arg( array(
		'post_type'    => 'mdjm-event',
		'page'         => 'mdjm-tasks',
		'mdjm-message' => $message
	), admin_url( 'edit.php' ) );

	wp_safe_redirect( $redirect );
	die();

} // mdjm_deactivate_task_action
add_action( 'mdjm-deactivate_task', 'mdjm_deactivate_task_action' );

/**
 * Save an individual task.
 *
 * @since	1.4.7
 * @param	arr		$data	Array of POST data
 * @return	void
 */
function mdjm_save_task_action( $data )	{

	if ( ! isset( $_POST['mdjm_task_nonce'] ) || ! wp_verify_nonce( $_POST['mdjm_task_nonce'], 'mdjm_update_task_details_nonce' ) )	{
		return;
	}

	if ( ! isset( $_POST['mdjm_task_id'] ) )	{
		return;
	}

	$task_data = array(
		'id'        => sanitize_text_field( $data['mdjm_task_id'] ),
		'name'      => sanitize_text_field( $data['task_name'] ),
		'frequency' => sanitize_text_field( $data['task_frequency'] ),
		'desc'      => $data['task_description'],
		'options'   => array(
			'age'       => absint( $data['task_run_time'] ) . ' ' . sanitize_text_field( $data['task_run_period'] ),
			'run_when'  => sanitize_text_field( $data['task_run_event_status'] )
		)
	);

	if ( isset( $data['task_email_template'] ) )	{
		$task_data['options']['email_template'] = absint( $data['task_email_template'] );
		$task_data['options']['email_subject']  = sanitize_text_field( $data['task_email_subject'] );
		$task_data['options']['email_from']     = sanitize_text_field( $data['task_email_from'] );
	}

	if ( 'upload-playlists' != $task_data['id'] )	{
		$task_data['active'] = ! empty( $data['task_active'] ) ? true : false;
	}

	if( mdjm_update_task( $task_data ) )	{
		$message = 'task-updated';
	} else	{
		$message = 'task-update-failed';
	}

	wp_safe_redirect( add_query_arg( array(
		'post_type'    => 'mdjm-event',
		'page'         => 'mdjm-tasks',
		'view'         => 'task',
		'id'           => $task_data['id'],
		'mdjm-message' => $message
	), admin_url( 'edit.php' ) ) );
	die();

} // mdjm_save_task_action
add_action( 'mdjm-update_task_details', 'mdjm_save_task_action' );

/**
 * Runs the given task.
 *
 * @since	1.4.7
 * @return	void
 */
function mdjm_run_now_task_action( $data )	{
	if ( empty( $data['id'] ) )	{
		return;
	}

	if ( mdjm_task_run_now( $data['id'] ) )	{
		$message = 'task-run';
	} else	{
		$message = 'task-run-failed';
	}

	$redirect = add_query_arg( array(
		'post_type'    => 'mdjm-event',
		'page'         => 'mdjm-tasks',
		'mdjm-message' => $message
	), admin_url( 'edit.php' ) );

	wp_safe_redirect( $redirect );
	die();

} // mdjm_run_now_task_action
add_action( 'mdjm-run_task', 'mdjm_run_now_task_action' );
