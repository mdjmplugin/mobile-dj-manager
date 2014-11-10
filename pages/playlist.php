<?php
	defined('ABSPATH') or die("Direct access to this page is disabled!!!");
	
	global $wpdb, $mdjm_options, $eventinfo, $current_user, $playlist_result;
	
	require_once WPMDJM_PLUGIN_DIR . '/includes/config.inc.php';
	require_once WPMDJM_PLUGIN_DIR . '/includes/functions.php';
	
/*****************************************************************
-- LOGGED IN
*****************************************************************/
	if ( is_user_logged_in() && empty ( $_GET['mdjmeventid'] ) )	{ // User is logged in, show their event playlist
		get_currentuserinfo();
		f_mdjm_get_eventinfo( $db_tbl, $current_user );
		if ( $_GET['playlist_id'] ) { f_mdjm_remove_playlistsong( $db_tbl,$song_id=$_GET['playlist_id'] ); }
		if ( isset ( $_POST['submit'] ) ) { f_mdjm_add_playlistsong( $db_tbl, $eventinfo, $_POST); }
		f_mdjm_get_playlist( $db_tbl, $eventinfo );
		if( $wpdb->num_rows > 0 )	{
			?>
			<p>You currently have <?php echo $wpdb->num_rows; ?> <?php if( $wpdb->num_rows == 1 ) { echo "song"; } else { echo "songs"; }?> on your playlist for your event on <?php echo date( "l, jS F Y", strtotime( $eventinfo->event_date ) ); ?>. You can add and remove songs below.</p>
           <?php
		}
        else	{
			?>
			<p>There are no songs added to your playlist yet for your event on <?php echo date( 'l, jS F Y', strtotime( $eventinfo->event_date ) ); ?>. Use the form below to start adding.</p>
			<?php	
        }
		?>
            <p>Why not invite your friends to add their own requests to your playlist? You will see all requests they have submitted in the list below, and have the ability to remove them if you do not agree with their choices.</p>
            <p>Simply provide your friends with the following URL by posting it on your <a href="https://www.facebook.com" target="_blank">Facebook</a> profile or sending it to them via email.</p>
            <p>Your unique guest playlist page URL is <a href="<?php echo get_permalink(); ?>?mdjmeventid=<?php echo $eventinfo->event_guest_call ?>" target="_blank"><?php echo get_permalink(); ?>?mdjmeventid=<?php echo $eventinfo->event_guest_call ?></a></p>
			<?php
		if ( $eventinfo->event_date > date('c', strtotime('TODAY + ' . $mdjm_options['playlist_close'] . ' DAYS')) )	{ // Is the playlist open?
		?>
            <form action="<?php get_permalink(); ?>" method="post" enctype="multipart/form-data" name="playlist">
			<input type="hidden" name="added_by" value="<?php echo $current_user->first_name." ".$current_user->last_name; ?>" />
            <table border="0" cellpadding="0" cellspacing="0" style="font-size:11px" width="100%">
            <tr align="left">
            <th>Song Name:</th>
            <th>Artist:</th>
            <th>When to Play?</th>
            <th>Info:</th>
            <th>&nbsp;</th>
            </tr>
            <tr valign="top">
            <td><input name="playlist_song" type="text" size="30" /></td>
            <td><input name="playlist_artist" type="text" size="30" /></td>
            <td><select name="playlist_when"><?php
				$pl_when = explode( "\n", $mdjm_options['playlist_when'] );
				foreach( $pl_when as $when )	{
					?>
					<option value="<?php echo $when; ?>"><?php echo $when; ?></option>
					<?php	
				}
			?>
            </td>
            <td><textarea name="playlist_info" placeholder="Optional: add information if you selected Other from the drop down list"></textarea></td>
            <td align="right"><input name="submit" type="submit" value="Add" /></td>
            </tr>
            </table>
            </form>
		<?php 
	   	}
	   	else	{ // If the playlist is closed
	   		$days_to_go = time() - strtotime( $eventinfo->event_date ); // Days until the event
			?>
			<p><strong>Additions to your playlist are disabled as your event is only <?php echo substr( floor( $days_to_go/(60*60*24)), 1 ); ?> days away!</strong><br /><br />
			Existing playlist entries are displayed below.</p>
            <?php
	   	}
		if( $wpdb->num_rows > 0 )	{ ?>
            <h4>Your Current Playlist</h4>
            <table width="100%" border="0" cellpadding="0" cellspacing="0" style="font-size:11px">
            <tr align="left"\>
            <th width="15%">Song</th>
            <th width="15%">Artist</th>
            <th width="15%">To be played</th>
            <th width="30%">Info</th>
            <th width="20%">Added By</th>
            <th width="5%">&nbsp;</th>
            </tr>
		<?php
			$i = 1;
			foreach($playlist_result as $playlist)	{
				if ($i == 1) { $bgcolour = "#FFF"; } elseif ($i == 2) { $bgcolour = "#EEE"; }
				print( '<tr bgcolor="' . $bgcolour . '">' );
				print( '<td>' . stripslashes( $playlist->song ) . '</td>' );
				print( '<td>' . stripslashes( $playlist->artist ) . '</td>' );
				print( '<td>' . stripslashes( $playlist->play_when ) . ' </td>' );
				print( '<td>' . stripslashes( nl2br( htmlentities( $playlist->info ) ) ) . '</td>' );
				print( '<td>' . stripslashes( $playlist->added_by ) . ' (' . date( 'd/m/Y', strtotime( $playlist->date_added ) ) . ')</td>' );
				print( '<td>' );
				if( $eventinfo->event_date > date('c', strtotime('TODAY + ' . $mdjm_options['playlist_close'] . ' DAYS')) )	{
					print( '<a href="' . get_permalink() . '?playlist_id=' . $playlist->id . '">Remove</a>' );
				}
				print( '</td>' );
				print( '</tr>' );
				$i++;
				if ( $i == 3 ) { $i = 1; }
			}
			?>
			</table>
            <?php
		}
	} // End if is_user_logged_in()
	
