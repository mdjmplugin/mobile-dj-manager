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
 * Communications contextual help.
 *
 * @since       1.3
 * @return      void
 */
function mdjm_comms_email_contextual_help() {

	$screen = get_current_screen();
		
	if ( $screen->id != 'mdjm-event_page_mdjm-comms' )	{
		return;
	}

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

	do_action( 'mdjm_pre_comms_email_contextual_help', $screen );

	$screen->add_help_tab( array(
		'id'	    => 'mdjm-comm-email',
		'title'	    => __( 'Communications', 'mobile-dj-manager' ),
		'content'	=>
			'<p>' . sprintf( 
						__( '<strong>Select a Recipient</strong> - Choose from the dropdown list who your email is to. Users are grouped into Clients and Employees. Once you have selected a recipient the Associated %s list will be updated with their active %s. This is a required field.', 'mobile-dj-manager' ), 
						mdjm_get_label_plural(), mdjm_get_label_plural( true )
					) . '</p>' .
			'<p>' . __( '<strong>Subject</strong> - Enter the subject of your email. If you select a template the subject will be updated to the title of the template. This is a required field.', 'mobile-dj-manager' ) . '</p>' .
			'<p>' . __( '<strong>Copy Yourself?</strong> - Select this option if you wish to receive a copy of the email. If the settings options have been enabled to copy Admin and/or Employee into Client emails, you may receive a copy regardless of whether or not this option is selected.', 'mobile-dj-manager' ) . '</p>' .
			'<p>' . __( '<strong>Select a Template</strong> - Choose a pre-defined email or contract template to populate the content field. Anything you have already entered into the content field will be overwritten. If you do not select a template, you will need to manually enter content into the content field.', 'mobile-dj-manager' ) . '</p>' .
			'<p>' . sprintf( __( '<strong>Associated %s</strong> - If the Client or Employee you have selected within the <strong>Select a Recipient</strong> field has active %s it is displayed here. Select it to tell MDJM that the email you are sending is associated to this %s and %1$s content tags can be used within the email content.', 'mobile-dj-manager' ),
						mdjm_get_label_singular(), mdjm_get_label_plural( true ), mdjm_get_label_singular( true ) ) . '</p>' .
			'<p>' . __( '<strong>Attach a File</strong> - Enables you to select a file from your computer to the email.', 'mobile-dj-manager' ) . '</p>' .
			'<p>' . __( '<strong>Content</strong> - If you have selected a template within the <strong>Select a Template</strong> field, this field will be populated with that templates content. You can adjust this content as required. Alternatively, if no template is selected, use this as a free text field for your email content. Content tags are supported and can be entered via the <strong>MDJM</strong> button on the text editor toolbar. Remember this field is resizeable. Drag from the bottom right hand corner to make bigger if necessary. This is a required field.', 'mobile-dj-manager' ) . '</p>'
	) );

	do_action( 'mdjm_post_comms_email_contextual_help', $screen );

} // mdjm_comms_email_contextual_help
add_action( 'load-mdjm-event_page_mdjm-comms', 'mdjm_comms_email_contextual_help' );
