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
		'post_title'   => $name,
		'post_content' => '',
		'post_type'    => 'mdjm-venue',
		'post_author'  => get_current_user_id(),
		'post_status'  => 'publish'
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
		return false;
	}
	
	if( ! empty( $venue_meta ) )	{
		
		foreach( $venue_meta as $key => $value )	{
			
			if ( $key == 'venue_email' && ! is_email( $value ) )	{
				continue;
			}
			
			if( ! empty( $value ) && $key != 'venue_name' )	{
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
		'post_status'  => 'publish',
		'orderby'	  => 'title',
		'order'		=> 'ASC',
		'numberposts'  => -1
	);
	
	$args = wp_parse_args( $args, $defaults );
	
	$venues = get_posts( $args );
	
	return apply_filters( 'mdjm_get_venues', $venues );

} // mdjm_get_venues

/**
 * Retrieve all venue meta data for the given event
 *
 * @since	1.3
 * @param	int		$item_id		Required: The post ID of the venue or the event.
 * @param	str		$field			Optional: The meta field to retrieve. Default to all (empty).
 * @return	arr		Array of all venue data
 */
function mdjm_get_event_venue_meta( $item_id, $field='' )	{

	$prefix = '';

	if ( 'mdjm-venue' == get_post_type( $item_id ) )	{
		$venue_id = $item_id;
	} else	{
		$venue_id = get_post_meta( $item_id, '_mdjm_event_venue_id', true );
		$prefix   = '_mdjm_event';
	}

	if ( empty( $venue_id ) )	{
		return;
	}

	switch( $field )	{
		case 'address' :
			$return[] = get_post_meta( $item_id, $prefix . '_venue_address1', true );
			$return[] = get_post_meta( $item_id, $prefix . '_venue_address2', true );
			$return[] = get_post_meta( $item_id, $prefix . '_venue_town', true );
			$return[] = get_post_meta( $item_id, $prefix . '_venue_county', true );
			$return[] = get_post_meta( $item_id, $prefix . '_venue_postcode', true );

			if ( ! empty( $return ) )	{
				$return = array_filter( $return );
			}
			break;

		case 'address1' :
			$return = get_post_meta( $item_id, $prefix . '_venue_address1', true );
			break;
			
		case 'address2' :
			$return = get_post_meta( $item_id, $prefix . '_venue_address2', true );
			break;

		case 'town' :
			$return = get_post_meta( $item_id, $prefix . '_venue_town', true );
			break;

		case 'county' :
			$return = get_post_meta( $item_id, $prefix . '_venue_county', true );
			break;

		case 'postcode' :
			$return = get_post_meta( $item_id, $prefix . '_venue_postcode', true );
			break;
		
		case 'contact' :
			$return = get_post_meta( $item_id, $prefix . '_venue_contact', true );
			break;
		
		case 'details' :
			$return = mdjm_get_venue_details( $item_id );
			break;
		
		case 'email' :
			$return = get_post_meta( $item_id, $prefix . '_venue_email', true );
			break;
		
		case 'name' :
			$return = empty( $prefix ) ? get_the_title( $item_id ) : get_post_meta( $item_id, $prefix . '_venue_name', true );
			break;
		
		case 'notes' :
			$return = get_post_meta( $item_id, $prefix . '_venue_information', true );
			break;
		
		case 'phone' :
			$return = get_post_meta( $item_id, $prefix . '_venue_phone', true );
			break;
		
		default :
			$return = '';
		break;
	}

	if ( ! empty( $return ) ) 	{
		if ( is_array( $return ) )	{
			$return = array_map( 'stripslashes', $return );
			$return = array_map( 'esc_html', $return );
		} else	{
			$return = esc_html( stripslashes( trim( $return ) ) );
		}
	}

	return empty ( $return ) ? '' : $return;
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
	
	$venue_details = array();

	if ( $details )	{
		foreach( $details as $detail ) 	{
			$venue_details[] = $detail->name;
		}
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

/**
 * Output the venues details.
 *
 * @since	1.3.7
 * @param	int		$venue_id	Venue ID
 * @param	int		$event_id	Event ID
 * @return	str
 */
function mdjm_do_venue_details_table( $venue_id = '', $event_id = '' )	{

	if ( empty( $venue_id ) && empty( $event_id ) )	{
		return;
	} else	{
		if ( empty ( $venue_id ) )	{
			$venue_id = $event_id;
		}
	}

	$venue_name     = mdjm_get_event_venue_meta( $venue_id, 'name' );
	$venue_contact  = mdjm_get_event_venue_meta( $venue_id, 'contact' );
	$venue_email    = mdjm_get_event_venue_meta( $venue_id, 'email' );
	$venue_address  = mdjm_get_event_venue_meta( $venue_id, 'address' );
	$venue_town     = mdjm_get_event_venue_meta( $venue_id, 'town' );
	$venue_county   = mdjm_get_event_venue_meta( $venue_id, 'county' );
	$venue_postcode = mdjm_get_event_venue_meta( $venue_id, 'postcode' );
	$venue_phone    = mdjm_get_event_venue_meta( $venue_id, 'phone' );
	$venue_notes    = mdjm_get_event_venue_meta( $venue_id, 'notes' );
	$venue_details  = mdjm_get_venue_details( $venue_id );
	$employee_id    = ! empty( $event_id ) ? mdjm_get_event_primary_employee_id( $event_id ) : '';

	if ( empty( $employee_id ) )	{
		$employee_id = get_current_user_id();
	}

	?>
    <div id="mdjm-event-venue-details" class="mdjm-hidden">
        <table class="widefat mdjm_event_venue_details mdjm_form_fields">
        	<thead>
            	<tr>
                	<th colspan="3"><?php printf( __( 'Details for %s', 'mobile-dj-manager' ),
						! empty( $venue_name ) ? $venue_name : '' ); ?></th>
                </tr>
            </thead>
            <tbody>
            	<?php do_action( 'mdjm_venue_details_table_before_contact_name', $venue_id = '', $event_id = '' ); ?>
            	<tr>
                	<td><i class="fa fa-user" aria-hidden="true" title="<?php _e( 'Contact Name', 'mobile-dj-manager' ); ?>"></i>
                    <?php echo ! empty( $venue_contact ) ? $venue_contact : ''; ?></td>

                	<td rowspan="3"><?php echo ! empty( $venue_address ) ? implode( '<br />', $venue_address ) : ''; ?></td>
                    <td rowspan="3"><?php echo ! empty( $venue_details ) ? implode( '<br />', $venue_details ) : ''; ?></td>
           		</tr>

				<?php do_action( 'mdjm_venue_details_table_after_contact_name', $venue_id = '', $event_id = '' ); ?>

                <tr>
                	<td><i class="fa fa-phone" aria-hidden="true" title="<?php _e( 'Phone', 'mobile-dj-manager' ); ?>"></i>
                    <?php echo ! empty( $venue_phone ) ? $venue_phone : ''; ?></td>
				</tr>

				<?php do_action( 'mdjm_venue_details_table_after_contact_phone', $venue_id = '', $event_id = '' ); ?>

				<?php $email = ! empty( $venue_email ) ? $venue_email : ''; ?>

				<tr>
                	<td><i class="fa fa-envelope-o" aria-hidden="true" title="<?php _e( 'Email', 'mobile-dj-manager' ); ?>"></i>
                    <a href="mailto:<?php echo $email; ?>"><?php echo $email; ?></a></td>                  	
           		</tr>

				<?php do_action( 'mdjm_venue_details_table_after_contact_email', $venue_id = '', $event_id = '' ); ?>

               <tr>
                	<td><i class="fa fa-comments-o" aria-hidden="true" title="<?php _e( 'Information', 'mobile-dj-manager' ); ?>"></i>
                    <?php echo ! empty( $venue_notes ) ? $venue_notes : ''; ?></td>                  	
           		</tr>

				<?php do_action( 'mdjm_after_venue_notes', $venue_address, $employee_id ); ?>

            </tbody>
        </table>
    </div>

    <?php

} // mdjm_do_venue_details_table
