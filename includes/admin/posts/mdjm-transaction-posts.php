<?php
	defined( 'ABSPATH' ) or die( "Direct access to this page is disabled!!!" );
	
/**
 * Manage the Transaction posts
 *
 *
 *
 */
		
/**
 * Save the meta data for the transaction
 *
 * @called	save_post_mdjm-transaction
 *
 * @param	int		$ID				The current post ID.
 *			obj		$post			The current post object (WP_Post).
 *			bool	$update			Whether this is an existing post being updated or not.
 * 
 * @return	void
 */
function mdjm_save_txn_post( $ID, $post, $update )	{
	global $mdjm_settings;
	
	if( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE )	{
		return;
	}
	
	if( empty( $update ) )	{
		return;
	}
		
	// Permission Check
	if( ! mdjm_employee_can( 'edit_txns' ) )	{
		
		if( MDJM_DEBUG == true )
			MDJM()->debug->log_it( 'PERMISSION ERROR: User ' . get_current_user_id() . ' is not allowed to edit transactions' );
		 
		return;
		
	}
	
	// Remove the save post action to avoid loops
	remove_action( 'save_post_mdjm-transaction', 'mdjm_save_txn_post', 10, 3 );
	
	// Fire our pre-save hook
	do_action( 'mdjm_before_txn_save', $ID, $post, $update );
	
	$trans_type = get_term( $_POST['mdjm_transaction_type'], 'transaction-types' );
				
	// Set the post data
	$trans_data['ID'] = $ID;
	$trans_data['post_status'] = ( $_POST['transaction_direction'] == 'Out' ? 'mdjm-expenditure' : 'mdjm-income' );
	$trans_data['post_date'] = date( 'Y-m-d H:i:s', strtotime( $_POST['transaction_date'] ) );
	$trans_data['edit_date'] = true;
		
	$trans_data['post_author'] = get_current_user_id();
	$trans_data['post_type'] = 'mdjm-transaction';
	$trans_data['post_category'] = array( $_POST['mdjm_transaction_type'] );	
	
	// Set the post meta		
	$trans_meta['_mdjm_txn_status'] = sanitize_text_field( $_POST['transaction_status'] );
	$trans_meta['_mdjm_txn_source'] = sanitize_text_field( $_POST['transaction_src'] );
	$trans_meta['_mdjm_txn_total'] = mdjm_format_amount( $_POST['transaction_amount'] );
	$trans_meta['_mdjm_txn_notes'] = sanitize_text_field( $_POST['transaction_description'] );
	
	if( $_POST['transaction_direction'] == 'In' )	{
		$trans_meta['_mdjm_payment_from'] = sanitize_text_field( $_POST['transaction_payee'] );
	}
		
	elseif( $_POST['transaction_direction'] == 'Out' )	{
		$trans_meta['_mdjm_payment_to'] = sanitize_text_field( $_POST['transaction_payee'] );
	}
									
	$trans_meta['_mdjm_txn_currency'] = $mdjm_settings['payments']['currency'];
	
	// Update the post
	if( MDJM_DEBUG == true )	{
		 MDJM()->debug->log_it( 'Updating the transaction' );
	}
	
	wp_update_post( $trans_data );
	
	// Set the transaction Type
	if( MDJM_DEBUG == true )	{
		 MDJM()->debug->log_it( 'Setting the transaction type' );
	}
	
	wp_set_post_terms( $ID, $_POST['mdjm_transaction_type'], 'transaction-types' );
	
	// Add the meta data
	if( MDJM_DEBUG == true )	{
		 MDJM()->debug->log_it( 'Updating the transaction post meta' );
	}
	
	// Loop through the post meta and add/update/delete the meta keys. 
	foreach( $trans_meta as $meta_key => $new_meta_value )	{
		$current_meta_value = get_post_meta( $ID, $meta_key, true );
		
		// If we have a value and the key did not exist previously, add it.
		if ( !empty( $new_meta_value ) && empty( $current_meta_value ) )	{
			add_post_meta( $ID, $meta_key, $new_meta_value, true );
		}
		
		// If a value existed, but has changed, update it.
		elseif ( !empty( $new_meta_value ) && $new_meta_value != $current_meta_value )	{
			update_post_meta( $ID, $meta_key, $new_meta_value );
		}
			
		// If there is no new meta value but an old value exists, delete it.
		elseif ( empty( $new_meta_value ) && !empty( $current_meta_value ) )	{
			delete_post_meta( $ID, $meta_key, $new_meta_value );
		}
	}
	
	// Fire our post save hook
	do_action( 'mdjm_after_txn_save', $ID, $post, $update );
	
	// Re-add the save post action to avoid loops
	add_action( 'save_post_mdjm-transaction', 'mdjm_save_txn_post', 10, 3 );
	
} // mdjm_save_txn_post
add_action( 'save_post_mdjm-transaction', 'mdjm_save_txn_post', 10, 3 );

/**
 * Customise the messages associated with managing transaction posts
 *
 * @since	1.3
 * @param	arr		$messages	The current messages
 * @return	arr		$messages	Filtered messages
 *
 */
function mdjm_txn_post_messages( $messages )	{
	
	global $post;
	
	if( 'mdjm-transaction' != get_post_type( $post->ID ) )	{
		return;
	}
	
	$messages = array(
		0 => '', // Unused. Messages start at index 1.
		1 => __( 'Transaction updated.', 'mobile-dj-manager' ),
		2 => __( 'Custom field updated.' ),
		3 => __( 'Custom field deleted.' ),
		4 => __( 'Transaction updated.', 'mobile-dj-manager' ),
		5 => isset( $_GET['revision'] ) ? sprintf( __( 'Transaction restored to revision from %s.', 'mobile-dj-manager' ), wp_post_revision_title( (int) $_GET['revision'], false ) ) : false,
		6 => __( 'Transaction created.' ),
		7 => __( 'Transaction saved.', 'mobile-dj-manager' ),
		8 => __( 'Transaction submitted.', 'mobile-dj-manager' ),
		9 => __( 'Transaction scheduled.', 'mobile-dj-manager' ),
		10 => __( 'Transaction draft updated.', 'mobile-dj-manager' )
	);
	
	return apply_filters( 'mdjm_txn_post_messages', $messages );
	
} // mdjm_txn_post_messages
add_filter( 'post_updated_messages','mdjm_txn_post_messages' );