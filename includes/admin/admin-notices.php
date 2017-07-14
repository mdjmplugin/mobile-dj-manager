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
	if ( mdjm_employee_can( 'manage_all_events' ) && ( mdjm_get_option( 'warn_unattended' ) ) )	{
		$unattended = MDJM()->events->mdjm_count_event_status( 'mdjm-unattended' );
		
		if( ! empty( $unattended ) && $unattended > 0 )	{
			echo '<div class="notice notice-info is-dismissible">';
			echo '<p>' .
					sprintf( 
						__( 'You have unattended enquiries. <a href="%s">Click here</a> to manage.', 'mobile-dj-manager' ),
						admin_url( 'edit.php?post_type=mdjm-event&post_status=mdjm-unattended' )
					). '</p>';
			echo '</div>';
		}
	}

	if( isset( $_GET['mdjm-message'] ) && 'upgrade-completed' == $_GET['mdjm-message'] )	{
		add_settings_error(
			'mdjm-notices',
			'mdjm-upgraded',
			__( 'MDJM Event Management has been upgraded successfully.', 'mobile-dj-manager' ),
			'updated'
		);
	}

	if( isset( $_GET['mdjm-message'] ) && 'song_added' == $_GET['mdjm-message'] )	{
		add_settings_error(
			'mdjm-notices',
			'mdjm-added-song',
			__( 'Entry added to playlist.', 'mobile-dj-manager' ),
			'updated'
		);
	}

	if( isset( $_GET['mdjm-message'] ) && 'adding_song_failed' == $_GET['mdjm-message'] )	{
		add_settings_error(
			'mdjm-notices',
			'mdjm-adding-song-failed',
			__( 'Could not add entry to playlist.', 'mobile-dj-manager' ),
			'error'
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
	
	if ( isset( $_GET['mdjm-message'] ) && 'song_remove_failed' == $_GET['mdjm-message'] )	{
		add_settings_error(
			'mdjm-notices',
			'mdjm-remove-faled',
			__( 'The songs count not be removed.', 'mobile-dj-manager' ),
			'error'
		);
	}
	
	if ( isset( $_GET['mdjm-message'] ) && 'security_failed' == $_GET['mdjm-message'] )	{
		add_settings_error(
			'mdjm-notices',
			'mdjm-security-failed',
			__( 'Security verification failed. Action not completed.', 'mobile-dj-manager' ),
			'error'
		);
	}
	
	if ( isset( $_GET['mdjm-message'] ) && 'playlist_emailed' == $_GET['mdjm-message'] )	{
		add_settings_error(
			'mdjm-notices',
			'mdjm-playlist-emailed',
			__( 'The playlist was emailed successfully.', 'mobile-dj-manager' ),
			'updated'
		);
	}
	
	if ( isset( $_GET['mdjm-message'] ) && 'playlist_email_failed' == $_GET['mdjm-message'] )	{
		add_settings_error(
			'mdjm-notices',
			'mdjm-playlist-email-failed',
			__( 'The playlist could not be emailed.', 'mobile-dj-manager' ),
			'error'
		);
	}
	
	if ( isset( $_GET['mdjm-message'] ) && 'employee_added' == $_GET['mdjm-message'] )	{
		add_settings_error(
			'mdjm-notices',
			'mdjm-employee_added',
			__( 'Employee added.', 'mobile-dj-manager' ),
			'updated'
		);
	}
	if ( isset( $_GET['mdjm-message'] ) && 'employee_add_failed' == $_GET['mdjm-message'] )	{
		add_settings_error(
			'mdjm-notices',
			'mdjm-employee_add-failed',
			__( 'Could not add employee.', 'mobile-dj-manager' ),
			'updated'
		);
	}
	if ( isset( $_GET['mdjm-message'] ) && 'employee_info_missing' == $_GET['mdjm-message'] )	{
		add_settings_error(
			'mdjm-notices',
			'mdjm-employee_info-missing',
			__( 'Insufficient information to create employee.', 'mobile-dj-manager' ),
			'error'
		);
	}
	if ( isset( $_GET['mdjm-message'] ) && 'comm_missing_content' == $_GET['mdjm-message'] )	{
		add_settings_error(
			'mdjm-notices',
			'mdjm-comm_content-missing',
			__( 'Not all required fields have been completed.', 'mobile-dj-manager' ),
			'error'
		);
	}
	if ( isset( $_GET['mdjm-message'] ) && 'comm_sent' == $_GET['mdjm-message'] )	{
		add_settings_error(
			'mdjm-notices',
			'mdjm-comm_sent',
			__( 'Email sent successfully.', 'mobile-dj-manager' ),
			'updated'
		);
	}
	if ( isset( $_GET['mdjm-message'] ) && 'comm_not_sent' == $_GET['mdjm-message'] )	{
		add_settings_error(
			'mdjm-notices',
			'mdjm-comm_not_sent',
			__( 'Email not sent.', 'mobile-dj-manager' ),
			'error'
		);
	}
	if ( isset( $_GET['mdjm-action'] ) && 'get_event_availability' == $_GET['mdjm-action'] )	{
		
		if ( ! wp_verify_nonce( $_GET[ 'mdjm_nonce' ], 'get_event_availability' ) )	{
			return;
		} elseif ( ! isset( $_GET['event_id'] ) )	{
			return;
		} else	{
			
			$date = get_post_meta( $_GET['event_id'], '_mdjm_event_date', true );
			
			$result = mdjm_do_availability_check( $date );
			
			if( ! empty( $result['available'] ) )	{

				$notice = '<ul>';

				foreach( $result['available'] as $employee_id )	{
					$notice .= '<li>' . sprintf( __( '<a href="%s" title="Assign &amp; Respond to Enquiry">Assign %s &amp; respond to enquiry</a>', 'mobile-dj-manager' ),
											add_query_arg(
							'primary_employee',
							$employee_id,
							get_edit_post_link( $_GET['event_id'] )
						),
						mdjm_get_employee_display_name( $employee_id )
					) .'</li>';

				}
				
				$notice .= '</ul>';

				echo '<div class="notice notice-info is-dismissible">';
        		echo '<p>' .
						sprintf(
							__( 'You have %d employees available to work %s %s on %s.', 'mobile-dj-manager' ),
							count( $result['available'] ),
							mdjm_get_label_singular( true ),
							mdjm_get_event_contract_id( $_GET['event_id'] ),
							mdjm_get_event_long_date( $_GET['event_id'] )
						) .
						$notice . '</p>';
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
	if ( isset( $_GET['mdjm-message'] ) && 'payment_event_missing' == $_GET['mdjm-message'] )	{
		add_settings_error(
			'mdjm-notices',
			'mdjm-payment_event_missing',
			__( 'Event not identified.', 'mobile-dj-manager' ),
			'error'
		);
	}
	if ( isset( $_GET['mdjm-message'] ) && 'pay_employee_failed' == $_GET['mdjm-message'] )	{
		add_settings_error(
			'mdjm-notices',
			'mdjm-payment_employee_failed',
			__( 'Unable to make payment to employee.', 'mobile-dj-manager' ),
			'error'
		);
	}
	if ( isset( $_GET['mdjm-message'] ) && 'pay_all_employees_failed' == $_GET['mdjm-message'] )	{
		add_settings_error(
			'mdjm-notices',
			'mdjm-payment_employees_failed',
			__( 'Unable to make payment to employees.', 'mobile-dj-manager' ),
			'error'
		);
	}
	if ( isset( $_GET['mdjm-message'] ) && 'pay_all_employees_some_success' == $_GET['mdjm-message'] )	{
		add_settings_error(
			'mdjm-notices',
			'mdjm-payment_all_employees_some_success',
			__( 'Not all employees could be paid.', 'mobile-dj-manager' ),
			'notice-info'
		);
	}
	if ( isset( $_GET['mdjm-message'] ) && 'pay_employee_success' == $_GET['mdjm-message'] )	{
		add_settings_error(
			'mdjm-notices',
			'mdjm-payment_employeee_success',
			__( 'Employee successfully paid.', 'mobile-dj-manager' ),
			'updated'
		);
	}
	if ( isset( $_GET['mdjm-message'] ) && 'pay_all_employees_success' == $_GET['mdjm-message'] )	{
		add_settings_error(
			'mdjm-notices',
			'mdjm-payment_all_employeees_success',
			__( 'Employees successfully paid.', 'mobile-dj-manager' ),
			'updated'
		);
	}
	if ( isset( $_GET['mdjm-message'] ) && 'unattended_enquiries_rejected_success' == $_GET['mdjm-message'] )	{
		add_settings_error(
			'mdjm-notices',
			'mdjm-unattended_enquiries_rejected_success',
			sprintf( _n( '%1$s %2$s successfully rejected.', '%1$s %3$s successfully rejected.', $_GET['mdjm-count'], 'mobile-dj-manager' ),
				$_GET['mdjm-count'],
				mdjm_get_label_singular(),
				mdjm_get_label_plural()
			),
			'updated'
		);
	}
	if ( isset( $_GET['mdjm-message'] ) && 'unattended_enquiries_rejected_failed' == $_GET['mdjm-message'] )	{
		add_settings_error(
			'mdjm-notices',
			'mdjm-unattended_enquiries_rejected_failed',
			__( 'Errors were encountered.', 'mobile-dj-manager' ),
			'error'
		);
	}
	if ( isset( $_GET['mdjm-message'] ) && 'api-key-generated' == $_GET['mdjm-message'] )	{
		add_settings_error(
			'mdjm-notices',
			'mdjm-api-key-generated',
			__( 'API keys generated.', 'mobile-dj-manager' ),
			'updated'
		);
	}
	if ( isset( $_GET['mdjm-message'] ) && 'api-key-regenerated' == $_GET['mdjm-message'] )	{
		add_settings_error(
			'mdjm-notices',
			'mdjm-api-key-regenerated',
			__( 'API keys re-generated.', 'mobile-dj-manager' ),
			'updated'
		);
	}
	if ( isset( $_GET['mdjm-message'] ) && 'api-key-revoked' == $_GET['mdjm-message'] )	{
		add_settings_error(
			'mdjm-notices',
			'mdjm-api-key-revoked',
			__( 'API keys revoked.', 'mobile-dj-manager' ),
			'updated'
		);
	}
	if ( isset( $_GET['mdjm-message'] ) && 'api-key-failed' == $_GET['mdjm-message'] )	{
		add_settings_error(
			'mdjm-notices',
			'mdjm-api-key-failed',
			__( 'Generating API keys failed.', 'mobile-dj-manager' ),
			'error'
		);
	}
	if ( isset( $_GET['mdjm-message'] ) && 'task-status-updated' == $_GET['mdjm-message'] )	{
		add_settings_error(
			'mdjm-notices',
			'mdjm-task-status-updated',
			__( 'Task status updated.', 'mobile-dj-manager' ),
			'updated'
		);
	}
	if ( isset( $_GET['mdjm-message'] ) && 'task-status-update-failed' == $_GET['mdjm-message'] )	{
		add_settings_error(
			'mdjm-notices',
			'mdjm-task-status-update-failed',
			__( 'Task status could not be updated.', 'mobile-dj-manager' ),
			'error'
		);
	}
	if ( isset( $_GET['mdjm-message'] ) && 'task-run' == $_GET['mdjm-message'] )	{
		add_settings_error(
			'mdjm-notices',
			'mdjm-task-run',
			__( 'Task executed successfully.', 'mobile-dj-manager' ),
			'updated'
		);
	}
	if ( isset( $_GET['mdjm-message'] ) && 'task-run-failed' == $_GET['mdjm-message'] )	{
		add_settings_error(
			'mdjm-notices',
			'mdjm-run-failed',
			__( 'Task could not be executed.', 'mobile-dj-manager' ),
			'error'
		);
	}
	if ( isset( $_GET['mdjm-message'] ) && 'task-updated' == $_GET['mdjm-message'] )	{
		add_settings_error(
			'mdjm-notices',
			'mdjm-task-updated',
			__( 'Task updated.', 'mobile-dj-manager' ),
			'updated'
		);
	}
	if ( isset( $_GET['mdjm-message'] ) && 'task-update-failed' == $_GET['mdjm-message'] )	{
		add_settings_error(
			'mdjm-notices',
			'mdjm-update-failed',
			__( 'Task update failed.', 'mobile-dj-manager' ),
			'error'
		);
	}
	if ( isset( $_GET['mdjm-message'] ) && 'settings-imported' == $_GET['mdjm-message'] )	{
		add_settings_error(
			'mdjm-notices',
			'mdjm-settings-imported',
			__( 'Settings sucessfully imported.', 'mobile-dj-manager' ),
			'updated'
		);
	}

	settings_errors( 'mdjm-notices' );
	
} // mdjm_admin_messages
add_action( 'admin_notices', 'mdjm_admin_notices' );
