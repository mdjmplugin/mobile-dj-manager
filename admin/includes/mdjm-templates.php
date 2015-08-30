<?php
/*
* mdjm-templates.php
* 13/11/2014
* Since 0.8.0
* Contains information required during installation procedure
* including default contract template
*/

/*
* Default (General) Contract Content *
* Only used during first installation
*/
	$contract_template_content = '<h2 style="text-align: center;"><span style="text-decoration: underline;">Confirmation of Booking</span></h2><h3>Agreement Date: <span style="color: #ff0000;">{DDMMYYYY}</span></h3>This document sets out the terms and conditions verbally agreed by both parties and any non-fulfilment of the schedule below may render the defaulting party liable to damages.This agreement is between: <strong>{COMPANY_NAME}</strong> (hereinafter called the Artiste)and:<strong>{CLIENT_FULLNAME}</strong> (hereinafter called the Employer)<strong>of</strong><address><strong>{CLIENT_FULL_ADDRESS}{CLIENT_EMAIL}{CLIENT_PRIMARY_PHONE}</strong> </address><address> </address><address>in compliance with the schedule set out below.</address><h3 style="text-align: center;"><span style="text-decoration: underline;">Schedule</span></h3>It is agreed that the Artiste shall appear for the performance set out below for a total inclusive fee of <span style="color: #ff0000;"><strong>{TOTAL_COST}</strong></span>.Payment terms are: <strong><span style="color: #ff0000;">{DEPOSIT}</span> Deposit</strong> to be returned together with this form followed by <strong>CASH ON COMPLETION</strong> for the remaining balance of <strong><span style="color: #ff0000;">{BALANCE}</span>. </strong>Cheques will only be accepted by prior arrangement.Deposits can be made via bank transfer to the following account or via cheque made payable to <strong>XXXXXX</strong> and sent to the address at the top of this form.<strong>Bank Transfer Details: Name XXXXXX | Acct No. 10000000 | Sort Code | 30-00-00</strong><strong>The confirmation of this booking is secured upon receipt of the signed contract and any stated deposit amount</strong>.<h3 style="text-align: center;"><span style="text-decoration: underline;">Venue and Event</span></h3><table border="0" width="100%" cellspacing="0" cellpadding="0"><tbody><tr><td align="center"><table border="0" width="75%" cellspacing="0" cellpadding="0"><tbody><tr><td style="border-bottom-width: thin; border-bottom-style: solid; border-bottom-color: #000; border-right-width: thin; border-right-style: solid; border-right-color: #000;" width="33%"><strong>Address</strong></td><td style="border-bottom-width: thin; border-bottom-style: solid; border-bottom-color: #000; border-right-width: thin; border-right-style: solid; border-right-color: #000;" width="33%"><strong>Telephone Number</strong></td><td style="border-bottom-width: thin; border-bottom-style: solid; border-bottom-color: #000;" width="33%"><strong>Date</strong></td></tr><tr><td style="border-right-width: thin; border-right-style: solid; border-right-color: #000;" valign="top" width="33%"><span style="color: #ff0000;"><strong>{VENUE_FULL_ADDRESS}</strong></span></td><td style="border-right-width: thin; border-right-style: solid; border-right-color: #000;" valign="top" width="33%"><span style="color: #ff0000;"><strong>{VENUE_TELEPHONE}</strong></span></td><td valign="top" width="33%"><span style="color: #ff0000;"><strong>{EVENT_DATE}</strong></span></td></tr></tbody></table></td></tr></tbody></table>The Artiste will perform between the times of <span style="color: #ff0000;"><strong>{START_TIME}</strong></span> to <span style="color: #ff0000;"><strong>{END_TIME}</strong></span>. Any additional time will be charged at £50 per hour or part of.<hr /><h2 style="text-align: center;"> Terms &amp; Conditions</h2><ol>	<li>This contract may be cancelled by either party, giving the other not less than 28 days prior notice.</li>	<li>If the Employer cancels the contract in less than 28 days’ notice, the Employer is required to pay full contractual fee, unless a mutual written agreement has been made by the Artiste and Employer.</li>	<li>Deposits are non-refundable, unless cancellation notice is issued by the Artiste or by prior written agreement.</li>	<li>This contract is not transferable to any other persons/pub/club without written permission of the Artiste.</li>	<li>Provided the Employer pays the Artiste his full contractual fee, he may without giving any reason, prohibit the whole or any part of the Artiste performance.</li>	<li>Whilst all safeguards are assured the Artiste cannot be held responsible for any loss or damage, out of the Artiste’s control during any performance whilst on the Employers premises.</li>	<li>The Employer is under obligation to reprimand or if necessary remove any persons being repetitively destructive or abusive to the Artiste or their equipment.</li>	<li>It is the Employer’s obligation to ensure that the venue is available 90 minutes prior to the event start time and 90 minutes from event completion.</li>	<li>The venue must have adequate parking facilities and accessibility for the Artiste and his or her equipment.</li>	<li>The Artiste reserves the right to provide an alternative performer to the employer for the event. Any substitution will be advised in writing at least 7 days before the event date and the performer is guaranteed to be able to provide at least the same level of service as the Artiste.</li>	<li>Failing to acknowledge and confirm this contract 28 days prior to the performance date does not constitute a cancellation, however it may render the confirmation unsafe. If the employer does not acknowledge and confirm the contract within the 28 days, the Artiste is under no obligation to confirm this booking.</li>	<li>From time to time the Artiste, or a member of their crew, may take photographs of the performance. These photographs may include individuals attending the event. If you do not wish for photographs to be taken or used publicly such as on the Artiste’s websites or other advertising media, notify the Artiste in writing.</li></ol>';
	
	$contract_template_args = array(
								'post_title'     => 'General',
								'post_content'   => $contract_template_content,
								'post_status'    => 'publish',
								'post_type'	  => 'contract',
								'post_author'   	=> 1,
								'ping_status'   	=> 'closed',
								'comment_status' => 'closed',
								);
