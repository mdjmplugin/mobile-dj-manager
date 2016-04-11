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
	
	if( !empty( $event_id ) )	{
		$id = $event_id;
	}
	elseif( !empty( $post_id ) )	{
		$id = $post_id;
	}
	elseif( !empty( $post ) )	{
		$id = $post->ID;
	}
	else	{
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
	global $mdjm, $post;
	
	$defaults = array(
		'name'				 => 'mdjm_event_status',
		'id'				   => 'mdjm_event_status',
		'selected'			 => ! empty( $post ) ? $post->post_status : 'mdjm-unattended',
		'first_entry'		  => '',
		'first_entry_value'	=> '0',
		'small'				=> false,
		'return_type'		  => 'list'
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
 * Set the event type label for given event ID.
 *
 * @since	1.3
 * @param	int			$event_id	Event ID.
 * @param	int|arr		$type		The term ID of the category to set for the event.
 * @return	bool		True on success, or false.
 */
function mdjm_set_event_type( $event_id, $type )	{
	
	if ( ! is_array( $type ) )	{
		$type = array( $type );
	}
	
	(int)$event_id;
	
	$set_event_terms = wp_set_object_terms( $event_id, $type, 'event-types', false );
	
	if( is_wp_error( $set_event_terms ) )	{
		MDJM()->debug->log_it( sprintf( 'Unable to assign term ID %d to Event %d: %s', $type, $event_id, $set_event_terms->get_error_message() ), true );
	}
	
	return;
} // mdjm_get_event_type

/**
 * Return the event type label for given event ID.
 *
 * @since	1.3
 * @param	int		$event_id	Optional: ID of the current event. If not set, check for global $post and $post_id.
 * @return	str		Label for current event type.
 */
function mdjm_get_event_type( $event_id='' )	{
	global $post, $post_id;
	
	if( !empty( $event_id ) )	{
		$id = $event_id;
	}
	elseif( !empty( $post_id ) )	{
		$id = $post_id;
	}
	elseif( !empty( $post ) )	{
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
	return $event->get_client();
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
 * @return	void
 */
function mdjm_add_event_meta( $event_id, $data )	{
	
	// For backwards compatibility
	$current_meta = get_post_meta( $event_id );
	
	$meta = get_post_meta( $event_id, '_mdjm_event_data', true );
	
	foreach( $data as $key => $value )	{
		
		if( $key == 'mdjm_nonce' || $key == 'mdjm_action' ) {
			continue;
		}
		
		if( $key == '_mdjm_event_cost' || $key == '_mdjm_event_deposit' || $key == '_mdjm_event_dj_wage' )	{
			$value = mdjm_format_amount( $value );
		} elseif( $key == 'venue_postcode' && ! empty( $value ) )	{ // Postcodes are uppercase.
			$value = strtoupper( $value );
		} elseif( $key == 'venue_email' && ! empty( $value ) )	{ // Emails are lowercase.
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
			
			$debug[] = sprintf( __( 'Adding %s value as %s' ), $key, $value );
			add_post_meta( $event_id, $key, $value );
			
			$meta[ str_replace( '_mdjm_event', '', $key ) ] = $value;
			
		} elseif ( ! empty( $value ) && $value != $current_meta[ $key ][0] )	{ // If a value existed, but has changed, update it.
		
			$debug[] = sprintf( __( 'Updating %s value as %s' ), $key, $value );
			update_post_meta( $event_id, $key, $value );
			
			$meta[ str_replace( '_mdjm_event', '', $key ) ] = $value;
			
		} elseif ( empty( $value ) && ! empty( $current_meta[ $key ][0] ) )	{ // If there is no new meta value but an old value exists, delete it.
		
			$debug[] = sprintf( __( 'Removing %s' ), $current_meta[ $key ][0] );
			delete_post_meta( $event_id, $key, $value );
			
			if( isset( $meta[ str_replace( '_mdjm_event', '', $key ) ] ) )	{
				unset( $meta[ str_replace( '_mdjm_event', '', $key ) ] );
			}
			
		}
		
	}
	
	update_post_meta( $event_id, '_mdjm_event_data', $meta );
	
	if ( ! empty( $debug ) )	{
		
		foreach( $debug as $log )	{
			MDJM()->debug->log_it( $log, false );
		}
		
	}
	
} // mdjm_add_event_meta

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
	
	if ( ! employee_can( 'manage_events' ) )	{
		return false;
	}
	
	if ( $new_status == $old_status )	{
		return false;
	}
	
	do_action( 'mdjm_pre_event_status_change', $event_id, $new_status, $old_status, $args );
	
	do_action( "mdjm_pre_update_event_status_{$new_status}", $event_id, $old_status, $args );
	
	$func = 'mdjm_set_event_status_' . str_replace( '-', '_', $new_status );
	
	$result = $func( $event_id, $old_status, $args );
	
	do_action( "mdjm_post_update_event_status_{$new_status}", $event_id, $old_status, $args );
	
	do_action( 'mdjm_post_event_status_change', $event_id, $new_status, $old_status, $args );
	
	return $result;
	
} // mdjm_update_event_status

/**
 * Update event status to mdjm-unattended.
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
	
	mdjm_add_event_meta( $event_id, $args['meta'] );
		
	return $update;
	
} // mdjm_set_event_status_mdjm_unattended

/**
 * Update event status to mdjm-enquiry.
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
	
	mdjm_add_event_meta( $event_id, $args['meta'] );
	
	// Email the client
	if( ! empty( $args['client_notices'] ) )	{
		mdjm_email_quote( $event_id );
	}
	
	// Generate an online quote that is visible via the Client Zone
	if( ! empty( mdjm_get_option( 'online_enquiry', false ) ) || ! empty( $args['online_quote'] ) )	{
		
	}
		
	return $update;
	
} // mdjm_set_event_status_mdjm_enquiry

/**
 * Update event status to mdjm-contract.
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
	
	mdjm_add_event_meta( $event_id, $args['meta'] );
	
	// Email the client
	if( ! empty( $args['client_notices'] ) )	{
		mdjm_email_enquiry_accepted( $event_id );
	}
	
	return $update;
	
} // mdjm_set_event_status_mdjm_contract

/**
 * Update event status to mdjm-approved.
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
	
	mdjm_add_event_meta( $event_id, $args['meta'] );
	
	// Email the client
	if( ! empty( $args['client_notices'] ) )	{
		mdjm_email_booking_confirmation( $event_id );
	}
	
	return $update;
	
} // mdjm_set_event_status_mdjm_approved

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
			'numberposts'		=> 1,
			'post_parent'		=> $event_id,
			'post_type'		  => 'mdjm-quotes'
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
 * @since	1.3
 * @param	int			$event_id	The event ID.
 * @return	int			Quote post ID false if no quote exists
 */
function mdjm_generate_online_quote( $event_id )	{
	
} // mdjm_generate_online_quote