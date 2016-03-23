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