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
 * Returns the default event contract ID.
 *
 * @since	1.3
 * @param
 * @return	int		The post ID of the default event contract.
 */
function mdjm_get_default_event_contract()	{
	return mdjm_get_option( 'default_contract', false );
} // mdjm_get_default_event_contract

/**
 * Returns the event contract ID.
 *
 * @since	1.3
 * @param	int		$event_id	The event ID.
 * @return	int		The post ID of the event contract.
 */
function mdjm_get_event_contract( $event_id )	{
	$event = new MDJM_Event( $event_id );
	
	return $event->get_contract();
} // mdjm_get_event_contract

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
 * @param	$event_id		The event ID.
 * @return	str|bool		The name of the person who signed the contract.
 */
function mdjm_get_contract_signatory_name( $event_id )	{	
	$name = get_post_meta( $event_id, '_mdjm_event_contract_approver', true );
	
	if( empty( $name ) )	{
		$name = __( 'Name not recorded', 'mobile-dj-manager' );
	}
	
	return apply_filters( 'mdjm_get_contract_signatory_name', $name, $event_id );
} // mdjm_get_contract_signatory_name

/**
 * Retrieve the contract signatory IP address.
 *
 * @since	1.3
 * @param	$event_id		The event ID.
 * @return	str|bool		The IP address used by the person who signed the contract.
 */
function mdjm_get_contract_signatory_ip( $event_id )	{	
	$ip = get_post_meta( $event_id, '_mdjm_event_contract_approver_ip', true );
	
	if( empty( $ip ) )	{
		$ip = __( 'IP address not recorded', 'mobile-dj-manager' );
	}
	
	return apply_filters( 'mdjm_get_contract_signatory_ip', $ip, $event_id );
} // mdjm_get_contract_signatory_ip

/**
 * Sign the event contract
 *
 * @since	1.3
 * @param	int		$event_id	The event ID
 * @param	arr		$details	Contract and event info
 * @return	bool	Whether or not the contract was signed
 */
function mdjm_sign_event_contract( $event_id, $details )	{
	$event = new MDJM_Event( $event_id );
	
	if( ! $event )	{
		return false;
	}
	
	$contract_template = mdjm_get_contract( mdjm_get_event_contract( $event->ID ) );
	
	if( ! $contract_template )	{
		return false;
	}
	
	do_action( 'mdjm_pre_sign_event_contract', $event_id, $details );
	
	// Prepare the content for the contract.
	$contract_content = $contract_template->post_content;
	$contract_content = apply_filters( 'the_content', $contract_content );
	$contract_content = str_replace( ']]>', ']]&gt;', $contract_content );
	$contract_content = mdjm_do_content_tags( $contract_content, $event->ID, $event->client );
	
	// The signatory information displayed at the foot of the contract
	$contract_signatory_content = '<hr>' . "\r\n";
	$contract_signatory_content .= '<p style="font-weight: bold">' . __( 'Signatory', 'mobile-dj-manager' ) . ': <span style="text-decoration: underline;">' . 
		ucfirst( $details['mdjm_first_name'] ) . ' ' . ucfirst( $details['mdjm_last_name'] ) . '</span></p>' . "\r\n";
		
	$contract_signatory_content .= '<p style="font-weight: bold">' . __( 'Date of Signature', 'mobile-dj-manager' ) . ': <span style="text-decoration: underline;">' . date( 'jS F Y' ) . '</span></p>' . "\r\n";
	$contract_signatory_content .= '<p style="font-weight: bold">' . __( 'Verification Method', 'mobile-dj-manager' ) . ': ' . __( 'User Password Confirmation', 'mobile-dj-manager' ) . '</p>' . "\r\n";
	$contract_signatory_content .= '<p style="font-weight: bold">' . __( 'IP Address Used', 'mobile-dj-manager' ) . ': ' . $_SERVER['REMOTE_ADDR'] . '</p>' . "\r\n";
	
	$contract_signatory_content = apply_filters( 'mdjm_contract_signatory', $contract_signatory_content );
	
	$contract_content .= $contract_signatory_content;
	
	// Filter the signed contract post data
	$signed_contract = apply_filters( 'mdjm_signed_contract_data',
		array(
			'post_title'     => sprintf( __( 'Event Contract: %s', 'mobile-dj-manager' ), mdjm_get_option( 'event_prefix' ) . $event->ID ),
			'post_author'    => get_current_user_id(),
			'post_type'      => 'mdjm-signed-contract',
			'post_status'    => 'publish',
			'post_content'   => $contract_content,
			'post_parent'    => $event->ID,
			'ping_status'    => 'closed',
			'comment_status' => 'closed'
		),
		$event->ID, $event
	);
	
	$signed_contract_id = wp_insert_post( $signed_contract, true );
	
	if( is_wp_error( $signed_contract_id ) )	{
		return false;
	}
	
	add_post_meta( $signed_contract, '_mdjm_contract_signed_name', ucfirst( $details['mdjm_first_name'] ) . ' ' . ucfirst( $details['mdjm_last_name'] ), true );
	
	$event_meta = array(
		'_mdjm_event_signed_contract'      => $signed_contract_id,
		'_mdjm_event_contract_approved'    => current_time( 'mysql' ),
		'_mdjm_event_contract_approver'    => strip_tags( addslashes( ucfirst( $details['mdjm_first_name'] ) . ' ' . ucfirst( $details['mdjm_last_name'] ) ) ),
		'_mdjm_event_contract_approver_ip' => $_SERVER['REMOTE_ADDR'],
		'_mdjm_event_last_updated_by'      => get_current_user_id()
	);
		
	// Update the event status
	mdjm_update_event_status(
		$event->ID,
		'mdjm-approved',
		$event->post_status,
		array(
			'meta'			  => $event_meta,
			'client_notices'	=> mdjm_get_option( 'booking_conf_to_client' )
		)
	);
	
	mdjm_add_journal( 
		array(
			'user' 				=> get_current_user_id(),
			'event'				=> $event->ID,
			'comment_content'	=> __( 'Contract Approval completed by ', 'mobile-dj-manager' ) . ucfirst( $details['mdjm_first_name'] ) . ' ' . ucfirst( $details['mdjm_last_name'] . '<br>' ),
		),
		array(
			'type'				=> 'update-event',
			'visibility'		=> '2'
		)
	);
	
	do_action( 'mdjm_post_sign_event_contract', $event_id, $details );
	
	return true;
} // mdjm_sign_event_contract
