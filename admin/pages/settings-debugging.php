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

	global $mdjm_options, $wpdb;
	
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
		
		if( !isset( $db_tbl ) )	{
			include( WPMDJM_PLUGIN_DIR . '/includes/config.inc.php' );	
		}

		$db_check = '';
		$i = 0;
		foreach( $db_tbl as $tbl_function => $tbl_name )	{
			$query = $wpdb->get_results( "SHOW TABLES LIKE '" . $tbl_name . "'" );
			
			if( $query )	{
				$count = $wpdb->get_var( "SELECT COUNT(*) FROM `" . $tbl_name . "`" );
				$db_check .= '<span class="pass">Pass</span>: ' . ucfirst( $tbl_function ) . ' table exists as <span id="mdjm-title">' . $tbl_name . '</span> (' . $count . ' records)';
			}
			else	{
				$db_check .= '<span class="fail">Fail</span>: ' . ucfirst( $tbl_function ) . ' table does not exist';
			}
			$i++;
			if( $i < count( $db_tbl ) )	{
				$db_check .= '<br />' . "\r\n";;	
			}
		}
		
		$debug_file = WPMDJM_PLUGIN_DIR . '/mdjm-debug-' . date( 'd-m-Y' ) . '.log';
		$mdjm_debug =  WPMDJM_PLUGIN_DIR . '/admin/includes/mdjm-error.log';
		$pp_debug = WPMDJM_PLUGIN_DIR . '/admin/includes/api/api-log/mdjm-pp-ipn-debug.log';
		$php_info_file = WPMDJM_PLUGIN_DIR . '/admin/includes/phpinfo.log';
		
		/* Get options */
		$mdjm_client_fields = get_option( WPMDJM_CLIENT_FIELDS );
		$mdjm_pages = get_option( 'mdjm_plugin_pages' );
		$mdjm_permissions = get_option( 'mdjm_plugin_permissions' );
		$mdjm_fetext = get_option( WPMDJM_FETEXT_SETTINGS_KEY );
		$mdjm_schedules = get_option( 'mdjm_schedules' );
		$pp_options = get_option( 'mdjm_pp_options' );
		
		$plugins = get_plugins();
		
		$lic_info = do_reg_check( 'check' );
		
		/* PHP INFO */
		ob_start();
		phpinfo();
		$php_info = ob_get_contents();
		ob_end_clean();
	 
		$fp = fopen( $php_info_file, "w+" );
		fwrite( $fp, $php_info );
		fclose( $fp );
			
		/* Create the Var */
		
		$dump = '<html xmlns="http://www.w3.org/1999/xhtml">' . "\r\n";
		$dump .= '<head>' . "\r\n";
		$dump .= '<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />' . "\r\n";
		
		$dump .= '<style type="text/css">' . "\r\n";
		$dump .= '#h2 {' . "\r\n";
		$dump .= '	color: #FF9900;' . "\r\n";
		$dump .= '}' . "\r\n";
		$dump .= 'p {' . "\r\n";
		$dump .= '	font-family: Verdana, Geneva, sans-serif;' . "\r\n";
		$dump .= '	font-size: 12px;' . "\r\n";
		$dump .= '}' . "\r\n";
		$dump .= '#mdjm-title {' . "\r\n";
		$dump .= '	font-weight: bold;' . "\r\n";
		$dump .= '}' . "\r\n";
		$dump .= '.pass {' . "\r\n";
		$dump .= '	font-weight: bold;' . "\r\n";
		$dump .= '	color: #090;' . "\r\n";
		$dump .= '}' . "\r\n";
		$dump .= '.fail {' . "\r\n";
		$dump .= '	font-weight: bold;' . "\r\n";
		$dump .= '	color: #F00;' . "\r\n";
		$dump .= '}' . "\r\n";
		$dump .= '</style>' . "\r\n";
		$dump .= '</head>' . "\r\n";
		
		$dump .= '<body>' . "\r\n";
		$dump .= '<h2 id="h2">********** General Information **********</h2>' . "\r\n";
		$dump .= '<p><span id="mdjm-title">' . date( 'd/m/Y H:i' ) . '</span><br />' . "\r\n";
		$dump .= '<span id="mdjm-title">Company</span>: ' . WPMDJM_CO_NAME . '<br />' .  "\r\n";
		$dump .= '<span id="mdjm-title">Contact</span>: <a href="mailto:' . get_bloginfo( 'admin_email' ) . '">' . get_bloginfo( 'admin_email' ) . '</a><br />' .  "\r\n";
		$dump .= '<span id="mdjm-title">URL</span>: <a href="' . get_site_url() . '" target="_blank">' . get_site_url() . '</a><br />' .  "\r\n\r\n";
		$dump .= '<span id="mdjm-title">Template</span>: ' . get_template() . '<br />' .  "\r\n\r\n";
		$dump .= '</p>' . "\r\n\r\n";
		
		$dump .= '<p>' . "\r\n";
		$dump .= '<a href="#db">Database Information<br /></a>' . "\r\n";
		$dump .= '<a href="#plugins">Plugin Information<br /></a>' . "\r\n";
		$dump .= '<a href="#options">MDJM Options<br /></a>' . "\r\n";
		$dump .= '<a href="#clientfields">MDJM Client Fields<br /></a>' . "\r\n";
		$dump .= '<a href="#pages">MDJM Pages<br /></a>' . "\r\n";
		$dump .= '<a href="#permissions">MDJM Permissions<br /></a>' . "\r\n";
		$dump .= '<a href="#frontend">MDJM Front End Text<br /></a>' . "\r\n";
		$dump .= '<a href="#schedules">MDJM Schedules<br /></a>' . "\r\n";
		$dump .= '<a href="#payments">MDJM Payments<br /></a>' . "\r\n";
		$dump .= '</p>' . "\r\n";
		
		$dump .= '<h2 id="h2"><a id="db"></a>********** Database Tables **********</h2>' . "\r\n";
		$dump .= '<p>' . $db_check . '</p>' . "\r\n";
		
		$dump .= '<h2 id="h2"><a id="plugins"></a>********** Plugins **********</h2>' . "\r\n";
		$dump .= '<p>' . var_export( $plugins, true ) . '</p>' . "\r\n\r\n";
		
		$dump .= '<h2 id="h2"><a id="options"></a>********** MDJM Options **********</h2>' . "\r\n";
		$dump .= '<p>' . var_export( $mdjm_options, true ) . '</p>' . "\r\n\r\n";
		
		$dump .= '<h2 id="h2"><a id="clientfields"></a>********** MDJM Client Fields **********</h2>' . "\r\n";
		$dump .= '<p>' . var_export( $mdjm_client_fields, true ) . '</p>' . "\r\n\r\n";
		
		$dump .= '<h2 id="h2"><a id="pages"></a>********** MDJM Pages **********</h2>' . "\r\n";
		$dump .= '<p>' . var_export( $mdjm_pages, true ) . '</p>' . "\r\n\r\n";
		
		$dump .= '<h2 id="h2"><a id="permissions"></a>********** MDJM Permissions **********</h2>' . "\r\n";
		$dump .= '<p>' . var_export( $mdjm_permissions, true ) . '</p>' . "\r\n\r\n";
		
		$dump .= '<h2 id="h2"><a id="frontend"></a>********** MDJM Front End Text **********</h2>' . "\r\n";
		$dump .= '<p>' . var_export( $mdjm_fetext, true ) . '</p>' . "\r\n\r\n";
		
		$dump .= '<h2 id="h2"><a id="schedules"></a>********** MDJM Schedules **********</h2>' . "\r\n";
		$dump .= '<p>' . var_export( $mdjm_schedules, true ) . '</p>' . "\r\n\r\n";
		
		$dump .= '<h2 id="h2"><a id="payments"></a>********** MDJM Payment Options **********</h2>' . "\r\n";
		$dump .= '<p>' . var_export( $pp_options, true ) . '</p>' . "\r\n\r\n";
		
		$dump .= '</body>' . "\r\n\r\n";
		$dump .= '</html>' . "\r\n\r\n";
		
		/* Write the debug file */		
		file_put_contents( $mdjm_debug, $dump );
		$files = array();
		if( file_exists( $mdjm_debug ) )	{
			$files[] = $mdjm_debug;	
		}
		$wp_debug = WP_CONTENT_DIR . '/debug.log';
		if( file_exists( $wp_debug ) )	{
			$files[] = $wp_debug;
		}
		if( file_exists( $pp_debug ) )	{
			$files[] = $pp_debug;
		}
		if( file_exists( $php_info_file ) )	{
			$files[] = $php_info_file;
		}
		
		$headers = 'MIME-Version: 1.0' . "\r\n";
		$headers .= 'Content-type: text/html; charset=UTF-8' . "\r\n";
		$headers .= 'From: ' . WPMDJM_CO_NAME . ' <' . $mdjm_options['system_email'] . '>' . "\r\n";
		if( wp_mail( 'support@mydjplanner.co.uk', 'Support Debug Info from ' . WPMDJM_CO_NAME, $dump, $headers, $files ) )	{
			f_mdjm_update_notice( 'updated', 'Thank you. Your files have been successfully submitted to MDJM Support who will be in touch shortly.' );	
		}
		else	{
			f_mdjm_update_notice( 'error', 'An error has occured.' );	
		}
		
		/* Cleanup */
		unlink( $debug_file );
		unlink( $php_info_file );
	}
	?>

    <div class="wrap">
    <div id="icon-themes" class="icon32"></div>
    <h2 id="h2">Debugging</h2>
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