<?php
/**
 * class-mdjm-events-table.php
 * 25/02/2015
 * @since 1.1
 * The class to display the Events table
 *
 * @version 1.0
 */

	class MDJM_Events_Table extends WP_List_Table	{
		function __construct() {
			parent::__construct( array(
				'singular'=> 'mdjm_event',
				'plural' => 'mdjm_events',
				'ajax'   => false
			) );
		} // __construct
		
		/**
		 * no_items
		 * Text displayed when no events have been found
		 * @since 1.1
		 * 
		 */
		 function no_items()	{
			_e( 'No events exist yet. <a href="' . admin_url( 'admin.php?page=mdjm-events&action=add_event_form' ) . '">Create one</a>' );
		 } // no_items

		/**
		 * monthly_filter
		 * Displays the monthly filter drop down
		 * @since 1.1.1
		 * 
		 */
		function monthly_filter()	{
			global $wpdb, $wp_locale;
			
			include( WPMDJM_PLUGIN_DIR . '/includes/config.inc.php' );
			
			$month_query = "SELECT DISTINCT YEAR( event_date ) AS year, MONTH( event_date ) AS month
				FROM `" . $db_tbl['events'] . "`";
				
			if( $this->status != 'All' )	{
				$month_query .= " WHERE `contract_status` = '" . $this->status . "'";
				if( !current_user_can( 'administrator' ) )
					$month_query .= " AND `event_dj` = '" . get_current_user_id() . "'";
			}
				
			elseif( !current_user_can( 'administrator' ) )	{
				$month_query .= " WHERE `event_dj` = '" . get_current_user_id() . "'";
			}
				
			$month_query .= " ORDER BY event_date DESC";
			
			$months = $wpdb->get_results( $month_query );
				
			$month_count = count( $months );
			
			if ( !$month_count || ( 1 == $month_count && 0 == $months[0]->month ) )
			return;

			$m = isset( $_GET['m'] ) ? (int) $_GET['m'] : 0;
			
			?>
            <label for="filter-by-date" class="screen-reader-text">Filter by Date</label>
            <select name="m" id="filter-by-date">
                <option<?php selected( $m, 0 ); ?> value="0"><?php _e( 'All Dates' ); ?></option>
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
		} // monthly_filter
		
		/**
		 * event_type_filter
		 * Displays the monthly filter drop down
		 * @since 1.1.1
		 * 
		 */
		function event_type_filter()	{
			global $wpdb;
			
			include( WPMDJM_PLUGIN_DIR . '/includes/config.inc.php' );
			
			$types_query = "SELECT DISTINCT event_type FROM `" . $db_tbl['events'] . "`";
			
			if( $this->status != 'All' )	{
				$types_query .= " WHERE `contract_status` = '" . $this->status . "'";
				if( !current_user_can( 'administrator' ) )
					$types_query .= " AND `event_dj` = '" . get_current_user_id() . "'";
			}
			elseif( !current_user_can( 'administrator' ) )	{
				$types_query .= " WHERE `event_dj` = '" . get_current_user_id() . "'";
			}
				
			$types_query .= " ORDER BY event_type ASC";
			
			$types = $wpdb->get_results( $types_query );
				
			$type_count = count( $types );
			
			if ( !$type_count || $type_count == 1 )
				return;

			$t = isset( $_GET['t'] ) ? $_GET['t'] : false;
			
			?>
            <label for="filter-by-type" class="screen-reader-text">Filter by Type</label>
            <select name="t" id="filter-by-type">
                <option<?php selected( $t, '' ); ?> value=""><?php _e( 'All Types' ); ?></option>
			<?php
			foreach ( $types as $type ) {
				?>
				<option value="<?php echo $type->event_type; ?>"<?php selected( $t, $type->event_type ); ?>><?php echo esc_attr( $type->event_type ); ?></option>
                <?php
			}
			?>
            </select>
			<?php
		} // event_type_filter
		
		/**
		 * dj_filter
		 * Displays the DJ filter drop down
		 * @since 1.1.1
		 * 
		 */
		function dj_filter()	{
			global $wpdb;
			
			include( WPMDJM_PLUGIN_DIR . '/includes/config.inc.php' );
			
			$dj_query = "SELECT DISTINCT event_dj FROM `" . $db_tbl['events'] . "`";
			
			if( $this->status != 'All' )	{
				$dj_query .= " WHERE `contract_status` = '" . $this->status . "'";
				if( !current_user_can( 'administrator' ) )
					$dj_query .= " AND `event_dj` = '" . get_current_user_id() . "'";
			}
			elseif( !current_user_can( 'administrator' ) )	{
				$dj_query .= " WHERE `event_dj` = '" . get_current_user_id() . "'";
			}
			
			$dj_query .= " ORDER BY event_dj ASC";
			
			$dj_list = $wpdb->get_results( $dj_query );
			
			foreach( $dj_list as $d )	{
				$include[] = $d->event_dj;
			}
						
			$djs = get_users( array( 'include' => $include,  'orderby' => 'display_name', 'order' => 'ASC' ) );
							
			$dj_count = count( $djs );
			
			$dj = isset( $_GET['dj'] ) ? $_GET['dj'] : '0';
			
			if ( !$dj_count || $dj_count == 1 || !current_user_can( 'administrator' ) )
				return;

			?>
            <label for="filter-by-dj" class="screen-reader-text">Filter by DJ</label>
            <select name="dj" id="filter-by-dj">
                <option<?php selected( $dj, '' ); ?> value=""><?php _e( 'All DJ\'s' ); ?></option>
			<?php
			foreach ( $djs as $emp ) {
				?>
				<option value="<?php echo $emp->ID; ?>"<?php selected( $dj, $emp->ID ); ?>><?php echo $emp->display_name; ?></option>
                <?php
			}
			?>
            </select>
			<?php
		} // dj_filter
		
		/**
		 * client_filter
		 * Displays the Client filter drop down
		 * @since 1.1.1
		 * 
		 */
		function client_filter()	{
			global $wpdb;
			
			include( WPMDJM_PLUGIN_DIR . '/includes/config.inc.php' );
			
			$client_query = "SELECT DISTINCT user_id FROM `" . $db_tbl['events'] . "`";
			
			if( $this->status != 'All' )	{
				$client_query .= " WHERE `contract_status` = '" . $this->status . "'";
				if( !current_user_can( 'administrator' ) )
					$client_query .= " AND `event_dj` = '" . get_current_user_id() . "'";	
			}
			elseif( !current_user_can( 'administrator' ) )	{
				$client_query .= " WHERE `event_dj` = '" . get_current_user_id() . "'";
			}
				
			$client_query .= " ORDER BY user_id ASC";
			
			$client_list = $wpdb->get_results( $client_query );
			
			foreach( $client_list as $c )	{
				$include[] = $c->user_id;
			}
						
			$clients = get_users( array( 'include' => $include,  'orderby' => 'display_name', 'order' => 'ASC' ) );
							
			$client_count = count( $clients );
			
			if ( !$client_count || $client_count == 1 )
				return;
			
			$c = isset( $_GET['c'] ) ? $_GET['c'] : '0';
			
			if ( !$client_count )
				return;

			?>
            <label for="filter-by-client" class="screen-reader-text">Filter by Client</label>
            <select name="c" id="filter-by-client">
                <option<?php selected( $c, '0' ); ?> value=""><?php _e( 'All Client\'s' ); ?></option>
			<?php
			foreach ( $clients as $client ) {
				if( current_user_can( 'administrator' ) || f_mdjm_client_is_mine( $client->ID ) )	{
					?>
					<option value="<?php echo $client->ID; ?>"<?php selected( $c, $client->ID ); ?>><?php echo $client->display_name; ?></option>
					<?php
				}
			}
			?>
            </select>
			<?php
		} // client_filter
		
		/**
		 * extra_tablnav
		 * Add navigational items above/below the main table
		 * @since 1.1
		 * 
		 */
		function extra_tablenav( $which ) {
			global $wpdb;
			
			if( !isset( $db_tbl ) )	{
				include( WPMDJM_PLUGIN_DIR . '/includes/config.inc.php' );
			}
			
			/* -- All Events -- */
			$all_events_query = "SELECT * FROM `" . $db_tbl['events'] . "`";
				if( !current_user_can( 'administrator' ) )
				$all_events_query .= " WHERE `event_dj` = '" . get_current_user_id() . "'";
			
			$default_status = 'Approved';
			/* -- Check for Unattended -- */
			$unattended_query = "SELECT * FROM `" . $db_tbl['events'] . "` WHERE `contract_status` = 'Unattended'";
			if( !current_user_can( 'administrator' ) )
				$unattended_query .= " AND `event_dj` = '" . get_current_user_id() . "'";
				
			if( count( $wpdb->get_results( $unattended_query ) ) > 0 )	{
				$default_status = 'Unattended';	
			}
			
			/* -- Event status parameter -- */
			$s = !empty( $_GET["status"] ) ? mysql_real_escape_string( $_GET["status"] ) : $default_status;
			
			$event_status = array( 'Unattended' => 'Unattended Enquiries', 'Approved' => 'Approved', 'Pending' => 'Pending', 'Enquiry' => 'Enquiries', 'Completed' => 'Completed', 'Failed Enquiry' => 'Lost Enquiries' );
			?>
			<div class="alignleft actions">
			<?php
            if ( $which == 'top' )	{
				?>
                <ul class='subsubsub'>
                <?php
                $i = 1;
                
                /* -- Loop through the event status' -- */
                foreach( $event_status as $current_status => $display )	{
                    $status_query = "SELECT * FROM `" . $db_tbl['events'] . "` WHERE `contract_status` = '" . $current_status . "'";
                    if( !current_user_can( 'administrator' ) )
                        $status_query .= " AND `event_dj` = '" . get_current_user_id() . "'";
                    ?>
                    <li class="publish"><a href="<?php f_mdjm_admin_page( 'events' ); ?>&status=<?php echo $current_status; ?>"<?php if( $current_status == $s ) { ?> class="current" <?php } ?>><?php echo $display; ?><?php if( $display == 'Unattended' ) echo '</span>'; ?> <span class="count">(<?php echo count( $wpdb->get_results( $status_query ) ); ?>)</span></a> |</li>
                    <?php
                    
                    $i++;	
                }
                
                /* -- All Events -- */
                ?>
                <li class='publish'><a href="<?php f_mdjm_admin_page( 'events' ); ?>&status=All"<?php if ( $s == 'All' ) { ?> class="current" <?php } ?>>All Events <span class="count">(<?php echo count( $wpdb->get_results( $all_events_query ) ); ?>)</span></a></li>
                </ul>
				<form id="event-filter" method="get" action="">
				<input type="hidden" name="page" value="<?php echo $_REQUEST['page']; ?>" />
                <input type="hidden" name="status" value="<?php echo $this->status; ?>" />
                
				<?php
				$this->monthly_filter();
				$this->event_type_filter();
				$this->dj_filter();
				$this->client_filter();
				
				submit_button( __( 'Filter' ), 'button', 'filter_action', false, array( 'id' => 'event-query-submit' ) );
				?>
                <br />
                <?php
			}
			?>
            </form>
			</div>
            <?php
		} // extra_tablenav
		
		/**
		 * get_columns
		 * The columns to display
		 * @since 1.1
		 * 
		 */
		function get_columns() {
			global $mdjm_client_text;
			$columns = array(
				'col_event_date'		  	  => __( 'Date' ),
				'col_event_id'				=> __( 'ID' ),
				'col_user_id'			 	 => __( 'Client' ),
				'col_event_dj'				=> __( 'DJ' ),
				'col_event_type'			  => __( 'Type' ),
				'col_event_start'			 => __( 'Start' ),
				'col_event_finish'			=> __( 'End' ),
				'col_event_description'	   => __( 'Description' ),
				'col_event_guest_call'		=> __( 'Guest Playlist' ),
				'col_booking_date'			=> __( 'Booking Date' ),
				'col_contract_status'		 => __( 'Status' ),
				'col_contract'				=> __( 'Contract' ),
				'col_contract_approved_date'  => __( 'Contract Approved' ),
				'col_contract_approver'	   => __( 'Contract Approver' ),
				'col_cost'					=> __( 'Value' ),
				'col_deposit'				 => __( $mdjm_client_text['deposit_label'] ),
				'col_deposit_status'		  => __( 'Deposit Status' ),
				'col_balance_status'		  => __( $mdjm_client_text['balance_label'] . ' Status' ),
				'col_venue'				   => __( 'Venue' ),
				'col_venue_contact'		   => __( 'Venue Contact' ),
				'col_venue_addr1'			 => __( 'Venue Address 1' ),
				'col_venue_addr2'			 => __( 'Venue Address 2' ),
				'col_venue_city'			  => __( 'Venue Town' ),
				'col_venue_state'			 => __( 'Venue County' ),
				'col_venue_zip'			   => __( 'Venue Postcode' ),
				'col_venue_phone'			 => __( 'Venue Phone' ),
				'col_venue_email'			 => __( 'Venue Email' ),
				'col_added_by'				=> __( 'Added By' ),
				'col_date_added'			  => __( 'Date Added' ),
				'col_referrer'				=> __( 'Referrer' ),
				'col_converted_by'			=> __( 'Converted By' ),
				'col_date_converted'		  => __( 'Date Converted' ),
				'col_last_updated_by'		 => __( 'Last Updated by' ),
				'col_last_updated'			=> __( 'Last Updated' ),
				'col_event_package'		   => __( 'Package' ),
				'col_event_addons'			=> __( 'Add-Ons' ),
				'col_cronned'				 => __( 'Automated Tasks' ),
				'col_dj_setup_time'		   => __( 'DJ Setup Time' ),
				'col_dj_setup_date'		   => __( 'DJ Setup Date' ),
				'col_dj_notes'				=> __( 'DJ Notes' ),
				'col_admin_notes'			 => __( 'Admin Notes' ),
				'col_playlist'				=> __( 'Playlist' ),
				'col_journal'				 => __( 'Journal' ),
			);
			return $columns;
		} // get_columns
		
		/**
		 * get_sortable_columns
		 * The columns we can sort by
		 * @since 1.1
		 * 
		 */
		public function get_sortable_columns() {
			$sortable = array(
				'col_event_id'		=> array( 'event_id', false ),
				'col_event_date'	  => array( 'event_date', true ),
				'col_event_type'  	  => array( 'event_type', false ),
				'col_contract_status' => array( 'contract_status', false ),
				'col_contract'	 	=> array( 'contract', false ),
				'col_cost'			=> array( 'cost', false ),
				'col_deposit_status'  => array( 'deposit_status', false ),
				'col_balance_status'  => array( 'balance_status', false ),
				'col_venue'  		   => array( 'venue', false ),
			);
			return $sortable;
		} // get_sortable_columns
		
		/**
		 * get_search_columns
		 * The columns we can search by
		 * @since 1.1
		 * 
		 */
		public function get_search_columns() {
			$searchable = array(
				'event_id',
				'user_id',
				'event_date',
				'event_dj',
				'event_type',
				'contract_status',
				'contract',
				'cost',
				'deposit_status',
				'balance_status',
				'venue',
				'referrer',
			);
			return $searchable;
		} // get_search_columns
		
		/**
		 * get_current_status
		 * Determine the current status display
		 * @since 1.1
		 * 
		 */
		function get_current_status() {
			global $wpdb;
			
			include( WPMDJM_PLUGIN_DIR . '/includes/config.inc.php' );
						
			/* -- Event status parameter -- */
			$default_status = 'Approved';
			
			/* -- Check for Unattended -- */
			$unattended_query = "SELECT * FROM `" . $db_tbl['events'] . "` WHERE `contract_status` = 'Unattended'";
			if( count( $wpdb->get_results( $unattended_query ) ) > 0 )	{
				$default_status = 'Unattended';	
			}
			
			/* -- Event status parameter -- */
			$s = !empty( $_GET['status'] ) ? mysql_real_escape_string( $_GET['status'] ) : $default_status;
			
			return $s;
		} // get_current_status
		
		/* -- Column row actions  -- */
		/**
		 * event_date_actions
		 * Row actions for the Event Date column
		 * @since 1.1
		 * 
		 */
		function event_date_actions( $item )	{
			$actions = array();
						
			if( $item->contract_status != 'Unattended' )	{
				$actions['edit'] = sprintf( '<a href="?page=%s&action=%s&event_id=%s">Edit</a>', $_REQUEST['page'], 'view_event_form', $item->event_id );
			}
			
			if( $item->contract_status == 'Approved' || $item->contract_status == 'Pending' )	{
				$actions['complete'] = sprintf( '<a href="' . admin_url( 'admin.php?page=%s&status=%s&action=%s&event_id=%s' ) . '">Complete</a>', $_REQUEST['page'], $this->status, 'complete_event', $item->event_id );
				$actions['cancel'] = sprintf( '<span class="trash"><a href="' . admin_url( 'admin.php?page=%s&status=%s&action=%s&event_id=%s' ) . '">Cancel</a></span>', $_REQUEST['page'], $this->status, 'cancel_event', $item->event_id );
			}
			
			if( $item->contract_status == 'Enquiry' )	{
				$actions['convert'] = sprintf( '<a href="' . admin_url( 'admin.php?page=%s&status=%s&action=%s&event_id=%s' ) . '">Convert</a>', $_REQUEST['page'], $this->status, 'convert_event', $item->event_id );
				$actions['failed'] = sprintf( '<span class="trash"><a href="' . admin_url( 'admin.php?page=%s&status=%s&action=%s&event_id=%s' ) . '">Fail</a></span>', $_REQUEST['page'], $this->status, 'fail_enquiry', $item->event_id );
			}
			
			if( $item->contract_status == 'Unattended' )	{
				$actions['quote'] = sprintf( '<a href="' . admin_url( 'admin.php?page=%s&action=%s&event_id=%s' ) . '">Quote</a>', $_REQUEST['page'], 'add_event_form', $item->event_id );
				$actions['availability'] = sprintf( '<a href="' . admin_url( 'admin.php?page=%s&status=%s&availability=%s&e_id=%s' ) . '">Availability</a>', $_REQUEST['page'], $this->status, date( 'Y-m-d', strtotime( $item->event_date ) ), $item->event_id );
				$actions['respond_unavailable'] = sprintf( '<span class="trash"><a href="' . admin_url( 'admin.php?page=%s&template=%s&to_user=%s&event_id=%s&action=%s' ) . '">Unavailable</a></span>', 'mdjm-comms', $this->mdjm_options['unavailable_email_template'], $item->user_id, $item->event_id, 'respond_unavailable' );
			}
			
			if( $item->contract_status == 'Failed Enquiry' )	{
				$actions['recover'] = sprintf( '<a href="' . admin_url( 'admin.php?page=%s&status=%s&action=%s&event_id=%s' ) . '">Recover</a>', $_REQUEST['page'], 'Enquiry', 'recover_event', $item->event_id );
			}
			
			return $this->row_actions( $actions );
		} // event_date_actions
		
		/**
		 * client_actions
		 * Row actions for the Client column
		 * @since 1.1
		 * 
		 */
		function client_actions( $item )	{					
			$actions = array(
					'edit' => sprintf( '<a href="' . admin_url( '%s?user_id=%s' ) . '">Profile</a>', 'user-edit.php', $item->user_id ),
					'email' => sprintf( '<a href="' . admin_url( 'admin.php?page=%s&to_user=%s&event_id=%s' ) . '">Email</a>', 'mdjm-comms', $item->user_id, $item->event_id ),
				);
			
			return $this->row_actions( $actions );
		} // client_actions
		
		/**
		 * dj_actions
		 * Row actions for the DJ column
		 * @since 1.1
		 * 
		 */
		function dj_actions( $item )	{					
			$actions = array(
					'edit' => sprintf( '<a href="'. admin_url( '%s?user_id=%s' ) . '">Profile</a>', 'user-edit.php', $item->event_dj ),
					'email' => sprintf( '<a href="' . admin_url( 'admin.php?page=%s&to_user=%s&event_id=%s' ) . '">Email</a>', 'mdjm-comms', $item->event_dj, $item->event_id ),
				);
			
			return $this->row_actions( $actions );
		} // dj_actions
		
		/**
		 * contract_status_actions
		 * Row actions for the Status column
		 * @since 1.1
		 * 
		 */
		function contract_status_actions( $item )	{
			$actions = array();
			if( $item->contract_status == 'Approved' )	{
				if ( get_option('permalink_structure') )	{
					$sep = '?';
				}
				else	{
					$sep = '&amp;';	
				}				
				$actions['view_contract'] = sprintf( '<a href="'. get_permalink( WPMDJM_CLIENT_CONTRACT_PAGE ) . $sep . '%s=%s">View Contract</a>', 'event_id', $item->event_id );
			}
			
			return $this->row_actions( $actions );
		} // contract_status_actions
		
		/**
		 * playlist_actions
		 * Row actions for the Playlist column
		 * @since 1.1
		 * 
		 */
		function playlist_actions( $item )	{
			global $wpdb;
			
			include( WPMDJM_PLUGIN_DIR . '/includes/config.inc.php' );
			
			$playlist = $wpdb->get_var( "SELECT COUNT(*) FROM " . $db_tbl['playlists'] . " WHERE event_id = " . $item->event_id );
			
			if( $playlist > 0 )	{
				$actions['playlist'] = sprintf( '<a href="?page=%s&action=%s&event_id=%s">View</a>', $_REQUEST['page'], 'render_playlist_table', $item->event_id );
				return sprintf( '%1$s %2$s', _n( $playlist . ' Song', $playlist . ' Songs', $playlist ), $this->row_actions( $actions ) );	
			}
			else	{
				return _n( $playlist . ' Song', $playlist . ' Songs', $playlist );
			}
			
			return $this->row_actions( $actions );
		} // playlist_actions
		
		/**
		 * query_builder
		 * Set the DB queries
		 * @since 1.1.1
		 * 
		 */
		function query_builder()	{
			$where = array();
			
			/* -- Filtering parameters -- */
			/* -- Status -- */
			$s = !empty( $this->status ) ? $this->status : 'Approved';
			if( !empty( $s ) )	{
				if( $s == 'Historic' )	{
					$where[] = " (`contract_status` != 'Enquiry' AND `contract_status` != 'Failed Enquiry' AND `event_date` < DATE(NOW()) OR `contract_status` = 'Cancelled' OR `contract_status` = 'Completed')";	
				}
				elseif( $s != 'All' )	{
					$where[] = " `contract_status` = '" . $s . "'";	
				}				
			}
			
			/* -- Month -- */
			$m = !empty( $_GET['m'] ) ? $_GET['m'] : '0';
			if( !empty( $m ) )
				$where[] = " year(event_date) = '" . substr( $m, 0, 4 ) . "' AND month(event_date) = '" . substr( $m, -2 ) . "'";
			
			/* -- Type -- */
			$t = !empty( $_GET['t'] ) ? $_GET['t'] : '';
			if( !empty( $t ) )
				$where[] = " `event_type` = '" . $t . "'";
			
			/* -- DJ -- */
			$dj = !empty( $_GET['dj'] ) ? $_GET['dj'] : '0';
			if( !current_user_can( 'administrator' ) )
				$dj = get_current_user_id();
			if( !empty( $dj ) )
				$where[] = " `event_dj` = '" . $dj . "'";
			
			if( !empty( $where ) )	{
				$i = 1;
				$q = ' WHERE';
				foreach( $where as $clause )	{
					if( $i > 1 )
						$q .= ' AND';
						
					$q .= ' ' . $clause;
					
					$i++;	
				}
			}
			
			/* -- Client -- */
			$c = !empty( $_GET['c'] ) ? $_GET['c'] : '0';
				
			if( !empty( $c ) )	{
				if( current_user_can( 'administrator' ) || f_mdjm_client_is_mine( $c ) )
					$where[] = " `user_id` = '" . $c . "'";
			}
			
			if( !empty( $where ) )	{
				$i = 1;
				$q = ' WHERE';
				foreach( $where as $clause )	{
					if( $i > 1 )
						$q .= ' AND';
						
					$q .= ' ' . $clause;
					
					$i++;	
				}
			}
			if( !empty( $q ) )
				return $q;
				
			return;
		} // query_builder
		
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
			
			$this->mdjm_options = $mdjm_options;
			
			$this->status = $this->get_current_status();
			
			/* -- Prepare the query -- */
			$query = 'SELECT * FROM `' . $db_tbl['events'] . '`';
			
			$query .= $this->query_builder( $_GET );
						
			/* -- Ordering parameters -- */
			//Parameters that are going to be used to order the result
			$orderby = !empty( $_GET["orderby"] ) ? mysql_real_escape_string( $_GET["orderby"] ) : 'event_date';
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
						'col_event_id',
						'col_event_start',
						'col_event_finish',
						'col_event_description',
						'col_event_guest_call',
						'col_booking_date',
						'col_contract',
						'col_contract_approved_date',
						'col_contract_approver',
						'col_deposit',
						'col_deposit_status',
						'col_balance_status',
						'col_venue',
						'col_venue_contact',
						'col_venue_addr1',
						'col_venue_addr2',
						'col_venue_city',
						'col_venue_state',
						'col_venue_zip',
						'col_venue_phone',
						'col_venue_email',
						'col_added_by',
						'col_date_added',
						'col_referrer',
						'col_converted_by',
						'col_date_converted',
						'col_last_updated_by',
						'col_last_updated',
						'col_event_package',
						'col_event_addons',
						'col_cronned',
						'col_dj_setup_time',
						'col_dj_setup_date',
						'col_dj_notes',
						'col_admin_notes',
						);
			$sortable = $this->get_sortable_columns();
			$this->_column_headers = array( $columns, $hidden, $sortable );
						
			/* -- Fetch the items -- */
			if( $search != NULL )	{ // Search being performed
				$searchable_columns = $this->get_search_columns();
				$search_query = "SELECT * FROM `" . $db_tbl['events'] . "` WHERE";
				$i = 1;
				/* -- Loop through searchable columns to build query -- */
				foreach( $searchable_columns as $column_to_search )	{
					if( $i != 1 && $i != count( $searchable_columns ) )	{
						$search_query .= " OR";	
					}
					$search_query .= " `event_id` LIKE '%%%s%%'";
					
					$i++;
				}
				
				$search_str = '';
				foreach( $searchable_columns as $search_str )	{
					$search_str .= ", " . $search;
				}
								
				$this->items = $wpdb->get_results( $wpdb->prepare( $search_query, $search_str ) );
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
			global $mdjm_options, $mdjm_currency, $wpdb;
			
			if( !isset( $db_tbl ) )	{
				include( WPMDJM_PLUGIN_DIR . '/includes/config.inc.php' );
			}
			
		   //Get the records registered in the prepare_items method
			$events = $this->items;
		
		   //Get the columns registered in the get_columns and get_sortable_columns methods
			list( $columns, $hidden ) = $this->get_column_info();
			
			$i = 0;
			
		   //Loop for each record
			if( !empty( $events ) )	{
				foreach( $events as $event )	{
					$eventinfo = f_mdjm_get_eventinfo_by_id( $event->event_id );
					$clientinfo = get_userdata( $eventinfo->user_id );
					$djinfo = get_userdata( $eventinfo->event_dj );
							
			  //Open the line
				echo '<tr id="record_' . $event->event_id . '"';
				if( $event->contract_status == 'Unattended' )	{
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
						
						//Display the cell
						switch ( $column_name ) {
							case "col_event_id":  
								echo '<td ' . $attributes . '>' . stripslashes( $event->event_id ) . '</td>' . "\n";
								break;
							case "col_user_id":
								if( !$clientinfo )	{
									$client_name = 'N/A';
								}
								else	{
									$client_name = $clientinfo->display_name;	
								}
								echo '<td ' . $attributes . '>' . $client_name;
								echo $this->client_actions( $event );
								echo '</td>' . "\n";
								break;
							case "col_event_date":
								echo '<td ' . $attributes . '>' . date( 'd M Y', strtotime( $event->event_date ) );
								echo $this->event_date_actions( $event );
								echo '</td>' . "\n";
								break;
							case "col_event_dj":
								if( !$djinfo )	{
									$dj_name = 'No DJ Assigned';
								}
								else	{
									$dj_name = $djinfo->display_name;	
								}
								echo '<td ' . $attributes . '>' . $dj_name;
								echo $this->dj_actions( $event );
								echo '</td>' . "\n";
								break;
							case "col_event_type":
								echo '<td ' . $attributes . '>' . stripslashes( $event->event_type ) . '</td>' . "\n";
								break;
							case "col_event_start":
								echo '<td ' . $attributes . '>' . date( $mdjm_options['time_format'], strtotime( $event->event_start ) ) . '</td>' . "\n";
								break;
							case "col_event_finish":
								echo '<td ' . $attributes . '>' . date( $mdjm_options['time_format'], strtotime( $event->event_finish ) ) . '</td>' . "\n";
								break;
							case "col_event_description":
								echo '<td ' . $attributes . '>' . stripslashes( $event->event_description ) . '</td>' . "\n";
								break;
							case "col_event_guest_call":
								echo '<td ' . $attributes . '>' . stripslashes( $event->event_guest_call ) . '</td>' . "\n";
								break;
							case "col_booking_date":
								echo '<td ' . $attributes . '>' . date( $mdjm_options['short_date_format'], strtotime( $event->booking_date ) ) . '</td>' . "\n";
								break;
							case "col_contract_status":
								echo '<td ' . $attributes . '>' . stripslashes( $event->contract_status );
								echo $this->contract_status_actions( $event );
								echo '</td>' . "\n";
								break;
							case "col_contract":
								echo '<td ' . $attributes . '>' . stripslashes( $event->contract ) . '</td>' . "\n";
								break;
							case "col_contract_approved_date":
								echo '<td ' . $attributes . '>' . date( $mdjm_options['short_date_format'], strtotime( $event->contract_approved_date ) ) . '</td>' . "\n";
								break;
							case "col_contract_approver":
								echo '<td ' . $attributes . '>' . stripslashes( $event->contract_approver ) . '</td>' . "\n";
								break;
							case "col_cost":
								echo '<td ' . $attributes . '>' . $mdjm_currency[$mdjm_options['currency']] . $event->cost . '</td>' . "\n";
								break;
							case "col_deposit":
								echo '<td ' . $attributes . '>' . $mdjm_currency[$mdjm_options['currency']] . $event->deposit . '</td>' . "\n";
								break;
							case "col_deposit_status":
								echo '<td ' . $attributes . '>' . stripslashes( $event->deposit_status ) . '</td>' . "\n";
								break;
							case "col_balance_status":
								echo '<td ' . $attributes . '>' . stripslashes( $event->balance_status ) . '</td>' . "\n";
								break;
							case "col_venue":
								echo '<td ' . $attributes . '>' . stripslashes( $event->venue ) . '</td>' . "\n";
								break;
							case "col_venue_contact":
								echo '<td ' . $attributes . '>' . stripslashes( $event->venue_contact ) . '</td>' . "\n";
								break;
							case "col_venue_addr1":
								echo '<td ' . $attributes . '>' . stripslashes( $event->venue_addr1 ) . '</td>' . "\n";
								break;
							case "col_venue_addr2":
								echo '<td ' . $attributes . '>' . stripslashes( $event->venue_addr2 ) . '</td>' . "\n";
								break;
							case "col_venue_city":
								echo '<td ' . $attributes . '>' . stripslashes( $event->venue_city ) . '</td>' . "\n";
								break;
							case "col_venue_state":
								echo '<td ' . $attributes . '>' . stripslashes( $event->venue_state ) . '</td>' . "\n";
								break;
							case "col_venue_zip":
								echo '<td ' . $attributes . '>' . stripslashes( $event->venue_zip ) . '</td>' . "\n";
								break;
							case "col_venue_phone":
								echo '<td ' . $attributes . '>' . $event->venue_phone . '</td>' . "\n";
								break;
							case "col_venue_email":
								echo '<td ' . $attributes . '>' . stripslashes( $event->venue_email ) . '</td>' . "\n";
								break;
							case "col_added_by":
								$user_added_by = get_userdata( $event->added_by );
								if( !$user_added_by )	{
									$added_by = 'N/A';
								}
								else	{
									$user_added_by = $user_added_by->display_name;	
								}
								echo '<td ' . $attributes . '>' . stripslashes( $added_by ) . '</td>' . "\n";
								break;
							case "col_date_added":
								echo '<td ' . $attributes . '>' . date( $mdjm_options['short_date_format'], strtotime( $event->date_added ) ) . '</td>' . "\n";
								break;
							case "col_referrer":
								echo '<td ' . $attributes . '>' . stripslashes( $event->referrer ) . '</td>' . "\n";
								break;
							case "col_converted_by":
								$user_converted_by = get_userdata( $event->converted_by );
								if( !$user_converted_by )	{
									$converted_by = 'N/A';
								}
								else	{
									$converted_by = $user_converted_by->display_name;	
								}
								echo '<td ' . $attributes . '>' . stripslashes( $converted_by ) . '</td>' . "\n";
								break;
							case "col_date_converted":
								echo '<td ' . $attributes . '>' . date( $mdjm_options['short_date_format'], strtotime( $event->date_converted ) ) . '</td>' . "\n";
								break;
							case "col_last_updated_by":
								$user_last_updated_by = get_userdata( $event->last_updated_by );
								if( !$user_last_updated_by )	{
									$last_updated_by = 'N/A';
								}
								else	{
									$last_updated_by = $user_last_updated_by->display_name;	
								}
								echo '<td ' . $attributes . '>' . stripslashes( $last_updated_by ) . '</td>' . "\n";
								break;
							case "col_last_updated":
								echo '<td ' . $attributes . '>' . date( $mdjm_options['short_date_format'], strtotime( $event->last_updated ) ) . '</td>' . "\n";
								break;
							case "col_event_package":
								echo '<td ' . $attributes . '>' . stripslashes( $event->event_package ) . '</td>' . "\n";
								break;
							case "col_event_addons":
								echo '<td ' . $attributes . '>' . stripslashes( $event->event_addons ) . '</td>' . "\n";
								break;
							case "col_cronned":
								echo '<td ' . $attributes . '>' . stripslashes( $event->cronned ) . '</td>' . "\n";
								break;
							case "col_dj_setup_time":
								echo '<td ' . $attributes . '>' . date( $mdjm_options['time_format'], strtotime( $event->dj_setup_time ) ) . '</td>' . "\n";
								break;
							case "col_dj_setup_date":
								echo '<td ' . $attributes . '>' . date( $mdjm_options['short_date_format'], strtotime( $event->dj_setup_date ) ) . '</td>' . "\n";
								break;
							case "col_dj_notes":
								echo '<td ' . $attributes . '>' . stripslashes( $event->dj_notes ) . '</td>' . "\n";
								break;
							case "col_admin_notes":
								echo '<td ' . $attributes . '>' . stripslashes( $event->admin_notes ) . '</td>' . "\n";
								break;
							case "col_playlist":
								echo '<td ' . $attributes . '>';
								echo $this->playlist_actions( $event );
								echo '</td>' . "\n";
								break;
							case "col_journal":
								echo '<td ' . $attributes . '><a href="' . admin_url( 'admin.php?page=mdjm-events&action=show_journal&event_id=' . $event->event_id ) . '">View</a></td>' . "\n";
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
		
	}