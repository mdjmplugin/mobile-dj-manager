<?php
	defined( 'ABSPATH' ) or die( "Direct access to this page is disabled!!!" );
	if ( !current_user_can( 'manage_options' ) )  {
		wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
	}
	
	f_mdjm_has_updated();
	
	if( isset( $_GET['action'] ) ) 	{
		switch( $_GET['action'] )	{
			case 'remove_mdjm_log':
				remove_log( MDJM_DEBUG_LOG );
			break;
			case 'remove_wp_log':
				remove_log( WP_CONTENT_DIR . '/debug.log' );
			break;
			case 'remove_api_log':
				remove_log( MDJM_PLUGIN_DIR . '/admin/includes/api/api-log/mdjm-pp-ipn-debug.log' );
			break;
			default:
				return;	
		} // switch
	}
	
	/* -- Check the size of the debug file and suggest deletion if necessary -- */
	if( file_exists( MDJM_DEBUG_LOG ) && filesize( MDJM_DEBUG_LOG ) > 2097152 )
		mdjm_update_notice( 'update-nag',
							'Warning: Your MDJM debug file is larger than the recommended size of 2MB. <a href="' . mdjm_get_admin_page( 'debugging' ) . 
							'&action=remove_mdjm_log">Click here to delete it</a> and start again (recommended)' );
							
	if( file_exists( WP_CONTENT_DIR . '/debug.log' ) && filesize( WP_CONTENT_DIR . '/debug.log' ) > 2097152 )
		mdjm_update_notice( 'update-nag',
							'Warning: Your WordPress debug file is larger than the recommended size of 2MB. <a href="' . mdjm_get_admin_page( 'debugging' ) . 
							'&action=remove_wp_log">Click here to delete it</a> and start again (recommended)' );
							
	if( file_exists( MDJM_PLUGIN_DIR . '/admin/includes/api/api-log/mdjm-pp-ipn-debug.log' ) 
		&& filesize( MDJM_PLUGIN_DIR . '/admin/includes/api/api-log/mdjm-pp-ipn-debug.log' ) > 2097152 )
		mdjm_update_notice( 'update-nag',
							'Warning: Your MDJM API debug file is larger than the recommended size of 2MB. <a href="' . mdjm_get_admin_page( 'debugging' ) . 
							'&action=remove_api_log">Click here to delete it</a> and start again (recommended)' );
	
	function remove_log( $file )	{
		if( empty( $file ) )	{
			mdjm_update_notice( 'error', 'No file selected for removal' );
			return;	
		}
		
		if( unlink( $file ) )
			return mdjm_update_notice( 'updated', 
					'The log file has been successfully removed. If you have debugging enabled, a new file will be created when required' );
			
		else
			return mdjm_update_notice( 'error', 
					'Could not delete the log file' );
		
	} // remove_log
	
	function db_backup( $tbl='', $replace='' )	{
			global $wpdb;
			
			if( !empty( $tbl ) && !is_array( $tbl ) )	{
				error_log( '$tbl is not an array ' . $tbl, 3, MDJM_DEBUG_LOG );
				return false;	
			}
			$replace = !empty( $replace ) ? $replace : false;
			
			$backup_dir = MDJM_PLUGIN_DIR . '/db_backups';
			
			/* -- Make sure the backup directory exists, otherwise create it -- */
			if( !file_exists( $backup_dir ) )
				mkdir( $backup_dir, 0777, true );
			
			$mdjm_tables = array(
							MDJM_EVENTS_TABLE,
							MDJM_PLAYLIST_TABLE,
							MDJM_TRANSACTION_TABLE,
							MDJM_JOURNAL_TABLE,
							MDJM_HOLIDAY_TABLE,
							);
			$mdjm_desc = array(
							MDJM_EVENTS_TABLE		=> 'Events Table',
							MDJM_PLAYLIST_TABLE		=> 'Playlist Table',
							MDJM_TRANSACTION_TABLE	=> 'Transactions Table',
							MDJM_JOURNAL_TABLE		=> 'Journal Table',
							MDJM_HOLIDAY_TABLE		=> 'Availability Table',
							);
			
			$tables = !empty( $tbl ) ? $tbl : $mdjm_tables;
			
			$file_content = '/*-------------------------------------------' . "\n" . 
							'MDJM Database Table Backup' . "\n" . 
							'MDJM Version: ' . MDJM_VERSION_NUM . "\n" . 
							'Date: ' . date( 'd M Y H:i:s' ) . "\n" . 
							'Table: {MDJM_TABLE} - {MDJM_DESC}' . "\n" . 
							'Total Rows: {MDJM_ROWS}' . "\n" . 
							"\n" . 
							'Support: http://www.mydjplanner.co.uk' . "\n" . 
							'         contact@mydjplanner.co.uk' . "\n" .
							'-------------------------------------------*/' . "\n";
			
			$data_id = array(
							MDJM_EVENTS_TABLE		=> 'event_id',
							MDJM_PLAYLIST_TABLE		=> 'id',
							MDJM_TRANSACTION_TABLE	=> 'trans_id',
							MDJM_JOURNAL_TABLE		=> 'id',
							MDJM_HOLIDAY_TABLE		=> 'id',
						);
			
			/* -- Loop through the tables creating the backups -- */
			foreach( $tables as $table )	{
				/* -- Error check -- */
				if( !in_array( $table, $mdjm_tables ) )	{
					error_log( $table . ' is not an MDJM table', 3, MDJM_DEBUG_LOG );
					continue;
				}
				error_log( 'Backing up ' . $table, 3, MDJM_DEBUG_LOG );
				$backup_file = $backup_dir . '/' . $table . '.sql';
				/* -- Delete existing backups -- */
				if( file_exists( $backup_file ) && empty( $replace ) )	{
					error_log( 'Backup file exists...skipping', 3, MDJM_DEBUG_LOG );
					continue;	
				}
				if( file_exists( $backup_file ) )
					unlink( $backup_file );
				
				$file_content .= 'DROP TABLE IF EXISTS `{MDJM_TABLE}`;' . "\n";
				
				/* -- Create table query -- */
				$create = $wpdb->get_row( 'SHOW CREATE TABLE ' . $table, ARRAY_N);
				
				$file_content .= $create[1] . ';' . "\n";
				
				$results = $wpdb->get_results( "SELECT * FROM `" . $table . '`', ARRAY_N );
				
				$num_rows = $wpdb->num_rows;
				
				if( $num_rows > 0 )	{
					error_log( $num_rows . ' rows of data to export', 3, MDJM_DEBUG_LOG );
					$vals = array(); 
					$z = 0;
										
					for( $i = 0; $i < $num_rows; $i++ )	{
						$items = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM `" . $table . "` WHERE `" . $data_id[$table] . "` = %d", $results[$i][0] ), ARRAY_N );
						$vals[$z] = '(';
						
						for( $j=0; $j < count( $items ); $j++ )	{
							if( isset( $items[$j] ) )	{
								$vals[$z] .= "'" . esc_sql( $items[$j] ) . "'";
							}
							else	{
								$vals[$z] .= 'NULL';
							}
							if( $j < ( count( $items ) -1 ) )	{
								$vals[$z] .= ',';
							}
						}
						
						$vals[$z] .= ')';
						$z++;
					}
					$file_content .= 'INSERT INTO `' . $table . '` VALUES ';      
					$file_content .= '  '.implode( ";\nINSERT INTO `" . $table . "` VALUES ", $vals ) . ";\n";
					
					$search = array( '{MDJM_TABLE}', '{MDJM_DESC}', '{MDJM_ROWS}' );
					$replace = array( $table, $mdjm_desc[$table], $num_rows );
					
					/* -- Write the file -- */
					$handle = fopen( $backup_file, 'x' );
					fwrite( $handle, str_replace( $search, $replace, $file_content ) );
					fclose( $handle );
					
					error_log( $table . ' backup complete', 3, MDJM_DEBUG_LOG );
				}
			} // End foreach( $tables as $table )
			mdjm_update_notice( 'updated', 'Backup completed' );
		} // db_backup

