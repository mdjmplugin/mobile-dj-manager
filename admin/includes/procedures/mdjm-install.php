<?php
/*
 * mdjm-install.php
 * 16/05/2015
 * @since 1.1.3
 * The installation procedures for MDJM
 */
	class MDJM_Install	{
		public function __construct()	{
			global $mdjm, $upgrade_error;
			
			/* -- Use the WP Error Class -- */
			$this->upgrade_error = new WP_Error;
			
			// Database
			$this->install_db();
						
			// Default settings
			$this->default_settings();
			
			// Pages
			$this->install_pages();
			
			// Posts
			$this->install_templates();
						
			// Terms
			$this->set_mdjm_terms();
			
		} // __construct

		/*
		 * Install the default settings for MDJM
		 *
		 *
		 *
		 */
		public function default_settings()	{
			global $mdjm;
			$enquiry_sources = 'Website' . "\r\n" . 
								'Google' . "\r\n" . 
								'Facebook' . "\r\n" . 
								'Email' . "\r\n" . 
								'Telephone' . "\r\n" . 
								'Other';
			
			$payment_sources = 'BACS' . "\r\n" . 
								'Cash' . "\r\n" . 
								'Cheque' . "\r\n" . 
								'PayPal' . "\r\n" . 
								'Other';
								
			$playlist_options = 'General' . "\r\n" . 
								'First Dance' . "\r\n" . 
								'Second Dance' . "\r\n" . 
								'Last Song' . "\r\n" . 
								'Father & Bride' . "\r\n" . 
								'Mother & Son' . "\r\n" . 
								'DO NOT PLAY\r\nOther';
			
			$default_settings = array( 
						'mdjm_plugin_settings' => array(
							'company_name'            		=> get_bloginfo( 'name' ),
							'artist'						  => 'DJ',
							'items_per_page'          		  => get_option( 'posts_per_page' ),
							'time_format'            		 => 'H:i',
							'short_date_format'       		   => 'd/m/Y',
							'show_dashboard'          		  => true,
							'show_credits'            		=> false,
						),
						'mdjm_email_settings' => array(
							'system_email'            		=> get_bloginfo( 'admin_email' ),
							'track_client_emails'			 => true,
							'bcc_dj_to_client'        		=> false,
							'bcc_admin_to_client'     		 => true,
						),
						'mdjm_event_settings' => array(
							'event_prefix'               		=> 'MDJM',
							'employer'             		 	=> false,
							'enable_packages'				 => false,
							'default_contract'        		=> 'N',
							'warn_unattended'         		 => true,
							'enquiry_sources'         		 => $enquiry_sources,
							'journaling'              		  => true,
						),
						'mdjm_playlist_settings' => array(
							'close'		           		   => '5',
							'playlist_cats'				   => $playlist_options,
							'upload_playlists'        		=> true,
						),
						'mdjm_templates_settings' => array(
							'enquiry'           		   		 => 'N',
							'online_enquiry'				  => '0',
							'unavailable'				     => 'N',
							'enquiry_from'      		  		=> 'admin',
							'contract_to_client'      		  => true,
							'contract'          		  		=> 'N',
							'contract_from'     		 	   =>'admin',
							'booking_conf_to_client' 		  => true,
							'booking_conf_client'    	  	 => 'N',
							'booking_conf_from'      		   => 'admin',
							'booking_conf_to_dj'     		  => false,
							'email_dj_confirm'        		=> 'N',
							'payment_cfm_template'   			=> 'N',
							'manual_payment_cfm_template' 	 => '0',
						),
						'mdjm_plugin_permissions' => array(
							'dj_see_wp_dash'             => true,
							'dj_add_event'               => false,
							'dj_view_enquiry'            => false,
							'dj_add_venue'               => false,
							'dj_add_client'              => false,
							'dj_disable_shortcode'       => array( '{ADMIN_NOTES}', '{DEPOSIT_AMOUNT}' ),
							'dj_disable_template'		=> '',
						),
						'mdjm_clientzone_settings' => array(
							'app_name'                	=> 'Client Zone',
							'pass_length'             	 => '8',
							'notify_profile' 			  => true,
							'package_prices'			  => false,
							'update_event'				=> false,
							'edit_event_stop'			  => '5',	
						),
						'mdjm_plugin_pages' => array(
							'app_home_page'                => 'N',
							'contact_page'                 => 'N',
							'contracts_page'               => 'N',
							'payments_page'				=> 'N',
							'playlist_page'                => 'N',
							'profile_page'                 => 'N',
							'quotes_page'                  => 'N',
						),
						'mdjm_availability_settings' => array(
							'availability_check_pass_page' => 'text',
							'availability_check_pass_text' => 'Good news, we are available on the date you entered. Please contact us now',
							'availability_check_fail_page' => 'text',
							'availability_check_fail_text' => 'Unfortunately we do not appear to be available on the date you selected. Why not try another date below...',
						),
						
						'mdjm_client_fields' => array(
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
							'address1' => array(
								'label' => 'Address 1',
								'id' => 'address1',
								'type' => 'text',
								'value' => '',
								'checked' => '0',
								'display' => '1',
								'required' => '1',
								'desc' => '',
								'default' => '1',
								'position' => '3',
								),
							'address2' => array(
								'label' => 'Address 2',
								'id' => 'address2',
								'type' => 'text',
								'value' => '',
								'checked' => '0',
								'display' => '1',
								'required' => '0',
								'desc' => '',
								'default' => '1',
								'position' => '4',
								),
							'town' => array(
								'label' => 'Town / City',
								'id' => 'town',
								'type' => 'text',
								'value' => '',
								'checked' => '0',
								'display' => '1',
								'required' => '1',
								'desc' => '',
								'default' => '1',
								'position' => '5',
								),
							'county' => array(
								'label' => 'County',
								'id' => 'county',
								'type' => 'text',
								'value' => '',
								'checked' => '0',
		
								'display' => '1',
								'required' => '1',
								'desc' => '',
								'default' => '1',
								'position' => '6',
								),
							'postcode' => array(
								'label' => 'Post Code',
								'id' => 'postcode',
								'type' => 'text',
								'value' => '',
								'checked' => '0',
								'display' => '1',
		
								'required' => '1',
								'desc' => '',
								'default' => '1',
								'position' => '7',
								),
							'phone1' => array(
								'label' => 'Primary Phone',
								'id' => 'phone1',
								'type' => 'text',
								'value' => '',
								'checked' => '0',
								'display' => '1',
								'required' => '1',
								'desc' => '',
								'default' => '1',
								'position' => '8',
								),
							'phone2' => array(
								'label' => 'Alternative Phone',
								'id' => 'phone2',
								'type' => 'text',
								'value' => '',
								'checked' => '0',
								'display' => '1',
								'desc' => '',
								'default' => '1',
								'position' => '9',
								),
							'birthday' => array(
								'label' => 'Birthday',
								'id' => 'birthday',
								'type' => 'dropdown',
								'value' => 'January' . "\r\n" . 
										   'February' . "\r\n" .
										   'March' . "\r\n" . 
										   'April' . "\r\n" . 
										   'May' . "\r\n" . 
										   'June' . "\r\n" . 
										   'July' . "\r\n" . 
										   'August' . "\r\n" . 
										   'September' . "\r\n" . 
										   'October' . "\r\n" . 
										   'November' . "\r\n" . 
										   'December',
								'checked' => '0',
								'display' => '1',
								'desc' => '',
								'default' => '1',
								'position' => '10',
								),
							'marketing' => array(
								'label' => 'Marketing Info?',
								'id' => 'marketing',
								'type' => 'checkbox',
								'value' => '1',
								'checked' => ' checked',
								'display' => '1',
								'desc' => 'Do we add the user to the mailing list?',
								'default' => '1',
								'position' => '11',
								),
						),
						'mdjm_payment_settings' => array(
							'currency'			   => 'GBP',
							'currency_format'		=> 'before',
							'decimal'				=> '.',
							'thousands_seperator'	=> ',',
							'deposit_type'		   => 'fixed',
							'deposit_amount'		 => '50.00',
							'deposit_label'		  => 'Deposit',
							'balance_label'		  => 'Balance',
							'default_type'		   => 'Cash',
							'form_layout'		 	=> 'horizontal',
							'payment_label'		  => 'Make a Payment Towards:',
							'enable_tax'			 => 'N',
							'tax_type'	   		   => 'percentage',
							'tax_rate'	   		   => '20',
							'payment_sources'	 	=> $payment_sources,
						),
						'mdjm_paypal_settings' 	=> array(
							'enable_paypal'	   => false,
							'paypal_email'		=> get_bloginfo( 'admin_email' ),
							'redirect_success'	=> 'N',
							'redirect_cancel'	 => 'N',
							'paypal_button'	   => 'btn_paynow_SM.gif',
							'enable_sandbox'	  => false,
							'sandbox_email'  	   => get_bloginfo( 'admin_email' ),
							'paypal_debug'		=> false,
							'receiver_email'	  => get_bloginfo( 'admin_email' ),
							'checkout_style' 	  => '',
						),
						'mdjm_debug_settings' => array(
							'enable'		=> false,
							'log_size'	  => '2',
							'auto_purge'	=> true,
						),
						'mdjm_uninst' => array(
							'uninst_remove_db'        		=> true,
							'uninst_remove_mdjm_posts'		=> true,
							'uninst_remove_mdjm_pages'		=> true,
							'uninst_remove_mdjm_templates'	=> true,
							'uninst_remove_mdjm_users'		=> true,
						),
						'mdjm_frontend_text' => array(
							'custom_client_text'      => false,
							'not_logged_in'           => 'You must be logged in to enter this area of the website.' . "\r\n\r\n" . 
								'Please enter your username and password below to continue, or use the menu items above to navigate to another page.',
								
							'home_welcome'            => 'Hello {CLIENT_FIRSTNAME} and welcome to the <a href="{APPLICATION_HOME}">{COMPANY_NAME}</a> {APPLICATION_NAME}.',
							'home_noevents'           => 'You currently have no upcoming events. Please <a title="Contact {COMPANY_NAME}" href="{CONTACT_PAGE}">' . 
								'contact me</a> now to start planning your next disco.',
								
							'home_notactive'          => 'The selected event is no longer active. ' . 
								'<a href="{CONTACT_PAGE}" title="Begin planning your next event with us">Contact us now</a> begin planning your next event.',
							
							'profile_intro'		   => 'Please keep your details up to date as incorrect information may cause problems with your event.',
							
							'profile_pass_intro'	  => 'To update your password, enter a new password below and confirm your new password. ' . 
								'Leaving these fields blank will keep your current password.',
								
							'playlist_welcome'        => 'Welcome to the {COMPANY_NAME} event playlist management system.',
							'playlist_intro'          => 'Use this tool to let your DJ know the songs that you would like played (or perhaps not played) ' . 
								'during your event on <strong> {EVENT_DATE}</strong>.' . "\r\n\r\n" . 
								'Invite your friends to add their song choices to this playlist too by sending them your unique event URL - ' . 
								'<a href="{GUEST_PLAYLIST_URL}" target="_blank">{GUEST_PLAYLIST_URL}</a>.' . "\r\n\r\n" . 
								'You can view and remove any songs added by your guests below.',
								
							'playlist_edit'           => 'You are currently editing the playlist for your event on {EVENT_DATE}. ' . "\r\n\r\n" . 
								'To edit the playlist for one of your other events, return to the <a href="{APPLICATION_HOME}">{APPLICATION_NAME} home page</a> ' . 
								'and select Edit Playlist from the drop down list displayed next to the event for which you want to edit the playlist.',
								
							'playlist_closed'         => '<strong>Additions to your playlist are disabled as your event is taking place soon</strong>',
							'playlist_noevent'        => 'You do not have any confirmed events with us. The Playlist is only available once you have ' . 
								'confirmed your event and signed your contract.' . "\r\n\r\n" . 
								'To begin planning your next event with us, please <a href="{CONTACT_PAGE}">contact us now</a>',
								
							'playlist_guest_welcome'  => 'Welcome to the {COMPANY_NAME} playlist management system.',
							'playlist_guest_intro'    => 'You are adding songs to the playlist for {CLIENT_FIRSTNAME} {CLIENT_LASTNAME}\'s event on {EVENT_DATE}.' . 
								"\r\n\r\n" . 'Add your playlist requests in the form below. All fields are required.',
								
							'playlist_guest_closed'   => 'This playlist is currently closed. No songs can be added at this time.',
							'contract_intro'		=> 'Your contract is displayed below and is ready for signing.' . 
							 "\r\n\r\n" . 'Please review its content carefully to ensure accuracy and once you are ready to do so, <a href="#sign_form">scroll to the bottom</a> of this page to confirm your acceptance of the contractual terms and digitally sign the contract.' . "\r\n\r\n" . ' Once you have signed the contract, you will receive a further email from us.',
							
							'contract_not_ready'	=> 'Your contract is not yet ready for signing as you have not indicated that you would like to proceed with your event. You can do this <a href="{APPLICATION_HOME}">here</a>.' . "\r\n\r\n" . 'The client contract is not yet ready for signing as the event status has not been updated to "Awaiting Contract"',
							
							'contract_signed'		=> 'Your signed contract is displayed below for your records',
							
							'contract_sign_success'	=> 'Thank you. Your contract has been successfully signed and your event is now <strong>confirmed</strong>.' . "\r\n\r\n" . 'A confirmation email is on it\'s way to you',
							
							'payment_welcome'		 => 'Paying for your event is easy as we accept secure online payments via PayPal.' . "\r\n\r\n" . 
								'PayPal accept all major credit cards and you do not need to be a PayPal member to process your payment to us',
			
							'payment_intro'		   => 'Select the payment you wish to make from the drop down list below and click the ' . 
								'<strong>Pay Now</strong> button to be redirected to <a title="PayPal" href="https://www.paypal.com" target="_blank">PayPal\'s</a> ' . 
								'secure website where you can complete your payment.' . "\r\n\r\n" . 
								'Upon completion, you can return to the {COMPANY_NAME} website. You will also receive an email as soon as your payment completes.',
							
							'payment_complete'		=> 'Thank you, your payment has completed successfully.' . "\r\n\r\n" . 
								'You will shortly receive an email from us (remember to check your junk email folder) confirming the payment and detailing ' . 
								'next steps for your event.' . "\r\n\r\n" . 
								'<strong>Please note</strong> that it can take a few minutes for our systems to be updated by ' . 
								'<a title="PayPal" href="https://www.paypal.com" target="_blank">PayPal</a>, and therefore your payment may not have ' . 
								'registered below as yet. Once you receive the payment confirmation email from us, the payment will be updated on our systems.' . 
								"\r\n\r\n" . '<a href="{APPLICATION_HOME}">Click here</a> to return to the <a href="{APPLICATION_HOME}">{APPLICATION_NAME}</a> home page.',
			
							'payment_cancel'		  => 'Your payment has been cancelled.' . "\r\n\r\n" . 'To process your payment, please follow the steps below',
							
							'payment_not_due'		 => 'There are no payments outstanding for this event. ' . 
								'If you believe this is an error, please <a href="{CONTACT_PAGE}">contact us</a>.' . "\r\n\r\n" . 
								'Otherwise, <a href="{APPLICATION_HOME}">Click here</a> return to the <a href="{APPLICATION_HOME}">{APPLICATION_NAME}</a> home page.',
							
							'payment_noevent'		 => 'No event has been selected for payment. <a href="{APPLICATION_HOME}">Click here</a> return to the ' . 
								'<a href="{APPLICATION_HOME}">{APPLICATION_NAME}</a> home page.',
			
							'payment_noaccess'		=> 'We\'re sorry but you do not have permission to access this page.' . "\r\n\r\n" . 
								'If you believe this is an error, please <a href="{CONTACT_PAGE}">contact us</a>..' . "\r\n\r\n" . 
								'Otherwise, <a href="{APPLICATION_HOME}">Click here</a> return to the <a href="{APPLICATION_HOME}">{APPLICATION_NAME}</a> home page.',
						) ); // $default_options
			
			/* -- Loop through the default settings array and set the options -- */			
			foreach( $default_settings as $key => $values )	{
				if( !get_option( $key ) )	{
					error_log(  date( 'd M Y H:i:s' ) . ' Adding default options for ' . $key . "\r\n", 3, MDJM_DEBUG_LOG );
					add_option( $key, $values );	
				}
					
				else	{
					error_log(  date( 'd M Y H:i:s' ) . ' Resetting default options for ' . $key . "\r\n", 3, MDJM_DEBUG_LOG );
					update_option( $key, $values );
				}
			}
			/* -- Other options -- */
			$mdjm_settings = array(
								'main'		=> get_option( MDJM_SETTINGS_KEY ),
								'custom_text' => get_option( MDJM_CUSTOM_TEXT_KEY ),
								'pages'	   => get_option( MDJM_PAGES_KEY ),
								'payments'	=> get_option( MDJM_PAYMENTS_KEY ),
								'permissions' => get_option( MDJM_PERMISSIONS_KEY ),
								);
			
			include( WPMDJM_PLUGIN_DIR . '/admin/includes/mdjm-templates.php' );
			add_option( 'mdjm_schedules', $mdjm_schedules );
			add_option( 'mdjm_debug', '0' );
			add_option( 'mdjm_updated', '0' );
			if( !get_option( 'mdjm_version' ) )
				add_option( 'mdjm_version', MDJM_VERSION_NUM );
				
			if( !get_option( 'm_d_j_m_installed' ) )
				add_option( 'm_d_j_m_installed', date( 'Y-m-d H:i:s' ) );
			
			if( !get_option( 'm_d_j_m_initiated' ) )	{
				set_transient( 'mdjm_is_trial', 'XXXX|' . date( 'Y-m-d' ) . '|' . date( 'Y-m-d', strtotime( "+30 days" ) ), 30 * DAY_IN_SECONDS );
				if( get_option( 'has_been_set' ) )
					delete_option( 'has_been_set' );
					
				if( get_option( 'mdjm_is_trial' ) )
					delete_option( 'mdjm_is_trial' );
					
				add_option( 'm_d_j_m_has_initiated', time() );
			}
			
			$status['key'] = 'N/A';
			$status['type'] = 'trial';
			$status['start'] = date( 'Y-m-d' );
			$status['expire'] = date( 'Y-m-d', strtotime( "+30 days" ) );
			$status['last_auth'] = date( 'Y-m-d H:i:s' );
			$status['missed'] = 0;
			
			add_option( '__mydj_validation', $status );
					
		} // default_settings

/*
 * --
 * PAGES & POSTS
 * --
 */
		/*
		 * Install the MDJM Default posts for Contract and Email Templates
		 * Once installed, set the appropriate settings to utilise these templates
		 *
		 *
		 */ 
		public function install_templates()	{
			$mdjm_settings = array(
								'main'		=> get_option( MDJM_SETTINGS_KEY ),
								'custom_text' => get_option( MDJM_CUSTOM_TEXT_KEY ),
								'pages'	   => get_option( MDJM_PAGES_KEY ),
								'payments'	=> get_option( MDJM_PAYMENTS_KEY ),
								'permissions' => get_option( MDJM_PERMISSIONS_KEY ),
								);
								
			include( WPMDJM_PLUGIN_DIR . '/admin/includes/mdjm-templates.php' );
			
			$template_args = array(
							'default_contract'			  => $contract_template_args,
							'enquiry'	 		   		   => $email_enquiry_content_args,
							'contract'   		  			  => $email_contract_review_args,
							'booking_conf_client'		   => $email_client_booking_confirm_args,
							'email_dj_confirm'			  => $email_dj_booking_confirm_args,
							'unavailable'				   => $email_unavailability_template_args,
							'payment_cfm_template'   		  => $email_payment_received_template_args,
							'online_enquiry'				=> $online_quote_template_args
							);
			
			/* -- Existing Settings -- */
			$mdjm_event_settings = get_option( 'mdjm_event_settings' );
			$mdjm_template_settings = get_option( 'mdjm_templates_settings' );
			
			/* -- Loop through the array and create the post before setting the option -- */				
			foreach( $template_args as $key => $args )	{
				if( $id = wp_insert_post( $args ) )	{
					error_log( $key . ' template created successfully' . "\r\n", 3, true );	
				}
				else	{
					error_log( $key . ' template was not created' . "\r\n", 3, true );		
				}
				
				/* -- Default Contract -- */
				if( $key == 'default_contract' )
					$mdjm_event_settings[$key] = $id;
					
				/* -- Online Quote Template -- */
				elseif( $key == 'online_enquiry' )
					continue;
					
				/* -- Email Templates -- */
				else
					$mdjm_template_settings[$key] = $id;
			}
			
			/* -- Apply the settings -- */
			update_option( 'mdjm_event_settings', $mdjm_event_settings );
			update_option( 'mdjm_templates_settings', $mdjm_template_settings );
			
		} // install_templates

 		/*
		 * install_pages
		 * 18/03/2015
		 * @since 1.1.3
		 * Creates the MDJM application pages for the front end
		 */
		public function install_pages()	{
			$mdjm_settings = array(
								'main'		=> get_option( MDJM_SETTINGS_KEY ),
								'custom_text' => get_option( MDJM_CUSTOM_TEXT_KEY ),
								'pages'	   => get_option( MDJM_PAGES_KEY ),
								'payments'	=> get_option( MDJM_PAYMENTS_KEY ),
								'permissions' => get_option( MDJM_PERMISSIONS_KEY ),
								);
								
			include( WPMDJM_PLUGIN_DIR . '/admin/includes/mdjm-templates.php' );
			
			error_log(  date( 'd M Y H:i:s' ) . ' *** Starting MDJM Page Installation ***' . "\r\n", 3, MDJM_DEBUG_LOG );
			
			/* -- Needed page params -- */
			$mdjm_pages = array( /* -- Page Title => array [0] = slug, [1] = content, [2] = parent/child -- */
							'Client Zone'		   => array( 'client-zone', 'Home', 'parent', 'app_home_page' ),
							'Your Details'		  => array( 'client-details', 'Profile', 'child', 'profile_page' ),
							'Event Contract'	 	=> array( 'client-contracts', 'Contract', 'child', 'contracts_page' ),
							'Playlist Management'   => array( 'client-playlists', 'Playlist', 'child', 'playlist_page' ),
							'Event Payments'		=> array( 'client-payments', 'Payments', 'child', 'payments_page' ),
							'Event Quotes'		  => array( 'client-quotes', 'Online Quote', 'child', 'quotes_page' ),
							);

			/* -- Defaults for all pages -- */
			$mdjm_page_defaults = array(
									'post_type'	 	  => 'page',
									'post_status'   		=> 'publish',
									'post_author'   		=> 1,
									'ping_status'   		=> 'closed',
									'comment_status'	 => 'closed',
									);

			/* -- Grab the existing page settings so we can update with page ID's -- */
			if( !isset( $mdjm_page_settings ) );
				$mdjm_page_settings = get_option( 'mdjm_plugin_pages' );
			
			/* -- Loop through the pages finialising the defaults -- */						
			foreach( $mdjm_pages as $mdjm_page_name => $mdjm_page_data )	{
				/* -- Set the page title & slug -- */
				$mdjm_page_defaults['post_title'] = $mdjm_page_name;
				$mdjm_page_defaults['post_name'] = $mdjm_page_data[0];
				
				/* -- Set the page content -- */
				$mdjm_page_defaults['post_content'] = '[MDJM page="' . $mdjm_page_data[1] . '"]';
				
				/* -- Check if this is a parent or child -- */
				$mdjm_page_defaults['post_parent'] = ( isset( $mdjm_parent_page ) && $mdjm_page_data[2] == 'child' ? $mdjm_parent_page : 0 );
				
				/* -- Insert the page & return the page ID -- */
				$mdjm_page_id[$mdjm_page_name] = wp_insert_post( $mdjm_page_defaults );
				if( $mdjm_page_id[$mdjm_page_name] )
					error_log(  date( 'd M Y H:i:s' ) . ' Page ' . $mdjm_page_id[$mdjm_page_name] . ' created successfully' . "\r\n", 3, MDJM_DEBUG_LOG );
				
				/* -- If this is a parent page, set the parent page ID for use by children -- */
				if( $mdjm_page_data[2] == 'parent' )
					$mdjm_parent_page = $mdjm_page_id[$mdjm_page_name];

				/* -- Add the setting for the page -- */
				$mdjm_page_settings[$mdjm_page_data[3]] = $mdjm_page_id[$mdjm_page_name];
			} // end foreach
			
			/* -- Update settings with the new pages -- */
			update_option( MDJM_PAGES_KEY, $mdjm_page_settings );
			
			error_log(  date( 'd M Y H:i:s' ) . ' *** Completed MDJM Page Installation ***' . "\r\n", 3, MDJM_DEBUG_LOG );
		} // install_pages
		
		/*
		 * set_mdjm_terms
		 * 23/03/2015
		 * @since 1.1.3
		 * Creates the MDJM post terms
		 */
		public function set_mdjm_terms()	{
			global $mdjm;
			
			if( !get_taxonomy( 'event-types' ) )	{
				$tax_labels['mdjm-event'] = array(
								'name'              		   => _x( 'Event Type', 'taxonomy general name' ),
								'singular_name'     		  => _x( 'Event Type', 'taxonomy singular name' ),
								'search_items'      		   => __( 'Search Event Types' ),
								'all_items'         		  => __( 'All Event Types' ),
								'edit_item'        		  => __( 'Edit Event Type' ),
								'update_item'       			=> __( 'Update Event Type' ),
								'add_new_item'      		   => __( 'Add New Event Type' ),
								'new_item_name'     		  => __( 'New Event Type' ),
								'menu_name'         		  => __( 'Event Types' ),
								'separate_items_with_commas' => NULL,
								'choose_from_most_used'	  => __( 'Choose from the most popular Event Types' ),
								'not_found'				  => __( 'No event types found' ),
								);
				$tax_args['mdjm-event'] = array(
								'hierarchical'      	   => true,
								'labels'            	 => $tax_labels['mdjm-event'],
								'show_ui'           		=> true,
								'show_admin_column' 	  => false,
								'query_var'         	  => true,
								'rewrite'           		=> array( 'slug' => 'event-types' ),
								'update_count_callback'      => '_update_generic_term_count',
							);
				register_taxonomy( 'event-types', 'mdjm-event', $tax_args['mdjm-event'] );
			}

			/* -- Transaction Types -- */
			if( !get_taxonomy( 'transaction-types' ) )	{
				$tax_labels['mdjm-transaction'] = array(
								'name'              		   => _x( 'Transaction Type', 'taxonomy general name' ),
								'singular_name'     		  => _x( 'Transaction Type', 'taxonomy singular name' ),
								'search_items'      		   => __( 'Search Transaction Types' ),
								'all_items'         		  => __( 'All Transaction Types' ),
								'edit_item'        		  => __( 'Edit Transaction Type' ),
								'update_item'       			=> __( 'Update Transaction Type' ),
								'add_new_item'      		   => __( 'Add New Transaction Type' ),
								'new_item_name'     		  => __( 'New Transaction Type' ),
								'menu_name'         		  => __( 'Transaction Types' ),
								'separate_items_with_commas' => NULL,
								'choose_from_most_used'	  => __( 'Choose from the most popular Transaction Types' ),
								'not_found'				  => __( 'No transaction types found' ),
								);
				$tax_args[MDJM_TRANS_POSTS] = array(
								'hierarchical'      	   => true,
								'labels'            	 => $tax_labels['mdjm-transaction'],
								'show_ui'           		=> true,
								'show_admin_column' 	  => false,
								'query_var'         	  => true,
								'rewrite'           		=> array( 'slug' => 'transaction-types' ),
								'update_count_callback'      => '_update_generic_term_count',
							);
				register_taxonomy( 'transaction-types', 'mdjm-transaction', $tax_args['mdjm-transaction'] );
			}
		/* -- Venue Details -- */
			if( !get_taxonomy( 'venue-details' ) )	{
				$tax_labels['mdjm-venue'] = array(
								'name'              		   => _x( 'Venue Details', 'taxonomy general name' ),
								'singular_name'     		  => _x( 'Venue Detail', 'taxonomy singular name' ),
								'search_items'      		   => __( 'Search Venue Details' ),
								'all_items'         		  => __( 'All Venue Details' ),
								'edit_item'        		  => __( 'Edit Venue Detail' ),
								'update_item'       			=> __( 'Update Venue Detail' ),
								'add_new_item'      		   => __( 'Add New Venue Detail' ),
								'new_item_name'     		  => __( 'New Venue Detail' ),
								'menu_name'         		  => __( 'Venue Details' ),
								'separate_items_with_commas' => NULL,
								'choose_from_most_used'	  => __( 'Choose from the most popular Venue Details' ),
								'not_found'				  => __( 'No details found' ),
								);
				$tax_args[MDJM_VENUE_POSTS] = array(
								'hierarchical'      => true,
								'labels'            => $tax_labels['mdjm-venue'],
								'show_ui'           => true,
								'show_admin_column' => true,
								'query_var'         => true,
								'rewrite'           => array( 'slug' => 'venue-details' ),
							);
				register_taxonomy( 'venue-details', 'mdjm-venue', $tax_args['mdjm-venue'] );
			}
			
			error_log(  date( 'd M Y H:i:s' ) . ' Adding Event Terms' . "\r\n", 3, MDJM_DEBUG_LOG );
			/* -- Event Terms -- */
			wp_insert_term( '16th Birthday Party', 'event-types' );
			wp_insert_term( '18th Birthday Party', 'event-types' );
			wp_insert_term( '21st Birthday Party', 'event-types' );
			wp_insert_term( '30th Birthday Party', 'event-types' );
			wp_insert_term( '40th Birthday Party', 'event-types' );
			wp_insert_term( '50th Birthday Party', 'event-types' );
			wp_insert_term( '60th Birthday Party', 'event-types' );
			wp_insert_term( '70th Birthday Party', 'event-types' );
			wp_insert_term( 'Anniversary Party', 'event-types' );
			wp_insert_term( 'Child Birthday Party', 'event-types' );
			wp_insert_term( 'Corporate Event', 'event-types' );
			wp_insert_term( 'Engagement Party', 'event-types' );
			wp_insert_term( 'Halloween Party', 'event-types' );
			wp_insert_term( 'New Years Eve Party', 'event-types' );
			wp_insert_term( 'Other', 'event-types' );
			wp_insert_term( 'School Disco', 'event-types' );
			wp_insert_term( 'School Prom', 'event-types' );
			wp_insert_term( 'Wedding', 'event-types' );
			
			error_log(  date( 'd M Y H:i:s' ) . ' Adding Transaction Terms' . "\r\n", 3, MDJM_DEBUG_LOG );
			/* -- Transaction Terms -- */
			wp_insert_term( 'Deposit', 'transaction-types' );
			wp_insert_term( 'Balance', 'transaction-types' );
			wp_insert_term( 'Certifications', 'transaction-types' );
			wp_insert_term( 'Hardware', 'transaction-types' );
			wp_insert_term( 'Insurance', 'transaction-types' );
			wp_insert_term( 'Maintenance', 'transaction-types' );
			wp_insert_term( 'Music', 'transaction-types' );
			wp_insert_term( 'Parking', 'transaction-types' );
			wp_insert_term( 'Petrol', 'transaction-types' );
			wp_insert_term( 'Software', 'transaction-types' );
			wp_insert_term( 'Vehicle', 'transaction-types' );
			
			error_log(  date( 'd M Y H:i:s' ) . ' Adding Venue Terms' . "\r\n", 3, MDJM_DEBUG_LOG );
			/* -- Venue Terms -- */
			wp_insert_term( 'Low Ceiling', 'venue-details', array( 'description' => 'Venue has a low ceiling' ) );
			wp_insert_term( 'PAT Required', 'venue-details', array( 'description' => 'Venue requires a copy of the PAT certificate' ) );
			wp_insert_term( 'PLI Required', 'venue-details', array( 'description' => 'Venue requires proof of PLI' ) );
			wp_insert_term( 'Smoke/Fog Allowed', 'venue-details', array( 'description' => 'Venue allows the use of Smoke/Fog/Haze' ) );
			wp_insert_term( 'Sound Limiter', 'venue-details', array( 'description' => 'Venue has a sound limiter' ) );
			wp_insert_term( 'Via Stairs', 'venue-details', array( 'description' => 'Access to this Venue is via stairs' ) );
		} //set_mdjm_terms
		
		public function install_db()	{
			global $wpdb, $mdjm_db_version;
			
			$charset_collate = !empty( $wpdb->charset ) ? 'DEFAULT CHARACTER SET ' . $wpdb->charset : '' . 
				( !empty( $wpdb->collate ) ? ' COLLATE ' . $wpdb->collate : '' );
			
			$mdjm_db_tables = array(
								/* PLAYLISTS TABLE */
								'playlist'	=> "CREATE TABLE " . MDJM_PLAYLIST_TABLE . " (
												id int(11) NOT NULL AUTO_INCREMENT,
												event_id int(11) NOT NULL,
												artist varchar(255) NOT NULL,
												song varchar(255) NOT NULL,
												play_when varchar(255) NOT NULL,
												info text NOT NULL,
												added_by varchar(255) NOT NULL,
												date_added date NOT NULL,
												date_to_mdjm datetime NULL,
												upload_procedure int(11) DEFAULT '0' NOT NULL,
												PRIMARY KEY  (id),
												KEY event_id (event_id),
												KEY artist (artist),
												KEY song (song)
												) " . $charset_collate . ";",
								/* MUSIC LIBRARY TABLE */
								'music_library' => 'CREATE TABLE ' . MDJM_MUSIC_LIBRARY_TABLE . ' (
												id int(11) NOT NULL AUTO_INCREMENT,
												library varchar(255) NOT NULL,
												library_slug varchar(255) NOT NULL,
												song varchar(255) NOT NULL,
												artist varchar(255) NOT NULL,
												album varchar(255) NULL,
												genre varchar(255) NULL,
												year varchar(10) NULL,
												comments text NULL,
												rating varchar(10) NULL,
												dj int(11) NOT NULL,
												date_added date NULL,
												PRIMARY KEY  (id),
												KEY library (library),
												KEY song (song),
												KEY artist (artist),
												KEY year (year),
												KEY genre (genre),
												KEY dj (dj)
												) ' . $charset_collate . ';',
								/* AVAILABILITY TABLE */
								'holiday'	=> 'CREATE TABLE ' . MDJM_HOLIDAY_TABLE . ' (
												id int(11) NOT NULL AUTO_INCREMENT,
												user_id int(11) NOT NULL,
												entry_id varchar(100) NOT NULL,
												date_from date NOT NULL,
												date_to date NOT NULL,
												notes text NULL,
												PRIMARY KEY  (id),
												KEY user_id (user_id)
												) ' . $charset_collate . ';',
								);
			
			require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
			
			foreach( $mdjm_db_tables as $table_name => $sql )	{
				error_log(  date( 'd M Y H:i:s' ) . ' Creating the ' . $table_name . ' database table' . "\r\n", 3, MDJM_DEBUG_LOG );
				dbDelta( $sql );
			} // End foreach
			
		} // install_db
	} // class

/*
 * --
 * Installation Procedures
 * --
 */
	if( class_exists( 'MDJM_Install' ) )
		$mdjm_install = new MDJM_Install();