<?php
/**
 * Post Type Functions
 *
 * @package     MDJM
 * @subpackage  Functions
 * @copyright   Copyright (c) 2016, Mike Howard
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.3
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) )
	exit;

/**
 * Registers and sets up the MDJM Event Management custom post types
 *
 * @since	1.3
 * @return	void
 */
function mdjm_register_post_types()	{
	
	// Communication History Post Type
	$email_history_labels = apply_filters( 
		'mdjm_email_history_labels',
		array(
			'name'               => _x( 'Email History', 'post type general name', 'mobile-dj-manager' ),
			'singular_name'      => _x( 'Email History', 'post type singular name', 'mobile-dj-manager' ),
			'menu_name'          => _x( 'Email History', 'admin menu', 'mobile-dj-manager' ),
			'name_admin_bar'     => _x( 'Email History', 'add new on admin bar', 'mobile-dj-manager' ),
			'add_new'            => __( 'Add Communication', 'mobile-dj-manager' ),
			'add_new_item'       => __( 'Add New Communication', 'mobile-dj-manager' ),
			'new_item'           => __( 'New Communication', 'mobile-dj-manager' ),
			'edit_item'          => __( 'Review Email', 'mobile-dj-manager' ),
			'view_item'          => __( 'View Email', 'mobile-dj-manager' ),
			'all_items'          => __( 'All Emails', 'mobile-dj-manager' ),
			'search_items'       => __( 'Search Emails', 'mobile-dj-manager' ),
			'not_found'          => __( 'No Emails found.', 'mobile-dj-manager' ),
			'not_found_in_trash' => __( 'No Emails found in Trash.', 'mobile-dj-manager' )
		)
	);
		
	$email_history_args = array(
		'labels'              => $email_history_labels,
		'description'         => __( 'Communication used by the MDJM Event Management for WordPress plugin', 'mobile-dj-manager' ),
		'exclude_from_search'    => true,
		'show_ui'                => true,
		'show_in_menu'           => 'edit.php?post_type=mdjm_communication',
		'show_in_admin_bar'      => false,
		'rewrite'                => array( 'slug' => 'mdjm-communications' ),
		'capability_type'        => 'mdjm_comm',
		'capabilities'           => apply_filters( 'mdjm_communications_caps', array(
			'edit_post'          => 'edit_mdjm_comm',
			'read_post'          => 'read_mdjm_comm',
			'delete_post'        => 'delete_mdjm_comm',
			'edit_posts'         => 'edit_mdjm_comms',
			'edit_others_posts'  => 'edit_others_mdjm_comms',
			'publish_posts'      => 'publish_mdjm_comms',
			'read_private_posts' => 'read_private_mdjm_comms'
		) ),
		'map_meta_cap'           => true,
		'has_archive'            => true,
		'supports'               => apply_filters( 'mdjm_email_history_supports', array( 'title' ) )
	);
	register_post_type( 'mdjm_communication', apply_filters( 'mdjm_email_history_post_type_args', $email_history_args ) );
	
	// Contract Post Type
	$contract_labels = apply_filters( 
		'mdjm_contract_labels',
		array(
			'name'               => _x( 'Contract Templates', 'post type general name', 'mobile-dj-manager' ),
			'singular_name'      => _x( 'Contract Template', 'post type singular name', 'mobile-dj-manager' ),
			'menu_name'          => _x( 'Contract Templates', 'admin menu', 'mobile-dj-manager' ),
			'name_admin_bar'     => _x( 'Contract Template', 'add new on admin bar', 'mobile-dj-manager' ),
			'add_new'            => __( 'Add Contract Template', 'mobile-dj-manager' ),
			'add_new_item'       => __( 'Add New Contract Template', 'mobile-dj-manager' ),
			'new_item'           => __( 'New Contract Template', 'mobile-dj-manager' ),
			'edit_item'          => __( 'Edit Contract Template', 'mobile-dj-manager' ),
			'view_item'          => __( 'View Contract Template', 'mobile-dj-manager' ),
			'all_items'          => __( 'All Contract Templates', 'mobile-dj-manager' ),
			'search_items'       => __( 'Search Contract Templates', 'mobile-dj-manager' ),
			'not_found'          => __( 'No contract templates found.', 'mobile-dj-manager' ),
			'not_found_in_trash' => __( 'No contract templates found in Trash.', 'mobile-dj-manager' )
		)
	);
		
	$contract_args = array(
		'labels'                 => $contract_labels,
		'description'            => __( 'Contracts used by the MDJM plugin', 'mobile-dj-manager' ),
		'exclude_from_search'    => true,
		'show_ui'                => true,
		'show_in_menu'           => 'edit.php?post_type=contract',
		'rewrite'                => array( 'slug' => 'contract-templates' ),
		'capability_type'        => array( 'mdjm_template', 'mdjm_templates' ),
		'capabilities'           => apply_filters( 'mdjm_contract_caps', array(
			'edit_post'          => 'edit_mdjm_template',
			'read_post'          => 'read_mdjm_template',
			'delete_post'        => 'delete_mdjm_template',
			'edit_posts'         => 'edit_mdjm_templates',
			'edit_others_posts'  => 'edit_others_mdjm_templates',
			'publish_posts'      => 'publish_mdjm_templates',
			'read_private_posts' => 'read_private_mdjm_templates'
		) ),
		'map_meta_cap'           => true,
		'has_archive'            => true,
		'supports'               => apply_filters( 'mdjm_contract_supports', array( 'title', 'editor', 'revisions' ) )
	);
	register_post_type( 'contract', apply_filters( 'mdjm_contract_post_type_args', $contract_args ) );
	
	// Signed Contract Post Type
	$signed_contract_labels = apply_filters( 
		'mdjm_signed_contract_labels',
		array(
			'name'               => _x( 'Signed Contracts', 'post type general name', 'mobile-dj-manager' ),
			'singular_name'      => _x( 'Signed Contract', 'post type singular name', 'mobile-dj-manager' ),
			'menu_name'          => _x( 'Signed Contracts', 'admin menu', 'mobile-dj-manager' ),
			'name_admin_bar'     => _x( 'Signed Contract', 'add new on admin bar', 'mobile-dj-manager' ),
			'add_new'            => __( 'Add Signed Contract', 'mobile-dj-manager' ),
			'add_new_item'       => __( 'Add New Signed Contract', 'mobile-dj-manager' ),
			'new_item'           => __( 'New Signed Contract', 'mobile-dj-manager' ),
			'edit_item'          => __( 'Edit Signed Contract', 'mobile-dj-manager' ),
			'view_item'          => __( 'View Signed Contract', 'mobile-dj-manager' ),
			'all_items'          => __( 'All Signed Contracts', 'mobile-dj-manager' ),
			'search_items'       => __( 'Search Signed Contracts', 'mobile-dj-manager' ),
			'not_found'          => __( 'No signed contracts found.', 'mobile-dj-manager' ),
			'not_found_in_trash' => __( 'No signed contracts found in Trash.', 'mobile-dj-manager' )
		)
	);
		
	$signed_contract_args = array(
		'labels'             => $signed_contract_labels,
		'description'        => __( 'Signed Contracts used by the MDJM plugin', 'mobile-dj-manager' ),
		'rewrite'            => array( 'slug' => 'mdjm-signed-contract' ),
		'capability_type'    => array( 'mdjm_signed_contract', 'mdjm_signed_contracts' ),
		'map_meta_cap'       => true,
		'has_archive'        => true,
		'supports'           => array( '' )
	);
	register_post_type( 'mdjm-signed-contract', apply_filters( 'mdjm_signed_contract_post_type_args', $signed_contract_args ) );
	
	// Custom Field Post Type
	$custom_field_labels = apply_filters( 
		'mdjm_custom_field_contract_labels',
		array(
			'name'               => _x( 'Custom Event Fields', 'post type general name', 'mobile-dj-manager' ),
			'singular_name'      => _x( 'Custom Event Field', 'post type singular name', 'mobile-dj-manager' ),
			'menu_name'          => _x( 'Custom Event Fields', 'admin menu', 'mobile-dj-manager' ),
			'add_new'            => _x( 'Add Custom Event Field', 'add new on admin bar', 'mobile-dj-manager' ),
			'add_new_item'       => __( 'Add New Custom Event Field' ),
			'edit'               => __( 'Edit Custom Event Field' ),
			'edit_item'          => __( 'Edit Custom Event Field' ),
			'new_item'           => __( 'New Hosted Plugin' ),
			'view'               => __( 'View Custom Event Field' ),
			'view_item'          => __( 'View Custom Event Field' ),
			'search_items'       => __( 'Search Custom Event Field' ),
			'not_found'          => __( 'No Custom Event Fields found' ),
			'not_found_in_trash' => __( 'No Custom Event Fields found in trash' )
		)
	);
		
	$custom_field_args = array(
		'labels'      => $custom_field_labels,
		'description' => __( 'This is where you can add Custom Event Fields for use in the event screen.', 'mobile-dj-manager' ),
		'rewrite'     => array( 'slug' => 'mdjm-custom-fields' ),
		'supports'    => array( 'title' )
	);
	register_post_type( 'mdjm-custom-field', apply_filters( 'mdjm_custom_field_post_type_args', $custom_field_args ) );
	
	// Email Template Post Type
	$email_template_labels = apply_filters( 
		'mdjm_email_template_labels',
		array(
			'name'               => _x( 'Email Templates', 'post type general name', 'mobile-dj-manager' ),
			'singular_name'      => _x( 'Email Template', 'post type singular name', 'mobile-dj-manager' ),
			'menu_name'          => _x( 'Email Templates', 'admin menu', 'mobile-dj-manager' ),
			'name_admin_bar'     => _x( 'Email Template', 'add new on admin bar', 'mobile-dj-manager' ),
			'add_new'            => __( 'Add Template', 'mobile-dj-manager' ),
			'add_new_item'       => __( 'Add New Template', 'mobile-dj-manager' ),
			'new_item'           => __( 'New Template', 'mobile-dj-manager' ),
			'edit_item'          => __( 'Edit Template', 'mobile-dj-manager' ),
			'view_item'          => __( 'View Template', 'mobile-dj-manager' ),
			'all_items'          => __( 'All Templates', 'mobile-dj-manager' ),
			'search_items'       => __( 'Search Templates', 'mobile-dj-manager' ),
			'not_found'          => __( 'No templates found.', 'mobile-dj-manager' ),
			'not_found_in_trash' => __( 'No templates found in Trash.', 'mobile-dj-manager' )
		)
	);
		
	$email_template_args = array(
		'labels'                  => $email_template_labels,
		'description'             => __( 'Email Templates for the MDJM Event Management plugin', 'mobile-dj-manager' ),
		'show_ui'                 => true,
		'show_in_menu'            => 'edit.php?post_type=email_template',
		'show_in_admin_bar'       => true,
		'rewrite'                 => array( 'slug' => 'email-template' ),
		'capability_type'         => 'mdjm_template',
		'capabilities'            => apply_filters( 'mdjm_email_template_caps', array(
			'publish_posts'       => 'publish_mdjm_templates',
			'edit_posts'          => 'edit_mdjm_templates',
			'edit_others_posts'   => 'edit_others_mdjm_templates',
			'delete_posts'        => 'delete_mdjm_templates',
			'delete_others_posts' => 'delete_others_mdjm_templates',
			'read_private_posts'  => 'read_private_mdjm_templates',
			'edit_post'           => 'edit_mdjm_template',
			'delete_post'         => 'delete_mdjm_template',
			'read_post'           => 'read_mdjm_template',
		) ),
		'map_meta_cap'            => true,
		'has_archive'             => true,
		'supports'                => apply_filters( 'mdjm_email_template_supports', array( 'title', 'editor', 'revisions' ) )
	);
	register_post_type( 'email_template', apply_filters( 'mdjm_email_template_post_type_args', $email_template_args ) );
	
	// Packages Post Type
	$package_labels = apply_filters( 
		'mdjm_package_labels',
		array(
			'name'                  => _x( 'Event Packages', 'post type general name', 'mobile-dj-manager' ),
			'singular_name'         => _x( 'Event Package', 'post type singular name', 'mobile-dj-manager' ),
			'menu_name'             => _x( 'Event Packages', 'admin menu', 'mobile-dj-manager' ),
			'name_admin_bar'        => _x( 'Event Package', 'add new on admin bar', 'mobile-dj-manager' ),
			'add_new'               => __( 'Add Package', 'mobile-dj-manager' ),
			'add_new_item'          => __( 'Add New Package', 'mobile-dj-manager' ),
			'new_item'              => __( 'New Package', 'mobile-dj-manager' ),
			'edit_item'             => __( 'Edit Package', 'mobile-dj-manager' ),
			'view_item'             => __( 'View Package', 'mobile-dj-manager' ),
			'all_items'             => __( 'All Packages', 'mobile-dj-manager' ),
			'search_items'          => __( 'Search Packages', 'mobile-dj-manager' ),
			'not_found'             => __( 'No packages found.', 'mobile-dj-manager' ),
			'not_found_in_trash'	=> __( 'No packages found in Trash.', 'mobile-dj-manager' )
		)
	);
		
	$package_args = array(
		'labels'                  => $package_labels,
		'description'             => __( 'Equipment Packages for the MDJM Event Management plugin', 'mobile-dj-manager' ),
		'show_ui'                 => true,
		'show_in_menu'            => 'edit.php?post_type=mdjm-package',
		'capability_type'		  => 'mdjm_template',
		'capabilities'            => apply_filters( 'mdjm_package_caps', array(
			'publish_posts'       => 'publish_mdjm_packages',
			'edit_posts'          => 'edit_mdjm_packages',
			'edit_others_posts'   => 'edit_others_mdjm_packages',
			'delete_posts'        => 'delete_mdjm_packages',
			'delete_others_posts' => 'delete_others_mdjm_packages',
			'read_private_posts'  => 'read_private_mdjm_packages',
			'edit_post'           => 'edit_mdjm_package',
			'delete_post'         => 'delete_mdjm_package',
			'read_post'           => 'read_mdjm_package',
		) ),
		'map_meta_cap'            => true,
		'has_archive'             => true,
		'supports'                => apply_filters( 'mdjm_package_supports', array( 'title' ) )
	);
	register_post_type( 'mdjm-package', apply_filters( 'mdjm_package_post_type_args', $package_args ) );
	
	// Event Post Type
	$event_labels = apply_filters( 
		'mdjm_event_labels',
		array(
			'name'               => _x( '%2$s', 'post type general name', 'mobile-dj-manager' ),
			'singular_name'      => _x( '%1$s', 'post type singular name', 'mobile-dj-manager' ),
			'menu_name'          => _x( 'MDJM %2$s', 'admin menu', 'mobile-dj-manager' ),
			'name_admin_bar'     => _x( '%1$s', 'add new on admin bar', 'mobile-dj-manager' ),
			'add_new'            => __( 'Create %1$s', 'mobile-dj-manager' ),
			'add_new_item'       => __( 'Create New %1$s', 'mobile-dj-manager' ),
			'new_item'           => __( 'New %1$s', 'mobile-dj-manager' ),
			'edit_item'          => __( 'Edit %1$s', 'mobile-dj-manager' ),
			'view_item'          => __( 'View %1$s', 'mobile-dj-manager' ),
			'all_items'          => __( 'All %2$s', 'mobile-dj-manager' ),
			'search_items'       => __( 'Search %2$s', 'mobile-dj-manager' ),
			'not_found'          => __( 'No %3$s found.', 'mobile-dj-manager' ),
			'not_found_in_trash' => __( 'No %3$s found in Trash.', 'mobile-dj-manager' )
		)
	);
	
	foreach ( $event_labels as $key => $value ) {
	   $event_labels[ $key ] = sprintf( $value, mdjm_get_label_singular(), mdjm_get_label_plural(), mdjm_get_label_plural( true ) );
	}
		
	$event_args = array(
		'labels'                  => $event_labels,
		'description'             => __( 'MDJM Events', 'mobile-dj-manager' ),
		'show_ui'                 => true,
		'show_in_menu'            => true,
		'menu_position'           => defined( 'MDJM_MENU_POS' ) ? MDJM_MENU_POS : 58.4,
		'show_in_admin_bar'       => true,
		'capability_type'         => 'mdjm_event',
		'capabilities'            => apply_filters( 'mdjm_event_caps', array(
			'publish_posts'       => 'publish_mdjm_events',
			'edit_posts'          => 'edit_mdjm_events',
			'edit_others_posts'   => 'edit_others_mdjm_events',
			'delete_posts'        => 'delete_mdjm_events',
			'delete_others_posts' => 'delete_others_mdjm_events',
			'read_private_posts'  => 'read_private_mdjm_events',
			'edit_post'           => 'edit_mdjm_event',
			'delete_post'         => 'delete_mdjm_event',
			'read_post'           => 'read_mdjm_event',
		) ),
		'map_meta_cap'            => true,
		'has_archive'             => true,
		'supports'                => apply_filters( 'mdjm_event_supports', array( 'title' ) ),
		'menu_icon'               => plugins_url( 'mobile-dj-manager/assets/images/mdjm-menu-16x16.jpg' ),
		'taxonomies'              => array( 'mdjm-event' )
	);
	register_post_type( 'mdjm-event', apply_filters( 'mdjm_event_post_type_args', $event_args ) );
	
	// Playlist Post Type
	$playlist_labels = apply_filters( 
		'mdjm_playlist_labels',
		array(
			'name'               => _x( 'Playlist Entries', 'post type general name', 'mobile-dj-manager' ),
			'singular_name'      => _x( 'Playlist Entry', 'post type singular name', 'mobile-dj-manager' ),
			'menu_name'          => _x( 'Playlist Entries', 'admin menu', 'mobile-dj-manager' ),
			'name_admin_bar'     => _x( 'Playlist Entry', 'add new on admin bar', 'mobile-dj-manager' ),
			'add_new'            => __( 'Add Playlist Entry', 'mobile-dj-manager' ),
			'add_new_item'       => __( 'Add New Playlist Entry', 'mobile-dj-manager' ),
			'new_item'           => __( 'New Entry', 'mobile-dj-manager' ),
			'edit_item'          => __( 'Edit Entry', 'mobile-dj-manager' ),
			'view_item'          => __( 'View Entry', 'mobile-dj-manager' ),
			'all_items'          => __( 'All Entries', 'mobile-dj-manager' ),
			'search_items'       => __( 'Search Entries', 'mobile-dj-manager' ),
			'not_found'          => __( 'No entries found.', 'mobile-dj-manager' ),
			'not_found_in_trash' => __( 'No entries found in Trash.', 'mobile-dj-manager' )
		)
	);
		
	$playlist_args = array(
		'labels'                 => $playlist_labels,
		'description'			 => __( 'MDJM Event Management Playlist Entries', 'mobile-dj-manager' ),
		'show_ui'				 => true,
		'show_in_menu'	   	     => false,
		'capability_type'	     => 'mdjm_playlist',
		'capabilities'           => apply_filters( 'mdjm_playlist_caps', array(
			'edit_post'          => 'edit_mdjm_playlist',
			'read_post'          => 'read_mdjm_playlist',
			'delete_post'        => 'delete_mdjm_playlist',
			'edit_posts'         => 'edit_mdjm_playlists',
			'edit_others_posts'  => 'edit_others_mdjm_playlists',
			'publish_posts'      => 'publish_mdjm_playlists',
			'read_private_posts' => 'read_private_mdjm_playlists'
		) ),
		'map_meta_cap'           => true,
		'supports'           	 => apply_filters( 'mdjm_playlist_supports', array( 'title' ) ),
		'taxonomies'			 => array( 'mdjm-playlist' )
	);
	register_post_type( 'mdjm-playlist', apply_filters( 'mdjm_playlist_post_type_args', $playlist_args ) );
	
	// Quote Post Type
	$quote_labels = apply_filters( 
		'mdjm_quote_labels',
		array(
			'name'               => _x( 'Quotes', 'post type general name', 'mobile-dj-manager' ),
			'singular_name'      => _x( 'Quote', 'post type singular name', 'mobile-dj-manager' ),
			'menu_name'          => _x( 'Quotes', 'admin menu', 'mobile-dj-manager' ),
			'name_admin_bar'     => _x( 'Quote', 'add new on admin bar', 'mobile-dj-manager' ),
			'add_new'            => __( 'Create Quote', 'mobile-dj-manager' ),
			'add_new_item'       => __( 'Create New Quote', 'mobile-dj-manager' ),
			'new_item'           => __( 'New Quote', 'mobile-dj-manager' ),
			'edit_item'          => __( 'Edit Quote', 'mobile-dj-manager' ),
			'view_item'          => __( 'View Quote', 'mobile-dj-manager' ),
			'all_items'          => __( 'All Quotes', 'mobile-dj-manager' ),
			'search_items'       => __( 'Search Quotes', 'mobile-dj-manager' ),
			'not_found'          => __( 'No quotes found.', 'mobile-dj-manager' ),
			'not_found_in_trash' => __( 'No quotes found in Trash.', 'mobile-dj-manager' )
		)
	);
		
	$quote_args = array(
		'labels'                 => $quote_labels,
		'description'			 => __( 'MDJM Event Management Quotes', 'mobile-dj-manager' ),
		'show_ui'				 => true,
		'show_in_menu'	   	     => 'edit.php?post_type=mdjm-quotes',
		'show_in_admin_bar'      => false,
		'rewrite'            	 => array( 'slug' => 'mdjm-quotes' ),
		'capability_type'	     => 'mdjm_quote',
		'capabilities'           => apply_filters( 'mdjm_quote_caps', array(
			'edit_post'          => 'edit_mdjm_quote',
			'read_post'          => 'read_mdjm_quote',
			'delete_post'        => 'delete_mdjm_quote',
			'edit_posts'         => 'edit_mdjm_quotes',
			'edit_others_posts'  => 'edit_others_mdjm_quotes',
			'publish_posts'      => 'publish_mdjm_quotes',
			'read_private_posts' => 'read_private_mdjm_quotes'
		) ),
		'map_meta_cap'           => true,
		'has_archive'            => true,
		'supports'               => apply_filters( 'mdjm_quote_supports', array( 'title' ) )
	);
	register_post_type( 'mdjm-quotes', apply_filters( 'mdjm_quotes_post_type_args', $quote_args ) );
	
	// Transaction Post Type
	$txn_labels = apply_filters( 
		'mdjm_txn_labels',
		array(
			'name'               => _x( 'Transactions', 'post type general name', 'mobile-dj-manager' ),
			'singular_name'      => _x( 'Transaction', 'post type singular name', 'mobile-dj-manager' ),
			'menu_name'          => _x( 'Transactions', 'admin menu', 'mobile-dj-manager' ),
			'name_admin_bar'     => _x( 'Transaction', 'add new on admin bar', 'mobile-dj-manager' ),
			'add_new'            => __( 'Add Transaction', 'mobile-dj-manager' ),
			'add_new_item'       => __( 'Add New Transaction', 'mobile-dj-manager' ),
			'new_item'           => __( 'New Transaction', 'mobile-dj-manager' ),
			'edit_item'          => __( 'Edit Transaction', 'mobile-dj-manager' ),
			'view_item'          => __( 'View Transaction', 'mobile-dj-manager' ),
			'all_items'          => __( 'All Transactions', 'mobile-dj-manager' ),
			'search_items'       => __( 'Search Transactions', 'mobile-dj-manager' ),
			'not_found'          => __( 'No Transactions found.', 'mobile-dj-manager' ),
			'not_found_in_trash' => __( 'No Transactions found in Trash.' )
		)
	);
		
	$txn_args = array(
		'labels'                 => $txn_labels,
		'description'			=> __( 'Transactions for the MDJM Event Management plugin', 'mobile-dj-manager' ),
		'show_ui'				=> true,
		'show_in_menu'		   => 'edit.php?post_type=mdjm-transaction',
		'show_in_admin_bar'	  => true,
		'rewrite' 			  	=> array( 'slug' => 'mdjm-transaction'),
		'capability_type'	    => 'mdjm_txn',
		'capabilities'           => apply_filters( 'mdjm_transaction_caps', array(
			'edit_post'             => 'edit_mdjm_txn',
			'read_post'             => 'read_mdjm_txn',
			'delete_post'           => 'delete_mdjm_txn',
			'edit_posts'            => 'edit_mdjm_txns',
			'edit_others_posts'     => 'edit_others_mdjm_txns',
			'publish_posts'         => 'publish_mdjm_txns',
			'read_private_posts'    => 'read_private_mdjm_txns'
		) ),
		'map_meta_cap'		   => true,
		'has_archive'        	=> true,
		'supports'			   => apply_filters( 'mdjm_transaction_supports', array( 'title' ) ),
		'taxonomies'			 => array( 'mdjm-transaction' )
	);
	register_post_type( 'mdjm-transaction', apply_filters( 'mdjm_transaction_post_type_args', $txn_args ) );
	
	// Venue Post Type
	$venue_labels = apply_filters( 
		'mdjm_txn_labels',
		array(
			'name'               => _x( 'Venues', 'post type general name', 'mobile-dj-manager' ),
			'singular_name'      => _x( 'Venue', 'post type singular name', 'mobile-dj-manager' ),
			'menu_name'          => _x( 'Venues', 'admin menu', 'mobile-dj-manager' ),
			'name_admin_bar'     => _x( 'Venue', 'add new on admin bar', 'mobile-dj-manager' ),
			'add_new'            => __( 'Add Venue', 'mobile-dj-manager' ),
			'add_new_item'       => __( 'Add New Venue', 'mobile-dj-manager' ),
			'new_item'           => __( 'New Venue', 'mobile-dj-manager' ),
			'edit_item'          => __( 'Edit Venue', 'mobile-dj-manager' ),
			'view_item'          => __( 'View Venue', 'mobile-dj-manager' ),
			'all_items'          => __( 'All Venues', 'mobile-dj-manager' ),
			'search_items'       => __( 'Search Venues', 'mobile-dj-manager' ),
			'not_found'          => __( 'No Venues found.', 'mobile-dj-manager' ),
			'not_found_in_trash' => __( 'No Venues found in Trash.', 'mobile-dj-manager' )
		)
	);
		
	$venue_args = array(
		'labels'                 => $venue_labels,
		'description'			=> __( 'Venues stored for the MDJM Event Management plugin', 'mobile-dj-manager' ),
		'show_ui'				=> true,
		'show_in_menu'		   => 'edit.php?post_type=' . MDJM_VENUE_POSTS,
		'show_in_admin_bar'	  => true,
		'rewrite' 			  	=> array( 'slug' => 'mdjm-venue'),
		'capability_type'	    => 'mdjm_venue',
		'capabilities'           => apply_filters( 'mdjm_venue_caps', array(
			'edit_post'             => 'edit_mdjm_venue',
			'read_post'             => 'read_mdjm_venue',
			'delete_post'           => 'delete_mdjm_venue',
			'edit_posts'            => 'edit_mdjm_venues',
			'edit_others_posts'     => 'edit_others_mdjm_venues',
			'publish_posts'         => 'publish_mdjm_venues',
			'read_private_posts'    => 'read_private_mdjm_venues'
		) ),
		'map_meta_cap'		   => true,
		'has_archive'        	=> true,
		'supports'			   => apply_filters( 'mdjm_venue_supports', array( 'title' ) ),
		'taxonomies'			 => array( 'mdjm-venue' )
	);
	register_post_type( 'mdjm-venue', apply_filters( 'mdjm_venue_post_type_args', $venue_args ) );
} // mdjm_register_post_types
add_action( 'init', 'mdjm_register_post_types', 1 );

