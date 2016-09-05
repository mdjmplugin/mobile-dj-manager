<?php

/**
 * Conversions by Enquiry Source Reports Table Class
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
 * MDJM_Conversions_Reports_Table Class
 *
 * Renders the Conversions Reports table
 *
 * @since	1.4
 */
class MDJM_Conversions_Reports_Table extends WP_List_Table {

	private $label_single;
	private $label_plural;
	private $total_enquiries    = 0;
	private $total_conversions  = 0;
	private $total_value        = 0;

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
			'plural'    => mdjm_get_label_plural(),    // Plural name of the listed records
			'ajax'      => false             			// Does this table support ajax?
		) );
		$this->label_single = mdjm_get_label_singular();
		$this->label_plural = mdjm_get_label_plural();

		add_action( 'mdjm_reports_conversions_graph_additional_stats', array( $this, 'graph_totals' ) );
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
		return 'source';
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
			'source'            => __( 'Source', 'mobile-dj-manager' ),
			'total_events'      => __( 'Total Enquiries', 'mobile-dj-manager' ),
			'total_conversions' => __( 'Successful Conversions', 'mobile-dj-manager' ),
			'conversion_ratio'  => __( 'Conversion %', 'mobile-dj-manager' ),
			'total_value'       => __( 'Total Value', 'mobile-dj-manager' )
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

			$categories = get_terms( 'enquiry-source', $term_args );

			foreach ( $categories as $category_id => $category )	{

				$conversions     = 0;
				$ratio           = 0;
				$event_count     = 0;

				$category_slugs = array( $category->slug );

				$all_event_args = array(
					'post_status'    => 'any',
					'fields'         => 'ids',
					'tax_query'      => array(
						array(
							'taxonomy' => 'enquiry-source',
							'field'    => 'slug',
							'terms'    => $category_slugs,
						)
					),
					'date_query'             => array(
						array( 
							'after'        => date( 'Y-m-d', $stats->start_date ),
							'before'       => date( 'Y-m-d', $stats->end_date ),
							'inclusive'    => true
						)
					)
				);

				$value        = 0.00;

				$events     = mdjm_get_events( $all_event_args );
				$statuses   = mdjm_active_event_statuses();
				$statuses[] = 'mdjm-completed';
				$statuses[] = 'mdjm-cancelled';

				if ( $events )	{
					foreach ( $events as $event ) {
						$event_count++;

						$mdjm_event = new MDJM_Event( $event );

						if ( in_array( $mdjm_event->post_status, $statuses ) )	{
							$current_value = $mdjm_event->get_total_profit();
							$value        += $current_value;
						}
					}
				} else	{
					continue;
				}

				$conversions += $stats->get_conversions( $events, 'enquiry-source', $category_slugs, $stats->start_date, $stats->end_date );

				$ratio = round( (float) ( $conversions / $event_count ) * 100 );

				$reports_data[] = array(
					'ID'                    => $category->term_id,
					'source'                => $category->name,
					'total_events'          => $event_count,
					'total_conversions'     => $conversions,
					'conversion_ratio'      => $ratio . '%',
					'total_value'           => mdjm_currency_filter( mdjm_format_amount( $value ) ),
					'total_value_raw'       => $value,
					'is_child'              => false,
				);

				$this->total_enquiries   += $event_count;
				$this->total_value       += $value;
				$this->total_conversions += $conversions;

			}
		}

		return $reports_data;
	} // reports_data

	/**
	 * Output the Sources Events Mix Pie Chart
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
			$total_events += $item[ 'total_events' ];

			$data[ $item[ 'source' ] ] = $item[ 'total_events' ];
		}


		if ( empty( $total_events ) ) {
			echo '<p><em>' . sprintf( __( 'No %s for dates provided.', 'mobile-dj-manager' ), strtolower( $this->label_plural ) ) . '</em></p>';
		}

		// Sort High to Low, prior to filter so people can reorder if they please
		arsort( $data );
		$data = apply_filters( 'mdjm_sources_graph_data', $data );

		$options = apply_filters( 'mdjm_sources_graph_options', array(
			'legend_formatter' => 'mdjmLegendFormatterSources',
		), $data );

		$pie_graph = new MDJM_Pie_Graph( $data, $options );
		$pie_graph->display();
	} // output_source_graph

	/**
	 * Output the Sources Earnings Mix Pie Chart
	 *
	 * @since	1.4
	 * @return	str		The HTML for the outputted graph
	 */
	public function output_earnings_graph() {
		if ( empty( $this->items ) ) {
			return;
		}

		$data           = array();
		$total_value = 0;

		foreach ( $this->items as $item ) {
			$total_value += $item[ 'total_value_raw' ];

			$data[ $item[ 'source' ] ] = $item[ 'total_value_raw' ];

		}

		if ( empty( $total_value ) ) {
			echo '<p><em>' . __( 'No earnings for dates provided.', 'mobile-dj-manager' ) . '</em></p>';
		}

		// Sort High to Low, prior to filter so people can reorder if they please
		arsort( $data );
		$data = apply_filters( 'mdjm_sources_earnings_graph_data', $data );

		$options = apply_filters( 'mdjm_sources_earnings_graph_options', array(
			'legend_formatter' => 'mdjmLegendFormatterEarnings',
		), $data );

		$pie_graph = new MDJM_Pie_Graph( $data, $options );
		$pie_graph->display();
	} // output_earnings_graph

	/**
	 * Display graph totals.
	 *
	 * @since	1.4
	 */
	public function graph_totals()	{
		if ( empty( $this->total_enquiries ) )	{
			return;
		}

		?>
        <p class="mdjm_graph_totals">
            <strong>
                <?php
                    _e( 'Total enquiries for period shown: ', 'mobile-dj-manager' );
                    echo $this->total_enquiries;
                ?>
            </strong>
        </p>
        <p class="mdjm_graph_totals">
            <strong>
                <?php
                    _e( 'Total Conversions for period shown: ', 'mobile-dj-manager' );
                    echo $this->total_conversions;
                ?>
            </strong>
        </p>
        <p class="mdjm_graph_totals">
            <strong>
                <?php
                    _e( 'Conversion ratio for period shown: ', 'mobile-dj-manager' );
                    echo round( (float) ( $this->total_conversions / $this->total_enquiries ) * 100 ) . '%';
                ?>
            </strong>
        </p>
        <p class="mdjm_graph_totals">
            <strong>
                <?php
                    _e( 'Total Value for period shown: ', 'mobile-dj-manager' );
                    echo mdjm_currency_filter( mdjm_format_amount( $this->total_value ) );
                ?>
            </strong>
        </p>
        <?php
	} // graph_totals

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
} // MDJM_Conversions_Reports_Table
