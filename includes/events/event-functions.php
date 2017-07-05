<?php
/**
 * Contains all event related functions
 *
 * @package		MDJM
 * @subpackage	Events
 * @since		1.3
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) )
	exit;

/**
 * Retrieve an event.
 *
 * @since	1.3
 * @param	int		$event_id	The event ID.
 * @return	obj		$event		The event WP_Post object
 */
function mdjm_get_event( $event_id )	{
	return mdjm_get_event_by_id( $event_id );
} // mdjm_get_event

/**
 * Retrieve an event by ID.
 *
 * @param	int		$event_id	The WP post ID for the event.
 * @return	mixed	$event		WP_Query object or false.
 */
function mdjm_get_event_by_id( $event_id )	{
	$event = new MDJM_Event( $event_id );
	
	return ( !empty( $event->ID ) ? $event : false );
} // mdjm_get_event_by_id

/**
 * Retrieve an event by date.
 *
 * @since	1.4
 * @param	str		$date		The date to query (Y-m-d).
 * @return	array	$events		Array of event WP_Query objects or false.
 */
function mdjm_get_events_by_date( $date )	{
	$args['meta_key']   = '_mdjm_event_date';
	$args['meta_value'] = $date;

	$events = mdjm_get_events( $args );

	if ( $events )	{
		return $events;
	}

	return false;
} // mdjm_get_events_by_date

/**
 * Retrieve the events.
 *
 * @since	1.3
 * @param	arr		$args			Array of possible arguments. See $defaults.
 * @return	mixed	$events			False if no events, otherwise an object array of all events.
 */
function mdjm_get_events( $args = array() )	{
		
	$defaults = array(
		'post_type'         => 'mdjm-event',
		'post_status'       => 'any',
		'posts_per_page'	=> -1,
	);
		
	$args = wp_parse_args( $args, $defaults );
		
	$events = get_posts( $args );
	
	// Return the results
	if ( $events )	{
		return $events;
	} else	{
		return false;
	}
	
} // mdjm_get_events

/**
 * Count Events
 *
 * Returns the total number of events.
 *
 * @since	1.4
 * @param	arr	$args	List of arguments to base the event count on
 * @return	arr	$count	Number of events sorted by event date
 */
function mdjm_count_events( $args = array() ) {

	global $wpdb;

	$defaults = array(
		'status'     => null, //array_keys( mdjm_all_event_status() ),
		'employee'   => null,
		'client'     => null,
		's'          => null,
		'start-date' => null, // This is the post date aka Enquiry received date
		'end-date'   => null,
		'date'       => null, // This is an event date or an array of dates if we want to search between
		'type'       => null
	);

	$args = wp_parse_args( $args, $defaults );

	$select = "SELECT p.post_status,count( * ) AS num_posts";
	$join = '';
	$where = "WHERE p.post_type = 'mdjm-event'";

	// Count events with a specific status or statuses
	if ( ! empty( $args['status'] ) )	{
		if ( is_array( $args['status'] ) )	{
			$clause = "IN ( " . implode( ', ', $args['status'] ) . " )";
		} else	{
			$clause = "= '{$args['status']}'";
		}

		$where .= " AND p.post_status " . $clause;
	}

	// Count events for a specific employee
	if ( ! empty( $args['employee'] ) ) {

		$join = "LEFT JOIN $wpdb->postmeta m ON (p.ID = m.post_id)";

		$where .= "
				AND m.meta_key = '_mdjm_event_dj'
				AND m.meta_value = '{$args['employee']}'
				OR m.meta_key = '_mdjm_event_employees'
				AND m.meta_value LIKE '%:\"{$args['employee']}\";%'";

	// Count events for a specific client
	} elseif ( ! empty( $args['client'] ) ) {

		$join = "LEFT JOIN $wpdb->postmeta m ON (p.ID = m.post_id)";
		$where .= "
			AND m.meta_key = '_mdjm_event_client'
			AND m.meta_value = '{$args['client']}'";

	// Count event for a search
	} elseif( ! empty( $args['s'] ) ) {

		if ( is_email( $args['s'] ) || strlen( $args['s'] ) == 32 ) {

			if( is_email( $args['s'] ) )	{
				$field = '_mdjm_event_client';
			}

			$join = "LEFT JOIN $wpdb->postmeta m ON (p.ID = m.post_id)";
			$where .= $wpdb->prepare( "
				AND m.meta_key = %s
				AND m.meta_value = %s",
				$field,
				$args['s']
			);

		} elseif ( is_numeric( $args['s'] ) ) {

			$join = "LEFT JOIN $wpdb->postmeta m ON (p.ID = m.post_id)";
			$where .= $wpdb->prepare( "
				AND m.meta_key = '_mdjm_event_client'
				AND m.meta_value = %d",
				$args['s']
			);

		} else {
			$search = $wpdb->esc_like( $args['s'] );
			$search = '%' . $search . '%';

			$where .= $wpdb->prepare( "AND ((p.post_title LIKE %s) OR (p.post_content LIKE %s))", $search, $search );
		}

	}

	// Limit event count by received date
	if ( ! empty( $args['start-date'] ) && false !== strpos( $args['start-date'], '-' ) ) {

		$date_parts = explode( '-', $args['start-date'] );
		$year       = ! empty( $date_parts[0] ) && is_numeric( $date_parts[0] ) ? $date_parts[0] : 0;
		$month      = ! empty( $date_parts[1] ) && is_numeric( $date_parts[1] ) ? $date_parts[1] : 0;
		$day        = ! empty( $date_parts[2] ) && is_numeric( $date_parts[2] ) ? $date_parts[2] : 0;

		$is_date    = checkdate( $month, $day, $year );
		if ( false !== $is_date ) {

			$date   = new DateTime( $args['start-date'] );
			$where .= $wpdb->prepare( " AND p.post_date >= '%s'", $date->format( 'Y-m-d' ) );

		}

		// Fixes an issue with the events list table counts when no end date is specified (partly with stats class)
		if ( empty( $args['end-date'] ) ) {
			$args['end-date'] = $args['start-date'];
		}

	}

	if ( ! empty ( $args['end-date'] ) && false !== strpos( $args['end-date'], '-' ) ) {

		$date_parts = explode( '-', $args['end-date'] );
		$year       = ! empty( $date_parts[0] ) && is_numeric( $date_parts[0] ) ? $date_parts[0] : 0;
		$month      = ! empty( $date_parts[1] ) && is_numeric( $date_parts[1] ) ? $date_parts[1] : 0;
		$day        = ! empty( $date_parts[2] ) && is_numeric( $date_parts[2] ) ? $date_parts[2] : 0;

		$is_date    = checkdate( $month, $day, $year );
		if ( false !== $is_date ) {

			$date   = new DateTime( $args['end-date'] );
			$where .= $wpdb->prepare( " AND p.post_date <= '%s'", $date->format( 'Y-m-d' ) );

		}

	}

	if ( ! empty( $args['date'] ) )	{

		$join = "LEFT JOIN $wpdb->postmeta m ON (p.ID = m.post_id)";

		if ( is_array( $args['date'] ) )	{
			$start_date = new DateTime( $args['date'][0] );
			$end_date   = new DateTime( $args['date'][1] );

			$where .= "
				AND m.meta_key = '_mdjm_event_date'
				AND STR_TO_DATE(m.meta_value, '%Y-%m-%d' )
					BETWEEN '" . $start_date->format( 'Y-m-d' ) . "'
					AND '" . $end_date->format( 'Y-m-d' ) . "'";

		} else	{

			$date = new DateTime( $args['date'] );
			$where .= $wpdb->prepare( "
				AND m.meta_key = '_mdjm_event_date'
				AND m.meta_value = '%s'",
				$date->format( 'Y-m-d' )
			);

		}

	}

	$where = apply_filters( 'mdjm_count_events_where', $where );
	$join  = apply_filters( 'mdjm_count_events_where', $join );

	$query = "$select
		FROM $wpdb->posts p
		$join
		$where
		GROUP BY p.post_status
	";

	$cache_key = md5( $query );

	$count = wp_cache_get( $cache_key, 'counts' );

	if ( false !== $count ) {
		return $count;
	}

	$count = $wpdb->get_results( $query, ARRAY_A );
	$stats    = array();
	$total    = 0;
	$statuses = mdjm_all_event_status();

	foreach ( array_keys( $statuses ) as $state ) {
		$stats[ $state ] = 0;
	}

	foreach ( (array) $count as $row ) {
		if ( ! array_key_exists( $row['post_status'], mdjm_all_event_status() ) )	{
			continue;
		}
		$stats[ $row['post_status'] ] = $row['num_posts'];
	}

	$stats = (object) $stats;
	wp_cache_set( $cache_key, $stats, 'counts' );

	return $stats;
} // mdjm_count_events

/**
 * Retrieve the total event count.
 *
 * @since	1.4
 * @param	str|arr	$status			Post statuses.
 * @return	int		Event count
 */
function mdjm_event_count( $status = 'any' )	{
	$args = array(
		'post_type'      => 'mdjm-event',
		'post_status'    => $status,
		'posts_per_page' => -1
	);

	$args = apply_filters( 'mdjm_event_count_args', $args );

	$events = new WP_Query( $args );

	return $events->found_posts;
} // mdjm_event_count

/**
 * Retrieve the event data.
 *
 * @since	1.4
 * @param	int|obj		$event	An event ID, or an MDJM_Event object.
 * @return	arr			Event meta.
 */
