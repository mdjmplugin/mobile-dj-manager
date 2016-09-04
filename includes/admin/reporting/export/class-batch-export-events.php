<?php
/**
 * Batch Events Export Class
 *
 * This class handles events exports
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
 * MDJM_Batch_Export_Events Class
 *
 * @since	1.4
 */
class MDJM_Batch_Export_Events extends MDJM_Batch_Export {

	/**
	 * Our export type. Used for export-type specific filters/actions
	 *
	 * @var		str
	 * @since	1.4
	 */
	public $export_type = 'events';

	/**
	 * Set the CSV columns
	 *
	 * @access	public
	 * @since	1.4
	 * @return	arr		$cols	All the columns
	 */
	public function csv_cols() {

		$cols = array(
			'id'                  => __( 'ID', 'mobile-dj-manager' ),
			'event_id'            => sprintf( __( '%s ID',   'mobile-dj-manager' ), $this->event_label_single ),
			'date'                => __( 'Date', 'mobile-dj-manager' ),
			'status'              => __( 'Status', 'mobile-dj-manager' ),
			'client'              => __( 'Client', 'mobile-dj-manager' ),
			'primary_employee'    => __( 'Primary Employee', 'mobile-dj-manager' ),
			'employees'           => __( 'Employees', 'mobile-dj-manager' ),
			'package'             => __( 'Package', 'mobile-dj-manager' ),
			'addons'              => __( 'Addons', 'mobile-dj-manager' ),
			'cost'                => __( 'Price', 'mobile-dj-manager' ),
			'deposit'             => __( 'Deposit', 'mobile-dj-manager' ),
			'deposit_status'      => __( 'Deposit Status', 'mobile-dj-manager' ),
			'balance'             => __( 'Balance', 'mobile-dj-manager' ),
			'balance_status'      => __( 'Balance Status', 'mobile-dj-manager' ),
			'start_time'          => __( 'Start Time', 'mobile-dj-manager' ),
			'end_time'            => __( 'End Time', 'mobile-dj-manager' ),
			'end_date'            => __( 'End Date', 'mobile-dj-manager' ),
			'setup_date'          => __( 'Setup Date', 'mobile-dj-manager' ),
			'setup_time'          => __( 'Setup Time', 'mobile-dj-manager' ),
			'duration'            => __( 'Duration', 'mobile-dj-manager' ),
			'contract'            => __( 'Contract ID', 'mobile-dj-manager' ),
			'contract_status'     => __( 'Contract Status', 'mobile-dj-manager' ),
			'playlist_enabled'    => __( 'Playlist Enabled', 'mobile-dj-manager' ),
			'playlist_status'     => __( 'Playlist Status', 'mobile-dj-manager' ),
			'source'              => __( 'Enquiry Source', 'mobile-dj-manager' ),
			'converted'           => __( 'Converted', 'mobile-dj-manager' ),
			'venue'               => __( 'Venue', 'mobile-dj-manager' ),
			'address'             => __( 'Venue Address', 'mobile-dj-manager' )
		);

		return $cols;
	} // csv_cols

