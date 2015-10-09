<?php

/**
 * * * * * * * * * * * * * * * MDJM * * * * * * * * * * * * * * *
 * This file is loaded immediately
 *
 *
 * @since 1.0
 *
 */
 
/**** THIS FILE IS DEFUNCT SINCE 1.2.3
	  REMOVE 5 VERSIONS AFTER 1.2.3

/****************************************************************************************************
 *	INSTALLATION & INITIALISATION
 ***************************************************************************************************/

/**
 * f_mdjm_upgrade
 * Determine if any upgrade procedures are required
 * 
 * Called from: add_action
 * @since 1.0
*/	
	function f_mdjm_upgrade()	{
		global $mdjm;
		
		$current_version_mdjm = get_option( MDJM_VERSION_KEY );
		
		if( !empty( $current_version_mdjm ) && MDJM_VERSION_NUM >= '1.2.3' )
			return;
		
		if( !get_option( MDJM_VERSION_KEY ) ) // Add application version to the DB if not already there
			add_option( MDJM_VERSION_KEY, '0.9.2' ); // Add the previous version to which this upgrade proc was introduced	
		
		if( MDJM_VERSION_NUM > $current_version_mdjm )	{ // We have some upgrades to perform
			$mdjm->debug_logger( 'UPGRADE REQUIRED', true );
			
			if( !get_option( MDJM_UPDATED_KEY ) ) // Add the option to show we've updated 
				add_option( MDJM_UPDATED_KEY, '1' );
			
			/*if( $current_version_mdjm < MDJM_UNSUPPORTED )	{
				return mdjm_update_notice( 'error',
										   'ERROR: Upgrading from your version of the MDJM plugin is not supported<br />' . 
										   'In order to continue using the MDJM plugin, you will need to uninstall your version ' . 
										   'and re-install the latest version manually.<br />' . 
										   '<a href="http://www.mydjplanner.co.uk/release-cycles-version-support/" target="_blank">More Information</a>' );
			}*/
/***************************************************
			 	UPGRADES FROM 1.0
***************************************************/			
			if( $current_version_mdjm <= '1.0' )	{
				$mdjm_options = get_option( MDJM_SETTINGS_KEY );
				$mdjm_init_pages = get_option( MDJM_PAGES_KEY );
				$mdjm_frontend_text = get_option( MDJM_CUSTOM_TEXT_KEY );
								
				/* Set new PayPal options */
				$mdjm_pp_options = array(
								'pp_cfm_template'   => '',
								'pp_form_layout'	=> 'horizontal',
								'pp_label'		  => 'Make a Payment Towards:',
								'pp_tax'			=> 'N',
								'pp_tax_type'	   => 'percentage',
								'pp_tax_rate'	   => '20',
								'pp_enable'		 => 'N',
								'pp_email'		  => $mdjm_options['system_email'],
								'pp_redirect'	   => '',
								'pp_button'		 => 'btn_paynow_SM.gif',
								'pp_sandbox'		=> 'N',
								'pp_sandbox_email'  => $mdjm_options['system_email'],
								'pp_debug'		  => 'Y',
								'pp_receiver'	   => $mdjm_options['system_email'],
								//'pp_inv_prefix'	 => $mdjm_options['id_prefix'] . '-',
								'pp_checkout_style' => '',
								);
				/* Update Pages Options */
				$mdjm_init_pages['payments_page'] = '';
				
				/* Update Client Text Options */
				$mdjm_frontend_text['deposit_label'] = 'Deposit';
				$mdjm_frontend_text['balance_label'] = 'Balance';
				$mdjm_frontend_text['payment_welcome'] = 'Paying for your event is easy as we accept secure online payments via PayPal.' . "\r\n\r\n" . 'PayPal accept all major credit cards and you do not need to be a PayPal member to process your payment to us';
				
				$mdjm_frontend_text['payment_intro'] = 'Select the payment you wish to make from the drop down list below and click the <strong>Pay Now</strong> button to be redirected to <a title="PayPal" href="https://www.paypal.com" target="_blank">PayPal\'s</a> secure website where you can complete your payment.' . "\r\n\r\n" . 'Upon completion, you can return to the {COMPANY_NAME} website. You will also receive an email as soon as your payment completes.';
				
				$mdjm_frontend_text['payment_complete'] = 'Thank you, your payment has completed successfully.' . "\r\n\r\n" . 'You will shortly receive an email from us (remember to check your junk email folder) confirming the payment and detailing next steps for your event.' . "\r\n\r\n" . '<strong>Please note</strong> that it can take a few minutes for our systems to be updated by <a title="PayPal" href="https://www.paypal.com" target="_blank">PayPal</a>, and therefore your payment may not have registered below as yet. Once you receive the payment confirmation email from us, the payment will be updated on our systems.' . "\r\n\r\n" . '<a href="{APPLICATION_HOME}">Click here</a> to return to the <a href="{APPLICATION_HOME}">{APPLICATION_NAME}</a> home page.';
				
				$mdjm_frontend_text['payment_cancel'] = 'Your payment has been cancelled.' . "\r\n\r\n" . 'To process your payment, please follow the steps below.';
				
				$mdjm_frontend_text['payment_not_due'] = 'There are no payments outstanding for this event. If you believe this is an error, please <a href="{CONTACT_PAGE}">contact us</a>.' . "\r\n\r\n" . 'Otherwise, <a href="{APPLICATION_HOME}">Click here</a> return to the <a href="{APPLICATION_HOME}">{APPLICATION_NAME}</a> home page.';
				
				$mdjm_frontend_text['payment_noevent'] = 'No event has been selected for payment. <a href="{APPLICATION_HOME}">Click here</a> return to the <a href="{APPLICATION_HOME}">{APPLICATION_NAME}</a> home page.';
				
				$mdjm_frontend_text['payment_noaccess'] = 'We\'re sorry but you do not have permission to access this page. If you believe this is an error, please <a href="{CONTACT_PAGE}">contact us</a>..' . "\r\n\r\n" . 'Otherwise, <a href="{APPLICATION_HOME}">Click here</a> return to the <a href="{APPLICATION_HOME}">{APPLICATION_NAME}</a> home page.';
				
				/* Add / Update Options */
				add_option( MDJM_PAYMENTS_KEY, $mdjm_pp_options );
				update_option( MDJM_SETTINGS_KEY, $mdjm_options );
				update_option( MDJM_PAGES_KEY, $mdjm_init_pages );
				update_option( MDJM_FETEXT_SETTINGS_KEY, $mdjm_frontend_text );
			} // if( $current_version_mdjm <= '1.0' )
			
/***************************************************
			 	UPGRADES FROM 1.1
***************************************************/			
			if( $current_version_mdjm <= '1.1' )	{
				$mdjm_options = get_option( MDJM_SETTINGS_KEY );
				$mdjm_pp_options = get_option( MDJM_PAYMENTS_KEY );
				
				/* -- Add the new payment options -- */
				$mdjm_pp_options['pp_payment_sources'] = "BACS\r\nCash\r\nCheque\r\nPayPal\r\nOther";
				$mdjm_pp_options['pp_transaction_types'] = "Certifications\r\nHardware\r\nInsurance\r\nMaintenance\r\nMusic\r\nParking\r\nPetrol\r\nSoftware\r\nVehicle";

				/* Add the Uninstall Contract/Email Templates Option */
				$mdjm_options['uninst_remove_mdjm_templates'] = 'N';
				
				/* Add / Update Options */
				update_option( MDJM_PAYMENTS_KEY, $mdjm_pp_options );
				update_option( MDJM_SETTINGS_KEY, $mdjm_options );
			} // if( $current_version_mdjm <= '1.1' )
			
/***************************************************
			 	UPGRADES FROM 1.1.1
***************************************************/			
			if( $current_version_mdjm <= '1.1.1' )	{
				$mdjm_options = get_option( MDJM_SETTINGS_KEY );

				/* Add the Email Tracking Option */
				$mdjm_options['track_client_emails'] = 'Y';
				
				/* -- Copy the venues -- */
				$venue_count = wp_count_posts( MDJM_VENUE_POSTS )->publish;
		
				/* Add / Update Options */
				update_option( MDJM_SETTINGS_KEY, $mdjm_options );
			} // if( $current_version_mdjm <= '1.1.1' )
/***************************************************
			 	UPGRADES FROM 1.1.2
***************************************************/			
			if( $current_version_mdjm <= '1.1.2' )	{
				// No actions
			} // if( $current_version_mdjm <= '1.1.2' )
			
/***************************************************
			 	UPGRADES FROM 1.1.3
***************************************************/			
			if( $current_version_mdjm <= '1.1.3.1' )	{
				// No actions
			} // if( $current_version_mdjm <= '1.1.3.1' )
			
/***************************************************
			 	UPGRADES FROM 1.1.3.2
***************************************************/			
			if( $current_version_mdjm <= '1.1.3.3' )	{
				// No actions
			} // if( $current_version_mdjm <= '1.1.3.3' )
/***************************************************
			 	UPGRADES FROM 1.1.3.3
***************************************************/			
			if( $current_version_mdjm < '1.2' )	{
				add_option( 'mdjm_update', '1.2' ); // Add option to tell updated.php we have tasks to complete
				// No actions everything is completed in updated.php
			} // if( $current_version_mdjm <= '1.2' )
			
/***************************************************
			 	UPGRADES FROM 1.2
***************************************************/			
			if( $current_version_mdjm < '1.2.1' )	{
				add_option( 'mdjm_update_me', '1.2.1' ); // Add option to tell updated.php we have tasks to complete
			} // if( $current_version_mdjm <= '1.2.1' )
/***************************************************
			 	UPGRADES FROM 1.2.1
***************************************************/			
			if( $current_version_mdjm < '1.2.2' )	{
				add_option( 'mdjm_update_me', '1.2.2' ); // Add option to tell updated.php we have tasks to complete
			} // if( $current_version_mdjm <= '1.2.2' )

/***************************************************
THESE SETTINGS APPLY TO ALL UPDATES - DO NOT ADJUST
***************************************************/
						
			/* Delete the template file */
			//unlink( WPMDJM_PLUGIN_DIR . '/admin/includes/mdjm-templates.php' );
			
			/* Update the version number */
			update_option( MDJM_VERSION_KEY, MDJM_VERSION_NUM );
			
			/* Make sure release notes are displayed */
			update_option( MDJM_UPDATED_KEY, '1' );
									
			mdjm_update_notice( 'updated',
								'Welcome to Mobile DJ Manager for WordPress version ' . MDJM_VERSION_NUM . 
								'. Click on one of the Mobile DJ Manager menu items to view the release notes.' );
			
		} // if( WPMDJM_VERSION_NUM > $current_version_mdjm )
	} // f_mdjm_upgrade


