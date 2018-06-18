<?php
/**
 * Availability
 *
 * @package     MDJM
 * @subpackage  Classes/Availability Checker
 * @copyright   Copyright (c) 2016, Mike Howard
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.3
*/

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) )
	exit;

/**
 * MDJM_Availability_Checker Class
 *
 * @since	1.3
 */
class MDJM_Availability_Checker {

	/**
	 * The start date to check
	 *
	 * @since	1.3
	 */
	public $start = 0;

	/**
	 * The end date of the check
	 *
	 * @since	1.3
	 */
	public $end = 0;

	/**
	 * The employees to check
	 *
	 * @since	1.3
	 */
	public $employees = array();

	/**
	 * The employee roles to report on
	 *
	 * @since	1.3
	 */
	public $roles = array();

	/**
	 * The event status' to report on
	 *
	 * @since	1.3
	 */
	public $status;

	/**
	 * The availability check result
	 *
	 * @since	1.3
	 */
	public $result = array();

	/**
	 * Employees that are available
	 *
	 * @since	1.5.6
	 */
	public $available = array();

	/**
	 * Employees that are not available
	 *
	 * @since	1.5.6
	 */
	public $unavailable = array();

	/*
	 * Array of absentees.
	 *
	 * @since	1.5.6
	 */
	public $absentees = array();

	/**
	 * The full availability check result
	 *
	 * @since	1.5.6
	 */
	public $results = array();

	/**
	 * Get things going
	 *
	 * All vars are optional.
	 * 
	 * Dates can be parsed either as a unix timestamp
	 * or as an english formatted date.
	 * See http://php.net/manual/en/datetime.formats.php.
	 *
	 * If no dates are provided the current day will be assumed.
	 *
	 * $roles is only referenced if no $employees are provided.
	 *
	 * @since	1.3
	 * $param	string			$start		The start date for the checker
	 * $param	string			$end		The end date for the checker
	 * @param	int|array		$employees	Employee ID, or an array of employee IDs
	 * @param	string|array	$roles		Employee role, or array of roles
	 * @param	string|array	$status		Event status, or array of event statuses
	 */
	public function __construct(
		$start     = false,
		$end       = false,
		$employees = array(),
		$roles     = array(),
		$status    = array()
	) {	return $this->setup_check( $start, $end, $employees, $roles, $status ); }
	// __construct

	/**
	 * Setup the availability checker.
	 *
	 * @since	1.3
	 * @param	str		$start	The start date of the check
	 * @return	bool
	 */
	public function setup_check( $start, $end, $employees, $roles, $status )	{
		$this->setup_dates( $start, $end );
		$this->setup_roles( $roles );
		$this->setup_employees( $employees );
		$this->setup_status( $status );

		return true;
	} // setup_check

	/**
	 * Setup dates.
	 *
	 * @since	1.5.6
	 * @param	mixed	$start
	 * @param	mixed	$end
	 * @return	array	Array of statuses to check
	 */
	public function setup_dates( $start, $end )	{

		$now = current_time( 'timestamp' );

		if ( ! empty( $start ) )	{
			if ( is_numeric( $start ) )	{
				$start = date( 'Y-m-d', $start );
			}
		}

		if ( ! empty( $end ) )	{
			if ( is_numeric( $end ) )	{
				$end = date( 'Y-m-d', $end );
			}
		}

		$start = ! empty( $start ) ? strtotime( $start ) : $now;
		$end   = ! empty( $end )   ? strtotime( $end )   : $start;

		$this->start = strtotime( date( 'Y-m-d', $start ) . ' 00:00:00' );
		$this->end   = strtotime( date( 'Y-m-d', $end )   . ' 23:59:59' );

	} // setup_dates

