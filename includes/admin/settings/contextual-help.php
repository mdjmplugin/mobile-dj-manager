<?php
/**
 * Contextual Help
 *
 * @package     MDJM
 * @subpackage  Admin/Settings
 * @copyright   Copyright (c) 2015, Mike Howard
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.3
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) )
	exit;

/**
 * Settings contextual help.
 *
 * @since       1.3
 * @return      void
 */
function mdjm_settings_contextual_help() {
	$screen = get_current_screen();
		
	if ( $screen->id != 'mdjm-event_page_mdjm-settings' )
		return;

	$screen->set_help_sidebar(
		'<p><strong>' . __( 'For more information:', 'mobile-dj-manager' ) . '</strong></p>' .
		'<p>' . sprintf( 
					__( 'Visit the <a href="%s">documentation</a> on the MDJM Event Management website.', 'mobile-dj-manager' ), 
					esc_url( 'http://mdjm.co.uk/support/' )
				) . '</p>' .
		'<p>' . sprintf( 
					__( 'Join our <a href="%s">Facebook Group</a>.', 'mobile-dj-manager' ), 
					esc_url( 'https://www.facebook.com/groups/mobiledjmanager/' )
				) . '</p>' .
		'<p>' . sprintf(
					__( '<a href="%s">Post an issue</a> on <a href="%s">GitHub</a>.', 'mobile-dj-manager' ),
					esc_url( 'https://github.com/mdjm/mobile-dj-manager/issues' ),
					esc_url( 'https://github.com/mdjm/mobile-dj-manager/' )
				) . '</p>' .
		'<p>' . sprintf(
					__( 'View <a href="%s">add-ons</a>.', 'mobile-dj-manager' ),
					esc_url( 'http://mdjm.co.uk/add-ons/' )
				) . '</p>'
	);
	
	do_action( 'mdjm_pre_settings_contextual_help', $screen );

	$screen->add_help_tab( array(
		'id'	    => 'mdjm-settings-general',
		'title'	    => __( 'General', 'mobile-dj-manager' ),
		'content'	=> '<p>' . __( 'This screen provides the most basic settings for configuring MDJM. Set your company name and preferred date and time format.', 'mobile-dj-manager' ) . '</p>'
	) );

	$screen->add_help_tab( array(
		'id'	    => 'mdjm-settings-events',
		'title'	    => mdjm_get_label_plural(),
		'content'	=>
			'<p>' . sprintf( __( 'This screen enables to you configure options %1$s and playlists. Select your %1$s default contract template, whether or not you are an employer and enable equipment packages.', 'mobile-dj-manager' ) . '</p>' .
			'<p>' . __( 'You can also toggle playlists on or off, select when a playlist should close choose whether or not to upload your playlists to the MDJM servers.', 'mobile-dj-manager' ),
						mdjm_get_label_plural( true ) ) . '</p>'
	) );

	$screen->add_help_tab( array(
		'id'	    => 'mdjm-settings-emails-templates',
		'title'	    => __( 'Emails &amp; Templates', 'mobile-dj-manager' ),
		'content'	=>
			'<p>' . __( 'This screen allows you to adjust options for emails, toggle on or off the email tracking feature and select which templates to use as content for emails.', 'mobile-dj-manager' ) . '</p>'
	) );

	$screen->add_help_tab( array(
		'id'	    => 'mdjm-settings-client-zone',
		'title'	    => mdjm_get_option( 'app_name', __( 'Client Zone', 'mobile-dj-manager' ) ),
		'content'	=> '<p>' . sprintf( __( "This screen allows you to configure settings associated with the %s as well as set various pages and configure the Availability Checker.", 'mobile-dj-manager' ), mdjm_get_option( 'app_name', __( 'Client Zone', 'mobile-dj-manager' ) ) ) . '</p>'
	) );

	$screen->add_help_tab( array(
		'id'	    => 'mdjm-settings-payments',
		'title'	    => __( 'Payments', 'mobile-dj-manager' ),
		'content'	=>
			'<p>' . __( 'This screen allows you to configure the payment settings. Specify your currency, format currency display, set default deposits and select whether or not to apply tax.', 'mobile-dj-manager' ) . '</p>'
	) );

	$screen->add_help_tab( array(
		'id'		=> 'mdjm-settings-extensions',
		'title'		=> __( 'Extensions', 'mobile-dj-manager' ),
		'content'	=> '<p>' . __( 'This screen provides access to settings added by most MDJM Event Management extensions.', 'mobile-dj-manager' ) . '</p>'
	) );

	$screen->add_help_tab( array(
		'id'	    => 'mdjm-settings-licenses',
		'title'	    => __( 'Licenses', 'mobile-dj-manager' ),
		'content'	=>
			'<p>' . sprintf(
						__( 'If you have any <a href="%s">MDJM Event Management paid add-ons</a> installed, this screen is where you should add the license to enable automatic updates whilst your license is valid.', 'mobile-dj-manager' ),
						esc_url( 'http://mdjm.co.uk/add-ons/' ) 
					) . '</p>'
	) );

	do_action( 'mdjm_post_settings_contextual_help', $screen );
}
add_action( 'load-mdjm-event_page_mdjm-settings', 'mdjm_settings_contextual_help' );
