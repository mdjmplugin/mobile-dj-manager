<?php
/**
 * Create terms for each of the playlist categories.
 *
 * @since	1.3
 * @param
 * @return	void
 */
function mdjm_create_playlist_terms()	{
	global $wpdb;
	
	$cats = mdjm_get_option( 'playlist_cats' );
	
	$terms = explode( "\r\n", $cats );
	
	if ( ! empty( $terms ) )	{
		foreach( $terms as $term )	{
			$new_term = wp_insert_term( $term, 'playlist-category' );
			
			if( is_wp_error( $new_term ) )	{
				error_log( $new_term->get_error_message() );
			}
		}
	}
		
	wp_insert_term(
		__( 'Guest', 'mobile-dj-manager' ),
		'playlist-category',
		array(
			'slug'			=> 'mdjm-playlist-guest',
			'description'	=> __( 'Playlist entries added by guests are stored in this term.', 'mobile-dj-manager' )
		)
		
	);
} // mdjm_create_playlist_terms

/**
 * Create terms for each of the enquiry sources.
 *
 * @since	1.3
 * @param
 * @return	void
 */
function mdjm_create_enquiry_source_terms_13( $term )	{
	global $wpdb;
		
	if ( empty( $term ) )	{
		return false;
	}
	
	$new_term = wp_insert_term( $term, 'enquiry-source' );
			
	if( is_wp_error( $new_term ) )	{
		error_log( $new_term->get_error_message() );
		return false;
	} else	{
		error_log( sprintf( 'Enquiry Source term %s created with ID %s', $term, $new_term['term_id'] ), 0 );
	}
	
	return $new_term;

} // mdjm_create_enquiry_source_terms_13

/**
 * Migrate events to new enquiry source categories.
 *
 * @since	1.3
 * @param
 * @return	void
 */
function mdjm_migrate_event_enquiry_sources_13()	{

	$cats = mdjm_get_option( 'enquiry_sources' );
	
	if ( empty( $cats ) )	{
		return;
	}
	
	$terms = explode( "\r\n", $cats );

	foreach( $terms as $term )	{
		
		$new_term = mdjm_create_enquiry_source_terms_13( $term );
		
		if ( empty( $new_term ) )	{
			continue;
		}
			
		$events = get_posts(
			array(
				'post_type'      => 'mdjm-event',
				'post_status'    => 'any',
				'posts_per_page' => -1,
				'meta_key'       => '_mdjm_event_enquiry_source',
				'meta_query'     => array(
					array(
						'key'    => '_mdjm_event_enquiry_source',
						'value'  => $term
					)
				)
			)
		);
		
		if ( ! $events )	{
			continue;
		}
		
		foreach( $events as $event )	{
			wp_set_object_terms( $event->ID, $new_term['term_id'], 'enquiry-source', true );
		}
		
	}
	
} // mdjm_migrate_event_enquiry_sources_13

/**
 * Create the Employee Wages term within Transactions and update other
 * MDJM Txn term slugs
 *
 *
 *
 */
function mdjm_txn_terms_13()	{
	
	if( get_option( 'mdjm_txn_terms_13' ) )	{
		return;
	}
	
	wp_insert_term(
		__( 'Employee Wages','mobile-dj-manager' ),
		'transaction-types',
		array(
			'description'	=> __( 'All employee wage payments are assigned to this term','mobile-dj-manager' ),
			'slug'			=> 'mdjm-employee-wages'
		)
	);
	
	$settings = get_option( 'mdjm_settings' );
	
	$terms = array(
		$settings['balance_label']					=> array( 
			'mdjm-balance-payment', __( 'Event balance payments are assigned to this term', 'mobile-dj-manager' ) ),
		$settings['deposit_label']					=> array(
			'mdjm-deposit-payment', __( 'Event deposit payments are assigned to this term', 'mobile-dj-manager' ) ),
		__( 'Merchant Fees', 'mobile-dj-manager' )	=> array(
			'mdjm-merchant-fees', __( 'Charges from payment gateways are assigned to this term', 'mobile-dj-manager' ) )
	);
	
	foreach( $terms as $name => $args )	{
		$term = get_term_by( 'name', $name, 'transaction-types' );
		
		if ( ! empty( $term ) )	{
			wp_update_term(
				(int) $term->term_id,
				'transaction-types',
				array(
					'slug'			=> $args[0],
					'description'	=> $args[1]
				)
			);
		}
	}
	
	update_option( 'mdjm_txn_terms_13', true );
	
} // mdjm_txn_terms_13
add_action( 'init', 'mdjm_txn_terms_13', 15 );

