<?php
/**
 * MDJM Playlist Table
 *
 * Displays an events playlist entries within the Admin UI
 *
 * @package     MDJM
 * @subpackage  Admin/Events/Playlist
 * @copyright   Copyright (c) 2016, Mike Howard
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.3
*/

if( ! defined( 'ABSPATH' ) )
	exit;
	
if( !class_exists( 'WP_List_Table' ) )	{
	require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}
	
/**
 * MDJM_Playlist_Table Class
 *
 * @since	1.3
 */
class MDJM_PlayList_Table extends WP_List_Table	{
	
	public function __construct() {
		
		parent::__construct( array(
			'singular'=> 'mdjm_playlist_entry', // Singular label
			'plural' => 'mdjm_playlist_entries', // Plural label, also this well be one of the table css class
			'ajax'   => false // We won't support Ajax for this table
		) );
					
	}
	
	/**
	 * Retrieve the entries for this playlist.
	 *
	 * @since	1.3
	 * @param	int		$per_page		The number of items to display per page
	 * @param	int		$page_num		The current page number
	 * @return	arr		The array of data to display within the table.
	 */
	private function get_entries( $per_page = -1, $page_num = 1 )	{
		
		if ( ! isset( $_GET['event_id'] ) )	{
			return false;
		}
		
		$result = array();
		
		$mdjm_event = new MDJM_Event( $_GET['event_id'] );
		
		$orderby	= isset( $_GET['orderby'] )	? $_GET['orderby']	: 'category';
		$order		= isset( $_GET['order'] )	? $_GET['order']	: 'ASC';
		
		if( $orderby == 'category' )	{
			
			$args = array(
				'orderby'		=> 'name',
				'order'			=> $order,
				'hide_empty'	=> true
			);
			
			$playlist = mdjm_get_playlist_by_category( $mdjm_event->ID, $args );
			
			if ( $playlist )	{
				
				foreach( $playlist as $cat => $entries )	{
										
					foreach( $entries as $entry )	{
						
						$entry_data = mdjm_get_playlist_entry_data( $entry->ID );
						
						$result[] = array(
							'ID'		=> $entry->ID,
							'event'		=> $mdjm_event->ID,
							'artist'	=> stripslashes( $entry_data['artist'] ),
							'song'		=> stripslashes( $entry_data['song'] ),
							'added_by'	=> stripslashes( $entry_data['added_by'] ),
							'category'	=> $cat,
							'notes'		=> stripslashes( $entry_data['djnotes'] ),
							'date'		=> mdjm_format_short_date( $entry->post_date )
						);
						
					}
	
				}
				
			}
			
		}
		else	{
			$args = array(
					'orderby'	=> $orderby == 'date' ? 'post_date' : 'meta_value',
					'order'		=> $order,
					'meta_key'	=> $orderby == 'date' ? '' : '_mdjm_playlist_entry_' . $orderby
			);
			
			$entries = mdjm_get_playlist_entries( $mdjm_event->ID, $args );
			
			if( $entries )	{
				foreach( $entries as $entry )	{
					$entry_data = mdjm_get_playlist_entry_data( $entry->ID );
					
					$categories = wp_get_object_terms( $entry->ID, 'playlist-category' );
									
					if ( ! empty( $categories ) )	{
						$category = $categories[0]->name;
					}
												
					$result[] = array(
						'ID'		=> $entry->ID,
						'event'		=> $mdjm_event->ID,
						'artist'	=> stripslashes( $entry_data['artist'] ),
						'song'		=> stripslashes( $entry_data['song'] ),
						'added_by'	=> stripslashes( $entry_data['added_by'] ),
						'category'	=> ! empty( $category ) ? $category : '',
						'notes'		=> stripslashes( $entry_data['djnotes'] ),
						'date'		=> mdjm_format_short_date( $entry->post_date )
					);
				}
			}
			
		}
		
		return apply_filters( 'mdjm_list_event_playlist', $result, $mdjm_event->ID );
		
	} // get_entries
		
	/**
	 * Delete entries from the playlist.
	 *
	 * @since	1.3
	 * @param	int		$id		The playlist entry ID
	 * @return
	 */
	public function delete_entry( $id )	{
		mdjm_remove_stored_playlist_entry( $id );
	} // delete_entry
	
