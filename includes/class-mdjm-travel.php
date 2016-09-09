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
	 * The start address
	 *
	 * @since	1.4
	 */
	public $start_address;

	/**
	 * The destination address
	 *
	 * @since	1.4
	 */
	public $destination_address;

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
	 * Whether or not to add travel costs to an event
	 *
	 * @since	1.4
	 */
	public $add_travel_cost = false;

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
	private $maps = 'https://maps.googleapis.com/maps/api/distancematrix/json';

	/**
	 * Directions.
	 *
	 * @access	private
	 * @since	1.4
	 */
	private $directions = 'https://maps.google.com/maps';

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
		$this->init();
	} // __construct

	/**
	 * Init.
	 *
	 * @access	private
	 * @since	1.4
	 */
	private function init()	{
		if ( ! isset( $this->start_address ) )	{
			$this->start_address = mdjm_get_option( 'travel_primary' );
		}

		$this->units = mdjm_get_option( 'travel_units' );
		$this->add_travel_cost = mdjm_get_option( 'travel_add_cost' );
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
		if ( ! isset( $this->destination_address ) )	{
			return false;
		}

		if ( is_array( $this->start_address ) )	{
			$this->start_address = implode( ',', $this->start_address );
		}

		if ( is_array( $this->destination_address ) )	{
			$this->destination_address = implode( ',', $this->destination_address );
		}

		$query_args = array(
			'units'        => $this->units,
			'mode'         => $this->mode,
			'origins'      => str_replace( '%2C', ',', urlencode( $this->start_address ) ),
			'destinations' => str_replace( '%2C', ',', urlencode( $this->destination_address ) )
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
	 * Process the query.
	 *
	 * @since	1.4
	 */
	public function process_query()	{
		$this->prepare_query();

		if ( ! isset( $this->query ) )	{
			return false;
		}

		$response = wp_remote_get( $this->query );

		if ( is_wp_error( $response ) ) {
			return false;
		}

		$travel_data = json_decode( wp_remote_retrieve_body( $response ) );

		if ( empty( $travel_data ) || $travel_data->status != 'OK' )	{
			return false;
		}

		if ( empty( $travel_data->rows ) )	{
			return false;
		}
	
		if ( empty( $travel_data->origin_addresses[0] ) || empty( $travel_data->destination_addresses[0] ) )	{
			return false;
		}
	
		if ( empty( $travel_data->rows[0]->elements[0]->distance->value ) || empty( $travel_data->rows[0]->elements[0]->duration->value ) )	{
			return false;
		}

		$this->distance = str_replace(
			array( 'km', 'mi' ),
			array( '', '' ),
			$travel_data->rows[0]->elements[0]->distance->text
		);

		$travel = array(
			'origin'      => $travel_data->origin_addresses[0],
			'destination' => $travel_data->destination_addresses[0],
			'duration'    => $travel_data->rows[0]->elements[0]->duration->value,
			'distance'    => $this->distance,
		);

		$travel = apply_filters( 'mdjm_travel_data', $travel, $travel_data );

		return $travel;

	} // process_query

	/**
	 * Retrieve the travel data.
	 *
	 * @since	1.4
	 */
	public function get_travel_data()	{
		if ( ! isset( $this->destination_address ) )	{
			return false;
		}

		$this->data = $this->process_query();

		return apply_filters( 'mdjm_get_travel_data', $this->data, $this );
	} // get_travel_data

	/**
	 * Retrieve the cost of the trip.
	 *
	 * @since	1.4
	 */
	function get_cost()	{
		if ( ! $this->add_travel_cost )	{
			return 0;
		}

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

		return apply_filters( 'mdjm_travel_cost', $this->cost, $this );
	} // get_cost

	/**
	 * Retrieve a venue address.
	 *
	 * @since	1.4
	 * @param	int			$id			Post ID of either an event or venue.
	 * @return	arr|false	$address	Array of address fields, or false.
	 */
	public function get_venue_address( $id )	{
		$post_type = get_post_type( $id );

		if ( 'mdjm-event' != $post_type && 'mdjm-venue' != $post_type )	{
			return false;
		}

		$venue_address = mdjm_get_event_venue_meta( $id, 'address' );

		$address = ! empty( $venue_address ) ? $venue_address : false;
		$address = apply_filters( 'mdjm_get_venue_address', $address, $id );

		return $address;
	} // get_venue_address

	/**
	 * Retrieve an employees address.
	 *
	 * @since	1.4
	 * @param	int			$employee_id	User ID of an employee.
	 * @return	arr|false	$address		Array of address fields, or false.
	 */
	public function get_employee_address( $employee_id )	{

		if ( ! mdjm_is_employee( $employee_id ) )	{
			return false;
		}

		$employee_address = mdjm_get_employee_address( $employee_id );

		if ( is_array( $employee_address ) )	{
			$employee_address = implode( ',', array_filter( $employee_address ) );
		}

		// To filter this use apply_filters( 'mdjm_get_employee_address', $address, $user_id );
		$address = ! empty( $employee_address ) ? $employee_address : false;

		return $address;
	} // get_employee_address

	/**
	 * Retrieve a clients address.
	 *
	 * @since	1.4
	 * @param	int			$client_id	User ID of an employee.
	 * @return	arr|false	$address	Array of address fields, or false.
	 */
	public function get_client_address( $client_id )	{
		$client_address = mdjm_get_client_address( $client_id );

		if ( is_array( $client_address ) )	{
			$client_address = implode( ',', array_filter( $client_address ) );
		}

		// To filter this use apply_filters( 'mdjm_get_client_address', $address, $client_id );
		$address = ! empty( $client_address ) ? $client_address : false;

		return $address;
	} // get_client_address

	/**
	 * Set the destination address.
	 *
	 * @since	1.4
	 * @param	int|arr|obj		$destination	An event ID or object, or a venue post ID or an address array.
	 * @return	void
	 */
	public function set_destination( $dest )	{
		if ( is_array( $dest ) )	{ // Address array is passed
			$dest = implode( ',', array_filter( $dest ) );
			$this->destination_address = $dest;
		} elseif ( is_object( $dest ) )	{ // Event object is passed
			$mdjm_event = new MDJM_Event( $event );
			$this->destination_address = $this->get_venue_address( $mdjm_event->get_venue_id() );
		} elseif ( is_numeric( $dest ) )	{ // Event or Venue ID is passed
			$this->destination_address = $this->get_venue_address( $dest );
		}
	} // set_destination

	/**
	 * Retrieve the URL for directions.
	 *
	 * @since	1.4
	 */
	function get_directions_url()	{
		if ( ! isset( $this->destination_address ) )	{
			return false;
		}

		if ( is_array( $this->start_address ) )	{
			$this->start_address = implode( ',', $this->start_address );
		}

		if ( is_array( $this->destination_address ) )	{
			$this->destination_address = implode( ',', $this->destination_address );
		}

		$url = add_query_arg( array(
			'dirflg' => 'd',
			'saddr'  => urlencode( $this->start_address ),
			'daddr'  => urlencode( $this->destination_address )
		), $this->directions );

		return apply_filters( 'mdjm_directions_url', $url, $this );

	} // get_directions_url

} // class MDJM_Travel
