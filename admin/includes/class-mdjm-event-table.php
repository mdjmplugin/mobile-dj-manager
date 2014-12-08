<?php
	class MDJM_Events_Table extends WP_List_Table	{
		private function get_events()	{
			global $wpdb, $display_query, $display;
			include ( WPMDJM_PLUGIN_DIR . '/includes/config.inc.php' );
			// Build the data query
			$query = 'SELECT * FROM `'.$db_tbl['events'].'`';
			
			if( isset( $_GET['display'] ) ) $display = $_GET['display'];
			else $display = 'active';
			
			/* If your not a site admin you only get your own events */
			if( !current_user_can( 'manage_options' ) )	$djonly .= ' AND `event_dj` = ' . get_current_user_id();
			
			$display_query['active'] = $query . " WHERE `event_date` >= DATE(NOW()) AND `contract_status` != 'Cancelled' AND `contract_status` != 'Completed' AND `contract_status` != 'Enquiry' AND `contract_status` != 'Failed Enquiry'" . $djonly;
			$display_query['historic'] = $query . " WHERE (`contract_status` != 'Enquiry' AND `contract_status` != 'Failed Enquiry' AND `event_date` < DATE(NOW()) OR `contract_status` = 'Cancelled' OR `contract_status` = 'Completed')" . $djonly;
			$display_query['all'] = $query . " WHERE `contract_status` != 'Enquiry' AND `contract_status` != 'Failed Enquiry'" . $djonly;
			
			$display_query['enquiries'] = $query . " WHERE `contract_status` = 'Enquiry'" . $djonly;
			
			$display_query['lost'] = $query . " WHERE `contract_status` = 'Failed Enquiry'" . $djonly;
			
			$query = $display_query[$display];
			
			if( isset( $_GET['orderby'] ) ) $orderby = $_GET['orderby'];
			else $orderby = 'event_date';
			
			if( isset( $_GET['order'] ) ) $order = $_GET['order'];
			else $order = 'ASC';
			
			$query .= ' ORDER BY `' . $orderby . '` ' . $order;
			$eventinfo = $wpdb->get_results( $query );
			
			$event_data = array();
			foreach( $eventinfo as $event )	{
				$playlist = $wpdb->get_var( "SELECT COUNT(*) FROM " . $db_tbl['playlists'] . " WHERE event_id = " . $event->event_id );
				$djinfo = get_userdata( $event->event_dj );
				if ( $playlist == 0 ) { $play_count = $playlist . ' Songs'; }
				elseif ( $playlist == 1 ) { $play_count = $playlist . ' Song'; }
				else { $play_count = $playlist . ' Songs</a>'; } 
				$client = get_userdata( $event->user_id );
				$url = admin_url();
				if( !current_user_can( 'administrator' ) && !dj_can( 'see_deposit' ) ) $event->cost = $event->cost - $event->deposit;
				$event_data[] = array(
									'event_id' => $event->event_id,
									'event_date' => date( "d M Y", strtotime( ( $event->event_date ) ) ),
									'client_id' => $event->user_id,
									'client' =>  $client->first_name . ' ' . $client->last_name,
									'event_dj' =>  $djinfo->display_name,
									'event_type' => $event->event_type,
									'contract_status' => $event->contract_status,
									'cost' => '&pound;' . $event->cost,
									'playlist' => $play_count,
									'journal' => '<a href="' . $url . 'admin.php?page=mdjm-events&action=show_journal&event_id=' . $event->event_id . '">View</a>'
								);
			}
			return $event_data;
		} // event_data
		
		function no_items() {
			_e( 'There are currently no events loaded within the application. Check out your open <a href="' . admin_url() . 'admin.php?page=mdjm-events&display=enquiries">enquiries</a>' );
		}
		
		function __construct()	{
			global $status, $page;
	
			parent::__construct( array(
				'singular'  => __( 'event', 'mdjmeventtable' ),     //singular name of the listed records
				'plural'    => __( 'events', 'mdjmeventtable' ),   //plural name of the listed records
				'ajax'      => false        //does this table support ajax?
			) );
			add_action( 'admin_head', array( &$this, 'admin_header' ) ); // Call the function to style the table
		} // __construct
		
		function admin_header() {
			$page = ( isset($_GET['page'] ) ) ? esc_attr( $_GET['page'] ) : false;
			if( 'mdjm-events' != $page )
			return; 
			
			echo '<style type="text/css">';
			echo '.wp-list-table .column-event_date { width: 15%; }';
			echo '.wp-list-table .column-client { width: 15%; }';
			echo '.wp-list-table .column-event_dj { width: 15%; }';
			echo '.wp-list-table .column-event_type { width: 20%; }';
			echo '.wp-list-table .column-contract_status { width: 20%; }';
			echo '.wp-list-table .column-cost { width: 5%; }';
			echo '.wp-list-table .column-playlist { width: 5%; }';
			echo '.wp-list-table .column-journal { width: 5%; }';
			echo '</style>';
		} // admin_header
	
		function column_default( $item, $column_name )	{
			switch( $column_name )	{ 
				case 'event_id':
				case 'event_date':
				case 'client_id':
				case 'client':
				case 'event_dj':
				case 'event_type':
				case 'contract_status':
				case 'cost':
				case 'playlist':
				case 'journal':
					return $item[ $column_name ];
				default:
					return print_r( $item, true ) ; // Show the whole array for troubleshooting purposes
			}
		} // column_default
	
		function get_columns()	{ // The table columns
			$columns = array(
				'cb'        => '<input type="checkbox" />',
				'event_id' => __( '<strong>ID</strong>', 'mdjmeventtable' ),
				'event_date' => __( '<strong>Date</strong>', 'mdjmeventtable' ),
				'client_id' => __( '<strong>Client ID</strong>', 'mdjmeventtable' ),
				'client' => __( '<strong>Client</strong>', 'mdjmeventtable' ),
				'event_dj' => __( '<strong>DJ</strong>', 'mdjmeventtable' ),
				'event_type' => __( '<strong>Type</strong>', 'mdjmeventtable' ),
				'contract_status' => __( '<strong>Status</strong>', 'mdjmeventtable' ),
				'cost' => __( '<strong>Value</strong>', 'mdjmeventtable' ),
				'playlist' => __( '<strong>Playlist</strong>', 'mdjmeventtable' ),
				'journal' => __( '<strong>Journal</strong>', 'mdjmeventtable' )
			);
			 return $columns;
		} // get_columns
		
		function get_sortable_columns() { // Defines the columns by which users can sort the table
			$sortable_columns = array(
			'event_id'  => array( 'event_id', false ),
			//'client' => array( 'client', false ), | Not working
			'event_date'   => array( 'event_date', true ),
			'event_type'   => array( 'event_type', false ),
			'contract_status'   => array( 'contract_status', false ),
			);
			return $sortable_columns;
		} // get_sortable_columns
		
		function get_bulk_actions() { // Define the bulk actions for the drop down list
			if( isset( $_GET['display'] ) && $_GET['display'] != 'enquiries' && $_GET['display'] != 'lost' )	{
				$actions = array(
							'complete' => 'Mark as Complete',
							'cancel' => 'Cancel Event',
							);
			}
			elseif( isset( $_GET['display'] ) && $_GET['display'] == 'lost' )	{
				$actions = array(
							'recover' => 'Recover Enquiry',
							);
			}
			else	{
				$actions = array(
							'convert' => 'Convert Enquiry',
							'fail' => 'Fail Enquiry',
							);	
			}
			return $actions;
		} // get_bulk_actions
		
		function process_bulk_action() {
			$action = $this->current_action();
			$events = isset( $_POST['event'] ) ? $_POST['event'] : false;
			if( !is_array( $events ) )
				$events = array( $events );
			
			if( empty( $action ) )
				return;
			
			if( 'cancel' === $this->current_action() ) {
					f_mdjm_cancel_event( $events );
			}
			if( 'complete' === $this->current_action() ) {
					f_mdjm_complete_event( $events );
			}
			if( 'convert' === $this->current_action() ) {
					f_mdjm_convert_event( $events );
			}
			if( 'fail' === $this->current_action() ) {
					f_mdjm_fail_enquiry( $events );
			}
			if( 'recover' === $this->current_action() ) {
					f_mdjm_recover_event( $events );
			}
		} // process_bulk_action
		
		function column_cb( $item ) { // Checkbox column
			return sprintf(
				'<input type="checkbox" name="event[]" value="%s" />', $item['event_id']
			);    
		} // column_cb
		
		function column_event_date( $item ) {
			$actions = array();
			$actions['edit'] = sprintf( '<a href="?page=%s&action=%s&event_id=%s">Edit</a>', $_REQUEST['page'], 'view_event_form', $item['event_id'] );
			
			if( $item['contract_status'] != 'Cancelled' && $item['contract_status'] != 'Completed' && $item['contract_status'] != 'Enquiry' && $item['contract_status'] != 'Failed Enquiry' )	{
				$actions['complete'] = sprintf( '<a href="?page=%s&action=%s&event_id=%s">Complete</a>', $_REQUEST['page'], 'complete_event', $item['event_id'] );
				$actions['cancel'] = sprintf( '<a href="?page=%s&action=%s&event_id=%s">Cancel</a>', $_REQUEST['page'], 'cancel_event', $item['event_id'] );
			}
			if( $item['contract_status'] == 'Enquiry' )	{
				$actions['convert'] = sprintf( '<a href="?page=%s&action=%s&event_id=%s">Convert</a>', $_REQUEST['page'], 'convert_event', $item['event_id'] );
				$actions['failed'] = sprintf( '<a href="?page=%s&action=%s&event_id=%s">Fail</a>', $_REQUEST['page'], 'fail_enquiry', $item['event_id'] );
			}
			if( $item['contract_status'] == 'Failed Enquiry' )	{
				$actions['recover'] = sprintf( '<a href="?page=%s&action=%s&event_id=%s">Recover</a>', $_REQUEST['page'], 'recover_event', $item['event_id'] );
			}	
			return sprintf( '%1$s %2$s', $item['event_date'], $this->row_actions( $actions ) );
		}
		
		function column_client( $item ) {
			$actions = array(
					'edit' => sprintf( '<a href="'. admin_url() . '%s?user_id=%s">Edit</a>', 'user-edit.php', $item['client_id'] ),
				);
			return sprintf( '%1$s %2$s', $item['client'], $this->row_actions( $actions ) );
		}
		
		function column_playlist( $item ) {
			if( $item['playlist'] != '0 Songs'	)	{
				$actions['playlist'] = sprintf( '<a href="?page=%s&action=%s&event=%s">View</a>', $_REQUEST['page'], 'render_playlist_table', $item['event_id'] );
			}
			return sprintf( '%1$s %2$s', $item['playlist'], $this->row_actions( $actions ) );
		}
		
		function extra_tablenav( $which )	{ // Determine what is to be shown before and after the table
			global $wpdb, $display_query, $display;
			if( isset( $which ) && $which == "top" ){ // Before table
		   ?>
				<ul class='subsubsub'>
				<li class='publish'><a href="<?php echo admin_url(); ?>admin.php?page=mdjm-events&display=active"<?php if ( $display == "active" ) { ?> class="current" <?php } ?>>Active <span class="count">(<?php echo count( $wpdb->get_results( $display_query['active'] ) ); ?>)</span></a> |</li>
				<li class='draft'><a href="<?php echo admin_url(); ?>admin.php?page=mdjm-events&display=historic"<?php if ( $display == "historic" ) { ?> class="current" <?php } ?>>Historic <span class="count">(<?php echo count( $wpdb->get_results( $display_query['historic'] ) ); ?>)</span></a> |</li>
				<li class='all'><a href="<?php echo admin_url(); ?>admin.php?page=mdjm-events&display=all"<?php if ( $display == "all" ) { ?> class="current" <?php } ?>>All <span class="count">(<?php echo count( $wpdb->get_results( $display_query['all'] ) ); ?>)</span></a> |</li>
                <?php
				if( current_user_can( 'manage_options' ) || dj_can( 'view_enquiry' ) )	{
					?>
                <li class='all'><a href="<?php echo admin_url(); ?>admin.php?page=mdjm-events&display=enquiries"<?php if ( $display == "enquiries" ) { ?> class="current" <?php } ?>>Enquiries <span class="count">(<?php echo count( $wpdb->get_results( $display_query['enquiries'] ) ); ?>)</span></a> |</li>
                <li class='all'><a href="<?php echo admin_url(); ?>admin.php?page=mdjm-events&display=lost"<?php if ( $display == "lost" ) { ?> class="current" <?php } ?>>Lost Enquiries <span class="count">(<?php echo count( $wpdb->get_results( $display_query['lost'] ) ); ?>)</span></a></li>
                <?php
				}
				?>
                </ul>
           <?php
		   }
		   if( isset( $which ) && $which == "bottom" )	{ // After table
			  
		   }
		} // extra_tablenav
		
		function prepare_items()	{
			$per_page = $this->get_items_per_page( 'events_per_page', 25 );
			$current_page = $this->get_pagenum();
			$this->process_bulk_action(); // Process bulk actions
			$columns  = $this->get_columns(); // Retrieve table columns
			$hidden   = array( 'event_id', 'client_id' ); // Which fields are hidden
			$sortable = $this->get_sortable_columns(); // Which fields can be sorted by
			$this->_column_headers = array( $columns, $hidden, $sortable );
			$this->items = $this->get_events(); // The data
		} // prepare_items
	} // Class
?>