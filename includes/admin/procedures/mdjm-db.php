<?php
/*
 * MDJM Database management class
 *
 *
 *
 */
	class MDJM_DB	{
		function __construct()	{
			
		}
		
		/*
		 * Update the database tables as required
		 *
		 *
		 *
		 */
		public function update_db()	{
			global $wpdb, $mdjm;
			
			if ( get_option( 'mdjm_db_version' ) == $mdjm->db_version )	{
				MDJM()->debug->log_it( 'No database update is required' );
				return;
			}
			
			MDJM()->debug->log_it( 'Starting database upgrade procedures', true );														
																
			/* AVAILABILITY TABLE */
			$holiday_sql = "CREATE TABLE ". MDJM_HOLIDAY_TABLE . " (
								id int(11) NOT NULL AUTO_INCREMENT,
								user_id int(11) NOT NULL,
								entry_id varchar(100) NOT NULL,
								date_from date NOT NULL,
								date_to date NOT NULL,
								notes text NULL,
								PRIMARY KEY  (id),
								KEY user_id (user_id)
								);";
																			
			require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
			dbDelta( $holiday_sql );
		
			update_option( 'mdjm_db_version', $mdjm->db_version );
			MDJM()->debug->log_it( 'Completed database upgrade procedures', true );
		} // update_db
	} // class MDJM_DB