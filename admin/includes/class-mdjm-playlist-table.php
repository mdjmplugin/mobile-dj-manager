<?php
	class MDJM_PlayList_Table extends WP_List_Table	{
		private function get_playlist()	{
			global $wpdb, $query;
			include ( WPMDJM_PLUGIN_DIR . '/includes/config.inc.php' );
			// Build the data query
			$query = 'SELECT * FROM `'.$db_tbl['playlists'].'` WHERE `event_id` = ' . $_GET['event'];
			
			if( isset( $_GET['orderby'] ) ) $orderby = $_GET['orderby'];
			else $orderby = 'song';
			
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
									'by' => stripslashes( $playlist->added_by )
								);
			}
			$eventinfo = $wpdb->get_row('SELECT * FROM ' . $db_tbl['events'] . ' WHERE `event_id` = ' . $_GET['event']);
			$client = get_userdata( $eventinfo->user_id );
			?>
			<div class="wrap">
			<h4>Event Date: <?php echo date( "l, jS F Y",strtotime( $eventinfo->event_date ) ); ?><br />
            Event Type: <?php echo $eventinfo->event_type; ?><br />
			Client Name: <?php echo $client->first_name . ' ' . $client->last_name ?><br />
			No. Songs in Playlist: <?php echo $pl_ttl; ?><br />
			<a href="?page=mdjm-events&action=print_playlist&event_id=<?php echo $_GET['event']; ?>" target="_blank">Click for printable version</a></h4>
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
			);
			 return $columns;
		} // get_columns
		
		function get_sortable_columns() { // Defines the columns by which users can sort the table
			$sortable_columns = array(
			'song'  => array( 'song', true ),
			'artist'   => array( 'artist', false ),
			'play_when'   => array( 'play_when', false ),
			'by'   => array( 'by', false ),
			);
			return $sortable_columns;
		} // get_sortable_columns
		
		function get_bulk_actions() { // Define the bulk actions for the drop down list
			$actions = array(
						'delete' => 'Delete'
						);
			return $actions;
		} // get_bulk_actions
		
		function column_cb( $item ) { // Checkbox column
			return sprintf(
				'<input type="checkbox" name="playlist_id[]" value="%s" />', $item['id']
			);    
		} // column_cb
		
		function current_action()	{ // Process the actions defined by the bulk action drop down
			
		} // current_action
		
		function prepare_items()	{
			$per_page = $this->get_items_per_page('events_per_page', 25);
			$current_page = $this->get_pagenum();
			$columns  = $this->get_columns(); // Retrieve table columns
			$hidden   = array(); // Which fields are hidden
			$sortable = $this->get_sortable_columns(); // Which fields can be sorted by
			$this->_column_headers = array( $columns, $hidden, $sortable );
			$action = $this->current_action(); // Process current action
			$this->items = $this->get_playlist(); // The data
		} // prepare_items
	} // Class
?>