/*****************************************************************
-- NOT LOGGED IN
*****************************************************************/
	
	else	{ // User is not logged in
		if ( isset ( $_GET['mdjmeventid'] ) )	{ // Guest user
			if ( !empty ($_GET['mdjmeventid'] ) )	{ // If we have the event id wecan display the form
				f_mdjm_get_guest_eventinfo( $db_tbl, $event_id=$_GET['mdjmeventid'] );
				if ( isset ( $_POST['submit'] ) ) { 
					$_POST['added_by'] = $_POST['first_name'] . ' ' . $_POST['last_name'];
					f_mdjm_add_playlistsong( $db_tbl, $eventinfo, $_POST );
				}
				if ( count ( $eventinfo ) == 0 )	{
					wp_die( '<pre>ERROR: The page you have arrived at is invalid. Please check it again or contact the person who sent you the link. To return to the home page click here - <a href="'. home_url() .'">' . home_url() . '</a></pre>' );
				}
				else	{
					$event_owner = get_userdata( $eventinfo->user_id );
					?>
					<p>Welcome to the <?php echo get_bloginfo( 'name' ); ?> playlist management system.</p>
                    <?php
					if ( $eventinfo->event_date > date('c', strtotime('TODAY + ' . $mdjm_options['playlist_close'] . ' DAYS')) )	{ // Is the playlist open?
					?>
                        <p>You are adding songs to the playlist for <?php echo $event_owner->first_name . " " . $event_owner->last_name; ?>'s event on <?php echo date("l, jS F Y",strtotime($eventinfo->event_date)); ?>.</p>
                        <p>Please add your playlist requests in the form below. All fields are required.</p>
                        <hr />
                        <form action="<?php echo get_permalink() . '?mdjmeventid=' . $_GET['mdjmeventid']; ?>" method="post" enctype="multipart/form-data" name="guest-playlist">
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
					}
					else	{ // Playlist closed
						$days_to_go = time() - strtotime($eventinfo->event_date); // Days until the event
						?>
                        <p><strong>The playlist for <?php echo $event_owner->first_name . " " . $event_owner->last_name; ?>'s event on <?php echo date("l, jS F Y",strtotime( $eventinfo->event_date ) ); ?> is now closed as the event is only <?php echo substr( floor( $days_to_go/(60*60*24) ) ,1 ); ?> days away!</strong></p>
                        <?php
					}
				}
			} // End if ( !empty ($_GET['mdjmeventid'] )
			else	{ // Invalid URL
				wp_die( '<pre>ERROR: The page you have arrived at is invalid. Please check it again or contact the person who sent you the link. To return to the home page click here - <a href="'. home_url() .'">' . home_url() . '</a></pre>' );
			}
		} // End if ( isset ( $_GET['mdjmeventid'] ) )
		else	{ // Show the login form
			f_mdjm_show_user_login_form();
		}
	}
	add_action( 'wp_footer', f_wpmdjm_print_credit );
?>