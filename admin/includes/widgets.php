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
                <td><?php echo $dash_dj['year_enquiries']; ?> <a href="<?php echo admin_url() . 'admin.php?page=mdjm-events&status=Enquiry'; ?>">(view)</a></td>
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
	} // f_mdjm_dash_overview
	
/*
* f_mdjm_dash_availability
* 07/01/2015
* @since 0.9.9.6
* Displays the MDJM AVailability Status on the main WP Dashboard
*/
	function f_mdjm_dash_availability()	{
		global $mdjm_options;
		
		/* Enqueue the jQuery Datepicker Scripts */
		wp_enqueue_script('jquery-ui-datepicker');
		wp_enqueue_style('jquery-ui-css', 'http://ajax.googleapis.com/ajax/libs/jqueryui/1.8.2/themes/smoothness/jquery-ui.css');
		
		?>
        <script type="text/javascript">
		jQuery(document).ready(function($) {
			$('.check_custom_date').datepicker({
			dateFormat : '<?php f_mdjm_short_date_jquery(); ?>',
			altField  : '#check_date',
			altFormat : 'yy-mm-dd',
			firstDay: <?php echo get_option( 'start_of_week' ); ?>
			});
		});
        </script>
        <table width="100%" border="0" cellspacing="0" cellpadding="0">
        <?php /* Display Availability Overview */ ?>
		<?php get_availability_activity( 0, 0 ); ?>
        
        <?php /* Availability Check */ ?>
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