function mdjm_get_event_data( $event )	{

	if ( is_numeric( $event ) )	{
		$mdjm_event = new MDJM_Event( $event );
	} else	{
		$mdjm_event = $event;
	}

	$contract_status = $mdjm_event->get_contract_status();
	$source = mdjm_get_enquiry_source( $mdjm_event->ID );

	$event_data = array(
		'client'              => $mdjm_event->client,
		'contract'            => $mdjm_event->get_contract(),
		'contract_status'     => $contract_status ? __( 'Signed', 'mobile-dj-manager' ) : __( 'Unsigned', 'mobile-dj-manager' ),
		'cost'                => array(
			'balance'             => $mdjm_event->get_balance(),
			'balance_status'      => $mdjm_event->get_balance_status(),
			'deposit'             => $mdjm_event->deposit,
			'deposit_status'      => $mdjm_event->get_deposit_status(),
			'remaining_deposit'   => $mdjm_event->get_remaining_deposit(),
			'cost'                => $mdjm_event->price
		),
		'date'                => $mdjm_event->date,
		'duration'            => mdjm_event_duration( $mdjm_event->ID ),
		'employees'           => array(
			'employees'           => $mdjm_event->get_all_employees(),
			'primary_employee'    => $mdjm_event->employee_id
		),
		'end_date'            => $mdjm_event->get_meta( '_mdjm_event_end_date' ),
		'end_time'            => $mdjm_event->get_finish_time(),
		'equipment'           => array(
			'package'             => mdjm_get_package_name( mdjm_get_event_package( $mdjm_event->ID ) ),
			'addons'              => mdjm_get_event_addons( $mdjm_event->ID )
		),
		'name'                => $mdjm_event->get_name(),
		'playlist'            => array(
			'playlist_enabled'    => $mdjm_event->playlist_is_enabled(),
			'playlist_guest_code' => $mdjm_event->get_playlist_code(),
			'playlist_status'     => $mdjm_event->playlist_is_open()
		),
		'setup_date'          => $mdjm_event->get_setup_date(),
		'setup_time'          => $mdjm_event->get_setup_time(),
		'source'              => ! empty( $source ) ? $source->name : '',
		'status'              => $mdjm_event->get_status(),
		'start_time'          => $mdjm_event->get_start_time(),
		'type'                => $mdjm_event->get_type(),
		'venue'               => array(
			'id'                  => $mdjm_event->get_meta( '_mdjm_event_venue_id' ),
			'name'                => mdjm_get_event_venue_meta( $mdjm_event->ID, 'name' ),
			'address'             => mdjm_get_event_venue_meta( $mdjm_event->ID, 'address' ),
			'contact'             => mdjm_get_event_venue_meta( $mdjm_event->ID, 'contact' ),
			'details'             => mdjm_get_venue_details( $mdjm_event->get_venue_id() ),
			'email'               => mdjm_get_event_venue_meta( $mdjm_event->ID, 'email' ),
			'phone'               => mdjm_get_event_venue_meta( $mdjm_event->ID, 'phone' ),
			'notes'               => mdjm_get_event_venue_meta( $mdjm_event->ID, 'notes' )
		)
	);

	$employees = $mdjm_event->get_all_employees();

	if ( ! empty( $employees ) )	{
		$event_data['employees']['employees'] = $employees;
	}

	$event_data = apply_filters( 'mdjm_get_event_data', $event_data, $mdjm_event->ID );

	return $event_data;

} // mdjm_get_event_data

/**
 * Whether or not the event is currently active.
 *
 * @since	1.3
 * @param	int		$event_id	Event ID.
 * @return	bool	True if active, false if not.
 */
function mdjm_event_is_active( $event_id = '' )	{

	$event_statuses   = mdjm_active_event_statuses();
	$event_statuses[] = 'mdjm-unattended';
	$event_statuses[] = 'auto-draft';
	$event_statuses[] = 'draft';

	if ( in_array( get_post_status( $event_id ), $event_statuses ) )	{
		return true;	
	}

	return false;

} // mdjm_event_is_active

/**
 * Retrieve the next event.
 * If the current user is not an MDJM admin, only list their own event.
 *
 * @since	1.3
 * @param	int		$employee_id	User ID of employee. Leave empty to check for all employees.
 * @return	obj		Events WP_Post object.
 */
function mdjm_get_next_event( $employee_id = '' )	{
	
	if ( ! empty( $employee_id ) && ! mdjm_employee_can( 'manage_all_events' ) && $employee_id != get_current_user_id() )	{
		wp_die(
			'<h1>' . __( 'Cheatin&#8217; uh?', 'mobile-dj-manager' ) . '</h1>' .
			'<p>' . sprintf( __( 'Your %s permissions do not permit you to search all %s!', 'mobile-dj-manager' ), mdjm_get_label_singular( true ), mdjm_get_label_plural( true ) ) . '</p>',
			403
		);
	}
	
	if ( ! empty( $employee_id ) || ! mdjm_employee_can( 'manage_all_events' ) )	{
	
		$employee_id  = ! empty( $employee_id ) ? $employee_id : get_current_user_id();
		$event		= mdjm_get_employees_next_event( $employee_id );
		
	} else	{
		
		$args = array(
			'post_status'	  => mdjm_active_event_statuses(),
			'posts_per_page'   => 1,
			'meta_key'		 => '_mdjm_event_date',
			'orderby'		  => 'meta_value',
			'order' 			=> 'ASC',
		);
		
		$event = mdjm_get_events( $args );
		
		if ( ! empty( $event ) )	{
			$event = $event[0];
		}
		
	}
	
	if ( empty( $event ) )	{
		return false;
	}
	
	return $event;
	
} // mdjm_get_next_event

/**
 * Retrieve today's events.
 *
 * @since	1.3
 * @param	int		$employee_id	User ID of employee. Leave empty to check for all employees.
 * @return	obj		Events WP_Post object.
 */
function mdjm_get_todays_events( $employee_id = '' )	{
	
	$employee_id = ! empty( $employee_id ) ? $employee_id : get_current_user_id();
	
	$args = array(
		'post_status'	  => mdjm_active_event_statuses(),
		'posts_per_page'   => 1,
		'meta_key'		 => '_mdjm_event_date',
		'orderby'		  => 'meta_value',
		'order' 			=> 'DESC',
	);
	
	$event = mdjm_get_employee_events( $employee_id, $args );
	
	if ( empty( $event ) )	{
		return false;
	}
	
	return $event[0];
	
} // mdjm_get_todays_events

/**
 * Retrieve an event by the guest playlist code.
 *
 * @since	1.3
 * @param	int		$access_code	The access code for the event playlist.
 * @return	obj		$event_query	WP_Query object.
 */
function mdjm_get_event_by_playlist_code( $access_code )	{
	global $wpdb;
	
	$query = "SELECT `post_id`
			  AS `event_id` 
			  FROM `$wpdb->postmeta` 
			  WHERE `meta_value` = '$access_code' 
			  LIMIT 1";
					
	$result = $wpdb->get_row( $query );
	
	return ( $result ? mdjm_get_event( $result->event_id ) : false );
} // mdjm_get_event_by_playlist_code

/**
 * Retrieve events by status.
 *
 * @since	1.3
 * @param	str			$status		The event status.
 * @return	obj|bool	The WP_Query results object.
 */
function mdjm_get_events_by_status( $status )	{
	
	$events = mdjm_get_events( array( 'post_status' => $status ) );
	
	if ( ! $events )	{
		return false;
	}
	
	return $events;
	
} // mdjm_get_events_by_status

/**
 * Retrieve a count of events by status.
 *
 * @since	1.3
 * @param	str			$status		The event status.
 * @return	int			The number of events with the status.
 */
function mdjm_count_events_by_status( $status )	{
	
	$count = 0;
	
	$events = mdjm_get_events_by_status( $status );
	
	if ( $events )	{
		$count = count( $events );
	}
	
	return $count;
	
} // mdjm_count_events_by_status

/**
 * Determine if the event exists.
 *
 * @since	1.3
 * @param	int			$event_id		The event ID.
 * @return	obj|bool	The WP_Post object for the event if it exists, otherwise false.
 */
function mdjm_event_exists( $event_id )	{
	return mdjm_get_event_by_id( $event_id );
} // mdjm_event_exists

/**
 * Returns an array of event post status keys.
 *
 * @since	1.4.6
 * @param
 * @return	arr		Array of event status keys
 */
function mdjm_all_event_status_keys()	{
	$post_status = array(
		'mdjm-unattended',
		'mdjm-enquiry',
		'mdjm-approved',
		'mdjm-contract',
		'mdjm-completed',
		'mdjm-cancelled',
		'mdjm-rejected',
		'mdjm-failed'
	);

	return apply_filters( 'mdjm_all_event_status', $post_status );
} // mdjm_all_event_status_keys

/**
 * Returns an array of event post status.
 *
 * @since	1.3
 * @param
 * @return	arr		Array of event status'. Key = post_status value = MDJM Event status
 */
function mdjm_all_event_status()	{
	$post_status = mdjm_all_event_status_keys();

	foreach( $post_status as $status )	{
		$mdjm_status[ $status ] = get_post_status_object( $status )->label;
	}

	// Sort alphabetically
	asort( $mdjm_status );

	return $mdjm_status;
} // mdjm_all_event_status

/**
 * Returns an array of active event post statuses.
 *
 * @since	1.3
 * @param
 * @return	arr		Array of active event status'.
 */
function mdjm_active_event_statuses()	{
	$statuses = mdjm_all_event_status_keys();
	$inactive = mdjm_inactive_event_status_keys();

	foreach( $inactive as $status )	{
		if ( in_array( $status, $statuses ) )	{
			unset( $statuses[ $status ] );
		}
	}

	// Sort alphabetically
	asort( $statuses );

	return $statuses;
} // mdjm_active_event_statuses

/**
 * Returns an array of inactive event post status keys.
 *
 * @since	1.4.6
 * @param
 * @return	arr		Array of event status keys
 */
function mdjm_inactive_event_status_keys()	{
	$post_status = array(
		'mdjm-completed',
		'mdjm-cancelled',
		'mdjm-rejected',
		'mdjm-failed'
	);

	return apply_filters( 'mdjm_inactive_event_status', $post_status );
} // mdjm_inactive_event_status_keys

/**
 * Return the event status label for given event ID.
 *
 * @since	1.3
 * @param	int		$event_id	Optional: ID of the current event. If not set, check for global $post and $post_id.
 * @return	str		Label for current event status.
 */
function mdjm_get_event_status( $event_id='' )	{
	global $post, $post_id;
	
	if ( ! empty( $event_id ) )	{
		$id = $event_id;
	} elseif ( ! empty( $post_id ) )	{
		$id = $post_id;
	} elseif ( ! empty( $post ) )	{
		$id = $post->ID;
	} else	{
		$id = '';
	}
	
	$event = new MDJM_Event( $id );
	
	// Return the label for the status
	return $event->get_status();
} // mdjm_get_event_status

