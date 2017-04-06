<?php
/**
 * Contains all admin playlist functions
 *
 * @package		MDJM
 * @subpackage	Admin/Events
 * @since		1.3
 */

/**
 * Display the event playlist page.
 *
 * @since	1.3
 * @param	
 * @return	str		The event playlist page content.
 */
function mdjm_display_event_playlist_page()	{
	
	if( ! mdjm_employee_can( 'read_events' ) && ! mdjm_employee_working_event( $_GET['event_id'] ) )	{
		wp_die(
			'<h1>' . __( 'Cheatin&#8217; uh?', 'mobile-dj-manager' ) . '</h1>' .
			'<p>' . __( 'You do not have permission to view this playlist.', 'mobile-dj-manager' ) . '</p>',
			403
		);
	}
	
	if ( ! class_exists( 'MDJM_PlayList_Table' ) )	{
		require_once( MDJM_PLUGIN_DIR . '/includes/admin/events/class-mdjm-playlist-table.php' );
	}
	
	$playlist_obj = new MDJM_PlayList_Table();
	
	?>
	<div class="wrap">
		<h1><?php printf( __( 'Playlist for %s %s', 'mobile-dj-manager' ), mdjm_get_label_singular(), mdjm_get_event_contract_id( $_GET['event_id'] ) ); ?></h1>

        <form method="post">
            <?php
            $playlist_obj->prepare_items();
			$playlist_obj->display_header();
			
			if( count( $playlist_obj->items ) > 0 )	{
				$playlist_obj->views();
			}
            
			$playlist_obj->display();
			$playlist_obj->entry_form();
            ?>
        </form>
        <br class="clear">
	</div>
	<?php
} // mdjm_display_event_playlist_page

/**
 * Format the playlist results for emailing/printing.
 *
 * @since	1.3
 * @param	int		$event_id		The event ID to retrieve the playlist for.
 * @param	str		$orderby		Which field to order the playlist entries by.
 * @param	str		$order			Order ASC or DESC.
 * @param	int		$repeat_headers	Repeat the table headers after this many rows.
 * @param	bool	$hide_empty		If displaying by category do we hide empty categories?
 * @return	str		$results		Output of playlist entries.
 */
