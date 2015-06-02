<?php
/*
* playlist.php
* 26/11/2014
* @since 0.8
* Provides a frontend for clients and guests to add songs to playlist
*/
	defined('ABSPATH') or die("Direct access to this page is disabled!!!");
	
	global $wpdb, // Needed until we transition playlist entries
		   $mdjm_settings, // MDJM Settings
		   $mdjm, // The MDJM Class
		   $clientzone, // The Client Zone class
		   $my_mdjm;
	
	/* Check for form submission and add songs */
	if( isset( $_POST['submit'] ) && $_POST['submit'] == 'Add' )	{
		f_mdjm_add_playlistsong( $_POST );
	}

/* Check if user is logged in */
	if( is_user_logged_in() )	{ // Yes
		/* Check if a song should be removed */
		if ( isset( $_GET['playlist_id'] ) && !empty( $_GET['playlist_id'] ) )	{
			f_mdjm_remove_playlistsong( $_GET['playlist_id'] );
		}
		
		$current_user = wp_get_current_user();
		
		if( isset( $_GET['mdjmeventid'] ) && !empty( $_GET['mdjmeventid'] ) )	{
			$event = get_post( $_GET['mdjmeventid'] );
		}
		elseif( isset( $_GET['event_id'] ) && !empty( $_GET['event_id'] ) )	{
			$event = get_post( $_GET['event_id'] );
		}
		else	{
			$event = $my_mdjm['next'][0];
		}
		
		$playlist = f_mdjm_get_playlist( $event->ID );
		$eventinfo = $mdjm->mdjm_events->event_detail( $event->ID );
		if( $event )	{ // Event found
			/* WELCOME TEXT */
			echo $clientzone->__text( 'playlist_welcome', '<p>Welcome to the ' . MDJM_COMPANY . ' event playlist management system.</p>' );

			/* INTRO TEXT */
			echo $clientzone->__text( 'playlist_intro', '<p>Use this tool to let your DJ know the songs that you would like played (or perhaps not played) during your event on <strong>' . date( 'l, jS F Y', $eventinfo['date'] ) . '</strong>.</p>' );
			
			$total_events = f_mdjm_total_client_events_by_status( 'Approved' );
			
			/* If client has more than one event, allow them to switch between events */
			if( $total_events > 1 )	{
				?>
                <?php
				/* EDITING PLAYLIST TEXT */
				echo $clientzone->__text( 'playlist_edit', '<p>You are currently editing the playlist for your event on ' . date( 'l, jS F Y', $eventinfo['date'] ) . '. To edit the playlist for one of your other events, return to the <a href="' . $mdjm->get_link( MDJM_HOME, false ) . '">' . MDJM_APP . ' home page</a> and select Edit Playlist from the drop down list displayed next to the event for which you want to edit the playlist.</p>' );
			} // if( $total_events > 1 )
			
			/* Display the form to add songs to playlist */
			if( $mdjm->mdjm_events->playlist_status( $eventinfo['date'] ) )	{ // Display form
				?>
                <form action="<?php get_permalink(); ?>" method="post" enctype="multipart/form-data" name="playlist">
                <input type="hidden" name="event_id" value="<?php echo $event->ID; ?>" />
                <input type="hidden" name="client_id" value="<?php echo  $my_mdjm['me']->ID; ?>" />
                <input type="hidden" name="added_by" value="<?php echo $my_mdjm['me']->first_name." ".$my_mdjm['me']->last_name; ?>" />
                <table border="0" cellpadding="0" cellspacing="0" style="font-size:11px" width="100%">
                <thead>
                <tr align="left">
                <th>Song Name:</th>
                <th>Artist:</th>
                <th>When to Play?</th>
                <th>Info:</th>
                <th>&nbsp;</th>
                </tr>
                </thead>
                <tr valign="top">
                <td><input name="playlist_song" type="text" size="30" /></td>
                <td><input name="playlist_artist" type="text" size="30" /></td>
                <td><select name="playlist_when"><?php
                $pl_when = explode( "\n", $mdjm_settings['main']['playlist_when'] );
                foreach( $pl_when as $when )	{
					?>
					<option value="<?php echo $when; ?>"><?php echo $when; ?></option>
					<?php	
                } // foreach( $pl_when as $when )
                ?>
                </td>
                <td><textarea name="playlist_info" placeholder="Optional: add information if you selected Other from the drop down list"></textarea></td>
                <td align="right"><input name="submit" type="submit" value="Add" /></td>
                </tr>
                </table>
                </form>
                <?php
			}
			else	{
				$days_to_go = time() - $eventinfo['date']; // Days until the event
	
				/* PLAYLIST CLOSED */
				echo $clientzone->__text( 'playlist_closed', '<p><strong>Additions to your playlist are disabled as your event is only ' . substr( floor( $days_to_go / ( 60*60*24 ) ), 1 ) . ' days away.</strong></p>' );
                
				if( count( $playlist ) > 0 ) echo '<p>Existing playlist entries are displayed below.</p>';
            	
			}
			if( count( $playlist ) > 0 )	{ // Songs to display
				?>
                <p>Your playlist currently has <?php echo count( $playlist ) . _n( ' entry', ' entries', count( $playlist ) ); ?></p>
                <table width="100%" border="0" cellpadding="0" cellspacing="0" style="font-size:11px">
                <thead>
                <tr align="left"\>
                <th width="15%">Song</th>
                <th width="15%">Artist</th>
                <th width="15%">To be played</th>
                <th width="30%">Info</th>
                <th width="20%">Added By</th>
                <th width="5%">&nbsp;</th>
                </tr>
                </thead>
                <?php
				foreach( $playlist as $entry )	{
					print( '<tr>' );
					print( '<td>' . stripslashes( $entry->song ) . '</td>' );
					print( '<td>' . stripslashes( $entry->artist ) . '</td>' );
					print( '<td>' . stripslashes( $entry->play_when ) . ' </td>' );
					print( '<td>' . stripslashes( nl2br( htmlentities( $entry->info ) ) ) . '</td>' );
					print( '<td>' . stripslashes( $entry->added_by ) . ' (' . date( MDJM_SHORTDATE_FORMAT, strtotime( $entry->date_added ) ) . ')</td>' );
					print( '<td>' );
					if( $mdjm->mdjm_events->playlist_status( $eventinfo['date'] ) )	{
						print( '<a href="' . get_permalink() . '?playlist_id=' . $entry->id . '">Remove</a>' );
					}
					print( '</td>' );
					print( '</tr>' );
				}
			?>
			</table>
            <?php
			}
			else	{ // No songs in playlist
				?>
                <p>Your playlist is currently empty. <?php if( $mdjm->mdjm_events->playlist_status( $eventinfo['date'] ) ) echo 'Use the form above to start adding songs.'; ?></p>
                <?php	
			}
		}
		else	{ // no event found
			/* PLAYLIST NO EVENTS */
			echo $clientzone->__text( 'playlist_noevent', '<p>You do not have any confirmed events with us. The Playlist is only available once you have confirmed your event and signed your contract.</p><p>To begin planning your next event with us, please <a href="' . $mdjm->get_link( MDJM_CONTACT_PAGE, false ) . '">contact us now</a>.</p>' );
		}
	} // if( is_user_logged_in() )

	/* Guest access */
	elseif( isset( $_GET['mdjmeventid'] ) && !empty( $_GET['mdjmeventid'] ) )	{
		/* Get event info */
		$event = f_mdjm_get_guest_eventinfo( $_GET['mdjmeventid'] );
		$eventinfo = $mdjm->mdjm_events->event_detail( $event->ID );
		if( count( $event ) == 0 )	{
			echo '<p>ERROR: No event found. Please check the URL and try again.</p>';
		}
		else	{
			$clientinfo = get_userdata( get_post_meta( $event->ID, '_mdjm_event_client', true ) );	
			/* GUEST WELCOME */
			echo $clientzone->__text( 'playlist_guest_welcome', 'Welcome to the ' . MDJM_COMPANY . ' playlist management system.' );

			if( $mdjm->mdjm_events->playlist_status( $eventinfo['date'] ) )	{ // Open
                /* GUEST ADD */
				echo $clientzone->__text( 'playlist_guest_intro', '<p>You are adding songs to the playlist for ' . $clientinfo->first_name . ' ' . $clientinfo->last_name . '\'s event on ' . date("l, jS F Y", $eventinfo['date'] ) . '.</p><p>Add your playlist requests in the form below. All fields are required.</p>' );

                ?>
				<hr />
                <form action="<?php echo $mdjm->get_link( MDJM_PLAYLIST_PAGE ) . 'mdjmeventid=' . $_GET['mdjmeventid']; ?>" method="post" enctype="multipart/form-data" name="guest-playlist">
				<input type="hidden" name="event_id" value="<?php echo $event->ID; ?>" />
				<input type="hidden" name="client_id" value="<?php echo $clientinfo->ID; ?>" />
                <table border="0" cellpadding="0" cellspacing="0" style="font-size:11px" width="100%">
                <tr align="left">
                <th>Your First Name:</th>
                <th>Your Last Name:</th>
                <th>Song Name:</th>
                <th>Artist:</th>
                <th>&nbsp;</th>
                </tr>
                <tr>
                <td><input name="first_name" type="text" size="25" value="<?php echo $_POST['first_name'] ?>"<?php if ( !isset ( $_POST['first_name'] ) || empty ($_POST['first_name'] ) ) echo ' autofocus'; ?>/></td>
                <td><input name="last_name" type="text" size="25" value="<?php echo $_POST['last_name'] ?>" /></td>
                <td><input name="playlist_song" type="text" size="25"<?php if ( isset ( $_POST['first_name'] ) && !empty ($_POST['first_name'] ) ) echo ' autofocus'; ?> /></td>
                <td><input name="playlist_artist" type="text" size="25" /></td>
                <td align="center"><input name="submit" type="submit" value="Add" /></td>
                </tr>
                </table>
                </form>
                 <?php
			} // if( f_mdjm_is_playlist_open( $eventinfo->event_date ) )
			else	{
				?>
                <p>
                <?php
                /* GUEST ADD */
				echo $clientzone->__text( 'playlist_guest_closed', '<p>This playlist is currently closed. No songs can be added at this time.</p>' );
			} // 
		} // if( count( $eventinfo ) == 0 )
	} // elseif( isset( $_GET['mdjmeventid'] )...
	
	/* Not logged in */
	else	{
		$clientinfo->login();
	}
/*
*
*
*
*
*/
?>