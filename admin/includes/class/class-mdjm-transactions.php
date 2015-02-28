<?php
/**
 * class-mdjm-transactions.php
 * 25/02/2015
 * @since 1.1
 * The class for MDJM transactions
 *
 * @version 1.0
 */

	class MDJM_Transactions extends WP_List_Table	{
		function __construct() {
		   parent::__construct( array(
		  'singular'=> 'mdjm_transaction',
		  'plural' => 'mdjm_transactions',
		  'ajax'   => false
		  ) );
		} // __construct
		
		/**
		 * extra_tablnav
		 * Add navigational items above/below the main table
		 * @since 1.1
		 * 
		 */
		 function extra_tablenav( $which ) {
		   if ( $which == "top" ){
			  //The code that goes before the table is here
			  
		   }
		   if ( $which == "bottom" ){
			  //The code that goes after the table is here
			  
		   }
		} // extra_tablenav
		
		/**
		 * get_columns
		 * The columns to display
		 * @since 1.1
		 * 
		 */
		function get_columns() {
		   return $columns= array(
			  'col_trans_id'			=> __( 'ID' ),
			  'col_event_id'			=> __( 'Event' ),
			  'col_client' 			  => __( 'Client' ),
			  'col_dj' 			 	  => __( 'DJ' ),
			  'col_payment_src'		 => __( 'Source' ),
			  'col_payment_txn_id'	  => __( 'Transaction ID' ),
			  'col_payment_date'		=> __( 'Date' ),
			  'col_payment_type'		=> __( 'Type' ),
			  'col_payer_id'			=> __( 'Payer ID' ),
			  'col_payment_status'	  => __( 'Status' ),
			  'col_payer_name'		  => __( 'Payer' ),
			  'col_payer_email'		 => __( 'Payer Email' ),
			  'col_payment_for'		 => __( 'For' ),
			  'col_payment_currency'	=> __( 'Currency' ),
			  'col_payment_tax'		 => __( 'Tax' ),
			  'col_payment_gross'	   => __( 'Total' ),
			  'col_full_ipn'				 => __( 'IPN' ),
			  'col_see_by_admin'				=> __( 'Seen' ),
		   );
		} // get_columns
		
		/**
		 * get_sortable_columns
		 * The columns we can sort by
		 * @since 1.1
		 * 
		 */
		public function get_sortable_columns() {
		   return $sortable = array(
			  'col_trans_id'		=> array( 'trans_id', true ),
			  'col_event_id'		=> array( 'event_id', true ),
			  'col_payment_src'	 => array( 'payment_src', true ),
			  'col_payment_date'	=> array( 'payment_date', true ),
			  'col_payment_status'  => array( 'payment_status', true ),
			  'col_payer_name'	  => array( 'payer_name', true ),
			  'col_payment_for'	 => array( 'payment_for', true ),
		   );
		} // get_sortable_columns
		
		/**
		 * prepare_items
		 * Set the table up with needed params, pagination etc
		 * @since 1.1
		 * 
		 */
		function prepare_items( $search = NULL ) {
			global $wpdb, $_wp_column_headers, $mdjm_options;
			
			$screen = get_current_screen();
			
			if( !isset( $db_tbl ) )	{
				include( WPMDJM_PLUGIN_DIR . '/includes/config.inc.php' );
			}
		   /* -- Preparing your query -- */
			$query = 'SELECT * FROM `' . $db_tbl['trans'] . '`';
		
		   /* -- Ordering parameters -- */
			//Parameters that are going to be used to order the result
			$orderby = !empty( $_GET["orderby"] ) ? mysql_real_escape_string( $_GET["orderby"] ) : '';
			$order = !empty( $_GET["order"] ) ? mysql_real_escape_string( $_GET["order"] ) : 'ASC';
			
			if( !empty( $orderby ) & !empty( $order ) )	{
			   $query .= ' ORDER BY ' . $orderby . ' ' . $order;
			}
		
		   /* -- Pagination parameters -- */
			//Number of elements in your table?
			$totalitems = $wpdb->query( $query ); //return the total number of affected rows
			
			//How many to display per page?
			$perpage = $mdjm_options['items_per_page'];
			
			//Which page is this?
			$paged = !empty( $_GET["paged"] ) ? mysql_real_escape_string( $_GET["paged"] ) : '';
			
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
			//The pagination links are automatically built according to those parameters
		
			/* -- Register the Columns -- */
			$columns = $this->get_columns();
			$hidden = array( 
						'col_trans_id',
						'col_payment_txn_id',
						'col_payment_type',
						'col_payer_id',
						'col_payer_email',
						'col_payment_currency',
						'col_payment_tax',
						'col_full_ipn',
						'col_see_by_admin',
						);
			$sortable = $this->get_sortable_columns();
			$this->_column_headers = array( $columns, $hidden, $sortable );
		
			/* -- Fetch the items -- */
			if( $search != NULL )	{ // Search being performed
				$this->items = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM `" . $db_tbl['trans'] . "` WHERE `event_id` LIKE '%%%s%%' OR `payment_src` LIKE '%%%s%%' OR `payment_status` LIKE '%%%s%%'", $search, $search, $search ) );
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
			global $mdjm_options, $mdjm_currency;
		   //Get the records registered in the prepare_items method
			$records = $this->items;
		
		   //Get the columns registered in the get_columns and get_sortable_columns methods
			list( $columns, $hidden ) = $this->get_column_info();
			
			$i = 0;
			
		   //Loop for each record
			if( !empty( $records ) )	{
				foreach( $records as $rec )	{
					$eventinfo = f_mdjm_get_eventinfo_by_id( $rec->event_id );
					$clientinfo = get_userdata( $eventinfo->user_id );
					$djinfo = get_userdata( $eventinfo->event_dj );
		
			  //Open the line
				echo '<tr id="record_' . $rec->trans_id . '"';
				if( $rec->payment_status != 'Completed' )	{
					echo ' class="form-invalid"';
				}
				elseif( $i == 0 )	{
					echo ' class="alternate"';
				}
				echo '>' . "\n";
					foreach ( $columns as $column_name => $column_display_name ) {
		
						//Style attributes for each col
						$class = "class='$column_name column-$column_name'";
						$style = "";
						if ( in_array( $column_name, $hidden ) ) $style = ' style="display:none;"';
						$attributes = $class . $style;
						
						//edit link
						$editlink  = '/wp-admin/link.php?action=edit&link_id='.(int)$rec->trans_id;
						
						//Display the cell
						switch ( $column_name ) {
							case "col_trans_id":  
								echo '<td ' . $attributes . '>' . stripslashes( $rec->trans_id ) . '</td>' . "\n";
								break;
							case "col_event_id":
								echo '<td ' . $attributes . '>' . stripslashes( $rec->event_id ) . '</td>' . "\n";
								break;
							case "col_client":
								echo '<td ' . $attributes . '>' . stripslashes( $clientinfo->display_name ) . '</td>' . "\n";
								break;
							case "col_dj":
								echo '<td ' . $attributes . '>' . stripslashes( $djinfo->display_name ) . '</td>' . "\n";
								break;
							case "col_payment_src":
								echo '<td ' . $attributes . '>' . stripslashes( $rec->payment_src ) . '</td>' . "\n";
								break;
							case "col_payment_txn_id":
								echo '<td ' . $attributes . '>' . stripslashes( $rec->payment_txn_id ) . '</td>' . "\n";
								break;	
							case "col_payment_date":
								echo '<td ' . $attributes . '>' . date( $mdjm_options['short_date_format'], strtotime( $rec->payment_date ) ) . '</td>' . "\n";
								break;
							case "col_payment_type":
								echo '<td ' . $attributes . '>' . stripslashes( $rec->payment_type ) . '</td>' . "\n";
								break;
							case "col_payer_id":
								echo '<td ' . $attributes . '>' . stripslashes( $rec->payer_id ) . '</td>' . "\n";
								break;
							case "col_payment_status":
								echo '<td ' . $attributes . '>' . stripslashes( $rec->payment_status ) . '</td>' . "\n";
								break;
							case "col_payer_name":
								echo '<td ' . $attributes . '>' . stripslashes( $rec->payer_firstname . ' ' . $rec->payer_lastname ) . '</td>' . "\n";
								break;
							case "col_payer_email":
								echo '<td ' . $attributes . '><a href="mailto:' . $rec->payer_email . '">' . $rec->payer_email . '</a></td>' . "\n";
								break;
							case "col_payment_for":
								echo '<td ' . $attributes . '>' . stripslashes( $rec->payment_for ) . '</td>' . "\n";
								break;
							case "col_payment_currency":
								echo '<td ' . $attributes . '>' . $rec->payment_currency . '</td>' . "\n";
								break;
							case "col_payment_tax":
								echo '<td ' . $attributes . '>' . $mdjm_currency[$mdjm_options['currency']] . $rec->payment_tax . '</td>' . "\n";
								break;
							case "col_payment_gross":
								echo '<td ' . $attributes . '>' . $mdjm_currency[$mdjm_options['currency']] . $rec->payment_gross . '</td>' . "\n";
								break;
							case "col_payment_ipn":
								echo '<td ' . $attributes . '>' . $rec->full_ipn . '</td>' . "\n";
								break;
							case "col_seen":
								echo '<td ' . $attributes . '>' . $rec->seen_by_admin . '</td>' . "\n";
								break;
						} // switch
				  }
		
			  //Close the line
			  echo'</tr>' . "\n";
			  $i++;
			  if( $i == 2 ) $i = 0;
			  }
		   }
		} // display_rows
		
	} // class MDJM_Transactions
 
?>