/**
 * Returns the event name.
 *
 * @since	1.4.7.3
 * @param	int		$event_id	ID of the event.
 * @return	str		Name for current event.
 */
function mdjm_get_event_name( $event_id = 0 )   {
    if ( empty( $event_id ) )   {
        return;
    }

    $name = get_post_meta( $event_id, '_mdjm_event_name', true );
		
    /**
     * Override the event name.
     *
     * @since	1.3
     *
     * @param	str		$name The event name.
     */
    return apply_filters( 'mdjm_event_name', $name, $event_id );
} // mdjm_get_event_name

/**
 * Return a select list of possible event statuses
 * 
 *	@since	1.1.3
 *	@param	arr		$args	array of options. See $defaults
 *	@return	str		HTML for the select list
 */
function mdjm_event_status_dropdown( $args='' )	{
	global $post;
	
	$defaults = array(
		'name'					=> 'mdjm_event_status',
		'id'					=> 'mdjm_event_status',
		'selected'				=> ! empty( $post ) ? $post->post_status : 'mdjm-unattended',
		'first_entry'			=> '',
		'first_entry_value'		=> '0',
		'small'					=> false,
		'return_type'			=> 'list'
	);
	
	$args = wp_parse_args( $args, $defaults );
	
	$event_status = mdjm_all_event_status();
	
	if( empty( $event_status ) )	{		
		return false;
	}
	
	if( ! empty( $post->ID ) && array_key_exists( $post->post_status, $event_status ) )	{
		$current_status = $post->post_status;
	}
					
	$output = '<select name="' . $args['name'] . '" id="' . $args['id'] . '"';
	$output .= ( !empty( $args['small'] ) ? ' style="font-size: 11px;"' : '' );
	$output .= '>' . "\r\n";
	
	if( !empty( $first_entry ) )	{
		$output .= '<option value="' . $args['first_entry_value'] . '">' . $args['first_entry'] . '</option>' . "\r\n";
	}
	
	foreach( $event_status as $slug => $label )	{
		$output .= '<option value="' . $slug . '"';
		$output .= $args['selected'] == $slug ? ' selected="selected"' : '';
		$output .= '>' . $label . '</option>' . "\r\n";	
	}
	
	$output .= '</select>' . "\r\n";
	
	if( $args['return_type'] == 'list' )	{
		echo $output;
	}

	return $output;
} // mdjm_event_status_dropdown

/**
 * Retrieve the enquiry source for the event.
 *
 * @since	1.3
 * @param	int			$event_id	Event ID.
 * @return	obj|bool	The enquiry source for the event, or false if not set
 */
function mdjm_get_enquiry_source( $event_id )	{

	$enquiry_source = wp_get_object_terms( $event_id, 'enquiry-source' );
	$return         = (bool) false;

	if ( isset( $enquiry_source[0]->term_id ) )	{
		$return = $enquiry_source[0];
	}
	
	return $return;

} // mdjm_get_enquiry_source

/**
 * Return all enquiry sources.
 *
 * @since	1.3
 * @param	arr		$args	See $defaults.
 * @return	obj		Object array of all enqury source categories.
 */
function mdjm_get_enquiry_sources( $args = array() )	{
	
	$defaults = array(
		'taxonomy'      => 'enquiry-source',
		'hide_empty'    => false,
		'orderby'       => 'name',
		'order'         => 'ASC'
	);
	
	$args = wp_parse_args( $args, $defaults );
	
	$enquiry_sources = get_categories( $args );
	
	return apply_filters( 'mdjm_get_enquiry_sources', $enquiry_sources, $args );
	
} // mdjm_get_enquiry_sources

/**
 * Set the enquiry source for the event.
 *
 * @since	1.3
 * @param	int			$event_id	Event ID.
 * @param	int|arr		$type		The term ID of the category to set for the event.
 * @return	bool		True on success, or false.
 */
function mdjm_set_enquiry_source( $event_id, $type = '' )	{
	
	if ( empty( $type ) && mdjm_get_option( 'enquiry_source_default' ) )	{
		$type = mdjm_get_option( 'enquiry_source_default' );
	}
	
	if ( ! is_array( $type ) )	{
		$type = array( $type );
	}
	
	$type = array_map( 'intval', $type );
	$type = array_unique( $type );
	
	(int)$event_id;
	
	$set_enquiry_source = wp_set_object_terms( $event_id, $type, 'enquiry-source', false );
	
	if( is_wp_error( $set_enquiry_source ) )	{
		MDJM()->debug->log_it( sprintf( 'Unable to assign term ID %d to Event %d: %s', $type, $event_id, $set_enquiry_source->get_error_message() ), true );
	}
	
	return;

} // mdjm_set_enquiry_source

/**
 * Generate a dropdown list of enquiry sources.
 *
 * @since	1.3
 * @param	arr		$args	See $defaults.
 * @return	str		HTML output for the dropdown list.
 */
function mdjm_enquiry_sources_dropdown( $args )	{
	
	$defaults = array(
		'show_option_none'   => '',
		'option_none_value'  => '',
		'orderby'            => 'name', 
		'order'              => 'ASC',
		'hide_empty'         => false, 
		'echo'               => true,
		'selected'           => 0,
		'name'               => 'mdjm_enquiry_source',
		'id'                 => '',
		'class'              => 'postform',
		'taxonomy'           => 'event-types',
		'required'           => false
	);
	
	$args = wp_parse_args( $args, $defaults );
	
	$args['id']                = ! empty( $args['id'] )                ? $args['id']                : $args['name'];
	$args['required']          = ! empty( $args['required'] )          ? ' required'                : '';
	$args['class']             = ! empty( $args['class'] )             ? $args['class']             : '';
	
	$enquiry_sources = mdjm_get_enquiry_sources();
	
	$output = sprintf( '<select name="%s" id="%s" class="%s"%s>', $args['name'], $args['id'], $args['class'], $args['required'] );
	
	if ( ! empty( $args['show_option_none'] ) )	{
		$output .= sprintf( '<option value="%s">%s</option>', $args['option_none_value'], $args['show_option_none'] );
	}
	
	if ( empty( $enquiry_sources ) )	{
		$output .= sprintf( '<option value="" disabled="disabled">%s</option>', apply_filters( 'mdjm_no_enquiry_source_options', __( 'No sources found', 'mobile-dj-manager' ) ) );
	} else	{
	
		foreach( $enquiry_sources as $enquiry_source )	{
			$selected = selected( $enquiry_source->term_id, $args['selected'], false );
			
			$output .= sprintf( '<option value="%s"%s>%s</option>', $enquiry_source->term_id, $selected, esc_attr( $enquiry_source->name ) ) . "\n";
			
		}
		
	}
	
	$output .= '</select>';
	
	if ( ! empty( $args['echo'] ) )	{
		echo $output;
	} else	{
		return $output;
	}
	
} // mdjm_enquiry_sources_dropdown

/**
 * Return all event types.
 *
 * @since	1.3
 * @param	arr		$args	See $defaults.
 * @return	obj		Object array of all event type categories.
 */
function mdjm_get_event_types( $args = array() )	{
	
	$defaults = array(
		'taxonomy'      => 'event-types',
		'hide_empty'    => false,
		'orderby'       => 'name',
		'order'         => 'ASC'
	);
	
	$args = wp_parse_args( $args, $defaults );
	
	$event_types = get_categories( $args );
	
	return apply_filters( 'mdjm_get_event_types', $event_types, $args );
	
} // mdjm_get_event_types

/**
 * Generate a dropdown list of event types.
 *
 * @since	1.3
 * @param	arr		$args	See $defaults.
 * @return	str		HTML output for the dropdown list.
 */
function mdjm_event_types_dropdown( $args )	{
	
	$defaults = array(
		'show_option_none'   => '',
		'option_none_value'  => '',
		'orderby'            => 'name', 
		'order'              => 'ASC',
		'hide_empty'         => false, 
		'echo'               => true,
		'selected'           => 0,
		'name'               => 'mdjm_event_type',
		'id'                 => '',
		'class'              => 'postform',
		'taxonomy'           => 'event-types',
		'required'           => false
	);
	
	$args = wp_parse_args( $args, $defaults );
	
	$args['id']                = ! empty( $args['id'] )                ? $args['id']                : $args['name'];
	$args['required']          = ! empty( $args['required'] )          ? ' required'                : '';
	$args['class']             = ! empty( $args['class'] )             ? $args['class']             : '';
	
	$types = mdjm_get_event_types();
	
	$output = sprintf( '<select name="%s" id="%s" class="%s"%s>', $args['name'], $args['id'], $args['class'], $args['required'] );
	
	if ( ! empty( $args['show_option_none'] ) )	{
		$output .= sprintf( '<option value="%s">%s</option>', $args['option_none_value'], $args['show_option_none'] );
	}
	
	if ( empty( $types ) )	{
		$output .= sprintf( '<option value="" disabled="disabled">%s</option>', apply_filters( 'mdjm_no_event_type_options', __( 'No options found', 'mobile-dj-manager' ) ) );
	} else	{
	
		foreach( $types as $type )	{
			$selected = selected( $type->term_id, $args['selected'], false );
			
			$output .= sprintf( '<option value="%s"%s>%s</option>', $type->term_id, $selected, esc_attr( $type->name ) ) . "\n";
			
		}
		
	}
	
	$output .= '</select>';
	
	if ( ! empty( $args['echo'] ) )	{
		echo $output;
	} else	{
		return $output;
	}
	
} // mdjm_event_types_dropdown

/**
 * Set the event type for the event.
 *
 * @since	1.3
 * @param	int			$event_id	Event ID.
 * @param	int|arr		$type		The term ID of the category to set for the event.
 * @return	bool		True on success, or false.
 */
function mdjm_set_event_type( $event_id, $type = '' )	{
	
	if ( empty( $type ) && mdjm_get_option( 'event_type_default' ) )	{
		$type = mdjm_get_option( 'event_type_default' );
	}
	
	if ( ! is_array( $type ) )	{
		$type = array( $type );
	}
	
	$type = array_map( 'intval', $type );
	$type = array_unique( $type );
	
	(int)$event_id;
	
	$set_event_terms = wp_set_object_terms( $event_id, $type, 'event-types', false );
	
	if( is_wp_error( $set_event_terms ) )	{
		MDJM()->debug->log_it( sprintf( 'Unable to assign term ID %d to Event %d: %s', $type, $event_id, $set_event_terms->get_error_message() ), true );
	}
	
	return;

} // mdjm_set_event_type

