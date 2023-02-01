<?php

/**
 * Registers the PayPal Standard Payment Gateways
 *
 * @since       1.0
 * @param       $existing_gw    arr     The existing registered payment gateways
 * @return      array
*/
function mdjm_paypal_register_gateway( $gateways ) {
	$register_gateways = array(
		'paypal' => array(
			'admin_label'   => __( 'PayPal Standard', 'mobile-dj-manager' ),
			'payment_label' => __( 'PayPal', 'mobile-dj-manager' ),
			'supports'      => array(),
		),
	);
	
	$gateways = array_merge( $gateways, $register_gateways );

	return $gateways;

} // mdjm_paypal_register_gateway
add_filter( 'mdjm_payment_gateways', 'mdjm_paypal_register_gateway', 10, 1 );

/**
 * Registers the PayPal Standard options section
 *
 * @since       1.3
 * @param       $sections array the existing plugin sections
 * @return      array
*/
function mdjm_paypal_register_section( $sections ) {
	if ( mdjm_is_gateway_active( 'paypal' ) ) {
		$sections['paypal'] = mdjm_get_gateway_admin_label( 'paypal' );
	}
	
	return $sections;
} // mdjm_paypal_register_section
add_filter( 'mdjm_settings_sections_payments', 'mdjm_paypal_register_section', 10, 1 );

/**
 * Registers the PayPal Standard options in Payments settings
 *
 * @since       1.3
 * @param       $settings array the existing plugin settings
 * @return      array
*/
function mdjm_paypal_settings( $settings ) {

	$paypal_settings = array(
		/** PayPal Settings */
		'paypal' => array(
			array(
				'id'   => 'paypal_header',
				'name' => '<h3>' . __( 'PayPal Configuration', 'mdjm-paypal-standard' ) . '<h3>',
				'desc' => '',
				'type' => 'header',
			),
			array(
				'id'   => 'paypal_email',
				'name' => __( 'PayPal Email', 'mdjm-paypal-standard' ),
				'desc' => __( 'Your registered PayPal email address is needed before you can take payments via your website', 'mdjm-paypal-standard' ),
				'type' => 'text',
				'size' => 'regular',
				'std'  => get_bloginfo( 'admin_email' ),
			),
			array(
				'id'   => 'paypal_page_style',
				'name' => __( 'Checkout Page Style', 'mdjm-paypal-standard' ),
				'desc' => sprintf(
					__( "If you have created a custom %1\$sPayPal Checkout Page%2\$s, enter it's ID here to use it", 'mdjm-paypal-standard' ),
					"<a href='https://www.paypal.com/customize' target='_blank' title='PayPal's Custom Payment Pages: An Overview'>",
					'</a>'
				),
				'type' => 'text',
				'size' => 'regular',
			),
			array(
				'id'      => 'paypal_redirect_success',
				'name'    => __( 'Redirect Successful Payment To', 'mdjm-paypal-standard' ),
				'desc'    => __( 'Where do you want your Client redirected to upon successful payment?', 'mdjm-paypal-standard' ),
				'type'    => 'select',
				'options' => mdjm_list_pages(),
				'std'     => mdjm_get_option( 'payments_page' ),
			),
			array(
				'id'      => 'paypal_redirect_cancel',
				'name'    => __( 'Redirect Cancelled Payment To', 'mdjm-paypal-standard' ),
				'desc'    => __( 'Where do you want your Client redirected to upon successful payment?', 'mdjm-paypal-standard' ),
				'type'    => 'select',
				'options' => mdjm_list_pages(),
				'std'     => mdjm_get_option( 'payments_page' ),
			),
			array(
				'id'   => 'paypal_sandbox_header',
				'name' => '<h3>' . __( 'PayPal Sandbox', 'mdjm-paypal-standard' ) . '<h3>',
				'desc' => '',
				'type' => 'header',
			),
			array(
				'id'   => 'paypal_enable_sandbox',
				'name' => __( 'Enable Sandbox?', 'mdjm-paypal-standard' ),
				'type' => 'checkbox',
				'desc' => sprintf( 
					__( 'Enable only to test payments. You can sign up for a developer account %1$shere%2$s', 'mdjm-paypal-standard' ),
					'<a href="https://developer.paypal.com/" target="_blank">',
					'</a>'
				),
			),
			array(
				'id'   => 'paypal_sandbox_email',
				'name' => __( 'PayPal Sandbox Email', 'mdjm-paypal-standard' ),
				'desc' => __( 'If using PayPal Sandbox, enter your sandbox "Facilitator" email here', 'mdjm-paypal-standard' ),
				'type' => 'text',
				'size' => 'regular',
			),
		),
	);
	
	return array_merge( $settings, $paypal_settings );
} // mdjm_paypal_settings
add_filter( 'mdjm_settings_payments', 'mdjm_paypal_settings' );
