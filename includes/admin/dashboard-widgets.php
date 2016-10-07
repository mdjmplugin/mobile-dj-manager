<?php
/**
 * WordPress Dashboard Widgets
 *
 * @package     MDJM
 * @subpackage	Admin/Widgets
 * @copyright   Copyright (c) 2016, Mike Howard
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.3
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) )
	exit;

/**
 * Registers the dashboard widgets.
 *
 * @since	1.3
 * @param
 * @return
 */
function mdjm_add_wp_dashboard_widgets() {
	
	wp_add_dashboard_widget( 'mdjm-widget-overview', sprintf( __( '%s Overview', 'mobile-dj-manager' ), mdjm_get_option( 'company_name', 'MDJM' ) ), 'mdjm_widget_events_overview' );

	wp_add_dashboard_widget( 'mdjm-availability-overview', 'MDJM Availability', 'f_mdjm_dash_availability' );

} // mdjm_add_wp_dashboard_widgets
add_action( 'wp_dashboard_setup', 'mdjm_add_wp_dashboard_widgets' );

/**
 * Generate and display the content for the Events Overview dashboard widget.
 *
 * @since	1.3
 * @param
 * @return
 */
function mdjm_widget_events_overview() {
	
	global $current_user;
	
	if ( mdjm_employee_can( 'manage_mdjm' ) )	{
	
		$stats = new MDJM_Stats();

		$enquiry_counts    = array( 'month' => 0, 'this_year' => 0, 'last_year' => 0 );
		$conversion_counts = array( 'month' => 0, 'this_year' => 0, 'last_year' => 0 );
		$enquiry_periods   = array(
			'month'     => date( 'Y-m-01' ),
			'this_year' => date( 'Y-01-01' ),
			'last_year' => date( 'Y-01-01', strtotime( '-1 year' ) )
		);

		foreach ( $enquiry_periods as $period => $date )	{
			$current_count = mdjm_count_events( array(
				'start-date' => $date,
				'end-date'   => $period != 'last_year' ? date( 'Y-m-d' ) : date( 'Y-12-31', strtotime( '-1 year' ) )
			) );

			foreach ( $current_count as $status => $count )	{
				$enquiry_counts[ $period ] += $count;

				if ( in_array( $status, array( 'mdjm-approved', 'mdjm-contract', 'mdjm-completed', 'mdjm-cancelled' ) ) )	{
					$conversion_counts[ $period ] += $count;
				}
			}
		}

		$completed_counts = array( 'month' => 0, 'this_year' => 0, 'last_year' => 0 );
		$event_periods = array(
			'month'     => array( date( 'Y-m-01' ), date( 'Y-m-d' ) ),
			'this_year' => array( date( 'Y-01-01' ), date( 'Y-m-d' ) ),
			'last_year' => array( date( 'Y-m-01', strtotime( '-1 year' ) ), date( 'Y-12-31', strtotime( '-1 year' ) ) )
		);

		foreach ( $event_periods as $period => $date )	{
			$current_count = mdjm_count_events( array( 'date' => $date, 'status' => 'mdjm-completed' ) );

			foreach ( $current_count as $status => $count )	{
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
						<th><?php _e( 'MTD', 'mobile-dj-manager' ); ?></th>
						<th><?php _e( 'YTD', 'mobile-dj-manager' ); ?></th>
						<th><?php echo date( 'Y', strtotime( '-1 year' ) ); ?></th>
					</tr>
				</thead>
				<tbody>
					<tr>
						<th><?php printf( __( '%s Received', 'mobile-dj-manager' ), get_post_status_object( 'mdjm-enquiry' )->plural ); ?></th>
						<td><?php echo $enquiry_counts['month']; ?></td>
						<td><?php echo $enquiry_counts['this_year']; ?></td>
						<td><?php echo $enquiry_counts['last_year']; ?></td>
					</tr>
					<tr>
						<th><?php printf( __( '%s Converted', 'mobile-dj-manager' ), get_post_status_object( 'mdjm-enquiry' )->plural ); ?></th>
						<td><?php echo $conversion_counts['month']; ?></td>
						<td><?php echo $conversion_counts['this_year']; ?></td>
						<td><?php echo $conversion_counts['last_year']; ?></td>
					</tr>
					<tr>
						<th><?php printf( __( '%s Completed', 'mobile-dj-manager' ), mdjm_get_label_plural() ); ?></th>
						<td><?php echo $completed_counts['month']; ?></td>
						<td><?php echo $completed_counts['this_year']; ?></td>
						<td><?php echo $completed_counts['last_year'];?></td>
					</tr>
					<tr>
						<th><?php _e( 'Income', 'mobile-dj-manager' ); ?></th>
						<td><?php echo mdjm_currency_filter( mdjm_format_amount( $income_month ) ); ?></td>
						<td><?php echo mdjm_currency_filter( mdjm_format_amount( $income_year ) ); ?></td>
						<td><?php echo mdjm_currency_filter( mdjm_format_amount( $income_last ) ); ?></td>
					</tr>
					<tr>
						<th><?php _e( 'Outgoings', 'mobile-dj-manager' ); ?></th>
						<td><?php echo mdjm_currency_filter( mdjm_format_amount( $expense_month ) ); ?></td>
						<td><?php echo mdjm_currency_filter( mdjm_format_amount( $expense_year ) ); ?></td>
						<td><?php echo mdjm_currency_filter( mdjm_format_amount( $expense_last ) ); ?></td>
					</tr>
					<tr>
						<th><?php _e( 'Earnings', 'mobile-dj-manager' ); ?></th>
						<td><?php echo mdjm_currency_filter( mdjm_format_amount( $earnings_month ) ); ?></td>
						<td><?php echo mdjm_currency_filter( mdjm_format_amount( $earnings_year ) ); ?></td>
						<td><?php echo mdjm_currency_filter( mdjm_format_amount( $earnings_last ) ); ?></td>
					</tr>
				</tbody>
			</table>
			
			<p>
				<?php printf(
					__( '<a href="%s">Create %s</a>', 'mobile-dj-manager' ),
					admin_url( 'post-new.php?post_type=mdjm-event' ),
					mdjm_get_label_singular() );
				?>
				&nbsp;&nbsp;&nbsp;|&nbsp;&nbsp;&nbsp;
				<?php printf(
					__( '<a href="%s">Manage %s</a>', 'mobile-dj-manager' ),
					admin_url( 'edit.php?post_type=mdjm-event' ),
					mdjm_get_label_plural() );
				?>
				&nbsp;&nbsp;&nbsp;|&nbsp;&nbsp;&nbsp;
				<?php printf(
					__( '<a href="%s">Transactions</a>', 'mobile-dj-manager' ),
					admin_url( 'edit.php?post_type=mdjm-transaction' ) );
				?>
				&nbsp;&nbsp;&nbsp;|&nbsp;&nbsp;&nbsp;
				<?php printf(
					__( '<a href="%s">Settings</a>', 'mobile-dj-manager' ),
					admin_url( 'admin.php?page=mdjm-settings' ) );
				?>
            </p>

			<?php $sources = $stats->get_enquiry_sources_by_date( 'this_month' ); ?>

			<?php if ( ! empty( $sources ) ) : ?>
				
				<?php foreach( $sources as $count => $source ) : ?>
					<p>
					 <?php printf( __( '<p>Most enquiries have been received via <strong>%s (%d)</strong> so far this month.', 'mobile-dj-manager' ), $source, (int) $count ); ?>
					</p>
				<?php endforeach; ?>
				
			<?php else : ?>
				<p><?php _e( 'No enquiries yet this month.', 'mobile-dj-manager' ); ?></p>
			<?php endif; ?>

			<?php do_action( 'mdjm_after_events_overview' ); ?>
			
		</div>
    
		<?php
	}
	
} // mdjm_widget_events_overview
	
/*
* f_mdjm_dash_availability
* 07/01/2015
* @since 0.9.9.6
* Displays the MDJM AVailability Status on the main WP Dashboard
*/
function f_mdjm_dash_availability()	{
	global $mdjm_settings;
	
	/* Enqueue the jQuery Datepicker Scripts */
	wp_enqueue_script('jquery-ui-datepicker');
	wp_enqueue_style('jquery-ui-css', '//ajax.googleapis.com/ajax/libs/jqueryui/1.8.2/themes/smoothness/jquery-ui.css');
	
	mdjm_insert_datepicker(
		array(
			'class'		=> 'check_custom_date',
			'altfield'	=> 'check_date'
		)
	);
	?>
	
	<table width="100%" border="0" cellspacing="0" cellpadding="0">
	<?php /* Display Availability Overview */ ?>
	<?php get_availability_activity( 0, 0 ); ?>
	
	<?php /* Availability Check */ ?>
	<form name="availability-check" id="availability-check" method="post" action="<?php mdjm_get_admin_page( 'availability', 'echo' ); ?>">
	<?php
	if( !current_user_can( 'administrator' ) )	{
		?><input type="hidden" name="check_employee" id="check_employee" value="<?php echo get_current_user_id(); ?>" /><?php
	}
	else	{
		?><input type="hidden" name="check_employee" id="check_employee" value="all" /><?php	
	}
	?>
	<tr>
	<td colspan="2">&nbsp;</td>
	</tr>
	<tr>
	<td colspan="2"><input type="text" name="show_check_date" id="show_check_date" class="check_custom_date" required="required" style="font-size:12px" />&nbsp;&nbsp;&nbsp;
	<input type="hidden" name="check_date" id="check_date" />
	<?php submit_button( 'Check Date', 'primary small', 'submit', false, '' ); ?></td>
	</tr>
	</form>
	</table>
	<?php	
}

/**
 * Add event count to At a glance widget
 *
 * @since	1.4
 * @return	void
 */
function mdjm_dashboard_at_a_glance_widget( $items ) {
	$num_posts = mdjm_count_events();
	$count     = 0;
	$statuses  = mdjm_all_event_status();

	foreach( $statuses as $status => $label )	{
		if ( ! empty( $num_posts->$status ) )	{
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
