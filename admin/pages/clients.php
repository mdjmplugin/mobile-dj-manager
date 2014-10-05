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
	
		if( ! class_exists( 'MDJM_Clients_Table' ) ) {
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

/**
 * 
 * Process actions determined by the $_GET var
 *
 * Calls: various functions
 *
 * @since 1.0
*/

	if(isset ($_GET['action'] ) )	{ // Action to process
		$func = 'f_mdjm_' . $_GET['action'];
		$func();
	}
	
	else	{ // Display the Client table
		f_mdjm_render_clients_table();
	}
?> 