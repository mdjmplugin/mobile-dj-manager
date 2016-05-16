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
 * If you wish to make changes, copy this file to your theme directory /theme/mdjm-templates/event/event-none.php
 */
?>
<div id="client-no-events">
	<p><?php _e( 'Hey', 'mobile-dj-manager' ); ?> {client_firstname}, <?php printf( __( 'welcome to the %s %s.', 'mobile-dj-manager' ),
		'{company_name}',
		'{application_name}' ); ?></p>
    
    <p><?php printf( __( 'You do not currently have any active %s booked with us.', 'mobile-dj-manager' ), mdjm_get_label_plural() ); ?></p>
    
    <p><?php printf( 
		__( 'If you are ready to plan your next %s, contact us <a href="%s">here</a>.', 'mobile-dj-manager' ),
		mdjm_get_label_singular( true ), '{contact_page}' ); ?></p>        
</div>