/*
* settings-debugging.php 
* 18/12/2014
* since 0.9.9
* Manage debugging
*/

	global $mdjm, $mdjm_settings, $wpdb;
	
	$mdjm_tables = array(
					'Events'		=> MDJM_EVENTS_TABLE,
					'Playlist'		=> MDJM_PLAYLIST_TABLE,
					'Transactions'	=> MDJM_TRANSACTION_TABLE,
					'Journal'		=> MDJM_JOURNAL_TABLE,
					'Availability'	=> MDJM_HOLIDAY_TABLE,
					);
					
	$db_backup_dir = MDJM_PLUGIN_DIR . '/db_backups';
	$backup_url = WPMDJM_PLUGIN_URL . '/db_backups';
	
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
			mdjm_update_notice( $class, $message );
		}
	}
	if( isset( $_POST['backup_db_tables'] ) && $_POST['backup_db_tables'] == 'Backup Selected Tables' )
		db_backup( $_POST['db_tables'], true );

	
/* Check for actions */
	if( isset( $_POST['action'] ) && $_POST['action'] == 'Submit Debug Files' )	{
		$db_check = '';
		$i = 0;
		foreach( $mdjm_tables as $tbl_function => $tbl_name )	{
			$query = $wpdb->get_results( "SHOW TABLES LIKE '" . $tbl_name . "'" );
			
			if( $query )	{
				$count = $wpdb->get_var( "SELECT COUNT(*) FROM `" . $tbl_name . "`" );
				$db_check .= '<span class="pass">Pass</span>: ' . ucfirst( $tbl_function ) . ' table exists as <span id="mdjm-title">' . $tbl_name . '</span> (' . $count . ' records)';
			}
			else	{
				$db_check .= '<span class="fail">Fail</span>: ' . ucfirst( $tbl_function ) . ' table does not exist';
			}
			$i++;
			if( $i < count( $mdjm_tables ) )	{
				$db_check .= '<br />' . "\r\n";;	
			}
		}
		
		//$mdjm_debug =  WPMDJM_PLUGIN_DIR . '/mdjm_debug.log';
		$pp_debug = WPMDJM_PLUGIN_DIR . '/admin/includes/api/api-log/mdjm-pp-ipn-debug.log';
		$php_info_file = WPMDJM_PLUGIN_DIR . '/admin/includes/phpinfo.html';
		$content_file = WPMDJM_PLUGIN_DIR . '/admin/includes/mdjm_content.html';
		
		/* Get options */
		$mdjm_client_fields = get_option( MDJM_CLIENT_FIELDS );
		$mdjm_schedules = get_option( MDJM_SCHEDULES_KEY );
		
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
		$dump .= '<span id="mdjm-title">Company</span>: ' . MDJM_COMPANY . '<br />' .  "\r\n";
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
		$dump .= '<p>' . var_export( $mdjm_settings['main'], true ) . '</p>' . "\r\n\r\n";
		
		$dump .= '<h2 id="h2"><a id="clientfields"></a>********** MDJM Client Fields **********</h2>' . "\r\n";
		$dump .= '<p>' . var_export( $mdjm_client_fields, true ) . '</p>' . "\r\n\r\n";
		
		$dump .= '<h2 id="h2"><a id="pages"></a>********** MDJM Pages **********</h2>' . "\r\n";
		$dump .= '<p>' . var_export( $mdjm_settings['pages'], true ) . '</p>' . "\r\n\r\n";
		
		$dump .= '<h2 id="h2"><a id="permissions"></a>********** MDJM Permissions **********</h2>' . "\r\n";
		$dump .= '<p>' . var_export( $mdjm_settings['permissions'], true ) . '</p>' . "\r\n\r\n";
		
		$dump .= '<h2 id="h2"><a id="frontend"></a>********** MDJM Front End Text **********</h2>' . "\r\n";
		$dump .= '<p>' . var_export( $mdjm_settings['custom_text'], true ) . '</p>' . "\r\n\r\n";
		
		$dump .= '<h2 id="h2"><a id="schedules"></a>********** MDJM Schedules **********</h2>' . "\r\n";
		$dump .= '<p>' . var_export( $mdjm_schedules, true ) . '</p>' . "\r\n\r\n";
		
		$dump .= '<h2 id="h2"><a id="payments"></a>********** MDJM Payment Options **********</h2>' . "\r\n";
		$dump .= '<p>' . var_export( $mdjm_settings['payments'], true ) . '</p>' . "\r\n\r\n";
		
		$dump .= '</body>' . "\r\n\r\n";
		$dump .= '</html>' . "\r\n\r\n";
		
		$fp = fopen( $content_file, "w+" );
		fwrite( $fp, $dump );
		fclose( $fp );
		
		if( file_exists( MDJM_DEBUG_LOG ) )
			$files[] = MDJM_DEBUG_LOG;
			
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
		if( file_exists( $content_file ) )	{
			$files[] = $content_file;
		}
		
		foreach( $mdjm_tables as $mdjm_table )	{
			if( file_exists( $db_backup_dir . '/' . $mdjm_table . '.sql' ) )	{
				$files[] = $db_backup_dir . '/' . $mdjm_table . '.sql';
			}
			if( file_exists( $db_backup_dir . '/' . $mdjm_table . '_pre_1.2.sql' ) )	{
				$files[] = $db_backup_dir . '/' . $mdjm_table . '_pre_1.2.sql';
			}
		}
		
		
		$headers = 'MIME-Version: 1.0' . "\r\n";
		$headers .= 'Content-type: text/html; charset=UTF-8' . "\r\n";
		$headers .= 'From: ' . MDJM_COMPANY . ' <' . $mdjm_settings['main']['system_email'] . '>' . "\r\n";
		if( wp_mail( 'support@mydjplanner.co.uk', 'Support Debug Info from ' . MDJM_COMPANY, $dump, $headers, $files ) )	{
			mdjm_update_notice( 'updated', 'Thank you. Your files have been successfully submitted to the MDJM Support team who will be in touch shortly.' );	
		}
		else	{
			mdjm_update_notice( 'error', 'An error has occured.' );	
		}
		
		/* Cleanup */
		unlink( $php_info_file );
		unlink( $content_file );
	}
	?>

    <div class="wrap">
    <div id="icon-themes" class="icon32"></div>
    <h2 id="h2">Debugging</h2>
    <p><strong>Important Note:</strong> It is not recommended that you enable debugging unless the <a href="<?php echo mdjm_get_admin_page( 'mydjplanner', 'str' ); ?>">Mobile DJ Manager for WordPress</a> support team have asked you to do so</p>
    <form name="form-debug" id="form-debug" method="post" action="">
    <table class="form-table">
    <tr>
    <th>Enable Debugging?</th>
    <td><input type="checkbox" name="debugging" id="debugging" value="1" <?php checked( '1', MDJM_DEBUG ); ?> /></td>
    </tr>
    <th>Database Table Backups:</th>
    <td>
    <?php
	$i = 0;
	foreach( $mdjm_tables as $table_type => $table_name )	{
		$backup_file = $db_backup_dir . '/' . $table_name . '.sql';
		echo '<input type="checkbox" name="db_tables[]" id="db_tables" value="' . $table_name . '" />' . 
		'&nbsp;&nbsp;&nbsp;' . 
		$table_type . 
		( file_exists( $backup_file ) ? ' <a class="mdjm-small" href="' . $backup_url . '/' . $table_name . '.sql">(Last backup: ' . 
			date( MDJM_SHORTDATE_FORMAT . ' ' . MDJM_TIME_FORMAT, filemtime( $backup_file ) ) . ')</a>' : '' );
		"\r\n";
		echo ( $i < count( $mdjm_tables ) ? '<br /><br />' : '' );
		
		$i++;
	}
	submit_button( 'Backup Selected Tables', 'button-small', 'backup_db_tables', false );
	?>
    </td>
    </tr>
    <?php
	if( MDJM_DEBUG == true )	{
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