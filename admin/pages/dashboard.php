<?php
/**
 * dashboard.php
 * 23/02./2015
 * @since 1.1
 * The MDJM Dashboard
 *
 * @version 1.0
 * 23/02/2015
 */
 	require_once( ABSPATH . 'wp-admin/admin.php' );
 	require_once( ABSPATH . 'wp-admin/includes/dashboard.php' );
	?>	
	<div class="wrap">
    <div id="dashboard-widgets-wrap">
	<?php wp_dashboard(); ?>
	</div><!-- dashboard-widgets-wrap -->
    </div>
    <?php
	require( ABSPATH . 'wp-admin/admin-footer.php' );

?>