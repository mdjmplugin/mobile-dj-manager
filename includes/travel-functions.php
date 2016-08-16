<?php
/**
 * Contains all travel related functions
 *
 * @package		MDJM
 * @subpackage	Venues
 * @since		1.3.8
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) )
	exit;

/**
 * Calculate the travel distance
 *
 * @since	1.3.8
 * @param	int|obj		$event		The event ID or the event MDJM_Event class object.
 * @param	int			$venue_id	The venue ID
 * @return	str			The distance to the event venue or an empty string
 */
function mdjm_travel_get_distance( $event = '', $venue_id = '' )	{

	if ( ! empty( $event ) )	{
		if ( ! is_object( $event ) )	{
			$mdjm_event = new MDJM_Event( $event );
		} else	{
			$mdjm_event = $event;
		}
	}

	$start       = mdjm_travel_get_start( $mdjm_event );
	$destination = mdjm_travel_get_destination( $mdjm_event, $venue_id );

	if ( empty( $start ) || empty( $destination ) )	{
		return false;
	}

	$query = mdjm_travel_build_url( $start, $destination );

	$response = wp_remote_get( $query );

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

	$return = array(
		'origin'      => $travel_data->origin_addresses[0],
		'destination' => $travel_data->destination_addresses[0],
		'duration'    => $travel_data->rows[0]->elements[0]->duration->value,
		'distance'    => str_replace(
			array( 'km', 'mi' ),
			array( '', '' ),
			$travel_data->rows[0]->elements[0]->distance->text
		),
	);

	return apply_filters( 'mdjm_travel_get_distance', $return, $event );

} // mdjm_travel_get_distance

/**
 * Calculate the travel cost.
 *
 * @since	1.3.8
 * @param	str			$distance		The distance of travel.
 * @return	str|int		The cost of travel.
 */
function mdjm_get_travel_cost( $distance )	{

	$min       = mdjm_get_option( 'travel_min_distance' );
	$unit_cost = mdjm_get_option( 'cost_per_unit' );
	$round     = mdjm_get_option( 'travel_cost_round' );

	if ( intval( $distance ) < $min )	{
		return '0.00';
	}

	$cost = $distance * $unit_cost;

	if ( $round )	{

		$nearest = mdjm_get_option( 'travel_round_to' );
	
		if ( intval( $cost ) == $cost && ! is_float( intval( $cost ) / $nearest ) )	{
			$cost = intval( $cost );
		} else	{
			if ( $round == 'up' )	{
				$cost = round( ( $cost + $nearest / 2 ) / $nearest ) * $nearest;
			} else	{
				$cost = floor( ( $cost + $nearest / 2 ) / $nearest ) * $nearest;
			}
		}

	} else	{
		$cost = $cost;
	}

	return apply_filters( 'mdjm_get_travel_cost', $cost, $distance );

} // mdjm_get_travel_cost

/**
 * Build the URL to retrieve the distance.
 *
 * @since	1.3.8
 * @param	str			$start			The travel start address.
 * @return	str			$destination	The travel destination address.
 */
function mdjm_travel_build_url( $start, $destination )	{

	$api_key = mdjm_travel_get_api_key();
	$prefix  = 'https://maps.googleapis.com/maps/api/distancematrix/json';
	$mode    = 'driving';
	$units   = mdjm_get_option( 'travel_units' );

	$url = add_query_arg( array(
		'units'        => $units,
		'origins'      => str_replace( '%2C', ',', urlencode( $start ) ),
		'destinations' => str_replace( '%2C', ',', urlencode( $destination ) ),
		'mode'         => $mode,
		//'key'          => $api_key
		),
		$prefix
	);

	return apply_filters( 'mdjm_travel_build_url', $url );

} // mdjm_travel_build_url

/**
 * Retrieve the Google API key.
 *
 * @since	1.3.8
 * @param
 * @return	str			The API key.
 */
function mdjm_travel_get_api_key()	{
	return '617372114575-g846rsgcm715pkmhkokrho9c75ii3cne.apps.googleusercontent.com';
} // mdjm_travel_get_api_key

/**
 * Retrieves the travel starting point.
 *
 * @since	1.3.8
 * @param	int|obj		$event	The event ID or the event MDJM_Event class object.
 * @return	str
 */
