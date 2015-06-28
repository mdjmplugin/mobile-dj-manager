<?php
/*
 * class-playlist.php
 * 19/06/2015
 * @since 2.1
 * The ClientZone Playlist class enables clients and guests to manage the evtn playlist
 * 
 */
	
	defined( 'ABSPATH' ) or die( "Direct access to this page is disabled!!!" );
	
	/* -- Build the MDJM_Playlist class -- */
	if( !class_exists( 'MDJM_Playlist' ) )	{
		require_once( 'class-clientzone.php' );
		class MDJM_Playlist extends ClientZone 	{
			/*
			 * The Constructor
			 *
			 *
			 *
			 */
			function __construct()	{
				global $mdjm, $my_mdjm, $post;
				
				if( !is_user_logged_in() )	{
					$playlist = ( isset( $_GET['mdjmeventid'] ) ? $this->playlist_exists( $_GET['mdjmeventid'] ) : false );
										
					// Incorrect playlist code (or not found)
					if( isset( $_GET['mdjmeventid'] ) && empty( $playlist ) )
						parent::display_notice( 4,
												'No event found. Please check the URL you have been sent and try again' );
					// Playlist code exists
					elseif( isset( $_GET['mdjmeventid'] ) && !empty( $playlist ) )	{
						$this->eventinfo = $mdjm->mdjm_events->event_detail( $this->event->ID );
						$post = $this->event;
						$this->guest_form( $playlist );
					}
					
					// User needs to log in
					else
						parent::login();
				}
				// Client is logged in	
				else	{
					if( isset( $_GET['event_id'] ) && !empty( $_GET['event_id'] ) )
						$this->event = get_post( $_GET['event_id'] );
					else
						$this->event = $my_mdjm['next'][0];
					
					$post = $this->event;
						
					$this->eventinfo = $mdjm->mdjm_events->event_detail( $this->event->ID );
					
					$this->client_form();
				}
				
			} // __construct
			
			/*
			 * Remove entry from the playlist
			 *
			 *
			 *
			 */
			function remove_song( $song )	{
				global $wpdb, $my_mdjm;
				
				$query = $wpdb->delete( MDJM_PLAYLIST_TABLE,
										array( 'id' => $song ) );
										
				if( !empty( $query ) )	{
					$GLOBALS['mdjm_debug']->log_it( 'Song removed from playlist by ' . $my_mdjm['me']->display_name, true );
					parent::display_notice( 2,
												__( 'The song was successfully removed' ) );	
				}
				else	{
					$GLOBALS['mdjm_debug']->log_it( 'Song could not be removed from playlist ' . $wpdb->print_error(), true );
					parent::display_notice( 4,
												__( 'An error occurred. Please try again.' ) );	
				}
				
			} // remove_song
			
			/*
			 * Add a new entry to the playlist
			 *
			 *
			 *
			 */
			function add_song()	{
				global $mdjm, $my_mdjm, $wpdb;
				
				// Firstly, our security check
				if( !isset( $_POST['__mdjm_playlist'] ) || !wp_verify_nonce( $_POST['__mdjm_playlist'], 'manage_playlist' ) )	{
					$GLOBALS['mdjm_debug']->log_it( 'Security verification failed during playlist addition. No update occured', false );
					return parent::display_message( 4, 4 );	
				}
				// Passed Security
				else	{
					$when = ( !empty( $_POST['playlist_when'] ) ? $_POST['playlist_when'] : 'General' );
					
					if( !is_user_logged_in() )
						$when = 'Guest Added';
					
					$by = ( is_user_logged_in() ? $my_mdjm['me']->display_name : ucwords( $_POST['first_name'] . ' ' . $_POST['last_name'] ) );
					
					// Insert the record
					$update_id = $wpdb->insert( MDJM_PLAYLIST_TABLE,
													array(
														'id' 		  =>	'',
														'event_id'	=> $this->event->ID,
														'artist'	  => $_POST['playlist_artist'],
														'song'		=> $_POST['playlist_song'],
														'play_when'   => $when,
														'info'		=> ( isset( $_POST['playlist_info'] ) ?
																		$_POST['playlist_info'] : '' ),
														'added_by'	=> $by,
														'date_added'  => date( 'Y-m-d' ),
													) );
					if( !empty( $update_id ) )	{ // Success
						// Journal Entry
						if( MDJM_JOURNAL == true )	{
							$mdjm->mdjm_events->add_journal( array(
											'user'			=> $this->eventinfo['client']->ID,
											'event'		   => $this->event->ID,
											'comment_content' => 'Song added to playlist by ' . $by,
											'comment_type'	=> 'mdjm-journal', ),
											 array(
												'type'			=> 'update-event',
												'visibility'	=> '2',) );
						}
						
						if( MDJM_DEBUG == true )
							$GLOBALS['mdjm_debug']->log_it( 'Song added to Event ID: ' . $this->event->ID . ' Playlist by ' . $by, true );
						
						// Create an array we can use to display the entries from this session
						$this->current_songs[] = $wpdb->insert_id;
						
						parent::display_notice( 2,
												__( 'The song was successfully added' ) );
														
					} // if( !empty( $update_id )
					
					else	{ // Failed
						if( MDJM_DEBUG == true )
							$GLOBALS['mdjm_debug']->log_it( 'ERROR: Could not add song to playlist. ' . $wpdb->print_error(), true );
						
						parent::display_notice( 4,
												__( 'An error occurred. Please try again.' ) );
					}
				}
				
			} // add_song
			
			/*
			 * Does the playlist exist?
			 *
			 * @param	str			$id		The guest playlist ID
			 * @return	arr|bool			The event post if it exists, otherwise false
			 */
			function playlist_exists( $id )	{
				global $mdjm;
								
				$event = $mdjm->mdjm_events->mdjm_event_by( 'playlist', $id );
							
				return ( $event ) ? $this->event = $event : false;
							
			} // playlist_exists
						
			/*
			 * Display the playlist form for event guests
			 *
			 *
			 * 
			 */
			function guest_form()	{
				global $mdjm;
				
				/* -- Playlist Additions -- */
				if( isset( $_POST['submit'] ) && $_POST['submit'] == 'Add Song' )
					$this->add_song();
					
				/* -- Playlist Removal -- */
				if( isset( $_GET['remove_id'] ) && !empty( $_GET['remove_id'] ) )	{
				}
								
				/* -- Enqueue jQuery validation -- */
				wp_enqueue_script( 'mdjm-validation' );
				
				/* GUEST WELCOME */
				echo parent::__text( 'playlist_guest_welcome', '<p>Welcome to the ' . MDJM_COMPANY . ' playlist management system.</p>' );
				
				/* GUEST INTRO */
				echo parent::__text( 'playlist_guest_intro',
									   '<p>You are adding songs to the playlist for ' . $this->eventinfo['client']->display_name . 
									   '\'s event on ' . date( 'l, jS F Y', $this->eventinfo['date'] ) . '.</p>' . 
									   '<p>Add your playlist requests within in the form below. All fields are required.</p>' );
				
				// If the playlist is open, display it
				if( $mdjm->mdjm_events->playlist_status( $this->eventinfo['date'] ) == true )	{					   
					// Display the form
                    echo '<hr />' . "\r\n";
					echo '<div id="mdjm-playlist-container">' . "\r\n";
					echo '<div id="mdjm-playlist-table">' . "\r\n";
					echo '<form action="' . $mdjm->get_link( MDJM_PLAYLIST_PAGE ) . 'mdjmeventid=' . $_GET['mdjmeventid'] . 
						'" method="post" enctype="multipart/form-data" name="guest-playlist" id="guest-playlist">' . "\r\n";
					
					wp_nonce_field( 'manage_playlist', '__mdjm_playlist' ) . "\r\n";
						
					echo '<table id="mdjm-playlist-display">' . "\r\n";
					
					echo '<tr>' . "\r\n";
					echo '<td><label for="first_name">Your First Name</label>:<br />' . 
					'<input name="first_name" id="first_name" type="text" size="25" value="' . 
					( isset( $_POST['first_name'] ) ? $_POST['first_name'] : '' ) . '" class="required"' . 
					( !isset( $_POST['first_name'] ) ? ' autofocus="autofocus"' : '' ) . ' /></td>' . "\r\n";					

					echo '<td><label for="last_name">Your Last Name</label>:<br />' . 
					'<input name="last_name" id="last_name" type="text" size="25" value="' . 
					( isset( $_POST['first_name'] ) ? $_POST['last_name'] : '' ) . '" class="required" /></td>' . "\r\n";
					
					echo '<td><label for="playlist_song">Song Name</label>:<br />' . 
					'<input name="playlist_song" id="playlist_song" type="text" size="25" class="required"' . 
					( isset( $_POST['first_name'] ) ? ' autofocus="autofocus"' : '' ) . ' /></td>' . "\r\n";
					
					echo '<td><label for="playlist_artist">Artist</label>:<br />' . 
					'<input name="playlist_artist" id="playlist_artist" type="text" size="25" class="required" /></td>' . "\r\n";
					echo '</tr>' . "\r\n";
					
					echo '<tr>' . "\r\n";
					echo '<td colspan="4" style="text-align: left;"><input name="submit" type="submit" value="Add Song" /></td>' . "\r\n";
					echo '</tr>' . "\r\n";
					
					// End the table display
					echo '</table>' . "\r\n";						
					
					echo '</form>' . "\r\n";
					echo '</div>' . "\r\n"; // End div mdjm-playlist-table
					
					// Display songs that the user has added during this session and allow deletions
					//$this->guest_songs();
					
					echo '</div>' . "\r\n"; // End div mdjm-playlist-container
					
				}
				// Otherwise it's closed
				else	{
					/* PLAYLIST CLOSED */
					parent::display_notice( 3,
												parent::__text( 'playlist_guest_closed', 
												'<p>This playlist is currently closed. No songs can be added at this time.</p>' ) );
				}
				
			} // guest_form
			
			/*
			 * Display the current songs added during this session
			 *
			 *
			 *
			 */
			function guest_songs()	{
				global $wpdb;
				
				if( empty( $this->current_songs ) )
					return;
				
				echo '<hr />' . "\r\n";
				echo parent::__text( 'playlist_current_songs_guest',
									 '<p>Below are the songs you have added during this session. ' . 
									 'If you wish to remove entries you can do so but once you move away ' . 
									 'from this page, you will no longer be able to do so</p>' );
			
				echo '<div class="mdjm-playlist-3column">' . "\r\n";
				echo '<label>Song</label>' . "\r\n";
				echo '</div>' . "\r\n";
				echo '<div class="mdjm-playlist-3column">' . "\r\n";
				echo '<label>Artist</label>' . "\r\n";
				echo '</div>' . "\r\n";echo '<div class="mdjm-playlist-3column">' . "\r\n";
				echo '<div class="mdjm-playlist-last-3column">' . "\r\n";
				echo '&nbsp;' . "\r\n";
				echo '</div>' . "\r\n";
								
				$entries = $wpdb->get_results( "SELECT event_id, song, artist 
					FROM `" . MDJM_PLAYLIST_TABLE . "` 
					WHERE `event_id` IN (" . implode( ',', $this->current_songs ) . ")" );
					
				foreach( $entries as $entry )	{
					echo '<div class="mdjm-playlist-3column">' . "\r\n";
					echo $entry->song . "\r\n";
					echo '</div>' . "\r\n";
					echo '<div class="mdjm-playlist-3column">' . "\r\n";
					echo $entry->artist . "\r\n";
					echo '</div>' . "\r\n";echo '<div class="mdjm-playlist-3column">' . "\r\n";
					echo '<div class="mdjm-playlist-last-3column">' . "\r\n";
					echo '<a href="' . $mdjm->get_link( MDJM_PLAYLIST_PAGE ) . 'mdjmeventid=' . $_GET['mdjmeventid'] . 
						'&remove_id=' . $entry->event_id . '"></a>' . "\r\n";
					echo '</div>' . "\r\n";	
				}
				
			} // guest_songs
			
			/*
			 * Display the client form for managing playlist
			 *
			 *
			 *
			 */
			function client_form()	{
				global $my_mdjm, $mdjm, $mdjm_settings;
				
				/* -- Playlist Additions -- */
				if( isset( $_POST['submit'] ) && $_POST['submit'] == 'Add Song' )
					$this->add_song();
					
				/* -- Playlist Removal -- */
				if( isset( $_GET['remove_song'] ) && !empty( $_GET['remove_song'] ) )	{
					$this->remove_song( $_GET['remove_song'] );
				}
				
				/* -- Enqueue jQuery validation -- */
				wp_enqueue_script( 'mdjm-validation' );
				
				/* WELCOME TEXT */
				echo parent::__text( 'playlist_welcome', '<p>Welcome to the ' . MDJM_COMPANY . ' event playlist management system.</p>' );
				
				/* INTRO TEXT */
				echo parent::__text( 'playlist_intro',
									 '<p>Use this tool to let your DJ know the songs that you would like played (or perhaps not played) ' . 
									 'during your event on <strong>' . date( 'l, jS F Y', $this->eventinfo['date'] ) . '</strong>.</p>' );
									 
				/* If client has more than one event, allow them to switch between events */
				if( count( $my_mdjm['active'] ) > 1 )	{
					?>
					<?php
					/* EDITING PLAYLIST TEXT */
					echo parent::__text( 'playlist_edit',
										 '<p>You are currently editing the playlist for your event on ' . 
										 date( 'l, jS F Y', $this->eventinfo['date'] ) . '. To edit the playlist for one of your other events, ' . 
										 'return to the <a href="' . $mdjm->get_link( MDJM_HOME, false ) . '">' . MDJM_APP . 
										 ' home page</a> and select Edit Playlist from the drop down list displayed next to the event for ' . 
										 'which you want to edit the playlist.</p>' );
				} // if( count( $my_mdjm['active'] ) > 1 )
				
				$num_songs = $mdjm->mdjm_events->count_playlist_entries( $this->event->ID );
				
				/* Display the form to add songs to playlist */
				if( $mdjm->mdjm_events->playlist_status( $this->eventinfo['date'] ) )	{ // Display form
					echo '<hr />' . "\r\n";
					echo '<div id="mdjm-playlist-container">' . "\r\n";
					echo '<div id="mdjm-playlist-table">' . "\r\n";
					echo '<form action="' . get_permalink() . 
						'" method="post" enctype="multipart/form-data" name="client-playlist" id="client-playlist">' . "\r\n";
					
					wp_nonce_field( 'manage_playlist', '__mdjm_playlist' ) . "\r\n";
						
					echo '<table id="mdjm-playlist-display">' . "\r\n";

					echo '<tr>' . "\r\n";
					echo '<td><label for="playlist_song">' . __( 'Song Name' ) . '</label>:<br />' . 
					'<input name="playlist_song" id="playlist_song" type="text" size="25" class="required" /></td>' . "\r\n";
					
					echo '<td><label for="playlist_artist">' . __( 'Artist' ) . '</label>?<br />' . 
					'<input name="playlist_artist" id="playlist_artist" type="text" size="25" class="required" /></td>' . "\r\n";
					
					echo '<td><label for="playlist_when">' . __( 'When to Play' ) . '</label>:<br />' . 
					'<select name="playlist_when" id="playlist_when">' . "\r\n";
					
					$pl_when = explode( "\n", $mdjm_settings['playlist']['playlist_cats'] );
					foreach( $pl_when as $when )	{
						echo '<option value="' . $when . '">' . $when . '</option>' . "\r\n";
					} // foreach( $pl_when as $when )
					
					echo '</select></td>' . "\r\n";
					
					echo '<td><label for="playlist_info">' . __( 'Info' ) . '</label>:<br />' . 
					'<textarea name="playlist_info" id="playlist_info" placeholder="Optional: add information if you selected Other from the drop down list">' . 
						'</textarea></td>' . "\r\n";
					
					echo '</tr>' . "\r\n";
					
					echo '<tr>' . "\r\n";
					echo '<td colspan="4" style="text-align: left;"><input name="submit" id="submit" type="submit" value="Add Song" /></td>' . "\r\n";
					echo '</tr>' . "\r\n";
					
					// End the table display
					echo '</table>' . "\r\n";						
					
					echo '</form>' . "\r\n";
					echo '</div>' . "\r\n"; // End div mdjm-playlist-table
					echo '</div>' . "\r\n"; // End div mdjm-playlist-container
				}
				else	{
					echo parent::display_notice( 1,
												parent::__text( 'playlist_closed', '<p>Additions to your playlist are disabled to allow your 
												' . MDJM_DJ . ' to prepare for your event.</p>' ) );
                
					if( $num_songs > 0 ) 
						echo '<p>' . __( 'Existing playlist entries are displayed below' ) . '.</p>';
				}
								
				/* -- Display existing entries if we have them -- */
				if( $num_songs > 0 )	{ // Songs to display
				
					$categories = $mdjm->mdjm_events->get_playlist_by_cat( $this->event->ID );
				
					echo '<div id="mdjm_song_container">' . "\r\n";
					foreach( $categories as $category => $songs )	{
						echo '<table class="mdjm_song_table">' . "\r\n";
						echo '<tr>' . "\r\n";
						echo '<th colspan="3">' . $category . '</th>' . "\r\n";
						echo '</tr>' . "\r\n";
						foreach( $songs as $song )	{
							echo '<tr>' . "\r\n";
							echo '<td>' . $song->song . '</td>' . "\r\n";
							
							echo '<td>' . $song->artist . '</td>' . "\r\n";
							
							echo '<td style="text-align: right;"><a href="' . $mdjm->get_link( MDJM_PLAYLIST_PAGE ) . 
								'remove_song=' . $song->id . '">' . __( 'Remove' ) . '</a></td>' . "\r\n";
							echo '</tr>' . "\r\n";
						}
						echo '<tr>' . "\r\n";
						echo '<td colspan="3" style="font-weight: bold; border-top: 2px solid;">' . 
							count( $songs ) . _n( ' Song', ' Songs', count( $songs ) ) . 
							'</td>' . "\r\n";
						echo '</tr>' . "\r\n";
						echo '</table>' . "\r\n";
					} // End foreach
					echo '</div>' . "\r\n"; // End div mdjm_song_container
					
				} // if( $num_songs > 0 )
				
			} // client_form
			
		} // class
		
	} // if( !class_exists( 'MDJM_Playlist' ) )
	
/* -- Insantiate the MDJM_Playlist class -- */
	$mdjm_playlist = new MDJM_Playlist();	
				
