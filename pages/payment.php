<?php
/*
* payment.php
* 15/02/2015
* @since 1.1
* Displays the Client Zone frontend payments page
* Last Updated: 15/02/2015
*/

	defined('ABSPATH') or die("Direct access to this page is disabled!!!");
	
	global $clientzone, $mdjm_posts, $mdjm, $mdjm_settings, $my_mdjm, $wpdb;
	
	// If the user is logged in we can display the page
	if( is_user_logged_in() )	{
		// Check for an Event ID
		if( !isset( $_GET['event_id'] ) )	{
			$default_text = '<p>No event has been selected for payment. <a href="' . $mdjm->get_link( MDJM_HOME ) . 
			'">Click here</a> return to the <a href="' . $mdjm->get_link( MDJM_HOME, false ) . '">' . MDJM_APP . '</a> home page.</p>' . "\r\n";
			
			echo ( MDJM_CUSTOM_TEXT == true ) ? $clientzone->custom_text( 'payment_noevent' ) : $default_text;
		}
		else	{
			$post = get_post( $_GET['event_id'] );
			
			// Make sure the event exists
			if( empty( $post ) || !$mdjm_posts->post_exists( $_GET['event_id'] ) )	{
				$default_text = '<p>We\'re sorry but you do not have permission to access this page. If you believe this is an error, please <a href="' . 
				$mdjm->get_link( MDJM_CONTACT_PAGE, false ) . '">contact us</a>.</p>' . "\r\n" . 
				'<p>Otherwise, <a href="' . $mdjm->get_link( MDJM_HOME, false ) . '">Click here</a> return to the <a href="' . $mdjm->get_link( MDJM_HOME, false ) . '">' . 
				MDJM_APP . '</a> home page.</p>' . "\r\n";
				
				echo ( MDJM_CUSTOM_TEXT == true ) ? $clientzone->custom_text( 'payment_noaccess' ) : $default_text;
			}
			
			// Must be the users own event
			elseif( get_post_meta( $post->ID, '_mdjm_event_client', true ) != get_current_user_id() && !current_user_can( 'administrator' ) )	{
				$default_text = '<p>We\'re sorry but you do not have permission to access this page. If you believe this is an error, please <a href="' . 
				$mdjm->get_link( MDJM_CONTACT_PAGE ) . '">contact us</a>.</p>' . "\r\n" . 
				'<p>Otherwise, <a href="' . $mdjm->get_link( MDJM_HOME ) . '">Click here</a> return to the <a href="' . $mdjm->get_link( MDJM_HOME ) . '">' . 
				MDJM_APP . '</a> home page.</p>' . "\r\n";
				
				echo ( MDJM_CUSTOM_TEXT == true ) ? $clientzone->custom_text( 'payment_noaccess' ) : $default_text;
			}
			
			// No payments due
			elseif( get_post_meta( $post->ID, '_mdjm_event_deposit_status', true ) == 'Paid' && get_post_meta( $post->ID, '_mdjm_event_balance_status', true ) == 'Paid' )	{
				$default_text = '<p>There are no payments outstanding for this event. If you believe this is an error, please <a href="' . $mdjm->get_link( MDJM_CONTACT_PAGE ) . 
				'">contact us</a>.</p>' . "\r\n" .
				'<p>Otherwise, <a href="' . $mdjm->get_link( MDJM_HOME ) . '">Click here</a> return to the <a href="' . $mdjm->get_link( MDJM_HOME ) . 
				'">' . MDJM_APP . '</a> home page.</p>' . "\r\n";
				
				echo ( MDJM_CUSTOM_TEXT == true ) ? $clientzone->custom_text( 'payment_not_due' ) : $default_text;
			}
			// We're good
			else	{
				// Are we returning from PayPal?
				if( isset( $_GET['pp_action'] ) && !empty( $_GET['pp_action'] ) )	{
					if( !isset( $db_tbl ) )	{
						include( WPMDJM_PLUGIN_DIR . '/includes/config.inc.php' );	
					}
					// Payment completed
					if( $_GET['pp_action'] == 'completed' )	{
						$default_text = '<p>Thank you. Your payment has completed successfully.</p>' . "\r\n" .
                        '<p>You will shortly receive an email from us (remember to check your junk email folder) confirming the payment and detailing next ' .
						'steps for your event.</p>' . "\r\n" .
                        '<p><strong>Please note</strong> that it can take a few minutes for our systems to be updated by ' .
						'<a title="PayPal" href="https://www.paypal.com" target="_blank">PayPal</a>, and therefore your payment may not have ' .
						'registered below as yet. Once you receive the payment confirmation email from us, the payment will be updated on our systems.</p>' . "\r\n" .
                        '<p><a href="' . $mdjm->get_link( MDJM_HOME ) . '">Click here</a> to return to the <a href="' . $mdjm->get_link( MDJM_HOME ) . '">' . 
						MDJM_APP . '</a> home page.</p>' . "\r\n" .
                        '<hr />' . "\r\n";
						
						echo ( MDJM_CUSTOM_TEXT == true ) ? $clientzone->custom_text( 'payment_complete' ) : $default_text;
					}
					// Payment Cancelled
					elseif( $_GET['pp_action'] == 'cancelled' )	{
						
						$update_trans_query = $wpdb->insert( $db_tbl['trans'],
													array(
														'trans_id'		  => '',
														'event_id'		  => $_GET['event_id'],
														'payment_src'		=> 'PayPal',
														'payment_txn_id'	=> stripslashes( 'N/A' ),
														'payment_date'	  => date( 'Y-m-d H:i:s' ),
														'payment_type'	  => 'N/A',
														'payer_id'	  	  => 'N/A',
														'payment_status'	=> 'Cancelled',
														'payer_firstname'   => stripslashes( $current_user->user_firstname ),
														'payer_lastname'	=> stripslashes( $current_user->user_lastname ),
														'payer_email'	   => $current_user->user_email,
														'payment_for'	   => '',
														'payment_currency'  => '',
														'payment_tax'	   => '',
														'payment_gross'	 => '0.00',
														'full_ipn'		  => '',
														'seen_by_admin'	  => 0,
														) );
						
						$default_text = '<p><span style="color: #ff0000;">Your payment has been cancelled.</span></p>' . "\r\n" . 
                        '<p>To process your payment again, please follow the steps below.</p>' . "\r\n";
						
						echo ( MDJM_CUSTOM_TEXT == true ) ? $clientzone->custom_text( 'payment_cancel' ) : $default_text;
					}
				}
				
				if( !class_exists( 'MDJM_PayPal' ) ) {
					require_once( WPMDJM_PLUGIN_DIR . '/includes/class/class-mdjm-pp-gateway.php' );
				}
				
				$default_text = '<p>Paying for your event is easy as we accept secure online payments via <a title="PayPal" href="https://www.paypal.com" target="_blank">' . 
				'PayPal</a>.</p>' . "\r\n" . 
                '<p><a title="PayPal" href="https://www.paypal.com" target="_blank">PayPal</a> accept all major credit cards and you do not need to be a ' . 
				'<a title="PayPal" href="https://www.paypal.com" target="_blank">PayPal</a> member to process your payment to us</p>' . "\r\n";
				
				echo ( MDJM_CUSTOM_TEXT == true ) ? $clientzone->custom_text( 'payment_welcome' ) : $default_text;
				
				$default_text = '<p>Any outstanding payments for your event are displayed via the drop down list below.</p>' . "\r\n" . 
                '<p>Select the payment you wish to make and click the <strong>Pay Now</strong> button to be redirected to ' . 
				'<a title="PayPal" href="https://www.paypal.com" target="_blank">PayPal\'s</a> secure website where you can complete your payment.</p>' . "\r\n" . 
                '<p>Upon completion, you can return to the ' . MDJM_COMPANY . ' website. You will also receive an email as soon as your payment completes.</p>' . "\r\n";
				
				echo ( MDJM_CUSTOM_TEXT == true ) ? $clientzone->custom_text( 'payment_intro' ) : $default_text;
										
				$mdjm_paypal = new MDJM_PayPal();
				
				$pp_form = $mdjm_paypal->pp_form( $post );
				
				echo $pp_form;
			}
		}
		
	} // if( is_user_logged_in() )
	
	// If the user is not logged in, show the login prompt
	else	{
		$clientzone->login();
	} // else (if( is_user_logged_in() ))
	
?>