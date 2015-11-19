<?php
	defined( 'ABSPATH' ) or die( "Direct access to this page is disabled!!!" );
	
/**
 * Class Name: MDJM_Users
 * Manage User roles within MDJM
 *
 *
 *
 */
if( !class_exists( 'MDJM_Users' ) ) : 
	class MDJM_Users	{
		public function __construct()	{
			// Runs after Events settings are updated
			add_action ( 'update_option_mdjm_event_settings', array( &$this, 'rename_dj_role' ), 10, 2 );
		}
		
		/**
		  * Rename the DJ role display name when admin saves the event settings from the settings page.
		  * Include both the standard and inactive role.
		  *
		  * Called by: update_option_mdjm_event_settings hook
		  *
		  * @param		arr		$old_value		Old settings values
		  *				arr		$new_value		New settings values
		  */
		function rename_dj_role( $old_value, $new_value )	{
			global $wpdb;
			
			// If the artist setting has not been updated, we can return and do nothing
			if( $new_value['artist'] == $old_value['artist'] )
				return;
			
			$user_roles = get_option( $wpdb->prefix . 'user_roles' );
			
			if( empty( $user_roles ) )	{
				if( MDJM_DEBUG == true )
					$GLOBALS['mdjm_debug']->log_it( 'ERROR: Could not retrieve user roles from DB', true );
					
				return;
			}
			
			$user_roles['inactive_dj']['name'] = __( 'Inactive', 'mobile-dj-manager' ) . ' ' . $new_value['artist'];
			$user_roles['dj']['name'] = $new_value['artist'];
			
			if( update_option( $wpdb->prefix . 'user_roles', $user_roles ) )	{
				if( MDJM_DEBUG == true )
					$GLOBALS['mdjm_debug']->log_it( 'Updated DJ role name to ' . $new_value['artist'], true );	
					
				return;
			}
			else	{
				if( MDJM_DEBUG == true )
					$GLOBALS['mdjm_debug']->log_it( 'ERROR: Could not update DJ role name to ' . $new_value['artist'], true );
			}
			
		} // rename_dj_role
		
	} // class MDJM_Users
endif;
