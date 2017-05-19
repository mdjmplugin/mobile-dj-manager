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
	 * The event date for the period we're getting stats for
	 *
	 * Can be a timestamp, formatted date, date string (such as August 3, 2013),
	 * an array containing 'start' and 'end' dates, or a predefined date string, such as last_week or this_month
	 *
	 * Predefined date options are: today, yesterday, this_week, last_week, this_month, last_month
	 * this_quarter, last_quarter, this_year, last_year
	 *
	 * The event date is optional
	 *
	 * @access	public
	 * @since	1.4
	 */
	public $event_date;

	/**
	 * Flag to determine if current query is based on timestamps
	 *
	 * @access	public
	 * @since	1.4
	 */
	public $timestamp;

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
	public function setup_dates( $_start_date = 'this_month', $_end_date = false, $_event_date = false ) {

		if( empty( $_start_date ) ) {
			$_start_date = 'this_month';
		}

		if( empty( $_end_date ) ) {
			$_end_date = $_start_date;
		}

		if ( ! empty( $_event_date ) )	{
			if ( is_array( $_event_date ) )	{
				if ( empty( $_event_date['start'] ) )	{
					$_event_date['start'] = 'this_month';
				}
				if ( empty( $_event_date['end'] ) )	{
					$_event_date['end'] = $_event_date['start'];
				}
			} else	{
				$_event_date = array( 'start' => $_event_date );
			}
		}

		$this->start_date = $this->convert_date( $_start_date );
		$this->end_date   = $this->convert_date( $_end_date, true );

		if ( ! empty( $_event_date ) )	{
			$this->event_date = array( 'start' => $this->convert_date( $_event_date ) );
			if ( ! empty( $_event_date['end'] ) )	{
				$this->event_date['end'] = $this->convert_date( $_event_date, true );
			}
		}

	} // setup_dates
	
	/**
	 * Converts a date to a WP_Query date query.
	 *
	 * @access	public
	 * @since	1.4
	 * @return	arr|WP_Error	If the date is invalid, a WP_Error object will be returned
	 */
	public function convert_date( $date, $end_date = false ) {

		$this->timestamp = false;
		$second          = $end_date ? 59 : 0;
		$minute          = $end_date ? 59 : 0;
		$hour            = $end_date ? 23 : 0;
		$day             = 1;
		$month           = date( 'n', current_time( 'timestamp' ) );
		$year            = date( 'Y', current_time( 'timestamp' ) );

		if ( array_key_exists( $date, $this->get_predefined_dates() ) ) {

			// This is a predefined date rate, such as last_week
			switch( $date ) {

				case 'this_month' :

					if( $end_date ) {

						$day    = cal_days_in_month( CAL_GREGORIAN, $month, $year );
						$hour   = 23;
						$minute = 59;
						$second = 59;

					}

					break;

				case 'last_month' :

					if( $month == 1 ) {

						$month = 12;
						$year--;

					} else {

						$month--;

					}

					if( $end_date ) {
						$day = cal_days_in_month( CAL_GREGORIAN, $month, $year );
					}

					break;

				case 'today' :

					$day = date( 'd', current_time( 'timestamp' ) );

					if( $end_date ) {
						$hour   = 23;
						$minute = 59;
						$second = 59;
					}

					break;

				case 'yesterday' :

					$day = date( 'd', current_time( 'timestamp' ) ) - 1;

					// Check if Today is the first day of the month (meaning subtracting one will get us 0)
					if( $day < 1 ) {

						// If current month is 1
						if( 1 == $month ) {

							$year -= 1; // Today is January 1, so skip back to last day of December
							$month = 12;
							$day   = cal_days_in_month( CAL_GREGORIAN, $month, $year );

						} else {

							// Go back one month and get the last day of the month
							$month -= 1;
							$day    = cal_days_in_month( CAL_GREGORIAN, $month, $year );

						}
					}

					break;

				case 'this_week' :

					$days_to_week_start = ( date( 'w', current_time( 'timestamp' ) ) - 1 ) *60*60*24;
				 	$today = date( 'd', current_time( 'timestamp' ) ) *60*60*24;

				 	if ( $today < $days_to_week_start ) {

				 		if( $month > 1 ) {
					 		$month -= 1;
					 	} else {
					 		$month = 12;
					 	}

				 	}

					if ( ! $end_date ) {
					 	// Getting the start day
						$day = date( 'd', current_time( 'timestamp' ) - $days_to_week_start ) - 1;
						$day += get_option( 'start_of_week' );

					} else {
						// Getting the end day
						$day = date( 'd', current_time( 'timestamp' ) - $days_to_week_start ) - 1;
						$day += get_option( 'start_of_week' ) + 6;

					}

					break;

				case 'last_week' :

					$days_to_week_start = ( date( 'w', current_time( 'timestamp' ) ) - 1 ) *60*60*24;
				 	$today = date( 'd', current_time( 'timestamp' ) ) *60*60*24;

				 	if ( $today < $days_to_week_start ) {

				 		if( $month > 1 ) {
					 		$month -= 1;
					 	} else {
					 		$month = 12;
					 	}

				 	}

					if ( ! $end_date ) {
					 	// Getting the start day
						$day = date( 'd', current_time( 'timestamp' ) - $days_to_week_start ) - 8;
						$day += get_option( 'start_of_week' );

					} else {
						// Getting the end day
						$day = date( 'd', current_time( 'timestamp' ) - $days_to_week_start ) - 8;
						$day += get_option( 'start_of_week' ) + 6;
					}

					break;

				case 'this_quarter' :

					$month_now = date( 'n', current_time( 'timestamp' ) );

					if ( $month_now <= 3 ) {

						if( ! $end_date ) {
							$month = 1;
						} else {
							$month = 3;
							$day    = cal_days_in_month( CAL_GREGORIAN, $month, $year );
							$hour   = 23;
							$minute = 59;
							$second = 59;
						}

					} else if ( $month_now <= 6 ) {

						if( ! $end_date ) {
							$month = 4;
						} else {
							$month = 6;
							$day    = cal_days_in_month( CAL_GREGORIAN, $month, $year );
							$hour   = 23;
							$minute = 59;
							$second = 59;
						}

					} else if ( $month_now <= 9 ) {

						if( ! $end_date ) {
							$month = 7;
						} else {
							$month = 9;
							$day    = cal_days_in_month( CAL_GREGORIAN, $month, $year );
							$hour   = 23;
							$minute = 59;
							$second = 59;
						}

					} else {

						if( ! $end_date ) {
							$month = 10;
						} else {
							$month = 12;
							$day    = cal_days_in_month( CAL_GREGORIAN, $month, $year );
							$hour   = 23;
							$minute = 59;
							$second = 59;
						}

					}

					break;

				case 'last_quarter' :

					$month_now = date( 'n', current_time( 'timestamp' ) );

					if ( $month_now <= 3 ) {

						if( ! $end_date ) {
							$month = 10;
						} else {
							$year -= 1;
							$month = 12;
							$day    = cal_days_in_month( CAL_GREGORIAN, $month, $year );
							$hour   = 23;
							$minute = 59;
							$second = 59;
						}

					} else if ( $month_now <= 6 ) {

						if( ! $end_date ) {
							$month = 1;
						} else {
							$month = 3;
							$day    = cal_days_in_month( CAL_GREGORIAN, $month, $year );
							$hour   = 23;
							$minute = 59;
							$second = 59;
						}

					} else if ( $month_now <= 9 ) {

						if( ! $end_date ) {
							$month = 4;
						} else {
							$month = 6;
							$day    = cal_days_in_month( CAL_GREGORIAN, $month, $year );
							$hour   = 23;
							$minute = 59;
							$second = 59;
						}

					} else {

						if( ! $end_date ) {
							$month = 7;
						} else {
							$month = 9;
							$day    = cal_days_in_month( CAL_GREGORIAN, $month, $year );
							$hour   = 23;
							$minute = 59;
							$second = 59;
						}

					}

					break;

				case 'this_year' :

					if( ! $end_date ) {
						$month  = 1;
					} else {
						$month  = 12;
						$day    = cal_days_in_month( CAL_GREGORIAN, $month, $year );
						$hour   = 23;
						$minute = 59;
						$second = 59;
					}

					break;

				case 'last_year' :

					$year -= 1;
					if( ! $end_date ) {
						$month = 1;
					} else {
						$month  = 12;
						$day    = cal_days_in_month( CAL_GREGORIAN, $month, $year );
						$hour   = 23;
						$minute = 59;
						$second = 59;
					}

				break;

			}


		} elseif ( is_numeric( $date ) )	{

			// return $date unchanged since it is a timestamp
			$this->timestamp = true;

		} elseif ( false !== strtotime( $date ) )	{

			$date  = strtotime( $date, current_time( 'timestamp' ) );
			$year  = date( 'Y', $date );
			$month = date( 'm', $date );
			$day   = date( 'd', $date );

		} else	{

			return new WP_Error( 'invalid_date', __( 'Improper date provided.', 'mobile-dj-manager' ) );

		}

		if ( false === $this->timestamp ) {
			// Create an exact timestamp
			$date = mktime( $hour, $minute, $second, $month, $day, $year );

		}

		return apply_filters( 'mdjm_stats_date', $date, $end_date, $this );

	} // convert_date

	/**
	 * Modifies the WHERE flag for event counts
	 *
	 * @access	public
	 * @since	1.4
	 * @return	str
	 */
	public function count_events_where( $where = '' ) {
		// Only get events in our date range

		$start_where = '';
		$end_where   = '';

		if( $this->start_date ) {

			if( $this->timestamp ) {
				$format = 'Y-m-d H:i:s';
			} else {
				$format = 'Y-m-d 00:00:00';
			}

			$start_date  = date( $format, $this->start_date );
			$start_where = " AND p.post_date >= '{$start_date}'";
		}

		if( $this->end_date ) {

			if( $this->timestamp ) {
				$format = 'Y-m-d H:i:s';
			} else {
				$format = 'Y-m-d 23:59:59';
			}

			$end_date  = date( $format, $this->end_date );

			$end_where = " AND p.post_date <= '{$end_date}'";
		}

		$where .= "{$start_where}{$end_where}";

		return $where;
	} // count_events_where

	/**
	 * Modifies the WHERE flag for event queries
	 *
	 * @access	public
	 * @since	1.4
	 * @return	str
	 */
	public function events_where( $where = '' ) {

		global $wpdb;

		$start_where = '';
		$end_where   = '';

		if( ! is_wp_error( $this->start_date ) ) {

			if( $this->timestamp ) {
				$format = 'Y-m-d H:i:s';
			} else {
				$format = 'Y-m-d 00:00:00';
			}

			$start_date  = date( $format, $this->start_date );
			$start_where = " AND $wpdb->posts.post_date >= '{$start_date}'";
		}

		if( ! is_wp_error( $this->end_date ) ) {

			if ( $this->timestamp ) {
				$format = 'Y-m-d 00:00:00';
			} else {
				$format = 'Y-m-d 23:59:59';
			}

			$end_date  = date( $format, $this->end_date );

			$end_where = " AND $wpdb->posts.post_date <= '{$end_date}'";
		}

		$where .= "{$start_where}{$end_where}";

		return $where;
	} // events_where

	/**
	 * Modifies the WHERE flag for txn counts
	 *
	 * @access	public
	 * @since	1.4
	 * @return	str
	 */
	public function count_txns_where( $where = '' ) {
		// Only get transactions in our date range

		$start_where = '';
		$end_where   = '';

		if( $this->start_date ) {

			if( $this->timestamp ) {
				$format = 'Y-m-d H:i:s';
			} else {
				$format = 'Y-m-d 00:00:00';
			}

			$start_date  = date( $format, $this->start_date );
			$start_where = " AND p.post_date >= '{$start_date}'";
		}

		if( $this->end_date ) {

			if( $this->timestamp ) {
				$format = 'Y-m-d H:i:s';
			} else {
				$format = 'Y-m-d 23:59:59';
			}

			$end_date  = date( $format, $this->end_date );

			$end_where = " AND p.post_date <= '{$end_date}'";
		}

		$where .= "{$start_where}{$end_where}";

		return $where;
	} // count_txns_where

	/**
	 * Modifies the WHERE flag for txn queries
	 *
	 * @access	public
	 * @since	1.4
	 * @return	str
	 */
	public function txns_where( $where = '' ) {

		global $wpdb;

		$start_where = '';
		$end_where   = '';

		if( ! is_wp_error( $this->start_date ) ) {

			if( $this->timestamp ) {
				$format = 'Y-m-d H:i:s';
			} else {
				$format = 'Y-m-d 00:00:00';
			}

			$start_date  = date( $format, $this->start_date );
			$start_where = " AND $wpdb->posts.post_date >= '{$start_date}'";
		}

		if( ! is_wp_error( $this->end_date ) ) {

			if ( $this->timestamp ) {
				$format = 'Y-m-d 00:00:00';
			} else {
				$format = 'Y-m-d 23:59:59';
			}

			$end_date  = date( $format, $this->end_date );

			$end_where = " AND $wpdb->posts.post_date <= '{$end_date}'";
		}

		$where .= "{$start_where}{$end_where}";

		return $where;
	} // txns_where

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
					'key'     => '_mdjm_txn_status',
					'value'   => 'Completed'
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
	 * @return	int		$income			Income
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
	 * Retrieve earnings.
	 *
	 * @since	1.4
	 * @param	str|bool	$start_date The starting date for which we'd like to retrieve our earnings. If false, we'll use the default start date of `this_month`
	 * @param	str|bool	$end_date	The end date for which we'd like to retrieve our earnings. If false, we'll use the default end date of `this_month`
	 *
	 */
	public function get_earnings( $month, $year ) {

		return $this->get_earnings_by_date( null, $month, $year );

	} // get_earnings

	/**
	 * Retrieve a count of coversions by enquiry source for the given date period.
	 *
	 * @since	1.4
	 * @param	arr			$event_ids	Array of event ID's to check if they have been converted.
	 * @param	str			$tax		The taxonomy to retrieve stats for.
	 * @param	str			$term		The term slug to retrieve stats for. If false, gets stats for all enquiry sources.
	 * @param	str|bool	$start_date The starting date for which we'd like to filter our sale stats. If false, we'll use the default start date of `this_month`
	 * @param	str|bool	$end_date	The end date for which we'd like to filter our sale stats. If false, we'll use the default end date of `this_month`
	 * @return	float|int	Total number of conversions based on the passed arguments.
	 */
	public function get_conversions( $event_ids = false, $tax = 'event-types', $term, $start_date = false, $end_date = false )	{
		$converted_statuses = apply_filters( 'mdjm_converted_event_statuses', array( 'mdjm-approved', 'mdjm-contract', 'mdjm-completed', 'mdjm-cancelled' ) );

		if ( ! empty( $event_ids ) )	{
			$conversions = 0;

			foreach( $event_ids as $event_id )	{
				if ( in_array( get_post_status( $event_id ), $converted_statuses ) )	{
					$conversions++;
				}
			}

			return $conversions;
		}

		$this->setup_dates( $start_date, $end_date );

		// Make sure start date is valid
		if( is_wp_error( $this->start_date ) )	{
			return $this->start_date;
		}

		// Make sure end date is valid
		if( is_wp_error( $this->end_date ) )	{
			return $this->end_date;
		}

		$args = array(
			'post_type'              => 'mdjm-event',
			'nopaging'               => true,
			'post_status'            => $converted_statuses,
			'fields'                 => 'ids',
			'update_post_term_cache' => false,
			'date_query'             => array(
				array(
					'after'     => $this->start_date,
					'before'    => $this->end_date,
					'inclusive' => true
				)
			),
			'tax_query'              => array(
				array(
					'taxonomy' => $tax,
					'field'    => 'slug',
					'terms'    => $term
				)
			)
		);

		$conversions = new WP_Query( $args );

		return $conversions->found_posts;
	} // get_conversions

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
		$this->setup_dates( $period );

		$tax_count = array();
		
		if ( ! empty( $sources ) )	{
			
			foreach( $sources as $source )	{
				
				$tax_query = array(
					'taxonomy' => 'enquiry-source',
					'terms'    => $source->term_id
				);
				
				$args = array(
					'date_query'       => array(
						array(
							'after' => array(
								'year'  => date( 'Y', $this->start_date ),
								'month' => date( 'n', $this->start_date ),
								'day'   => date( 'd', $this->start_date )
							),
							'before'    => array(
								'year'  => date( 'Y', $this->end_date ),
								'month' => date( 'n', $this->end_date ),
								'day'   => date( 'd', $this->end_date )
							),
							'inclusive' => true
						)
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
