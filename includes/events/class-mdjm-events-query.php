<?php
/**
 * Events Query
 *
 * @package     MDJM
 * @subpackage  Classes/Stats
 * @copyright   Copyright (c) 2016, Mike Howard
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.4
*/

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) )
	exit;

/**
 * MDJM_Events_Query Class
 *
 * This class is for retrieving transaction data
 *
 * Transactions can be retrieved for date ranges and pre-defined periods
 *
 * @since	1.4
 */
class MDJM_Events_Query extends MDJM_Stats {

	/**
	 * The args to pass to the mdjm_get_events() query
	 *
	 * @var		array
	 * @access	public
	 * @since	1.4
	 */
	public $args = array();

	/**
	 * The events found based on the criteria set
	 *
	 * @var		array|false
	 * @access	public
	 * @since	1.4
	 */
	public $events = false;

	/**
	 * Default query arguments.
	 *
	 * Not all of these are valid arguments that can be passed to WP_Query. The ones that are not, are modified before
	 * the query is run to convert them to the proper syntax.
	 *
	 * @access	public
	 * @since	1.4
	 * @param	arr		$args	The array of arguments that can be passed in and used for setting up this payment query.
	 */
	public function __construct( $args = array() ) {
		$defaults = array(
			'output'           => 'events', // Use 'posts' to get standard post objects
			'post_type'        => array( 'mdjm-event' ),
			'start_date'       => false,
			'end_date'         => false,
			'number'           => 20,
			'page'             => null,
			'orderby'          => 'date',
			'order'            => 'DESC',
			'event_date_start' => null,
			'event_date_end'   => null,
			'employee'         => null,
			'client'           => null,
			'status'           => 'any',
			'source'           => null,
			'type'             => null,
			'meta_key'         => null,
			'year'             => null,
			'month'            => null,
			'day'              => null,
			's'                => null,
			'fields'           => null
		);

		$this->args = wp_parse_args( $args, $defaults );

		$this->init();
	} // __construct

	/**
	 * Set a query variable.
	 *
	 * @access	public
	 * @since	1.4
	 */
	public function __set( $query_var, $value ) {
		if ( in_array( $query_var, array( 'meta_query', 'tax_query' ) ) )
			$this->args[ $query_var ][] = $value;
		else
			$this->args[ $query_var ] = $value;
	} // __set

	/**
	 * Unset a query variable.
	 *
	 * @access	public
	 * @since	1.4
	 */
	public function __unset( $query_var ) {
		unset( $this->args[ $query_var ] );
	} // __unset

	/**
	 * Modify the query/query arguments before we retrieve transactions.
	 *
	 * @access	public
	 * @since	1.4
	 * @return	void
	 */
	public function init() {
		add_action( 'mdjm_pre_get_events',  array( $this, 'date_filter_pre'  ) );
		add_action( 'mdjm_post_get_events', array( $this, 'date_filter_post' ) );

		add_action( 'mdjm_pre_get_events',  array( $this, 'orderby'    ) );
		add_action( 'mdjm_pre_get_events',  array( $this, 'status'     ) );
		add_action( 'mdjm_pre_get_events',  array( $this, 'month'      ) );
		add_action( 'mdjm_pre_get_events',  array( $this, 'per_page'   ) );
		add_action( 'mdjm_pre_get_events',  array( $this, 'page'       ) );
		add_action( 'mdjm_pre_get_events',  array( $this, 'event_date' ) );
		add_action( 'mdjm_pre_get_events',  array( $this, 'employee'   ) );
		add_action( 'mdjm_pre_get_events',  array( $this, 'client'     ) );
		add_action( 'mdjm_pre_get_events',  array( $this, 'search'     ) );
		add_action( 'mdjm_pre_get_events',  array( $this, 'source'     ) );
		add_action( 'mdjm_pre_get_events',  array( $this, 'type'       ) );
	} // init

