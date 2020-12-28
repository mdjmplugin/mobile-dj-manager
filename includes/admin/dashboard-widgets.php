<?php
/**
 * WordPress Dashboard Widgets
 *
 * @package     MDJM
 * @subpackage  Admin/Widgets
 * @copyright   Copyright (c) 2016, Mike Howard
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.3
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Registers the dashboard widgets.
 *
 * @since   1.3
 * @param
 * @return
 */
function mdjm_add_wp_dashboard_widgets() {

	wp_add_dashboard_widget( 'mdjm-widget-overview', sprintf( __( '%s Overview', 'mobile-dj-manager' ), mdjm_get_option( 'company_name', 'MDJM' ) ), 'mdjm_widget_events_overview' );

} // mdjm_add_wp_dashboard_widgets
add_action( 'wp_dashboard_setup', 'mdjm_add_wp_dashboard_widgets' );

/**
 * Generate and display the content for the Events Overview dashboard widget.
 *
 * @since   1.3
 * @param
 * @return
 */
function mdjm_widget_events_overview() {

	global $current_user;

	if ( mdjm_employee_can( 'manage_mdjm' ) ) {

		$stats = new MDJM_Stats();

		$enquiry_counts    = array(
			'month'     => 0,
			'this_year' => 0,
			'last_year' => 0,
		);
		$conversion_counts = array(
			'month'     => 0,
			'this_year' => 0,
			'last_year' => 0,
		);
		$enquiry_periods   = array(
			'month'     => date( 'Y-m-01' ),
			'this_year' => date( 'Y-01-01' ),
			'last_year' => date( 'Y-01-01', strtotime( '-1 year' ) ),
		);

		foreach ( $enquiry_periods as $period => $date ) {
			$current_count = mdjm_count_events( array(
				'start-date' => $date,
				'end-date'   => $period != 'last_year' ? date( 'Y-m-d' ) : date( 'Y-12-31', strtotime( '-1 year' ) ),
			) );

			foreach ( $current_count as $status => $count ) {
				$enquiry_counts[ $period ] += $count;

				if ( in_array( $status, array( 'mdjm-approved', 'mdjm-contract', 'mdjm-completed', 'mdjm-cancelled' ) ) ) {
					$conversion_counts[ $period ] += $count;
				}
			}
		}

		$completed_counts = array(
			'month'     => 0,
			'this_year' => 0,
			'last_year' => 0,
		);
		$event_periods    = array(
			'month'     => array( date( 'Y-m-01' ), date( 'Y-m-d' ) ),
			'this_year' => array( date( 'Y-01-01' ), date( 'Y-m-d' ) ),
			'last_year' => array( date( 'Y-m-01', strtotime( '-1 year' ) ), date( 'Y-12-31', strtotime( '-1 year' ) ) ),
		);

		foreach ( $event_periods as $period => $date ) {
			$current_count = mdjm_count_events( array(
                'date'   => $date,
                'status' => 'mdjm-completed',
			) );

			foreach ( $current_count as $status => $count ) {
				$completed_counts[ $period ] += $count;
			}
		}

		$income_month  = $stats->get_income_by_date( null, date( 'n' ), date( 'Y' ) );
		$income_year   = $stats->get_income_by_date( null, '', date( 'Y' ) );
		$income_last   = $stats->get_income_by_date( null, '', date( 'Y' ) - 1 );
		$expense_month = $stats->get_expenses_by_date( null, date( 'n' ), date( 'Y' ) );
		$expense_year  = $stats->get_expenses_by_date( null, '', date( 'Y' ) );
		$expense_last  = $stats->get_expenses_by_date( null, '', date( 'Y' ) - 1 );

		$earnings_month = $income_month - $expense_month;
		$earnings_year  = $income_year - $expense_year;
		$earnings_last  = $income_last - $expense_last;

		?>
		<div class="mdjm_stat_grid">
        	<?php do_action( 'mdjm_before_events_overview' ); ?>
			<table>
				<thead>
					<tr>
						<th>&nbsp;</th>
						<th><?php esc_html_e( 'MTD', 'mobile-dj-manager' ); ?></th>
						<th><?php esc_html_e( 'YTD', 'mobile-dj-manager' ); ?></th>
						<th><?php echo esc_html( date( 'Y', strtotime( '-1 year' ) ) ); ?></th>
					</tr>
				</thead>
				<tbody>
					<tr>
						<th><?php printf( esc_html__( '%s Received', 'mobile-dj-manager' ), esc_html( get_post_status_object( 'mdjm-enquiry' )->plural ) ); ?></th>
						<td><?php echo esc_html( $enquiry_counts['month'] ); ?></td>
						<td><?php echo esc_html( $enquiry_counts['this_year'] ); ?></td>
						<td><?php echo esc_html( $enquiry_counts['last_year'] ); ?></td>
					</tr>
					<tr>
						<th><?php printf( esc_html__( '%s Converted', 'mobile-dj-manager' ), esc_html( get_post_status_object( 'mdjm-enquiry' )->plural ) ); ?></th>
						<td><?php echo esc_html( $conversion_counts['month'] ); ?></td>
						<td><?php echo esc_html( $conversion_counts['this_year'] ); ?></td>
						<td><?php echo esc_html( $conversion_counts['last_year'] ); ?></td>
					</tr>
					<tr>
						<th><?php printf( esc_html__( '%s Completed', 'mobile-dj-manager' ), esc_html( mdjm_get_label_plural() ) ); ?></th>
						<td><?php echo esc_html( $completed_counts['month'] ); ?></td>
						<td><?php echo esc_html( $completed_counts['this_year'] ); ?></td>
						<td><?php echo esc_html( $completed_counts['last_year'] ); ?></td>
					</tr>
					<tr>
						<th><?php esc_html_e( 'Income', 'mobile-dj-manager' ); ?></th>
						<td><?php echo esc_html( mdjm_currency_filter( mdjm_format_amount( $income_month ) ) ); ?></td>
						<td><?php echo esc_html( mdjm_currency_filter( mdjm_format_amount( $income_year ) ) ); ?></td>
						<td><?php echo esc_html( mdjm_currency_filter( mdjm_format_amount( $income_last ) ) ); ?></td>
					</tr>
					<tr>
						<th><?php esc_html_e( 'Outgoings', 'mobile-dj-manager' ); ?></th>
						<td><?php echo esc_html( mdjm_currency_filter( mdjm_format_amount( $expense_month ) ) ); ?></td>
						<td><?php echo esc_html( mdjm_currency_filter( mdjm_format_amount( $expense_year ) ) ); ?></td>
						<td><?php echo esc_html( mdjm_currency_filter( mdjm_format_amount( $expense_last ) ) ); ?></td>
					</tr>
					<tr>
						<th><?php esc_html_e( 'Earnings', 'mobile-dj-manager' ); ?></th>
						<td><?php echo esc_html( mdjm_currency_filter( mdjm_format_amount( $earnings_month ) ) ); ?></td>
						<td><?php echo esc_html( mdjm_currency_filter( mdjm_format_amount( $earnings_year ) ) ); ?></td>
						<td><?php echo esc_html( mdjm_currency_filter( mdjm_format_amount( $earnings_last ) ) ); ?></td>
					</tr>
				</tbody>
			</table>

			<p>
				<?php 
                printf(
					__( '<a href="%1$s">Create %2$s</a>', 'mobile-dj-manager' ), // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
					admin_url( 'post-new.php?post_type=mdjm-event' ), // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
                mdjm_get_label_singular() ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				?>
				&nbsp;&nbsp;&nbsp;|&nbsp;&nbsp;&nbsp;
				<?php 
                printf(
					__( '<a href="%1$s">Manage %2$s</a>', 'mobile-dj-manager' ), // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
					admin_url( 'edit.php?post_type=mdjm-event' ), // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
                mdjm_get_label_plural() ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				?>
				&nbsp;&nbsp;&nbsp;|&nbsp;&nbsp;&nbsp;
				<?php 
                printf(
					__( '<a href="%s">Transactions</a>', 'mobile-dj-manager' ), // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
                admin_url( 'edit.php?post_type=mdjm-transaction' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				?>
				&nbsp;&nbsp;&nbsp;|&nbsp;&nbsp;&nbsp;
				<?php 
                printf(
					__( '<a href="%s">Settings</a>', 'mobile-dj-manager' ), // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
                admin_url( 'admin.php?page=mdjm-settings' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				?>
            </p>

			<?php $sources = $stats->get_enquiry_sources_by_date( 'this_month' ); ?>

			<?php if ( ! empty( $sources ) ) : ?>

				<?php foreach ( $sources as $count => $source ) : ?>
					<p>
					 <?php printf( __( '<p>Most enquiries have been received via <strong>%1$s (%2$d)</strong> so far this month.', 'mobile-dj-manager' ), esc_html( $source ), (int) $count ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
					</p>
				<?php endforeach; ?>

			<?php else : ?>
				<p><?php esc_html_e( 'No enquiries yet this month.', 'mobile-dj-manager' ); ?></p>
			<?php endif; ?>

			<?php do_action( 'mdjm_after_events_overview' ); ?>

		</div>

		<?php
	}

} // mdjm_widget_events_overview

/**
 * Add event count to At a glance widget
 *
 * @since   1.4
 * @return  void
 */
function mdjm_dashboard_at_a_glance_widget( $items ) {
	$num_posts = mdjm_count_events();
	$count     = 0;
	$statuses  = mdjm_all_event_status();

	foreach ( $statuses as $status => $label ) {
		if ( ! empty( $num_posts->$status ) ) {
			$count += $num_posts->$status;
		}
	}

	if ( $num_posts && $count > 0 ) {
		$text = _n( '%s ' . mdjm_get_label_singular(), '%s ' . mdjm_get_label_plural(), $count, 'mobile-dj-manager' );

		$text = sprintf( $text, number_format_i18n( $count ) );

		if ( mdjm_employee_can( 'read_events' ) ) {
			$text = sprintf( '<a class="event-count" href="edit.php?post_type=mdjm-event">%1$s</a>', $text );
		} else {
			$text = sprintf( '<span class="event-count">%1$s</span>', $text );
		}

		$items[] = $text;
	}

	return $items;
} // mdjm_dashboard_at_a_glance_widget
add_filter( 'dashboard_glance_items', 'mdjm_dashboard_at_a_glance_widget', 1 );
