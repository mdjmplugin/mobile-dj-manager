<?php
	defined( 'ABSPATH' ) || die( 'Direct access to this page is disabled!!!' );

/**
 * Class: MDJM_Event_Fields
 * Contains methods relating to the custom event fields functionality
 *
 *
 */
if ( ! class_exists( 'MDJM_Event_Fields' ) ) :
	class MDJM_Event_Fields {
		/**
		 * Class constructor
		 *
		 *
		 *
		 */
		public function __construct() {
			add_action( 'admin_init', array( &$this, 'custom_fields_controller' ) );

			add_action( 'mdjm_add_content_tags', array( &$this, 'add_tags' ) );
			add_action( 'mdjm_event_overview_standard_client_sections', array( $this, 'custom_client_event_fields' ), 100 );
			add_action( 'mdjm_event_overview_standard_event_sections', array( $this, 'custom_event_details_fields' ), 100 );
			add_action( 'mdjm_event_overview_standard_venue_sections', array( $this, 'custom_venue_event_fields' ), 100 );
			add_action( 'mdjm_save_event', array( &$this, 'manage_custom_fields_on_event_save' ), 10 );

			add_filter( 'mdjm_shortcode_filter_pairs', array( &$this, 'custom_event_fields_shortcode_pairs' ), 10, 3 );

			add_filter( 'dcf_mapping_fields', array( &$this, 'dcf_mapping_fields' ), 10, 2 );
			add_filter( 'dcf_mapping_fields_on_submit', array( &$this, 'dcf_mapping_fields_on_submit' ), 10, 2 );
		}

		/**
		 * Load the custom tags into the MDJM_Content_Tags class.
		 *
		 * @since   1.3
		 * @param
		 * @return
		 */
		public function add_tags() {

			$query  = mdjm_get_custom_fields();
			$fields = $query->get_posts();

			if ( $fields ) {
				foreach ( $fields as $field ) {
					mdjm_add_content_tag(
						'mdjm_cf_' . strtolower( str_replace( array( ' ', '/' ), array( '_', '' ), get_the_title( $field->ID ) ) ),
						$field->post_content,
						array( &$this, 'do_custom_field_tags' )
					);
				}
			}

		} // add_tags

		/**
		 * Process the custom field tags.
		 *
		 * @since   1.3
		 * @param   int     $event_id   The event ID.
		 * @param   int     $client_id  The client ID (not used).
		 * @param   str     $tag        The tag being processed.
		 * @return  str     The value of the custom field for the event or an empty string.
		 */
		public function do_custom_field_tags( $event_id = '', $client_id = '', $tag = '' ) {
			if ( empty( $event_id ) || empty( $tag ) ) {
				return '';
			}
			$tag = str_replace( array( '-', '(', ')', 'mdjm_cf_' ), array( '_', '', '', '' ), $tag );

			$meta_key = '_mdjm_event_' . str_replace( array( '-', '(', ')' ), array( '_', '', '' ), $tag );

			$meta_value = get_post_meta( $event_id, $meta_key, true );

			if ( empty( $meta_value ) ) {
				return '';
			}

			return apply_filters( "do_custom_field_tag_{$meta_key}", $meta_value, $event_id, $tag );

		} // do_custom_field_tags

		/**
		 * Determine if any actions need to be taken for custom fields
		 *
		 *
		 *
		 *
		 */
		public function custom_fields_controller() {
			if ( isset( $_POST['submit_custom_field'] ) ) {
				if ( $_POST['submit_custom_field'] == __( 'Add Field', 'mobile-dj-manager' ) ) {
					$this->add_field();

				} elseif ( $_POST['submit_custom_field'] == __( 'Save Changes', 'mobile-dj-manager' ) ) {
					$this->update_field();
                }
			}

			if ( isset( $_GET['delete_custom_field'], $_GET['id'] ) ) {
				$this->delete_field();
            }

		} // custom_fields_controller

		/**
		 * Display a list of custom shortcodes.
		 *
		 * This function outputs a HTML list of custom shortcodes to be used.
		 *
		 * @since   1.3
		 * @param
		 * @return  HTML formatted list of tags.
		 */
		public static function list_custom_tags() {
			$content_tags = mdjm_get_content_tags();
			$output       = '';

			if ( count( $content_tags ) > 0 ) {
				foreach ( $content_tags as $content_tag ) {
					if ( strpos( strtolower( $content_tag['tag'] ), 'mdjm_cf_' ) !== false ) {
						$custom_tags[] = $content_tag;
					}
				}
			}

			if ( ! empty( $custom_tags ) ) {
				$output .= '<ul>';

				foreach ( $custom_tags as $custom_tag ) {
					$output .= '<li style="font-style: italic; font-size: smaller;">';
					$output .= "{{$custom_tag['tag']}} - {$custom_tag['description']}";
					$output .= '</li>';
				}

				$output .= '</ul>';
			}

			echo $output; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		} // list_custom_tags

		/**
		 * Insert a new custom field post to the relevant section
		 *
		 * @param
		 *
		 * @return
		 */
		public function add_field() {

			if ( ! isset( $_POST['_mdjm_field_section'] ) || ! isset( $_POST['field_label'] ) ) {
				wp_safe_redirect( mdjm_get_admin_page( 'custom_event_fields' ) . '&message=4' );
				exit;
			}

			$existing = mdjm_get_custom_fields( sanitize_text_field( wp_unslash( $_POST['_mdjm_field_section'] ) ), 'menu_order', 'DESC', 1 );

			if ( $existing->have_posts() ) {
				while ( $existing->have_posts() ) {
					$existing->the_post();

					$menu_order = $existing->post->menu_order;
				}
				wp_reset_postdata();

				if ( ! empty( $menu_order ) ) {
					$menu_order++;

				} else {
					$menu_order = 1;
                }
			} else {
				$menu_order = 1;
            }

			$id = wp_insert_post(
				array(
					'post_title'   => sanitize_text_field( wp_strip_all_tags( wp_unslash( $_POST['field_label'] ) ) ),
					'post_content' => ! empty( $_POST['field_desc'] ) ? sanitize_text_field( wp_unslash( $_POST['field_desc'] ) ) : '',
					'post_status'  => 'publish',
					'post_author'  => get_current_user_id(),
					'post_type'    => 'mdjm-custom-fields',
					'menu_order'   => $menu_order,
                ),
            true );

			/**
			* Success
			* We can now add the meta data
			*/
			if ( ! empty( $id ) ) {
				if ( MDJM_DEBUG == true ) {
					MDJM()->debug->log_it( 'Custom field added ' . sanitize_text_field( wp_unslash( $_POST['field_label'] ) ), true );
                }

				foreach ( $_POST as $key => $value ) {
					if ( substr( $key, 0, 5 ) != '_mdjm' ) {
						continue;
                    }

					// Add the meta data to the post
					add_post_meta( $id, $key, $value );
				}

				wp_safe_redirect( mdjm_get_admin_page( 'custom_event_fields' ) . '&message=1' );
				exit;
			} else {
				/**
				 * Error
				 * Lets log it
				 */

				if ( MDJM_DEBUG == true && is_wp_error( $id ) ) {
					MDJM()->debug->log_it( 'Unable to create custom field ' . sanitize_text_field( wp_unslash( $_POST['field_label'] ) ) . '. ' . get_error_message(), true );
                }

				wp_safe_redirect( mdjm_get_admin_page( 'custom_event_fields' ) . '&message=4' );
				exit;
			}
		} // add_field

		/**
		 * Update a custom field post
		 *
		 * @param
		 *
		 * @return
		 */
		public function update_field() {

			if ( ! isset( $_POST['custom_field_id'] ) || ! isset( $_GET['id'] ) || ! isset( $_POST['field_label'] ) ) {
				wp_safe_redirect( mdjm_get_admin_page( 'custom_event_fields' ) . '&message=5' );
				exit;
			}

			// Retrieve the existing settings for the field so we can compare
			$existing = get_post( absint( wp_unslash( $_POST['custom_field_id'] ) ) );

			if ( ! $existing ) {
				if ( MDJM_DEBUG == true ) {
					MDJM()->debug->log_it( 'Unable to update field with ID: ' . absint( wp_unslash( $_GET['id'] ) ) . '. May not exist', true );
                }

				wp_safe_redirect( mdjm_get_admin_page( 'custom_event_fields' ) . '&message=5' );
				exit;
			}

			$id = wp_update_post(
				array(
					'ID'           => absint( wp_unslash( $_POST['custom_field_id'] ) ),
					'post_title'   => sanitize_text_field( wp_strip_all_tags( wp_unslash( $_POST['field_label'] ) ) ),
					'post_content' => ! empty( $_POST['field_desc'] ) ? sanitize_text_field( wp_unslash( $_POST['field_desc'] ) ) : '',
					'post_name'    => sanitize_title( wp_unslash( $_POST['field_label'] ) ),
					'post_status'  => 'publish',
                ),
            true );

			/**
			* Success
			* We can now add the meta data
			*/
			if ( ! empty( $id ) ) {
				if ( MDJM_DEBUG == true ) {
					MDJM()->debug->log_it( 'Custom field updated ' . sanitize_text_field( wp_unslash( $_POST['field_label'] ) ), true );
                }

				foreach ( $_POST as $key => $value ) {
					if ( substr( $key, 0, 5 ) != '_mdjm' ) {
						continue;
                    }

					// Add the meta data to the post
					update_post_meta( $id, $key, $value );
				}

				wp_safe_redirect( mdjm_get_admin_page( 'custom_event_fields' ) . '&message=2' );
				exit;
			} else {
				/**
				 * Error
				 * Lets log it
				 */

				if ( MDJM_DEBUG == true && is_wp_error( $id ) ) {
					MDJM()->debug->log_it( 'Unable to create custom field ' . sanitize_text_field( wp_unslash( $_POST['field_label'] ) ) . '. ' . get_error_message(), true );
                }

				wp_safe_redirect( mdjm_get_admin_page( 'custom_event_fields' ) . '&message=4' );
				exit;
			}
		} // update_field

		/**
		 * Delete a custom field with force
		 *
		 * @param
		 *
		 * @return
		 */
		public function delete_field() {
			if ( ! isset( $_GET['id'] ) ) {
				wp_safe_redirect( mdjm_get_admin_page( 'custom_event_fields' ) . '&message=6' );
				exit;
			}

			if ( wp_delete_post( absint( wp_unslash( $_GET['id'] ) ), true ) ) {
				if ( MDJM_DEBUG == true ) {
					MDJM()->debug->log_it( 'Custom event field deleted with ID: ' . absint( wp_unslash( $_GET['id'] ) ), true );
                }

				wp_safe_redirect( mdjm_get_admin_page( 'custom_event_fields' ) . '&message=3' );
				exit;
			} else {
				if ( MDJM_DEBUG == true ) {
					MDJM()->debug->log_it( 'Custom event field with ID: ' . absint( wp_unslash( $_GET['id'] ) ) . ' could not be deleted', true );
                }

				wp_safe_redirect( mdjm_get_admin_page( 'custom_event_fields' ) . '&message=6' );
				exit;
			}
		} // delete_field

		/**
		 * Display the settings page to enable admins to add/delete/edit fields
		 *
		 *
		 *
		 *
		 */
		public static function custom_event_field_settings() {
			if ( isset( $_GET['message'] ) ) {
				$messages = array(
					'1' => array( 'updated', __( 'Field added successfully.', 'mobile-dj-manager' ) ),
					'2' => array( 'updated', __( 'Field updated successfully.', 'mobile-dj-manager' ) ),
					'3' => array( 'updated', __( 'Field deleted successfully.', 'mobile-dj-manager' ) ),
					'4' => array( 'error', __( 'Unable to add field.', 'mobile-dj-manager' ) ),
					'5' => array( 'error', __( 'Unable to update field.', 'mobile-dj-manager' ) ),
					'6' => array( 'error', __( 'Unable to delete field.', 'mobile-dj-manager' ) ),
				);

				mdjm_update_notice( $messages[ sanitize_text_field( wp_unslash( $_GET['message'] ) ) ][0], $messages[ sanitize_text_field( wp_unslash( $_GET['message'] ) ) ][1], true );
			}
			?>
			<div class="mdjm-event-field-container">
			<div class="mdjm-event-field-column-left">

			<?php
			/**
			 * Display the Custom fields
			 *
			 *
			 *
			 */
			$field_types = array( 'client', 'event', 'venue' );

			foreach ( $field_types as $field_type ) {
				?>
				<h3><?php printf( esc_html__( '%s Fields', 'mobile-dj-manager' ), esc_html( ucfirst( $field_type ) ) ); ?></h3>
				<table id="mdjm_custom-<?php echo esc_attr( $field_type ); ?>" class="widefat mdjm-custom-<?php echo esc_attr( $field_type ); ?>-list-item" style="width:90%">
					<thead>
					<tr>
                    	<th style="width: 20px;"></th>
						<th style="width: 15%;"><?php esc_html_e( 'Label', 'mobile-dj-manager' ); ?></th>
						<th style="width: 15%;"><?php esc_html_e( 'Type', 'mobile-dj-manager' ); ?></th>
						<th style="width: 30%;"><?php esc_html_e( 'Description', 'mobile-dj-manager' ); ?></th>
                        <th style="width: 15%;"><?php esc_html_e( 'Options', 'mobile-dj-manager' ); ?></th>
						<th><?php esc_html_e( 'Actions', 'mobile-dj-manager' ); ?></th>
					</tr>
					</thead>
					<tbody>
				<?php
				$fields = mdjm_get_custom_fields( $field_type );

				// Display the custom fields
				if ( $fields->have_posts() ) {
					$i = 0;

					while ( $fields->have_posts() ) {
						$fields->the_post();
						?>
						<tr id="customfields_<?php echo esc_attr( $fields->post->ID ); ?>" class="
							<?php echo ( $i == 0 ? 'alternate mdjm_sortable_row' : 'mdjm_sortable_row' ); ?>" data-key="<?php echo esc_attr( $fields->post->ID ); ?>">
                            <td><span class="mdjm_draghandle"></span></td>
                            <td><?php the_title(); ?></td>
                            <td><?php echo esc_html( ucfirst( get_post_meta( $fields->post->ID, '_mdjm_field_type', true ) ) ); ?></td>
                            <td><?php echo esc_html( $fields->post->post_content ); ?></td>
                            <td><?php self::field_icons( $fields->post->ID ); ?></td>
                            <td>
                                <a href="<?php echo esc_url( mdjm_get_admin_page( 'custom_event_fields' ) . '&edit_custom_field=1&id=' . $fields->post->ID ); ?>"
                                class="button button-primary button-small"><?php esc_html_e( 'Edit', 'mobile-dj-manager' ); ?></a>
                                &nbsp;&nbsp;&nbsp;<a href="<?php echo esc_url( mdjm_get_admin_page( 'custom_event_fields' ) . '&delete_custom_field=1&id=' . $fields->post->ID ); ?>"
                        		class="button button-secondary button-small"><?php esc_html_e( 'Delete', 'mobile-dj-manager' ); ?></a>
                            </td>
                        </tr>
                        <?php
						$i++;

						if ( $i == 2 ) {
							$i = 0;
                        }
					}
					wp_reset_postdata();
				} else {
					// No custom fields

					?>
					<tr>
						<td colspan="6"><?php printf( esc_html__( 'No Custom %s Fields Defined!', 'mobile-dj-manager' ), esc_html( ucfirst( $field_type ) ) ); ?></td>
					</tr>
					<?php
				}

				?>
					</tbody>
					<tfoot>
					<tr style="font-weight: bold;">
                    	<th style="width: 20px;"></th>
						<th style="width: 15%;"><?php esc_html_e( 'Label', 'mobile-dj-manager' ); ?></th>
						<th style="width: 15%;"><?php esc_html_e( 'Type', 'mobile-dj-manager' ); ?></th>
						<th style="width: 25%;"><?php esc_html_e( 'Description', 'mobile-dj-manager' ); ?></th>
                        <th style="width: 15%;"><?php esc_html_e( 'Options', 'mobile-dj-manager' ); ?></th>
						<th><?php esc_html_e( 'Actions', 'mobile-dj-manager' ); ?></th>
					</tr>
					</tfoot>
				</table>
				<?php
			} // End foreach loop
			?>
			</div>

			<?php self::add_new_custom_field_table( $field_types ); ?>

			</div>
			<?php
		} // custom_event_field_settings

		/**
		 * Add the table allowing addition of new custom fields
		 *
		 * @params  arr     $field_types    Required: The types of custom fields that can be created
		 *
		 *
		 */
		public static function add_new_custom_field_table( $field_types ) {
			wp_enqueue_script( 'jquery' );

			wp_register_script( 'jquery-validation-plugin', MDJM_PLUGIN_URL . '/assets/libs/jquery-validate/jquery.validate.min.js', array(), MDJM_VERSION_NUM, true );
			wp_enqueue_script( 'jquery-validation-plugin' );
			?>
			<script type="text/javascript">
			jQuery().ready(function()	{
				jQuery("#mdjm-custom-event-fields").validate(	{

					/* -- Classes -- */
					errorClass: "mdjm-form-error",
					validClass: "mdjm-form-valid",
					focusInvalid: false,

					messages:	{
						field_label: " <?php esc_attr_e( 'Enter a label!', 'mobile-dj-manager' ); ?>",
					}

				} ); // Validate
			} ); // function
			</script>
			<div class="mdjm-event-field-column-right">
			<form name="mdjm-custom-event-fields" id="mdjm-custom-event-fields" method="post" action="<?php echo esc_url( mdjm_get_admin_page( 'custom_event_fields' ) ); ?>">

			<?php
			if ( isset( $_GET['edit_custom_field'], $_GET['id'] ) ) {
				$editing = true;
			}

			// If editing a field we need this hidden field to identify it
			if ( ! empty( $editing ) ) {
				$field = get_post( absint( wp_unslash( $_GET['id'] ) ) );
				echo '<input type="hidden" name="custom_field_id" id="custom_field_id" value="' . absint( wp_unslash( $_GET['id'] ) ) . '" />' . "\r\n";
			}

			echo '<h3>';
			echo ( empty( $editing ) ? esc_html__( 'Add New Custom Field', 'mobile-dj-manager' ) :
				sprintf(
					esc_html__( 'Edit the %1$s%2$s%3$s %4$s', 'mobile-dj-manager' ),
					'<span class="mdjm-color">',
					esc_html( get_the_title( absint( wp_unslash( $_GET['id'] ) ) ) ),
					'</span>',
                esc_html__( 'Field', 'mobile-dj-manager' ) ) );
			echo '</h3>' . "\r\n";

			// Types of input fields that can be selected
			$types = array( 'text', 'checkbox', 'select', 'multi select', 'textarea' );

			?>

            <p>
			<label for="_mdjm_field_section"><?php esc_html_e( 'Section', 'mobile-dj-manager' ); ?>:</label><br />
			<select name="_mdjm_field_section" id="_mdjm_field_section">
			<?php
			foreach ( $field_types as $type ) {
				echo '<option value="' . esc_attr( $type ) . '"';
				if ( ! empty( $editing ) ) {
					selected( $type, get_post_meta( absint( wp_unslash( $_GET['id'] ) ), '_mdjm_field_section', true ) );
                }
				echo '>' . sprintf( esc_html__( '%s Section', 'mobile-dj-manager' ), esc_html( ucfirst( $type ) ) ) . '</option>' . "\r\n";
			}
			?>
			</select>
			</p>

			<p>
			<label for="field_label"><?php esc_html_e( 'Label', 'mobile-dj-manager' ); ?>:</label><br />
			<input type="text" name="field_label" id="field_label" class="regular-text" value="<?php echo ( ! empty( $editing ) ? esc_attr( get_the_title( absint( wp_unslash( $_GET['id'] ) ) ) ) : '' ); ?>" class="regular-text" required="required" />
			</p>

			<p>
			<label for="_mdjm_field_type"><?php esc_html_e( 'Type', 'mobile-dj-manager' ); ?>:</label><br />
			<select name="_mdjm_field_type" id="_mdjm_field_type" onChange="whichField();">
			<?php
			foreach ( $types as $type ) {
				echo '<option value="' . esc_attr( $type ) . '"';
				if ( ! empty( $editing ) ) {
					selected( $type, get_post_meta( absint( wp_unslash( $_GET['id'] ) ), '_mdjm_field_type', true ) );
                }
				echo '>' . sprintf( esc_html__( '%s Field', 'mobile-dj-manager' ), esc_html( ucwords( $type ) ) ) . '</option>' . "\r\n";
			}
			?>
			</select>
			</p>

            <style type="text/css">
				#value_field_select	{
					display: <?php echo ( ! empty( $editing ) && get_post_meta( absint( wp_unslash( $_GET['id'] ) ), '_mdjm_field_type', true ) == 'select' ? 'block;' : 'none;' ); ?>
				}
				#value_field_checkbox	{
					display: <?php echo ( ! empty( $editing ) && get_post_meta( absint( wp_unslash( $_GET['id'] ) ), '_mdjm_field_type', true ) == 'checkbox' ? 'block;' : 'none;' ); ?>
				}
			</style>

            <div id="value_field_select">
				<p>
				<label for="_mdjm_field_options"><?php esc_html_e( 'Selectable Options', 'mobile-dj-manager' ); ?>:</label><br />
				<textarea name="_mdjm_field_options" id="_mdjm_field_options" class="all-options" rows="5">
                <?php
					echo ( ! empty( $editing ) ? esc_html( get_post_meta( absint( wp_unslash( $_GET['id'] ) ), '_mdjm_field_options', true ) ) : '' );
				?>
                    </textarea><br />
                <span class="description"><?php esc_html_e( 'One entry per line', 'mobile-dj-manager' ); ?></span>
				</p>
            </div>
            <div id="value_field_checkbox">
				<p>
				<label for="_mdjm_field_value"><?php esc_html_e( 'Checked Value', 'mobile-dj-manager' ); ?>:</label><br />
				<input type="text" name="_mdjm_field_value" id="_mdjm_field_value" value="
                <?php
                echo ( ! empty( $editing ) ?
					esc_attr( get_post_meta( absint( wp_unslash( $_GET['id'] ) ), '_mdjm_field_value', true ) ) : '1' );
				?>
                    " class="small-text" />
				</p>

				<p>
				<input type="checkbox" name="_mdjm_field_checked" id="_mdjm_field_checked" value="1"
					<?php
					if ( ! empty( $editing ) ) {
						checked( '1', get_post_meta( absint( wp_unslash( $_GET['id'] ) ), '_mdjm_field_checked', true ) );
                    }
					?>
                    />&nbsp;<label for="_mdjm_field_checked"><?php esc_html_e( 'Checked by Default', 'mobile-dj-manager' ); ?>?</label>
				</p>
            </div>

            <script type="text/javascript">
				function whichField() {
					var type = _mdjm_field_type.options[_mdjm_field_type.selectedIndex].value;
					var select_div =  document.getElementById("value_field_select");
					var checkbox_div =  document.getElementById("value_field_checkbox");

					if (type == 'text' || type == 'textarea') {
						select_div.style.display = "none";
						checkbox_div.style.display = "none";
					}
					if (type == 'select' || type == 'multi select')	{
						select_div.style.display = "block";
						checkbox_div.style.display = "none";
					}
					if (type == 'checkbox')	{
						select_div.style.display = "none";
						checkbox_div.style.display = "block";
					}
				}
            </script>

			<p>
			<label for="field_desc"><?php esc_html_e( 'Description', 'mobile-dj-manager' ); ?>:</label><br />
			<input type="text" name="field_desc" id="field_desc" value="
            <?php
            echo ( ! empty( $editing ) ?
			esc_attr( $field->post_content ) : '' );
			?>
            " class="regular-text" /><br />
			<span class="description"><?php esc_html_e( "Not visible to client's", 'mobile-dj-manager' ); ?></span>
			</p>

			<p>
			<?php
			submit_button( ( empty( $editing ) ? esc_html__( 'Add Field', 'mobile-dj-manager' ) : esc_html__( 'Save Changes', 'mobile-dj-manager' ) ),
                'primary',
                'submit_custom_field',
            false );

			if ( ! empty( $editing ) ) {
				echo '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
				echo '<a href="' . esc_url( mdjm_get_admin_page( 'custom_event_fields' ) ) . '" class="button-secondary">' .
					esc_html__( 'Cancel Changes', 'mobile-dj-manager' ) . '</a>';
			}
			?>
			</p>
			</form>
            <h4><?php esc_html_e( 'Your Custom Shortcodes', 'mobile-dj-manager' ); ?></h4>
            <p><?php self::list_custom_tags(); ?></p>
			</div>
			<?php
		} // add_new_custom_field_table

		/**
		 * Display icons to identify the field configuration
		 *
		 * @param   int     $field_id   The field which to query
		 *
		 * @return  str                 Echo the HTML required to display the necessary icons
		 */
		public static function field_icons( $field_id ) {
			$dir = MDJM_PLUGIN_URL . '/assets/images/form-icons';

			$output = '';

			$type     = get_post_meta( $field_id, '_mdjm_field_type', true );
			$selected = get_post_meta( $field_id, '_mdjm_field_checked', true );
			$value    = $type == 'checkbox' ? get_post_meta( $field_id, '_mdjm_field_value', true ) : get_post_meta( $field_id, '_mdjm_field_options', true );

			if ( ! empty( $type ) && $type == 'checkbox' && $selected == '1' ) {
				$output .= '<img src="' . $dir . '/captcha.jpg" width="14" height="14" alt="' . __( 'Checked Checkbox Field', 'mobile-dj-manager' ) . '" title="' . __( 'Checked', 'mobile-dj-manager' ) . '" />';
            }

			if ( $type == 'checkbox' ) {
				$output .= '<img src="' . $dir . '/select_list.jpg" width="14" height="14" alt="' . __( 'Checked Value', 'mobile-dj-manager' ) . '" title="' . __( 'Checked Value', 'mobile-dj-manager' ) . ' = ' . $value . '" />' . "\r\n";
            }

			if ( $type == 'select' || $type == 'multi select' ) {
				$output .= '<img src="' . $dir . '/select_list.jpg" width="14" height="14" alt="' . __( 'Dropdown field options', 'mobile-dj-manager' ) . '" title="' . str_replace( ',', "\r\n", $value ) . '" />' . "\r\n";
            }

			echo $output; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		} // field_icons

		/**
		 * Add the custom fields to the end of the event client details metabox.
		 *
		 * @since   1.3.7
		 * @called: mdjm_event_client_fields
		 * @param   int     $event_id   The Event ID
		 * @return  str     $output     This function must output the full required HTML
		 */
		public function custom_client_event_fields( $event_id ) {
			global $mdjm_event, $mdjm_event_update;

			$query  = mdjm_get_custom_fields( 'client' );
			$fields = $query->get_posts();

			if ( $fields ) :
				?>
				<div class="mdjm-client-custom-fields">
					<?php foreach ( $fields as $field ) : ?>
						<div class="mdjm-custom-field">
							<?php self::display_input( $field, $mdjm_event ); ?>
						</div>
					<?php endforeach; ?>
				</div>
				<?php
            endif;
		} // custom_client_event_fields

		/**
		 * Add the custom fields to the end of the event Event Details metabox.
		 *
		 * @since   1.3.7
		 * @called: mdjm_event_details_fields
		 * @param   int     $event_id   The Event ID
		 * @return  str     $output     This function must output the full required HTML
		 */
		public function custom_event_details_fields( $event_id ) {
			global $mdjm_event, $mdjm_event_update;

			$query  = mdjm_get_custom_fields( 'event' );
			$fields = $query->get_posts();

			if ( $fields ) :
				?>
				<div class="mdjm-event-custom-fields">
					<?php foreach ( $fields as $field ) : ?>
						<div class="mdjm-custom-field">
							<?php self::display_input( $field, $mdjm_event ); ?>
						</div>
					<?php endforeach; ?>
				</div>
				<?php
            endif;
		} // custom_event_details_fields

		/**
		 * Add the custom fields to the end of the event Venue Details metabox.
		 *
		 * @since   1.3.7
		 * @called: mdjm_event_venue_fields
		 * @param   int     $event_id   The Event ID
		 * @return  str     $output     This function must output the full required HTML
		 */
		public function custom_venue_event_fields( $event_id ) {
			global $mdjm_event, $mdjm_event_update;

			$query  = mdjm_get_custom_fields( 'venue' );
			$fields = $query->get_posts();

			if ( $fields ) :
				?>
				<div class="mdjm-venue-custom-fields">
					<?php foreach ( $fields as $field ) : ?>
						<div class="mdjm-custom-field">
							<?php self::display_input( $field, $mdjm_event ); ?>
						</div>
					<?php endforeach; ?>
				</div>
				<?php
            endif;
		} // custom_venue_event_fields

		/**
		 * Output the input field for the current field
		 *
		 * @param   obj     $field      The post object for the custom field we are displaying
		 *          obj     $mdjm_event The MDJM Event object for the event
		 *
		 */
		public function display_input( $field, $mdjm_event ) {
			$name      = '_mdjm_event_' . strtolower( str_replace( '-', '_', $field->post_name ) );
			$type      = get_post_meta( $field->ID, '_mdjm_field_type', true );
			$current   = get_post_meta( $field->ID, '_mdjm_field_checked', true );
			$title     = get_the_title( $field->ID );
			$div_class = 'mdjm-custom-event-field';

			if ( 'draft' != $mdjm_event->post_status && 'auto-draft' != $mdjm_event->post_status ) {
				$current = $mdjm_event->get_meta( $name );
			}

			$value = $type == 'checkbox' ?
				get_post_meta( $field->ID, '_mdjm_field_value', true ) :
				get_post_meta( $field->ID, '_mdjm_field_options', true );

			switch ( $type ) {
				case 'checkbox':
					$div_class = 'mdjm-repeatable-option mdjm_repeatable_default_wrapper';
					$output    = MDJM()->html->checkbox( array(
						'name'    => $name,
						'current' => $current,
						'value'   => $value,
					) );
					break;

				case 'text':
				default:
					$output = MDJM()->html->text( array(
						'name'  => $name,
						'value' => esc_attr( get_post_meta( $mdjm_event->ID, $name, true ) ),
						'class' => 'mdjm-custom-field',
					) );
					break;

				case 'select':
				case 'multi select':
					$values   = explode( "\r\n", $value );
					$values   = array_map( 'esc_attr', $values );
                    $selected = get_post_meta( $mdjm_event->ID, $name, true );
                    foreach ( $values as $key => $value ) {
                        $options[ $value ] = $value;
                    }
					$output = MDJM()->html->select( array(
						'name'     => $name,
						'options'  => $options,
						'selected' => $selected,
						'chosen'   => true,
						'multiple' => 'multi select' == $type ? true : false,
						'data'     => array(
							'search-type'        => 'custom',
							'search-placeholder' => __( 'Type to search all options', 'mobile-dj-manager' ),
						),
					) );
					break;

				case 'textarea':
					$output = MDJM()->html->textarea( array(
						'name'  => $name,
						'value' => get_post_meta( $mdjm_event->ID, $name, true ),
					) );
					break;
			}

			?>
			<div class="<?php echo esc_attr( $div_class ); ?>">
				<span class="mdjm-repeatable-row-setting-label"><?php echo esc_html( $title ); ?></span>
				<label class="mdjm-custom-field-label">
					<?php echo $output; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
				</label>
			</div>
			<?php
		} // display_input

		/**
		 * Manage checkboxes on event save.
		 * When an event is saved on the event screen and a checkbox is not checked,
		 * no value is posted and therefore the event meta key is not updated.
		 *
		 * @params  obj     $post   The events WP_POST object
		 * @return  void
		 */
		public function manage_custom_fields_on_event_save( $post ) {

			$mdjm_event    = new MDJM_Event( $post->ID );
			$query         = mdjm_get_custom_fields();
			$custom_fields = $query->get_posts();

			foreach ( $custom_fields as $custom_field ) {

				if ( 'checkbox' != get_post_meta( $custom_field->ID, '_mdjm_field_type', true ) ) {
					continue;
				}

				$key = '_mdjm_event_' . str_replace( '-', '_', $custom_field->post_name );

				if ( $mdjm_event->get_meta( $key ) && empty( $_POST[ $key ] ) ) {
					$meta[ $key ] = '';
				}
			}

			if ( ! empty( $meta ) ) {
				mdjm_update_event_meta( $mdjm_event->ID, $meta );
			}

		} // manage_custom_fields_on_event_save

		/**
		 * Append the custom event fields to the Contact Form mapping options
		 * @called: dcf_mapping_fields hook
		 *
		 * @params  arr     $mappings           Required: The existing mapping options
		 *          str     $type               Required: client | event | venue
		 *
		 *
		 * @return  arr     $mappings_event     The filtered mapping options
		 */
		public function dcf_mapping_fields( $mappings, $type ) {
			$query  = mdjm_get_custom_fields( $type );
			$fields = $query->get_posts();

			if ( $fields ) {
				foreach ( $fields as $field ) {
					$name = '_mdjm_event_' . str_replace( '-', '_', $field->post_name );

					$mappings[ $name ] = sprintf(
                        __( '%s', 'mobile-dj-manager' ),
                    ucfirst( $type ) ) . ' ' . get_the_title( $field->ID );
				}
			}

			return $mappings;
		} // dcf_mapping_fields

		/**
		 * Append the custom event fields to the Contact Form mapping array.
		 * Used on the front end of the contact form
		 * @called: dcf_mapping_fields_on_submit hook
		 *
		 * @params  arr     $mappings           Required: The existing mapping options
		 *          str     $type               Required: client | event | venue
		 *
		 *
		 * @return  arr     $mappings_event     The filtered mapping options
		 */
		public function dcf_mapping_fields_on_submit( $mappings, $type ) {
			$query  = mdjm_get_custom_fields( $type );
			$fields = $query->get_posts();

			if ( $fields ) {
				foreach ( $fields as $field ) {
					$name = '_mdjm_event_' . str_replace( '-', '_', $field->post_name );

					$mappings[] = $name;
				}
			}

			return $mappings;
		} // dcf_mapping_fields_on_submit

		/**
		 * Add the custom event fields and their values to the filter array
		 *
		 * @params  arr     $pairs      Required: The existing pairs array
		 *          int     $event_id   Optional: The post ID of the event
		 *          arr     $eventinfo  Optional: Event info
		 *
		 * @return  arr     $pairs      The filtered pairs array
		 */
		public function custom_event_fields_shortcode_pairs( $pairs, $event_id = '', $eventinfo = '' ) {
			// Get the custom fields
			$query  = mdjm_get_custom_fields();
			$fields = $query->get_posts();

			if ( $fields ) {
				foreach ( $fields as $field ) {
					$meta_key   = '_mdjm_event_' . str_replace( '-', '_', $field->post_name );
					$meta_value = get_post_meta( $event_id, $meta_key, true );

					if ( ! empty( $meta_value ) ) {
						if ( is_array( $meta_value ) ) {
							$meta_value = implode( '<br />', $meta_value );
                        }

						$pairs[ '{MDJM_CF_' . strtoupper( str_replace( ' ', '_', get_the_title( $field->ID ) ) ) . '}' ] = ! empty( $meta_value ) ?
							$meta_value : '';
					}
				}
			}

			return $pairs;
		} // custom_event_fields_shortcode_pairs

	} // class MDJM_Event_Fields
endif;
	new MDJM_Event_Fields();
