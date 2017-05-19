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
 * Update a single tasks
 *
 * @since	1.4.7
 * @param	arr		$data		Array of task data to save
 * @return	bool	True if update successful, or false
 */
function mdjm_update_task( $data )	{
	if ( ! isset( $data['id'] ) )	{
		return false;
	}

	$id    = $data['id'];
	$tasks = mdjm_get_tasks();

	foreach( $data as $key => $value )	{
		if ( 'id' == $key )	{
			continue;
		}

		$tasks[ $id ][ $key ] = $value;

	}

	return update_option( 'mdjm_schedules', $tasks );

} // mdjm_update_task

/**
 * Retrieve a single tasks
 *
 * @since	1.4.7
 * @param	str		$id		The ID of the task
 * @return	arr		Array of tasks
 */
function mdjm_get_task( $id )	{
	$tasks = mdjm_get_tasks();

	if ( array_key_exists( $id, $tasks ) )	{
		return $tasks[ $id ];
	}

	return false;
} // mdjm_get_task

/**
 * Retrieve a tasks status
 *
 * @since	1.4.7
 * @param	str|arr		$task		A task ID, or array
 * @return	bool		True or false
 */
function mdjm_is_task_active( $task )	{
	if ( ! is_array( $task ) )	{
		$task = mdjm_get_task( $task );
	}

	if ( ! empty( $task['active'] ) )	{
		return true;
	}

	return false;
} // mdjm_is_task_active

/**
 * Whether or not a task can be deleted
 *
 * @since	1.4.7
 * @param	str|arr		$task		A task ID, or array
 * @return	arr			Array of tasks
 */
function mdjm_can_delete_task( $task )	{
	if ( ! is_array( $task ) )	{
		$task = mdjm_get_task( $task );
	}

	if ( empty( $task['default'] ) )	{
		return true;
	}

	return false;
} // mdjm_get_task

/**
 * Retrieve schedule options for tasks
 *
 * @since	1.4.7
 * @return	arr		Array of options
 */
function mdjm_get_task_schedule_options()	{
	$schedules = array(
		'Hourly'      => __( 'Hourly', 'mobile-dj-manager' ),
		'Daily'       => __( 'Daily', 'mobile-dj-manager' ),
        'Twice Daily' => __( 'Twice Daily', 'mobile-dj-manager' ),
        'Weekly'      => __( 'Weekly', 'mobile-dj-manager' ),
        'Monthly'     => __( 'Monthly', 'mobile-dj-manager' ),
        'Yearly'      => __( 'Yearly', 'mobile-dj-manager' )
	);

	$schedules = apply_filters( 'mdjm_task_schedule_options', $schedules );

	return $schedules;
} // mdjm_get_task_schedule_options

/**
 * Retrieve task run time options
 *
 * @since	1.4.7
 * @param	int		$id		The task ID
 * @return	arr		Array of options
 */
function mdjm_get_task_run_times( $id = false )	{
	$event_label = mdjm_get_label_singular();
	$run_times   = array(
		'event_created'  => sprintf( __( 'After the %s is Created', 'mobile-dj-manager' ), $event_label ),
		'after_approval' => sprintf( __( 'After the %s is Confirmed', 'mobile-dj-manager' ), $event_label ),
		'before_event'   => sprintf( __( 'Before the %s', 'mobile-dj-manager' ), $event_label ),
		'after_event'    => sprintf( __( 'After the %s', 'mobile-dj-manager' ), $event_label ),
	);

	$run_times = apply_filters( 'mdjm_task_run_times', $run_times, $id );

	return $run_times;
} // mdjm_get_task_run_times

/**
 *
 * @since	1.4.7
 * @param	arr		$run_times	The run time schedules
 * @param	int		$id			The task ID
 * @return	arr		The run time schedules
 */
function mdjm_filter_task_run_times( $run_times, $id )	{
	if ( 'complete-events' == $id || 'upload-playlists' == $id )	{
		$unset = array( 'event_created', 'after_approval', 'before_event' );
	} elseif( 'fail-enquiry' == $id )	{
		$unset = array( 'after_approval', 'before_event', 'after_event' );
	} elseif( 'request-deposit' == $id || 'balance-reminder' == $id )	{
		$unset = array( 'event_created', 'after_event' );
	}

	if ( isset( $unset ) )	{
		foreach( $unset as $time )	{
			unset( $run_times[ $time ] );
		}
	}

	return $run_times;
} // mdjm_filter_task_run_times
add_filter( 'mdjm_task_run_times', 'mdjm_filter_task_run_times', 10, 2 );

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
