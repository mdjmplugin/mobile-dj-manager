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
			require_once( MDJM_PLUGIN_DIR . '/admin/includes/class/class-mdjm-client-table.php' );
		}
		
		$clients_table = new MDJM_Clients_Table();
		?>
		</pre><div class="wrap"><h1>Clients <?php if( current_user_can( 'administrator' ) || dj_can( 'add_client' ) ) echo '<a href="' . admin_url() . 
			'user-new.php" class="page-title-action">Add New</a></h1>';
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
		global $mdjm;
		$client = get_userdata( $user_id );
		
		?>
        <div class="wrap">
        <h1><?php echo $client->display_name; ?> Contact Details</h1>
        <table class="form-table">
        <tr>
        <th class="row-title">Email:</th>
        <td><a href="<?php mdjm_get_admin_page( 'comms', 'echo' ); ?>&to_user=<?php echo $client->ID; ?>"><?php echo $client->user_email; ?></a></td>
        </tr>
        <tr>
        <th class="row-title">Primary Phone:</th>
        <td><?php echo ( !empty( $client->phone1 ) ? $client->phone1 : 'N/A' ); ?></td>
        </tr>
        <?php
		if( !empty( $client->phone2 ) )	{
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
		$client_events = $mdjm->mdjm_events->client_events( $user_id );
		
		if( $client_events )	{
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
				$eventinfo = $mdjm->mdjm_events->event_detail( $client_event->ID );
				
				if( $eventinfo['dj'] == get_current_user_id() )	{

					$playlist =  $mdjm->mdjm_events->count_playlist_entries( $client_event->ID );
					
					?>
                    <tr>
                    <td><a href="<?php echo mdjm_get_admin_page( 'events' ) . 
						's&post_status=all&post_type=mdjm-event&action=-1&mdjm_filter_date=0&mdjm_filter_type&mdjm_filter_dj=0&mdjm_filter_client=' 
						. $user_id . '&filter_action=Filter&paged=1&mode=list&action2=-1'; ?>"><?php echo date( 'd M Y', $eventinfo['date'] ); ?></a></td>
                    <td><?php echo $eventinfo['type']; ?></td>
                    <td><?php echo get_post_status_object( $client_event->post_status )->label; ?></td>
                    <td><?php echo _n( 'Song', 'Songs', $playlist, 'mobile-dj-manager' ); ?></td>
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

	if( isset( $_GET['action'] ) && !isset( $_POST['client'] ) )	{ // Action to process
		$func = 'f_mdjm_' . $_GET['action'];
		/* Check for actions */
		if( isset( $_GET['role'] ) && !empty( $_GET['role'] ) )	{
			$func( $_GET['client_id'], $_GET['role'] );
			f_mdjm_render_clients_table();
		}
		else	{
			$func( $_GET['client_id'] );
		}
	}
	
	else	{ // Display the Client table
		f_mdjm_render_clients_table();
	}
?> 