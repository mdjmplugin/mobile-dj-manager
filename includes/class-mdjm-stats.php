<?php
/**
 * Stats class
 *
 * @package     MDJM
 * @subpackage  Classes/Stats
 * @copyright   Copyright (c) 2016, Mike Howard
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.3
 *
 * Largely based on Easy Digital Downloads EDD_Stats class
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * MDJM_Stats Class
 *
 * Base class for other MDJM Stat classes.
 * Primary function is to convert date parameters to queries.
 *
 * @since	1.3
 */
class MDJM_Stats	{
	/**
	 * The start date for the period we're getting stats for.
	 *
	 * Can be a timestamp, formatted date, date string (such as August 3, 2013),
	 * or a predefined date string, such as last_week or this_month
	 *
	 * Predefined date options are: today, yesterday, this_week, last_week, this_month, last_month
	 * this_quarter, last_quarter, this_year, last_year
	 *
	 * @access	public
	 * @since	1.3
	 */
	public $start_date;


	/**
	 * The end date for the period we're getting stats for
	 *
	 * Can be a timestamp, formatted date, date string (such as August 3, 2013),
	 * or a predefined date string, such as last_week or this_month
	 *
	 * Predefined date options are: today, yesterday, this_week, last_week, this_month, last_month
	 * this_quarter, last_quarter, this_year, last_year
	 *
	 * The end date is optional
	 *
	 * @access	public
	 * @since	1.3
	 */
	public $end_date;
	
	/**
	 *
	 * @access	public
	 * @since	1.3
	 * @return	void
	 */
	public function __construct() {
		/* Nothing here. Call get_event_stats() and get_txn_stats() directly */
	} // __construct
	
	/**
	 * Get the predefined date periods permitted
	 *
	 * @access	public
	 * @since	1.3
	 * @return	array
	 */
	public function get_predefined_dates() {
		
		$predefined = array(
			'today'        => __( 'Today',        'mobile-dj-manager' ),
			'yesterday'    => __( 'Yesterday',    'mobile-dj-manager' ),
			'this_week'    => __( 'This Week',    'mobile-dj-manager' ),
			'last_week'    => __( 'Last Week',    'mobile-dj-manager' ),
			'this_month'   => __( 'This Month',   'mobile-dj-manager' ),
			'last_month'   => __( 'Last Month',   'mobile-dj-manager' ),
			'this_quarter' => __( 'This Quarter', 'mobile-dj-manager' ),
			'last_quarter' => __( 'Last Quarter',  'mobile-dj-manager' ),
			'this_year'    => __( 'This Year',    'mobile-dj-manager' ),
			'last_year'    => __( 'Last Year',    'mobile-dj-manager' )
		);
		
		return apply_filters( 'mdjm_stats_predefined_dates', $predefined );

	} // get_predefined_dates
	
	/**
	 * Setup the dates passed to our constructor.
	 *
	 * This calls the convert_date() member function to ensure the dates are formatted correctly
	 *
	 * @access	public
	 * @since	1.3
	 * @return	void
	 */
	public function setup_dates( $_start_date = 'this_month', $_end_date = false ) {

		if( empty( $_start_date ) ) {
			$_start_date = 'this_month';
		}

		$this->start_date = $this->convert_date( $_start_date );
		$this->end_date   = ! empty( $_end_date ) ? $this->convert_date( $_end_date, true ) : false;

	} // setup_dates
	
