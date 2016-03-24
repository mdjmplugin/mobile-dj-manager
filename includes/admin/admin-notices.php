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
function mdjm_admin_messages() {
	global $mdjm_options;

	// Unattended events
	if( mdjm_employee_can( '', 'manage_all_events' ) && ! empty( mdjm_get_option( 'warn_unattended' ) ) )	{
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
				'update-nag'
			);
	}

	settings_errors( 'mdjm-notices' );
} // mdjm_admin_messages
add_action( 'admin_notices', 'mdjm_admin_messages' );