/**** Default Contract end ****/

/*
* Default Email Templates *
* Only used during first installation
*/
	$email_enquiry_content = '<h1>Your DJ Enquiry from {COMPANY_NAME}</h1>
								Dear {CLIENT_FIRSTNAME},<br />
								<br />
								Thank you for contacting {COMPANY_NAME} regarding your up and coming event on {EVENT_DATE}.<br />
								<br />
								I am pleased to tell you that we are available and would love to provide the disco for you.<br />
								<br />
								To provide a disco from {START_TIME} to {END_TIME} our cost would be {TOTAL_COST}. There are no hidden charges.<br />
								<br />
								My standard disco package includes a vast music collection and great lighting. In addition I would stay in regular contact with you to ensure the night goes to plan. I can incorporate your own playlists, a few songs you want played, requests on the night, or remain in full control of the music - this is your decision, but I can be as flexible as required.<br />
								<br />
								Mobile DJs are required to have both PAT and PLI (Portable Appliance Testing and Public Liability Insurance). Confirmation of both can be provided.<br />
								<br />
								If you have any further questions, or would like to go ahead and book, please let me know by return.<br />
								<br />
								I hope to hear from you soon.<br />
								<br />
								Best Regards<br />
								<br />
								{DJ_FULLNAME}<br />
								<br />
								Email: <a href="mailto:{DJ_EMAIL}">{DJ_EMAIL}</a><br />
								Tel: {DJ_PRIMARY_PHONE}<br />
								<a href="{WEBSITE_URL}">{WEBSITE_URL}</a>';
	
	$email_enquiry_content_args = array(
								'post_title'     => 'Client Enquiry',
								'post_content'   => $email_enquiry_content,
								'post_status'   	=> 'publish',
								'post_type'	  => 'email_template',
								'post_author'   	=> 1,
								'ping_status'   	=> 'closed',
								'comment_status' => 'closed',
								);

	$email_contract_review = '<h2>Your DJ Booking with {COMPANY_NAME}</h2>
								Dear {CLIENT_FIRSTNAME},<br />
								<br />
								Thank you for indicating that you wish to proceed with booking {COMPANY_NAME} for your up and coming disco on {EVENT_DATE}.<br />
								<br />
								There are two final tasks to complete before your booking can be confirmed...<br />
								<ul><br />
									<li><strong>Review and accept your contract</strong><br />
								Your contract has now been produced. You can review it by <a href="{CONTRACT_URL}">clicking here</a>. Please review the terms and accept the contract. If you would prefer the contract to be emailed to you, please let me know by return email.</li><br />
									<li><strong>Pay your deposit</strong><br />
								Your deposit of <strong>{DEPOSIT}</strong> is now due. If you have not already done so please make this payment now. Details of how to make this payment are shown within the <a href="{CONTRACT_URL}">contract</a>.</li><br />
								</ul><br />
								Once these actions have been completed you will receive a further email confirming your booking.<br />
								<br />
								Meanwhile if you have any questions, please do not hesitate to get in touch.<br />
								<br />
								Thank you for choosing {COMPANY_NAME}.<br />
								<br />
								Regards<br />
								<br />
								{COMPANY_NAME}<br />
								<a href="{WEBSITE_URL}">{WEBSITE_URL}</a>';
	
	$email_contract_review_args = array(
								'post_title'    => 'Client Contract Review',
								'post_content'  => $email_contract_review,
								'post_status'   => 'publish',
								'post_type'		=> 'email_template',
								'post_author'   => 1,
								'ping_status'   	=> 'closed',
								'comment_status' => 'closed',
								);

	$email_client_booking_confirm = '<h1>Your DJ Booking is Confirmed</h1>
									Dear {CLIENT_FIRSTNAME},<br />
									<br />
									Thank you for booking your up and coming disco with {COMPANY_NAME}. Your booking is now confirmed.<br />
									<br />
									My name is {DJ_FULLNAME} and I will be your DJ on {EVENT_DATE}. Should you wish to contact me at any stage to discuss your disco, my details are at the end of this email.<br />
									<h2>What Now?</h2><br />
									<strong>Music Selection & Playlists</strong><br />
									<br />
									We have an online portal where you can add songs that you would like to ensure we play during your disco. To access this feature, head over to the {COMPANY_NAME} <a href="{APPLICATION_HOME}">{APPLICATION_NAME}</a>. The playlist feature will close {PLAYLIST_CLOSE} days before your event.<br />
									<br />
									You will need to login. Your username and password have already been sent to you in a previous email but if you no longer have this information, click on the lost password link and enter your user name, which is your email address. Instructions on resetting your password will then be sent to you.<br />
									<br />
									You can also invite your guests to add songs to your playlist by providing them with your unique playlist URL - <a href="{PLAYLIST_URL}">{PLAYLIST_URL}</a>. We recommend creating a <a href="https://www.facebook.com/events/">Facebook Events Page</a> and sharing the link on there. Alternatively of course, you can email the URL to your guests.<br />
									<br />
									Don\'t worry though, you have full control over your playlist so you can remove songs added by your guests if you do not like their choices.<br />
									<br />
									<strong>When will you next hear from me?</strong><br />
									<br />
									I generally contact you again approximately 2 weeks before your event to finalise details with you. However, if you have any questions, concerns, or just want a general chat about your disco, feel free to contact me at any time.<br />
									<br />
									Thanks again for choosing {COMPANY_NAME} to provide the DJ & Disco for your event. I look forward to partying with you on {EVENT_DATE}.<br />
									<br />
									Best Regards<br />
									<br />
									{DJ_FULLNAME}<br />
									<br />
									Email: <a href="mailto:{DJ_EMAIL}">{DJ_EMAIL}</a><br />
									Tel: {DJ_PRIMARY_PHONE}<br />
									<a href="{WEBSITE_URL}">{WEBSITE_URL}</a>';
	
	$email_client_booking_confirm_args = array(
								'post_title'     => 'Client Booking Confirmation',
								'post_content'   => $email_client_booking_confirm,
								'post_status'   	=> 'publish',
								'post_type'	  => 'email_template',
								'post_author'   	=> 1,
								'ping_status'   	=> 'closed',
								'comment_status' => 'closed',
								);

	$email_dj_booking_confirm = '<h1>Booking Confirmation</h1>
								Dear {DJ_FIRSTNAME},<br />
								<br />
								Your client {CLIENT_FULLNAME} has just confirmed their booking for you to DJ at their event on {EVENT_DATE}.<br />
								<br />
								A booking confirmation email has been sent to them and they now have your contact details and access to the online {APPLICATION_NAME} tools to create playlist entries etc.<br />
								<br />
								Make sure you login regularly to the <a href="{ADMIN_URL}admin.php?page=mdjm-dashboard">{COMPANY_NAME} {APPLICATION_NAME} admin interface</a> to ensure you have all relevant information relating to their booking.<br />
								<br />
								Remember it is your responsibility to remain in regular contact with your client regarding their event as well as answer any queries or concerns they may have. Customer service is one of our key selling points and after the event, your client will be invited to provide feedback regarding the booking process, communication in the lead up to the event, as well as the event itself.<br />
								<h2>Event Details</h2><br />
								Client Name: {CLIENT_FULLNAME}<br />
								Event Date: {EVENT_DATE}<br />
								Type: {EVENT_TYPE}<br />
								Start Time: {START_TIME}<br />
								Finish Time: {END_TIME}<br />
								Venue: {VENUE}<br />
								Balance Due: {BALANCE}<br />
								<br />
								Further information is available on the <a href="{ADMIN_URL}admin.php?page=mdjm-dashboard">{COMPANY_NAME} {APPLICATION_NAME} admin interface</a>.<br />
								<br />
								Regards<br />
								<br />
								{COMPANY_NAME}';
	
	$email_dj_booking_confirm_args = array(
								'post_title'     => 'DJ Booking Confirmation',
								'post_content'   => $email_dj_booking_confirm,
								'post_status'   	=> 'publish',
								'post_type'	  => 'email_template',
								'post_author'   	=> 1,
								'ping_status'    => 'closed',
								'comment_status' => 'closed',
								);
								
	$email_unavailability_template = '<h1>Your DJ Enquiry with {COMPANY_NAME}</h1>
								Dear {CLIENT_FIRSTNAME},<br />
								<br />
								Thank you for contacting {COMPANY_NAME} regarding your up and coming event on {EVENT_DATE}.<br />
								<br />
								Unfortunately however, we are not available on the date you have selected for your event. ' . 
								'If you have alternative dates you are looking at, we\'d love to hear from you again.<br />
								<br />
								Otherwise, we hope you have a great event and hope to hear from you again next time.<br />
								<br />
								Best Regards<br />
								<br />
								{DJ_FULLNAME}<br />
								<br />
								Email: <a href="mailto:{DJ_EMAIL}">{DJ_EMAIL}</a><br />
								Tel: {DJ_PRIMARY_PHONE}<br />
								<a href="{WEBSITE_URL}">{WEBSITE_URL}</a>';
	
	$email_unavailability_template_args = array(
								'post_title'     => '{COMPANY_NAME} is not Available',
								'post_content'   => $email_unavailability_template,
								'post_status'   	=> 'publish',
								'post_type'	  => 'email_template',
								'post_author'   	=> 1,
								'ping_status'   	=> 'closed',
								'comment_status' => 'closed',
								);
								
	$email_payment_received_template = '<h4><span style="color: #ff9900;">Thank you for your {PAYMENT_FOR} payment</span></h4>
								Dear {CLIENT_FIRSTNAME},<br />
								<br />
								Thank you for your recent payment of <strong>{PAYMENT_AMOUNT}</strong> towards the ' . 
								'<strong>{PAYMENT_FOR}</strong> for you event on <strong>{EVENT_DATE}</strong>. Your payment has been received and your ' . 
								'event details have been updated.<br />
								<br />
								You can view your event details and manage your playlist by logging onto our ' . 
								'<a title="{COMPANY_NAME} {APPLICATION_NAME}" href="{APPLICATION_HOME}">{APPLICATION_NAME}</a> event management system.<br />
								<br />
								Your username is {CLIENT_USERNAME} and if you can\'t recall your password, you can reset it by clicking the ' . 
								'<a title="Reset your password for the {COMPANY_NAME} {APPLICATION_NAME}" href="http://www.djmikehoward.co.uk//wp-login.php?action=lostpassword">' . 
								'Lost Password</a> link.<br />
								<br />
								Best Regards<br />
								<br />
								{DJ_FULLNAME}<br />
								<br />
								Email: <a href="mailto:{DJ_EMAIL}">{DJ_EMAIL}</a><br />
								Tel: {DJ_PRIMARY_PHONE}<br />
								<a href="{WEBSITE_URL}">{WEBSITE_URL}</a>';
	
	$email_payment_received_template_args = array(
								'post_title'     => 'Event {PAYMENT_FOR} Payment Confirmation',
								'post_content'   => $email_payment_received_template,
								'post_status'   	=> 'publish',
								'post_type'	  => 'email_template',
								'post_author'   	=> 1,
								'ping_status'   	=> 'closed',
								'comment_status' => 'closed',
								);
								
		$online_quote_template = '[caption id="" align="alignleft" width="128"]<a href="{WEBSITE_URL}"><img title="{COMPANY_NAME}" src="http://www.mydjplanner.co.uk/wp-content/uploads/2014/10/icon-128x1281.png" alt="{COMPANY_NAME}" width="128" height="128" /></a> {COMPANY_NAME}[/caption]
							<h3>Event Quotation for {CLIENT_FULLNAME}</h3>
							<pre>Prepared by: {DJ_FULLNAME}<br />
							Date: {DDMMYYYY}<br />
							Valid for: 2 weeks from date</pre><br />
							Dear {CLIENT_FIRSTNAME},<br />
							
							It is with pleasure that I am providing you with the following costs for your event on {EVENT_DATE}.<br /><br />
							
							I hope you find our quotation to your satisfaction. If there is anything you would like to discuss in further detail, please contact me on {DJ_PRIMARY_PHONE} or at <a href="mailto: {DJ_EMAIL}">{DJ_EMAIL}</a>.<br />
							<table style="font-size: 11px;">
							<tbody>
							<tr>
							<td>Event Date:</td>
							<td>{EVENT_DATE}</td>
							<td>Event Type:</td>
							<td>{EVENT_TYPE}</td>
							</tr>
							<tr>
							<td>Start Time:</td>
							<td>{START_TIME}</td>
							<td>End Time:</td>
							<td>{END_TIME}</td>
							</tr>
							<tr>
							<td>Selected Package:</td>
							<td>{EVENT_PACKAGE}</td>
							<td>Add-ons:</td>
							<td>{EVENT_ADDONS}</td>
							</tr>
							<tr>
							<td>Venue Details:</td>
							<td colspan="3">{VENUE_FULL_ADDRESS}</td>
							</tr>
							<tr>
							<td colspan="4">
							
							<hr />
							
							</td>
							</tr>
							<tr style="font-weight: bold;">
							<td colspan="2">Event Cost:</td>
							<td colspan="2">{TOTAL_COST}</td>
							</tr>
							<tr style="font-weight: bold;">
							<td colspan="2">Booking Fee:</td>
							<td colspan="2">{DEPOSIT} <span style="font-size: 9px;">(due at time of booking)</span></td>
							</tr>
							</tbody>
							</table>
							<span style="color: #cccccc; font-size: 9px;"><a style="color: #cccccc;" href="#">Click here</a> to view our list of terms and conditions</span>';
	
	$online_quote_template_args = array(
								'post_title'     => 'Default Online Quote',
								'post_content'   => $online_quote_template,
								'post_status'    => 'publish',
								'post_type'		 => 'email_template',
								'post_author'    => 1,
								'ping_status'    => 'closed',
								'comment_status' => 'closed',
								);
