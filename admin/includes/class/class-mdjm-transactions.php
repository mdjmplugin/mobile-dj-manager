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
		 * monthly_filter
		 * Displays the monthly filter drop down
		 * @since 1.1.1
		 * 
		 */
		function monthly_filter()	{
			global $wpdb, $wp_locale;
			
			include( WPMDJM_PLUGIN_DIR . '/includes/config.inc.php' );
			
			$months = $wpdb->get_results("
				SELECT DISTINCT YEAR( payment_date ) AS year, MONTH( payment_date ) AS month
				FROM `" . $db_tbl['trans'] . "`
				ORDER BY payment_date DESC" );
				
			$month_count = count( $months );
			
			if ( !$month_count || ( 1 == $month_count && 0 == $months[0]->month ) )
			return;

			$m = isset( $_GET['m'] ) ? (int) $_GET['m'] : 0;
			
			?>
            <label for="filter-by-date" class="screen-reader-text">Filter by</label>
            <select name="m" id="filter-by-date">
                <option<?php selected( $m, 0 ); ?> value="0"><?php _e( 'All dates' ); ?></option>
			<?php
			foreach ( $months as $arc_row ) {
				if ( 0 == $arc_row->year )
					continue;
	
				$month = zeroise( $arc_row->month, 2 );
				$year = $arc_row->year;
	
				printf( "<option %s value='%s'>%s</option>\n",
					selected( $m, $year . $month, false ),
					esc_attr( $arc_row->year . $month ),
					/* translators: 1: month name, 2: 4-digit year */
					sprintf( __( '%1$s %2$d' ), $wp_locale->get_month( $month ), $year )
				);
			}
			?>
		</select>
        <?php
		}
		
		/**
		 * extra_tablnav
		 * Add navigational items above/below the main table
		 * @since 1.1
		 * 
		 */
		function extra_tablenav( $which ) {
			?>
            <form id="trans-filter" method="get" action="">
            <input type="hidden" name="page" value="<?php echo $_REQUEST['page']; ?>" />
            <div class="alignleft actions">
            <?php
			if ( $which == "top" ){
			   
			$this->monthly_filter();
			
			submit_button( __( 'Filter' ), 'button', 'filter_action', false, array( 'id' => 'trans-query-submit' ) );
			
			}
			if ( $which == "bottom" ){
			  //The code that goes after the table is here
			  
			}
			?>
            </div>
            </form>
            <?php
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
			  'col_dj' 			 	  => __( 'DJ' ),
			  'col_payment_txn_id'	  => __( 'Transaction ID' ),
			  'col_payment_date'		=> __( 'Date' ),
			  'col_direction'		   => __( 'Direction' ),
			  'col_event_id'			=> __( 'Booking Ref' ),
			  'col_payment_gross'	   => __( 'Amount' ),
			  'col_payment_for'		 => __( 'Details' ),
			  'col_payment_src'		 => __( 'Source' ),
			  'col_payment_status'	  => __( 'Status' ),
			  'col_client' 			  => __( 'Client' ),
			  'col_payment_type'		=> __( 'Type' ),
			  'col_payer_id'			=> __( 'Payer ID' ),
			  'col_payer_name'		  => __( 'Payer' ),
			  'col_payer_email'		 => __( 'Payer Email' ),
			  'col_payment_currency'	=> __( 'Currency' ),
			  'col_payment_tax'		 => __( 'Tax' ),
			  'col_full_ipn'			=> __( 'IPN' ),
			  'col_see_by_admin'		=> __( 'Seen' ),
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
			  'col_trans_id'		=> array( 'trans_id', false ),
			  'col_direction'	   => array( 'direction', false ),
			  'col_event_id'		=> array( 'event_id', false ),
			  'col_payment_src'	 => array( 'payment_src', false ),
			  'col_payment_date'	=> array( 'payment_date', true ),
			  'col_payment_status'  => array( 'payment_status', false ),
			  'col_payer_name'	  => array( 'payer_name', false ),
			  'col_payment_for'	 => array( 'payment_for', false ),
		   );
		} // get_sortable_columns
		
		/**
		 * date_actions
		 * Row actions for the Date column
		 * @since 1.1
		 * 
		 */
		function date_actions( $item )	{
			global $mdjm_options;

			$actions = array(
					'view_transaction' => sprintf( '<a href="' . admin_url( 'admin.php?page=%s&action=%s&transaction=%s' ) . '">View Details</a>', $_REQUEST['page'], 'view_transaction', $item->trans_id ),
				);
			
			return $this->row_actions( $actions );
		} // date_actions
		
		/**
		 * booking_ref_actions
		 * Row actions for the Date column
		 * @since 1.1
		 * 
		 */
		function booking_ref_actions( $item )	{
			$actions = array(
					'view_event' => sprintf( '<a href="' . admin_url( 'admin.php?page=%s&action=%s&event_id=%s' ) . '">View Event</a>', 'mdjm-events', 'view_event_form', $item->event_id ),
				);
			
			return $this->row_actions( $actions );
		} // booking_ref_actions
		
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
			
			/* -- Filtering parameters -- */
			$m = !empty( $_GET['m'] ) ? $_GET['m'] : false;
			
			if( !empty( $m ) )	{
				$query .= ' WHERE year(payment_date) = ' . substr( $m, 0, 4 ) . ' AND month(payment_date) = ' . substr( $m, -2 );	
			}
		
		   /* -- Ordering parameters -- */
			//Parameters that are going to be used to order the result
			$orderby = !empty( $_GET['orderby'] ) ? mysql_real_escape_string( $_GET['orderby'] ) : 'payment_date';
			$order = !empty( $_GET['order'] ) ? mysql_real_escape_string( $_GET['order'] ) : 'ASC';
			
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
						'col_client',
						'col_dj',
						'col_payment_txn_id',
						'col_payment_type',
						'col_payer_id',
						'col_payer_name',
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
								echo '<td ' . $attributes . '>' . $rec->trans_id . '</td>' . "\n";
								break;
							case "col_direction":
								if( $rec->direction == 'In' )	{
									$color = '#090';	
								}
								else	{
									$color = '#F00';	
								}
								echo '<td ' . $attributes . '><font style="color: ' . $color . '">' . $rec->direction . '</font></td>' . "\n";
								break;
							case "col_event_id":
								$event_id = !empty( $mdjm_options['id_prefix'] ) ? $mdjm_options['id_prefix'] . $rec->event_id : $rec->event_id;
								echo '<td ' . $attributes . '>' . $event_id;
								echo $this->booking_ref_actions( $rec );
								echo '</td>' . "\n";
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
								echo '<td ' . $attributes . '>' . $rec->payment_txn_id . '</td>' . "\n";
								break;	
							case "col_payment_date":
								echo '<td ' . $attributes . '>' . date( $mdjm_options['short_date_format'], strtotime( $rec->payment_date ) );
								//echo $this->date_actions( $rec );
								echo '</td>' . "\n";
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
								if( $rec->direction == 'In' )	{
									$color = '#090';	
								}
								else	{
									$color = '#F00';	
								}
								echo '<td ' . $attributes . '><font style="color: ' . $color . '">' . $mdjm_currency[$mdjm_options['currency']] . $rec->payment_gross . '</font></td>' . "\n";
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
		
		/*
		 * drop_transaction_types
		 * 06/03/2015
		 * @since 1.1.1
		 * Display drop down list of transaction types
		 */
		function drop_transaction_types()	{
			if( !isset( $db_tbl ) )	{
				include( WPMDJM_PLUGIN_DIR . '/includes/config.inc.php' );
			}
			$mdjm_pp_options = get_option( 'mdjm_pp_options' );
			$client_dialogue = get_option( WPMDJM_FETEXT_SETTINGS_KEY );
			
			$types = explode( "\n", $mdjm_pp_options['pp_transaction_types'] );
			
			array_unshift( $types, $client_dialogue['balance_label'], $client_dialogue['deposit_label'] );
			asort( $types );
			
			echo '<select name="payment_for" id="transaction_type" class="regular-text">' . "\r\n";
			echo '<option value="">--- Select ---</option>' . "\r\n";

			foreach( $types as $type )	{
				echo '<option value="' . $type . '">' . $type . '</option>' . "\r\n";
			}
			
			echo '</select>' . "\r\n";
		} // drop_transaction_types
		
		/*
		 * drop_payment_source
		 * 06/03/2015
		 * @since 1.1.1
		 * Display drop down list of payment sources
		 */
		function drop_payment_source()	{
			if( !isset( $db_tbl ) )	{
				include( WPMDJM_PLUGIN_DIR . '/includes/config.inc.php' );
			}
			$mdjm_pp_options = get_option( 'mdjm_pp_options' );
			
			$sources = explode( "\n", $mdjm_pp_options['pp_payment_sources'] );
			
			asort( $sources );
			
			echo '<select name="payment_src" id="payment_source" class="regular-text">' . "\r\n";
			echo '<option value="">--- Select ---</option>' . "\r\n";

			foreach( $sources as $source )	{
				echo '<option value="' . $source . '">' . $source . '</option>' . "\r\n";
			}
			
			echo '</select>' . "\r\n";
		} // drop_payment_source
		
		/*
		 * single_event_transactions
		 * 06/03/2015
		 * @since 1.1.1
		 * Obtain transactions for a single event
		 */
		function single_event_transactions( $event_id )	{
			if( !isset( $db_tbl ) )	{
				include( WPMDJM_PLUGIN_DIR . '/includes/config.inc.php' );
			}
			
			$transactions = $wpdb->get_results( "SELECT * FROM `" . $db_tbl['trans'] . "` WHERE `event_id` = '" . $event_id . "' AND `payment_status` = 'Completed'" );

			return $transactions;
		} // single_event_transactions
		
		/*
		 * add_transaction
		 * 06/03/2015
		 * @since 1.1.1
		 * Display drop down list of payment sources
		 */
		function add_transaction( $post_data )	{
			global $wpdb, $current_user;
			
			if( !isset( $db_tbl ) )	{
				include( WPMDJM_PLUGIN_DIR . '/includes/config.inc.php' );
			}
			
			$trans_update = array();
			$trans_update['event_id'] = '0';
			$trans_update['payment_status'] = 'Completed';
			if( !empty( $post_data['event_id'] ) )	{
				$trans_update['event_id'] = $post_data['event_id'];	
			}
			if( !empty( $post_data['payment_status'] ) )	{
				$trans_update['payment_status'] = $post_data['payment_status'];	
			}
			
			/* -- Validate -- */
			if( !isset( $post_data['payment_gross'] ) || empty( $post_data['payment_gross'] ) )	{
				$class = 'error';
				$message = 'ERROR: You must enter an amount for the transaction';	
			}
			elseif( !isset( $post_data['payment_date'] ) || empty( $post_data['payment_date'] ) )	{
				$class = 'error';
				$message = 'ERROR: You must enter a date for the transaction';	
			}
			elseif( !isset( $post_data['payment_for'] ) || empty( $post_data['payment_for'] ) )	{
				$class = 'error';
				$message = 'ERROR: You must enter details of the transaction';	
			}
			elseif( !isset( $post_data['payment_src'] ) || empty( $post_data['payment_src'] ) )	{
				$class = 'error';
				$message = 'ERROR: You must enter the payment source for the transaction';	
			}
			else	{
				unset( $post_data['trans_date'], $post_data['action'], $post_data['_wpnonce'], $post_data['_wp_http_referer'], $post_data['submit'] );
				
				if( $post_data['direction'] == 'Out' && !isset( $post_data['payer_firstname'], $post_data['payer_lastname'], $post_data['payer_email'] ) )	{
					$post_data['payer_firstname'] = $current_user->user_firstname;
					$post_data['payer_lastname'] = $current_user->user_lastname;
					$post_data['payer_email'] = $current_user->user_email;
				}
				elseif( $post_data['direction'] == 'In' && empty( $post_data['payment_from'] ) )	{
					$eventinfo = f_mdjm_get_eventinfo_by_id( $post_data['event_id'] );
					$client = get_userdata( $eventinfo->user_id );
					$post_data['payer_firstname'] = $client->first_name;
					$post_data['payer_lastname'] = $client->last_name;
					$post_data['payer_email'] = $client->user_email;
				}
				else	{
					$post_data['payer_firstname'] = $post_data['payment_from'];
				}
				if( isset( $post_data['payment_from'] ) )	{
					unset( $post_data['payment_from'] );
				}
				
				$post_data['payment_gross'] = number_format( $post_data['payment_gross'], 2 );
				foreach( $post_data as $field => $value )	{
					$trans_update[$field] = $value;
				}
				$insert = $wpdb->insert( $db_tbl['trans'], $trans_update );
				/* -- Failure -- */
				if( !$insert )	{
					$class = 'error';
					$message = 'ERROR: Unable to add transaction';
				}
				else	{
					$class = 'updated';
					$message = 'Transaction successfully added';	
				}
			}
			
			return f_mdjm_update_notice( $class, $message );
		} // add_transaction
		
	} // class MDJM_Transactions
 
?>