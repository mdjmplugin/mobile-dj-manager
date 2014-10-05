<?php

/**
 * * * * * * * * * * * * * * * MDJM * * * * * * * * * * * * * * *
 * custom-user.php
 * Functions for custom user fields
 *
 * @since 1.0
 *
 */

/**
 * f_mdjm_edit_profile_custom_fields
 * Display the custom user profile fields on the edit user page
 * 
 * Called from: add_action hook
 * @since 1.0
*/
	function f_mdjm_edit_profile_custom_fields($user)	{
		global $current_screen, $user_ID;
		$user_id = ( $current_screen->id == 'profile' ) ? $user_ID : $_REQUEST['user_id'];
		?>
		<h3>Mobile DJ Manager Custom User Fields</h3>
		<table class="form-table">
        <?php
		$custom_fields = get_option( WPMDJM_CLIENT_FIELDS );
		foreach( $custom_fields as $custom_field )	{
			$field_value = get_user_meta( $user_id, $custom_field['id'], true );
			if( $custom_field['display'] == 'Y' )	{
				?>
				<tr>
				<th><label for="<?php echo $custom_field['id']; ?>"><?php echo $custom_field['label']; ?></label></th>
				<td>
                <?php
                if( $custom_field['type'] == 'checkbox' )	{
                    echo '<input type="' . $custom_field['type'] . '" name="' . $custom_field['id'] . '" id="' . $custom_field['id'] . '" value="Y" ' . checked( $field_value, 'Y', false ) . '  />';
				}
				elseif( $custom_field['type'] == 'dropdown' )	{
                    echo '<select name="' . $custom_field['id'] . '" id="' . $custom_field['id'] . '">';
					$option_data = explode( ',', $custom_field['value'] );
					echo '<option value="empty"';
					if( !$field_value || $field_value == '' || $field_value == 'empty' ) echo ' selected';
					echo '></option>';
					foreach( $option_data as $option )	{
						echo '<option value="' . $option . '"';
						if( $option == $field_value ) echo ' selected';
						echo '>' . $option . '</option>';
					}
					
					echo '<select/>';
				}
				else	{
					?>
                    <input type="<?php echo $custom_field['type']; ?>" name="<?php echo $custom_field['id']; ?>" id="<?php echo $custom_field['id']; ?>" value="<?php echo esc_attr( get_the_author_meta( $custom_field['id'], $user->ID ) ); ?>" class="regular-text" />
                    <?php
				} 
					if( $custom_field['desc'] != '' )	{
						?>
						<br />
						<span class="description"><?php echo $custom_field['desc']; ?></span>
						<?php
					}
				?>
				</td>
				</tr>
				<?php
			}
		}
		?>
		</table>
		<?php
	} // f_mdjm_extra_profile_fields


/**
 * f_mdjm_show_custom_user_field_registration
 * Display the custom user fields on the user registration page
 * 
 * Called from: add_action hooks
 *
 * @since 1.0
*/
	function f_mdjm_show_custom_user_field_registration()	{
		?>
    	<h3>Mobile DJ Manager Custom User Fields</h3>
		<table class="form-table">
        <?php
		$custom_fields = get_option( WPMDJM_CLIENT_FIELDS );
		foreach( $custom_fields as $custom_field )	{
			if( $custom_field['display'] == 'Y' )	{
				?>
				<tr>
				<th><label for="<?php echo $custom_field['id']; ?>"><?php echo $custom_field['label']; ?></label></th>
				<td>
                <?php
                if( $custom_field['type'] == 'checkbox' )	{
                    echo '<input type="' . $custom_field['type'] . '" name="' . $custom_field['id'] . '" id="' . $custom_field['id'] . '" value="Y"' . $custom_field['checked'] . '  />';
				}
				elseif( $custom_field['type'] == 'dropdown' )	{
                    echo '<select name="' . $custom_field['id'] . '" id="' . $custom_field['id'] . '">';
					$option_data = explode( ',', $custom_field['value'] );
					echo '<option value="empty" selected></option>';
					foreach( $option_data as $option )	{
						echo '<option value="' . $option . '">' . $option . '</option>';
					}
					echo '<select/>';
				}
				else	{
					?>
                    <input type="<?php echo $custom_field['type']; ?>" name="<?php echo $custom_field['id']; ?>" id="<?php echo $custom_field['id']; ?>" value="<?php echo esc_attr( $field_value ); ?>" class="regular-text" />
                    <?php
				} 
					if( $custom_field['desc'] != '' )	{
						?>
						<br />
						<span class="description"><?php echo $custom_field['desc']; ?></span>
						<?php
					}
				?>
				</td>
				</tr>
				<?php
			}
		}
		?>
        </table>
		<?php
	} //f_mdjm_show_custom_user_field_registration
	
/**
 * f_mdjm_save_custom_user_fields
 * Saves the input for the custom user profile fields
 * 
 * Called from: add_action hooks
 *
 * @since 1.0
*/
	function f_mdjm_save_custom_user_fields( $user_id )	{
		$custom_fields = get_option( WPMDJM_CLIENT_FIELDS );
		$default_fields = get_user_by( 'id', $user_id );
		if ( current_user_can( 'edit_user', $user_id ) )	{
			foreach( $custom_fields as $custom_field )	{
				$field = $custom_field['id'];
				if( $custom_field['type'] == 'checkbox' && $_POST[$field] == '' ) $_POST[$field] = 'N';
				if( !empty( $_POST[$field] ) ) update_user_meta( $user_id, $field, $_POST[$field] );
				if( $_POST['action'] == 'createuser' )	{
					update_user_option( $user_id, 'show_admin_bar_front', false );
					if( !empty( $default_fields->first_name ) && !empty( $default_fields->last_name ) )	{
						update_user_option( $user_id, 'display_name', $default_fields->first_name . ' ' . $default_fields->last_name );
					}
					$client_action = 'created';	
				}
				else	{
					$client_action = 'updated';	
				}
			}
		require_once( WPMDJM_PLUGIN_DIR . '/includes/functions.php' );
		$j_args = array (
					'client' => $user_id,
					'event' => '',
					'author' => get_current_user_id(),
					'type' => 'Edit Client',
					'source' => 'Admin',
					'entry' => 'Client ' . $default_fields->display_name . '\'s profile has been ' . $client_action
				);
		if( WPDJM_JOURNAL == 'Y' ) f_mdjm_do_journal( $j_args );
		}
	} // f_mdjm_save_custom_user_fields
?>