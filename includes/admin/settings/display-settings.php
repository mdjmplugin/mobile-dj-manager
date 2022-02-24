<?php
/**
 * This plugin utilizes Open Source code. Details of these open source projects along with their licenses can be found below.
 * We acknowledge and are grateful to these developers for their contributions to open source.
 *
 * Project: mobile-dj-manager https://github.com/deckbooks/mobile-dj-manager
 * License: (GNU General Public License v2.0) https://github.com/deckbooks/mobile-dj-manager/blob/master/license.txt
 *
 * @author: Mike Howard, Jack Mawhinney, Dan Porter
 *
 * Admin Options Page
 *
 * @package     MDJM
 * @subpackage  Admin/Settings
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.3
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Options Page
 *
 * Renders the options page contents.
 *
 * @since   1.3
 * @return  void
 */
function mdjm_options_page() {
	$settings_tabs = mdjm_get_settings_tabs();
	$settings_tabs = empty( $settings_tabs ) ? array() : $settings_tabs;
	$active_tab    = isset( $_GET['tab'] ) && array_key_exists( sanitize_text_field( wp_unslash( $_GET['tab'] ) ), $settings_tabs ) ? sanitize_text_field( wp_unslash( $_GET['tab'] ) ) : 'general';
	$sections      = mdjm_get_settings_tab_sections( $active_tab );
	$key           = 'main';

	if ( is_array( $sections ) ) {
		$key = key( $sections );
	}

	$registered_sections = mdjm_get_settings_tab_sections( $active_tab );
	$section             = isset( $_GET['section'] ) && ! empty( $registered_sections ) && array_key_exists( sanitize_text_field( wp_unslash( $_GET['section'] ) ), $registered_sections ) ? sanitize_text_field( wp_unslash( $_GET['section'] ) ) : $key;
	ob_start();
	?>
	<div class="wrap <?php echo 'wrap-' . esc_attr( $active_tab ); ?>">
		<h1 class="wp-heading-inline">Settings</h1>
		<div class="nav-tab-wrapper">
			<?php
			foreach ( mdjm_get_settings_tabs() as $tab_id => $tab_name ) {

				$tab_url = add_query_arg(
					array(
						'settings-updated' => false,
						'tab'              => $tab_id,
					)
				);

				// Remove the section from the tabs so we always end up at the main section
				$tab_url = remove_query_arg( 'section', $tab_url );

				$active = $active_tab == $tab_id ? ' nav-tab-active' : '';

				echo '<a href="' . esc_url( $tab_url ) . '" title="' . esc_attr( $tab_name ) . '" class="nav-tab' . esc_attr( $active ) . '">';
					echo esc_html( $tab_name );
				echo '</a>';
			}
			?>
		</div>
		<?php

		$number_of_sections = is_array( $sections ) ? count( $sections ) : '0';
		$number             = 0;
		if ( $number_of_sections > 1 ) {
			echo '<div><ul class="subsubsub">';
			foreach ( $sections as $section_id => $section_name ) {
				echo '<li>';
				$number++;
				$tab_url = add_query_arg(
					array(
						'settings-updated' => false,
						'tab'              => $active_tab,
						'section'          => $section_id,
					)
				);
				$class   = '';
				if ( $section == $section_id ) {
					$class = 'current';
				}
				echo '<a class="' . esc_attr( $class ) . '" href="' . esc_url( $tab_url ) . '">' . esc_html( $section_name ) . '</a>';

				if ( $number != $number_of_sections ) {
					echo ' | ';
				}
				echo '</li>';
			}
			echo '</ul></div>';
		}
		?>
		<div id="tab_container">
			<form method="post" action="options.php">
				<table class="form-table">
				<?php

				settings_fields( 'mdjm_settings' );

				if ( 'main' === $section ) {
					do_action( 'mdjm_settings_tab_top', $active_tab );
				}

				do_action( 'mdjm_settings_tab_top_' . $active_tab . '_' . $section );

				do_settings_sections( 'mdjm_settings_' . $active_tab . '_' . $section );

				do_action( 'mdjm_settings_tab_bottom_' . $active_tab . '_' . $section );

				// For backwards compatibility
				if ( 'main' === $section ) {
					do_action( 'mdjm_settings_tab_bottom', $active_tab );
				}

				?>
				</table>
				<?php submit_button(); ?>
			</form>
		</div><!-- #tab_container-->
	</div><!-- .wrap -->
	<?php
	echo ob_get_clean(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
} // mdjm_options_page
