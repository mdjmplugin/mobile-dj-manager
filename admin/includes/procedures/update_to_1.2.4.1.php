<?php
/*
 * Update procedures for version 1.2.4.1
 *
 *
 *
 */
	class MDJM_Upgrade_to_1_2_4_1	{
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
			
			$GLOBALS['mdjm_debug']->log_it( 'Updating Settings for version 1.2.4.1' );
						
			$status = get_option( '__mydj_validation' );
			
			if( $status['key'] == 'XXXX' || $status['type'] = 'trial' || time() >= strtotime( $status['expire'] ) )
				add_option( 'mdjm_price_warn', 1, '', 'yes' );
			
			$GLOBALS['mdjm_debug']->log_it( 'Settings Updated' );
						
		} // update_settings
				
	} // class MDJM_Upgrade_to_1_2_4_1
	
	new MDJM_Upgrade_to_1_2_4_1();