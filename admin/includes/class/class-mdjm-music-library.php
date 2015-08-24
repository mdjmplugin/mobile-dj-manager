<?php
	defined( 'ABSPATH' ) or die( 'Direct access to this page is disabled!!!' );
	
/*
 * The MDJM music library Class manages DJ music libraries from the admin interface
 *
 *
 *
 */
	if( !class_exists( 'MDJM_Music_Lib_Manager' ) )	{
		class MDJM_Music_Lib_Manager	{
			/*
			 * The class constructor
			 *
			 *
			 *
			 */
			function __construct()	{
				global $current_user;
				
				// Enqueue the jQuery file
				wp_enqueue_script( "mdjm-music-library" );
				
				if( isset( $_GET['action'] ) )	{
					// Delete a library
					if( $_GET['action'] == 'delete_lib' && !empty( $_GET['lib'] ) )
						$this->delete_library( $_GET['lib'] );
					
					// Make a library default
					elseif( $_GET['action'] == 'make_default' && !empty( $_GET['lib'] ) && !empty( $_GET['dj'] ) )
						$this->make_default( $_GET['dj'], $_GET['lib'] );
				}
				
				// Get existing libraries
				$this->existing_libraries = $this->get_library_names( $dj = current_user_can( 'administrator' ) ? '' : 
																$current_user->ID );
																
				$this->page_header();
		
				// Check for form submissions and process
				if( isset( $_POST['submit'], $_POST['upload_from'] ) && !empty( $_POST['submit'] ) )	{
					if( $_POST['upload_from'] == 'itunes' )	{
						if( MDJM_DEBUG == true )
							$GLOBALS['mdjm_debug']->log_it( 'iTunes playlist upload initiated', true );
							
						$this->itunes_import();	
					}
				}
				else	{
					$this->page_main();
				}
				
				$this->page_footer();
				
			} // __construct
			
			/*
			 * HTML output for the page header
			 *
			 *
			 *
			 */
			function page_header()	{
				?>
                <style>
				.library_name_field	{
					display: block;	
				}
				.library_upload_options	{
					display: none;	
				}
				</style>
                <?php
				echo '<div class="wrap">' . "\r\n";
				echo '<div id="icon-themes" class="icon32"></div>' . "\r\n";
			} // page_header
			
			function page_main()	{
				global $current_user;
				
				$libraries = $this->get_library( 
										$dj= ( !current_user_can( 'administrator' ) ? $current_user->ID : '' ), 
										'',
										$return='count' );
										
				$count = !empty( $libraries ) ? $libraries : '0';
				
				echo '<h1>Playlist Library Management</h1>' . "\r\n";
				echo '<table class="widefat" width="100%">' . "\r\n"; // Container table
				echo '<tr>';
				echo '<th width="60%" class="alternate"><strong>' . MDJM_COMPANY . ' Music Library</strong> <em>(' . $count. ' Uploads)</em></th>' . "\r\n";
				echo '<th width="40%" class="alternate"><strong>Options</strong></th>' . "\r\n";
				echo '</tr>';
				
				echo '<tr style="vertical-align: top">';
				echo '<td>' . "\r\n";
				echo '<table class="widefat" width="70%">' . "\r\n"; // Main body table
				echo '<thead>' . "\r\n";
				echo '<tr>' . "\r\n";
				echo '<th>' . __( 'Library Name' ) . '</th>' . "\r\n";
				echo '<th>' . __( MDJM_DJ ) . '</th>' . "\r\n";
				echo '<th>' . __( 'Songs' ) . '</th>' . "\r\n";
				echo '<th>&nbsp;</th>' . "\r\n";
				echo '</tr>' . "\r\n";
				echo '</thead>' . "\r\n";
				echo '<tbody>' . "\r\n";
										
				if( empty( $this->existing_libraries ) || $this->existing_libraries == '0' )	{
					echo '<tr>' . "\r\n";
					echo '<td colspan="4" class="form-invalid">' . __( 'No music libraries have been uploaded yet' ) . '</th>' . "\r\n";
					echo '</tr>' . "\r\n";	
				}
				else	{ // We have results
					$i = 0;
					foreach( $this->existing_libraries as $existing_library )	{
						$library_entries = $this->get_library( 
															$dj = !current_user_can( 'administrator' ) ? $current_user->ID 
															: '', 
															$lib = $existing_library->library_slug );
															
						//if( $library_entries )	{
							echo '<tr' . ( $i == 0 ? ' class="alternate"' : '' ) . '>' . "\r\n";
							echo '<td>' . $existing_library->library . '</td>' . "\r\n";
							echo '<td>' . get_userdata( $existing_library->dj )->display_name. '</td>' . "\r\n";
							echo '<td>' . $this->get_library( '', $existing_library->library_slug ) . '</td>' . "\r\n";
							echo '<td>';
								if( $this->is_default_for_dj( $existing_library->dj, $existing_library->library_slug ) )	{
									echo '<span style="color: #F90; font-weight: bold;">Assigned as Default for ' . MDJM_DJ . '<span>';
								}
								else	{
									echo '<a href="' . mdjm_get_admin_page( 'music_library' ) . '&action=make_default&lib=' . $existing_library->library_slug . 
										'&dj=' . $existing_library->dj . '" class="button button-primary button-small">Make Default</a>';
									echo '&nbsp;&nbsp;&nbsp;';
									echo '<a href="' . mdjm_get_admin_page( 'music_library' ) . '&action=delete_lib&lib=' . $existing_library->library_slug . 
										'" class="button button-secondary button-small">Delete</a>';
								}
							echo '</td>' . "\r\n";
							echo '</tr>' . "\r\n";
						//}
					}
					$i++;
					if( $i == 2 )
						$i = 0;
				}
				
				echo '</tbody>' . "\r\n";
				echo '<tfoot>' . "\r\n";
				echo '<tr>' . "\r\n";
				echo '<th>' . __( 'Library Name' ) . '</th>' . "\r\n";
				echo '<th>' . __( MDJM_DJ ) . '</th>' . "\r\n";
				echo '<th>' . __( 'Songs' ) . '</th>' . "\r\n";
				echo '<th>&nbsp;</th>' . "\r\n";
				echo '</tr>' . "\r\n";
				echo '</tfoot>' . "\r\n";
				
				echo '</table>' . "\r\n";  // Main body table
				echo '</td>' . "\r\n";
				echo '<td>' . "\r\n";
				echo '<table class="widefat" width="30%">' . "\r\n"; // Admin table
				echo '<form name="library_upload" id="library_upload" method="post" action="' . mdjm_get_admin_page( 'music_library' ) . 
					'" enctype="multipart/form-data">' . "\r\n";
				echo '<tr class="alternate">' . "\r\n";
				echo '<td><label for="upload_from" class="mdjm-label">Upload Library From:</label><br />' . "\r\n";
					echo '<select name="upload_from" id="upload_from">' . "\r\n";
						echo '<option value="0">--- Select ---</option>' . "\r\n";
						echo '<option value="itunes">iTunes Playlist (.txt)</option>' . "\r\n";
					echo '</select></td>' . "\r\n";
				echo '</tr>' . "\r\n";
				
				echo '<tr class="library_upload_options alternate" id="library_upload_options">' . "\r\n";
				echo '<td>' . "\r\n";
				
				
				// Upload Options
				$this->library_upload_options();
				
				echo '</td>' . "\r\n";
				echo '</tr>' . "\r\n";
				echo '</form>' . "\r\n";
				echo '</table>' . "\r\n";  // Admin table
				echo '</td>' . "\r\n";
								
				echo '</tr>' . "\r\n";
				echo '</table>' . "\r\n"; // Container table
			} // page_main
			
			/*
			 * HTML output for the page footer
			 *
			 *
			 *
			 */
			function page_footer()	{
				echo '</div>' . "\r\n"; // wrap
			} // page_footer
			
			/*
			 * Display option properties for library upload
			 *
			 *
			 *
			 *
			 */
			function library_upload_options()	{
				global $current_user;
				
				echo '<table class="widefat" width="100%">' . "\r\n";
				echo '<tr class="alternate">' . "\r\n";
				echo '<td><select name="upload_to" id="upload_to">' .  "\r\n";
				echo '<option value="add_new" selected="selected">New Library</option>' . "\r\n";
				
				if( $this->existing_libraries )	{
					echo '<optgroup label="Append to Library">' . "\r\n";
					foreach( $this->existing_libraries as $existing_library )	{
						echo '<option value="' . $existing_library->library_slug . '">' . 
							$existing_library->library . '</option>' . "\r\n";
					}
					echo '</optgroup>' . "\r\n";
				}
				echo '</td>' . "\r\n";
				echo '</tr>' . "\r\n";
				echo '<tr class="library_name_field alternate" id="library_name_field">' . "\r\n";
				echo '<td><label for="library_name" class="mdjm-label">' . __( 'Library Name' ) . ':</label><br />' . "\r\n";
				echo '<input type="text" name="library_name" id="library_name" /><br />' . "\r\n";
				
				// If multiple DJ's, only admins can select a DJ
				if( current_user_can( 'administrator' ) && MDJM_MULTI == true )	{
					echo '<label for="dj_library" class="mdjm-label">' . __( 'DJ' ) . ':</label><br />' . "\r\n";
					echo '<select name="dj_library" id="dj_library">' . "\r\n";
					
					foreach ( mdjm_get_djs() as $dj )	{
						echo '<option value="' . $dj->ID . '" ' . 
						selected( $dj->ID, $current_user->ID, false ) .
						'>' . $dj->display_name . '</option>' . "\r\n"; 	
					}
					
					echo '<select>' . "\r\n";
				}
				// Otherwise just use current user
				else	{
					echo '<input type="hidden" name="dj_library" id="dj_library" value="' . $current_user->ID . '" />';	
				}
				
				echo '</td>' . "\r\n";
				echo '</tr>' . "\r\n";
				echo '<tr class="alternate">' . "\r\n";
				echo '<td><label for="playlist_file" class="mdjm-label">' . __( 'File to Import' ) . ':</label><br />' . "\r\n";
				echo '<input type="file" name="playlist_file" id="playlist_file">' . "\r\n";
				echo '</td>' . "\r\n";
				echo '</tr>' . "\r\n";
				echo '<tr class="alternate">' . "\r\n";
				echo '<td>';
				submit_button( 'Upload Playlist', 'primary small', 'submit', false );
				echo '</td>' . "\r\n";
				echo '</tr>' . "\r\n";
				echo '</table>' . "\r\n";
			} // library_upload_options
			
			/*
			 * Import songs from iTunes playlist export file (txt)
			 *
			 *
			 *
			 */
			function itunes_import()	{
				global $current_user, $wpdb;
				
				// Validation checks
				
				if( empty( $_FILES ) )	{
					
				}
				// Check the file type
				if( $_FILES['playlist_file']['type'] != "text/plain" )	{
					if( MDJM_DEBUG == true )
						$GLOBALS['mdjm_debug']->log_it( '    ERROR: -- The file is not a valid text file. ' . $_FILES['playlist_file']['type'], false );
						
					mdjm_update_notice( 'error', 'The file you selected does not appear to be a valid text file. iTunes playlist files must be text files ' . 
						'with the <code>.txt</code> extension' );
						
					return $this->page_main();
					
				}
				
				// File size
				/*if( $_FILES['playlist_file']['size'] > $this->library_settings )	{
					if( MDJM_DEBUG == true )
						$GLOBALS['mdjm_debug']->log_it( '    ERROR: -- The file exceeds the maximum allowed size. ' . $_FILES['playlist_file']['size'], false );
						
					mdjm_update_notice( 'error', 'The file you selected exceeds the maximum allowed size.' );
						
					return $this->page_main();
				}*/
				
				
				
				// File properties
				$dir = MDJM_PLUGIN_DIR . '/admin/temp/';
				$name = basename( $_FILES['playlist_file']['name'] );
				
				$target = $dir . $name;
				
				// Upload failed
				if( !move_uploaded_file( $_FILES['playlist_file']['tmp_name'], $target ) )	{
					if( MDJM_DEBUG == true )
						$GLOBALS['mdjm_debug']->log_it( '    ERROR: -- The file could not be uploaded', false );
						
					mdjm_update_notice( 'error', 'The file you selected could not be uploaded' );
						
					return $this->page_main();
				}
				// Upload succeeded
				else	{
					$library_name = $_POST['upload_to'] == 'add_new' ? sanitize_text_field( $_POST['library_name'] ) : $this->get_library_name( $_POST['upload_to'] );
					
					// Set the slug
					$library_slug = $_POST['upload_to'] == 'add_new' ? sanitize_title( $_POST['library_name'] ) : $_POST['upload_to'];
					
					// Access the new file
					$file = fopen( $target, 'r' );
					
					// Read each line into the data array
					while ( !feof($file) )	{
						$line = fgets( $file, 2048 );
					
						$delimiter = "\t";
						$data[] = str_getcsv( $line, $delimiter );
					}
					
					$ratings['0'] = '0';
					$ratings['20'] = '1';
					$ratings['40'] = '2';
					$ratings['60'] = '3';
					$ratings['80'] = '4';
					$ratings['100'] = '5';
					
					$x = 0;
					for( $i = 1; $i <= count( $data ); $i++ )	{
						if( empty( $data[$i][0] ) || empty( $data[$i][1] ) )
							continue;
						
						$library[$x]['id'] = '';
						$library[$x]['song'] = sanitize_text_field( $data[$i][0] );
						$library[$x]['artist'] = sanitize_text_field( $data[$i][1] );
						$library[$x]['library'] = $library_name;
						$library[$x]['library_slug'] = $library_slug;
						
						if( !empty( $data[$i][3] ) )
							$library[$x]['album'] = sanitize_text_field( $data[$i][3] );
						
						if( !empty( $data[$i][5] ) )
							$library[$x]['genre'] = sanitize_text_field( $data[$i][5] );
							
						if( !empty( $data[$i][12] ) )
							$library[$x]['year'] = sanitize_text_field( $data[$i][12] );
							
						if( !empty( $data[$i][20] ) )
							$library[$x]['comments'] = sanitize_text_field( $data[$i][20] );
							
						if( !empty( $data[$i][25] ) )
							$library[$x]['rating'] = sanitize_text_field( $data[$i][25] );
							
						$library[$x]['dj'] = ( !empty( $_POST['dj_library'] ) ? $_POST['dj_library'] : $current_user->ID );
						
						$library[$x]['date_added'] = date( 'Y-m-d' );
						
						if( !empty( $_POST['lib_name'] ) )
							$library[$x]['library'] = $_POST['lib_name'];
						$x++;
					}

				}				
				
				// Close the file				
				fclose( $file );
				// Remove the file
				unlink( $target );
				
				foreach( $library as $record )	{
					$upload = $wpdb->insert(
											MDJM_MUSIC_LIBRARY_TABLE,
											$record
											);
				}
				
				mdjm_update_notice( 'updated',
									'The library has been updated successfully' );
				
				// Refresh existing libraries
				$this->existing_libraries = $this->get_library_names( $dj = current_user_can( 'administrator' ) ? '' : 
																$current_user->ID );
				
				$this->page_main();

			} // itunes_import
			
			/*
			 * Delete specified library
			 *
			 * @param		str		$lib	The slug of the library to delete
			 *
			 */
			function delete_library( $lib )	{
				global $wpdb;
				
				if( empty( $lib ) )	{
					if( MDJM_DEBUG == true )
						$GLOBALS['mdjm_debug']->log_it( 'No library named for deletion', true );
						
					mdjm_update_notice( 'error', 'No library was selected for deletion' );
						
					return $this->page_main();	
				}
				
				$delete = $wpdb->delete( MDJM_MUSIC_LIBRARY_TABLE, 
										array( 'library_slug' => $lib ) );
										
				if( !empty( $delete ) )
					mdjm_update_notice( 'updated',
										'The library has been deleted. (' . $delete . _n( ' song', ' songs', $delete ) . ')' );
										
				else
					mdjm_update_notice( 'error',
										'Could not delete selected library' );
														
			} // delete_library
			
			/*
			 * Check whether or not a music library exists
			 *
			 * @param		int			$dj		The ID of the DJ to check, default to all DJ's
			 *				str			$lib	The slug of the library to query
			 *				str			$return	The type of return required
			 *									'count' (default) return the count of records as an int
			 *									'details' return the library details as an array
			 *
			 */
			function get_library( $dj='', $lib='', $return='' )	{
				(int)$dj = !empty( $dj ) ? $dj : '0';
				$lib = !empty( $lib ) ? $lib : '';
				$return = !empty( $return ) ? $return : 'count';
				
				if( $return == 'count' )
					return $this->count_records( $dj, $lib );
					
				if( $return == 'details' )
					return $this->get_library_info( $dj, $lib );
				
			} // get_library
			
			/*
			 * Return all details of the given library
			 *
			 * @param	str			$lib	Required: Slug of the library
			 * @return 	arr					Library information
			 */
			function get_library_details( $lib )	{
				global $wpdb;
				
				$library['details'] = $wpdb->get_results( "SELECT * FROM '" . MDJM_MUSIC_LIBRARY_TABLE . "' WHERE 
					`library_slug` = '" . $lib . "' ORDER BY `library`" );
					
				
				
			} // get_library_details
			
			/*
			 * Retrieve all unique libraries
			 *
			 * @param	int		$dj		Optional: The user ID of the DJ
			 * @result	objarr	$result	Object array of results or false if none
			 */
			function get_library_names( $dj='' )	{
				global $wpdb;
				
				$query = "SELECT `library` as `library`, `library_slug` as `library_slug`, `dj` as `dj` FROM `" . 
					MDJM_MUSIC_LIBRARY_TABLE . "`";
					
				if( !empty( $dj ) && $dj != '0' )
					$query .= " WHERE `dj` = '" . $dj . "'";
				
				$query .= " GROUP BY `library` ORDER BY `library`";
				$result = $wpdb->get_results( $query );
				
				return $result;
			}
			
			/*
			 * Return library info
			 *
			 * @param		int			$dj		The ID of the DJ to check, default to all DJ's
			 *				str			$lib	The slug of the library to retrieve
			 *				str			$orderbyThe name of the colunm to order by
			 *				str			$order	The order type ASC|DESC
			 * @return		obj arr		
			 */
			function get_library_info( $dj='', $lib='', $orderby='song', $order='ASC' )	{
				global $wpdb;
				
				(int)$dj = !empty( $dj ) ? $dj : '0';
				$lib = !empty( $lib ) ? $lib : '';
				
				$query = "SELECT * FROM " . MDJM_MUSIC_LIBRARY_TABLE;
				
				if( !empty( $dj ) || !empty( $lib ) )
					$query .= " WHERE ";
				
				// DJ query
				if( !empty( $dj ) )
					$query .= "`dj` = '" . $dj . "'"; 
					
				if( !empty( $dj ) && !empty( $lib ) )
					$query .= " AND ";
					
				// Library query
				if( !empty( $lib ) )
					$query .= "`library_slug` = '" . $lib . "'"; 
				
				return $wpdb->get_results( $query . " order by `" . $orderby . "` " . $order ); 
				
			} // get_library_info
			
			/*
			 * Return library nice name
			 *
			 * @param		str			$lib	The slug of the library to retrieve
			 *				
			 * @return		str			The nice name of the library		
			 */
			function get_library_name( $lib )	{
				global $wpdb;
				
				$lib = !empty( $lib ) ? $lib : '';
				
				$query = $wpdb->get_row( "SELECT * FROM " . MDJM_MUSIC_LIBRARY_TABLE . " WHERE `library_slug` = '" . $lib . "'" ); 
				
				return $query->library; 
				
			} // get_library_name
			
			/*
			 * Count the records in the library
			 *
			 * @param		int			$dj		The ID of the DJ to check. If not provided, count all
			 *				str			$lib	The slug of the library to query
			 * @return		int|bool			Number of records, or false if none
			 */
			function count_records( $dj='', $lib='' )	{
				global $wpdb;
				
				$query = "SELECT COUNT(*) FROM `" . MDJM_MUSIC_LIBRARY_TABLE . "`";
				
				if( !empty( $dj ) || $dj != '0' || !empty( $lib ) )
					$query .= " WHERE ";
				
				if( !empty( $dj ) && $dj != '0' )
					$query .= "`dj` = '" . (int)$dj . "'";
					
				if( !empty( $lib ) && ( !empty( $dj ) || $dj != '0' ) )
					$query .= " AND ";
					
				if( !empty( $lib ) )
					$query .= "`library_slug` = '" . $lib . "'";
					
				$count = $wpdb->get_var( $query );
				
				return $count;
				
			} // count_records
			
			/*
			 * Check if the given library is default for the DJ
			 *
			 * @param		str		$dj		The WP UserID of the DJ to check
			 *				str		$lib	The slug of the library to compare
			 * @return		bool			true if default, otherwise false
			 *
			 */
			function is_default_for_dj( $dj, $lib )	{
				if( empty( $dj ) )	{
					if( MDJM_DEBUG == true )
						$GLOBALS['mdjm_debug']->log_it( 'WARNING: No DJ specified in ' . __METHOD__ );
					return false;	
				}
				if( empty( $lib ) )	{
					if( MDJM_DEBUG == true )
						$GLOBALS['mdjm_debug']->log_it( 'WARNING: No Library specified in ' . __METHOD__ );
					return false;	
				}
				
				$default = get_user_meta( $dj, 'mdjm_default_library', true );
				
				return ( empty( $default ) || $default != $lib ? false : true );
				
			} // is_default_for_dj
			
			/*
			 * Make the given library default for the DJ
			 *
			 * @param		str		$dj		The WP UserID of the DJ
			 *				str		$lib	The slug of the library to make default
			 * @return		
			 *
			 */
			function make_default( $dj, $lib )	{
				if( empty( $dj ) )	{
					if( MDJM_DEBUG == true )
						$GLOBALS['mdjm_debug']->log_it( 'WARNING: No DJ specified in ' . __METHOD__ );
					return false;	
				}
				if( empty( $lib ) )	{
					if( MDJM_DEBUG == true )
						$GLOBALS['mdjm_debug']->log_it( 'WARNING: No Library specified in ' . __METHOD__ );
					return false;	
				}
				
				update_user_meta( $dj, 'mdjm_default_library', $lib );
				
			} // make_default
			
		} // class MDJM_Music_Lib_Manager
	} // if( !class_exists( 'MDJM_Music_Lib_Manager' ) )
	
	// Security Check
	if( !current_user_can( 'administrator' ) && !dj_can( 'upload_music' ) )	{
		if( MDJM_DEBUG == true )
			$GLOBALS['mdjm_debug']->log_it( 'SECURITY ALERT: Non admin accessing music library when library uploads are disabled', true );
		wp_die( 'SECURITY: You do not have permission to be here' );
	}
	
	// Instantiate
	if( class_exists( 'MDJM_Music_Lib_Manager' ) )
		$playlist_lib = new MDJM_Music_Lib_Manager();