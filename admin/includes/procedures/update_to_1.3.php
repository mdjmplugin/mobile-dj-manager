<?php
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
				'mdjm_comms_send' => true, 'edit_mdjm_comm' => true, 'read_mdjm_comm' => true,
				'delete_mdjm_comm' => true, 'edit_mdjm_comms' => true, 'edit_others_mdjm_comms' => true,
				'publish_mdjm_comms' => true, 'read_private_mdjm_comms' => true, 
				'edit_published_mdjm_comms' => true, 'delete_mdjm_comms' => true,
				'delete_others_mdjm_comms' => true, 'delete_private_mdjm_comms' => true,
				'delete_published_mdjm_comms' => true, 'edit_private_mdjm_comms' => true,
				
				// Event posts
				'mdjm_event_edit' => true, 'mdjm_event_edit_own' => true, 'edit_mdjm_event' => true,
				'read_mdjm_event' => true, 'delete_mdjm_event' => true, 'edit_mdjm_events' => true,
				'edit_others_mdjm_events' => true, 'publish_mdjm_events' => true, 'read_private_mdjm_events' => true,
				'edit_published_mdjm_events' => true, 'edit_private_mdjm_events' => true, 'delete_mdjm_events' => true,
				'delete_others_mdjm_events' => true, 'delete_private_mdjm_events' => true,
				'delete_published_mdjm_events' => true,
				
				// Quote posts
				'mdjm_quote_view_own' => true, 'mdjm_quote_view' => true, 'edit_mdjm_quote' => true,
				'read_mdjm_quote' => true, 'delete_mdjm_quote' => true, 'edit_mdjm_quotes' => true,
				'edit_others_mdjm_quotes' => true, 'publish_mdjm_quotes' => true, 
				'read_private_mdjm_quotes' => true, 'edit_published_mdjm_quotes' => true,
				'edit_private_mdjm_quotes' => true, 'delete_mdjm_quotes' => true, 'delete_others_mdjm_quotes' => true,
				'delete_private_mdjm_quotes' => true, 'delete_published_mdjm_quotes' => true,
				
				// Templates
				'mdjm_template_edit' => true, 'edit_mdjm_template' => true,
				'read_mdjm_template' => true, 'delete_mdjm_template' => true, 'edit_mdjm_templates' => true,
				'edit_others_mdjm_templates' => true, 'publish_mdjm_templates' => true, 'read_private_mdjm_templates' => true,
				'edit_published_mdjm_templates' => true, 'edit_private_mdjm_templates' => true, 'delete_mdjm_templates' => true,
				'delete_others_mdjm_templates' => true, 'delete_private_mdjm_templates' => true,
				'delete_published_mdjm_templates' => true,
				
				// Transaction posts
				'mdjm_txn_edit' => true, 'edit_mdjm_txn' => true, 'read_mdjm_txn' => true, 'delete_mdjm_txn' => true,
				'edit_mdjm_txns' => true, 'edit_others_mdjm_txns' => true, 'publish_mdjm_txns' => true,
				'read_private_mdjm_txns' => true, 'edit_published_mdjm_txns' => true, 'edit_private_mdjm_txns' => true,
				'delete_mdjm_txns' => true, 'delete_others_mdjm_txns' => true, 'delete_private_mdjm_txns' => true,
				'delete_published_mdjm_txns' => true,
				
				// Venue posts
				'mdjm_venue_read' => true, 'mdjm_venue_edit' => true, 'edit_mdjm_venue' => true,
				'read_mdjm_venue' => true, 'delete_mdjm_venue' => true, 'edit_mdjm_venues' => true,
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