<?php
/**
 * Task Object
 *
 * @package     MDJM
 * @subpackage  Classes/Tasks
 * @copyright   Copyright (c) 2017, Mike Howard
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.4.7
*/

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) )
	exit;

/**
 * MDJM_Task_Runner Class
 *
 * @since	1.4.7
 */
class MDJM_Task_Runner {

	/**
	 * All tasks
	 *
	 * @since	1.4.7
	 * @arr
	 */
	public $all_tasks = array();

	/**
	 * The task slug
	 *
	 * @since	1.4.7
	 * @str
	 */
	public $slug;

	/**
	 * The task name
	 *
	 * @since	1.4.7
	 * @str
	 */
	public $name;

	/**
	 * Active task
	 *
	 * @since	1.4.7
	 * @bool
	 */
	public $active;

	/**
	 * The task description
	 *
	 * @since	1.4.7
	 * @str
	 */
	public $description;

	/**
	 * The task frequency
	 *
	 * @since	1.4.7
	 * @str
	 */
	public $frequency;

	/**
	 * Last run
	 *
	 * @since	1.4.7
	 * @str
	 */
	public $last_run;

	/**
	 * Next run
	 *
	 * @since	1.4.7
	 * @str
	 */
	public $next_run;

	/**
	 * Total runa
	 *
	 * @since	1.4.7
	 * @int
	 */
	public $total;

	/**
	 * The task options
	 *
	 * @since	1.4.7
	 */
	public $options;

	/**
	 * Default task
	 *
	 * @since	1.4.7
	 * @bool
	 */
	public $default;

	/**
	 * The last result
	 *
	 * @since	1.4.7
	 * @str
	 */
	public $last_result;

	/**
	 * Get things going
	 *
	 * @since	1.4.7
	 */
	public function __construct( $task = false ) {
		if ( empty( $task ) )	{
			return false;
		}

		if ( $this->setup_task( $task ) )	{
			return $this->execute();
		}

		return false;
	} // __construct

	/**
	 * Given the task slug, let's set the variables
	 *
	 * @since	1.4.7
	 * @param 	str		$task	The Task slug
	 * @return	bool	If the setup was successful or not
	 */
	private function setup_task( $task )	{
		$this->all_tasks = get_option( 'mdjm_schedules' );
		if ( empty( $this->all_tasks ) || ! array_key_exists( $task, $this->all_tasks ) )	{
			return false;
		}

		$this_task = $this->all_tasks[ $task ];

		$this->slug        = $task;
		$this->name        = $this_task['name'];
		$this->description = $this_task['desc'];
		$this->frequency   = $this_task['frequency'];
		$this->active      = ! empty( $this_task['active'] )      ? true                      : false;
		$this->next_run    = ! empty( $this_task['nextrun'] )     ? $this_task['nextrun']     : false;
		$this->last_run    = ! empty( $this_task['lastran'] )     ? $this_task['lastran']     : 'never';
		$this->total       = ! empty( $this_task['totalruns'] )   ? $this_task['totalruns']   : '0';
		$this->default     = ! empty( $this_task['default'] )     ? true                      : false;
		$this->last_result = ! empty( $this_task['last_result'] ) ? $this_task['last_result'] : false;
		$this->options     = $this_task['options'];

		if ( ! $this->ready_to_execute() )	{
			return false;
		}

		return true;

	} // setup_task

	/**
	 * Determine if the task should be execute
	 *
	 * @since	1.4.7
	 * @return	bool
	 */
	private function ready_to_execute()	{
		if ( ! $this->active )	{
			return false;
		}

		$now = current_time( 'timestamp' );

		if ( empty( $this->next_run ) || $this->next_run <= $now )	{
			return true;
		}

		return false;
	} // ready_to_execute

	/**
	 * Execute the task
	 *
	 * @since	1.4.7
	 * @return	bool
	 */
	public function execute()	{
		$method = str_replace( '-', '_', $this->slug );

		if ( method_exists( $this, $method ) )	{
			if ( $this->$method() )	{
				return $this->complete_task();
			}
		}

		return false;
	} // execute

	/**
	 * Whether or not the task has run
	 *
	 * @since	1.4.7
	 * @param	obj		$event	Post object
	 * @return	bool
	 */
	public function task_has_run( $event )	{
		$tasks = $event->get_tasks();

		if ( ! empty( $tasks ) && array_key_exists( $this->slug, $tasks ) )	{
			return true;
		}

		return false;
	} // task_has_run

	/**
	 * Complete the task
	 *
	 * @since	1.4.7
	 * @return	void
	 */
	public function complete_task()	{
		$this->all_tasks[ $this->slug ]['totalruns'] = $this->total + 1;
		$this->all_tasks[ $this->slug ]['nextrun'] = current_time( 'timestamp' ) + $this->set_next_run();
		$this->all_tasks[ $this->slug ]['lastran'] = current_time( 'timestamp' );

		return update_option( 'mdjm_schedules', $this->all_tasks );
	} // complete_task

