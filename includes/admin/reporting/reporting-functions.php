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
	$event_label = mdjm_get_label_singular();

	$views = array(
		'earnings'   => __( 'Earnings', 'mobile-dj-manager' ),
		'types'      => sprintf( __( 'Earnings by %s Type', 'mobile-dj-manager' ), $event_label ),
		'events'     => mdjm_get_label_plural(),
		'gateways'   => __( 'Payment Methods', 'mobile-dj-manager' )
	);

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
function mdjm_get_reporting_view( $default = 'earnings' ) {

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

	if( ! current_user_can( 'view_event_reports' ) ) {
		wp_die( __( 'You do not have permission to access this report', 'mobile-dj-manager' ), __( 'Error', 'mobile-dj-manager' ), array( 'response' => 403 ) );
	}

	$current_view = 'earnings';
	$views        = mdjm_reports_default_views();

	if ( isset( $_GET['view'] ) && array_key_exists( $_GET['view'], $views ) )
		$current_view = $_GET['view'];

	do_action( 'mdjm_reports_view_' . $current_view );

}
add_action( 'mdjm_reports_tab_reports', 'mdjm_reports_tab_reports' );

/**
 * Renders the Reports Page Views Drop Downs
 *
 * @since 1.3
 * @return void
 */
function mdjm_report_views() {

	if( ! current_user_can( 'view_event_reports' ) ) {
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
 * Renders the Reports Events Table
 *
 * @since	1.4
 * @uses	MDJM_Event_Reports_Table::prepare_items()
 * @uses	MDJM_Event_Reports_Table::display()
 * @return	void
 */
function mdjm_reports_events_table() {

	if( ! current_user_can( 'view_event_reports' ) ) {
		return;
	}

	if( isset( $_GET['event-id'] ) )
		return;

	include( dirname( __FILE__ ) . '/class-event-reports-table.php' );

	$downloads_table = new MDJM_Event_Reports_Table();
	$downloads_table->prepare_items();
	$downloads_table->display();
} // mdjm_reports_events_table
add_action( 'mdjm_reports_view_events', 'mdjm_reports_events_table' );

/**
 * Renders the Reports Earnings Graphs
 *
 * @since	1.4
 * @return	void
 */
function mdjm_reports_earnings() {

	if( ! current_user_can( 'view_event_reports' ) ) {
		return;
	}
	?>
	<div class="tablenav top">
		<div class="alignleft actions"><?php mdjm_report_views(); ?></div>
	</div>
	<?php
	mdjm_reports_graph();
} // mdjm_reports_earnings
add_action( 'mdjm_reports_view_earnings', 'mdjm_reports_earnings' );
