<?php
/*
* uninstall.php
* 04/10/2014
* @since 0.8
* Uninstallation procedures for when plugin is deleted
*/

/* Do not run unless the uninstall procedure was called by WordPress */
	if( !defined( 'WP_UNINSTALL_PLUGIN' ) )	{
		exit();
	}
	
	$mdjm_options = get_option ( 'mdjm_plugin_settings' );

/* Remove capabilities and roles */
	$role = get_role( 'administrator' );
	$role->remove_cap( 'manage_mdjm' );
	$role = get_role( 'dj' );
	$role->remove_cap( 'manage_mdjm' );
	
	remove_role( 'dj' );
	remove_role( 'inactive_dj' );
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

/* Remove the template posts -- NOT WORKING */
	if( isset( $mdjm_options['uninst_remove_mdjm_templates'] ) && $mdjm_options['uninst_remove_mdjm_templates'] == 'Y' )	{
		$mdjm_template_types = array( 'contract', 'email_template' );
		
		foreach( $mdjm_template_types as $mdjm_template )	{
			$mdjm_posts = get_pages( array( 'post_type' => $mdjm_template ) );
			foreach ( $mdjm_posts as $mdjm_post ) {
				wp_delete_post( $mdjm_post, false );
			}
		}
	}

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
						'mdjm_updated',
						'mdjm_debug',
						'mdjm_frontend_text',
						'mdjm_pp_options',
						);
	foreach( $option_name as $option )	{
		delete_option( $option );
	}

?>