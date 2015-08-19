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
			
			if ( get_option( MDJM_DB_VERSION_KEY ) == $mdjm->db_version )	{
				$GLOBALS['mdjm_debug']->log_it( 'No database update is required' );
				return;
			}
			
			$GLOBALS['mdjm_debug']->log_it( 'Starting database upgrade procedures', true );														
			
			/* PLAYLISTS TABLE */
			$playlists_sql = "CREATE TABLE ". MDJM_PLAYLIST_TABLE . " (
								id int(11) NOT NULL AUTO_INCREMENT,
								event_id int(11) NOT NULL,
								artist varchar(255) NOT NULL,
								song varchar(255) NOT NULL,
								play_when varchar(255) NOT NULL,
								info text NOT NULL,
								added_by varchar(255) NOT NULL,
								date_added date NOT NULL,
								date_to_mdjm datetime NULL,
								upload_procedure int(11) DEFAULT '0' NOT NULL
								PRIMARY KEY  (id)
								);";
								
			/* PLAYLISTS LIBRARY TABLE */
			$music_library_sql = "CREATE TABLE ". MDJM_MUSIC_LIBRARY_TABLE . " (
								id int(11) NOT NULL AUTO_INCREMENT,
								library varchar(255) NULL,
								library_slug varchar(255) NOT NULL,
								song varchar(255) NOT NULL,
								artist varchar(255) NOT NULL,
								album varchar(255) NULL,
								genre varchar(255) NULL,
								year varchar(10) NULL,
								comments text NULL,
								rating varchar(10) NULL,
								dj int(11) NOT NULL,
								date_added date NULL,
								PRIMARY KEY  (id)
								);";
								
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
								
			/* JOURNAL TABLE */
			$journal_sql = "CREATE TABLE ". MDJM_JOURNAL_TABLE . " (
							id int(11) NOT NULL AUTO_INCREMENT,
							client int(11) NOT NULL,
							event int(11) NOT NULL,
							timestamp varchar(255) NOT NULL,
							author int(11) NOT NULL,
							type varchar(255) NOT NULL,
							source varchar(255) NOT NULL,
							entry text NOT NULL,
							migration varchar(10) NULL,
							PRIMARY KEY  (id),
							KEY client (client,event),
							KEY entry_date (timestamp,type(10)),
							KEY author (author)
							);";
											
			require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
			dbDelta( $playlists_sql );
			dbDelta( $music_library_sql );
			dbDelta( $holiday_sql );
			dbDelta( $journal_sql );
		
			update_option( MDJM_DB_VERSION_KEY, $mdjm->db_version );
			$GLOBALS['mdjm_debug']->log_it( 'Completed database upgrade procedures', true );
		} // update_db
	} // class MDJM_DB