	/**
	 * Count entries in the playlist.
	 *
	 * @since	1.3
	 * @param	int		$event_id	The event ID
	 * @return
	 */
	public function count_entries( $event_id )	{
		return mdjm_count_playlist_entries( $event_id );
	} // count_entries
	
	/**
	 * Text displayed when their are no entries.
	 *
	 * @since	1.3
	 * @param
	 * @return
	 */
	public function no_items() {
	  _e( 'No entries in this playlist.', 'mobile-dj-manager' );
	}
	
	/**
	 * Default display for columns that do not have a method defined.
	 *
	 * @since	1.3
	 * @param	str		$item			The playlist data
	 * @param	str		$column_name	The table column name
	 * @return	str
	 */
	public function column_default( $item, $column_name )	{
		
		switch( $column_name )	{ 
			case 'ID':
			case 'song':
			case 'artist':
			case 'category':
			case 'notes':
			case 'added_by':
			case 'date':
				return $item[ $column_name ];
			
			default:
				return print_r( $item, true ) ; // Show the whole array for troubleshooting purposes
		}
		
	} // column_default

	/**
	 * Data for the song column.
	 *
	 * @since	1.3
	 * @param	arr		$item		The current item
	 * @return	str
	 */
	public function column_song( $item )	{
						
		$title = '<strong>' . $item['song'] . '</strong>';

		$url = add_query_arg(
			array(
				'mdjm-action'	=> 'delete_song',
				'id'			=> absint( $item['ID'] ),
				'mdjm_nonce'	=> wp_create_nonce( 'remove_playlist_entry' )
			)
		);
		$actions['delete'] = mdjm_employee_can( 'manage_events' ) ? sprintf( __( '<a href="%s">Delete</a>', 'mobile-dj-manager' ), $url ) : '';

		return $title . $this->row_actions( $actions );
		
	} // column_song
	
	/**
	 * Data for the added_by column.
	 *
	 * @since	1.3
	 * @param	arr		$item		The current item
	 * @return	str
	 */
	public function column_added_by( $item )	{
		
		if ( is_numeric( $item['added_by'] ) )	{
			$user = get_userdata( $item['added_by'] );
			
			$name = $user->display_name; 
		} else	{
			$name = $item['added_by'];
		}

		return $name;
		
	} // column_added_by
	
	/**
	 * Render the checkbox column.
	 *
	 * @since	1.3
	 * @param	arr		$item		The current item
	 * @return	str
	 */
	public function column_cb( $item )	{
		return sprintf( '<input type="checkbox" name="mdjm-playlist-bulk-delete[]" value="%s" />', $item['ID'] );
	} // column_song
	
	/**
	 * Define the table columns.
	 *
	 * @since	1.3
	 * @param
	 * @return	arr		$columns	Array of columns
	 */
	public function get_columns()	{
		
		$columns = array(
			'cb'		=> '<input type="checkbox" />',
			'song'		=> __( 'Song', 'mobile-dj-manager' ),
			'artist'	=> __( 'Artist', 'mobile-dj-manager' ),
			'category'	=> __( 'Category', 'mobile-dj-manager' ),
			'notes'		=> __( 'Notes', 'mobile-dj-manager' ),
			'added_by'	=> __( 'Added By', 'mobile-dj-manager' ),
			'date'		=> __( 'Date Added', 'mobile-dj-manager' )
		);
		
		if( ! mdjm_employee_can( 'manage_events' ) )	{
			unset( $columns['cb'] );
		}

		return $columns;
		
	} // column_song
	
	/**
	 * Define which table columns are sortable.
	 *
	 * @since	1.3
	 * @param
	 * @return	arr		$sortable_columns	Array of sortable columns
	 */
	public function get_sortable_columns()	{
		
		$sortable_columns = array(
			'song'		=> array( 'song', false ),
			'artist'	=> array( 'artist', false ),
			'category'	=> array( 'category', true ),
			'added_by'	=> array( 'added_by', false ),
			'date'		=> array( 'date', false )
		);

		return $sortable_columns;
		
	} // get_sortable_columns
	
