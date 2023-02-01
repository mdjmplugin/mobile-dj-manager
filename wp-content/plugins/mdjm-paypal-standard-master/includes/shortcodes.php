<?php
/**
 * THIS FILE IS DEPRECATED AND REMAINS FOR BACKWARDS COMPATIBILITY ONLY.
 * Contains all shortcode related functions.
 *
 * @package     MDJM
 * @subpackage  Shortcodes
 * @since       1.0
 */

/**
 * THIS FUNCTION IS DEPRECATED AND HERE FOR BACKWARDS COMPATIBILITY ONLY
 * Add the PG Shortcodes to the MDJM Shortcodes
 *
 * @param   arr     $pairs      The existing pairs array
 *
 * @return  arr     $pairs      The updated pairs array
 */
function mdjm_paypal_bw_compat_shortcodes( $pairs ) {
	$pairs['Payments'] = MDJM_PAYPAL_STD_DIR . '/client-zone/mdjm-pg-page.php';
		
	return $pairs;
} // mdjm_paypal_bw_compat_shortcodes
add_filter( 'mdjm_filter_shortcode_pairs', 'mdjm_paypal_bw_compat_shortcodes' );

/**
 * THIS FUNCTION IS DEPRECATED AND HERE FOR BACKWARDS COMPATIBILITY ONLY
 * Execute the PG Shortcode by instantiating the MDJM_PG_Page class.
 * The MDJM_PG_Page needs to be available.
 *
 * @param   arr     $atts       The attributes passed to the shortcode
 *
 * @return                      Instantiate the class
 */
function mdjm_paypal_bw_compat_do_shortcodes( $atts ) {
	if ( function_exists( 'mdjm_shortcode_payment' ) ) {
		return mdjm_shortcode_payment( $atts );
	} else {
		return mdjm_shortcode_payments( $atts );
	}
} // mdjm_paypal_bw_compat_do_shortcodes
add_action( 'mdjm_paypal_execute_shortcode', 'mdjm_paypal_bw_compat_do_shortcodes' );

/**
 * MDJM Payments Shortcode.
 *
 * THIS FUNCTION IS DEPRECATED AND HERE FOR BACKWARDS COMPATIBILITY ONLY
 * Displays the MDJM payments page to allow the client to make payments towards events.
 * 
 * @since   1.0
 *
 * @return  string
 */
function mdjm_shortcode_payments( $atts ) {

	if ( is_user_logged_in() ) {

		global $mdjm_event;

		if ( isset( $_GET['event_id'] ) ) { // phpcs:ignore: WordPress.Security.NonceVerification.Recommended
			$event_id = absint( wp_unslash( $_GET['event_id'] ) ); // phpcs:ignore: WordPress.Security.NonceVerification.Recommended
		} else {
			$next_event = mdjm_get_clients_next_event( get_current_user_id() );
			
			if ( $next_event ) {
				$event_id = $next_event[0]->ID;
			}
		}
		
		if ( ! isset( $event_id ) ) {
			return __( "Ooops! There seems to be a slight issue and we've been unable to find your event", 'mobile-dj-manager' );
		}
		
		$mdjm_event = new MDJM_Event( $event_id );
					
		if ( $mdjm_event ) {

			return mdjm_payment_form();

		} else {
			return __( "Ooops! There seems to be a slight issue and we've been unable to find your event", 'mobile-dj-manager' );
		}
		
		// Reset global var
		$mdjm_event = '';
		
	} else {
		echo mdjm_login_form(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}

} // mdjm_shortcode_payments
