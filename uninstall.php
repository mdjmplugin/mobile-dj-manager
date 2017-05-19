<?php
/**
 * Uninstallation procedures for MDJM.
 * What happens here is determined by the plugin uninstallation settings.
 * 
 * @since 0.8
 */

// Do not run unless the uninstall procedure was called by WordPress
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) )	{
	exit;
}

// Call the MDJM main class file
include_once( 'mobile-dj-manager.php' );
	
global $wpdb;

ignore_user_abort( true );

if ( ! mdjm_is_func_disabled( 'set_time_limit' ) && ! ini_get( 'safe_mode' ) ) {
    @set_time_limit( 0 );
}

remove_action( 'save_post_mdjm-event', 'mdjm_save_event_post', 10, 3 );
remove_action( 'save_post_mdjm-package', 'mdjm_save_package_post', 10, 2 );
remove_action( 'save_post_mdjm-addon', 'mdjm_save_addon_post', 10, 2 );
remove_action( 'save_post_mdjm-transaction', 'mdjm_save_txn_post', 10, 3 );
remove_action( 'save_post_mdjm-venue', 'mdjm_save_venue_post', 10, 3 );
remove_action( 'mdjm_delete_package', 'mdjm_remove_package_from_events' );
remove_action( 'mdjm_delete_addon', 'mdjm_remove_addons_from_packages', 10 );
remove_action( 'mdjm_delete_addon', 'mdjm_remove_addons_from_events', 15 );
remove_action( 'before_delete_post', 'mdjm_deleting_package' );
remove_action( 'wp_trash_post', 'mdjm_deleting_package' );
remove_action( 'before_delete_post', 'mdjm_deleting_addon' );
remove_action( 'wp_trash_post', 'mdjm_deleting_addon' );