	/**
	 * Get the Export Data
	 *
	 * @access	public
	 * @since	1.4
	 * @return	arr		$data	The data for the CSV file
	 */
	public function get_data() {

		$data = array();

		// Export all events
		$offset = 30 * ( $this->step - 1 );

		$args = array(
			'post_type'      => 'mdjm-event',
			'posts_per_page' => 30,
			'offset'         => $offset,
			'paged'          => $this->step,
			'post_status'    => $this->status,
			'order'          => 'ASC',
			'orderby'        => 'ID'
		);

		if ( ! empty( $this->start ) || ! empty( $this->end ) )	{

			$args['meta_query'] = array(
				array(
					'key'     => '_mdjm_event_date',
					'value'   => array( date( 'Y-m-d', strtotime( $this->start ) ), date( 'Y-m-d', strtotime( $this->end ) ) ),
					'compare' => 'BETWEEN',
					'type'    => 'DATE'
				)
			);

		}

		$events = get_posts( $args );

		if ( $events )	{

			$i       = 0;

			foreach ( $events as $event ) {
	
				$event_data = mdjm_get_event_data( $event->ID );
				$employees  = array();
				$package    = '';
				$addons     = array();

				if ( ! empty( $event_data['client'] ) )	{
					$client = '(' . $event_data['client'] . ') ' . mdjm_get_client_display_name( $event_data['client'] );
				}
				if ( ! empty( $event_data['employees']['primary_employee'] ) )	{
					$primary_employee = '(' . $event_data['employees']['primary_employee'] . ') ' . mdjm_get_employee_display_name( $event_data['employees']['primary_employee'] );
				}
				if ( ! empty( $event_data['employees']['employees'] ) )	{
					foreach( $event_data['employees']['employees'] as $employee_id => $employee_data )	{
						$employees[] = '(' . $employee_id . ') ' . mdjm_get_employee_display_name( $employee_id );
					}
				}
				if ( ! empty( $event_data['equipment']['package'] ) )	{
					$package = $event_data['equipment']['package'];
				}
				if ( ! empty( $event_data['equipment']['addons'] ) )	{
					foreach( $event_data['equipment']['addons'] as $addon_id )	{
						$addons[] = mdjm_get_addon_name( $addon_id );
					}
				}

				$data[ $i ] = array(
					'id'                  => $event->ID,
					'event_id'            => mdjm_get_event_contract_id( $event->ID ),
					'date'                => mdjm_format_short_date( $event_data['date'] ),
					'status'              => $event_data['status'],
					'client'              => $client,
					'primary_employee'    => '(' . $event_data['employees']['primary_employee'] . ') ' . mdjm_get_client_display_name( $event_data['employees']['primary_employee'] ),
					'employees'           => implode( ',', $employees ),
					'package'             => $package,
					'addons'              => implode( ', ', $addons ),
					'cost'                => mdjm_format_amount( $event_data['cost']['cost'] ),
					'deposit'             => mdjm_format_amount( $event_data['cost']['deposit'] ),
					'deposit_status'      => $event_data['cost']['deposit_status'],
					'balance'             => mdjm_format_amount( $event_data['cost']['balance'] ),
					'balance_status'      => $event_data['cost']['balance_status'],
					'start_time'          => mdjm_format_time( $event_data['start_time'] ),
					'end_time'            => mdjm_format_time( $event_data['end_time'] ),
					'end_date'            => mdjm_format_short_date( $event_data['end_date'] ),
					'setup_date'          => mdjm_format_short_date( $event_data['setup_date'] ),
					'setup_time'          => mdjm_format_time( $event_data['setup_time'] ),
					'duration'            => $event_data['duration'],
					'contract'            => $event_data['contract'],
					'contract_status'     => $event_data['contract_status'],
					'playlist_enabled'    => $event_data['playlist']['playlist_enabled'],
					'playlist_status'     => $event_data['playlist']['playlist_status'],
					'source'              => $event_data['source'],
					'converted'           => $event_data['contract_status'],
					'venue'               => $event_data['venue']['name'],
					'address'             => ! empty( $event_data['venue']['address'] ) ? implode( ', ', $event_data['venue']['address'] ) : ''
				);

				$i++;

			}

			$data = apply_filters( 'mdjm_export_get_data', $data );
			$data = apply_filters( 'mdjm_export_get_data_' . $this->export_type, $data );
	
			return $data;

		}

		return false;
	} // get_data

	/**
	 * Set the count args.
	 *
	 * @since	1.4
	 * @param	arr		$args	The args for the count query
	 * @return	arr		$args	The args for the count query
	 */
	 function filter_count_args( $args )	{

		 if ( ! empty( $this->start ) || ! empty( $this->end ) )	{

			$args['meta_query'] = array(
				array(
					'key'     => '_mdjm_event_date',
					'value'   => array( date( 'Y-m-d', strtotime( $this->start ) ), date( 'Y-m-d', strtotime( $this->end ) ) ),
					'compare' => 'BETWEEN',
					'type'    => 'DATE'
				)
			);

		}

		if ( ! empty( $this->status ) )	{
			$args['post_status'] = $this->status;
		}

		return $args;

	 } // filter_count_args

	/**
	 * Return the calculated completion percentage
	 *
	 * @since	1.4
	 * @return	int
	 */
	public function get_percentage_complete() {

		add_filter( 'mdjm_event_count_args', array( $this, 'filter_count_args' ) );
		$total = mdjm_event_count();
		remove_filter( 'mdjm_event_count_args', array( $this, 'filter_count_args' ) );

		$percentage = 100;

		if ( $total > 0 ) {
			$percentage = ( ( 30 * $this->step ) / $total ) * 100;
		}

		if ( $percentage > 100 ) {
			$percentage = 100;
		}

		return $percentage;
	} // get_percentage_complete

	/**
	 * Set the properties specific to the Events export
	 *
	 * @since	1.4
	 * @param	arr		$request	The Form Data passed into the batch processing
	 */
	public function set_properties( $request ) {
		$this->start  = isset( $request['event_start'] )    ? sanitize_text_field( $request['event_start'] ) : '';
		$this->end    = isset( $request['event_end']  )     ? sanitize_text_field( $request['event_end']  )  : '';
		$this->status = isset( $request['event_status'] )   ? $request['event_status']                       : 'any';
		$this->type   = isset( $request['event_type'] )     ? get_term( (int) $request['event_type'], 'event-type' )                   : false;
	} // set_properties

} // MDJM_Batch_Export_Events