/**
 * Get Default Labels
 *
 * @since	1.3
 * @return	arr		$defaults	Default labels
 */
function mdjm_get_default_labels() {
	$defaults = array(
	   'singular' => __( 'Event', 'mobile-dj-manager' ),
	   'plural'   => __( 'Events','mobile-dj-manager' )
	);
	return apply_filters( 'mdjm_default_events_name', $defaults );
} // mdjm_get_default_labels

/**
 * Get Post Status Label
 *
 * @since	1.3
 * @param	str		$status		The post status
 * @return	arr		$defaults	Default labels
 */
function mdjm_get_post_status_label( $status ) {
	$object = get_post_status_object( $status );
	
	return apply_filters( 'mdjm_post_status_label_{$status}', $object->label );
} // mdjm_get_post_status_label

/**
 * Get Singular Label
 *
 * @since	1.3
 * @param	bool	$lowercase
 * @return	str		$defaults['singular']	Singular label
 */
function mdjm_get_label_singular( $lowercase = false ) {
	$defaults = mdjm_get_default_labels();
	return ( $lowercase ) ? strtolower( $defaults['singular'] ) : $defaults['singular'];
} // mdjm_get_label_singular

/**
 * Get Plural Label
 *
 * @since	1.3
 * @param	bool	$lowercase
 * @return	str		$defaults['plural']	Plural label
 */
