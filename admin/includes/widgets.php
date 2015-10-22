<?php
	function f_mdjm_add_wp_dashboard_widgets() {
		wp_add_dashboard_widget( 'mdjm-widget-overview', 'Mobile DJ Manager Overview', 'f_mdjm_dash_overview' );
		wp_add_dashboard_widget( 'mdjm-availability-overview', 'Mobile DJ Manager Availability', 'f_mdjm_dash_availability' );	
	}

/*
* f_mdjm_dash_overview
* 04/10/2014
* @since 0.8
* Displays the MDJM Overview Widget on the main WP Dashboard
*/
	function f_mdjm_dash_overview() {
		global $mdjm, $mdjm_settings;
		$next_event = $mdjm->mdjm_events->next_event( '', 'dj' );
		if( !empty( $next_event ) )
			$event_types = get_the_terms( $next_event[0]->ID, 'event-types' );
						
		$bookings_today = $mdjm->mdjm_events->employee_bookings();
		?>
		<table width="100%" border="0" cellspacing="0" cellpadding="0">
         <tr>
            <th width="40%" align="left">Today's Status:</th>
            <td width="60%">
			<?php
			echo ( !empty( $next_event ) && date( 'd-m-Y', strtotime( get_post_meta( $next_event[0]->ID, '_mdjm_event_date', true ) ) ) == date( 'd-m-Y' ) 
				? '<a href="' . admin_url( 'post.php?post=' . $next_event[0]->ID . '&action=edit' ) . '">Booked from ' . 
					date( MDJM_TIME_FORMAT, strtotime( get_post_meta( $next_event[0]->ID, '_mdjm_event_start', true ) ) ) . '</a>'
					 
				: 'Available' );
			?>
            </td>
          </tr>
          <tr>
            <th width="40%" align="left">Next Event:</th>
            <td width="60%">
            <?php
			if( !empty( $next_event ) )	{
				$eventinfo = $mdjm->mdjm_events->event_detail( $next_event[0]->ID );
            	
				echo '<a href="' . get_edit_post_link( $next_event[0]->ID ) . '">' . 
					date( 'd M Y', $eventinfo['date'] ) . '</a> (' . $eventinfo['type'] . ')';
			}
			else
				echo 'No event scheduled';
			?>
            </td>
          </tr>
          <?php if( current_user_can( 'administrator' ) || dj_can( 'view_enquiry' ) )	{
			  ?>
              <tr>
                <th align="left">Outstanding Enquiries:</th>
                <td>
				<?php
						$e = $mdjm->mdjm_events->mdjm_count_event_status( 'mdjm-enquiry' );
						$ue = $mdjm->mdjm_events->mdjm_count_event_status( 'mdjm-unattended' );
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
				$dj_event_results = $mdjm->mdjm_events->employee_bookings();
				if( $dj_event_results )	{
					foreach( $dj_event_results as $info )	{
						$djinfo = get_userdata( $info->event_dj );
						echo '<li>' . $djinfo->first_name . ' is working from ' . date( 'H:i', strtotime( $info->event_start ) ) . ' (<a href="' . admin_url() . 'admin.php?page=mdjm-events&action=view_event_form&event_id=' . $info->event_id . '">view details</a>)</li>';
					}
				}
			}
		?>
            <li><?php if( current_user_can( 'administrator' ) || dj_can( 'add_event' ) ) { ?><a href="<?php echo admin_url( 'post-new.php?post_type=' . MDJM_EVENT_POSTS ); ?>">Add New Event</a> | <?php } ?><a href="<?php echo admin_url( 'admin.php?page=mdjm-dashboard' ); ?>">View Dashboard</a> | <a href="<?php echo admin_url( 'admin.php?page=mdjm-settings' ); ?>">Edit Settings</a>
            
            </li>
        </ul>
        </div>
        <div class="alternate">
        <?php wp_widget_rss_output( 'http://www.mdjm.co.uk/category/news/feed/rss2/', $args = array( 'show_author' => 0, 'show_date' => 1, 'show_summary' => 1, 'items' => 1 ) ); ?>
        </div>
       	<?php
	} // f_mdjm_dash_overview
	
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
		
		?>
        <script type="text/javascript">
		<?php
		mdjm_jquery_datepicker_script( array( 'check_custom_date', 'check_date' ) );
		?>
		
        </script>
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
?>