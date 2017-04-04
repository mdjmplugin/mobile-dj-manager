<?php
/**
 * Task functions
 *
 * @package		MDJM
 * @subpackage	Tasks
 * @since		1.4.7
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) )
	exit;

/**
 * Retrieve the tasks
 *
 * @since	1.4.7
 * @return	arr		Array of tasks
 */
function mdjm_get_tasks()	{
	$tasks = get_option( 'mdjm_schedules' );
	if ( $tasks )	{
		ksort( $tasks );
	}

	return apply_filters( 'mdjm_tasks', $tasks );
} // mdjm_get_tasks

/**
 * Set the status for a given task
 *
 * @since	1.4.7
 * @param	str		$id			The slug ID of the task
 * @param	bool	$activate	True to activate task, false to deactivate
 * @return	bool	True on success, otherwise false
 */
function mdjm_task_set_active_status( $id, $activate = null )	{
	$tasks = mdjm_get_tasks();

	if ( $tasks && array_key_exists( $id, $tasks ) )	{
		if ( ! isset( $activate ) )	{
			$activate = true;
		}

		$tasks[ $id ]['active'] = $activate;

		if ( $activate )	{
			$tasks[ $id ]['nextrun'] = current_time( 'timestamp' );
		} else	{
			$tasks[ $id ]['nextrun'] = 'Never';
		}

		return update_option( 'mdjm_schedules', $tasks );
	}

	return false;
} // mdjm_task_set_active_status

/**
 * Runs given task
 *
 * @since	1.4.7
 * @param	str		$id			The slug ID of the task
 * @return	bool	True on success, otherwise false
 */
function mdjm_task_run_now( $id )	{
	$tasks = mdjm_get_tasks();

	if ( empty( $tasks ) || ! array_key_exists( $id, $tasks ) )	{
		return false;
	}

	$tasks[ $id ]['nextrun'] = current_time( 'timestamp' );
	update_option( 'mdjm_schedules', $tasks );

	require_once( MDJM_PLUGIN_DIR . '/includes/class-mdjm-task-runner.php' );

	return new MDJM_Task_Runner( $id );
} // mdjm_task_run_now

