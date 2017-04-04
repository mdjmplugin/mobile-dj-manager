<?php
/**
 * Tasks Table Class
 *
 * @package     MDJM
 * @subpackage  Admin/Tasks
 * @copyright   Copyright (c) 2017, Mike Howard
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.4.7
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) )
	exit;

// Load WP_List_Table if not loaded
if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

/**
 * MDJM_Tasks_Table Class
 *
 * Renders the Tasks table
 *
 * @since	1.4.7
 */
class MDJM_Tasks_Table extends WP_List_Table {

	/**
	 * Number of items per page
	 *
	 * @var		int
	 * @since	1.4.7
	 */
	public $per_page = 30;

	/**
	 * Number of customers found
	 *
	 * @var		int
	 * @since	1.4.7
	 */
	public $count = 0;

	/**
	 * Total customers
	 *
	 * @var	int
	 * @since	1.4.7
	 */
	public $total = 0;

	/**
	 * The arguments for the data set
	 *
	 * @var		arr
	 * @since	1.4.7
	 */
	public $args = array();

	/**
	 * Get things started
	 *
	 * @since	1.4.7
	 * @see WP_List_Table::__construct()
	 */
	public function __construct() {
		global $status, $page;

		// Set parent defaults
		parent::__construct( array(
			'singular' => __( 'Task', 'mobile-dj-manager' ),
			'plural'   => __( 'Tasks', 'mobile-dj-manager' ),
			'ajax'     => false,
		) );
	} // __construct

	/**
	 * Show the search field
	 *
	 * @since	1.4.7
	 * @access	public
	 *
	 * @param	str		$text		Label for the search box
	 * @param	str		$input_id	ID of the search box
	 * @return void
	 */
	public function search_box( $text, $input_id ) {
		$input_id = $input_id . '-search-input';

		if ( ! empty( $_REQUEST['orderby'] ) ) : ?>
			<input type="hidden" name="orderby" value="<?php echo esc_attr( $_REQUEST['orderby'] ); ?>" />
        <?php endif;

		if ( ! empty( $_REQUEST['order'] ) ) : ?>
			<input type="hidden" name="order" value="<?php echo esc_attr( $_REQUEST['order'] ); ?>" />
		<?php endif; ?>

		<p class="search-box">
			<label class="screen-reader-text" for="<?php echo $input_id ?>"><?php echo $text; ?>:</label>
			<input type="search" id="<?php echo $input_id ?>" name="s" value="<?php _admin_search_query(); ?>" />
			<?php submit_button( $text, 'button', false, false, array('ID' => 'search-submit') ); ?>
		</p>
		<?php
	} // search_box

	/**
	 * Gets the name of the primary column.
	 *
	 * @since	1.4.7
	 * @access	protected
	 *
	 * @return	str		Name of the primary column.
	 */
	protected function get_primary_column_name() {
		return 'name';
	} // get_primary_column_name

	/**
	 * This function renders most of the columns in the list table.
	 *
	 * @access	public
	 * @since	1.4.7
	 *
	 * @param	arr		$item			Contains all the data of the tasks
	 * @param	str		$column_name	The name of the column
	 *
	 * @return string Column Name
	 */
	public function column_default( $item, $column_name ) {
		switch ( $column_name ) {

			case 'totalruns':
				$value = $item['totalruns'];
				break;

			case 'lastran':
				if ( 'Never' == $item['lastran'] )	{
					$value = __( 'Never', 'mobile-dj-manager' );
				} else	{
					$value = date_i18n( get_option( 'time_format' ), $item['lastran'] );
					$value .= '<br />';
					$value .= date_i18n( get_option( 'date_format' ), $item['lastran'] );
				}
				break;

			case 'nextrun':
				if ( empty( $item['active'] ) )	{
					$value = __( 'Inactive', 'mobile-dj-manager' );
				} else	{
					$value = date_i18n( get_option( 'time_format' ), $item['nextrun'] );
					$value .= '<br />';
					$value .= date_i18n( get_option( 'date_format' ), $item['nextrun'] );
				}
				break;

			default:
				$value = isset( $item[ $column_name ] ) ? $item[ $column_name ] : null;
				break;
		}

		return apply_filters( 'mdjm_tasks_column_' . $column_name, $value, $item['id'] );
	} // column_default

	public function column_active( $item )	{
		$checked  = checked( 1, $item['active'], false );
		$disabled = ! empty( $item['default'] ) ? ' disabled="disabled"' : '';
		
		return '<input type="checkbox" name="active_task_' . $item['id'] . '"' . $checked . $disabled . ' />'; 
	} // column_active

