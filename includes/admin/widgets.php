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
if ( ! defined( 'ABSPATH' ) ) exit;

function mdjm_get_event_stats( $period = 'this_week', $status = 'any' )	{
	$periods = array(
		'this_week'		=> array( 'year' => date( 'Y' ), 'week' => date( 'W' ) ),
		'last_week'		=> array( 'year' => date( 'Y', strtotime( '-1 week' ) ), 'week' => date( 'W', strtotime( '-1 week' ) ) ),
		'this_month'	=> array( 'year' => date( 'Y' ), 'month' => date( 'm' ) ),
		'last_month'	=> array( 'year' => date( 'Y', strtotime( '-1 month' ) ), 'month' => date( 'm', strtotime( '-1 month' ) ) ),
		'this_year'		=> array( 'year' => date( 'Y' ) ),
		'last_year'		=> array( 'year' => date( 'Y', strtotime( '-1 year' ) ) ),
	);
	
	$args = array(
		'post_status'	=> $status,
		'date_query'	=> array(
			$periods[ $period ]
		)
	);
	
	$events = mdjm_get_events( $args );
	$count  = 0;
	
	if ( $events )	{
		$count = count( $events );
	}
	
	return $count;
	
}

function mdjm_get_txn_stats( $period = 'this_week', $status = 'any'  )	{
	$periods = array(
		'this_week'		=> array( 'year' => date( 'Y' ), 'week' => date( 'W' ) ),
		'last_week'		=> array( 'year' => date( 'Y', strtotime( '-1 week' ) ), 'week' => date( 'W', strtotime( '-1 week' ) ) ),
		'this_month'	=> array( 'year' => date( 'Y' ), 'month' => date( 'm' ) ),
		'last_month'	=> array( 'year' => date( 'Y', strtotime( '-1 month' ) ), 'month' => date( 'm', strtotime( '-1 month' ) ) ),
		'this_year'		=> array( 'year' => date( 'Y' ) ),
		'last_year'		=> array( 'year' => date( 'Y', strtotime( '-1 year' ) ) ),
	);
	
	$args = array(
		'post_status'	=> $status,
		'date_query'	=> array(
			$periods[ $period ]
		),
		'meta_query'	=> array(
			array(
				'_mdjm_txn_status'	=> 'Completed'
			)
		)
	);
	
	$txns = mdjm_get_txns( $args );
	
	$total = 0;
	
	if ( $txns )	{
		
		foreach( $txns as $txn )	{
			
			if ( $args['post_status'] == 'any' )	{
				
				if ( $txn->post_status == 'mdjm-income' )	{
					$total += get_post_meta( $txn->ID, '_mdjm_txn_total', true );
				} else	{
					$total = ( $total - get_post_meta( $txn->ID, '_mdjm_txn_total', true ) );
				}
				
			} else	{
				
				$total += get_post_meta( $txn->ID, '_mdjm_txn_total', true );
				
			}
		}
		
	}
	
	return mdjm_format_amount( $total );
	
}

/**
 * Registers the dashboard widgets.
 *
 * @since	1.3
 * @param
 * @return
 */
function mdjm_add_wp_dashboard_widgets() {
	
	$overview_function = mdjm_employee_can( 'manage_mdjm' ) ? 'mdjm_widget_events_overview_admin' : 'mdjm_widget_events_overview_employee';

	wp_add_dashboard_widget( 'mdjm-widget-overview', sprintf( __( '%s Overview', 'mobile-dj-manager' ), mdjm_get_label_plural() ), $overview_function );

	wp_add_dashboard_widget( 'mdjm-availability-overview', 'MDJM Availability', 'f_mdjm_dash_availability' );

} // mdjm_add_wp_dashboard_widgets
add_action( 'wp_dashboard_setup', 'mdjm_add_wp_dashboard_widgets' );

/**
 * Generate and display the content for the Events Overview Admin dashboard widget.
 *
 * @since	1.3
 * @param
 * @return
 */
