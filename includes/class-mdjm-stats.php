<?php
/**
 * Stats class
 *
 * @package     MDJM
 * @subpackage  Classes/Stats
 * @copyright   Copyright (c) 2016, Mike Howard
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.4
 *
 * Largely based on Easy Digital Downloads EDD_Stats class
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) )
	exit;

/**
 * MDJM_Stats Class
 *
 * Base class for other MDJM Stat classes.
 * Primary function is to convert date parameters to queries.
 *
 * @since	1.4
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
	 * @since	1.4
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
	 * @since	1.4
	 */
	public $end_date;
	
	/**
	 *
	 * @access	public
	 * @since	1.4
	 * @return	void
	 */
	public function __construct() {
		/* Nothing here. Call get_event_stats() and get_txn_stats() directly */
	} // __construct
	
	/**
	 * Get the predefined date periods permitted
	 *
	 * @access	public
	 * @since	1.4
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
	 * @since	1.4
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
	 * @access	public
	 * @since	1.4
	 * @return	arr|WP_Error	If the date is invalid, a WP_Error object will be returned
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
	 * @since	1.4
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
	 * Get Events by Date.
	 *
	 * @since	1.4
	 * @param	int		$day			Day number
	 * @param	int		$month_num		Month number
	 * @param	int		$year			Year
	 * @param	int		$hour			Hour
	 * @return	int		$events			Events
	 */
	public function get_events_by_date( $day = null, $month_num, $year = null, $hour = null )	{

		$args = array(
			'post_type'              => 'mdjm-event',
			'nopaging'               => true,
			'post_status'            => 'any',
			'fields'                 => 'ids',
			'meta_key'               => '_mdjm_event_date',
			'meta_value'             => date( 'Y-m-d' ),
			'update_post_term_cache' => false
		);

		$date = date( 'Y-m-d', strtotime( $year . '-' . $month_num . '-' . $day ) );

		$args['meta_value'] = $date;

		if ( ! empty( $hour ) )	{
			if ( $hour < 10 )	{
				$hour = '0' . $hour;
			}
			$args['meta_query'] = array(
				'relation' => 'OR',
				array(
					'key'     => '_mdjm_event_start',
					'value'   => $hour . ':00:00'
				),
				array(
					'key'     => '_mdjm_event_start',
					'value'   => $hour . ':15:00'
				),
				array(
					'key'     => '_mdjm_event_start',
					'value'   => $hour . ':30:00'
				),
				array(
					'key'     => '_mdjm_event_start',
					'value'   => $hour . ':45:00'
				)
			);
		}

		$args   = apply_filters( 'mdjm_get_events_by_date_args', $args );
		$key    = 'mdjm_stats_' . substr( md5( serialize( $args ) ), 0, 15 );
		$events = get_transient( $key );
	
		if( false === $events )	{
			$query  = new WP_Query( $args );
			$events += $query->found_posts;

			// Cache the results for one hour
			set_transient( $key, $events, HOUR_IN_SECONDS );
		}

		return $events;

	} // get_events_by_date

	/**
	 * Retrieves the total income taken over the given date period.
	 *
	 * @since	1.4
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
	 * @since	1.4
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
	 * @since	1.4
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
						$total += mdjm_sanitize_amount( get_post_meta( $txn->ID, '_mdjm_txn_total', true ) );
					} else	{
						$total -= mdjm_sanitize_amount( get_post_meta( $txn->ID, '_mdjm_txn_total', true ) );
					}
					
				} else	{
					
					$total += mdjm_sanitize_amount( get_post_meta( $txn->ID, '_mdjm_txn_total', true ) );
					
				}
			}
			
		}
		
		$total = apply_filters( 'get_txns_total_by_date', $total, $period, $status );
		
		return mdjm_currency_filter( mdjm_format_amount( $total ) );
		
	} // get_txns_total_by_date

	/**
	 * Get Income By Date.
	 *
	 * Has no consideration for any expenditure within given date range.
	 *
	 * @since	1.4
	 * @param	int		$day			Day number
	 * @param	int		$month_num		Month number
	 * @param	int		$year			Year
	 * @param	int		$hour			Hour
	 * @return	int		$income			Earnings
	 */
	public function get_income_by_date( $day = null, $month_num, $year = null, $hour = null )	{
		global $wpdb;

		$args = array(
			'post_type'              => 'mdjm-transaction',
			'nopaging'               => true,
			'year'                   => $year,
			'monthnum'               => $month_num,
			'post_status'            => 'mdjm-income',
			'meta_key'               => '_mdjm_txn_status',
			'meta_value'             => 'Completed',
			'fields'                 => 'ids',
			'update_post_term_cache' => false
		);

		if ( ! empty( $day ) )	{
			$args['day'] = $day;
		}

		if ( ! empty( $hour ) )	{
			$args['hour'] = $hour;
		}

		$args = apply_filters( 'mdjm_get_income_by_date_args', $args );
	
		$txns = mdjm_get_txns( $args );
		$income = 0;
		if ( $txns ) {
			$txns = implode( ',', $txns );

			$income = $wpdb->get_var( "SELECT SUM(meta_value) FROM $wpdb->postmeta WHERE meta_key = '_mdjm_txn_total' AND post_id IN ({$txns})" );

		}
	
		return round( $income, 2 );

	} // get_income_by_date

	/**
	 * Get Expense By Date.
	 *
	 * @since	1.4
	 * @param	int		$day			Day number
	 * @param	int		$month_num		Month number
	 * @param	int		$year			Year
	 * @param	int		$hour			Hour
	 * @return	int		$expense		Expenses
	 */
	public function get_expenses_by_date( $day = null, $month_num, $year = null, $hour = null )	{
		global $wpdb;

		$args = array(
			'post_type'              => 'mdjm-transaction',
			'nopaging'               => true,
			'year'                   => $year,
			'monthnum'               => $month_num,
			'post_status'            => 'mdjm-expenditure',
			'meta_key'               => '_mdjm_txn_status',
			'meta_value'             => 'Completed',
			'fields'                 => 'ids',
			'update_post_term_cache' => false
		);

		if ( ! empty( $day ) )	{
			$args['day'] = $day;
		}

		if ( ! empty( $hour ) )	{
			$args['hour'] = $hour;
		}

		$args     = apply_filters( 'mdjm_get_expenses_by_date_args', $args );
	
		$txns    = mdjm_get_txns( $args );
		$expense = 0;
		if ( $txns ) {
			$txns = implode( ',', $txns );

			$expense = $wpdb->get_var( "SELECT SUM(meta_value) FROM $wpdb->postmeta WHERE meta_key = '_mdjm_txn_total' AND post_id IN ({$txns})" );

		}
	
		return round( $expense, 2 );

	} // get_expenses_by_date

	/**
	 * Get Earnings By Date.
	 *
	 * @since	1.4
	 * @param	int		$day 		Day number
	 * @param	int		$month_num 	Month number
	 * @param	int		$year		Year
	 * @param	int		$hour		Hour
	 * @return	int		$earnings	Earnings
	 */
	public function get_earnings_by_date( $day = null, $month_num, $year = null, $hour = null )	{
		global $wpdb;

		$args = array(
			'post_type'              => 'mdjm-transaction',
			'nopaging'               => true,
			'year'                   => $year,
			'monthnum'               => $month_num,
			'post_status'            => array( 'mdjm-income', 'mdjm-expenditure' ),
			'meta_key'               => '_mdjm_txn_status',
			'meta_value'             => 'Completed',
			'fields'                 => 'ids',
			'update_post_term_cache' => false
		);

		if ( ! empty( $day ) )	{
			$args['day'] = $day;
		}

		if ( ! empty( $hour ) )	{
			$args['hour'] = $hour;
		}

		$args     = apply_filters( 'mdjm_get_earnings_by_date_args', $args );
		$key      = 'mdjm_stats_' . substr( md5( serialize( $args ) ), 0, 15 );
		$earnings = get_transient( $key );
	
		if( false === $earnings ) {
			$income   = $this->get_income_by_date( $day, $month_num, $year, $hour );
			$expense  = $this->get_expenses_by_date( $day, $month_num, $year, $hour );

			$earnings = $income - $expense;

			// Cache the results for one hour
			set_transient( $key, $earnings, HOUR_IN_SECONDS );
		}

		return round( $earnings, 2 );
	} // get_earnings_by_date

	/**
	 * Retrieves the count of enquiry sources over given date period.
	 *
	 * @since	1.4
	 * @param	str		$period		The date period for which to collect the stats
	 * @param	int		$status		The transaction status' for which to collect the stats
	 * @return	int		$total		The total value for all transactions that meet the criteria
	 */
	public function get_enquiry_sources_by_date( $period = 'this_week' )	{
		
		$sources = get_terms(
			array(
				'taxonomy'    => 'enquiry-source',
				'hide_empty'  => true				
			)
		);
		
		$tax_count = array();
		
		if ( ! empty( $sources ) )	{
			
			foreach( $sources as $source )	{
				
				$tax_query = array(
					'taxonomy' => 'enquiry-source',
					'terms'    => $source->term_id
				);
				
				$args = array(
					'date_query'       => array(
						$this->setup_dates( $period )
					),
					'tax_query'        => array(
						$tax_query
					)
				);
				
				$events = mdjm_get_events( $args );
				
				if ( $events )	{
					$tax_count[ count( $events ) ] = $source->name;
				}

			}
			
		}
		
		krsort( $tax_count );
		
		return $tax_count;
				
	} // get_enquiry_sources_by_date

} // class MDJM_Stats