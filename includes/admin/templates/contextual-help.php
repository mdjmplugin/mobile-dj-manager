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
 * Contracts contextual help.
 *
 * @since       1.3
 * @return      void
 */
function mdjm_contract_contextual_help() {
	$screen = get_current_screen();

	if ( $screen->id != 'contract' )	{
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

	do_action( 'mdjm_pre_contract_contextual_help', $screen );

	$screen->add_help_tab( array(
		'id'	    => 'mdjm-contract-add',
		'title'	    => __( 'Add New Template', 'mobile-dj-manager' ),
		'content'	=>
			'<p>' . __( '<strong>Title</strong> - Enter a title for your contract. A good title is short but descriptive of the type of contract.', 'mobile-dj-manager' ) . '</p>' .
			'<p>' . __( '<strong>Content</strong> - Enter the content for your template. HTML, images, and MDJM content tags are supported. Use the MDJM button on the content editor toolbar for easy access to the content tags.', 'mobile-dj-manager' ) . '</p>'
	) );

	$screen->add_help_tab( array(
		'id'	    => 'mdjm-contract-save',
		'title'	    => __( 'Save Contract', 'mobile-dj-manager' ),
		'content'	=>
			'<p>' . __( "Save a draft if you've still got content to add, click preview to see what your contract looks like when formatted and click Save Contract when you are ready to publish.", 'mobile-dj-manager' ) . '</p>'
	) );

	$screen->add_help_tab( array(
		'id'	    => 'mdjm-contract-details',
		'title'	    => sprintf( __( '%s Details', 'mobile-dj-manager' ), mdjm_get_label_singular() ),
		'content'	=>
			'<p>' . sprintf( __( 'Displays general information regarding this contract such as Author, whether it is the default contract used for %1$s, and the number of %1$s it is assigned to. Enter a description if necessary to describe the type of contract and for which type of %1$s it should be used. The description will not be seen by clients.', 'mobile-dj-manager' ), mdjm_get_label_plural( true ) ) . '</p>'
	) );

	do_action( 'mdjm_post_contract_contextual_help', $screen );
}
add_action( 'load-post.php', 'mdjm_contract_contextual_help' );
add_action( 'load-post-new.php', 'mdjm_contract_contextual_help' );

/**
 * Email Templates contextual help.
 *
 * @since       1.3
 * @return      void
 */
function mdjm_email_template_contextual_help() {
	$screen = get_current_screen();

	if ( $screen->id != 'email_template' )	{
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

	do_action( 'mdjm_pre_email_template_contextual_help', $screen );

	$screen->add_help_tab( array(
		'id'	    => 'mdjm-email-template-add',
		'title'	    => __( 'Add New Template', 'mobile-dj-manager' ),
		'content'	=>
			'<p>' . __( '<strong>Title</strong> - Enter a title for your email template. A good title is short but descriptive. Remember that the title is also used as the email subject.', 'mobile-dj-manager' ) . '</p>' .
			'<p>' . __( '<strong>Content</strong> - Enter the content for your email template. HTML, images, and MDJM content tags are supported. Use the MDJM button on the content editor toolbar for easy access to the content tags.', 'mobile-dj-manager' ) . '</p>' .
			'<p>' . __( "<strong>Publish</strong> - Save a draft if you've still got content to add, click preview to see what your email template looks like when formatted and click Publish when you are finished editing.", 'mobile-dj-manager' ) . '</p>'
	) );

	do_action( 'mdjm_post_email_template_contextual_help', $screen );
}
add_action( 'load-post.php', 'mdjm_email_template_contextual_help' );
add_action( 'load-post-new.php', 'mdjm_email_template_contextual_help' );
