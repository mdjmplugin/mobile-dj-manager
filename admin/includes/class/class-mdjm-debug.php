<?php
/*
 * class-mdjm-debug.php
 * 03/06/2015
 * @since 1.2.1
 * The MDJM Debug class
 */
	
	/* -- Build the MDJM Debug class -- */
	if( !class_exists( 'MDJM_Debug' ) )	{
		class MDJM_Debug	{
			
			public function __construct()	{
				$this->settings = get_option( MDJM_DEBUG_SETTINGS_KEY );
				$this->db_backup_dir = MDJM_PLUGIN_DIR . '/db_backups';
				$this->backup_url = WPMDJM_PLUGIN_URL . '/db_backups';
				$this->tables = array(
									'Events'			  => MDJM_EVENTS_TABLE,
									'Playlist'	  		=> MDJM_PLAYLIST_TABLE,
									'Music Library'	   => MDJM_MUSIC_LIBRARY_TABLE,
									'Transactions'  		=> MDJM_TRANSACTION_TABLE,
									'Journal'	   		 => MDJM_JOURNAL_TABLE,
									'Availability'  		=> MDJM_HOLIDAY_TABLE,
									);
				$this->deprecated = array(
										MDJM_EVENTS_TABLE,
										MDJM_TRANSACTION_TABLE,
										MDJM_JOURNAL_TABLE,
										);
										
				define( 'MDJM_DEBUG', !empty( $this->settings['enable'] ) ? true : false );
				define( 'MDJM_DEBUG_LOG', MDJM_PLUGIN_DIR . '/mdjm_debug.log' );
								
			} // __construct
			
			/**
			 * log_it
			 * Send the specified message to the debug file
			 *
			 * @param	str		Required: $msg 		The message to log
			 *			bool	Optional: $stampit	true to include timestamp otherwise false
			 * @return
			 * @since	1.1.3
			 * 
			 */
			public function log_it( $msg, $stampit=false )	{
				if( MDJM_DEBUG == false || empty( $msg ) )
					return;
				
				$debug_log = ( $stampit == true ? date( 'd/m/Y  H:i:s', current_time( 'timestamp' ) ) . ' : ' . $msg : '    ' . $msg );
				
				error_log( $debug_log . "\r\n", 3, MDJM_DEBUG_LOG );	
				
			} // log_it
			
			/*
			 * Check the size of the debug log files and display notice
			 * if larger than recommended
			 *
			 * 
			 */
			function log_file_check()	{
				
				$this->files = array(
							'MDJM Debug'		=> array( MDJM_DEBUG_LOG, 'mdjm_debug.log' ),
							'MDJM PayPal API'   => array( MDJM_PLUGIN_DIR . '/admin/includes/api/api-log/mdjm-pp-ipn-debug.log', 'mdjm-pp-ipn-debug.log' ),
							'WordPress Debug'   => array( WP_CONTENT_DIR . '/debug.log', 'debug.log' ),
							);
				
				/* -- Do we need to delete any files? -- */
				if( isset( $_POST['delete_log_files'], $_POST['delete_files'] ) && $_POST['delete_log_files'] == 'Delete Selected Files' )
					$this->delete_log( $_POST['delete_files'] );
				
				if( empty( $this->settings['warn'] ) && empty( $this->settings['auto_purge'] ) )
					return;
							
				$bytes = pow( 1024, $this->settings['log_size'] );
				
				/* -- Check the files -- */
				foreach( $this->files as $name => $conf )	{
					if( file_exists( $conf[0] ) && filesize( $conf[0] ) > $bytes )	{
						if( !empty( $this->settings['auto_purge'] ) )	{
							$this->log_it( 'Auto purge enabled for oversized log file ' . $name, true );
							$this->delete_log( array( $name ) );
						}
						else	{
							if( empty( $this->settings['warn'] ) ) // If warnings are disabled, skip
								continue;
							
							$this->log_it( 'Auto purge disabled. Displaying notice for oversized log file ' . $name, true );
							$warn[$conf[1]] = $name;
						}
						
					}
				}
				
				if( isset( $warn ) )	{
					echo '<div class="mdjm-warning">' . "\r\n";
					echo '<form name="mdjm_log_files" method="POST">' . "\r\n";
					echo _n( 'One', 'Some', count( $warn ) ) . __( ' of your log files exceed the specified limit of ' . $this->settings['log_size'] . ' megabytes' ) 
					. '<p>' . "\r\n";
					
					$i = 1;
					 
					foreach( $warn as $file => $file_name )	{
						echo '<input type="checkbox" name="delete_files[]" id = "delete_files" value="' . $file_name . '" />' . 
						'&nbsp;&nbsp;&nbsp;' . 
						$file_name . 
						( $i != count( $warn ) ? '<br />' : '' ) . "\r\n";
					}
					echo '</p>' . "\r\n";
					submit_button( 'Delete Selected Files', 'primary', 'delete_log_files', true );
					echo '</form>' . "\r\n";
					echo '</div>' . "\r\n";	
				}
							
			} // log_file_check
			
			/*
			 * Delete the given log file so that a new one may be generated
			 *
			 * @param	arr			$file	Required: Log file (incl path) to be removed. 
			 *								If "all" is parsed, all log files will be removed	
			 * @return	notice
			 */
			function delete_log( $files='' )	{
				foreach( $files as $file )	{
					if( file_exists( $this->files[$file][0] ) )	{
						if( unlink( $this->files[$file][0] ) )	{
							$this->log_it( 'Purged the ' . $file . ' log file', true );
							$success[] = 'The ' . $file . ' log file was auto-purged successfully in accordance with your <a href="' . 
								mdjm_get_admin_page( 'debugging') . '">Debug Settings</a>';
						}
						else	{
							$this->log_it( 'ERROR: Could not purge the ' . $file . ' log file', true );
							$error[] = 'The ' . $file . ' log file cound not be purged';
						}
					}
				}
				
				if( isset( $success ) )
					mdjm_update_notice( 'updated', implode( '<br />', $success ) );
					
				if( isset( $error ) )
					mdjm_update_notice( 'error', implode( '<br />', $success ) );
				
			} // delete_log
			
			/*
			 * Allow user to backup the database tables
			 *
			 *
			 *
			 */
			function db_backup_form()	{
				/* -- Should we backup? -- */
				if( isset( $_POST['backup_db_tables'] ) && $_POST['backup_db_tables'] == 'Backup Selected Tables' )
					$this->backup_tables( true );
				
				echo '<h3>Database Table Backups<hr /></h3>' . "\r\n" . 
				'<p>Backup the custom MDJM database tables below. Once the backup has completed ' . 
				'you can click the link to download the backup file</p>' . "\r\n" . 
				'<form name="mdjm_db_backup" id="mdjm_db_backup" method="POST">' . "\r\n" . 
				'<table class="form-table">' . "\r\n";
				
				asort( $this->tables );
				
				/* -- Table row for each DB table -- */
				foreach( $this->tables as $table => $name )	{
					echo '<tr>' . "\r\n" . 
						'<th scope="row"><label for="' . $name . '">' . __( $table ) . ':</label></th>' . "\r\n" . 
						'<td>' . 
						'<input type="checkbox" name="mdjm_table[]" id="' . $name . '" value="' . $name . '" />' . 
						$this->backup_exists( $name );
						if( in_array( $name, $this->deprecated ) )
							echo '<p class="description">This table became deprecated with MDJM version 1.2 ' . 
							'and is no longer in use</p>'; 
						'</td>' . "\r\n" . 
						'</tr>' . "\r\n";
				}
				
				echo '</table>' . "\r\n";
				submit_button( 'Backup Selected Tables', 'primary', 'backup_db_tables', true );
				echo '</form>' . "\r\n";
			}
			
			/*
			 * Check if a backup exists for the given file and if so, print date of backup
			 *
			 * @param	str		$table		Required: The name of the DB table
			 * @return	str					Date of backup of empty
			 */
			function backup_exists( $table )	{
				$backup_file = $this->db_backup_dir . '/' . $table . '.sql';
				
				if( file_exists( $backup_file ) ) // We have a backup file
					return ' <a class="mdjm-small" href="' . $this->backup_url . '/' . 
					$table . '.sql">Last backup: ' . date( MDJM_SHORTDATE_FORMAT, filemtime( $backup_file ) ) . ' ' . 
					get_date_from_gmt( filemtime( $backup_file ), MDJM_TIME_FORMAT ) . '</a>' . "\r\n";
					
				return;
			} // backup_exists
			
			/*
			 * Display the button to submit log files via email
			 *
			 *
			 *
			 */
			function submit_files_button()	{
				if( MDJM_DEBUG != true )
					return;
				
				/* -- Are we submitting debug logs? -- */
				if( isset( $_POST['submit_files'] ) && $_POST['submit_files'] == 'Submit Debug Files' )
					$this->submit_logs();
				
				echo '<h3>Submit Log Files to MDJM Support</h3><hr />' . "\r\n";
				
				echo '<ul class="subsubsub">' . "\r\n" . 
				'<li><a href="' . mdjm_get_admin_page( 'mydjplanner' ) . '/forums/" target="blank">Support Forums</a></li>' . 
				"\r\n" . 
				'<li> | <a href="https://www.facebook.com/groups/mobiledjmanager" target="blank">MDJM Facebook Group</a></li>' . 
				'</ul>' . "\r\n";
					
				echo '<form name="mdjm_submit_debug" id="mdjm_submit_debug" method="POST">' . "\r\n" . 
				'<table class="form-table">' . "\r\n" . 
				'<tr>' . "\r\n" . 
				'<th scope="row"><label for="share_plugins">Include Plugin List?</label></th>' . "\r\n" . 
				'<td>' . 
				'<input type="checkbox" name="share_plugins" id="share_plugins" value="1" checked="checked" />' . 
				'<p class="description">With selected, the log files you submit will include a list of your currently installed plugins ' . 
				'which helps us to identify any conflicts. It is recommended to keep this checked.</p>' .  
				'</td>' . "\r\n" . 
				'</tr>' . "\r\n" . 
				'<tr>' . "\r\n" . 
				'<th scope="row"><label for="share_tables">Include Table Info?</label></th>' . "\r\n" . 
				'<td>' . 
				'<input type="checkbox" name="share_tables" id="share_tables" value="1" checked="checked" />' . 
				'<p class="description">With selected, the log files you submit will include the status of the MDJM custom database tables, ' . 
				'and the number of records within them. If a backup of these tables exists, that will be included too. Again, recommended to keep this checked.</p>' . 
				'</td>' . "\r\n" . 
				'</tr>' . "\r\n" . 
				'</table>' . "\r\n";
				
				submit_button( 'Submit Debug Files', 'secondary', 'submit_files', true, '' );
				echo '<p class="description">Click the button above to submit your debug log files to the ' . 
					'MDJM support team. Please do not submit your files if you have not been asked to do so</p>' . "\r\n" . 
				'</form>' . "\r\n";
			} // submit_files_button
			
			/*
			 * Submit the debug files to MDJM Support
			 *
			 *
			 *
			 */
			function submit_logs()	{
				global $mdjm, $mdjm_settings, $wpdb;
				
				$db_check = '';
				$i = 0;
				foreach( $this->tables as $tbl_function => $tbl_name )	{
					$query = $wpdb->get_results( "SHOW TABLES LIKE '" . $tbl_name . "'" );
					
					if( $query )	{
						$count = $wpdb->get_var( "SELECT COUNT(*) FROM `" . $tbl_name . "`" );
						$db_check .= '<span class="pass">Pass</span>: ' . ucfirst( $tbl_function ) . 
						' table exists as <span id="mdjm-title">' . $tbl_name . '</span> (' . $count . ' records)';
					}
					else	{
						$db_check .= '<span class="fail">Fail</span>: ' . ucfirst( $tbl_function ) . 
						' table does not exist';
					}
					$i++;
					if( $i < count( $this->tables ) )
						$db_check .= '<br />' . "\r\n";;	
				}
				
				/* -- Set log files -- */
				$this->payments_log = MDJM_PLUGIN_DIR . '/admin/includes/api/api-log/mdjm-pp-ipn-debug.log';
				$this->php_info_log = MDJM_PLUGIN_DIR . '/admin/includes/phpinfo.html';
				$this->content_file = MDJM_PLUGIN_DIR . '/admin/includes/mdjm_content.html';
				
				/* -- Get options -- */
				$mdjm_client_fields = get_option( MDJM_CLIENT_FIELDS );
				$mdjm_schedules = get_option( MDJM_SCHEDULES_KEY );
				
				/* -- List of plugins installed -- */
				$plugins = get_plugins();
				
				/* -- License Status -- */
				$status = $mdjm->_mdjm_validation();
				
				/* -- Generate the PHP info log -- */
				ob_start();
				phpinfo();
				$php_info = ob_get_contents();
				ob_end_clean();
			 
				$fp = fopen( $this->php_info_log, "w+" );
				fwrite( $fp, $php_info );
				fclose( $fp );
				
				/* -- Generate the debug content -- */
				$content = '<html xmlns="http://www.w3.org/1999/xhtml">' . "\r\n";
				$content .= '<head>' . "\r\n";
				$content .= '<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />' . "\r\n";
				
				$content .= '<style type="text/css">' . "\r\n";
				$content .= '#h2 {' . "\r\n";
				$content .= '	color: #FF9900;' . "\r\n";
				$content .= '}' . "\r\n";
				$content .= 'p {' . "\r\n";
				$content .= '	font-family: Verdana, Geneva, sans-serif;' . "\r\n";
				$content .= '	font-size: 12px;' . "\r\n";
				$content .= '}' . "\r\n";
				$content .= '#mdjm-title {' . "\r\n";
				$content .= '	font-weight: bold;' . "\r\n";
				$content .= '}' . "\r\n";
				$content .= '.pass {' . "\r\n";
				$content .= '	font-weight: bold;' . "\r\n";
				$content .= '	color: #090;' . "\r\n";
				$content .= '}' . "\r\n";
				$content .= '.fail {' . "\r\n";
				$content .= '	font-weight: bold;' . "\r\n";
				$content .= '	color: #F00;' . "\r\n";
				$content .= '}' . "\r\n";
				$content .= '</style>' . "\r\n";
				$content .= '</head>' . "\r\n";
				
				$content .= '<body>' . "\r\n";
				$content .= '<h2 id="h2">********** General Information **********</h2>' . "\r\n";
				$content .= '<p><span id="mdjm-title">' . date( 'd/m/Y H:i' ) . '</span><br />' . "\r\n";
				$content .= '<span id="mdjm-title">Company</span>: ' . MDJM_COMPANY . '<br />' .  "\r\n";
				$content .= '<span id="mdjm-title">Contact</span>: <a href="mailto:' . get_bloginfo( 'admin_email' ) . '">' . get_bloginfo( 'admin_email' ) . '</a><br />' .  "\r\n";
				$content .= '<span id="mdjm-title">URL</span>: <a href="' . get_site_url() . '" target="_blank">' . get_site_url() . '</a><br />' .  "\r\n\r\n";
				$content .= '<span id="mdjm-title">Template</span>: ' . get_template() . '<br />' .  "\r\n\r\n";
				$content .= '</p>' . "\r\n\r\n";
				
				$content .= '<p>' . "\r\n";
				
				if( isset( $_POST['share_tables'] ) )
					$content .= '<a href="#db">Database Information<br /></a>' . "\r\n";
				
				if( isset( $_POST['share_plugins'] ) )
					$content .= '<a href="#plugins">Plugin Information<br /></a>' . "\r\n";
					
				$content .= '<a href="#options">MDJM Options<br /></a>' . "\r\n";
				$content .= '<a href="#events">MDJM Event Options<br /></a>' . "\r\n";
				$content .= '<a href="#playlist">MDJM Playlist Options<br /></a>' . "\r\n";
				$content .= '<a href="#clientzone">MDJM Client Zone Settings<br /></a>' . "\r\n";
				$content .= '<a href="#email">MDJM Email Options<br /></a>' . "\r\n";
				$content .= '<a href="#templates">MDJM Templates<br /></a>' . "\r\n";
				$content .= '<a href="#clientfields">MDJM Client Fields<br /></a>' . "\r\n";
				$content .= '<a href="#pages">MDJM Pages<br /></a>' . "\r\n";
				$content .= '<a href="#permissions">MDJM Permissions<br /></a>' . "\r\n";
				$content .= '<a href="#frontend">MDJM Front End Text<br /></a>' . "\r\n";
				$content .= '<a href="#schedules">MDJM Schedules<br /></a>' . "\r\n";
				$content .= '<a href="#payments">MDJM Payments<br /></a>' . "\r\n";
				$content .= '<a href="#paypal">MDJM PayPal Configuration<br /></a>' . "\r\n";
				$content .= '</p>' . "\r\n";
								
				if( isset( $_POST['share_tables'] ) )	{
					$content .= '<h2 id="h2"><a id="db"></a>********** Database Tables **********</h2>' . "\r\n";
					$content .= '<p>' . $db_check . '</p>' . "\r\n";
				}
				
				if( isset( $_POST['share_plugins'] ) )	{
					$content .= '<h2 id="h2"><a id="plugins"></a>********** Plugins **********</h2>' . "\r\n";
					$content .= '<p>' . var_export( $plugins, true ) . '</p>' . "\r\n\r\n";
				}
				
				$content .= '<h2 id="h2"><a id="options"></a>********** MDJM Options **********</h2>' . "\r\n";
				$content .= '<p>' . var_export( $mdjm_settings['main'], true ) . '</p>' . "\r\n\r\n";
				
				$content .= '<h2 id="h2"><a id="events"></a>********** MDJM Event Options **********</h2>' . "\r\n";
				$content .= '<p>' . var_export( $mdjm_settings['events'], true ) . '</p>' . "\r\n\r\n";
				
				$content .= '<h2 id="h2"><a id="playlist"></a>********** MDJM Playlist Options **********</h2>' . "\r\n";
				$content .= '<p>' . var_export( $mdjm_settings['playlist'], true ) . '</p>' . "\r\n\r\n";
				
				$content .= '<h2 id="h2"><a id="clientzone"></a>********** MDJM Client Zone Options **********</h2>' . "\r\n";
				$content .= '<p>' . var_export( $mdjm_settings['clientzone'], true ) . '</p>' . "\r\n\r\n";
				
				$content .= '<h2 id="h2"><a id="email"></a>********** MDJM Email Options **********</h2>' . "\r\n";
				$content .= '<p>' . var_export( $mdjm_settings['email'], true ) . '</p>' . "\r\n\r\n";
				
				$content .= '<h2 id="h2"><a id="templates"></a>********** MDJM Templates **********</h2>' . "\r\n";
				$content .= '<p>' . var_export( $mdjm_settings['templates'], true ) . '</p>' . "\r\n\r\n";
				
				$content .= '<h2 id="h2"><a id="clientfields"></a>********** MDJM Client Fields **********</h2>' . "\r\n";
				$content .= '<p>' . var_export( $mdjm_client_fields, true ) . '</p>' . "\r\n\r\n";
				
				$content .= '<h2 id="h2"><a id="pages"></a>********** MDJM Pages **********</h2>' . "\r\n";
				$content .= '<p>' . var_export( $mdjm_settings['pages'], true ) . '</p>' . "\r\n\r\n";
				
				$content .= '<h2 id="h2"><a id="permissions"></a>********** MDJM Permissions **********</h2>' . "\r\n";
				$content .= '<p>' . var_export( $mdjm_settings['permissions'], true ) . '</p>' . "\r\n\r\n";
				
				$content .= '<h2 id="h2"><a id="frontend"></a>********** MDJM Front End Text **********</h2>' . "\r\n";
				$content .= '<p>' . var_export( $mdjm_settings['custom_text'], true ) . '</p>' . "\r\n\r\n";
				
				$content .= '<h2 id="h2"><a id="schedules"></a>********** MDJM Schedules **********</h2>' . "\r\n";
				$content .= '<p>' . var_export( $mdjm_schedules, true ) . '</p>' . "\r\n\r\n";
				
				$content .= '<h2 id="h2"><a id="payments"></a>********** MDJM Payment Options **********</h2>' . "\r\n";
				$content .= '<p>' . var_export( $mdjm_settings['payments'], true ) . '</p>' . "\r\n\r\n";
				
				$content .= '<h2 id="h2"><a id="paypal"></a>********** MDJM PayPal Options **********</h2>' . "\r\n";
				$content .= '<p>' . var_export( $mdjm_settings['paypal'], true ) . '</p>' . "\r\n\r\n";
				
				$content .= '</body>' . "\r\n\r\n";
				$content .= '</html>' . "\r\n\r\n";
				
				/* -- Write content to file -- */
				$fp = fopen( $this->content_file, "w+" );
				fwrite( $fp, $content );
				fclose( $fp );
				
				/* -- Determine which files to attach -- */
				if( file_exists( MDJM_DEBUG_LOG ) ) // MDJM Log file
					$files[] = MDJM_DEBUG_LOG;
					
				if( file_exists( WP_CONTENT_DIR . '/debug.log' ) ) // WP Log file
					$files[] = WP_CONTENT_DIR . '/debug.log';
					
				if( file_exists( $this->payments_log ) ) // Paypal IPN Log
					$files[] = $this->payments_log;
					
				if( file_exists( $this->php_info_log ) ) // PHP Info file
					$files[] = $this->php_info_log;
				
				if( file_exists( $this->content_file ) ) // Debug content log
					$files[] = $this->content_file;
					
				/* -- If we have DB table backups we can include those -- */
				foreach( $this->tables as $table )	{
					if( file_exists( $this->db_backup_dir . '/' . $table . '.sql' ) && isset( $_POST['share_tables'] ) )	{
						$files[] = $this->db_backup_dir . '/' . $table . '.sql';
					}
					/* -- Pre version 1.2 backup files -- */
					if( file_exists( $this->db_backup_dir . '/' . $table . '_pre_1.2.sql' ) && isset( $_POST['share_tables'] ) )	{
						$files[] = $this->db_backup_dir . '/' . $table . '_pre_1.2.sql';
					}
				}
				
				/* -- Submit the log files -- */
				$headers[] = 'MIME-Version: 1.0' . "\r\n";
				$headers[] .= 'Content-type: text/html; charset=UTF-8' . "\r\n";
				$headers[] .= 'From: ' . MDJM_COMPANY . ' <' . $mdjm_settings['email']['system_email'] . '>' . "\r\n";
				$headers[] = 'X-Mailer: ' . MDJM_NAME . ' version ' . MDJM_VERSION_NUM . ' (' . mdjm_get_admin_page( 'mydjplanner' ) . ')';
				
				if( wp_mail( 'support@mydjplanner.co.uk', 
							 'Support Debug Info from ' . MDJM_COMPANY, 
							 $content,
							 $headers,
							 $files ) )
					mdjm_update_notice( 'updated', 'Thank you. Your files have been successfully submitted to the MDJM Support team who will be in touch shortly.' );	
				else
					mdjm_update_notice( 'error', 'An error has occured.' );	
				
				/* Cleanup */
				if( file_exists( $this->php_info_log ) )
					unlink( $this->php_info_log );
				
				if( file_exists( $this->content_file ) )
					unlink( $this->content_file );
						
			} // submit_logs
			
			/*
			 * Backup the selected database tables
			 *
			 * @param	array	$_POST['mdjm_table']	Required: The table names to backup
			 *			bool	$replace				Optional: true replaces existing files, 
			 *											false (default) does not and will fail
			 *
			 */
			function backup_tables( $replace='' )	{
				global $wpdb;
				
				if( empty( $_POST['mdjm_table'] ) )	{
					$this->log_it( 'No database table was selected for backup', true );
					mdjm_update_notice( 'error', 'No tables selected' );
					return false;
				}
				
				$tables = $_POST['mdjm_table'];
				
				$replace = ( isset( $replace ) ? $replace : false );
				
				/* -- Make sure the backup directory exists, otherwise create it -- */
				if( !file_exists( $this->db_backup_dir ) )
					mkdir( $this->db_backup_dir, 0777, true );
					
				/* -- Start the SQL file -- */
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
				
				/* -- Unique identifier fields -- */				
				$data_id = array(
							MDJM_EVENTS_TABLE				  => 'event_id',
							MDJM_PLAYLIST_TABLE				=> 'id',
							MDJM_MUSIC_LIBRARY_TABLE		   => 'id',
							MDJM_TRANSACTION_TABLE			 => 'trans_id',
							MDJM_JOURNAL_TABLE				 => 'id',
							MDJM_HOLIDAY_TABLE				 => 'id',
						);
						
				/* -- Loop through the tables creating the backups -- */
				foreach( $tables as $table )	{
					$backup_file = $this->db_backup_dir . '/' . $table . '.sql';
					
					/* -- Error checking -- */
					if( !in_array( $table, $this->tables ) )	{
						if( MDJM_DEBUG == true )
							$this->log_it( $table . ' is not an MDJM table', true );
						continue;
					}
					if( file_exists( $backup_file ) && $replace != true )	{
						$this->log_it( 'Backup file exists for ' . $table . '. Skipping', true );
						continue;	
					}
					
					/* -- We are good -- */
					if( MDJM_DEBUG == true )
						$this->log_it( 'Backing up ' . $table, 3, MDJM_DEBUG_LOG );
					
					/* -- Delete existing backups -- */
					if( file_exists( $backup_file ) )
						unlink( $backup_file );
						
					$file_content .= 'DROP TABLE IF EXISTS `{MDJM_TABLE}`;' . "\n";
				
					/* -- Create table query -- */
					$create = $wpdb->get_row( 'SHOW CREATE TABLE ' . $table, ARRAY_N);
					
					$file_content .= $create[1] . ';' . "\n";
					
					$results = $wpdb->get_results( "SELECT * FROM `" . $table . '`', ARRAY_N );
					
					$num_rows = $wpdb->num_rows;
					
					if( $num_rows > 0 )	{
						$this->log_it( $num_rows . ' rows of data to export', false );
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
						
						$mdjm_desc = array(
							MDJM_EVENTS_TABLE				  => 'Events Table',
							MDJM_PLAYLIST_TABLE				=> 'Playlist Table',
							MDJM_MUSIC_LIBRARY_TABLE		   => 'Music Library Table',
							MDJM_TRANSACTION_TABLE			 => 'Transactions Table',
							MDJM_JOURNAL_TABLE				 => 'Journal Table',
							MDJM_HOLIDAY_TABLE				 => 'Availability Table',
							);
						
						$search = array( '{MDJM_TABLE}', '{MDJM_DESC}', '{MDJM_ROWS}' );
						$replace = array( $table, $mdjm_desc[$table], $num_rows );
						
						/* -- Write the file -- */
						$handle = fopen( $backup_file, 'x' );
						fwrite( $handle, str_replace( $search, $replace, $file_content ) );
						fclose( $handle );
						
						$this->log_it( $table . ' backup complete', true );
					} // if( $num_rows > 0 )
						
				} // End foreach( $tables as $table )
				mdjm_update_notice( 'updated', 'Backup completed' );
			} // backup_tables
			
		} // Class MDJM_Debug
	}
	
	$GLOBALS['mdjm_debug'] = new MDJM_Debug();