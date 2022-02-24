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
 * Contains all metabox functions for the mdjm-venue post type
 *
 * @package     MDJM
 * @subpackage  Communications
 * @since       1.3
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Remove unwanted metaboxes to for the mdjm_communication post type.
 * Apply the `mdjm_communication_remove_metaboxes` filter to allow for filtering of metaboxes to be removed.
 *
 * @since   1.3
 */
function mdjm_remove_communication_meta_boxes() {
	$metaboxes = apply_filters(
		'mdjm_communication_remove_metaboxes',
		array(
			array( 'submitdiv', 'mdjm_communication', 'side' ),
		)
	);

	foreach ( $metaboxes as $metabox ) {
		remove_meta_box( $metabox[0], $metabox[1], $metabox[2] );
	}
} // mdjm_remove_transaction_meta_boxes
add_action( 'admin_head', 'mdjm_remove_communication_meta_boxes' );

/**
 * Define and add the metaboxes for the mdjm_communication post type.
 * Apply the `mdjm_venue_add_metaboxes` filter to allow for filtering of metaboxes and settings.
 * Uses function_exists to verify the callback function exists.
 *
 * @since   1.3
 * @param str $post Venue Add Details.
 */
function mdjm_add_communication_meta_boxes( $post ) {
	$metaboxes = apply_filters(
		'mdjm_communication_add_metaboxes',
		array(
			array(
				'id'         => 'mdjm-email-details',
				'title'      => __( 'Details', 'mobile-dj-manager' ),
				'callback'   => 'mdjm_communication_details_metabox',
				'context'    => 'side',
				'priority'   => 'high',
				'args'       => array(),
				'dependancy' => '',
				'permission' => 'mdjm_comms_send',
			),
			array(
				'id'         => 'mdjm-email-content',
				'title'      => __( 'Email Content', 'mobile-dj-manager' ),
				'callback'   => 'mdjm_communication_content_metabox',
				'context'    => 'normal',
				'priority'   => 'high',
				'args'       => array(),
				'dependancy' => '',
				'permission' => 'mdjm_comms_send',
			),
		)
	);
	// Runs before metabox output.
	do_action( 'mdjm_communication_before_metaboxes' );

	// Begin metaboxes.
	foreach ( $metaboxes as $metabox ) {
		// Dependancy check.
		if ( ! empty( $metabox['dependancy'] ) && false === $metabox['dependancy'] ) {
			continue;
		}

		// Permission check.
		if ( ! empty( $metabox['permission'] ) && ! mdjm_employee_can( $metabox['permission'] ) ) {
			continue;
		}

		// Callback check.
		if ( ! is_callable( $metabox['callback'] ) ) {
			continue;
		}

		add_meta_box(
			$metabox['id'],
			$metabox['title'],
			$metabox['callback'],
			'mdjm_communication',
			$metabox['context'],
			$metabox['priority'],
			$metabox['args']
		);
	}

	// Runs after metabox output.
	do_action( 'mdjm_communication_after_metaboxes' );

} // mdjm_add_communication_meta_boxes.
add_action( 'add_meta_boxes_mdjm_communication', 'mdjm_add_communication_meta_boxes' );

/**
 * Output for the Communication Details meta box.
 *
 * @since   1.3
 * @param   obj $post       The post object (WP_Post).
 */
