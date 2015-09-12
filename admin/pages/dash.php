<?php
	defined('ABSPATH') or die("Direct access to this page is disabled!!!");
	if ( !current_user_can( 'manage_options' ) && !current_user_can( 'manage_mdjm' ) )  {
		wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
	}
	
	//f_mdjm_has_updated();
	
	function mdjm_dashboard() {
		global $mdjm, $my_mdjm, $current_user;
		
		if( !class_exists( 'MDJM_Dashboard' ) )	{
			require_once( MDJM_PLUGIN_DIR . '/admin/includes/class/class-mdjm-dashboard.php' );
			$mdjm_dash = new MDJM_Dashboard();
		}
			
				
		wp_enqueue_script( 'jquery-ui-datepicker' );
		wp_enqueue_script( 'youtube-subscribe' );
		wp_enqueue_style( 'jquery-ui-css', 'http://ajax.googleapis.com/ajax/libs/jqueryui/1.8.2/themes/smoothness/jquery-ui.css' );
?>
		<script type="text/javascript">
		<?php mdjm_jquery_datepicker_script( array( 'check_custom_date', 'check_date' ) ); ?>
		</script>
        <div class="wrap">
        <h1>Mobile DJ Manager - <?php echo $current_user->display_name; ?> (<?php if( !current_user_can( 'manage_options' ) ) echo 'DJ'; else echo 'Admin'; ?>)</h1>
        <hr />
        <h2>
        <?php
		//$dash_dj = f_mdjm_dashboard_dj_overview();
		$dj_event_count = $mdjm->mdjm_events->count_events_by_status( 'dj', get_current_user_id() );
        ?></h2>
        <table width="100%" border="0" cellspacing="0" cellpadding="0" class="widefat">
          <tr>
            <td width="60%"><table width="100%" border="0" cellspacing="0" cellpadding="0" class="widefat">
          <tr>
            <td colspan="2" class="alternate"><strong>Monthly DJ Overview for <?php echo date( 'F Y' ); ?></strong></td>
            </tr>
          <tr>
            <td width="30%">Active Bookings:</td>
            <td width="70%"><?php echo $dj_event_count['active_month']; ?></td>
          </tr>
          <?php if( current_user_can( 'administrator' ) || dj_can( 'view_enquiry' ) )	{
			  ?>
              <tr>
                <td><a href="<?php echo mdjm_get_admin_page( 'enquiries' ) . '&mdjm_filter_type&mdjm_filter_date=' . 
				date( 'Ym' ); ?>">Outstanding Enquiries:</a></td>
                <td><?php echo $dj_event_count['enquiry_month'] + $dj_event_count['unattended_month'] . 
				( !empty( $dj_event_count['unattended_month'] ) && $dj_event_count['unattended_month'] > 0 ? 
				' (<a href="' . mdjm_get_admin_page( 'unattended' ) . '&mdjm_filter_date=' . date( 'Ym' ) . '">' . 
				$dj_event_count['unattended_month'] . ' Unattended)</a>' : '' ); ?></td>
              </tr>
              <tr>
                <td><a href="<?php echo mdjm_get_admin_page( 'events' ) . '&post_status=mdjm-lost&mdjm_filter_date=' . date( 'Ym' ) . 
				'&mdjm_filter_type'; ?>">Lost Enquiries:</a></td>
                <td><?php echo $dj_event_count['lost_month']; ?></td>
              </tr>
				<?php
		  }
		  ?>
          <tr>
            <td><a href="<?php echo mdjm_get_admin_page( 'events' ) . '&post_status=mdjm-completed&mdjm_filter_date=' . date( 'Ym' ) . 
				'&mdjm_filter_type'; ?>">Completed Bookings:</a></td>
            <td><?php echo $dj_event_count['completed_month']; ?></td>
          </tr>
		<?php if( current_user_can( 'administrator' ) || dj_can( 'view_enquiry' ) )	{
			?>
          <tr>
            <td>Potential Earnings: </td>
            <td><?php echo $mdjm_dash->period_earnings( 'month', $current_user->ID, false ); ?></td>
          </tr>
          <?php
		}
		  ?>
          <tr>
            <td>Earnings so Far:</td>
            <td><?php echo $mdjm_dash->period_earnings( 'month', $current_user->ID, true ); ?></td>
          </tr>
          <tr>
            <td colspan="2" class="alternate"><strong>Annual DJ Overview for <?php echo date( 'Y' ); ?></strong></td>
            </tr>
         <?php if( current_user_can( 'administrator' ) || dj_can( 'view_enquiry' ) )	{
			 ?>
          <tr>
            <td><a href="<?php echo mdjm_get_admin_page( 'enquiries' ); ?>">Outstanding Enquiries:</a></td>
                <td><?php echo $dj_event_count['enquiry_year'] + $dj_event_count['unattended_year'] . 
				( !empty( $dj_event_count['unattended_year'] ) && $dj_event_count['unattended_year'] > 0 ? 
				' (<a href="' . mdjm_get_admin_page( 'unattended' ) . '&mdjm_filter_date=' . date( 'Ym' ) . '">' . 
				$dj_event_count['unattended_year'] . ' Unattended)</a>' : '' ); ?></td>
          </tr>
          <tr>
            <td>Lost Enquiries:</td>
            <td><?php echo $dj_event_count['lost_year']; ?></td>
          </tr>
          <?php
		 }
		 ?>
          <tr>
            <td>Completed Bookings:</td>
            <td><?php echo $dj_event_count['completed_year']; ?></td>
          </tr>
          <?php if( current_user_can( 'administrator' ) || dj_can( 'view_enquiry' ) )	{
			  ?>
          <tr>
            <td>Potential Earnings:</td>
            <td><?php echo $mdjm_dash->period_earnings( 'year', $current_user->ID, false ); ?></td>
          </tr>
          <?php
		  }
		  ?>
          <tr>
            <td>Earnings so Far:</td>
            <td><?php echo $mdjm_dash->period_earnings( 'year', $current_user->ID, true );; ?></td>
          </tr>
            </table>
        </td>
            <td width="40%" valign="top"><table width="100%" border="0" cellspacing="0" cellpadding="0" class="widefat">
              <tr>
                <td colspan="2" class="alternate"><strong><?php echo date( 'l jS F Y' ); ?></strong></td>
              </tr>
              <tr>
                <td width="35%">Your Status:</td>
                <?php
				$next_event = $mdjm->mdjm_events->next_event( $current_user->ID, 'dj' );
				if( !empty( $next_event ) )
					$eventinfo = $mdjm->mdjm_events->event_detail( $next_event[0]->ID );
				?>
                <td width="65%">
				<?php
                echo ( isset( $eventinfo ) && date( 'Y-m-d', $eventinfo['date'] ) == date( 'Y-m-d' ) ?
					'<a href="' . get_edit_post_link( $next_event[0]->ID ) . '">Booked from ' . $eventinfo['start'] . '</a>' : 'Available' ); 
				?>
                </td>
              </tr>
              <?php
			  if( current_user_can( 'administrator' ) && MDJM_MULTI == true )	{
				  $bookings_today = $mdjm->mdjm_events->employee_bookings();
				  //$dj_event_results = f_mdjm_dj_working_today();
				  ?>
				  <tr>
					<td>Employee Bookings:</td>
					<?php 
					if( empty( $bookings_today ) ) {
							?>
							<td>None</td>
							<?php
					}
					else	{
						echo '<td>';
						$i = 1;
						foreach( $bookings_today as $event )	{
							$eventinfo = $mdjm->mdjm_events->event_detail( $event->ID );
								
							echo '<a href="' . get_edit_post_link( $event->ID ) . '">' . 
							$eventinfo['dj']->display_name . ' from ' . $eventinfo['start'] . '</a>' . 
							( $i < count( $bookings_today ) ? '<br />' : '' );
							$i++;
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
                  <td colspan="2"><p><a href="http://twitter.com/mobiledjmanager" class="twitter-follow-button" data-show-count="false">Follow @mobiledjmanager</a>
<script src="http://platform.twitter.com/widgets.js" type="text/javascript"></script></p>
<p><div class="g-ytsubscribe" data-channelid="UCaD6icd6OZ8haoTBc5YjJrw" data-layout="default" data-count="hidden"></div></p></td>
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
			if( current_user_can( 'administrator' ) && MDJM_MULTI == true )	{
				//$dash_emp = f_mdjm_dashboard_employee_overview();
				$emp_event_count = $mdjm->mdjm_events->count_events_by_status();
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
                <td width="70%"><?php echo $emp_event_count['active_month']; ?></td>
                </tr>
                <tr>
                <td><a href="<?php echo mdjm_get_admin_page( 'enquiries' ) . '&mdjm_filter_type&mdjm_filter_date=' . 
				date( 'Ym' ); ?>">Outstanding Enquiries:</a></td>
                <td><?php echo $emp_event_count['enquiry_month'] + $emp_event_count['unattended_month'] . 
				( !empty( $emp_event_count['unattended_month'] ) && $emp_event_count['unattended_month'] > 0 ? 
				' (<a href="' . mdjm_get_admin_page( 'unattended' ) . '&mdjm_filter_date=' . date( 'Ym' ) . '">' . 
				$emp_event_count['unattended_month'] . ' Unattended)</a>' : '' ); ?></td>
                </tr>
                <tr>
                <td>Lost Enquiries:</td>
                <td><?php echo $emp_event_count['lost_month']; ?></td>
                </tr>
                <tr>
                <td>Completed Bookings:</td>
                <td><?php echo $emp_event_count['completed_month']; ?></td>
                </tr>
                <tr>
                <td>Potential Earnings:</td>
                <td><?php echo $mdjm_dash->period_earnings( 'month', '', false ); ?></td>
                </tr>
                <tr>
                <td>Earnings so Far:</td>
                <td><?php echo $mdjm_dash->period_earnings( 'month', '', true ); ?></td>
                </tr>
                <tr>
                <td colspan="2" class="alternate"><strong>Annual Employer Overview for <?php echo date( 'Y' ); ?></strong></td>
                </tr>
                <tr>
                <td><a href="<?php echo mdjm_get_admin_page( 'enquiries' ) . '&mdjm_filter_type&mdjm_filter_date=' . 
				date( 'Ym' ); ?>">Outstanding Enquiries:</a></td>
                <td><?php echo $emp_event_count['enquiry_year'] + $emp_event_count['unattended_year'] . 
				( !empty( $emp_event_count['unattended_year'] ) && $emp_event_count['unattended_year'] > 0 ? 
				' (<a href="' . mdjm_get_admin_page( 'unattended' ) . '&mdjm_filter_date=' . date( 'Ym' ) . '">' . 
				$emp_event_count['unattended_year'] . ' Unattended)</a>' : '' ); ?>
                </tr>
                <tr>
                <td>Lost Enquiries:</td>
                <td><?php echo $emp_event_count['lost_year']; ?></td>
                </tr>
                <tr>
                <td>Completed Bookings:</td>
                <td><?php echo $emp_event_count['completed_year']; ?></td>
                </tr>
                <tr>
                <td>Potential Earnings:</td>
                <td><?php echo $mdjm_dash->period_earnings( 'year', '', false ); ?></td>
                </tr>
                <tr>
                <td>Earnings so Far:</td>
                <td><?php echo $mdjm_dash->period_earnings( 'year', '', true ); ?></td>
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