/**
 * Return the event type label for given event ID.
 *
 * @since	1.3
 * @param	int		$event_id	ID of the current event. If not set, check for global $post and $post_id.
 * @param	bool	$raw		True to return the raw slug of the event type, false for the label
 * @return	str		Label for current event type.
 */
function mdjm_get_event_type( $event_id='', $raw = false )	{
	
	global $post, $post_id;
	
	if( ! empty( $event_id ) )	{
		$id = $event_id;
	} elseif( ! empty( $post_id ) )	{
		$id = $post_id;
	} elseif( ! empty( $post ) )	{
		$id = $post->ID;
	} else	{
		$id = '';
	}

	if ( $raw )	{
		return mdjm_get_event_type_raw( $id );
	}

	$event = new MDJM_Event( $id );
	
	// Return the label for the status
	return $event->get_type();

} // mdjm_get_event_type

/**
 * Return the event type slug for given event ID.
 *
 * @since	1.3
 * @param	int		$event_id	ID of the current event. If not set, check for global $post and $post_id.
 * @return	str		Slug for current event type.
 */
function mdjm_get_event_type_raw( $event_id )	{
	$event_type =  wp_get_object_terms( $event_id, 'event-types' );
	
	if ( $event_type )	{
		return absint( $event_type[0]->term_id );
	}

	return false;
} // mdjm_get_event_type_raw

/**
 * Returns the contract ID for the event.
 *
 * @since	1.3
 * @param	int		$event_id	The event ID.
 * @return	str					The contract ID for the event.
 */
function mdjm_get_event_contract_id( $event_id )	{
	
	if( empty( $event_id ) )	{
		return false;
	}

	return mdjm_get_option( 'event_prefix', '' ) . $event_id;

} // mdjm_get_event_contract_id

/**
 * Returns the date for an event in short format.
 *
 * @since	1.3
 * @param	int		$event_id	The event ID.
 * @return	str					The date of the event.
 */
function mdjm_get_event_date( $event_id )	{
	
	if( empty( $event_id ) )	{
		return false;
	}

	$event = new MDJM_Event( $event_id );

	return mdjm_format_short_date( $event->get_date() );

} // mdjm_get_event_date

/**
 * Returns the date for an event in long format.
 *
 * @since	1.3
 * @param	int		$event_id	The event ID.
 * @return	str					The date of the event.
 */
function mdjm_get_event_long_date( $event_id )	{
	
	if( empty( $event_id ) )	{
		return false;
	}

	$event = new MDJM_Event( $event_id );
	
	return $event->get_long_date();

} // mdjm_get_event_long_date

/**
 * Returns the start time for an event.
 *
 * @since	1.3
 * @param	int		$event_id	The event ID.
 * @return	str					The date of the event.
 */
function mdjm_get_event_start( $event_id )	{
	
	$time = get_post_meta( $event_id, '_mdjm_event_start', true );
	
	return date( mdjm_get_option( 'time_format' ), strtotime( $time ) );

} // mdjm_get_event_start

/**
 * Returns the price for an event.
 *
 * @since	1.3
 * @param	int		$event_id	The event ID.
 * @return	int|str				The price of the event.
 */
function mdjm_get_event_price( $event_id = 0 )	{
	if( empty( $event_id ) )	{
		return false;
	}

	$event = new MDJM_Event( $event_id );
	return $event->get_price();
} // mdjm_get_event_price

/**
 * Returns the deposit type.
 *
 * @since	1.3
 * @param	int		$event_id	The event ID.
 * @return	int|str				The deposit type.
 */
function mdjm_get_event_deposit_type()	{
	return mdjm_get_option( 'deposit_type', 'fixed' );
} // mdjm_get_event_deposit_type

/**
 * Returns the deposit price for an event.
 *
 * @since	1.3
 * @param	int		$event_id	The event ID.
 * @return	int|str				The deposit price of the event.
 */
function mdjm_get_event_deposit( $event_id = 0 )	{
	if( empty( $event_id ) )	{
		
		return false;
	}

	$event = new MDJM_Event( $event_id );
	return $event->get_deposit();
} // mdjm_get_event_deposit

/**
 * Returns the deposit status for an event.
 *
 * @since	1.3
 * @param	int		$event_id	The event ID.
 * @return	str					The deposit status of the event.
 */
function mdjm_get_event_deposit_status( $event_id )	{
	if( empty( $event_id ) )	{
		return false;
	}

	$event = new MDJM_Event( $event_id );
	return $event->get_deposit_status();
} // mdjm_get_event_deposit_status

/**
 * Returns the remaining deposit due for an event.
 *
 * @since	1.3
 * @param	int		$event_id	The event ID.
 * @return	str					The remaining deposit value due for the event.
 */
function mdjm_get_event_remaining_deposit( $event_id )	{
	if( empty( $event_id ) )	{
		return false;
	}

	$event = new MDJM_Event( $event_id );
	return $event->get_remaining_deposit();
} // mdjm_get_event_remaining_deposit

/**
 * Determine the event deposit value based upon event cost and
 * payment settings
 *
 * @param	int|str		$price	Current price of event.
 */
function mdjm_calculate_deposit( $price = '' )	{
	
	$deposit_type = mdjm_get_event_deposit_type();

	if ( empty( $price ) && 'fixed' != $deposit_type )	{
		$deposit = 0;
	}
	
	if ( empty( $deposit_type ) )	{
		$deposit = '0';
	} elseif( $deposit_type == 'fixed' )	{
		$deposit = mdjm_get_option( 'deposit_amount' );
	} elseif( $deposit_type == 'percentage' )	{
		$percentage = mdjm_get_option( 'deposit_amount' );
		
		$deposit = ( !empty( $price ) && $price > 0 ? round( $percentage * ( $price / 100 ), 2 ) : 0 );
	}
	
	apply_filters( 'mdjm_calculate_deposit', $deposit, $price );
	
	return mdjm_sanitize_amount( $deposit );
	
} // mdjm_calculate_deposit

/**
 * Mark the event deposit as paid.
 *
 * Determines if any deposit remains and if so, assumes it has been paid and
 * creates an associted transaction.
 *
 * @since	1.3
 * @param	int		$event_id	The event ID.
 * @return	void
 */
function mdjm_mark_event_deposit_paid( $event_id )	{

	$mdjm_event = new MDJM_Event( $event_id );
	$txn_id     = 0;
	
	if ( 'Paid' == $mdjm_event->get_deposit_status() )	{
		return;
	}

	$remaining = $mdjm_event->get_remaining_deposit();

	do_action( 'mdjm_pre_mark_event_deposit_paid', $event_id, $remaining );

	if ( ! empty( $remaining ) && $remaining > 0 )	{
		$mdjm_txn = new MDJM_Txn;
		
		$txn_meta = array(
			'_mdjm_txn_source'      => mdjm_get_option( 'default_type', __( 'Cash', 'mobile-dj-manager' ) ),
			'_mdjm_txn_currency'    => mdjm_get_currency(),
			'_mdjm_txn_status'      => 'Completed',
			'_mdjm_txn_total'       => $remaining,
			'_mdjm_payer_firstname' => mdjm_get_client_firstname( $mdjm_event->client ),
			'_mdjm_payer_lastname'  => mdjm_get_client_lastname( $mdjm_event->client ),
			'_mdjm_payer_email'     => mdjm_get_client_email( $mdjm_event->client ),
			'_mdjm_payment_from'    => mdjm_get_client_display_name( $mdjm_event->client ),
		);
		
		$mdjm_txn->create( array( 'post_parent' => $event_id ), $txn_meta );
		
		if ( $mdjm_txn->ID > 0 )	{

			mdjm_set_txn_type( $mdjm_txn->ID, mdjm_get_txn_cat_id( 'slug', 'mdjm-deposit-payments' ) );

			$args = array(
				'user_id'          => get_current_user_id(),
				'event_id'         => $event_id,
				'comment_content'  => sprintf( __( '%1$s payment of %2$s received and %1$s marked as paid.', 'mobile-dj-manager' ),
					mdjm_get_deposit_label(),
					mdjm_currency_filter( mdjm_format_amount( $remaining ) )
				)
			);
			
			mdjm_add_journal( $args );

			mdjm_add_content_tag( 'payment_for', __( 'Reason for payment', 'mobile-dj-manager' ), 'mdjm_content_tag_deposit_label' );
			mdjm_add_content_tag( 'payment_amount', __( 'Payment amount', 'mobile-dj-manager' ), function() use ( $remaining ) { return mdjm_currency_filter( mdjm_format_amount( $remaining ) ); } );
			mdjm_add_content_tag( 'payment_date', __( 'Date of payment', 'mobile-dj-manager' ), 'mdjm_content_tag_ddmmyyyy' );
			
			do_action( 'mdjm_post_add_manual_txn_in', $event_id, $mdjm_txn->ID );
			
		}

	}

	mdjm_update_event_meta( $mdjm_event->ID, array( '_mdjm_event_deposit_status' => 'Paid' ) );

	do_action( 'mdjm_post_mark_event_deposit_paid', $event_id );

} // mdjm_mark_event_deposit_paid

/**
 * Mark the event balance as paid.
 *
 * Determines if any balance remains and if so, assumes it has been paid and
 * creates an associted transaction.
 *
 * @since	1.3
 * @param	int		$event_id	The event ID.
 * @return	void
 */
