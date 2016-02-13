<?php
/**
 * This template is used to display the availability widget.
 *
 * @version			1.0
 * @author			Mike Howard
 * @since			1.3
 * @content_tag
 * @shortcodes		Not Supported
 *
 * Do not customise this file!
 * If you wish to make changes, copy this file to your theme directory /theme/mdjm-templates/availability/availability-widget.php
 */
?>
<form name="mdjm-widget-availability-check" id="mdjm-widget-availability-check" method="post">
    <label for="widget_avail_date"><?php echo $instance['label']; ?></label>
    <input type="text" name="widget_avail_date" id="widget_avail_date" class="mdjm_widget_date" style="z-index:99;" placeholder="<?php mdjm_format_datepicker_date(); ?>" />
    <input type="hidden" name="widget_check_date" id="widget_check_date" value="" />
    <p<?php echo ( isset( $instance['submit_centre'] ) && $instance['submit_centre'] == 'Y' ? ' style="text-align:center"' : '' ); ?>>
    <input type="submit" name="mdjm_widget_avail_submit" id="mdjm_widget_avail_submit" value="<?php echo $instance['submit_text']; ?>" /></p>
    <div id="widget_pleasewait" class="page-content" style="display: none;"><?php _e( 'Please wait...', 'mobile-dj-manager' ); ?><img src="/wp-admin/images/loading.gif" alt="<?php _e( 'Please wait...', 'mobile-dj-manager' ); ?>" /></div>
</form>
