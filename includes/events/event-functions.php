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
 *
 * @return	mixed	$event		WP_Query object or false.
 */
function mdjm_get_event_by_id( $event_id )	{
	$event = new MDJM_Event( $event_id );
	
	return ( !empty( $event->ID ) ? $event : false );
} // mdjm_get_event_by_id

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
			'<h1>' . __( 'Cheatin&#8217; uh?' ) . '</h1>' .
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
 * Retrieve the next event.
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
	
} // mdjm_get_next_event

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
 * Retrieve events by date period.
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
 * Returns an array of event post status.
 *
 * @since	1.3
 * @param
 * @return	arr		Array of event status'. Key = post_status value = MDJM Event status
 */
function mdjm_all_event_status()	{
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
	$event_status = apply_filters( 'mdjm_active_event_statuses',
		array(
			'mdjm-approved',
			'mdjm-contract',
			'mdjm-enquiry'
		)
	);
	
	// Sort alphabetically
	asort( $event_status );
	
	return $event_status;
} // mdjm_active_event_statuses

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
 * @param	int		$event_id	Optional: ID of the current event. If not set, check for global $post and $post_id.
 * @return	str		Label for current event type.
 */
function mdjm_get_event_type( $event_id='' )	{
	
	global $post, $post_id;
	
	if( ! empty( $event_id ) )	{
		$id = $event_id;
	}
	elseif( ! empty( $post_id ) )	{
		$id = $post_id;
	}
	elseif( ! empty( $post ) )	{
		$id = $post->ID;
	}
	else	{
		$id = '';
	}
	
	$event = new MDJM_Event( $id );
	
	// Return the label for the status
	return $event->get_type();

} // mdjm_get_event_type

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
function mdjm_get_event_price( $event_id )	{
	if( empty( $event_id ) )	{
		return false;
	}

	$event = new MDJM_Event( $event_id );
	return $event->get_price();
} // mdjm_get_event_price

/**
 * Returns the deposit price for an event.
 *
 * @since	1.3
 * @param	int		$event_id	The event ID.
 * @return	int|str				The deposit price of the event.
 */
