<?php
	defined( 'ABSPATH' ) or die( "Direct access to this page is disabled!!!" );
	
/**
 * Class Name: MDJM_Venue_Posts
 * Manage the Venue posts
 *
 *
 *
 */
		
/**
 * Define the columns to be displayed for venue posts
 *
 * @params	arr		$columns	Array of column names
 *
 * @return	arr		$columns	Filtered array of column names
 */
function mdjm_venue_post_columns( $columns ) {
	$columns = array(
			'cb'			 => '<input type="checkbox" />',
			'title' 	 	  => __( 'Venue', 'mobile-dj-manager' ),
			'contact'		=> __( 'Contact', 'mobile-dj-manager' ),
			'phone'		  => __( 'Phone', 'mobile-dj-manager' ),
			'town'		   => __( 'Town', 'mobile-dj-manager' ),
			'county'   		 => __( 'County', 'mobile-dj-manager' ),
			'event_count'	=> __( 'Events', 'mobile-dj-manager' ),
			'info'		   => __( 'Information', 'mobile-dj-manager' ),
			'details'	    => __( 'Details', 'mobile-dj-manager' ),
		);
	
	return $columns;
} // mdjm_venue_post_columns
add_filter( 'manage_mdjm-venue_posts_columns' , 'mdjm_venue_post_columns' );
		
/**
 * Define which columns are sortable for venue posts
 *
 * @params	arr		$sortable_columns	Array of event post sortable columns
 *
 * @return	arr		$sortable_columns	Filtered Array of event post sortable columns
 */
function mdjm_venue_post_sortable_columns( $sortable_columns )	{
	$sortable_columns['town'] = 'town';
	$sortable_columns['county'] = 'county';
	
	return $sortable_columns;
} // mdjm_venue_post_sortable_columns
add_filter( 'manage_edit-mdjm-venue_sortable_columns', 'mdjm_venue_post_sortable_columns' );
		
/**
 * Define the data to be displayed in each of the custom columns for the Event post types
 *
 * @param	str		$column_name	The name of the column to display
 *			int		$post_id		The current post ID
 * 
 *
 */
function mdjm_venue_posts_custom_column( $column_name, $post_id )	{				
	switch ( $column_name ) {
		case 'contact':
			echo sprintf( 
				'<a href="mailto:%s">%s</a>', 
				get_post_meta( $post_id,
				'_venue_email', true ),
				stripslashes( get_post_meta( $post_id, '_venue_contact', true ) ) );					
			break;
		
		// Phone
		case 'phone':
			echo get_post_meta( $post_id, '_venue_phone', true );
			break;
		
		// Town
		case 'town':
			echo get_post_meta( $post_id, '_venue_town', true );
			break;
			
		// County
		case 'county':
			echo get_post_meta( $post_id, '_venue_county', true );
			break;
		
		case 'event_count':
			$events_at_venue = get_posts( 
				array(
					'post_type'	=> MDJM_EVENT_POSTS,
					'meta_key'	 => '_mdjm_event_venue_id',
					'meta_value'   => $post_id,
					'post_status'  => array( 'mdjm-approved', 'mdjm-contract', 'mdjm-completed', 'mdjm-enquiry', 'mdjm-unattended' ) ) );
			
			echo( !empty( $events_at_venue ) ? count( $events_at_venue ) : '0' );
			break;	
		// Information
		case 'info':
			echo stripslashes( get_post_meta( $post_id, '_venue_information', true ) );
			break;
		
		// Details
		case 'details':
			$venue_terms = get_the_terms( $post_id, 'venue-details' );
			$venue_term = '';
			if( !empty( $venue_terms ) )	{
				$venue_term .= '<ul class="details">' . "\r\n";
				foreach( $venue_terms as $v_term )	{
					$venue_term .= '<li>' . $v_term->name . '</li>' . "\r\n";	
				}
				$venue_term .= '</ul>' . "\r\n";
			}
			echo ( !empty( $venue_term ) ? $venue_term : '' );
			break;
	} // switch ( $column_name )
} // mdjm_venue_posts_custom_column
add_action( 'manage_mdjm-venue_posts_custom_column' , 'mdjm_venue_posts_custom_column', 10, 2 );
		
/**
 * Remove the edit bulk action from the venue posts list
 *
 * @params	arr		$actions	Array of actions
 *
 * @return	arr		$actions	Filtered Array of actions
 */
function mdjm_venue_bulk_action_list( $actions )	{
	unset( $actions['edit'] );
	
	return $actions;
} // mdjm_venue_bulk_action_list
add_filter( 'bulk_actions-edit-mdjm-venue', 'mdjm_venue_bulk_action_list' );

/**
 * Save the meta data for the venue
 *
 * @called	save_post_mdjm-venue
 *
 * @param	int		$ID				The current post ID.
 *			obj		$post			The current post object (WP_Post).
 *			bool	$update			Whether this is an existing post being updated or not.
 * 
 * @return	void
 */
function mdjm_save_venue_post( $ID, $post, $update )	{
	if( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE )
		return;
	
	if( empty( $update ) )
		return;
		
	// Permission Check
	if( !MDJM()->permissions->employee_can( 'add_venues' ) )	{
		if( MDJM_DEBUG == true )
			MDJM()->debug->log_it( 'PERMISSION ERROR: User ' . get_current_user_id() . ' is not allowed to edit venues' );
		 
		return;
	}
	
	// Remove the save post action to avoid loops
	remove_action( 'save_post_mdjm-venue', 'mdjm_save_venue_post', 10, 3 );
	
	// Fire our pre-save hook
	do_action( 'mdjm_before_venue_save', $ID, $post, $update );
				
	// Loop through all fields sanitizing and updating as required	
	foreach( $_POST as $meta_key => $new_meta_value )	{
		// We're only interested in 'venue_' fields
		if( substr( $meta_key, 0, 6 ) == 'venue_' )	{
			$current_meta_value = get_post_meta( $ID, '_' . $meta_key, true );
			
			if( $meta_key == 'venue_postcode' && !empty( $new_meta_value ) )
				$new_meta_value = strtoupper( $new_meta_value );
			
			if( $meta_key == 'venue_email' && !empty( $new_meta_value ) )
				$new_meta_value = sanitize_email( $new_meta_value );
				
			else
				$new_meta_value = sanitize_text_field( ucwords( $new_meta_value ) );
			
			// If we have a value and the key did not exist previously, add it
			if ( !empty( $new_meta_value ) && empty( $current_meta_value ) )
				add_post_meta( $ID, '_' . $meta_key, $new_meta_value, true );
			
			/* -- If a value existed, but has changed, update it -- */
			elseif ( !empty( $new_meta_value ) && $new_meta_value != $current_meta_value )
				update_post_meta( $ID, '_' . $meta_key, $new_meta_value );
				
			/* If there is no new meta value but an old value exists, delete it. */
			elseif ( empty( $new_meta_value ) && !empty( $current_meta_value ) )
				delete_post_meta( $ID, '_' . $meta_key, $meta_value );
		}
	}
	
	// Fire our post save hook
	do_action( 'mdjm_after_venue_save', $ID, $post, $update );
	
	// Re-add the save post action to avoid loops
	add_action( 'save_post_mdjm-venue', 'mdjm_save_venue_post', 10, 3 );
	
} // mdjm_save_venue_post
add_action( 'save_post_mdjm-venue', 'mdjm_save_venue_post', 10, 3 );
?>