	/**
	 * Retrieve events.
	 *
	 * The query can be modified in two ways; either the action before the
	 * query is run, or the filter on the arguments (existing mainly for backwards
	 * compatibility).
	 *
	 * @access	public
	 * @since	1.4
	 * @return	obj
	 */
	public function get_events() {
		do_action( 'mdjm_pre_get_events', $this );

		$query = new WP_Query( $this->args );

		$custom_output = array(
			'events',
			'mdjm_events',
		);

		if ( ! in_array( $this->args['output'], $custom_output ) ) {
			return $query->posts;
		}

		if ( $query->have_posts() ) {
			while ( $query->have_posts() ) {
				$query->the_post();

				$event_id = get_post()->ID;
				$event    = new MDJM_Event( $event_id );

				$this->events[] = apply_filters( 'mdjm_event', $event, $event_id, $this );
			}

			wp_reset_postdata();
		}

		do_action( 'mdjm_post_get_events', $this );

		return $this->events;
	} // get_events

	/**
	 * If querying a specific date, add the proper filters.
	 *
	 * @access	public
	 * @since	1.4
	 * @return	void
	 */
	public function date_filter_pre() {
		if( ! ( $this->args['start_date'] || $this->args['end_date'] ) ) {
			return;
		}

		$this->setup_dates( $this->args['start_date'], $this->args['end_date'] );

		add_filter( 'posts_where', array( $this, 'events_where' ) );
	} // date_filter_pre

	/**
	 * If querying a specific date, remove filters after the query has been run
	 * to avoid affecting future queries.
	 *
	 * @access	public
	 * @since	1.4
	 * @return	void
	 */
	public function date_filter_post() {
		if ( ! ( $this->args['start_date'] || $this->args['end_date'] ) ) {
			return;
		}

		remove_filter( 'posts_where', array( $this, 'events_where' ) );
	} // date_filter_post

	/**
	 * Post Status
	 *
	 * @access	public
	 * @since	1.4
	 * @return	void
	 */
	public function status() {
		if ( ! isset ( $this->args['status'] ) ) {
			return;
		}

		$this->__set( 'post_status', $this->args['status'] );
		$this->__unset( 'status' );
	} // status

	/**
	 * Current Page
	 *
	 * @access	public
	 * @since	1.4
	 * @return	void
	 */
	public function page() {
		if ( ! isset ( $this->args['page'] ) ) {
			return;
		}

		$this->__set( 'paged', $this->args['page'] );
		$this->__unset( 'page' );
	} // page

	/**
	 * Posts Per Page
	 *
	 * @access	public
	 * @since	1.4
	 * @return	void
	 */
	public function per_page() {

		if( ! isset( $this->args['number'] ) ){
			return;
		}

		if ( $this->args['number'] == -1 ) {
			$this->__set( 'nopaging', true );
		}
		else{
			$this->__set( 'posts_per_page', $this->args['number'] );
		}

		$this->__unset( 'number' );
	} // per_page

	/**
	 * Current Month
	 *
	 * @access	public
	 * @since	1.4
	 * @return	void
	 */
	public function month() {
		if ( ! isset ( $this->args['month'] ) ) {
			return;
		}

		$this->__set( 'monthnum', $this->args['month'] );
		$this->__unset( 'month' );
	} // month

	/**
	 * Order by
	 *
	 * @access	public
	 * @since	1.4
	 * @return	void
	 */
	public function orderby() {
		switch ( $this->args['orderby'] ) {
			case 'event_date' :
				$this->__set( 'orderby', 'meta_value_num' );
				$this->__set( 'meta_key', '_mdjm_event_date' );
			break;
			case 'value' :
				$this->__set( 'orderby', 'meta_value_num' );
				$this->__set( 'meta_key', '_mdjm_event_cost' );
			break;
			default :
				$this->__set( 'orderby', $this->args['orderby'] );
			break;
		}
	} // orderby

	/**
	 * Event Date
	 *
	 * @access	public
	 * @since	1.4
	 * @return	void
	 */
	public function event_date() {
		if ( empty( $this->args['event_date_start'] ) ) {
			return;
		}

		$key = '_mdjm_event_date';

		if ( ! empty( $this->args['event_date_end'] ) )	{
			$query = array(
				'key'     => $key,
				'value'   => array(
					$this->args['event_date_start'],
					$this->args['event_date_end']
				),
				'compare' => 'BETWEEN',
				'type'    => 'DATE'
			);
		} else	{
			$query = array(
				'key'     => $key,
				'value'   => $this->args['event_date_start']
			);
		}

		$this->__set( 'meta_query', $query );
		$this->__unset( 'event_date_start' );
		$this->__unset( 'event_date_end' );
	} // event_date

