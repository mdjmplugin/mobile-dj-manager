<?php
/**
 * Transactions Query
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
 * MDJM_Txns_Query Class
 *
 * This class is for retrieving transaction data
 *
 * Transactions can be retrieved for date ranges and pre-defined periods
 *
 * @since	1.4
 */
class MDJM_Txns_Query extends MDJM_Stats {

	/**
	 * The args to pass to the mdjm_get_transactions() query
	 *
	 * @var		array
	 * @access	public
	 * @since	1.4
	 */
	public $args = array();

	/**
	 * The transactions found based on the criteria set
	 *
	 * @var		array
	 * @access	public
	 * @since	1.4
	 */
	public $txns = array();

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
			'output'          => 'transactions', // Use 'posts' to get standard post objects
			'post_type'       => array( 'mdjm-transaction' ),
			'start_date'      => false,
			'end_date'        => false,
			'number'          => 20,
			'page'            => null,
			'orderby'         => 'date',
			'order'           => 'DESC',
			'user'            => null,
			'client'          => null,
			'status'          => array( 'mdjm-income', 'mdjm-expenditure' ),
			'meta_key'        => null,
			'year'            => null,
			'month'           => null,
			'day'             => null,
			's'               => null,
			'children'        => false,
			'fields'          => null,
			'event'           => null
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

		add_action( 'mdjm_pre_get_txns', array( $this, 'date_filter_pre' ) );
		add_action( 'mdjm_post_get_txns', array( $this, 'date_filter_post' ) );

		add_action( 'mdjm_pre_get_txns', array( $this, 'orderby' ) );
		add_action( 'mdjm_pre_get_txns', array( $this, 'status' ) );
		add_action( 'mdjm_pre_get_txns', array( $this, 'month' ) );
		add_action( 'mdjm_pre_get_txns', array( $this, 'per_page' ) );
		add_action( 'mdjm_pre_get_txns', array( $this, 'page' ) );
		add_action( 'mdjm_pre_get_txns', array( $this, 'user' ) );
		add_action( 'mdjm_pre_get_txns', array( $this, 'client' ) );
		add_action( 'mdjm_pre_get_txns', array( $this, 'search' ) );
		add_action( 'mdjm_pre_get_txns', array( $this, 'mode' ) );
		add_action( 'mdjm_pre_get_txns', array( $this, 'children' ) );
		add_action( 'mdjm_pre_get_txns', array( $this, 'event' ) );
	} // init

	/**
	 * Retrieve transactions.
	 *
	 * The query can be modified in two ways; either the action before the
	 * query is run, or the filter on the arguments (existing mainly for backwards
	 * compatibility).
	 *
	 * @access	public
	 * @since	1.4
	 * @return	obj
	 */
	public function get_txns() {

		do_action( 'mdjm_pre_get_txns', $this );

		$query = new WP_Query( $this->args );

		$custom_output = array(
			'transactions',
			'mdjm_transactions',
		);

		if ( ! in_array( $this->args['output'], $custom_output ) ) {
			return $query->posts;
		}

		if ( $query->have_posts() ) {
			while ( $query->have_posts() ) {
				$query->the_post();

				$txn_id = get_post()->ID;
				$txn    = new MDJM_Txn( $txn_id );

				$this->txns[] = apply_filters( 'mdjm_txn', $txn, $txn_id, $this );
			}

			wp_reset_postdata();
		}

		do_action( 'mdjm_post_get_txns', $this );

		return $this->txns;
	} // get_txns

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

		add_filter( 'posts_where', array( $this, 'txns_where' ) );
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

		remove_filter( 'posts_where', array( $this, 'txns_where' ) );
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
			case 'amount' :
				$this->__set( 'orderby', 'meta_value_num' );
				$this->__set( 'meta_key', '_mdjm_txn_total' );
			break;
			default :
				$this->__set( 'orderby', $this->args['orderby'] );
			break;
		}
	} // orderby

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

		$this->__set( 'author', (int) $this->args['client'] );
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

			$key = '_mdjm_payer_email';
			$search_meta = array(
				'key'     => $key,
				'value'   => $search,
				'compare' => 'LIKE'
			);

			$this->__set( 'meta_query', $search_meta );
			$this->__unset( 's' );

		} elseif ( $is_user ) {

			$search_meta = array(
				'key'   => '_mdjm_payee_user_id',
				'value' => trim( str_replace( 'user:', '', strtolower( $search ) ) )
			);

			$this->__set( 'meta_query', $search_meta );

			$this->__unset( 's' );

		} elseif ( is_numeric( $search ) ) {

			$post = get_post( $search );

			if( is_object( $post ) && $post->post_type == 'mdjm-transaction' ) {

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
	 * Payment Mode
	 *
	 * @access public
	 * @since 1.8
	 * @return void
	 */
	public function mode() {
		if ( empty( $this->args['mode'] ) || $this->args['mode'] == 'all' ) {
			$this->__unset( 'mode' );
			return;
		}

		$this->__set( 'meta_query', array(
			'key'   => '_mdjm_txn_source',
			'value' => $this->args['mode']
		) );
	} // mode

	/**
	 * Specific Event
	 *
	 * @access	public
	 * @since	1.4
	 * @return	void
	 */
	public function event() {

		if ( empty( $this->args['event'] ) )	{
			return;
		}

		$key = is_array( $this->args['event'] ) ? 'post_parent__in' : 'post_parent';

		unset( $args['post_parent'] );
		$this->__set( $key, $this->args['event'] );
		$this->__unset( 'event' );

	} // event

} // MDJM_Txns_Query
