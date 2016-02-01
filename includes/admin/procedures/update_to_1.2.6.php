<?php
/*
 * Update procedures for version 1.2.6
 *
 *
 *
 */
	class MDJM_Upgrade_to_1_2_6	{
		function __construct()	{
			$this->remove_deprecated_db_tables();
		}
		
		/**
		 * Remove deprecated DB tables
		 *
		 *
		 *
		 *
		 */
		function remove_deprecated_db_tables()	{
			global $wpdb;
			
			MDJM()->debug->log_it( 'Removing deprecated DB tables' );
			
			$tables = array( 
						'Events'		   => $wpdb->prefix . 'mdjm_events', 
						'Music Library'	=> $wpdb->prefix . 'mdjm_music_library',
						'Transactions'	 => $wpdb->prefix . 'mdjm_trans',
						'Journal'		  => $wpdb->prefix . 'mdjm_journal' );
		
			foreach( $tables as $table_display => $table_name )	{
				$results = $wpdb->get_results( "SHOW TABLES LIKE '" . $table_name . "'" );
				if( $results )	{
					$wpdb->query( 'DROP TABLE IF EXISTS ' . $table_name );
					MDJM()->debug->log_it( $table_name . ' Removed' );
				}
			}
			
			MDJM()->debug->log_it( 'Completed removing deprecated DB tables' );
		}
	} // class MDJM_Upgrade_to_1_2_6
	
	new MDJM_Upgrade_to_1_2_6();