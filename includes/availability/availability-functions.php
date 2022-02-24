<?php
/**
 * This plugin utilizes Open Source code. Details of these open source projects along with their licenses can be found below.
 * We acknowledge and are grateful to these developers for their contributions to open source.
 *
 * Project: mobile-dj-manager https://github.com/deckbooks/mobile-dj-manager
 * License: (GNU General Public License v2.0) https://github.com/deckbooks/mobile-dj-manager/blob/master/license.txt
 *
 * @author: Mike Howard, Jack Mawhinney, Dan Porter
 *
 * Contains all availability checker related functions
 *
 * @package     MDJM
 * @subpackage  Availability
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Retrieve absence title
 *
 * @since   1.5.7
 * @return  string  Absence title
 */
function mdjm_get_calendar_absence_title() {
	$absence_title = mdjm_get_option( 'calendar_absence_title' );
	return stripslashes( $absence_title );
} // mdjm_get_calendar_absence_title

/**
 * Retrieve absence tip title
 *
 * @since   1.5.7
 * @return  string  Absence tip title
 */
function mdjm_get_calendar_absence_tip_title() {
	$tip_title = mdjm_get_option( 'calendar_absence_tip_title' );
	return stripslashes( $tip_title );
} // mdjm_get_calendar_absence_tip_title

/**
 * Retrieve absence content
 *
 * @since   1.5.7
 * @return  string  Absence tip content
 */
function mdjm_get_calendar_absence_tip_content() {
	$tip_content = mdjm_get_option( 'calendar_absence_tip_content' );
	return stripslashes( $tip_content );
} // mdjm_get_calendar_absence_tip_content

/**
 * Retrieve event title
 *
 * @since   1.5.7
 * @return  string  Event title
 */
function mdjm_get_calendar_event_title() {
	$event_title = mdjm_get_option( 'calendar_event_title' );
	return stripslashes( $event_title );
} // mdjm_get_calendar_event_title

/**
 * Retrieve absence tip title
 *
 * @since   1.5.7
 * @return  string  Event tip title
 */
function mdjm_get_calendar_event_tip_title() {
	$tip_title = mdjm_get_option( 'calendar_event_tip_title' );
	return stripslashes( $tip_title );
} // mdjm_get_calendar_event_tip_title

/**
 * Retrieve event content
 *
 * @since   1.5.7
 * @return  string  Event tip content
 */
function mdjm_get_calendar_event_tip_content() {
	$tip_content = mdjm_get_option( 'calendar_event_tip_content' );
	return stripslashes( $tip_content );
} // mdjm_get_calendar_event_tip_content

/**
 * Retrieve absence content tags
 *
 * @since   1.5.7
 * @return  array   Array of tags
 */
function mdjm_get_absence_content_tags() {
	$absence_tags = array(
		'{employee_name}',
		'{start}',
		'{end}',
		'{notes}',
	);

	$absence_tags = apply_filters( 'mdjm_absence_tags', $absence_tags );
	return $absence_tags;
} // mdjm_get_absence_content_tags

/**
 * Display available absence content tags
 *
 * @since   1.5.7
 * @param   string $seperator  The string to use to seperate the tags
 * @return  string  Absence tags
 */
function mdjm_display_absence_content_tags( $seperator = ', ' ) {
	$absence_tags = array();

	foreach ( mdjm_get_absence_content_tags() as $absence_tag ) {
		$absence_tags[] = sprintf( '<code>%s</code>', $absence_tag );
	}

	return implode( $seperator, $absence_tags );
} // mdjm_display_absence_content_tags

/**
 * Perform the content tag replacements for absences.
 *
 * @since   1.5.7
 * @param   string $content    The content to search and replace
 * @param   object $absence    Absence database object
 * @return  string  Filtered content
 */
function mdjm_do_absence_content_tags( $content = '', $absence = false ) {
	if ( ! empty( $content ) && ! empty( $absence ) ) {
		$employee_id = $absence->employee_id;
		$start       = strtotime( $absence->start );
		$end         = strtotime( $absence->end );
		$notes       = ! empty( $absence->notes ) ? stripslashes( $absence->notes ) : '';
		$date_format = get_option( 'date_format' ) . ' ' . get_option( 'time_format' );

		$search = mdjm_get_absence_content_tags();

		$replace = array(
			mdjm_get_employee_display_name( $employee_id ),
			date( $date_format, $start ),
			date( $date_format, $end ),
			$notes,
		);

		$content = str_replace( $search, $replace, $content );
	}

	return $content;
} // mdjm_do_absence_content_tags

/**
 * Retrieve the default event statuses that make an employee unavailable.
 *
 * @since   1.5.6
 * @return  array
 */
function mdjm_get_availability_statuses() {
	$statuses = mdjm_get_option( 'availability_status', 'any' );
	return apply_filters( 'mdjm_availability_statuses', $statuses );
} // mdjm_get_availability_statuses

/**
 * Retrieve the default roles to check for availability.
 *
 * @since   1.5.6
 * @return  array
 */
