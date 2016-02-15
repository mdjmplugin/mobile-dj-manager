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
global $mdjm_notice;
?>
<div id="mdjm_availability_widget">
	<div id="mdjm_availability_widget_intro"><p><?php echo ( !isset( $mdjm_notice ) ) ? $instance['intro'] : $mdjm_notice; ?></p></div>
    <form name="mdjm_availability_check_widget" id="mdjm_availability_check_widget" method="post">
        <p><label for="mdjm_show_date_widget"><?php echo $instance['label']; ?></label>
        <input type="text" name="mdjm_show_date_widget" id="mdjm_show_date_widget" class="mdjm_datepicker_widget" placeholder="<?php mdjm_format_datepicker_date(); ?>" /></p>
        
        <input type="hidden" name="mdjm_enquiry_date_widget" id="mdjm_enquiry_date_widget" value="" />
                
        <p<?php if( $instance['submit_centre'] == 'Y' ) : echo ' style="text-align: center;";'; endif; ?>><input type="submit" name="submit" id="submit" class="mdjm_submit" value="<?php echo $instance['submit_text']; ?>" /></p>
        
        <div class="mdjm_pleasewait"><?php _e( 'Please wait...', 'mobile-dj-manager' ); ?><img src="/wp-admin/images/loading.gif" alt="<?php _e( 'Please wait...', 'mobile-dj-manager' ); ?>" /></div>
    </form>
</div>