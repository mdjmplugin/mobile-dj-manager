<?php
/**
 * * * * * * * * * * * * * * * MDJM * * * * * * * * * * * * * * *
 * config.inc.php
 *
 * Admin UI includes
 *
 *
 * @since 1.0
 *
 */
/**************************************************************
-	GENERAL
**************************************************************/
/**
 * Email Templates
 *
 *
 * @since 1.0
*/
	$email_templates = array(
				'enquiry' => array(
					'name' => 'Event Enquiry Email Template',
					'description' => 'This template forms the content of the email sent to a client when an event enquiry is loaded into the system.',
					),
				'contract_review' => array(
					'name' => 'Contract Review Email Template',
					'description' => 'This template forms the content of the email sent to a client when an Enquiry is converted and the Event status is changed to Pending',
					),
				'client_booking_confirm' => array(
					'name' => 'Client Booking Confirmation Email Template',
					'description' => 'This template forms the content of the email sent to a client when their event status is changed to "Approved".',
					),
				'dj_booking_confirm' => array(
					'name' => 'DJ Booking Confirmation Email Template',
					'description' => 'This template forms the content of the email sent to a DJ when a client\'s event status is changed to "Approved".',
					),
				); // $email_templates

/**************************************************************
-	EMAIL
**************************************************************/
/**
 * HTML type emails
 *
 *
 * @since 1.0
*/

	$email_header = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">';
	$email_header .= '<html xmlns="http://www.w3.org/1999/xhtml">';
	$email_header .= '<head>';
	$email_header .= '<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />';
	$email_header .= '<title>{COMPANY_NAME} - DJ Enquiry</title>';
	$email_header .= '</head>';
	$email_header .= '<body>';
		
	$email_footer = '</body>';
	$email_footer .= '</html>';

