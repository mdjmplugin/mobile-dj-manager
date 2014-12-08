<?php
	function f_mdjm_add_wp_dashboard_widgets() {
		wp_add_dashboard_widget( 'mdjm-widget-overview', 'Mobile DJ Manager', 'f_mdjm_dash_overview' );	
	}

	function f_mdjm_dash_overview() {
		global $mdjm_options;
		$events = f_mdjm_dj_get_events( get_current_user_id() );
		$dash_dj = f_mdjm_dashboard_dj_overview();
		?>
		<table width="100%" border="0" cellspacing="0" cellpadding="0">
         <tr>
            <th width="40%" align="left">Today's Status:</tg>
            <td width="60%"><?php if( $events['next_event'] == date( "jS F Y" ) ) echo 'Booked from ' . $events['next_event_start'] . ' (<a href="' . admin_url() . 'admin.php?page=mdjm-events&action=view_event_form&event_id=' . $events['event_id'] . '">view details</a>)'; else echo 'Available'; ?></td>
          </tr>
          <tr>
            <th width="40%" align="left">Next Event:</tg>
            <td width="60%"><a href="<?php echo admin_url() . 'admin.php?page=mdjm-events&action=view_event_form&event_id=' . $events['event_id']; ?>"><?php echo $events['next_event']; ?></a> (<?php echo $events['event_type']; ?>)</td>
          </tr>
          <?php if( current_user_can( 'administrator' ) || dj_can( 'view_enquiry' ) )	{
			  ?>
              <tr>
                <th align="left">Outstanding Enquiries:</th>
                <td><?php echo $dash_dj['year_enquiries']; ?> <a href="<?php echo admin_url() . 'admin.php?page=mdjm-events&display=enquiries'; ?>">(view)</a></td>
              </tr>
              <?php
		  }
		  ?>
     	</table>
        <div>
        <ul>
        <?php
			if( current_user_can( 'administrator' ) && isset( $mdjm_options['multiple_dj'] ) )	{
				$dj_event_results = f_mdjm_dj_working_today();
				foreach( $dj_event_results as $info )	{
					$djinfo = get_userdata( $info->event_dj );
					echo '<li>' . $djinfo->first_name . ' is working from ' . date( 'H:i', strtotime( $info->event_start ) ) . ' (<a href="' . admin_url() . 'admin.php?page=mdjm-events&action=view_event_form&event_id=' . $info->event_id . '">view details</a>)</li>';
				}
			}
		?>
            <li><?php if( current_user_can( 'administrator' ) || dj_can( 'add_event' ) ) { ?><a href="<?php echo admin_url(); ?>admin.php?page=mdjm-events&action=add_event_form">Add New Event</a> | <?php } ?><a href="<?php echo admin_url(); ?>admin.php?page=mdjm-dashboard">View Dashboard</a> | <a href="<?php echo admin_url(); ?>admin.php?page=mdjm-settings">Edit Settings</a>
            <?php
			if( !do_reg_check( 'check' ) && current_user_can( 'manage_options' ) )	{
                 echo '| <strong><a style="color:#F90" href="http://www.mydjplanner.co.uk" target="_blank">Buy License</a></strong>';
			}
			?>
            
            </li>
        </ul>
        </div>
        <div class="alternate">
        <?php wp_widget_rss_output( 'http://www.mdjm.co.uk/category/news/feed/rss2/', $args = array( 'show_author' => 0, 'show_date' => 1, 'show_summary' => 1, 'items' => 1 ) ); ?>
        </div>
       	<?php
	}
?>