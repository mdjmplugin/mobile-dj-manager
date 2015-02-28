<?php
/**
 * transactions.php
 * 25/02/2015
 * @since 1.1
 *
 * @version 1.0
 */

	defined( 'ABSPATH' ) or die( "Direct access to this page is disabled!!!" );
	if ( !current_user_can( 'manage_options' ) )  {
		wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
	}
	
	// If recently updated, display the release notes
	f_mdjm_has_updated();

	if( !class_exists( 'WP_List_Table' ) )	{
		require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
	}
	if( !class_exists( 'MDJM_Transactions' ) )	{
		require_once( WPMDJM_PLUGIN_DIR . '/admin/includes/class/class-mdjm-transactions.php' );
	}
	
	?>
    <div class="wrap">
    <h2>Transactions</h2>
    <hr />
    <?php
	
	$mdjm_transactions = new MDJM_Transactions();
	
	if( isset( $_POST['s'] ) )	{
		$mdjm_transactions->prepare_items( $_POST['s'] );
	}
	else	{
		$mdjm_transactions->prepare_items();
	}
	
	/* Search form */
	/*
	?>
	<form method="post">
    <input type="hidden" name="page" value="mdjm-transactions">
	<?php $mdjm_transactions->search_box( 'Search Transactions', 'search_id' ); ?>
    </form>
    
    <?php */$mdjm_transactions->display(); ?>
    </div>
    <?php
?>