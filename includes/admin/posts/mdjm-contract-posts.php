<?php
	defined( 'ABSPATH' ) or die( "Direct access to this page is disabled!!!" );
	
/**
 * Manage the Contract posts
 *
 *
 *
 *
 */
		
/**
 * Define the columns to be displayed for contract posts
 *
 * @params	arr		$columns	Array of column names
 *
 * @return	arr		$columns	Filtered array of column names
 */
function mdjm_contract_post_columns( $columns ) {
	$columns = array(
			'cb'			   => '<input type="checkbox" />',
			'title' 			=> __( 'Contract Name', 'mobile-dj-manager' ),
			'event_default'	=> __( 'Is Default?', 'mobile-dj-manager' ),
			'assigned'		 => __( 'Assigned To', 'mobile-dj-manager' ),
			'author'		   => __( 'Created By', 'mobile-dj-manager' ),
			'date' 			 => __( 'Date', 'mobile-dj-manager' ) );
	
	return $columns;
} // contract_post_columns
add_filter( 'manage_contract_posts_columns' , 'mdjm_contract_post_columns' );
				
/**
 * Define the data to be displayed in each of the custom columns for the Contract post types
 *
 * @param	str		$column_name	The name of the column to display
 *			int		$post_id		The current post ID
 * 
 *
 */
function mdjm_contract_posts_custom_column( $column_name, $post_id )	{
	switch ( $column_name ) {
		// Is Default?
		case 'event_default':
			echo ( $post_id == $GLOBALS['mdjm_settings']['events']['default_contract'] ? 
				'<span style="color: green; font-weight: bold;">' . __( 'Yes', 'mobile-dj-manager' ) . '</span>' : __( 'No', 'mobile-dj-manager' ) );
			break;
		// Assigned To
		case 'assigned':
			$contract_events = get_posts(
				array(
					'post_type'		=> MDJM_EVENT_POSTS,
					'posts_per_page'   => -1,
					'meta_key'	 	 => '_mdjm_event_contract',
					'meta_value'   	   => $post_id,
					'post_status'  	  => 'any',
					)
				);
			
			$total = count( $contract_events );
			echo $total . ' ' . _n( 'Event', 'Events', $total, 'mobile-dj-manager' );
			break;	
	} // switch
} // contract_posts_custom_column
add_action( 'manage_contract_posts_custom_column' , 'mdjm_contract_posts_custom_column', 10, 2 );
		
/**
 * Save the meta data for the contract
 *
 * @called	save_post_contract
 *
 * @param	int		$ID				The current post ID.
 *			obj		$post			The current post object (WP_Post).
 *			bool	$update			Whether this is an existing post being updated or not.
 * 
 * @return	void
 */
function mdjm_save_contract_template_post( $ID, $post, $update )	{
	if( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE )
		return;
		
	// Permission Check
	if( !MDJM()->permissions->employee_can( 'manage_templates' ) )	{
		if( MDJM_DEBUG == true )
			MDJM()->debug->log_it( 'PERMISSION ERROR: User ' . get_current_user_id() . ' is not allowed to edit templates' );
		 
		return;
	}
	
	// Remove the save post action to avoid loops
	remove_action( 'save_post_contract', 'mdjm_save_contract_template_post', 10, 3 );
	
	// Fire our pre-save hook
	do_action( 'mdjm_before_contract_template_save', $ID, $post, $update );
				
	// Current value of the contract description for comaprison.
	$current_desc = get_post_meta( $ID, '_contract_description', true );
	
	// If we have a value and the key did not exist previously, add it.
	if( !empty( $_POST['contract_description'] ) && empty( $current_desc ) )
		add_post_meta( $ID, '_contract_description', $_POST['contract_description'], true );
	
	// If a value existed, but has changed, update it
	elseif( !empty( $_POST['contract_description'] ) && $current_desc != $_POST['contract_description'] )
		update_post_meta( $ID, '_contract_description', $_POST['contract_description'] );
		
	// If there is no new meta value but an old value exists, delete it.
	elseif ( empty( $_POST['contract_description'] ) && !empty( $current_desc ) )
		delete_post_meta( $ID, '_contract_description' );
	
	// Fire our post save hook
	do_action( 'mdjm_after_contract_save', $ID, $post, $update );
		
	// Re-add the save post action
	add_action( 'save_post_contract', 'mdjm_save_contract_template_post', 10, 3 );
} // contract_posts_custom_column
add_action( 'save_post_contract', 'mdjm_save_contract_template_post', 10, 3 );
?>