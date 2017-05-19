<?php
	defined( 'ABSPATH' ) or die( "Direct access to this page is disabled!!!" );
	
/**
 * Manage the contract template posts
 *
 *
 *
 */
		
/**
 * Define the columns to be displayed for contract template posts
 *
 * @since	0.5
 * @param	arr		$columns	Array of column names
 * @return	arr		$columns	Filtered array of column names
 */
function mdjm_contract_post_columns( $columns ) {
		
	$columns = array(
		'cb'			=> '<input type="checkbox" />',
		'title'			=> __( 'Contract Name', 'mobile-dj-manager' ),
		'event_default'	=> __( 'Is Default?', 'mobile-dj-manager' ),
		'assigned'		=> __( 'Assigned To', 'mobile-dj-manager' ),
		'author'		=> __( 'Created By', 'mobile-dj-manager' ),
		'date'			=> __( 'Date', 'mobile-dj-manager' )
	);
		
	if( ! mdjm_employee_can( 'manage_templates' ) && isset( $columns['cb'] ) )	{
		unset( $columns['cb'] );
	}
				
	return $columns;
} // mdjm_contract_post_columns
add_filter( 'manage_contract_posts_columns' , 'mdjm_contract_post_columns' );

/**
 * Define the data to be displayed in each of the custom columns for the Contract post types
 *
 * @since	0.9
 * @param	str		$column_name	The name of the column to display
 * @param	int		$post_id		The current post ID
 * @return
 */
function mdjm_contract_posts_custom_column( $column_name, $post_id )	{
				
	switch( $column_name ) {
		// Is Default?
		case 'event_default':
			$event_default = mdjm_get_option( 'default_contract' );
			
			if ( $event_default == $post_id )	{
				echo '<span style="color: green; font-weight: bold;">' . __( 'Yes', 'mobile-dj-manager' );
			} else	{
				_e( 'No', 'mobile-dj-manager' );
			}
			break;
			
		// Assigned To
		case 'assigned':
			
			$contract_events = get_posts(
				array(
					'post_type'			=> 'mdjm-event',
					'posts_per_page'	=> -1,
					'meta_key'			=> '_mdjm_event_contract',
					'meta_value'		=> $post_id,
					'post_status'		=> 'any'
					)
				);
			
			$total = count( $contract_events );
			echo $total . sprintf( _n( ' %1$s', ' %2$s', $total, 'mobile-dj-manager' ), mdjm_get_label_singular(), mdjm_get_label_plural() );
			break;
	} // switch
				
} // mdjm_contract_posts_custom_column
add_action( 'manage_contract_posts_custom_column' , 'mdjm_contract_posts_custom_column', 10, 2 );

/**
 * Customise the post row actions on the contract edit screen.
 *
 * @since	1.0
 * @param	arr		$actions	Current post row actions
 * @param	obj		$post		The WP_Post post object
 */
function mdjm_contract_post_row_actions( $actions, $post )	{
	
	if( $post->post_type != 'contract' )	{
		return $actions;
	}
	
	if( isset( $actions['inline hide-if-no-js'] ) )	{
		unset( $actions['inline hide-if-no-js'] );
	}
	
	return $actions = array();
	
} // mdjm_contract_post_row_actions
add_filter( 'post_row_actions', 'mdjm_contract_post_row_actions', 10, 2 );

/**
 * Set the post title placeholder for contracts
 * 
 * @since	1.3
 * @param	str		$title		The post title
 * @return  str		$title		The filtered post title
 */
function mdjm_contract_title_placeholder( $title )	{
	global $post;
	
	if( !isset( $post ) || 'contract' != $post->post_type )	{
		return $title;
	}
	
	return __( 'Enter Contract name here...', 'mobile-dj-manager' );

} // mdjm_contract_title_placeholder
add_filter( 'enter_title_here', 'mdjm_contract_title_placeholder' );

/**
 * Rename the Publish and Update post buttons for contracts
 *
 * @since	1.3
 * @param	str		$translation	The current button text translation
 * @param	str		$text			The text translation for the button
 * @return	str		$translation	The filtererd text translation
 */
