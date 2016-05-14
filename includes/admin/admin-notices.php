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
			echo '<div class="notice notice-info is-dismissible">';
			echo '<p>' .
					sprintf( 
						__( 'You have unattended enquiries. <a href="%s">Click here</a> to manage.', 'mobile-dj-manager' ),
						admin_url( 'edit.php?post_type=mdjm-event&post_status=mdjm-unattended' )
					). '</p>';
			echo '</div>';
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
	if( isset( $_GET['mdjm-action'] ) && 'get_event_availability' == $_GET['mdjm-action'] )	{
		
		if( ! wp_verify_nonce( $_GET[ 'mdjm_nonce' ], 'get_event_availability' ) )	{
			return;
		} elseif( ! isset( $_GET['event_id'] ) )	{
			return;
		} else	{
			
			$date = strtotime( mdjm_get_event_date( $_GET['event_id'] ) );
			
			$result = mdjm_do_availability_check( $date );
			
			if( ! empty( $result['available'] ) )	{
				echo '<div class="notice notice-info is-dismissible">';
        		echo '<p>' .
						sprintf(
							__( 'There are no employees available to work %s %s on %s', 'mobile-dj-manager' ),
							mdjm_get_label_singular( true ),
							mdjm_get_event_contract_id( $_GET['event_id'] ),
							mdjm_get_event_long_date( $_GET['event_id'] )
						) . '</p>';
    			echo '</div>';
			} else	{
				echo '<div class="notice notice-error is-dismissible">';
        		echo '<p>' .
						sprintf(
							__( 'There are no employees available to work %s %s on %s', 'mobile-dj-manager' ),
							mdjm_get_label_singular( true ),
							mdjm_get_event_contract_id( $_GET['event_id'] ),
							mdjm_get_event_long_date( $_GET['event_id'] )
						) . '</p>';
    			echo '</div>';
			}
			
		}
	}

	settings_errors( 'mdjm-notices' );
	
} // mdjm_admin_messages
add_action( 'admin_notices', 'mdjm_admin_notices' );