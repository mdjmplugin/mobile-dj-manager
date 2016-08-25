<?php

/**
 * Earnings by Event Type Reports Table Class
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

// Load WP_List_Table if not loaded
if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

/**
 * MDJM_Types_Reports_Table Class
 *
 * Renders the Event Types Reports table
 *
 * @since	1.4
 */
class MDJM_Types_Reports_Table extends WP_List_Table {

	/**
	 * Get things started
	 *
	 * @since	1.4
	 * @see WP_List_Table::__construct()
	 */
	public function __construct() {
		global $status, $page;

		// Set parent defaults
		parent::__construct( array(
			'singular'  => mdjm_get_label_singular(),   // Singular name of the listed records
			'plural'    => mdjm_get_label_plural(),    	// Plural name of the listed records
			'ajax'      => false             			// Does this table support ajax?
		) );
	} // __construct

	/**
	 * Gets the name of the primary column.
	 *
	 * @since	1.4
	 * @access	protected
	 *
	 * @return	str		Name of the primary column.
	 */
	protected function get_primary_column_name() {
		return 'type';
	} // get_primary_column_name

	/**
	 * This function renders most of the columns in the list table.
	 *
	 * @access	public
	 * @since	1.4
	 *
	 * @param	arr		$item			Contains all the data of the events
	 * @param	str		$column_name	The name of the column
	 *
	 * @return	str		Column Name
	 */
	public function column_default( $item, $column_name ) {
		return $item[ $column_name ];
	} // column_default

	/**
	 * Retrieve the table columns
	 *
	 * @access	public
	 * @since	1.4
	 * @return	arr		$columns	Array of all the list table columns
	 */
	public function get_columns() {
		$columns = array(
			'type'           => __( 'Type', 'mobile-dj-manager' ),
			'total_value'    => __( 'Total Value', 'mobile-dj-manager' ),
			'total_earnings' => __( 'Total Earnings', 'mobile-dj-manager' ),
			'avg_events'     => sprintf( __( 'Monthly %s Avg', 'mobile-dj-manager' ), mdjm_get_label_plural() ),
			'avg_earnings'   => __( 'Monthly Earnings Avg', 'mobile-dj-manager' ),
		);

		return $columns;
	} // get_columns

	/**
	 * Retrieve the current page number
	 *
	 * @access	public
	 * @since	1.4
	 * @return	int		Current page number
	 */
	public function get_paged() {
		return isset( $_GET[ 'paged' ] ) ? absint( $_GET[ 'paged' ] ) : 1;
	} // get_paged

	/**
	 * Outputs the reporting views
	 *
	 * @access	public
	 * @since	1.4
	 * @return	void
	 */
	public function bulk_actions( $which = '' ) {
		if ( 'bottom' === $which ) {
			return;
		}

		// These aren't really bulk actions but this outputs the markup in the right place
		mdjm_report_views();
		mdjm_reports_graph_controls();
	} // bulk_actions

	/**
	 * Build all the reports data
	 *
	 * @access	public
	 * @since	1.4
	 * @return	arr		$reports_data	All the data for customer reports
	 */
	public function reports_data() {
		/*
		 * Date filtering
		 */
		$dates = mdjm_get_report_dates();

		if ( ! empty( $dates[ 'year' ] ) ) {
			$date = new DateTime();
			$date->setDate( $dates[ 'year' ], $dates[ 'm_start' ], $dates[ 'day' ] );
			$start_date = $date->format( 'Y-m-d' );

			$date->setDate( $dates[ 'year_end' ], $dates[ 'm_end' ], $dates[ 'day_end' ] );
			$end_date          = $date->format( 'Y-m-d' );
			$cached_report_key = 'mdjm_earnings_by_type_data' . $start_date . '_' . $end_date;
		} else {
			$start_date        = false;
			$end_date          = false;
			$cached_report_key = 'mdjm_earnings_by_type_data';
		}

		$cached_reports = get_transient( $cached_report_key );

		if ( false !== $cached_reports ) {
			$reports_data = $cached_reports;
		} else {

			$reports_data = array();
			$term_args    = array(
				'parent'       => 0,
				'hierarchical' => 0,
			);

			$categories = get_terms( 'event-types', $term_args );

			foreach ( $categories as $category_id => $category ) {

				$category_slugs = array( $category->slug );

				$event_args = array(
					'post_type'      => 'mdjm-event',
					'post_status'    => 'any',
					'posts_per_page' => -1,
					'fields'         => 'ids',
					'tax_query'      => array(
						array(
							'taxonomy' => 'event-types',
							'field'    => 'slug',
							'terms'    => $category_slugs
						)
					)
				);

				$events = get_posts( $event_args );

				$events        = 0;
				$earnings      = 0.00;
				$avg_events    = 0;
				$avg_earnings  = 0.00;

				$stats = new MDJM_Stats();

				foreach ( $events as $event ) {
					$current_average_event    = $current_events   = $stats->get_events( $event, $start_date, $end_date );
					$current_average_earnings = $current_earnings = $stats->get_earnings( $event, $start_date, $end_date );

					$release_date = get_post_field( 'post_date', $event );
					$diff         = abs( current_time( 'timestamp' ) - strtotime( $release_date ) );
					$months       = floor( $diff / ( 30 * 60 * 60 * 24 ) ); // Number of months since publication

					if ( $months > 0 ) {
						$current_average_events   = ( $current_events / $months );
						$current_average_earnings = ( $current_earnings / $months );
					}

					$events       += $current_events;
					$earnings     += $current_earnings;
					$avg_events   += $current_average_events;
					$avg_earnings += $current_average_earnings;
				}

				$avg_events   = round( $avg_events / count( $events ) );
				$avg_earnings = round( $avg_earnings / count( $avg_events ), mdjm_currency_decimal_filter() );

				$reports_data[] = array(
					'ID'                 => $category->term_id,
					'type'               => $category->name,
					'total_value'        => mdjm_currency_filter( mdjm_format_amount( $events, false ) ),
					'total_value_raw'    => $events,
					'total_earnings'     => mdjm_currency_filter( mdjm_format_amount( $earnings ) ),
					'total_earnings_raw' => $earnings,
					'avg_events'         => mdjm_format_amount( $avg_events, false ),
					'avg_earnings'       => mdjm_currency_filter( mdjm_format_amount( $avg_earnings ) ),
					'is_child'           => false,
				);
			}
		}

		return $reports_data;
	} // reports_data

