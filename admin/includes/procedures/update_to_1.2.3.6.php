<?php
/*
 * Update procedures for version 1.2.3.6
 *
 *
 *
 */
	class MDJM_Upgrade_to_1_2_3_6	{
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
			
			$GLOBALS['mdjm_debug']->log_it( 'Updating Settings for version 1.2.3.6' );
			
			$mdjm_paypal_settings = get_option( 'mdjm_paypal_settings' );
			$mdjm_payment_settings = get_option( 'mdjm_payment_settings' );
			
			$mdjm_paypal_settings['button_text'] = __( 'Pay Now', 'mobile-dj-manager' ); // Customised HTML button text for payments
			$mdjm_payment_settings['other_amount_label'] = __( 'Other Amount', 'mobile-dj-manager' ); // Customised text for other amoun radio field
			
			update_option( 'mdjm_paypal_settings', $mdjm_paypal_settings );
			update_option( 'mdjm_payment_settings', $mdjm_payment_settings );
			
			$GLOBALS['mdjm_debug']->log_it( 'Settings Updated' );
						
		} // update_settings
		
	} // class MDJM_Upgrade_to_1_2_3_6
	
	new MDJM_Upgrade_to_1_2_3_6();