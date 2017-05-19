<?php
/**
 * Process event actions
 *
 * @package		MDJM
 * @subpackage	Events
 * @since		1.3
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) )
	exit;

/**
 * Adds an entry to the playlist from the admin interface.
 *
 * @since	1.4
 * @param	arr		Array of form post data.
 */
function mdjm_add_event_playlist_entry_action( $data )	{
	if ( empty( $data['entry_song'] ) || empty( $data['entry_artist'] ) )	{
		$message = 'adding_song_failed';
	}
	elseif ( ! wp_verify_nonce( $data['mdjm_nonce'], 'add_playlist_entry' ) )	{
		$message = 'security_failed';
	} else	{
		if( mdjm_store_playlist_entry( $data ) )	{
			$message = 'song_added';
		}
		else	{
			$message = 'adding_song_failed';
		}
	}
	
	$url = remove_query_arg( array( 'mdjm-action', 'mdjm_nonce', 'mdjm-message' ) );
	
	wp_redirect( 
		add_query_arg( 
			array(
				'mdjm-message'  => $message
			),
			$url
		)
	);
	die();
} // mdjm_add_event_playlist_entry_action
add_action( 'mdjm-add_playlist_entry', 'mdjm_add_event_playlist_entry_action' );

/**
 * Process song removals from bulk action
 *
 * @since	1.3
 * @param	arr		$_POST super global
 * @return	void
 */
function mdjm_bulk_action_remove_playlist_entry_action()	{
	
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
add_action( 'load-admin_page_mdjm-playlists', 'mdjm_bulk_action_remove_playlist_entry_action' );

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
					'<p align="center" style="font-size: 9px">Powered by <a style="color:#F90" href="http://mdjm.co.uk" target="_blank">' . MDJM_NAME . '</a> version ' . MDJM_VERSION_NUM . '</p>' . "\n" .
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
