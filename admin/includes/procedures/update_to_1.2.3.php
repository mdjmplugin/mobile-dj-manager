<?php
/*
 * Update procedures for version 1.2.3
 *
 *
 *
 */
	class MDJM_Upgrade_to_1_2_3	{
		function __construct()	{
			$this->update_settings();
			$this->update_playlist();
			$this->update_playlist_upload_schedule();
		}
		
		function update_settings()	{
			$clientzone_settings = get_option( 'mdjm_clientzone_settings' );
			
			$clientzone_settings['update_event'] = false;
			$clientzone_settings['edit_event_stop'] = '5';
			
			update_option( 'mdjm_clientzone_settings', $clientzone_settings );
			
			$uninst_settings = get_option( 'mdjm_uninst' );
			
			$uninst_settings['uninst_remove_mdjm_posts'] = true;
			$uninst_settings['uninst_remove_mdjm_pages'] = true;
			$uninst_settings['uninst_remove_users'] = true;
			
			update_option( 'mdjm_uninst', $uninst_settings );
			
		} // update_settings
		
		/*
		 * Update all playlist records.
		 * Each entry that has been uploaded will have the field `uploaded_to_mdjm` set to 1
		 *
		 *
		 */
		function update_playlist()	{
			global $wpdb;
			
			$GLOBALS['mdjm_debug']->log_it( 'Updating playlist entries' );
			
			$query = "SELECT * FROM " . MDJM_PLAYLIST_TABLE . " WHERE `date_to_mdjm` IS NOT NULL AND `date_to_mdjm` != '' AND `date_to_mdjm` != '0000-00-00 00:00:00' ORDER BY `event_id`";
			
			$records = $wpdb->get_results( $query );
			
			$rows = $wpdb->num_rows;
			
			$GLOBALS['mdjm_debug']->log_it( $rows . ' ' . _n( 'record ', 'records ', $rows ) . ' to update' );
			
			if( $rows > 0 )	{
				$i = 0;
				
				foreach( $records as $record )	{
					$wpdb->update( MDJM_PLAYLIST_TABLE, 
										 array( 'upload_procedure' => '1' ), 
										 array( 'id' => $record->id ) );
					$i++;
				}
				$GLOBALS['mdjm_debug']->log_it( $i . ' ' . _n( 'record ', 'records ', $rows ) . ' updated' );				
			}
			else	{
				$GLOBALS['mdjm_debug']->log_it( 'No records to update' );	
			}
		} // update_playlist
		
		/*
		 * Update upload playlist scheduled task to run twice daily
		 * 
		 *
		 *
		 */
		function update_playlist_upload_schedule()	{
			$mdjm_schedules = get_option( MDJM_SCHEDULES_KEY );
			
			$mdjm_schedules['upload-playlists']['frequency'] = 'Twice Daily';
			
			update_option( MDJM_SCHEDULES_KEY, $mdjm_schedules );
		}
	} // class MDJM_Upgrade_to_1_2_3
	
	new MDJM_Upgrade_to_1_2_3();