<?php
/*
 * Update procedures for version 1.2.2
 *
 *
 *
 */
	class MDJM_Upgrade_to_1_2_2	{
		function __construct()	{
			$this->update_settings();
		}
		
		function update_settings()	{
			// Change the enable debug setting key whilst carrying the current setting
			// Then remove the old key
			$debugging = get_option( 'mdjm_debug_settings' );
			
			$debugging['enable'] = get_option( 'mdjm_debug' );
			
			update_option( 'mdjm_debug_settings', $debugging );
			delete_option( 'mdjm_debug' );
						
		} // update_settings
	} // class MDJM_Upgrade_to_1_2_2
	
	new MDJM_Upgrade_to_1_2_2();