<?php


/**
 * Cron tasks
 *
 * @package MDJM
 * @subpackage Classes/Tasks
 * @copyright Copyright (c) 2016, Mike Howard
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 1.1.2
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class MDJM_Cron {

	/**
	 * Get things going
	 */
	public function __construct() {
		add_filter( 'cron_schedules', array( $this, 'add_schedules' ) );
		add_action( 'wp', array( $this, 'schedule_events' ) );
		add_action( 'mdjm_hourly_scheduled_events', array( $this, 'execute_tasks' ) );
	} // __construct

	/**
	 * Creates custom cron schedules within WP.
	 *
	 * @since 1.3.8.6
	 * @param arr $schedules Creates custom cron schedules.
	 * @return arr $schedules
	 */
	function add_schedules( $schedules = array() ) {
		// Adds once weekly to the existing schedules.
		$schedules['weekly'] = array(
			'interval' => 604800,
			'display'  => __( 'Once Weekly', 'mobile-dj-manager' ),
		);

		return $schedules;
	} // add_schedules

	/**
	 * Schedules our events
	 *
	 * @since 1.0
	 * @return void
	 */
	public function schedule_events() {
		$this->hourly_events();
		$this->daily_events();
		$this->weekly_events();
	} // schedule_events

	/**
	 * Schedule hourly events
	 *
	 * @since 1.0
	 * @return void
	 */
	private function hourly_events() {
		if ( ! wp_next_scheduled( 'mdjm_hourly_scheduled_events' ) ) {
			wp_schedule_event( time(), 'hourly', 'mdjm_hourly_scheduled_events' );
		}
	} // hourly_events

	/**
	 * Schedule daily events
	 *
	 * @since 1.4.7
	 * @return void
	 */
	private function daily_events() {
		if ( ! wp_next_scheduled( 'mdjm_daily_scheduled_events' ) ) {
			wp_schedule_event( time(), 'daily', 'mdjm_daily_scheduled_events' );
		}
	} // daily_events

	/**
	 * Schedule weekly events
	 *
	 * @since 1.4.7
	 * @return void
	 */
	private function weekly_events() {
		if ( ! wp_next_scheduled( 'mdjm_weekly_scheduled_events' ) ) {
			wp_schedule_event( time(), 'weekly', 'mdjm_weekly_scheduled_events' );
		}
	} // weekly_events

	/**
	 * Unschedule events.
	 *
	 * Runs on plugin deactivation.
	 *
	 * @since 1.4.7
	 * @return void
	 */
	public function unschedule_events() {
		wp_clear_scheduled_hook( 'mdjm_hourly_scheduled_events' );
		wp_clear_scheduled_hook( 'mdjm_daily_scheduled_events' );
		wp_clear_scheduled_hook( 'mdjm_weekly_scheduled_events' );
	} // unschedule_events

	/**
	 * Execute the schedules tasks which are due to be run
	 *
	 * @since   1.4.7
	 */
	public function execute_tasks() {
		require_once MDJM_PLUGIN_DIR . '/includes/class-mdjm-task-runner.php';
		$tasks = get_option( 'mdjm_schedules' );

		if ( $tasks ) {
			foreach ( $tasks as $slug => $task ) {
				new MDJM_Task_Runner( $slug );
			}
		}
	} // execute_tasks

	/**
	 * Setup the tasks
	 *
	 * @since   1.3
	 */
	public function create_tasks() {
		global $mdjm_options;

		$time = current_time( 'timestamp' );

		if ( isset( $mdjm_options['upload_playlists'] ) ) {
			$playlist_nextrun = strtotime( '+1 day', $time );
		} else {
			$playlist_nextrun = 'N/A';
		}

		$mdjm_schedules = array(
			'complete-events'          => array(
				'slug'        => 'complete-events',
				'name'        => __( 'Complete Events', 'mobile-dj-manager' ),
				'active'      => true,
				/* translators: %1: Payment Type %2: Event Type */
				'desc'        => sprintf( esc_html__( 'Mark %1$s as completed once the %2$s date has passed', 'mobile-dj-manager' ), mdjm_get_label_plural( true ), esc_html( mdjm_get_label_singular( true ) ) ),
				'frequency'   => 'Daily',
				'nextrun'     => $time,
				'lastran'     => 'Never',
				'options'     => array(
					'run_when' => 'after_event',
					'age'      => '1 HOUR',
				),
				'totalruns'   => '0',
				'default'     => true,
				'last_result' => false,
			),
			'request-deposit'          => array(
				'slug'        => 'request-deposit',
				'name'        => __( 'Request Deposit', 'mobile-dj-manager' ),
				'active'      => false,
				/* translators: %s: Event Type */
				'desc'        => sprintf( esc_html__( 'Send reminder email to client requesting deposit payment if %s status is Approved and deposit has not been received', 'mobile-dj-manager' ), esc_html( mdjm_get_label_singular( true ) ) ),
				'frequency'   => 'Daily',
				'nextrun'     => 'N/A',
				'lastran'     => __( 'Never', 'mobile-dj-manager' ),
				'options'     => array(
					'email_template' => '0',
					/* translators: %1: Payment Type %2: Event Type */
					'email_subject'  => sprintf( esc_html__( 'The %1$s for your %2$s is now due', 'mobile-dj-manager' ), mdjm_get_balance_label(), esc_html( mdjm_get_label_singular( true ) ) ),
					'email_from'     => 'admin',
					'run_when'       => 'after_approval',
					'age'            => '3 DAY',
				),
				'totalruns'   => '0',
				'default'     => true,
				'last_result' => false,
			),
			'balance-reminder'         => array(
				'slug'        => 'balance-reminder',
				'name'        => __( 'Balance Reminder', 'mobile-dj-manager' ),
				'active'      => false,
				/* translators: %s: Event Type */
				'desc'        => sprintf( esc_html__( 'Send email to client requesting they pay remaining balance for %s', 'mobile-dj-manager' ), esc_html( mdjm_get_label_singular( true ) ) ),
				'frequency'   => 'Daily',
				'nextrun'     => 'N/A',
				'lastran'     => __( 'Never', 'mobile-dj-manager' ),
				'options'     => array(
					'email_template' => '0',
					/* translators: %1: payment type %2: Event Type */
					'email_subject'  => sprintf( esc_html__( 'The %1$s for your %2$s is now due', 'mobile-dj-manager' ), mdjm_get_deposit_label(), esc_html( mdjm_get_label_singular( true ) ) ),
					'email_from'     => 'admin',
					'run_when'       => 'before_event',
					'age'            => '2 WEEK',
				),
				'totalruns'   => '0',
				'default'     => true,
				'last_result' => false,
			),
			'fail-enquiry'             => array(
				'slug'        => 'fail-enquiry',
				'name'        => __( 'Fail Enquiry', 'mobile-dj-manager' ),
				'active'      => false,
				/* translators: %s: Event Type */
				'desc'        => sprintf( esc_html__( 'Automatically set %s status to Failed for enquiries that have not been updated within the specified amount of time', 'mobile-dj-manager' ), esc_html( mdjm_get_label_singular() ) ),
				'frequency'   => 'Daily',
				'nextrun'     => 'N/A',
				'lastran'     => 'Never',
				'options'     => array(
					'run_when' => 'event_created',
					'age'      => '2 WEEK',
				),
				'totalruns'   => '0',
				'default'     => true,
				'last_result' => false,
			),
			'playlist-notification'    => array(
				'slug'        => 'playlist-notification',
				'name'        => __( 'Client Playlist Notifications', 'mobile-dj-manager' ),
				'active'      => false,
				'desc'        => __( 'Sends notifications to clients if a guest has added an entry to the playlist.', 'mobile-dj-manager' ),
				'frequency'   => 'Daily',
				'nextrun'     => 'N/A',
				'lastran'     => 'Never',
				'options'     => array(
					'run_when'       => 'after_event',
					'age'            => '1 HOUR',
					'email_template' => '0',
					/* translators: %s: Event Type */
					'email_subject'  => sprintf( esc_html__( 'Your %s playlist has been updated', 'mobile-dj-manager' ), esc_html( mdjm_get_label_singular( true ) ) ),
					'email_from'     => 'admin',
				),
				'totalruns'   => '0',
				'default'     => true,
				'last_result' => false,
			),
			'playlist-employee-notify' => array(
				'slug'        => 'playlist-employee-notify',
				'name'        => __( 'Employee Playlist Notification', 'mobile-dj-manager' ),
				'active'      => false,
				/* translators: %s: Event Type */
				'desc'        => sprintf( esc_html__( 'Sends notifications to an employee if an %s playlist has entries.', 'mobile-dj-manager' ), esc_html( mdjm_get_label_singular( true ) ) ),
				'frequency'   => 'Daily',
				'nextrun'     => 'N/A',
				'lastran'     => 'Never',
				'options'     => array(
					'run_when'      => 'before_event',
					'age'           => '3 DAY',
					/* translators: %s: Event Type */
					'email_subject' => sprintf( esc_html__( '%s playlist notification', 'mobile-dj-manager' ), esc_html( mdjm_get_label_singular() ) ),
					'email_from'    => 'admin',
				),
				'totalruns'   => '0',
				'default'     => true,
				'last_result' => false,
			),
		);

		update_option( 'mdjm_schedules', $mdjm_schedules );
	} // create_tasks

} // class
