<?php
	defined( 'ABSPATH' ) or die( "Direct access to this page is disabled!!!" );
	if ( !current_user_can( 'manage_options' ) )  {
		wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
	}
	
	// If recently updated, display the release notes
	f_mdjm_has_updated();

/*
* settings-debugging.php 
* 18/12/2014
* since 0.9.9
* Manage debugging
*/

	global $mdjm_options;
	
/* Check for form submission */
	if( isset( $_POST['submit'] ) )	{
		if( $_POST['submit'] == 'Save Changes' )	{
			
			/* Update the options table */
			if( !isset( $_POST['debugging'] ) || $_POST['debugging'] != '1' )	{
				$_POST['debugging'] = '0';
			}
			else	{
				$_POST['debugging'] = true;	
			}
			update_option( 'mdjm_debug', $_POST['debugging'] );
			
			$class = 'updated';
			$message = 'Settings updated';
			f_mdjm_update_notice( $class, $message );
		}
	}
	
/* Check for actions */
	if( isset( $_POST['action'] ) && $_POST['action'] == 'Submit Debug Files' )	{
		$debug_file = WPMDJM_PLUGIN_DIR . '/mdjm-debug-' . date( 'd-m-Y' ) . '.log';
		$mdjm_debug =  WPMDJM_PLUGIN_DIR . '/admin/includes/mdjm-error.log';
		/* Get options */
		$mdjm_client_fields = get_option( WPMDJM_CLIENT_FIELDS );
		$mdjm_pages = get_option( 'mdjm_plugin_pages' );
		$mdjm_permissions = get_option( 'mdjm_plugin_permissions' );
		$mdjm_fetext = get_option( WPMDJM_FETEXT_SETTINGS_KEY );
		$mdjm_schedules = get_option( 'mdjm_schedules' );
		
		$plugins = get_plugins();
		
		$lic_info = do_reg_check( 'check' );
		
		/* Create the Var */
		
		$dump = '/********** INFO **********/' . "\r\n";
		$dump .= date( 'd/m/Y H:i' ) . "\r\n";
		$dump .= 'Company: ' . WPMDJM_CO_NAME. "\r\n";
		$dump .= 'Contact: ' . get_bloginfo( 'admin_email' ). "\r\n";
		$dump .= 'URL: ' . get_site_url(). "\r\n\r\n";
		$dump .= 'Template: ' . get_template(). "\r\n\r\n";
		
		$dump .= '/********** PLUGINS **********/' . "\r\n";
		$dump .= var_export( $plugins, true ) . "\r\n\r\n";
		
		$dump .= '/********** MDJM OPTIONS **********/' . "\r\n";
		$dump .= var_export( $mdjm_options, true ) . "\r\n\r\n";
		
		$dump .= '/********** MDJM CLIENT FIELDS **********/' . "\r\n";
		$dump .= var_export( $mdjm_client_fields, true ) . "\r\n\r\n";
		
		$dump .= '/********** MDJM PAGES **********/' . "\r\n";
		$dump .= var_export( $mdjm_pages, true ) . "\r\n\r\n";
		
		$dump .= '/********** MDJM PERMISSIONS **********/' . "\r\n";
		$dump .= var_export( $mdjm_permissions, true ) . "\r\n\r\n";
		
		$dump .= '/********** MDJM FRONT END TEXT **********/' . "\r\n";
		$dump .= var_export( $mdjm_fetext, true ) . "\r\n\r\n";
		
		$dump .= '/********** MDJM SCHEDULES **********/' . "\r\n";
		$dump .= var_export( $mdjm_schedules, true ) . "\r\n\r\n";
		
		/* Write the debug file */		
		file_put_contents( WPMDJM_PLUGIN_DIR . '/mdjm-debug-' . date( 'd-m-Y' ) . '.log', $dump );
		$files = array( $debug_file );
		if( file_exists( $mdjm_debug ) )	{
			$files[] = $mdjm_debug;	
		}
		$wp_debug = WP_CONTENT_DIR . '/debug.log';
		if( file_exists( $wp_debug ) )	{
			$files[] = $wp_debug;
		}
		
		$headers = 'From: ' . WPMDJM_CO_NAME . ' <' . $mdjm_options['system_email'] . '>' . "\r\n";
		if( wp_mail( 'support@mydjplanner.co.uk', 'Support Debug Info from ' . WPMDJM_CO_NAME, 'Test', $headers, $files ) )	{
			f_mdjm_update_notice( 'updated', 'Thank you. Your files have been successfully submitted to MDJM Support who will be in touch shortly.' );	
		}
		else	{
			f_mdjm_update_notice( 'error', 'An error has occured.' );	
		}
		
		/* Cleanup */
		unlink( $debug_file );
	}
	?>

    <div class="wrap">
    <div id="icon-themes" class="icon32"></div>
    <h2>Debugging</h2>
    <p><strong>Important Note:</strong> It is not recommended that you enable debugging unless the <a href="<?php f_mdjm_admin_page( 'mydjplanner' ); ?>">Mobile DJ Manager for WordPress</a> support team have asked you to do so</p>
    <form name="form-debug" id="form-debug" method="post" action="">
    <table class="form-table">
    <tr>
    <th>Enable Debugging?</th>
    <td><input type="checkbox" name="debugging" id="debugging" value="1" <?php checked( '1', get_option( 'mdjm_debug' ) ); ?> /></td>
    </tr>
    <?php
	if( get_option( 'mdjm_debug' ) == 1 )	{
		?>
        <tr>
        <th scope="row">Submit Support Files</th>
        <td colspan="2"><?php submit_button( 'Submit Debug Files', 'secondary', 'action', false, '' ); ?></td>
        </tr>
        <?php	
	}
	?>
    </table>
    </div>
    <?php
	
?>