function mdjm_get_availability_roles() {
	$roles = mdjm_get_option( 'availability_roles', array() );
	return apply_filters( 'mdjm_availability_roles', $roles );
} // mdjm_get_availability_roles

/**
 * Set the correct time format for the calendar
 *
 * @since   1.5.6
 * @return
 */
function mdjm_format_calendar_time() {
	$time_format = get_option( 'time_format' );

	$search  = array( 'g', 'G', 'i', 'a', 'A' );
	$replace = array( 'h', 'H', 'mm', 't', 'T' );

	$time_format = str_replace( $search, $replace, $time_format );

	return apply_filters( 'mdjm_format_calendar_time', $time_format );
} // mdjm_format_calendar_time

/**
 * Retrieve the default view for the calendar.
 *
 * @since   1.5.6
 * @param   bool $dashboard  True if the view is for the dashboard page
 * @return  string  The default view for the calendar
 */
function mdjm_get_calendar_view( $dashboard = false ) {
	$option_name = ! $dashboard ? 'availability' : 'dashboard';
	$option_name = $option_name . '_view';

	$view = mdjm_get_option( $option_name, 'month' );

	return apply_filters( 'mdjm_calendar_view', $view, $dashboard );
} // mdjm_get_calendar_view

/**
 * Retrieve view options for the calendar
 *
 * @since   1.5.6
 * @return  array   Array of view options
 */
function mdjm_get_calendar_views() {
	$views = array(
		'agendaDay'  => __( 'Day', 'mobile-dj-manager' ),
		'list'       => __( 'List', 'mobile-dj-manager' ),
		'month'      => __( 'Month', 'mobile-dj-manager' ),
		'agendaWeek' => __( 'Week', 'mobile-dj-manager' ),
	);

	return apply_filters( 'mdjm_calendar_views', $views );
} // mdjm_get_calendar_views

/**
 * Retrieve color options for the calendar
 *
 * @since   1.5.6
 * @param   $color  string  Which color to retrieve
 * @param   $event  bool    True if retrieving for an event or false for an asbence
 * @return  array   Array of view options
 */
function mdjm_get_calendar_color( $color = 'background', $event = false ) {
	$option_name = ! $event ? 'absence_' : 'event_';
	$option_name = $option_name . $color . '_color';
	$return      = mdjm_get_option( $option_name );

	return apply_filters( 'mdjm_calendar_color', $return, $color, $event, $option_name );
} // mdjm_get_calendar_color

/**
 * Retrieve all dates within the given range
 *
 * @since   1.5.6
 * @param   string $start      The start date Y-m-d
 * @param   string $end        The end date Y-m-d
 * @return  array   Array of all dates between two given dates
 */
function mdjm_get_all_dates_in_range( $start, $end ) {
	$start = \DateTime::createFromFormat( 'Y-m-d', $start );
	$end   = \DateTime::createFromFormat( 'Y-m-d', $end );

	$range = new \DatePeriod( $start, new \DateInterval( 'P1D' ), $end->modify( '+1 day' ) );

	return $range;
} // mdjm_get_all_dates_in_range

/**
 * Add an employee absence entry.
 *
 * @since   1.5.6
 * @param   int   $employee_id    Employee user ID
 * @param   array $data           Array of absence data
 * @return  bool    True on success, otherwise false
 */
function mdjm_add_employee_absence( $employee_id, $data ) {
	$employee_id = absint( $employee_id );

	if ( empty( $employee_id ) ) {
		return false;
	}

	$all_day    = isset( $data['all_day'] ) ? $data['all_day'] : 0;
	$start      = ! empty( $data['start'] ) ? $data['start'] : date( 'Y-m-d' );
	$end        = ! empty( $data['end'] ) ? $data['end'] : date( 'Y-m-d' );
	$start_time = ! empty( $data['start_time'] ) ? $data['start_time'] : '00:00:00';
	$end_time   = ! empty( $data['end_time'] ) ? $data['end_time'] : '00:00:00';
	$start      = $start . ' ' . $start_time;
	$end        = $end . ' ' . $end_time;
	$notes      = ! empty( $data['notes'] ) ? $data['notes'] : '';

	if ( $all_day ) {
		$end = date( 'Y-m-d', strtotime( '+1 day', strtotime( $end ) ) ) . ' 00:00:00';
	}

	$args                = array();
	$args['employee_id'] = $employee_id;
	$args['all_day']     = $all_day;
	$args['start']       = $start;
	$args['end']         = $end;
	$args['notes']       = sanitize_textarea_field( $notes );

	$args = apply_filters( 'mdjm_add_employee_absence_args', $args, $employee_id, $data );

	do_action( 'mdjm_before_add_employee_absence', $args, $data );

	$return = MDJM()->availability_db->add( $args );

	do_action( 'mdjm_add_employee_absence', $args, $data, $return );
	do_action( 'mdjm_add_employee_absence_' . $employee_id, $args, $data, $return );

	return $return;
} // mdjm_add_employee_absence

/**
 * Remove an employee absence entry.
 *
 * @since   1.5.6
 * @param   int $id     The absence ID to remove
 * @return  int     The number of rows deleted or false
 */
