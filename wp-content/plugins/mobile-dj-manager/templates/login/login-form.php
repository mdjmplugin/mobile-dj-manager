<?php
/**
 * This template is used to generate the page for the shortcode [mdjm-login].
 *
 * @version         1.0
 * @author          Mike Howard, Jack Mawhinney, Dan Porter
 * @content_tag
 * @shortcodes      Not Supported
 *
 * Do not customise this file!
 * If you wish to make changes, copy this file to your theme directory /theme/mdjm-templates/login/login-form.php
 */
global $mdjm_login_redirect; ?>

<?php if ( ! is_user_logged_in() ) : ?>

	<?php do_action( 'mdjm_print_notices' ); ?>

	<?php do_action( 'mdjm_before_login_form' ); ?>

	<!-- MDJM login form content starts -->


	<form id="mdjm-login-form" name="mdjm-login-form" class="mdjm_form" action="" method="post">
		<fieldset>
			<legend><?php printf( esc_html__( 'Login to %s', 'mobile-dj-manager' ), '{company_name}' ); ?></legend>
			<?php do_action( 'mdjm_login_form_top' ); ?>
			<p class="mdjm-login-username">
				<label for="mdjm-login-username"><?php esc_html_e( 'Email address:', 'mobile-dj-manager' ); ?></label>
				<input type="text" name="mdjm_user_login" id="mdjm-login-username" class="mdjm-input" value="" size="20" required />
			</p>

			<p class="mdjm-login-password">
				<label for="mdjm-login-password"><?php esc_html_e( 'Password:', 'mobile-dj-manager' ); ?></label>
				<input type="password" name="mdjm_user_pass" id="mdjm-login-password" class="mdjm-input" value="" size="20" required />
			</p>

			<?php do_action( 'mdjm_login_form_middle' ); ?>

			<p class="mdjm-login-submit">
				<input type="hidden" name="mdjm_redirect" value="<?php echo esc_url( $mdjm_login_redirect ); ?>"/>
				<input type="hidden" name="mdjm_login_nonce" value="<?php echo esc_attr( wp_create_nonce( 'mdjm-login-nonce' ) ); ?>"/>
				<input type="hidden" name="mdjm_action" value="user_login"/>
				<input id="mdjm_login_submit" type="submit" class="mdjm_submit" value="<?php printf( esc_attr__( 'Login to %s', 'mobile-dj-manager' ), '{application_name}' ); ?>" />
			</p>

			<p class="mdjm-lost-password">
				<a href="<?php echo esc_url( wp_lostpassword_url() ); ?>" title="<?php esc_attr_e( 'Lost Password', 'mobile-dj-manager' ); ?>">
					<?php esc_html_e( 'Lost Password?', 'mobile-dj-manager' ); ?>
				</a>
			</p>

			<?php do_action( 'mdjm_login_form_bottom' ); ?>
		</fieldset>
	</form>

	<?php ( 'mdjm_after_login_form' ); ?>

<?php else : ?>

	<?php esc_html_e( 'You are already logged in', 'mobile-dj-manager' ); ?>

<?php endif; ?>
