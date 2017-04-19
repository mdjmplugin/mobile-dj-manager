<?php
/**
 * Admin Reports Page
 *
 * @package     MDJM
 * @subpackage  Admin/Reports
 * @copyright   Copyright (c) 2016, Mike Howard
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.4
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) )
	exit;

/**
 * Reports Page
 *
 * Renders the reports page contents.
 *
 * @since	1.4
 * @return	void
*/
function mdjm_reports_page() {
	$current_page = admin_url( 'edit.php?post_type=mdjm-event&page=mdjm-reports' );
	$active_tab = isset( $_GET['tab'] ) ? $_GET['tab'] : 'reports';
	?>
	<div class="wrap">
		<h1 class="nav-tab-wrapper">
			<a href="<?php echo add_query_arg( array( 'tab' => 'reports', 'settings-updated' => false ), $current_page ); ?>" class="nav-tab <?php echo $active_tab == 'reports' ? 'nav-tab-active' : ''; ?>"><?php _e( 'Reports', 'mobile-dj-manager' ); ?></a>
            <a href="<?php echo add_query_arg( array( 'tab' => 'export', 'settings-updated' => false ), $current_page ); ?>" class="nav-tab <?php echo $active_tab == 'export' ? 'nav-tab-active' : ''; ?>"><?php _e( 'Export', 'mobile-dj-manager' ); ?></a>
			<?php do_action( 'mdjm_reports_tabs' ); ?>
		</h1>

		<?php
		do_action( 'mdjm_reports_page_top' );
		do_action( 'mdjm_reports_tab_' . $active_tab );
		do_action( 'mdjm_reports_page_bottom' );
		?>
	</div><!-- .wrap -->
	<?php
} // mdjm_reports_page

/**
 * Default Report Views
 *
 * @since	1.4
 * @return	arr		$views	Report Views
 */
function mdjm_reports_default_views() {
	$event_label_single = mdjm_get_label_singular();
	$event_label_plural = mdjm_get_label_plural();

	$views = array(
		'earnings'     => __( 'Earnings', 'mobile-dj-manager' ),
		'transactions' => __( 'Transactions', 'mobile-dj-manager' ),
		'txn-types'    => __( 'Transactions by Type', 'mobile-dj-manager' ),
		'conversions'  => __( 'Enquiries by Source', 'mobile-dj-manager' ),
		'employees'    => sprintf( __( '%s by Employee', 'mobile-dj-manager' ), $event_label_plural ),
		'types'        => sprintf( __( '%s by Type', 'mobile-dj-manager' ), $event_label_plural ),
		'packages'     => sprintf( __( '%s by Package', 'mobile-dj-manager' ), $event_label_plural ),
		'addons'       => sprintf( __( '%s by Addon', 'mobile-dj-manager' ), $event_label_plural )
	);

	if ( ! mdjm_is_employer() )	{
		unset( $views['employees'] );
	}

	if ( ! mdjm_packages_enabled() )	{
		unset( $views['packages'] );
		unset( $views['addons'] );
	}

	$views = apply_filters( 'mdjm_report_views', $views );

	return $views;
} // mdjm_reports_default_views

/**
 * Default Report Views
 *
 * Checks the $_GET['view'] parameter to ensure it exists within the default allowed views.
 *
 * @param string $default Default view to use.
 *
 * @since	1.4
 * @return	str		$view	Report View
 *
 */
function mdjm_get_reporting_view( $default = 'events' ) {

	if ( ! isset( $_GET['view'] ) || ! in_array( $_GET['view'], array_keys( mdjm_reports_default_views() ) ) ) {
		$view = $default;
	} else {
		$view = $_GET['view'];
	}

	return apply_filters( 'mdjm_get_reporting_view', $view );
} // mdjm_get_reporting_view

/**
 * Renders the Reports page
 *
 * @since	1.4
 * @return	void
 */
