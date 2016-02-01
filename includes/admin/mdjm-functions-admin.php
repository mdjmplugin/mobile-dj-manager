<?php
/**
 * Contains functions that can only be used within the admin UI
 *
 *
 *
 */
 
/**
 * Check DJ Availability for given date when an event is in unattended status
 * 
 * @params	str		$date	Optional: The date to check (Y-m-d). Default today's date
 * 			int		$dj		Optional: The user ID of the employee to check. Default None
 * 
 * @return	Admin notice with availability report
 */
function mdjm_availability_check( $date='', $dj='' )	{	
	$date = !empty( $date ) ? $date : date( 'Y-m-d' );
	
	// Run the availability check with the correct params depending on what we've been passed
	$dj_avail = ( is_dj() ) ? dj_available( $dj, '', $date ) : dj_available( '', '', $date );
	
	// Print the availability result
	if( isset( $dj_avail ) )	{
		MDJM()->debug->log_it( 'DJ Availability check returns availability for ' . $date );
		/* Check all DJ's */
		if ( !empty( $dj_avail['available'] ) && current_user_can( 'administrator' ) )	{
			$avail_message = count( $dj_avail['available'] ) . ' ' . _n( MDJM_DJ, MDJM_DJ . '\'s', count( $dj_avail['available'] ) ) . ' available on ' . date( 'l, jS F Y', strtotime( $date ) );
		$class = 'updated';
			?><ui><?php
			foreach( $dj_avail['available'] as $dj_detail )	{
				$dj = get_userdata( $dj_detail );
				$avail_message .= '<li>' . $dj->display_name . 
				'<a href="' . get_edit_post_link( $_GET['e_id'] ) . '&dj=' . $dj->ID . 
				'"> Assign &amp; Respond to Enquiry</a><br /></li>';
			}
			?></ui><?php
		}
		// Single DJ Check
		elseif ( !empty( $dj_avail['available'] ) && !current_user_can( 'administrator' ) )	{
			$dj = get_userdata( get_current_user_id() );
			$class = 'updated';
			$avail_message = $dj->display_name . ' is available on ' . date( 'l, jS F Y', strtotime( $date ) ) . '<a href="' . admin_url( 'admin.php?page=mdjm-events&action=add_event_form&event_id=' . $_GET['e_id'] . '&dj=' . $dj->ID ) . '"> Assign &amp; Respond to Enquiry</a><br />';
		}
		else	{
			$class = 'error';
			if( current_user_can( 'administrator' ) )	{
				$avail_message = 'No ' . MDJM_DJ . '\'s available on ' . date( 'l, jS F Y', strtotime( $date ) );
			}
			else	{
				$dj = get_userdata( get_current_user_id() );
				$avail_message = $dj->display_name . ' is not available on ' . date( 'l, jS F Y', strtotime( $date ) );
			}
		}
		mdjm_update_notice( $class, $avail_message );
	}
	else	{
		MDJM()->debug->log_it( 'DJ Availability check returns no availability for ' . $date );
	}
} // mdjm_availability_check
?>