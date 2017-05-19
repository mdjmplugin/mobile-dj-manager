<?php
/*
 * MDJM Debug Class for logging errors
 * @since 1.2.1
 * 
 */
	
/* -- Build the MDJM Debug class -- */
class MDJM_Debug	{
	/**
	 * The backup DIR
	 *
	 * @since	1.3
	 */
	private $db_backup_dir;
	
	/**
	 * The backup URL
	 *
	 * @since	1.3
	 */
	private $backup_url;
	
	/**
	 * The DB tables
	 *
	 * @since	1.3
	 */
	private $tables;
	
	/**
	 * The Log files
	 *
	 * @since	1.3
	 */
	private $files;
	
	/**
	 * Class constructor
	 */
	public function __construct()	{
		
		global $wpdb;
		
		$this->db_backup_dir	= MDJM_PLUGIN_DIR . '/db_backups';
		$this->backup_url	   = MDJM_PLUGIN_URL . '/db_backups';
		$this->tables		   = array( 'Availability' => $wpdb->prefix . 'mdjm_avail' );
							
		define( 'MDJM_DEBUG', mdjm_get_option( 'enable_debugging', false ) );
		define( 'MDJM_DEBUG_LOG', MDJM_PLUGIN_DIR . '/mdjm_debug.log' );
		
		add_action( 'admin_init', array( &$this, 'log_file_check' ) );
		
	} // __construct
	
	/**
	 * log_it
	 * Send the specified message to the debug file
	 *
	 * @param	str		Required: $msg 		The message to log
	 * @param	bool	Optional: $stampit	true to include timestamp otherwise false
	 * @return	void
	 * @since	1.1.3
	 * 
	 */
	public function log_it( $msg, $stampit=false )	{
		
		if( MDJM_DEBUG == false || empty( $msg ) )	{
			return;
		}
		
		$debug_log = $stampit == true ? date( 'd/m/Y  H:i:s', current_time( 'timestamp' ) ) . ' : ' . $msg : '    ' . $msg;
		
		error_log( $debug_log . "\r\n", 3, MDJM_DEBUG_LOG );	
		
	} // log_it
	
	/*
	 * Check the size of the debug log files and display notice
	 * if larger than recommended
	 *
	 * 
	 */
	function log_file_check()	{
		
		$files = array(
			'MDJM Debug'		  => array( MDJM_DEBUG_LOG, 'mdjm_debug.log' ),				
			'WordPress Debug'	 => array( WP_CONTENT_DIR . '/debug.log', 'debug.log' ),
		);
		
		$this->files = apply_filters( 'mdjm_log_files', $files );
		
		/* -- Do we need to delete any files? -- */
		if( isset( $_POST['delete_log_files'], $_POST['delete_files'] ) && $_POST['delete_log_files'] == 'Delete Selected Files' )	{
			$this->delete_log( $_POST['delete_files'] );
		}
		
		if( ! ( mdjm_get_option( 'debug_warn', false ) ) && ! ( mdjm_get_option( 'debug_auto_purge' ) ) )	{
			return;
		}
					
		$bytes = pow( 1024, mdjm_get_option( 'debug_log_size', 2 ) );
		
		/* -- Check the files -- */
		foreach( $this->files as $name => $conf )	{
			
			if( file_exists( $conf[0] ) && filesize( $conf[0] ) > $bytes )	{
				
				if( ! ! ( mdjm_get_option( 'debug_auto_purge' ) ) )	{
					
					$this->log_it( 'Auto purge enabled for oversized log file ' . $name, true );
					$this->delete_log( array( $name ) );
					mdjm_update_option( 'debug_purged', current_time( 'timestamp' ) );
					
				} else	{
					
					if( ! ( mdjm_get_option( 'debug_warn' ) ) )	{ // If warnings are disabled, skip
						continue;
					}
					
					$this->log_it( 'Auto purge disabled. Displaying notice for oversized log file ' . $name, true );
					$warn[$conf[1]] = $name;
				}
				
			}
		}
		
		if( isset( $warn ) )	{
			
			echo '<div class="mdjm-warning">' . "\r\n";
			echo '<form name="mdjm_log_files" method="POST">' . "\r\n";
			echo _n( 'One', 'Some', count( $warn ), 'mobile-dj-manager' ) . __( ' of your log files exceed the specified limit of ' . mdjm_get_option( 'debug_log_size', 2 ) . ' megabytes', 'mobile-dj-manager' ) 
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
					$success[] = 'The ' . $file . ' log file was auto-purged successfully in accordance with your <a href="' . mdjm_get_admin_page( 'debugging') . '">Debug Settings</a>';
					
				} else	{
					
					$this->log_it( 'ERROR: Could not purge the ' . $file . ' log file', true );
					$error[] = 'The ' . $file . ' log file cound not be purged';
					
				}
				
			}
			
		}
		
		if( isset( $success ) )	{
			mdjm_update_notice( 'updated', implode( '<br />', $success ) );
		}
			
		if( isset( $error ) )	{
			mdjm_update_notice( 'error', implode( '<br />', $success ) );
		}
		
	} // delete_log
	
} // Class MDJM_Debug