function mdjm_reports_tab_reports() {

	if( ! mdjm_employee_can( 'run_reports' ) ) {
		wp_die( __( 'You do not have permission to access this report', 'mobile-dj-manager' ), __( 'Error', 'mobile-dj-manager' ), array( 'response' => 403 ) );
	}

	$current_view = 'earnings';
	$views        = mdjm_reports_default_views();

	if ( isset( $_GET['view'] ) && array_key_exists( $_GET['view'], $views ) )
		$current_view = $_GET['view'];

	do_action( 'mdjm_reports_view_' . $current_view );

} // mdjm_reports_tab_reports
add_action( 'mdjm_reports_tab_reports', 'mdjm_reports_tab_reports' );

/**
 * Renders the Reports Page Views Drop Downs
 *
 * @since 1.3
 * @return void
 */
function mdjm_report_views() {

	if( ! mdjm_employee_can( 'run_reports' ) ) {
		return;
	}

	$views        = mdjm_reports_default_views();
	$current_view = isset( $_GET['view'] ) ? $_GET['view'] : 'earnings';
	?>
	<form id="mdjm-reports-filter" method="get">
		<select id="mdjm-reports-view" name="view">
			<option value="-1"><?php _e( 'Report Type', 'mobile-dj-manager' ); ?></option>
			<?php foreach ( $views as $view_id => $label ) : ?>
				<option value="<?php echo esc_attr( $view_id ); ?>" <?php selected( $view_id, $current_view ); ?>><?php echo $label; ?></option>
			<?php endforeach; ?>
		</select>

		<?php do_action( 'mdjm_report_view_actions' ); ?>

		<input type="hidden" name="post_type" value="mdjm-event"/>
		<input type="hidden" name="page" value="mdjm-reports"/>
		<?php submit_button( __( 'Show', 'mobile-dj-manager' ), 'secondary', 'submit', false ); ?>
	</form>
	<?php
	do_action( 'mdjm_report_view_actions_after' );
} // mdjm_report_views

/**
 * Renders the Reports Earnings Graphs
 *
 * @since	1.4
 * @return	void
 */
function mdjm_reports_earnings() {

	if( ! mdjm_employee_can( 'run_reports' ) ) {
		return;
	}
	?>
	<div class="tablenav top">
		<div class="alignleft actions"><?php mdjm_report_views(); ?></div>
	</div>
	<?php
	mdjm_earnings_reports_graph();
} // mdjm_reports_earnings
add_action( 'mdjm_reports_view_earnings', 'mdjm_reports_earnings' );

/**
 * Renders the Reports Transactions Graphs
 *
 * @since	1.4
 * @return	void
 */
function mdjm_reports_transactions() {

	if( ! mdjm_employee_can( 'run_reports' ) ) {
		return;
	}
	?>
	<div class="tablenav top">
		<div class="alignleft actions"><?php mdjm_report_views(); ?></div>
	</div>
	<?php
	mdjm_transactions_reports_graph();
} // mdjm_reports_transactions
add_action( 'mdjm_reports_view_transactions', 'mdjm_reports_transactions' );

/**
 * Renders the Reports Event Employees Table
 *
 * @since	1.4
 * @uses	MDJM_Employees_Reports_Table::prepare_items()
 * @uses	MDJM_Employees_Reports_Table::display()
 * @return	void
 */
function mdjm_reports_employees_table() {

	if( ! mdjm_employee_can( 'run_reports' ) ) {
		return;
	}

	include( dirname( __FILE__ ) . '/class-mdjm-employees-reports-table.php' );

	?>

	<div class="inside">
		<?php

        $employees_table = new MDJM_Employees_Reports_Table();
        $employees_table->prepare_items();
        $employees_table->display();
        ?>

        <?php echo $employees_table->load_scripts(); ?>

        <div class="mdjm-mix-totals">
            <div class="mdjm-mix-chart">
                <strong><?php printf( __( 'Employee %s Mix: ', 'mobile-dj-manager' ), mdjm_get_label_plural() ); ?></strong>
                <?php $employees_table->output_employee_graph(); ?>
            </div>
            <div class="mdjm-mix-chart">
                <strong><?php _e( 'Employee Wages Mix: ', 'mobile-dj-manager' ); ?></strong>
                <?php $employees_table->output_wages_graph(); ?>
            </div>
        </div>

        <?php do_action( 'mdjm_reports_employees_graph_additional_stats' ); ?>

		<p class="mdjm-graph-notes">
            <span>
                <em><sup>&dagger;</sup> <?php printf( __( 'All employee %s are included whether they are the primary employee or not.', 'mobile-dj-manager' ), mdjm_get_label_plural( true ) ); ?></em>
            </span>
            <span>
                <em><?php printf( __( 'Stats include all %s that take place within the date period selected.', 'mobile-dj-manager' ), mdjm_get_label_plural( true ) ); ?></em>
            </span>
        </p>

    </div>
	<?php
} // mdjm_reports_employees_table
add_action( 'mdjm_reports_view_employees', 'mdjm_reports_employees_table' );