function mdjm_widget_events_overview_admin() {

	$next_event = mdjm_get_next_event();
	
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
                    <td><?php echo mdjm_get_event_stats( 'this_month' ); ?></td>
                    <td><?php echo mdjm_get_event_stats( 'this_year' ); ?></td>
                    <td><?php echo mdjm_get_event_stats( 'last_year' ); ?></td>
                </tr>
                <tr>
                	<th><?php printf( __( '%s Converted', 'mobile-dj-manager' ), get_post_status_object( 'mdjm-enquiry' )->plural ); ?></th>
                    <td><?php echo mdjm_get_event_stats( 'this_month', mdjm_active_event_statuses() ); ?></td>
                    <td><?php echo mdjm_get_event_stats( 'this_year', mdjm_active_event_statuses() ); ?></td>
                    <td><?php echo mdjm_get_event_stats( 'last_year', mdjm_active_event_statuses() ); ?></td>
                </tr>
                <tr>
                	<th><?php printf( __( '%s Completed', 'mobile-dj-manager' ), mdjm_get_label_plural() ); ?></th>
                    <td><?php echo mdjm_get_event_stats( 'this_month', 'mdjm-completed' ); ?></td>
                    <td><?php echo mdjm_get_event_stats( 'this_year', 'mdjm-completed' ); ?></td>
                    <td><?php echo mdjm_get_event_stats( 'last_year', 'mdjm-completed' ); ?></td>
                </tr>
                <tr>
                	<th><?php _e( 'Income', 'mobile-dj-manager' ); ?></th>
                    <td><?php echo mdjm_currency_filter( mdjm_get_txn_stats( 'this_month', 'mdjm-income' ) ); ?></td>
                    <td><?php echo mdjm_currency_filter( mdjm_get_txn_stats( 'this_year', 'mdjm-income' ) ); ?></td>
                    <td><?php echo mdjm_currency_filter( mdjm_get_txn_stats( 'last_year', 'mdjm-income' ) ); ?></td>
                </tr>
                <tr>
                	<th><?php _e( 'Outgoings', 'mobile-dj-manager' ); ?></th>
                    <td><?php echo mdjm_currency_filter( mdjm_get_txn_stats( 'this_month', 'mdjm-expenditure' ) ); ?></td>
                    <td><?php echo mdjm_currency_filter( mdjm_get_txn_stats( 'this_year', 'mdjm-expenditure' ) ); ?></td>
                    <td><?php echo mdjm_currency_filter( mdjm_get_txn_stats( 'last_year', 'mdjm-expenditure' ) ); ?></td>
                </tr>
                <tr>
                	<th><?php _e( 'Earnings', 'mobile-dj-manager' ); ?></th>
                    <td><?php echo mdjm_currency_filter( mdjm_get_txn_stats( 'this_month' ) ); ?></td>
                    <td><?php echo mdjm_currency_filter( mdjm_get_txn_stats( 'this_year' ) ); ?></td>
                    <td><?php echo mdjm_currency_filter( mdjm_get_txn_stats( 'last_year' ) ); ?></td>
                </tr>
            </tbody>
        </table>
        
        <p><?php echo 
        	sprintf( __( '<a href="%s">Create %s</a>', 'mobile-dj-manager' ), admin_url( 'post-new.php?post_type=mdjm-event' ), mdjm_get_label_singular() ) .
			'&nbsp;&nbsp;&nbsp;|&nbsp;&nbsp;&nbsp;' .
			sprintf( __( '<a href="%s">Manage %s</a>', 'mobile-dj-manager' ), admin_url( 'edit.php?post_type=mdjm-event' ), mdjm_get_label_plural() ) .
			'&nbsp;&nbsp;&nbsp;|&nbsp;&nbsp;&nbsp;' .
			sprintf( __( '<a href="%s">MDJM Settings</a>', 'mobile-dj-manager' ), admin_url( 'admin.php?page=mdjm-settings' ) ); ?>
			
		</p>
        
    </div>
    
    <?php
	
	/*$next_event = MDJM()->events->next_event( '', 'dj' );
	
	if( !empty( $next_event ) )	{
		$event_types = get_the_terms( $next_event[0]->ID, 'event-types' );
	}
					
	$bookings_today = MDJM()->events->employee_bookings();
	?>
	<table width="100%" border="0" cellspacing="0" cellpadding="0">
	 <tr>
		<th width="40%" align="left">Today's Status:</th>
		<td width="60%">
		<?php
		echo ( !empty( $next_event ) && date( 'd-m-Y', strtotime( get_post_meta( $next_event[0]->ID, '_mdjm_event_date', true ) ) ) == date( 'd-m-Y' ) 
			? '<a href="' . admin_url( 'post.php?post=' . $next_event[0]->ID . '&action=edit' ) . '">Booked from ' . 
				date( mdjm_get_option( 'time_format', 'H:i' ), strtotime( get_post_meta( $next_event[0]->ID, '_mdjm_event_start', true ) ) ) . '</a>'
				 
			: 'Available' );
		?>
		</td>
	  </tr>
	  <tr>
		<th width="40%" align="left">Next Event:</th>
		<td width="60%">
		<?php
		if( !empty( $next_event ) )	{
			$eventinfo = MDJM()->events->event_detail( $next_event[0]->ID );
			
			echo '<a href="' . get_edit_post_link( $next_event[0]->ID ) . '">' . 
				date( 'd M Y', $eventinfo['date'] ) . '</a> (' . $eventinfo['type'] . ')';
		}
		else
			echo 'No event scheduled';
		?>
		</td>
	  </tr>
	  <?php if( mdjm_employee_can( 'read_events_all' ) )	{
		  ?>
		  <tr>
			<th align="left">Outstanding Enquiries:</th>
			<td>
			<?php
					$e = MDJM()->events->mdjm_count_event_status( 'mdjm-enquiry' );
					$ue = MDJM()->events->mdjm_count_event_status( 'mdjm-unattended' );
					echo '<a href="' . mdjm_get_admin_page( 'enquiries' ) . '">' . $e . _n( ' Enquiry', ' Enquiries', $e ) . '</a> | ' .
					'<a href="' . mdjm_get_admin_page( 'events' ) . '&post_status=mdjm-unattended">' . $ue . ' Unattended' . '</a>';
			?>
			</td>
		  </tr>
		  <?php
	  }
	  ?>
	</table>
	<div>
	<ul>
	<?php
		if( current_user_can( 'administrator' ) && MDJM_MULTI == true )	{
			$dj_event_results = MDJM()->events->employee_bookings();
			if( $dj_event_results )	{
				foreach( $dj_event_results as $info )	{
					$djinfo = get_userdata( get_post_meta( $info->ID, '_mdjm_event_dj', true ) );
					
					if( !empty( $djinfo ) && !empty( $djinfo->first_name ) )
						$dj_name = $djinfo->first_name;
						
					else
						$dj_name = __( 'No name', 'mobile-dj-manager' );
					
					$event_start = date( 'H:i', strtotime( get_post_meta( $info->ID, '_mdjm_event_start', true ) ) );
					
					if( empty( $event_start ) )
						$event_start = __( 'No start', 'mobile-dj-manager' );
					
					echo '<li>' . $dj_name . ' is working from ' . $event_start . ' (<a href="' . admin_url( 'post.php?post=' . $info->ID . '&action=edit' ) . '">view details</a>)</li>';
				}
			}
		}
	?>
		<li><?php if( mdjm_employee_can( 'manage_events' ) ) { ?><a href="<?php echo admin_url( 'post-new.php?post_type=' . MDJM_EVENT_POSTS ); ?>">Add New Event</a> | <?php } ?><a href="<?php echo admin_url( 'admin.php?page=mdjm-dashboard' ); ?>">View Dashboard</a> | <a href="<?php echo admin_url( 'admin.php?page=mdjm-settings' ); ?>">Edit Settings</a>
		
		</li>
	</ul>
	</div>
	<div class="alternate">
	<?php wp_widget_rss_output( 'http://www.mydjplanner.co.uk/category/news/feed/rss2/', $args = array( 'show_author' => 0, 'show_date' => 1, 'show_summary' => 1, 'items' => 1 ) ); ?>
	</div>
	<?php
	*/
} // mdjm_widget_events_overview_admin

/**
 * Generate and display the content for the Events Overview Employee dashboard widget.
 *
 * @since	1.3
 * @param
 * @return
 */
function mdjm_widget_events_overview_employee() {

	$next_event = mdjm_get_next_event();
		
	?>
    
    <table id="mdjm-events-overview">
    
    	<tr>
        	<th><?php _e( "Today's Status:", 'mobile-dj-manager' ); ?></th>
            <td>-</td>
        </tr>
        
        <tr>
        	<th><?php printf( __( 'Next %s:', 'mobile-dj-manager' ), mdjm_get_label_singular() ); ?></th>
            <td>
            	<?php if ( ! empty( $next_event ) ) : ?>
                
                	<?php printf( '<a href="%s">%s</a>', admin_url( 'post.php?post=' . $next_event->ID . '&action=edit' ), mdjm_get_event_date( $next_event->ID ) ); ?>
                    
                <?php else : ?>
                
                	<?php printf( __( 'No %s scheduled', 'mobile-dj-manager' ), mdjm_get_label_singular( true ) ); ?>
                
                <?php endif; ?>
            </td>
        </tr>
    
    </table>
    
    <?php
	
} // mdjm_widget_events_overview_employee
	
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