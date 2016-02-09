<?php

/**
 * Create terms for each of the playlist categories.
 *
 * @since	1.3
 * @param
 * @return	void
 */
function mdjm_create_playlist_terms()	{
	global $wpdb;
	
	$cats = mdjm_get_option( 'playlist_cats' );
	
	$terms = explode( "\r\n", $cats );
	
	if ( ! empty( $terms ) )	{
		foreach( $terms as $term )	{
			$new_term = wp_insert_term( $term, 'playlist-category' );
			
			if( is_wp_error( $new_term ) )	{
				error_log( $new_term->get_error_message() );
			}
		}
	}
		
	wp_insert_term( __( 'Guest', 'mobile-dj-manager' ), 'playlist-category' );
} // mdjm_create_playlist_terms

/**
 * Create terms for each of the playlist categories.
 *
 * @since	1.3
 * @param
 * @return	void
 */
function mdjm_import_playlist_entries()	{
	global $wpdb;
	
	if( get_option( 'mdjm_playlist_import' ) )	{
		return;
	}
	
	// Create the terms
	mdjm_create_playlist_terms();
		
	$query = "SELECT * FROM 
			 " . $wpdb->prefix . "mdjm_playlists";
			 
	$entries = $wpdb->get_results( $query );
	
	if( $entries )	{
		add_option( 'mdjm_playlist_import', false );
		foreach( $entries as $entry )	{
			$meta = array(
				'song'          => isset( $entry->song )             ? $entry->song              : '',
				'artist'        => isset( $entry->artist )           ? $entry->artist            : '',
				'added_by'      => isset( $entry->added_by )         ? $entry->added_by          : get_current_user_id(),
				'djnotes'       => isset( $entry->info )	         ? $entry->info	          : '',
				'added_date'    => isset( $entry->date_added )       ? $entry->date_added	    : '',
				'to_mdjm'       => isset( $entry->date_to_mdjm )	 ? date( 'Y-m-d H:i:s', strtotime( $entry->date_to_mdjm ) )	  : '',
				'uploaded'      => isset( $entry->upload_procedure ) ? $entry->upload_procedure  : '',
			);
			
			$term        = isset( $entry->play_when )   ? $entry->play_when : 'General';
			$event_id	= isset( $entry->event_id )    ? $entry->event_id  : '';
			
			if( empty( $term ) || $term == 'Guest Added' )	{
				$term = 'Guest';
			}
		
			$title = sprintf( __( 'Event ID: %s %s %s', 'mobile-dj-manager' ),
				mdjm_get_option( 'event_prefix', '' ) . $event_id,
				$meta['song'],
				$meta['artist'] );
			
			$category = get_term_by( 'name', $term, 'playlist-category' );
						
			$entry_id = wp_insert_post(
				array(
					'post_type'     => 'mdjm-playlist',
					'post_title'    => $title,
					'post_author'   => 1,
					'post_status'   => 'publish',
					'post_parent'   => $event_id,
					'post_date'     => isset( $entry->date_added )? date( 'Y-m-d H:i:s', strtotime( $entry->date_added ) ) : date( 'Y-m-d H:i:s' ),
					'post_category' => !empty( $category ) ? array( $category->term_id ) : ''
				)
			);
			
			if( ! empty( $category ) )	{
				mdjm_set_playlist_entry_category( $entry_id, $category->term_id );
			}
		
			foreach( $meta as $key => $value ) {
				update_post_meta( $entry_id, '_mdjm_playlist_entry_' . $key, $value );
			}
		}
		update_option( 'mdjm_playlist_import', true );
	}
} // mdjm_import_playlist_entries
add_action( 'init', 'mdjm_import_playlist_entries', 15 );