<?php
/**
 * Contains all admin playlist functions
 *
 * @package		MDJM
 * @subpackage	Events/Playlists
 * @since		1.3
 */

/**
 * Process song removals from bulk action
 *
 * @since	1.3
 * @param	arr		$_POST super global
 * @return	void
 */
function mdjm_bulk_action_remove_playlist_entry()	{
	
	if ( isset( $_POST['action'] ) )	{
		$action = $_POST['action'];
	} elseif( isset( $_POST['action2'] ) )	{
		$action = $_POST['action2'];
	} else	{
		return;
	}
	
	if( ! isset( $action, $_POST['mdjm-playlist-bulk-delete'] ) )	{
		return;
	}
		
	foreach ( $_POST['mdjm-playlist-bulk-delete'] as $id ) {
		mdjm_remove_stored_playlist_entry( $id );
	}
	
	wp_redirect( 
		add_query_arg( 
			array(
				'mdjm-message'  => 'song_removed'
			)
		)
	);
	die();
	
} // mdjm_bulk_action_remove_playlist_entry
add_action( 'load-admin_page_mdjm-playlists', 'mdjm_bulk_action_remove_playlist_entry' );

/**
 * Process song removals from delete link
 *
 * @since	1.3
 * @param	int|arr		$entry_ids	Playlist entries to remove
 * @return	void
 */
function mdjm_remove_playlist_song_action( $data )	{
	if( ! wp_verify_nonce( $data['mdjm_nonce'], 'remove_playlist_entry' ) )	{
		$message = 'security_failed';
	} else	{
		if( mdjm_remove_stored_playlist_entry( $data['id'] ) )	{
			$message = 'song_removed';
		}
		else	{
			$message = 'song_remove_failed';
		}
	}
	
	$url = remove_query_arg( array( 'mdjm-action', 'mdjm_nonce' ) );
	
	wp_redirect( 
		add_query_arg( 
			array(
				'mdjm-message'  => $message
			),
			$url
		)
	);
	die();
} // mdjm_remove_playlist_entry_action
add_action( 'mdjm-delete_song', 'mdjm_remove_playlist_song_action' );

/**
 * Add an option field to set the default category when adding a new category.
 *
 * @since	1.3
 * @param	obj		$tag	The tag object
 * @return	str
 */
function mdjm_add_playlist_category_fields( $tag )	{
	?>
    <div class="form-field term-group">
        <label for="playlist_default_cat"><?php _e( 'Set as default Category?', 'mobile-dj-manager' ); ?></label>
        <input type="checkbox" name="playlist_default_cat" id="playlist_default_cat" value="<?php echo $tag->term_id; ?>" />
    </div>
    <?php
	
} // mdjm_add_default_playlist_category
add_action( 'playlist-category_add_form_fields', 'mdjm_add_playlist_category_fields', 10, 2 );

/**
 * Add an option field to set the default category when editing a new category.
 *
 * @since	1.3
 * @param	obj		$tag	The tag object
 * @return	str
 */
function mdjm_edit_playlist_category_fields( $tag )	{
	
	?>
    <tr class="form-field term-group-wrap">
        <th scope="row"><label for="playlist_default_cat"><?php _e( 'Set as Default Category?', 'mobile-dj-manager' ); ?></label></th>
        <td><input type="checkbox" id="playlist_default_cat" name="playlist_default_cat" value="<?php echo $tag->term_id; ?>" <?php checked( mdjm_get_option( 'playlist_default_cat' ), $tag->term_id ); ?>></td>
    </tr>
    <?php
	
} // mdjm_add_default_playlist_category
add_action( 'playlist-category_edit_form_fields', 'mdjm_edit_playlist_category_fields', 10, 2 );

/**
 * Fires when a playlist category is created or edited.
 *
 * Check whether the set as default option is set and update options.
 *
 * @since	1.3
 * @param	int		$term_id	The term ID
 * @param	int		$tt_id		The term taxonomy ID
 * @return	str
 */
function mdjm_save_playlist_category( $term_id, $tt_id )	{
	
    if( ! empty( $_POST['playlist_default_cat'] ) )	{
	
		mdjm_update_option( 'playlist_default_cat', $term_id );
	
    } else	{
		
		if( mdjm_get_option( 'playlist_default_cat' ) == $term_id )	{
			
			mdjm_delete_option( 'playlist_default_cat' );
			
		}
		
	}
	
} // mdjm_save_playlist_category
add_action( 'create_playlist-category', 'mdjm_save_playlist_category', 10, 2 );
add_action( 'edited_playlist-category', 'mdjm_save_playlist_category', 10, 2 );

/**
 * Adds the Default column to the playlist category terms list.
 *
 * @since	1.3
 * @param	arr		$columns	The table columns
 * @return	arr		$columns	The table columns
 */
function mdjm_add_playlist_category_default_column( $columns )	{
    $columns['default'] = 'Default?';
    
	return $columns;
} // mdjm_add_playlist_category_default_column
add_filter( 'manage_edit-playlist-category_columns', 'mdjm_add_playlist_category_default_column' );

/**
 * Adds the content to the Default column within the playlist category terms list.
 *
 * @since	1.3
 * @param	str		$content		The cell content
 * @param	str		$column_name	The column name
 * @param	int		$term_id		The term ID
 * @return	str		$content		The table columns
 */
