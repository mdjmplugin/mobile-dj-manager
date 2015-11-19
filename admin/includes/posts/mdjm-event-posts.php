<?php
	defined( 'ABSPATH' ) or die( "Direct access to this page is disabled!!!" );
	
/**
 * Class Name: MDJM_Event_Posts
 * Manage the Event posts
 *
 *
 *
 */
if( !class_exists( 'MDJM_Event_Posts' ) ) :
	class MDJM_Event_Posts	{
		/**
		 * Initialise
		 */
		public static function init()	{
			add_action( 'manage_mdjm-event_posts_custom_column' , array( __CLASS__, 'event_posts_custom_column' ), 10, 2 );
			
			add_action( 'admin_footer', array( __CLASS__, 'highlight_unattended_event_rows' ) ); // Unattended event row colour
			
			add_action( 'restrict_manage_posts', array( __CLASS__, 'event_post_filter_list' ) ); // Filter dropdown boxes
			
			add_filter( 'manage_mdjm-event_posts_columns' , array( __CLASS__, 'event_post_columns' ) );
			
			add_filter( 'manage_edit-mdjm-event_sortable_columns', array( __CLASS__, 'event_post_sortable_columns' ) );
			
			add_filter( 'bulk_actions-edit-mdjm-event', array( __CLASS__, 'event_bulk_action_list' ) );
		} // init
		
		/**
		 * Define the columns to be displayed for event posts
		 *
		 * @params	arr		$columns	Array of column names
		 *
		 * @return	arr		$columns	Filtered array of column names
		 */
		function event_post_columns( $columns ) {
			$columns = array(
					'cb'			=> '<input type="checkbox" />',
					'title'			=> __( 'Event ID', 'mobile-dj-manager' ),
					'event_date'	=> __( 'Date', 'mobile-dj-manager' ),
					'client'		=> __( 'Client', 'mobile-dj-manager' ),
					'dj'			=> MDJM_DJ,
					'event_status'	=> __( 'Status', 'mobile-dj-manager' ),
					'event_type'	=> __( 'Event Type', 'mobile-dj-manager' ),
					'value'			=> __( 'Value', 'mobile-dj-manager' ),
					'balance'		=> __( 'Due', 'mobile-dj-manager' ),
					'playlist'		=> __( 'Playlist', 'mobile-dj-manager' ),
					'journal'		=> __( 'Journal', 'mobile-dj-manager' ),
				);
			
			return $columns;
		} // event_post_columns
		
		/**
		 * Define which columns are sortable for event posts
		 *
		 * @params	arr		$sortable_columns	Array of event post sortable columns
		 *
		 * @return	arr		$sortable_columns	Filtered Array of event post sortable columns
		 */
		function event_post_sortable_columns( $sortable_columns )	{
			$sortable_columns['event_date'] = 'event_date';
			$sortable_columns['value'] = 'value';
			
			return $sortable_columns;
		} // event_post_sortable_columns
		
		/**
		 * Define the data to be displayed in each of the custom columns for the Communications post types
		 *
		 * @param	str		$column_name	The name of the column to display
		 *			int		$post_id		The current post ID
		 * 
		 *
		 */
		function event_posts_custom_column( $column_name, $post_id )	{
			global $post;
			
			if( $column_name == 'value' || $column_name == 'balance' )
				$value = get_post_meta( $post->ID, '_mdjm_event_cost', true );
				
			switch ( $column_name ) {
				// Event Date
				case 'event_date':
					echo sprintf( '<a href="' . admin_url( 'post.php?post=%s&action=edit' ) . '">%s</a>', 
						$post_id, date( 'd M Y', strtotime( get_post_meta( $post_id, '_mdjm_event_date', true ) ) ) );
					break;
					
				// Client
				case 'client':
					$client = get_userdata( get_post_meta( $post->ID, '_mdjm_event_client', true ) );
					
					if( !empty( $client ) )
						echo '<a href="' . mdjm_get_admin_page( 'comms') . '&to_user=' . 
							$client->ID . '&event_id=' . $post_id . '">' . 
							$client->display_name . '</a>';
					else
						printf( __( '%sNot Assigned%s', 'mobile-dj-manager' ),
							'<span class="mdjm-form-error">',
							'</span>' );
					break;
					
				// DJ
				case 'dj':
					$dj = get_userdata( get_post_meta( $post_id, '_mdjm_event_dj', true ) );
					if( !empty( $dj ) )
						echo '<a href="' . mdjm_get_admin_page( 'comms') . '&to_user=' . 
							$dj->ID . '&event_id=' . $post->ID . '">' . 
							$dj->display_name . '</a>';
							
					else
						printf( __( '%sNot Assigned%s', 'mobile-dj-manager' ),
							'<span class="mdjm-form-error">',
							'</span>' );
					break;
					
				// Status
				case 'event_status':
					echo get_post_status_object( $post->post_status )->label;
					
					if( isset( $_GET['availability'] ) && $post_id == $_GET['e_id'] )	{
						if( is_dj() )
							$dj_avail = $this->availability_check( $_GET['availability'], $current_user->ID );
						else
							$dj_avail = $this->availability_check( $_GET['availability'] );
					}
					break;
					
				// Event Type
				case 'event_type':
					$event_types = get_the_terms( $post_id, 'event-types' );
					if( is_array( $event_types ) )	{
						foreach( $event_types as $key => $event_type ) {
							$event_types[$key] = $event_type->name;
						}
						echo implode( "<br/>", $event_types );
					}
					break;
					
				// Value
				case 'value':
					echo ( !empty( $value ) ? display_price( $value ) : '<span class="mdjm-form-error">' . display_price( '0.00' ) . '</span>' );
					break;
					
				// Balance
				case 'balance':
					if( !class_exists( 'MDJM_Transactions' ) )
						require_once( MDJM_PLUGIN_DIR . '/admin/includes/transactions/mdjm-transactions.php' );
					
					$mdjm_transactions = new MDJM_Transactions();
					
					$rcvd = $mdjm_transactions->get_transactions( $post->ID, 'mdjm-income' );
					echo ( !empty( $rcvd ) && $rcvd != '0.00' ? display_price( ( $value - $rcvd ) ) : display_price( $value ) );
					break;
					
				/* -- Playlist -- */
				case 'playlist':
					$total = $GLOBALS['mdjm']->mdjm_events->count_playlist_entries( $post_id );
					echo '<a href="' . mdjm_get_admin_page( 'playlists' ) . $post_id . '">' . $total . ' ' . 
						_n( 'Song', 'Songs', $total, 'mobile-dj-manager' ) . '</a>' . "\r\n";
					break;
				
				// Journal
				case 'journal':
					$total = wp_count_comments( $post_id )->approved;
					echo '<a href="' . admin_url( '/edit-comments.php?p=' . $post_id ) . '">' . 
						$total . ' ' . 
						_n( 'Entry', 'Entries', $total, 'mobile-dj-manager' ) . 
						'</a>' . "\r\n";
					break;
			} // switch
			
		} // event_posts_custom_column
		
		/**
		 * Remove the edit bulk action from the event posts list
		 *
		 * @params	arr		$actions	Array of actions
		 *
		 * @return	arr		$actions	Filtered Array of actions
		 */
		function event_bulk_action_list( $actions )	{
			unset( $actions['edit'] );
			
			return $actions;
		} // event_bulk_action_list
		
		/**
		 * Highlight unattended events within post listings
		 *
		 *
		 *
		 *
		 */
		function highlight_unattended_event_rows()	{
			global $post;
					
			if( !isset( $post ) || $post->post_type != MDJM_EVENT_POSTS )
				return;
			
			?>
			<style>
			/* Color by post Status */
			.status-mdjm-unattended	{
				background: #FFEBE8 !important;
			}
			</style>
			<?php
		} // highlight_unattended_event_rows
		
		/**
		 * Add the filter dropdowns to the event post list
		 *
		 * @params
		 *
		 * @return
		 */
		function event_post_filter_list()	{
			if( !isset( $_GET['post_type'] ) || $_GET['post_type'] != MDJM_EVENT_POSTS )
				return;
			
			self::event_date_filter_dropdown();
			self::event_type_filter_dropdown();
			if( MDJM_MULTI == true && current_user_can( 'administrator' ) )
				self::event_dj_filter_dropdown();
				
			if( current_user_can( 'administrator' ) )
				self::event_client_filter_dropdown();	
		} // event_post_filter_list
		
		/**
		 * Display the filter drop down list to enable user to select and filter event by month/year
		 * 
		 * @params
		 *
		 * @return
		 */
		function event_date_filter_dropdown()	{
			global $wpdb, $wp_locale;
			
			$month_query = "SELECT DISTINCT YEAR( meta_value ) as year, MONTH( meta_value ) as month 
				FROM `" . $wpdb->postmeta . "` WHERE `meta_key` = '_mdjm_event_date'";
															
			$months = $wpdb->get_results( $month_query );
				
			$month_count = count( $months );
			
			if ( !$month_count || ( 1 == $month_count && 0 == $months[0]->month ) )
				return;

			$m = isset( $_GET['mdjm_filter_date'] ) ? (int) $_GET['mdjm_filter_date'] : 0;
			
			?>
			<label for="filter-by-date" class="screen-reader-text">Filter by Date</label>
			<select name="mdjm_filter_date" id="filter-by-date">
				<option value="0"><?php _e( 'All Dates', 'mobile-dj-manager' ); ?></option>
			<?php
			foreach ( $months as $arc_row ) {
				if ( 0 == $arc_row->year )
					continue;
	
				$month = zeroise( $arc_row->month, 2 );
				$year = $arc_row->year;
	
				printf( 
					"<option %s value='%s'>%s</option>\r\n",
					selected( $m, $year . $month, false ),
					esc_attr( $arc_row->year . $month ),
					/* translators: 1: month name, 2: 4-digit year */
					sprintf( 
						__( '%1$s %2$d', 'mobile-dj-manager' ),
						$wp_locale->get_month( $month ),
						$year )
				);
			}
			?>
			</select>
			<?php
		} // event_date_filter_dropdown
		
		/**
		 * Display the filter drop down list to enable user to select and filter event by type
		 * 
		 * @params
		 *
		 * @return
		 */
		public function event_type_filter_dropdown()	{			
			$event_types = get_categories(
								array(
									'type'			  => MDJM_EVENT_POSTS,
									'taxonomy'		  => 'event-types',
									'pad_counts'		=> false,
									'hide_empty'		=> true,
									'orderby'		  => 'name' ) );
			
			foreach( $event_types as $event_type )	{
				$values[$event_type->term_id] = $event_type->name;
			}
			?>
			<select name="mdjm_filter_type">
                <option value=""><?php echo __( 'All Event Types', 'mobile-dj-manager' ); ?></option>
                <?php
                $current_v = isset( $_GET['mdjm_filter_type'] ) ? $_GET['mdjm_filter_type'] : '';
                if( !empty( $values ) )	{
                    foreach( $values as $value => $label ) {
                        printf(
                            '<option value="%s"%s>%s (%s)</option>',
                            $value,
                            $value == $current_v ? ' selected="selected"' : '',
                            $label,
                            $label );
                    }
                }
                ?>
			</select>
			<?php
		} // event_type_filter_dropdown
		
		/**
		 * Display the filter drop down list to enable user to select and filter event by DJ
		 * 
		 * @params
		 *
		 * @return
		 */
		public function event_dj_filter_dropdown()	{
			global $wpdb;
			
			$dj_query = "SELECT DISTINCT meta_value FROM `" . $wpdb->postmeta . 
				"` WHERE `meta_key` = '_mdjm_event_dj'";
									
			$djs = $wpdb->get_results( $dj_query );
			$dj_count = count( $djs );
			
			if ( !$dj_count || 1 == $dj_count )
				return;

			$artist = isset( $_GET['mdjm_filter_dj'] ) ? (int) $_GET['mdjm_filter_dj'] : 0;
			
			?>
			<label for="filter-by-dj" class="screen-reader-text">Filter by <?php echo MDJM_DJ; ?></label>
			<select name="mdjm_filter_dj" id="filter-by-dj">
				<option value="0"<?php selected( $artist, 0, false ); ?>><?php printf( __( 'All %s', 'mobile-dj-manager' ), MDJM_DJ . '\'s' ); ?></option>
			<?php
			foreach( $djs as $dj ) {
				$djinfo = get_userdata( $dj->meta_value );
				if( empty( $djinfo->display_name ) )
					continue;
					
				printf( "<option %s value='%s'>%s</option>\n",
					selected( $artist, $dj->meta_value, false ),
					$dj->meta_value,
					$djinfo->display_name
				);
			}
			?>
			</select>
			<?php			
		} // event_dj_filter_dropdown
		
		/**
		 * Display the filter drop down list to enable user to select and filter event by Client
		 * 
		 * @params
		 *
		 * @return
		 */
		function event_client_filter_dropdown()	{
			global $wpdb;
							
			$client_query = "SELECT DISTINCT meta_value FROM `" . $wpdb->postmeta . 
				"` WHERE `meta_key` = '_mdjm_event_client'";
									
			$clients = $wpdb->get_results( $client_query );
			$client_count = count( $clients );
			
			if ( !$client_count || 1 == $client_count )
				return;

			$c = isset( $_GET['mdjm_filter_client'] ) ? (int) $_GET['mdjm_filter_client'] : 0;
			
			?>
			<label for="filter-by-client" class="screen-reader-text">Filter by <?php _e( 'Client', 'mobile-dj-manager' ); ?></label>
			<select name="mdjm_filter_client" id="mdjm_filter_client-by-dj">
				<option value="0"<?php selected( $c, 0, false ); ?>><?php _e( "All Client's", 'mobile-dj-manager' ); ?></option>
			<?php
			foreach( $clients as $client ) {
				$clientinfo = get_userdata( $client->meta_value );
				if( empty( $clientinfo->display_name ) )
					continue;
				
				printf( "<option %s value='%s'>%s</option>\n",
					selected( $c, $client->meta_value, false ),
					$client->meta_value,
					$clientinfo->display_name
				);
			}
			?>
			</select>
			<?php
		} // event_client_filter_dropdown
	} // MDJM_Event_Posts
endif;
	MDJM_Event_Posts::init();