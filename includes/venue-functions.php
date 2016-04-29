<?php
/**
 * Contains all venue related functions
 *
 * @package		MDJM
 * @subpackage	Venues
 * @since		1.3
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) )
	exit;

/**
 * Adds a new venue.
 *
 * @since	1.3
 * @param	str			$venue_name		The name of the venue.
 * @param	arr			$venue_meta		Meta data for the venue.
 * @return	int|bool	$venue_id		Post ID of the new venue or false on failure.
 */
function mdjm_add_venue( $venue_name = '', $venue_meta = array() )	{
	
	if( ! mdjm_employee_can( 'add_venues' ) )	{
		return false;
	}
	
	if( empty( $venue_name ) && empty( $_POST['venue_name'] ) )	{
		return false;
	} elseif( ! empty( $venue_name ) )	{
		$name = $venue_name;
	} else	{
		$name = $_POST['venue_name'];
	}
	
	$args = array(
		'post_title'	 => $name,
		'post_content'   => '',
		'post_type'	  => 'mdjm-venue',
		'post_author'	=> get_current_user_id(),
		'post_status'	=> 'publish'
	);
	
	// Remove the save post hook for venue posts to avoid loops
	remove_action( 'save_post_mdjm-venue', 'mdjm_save_venue_post', 10, 3 );
	
	/**
	 * Allow filtering of the venue post data
	 *
	 * @since	1.3
	 * @param	arr		$args	Array of user data
	 */
	$args = apply_filters( 'mdjm_add_venue', $args );
	
	/**
	 * Fire the `mdjm_pre_add_venue` action.
	 *
	 * @since	1.3
	 * @param	str		$name		Name of venue
	 * @param	arr		$venue_meta	Array of venue meta data
	 */
	do_action( 'mdjm_pre_add_venue', $name, $venue_meta );
	
	$venue_id = wp_insert_post( $args );
	
	if( empty( $venue_id ) )	{
		
		$debug[] = 'Adding new venue failed';
		
		return false;
		
	}
	
	$debug = sprintf( 'New venue %s (ID %s) added', $name, $venue_id );
	
	if( ! empty( $venue_meta ) )	{
		
		foreach( $venue_meta as $key => $value )	{
			
			if( !empty( $value ) && $key != 'venue_name' )	{
				
				$debug[] = sprintf( 'Updating venue %s. Adding %s with value of %s.', $name, $key, $value );
				
				add_post_meta( $venue_id, '_' . $key, $value );
				
			}
			
		}
		
	}
	
	/**
	 * Fire the `mdjm_post_add_venue` action.
	 *
	 * @since	1.3
	 * @param	str		$venue_id	Post ID of new venue
	 */
	do_action( 'mdjm_post_add_venue', $venue_id );
	
	// Re-add the save post hook for venue posts
	add_action( 'save_post_mdjm-venue', 'mdjm_save_venue_post', 10, 3 );
	
	if( ! empty( $debug ) && MDJM_DEBUG == true )	{
		
		$true = true;
		
		foreach( $debug as $log )	{
			MDJM()->debug->log_it( $log, $true );
			$true = false;
		}
		
	}
	
	return $venue_id;
	
} // mdjm_add_venue

/**
 * Retrieve all venues
 *
 * @since	1.3
 * @param	arr		$args	Array of options to pass to get_posts. See $defaults.
 * @return	obj		Post objects for all venues.
 */
function mdjm_get_venues( $args = array() )	{
	
	$defaults = array(
		'post_type'	=> 'mdjm-venue',
		'orderby'	  => 'title',
		'order'		=> 'ASC',
		'numberposts'  => -1
	);
	
	$args = wp_parse_args( $args, $defaults );
	
	return get_posts( $args );

} // mdjm_get_venues

/**
 * Retrieve all venue meta data for the given event
 *
 * @since	1.3
 * @param	int		$id				Required: The post ID of the venue or the event.
 * @param	str		$field			Optional: The meta field to retrieve. Default to all (empty).
 * @return	arr		Array of all venue data
 */
