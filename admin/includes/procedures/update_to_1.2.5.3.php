<?php
/*
 * Update procedures for version 1.2.5.3
 *
 *
 *
 */
	class MDJM_Upgrade_to_1_2_5_3	{
		function __construct()	{
			$this->update_settings();
		}
		
		function update_settings()	{
			$GLOBALS['mdjm_debug']->log_it( 'Updating Settings' );
			// Add the enable playlist setting option and enable by default
			$playlist_settings = get_option( MDJM_PLAYLIST_SETTINGS_KEY );
			
			$playlist_settings['enable_playlists'] = true;
			
			update_option( MDJM_PLAYLIST_SETTINGS_KEY, $playlist_settings );
			$GLOBALS['mdjm_debug']->log_it( 'Settings Updated' );
		} // update_settings
		
	}
	
	new MDJM_Upgrade_to_1_2_5_3();