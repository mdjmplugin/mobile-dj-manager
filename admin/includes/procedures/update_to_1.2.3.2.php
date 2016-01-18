<?php
/*
 * Update procedures for version 1.2.3.2
 *
 *
 *
 */
	class MDJM_Upgrade_to_1_2_3_2	{
		function __construct()	{
			$this->update_settings();
		}
		
		function update_settings()	{
			$payment_settings = get_option( 'mdjm_payment_settings' );
			
			$GLOBALS['mdjm_debug']->log_it( 'Updating Payment settings', false );
			
			$payment_settings['deposit_type'] = false;
			$payment_settings['deposit_amount'] = '50.00';
			
			update_option( 'mdjm_payment_settings', $payment_settings );
						
		} // update_settings
		
	} // class MDJM_Upgrade_to_1_2_3_2
	
	new MDJM_Upgrade_to_1_2_3_2();