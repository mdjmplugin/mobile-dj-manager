<?php
/**
 * This template is used when the current logged in client accesses the playlist page but has no events.
 *
 * @version         1.0
 * @author          Mike Howard
 * @since           1.3
 * @content_tag     {client_*}
 * @shortcodes      Not Supported
 *
 * Do not customise this file!
 * If you wish to make changes, copy this file to your theme directory /theme/mdjm-templates/quote/quote-noevent.php
 */
?>
<div id="client-quote-no-events">
	<p>
    <?php 
    printf( __( "We haven't been able to locate your %1\$s. Please return to our <a href='%2\$s'>%3\$s</a> page to see a list of your %4\$s.", 'mobile-dj-manager' ), // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
    esc_html( mdjm_get_label_singular() ), '{application_home}', '{application_name}', esc_html( mdjm_get_label_plural() ) ); 
	?>
        </p>
</div>
