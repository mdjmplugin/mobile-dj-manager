<?php
	defined('ABSPATH') or die("Direct access to this page is disabled!!!");
			
function mdjm_packages_page()	{
	
	if( ! mdjm_employee_can( 'manage_packages' ) )	{
		wp_die(
			'<h1>' . __( 'Cheatin&#8217; uh?' ) . '</h1>' .
			'<p>' . __( 'You do not have permission to manage equipment packages.', 'mobile-dj-manager' ) . '</p>',
			403
		);
	}
	
	?>
	<div class="wrap">
	<div id="icon-themes" class="icon32"></div>
	<?php 
	settings_errors();
	$active_tab = isset( $_GET['tab'] ) ? $_GET['tab'] : 'packages';
	?>
	<h2 class="nav-tab-wrapper">
		<a href="admin.php?page=mdjm-packages&amp;tab=packages" class="nav-tab <?php echo $active_tab == 'packages' ? 'nav-tab-active' : ''; ?>">Available Packages</a>
		<a href="admin.php?page=mdjm-packages&amp;tab=equipment" class="nav-tab <?php echo $active_tab == 'equipment' ? 'nav-tab-active' : ''; ?>">Equipment List</a>
	</h2>
	<?php
	if( $active_tab == 'packages' ) {
		include( MDJM_PLUGIN_DIR . '/includes/admin/equipment/packages-page.php' );
	}
	elseif( $active_tab == 'equipment' ) {
		include( MDJM_PLUGIN_DIR . '/includes/admin/equipment/equipment-page.php' );
	}
	else	{
		wp_die( 'You do not have the necessary permissions to view this page!' );
	}
	?>
	</div>
	<?php
} // mdjm_packages_page