	/**
	 * Setup roles.
	 *
	 * @since	1.5.6
	 * @param	mixed	$roles
	 * @return	array	Array of roles to check
	 */
	public function setup_roles( $roles )	{
		$roles = ! empty( $roles )  ? $roles  : mdjm_get_availability_roles();
		$roles = ! empty( $roles )  ? $roles  : mdjm_get_roles( $roles );

		if ( ! is_array( $roles ) )	{
			$roles = array( $roles );
		}

		$this->roles = $roles;
	} // setup_roles

	/**
	 * Setup employees.
	 *
	 * @since	1.5.6
	 * @param	mixed	$employees
	 * @return	array	Array of employees to check
	 */
	public function setup_employees( $employees )	{
		$employees = ! empty( $employees )  ? $employees : mdjm_get_employees( $this->roles );
		$employees = is_array( $employees ) ? $employees : array( $employees );

		foreach( $employees as $employee )	{
			if ( is_object( $employee ) )	{
				$this->employees[] = $employee->ID;
			} else	{
				$this->employees[] = $employee;
			}
		}

        $this->employees = array_map( 'intval', array_unique( $this->employees ) );
		$this->available = $this->employees;

	} // setup_employees

	/**
	 * Setup status.
	 *
	 * @since	1.5.6
	 * @param	mixed	$status
	 * @return	array	Array of statuses to check
	 */
	public function setup_status( $status )	{
		$status = ! empty( $status ) ? $status : mdjm_get_availability_statuses();

		$this->status = ! is_array( $status ) ? array( $status ) : $status;
	} // setup_status

	/**
	 * Perform the availability lookup.
	 *
	 * @since	1.3
	 * @param
	 * @return	bool
	 */
	public function check_availability()	{

		foreach( $this->employees as $employee_id )	{

			if ( ! $this->employee_working( $employee_id ) && ! $this->employee_has_vacation( $employee_id ) )	{
				$this->result['available'][] = $employee_id;
			} else	{
				$this->result['unavailable'][] = $employee_id;
			}
		}

		if ( ! empty( $this->result['available'] ) )	{
			return true;
		}

		return false;
	} // check_availability

	/**
	 * Perform a detailed lookup.
	 *
	 * @since	1.5.6
	 */
	public function availability_check()	{
        if ( ! empty( $this->available ) )  {
			$this->check_absences();
        }

        if ( ! empty( $this->available ) )  {
            $this->check_events();
        }
	} // availability_check

	/**
	 * Checks employee absences for the given date(s).
	 *
	 * @since	1.5.6
	 * @return	array	Array of absence data
	 */
	public function check_absences()	{
		$absences = MDJM()->availability_db->get_entries( array(
			'employee_id' => $this->available,
			'start'       => $this->start,
			'end'         => $this->end,
			'number'      => 100
		) );

		foreach( $absences as $absence )	{
			$this->unavailable[ $absence->employee_id ][] = array(
				'absence' => array(
					'id'    => $absence->id,
					'start' => $absence->start,
					'end'   => $absence->end,
					'notes' => stripslashes( $absence->notes )
				)
			);

			$this->absentees[] = $absence->employee_id;

            if ( false !== $key = array_search( $absence->employee_id, $this->available ) ) {
                unset( $this->available[ $key ] );
            }
		}
	} // check_absences

