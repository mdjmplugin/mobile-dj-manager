<?php
/**
 * This plugin utilizes Open Source code. Details of these open source projects along with their licenses can be found below.
 * We acknowledge and are grateful to these developers for their contributions to open source.
 *
 * Project: mobile-dj-manager https://github.com/deckbooks/mobile-dj-manager
 * License: (GNU General Public License v2.0) https://github.com/deckbooks/mobile-dj-manager/blob/master/license.txt
 *
 * @author: Mike Howard, Jack Mawhinney, Dan Porter
 */
	defined( 'ABSPATH' ) or die( 'Direct access to this page is disabled!!!' );

if ( ! mdjm_employee_can( 'manage_employees' ) ) {
	wp_die(
		'<h1>' . esc_html__( 'Cheatin&#8217; uh?', 'mobile-dj-manager' ) . '</h1>' .
		'<p>' . esc_html__( 'You do not have permission to manage employees or permissions.', 'mobile-dj-manager' ) . '</p>',
		403
	);
}

/**
 * The user table class extended WP_List_Table
 */
if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

class MDJM_Employee_Table extends WP_List_Table {
	public function __construct() {
		parent::__construct(
			array(
				'singular' => 'mdjm_list_employee', // Singular label
				'plural'   => 'mdjm_list_employees', // plural label, also this well be one of the table css class
				'ajax'     => false, // We won't support Ajax for this table
			)
		);
	}

	/**
	 * Define items/data to be displayed before and after the list table
	 *
	 * @param   str $action     Required: top for top of the table or bottom
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
			// 'earnings'  => __( 'Earnings', 'mobile-dj-manager' ),
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
	 * @param   obj $item           The object array for the current data object
	 *      str     $column_name    The name of the current column
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
	 * @param   obj $item   The object array for the current item
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
	 * @param   obj $item   The object array for the current item
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
	 * @param   obj $item   The object array for the current item
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

				printf(
					'%s%s%s',
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
	 * @param   obj $item   The object array for the current item
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
			! empty( $total_events ) ? '<a href="' . esc_url(
				admin_url(
					'edit.php?s&post_type=mdjm-event&post_status=all' .
					'&action=-1&mdjm_filter_date=0&mdjm_filter_type&mdjm_filter_employee=' . $item->ID .
					'&mdjm_filter_client=0&filter_action=Filter&paged=1&action2=-1'
				)
			) . '">' . esc_html( $total_events ) . '</a>' :
				'0'
		);
	} // column_events

	/**
	 * Create the HTML output for the login column
	 *
	 * @param   obj $item   The object array for the current item
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

/**
 * Class Name: MDJM_Employee_Manager
 * User management interface for employees
 */
if ( ! class_exists( 'MDJM_Employee_Manager' ) ) :
	class MDJM_Employee_Manager {
		private static $active_tab;
		public static $display_role;
		private static $orderby;
		private static $order;
		private static $all_roles;
		public static $employees;
		public static $total_employees;
		public static $mdjm_roles;
		private static $total_roles;
		private static $mdjm_employee_table;
		/**
		 * Init
		 */
		public static function init() {
			global $wp_roles;

			// Listen for post requests
			// Update the user role
			if ( isset( $_POST['change_role'], $_POST['new_role'], $_POST['employees'] ) ) {
				foreach ( array_map( 'sanitize_text_field', wp_unslash( $_POST['employees'] ) ) as $employee ) {
					mdjm_set_employee_role( $employee, sanitize_text_field( wp_unslash( $_POST['new_role'] ) ) );
				}

				mdjm_update_notice( 'updated', __( 'Employee roles updated.', 'mobile-dj-manager' ), true );
			}

			wp_enqueue_script( 'jquery-validation-plugin' );

			self::$all_roles = $wp_roles;

			// Filter our search by role if we need to
			self::$display_role = ! empty( $_GET['display_role'] ) ? sanitize_text_field( wp_unslash( $_GET['display_role'] ) ) : '';
			self::$orderby      = ! empty( $_GET['orderby'] ) ? sanitize_text_field( wp_unslash( $_GET['orderby'] ) ) : '';
			self::$order        = ! empty( $_GET['order'] ) ? sanitize_text_field( wp_unslash( $_GET['order'] ) ) : '';

			// Which tab?
			self::$active_tab = isset( $_GET['tab'] ) ? sanitize_text_field( wp_unslash( $_GET['tab'] ) ) : 'user_roles';

			// Display the page tabs
			self::page_header();

			// Retrieve all MDJM roles
			self::$mdjm_roles  = mdjm_get_roles();
			self::$total_roles = count( self::$mdjm_roles );

			// Determine the page to display
			if ( self::$active_tab == 'permissions' ) {
				self::permissions_manager();

			} else {
				// Instantiate the user table class
				self::$mdjm_employee_table = new MDJM_Employee_Table();
				self::$mdjm_employee_table->process_bulk_actions();
				// Retrieve employee list
				self::$employees       = empty( $_POST['s'] ) ? mdjm_get_employees( self::$display_role, self::$orderby, self::$order ) : self::search();
				self::$total_employees = count( mdjm_get_employees() );
				self::$mdjm_employee_table->prepare_items();

				// The header for the user management page
				self::employee_page();
			}
		} // init

