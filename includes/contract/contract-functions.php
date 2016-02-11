<?php

/**
 * Contains event contract functions.
 *
 * @package		MDJM
 * @subpackage	Events
 * @since		1.3
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) )
	exit;

/**
 * Retrieve the contract.
 *
 * @since	1.3
 * @param	int			$contract_id		The contract ID
 * @return	obj|false	Contract post object or false.
 */
function mdjm_get_contract( $contract_id )	{
	$contract = get_post( $contract_id );
	
	if( ! $contract || ( $contract->post_type != 'contract' && $contract->post_type != 'mdjm-signed-contract' ) )	{
		$contract = false;
	}
	
	return apply_filters( 'mdjm_get_contract', $contract, $contract_id );
} // mdjm_get_contract

/**
 * Make sure the contract exists.
 *
 * @since	1.3
 * @param	int		$contract_id		The contract ID
 * @return	bool	true if it exists, otherwise false
 */
function mdjm_contract_exists( $contract_id )	{
	return mdjm_get_contract( $contract_id );
} // mdjm_contract_exists
 
/**
 * Determine if the event contract is signed.
 *
 * @since	1.3
 * @param	$event_id		The event ID.
 * @return	int|bool		The signed contracted post ID or false if not signed yet.
 */
function mdjm_contract_is_signed( $event_id )	{
	$event = new MDJM_Event( $event_id );
	
	return $event->get_contract_status();
} // mdjm_contract_is_signed

/**
 * Output the contract to the screen.
 *
 * @since	1.3
 * @param	$contract		The contract ID.
 * @param	$event			An MDJM_Event class object.
 * @return	str				The contract content.
 */
function mdjm_show_contract( $contract_id, $event )	{
	$contract = mdjm_get_contract( $contract_id );
	
	if( $contract )	{
		// Retrieve the contract content
		$content = $contract->post_content;
		$content = apply_filters( 'the_content', $content );
		$content = str_replace( ']]>', ']]&gt;', $content );
		
		$output = mdjm_do_content_tags( $content, $event->ID, $event->client );
	}
	else	{
		$output = __( 'The contract could not be displayed', 'mobile-dj-manager' );
	}
	return apply_filters( 'mdjm_show_contract', $output, $contract_id, $event );
} // mdjm_show_contract


/**
 * Retrieve the contract signatory name.
 *
 * @since	1.3
 * @param	$contract_id	The contract ID.
 * @return	str|bool		The name of the person who signed the contract.
 */
function mdjm_get_contract_signatory_name( $contract_id )	{	
	$name = get_post_meta( $contract_id, '_mdjm_contract_signed_name', true );
	
	if( empty( $name ) )	{
		$name = __( 'Name not recorded', 'mobile-dj-manager' );
	}
	
	return apply_filters( 'mdjm_get_contract_signatory_name', $name, $contract_id );
} // mdjm_get_contract_signatory_name

/**
 * Retrieve the contract signatory IP address.
 *
 * @since	1.3
 * @param	$contract_id	The contract ID.
 * @return	str|bool		The IP address used by the person who signed the contract.
 */
function mdjm_get_contract_signatory_ip( $contract_id )	{	
	$ip = get_post_meta( $contract_id, '_mdjm_contract_signed_ip', true );
	
	if( empty( $ip ) )	{
		$ip = __( 'IP address not recorded', 'mobile-dj-manager' );
	}
	
	return apply_filters( 'mdjm_get_contract_signatory_ip', $ip, $contract_id );
} // mdjm_get_contract_signatory_ip
