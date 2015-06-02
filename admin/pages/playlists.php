<?php
	defined('ABSPATH') or die("Direct access to this page is disabled!!!");
	if ( !current_user_can( 'manage_options' ) && !current_user_can( 'manage_mdjm' ) )  {
		wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
	}

	f_mdjm_has_updated();

/**
* * * * * * * * * * * * * * * MDJM * * * * * * * * * * * * * * *
* events.php
*
* Displays table of events & enables adding new / editing existing
*
* Calls: class-mdjm-event-table.php
*
* @since 1.0
*
*/

/**
 * Display playlist entries for event within the Admin UI
 * 
 * Calls: class-wp-list-table.php; class-wp-list-table.php
 *
 * @since 1.0
*/	
	function f_mdjm_render_playlist_table()	{
		global $mdjm;
		
		if( empty( $_GET['event_id'] ) )
			wp_die( 'ERROR: No event to list the playlist for!<br />' . 
			'<a class="button-secondary" href="' . $_SERVER['HTTP_REFERER'] . '" title="' . __( 'Back' ) . '">' . __( 'Back' ) . '</a>' );
			
		if( !current_user_can( 'administrator' ) && !$mdjm->mdjm_events->is_my_event( $_GET['event_id'] ) )
			wp_die( 'ERROR: You can only view the playlists for your own event!<br />' . 
			'<a class="button-secondary" href="' . $_SERVER['HTTP_REFERER'] . '" title="' . __( 'Back' ) . '">' . __( 'Back' ) . '</a>' );
		
		if( !class_exists( 'WP_List_Table' ) ){
			require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
		}
	
		if( !class_exists( 'MDJM_PlayList_Table' ) ) {
			require_once( WPMDJM_PLUGIN_DIR . '/admin/includes/class/class-mdjm-playlist-table.php' );
		}
		
		$playlist_table = new MDJM_PlayList_Table();
		
		// Email the playlist
		if( isset( $_POST['email_pl'] ) && $_POST['email_pl'] == 'Email me this List' )	{
			$playlist_table->send_to_email( $_POST, $_GET );	
		}
				
		?>
		</pre><div class="wrap"><h2>Event Playlist</h2>
		<?php
		$playlist_table->prepare_items();
		?>
		<form method="post" name="mdjm_playlist" id="mdjm_playlist">
		<input type="hidden" name="page" value="mdjm-playlist">
		<?php
		$playlist_table->display(); 
		?>
        </form>
        <a class="button-secondary" href="<?php echo $_SERVER['HTTP_REFERER']; ?>" title="<?php _e( 'Back' ); ?>"><?php _e( 'Back' ); ?></a>
        </div>
        <?php 
	} // f_mdjm_render_playlist_table

/**
 * Show printable playlist
 * 
 *
 * @since 1.0
*/
	function f_mdjm_print_playlist( $post_data )	{
		global $wpdb;
		
		if( empty( $post_data['event_id'] ) )
			wp_die( 'ERROR: No event to list the playlist for!<br />' . 
			'<a class="button-secondary" href="' . $_SERVER['HTTP_REFERER'] . '" title="' . __( 'Back' ) . '">' . __( 'Back' ) . '</a>' );
			
		if( !current_user_can( 'administrator' ) && !!$mdjm->mdjm_events->is_my_event( $post_data['event_id'] ) )
			wp_die( 'ERROR: You can only view the playlists for your own event!<br />' . 
			'<a class="button-secondary" href="' . $_SERVER['HTTP_REFERER'] . '" title="' . __( 'Back' ) . '">' . __( 'Back' ) . '</a>' );
				
		$print_query = 'SELECT * FROM `' . MDJM_PLAYLIST_TABLE . '` WHERE `event_id` = ' . $post_data['event_id'] . ' ORDER BY `' . $post_data['order_pl_by'] . '` ASC';
		$print_result = $wpdb->get_results( $print_query );
		$pl_ttl = $wpdb->num_rows;
		
		if( !isset( $post_data['repeat_headers'] ) || empty( $post_data['repeat_headers'] ) || $post_data['repeat_headers'] == 0 )	{
			$repeat = 0;
		}
		else	{
			$repeat = $post_data['repeat_headers'];
		}
		
		$i = 0;
		
		$eventinfo = get_post( $post_data['event_id'] );
		$client = get_userdata( get_post_meta( $eventinfo->ID, '_mdjm_event_client', true ) );
		
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
        <p>Client Name: <?php echo $client->first_name . ' ' . $client->last_name; ?><br />
        Event Date: <?php echo date( "l, jS F Y", strtotime( get_post_meta( $eventinfo->ID, '_mdjm_event_date', true ) ) ); ?><br />
        Event Type: 
		<?php
		$event_types = get_the_terms( $eventinfo->ID, 'event-types' );
		if( is_array( $event_types ) )	{
			foreach( $event_types as $key => $event_type ) {
				$event_types[$key] = $event_type->name;
			}
			echo implode( "<br/>", $event_types );
		}
       ?>
        <br />
        No. Songs in Playlist: <?php echo $pl_ttl; ?><br /></p>
        <hr />
        <table border="0" cellpadding="0" cellspacing="0" width="90%" align="center">
        <tr height="30">
        <th width="15%">Artist</th>
        <th width="15%">Song</th>
        <th width="15%">When to Play</th>
        <th width="40%">Info</th>
        <th width="15%">Added By</th>
        </tr>
        <?php
		foreach( $print_result as $playlist )	{
			if( $repeat > 0 && $i == $repeat )	{
					?>
                    </table>
                    <p style="page-break-after:always;">&nbsp;</p>
                    <table border="1" cellpadding="0" cellspacing="0" width="90%" align="center">
                    <tr height="30">
                    <th width="15%">Artist</th>
                    <th width="15%">Song</th>
                    <th width="15%">When to Play</th>
                    <th width="40%">Info</th>
                    <th width="15%">Added By</th>
                    </tr>
					<?php
					$i = 0;
				}
			?>
            <tr height="30">
            <td><?php echo stripslashes( $playlist->artist ); ?></td>
            <td><?php echo stripslashes( $playlist->song ); ?></td>
            <td><?php echo stripslashes( $playlist->play_when ); ?></td>
            <td><?php echo stripslashes( $playlist->info ); ?></td>
            <td><?php echo stripslashes( $playlist->added_by ); ?></td>
            </tr>
            <?php
			$i++;	
		}
        ?>
        </table>
        <p style="text-align: center" class="description">Powered by <a style="color:#F90" href="<?php echo mdjm_get_admin_page( 'mydjplanner' ); ?> 
					" target="_blank"><?php echo MDJM_NAME; ?></a>, version <?php echo MDJM_VERSION_NUM; ?></p>
        <?php
	} // f_mdjm_print_playlist
		
	
/**
 * Process actions submitted via $_GET or show the main events page
 * 
 *
 * @since 1.0
*/
	if( isset( $_POST['print_pl'] ) && $_POST['print_pl'] == 'Print this List' )
		f_mdjm_print_playlist( $_POST );

	if( empty( $_POST['print_pl'] ) || $_POST['print_pl'] != 'Print this List' )
		f_mdjm_render_playlist_table();	
?> 