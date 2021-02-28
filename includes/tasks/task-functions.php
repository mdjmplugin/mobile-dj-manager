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
 * Retrieve a task name
 *
 * @since	1.5
 * @param	string|array     $task	A task ID, or array
 * @return	string
 */
function mdjm_get_task_name( $task )	{
	if ( ! is_array( $task ) )	{
		$task = mdjm_get_task( $task );
	}

	return $task['name'];
} // mdjm_get_task_name

/**
 * Retrieve a task description
 *
 * @since	1.5
 * @param	string|array     $task	A task ID, or array
 * @return	string
 */
function mdjm_get_task_description( $task )	{
	if ( ! is_array( $task ) )	{
		$task = mdjm_get_task( $task );
	}

	return $task['desc'];
} // mdjm_get_task_description

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
	if ( 'complete-events' == $id )	{
		$unset = array( 'event_created', 'after_approval', 'before_event' );
	} elseif( 'fail-enquiry' == $id )	{
		$unset = array( 'after_approval', 'before_event', 'after_event' );
	} elseif( 'request-deposit' == $id || 'balance-reminder' == $id )	{
		$unset = array( 'event_created', 'after_event' );
	} elseif( 'playlist-employee-notify' == $id )	{
		$unset = array( 'event_created', 'after_event', 'after_approval' );
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

/**
 * Retrieve single available event tasks based on the event status.
 *
 * Does not consider whether or not a task has been previously executed.
 *
 * @since   1.5
 * @param   int     $event_id   Event post ID
 * @return  array   Array of tasks that can be executed for the event
 */
function mdjm_get_tasks_for_event( $event_id )  {
    $event           = new MDJM_Event( $event_id );
    $tasks           = array();
    $completed_tasks = $event->get_tasks();
    $playlist        = mdjm_get_playlist_entries( $event_id, array( 'posts_per_page' => 1 ) );
    $guest_args      = array(
        'posts_per_page' => 1,
        'tax_query'      => array(
            array(
                'taxonomy' => 'playlist-category',
                'field'    => 'slug',
                'terms'    => 'guest'
            )
        )
    );

    $guest_playlist = mdjm_get_playlist_entries( $event_id, $guest_args );

    if ( in_array( $event->post_status, array( 'mdjm-awaitingdeposit', 'mdjm-approved', 'mdjm-contract' ) ) )  {
        if ( 'Due' == $event->get_deposit_status() )    {
            $tasks['request-deposit'] = mdjm_get_task_name( 'request-deposit' );
        }
        if ( 'Due' == $event->get_balance_status() )    {
            $tasks['balance-reminder'] = mdjm_get_task_name( 'balance-reminder' );
        }
    }

    /*if ( $playlist )    {
        $tasks['playlist-employee-notify'] = mdjm_get_task_name( 'playlist-employee-notify' );
    }*/

    /*if ( $guest_playlist )  {
        $tasks['playlist-notification'] = mdjm_get_task_name( 'playlist-notification' );
    }*/

    if ( 'mdjm-unattended' == $event->post_status ) {
        $tasks['reject-enquiry'] = __( 'Reject Enquiry', 'mobile-dj-manager' );
    }

    if ( 'mdjm-enquiry' == $event->post_status ) {
        $tasks['fail-enquiry']   = mdjm_get_task_name( 'fail-enquiry' );
    }

    $tasks = apply_filters( 'mdjm_tasks_for_event', $tasks, $event_id );

    if ( ! empty( $tasks ) )    {
        ksort( $tasks );
    }

    return $tasks;
} // mdjm_get_tasks_for_event

/**
 * Executes a single event task.
 *
 * @since   1.5
 * @param   int     $event_id   The event post ID
 * @param   string  $task_id    The slug (id) of the task to be executed
 * @return   bool    True if the task ran successfully, otherwise false
 */
function mdjm_run_single_event_task( $event_id, $task_id ) {
    $task = mdjm_get_task( $task_id );

    switch( $task_id )  {
        case 'fail-enquiry':
            return mdjm_fail_enquiry_single_task( $event_id );
            break;

        case 'request-deposit':
			return mdjm_request_deposit_single_task( $event_id );
			break;

		case 'balance-reminder':
			return mdjm_balance_reminder_single_task( $event_id );
			break;

        case 'playlist-employee-notify':
            return mdjm_employee_playlist_notify_single_task( $event_id );
            break;

		default:
			break;
    }
} // mdjm_run_single_event_task

/**
 * Executes the fail enquiry task for a single event.
 *
 * @since   1.5
 * @param   $event_id
 * @return  bool    True if task ran successfully
 */
function mdjm_fail_enquiry_single_task( $event_id ) {
    $event = new MDJM_Event( $event_id );

    if ( mdjm_update_event_status( $event->ID, 'mdjm-failed', $event->post_status ) )	{
        mdjm_add_journal( array(
            'user_id'         => 1,
            'event_id'        => $event->ID,
            'comment_content' => __( 'Enquiry marked as lost via manually executed Scheduled Task', 'mobile-dj-manager' ) . '<br /><br />' . time()
        ) );

        $event->complete_task( 'fail-enquiry' );

        return true;
    }

    return false;
} // mdjm_fail_enquiry_single_task

/**
 * Executes the request deposit task for a single event.
 *
 * @since   1.5
 * @param   $event_id
 * @return  bool    True if task ran successfully
 */
function mdjm_request_deposit_single_task( $event_id ) {
    $event = new MDJM_Event( $event_id );
    $task  = mdjm_get_task( 'request-deposit' );

    if ( ! empty( $task['options']['email_template'] ) && ! empty( $event->client ) )	{

        $client = get_userdata( $event->client );

        $email_args = array(
            'to_email'  => $client->user_email,
            'event_id'  => $event->ID,
            'client_id' => $event->client,
            'subject'   => $task['options']['email_subject'],
            'message'   => mdjm_get_email_template_content( $task['options']['email_template'] ),
            'track'     => true,
            'source'    => sprintf( __( 'Request %s Scheduled Task', 'mobile-dj-manager' ), mdjm_get_deposit_label() )
        );

        if ( 'employee' == $task['options']['email_from'] && ! empty( $event->employee_id ) )	{
            $employee                 = get_userdata( $event->employee_id );
            $email_args['from_email'] = $employee->user_email;
            $email_args['from_name']  = $employee->display_name;
        }

        if ( mdjm_send_email_content( $email_args ) )	{
            remove_action( 'save_post_mdjm-event', 'mdjm_save_event_post', 10, 3 );
            wp_update_post( array( 'ID' => $event->ID, 'post_modified' => date( 'Y-m-d H:i:s' ) ) );
            add_action( 'save_post_mdjm-event', 'mdjm_save_event_post', 10, 3 );

            update_post_meta( $event->ID, '_mdjm_event_last_updated_by', 0 );
            $event->complete_task( $task['slug'] );

            mdjm_add_journal( array(
                'user_id'         => 1,
                'event_id'        => $event->ID,
                'comment_content' => sprintf( __( '%s task manually executed', 'mobile-dj-manager' ), esc_attr( $task['name'] ) ) . '<br /><br />' . time()
            ) );

            return true;
        }
    }

    return false;
} // mdjm_request_deposit_single_task

/**
 * Executes the balance reminder task for a single event.
 *
 * @since   1.5
 * @param   $event_id
 * @return  bool    True if task ran successfully
 */
function mdjm_balance_reminder_single_task( $event_id ) {
    $event = new MDJM_Event( $event_id );
    $task  = mdjm_get_task( 'balance-reminder' );

    if ( ! empty( $task['options']['email_template'] ) && ! empty( $event->client ) )	{

        $client = get_userdata( $event->client );

        $email_args = array(
            'to_email'       => $client->user_email,
            'event_id'       => $event->ID,
            'client_id'      => $event->client,
            'subject'        => $task['options']['email_subject'],
            'message'        => mdjm_get_email_template_content( $task['options']['email_template'] ),
            'track'          => true,
            'source'         => sprintf( __( 'Request %s Scheduled Task', 'mobile-dj-manager' ), mdjm_get_balance_label() )
        );

        if ( 'employee' == $task['options']['email_from'] && ! empty( $event->employee_id ) )	{
            $employee                 = get_userdata( $event->employee_id );
            $email_args['from_email'] = $employee->user_email;
            $email_args['from_name']  = $employee->display_name;
        }

        if ( mdjm_send_email_content( $email_args ) )	{

            remove_action( 'save_post_mdjm-event', 'mdjm_save_event_post', 10, 3 );
            wp_update_post( array( 'ID' => $event->ID, 'post_modified' => date( 'Y-m-d H:i:s' ) ) );
            add_action( 'save_post_mdjm-event', 'mdjm_save_event_post', 10, 3 );

            update_post_meta( $event->ID, '_mdjm_event_last_updated_by', 0 );
            $event->complete_task( $task['slug'] );

            mdjm_add_journal( array(
                'user_id'         => 1,
                'event_id'        => $event->ID,
                'comment_content' => sprintf( __( '%s  task manually executed', 'mobile-dj-manager' ), $task['name'] ) . '<br /><br />' . time()
            ) );

            return true;
        }
    }

    return false;
} // mdjm_balance_reminder_single_task

/**
 * Executes the employee playlist notification task for a single event.
 *
 * @since   1.5
 * @param   $event_id
 * @return  bool    True if task ran successfully
 */
function mdjm_employee_playlist_notify_single_task( $event_id ) {
    $event = new MDJM_Event( $event_id );

    $content = mdjm_format_playlist_content( $event_id, '', 'ASC', '', true );
    $content = apply_filters( 'mdjm_print_playlist', $content, $event );

    $html_content_start = '<html>' . "\n" . '<body>' . "\n";
    $html_content_end   = '<p>' . __( 'Regards', 'mobile-dj-manager' ) . '</p>' . "\n" .
        '<p>{company_name}</p>' . "\n";
        '<p>&nbsp;</p>' . "\n";
        '<p align="center" style="font-size: 9px">Powered by <a style="color:#F90" href="https://mdjm.co.uk" target="_blank">' . MDJM_NAME . '</a> version ' . MDJM_VERSION_NUM . '</p>' . "\n" .
        '</body>' . "\n" . '</html>';

    $args = array(
        'to_email'		=> mdjm_get_employee_email( $event->employee_id ),
        'from_name'		=> mdjm_get_option( 'company_name' ),
        'from_email'	=> mdjm_get_option( 'system_email' ),
        'event_id'		=> $event_id,
        'client_id'		=> $event->client,
        'subject'		=> sprintf( __( 'Playlist for %s ID %s', 'mobile-dj-manager' ), mdjm_get_label_singular(), '{contract_id}' ),
        'message'		=> $html_content_start . $content . $html_content_end,
        'copy_to'       => 'disable'
    );

    $event->complete_task( 'employee-playlist-notify' );
    return mdjm_send_email_content( $args );
} // mdjm_employee_playlist_notify_single_task
