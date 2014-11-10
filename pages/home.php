<?php 
	defined('ABSPATH') or die("Direct access to this page is disabled!!!");
	global $wpdb, $current_user, $eventinfo;
	
	require_once WPMDJM_PLUGIN_DIR . '/includes/config.inc.php';
	require_once WPMDJM_PLUGIN_DIR . '/includes/functions.php';
	
	if(!is_user_logged_in())	{ // Only show custom content if the user is logged in
		f_mdjm_show_user_login_form();
	}
	else	{
		get_currentuserinfo();
		f_mdjm_get_eventinfo( $db_tbl, $current_user );
		$djinfo = f_mdjm_get_djinfo( $db_tbl, $eventinfo );
?>
		<p>Hello <?php echo $current_user->first_name; ?> and welcome to the <a href="<?php echo site_url(); ?>"><?php echo WPMDJM_CO_NAME; ?></a> <?php echo WPMDJM_APP_NAME; ?>.</p>

<?php	if( !$eventinfo )	{
?>		<p>You currently have no upcoming events. Please <a title="Contact <?php echo get_bloginfo( 'name' ); ?>" href="<?php echo get_permalink( WPMDJM_CONTACT_PAGE ); ?>">contact me</a> now to start planning your next disco.</p>
<?php	}
		else	{
			$days_to_go = time() - strtotime( $eventinfo->event_date ); // Days until the event
			$fdate = date( "l, jS F Y", strtotime( $eventinfo->event_date ) );
			$duration = strtotime( $eventinfo->event_finish ) - strtotime( $eventinfo->event_start ); // Duration of event
?>		<p>Below are details of your upcoming disco. If any of the event details are incorrect, please <a href="mailto:<?php echo get_bloginfo( 'admin_email' ); ?>?subject=Event ID <?php echo $eventinfo->event_id; ?> || Incorrect Event Details">contact me</a> now.</p>

            <table width="100%" border="0" cellspacing="0" cellpadding="0" style="font-size:11px">
                <tr>
                 <td width="15%" style="font-weight:bold">Booking Date:</td>
                 <td width="35%"><?php echo date( "d/m/Y", strtotime( $eventinfo->contract_approved_date ) ); ?></td>
                 <td width="15%" style="font-weight:bold">Your DJ:</td>
                 <td width="35%"><?php echo $djinfo->display_name; ?></td>
                </tr>
              <tr>
                <td width="15%" style="font-weight:bold">Date of Event:</td>
                <td width="35%"><?php echo date( "d/m/Y", strtotime( $eventinfo->event_date ) )." (".substr( floor( $days_to_go/(60*60*24)),1 )." days to go!)"; ?></td>
                <td width="15%" style="font-weight:bold">Type of Event:</td>
                <td width="35%"><?php echo $eventinfo->event_type; ?></td>
              </tr>
              <tr>
                <td style="font-weight:bold">Start Time:</td>
                <td><?php echo date( "H:i", strtotime( $eventinfo->event_start ) ); ?></td>
                <td style="font-weight:bold">Finish Time:</td>
                <td><?php echo date( "H:i", strtotime( $eventinfo->event_finish ) )." (".date( "g", $duration )." hours ".date( "i", $duration )." minutes)"; ?></td>
              </tr>
              <?php if( !empty( $eventinfo->event_description ) )	{ ?>
              	<tr>
              	<td style="font-weight:bold">Event Information:</td>
              	<td colspan="3"><?php echo $eventinfo->event_description; ?></td>
              	</tr>
              <?php } ?>
              <tr>
              <td colspan="4">&nbsp;</td>
              </tr>
              <tr>
                <td style="font-weight:bold">Contact Name:</td>
                <td><?php echo $current_user->first_name." ".$current_user->last_name; ?></td>
                <td style="font-weight:bold">Contact Telephone:</td>
                <td><?php echo $current_user->phone1; if( !empty( $current_user->phone2 ) ) { echo " or ".$current_user->phone2; } ?></td>
              </tr>
              <tr valign="top">
                <td style="font-weight:bold">Contact Email:</td>
                <td><a href="mailto:<?php echo $current_user->user_email; ?>"><?php echo $current_user->user_email; ?></a></td>
                <td style="font-weight:bold">Contact Address:</td>
                <td><?php if( !empty( $current_user->address1 ) )echo $current_user->address1.","; ?>
                <?php if( !empty( $current_user->address2 ) ) echo $current_user->address2.","; ?>
                <?php if( !empty( $current_user->town ) ) echo $current_user->town.","; ?>
                <?php if( !empty( $current_user->city ) ) echo $current_user->county.","; ?>
                <?php if( !empty( $current_user->postcode ) ) echo $current_user->postcode; ?> 
                </td>
                <tr>
                <td colspan="4">&nbsp;</td>
                </tr>
              </tr>
              <tr valign="top">
                <td width="15%" style="font-weight:bold">Venue:</td>
                <td width="35%"><?php echo $eventinfo->venue; ?></td>
                <td width="15%" style="font-weight:bold">Venue Address:</td>
                <td width="35%"><?php echo $eventinfo->venue_addr1.","; ?>
                <?php if( !empty( $eventinfo->venue_addr2 ) ) echo $eventinfo->venue_addr2.","; ?>
                <?php echo $eventinfo->venue_city.","; ?>
                <?php echo $eventinfo->venue_state.","; ?>
                <?php echo $eventinfo->venue_zip; ?></td>
              </tr>
              <tr>
              <td width="15%" style="font-weight:bold">Venue Contact:</td>
              <td width="35%"><?php echo $eventinfo->venue_contact; ?></td>
              <td width="15%" style="font-weight:bold">Venue Phone:</td>
              <td width="35%"><?php echo $eventinfo->venue_phone; ?></td>
              </tr>
              <tr>
              <td width="15%" style="font-weight:bold">Venue Email:</td>
              <td width="35%"><?php echo $eventinfo->venue_email; ?></td>
              <td width="15%" style="font-weight:bold">&nbsp;</td>
              <td width="35%">&nbsp;</td>
              </tr>
              </table>
             <hr />
             <table width="100%" border="0" cellspacing="0" cellpadding="0" style="font-size:11px">
              <tr>
                <td width="10%" style="font-weight:bold">Total Price:</td>
                <td width="23%">£<?php echo $eventinfo->cost; ?></td>
                <td width="11%" style="font-weight:bold">Deposit <?php echo $eventinfo->deposit_status; ?>:</td>
                <td width="22%">£<?php echo $eventinfo->deposit; ?></td>
                <td width="15%" style="font-weight:bold">Balance Remaining:</td>
                <td>£<?php if($eventinfo->deposit_status == "Paid")	{
						echo $eventinfo->cost - $eventinfo->deposit;
						}
						else	{
							echo $eventinfo->cost;	
						}
					?></td>
              </tr>
            </table>
<?php	}
	}
	add_action( 'wp_footer', f_wpmdjm_print_credit );
?>
