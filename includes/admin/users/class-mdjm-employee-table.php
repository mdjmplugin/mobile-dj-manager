<?php
defined( 'ABSPATH' ) || die( 'Direct access to this page is disabled!!!' );

if ( ! mdjm_employee_can( 'manage_employees' ) ) {
	wp_die(
		'<h1>' . esc_html__( 'Cheatin&#8217; uh?', 'mobile-dj-manager' ) . '</h1>' .
		'<p>' . esc_html__( 'You do not have permission to manage employees or permissions.', 'mobile-dj-manager' ) . '</p>',
		403
	);
}

/**
 * The user table class extended WP_List_Table
 *
 *
 *
 *
 */
if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

class MDJM_Employee_Table extends WP_List_Table {
	public function __construct() {
		parent::__construct( array(
			'singular' => 'mdjm_list_employee', //Singular label
			'plural'   => 'mdjm_list_employees', //plural label, also this well be one of the table css class
			'ajax'     => false, //We won't support Ajax for this table
		) );
	}

	/**
	 * Define items/data to be displayed before and after the list table
	 *
	 * @param   str     $action     Required: top for top of the table or bottom
	 *
	 * @return  str                 The HTML to be output
	 */
	public function extra_tablenav( $which ) {
		if ( $which == 'top' ) {
			?>
			<div class="alignleft actions">
				<label class="screen-reader-text" for="new_role"><?php esc_html_e( 'Change role to', 'mobile-dj-manager' ); ?>&hellip;</label>
				<select name="new_role" id="new_role">
					<option value=""><?php esc_html_e( 'Change role to', 'mobile-dj-manager' ); ?>&hellip;</option>
					<?php
					add_filter( 'mdjm_user_roles', array( MDJM()->roles, 'no_admin_role' ) );
					echo MDJM()->roles->roles_dropdown(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
					remove_filter( 'mdjm_user_roles', array( MDJM()->roles, 'no_admin_role' ) );
					?>
				</select>
				<input type="submit" name="change_role" id="change_role" class="button" value="<?php esc_attr_e( 'Change', 'mobile-dj-manager' ); ?>" />
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
	 * @return  $arr    $columns    The table column IDs and names
	 */
	public function get_columns() {
		$columns = array(
			'cb'     => '<input type="checkbox" />',
			'name'   => __( 'Name', 'mobile-dj-manager' ),
			'role'   => __( 'Role(s)', 'mobile-dj-manager' ),
			'events' => __( 'Events', 'mobile-dj-manager' ),
			//'earnings'  => __( 'Earnings', 'mobile-dj-manager' ),
			'login'  => __( 'Last Login', 'mobile-dj-manager' ),
		);

		return $columns;
	} // get_columns

	/**
	 * This is where we define the layout of the list table and the data to be used
	 *
	 * @param
	 *
	 * @return
	 */
	public function prepare_items() {
		// The data is prepared in the MDJM_Employee_Manager class
		$employees = MDJM_Employee_Manager::$employees;

		// Prepare columns
		$columns               = $this->get_columns();
		$hidden                = array();
		$sortable              = array();
		$this->_column_headers = array( $columns, $hidden, $sortable );

		// Pagination. TODO
		$per_page     = 5;
		$current_page = $this->get_pagenum();

		$total_items = count( $employees );

		$this->items = $employees;
	} // prepare_items

	/**
	 * Specifies the default text to be displayed when there is no data
	 *
	 * @param
	 *
	 * @return  str     The text to be displayed when there are no results to display
	 */
	public function no_items() {
		esc_html_e( "No Employee's found.", 'mobile-dj-manager' );
	} // no_items

	/**
	 * Specifies the default data to be displayed within columns that do not have a
	 * defined method within this class
	 *
	 * @param   obj     $item           The object array for the current data object
	 *          str     $column_name    The name of the current column
	 *
	 * @return  str     The data to be output into the column
	 */
	public function column_default( $item, $column_name ) {
		global $wp_roles;

		switch ( $column_name ) {
			default:
				return;
		}
	} // column_default

	/**
	 * Create the HTML output for the checkboxes column
	 *
	 * @param   obj     $item   The object array for the current item
	 *
	 * @return  str     The HTML output for the checkbox column
	 */
	public function column_cb( $item ) {
		echo '<input type="checkbox" name="employees[]" id="employees-' . esc_attr( $item->ID ) . '"';

		if ( in_array( 'administrator', $item->roles ) ) {
			echo ' disabled="disabled"';
		}

		echo ' value="' . esc_attr( $item->ID ) . '" />';
	} // column_cb

	/**
	 * Create the HTML output for the name column
	 *
	 * @param   obj     $item   The object array for the current item
	 *
	 * @return  str     The HTML output for the checkbox column
	 */
	public function column_name( $item ) {
		if ( current_user_can( 'edit_users' ) || $item->ID == get_current_user_id() ) {
			$edit_users = true;
		}

		if ( ! empty( $edit_users ) ) {
			echo '<a href="' . esc_url( get_edit_user_link( $item->ID ) ) . '">';
		}

		echo esc_html( $item->display_name );

		if ( ! empty( $edit_users ) ) {
			echo '</a>';
		}

		if ( mdjm_is_admin( $item->ID ) ) {
			echo '<br />' . __( '<em>MDJM Admin</em>', 'mobile-dj-manager' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		}

		if ( user_can( $item->ID, 'administrator' ) ) {
			echo '<br />' . __( '<em>WordPress Admin</em>', 'mobile-dj-manager' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		}

	} // column_name

	/**
	 * Create the HTML output for the role column
	 *
	 * @param   obj     $item   The object array for the current item
	 *
	 * @return  str     The HTML output for the checkbox column
	 */
	public function column_role( $item ) {

		global $wp_roles;

		if ( ! empty( $item->roles ) ) {

			foreach ( $item->roles as $role ) {

				if ( array_key_exists( $role, MDJM_Employee_Manager::$mdjm_roles ) ) {
					$roles[ $role ] = MDJM_Employee_Manager::$mdjm_roles[ $role ];
				}
			}
		}

		if ( ! empty( $roles ) ) {

			$i = 1;
			foreach ( $roles as $role_id => $role ) {

				printf( '%s%s%s',
					esc_attr( $item->roles[0] ) != $role_id ? '<span style="font-style: italic;">' : '',
					esc_html( translate_user_role( $wp_roles->roles[ $role_id ]['name'] ) ),
					esc_attr( $item->roles[0] ) != $role_id ? '</span>' : ''
				);

				if ( $i < count( $roles ) ) {
					echo '<br />';
				}

				$i++;

			}
		} else {
			echo esc_html__( 'No role assigned', 'mobile-dj-manager' );
		}

	} // column_role

	/**
	 * Create the HTML output for the events column
	 *
	 * @param   obj     $item   The object array for the current item
	 *
	 * @return  str     The HTML output for the checkbox column
	 */
	public function column_events( $item ) {
		$next_event   = mdjm_get_employees_next_event( $item->ID );
		$total_events = mdjm_count_employee_events( $item->ID );

		printf(
			__( 'Next: %s', 'mobile-dj-manager' ),
			! empty( $next_event ) ? '<a href="' . esc_url( get_edit_post_link( $next_event->ID ) ) . '">' .
				esc_html( mdjm_format_short_date( get_post_meta( $next_event->ID, '_mdjm_event_date', true ) ) ) . '</a>' :
				esc_html__( 'None', 'mobile-dj-manager' )
		);

		echo '<br />';

		printf(
			__( 'Total: %s', 'mobile-dj-manager' ),
			! empty( $total_events ) ? '<a href="' . esc_url( admin_url( 'edit.php?s&post_type=mdjm-event&post_status=all' .
					'&action=-1&mdjm_filter_date=0&mdjm_filter_type&mdjm_filter_employee=' . $item->ID .
					'&mdjm_filter_client=0&filter_action=Filter&paged=1&action2=-1' ) ) . '">' . esc_html( $total_events ) . '</a>' :
				'0'
		);
	} // column_events

	/**
	 * Create the HTML output for the login column
	 *
	 * @param   obj     $item   The object array for the current item
	 *
	 * @return  str     The HTML output for the checkbox column
	 */
	public function column_login( $item ) {
		if ( '' != get_user_meta( $item->ID, 'last_login', true ) ) {
			echo esc_html( date_i18n( 'H:i d M Y', strtotime( get_user_meta( $item->ID, 'last_login', true ) ) ) );

		} else {
			echo esc_html__( 'Never', 'mobile-dj-manager' );
		}
	} // column_login

	/**
	 * Generate the role view filters
	 *
	 * @param
	 *
	 * @return  $views      Array of $view => $link
	 */
	public function get_views() {
		$views   = array();
		$current = MDJM_Employee_Manager::$display_role;

		// All roles link
		$class        = ( empty( $current ) || $current == 'all' ? ' class="current"' : '' );
		$all_url      = remove_query_arg( 'display_role' );
		$views['all'] = '<a href="' . $all_url . '" ' . $class . '>' . __( 'All', 'mobile-dj-manager' ) .
			' <span class="count">(' . MDJM_Employee_Manager::$total_employees . ')</span></a>';

		// Loop through all roles and generate the required views for each
		foreach ( MDJM_Employee_Manager::$mdjm_roles as $role_id => $role ) {
			$count = count( mdjm_get_employees( $role_id ) );

			if ( empty( $count ) ) {
				continue;
			}

			$class             = ( $current == $role_id ? ' class="current"' : '' );
			$role_url          = add_query_arg( 'display_role', $role_id );
			$views[ $role_id ] = '<a href="' . $role_url . '" ' . $class . '>' . $role .
				' <span class="count">(' . $count . ')</span></a>';
		}

		return $views;
	} // get_views

	/**
	 * Add the bulk actions to the table header and footer and define the options
	 *
	 * @params
	 *
	 * @return  arr     $actions        The options for the bulk action dropdown
	 */
	public function get_bulk_actions() {
		$actions = array(
			'delete' => __( 'Delete Employee', 'mobile-dj-manager' ),
		);

		foreach ( MDJM_Employee_Manager::$mdjm_roles as $role_id => $role ) {
			if ( $role_id !== 'administrator' ) {
				$actions[ 'add_role_' . $role_id ] = sprintf( __( 'Add %s Role', 'mobile-dj-manager' ), $role );
			}
		}

		return $actions;
	} // get_bulk_actions

	/**
	 * Process bulk actions if requested
	 *
	 * @param
	 *
	 * @return
	 */
	public function process_bulk_actions() {

		if ( 'delete' === $this->current_action() && ! empty( $_POST['employees'] ) ) {

			foreach ( array_map( 'sanitize_text_field', wp_unslash( $_POST['employees'] ) ) as $user_id ) {
				MDJM()->debug->log_it( 'Deleting employee with ID ' . $user_id, true );
				wp_delete_user( $user_id );
			}

			mdjm_update_notice( 'updated', __( 'Employee(s) deleted.', 'mobile-dj-manager' ), true );

		}

		// Determine if we are adding an additional role to a user
		foreach ( MDJM_Employee_Manager::$mdjm_roles as $role_id => $role ) {

			if ( 'add_role_' . $role_id === $this->current_action() && ! empty( $_POST['employees'] ) ) {

				foreach ( array_map( 'sanitize_text_field', wp_unslash( $_POST['employees'] ) ) as $user_id ) {

					MDJM()->debug->log_it( 'Adding additional role ' . $role . ' to user ' . $user_id, true );

					$e = new WP_User( $user_id );

					if ( ! in_array( $role_id, $e->roles ) ) {
						$e->add_cap( $role_id );
					}
				}

				mdjm_update_notice( 'updated', __( $role . ' added to employee(s).', 'mobile-dj-manager' ), true );

			}
		}

	} // process_bulk_actions

} // MDJM_Employee_Table
