<?php
/*
* payment.php
* 15/02/2015
* @since 1.1
* Displays the Client Zone frontend payments page
* Last Updated: 15/02/2015
*/

	defined('ABSPATH') or die("Direct access to this page is disabled!!!");
	
	global $mdjm_options, $mdjm_client_text, $current_user, $wpdb;
	
	// If the user is logged in we can display the page
	if( is_user_logged_in() )	{
		// Check for an Event ID
		if( !isset( $_GET['event_id'] ) )	{
			if( isset( $mdjm_client_text['custom_client_text'] ) && $mdjm_client_text['custom_client_text'] == 'Y' )	{
				f_mdjm_client_text( 'payment_noevent' );
			}
			else	{
				?>
				<p>No event has been selected for payment. <a href="<?php echo get_permalink( $mdjm_options['app_home_page'] ); ?>">Click here</a> return to the <a href="<?php echo get_permalink( $mdjm_options['app_home_page'] ); ?>"><?php echo WPMDJM_APP_NAME; ?></a> home page.</p>
				<?php
			}
		}
		else	{
			$eventinfo = f_mdjm_get_eventinfo_by_id( $_GET['event_id'] );
			
			// Make sure the event exists
			if( empty( $eventinfo ) )	{
				if( isset( $mdjm_client_text['custom_client_text'] ) && $mdjm_client_text['custom_client_text'] == 'Y' )	{
					f_mdjm_client_text( 'payment_noaccess' );
				}
				else	{
					?>
					<p>We're sorry but you do not have permission to access this page. If you believe this is an error, please <a href="<?php echo get_permalink( WPMDJM_CONTACT_PAGE ); ?>">contact us</a>.</p>
					<p>Otherwise, <a href="<?php echo get_permalink( $mdjm_options['app_home_page'] ); ?>">Click here</a> return to the <a href="<?php echo get_permalink( $mdjm_options['app_home_page'] ); ?>"><?php echo WPMDJM_APP_NAME; ?></a> home page.</p>
					<?php
				}
			}
			
			// Must be the users own event
			elseif( $eventinfo->user_id != get_current_user_id() && !current_user_can( 'administrator' ) )	{
				if( isset( $mdjm_client_text['custom_client_text'] ) && $mdjm_client_text['custom_client_text'] == 'Y' )	{
					f_mdjm_client_text( 'payment_noaccess' );
				}
				else	{
					?>
					<p>We're sorry but you do not have permission to access this page. If you believe this is an error, please <a href="<?php echo get_permalink( WPMDJM_CONTACT_PAGE ); ?>">contact us</a>.</p>
					<p>Otherwise, <a href="<?php echo get_permalink( $mdjm_options['app_home_page'] ); ?>">Click here</a> return to the <a href="<?php echo get_permalink( $mdjm_options['app_home_page'] ); ?>"><?php echo WPMDJM_APP_NAME; ?></a> home page.</p>
					<?php
				}
			}
			
			// No payments due
			elseif( $eventinfo->deposit_status == 'Paid' && $eventinfo->balance_status == 'Paid' )	{
				if( isset( $mdjm_client_text['custom_client_text'] ) && $mdjm_client_text['custom_client_text'] == 'Y' )	{
					f_mdjm_client_text( 'payment_not_due' );
				}
				else	{
					?>
					<p>There are no payments outstanding for this event. If you believe this is an error, please <a href="<?php echo get_permalink( WPMDJM_CONTACT_PAGE ); ?>">contact us</a>.</p>
					<p>Otherwise, <a href="<?php echo get_permalink( $mdjm_options['app_home_page'] ); ?>">Click here</a> return to the <a href="<?php echo get_permalink( $mdjm_options['app_home_page'] ); ?>"><?php echo WPMDJM_APP_NAME; ?></a> home page.</p>
					<?php
				}
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
						if( isset( $mdjm_client_text['custom_client_text'] ) && $mdjm_client_text['custom_client_text'] == 'Y' )	{
							f_mdjm_client_text( 'payment_complete' );
						}
						else	{
							?>
							<p>Thank you. Your payment has completed successfully.</p>
                            <p>You will shortly receive an email from us (remember to check your junk email folder) confirming the payment and detailing next steps for your event.</p>
                            <p><strong>Please note</strong> that it can take a few minutes for our systems to be updated by <a title="PayPal" href="https://www.paypal.com" target="_blank">PayPal</a>, and therefore your payment may not have registered below as yet. Once you receive the payment confirmation email from us, the payment will be updated on our systems.</p>
                            <p><a href="<?php echo get_permalink( $mdjm_options['app_home_page'] ); ?>">Click here</a> to return to the <a href="<?php echo get_permalink( $mdjm_options['app_home_page'] ); ?>"><?php echo WPMDJM_APP_NAME; ?></a> home page.</p>
                            <hr />
							<?php
						}
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
						
						if( isset( $mdjm_client_text['custom_client_text'] ) && $mdjm_client_text['custom_client_text'] == 'Y' )	{
							f_mdjm_client_text( 'payment_cancel' );
						}
						else	{
							?>
							<p><span style="color: #ff0000;">Your payment has been cancelled.</span></p>
                            <p>To process your payment again, please follow the steps below.</p>
							<?php
						}
					}
				}
				
				if( !class_exists( 'MDJM_PayPal' ) ) {
					require_once( WPMDJM_PLUGIN_DIR . '/includes/class/class-mdjm-pp-gateway.php' );
				}
				
				if( isset( $mdjm_client_text['custom_client_text'] ) && $mdjm_client_text['custom_client_text'] == 'Y' )	{
					?><p><?php f_mdjm_client_text( 'payment_welcome' ); ?></p><?php
				}
				else	{
					?>
					<p>Paying for your event is easy as we accept secure online payments via <a title="PayPal" href="https://www.paypal.com" target="_blank">PayPal</a>.</p>
                    <p><a title="PayPal" href="https://www.paypal.com" target="_blank">PayPal</a> accept all major credit cards and you do not need to be a <a title="PayPal" href="https://www.paypal.com" target="_blank">PayPal</a> member to process your payment to us</p>
					<?php
				}
				
				if( isset( $mdjm_client_text['custom_client_text'] ) && $mdjm_client_text['custom_client_text'] == 'Y' )	{
					?><p><?php f_mdjm_client_text( 'payment_intro' ); ?></p><?php
				}
				else	{
					?>
					<p>Any outstanding payments for your event are displayed via the drop down list below.</p>
                    <p>Select the payment you wish to make and click the <strong>Pay Now</strong> button to be redirected to <a title="PayPal" href="https://www.paypal.com" target="_blank">PayPal's</a> secure website where you can complete your payment.</p>
                    <p>Upon completion, you can return to the <?php echo WPMDJM_CO_NAME; ?> website. You will also receive an email as soon as your payment completes.</p>
                    
					<?php
				}
				
				$pp_options = get_option( 'mdjm_pp_options' );
						
				$mdjm_paypal = new MDJM_PayPal();
				
				$pp_form = $mdjm_paypal->pp_form( $pp_options, $eventinfo );
				
				echo $pp_form;
			}
		}
		
	} // if( is_user_logged_in() )
	
	// If the user is not logged in, show the login prompt
	else	{
		f_mdjm_show_user_login_form();
	} // else (if( is_user_logged_in() ))
	
	/* Print the credit if set */
	add_action( 'wp_footer', 'f_wpmdjm_print_credit' );

?>