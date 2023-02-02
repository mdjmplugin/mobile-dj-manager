<?php
/**
 * @author: Mike Howard, Jack Mawhinney, Dan Porter
 *
 * Upgrade Screen
 *
 * @package     MDJM
 * @subpackage  Admin/Upgrades
 * @copyright   Copyright (c) 2016, Mike Howard
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.4
 *
 * Taken from Easy Digital Downloads.
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Render Upgrades Screen
 *
 * @since   1.4
 * @return  void
 */
function mdjm_upgrades_screen() {
	$action = isset( $_GET['mdjm-upgrade'] ) ? sanitize_text_field( wp_unslash( $_GET['mdjm-upgrade'] ) ) : '';
	$step   = isset( $_GET['step'] ) ? absint( $_GET['step'] ) : 1;
	$total  = isset( $_GET['total'] ) ? absint( $_GET['total'] ) : false;
	$custom = isset( $_GET['custom'] ) ? absint( $_GET['custom'] ) : 0;
	$number = isset( $_GET['number'] ) ? absint( $_GET['number'] ) : 100;
	$steps  = round( ( $total / $number ), 0 );

	$doing_upgrade_args = array(
		'page'         => 'mdjm-upgrades',
		'mdjm-upgrade' => $action,
		'step'         => $step,
		'total'        => $total,
		'custom'       => $custom,
		'steps'        => $steps,
	);
	update_option( 'mdjm_doing_upgrade', $doing_upgrade_args );
	if ( $step > $steps ) {
		// Prevent a weird case where the estimate was off. Usually only a couple.
		$steps = $step;
	}
	?>
	<div class="wrap">
		<h2><?php esc_html_e( 'Mobile DJ Manager - Upgrading, Please wait...', 'mobile-dj-manager' ); ?></h2>

		<?php if ( ! empty( $action ) ) : ?>

			<div id="mdjm-upgrade-status">
				<p><?php esc_html_e( 'The upgrade process has started, please be patient. This could take several minutes. You will be automatically redirected when the upgrade is finished.', 'mobile-dj-manager' ); ?></p>

				<?php if ( ! empty( $total ) ) : ?>
					<p><strong>
						<?php printf( esc_html__( 'Step %1$d of approximately %2$d running', 'mobile-dj-manager' ), esc_html( $step ), esc_html( $steps ) ); ?>
					</strong><img src="<?php echo esc_url( MDJM_PLUGIN_URL ) . '/assets/images/loading.gif'; ?>" id="mdjm-upgrade-loader"/></p>
				<?php endif; ?>
			</div>
			<script type="text/javascript">
				setTimeout(function() { document.location.href = "index.php?mdjm-action=<?php echo esc_attr( $action ); ?>&step=<?php echo esc_attr( $step ); ?>&total=<?php echo esc_attr( $total ); ?>&custom=<?php echo esc_attr( $custom ); ?>"; }, 250);
			</script>

		<?php else : ?>

			<div id="mdjm-upgrade-status">
				<p>
					<?php esc_html_e( 'The upgrade process has started, please be patient. This could take several minutes. You will be automatically redirected when the upgrade is finished.', 'mobile-dj-manager' ); ?>
					<img src="<?php echo esc_url( MDJM_PLUGIN_URL ) . '/assets/images/loading.gif'; ?>" id="mdjm-upgrade-loader"/>
				</p>
			</div>
			<script type="text/javascript">
				jQuery( document ).ready( function() {
					// Trigger upgrades on page load
					var data = { action: 'mdjm_trigger_upgrades' };
					jQuery.post( ajaxurl, data, function (response) {
						if( response == 'complete' ) {
							jQuery('#mdjm-upgrade-loader').hide();
							document.location.href = 'index.php?page=mdjm-about'; // Redirect to the welcome page
						}
					});
				});
			</script>

		<?php endif; ?>

	</div>
	<?php
} // mdjm_upgrades_screen
