<?php
	defined( 'ABSPATH' ) or die( "Direct access to this page is disabled!!!" );
	
/**
 * Manage the venue posts
 *
 *
 *
 */
		
/**
 * Define the columns to be displayed for venue posts
 *
 * @since	0.5
 * @param	arr		$columns	Array of column names
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
		'event_count'	=> sprintf( __( '%s', 'mobile-dj-manager' ), mdjm_get_label_plural() ),
		'info'		   => __( 'Information', 'mobile-dj-manager' ),
		'details'	    => __( 'Details', 'mobile-dj-manager' ),
	);
	
	if( ! mdjm_employee_can( 'add_venues' ) && isset( $columns['cb'] ) )	{
		unset( $columns['cb'] );
	}
				
	return $columns;
} // mdjm_venue_post_columns
add_filter( 'manage_mdjm-venue_posts_columns' , 'mdjm_venue_post_columns' );

/**
 * Define which columns are sortable for venue posts
 *
 * @since	0.7
 * @param	arr		$sortable_columns	Array of transaction post sortable columns
 * @return	arr		$sortable_columns	Filtered Array of transaction post sortable columns
 */
function mdjm_venue_post_sortable_columns( $sortable_columns )	{
	
	$sortable_columns['town']	  = 'town';
	$sortable_columns['county']	= 'county';
	
	return $sortable_columns;
	
} // mdjm_venue_post_sortable_columns
add_filter( 'manage_edit-mdjm-venue_sortable_columns', 'mdjm_venue_post_sortable_columns' );

/**
 * Order posts.
 *
 * @since	1.3
 * @param	obj		$query		The WP_Query object
 * @return	void
 */
function mdjm_venue_post_order( $query )	{
	
	if ( ! is_admin() || 'mdjm-venue' != $query->get( 'post_type' ) )	{
		return;
	}
	
	switch( $query->get( 'orderby' ) )	{
		
		case 'town':
			$query->set( 'meta_key', '_venue_town' );
			$query->set( 'orderby',  'meta_value' );
            break;
			
		case 'county':
			$query->set( 'meta_key', '_venue_county' );
			$query->set( 'orderby',  'meta_value' );
            break;
		
	}
	
} // mdjm_venue_post_order
add_action( 'pre_get_posts', 'mdjm_venue_post_order' );

/**
 * Define the data to be displayed in each of the custom columns for the Venue post types
 *
 * @since	0.9
 * @param	str		$column_name	The name of the column to display
 * @param	int		$post_id		The current post ID
 * @return
 */
function mdjm_venue_posts_custom_column( $column_name, $post_id )	{
		
	switch ( $column_name ) {
		case 'contact':
			echo sprintf( 
				'<a href="mailto:%s">%s</a>', 
				get_post_meta( $post_id, '_venue_email', true ),
				stripslashes( get_post_meta( $post_id, '_venue_contact', true ) )
			);					
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
		
		// Event Count
		case 'event_count':
			$events_at_venue = get_posts( 
				array(
					'post_type'	=> 'mdjm-event',
					'meta_query'   => array(
						'key'	  => '_mdjm_event_venue_id',
						'value'    => $post_id,
						'type'     => 'NUMERIC'
					),
					'post_status'  => array( 'mdjm-approved', 'mdjm-contract', 'mdjm-completed', 'mdjm-enquiry', 'mdjm-unattended' )
				)
			);
			
			echo ! empty( $events_at_venue ) ? count( $events_at_venue ) : '0';
			break;	
		
		// Information
		case 'info':
			echo stripslashes( get_post_meta( $post_id, '_venue_information', true ) );
			break;
		
		// Details
		case 'details':
			$venue_terms	= get_the_terms( $post_id, 'venue-details' );
			$venue_term	 = '';
			
			if( !empty( $venue_terms ) )	{
				
				$venue_term .= '<ul class="details">' . "\r\n";
				
				foreach( $venue_terms as $v_term )	{
					$venue_term .= '<li>' . $v_term->name . '</li>' . "\r\n";	
				}
				
				$venue_term .= '</ul>' . "\r\n";
			}
			
			echo ! empty( $venue_term ) ? $venue_term : '';
			break;
	} // switch
	
} // mdjm_venue_posts_custom_column
add_action( 'manage_mdjm-venue_posts_custom_column' , 'mdjm_venue_posts_custom_column', 10, 2 );