/**
 * Update the post meta key '_mdjm_signed_contract' to the correct
 * naming format.
 *
 * @since	1.3
 * @param
 * @return	void
 *
 */
function mdjm_update_contract_meta_key_13()	{
	global $wpdb;
	
	$wpdb->update(
		$wpdb->postmeta,
		array( 'meta_key' => '_mdjm_event_signed_contract' ),
		array( 'meta_key' => '_mdjm_signed_contract' )
	);
	
} // mdjm_update_contract_meta_key_13
add_action( 'init', 'mdjm_update_contract_meta_key_13' );

/**
 * Import playlist entries from custom DB table.
 *
 * Loop through all entries in the custom table, create posts for them and assign the terms.
 *
 * @since	1.3
 * @param
 * @return	void
 */
function mdjm_import_playlist_entries()	{
	global $wpdb;
	
	if( get_option( 'mdjm_playlist_import' ) )	{
		return;
	}
	
	// Create the terms
	mdjm_create_playlist_terms();
		
	$query = "SELECT * FROM 
			 " . $wpdb->prefix . "mdjm_playlists";
			 
	$entries = $wpdb->get_results( $query );
	
	if( $entries )	{
		add_option( 'mdjm_playlist_import', false );
		foreach( $entries as $entry )	{
			$meta = array(
				'song'          => isset( $entry->song )             ? $entry->song              : '',
				'artist'        => isset( $entry->artist )           ? $entry->artist            : '',
				'added_by'      => isset( $entry->added_by )         ? $entry->added_by          : get_current_user_id(),
				'djnotes'       => isset( $entry->info )	         ? $entry->info	          : '',
				'added_date'    => isset( $entry->date_added )       ? $entry->date_added	    : '',
				'category'      => isset( $entry->play_when )        ? trim( $entry->play_when ) : 'Guest',
				'to_mdjm'       => isset( $entry->date_to_mdjm )	 ? date( 'Y-m-d H:i:s', strtotime( $entry->date_to_mdjm ) )	  : '',
				'uploaded'      => isset( $entry->upload_procedure ) ? $entry->upload_procedure  : '',
			);
			
			$term        = isset( $entry->play_when )   ? trim( $entry->play_when ) : 'Guest';
			$event_id	= isset( $entry->event_id )    ? $entry->event_id  : '';
			
			if( empty( $term ) || $term == 'Guest Added' )	{
				$term = 'Guest';
				$meta['category'] = $term;
			}
			
			if( ! term_exists( $term, 'playlist-category' ) )	{
				wp_insert_term( $term, 'playlist-category' );
			}
		
			$title = sprintf( __( 'Event ID: %s %s %s', 'mobile-dj-manager' ),
				mdjm_get_option( 'event_prefix', '' ) . $event_id,
				$meta['song'],
				$meta['artist'] );
			
			$category = get_term_by( 'name', $term, 'playlist-category' );
						
			$entry_id = wp_insert_post(
				array(
					'post_type'     => 'mdjm-playlist',
					'post_title'    => $title,
					'post_author'   => 1,
					'post_status'   => 'publish',
					'post_parent'   => $event_id,
					'post_date'     => isset( $entry->date_added )? date( 'Y-m-d H:i:s', strtotime( $entry->date_added ) ) : date( 'Y-m-d H:i:s' ),
					'post_category' => !empty( $category ) ? array( $category->term_id ) : ''
				)
			);
			
			if( ! empty( $category ) )	{
				mdjm_set_playlist_entry_category( $entry_id, $category->term_id );
			}
		
			foreach( $meta as $key => $value ) {
				update_post_meta( $entry_id, '_mdjm_playlist_entry_' . $key, $value );
			}
		}
		update_option( 'mdjm_playlist_import', true );
	}
} // mdjm_import_playlist_entries
add_action( 'init', 'mdjm_import_playlist_entries', 15 );

