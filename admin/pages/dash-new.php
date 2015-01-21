<?php
/*
* dash.php
* 19/01/2015
* @since 1.0
* Displays the main MDJM Dahboard
*/
		global $mdjm_options, $current_user;
		
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
		<div id="fb-root"></div>
		<script>(function(d, s, id) {
			  var js, fjs = d.getElementsByTagName(s)[0];
			  if (d.getElementById(id)) return;
			  js = d.createElement(s); js.id = id;
			  js.src = "//connect.facebook.net/en_GB/sdk.js#xfbml=1&appId=832846726735750&version=v2.0";
			  fjs.parentNode.insertBefore(js, fjs);
        }	(document, 'script', 'facebook-jssdk'));
        </script>
        <div class="wrap">
        <h2>Mobile DJ Manager - <?php echo $current_user->display_name; ?> (<?php if( !current_user_can( 'manage_options' ) ) echo 'DJ'; else echo 'Admin'; ?>)</h2>
        <hr />
<?php 
/* Main table*/
/* Determine arguments for event count monthly stats */
	$event_status = array( 'Approved', 'Pending', 'Enquiry', 'Unattended', 'Completed', 'Failed Enquiry' );
	$mdjm_args = array(
					'print' => true,
					'scope' => 'month'
					);
	if( is_dj() )	{
		$mdjm_args['dj'] = $current_user->ID;	
	}
?>
<table class="widefat" width="100%" border="0" cellspacing="0" cellpadding="0">
  <tr>
    <td width="70%"><table class="widefat" width="100%" border="0" cellspacing="0" cellpadding="0">
  <tr>
    <td colspan="6" class="alternate"><strong><?php echo $mdjm_options['company_name']; ?> Overview for <font color="#F90"><?php echo date ( 'F Y' ); ?></font></strong></td>
    </tr>
  <tr>
<!-- Start Monthly boxed cells -->
<?php
	foreach( $event_status as $status )	{
	?>
    <td width="16%"><table class="widefat" width="100%" border="0" cellspacing="0" cellpadding="0">
      <tr class="alternate">
        <td align="center"><font style="font-weight:bold; font-size:12px"><?php echo $status; ?></font></td>
      </tr>
      <tr class="alternate">
        <td height="50" align="center" valign="middle"><font style="font-weight:bold; font-size:36px; color:#F90;"><?php f_mdjm_event_count( $status, $mdjm_args ); ?></font></td>
      </tr>
    </table></td>
    <?php	
	}
?>
<!-- End Monthly boxed cells -->
  </tr>
  
</table>
</td>
    <td width="30%" rowspan="2" valign="top"><table width="100%" border="0" cellspacing="0" cellpadding="0">
  <tr>
    <td class="alternate" colspan="2">January</td>
    </tr>
  <tr>
    <td>Today's Status</td>
    <td>Available</td>
  </tr>
</table>&nbsp;</td>
  </tr>
  <tr>
<!-- Start Annual boxed cells -->
    <td><table class="widefat" width="100%" border="0" cellspacing="0" cellpadding="0">
      <tr>
        <td colspan="6" class="alternate"><strong><?php echo $mdjm_options['company_name']; ?> Overview for <font color="#F90"><?php echo date ( 'Y' ); ?></font></strong></td>
        </tr>
      <tr>
<?php
/* Adjust arguments for event count annual stats */
	$mdjm_args['scope'] = 'year';
	foreach( $event_status as $status )	{
	?>
    <td width="16%"><table class="widefat" width="100%" border="0" cellspacing="0" cellpadding="0">
      <tr class="alternate">
        <td align="center"><font style="font-weight:bold; font-size:12px"><?php echo $status; ?></font></td>
      </tr>
      <tr class="alternate">
        <td height="50" align="center" valign="middle"><font style="font-weight:bold; font-size:36px; color:#F90;"><?php f_mdjm_event_count( $status, $mdjm_args ); ?></font></td>
      </tr>
    </table></td>
    <?php	
	}
?>
<!-- End Annual boxed cells -->
</table></td>
  </tr>
  <tr>
<!-- Start Lifetime boxed cells -->
    <td><table class="widefat" width="100%" border="0" cellspacing="0" cellpadding="0">
      <tr>
        <td colspan="6" class="alternate"><strong><?php echo $mdjm_options['company_name']; ?> <font color="#F90">Lifetime</font> Overview</strong></td>
        </tr>
      <tr>
<?php
/* Adjust arguments for event count annual stats */
	unset( $mdjm_args['scope'] );
	foreach( $event_status as $status )	{
	?>
    <td width="16%"><table class="widefat" width="100%" border="0" cellspacing="0" cellpadding="0">
      <tr class="alternate">
        <td align="center"><font style="font-weight:bold; font-size:12px"><?php echo $status; ?></font></td>
      </tr>
      <tr class="alternate">
        <td height="50" align="center" valign="middle"><font style="font-weight:bold; font-size:36px; color:#F90;"><?php f_mdjm_event_count( $status, $mdjm_args ); ?></font></td>
      </tr>
    </table></td>
    <?php	
	}
?>
<!-- End Lifetime boxed cells -->  
</table>
</div>
<?php /* End main table */ ?>
