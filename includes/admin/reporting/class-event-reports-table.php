<?php
/**
 * Event Reports Table Class
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
 * MDJM_Event_Reports_Table Class
 *
 * Renders the Event Reports table
 *
 * @since	1.4
 */
class MDJM_Event_Reports_Table extends WP_List_Table {

	/**
	 * @var		int		Number of items per page
	 * @since	1.4
	 */
	public $per_page = 30;

	/**
	 * @var		obj		Query results
	 * @since	1.4
	 */
	private $events;

	/**
	 * Get things started
	 *
	 * @since	1.4
	 * @see		WP_List_Table::__construct()
	 */
	public function __construct() {
		global $status, $page;

		// Set parent defaults
		parent::__construct( array(
			'singular' => mdjm_get_label_singular(),
			'plural'   => mdjm_get_label_plural(),
			'ajax'     => false,
		) );

		add_action( 'mdjm_report_view_actions', array( $this, 'category_filter' ) );

		$this->query();

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
	 * @param	arr		$item			Contains all the data of the events
	 * @param	str		$column_name	The name of the column
	 *
	 * @return	str		Column Name
	 */
	public function column_default( $item, $column_name ) {
		switch( $column_name ){
			case 'earnings' :
				return mdjm_currency_filter( mdjm_format_amount( $item[ $column_name ] ) );
			case 'details' :
				return '<a href="' . admin_url( 'edit.php?post_type=mdjm-event&page=mdjm-reports&view=event&event-id=' . $item['ID'] ) . '">' . __( 'View Detailed Report', 'mobile-dj-manager' ) . '</a>';
			default:
				return $item[ $column_name ];
		}
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
			'type'     => __( 'Type', 'mobile-dj-manager' ),
			'events'   => mdjm_get_label_plural(),
			'earnings' => __( 'Earnings', 'mobile-dj-manager' ),
			'details'  => __( 'Detailed Report', 'mobile-dj-manager' ),
		);

		return $columns;
	} // get_columns

	/**
	 * Retrieve the table's sortable columns
	 *
	 * @access public
	 * @since 1.4
	 * @return array Array of all the sortable columns
	 */
	public function get_sortable_columns() {
		return array(
			'type'      => array( 'type', true ),
			'events'    => array( 'events', false ),
			'earnings'  => array( 'earnings', false ),
		);
	} // get_sortable_columns

	/**
	 * Retrieve the current page number
	 *
	 * @access	public
	 * @since	1.4
	 * @return	int Current page number
	 */
	public function get_paged() {
		return isset( $_GET['paged'] ) ? absint( $_GET['paged'] ) : 1;
	} // get_paged


	/**
	 * Retrieve the category being viewed
	 *
	 * @access	public
	 * @since	1.4
	 * @return	int		Category ID
	 */
	public function get_category() {
		return isset( $_GET['category'] ) ? absint( $_GET['category'] ) : 0;
	} // get_category


	/**
	 * Retrieve the total number of downloads
	 *
	 * @access	public
	 * @since	1.4
	 * @return	int	$total	Total number of downloads
	 */
	public function get_total_events() {
		$total  = 0;
		$counts = wp_count_posts( 'mdjm-event', 'readable' );
		foreach( $counts as $status => $count ) {
			$total += $count;
		}
		return $total;
	} // get_total_events

	/**
	 * Outputs the reporting views
	 *
	 * @access	public
	 * @since	1.4
	 * @return	void
	 */
	public function bulk_actions( $which = '' ) {
		// These aren't really bulk actions but this outputs the markup in the right place
		mdjm_report_views();
	} // bulk_actions


	/**
	 * Attaches the category filter to the log views
	 *
	 * @access	public
	 * @since	1.4
	 * @return	void
	 */
	public function category_filter() {
		if( get_terms( 'event-types' ) ) {
			echo MDJM()->html->event_type_dropdown( 'selected', $this->get_category() );
		}
	} // category_filter

	/**
	 * Performs the events query
	 *
	 * @access	public
	 * @since	1.4
	 * @return	void
	 */
	public function query() {

		$orderby  = isset( $_GET['orderby'] ) ? $_GET['orderby'] : 'title';
		$order    = isset( $_GET['order'] ) ? $_GET['order'] : 'DESC';
		$category = $this->get_category();

		$args = array(
			'post_type'        => 'mdjm-event',
			'post_status'      => 'any',
			'order'            => $order,
			'fields'           => 'ids',
			'posts_per_page'   => $this->per_page,
			'paged'            => $this->get_paged(),
			'suppress_filters' => true,
		);

		if( ! empty( $category ) ) {
			$args['tax_query'] = array(
				array(
					'taxonomy' => 'event-types',
					'terms'    => $category,
				)
			);
		}

		switch ( $orderby ) :
			case 'type' :
				$args['orderby'] = 'type';
				break;

			case 'date' :
				$args['orderby']  = 'meta_value_num';
				$args['meta_key'] = '_mdjm_event_date';
				break;

			case 'earnings' :
				$args['orderby']  = 'meta_value_num';
				$args['meta_key'] = '_edd_download_earnings';
				break;
		endswitch;

		$args = apply_filters( 'mdjm_event_reports_prepare_items_args', $args, $this );

		$this->events = new WP_Query( $args );

	} // query

	/**
	 * Build all the reports data
	 *
	 * @access	public
	 * @since	1.4
	 * @return	array	$reports_data	All the data for customer reports
	 */
	public function reports_data() {
		$reports_data = array();

		$events = $this->events->posts;

		if ( $events ) {
			foreach ( $events as $event ) {
				$mdjm_event = new MDJM_Event( $event );
				$reports_data[] = array(
					'ID'       => $event,
					'type'     => get_the_title( $event ),
					'events'   => count( $event ),
					'earnings' => $mdjm_event->get_total_profit(),
					//'average_sales'    => edd_get_average_monthly_download_sales( $event ),
					//'average_earnings' => edd_get_average_monthly_download_earnings( $event ),
				);
			}
		}

		return $reports_data;
	} // reports_data

	/**
	 * Setup the final data for the table
	 *
	 * @access	public
	 * @since	1.4
	 * @uses	MDJM_Event_Reports_Table::get_columns()
	 * @uses	MDJM_Event_Reports_Table::get_sortable_columns()
	 * @uses	MDJM_Event_Reports_Table::reports_data()
	 * @uses	MDJM_Event_Reports_Table::get_pagenum()
	 * @uses	MDJM_Event_Reports_Table::get_total_events()
	 * @return	void
	 */
	public function prepare_items() {
		$columns = $this->get_columns();

		$hidden = array(); // No hidden columns

		$sortable = $this->get_sortable_columns();

		$this->_column_headers = array( $columns, $hidden, $sortable );

		$data = $this->reports_data();

		$total_items = $this->get_total_events();

		$this->items = $data;

		$this->set_pagination_args( array(
				'total_items' => $total_items,
				'per_page'    => $this->per_page,
				'total_pages' => ceil( $total_items / $this->per_page ),
			)
		);
	} // prepare_items
}