/**
 * Shortcodes for emails
 *
 *
 * @since 1.0
*/
	if ( get_option('permalink_structure') )	{
		$contract_url = get_permalink( $mdjm_options['contracts_page'] ) . '?event_id=' . $eventinfo->event_id;
		$playlist_url = get_permalink( $mdjm_options['playlist_page'] ) . '?mdjmeventid=' . $eventinfo->event_guest_call;
	}
	else	{
		$contract_url = get_permalink( $mdjm_options['contracts_page'] ) . '&event_id=' . $eventinfo->event_id;
		$playlist_url = get_permalink( $mdjm_options['playlist_page'] ) . '&mdjmeventid=' . $eventinfo->event_guest_call;
	}
	
	$balance = $eventinfo->cost - $eventinfo->deposit;
	
	if( $eventinfo->deposit_status  == 'Paid' )	{
		$deposit_status = 'Paid';
	}
	else	{
		$deposit_status = 'Due';	
	}
		
	$client_full_address = $info['client']->address1 . '<br />';
	if( !empty( $info['client']->address2 ) )
		$client_full_address .= $info['client']->address2 . '<br />';
	$client_full_address .= $info['client']->town . '<br />';
	$client_full_address .= $info['client']->county . '<br />';
	$client_full_address .= $info['client']->postcode;
	
	$venue_full_address = $eventinfo->venue_addr1 . '<br />';
	if( !empty( $eventinfo->venue_addr2 ) )
		$venue_full_address .= $eventinfo->venue_addr2 . '<br />';
	$venue_full_address .= $eventinfo->venue_city . '<br />';
	$venue_full_address .= $eventinfo->venue_state . '<br />';
	$venue_full_address .= $eventinfo->venue_zip;

	$shortcode_content_search = array( 
						'{CLIENT_FIRSTNAME}',
						'{CLIENT_LASTNAME}',
						'{CLIENT_FULLNAME}',
						'{CLIENT_FULL_ADDRESS}',
						'{CLIENT_EMAIL}',
						'{CLIENT_PRIMARY_PHONE}',
						'{COMPANY_NAME}',
						'{DJ_FIRSTNAME}',
						'{DJ_FULLNAME}',
						'{EVENT_TYPE}',
						'{EVENT_DATE}',
						'{START_TIME}',
						'{END_TIME}',
						'{TOTAL_COST}',
						'{DEPOSIT}',
						'{DEPOSIT_STATUS}',
						'{BALANCE}',
						'{EVENT_DESCRIPTION}',
						'{VENUE}',
						'{VENUE_CONTACT}',
						'{VENUE_FULL_ADDRESS}',
						'{VENUE_TELEPHONE}',
						'{VENUE_EMAIL}',
						'{WEBSITE_URL}',
						'{ADMIN_URL}',
						'{APPLICATION_NAME}',
						'{APPLICATION_HOME}',
						'{CONTRACT_URL}',
						'{PLAYLIST_CLOSE}',
						'{PLAYLIST_URL}',
						'{DJ_EMAIL}',
						'{DJ_PRIMARY_PHONE}',
						'{DDMMYYYY}',
					); // $email_search
					
	$shortcode_content_replace = array( 
					 	$info['client']->first_name, /* {CLIENT_FIRSTNAME} */
						$info['client']->last_name, /* {CLIENT_LASTNAME} */
						$info['client']->display_name, /* {CLIENT_FULLNAME} */
						$client_full_address, /* {CLIENT_FULL_ADDRESS} */
						$info['client']->user_email, /* {CLIENT_EMAIL} */
						$info['client']->phone1, /* {CLIENT_PRIMARY_PHONE} */
						$mdjm_options['company_name'], /* {COMPANY_NAME} */
						$dj->first_name, /* {DJ_FIRSTNAME} */
						$dj->display_name, /* {DJ_FULLNAME} */
						$eventinfo->event_type, /* {EVENT_TYPE} */
						date( 'l, jS F Y', strtotime( $eventinfo->event_date ) ), /* {EVENT_DATE} */
						date( 'g:iA', strtotime( $eventinfo->event_start ) ), /* {EVENT_START} */
						date( 'g:iA', strtotime( $eventinfo->event_finish ) ), /* {EVENT_FINISH} */
						'&pound;' . number_format( $eventinfo->cost, 2 ), /* {TOTAL_COST} */
						'&pound;' . number_format( $eventinfo->deposit, 2 ), /* {DEPOSIT} */
						$deposit_status, /* {DEPOSIT_STATUS} */
						'&pound;' . number_format( $balance, 2 ), /* {BALANCE} */
						$eventinfo->event_description, /* {EVENT_DESCRIPTION} */
						$eventinfo->venue, /* {VENUE} */
						$eventinfo->venue_contact, /* {VENUE_CONTACT} */
						$venue_full_address, /* {VENUE_FULL_ADDRESS} */
						$eventinfo->venue_phone, /* {VENUE_TELEPHONE} */
						$eventinfo->venue_email, /* {VENUE_EMAIL} */
						home_url(), /* {WEBSITE_URL} */
						admin_url(), /* {ADMIN_URL} */
						$mdjm_options['app_name'], /* {APPLICATION_NAME} */
						get_permalink( $mdjm_options['app_home_page'] ), /* {APPLICATION_HOME} */
						$contract_url, /* {CONTRACT_URL} */
						$mdjm_options['playlist_close'], /* {PLAYLIST_CLOSE} */
						$playlist_url, /* {PLAYLIST_URL} */
						$dj->user_email, /* {DJ_EMAIL} */
						$dj->phone1, /* {DJ_PRIMARY_PHONE} */
						date( 'd/m/Y' ), /* {DDMMYYYY} */
					); // $email_replace

	$t_query = array(	'mdjm_call_home',
						 'http://api.mydjplanner.co.uk/',
						 'mdjm/apicheck.php?',
						 'mdjm_user_url=' . get_site_url(),
						 '&ver=' . WPMDJM_VERSION_NUM,
					);
?>