<?php
/**
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
				
				if( MDJM_PAYMENTS == false )
					wp_die( 'An error has occured' );
								
				if( !is_user_logged_in() )
					parent::login();
					
				else	{
					$gateways = array(
									'paypal'	=> array( 
													__( 'PayPal', 'mobile-dj-manager' ), 'https://www.paypal.com' ),
									'payfast'	=> array( 
													__( 'PayFast', 'mobile-dj-manager' ), 'https://www.payfast.co.za' ) );
									
					$this->gw = $gateways[MDJM_PAYMENT_GW];
					
					$this->event = isset( $_GET['event_id'] ) ? get_post( $_GET['event_id'] ) : '';
					$post = ( !empty( $this->event ) ? $this->event : '' );
					
					if( empty( $post ) )	{
						return parent::display_notice( 4,
											'<p>' . 
											sprintf( __( 'No event has been defined for payment. %sReturn to the %s%s%s to try again', 
												'mobile-dj-manager' ),
												'<a href="' . $GLOBALS['mdjm']->get_link( MDJM_HOME ) . '">',
												MDJM_COMPANY,
												MDJM_APP,
												'</a>' ) . 
											'</p>'
											);	
					}
					
					// Returning from Payment Gateway?
					if( isset( $_GET['return_action'] ) && !empty( $_GET['return_action'] ) )
						$this->payment_return( $_GET['return_action'] );
						
					$this->payment_wrapper();	
					//$this->display();
				}
				
			} // __construct
			
			/*
			 * Generate and display the page header and welcome text
			 *
			 *
			 *
			 */
			function payment_wrapper()	{
				
				$gateways = array(
								'paypal'	=> array( __( 'PayPal', 'mobile-dj-manager' ), 
													  '<a title="PayPal" href="https://www.paypal.com" target="_blank">' ),
								'payfast'   => array( __( 'PayFast', 'mobile-dj-manager' ), 
													  '<a title="PayFast" href="https://www.payfast.co.za" target="_blank">' )
								);
				
				echo '<!-- ' . 
				sprintf( __( '%s (%s) %s API integration form for online client payments', 'mobile-dj-manager' ),
						 MDJM_NAME,
						 MDJM_VERSION_NUM,
						 $this->gw[0] ) .
				' -->' . "\r\n"; 
				
				$this->dynamic_transaction();
							
				/* -- Welcome text -- */
				echo parent::__text( 'payment_welcome',
								'<p>' . sprintf( __( 'Paying for your event is easy as we accept secure online payments via %s' . 
								'%s%s', 'mobile-dj-manager' ), 
									'<a href="' . $this->gw[1] . '" target="_blank" title="' . $this->gw[0] . '">',
									$this->gw[0],
									'</a>' ) . '.</p>' . "\r\n" . 
								'<p>' . 
								
								sprintf( __( '%s%s%s accept all major credit cards and you do not need to be a ' . 
								'%s%s%s member to process your payment to us', 'mobile-dj-manager' ), 
									'<a href="' . $this->gw[1] . '" target="_blank" title="' . $this->gw[0] . '">',
									$this->gw[0],
									'</a>',
									'<a href="' . $this->gw[1] . '" target="_blank" title="' . $this->gw[0] . '">',
									$this->gw[0],
									'</a>' ) . '</p>' . "\r\n" );
									
				/* -- Intro Text -- */
				echo parent::__text( 'payment_intro',
								'<p>' . __( 'Any outstanding payments for your event are displayed via the drop down list below' ) . '.</p>' . "\r\n" . 
								
								'<p>' .
								 
								sprintf( __( 'Select the payment you wish to make and click the %sPay Now%s button to be redirected to ' . 
								'%s%s\'s%s secure website where you can complete your payment', 'mobile-dj-manager' ), 
								'<strong>', 
								'</strong>', 
								'<a href="' . $this->gw[1] . '" target="_blank" title="' . $this->gw[0] . '">',
								$this->gw[0] ,
								'</a>' ) . '</p>' . "\r\n" . 
								
								'<p>' . 
								
								sprintf( __( 'Upon completion, you can return to the %s website. You will also receive an email as soon as your payment completes',
									'mobile-dj-manager' ), MDJM_COMPANY ) . 
									
								'</p>' . "\r\n" );
				
				/* -- Display the Payment Gateway form -- */				
				$gw_form = $this->gw[0] . '_form';
				$this->$gw_form();
							
				echo '<!-- ' . 
				sprintf( __( 'End %s (%s) %s API integration form for online client payments', 'mobile-dj-manager' ),
						 MDJM_NAME,
						 MDJM_VERSION_NUM,
						 $this->gw[0] ) .
				' -->' . "\r\n"; 
				
			} // payment_wrapper
			
			/*
			 * Add dynamic creation of invoice
			 *
			 *
			 *
			 */
			function dynamic_transaction()	{
			?>
            <script type="text/javascript">
			jQuery(document).ready(function($) 	{
				$("#payment_submit").click(function(event)	{
					event.preventDefault();
					var trans_id = $("#transaction_id");
					$.ajax({
						type: "POST",
						dataType: "json",
						url: "<?php echo admin_url( 'admin-ajax.php' ); ?>",
						data: {
							mdjm_post_type : "<?php echo MDJM_TRANS_POSTS; ?>",
							action : "mdjm_create_post"
						},
						beforeSend: function()	{
							$("#payment_submit").replaceWith('<?php _e( 'Please Wait', 'mobile-dj-manager' ); ?>...<img src="/wp-admin/images/loading.gif" />');
						},
						success: function(response)	{
							if(response.type == "success") {
								trans_id.val(response.id);
								$("#mdjm_payment").submit();
							}
							else	{
								alert( "<?php _e( 'An error has ocurred with your payment. Please contact us', 'mobile-dj-manager' ); ?>" );
								return false;
							}
						}
					});
				});
				//
			});
			</script>
            <?php
			} // dynamic_transaction
			
			/*
			 * Generate and display the PayPal form to allow payments
			 *
			 * @param	obj		$post		The post object for the event
			 *
			 */
			function PayPal_form()	{
				global $mdjm, $mdjm_settings, $post;
				
				$layout = $mdjm_settings['payments']['form_layout'];
				
				// Create required arrays
				$payment_settings = $mdjm_settings['payments'];
				$paypal_settings = $mdjm_settings['paypal'];
				
				// Obtain received payments
				if( !class_exists( 'MDJM_Transactions' ) )
					require_once( MDJM_PLUGIN_DIR . '/admin/includes/class/class-mdjm-transactions.php' );
				
				$mdjm_transactions = new MDJM_Transactions();
				
				$rcvd = $mdjm_transactions->get_transactions( $post->ID, 'mdjm-income' );
				
				// Determine balance and deposit amounts due
				$total = get_post_meta( $post->ID, '_mdjm_event_cost', true );
				$balance = ( !empty( $rcvd ) && $rcvd != '0.00' ? ( $total - $rcvd ) : $total );
				$balance_status = get_post_meta( $post->ID, '_mdjm_event_balance_status', true );
				
				$deposit = get_post_meta( $post->ID, '_mdjm_event_deposit', true );
				
				if( empty( $deposit ) )
					$deposit = '0.00';
				
				if( $balance < $deposit )
					$deposit = $balance;
					
				$deposit_status = ( get_post_meta( $post->ID, '_mdjm_event_deposit_status', true ) == 'Paid' || empty( $deposit ) || $deposit == '0.00' ? 'Paid' : 'Due' );
					
				// Configure PayPal email depending on Sandbox or Live
				$paypal_api = ( !empty( $paypal_settings['enable_sandbox'] ) ? 'www.sandbox.paypal.com/cgi-bin/webscr' : 'www.paypal.com/cgi-bin/webscr' );
				
				$paypal_email = ( !empty( $paypal_settings['sandbox_email'] ) && !empty( $paypal_settings['enable_sandbox'] ) ? 
					$paypal_settings['sandbox_email'] : $paypal_settings['paypal_email'] );
				
				// Now we print out the HTML form for PayPal payments
				
				// Start with the javascript function to populate the required hidden fields
				$payment_form = '<script type="text/javascript">' . "\r\n";
				$payment_form .= 'function changeCustomInput (objDropDown)' . "\r\n";
				$payment_form .= '{' . "\r\n";
				$payment_form .= '    var objHidden = document.getElementById("custom");' . "\r\n";
				$payment_form .= '    var update_amount = document.getElementById("option_amount2");' . "\r\n";
				$payment_form .= '    var manual_input = document.getElementsByName("part_payment")[0];' . "\r\n";
				$payment_form .= '    objHidden.value = objDropDown.value;' . "\r\n";
				$payment_form .= '    if( objDropDown.value == "' . $payment_settings['other_amount_label'] . '" )	{' . "\r\n";
				$payment_form .= '        update_amount.value = manual_input.value;' . "\r\n";
				$payment_form .= '    }' . "\r\n";
				$payment_form .= '}' . "\r\n";
				$payment_form .= 'function setAmount()' . "\r\n";
				$payment_form .= '{' . "\r\n";
				$payment_form .= '    var manual_input = document.getElementsByName("part_payment")[0];' . "\r\n";
				$payment_form .= '    var radio_group = document.getElementsByName("os0")[0];' . "\r\n";
				$payment_form .= '    var update_amount = document.getElementById("option_amount2");' . "\r\n";
				$payment_form .= '    var selected_type = document.getElementById("' . $payment_settings['other_amount_label'] . '");' . "\r\n";
				$payment_form .= '    if( selected_type.checked )	{' . "\r\n";
				$payment_form .= '        update_amount.value = manual_input.value;' . "\r\n";
				$payment_form .= '    }' . "\r\n";
				
				$payment_form .= '}' . "\r\n";
				$payment_form .= '</script>' . "\r\n";
				
				// Begin the form
				$payment_form .= '<form action="https://' . $paypal_api . '" method="post" target="_top" name="mdjm_payment" id="mdjm_payment">' . "\r\n";
				$payment_form .= '<input type="hidden" name="mdjm_post_type" id="mdjm_post_type" value="' . MDJM_TRANS_POSTS . '">' . "\r\n";
				$payment_form .= '<input type="hidden" name="cmd" value="_xclick">' . "\r\n";
				$payment_form .= '<input type="hidden" name="business" value="' . $paypal_email . '">' . "\r\n";
				$payment_form .= '<input type="hidden" name="lc" value="' . get_locale() . '">' . "\r\n";
				
				// This is where we set the payment description / item name
				$payment_form .= '<input type="hidden" name="item_name" value="Event ID ' . MDJM_EVENT_PREFIX. $post->ID . '">' . "\r\n";
				
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
					$mdjm->get_link( $paypal_settings['redirect_success'] ) . 'return_action=completed&event_id=' . $post->ID . '">' . "\r\n";
					
				// Set the return page from PayPal upon cancellation of payment
				$payment_form .= '<input type="hidden" name="cancel_return" value="' . 
					$mdjm->get_link( $paypal_settings['redirect_cancel'] ) . 'return_action=cancelled&event_id=' . $post->ID . '">' . "\r\n";
				
				// Set the currency for payment	
				$payment_form .= '<input type="hidden" name="currency_code" value="' . $payment_settings['currency'] . '">' . "\r\n";
				
				// Set the desired payment button
				$payment_form .= '<input type="hidden" name="bn" value="PP-BuyNowBF:' . $paypal_settings['paypal_button'] . ':NonHosted">' . "\r\n";
				
				// Set the notify URL for API
				$payment_form .= '<input type="hidden" name="notify_url" value="' . home_url() . '/?mdjm-api=MDJM_PAYPAL_GW">' . "\r\n";
				
				// Invoice number - Set during post action
				$payment_form .= '<input type="hidden" name="invoice" id="transaction_id" value="">' . "\r\n";
				
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
					
				$payment_form .= '<input type="hidden" name="on0" id="on0" value="' . __( 'for' ) . '">' . "\r\n";
				
				// Start the table structure. No styling to keep in line with users WP theme
				if( $layout == 'horizontal' )	{
					$payment_form .= '<table>' . "\r\n";
					$payment_form .= '<tr valign="middle">' . "\r\n";
					$payment_form .= '<td>';	
				}
				
				$payment_form .= '<span style="font-weight: bold;">' . $payment_settings['payment_label'] . '</span>' . "\r\n";
				if( $layout == 'vertical' )
					$payment_form .=  '<br />' . "\r\n";
				
				else	{
					$payment_form .= '</td>' . "\r\n";
					$payment_form .= '<td>';	
				}
				
				$payment_form .= '<input type="radio" name="os0" id="' . MDJM_DEPOSIT_LABEL . '" value="' . MDJM_DEPOSIT_LABEL . 
					'" onclick="changeCustomInput(this)"' . ( $deposit_status == 'Paid' ? ' disabled="disabled"' : '' ) . 
					checked( $deposit_status, 'Due', false ) . '>';
				$payment_form .= '&nbsp;<label for="' . MDJM_DEPOSIT_LABEL . '">' . MDJM_DEPOSIT_LABEL . ( $deposit_status == 'Due' ? ' - ' 
					. display_price( $deposit, true ) : '' ) . '</label>' . "\r\n";
					
				$payment_form .= '<br />' . "\r\n";
					
				$payment_form .= '<input type="radio" name="os0" id="' . MDJM_BALANCE_LABEL . '" value="' . MDJM_BALANCE_LABEL . 
					'" onclick="changeCustomInput(this)"' . ( $balance_status == 'Paid' ? ' disabled="disabled"' : '' ) . 
					( $deposit_status == 'Paid' && $balance_status == 'Due' ? ' checked="checked"' : '' ) . '>';
				$payment_form .= '&nbsp;<label for="' . MDJM_BALANCE_LABEL . '">' . MDJM_BALANCE_LABEL . ( $balance_status == 'Due' ? ' - ' 
					. display_price( $balance, true ) : '' ) . '</label>' . "\r\n";
					
				$payment_form .= '<br />' . "\r\n";
				
				$payment_form .= '<input type="radio" name="os0" id="' . $payment_settings['other_amount_label'] . '" value="' . $payment_settings['other_amount_label'] . 
					'"  onclick="changeCustomInput(this); document.getElementById(\'part_payment\').select();">' . "\r\n";
				$payment_form .= '<label for="part_payment">' . $payment_settings['other_amount_label'] . ':</label>' . "\r\n";
				$payment_form .= MDJM_CURRENCY . '&nbsp;<input type="text" style="max-width: 80px;" name="part_payment" id="part_payment" ' . 
					' placeholder="0.00" value="' . number_format( $payment_settings['other_amount_default'], 2 ) . '" onkeyup="setAmount();" onclick="document.getElementById(\'' . $payment_settings['other_amount_label'] . 
					'\').checked = true;" autocomplete="off">' . "\r\n";
								
				if( $layout == 'vertical' )
					$payment_form .= '<br />' . "\r\n";
			
				else	{
					$payment_form .= '</td>' . "\r\n";
					$payment_form .= '<td>&nbsp;';
				}
				
				$payment_form .= '<input type="hidden" name="currency_code" value="' . $payment_settings['currency'] . '">' . "\r\n";
				$payment_form .= '<input type="hidden" name="option_select0" value="' . MDJM_DEPOSIT_LABEL . '">' . "\r\n";
				$payment_form .= '<input type="hidden" name="option_amount0" value="' . number_format( $deposit, 2 ) . '">' . "\r\n";
				$payment_form .= '<input type="hidden" name="option_select1" value="' . MDJM_BALANCE_LABEL . '">' . "\r\n";
				$payment_form .= '<input type="hidden" name="option_amount1" value="' . number_format( $balance, 2 ) . '">' . "\r\n";				
				$payment_form .= '<input type="hidden" name="option_select2" value="' . $payment_settings['other_amount_label'] . '">' . "\r\n";
				$payment_form .= '<input type="hidden" name="option_amount2" id="option_amount2" value="' . number_format( $payment_settings['other_amount_default'], 2 ) . '">' . "\r\n";	
				
				$payment_form .= '<input type="hidden" name="option_index" value="0">' . "\r\n";
				
				if( $paypal_settings['paypal_button'] == 'html' )
					$payment_form .= '<input type="submit" name="submit" id="payment_submit" value="' . esc_attr( $paypal_settings['button_text'] ) . '">' . "\r\n";
				
				else
					$payment_form .= '<input type="image" src="https://www.paypalobjects.com/en_GB/i/btn/' . $paypal_settings['paypal_button'] . 
					'" border="0" name="submit" id="payment_submit" alt="PayPal – The safer, easier way to pay online.">' . "\r\n";
				
				if( $layout == 'horizontal' )	{
					$payment_form .= '</td>' . "\r\n";
					$payment_form .= '</tr>' . "\r\n";
					$payment_form .= '</table>' . "\r\n";
				}
				
				$payment_form .= '<img alt="" border="0" src="https://www.paypalobjects.com/en_GB/i/scr/pixel.gif" width="1" height="1">' . "\r\n";
				$payment_form .= '</form>' . "\r\n";
				
				echo $payment_form;
			} // PayPal_form
			
			/*
			 * Generate and display the PayFast form to allow payments
			 *
			 * @param	obj		$post		The post object for the event
			 *
			 */
			function PayFast_form()	{
				global $mdjm, $mdjm_settings, $post, $my_mdjm;
				
				$layout = $mdjm_settings['payments']['form_layout'];
				
				// Create required arrays
				$payment_settings = $mdjm_settings['payments'];
				$payfast_settings = $mdjm_settings['payfast'];
				
				// Obtain received payments
				if( !class_exists( 'MDJM_Transactions' ) )
					require_once( MDJM_PLUGIN_DIR . '/admin/includes/class/class-mdjm-transactions.php' );
				
				$mdjm_transactions = new MDJM_Transactions();
				
				$rcvd = $mdjm_transactions->get_transactions( $post->ID, 'mdjm-income' );
				
				// Determine balance and deposit amounts due
				$total = get_post_meta( $post->ID, '_mdjm_event_cost', true );
				$balance = ( !empty( $rcvd ) && $rcvd != '0.00' ? ( $total - $rcvd ) : $total );
				$balance_status = get_post_meta( $post->ID, '_mdjm_event_balance_status', true );
				
				$deposit = get_post_meta( $post->ID, '_mdjm_event_deposit', true );
				
				if( empty( $deposit ) )
					$deposit = '0.00';
				
				if( $balance < $deposit )
					$deposit = $balance;
				
				$deposit_status = ( get_post_meta( $post->ID, '_mdjm_event_deposit_status', true ) == 'Paid' || empty( $deposit ) || $deposit == '0.00' ? 'Paid' : 'Due' );
					
				// Configure PayFast email depending on Sandbox or Live
				$payfast_api = ( !empty( $payfast_settings['enable_pf_sandbox'] ) ? 'sandbox.payfast.co.za/eng/process' : 'www.payfast.co.za/eng/process' );
				
				$payfast_merchant_id = ( !empty( $payfast_settings['sandbox_merchant_id'] ) && !empty( $payfast_settings['enable_pf_sandbox'] ) ? 
					$payfast_settings['sandbox_merchant_id'] : $payfast_settings['merchant_id'] );
					
				$payfast_merchant_key = ( !empty( $payfast_settings['sandbox_merchant_key'] ) && !empty( $payfast_settings['enable_pf_sandbox'] ) ? 
					$payfast_settings['sandbox_merchant_key'] : $payfast_settings['merchant_key'] );
				
				// Now we print out the HTML form for PayFast payments
				
				// Start with the javascript function to populate the required hidden fields
				$payment_form = '<script type="text/javascript">' . "\r\n";
				$payment_form .= 'function changeCustomInput (objDropDown)	{' . "\r\n";
				$payment_form .= '    var objHidden = document.getElementById("item_name");' . "\r\n";
				$payment_form .= '    var update_amount = document.getElementById("amount");' . "\r\n";
				$payment_form .= '    var manual_input = document.getElementsByName("part_payment")[0];' . "\r\n";
				$payment_form .= '    var item_str = document.getElementById("custom_str1");' . "\r\n";
				$payment_form .= '    item_str.value = objDropDown.value;' . "\r\n";
				$payment_form .= '    objHidden.value = objDropDown.value + " for Event ID ' . $post->ID . '";' . "\r\n";
				$payment_form .= '    if( objDropDown.value == "' . MDJM_DEPOSIT_LABEL . '" )	{' . "\r\n";
				$payment_form .= '        update_amount.value = "' . number_format( $deposit, 2 ) . '";' . "\r\n";
				$payment_form .= '    }' . "\r\n";
				$payment_form .= '    else if( objDropDown.value == "' . MDJM_BALANCE_LABEL . '" )	{' . "\r\n";
				$payment_form .= '        update_amount.value = "' . number_format( $balance, 2 ) . '";' . "\r\n";
				$payment_form .= '    }' . "\r\n";
				$payment_form .= '    else if( objDropDown.value == "' . $payment_settings['other_amount_label'] . '" )	{' . "\r\n";
				$payment_form .= '        update_amount.value = manual_input.value;' . "\r\n";
				$payment_form .= '    }' . "\r\n";
				$payment_form .= '}' . "\r\n";
				$payment_form .= 'function setAmount()	{' . "\r\n";
				$payment_form .= '    var manual_input = document.getElementsByName("part_payment")[0];' . "\r\n";
				$payment_form .= '    var radio_group = document.getElementsByName("os0")[0];' . "\r\n";
				$payment_form .= '    var update_amount = document.getElementById("amount");' . "\r\n";
				$payment_form .= '    var selected_type = document.getElementById("' . $payment_settings['other_amount_label'] . '");' . "\r\n";
				$payment_form .= '    if( selected_type.checked )	{' . "\r\n";
				$payment_form .= '        update_amount.value = manual_input.value;' . "\r\n";
				$payment_form .= '    }' . "\r\n";
				$payment_form .= '}' . "\r\n";
				$payment_form .= '</script>' . "\r\n";
				
				// Begin the form
				$payment_form .= '<form action="https://' . $payfast_api . '" method="post" target="_top" name="mdjm_payment" id="mdjm_payment">' . "\r\n";
				
				// For MDJM
				$payment_form .= '<input type="hidden" name="mdjm_post_type" id="mdjm_post_type" value="' . MDJM_TRANS_POSTS . '">' . "\r\n";
				$payment_form .= '<input type="hidden" name="custom_int1" id="custom_int1" value="' . $post->ID . '">' . "\r\n";
				$payment_form .= '<input type="hidden" name="custom_str1" id="custom_str1" value="' . 
				( $deposit_status != 'Paid' ? MDJM_DEPOSIT_LABEL : MDJM_BALANCE_LABEL ) . '">' . "\r\n";
				
				/* -- Receiver details -- */
				$payment_form .= '<input type="hidden" name="merchant_id" value="' . $payfast_merchant_id . '">' . "\r\n";
				$payment_form .= '<input type="hidden" name="merchant_key" value="' . $payfast_merchant_key . '">' . "\r\n";
				$payment_form .= '<input type="hidden" name="return_url" value="' . 
					$mdjm->get_link( $payfast_settings['redirect_pf_success'] ) . 'return_action=completed&event_id=' . $post->ID . '">' . "\r\n";
					
				$payment_form .= '<input type="hidden" name="cancel_url" value="' . 
					$mdjm->get_link( $payfast_settings['redirect_pf_cancel'] ) . 'return_action=cancelled&event_id=' . $post->ID . '">' . "\r\n";
					
				$payment_form .= '<input type="hidden" name="notify_url" value="' . home_url() . '/?mdjm-api=MDJM_PAYFAST_GW">' . "\r\n";
				
				/* -- Client details -- */
				$payment_form .= '<input type="hidden" name="name_first" value="' . $my_mdjm['me']->first_name . '">' . "\r\n";
				$payment_form .= '<input type="hidden" name="name_last" value="' . $my_mdjm['me']->last_name . '">' . "\r\n";
				$payment_form .= '<input type="hidden" name="email_address" value="' . $my_mdjm['me']->user_email . '">' . "\r\n";
				
				/* -- Transaction details -- */
				$payment_form .= '<input type="hidden" name="m_payment_id" id="transaction_id" value="">' . "\r\n";
				$payment_form .= '<input type="hidden" name="amount" id="amount" value="' . 
					( $deposit_status != 'Paid' ? number_format( $deposit, 2 ) : number_format( $balance, 2 ) ) . '">' . "\r\n";
				$payment_form .= '<input type="hidden" name="item_name" id="item_name" value="' . 
					( $deposit_status != 'Paid' ? MDJM_DEPOSIT_LABEL : MDJM_BALANCE_LABEL ) . ' for Event ID ' . MDJM_EVENT_PREFIX . $post->ID . '">' . "\r\n";
				$payment_form .= '<input type="hidden" name="item_description" value="Event ID ' . MDJM_EVENT_PREFIX . $post->ID . ' (' . 
					date( MDJM_SHORTDATE_FORMAT, strtotime( get_post_meta( $post->ID, '_mdjm_event_date', true ) ) ) . 
					') - ' . MDJM_COMPANY . '">' . "\r\n";
					
				/* -- Transaction options -- */
				if( !empty( $payfast_settings['email_confirmation'] ) )	{
					$payment_form .= '<input type="hidden" name="email_confirmation" value="1">' . "\r\n";
					$payment_form .= '<input type="hidden" name="confirmation_address" value="' . $payfast_settings['email_confirmation'] . '">' . "\r\n";
				}
				
				/* -- Transaction security -- */
				$payment_form .= '<input type="hidden" name="signature" value="">' . "\r\n";
								
				// Start the table structure. No styling to keep in line with users WP theme
				if( $layout == 'horizontal' )	{
					$payment_form .= '<table>' . "\r\n";
					$payment_form .= '<tr valign="middle">' . "\r\n";
					$payment_form .= '<td>';	
				}
				
				$payment_form .= '<span style="font-weight: bold;">' . $payment_settings['payment_label'] . '</span>' . "\r\n";
				if( $layout == 'vertical' )
					$payment_form .=  '<br />' . "\r\n";
				
				else	{
					$payment_form .= '</td>' . "\r\n";
					$payment_form .= '<td>';	
				}
				
				$payment_form .= '<input type="radio" name="os0" id="' . MDJM_DEPOSIT_LABEL . '" value="' . MDJM_DEPOSIT_LABEL . 
					'" onclick="changeCustomInput(this)"' . ( $deposit_status == 'Paid' ? ' disabled="disabled"' : '' ) . 
					checked( $deposit_status, 'Due', false ) . '>';
				$payment_form .= '&nbsp;<label for="' . MDJM_DEPOSIT_LABEL . '">' . MDJM_DEPOSIT_LABEL . ( $deposit_status == 'Due' ? ' - ' 
					. display_price( $deposit, true ) : '' ) . '</label>' . "\r\n";
					
				$payment_form .= '<br />' . "\r\n";
					
				$payment_form .= '<input type="radio" name="os0" id="' . MDJM_BALANCE_LABEL . '" value="' . MDJM_BALANCE_LABEL . 
					'" onclick="changeCustomInput(this)"' . ( $balance_status == 'Paid' ? ' disabled="disabled"' : '' ) . 
					( $deposit_status == 'Paid' && $balance_status == 'Due' ? ' checked="checked"' : '' ) . '>';
				$payment_form .= '&nbsp;<label for="' . MDJM_BALANCE_LABEL . '">' . MDJM_BALANCE_LABEL . ( $balance_status == 'Due' ? ' - ' 
					. display_price( $balance, true ) : '' ) . '</label>' . "\r\n";
					
				$payment_form .= '<br />' . "\r\n";
				
				$payment_form .= '<input type="radio" name="os0" id="' . $payment_settings['other_amount_label'] . '" value="' . $payment_settings['other_amount_label'] . 
					'" onclick="changeCustomInput(this); document.getElementById(\'part_payment\').select();">' . "\r\n";
				$payment_form .= '<label for="part_payment">' . $payment_settings['other_amount_label'] . ':</label>' . "\r\n";
				$payment_form .= MDJM_CURRENCY . '&nbsp;<input type="text" style="max-width: 80px;" name="part_payment" id="part_payment" ' . 
					' placeholder="0.00" value="' . number_format( $payment_settings['other_amount_default'], 2 ) . 
					'" onkeyup="setAmount();" onclick="document.getElementById(\'' . $payment_settings['other_amount_label'] . '\').checked = true;" autocomplete="off">' . "\r\n";
								
				if( $layout == 'vertical' )
					$payment_form .= '<br />' . "\r\n";
			
				else	{
					$payment_form .= '</td>' . "\r\n";
					$payment_form .= '<td>&nbsp;';
				}
								
				if( $payfast_settings['payfast_button'] == 'html' )
					$payment_form .= '<input type="submit" name="submit" id="payment_submit" value="' . esc_attr( $payfast_settings['button_text'] ) . '">' . "\r\n";
				
				else
					$payment_form .= '<input type="image" src="https://www.payfast.co.za/images/buttons/' . $payfast_settings['button'] . 
					'" border="0" name="submit" id="payment_submit">' . "\r\n";
				
				if( $layout == 'horizontal' )	{
					$payment_form .= '</td>' . "\r\n";
					$payment_form .= '</tr>' . "\r\n";
					$payment_form .= '</table>' . "\r\n";
				}
				
				$payment_form .= '</form>' . "\r\n";
				
				echo $payment_form;
			} // PayFast_form
			
			/*
			 * Actions for when the user returns to this page from PayPal
			 * after they have completed or cancelled a payment
			 *
			 *
			 */
			function payment_return( $action )	{
				global $mdjm;
				
				// Display message for a completed transaction
				if( $action == 'completed' )	{
					parent::display_notice( 2,
											parent::__text( 'payment_complete',
															'<p>' . __( 'Thank you. Your payment has completed successfully', 'mobile-dj-manager' ) . '.</p>' . "\r\n" .
															'<p>' . __( 'You will shortly receive an email from us (remember to check your junk email folder) confirming the ' . 
															'payment and detailing next steps for your event', 'mobile-dj-manager' ) . '.</p>' . "\r\n" .
															'<p>' . sprintf( __( '%sPlease note%s that it can take a few minutes for our systems to be updated by ' .
															'%s%s%s, and therefore your payment may not have ' .
															'registered below as yet. Once you receive the payment confirmation email from us, the payment will be ' . 
															'updated on our systems', 'mobile-dj-manager' ), 
															'<strong>',
															'</strong>',
															'<a href="' . $this->gw[1] . '" target="_blank" title="' . $this->gw[0] . '">',
															$this->gw[0],
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
			} // payment_return
			
		} // class
		
	} // if( !class_exists( 'MDJM_Payment' ) )
	
/* -- Insantiate the MDJM_Payment class -- */
	$mdjm_payment = new MDJM_Payment();
?>