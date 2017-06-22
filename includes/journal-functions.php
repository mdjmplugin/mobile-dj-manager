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
		'user_id'         => get_current_user_id(),
		'event_id'        => '',
		'comment_content' => '',
		'comment_type'    => 'mdjm-journal'
	);

	$data = wp_parse_args( $args, $defaults );

	// Make sure we have the required data
	if( empty( $data['comment_content'] ) || empty( $data['event_id'] ) )	{
		return false;
	}

	$comment_author = ( ! empty( $data['user_id'] ) ) ? get_userdata( $data['user_id'] ) : 'mdjm';

	$comment_data = apply_filters( 'mdjm_add_journal',
		array(
			'comment_post_ID'      => (int) $data['event_id'],
			'comment_author'       => $comment_author != 'mdjm' ? $comment_author->display_name : 'MDJM',
			'comment_author_email' => $comment_author != 'mdjm' ? $comment_author->user_email   : mdjm_get_option( 'system_email' ),
			'comment_author_IP'    => '',
			'comment_agent'        => '',
			'comment_author_url'   => '',
			'comment_date'         => current_time( 'mysql' ),
			'comment_date_gmt'     => current_time( 'mysql', 1 ),
			'comment_content'      => $data['comment_content'],
			'comment_type'         => 'mdjm-journal',
			'user_id'              => $comment_author != 'mdjm' ? $comment_author->ID : '0',
			'comment_parent'       => 0,
			'comment_approved'     => 1
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

/**
 * Retrieve journal entries for the event.
 *
 * @since	1.3.7
 * @param	int		$event_id	The event ID
 * @result	obj		Journal entries
 */
function mdjm_get_journal_entries( $event_id )	{
	remove_action( 'pre_get_comments', 'mdjm_hide_journal_entries', 10 );

	$journals = get_comments( array( 'post_id' => $event_id ) );

	add_action( 'pre_get_comments', 'mdjm_hide_journal_entries', 10 );

	return $journals;
} // mdjm_get_journal_entries

/**
 * Gets the journal note HTML.
 *
 * @since	1.0
 * @param	obj|int	$journal	The comment object or ID
 * @param	int		$event_id	The event ID the journal entry is connected to
 * @return	str
 */
function mdjm_event_get_journal_entries_html( $note, $event_id = 0 ) {

	if ( is_numeric( $journal ) ) {
		$journal = get_comment( $journal );
	}

	if ( ! empty( $journal->user_id ) ) {
		$user = get_userdata( $journal->user_id );
		$user = $journal->display_name;
	} else {
		$user = __( 'MDJM Bot', 'mobile-dj-manager' );
	}

	$date_format = get_option( 'date_format' ) . ', ' . get_option( 'time_format' );

	$journal_html  ='<h3>';
		$journal_html .= date_i18n( $date_format, strtotime( $journal->comment_date ) ) . '&nbsp;&ndash;&nbsp;' . $user;
	$journal_html .= '</h3>';

	$journal_html .= '<div>';
		$journal_html .= wpautop( $journal->comment_content );
	$journal_html .= '</div>';

	return $note_html;

} // mdjm_event_get_journal_entries_html

/**
 * Exclude notes (comments) on mdjm-event post type from showing in Recent
 * Comments widgets.
 *
 * @since	1.4.3
 * @param	obj		$query	WordPress Comment Query Object
 * @return	void
 */
function mdjm_hide_journal_entries( $query ) {
	global $wp_version;

	if ( version_compare( floatval( $wp_version ), '4.1', '>=' ) )	{

		if ( isset( $_REQUEST['p'] ) && 'mdjm-event' == get_post_type( $_REQUEST['p'] ) )	{
			return;
		}

		$types = isset( $query->query_vars['type__not_in'] ) ? $query->query_vars['type__not_in'] : array();

		if ( ! is_array( $types ) ) {
			$types = array( $types );
		}

		$types[] = 'mdjm-journal';
		$query->query_vars['type__not_in'] = $types;

	}

} // mdjm_hide_journal_entries
add_action( 'pre_get_comments', 'mdjm_hide_journal_entries', 10 );

/**
 * Exclude notes (comments) on mdjm-event post type from showing in Recent
 * Comments widgets.
 *
 * @since	1.4.3
 * @param	arr		$clauses			Comment clauses for comment query
 * @param	obj		$wp_comment_query	WordPress Comment Query Object
 * @return	arr		$clauses			Updated comment clauses
 */
function mdjm_hide_journal_entries_pre_41( $clauses, $wp_comment_query ) {
	global $wpdb, $wp_version;

	if( version_compare( floatval( $wp_version ), '4.1', '<' ) ) {
		$clauses['where'] .= ' AND comment_type != "mdjm-journal"';
	}

	return $clauses;
} // mdjm_hide_journal_entries_pre_41
add_filter( 'comments_clauses', 'mdjm_hide_journal_entries_pre_41', 10, 2 );


/**
 * Exclude notes (comments) on mdjm-event post type from showing in comment feeds.
 *
 * @since	1.4.3
 * @param	arr		$where
 * @param	obj		$wp_comment_query	WordPress Comment Query Object
 * @return	arr		$where
 */
function mdjm_hide_journal_entries_from_feeds( $where, $wp_comment_query ) {
    global $wpdb;

	$where .= $wpdb->prepare( " AND comment_type != %s", 'mdjm-journal' );
	return $where;
} // mdjm_hide_journal_entries_from_feeds
add_filter( 'comment_feed_where', 'mdjm_hide_journal_entries_from_feeds', 10, 2 );


/**
 * Remove MDJM Journal Comments from the wp_count_comments function.
 *
 * @since	1.4.3
 * @param	arr		$stats		(empty from core filter)
 * @param	int		$post_id	Post ID
 * @return	arr		Array of comment counts
*/
function mdjm_remove_journal_entries_in_comment_counts( $stats, $post_id ) {
	global $wpdb, $pagenow;

	if( 'index.php' != $pagenow ) {
		return $stats;
	}

	$post_id = (int) $post_id;

	if ( apply_filters( 'mdjm_count_journal_entries_in_comments', false ) )	{
		return $stats;
	}

	$stats = wp_cache_get( "comments-{$post_id}", 'counts' );

	if ( false !== $stats )	{
		return $stats;
	}

	$where = 'WHERE comment_type != "mdjm-journal"';

	if ( $post_id > 0 )	{
		$where .= $wpdb->prepare( " AND comment_post_ID = %d", $post_id );
	}

	$count = $wpdb->get_results( "SELECT comment_approved, COUNT( * ) AS num_comments FROM {$wpdb->comments} {$where} GROUP BY comment_approved", ARRAY_A );

	$total    = 0;
	$approved = array( '0' => 'moderated', '1' => 'approved', 'spam' => 'spam', 'trash' => 'trash', 'post-trashed' => 'post-trashed' );

	foreach( (array) $count as $row )	{
		// Don't count post-trashed toward totals
		if ( 'post-trashed' != $row['comment_approved'] && 'trash' != $row['comment_approved'] )	{
			$total += $row['num_comments'];
		}

		if ( isset( $approved[ $row['comment_approved'] ] ) )	{
			$stats[ $approved[ $row['comment_approved'] ] ] = $row['num_comments'];
		}
	}

	$stats['total_comments'] = $total;

	foreach ( $approved as $key )	{
		if ( empty($stats[ $key ] ) )	{
			$stats[ $key ] = 0;
		}
	}

	$stats = (object) $stats;
	wp_cache_set( "comments-{$post_id}", $stats, 'counts' );

	return $stats;
} // mdjm_remove_journal_entries_in_comment_counts
add_filter( 'wp_count_comments', 'mdjm_remove_journal_entries_in_comment_counts', 10, 2 );
