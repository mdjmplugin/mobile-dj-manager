<?php
/**
 * Travel Class
 *
 * @package     MDJM
 * @subpackage  Classes/Travel
 * @copyright   Copyright (c) 2016, Mike Howard
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.4
*/

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) )
	exit;

/**
 * MDJM_Travel Class
 *
 * @since	1.4
 */
class MDJM_Travel {

	/**
	 * The start location
	 *
	 * @since	1.4
	 */
	public $start;

	/**
	 * The end location
	 *
	 * @since	1.4
	 */
	public $end;

	/**
	 * The distance of travel
	 *
	 * @since	1.4
	 */
	public $distance;

	/**
	 * The cost of travel
	 *
	 * @since	1.4
	 */
	public $cost = '0.00';

	/**
	 * The travel data.
	 *
	 * @since	1.4
	 */
	public $data;

	/**
	 * The travel mode.
	 *
	 * @since	1.4
	 */
	public $mode = 'driving';

	/**
	 * Units.
	 *
	 * @since	1.4
	 */
	public $units;

	/**
	 * API key
	 *
	 * @access	private
	 * @since	1.4
	 */
	private $api_key;

	/**
	 * Maps.
	 *
	 * @access	private
	 * @since	1.4
	 */
	private $maps = 'https://maps.googleapis.com/maps/api/distancematrix/json/';

	/**
	 * The query.
	 *
	 * @access	private
	 * @since	1.4
	 */
	private $query = false;

	/**
	 * Constructor
	 *
	 * @since	1.4
	 */
	public function __construct( $args = array() ) {
		
	} // __construct

	/**
	 * Init.
	 *
	 * @access	private
	 * @since	1.4
	 */
	private function init()	{
		if ( ! isset( $this->start ) )	{
			$this->start = mdjm_get_option( 'travel_primary' );
		}
		if ( ! isset( $this->units ) )	{
			$this->units = mdjm_get_option( 'travel_units' );
		}
	} // init

	/**
	 * Set a property
	 *
	 * @since	1.4
	 */
	public function __set( $key, $value ) {
		$this->$key = $value;
	} // __set
	
	/**
	 * Magic __get function to dispatch a call to retrieve a private property
	 *
	 * @since	1.4
	 */
	public function __get( $key ) {
		if( method_exists( $this, 'get_' . $key ) ) {
			return call_user_func( array( $this, 'get_' . $key ) );
		} else {
			return new WP_Error( 'mdjm-travel-invalid-property', sprintf( __( "Can't get property %s", 'mobile-dj-manager' ), $key ) );
		}
	} // __get

	/**
	 * Prepare query data.
	 *
	 * @access	private
	 * @since	1.4
	 */
	private function prepare_query()	{
		if ( ! isset( $this->end ) )	{
			return false;
		}

		$query_args = array(
			'units'       => $this->units,
			'mode'        => $this->mode,
			'origin'      => str_replace( '%2C', ',', urlencode( $this->start ) ),
			'destination' => str_replace( '%2C', ',', urlencode( $this->end ) )
		);

		/*
		 * Allow filtering of the query args.
		 *
		 * @since	1.4
		 */
		$query_args = apply_filters( 'mdjm_travel_query_args', $query_args, $this );

		$this->query = add_query_arg( $query_args, $this->maps );
	} // prepare_query

	/**
	 * Retrieve the cost of the trip.
	 *
	 * @since	1.4
	 */
	function get_cost()	{
		if ( ! isset( $this->cost ) )	{
			$min       = mdjm_get_option( 'travel_min_distance' );
			$unit_cost = mdjm_get_option( 'cost_per_unit' );
			$round     = mdjm_get_option( 'travel_cost_round' );
		
			if ( intval( $this->distance ) >= $min )	{
				$this->cost = $this->distance * $unit_cost;
			
				if ( $round )	{
					$nearest = mdjm_get_option( 'travel_round_to' );
				
					if ( intval( $this->cost ) == $this->cost && ! is_float( intval( $this->cost ) / $nearest ) )	{
						$this->cost = intval( $this->cost );
					} else	{
						if ( $round == 'up' )	{
							$this->cost = round( ( $this->cost + $nearest / 2 ) / $nearest ) * $nearest;
						} else	{
							$this->cost = floor( ( $this->cost + $nearest / 2 ) / $nearest ) * $nearest;
						}
					}
				}
			}
		}

		return apply_filters( 'mdjm_travel_cost', $this->cost, $this );
	} // get_cost

} // class MDJM_Travel