function mdjm_mark_event_balance_paid( $event_id )	{

	$mdjm_event = new MDJM_Event( $event_id );
	$txn_id     = 0;
	
	if ( 'Paid' == $mdjm_event->get_balance_status() )	{
		return;
	}

	$remaining = $mdjm_event->get_balance();

	do_action( 'mdjm_pre_mark_event_balance_paid', $event_id, $remaining );

	if ( ! empty( $remaining ) && $remaining > 0 )	{
		$mdjm_txn = new MDJM_Txn;
		
		$txn_meta = array(
			'_mdjm_txn_source'      => mdjm_get_option( 'default_type', __( 'Cash', 'mobile-dj-manager' ) ),
			'_mdjm_txn_currency'    => mdjm_get_currency(),
			'_mdjm_txn_status'      => 'Completed',
			'_mdjm_txn_total'       => $remaining,
			'_mdjm_payer_firstname' => mdjm_get_client_firstname( $mdjm_event->client ),
			'_mdjm_payer_lastname'  => mdjm_get_client_lastname( $mdjm_event->client ),
			'_mdjm_payer_email'     => mdjm_get_client_email( $mdjm_event->client ),
			'_mdjm_payment_from'    => mdjm_get_client_display_name( $mdjm_event->client ),
		);
		
		$mdjm_txn->create( array( 'post_parent' => $event_id ), $txn_meta );
		
		if ( $mdjm_txn->ID > 0 )	{

			mdjm_set_txn_type( $mdjm_txn->ID, mdjm_get_txn_cat_id( 'slug', 'mdjm-balance-payments' ) );

			$args = array(
				'user_id'          => get_current_user_id(),
				'event_id'         => $event_id,
				'comment_content'  => sprintf( __( '%1$s payment of %2$s received and %1$s marked as paid.', 'mobile-dj-manager' ),
					mdjm_get_balance_label(),
					mdjm_currency_filter( mdjm_format_amount( $remaining ) )
				)
			);
			
			mdjm_add_journal( $args );

			mdjm_add_content_tag( 'payment_for', __( 'Reason for payment', 'mobile-dj-manager' ), 'mdjm_content_tag_balance_label' );
			mdjm_add_content_tag( 'payment_amount', __( 'Payment amount', 'mobile-dj-manager' ), function() use ( $remaining ) { return mdjm_currency_filter( mdjm_format_amount( $remaining ) ); } );
			mdjm_add_content_tag( 'payment_date', __( 'Date of payment', 'mobile-dj-manager' ), 'mdjm_content_tag_ddmmyyyy' );
			
			do_action( 'mdjm_post_add_manual_txn_in', $event_id, $mdjm_txn->ID );
			
		}

	}

	mdjm_update_event_meta(
		$mdjm_event->ID,
		array(
			'_mdjm_event_deposit_status' => 'Paid',
			'_mdjm_event_balance_status' => 'Paid'
		)
	);

	do_action( 'mdjm_post_mark_event_balance_paid', $event_id );

} // mdjm_mark_event_balance_paid

/**
 * Returns the balance status for an event.
 *
 * @since	1.3
 * @param	int		$event_id	The event ID.
 * @return	str					The balance status of the event.
 */
function mdjm_get_event_balance_status( $event_id )	{
	if( empty( $event_id ) )	{
		return false;
	}

	$event = new MDJM_Event( $event_id );
	return $event->get_balance_status();
} // mdjm_get_event_balance_status

/**
 * Returns the balance owed for an event.
 *
 * @since	1.3
 * @param	int		$event_id	The event ID.
 * @return	int|str				The balance owed for the event.
 */
function mdjm_get_event_balance( $event_id )	{
	if( empty( $event_id ) )	{
		return false;
	}

	$event = new MDJM_Event( $event_id );
	return $event->get_balance();
} // mdjm_get_event_balance

/**
 * Returns the total income for an event.
 *
 * @since	1.3
 * @param	int		$event_id	The event ID.
 * @return	int|str				The income for the event.
 */
function mdjm_get_event_income( $event_id )	{
	if( empty( $event_id ) )	{
		return false;
	}

	$event = new MDJM_Event( $event_id );
	return $event->get_total_income();
} // mdjm_get_event_income

/**
 * Retrieve event transactions.
 *
 * @since	1.3.8
 * @param	int		$event_id		Event ID.
 * @param	arr		$args			@see get_posts
 * @return	obj						Array of event transactions.
 */
function mdjm_get_event_txns( $event_id, $args = array() )	{

	$defaults = array(
		'post_parent' => $event_id,
		'post_status' => 'any',
		'meta_key'    => '_mdjm_txn_status',										
		'meta_query'  => array(
			'key'     => '_mdjm_txn_status',
			'value'   => 'Completed',
			'compare' => '='
		)
	);

	$args = wp_parse_args( $args, $defaults );

	return mdjm_get_txns( $args );

} // mdjm_get_event_txns

/**
 * Generate a list of event transactions.
 *
 * @since	1.3.8
 * @param	int		$event_id		Event ID.
 * @return	arr		$event_txns		Array of event transactions.
 */
function mdjm_list_event_txns( $event_id )	{

	$args = array( 'post_status' => 'mdjm-income' );

	$event_txns = mdjm_get_event_txns( $event_id, $args );

	$txns = array();

	if ( $event_txns )	{
		foreach ( $event_txns as $txn )	{
			$mdjm_txn = new MDJM_Txn( $txn->ID );

			$txns[] = mdjm_currency_filter( mdjm_format_amount( $mdjm_txn->price ) ) .
						' on ' .
						mdjm_format_short_date( $mdjm_txn->post_date ) .
						' (' . $mdjm_txn->get_type() . ')';

		}
	}

	return implode( '<br />', $txns );

} // mdjm_list_event_txns

/**
 * Displays all event transactions within a table.
 *
 * @since	1.3.7
 * @global	obj		$mdjm_event			MDJM_Event class object
 * @param	int		$event_id
 * @return	str
 */
function mdjm_do_event_txn_table( $event_id )	{

	global $mdjm_event;

	$event_txns = apply_filters( 'mdjm_event_txns', mdjm_get_event_txns(
		$event_id,
		array( 'orderby' => 'post_status' )
	) );

	$in  = 0;
	$out = 0;

	?>

	<table class="widefat mdjm_event_txn_list">
        <thead>
            <tr>
                <th style="width: 20%"><?php _e( 'Date', 'mobile-dj-manager' ); ?></th>
                <th style="width: 20%"><?php _e( 'To/From', 'mobile-dj-manager' ); ?></th>
                <th style="width: 15%"><?php _e( 'In', 'mobile-dj-manager' ); ?></th>
                <th style="width: 15%"><?php _e( 'Out', 'mobile-dj-manager' ); ?></th>
                <th><?php _e( 'Details', 'mobile-dj-manager' ); ?></th>
                <?php do_action( 'mdjm_event_txn_table_head', $event_id ); ?>
            </tr>
        </thead>
        <tbody>
        <?php if ( $event_txns ) :  ?>
            <?php foreach ( $event_txns as $event_txn ) : ?>

                <?php $txn = new MDJM_Txn( $event_txn->ID ); ?>

                <tr class="mdjm_field_wrapper">
                    <td><a href="<?php echo get_edit_post_link( $txn->ID ); ?>"><?php echo mdjm_format_short_date( $txn->post_date ); ?></a></td>
                    <td><?php echo esc_attr( mdjm_get_txn_recipient_name( $txn->ID ) ); ?></td>
                    <td>
                        <?php if ( $txn->post_status == 'mdjm-income' ) : ?>
                            <?php $in += mdjm_sanitize_amount( $txn->price ); ?>
                            <?php echo mdjm_currency_filter( mdjm_format_amount( $txn->price ) ); ?>
                        <?php else : ?>
                            <?php echo '&ndash;' ?>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php if ( $txn->post_status == 'mdjm-expenditure' ) : ?>
                            <?php $out += mdjm_sanitize_amount( $txn->price ); ?>
                            <?php echo mdjm_currency_filter( mdjm_format_amount( $txn->price ) ); ?>
                        <?php else : ?>
                            <?php echo '&ndash;' ?>
                        <?php endif; ?>
                    </td>
                    <td><?php echo $txn->get_type(); ?></td>
                </tr>
            <?php endforeach; ?>
        <?php else : ?>
        <tr>            
            <td colspan="5"><?php printf( __( 'There are currently no transactions for this %s', 'mobile-dj-manager' ), mdjm_get_label_singular( true ) ); ?></td>
        </tr>
        <?php endif; ?>
        </tbody>
        <tfoot>
        <tr>
            <th style="width: 20%">&nbsp;</th>
            <th style="width: 20%">&nbsp;</th>
            <th style="width: 15%"><strong><?php echo mdjm_currency_filter( mdjm_format_amount( $in ) ); ?></strong></th>
            <th style="width: 15%"><strong><?php echo mdjm_currency_filter( mdjm_format_amount( $out ) ); ?></strong></th>
            <th><strong><?php printf( __( '%s Earnings:', 'mobile-dj-manager' ), mdjm_get_label_singular() ); ?> <?php echo mdjm_currency_filter( mdjm_format_amount( ( $in - $out ) ) ); ?></strong></th>
        </tr>
        <?php do_action( 'mdjm_event_txn_table_foot', $event_id ); ?>
        </tfoot>
    </table>

	<?php

} // mdjm_do_event_txn_table

/**
 * Returns the client ID.
 *
 * @since	1.3
 * @param	int		$event_id	The event ID.
 * @return	int					The user ID of the event client.
 */
function mdjm_get_event_client_id( $event_id )	{
	$event = new MDJM_Event( $event_id );
	return $event->client;
} // mdjm_get_event_client_id

/**
 * Retrieve the event employees.
 *
 * @since	1.3
 * @param	int		$event_id
 * @return	arr		Array of all event employees and data.
 */
function mdjm_get_all_event_employees( $event_id )	{
	$mdjm_event = new MDJM_Event( $event_id );
	
	return $mdjm_event->get_all_employees();
} // mdjm_get_event_employees

/**
 * Returns the primary employee ID.
 *
 * @since	1.3
 * @param	int		$event_id	The event ID.
 * @return	int					The user ID of the events primary employee.
 */
function mdjm_get_event_primary_employee_id( $event_id )	{
	if( empty( $event_id ) )	{
		return false;
	}

	$event = new MDJM_Event( $event_id );
	return $event->get_employee();
} // mdjm_get_event_primary_employee_id
	
/**
 * Returns the URL for an event.
 *
 * @since	1.3
 * @param	int		$event_id	The event ID.
 * @param	bool	$admin		True to retrieve the admin URL to the event
 * @return	str		URL to Client Zone page for the event.
 */