/**
 * Customise the post row actions on the venue edit screen.
 *
 * @since	1.0
 * @param	arr		$actions	Current post row actions
 * @param	obj		$post		The WP_Post post object
 */
function mdjm_venue_post_row_actions( $actions, $post )	{
	
	if( $post->post_type != 'mdjm-venue' )	{
		return $actions;
	}
	
	if( isset( $actions['view'] ) )	{
		unset( $actions['view'] );
	}
	
	if( isset( $actions['inline hide-if-no-js'] ) )	{
		unset( $actions['inline hide-if-no-js'] );
	}

	return $actions;
	
} // mdjm_venue_post_row_actions
add_filter( 'post_row_actions', 'mdjm_venue_post_row_actions', 10, 2 );

/**
 * Remove the edit bulk action from the venue posts list
 *
 * @since	1.3
 * @param	arr		$actions	Array of actions
 * @return	arr		$actions	Filtered Array of actions
 */
function mdjm_venue_bulk_action_list( $actions )	{
	
	unset( $actions['edit'] );
	
	return $actions;
	
} // mdjm_venue_bulk_action_list
add_filter( 'bulk_actions-edit-mdjm-venue', 'mdjm_venue_bulk_action_list' );

/**
 * Remove the dropdown filters from the edit post screen.
 *
 * @since	1.3
 * @param
 * @param
 */
function mdjm_venue_remove_filters()	{
	
	if( ! isset( $_GET['post_type'] ) ||  $_GET['post_type'] != 'mdjm-venue' )	{
		return;
	}
	
	?>
	<style type="text/css">
        #posts-filter .tablenav select[name=m],
        #posts-filter .tablenav select[name=cat],
        #posts-filter .tablenav #post-query-submit{
            display:none;
        }
    </style>
    <?php
	
} // mdjm_venue_remove_filters
add_action( 'admin_head', 'mdjm_venue_remove_filters' );

/**
 * Set the post title placeholder for venues
 * 
 * @since	1.3
 * @param	str		$title		The post title
 * @return  str		$title		The filtered post title
 */
function mdjm_venue_title_placeholder( $title )	{
	global $post;
	
	if( !isset( $post ) || 'mdjm-venue' != $post->post_type )	{
		return $title;
	}
	
	return __( 'Enter Venue name here...', 'mobile-dj-manager' );

} // mdjm_venue_title_placeholder
add_filter( 'enter_title_here', 'mdjm_venue_title_placeholder' );

/**
 * Rename the Publish and Update post buttons for venues
 *
 * @since	1.3
 * @param	str		$translation	The current button text translation
 * @param	str		$text			The text translation for the button
 * @return	str		$translation	The filtererd text translation
 */
function mdjm_venue_rename_publish_button( $translation, $text )	{
	
	global $post;
	
	if( ! isset( $post ) || 'mdjm-venue' != $post->post_type )	{
		return $translation;
	}
			
	if( $text == 'Publish' )	{
		return __( 'Save Venue', 'mobile-dj-manager' );
	} elseif( $text == 'Update' )	{
		return __( 'Update Venue', 'mobile-dj-manager' );
	} else
		return $translation;
	
} // mdjm_venue_rename_publish_button
add_filter( 'gettext', 'mdjm_venue_rename_publish_button', 10, 2 );

/**
 * Save the meta data for the venue
 *
 * @since	1.3
 * @param	int		$post_id		The current post ID.
 * @param	obj		$post			The current post object (WP_Post).
 * @param	bool	$update			Whether this is an existing post being updated or not.
 * @return	void
 */