/**
 * f_mdjm_init
 * Regularly called named constants
 * 
 * Called from: mobile-dj-manager.php
 * @since 1.0
*/
/***** TO BE DEPRECATED *****/
	function f_mdjm_init()	{
		require_once( MDJM_PLUGIN_DIR . '/includes/functions.php' );
		$mdjm_options = f_mdjm_get_options();
		define( 'WPMDJM_CLIENT_FIELDS', 'mdjm_client_fields' );
		define( 'WPMDJM_CREDITS', !empty( $mdjm_options['show_credits'] ) ? $mdjm_options['show_credits'] : '' );
		define( 'WPMDJM_CO_NAME', !empty( $mdjm_options['company_name'] ) ? $mdjm_options['company_name'] : '' );
		define( 'WPMDJM_APP_NAME', !empty( $mdjm_options['app_name'] ) ? $mdjm_options['app_name'] : '' );
		define( 'WPDJM_JOURNAL', !empty( $mdjm_options['journaling'] ) ? $mdjm_options['journaling'] : '' );
		define( 'WPMDJM_CLIENT_HOME_PAGE', !empty( $mdjm_options['app_home_page'] ) ? $mdjm_options['app_home_page'] : '' );
		define( 'WPMDJM_CONTACT_PAGE', !empty( $mdjm_options['contact_page'] ) ? $mdjm_options['contact_page'] : '' );
		define( 'WPMDJM_CLIENT_CONTRACT_PAGE', !empty( $mdjm_options['contracts_page'] ) ? $mdjm_options['contracts_page'] : '' );
		define( 'WPMDJM_CLIENT_PLAYLIST_PAGE', !empty( $mdjm_options['playlist_page'] ) ? $mdjm_options['playlist_page'] : '' );
		define( 'WPMDJM_CLIENT_PROFILE_PAGE', !empty( $mdjm_options['profile_page'] ) ? $mdjm_options['profile_page'] : '' );
		define( 'WPMDJM_CLIENT_PAYMENT_PAGE', !empty( $mdjm_options['payments_page'] ) ? $mdjm_options['payments_page'] : '' );
		
		return $mdjm_options;
	} // f_mdjm_init
	
/*
* f_mdjm_has_updated
* 23/11/2014
* @since 0.9.3
* Checks for upgrade and displays the upgrade notice
*/
	function f_mdjm_has_updated()	{
		global $mdjm;
		$mdjm->debug_logger( 'WARNING: Use of deprecated function ' . __FUNCTION__, true );
		$updated = get_option( 'mdjm_updated' );
		if( !empty( $updated ) && $updated == '1' )	{
			$_GET['updated'] = '1';
			include( MDJM_PLUGIN_DIR .  '/admin/pages/updated.php' );
			exit;
		}
	} // f_mdjm_has_updated
	
	
?>