function mdjm_get_label_plural( $lowercase = false ) {
	$defaults = mdjm_get_default_labels();
	return ( $lowercase ) ? strtolower( $defaults['plural'] ) : $defaults['plural'];
} // mdjm_get_label_plural

/**
 * Get the singular and plural labels for custom taxonomies.
 *
 * @since	0.1
 * @param	str		$taxonomy	The Taxonomy to get labels for
 * @return	arr		Associative array of labels (name = plural)
 */
function mdjm_get_taxonomy_labels( $taxonomy = 'event-types' ) {

	$allowed_taxonomies = apply_filters(
		'mdjm_allowed_taxonomies',
		array( 'event-types', 'mdjm-playlist', 'enquiry-source', 'mdjm-transactions', 'venue-details' )
	);

	if ( ! in_array( $taxonomy, $allowed_taxonomies ) ) {
		return false;
	}

	$labels   = array();
	$taxonomy = get_taxonomy( $taxonomy );

	if ( false !== $taxonomy ) {
		$singular = $taxonomy->labels->singular_name;
		$name     = $taxonomy->labels->name;

		$labels = array(
			'name'          => $name,
			'singular_name' => $singular,
		);
	}

	return apply_filters( 'mdjm_get_taxonomy_labels', $labels, $taxonomy );

} // mdjm_get_taxonomy_labels

