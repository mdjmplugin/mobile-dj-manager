<?php
/**
 * Batch Employees Export Class
 *
 * This class handles client export
 *
 * @package     MDJM
 * @subpackage  Admin/Reports
 * @copyright   Copyright (c) 2016, Mike Howard
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.4
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) )
	exit;

/**
 * MDJM_Batch_Export_Clients Class
 *
 * @since	1.4
 */
class MDJM_Batch_Export_Employees extends MDJM_Batch_Export {

	/**
	 * Our export type. Used for export-type specific filters/actions
	 *
	 * @var		str
	 * @since	1.4
	 */
	public $export_type = 'employees';

	/**
	 * Set the CSV columns
	 *
	 * @access	public
	 * @since	1.4
	 * @return	arr		$cols	All the columns
	 */
	public function csv_cols() {

		$cols = array(
			'id'        => __( 'ID',   'mobile-dj-manager' ),
			'name'      => __( 'Name',   'mobile-dj-manager' ),
			'email'     => __( 'Email', 'mobile-dj-manager' ),
			'events'    => sprintf( __( 'Number of %s', 'mobile-dj-manager' ), mdjm_get_label_plural() ),
			'roles'     => __( 'Roles', 'mobile-dj-manager' ),
			'wages'     => __( 'Total Wages', 'mobile-dj-manager' ),
			'paid'      => __( 'Paid Wages', 'mobile-dj-manager' ),
			'owed'      => __( 'Owed Wages', 'mobile-dj-manager' )
		);

		return $cols;
	} // csv_cols

	/**
	 * Filter the get_client args.
	 *
	 * @since	1.4
	 * @param	arr		$args	Args passed to get_users query.
	 * @return	arr		$args	Args passed to get_users query.
	 */
	public function filter_args( $args )	{
		$offset = 30 * ( $this->step - 1 );
		$args['number'] = 30;
		$args['offset'] = $offset;
		$args['paged']  = $this->step;

		return $args;
	} // filter_args

	/**
	 * Get the Export Data
	 *
	 * @access	public
	 * @since	1.4
	 * @return	arr		$data	The data for the CSV file
	 */
	public function get_data() {

		global $wp_roles;

		$data = array();
		$mdjm_roles = mdjm_get_roles();
		$roles      = array();
		$offset     = 30 * ( $this->step - 1 );
	
		foreach ( $mdjm_roles as $role_id => $role_name )	{
			$roles[] = $role_id;
		}

		$args = array(
			'number'   => 30,
			'offset'   => $offset,
			'paged'    => $this->step,
			'role__in' => $roles
		);

		$employee_query = new WP_User_Query( $args );
		$employees      = $employee_query->get_results();

		$i = 0;

		if ( $employees )	{
			foreach ( $employees as $employee ) {
	
				$events = mdjm_get_employee_events( $employee->ID );
				$wages  = 0;
				$paid   = 0;

				$role_names = array();
				foreach( $employee->roles as $role )	{
					$role_names[] = translate_user_role( $wp_roles->roles[ $role ]['name'] );
				}
	
				$data[$i]['id']     = $employee->ID;
				$data[$i]['name']   = $employee->display_name;
				$data[$i]['email']  = $employee->user_email;
				$data[$i]['events'] = $events ? count( $events ) : 0;
				$data[$i]['roles']  = implode( ', ', $role_names );
	
				if ( $events )	{
					foreach ( $events as $event )	{
						$event_wage = mdjm_get_employees_event_wage( $event->ID, $employee->ID );
						$wages      += $event_wage;
	
						if ( ! empty( $event_wage ) && 'paid' == mdjm_get_employees_event_payment_status( $event->ID, $employee->ID ) )	{
							$paid += $wages;
						}
					}
				}
	
				$data[$i]['wages'] = mdjm_format_amount( $wages );
				$data[$i]['paid']  = mdjm_format_amount( $paid );
				$data[$i]['owed']  = mdjm_format_amount( $wages - $paid );
	
				$i++;
			}
		}
		$data = apply_filters( 'mdjm_export_get_data', $data );
		$data = apply_filters( 'mdjm_export_get_data_' . $this->export_type, $data );

		return $data;
	} // get_data

	/**
	 * Return the calculated completion percentage
	 *
	 * @since	1.4
	 * @return	int
	 */
	public function get_percentage_complete() {

		$percentage = 0;

		$total = mdjm_employee_count();//count( mdjm_get_employees() );
error_log( $total );
		if ( $total > 0 ) {
			$percentage = ( ( 30 * $this->step ) / $total ) * 100;
		}

		if ( $percentage > 100 ) {
			$percentage = 100;
		}

		return $percentage;
	} // get_percentage_complete

	/**
	 * Set the properties specific to the Clients export
	 *
	 * @since	1.4
	 * @param	arr		$request	The Form Data passed into the batch processing
	 */
	public function set_properties( $request ) {
		$this->start    = isset( $request['start'] ) ? sanitize_text_field( $request['start'] ) : '';
		$this->end      = isset( $request['end']   ) ? sanitize_text_field( $request['end']   ) : '';
	} // set_properties

} // MDJM_Batch_Export_Employees
