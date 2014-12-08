<?php
	defined('ABSPATH') or die("Direct access to this page is disabled!!!");
	if ( !current_user_can( 'manage_options' ) && !current_user_can( 'manage_mdjm' ) )  {
		wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
	}
	
	// If recently updated, display the release notes
	f_mdjm_has_updated();

	function mdjm_dashboard() {
		global $mdjm_options;
		$current_user = wp_get_current_user();
		$events = f_mdjm_dj_get_events( $current_user->ID );
		$days_to_go = time() - strtotime( $events['next_event'] )
?>
        <div class="wrap">
        <h2>Mobile DJ Manager - <?php echo $current_user->display_name; ?> (<?php if( !current_user_can( 'manage_options' ) ) echo 'DJ'; else echo 'Admin'; ?>)</h2>
        <hr />
        <h3>
        <?php
		$dash_dj = f_mdjm_dashboard_dj_overview();
        ?></h3>
        <table width="100%" border="0" cellspacing="0" cellpadding="0" class="widefat">
          <tr>
            <td width="60%"><table width="100%" border="0" cellspacing="0" cellpadding="0" class="widefat">
          <tr>
            <td colspan="2" class="alternate"><strong>Monthly DJ Overview for <?php echo date( 'F Y' ); ?></strong></td>
            </tr>
          <tr>
            <td width="30%">Active Bookings:</td>
            <td width="70%"><?php echo $dash_dj['month_active_events']; ?></td>
          </tr>
          <?php if( current_user_can( 'administrator' ) || dj_can( 'view_enquiry' ) )	{
			  ?>
              <tr>
                <td>Outstanding Enquiries:</td>
                <td><?php echo $dash_dj['month_enquiries']; ?></td>
              </tr>
              <tr>
                <td>Lost Enquiries:</td>
                <td><?php echo $dash_dj['lost_month_enquiries']; ?></td>
              </tr>
				<?php
		  }
		  ?>
          <tr>
            <td>Completed Bookings:</td>
            <td><?php echo $dash_dj['month_completed']; ?></td>
          </tr>
		<?php if( current_user_can( 'administrator' ) || dj_can( 'view_enquiry' ) )	{
			?>
          <tr>
            <td>Potential Earnings: </td>
            <td>&pound;<?php echo number_format( $dash_dj['potential_month_earn'], 2 ); ?></td>
          </tr>
          <?php
		}
		  ?>
          <tr>
            <td>Earnings so Far:</td>
            <td>&pound;<?php echo number_format( $dash_dj['month_earn'], 2 ); ?></td>
          </tr>
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
            <td>£<?php echo number_format( $dash_dj['potential_year_earn'], 2 ); ?></td>
          </tr>
          <?php
		  }
		  ?>
          <tr>
            <td>Earnings so Far:</td>
            <td>£<?php echo number_format( $dash_dj['year_earn'], 2 ); ?></td>
          </tr>
            </table>
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
              <tr>
                  <td>To do list</td>
                  <td>(coming soon)</td>
              </tr>
              <tr class="alternate">
                <td colspan="2"><?php wp_widget_rss_output( 'http://www.mydjplanner.co.uk/category/news/feed/rss2/', $args = array( 'show_author' => 0, 'show_date' => 1, 'show_summary' => 1, 'items' => 3 ) ); ?></td>
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
                <td>&pound;<?php echo number_format( $dash_emp['potential_month_earn'], 2 ); ?></td>
                </tr>
                <tr>
                <td>Earnings so Far:</td>
                <td>&pound;<?php echo number_format( $dash_emp['month_earn'], 2 ); ?></td>
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
                <td>&pound;<?php echo number_format( $dash_emp['potential_year_earn'], 2 ); ?></td>
                </tr>
                <tr>
                <td>Earnings so Far:</td>
                <td>&pound;<?php echo number_format( $dash_emp['year_earn'], 2 ); ?></td>
                </tr>
                </table></td>
                <td width="40%" valign="top"><table width="100%" border="0" cellspacing="0" cellpadding="0" class="widefat">
                  <tr>
                    <td width="100%" class="alternate"><strong>Latest Support Topics</strong></td>
                  </tr>
                  <tr>
                    <td><?php wp_widget_rss_output( 'http://www.mydjplanner.co.uk/forums/feed/?post_type=topic', $args = array( 'show_author' => 0, 'show_date' => 0, 'show_summary' => 0, 'items' => 3 ) ); ?></td>
                  </tr>
                  <?php
			  if( current_user_can( 'administrator' ) && isset( $mdjm_options['multiple_dj'] ) )	{
				  $dj_event_results = f_mdjm_dj_working_today();
				  ?>
                  <?php
			  }
			  ?>
                </table></td>
                </tr>
                </table>
                <?php
			}
		?>	
        </div>
<?php
	}
	if( isset( $_GET['updated'] ) || isset( $_GET['ver'] ) )	{
		include( 'updated.php' );
	}
	else	{
		mdjm_dashboard();
	}
?>