/**
 * Registers Custom Post Statuses which are used by the Communication, 
 * Event, Transaction and Quote custom post types.
 *
 * @since	1.3
 * @return	void
 */
function mdjm_register_post_statuses()	{
	/** Communication Post Statuses */
	register_post_status( 
		'ready to send',
		apply_filters( 'mdjm_comm_ready_to_send_status',
			array(
				'label'                     => __( 'Ready to Send', 'mobile-dj-manager' ),
				'public'                    => true,
				'exclude_from_search'       => false,
				'show_in_admin_all_list'    => true,
				'show_in_admin_status_list' => true,
				'label_count'               => _n_noop( 'Ready to Send <span class="count">(%s)</span>', 'Ready to Send <span class="count">(%s)</span>', 'mobile-dj-manager' )
			)
		)
	);
	
	register_post_status( 
		'sent',
		apply_filters( 'mdjm_comm_sent_status',
			array(
				'label'                     => __( 'Sent', 'mobile-dj-manager' ),
				'public'                    => true,
				'exclude_from_search'       => false,
				'show_in_admin_all_list'    => true,
				'show_in_admin_status_list' => true,
				'label_count'               => _n_noop( 'Sent <span class="count">(%s)</span>', 'Sent <span class="count">(%s)</span>', 'mobile-dj-manager' )
			)
		)
	);
	
	register_post_status(
		'opened',
		apply_filters( 'mdjm_comm_opened_status',
			array(
				'label'                     => __( 'Opened', 'mobile-dj-manager' ),
				'public'                    => true,
				'exclude_from_search'       => false,
				'show_in_admin_all_list'    => true,
				'show_in_admin_status_list' => true,
				'label_count'               => _n_noop( 'Opened <span class="count">(%s)</span>', 'Opened <span class="count">(%s)</span>', 'mobile-dj-manager' )
			)
		)
	);
	
	register_post_status(
		'failed',
		apply_filters( 'mdjm_comm_failed_status',
			array(
				'label'                     => __( 'Failed', 'mobile-dj-manager' ),
				'public'                    => true,
				'exclude_from_search'       => false,
				'show_in_admin_all_list'    => true,
				'show_in_admin_status_list' => true,
				'label_count'               => _n_noop( 'Failed <span class="count">(%s)</span>', 'Failed <span class="count">(%s)</span>', 'mobile-dj-manager' )
			)
		)
	);
	
	/** Event Post Statuses */
	register_post_status( 
		'mdjm-unattended',
		apply_filters( 'mdjm_event_unattended_status',
			array(
				'label'                     => __( 'Unattended Enquiry', 'mobile-dj-manager' ),
				'plural'					=> __( 'Unattended Enquiries', 'mobile-dj-manager' ),
				'public'                    => true,
				'exclude_from_search'       => false,
				'show_in_admin_all_list'    => true,
				'show_in_admin_status_list' => true,
				'label_count'               => _n_noop( 'Unattended Enquiry <span class="count">(%s)</span>', 'Unattended Enquiries <span class="count">(%s)</span>', 'mobile-dj-manager' ),
				'mdjm-event'                => true
			)
		)
	);

	register_post_status(
		'mdjm-enquiry',
		apply_filters( 'mdjm_event_enquiry_status',
			array(
				'label'                     => __( 'Enquiry', 'mobile-dj-manager' ),
				'plural'					=> __( 'Enquiries', 'mobile-dj-manager' ),
				'public'                    => true,
				'exclude_from_search'       => false,
				'show_in_admin_all_list'    => true,
				'show_in_admin_status_list' => true,
				'label_count'               => _n_noop( 'Enquiry <span class="count">(%s)</span>', 'Enquiries <span class="count">(%s)</span>', 'mobile-dj-manager' ),
				'mdjm-event'                => true
			)
		)
	);
					
	register_post_status(
		'mdjm-approved',
		apply_filters( 'mdjm_event_approved_status',
			array(
				'label'                     => __( 'Confirmed', 'mobile-dj-manager' ),
				'plural'					=> __( 'Confirmed', 'mobile-dj-manager' ),
				'public'                    => true,
				'exclude_from_search'       => false,
				'show_in_admin_all_list'    => true,
				'show_in_admin_status_list' => true,
				'label_count'               => _n_noop( 'Confirmed <span class="count">(%s)</span>', 'Confirmed <span class="count">(%s)</span>', 'mobile-dj-manager' ),
				'mdjm-event'                => true
			)
		)
	);
		
	register_post_status(
		'mdjm-contract',
		apply_filters( 'mdjm_event_contract_status',
			array(
				'label'                     => __( 'Awaiting Contract', 'mobile-dj-manager' ),
				'plural'					=> __( 'Awaiting Contracts', 'mobile-dj-manager' ),
				'public'                    => true,
				'exclude_from_search'       => false,
				'show_in_admin_all_list'    => true,
				'show_in_admin_status_list' => true,
				'label_count'               => _n_noop( 'Awaiting Contract <span class="count">(%s)</span>', 'Awaiting Contracts <span class="count">(%s)</span>', 'mobile-dj-manager' ),
				'mdjm-event'                => true
			)
		)
	);

	register_post_status(
		'mdjm-completed',
		apply_filters( 'mdjm_event_completed_status',
			array(
				'label'                     => __( 'Completed', 'mobile-dj-manager' ),
				'plural'					=> __( 'Completed', 'mobile-dj-manager' ),
				'public'                    => true,
				'exclude_from_search'       => false,
				'show_in_admin_all_list'    => true,
				'show_in_admin_status_list' => true,
				'label_count'               => _n_noop( 'Completed <span class="count">(%s)</span>', 'Completed <span class="count">(%s)</span>', 'mobile-dj-manager' ),
				'mdjm-event'                => true
			)
		)
	);
				
	register_post_status(
		'mdjm-cancelled',
		apply_filters( 'mdjm_event_cancelled_status',
			array(
				'label'                     => __( 'Cancelled', 'mobile-dj-manager' ),
				'plural'					=> __( 'Cancelled', 'mobile-dj-manager' ),
				'public'                    => true,
				'exclude_from_search'       => false,
				'show_in_admin_all_list'    => true,
				'show_in_admin_status_list' => true,
				'label_count'               => _n_noop( 'Cancelled <span class="count">(%s)</span>', 'Cancelled <span class="count">(%s)</span>', 'mobile-dj-manager' ),
				'mdjm-event'                => true
			)
		)
	);
		
	register_post_status(
		'mdjm-rejected',
		apply_filters( 'mdjm_event_rejected_status',
			array(
				'label'                     => __( 'Rejected Enquiry', 'mobile-dj-manager' ),
				'plural'					=> __( 'Rejected Enquiries', 'mobile-dj-manager' ),
				'public'                    => true,
				'exclude_from_search'       => false,
				'show_in_admin_all_list'    => true,
				'show_in_admin_status_list' => true,
				'label_count'               => _n_noop( 'Rejected Enquiry <span class="count">(%s)</span>', 'Rejected Enquiries <span class="count">(%s)</span>', 'mobile-dj-manager' ),
				'mdjm-event'                => true
			)
		)
	);
		
	register_post_status(
		'mdjm-failed',
		apply_filters( 'mdjm_event_failed_status',
			array(
				'label'                     => __( 'Failed Enquiry', 'mobile-dj-manager' ),
				'plural'					=> __( 'Failed Enquiries', 'mobile-dj-manager' ),
				'public'                    => true,
				'exclude_from_search'       => false,
				'show_in_admin_all_list'    => true,
				'show_in_admin_status_list' => true,
				'label_count'               => _n_noop( 'Failed Enquiry <span class="count">(%s)</span>', 'Failed Enquiries <span class="count">(%s)</span>', 'mobile-dj-manager' ),
				'mdjm-event'                => true
			)
		)
	);
		
	/** Online Quote Post Statuses */		
	register_post_status(
		'mdjm-quote-generated',
		apply_filters( 'mdjm_quote_generated_status',
			array(
				'label'                     => __( 'Generated', 'mobile-dj-manager' ),
				'public'                    => true,
				'exclude_from_search'       => false,
				'show_in_admin_all_list'    => true,
				'show_in_admin_status_list' => true,
				'label_count'               => _n_noop( 'Generated Quote <span class="count">(%s)</span>', 'Generated Quotes <span class="count">(%s)</span>', 'mobile-dj-manager' )
			)
		)
	);
		
	register_post_status(
		'mdjm-quote-viewed',
		apply_filters( 'mdjm_quote_viewed_status',
			array(
				'label'                     => __( 'Viewed', 'mobile-dj-manager' ),
				'public'                    => true,
				'exclude_from_search'       => false,
				'show_in_admin_all_list'    => true,
				'show_in_admin_status_list' => true,
				'label_count'               => _n_noop( 'Viewed Quote <span class="count">(%s)</span>', 'Viewed Quotes <span class="count">(%s)</span>', 'mobile-dj-manager' )
			)
		)
	);
		
	/** Transaction Post Statuses */		
	register_post_status(
		'mdjm-income',
		apply_filters( 'mdjm_transaction_income_status',
			array(
				'label'                     => __( 'Income', 'mobile-dj-manager' ),
				'public'                    => true,
				'exclude_from_search'       => false,
				'show_in_admin_all_list'    => true,
				'show_in_admin_status_list' => true,
				'label_count'               => _n_noop( 'Received Payment <span class="count">(%s)</span>', 'Received Payments <span class="count">(%s)</span>', 'mobile-dj-manager' ),
				'mdjm'                      => true
			)
		)
	);
		
	register_post_status(
		'mdjm-expenditure',
		apply_filters( 'mdjm_transaction_expenditure_status',
			array(
				'label'                     => __( 'Expenditure', 'mobile-dj-manager' ),
				'public'                    => true,
				'exclude_from_search'       => false,
				'show_in_admin_all_list'    => true,
				'show_in_admin_status_list' => true,
				'label_count'               => _n_noop( 'Outgoing Payment <span class="count">(%s)</span>', 'Outgoing Payments <span class="count">(%s)</span>', 'mobile-dj-manager' ),
				'mdjm'                      => true
			)
		)
	);
} // mdjm_register_post_statuses
add_action( 'init', 'mdjm_register_post_statuses', 2 );

