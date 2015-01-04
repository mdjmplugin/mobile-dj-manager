<?php
	class MDJM_Venues_Table extends WP_List_Table	{
		private function get_venues()	{
			global $wpdb, $mdjm_options, $query;
			include ( WPMDJM_PLUGIN_DIR . '/includes/config.inc.php' );
			// Build the data query
			$query = 'SELECT * FROM `' . $db_tbl['venues'] . '`';
			
			if( isset( $_POST['s'] ) && !empty( $_POST['s'] ) )	{
				$query .= " WHERE `venue_name` LIKE '%" . $_POST['s'] . "%'";
			}
			
			if( isset( $_GET['orderby'] ) ) $orderby = $_GET['orderby'];
			else $orderby = 'venue_name';
			
			if( isset( $_GET['order'] ) ) $order = $_GET['order'];
			else $order = 'ASC';
			
			$query .= ' ORDER BY `' . $orderby . '` ' . $order;
			
			/* Pagination (but not for searches)*/
			if( !isset( $_POST['s'] ) || empty( $_POST['s'] ) )	{
				$per_page = $mdjm_options['items_per_page'];
				$current_page = $this->get_pagenum();
				$total_items = $wpdb->query( $query );
				$total_pages = ceil( $total_items/$per_page );
				
				$paged = !empty( $_GET['paged'] ) ? mysql_real_escape_string( $_GET['paged'] ) : '';
				
				if( empty( $paged ) || !is_numeric( $paged ) || $paged <= 0 )	{
					$paged = 1;
				}
				
				if( !empty( $paged ) && !empty( $per_page ) )	{
					$offset = ( $paged - 1 ) * $per_page;
					$query .= ' LIMIT ' . (int)$offset . ',' . (int)$per_page;
				}
				
				$this->set_pagination_args( array(
											'total_items' => $total_items,
											'per_page'    => $per_page,
											'total_pages' => $total_pages,
												)
											);
			}
			
			$venueinfo = $wpdb->get_results( $query );
			
			$venue_data = array();
			foreach( $venueinfo as $venue )	{
				$venue_data[] = array(
									'venue_id' => $venue->venue_id,
									'venue_name' => esc_attr( $venue->venue_name ),
									'venue_address1' => esc_attr( $venue->venue_address1 ),
									'venue_address2' =>  esc_attr( $venue->venue_address2 ),
									'venue_town' =>  esc_attr( $venue->venue_town ),
									'venue_county' => esc_attr( $venue->venue_county ),
									'venue_postcode' => esc_attr( $venue->venue_postcode ),
									'venue_contact' => esc_attr( $venue->venue_contact ),
									'venue_phone' => esc_attr( $venue->venue_phone ),
									'venue_email' => esc_attr( $venue->venue_email ),
									'venue_information' => esc_attr( $venue->venue_information ),
								);
			}
			return $venue_data;
		} // venue_data
		
		function no_items() {
			_e( 'There are currently no venues loaded within the application' );
		}
		
		function __construct()	{
			global $status, $page;
	
			parent::__construct( array(
				'singular'  => __( 'venue', 'mdjmvenuetable' ),     //singular name of the listed records
				'plural'    => __( 'venues', 'mdjmvenuestable' ),   //plural name of the listed records
				'ajax'      => false        //does this table support ajax?
			) );
			add_action( 'admin_head', array( &$this, 'admin_header' ) ); // Call the function to style the table
		} // __construct
		
		function admin_header() {
			$page = ( isset( $_GET['page'] ) ) ? esc_attr( $_GET['page'] ) : false;
			if( 'mdjm-venues' != $page )
			return; 
			
			echo '<style type="text/css">';
			echo '.wp-list-table .column-venue_name { width: 15%; }';
			echo '.wp-list-table .column-venue_town { width: 15%; }';
			echo '.wp-list-table .column-venue_county { width: 15%; }';
			echo '.wp-list-table .column-venue_contact { width: 15%; }';
			echo '.wp-list-table .column-venue_phone { width: 15%; }';
			echo '.wp-list-table .column-venue_email { width: 15%; }';
			echo '.wp-list-table .column-information { width: 10%; }';
			echo '</style>';
		} // admin_header
	
		function column_default( $item, $column_name )	{
			switch( $column_name )	{ 
				case 'venue_id':
				case 'venue_name':
				case 'venue_address1':
				case 'venue_address2':
				case 'venue_town':
				case 'venue_county':
				case 'venue_postcode':
				case 'venue_contact':
				case 'venue_phone':
				case 'venue_email':
				case 'venue_information':
					return $item[ $column_name ];
				default:
					return print_r( $item, true ) ; // Show the whole array for troubleshooting purposes
			}
		} // column_default
	
		function get_columns()	{ // The table columns
			$columns = array(
				'cb'        => '<input type="checkbox" />',
				'venue_id' => __( '<strong>ID</strong>', 'mdjmvenuetable' ),
				'venue_name' => __( '<strong>Name</strong>', 'mdjmvenuetable' ),
				'venue_contact' => __( '<strong>Contact</strong>', 'mdjmvenuetable' ),
				'venue_address1' => __( '<strong>Address 1</strong>', 'mdjmvenuetable' ),
				'venue_address2' => __( '<strong>Address 2</strong>', 'mdjmvenuetable' ),
				'venue_town' => __( '<strong>Town</strong>', 'mdjmvenuetable' ),
				'venue_county' => __( '<strong>County</strong>', 'mdjmvenuetable' ),
				'venue_postcode' => __( '<strong>Post Code</strong>', 'mdjmvenuetable' ),
				'venue_phone' => __( '<strong>Phone</strong>', 'mdjmvenuetable' ),
				'venue_email' => __( '<strong>Email</strong>', 'mdjmvenuetable' ),
				'venue_information' => __( '<strong>Information</strong>', 'mdjmvenuetable' ),
			);
			 return $columns;
		} // get_columns
		
		function get_sortable_columns() { // Defines the columns by which users can sort the table
			$sortable_columns = array(
			'venue_id'  => array( 'venue_id', false ),
			'venue_name'   => array( 'venue_name', true ),
			'venue_town'   => array( 'venue_town', false ),
			'venue_county'   => array( 'venue_county', false ),
			);
			return $sortable_columns;
		} // get_sortable_columns
		
		function get_bulk_actions() { // Define the bulk actions for the drop down list
			$actions = array(
						'delete' => 'Delete',
						);
			return $actions;
		} // get_bulk_actions
		
		function process_bulk_action() {
			$action = $this->current_action();
			$venues = isset( $_POST['venue'] ) ? $_POST['venue'] : false;
			if( !is_array( $venues ) )
				$venues = array( $venues );
			
			if( empty( $action ) )
				return;
			
			if( 'delete' === $this->current_action() ) {
					f_mdjm_delete_venue( $venues );
			}
		} // process_bulk_action
		
		function column_cb( $item ) { // Checkbox column
			return sprintf(
				'<input type="checkbox" name="venue[]" value="%s" />', $item['venue_id']
			);    
		} // column_cb
		
		function column_venue_name( $item ) {
			$actions = array();
			$actions['edit'] = sprintf( '<a href="?page=%s&action=%s&venue_id=%s">Edit</a>', $_REQUEST['page'], 'view_venue_form', $item['venue_id'] );
			$actions['delete'] = sprintf( '<a href="?page=%s&action=%s&venue_id=%s">Delete</a>', $_REQUEST['page'], 'delete_venue', $item['venue_id'] );
			
			return sprintf( '%1$s %2$s', $item['venue_name'], $this->row_actions( $actions ) );
		}
		
		function extra_tablenav( $which )	{ // Determine what is to be shown before and after the table
			global $wpdb, $query;
			if( isset( $which ) && $which == "top" ){ // Before table
		   ?>
				<ul class='subsubsub'>
				<li class='publish'><a class="current">All Venues <span class="count">(<?php echo count( $wpdb->get_results( $query ) ); ?>)</span></a></li>
                </ul>
           <?php
		   }
		   if( isset( $which ) && $which == "bottom" )	{ // After table
			  
		   }
		} // extra_tablenav
		
		function prepare_items()	{
			$per_page = $this->get_items_per_page( 'venues_per_page', 25 );
			$current_page = $this->get_pagenum();
			$this->process_bulk_action(); // Process bulk actions
			$columns  = $this->get_columns(); // Retrieve table columns
			$hidden   = array( 'venue_id', 'venue_address1', 'venue_address2', 'venue_postcode' ); // Which fields are hidden
			$sortable = $this->get_sortable_columns(); // Which fields can be sorted by
			$this->_column_headers = array( $columns, $hidden, $sortable );
			$this->items = $this->get_venues(); // The data
		} // prepare_items
	} // Class
?>