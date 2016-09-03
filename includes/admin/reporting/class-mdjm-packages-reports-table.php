<?php

/**
 * Events by Package Reports Table Class
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
if ( !class_exists( 'WP_List_Table' ) ) {
	require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

/**
 * MDJM_Packages_Reports_Table Class
 *
 * Renders the Conversions Reports table
 *
 * @since	1.4
 */
class MDJM_Packages_Reports_Table extends WP_List_Table {

	private $label_single;
	private $label_plural;

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
			'singular'  => mdjm_get_label_singular(),    // Singular name of the listed records
			'plural'    => mdjm_get_label_plural(),     // Plural name of the listed records
			'ajax'      => false             			// Does this table support ajax?
		) );
		$this->label_single = mdjm_get_label_singular();
		$this->label_plural = mdjm_get_label_plural();
	}

	/**
	 * Gets the name of the primary column.
	 *
	 * @since	1.4
	 * @access	protected
	 *
	 * @return	str		Name of the primary column.
	 */
	protected function get_primary_column_name() {
		return 'package';
	}

	/**
	 * This function renders most of the columns in the list table.
	 *
	 * @access	public
	 * @since	1.4
	 *
	 * @param	arr		$item			Contains all the data of the downloads
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
			'package' => __( 'Package', 'mobile-dj-manager' ),
			'events'  => sprintf( __( 'Total %s', 'mobile-dj-manager' ), $this->label_plural ),
			'value'   => __( 'Total Value', 'mobile-dj-manager' )
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
	 * @access 	public
	 * @since	1.4
	 * @return	void
	 */
	public function extra_tablenav( $which = '' ) {
		if ( 'bottom' === $which ) {
			return;
		}

		mdjm_report_views();
		mdjm_reports_graph_controls();
	} // extra_tablenav

	/**
	 * Build all the reports data
	 *
	 * @access	public
	 * @since	1.4
	 * @return	arr		$reports_data	All the data for customer reports
	 */
	public function reports_data() {
		$stats = new MDJM_Stats();
		$dates = mdjm_get_report_dates();
		$stats->setup_dates( $dates['range'] );

		$cached_reports = false;
		if ( false !== $cached_reports ) {
			$reports_data = $cached_reports;
		} else {
			$reports_data = array();
			$term_args    = array(
				'parent'       => 0,
				'hierarchical' => 0,
			);

			$packages = mdjm_get_packages();

			if ( $packages )	{

				foreach ( $packages as $package )	{
	
					$event_count  = 0;
					$total_value  = 0;

					$event_args = array(
						'fields'     => 'ids',
						'meta_query' => array(
							'relation' => 'AND',
							array(
								'key'		=> '_mdjm_event_package',
								'value'		=> $package->ID,
								'type'		=> 'NUMERIC'
							),
							array(
								'key'		=> '_mdjm_event_date',
								'value'		=> array( date( 'Y-m-d', $stats->start_date ), date( 'Y-m-d', $stats->end_date ) ),
								'type'		=> 'date',
								'compare'	=> 'BETWEEN',
							)
						)
					);

					$events = mdjm_get_events( $event_args );
	
					if ( $events )	{
						foreach ( $events as $event ) {
							$event_count++;
							$event_date  = get_post_meta( $event, '_mdjm_event_date', true );
							$total_value += mdjm_get_package_price( $package->ID, $event_date );
						}
					} else	{
						continue;
					}
	
					$reports_data[] = array(
						'ID'        => $package->ID,
						'package'   => mdjm_get_package_name( $package->ID ),
						'events'    => $event_count,
						'value'     => mdjm_currency_filter( mdjm_format_amount( $total_value ) ),
						'value_raw' => $total_value
					);
	
				}
			}
		}

		return $reports_data;
	} // reports_data

	/**
	 * Output the Sources Packages Mix Pie Chart
	 *
	 * @since	1.4
	 * @return	str		The HTML for the outputted graph
	 */
	public function output_source_graph() {
		if ( empty( $this->items ) ) {
			return;
		}

		$data        = array();
		$total_events = 0;

		foreach ( $this->items as $item ) {
			$total_events += $item[ 'events' ];

			$data[ $item[ 'package' ] ] = $item[ 'events' ];
		}


		if ( empty( $total_events ) ) {
			echo '<p><em>' . __( 'No data for dates provided.', 'mobile-dj-manager' ) . '</em></p>';
		}

		// Sort High to Low, prior to filter so people can reorder if they please
		arsort( $data );
		$data = apply_filters( 'mdjm_packages_graph_data', $data );

		$options = apply_filters( 'mdjm_packages_graph_options', array(
			'legend_formatter' => 'mdjmLegendFormatterSources',
		), $data );

		$pie_graph = new MDJM_Pie_Graph( $data, $options );
		$pie_graph->display();
	} // output_source_graph

	/**
	 * Output the Package Earnings Mix Pie Chart
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
			$total_earnings += $item[ 'value_raw' ];

			$data[ $item[ 'package' ] ] = $item[ 'value_raw' ];

		}

		if ( empty( $total_earnings ) ) {
			echo '<p><em>' . __( 'No earnings for dates provided.', 'mobile-dj-manager' ) . '</em></p>';
		}

		// Sort High to Low, prior to filter so people can reorder if they please
		arsort( $data );
		$data = apply_filters( 'mdjm_packages_earnings_graph_data', $data );

		$options = apply_filters( 'mdjm_packages_earnings_graph_options', array(
			'legend_formatter' => 'mdjmLegendFormatterEarnings',
		), $data );

		$pie_graph = new MDJM_Pie_Graph( $data, $options );
		$pie_graph->display();
	} // output_earnings_graph

	/**
	 * The output when no records are found.
	 *
	 * @since	1.4
	 */
	public function no_items() {
		_e( 'No data to display for this period.', 'mobile-dj-manager' );
	} // no_items

	/**
	 * Setup the final data for the table
	 *
	 * @access	public
	 * @since 	1.4
	 * @uses	MDJM_Conversions_Reports_Table::get_columns()
	 * @uses	MDJM_Conversions_Reports_Table::get_sortable_columns()
	 * @uses	MDJM_Conversions_Reports_Table::reports_data()
	 * @return	void
	 */
	public function prepare_items() {
		$columns               = $this->get_columns();
		$hidden                = array(); // No hidden columns
		$sortable              = $this->get_sortable_columns();
		$this->_column_headers = array( $columns, $hidden, $sortable );
		$this->items           = $this->reports_data();
	} // prepare_items
} // MDJM_Packages_Reports_Table
