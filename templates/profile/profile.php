<?php
/**
 * This template is used to generate the page for the shortcode [mdjm-profile].
 *
 * @version			1.0
 * @author			Mike Howard
 * @since			1.3
 * @content_tag		client
 * @shortcodes		Not Supported
 *
 * Do not customise this file!
 * If you wish to make changes, copy this file to your theme directory /theme/mdjm-templates/profile/profile.php
 */
global $current_user;
?>
<div id="mdjm-profile-wrapper">

	<div id="mdjm-profile-header">
		<?php do_action( 'mdjm_profile_before_header', $current_user ); ?>
        
        <p><?php _( 'Please keep your details up to date as incorrect information may cause problems with your event.', 'mobile-dj-manager' ); ?></p>
        
        <form action="" method="post" name="mdjm-user-profile" id="mdjm-user-profile">
        
        <?php do_action( 'mdjm_profile_after_header', $current_user ); ?>
    </div><!-- end header -->
    
    <div id="mdjm-profile-content">
		<?php do_action( 'mdjm_profile_before_content', $current_user ); ?>
        
        
        
        <?php do_action( 'mdjm_profile_after_content', $current_user ); ?>
    </div><!-- end content -->
    
    <div id="mdjm-profile-footer">
		<?php do_action( 'mdjm_profile_before_footer', $current_user ); ?>
        
        </form>
        
        <?php do_action( 'mdjm_profile_after_footer', $current_user ); ?>
    </div><!-- end footer -->

</div><!-- end wrapper -->