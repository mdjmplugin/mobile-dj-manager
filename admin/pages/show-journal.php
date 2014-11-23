<?php
	defined('ABSPATH') or die("Direct access to this page is disabled!!!");
	if ( !current_user_can( 'manage_options' ) && !current_user_can( 'manage_mdjm' ) )  {
		wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
	}

/**
 * * * * * * * * * * * * * * * MDJM * * * * * * * * * * * * * * *
 * show-journal.php
 *
 * Displays journal entries
 *
 * Calls: class-mdjm-journal-table.php
 *
 * @since 1.0
 *
 */	
 
	/* Check for plugin update */
	f_mdjm_has_updated();

/**
 * Retrieve the information submitted via $_GET
 * 
 *
 * @since 1.0
*/
	if( empty ( $_GET['client_id'] ) && empty ( $_GET['event_id'] ) )	{ 
		wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
	}
	
/**
 * Display the journal entries within the Admin UI
 * 
 * Calls: class-wp-list-table.php; class-mdjm-journal-table.php
 *
 * @since 1.0
*/
	function f_mdjm_render_journal_table()	{
		if( !class_exists( 'WP_List_Table' ) ){
			require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
		}
	
		if( !class_exists( 'MDJM_Journal_Table' ) ) {
			require_once( WPMDJM_PLUGIN_DIR . '/admin/includes/class-mdjm-journal-table.php' );
		}
		if( $_GET['client_id'] ) $client = true;
		if( $_GET['event_id'] ) $event = true;
		$journal_table = new MDJM_Journal_Table();
		?>
		
		</pre><div class="wrap"><h2>Journal Entry</h2>
        <?php
		$journal_table->prepare_items();
		?>
		<form method="post" name="mdjm_journal" id="mdjm_journal">
		<input type="hidden" name="page" value="mdjm-journal">
		<?php
		$journal_table->display(); 
		?>
        </form>
        <a class="button-secondary" href="<?php echo $_SERVER['HTTP_REFERER']; ?>" title="<?php _e( 'Back' ); ?>"><?php _e( 'Back' ); ?></a>
        </div>
        <?php 
	} // f_mdjm_render_journal_table
	
	f_mdjm_render_journal_table();
	
?>