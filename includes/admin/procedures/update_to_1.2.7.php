<?php
/*
 * Update procedures for version 1.2.7
 *
 *
 *
 */
	class MDJM_Upgrade_to_1_2_7	{
		function __construct()	{
			$this->update_settings();
		}
		
		/*
		 * Update MDJM settings
		 *
		 *
		 *
		 */
		function update_settings()	{
			MDJM()->debug->log_it( 'Updating Settings for version 1.2.7' );
				
			// Update the Availability Checker settings and set default settings for Unavailable Status
			$mdjm_availability_settings = get_option( 'mdjm_availability_settings' );
			
			$mdjm_availability_settings['availability_status'] = array( 'mdjm-enquiry', 'mdjm-contract', 'mdjm-approved' );
			
			update_option( 'mdjm_availability_settings', $mdjm_availability_settings );
			
			MDJM()->debug->log_it( 'Settings Updated' );
		} // update_settings
				
	} // class MDJM_Upgrade_to_1_2_7
	
	new MDJM_Upgrade_to_1_2_7();