function mdjm_get_event_venue_meta( $id, $field='' )	{
	$prefix = 'mdjm-venue' == get_post_type( $id ) ? '' : '_mdjm_event';
	
	switch( $field )	{
		case 'address' :
			$return[] = get_post_meta( $id, $prefix . '_venue_address1', true );
			$return[] = get_post_meta( $id, $prefix . '_venue_address2', true );
			$return[] = get_post_meta( $id, $prefix . '_venue_town', true );
			$return[] = get_post_meta( $id, $prefix . '_venue_county', true );
			$return[] = get_post_meta( $id, $prefix . '_venue_postcode', true );
			
			$return = array_filter( $return );
		break;
		
		case 'town' :
			$return = get_post_meta( $id, $prefix . '_venue_town', true );
		break;
		
		case 'contact' :
			$return = get_post_meta( $id, $prefix . '_venue_contact', true );
		break;
		
		case 'details' :
			$return = mdjm_get_venue_details( $id );
		break;
		
		case 'email' :
			$return = get_post_meta( $id, $prefix . '_venue_email', true );
		break;
		
		case 'name' :
			$return = empty( $prefix ) ? get_post_title( $id ) : get_post_meta( $id, $prefix . '_venue_name', true );
		break;
		
		case 'notes' :
			$return = get_post_meta( $id, $prefix . '_venue_information', true );
		break;
		
		case 'phone' :
			$return = get_post_meta( $id, $prefix . '_venue_phone', true );
		break;
		
		default :
			
		break;
	}
	
	return $return;
} // mdjm_get_event_venue_meta

/**
 * Retrieve all details for the given venue.
 *
 * @since	1.3
 * @param	int		$venue_id		Required: The post ID of the venue.
 * @return	arr		Array of all venue detail labels.
 */
function mdjm_get_venue_details( $venue_id )	{
	$details = wp_get_object_terms( $venue_id, 'venue-details' );
	
	foreach( $details as $detail ) 	{
		$venue_details[] = $detail->name;
	}
	
	return $venue_details;
} // mdjm_get_venue_details

/**
 * Display a select list for venues.
 *
 * @since	1.3
 * @param	arr		$args	See $defaults.
 * @return	str		The select list.
 */
function mdjm_venue_dropdown( $args = array() )	{
	
	$defaults = array(
		'name'                => '_mdjm_event_venue',
		'id'                  => '',
		'selected'            => '',
		'first_entry'         => '',
		'first_entry_value'   => '',
		'class'               => '',
		'required'            => false,
		'echo'                => true
	);
	
	$args = wp_parse_args( $args, $defaults );
	
	$args['id'] = ! empty( $args['id'] ) ? $args['id'] : $args['name'];
	$required   = ! empty( $args['required'] ) ? ' required' : '';
	
	$output = '';
	
	$venues = mdjm_get_venues();
	
	$output .= '<select name="' . $args['name'] . '" id="' . $args['name'] . '" class="' . $args['class'] . '"' . $required . '>';
	
	if ( ! empty( $args['first_entry'] ) )	{
		$output .= '<option value="' . $args['first_entry_value'] . '">' . esc_attr( $args['first_entry'] ) . '</option>';
	}
	
	if ( ! empty( $venues ) )	{
		foreach ( $venues as $venue )	{
			$address  = mdjm_get_event_venue_meta( $venue->ID, 'address' );
			$town     = mdjm_get_event_venue_meta( $venue->ID, 'town' );
			$option   = esc_attr( $venue->post_title );
			$option  .= ! empty( $town )             ? ' (' . esc_attr( $town ) . ')'                   : '';
			$title    = ! empty ( $address )         ? implode( "\n", $address )                        : '';			
			$selected = ! empty( $args['selected'] ) ? selected( $args['selected'], $venue->ID, false ) : '';
			
			$output .= '<option value="' . $venue->ID . '" title="' . $title . '"' . $selected . '>' . $option . '</option>';
		}
	} else	{
		$output .= '<option value="" disabled="disabled">' . apply_filters( 'mdjm_no_venues', __( 'No venues exist', 'mobile-dj-manager' ) ) . '</option>';
	}
	
	$output .= '</select>';
	
	if ( ! empty( $args['echo'] ) )	{
		echo $output;
	} else	{
		return $output;
	}
	
} // mdjm_venue_dropdown