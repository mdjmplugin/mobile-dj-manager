<?php
	/* Do not run unless the uninstall procedure was called by WordPress */
	if ( !defined( 'WP_UNINSTALL_PLUGIN' ) ) 
		exit();
	$mdjm_options = get_option ( 'mdjm_plugin_settings' );

/* Remove capabilities and roles */
	$role = get_role( 'administrator' );
	$role->remove_cap( 'manage_mdjm' );
	$role = get_role( 'dj' );
	$role->remove_cap( 'manage_mdjm' );
	
	remove_role( 'dj' );
	remove_role( 'client' );
	remove_role( 'inactive_client' );

/* Remove Transient info */
	$transient_name = array(
							'mdjm_call_home',
							'mdjm_is_beta',
							);
	foreach( $transient_name as $transient )	{
		delete_transient( $transient );
	}

/* Remove the contracts data -- NOT WORKING */
	/*$contract_posts = new WP_Query( array( 'post_type' => 'contract' ) );
	if ( $contract_posts->have_posts() ) {
		while ( $contract_posts->have_posts() ) {
			wp_delete_post( get_the_id(), true);
		}
	}*/

/* Remove the DB tables & data */
	if( isset( $mdjm_options['uninst_remove_db'] ) && $mdjm_options['uninst_remove_db'] == 'Y' )	{
		global $wpdb;
		require_once 'includes/config.inc.php';
		foreach( $db_tbl as $key => $value )	{
			$wpdb->query( 'DROP TABLE IF EXISTS ' . $value );
		}
	}
	
/* Remove the options */
	$option_name = array( 
						'mdjm_client_fields',
						'mdjm_plugin_settings',
						'mdjm_plugin_permissions',
						'mdjm_plugin_pages',
						'mdjm_db_version',
						'mdjm_schedules',
						'mdjm_cats',
						'mdjm_client_fields',
						'mdjm_equipment',
						'mdjm_packages',
						'mdjm_version',
						'mdjm_plugin_email_template_enquiry',
						'mdjm_plugin_email_template_client_booking_confirm',
						'mdjm_plugin_email_template_dj_booking_confirm',
						'mdjm_plugin_email_template_contract_review',
						'mdjm_updated',
						);
	foreach( $option_name as $option )	{
		delete_option( $option );
	}

?>