	/**
	 * Set the next run time
	 *
	 * @since	1.4.7
	 * @return	str
	 */
	public function set_next_run()	{
		switch( $this->frequency )	{
			case 'Daily':
				$wait = DAY_IN_SECONDS;
				break;

			case 'Twice Daily':
				$wait = DAY_IN_SECONDS / 2;
				break;

			case 'Weekly':
				$wait = WEEK_IN_SECONDS;
				break;

			case 'Monthly':
				$wait = MONTH_IN_SECONDS;
				break;

			case 'Yearly':
				$wait = YEAR_IN_SECONDS;
				break;

			case 'Hourly':
			default:
				$wait = HOUR_IN_SECONDS;
				break;
		}

		return $wait;
	} // set_next_run

	/**
	 * Execute the Upload Playlist task
	 *
	 * @since	1.4.7
	 * @return	bool
	 */
	public function upload_playlists()	{
		MDJM()->debug->log_it( "*** Starting the $this->name task ***", true );

		mdjm_process_playlist_upload();

		MDJM()->debug->log_it( "*** $this->name task Completed ***", true );

		return true;
	} // upload_playlists

	/**
	 * Execute the Complete Events task
	 *
	 * @since	1.4.7
	 * @return	bool
	 */
	public function complete_events()	{
		MDJM()->debug->log_it( "*** Starting the $this->name task ***", true );

		$i         = 1;
		$completed = 0;
		$events    = mdjm_get_events( $this->build_query() );

		if ( $events )	{
			remove_action( 'save_post_mdjm-event', 'mdjm_save_event_post', 10, 3 );

			$count = count( $events );
			MDJM()->debug->log_it( $count . ' ' . _n( 'event', 'events', $events, 'mobile-dj-manager' ) . ' to be marked as completed' );

			foreach( $events as $_event )	{
				$event = new MDJM_Event( $_event->ID );

				if ( ! $event )	{
					continue;
				}

				if ( $this->task_has_run( $event ) )	{
					continue;
				}

				$date_format = 'Y-m-d H:i:s';
				$time       = $event->get_finish_time();
				$end_date   = get_post_meta( $event->ID, '_mdjm_event_end_date', true );
		
				if ( ! $end_date )	{
					$end_date = $event->date;
				}

				$end_time      = DateTime::createFromFormat( $date_format, $end_date . ' ' . $time );
				$mark_complete = strtotime( '+' . $this->options['age'] );

				if ( $mark_complete < strtotime( $end_time->format( $date_format ) ) )	{
					continue;
				}

				$update = mdjm_update_event_status( $event->ID, 'mdjm-completed', $event->post_status );
				if ( $update )	{

					if ( mdjm_get_option( 'employee_auto_pay_complete' ) )	{
						mdjm_pay_event_employees( $event->ID );
					}

					mdjm_add_journal(
						array(
							'user_id'         => 1,
							'event_id'        => $event->ID,
							'comment_content' => __( 'Event marked as completed via Scheduled Task', 'mobile-dj-manager' ) . '<br /><br />' . time()
						)
					);

					$event->complete_task( $this->slug );
					$completed++;

					MDJM()->debug->log_it( 'Event ' . $event->ID . ' marked as completed' );
				} else	{
					MDJM()->debug->log_it( 'Event ' . $event->ID . ' could not be marked as completed' );
				}
			}
			add_action( 'save_post_mdjm-event', 'mdjm_save_event_post', 10, 3 );
		}

		MDJM()->debug->log_it( "$completed event(s) marked as completed" );
		MDJM()->debug->log_it( "*** $this->name task Completed ***", true );

		return true;
	} // complete_events

	/**
	 * Execute the Fail Enquiry task
	 *
	 * @since	1.4.7
	 * @return	bool
	 */
	public function fail_enquiry()	{
		MDJM()->debug->log_it( "*** Starting the $this->name task ***", true );

		$i         = 1;
		$completed = 0;
		$events    = mdjm_get_events( $this->build_query() );

		if ( $events )	{
			remove_action( 'save_post_mdjm-event', 'mdjm_save_event_post', 10, 3 );

			$count = count( $events );
			MDJM()->debug->log_it( $count . ' ' . _n( 'enquiry', 'enquiries', $events, 'mobile-dj-manager' ) . ' to be marked as failed' );

			foreach( $events as $_event )	{
				$event = new MDJM_Event( $_event->ID );

				if ( ! $event )	{
					continue;
				}

				if ( $this->task_has_run( $event ) )	{
					continue;
				}

				$update = mdjm_update_event_status( $event->ID, 'mdjm-failed', $event->post_status );
				if ( $update )	{
					mdjm_add_journal(
						array(
							'user_id'         => 1,
							'event_id'        => $event->ID,
							'comment_content' => __( 'Enquiry marked as lost via Scheduled Task', 'mobile-dj-manager' ) . '<br /><br />' . time()
						)
					);

					$event->complete_task( $this->slug );
					$completed++;

					MDJM()->debug->log_it( 'Event ' . $event->ID . ' marked as failed' );
				} else	{
					MDJM()->debug->log_it( 'Event ' . $event->ID . ' could not be marked as failed' );
				}

			}
			add_action( 'save_post_mdjm-event', 'mdjm_save_event_post', 10, 3 );
		}

		MDJM()->debug->log_it( "*** $this->name task Completed ***", true );

		return true;
	} // fail_enquiry

