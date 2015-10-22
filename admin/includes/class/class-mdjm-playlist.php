<?php
/**
 * class-mdjm-playlist.php
 * 08/03/2015
 * @since 1.1.1
 * The class to display & manage the Playlists
 *
 * @version 1.0
 */

	class MDJM_Playlist_Table extends WP_List_Table	{
		function __construct() {
			parent::__construct( array(
				'singular'=> 'mdjm_playlist',
				'plural' => 'mdjm_playlists',
				'ajax'   => false
			) );
			$this->mdjm_options = get_option( MDJM_SETTINGS_KEY );
		} // __construct
		
		/**
		 * no_items
		 * Text displayed when no playlists have been found
		 * @since 1.1
		 * 
		 */
		 function no_items()	{
			_e( 'No Playlist entries exist yet.' );
		 } // no_items
		 
		 /**
		 * get_columns
		 * The columns to display
		 * @since 1.1
		 * 
		 */
		function get_columns() {
			$columns = array(
						'id'			=> __( 'ID' ),
						'event_id'	  => __( 'Event ID' ),
						'artist'		=> __( 'Artist' ),
						'song'		  => __( 'Song' ),
						'play_when'	 => __( 'Category' ),
						'info'		  => __( 'Notes' ),
						'added_by'	  => __( 'Added By' ),
						'date_added'	=> __( 'Date Added' ),
						'date_to_mdjm'  => __( 'Date Uploaded' ),
						);
			return $columns;
		} // get_columns
		
		/**
		 * get_sortable_columns
		 * The columns we can sort by
		 * @since 1.1
		 * 
		 */
		public function get_sortable_columns() {
			$sortable = array(
						'artist'		=> array( 'artist', true ),
						'song'		  => array( 'song', false ),
						'play_when'	 => array( 'play_when', false ),
						'added_by'	  => array( 'added_by', false ),
						'date_added'	=> array( 'date_added', false ),
			);
			return $sortable;
		} // get_sortable_columns
		
		/**
		 * get_search_columns
		 * The columns we can search by
		 * @since 1.1
		 * 
		 */
		public function get_search_columns() {
			$searchable = array(
				'event_id',
				'artist',
				'song',
				'event_dj',
			);
			return $searchable;
		} // get_search_columns
		
		/**
		 * query_builder
		 * Set the DB queries
		 * @since 1.1.1
		 * 
		 */
		function query_builder( $event )	{
			include( MDJM_PLUGIN_DIR . '/includes/config.inc.php' );
			/* -- Prepare the query -- */
			$query = "SELECT * FROM `" . $db_tbl['playlists'] . "` WHERE `event_id` = '" . $event . "'";
						
			return $query;
		} // query_builder
		
		function playlist_header()	{
			global $wpdb;
			
			if( empty( $_GET['event_id'] ) )
				return;
			
			include( MDJM_PLUGIN_DIR . '/includes/config.inc.php' );
			
			$eventinfo = $wpdb->get_row( "SELECT * FROM `" . $db_tbl['events'] . "` WHERE `event_id` = '" . $_GET['event_id'] . "'" );
			$clientinfo = get_userdata( $eventinfo->user_id );
			?>
            <table class="widefat">
            <tr class="alternate">
            <th colspan="2"><strong>Event Details</strong></th>
            </tr>
            <tr>
            <td><strong>Date</strong>:</td>
            <td><?php echo date( "l, jS F Y", strtotime( $eventinfo->event_date ) ); ?></td>
            </tr>
            <tr>
            <td><strong>Type</strong>:</td>
            <td><?php echo stripslashes( $eventinfo->event_type ); ?></td>
            </tr>
            <tr>
            <td><strong>Client</strong>:</td>
            <td><?php echo stripslashes( $clientinfo->display_name ); ?></td>
            </tr>
            <tr>
            <td><strong>Songs</strong>:</td>
            <td><?php echo count( $this->items ); ?></td>
            </tr>
            </table>
            <?php	
		}
		
		/**
		 * send_to_email
		 * Email the Playlist
		 * @since 1.1
		 * 
		 */
		function send_to_email( $post_data, $get_data )	{
			global $wpdb, $current_user;
			if( !isset( $get_data['event_id'] ) || empty( $get_data['event_id'] ) )	{
				return;	
			}
			else	{
				include ( MDJM_PLUGIN_DIR . '/includes/config.inc.php' );
				$email_query = 'SELECT * FROM `'.$db_tbl['playlists'].'` WHERE `event_id` = ' . $get_data['event_id'] . ' ORDER BY `' . $post_data['order_pl_by'] . '` ASC';
				$email_result = $wpdb->get_results( $email_query );
				$pl_ttl = $wpdb->num_rows;
				
				if( !isset( $post_data['repeat_headers'] ) || empty( $post_data['repeat_headers'] ) || $post_data['repeat_headers'] == 0 )	{
					$repeat = 0;
				}
				else	{
					$repeat = $post_data['repeat_headers'];
				}
				
				$i = 0;
				
				$eventinfo = $wpdb->get_row('SELECT * FROM ' . $db_tbl['events'] . ' WHERE `event_id` = ' . $get_data['event_id']);
				$client = get_userdata( $eventinfo->user_id );
				get_currentuserinfo();
				
				$email_body = '<html>' . "\n" . '<body>' . "\n";
				
				$email_body .= '<p>Hey ' . $current_user->first_name . ',</p>' . "\n";
				$email_body .= '<p>Here is the playlist you requested.</p>' . "\n";
				
				$email_body .= '<p>Client Name: ' . $client->first_name . ' ' . $client->last_name . '<br />' . "\n";
				$email_body .= 'Event Date: ' . date( "l, jS F Y", strtotime( $eventinfo->event_date ) ) . '<br />' . "\n";
				$email_body .= 'Event Type: ' . $eventinfo->event_type . '<br />' . "\n";
				$email_body .= 'No. Songs in Playlist: ' . $pl_ttl . '<br /></p>' . "\n";
				$email_body .= '<hr />' . "\n";
				
				$email_body .= '<table width="100%" border="0" cellpadding="0" cellspacing="0">' . "\n";
				$email_body .= '<tr>' . "\n";
				$email_body .= '<th>Song Name</th>' . "\n";
				$email_body .= '<th>Artist</th>' . "\n";
				$email_body .= '<th>When to Play</th>' . "\n";
				$email_body .= '<th>Information</th>' . "\n";
				$email_body .= '<th>Added By</th>' . "\n";
				$email_body .= '</tr>' . "\n";
				
				foreach( $email_result as $pl_info )	{
					if( $repeat > 0 && $i == $repeat )	{
						$email_body .= '<tr>' . "\n";
						$email_body .= '<td colspan="5">&nbsp;</td>' . "\n";
						$email_body .= '</tr>' . "\n";
						$email_body .= '<tr>' . "\n";
						$email_body .= '<th>Song Name</th>' . "\n";
						$email_body .= '<th>Artist</th>' . "\n";
						$email_body .= '<th>When to Play</th>' . "\n";
						$email_body .= '<th>Information</th>' . "\n";
						$email_body .= '<th>Added By</th>' . "\n";
						$email_body .= '</tr>' . "\n";
						
						$i = 0;
					}
					
					$email_body .= '<tr>' . "\n";
					$email_body .= '<td>' . stripslashes( $pl_info->song ) . '</td>' . "\n";
					$email_body .= '<td>' . stripslashes( $pl_info->artist ) . '</td>' . "\n";
					$email_body .= '<td>' . stripslashes( $pl_info->play_when ) . '</td>' . "\n";
					$email_body .= '<td>' . stripslashes( $pl_info->info ) . '</td>' . "\n";
					$email_body .= '<td>' . stripslashes( $pl_info->added_by ) . '</td>' . "\n";
					$email_body .= '</tr>' . "\n";
					
					$i++;
				}
				
				$email_body .= '</table>' . "\n";
				$email_body .= '<p>Regards</p>' . "\n";
				$email_body .= '<p>' . WPMDJM_CO_NAME . '</p>' . "\n";
				$email_body .= '<p>&nbsp;</p>' . "\n";
				$email_body .= '<p align="center" style="font-size: 9px">Powered by <a style="color:#F90" href="http://www.mydjplanner.co.uk" target="_blank">' . MDJM_NAME . '</a> version ' . MDJM_VERSION_NUM . '</p>' . "\n";
				$email_body .= '</body>' . "\n" . '</html>' . "\n";
				
				$headers = 'MIME-Version: 1.0' . "\r\n";
				$headers .= 'Content-type: text/html; charset=UTF-8' . "\r\n";
				$headers .= 'From: ' . $this->mdjm_options['company_name'] . ' <' . $this->mdjm_options['system_email'] . '>' . "\r\n";
				
				if( wp_mail( $current_user->user_email, 'Event Playlist for ' . date( "l, jS F Y", strtotime( $eventinfo->event_date ) ), $email_body, $headers ) )	{
					mdjm_update_notice( 'updated', 'Playlist successfully emailed to <a href="mailto:' . $current_user->user_email . '">' . $current_user->display_name . '</a>' );	
				}
				else	{
					mdjm_update_notice( 'error', 'Unable to email playlist' );	
				}
			}
		} // send_to_email
		
		/**
		 * prepare_items
		 * Set the table up with needed params, pagination etc
		 * @since 1.1
		 * 
		 */
		function prepare_items( $search = NULL ) {
			global $wpdb, $_wp_column_headers;
			
			$screen = get_current_screen();
			
			$event = !empty( $_GET['event_id'] ) ? $_GET['event_id'] : '';
			
			if( empty( $event ) )
				return;
			
			$query = $this->query_builder( $event );
			
			/* -- Ordering parameters -- */
			//Parameters that are going to be used to order the result
			$orderby = !empty( $_GET['orderby'] ) ? mysql_real_escape_string( $_GET['orderby'] ) : 'artist';
			$order = !empty( $_GET['order'] ) ? mysql_real_escape_string( $_GET['order'] ) : 'ASC';
			
			if( !empty( $orderby ) & !empty( $order ) )	{
			   $query .= " ORDER BY " . $orderby . " " . $order;
			}
			
			/* -- Pagination parameters -- */
			//Number of elements in your table?
			$totalitems = $wpdb->query( $query ); //return the total number of affected rows
			
			//How many to display per page?
			$perpage = $this->mdjm_options['items_per_page'];
			
			//Which page is this?
			$paged = !empty( $_GET['paged'] ) ? mysql_real_escape_string( $_GET['paged'] ) : '';
			
			//Page Number
			if( empty( $paged ) || !is_numeric( $paged ) || $paged <= 0 )	{
				$paged = 1;
			}
				
			//How many pages do we have in total?
			$totalpages = ceil( $totalitems/$perpage );
			
			//adjust the query to take pagination into account
			if( !empty( $paged ) && !empty( $perpage ) )	{
				$offset = ( $paged-1 )*$perpage;
				$query .= ' LIMIT ' . (int)$offset . ',' . (int)$perpage;
			}
	
			/* -- Register the pagination -- */
			$this->set_pagination_args( array(
				"total_items" => $totalitems,
				"total_pages" => $totalpages,
				"per_page" => $perpage,
			) );
			
			/* -- Register the Columns -- */
			$columns = $this->get_columns();
			
			$hidden = array( 
						'id',
						'event_id',
						'date_to_mdjm',
						);
						
			$sortable = $this->get_sortable_columns();
			$this->_column_headers = array( $columns, $hidden, $sortable );
			
			/* -- Fetch the items -- */
			if( $search != NULL )	{ // Search being performed
				$searchable_columns = $this->get_search_columns();
				$search_query = "SELECT * FROM `" . $db_tbl['playlist'] . "` WHERE";
				$i = 1;
				/* -- Loop through searchable columns to build query -- */
				foreach( $searchable_columns as $column_to_search )	{
					if( $i != 1 && $i != count( $searchable_columns ) )	{
						$search_query .= " OR";	
					}
					$search_query .= " `event_id` LIKE '%%%s%%'";
					
					$i++;
				}
				
				$search_str = '';
				foreach( $searchable_columns as $search_str )	{
					$search_str .= ", " . $search;
				}
								
				$this->items = $wpdb->get_results( $wpdb->prepare( $search_query, $search_str ) );
			}
			else	{ // No search
				$this->items = $wpdb->get_results( $query );
			}
		} // prepare_items
		
		/**
		 * display_rows
		 * Display each record row in the table
		 * @since 1.1
		 * 
		 */
		function display_rows() {
			/* -- Get the records registered -- */
			$entries = $this->items;
			
			/* -- Get the columns registered in the get_columns and get_sortable_columns methods -- */
			list( $columns, $hidden ) = $this->get_column_info();
			
			$i = 0;
			
		   /* -- Loop for each record -- */
			if( !empty( $entries ) )	{
				foreach( $entries as $entry )	{
					/* -- Open the table row -- */
					echo '<tr id="record_' . $entry->id . '"';
					if( $i == 0 )	{
						echo ' class="alternate"';
					}
					echo '>' . "\n";
					
					/* -- The columns -- */
					foreach ( $columns as $column_name => $column_display_name ) {
						/* -- Style attributes for each column -- */
						$class = 'class="' . $column_name . 'column-' . $column_name . '"';
						$style = "";
						if ( in_array( $column_name, $hidden ) ) $style = ' style="display:none;"';
							$attributes = $class . $style;
						
						/* -- Display the cell -- */
						switch ( $column_name ) {
							case "id":  
								echo '<td ' . $attributes . '>' . $entry->id . '</td>' . "\n";
								break;
							case "event_id":  
								echo '<td ' . $attributes . '>' . $entry->event_id . '</td>' . "\n";
								break;
							case "artist":  
								echo '<td ' . $attributes . '>' . stripslashes( $entry->artist ) . '</td>' . "\n";
								break;
							case "song":  
								echo '<td ' . $attributes . '>' . stripslashes( $entry->song ) . '</td>' . "\n";
								break;
							case "play_when":  
								echo '<td ' . $attributes . '>' . stripslashes( $entry->play_when ) . '</td>' . "\n";
								break;
							case "info":  
								echo '<td ' . $attributes . '>' . stripslashes( $entry->info ) . '</td>' . "\n";
								break;
							case "added_by":  
								echo '<td ' . $attributes . '>' . stripslashes( $entry->added_by ) . '</td>' . "\n";
								break;
							case "date_added":  
								echo '<td ' . $attributes . '>' . date( $this->mdjm_options['short_date_format'], strtotime( $entry->date_added ) ) . '</td>' . "\n";
								break;
							case "date_added":  
								echo '<td ' . $attributes . '>' . date( $this->mdjm_options['short_date_format'], strtotime( $entry->date_to_mdjm ) ) . '</td>' . "\n";
								break;
						} // switch
					}
					/* -- Close the row -- */
					echo'</tr>' . "\n";
					$i++;
					if( $i == 2 )
						$i = 0;
				}
			}
		} // display_rows
	} // class MDJM_Playlist_Table
		
?>