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
			MDJM()->debug->log_it( 'Updating Settings' );
			// Add the enable playlist setting option and enable by default
			$playlist_settings = get_option( 'mdjm_playlist_settings' );
			
			$playlist_settings['enable_playlists'] = true;
			
			update_option( 'mdjm_playlist_settings', $playlist_settings );
			MDJM()->debug->log_it( 'Settings Updated' );
		} // update_settings
		
	}
	
	new MDJM_Upgrade_to_1_2_5_3();