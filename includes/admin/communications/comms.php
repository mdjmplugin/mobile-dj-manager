<?php
/**
 * This plugin utilizes Open Source code. Details of these open source projects along with their licenses can be found below.
 * We acknowledge and are grateful to these developers for their contributions to open source.
 *
 * Project: mobile-dj-manager https://github.com/deckbooks/mobile-dj-manager
 * License: (GNU General Public License v2.0) https://github.com/deckbooks/mobile-dj-manager/blob/master/license.txt
 *
 * @author: Mike Howard, Jack Mawhinney, Dan Porter
 * @package MDJM
 */

	defined( 'ABSPATH' ) || die( 'Direct access to this page is disabled!!!' );

/**
 * Manage the communication history posts
 */

/**
 * Define the columns to be displayed for communication posts
 *
 * @since   0.5
 * @param   arr $columns    Array of column names.
 * @return  arr     $columns    Filtered array of column names
 */
function mdjm_communication_post_columns( $columns ) {

	$columns = array(
		'cb'             => '<input type="checkbox" />',
		'date_sent'      => __( 'Date Sent', 'mobile-dj-manager' ),
		'title'          => __( 'Email Subject', 'mobile-dj-manager' ),
		'from'           => __( 'From', 'mobile-dj-manager' ),
		'recipient'      => __( 'Recipient', 'mobile-dj-manager' ),
		/* translators: %s Event/Events placeholder */
		'event'          => sprintf( __( '%s', 'mobile-dj-manager' ), mdjm_get_label_singular() ),
		'current_status' => __( 'Status', 'mobile-dj-manager' ),
		'source'         => __( 'Source', 'mobile-dj-manager' ),
	);

	if ( ! mdjm_is_admin() && isset( $columns['cb'] ) ) {
		unset( $columns['cb'] );
	}

	return $columns;
} // mdjm_communication_post_columns
add_filter( 'manage_mdjm_communication_posts_columns', 'mdjm_communication_post_columns' );

/**
 * Define the data to be displayed in each of the custom columns for the Communication post types
 *
 * @since   0.9
 * @param   str $column_name    The name of the column to display.
 * @param   int $post_id        The current post ID.
 */
function mdjm_communication_posts_custom_column( $column_name, $post_id ) {

	global $post;

	switch ( $column_name ) {
		// Date Sent.
		case 'date_sent':
			echo esc_html( date( mdjm_get_option( 'time_format', 'H:i' ) . ' ' . mdjm_get_option( 'short_date_format', 'd/m/Y' ), get_post_meta( $post_id, '_date_sent', true ) ) );

			break;

		// From.
		case 'from':
			$author = get_userdata( $post->post_author );

			if ( $author ) {
				printf( '<a href="%s">%s</a>', esc_url( admin_url( "user-edit.php?user_id={$author->ID}" ) ), esc_html( ucwords( $author->display_name ) ) );
			} else {
				echo esc_html( get_post_meta( $post_id, '_recipient', true ) );
			}

			break;

		// Recipient.
		case 'recipient':
			$client = get_userdata( get_post_meta( $post_id, '_recipient', true ) );

			if ( $client ) {
				printf( '<a href="%s">%s</a>', esc_url( admin_url( "user-edit.php?user_id={$client->ID}" ) ), esc_html( ucwords( $client->display_name ) ) );
			} else {
				echo esc_html__( 'Recipient no longer exists', 'mobile-dj-manager' );
			}

			$copies = get_post_meta( $post_id, '_mdjm_copy_to', true );

			if ( ! empty( $copies ) ) {
				if ( ! is_array( $copies ) ) {
					$copies = array( $copies );
				}
				foreach ( $copies as $copy ) {
					$user = get_user_by( 'email', $copy );
					if ( $user ) {
						echo '<br /><em>' . esc_html( $user->display_name ) . ' (copy)</em>';
					}
				}
			}

			break;

		// Associated Event.
		case 'event':
			$event_id = get_post_meta( $post_id, '_event', true );

			if ( ! empty( $event_id ) ) {
				echo '<a href="' . esc_url( get_edit_post_link( $event_id ) ) . '">' . esc_html( mdjm_get_event_contract_id( $event_id ) ) . '</a>';
			} else {
				esc_html_e( 'N/A', 'mobile-dj-manager' );
			}

			break;

		// Status.
		case 'current_status':
			echo esc_html( get_post_status_object( $post->post_status )->label );

			if ( ! empty( $post->post_modified ) && 'opened' === $post->post_status ) {
				echo '<br />';
				echo '<em>' . esc_html( date( mdjm_get_option( 'time_format', 'H:i' ) . ' ' . mdjm_get_option( 'short_date_format', 'd/m/Y' ), strtotime( $post->post_modified ) ) ) . '</em>';
			}

			break;

		// Source.
		case 'source':
			echo esc_html( wp_unslash( get_post_meta( $post_id, '_source', true ) ) );

			break;
	} // switch

} // mdjm_communication_posts_custom_column
add_action( 'manage_mdjm_communication_posts_custom_column', 'mdjm_communication_posts_custom_column', 10, 2 );

/**
 * Remove the edit bulk action from the communication posts list
 *
 * @since   1.3
 * @param   arr $actions    Array of actions.
 * @return  arr     $actions    Filtered Array of actions
 */
function mdjm_communication_bulk_action_list( $actions ) {

	unset( $actions['edit'] );

	return $actions;

} // mdjm_communication_bulk_action_list
add_filter( 'bulk_actions-edit-mdjm_communication', 'mdjm_communication_bulk_action_list' );

/**
 * Customise the post row actions on the communication edit screen.
 *
 * @since   1.0
 * @param   arr $actions    Current post row actions.
 * @param   obj $post       The WP_Post post object.
 */
function mdjm_communication_post_row_actions( $actions, $post ) {

	if ( 'mdjm_communication' !== $post->post_type ) {
		return $actions;
	}

	return $actions = array();

} // mdjm_communication_post_row_actions
add_filter( 'post_row_actions', 'mdjm_communication_post_row_actions', 10, 2 );

/**
 * Remove the dropdown filters from the edit post screen.
 *
 * @since   1.3
 */
function mdjm_communication_remove_add_new() {

	if ( ! isset( $_GET['post_type'] ) || 'mdjm_communication' !== $_GET['post_type'] ) { // phpcs:ignore WordPress.Security.NonceVerification
		return;
	}

	?>
	<style type="text/css">
		.page-title-action	{
			display: none;
		}
	</style>
	<?php

} // mdjm_communication_remove_add_new
add_action( 'admin_head', 'mdjm_communication_remove_add_new' );
