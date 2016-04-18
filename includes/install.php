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
	
	global $mdjm_options;
	
	// Setup custom post types
	mdjm_register_post_types();
	
	// Setup custom post statuses
	mdjm_register_post_statuses();
	
	// Setup custom taxonomies
	mdjm_register_taxonomies();
	
	// Clear the permalinks
	flush_rewrite_rules( false );
	
	// Add Upgraded From Option
	$current_version = get_option( 'mdjm_version' );
	if ( $current_version ) {
		update_option( 'mdjm_version_upgraded_from', $current_version );
	}

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
									sprintf( __( 'Thank you for contacting {company_name} regarding your up and coming %s on {event_date}.', 'mobile-dj-manager' ), mdjm_get_label_singular() ) .
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
									sprintf( __( 'Thank you for indicating that you wish to proceed with booking %s for your up and coming %s on %s', 'mobile-dj-manager' ), '{company_name}', mdjm_get_label_singular(), '{event_date}' ) .
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
				'post_title'     => 'Client Booking Confirmation',
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
									sprintf( __( 'My name is %s and I will be your DJ on %s. Should you wish to contact me at any stage to discuss your %s, my details are at the end of this email.', 'mobile-dj-manager' ), '{dj_fullname}', '{event_date}', mdjm_get_label_singular() ) .
									'<br />' .
									'<h2>' . __( 'What Now?', 'mobile-dj-manager' ) . '</h2>' .
									'<br />' .
									'<strong>' . __( 'Music Selection & Playlists', 'mobile-dj-manager' ) . '</strong>' .
									'<br /><br />' .
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
									<a href="{WEBSITE_URL}">{WEBSITE_URL}</a>'
			)
		);
		
		$options['enquiry']  = $enquiry;
		$options['contract'] = $contract;
		
	}
	
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

	$merged_options = array_merge( $mdjm_options, $options );
	$mdjm_options   = $merged_options;

	update_option( 'mdjm_settings', $merged_options );
	update_option( 'mdjm_version', MDJM_VERSION_NUM );
	
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
	
	foreach( $administrators as $user )	{
		update_user_meta( $user->ID, '_mdjm_event_staff', true );
		$user->add_cap( 'mdjm_employee' );
		$user->add_cap( 'manage_mdjm' );
	}
	
	// Assigned full MDJM caps to the manage_mdjm role
	$permissions = new MDJM_Permissions();
	$permissions->make_admin( 'manage_mdjm' );
	
	// Assign the MDJM employee cap to the DJ role
	$role = get_role( 'dj' );
	$role->add_cap( 'mdjm_employee' );
		
} // mdjm_run_install