function mdjm_format_playlist_content( $event_id, $orderby='category', $order='ASC', $hide_empty=true, $repeat_headers=0 )	{
	global $current_user;
	
	$mdjm_event = mdjm_get_event( $event_id );
	
	// Obtain results ordered by category
	if( $orderby == 'category' )	{
			
		$playlist = mdjm_get_playlist_by_category( $event_id, array( 'hide_empty' => $hide_empty ) );
		
		if ( $playlist )	{
			
			foreach( $playlist as $cat => $entries )	{
									
				foreach( $entries as $entry )	{
					
					$entry_data = mdjm_get_playlist_entry_data( $entry->ID );
					
					$results[] = array(
						'ID'		=> $entry->ID,
						'event'		=> $event_id,
						'artist'	=> stripslashes( $entry_data['artist'] ),
						'song'		=> stripslashes( $entry_data['song'] ),
						'added_by'	=> stripslashes( $entry_data['added_by'] ),
						'category'	=> $cat,
						'notes'		=> stripslashes( $entry_data['djnotes'] ),
						'date'		=> mdjm_format_short_date( $entry->post_date )
					);
					
				}

			}
			
		}
	} 
	// Obtain results ordered by another field.
	else	{
		
		$args = array(
				'orderby'	=> $orderby == 'date' ? 'post_date'	: 'meta_value',
				'order'		=> $order,
				'meta_key'	=> $orderby == 'date' ? ''			: '_mdjm_playlist_entry_' . $orderby
		);
		
		$entries = mdjm_get_playlist_entries( $event_id, $args );
		
		if( $entries )	{
			foreach( $entries as $entry )	{
				$entry_data = mdjm_get_playlist_entry_data( $entry->ID );
				
				$categories = wp_get_object_terms( $entry->ID, 'playlist-category' );
								
				if ( ! empty( $categories ) )	{
					$category = $categories[0]->name;
				}
											
				$results[] = array(
					'ID'		=> $entry->ID,
					'event'		=> $event_id,
					'artist'	=> stripslashes( $entry_data['artist'] ),
					'song'		=> stripslashes( $entry_data['song'] ),
					'added_by'	=> stripslashes( $entry_data['added_by'] ),
					'category'	=> ! empty( $category ) ? $category : '',
					'notes'		=> stripslashes( $entry_data['djnotes'] ),
					'date'		=> mdjm_format_short_date( $entry->post_date )
				);
			}
		}
	}
	
	// Build out the formatted display
	if( ! empty( $results ) )	{
		
		$i				= 0;
		
		$output = '<p>' . sprintf( __( 'Hey %s', 'mobile-dj-manager' ), $current_user->first_name ) . '</p>' . "\n";
		$output .= '<p>' . __( 'Here is the playlist you requested...', 'mobile-dj-manager' ) . '</p>' . "\n";
		
		$output .= '<p>' .
					   __( 'Client Name', 'mobile-dj-manager' ) . ': ' . mdjm_get_client_display_name( $mdjm_event->client ) . '<br />' . "\n" .
					   __( 'Event Date', 'mobile-dj-manager' ) . ': ' . mdjm_get_event_long_date( $mdjm_event->ID ) . '<br />' . "\n" .
					   __( 'Event Type', 'mobile-dj-manager' ) . ': ' . mdjm_get_event_type( $mdjm_event->ID ) . '<br />' . "\n" .
					   __( 'Songs in Playlist', 'mobile-dj-manager' ) . ': ' . count( $results ) . '<br />' . "\n" .
					   '</p>';
					   
		$output .= '<hr />' . "\n";
		
		$headers = '<tr style="height: 30px">' . "\n" .
						'<td style="width: 15%"><strong>' . __( 'Song', 'mobile-dj-manager' ) . '</strong></td>' . "\n" .
						'<td style="width: 15%"><strong>' . __( 'Artist', 'mobile-dj-manager' ) . '</strong></td>' . "\n" .
						'<td style="width: 15%"><strong>' . __( 'Category', 'mobile-dj-manager' ) . '</strong></td>' . "\n" .
						'<td style="width: 40%"><strong>' . __( 'Notes', 'mobile-dj-manager' ) . '</strong></td>' . "\n" .
						'<td style="width: 15%"><strong>' . __( 'Added By', 'mobile-dj-manager' ) . '</strong></td>' . "\n" .
					'</tr>' . "\n";
		
		$output .= '<table width="90%" border="0" cellpadding="0" cellspacing="0">' . "\n";
		
		$output .= $headers;
		
		foreach( $results as $result )	{
			if( $repeat_headers > 0 && $i == $repeat_headers )	{
				$output .= '<tr>' . "\n" .
								'<td colspan="5">&nbsp;</td>' . "\n" .
							'</tr>' . "\n" .
							$headers;
				$i = 0;
			}
			
			if ( is_numeric( $result['added_by'] ) )	{
				$user = get_userdata( $result['added_by'] );
				
				$name = $user->display_name; 
			} else	{
				$name = $result['added_by'];
			}
			
			$output .= '<tr>' . "\n" .
							'<td>' . stripslashes( $result['song'] ) . '</td>' . "\n" .
							'<td>' . stripslashes( $result['artist'] ) . '</td>' . "\n" .
							'<td>' . stripslashes( $result['category'] ) . '</td>' . "\n" .
							'<td>' . stripslashes( $result['notes'] ) . '</td>' . "\n" .
							'<td>' . stripslashes( $name ) . '</td>' . "\n" .
						'</tr>' . "\n";
			
			$i++;
		}
		
		$output .= '</table>' . "\n";
		
	}
	else	{
		$output = '<p>' . __( 'The playlist for this event does not contain any entries!', 'mobile-dj-manager' ) . '</p>' . "\n";
	}
	
	return $output;
} // mdjm_format_playlist_content
