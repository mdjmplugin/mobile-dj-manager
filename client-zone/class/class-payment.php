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
															'<p>We\'re sorry but you do not have permission to access this page. If you believe this is an error, please <a href="' . 
															$mdjm->get_link( MDJM_CONTACT_PAGE, false ) . '">contact us</a>.</p>' . "\r\n" . 
															'<p>Otherwise, <a href="' . $mdjm->get_link( MDJM_HOME ) . 
															'">Click here</a> return to the <a href="' . $mdjm->get_link( MDJM_HOME, false ) . '">' . 
															MDJM_APP . '</a> home page.</p>' . "\r\n" ) );
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
								'<p>Paying for your event is easy as we accept secure online payments via <a title="PayPal" href="https://www.paypal.com" target="_blank">' . 
				'PayPal</a>.</p>' . "\r\n" . 
                '<p><a title="PayPal" href="https://www.paypal.com" target="_blank">PayPal</a> accept all major credit cards and you do not need to be a ' . 
				'<a title="PayPal" href="https://www.paypal.com" target="_blank">PayPal</a> member to process your payment to us</p>' . "\r\n" );
								
				echo parent::__text( 'payment_intro',
								'<p>Any outstanding payments for your event are displayed via the drop down list below.</p>' . "\r\n" . 
                '<p>Select the payment you wish to make and click the <strong>Pay Now</strong> button to be redirected to ' . 
				'<a title="PayPal" href="https://www.paypal.com" target="_blank">PayPal\'s</a> secure website where you can complete your payment.</p>' . "\r\n" . 
                '<p>Upon completion, you can return to the ' . MDJM_COMPANY . ' website. You will also receive an email as soon as your payment completes.</p>' . "\r\n" );
										
				$mdjm_paypal = new MDJM_PayPal();
				
				$pp_form = $mdjm_paypal->pp_form( $this->event );
				
				echo $pp_form;
			} // display_page
			
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
					$default_text = '<p>Thank you. Your payment has completed successfully.</p>' . "\r\n" .
					'<p>You will shortly receive an email from us (remember to check your junk email folder) confirming the payment and detailing next ' .
					'steps for your event.</p>' . "\r\n" .
					'<p><strong>Please note</strong> that it can take a few minutes for our systems to be updated by ' .
					'<a title="PayPal" href="https://www.paypal.com" target="_blank">PayPal</a>, and therefore your payment may not have ' .
					'registered below as yet. Once you receive the payment confirmation email from us, the payment will be updated on our systems.</p>' . "\r\n" .
					'<p><a href="' . $mdjm->get_link( MDJM_HOME ) . '">Click here</a> to return to the <a href="' . $mdjm->get_link( MDJM_HOME ) . '">' . 
					MDJM_APP . '</a> home page.</p>' . "\r\n" .
					'<hr />' . "\r\n";
					
					parent::display_notice( 2,
											parent::__text( 'payment_complete',
															'<p>Thank you. Your payment has completed successfully.</p>' . "\r\n" .
															'<p>You will shortly receive an email from us (remember to check your junk email folder) confirming the ' . 
															'payment and detailing next steps for your event.</p>' . "\r\n" .
															'<p><strong>Please note</strong> that it can take a few minutes for our systems to be updated by ' .
															'<a title="PayPal" href="https://www.paypal.com" target="_blank">PayPal</a>, and therefore your payment may not have ' .
															'registered below as yet. Once you receive the payment confirmation email from us, the payment will be ' . 
															'updated on our systems.</p>' . "\r\n" . '<p><a href="' . $mdjm->get_link( MDJM_HOME ) . 
															'">Click here</a> to return to the <a href="' . $mdjm->get_link( MDJM_HOME ) . '">' . 
															MDJM_APP . '</a> home page.</p>' . "\r\n" ) );
				}
				
				// Display message for a cancelled transaction
				if( $action == 'cancelled' )	{										
					parent::display_notice( 1,
											parent::__text( 'payment_cancel',
															'<p>Your payment has been cancelled.</p>' . "\r\n" . 
															'<p>To process your payment again, please follow the steps below.</p>' . "\r\n" ) );
				}
			} // paypal_return
			
		} // class
		
	} // if( !class_exists( 'MDJM_Payment' ) )
	
/* -- Insantiate the MDJM_Payment class -- */
	$mdjm_payment = new MDJM_Payment();	
				