function mdjm_save_venue_post( $post_id, $post, $update )	{
	
	if( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE )	{
		return;
	}
	
	if ( $post->post_status == 'trash' )	{
		return;
	}
	
	if( empty( $update ) )	{
		return;
	}
		
	// Permission Check
	if( ! mdjm_employee_can( 'add_venues' ) )	{
		
		if( MDJM_DEBUG == true )	{
			MDJM()->debug->log_it( 'PERMISSION ERROR: User ' . get_current_user_id() . ' is not allowed to edit venues' );
		}
		 
		return;
	}
	
	// Remove the save post action to avoid loops
	remove_action( 'save_post_mdjm-venue', 'mdjm_save_venue_post', 10, 3 );
	
	// Fire our pre-save hook
	do_action( 'mdjm_before_venue_save', $post_id, $post, $update );
				
	// Loop through all fields sanitizing and updating as required	
	foreach( $_POST as $meta_key => $new_meta_value )	{
		
		// We're only interested in 'venue_' fields
		if( substr( $meta_key, 0, 6 ) == 'venue_' )	{
			
			$current_meta_value = get_post_meta( $post_id, '_' . $meta_key, true );
			
			if( $meta_key == 'venue_postcode' && !empty( $new_meta_value ) )	{
				$new_meta_value = strtoupper( $new_meta_value );
			} elseif( $meta_key == 'venue_email' && !empty( $new_meta_value ) )	{
				$new_meta_value = sanitize_email( $new_meta_value );
			} else	{
				$new_meta_value = sanitize_text_field( ucwords( $new_meta_value ) );
			}
			
			// If we have a value and the key did not exist previously, add it
			if ( !empty( $new_meta_value ) && empty( $current_meta_value ) )	{
				add_post_meta( $post_id, '_' . $meta_key, $new_meta_value, true );
			}
			
			/* -- If a value existed, but has changed, update it -- */
			elseif ( !empty( $new_meta_value ) && $new_meta_value != $current_meta_value )	{
				update_post_meta( $post_id, '_' . $meta_key, $new_meta_value );
			}
				
			/* If there is no new meta value but an old value exists, delete it. */
			elseif ( empty( $new_meta_value ) && !empty( $current_meta_value ) )	{
				delete_post_meta( $post_id, '_' . $meta_key, $meta_value );
			}
		}
	}
	
	// Fire our post save hook
	do_action( 'mdjm_after_venue_save', $post_id, $post, $update );
	
	// Re-add the save post action to avoid loops
	add_action( 'save_post_mdjm-venue', 'mdjm_save_venue_post', 10, 3 );
	
}
add_action( 'save_post_mdjm-venue', 'mdjm_save_venue_post', 10, 3 );

/**
 * Customise the messages associated with managing venue posts
 *
 * @since	1.3
 * @param	arr		$messages	The current messages
 * @return	arr		$messages	Filtered messages
 */
function mdjm_venue_post_messages( $messages )	{
	
	global $post;
	
	if( 'mdjm-venue' != $post->post_type )	{
		return $messages;
	}
	
	$url1 = '<a href="' . admin_url( 'edit.php?post_type=mdjm-venue' ) . '">';
	$url2 = get_post_type_object( $post->post_type )->labels->singular_name;
	$url3 = '</a>';
	
	$messages['mdjm-venue'] = array(
		0 => '', // Unused. Messages start at index 1.
		1 => sprintf( __( '%2$s updated. %1$s%2$s List%3$s.', 'mobile-dj-manager' ), $url1, $url2, $url3 ),
		4 => sprintf( __( '%2$s updated. %1$s%2$s List%3$s.', 'mobile-dj-manager' ), $url1, $url2, $url3 ),
		6 => sprintf( __( '%2$s added. %1$s%2$s List%3$s.', 'mobile-dj-manager' ), $url1, $url2, $url3 ),
		7 => sprintf( __( '%2$s saved. %1$s%2$s List%3$s.', 'mobile-dj-manager' ), $url1, $url2, $url3 )
	);
	
	return apply_filters( 'mdjm_venue_post_messages', $messages );
	
} // mdjm_venue_post_messages
add_filter( 'post_updated_messages','mdjm_venue_post_messages' );