function mdjm_get_event_uri( $event_id, $admin = false )	{
	if ( $admin )	{
		return add_query_arg( array( 'post' => $event_id, 'action' => 'edit' ), admin_url( 'post.php' ) );
	} else	{
		return add_query_arg( 'event_id', $event_id, mdjm_get_formatted_url( mdjm_get_option( 'app_home_page' ) ) );
	}
} // mdjm_get_event_uri

/**
 * Retrieve the duration of the event.
 *
 * Calculate the duration of the event and return in human readable format.
 *
 * @since	1.3
 * @uses	human_time_diff
 * @param	int		$event_id	The event ID.
 * @return	str		The length of the event.
 */
function mdjm_event_duration( $event_id )	{
	$start_time = get_post_meta( $event_id, '_mdjm_event_start', true );
	$start_date = get_post_meta( $event_id, '_mdjm_event_date', true );
	$end_time   = get_post_meta( $event_id, '_mdjm_event_finish', true );
	$end_date   = get_post_meta( $event_id, '_mdjm_event_end_date', true );
	
	if( ! empty( $start_time ) && ! empty( $start_date ) && ! empty( $end_time ) && ! empty( $end_time ) )	{
		$start  = strtotime( $start_time . ' ' . $start_date );
		$end    = strtotime( $end_time . ' ' . $end_date );
		
		$duration = str_replace( 'min', 'minute', human_time_diff( $start, $end ) );
		
		return apply_filters( 'mdjm_event_duration', $duration );
	}
} // mdjm_event_duration

/**
 * Calculate time to event.
 *
 * Calculate the length of time until the event starts.
 *
 * @since	1.3
 * @uses	human_time_diff
 * @param	int		$event_id	The event ID.
 * @return	str		The length of the event.
 */
function mdjm_time_until_event( $event_id )	{
	
	$start_time = get_post_meta( $event_id, '_mdjm_event_start', true );
	$start_date = get_post_meta( $event_id, '_mdjm_event_date', true );
	
	if( ! empty( $start_time ) && ! empty( $start_date ) )	{
		
		$start  = strtotime( $start_time . ' ' . $start_date );
		$end    = strtotime( $end_time . ' ' . $end_date );
		
		$length = str_replace( 'min', 'minute', human_time_diff( $start, $end ) );
		
		return apply_filters( 'mdjm_time_until_event', $length );
		
	}

} // mdjm_time_until_event

/**
 * Update event meta.
 *
 * We don't currently delete empty meta keys or values, instead we update with an empty value
 * if an empty value is passed to the function.
 *
 * We may soon move to a configuration where all meta key => value pairs are stored in a single
 * meta key (_mdjm_event_data). As a result there is some duplication here, but performance
 * impact is minimal.
 *
 * @since	1.3
 * @param	int		$event_id	The event ID.
 * @param	arr		$data		The appropriately formatted meta data values.
 * @return	mixed	See get_post_meta()
 */
function mdjm_update_event_meta( $event_id, $data )	{

	do_action( 'mdjm_pre_update_event_meta', $event_id, $data );
	
	// For backwards compatibility
	$current_meta = get_post_meta( $event_id );
	
	$debug = array();
	$meta  = get_post_meta( $event_id, '_mdjm_event_data', true );
	
	foreach( $data as $key => $value )	{
		
		if ( $key == 'mdjm_nonce' || $key == 'mdjm_action' || substr( $key, 0, 12 ) != '_mdjm_event_' ) {
			continue;
		}
		
		if ( $key == '_mdjm_event_cost' || $key == '_mdjm_event_deposit' || $key == '_mdjm_event_dj_wage' )	{
			$value = $value;
		} elseif ( $key == '_mdjm_event_venue_postcode' && ! empty( $value ) )	{ // Postcodes are uppercase.
			$value = strtoupper( $value );
		} elseif ( $key == '_mdjm_event_venue_email' && ! empty( $value ) )	{ // Emails are lowercase.
			$value = strtolower( $value );
		} elseif ( $key == '_mdjm_event_package' && ! empty( $value ) )	{
			$value = sanitize_text_field( strtolower( $value ) );	
		} elseif ( $key == '_mdjm_event_addons' && ! empty( $value ) )	{
			$value = $value;
		} elseif ( $key == '_mdjm_event_travel_data' )	{
			$value = $value;
		} elseif ( ! strpos( $key, 'notes' ) && ! empty( $value ) )	{
			$value = sanitize_text_field( ucwords( $value ) );
		} elseif ( ! empty( $value ) )	{
			$value = $value;
		} else	{
			$value = '';
		}
		
		// If we have a value and the key did not exist previously, add it.
		if ( ! empty( $value ) && ( empty( $current_meta[ $key ] ) || empty( $current_meta[ $key ][0] ) ) )	{
			
			$debug[] = sprintf( __( 'Adding %s value as %s', 'mobile-dj-manager' ), mdjm_event_get_meta_label( $key ), is_array( $value ) ? var_export( $value, true ) : $value );
			add_post_meta( $event_id, $key, $value );
			
		} elseif ( ! empty( $value ) && $value != $current_meta[ $key ][0] )	{ // If a value existed, but has changed, update it.
		
			$debug[] = sprintf( __( 'Updating %s with %s', 'mobile-dj-manager' ), mdjm_event_get_meta_label( $key ), is_array( $value ) ? var_export( $value, true ) : $value );
			update_post_meta( $event_id, $key, $value );

			
		} elseif ( empty( $value ) && ! empty( $current_meta[ $key ][0] ) )	{ // If there is no new meta value but an old value exists, delete it.
		
			$debug[] = sprintf( __( 'Removing %s from %s', 'mobile-dj-manager' ), $current_meta[ $key ][0], mdjm_event_get_meta_label( $key ) );
			delete_post_meta( $event_id, $key, $value );
			
		}
		
	}
	
	$journal_args = array(
		'user_id'         => is_user_logged_in() ? get_current_user_id() : 1,
		'event_id'        => $event_id,
		'comment_content' => sprintf( __( '%s Updated', 'mobile-dj-manager' ) . ':<br />    %s',
			mdjm_get_label_singular(), implode( '<br />', $debug )
		)
	);
	
	mdjm_add_journal( $journal_args );
	
	do_action( 'mdjm_primary_employee_payment_status', $event_id, $current_meta, $data );
	do_action( 'mdjm_post_update_event_meta', $event_id, $current_meta, $data );
	
	if ( ! empty( $debug ) )	{
		
		foreach( $debug as $log )	{
			MDJM()->debug->log_it( $log, false );
		}
		
	}
	
	return true;
	
} // mdjm_update_event_meta

/**
 * Retrieve a readable name for the meta key.
 *
 * @since	1.3
 * @param	str		$key	The meta key.
 * @return	str		The readable label
 */
function mdjm_event_get_meta_label( $key )	{
	
	$keys = array(
		'_mdjm_event_addons'            => __( 'Add-ons', 'mobile-dj-manager' ),
		'_mdjm_event_admin_notes'       => __( 'Admin Notes', 'mobile-dj-manager' ),
		'_mdjm_event_balance_status'    => sprintf( __( '%s Status', 'mobile-dj-manager' ), mdjm_get_balance_label() ),
		'_mdjm_event_client'            => __( 'Client', 'mobile-dj-manager' ),
		'_mdjm_event_contract'          => sprintf( __( '%s Contract', 'mobile-dj-manager' ), mdjm_get_label_singular() ),
		'_mdjm_event_contract_approved' => __( 'Contract Approved Date', 'mobile-dj-manager' ),
		'_mdjm_event_contract_approver' => __( 'Contract Approved By', 'mobile-dj-manager' ),
		'_mdjm_event_cost'              => __( 'Total Cost', 'mobile-dj-manager' ),
		'_mdjm_event_date'              => sprintf( __( '%s Date', 'mobile-dj-manager' ), mdjm_get_label_singular() ),
		'_mdjm_event_deposit'           => mdjm_get_deposit_label(),
		'_mdjm_event_deposit_status'    => sprintf( __( '%s Status', 'mobile-dj-manager' ), mdjm_get_deposit_label() ),
		'_mdjm_event_dj'                => sprintf( __( '%s Contract', 'mobile-dj-manager' ), mdjm_get_option( 'artist' ) ),
		'_mdjm_event_dj_notes'          => __( 'Employee Notes', 'mobile-dj-manager' ),
		'_mdjm_event_dj_payment_status' => sprintf( __( 'Primary Employee %s Payment Details', 'mobile-dj-manager' ), mdjm_get_label_singular() ),
		'_mdjm_event_djsetup_date'      => sprintf( __( '%s Setup Date', 'mobile-dj-manager' ), mdjm_get_label_singular() ),
		'_mdjm_event_djsetup_time'      => sprintf( __( '%s Setup Time', 'mobile-dj-manager' ), mdjm_get_label_singular() ),
		'_mdjm_event_dj_wage'           => sprintf( __( 'Primary Employee %s Wage', 'mobile-dj-manager' ), mdjm_get_label_singular() ),
		'_mdjm_event_employees'         => __( 'Employees', 'mobile-dj-manager' ),
		'_mdjm_event_employees_data'    => __( 'Employees Payment Data', 'mobile-dj-manager' ),
		'_mdjm_event_enquiry_source'    => __( 'Enquiry Source', 'mobile-dj-manager' ),
		'_mdjm_event_finish'            => __( 'End Time', 'mobile-dj-manager' ),
		'_mdjm_event_last_updated_by'   => __( 'Last Updated By', 'mobile-dj-manager' ),
		'_mdjm_event_name'              => sprintf( __( '%s Name', 'mobile-dj-manager' ), mdjm_get_label_singular() ),
		'_mdjm_event_notes'             => __( 'Description', 'mobile-dj-manager' ),
		'_mdjm_event_package'           => __( 'Package', 'mobile-dj-manager' ),
		'_mdjm_event_playlist'          => __( 'Playlist Enabled', 'mobile-dj-manager' ),
		'_mdjm_event_playlist_access'   => __( 'Playlist Guest Access Code', 'mobile-dj-manager' ),
		'_mdjm_event_start'             => __( 'Start Time', 'mobile-dj-manager' ),
		'_mdjm_event_travel_data'       => __( 'Travel Data', 'mobile-dj-manager' ),
		'_mdjm_event_venue_address1'    => __( 'Venue Address Line 1', 'mobile-dj-manager' ),
		'_mdjm_event_venue_address2'    => __( 'Venue Address Line 2', 'mobile-dj-manager' ),
		'_mdjm_event_venue_contact'     => __( 'Venue Contact', 'mobile-dj-manager' ),
		'_mdjm_event_venue_county'      => __( 'Venue County', 'mobile-dj-manager' ),
		'_mdjm_event_venue_email'       => __( 'Venue Email Address', 'mobile-dj-manager' ),
		'_mdjm_event_venue_id'          => __( 'Venue ID', 'mobile-dj-manager' ),
		'_mdjm_event_venue_name'        => __( 'Venue Name', 'mobile-dj-manager' ),
		'_mdjm_event_venue_phone'       => __( 'Venue Phone Number', 'mobile-dj-manager' ),
		'_mdjm_event_venue_postcode'    => __( 'Venue Post Code', 'mobile-dj-manager' ),
		'_mdjm_event_venue_town'        => __( 'Venue Post Town', 'mobile-dj-manager' )
	);
	
	$keys = apply_filters( 'mdjm_event_meta_labels', $keys );
	
	if ( array_key_exists( $key, $keys ) )	{
		return $keys[ $key ];
	} else	{
		return $key;
	}
	
} // mdjm_event_get_meta_label