/*
 * Update procedures for version 1.3
 *
 *
 *
 */
class MDJM_Upgrade_to_1_3	{
	function __construct()	{
		$this->update_caps();
	}
	
	/**
	 * Apply all methods for updating the caps
	 *
	 *
	 */
	public function update_caps()	{
		$this->update_admin_caps();
		$this->remove_old_caps();
		$this->update_dj_caps();	
	} // update_caps
	
	/**
	 * Set all admin's to be DJ's. Users can turn off if they want to.
	 * We'll also give these users the full admin rights over MDJM.
	 * If the _mdjm_event_staff user meta is disabled in future, this cap will be removed
	 *
	 *
	 */
	public function update_admin_caps()	{
		MDJM()->debug->log_it( 'Updating Administrator capabilities', true );
		
		$admins = get_users( array( 'role' => 'administrator' ) );
		
		// Remove the mdjm_employee and manage_mdjm caps from the administrator role
		$role = get_role( 'administrator' );
		$role->remove_cap( 'manage_mdjm' );
		$role->remove_cap( 'mdjm_employee' );
					
		// Give each admin the additional caps of mdjm_employee and manage_mdjm
		foreach( $admins as $user )	{
			update_user_meta( $user->ID, '_mdjm_event_staff', true );
			$user->add_cap( 'manage_mdjm' );
			$user->add_cap( 'mdjm_employee' );
		}
		
		// By default, admins can view and edit all post types
		$caps = array(
			// Clients
			'mdjm_client_edit' => true, 'mdjm_client_edit_own' => true,
			
			// Employees
			'mdjm_employee_edit' => true,
			
			// Packages
			'mdjm_package_edit_own' => true, 'mdjm_package_edit' => true,
						
			// Comm posts
			'mdjm_comms_send' => true, 'edit_mdjm_comms' => true, 'edit_others_mdjm_comms' => true,
			'publish_mdjm_comms' => true, 'read_private_mdjm_comms' => true, 
			'edit_published_mdjm_comms' => true, 'delete_mdjm_comms' => true,
			'delete_others_mdjm_comms' => true, 'delete_private_mdjm_comms' => true,
			'delete_published_mdjm_comms' => true, 'edit_private_mdjm_comms' => true,
			
			// Event posts
			'mdjm_event_read' => true, 'mdjm_event_read_own' => true, 'mdjm_event_edit' => true,
			'mdjm_event_edit_own' => true, 'edit_mdjm_events' => true, 'edit_others_mdjm_events' => true,
			'publish_mdjm_events' => true, 'read_private_mdjm_events' => true,
			'edit_published_mdjm_events' => true, 'edit_private_mdjm_events' => true, 'delete_mdjm_events' => true,
			'delete_others_mdjm_events' => true, 'delete_private_mdjm_events' => true,
			'delete_published_mdjm_events' => true,
			
			// Quote posts
			'mdjm_quote_view_own' => true, 'mdjm_quote_view' => true, 'edit_mdjm_quotes' => true,
			'edit_others_mdjm_quotes' => true, 'publish_mdjm_quotes' => true, 
			'read_private_mdjm_quotes' => true, 'edit_published_mdjm_quotes' => true,
			'edit_private_mdjm_quotes' => true, 'delete_mdjm_quotes' => true, 'delete_others_mdjm_quotes' => true,
			'delete_private_mdjm_quotes' => true, 'delete_published_mdjm_quotes' => true,
			
			// Templates
			'mdjm_template_edit' => true, 'edit_mdjm_templates' => true,
			'edit_others_mdjm_templates' => true, 'publish_mdjm_templates' => true, 'read_private_mdjm_templates' => true,
			'edit_published_mdjm_templates' => true, 'edit_private_mdjm_templates' => true, 'delete_mdjm_templates' => true,
			'delete_others_mdjm_templates' => true, 'delete_private_mdjm_templates' => true,
			'delete_published_mdjm_templates' => true,
			
			// Transaction posts
			'mdjm_txn_edit' => true, 'edit_mdjm_txns' => true, 'edit_others_mdjm_txns' => true, 'publish_mdjm_txns' => true,
			'read_private_mdjm_txns' => true, 'edit_published_mdjm_txns' => true, 'edit_private_mdjm_txns' => true,
			'delete_mdjm_txns' => true, 'delete_others_mdjm_txns' => true, 'delete_private_mdjm_txns' => true,
			'delete_published_mdjm_txns' => true,
			
			// Venue posts
			'mdjm_venue_read' => true, 'mdjm_venue_edit' => true, 'edit_mdjm_venues' => true,
			'edit_others_mdjm_venues' => true, 'publish_mdjm_venues' => true, 'read_private_mdjm_venues' => true,
			'edit_published_mdjm_venues' => true, 'edit_private_mdjm_venues' => true, 'delete_mdjm_venues' => true,
			'delete_others_mdjm_venues' => true, 'delete_private_mdjm_venues' => true,
			'delete_published_mdjm_venues' => true
		);
		
		foreach( $caps as $cap => $set )	{
			$role->add_cap( $cap );					
		}
		
		MDJM()->debug->log_it( 'Completed updating Administrator capabilities', true );
	} // update_admin_caps
	
