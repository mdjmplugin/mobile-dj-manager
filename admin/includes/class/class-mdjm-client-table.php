<?php
	class MDJM_Clients_Table extends WP_List_Table	{
		private function get_clients()	{
			global $wpdb, $display, $order, $orderby, $mdjm;
			
			if (isset ( $_GET['display'] ) ) $display = $_GET['display'];
			else $display = 'client';
			
			if (isset ( $_GET['orderby'] ) ) $orderby = $_GET['orderby'];
			else $orderby = 'display_name';
			
			if (isset ( $_GET['order'] ) ) $order = $_GET['order'];
			else $order = 'ASC';
			
			if( !isset( $_POST['s'] ) || empty( $_POST['s'] )	 ){
				$clientinfo = $mdjm->mdjm_events->get_clients( $display, $orderby, $order );
			}
			else	{
				$arg = array(	'search'  => $_POST['s'],
								'role'    => $display,
								'orderby' => $orderby,
								'order'   => $order
							);
				$clientinfo = get_users( $arg );
			}
			$client_data = array();
			$url = admin_url();
			
			foreach( $clientinfo as $client )	{
				
				if( !current_user_can( 'administrator' ) )	{ // Non-Admins only see their own clients
					if( $mdjm->mdjm_events->is_my_client( $client->ID ) )	{
						$events = $mdjm->mdjm_events->client_events( $client->ID );
						$next_event = $mdjm->mdjm_events->next_event( $client->ID, $user_type='client' );
						if( !empty( $next_event ) )	{
							$event_link = '<a href="' . admin_url( 'post.php?post=' . $next_event[0]->ID . '&action=edit' ) . '">' . 
								date( MDJM_SHORTDATE_FORMAT, get_post_meta( $next_event[0]->ID, '_mdjm_event_date', true ) ) . '</a>';	
						}
						else	{
							$event_link = 'N/A';
						}
						$last_login = strtotime( get_user_meta( $client->ID, 'last_login', true ) );
						if( !$last_login ) $last_login = 'Never';
						else $last_login = date( 'H:i d M Y', $last_login );
						$client_data[] = array(
											'client_id' => $client->ID,
											'client_name' => '<a href="' . admin_url( 'admin.php?page=mdjm-clients&action=view_client&client_id=' . $client->ID ) . '">' . $client->display_name . '</a>',
											'last_login' => $last_login,
											'client_email' => '<a href="' . admin_url( 'admin.php?page=mdjm-comms&to_user=' ) . $client->ID . '">' . $client->user_email . '</a>',
											'client_events' => ( count( $events ) != 0 ? '<a href="' . admin_url( 'edit.php?post_type=' . MDJM_EVENT_POSTS . '&client=' 
															. $client->ID ) . '">' . count( $events ) . '</a>' : '0' ),
											'client_next_event' => $event_link,
											'client_journal' => '<a href="' . $url . 'admin.php?page=mdjm-journal&client_id=' . $client->ID . '">' . 'View</a>'
											);
					}
				}
				else	{
					$events = $mdjm->mdjm_events->client_events( $client->ID );
					$next_event = $mdjm->mdjm_events->next_event( $client->ID, $user_type='client' );
					if( !empty( $next_event ) )	{
						$event_link = '<a href="' . admin_url( 'post.php?post=' . $next_event[0]->ID . '&action=edit' ) . '">' . 
							date( MDJM_SHORTDATE_FORMAT, strtotime( get_post_meta( $next_event[0]->ID, '_mdjm_event_date', true ) ) ) . '</a>';	
					}
					else	{
						$event_link = 'N/A';
					}
					$last_login = strtotime( get_user_meta( $client->ID, 'last_login', true ) );
					if( !$last_login ) $last_login = 'Never';
					else $last_login = date( 'H:i d M Y', $last_login );
					$client_data[] = array(
										'client_id' => $client->ID,
										'client_name' => '<a href="' . $url . 'user-edit.php?user_id=' . $client->ID . '">' . $client->display_name . '</a>',
										'last_login' => $last_login,
										'client_email' => '<a href="' . admin_url( 'admin.php?page=mdjm-comms&to_user=' ) . $client->ID . '">' . $client->user_email . '</a>',
										'client_events' => ( count( $events ) != 0 ? '<a href="' . admin_url( 'edit.php?post_type=' . MDJM_EVENT_POSTS . '&client=' 
															. $client->ID ) . '">' . count( $events ) . '</a>' : '0' ),
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
			$active_clients = mdjm_get_clients( 'client', $orderby, $order );
			$inactive_clients = mdjm_get_clients( 'inactive_client', $orderby, $order );
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
						'cb'                => '<input type="checkbox" />',
						'client_name'       => '<strong>' . __( 'Name', 'mobile-dj-manager' ) . '</strong>',
						'last_login'        => '<strong>' . __( 'Last Login', 'mobile-dj-manager' ) . '</strong>',
						'client_email'      => '<strong>' . __( 'Email', 'mobile-dj-manager' ) . '</strong>',
						'client_events'     => '<strong>' . __( 'No. Events', 'mobile-dj-manager' ) . '</strong>',
						'client_next_event' => '<strong>' . __( 'Next Event', 'mobile-dj-manager' ) . '</strong>',
						'client_journal'    => '<strong>' . __( 'Journal', 'mobile-dj-manager' ) . '</strong>'
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
		
		/* Bulk Actions */
		
		function get_bulk_actions() { // Define the bulk actions for the drop down list
			if( !isset( $_GET['display'] ) || $_GET['display'] != 'inactive_client' )	{
				$actions = array(
							'inactive' => 'Mark Inactive',
							);
			}
			if( isset( $_GET['display'] ) && $_GET['display'] == 'inactive_client' )	{
				$actions = array(
							'active' => 'Mark Active',
							);
			}
			return $actions;
		} // get_bulk_actions
		
		function process_bulk_action() {
			$action = $this->current_action();
			$clients = isset( $_POST['client'] ) ? $_POST['client'] : false;
			if( !is_array( $clients ) )
				$clients = array( $clients );
			
			if( empty( $action ) )
				return;
			
			if( 'inactive' === $this->current_action() ) {
					f_mdjm_set_client_role( $clients, 'inactive_client' );
			}
			if( 'active' === $this->current_action() ) {
					f_mdjm_set_client_role( $clients, 'client' );
			}
		} // process_bulk_action
		
		function column_cb( $item ) { // Checkbox column
			return sprintf(
				'<input type="checkbox" name="client[]" value="%s" />', $item['client_id']
			);    
		} // column_cb
		
		function column_client_name( $item ) {
			if( !isset( $_GET['display'] ) || $_GET['display'] != 'inactive_client' )	{
				$actions = array(
						'inactive_client' => sprintf( '<a href="?page=%s&action=%s&role=inactive_client&client_id=%s">Mark Inactive</a>', $_REQUEST['page'], 'set_client_role', $item['client_id'] ),
							);
			}
			else	{
				$actions = array(
						'active_client' => sprintf( '<a href="?page=%s&action=%s&role=client&client_id=%s">Mark Active</a>', $_REQUEST['page'], 'set_client_role', $item['client_id'] ),
							);	
			}
			return sprintf( '%1$s %2$s', $item['client_name'], $this->row_actions( $actions ) );
		}
		
		function prepare_items()	{
			$per_page = $this->get_items_per_page('clients_per_page', 25);
			$current_page = $this->get_pagenum();
			$this->process_bulk_action(); // Process bulk actions
			$columns  = $this->get_columns(); // Retrieve table columns
			$hidden   = array(); // Which fields are hidden
			$sortable = $this->get_sortable_columns(); // Which fields can be sorted by
			$this->_column_headers = array( $columns, $hidden, $sortable );
			$this->items = $this->get_clients(); // The data
		} // prepare_items
	} // Class
?>