/**
 * Renders the Reports Event Types Table
 *
 * @since	1.4
 * @uses	MDJM_Types_Reports_Table::prepare_items()
 * @uses	MDJM_Types_Reports_Table::display()
 * @return	void
 */
function mdjm_reports_types_table() {

	if( ! mdjm_employee_can( 'run_reports' ) ) {
		return;
	}

	include( dirname( __FILE__ ) . '/class-mdjm-types-reports-table.php' );

	?>

	<div class="inside">
		<?php

        $types_table = new MDJM_Types_Reports_Table();
        $types_table->prepare_items();
        $types_table->display();
        ?>

        <?php echo $types_table->load_scripts(); ?>

        <div class="mdjm-mix-totals">
            <div class="mdjm-mix-chart">
                <strong><?php printf( __( '%s Types Mix: ', 'mobile-dj-manager' ), mdjm_get_label_singular() ); ?></strong>
                <?php $types_table->output_source_graph(); ?>
            </div>
            <div class="mdjm-mix-chart">
                <strong><?php _e( 'Type Earnings Mix: ', 'mobile-dj-manager' ); ?></strong>
                <?php $types_table->output_earnings_graph(); ?>
            </div>
        </div>

        <?php do_action( 'mdjm_reports_types_graph_additional_stats' ); ?>

		<p class="mdjm-graph-notes">
            <span>
                <em><sup>&dagger;</sup> <?php printf( __( 'Stats include all %s taking place within the date period selected.', 'mobile-dj-manager' ), mdjm_get_label_plural( true ) ); ?></em>
            </span>
        </p>

    </div>
	<?php
} // mdjm_reports_types_table
add_action( 'mdjm_reports_view_types', 'mdjm_reports_types_table' );

/**
 * Renders the Reports Event Conversions Table
 *
 * @since	1.4
 * @uses	MDJM_Conversions_Reports_Table::prepare_items()
 * @uses	MDJM_Conversions_Reports_Table::display()
 * @return	void
 */
function mdjm_reports_conversions_table() {

	if( ! mdjm_employee_can( 'run_reports' ) ) {
		return;
	}

	include( dirname( __FILE__ ) . '/class-mdjm-conversions-reports-table.php' );

	?>

	<div class="inside">
		<?php

        $conversions_table = new MDJM_Conversions_Reports_Table();
        $conversions_table->prepare_items();
        $conversions_table->display();
        ?>

        <?php echo $conversions_table->load_scripts(); ?>

        <div class="mdjm-mix-totals">
            <div class="mdjm-mix-chart">
                <strong><?php _e( 'Enquiry Source Mix: ', 'mobile-dj-manager' ); ?></strong>
                <?php $conversions_table->output_source_graph(); ?>
            </div>
            <div class="mdjm-mix-chart">
                <strong><?php _e( 'Source Values Mix: ', 'mobile-dj-manager' ); ?></strong>
                <?php $conversions_table->output_earnings_graph(); ?>
            </div>
        </div>

        <?php do_action( 'mdjm_reports_conversions_graph_additional_stats' ); ?>

		<p class="mdjm-graph-notes">
            <span>
                <em><sup>&dagger;</sup> <?php printf( __( 'Stats include all enquiries that were received within the date period selected.', 'mobile-dj-manager' ), mdjm_get_label_plural( true ) ); ?></em>
            </span>
        </p>

    </div>
	<?php
} // mdjm_reports_conversions_table
add_action( 'mdjm_reports_view_conversions', 'mdjm_reports_conversions_table' );

