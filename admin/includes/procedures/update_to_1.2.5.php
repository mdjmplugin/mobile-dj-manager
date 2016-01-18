<?php
/*
 * Update procedures for version 1.2.5
 *
 *
 *
 */
	class MDJM_Upgrade_to_1_2_5	{
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
			$GLOBALS['mdjm_debug']->log_it( 'Updating Settings for version 1.2.5' );
				
			// Copy the payment gateway settings over the the MDJM PG addon even if it is not installed yet
			$mdjm_paypal_settings = get_option( 'mdjm_paypal_settings' );
			$mdjm_payfast_settings = get_option( 'mdjm_payfast_settings' );
			
			if( !empty( $mdjm_paypal_settings ) )
				update_option( 'mdjm_pg_paypal', $mdjm_paypal_settings );
				
			if( !empty( $mdjm_payfast_settings ) )
				update_option( 'mdjm_pg_payfast', $mdjm_payfast_settings );
			
			// Now delete the options from the MDJM Settings
			delete_option( 'mdjm_paypal_settings' );
			delete_option( 'mdjm_payfast_settings' );
			
			// Delete no longer required settings
			delete_option( '__mydj_validation' );
			delete_option( 'm_d_j_m_has_initiated' );
			delete_option( 'mdjm_pp_options' );
			delete_option( 'mdjm_price_warn' );
			
			$GLOBALS['mdjm_debug']->log_it( 'Settings Updated' );
		} // update_settings
				
	} // class MDJM_Upgrade_to_1_2_5
	
	new MDJM_Upgrade_to_1_2_5();