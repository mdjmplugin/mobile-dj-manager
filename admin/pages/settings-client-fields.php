<?php
	defined( 'ABSPATH' ) or die( "Direct access to this page is disabled!!!" );
	if ( !current_user_can( 'manage_options' ) && !current_user_can( 'manage_mdjm' ) )  {
		wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
	}
	
	/* Check for plugin update */
	f_mdjm_has_updated();

	if( isset( $_GET['action'] ) && $_GET['action'] == 'del_field' )	{
		$client_fields = get_option( WPMDJM_CLIENT_FIELDS );
		unset( $client_fields[$_GET['field']] );
		if( update_option( WPMDJM_CLIENT_FIELDS, $client_fields ) )	{
			?>
			<div id="message" class="updated">
			<p><strong><?php _e('Settings saved.') ?></strong></p>
			</div>
			<?php
		}
	}
	
	if( isset( $_POST['client_fields'] ) && $_POST['client_fields'] == 'update' )	{
		/* Add the new field */
		if( $_POST['field_checked'] == 'Y' ) $checked = ' checked'; else $checked = false;
		if( $_POST['field_enabled'] == 'Y' ) $display = 'Y'; else $display = '';
		$client_fields = get_option( WPMDJM_CLIENT_FIELDS );
		
		$client_fields[$_POST['field_id']]['label'] = sanitize_text_field( $_POST['field_label'] );
		$client_fields[$_POST['field_id']]['id'] = sanitize_text_field( $_POST['field_id'] );
		$client_fields[$_POST['field_id']]['type'] = $_POST['field_type'];
		$client_fields[$_POST['field_id']]['value'] = $_POST['field_value'];
		$client_fields[$_POST['field_id']]['checked'] = $checked;
		$client_fields[$_POST['field_id']]['display'] = $display;
		$client_fields[$_POST['field_id']]['desc'] = sanitize_text_field( $_POST['field_desc'] );
		$client_fields[$_POST['field_id']]['default'] = false;

		if( update_option( WPMDJM_CLIENT_FIELDS, $client_fields ) )	{
			?>
			<div id="message" class="updated">
			<p><strong><?php _e('Settings saved.') ?></strong></p>
			</div>
            <?php
		}
	}

	function f_mdjm_client_fields()	{
		$client_fields = get_option( WPMDJM_CLIENT_FIELDS );
		?>
        <h3>Existing Client Fields</h3>
        <table class="widefat">
        <thead>
		<tr>
			<th class="row-title">Label</th>
			<th>ID</th>
            <th>Type</th>
            <th>Description</th>
            <th>Value</th>
            <th>Checked</th>
            <th>Enabled</th>
            <th>&nbsp;</th>
		</tr>
        </thead>
        <tbody>
        <tr>
        	<td>First Name</td>
            <td>firstname</td>
            <td>text</td>
            <td>&nbsp;</td>
            <td>&nbsp;</td>
            <td><input type="checkbox" disabled></td>
            <td>Y</td>
            <td>&nbsp;</td>
        </tr>
        <tr class="alternate">
        	<td>Last Name</td>
            <td>lastname</td>
            <td>text</td>
            <td>&nbsp;</td>
            <td>&nbsp;</td>
            <td><input type="checkbox" disabled></td>
            <td>Y</td>
            <td>&nbsp;</td>
        </tr>
        <tr>
        	<td>E-mail</td>
            <td>email</td>
            <td>text</td>
            <td>&nbsp;</td>
            <td>&nbsp;</td>
            <td><input type="checkbox" disabled></td>
            <td>Y</td>
            <td>&nbsp;</td>
        </tr>
        <form name="form-client-fields" id="form-client-fields" method="post" action="<?php echo admin_url() . 'admin.php?page=mdjm-settings&tab=client_fields'; ?>">
        <input type="hidden" name="client_fields" value="update">
        <?php
		$rowclass = ' class="alternate"';
		foreach( $client_fields as $field )	{
            echo '<tr' . $rowclass . '>';
			
			echo '<td><input type="text" name="mdjm_client_fields[' . $field['id'] . '][label]" value="' . $field['label'] . '" disabled="disabled" /></td>';
			
			echo '<td><input type="text" name="mdjm_client_fields[' . $field['id'] . '][id]" value="' . $field['id'] . '" disabled="disabled" /></td>';
			
			echo '<td><input type="text" name="mdjm_client_fields[' . $field['id'] . '][type]" value="' . $field['type'] . '" disabled="disabled" /></td>';
			
			echo '<td><input type="text" name="mdjm_client_fields[' . $field['id'] . '][desc]" value="' . $field['desc'] . '" disabled="disabled" /></td>';

			echo '<td><input type="text" name="mdjm_client_fields[' . $field['id'] . '][value]" value="' . $field['value'] . '" disabled="disabled" /></td>';

			echo '<td><input type="checkbox" disabled' . $field['checked'] . '></td>';
			
			echo '<td>' . $field['display'] . '</td>';
			
			echo '<td>';
			if( $field['default'] != 1 ) echo '<a href="' . admin_url() . 'admin.php?page=mdjm-settings&tab=client_fields&action=edit_field&field=' . $field['id'] . '" class="add-new-h2">Edit</a> <a href="' . admin_url() . 'admin.php?page=mdjm-settings&tab=client_fields&action=del_field&field=' . $field['id'] . '" class="add-new-h2">Delete</a>'; else echo '&nbsp;';
			echo '</td>';
			
            echo '</tr>';
			
			if( $rowclass == ' class="alternate"' ) $rowclass = ''; else $rowclass = ' class="alternate"';
		}
		?>
        </tbody>
        </table>
        <hr />
        <h3>Add New Client Field</h3>
        <table class="form-table">
        <?php
		if( isset( $_GET['action'] ) && $_GET['action'] == 'edit_field' )	{
			?>
            <input type="hidden" name="field_id" id="field_id" value="<?php echo $client_fields[$_GET['field']]['id']; ?>" />
            <tr>
                <th><label for="field_label">Field Label</label></th>
                <td><input type="text" name="field_label" id="field_label" value="<?php echo $client_fields[$_GET['field']]['label']; ?>" class="regular-text" /></td>
            </tr>
            <tr>
                <th><label for="field_id">Field ID</label></th>
                <td><input type="text" name="field_id" id="field_id" value="<?php echo $client_fields[$_GET['field']]['id']; ?>" class="regular-text"  readonly /></td>
            </tr>
            <tr>
                <th><label for="field_type">Field Type</label></th>
                <td><select name="field_type" id="field_type">
                    <option value="text"<?php selected( 'text', $client_fields[$_GET['field']]['type'] ); ?>>Text</option>
                    <option value="checkbox"<?php selected( 'checkbox', $client_fields[$_GET['field']]['type'] ); ?>>Checkbox</option>
                    <option value="dropdown"<?php selected( 'dropdown', $client_fields[$_GET['field']]['type'] ); ?>>Dropdown</option>
                </td>
            </tr>
            <tr>
                <th><label for="field_desc">Field Description</label></th>
                <td><input type="text" name="field_desc" id="field_desc" value="<?php echo $client_fields[$_GET['field']]['desc']; ?>" class="regular-text" /></td>
            </tr>
            <tr>
                <th><label for="field_value">Value</label></th>
                <td><input type="text" name="field_value" id="field_value" class="regular-text" value="<?php echo $client_fields[$_GET['field']]['value']; ?>" /><span class="description">Enter 'Y' for a checkbox. For a dropdown box, type all entries seperating each with a comma</span></td>
            </tr>
            <tr>
                <th><label for="field_checked">Checked?</label></th>
                <td><input type="checkbox" name="field_checked" id="field_checked" value="<?php echo $client_fields[$_GET['field']]['value']; ?>"<?php checked( ' checked', $client_fields[$_GET['field']]['checked'] ); ?> /></td>
            </tr>
            <tr>
                <th><label for="field_enabled">Enabled</label></th>
                <td><input type="checkbox" name="field_enabled" id="field_enabled" value="Y"<?php checked( 'Y', $client_fields[$_GET['field']]['display'] ); ?> /></td>
            </tr>
            <?php
		}
		else	{
			?>
			<tr>
                <th><label for="field_label">Field Label</label></th>
                <td><input type="text" name="field_label" id="field_label" class="regular-text" /></td>
            </tr>
            <tr>
                <th><label for="field_id">Field ID</label></th>
                <td><input type="text" name="field_id" id="field_id" class="regular-text" /></td>
            </tr>
            <tr>
                <th><label for="field_type">Field Type</label></th>
                <td><select name="field_type" id="field_type">
                    <option value="text">Text</option>
                    <option value="checkbox">Checkbox</option>
                    <option value="dropdown">Dropdown</option>
                </td>
            </tr>
            <tr>
                <th><label for="field_desc">Field Description</label></th>
                <td><input type="text" name="field_desc" id="field_desc" class="regular-text" /></td>
            </tr>
            <tr>
                <th><label for="field_value">Value</label></th>
                <td><input type="text" name="field_value" id="field_value" class="regular-text" /><span class="description">Enter 'Y' for a checkbox. For a dropdown box, type all entries seperating each with a comma</span></td>
            </tr>
            <tr>
                <th><label for="field_checked">Checked?</label></th>
                <td><input type="checkbox" name="field_checked" id="field_checked" value="Y" /></td>
            </tr>
            <tr>
                <th><label for="field_enabled">Enabled</label></th>
                <td><input type="checkbox" name="field_enabled" id="field_enabled" value="Y" checked /></td>
            </tr>
            <?php
		}
		?>
        </table>
        <?php
	} // f_mdjm_client_fields
	
	f_mdjm_client_fields();
?>