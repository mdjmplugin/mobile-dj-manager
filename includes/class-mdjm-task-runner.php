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
	 * The task slug
	 *
	 * @since	1.4.7
	 */
	public $slug;

	/**
	 * The task name
	 *
	 * @since	1.4.7
	 */
	public $name;

	/**
	 * Active task
	 *
	 * @since	1.4.7
	 */
	public $active;

	/**
	 * The task description
	 *
	 * @since	1.4.7
	 */
	public $description;

	/**
	 * The task frequency
	 *
	 * @since	1.4.7
	 */
	public $frequency;

	/**
	 * Last run
	 *
	 * @since	1.4.7
	 */
	public $last_run;

	/**
	 * Next run
	 *
	 * @since	1.4.7
	 */
	public $next_run;

	/**
	 * Total runa
	 *
	 * @since	1.4.7
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
	 */
	public $default;

	/**
	 * The event ID
	 *
	 * @since	1.4.7
	 */
	public $event = 0;

	/**
	 * Get things going
	 *
	 * @since	1.4.7
	 */
	public function __construct( $task = false ) {

		if ( empty( $task ) )	{
			return false;
		}
		
		return $this->setup_task( $task );
	} // __construct

	/**
	 * Given the task slug, let's set the variables
	 *
	 * @since	1.4.7
	 * @param 	str		$task	The Task slug
	 * @return	bool	If the setup was successful or not
	 */
	private function setup_task( $task )	{
		$all_tasks = get_option( 'mdjm_schedules' );

		if ( empty( $all_tasks ) || ! array_key_exists( $task ) )	{
			return false;
		}

		$this_task = $all_tasks[ $task ];

		$this->task        = $task;
		$this->name        = $this_task['name'];
		$this->description = $this_task['desc'];
		$this->frequency   = $this_task['frequency'];
		$this->next_run    = ! empty( $this_task['nextrun'] )   ? $this_task['nextrun'] : '';
		$this->last_run    = ! empty( $this_task['lastran'] )   ? $this_task['lastran'] : 'never';
		$this->total       = ! empty( $this_task['totalruns'] ) ? $this_task['totalruns'] : 0;
		$this->options     = $this_task['options'];
		$this->default     = ! empty( $this_task['default'] ) ? true : false;

	} // setup_task

}