	/**
	 * Define the available bulk actions.
	 *
	 * @since	1.3
	 * @param
	 * @return	arr		$actions	Array of bulk actions
	 */
	public function get_bulk_actions() {
		
		$actions = array();
		
		if( mdjm_employee_can( 'manage_events' ) )	{
			$actions['bulk-delete'] = 'Delete';
		}
		
		return $actions;
		
	} // get_bulk_actions
	
	/**
	 * Define the category views.
	 *
	 * @since	1.3
	 * @param
	 * @return	arr		$views		Category views
	 */
	public function get_views() {
		
		$views		= array();
		$current	= ( !empty( $_GET['view_cat'] ) ? $_GET['view_cat'] : 'all' );
		
		$categories = mdjm_get_playlist_categories( $_GET['event_id'] );
		
		if( $categories )	{
			$class		= ( $current == 'all' ? ' class="current"' : '' );
			$all_url	= remove_query_arg( 'view_cat' );
			
			$views['all'] = sprintf( 
								__( '<a href="%s" %s >All</a>', 'mobile-dj-manager' ),
								$all_url,
								$class
							) .
							'<span class="count">' . mdjm_count_playlist_entries( $_GET['event_id'] ) . '</span>';
							
			foreach( $categories as $category )	{
				
				$count = mdjm_count_playlist_entries( $_GET['event_id'], $category->name );
				
				if( $count > 0 )	{
				
					$view_url = add_query_arg( 'view_cat', $category->name );
					$class = ( $current == $category->name ? ' class="current"' :'') ;
					
					$views[ $category->name ] = '<a href="' . $view_url . '" ' . $class . ' >' . $category->name . '</a>' .
								'<span class="count">(' . $count . ')</span>';
								
				}
			}

		}
		
		return $views;
				
	} // get_views
	
	/**
	 * Displays the playlist details.
	 *
	 * @since	1.3
	 * 
	 * 
	 * @return	str
	 */
	function display_header()	{
		?>
        <p><?php printf( __( '<strong>Date</strong>: %s', 'mobile-dj-manager'), mdjm_get_event_long_date( $_GET['event_id'] ) ); ?>
        <br />
        <?php printf( __( '<strong>Status</strong>: %s', 'mobile-dj-manager'), mdjm_get_event_status( $_GET['event_id'] ) ); ?>
        <br />
        <?php printf( __( '<strong>Type</strong>: %s', 'mobile-dj-manager'), mdjm_get_event_type( $_GET['event_id'] ) ); ?>
        <br />
        <?php printf( __( '<strong>Primary Employee</strong>: %s', 'mobile-dj-manager'), mdjm_get_employee_display_name( mdjm_get_event_primary_employee_id( $_GET['event_id'] ) ) ); ?>
        <br />
        <?php printf( __( '<strong>Client</strong>: %s', 'mobile-dj-manager'), mdjm_get_employee_display_name( mdjm_get_event_client_id( $_GET['event_id'] ) ) ); ?>
        <br />
        <?php printf( __( '<strong>Total Songs</strong>: %s', 'mobile-dj-manager'), count( $this->items ) ); ?>
        <br />
        <?php printf( __( '<strong>Current Status</strong>: %s', 'mobile-dj-manager'), mdjm_playlist_is_open( $_GET['event_id'] ) ? __( 'Open', 'mobile-dj-manager' ) : __( 'Closed', 'mobile-dj-manager' ) ); ?>
        </p>
        
        <?php
        if( $this->count_entries( $_GET['event_id'] ) > 0 )	:
			?>
        
            <p>
            <form method="post" target="_blank">
                <?php mdjm_admin_action_field( 'print_playlist' ); ?>
                <input type="hidden" name="print_playlist_event_id" id="print_playlist_event_id" value="<?php echo $_GET['event_id']; ?>" />
                <?php wp_nonce_field( 'print_playlist_entry', 'mdjm_nonce', true, true ); ?>
                <?php submit_button( 'Print this List', 'primary small', 'submit_print_pl', false ); ?> 
                <?php _e( 'ordered by', 'mobile-dj-manager' ); ?> <select name="print_order_by" id="print_order_by">
                <option value="date" selected="selected"><?php _e( 'Date Added', 'mobile-dj-manager' ); ?></option>
                <option value="artist"><?php _e( 'Artist Name', 'mobile-dj-manager' ); ?></option>
                <option value="song"><?php _e( 'Song Name', 'mobile-dj-manager' ); ?></option>
                <option value="category"><?php _e( 'Category', 'mobile-dj-manager' ); ?></option>
                </select> <?php _e( 'and repeating headers after', 'mobile-dj-manager' ); ?> <input type="text" name="print_repeat_headers" id="print_repeat_headers" class="small-text" value="20" /> <?php _e( 'rows', 'mobile-dj-manager' ); ?> <code><?php _e( 'Enter 0 for no repeat of headers', 'mobile-dj-manager' ); ?></code>
            </form>
                    
            <form method="post">
                <?php mdjm_admin_action_field( 'email_playlist' ); ?>
                <?php wp_nonce_field( 'email_playlist_entry', 'mdjm_nonce', true, true ); ?>
                <input type="hidden" name="email_playlist_event_id" id="email_playlist_event_id" value="<?php echo $_GET['event_id']; ?>" />
                <?php submit_button( 'Email this List', 'primary small', 'submit_email_pl', false ); ?> 
                <?php _e( 'ordered by', 'mobile-dj-manager' ); ?> <select name="email_order_by" id="email_order_by">
                <option value="date" selected="selected"><?php _e( 'Date Added', 'mobile-dj-manager' ); ?></option>
                <option value="artist"><?php _e( 'Artist Name', 'mobile-dj-manager' ); ?></option>
                <option value="song"><?php _e( 'Song Name', 'mobile-dj-manager' ); ?></option>
                <option value="category"><?php _e( 'Category', 'mobile-dj-manager' ); ?></option>
                </select> <?php _e( 'and repeating headers after', 'mobile-dj-manager' ); ?> <input type="text" name="repeat_headers" id="repeat_headers" class="small-text" value="20" /> <?php _e( 'rows', 'mobile-dj-manager' ); ?> <code><?php _e( 'Enter 0 for no repeat of headers', 'mobile-dj-manager' ); ?></code>
            </form>
            </p>
            <?php
		endif;
	} // display_header
	