	/**
	 * Specific client id
	 *
	 * @access  public
	 * @since   1.4
	 * @return  void
	 */
	public function client() {
		if ( is_null( $this->args['client'] ) || ! is_numeric( $this->args['client'] ) ) {
			return;
		}

		$client = $this->args['client'];

		$key = '_mdjm_event_client';
		$meta = array(
			'key'     => $key,
			'value'   => $client
		);

		$this->__set( 'meta_query', $meta );
	} // client

	/**
	 * Search
	 *
	 * @access	public
	 * @since	1.4
	 * @return	void
	 */
	public function search() {

		if( ! isset( $this->args['s'] ) ) {
			return;
		}

		$search = trim( $this->args['s'] );

		if( empty( $search ) ) {
			return;
		}

		$is_email = is_email( $search ) || strpos( $search, '@' ) !== false;
		$is_user  = strpos( $search, strtolower( 'user:' ) ) !== false;

		if ( $is_email ) {

			$user_data = get_user_by( 'email', $search );

			if ( $user_data )	{
				$search = $user_data->ID;
			}

			$key = '_mdjm_event_client';
			$search_meta = array(
				'key'     => $key,
				'value'   => $search
			);

			$this->__set( 'meta_query', $search_meta );
			$this->__unset( 's' );

		} elseif ( is_numeric( $search ) ) {

			$post = get_post( $search );

			if( is_object( $post ) && $post->post_type == 'mdjm-event' ) {

				$arr   = array();
				$arr[] = $search;
				$this->__set( 'post__in', $arr );
				$this->__unset( 's' );
			}

		} elseif ( '#' == substr( $search, 0, 1 ) ) {

			$search = str_replace( '#:', '', $search );
			$search = str_replace( '#', '', $search );
			$this->__set( 'event', $search );
			$this->__unset( 's' );

		} else {
			$this->__set( 's', $search );
		}

	} // search

	/**
	 * Employee
	 *
	 * @access	public
	 * @since	1.4
	 * @return	void
	 */
	public function employee() {
		if ( empty( $this->args['employee'] ) || $this->args['employee'] == 'all' ) {
			return;
		}

		$query = array(
			'relation' => 'OR',
			array(
				'key'     => '_mdjm_event_dj',
				'value'   => $this->args['employee']
			),
			array(
				'key'     => '_mdjm_event_dj',
				'value'   => sprintf( ':"%s";', $this->args['employee'] ),
				'compare' => 'LIKE'
			)
		);

		$this->__set( 'meta_query', $query );
	} // employee

	/**
	 * Specific enquiry source
	 *
	 * @access  public
	 * @since   1.4
	 * @return  void
	 */
	public function source() {
		if ( is_null( $this->args['source'] ) ) {
			return;
		}

		$source = $this->args['source'];

		if ( is_numeric( $source ) )	{
			$field = 'term_id';
			(int) $source;
		} elseif ( strpos( $source, '-') !== false || $type == strtolower( $source ) )	{
			$field = 'slug';
		} else	{
			$field = 'name';
		}

		$query = array(
			'taxonomy' => 'enquiry-source',
			'field'    => $field,
			'terms'    => $source
		);

		$this->__set( 'tax_query', $query );
	} // source

	/**
	 * Specific event type
	 *
	 * @access  public
	 * @since   1.4
	 * @return  void
	 */
	public function type() {
		if ( is_null( $this->args['type'] ) ) {
			return;
		}

		$type = $this->args['type'];

		if ( is_numeric( $type ) )	{
			$field = 'term_id';
			(int) $type;
		} elseif ( strpos( $type, '-') !== false || $type == strtolower( $type ) )	{
			$field = 'slug';
		} else	{
			$field = 'name';
		}

		$query = array(
			'taxonomy' => 'event-type',
			'field'    => $field,
			'terms'    => $this->args['type']
		);

		$this->__set( 'tax_query', $query );
	} // type

} // MDJM_Events_Query