function mdjm_get_event_deposit( $event_id )	{
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
 * Determine the event deposit value based upon event cost and
 * payment settings
 *
 * @param	int|str		$price	Current price of event.
 */
function mdjm_calculate_deposit( $price = '' )	{
	
	if ( empty( $price ) )	{
		$deposit = 0;
	}
	
	$deposit_type = ( mdjm_get_option( 'deposit_type' ) );
	
	if ( empty( $deposit_type ) )	{
		$deposit = '0';
	} elseif( $deposit_type == 'fixed' )	{
		$deposit = mdjm_get_option( 'deposit_amount' );
	} elseif( $deposit_type == 'percentage' )	{
		$percentage = mdjm_get_option( 'deposit_amount' );
		
		$deposit = ( !empty( $price ) && $price > 0 ? round( $percentage * ( $price / 100 ), 2 ) : 0 );
	}
	
	apply_filters( 'mdjm_calculate_deposit', $deposit, $price );
	
	return mdjm_format_amount( $deposit );
	
} // mdjm_calculate_deposit

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
 * Returns the client ID.
 *
 * @since	1.3
 * @param	int		$event_id	The event ID.
 * @return	int					The user ID of the event client.
 */
function mdjm_get_event_client_id( $event_id )	{
	if( empty( $event_id ) )	{
		return false;
	}

	$event = new MDJM_Event( $event_id );
	return $event->client;
} // mdjm_get_event_client_id

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
 * @return	str		URL to Client Zone page for the event.
 */
function mdjm_get_event_uri( $event_id )	{
	return add_query_arg( 'event_id', $event_id, mdjm_get_formatted_url( mdjm_get_option( 'app_home_page' ) ) );
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
	
	$meta = get_post_meta( $event_id, '_mdjm_event_data', true );
	
	foreach( $data as $key => $value )	{
		
		if( $key == 'mdjm_nonce' || $key == 'mdjm_action' || substr( $key, 0, 12 ) != '_mdjm_event_' ) {
			continue;
		}
		
		if( $key == '_mdjm_event_cost' || $key == '_mdjm_event_deposit' || $key == '_mdjm_event_dj_wage' )	{
			$value = mdjm_format_amount( $value );			
		} elseif( $key == '_mdjm_event_venue_postcode' && ! empty( $value ) )	{ // Postcodes are uppercase.
			$value = strtoupper( $value );
		} elseif( $key == '_mdjm_event_venue_email' && ! empty( $value ) )	{ // Emails are lowercase.
			$value = strtolower( $value );
		} elseif( $key == '_mdjm_event_package' && ! empty( $value ) )	{
			$value = sanitize_text_field( strtolower( $value ) );	
		} elseif( $key == '_mdjm_event_addons' && ! empty( $value ) )	{
			$value = $value;
		} elseif( ! strpos( $key, 'notes' ) && ! empty( $value ) )	{
			$value = sanitize_text_field( ucwords( $value ) );
		} elseif( ! empty( $value ) )	{
			$value = sanitize_text_field( ucfirst( $value ) );
		} else	{
			$value = '';
		}
		
		// If we have a value and the key did not exist previously, add it.
		if ( ! empty( $value ) && ( empty( $current_meta[ $key ] ) || empty( $current_meta[ $key ][0] ) ) )	{
			
			$debug[] = sprintf( __( 'Adding %s value as %s' ), mdjm_event_get_meta_label( $key ), is_array( $value ) ? var_export( $value, true ) : $value );
			add_post_meta( $event_id, $key, $value );
			
			$meta[ str_replace( '_mdjm_event', '', $key ) ] = $value;
			
		} elseif ( ! empty( $value ) && $value != $current_meta[ $key ][0] )	{ // If a value existed, but has changed, update it.
		
			$debug[] = sprintf( __( 'Updating %s with %s' ), mdjm_event_get_meta_label( $key ), is_array( $value ) ? var_export( $value, true ) : $value );
			update_post_meta( $event_id, $key, $value );
			
			$meta[ str_replace( '_mdjm_event', '', $key ) ] = $value;
			
		} elseif ( empty( $value ) && ! empty( $current_meta[ $key ][0] ) )	{ // If there is no new meta value but an old value exists, delete it.
		
			$debug[] = sprintf( __( 'Removing %s from %s' ), $current_meta[ $key ][0], mdjm_event_get_meta_label( $key ) );
			delete_post_meta( $event_id, $key, $value );
			
			if( isset( $meta[ str_replace( '_mdjm_event_', '', $key ) ] ) )	{
				unset( $meta[ str_replace( '_mdjm_event_', '', $key ) ] );
			}
			
		}
		
	}
	
	$update = update_post_meta( $event_id, '_mdjm_event_data', $meta );
	
	$journal_args = array(
		'user_id'          => is_user_logged_in() ? get_current_user_id() : 1,
		'event_id'         => $event_id,
		'comment_content'  => sprintf( __( '%s Updated', 'mobile-dj-manager' ) . ':<br />    %s',
								mdjm_get_label_singular(), implode( '<br />', $debug ) ),
		'comment_type'     => 'update-event'
	);
	
	mdjm_add_journal( $journal_args );
	
	do_action( 'mdjm_primary_employee_payment_status', $event_id, $current_meta, $data );
	do_action( 'mdjm_post_update_event_meta', $event_id, $current_meta, $data, $meta );
	
	if ( ! empty( $debug ) )	{
		
		foreach( $debug as $log )	{
			MDJM()->debug->log_it( $log, false );
		}
		
	}
	
	return $update;
	
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
	
	add_action( 'save_post_mdjm-event', 'mdjm_save_event_post', 10, 3 );
	
	// Meta updates
	$args['meta']['_mdjm_event_last_updated_by'] = is_user_logged_in() ? get_current_user_id() : 1;
	
	mdjm_update_event_meta( $event_id, $args['meta'] );
	
	// Email the client
	if( ! empty( $args['client_notices'] ) )	{
		mdjm_email_enquiry_accepted( $event_id );
	}
	
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