/**
 * Renders the Reports Event Packages Table
 *
 * @since	1.4
 * @uses	MDJM_Conversions_Reports_Table::prepare_items()
 * @uses	MDJM_Conversions_Reports_Table::display()
 * @return	void
 */
function mdjm_reports_packages_table() {

	if( ! mdjm_employee_can( 'run_reports' ) ) {
		return;
	}

	include( dirname( __FILE__ ) . '/class-mdjm-packages-reports-table.php' );

	?>

	<div class="inside">
		<?php

        $packages_table = new MDJM_Packages_Reports_Table();
        $packages_table->prepare_items();
        $packages_table->display();
        ?>

        <?php echo $packages_table->load_scripts(); ?>

        <div class="mdjm-mix-totals">
            <div class="mdjm-mix-chart">
                <strong><?php printf( __( '%s Package Mix: ', 'mobile-dj-manager' ), mdjm_get_label_singular() ); ?></strong>
                <?php $packages_table->output_source_graph(); ?>
            </div>
            <div class="mdjm-mix-chart">
                <strong><?php _e( 'Package Earnings Mix: ', 'mobile-dj-manager' ); ?></strong>
                <?php $packages_table->output_earnings_graph(); ?>
            </div>
        </div>

        <?php do_action( 'mdjm_reports_packages_graph_additional_stats' ); ?>

		<p class="mdjm-graph-notes">
            <span>
                <em><sup>&dagger;</sup> <?php printf( __( 'Stats include all %s with a date within the period selected regardless of their status.', 'mobile-dj-manager' ), mdjm_get_label_plural( true ) ); ?></em>
            </span>
        </p>

    </div>
	<?php
} // mdjm_reports_packages_table
add_action( 'mdjm_reports_view_packages', 'mdjm_reports_packages_table' );

/**
 * Renders the Reports Event Packages Table
 *
 * @since	1.4
 * @uses	MDJM_Conversions_Reports_Table::prepare_items()
 * @uses	MDJM_Conversions_Reports_Table::display()
 * @return	void
 */
function mdjm_reports_addons_table() {

	if( ! mdjm_employee_can( 'run_reports' ) ) {
		return;
	}

	include( dirname( __FILE__ ) . '/class-mdjm-addons-reports-table.php' );

	?>

	<div class="inside">
		<?php

        $addons_table = new MDJM_Addons_Reports_Table();
        $addons_table->prepare_items();
        $addons_table->display();
        ?>

        <?php echo $addons_table->load_scripts(); ?>

        <div class="mdjm-mix-totals">
            <div class="mdjm-mix-chart">
                <strong><?php printf( __( '%s Addons Mix: ', 'mobile-dj-manager' ), mdjm_get_label_Plural() ); ?></strong>
                <?php $addons_table->output_source_graph(); ?>
            </div>
            <div class="mdjm-mix-chart">
                <strong><?php _e( 'Addon Earnings Mix: ', 'mobile-dj-manager' ); ?></strong>
                <?php $addons_table->output_earnings_graph(); ?>
            </div>
        </div>

        <?php do_action( 'mdjm_reports_addons_graph_additional_stats' ); ?>

		<p class="mdjm-graph-notes">
            <span>
                <em><sup>&dagger;</sup> <?php printf( __( 'Stats include all %s with a date within the period selected regardless of their status.', 'mobile-dj-manager' ), mdjm_get_label_plural( true ) ); ?></em>
            </span>
        </p>

    </div>
	<?php
} // mdjm_reports_addons_table
add_action( 'mdjm_reports_view_addons', 'mdjm_reports_addons_table' );

/**
 * Renders the Reports Transactions Graphs
 *
 * @since	1.4
 * @return	void
 */
