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
 * Accept an enquiry.
 *
 * When a client clicks the Book Event button to accept an enquiry
 * transition the event to the awaiting contract status.
 *
 * @since	1.3
 * @param	arr		$data	Data for the transition.
 * @return	bool	True on succes, otherwise false
 */
function mdjm_accept_enquiry( $data )	{
	
	global $current_user;
	
	$mdjm_event = mdjm_get_event( $data['event_id'] );
	
	if ( ! $mdjm_event )	{
		return false;
	}
	
	do_action( 'mdjm_pre_event_accept_enquiry', $mdjm_event->ID, $data );
	
	$data['meta'] = array(
		'_mdjm_event_enquiry_accepted'	   => current_time( 'mysql' ),
		'_mdjm_event_enquiry_accepted_by'	=> $current_user->ID
	);
	
	$data['client_notices'] = mdjm_get_option( 'contract_to_client' );
	
	if( ! mdjm_update_event_status( $mdjm_event->ID, 'mdjm-contract', $mdjm_event->post_status, $data ) )	{
		return false;
	}
	
	mdjm_add_journal( 
		array(
			'user' 				=> get_current_user_id(),
			'event'				=> $mdjm_event->ID,
			'comment_content'	=> sprintf( __( '%s has accepted their event enquiry', 'mobile-dj-manager' ), $current_user->display_name . '<br>' ),
		),
		array(
			'type'				=> 'update-event',
			'visibility'		=> '2'
		)
	);
		
	$content = '<html>' . "\n" . '<body>' . "\n";
	$content .= '<p>' . sprintf( 
							__( 'Good news... %s has just accepted their %s quotation via %s', 'mobile-dj-manager' ), 
						'{client_fullname}',
						mdjm_get_label_singular( true ),
						'{application_name}' ) . '</p>';
						
	$content .= '<hr />' . "\n";
	$content .= '<h4>' . sprintf( 
							__( '<a href="%s">%s ID: %s</a>', 'mobile-dj-manager' ),
							admin_url( 'post.php?post=' . $mdjm_event->ID . '&action=edit' ),
							mdjm_get_label_singular(),
							'{contract_id}'
						 ) . '</h4>' . "\n";
		
	$content .= '<p>' . 
					sprintf( __( 'Date: %s', 'mobile-dj-manager' ), '{event_date}' ) . 
				'<br />' . "\n";
	$content .= __( 'Type', 'mobile-dj-manager' ) . ': ' . mdjm_get_event_type( $mdjm_event->ID ) . '<br />' . "\n";
		
	$content .= __( 'Status', 'mobile-dj-manager' ) . ': ' . mdjm_get_event_status( $mdjm_event->ID ) . '<br />' . "\n";
	$content .= __( 'Client', 'mobile-dj-manager' ) . ': {client_fullname}<br />' . "\n";
	$content .= __( 'Value', 'mobile-dj-manager' ) . ': {total_cost}<br />' . "\n";
		
	$content .= __( 'Deposit', 'mobile-dj-manager' ) . ': {deposit} ({deposit_status})<br />' . "\n";
	
	$content .= __( 'Balance Due', 'mobile-dj-manager' ) . ': {balance}</p>' . "\n";
	
	$content .= '<p>' . sprintf( 
							__( '<a href="%s">View %s</a>', 'mobile-dj-manager' ),
							admin_url( 'post.php?post=' . $mdjm_event->ID . '&action=edit' ),
							mdjm_get_label_singular()
						) . '</p>' . "\n";
	
	$content .= '</body>' . "\n" . '</html>' . "\n";
	
	$args = array(
		'to_email'		=> mdjm_get_option( 'system_email' ),
		'event_id'		=> $mdjm_event->ID,
		'client_id'		=> $mdjm_event->client,
		'subject'		=> sprintf( __( '%s Quotation Accepted', 'mobile-dj-manager' ), mdjm_get_label_singular() ),
		'message'		=> $content
	);
	
	mdjm_send_email_content( $args );
	
	do_action( 'mdjm_post_event_accept_enquiry', $mdjm_event->ID, $data );
	
	return true;
	
} // mdjm_accept_enquiry

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
function mdjm_get_event_action_buttons( $event_id, $min=true )	{
	$event_status	= get_post_status( $event_id );
	$buttons 		 = array();
	
	// Buttons for events in enquiry state
	if( $event_status == 'mdjm-enquiry' )	{
		if( ! empty( mdjm_get_option( 'online_enquiry', '0' ) ) )	{
			$buttons[5] = apply_filters( 'mdjm_quote_action_button',
				array(
					'label'		=> __( 'View Quote', 'mobile-dj-manager' ),
					'id'		=> 'mdjm-quote-button',
					'url'		=> mdjm_get_formatted_url( mdjm_get_option( 'quotes_page' ), true ) . 'event_id=' . $event_id
				)
			);
		}
		
		$buttons[10] = apply_filters( 'mdjm_book_action_button',
			array(
				'label'		=> sprintf( __( 'Book %s', 'mobile-dj-manager' ), mdjm_get_label_singular() ),
				'id'		=> 'mdjm-book-button',
				'url'		=> add_query_arg( 
					array( 
						'mdjm_action' => 'accept_enquiry',
						'mdjm_nonce'  => wp_create_nonce( 'accept_enquiry' )
					),
					mdjm_get_event_uri( $event_id )
				)
			)
		);
	}
	
	// Buttons for events in awaiting contract state
	if( $event_status == 'mdjm-contract' )	{
		$buttons[15] = apply_filters( 'mdjm_sign_contract_action_button',
			array(
				'label'		=> __( 'Review &amp; Sign Contract', 'mobile-dj-manager' ),
				'id'		=> 'mdjm-sign-contract-button',
				'url'		=> mdjm_get_formatted_url( mdjm_get_option( 'contracts_page' ), true ) . 'event_id=' . $event_id
			)
		);
	}
	
	// Buttons for events in approved state
	if( $event_status == 'mdjm-approved' )	{
		$buttons[20] = apply_filters( 'mdjm_view_contract_action_button',
			array(
				'label'		=> __( 'View Contract', 'mobile-dj-manager' ),
				'id'		   => 'mdjm-view-contract-button',
				'url'		  => mdjm_get_formatted_url( mdjm_get_option( 'contracts_page' ), true ) . 'event_id=' . $event_id
			)
		);
	}
	
	// Playlist action button
	if( mdjm_playlist_is_open( $event_id ) )	{
		if( $event_status == 'mdjm-approved' || $event_status == 'mdjm-contract' )	{
			$buttons[25] = apply_filters( 'mdjm_manage_playlist_action_button',
				array(
					'label'		=> __( 'Manage Playlist', 'mobile-dj-manager' ),
					'id'		   => 'mdjm-manage-playlist-button',
					'url'		  => mdjm_get_formatted_url( mdjm_get_option( 'playlist_page' ), true ) . 'event_id=' . $event_id
				)
			);
		}
	}
	
	if( empty( $min ) )	{		
		$buttons[50] = apply_filters( 'mdjm_update_profile_action_button',
			array(
				'label'		=> __( 'Update Profile', 'mobile-dj-manager' ),
				'id'		   => 'mdjm-update-profile-button',
				'url'		  => mdjm_get_formatted_url( mdjm_get_option( 'profile_page' ), false )
			)
		);
		
	}
	
	$buttons = apply_filters( 'mdjm_event_action_buttons', $buttons, $event_id );
	ksort( $buttons );
	
	return $buttons;
} // mdjm_get_event_action_buttons

/**
 * Output the book event button.
 *
 * If you are filtering the mdjm_get_action_buttons function you may need to adjust the array key
 * within this function.
 *
 * @since	1.3
 * @param	int		$event_id	The event ID.
 * @param	arr		$args		Arguments for button display. See $defaults.
 * @return	str		The Book Event button
 */
function mdjm_display_book_event_button( $event_id, $args = array() )	{

	$buttons = mdjm_get_event_action_buttons( $event_id );
	
	$book_button = $buttons[10];
	
	$defaults = array(
		'colour'   => mdjm_get_option( 'action_button_colour' ),
		'label'    => $book_button['label'],
		'url'      => $book_button['url']
	);
	
	$args - wp_parse_args( $args, $defaults );
	
	$output = sprintf( '<a class="mdjm-action-button mdjm-action-button-%s" href="%s">%s</a>', $args['colour'], $args['url'], $args['label'] );
	
	return apply_filters( 'mdjm_book_event_button', $output, $event_id, $args );
	
} // mdjm_display_book_event_button