<?php
/**
 * @author: Mike Howard, Jack Mawhinney, Dan Porter
 *
 * MDJM_ClientFields Class
 *
 * @package     MDJM
 * @subpackage  Clients
 * @copyright   Copyright (c) 2016, Mike Howard
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/* -- Build the MDJM_ClientFields class -- */
if ( ! class_exists( 'MDJM_ClientFields' ) ) {
	class MDJM_ClientFields {
		/*
		 * The Constructor
		 */
		function __construct() {

			$this->fields = get_option( 'mdjm_client_fields' );

			foreach ( $this->fields as $key => $row ) {
				$field[ $key ] = $row['position'];
			}

			// Sort the fields into a positional array
			array_multisort( $field, SORT_ASC, $this->fields );

			if ( isset( $_GET['action'], $_GET['id'] ) && $_GET['action'] == 'delete_field' ) {
				$this->delete_field();
			}

			if ( isset( $_POST['submit'] ) ) {

				if ( $_POST['submit'] == __( 'Add Field', 'mobile-dj-manager' ) ) {
					$this->add_field();
				}

				if ( $_POST['submit'] == __( 'Save Changes', 'mobile-dj-manager' ) ) {
					$this->update_field();
				}
			}

			$this->display_fields();
		} // __construct

		/*
		 * Process field deletions
		 */
		function delete_field() {
			unset( $this->fields[ $_GET['id'] ] );

			if ( update_option( 'mdjm_client_fields', $this->fields ) ) {
				mdjm_update_notice( 'updated', __( 'The field was deleted successfully.', 'mobile-dj-manager' ) );
			} else {
				mdjm_update_notice( 'error', __( 'Field could not be deleted', 'mobile-dj-manager' ) );
			}
		} // delete_field

		/*
		 * Add new field
		 */
		function add_field() {
			global $current_user;

			$id = sanitize_title_with_dashes( wp_unslash( $_POST['field_label'] ), '', 'save' ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotValidated

			if ( array_key_exists( $id, $this->fields ) ) {
				$id = $id . '_';
			}

			$field_type = isset( $_POST['field_type'] ) ? sanitize_text_field( wp_unslash( $_POST['field_type'] ) ) : '';
			if ( $field_type == 'checkbox' ) {
				$value = isset( $_POST['field_value'] ) ? sanitize_text_field( wp_unslash( $_POST['field_value'] ) ) : '';
			} elseif ( $field_type == 'dropdown' ) {
				$value = isset( $_POST['field_options'] ) ? sanitize_textarea_field( wp_unslash( $_POST['field_options'] ) ) : '';
			}

			$this->fields[ $id ] = array(
				'label'    => sanitize_text_field( wp_unslash( $_POST['field_label'] ) ), // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotValidated
				'id'       => $id,
				'type'     => $field_type,
				'value'    => ! empty( $value ) ? $value : '',
				'checked'  => ! empty( $_POST['field_checked'] ) ? '1' : '0',
				'display'  => ! empty( $_POST['field_enabled'] ) ? '1' : '0',
				'required' => ! empty( $_POST['field_required'] ) ? '1' : '0',
				'desc'     => ! empty( $_POST['field_desc'] ) ? sanitize_text_field( wp_unslash( $_POST['field_desc'] ) ) : '',
				'default'  => '0',
				'position' => isset( $_POST['field_position'] ) ? absint( wp_unslash( $_POST['field_position'] ) ) : 0,
			);

			if ( update_option( 'mdjm_client_fields', $this->fields ) ) {
				mdjm_update_notice( 'updated', sanitize_text_field( wp_unslash( $_POST['field_label'] ) ) . esc_html__( ' created successfully.', 'mobile-dj-manager' ) ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotValidated
			} else {
				mdjm_update_notice( 'error', esc_html__( 'Field could not be created', 'mobile-dj-manager' ) );
			}

		} // add_field

		/*
		 * Update existing field
		 */
		function update_field() {
			global $current_user;

			if ( isset( $_POST['field_id'] ) ) {
				$field_type = isset( $_POST['field_type'] ) ? sanitize_text_field( wp_unslash( $_POST['field_type'] ) ) : '';
				if ( $field_type == 'checkbox' ) {
					$value = isset( $_POST['field_value'] ) ? sanitize_text_field( wp_unslash( $_POST['field_value'] ) ) : '';
				} elseif ( $field_type == 'dropdown' ) {
					$value = isset( $_POST['field_options'] ) ? sanitize_textarea_field( wp_unslash( $_POST['field_options'] ) ) : '';
				}

				$field_id = sanitize_text_field( wp_unslash( $_POST['field_id'] ) );

				$this->fields[ $field_id ] = array(
					'label'        => sanitize_text_field( wp_unslash( $_POST['field_label'] ) ), // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotValidated
						'id'       => $field_id,
						'type'     => $field_type,
						'value'    => ! empty( $value ) ? $value : '',
						'checked'  => ! empty( $_POST['field_checked'] ) ? '1' : '0',
						'display'  => ! empty( $_POST['field_enabled'] ) ? '1' : '0',
						'required' => ! empty( $_POST['field_required'] ) ? '1' : '0',
						'desc'     => ! empty( $_POST['field_desc'] ) ? sanitize_text_field( wp_unslash( $_POST['field_desc'] ) ) : '',
						'default'  => $this->fields[ $field_id ]['default'],
						'position' => $this->fields[ $field_id ]['position'],
				);

				if ( update_option( 'mdjm_client_fields', $this->fields ) ) {
					mdjm_update_notice( 'updated', sanitize_text_field( wp_unslash( $_POST['field_label'] ) ) . ' updated successfully.' ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotValidated
				} else {
					mdjm_update_notice( 'error', esc_html__( 'Field could not be updated', 'mobile-dj-manager' ) );
				}
			}

		} // update_field

		/*
		 * Display the client fields admin interface
		 */
		function display_fields() {
			/* -- Enable drag & drop -- */

			$dir = MDJM_PLUGIN_URL . '/assets/images/form-icons';

			// Start the container
			echo '<div class="mdjm-client-field-container">' . "\r\n";

			// Left Column
			echo '<div class="mdjm-client-field-column-left">' . "\r\n";

			// Start the display table
			echo '<h3>Existing Client Fields</h3>' . "\r\n";
			echo '<table class="widefat mdjm-client-list-item" style="width:90%">' . "\r\n";
			echo '<thead>' . "\r\n";
			echo '<tr>' . "\r\n";
			echo '<th style="width: 20px;"></th>' . "\r\n";
			echo '<th style="width: 15%;">' . esc_html__( 'Label', 'mobile-dj-manager' ) . '</th>' . "\r\n";
			echo '<th style="width: 15%;">' . esc_html__( 'Type', 'mobile-dj-manager' ) . '</th>' . "\r\n";
			echo '<th style="width: 35%;">' . esc_html__( 'Description', 'mobile-dj-manager' ) . '</th>' . "\r\n";
			echo '<th style="width: 15%;">' . esc_html__( 'Options', 'mobile-dj-manager' ) . '</th>' . "\r\n";
			echo '<th>' . esc_html__( 'Actions', 'mobile-dj-manager' ) . '</th>' . "\r\n";
			echo '</tr>' . "\r\n";
			echo '</thead>' . "\r\n";
			echo '<tbody>' . "\r\n";

			$i = 0;

			foreach ( $this->fields as $field ) {
				if ( $i == 0 && $field['display'] == true ) {
					$class = 'alternate mdjm_sortable_row';
				} elseif ( empty( $field['display'] ) ) {
					$class = 'form-invalid mdjm_sortable_row';
				} else {
					$class = 'mdjm_sortable_row';
				}

				echo '<tr id="fields=' . esc_attr( $field['id'] ) . '"' .
					' class="' . esc_attr( $class ) . '" data-key="' . esc_attr( $field['id'] ) . '">' . "\r\n";

				echo '<td><span class="mdjm_draghandle"></span></td>' . "\r\n";
				echo '<td>' . esc_html( wp_unslash( $field['label'] ) ) . '</td>' . "\r\n";
				echo '<td>' . esc_html( ucfirst( str_replace( 'dropdown', 'select', $field['type'] ) ) ) . ' Field</td>' . "\r\n";
				echo '<td>' . ( ! empty( $field['desc'] ) ? esc_html( $field['desc'] ) : '' ) . '</td>' . "\r\n";
				echo '<td>' . $this->field_icons( $field ) . '</td>' . "\r\n"; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				echo '<td>';
				echo '<a href="' . esc_url( mdjm_get_admin_page( 'client_fields' ) . '&action=edit_field&id=' . $field['id'] ) .
					'" class="button button-primary button-small">' . esc_html__( 'Edit', 'mobile-dj-manager' ) . '</a>';
				if ( empty( $field['default'] ) ) {
					echo '&nbsp;&nbsp;&nbsp;<a href="' . esc_url( mdjm_get_admin_page( 'client_fields' ) . '&action=delete_field&id=' . $field['id'] ) .
					'" class="button button-secondary button-small">' . esc_html__( 'Delete', 'mobile-dj-manager' ) . '</a>';
				}
				echo '</td>' . "\r\n";
				echo '</tr>' . "\r\n";

				if ( $i == 1 ) {
					$i = 0;
				} else {
					$i++;
				}
			}

			// End the display table
			echo '</tbody>' . "\r\n";
			echo '</table>' . "\r\n";

			// End left column
			echo '</div>' . "\r\n";

			// Form fields
			$this->manage_field_form();

			// End the container
			echo '</div>' . "\r\n";

		} // display_fields

		/*
		 * Display form to add or edit a field
		 */
		function manage_field_form() {
			$must = array( 'first_name', 'last_name', 'user_email' );

			$total = count( $this->fields );

			// Right Column
			echo '<div class="mdjm-client-field-column-right">' . "\r\n";
			echo '<form name="mdjm-client-fields" id="mdjm-client-fields" method="post" action="' . esc_url( mdjm_get_admin_page( 'client_fields' ) ) . '">' . "\r\n";

			if ( isset( $_GET['action'] ) && $_GET['action'] == 'edit_field' && isset( $_GET['id'] ) ) {
				$editing = true;
			}

			// If editing a field we need this hidden field to identify it
			if ( ! empty( $editing ) ) {
				echo '<input type="hidden" name="field_id" id="field_id" value="' . esc_attr( $this->fields[ sanitize_text_field( wp_unslash( $_GET['id'] ) ) ]['id'] ) . '" />' . "\r\n";
			} else {
				echo '<input type="hidden" name="field_position" id="field_position" value="' . esc_attr( $total ) . '" />' . "\r\n";
			}

			echo '<h3>' . ( empty( $editing ) ? esc_html__( 'Add New Client Field', 'mobile-dj-manager' ) :
				esc_html__( 'Edit the ', 'mobile-dj-manager' ) . '<span class="mdjm-color">' . esc_html( sanitize_text_field( wp_unslash( $this->fields[ $_GET['id'] ]['label'] ) ) ) . '</span>' . esc_html__( ' Field', 'mobile-dj-manager' ) ) . '</h3>' . "\r\n";

			// Field Label
			echo '<p>';
			echo '<label class="mdjm-label" for="field_label">' . esc_html__( 'Field Label', 'mobile-dj-manager' ) . ':</label><br />' . "\r\n";
			echo '<input type="text" name="field_label" id="field_label" class="regular-text" value="' . ( ! empty( $editing ) ? esc_html( sanitize_text_field( wp_unslash( $this->fields[ $_GET['id'] ]['label'] ) ) ) : '' ) .
				'" class="regular-text" />';
			echo '</p>' . "\r\n";

			// Field Type
			$types = array( 'text', 'checkbox', 'dropdown' );
			echo '<p>' . "\r\n";
			echo '<label class="mdjm-label" for="field_type">' . esc_html__( 'Field Type', 'mobile-dj-manager' ) . ':</label><br />' . "\r\n";
			echo '<select name="field_type" id="field_type"' . ( ! empty( $editing ) && sanitize_text_field( wp_unslash( $this->fields[ $_GET['id'] ]['default'] ) ) == true ?
				' disabled="disabled"' : '' ) . ' onChange="whichField(); showRequired();">' . "\r\n";

			foreach ( $types as $type ) {
				echo '<option value="' . esc_attr( $type ) . '"';
				if ( ! empty( $editing ) ) {
					selected( $type, sanitize_text_field( wp_unslash( $this->fields[ $_GET['id'] ]['type'] ) ) );
				}
				echo '>' . esc_html__( ucfirst( str_replace( 'dropdown', 'select', $type ) ) . ' Field', 'mobile-dj-manager' ) . '</option>' . "\r\n";
			}

			echo '</select>' . "\r\n";
			echo '</p>' . "\r\n";
			if ( ! empty( $editing ) ) { // If the select field is disabled we need to set the value
				echo '<input type="hidden" name="field_type" id="field_type" value="' . esc_attr( sanitize_text_field( wp_unslash( $this->fields[ $_GET['id'] ]['type'] ) ) ) . '" />' . "\r\n";
			}

			// Value
			?>
			<style>
			#value_field_dropdown	{
				display: <?php echo ( ! empty( $editing ) && $this->fields[ sanitize_text_field( wp_unslash( $_GET['id'] ) ) ]['type'] == 'dropdown' ? 'block;' : 'none;' ); ?>
			}
			#value_field_checkbox	{
				display: <?php echo ( ! empty( $editing ) && $this->fields[ sanitize_text_field( wp_unslash( $_GET['id'] ) ) ]['type'] == 'checkbox' ? 'block;' : 'none;' ); ?>
			}
			#required_checkbox	{
				display: <?php echo ( empty( $editing ) || $this->fields[ sanitize_text_field( wp_unslash( $_GET['id'] ) ) ]['type'] != 'checkbox' ? 'block;' : 'none;' ); ?>
			}
			</style>

			<div id="value_field_dropdown">
			<?php
			echo '<p>' . "\r\n";
			echo '<label class="mdjm-label" for="field_options">' . esc_html__( 'Selectable Options', 'mobile-dj-manager' ) . ':</label> <br />' . "\r\n";
			echo '<textarea name="field_options" id="field_options" class="all-options" rows="5">' .
				( ! empty( $editing ) ? esc_html( $this->fields[ sanitize_text_field( wp_unslash( $_GET['id'] ) ) ]['value'] ) : '' ) . '</textarea><br /><span class="description">One entry per line</span>';
			echo '</p>' . "\r\n";
			?>
			</div>
			<div id="value_field_checkbox">
			<?php
			echo '<p>' . "\r\n";
			echo '<label class="mdjm-label" for="field_value">' . esc_html__( 'Checked Value', 'mobile-dj-manager' ) . ':</label><br />' . "\r\n";
			echo '<input type="text" name="field_value" id="field_value" value="' . ( ! empty( $editing ) ? esc_attr( $this->fields[ sanitize_text_field( wp_unslash( $_GET['id'] ) ) ]['value'] ) : '' ) .
				'" class="small-text" />';
			echo '</p>' . "\r\n";

			echo '<p>' . "\r\n";
			echo '<label class="mdjm-label" for="field_checked">' . esc_html__( 'Checked by Default', 'mobile-dj-manager' ) . '?</label><br />' . "\r\n";
			echo '<input type="checkbox" name="field_checked" id="field_checked" value="1"' .
				( ! empty( $editing ) && $this->fields[ sanitize_text_field( wp_unslash( $_GET['id'] ) ) ]['checked'] ? ' checked="checked"' : '' ) . '" />';
			echo '</p>' . "\r\n";
			?>
			</div>

			<script type="text/javascript">
			function whichField() {
				var type = field_type.options[field_type.selectedIndex].value;
				var dropdown_div =  document.getElementById("value_field_dropdown");
				var checkbox_div =  document.getElementById("value_field_checkbox");

				if (type == 'text') {
					dropdown_div.style.display = "none";
					checkbox_div.style.display = "none";
				}
				if (type == 'dropdown')	{
					dropdown_div.style.display = "block";
					checkbox_div.style.display = "none";
				}
				if (type == 'checkbox')	{
					dropdown_div.style.display = "none";
					checkbox_div.style.display = "block";
				}
			}
			</script>

			<?php
			// Description
			echo '<p>' . "\r\n";
			echo '<label class="mdjm-label" for="field_desc">' . esc_html__( 'Description', 'mobile-dj-manager' ) . ':</label><br />' . "\r\n";
			echo '<input type="text" name="field_desc" id="field_desc" value="' . ( ! empty( $editing ) ? esc_attr( $this->fields[ sanitize_text_field( wp_unslash( $_GET['id'] ) ) ]['desc'] ) : '' ) .
				'" class="regular-text" />';
			echo '</p>' . "\r\n";

			// Options
			echo '<p>' . "\r\n";
			echo '<input type="checkbox" name="field_enabled" id="field_enabled" value="1"' .
				( ! empty( $editing ) && ! empty( $this->fields[ $_GET['id'] ]['display'] ) ? ' checked="checked"' : '' ) .
				( ! empty( $editing ) && in_array( $this->fields[ $_GET['id'] ]['id'], $must ) ? ' disabled="disabled"' : '' ) . ' />' .
				'<label class="mdjm-label" for="field_enabled">' . esc_html__( ' Field Enabled?', 'mobile-dj-manager' ) . '</label>';
			if ( ! empty( $editing ) && in_array( $this->fields[ $_GET['id'] ]['id'], $must ) ) {
				echo '<input type="hidden" name="field_enabled" id="field_enabled" value="1" />' . "\r\n";
			}
			echo '</p>' . "\r\n";
			echo '<div id="required_checkbox">' . "\r\n";
			echo '<p>' . "\r\n";
			echo '<input type="checkbox" name="field_required" id="field_required" value="1"' .
				( ! empty( $editing ) && ! empty( $this->fields[ $_GET['id'] ]['required'] ) ? ' checked="checked"' : '' ) .
				( ! empty( $editing ) && in_array( $this->fields[ $_GET['id'] ]['id'], $must ) ? ' disabled="disabled"' : '' ) . ' />' .
				'<label class="mdjm-label" for="field_required">' . esc_html__( ' Required Field?', 'mobile-dj-manager' ) . '</label>';
			if ( ! empty( $editing ) && in_array( $this->fields[ $_GET['id'] ]['id'], $must ) ) {
				echo '<input type="hidden" name="field_required" id="field_required" value="1" />' . "\r\n";
			}
			echo '</p>' . "\r\n";
			echo '</div>' . "\r\n";

			?>
			<script type="text/javascript">
			function showRequired() {
				var type = field_type.options[field_type.selectedIndex].value;
				var required_div = document.getElementById("required_checkbox");

				if (type == 'checkbox')	{
					required_div.style.display = "none";
				}
				else	{
					required_div.style.display = "block";
				}
			}
			</script>
			<?php
			echo '<p>';
			submit_button(
				( empty( $editing ) ? __( 'Add Field', 'mobile-dj-manager' ) : __( 'Save Changes', 'mobile-dj-manager' ) ),
				'primary',
				'submit',
				false
			);

			if ( ! empty( $editing ) ) {
				echo '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
				echo '<a href="' . esc_url( mdjm_get_admin_page( 'client_fields' ) ) . '" class="button-secondary">' .
					esc_html__( 'Cancel Changes', 'mobile-dj-manager' ) . '</a>';
			}

			echo '</p>' . "\r\n";

			// End form
			echo '</form>' . "\r\n";
			// End Right Column
			echo '</div>' . "\r\n";

		} // manage_field_form

		/*
		 * Display icons to identify the field configuration
		 *
		 *
		 * @param	arr		$field		The field which to query
		 */
		function field_icons( $field ) {
			$dir = MDJM_PLUGIN_URL . '/assets/images/form-icons';

			$output = '';

			if ( ! empty( $field['required'] ) ) {
				$output .= '<img src="' . $dir . '/req_field.jpg" width="14" height="14" alt="' . __( 'Required Field', 'mobile-dj-manager' ) . '" title="' . __( 'Required Field', 'mobile-dj-manager' ) . '" />' . "\r\n";

			} else {
				$output .= '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
			}

			if ( $field['type'] == 'checkbox' && ! empty( $field['checked'] ) ) {
				$output .= '<img src="' . $dir . '/captcha.jpg" width="14" height="14" alt="' . __( 'Checked checkbox Field', 'mobile-dj-manager' ) . '" title="' . __( 'Checked', 'mobile-dj-manager' ) . '" />';

			} else {
				$output .= '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
			}

			if ( $field['type'] == 'dropdown' ) {
				$output .= '<img src="' . $dir . '/select_list.jpg" width="14" height="14" alt="' . __( 'Dropdown field options', 'mobile-dj-manager' ) . '" title="' . str_replace( ',', "\r\n", $field['value'] ) . '" />' . "\r\n";
			}

			if ( $field['type'] == 'checkbox' ) {
				$output .= '<img src="' . $dir . '/select_list.jpg" width="14" height="14" alt="' . __( 'Checked Value', 'mobile-dj-manager' ) . '" title="' . $field['value'] . '" />' . "\r\n";

			} else {
				$output .= '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
			}

			return $output;

		} // field_icons
	} // class

} // if( !class_exists( 'MDJM_ClientFields' ) )
