<?php
/*
 * Update procedures for version 1.2.4
 *
 *
 *
 */
	class MDJM_Upgrade_to_1_2_4	{
		function __construct()	{
			$this->update_settings();
			$this->update_terms();
		}
		
		/*
		 * Update MDJM settings
		 *
		 *
		 *
		 */
		function update_settings()	{
			
			$GLOBALS['mdjm_debug']->log_it( 'Updating Settings for version 1.2.4' );
			
			$mdjm_paypal_settings = get_option( 'mdjm_paypal_settings' );
			$mdjm_payment_settings = get_option( 'mdjm_payment_settings' );
			$mdjm_clientzone_settings = get_option( 'mdjm_clientzone_settings' );
			
			// Set the payment gateway - PayPal or none
			if( !empty( $mdjm_paypal_settings['enable_paypal'] ) )
				$mdjm_payment_settings['payment_gateway'] = 'paypal'; // PayPal is the gateway
				
			else
				$mdjm_payment_settings['payment_gateway'] = false;
			
			// Remove the enable PayPal setting as no longer required	
			unset( $mdjm_paypal_settings['enable_paypal'] );
			
			// Setup PayFast default options	
			$mdjm_payfast_settings = array(
							'merchant_id'			=> '',
							'merchant_key'			=> '',
							'email_confirmation'	=> get_bloginfo( 'admin_email' ),
							'redirect_pf_success'	=> ( !empty( $mdjm_paypal_settings['redirect_success'] ) ? 
								$mdjm_paypal_settings['redirect_success'] : 'N' ),
								
							'redirect_pf_cancel'	=> ( !empty( $mdjm_paypal_settings['redirect_cancel'] ) ? 
								$mdjm_paypal_settings['redirect_cancel'] : 'N' ),
								
							'payfast_button'		=> 'paynow_basic_logo.gif',
							'enable_pf_sandbox'		=> false,
							'sandbox_merchant_id'	=> '',
							'sandbox_merchant_key'	=> '',
							'payfast_debug'			=> false
							);
							
			// Add the notification to admin on event status change setting
			$mdjm_clientzone_settings['mdjm_clientzone_settings'] = true;
			
			update_option( 'mdjm_paypal_settings', $mdjm_paypal_settings );
			update_option( 'mdjm_payfast_settings', $mdjm_payfast_settings );
			update_option( 'mdjm_payment_settings', $mdjm_payment_settings );
			update_option( 'mdjm_clientzone_settings', $mdjm_clientzone_settings );
			
			$status = get_option( '__mydj_validation' );
			
			if( $status['key'] == 'XXXX' || $status['type'] = 'trial' || time() >= strtotime( $status['expire'] ) )
				add_option( 'mdjm_price_warn', 1, '', 'yes' );
			
			$GLOBALS['mdjm_debug']->log_it( 'Settings Updated' );
						
		} // update_settings
		
		/*
		 * Add new transaction terms
		 *
		 *
		 *
		 */
		function update_terms()	{
			wp_insert_term( 
						__( 'Merchant Fees', 'mobile-dj-manager' ),
						'transaction-types',
						array( __( 'Used to track fees for using payment gateways such as PayPal', 
							'mobile-dj-manager' ) ) );
						
		} // update_terms
		
	} // class MDJM_Upgrade_to_1_2_4
	
	new MDJM_Upgrade_to_1_2_4();