<?php
/*
 * class-payment.php
 * 19/06/2015
 * @since 2.1
 * The ClientZone Payment class enables clients to make online payments
 * via PayPal
 * 
 */
	
	defined( 'ABSPATH' ) or die( "Direct access to this page is disabled!!!" );
	
	/* -- Build the MDJM_Profile class -- */
	if( !class_exists( 'MDJM_Payment' ) )	{
		require_once( 'class-clientzone.php' );
		class MDJM_Payment extends ClientZone 	{
			/*
			 * The Constructor
			 *
			 *
			 *
			 */
			function __construct()	{
				global $post;
				
				if( !is_user_logged_in() )
					parent::login();
					
				else	{
					$this->event = isset( $_GET['event_id'] ) ? get_post( $_GET['event_id'] ) : '';
					$post = ( !empty( $this->event ) ? $this->event : '' );
					
					// Returning from PayPal?
					if( isset( $_GET['pp_action'] ) && !empty( $_GET['pp_action'] ) )
						$this->paypal_return( $_GET['pp_action'] );
						
					$this->display();	
				}
				
			} // __construct
			
			/*
			 * Control the page display
			 *
			 *
			 *
			 */
			function display()	{
				global $mdjm, $mdjm_posts;
				
				// Make sure the event exists
				if( empty( $this->event ) || !$mdjm_posts->post_exists( $this->event ) )	{					
					parent::display_notice( 4,
								parent::__text( 'payment_noaccess', 
												'<p>' . sprintf( __( "We're sorry but you do not have permission to access this page. " . 
													'If you believe this is an error, please %scontact us', 'mobile-dj-manager' ),
													'<a href="' . $mdjm->get_link( MDJM_CONTACT_PAGE, false ) . '">',
													'</a>' ) . 
												'.</p>' . "\r\n" . 
												
												'<p>' . sprintf( __( 'Otherwise, %sClick here%s return to the %s%s%s home page', 'mobile-dj-manager' ),
													'<a href="' . $mdjm->get_link( MDJM_HOME ) . '">',
													'</a>',
													'<a href="' . $mdjm->get_link( MDJM_HOME, false ) . '">',
													MDJM_APP,
													'</a>' ) . 
												'.</p>' . "\r\n" ) );
				}
				
				// Must be the users own event
				elseif( get_post_meta( $this->event->ID, '_mdjm_event_client', true ) != get_current_user_id() && !current_user_can( 'administrator' ) )	{
					parent::display_notice( 4,
											parent::__text( 'payment_noaccess', 
															'<p>We\'re sorry but you do not have permission to access this page. If you believe this is an error, please <a href="' . 
															$mdjm->get_link( MDJM_CONTACT_PAGE ) . '">contact us</a>.</p>' . "\r\n" . 
															'<p>Otherwise, <a href="' . $mdjm->get_link( MDJM_HOME ) . '">Click here</a> return to the <a href="' . 
															$mdjm->get_link( MDJM_HOME ) . '">' . MDJM_APP . '</a> home page.</p>' . "\r\n" ) );
				}
				
				// No payments due
				elseif( get_post_meta( $this->event->ID, '_mdjm_event_deposit_status', true ) == 'Paid' && 
					get_post_meta( $this->event->ID, '_mdjm_event_balance_status', true ) == 'Paid' )	{
					
					parent::display_notice( 5,
											parent::__text( 'payment_not_due', 
															'<p>There are no payments outstanding for this event. If you believe this is an error, please <a href="' . 
															$mdjm->get_link( MDJM_CONTACT_PAGE ) . 
															'">contact us</a>.</p>' . "\r\n" .
															'<p>Otherwise, <a href="' . $mdjm->get_link( MDJM_HOME ) . '">Click here</a> return to the <a href="' . 
															$mdjm->get_link( MDJM_HOME ) . '">' . MDJM_APP . '</a> home page.</p>' . "\r\n" ) );
				}
				else // We're good to go
					$this->display_page();
					
				
			} // display
			
			/*
			 * Display the payments page
			 *
			 *
			 *
			 */
			function display_page()	{
				if( !class_exists( 'MDJM_PayPal' ) )
					require_once( MDJM_PLUGIN_DIR . '/includes/class/class-mdjm-pp-gateway.php' );
				
				echo parent::__text( 'payment_welcome',
								'<p>' . sprintf( __( 'Paying for your event is easy as we accept secure online payments via ' . 
								'%sPayPal%s', 'mobile-dj-manager' ),
									'<a title="PayPal" href="https://www.paypal.com" target="_blank">',
									'</a>' ) . '.</p>' . "\r\n" . 
								
								'<p>' . sprintf( __( '%sPayPal%s accept all major credit cards and you do not need to be a ' . 
								'<a title="PayPal" href="https://www.paypal.com" target="_blank">PayPal</a> member to process your payment to us', 'mobile-dj-manager' ),
									'<a title="PayPal" href="https://www.paypal.com" target="_blank">',
									'</a>',
									'<a title="PayPal" href="https://www.paypal.com" target="_blank">',
									'</a>' ) . '</p>' . "\r\n" );
								
				echo parent::__text( 'payment_intro',
								'<p>' . __( 'Any outstanding payments for your event are displayed via the drop down list below', 'mobile-dj-manager' ) . '.</p>' . "\r\n" . 
								
								'<p>' . sprintf( __( 'Select the payment you wish to make and click the %sPay Now%s button to be redirected to ' . 
								'%sPayPal\'s%s secure website where you can complete your payment', 'mobile-dj-manager' ),
								'<strong>',
								'</strong>',
								'<a title="PayPal" href="https://www.paypal.com" target="_blank">',
								'</a>' ) . '.</p>' . "\r\n" . 
                				
								'<p>' . sprintf( __( 'Upon completion, you can return to the %s website. ' . 
								'You will also receive an email as soon as your payment completes', 'mobile-dj-manager' ),
								MDJM_COMPANY ) . '.</p>' . "\r\n" );
										
				$mdjm_paypal = new MDJM_PayPal();
				
				$pp_form = $mdjm_paypal->pp_form( $this->event );
				
				echo $pp_form;
			} // display_page
			
			/*
			 * Generate and display the page header and welcome text
			 *
			 *
			 *
			 */
			function payment_header()	{
				echo '<!-- ' . 
				sprintf( __( '%s (%s) PayPal API integration form for online client payments', 'mobile-dj-manager' ),
						 MDJM_NAME,
						 MDJM_VERSION_NUM ) .
				' -->'; 
					
				
				
				/* -- Welcome text -- */
				echo parent::__text( 'payment_welcome',
								'<p>' . sprintf( __( 'Paying for your event is easy as we accept secure online payments via %s' . 
								'PayPal%s', 'mobile-dj-manager' ), 
									'<a title="PayPal" href="https://www.paypal.com" target="_blank">', '</a>' ) . '.</p>' . "\r\n" . 
								'<p><a title="PayPal" href="https://www.paypal.com" target="_blank">' . 
								
								sprintf( __( 'PayPal%s accept all major credit cards and you do not need to be a ' . 
								'%sPayPal%s member to process your payment to us', 'mobile-dj-manager' ), 
									'</a>', '<a title="PayPal" href="https://www.paypal.com" target="_blank">', '</a>' ) . '</p>' . "\r\n" );
									
				/* -- Intro Text -- */
				echo parent::__text( 'payment_intro',
								'<p>' . __( 'Any outstanding payments for your event are displayed via the drop down list below' ) . '.</p>' . "\r\n" . 
								
								'<p>' .
								 
								sprintf( __( 'Select the payment you wish to make and click the %sPay Now%s button to be redirected to ' . 
								'%sPayPal\'s%s secure website where you can complete your payment', 'mobile-dj-manager' ), 
								'<strong', '</strong>', '<a title="PayPal" href="https://www.paypal.com" target="_blank">', '</a>' ) . '</p>' . "\r\n" . 
								
								'<p>' . 
								
								sprintf( __( 'Upon completion, you can return to the %s website. You will also receive an email as soon as your payment completes',
									'mobile-dj-manager' ), MDJM_COMPANY ) . 
									
								'</p>' . "\r\n" );
								
				/* -- Display the PayPal form -- */
				$this->PayPal_form();
				
			} // payment_header
			
			/*
			 * Generate and display the PayPal form to allow payments
			 *
			 * @param	obj		$post		The post object for the event
			 *
			 */
			function PayPal_form()	{
				global $mdjm, $mdjm_settings, $post;
				
				// Create required arrays
				$payments_settings = $mdjm_settings['payments'];
				$paypal_settings = $mdjm_settings['paypal'];
				
				// Determine balance and deposit amounts due
				$balance = get_post_meta( $post->ID, '_mdjm_event_cost', true );
				$balance_status = get_post_meta( $post->ID, '_mdjm_event_balance_status', true );
				
				$deposit = get_post_meta( $post_id, '_mdjm_event_deposit', true );
				$deposit_status = get_post_meta( $post->ID, '_mdjm_event_deposit_status', true );
				
				if( $deposit_status == 'Paid' ) // If deposit is paid, remove from event balance
					$balance = get_post_meta( $post->ID, '_mdjm_event_cost', true ) - get_post_meta( $post->ID, '_mdjm_event_deposit', true );
					
				// Configure PayPal email depending on Sandbox or Live
				$paypal_api = ( !empty( $paypal_settings['enable_sandbox'] ) ? 'www.sandbox.paypal.com/cgi-bin/webscr' : 'www.paypal.com/cgi-bin/webscr' );
				
				$paypal_email = ( !empty( $paypal_settings['sandbox_email'] ) && !empty( $paypal_settings['enable_sandbox'] ) ? 
					$paypal_settings['sandbox_email'] : $paypal_settings['paypal_email'] );
				
				// Now we print out the HTML form for PayPal payments
				
				// Start with the javascript function to populate the required hidden fields
				$payment_form .= '<script type="text/javascript">' . "\r\n";
				$payment_form .= 'function changeCustomInput (objDropDown)' . "\r\n";
				$payment_form .= '{' . "\r\n";
				$payment_form .= '    var objHidden = document.getElementById("custom");' . "\r\n";
				$payment_form .= '    objHidden.value = objDropDown.value;' . "\r\n";
				$payment_form .= '}' . "\r\n";
				$payment_form .= '</script>' . "\r\n";
				$payment_form .= '<form action="https://' . $paypal_api . '" method="post" target="_top">' . "\r\n";
				$payment_form .= '<input type="hidden" name="cmd" value="_xclick">' . "\r\n";
				$payment_form .= '<input type="hidden" name="business" value="' . $paypal_email . '">' . "\r\n";
				$payment_form .= '<input type="hidden" name="lc" value="' . get_locale() . '">' . "\r\n";
				
				// This is where we set the payment description / item name
				$payment_form .= '<input type="hidden" name="item_name" value="Event ID ' . $post->ID . ' (' . 
					date( MDJM_SHORTDATE_FORMAT, strtotime( get_post_meta( $post->ID, '_mdjm_event_date', true ) ) ) . 
					') - ' . MDJM_COMPANY . '">' . "\r\n";
				
				$payment_form .= '<input type="hidden" name="item_number" value="' . $post->ID . '">' . "\r\n";
				
				// Set the default payment selection
				$payment_form .= '<input type="hidden" name="custom" id="custom" value="';
				$payment_form .= ( $deposit_status != 'Paid' ? MDJM_DEPOSIT_LABEL : MDJM_BALANCE_LABEL );
				$payment_form .= '">' . "\r\n";
				
				$payment_form .= '<input type="hidden" name="button_subtype" value="services">' . "\r\n";
				$payment_form .= '<input type="hidden" name="no_note" value="1">' . "\r\n";
				$payment_form .= '<input type="hidden" name="no_shipping" value="1">' . "\r\n";
				$payment_form .= '<input type="hidden" name="rm" value="2">' . "\r\n";
				
				// Set the return page from PayPal once successful payment completes
				$payment_form .= '<input type="hidden" name="return" value="' . 
					$mdjm->get_link( $paypal_settings['redirect_success'] ) . 'pp_action=completed&event_id=' . $post->ID . '">' . "\r\n";
					
				// Set the return page from PayPal upon cancellation of payment
				$payment_form .= '<input type="hidden" name="cancel_return" value="' . 
					$mdjm->get_link( $paypal_settings['redirect_cancel'] ) . 'pp_action=cancelled&event_id=' . $post->ID . '">' . "\r\n";
				
				// Set the currency for payment	
				$payment_form .= '<input type="hidden" name="currency_code" value="' . $payment_settings['currency'] . '">' . "\r\n";
				
				// Set the desired payment button
				$payment_form .= '<input type="hidden" name="bn" value="PP-BuyNowBF:' . $paypal_settings['paypal_button'] . ':NonHosted">' . "\r\n";
				
				// Set the notify URL for API
				$payment_form .= '<input type="hidden" name="notify_url" value="' . home_url() . '/?mdjm-api=MDJM_PAYPAL_GW">' . "\r\n";
				
				// Invoice number - NOT IN USE
				//$payment_form .= '<input type="hidden" name="invoice" value="' . MDJM_EVENT_PREFIX . '0' . $eventinfo->event_id . '">' . "\r\n";
				
				// Determine and set taxes
				if( !empty( $payment_settings['enable_tax'] ) && !empty( $payment_settings['tax_rate'] ) )	{
					if( isset( $payment_settings['tax_type'] ) && $payment_settings['tax_type'] == 'percentage' )
						$tax = 'tax_rate';
					
					if( isset( $payment_settings['tax_type'] ) && $payment_settings['tax_type'] == 'fixed' )
						$tax = 'tax';
					
					if( !empty( $tax ) )
						$payment_form .= '<input type="hidden" name="' . $tax . '" value="' . $payment_settings['tax_type'] . '">' . "\r\n";
				}
				
				// Set customised PayPal checkout page if configured
				if( !empty( $paypal_settings['checkout_style'] ) )
					$payment_form .= '<input type="hidden" name="page_style" value="' . $paypal_settings['checkout_style'] . '">' . "\r\n";
					
				$payment_form .= '<input type="hidden" name="on0" value="Paying for">' . "\r\n";
				
				
			
			} // PayPal_form
			
			/*
			 * Actions for when the user returns to this page from PayPal
			 * after they have completed or cancelled a payment
			 *
			 *
			 */
			function paypal_return( $action )	{
				global $mdjm;
				
				// Display message for a completed transaction
				if( $action == 'completed' )	{
					parent::display_notice( 2,
											parent::__text( 'payment_complete',
															'<p>' . __( 'Thank you. Your payment has completed successfully', 'mobile-dj-manager' ) . '.</p>' . "\r\n" .
															'<p>' . __( 'You will shortly receive an email from us (remember to check your junk email folder) confirming the ' . 
															'payment and detailing next steps for your event', 'mobile-dj-manager' ) . '.</p>' . "\r\n" .
															'<p>' . sprintf( __( '%sPlease note%s that it can take a few minutes for our systems to be updated by ' .
															'%sPayPal%s, and therefore your payment may not have ' .
															'registered below as yet. Once you receive the payment confirmation email from us, the payment will be ' . 
															'updated on our systems', 'mobile-dj-manager' ), 
															'<strong>',
															'</strong>',
															'<a title="PayPal" href="https://www.paypal.com" target="_blank">',
															'</a>' ) . '.</p>' . "\r\n" . 
															'<p>' . sprintf( __( '%sClick here%s to return to the %s home page', 'mobile-dj-manager' ),
															'<a href="' . $mdjm->get_link( MDJM_HOME ) . '">',
															'</a>',
															'<a href="' . $mdjm->get_link( MDJM_HOME ) . '">' . MDJM_APP . '</a>' ) . '.</p>' . "\r\n" ) );
				}
				
				// Display message for a cancelled transaction
				if( $action == 'cancelled' )	{										
					parent::display_notice( 1,
											parent::__text( 'payment_cancel',
															'<p>' . __( 'Your payment has been cancelled', 'mobile-dj-manager' ) . '.</p>' . "\r\n" . 
															'<p>' . __( 'To process your payment again, please follow the steps below', 'mobile-dj-manager' ) 
																. '.</p>' . "\r\n" ) );
				}
			} // paypal_return
			
		} // class
		
	} // if( !class_exists( 'MDJM_Payment' ) )
	
/* -- Insantiate the MDJM_Payment class -- */
	$mdjm_payment = new MDJM_Payment();	
				