		public static function search() {

			foreach ( self::$mdjm_roles as $role => $label ) {
				$roles[] = $role;
			}

			$employees = array();

			$args = array(
				'search'   => '*' . sanitize_text_field( wp_unslash( $_POST['s'] ) ) . '*', // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotValidated
				'role__in' => $roles,
			);

			// Execute the query
			$employee_query = new WP_User_Query( $args );

			$results = $employee_query->get_results();

			$employees = array_merge( $employees, $results );
			$employees = array_unique( $employees, SORT_REGULAR );

			return $employees;

		}

		/**
		 * Display the page header for the user management interface
		 */
		public static function page_header() {
			?>
			<div class="wrap">
			<div id="icon-themes" class="icon32"></div>
			<h1><?php esc_html_e( "Employee's &amp; Roles", 'mobile-dj-manager' ); ?></h1>
			<h2 class="nav-tab-wrapper">
				<a href="admin.php?page=mdjm-employees&amp;tab=user_roles" class="nav-tab
					<?php echo self::$active_tab == 'user_roles' ? 'nav-tab-active' : ''; ?>">
					<?php esc_html_e( 'Employees & Roles', 'mobile-dj-manager' ); ?>
				</a>

				<a href="admin.php?page=mdjm-employees&amp;tab=permissions" class="nav-tab
					<?php echo self::$active_tab == 'permissions' ? 'nav-tab-active' : ''; ?>">
					<?php esc_html_e( 'Permissions', 'mobile-dj-manager' ); ?>
				</a>
			</h2>
			<?php
		} // page_header