function mdjm_add_playlist_category_default_column_content( $content, $column_name, $term_id )	{
	
	$term = get_term( $term_id, 'playlist-category' );
    
	switch ( $column_name ) {
        case 'default':
            if( mdjm_get_option( 'playlist_default_cat' ) == $term_id )	{
				$content = __( 'Yes', 'mobile-dj-manager' );
			} else	{
				$content = __( 'No', 'mobile-dj-manager' );
			}
            break;
        
		default:
            break;
    }
	
	return $content;
}
add_filter( 'manage_playlist-category_custom_column', 'mdjm_add_playlist_category_default_column_content', 10, 3 );

/**
 * Display the event playlist page.
 *
 * @since	1.3
 * @param	
 * @return	str		The event playlist page content.
 */
function mdjm_display_event_playlist_page()	{
	
	if ( ! class_exists( 'MDJM_PlayList_Table' ) )	{
		require_once( MDJM_PLUGIN_DIR . '/includes/admin/playlist/class-mdjm-playlist-table.php' );
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
 
/**
 * Display the playlist for printing.
 *
 * @since	1.3
 * @param	arr		$data	The super global $_POST
 * @return	str		Output for the print page.
 */
function mdjm_print_event_playlist_action( $data )	{
	if( ! wp_verify_nonce( $data[ 'mdjm_nonce' ], 'print_playlist_entry' ) )	{
		$message = 'security_failed';
	}
	
	else	{
		
		$mdjm_event = mdjm_get_event( $data['print_playlist_event_id'] );
		
		$content = mdjm_format_playlist_content( $mdjm_event->ID, $data['print_order_by'], 'ASC', true );
		
		$content = apply_filters( 'mdjm_print_playlist', $content, $data, $mdjm_event );
		
		?>
        <script type="text/javascript">
		window.onload = function() { window.print(); }
		</script>
        <style>
		@page	{
			size: landscape;
			margin: 2cm;
		}
		body { 
			background:white;
			color:black;
			margin:0;
			width:auto
		}
		#adminmenu {
			display: none !important
		}
		#adminmenumain {
			display: none !important
		}
		#adminmenuback {
			display: none !important
		}
		#adminmenuwrap {
			display: none !important
		}
		#wpadminbar {
			display: none !important
		}
		#wpheader {
			display: none !important;
		}
		#wpcontent {
			margin-left:0; 
			float:none; 
			width:auto }
		}
		#wpcomments {
			display: none !important;
		}
		#message {
			display: none !important;
		}
		#wpsidebar {
			display: none !important;
		}
		#wpfooter {
			display: none !important;
		}
		</style>
        <?php
		echo $content;
		echo '<p style="text-align: center" class="description">Powered by <a style="color:#F90" href="http://mdjm.co.uk" target="_blank">' . MDJM_NAME . '></a>, version ' . MDJM_VERSION_NUM . '</p>' . "\n";
		
	}
	
	die();	
} // mdjm_print_event_playlist_action
add_action( 'mdjm-print_playlist', 'mdjm_print_event_playlist_action' );

/**
 * Send the playlist via email.
 *
 * @since	1.3
 * @param	arr		$data	The super global $_POST
 * @return	void
 */
function mdjm_email_event_playlist_action( $data )	{
	
	if( ! wp_verify_nonce( $data[ 'mdjm_nonce' ], 'email_playlist_entry' ) )	{
		$message = 'security_failed';
	}
	
	else	{
		global $current_user;
		
		$mdjm_event = mdjm_get_event( $data['email_playlist_event_id'] );
		
		$content = mdjm_format_playlist_content( $mdjm_event->ID, $data['email_order_by'], 'ASC', true );
		
		$content = apply_filters( 'mdjm_print_playlist', $content, $data, $mdjm_event );
		
		$html_content_start = '<html>' . "\n" . '<body>' . "\n";
		$html_content_end = '<p>' . __( 'Regards', 'mobile-dj-manager' ) . '</p>' . "\n" .
					'<p>{company_name}</p>' . "\n";
					'<p>&nbsp;</p>' . "\n";
					'<p align="center" style="font-size: 9px">Powered by <a style="color:#F90" href="http://www.mydjplanner.co.uk" target="_blank">' . MDJM_NAME . '</a> version ' . MDJM_VERSION_NUM . '</p>' . "\n" .
					'</body>' . "\n" . '</html>';
		
		$args = array(
			'to_email'		=> $current_user->user_email,
			'from_name'		=> mdjm_get_option( 'company_name' ),
			'from_email'	=> mdjm_get_option( 'system_email' ),
			'event_id'		=> $mdjm_event->ID,
			'client_id'		=> $mdjm_event->client,
			'subject'		=> sprintf( __( 'Playlist for %s ID {contract_id}', 'mobile-dj-manager' ), mdjm_get_label_singular() ),
			'message'		=> $html_content_start . $content . $html_content_end
		);
		
		if ( mdjm_send_email_content( $args ) )	{
			$message = 'playlist_emailed';
		} else	{
			$message = 'playlist_email_failed';
		}
	}
	
	wp_redirect(
		add_query_arg( 'mdjm-message', $message )
	);
	die();
} // mdjm_email_event_playlist
add_action( 'mdjm-email_playlist', 'mdjm_email_event_playlist_action' );