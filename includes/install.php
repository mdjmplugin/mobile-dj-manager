<?php
/**
 * Install Function
 *
 * @package     MDJM
 * @subpackage  Functions/Install
 * @copyright   Copyright (c) 2016, Mike Howard
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.3
*/

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Install
 *
 * Runs on plugin install by setting up the post types, custom taxonomies,
 * creates the plugin pages and populates the settings fields for those plugin
 * pages. After successful install, the user is redirected to the MDJM Welcome
 * screen.
 *
 * @since 	1.3
 * @global	$wpdb
 * @param 	bool	$network_side	If the plugin is being network-activated
 * @return	void
 */
function mdjm_install( $network_wide = false )	{
	
	global $wpdb;

	if ( is_multisite() && $network_wide ) {

		foreach ( $wpdb->get_col( "SELECT blog_id FROM $wpdb->blogs LIMIT 100" ) as $blog_id ) {

			switch_to_blog( $blog_id );
			mdjm_run_install();
			restore_current_blog();

		}

	} else {

		mdjm_run_install();

	}
	
} // mdjm_install
register_activation_hook( MDJM_PLUGIN_FILE, 'mdjm_install' );

/**
 * Execute the install procedures
 *
 * @since	1.3
 * @param
 * @return	void
 */
function mdjm_run_install()	{
	
	global $mdjm_options, $wpdb;
	
	// Schedule the hourly tasks.
	wp_schedule_event( time(), 'hourly', 'mdjm_hourly_schedule' );
	wp_schedule_event( current_time( 'timestamp' ), 'weekly', 'mdjm_weekly_scheduled_events' );
	
	$current_version = get_option( 'mdjm_version' );
	if ( $current_version ) {
		return;
	}
	
	// Setup custom post types
	mdjm_register_post_types();
	
	// Setup custom post statuses
	mdjm_register_post_statuses();
	
	// Setup custom taxonomies
	mdjm_register_taxonomies();
	
	// Clear the permalinks
	flush_rewrite_rules( false );
	
	// Setup some default options
	$options = array();

	// Pull options from WP, not MDJM's global
	$current_options = get_option( 'mdjm_settings', array() );
	
	// Checks if the Client Zone page option exists
	if ( ! array_key_exists( 'app_home_page', $current_options ) ) {
		
		// Client Zone Home Page
		$client_zone = wp_insert_post(
			array(
				'post_title'     => __( 'Client Zone', 'mobile-dj-manager' ),
				'post_content'   => '[mdjm-home]',
				'post_status'    => 'publish',
				'post_author'    => 1,
				'post_type'      => 'page',
				'comment_status' => 'closed'
			)
		);
		
		// User Profile Page
		$profile = wp_insert_post(
			array(
				'post_title'     => __( 'Your Details', 'mobile-dj-manager' ),
				'post_content'   => '[mdjm-profile]',
				'post_status'    => 'publish',
				'post_author'    => 1,
				'post_type'      => 'page',
				'post_parent'    => $client_zone,
				'comment_status' => 'closed'
			)
		);
		
		// Event Contract Page
		$contract = wp_insert_post(
			array(
				'post_title'     => sprintf( __( '%s Contract', 'mobile-dj-manager' ), mdjm_get_label_singular() ),
				'post_content'   => '[mdjm-contract]',
				'post_status'    => 'publish',
				'post_author'    => 1,
				'post_type'      => 'page',
				'post_parent'    => $client_zone,
				'comment_status' => 'closed'
			)
		);
		
		// Playlist Management Page
		$playlist = wp_insert_post(
			array(
				'post_title'     => __( 'Playlist Management', 'mobile-dj-manager' ),
				'post_content'   => '[mdjm-playlist]',
				'post_status'    => 'publish',
				'post_author'    => 1,
				'post_type'      => 'page',
				'post_parent'    => $client_zone,
				'comment_status' => 'closed'
			)
		);
		
		// Event Quotes Page
		$quotes = wp_insert_post(
			array(
				'post_title'     => sprintf( __( '%s Quotes', 'mobile-dj-manager' ), mdjm_get_label_singular() ),
				'post_content'   => '[mdjm-quote]',
				'post_status'    => 'publish',
				'post_author'    => 1,
				'post_type'      => 'page',
				'post_parent'    => $client_zone,
				'comment_status' => 'closed'
			)
		);
		
		// Store the page IDs in MDJM options
		$options['app_home_page']         = $client_zone;
		$options['contracts_page']        = $contract;
		$options['playlist_page']         = $playlist;
		$options['profile_page']          = $profile;
		$options['quotes_page']           = $quotes;
		
	}
	
	// Create the default email and contract templates
	// Checks if the enquiry template option exists
	if ( ! array_key_exists( 'enquiry', $current_options ) ) {
		
		$enquiry = wp_insert_post(
			array(
				'post_title'     => __( 'Client Enquiry', 'mobile-dj-manager' ),
				'post_status'    => 'publish',
				'post_type'      => 'email_template',
				'post_author'   	=> 1,
				'ping_status'   	=> 'closed',
				'comment_status' => 'closed',
				'post_content'   => '<h1>' . __( 'Your DJ Enquiry from {company_name}', 'mobile-dj-manager' ) . '</h1>' .
									__( 'Dear {client_firstname},', 'mobile-dj-manager' ) .
									'<br /><br />' .
									sprintf( __( 'Thank you for contacting {company_name} regarding your up and coming %s on {event_date}.', 'mobile-dj-manager' ), mdjm_get_label_singular( true ) ) .
									'<br /><br />' .
									__( 'I am pleased to tell you that we are available and would love to provide the disco for you.', 'mobile-dj-manager' ) .
									'<br /><br />' .
									__( 'To provide a disco from {start_time} to {end_time} our cost would be {total_cost}. There are no hidden charges.', 'mobile-dj-manager' ) .
									'<br /><br />' .
									__( 'My standard package includes a vast music collection and great lighting. In addition I would stay in regular contact with you to ensure the night goes to plan. I can incorporate your own playlists, a few songs you want played, requests on the night, or remain in full control of the music - this is your decision, but I can be as flexible as required.', 'mobile-dj-manager' ) .
									'<br /><br />' .
									__( 'Mobile DJs are required to have both PAT and PLI (Portable Appliance Testing and Public Liability Insurance). Confirmation of both can be provided.', 'mobile-dj-manager' ) .
									'<br /><br />' .
									__( 'If you have any further questions, or would like to go ahead and book, please let me know by return.', 'mobile-dj-manager' ) .
									'<br /><br />' .
									__( 'I hope to hear from you soon.', 'mobile-dj-manager' ) .
									'<br /><br />' .
									__( 'Best Regards', 'mobile-dj-manager' ) .
									'<br /><br />' .
									'{dj_fullname}' .
									'<br /><br />' .
									__( 'Email:', 'mobile-dj-manager' ) . ' <a href="mailto:{dj_email}">{dj_email}</a>' .
									'<br />' .
									__( 'Tel:', 'mobile-dj-manager' ) . ' {dj_primary_phone}' .
									'<br />' .
									'<a href="{website_url}">{website_url}</a>'
			)
		);
		
		$online_enquiry = wp_insert_post(
			array(
				'post_title'     => __( 'Default Online Quote', 'mobile-dj-manager' ),
				'post_status'    => 'publish',
				'post_type'		 => 'email_template',
				'post_author'    => 1,
				'ping_status'    => 'closed',
				'comment_status' => 'closed',
				'post_content'   => sprintf( '[caption id="" align="alignleft" width="128"]<a href="%1$s"><img title="%2$s" src="http://www.mydjplanner.co.uk/wp-content/uploads/2014/10/icon-128x1281.png" alt="%2$s" width="128" height="128" /></a> %2$s[/caption]', '{website_url}', '{company_name}' ) .
									'<h3>' . sprintf( __( '%s Quotation for %s', 'mobile-dj-manager' ), mdjm_get_label_singular(), '{client_fullname}' ) . '</h3>' .
									'<pre>' . sprintf( __( 'Prepared by: %s', 'mobile-dj-manager' ), '{dj_fullname}' ) .
									'<br />' .
									__( 'Date:', 'mobile-dj-manager' ) . ' {DDMMYYYY}' .
									'<br />' .
									__( 'Valid for: 2 weeks from date', 'mobile-dj-manager' ) . 
									'</pre><br />' .
									sprintf( __( 'Dear %s,', 'mobile-dj-manager' ), '{client_firstname}' ) .
									'<br />' .
									sprintf( __( 'It is with pleasure that I am providing you with the following costs for your %s on %s.', 'mobile-dj-manager' ), mdjm_get_label_singular( true ), '{event_date}' ) .
									'<br /><br />' .
									sprintf( __( 'I hope you find our quotation to your satisfaction. If there is anything you would like to discuss in further detail, please contact me on %1$s or at <a href="mailto: %2$s">%2$s</a>.', 'mobile-dj-manager' ), '{dj_primary_phone}', '{dj_email}' ) .
									'<br />' .
									'<table style="font-size: 11px;">' .
									'<tbody>' .
									'<tr>' .
									'<td>' . sprintf( __( '%s Date:', 'mobile-dj-manager' ), mdjm_get_label_singular() ) . '</td>' .
									'<td>{event_date}</td>' .
									'<td>' . sprintf( __( '%s Type:', 'mobile-dj-manager' ), mdjm_get_label_singular() )  . '</td>' .
									'<td>{event_type}</td>' .
									'</tr>' .
									'<tr>' .
									'<td>' . __( 'Start Time:', 'mobile-dj-manager' ) . '</td>' .
									'<td>{start_time}</td>' .
									'<td>' . __( 'End Time:', 'mobile-dj-manager' ) . '</td>' .
									'<td>{end_time}</td>' .
									'</tr>' .
									'<tr>' .
									'<td>' . __( 'Selected Package:', 'mobile-dj-manager' ) . '</td>' .
									'<td>{event_package}</td>' .
									'<td>' . __( 'Add-ons:', 'mobile-dj-manager' ) . '</td>' .
									'<td>{event_addons}</td>' .
									'</tr>' .
									'<tr>' .
									'<td>' . __( 'Venue Details:', 'mobile-dj-manager' ) . '</td>' .
									'<td colspan="3">{venue_full_address}</td>' .
									'</tr>' .
									'<tr>' .
									'<td colspan="4">' .
									'<hr />' .
									'</td>' .
									'</tr>' .
									'<tr style="font-weight: bold;">' .
									'<td colspan="2">' . sprintf( __( '%s Cost:', 'mobile-dj-manager' ), mdjm_get_label_singular() ) . '</td>' .
									'<td colspan="2">{total_cost}</td>' .
									'</tr>' .
									'<tr style="font-weight: bold;">' .
									'<td colspan="2">' . __( 'Booking Fee:', 'mobile-dj-manager' ) . '</td>' .
									'<td colspan="2">{DEPOSIT} <span style="font-size: 9px;">(' . __( 'due at time of booking', 'mobile-dj-manager' ) . ')</span></td>' .
									'</tr>' .
									'</tbody>' .
									'</table>' .
									'<span style="color: #cccccc; font-size: 9px;"><a style="color: #cccccc;" href="#">' . __( 'Click here to view our list of terms and conditions', 'mobile-dj-manager' ) . '</a></span>'
			)
		);
		
		$contract = wp_insert_post(
			array(
				'post_title'     => __( 'Client Contract Review', 'mobile-dj-manager' ),
				'post_status'    => 'publish',
				'post_type'      => 'email_template',
				'post_author'    => 1,
				'ping_status'    => 'closed',
				'comment_status' => 'closed',
				'post_content'   => '<h2>' . sprintf( __( 'Your Booking with %s', 'mobile-dj-manager' ), '{company_name}' ) . '</h2>' .
									sprintf( __( 'Dear %s,', 'mobile-dj-manager' ), '{client_firstname}' ) .
									'<br /><br />' .
									sprintf( __( 'Thank you for indicating that you wish to proceed with booking %s for your up and coming %s on %s', 'mobile-dj-manager' ), '{company_name}', mdjm_get_label_singular( true ), '{event_date}' ) .
									'<br /><br />' .
									__( 'There are two final tasks to complete before your booking can be confirmed...', 'mobile-dj-manager' ) .
									'<br />' .
									'<ul>' .
									'<li><strong>' . __( 'Review and accept your contract', 'mobile-dj-manager' ) . '</strong><br />' .
									sprintf( __( 'Your contract has now been produced. You can review it by <a href="%s">clicking here</a>. Please review the terms and accept the contract. If you would prefer the contract to be emailed to you, please let me know by return email.', 'mobile-dj-manager' ), '{contract_url}' ) . '</li>' .
									'<li><strong>' . __( 'Pay your deposit', 'mobile-dj-manager' ) . '</strong><br />' .
									sprintf( __( 'Your deposit of <strong>%s</strong> is now due. If you have not already done so please make this payment now. Details of how to make this payment are shown within the <a href="%s">contract</a>', 'mobile-dj-manager' ), '{deposit}', '{contract_url}' ) . '</li>' .
									'</ul><br />' .
									__( 'Once these actions have been completed you will receive a further email confirming your booking.', 'mobile-dj-manager' ) .
									'<br /><br />' .
									__( 'Meanwhile if you have any questions, please do not hesitate to get in touch.', 'mobile-dj-manager' ) .
									'<br /><br />' .
									sprintf( __( 'Thank you for choosing %s', 'mobile-dj-manager' ), '{company_name}' ) .
									'<br /><br />' .
									__( 'Regards', 'mobile-dj-manager' ) .
									'<br /><br />' .
									'{company_name}' .
									'<br />' .
									'<a href="{website_url}">{website_url}</a>'
			)
		);
		
		$booking_conf_client = wp_insert_post(
			array(
				'post_title'     => __( 'Client Booking Confirmation', 'mobile-dj-manager' ),
				'post_status'   	=> 'publish',
				'post_type'      => 'email_template',
				'post_author'   	=> 1,
				'ping_status'   	=> 'closed',
				'comment_status' => 'closed',
				'post_content'   => '<h1>' . __( 'Booking Confirmation', 'mobile-dj-manager' ) . '</h1>' .
									sprintf( __( 'Dear %s,', 'mobile-dj-manager' ), '{client_firstname}' ) .
									'<br /><br />' .
									sprintf( __( 'Thank you for booking your up and coming %s with %s. Your booking is now confirmed.', 'mobile-dj-manager' ), mdjm_get_label_singular(), '{company_name}' ) .
									'<br /><br />' .
									sprintf( __( 'My name is %s and I will be your DJ on %s. Should you wish to contact me at any stage to discuss your %s, my details are at the end of this email.', 'mobile-dj-manager' ), '{dj_fullname}', '{event_date}', mdjm_get_label_singular( true ) ) .
									'<br />' .
									'<h2>' . __( 'What Now?', 'mobile-dj-manager' ) . '</h2>' .
									'<br />' .
									'<strong>' . __( 'Music Selection & Playlists', 'mobile-dj-manager' ) . '</strong>' .
									'<br /><br />' .
									sprintf( __( 'We have an online portal where you can add songs that you would like to ensure we play during your disco. To access this feature, head over to the %1$s <a href="%2$s">%2$s</a>. The playlist feature will close %3$s days before your %4$s.', 'mobile-dj-manager' ), '{company_name}', '{application_home}', '{playlist_close}', mdjm_get_label_singular( true ) ) .
									'<br /><br />' .
									__( 'You will need to login. Your username and password have already been sent to you in a previous email but if you no longer have this information, click on the lost password link and enter your user name, which is your email address. Instructions on resetting your password will then be sent to you.', 'mobile-dj-manager' ) .
									'<br /><br />' .
									sprintf( __( 'You can also invite your guests to add songs to your playlist by providing them with your unique playlist URL - <a href="%1$s">%1$s</a>. We recommend creating a <a href="https://www.facebook.com/events/">Facebook Events Page</a> and sharing the link on there. Alternatively of course, you can email the URL to your guests.', 'mobile-dj-manager' ), '{playlist_url}' ) .
									'<br /><br />' .
									__( "Don\'t worry though, you have full control over your playlist so you can remove songs added by your guests if you do not like their choices.", 'mobile-dj-manager' ) .
									'<br /><br />' .
									'<strong>' . __( 'When will you next hear from me?', 'mobile-dj-manager' ) . '</strong>' .
									'<br /><br />' .
									sprintf( __( 'I generally contact you again approximately 2 weeks before your %s to finalise details with you. However, if you have any questions, concerns, or just want a general chat about your disco, feel free to contact me at any time.', 'mobile-dj-manager' ), mdjm_get_label_singular( true ) ) .
									'<br /><br />' .
									sprintf( __( 'Thanks again for choosing %s to provide the DJ & Disco for your %s. I look forward to partying with you on %s.', 'mobile-dj-manager' ), '{company_name}', mdjm_get_label_singular( true ), '{event_date}' ) .
									'<br /><br />' .
									__( 'Best Regards', 'mobile-dj-manager' ) .
									'<br /><br />' .
									'{dj_fullname}' .
									'<br /><br />' .
									__( 'Email:', 'mobile-dj-manager' ) . ' <a href="mailto:{dj_email}">{dj_email}</a>' .
									'<br />' .
									__( 'Tel:', 'mobile-dj-manager' ) . ' {dj_primary_phone}' .
									'<br />' .
									'<a href="{website_url}">{website_url}</a>'
			)
		);
		
		$email_dj_confirm = wp_insert_post(
			array(
				'post_title'     => __( 'DJ Booking Confirmation', 'mobile-dj-manager' ),
				'post_status'    => 'publish',
				'post_type'      => 'email_template',
				'post_author'    => 1,
				'ping_status'    => 'closed',
				'comment_status' => 'closed',
				'post_content'   => '<h1>' . __( 'Booking Confirmation', 'mobile-dj-manager' ) . '</h1>' .
									sprintf( __( 'Dear %s,', 'mobile-dj-manager' ), '{dj_firstname}' ) .
									'<br /><br />' .
									sprintf( __( 'Your client %s has just confirmed their booking for you to DJ at their %s on %s.', 'mobile-dj-manager' ), '{client_fullname}', mdjm_get_label_singular( true ), '{event_date}' ) .
									'<br /><br />' .
									sprintf( __( 'A booking confirmation email has been sent to them and they now have your contact details and access to the online %s tools to create playlist entries etc.', 'mobile-dj-manager' ), '{application_name}' ) .
									'<br /><br />' .
									sprintf( __( 'Make sure you login regularly to the <a href="%s">%s %s admin interface</a> to ensure you have all relevant information relating to their booking.', 'mobile-dj-manager' ), admin_url(), '{company_name}', '{application_name}' ) .
									'<br /><br />' .
									sprintf( __( 'Remember it is your responsibility to remain in regular contact with your client regarding their %1$s as well as answer any queries or concerns they may have. Customer service is one of our key selling points and after the event, your client will be invited to provide feedback regarding the booking process, communication in the lead up to the %1$s, as well as the %1$s itself.', 'mobile-dj-manager' ), mdjm_get_label_singular( true ) ) .
									'<br />' .
									'<h2>' . sprintf( __( '%s Details', 'mobile-dj-manager' ), mdjm_get_label_singular() ) . '</h2>' .
									'<br />' .
									__( 'Client Name: ', 'mobile-dj-manager' ) . '{client_fullname}' . '<br />' .
									__( 'Event Date: ', 'mobile-dj-manager' ). '{event_date}' . '<br />' .
									__( 'Type: ', 'mobile-dj-manager' ). '{event_type}' . '<br />' .
									__( 'Start Time: ', 'mobile-dj-manager' ). '{start_time}' . '<br />' .
									__( 'Finish Time: ', 'mobile-dj-manager' ). '{end_time}' . '<br />' .
									__( 'Venue: ', 'mobile-dj-manager' ). '{venue}' . '<br />' .
									__( 'Balance Due: ', 'mobile-dj-manager' ). '{balance}' . '<br />' .
									'<br />' .
									sprintf( __( 'Further information is available on the <a href="%s">%s %s admin interface</a>.', 'mobile-dj-manager' ), admin_url(), '{company_name}', '{application_home}' ) .
									'<br /><br />' .
									__( 'Regards', 'mobile-dj-manager' ) .
									'<br /><br />' .
									'{company_name}'
			)
		);
		
		$unavailable = wp_insert_post(
			array(
				'post_title'     => sprintf( __( '%s is not Available', 'mobile-dj-manager' ), '{company_name}' ),
				'post_status'    => 'publish',
				'post_type'      => 'email_template',
				'post_author'    => 1,
				'ping_status'    => 'closed',
				'comment_status' => 'closed',
				'post_content'   => '<h1>' . sprintf( __( 'Your DJ Enquiry with %s', 'mobile-dj-manager' ), '{company_name}' ) . '</h1>' .
									sprintf( __( 'Dear %s', 'mobile-dj-manager' ), '{client_firstname}' ) .
									'<br /><br />' .
									sprintf( __( 'Thank you for contacting %s regarding your up and coming %s on %s.', 'mobile-dj-manager' ), '{company_name}', mdjm_get_label_singular( true ), '{event_date}' ) .
									'<br /><br />' .
									sprintf( __( 'Unfortunately however, we are not available on the date you have selected for your %s.', 'mobile-dj-manager' ), mdjm_get_label_singular( true ) ) .
									__( "If you have alternative dates you are looking at, we'd love to hear from you again.", 'mobile-dj-manager' ) .
									'<br /><br />' .
									sprintf( __( 'Otherwise, we hope you have a great %s and hope to hear from you again next time.', 'mobile-dj-manager' ), mdjm_get_label_singular( true ) ) .
									'<br /><br />' .
									__( 'Best Regards', 'mobile-dj-manager' ) .
									'<br /><br />' .
									'{dj_fullname}' .
									'<br /><br />' .
									__( 'Email:', 'mobile-dj-manager' ) . ' <a href="mailto:{dj_email}">{dj_email}</a>' .
									'<br />' .
									__( 'Tel:', 'mobile-dj-manager' ) . ' {dj_primary_phone}' .
									'<br />' .
									'<a href="{website_url}">{website_url}</a>'
			)
		);
		
		$payment_cfm = wp_insert_post(
			array(
				'post_title'     => sprintf( __( '%s %s Payment Confirmation', 'mobile-dj-manager' ), mdjm_get_label_singular(), '{payment_for}' ),
				'post_status'    => 'publish',
				'post_type'      => 'email_template',
				'post_author'    => 1,
				'ping_status'    => 'closed',
				'comment_status' => 'closed',
				'post_content'   => '<h4><span style="color: #ff9900;">' . sprintf( __( 'Thank you for your %s payment', 'mobile-dj-manager' ), '{payment_for}' ) . '</span></h4>' .
									sprintf( __( 'Dear %s,', 'mobile-dj-manager' ), '{client_firstname}' ) .
									'<br /><br />' .
									sprintf( __( 'Thank you for your recent payment of <strong>%s</strong> towards the <strong>%s</strong> for you event on <strong>%s</strong>. Your payment has been received and your %s details have been updated.', 'mobile-dj-manager' ), '{payment_amount}', '{payment_for}', '{event_date}', mdjm_get_label_singular( true ) ) .
									'<br /><br />' .
									sprintf( __( 'You can view your event details and manage your playlist by logging onto our <a title="%1$s %2$s" href="%3$s">%2$s</a> event management system.', 'mobile-dj-manager' ), '{company_name}', '{application_name}', '{application_home}' ) .
									'<br /><br />' .
									sprintf( __( "Your username is %s and if you can't recall your password, you can reset it by clicking the <a title='Reset your password for the %s %s' href='%s'>Lost Password</a> link.", 'mobile-dj-manager' ), '{client_username}', '{company_name}', '{application_name}', wp_lostpassword_url() ) .
									'<br /><br />' .
									__( 'Best Regards', 'mobile-dj-manager' ) .
									'<br /><br />' .
									'{dj_fullname}' .
									'<br /><br />' .
									__( 'Email:', 'mobile-dj-manager' ) . ' <a href="mailto:{dj_email}">{dj_email}</a>' .
									'<br />' .
									__( 'Tel:', 'mobile-dj-manager' ) . ' {dj_primary_phone}' .
									'<br />' .
									'<a href="{website_url}">{website_url}</a>'
			)
		);
		
		$default_contract = wp_insert_post(
			array(
				'post_title'     => __( 'General', 'mobile-dj-manager' ),
				'post_status'    => 'publish',
				'post_type'      => 'contract',
				'post_author'    => 1,
				'ping_status'    => 'closed',
				'comment_status' => 'closed',
				'post_content'   => '<h2 style="text-align: center;"><span style="text-decoration: underline;">Confirmation of Booking</span></h2><h3>Agreement Date: <span style="color: #ff0000;">{DDMMYYYY}</span></h3>This document sets out the terms and conditions verbally agreed by both parties and any non-fulfilment of the schedule below may render the defaulting party liable to damages.This agreement is between: <strong>{COMPANY_NAME}</strong> (hereinafter called the Artiste)and:<strong>{CLIENT_FULLNAME}</strong> (hereinafter called the Employer)<strong>of</strong><address><strong>{CLIENT_FULL_ADDRESS}{CLIENT_EMAIL}{CLIENT_PRIMARY_PHONE}</strong> </address><address> </address><address>in compliance with the schedule set out below.</address><h3 style="text-align: center;"><span style="text-decoration: underline;">Schedule</span></h3>It is agreed that the Artiste shall appear for the performance set out below for a total inclusive fee of <span style="color: #ff0000;"><strong>{TOTAL_COST}</strong></span>.Payment terms are: <strong><span style="color: #ff0000;">{DEPOSIT}</span> Deposit</strong> to be returned together with this form followed by <strong>CASH ON COMPLETION</strong> for the remaining balance of <strong><span style="color: #ff0000;">{BALANCE}</span>. </strong>Cheques will only be accepted by prior arrangement.Deposits can be made via bank transfer to the following account or via cheque made payable to <strong>XXXXXX</strong> and sent to the address at the top of this form.<strong>Bank Transfer Details: Name XXXXXX | Acct No. 10000000 | Sort Code | 30-00-00</strong><strong>The confirmation of this booking is secured upon receipt of the signed contract and any stated deposit amount</strong>.<h3 style="text-align: center;"><span style="text-decoration: underline;">Venue and Event</span></h3><table border="0" width="100%" cellspacing="0" cellpadding="0"><tbody><tr><td align="center"><table border="0" width="75%" cellspacing="0" cellpadding="0"><tbody><tr><td style="border-bottom-width: thin; border-bottom-style: solid; border-bottom-color: #000; border-right-width: thin; border-right-style: solid; border-right-color: #000;" width="33%"><strong>Address</strong></td><td style="border-bottom-width: thin; border-bottom-style: solid; border-bottom-color: #000; border-right-width: thin; border-right-style: solid; border-right-color: #000;" width="33%"><strong>Telephone Number</strong></td><td style="border-bottom-width: thin; border-bottom-style: solid; border-bottom-color: #000;" width="33%"><strong>Date</strong></td></tr><tr><td style="border-right-width: thin; border-right-style: solid; border-right-color: #000;" valign="top" width="33%"><span style="color: #ff0000;"><strong>{VENUE_FULL_ADDRESS}</strong></span></td><td style="border-right-width: thin; border-right-style: solid; border-right-color: #000;" valign="top" width="33%"><span style="color: #ff0000;"><strong>{VENUE_TELEPHONE}</strong></span></td><td valign="top" width="33%"><span style="color: #ff0000;"><strong>{EVENT_DATE}</strong></span></td></tr></tbody></table></td></tr></tbody></table>The Artiste will perform between the times of <span style="color: #ff0000;"><strong>{START_TIME}</strong></span> to <span style="color: #ff0000;"><strong>{END_TIME}</strong></span>. Any additional time will be charged at £50 per hour or part of.<hr /><h2 style="text-align: center;"> Terms &amp; Conditions</h2><ol>	<li>This contract may be cancelled by either party, giving the other not less than 28 days prior notice.</li>	<li>If the Employer cancels the contract in less than 28 days’ notice, the Employer is required to pay full contractual fee, unless a mutual written agreement has been made by the Artiste and Employer.</li>	<li>Deposits are non-refundable, unless cancellation notice is issued by the Artiste or by prior written agreement.</li>	<li>This contract is not transferable to any other persons/pub/club without written permission of the Artiste.</li>	<li>Provided the Employer pays the Artiste his full contractual fee, he may without giving any reason, prohibit the whole or any part of the Artiste performance.</li>	<li>Whilst all safeguards are assured the Artiste cannot be held responsible for any loss or damage, out of the Artiste’s control during any performance whilst on the Employers premises.</li>	<li>The Employer is under obligation to reprimand or if necessary remove any persons being repetitively destructive or abusive to the Artiste or their equipment.</li>	<li>It is the Employer’s obligation to ensure that the venue is available 90 minutes prior to the event start time and 90 minutes from event completion.</li>	<li>The venue must have adequate parking facilities and accessibility for the Artiste and his or her equipment.</li>	<li>The Artiste reserves the right to provide an alternative performer to the employer for the event. Any substitution will be advised in writing at least 7 days before the event date and the performer is guaranteed to be able to provide at least the same level of service as the Artiste.</li>	<li>Failing to acknowledge and confirm this contract 28 days prior to the performance date does not constitute a cancellation, however it may render the confirmation unsafe. If the employer does not acknowledge and confirm the contract within the 28 days, the Artiste is under no obligation to confirm this booking.</li>	<li>From time to time the Artiste, or a member of their crew, may take photographs of the performance. These photographs may include individuals attending the event. If you do not wish for photographs to be taken or used publicly such as on the Artiste’s websites or other advertising media, notify the Artiste in writing.</li></ol>'
			)
		);
		
		$options['enquiry']                     = $enquiry;
		$options['online_enquiry']              = $online_enquiry;
		$options['contract']                    = $contract;
		$options['booking_conf_client']         = $booking_conf_client;
		$options['email_dj_confirm']            = $email_dj_confirm;
		$options['unavailable']                 = $unavailable;
		$options['payment_cfm_template']        = $payment_cfm;
		$options['manual_payment_cfm_template'] = $payment_cfm;
		$options['default_contract']            = $default_contract;

	}
	
	// Setup default client fields
	$client_fields = array(
		'first_name' => array(
			'label' => __( 'First Name', 'mobile-dj-manager' ),
			'id' => 'first_name',
			'type' => 'text',
			'value' => '',
			'checked' => '0',
			'display' => '1',
			'required' => '1',
			'desc' => '',
			'default' => '1',
			'position' => '0',
		),
		'last_name' => array(
			'label' => __( 'Last Name', 'mobile-dj-manager' ),
			'id' => 'last_name',
			'type' => 'text',
			'value' => '',
			'checked' => '0',
			'display' => '1',
			'required' => '1',
			'desc' => '',
			'default' => '1',
			'position' => '1',
		),
		'user_email' => array(
			'label' => __( 'Email Address', 'mobile-dj-manager' ),
			'id' => 'user_email',
			'type' => 'text',
			'value' => '',
			'checked' => '0',
			'display' => '1',
			'required' => '1',
			'desc' => '',
			'default' => '1',
			'position' => '2',
		),
		'address1' => array(
			'label' => __( 'Address 1', 'mobile-dj-manager' ),
			'id' => 'address1',
			'type' => 'text',
			'value' => '',
			'checked' => '0',
			'display' => '1',
			'required' => '1',
			'desc' => '',
			'default' => '1',
			'position' => '3',
		),
		'address2' => array(
			'label' => __( 'Address 2', 'mobile-dj-manager' ),
			'id' => 'address2',
			'type' => 'text',
			'value' => '',
			'checked' => '0',
			'display' => '1',
			'required' => '0',
			'desc' => '',
			'default' => '1',
			'position' => '4',
		),
		'town' => array(
			'label' => __( 'Town / City', 'mobile-dj-manager' ),
			'id' => 'town',
			'type' => 'text',
			'value' => '',
			'checked' => '0',
			'display' => '1',
			'required' => '1',
			'desc' => '',
			'default' => '1',
			'position' => '5',
		),
		'county' => array(
			'label' => __( 'County', 'mobile-dj-manager' ),
			'id' => 'county',
			'type' => 'text',
			'value' => '',
			'checked' => '0',

			'display' => '1',
			'required' => '1',
			'desc' => '',
			'default' => '1',
			'position' => '6',
		),
		'postcode' => array(
			'label' => __( 'Post Code', 'mobile-dj-manager' ),
			'id' => 'postcode',
			'type' => 'text',
			'value' => '',
			'checked' => '0',
			'display' => '1',

			'required' => '1',
			'desc' => '',
			'default' => '1',
			'position' => '7',
		),
		'phone1' => array(
			'label' => __( 'Primary Phone', 'mobile-dj-manager' ),
			'id' => 'phone1',
			'type' => 'text',
			'value' => '',
			'checked' => '0',
			'display' => '1',
			'required' => '1',
			'desc' => '',
			'default' => '1',
			'position' => '8',
		),
		'phone2' => array(
			'label' => __( 'Alternative Phone', 'mobile-dj-manager' ),
			'id' => 'phone2',
			'type' => 'text',
			'value' => '',
			'checked' => '0',
			'display' => '1',
			'desc' => '',
			'default' => '1',
			'position' => '9',
		),
		'birthday' => array(
			'label' => __( 'Birthday', 'mobile-dj-manager' ),
			'id' => 'birthday',
			'type' => 'dropdown',
			'value' => __( 'January' ) . "\r\n" . 
					   __( 'February' ) . "\r\n" .
					   __( 'March' ) . "\r\n" . 
					   __( 'April' ) . "\r\n" . 
					   __( 'May' ) . "\r\n" . 
					   __( 'June' ) . "\r\n" . 
					   __( 'July' ) . "\r\n" . 
					   __( 'August' ) . "\r\n" . 
					   __( 'September' ) . "\r\n" . 
					   __( 'October' ) . "\r\n" . 
					   __( 'November' ) . "\r\n" . 
					   __( 'December' ),
			'checked' => '0',
			'display' => '1',
			'desc' => '',
			'default' => '1',
			'position' => '10',
		),
		'marketing' => array(
			'label' => __( 'Marketing Info', 'mobile-dj-manager' ) . '?',
			'id' => 'marketing',
			'type' => 'checkbox',
			'value' => '1',
			'checked' => ' checked',
			'display' => '1',
			'desc' => __( 'Do we add the user to the mailing list', 'mobile-dj-manager' ) . '?',
			'default' => '1',
			'position' => '11',
		)
	);
	
	// Populate some default values
	foreach( mdjm_get_registered_settings() as $tab => $sections ) {	
		foreach( $sections as $section => $settings ) {

			// Check for backwards compatibility
			$tab_sections = mdjm_get_settings_tab_sections( $tab );
			
			if( ! is_array( $tab_sections ) || ! array_key_exists( $section, $tab_sections ) ) {
				$section = 'main';
				$settings = $sections;
			}

			foreach ( $settings as $option ) {

				if( 'checkbox' == $option['type'] && ! empty( $option['std'] ) ) {
					$options[ $option['id'] ] = '1';
				}

			}
		}

	}

	$options['employee_pay_status'] = array( 'mdjm-completed' );

	$merged_options = array_merge( $mdjm_options, $options );
	$mdjm_options   = $merged_options;

	update_option( 'mdjm_settings', $merged_options );
	update_option( 'mdjm_version', MDJM_VERSION_NUM );
	update_option( 'mdjm_client_fields', $client_fields );
	
	// Setup scheduled tasks
	MDJM()->cron->create_tasks();
	
	// Create taxonomy terms
	// Event Types
	wp_insert_term( __( '16th Birthday Party', 'mobile-dj-manager' ), 'event-types' );
	wp_insert_term( __( '18th Birthday Party', 'mobile-dj-manager' ), 'event-types' );
	wp_insert_term( __( '21st Birthday Party', 'mobile-dj-manager' ), 'event-types' );
	wp_insert_term( __( '30th Birthday Party', 'mobile-dj-manager' ), 'event-types' );
	wp_insert_term( __( '40th Birthday Party', 'mobile-dj-manager' ), 'event-types' );
	wp_insert_term( __( '50th Birthday Party', 'mobile-dj-manager' ), 'event-types' );
	wp_insert_term( __( '60th Birthday Party', 'mobile-dj-manager' ), 'event-types' );
	wp_insert_term( __( '70th Birthday Party', 'mobile-dj-manager' ), 'event-types' );
	wp_insert_term( __( 'Anniversary Party', 'mobile-dj-manager' ), 'event-types' );
	wp_insert_term( __( 'Child Birthday Party', 'mobile-dj-manager' ), 'event-types' );
	wp_insert_term( __( 'Corporate Event', 'mobile-dj-manager' ), 'event-types' );
	wp_insert_term( __( 'Engagement Party', 'mobile-dj-manager' ), 'event-types' );
	wp_insert_term( __( 'Halloween Party', 'mobile-dj-manager' ), 'event-types' );
	wp_insert_term( __( 'New Years Eve Party', 'mobile-dj-manager' ), 'event-types' );
	wp_insert_term( __( 'Other', 'mobile-dj-manager' ), 'event-types' );
	wp_insert_term( __( 'School Disco', 'mobile-dj-manager' ), 'event-types' );
	wp_insert_term( __( 'School Prom', 'mobile-dj-manager' ), 'event-types' );
	wp_insert_term( __( 'Wedding', 'mobile-dj-manager' ), 'event-types' );
	
	// Enquiry Sources
	wp_insert_term( __( 'Business Card', 'mobile-dj-manager' ), 'enquiry-source' );
	wp_insert_term( __( 'Email', 'mobile-dj-manager' ), 'enquiry-source' );
	wp_insert_term( __( 'Facebook', 'mobile-dj-manager' ), 'enquiry-source' );
	wp_insert_term( __( 'Flyer', 'mobile-dj-manager' ), 'enquiry-source' );
	wp_insert_term( __( 'Google', 'mobile-dj-manager' ), 'enquiry-source' );
	wp_insert_term( __( 'Other', 'mobile-dj-manager' ), 'enquiry-source' );
	wp_insert_term( __( 'Telephone', 'mobile-dj-manager' ), 'enquiry-source' );
	wp_insert_term( __( 'Website', 'mobile-dj-manager' ), 'enquiry-source' );
	
	// Playlist Terms
	wp_insert_term( __( 'General', 'mobile-dj-manager' ), 'playlist-category' );
	wp_insert_term( __( 'First Dance', 'mobile-dj-manager' ), 'playlist-category' );
	wp_insert_term( __( 'Second Dance', 'mobile-dj-manager' ), 'playlist-category' );
	wp_insert_term( __( 'Last Song', 'mobile-dj-manager' ), 'playlist-category' );
	wp_insert_term( __( 'Father & Bride', 'mobile-dj-manager' ), 'playlist-category' );
	wp_insert_term( __( 'Mother & Son', 'mobile-dj-manager' ), 'playlist-category' );
	wp_insert_term( __( 'DO NOT PLAY', 'mobile-dj-manager' ), 'playlist-category' );
	wp_insert_term( __( 'Other', 'mobile-dj-manager' ), 'playlist-category' );
	wp_insert_term( __( 'Guest', 'mobile-dj-manager' ), 'playlist-category' );
	
	// Transaction Terms
	wp_insert_term( __( 'Deposit', 'mobile-dj-manager' ), 'transaction-types', array( 'description' => __( 'Event deposit payments are assigned to this term', 'mobile-dj-manager' ), 'slug' => 'mdjm-deposit-payments' ) );
	wp_insert_term( __( 'Balance', 'mobile-dj-manager' ), 'transaction-types', array( 'description' => __( 'Event balance payments are assigned to this term', 'mobile-dj-manager' ), 'slug' => 'mdjm-balance-payments' ) );
	wp_insert_term( __( 'Certifications', 'mobile-dj-manager' ), 'transaction-types' );
	wp_insert_term( __( 'Employee Wages','mobile-dj-manager' ), 'transaction-types', array( 'description' => __( 'All employee wage payments are assigned to this term', 'mobile-dj-manager' ), 'slug' => 'mdjm-employee-wages' ) );
	wp_insert_term( __( 'Hardware', 'mobile-dj-manager' ), 'transaction-types' );
	wp_insert_term( __( 'Insurance', 'mobile-dj-manager' ), 'transaction-types' );
	wp_insert_term( __( 'Maintenance', 'mobile-dj-manager' ), 'transaction-types' );
	wp_insert_term( __( 'Merchant Fees', 'mobile-dj-manager' ), 'transaction-types', array( 'description' => __( 'Charges from payment gateways are assigned to this term', 'mobile-dj-manager' ), 'slug' => 'mdjm-merchant-fees' ) );
	wp_insert_term( __( 'Music', 'mobile-dj-manager' ), 'transaction-types' );
	wp_insert_term( __( 'Other Amount', 'mobile-dj-manager' ), 'transaction-types', array( 'description' => __( 'Term used for payments that are a contribution towards balance', 'mobile-dj-manager' ), 'slug' => 'mdjm-other-amount' ) );
	wp_insert_term( __( 'Parking', 'mobile-dj-manager' ), 'transaction-types' );
	wp_insert_term( __( 'Petrol', 'mobile-dj-manager' ), 'transaction-types' );
	wp_insert_term( __( 'Software', 'mobile-dj-manager' ), 'transaction-types' );
	wp_insert_term( __( 'Vehicle', 'mobile-dj-manager' ), 'transaction-types' );
	
	// Venue Terms
	wp_insert_term( __( 'Low Ceiling', 'mobile-dj-manager' ), 'venue-details', array( 'description' => __( 'Venue has a low ceiling', 'mobile-dj-manager' ) ) );
	wp_insert_term( __( 'PAT Required', 'mobile-dj-manager' ), 'venue-details', array( 'description' => __( 'Venue requires a copy of the PAT certificate', 'mobile-dj-manager' ) ) );		
	wp_insert_term( __( 'PLI Required', 'mobile-dj-manager' ), 'venue-details', array( 'description' => __( 'Venue requires proof of PLI', 'mobile-dj-manager' ) ) );
	wp_insert_term( __( 'Smoke/Fog Allowed', 'mobile-dj-manager' ), 'venue-details', array( 'description' => __( 'Venue allows the use of Smoke/Fog/Haze', 'mobile-dj-manager' ) ) );
	wp_insert_term( __( 'Sound Limiter', 'mobile-dj-manager' ), 'venue-details', array( 'description' => __( 'Venue has a sound limiter', 'mobile-dj-manager' ) ) );
	wp_insert_term( __( 'Via Stairs', 'mobile-dj-manager' ), 'venue-details', array( 'description' => __( 'Access to this Venue is via stairs', 'mobile-dj-manager' ) ) );
	
	// Create the custom MDJM User Roles
	$roles = new MDJM_Roles();
	$roles->add_roles();
	
	// Make all admins MDJM employees and admins by default by assigning caps to the user directly
	$administrators = get_users( array( 'role' => 'administrator' ) );
	
	$permissions = new MDJM_Permissions();
	
	foreach( $administrators as $user )	{
		update_user_meta( $user->ID, '_mdjm_event_staff', true );
		update_user_meta( $user->ID, '_mdjm_event_admin', true );
		$user->add_role( 'dj' );
		$user->add_cap( 'mdjm_employee' );
		$permissions->make_admin( $user->ID );	
	}
	
	// Assign the MDJM employee cap to the DJ role
	$role = get_role( 'dj' );
	$role->add_cap( 'mdjm_employee' );
	
	// Create the availability check DB table
	require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
	
	$sql = "CREATE TABLE " . $wpdb->prefix . "mdjm_avail (
		id int(11) NOT NULL AUTO_INCREMENT,
		user_id int(11) NOT NULL,
		entry_id varchar(100) NOT NULL,
		date_from date NOT NULL,
		date_to date NOT NULL,
		notes text NULL,
		PRIMARY KEY  (id),
		KEY user_id (user_id)
		) CHARACTER SET utf8 COLLATE utf8_general_ci;";

	dbDelta( $sql );

	update_option( 'mdjm_db_version', $mdjm_db_version );
	
	// Add the transient to redirect
	set_transient( '_mdjm_activation_redirect', true, 30 );
		
} // mdjm_run_install

/**
 * Run during plugin deactivation.
 * 
 * Clear the scheduled hook for hourly tasks.
 * 
 * @since	1.3
 * @param
 * @return	void
 */
function mdjm_deactivate()	{
	wp_clear_scheduled_hook( 'mdjm_hourly_schedule' );
	wp_clear_scheduled_hook( 'mdjm_weekly_scheduled_events' );
} // mdjm_deactivate
register_deactivation_hook( MDJM_PLUGIN_FILE, 'mdjm_deactivate' );