function mdjm_contract_rename_publish_button( $translation, $text )	{
	
	global $post;
	
	if( ! isset( $post ) || 'contract' != $post->post_type )	{
		return $translation;
	}
			
	if( $text == 'Publish' )	{
		return __( 'Save Contract', 'mobile-dj-manager' );
	} elseif( $text == 'Update' )	{
		return __( 'Update Contract', 'mobile-dj-manager' );
	} else
		return $translation;
	
} // mdjm_contract_rename_publish_button
add_filter( 'gettext', 'mdjm_contract_rename_publish_button', 10, 2 );

/**
 * Save the meta data for the contract
 *
 * @since	1.3
 * @param	int		$post_id		The current post ID.
 * @param	obj		$post			The current post object (WP_Post).
 * @param	bool	$update			Whether this is an existing post being updated or not.
 * @return	void
 */
function mdjm_save_contract_post( $post_id, $post, $update )	{
	
	if( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE )	{
		return;
	}
	
	if( empty( $update ) )	{
		return;
	}
		
	// Permission Check
	if( ! mdjm_employee_can( 'manage_templates' ) )	{
		
		if( MDJM_DEBUG == true )	{
			MDJM()->debug->log_it( 'PERMISSION ERROR: User ' . get_current_user_id() . ' is not allowed to edit contracts' );
		}
		 
		return;
	}
	
	// Remove the save post action to avoid loops
	remove_action( 'save_post_contract', 'mdjm_save_contract_post', 10, 3 );
	
	// Fire our pre-save hook
	do_action( 'mdjm_pre_contract_save', $post_id, $post, $update );
	
	// Current value of the contract description for comaprison.
	$current_desc = get_post_meta( $ID, '_contract_description', true );
	
	// If we have a value and the key did not exist previously, add it.
	if( !empty( $_POST['contract_description'] ) && empty( $current_desc ) )	{
		add_post_meta( $ID, '_contract_description', $_POST['contract_description'], true );
	}
	
	// If a value existed, but has changed, update it
	elseif( !empty( $_POST['contract_description'] ) && $current_desc != $_POST['contract_description'] )	{
		update_post_meta( $ID, '_contract_description', $_POST['contract_description'] );
	}
		
	// If there is no new meta value but an old value exists, delete it.
	elseif ( empty( $_POST['contract_description'] ) && !empty( $current_desc ) )	{
		delete_post_meta( $ID, '_contract_description' );
	}
	
	// Fire our post save hook
	do_action( 'mdjm_post_contract_save', $post_id, $post, $update );
	
	// Re-add the save post action to avoid loops
	add_action( 'save_post_contract', 'mdjm_save_contract_post', 10, 3 );
	
}
add_action( 'save_post_contract', 'mdjm_save_contract_post', 10, 3 );

/**
 * Customise the messages associated with managing contract posts
 *
 * @since	1.3
 * @param	arr		$messages	The current messages
 * @return	arr		$messages	Filtered messages
 */
function mdjm_contract_post_messages( $messages )	{
	
	global $post;
	
	if( 'contract' != $post->post_type )	{
		return $messages;
	}
	
	$url1 = '<a href="' . admin_url( 'edit.php?post_type=contract' ) . '">';
	$url2 = get_post_type_object( $post->post_type )->labels->singular_name;
	$url3 = '</a>';
	
	$messages['contract'] = array(
		0 => '', // Unused. Messages start at index 1.
		1 => sprintf( __( '%2$s updated. %1$s%2$s List%3$s.', 'mobile-dj-manager' ), $url1, $url2, $url3 ),
		4 => sprintf( __( '%2$s updated. %1$s%2$s List%3$s.', 'mobile-dj-manager' ), $url1, $url2, $url3 ),
		5 => isset( $_GET['revision'] ) ? sprintf( __( '%1$s restored to revision from %2$s.', 'mobile-dj-manager' ), $url2, wp_post_revision_title( (int) $_GET['revision'], false ) ) : false,
		6 => sprintf( __( '%2$s published. %1$s%2$s List%3$s.', 'mobile-dj-manager' ), $url1, $url2, $url3 ),
		7 => sprintf( __( '%2$s saved. %1$s%2$s List%3$s.', 'mobile-dj-manager' ), $url1, $url2, $url3 ),
		10 => sprintf( __( '%2$s draft updated. %1$s%2$s List%3$s..', 'mobile-dj-manager' ), $url1, $url2, $url3 )
	);
	
	return apply_filters( 'mdjm_contract_post_messages', $messages );
	
} // mdjm_contract_post_messages
add_filter( 'post_updated_messages','mdjm_contract_post_messages' );
