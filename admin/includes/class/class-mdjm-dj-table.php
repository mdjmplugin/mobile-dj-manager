<?php
	class MDJM_DJs_Table extends WP_List_Table	{
		private function get_djs()	{
			global $wpdb, $display, $order, $orderby;
			
			if (isset ( $_GET['display'] ) ) $display = $_GET['display'];
			else $display = 'djs';
			
			if( isset( $_GET['orderby'] ) ) $orderby = $_GET['orderby'];
			else $orderby = 'display_name';
			
			if( isset( $_GET['order'] ) ) $order = $_GET['order'];
			else $order = 'ASC';
			
			$func = 'f_mdjm_get_' . $display;
			
			$djs = $func();
			$dj_data = array();
			$url = admin_url();
			foreach( $djs as $dj )	{
				$info = f_mdjm_dj_get_events( $dj->ID );
				if( isset( $info['next_event'] ) && $info['next_event'] != 'N/A' )	{
					$event_link = '<a href="' . $url . 'admin.php?page=mdjm-events&action=view_event_form&event_id=' . $info['event_id'] . '">' . $info['next_event'] . '</a>';	
				}
				else	{
					$event_link = $info['next_event'];
				}
				$last_login = strtotime( get_user_meta( $dj->ID, 'last_login', true ) );
				if( !$last_login ) $last_login = 'Never';
				else $last_login = date( 'H:i d M Y', $last_login );
				$dj_data[] = array(
									'dj_id'             => $dj->ID,
									'dj_name'           => '<a href="' . $url . 'user-edit.php?user_id=' . $dj->ID . '">' . $dj->display_name . '</a>',
									'dj_email'          => '<a href="' . admin_url( 'admin.php?page=mdjm-comms&to_user=' ). $dj->ID . '">' . $dj->user_email . '</a>',
									'dj_last_login'     => $last_login,
									'dj_active_events'  => $info['active_events'],
									'dj_all_events'     => $info['all_events'],
									'dj_next_event'     => $event_link,
									'dj_open_enquiries' => $info['enquiries'],
									);
			}
			return $dj_data;
		} // get_djs
		
		function admin_header() {
			$page = ( isset($_GET['page'] ) ) ? esc_attr( $_GET['page'] ) : false;
			if( 'mdjm-djs' != $page )
			return; 
			
			echo '<style type="text/css">';
			echo '.wp-list-table .column-dj_name { width: 15%; }';
			echo '.wp-list-table .column-dj_email { width: 15%; }';
			echo '.wp-list-table .column-dj_last_login { width: 10%; }';
			echo '.wp-list-table .column-dj_active_events { width: 10%; }';
			echo '.wp-list-table .column-dj_all_events { width: 10%; }';
			echo '.wp-list-table .column-dj_next_event { width: 20%; }';
			echo '.wp-list-table .column-dj_open_enquiries { width: 20%; }';
			echo '</style>';
		} // admin_header
		
		function no_items() {
			_e( 'There are currently no DJ\'s loaded within the application.' );
		}
		
		function __construct()	{
			global $status, $page;
	
			parent::__construct( array(
				'singular'  => __( 'dj', 'mdjmdjtable' ),     //singular name of the listed records
				'plural'    => __( 'dj\'', 'mdjmdjtable' ),   //plural name of the listed records
				'ajax'      => false        //does this table support ajax?
			) );
			add_action( 'admin_head', array( &$this, 'admin_header' ) ); // Call the function to style the table
		} // __construct
	
		function column_default( $item, $column_name )	{
			switch( $column_name )	{ 
				case 'dj_name':
				case 'dj_email':
				case 'dj_last_login':
				case 'dj_active_events':
				case 'dj_all_events':
				case 'dj_next_event':
				case 'dj_open_enquiries':
					return $item[ $column_name ];
				default:
					return print_r( $item, true ) ; // Show the whole array for troubleshooting purposes
			}
		} // column_default
		
		function extra_tablenav( $which )	{ // Determine what is to be shown before and after the table
			global $wpdb, $display_query, $display, $order, $orderby;
			$active_djs = f_mdjm_get_djs();
			$inactive_djs = f_mdjm_get_inactive_djs();
			if( isset( $which ) && $which == "top" ){ // Before table
		   ?>
				<ul class='subsubsub'>
				<li class='publish'><a href="<?php echo admin_url(); ?>admin.php?page=mdjm-djs&display=djs"<?php if( isset( $display ) && $display == "djs" ) { ?> class="current" <?php } ?>>Active DJ's <span class="count">(<?php echo count( $active_djs ); ?>)</span></a> |</li>
				<li class='draft'><a href="<?php echo admin_url(); ?>admin.php?page=mdjm-djs&display=inactive_djs"<?php if( isset( $display ) && $display == "inactive_djs" ) { ?> class="current" <?php } ?>>Inactive DJ's <span class="count">(<?php echo count( $inactive_djs ); ?>)</span></a></li>
                </ul>
           <?php
		   }
		   if( isset( $which ) && $which == "bottom" )	{ // After table
			  
		   }
		} // extra_tablenav
	
		function get_columns()	{ // The table columns
			$columns = array();
			if( current_user_can( 'administrator' ) ) $columns['cb'] = '<input type="checkbox" />';
			$columns['dj_name'] = __( '<strong>Name</strong>', 'mdjmdjtable' );
			$columns['dj_email'] = __( '<strong>Email</strong>', 'mdjmdjtable' );
			$columns['dj_last_login'] = __( '<strong>Last Login</strong>', 'mdjmdjtable' );
			$columns['dj_active_events'] = __( '<strong>Active Events</strong>', 'mdjmdjtable' );
			$columns['dj_all_events'] = __( '<strong>Total Events</strong>', 'mdjmdjtable' );
			$columns['dj_next_event'] = __( '<strong>Next Event</strong>', 'mdjmdjtable' );
			$columns['dj_open_enquiries'] = __( '<strong>Open Enquiries</strong>', 'mdjmdjtable' );
			return $columns;
		} // get_columns
		
		function get_sortable_columns() { // Defines the columns by which users can sort the table
			$sortable_columns = array(
			'dj_name'   => array( 'dj_name', true ),
			'dj_email'   => array( 'dj_email', false ),
			'dj_email'   => array( 'dj_last_login', false ),
			'dj_active_events'   => array( 'active_events', false ),
			'dj_all_events'   => array( 'all_events', false ),
			'dj_next_event' => array( 'dj_next_event', false ),
			'dj_open_enquiries' => array( 'dj_open_enquiries', false ),
			);
			return $sortable_columns;
		} // get_sortable_columns
		
		function get_bulk_actions() { // Define the bulk actions for the drop down list
			if( current_user_can( 'administrator' ) )	{
				if( !isset( $_GET['display'] ) || $_GET['display'] != 'inactive_djs' )	{
					$actions = array(
								'inactive' => 'Mark Inactive',
								);
				}
				if( isset( $_GET['display'] ) && $_GET['display'] == 'inactive_djs' )	{
					$actions = array(
								'active' => 'Mark Active',
								);
				}
				return $actions;
			}
		} // get_bulk_actions
		
		function process_bulk_action() {
			$action = $this->current_action();
			$djs = isset( $_POST['djs'] ) ? $_POST['djs'] : false;
			if( !is_array( $djs ) )
				$djs = array( $djs );
			
			if( empty( $action ) )
				return;
			
			if( 'inactive' === $this->current_action() ) {
					f_mdjm_set_client_role( $djs, 'inactive_dj' );
			}
			if( 'active' === $this->current_action() ) {
					f_mdjm_set_client_role( $djs, 'dj' );
			}
		} // process_bulk_action
		
		function column_cb( $item ) { // Checkbox column
			return sprintf(
				'<input type="checkbox" name="djs[]" value="%s" />', $item['dj_id']
			);    
		} // column_cb
		
		function column_dj_name( $item ) {
			$actions = array(
					'edit' => sprintf( '<a href="'. admin_url() . '%s?user_id=%s">Edit</a>', 'user-edit.php', $item['dj_id'] ),
				);
			if( !isset( $_GET['display'] ) || $_GET['display'] != 'inactive_djs' )	{
				$actions['inactive_djs'] = sprintf( '<a href="?page=%s&action=%s&role=inactive_dj&dj_id=%s">Mark Inactive</a>', $_REQUEST['page'], 'set_client_role', $item['dj_id'] );
			}
			else	{
				$actions['active_dj'] = sprintf( '<a href="?page=%s&action=%s&role=dj&dj_id=%s">Mark Active</a>', $_REQUEST['page'], 'set_client_role', $item['dj_id'] );
			}
			return sprintf( '%1$s %2$s', $item['dj_name'], $this->row_actions( $actions ) );
		} // column_dj_name
				
		function prepare_items()	{
			$per_page = $this->get_items_per_page('djs_per_page', 25);
			$current_page = $this->get_pagenum();
			$this->process_bulk_action(); // Process bulk actions
			$columns  = $this->get_columns(); // Retrieve table columns
			$hidden   = array(); // Which fields are hidden
			$sortable = $this->get_sortable_columns(); // Which fields can be sorted by
			$this->_column_headers = array( $columns, $hidden, $sortable );
			$action = $this->current_action(); // Process current action
			$this->items = $this->get_djs(); // The data
		} // prepare_items
	} // Class
?>