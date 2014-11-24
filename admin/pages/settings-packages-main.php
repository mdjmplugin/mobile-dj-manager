<?php
	defined('ABSPATH') or die("Direct access to this page is disabled!!!");
	if ( !current_user_can( 'manage_options' ) )  {
		wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
	}
	
	require_once WPMDJM_PLUGIN_DIR . '/admin/includes/functions.php';

	function f_mdjm_display_package_settings()	{
		?>
		<div class="wrap">
        <div id="icon-themes" class="icon32"></div>
        <?php 
		settings_errors();
		$active_tab = isset( $_GET['tab'] ) ? $_GET['tab'] : 'packages';
		?>
		<h2 class="nav-tab-wrapper">
			<a href="admin.php?page=mdjm-packages&tab=packages" class="nav-tab <?php echo $active_tab == 'packages' ? 'nav-tab-active' : ''; ?>">Available Packages</a>
			<a href="admin.php?page=mdjm-packages&tab=equipment" class="nav-tab <?php echo $active_tab == 'equipment' ? 'nav-tab-active' : ''; ?>">Equipment List</a>
		</h2>
		<?php
		if( $active_tab == 'packages' ) {
			include( WPMDJM_PLUGIN_DIR . '/admin/pages/settings-packages.php' );
		}
		elseif( $active_tab == 'equipment' ) {
			include( WPMDJM_PLUGIN_DIR . '/admin/pages/settings-equipment.php' );
		}
		else	{
			wp_die( 'You do not have the necessary permissions to view this page!' );
		}
		if( current_user_can( 'manage_options' ) && $lic_info )	{ submit_button(); }
		?>
        </div>
        <?php
	}
	
	f_mdjm_display_package_settings();
	
?>