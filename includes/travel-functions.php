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
	$mdjm_travel = new MDJM_Travel;
	$mdjm_travel->__set( 'distance', $distance );

	return $mdjm_travel->get_cost();
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
 * Retrieve the event travel fields.
 *
 * These fields are used to generate hidden fields on the event admin page
 * and store data relating to event travel.
 *
 * @since	1.4
 * @return	arr
 */
function mdjm_get_event_travel_fields()	{
	$travel_fields = array( 'cost', 'distance', 'time', 'directions_url' );

	/**
	 * Allow filtering of the travel fields for developers.
	 *
	 * @since	1.4
	 */
	return apply_filters( 'mdjm_event_travel_fields', $travel_fields );
} // mdjm_get_event_travel_fields

/**
 * Retrieve event travel data.
 *
 * @since	1.4
 * @param	int		$event_id	Event ID.
 * @param	str		$field		The travel field to retrieve.
 * @return	str
 */
function mdjm_get_event_travel_data( $event_id, $field = 'cost' )	{
	$travel_data = get_post_meta( $event_id, '_mdjm_event_travel_data', true );

	if ( $travel_data )	{
		if ( ! empty( $travel_data[ $field ] ) )	{
			return apply_filters( 'mdjm_event_travel_' . $field, $travel_data[ $field ], $event_id );
		}
	}

	return false;
} // mdjm_get_event_travel_fields

/**
 * Adds the travel data row to the venue details metabox on the event screen.
 *
 * @since	1.4
 * @param	int|arr|obj	$dest			An address array, event ID, event object or venue ID.
 * @param	int			$employee_id	An employee user ID.
 * @return	void
 */
function mdjm_show_travel_data_row( $dest, $employee_id = '' )	{

	$mdjm_travel = new MDJM_Travel;

	if ( ! empty( $employee_id ) )	{
		$mdjm_travel->__set( 'start_address', $mdjm_travel->get_employee_address( $employee_id ) );
	}

	$mdjm_travel->set_destination( $dest );

	if ( empty( $employee_id ) )	{
		if ( is_object( $dest ) )	{
			$mdjm_travel->__set( 'start_address', $mdjm_travel->get_employee_address( $dest->employee_id ) );
		} elseif ( is_numeric( $dest ) )	{
			if ( 'mdjm-event' == get_post_type( $dest ) )	{
				$mdjm_travel->__set( 'start_address', $mdjm_travel->get_employee_address( mdjm_get_event_primary_employee_id( $dest ) ) );
			}
		}
	}

	$mdjm_travel->get_travel_data();
	$distance       = '';
	$duration       = '';
	$cost           = '';
	$directions_url = '';
	$directions     = $mdjm_travel->get_directions_url();
	$class          = 'mdjm-hidden';

    if ( ! empty( $mdjm_travel->data ) )	{
		$distance       = mdjm_format_distance( $mdjm_travel->data['distance'], false, true );
		$duration       = mdjm_seconds_to_time( $mdjm_travel->data['duration'] );
		$cost           = mdjm_currency_filter( mdjm_format_amount( $mdjm_travel->get_cost() ) );
		$directions_url = $directions ? $directions : '';
		$class          = '';
	}

	ob_start(); ?>
	<tr id="mdjm-travel-data" class="<?php echo $class; ?>">
		<td><i class="fa fa-car" aria-hidden="true" title="<?php _e( 'Distance', 'mobile-dj-manager' ); ?>"></i>
			<span class="mdjm-travel-distance"><?php echo $distance; ?></span></td>
		<td><i class="fa fa-clock-o" aria-hidden="true" title="<?php _e( 'Travel Time', 'mobile-dj-manager' ); ?>"></i>
			<span class="mdjm-travel-time"><?php echo $duration; ?></span></td>
		<td><i class="fa fa-money" aria-hidden="true" title="<?php _e( 'Cost', 'mobile-dj-manager' ); ?>"></i>
			<span class="mdjm-travel-cost"><?php echo $cost; ?></span></td>
	</tr>

    <tr id="mdjm-travel-directions" class="<?php echo $class; ?>">
        <td colspan="3"><i class="fa fa-map-signs" aria-hidden="true" title="<?php _e( 'Directions', 'mobile-dj-manager' ); ?>"></i>
        <span class="mdjm-travel-directions"><a id="travel_directions" href="<?php echo $directions_url; ?>" target="_blank"><?php _e( 'Directions', 'mobile-dj-manager' ); ?></a></span></td>
    </tr>

	<?php $travel_data_row = ob_get_contents();
	ob_end_clean();

	echo $travel_data_row;

} // mdjm_show_travel_data_row
add_action( 'mdjm_after_venue_notes', 'mdjm_show_travel_data_row', 10, 2         );
add_action( 'mdjm_venue_details_travel_data', 'mdjm_show_travel_data_row', 10, 2 );