if ( mdjm_get_option( 'remove_on_uninstall' ) )	{

	// Delete the Custom Post Types
	$mdjm_taxonomies = array(
		'package-category', 'addon-category', 'event-types', 'enquiry-source', 'playlist-category',
		'transaction-types', 'venue-details'
	);
	
	$mdjm_post_types = array(
		'mdjm-event', 'mdjm-package', 'mdjm-addon', 'mdjm_communication',
		'contract', 'mdjm-signed-contract', 'mdjm-custom-field', 'email_template',
		'mdjm-playlist', 'mdjm-quotes', 'mdjm-transaction', 'mdjm-venue'
	);

    foreach ( $mdjm_post_types as $post_type ) {

		$mdjm_taxonomies = array_merge( $mdjm_taxonomies, get_object_taxonomies( $post_type ) );
		$items = get_posts( array( 'post_type' => $post_type, 'post_status' => 'any', 'numberposts' => -1, 'fields' => 'ids' ) );

		if ( $items ) {
			foreach ( $items as $item ) {
				wp_delete_post( $item, true);
			}
		}
	}


	// Delete Terms & Taxonomies
	foreach ( array_unique( array_filter( $mdjm_taxonomies ) ) as $taxonomy )	{
	
		$terms = $wpdb->get_results( $wpdb->prepare(
			"SELECT t.*, tt.*
			FROM $wpdb->terms
			AS t
			INNER JOIN $wpdb->term_taxonomy
			AS tt
			ON t.term_id = tt.term_id
			WHERE tt.taxonomy IN ('%s')
			ORDER BY t.name ASC", $taxonomy
		) );
	
		// Delete Terms.
		if ( $terms ) {
			foreach ( $terms as $term ) {
				$wpdb->delete( $wpdb->term_relationships, array( 'term_taxonomy_id' => $term->term_taxonomy_id ) );
				$wpdb->delete( $wpdb->term_taxonomy, array( 'term_taxonomy_id' => $term->term_taxonomy_id ) );
				$wpdb->delete( $wpdb->terms, array( 'term_id' => $term->term_id ) );
			}
		}
	
		// Delete Taxonomies.
		$wpdb->delete( $wpdb->term_taxonomy, array( 'taxonomy' => $taxonomy ), array( '%s' ) );
	}

	// Delete Plugin Pages
	$mdjm_pages = array(
		'app_home_page', 'contracts_page', 'payments_page',
		'playlist_page', 'profile_page', 'quotes_page'
	);

	foreach ( $mdjm_pages as $mdjm_page ) {

		$page = mdjm_get_option( $mdjm_page, false );

		if ( $page )	{
			wp_delete_post( $page, true );
		}

	}

	// Remove setting options
	$all_options = array(
		'mdjm_api_data',
		'mdjm_availability_settings',
		'mdjm_cats',
		'mdjm_clientzone_settings',
		'mdjm_client_fields',
		'mdjm_completed_upgrades',
		'mdjm_db_update_to_13',
		'mdjm_db_version',
		'mdjm_debug_settings',
		'mdjm_email_settings',
		'mdjm_enquiry_terms_created',
		'mdjm_equipment',
		'mdjm_event_settings',
		'mdjm_frontend_text',
		'mdjm_packages',
		'mdjm_playlist_import',
		'mdjm_playlist_settings',
		'mdjm_plugin_pages',
		'mdjm_plugin_permissions',
		'mdjm_plugin_settings',
		'mdjm_schedules',
		'mdjm_settings',
		'mdjm_templates_settings',
		'mdjm_txn_terms_13',
		'mdjm_updated',
		'mdjm_update_me',
		'mdjm_version',
		'mdjm_version_upgraded_from'
	);

	foreach ( $all_options as $all_option )	{
		delete_option( $all_option );
	}

	$availability_table = $wpdb->prefix . 'mdjm_avail';

	// Remove all database tables
	$wpdb->query( "DROP TABLE IF EXISTS $availability_table" );

	// Remove any transients and options we've left behind
	$wpdb->query( "DELETE FROM $wpdb->options WHERE option_name LIKE '\_transient\_mdjm\_%'" );
	$wpdb->query( "DELETE FROM $wpdb->options WHERE option_name LIKE '\_transient\_timeout\_mdjm\_%'" );

	// Remove roles and capabilities
	$all_caps = array(
		// MDJM Admin
		'manage_mdjm',
		
		// Clients
		'mdjm_client_edit', 'mdjm_client_edit_own',
		
		// Employees
		'mdjm_employee_edit',
		
		// Packages
		'mdjm_package_edit_own', 'mdjm_package_edit',
		'publish_mdjm_packages', 'edit_mdjm_packages',
		'edit_others_mdjm_packages', 'delete_mdjm_packages',
		'delete_others_mdjm_packages', 'read_private_mdjm_packages',
	
		// Comm posts
		'mdjm_comms_send', 'edit_mdjm_comms', 'edit_others_mdjm_comms',
		'publish_mdjm_comms', 'read_private_mdjm_comms', 
		'edit_published_mdjm_comms', 'delete_mdjm_comms',
		'delete_others_mdjm_comms', 'delete_private_mdjm_comms',
		'delete_published_mdjm_comms', 'edit_private_mdjm_comms',
		
		// Event posts
		'mdjm_event_read', 'mdjm_event_read_own', 'mdjm_event_edit',
		'mdjm_event_edit_own', 'publish_mdjm_events', 'edit_mdjm_events',
		'edit_others_mdjm_events', 'delete_mdjm_events', 'delete_others_mdjm_events',
		'read_private_mdjm_events',
		
		// Quote posts
		'mdjm_quote_view_own', 'mdjm_quote_view', 'edit_mdjm_quotes',
		'edit_others_mdjm_quotes', 'publish_mdjm_quotes', 
		'read_private_mdjm_quotes', 'edit_published_mdjm_quotes',
		'edit_private_mdjm_quotes', 'delete_mdjm_quotes', 'delete_others_mdjm_quotes',
		'delete_private_mdjm_quotes', 'delete_published_mdjm_quotes',
	
		// Reports
		'view_event_reports',
	
		// Templates
		'mdjm_template_edit', 'edit_mdjm_templates',
		'edit_others_mdjm_templates', 'publish_mdjm_templates', 'read_private_mdjm_templates',
		'edit_published_mdjm_templates', 'edit_private_mdjm_templates', 'delete_mdjm_templates',
		'delete_others_mdjm_templates', 'delete_private_mdjm_templates',
		'delete_published_mdjm_templates',
		
		// Transaction posts
		'mdjm_txn_edit', 'edit_mdjm_txns', 'edit_others_mdjm_txns', 'publish_mdjm_txns',
		'read_private_mdjm_txns', 'edit_published_mdjm_txns', 'edit_private_mdjm_txns',
		'delete_mdjm_txns', 'delete_others_mdjm_txns', 'delete_private_mdjm_txns',
		'delete_published_mdjm_txns',
		
		// Venue posts
		'mdjm_venue_read', 'mdjm_venue_edit', 'edit_mdjm_venues',
		'edit_others_mdjm_venues', 'publish_mdjm_venues', 'read_private_mdjm_venues',
		'edit_published_mdjm_venues', 'edit_private_mdjm_venues', 'delete_mdjm_venues',
		'delete_others_mdjm_venues', 'delete_private_mdjm_venues',
		'delete_published_mdjm_venues'
	);

	$roles = MDJM()->roles->get_roles();

	foreach( $roles as $role_id => $role_name )	{
		$role = get_role( $role_id );

		if ( empty( $role ) )	{
			continue;
		}

		foreach( $all_caps as $cap )	{
			$role->remove_cap( $cap );
		}

		if ( 'administrator' != $role_id )	{
			remove_role( $role_id );
		}

	}

	// Remove users
	$roles = array( 'client', 'inactive_client', 'dj', 'inactive_dj' );

	// Loop through roles array removing users
	foreach( $roles as $role )	{
		$args = array(
			'role'         => $role,
			'role__not_in' => 'Administrator',
			'orderby'      => 'display_name',
			'order'        => 'ASC'
		);

		$mdjm_users = get_users( $args );

		foreach( $mdjm_users as $mdjm_user )	{
			wp_delete_user( $mdjm_user->ID );
		}
	}

	remove_role( 'inactive_dj' );
	remove_role( 'client' );
	remove_role( 'inactive_client' );
}
