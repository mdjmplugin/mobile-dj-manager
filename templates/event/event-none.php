<?php
/**
 * This template is used when the current logged in client has no events.
 *
 * @version         1.0
 * @author          Mike Howard
 * @since           1.3
 * @content_tag     {client_*}
 * @shortcodes      Supported
 *
 * Do not customise this file!
 * If you wish to make changes, copy this file to your theme directory /theme/mdjm-templates/event/event-none.php
 */
?>
<div id="client-no-events">
	<p><?php esc_html_e( 'Hey', 'mobile-dj-manager' ); ?> {client_firstname}, 
                         <?php 
							printf( esc_html__( 'welcome to the %1$s %2$s.', 'mobile-dj-manager' ),
                                '{company_name}',
							'{application_name}' ); 
							?>
        </p>

    <p><?php printf( esc_html__( 'You do not currently have any active %s booked with us.', 'mobile-dj-manager' ), esc_html( mdjm_get_label_plural() ) ); ?></p>

    <p>
    <?php 
    printf(
		__( 'If you are ready to plan your next %1$s, contact us <a href="%2$s">here</a>.', 'mobile-dj-manager' ), // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
    esc_html( mdjm_get_label_singular( true ) ), '{contact_page}' ); 
	?>
        </p>
</div>
