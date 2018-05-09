<?php
/**
 * This template is used to generate the page for the shortcode [mdjm-profile] and is used by clients editing their profile.
 *
 * @version			1.0
 * @author			Mike Howard
 * @since			1.5
 *
 * Do not customise this file!
 * If you wish to make changes, copy this file to your theme directory /theme/mdjm-templates/playlist/playlist-guest.php
 */

$client_id  = get_current_user_id();
$client     = new MDJM_Client( $client_id );
$intro_text = sprintf( __( 'Please keep your details up to date as incorrect information may cause problems with your %s.', 'mobile-dj-manager' ), mdjm_get_label_singular() );

$form_title    = __( 'Your Details', 'mobile-dj-manager' );
$submit_label  = __( 'Update Details', 'mobile-dj-manager' );

$client_fields = mdjm_get_client_fields();

?>
<div id="mdjm_client_profile_wrap">
	<?php do_action( 'mdjm_print_notices' ); ?>
	<div id="mdjm_client_profile_form_wrap" class="mdjm_clearfix">
        <?php do_action( 'mdjm_before_client_profile_form' ); ?>

        <p><?php echo esc_attr( $intro_text ); ?></p>

                <form id="mdjm_client_profile_form" class="mdjm_form" method="post">
                    <?php wp_nonce_field( 'update_client_profile', 'mdjm_nonce', true, true ); ?>
                    <input type="hidden" id="mdjm_client_id" name="mdjm_client_id" value="<?php echo $mdjm_event->ID; ?>" />
                    <input type="hidden" id="action" name="action" value="mdjm_update_client_profile" />

                    <div class="mdjm-alert mdjm-alert-error mdjm-hidden"></div>

					<?php do_action( 'mdjm_client_profile_form_top' ); ?>

                    <fieldset id="mdjm_guest_playlist_form_fields">
                        <legend><?php echo esc_attr( $form_title ); ?></legend>

						<div id="mdjm-guest-playlist-input-fields">

                            <?php foreach( $client->get_profile_fields() as $field ) : ?>
                                <?php if ( mdjm_display_client_field( $field ) ) : ?>

                                    <p class="mdjm_guest_name_field">
                                        <label for="mdjm_guest_name">
                                            <?php echo esc_attr( $name_label ); ?> <span class="mdjm-required-indicator">*</span>
                                        </label>
                                        <span class="mdjm-description"><?php echo esc_html( $name_description ); ?></span>

                                        <input type="text" name="mdjm_guest_name" id="mdjm-guest-name" class="mdjm-input" />
                                    </p>

                                <?php endif; ?>
                            <?php endforeach; ?>

							<?php do_action( 'mdjm_client_profile_form_after_fields' ); ?>

                            <input class="button" name="entry_guest_submit" id="entry_guest_submit" type="submit" value="<?php echo esc_attr( $submit_label ); ?>" />

							<?php do_action( 'mdjm_client_profile_form_after_submit' ); ?>
                        </div>

                    </fieldset>

					<?php do_action( 'mdjm_client_profile_form_bottom' ); ?>

                </form>

				<?php do_action( 'mdjm_after_client_profile_form' ); ?>

    </div><!--end #mdjm_guest_playlist_form_wrap-->
</div><!-- end of #mdjm_guest_playlist_wrap -->
