<?php
	defined( 'ABSPATH' ) or die( "Direct access to this page is disabled!!!" );
	
	if( !mdjm_employee_can( 'view_clients_list' ) )	{
		wp_die(
			'<h1>' . __( 'Cheatin&#8217; uh?', 'mobile-dj-manager' ) . '</h1>' .
			'<p>' . __( 'You do not have permission to manage clients.', 'mobile-dj-manager' ) . '</p>',
			403
		);
	}
	
// This class extends WP_List_Table
if( !class_exists( 'WP_List_Table' ) )
	require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
	
/**
 * Class Name: MDJM_Client_Manager
 * User management interface for employees
 *
 *
 *
 */
if( !class_exists( 'MDJM_Client_Manager' ) ) : 
	class MDJM_Client_Manager extends WP_List_Table	{
		private static $orderby;
		private static $order;
		public static $clients;
		public static $total_clients;
		private static $display_role;
		
		/**
		 * Class constructor
		 *
		 *
		 *
		 */
		public function __construct()	{
			parent::__construct( array(
				'singular'=> 'mdjm_list_client', //Singular label
				'plural' => 'mdjm_list_clients', //plural label, also this will be one of the table css class
				'ajax'   => false //We won't support Ajax for this table
			) );
			$this->process_bulk_actions();
			$this->get_clients();
			
			$this->prepare_items();
				
			// Display the page
			$this->client_page();	
		} // __construct
		
		public function get_clients()	{
			// Filter our search by role if we need to
			self::$display_role = ! empty( $_GET['display_role'] ) ? $_GET['display_role'] : array( 'client', 'inactive_client' );
			self::$orderby      = ! empty( $_GET['orderby'] )      ? $_GET['orderby']      : 'display_name';
			self::$order        = ! empty( $_GET['order'] )        ? $_GET['order']        : 'ASC';
			
			// Searching
			if( ! empty( $_POST['s'] ) )	{

				// Build out the query args for the WP_User_Query
				self::$clients = get_users(
					array(
						'search'  => $_POST['s'],
						'role__in'=> array( 'client', 'inactive_client' ),
						'orderby' => self::$orderby,
						'order'   => self::$order
					)
				);

			} elseif( ! empty( $_POST['filter_client'] ) )	{
	
				self::$clients = mdjm_get_clients(
					self::$display_role,
					$_POST['filter_client'],
					self::$orderby,
					self::$order
				);

			} else	{

				self::$clients = mdjm_get_clients(
					self::$display_role,
					! mdjm_employee_can( 'list_all_clients' ) ? get_current_user_id() : '',
					self::$orderby,
					self::$order
				);

			}
			
			self::$total_clients = count( mdjm_get_clients() );

		} // get_clients
					
		/**
		 * Display the page header for the client management interface
		 *
		 *
		 *
		 */
		public function client_page()	{
			?>
			<div class="wrap">
			<div id="icon-themes" class="icon32"></div>
            <h1><?php printf( __( '%s Clients', 'mobile-dj-manager' ), MDJM_COMPANY ); ?></h1>
            <form name="mdjm-client-list" id="mdjm-client-list" method="post">
            <?php
			$this->views();
			$this->display();
			?>
            </form>
            <?php
		} // page_header
		
		/**
		 * Define items/data to be displayed before and after the list table
		 *
		 * @param	str		$action		Required: top for top of the table or bottom
		 *
		 * @return	str					The HTML to be output
		 */
		public function extra_tablenav( $which ) {
			if ( $which == "top" )	{
			?>
			<div class="alignleft actions">
                <label class="screen-reader-text" for="filter_client"><?php _e( 'Only show', 'mobile-dj-manager' ); ?>&hellip;</label>
                <?php
               mdjm_employee_dropdown( 
                	array(
						'name'			=> 'filter_client',
						'first_entry' 	 => __( 'Show clients of', 'mobile-dj-manager' ) . '...',
						'first_entry_val' => '',
						'structure'	   => true,
						'echo'			=> true
                    )
                );
				?>
                <input type="submit" name="show_only" id="show_only" class="button" value="<?php _e( 'Go!', 'mobile-dj-manager' ); ?>" />
            </div>
              <?php
			  $this->search_box( __( 'Search', 'mobile-dj-manager' ), 'search_id' );
		   }	   
		} // extra_tablenav
		
		/**
		 * Define the table column ID's and names
		 *
		 * @param
		 *
		 * @return	$arr	$columns	The table column IDs and names
		 */
		public function get_columns(){
			$columns = array(
				'cb'     => '<input type="checkbox" />',
				'name'   => __( 'Name', 'mobile-dj-manager' ),
				'events' => __( 'Total Events', 'mobile-dj-manager' ),
				'next'   => __( 'Next Event', 'mobile-dj-manager' ),
				'login'  => __( 'Last Login', 'mobile-dj-manager' ) );
			
			return $columns;
		} // get_columns
		
		function get_sortable_columns() {
			$sortable_columns = array(
				'name'  => array( 'display_name', true )
			);
			return $sortable_columns;
		}
		
		/**
		 * This is where we define the layout of the list table and the data to be used
		 *
		 * @param
		 *
		 * @return
		 */
		public function prepare_items() {
			// Prepare columns
			$columns = $this->get_columns();
			$hidden = array();
			$sortable = $this->get_sortable_columns();
			$this->_column_headers = array( $columns, $hidden, $sortable );
			
			// Pagination. TODO
			$per_page = 5;
			$current_page = $this->get_pagenum();
									
			$total_items = count( self::$total_clients );
									
			$this->items = self::$clients;
		} // prepare_items
		
		/**
		 * Specifies the default text to be displayed when there is no data
		 *
		 * @param
		 *
		 * @return	str		The text to be displayed when there are no results to display
		 */
		public function no_items() {
			_e( "No Client's found.", 'mobile-dj-manager' );
		} // no_items
		
		/**
		 * Specifies the default data to be displayed within columns that do not have a
		 * defined method within this class
		 *
		 * @param	obj		$item			The object array for the current data object
		 *			str		$column_name	The name of the current column
		 *
		 * @return	str		The data to be output into the column
		 */
		public function column_default( $item, $column_name ) {
			global $wp_roles;
			
			switch( $column_name ) { 				
				default:
					return;
			  }
		} // column_default
		
		/**
		 * Create the HTML output for the checkboxes column
		 *
		 * @param	obj		$item	The object array for the current item
		 *
		 * @return	str		The HTML output for the checkbox column
		 */
		public function column_cb( $item ) {
			echo '<input type="checkbox" name="client[]" id="clients-' . $item->ID . '" value="' . $item->ID . '" />';    
		} // column_cb
		
		/**
		 * Create the HTML output for the name column
		 *
		 * @param	obj		$item	The object array for the current item
		 *
		 * @return	str		The HTML output for the name column
		 */
		public function column_name( $item ) {
			if ( current_user_can( 'edit_users' ) || $item->ID == get_current_user_id() )	{
				$edit_users = true;
			}
		
			if( ! empty( $edit_users ) )	{
				echo '<a href="' . get_edit_user_link( $item->ID ) . '">';
			}
			
			echo $item->display_name;
			
			if( !empty( $edit_users ) )	{
				echo '</a>';
			}
		} // column_name
				
		/**
		 * Create the HTML output for the events column
		 *
		 * @param	obj		$item	The object array for the current item
		 *
		 * @return	str		The HTML output for the events column
		 */
		public function column_events( $item ) {
			$total = MDJM()->events->client_events( $item->ID );
			
			echo ( !empty( $total ) ?
				'<a href="' . admin_url( 'edit.php?s&post_type=mdjm-event?s&post_status=all' .
				'&post_type=mdjm-event&action=-1&mdjm_filter_date=0&mdjm_filter_type&mdjm_filter_employee=0' . 
				'&mdjm_filter_client=' . $item->ID . '&filter_action=Filter&paged=1&action2=-1' ) . '">' . count( $total ) . '</a>' : '0'
			);    
		} // column_events
		
		/**
		 * Create the HTML output for the next event column
		 *
		 * @param	obj		$item	The object array for the current item
		 *
		 * @return	str		The HTML output for the next event column
		 */
		public function column_next( $item ) {
			$next = mdjm_get_clients_next_event( $item->ID );
			
			if ( ! empty( $next ) )	{
				echo '<a href="' . get_edit_post_link( $next[0]->ID ) . '">' . mdjm_get_event_date( $next[0]->ID ) . '</a>';
			} else	{
				echo __( 'N/A', 'mobile-dj-manager' );
			}
						
		} // column_next
		
		/**
		 * Create the HTML output for the login column
		 *
		 * @param	obj		$item	The object array for the current item
		 *
		 * @return	str		The HTML output for the login column
		 */
		public function column_login( $item ) {
			if( '' != get_user_meta( $item->ID, 'last_login', true ) )
				echo date( 'H:i d M Y', strtotime( get_user_meta( $item->ID, 'last_login', true ) ) );
				
			else
				echo __( 'Never', 'mobile-dj-manager' );
		} // column_login
		
		/**
		 * Generate the role view filters
		 *
		 * @param
		 *
		 * @return	$views		Array of $view => $link
		 */
		public function get_views()	{
			global $wp_roles;
			
			$views = array();
			$current = self::$display_role;
		
			// All roles link
			$class = ( empty( $current ) || $current == 'all' ? ' class="current"' : '' );
			$all_url = remove_query_arg( 'display_role' );
			$views['all'] = '<a href="' . $all_url . '" ' . $class . '>' . __( 'All', 'mobile-dj-manager' ) . 
				' <span class="count">(' . self::$total_clients . ')</span></a>';
			
			// Loop through all roles and generate the required views for each
			$roles = array( 'client', 'inactive_client' );
			foreach( $roles as $key => $role )	{
				$count = count( mdjm_get_clients( $role ) );
				
				if( empty( $count ) )
					continue;
				
				$class = ( $current == $role ? ' class="current"' : '' );
				$role_url = add_query_arg( 'display_role', $role );
				$views[$role] = '<a href="' . $role_url . '" ' . $class . '>' . translate_user_role( $wp_roles->roles[$role]['name'] ) . 
					 ' <span class="count">(' . $count . ')</span></a>';
			}
			
			if( !empty( $_POST['s'] ) )	{
				$class = ( $current == $role ? ' class="current"' : '' );
				$views['search'] ='<a href="#" ' . $class . '>' . __( 'Search Results', 'mobile-dj-manager' ) . 
					 ' <span class="count">(' . count( self::$clients ) . ')</span></a>';
			}
		   
		   return $views;
		} // get_views
				
		/**
		 * Add the bulk actions to the table header and footer and define the options
		 *
		 * @params
		 *
		 * @return	arr		$actions		The options for the bulk action dropdown
		 */
		public function get_bulk_actions() {
			$actions = array(
				'delete'    => __( 'Delete Client', 'mobile-dj-manager' ) );
			
			if( !isset( $_GET['display_role'] ) || $_GET['display_role'] == 'inactive_client' )
				$actions['active'] = 'Mark Active';
			
			if( !isset( $_GET['display_role'] ) || $_GET['display_role'] == 'client' )
				$actions['inactive'] = 'Mark Inactive';
				
			return $actions;
		} // get_bulk_actions
		
		/**
		 * Process bulk actions if requested
		 *
		 * @param
		 *
		 * @return
		 */
		public function process_bulk_actions()	{
			$action = $this->current_action();
			
			if( empty( $action ) )
				return;
			
			$i = 0;
			
			if( 'delete' === $action && !empty( $_POST['client'] ) )	{
				foreach( $_POST['client'] as $user_id )	{
					if( MDJM_DEBUG == true )
						MDJM()->debug->log_it( 'Deleting client with ID ' . $user_id, true );
						
					wp_delete_user( $user_id );
					$i++;
				}
				
				mdjm_update_notice( 'updated', __( $i . ' Client(s) deleted.', 'mobile-dj-manager' ), true );
			}
			if( 'inactive' === $action || 'active' === $action )	{
				if( empty( $_POST['client'] ) )
					return;
				
				foreach( $_POST['client'] as $user_id )	{
					if( MDJM_DEBUG == true )
						MDJM()->debug->log_it( 'Setting client with ID ' . $user_id . ' to ' . ucfirst( $this->current_action() ), true );
						
					$user = new WP_User( $user_id );
					
					$user->set_role( $action == 'inactive' ? 'inactive_client' : 'client' );
					$i++;
				}
				
				mdjm_update_notice( 'updated', __( $i . ' Client(s) updated.', 'mobile-dj-manager' ), true );
			}		
		} // process_bulk_actions
	} // class MDJM_Client_Manager
endif;
	new MDJM_Client_Manager();