		/**
		 * Display the interface for managing users and roles
		 */
		public static function employee_page() {
			?>
			<form name="mdjm_employee_list" id="mdjm_employee_list" method="post">
			<?php
			wp_nonce_field( 'mdjm_user_list_table' );
			self::$mdjm_employee_table->views();
			?>
			<table style="width: 100%;">
			<tr valign="top">
			<td style="width: auto;"><?php self::$mdjm_employee_table->display(); ?></td>
			<td style="width: auto; vertical-align: top;">

			<table class="widefat" class="alternate">
			<tr>
			<td style="width: 250px;">
			<select name="all_roles[]" id="all_roles" multiple="multiple" style="min-width: 250px; height: auto;">
			<?php
			// Display the roles
			echo MDJM()->roles->roles_dropdown( array( 'disable_default' => true ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			?>
			</select>
			<br />
			<span style="font-size: smaller; font-style: italic;"><?php esc_html_e( 'Hold CTRL & click to select multiple entries', 'mobile-dj-manager' ); ?></span>
			</td>
			</tr>
			<tr>
			<td style="text-align: right;">
			<?php submit_button( __( 'Delete Selected Role(s)', 'mobile-dj-manager' ), 'delete', 'delete-roles', false ); ?>
			</td>
			</tr>
			<tr>
			<td>
			<input type="text" name="add_mdjm_role" id="add_mdjm_role" />&nbsp;<a id="new_mdjm_role" class="button button-primary" href="#"><?php esc_html_e( 'Add Role', 'mobile-dj-manager' ); ?></a><span id="pleasewait" style="display: none;" class="page-content"><img src="/wp-admin/images/loading.gif" alt="<?php esc_attr_e( 'Please wait...', 'mobile-dj-manager' ); ?>" /></span>
			</td>
			</tr>
			</table>
			</form>
			<?php
			self::add_employee_form();
			?>
			</td>
			</tr>
			</table>
			</div>
			<?php
		} // employee_page

		/**
		 * Display the form for adding a new employee to MDJM
		 */
		public static function add_employee_form() {
			// Ensure user has permssion to add an employee
			if ( ! mdjm_employee_can( 'manage_staff' ) ) {
				return;
			}

			?>
			<h3><?php esc_html_e( 'Employee Quick Add', 'mobile-dj-manager' ); ?></h3>
			<form name="mdjm_employee_add" id="mdjm_employee_add" method="post">
			<?php mdjm_admin_action_field( 'add_employee' ); ?>
			<?php wp_nonce_field( 'add_employee', 'mdjm_nonce', true, true ); ?>
			<table class="widefat">
			<tr>
			<td style="width: 250px;"><label class="mdjm-label" for="first_name"><?php esc_html_e( 'First Name', 'mobile-dj-manager' ); ?>:</label><br />
			<input type="text" name="first_name" id="first_name" required /></td>
			</tr>
			<tr>
			<td style="width: 250px;"><label class="mdjm-label" for="last_name"><?php esc_html_e( 'Last Name', 'mobile-dj-manager' ); ?>:</label><br />
			<input type="text" name="last_name" id="last_name" required /></td>
			</tr>
			<tr>
			<td style="width: 250px;"><label class="mdjm-label" for="user_email"><?php esc_html_e( 'Email Address', 'mobile-dj-manager' ); ?>:</label><br />
			<input type="text" name="user_email" id="user_email" required /></td>
			</tr>
			<tr>
			<td style="width: 250px;"><label class="mdjm-label" for="employee_role"><?php esc_html_e( 'Role', 'mobile-dj-manager' ); ?>:</label><br />
			<select name="employee_role" id="employee_role" required>
				<option value=""><?php esc_attr_e( 'Select Role', 'mobile-dj-manager' ); ?>&hellip;</option>
				<?php
				echo MDJM()->roles->roles_dropdown(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				?>
			</select>
			</td>
			</tr>
			<tr>
			<td style="text-align: right;"><?php submit_button( __( 'Add Employee', 'mobile-dj-manager' ), 'primary', 'mdjm-add-employee', false ); ?></td>
			</tr>
			</table>
			</form>
			<?php
		} // add_employee_form

		/**
		 * Display the interface for managing role permissions
		 */
		public static function permissions_manager() {
			?>
			<form name="mdjm_permissions" id="mdjm_permissions" method="post">
			<?php wp_nonce_field( 'mdjm_permissions_table' ); ?>
			<input type="hidden" name="mdjm_set_permissions" id="mdjm_set_permissions" />
			<table class="widefat fixed striped" style="width: 100%;">
			<thead>
			<tr>
			<td id="mdjm-emp-roles"><label class="screen-reader-text"><?php esc_html_e( 'Roles', 'mobile-dj-manager' ); ?></label></td>
			<th scope="col" id="mdjm-full-admin" style="font-size: small;"><strong><?php esc_html_e( 'MDJM Admin', 'mobile-dj-manager' ); ?></strong></th>
			<th scope="col" colspan="6" style="font-size: small;"><strong><?php esc_html_e( 'Permissions', 'mobile-dj-manager' ); ?></strong></th>
			</tr>
			</thead>
			<tbody id="the-list">
			<?php
			$i = 0;
			foreach ( self::$mdjm_roles as $role_id => $role ) {
				// Don't show the admin role as this cannot be changed within MDJM
				if ( $role_id == 'administrator' ) {
					continue;
				}

				$caps = get_role( $role_id );

				echo '<input type="hidden" name="employee_roles[]" value="' . esc_attr( $role_id ) . '" />' . "\r\n";
				echo '<tr' . ( $i == 0 ? ' class="alternate"' : '' ) . '>' . "\r\n";
				echo '<th scope="row" id="' . esc_attr( $role_id ) . '-role" class="manage-row row-' . esc_attr( $role_id ) . '-roles row-primary" style="font-size: small;"><strong>' . esc_html( $role ) . '</strong></th>' . "\r\n";

				echo '<td scope="row" style="font-size: small; vertical-align: middle;">';
					echo '<input type="checkbox" name="manage_mdjm_' . esc_attr( $role_id ) . '" id="manage_mdjm_' . esc_attr( $role_id ) . '" value="1" style="font-size: small;"';
				if ( ! empty( $caps->capabilities['manage_mdjm'] ) ) {
					echo ' checked="checked"';
				}
					echo ' />' . "\r\n";
				echo '</td>' . "\r\n";

				echo '<td scope="row" style="font-size: small;">';
					echo '<span style="font-size: small; font-weight: bold;">' . esc_html__( 'Clients', 'mobile-dj-manager' ) . ':</span><br />' . "\r\n";
					echo '<select name="client_permissions_' . esc_attr( $role_id ) . '" id="client_permissions_' . esc_attr( $role_id ) . '" style="font-size: small;">' . "\r\n";
					echo '<option value="mdjm_client_none">' . esc_html__( 'None', 'mobile-dj-manager' ) . '</option>' . "\r\n";

					echo '<option value="mdjm_client_edit_own"';
				if ( ! empty( $caps->capabilities['mdjm_client_edit_own'] ) ) {
					echo ' selected="selected"';
				}
					echo '>' . esc_html__( 'Edit Own', 'mobile-dj-manager' ) . '</option>' . "\r\n";

					echo '<option value="mdjm_client_edit"';
				if ( ! empty( $caps->capabilities['mdjm_client_edit'] ) ) {
					echo ' selected="selected"';
				}
					echo '>' . esc_html__( 'Edit All', 'mobile-dj-manager' ) . '</option>' . "\r\n";

					echo '</select>' . "\r\n";

					echo '<br /><br />' . "\r\n";

					echo '<span style="font-size: small; font-weight: bold;">' . esc_html__( 'Employees', 'mobile-dj-manager' ) . ':</span><br />' . "\r\n";
					echo '<select name="employee_permissions_' . esc_attr( $role_id ) . '" id="employee_permissions_' . esc_attr( $role_id ) . '" style="font-size: small;">' . "\r\n";
					echo '<option value="mdjm_employee_none">' . esc_html__( 'None', 'mobile-dj-manager' ) . '</option>' . "\r\n";

					echo '<option value="mdjm_employee_edit"';
				if ( ! empty( $caps->capabilities['mdjm_employee_edit'] ) ) {
					echo ' selected="selected"';
				}
					echo '>' . esc_html__( 'Manage', 'mobile-dj-manager' ) . '</option>' . "\r\n";

					echo '</select>' . "\r\n";

				echo '</td>' . "\r\n";

				echo '<td scope="row" style="font-size: small;">';
					echo '<span style="font-size: small; font-weight: bold;">' . esc_html__( 'Comms', 'mobile-dj-manager' ) . ':</span><br />' . "\r\n";
					echo '<select name="comm_permissions_' . esc_attr( $role_id ) . '" id="comm_permissions_' . esc_attr( $role_id ) . '" style="font-size: small;">' . "\r\n";
					echo '<option value="mdjm_comms_none">' . esc_html__( 'None', 'mobile-dj-manager' ) . '</option>' . "\r\n";

					echo '<option value="mdjm_comms_send"';
				if ( ! empty( $caps->capabilities['mdjm_comms_send'] ) ) {
					echo ' selected="selected"';
				}
					echo '>' . esc_html__( 'Send', 'mobile-dj-manager' ) . '</option>' . "\r\n";

					echo '</select>' . "\r\n";

					echo '<br /><br />' . "\r\n";

					echo '<span style="font-size: small; font-weight: bold;">' . esc_html__( 'Packages', 'mobile-dj-manager' ) . ':</span><br />' . "\r\n";
					echo '<select name="package_permissions_' . esc_attr( $role_id ) . '" id="package_permissions_' . esc_attr( $role_id ) . '" style="font-size: small;">' . "\r\n";
					echo '<option value="mdjm_package_none">' . esc_html__( 'None', 'mobile-dj-manager' ) . '</option>' . "\r\n";

					echo '<option value="mdjm_package_edit_own"';
				if ( ! empty( $caps->capabilities['mdjm_package_edit_own'] ) ) {
					echo ' selected="selected"';
				}
					echo '>' . esc_html__( 'Edit Own', 'mobile-dj-manager' ) . '</option>' . "\r\n";

					echo '<option value="mdjm_package_edit"';
				if ( ! empty( $caps->capabilities['mdjm_package_edit'] ) ) {
					echo ' selected="selected"';
				}
					echo '>' . esc_html__( 'Edit All', 'mobile-dj-manager' ) . '</option>' . "\r\n";

					echo '</select>' . "\r\n";

				echo '</td>' . "\r\n";

				echo '<td scope="row" style="font-size: small;">';
					echo '<span style="font-size: small; font-weight: bold;">' . esc_html__( 'Events', 'mobile-dj-manager' ) . ':</span><br />' . "\r\n";
					echo '<select name="event_permissions_' . esc_attr( $role_id ) . '" id="event_permissions_' . esc_attr( $role_id ) . '" style="font-size: small;">' . "\r\n";
					echo '<option value="mdjm_event_none">' . esc_html__( 'None', 'mobile-dj-manager' ) . '</option>' . "\r\n";

					echo '<option value="mdjm_event_read_own"';
				if ( ! empty( $caps->capabilities['mdjm_event_read_own'] ) ) {
					echo ' selected="selected"';
				}
					echo '>' . esc_html__( 'Read Own', 'mobile-dj-manager' ) . '</option>' . "\r\n";

					echo '<option value="mdjm_event_read"';
				if ( ! empty( $caps->capabilities['mdjm_event_read'] ) ) {
					echo ' selected="selected"';
				}
					echo '>' . esc_html__( 'Read All', 'mobile-dj-manager' ) . '</option>' . "\r\n";

					echo '<option value="mdjm_event_edit_own"';
				if ( ! empty( $caps->capabilities['mdjm_event_edit_own'] ) ) {
					echo ' selected="selected"';
				}
					echo '>' . esc_html__( 'Edit Own', 'mobile-dj-manager' ) . '</option>' . "\r\n";

					echo '<option value="mdjm_event_edit"';
				if ( ! empty( $caps->capabilities['mdjm_event_edit'] ) ) {
					echo ' selected="selected"';
				}
					echo '>' . esc_html__( 'Edit All', 'mobile-dj-manager' ) . '</option>' . "\r\n";

					echo '</select>' . "\r\n";

					echo '<br /><br />' . "\r\n";

					echo '<span style="font-size: small; font-weight: bold;">' . esc_html__( 'Templates', 'mobile-dj-manager' ) . ':</span><br />' . "\r\n";
					echo '<select name="template_permissions_' . esc_attr( $role_id ) . '" id="template_permissions_' . esc_attr( $role_id ) . '" style="font-size: small;">' . "\r\n";
					echo '<option value="mdjm_template_none">' . esc_html__( 'None', 'mobile-dj-manager' ) . '</option>' . "\r\n";

					echo '<option value="mdjm_template_edit"';
				if ( ! empty( $caps->capabilities['mdjm_template_edit'] ) ) {
					echo ' selected="selected"';
				}
					echo '>' . esc_html__( 'Edit All', 'mobile-dj-manager' ) . '</option>' . "\r\n";

					echo '</select>' . "\r\n";

				echo '</td>' . "\r\n";

				echo '<td scope="row" style="font-size: small;">';
					echo '<span style="font-size: small; font-weight: bold;">' . esc_html__( 'Txns', 'mobile-dj-manager' ) . ':</span><br />' . "\r\n";
					echo '<select name="txn_permissions_' . esc_attr( $role_id ) . '" id="txn_permissions_' . esc_attr( $role_id ) . '" style="font-size: small;">' . "\r\n";
					echo '<option value="mdjm_txn_none">' . esc_html__( 'None', 'mobile-dj-manager' ) . '</option>' . "\r\n";

					echo '<option value="mdjm_txn_edit"';
				if ( ! empty( $caps->capabilities['mdjm_txn_edit'] ) ) {
					echo ' selected="selected"';
				}
					echo '>' . esc_html__( 'Edit All', 'mobile-dj-manager' ) . '</option>' . "\r\n";

					echo '</select>' . "\r\n";

					echo '<br /><br />' . "\r\n";

					echo '<span style="font-size: small; font-weight: bold;">' . esc_html__( 'Reports', 'mobile-dj-manager' ) . ':</span><br />' . "\r\n";
					echo '<select name="report_permissions_' . esc_attr( $role_id ) . '" id="report_permissions_' . esc_attr( $role_id ) . '" style="font-size: small;">' . "\r\n";
					echo '<option value="mdjm_reports_none">' . esc_html__( 'None', 'mobile-dj-manager' ) . '</option>' . "\r\n";

					echo '<option value="mdjm_reports_run"';
				if ( ! empty( $caps->capabilities['mdjm_reports_run'] ) ) {
					echo ' selected="selected"';
				}
					echo '>' . esc_html__( 'Run', 'mobile-dj-manager' ) . '</option>' . "\r\n";

					echo '</select>' . "\r\n";

				echo '</td>' . "\r\n";

				echo '<td scope="row" style="font-size: small;">';
					echo '<span style="font-size: small; font-weight: bold;">' . esc_html__( 'Quotes', 'mobile-dj-manager' ) . ':</span><br />' . "\r\n";
					echo '<select name="quote_permissions_' . esc_attr( $role_id ) . '" id="quote_permissions_' . esc_attr( $role_id ) . '" style="font-size: small;">' . "\r\n";
					echo '<option value="mdjm_quote_none">' . esc_html__( 'None', 'mobile-dj-manager' ) . '</option>' . "\r\n";

					echo '<option value="mdjm_quote_view_own"';
				if ( ! empty( $caps->capabilities['mdjm_quote_view_own'] ) ) {
					echo ' selected="selected"';
				}
					echo '>' . esc_html__( 'View Own', 'mobile-dj-manager' ) . '</option>' . "\r\n";

					echo '<option value="mdjm_quote_view"';
				if ( ! empty( $caps->capabilities['mdjm_quote_view'] ) ) {
					echo ' selected="selected"';
				}
					echo '>' . esc_html__( 'View All', 'mobile-dj-manager' ) . '</option>' . "\r\n";

					echo '</select>' . "\r\n";
				echo '</td>' . "\r\n";

				echo '<td scope="row" style="font-size: small;">';
					echo '<span style="font-size: small; font-weight: bold;">' . esc_html__( 'Venues', 'mobile-dj-manager' ) . ':</span><br />' . "\r\n";
					echo '<select name="venue_permissions_' . esc_attr( $role_id ) . '" id="venue_permissions_' . esc_attr( $role_id ) . '" style="font-size: small;">' . "\r\n";
					echo '<option value="mdjm_venue_none">' . esc_html__( 'None', 'mobile-dj-manager' ) . '</option>' . "\r\n";

					echo '<option value="mdjm_venue_read"';
				if ( ! empty( $caps->capabilities['mdjm_venue_read'] ) ) {
					echo ' selected="selected"';
				}
					echo '>' . esc_html__( 'View', 'mobile-dj-manager' ) . '</option>' . "\r\n";

					echo '<option value="mdjm_venue_edit"';
				if ( ! empty( $caps->capabilities['mdjm_venue_edit'] ) ) {
					echo ' selected="selected"';
				}
					echo '>' . esc_html__( 'Edit', 'mobile-dj-manager' ) . '</option>' . "\r\n";
					echo '</select>' . "\r\n";
				echo '</td>' . "\r\n";

				echo '</tr>' . "\r\n";

				$i == 0 ? $i++ : $i = 0;
			}
			?>
			</tbody>
			</table>
			<p>
			<?php
			submit_button(
				__( 'Update Permissions', 'mobile-dj-manager' ),
				'primary',
				'set-permissions',
				'',
				false
			);
			?>
</form>
			</p>
</div>
			<?php
		} // permissions_manager

	} // class MDJM_Employee_Manager
endif;
	MDJM_Employee_Manager::init();
