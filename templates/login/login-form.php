<?php
/**
 * This template is used to generate the page for the shortcode [mdjm-login].
 *
 * @version			1.0
 * @author			Mike Howard
 * @since			1.3
 * @content_tag
 * @shortcodes		Not Supported
 *
 * Do not customise this file!
 * If you wish to make changes, copy this file to your theme directory /theme/mdjm-templates/login/login-form.php
 */
global $mdjm_login_redirect;
?>
<?php if( ! is_user_logged_in() ) : ?>
    
	<?php do_action( 'mdjm_before_login_form' ); ?>
    
    <!-- MDJM login form header content starts -->
    <h2><?php _e( 'Please Login', 'mobile-dj-manager' ); ?></h2>
    <p><?php printf( __( 'You must be logged in to access the %s %s. Enter your login details below.', 'mobile-dj-manager' ),
                '{company_name}',
                '{application_name}' ); ?></p>
                
    
    <!-- MDJM login form header content ends -->
    
    <!-- MDJM login form content starts -->
    
    <form name="mdjm-login-form" id="mdjm-login-form" action="<?php echo esc_url( site_url( 'wp-login.php', 'login_post' ) ); ?>" method="post">
    	<fieldset>
        	<legend><?php printf( __( 'Login to %s', 'mobile-dj-manager' ), '{company_name}' ); ?></legend>
			<?php do_action( 'mdjm_login_form_top' ); ?>
            <p class="mdjm-login-username">
                <label for="mdjm-login-username"><?php _e( 'Email address:', 'mobile-dj-manager' ); ?></label>
                <input type="text" name="log" id="mdjm-login-username" class="mdjm-input" value="" size="20" />
            </p>
            
            <p class="mdjm-login-password">
                <label for="mdjm-login-password"><?php _e( 'Password:', 'mobile-dj-manager' ); ?></label>
                <input type="password" name="pwd" id="mdjm-login-password" class="mdjm-input" value="" size="20" />
            </p>
            
            <?php do_action( 'mdjm_login_form_middle' ); ?>
            
            <p class="mdjm-login-remember"><label><input name="rememberme" type="checkbox" id="mdjm-login-rememberme" value="forever" /> <?php _e( 'Remember me', 'mobile-dj-manager' ); ?></label></p>
            
            <p class="mdjm-login-submit">
                <input type="submit" name="wp-submit" id="mdjm-login-submit" class="button" value="<?php printf( __( 'Login to %s', 'mobile-dj-manager' ), '{application_name}' ); ?>" />
                <input type="hidden" name="redirect_to" value="<?php echo esc_url( $mdjm_login_redirect ); ?>" />
            </p>
            
            <p class="mdjm-lost-password">
                <a href="<?php echo wp_lostpassword_url(); ?>" title="<?php _e( 'Lost Password', 'mobile-dj-manager' ); ?>">
                    <?php _e( 'Lost Password?', 'mobile-dj-manager' ); ?>
                </a>
            </p>
            
            <?php do_action( 'mdjm_login_form_bottom' ); ?>
        </fieldset>
    </form>
    
    <?php do_action( 'mdjm_after_login_form' ); ?>        
    
<?php else :?>

    <?php _e( 'You are already logged in', 'mobile-dj-manager' ); ?>
    
<?php endif; ?><!-- endif( ! user_is_logged_in() ) )