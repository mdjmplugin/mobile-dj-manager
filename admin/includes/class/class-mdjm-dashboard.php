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
		public $is_admin = false;
		
		public function test()	{
			if( $is_admin == true )	{
				echo 'True!';	
			}
		}
		
	}
?>