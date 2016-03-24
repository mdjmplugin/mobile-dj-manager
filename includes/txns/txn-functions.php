<?php
/**
 * Contains all transaction related functions
 *
 * @package		MDJM
 * @subpackage	Transactions
 * @since		1.3
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) )
	exit;

/**
 * Retrieve a transaction.
 *
 * @since	1.3
 * @param	int		$txn_id	The transaction ID.
 * @return	obj		$txn	The transaction WP_Post object
 */
function mdjm_get_txn( $txn_id )	{
	return mdjm_get_txn_by_id( $txn_id );
} // mdjm_get_txn

/**
 * Retrieve a transaction by ID.
 *
 * @param	int		$txn_id		The WP post ID for the transaction.
 *
 * @return	mixed	$txn		WP_Query object or false.
 */
function mdjm_get_txn_by_id( $txn_id )	{
	$txn = new MDJM_Txn( $txn_id );
	
	return ( !empty( $txn->ID ) ? $txn : false );
} // mdjm_get_txn_by_id

/**
 * Return the type of transaction.
 *
 * @since	1.3
 * @param	int		$txn_id		ID of the current transaction.
 * @return	str		Transaction type.
 */
function mdjm_get_txn_type( $txn_id )	{
	$txn = new MDJM_Txn( $txn_id );
	
	// Return the label for the status
	return $txn->get_type();
} // mdjm_get_txn_type

/**
 * Returns the date for a transaction in short format.
 *
 * @since	1.3
 * @param	int		$txn_id		The transaction ID.
 * @return	str					The date of the transaction.
 */
function mdjm_get_txn_date( $txn_id='' )	{
	if( empty( $txn_id ) )	{
		return false;
	}

	$txn = new MDJM_Txn( $txn_id );
	
	return mdjm_format_short_date( $txn->get_date() );
} // mdjm_get_txn_date

/**
 * Registers a new transaction or updates an existing.
 *
 * @since	1.3
 * @param	arr			$data		Array of transaction post data.
 * @return	int|bool				The new transaction ID or false on failure.
 */
function mdjm_add_txn( $data )	{
	
	$post_defaults = apply_filters( 
		'mdjm_add_txn_defaults',
		array(
			'ID'			=> isset ( $data['invoice'] ) ? $data['invoice'] : '',
			'post_title'	=> isset ( $data['invoice'] ) ? mdjm_get_option( 'event_prefix' ) . $data['invoice'] : '',
			'post_status' 	=> 'mdjm-income',
			'post_date'		=> date( 'Y-m-d H:i:s', current_time( 'timestamp' ) ),
			'edit_date'		=> true,
			'post_author'	=> isset ( $data['item_number'] ) ? get_post_meta( $data['item_number'], '_mdjm_event_client', true ) : 1,
			'post_type'		=> 'mdjm-transaction',
			'post_category'	=> ( !empty( $data['txn_type'] ) ? array( $data['txn_type'] ) : '' ),
			'post_parent'	=> isset( $data['event_id'] ) ? $data['event_id'] : '',
			'post_modified'	=> date( 'Y-m-d H:i:s', current_time( 'timestamp' ) )
		)
	);
	
	$txn_data = wp_parse_args( $data, $post_defaults );
	
	do_action( 'mdjm_pre_add_txn', $txn_data );
	
	$txn_id = wp_insert_post( $txn_data );
	
	// Failed
	if ( $txn_id == 0 )	{
		return false;
	}
	
	// Set the transaction type (category)
	if ( ! empty( $txn_data['post_category'] ) )	{
		wp_set_post_terms( $txn_id, $txn_data['post_category'], 'transaction-types' );
	}
	
	do_action( 'mdjm_post_add_txn', $txn_id, $txn_data );
	
	return $txn_id;
	
} // mdjm_add_txn

/**
 * Add or Update transaction meta data.
 *
 * We don't currently delete empty meta keys or values, instead we update with an empty value
 * if an empty value is passed to the function.
 *
 * @since	1.3
 * @param	int			$txn_id		The transaction ID.
 * @param	arr			$data		Array of transaction post meta data.
 * @return	void
 */
function mdjm_add_txn_meta( $txn_id, $data )	{
	
	$meta = get_post_meta( $txn_id, '_mdjm_txn_data', true );
	
	foreach( $data as $key => $value )	{
		
		if( $key == 'mdjm_nonce' || $key == 'mdjm_action' ) {
			continue;
		}
		
		// For backwards comaptibility
		update_post_meta( $txn_id, $key, $value );
		
		$meta[ $key ] = $value;
		
	}
	
	update_post_meta( $txn_id, '_mdjm_txn_data', $meta );
		
} // mdjm_add_txn_meta

/**
 * Remove the post save action whilst adding or updating transactions.
 *
 * @since	1.3
 * @param
 * @return	void
 */
function mdjm_remove_txn_save_post_action()	{
	remove_action( 'save_post_mdjm-transaction', 'mdjm_save_txn_post', 10, 3 );
} // mdjm_remove_txn_save_post_action
add_action( 'mdjm_pre_add_txn', 'mdjm_remove_txn_save_post_action' );
add_action( 'mdjm_pre_update_txn', 'mdjm_remove_txn_save_post_action' );

/**
 * Add the post save action after adding or updating transactions.
 *
 * @since	1.3
 * @param
 * @return	void
 */
function mdjm_add_txn_save_post_action()	{
	add_action( 'save_post_mdjm-transaction', 'mdjm_save_txn_post', 10, 3 );
} // mdjm_add_txn_save_post_action
add_action( 'mdjm_post_add_txn', 'mdjm_add_txn_save_post_action' );
add_action( 'mdjm_post_update_txn', 'mdjm_add_txn_save_post_action' );