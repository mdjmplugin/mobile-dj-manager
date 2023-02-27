<?php
/**
 * mdjm-dashboard.php
 * MDJM_Dashboard Class
 * 21/02/2015
 * @since 1.1
 * A class to produce the MDJM Dashboard Overview
 * 
 * @version 1.0
 * @21/02/2015
 *
 * TODO 7 day status (admin & DJ)
 *	Status overview for month (admin & DJ)
 *	To do list (admin only)
 * 	Availability check
 *	Recent activity (payments etc..)
 * 	Latest news
 */
// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
/**
 * Tasks Page
 *
 * Renders the task page contents.
 *
 * @since   1.0
 * @return  void
 */
function mdjm_dashboard_page() { 
	
	global $current_user;
	
	// Monthly Dashboard Variables
		$current_month = date ( 'M Y' );
		$item = wp_get_current_user();
		$nextevent   = mdjm_get_employees_next_event( $item->ID );
		$completed_events = sprintf( '<a href="' . esc_url( admin_url( 'edit.php?post_status=mdjm-completed&post_type=mdjm-event' ) ) . '">' . mdjm_event_count ( 'mdjm-completed' ) . '</a>' );
		$unattended_enquiries = sprintf( '<a href="' . esc_url( admin_url( 'edit.php?post_type=mdjm-event&post_status=mdjm-unattended' ) ) . '">'. mdjm_event_count( 'mdjm-unattended' ) . '</a>' );
	 	$failed_enquiries = sprintf( '<a href="' . esc_url( admin_url( 'edit.php?post_status=mdjm-failed&post_type=mdjm-event' ) ) . '">' . mdjm_event_count ( 'mdjm-failed' ) . '</a>' );
	
	// Yearly Dashboard Variables
		$current_year = date('Y');
	
	
// Get the start and end of the current month
$now = new DateTime();
$current_month_start = $now->format('Y-m-01');
$current_month_end = $now->format('Y-m-t');

$required_status = array( 'mdjm-completed', 'mdjm-enquiry', 'mdjm-contract', 'mdjm-approved' );

// Set up the $args array with the required parameters
$args = array(
    'employee_id' => $item->ID,
    'post_type' => 'mdjm-event',
    'post_status' => $required_status,
    'fields' => 'ids',
    'meta_query' => array(
        array(
            'key' => '_mdjm_event_date',
            'value' => array( $current_month_start, $current_month_end ),
            'type' => 'DATE',
            'compare' => 'BETWEEN'
        )
    )
);
	$events = mdjm_get_events( $args );


// Count the number of events
$total_events = count( $events );
	
	function get_earnings_by_date( $day = null, $month_num, $year = null, $hour = null ) {
		$args = array(
			'post_type'              => 'mdjm-transaction',
			'nopaging'               => true,
			'year'                   => $year,
			'monthnum'               => $month_num,
			'post_status'            => array( 'mdjm-income', 'mdjm-expenditure' ),
			'meta_key'               => '_mdjm_txn_status',
			'meta_value'             => 'Completed',
			'fields'                 => 'ids',
			'update_post_term_cache' => false,
		);

		if ( ! empty( $day ) ) {
			$args['day'] = $day;
		}

		if ( ! empty( $hour ) ) {
			$args['hour'] = $hour;
		}

		$args     = apply_filters( 'mdjm_get_earnings_by_date_args', $args );
		$key      = 'mdjm_stats_' . substr( md5( serialize( $args ) ), 0, 15 );
		$earnings = get_transient( $key );

/*		if ( false === $earnings ) {
			$income  = $this->get_income_by_date( $day, $month_num, $year, $hour );
			$expense = $this->get_expenses_by_date( $day, $month_num, $year, $hour );

			$earnings = $income - $expense;

			// Cache the results for one hour.
			set_transient( $key, $earnings, HOUR_IN_SECONDS );
		}*/

		return round( $earnings, 2 );
	} // get_earnings_by_date


	
?>
		<div class="wrap">
		<h1>
			Dashboard for <?php echo $current_user->display_name; ?>
		</h1>

<div class="enquiry-dashboard">
       <table class="dashboard-monthly-table">
		   <tbody>
            <tr>
				<td colspan="2" class="dashboard-table-header"><strong>Monthly DJ Overview for <?php echo $current_month ?></strong></td>
            </tr>
          <tr>
            <td class="dashboard-table-items" width="30%">Bookings:</td>
            <td class="dashboard-table-content" width="70%"><?php echo $total_events ?></td>
          </tr>
		<tr>
            <td class="dashboard-table-items" width="30%">Next Booking:</td>
            <td class="dashboard-table-content" width="70%"><?php printf(
			! empty( $nextevent ) ? '<a href="' . esc_url( 'admin.php?page=mdjm-playlists&event_id=' . ( $nextevent->ID ) ) . '">' .
			esc_html( mdjm_format_short_date( get_post_meta( $nextevent->ID, '_mdjm_event_date', true ) ) ) . '</a>' :
			esc_html__( 'None', 'mobile-dj-manager' )
		); ?></td>
          </tr>
          <?php if( user_can( $item->ID, 'administrator' ) )	{
			  ?>
              <tr>
                <td class="dashboard-table-items" width="30%">Outstanding Enquiries:</td>
                <td class="dashboard-table-content" width="70%"><?php echo $unattended_enquiries ?></td>
              </tr>
              <tr>
                <td class="dashboard-table-items" width="30%">Lost Enquiries:</td>
                <td class="dashboard-table-content" width="70%"><?php echo $failed_enquiries ?></td>
              </tr>
				<?php
		  }
		  ?>
          <tr>
           <td class="dashboard-table-items" width="30%">Completed Bookings:</td>
            <td class="dashboard-table-content" width="70%"><?php echo $completed_events?></td>
          </tr>
		<?php if( current_user_can( 'administrator' ) )	{
			?>
          <tr>
            <td class="dashboard-table-items" width="30%">Potential Earnings: </td>
            <td class="dashboard-table-content" width="70%"><?php echo get_earnings_by_date($month,0) ?></td>
          </tr>
          <?php
		}
		  ?>
          <tr>
            <td class="dashboard-table-items" width="30%">Earnings so Far:</td>
            <td class="dashboard-table-content" width="70%"><?php echo  mdjm_currency_filter( 0.00 ); ?></td>
          </tr>
	</table>
       <table class="dashboard-yearly-table">
		   <tbody>
            <tr>
				<td colspan="2" class="dashboard-table-header"><strong>Yearly DJ Overview for <?php echo $current_year ?></strong></td>
            </tr>
          <tr>
            <td class="dashboard-table-items" width="30%">Bookings:</td>
            <td class="dashboard-table-content" width="70%"><?php echo $total_events ?></td>
          </tr>
		<tr>
            <td class="dashboard-table-items" width="30%">Next Booking:</td>
            <td class="dashboard-table-content" width="70%"><?php printf(
			! empty( $nextevent ) ? '<a href="' . esc_url( 'admin.php?page=mdjm-playlists&event_id=' . ( $nextevent->ID ) ) . '">' .
			esc_html( mdjm_format_short_date( get_post_meta( $nextevent->ID, '_mdjm_event_date', true ) ) ) . '</a>' :
			esc_html__( 'None', 'mobile-dj-manager' )
		); ?></td>
          </tr>
          <?php if( user_can( $item->ID, 'administrator' ) )	{
			  ?>
              <tr>
                <td class="dashboard-table-items" width="30%">Outstanding Enquiries:</td>
                <td class="dashboard-table-content" width="70%"><?php echo $unattended_enquiries ?></td>
              </tr>
              <tr>
                <td class="dashboard-table-items" width="30%">Lost Enquiries:</td>
                <td class="dashboard-table-content" width="70%"><?php echo $failed_enquiries ?></td>
              </tr>
				<?php
		  }
		  ?>
          <tr>
           <td class="dashboard-table-items" width="30%">Completed Bookings:</td>
            <td class="dashboard-table-content" width="70%"><?php echo $completed_events?></td>
          </tr>
		<?php if( current_user_can( 'administrator' ) )	{
			?>
          <tr>
            <td class="dashboard-table-items" width="30%">Potential Earnings: </td>
            <td class="dashboard-table-content" width="70%"><?php echo get_earnings_by_date($month,0) ?></td>
          </tr>
          <?php
		}
		  ?>
          <tr>
            <td class="dashboard-table-items" width="30%">Earnings so Far:</td>
            <td class="dashboard-table-content" width="70%"><?php echo  mdjm_currency_filter( 0.00 ); ?></td>
          </tr>
	</table>
</div>
	</div>
                <?php
			}
// mdjm_dashboard_page