    /**
	 * Checks active events for the given date(s).
	 *
	 * @since	1.5.6
	 * @return	array	Array of absence data
	 */
    function check_events() {
        $employees_query = array();

        foreach( $this->available as $employee_id )    {
            $employees_query[] = array(
                'key'     => '_mdjm_event_employees',
                'value'   => sprintf( ':"%s";', $employee_id ),
                'compare' => 'LIKE'
            );
        }

        $events = mdjm_get_events( array(
            'post_status'    => $this->status,
            'posts_per_page' => -1,
            'meta_key'       => '_mdjm_event_date',
            'meta_value'     => date( 'Y-m-d', $this->start ),
            'meta_query'     => array(
                'relation' => 'OR',
                array(
                    'key'     => '_mdjm_event_dj',
                    'value'   => implode( ',', $this->available ),
                    'compare' => 'IN',
                    'type'    => 'NUMERIC'
                ),
                $employees_query
            )
        ) );

        foreach( $events as $event )    {
			$mdjm_event = new MDJM_Event( $event->ID );
            $employees  = $mdjm_event->get_all_employees();

            foreach( $employees as $employee_id => $data )  {

				$this->unavailable[ $absence->employee_id ][] = array(
					'events' => array(
						'id'     => $event->ID,
						'date'   => $mdjm_event->date,
						'end'    => $mdjm_event->get_finish_date(),
						'start'  => $mdjm_event->get_start_time(),
						'finish' => $mdjm_event->get_finish_time(),
						'role'   => $data['role'],
						'status' => $mdjm_event->get_status()
					)
				);

                if ( ! in_array( $employee_id, $this->absentees ) ) {
                    $this->absentees[] = $employee_id;
                }

                if ( false !== $key = array_search( $employee_id, $this->available ) ) {
                    unset( $this->available[ $key ] );
                }
            }
        }
    } // check_events

    /**
	 * Retrieve entries for the calendar.
	 *
	 * @since	1.5.6
	 */
	public function get_calendar_entries()	{
		$this->get_absences_in_range();
        $this->get_events_in_range();

        return $this->results;
	} // get_calendar_entries

	/**
	 * Retrieve employee absences within the given date range.
	 *
	 * @since	1.5.6
	 * @return	array	Array of absence data
	 */
	public function get_absences_in_range()	{
        $date_format = get_option( 'date_format' );
        $time_format = get_option( 'time_format' );

		$absences = MDJM()->availability_db->get_entries( array(
			'employee_id' => $this->available,
			'start'       => $this->start,
			'end'         => $this->end,
            'calendar'    => true,
			'number'      => 100
		) );

		foreach( $absences as $entry )	{

            $short_date_start = date( 'Y-m-d', strtotime( $entry->start ) );
			$short_date_end   = date( 'Y-m-d', strtotime( $entry->end ) );
            $description      = array();
            $employee         = mdjm_get_employee_display_name( $entry->employee_id );
			$title            = __( 'Unknown', 'mobile-dj-manager' );
            $all_day          = ! empty( $entry->all_day ) ? true : false;

			if ( ! empty( $employee ) )	{
				$title = sprintf( '%s: %s', __( 'Absence', 'mobile-dj-manager' ), $employee );
			}

			$from = date( $time_format . ' \o\n ' . $date_format, strtotime( $entry->start ) );
			$to   = date( $time_format . ' \o\n ' . $date_format, strtotime( $entry->end ) );

			$description[] = sprintf( __( 'From: %s', 'mobile-dj-manager' ), $from );
			$description[] = sprintf( __( 'To: %s', 'mobile-dj-manager' ), $to );

			if ( ! empty( $entry->notes ) )	{
				$description[] = stripslashes( $entry->notes );
			}

            $description  = '<p>' . implode( '<br>', $description ) . '</p>';

            if ( mdjm_employee_can( 'manage_employees' ) )  {
                $description .= sprintf(
                    '<p><a class="mdjm-delete availability-link delete-absence" data-entry="%d" href="#" >%s</a></p>',
                    $entry->id,
                    __( 'Delete entry', 'mobile-dj-manager' )
                );
            }

            $this->results[] = array(
				'allDay'          => $all_day,
				'backgroundColor' => mdjm_get_calendar_color(),
				'borderColor'     => mdjm_get_calendar_color( 'border' ),
                'className'       => 'mdjm_calendar_absence',
				'end'             => $entry->end,
				'id'              => $entry->id,
				'notes'           => $description,
				'start'           => $entry->start,
				'textColor'       => mdjm_get_calendar_color( 'text' ),
				'tipTitle'        => $title,
				'title'           => $employee
            );

            $array_key = $key = array_search( $entry->employee_id, $this->available );
			if ( false !== $key ) {
                unset( $this->available[ $key ] );
            }
		}
	} // get_absences_in_range

