<?php
/**
 * * * * * * * * * * * * * * * MDJM * * * * * * * * * * * * * * *
 * djs.php
 *
 * Displays table of DJ's & enables adding new / editing existing
 *
 * Calls: class-mdjm-dj-table.php
 *
 * @since 1.0
 *
 */

	defined('ABSPATH') or die("Direct access to this page is disabled!!!");
	if ( !current_user_can( 'manage_mdjm' ) )  {
		wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
	}
			
/**
 * f_mdjm_render_djs_table
 * Render the table with list of DJ's
 *
 * Calls: class-mdjm-dj-table.php
 *
 * @since 1.0
*/
	function f_mdjm_render_djs_table()	{
		if( !class_exists( 'WP_List_Table' ) ){
			require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
		}
	
		if( !class_exists( 'MDJM_djs_table' ) ) {
			require_once( MDJM_PLUGIN_DIR . '/admin/includes/class/class-mdjm-dj-table.php' );
		}
		$djs_table = new MDJM_DJs_Table();
		?>
		</pre><div class="wrap"><h1><?php echo MDJM_DJ; ?>'s <a href="<?php echo admin_url(); ?>user-new.php" class="page-title-action">Add New</a></h1>
		<?php
		$djs_table->prepare_items();
		?>
		<form method="post" name="mdjm_dj_search" id="mdjm_dj_search">
		<input type="hidden" name="page" value="mdjm-djs">
		<?php
		//$djs_table->search_box( 'Search DJ\'s', 'search_id' );
		
		$djs_table->display(); 
		?>
        </form></div>
        <?php 
	} // f_mdjm_render_djs_table

/**
 * 
 * Process actions determined by the $_GET var
 *
 * Calls: various functions
 *
 * @since 1.0
*/

	if( isset($_GET['action'] ) )	{ // Action to process
		$func = 'f_mdjm_' . $_GET['action'];
		/* Check for actions */
		if( isset( $_GET['role'] ) && !empty( $_GET['role'] ) )	{
			$func( $_GET['dj_id'], $_GET['role'] );
			f_mdjm_render_djs_table();
		}
		else	{
			$func();
		}
	}
	
	else	{ // Display the Client table
		f_mdjm_render_djs_table();
	}
?> 