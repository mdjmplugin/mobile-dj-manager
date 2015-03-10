<?php
	class MDJM_PlayList_Table extends WP_List_Table	{
		private function get_playlist()	{
			global $wpdb, $query, $mdjm_options;
			include ( WPMDJM_PLUGIN_DIR . '/includes/config.inc.php' );
			// Build the data query
			$query = 'SELECT * FROM `'.$db_tbl['playlists'].'` WHERE `event_id` = ' . $_GET['event_id'];
			
			if( isset( $_GET['orderby'] ) ) $orderby = $_GET['orderby'];
			else $orderby = 'date_added';
			
			if( isset( $_GET['order'] ) ) $order = $_GET['order'];
			else $order = 'ASC';
			
			$query .= ' ORDER BY `' . $orderby . '` ' . $order;
			$playlistinfo = $wpdb->get_results( $query );
			$pl_ttl = $wpdb->num_rows;
			
			$playlist_data = array();
			foreach( $playlistinfo as $playlist )	{
				$playlist_data[] = array(
									'id' => $playlist->id,
									'event_id' => $playlist->event_id,
									'artist' => stripslashes( $playlist->artist ),
									'song' => stripslashes( $playlist->song ),
									'play_when' => stripslashes( $playlist->play_when ),
									'info' => stripslashes( $playlist->info ),
									'by' => stripslashes( $playlist->added_by ),
									'date_added' => date( $mdjm_options['short_date_format'], strtotime( $playlist->date_added ) )
								);
			}
			$eventinfo = $wpdb->get_row('SELECT * FROM ' . $db_tbl['events'] . ' WHERE `event_id` = ' . $_GET['event_id']);
			$client = get_userdata( $eventinfo->user_id );
			?>
			<div class="wrap">
			<h4>Event Date: <?php echo date( "l, jS F Y",strtotime( $eventinfo->event_date ) ); ?><br />
            Event Type: <?php echo $eventinfo->event_type; ?><br />
			Client Name: <?php echo $client->first_name . ' ' . $client->last_name ?><br />
			No. Songs in Playlist: <?php echo $pl_ttl; ?></h4>
            <form name="email_pl" id="email_pl" action="" method="post">
            <?php
            submit_button( 'Email me this List', 'primary small', 'email_pl', false );
			?>
            ordered by <select name="order_pl_by" id="order_pl_by">
            <option value="date_added" selected="selected">Date Added</option>
            <option value="artist">Artist Name</option>
            <option value="song">Song Name</option>
            <option value="play_when">When to Play</option>
            </select> and repeating headers after <input type="text" name="repeat_headers" id="repeat_headers" class="small-text" value="0" /> rows <code>Enter 0 for no repeat of headers</code>
            </form>
            <br />
			<form name="print_pl" id="print_pl" action="<?php f_mdjm_admin_page( 'events' ); ?>" method="post" target="_blank">
            <input type="hidden" name="event_id" id="event_id" value="<?php echo $eventinfo->event_id; ?>" />
            <?php
            submit_button( 'Print this List', 'primary small', 'print_pl', false );
			?>
            ordered by <select name="order_pl_by" id="order_pl_by">
            <option value="date_added" selected="selected">Date Added</option>
            <option value="artist">Artist Name</option>
            <option value="song">Song Name</option>
            <option value="play_when">When to Play</option>
            </select> and repeating headers after <input type="text" name="repeat_headers" id="repeat_headers" class="small-text" value="20" /> rows <code>Enter 0 for no repeat of headers</code>
            </form>
			</div>
            <?php
			return $playlist_data;
		} // get_playlist
		
		function extra_tablenav( $which )	{ // Determine what is to be shown before and after the table
			global $wpdb, $query;
			if( isset( $which ) && $which == "top" ){ // Before table
				?>
                
                <?php
			}
			if( isset( $which ) && $which == "bottom" )	{ // After table
			
			}
		} // extra_tablenav
		
		function admin_header() {
			$page = ( isset($_GET['page'] ) ) ? esc_attr( $_GET['page'] ) : false;
			if( 'mdjm-events' != $page )
			return; 
			
			echo '<style type="text/css">';
			echo '.wp-list-table .column-artist { width: 20%; }';
			echo '.wp-list-table .column-song { width: 20%; }';
			echo '.wp-list-table .column-play_when { width: 20%; }';
			echo '.wp-list-table .column-info { width: 30%; }';
			echo '.wp-list-table .column-by { width: 10%; }';
			echo '.wp-list-table .column-date_added { width: 10%; }';
			echo '</style>';
		} // admin_header
		
		function no_items() {
			_e( 'There are currently no songs in the playlist for this event.' );
		}
		
		function __construct()	{
			global $status, $page;
	
			parent::__construct( array(
				'singular'  => __( 'song', 'mdjmplaylisttable' ),     //singular name of the listed records
				'plural'    => __( 'songs', 'mdjmplaylisttable' ),   //plural name of the listed records
				'ajax'      => false        //does this table support ajax?
			) );
			add_action( 'admin_head', array( &$this, 'admin_header' ) ); // Call the function to style the table
		} // __construct
	
		function column_default( $item, $column_name )	{
			switch( $column_name )	{ 
				case 'song':
				case 'artist':
				case 'play_when':
				case 'info':
				case 'by':
				case 'date_added':
					return $item[ $column_name ];
				default:
					return print_r( $item, true ) ; // Show the whole array for troubleshooting purposes
			}
		} // column_default
	
		function get_columns()	{ // The table columns
			$columns = array(
				'cb'        => '<input type="checkbox" />',
				'song' => __( '<strong>Song Name</strong>', 'mdjmplaylisttable' ),
				'artist' => __( '<strong>Artist</strong>', 'mdjmplaylisttable' ),
				'play_when' => __( '<strong>When to Play</strong>', 'mdjmplaylisttable' ),
				'info' => __( '<strong>Information</strong>', 'mdjmplaylisttable' ),
				'by' => __( '<strong>Added By</strong>', 'mdjmplaylisttable' ),
				'date_added' => __( '<strong>Date Added</strong>', 'mdjmplaylisttable' ),
			);
			 return $columns;
		} // get_columns
		
		function get_sortable_columns() { // Defines the columns by which users can sort the table
			$sortable_columns = array(
			'song'  => array( 'song', false ),
			'artist'   => array( 'artist', false ),
			'play_when'   => array( 'play_when', false ),
			'by'   => array( 'by', false ),
			'date_added'   => array( 'date_added', true ),
			);
			return $sortable_columns;
		} // get_sortable_columns
		
		function get_bulk_actions() { // Define the bulk actions for the drop down list
			$actions = array(
						'delete' => 'Delete'
						);
			return $actions;
		} // get_bulk_actions
		
		function process_bulk_action() {
			if( 'delete'===$this->current_action() ) {
				foreach( $_POST['playlist_id'] as $pl ) {
					f_mdjm_delete_from_playlist( $pl );
				}
				f_mdjm_update_notice( 'updated', 'Selected songs have deleted' );
			}
		}
		
		function column_cb( $item ) { // Checkbox column
			return sprintf(
				'<input type="checkbox" name="playlist_id[]" value="%s" />', $item['id']
			);    
		} // column_cb
				
		function send_to_email( $post_data, $get_data )	{
			global $mdjm_options, $wpdb, $current_user;
			if( !isset( $get_data['event'] ) || empty( $get_data['event'] ) )	{
				return;	
			}
			else	{
				include ( WPMDJM_PLUGIN_DIR . '/includes/config.inc.php' );
				$email_query = 'SELECT * FROM `'.$db_tbl['playlists'].'` WHERE `event_id` = ' . $get_data['event'] . ' ORDER BY `' . $post_data['order_pl_by'] . '` ASC';
				$email_result = $wpdb->get_results( $email_query );
				$pl_ttl = $wpdb->num_rows;
				
				if( !isset( $post_data['repeat_headers'] ) || empty( $post_data['repeat_headers'] ) || $post_data['repeat_headers'] == 0 )	{
					$repeat = 0;
				}
				else	{
					$repeat = $post_data['repeat_headers'];
				}
				
				$i = 0;
				
				$eventinfo = $wpdb->get_row('SELECT * FROM ' . $db_tbl['events'] . ' WHERE `event_id` = ' . $get_data['event']);
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
				$email_body .= '<p align="center" style="font-size: 9px">Powered by <a style="color:#F90" href="http://www.mydjplanner.co.uk" target="_blank">' . WPMDJM_NAME . '</a> version ' . WPMDJM_VERSION_NUM . '</p>' . "\n";
				$email_body .= '</body>' . "\n" . '</html>' . "\n";
				
				$headers = 'MIME-Version: 1.0' . "\r\n";
				$headers .= 'Content-type: text/html; charset=UTF-8' . "\r\n";
				$headers .= 'From: ' . $mdjm_options['company_name'] . ' <' . $mdjm_options['system_email'] . '>' . "\r\n";
				
				if( wp_mail( $current_user->user_email, 'Event Playlist for ' . date( "l, jS F Y", strtotime( $eventinfo->event_date ) ), $email_body, $headers ) )	{
					f_mdjm_update_notice( 'updated', 'Playlist successfully emailed to <a href="mailto:' . $current_user->user_email . '">' . $current_user->display_name . '</a>' );	
				}
				else	{
					f_mdjm_update_notice( 'error', 'Unable to email playlist' );	
				}
				
			}
		} // send_to_email
				
		function prepare_items()	{
			$per_page = $this->get_items_per_page('events_per_page', 25);
			$current_page = $this->get_pagenum();
			$columns  = $this->get_columns(); // Retrieve table columns
			$hidden   = array(); // Which fields are hidden
			$sortable = $this->get_sortable_columns(); // Which fields can be sorted by
			$this->_column_headers = array( $columns, $hidden, $sortable );
			$action = $this->process_bulk_action(); // Process current action
			$this->items = $this->get_playlist(); // The data
		} // prepare_items
	} // Class
?>