/**
 * Update the event status.
 *
 * @since	1.3
 * @param	int		$event_id	The event ID.
 * @param	str		$new_status	The new event status.
 * @param	str		$old_status	The old event status.
 * @param	arr		$args		Array of data required for transition.
 * @return	int		The ID of the event if it is successfully updated. Otherwise returns 0.
 */
function mdjm_update_event_status( $event_id, $new_status, $old_status, $args = array() )	{
		
	if ( $new_status == $old_status )	{
		return false;
	}
	
	do_action( 'mdjm_pre_event_status_change', $event_id, $new_status, $old_status, $args );
	
	do_action( "mdjm_pre_update_event_status_{$new_status}", $event_id, $old_status, $args );
	
	$func = 'mdjm_set_event_status_' . str_replace( '-', '_', $new_status );
	
	if ( function_exists( $func ) )	{
		$result = $func( $event_id, $old_status, $args );
	} else	{
		$result = true;
	}
	
	do_action( "mdjm_post_update_event_status_{$new_status}", $result, $event_id, $old_status, $args );
	
	do_action( 'mdjm_post_event_status_change', $result, $event_id, $new_status, $old_status, $args );
	
	return $result;
	
} // mdjm_update_event_status

/**
 * Update event status to Unattended Enquiry.
 *
 * If you're looking for hooks, see the mdjm_update_event_status() function.
 * Do not call this function directly, instead call mdjm_update_event_status() to ensure
 * all hooks are processed.
 *
 * @since	1.3
 * @param	int		$event_id	The event ID.
 * @param	str		$old_status	The old event status.
 * @param	arr		$args		Array of data required for transition.
 * @return	int		The ID of the event if it is successfully updated. Otherwise returns 0.
 */
function mdjm_set_event_status_mdjm_unattended( $event_id, $old_status, $args = array() )	{
	
	remove_action( 'save_post_mdjm-event', 'mdjm_save_event_post', 10, 3 );
	
	$update = wp_update_post(
		array( 
			'ID'             => $event_id,
			'post_status'    => 'mdjm-unattended'
		)
	);
	
	add_action( 'save_post_mdjm-event', 'mdjm_save_event_post', 10, 3 );
	
	// Meta updates
	$args['meta']['_mdjm_event_last_updated_by'] = is_user_logged_in() ? get_current_user_id() : 1;
	
	mdjm_update_event_meta( $event_id, $args['meta'] );
		
	return $update;
	
} // mdjm_set_event_status_mdjm_unattended

/**
 * Update event status to Enquiry.
 *
 * If you're looking for hooks, see the mdjm_update_event_status() function.
 * Do not call this function directly, instead call mdjm_update_event_status() to ensure
 * all hooks are processed.
 *
 * @since	1.3
 * @param	int		$event_id	The event ID.
 * @param	str		$old_status	The old event status.
 * @param	arr		$args		Array of data required for transition.
 * @return	int		The ID of the event if it is successfully updated. Otherwise returns 0.
 */
function mdjm_set_event_status_mdjm_enquiry( $event_id, $old_status, $args = array() )	{
	
	remove_action( 'save_post_mdjm-event', 'mdjm_save_event_post', 10, 3 );
	
	$update = wp_update_post(
		array( 
			'ID'             => $event_id,
			'post_status'    => 'mdjm-enquiry'
		)
	);
	
	add_action( 'save_post_mdjm-event', 'mdjm_save_event_post', 10, 3 );
	
	// Meta updates
	$args['meta']['_mdjm_event_last_updated_by'] = is_user_logged_in() ? get_current_user_id() : 1;
	
	mdjm_update_event_meta( $event_id, $args['meta'] );
	
	// Generate an online quote that is visible via the Client Zone
	if( mdjm_get_option( 'online_enquiry', false ) )	{
		
		$quote_template = isset( $args['quote_template'] ) ? $args['quote_template'] : mdjm_get_option( 'online_enquiry' );
		
		$quote_id = mdjm_create_online_quote( $event_id, $quote_template );

	}
	
	// Email the client
	if( ! empty( $args['client_notices'] ) )	{

		$email_template = isset( $args['email_template'] ) ? $args['email_template'] : mdjm_get_option( 'enquiry' );
		
		mdjm_email_quote( $event_id, $email_template );

	}
		
	return $update;
	
} // mdjm_set_event_status_mdjm_enquiry

/**
 * Update event status to Awaiting Contract.
 *
 * If you're looking for hooks, see the mdjm_update_event_status() function.
 * Do not call this function directly, instead call mdjm_update_event_status() to ensure
 * all hooks are processed.
 *
 * @since	1.3
 * @param	int		$event_id	The event ID.
 * @param	str		$old_status	The old event status.
 * @param	arr		$args		Array of data required for transition.
 * @return	int		The ID of the event if it is successfully updated. Otherwise returns 0.
 */
function mdjm_set_event_status_mdjm_contract( $event_id, $old_status, $args = array() )	{
	
	remove_action( 'save_post_mdjm-event', 'mdjm_save_event_post', 10, 3 );
	
	$update = wp_update_post(
		array( 
			'ID'             => $event_id,
			'post_status'    => 'mdjm-contract'
		)
	);
	
	// Meta updates
	$args['meta']['_mdjm_event_last_updated_by'] = is_user_logged_in() ? get_current_user_id() : 1;
	
	mdjm_update_event_meta( $event_id, $args['meta'] );
	
	// Email the client
	if( ! empty( $args['client_notices'] ) )	{
		mdjm_email_enquiry_accepted( $event_id );
	}
	
	add_action( 'save_post_mdjm-event', 'mdjm_save_event_post', 10, 3 );
	
	return $update;
	
} // mdjm_set_event_status_mdjm_contract

/**
 * Update event status to Approved.
 *
 * If you're looking for hooks, see the mdjm_update_event_status() function.
 * Do not call this function directly, instead call mdjm_update_event_status() to ensure
 * all hooks are processed.
 *
 * @since	1.3
 * @param	int		$event_id	The event ID.
 * @param	str		$old_status	The old event status.
 * @param	arr		$args		Array of data required for transition.
 * @return	int		The ID of the event if it is successfully updated. Otherwise returns 0.
 */
function mdjm_set_event_status_mdjm_approved( $event_id, $old_status, $args = array() )	{
	
	remove_action( 'save_post_mdjm-event', 'mdjm_save_event_post', 10, 3 );
	
	$update = wp_update_post(
		array( 
			'ID'             => $event_id,
			'post_status'    => 'mdjm-approved'
		)
	);
	
	add_action( 'save_post_mdjm-event', 'mdjm_save_event_post', 10, 3 );
	
	// Meta updates
	$args['meta']['_mdjm_event_last_updated_by'] = is_user_logged_in() ? get_current_user_id() : 1;
	
	mdjm_update_event_meta( $event_id, $args['meta'] );
	
	// Email the client
	if( ! empty( $args['client_notices'] ) )	{
		mdjm_email_booking_confirmation( $event_id );
	}
	
	return $update;
	
} // mdjm_set_event_status_mdjm_approved

/**
 * Update event status to Completed.
 *
 * If you're looking for hooks, see the mdjm_update_event_status() function.
 * Do not call this function directly, instead call mdjm_update_event_status() to ensure
 * all hooks are processed.
 *
 * @since	1.3
 * @param	int		$event_id	The event ID.
 * @param	str		$old_status	The old event status.
 * @param	arr		$args		Array of data required for transition.
 * @return	int		The ID of the event if it is successfully updated. Otherwise returns 0.
 */
function mdjm_set_event_status_mdjm_completed( $event_id, $old_status, $args = array() )	{
	
	remove_action( 'save_post_mdjm-event', 'mdjm_save_event_post', 10, 3 );
	
	$update = wp_update_post(
		array( 
			'ID'             => $event_id,
			'post_status'    => 'mdjm-completed'
		)
	);
	
	add_action( 'save_post_mdjm-event', 'mdjm_save_event_post', 10, 3 );
	
	// Meta updates
	$args['meta']['_mdjm_event_last_updated_by'] = is_user_logged_in() ? get_current_user_id() : 1;
	
	mdjm_update_event_meta( $event_id, $args['meta'] );
		
	return $update;
	
} // mdjm_set_event_status_mdjm_completed

/**
 * Update event status to Cancelled.
 *
 * If you're looking for hooks, see the mdjm_update_event_status() function.
 * Do not call this function directly, instead call mdjm_update_event_status() to ensure
 * all hooks are processed.
 *
 * @since	1.3
 * @param	int		$event_id	The event ID.
 * @param	str		$old_status	The old event status.
 * @param	arr		$args		Array of data required for transition.
 * @return	int		The ID of the event if it is successfully updated. Otherwise returns 0.
 */