	/**
	 * Prepare the table columns, pagination and data for the table
	 *
	 *
	 *
	 *
	 */
	public function prepare_items() {
		
		$columns  = $this->get_columns(); // Retrieve table columns
		$hidden   = array(); // Which fields are hidden
		$sortable = $this->get_sortable_columns(); // Which fields can be sorted by
		$this->_column_headers = array( $columns, $hidden, $sortable );
		
		$per_page     = $this->get_items_per_page( 'entries_per_page', 5 );
		$current_page = $this->get_pagenum();
		$total_items  = $this->count_entries( $_GET['event_id'] );
		
		$this->set_pagination_args( 
			array(
				'total_items' => $total_items, // We have to calculate the total number of items
				'per_page'    => $per_page // We have to determine how many items to show on a page
			)
		);
		
		$this->items = $this->get_entries( $per_page, $current_page );
		
	} // prepare_items

			
	function send_to_email( $post_data, $get_data )	{
		global $mdjm_settings, $wpdb, $current_user;
		if( ! isset( $get_data['event_id'] ) || empty( $get_data['event_id'] ) )	{
			return;	
		}
		else	{
			$email_query = 'SELECT * FROM `' . MDJM_PLAYLIST_TABLE . '` WHERE `event_id` = ' . $get_data['event_id'] . ' ORDER BY `' . $post_data['order_pl_by'] . '` ASC';
			$email_result = $wpdb->get_results( $email_query );
			$pl_ttl = $wpdb->num_rows;
			
			if( !isset( $post_data['repeat_headers'] ) || empty( $post_data['repeat_headers'] ) || $post_data['repeat_headers'] == 0 )	{
				$repeat = 0;
			}
			else	{
				$repeat = $post_data['repeat_headers'];
			}
			
			$i = 0;
			
			$eventinfo = get_post( $get_data['event_id'] );
			$client = get_userdata( get_post_meta( $eventinfo->ID, '_mdjm_event_client', true ) );
			get_currentuserinfo();
			
			$email_body = '<html>' . "\n" . '<body>' . "\n";
			
			$email_body .= '<p>Hey ' . $current_user->first_name . ',</p>' . "\n";
			$email_body .= '<p>Here is the playlist you requested.</p>' . "\n";
			
			$email_body .= '<p>Client Name: ' . $client->first_name . ' ' . $client->last_name . '<br />' . "\n";
			$email_body .= 'Event Date: ' . date( "l, jS F Y", strtotime( get_post_meta( $eventinfo->ID, '_mdjm_event_date', true ) ) ) . '<br />' . "\n";
			$email_body .= 'Event Type: ' . 
			$event_types = get_the_terms( $eventinfo->ID, 'event-types' );
			if( is_array( $event_types ) )	{
				foreach( $event_types as $key => $event_type ) {
					$event_types[$key] = $event_type->name;
				}
				$email_body .= implode( "<br/>", $event_types );
			}
			$email_body .= '<br />' . "\n";
			$email_body .= 'No. Songs in Playlist: ' . $pl_ttl . '<br /></p>' . "\n";
			$email_body .= '<hr />' . "\n";
			
			$email_body .= '<table width="100%" border="0" cellpadding="0" cellspacing="0">' . "\n";
			$email_body .= '<tr>' . "\n";
			$email_body .= '<th>Song Name</th>' . "\n";
			$email_body .= '<th>Artist</th>' . "\n";
			$email_body .= '<th>When to Play</th>' . "\n";
			$email_body .= '<th>Information</th>' . "\n";
			$email_body .= '<th>Added By</th>' . "\n";
			$email_body .= '</tr>' . "\n";
			
			foreach( $email_result as $pl_info )	{
				if( $repeat > 0 && $i == $repeat )	{
					$email_body .= '<tr>' . "\n";
					$email_body .= '<td colspan="5">&nbsp;</td>' . "\n";
					$email_body .= '</tr>' . "\n";
					$email_body .= '<tr>' . "\n";
					$email_body .= '<th>Song Name</th>' . "\n";
					$email_body .= '<th>Artist</th>' . "\n";
					$email_body .= '<th>When to Play</th>' . "\n";
					$email_body .= '<th>Information</th>' . "\n";
					$email_body .= '<th>Added By</th>' . "\n";
					$email_body .= '</tr>' . "\n";
					
					$i = 0;
				}
				
				$email_body .= '<tr>' . "\n";
				$email_body .= '<td>' . stripslashes( $pl_info->song ) . '</td>' . "\n";
				$email_body .= '<td>' . stripslashes( $pl_info->artist ) . '</td>' . "\n";
				$email_body .= '<td>' . stripslashes( $pl_info->play_when ) . '</td>' . "\n";
				$email_body .= '<td>' . stripslashes( $pl_info->info ) . '</td>' . "\n";
				$email_body .= '<td>' . stripslashes( $pl_info->added_by ) . '</td>' . "\n";
				$email_body .= '</tr>' . "\n";
				
				$i++;
			}
			
			$email_body .= '</table>' . "\n";
			$email_body .= '<p>Regards</p>' . "\n";
			$email_body .= '<p>' . MDJM_COMPANY . '</p>' . "\n";
			$email_body .= '<p>&nbsp;</p>' . "\n";
			$email_body .= '<p align="center" style="font-size: 9px">Powered by <a style="color:#F90" href="http://mdjm.co.uk" target="_blank">' . MDJM_NAME . '</a> version ' . MDJM_VERSION_NUM . '</p>' . "\n";
			$email_body .= '</body>' . "\n" . '</html>' . "\n";
			
			$headers = 'MIME-Version: 1.0' . "\r\n";
			$headers .= 'Content-type: text/html; charset=UTF-8' . "\r\n";
			$headers .= 'From: ' . MDJM_COMPANY . ' <' . $mdjm_settings['email']['system_email'] . '>' . "\r\n";
			
			if( wp_mail( $current_user->user_email, 'Event Playlist for ' . date( "l, jS F Y", strtotime( get_post_meta( $eventinfo->ID, '_mdjm_event_date', true ) ) ), $email_body, $headers ) )	{
				mdjm_update_notice( 'updated', 'Playlist successfully emailed to <a href="mailto:' . $current_user->user_email . '">' . $current_user->display_name . '</a>' );	
			}
			else	{
				mdjm_update_notice( 'error', 'Unable to email playlist' );	
			}
			
		}
	} // send_to_email
			
} // MDJM_PlayList_Table