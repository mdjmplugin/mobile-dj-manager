<?php
/*
 * MDJM Cron Class
 * 10/03/2015
 * @since 1.1.2
 * The MDJM cron class
 */
	
/* -- Build the MDJM_Cron class -- */
class MDJM_Cron	{
		
	/*
	 * Get things going
	 */
	public function __construct()	{
		add_filter( 'cron_schedules', array( $this, 'add_schedules'   ) );
		add_action( 'mdjm_hourly_schedule', array( &$this, 'execute_cron' ) ); // Run the MDJM scheduler
	} // __construct

	/**
	 * Creates custom cron schedules within WP.
	 *
	 * @since	1.3.8.6
	 * @return	void
	 */
	function add_schedules( $schedules = array() )	{
		// Adds once weekly to the existing schedules.
		$schedules['weekly'] = array(
			'interval' => 604800,
			'display'  => __( 'Once Weekly', 'mobile-dj-manager' )
		);

		return $schedules;
	} // add_schedules

	/*
	 * Execute the schedules tasks which are due to be run
	 *
	 * @since	1.4.7
	 * @return	void
	 */
	public function execute_cron()	{
		require_once( MDJM_PLUGIN_DIR . '/includes/class-mdjm-task-runner.php' );
		$tasks = get_option( 'mdjm_schedules' );

		if ( $tasks )	{
			foreach( $tasks as $slug => $task )	{
				new MDJM_Task_Runner( $slug );
			}
		}
	} // execute_cron

	/*
	 * Setup the tasks
	 *
	 * @since	1.3
	 * @return	void
	 */
	public function create_tasks()	{
		global $mdjm_options;
		
		$time = current_time( 'timestamp' );
		
		if( isset( $mdjm_options['upload_playlists'] ) )	{
			$playlist_nextrun = strtotime( '+1 day', $time );
		} else	{
			$playlist_nextrun = 'N/A';
		}
		
		$mdjm_schedules = array(
			'complete-events'    => array(
				'slug'           => 'complete-events',
				'name'           => __( 'Complete Events', 'mobile-dj-manager' ),
				'active'         => true,
				'desc'           => sprintf( __( 'Mark %s as completed once the %s date has passed', 'mobile-dj-manager' ), mdjm_get_label_plural( true ), mdjm_get_label_singular( true ) ),
				'frequency'      => 'Daily',
				'nextrun'        => $time,
				'lastran'        => 'Never',
				'options'        => array(
					'run_when'       => 'after_event',
					'age'            => '1 HOUR'
				),
				'totalruns'      => '0',
				'default'        => true,
				'last_result'    => false
			),			
			'request-deposit'    => array(
				'slug'           => 'request-deposit',
				'name'           => __( 'Request Deposit', 'mobile-dj-manager' ),
				'active'         => false,
				'desc'           => sprintf( __( 'Send reminder email to client requesting deposit payment if %s status is Approved and deposit has not been received', 'mobile-dj-manager' ), mdjm_get_label_singular( true ) ),
				'frequency'      => 'Daily',
				'nextrun'        => 'N/A',
				'lastran'        => __( 'Never', 'mobile-dj-manager' ),
				'options'        => array(
					'email_template' => '0',
					'email_subject'  => __( 'Request Deposit Task Complete', 'mobile-dj-manager' ),
					'email_from'	 => 'admin',
					'run_when'	   => 'after_approval',
					'age'			=> '3 DAY'
				),
				'totalruns'      => '0',
				'default'        => true,
				'last_result'    => false
			),			
			'balance-reminder'    => array(
				'slug'            => 'balance-reminder',
				'name'            => __( 'Balance Reminder', 'mobile-dj-manager' ),
				'active'          => false,
				'desc'            => sprintf( __( 'Send email to client requesting they pay remaining balance for %s', 'mobile-dj-manager' ), mdjm_get_label_singular( true ) ),
				'frequency'       => 'Daily',
				'nextrun'         => 'N/A',
				'lastran'         => __( 'Never', 'mobile-dj-manager' ),
				'options'         => array(
					'email_template' => '0',
					'email_subject'  => __( 'Balance Reminder Task Complete', 'mobile-dj-manager' ),
					'email_from'     => 'admin',
					'run_when'       => 'before_event',
					'age'            => '2 WEEK'
				),
				'totalruns'       => '0',
				'default'         => true,
				'last_result'     => false
			), 
			'fail-enquiry'         => array(
				'slug'             => 'fail-enquiry',
				'name'             => __( 'Fail Enquiry', 'mobile-dj-manager' ),
				'active'           => false,
				'desc'             => sprintf( __( 'Automatically set %s status to Failed for enquiries that have not been updated within the specified amount of time', 'mobile-dj-manager' ), mdjm_get_label_singular() ),
				'frequency'        => 'Daily',
				'nextrun'          => 'N/A',
				'lastran'          => 'Never',
				'options'          => array(
					'run_when'	   => 'event_created',
					'age'			=> '2 WEEK'
				),
				'totalruns'          => '0',
				'default'            => true,
				'last_result'        => false
			),		
			'upload-playlists'      => array(
				'slug'              => 'upload-playlists',
				'name'              => __( 'Upload Playlists', 'mobile-dj-manager' ),
				'active'            => true,
				'desc'              => __( 'Transmit playlist information back to the MDJM servers to help build an information library. This option is updated the MDJM Settings pages.', 'mobile-dj-manager' ),
				'frequency'         => 'Twice Daily',
				'nextrun'           => $playlist_nextrun,
				'lastran'           => 'Never',
				'options'           => array(
					'run_when'        => 'after_event',
					'age'             => '1 HOUR'
				),
				'totalruns'           => '0',
				'default'             => true,
				'last_result'         => false
			)
		);
		
		update_option( 'mdjm_schedules', $mdjm_schedules );
	} // create_tasks

} // class
