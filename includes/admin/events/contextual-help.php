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
 * Events contextual help.
 *
 * @since       1.3
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
					esc_url( 'https://github.com/mdjm/mobile-dj-manager/issues' ),
					esc_url( 'https://github.com/mdjm/mobile-dj-manager/' )
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
			'<p>' . sprintf( 
						__( '<strong>%1$s Status</strong> - Set the status of this %1$s. An description of each status can be found <a href="%2$s" target="_blank">here</a>', 'mobile-dj-manager' ), 
						mdjm_get_label_singular(), 'http://mdjm.co.uk/docs/event-statuses/'
					) . '</p>' .
			'<p>' . sprintf( 
						__( '<strong>%1$s Status</strong> - Set the type of %1$s <em>i.e. Wedding or 40th Birthday</em>. You can define the types <a href="%s">here</a>.', 'mobile-dj-manager' ),
						mdjm_get_label_singular(),
						admin_url( 'edit-tags.php?taxonomy=event-types&post_type=mdjm-event' )
					) . '</p>' .
			'<p>' . sprintf( __( '<strong>%1$s Contract</strong> - Select the contract associated with this %1$s.', 'mobile-dj-manager' ), mdjm_get_label_singular() ) . '</p>' .
			'<p>' . __( '<strong>Email Quote Template</strong> - During transition to Enquiry status, select which quote email template should be sent to the client.', 'mobile-dj-manager' ) . '</p>' .
			'<p>' . __( '<strong>Online Quote Template</strong> - During transition to Enquiry status, select which quote template should be used to generate the page that displays the online quote.', 'mobile-dj-manager' ) . '</p>' .
			'<p>' . sprintf(
						__( '<strong>%1$s Paid?</strong> - Select this option if the client has paid their %1$s.', 'mobile-dj-manager' ),
						mdjm_get_deposit_label()
					) . '</p>' .
			'<p>' . sprintf(
						__( '<strong>%1$s Paid?</strong> - Select this option if the client has paid their %1$s.', 'mobile-dj-manager' ),
						mdjm_get_balance_label() 
					). '</p>' .
			'<p>' . sprintf( 
						__( '<strong>Enable %1$s Playlist?</strong> - Toggle whether or not the client can manage the playlist for this %1$s.', 'mobile-dj-manager' ), mdjm_get_label_singular() ) . '</p>'
	) );

	$screen->add_help_tab( array(
		'id'	    => 'mdjm-settings-client-details',
		'title'	    => __( 'Client Details', 'mobile-dj-manager' ),
		'content'	=>
			'<p>' . sprintf( __( "Select a client for this %s. If the client does not exist, you can select <em>Add New Client</em>. In doing so, additional fields will be displayed enabling you to enter the new client's details.", 'mobile-dj-manager' ), mdjm_get_label_singular( true ) ) . '</p>' .
            '<p>' . __( '<strong>Disable Client Update Emails?</strong> - Selecting this option will stop any emails being sent to the client during update.', 'mobile-dj-manager' ) . '</p>' .
			'<p>' . __( "<strong>Reset Client Password</strong> - If selected whilst transitioning to enquiry status, the client's password will be reset. If you insert the <code>{client_password}</code> content tag into your email template, the password will be inserted.", 'mobile-dj-manager' ) . '</p>'
	) );

	$screen->add_help_tab( array(
		'id'	    => 'mdjm-event-details',
		'title'	    => sprintf( __( '%s Details', 'mobile-dj-manager' ), mdjm_get_label_singular() ),
		'content'	=>
			'<p>' . sprintf( __( '<strong>%1$s Name</strong> - Assign a name for this %1$s. Can be viewed and adjusted by the client.', 'mobile-dj-manager' ), mdjm_get_label_singular() ) . '</p>' .
			'<p>' . sprintf( 
						__( '<strong>Select Primary Employee</strong> - Select the primary employee for this %s.', 'mobile-dj-manager' ),
						mdjm_get_label_singular( true )
					) . '</p>' .
			'<p>' . sprintf( 
						__( '<strong>%1$s Date</strong> - Use the datepicker to set the date for this %1s$s.', 'mobile-dj-manager' ),
						mdjm_get_label_singular()
					) . '</p>' .
			'<p>' . sprintf(
						__( '<strong>Start Time</strong> - Set the start time of the %s', 'mobile-dj-manager' ),
						mdjm_get_label_singular( true )
					) . '</p>' .
			'<p>' . sprintf( 
						__( '<strong>End Time</strong> - Set the end time of the %', 'mobile-dj-manager' ),
						mdjm_get_label_singular( true )
					) . '</p>' .
			'<p>' . sprintf(
						__( '<strong>Total Cost</strong> - Enter the total cost of the %s. If using equipment packages and add-ons, selecting these will automatically set this cost.', 'mobile-dj-manager' ),
						mdjm_get_label_singular( true )
					) . '</p>' .
			'<p>' . sprintf( 
						__( '<strong>%1$s</strong> - Enter the %1$s that needs to be collected for this %2$s upon contract signing. This field can be auto populated depending on your settings and equipment packages and add-on selections', 'mobile-dj-manager' ),
						mdjm_get_deposit_label(),
						mdjm_get_label_singular( true )
					) . '</p>' .
			'<p>' . sprintf(
						__( '<strong>Select an %1$s Package</strong> - If packages are enabled and defined you can assign one to the %1$s here. Doing so will auto update the <em>Total Cost</em> and <em>%2$s</em> fields. Additionally, the <em>Select Add-ons</em> options will be updated to exclude add-ons included within the selected package.', 'mobile-dj-manager' ),
						mdjm_get_label_singular(),
						mdjm_get_deposit_label()
					) . '</p>' .
			'<p>' . sprintf( 
						__( '<strong>Select Add-ons</strong> - If packages are enabled you can assign add-ons to your %s here. The <em>Total Cost</em> and <em>%s</em> fields will be updated automatically to reflect the new costs.', 'mobile-dj-manager' ),
						mdjm_get_label_singular( true ),
						mdjm_get_deposit_label()
					) . '</p>' .
			'<p>' . __( '<strong>Notes</strong> - Information entered here will be visible to the client and all event employees.', 'mobile-dj-manager' ) . '</p>'
	) );

	$screen->add_help_tab( array(
		'id'	    => 'mdjm-event-employees',
		'title'	    => sprintf( __( '%s Employees', 'mobile-dj-manager' ), mdjm_get_label_singular() ),
		'content'	=> 
			'<p>' . sprintf(
						__( 'Employees that are assigned to the %1$s are listed here together with their role and wage. You can add additional employees to the %1$s, select their %1$s role and allocate their wages.', 'mobile-dj-manager' ),
						mdjm_get_label_singular( true )
					) . '</p>'
	) );

	$screen->add_help_tab( array(
		'id'	    => 'mdjm-event-venue-details',
		'title'	    => __( 'Venue Details', 'mobile-dj-manager' ),
		'content'	=>
			'<p>' . __( 'Select the venue from the drop down. If the venue does not exist, you can specify it manually by selecting <em>Enter Manually</em> and completing the additional fields that are then displayed.', 'mobile-dj-manager' ) . '</p>' .
			'<p>' . __( 'Check the <em>Save this Venue</em> option to save the venue.', 'mobile-dj-manager' ) . '</p>'
	) );

	$screen->add_help_tab( array(
		'id'		=> 'mdjm-event-transactions',
		'title'		=> __( 'Transactions', 'mobile-dj-manager' ),
		'content'	=> 
			'<p>' . sprintf(
						__( 'This section allows you to add transactions associated with the %1$s as well as listing existing associated transactions.', 'mobile-dj-manager' ), mdjm_get_label_singular( true )
					) . '</p>' . 
			'<p>' . sprintf( 
						__( 'If transactions already exist, the total amount of income and expenditure is displayed as well as the total overall earnings so far for the %1$s.', 'mobile-dj-manager' ),
						mdjm_get_label_singular( true )
					) . '</p>'
	) );

	$screen->add_help_tab( array(
		'id'	    => 'mdjm-event-administration',
		'title'	    => __( 'Administration', 'mobile-dj-manager' ),
		'content'	=>
			'<p>' . __( '<strong>Enquiry Source</strong> - Select how the client heard about your business', 'mobile-dj-manager' ) . '</p>' .
			'<p>' . sprintf( 
						__( '<strong>Setup Date</strong> - Use the datepicker to select the date that you need to setup for this %s.', 'mobile-dj-manager' ),
						mdjm_get_label_singular( true )
					) . '</p>' .
			'<p>' . sprintf( 
						__( '<strong>Setup Time</strong> - Select the time that you need to setup for this %s.', 'mobile-dj-manager' ),
						mdjm_get_label_singular( true )
					) . '</p>' .
			'<p>' . __( '<strong>Employee Notes</strong> - Enter notes that are only visible by employees. Clients will not see these notes', 'mobile-dj-manager' ) . '</p>' .
			'<p>' . sprintf( 
						__( '<strong>Admin Notes</strong> - Enter notes that are only visible by admins. Employees and clients will not see these notes', 'mobile-dj-manager' ),
						mdjm_get_label_singular( true )
					) . '</p>'
	) );

	do_action( 'mdjm_events_contextual_help', $screen );
}
add_action( 'load-post.php', 'mdjm_events_contextual_help' );
add_action( 'load-post-new.php', 'mdjm_events_contextual_help' );
