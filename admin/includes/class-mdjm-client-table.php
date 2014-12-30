<?php
	class MDJM_Clients_Table extends WP_List_Table	{
		private function get_clients()	{
			global $wpdb, $display, $order, $orderby;
			
			if (isset ( $_GET['display'] ) ) $display = $_GET['display'];
			else $display = 'client';
			
			if (isset ( $_GET['orderby'] ) ) $orderby = $_GET['orderby'];
			else $orderby = 'display_name';
			
			if (isset ( $_GET['order'] ) ) $order = $_GET['order'];
			else $order = 'ASC';
			
			$clientinfo = f_mdjm_get_clients( $display, $orderby, $order );
			$client_data = array();
			$url = admin_url();
			
			foreach( $clientinfo as $client )	{
				
				if( !current_user_can( 'administrator' ) )	{ // Non-Admins only see their own clients
					if( f_mdjm_client_is_mine( $client->ID ) )	{
						$info = f_mdjm_client_get_events( $client->ID );
						if( isset( $info['next_event'] ) && $info['next_event'] != 'N/A' )	{
							$event_link = '<a href="' . $url . 'admin.php?page=mdjm-events&action=view_event_form&event_id=' . $info['event_id'] . '">' . $info['next_event'] . '</a>';	
						}
						else	{
							$event_link = $info['next_event'];
						}
						$last_login = strtotime( get_user_meta( $client->ID, 'last_login', true ) );
						if( !$last_login ) $last_login = 'Never';
						else $last_login = date( 'H:i d M Y', $last_login );
						$client_data[] = array(
											'client_id' => $client->ID,
											'client_name' => '<a href="' . admin_url( 'admin.php?page=mdjm-clients&action=view_client&client_id=' . $client->ID ) . '">' . $client->display_name . '</a>',
											'last_login' => $last_login,
											'client_email' => '<a href="' . admin_url( 'admin.php?page=mdjm-comms&to_user=' ) . $client->ID . '">' . $client->user_email . '</a>',
											'client_events' => $info['num_rows'],
											'client_next_event' => $event_link,
											'client_journal' => '<a href="' . $url . 'admin.php?page=mdjm-journal&client_id=' . $client->ID . '">' . 'View</a>'
											);
					}
				}
				else	{
					$info = f_mdjm_client_get_events( $client->ID );
					if( isset( $info['next_event'] ) && $info['next_event'] != 'N/A' )	{
						$event_link = '<a href="' . $url . 'admin.php?page=mdjm-events&action=view_event_form&event_id=' . $info['event_id'] . '">' . $info['next_event'] . '</a>';	
					}
					else	{
						$event_link = $info['next_event'];
					}
					$last_login = strtotime( get_user_meta( $client->ID, 'last_login', true ) );
					if( !$last_login ) $last_login = 'Never';
					else $last_login = date( 'H:i d M Y', $last_login );
					$client_data[] = array(
										'client_id' => $client->ID,
										'client_name' => '<a href="' . $url . 'user-edit.php?user_id=' . $client->ID . '">' . $client->display_name . '</a>',
										'last_login' => $last_login,
										'client_email' => '<a href="' . admin_url( 'admin.php?page=mdjm-comms&to_user=' ) . $client->ID . '">' . $client->user_email . '</a>',
										'client_events' => $info['num_rows'],
										'client_next_event' => $event_link,
										'client_journal' => '<a href="' . $url . 'admin.php?page=mdjm-events&action=show_journal&client_id=' . $client->ID . '">' . 'View</a>'
										);	
				}
			}
			return $client_data;
		} // get_clients
		
		function admin_header() {
			$page = ( isset($_GET['page'] ) ) ? esc_attr( $_GET['page'] ) : false;
			if( 'mdjm-clients' != $page )
			return; 
			
			echo '<style type="text/css">';
			echo '.wp-list-table .column-client_name { width: 15%; }';
			echo '.wp-list-table .column-last_login { width: 20%; }';
			echo '.wp-list-table .column-client_email { width: 15%; }';
			echo '.wp-list-table .column-client_events { width: 10%; }';
			echo '.wp-list-table .column-client_next_event { width: 20%; }';
			echo '.wp-list-table .column-client_journal { width: 20%; }';
			echo '</style>';
		} // admin_header
		
		function no_items() {
			_e( 'There are currently no such clients within the application.' );
		}
		
		function __construct()	{
			global $status, $page;
	
			parent::__construct( array(
				'singular'  => __( 'client', 'mdjmclienttable' ),     //singular name of the listed records
				'plural'    => __( 'clients', 'mdjmclienttable' ),   //plural name of the listed records
				'ajax'      => false        //does this table support ajax?
			) );
			add_action( 'admin_head', array( &$this, 'admin_header' ) ); // Call the function to style the table
		} // __construct
	
		function column_default( $item, $column_name )	{
			switch( $column_name )	{ 
				case 'client_name':
				case 'last_login':
				case 'client_email':
				case 'client_events':
				case 'client_next_event':
				case 'client_journal':
					return $item[ $column_name ];
				default:
					return print_r( $item, true ) ; // Show the whole array for troubleshooting purposes
			}
		} // column_default
		
		function extra_tablenav( $which )	{ // Determine what is to be shown before and after the table
			global $wpdb, $display_query, $display, $order, $orderby;
			$active_clients = f_mdjm_get_clients( 'client', $orderby, $order );
			$inactive_clients = f_mdjm_get_clients( 'inactive_client', $orderby, $order );
			if( isset( $which ) && $which == "top" ){ // Before table
		   ?>
				<ul class='subsubsub'>
				<li class='publish'><a href="<?php echo admin_url(); ?>admin.php?page=mdjm-clients&display=client"<?php if( isset( $display ) && $display == "client" ) { ?> class="current" <?php } ?>>Active Clients <span class="count">(<?php echo count( $active_clients ); ?>)</span></a> |</li>
				<li class='draft'><a href="<?php echo admin_url(); ?>admin.php?page=mdjm-clients&display=inactive_client"<?php if( isset( $display ) && $display == "inactive_client" ) { ?> class="current" <?php } ?>>Inactive Clients <span class="count">(<?php echo count( $inactive_clients ); ?>)</span></a></li>
                </ul>
           <?php
		   }
		   if( isset( $which ) && $which == "bottom" )	{ // After table
			  
		   }
		} // extra_tablenav
	
		function get_columns()	{ // The table columns
			$columns = array(
				'client_name' => __( '<strong>Name</strong>', 'mdjmclienttable' ),
				'last_login' => __( '<strong>Last Login</strong>', 'mdjmclienttable' ),
				'client_email' => __( '<strong>Email</strong>', 'mdjmclienttable' ),
				'client_events' => __( '<strong>No. Events</strong>', 'mdjmclienttable' ),
				'client_next_event' => __( '<strong>Next Event</strong>', 'mdjmclienttable' ),
				'client_journal' => __( '<strong>Journal</strong>', 'mdjmclienttable' )
			);
			 return $columns;
		} // get_columns
		
		function get_sortable_columns() { // Defines the columns by which users can sort the table
			$sortable_columns = array(
			'client_name'   => array( 'client_name', true ),
			'last_login'   => array( 'last_login', false ),
			'client_email'   => array( 'client_email', false ),
			'client_events'   => array( 'client_events', false ),
			'client_next_event' => array( 'client_next_event', false ),
			);
			return $sortable_columns;
		} // get_sortable_columns
		
		function prepare_items()	{
			$per_page = $this->get_items_per_page('clients_per_page', 25);
			$current_page = $this->get_pagenum();
			$columns  = $this->get_columns(); // Retrieve table columns
			$hidden   = array(); // Which fields are hidden
			$sortable = $this->get_sortable_columns(); // Which fields can be sorted by
			$this->_column_headers = array( $columns, $hidden, $sortable );
			$this->items = $this->get_clients(); // The data
		} // prepare_items
	} // Class
?>