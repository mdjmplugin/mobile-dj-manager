<?php
/**
 * Contains all journal related functions
 *
 * @package		MDJM
 * @subpackage	Events
 * @since		1.3
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) )
	exit;
	
/**
 * Update the event journal.
 *
 * @since	1.3
 * @param 	arr			$data	
 * @param 	arr			$meta
 * @return: int|bool	comment_id or false on failure
 */
function mdjm_add_journal( $args = array(), $meta = array() )	{
	// Return if journaling is disabled.
	if( ! mdjm_get_option( 'journaling', false ) )	{
		return false;
	}
	
	$defaults = array(
		'user_id'          => get_current_user_id(),
		'event_id'         => '',
		'comment_content'  => '',
		'comment_type'     => 'mdjm-journal',
		'comment_date'     => current_time( 'timestamp' )
	);
	
	$data = wp_parse_args( $args, $defaults );
	
	// Make sure we have the required data
	if( empty( $data['comment_content'] ) || empty( $data['event_id'] ) )	{
		return false;
	}
	
	$comment_author = ( ! empty( $data['user_id'] ) ) ? get_userdata( $data['user_id'] ) : 'mdjm';
	
	$comment_data = apply_filters( 'mdjm_add_journal',
		array(
			'comment_post_ID'       => (int) $data['event_id'],
			'comment_author'        => $comment_author != 'mdjm' ? $comment_author->display_name : 'MDJM',
			'comment_author_email'  => $comment_author != 'mdjm' ? $comment_author->user_email : mdjm_get_option( 'system_email' ),
			'comment_author_IP'     => ! empty( $_SERVER['REMOTE_ADDR'] ) ? preg_replace( '/[^0-9a-fA-F:., ]/', '', $_SERVER['REMOTE_ADDR'] ) : '',
			'comment_agent'         => isset( $_SERVER['HTTP_USER_AGENT'] ) ? substr( $_SERVER['HTTP_USER_AGENT'], 0, 254 ) : '',
			'comment_author_url'    => $comment_author != 'mdjm' ? ! empty( $comment_author->user_url ) ? $comment_author->user_url : '' : '',
			'comment_content'       => $data['comment_content'],
			'comment_type'          => $data['comment_type'],
			'comment_date'          => date( 'Y-m-d H:i:s', $data['comment_date'] ),
			'user_id'               => ( $comment_author != 'mdjm' ) ? $comment_author->ID : '0',
			'comment_parent'        => 0,
			'comment_approved'      => 1
		)
	);
	
	// Filter the comment data before inserting
	$comment_data = apply_filters( 'preprocess_comment', $comment_data );
	
	$comment_data = wp_filter_comment( $comment_data );
	
	// Disable comment duplication check filter
	remove_filter( 'commentdata','comment_duplicate_trigger' );
	
	do_action( 'mdjm_pre_add_journal', $data, $meta, $comment_data );
	
	// Insert the comment
	$comment_id = wp_insert_comment( $comment_data );
	
	if( ! $comment_id )	{
		return false;
	}
	
	$comment_meta = array(
		'mdjm_type'         => ! empty( $meta['type'] )       ? $meta['type']       : 'mdjm-journal',
		'mdjm_visibility'   => ! empty( $meta['visibility'] ) ? $meta['visibility'] : '0',
		'mdjm_notify'       => ! empty( $meta['notify'] )     ? $meta['notify']     : '',
		'mdjm_to'           => ! empty( $meta['to'] )         ? $meta['to']         : '',
		'mdjm_isread'       => ! empty( $meta['isread'] )     ? $meta['isread']     : '',
	);
	
	$comment_meta = wp_parse_args( $meta, $comment_meta );
	
	foreach( $comment_meta as $key => $value )	{
		if( ! empty( $value ) )	{
			add_comment_meta( $comment_id, $key, $value, false );
		}
	}
	
	// Enable comment duplication check filter
	add_filter( 'commentdata', 'comment_duplicate_trigger' );
	
	do_action( 'mdjm_post_add_journal', $data, $meta, $comment_data );
	
	return $comment_id;
} // mdjm_add_journal