<?php
	defined('ABSPATH') or die("Direct access to this page is disabled!!!");

	global $current_user;
	
	require_once WPMDJM_PLUGIN_DIR . '/includes/config.inc.php';
	require_once WPMDJM_PLUGIN_DIR . '/includes/functions.php';

	if ( is_user_logged_in() )	{ // Display the profile
		// First check if the user is submitting the form
		if( isset( $_POST['submit'] ) )	{	f_mdjm_update_user_profile();	}
		get_currentuserinfo();
		?>
        <p>Please keep your details up to date as incorrect information may cause problems with your event.</p>
        <form action="<?php get_permalink(); ?>" method="post" enctype="multipart/form-data" name="user-profile">
        <table width="100%" border="0" cellspacing="0" cellpadding="0" style="font-size:11px">
          <tr>
            <td width="15%" style="font-weight:bold">First Name:</td>
            <td width="35%"><input name="first_name" type="text" size="30" value="<?php echo $current_user->first_name; ?>" /></td>
            <td width="15%" style="font-weight:bold">Last Name:</td>
            <td width="35%"><input name="last_name" type="text" size="30" value="<?php echo $current_user->last_name; ?>" /></td>
          </tr>
          <tr>
            <td width="15%" style="font-weight:bold">Phone Number:</td>
            <td width="35%"><input name="phone1" type="text" size="30" value="<?php echo $current_user->phone1; ?>" /></td>
            <td width="15%" style="font-weight:bold">Alternative Phone:</td>
            <td width="35%"><input name="phone2" type="text" size="30" value="<?php echo $current_user->phone2; ?>" /></td>
          </tr>
          <tr>
            <td width="15%" style="font-weight:bold">Email Address:</td>
            <td><input name="user_email" type="text" size="30" value="<?php echo $current_user->user_email; ?>" /></td>
            <td width="15%" style="font-weight:bold">Birthday:</td>
            <td><select name="birthday" id="birthday">
            	<?php
				$custom_fields = get_option( WPMDJM_CLIENT_FIELDS );
				$option_data = explode( ',', $custom_fields['birthday']['value'] );
				?>
                <option value="empty" selected></option>
                <?php
				foreach( $option_data as $option )	{
					?>
                    	<option value="<?php echo $option; ?>"<?php if( $current_user->birthday == $option ) echo ' selected'; ?>><?php echo $option; ?></option>
                    <?php
				}
				?>
            	</select>
            </td>
          </tr>
          <tr>
            <td width="15%" style="font-weight:bold">Address Line 1:</td>
            <td><input name="address1" type="text" size="30" value="<?php echo $current_user->address1; ?>" /></td>
            <td colspan="2" valign="bottom"><strong>Personal Preferences</strong><font size="-3"> (optional)</font></td>
          </tr>
            <tr>
            <td width="15%" style="font-weight:bold">Address Line 2:</td>
            <td><input name="address2" type="text" size="30" value="<?php echo $current_user->address2; ?>" /></td>
            <td width="15%" align="right">Marketing Info?</td>
            <td><input name="marketing" type="checkbox" value="Y" <?php if( $current_user->marketing == "Y" ) echo "checked" ?> /> <font style="font-size:9px">Your details will never be shared</style></td>
          </tr>
          <tr>
            <td width="15%" style="font-weight:bold">Town/City:</td>
            <td><input name="town" type="text" size="30" value="<?php echo $current_user->town; ?>" /></td>
            <td>&nbsp;</td>
            <td>&nbsp;</td>
          </tr>
          <tr>
            <td width="15%" style="font-weight:bold">County:</td>
            <td><input name="county" type="text" size="30" value="<?php echo $current_user->county; ?>" /></td>
            <td>&nbsp;</td>
            <td>&nbsp;</td>
          </tr>
          <tr>
            <td width="15%" style="font-weight:bold">Post Code:</td>
            <td><input name="postcode" type="text" size="30" value="<?php echo $current_user->postcode; ?>" /></td>
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
				<td width="15%" style="font-weight:bold"><?php echo $field['label']; ?>:</td>
				
				<?php 
				if( $field['type'] == 'checkbox' )	{ 
				?>
					<td width="85%"><input name="<?php echo $field['id']; ?>" type="checkbox" size="30" value="<?php echo $field['value']; ?>"<?php checked( $field['value'], $current_user->$field['id'] ); ?> /></td>
				<?php }
				elseif( $field['type'] == 'dropdown' )	{
					$value_data = explode( ',', $field['value'] );
					?>
					<td width="85%"><select name="<?php echo $field['id']; ?>">
                    <?php
					foreach( $value_data as $value )	{
						?>
						<option value="<?php echo $value; ?>"<?php selected( $value, $current_user->$field['id'] ); ?>><?php echo $value; ?></option>
                        <?php
					}
                    ?>
                    </select></td>
                    <?php
					
				}
				else	{
					?>
					<td width="85%"><input name="<?php echo $field['id']; ?>" type="text" size="30" value="<?php echo $current_user->$field['id']; ?>" /></td>
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
          <td width="15%" style="font-weight:bold">New Password:</td>
            <td width="35%"><input name="new_password" type="password" size="30" value="" /></td>
            <td width="15%" style="font-weight:bold">Confirm Password:</td>
            <td width="35%"><input name="new_password_confirm" type="password" size="30" value="" /></td>
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
		f_mdjm_show_user_login_form();
	}
	add_action( 'wp_footer', f_wpmdjm_print_credit );
?>