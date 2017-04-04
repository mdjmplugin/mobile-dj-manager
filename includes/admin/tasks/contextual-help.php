<?php
/**
 * Contextual Help
 *
 * @package     KBS
 * @subpackage  Admin/Customers
 * @copyright   Copyright (c) 2017, Mike Howard
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) )
	exit;

/**
 * Customers contextual help.
 *
 * @since       1.0
 * @return      void
 */
function kbs_customers_contextual_help() {
	$screen = get_current_screen();

	if ( 'kbs_ticket_page_kbs-customers' != $screen->id || ! isset( $_GET['view'] ) )	{
		return;
	}

	$article_singular = kbs_get_article_label_singular();
	$article_plural   = kbs_get_article_label_plural();
	$ticket_singular  = kbs_get_ticket_label_singular();
	$ticket_plural    = kbs_get_ticket_label_plural();

	$screen->set_help_sidebar(
		'<p><strong>' . __( 'More Information:', 'kb-support' ) . '</strong></p>' .
		'<p>' . sprintf( 
			__( '<a href="%s" target="_blank">Documentation</a>', 'kb-support' ), 
			esc_url( 'https://kb-support.com/support/' )
		) . '</p>' .
		'<p>' . sprintf( 
			__( '<a href="%s" target="_blank">Twitter</a>', 'kb-support' ), 
			esc_url( 'https://twitter.com/kbsupport_wp/' )
		) . '</p>' .
		'<p>' . sprintf( 
			__( '<a href="%s" target="_blank">Facebook</a>', 'kb-support' ), 
			esc_url( 'https://www.facebook.com/kbsupport/' )
		) . '</p>' .
		'<p>' . sprintf(
			__( '<a href="%s" target="_blank">Post an issue</a> on <a href="%s" target="_blank">GitHub</a>', 'kb-support' ),
			esc_url( 'https://github.com/KB-Support/kb-support/issues' ),
			esc_url( 'https://github.com/KB-Support/kb-support' )
		) . '</p>' .
		'<p>' . sprintf(
			__( '<a href="%s" target="_blank">Extensions</a>', 'kb-support' ),
			esc_url( 'https://kb-support.com/extensions/' )
		) . '</p>'
	);

	if ( 'add' == $_GET['view'] )	{

		do_action( 'kbs_before_customer_add_contextual_help' );
		$screen->add_help_tab( array(
			'id'      => 'kbs-customer-add',
			'title'   => __( 'Add Customer', 'kb-support' ),
			'content' =>
				'<p>' . __( 'To add a new customer, simply enter their name and email address before clicking <em>Add Customer</em>.', 'kb-support' ) . '</p>'
		) );

	} else	{

		do_action( 'kbs_customer_before_profile_contextual_help' );
		$screen->add_help_tab( array(
			'id'      => 'kbs-customer-profile',
			'title'   => __( 'Customer Profile', 'kb-support' ),
			'content' =>
				'<p>' . __( 'You can view and edit your customers profile here.', 'kb-support' ) . '</p>' .
				'<p>' . __( 'Click on the <em>Edit Customer</em> link to reveal a number of input fields that you can complete to fill the customer profile. You can also attach the customer account to a WordPress user account that is already registered on your website by entering the relevant username where specified.', 'kb-support' ) . '</p>' .
				'<p>' . sprintf(
					__( '<strong>Customer Emails</strong> - All of the customers associated email addresses are displayed within this table. Adding additional email addresses for the customer will enable them to log %1$s with any of their associated email addresses, and have the %2$s still associated to their account. Use the relevant action links to remove additional email addresses or set them as the customers primary address.', 'kb-support' ),
					strtolower( $ticket_plural ),
					strtolower( $ticket_singular )
				) . '</p>' .
				'<p>' . sprintf(
					__( '<strong>Recent %1$s</strong> - An overview of all the customers %2$s are displayed here.', 'kb-support' ),
					$ticket_plural,
					strtolower( $ticket_plural )
				) . '</p>'
		) );
	
		do_action( 'kbs_customer_before_notes_contextual_help' );
		$screen->add_help_tab( array(
			'id'      => 'kbs-customer-notes',
			'title'   => __( 'Customer Notes', 'kb-support' ),
			'content' =>
				'<p>' . __( 'Enter notes regarding your customer here. These notes are not visible to the customers themselves.', 'kb-support' ) . '</p>' .
				'<p>' . __( 'Under the textarea that enables you to add a new note, existing notes are displayed.', 'kb-support' )  . '</p>'
		) );
	
		do_action( 'kbs_customer_before_delete_contextual_help' );
		$screen->add_help_tab( array(
			'id'      => 'kbs-customer-delete',
			'title'   => __( 'Delete Customer', 'kb-support' ),
			'content' =>
				'<p>' . __( 'This tab enables you to delete a customer from the database.', 'kb-support' ) . '</p>' .
				'<p>' . __( 'To proceed select the <em>Are you sure you want to delete this customer?</em> checkbox to enable the <em>Delete Customer</em> button. Click the button to delete the customer.', 'kb-support' ) . '</p>'
		) );

	}
	do_action( 'kbs_customers_contextual_help' );

} // kbs_customers_contextual_help
add_action( 'load-kbs_ticket_page_kbs-customers', 'kbs_customers_contextual_help' );
