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
 * Manage playlists in admin.
 *
 * @since       1.5
 * @package     MDJM
 * @subpackage  Functions/Playlists
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Ensure that the guest playlist term cannot be deleted by removing the
 * delete option from the hover menu on the edit screen.
 *
 * @since   1.5
 * @param   arr $actions        The array of actions in the hover menu
 * @param   obj $tag            The object array for the term
 * @return  arr     $actions        The filtered array of actions in the hover menu
 */
function mdjm_playlist_guest_term_remove_delete_row_action( $actions, $tag ) {

	if ( 'guest' == $tag->slug ) {
		unset( $actions['delete'] );
	}

	return $actions;

} // mdjm_playlist_guest_term_remove_delete_row_action
add_filter( 'playlist-category_row_actions', 'mdjm_playlist_guest_term_remove_delete_row_action', 10, 2 );

/**
 * Ensure that the guest playlist category term cannot be deleted by removing the
 * bulk action checkboxes.
 *
 * @since   1.05
 * @return  void
 */
function mdjm_playlist_guest_term_remove_checkbox() {

	if ( ! isset( $_GET['taxonomy'] ) || 'playlist-category' != $_GET['taxonomy'] ) {
		return;
	}

	$terms = mdjm_get_playlist_categories();

	if ( empty( $terms ) ) {
		return;
	}

	?>
	<script type="text/javascript">
	jQuery(document).ready(function($) {
		<?php
		foreach ( $terms as $term ) {

			if ( ! empty( $term->term_id ) && 'guest' == $term->slug ) {
				?>
				$('input#cb-select-<?php echo esc_attr( $term->term_id ); ?>').prop('disabled', true).hide();
											  <?php
			}
		}
		?>
	});
	</script>
	<?php
} // kbs_edd_download_terms_remove_checkbox
add_action( 'admin_footer-edit-tags.php', 'mdjm_playlist_guest_term_remove_checkbox' );

/**
 * Make the guest playlist term slug readonly when editing
 *
 * @since   1.5
 * @param   obj $tag    The tag object
 * @return  str
 */
function mdjm_playlist_set_guest_term_readonly( $tag ) {

	if ( 'guest' == $tag->slug ) :
		?>
		<script type="text/javascript">
		jQuery().ready(function($)	{
			$("#slug").attr('readonly','true');
		});
		</script>
		<?php
	endif;

} // mdjm_playlist_set_guest_term_readonly
add_action( 'playlist-category_edit_form_fields', 'mdjm_playlist_set_guest_term_readonly' );
