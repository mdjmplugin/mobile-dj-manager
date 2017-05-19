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
	 * Outputs the form for adding an entry
	 *
	 * @access 	public
	 * @since	1.4
	 * @return	void
	 */
	public function entry_form() {
		?>
        <h3><?php _e( 'Add Entry to Playlist', 'mobile-dj-manager' ); ?></h3>
        <form id="mdjm-playlist-form" name="mdjm-playlist-form" action="" method="post">
			<?php wp_nonce_field( 'add_playlist_entry', 'mdjm_nonce', true, true ); ?>
            <?php mdjm_admin_action_field( 'add_playlist_entry' ); ?>
            <input type="hidden" id="entry_event" name="entry_event" value="<?php echo $_GET['event_id']; ?>" />
            <input type="hidden" id="entry_addedby" name="entry_addedby" value="<?php echo mdjm_get_event_client_id( $_GET['event_id'] ); ?>" />
            <table id="mdjm-playlist-form-table">
                <tr>
                    <td>
                        <label for="entry_song"><?php _e( 'Song', 'mobile-dj-manager' ); ?></label><br />
                        <?php echo MDJM()->html->text( array(
							'name' => 'entry_song',
							'type' => 'text'
						) ); ?>
                    </td>
                    
                    <td class="mdjm-playlist-artist-cell">
                        <label for="entry_artist"><?php _e( 'Artist', 'mobile-dj-manager' ); ?></label><br />
                        <?php echo MDJM()->html->text( array(
							'name'  => 'entry_artist',
							'type'  => 'text'
						) ); ?>
                    </td>

                    <td class="mdjm-playlist-category-cell">
                        <label for="entry_category"><?php _e( 'Category', 'mobile-dj-manager' ); ?></label><br />
                        <?php $playlist_categories = mdjm_get_playlist_categories(); ?>
                        <?php $options = array(); ?>
                        <?php foreach( $playlist_categories as $playlist_category ) : ?>
                        	<?php $options[ $playlist_category->term_id ] = $playlist_category->name; ?>
                        <?php endforeach; ?>
                        <?php echo MDJM()->html->select( array(
							'options'          => $options,
							'name'             => 'entry_category',
							'selected'         => mdjm_get_option( 'playlist_default_cat', 0 )
						) ); ?>
                    </td>

				</tr>
                <tr>

                    <td class="mdjm-playlist-djnotes-cell" colspan="3">
                        <label for="mdjm_playlist_djnotes"><?php printf( __( 'Notes', 'mobile-dj-manager' ), '{artist_label}' ); ?></label><br />
                        <?php echo MDJM()->html->textarea( array(
							'name'        => 'entry_djnotes'
						) ); ?>
                    </td>
                </tr>
            </table>
            <?php submit_button(
				__( 'Add to Playlist', 'mobile-dj-manager' ),
				'primary'
			); ?>
        </form>
        <?php
	} // entry_form

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
			
} // MDJM_PlayList_Table
