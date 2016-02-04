<?php
/**
 * This template is used when the current logged in client has no events.
 *
 * @version			1.0
 * @author			Mike Howard
 * @since			1.3
 * @content_tag		{client_*}
 * @shortcodes		Supported
 *
 * Do not customise this file!
 * If you wish to make changes, copy this file to your theme, or child theme, directory /theme/templates/event-none.php
 */
?>
<div id="client-no-events">
	<p><?php _e( 'Hey', 'mobile-dj-manager' ); ?> {client_firstname}, <?php printf( __( 'welcome to the %s %s.', 'mobile-dj-manager' ),
		mdjm_get_option( 'company_name' ),
		mdjm_get_option( 'app_name', __( 'Client Zone', 'mobile-dj-manager' ) ) ); ?></p>
    
    <p><?php _e( 'You do not appear to have any active events booked with us.', 'mobile-dj-manager' ); ?>
    
    <p><?php printf( 
		__( 'If you are ready to plan your next event, contact us <a href="%s">here</a>.', 'mobile-dj-manager' ),
		mdjm_get_formatted_url( mdjm_get_option( 'contact_page', '#' ), false ) ); ?></p>        
</div>