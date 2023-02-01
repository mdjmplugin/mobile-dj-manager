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
		$item = wp_get_current_user();
		$next_event   = mdjm_get_employees_next_event( $item->ID );
		$total_events = mdjm_count_employee_active_events( $item->ID );
		$unattended_enquiries = sprintf( '<a href="' . esc_url( admin_url( 'edit.php?post_type=mdjm-event&post_status=mdjm-unattended' ) ) . '">'. mdjm_event_count( 'mdjm-unattended' ) . '</a>' );
?>
		
		<h1>
			<?php esc_html_e( 'Dashboard', 'mobile-dj-manager' ); ?>
		</h1>
<div class="wrap">
<div class="enquiry-dashboard">
       <table class="dashboard-table">
		   <tbody>
            <tr>
				<td colspan="2" class="dashboard-table-header"><strong>Monthly DJ Overview for <?php echo date( 'F Y' ); ?></strong></td>
            </tr>
          <tr>
            <td class="dashboard-table-items" width="30%">Active Bookings:</td>
            <td class="dashboard-table-content" width="70%"><?php echo "hello" ?></td>
          </tr>
          <?php if( user_can( $item->ID, 'administrator' ) )	{
			  ?>
              <tr>
                <td class="dashboard-table-items" width="30%">Outstanding Enquiries:</td>
                <td class="dashboard-table-content" width="70%"><?php echo $unattended_enquiries ?></td>
              </tr>
              <tr>
                <td class="dashboard-table-items" width="30%">Lost Enquiries:</td>
                <td class="dashboard-table-content" width="70%"><?php echo "Too many" ?></td>
              </tr>
				<?php
		  }
		  ?>
          <tr>
           <td class="dashboard-table-items" width="30%">Completed Bookings:</td>
            <td class="dashboard-table-content" width="70%"><?php echo "Loads" ?></td>
          </tr>
		<?php if( current_user_can( 'administrator' ) )	{
			?>
          <tr>
            <td>Potential Earnings: </td>
            <td><?php echo number_format( $dash_dj['potential_month_earn'], 2 ); ?></td>
          </tr>
          <?php
		}
		  ?>
          <tr>
            <td>Earnings so Far:</td>
            <td><?php echo  number_format( $dash_dj['month_earn'], 2 ); ?></td>
          </tr>
	</table>
          <tr>
            <td colspan="2" class="alternate"><strong>Annual DJ Overview for <?php echo date( 'Y' ); ?></strong></td>
            </tr>
         <?php if( current_user_can( 'administrator' ) || dj_can( 'view_enquiry' ) )	{
			 ?>
          <tr>
            <td>Outstanding Enquiries:</td>
            <td><?php echo $dash_dj['year_enquiries']; ?></td>
          </tr>
          <tr>
            <td>Lost Enquiries:</td>
            <td><?php echo $dash_dj['lost_year_enquiries']; ?></td>
          </tr>
          <?php
		 }
		 ?>
          <tr>
            <td>Completed Bookings:</td>
            <td><?php echo $dash_dj['year_completed']; ?></td>
          </tr>
          <?php if( current_user_can( 'administrator' ) || dj_can( 'view_enquiry' ) )	{
			  ?>
          <tr>
            <td>Potential Earnings:</td>
            <td><?php echo f_mdjm_currency() . number_format( $dash_dj['potential_year_earn'], 2 ); ?></td>
          </tr>
          <?php
		  }
		  ?>
          <tr>
            <td>Earnings so Far:</td>
            <td><?php echo f_mdjm_currency() . number_format( $dash_dj['year_earn'], 2 ); ?></td>
          </tr>
			   </tbody>
            </table>
</div>
	</div>
	
        </td>
            <td width="40%" valign="top"><table width="100%" border="0" cellspacing="0" cellpadding="0" class="widefat">
              <tr>
                <td colspan="2" class="alternate"><strong><?php echo date( 'l jS F Y' ); ?></strong></td>
              </tr>
              <tr>
                <td width="35%">Your Status:</td>
                <td width="65%"><?php if( $events['next_event'] == date( "jS F Y" ) ) echo 'Booked from ' . $events['next_event_start'] . ' (<a href="' . admin_url() . 'admin.php?page=mdjm-events&action=view_event_form&event_id=' . $events['event_id'] . '">view details</a>)'; else echo 'Available'; ?></td>
              </tr>
              <?php
			  if( current_user_can( 'administrator' ) && $mdjm_options['multiple_dj'] )	{
				  $dj_event_results = f_mdjm_dj_working_today();
				  ?>
				  <tr>
					<td>Employee Bookings:</td>
					<?php 
					if( empty( $dj_event_results ) ) {
							?>
							<td>None</td>
							<?php
					}
					else	{
						echo '<td>';
						foreach( $dj_event_results as $info )	{
							$djinfo = get_userdata( $info->event_dj );
							echo $djinfo->display_name . ' from ' . date( $mdjm_options['time_format'], strtotime( $info->event_start ) ) . ' (<a href="' . admin_url() . 'admin.php?page=mdjm-events&action=view_event_form&event_id=' . $info->event_id . '">view details</a>)<br />';
						}
						echo '</td>';
					}
					?>
				  </tr>
					<?php
			  }
			  ?>
                <form name="availability-check" id="availability-check" method="post" action="<?php f_mdjm_admin_page( 'availability' ); ?>">
                <?php
				if( !current_user_can( 'administrator' ) )	{
					?><input type="hidden" name="check_employee" id="check_employee" value="<?php echo get_current_user_id(); ?>" /><?php
				}
				else	{
					?><input type="hidden" name="check_employee" id="check_employee" value="all" /><?php	
				}
				?>
                <tr>
                <td><label for="show_check_date">Availability Check:</label></th>
                <td><input type="text" name="show_check_date" id="show_check_date" class="check_custom_date" required="required" />&nbsp;&nbsp;&nbsp;
                <input type="hidden" name="check_date" id="check_date" />
                <?php submit_button( 'Check Date', 'primary small', 'submit', false, '' ); ?>
                </td>
                </tr>
                </form>
              <tr>
                  <td>To do list</td>
                  <td>(coming soon)</td>
              </tr>
              <tr>
                  <td colspan="2"><a href="http://twitter.com/mobiledjmanager" class="twitter-follow-button" data-show-count="false">Follow @mobiledjmanager</a>
