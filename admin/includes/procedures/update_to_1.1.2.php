<?php



/*
 * Update procedures for version 1.2.1
 *
 *
 *
 */
 
/* -- Update the settings -- */
	$settings = get_option( MDJM_SETTINGS_KEY );
	$permissions = get_option( MDJM_PERMISSIONS_KEY );
	$client_text = get_option( MDJM_CUSTOM_TEXT_KEY );
	$payment_settings = get_option( 'mdjm_pp_options' );
	$pages = get_option( MDJM_PAGES_KEY );
	$current_client_fields = get_option( MDJM_CLIENT_FIELDS );
	
	if( isset( $settings['title_as_subject'] ) )
		unset( $settings['title_as_subject'] );
	
	$event_settings['event_prefix'] = $settings['id_prefix'];
	unset( $settings['id_prefix'] );

/* -- Permissions -- */
	if( isset( $permissions['dj_see_wp_dash'] ) )
		$permissions['dj_see_wp_dash'] = true;
		
	if( isset( $permissions['dj_add_client'] ) )
		$permissions['dj_add_client'] = true;
		
	if( isset( $permissions['dj_add_event'] ) )
		$permissions['dj_add_event'] = true;
		
	if( isset( $permissions['dj_view_enquiry'] ) )
		$permissions['dj_view_enquiry'] = true;
		
	if( isset( $permissions['dj_add_venue'] ) )
		$permissions['dj_add_venue'] = true;
		
	if( isset( $permissions['dj_see_deposit'] ) )
		$permissions['dj_see_deposit'] = true;
				
/* -- Event Settings -- */
	if( isset( $settings['warn_unattended'] ) )	{
		$event_settings['warn_unattended'] = true;
		unset( $settings['warn_unattended'] );
	}
	
	if( isset( $settings['multiple_dj'] ) )	{
		$event_settings['employer'] = true;
		unset( $settings['multiple_dj'] );
	}
	
	if( isset( $settings['enable_packages'] ) )	{
		$event_settings['enable_packages'] = true;
		unset( $settings['enable_packages'] );
	}
	
	$event_settings['artist'] = $settings['artist'];
	unset( $settings['artist'] );
	
	$event_settings['default_contract'] = $settings['default_contract'];
	unset( $settings['default_contract'] );
	
	$event_settings['enquiry_sources'] = $settings['enquiry_sources'];
	unset( $settings['enquiry_sources'] );
	
	if( isset( $settings['journaling'] ) )	{
		$event_settings['journaling'] = true;
		unset( $settings['journaling'] );
	}
	
/* -- Playlist Settings -- */
	$playlist_settings['playlist_cats'] = $settings['playlist_when'];
	unset( $settings['playlist_when'] );
	
	$playlist_settings['close'] = $settings['playlist_close'];
	unset( $settings['playlist_close'] );
	
	if( isset( $settings['upload_playlists'] ) )	{
		$playlist_settings['upload_playlists'] = true;
		unset( $settings['upload_playlists'] );
	}
	
/* -- Email Settings -- */
	$email_settings['system_email'] = $settings['system_email'];
	unset( $settings['system_email'] );
	
	if( isset( $settings['track_client_emails'] ) )	{
		$email_settings['track_client_emails'] = true;
		unset( $settings['track_client_emails'] );
	}
	
	if( isset( $settings['bcc_dj_to_client'] ) )	{
		$email_settings['bcc_dj_to_client'] = true;
		unset( $settings['bcc_dj_to_client'] );
	}
	
	if( isset( $settings['bcc_admin_to_client'] ) )	{
		$email_settings['bcc_admin_to_client'] = true;
		unset( $settings['bcc_admin_to_client'] );
	}
		
