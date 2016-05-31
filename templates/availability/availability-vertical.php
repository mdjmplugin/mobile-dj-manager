<?php
/**
 * This template is used to display the availability form with shortcode [mdjm-availability display="vertical"].
 *
 * @version			1.0
 * @author			Mike Howard
 * @since			1.3
 * @content_tag		No {client_*}
 * @content_tag		No {event_*}
 * @content_tags	{label}, {label_class}, {field}, {field_class}, {submit_text}, {submit_class}, {please_wait_text}, {please_wait_class}
 * @shortcodes		Not Supported
 *
 * Do not change any form field ID's.
 *
 * Do not customise this file!
 * If you wish to make changes, copy this file to your theme directory /theme/mdjm-templates/availabilty/availabilty-vertical.php
 */
?>

<?php do_action( 'mdjm_print_notices' ); ?>
<div id="mdjm-availability-result"></div>
<div id="mdjm-availability-checker">
	<p><label for="{field}" class="{label_class}">{label}</label><br />
    <input type="text" name="{field}" id="{field}" class="{field_class}" size="20" placeholder="<?php echo mdjm_format_datepicker_date(); ?>" /></p>
    <p><input type="submit" name="mdjm-submit-availability" id="mdjm-submit-availability" class="{submit_class}" value="{submit_text}" /></p>
	<span id="pleasewait" class="{please_wait_class}">{please_wait_text} <img src="<?php echo MDJM_PLUGIN_URL; ?>/assets/images/loading.gif" alt="{please_wait_text}" /></span>
</div>
