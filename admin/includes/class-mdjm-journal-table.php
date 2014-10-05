<?php
	class MDJM_Journal_Table extends WP_List_Table	{
		private function get_journal()	{
			global $wpdb;
			include ( WPMDJM_PLUGIN_DIR . '/includes/config.inc.php' );
			
			// Build the data query
			$query = 'SELECT * FROM `'.$db_tbl['journal'].'` WHERE';
			if( isset( $_GET['client_id'] ) ) $query .= ' `client` = ' . $_GET['client_id'];
			if( isset( $_GET['client_id'], $_GET['event_id'] ) ) $query .= ' AND';
			if( isset( $_GET['event_id'] ) ) $query .= ' `event` = ' . $_GET['event_id'];
			
			if (isset ( $_GET['orderby'] ) ) $orderby = $_GET['orderby'];
			else $orderby = 'timestamp';
			
			if (isset ( $_GET['order'] ) ) $order = $_GET['order'];
			else $order = 'DESC';
			
			$query .= ' ORDER BY `' . $orderby . '` ' . $order;
			
			$clientinfo = get_userdata( $client );
			$journalinfo = $wpdb->get_results( $query );
			
			$journal_data = array();
			foreach( $journalinfo as $journal )	{
				if( $journal->author == 0 )	{
					$author = 'System';
				}
				else	{
					$authorinfo = get_userdata( $journal->author );
					$author = $authorinfo->display_name;
				}
				$journal_data[] = array(
									'id' => $journal->id,
									'timestamp' => date( 'd M Y', $journal->timestamp ),
									'author' => $author,
									'type' => $journal->type,
									'source' => $journal->source,
									'entry' =>  $journal->entry,
								);
			}
			return $journal_data;
		} // get_journal
		
		function no_items() {
			_e( 'There are currently no Journal entries for this client and/or event' );
		}
		
		function __construct()	{
			global $status, $page;
	
			parent::__construct( array(
				'singular'  => __( 'journal', 'mdjmjournaltable' ),     //singular name of the listed records
				'plural'    => __( 'journals', 'mdjmjournaltable' ),   //plural name of the listed records
				'ajax'      => false        //does this table support ajax?
			) );
			add_action( 'admin_head', array( &$this, 'admin_header' ) ); // Call the function to style the table
		} // __construct
		
		function admin_header() {
			$page = ( isset($_GET['page'] ) ) ? esc_attr( $_GET['page'] ) : false;
			if( 'mdjm-events' != $page )
			return; 
			
			echo '<style type="text/css">';
			echo '.wp-list-table .column-timestamp { width: 15%; }';
			echo '.wp-list-table .column-author { width: 15%; }';
			echo '.wp-list-table .column-type { width: 15%; }';
			echo '.wp-list-table .column-source { width: 15%; }';
			echo '.wp-list-table .column-entry { width: 40%; }';
			echo '</style>';
		} // admin_header
	
		function column_default( $item, $column_name )	{
			switch( $column_name )	{ 
				case 'id':
				case 'timestamp':
				case 'author':
				case 'type':
				case 'source':
				case 'entry':
					return $item[ $column_name ];
				default:
					return print_r( $item, true ) ; // Show the whole array for troubleshooting purposes
			}
		} // column_default
	
		function get_columns()	{ // The table columns
			$columns = array(
				'id' => __( '<strong>id</strong>', 'mdjmjournaltable' ),
				'timestamp' => __( '<strong>Date</strong>', 'mdjmjournaltable' ),
				'author' => __( '<strong>Author</strong>', 'mdjmjournaltable' ),
				'type' => __( '<strong>Entry Type</strong>', 'mdjmjournaltable' ),
				'source' => __( '<strong>Source of Entry</strong>', 'mdjmjournaltable' ),
				'entry' => __( '<strong>Journal Entry</strong>', 'mdjmjournaltable' ),
			);
			 return $columns;
		} // get_columns
		
		function get_sortable_columns() { // Defines the columns by which users can sort the table
			$sortable_columns = array(
			'timestamp'  => array( 'timestamp', false ),
			'author'   => array( 'author', true ),
			'type'   => array( 'type', false ),
			'source'   => array( 'source', false ),
			);
			return $sortable_columns;
		} // get_sortable_columns
		
		function prepare_items()	{
			$per_page = $this->get_items_per_page( 'entries_per_page', 25 );
			$current_page = $this->get_pagenum();
			$columns  = $this->get_columns(); // Retrieve table columns
			$hidden   = array( 'id' ); // Which fields are hidden
			$sortable = $this->get_sortable_columns(); // Which fields can be sorted by
			$this->_column_headers = array( $columns, $hidden, $sortable );
			$this->items = $this->get_journal(); // The data
		} // prepare_items
	} // Class
?>