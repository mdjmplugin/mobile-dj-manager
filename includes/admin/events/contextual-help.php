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
 * @access      private
 * @since       1.4
 * @return      void
 */
function mdjm_events_contextual_help() {
	$screen = get_current_screen();
		
	if ( $screen->id != 'mdjm-event' )
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
					esc_url( 'https://github.com/mydjplanner/mobile-dj-manager/issues' ),
					esc_url( 'https://github.com/mydjplanner/mobile-dj-manager/' )
				) . '</p>' .
		'<p>' . sprintf(
					__( 'View <a href="%s">add-ons</a>.', 'mobile-dj-manager' ),
					esc_url( 'http://mdjm.co.uk/add-ons/' )
				) . '</p>'
	);

	$screen->add_help_tab( array(
		'id'	    => 'mdjm-event-options',
		'title'	    => sprintf( __( '%s Options', 'mobile-dj-manager' ), mdjm_get_label_singular() ),
		'content'	=>
			'<p>' . __( '<strong>Event Status</strong> - Define how many times customers are allowed to download their purchased files. Leave at 0 for unlimited. Resending the purchase receipt will permit the customer one additional download if their limit has already been reached.', 'easy-digital-downloads' ) . '</p>' .

			'<p>' . __( '<strong>Event Type</strong> - If enabled, define an individual SKU or product number for this download.', 'easy-digital-downloads' ) . '</p>' .

			'<p>' . __( '<strong>Event Contract</strong> - Disable the automatic output of the purchase button. If disabled, no button will be added to the download page unless the <code>[purchase_link]</code> shortcode is used.', 'easy-digital-downloads' ) . '</p>' .
			'<p>' . __( '<strong>Disable Client Update Emails?</strong> - Disable the automatic output of the purchase button. If disabled, no button will be added to the download page unless the <code>[purchase_link]</code> shortcode is used.', 'easy-digital-downloads' ) . '</p>' .
			'<p>' . __( '<strong>Reset Client Password</strong> - Disable the automatic output of the purchase button. If disabled, no button will be added to the download page unless the <code>[purchase_link]</code> shortcode is used.', 'easy-digital-downloads' ) . '</p>' .
			'<p>' . __( '<strong>Email Quote Template</strong> - Disable the automatic output of the purchase button. If disabled, no button will be added to the download page unless the <code>[purchase_link]</code> shortcode is used.', 'easy-digital-downloads' ) . '</p>' .
			'<p>' . __( '<strong>Online Quote Template</strong> - Disable the automatic output of the purchase button. If disabled, no button will be added to the download page unless the <code>[purchase_link]</code> shortcode is used.', 'easy-digital-downloads' ) . '</p>' .
			'<p>' . __( '<strong>Booking Fee Paid?</strong> - Disable the automatic output of the purchase button. If disabled, no button will be added to the download page unless the <code>[purchase_link]</code> shortcode is used.', 'easy-digital-downloads' ) . '</p>' .
			'<p>' . __( '<strong>Balance Paid?</strong> - Disable the automatic output of the purchase button. If disabled, no button will be added to the download page unless the <code>[purchase_link]</code> shortcode is used.', 'easy-digital-downloads' ) . '</p>' .
			'<p>' . __( '<strong>Enable Event Playlist?</strong> - Disable the automatic output of the purchase button. If disabled, no button will be added to the download page unless the <code>[purchase_link]</code> shortcode is used.', 'easy-digital-downloads' ) . '</p>'
	) );

	$screen->add_help_tab( array(
		'id'	    => 'mdjm-settings-events',
		'title'	    => __( 'Events', 'mobile-dj-manager' ),
		'content'	=>
			'<p>' . __( 'This screen enables to you configure options events and playlists. Select your events default contract template, whether or not you are an employer and enable equipment packages.', 'mobile-dj-manager' ) . '</p>' .
			'<p>' . __( 'You can also toggle playlists on or off, select when a playlist should close choose whether or not to upload your playlists to the MDJM servers.', 'mobile-dj-manager' ) . '</p>'
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
		'id'	    => 'edd-settings-licenses',
		'title'	    => __( 'Licenses', 'mobile-dj-manager' ),
		'content'	=>
			'<p>' . sprintf(
						__( 'If you have any <a href="%s">MDJM Event Management paid add-ons</a> installed, this screen is where you should add the license to enable automatic updates whilst your license is valid.', 'mobile-dj-manager' ),
						esc_url( 'http://mdjm.co.uk/add-ons/' ) 
					) . '</p>'
	) );

	do_action( 'mdjm_events_contextual_help', $screen );
}
add_action( 'load-post.php', 'mdjm_events_contextual_help' );
add_action( 'load-post-new.php', 'mdjm_events_contextual_help' );