<?php
/**
 * class-mdjm-dashboard.php
 * MDJM_Dashboard Class
 * 21/02/2015
 * @since 1.1
 * A class to produce the MDJM Dashboard Overview
 * 
 * @version 1.0
 * @21/02/2015
 *
 * TODO 7 day status (admin & DJ)
 *	Status overview for month (admin & DJ)
 *	To do list (admin only)
 * 	Availability check
 *	Recent activity (payments etc..)
 * 	Latest news
 */

	class MDJM_Dashboard	{
		
		// Always assume user is not an admin
		var $is_admin = false;
		
		function f_mdjm_unseen_trans()	{
			global $wpdb;
			
			include( WPMDJM_PLUGIN_DIR . '/includes/config.inc.php' );
			
			$unseen_trans = $wpdb->get_var( "SELECT COUNT(*) FROM `". $db_tbl['trans'] . "` WHERE `seen_by_admin` = '0'" );
			
			return $unseen_trans;	
		}
		
		public function f_mdjm_notifications()	{
			if( $is_admin == true )	{
				
			}
		}
		
	}
?>