	/**
	 * Execute the Balance Reminder task
	 *
	 * @since	1.4.7
	 * @return	bool
	 */
	public function balance_reminder()	{
		MDJM()->debug->log_it( "*** Starting the $this->name task ***", true );

		$due_date = date( 'Y-m-d', strtotime( "-" . $this->options['age'] ) );

		$i         = 1;
		$completed = 0;
		$events    = mdjm_get_events( $this->build_query() );

		if ( $events )	{
			$count = count( $events );
			MDJM()->debug->log_it( $count . ' ' . _n( 'event', 'events', $events, 'mobile-dj-manager' ) . ' due balance' );

			foreach( $events as $_event )	{
				$event = new MDJM_Event( $_event->ID );
				MDJM()->debug->log_it( 'Event: ' . $event->ID );
				if ( ! $event )	{
					continue;
				}

				if ( $this->task_has_run( $event ) )	{
					continue;
				}

				if ( empty( $this->options['email_template'] ) || empty( $event->client ) )	{
					continue;
				}

				$client = get_userdata( $event->client );

				$email_args = array(
					'to_email'       => $client->user_email,
					'event_id'       => $event->ID,
					'client_id'      => $event->client,
					'subject'        => $this->options['email_subject'],
					'message'        => mdjm_get_email_template_content( $this->options['email_template'] ),
					'track'          => true,
					'source'         => sprintf( __( 'Request %s Scheduled Task', 'mobile-dj-manager' ), mdjm_get_balance_label() )
				);

				if ( 'employee' == $this->options['email_from'] && ! empty( $event->employee_id ) )	{
					$employee                 = get_userdata( $event->employee_id );
					$email_args['from_email'] = $employee->user_email;
					$email_args['from_name']  = $employee->display_name;
				}

				if ( mdjm_send_email_content( $email_args ) )	{
					MDJM()->debug->log_it( 'Balance reminder sent to ' . $client->display_name );

					remove_action( 'save_post_mdjm-event', 'mdjm_save_event_post', 10, 3 );
					wp_update_post( array( 'ID' => $event->ID, 'post_modified' => date( 'Y-m-d H:i:s' ) ) );
					add_action( 'save_post_mdjm-event', 'mdjm_save_event_post', 10, 3 );

					update_post_meta( $event->ID, '_mdjm_event_last_updated_by', 0 );
					$event->complete_task( $this->slug );

					mdjm_add_journal(
						array(
							'user_id'         => 1,
							'event_id'        => $event->ID,
							'comment_content' => $this->name . ' task executed<br /><br />' . time()
						)
					);

				} else	{
					MDJM()->debug->log_it( 'ERROR: Balance reminder was not sent. Event ID ' . $event->ID );
				}

			}

		} else	{
			MDJM()->debug->log_it( 'No events requiring balance reminders' );
		}

		MDJM()->debug->log_it( "*** $this->name task Completed ***", true );

		return true;
	} // balance_reminder

