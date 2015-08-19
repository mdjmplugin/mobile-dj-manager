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
	
	global $wpdb;
	
	$settings = get_option( 'mdjm_uninst' );
	
	// Delete users		
	if( !empty( $settings['uninst_remove_mdjm_users'] ) )	{
		$roles = array( 'client'			 => 'Client',
						'inactive_client'	=> 'Inactive Client',
						'dj'				 => 'DJ', 
						'inactive_dj'   		=> 'Inactive DJ' );
		
		// Loop through roles array removing users
		foreach( $roles as $role => $display )	{
			$args = array( 'role' => $role,
						   'orderby' => 'display_name',
						   'order' => 'ASC' );
			
			$mdjm_users = get_users( $args );
			
			foreach( $mdjm_users as $mdjm_user )	{
				wp_delete_user( $mdjm_user->ID );
			}
		} // End foreach( $roles as $role => $display )
	}
	
/* -- Remove capabilities -- */
	$role = get_role( 'administrator' );
	$role->remove_cap( 'manage_mdjm' );
	$role = get_role( 'dj' );
	
/* -- Remove roles -- */
	remove_role( 'dj' );
	remove_role( 'inactive_dj' );
	remove_role( 'client' );
	remove_role( 'inactive_client' );
	
/* -- Remove MDJM Data -- */			
	$data_type = array( 'Communications'		   => 'mdjm_communication',
						'Contact Forms'			=> 'mdjm-contact-form',
						'Contact Form Fields'	  => 'mdjm-contact-field',
						'Contract Templates'	   => 'contract',
						'Signed Contracts'		 => 'mdjm-signed-contract',
						'Email Templates'		  => 'email_template',
						'Events'				   => 'mdjm-event',
						'Transactions'			 => 'mdjm-transaction',
						'Venues' 				   => 'mdjm-venue' );
	
	$mdjm_custom = array( 'mdjm_communication',
						  'mdjm-contact-form',
						  'mdjm-contact-field',
						  'mdjm-signed-contract',
						  'mdjm-event',
						  'mdjm-transaction',
						  'mdjm-venue' );
										
	$templates = array( 'contract', 'email_template' );
						
	// Loop through the array removing the data
	foreach( $data_type as $display => $type )	{
		// Check if we are deleting
		if( empty( $settings['uninst_remove_mdjm_posts'] ) && in_array( $type, $mdjm_custom ) )
			continue;
		
		if( empty( $settings['uninst_remove_mdjm_templates'] ) && in_array( $type, $templates ) )
			continue;
		
		$mdjm_posts = $wpdb->get_results( "SELECT ID FROM " . $wpdb->posts . " WHERE `post_type` = '" . $type . "'" );
		
		// Delete the post and all data permanently
		foreach( $mdjm_posts as $mdjm_post )	{
			wp_delete_post( $mdjm_post->ID, true );
		}
		
	} // End foreach( $data_type as $type )
	
/* -- Remove custom DB Tables -- */
	// If the option to remove the tables is not selected, skip
	if( !empty( $settings['uninst_remove_db'] ) )	{	
		$tables = array( 'Events'		   => $wpdb->prefix . 'mdjm_events', 
						 'Playlist'		 => $wpdb->prefix . 'mdjm_playlists',
						 'Music Library'	=> $wpdb->prefix . 'mdjm_music_library',
						 'Transactions'	 => $wpdb->prefix . 'mdjm_trans',
						 'Journal'		  => $wpdb->prefix . 'mdjm_journal',
						 'Availability'	 => $wpdb->prefix . 'mdjm_avail' );
		
		foreach( $tables as $table_display => $table_name )	{
			$results = $wpdb->get_results( "SHOW TABLES LIKE '" . $table_name . "'" );
			if( $results )
				$wpdb->query( 'DROP TABLE IF EXISTS ' . $table_name );
		}
	}
	
/* -- Remove MDJM Pages -- */
	// If the option to remove the pages is not selected, skip
	if( !empty( $settings['uninst_remove_mdjm_pages'] ) )	{
		$mdjm_pages = get_option( 'mdjm_plugin_pages' );
		
		// Do not delete the contact page
		unset( $mdjm_pages['contact_page'] );
		
		foreach( $mdjm_pages as $mdjm_page )	{
			wp_delete_post( $mdjm_page, true );
		} // End foreach( $mdjm_pages as $mdjm_page )
			
	}
	
/* -- Remove setting options -- */
	delete_option( 'mdjm_availability_settings' );
			
	delete_option( 'mdjm_cats' );
	delete_option( 'mdjm_equipment' );
	delete_option( 'mdjm_packages' );
	
	delete_option( 'mdjm_client_fields' );
	
	delete_option( 'mdjm_clientzone_settings' );
	
	delete_option( 'mdjm_email_settings' );
	delete_option( 'mdjm_event_settings' );
	delete_option( 'mdjm_frontend_text' );
	delete_option( 'mdjm_plugin_settings' );
	
	delete_option( 'mdjm_payment_settings' );
	delete_option( 'mdjm_paypal_settings' );
	delete_option( 'mdjm_playlist_settings' );
	delete_option( 'mdjm_plugin_pages' );
	delete_option( 'mdjm_plugin_permissions' );
	delete_option( 'mdjm_plugin_settings' );
	delete_option( 'mdjm_pp_settings' );
	delete_option( 'mdjm_templates_settings' );
				
	delete_option( 'mdjm_schedules' );
	
	delete_option( 'mdjm_version');
	delete_option( 'mdjm_db_version' );
	delete_option( 'mdjm_updated' );
	delete_option( 'mdjm_uninst' );
	delete_option( 'mdjm_debug_settings' );
	delete_option( 'm_d_j_m_has_initiated' );
?>