	/**
	 * Converts a date to a WP_Query date query.
	 *
	 * @access public
	 * @since 1.8
	 * @return array|WP_Error If the date is invalid, a WP_Error object will be returned
	 */
	public function convert_date( $date, $end_date = false ) {

		$now             = current_time( 'timestamp' );
		$second          = $end_date ? 59 : 0;
		$minute          = $end_date ? 59 : 0;
		$hour            = $end_date ? 23 : 0;
		$day             = 1;
		$month           = date( 'n', $now );
		$year            = date( 'Y', $now );

		if ( array_key_exists( $date, $this->get_predefined_dates() ) ) {
			
			// This is a predefined date rate, such as last_week
			switch( $date ) {

				case 'this_month' :

					$date_query = array( 'year' => date( 'Y' ), 'month' => date( 'm' ) );

					break;

				case 'last_month' :
					
					$date_query = array( 'year' => date( 'Y', strtotime( '-1 month' ) ), 'month' => date( 'm', strtotime( '-1 month' ) ) );

					break;

				case 'today' :
				
					$today = get_date( $now );

					$date_query = array( 'year' => $today['year'], 'month' => $today['mon'], 'day' => $today['mday'] );

					break;

				case 'yesterday' :
					
					$yesterday = getdate( ( $now - DAY_IN_SECONDS ) );
					
					$date_query = array( 'year' => $yesterday['year'], 'month' => $yesterday['mon'], 'day' => $yesterday['mday'] );

					break;

				case 'this_week' :
					
					$date_query = array( 'year' => date( 'Y', $now ), 'week' => date( 'W', $now ) );

					break;

				case 'last_week' :
				
					$last_week = ( $now - WEEK_IN_SECONDS );

					$date_query = array( 'year' => date( 'Y', $last_week ), 'week' => date( 'W', $last_week ) );

					break;

				case 'this_year' :

					$date_query = array( 'year' => date( 'Y', $now ) );

					break;

				case 'last_year' :
				
					$last_year = ( $now - YEAR_IN_SECONDS  );

					$date_query = array( 'year' => date( 'Y', $last_year ) );

				break;

			}

		} else {

			return new WP_Error( 'invalid_date', __( 'Improper date provided.', 'mobile-dj-manager' ) );

		}

		return apply_filters( 'mdjm_stats_date', $date_query, $date, $end_date, $this );

	} // convert_date
	
	/**
	 * Retrieves the number of events over the given date period.
	 *
	 * @since	1.3
	 * @param	str		$period		The date period for which to collect the stats
	 * @param	str|arr	$status		The event status' for which to collect the stats
	 * @return	int		$count		The total number of events that match the criteria
	 */
	public function events_by_date( $period = 'this_week', $status = 'any' )	{
		
		$date_query = $this->setup_dates( $period );
				
		$args = array(
			'post_status'	=> $status,
			'date_query'	=> array(
				$this->start_date
			)
		);
		
		$events = mdjm_get_events( $args );
		$count  = 0;
		
		if ( $events )	{
			$count = count( $events );
		}
		
		return $count;
		
	} // events_by_date
	
	/**
	 * Retrieves the total income taken over the given date period.
	 *
	 * @since	1.3
	 * @param	str		$period		The date period for which to collect the stats
	 * @param	str|arr	$status		The event status' for which to collect the stats
	 * @return	int		$total		The total value for all transactions that meet the criteria
	 */
	public function get_total_income_by_date( $period = 'this_week' )	{
		return $this->get_txns_total_by_date( $period, 'mdjm-income' );
	} // get_total_income_by_date
	
	/**
	 * Retrieves the total income taken over the given date period.
	 *
	 * @since	1.3
	 * @param	str		$period		The date period for which to collect the stats
	 * @param	str|arr	$status		The event status' for which to collect the stats
	 * @return	int		$total		The total value for all transactions that meet the criteria
	 */
	public function get_total_outgoings_by_date( $period = 'this_week' )	{
		return $this->get_txns_total_by_date( $period, 'mdjm-expenditure' );
	} // get_total_outgoings_by_date
	
	/**
	 * Retrieves the total of all transactions over the given date period.
	 * Total income - total expenditure if $status = any
	 *
	 * @since	1.3
	 * @param	str		$period		The date period for which to collect the stats
	 * @param	int		$status		The transaction status' for which to collect the stats
	 * @return	int		$total		The total value for all transactions that meet the criteria	
	 */
	public function get_txns_total_by_date( $period = 'this_week', $status = 'any' )	{
		
		$this->setup_dates( $period );
		
		$args = array(
			'post_status'      => $status,
			'date_query'       => array(
				$this->start_date
			),
			'meta_query'       => array(
				array(
					'_mdjm_txn_status'    => 'Completed'
				)
			)
		);
		
		$txns = mdjm_get_txns( $args );
		
		$total = 0;
		
		if ( $txns )	{
			
			foreach( $txns as $txn )	{
				
				if ( $args['post_status'] == 'any' )	{
					
					if ( $txn->post_status == 'mdjm-income' )	{
						$total += get_post_meta( $txn->ID, '_mdjm_txn_total', true );
					} else	{
						$total -= get_post_meta( $txn->ID, '_mdjm_txn_total', true );
					}
					
				} else	{
					
					$total += get_post_meta( $txn->ID, '_mdjm_txn_total', true );
					
				}
			}
			
		}
		
		$total = apply_filters( 'get_txns_total_by_date', $total, $period, $status );
		
		return mdjm_format_amount( $total );
		
	} // get_txns_total_by_date
	
} // class MDJM_Stats