function mdjm_remove_employee_absence( $id ) {

	$id = absint( $id );

	if ( empty( $id ) ) {
		return false;
	}

	do_action( 'mdjm_before_remove_employee_absence', $id );

	$deleted = MDJM()->availability_db->delete( $id );

	do_action( 'mdjm_remove_employee_absence', $id, $deleted );

	return $deleted;
} // mdjm_remove_employee_absence

/**
 * Retrieve all employee absences.
 *
 * @since   1.5.6
 * @return  array   Array of database result objects
 */
function mdjm_get_all_absences() {
	$absences = MDJM()->availability_db->get_entries(
		array(
			'number'         => -1,
			'employees_only' => true,
		)
	);

	return $absences;
} // mdjm_get_all_absences

/**
 * Retrieve all absences for an employee.
 *
 * @since   1.5.7
 * @param   int $employee_id    WP User ID
 * @return  array   Array of employee absence objects
 */
function mdjm_get_employee_absences( $employee_id ) {
	$employee_id = absint( $employee_id );

	if ( empty( $employee_id ) ) {
		return false;
	}

	$absences = MDJM()->availability_db->get_entries(
		array(
			'number'      => -1,
			'employee_id' => $employee_id,
		)
	);

	return $absences;
} // mdjm_get_employee_absences

/**
 * Perform the availability lookup.
 *
 * @since   1.3
 * @param   str       $date       The requested date
 * @param   int|array $employees  The employees to check
 * @param   str|array $roles      The employee roles to check
 * @return  array|bool  Array of available employees or roles, or false if not available
 */
function mdjm_do_availability_check( $date, $employees = '', $roles = '', $status = '' ) {

	$check = new MDJM_Availability_Checker( $date, '', $employees, $roles, $status );

	$check->availability_check();

	return $check->result;

} // mdjm_do_availability_check

/**
 * Retrieve employee and event activity for a given date range.
 *
 * @since   1.5.6
 * @param   string $start  Start date for which to retrieve activity
 * @param   string $end    End date for which to retrieve activity
 * @return  array   Array of data for the calendar
 */
function mdjm_get_calendar_entries( $start, $end ) {
	$activity     = array();
	$roles        =
	$availability = new MDJM_Availability_Checker( $start, $end, '', mdjm_get_roles() );

	$availability->get_calendar_entries();

	$activity = apply_filters( 'mdjm_employee_availability_activity', $availability->results, $start, $end );

	return $activity;
} // mdjm_get_calendar_entries

/**
 * Determine if an employee is absent on the given date.
 *
 * @since   1.3
 * @param   str $date       The date
 * @param   int $employee   The employee ID
 * @return  bool        True if the employee is working, otherwise false.
 */
function mdjm_employee_is_absent( $date, $employee_id = '' ) {

	if ( empty( $employee_id ) && is_user_logged_in() ) {
		$employee_id = get_current_user_id();
	}

	$availability = new MDJM_Availability_Checker( $date, $employee_id );

	return $availability->is_employee_absent();
} // mdjm_employee_is_absent

/**
 * Determine if an employee is working on the given date.
 *
 * @since   1.3
 * @param   str     $date       The date
 * @param   int     $employee   The employee ID
 * @param   str|arr $status     The employee ID
 * @return  bool        True if the employee is working, otherwise false.
 */
function mdjm_employee_is_working( $date, $employee_id = '' ) {

	if ( empty( $employee_id ) && is_user_logged_in() ) {
		$employee_id = get_current_user_id();
	}

	$availability = new MDJM_Availability_Checker( $date, $employee_id );

	return $availability->is_employee_working();
} // mdjm_employee_is_working

/**
 * Retrieve the description text for the calendar popup
 *
 * @since   1.5.6
 * @return  string
 */
function mdjm_get_calendar_event_description_text() {
	$newline = "\n";

	$default  = sprintf( __( 'Status: %s', 'mobile-dj-manager' ), '{event_status}' ) . $newline;
	$default .= sprintf( __( '<br/> Date: %s', 'mobile-dj-manager' ), '{event_date}' ) . $newline;
	$default .= sprintf( __( '<br/> Start: %s', 'mobile-dj-manager' ), '{start_time}' ) . $newline;
	$default .= sprintf( __( '<br/> Finish: %s', 'mobile-dj-manager' ), '{end_time}' ) . $newline;
	$default .= sprintf( __( '<br/> Setup: %s', 'mobile-dj-manager' ), '{dj_setup_time}' ) . $newline;
	$default .= sprintf( __( '<br/> Cost: %s', 'mobile-dj-manager' ), '{total_cost}' ) . $newline;
	$default .= sprintf( __( '<br/> Employees: %s', 'mobile-dj-manager' ), '{event_employees}' );

	// $text = mdjm_get_event_tip_content();
	$text = ! empty( $text ) ? $text : $default;
	// $text = utf8_encode( str_replace( PHP_EOL, '<br>', $text ) );
	$text = utf8_encode( str_replace( '<br>', PHP_EOL, $text ) );

	return $text;
} // mdjm_get_calendar_event_description_text
