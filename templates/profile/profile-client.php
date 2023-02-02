<?php
/**
 * This template is used to generate the page for the shortcode [mdjm-profile] and is used by clients editing their profile.
 *
 * @version         1.0
 * @author          Mike Howard, Jack Mawhinney, Dan Porter
 *
 * Do not customise this file!
 * If you wish to make changes, copy this file to your theme directory /theme/mdjm-templates/playlist/playlist-guest.php
 */

if ( ! is_user_logged_in() ) : ?>

	<?php echo mdjm_display_notice( 'login_profile' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
	<?php echo mdjm_login_form( mdjm_get_current_page_url() ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>

	<?php
else :

	$client_id     = get_current_user_id();
	$client        = new MDJM_Client( $client_id );
	$intro_text    = sprintf( __( 'Please keep your details up to date as incorrect information may cause problems with your %s.', 'mobile-dj-manager' ), mdjm_get_label_singular( true ) );
	$form_title    = __( 'Your Details', 'mobile-dj-manager' );
	$password_text = __( 'To change your password, type the new password and confirm it below. Leave this field empty to keep your current password', 'mobile-dj-manager' );
	$submit_label  = __( 'Update Details', 'mobile-dj-manager' );

	$client_fields = $client->get_profile_fields();

	?>
	<div id="mdjm_client_profile_wrap">
		<?php do_action( 'mdjm_print_notices' ); ?>
		<div id="mdjm_client_profile_form_wrap" class="mdjm_clearfix">
			<?php do_action( 'mdjm_before_client_profile_form' ); ?>

			<p><?php echo esc_attr( $intro_text ); ?></p>

					<form id="mdjm_client_profile_form" class="mdjm_form" method="post">
						<?php wp_nonce_field( 'update_client_profile', 'mdjm_nonce', true, true ); ?>
						<input type="hidden" id="mdjm_client_id" name="mdjm_client_id" value="<?php echo esc_attr( $client->ID ); ?>" />
						<input type="hidden" id="action" name="action" value="mdjm_validate_client_profile" />

						<div class="mdjm-alert mdjm-alert-error mdjm-hidden"></div>
						<div class="mdjm-alert mdjm-alert-success mdjm-hidden"></div>

						<?php do_action( 'mdjm_client_profile_form_top' ); ?>

						<fieldset id="mdjm_client_profile_form_fields">
							<legend><?php echo esc_attr( $form_title ); ?></legend>

							<div id="mdjm-client-profile-input-fields">

								<?php foreach ( $client->get_profile_fields() as $field ) : ?>
									<?php
									if ( mdjm_display_client_field( $field ) ) :
										$id    = esc_attr( $field['id'] );
										$label = esc_attr( $field['label'] );
										?>

										<p class="mdjm_<?php echo $id; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>_field">
											<label for="mdjm_<?php echo $id; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>">
												<?php echo $label; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?> <?php
												if ( ! empty( $field['required'] ) ) :
													?>
													<span class="mdjm-required-indicator">*</span><?php endif; ?>
											</label>

											<?php mdjm_display_client_input_field( $field, $client ); ?>
										</p>

									<?php endif; ?>
								<?php endforeach; ?>

								<p><span class="mdjm-description"><?php echo esc_html( $password_text ); ?></span></p>

								<p class="mdjm_new_password_field">
									<label for="mdjm_new_password">
										<?php esc_html_e( 'New Password', 'mobile-dj-manager' ); ?>
									</label>

									<input name="mdjm_new_password" id="mdjm_new_password" type="password" autocomplete="off" />
								</p>

								<p class="mdjm_confirm_password_field">
									<label for="mdjm_confirm_password">
										<?php esc_html_e( 'Confirm New Password', 'mobile-dj-manager' ); ?>
									</label>

									<input name="mdjm_confirm_password" id="mdjm_confirm_password" type="password" autocomplete="off" />
								</p>

								<?php do_action( 'mdjm_client_profile_form_after_fields' ); ?>

								<div id="mdjm_client_profile_submit_fields">
									<input class="button" name="update_profile_submit" id="update_profile_submit" type="submit" value="<?php echo esc_attr( $submit_label ); ?>" />
								</div>

								<?php do_action( 'mdjm_client_profile_form_after_submit' ); ?>
							</div>

						</fieldset>

						<?php do_action( 'mdjm_client_profile_form_bottom' ); ?>

					</form>

					<?php do_action( 'mdjm_after_client_profile_form' ); ?>

		</div><!--end #mdjm_guest_playlist_form_wrap-->
	</div><!-- end of #mdjm_guest_playlist_wrap -->
<?php endif; ?>
