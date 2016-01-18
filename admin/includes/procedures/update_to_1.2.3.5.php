<?php
/*
 * Update procedures for version 1.2.3.5
 *
 *
 *
 */
	class MDJM_Upgrade_to_1_2_3_5	{
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
			
			$GLOBALS['mdjm_debug']->log_it( 'Updating Settings for version 1.2.3.3' );
			
			if( !get_option( 'm_d_j_m_has_initiated' ) )
				add_option( 'm_d_j_m_has_initiated', current_time( 'timestamp' ) );
			
			$GLOBALS['mdjm_debug']->log_it( 'Settings Updated' );
						
		} // update_settings
		
	} // class MDJM_Upgrade_to_1_2_3_5
	
	new MDJM_Upgrade_to_1_2_3_5();