function mdjm_communication_details_metabox( $post ) {

	do_action( 'mdjm_pre_communication_details_metabox', $post );

	wp_nonce_field( basename( __FILE__ ), 'mdjm_communication_nonce' );

	$from      = get_userdata( $post->post_author );
	$recipient = get_userdata( get_post_meta( $post->ID, '_recipient', true ) );

	$attachments = get_children(
		array(
			'post_parent'  => $post->ID,
			'post_type'    => 'attachment',
			'number_posts' => -1,
			'post_status'  => 'any',
		)
	);

	?>
	<p>
	<?php
	printf(
		/* translators: %s Date */
		__( '<strong>Date Sent</strong>: %s', 'mobile-dj-manager' ), // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		gmdate( mdjm_get_option( 'time_format', 'H:i' ) . ' ' . mdjm_get_option( 'short_date_format', 'd/m/Y' ), get_post_meta( $post->ID, '_date_sent', true ) ) // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped 
	);
	?>
		</p>

	<p>
	<?php
	printf(
		/* translators: %1 mailto link %2 email address */
		__( '<strong>From</strong>: <a href="%1$s">%2$s</a>', 'mobile-dj-manager' ), // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		esc_url( admin_url( "/user-edit.php?user_id={$from->ID}" ) ),
		esc_html( $from->display_name )
	);
	?>
		</p>

	<p>
	<?php
	printf(
		/* translators: %1 mailto link %2 email address */
		__( '<strong>Recipient</strong>: <a href="%1$s">%2$s</a>', 'mobile-dj-manager' ), // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		esc_url( admin_url( "/user-edit.php?user_id={$recipient->ID}" ) ),
		esc_html( $recipient->display_name )
	);
	?>
		</p>

	<?php
	$copies = get_post_meta( $post->ID, '_mdjm_copy_to', true );

	if ( ! empty( $copies ) ) {

		?>
			<p>
			<?php
			_e( '<strong>Copied To</strong>: ', 'mobile-dj-manager' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped, WordPress.Security.EscapeOutput.UnsafePrintingFunction 
			?>
			<em>
				<?php

				$i = 1;
				foreach ( $copies as $copy ) {
					$user = get_user_by( 'email', $copy );
					if ( $user ) {
						echo esc_html( $user->display_name );

						$i++;

						if ( $i < count( $copies ) ) {
							echo '<br />';
						}
					}
				}

				?>
			</em></p>
			<?php
	}
	?>

	<p><?php _e( '<strong>Status</strong>:', 'mobile-dj-manager' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped, WordPress.Security.EscapeOutput.UnsafePrintingFunction ?>

		<?php
		echo esc_html( get_post_status_object( $post->post_status )->label );

		if ( 'opened' === $post->post_status ) {
			echo ' ' . esc_html( gmdate( mdjm_get_option( 'time_format', 'H:i' ) . ' ' . mdjm_get_option( 'short_date_format', 'd/m/Y' ), strtotime( $post->post_modified ) ) );
		}
		?>
		</p>

	<p><strong><?php echo esc_html( mdjm_get_label_singular() ); ?></strong>: <a href="<?php echo esc_url( get_edit_post_link( get_post_meta( $post->ID, '_event', true ) ) ); ?>"><?php echo esc_html( mdjm_get_event_contract_id( wp_unslash( get_post_meta( $post->ID, '_event', true ) ) ) ); ?></a></p>

	<?php
	if ( ! empty( $attachments ) ) {

		$i = 1;
		?>
		<p><strong><?php esc_html_e( 'Attachments', 'mobile-dj-manager' ); ?></strong>:<br />

			<?php
			foreach ( $attachments as $attachment ) {
				echo '<a style="font-size: 11px;" href="' . esc_url( wp_get_attachment_url( $attachment->ID ) ) . '">';
				echo esc_html( basename( get_attached_file( $attachment->ID ) ) );
				echo '</a>';
				echo ( $i < count( $attachments ) ? '<br />' : '' );
				$i++;
			}
			?>
		</p>
		<?php
	}
	?>

	<a class="button-secondary" href="<?php echo isset( $_SERVER['HTTP_REFERER'] ) ? esc_url( sanitize_text_field( wp_unslash( $_SERVER['HTTP_REFERER'] ) ) ) : '#'; ?>" title="<?php esc_attr_e( 'Back to List', 'mobile-dj-manager' ); ?>"><?php esc_html_e( 'Back', 'mobile-dj-manager' ); ?></a>

	<?php

	do_action( 'mdjm_post_communication_details_metabox', $post );

} // mdjm_communication_details_metabox

/**
 * Output for the Communication Content meta box.
 *
 * @since   1.3
 * @param   obj $post       The post object (WP_Post).
 */
function mdjm_communication_content_metabox( $post ) {

	do_action( 'mdjm_pre_communication_content_metabox', $post );

	echo $post->post_content; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

	do_action( 'mdjm_post_communication_content_metabox', $post );

} // mdjm_communication_content_metabox