	/**
	 * Execute the Deposit Reminder task
	 *
	 * @since	1.4.7
	 * @return	bool
	 */
	public function request_deposit()	{
		MDJM()->debug->log_it( "*** Starting the $this->name task ***", true );

		$due_date = date( 'Y-m-d', strtotime( "-" . $this->options['age'] ) );

		$i         = 1;
		$completed = 0;
		$events    = mdjm_get_events( $this->build_query() );

		if ( $events )	{
			$count = count( $events );
			MDJM()->debug->log_it( $count . ' ' . _n( 'event', 'events', $events, 'mobile-dj-manager' ) . ' due deposit' );

			foreach( $events as $_event )	{
				$event = new MDJM_Event( $_event->ID );

				if ( ! $event )	{
					continue;
				}

				if ( $this->task_has_run( $event ) )	{
					continue;
				}

				if ( empty( $this->options['email_template'] ) || empty( $event->client ) )	{
					continue;
				}

				$client = get_userdata( $event->client );

				$email_args = array(
					'to_email'       => $client->user_email,
					'event_id'       => $event->ID,
					'client_id'      => $event->client,
					'subject'        => $this->options['email_subject'],
					'message'        => mdjm_get_email_template_content( $this->options['email_template'] ),
					'track'          => true,
					'source'         => sprintf( __( 'Request %s Scheduled Task', 'mobile-dj-manager' ), mdjm_get_deposit_label() )
				);

				if ( 'employee' == $this->options['email_from'] && ! empty( $event->employee_id ) )	{
					$employee                 = get_userdata( $event->employee_id );
					$email_args['from_email'] = $employee->user_email;
					$email_args['from_name']  = $employee->display_name;
				}

				if ( mdjm_send_email_content( $email_args ) )	{
					MDJM()->debug->log_it( 'Deposit request sent to ' . $client->display_name );

					remove_action( 'save_post_mdjm-event', 'mdjm_save_event_post', 10, 3 );
					wp_update_post( array( 'ID' => $event->ID, 'post_modified' => date( 'Y-m-d H:i:s' ) ) );
					add_action( 'save_post_mdjm-event', 'mdjm_save_event_post', 10, 3 );

					update_post_meta( $event->ID, '_mdjm_event_last_updated_by', 0 );
					$event->complete_task( $this->slug );

					mdjm_add_journal(
						array(
							'user_id'         => 1,
							'event_id'        => $event->ID,
							'comment_content' => $this->name . ' task executed<br /><br />' . time()
						)
					);

				} else	{
					MDJM()->debug->log_it( 'ERROR: Deposit request was not sent. Event ID ' . $event->ID );
				}

			}

		} else	{
			MDJM()->debug->log_it( 'No events requiring deposit requests' );
		}

		MDJM()->debug->log_it( "*** $this->name task Completed ***", true );

		return true;
	} // request_deposit

	/**
	 * Build the task query
	 *
	 * @since	1.4.7
	 * @return	bool
	 */
	public function build_query()	{
		if ( 'after_approval' == $this->options['run_when'] )	{
			$run_date   = date( 'Y-m-d H:i:s', strtotime( "-" . $this->options['age'] ) );
			$date_query = array(
				'key'     => '_mdjm_event_task_after_approval_' . $this->slug,
				'compare' => '<',
				'value'   => $run_date,
				'type'    => 'datetime'
			);
		} else	{
			$run_date = date( 'Y-m-d', strtotime( "-" . $this->options['age'] ) );
			$date_query = array(
				'key'     => '_mdjm_event_date',
				'compare' => '>=',
				'value'   => $run_date,
				'type'    => 'date'
			);
		}

		switch ( $this->slug )	{
			case 'complete-events':
				$query = array(
					'post_status' => 'mdjm-approved',
					'meta_key'    => '_mdjm_event_date',
					'orderby'     => 'meta_value',
					'order'       => 'ASC',
					'meta_query'  => array(
						'key'     => '_mdjm_event_date',
						'value'   => date( 'Y-m-d' ),
						'type'    => 'date',
						'compare' => '<='
					)
				);
				break;

			case 'fail-enquiry':
				$expired = date( 'Y-m-d', strtotime( "-" . $this->options['age'] ) );

				$query = array(
					'post_status' => array( 'mdjm-unattended', 'mdjm-enquiry' ),
					'date_query'  => array(
						'before' => $expired
					)
				);
				break;

			case 'balance-reminder':
				$query = array(
					'post_status'  => 'mdjm-approved',
					'meta_query'   => array(
						'relation' => 'AND',
						$date_query,
						array(
							'key'     => '_mdjm_event_date',
							'compare' => '<=',
							'value'   => date( 'Y-m-d' ),
							'type'    => 'date'
						),
						array(
							'key'     => '_mdjm_event_balance_status',
							'value'   => 'Due'
						),
						array(
							'key'     => '_mdjm_event_cost',
							'value'   => '0.00',
							'compare' => '>'
						)
					)
				);
				break;

			case 'request-deposit':
				$query = array(
					'post_status' => 'mdjm-approved',
					'meta_query'  => array(
					'relation' => 'AND',
						$date_query,
						array(
							'key'     => '_mdjm_event_date',
							'compare' => '>=',
							'value'   => date( 'Y-m-d' ),
							'type'    => 'date'
						),
						array(
							'key'     => '_mdjm_event_deposit_status',
							'value'   => 'Due'
						),
						array(
							'key'     => '_mdjm_event_deposit',
							'value'   => '0.00',
							'compare' => '>',
						)
					)
				);
				break;

			default:
				$query = false;
		}

		return apply_filters( 'mdjm_task_query', $query, $this->slug, $this );
	} // build_query

} // MDJM_Task_Runner