function mdjm_travel_get_start( $event = '' )	{

	if ( ! empty( $event ) )	{
		if ( ! is_object( $event ) )	{
			$mdjm_event = new MDJM_Event( $event );
		} else	{
			$mdjm_event = $event;
		}
	}

	$employee = $mdjm_event->get_employee();

	if ( $employee )	{
		$address = mdjm_get_employee_address( $employee );
	} else	{
		$address = mdjm_get_option( 'travel_primary' );
	}

	$start = $address;

	if ( empty( $start ) )	{
		return;
	}

	if ( is_array( $start ) )	{
		$start = implode( ',', $start );
	}

	return apply_filters( 'mdjm_travel_get_start', $start );

} // mdjm_travel_get_start

/**
 * Retrieves the travel destination address.
 *
 * @since	1.3.8
 * @param	int|obj		$event	The event ID or the event MDJM_Event class object.
 * @return	str
 */
function mdjm_travel_get_destination( $event, $venue_id = '' )	{

	if ( ! is_object( $event ) )	{
		$mdjm_event = new MDJM_Event( $event );
	} else	{
		$mdjm_event = $event;
	}

	$venue = ! empty( $venue_id ) ? $venue_id : $mdjm_event->get_venue_id();

	$destination = mdjm_get_event_venue_meta( $venue, 'address' );

	if ( ! $destination )	{
		return;
	}

	if ( is_array( $destination ) )	{
		$destination = implode( ',', $destination );
	}

	return apply_filters( 'mdjm_travel_get_destination', $destination );

} // mdjm_travel_get_destination

/**
 * Returns the label for the selected measurement unit.
 *
 * @since	1.3.8
 * @param	bool	$singular	Whether to return a singular (true) or plural (false) value.
 * @param	bool	$lowercase	True to return a lowercase label, otherwise false.
 * @return	str
 */
function mdjm_travel_unit_label( $singular = false, $lowercase = true )	{
	$units = array(
		'singular' => array(
			'imperial' => 'Mile',
			'metric'   => 'Kilometer'
		),
		'plural'   => array(
			'imperial' => 'Miles',
			'metric'   => 'Kilometers'
		)
	);

	$type = 'singular';

	if ( ! $singular )	{
		$type = 'plural';
	}

	$return = $units[ $type ][ mdjm_get_option( 'travel_units', 'imperial' ) ];

	if ( $lowercase )	{
		$return = strtolower( $return );
	}

	return apply_filters( 'mdjm_travel_unit_label', $return );

} // mdjm_travel_unit_label

/**
 * Adds the travel data row to the venue details metabox on the event screen.
 *
 * @since	1.4
 * @param	int		$event_id	The event ID
 * @param	int		$venue_id	The venue ID
 * @return	void
 */
function mdjm_show_travel_data_row( $event_id = '', $venue_id = '' )	{

	if ( empty( $event_id ) && empty( $venue_id ) )	{
		return;
	}

	$travel_data    = mdjm_travel_get_distance( $event_id, $venue_id ); ?>

    <?php if ( ! empty( $travel_data ) ) : ?>
        <tr>
            <td><i class="fa fa-car" aria-hidden="true" title="<?php _e( 'Distance', 'mobile-dj-manager' ); ?>"></i>
                <?php echo mdjm_format_distance( $travel_data['distance'], false, true ); ?></td>
            <td><i class="fa fa-clock-o" aria-hidden="true" title="<?php _e( 'Travel Time', 'mobile-dj-manager' ); ?>"></i>
                <?php echo mdjm_seconds_to_time( $travel_data['duration'] ); ?></td>
            <td><i class="fa fa-money" aria-hidden="true" title="<?php _e( 'Cost', 'mobile-dj-manager' ); ?>"></i>
                <?php echo mdjm_currency_filter( mdjm_format_amount( mdjm_get_travel_cost( $travel_data['distance'] ) ) ); ?></td>
        </tr>
	<?php endif;

} // mdjm_show_travel_data_row
add_action( 'mdjm_venue_details_table_after_info', 'mdjm_show_travel_data_row', 10, 2 );
add_action( 'mdjm_venue_details_table_after_save', 'mdjm_show_travel_data_row', 10, 2 );
