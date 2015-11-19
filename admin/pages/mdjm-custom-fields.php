<?php
	defined( 'ABSPATH' ) or die( "Direct access to this page is disabled!!!" );
	
/**
 * Class: MDJM_Event_Fields
 * Contains methods relating to the custom event fields functionality
 *
 *
 */
if( !class_exists( 'MDJM_Event_Fields' ) ) :
	class MDJM_Event_Fields	{
		/**
		 * Class constructor
		 *
		 *
		 *
		 */
		function __construct()	{
			add_action( 'admin_init', array( &$this, 'custom_fields_controller' ) );
			
			add_action( 'mdjm_events_client_metabox_last', array( &$this, 'custom_client_event_fields' ), 10, 2 );
			add_action( 'mdjm_events_metabox_last', array( &$this, 'custom_event_details_fields' ) );
			add_action( 'mdjm_events_venue_metabox_last', array( &$this, 'custom_venue_event_fields' ) );
			
			add_filter( 'dcf_mapping_fields', array( &$this, 'dcf_mapping_fields' ), 10, 2 );
		}
					
		/**
		 * Determine if any actions need to be taken for custom fields
		 *
		 *
		 *
		 *
		 */
		function custom_fields_controller()	{
			if( isset( $_POST['submit_custom_field'] ) )	{
				if( $_POST['submit_custom_field'] == __( 'Add Field', 'mobile-dj-manager' ) )
					self::add_field();
					
				elseif( $_POST['submit_custom_field'] == __( 'Save Changes', 'mobile-dj-manager' ) )
					self::update_field();
			}
				
			if( isset( $_GET['delete_custom_field'], $_GET['id'] ) )
				self::delete_field();
			
			return;
		} // custom_fields_controller
			
		/**
		 * Insert a new custom field post to the relevant section
		 *
		 * @param
		 *
		 * @return
		 */
		function add_field()	{
			global $mdjm_posts;
			
			remove_action( 'save_post', array( $mdjm_posts, 'save_custom_post' ), 10, 2 );
			
			$existing = mdjm_get_custom_fields( $_POST['_mdjm_field_section'], 'menu_order', 'DESC', 1 );
			
			if( $existing->have_posts() )	{
				while ( $existing->have_posts() ) {
					$existing->the_post();
					
					$menu_order = $existing->post->menu_order;
				}
				wp_reset_postdata();
				
				if( !empty( $menu_order ) )
					$menu_order++;
					
				else
					$menu_order = 1;
			}
			else
				$menu_order = 1;
			
			$id = wp_insert_post( 
				array(
					'post_title'	=> wp_strip_all_tags( $_POST['field_label'] ),
					'post_content'  => !empty( $_POST['field_desc'] ) ? $_POST['field_desc'] : '',
					'post_status'   => 'publish',
					'post_author'   => get_current_user_id(),
					'post_type'	 => MDJM_CUSTOM_FIELD_POSTS,
					'menu_order'	=> $menu_order ),
				true );
			
			/**
			* Success
			* We can now add the meta data
			*/
			if( !empty( $id ) )	{
				if( MDJM_DEBUG == true )
					$GLOBALS['mdjm_debug']->log_it( 'Custom field added ' . $_POST['field_label'], true );
				
				foreach( $_POST as $key => $value )	{
					if( substr( $key, 0, 5 ) != '_mdjm' )
						continue;
						
					// Add the meta data to the post
					add_post_meta( $id, $key, $value );
				}
				
				wp_redirect( mdjm_get_admin_page( 'custom_event_fields' ) . '&message=1' );
				exit;
			}
			/**
			* Error
			* Lets log it
			*/
			else	{
				if( MDJM_DEBUG == true && is_wp_error( $id ) )
					$GLOBALS['mdjm_debug']->log_it( 'Unable to create custom field ' . $_POST['field_label'] . '. ' . get_error_message(), true );
					
				wp_redirect( mdjm_get_admin_page( 'custom_event_fields' ) . '&message=4' );
				exit;
			}
			
			add_action( 'save_post', array( $mdjm_posts, 'save_custom_post' ), 10, 2 );
		} // add_field
		
		/**
		 * Update a custom field post
		 *
		 * @param
		 *
		 * @return
		 */
		function update_field()	{
			global $mdjm_posts;
			
			remove_action( 'save_post', array( $mdjm_posts, 'save_custom_post' ), 10, 2 );
			
			// Retrieve the existing settings for the field so we can compare
			$existing = get_post( $_POST['custom_field_id'] );
			
			if( !$existing )	{
				if( MDJM_DEBUG == true )
					$GLOBALS['mdjm_debug']->log_it( 'Unable to update field with ID: ' . $_GET['id'] . '. May not exist', true );
					
				wp_redirect( mdjm_get_admin_page( 'custom_event_fields' ) . '&message=5' );
				exit;
			}
			
			$id = wp_update_post( 
				array(
					'ID'			=> $_POST['custom_field_id'],
					'post_title'	=> wp_strip_all_tags( $_POST['field_label'] ),
					'post_content'  => !empty( $_POST['field_desc'] ) ? $_POST['field_desc'] : '',
					'post_status'   => 'publish' ),
				true );
				
			/**
			* Success
			* We can now add the meta data
			*/
			if( !empty( $id ) )	{
				if( MDJM_DEBUG == true )
					$GLOBALS['mdjm_debug']->log_it( 'Custom field updated ' . $_POST['field_label'], true );
				
				foreach( $_POST as $key => $value )	{
					if( substr( $key, 0, 5 ) != '_mdjm' )
						continue;
						
					// Add the meta data to the post
					update_post_meta( $id, $key, $value );
				}
				
				wp_redirect( mdjm_get_admin_page( 'custom_event_fields' ) . '&message=2' );
				exit;
			}
			
			/**
			* Error
			* Lets log it
			*/
			else	{
				if( MDJM_DEBUG == true && is_wp_error( $id ) )
					$GLOBALS['mdjm_debug']->log_it( 'Unable to create custom field ' . $_POST['field_label'] . '. ' . get_error_message(), true );
					
				wp_redirect( mdjm_get_admin_page( 'custom_event_fields' ) . '&message=4' );
				exit;
			}
			
			add_action( 'save_post', array( $mdjm_posts, 'save_custom_post' ), 10, 2 );
		} // update_field
			
		/**
		 * Delete a custom field with force
		 *
		 * @param
		 *
		 * @return
		 */
		function delete_field()	{
			if( wp_delete_post( $_GET['id'], true ) )	{
				if( MDJM_DEBUG == true )
					$GLOBALS['mdjm_debug']->log_it( 'Custom event field deleted with ID: ' . $_GET['id'], true );
					
				wp_redirect( mdjm_get_admin_page( 'custom_event_fields' ) . '&message=3' );
				exit;
			}
			
			else	{
				if( MDJM_DEBUG == true )
					$GLOBALS['mdjm_debug']->log_it( 'Custom event field with ID: ' . $_GET['id'] . ' could not be deleted', true );
				
				wp_redirect( mdjm_get_admin_page( 'custom_event_fields' ) . '&message=6' );
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
		function custom_event_field_settings()	{
			if( isset( $_GET['message'] ) )	{
				$messages = array(
					'1'	=> array( 'updated', __( 'Field added successfully.', 'mobile-dj-manager' ) ),
					'2'	=> array( 'updated', __( 'Field updated successfully.', 'mobile-dj-manager' ) ),
					'3'	=> array( 'updated', __( 'Field deleted successfully.', 'mobile-dj-manager' ) ),
					'4'	=> array( 'error', __( 'Unable to add field.', 'mobile-dj-manager' ) ),
					'5'	=> array( 'error', __( 'Unable to update field.', 'mobile-dj-manager' ) ),
					'6'	=> array( 'error', __( 'Unable to delete field.', 'mobile-dj-manager' ) ) );
				
				mdjm_update_notice( $messages[$_GET['message']][0], $messages[$_GET['message']][1], true );
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
			
			foreach( $field_types as $field_type )	{
				?>
				<h3><?php printf( __( '%s Fields', 'mobile-dj-manager' ), ucfirst( $field_type ) ) ; ?></h3>
				<table class="widefat mdjm-custom-<?php echo $field_type; ?>-list-item" style="width:90%">
					<thead>
					<tr>
						<th style="width: 15%; font-weight: bold;"><?php _e( 'Label', 'mobile-dj-manager' ); ?></th>
						<th style="width: 15%; font-weight: bold;"><?php _e( 'Type', 'mobile-dj-manager' ); ?></th>
						<th style="width: 35%; font-weight: bold;"><?php _e( 'Description', 'mobile-dj-manager' ); ?></th>
                        <th style="width: 15%; font-weight: bold;"><?php _e( 'Options', 'mobile-dj-manager' ); ?></th>
						<th style="font-weight: bold;"><?php _e( 'Actions', 'mobile-dj-manager' ); ?></th>
					</tr>
					</thead>
					<tbody>
				<?php
				$fields = mdjm_get_custom_fields( $field_type );
				
				// Display the custom fields
				if( $fields->have_posts() )	{
					$i = 0;
					
					while( $fields->have_posts() ) {
						$fields->the_post();
						?>
						<tr id="<?php echo $field_type . 'fields_' . $fields->post->ID; ?>" class="
							<?php echo ( $i == 0 ? 'alternate mdjm-custom-' . $field_type . '-list-item' : 'mdjm-custom-' . $field_type . '-list-item' ); ?>">
                            
                            <td><?php the_title(); ?></td>
                            <td><?php echo ucfirst( get_post_meta( $fields->post->ID, '_mdjm_field_type', true ) ); ?></td>
                            <td><?php the_content(); ?></td>
                            <td><?php self::field_icons( $fields->post->ID ); ?></td>
                            <td>
                                <a href="<?php echo mdjm_get_admin_page( 'custom_event_fields' ) . '&edit_custom_field=1&id=' . $fields->post->ID; ?>"
                                class="button button-primary button-small"><?php _e( 'Edit', 'mobile-dj-manager' ); ?></a>
                                &nbsp;&nbsp;&nbsp;<a href="<?php echo mdjm_get_admin_page( 'custom_event_fields' ) . '&delete_custom_field=1&id=' . $fields->post->ID; ?>"
                        		class="button button-secondary button-small"><?php _e( 'Delete', 'mobile-dj-manager' ); ?></a>
                            </td>
                        </tr>
                        <?php
						$i++;
						
						if( $i == 2 )
							$i = 0;
					}
					wp_reset_postdata();
				}
				
				// No custom fields
				else	{
					?>
					<tr>
						<td colspan="5"><?php printf( __( 'No Custom %s Fields Defined!', 'mobile-dj-manager' ), ucfirst( $field_type ) ); ?></td>
					</tr>
					<?php
				}
				
				?>
					</tbody>
					<tfoot>
					<tr style="font-weight: bold;">
						<th style="width: 15%; font-weight: bold;"><?php _e( 'Label', 'mobile-dj-manager' ); ?></th>
						<th style="width: 15%; font-weight: bold;"><?php _e( 'Type', 'mobile-dj-manager' ); ?></th>
						<th style="width: 35%; font-weight: bold;"><?php _e( 'Description', 'mobile-dj-manager' ); ?></th>
                        <th style="width: 15%; font-weight: bold;"><?php _e( 'Options', 'mobile-dj-manager' ); ?></th>
						<th style="font-weight: bold;"><?php _e( 'Actions', 'mobile-dj-manager' ); ?></th>
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
		 * @params	arr		$field_types	Required: The types of custom fields that can be created
		 *
		 *
		 */
		function add_new_custom_field_table( $field_types )	{
			wp_enqueue_script( 'jquery' );
			
			wp_register_script( 'jquery-validation-plugin', 'https://ajax.aspnetcdn.com/ajax/jquery.validate/1.11.1/jquery.validate.min.js', false );
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
						field_label: " <?php _e( 'Enter a label!', 'mobile-dj-manager' ); ?>",
					}
					
				} ); // Validate
			} ); // function
			</script>
			<div class="mdjm-event-field-column-right">
			<form name="mdjm-custom-event-fields" id="mdjm-custom-event-fields" method="post" action="<?php echo mdjm_get_admin_page( 'custom_event_fields' ); ?>">
			
			<?php
			if( isset( $_GET['edit_custom_field'], $_GET['id'] ) )
				$editing = true;
			
			// If editing a field we need this hidden field to identify it
			if( !empty( $editing ) )
				echo '<input type="hidden" name="custom_field_id" id="custom_field_id" value="' . $_GET['id'] . '" />' . "\r\n";
				
			echo '<h3>';
			echo ( empty( $editing ) ? __( 'Add New Custom Field', 'mobile-dj-manager' ) : 
				sprintf(
					__( 'Edit the %s%s%s %s', 'mobile-dj-manager' ),
					'<span class="mdjm-color">',
					get_the_title( $_GET['id'] ),
					'</span>',
					__( 'Field', 'mobile-dj-manager' ) ) );
			echo '</h3>' . "\r\n";
			
			// Types of input fields that can be selected
			$types = array( 'text', 'checkbox', 'select', 'multi select', 'textarea' );
			
			?>
			
            <p>
			<label class="mdjm-label" for="_mdjm_field_section"><?php _e( 'Section', 'mobile-dj-manager' ); ?>:</label><br />
			<select name="_mdjm_field_section" id="_mdjm_field_section">
			<?php
			foreach( $field_types as $type )	{
				echo '<option value="' . $type . '"';
				if( !empty( $editing ) )
					selected( $type, get_post_meta( $_GET['id'], '_mdjm_field_section', true ) );
				echo '>' . sprintf( __( '%s Section', 'mobile-dj-manager' ), ucfirst( $type ) ) . '</option>' . "\r\n";
			}
			?>
			</select>
			</p>
            
			<p>
			<label class="mdjm-label" for="field_label"><?php _e( 'Label', 'mobile-dj-manager' ); ?>:</label><br />
			<input type="text" name="field_label" id="field_label" class="regular-text" value="<?php echo ( !empty( $editing ) ? 
			get_the_title( $_GET['id'] ) : '' ); ?>" class="regular-text" required="required" />
			</p>
			
			<p>
			<label class="mdjm-label" for="_mdjm_field_type"><?php _e( 'Type', 'mobile-dj-manager' ); ?>:</label><br />
			<select name="_mdjm_field_type" id="_mdjm_field_type" onChange="whichField();">
			<?php
			foreach( $types as $type )	{
				echo '<option value="' . $type . '"';
				if( !empty( $editing ) )
					selected( $type, get_post_meta( $_GET['id'], '_mdjm_field_type', true ) );
				echo '>' . sprintf( __( '%s Field', 'mobile-dj-manager' ), ucwords( $type ) ) . '</option>' . "\r\n";
			}
			?>
			</select>
			</p>
			
            <style type="text/css">
				#value_field_select	{
					display: <?php echo ( !empty( $editing ) && get_post_meta( $_GET['id'], '_mdjm_field_type', true ) == 'select' ? 'block;' : 'none;' ); ?>
				}
				#value_field_checkbox	{
					display: <?php echo ( !empty( $editing ) && get_post_meta( $_GET['id'], '_mdjm_field_type', true ) == 'checkbox' ? 'block;' : 'none;' ); ?>
				}
			</style>
            
            <div id="value_field_select">
				<p>
				<label class="mdjm-label" for="_mdjm_field_options"><?php _e( 'Selectable Options', 'mobile-dj-manager' ); ?>:</label><br />
				<textarea name="_mdjm_field_options" id="_mdjm_field_options" class="all-options" rows="5"><?php 
					echo ( !empty( $editing ) ? get_post_meta( $_GET['id'], '_mdjm_field_options', true ) : '' ); ?></textarea><br />
                <span class="description"><?php _e( 'One entry per line', 'mobile-dj-manager' ); ?></span>
				</p>
            </div>
            <div id="value_field_checkbox">
				<p>
				<label class="mdjm-label" for="_mdjm_field_value"><?php _e( 'Checked Value', 'mobile-dj-manager' ); ?>:</label><br />
				<input type="text" name="_mdjm_field_value" id="_mdjm_field_value" value="<?php echo ( !empty( $editing ) ? 
					get_post_meta( $_GET['id'], '_mdjm_field_value', true ) : '1' ); ?>" class="small-text" />
				</p>
				
				<p>
				<input type="checkbox" name="_mdjm_field_checked" id="_mdjm_field_checked" value="1" 
					<?php 
					if( !empty( $editing ) )
						checked( '1', get_post_meta( $_GET['id'], '_mdjm_field_checked', true ) );
					?> 
                    />&nbsp;<label class="mdjm-label" for="_mdjm_field_checked"><?php _e( 'Checked by Default', 'mobile-dj-manager' ); ?>?</label>
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
			<label class="mdjm-label" for="field_desc"><?php _e( 'Description', 'mobile-dj-manager' ); ?>:</label><br />
			<input type="text" name="field_desc" id="field_desc" value="<?php echo ( !empty( $editing ) ? 
			get_the_content( $_GET['id'] ) : '' ); ?>" class="regular-text" /><br />
			<span class="description"><?php _e( "Not visible to client's", 'mobile-dj-manager' ); ?></span>
			</p>
			
			<p>
			<?php
			submit_button( ( empty( $editing ) ? __( 'Add Field', 'mobile-dj-manager' ) : __( 'Save Changes', 'mobile-dj-manager' ) ), 
							'primary', 
							'submit_custom_field',
							false );
			
			if( !empty( $editing ) )	{
				echo '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
				echo '<a href="' . mdjm_get_admin_page( 'custom_event_fields' ) . '" class="button-secondary">' . 
					__( 'Cancel Changes', 'mobile-dj-manager' ) . '</a>';
			}
			?>                
			</p>
			
			</form>
			</div>
			<?php
		} // add_new_custom_field_table
		
		/**
		 * Display icons to identify the field configuration
		 *
		 * @param	int		$field_id	The field which to query
		 *
		 * @return	str					Echo the HTML required to display the necessary icons
		 */
		function field_icons( $field_id )	{
			$dir = MDJM_PLUGIN_URL . '/admin/images/form-icons';
			
			$output = '';
			
			$type = get_post_meta( $field_id, '_mdjm_field_type', true );
			$selected = get_post_meta( $field_id, '_mdjm_field_checked', true );
			$value = $type == 'checkbox' ? get_post_meta( $field_id, '_mdjm_field_value', true ) : get_post_meta( $field_id, '_mdjm_field_options', true );
							
			if( !empty( $type ) && $type == 'checkbox' && $selected == '1' )
				$output .= '<img src="' . $dir . '/captcha.jpg" width="14" height="14" alt="' . __( 'Checked Checkbox Field', 'mobile-dj-manager' ) . '" title="' . __( 'Checked', 'mobile-dj-manager' ) . '" />';
			
			if( $type == 'checkbox' )
				$output .= '<img src="' . $dir . '/select_list.jpg" width="14" height="14" alt="' . __( 'Checked Value', 'mobile-dj-manager' ) . '" title="' . __( 'Checked Value', 'mobile-dj-manager' ) . ' = ' . $value . '" />' . "\r\n";
				
			if( $type == 'select' || $type == 'multi select' )
				$output .= '<img src="' . $dir . '/select_list.jpg" width="14" height="14" alt="' . __( 'Dropdown field options', 'mobile-dj-manager' ) . '" title="' . str_replace( ',', "\r\n", $value ) . '" />' . "\r\n";
								
			echo $output;
		} // field_icons
		
		/**
		 * Add the custom fields to the end of the event client details metabox.
		 * @called: mdjm_events_client_metabox_last hook
		 *
		 * @params	obj		$post		Required: The current event post object
		 *			int		$client_id	Required: The ID of the current client
		 *
		 * @return	str		$output		This function must output the full required HTML
		 */
		function custom_client_event_fields( $post, $client_id )	{
			$query = mdjm_get_custom_fields( 'client' );
			$fields = $query->get_posts();
			
			/**
			* We have fields so let's display them
			*/
			if( $fields )	{
				foreach( $fields as $field )	{
					self::display_input( $field, $post );
				}
			}
			
			/**
			* No fields so do nothing and return
			*/
			else
				return;
		} // custom_client_event_fields
		
		/**
		 * Add the custom fields to the end of the event Event Details metabox.
		 * @called: mdjm_events_metabox_last hook
		 *
		 * @params	obj		$post		Required: The current event post object
		 *
		 *
		 * @return	str		$output		This function must output the full required HTML
		 */
		function custom_event_details_fields( $post )	{
			$query = mdjm_get_custom_fields( 'event' );
			$fields = $query->get_posts();
			
			/**
			* We have fields so let's display them
			*/
			if( $fields )	{
				foreach( $fields as $field )	{
					self::display_input( $field, $post );
				}
			}
			
			/**
			* No fields so do nothing and return
			*/
			else
				return;
		} // custom_event_details_fields
		
		/**
		 * Add the custom fields to the end of the event Venue Details metabox.
		 * @called: mdjm_events_venue_metabox_last hook
		 *
		 * @params	obj		$post		Required: The current event post object
		 *
		 *
		 * @return	str		$output		This function must output the full required HTML
		 */
		function custom_venue_event_fields( $post )	{
			$query = mdjm_get_custom_fields( 'venue' );
			$fields = $query->get_posts();
			
			/**
			* We have fields so let's display them
			*/
			if( $fields )	{
				foreach( $fields as $field )	{
					self::display_input( $field, $post );
				}
			}
			
			/**
			* No fields so do nothing and return
			*/
			else
				return;
		} // custom_venue_event_fields
		
		/**
		 * Output the input field for the current field
		 *
		 * @param	obj		$field		Required: The post object for the custom field we are displaying
		 *			obj		$post		Required: The post object for the current event
		 *
		 */
		function display_input( $field, $post )	{
			$name = '_mdjm_event_' . str_replace( '-', '_', $field->post_name );
			$type = get_post_meta( $field->ID, '_mdjm_field_type', true );
			$selected = get_post_meta( $field->ID, '_mdjm_field_checked', true );
			
			$value = $type == 'checkbox' ? 
				get_post_meta( $field->ID, '_mdjm_field_value', true ) : 
				get_post_meta( $field->ID, '_mdjm_field_options', true );
			
			$height = array( 'textarea', 'multi select' );
			
			?>
			<div class="mdjm-post-row-single"<?php if( in_array( $type, $height ) ) echo ' style="height: auto !important;"'; ?>>
				<div class="mdjm-post-1column">
					<?php
					// Checkbox fields appear before and on the same line as the label
					if( $type == 'checkbox' )	{
						echo '<input type="checkbox"';
						echo ' name="' . $name . '" id="' . $name . '"';
						echo ' value="' . $value . '"';
						if( !empty( $selected ) )
							echo ' checked="checked"';
							
						echo ' />' . "\r\n";
						
					}
					?>
					<label for="<?php echo $name; ?>" class="mdjm-label"><?php echo get_the_title( $field->ID ); ?>:</label>
					
					<?php
					if( $type != 'checkbox' )
						echo '<br />' . "\r\n";
					
					if( $type == 'text' )	{
						echo '<input type="text"';
						echo ' name="' . $name . '" id="' . $name . '"';
						echo ' value="' . get_post_meta( $post->ID, $name, true ) . '" />' . "\r\n";
					}
						
					elseif( $type == 'select' || $type == 'multi select' )	{
						$values = explode( "\r\n", $value );
						echo '<select name="' . $name . '" id="' . $name . '"';
						
						if( $type == 'multi select' )
							echo ' multiple="multiple"';
						
						echo '>' . "\r\n";
						
						foreach( $values as $option )	{
							echo '<option value="' . $option . '"';
							selected( $option, get_post_meta( $post->ID, $name, true ) );
							echo '>' . $option . '</option>' . "\r\n";
						}
						
						echo '</select>' . "\r\n";
					}
					
					elseif( $type == 'textarea' )	{
						echo '<textarea name="' . $name . '" id="' . $name . '"';
						echo ' class="widefat" rows="3">';
						echo get_post_meta( $post->ID, $name, true );
						echo '</textarea>' . "\r\n";
					}
					?>
				</div>
			</div>
			<?php
		} // display_input
				
		/**
		 * Append the custom event fields to the Contact Form mapping options
		 * @called: dcf_mapping_fields hook
		 *
		 * @params	arr		$mappings			Required: The existing mapping options
		 *			str		$type				Required: client | event | venue
		 *
		 *
		 * @return	arr		$mappings_event		The filtered mapping options
		 */
		function dcf_mapping_fields( $mappings, $type )	{
			$query = mdjm_get_custom_fields( $type );
			$fields = $query->get_posts();
			
			if( $fields )	{
				foreach( $fields as $field )	{
					$name = '_mdjm_event_' . str_replace( '-', '_', $field->post_name );
					
					$mappings[$name] = sprintf( 
											__( '%s', 'mobile-dj-manager' ), 
											ucfirst( $type ) ) . ' ' . get_the_title( $field->ID );
				}
			}
			
			return $mappings;
		} // dcf_mapping_fields
		
	} // class MDJM_Event_Fields
endif;
	new MDJM_Event_Fields();