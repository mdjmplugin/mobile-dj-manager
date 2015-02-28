<?php
/*
* class-mdjm-pp-gateway.php
* 14/02/2015
* @since 1.1
* MDJM PayPal Integration
*/

	class MDJM_PayPal	{
		/*
		* pp_form
		* 18/02/2015
		* @since 1.1
		* The payment form header content
		*/
		function pp_form( $pp_options, $eventinfo )	{
			global $mdjm_options, $mdjm_client_text;
			
			// Set the currency
			if( !isset( $mdjm_currency ) )	{
				include( WPMDJM_PLUGIN_DIR . '/includes/config.inc.php' );
			}
			
			// Set the correct seperator for links
			$sep = '&amp;';
			if ( get_option( 'permalink_structure' ) )	{
				$sep = '?';
			}
			
			$balance = $eventinfo->cost;
			if( $eventinfo->deposit_status == 'Paid' )	{
				$balance = $eventinfo->cost - $eventinfo->deposit;
			}
			
			// Sandbox or Live?
			$pp_api = 'www.paypal.com/cgi-bin/webscr';
			$pp_email = $pp_options['pp_email'];
			if( isset( $pp_options['pp_sandbox'] ) && $pp_options['pp_sandbox'] == 'Y' )	{
				$pp_api = 'www.sandbox.paypal.com/cgi-bin/webscr';
				if( isset( $pp_options['pp_sandbox_email'] ) && !empty( $pp_options['pp_sandbox_email'] ) )	{
					$pp_email = $pp_options['pp_sandbox_email'];
				}
			}
			
			// The form
			$pp_form = '<script type="text/javascript">' . "\n";
			$pp_form .= 'function changeCustomInput (objDropDown)' . "\n";
			$pp_form .= '{' . "\n";
			$pp_form .= '    var objHidden = document.getElementById("custom");' . "\n";
			$pp_form .= '    objHidden.value = objDropDown.value;' . "\n"; 
			$pp_form .= '}' . "\n";
			$pp_form .= '</script>' . "\n";
			$pp_form .= '<form action="https://' . $pp_api . '" method="post" target="_top">' . "\n";
			$pp_form .= '<input type="hidden" name="cmd" value="_xclick">' . "\n";
			$pp_form .= '<input type="hidden" name="business" value="' . $pp_email . '">' . "\n";
			$pp_form .= '<input type="hidden" name="lc" value="' . get_locale() . '">' . "\n";
			$pp_form .= '<input type="hidden" name="item_name" value="Event ID ' . $eventinfo->event_id . ' (' . date( $mdjm_options['short_date_format'], strtotime( $eventinfo->event_date ) ) . ') - ' . WPMDJM_CO_NAME . '">' . "\n";
			$pp_form .= '<input type="hidden" name="item_number" value="' . $eventinfo->event_id . '">' . "\n";
			$pp_form .= '<input type="hidden" name="custom" id="custom" value="';
			
			if( $eventinfo->deposit_status !='Paid' )	{
				$pp_form .= $mdjm_client_text['deposit_label'];	
			}
			else	{
				$pp_form .= $mdjm_client_text['balance_label'];
			}
			
			$pp_form .= '">' . "\n";
			$pp_form .= '<input type="hidden" name="button_subtype" value="services">' . "\n";
			$pp_form .= '<input type="hidden" name="no_note" value="1">' . "\n";
			$pp_form .= '<input type="hidden" name="no_shipping" value="1">' . "\n";
			$pp_form .= '<input type="hidden" name="rm" value="2">' . "\n";
			$pp_form .= '<input type="hidden" name="return" value="' . get_permalink( $pp_options['pp_redirect'] ) . $sep . 'pp_action=completed&event_id=' . $eventinfo->event_id . '">' . "\n";
			$pp_form .= '<input type="hidden" name="cancel_return" value="' . get_permalink( $pp_options['pp_cancel'] ) . $sep . 'pp_action=cancelled&event_id=' . $eventinfo->event_id . '">' . "\n";
			$pp_form .= '<input type="hidden" name="currency_code" value="' . $mdjm_options['currency'] . '">' . "\n";
			$pp_form .= '<input type="hidden" name="bn" value="PP-BuyNowBF:' . $pp_options['pp_button'] . ':NonHosted">' . "\n";
			$pp_form .= '<input type="hidden" name="notify_url" value="' . home_url() . '/?mdjm-api=MDJM_PAYPAL_GW">' . "\n";
			//$pp_form .= '<input type="hidden" name="invoice" value="' . $pp_options['pp_inv_prefix'] . '0' . $eventinfo->event_id . '">' . "\n";
			
			// Taxes
			if( isset( $pp_options['pp_enable_tax'], $pp_options['pp_tax_type'], $pp_options['pp_tax_rate'] ) && $pp_options['pp_enable_tax'] == 'Y' )	{
				if( $pp_options['pp_tax_type'] == 'percentage' )	{
					$tax = 'tax_rate';
				}
				elseif( $pp_options['pp_tax_type'] == 'fixed' )	{
					$tax = 'tax';
				}
				$pp_form .= '<input type="hidden" name="' . $tax . '" value="' . $pp_options['pp_tax_rate'] . '">' . "\n";
			}

			if( isset( $pp_options['pp_checkout_style'] ) && !empty( $pp_options['pp_checkout_style'] ) )	{
				$pp_form .= '<input type="hidden" name="page_style" value="' . $pp_options['pp_checkout_style'] . '">' . "\n";
			}
			
			$pp_form .= '<input type="hidden" name="on0" value="Paying for">' . "\n";
			
			if( $pp_options['pp_form_layout'] == 'horizontal' )	{
				$pp_form .= '<table>' . "\n";
				$pp_form .= '<tr valign="middle">' . "\n";
				$pp_form .= '<td>';
			}
			
			$pp_form .= $pp_options['pp_label'] . "\n";
			if( $pp_options['pp_form_layout'] == 'vertical' )	{
				$pp_form .=  '<br />' . "\n";
			}
			else	{
				$pp_form .= '</td>' . "\n";
				$pp_form .= '<td>&nbsp;';	
			}
			$pp_form .= '<select name="os0" id="os0" onchange="changeCustomInput(this)">' . "\n";
			$pp_form .= '<option value="' . $mdjm_client_text['deposit_label'] . '"';
			
			$balance = $eventinfo->cost;
			
			if( $eventinfo->deposit_status == 'Paid' )	{
				$pp_form .= ' disabled="disabled"';
				$balance = $eventinfo->cost - $eventinfo->deposit;
			}
			
			$pp_form .= '>' . $mdjm_client_text['deposit_label'] . ' ' . $mdjm_currency[$mdjm_options['currency']] . number_format( $eventinfo->deposit, 2 ) . '</option>' . "\n";
			$pp_form .= '<option value="' . $mdjm_client_text['balance_label'] . '"';
			
			if( $eventinfo->balance_status == 'Paid' )	{
				$pp_body .= ' disabled="disabled"';
			}
			
			$pp_form .= '>' . $mdjm_client_text['balance_label'] . ' ' . $mdjm_currency[$mdjm_options['currency']] . number_format( $balance, 2 ) . '</option>' . "\n";
			$pp_form .= '</select>' . "\n";
			
			if( $pp_options['pp_form_layout'] == 'vertical' )	{
				$pp_form .= '<br /><br />' . "\n";
			}
			else	{
				$pp_form .= '</td>' . "\n";
				$pp_form .= '<td>&nbsp;';
			}
			
			$pp_form .= '<input type="hidden" name="currency_code" value="' . $mdjm_options['currency'] . '">' . "\n";
			$pp_form .= '<input type="hidden" name="option_select0" value="' . $mdjm_client_text['deposit_label'] . '">' . "\n";
			$pp_form .= '<input type="hidden" name="option_amount0" value="' . number_format( $eventinfo->deposit, 2 ) . '">' . "\n";
			$pp_form .= '<input type="hidden" name="option_select1" value="' . $mdjm_client_text['balance_label'] . '">' . "\n";
			$pp_form .= '<input type="hidden" name="option_amount1" value="' . number_format( $balance, 2 ) . '">' . "\n";
			$pp_form .= '<input type="hidden" name="option_index" value="0">' . "\n";
			$pp_form .= '<input type="image" src="https://www.paypalobjects.com/en_GB/i/btn/' . $pp_options['pp_button'] . '" border="0" name="submit" alt="PayPal – The safer, easier way to pay online.">' . "\n";
			
			if( $pp_options['pp_form_layout'] == 'horizontal' )	{
				$pp_form .= '</td>' . "\n";
				$pp_form .= '</tr>' . "\n";
				$pp_form .= '</table>' . "\n";
			}
			
			$pp_form .= '<img alt="" border="0" src="https://www.paypalobjects.com/en_GB/i/scr/pixel.gif" width="1" height="1">' . "\n";
			$pp_form .= '</form>' . "\n";
			
			return $pp_form;
		} // pp_form
						
	} // MDJM_PayPal

?>