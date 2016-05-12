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
		
		?>
		<div class="mdjm_stat_grid">
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
						<td><?php echo $stats->events_by_date( 'this_month' ); ?></td>
						<td><?php echo $stats->events_by_date( 'this_year' ); ?></td>
						<td><?php echo $stats->events_by_date( 'last_year' ); ?></td>
					</tr>
					<tr>
						<th><?php printf( __( '%s Converted', 'mobile-dj-manager' ), get_post_status_object( 'mdjm-enquiry' )->plural ); ?></th>
						<td><?php echo $stats->events_by_date( 'this_month', mdjm_active_event_statuses() ); ?></td>
						<td><?php echo $stats->events_by_date( 'this_year', mdjm_active_event_statuses() ); ?></td>
						<td><?php echo $stats->events_by_date( 'last_year', mdjm_active_event_statuses() ); ?></td>
					</tr>
					<tr>
						<th><?php printf( __( '%s Completed', 'mobile-dj-manager' ), mdjm_get_label_plural() ); ?></th>
						<td><?php echo $stats->events_by_date( 'this_month', 'mdjm-completed' ); ?></td>
						<td><?php echo $stats->events_by_date( 'this_year', 'mdjm-completed' ); ?></td>
						<td><?php echo $stats->events_by_date( 'last_year', 'mdjm-completed' ); ?></td>
					</tr>
					<tr>
						<th><?php _e( 'Income', 'mobile-dj-manager' ); ?></th>
						<td><?php echo $stats->get_total_income_by_date( 'this_month' ); ?></td>
						<td><?php echo $stats->get_total_income_by_date( 'this_year' ); ?></td>
						<td><?php echo $stats->get_total_income_by_date( 'last_year' ); ?></td>
					</tr>
					<tr>
						<th><?php _e( 'Outgoings', 'mobile-dj-manager' ); ?></th>
						<td><?php echo $stats->get_total_outgoings_by_date( 'this_month' ); ?></td>
						<td><?php echo $stats->get_total_outgoings_by_date( 'this_year' ); ?></td>
						<td><?php echo $stats->get_total_outgoings_by_date( 'last_year' ); ?></td>
					</tr>
					<tr>
						<th><?php _e( 'Earnings', 'mobile-dj-manager' ); ?></th>
						<td><?php echo $stats->get_txns_total_by_date( 'this_month' ); ?></td>
						<td><?php echo $stats->get_txns_total_by_date( 'this_year' ); ?></td>
						<td><?php echo $stats->get_txns_total_by_date( 'last_year' ); ?></td>
					</tr>
				</tbody>
			</table>
			
			<p><?php echo 
				sprintf( __( '<a href="%s">Create %s</a>', 'mobile-dj-manager' ), admin_url( 'post-new.php?post_type=mdjm-event' ), mdjm_get_label_singular() ) .
				'&nbsp;&nbsp;&nbsp;|&nbsp;&nbsp;&nbsp;' .
				sprintf( __( '<a href="%s">Manage %s</a>', 'mobile-dj-manager' ), admin_url( 'edit.php?post_type=mdjm-event' ), mdjm_get_label_plural() ) .
				'&nbsp;&nbsp;&nbsp;|&nbsp;&nbsp;&nbsp;' .
				sprintf( __( '<a href="%s">Transactions</a>', 'mobile-dj-manager' ), admin_url( 'edit.php?post_type=mdjm-transaction' ) ) .
				'&nbsp;&nbsp;&nbsp;|&nbsp;&nbsp;&nbsp;' .
				sprintf( __( '<a href="%s">Settings</a>', 'mobile-dj-manager' ), admin_url( 'admin.php?page=mdjm-settings' ) ); ?>
				
			</p>
			<?php
			$sources = $stats->get_enquiry_sources_by_date( 'this_month' );
			
			if ( ! empty( $sources ) )	{
				
				foreach( $sources as $count => $source )	{
					echo '<p>' . 
						sprintf( __( '<p>Most enquiries have been received via <strong>%s (%d)</strong> so far this month', 'mobile-dj-manager' ),
						$source,
						(int)$count ) .
						'</p>';
						
					break;
				}
				
			}
			
			?>
			
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
	wp_enqueue_style('jquery-ui-css', 'http://ajax.googleapis.com/ajax/libs/jqueryui/1.8.2/themes/smoothness/jquery-ui.css');
	
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