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
		function pp_form( $post )	{
			global $clientzone, $mdjm, $mdjm_settings;
						
			$balance = get_post_meta( $post->ID, '_mdjm_event_cost', true );
			if( get_post_meta( $post->ID, '_mdjm_event_deposit_status', true ) == 'Paid' )	{
				$balance = get_post_meta( $post->ID, '_mdjm_event_cost', true ) - get_post_meta( $post->ID, '_mdjm_event_deposit', true );
			}
			
			// Sandbox or Live?
			$pp_api = 'www.paypal.com/cgi-bin/webscr';
			$pp_email = $mdjm_settings['payments']['pp_email'];
			if( isset( $mdjm_settings['payments']['pp_sandbox'] ) )	{
				$pp_api = 'www.sandbox.paypal.com/cgi-bin/webscr';
				$pp_email = !empty( $mdjm_settings['payments']['pp_sandbox_email'] ) ? $mdjm_settings['payments']['pp_sandbox_email'] : $mdjm_settings['payments']['pp_email'];
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
			$pp_form .= '<input type="hidden" name="item_name" value="Event ID ' . $post->ID . ' (' . 
				date( MDJM_SHORTDATE_FORMAT, strtotime( get_post_meta( $post->ID, '_mdjm_event_date', true ) ) ) . 
				') - ' . MDJM_COMPANY . '">' . "\n";
			$pp_form .= '<input type="hidden" name="item_number" value="' . $post->ID . '">' . "\n";
			$pp_form .= '<input type="hidden" name="custom" id="custom" value="';
			
			if( get_post_meta( $post->ID, '_mdjm_event_deposit_status', true ) !='Paid' )	{
				$pp_form .= MDJM_DEPOSIT_LABEL;	
			}
			else	{
				$pp_form .= MDJM_BALANCE_LABEL;
			}
			
			$pp_form .= '">' . "\n";
			$pp_form .= '<input type="hidden" name="button_subtype" value="services">' . "\n";
			$pp_form .= '<input type="hidden" name="no_note" value="1">' . "\n";
			$pp_form .= '<input type="hidden" name="no_shipping" value="1">' . "\n";
			$pp_form .= '<input type="hidden" name="rm" value="2">' . "\n";
			$pp_form .= '<input type="hidden" name="return" value="' . $mdjm->get_link( $mdjm_settings['payments']['pp_redirect'] ) . 'pp_action=completed&event_id=' . $post->ID . '">' . "\n";
			$pp_form .= '<input type="hidden" name="cancel_return" value="' . $mdjm->get_link( $mdjm_settings['payments']['pp_cancel'] ) . 'pp_action=cancelled&event_id=' . $post->ID . '">' . "\n";
			$pp_form .= '<input type="hidden" name="currency_code" value="' . $mdjm_settings['main']['currency'] . '">' . "\n";
			$pp_form .= '<input type="hidden" name="bn" value="PP-BuyNowBF:' . $mdjm_settings['payments']['pp_button'] . ':NonHosted">' . "\n";
			$pp_form .= '<input type="hidden" name="notify_url" value="' . home_url() . '/?mdjm-api=MDJM_PAYPAL_GW">' . "\n";
			//$pp_form .= '<input type="hidden" name="invoice" value="' . MDJM_EVENT_PREFIX . '0' . $eventinfo->event_id . '">' . "\n";
			
			// Taxes
			if( isset( $mdjm_settings['payments']['pp_enable_tax'], $mdjm_settings['payments']['pp_tax_type'], $mdjm_settings['payments']['pp_tax_rate'] ) 
				&& $mdjm_settings['payments']['pp_enable_tax'] == 'Y' )	{
				if( $mdjm_settings['payments']['pp_tax_type'] == 'percentage' )	{
					$tax = 'tax_rate';
				}
				elseif( $mdjm_settings['payments']['pp_tax_type'] == 'fixed' )	{
					$tax = 'tax';
				}
				$pp_form .= '<input type="hidden" name="' . $tax . '" value="' . $mdjm_settings['payments']['pp_tax_rate'] . '">' . "\n";
			}

			if( !empty( $mdjm_settings['payments']['pp_checkout_style'] ) )	{
				$pp_form .= '<input type="hidden" name="page_style" value="' . $mdjm_settings['payments']['pp_checkout_style'] . '">' . "\n";
			}
			
			$pp_form .= '<input type="hidden" name="on0" value="Paying for">' . "\n";
			
			if( $mdjm_settings['payments']['pp_form_layout'] == 'horizontal' )	{
				$pp_form .= '<table>' . "\n";
				$pp_form .= '<tr valign="middle">' . "\n";
				$pp_form .= '<td>';
			}
			
			$pp_form .= $mdjm_settings['payments']['pp_label'] . "\n";
			if( $mdjm_settings['payments']['pp_form_layout'] == 'vertical' )	{
				$pp_form .=  '<br />' . "\n";
			}
			else	{
				$pp_form .= '</td>' . "\n";
				$pp_form .= '<td>&nbsp;';	
			}
			$pp_form .= '<select name="os0" id="os0" onchange="changeCustomInput(this)">' . "\n";
			$pp_form .= '<option value="' . MDJM_DEPOSIT_LABEL . '"';
			
			$balance = get_post_meta( $post->ID, '_mdjm_event_cost', true );
			
			if( get_post_meta( $post->ID, '_mdjm_event_deposit_status', true ) == 'Paid' )	{
				$pp_form .= ' disabled="disabled"';
				$balance = get_post_meta( $post->ID, '_mdjm_event_cost', true ) - get_post_meta( $post->ID, '_mdjm_event_deposit', true );
			}
			
			$pp_form .= '>' . MDJM_DEPOSIT_LABEL . ' ' . MDJM_CURRENCY . number_format( get_post_meta( $post->ID, '_mdjm_event_deposit', true ), 2 ) . '</option>' . "\n";
			$pp_form .= '<option value="' . MDJM_BALANCE_LABEL . '"';
			
			if( get_post_meta( $post->ID, '_mdjm_event_balance_status', true ) == 'Paid' )	{
				$pp_body .= ' disabled="disabled"';
			}
			
			$pp_form .= '>' . MDJM_BALANCE_LABEL . ' ' . MDJM_CURRENCY . number_format( $balance, 2 ) . '</option>' . "\n";
			$pp_form .= '</select>' . "\n";
			
			if( $mdjm_settings['payments']['pp_form_layout'] == 'vertical' )	{
				$pp_form .= '<br /><br />' . "\n";
			}
			else	{
				$pp_form .= '</td>' . "\n";
				$pp_form .= '<td>&nbsp;';
			}
			
			$pp_form .= '<input type="hidden" name="currency_code" value="' . $mdjm_settings['main']['currency'] . '">' . "\n";
			$pp_form .= '<input type="hidden" name="option_select0" value="' . MDJM_DEPOSIT_LABEL . '">' . "\n";
			$pp_form .= '<input type="hidden" name="option_amount0" value="' . number_format( get_post_meta( $post->ID, '_mdjm_event_deposit', true ), 2 ) . '">' . "\n";
			$pp_form .= '<input type="hidden" name="option_select1" value="' . MDJM_BALANCE_LABEL . '">' . "\n";
			$pp_form .= '<input type="hidden" name="option_amount1" value="' . number_format( $balance, 2 ) . '">' . "\n";
			$pp_form .= '<input type="hidden" name="option_index" value="0">' . "\n";
			$pp_form .= '<input type="image" src="https://www.paypalobjects.com/en_GB/i/btn/' . $mdjm_settings['payments']['pp_button'] . 
				'" border="0" name="submit" alt="PayPal – The safer, easier way to pay online.">' . "\n";
			
			if( $mdjm_settings['payments']['pp_form_layout'] == 'horizontal' )	{
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