function mdjm_reports_txn_types_table() {

	if( ! mdjm_employee_can( 'run_reports' ) ) {
		return;
	}

	include( dirname( __FILE__ ) . '/class-mdjm-transaction-types-reports-table.php' );

	?>

	<div class="inside">
		<?php

        $txn_types_table = new MDJM_Transaction_Types_Reports_Table();
        $txn_types_table->prepare_items();
        $txn_types_table->display();
        ?>

        <?php echo $txn_types_table->load_scripts(); ?>

        <div class="mdjm-mix-totals">
            <div class="mdjm-mix-chart">
                <strong><?php _e( 'Transaction Types Mix: ', 'mobile-dj-manager' ); ?></strong>
                <?php $txn_types_table->output_types_graph(); ?>
            </div>
            <div class="mdjm-mix-chart">
                <strong><?php _e( 'Transaction Values Mix: ', 'mobile-dj-manager' ); ?></strong>
                <?php $txn_types_table->output_values_graph(); ?>
            </div>
        </div>

        <?php do_action( 'mdjm_reports_txn_types_additional_stats' ); ?>

    </div>
	<?php
} // mdjm_reports_txn_types_table
add_action( 'mdjm_reports_view_txn-types', 'mdjm_reports_txn_types_table' );

/**
 * Renders the 'Export' tab on the Reports Page
 *
 * @since	1.4
 * @return	void
 */
