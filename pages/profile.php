<?php
	defined('ABSPATH') or die("Direct access to this page is disabled!!!");
	
	global $clientzone, $mdjm, $my_mdjm, $mdjm_settings;
	
	wp_enqueue_script( 'mdjm-validation' );
	
	if ( is_user_logged_in() )	{ // Display the profile
		// First check if the user is submitting the form
		if( isset( $_POST['submit'] ) )	{	
			f_mdjm_update_user_profile();	
		}
		$custom_fields = get_option( MDJM_CLIENT_FIELDS );
		?>
        <p>Please keep your details up to date as incorrect information may cause problems with your event.</p>
        <form action="" method="post" enctype="multipart/form-data" name="user-profile" id="user-profile">
        <table width="100%" border="0" cellspacing="0" cellpadding="0" style="font-size:11px">
          <tr>
            <td width="15%" style="font-weight:bold"><label for="first_name">First Name:</label></td>
            <td width="35%"><input name="first_name" id="first_name" type="text" size="30" class="required" value="<?php echo $my_mdjm['me']->first_name; ?>" /></td>
            <td width="15%" style="font-weight:bold"><label for="last_name">Last Name:</label></td>
            <td width="35%"><input name="last_name" id="last_name" type="text" size="30" class="required" value="<?php echo $my_mdjm['me']->last_name; ?>" /></td>
          </tr>
          <tr>
            <td width="15%" style="font-weight:bold"><label for="phone1"><?php echo $custom_fields['phone1']['label']; ?>:</label></td>
            <td width="35%"><input name="phone1" id="phone1" type="text" size="30"<?php if( $custom_fields['phone1']['required'] == 'Y' ) echo ' class="required" '; ?> value="<?php echo $my_mdjm['me']->phone1; ?>" /></td>
            <td width="15%" style="font-weight:bold"><label for="phone2"><?php echo $custom_fields['phone2']['label']; ?>:</label></td>
            <td width="35%"><input name="phone2" id="phone2" type="text" size="30" value="<?php echo $my_mdjm['me']->phone2; ?>" /></td>
          </tr>
          <tr>
            <td width="15%" style="font-weight:bold"><label for="user_email">Email Address:</label></td>
            <td><input name="user_email" id="user_email" type="text" size="30" class="required" value="<?php echo $my_mdjm['me']->user_email; ?>" /></td>
            <td width="15%" style="font-weight:bold"><label for="birthday"><?php echo $custom_fields['birthday']['label']; ?>:</label></td>
            <td><select name="birthday" id="birthday">
            	<?php
				$option_data = explode( ',', $custom_fields['birthday']['value'] );
				?>
                <option value="empty" selected></option>
                <?php
				foreach( $option_data as $option )	{
					?>
                    	<option value="<?php echo $option; ?>"<?php selected( $my_mdjm['me']->birthday, $option ); ?>><?php echo $option; ?></option>
                    <?php
				}
				?>
            	</select>
            </td>
          </tr>
          <tr>
            <td width="15%" style="font-weight:bold"><label for="address1"><?php echo $custom_fields['address1']['label']; ?>:</label></td>
            <td><input name="address1" id="address1" type="text" size="30" class="required" value="<?php echo $my_mdjm['me']->address1; ?>" /></td>
            <td colspan="2" valign="bottom"><strong>Personal Preferences</strong><font size="-3"> (optional)</font></td>
          </tr>
            <tr>
            <td width="15%" style="font-weight:bold"><label for="address2"><?php echo $custom_fields['address2']['label']; ?>:</label></td>
            <td><input name="address2" id="address2" type="text" size="30" value="<?php echo $my_mdjm['me']->address2; ?>" /></td>
            <td width="15%" align="right"><label for="marketing"><?php echo $custom_fields['marketing']['label']; ?></label></td>
            <td><input name="marketing" id="marketing" type="checkbox" value="Y" <?php if( isset( $my_mdjm['me']->marketing ) && $my_mdjm['me']->marketing == "Y" ) echo "checked" ?> /> <font style="font-size:9px">Your details will never be shared</style></td>
          </tr>
          <tr>
            <td width="15%" style="font-weight:bold"><label for="town"><?php echo $custom_fields['town']['label']; ?>:</label></td>
            <td><input name="town" id="town" type="text" size="30" class="required" value="<?php echo $my_mdjm['me']->town; ?>" /></td>
            <td>&nbsp;</td>
            <td>&nbsp;</td>
          </tr>
          <tr>
            <td width="15%" style="font-weight:bold"><label for="county"><?php echo $custom_fields['county']['label']; ?>:</label></td>
            <td><input name="county" id="county" type="text" size="30" class="required" value="<?php echo $my_mdjm['me']->county; ?>" /></td>
            <td>&nbsp;</td>
            <td>&nbsp;</td>
          </tr>
          <tr>
            <td width="15%" style="font-weight:bold"><label for="postcode"><?php echo $custom_fields['postcode']['label']; ?>:</label></td>
            <td><input name="postcode" id="postcode" type="text" size="30" class="required" value="<?php echo $my_mdjm['me']->postcode; ?>" /></td>
            <td>&nbsp;</td>
            <td>&nbsp;</td>
          </tr>
          </table>
          <table width="100%" border="0" cellspacing="0" cellpadding="0" style="font-size:11px">
          <?php 
		  foreach ( $custom_fields as $field )	{
			if( $field['default'] == false && $field['display'] == 'Y' )	{
				?>
				<tr>
				<td width="15%" style="font-weight:bold"><label for="<?php echo $field['label']; ?>"><?php echo $field['label']; ?>:</label></td>
				
				<?php 
				if( $field['type'] == 'checkbox' )	{ 
				?>
					<td width="85%"><input name="<?php echo $field['id']; ?>" id="<?php echo $field['id']; ?>" type="checkbox" size="30" value="<?php echo $field['value']; ?>"<?php checked( $field['value'], $my_mdjm['me']->$field['id'] ); ?> /></td>
				<?php }
				elseif( $field['type'] == 'dropdown' )	{
					$value_data = explode( ',', $field['value'] );
					?>
					<td width="85%"><select name="<?php echo $field['id']; ?>" id="<?php echo $field['id']; ?>">
                    <?php
					foreach( $value_data as $value )	{
						?>
						<option value="<?php echo $value; ?>"<?php selected( $value, $my_mdjm['me']->$field['id'] ); ?>><?php echo $value; ?></option>
                        <?php
					}
                    ?>
                    </select></td>
                    <?php
					
				}
				else	{
					?>
					<td width="85%"><input name="<?php echo $field['id']; ?>" id="<?php echo $field['id']; ?>" type="text" size="30" value="<?php echo $my_mdjm['me']->$field['id']; ?>" /></td>
                    <?php
				}
				?>
				</tr>
                <?php
			  }
		  }
			?>
          </table>
          <hr />
          <p style="font-size:11px">To update your password, enter a new password below and confirm your new password. Leaving these fields blank will keep your current password.</p>
          <table width="100%" border="0" cellpadding="0" cellspacing="0" style="font-size:11px">
          <tr>
          <td width="15%" style="font-weight:bold"><label for="new_password">New Password:</label></td>
            <td width="35%"><input name="new_password" id="new_password" type="password" size="30" value="" /></td>
            <td width="15%" style="font-weight:bold"><label for="new_password_confirm">Confirm Password:</label></td>
            <td width="35%"><input name="new_password_confirm" id="new_password_confirm" type="password" size="30" value="" /></td>
          </table>
          <br />
          <table width="100%" border="0" cellspacing="0" cellpadding="0" style="font-size:11px">
          <tr>
            <td width="50%" align="center"><input name="submit" type="submit" value="Update Profile" /></td>
            <td align="center"><input name="reset" type="reset" value="Reset Values" /></td>
          </tr>
        </table>
        </form>
        <?php
	} // End if ( is_user_logged_in() )
	else	{ // User not logged in
		$clientzone->login();
	}
?>