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