/**
 * Retrieve all MDJM Event custom post statuses.
 *
 * @since	1.0
 * @uses	get_post_stati()
 * @param	str		$output		The type of output to return, either 'names' or 'objects'. Default 'names'.
 * @return	arr|obj		
 */
function mdjm_get_post_statuses( $output = 'names' )	{
	$args['mdjm-event'] = true;
		
	$mdjm_post_statuses = get_post_stati( $args, $output );
	
	return $mdjm_post_statuses;
} // mdjm_get_post_statuses

/**
 * Registers the custom taxonomies for the Event, Playlist.
 * Transaction and Venue custom post types.
 *
 * @since	1.3
 * @return	void
 */
function mdjm_register_taxonomies()	{
	/** Event Types */
	$event_type_labels = array(
		'name'              		   => _x( 'Event Type', 'taxonomy general name', 'mobile-dj-manager' ),
		'singular_name'     		  => _x( 'Event Type', 'taxonomy singular name', 'mobile-dj-manager' ),
		'search_items'      		   => __( 'Search Event Types', 'mobile-dj-manager' ),
		'all_items'         		  => __( 'All Event Types', 'mobile-dj-manager' ),
		'edit_item'        		  => __( 'Edit Event Type', 'mobile-dj-manager' ),
		'update_item'       			=> __( 'Update Event Type', 'mobile-dj-manager' ),
		'add_new_item'      		   => __( 'Add New Event Type', 'mobile-dj-manager' ),
		'new_item_name'     		  => __( 'New Event Type', 'mobile-dj-manager' ),
		'menu_name'         		  => __( 'Event Types', 'mobile-dj-manager' ),
		'separate_items_with_commas' => NULL,
		'choose_from_most_used'	  => __( 'Choose from the most popular Event Types', 'mobile-dj-manager' ),
		'not_found'				  => __( 'No event types found', 'mobile-dj-manager' )
	);

	$event_type_args = apply_filters( 'mdjm_event_type_args', array(
			'hierarchical'          => true,
			'labels'                => apply_filters( 'mdjm_event_type_labels', $event_type_labels ),
			'query_var'             => true,
			'rewrite'               => array( 'slug' => 'event-types' ),
			'capabilities'          => apply_filters( 'mdjm_event_type_caps', array(
				'manage_terms'          => 'manage_mdjm',
				'edit_terms'            => 'manage_mdjm',
				'delete_terms'          => 'manage_mdjm',
				'assign_terms'          => 'mdjm_employee'
			) ),
			'update_count_callback' => '_update_generic_term_count'
		)
	);
	register_taxonomy( 'event-types', array( 'mdjm-event' ), $event_type_args );
	register_taxonomy_for_object_type( 'event-types', 'mdjm-event' );
	
	/** Enquiry Sources */
	$enquiry_source_labels = array(
		'name'                       => _x( 'Enquiry Sources', 'taxonomy general name', 'mobile-dj-manager' ),
		'singular_name'              => _x( 'Enquiry Source', 'taxonomy singular name', 'mobile-dj-manager' ),
		'search_items'               => __( 'Search Enquiry Sources', 'mobile-dj-manager' ),
		'all_items'                  => __( 'All Enquiry Sources', 'mobile-dj-manager' ),
		'edit_item'                  => __( 'Edit Enquiry Source', 'mobile-dj-manager' ),
		'update_item'                => __( 'Update Enquiry Source', 'mobile-dj-manager' ),
		'add_new_item'               => __( 'Add New Enquiry Source', 'mobile-dj-manager' ),
		'new_item_name'              => __( 'New Enquiry Source', 'mobile-dj-manager' ),
		'menu_name'                  => __( 'Enquiry Sources', 'mobile-dj-manager' ),
		'popular_items'              => __( 'Most Enquiries from', 'mobile-dj-manager' ),
		'separate_items_with_commas' => NULL,
		'choose_from_most_used'      => __( 'Choose from the most popular Enquiry Sources', 'mobile-dj-manager' ),
		'not_found'                  => __( 'No enquiry sources found', 'mobile-dj-manager' )
	);

	$enquiry_source_args = apply_filters( 'mdjm_enquiry_source_args', array(
			'hierarchical'          => false,
			'labels'                => apply_filters( 'mdjm_enquiry_source_labels', $enquiry_source_labels ),
			'description'           => sprintf( __( 'Track how clients found %s', 'mobile-dj-manager' ), mdjm_get_option( 'company_name', get_bloginfo( 'name' ) ) ),
			'public'                => false,
			'show_ui'               => true,
			'query_var'             => true,
			'rewrite'               => array( 'slug' => 'enquiry-source' ),
			'capabilities'          => apply_filters( 'mdjm_event_type_caps', array(
				'manage_terms'          => 'manage_mdjm',
				'edit_terms'            => 'manage_mdjm',
				'delete_terms'          => 'manage_mdjm',
				'assign_terms'          => 'mdjm_employee'
			) ),
			'update_count_callback' => '_update_generic_term_count'
		)
	);
	register_taxonomy( 'enquiry-source', array( 'mdjm-event' ), $enquiry_source_args );
	register_taxonomy_for_object_type( 'enquiry-source', 'mdjm-event' );
	
	/** Playlist Category */
	$playlist_category_labels = array(
		'name'              		   => _x( 'Playlist Categories', 'taxonomy general name', 'mobile-dj-manager' ),
		'singular_name'     		  => _x( 'Playlist Category', 'taxonomy singular name', 'mobile-dj-manager' ),
		'search_items'      		   => __( 'Playlist Categories', 'mobile-dj-manager' ),
		'all_items'         		  => __( 'All Playlist Categories', 'mobile-dj-manager' ),
		'edit_item'        		  => __( 'Edit Playlist Category', 'mobile-dj-manager' ),
		'update_item'       			=> __( 'Update Playlist Category', 'mobile-dj-manager' ),
		'add_new_item'      		   => __( 'Add New Playlist Category', 'mobile-dj-manager' ),
		'new_item_name'     		  => __( 'New Playlist Category', 'mobile-dj-manager' ),
		'menu_name'         		  => __( 'Event Playlist Categories', 'mobile-dj-manager' ),
		'separate_items_with_commas' => NULL,
		'choose_from_most_used'	  => __( 'Choose from the most popular Playlist Categories', 'mobile-dj-manager' ),
		'not_found'				  => __( 'No playlist categories found', 'mobile-dj-manager' )
	);

	$playlist_category_args = apply_filters( 'mdjm_playlist_category_args', array(
			'hierarchical'          => true,
			'labels'                => apply_filters( 'mdjm_playlist_category_labels', $playlist_category_labels ),
			'query_var'             => true,
			'capabilities'          => apply_filters( 'mdjm_playlist_category_caps', array(
				'manage_terms'          => 'manage_mdjm',
				'edit_terms'            => 'manage_mdjm',
				'delete_terms'          => 'manage_mdjm',
				'assign_terms'          => 'mdjm_employee'
			) ),
			'update_count_callback' => '_update_generic_term_count'
		)
	);
	register_taxonomy( 'playlist-category', array( 'mdjm-playlist' ), $playlist_category_args );
	register_taxonomy_for_object_type( 'playlist-category', 'mdjm-playlist' );
	
	/** Transaction Types */
	$txn_type_labels = array(
		'name'              		   => _x( 'Transaction Type', 'taxonomy general name', 'mobile-dj-manager' ),
		'singular_name'     		  => _x( 'Transaction Type', 'taxonomy singular name', 'mobile-dj-manager' ),
		'search_items'      		   => __( 'Search Transaction Types', 'mobile-dj-manager' ),
		'all_items'         		  => __( 'All Transaction Types', 'mobile-dj-manager' ),
		'edit_item'        		  => __( 'Edit Transaction Type', 'mobile-dj-manager' ),
		'update_item'       			=> __( 'Update Transaction Type', 'mobile-dj-manager' ),
		'add_new_item'      		   => __( 'Add New Transaction Type', 'mobile-dj-manager' ),
		'new_item_name'     		  => __( 'New Transaction Type', 'mobile-dj-manager' ),
		'menu_name'         		  => __( 'Transaction Types', 'mobile-dj-manager' ),
		'separate_items_with_commas' => NULL,
		'choose_from_most_used'	  => __( 'Choose from the most popular Transaction Types', 'mobile-dj-manager' ),
		'not_found'				  => __( 'No transaction types found', 'mobile-dj-manager' )
	);

	$txn_type_args = apply_filters( 'mdjm_transaction_type_args', array(
			'hierarchical'          => true,
			'labels'                => apply_filters( 'mdjm_transaction_type_labels', $txn_type_labels ),
			'query_var'             => true,
			'rewrite'           		=> array( 'slug' => 'transaction-types' ),
			'capabilities'          => apply_filters( 'mdjm_transaction_type_caps', array(
				'manage_terms'	=> 'manage_mdjm',
				'edit_terms'	  => 'manage_mdjm',
				'delete_terms'	=> 'manage_mdjm',
				'assign_terms'	=> 'mdjm_employee'
			) ),
			'update_count_callback' => '_update_generic_term_count'
		)
	);
	register_taxonomy( 'transaction-types', array( 'mdjm-transaction' ), $txn_type_args );
	register_taxonomy_for_object_type( 'transaction-types', 'mdjm-transaction' );
	
	/** Venue Details */
	$venue_details_labels = array(
		'name'              		   => _x( 'Venue Details', 'taxonomy general name', 'mobile-dj-manager' ),
		'singular_name'     		  => _x( 'Venue Detail', 'taxonomy singular name', 'mobile-dj-manager' ),
		'search_items'      		   => __( 'Search Venue Details', 'mobile-dj-manager' ),
		'all_items'         		  => __( 'All Venue Details', 'mobile-dj-manager' ),
		'edit_item'        		  => __( 'Edit Venue Detail', 'mobile-dj-manager' ),
		'update_item'       			=> __( 'Update Venue Detail', 'mobile-dj-manager' ),
		'add_new_item'      		   => __( 'Add New Venue Detail', 'mobile-dj-manager' ),
		'new_item_name'     		  => __( 'New Venue Detail', 'mobile-dj-manager' ),
		'menu_name'         		  => __( 'Venue Details', 'mobile-dj-manager' ),
		'separate_items_with_commas' => NULL,
		'choose_from_most_used'	  => __( 'Choose from the most popular Venue Details', 'mobile-dj-manager' ),
		'not_found'				  => __( 'No details found', 'mobile-dj-manager' )
	);

	$venue_details_args = apply_filters( 'mdjm_venue_details_args', array(
			'hierarchical'          => true,
			'labels'                => apply_filters( 'mdjm_venue_details_labels', $venue_details_labels ),
			'query_var'             => true,
			'rewrite'           		=> array( 'slug' => 'venue-details' ),
			'capabilities'          => apply_filters( 'mdjm_venue_details_caps', array(
				'manage_terms'	=> 'manage_mdjm',
				'edit_terms'	  => 'manage_mdjm',
				'delete_terms'	=> 'manage_mdjm',
				'assign_terms'	=> 'mdjm_employee'
			) ),
			'update_count_callback' => '_update_generic_term_count'
		)
	);
	register_taxonomy( 'venue-details', array( 'mdjm-venue' ), $venue_details_args );
	register_taxonomy_for_object_type( 'venue-details', 'mdjm-venue' );
} // mdjm_register_taxonomies
add_action( 'init', 'mdjm_register_taxonomies', 0 );

/**
 * Retrieve all MDJM Post Types.
 *
 * @since	1.3
 * @param
 * @return	arr
 */
function mdjm_get_post_types()	{
	$post_types = array(
		'mdjm_communication',
		'contract',
		'mdjm-custom-fields',
		'mdjm-signed-contract',
		'email_template',
		'mdjm-event',
		'mdjm-quotes',
		'mdjm-transaction',
		'mdjm-venue'
	);
	
	return apply_filters( 'mdjm_post_types', $post_types );
} // mdjm_get_post_types