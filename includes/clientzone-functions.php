<?php

/**
 * Contains all role functions.
 *
 * @package		MDJM
 * @subpackage	Client Zone
 * @since		1.3
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) )
	exit;

/**
 * Return all relevant action buttons for the event.
 *
 * Allow filtering of the buttons so they can be re-ordered, re-named etc.
 *
 * @since	1.3
 * @param	int		$event_id	The event ID.
 * @param	bool	$min		True returns only minimal action buttons used within loop.
 * @return	arr		Array of event action buttons.
 */
function mdjm_get_event_action_buttons( $event_id, $min=false )	{
	$event_status	= get_post_status( $event_id );
	$buttons 		= array();
	
	// Buttons for events in enquiry state
	if( $event_status == 'mdjm-enquiry' )	{
		if( ! empty( mdjm_get_option( 'online_enquiry', '0' ) ) )	{
			$buttons[5]		= apply_filters( 'mdjm_quote_action_button',
				array(
					'label'		=> __( 'View Quote', 'mobile-dj-manager' ),
					'id'		=> 'mdjm-quote-button',
					'uri'		=> mdjm_get_formatted_url( MDJM_QUOTES_PAGE, true ) . 'event_id=' . $event_id
				)	
			);
		}
		
		$buttons[10]	= apply_filters( 'mdjm_book_action_button',
			array(
				'label'		=> __( 'Book Event', 'mobile-dj-manager' ),
				'id'		=> 'mdjm-book-button',
				'uri'		=> mdjm_get_formatted_url( MDJM_HOME, true ) . 'do_action=accept_enquiry&amp;event_id=' . $event_id
			)	
		);
	}
	
	// Buttons for events in awaiting contract state
	if( $event_status == 'mdjm-contract' )	{
		$buttons[15]		= apply_filters( 'mdjm_sign_contract_action_button',
			array(
				'label'		=> __( 'Review &amp; Sign Contract', 'mobile-dj-manager' ),
				'id'		=> 'mdjm-sign-contract-button',
				'uri'		=> mdjm_get_formatted_url( MDJM_CONTRACT_PAGE, true ) . 'event_id=' . $event_id
			)	
		);
	}
	
	// Buttons for events in approved state
	if( $event_status == 'mdjm-approved' )	{
		$buttons[20]	= apply_filters( 'mdjm_view_contract_action_button',
			array(
				'label'		=> __( 'View Contract', 'mobile-dj-manager' ),
				'id'		=> 'mdjm-view-contract-button',
				'uri'		=> mdjm_get_formatted_url( MDJM_CONTRACT_PAGE, true ) . 'event_id=' . $event_id
			)	
		);
	}
	
	// Playlist action button
	if( mdjm_playlist_is_open( $event_id ) )	{
		if( $event_status == 'mdjm-approved' || $event_status == 'mdjm-contract' )	{
			$buttons[25]	= apply_filters( 'mdjm_manage_playlist_action_button',
				array(
					'label'		=> __( 'Manage Playlist', 'mobile-dj-manager' ),
					'id'		=> 'mdjm-manage-playlist-button',
					'uri'		=> mdjm_get_formatted_url( MDJM_PLAYLIST_PAGE, true )
				)	
			);
		}
	}
	
	if( ! empty( $min ) )	{
		$buttons[50]	= apply_filters( 'mdjm_update_profile_action_button',
			array(
				'label'		=> __( 'Update Profile', 'mobile-dj-manager' ),
				'id'		=> 'mdjm-manage-playlist-button',
				'uri'		=> mdjm_get_formatted_url( MDJM_PROFILE_PAGE, false )
			)	
		);
		
		$buttons[60]	= apply_filters( 'mdjm_update_profile_action_button',
			array(
				'label'		=> __( 'Book Another Event', 'mobile-dj-manager' ),
				'id'		=> 'mdjm-book-again-button',
				'uri'		=> mdjm_get_formatted_url( MDJM_CONTACT_PAGE, false )
			)	
		);
	}
	
	return apply_filters( 'mdjm_event_action_buttons', $buttons );
} // mdjm_get_event_action_buttons