<script src="http://platform.twitter.com/widgets.js" type="text/javascript"></script><br />
<div class="fb-like" data-href="https://www.facebook.com/pages/Mobile-DJ-Manager-for-WordPress/544353295709781?ref=bookmarks" data-layout="button" data-action="like" data-show-faces="false" data-share="false"></div></td>
              </tr>
              <tr class="alternate">
                <td colspan="2"><strong>Your 7 Day Schedule</strong></td>
              </tr>
              <tr>
              <td colspan="2"><?php get_availability_activity( 0, 0 ); ?></td>
              </tr>
            </table></td>
          </tr>
          </table>
		<?php
			if( current_user_can( 'administrator' ) && isset( $mdjm_options['multiple_dj'] ) && $mdjm_options['multiple_dj'] == 'Y' )	{
				$dash_emp = f_mdjm_dashboard_employee_overview();
		?>
                <hr />
                <table width="100%" border="0" cellspacing="0" cellpadding="0" class="widefat">
                <tr>
                <td width="60%"><table width="100%" border="0" cellspacing="0" cellpadding="0" class="widefat">
                <tr>
                <td colspan="2" class="alternate"><strong>Monthly Employer Overview for <?php echo date( 'F Y' ); ?></strong></td>
                </tr>
                <tr>
                <td width="30%">Active Bookings:</td>
                <td width="70%"><?php echo $dash_emp['month_active_events']; ?></td>
                </tr>
                <tr>
                <td>Outstanding Enquiries:</td>
                <td><?php echo $dash_emp['month_enquiries']; ?></td>
                </tr>
                <tr>
                <td>Lost Enquiries:</td>
                <td><?php echo $dash_emp['lost_month_enquiries']; ?></td>
                </tr>
                <tr>
                <td>Completed Bookings:</td>
                <td><?php echo $dash_emp['month_completed']; ?></td>
                </tr>
                <tr>
                <td>Potential Earnings:</td>
                <td><?php echo f_mdjm_currency() . number_format( $dash_emp['potential_month_earn'], 2 ); ?></td>
                </tr>
                <tr>
                <td>Earnings so Far:</td>
                <td><?php echo f_mdjm_currency() . number_format( $dash_emp['month_earn'], 2 ); ?></td>
                </tr>
                <tr>
                <td colspan="2" class="alternate"><strong>Annual Employer Overview for <?php echo date( 'Y' ); ?></strong></td>
                </tr>
                <tr>
                <td>Outstanding Enquiries:</td>
                <td><?php echo $dash_emp['year_enquiries']; ?></td>
                </tr>
                <tr>
                <td>Lost Enquiries:</td>
                <td><?php echo $dash_emp['lost_year_enquiries']; ?></td>
                </tr>
                <tr>
                <td>Completed Bookings:</td>
                <td><?php echo $dash_emp['year_completed']; ?></td>
                </tr>
                <tr>
                <td>Potential Earnings:</td>
                <td><?php echo f_mdjm_currency() . number_format( $dash_emp['potential_year_earn'], 2 ); ?></td>
                </tr>
                <tr>
                <td>Earnings so Far:</td>
                <td><?php echo f_mdjm_currency() . number_format( $dash_emp['year_earn'], 2 ); ?></td>
                </tr>
                </table></td>
                <td width="40%" valign="top">
                <table width="100%" border="0" cellspacing="0" cellpadding="0" class="widefat">
                  <tr>
                    <td width="100%" class="alternate"><strong>Latest News from <a href="<?php f_mdjm_admin_page( 'mydjplanner' ); ?>">My DJ Planner</a></strong></td>
                  </tr>
                  <tr>
                    <td><?php wp_widget_rss_output( 'http://www.mydjplanner.co.uk/category/news/feed/rss2/', $args = array( 'show_author' => 0, 'show_date' => 1, 'show_summary' => 1, 'items' => 3 ) ); ?></td>
                  </tr>
                  <tr>
                    <td width="100%" class="alternate"><strong>Latest Support Topics</strong></td>
                  </tr>
                  <tr>
                    <td><?php wp_widget_rss_output( 'http://www.mydjplanner.co.uk/forums/feed/?post_type=topic', $args = array( 'show_author' => 0, 'show_date' => 0, 'show_summary' => 0, 'items' => 3 ) ); ?></td>
                  </tr>
                </table>
                </td>
                </tr>
                </table>
                <?php
			}
		?>	
<?php
	}
// mdjm_dashboard_page
