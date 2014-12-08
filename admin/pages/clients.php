<?php
/**
 * * * * * * * * * * * * * * * MDJM * * * * * * * * * * * * * * *
 * clients.php
 *
 * Displays table of clients & enables adding new / editing existing
 *
 * Calls: class-mdjm-client-table.php
 *
 * @since 1.0
 *
 */

	defined('ABSPATH') or die("Direct access to this page is disabled!!!");
	if ( !current_user_can( 'manage_mdjm' ) )  {
		wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
	}
	
	// If recently updated, display the release notes
	f_mdjm_has_updated();
	
/**
 * f_mdjm_render_clients_table
 * Render the table with list of clients
 *
 * Calls: class-mdjm-client-table.php
 *
 * @since 1.0
*/
	function f_mdjm_render_clients_table()	{ // Show the client list
		if( !class_exists( 'WP_List_Table' ) ){
			require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
		}
	
		if( !class_exists( 'MDJM_Clients_Table' ) ) {
			require_once( WPMDJM_PLUGIN_DIR . '/admin/includes/class-mdjm-client-table.php' );
		}
		
		$clients_table = new MDJM_Clients_Table();
		?>
		</pre><div class="wrap"><h2>Clients <?php if( current_user_can( 'administrator' ) || dj_can( 'add_client' ) ) echo '<a href="' . admin_url() . 'user-new.php" class="add-new-h2">Add New</a></h2>';
		$clients_table->prepare_items();
		?>
		<form method="post" name="mdjm_client_search" id="mdjm_client_search">
		<input type="hidden" name="page" value="mdjm-clients">
		<?php
		$clients_table->search_box( 'Search Clients', 'search_id' );
		
		$clients_table->display(); 
		?>
        </form></div>
        <?php 
	} // f_mdjm_render_client_table

/*
* f_mdjm_view_client
* 08/12/2014
* @since 0.9.7
* Displays client contact information for DJ's
*/
	function f_mdjm_view_client( $user_id )	{
		global $wpdb, $mdjm_options;
		$client = get_userdata( $user_id );
		
		?>
        <div class="wrap">
        <h2><?php echo $client->display_name; ?> Contact Details</h2>
        <table class="form-table">
        <tr>
        <th class="row-title">Email:</th>
        <td><a href="<?php f_mdjm_admin_page( 'comms' ); ?>&to_user=<?php echo $client->ID; ?>"><?php echo $client->user_email; ?></a></td>
        </tr>
        <tr>
        <th class="row-title">Primary Phone:</th>
        <td><?php echo $client->phone1; ?></td>
        </tr>
        <?php
		if( isset( $client->phone2 ) && !empty( $client->phone2 ) )	{
			?>
            <tr>
            <th class="row-title">Secondary Phone:</th>
            <td><?php echo $client->phone2; ?></td>
            </tr>
            <?php	
		}
		?>
        </table>
        <h3>Event List</h3>
        <?php
		$client_events = f_mdjm_admin_get_client_events( $user_id );
		if( !is_array( $client_events ) )	{
			$client_events = array( $client_events );
		}
		if( count( $client_events > 0 ) )	{
			?>
            <table class="widefat">
            <thead>
            <th>Event Date</th>
            <th>Type</th>
            <th>Status</th>
            <th>Playlist</th>
            </thead>
            <?php
			foreach( $client_events as $client_event )	{
				if( $client_event->event_dj == get_current_user_id() && f_mdjm_event_is_active( $client_event->event_id ) )	{
					include( WPMDJM_PLUGIN_DIR . '/includes/config.inc.php' );
					$playlist = $wpdb->get_var( "SELECT COUNT(*) FROM " . $db_tbl['playlists'] . " WHERE event_id = " . $client_event->event_id );
					if ( $playlist == 0 ) { $play_count = $playlist . ' Songs'; }
					elseif ( $playlist == 1 ) { $play_count = '<a href="' . admin_url( 'admin.php?page=mdjm-events&action=render_playlist_table&event=' . $client_event->event_id ) . '">' .  $playlist . ' Song</a>'; }
					else { $play_count = '<a href="' . admin_url( 'admin.php?page=mdjm-events&action=render_playlist_table&event=' . $client_event->event_id ) . '">' .  $playlist . ' Song</a>s'; }
					?>
                    <tr>
                    <td><a href="<?php f_mdjm_admin_page( 'events' ); ?>&action=view_event_form&event_id=<?php echo $client_event->event_id; ?>"><?php echo date( 'd M Y', strtotime( $client_event->event_date ) ); ?></a></td>
                    <td><?php echo $client_event->event_type; ?></td>
                    <td><?php echo $client_event->contract_status; ?></td>
                    <td><?php echo $play_count; ?></td>
                    </tr>
                    <?php
				}
			}
			?>
            </table>
            <?php
		}
		else	{
			?>
            <p>The client has no events in the system where you are assigned as the DJ</p>
            <?php	
		}
		?>
        </div>
        <?php
		
	} // f_mdjm_view_client

/**
 * 
 * Process actions determined by the $_GET var
 *
 * Calls: various functions
 *
 * @since 1.0
*/

	if( isset( $_GET['action'] ) )	{ // Action to process
		$func = 'f_mdjm_' . $_GET['action'];
		$func( $_GET['client_id'] );
	}
	
	else	{ // Display the Client table
		f_mdjm_render_clients_table();
	}
?> 