	/**
	 * Output the Event Types Mix Pie Chart
	 *
	 * @since	1.4
	 * @return	str		The HTML for the outputted graph
	 */
	public function output_types_graph() {
		if ( empty( $this->items ) ) {
			return;
		}

		$data         = array();
		$total_events = 0;

		foreach ( $this->items as $item ) {
			$total_sales += $item['total_events_raw'];

			if ( ! empty( $item[ 'is_child' ] ) || empty( $item[ 'total_events_raw' ] ) ) {
				continue;
			}

			$data[ $item[ 'type' ] ] = $item[ 'total_events_raw' ];
		}


		if ( empty( $total_events ) ) {
			echo '<p><em>' . sprintf( __( 'No %s for dates provided.', 'mobile-dj-manager' ), mdjm_get_label_singular( true ) ) . '</em></p>';
		}

		// Sort High to Low, prior to filter so people can reorder if they please
		arsort( $data );
		$data = apply_filters( 'mdjm_types_events_graph_data', $data );

		$options = apply_filters( 'mdjm_types_events_graph_options', array(
			'legend_formatter' => 'mdjmLegendFormatterEvents',
		), $data );

		$pie_graph = new MDJM_Pie_Graph( $data, $options );
		$pie_graph->display();
	} // output_types_graph

	/**
	 * Output the Event Type Earnings Mix Pie Chart
	 *
	 * @since	1.4
	 * @return	str		The HTML for the outputted graph
	 */
	public function output_earnings_graph() {
		if ( empty( $this->items ) ) {
			return;
		}

		$data           = array();
		$total_earnings = 0;

		foreach ( $this->items as $item ) {
			$total_earnings += $item['total_earnings_raw'];

			if ( ! empty( $item[ 'is_child' ] ) || empty( $item[ 'total_earnings_raw' ] ) ) {
				continue;
			}

			$data[ $item[ 'type' ] ] = $item[ 'total_earnings_raw' ];

		}

		if ( empty( $total_earnings ) ) {
			echo '<p><em>' . __( 'No earnings for dates provided.', 'mobile-dj-manager' ) . '</em></p>';
		}

		// Sort High to Low, prior to filter so people can reorder if they please
		arsort( $data );
		$data = apply_filters( 'mdjm_types_earnings_graph_data', $data );

		$options = apply_filters( 'mdjm_types_earnings_graph_options', array(
			'legend_formatter' => 'mdjmLegendFormatterEarnings',
		), $data );

		$pie_graph = new MDJM_Pie_Graph( $data, $options );
		$pie_graph->display();
	} // output_earnings_graph

	/**
	 * Setup the final data for the table
	 *
	 * @access	public
	 * @since	1.4
	 * @uses	MDJM_Types_Reports_Table::get_columns()
	 * @uses	MDJM_Types_Reports_Table::get_sortable_columns()
	 * @uses	MDJM_Types_Reports_Table::reports_data()
	 * @return	void
	 */
	public function prepare_items() {
		$columns               = $this->get_columns();
		$hidden                = array(); // No hidden columns
		$sortable              = $this->get_sortable_columns();
		$this->_column_headers = array( $columns, $hidden, $sortable );
		$this->items           = $this->reports_data();
	} // prepare_items
} // MDJM_Types_Reports_Table