/* -- Email Templates -- */
	$email_templates['enquiry'] = $settings['email_enquiry'];
	unset( $settings['email_enquiry'] );
	
	$email_templates['unavailable'] = $settings['unavailable_email_template'];
	unset( $settings['unavailable_email_template'] );
	
	$email_templates['enquiry_from'] = $settings['enquiry_email_from'];
	unset( $settings['enquiry_email_from'] );
	
	if( isset( $settings['contract_to_client'] ) )	{
		$email_templates['contract_to_client'] = true;
		unset( $settings['contract_to_client'] );
	}
	
	$email_templates['contract'] = $settings['email_contract'];
	unset( $settings['email_contract'] );
	
	$email_templates['contract_from'] = $settings['contract_email_from'];
	unset( $settings['contract_email_from'] );
	
	if( isset( $settings['booking_conf_to_client'] ) )	{
		$email_templates['booking_conf_to_client'] = true;
		unset( $settings['booking_conf_to_client'] );
	}
	
	$email_templates['booking_conf_client'] = $settings['email_client_confirm'];
	unset( $settings['email_client_confirm'] );
	
	$email_templates['booking_conf_from'] = $settings['confirm_email_from'];
	unset( $settings['confirm_email_from'] );
	
	if( isset( $settings['booking_conf_to_dj'] ) )	{
		$email_templates['booking_conf_to_dj'] = true;
		unset( $settings['booking_conf_to_dj'] );
	}
	
	$email_templates['booking_conf_dj'] = $settings['email_dj_confirm'];
	unset( $settings['email_dj_confirm'] );
	
	$email_templates['payment_cfm_template'] = $payment_settings['pp_cfm_template'];
	unset( $payment_settings['pp_cfm_template'] );
	
	$email_templates['manual_payment_cfm_template'] = $payment_settings['pp_manual_cfm_template'];
	unset( $payment_settings['pp_manual_cfm_template'] );

/* -- Client Zone Settings -- */
	$clientzone_settings['app_name'] = $settings['app_name'];
	unset( $settings['app_name'] );
	
	$clientzone_settings['pass_length'] = $settings['pass_length'];
	unset( $settings['pass_length'] );
	
	if( isset( $client_text['warn_incomplete_profile'] ) )	{
		$clientzone_settings['notify_profile'] = true;
		unset( $client_text['warn_incomplete_profile'] );
	}
	
	// New for profile page
	$client_text['profile_intro'] = 'Please keep your details up to date as incorrect information may cause problems with your event.';
	$client_text['profile_pass_intro'] = 'To update your password, enter a new password below and confirm your new password. ' . 
								'Leaving these fields blank will keep your current password.';
								
	// New for Contracts page
	$client_text['contract_intro'] = 'Your contract is displayed below and is ready for signing.' . 
							 "\r\n\r\n" . 'Please review its content carefully to ensure accuracy and once you are ready to do so, ' .
							 '<a href="#sign_form">scroll to the bottom</a> of this page to confirm your acceptance of the contractual terms ' . 
							 'and digitally sign the contract.' . "\r\n\r\n" . ' Once you have signed the contract, you will receive a further email from us.';
							
	$client_text['contract_not_ready'] = 'Your contract is not yet ready for signing as you have not indicated that you would like to ' . 
							'proceed with your event. You can do this <a href="{APPLICATION_HOME}">here</a>.' . "\r\n\r\n" . 
							'The client contract is not yet ready for signing as the event status has not been updated to "Awaiting Contract"';
							
	$client_text['contract_signed'] = 'Your signed contract is displayed below for your records';
							
	$client_text['contract_sign_success'] = 'Thank you. Your contract has been successfully signed and your event is now <strong>confirmed</strong>.' . 
											"\r\n\r\n" . 'A confirmation email is on it\'s way to you';
							
	
/* -- Availability Checker -- */
	$availability['availability_check_pass_page'] = $pages['availability_check_pass_page'];
	$availability['availability_check_pass_text'] = $pages['availability_check_pass_text'];
	$availability['availability_check_fail_page'] = $pages['availability_check_fail_page'];
	$availability['availability_check_fail_text'] = $pages['availability_check_fail_text'];
	
	unset( $pages['availability_check_pass_page'],
		   $pages['availability_check_pass_text'],
		   $pages['availability_check_fail_page'],
		   $pages['availability_check_fail_text'] );
	
/* -- Payment Settings -- */
	$payments['currency'] = $settings['currency'];
	$payments['currency_format'] = 'before';
	$payments['decimal'] = '.';
	$payments['thousands_seperator'] = ',';
	$payments['deposit_label'] = $client_text['deposit_label'];
	$payments['balance_label'] = $client_text['balance_label'];
	
	$payments['default_type'] = $payment_settings['pp_default_method'];
	$payments['form_layout'] = $payment_settings['pp_form_layout'];
	$payments['payment_label'] = $payment_settings['pp_label'];
	
	if( isset( $payment_settings['pp_enable_tax'] ) )
		$payments['enable_tax'] = true;
		
	$payments['tax_type'] = $payment_settings['pp_tax_type'];
	$payments['tax_rate'] = $payment_settings['pp_tax_rate'];
	$payments['payment_sources'] = $payment_settings['pp_payment_sources'];
	
