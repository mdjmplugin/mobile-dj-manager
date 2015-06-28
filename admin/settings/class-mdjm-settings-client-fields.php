<?php
/*
 * class-mdjm-settings-client-fields.php
 * 16/06/2015
 * @since 2.1
 * The Client Fields settings class
 * 
 */
	
	defined( 'ABSPATH' ) or die( 'Direct access to this page is disabled!!!' );
	
	if ( !current_user_can( 'manage_options' ) && !current_user_can( 'manage_mdjm' ) )
		wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
	
	f_mdjm_has_updated();
	
	/* -- Build the MDJM_ClientFields class -- */
	if( !class_exists( 'MDJM_ClientFields' ) )	{
		class MDJM_ClientFields	{
			/*
			 * The Constructor
			 *
			 *
			 *
			 */
			function __construct()	{
				
				$this->fields = get_option( MDJM_CLIENT_FIELDS );
				foreach( $this->fields as $key => $row )	{
					$field[$key] = $row['position'];	
				}
				// Sort the fields into a positional array
				array_multisort( $field, SORT_ASC, $this->fields );
				
				if( isset( $_GET['action'], $_GET['id'] ) && $_GET['action'] == 'delete_field' )
					$this->delete_field();
				
				if( isset( $_POST['submit'] ) )	{
					if( $_POST['submit'] == 'Add Field' )
						$this->add_field();
						
					if( $_POST['submit'] == 'Save Changes' )
						$this->update_field();
				}
				
				$this->display_fields();
			} // __construct
						
			/*
			 * Process field deletions
			 *
			 *
			 *
			 */
			function delete_field()	{
				global $mdjm_debug;
				
				unset( $this->fields[$_GET['id']] );
				
				if( update_option( MDJM_CLIENT_FIELDS, $this->fields ) )	{
					$mdjm_debug->log_it( 'Client field ' . $_GET['id'] . ' has been deleted', true );
					mdjm_update_notice( 'updated', 'The field was deleted successfully.' );
				}
				else	{
					$mdjm_debug->log_it( 'Error deleting Client field ' . $_GET['id'], true );
					mdjm_update_notice( 'error', 'Field could not be deleted' );	
				}
			} // delete_field
			
			/*
			 * Add new field
			 *
			 *
			 *
			 */
			function add_field()	{
				global $mdjm_debug, $current_user;				
								
				// Set the field id
				$id = preg_replace( '/[^a-zA-Z0-9_-]$/s', '', sanitize_text_field( $_POST['field_label'] ) );
				
				$id = strtolower( str_replace( array( ' ', '.' ), array( '_', '' ), $id ) );
								
				if( array_key_exists( $id, $this->fields ) )
					$id = strtolower( str_replace( ' ', '_', $id ) ) . '_';
				
				if( $_POST['field_type'] == 'checkbox' )
					$value = $_POST['field_value'];
				elseif( $_POST['field_type'] == 'dropdown' )
					$value = $_POST['field_options'];
				
				$this->fields[$id] = array(
										'label' => sanitize_text_field( $_POST['field_label'] ),
										'id' => $id,
										'type' => $_POST['field_type'],
										'value' => ( !empty( $value ) ? $value : '' ),
										'checked' => ( !empty( $_POST['field_checked'] ) ? '1' : '0' ),
										'display' => ( !empty( $_POST['field_enabled'] ) ? '1' : '0' ),
										'required' => ( !empty( $_POST['field_required'] ) ? '1' : '0' ),
										'desc' => ( !empty( $_POST['field_desc'] ) ? 
											sanitize_text_field( $_POST['field_desc'] ) : '' ),
										'default' => '0',
										'position' => $_POST['field_position'],
										);
				
				if( update_option( MDJM_CLIENT_FIELDS, $this->fields ) )	{
					$mdjm_debug->log_it( 'Client field ' . sanitize_text_field( $_POST['field_label'] ) . 
						' created successfully by ' .  $current_user->display_name, true );
					mdjm_update_notice( 'updated', sanitize_text_field( $_POST['field_label'] ) . 
						' created successfully.' );
				}
				else	{
					$mdjm_debug->log_it( 'Error creating Client field ' . 
						sanitize_text_field( $_POST['field_label'] ), true );
						
					mdjm_update_notice( 'error', 'Field could not be created' );	
				}
				
			} // add_field
			
			/*
			 * Update existing field
			 *
			 *
			 *
			 */
			function update_field()	{
				global $mdjm_debug, $current_user;
				
				if( $_POST['field_type'] == 'checkbox' )
					$value = $_POST['field_value'];
				elseif( $_POST['field_type'] == 'dropdown' )
					$value = $_POST['field_options'];
				
				$this->fields[$_POST['field_id']] = array(
										'label' => sanitize_text_field( $_POST['field_label'] ),
										'id' => $_POST['field_id'],
										'type' => $_POST['field_type'],
										'value' => ( !empty( $value ) ? $value : '' ),
										'checked' => ( !empty( $_POST['field_checked'] ) ? '1' : '0' ),
										'display' => ( !empty( $_POST['field_enabled'] ) ? '1' : '0' ),
										'required' => ( !empty( $_POST['field_required'] ) ? '1' : '0' ),
										'desc' => ( !empty( $_POST['field_desc'] ) ? 
											sanitize_text_field( $_POST['field_desc'] ) : '' ),
										'default' => $this->fields[$_POST['field_id']]['default'],
										'position' => $this->fields[$_POST['field_id']]['position'],
										);
				
				if( update_option( MDJM_CLIENT_FIELDS, $this->fields ) )	{
					$mdjm_debug->log_it( 'Client field ' . sanitize_text_field( $_POST['field_label'] ) . 
						' updated successfully by ' .  $current_user->display_name, true );
					mdjm_update_notice( 'updated', sanitize_text_field( $_POST['field_label'] ) . 
						' updated successfully.' );
				}
				else	{
					$mdjm_debug->log_it( 'Error updating Client field ' . 
						sanitize_text_field( $_POST['field_label'] ), true );
						
					mdjm_update_notice( 'error', 'Field could not be updated' );	
				}
				
				
			} // update_field
			
			/*
			 * Enqueue scripts for drag and drop functionality
			 *
			 * @param		none
			 * @return		none
			 *
			 */
			public function drag_n_drop()	{
				wp_enqueue_script( 'jquery-ui-sortable' );
				wp_enqueue_script( 'update-order', WPMDJM_PLUGIN_URL . '/admin/includes/js/mdjm-order-list.js' );
			} // drag_n_drop
			
			/*
			 * Display the client fields admin interface
			 *
			 *
			 *
			 */
			function display_fields()	{
				global $mdjm_debug;
				
				/* -- Enable drag & drop -- */
				$this->drag_n_drop();
				
				$dir = WPMDJM_PLUGIN_URL . '/admin/images/contact-form-icons';
				
				// Start the container
				echo '<div class="mdjm-client-field-container">' . "\r\n";
				
				// Left Column
				echo '<div class="mdjm-client-field-column-left">' . "\r\n";
				
				// Start the display table
				echo '<h3>Existing Client Fields</h3>' . "\r\n";
				echo '<table class="widefat mdjm-client-list-item" style="width:90%">' . "\r\n";
				echo '<thead>' . "\r\n";
				echo '<tr>' . "\r\n";
				echo '<th style="width: 15%;">' . __( 'Label' ) . '</th>' . "\r\n";
				echo '<th style="width: 15%;">' . __( 'Type' ) . '</th>' . "\r\n";
				echo '<th style="width: 35%;">' . __( 'Description' ) . '</th>' . "\r\n";
				echo '<th style="width: 15%;">' . __( 'Options' ) . '</th>' . "\r\n";
				echo '<th>' . __( 'Actions' ) . '</th>' . "\r\n";
				echo '</tr>' . "\r\n";
				echo '</thead>' . "\r\n";
        		echo '<tbody>' . "\r\n";
        		
				$i = 0;
				
				foreach( $this->fields as $field )	{
					if( $i == 0 && $field['display'] == true )
						$class = 'alternate mdjm-client-list-item';
					elseif( empty( $field['display'] ) )
						$class = 'form-invalid mdjm-client-list-item';
					else
						$class = 'mdjm-client-list-item';
						
					echo '<tr id="fields=' . $field['id'] . '"' . 
						' class="' . $class . '">' . "\r\n";
						
					echo '<td>' . $field['label'] . '</td>' . "\r\n";
					echo '<td>' . ucfirst( str_replace( 'dropdown', 'select', $field['type'] ) ) . ' Field</td>' . "\r\n";
					echo '<td>' . ( !empty( $field['desc'] ) ? esc_attr( $field['desc'] ) : '' ) . '</td>' . "\r\n";
					echo '<td>' . $this->field_icons( $field ) . '</td>' . "\r\n";
					echo '<td>';
					echo '<a href="' . mdjm_get_admin_page( 'client_fields' ) . '&action=edit_field&id=' . $field['id'] . 
                        '" class="button button-primary button-small">' . __( 'Edit' ) . '</a>';
					if( empty( $field['default'] ) )
						echo '&nbsp;&nbsp;&nbsp;<a href="' . mdjm_get_admin_page( 'client_fields' ) . '&action=delete_field&id=' . $field['id'] . 
                        '" class="button button-secondary button-small">' . __( 'Delete' ) . '</a>';
					echo'</td>' . "\r\n";
					echo '</tr>' . "\r\n";
					
					if( $i == 1 )
						$i = 0;
					else
						$i++;
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
			 *
			 *
			 *
			 */
			function manage_field_form()	{
				
				$must = array( 'first_name', 'last_name', 'user_email' );
				
				$total = count( $this->fields );
				
				// Right Column
				echo '<div class="mdjm-client-field-column-right">' . "\r\n";
				echo '<form name="mdjm-client-fields" id="mdjm-client-fields" method="post" action="' . mdjm_get_admin_page( 'client_fields' ) . '">' . "\r\n";
				
				if( isset( $_GET['action'] ) && $_GET['action'] == 'edit_field' )
					$editing = true;
				
				// If editing a field we need this hidden field to identify it
				if( !empty( $editing ) )
					echo '<input type="hidden" name="field_id" id="field_id" value="' . $this->fields[$_GET['id']]['id'] . '" />' . "\r\n";
				
				else
					echo '<input type="hidden" name="field_position" id="field_position" value="' . $total . '" />' . "\r\n";
				
        		echo '<h3>' . ( empty( $editing ) ? __( 'Add New Client Field' ) : 
					__( 'Edit the ' ) . '<span class="mdjm-color">' . $this->fields[$_GET['id']]['label'] . '</span>' . __( ' Field' ) ) . '</h3>' . "\r\n";
				
				// Field Label
				echo '<p>';
				echo '<label class="mdjm-label" for="field_label">' . __( 'Field Label' ) . ':</label><br />' . "\r\n";
				echo '<input type="text" name="field_label" id="field_label" class="regular-text" value="' . ( !empty( $editing ) ? $this->fields[$_GET['id']]['label'] : '' ) . 
					'" class="regular-text" />';
				echo '</p>' . "\r\n";
				
				// Field Type
				$types = array( 'text', 'checkbox', 'dropdown' );
				echo '<p>' . "\r\n";
                echo '<label class="mdjm-label" for="field_type">' . __( 'Field Type' ) . ':</label><br />' . "\r\n";
				echo '<select name="field_type" id="field_type"' . ( !empty( $editing ) && $this->fields[$_GET['id']]['default'] == true ? 
					' disabled="disabled"' : '' ) . ' onChange="whichField(); showRequired();">' . "\r\n";
				
				foreach( $types as $type )	{
					echo '<option value="' . $type . '"';
					if( !empty( $editing ) )
						selected( $type, $this->fields[$_GET['id']]['type'] );
					echo '>' . __( ucfirst( str_replace( 'dropdown', 'select', $type ) ) . ' Field' ) . '</option>' . "\r\n";
				}
				
				echo '</select>' . "\r\n";
				echo '</p>' . "\r\n";
				if( !empty( $editing ) ) // If the select field is disabled we need to set the value
					echo '<input type="hidden" name="field_type" id="field_type" value="' . $this->fields[$_GET['id']]['type'] . '" />' . "\r\n";
				
				// Value
				?>
				<style>
				#value_field_dropdown	{
					display: <?php echo ( !empty( $editing ) && $this->fields[$_GET['id']]['type'] == 'dropdown' ? 'block;' : 'none;' ); ?>
				}
				#value_field_checkbox	{
					display: <?php echo ( !empty( $editing ) && $this->fields[$_GET['id']]['type'] == 'checkbox' ? 'block;' : 'none;' ); ?>
				}
				#required_checkbox	{
					display: <?php echo ( empty( $editing ) || $this->fields[$_GET['id']]['type'] != 'checkbox' ? 'block;' : 'none;' ); ?>	
				}
				</style>
                
                <div id="value_field_dropdown">
                <?php
				echo '<p>' . "\r\n";
				echo '<label class="mdjm-label" for="field_options">' . __( 'Selectable Options' ) . ':</label> <br />' . "\r\n";
				echo '<textarea name="field_options" id="field_options" class="all-options" rows="5">' . 
					( !empty( $editing ) ? $this->fields[$_GET['id']]['value'] : '' ) . '</textarea><br /><span class="description">One entry per line</span>';
				echo '</p>' . "\r\n";
				?>
                </div>
                <div id="value_field_checkbox">
                <?php
				echo '<p>' . "\r\n";
				echo '<label class="mdjm-label" for="field_value">' . __( 'Checked Value' ) . ':</label><br />' . "\r\n";
				echo '<input type="text" name="field_value" id="field_value" value="' . ( !empty( $editing ) ? $this->fields[$_GET['id']]['value'] : '' ) . 
					'" class="small-text" />';
				echo '</p>' . "\r\n";
				
				echo '<p>' . "\r\n";
				echo '<label class="mdjm-label" for="field_checked">' . __( 'Checked by Default' ) . '?</label><br />' . "\r\n";
				echo '<input type="checkbox" name="field_checked" id="field_checked" value="1"' . 
					( !empty( $editing ) && $this->fields[$_GET['id']]['checked'] ? ' checked="checked"' : '' ) . '" />';
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
				echo '<label class="mdjm-label" for="field_desc">' . __( 'Description' ) . ':</label><br />' . "\r\n";
				echo '<input type="text" name="field_desc" id="field_desc" value="' . ( !empty( $editing ) ? $this->fields[$_GET['id']]['desc'] : '' ) . 
					'" class="regular-text" />';
				echo '</p>' . "\r\n";
				
				// Options
				echo '<p>' . "\r\n";
				echo '<input type="checkbox" name="field_enabled" id="field_enabled" value="1"' . 
					( !empty( $editing ) && !empty( $this->fields[$_GET['id']]['display'] ) ? ' checked="checked"' : '' ) . 
					( !empty( $editing ) && in_array( $this->fields[$_GET['id']]['id'], $must ) ? ' disabled="disabled"' : '' ) . ' />' . 
					'<label class="mdjm-label" for="field_enabled">' . __( ' Field Enabled?' ) . '</label>';
				if( !empty( $editing ) && in_array( $this->fields[$_GET['id']]['id'], $must ) )
					echo '<input type="hidden" name="field_enabled" id="field_enabled" value="1" />' . "\r\n";
				echo '</p>' . "\r\n";
				echo '<div id="required_checkbox">' . "\r\n";
				echo '<p>' . "\r\n";
				echo '<input type="checkbox" name="field_required" id="field_required" value="1"' . 
					( !empty( $editing ) && !empty( $this->fields[$_GET['id']]['required'] ) ? ' checked="checked"' : '' ) . 
					( !empty( $editing ) && in_array( $this->fields[$_GET['id']]['id'], $must ) ? ' disabled="disabled"' : '' ) . ' />' . 
					'<label class="mdjm-label" for="field_required">' . __( ' Required Field?' ) . '</label>';
				if( !empty( $editing ) && in_array( $this->fields[$_GET['id']]['id'], $must ) )
					echo '<input type="hidden" name="field_required" id="field_required" value="1" />' . "\r\n";
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
				submit_button( ( empty( $editing ) ? 'Add Field' : 'Save Changes' ), 
								'primary', 
								'submit',
								false );
				
				if( !empty( $editing ) )	{
					echo '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
					echo '<a href="' . mdjm_get_admin_page( 'client_fields' ) . '" class="button-secondary">' . 
						'Cancel Changes</a>';
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
			function field_icons( $field )	{
				$dir = WPMDJM_PLUGIN_URL . '/admin/images/contact-form-icons';
				
				$output = '';
				
				if( $field['required'] == true )
					$output .= '<img src="' . $dir . '/req_field.jpg" width="14" height="14" alt="Required Field" title="Required Field" />' . "\r\n";
					
				else
					$output .= '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
					
				if( $field['type'] == 'checkbox' && !empty( $field['checked'] ) )
					$output .= '<img src="' . $dir . '/captcha.jpg" width="14" height="14" alt="Checked checkbox field Field" title="Checked" />';
				
				else
					$output .= '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
					
				if( $field['type'] == 'dropdown' )
					$output .= '<img src="' . $dir . '/select_list.jpg" width="14" height="14" alt="Dropdown field options" title="' . str_replace( ',', "\r\n", $field['value'] ) . '" />' . "\r\n";
					
				if( $field['type'] == 'checkbox' )
					$output .= '<img src="' . $dir . '/select_list.jpg" width="14" height="14" alt="Checked Value" title="' . $field['value'] . '" />' . "\r\n";
					
				else
					$output .= '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
					
				return $output;
				
			} // field_icons
		} // class
		
	} // if( !class_exists( 'MDJM_ClientFields' ) )
	
/* -- Instantiate the class -- */
	new MDJM_ClientFields();