function mdjm_set_event_status_mdjm_cancelled( $event_id, $old_status, $args = array() )	{
	
	remove_action( 'save_post_mdjm-event', 'mdjm_save_event_post', 10, 3 );
	
	$update = wp_update_post(
		array( 
			'ID'             => $event_id,
			'post_status'    => 'mdjm-cancelled'
		)
	);
	
	add_action( 'save_post_mdjm-event', 'mdjm_save_event_post', 10, 3 );
	
	// Meta updates
	$args['meta']['_mdjm_event_last_updated_by'] = is_user_logged_in() ? get_current_user_id() : 1;
	
	mdjm_update_event_meta( $event_id, $args['meta'] );
		
	return $update;
	
} // mdjm_set_event_status_mdjm_cancelled

/**
 * Update event status to Failed Enquiry.
 *
 * If you're looking for hooks, see the mdjm_update_event_status() function.
 * Do not call this function directly, instead call mdjm_update_event_status() to ensure
 * all hooks are processed.
 *
 * @since	1.3
 * @param	int		$event_id	The event ID.
 * @param	str		$old_status	The old event status.
 * @param	arr		$args		Array of data required for transition.
 * @return	int		The ID of the event if it is successfully updated. Otherwise returns 0.
 */
function mdjm_set_event_status_mdjm_failed( $event_id, $old_status, $args = array() )	{
	
	remove_action( 'save_post_mdjm-event', 'mdjm_save_event_post', 10, 3 );
	
	$update = wp_update_post(
		array( 
			'ID'             => $event_id,
			'post_status'    => 'mdjm-failed'
		)
	);
	
	add_action( 'save_post_mdjm-event', 'mdjm_save_event_post', 10, 3 );
	
	// Meta updates
	$args['meta']['_mdjm_event_last_updated_by'] = is_user_logged_in() ? get_current_user_id() : 1;
	
	mdjm_update_event_meta( $event_id, $args['meta'] );
		
	return $update;
	
} // mdjm_set_event_status_mdjm_failed

/**
 * Update event status to Rejected Enquiry.
 *
 * If you're looking for hooks, see the mdjm_update_event_status() function.
 * Do not call this function directly, instead call mdjm_update_event_status() to ensure
 * all hooks are processed.
 *
 * @since	1.3
 * @param	int		$event_id	The event ID.
 * @param	str		$old_status	The old event status.
 * @param	arr		$args		Array of data required for transition.
 * @return	int		The ID of the event if it is successfully updated. Otherwise returns 0.
 */
function mdjm_set_event_status_mdjm_rejected( $event_id, $old_status, $args = array() )	{
	
	remove_action( 'save_post_mdjm-event', 'mdjm_save_event_post', 10, 3 );
	
	$update = wp_update_post(
		array( 
			'ID'             => $event_id,
			'post_status'    => 'mdjm-rejected'
		)
	);
	
	add_action( 'save_post_mdjm-event', 'mdjm_save_event_post', 10, 3 );
	
	// Meta updates
	$args['meta']['_mdjm_event_last_updated_by'] = is_user_logged_in() ? get_current_user_id() : 1;
	
	mdjm_update_event_meta( $event_id, $args['meta'] );
		
	return $update;
	
} // mdjm_set_event_status_mdjm_rejected

/**
 * Retrieve the quote for the event.
 *
 * @since	1.3
 * @param	int			$event_id	The event ID.
 * @return	obj			Quote post object or false if no quote exists
 */
function mdjm_get_event_quote( $event_id )	{
	
	$quote = get_posts( 
		array( 
			'posts_per_page'	 => 1,
			'post_parent'		=> $event_id,
			'post_type'		  => 'mdjm-quotes',
			'post_status'		=> 'any'
		)
	);
								
	if( $quote )	{
		return $quote[0];
	} else	{
		return false;
	}
					
} // mdjm_get_event_quote

/**
 * Retrieve the Quote ID for the event
 *
 * @since	1.3
 * @param	int			$event_id	The event ID.
 * @return	int			Quote post ID false if no quote exists
 */
function mdjm_get_event_quote_id( $event_id )	{
	
	$quote = mdjm_get_event_quote( $event_id );
									
	if( $quote )	{
		return $quote->ID;
	} else	{
		return false;
	}
					
} // mdjm_get_event_quote_id

/**
 * Generates a new online quote for the event.
 *
 * Uses the quote template defined within settings unless $template_id is provided.
 *
 * @since	1.3
 * @param	int			$event_id		The event ID.
 * @param	int			$template_id	The template ID from which to create the quote.
 * @return	int			$quote_id		The ID of the newly created post or false on fail.
 */
function mdjm_create_online_quote( $event_id, $template_id = '' )	{
	
	$existing_id = mdjm_get_event_quote_id( $event_id );
	
	$template_id = ! empty( $template_id ) ? $template_id : mdjm_get_option( 'online_enquiry' );
	
	if ( empty( $template_id ) )	{
		return false;
	}
	
	/**
	 * Allow filtering of the quote template.
	 *
	 * @since	1.3
	 * @param	$template_id
	 */
	$template_id = apply_filters( 'mdjm_online_quote_template', $template_id );
	
	$template = get_post( $template_id );
	
	if ( ! $template )	{
		return false;
	}
	
	/**
	 * Fire the `mdjm_pre_create_online_quote` hook.
	 *
	 * @since	1.3
	 * @param	int		$event_id		The Event ID
	 * @param	int		$template_id	The quote template ID
	 * @param	obj		$template		The quote template WP_Post object
	 */
	do_action( 'mdjm_pre_create_online_quote', $event_id, $template_id, $template );
	
	$client_id = mdjm_get_event_client_id( $event_id );
	
	$content = $template->post_content;
	$content = apply_filters( 'the_content', $content );
	$content = str_replace( ']]>', ']]&gt;', $content );
	$content = mdjm_do_content_tags( $content, $event_id, $client_id );
	
	$args = array(
		'ID'			=> $existing_id,
		'post_date'		=> current_time( 'mysql' ),
		'post_modified'	=> current_time( 'mysql' ),
		'post_title'	=> sprintf( __( 'Quote %s', 'mobile-dj-manager' ), mdjm_get_event_contract_id( $event_id ) ),
		'post_content'	=> $content,
		'post_type'		=> 'mdjm-quotes',
		'post_status'	=> 'mdjm-quote-generated',
		'post_author'	=> ! empty( $client_id ) ? $client_id : 1,
		'post_parent'	=> $event_id,
		'meta_input'	=> array(
			'_mdjm_quote_viewed_date'	=> 0,
			'_mdjm_quote_viewed_count'	=> 0
		)
	);
	
	/**
	 * Allow filtering of the quote template args.
	 *
	 * @since	1.3
	 * @param	$args
	 */
	$args = apply_filters( 'mdjm_create_online_quote_args', $args );
	
	$quote_id = wp_insert_post( $args );
	
	if ( ! $quote_id )	{
		return false;
	}
	
	// Reset view date and count for existing quotes
	if( ! empty( $existing_id ) )	{
		delete_post_meta( $quote_id, '_mdjm_quote_viewed_date' );
		delete_post_meta( $quote_id, '_mdjm_quote_viewed_count' );
	}
	
	/**
	 * Fire the `mdjm_post_create_online_quote` hook.
	 *
	 * @since	1.3
	 * @param	int		$quote_id		The new quote ID
	 */
	do_action( 'mdjm_pre_create_online_quote', $quote_id );
	
	return $quote_id;
	
} // mdjm_create_online_quote

/**
 * Display the online quote.
 *
 * @since	1.3
 * @param	int			$event_id		The event ID.
 * @return	str			The content of the quote.
 */
function mdjm_display_quote( $event_id )	{
	
	$quote = mdjm_get_event_quote( $event_id );
	
	if ( ! $quote )	{
		return apply_filters( 'mdjm_quote_not_found_msg', sprintf( __( 'Sorry but the quote for your %s could not be displayed.', 'mobile-dj-manager' ), mdjm_get_label_singular( true ) ) );
	}
	
	$quote_content = $quote->post_content;
	$quote_content = apply_filters( 'the_content', $quote_content );
	$quote_content = str_replace( ']]>', ']]&gt;', $quote_content );
	
	mdjm_viewed_quote( $quote->ID, $event_id );
	
	return apply_filters( 'mdjm_display_quote', $quote_content, $event_id );
	
} // mdjm_display_quote

/**
 * Registers the online quote as viewed.
 *
 * Increase the view count.
 *
 * @since	1.3
 * @param	int			$quote_id		The quote ID.
 * @param	int			$event_id		The event ID.
 * @return	void
 */
function mdjm_viewed_quote( $quote_id, $event_id )	{
	
	// Only counts if the current user is the event client
	if ( get_current_user_id() != get_post_meta( $event_id, '_mdjm_event_client', true ) )	{
		return;
	}

	if( wp_update_post( array( 'ID' => $quote_id, 'post_status' => 'mdjm-quote-viewed' ) ) )	{

		$view_count = get_post_meta( $quote_id, '_mdjm_quote_viewed_count', true );

		if( ! empty( $view_count ) )	{
			$view_count++;
		} else	{
			$view_count = 1;
		}

		MDJM()->debug->log_it( 'Updating quote view count for Quote ID: ' . $quote_id . ' and event ID: ' . $event_id, true );
		update_post_meta( $quote_id, '_mdjm_quote_viewed_count', $view_count );

		// Only update the view date if this is the first viewing
		if( $view_count == 1 )	{

			MDJM()->debug->log_it( 'Updating quote viewed time', false );

			update_post_meta( $quote_id, '_mdjm_quote_viewed_date', current_time( 'mysql' ) );
		}

	}

} // mdjm_viewed_quote

/**
 * Retrieve the emails associated with the event.
 *
 * @since	1.3.7
 * @param	int		$event_id	Event ID
 * @return	obj		The email post objects.
 */
function mdjm_event_get_emails( $event_id )	{

	if ( ! mdjm_employee_can( 'read_events' ) )	{
		return false;
	}

	$args = array(
		'post_type'      => 'mdjm_communication',
		'post_status'    => 'any',
		'posts_per_page' => -1,
		'post_parent'    => $event_id,
		'order'          => 'DESC'
	);

	if ( ! mdjm_employee_can( 'read_events_all' ) )	{
		$args['post_author'] = get_current_user_id();
	}

	$emails = get_posts( $args );

	return apply_filters( 'mdjm_event_get_emails', $emails, $event_id );

} // mdjm_event_get_emails