/* -- PayPal Settings -- */
	if( isset( $payment_settings['pp_enable'] ) )
		$paypal['enable_paypal'] = true;
	
	$paypal['paypal_email'] = $payment_settings['pp_email'];
	$paypal['redirect_success'] = $payment_settings['pp_redirect'];
	$paypal['redirect_cancel'] = $payment_settings['pp_cancel'];
	$paypal['paypal_button'] = $payment_settings['pp_button'];
	
	if( isset( $payment_settings['pp_sandbox'] ) )
		$paypal['enable_sandbox'] = true;
		
	$paypal['sandbox_email'] = $payment_settings['pp_sandbox_email'];
	
	if( isset( $payment_settings['pp_debug'] ) )
	$paypal['paypal_debug'] = true;
	
	$paypal['receiver_email'] = $payment_settings['pp_receiver'];
	
	if( isset( $payment_settings['pp_checkout_style'] ) )
		$paypal['checkout_style'] = $payment_settings['pp_checkout_style'];
	
/* -- Debug File Settings -- */
	$debug_settings['log_size'] = '2';
	$debug_settings['auto_purge'] = true;
	
/* -- Uninstallation Settings -- */
	if( isset( $settings['uninst_remove_db'] ) )	{
		$uninst_settings['uninst_remove_db'] = true;
		unset( $settings['uninst_remove_db'] );
	}
	if( isset( $settings['uninst_remove_mdjm_templates'] ) )	{
		$uninst_settings['uninst_remove_mdjm_templates'] = true;
		unset( $settings['uninst_remove_mdjm_templates'] );
	}
	
/* -- Client Fields -- */
	$client_fields = array(
						'first_name' => array(
							'label' => 'First Name',
							'id' => 'first_name',
							'type' => 'text',
							'value' => '',
							'checked' => '0',
							'display' => '1',
							'required' => '1',
							'desc' => '',
							'default' => '1',
							'position' => '0',
							),
						'last_name' => array(
							'label' => 'Last Name',
							'id' => 'last_name',
							'type' => 'text',
							'value' => '',
							'checked' => '0',
							'display' => '1',
							'required' => '1',
							'desc' => '',
							'default' => '1',
							'position' => '1',
							),
						'user_email' => array(
							'label' => 'Email Address',
							'id' => 'user_email',
							'type' => 'text',
							'value' => '',
							'checked' => '0',
							'display' => '1',
							'required' => '1',
							'desc' => '',
							'default' => '1',
							'position' => '2',
							),
						);
	
	$i = 3;					
	foreach( $current_client_fields as $field )	{
		if( !empty( $field['value'] ) )	{
			if( $field['type'] == 'dropdown' )
				$value = str_replace( ',', "\r\n", $field['value'] );
			else
				$value = $field['value'];
		}
		$client_fields[$field['id']] = array(
										'label' => $field['label'],
										'id' => $field['id'],
										'type' => $field['type'],
										'value' => ( !empty( $value ) ? $value : '' ),
										'checked' => ( isset( $field['checked'] ) && $field['checked'] == 'Y' ? '1' : '0' ),
										'display' => ( isset( $field['display'] ) && $field['display'] == 'Y' ? '1' : '0' ),
										'required' => ( isset( $field['required'] ) && $field['required'] == 'Y' ? '1' : '0' ),
										'desc' => ( !empty( $field['desc'] ) ? $field['desc'] : '' ),
										'default' => ( isset( $field['default'] ) && $field['default'] == 'Y' ? '1' : '0' ),
										'position' => $i,
										);
		$i++;
	}
	
	/* -- Update the options table -- */
	update_option( MDJM_SETTINGS_KEY, $settings );
	update_option( MDJM_PERMISSIONS_KEY, $permissions );
	update_option( MDJM_EVENT_SETTINGS_KEY, $event_settings );
	update_option( MDJM_PLAYLIST_SETTINGS_KEY, $playlist_settings );
	update_option( MDJM_EMAIL_SETTINGS_KEY, $email_settings );
	update_option( MDJM_TEMPLATES_SETTINGS_KEY, $email_templates );
	update_option( MDJM_CLIENTZONE_SETTINGS_KEY, $clientzone_settings );
	update_option( MDJM_CUSTOM_TEXT_KEY, $client_text );
	update_option( MDJM_AVAILABILITY_SETTINGS_KEY, $availability );
	update_option( MDJM_DEBUG_SETTINGS_KEY, $debug_settings );
	update_option( MDJM_PAGES_KEY, $pages );
	
	if( !empty( $uninst_settings ) )
		update_option( MDJM_UNINST_SETTINGS_KEY, $uninst_settings );
		
	update_option( MDJM_PAYMENTS_KEY, $payments );
	update_option( MDJM_PAYPAL_KEY, $paypal );
	update_option( MDJM_CLIENT_FIELDS, $client_fields );
	delete_option( 'mdjm_pp_options' );
 

?>