	public function column_name( $item ) {
		$name     = $item['name'];

		$edit_url = add_query_arg( array(
			'post_type' => 'mdjm-event',
			'page'      => 'mdjm-tasks',
			'view'      => 'task',
			'id'        => $item['id']
		), admin_url( 'edit.php' ) );

		$delete_url = add_query_arg( array(
			'post_type'   => 'mdjm-event',
			'page'        => 'mdjm-tasks',
			'id'          => $item['id'],
			'mdjm-action' => 'delete_task'   
		), admin_url( 'edit.php' ) );

		$activate_url = add_query_arg( array(
			'post_type'   => 'mdjm-event',
			'page'        => 'mdjm-tasks',
			'id'          => $item['id'],
			'mdjm-action' => 'activate_task'   
		), admin_url( 'edit.php' ) );

		$deactivate_url = add_query_arg( array(
			'post_type'   => 'mdjm-event',
			'page'        => 'mdjm-tasks',
			'id'          => $item['id'],
			'mdjm-action' => 'deactivate_task'   
		), admin_url( 'edit.php' ) );

		$run_now_url = add_query_arg( array(
			'post_type'   => 'mdjm-event',
			'page'        => 'mdjm-tasks',
			'id'          => $item['id'],
			'mdjm-action' => 'run_task'   
		), admin_url( 'edit.php' ) );

		$actions  = array(
			'view'   => '<a href="' . $edit_url . '">' . __( 'Edit', 'mobile-dj-manager' ) . '</a>'
		);

		if ( 'upload-playlists' != $item['id'] )	{
			if ( empty( $item['active'] ) )	{
				$actions['activate'] = '<a href="' . $activate_url . '">' . __( 'Activate', 'mobile-dj-manager' ) . '</a>';
			} else	{
				$actions['deactivate'] = '<a class="mdjm-delete" href="' . $deactivate_url . '">' . __( 'Deactivate', 'mobile-dj-manager' ) . '</a>';
			}
		}

		if ( ! empty( $item['active'] ) )	{
			$actions['run'] = '<a href="' . $run_now_url . '">' . __( 'Run Task', 'mobile-dj-manager' ) . '</a>';
		}

		if ( empty( $item['default'] ) )	{
			$actions['delete'] = '<a class="mdjm-delete" href="' . $delete_url . '">' . __( 'Delete', 'mobile-dj-manager' ) . '</a>';
		}

		$output = '<a href="' . esc_url( $edit_url ) . '">' . $name . '</a>' . $this->row_actions( $actions );

		return $output;
	} // column_name

	/**
	 * Retrieve the table columns
	 *
	 * @access	public
	 * @since	1.4.7
	 * @return	arr		$columns	Array of all the list table columns
	 */
	public function get_columns() {
		$columns = array(
			'active'      => __( 'Active', 'mobile-dj-manager' ),
			'name'        => __( 'Name', 'mobile-dj-manager' ),
			'frequency'   => __( 'Runs', 'mobile-dj-manager' ),
			'description' => __( 'Description', 'mobile-dj-manager' ),
			'totalruns'   => __( 'Total Runs', 'mobile-dj-manager' ),
			'lastran'     => __( 'Last Run', 'mobile-dj-manager' ),
			'nextrun'     => __( 'Next Run', 'mobile-dj-manager' )
		);

		return apply_filters( 'mdjm_tasks_columns', $columns );
	} // get_columns

	/**
	 * Get the sortable columns
	 *
	 * @access	public
	 * @since	1.4.7
	 * @return	arr		Array of all the sortable columns
	 */
	public function get_sortable_columns() {
		$sortable = array();

		return apply_filters( 'mdjm_task_table_sortable_columns', $sortable );
	} // get_sortable_columns

	/**
	 * Outputs the reporting views
	 *
	 * @access	public
	 * @since	1.4.7
	 * @return	void
	 */
	public function bulk_actions( $which = '' ) {
	} // bulk_actions

	/**
	 * Retrieve the current page number
	 *
	 * @access	public
	 * @since	1.4.7
	 * @return	int		Current page number
	 */
	public function get_paged() {
		return isset( $_GET['paged'] ) ? absint( $_GET['paged'] ) : 1;
	} // get_paged

	/**
	 * Retrieves the search query string
	 *
	 * @access	public
	 * @since	1.4.7
	 * @return	mixed	String if search is present, false otherwise
	 */
	public function get_search() {
		return ! empty( $_GET['s'] ) ? urldecode( trim( $_GET['s'] ) ) : false;
	} // get_search

	/**
	 * Build all the reports data
	 *
	 * @access	public
	 * @since	1.4.7
	 * @global	obj		$wpdb			Used to query the database using the WordPress
	 * @return	arr		$reports_data	All the data for customer reports
	 */
	public function reports_data() {

		$data       = array();
		$paged      = $this->get_paged();
		$offset     = $this->per_page * ( $paged - 1 );
		$search     = $this->get_search();
		$order      = isset( $_GET['order'] )      ? sanitize_text_field( $_GET['order'] )   : 'DESC';
		$orderby    = isset( $_GET['orderby'] )    ? sanitize_text_field( $_GET['orderby'] ) : 'id';

		$tasks      = mdjm_get_tasks();

		if ( $tasks ) {
			foreach ( $tasks as $task ) {

				if ( 'upload-playlists' == $task['slug'] )	{
					$task['active'] = mdjm_get_option( 'upload_playlists' );
				}

				$data[] = array(
					'id'           => $task['slug'],
					'name'         => $task['name'],
					'active'       => ! empty( $task['active'] ) ? true : false,
					'frequency'    => $task['frequency'],
					'description'  => $task['desc'],
					'totalruns'    => absint( $task['totalruns'] ),
					'lastran'      => $task['lastran'],
					'nextrun'      => $task['nextrun'],
					'default'      => ! empty( $task['default'] ) ? true : false
				);

			}
		}

		return $data;
	} // reports_data

	/**
	 * Setup the final data for the table.
	 *
	 * @access	public
	 * @since	1.4.7
	 * @uses	MDJM_Tasks_Table::get_columns()
	 * @uses	WP_List_Table::get_sortable_columns()
	 * @uses	MDJM_Tasks_Table::get_pagenum()
	 * @uses	MDJM_Tasks_Table::get_total_customers()
	 * @return	void
	 */
	public function prepare_items() {
		$columns  = $this->get_columns();
		$hidden   = array(); // No hidden columns
		$sortable = $this->get_sortable_columns();

		$this->_column_headers = array( $columns, $hidden, $sortable );

		$this->items = $this->reports_data();

		$this->total = count( $this->items );

		$this->set_pagination_args( array(
			'total_items' => $this->total,
			'per_page'    => $this->per_page,
			'total_pages' => ceil( $this->total / $this->per_page ),
		) );
	} // prepare_items
} // MDJM_Tasks_Table