    /**
	 * Retrieve events within the given date range.
     *
     * We only need to search for events where an employee is available.
     * i.e. not absent as a result of the get_absences_in_range() method
	 *
	 * @since	1.5.6
	 * @return	array	Array of absence data
	 */
	public function get_events_in_range()	{
        $event_statuses   = mdjm_active_event_statuses();
        $event_statuses[] = 'mdjm-completed';

        $query_args = array(
            'post_status' => $event_statuses,
            'meta_query'  => array(
                'key'     => '_mdjm_event_date',
                'value'   => array( date( 'Y-m-d', $this->start ), date( 'Y-m-d', $this->end ) ),
                'compare' => 'BETWEEN',
                'type'    => 'DATE'
            )
        );

        $query_args = apply_filters( 'mdjm_events_in_range_args', $query_args );
        $events     = mdjm_get_events( $query_args );

        if ( $events )  {
            foreach( $events as $event )    {
                $popover      = 'top';
                $mdjm_event   = new MDJM_Event( $event->ID );
                $event_type   = $mdjm_event->get_type();
                $event_status = $mdjm_event->get_status();
                $employee     = mdjm_get_employee_display_name( $mdjm_event->employee_id );
                $event_id     = mdjm_get_event_contract_id( $mdjm_event->ID );
                $title        = sprintf( '%s (%s)', esc_attr( $event_type ), esc_attr( $event_status) );
                $description  = array();
                $notes        = mdjm_get_calendar_event_description_text();
                $notes        = mdjm_do_content_tags( $notes, $mdjm_event->ID, $mdjm_event->client );
                $tip_title    = sprintf(
                    '%s %s - %s',
                    esc_html( mdjm_get_label_singular() ),
                    $event_id,
                    esc_attr( $event_type )
                );			

                $event_url = add_query_arg( array(
                    'post'   => $mdjm_event->ID,
                    'action' => 'edit'
                ), admin_url( 'post.php' ) );

                $notes .= sprintf(
                    '<p><a class="availability-link" href="%s" >%s</a></p>',
                    $event_url,
                    sprintf( __( 'View %s', 'mobile-dj-manager' ), mdjm_get_label_singular( true ) )
                );

                $this->results[] = array(
                    'allDay'          => false,
                    'backgroundColor' => mdjm_get_calendar_color( 'background', true ),
                    'borderColor'     => mdjm_get_calendar_color( 'border', true ),
                    'className'       => 'mdjm_calendar_event',
                    'end'             => $mdjm_event->get_finish_date() . ' ' . $mdjm_event->get_finish_time(),
                    'id'              => $mdjm_event->ID,
                    'notes'           => $notes,
                    'start'           => $mdjm_event->date . ' ' . $mdjm_event->get_start_time(),
                    'textColor'       => mdjm_get_calendar_color( 'text', true ),
                    'tipTitle'        => $tip_title,
                    'title'           => $title
                );
            }
        }
    } // get_events_in_range

	/**
	 * Determine if the employee is working on the given day.
	 *
	 * @since	1.3
	 * @param	int		$employee	The employee ID
	 * @param	int		$start		The date
	 * @return	bool	True if the employee has an event, or false
	 */
	public function employee_working( $employee_id )	{
		return mdjm_employee_is_working( $this->start, $employee_id, $this->status );
	} // employee_working

	/**
	 * Determine if the employee has vacation on the given day.
	 *
	 * @since	1.3
	 * @param	int		$employee	The employee ID
	 * @param	int		$start		The date
	 * @return	bool	True if the employee has vacation, or false
	 */
	public function employee_has_vacation( $employee_id )	{
		return mdjm_employee_is_on_vacation( $this->start, $employee_id );
	} // employee_has_vacation
} // class MDJM_Availability_Checker