function mdjm_reports_tab_export()	{

	if( ! mdjm_employee_can( 'run_reports' ) ) {
		wp_die( __( 'You do not have permission to export reports', 'mobile-dj-manager' ), __( 'Error', 'mobile-dj-manager' ), array( 'response' => 403 ) );
	}

	$label_single = mdjm_get_label_singular();
	$label_plural = mdjm_get_label_plural();

	?>
	<div id="mdjm-dashboard-widgets-wrap">
		<div class="metabox-holder">
			<div id="post-body">
				<div id="post-body-content">

					<?php do_action( 'mdjm_reports_tab_export_content_top' ); ?>

					<div class="postbox mdjm-export-events-earnings">
						<h3><span><?php _e( 'Export Transaction History', 'mobile-dj-manager' ); ?></span></h3>
						<div class="inside">
							<p><?php _e( 'Download a CSV of all transactions recorded.', 'mobile-dj-manager' ); ?></p>
							<form id="mdjm-export-txns" class="mdjm-export-form mdjm-import-export-form" method="post">
								<?php mdjm_insert_datepicker( array(
									'id'       => 'mdjm-txn-export-start',
									'altfield' => 'txn_start'
								) ); ?>
                                <?php echo MDJM()->html->date_field( array(
									'id'          => 'mdjm-txn-export-start',
									'name'        => 'display_start_date',
									'placeholder' => __( 'Select Start Date', 'mobile-dj-manager' )
								) ); ?>
								<?php echo MDJM()->html->hidden( array( 
									'name' => 'txn_start'
								) ); ?>
                                <?php mdjm_insert_datepicker( array(
									'id'       => 'mdjm-txn-export-end',
									'altfield' => 'txn_end'
								) ); ?>
                                <?php echo MDJM()->html->date_field( array(
									'id'          => 'mdjm-txn-export-end',
									'name'        => 'display_end_date',
									'placeholder' => __( 'Select End Date', 'mobile-dj-manager' )
								) ); ?>
								<?php echo MDJM()->html->hidden( array( 
									'name' => 'txn_end'
								) ); ?>
								<select name="txn_status">
									<option value=""><?php _e( 'All Statuses', 'mobile-dj-manager' ); ?></option>
                                    <option value="Completed"><?php _e( 'Completed', 'mobile-dj-manager' ); ?></option>
                                    <option value="Pending"><?php _e( 'Pending', 'mobile-dj-manager' ); ?></option>
                                    <option value="Cancelled"><?php _e( 'Cancelled', 'mobile-dj-manager' ); ?></option>
                                    <option value="Failed"><?php _e( 'Failed', 'mobile-dj-manager' ); ?></option>
								</select>
								<?php wp_nonce_field( 'mdjm_ajax_export', 'mdjm_ajax_export' ); ?>
								<input type="hidden" name="mdjm-export-class" value="MDJM_Batch_Export_Txns"/>
								<span>
									<input type="submit" value="<?php _e( 'Generate CSV', 'mobile-dj-manager' ); ?>" class="button-secondary"/>
									<span class="spinner"></span>
								</span>
							</form>
						</div><!-- .inside -->
					</div><!-- .postbox -->

					<div class="postbox mdjm-export-events">
						<h3><span><?php printf( __( 'Export %s', 'mobile-dj-manager' ), $label_plural ); ?></span></h3>
						<div class="inside">
							<p><?php printf( __( 'Download a CSV of %s data.', 'mobile-dj-manager' ), $label_plural ); ?></p>
							<form id="mdjm-export-events" class="mdjm-export-form mdjm-import-export-form" method="post">
								<?php mdjm_insert_datepicker( array(
									'id'       => 'mdjm-event-export-start',
									'altfield' => 'event_start'
								) ); ?>
                                <?php echo MDJM()->html->date_field( array(
									'id'          => 'mdjm-event-export-start',
									'name'        => 'display_start_date',
									'placeholder' => __( 'Select Start Date', 'mobile-dj-manager' )
								) ); ?>
								<?php echo MDJM()->html->hidden( array( 
									'name' => 'event_start'
								) ); ?>
                                <?php mdjm_insert_datepicker( array(
									'id'       => 'mdjm-event-export-end',
									'altfield' => 'event_end'
								) ); ?>
                                <?php echo MDJM()->html->date_field( array(
									'id'          => 'mdjm-event-export-end',
									'name'        => 'display_end_date',
									'placeholder' => __( 'Select End Date', 'mobile-dj-manager' )
								) ); ?>
								<?php echo MDJM()->html->hidden( array( 
									'name' => 'event_end'
								) ); ?>
								<select name="event_status">
									<option value="any"><?php _e( 'All Statuses', 'mobile-dj-manager' ); ?></option>
                                    <?php foreach( mdjm_all_event_status() as $status => $label ) : ?>
                                    	<option value="<?php echo $status; ?>"><?php echo $label; ?></option>
                                    <?php endforeach; ?>
								</select>
								<?php wp_nonce_field( 'mdjm_ajax_export', 'mdjm_ajax_export' ); ?>
								<input type="hidden" name="mdjm-export-class" value="MDJM_Batch_Export_Events"/>
								<span>
									<input type="submit" value="<?php _e( 'Generate CSV', 'mobile-dj-manager' ); ?>" class="button-secondary"/>
									<span class="spinner"></span>
								</span>
							</form>
						</div><!-- .inside -->
					</div><!-- .postbox -->

					<div class="postbox mdjm-export-clients">
						<h3><span><?php _e( 'Export Clients','mobile-dj-manager' ); ?></span></h3>
						<div class="inside">
							<p><?php _e( 'Download a CSV of clients.', 'mobile-dj-manager' ); ?></p>
							<form id="mdjm-export-clients" class="mdjm-export-form mdjm-import-export-form" method="post">
								<?php wp_nonce_field( 'mdjm_ajax_export', 'mdjm_ajax_export' ); ?>
								<input type="hidden" name="mdjm-export-class" value="MDJM_Batch_Export_Clients"/>
								<input type="submit" value="<?php _e( 'Generate CSV', 'mobile-dj-manager' ); ?>" class="button-secondary"/>
							</form>
						</div><!-- .inside -->
					</div><!-- .postbox -->

					<?php if ( mdjm_is_employer() ) : ?>
                        <div class="postbox mdjm-export-employees">
                            <h3><span><?php _e( 'Export Employees','mobile-dj-manager' ); ?></span></h3>
                            <div class="inside">
                                <p><?php _e( 'Download a CSV of employees.', 'mobile-dj-manager' ); ?></p>
                                <form id="mdjm-export-employees" class="mdjm-export-form mdjm-import-export-form" method="post">
                                    <?php wp_nonce_field( 'mdjm_ajax_export', 'mdjm_ajax_export' ); ?>
                                    <input type="hidden" name="mdjm-export-class" value="MDJM_Batch_Export_Employees"/>
                                    <input type="submit" value="<?php _e( 'Generate CSV', 'mobile-dj-manager' ); ?>" class="button-secondary"/>
                                </form>
                            </div><!-- .inside -->
                        </div><!-- .postbox -->
                    <?php endif; ?>

				</div><!-- .post-body-content -->
			</div><!-- .post-body -->
		</div><!-- .metabox-holder -->
	</div><!-- #mdjm-dashboard-widgets-wrap -->

	<?php
} // mdjm_reports_tab_export
add_action( 'mdjm_reports_tab_export', 'mdjm_reports_tab_export' );