/**** Default Email Template end ****/

/*
* Default Schedules
* Only used during first installation
*/
	if( isset( $mdjm_settings['playlist']['upload_playlists'] ) )	{
		$time = time();
		$playlist_nextrun = strtotime( '+1 day', $time );
	}
	else	{
		$playlist_nextrun = 'N/A';
	}
	$mdjm_schedules = array(
			'complete-events'	=> array(
				'slug'		 => 'complete-events',
				'name'	     => 'Complete Events',
				'active'	   => 'Y',
				'desc'	     => 'For events with the Approved status, change to Completed if date of event has now passed',
				'frequency'	=> 'Daily',
				'nextrun'	  => time(),
				'lastran'	  => 'Never',
				'options'	  => array(
									'email_client'   => 'N',
									'email_template' => '0',
									'email_subject'  => 'Task "Complete Events" Complete - Client Zone',
									'email_from'	 => 'admin',
									'run_when'	   	 => 'after_event',
									'age'			 => '1 HOUR',
									'notify_admin'   => 'Y',
									'notify_dj' 	 => 'N',
									),
				'function'	 => 'complete_event',
				'totalruns'	 => '0',
				'default'	 => 'Y',
			), // complete-events
						
			'request-deposit'	=> array(
				'slug'		 => 'request-deposit',
				'name'	     => 'Request Deposit',
				'active'	   => 'N',
				'desc'	     => 'Send reminder email to client requesting deposit payment if event status is Approved and deposit has not been received',
				'frequency'	=> 'Daily',
				'nextrun'	  => 'N/A',
				'lastran'	  => 'Never',
				'options'	  => array(
									'email_client'   => 'Y',
									'email_template' => '0',
									'email_subject'  => 'Task "Request Deposit" Complete - Client Zone',
									'email_from'	 => 'admin',
									'run_when'	   => 'after_approval',
									'age'			=> '3 DAY',
									'notify_admin'   => 'Y',
									'notify_dj' 	  => 'N',
									),
				'function'	 => 'request_deposit',
				'totalruns'	 => '0',
				'default'	 => 'Y',
			), // request-deposit
						
			'balance-reminder'	=> array(
				'slug'		 => 'balance-reminder',
				'name'	     => 'Balance Reminder',
				'active'	   => 'N',
				'desc'	     => 'Send email to client requesting they pay remaining balance for event',
				'frequency'	=> 'Daily',
				'nextrun'	  => 'N/A',
				'lastran'	  => 'Never',
				'options'	  => array(
									'email_client'   => 'Y',
									'email_template' => '0',
									'email_subject'  => 'Task "Balance Reminder" Complete - Client Zone',
									'email_from'     => 'admin',
									'run_when'	     => 'before_event',
									'age'			 => '2 WEEK',
									'notify_admin'   => 'Y',
									'notify_dj' 	 => 'N',
									),
				'function'	 => 'balance_reminder',
				'totalruns'	 => '0',
				'default'	 => 'Y',
			), // balance-reminder
						
			'fail-enquiry'	=> array(
				'slug'		 => 'fail-enquiry',
				'name'	     => 'Fail Enquiry',
				'active'	   => 'N',
				'desc'	     => 'Automatically fail enquiries that have not been updated within the specified amount of time',
				'frequency'	=> 'Daily',
				'nextrun'	  => 'N/A',
				'lastran'	  => 'Never',
				'options'	  => array(
									'email_client'   => 'N',
									'email_template' => '0',
									'email_subject'  => 'Task "Fail Enquiry" Complete - Client Zone',
									'email_from'	 => 'admin',
									'run_when'	   => 'event_created',
									'age'			=> '2 WEEK',
									'notify_admin'   => 'Y',
									'notify_dj' 	  => 'N',
									),
				'function'	 => 'fail_enquiry',
				'totalruns'	 => '0',
				'default'	 => 'Y',
			), // fail-enquiry
						
			'upload-playlists'	=> array(
				'slug'		 => 'upload-playlists',
				'name'	     => 'Upload Playlists',
				'active'	   => 'Y',
				'desc'	     => 'Transmit playlist information back to the MDJM servers to help build an information library. This option is updated via the <a href="' . admin_url( 'admin.php?page=mdjm-settings&tab=general' ) . '">General tab</a>',
				'frequency'	=> 'Twice Daily',
				'nextrun'	  => $playlist_nextrun,
				'lastran'	  => 'Never',
				'options'	  => array(
									'email_client'   => 'N',
									'email_template' => '0',
									'email_subject'  => '0',
									'email_from'	 => '0',
									'run_when'	   => 'after_event',
									'age'			=> '1 HOUR',
									'notify_admin'   => 'N',
									'notify_dj' 	  => 'N',
									),
				'function'	 => 'submit_playlist',
				'totalruns'	 => '0',
				'default'	 => 'Y',
			), // upload-playlists
		); // $mdjm_schedules
/**** Default Schedules end ****/
?>