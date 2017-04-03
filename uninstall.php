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
		$items = get_posts( array(
			'post_type'   => $post_type,
			'post_status' => 'any',
			'numberposts' => -1,
			'fields'      => 'ids'
		) );
	
		if ( $items ) {
			foreach ( $items as $item )	{
				wp_delete_post( $item, true );
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

	// Remove users
	$roles = array( 'client', 'inactive_client', 'dj', 'inactive_dj' );

	// Loop through roles array removing users
	foreach( $roles as $role )	{
		$args = array(
			'role'    => $role,
			'orderby' => 'display_name',
			'order'   => 'ASC'
		);

		$mdjm_users = get_users( $args );

		foreach( $mdjm_users as $mdjm_user )	{
			wp_delete_user( $mdjm_user->ID );
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

	// Remove all database tables
	$wpdb->query( "DROP TABLE IF EXISTS " . $wpdb->prefix . "mdjm_avail" );

	// Remove any transients and options we've left behind
	$wpdb->query( "DELETE FROM $wpdb->options WHERE option_name LIKE '\_transient\_mdjm\_%'" );
	$wpdb->query( "DELETE FROM $wpdb->options WHERE option_name LIKE '\_transient\_timeout\_mdjm\_%'" );

}

// Remove capabilities
$role = get_role( 'administrator' );
$role->remove_cap( 'manage_mdjm' );
$role = get_role( 'dj' );

// Remove roles
remove_role( 'dj' );
remove_role( 'inactive_dj' );
remove_role( 'client' );
remove_role( 'inactive_client' );
