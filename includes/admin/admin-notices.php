<?php
/**
 * Admin Notices
 *
 * @package     MDJM
 * @subpackage  Admin/Notices
 * @copyright   Copyright (c) 2016, Mike Howard
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.3
*/

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) )
	exit;

/**
 * Admin Messages
 *
 * @since	1.3
 * @global	$mdjm_options	Array of all the MDJM Options
 * @return void
 */
function mdjm_admin_notices() {
	global $mdjm_options;

	// Unattended events
	if( mdjm_employee_can( 'manage_all_events' ) && ( mdjm_get_option( 'warn_unattended' ) ) )	{
		$unattended = MDJM()->events->mdjm_count_event_status( 'mdjm-unattended' );
		
		if( ! empty( $unattended ) && $unattended > 0 )
			add_settings_error( 
				'mdjm-notices',
				'mdjm-unattended-events',
				sprintf( 
					__( 'You have unattended enquiries. %sClick here%s to manage.', 'mobile-dj-manager' ),
					'<a href="' . mdjm_get_admin_page( 'events', 'str' ) . '&post_status=mdjm-unattended">',
					'</a>'
				),
				'updated'
			);
	}
	
	if( isset( $_GET['mdjm-message'] ) && 'song_removed' == $_GET['mdjm-message'] )	{
		add_settings_error(
			'mdjm-notices',
			'mdjm-removed-song',
			__( 'The selected songs were removed.', 'mobile-dj-manager' ),
			'updated'
		);
	}
	
	if( isset( $_GET['mdjm-message'] ) && 'song_remove_failed' == $_GET['mdjm-message'] )	{
		add_settings_error(
			'mdjm-notices',
			'mdjm-remove-faled',
			__( 'The songs count not be removed.', 'mobile-dj-manager' ),
			'error'
		);
	}
	
	if( isset( $_GET['mdjm-message'] ) && 'security_failed' == $_GET['mdjm-message'] )	{
		add_settings_error(
			'mdjm-notices',
			'mdjm-security-failed',
			__( 'Security verification failed. Action not completed.', 'mobile-dj-manager' ),
			'error'
		);
	}
	
	if( isset( $_GET['mdjm-message'] ) && 'playlist_emailed' == $_GET['mdjm-message'] )	{
		add_settings_error(
			'mdjm-notices',
			'mdjm-playlist-emailed',
			__( 'The playlist was emailed successfully.', 'mobile-dj-manager' ),
			'updated'
		);
	}
	
	if( isset( $_GET['mdjm-message'] ) && 'playlist_email_failed' == $_GET['mdjm-message'] )	{
		add_settings_error(
			'mdjm-notices',
			'mdjm-playlist-email-failed',
			__( 'The playlist could not be emailed.', 'mobile-dj-manager' ),
			'error'
		);
	}
	
	if( isset( $_GET['mdjm-message'] ) && 'employee_added' == $_GET['mdjm-message'] )	{
		add_settings_error(
			'mdjm-notices',
			'mdjm-employee_added',
			__( 'Employee added.', 'mobile-dj-manager' ),
			'updated'
		);
	}
	if( isset( $_GET['mdjm-message'] ) && 'employee_add_failed' == $_GET['mdjm-message'] )	{
		add_settings_error(
			'mdjm-notices',
			'mdjm-employee_add-failed',
			__( 'Could not add employee.', 'mobile-dj-manager' ),
			'updated'
		);
	}
	if( isset( $_GET['mdjm-message'] ) && 'employee_info_missing' == $_GET['mdjm-message'] )	{
		add_settings_error(
			'mdjm-notices',
			'mdjm-employee_info-missing',
			__( 'Insufficient information to create employee.', 'mobile-dj-manager' ),
			'error'
		);
	}
	if( isset( $_GET['mdjm-message'] ) && 'comm_missing_content' == $_GET['mdjm-message'] )	{
		add_settings_error(
			'mdjm-notices',
			'mdjm-comm_content-missing',
			__( 'Not all required fields have been completed.', 'mobile-dj-manager' ),
			'error'
		);
	}
	if( isset( $_GET['mdjm-message'] ) && 'comm_sent' == $_GET['mdjm-message'] )	{
		add_settings_error(
			'mdjm-notices',
			'mdjm-comm_sent',
			__( 'Email sent successfully.', 'mobile-dj-manager' ),
			'updated'
		);
	}
	if( isset( $_GET['mdjm-message'] ) && 'comm_not_sent' == $_GET['mdjm-message'] )	{
		add_settings_error(
			'mdjm-notices',
			'mdjm-comm_not_sent',
			__( 'Email not sent.', 'mobile-dj-manager' ),
			'error'
		);
	}

	settings_errors( 'mdjm-notices' );
	
} // mdjm_admin_messages
add_action( 'admin_notices', 'mdjm_admin_notices' );
