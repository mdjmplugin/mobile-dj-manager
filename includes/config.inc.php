<?php
	global $wpdb;
/****************************************************************************************************
--	Database information	--
****************************************************************************************************/
	//if( isset( $wpdb ) )	{
		$db_tbl = array(
				'events'        => $wpdb->prefix . 'mdjm_events',
				'playlists'     => $wpdb->prefix . 'mdjm_playlists',
				'journal'       => $wpdb->prefix . 'mdjm_journal',
				'venues'        => $wpdb->prefix . 'mdjm_venues',
				'holiday'       => $wpdb->prefix . 'mdjm_avail',
				);
	//}
			
/****************************************************************************************************
--	Currency information	--
****************************************************************************************************/
	$mdjm_currency = array(
						'EUR' => '&euro;',
						'GBP' => '&pound;',
						'USD' => '$',
						);
?>