	/**
	 * Update the DJ capabilities
	 * 
	 * 
	 *
	 *
	 */
	public function update_dj_caps()	{
		MDJM()->debug->log_it( 'Updating DJ capabilities', true );
		
		$role = get_role( 'dj' );
		$role->add_cap( 'mdjm_employee' );
		$role->add_cap( 'edit_posts' );
		$role->add_cap( 'delete_posts' );
		$role->add_cap( 'read' );
		
		MDJM()->debug->log_it( 'Completed updating DJ capabilities', true );
	} // update_dj_caps
	
	/**
	 * Remove old capabilities
	 * 
	 * 
	 *
	 *
	 */
	public function remove_old_caps()	{
		MDJM()->debug->log_it( 'Removing deprecated capabilities', true );
		
		$roles = array( 'dj', 'inactive_dj', 'administrator' );
		
		foreach( $roles as $_role )	{
			$role = get_role( $_role );
			
			$role->remove_cap( 'delete_mdjm_manage_events' );
			$role->remove_cap( 'delete_mdjm_manage_quotes' );
			$role->remove_cap( 'delete_mdjm_manage_transactions' );
			$role->remove_cap( 'delete_mdjm_manage_venues' );
			$role->remove_cap( 'delete_mdjm_signed_contracts' );
			$role->remove_cap( 'delete_others_mdjm_manage_events' );
			$role->remove_cap( 'delete_others_mdjm_manage_quotes' );
			$role->remove_cap( 'delete_others_mdjm_manage_transactions' );
			$role->remove_cap( 'delete_others_mdjm_manage_venues' );
			$role->remove_cap( 'delete_others_mdjm_signed_contracts' );
			$role->remove_cap( 'delete_private_mdjm_manage_events' );
			$role->remove_cap( 'delete_private_mdjm_manage_quotes' );
			$role->remove_cap( 'delete_private_mdjm_manage_transactions' );
			$role->remove_cap( 'delete_private_mdjm_manage_venues' );
			$role->remove_cap( 'delete_private_mdjm_signed_contracts' );
			$role->remove_cap( 'delete_published_mdjm_manage_events' );
			$role->remove_cap( 'delete_published_mdjm_manage_quotes' );
			$role->remove_cap( 'delete_published_mdjm_manage_transactions' );
			$role->remove_cap( 'delete_published_mdjm_manage_venues' );
			$role->remove_cap( 'delete_published_mdjm_signed_contracts' );
			$role->remove_cap( 'edit_mdjm_manage_event' );
			$role->remove_cap( 'edit_mdjm_manage_events' );
			$role->remove_cap( 'edit_mdjm_manage_quote' );
			$role->remove_cap( 'edit_mdjm_manage_quotes' );
			$role->remove_cap( 'edit_mdjm_manage_transaction' );
			$role->remove_cap( 'edit_mdjm_manage_transactions' );
			$role->remove_cap( 'edit_mdjm_manage_venue' );
			$role->remove_cap( 'edit_mdjm_manage_venues' );
			$role->remove_cap( 'edit_mdjm_signed_contract' );
			$role->remove_cap( 'edit_mdjm_signed_contracts' );
			$role->remove_cap( 'edit_others_comms' );
			$role->remove_cap( 'edit_others_mdjm-events' );
			$role->remove_cap( 'edit_others_mdjm_manage_events' );
			$role->remove_cap( 'edit_others_mdjm_manage_quotes' );
			$role->remove_cap( 'edit_others_mdjm_manage_transactions' );
			$role->remove_cap( 'edit_others_mdjm_manage_venues' );
			$role->remove_cap( 'edit_others_mdjm_signed_contracts' );
			$role->remove_cap( 'edit_private_mdjm_manage_events' );
			$role->remove_cap( 'edit_private_mdjm_manage_quotes' );
			$role->remove_cap( 'edit_private_mdjm_signed_contracts' );
			$role->remove_cap( 'edit_published_mdjm-events' );
			$role->remove_cap( 'edit_published_mdjm_manage_events' );
			$role->remove_cap( 'edit_published_mdjm_manage_quotes' );
			$role->remove_cap( 'edit_published_mdjm_manage_transactions' );
			$role->remove_cap( 'edit_published_mdjm_manage_venues' );
			$role->remove_cap( 'edit_published_mdjm_signed_contracts' );
			$role->remove_cap( 'publish_mdjm_manage_event' );
			$role->remove_cap( 'publish_mdjm_manage_events' );
			$role->remove_cap( 'publish_mdjm_manage_quotes' );
			$role->remove_cap( 'publish_mdjm_manage_transactions' );
			$role->remove_cap( 'publish_mdjm_manage_venues' );
			$role->remove_cap( 'publish_mdjm_signed_contracts' );
			$role->remove_cap( 'read_mdjm-event' );
			$role->remove_cap( 'read_mdjm_event_quote' );
			$role->remove_cap( 'read_mdjm_manage_event' );
			$role->remove_cap( 'read_mdjm_manage_quote' );
			$role->remove_cap( 'read_mdjm_manage_transaction' );
			$role->remove_cap( 'read_mdjm_manage_venue' );
			$role->remove_cap( 'read_mdjm_signed_contract' );
			$role->remove_cap( 'read_private_mdjm-events' );
			$role->remove_cap( 'read_private_mdjm_event_quotes' );
			$role->remove_cap( 'read_private_mdjm_manage_events' );
			$role->remove_cap( 'read_private_mdjm_manage_quotes' );
			$role->remove_cap( 'read_private_mdjm_manage_transactions' );
			$role->remove_cap( 'read_private_mdjm_manage_venues' );
			$role->remove_cap( 'read_private_mdjm_signed_contracts' );
		}
		
		MDJM()->debug->log_it( 'Completed removing deprecated capabilities', true );
	} // remove_old_caps
			
	/*
	 * Update MDJM settings
	 *
	 *
	 *
	 */
	public function update_settings()	{
		
	} // update_settings
			
} // class MDJM_Upgrade_to_1_3

new MDJM_Upgrade_to_1_3();