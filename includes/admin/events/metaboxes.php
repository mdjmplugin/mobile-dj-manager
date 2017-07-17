<?php

/**
 * Contains all metabox functions for the mdjm-event post type
 *
 * @package		MDJM
 * @subpackage	Events
 * @since		1.3
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) )
	exit;

/**
 * Remove unwanted metaboxes to for the mdjm-event post type.
 * Apply the `mdjm_event_remove_metaboxes` filter to allow for filtering of metaboxes to be removed.
 *
 * @since	1.3
 * @param
 * @return
 */
function mdjm_remove_event_meta_boxes()	{
	$metaboxes = apply_filters( 'mdjm_event_remove_metaboxes',
		array(
			array( 'submitdiv', 'mdjm-event', 'side' ),
			array( 'event-typesdiv', 'mdjm-event', 'side' ),
			array( 'tagsdiv-enquiry-source', 'mdjm-event', 'side' )
		)
	);
	
	foreach( $metaboxes as $metabox )	{
		remove_meta_box( $metabox[0], $metabox[1], $metabox[2] );
	}
} // mdjm_remove_event_meta_boxes
add_action( 'admin_head', 'mdjm_remove_event_meta_boxes' );

/**
 * Define and add the metaboxes for the mdjm-event post type.
 * Apply the `mdjm_event_add_metaboxes` filter to allow for filtering of metaboxes and settings.
 * Uses function_exists to verify the callback function exists.
 *
 * @since	1.3
 * @param
 * @return
 */
function mdjm_add_event_meta_boxes( $post )	{

	global $mdjm_event, $mdjm_event_update;

	$save              = __( 'Create', 'mobile-dj-manager' );
	$mdjm_event_update = false;
	$mdjm_event        = new MDJM_Event( $post->ID );
	
	if ( 'draft' != $post->post_status && 'auto-draft' != $post->post_status )	{
		$mdjm_event_update = true;
	}

	$metaboxes = apply_filters( 'mdjm_event_add_metaboxes',
		array(
			array(
				'id'         => 'mdjm-event-options-mb',
				'title'      => sprintf( __( '%s Options', 'mobile-dj-manager' ), mdjm_get_label_singular() ),
				'callback'   => 'mdjm_event_metabox_options_callback',
				'context'    => 'side',
				'priority'   => 'high',
				'args'       => array(),
				'dependancy' => '',
				'permission' => ''
			),
			array(
				'id'         => 'mdjm-event-client-mb',
				'title'      => __( 'Client Details', 'mobile-dj-manager' ),
				'callback'   => 'mdjm_event_metabox_client_callback',
				'context'    => 'normal',
				'priority'   => 'high',
				'args'       => array(),
				'dependancy' => '',
				'permission' => ''
			),
			array(
				'id'         => 'mdjm-event-employees-mb',
				'title'      => sprintf( __( '%s Employees', 'mobile-dj-manager' ), mdjm_get_label_singular() ),
				'callback'   => 'mdjm_event_metabox_employees_callback',
				'context'    => 'normal',
				'priority'   => 'high',
				'args'       => array(),
				'dependancy' => '',
				'permission' => ''
			),
			array(
				'id'         => 'mdjm-event-details-mb',
				'title'      => sprintf( __( '%s Details', 'mobile-dj-manager' ), mdjm_get_label_singular() ),
				'callback'   => 'mdjm_event_metabox_details_callback',
				'context'    => 'normal',
				'priority'   => 'high',
				'args'       => array(),
				'dependancy' => '',
				'permission' => ''
			),
			array(
				'id'         => 'mdjm-event-venue-mb',
				'title'      => __( 'Venue Details', 'mobile-dj-manager' ),
				'callback'   => 'mdjm_event_metabox_venue_callback',
				'context'    => 'normal',
				'priority'   => '',
				'args'       => array(),
				'dependancy' => '',
				'permission' => ''
			),
			array(
				'id'         => 'mdjm-event-admin-mb',
				'title'      => __( 'Administration', 'mobile-dj-manager' ),
				'callback'   => 'mdjm_event_metabox_admin_callback',
				'context'    => 'normal',
				'priority'   => 'low',
				'args'       => array(),
				'dependancy' => '',
				'permission' => ''
			),
			array(
				'id'         => 'mdjm-event-transactions-mb',
				'title'      => __( 'Transactions', 'mobile-dj-manager' ),
				'callback'   => 'mdjm_event_metabox_transactions_callback',
				'context'    => 'normal',
				'priority'   => 'low',
				'args'       => array(),
				'dependancy' => '',
				'permission' => 'edit_txns'
			),
			array(
				'id'         => 'mdjm-event-history-mb',
				'title'      => sprintf( __( '%s History', 'mobile-dj-manager' ), mdjm_get_label_singular() ),
				'callback'   => 'mdjm_event_metabox_history_callback',
				'context'    => 'normal',
				'priority'   => 'low',
				'args'       => array(),
				'dependancy' => '',
				'permission' => 'manage_mdjm'
			)
		)
	);
	// Runs before metabox output
	do_action( 'mdjm_event_before_metaboxes' );
	
	// Begin metaboxes
	foreach( $metaboxes as $metabox )	{
		// Dependancy check
		if ( ! empty( $metabox['dependancy'] ) && $metabox['dependancy'] === false )	{
			continue;
		}
		
		// Permission check
		if ( ! empty( $metabox['permission'] ) && ! mdjm_employee_can( $metabox['permission'] ) )	{
			continue;
		}
		
		// Callback check
		if ( ! is_callable( $metabox['callback'] ) )	{
			continue;
		}
				
		add_meta_box(
			$metabox['id'],
			$metabox['title'],
			$metabox['callback'],
			'mdjm-event',
			$metabox['context'],
			$metabox['priority'],
			$metabox['args']
		);
	}
	
	// Runs after metabox output
	do_action( 'mdjm_event_after_metaboxes' );
} // mdjm_add_event_meta_boxes
add_action( 'add_meta_boxes_mdjm-event', 'mdjm_add_event_meta_boxes' );

/**
 * Output for the Event Options meta box.
 *
 * @since	1.3
 * @param	obj		$post	The post object (WP_Post).
 * @return
 */
function mdjm_event_metabox_options_callback( $post )	{

	global $post, $mdjm_event, $mdjm_event_update;

	?>

	<div class="submitbox" id="submitpost">
		<div id="minor-publishing">
        	<div id="minor-publishing-actions">
            
            </div><!-- #minor-publishing-actions -->
            <div id="mdjm-event-actions">
            	
                <?php
				/*
				 * Output the items for the options metabox
				 * These items go inside the mdjm-event-actions div
				 * @since	1.3.7
				 * @param	int	$post_id	The Event post ID
				 */
				do_action( 'mdjm_event_options_fields', $post->ID ); ?>
            </div><!-- #mdjm-event-actions -->
        </div><!-- #minor-publishing -->
    </div><!-- #submitpost -->
	<?php
	do_action( 'mdjm_event_options_fields_save', $post->ID );

} // mdjm_event_metabox_options_callback

/**
 * Output for the Client Details meta box.
 *
 * @since	1.3
 * @param	obj		$post	The post object (WP_Post).
 * @return
 */
function mdjm_event_metabox_client_callback( $post )	{

	global $post, $mdjm_event, $mdjm_event_update;

	wp_nonce_field( basename( __FILE__ ), 'save-event' . 'mdjm_event_nonce' );

	/*
	 * Output the items for the client metabox
	 * @since	1.3.7
	 * @param	int	$post_id	The Event post ID
	 */
	do_action( 'mdjm_event_client_fields', $post->ID );

} // mdjm_event_metabox_client_callback

/**
 * Output for the Employees meta box.
 *
 * @since	1.3
 * @param	obj		$post	The post object (WP_Post).
 * @return
 */
function mdjm_event_metabox_employees_callback( $post )	{

	global $post, $mdjm_event, $mdjm_event_update;

	/*
	 * Output the items for the employee metabox
	 * @since	1.3.7
	 * @param	int	$post_id	The Event post ID
	 */
	do_action( 'mdjm_event_employee_fields', $post->ID );

} // mdjm_event_metabox_employees_callback

/**
 * Output for the Event Details meta box.
 *
 * @since	1.3
 * @param	obj		$post	The post object (WP_Post).
 * @return
 */
function mdjm_event_metabox_details_callback( $post )	{

	global $post, $mdjm_event, $mdjm_event_update;

	/*
	 * Output the items for the event details metabox
	 * @since	1.3.7
	 * @param	int	$post_id	The Event post ID
	 */
	do_action( 'mdjm_event_details_fields', $post->ID );

} // mdjm_event_metabox_details_callback

/**
 * Output for the Event Venue meta box.
 *
 * @since	1.3
 * @param	obj		$post	The post object (WP_Post).
 * @return
 */
function mdjm_event_metabox_venue_callback( $post )	{

	global $post, $mdjm_event, $mdjm_event_update;

	/*
	 * Output the items for the event venue metabox
	 * @since	1.3.7
	 * @param	int	$post_id	The Event post ID
	 */
	do_action( 'mdjm_event_venue_fields', $post->ID );

} // mdjm_event_metabox_venue_callback

/**
 * Output for the Event Administration meta box.
 *
 * @since	1.3
 * @param	obj		$post	The post object (WP_Post).
 * @return
 */
function mdjm_event_metabox_admin_callback( $post )	{

	global $post, $mdjm_event, $mdjm_event_update;

	/*
	 * Output the items for the event admin metabox
	 * @since	1.3.7
	 * @param	int	$post_id	The Event post ID
	 */
	do_action( 'mdjm_event_admin_fields', $post->ID );

} // mdjm_event_metabox_admin_callback

/**
 * Output for the Event Details meta box.
 *
 * @since	1.3
 * @param	obj		$post	The post object (WP_Post).
 * @return
 */
function mdjm_event_metabox_history_callback( $post )	{

	global $post, $mdjm_event, $mdjm_event_update;

	/*
	 * Output the items for the event history metabox
	 * @since	1.3.7
	 * @param	int	$post_id	The Event post ID
	 */
	do_action( 'mdjm_event_history_fields', $post->ID );

} // mdjm_event_metabox_history_callback

/**
 * Output for the Event Transactions meta box.
 *
 * @since	1.3
 * @global	obj		$post				WP_Post object
 * @global	obj		$mdjm_event			MDJM_Event class object
 * @global	bool	$mdjm_event_update	True if this event is being updated, false if new
 * @param	obj		$post				The post object (WP_Post).
 * @return
 */
function mdjm_event_metabox_transactions_callback( $post )	{

	global $post, $mdjm_event, $mdjm_event_update;

	/*
	 * Output the items for the event transactions metabox
	 * @since	1.3.7
	 * @param	int	$post_id	The Event post ID
	 */
	do_action( 'mdjm_event_txn_fields', $post->ID );
									
} // mdjm_event_metabox_transactions_callback

/**
 * Output the event options type row
 *
 * @since	1.3.7
 * @global	obj		$mdjm_event			MDJM_Event class object
 * @global	bool	$mdjm_event_update	True if this event is being updated, false if new.
 * @param	int		$event_id			The event ID.
 * @return	str
 */
function mdjm_event_metabox_options_status_row( $event_id )	{

	global $mdjm_event, $mdjm_event_update;

	$contract        = $mdjm_event->get_contract();
	$contract_status = $mdjm_event->get_contract_status();

	?>

    <p>
        <label for="mdjm_event_status"><?php _e( 'Status:', 'mobile-dj-manager' ); ?></label>
        <?php echo MDJM()->html->event_status_dropdown( 'mdjm_event_status', $mdjm_event->post_status ); ?>
    </p>

	<?php

} // mdjm_event_metabox_options_status_row
add_action( 'mdjm_event_options_fields', 'mdjm_event_metabox_options_status_row', 10 );

/**
 * Output the event options email template row
 *
 * @since	1.3.7
 * @global	obj		$mdjm_event			MDJM_Event class object
 * @global	bool	$mdjm_event_update	True if this event is being updated, false if new.
 * @param	int		$event_id			The event ID.
 * @return	str
 */
function mdjm_event_metabox_options_email_templates_row( $event_id )	{

	global $mdjm_event, $mdjm_event_update;
	
	if ( $mdjm_event_update && $mdjm_event->post_status != 'mdjm-unattended' )	{
		return;
	}

	?>

	<p><strong><?php _e( 'Templates', 'mobile-dj-manager' ); ?></strong></p>

        <div id="mdjm-event-email-templates">
            <p><label for="mdjm_email_template"><?php _e( 'Quote:', 'mobile-dj-manager' ); ?></label>
                <?php echo MDJM()->html->select( array(
                    'name'     => 'mdjm_email_template',
                    'options'  => mdjm_list_templates( 'email_template' ),
                    'selected' => mdjm_get_option( 'enquiry' )
                ) ); ?></p>
        </div>
    <?php
	
	if ( ! mdjm_get_option( 'online_enquiry', false ) )	{
		return;
	}

	?>
    
    <p><label for="mdjm_email_template"><?php _e( 'Online:', 'mobile-dj-manager' ); ?></label>
		<?php echo MDJM()->html->select( array(
			'name'     => 'mdjm_online_quote',
			'options'  => mdjm_list_templates( 'email_template' ),
			'selected' => mdjm_get_option( 'online_enquiry' )
		) ); ?></p>
    <?php

} // mdjm_event_metabox_options_email_templates_row
add_action( 'mdjm_event_options_fields', 'mdjm_event_metabox_options_email_templates_row', 25 );

/**
 * Output the event options payments row
 *
 * @since	1.3.7
 * @global	obj		$mdjm_event			MDJM_Event class object
 * @global	bool	$mdjm_event_update	True if this event is being updated, false if new.
 * @param	int		$event_id			The event ID.
 * @return	str
 */
function mdjm_event_metabox_options_payments_row( $event_id )	{

	global $mdjm_event, $mdjm_event_update;
	
	if ( ! mdjm_employee_can( 'edit_txns' ) )	{
		return;
	}

	$deposit_status = __( 'Due', 'mobile-dj-manager' );
	$balance_status = __( 'Due', 'mobile-dj-manager' );
	
	if ( $mdjm_event_update && 'mdjm-unattended' != $mdjm_event->post_status )	{
		$deposit_status = $mdjm_event->get_deposit_status();
		$balance_status = $mdjm_event->get_balance_status();
	}

	?>

	<p><strong><?php _e( 'Payments', 'mobile-dj-manager' ); ?></strong></p>

    <p><?php echo MDJM()->html->checkbox( array(
			'name'     => 'deposit_paid',
			'value'    => 'Paid',
			'current'  => $deposit_status
		) ); ?> <?php printf( __( '%s Paid?', 'mobile-dj-manager' ), mdjm_get_deposit_label() ); ?></p>

	<p><?php echo MDJM()->html->checkbox( array(
			'name'     => 'balance_paid',
			'value'    => 'Paid',
			'current'  => $balance_status
		) ); ?> <?php printf( __( '%s Paid?', 'mobile-dj-manager' ), mdjm_get_balance_label() ); ?></p>

    <?php

} // mdjm_event_metabox_options_payments_row
add_action( 'mdjm_event_options_fields', 'mdjm_event_metabox_options_payments_row', 30 );

/**
 * Output the event options playlist row
 *
 * @since	1.3.7
 * @global	obj		$mdjm_event			MDJM_Event class object
 * @global	bool	$mdjm_event_update	True if this event is being updated, false if new.
 * @param	int		$event_id			The event ID.
 * @return	str
 */
function mdjm_event_metabox_options_playlist_row( $event_id )	{

	global $mdjm_event, $mdjm_event_update;

	$enable_playlist = mdjm_get_option( 'enable_playlists', true );

	if ( $mdjm_event_update )	{
		$enable_playlist = $mdjm_event->playlist_is_enabled();
	}

	?>

	<p><strong><?php printf( __( '%s Options', 'mobile-dj-manager' ), mdjm_get_label_singular() ); ?></strong></p>

    <p><?php echo MDJM()->html->checkbox( array(
			'name'     => '_mdjm_event_playlist',
			'value'    => 'Y',
			'current'  => $enable_playlist ? 'Y' : 0,
		) ); ?> <?php _e( 'Enable Playlist?', 'mobile-dj-manager' ); ?></p>

    <?php

} // mdjm_event_metabox_options_playlist_row
add_action( 'mdjm_event_options_fields', 'mdjm_event_metabox_options_playlist_row', 35 );

/**
 * Output the event options save row
 *
 * @since	1.3.7
 * @global	obj		$mdjm_event			MDJM_Event class object
 * @global	bool	$mdjm_event_update	True if this event is being updated, false if new.
 * @param	int		$event_id			The event ID.
 * @return	str
 */
function mdjm_event_metabox_options_save_row( $event_id )	{

	global $mdjm_event, $mdjm_event_update;

	if ( ! mdjm_employee_can( 'manage_events' ) )	{
		return;
	}

	$button_text = __( 'Add %s', 'mobile-dj-manager' );

	if ( $mdjm_event_update )	{
		$button_text = __( 'Update %s', 'mobile-dj-manager' );
	}

	$class = '';
	$url   = add_query_arg( array( 'post_type' => 'mdjm-event' ), admin_url( 'edit.php' ) );
	$a     = sprintf( __( 'Back to %s', 'mobile-dj-manager' ), mdjm_get_label_plural() );
	
	if ( mdjm_employee_can( 'manage_all_events' ) && ( ! $mdjm_event_update || $mdjm_event->post_status == 'mdjm-unattended' ) )	{
		$class = 'mdjm-delete';
		$url   = wp_nonce_url( add_query_arg( array( 'post' => $event_id, 'action' => 'trash' ), admin_url( 'post.php' ) ), 'trash-post_' . $event_id );
		$a     = sprintf( __( 'Delete %s', 'mobile-dj-manager' ), mdjm_get_label_singular() );
	}

	?>
	<div id="major-publishing-actions">
        <div id="delete-action">
            <a class="<?php echo $class; ?>" href="<?php echo $url; ?>"><?php echo $a; ?></a>
        </div>
        
        <div id="publishing-action">
            <?php
			submit_button( 
				sprintf( $button_text, mdjm_get_label_singular() ),
				'primary',
				'save',
				false,
				array( 'id' => 'save-post' )
			); ?>
        </div>
        <div class="clear"></div>
    </div>

	<?php

} // mdjm_event_metabox_options_save_row
add_action( 'mdjm_event_options_fields_save', 'mdjm_event_metabox_options_save_row', 40 );

/**
 * Output the event client row
 *
 * @since	1.3.7
 * @global	obj		$mdjm_event			MDJM_Event class object
 * @global	bool	$mdjm_event_update	True if this event is being updated, false if new.
 * @param	int		$event_id			The event ID.
 * @return	str
 */
function mdjm_event_metabox_client_select_row( $event_id )	{

	global $mdjm_event, $mdjm_event_update;

	?>
	<div class="mdjm_field_wrap mdjm_form_fields">
    	<div class="mdjm_col">
            <label for="client_name"><?php _e( 'Client:', 'mobile-dj-manager' ); ?></label> 
            <?php if ( mdjm_event_is_active( $event_id ) ) : ?>
    
                <?php $clients = mdjm_get_clients( 'client' ); ?>
                
                <?php echo MDJM()->html->client_dropdown( array(
                    'selected'         => $mdjm_event->client,
                    'class'            => '',
                    'roles'            => array( 'client' ),
                    'chosen'           => true,
                    'placeholder'      => __( 'Select a Client', 'mobile-dj-manager' ),
                    'null_value'       => array( '' => __( 'Select a Client', 'mobile-dj-manager' ) ),
                    'add_new'          => empty( $mdjm_event->client ) ? true : false,
                    'show_option_all'  => false,
                    'show_option_none' => false
                ) ); ?>
    
            <?php else : ?>
    
                <?php echo MDJM()->html->text( array(
                    'name'     => 'client_name_display',
                    'class'    => '',
                    'value'    => mdjm_get_client_display_name( $mdjm_event->client ),
                    'readonly' => true
                ) ); ?>
                
                <?php echo MDJM()->html->hidden( array(
                    'name'  => 'client_name',
                    'class' => '',
                    'value' =>$mdjm_event->client
                ) ); ?>
    
            <?php endif; ?>
            <?php if ( mdjm_employee_can( 'view_clients_list' ) && $mdjm_event_update && $mdjm_event->client ) : ?>
                <a id="toggle_client_details" class="mdjm-small mdjm-fake"><?php _e( 'Toggle Client Details', 'mobile-dj-manager' ); ?></a>
            <?php endif; ?>
        </div>
	</div>
	<?php

} // mdjm_event_metabox_client_select_row
add_action( 'mdjm_event_client_fields', 'mdjm_event_metabox_client_select_row', 10 );

/**
 * Output the event add new client row
 *
 * @since	1.3.7
 * @global	obj		$mdjm_event			MDJM_Event class object
 * @global	bool	$mdjm_event_update	True if this event is being updated, false if new.
 * @param	int		$event_id			The event ID.
 * @return	str
 */
function mdjm_event_metabox_client_add_new_row( $event_id )	{

	global $mdjm_event, $mdjm_event_update;

	if ( ! mdjm_employee_can( 'view_clients_list' ) )	{
		return;
	}

	?>
    <div id="mdjm-event-add-new-client-fields" class="mdjm-hidden">
		<?php do_action( 'mdjm_event_add_client_before_table' ); ?>
    	<table class="widefat mdjm_event_add_client_table mdjm_form_fields">
        	<thead>
            	<tr>
                	<th colspan="3"><?php _e( 'New Client Details', 'mobile-dj-manager' ); ?></th>
                </tr>
            </thead>
        	<tbody>
                <tr>
                    <td><label for="client_firstname"><?php _e( 'First Name:', 'mobile-dj-manager' ); ?></label><br />
                        <?php echo MDJM()->html->text( array(
							'name'  => 'client_firstname',
							'class' => ''
						) ); ?></td>

                    <td><label for="client_lastname"><?php _e( 'Last Name:', 'mobile-dj-manager' ); ?></label><br />
                         <?php echo MDJM()->html->text( array(
							'name'        => 'client_lastname',
							'class'       => '',
							'placeholder' => __( 'Optional', 'mobile-dj-manager' )
						) ); ?></td>
                </tr>
                <?php do_action( 'mdjm_event_add_client_after_name' ); ?>
                <tr>
                    <td colspan="2"><label for="client_email"><?php _e( 'Email Address:', 'mobile-dj-manager' ); ?></label><br />
                        <?php echo MDJM()->html->text( array(
							'name'  => 'client_email',
							'class' => 'regular-text',
							'type'  => 'email'
						) ); ?></td>
                </tr>
                <?php do_action( 'mdjm_event_add_client_after_email' ); ?>
                <tr>
                	<td><label for="client_phone"><?php _e( 'Phone:', 'mobile-dj-manager' ); ?></label><br />
                        <?php echo MDJM()->html->text( array(
							'name'        => 'client_phone',
							'class'       => '',
							'placeholder' => __( 'Optional', 'mobile-dj-manager' )
						) ); ?></td>

					<td><label for="client_phone2"><?php _e( 'Alt. Phone:', 'mobile-dj-manager' ); ?></label><br />
                         <?php echo MDJM()->html->text( array(
							'name'        => 'client_phone2',
							'class'       => '',
							'placeholder' => __( 'Optional', 'mobile-dj-manager' )
						) ); ?></td>
                </tr>
				<?php do_action( 'mdjm_event_add_client_after_phone' ); ?>
                <tr>
                	<td colspan="2">
                    	<a id="mdjm-add-client" class="button button-primary button-small"><?php _e( 'Add Client', 'mobile-dj-manager' ); ?></a>
                    </td>
                </tr>
            </tbody>
        </table>
        <?php do_action( 'mdjm_event_add_client_after_table' ); ?>
    </div>
    <?php

} // mdjm_event_metabox_client_add_new_row
add_action( 'mdjm_event_client_fields', 'mdjm_event_metabox_client_add_new_row', 15 );

/**
 * Output the event client details row
 *
 * @since	1.3.7
 * @global	obj		$mdjm_event			MDJM_Event class object
 * @global	bool	$mdjm_event_update	True if this event is being updated, false if new.
 * @param	int		$event_id			The event ID.
 * @return	str
 */
function mdjm_event_metabox_client_details_row( $event_id )	{

	global $mdjm_event, $mdjm_event_update;

	if ( ! mdjm_employee_can( 'view_clients_list' ) )	{
		return;
	}

	mdjm_do_client_details_table( $mdjm_event->client, $event_id );

} // mdjm_event_metabox_client_details_row
add_action( 'mdjm_event_client_fields', 'mdjm_event_metabox_client_details_row', 20 );

/**
 * Output the event options block emails row
 *
 * @since	1.3.7
 * @global	obj		$mdjm_event			MDJM_Event class object
 * @global	bool	$mdjm_event_update	True if this event is being updated, false if new.
 * @param	int		$event_id			The event ID.
 * @return	str
 */
function mdjm_event_metabox_client_options_row( $event_id )	{

	global $mdjm_event, $mdjm_event_update;

	$block_emails = false;

	if ( 'mdjm-enquiry' == $mdjm_event->post_status && ! mdjm_get_option( 'contract_to_client' ) )	{
		$block_emails = true;
	}
			
	if ( 'mdjm-enquiry' == $mdjm_event->post_status && ! mdjm_get_option( 'booking_conf_to_client' ) )	{
		$block_emails = true;
	}

	?>

    <p><?php echo MDJM()->html->checkbox( array(
        'name'     => 'mdjm_block_emails',
        'current'  => $block_emails
    ) ); ?> <label for="mdjm_block_emails"><?php _e( 'Disable Client Update Emails?', 'mobile-dj-manager' ); ?></label></p>
    
    <p><?php echo MDJM()->html->checkbox( array(
        'name'  => 'mdjm_reset_pw',
        'value' => 'Y'
    ) ); ?> <label for="mdjm_reset_pw"><?php _e( 'Reset Client Password?', 'mobile-dj-manager' ); ?></label></p>
    
    <?php

} // mdjm_event_metabox_client_options_row
add_action( 'mdjm_event_client_fields', 'mdjm_event_metabox_client_options_row', 20 );

/**
 * Output the event employee selection row
 *
 * @since	1.3.7
 * @global	obj		$mdjm_event			MDJM_Event class object
 * @global	bool	$mdjm_event_update	True if this event is being updated, false if new.
 * @param	int		$event_id			The event ID.
 * @return	str
 */
function mdjm_event_metabox_employee_select_row( $event_id )	{

	global $mdjm_event, $mdjm_event_update;

	$employee_id    = $mdjm_event->employee_id ? $mdjm_event->employee_id : get_current_user_id();
	$payment_status = $mdjm_event->employee_id ? mdjm_event_employees_paid( $event_id, $mdjm_event->employee_id ) : false;

	if ( isset( $_GET['primary_employee'] ) )	{
		$employee_id = $_GET['primary_employee'];
	}

	echo MDJM()->html->hidden( array(
		'name'  => 'event_dj',
		'value' => $employee_id
	) );

	?>

	<div class="mdjm_field_wrap mdjm_form_fields">
        <div class="mdjm_col col2">
			<label for="_mdjm_event_dj"><?php _e( 'Primary Employee:', 'mobile-dj-manager' ); ?></label><br />
				<?php if ( ! mdjm_is_employer() || ! mdjm_employee_can( 'manage_employees' ) || $payment_status ) : ?>

                    <?php echo MDJM()->html->text( array(
						'name'     => 'event_dj_display',
						'class'    => '',
						'value'    => mdjm_get_employee_display_name( $employee_id ),
						'readonly' => true
					) ); ?>

					<?php echo MDJM()->html->hidden( array(
						'name'     => '_mdjm_event_dj',
						'value'    => $employee_id
					) ); ?>

                <?php else : ?>

					<?php echo MDJM()->html->employee_dropdown( array(
                        'selected'    => $mdjm_event->employee_id,
                        'group'       => true,
						'chosen'      => true,
						'placeholder' => __( 'Select an Employee', 'mobile-dj-manager' )
                    ) ); ?>
            
                <?php endif; ?>

		</div>

		<?php if ( mdjm_get_option( 'enable_employee_payments' ) && mdjm_employee_can( 'edit_txns' ) ) : ?>

			<?php $wage = mdjm_get_employees_event_wage( $event_id, $employee_id ); ?>

			<div class="mdjm_col col2">
				<label for="_mdjm_event_dj_wage"><?php _e( 'Wage', 'mobile-dj-manager' ); ?>:</label><br />
                <?php echo mdjm_currency_symbol() . MDJM()->html->text( array(
					'name'        => '_mdjm_event_dj_wage',
					'class'       => 'mdjm-currency',
					'value'       => ! empty( $wage ) ? $wage : '',
					'placeholder' => mdjm_sanitize_amount( '0' ),
					'readonly'    => $payment_status ? true : false
				) ); ?>
			</div>

        <?php endif; ?>

    </div>

	<?php

} // mdjm_event_metabox_employee_select_row
add_action( 'mdjm_event_employee_fields', 'mdjm_event_metabox_employee_select_row', 10 );

/**
 * Output the event employee table
 *
 * @since	1.3.7
 * @global	obj		$mdjm_event			MDJM_Event class object
 * @global	bool	$mdjm_event_update	True if this event is being updated, false if new.
 * @param	int		$event_id			The event ID.
 * @return	str
 */
function mdjm_event_metabox_employee_table( $event_id )	{

	global $mdjm_event, $mdjm_event_update;

	?>

    <div id="mdjm-event-employee-list">
        <?php mdjm_do_event_employees_list_table( $event_id ); ?>

		<?php if ( mdjm_get_option( 'enable_employee_payments' ) && in_array( $mdjm_event->post_status, mdjm_get_option( 'employee_pay_status' ) ) && mdjm_employee_can( 'manage_txns' ) && ! mdjm_event_employees_paid( $event_id ) ) : ?>

        <div class="mdjm_field_wrap mdjm_form_fields">
        	<p><a href="<?php echo wp_nonce_url( add_query_arg( array( 'mdjm-action' => 'pay_event_employees', 'event_id' => $event_id ), admin_url( 'admin.php' ) ), 'pay_event_employees', 'mdjm_nonce' ); ?>" id="pay_event_employees" class="button button-primary button-small"><?php printf( __( 'Pay %s Employees', 'mobile-dj-manager' ), mdjm_get_label_singular() ); ?></a></p>
        </div>

	<?php endif; ?>

    </div>

	<?php

} // mdjm_event_metabox_details_time_row
add_action( 'mdjm_event_employee_fields', 'mdjm_event_metabox_employee_table', 20 );

/**
 * Output the event add employee fields
 *
 * @since	1.3.7
 * @global	obj		$mdjm_event			MDJM_Event class object
 * @global	bool	$mdjm_event_update	True if this event is being updated, false if new.
 * @param	int		$event_id			The event ID.
 * @return	str
 */
function mdjm_event_metabox_add_employee_fields( $event_id )	{

	global $mdjm_event, $mdjm_event_update;

	if ( ! mdjm_employee_can( 'manage_employees' ) || in_array( $mdjm_event->post_status, array(
		'mdjm-completed', 'mdjm-failed', 'mdjm-rejected' )
	) )	{
		return;
	}

	$employees = $mdjm_event->get_all_employees();
	$exclude   = false;

	if ( ! empty( $employees ) )	{
		foreach( $employees as $employee_id => $employee_data )	{
			$exclude[] = $employee_id;
		}
	}

	?>

    <div id="mdjm-event-add-employee-table" class="mdjm_field_wrap mdjm_form_fields">
        <table id="mdjm_event_add_employee_table" class="widefat mdjm_event_add_employee_table mdjm_form_fields">
        	<thead>
            	<tr>
            		<th colspan="3"><?php _e( 'Add Employees', 'mobile-dj-manager' ); ?> <a id="toggle_add_employee_fields" class="mdjm-small mdjm-fake"><?php _e( 'show form', 'mobile-dj-manager' ); ?></a></th>
                </tr>
            </thead>

			<tbody class="mdjm-hidden">
            	<tr>
                	<td><label for="event_new_employee"><?php _e( 'Employee', 'mobile-dj-manager' ); ?>:</label><br />
                    	<?php echo MDJM()->html->employee_dropdown( array(
							'name'        => 'event_new_employee',
							'exclude'     => $exclude,
							'group'       => true,
							'chosen'      => true,
							'placeholder' => __( 'Select an Employee', 'mobile-dj-manager' )
						) ); ?></td>

					<td><label for="event_new_employee_role"><?php _e( 'Role', 'mobile-dj-manager' ); ?>:</label><br />
                    	<?php echo MDJM()->html->roles_dropdown( array(
							'name'   => 'event_new_employee_role',
							'chosen' => true
						) ); ?></td>

					<td>
						<?php if ( mdjm_get_option( 'enable_employee_payments' ) && mdjm_employee_can( 'manage_txns' ) ) : ?>
                    		<label for="event_new_employee_wage"><?php _e( 'Wage', 'mobile-dj-manager' ); ?>:</label><br />
                            <?php echo mdjm_currency_symbol() . MDJM()->html->text( array(
								'name'        => 'event_new_employee_wage',
								'class'       => 'mdjm-currency',
								'placeholder' => mdjm_sanitize_amount( '0' )
							) ); ?>
                    	<?php endif; ?>
                    </td>
                </tr>

                <tr>
                	<td colspan="3"><a id="add_event_employee" class="button button-primary button-small"><?php _e( 'Add', 'mobile-dj-manager' ); ?></a></td>
                </tr>
            </tbody>

        </table>
	</div>
	<?php

} // mdjm_event_metabox_add_employee_fields
add_action( 'mdjm_event_employee_fields', 'mdjm_event_metabox_add_employee_fields', 30 );

/**
 * Output the event type and contract row
 *
 * @since	1.3.7
 * @global	obj		$mdjm_event			MDJM_Event class object
 * @global	bool	$mdjm_event_update	True if this event is being updated, false if new.
 * @param	int		$event_id			The event ID.
 * @return	str
 */
function mdjm_event_metabox_event_type_contract_row( $event_id )	{

	global $mdjm_event, $mdjm_event_update;

    $event_type = mdjm_get_event_type( $event_id, true );
	
	if ( ! $event_type )	{
		$event_type = mdjm_get_option( 'event_type_default', '' );
	}

    $contract        = $mdjm_event->get_contract();
	$contract_status = $mdjm_event->get_contract_status();

	?>

    <div class="mdjm_field_wrap mdjm_form_fields">
        <div class="mdjm_col col2">
            <label for="mdjm_event_type"><?php _e( 'Type:', 'mobile-dj-manager' ); ?></label><br>
            <?php echo MDJM()->html->event_type_dropdown( array(
                'name'     => 'mdjm_event_type',
                'selected' => $event_type
            ) ); ?>

            <?php if ( mdjm_is_admin() ) : ?>
                <i id="event-type-add" class="fa fa-plus" aria-hidden="true"></i>

                <div id="mdjm-new-event-type-row">
                    <?php echo MDJM()->html->text( array(
                        'name'        => 'event_type_name',
                        'placeholder' => sprintf( __( '%s Type Name', 'mobile-dj-manager' ), mdjm_get_label_singular() )
                    ) ); ?> 
                    <button id="add_event_type" class="button button-primary button-small"><?php _e( 'Add', 'mobile-dj-manager' ); ?></button></div>
                <span id="mdjm-event-type-loader" class="mdjm-loader mdjm-hidden"><img src="<?php echo MDJM_PLUGIN_URL . '/assets/images/loading.gif'; ?>" /></span>

            <?php endif; ?>
        </div>

         <div class="mdjm_col col2">
            <label for="_mdjm_event_contract"><?php _e( 'Contract:', 'mobile-dj-manager' ); ?></label><br />
            <?php if ( ! $contract_status ) : ?>
                <?php echo MDJM()->html->select( array(
                    'name'     => '_mdjm_event_contract',
                    'options'  => mdjm_list_templates( 'contract' ),
                    'selected' => ! empty( $contract ) ? $contract : mdjm_get_option( 'default_contract' )
                ) ); ?>
            <?php else : ?>
                <?php if ( mdjm_employee_can( 'manage_events' ) ) : ?>
                    <a id="view_contract" href="<?php echo esc_url( add_query_arg( array( 'mdjm_action' => 'review_contract', 'event_id' => $event_id ), home_url() ) ); ?>" target="_blank"><?php _e( 'Signed Contract', 'mobile-dj-manager' ); ?></a>
                <?php else : ?>
                    <?php _e( 'Contract is Signed', 'mobile-dj-manager' ); ?>
                <?php endif; ?>
            <?php endif; ?>
        </div>
	</div>
    <?php

} // mdjm_event_metabox_event_type_contract_row
add_action( 'mdjm_event_details_fields', 'mdjm_event_metabox_event_type_contract_row', 10 );

/**
 * Output the event name row
 *
 * @since	1.4.8
 * @global	obj		$mdjm_event			MDJM_Event class object
 * @global	bool	$mdjm_event_update	True if this event is being updated, false if new.
 * @param	int		$event_id			The event ID.
 * @return	str
 */
function mdjm_event_metabox_details_name_row( $event_id )	{

	global $mdjm_event, $mdjm_event_update;

	?>
    <div class="mdjm_field_wrap mdjm_form_fields">
		<div class="mdjm_col">
            <label for="_mdjm_event_name"><?php _e( 'Name:', 'mobile-dj-manager' ); ?></label><br />
			<?php echo MDJM()->html->text( array(
                'name'        => '_mdjm_event_name',
                'value'       => $mdjm_event->get_name(),
                'placeholder' => sprintf( __( 'Optional: Display name in %s', 'mobile-dj-manager' ), mdjm_get_option( 'app_name', __( 'Client Zone', 'mobile-dj-manager' ) ) )
            ) ); ?>
        </div>
	</div>    

    <?php

} // mdjm_event_metabox_details_name_row
add_action( 'mdjm_event_details_fields', 'mdjm_event_metabox_details_name_row', 20 );

/**
 * Output the event start date/time row
 *
 * @since	1.3.7
 * @global	obj		$mdjm_event			MDJM_Event class object
 * @global	bool	$mdjm_event_update	True if this event is being updated, false if new.
 * @param	int		$event_id			The event ID.
 * @return	str
 */
function mdjm_event_metabox_details_start_date_time_row( $event_id )	{

	global $mdjm_event, $mdjm_event_update;

    $finish_date = $mdjm_event->get_finish_date();

	mdjm_insert_datepicker(array(
		'id'       => 'display_event_date'
	) );

    mdjm_insert_datepicker( array(
		'id'       => 'display_event_finish_date',
		'altfield' => '_mdjm_event_end_date'
	) );

	?>
    <div class="mdjm_field_wrap mdjm_form_fields">
        <div class="mdjm_col col2">
            <label for="display_event_date"><?php printf( __( '%s Date:', 'mobile-dj-manager' ), mdjm_get_label_singular() ); ?></label><br />
            <?php echo MDJM()->html->text( array(
                'name'     => 'display_event_date',
                'class'    => 'mdjm_date',
                'required' => true,
                'value'    => ! empty( $mdjm_event->date ) ? mdjm_format_short_date( $mdjm_event->date ) : ''
            ) ); ?>
            <?php echo MDJM()->html->hidden( array(
                'name'  => '_mdjm_event_date',
                'value' => ! empty( $mdjm_event->date ) ? $mdjm_event->date : ''
            ) ); ?>
        </div>

        <div class="mdjm_col col2">
            <label for="display_event_end_date"><?php _e( 'End Date:', 'mobile-dj-manager' ); ?></label><br />
            <?php echo MDJM()->html->text( array(
                'name'     => 'display_event_finish_date',
                'class'    => 'mdjm_date',
                'required' => true,
                'value'    => ! empty( $finish_date ) ? mdjm_format_short_date( $finish_date ) : ''
            ) ); ?>
            <?php echo MDJM()->html->hidden( array(
                'name'  => '_mdjm_event_end_date',
                'value' => ! empty( $finish_date ) ? $finish_date : ''
            ) ); ?>
        </div>
	</div>

    <?php

} // mdjm_event_metabox_details_start_date_time_row
add_action( 'mdjm_event_details_fields', 'mdjm_event_metabox_details_start_date_time_row', 30 );

/**
 * Output the event time row
 *
 * @since	1.3.7
 * @global	obj		$mdjm_event			MDJM_Event class object
 * @global	bool	$mdjm_event_update	True if this event is being updated, false if new.
 * @param	int		$event_id			The event ID.
 * @return	str
 */
function mdjm_event_metabox_details_time_row( $event_id )	{

	global $mdjm_event, $mdjm_event_update;

    $start  = $mdjm_event->get_start_time();
	$finish = $mdjm_event->get_finish_time();
	$format = mdjm_get_option( 'time_format', 'H:i' );

	?>
    <div class="mdjm_field_wrap mdjm_form_fields">

        <div class="mdjm_col col2">
            <label for="event_start_hr"><?php _e( 'Start Time:', 'mobile-dj-manager' ); ?></label><br />
			<?php echo MDJM()->html->time_hour_select( array(
                'selected' => ! empty( $start ) ? date( $format[0], strtotime( $start ) ) : ''
            ) ); ?> 
            <?php echo MDJM()->html->time_minute_select( array(
                'selected' => ! empty( $start ) ? date( $format[2], strtotime( $start ) ) : ''
            ) ); ?> 
            <?php if ( 'H:i' != $format ) : ?>
                <?php echo MDJM()->html->time_period_select( array(
                    'selected' => ! empty( $start ) ? date( 'A', strtotime( $start ) ) : ''
                ) ); ?>
            <?php endif; ?>
        </div>

		<div class="mdjm_col col2">
        	<label for="event_finish_hr"><?php _e( 'End Time:', 'mobile-dj-manager' ); ?></label><br />
			<?php echo MDJM()->html->time_hour_select( array(
                'name'     => 'event_finish_hr',
                'selected' => ! empty( $finish ) ? date( $format[0], strtotime( $finish ) ) : ''
            ) ); ?> 
            <?php echo MDJM()->html->time_minute_select( array(
                'name'     => 'event_finish_min',
                'selected' => ! empty( $finish ) ? date( $format[2], strtotime( $finish ) ) : ''
            ) ); ?> 
            <?php if ( 'H:i' != $format ) : ?>
                <?php echo MDJM()->html->time_period_select( array(
                    'name'     => 'event_finish_period',
                    'selected' => ! empty( $finish ) ? date( 'A', strtotime( $finish ) ) : ''
                ) ); ?>
            <?php endif; ?>
        </div>
    </div>

    <?php
} // mdjm_event_metabox_details_time_row
add_action( 'mdjm_event_details_fields', 'mdjm_event_metabox_details_time_row', 40 );

/**
 * Output the event price row
 *
 * @since	1.3.7
 * @global	obj		$mdjm_event			MDJM_Event class object
 * @global	bool	$mdjm_event_update	True if this event is being updated, false if new.
 * @param	int		$event_id			The event ID.
 * @return	str
 */
function mdjm_event_metabox_details_price_row( $event_id )	{

	global $mdjm_event, $mdjm_event_update;

	if ( mdjm_employee_can( 'edit_txns' ) ) : ?>
		<span id="mdjm-price-loader" class="mdjm-loader mdjm-hidden"><img src="<?php echo MDJM_PLUGIN_URL . '/assets/images/loading.gif'; ?>" /></span>
        <div  id="mdjm-event-price-row" class="mdjm_field_wrap mdjm_form_fields">
            <div class="mdjm_col col2">
                <label for="_mdjm_event_deposit"><?php _e( 'Total Cost:', 'mobile-dj-manager' ); ?></label><br />
				<?php echo mdjm_currency_symbol() . MDJM()->html->text( array(
					'name'        => '_mdjm_event_cost',
					'class'       => 'mdjm-currency',
					'placeholder' => mdjm_sanitize_amount( '0.00' ),
					'value'       => ! empty( $mdjm_event->price ) ? mdjm_sanitize_amount( $mdjm_event->price ) : ''
				) ); ?>
            </div>

			<div class="mdjm_col col2">
                <label for="_mdjm_event_deposit"><?php _e( 'Deposit:', 'mobile-dj-manager' ); ?></label><br />
				<?php echo mdjm_currency_symbol() . MDJM()->html->text( array(
                    'name'        => '_mdjm_event_deposit',
                    'class'       => 'mdjm-currency',
                    'placeholder' => mdjm_sanitize_amount( '0.00' ),
                    'value'       => $mdjm_event_update ? mdjm_sanitize_amount( $mdjm_event->deposit ) : mdjm_calculate_deposit( $mdjm_event->price )
                ) ); ?>
            </div>
        </div>
    
	<?php else : ?>

        <?php echo MDJM()->html->hidden( array(
            'name'  => '_mdjm_event_cost',
            'value' => ! empty( $mdjm_event->price ) ? mdjm_sanitize_amount( $mdjm_event->price ) : ''
        ) ); ?>

        <?php echo MDJM()->html->hidden( array(
            'name'  => '_mdjm_event_deposit',
            'value' => $mdjm_event_update ? mdjm_sanitize_amount( $mdjm_event->deposit ) : ''
        ) ); ?>

    <?php endif;

} // mdjm_event_metabox_details_price_row
add_action( 'mdjm_event_details_fields', 'mdjm_event_metabox_details_price_row', 50 );

/**
 * Output the event packages row
 *
 * @since	1.3.7
 * @global	obj		$mdjm_event			MDJM_Event class object
 * @global	bool	$mdjm_event_update	True if this event is being updated, false if new.
 * @param	int		$event_id			The event ID.
 * @return	str
 */
function mdjm_event_metabox_details_packages_row( $event_id )	{

	global $mdjm_event, $mdjm_event_update;

	if ( ! mdjm_packages_enabled() )	{
		return;
	}

	$package    = $mdjm_event->get_package();
	$addons     = $mdjm_event->get_addons();
	$employee   = $mdjm_event->employee_id ? $mdjm_event->employee_id : get_current_user_id();
	$event_type = mdjm_get_event_type( $event_id, true );
	$event_date = $mdjm_event->date ? $mdjm_event->date : false;
	
	if ( ! $event_type )	{
		$event_type = mdjm_get_option( 'event_type_default', '' );
	}

	?>
    <span id="mdjm-equipment-loader" class="mdjm-loader mdjm-hidden"><img src="<?php echo MDJM_PLUGIN_URL . '/assets/images/loading.gif'; ?>" /></span>
    <div id="mdjm-event-equipment-row" class="mdjm_field_wrap mdjm_form_fields">
        <div class="mdjm_col col2">
            <label for="_mdjm_event_package"><?php _e( 'Package:', 'mobile-dj-manager' ); ?></label><br />
			<?php echo MDJM()->html->packages_dropdown( array(
                'employee'   => $employee,
				'event_type' => $event_type,
				'event_date' => $event_date,
                'selected'   => $package,
				'chosen'     => true
            ) ); ?>
		</div>

		<div class="mdjm_col col2">
            <span><label for="event_addons"><?php _e( 'Add-ons:', 'mobile-dj-manager' ); ?></label><br />
            <?php echo MDJM()->html->addons_dropdown( array(
                'selected'         => $addons,
                'show_option_none' => false,
                'show_option_all'  => false,
                'employee'         => $employee,
				'event_type'       => $event_type,
				'event_date'       => $event_date,
                'package'          => $package,
                'cost'             => true,
				'placeholder'      => __( 'Select Add-ons', 'mobile-dj-manager' ),
                'chosen'           => true,
                'data'             => array()
            ) ); ?></span>
		</div>
    </div>

    <?php

} // mdjm_event_metabox_details_packages_row
add_action( 'mdjm_event_details_fields', 'mdjm_event_metabox_details_packages_row', 60 );

/**
 * Output the event notes row
 *
 * @since	1.3.7
 * @global	obj		$mdjm_event			MDJM_Event class object
 * @global	bool	$mdjm_event_update	True if this event is being updated, false if new.
 * @param	int		$event_id			The event ID.
 * @return	str
 */
function mdjm_event_metabox_details_notes_row( $event_id )	{

	global $mdjm_event, $mdjm_event_update;

	?>
    <div class="mdjm_field_wrap mdjm_form_fields">
    	<?php echo MDJM()->html->textarea( array(
			'label'       => __( 'Notes:', 'mobile-dj-manager' ),
			'name'        => '_mdjm_event_notes',
			'placeholder' => __( 'Information entered here is visible by employees and clients', 'mobile-dj-manager' ),
			'value'       => esc_attr( $mdjm_event->get_meta( '_mdjm_event_notes' ) )
		) ); ?>
    </div>

	<?php

} // mdjm_event_metabox_details_notes_row
add_action( 'mdjm_event_details_fields', 'mdjm_event_metabox_details_notes_row', 70 );

/**
 * Output the event venue select row
 *
 * @since	1.3.7
 * @global	obj		$mdjm_event			MDJM_Event class object
 * @global	bool	$mdjm_event_update	True if this event is being updated, false if new.
 * @param	int		$event_id			The event ID.
 * @return	str
 */
function mdjm_event_metabox_venue_select_row( $event_id )	{

	global $mdjm_event, $mdjm_event_update;

	$venue_id = $mdjm_event->get_venue_id();

	if ( ! empty( $venue_id ) && $venue_id == $event_id )	{
		$venue_id = 'manual';
	}

	?>
	<div class="mdjm_field_wrap mdjm_form_fields">
    	<div class="mdjm_col">
            <label for="venue_id"><?php _e(' Select Venue:', 'mobile-dj-manager' ); ?></label> 
            <?php echo MDJM()->html->venue_dropdown( array(
                'name'        => 'venue_id',
                'selected'    => $venue_id,
                'placeholder' => __( 'Select a Venue', 'mobile-dj-manager' ),
                'chosen'      => true
            ) ); ?> 
            <a id="toggle_venue_details" class="mdjm-small mdjm-fake mdjm-hidden"><?php _e( 'Toggle Venue Details', 'mobile-dj-manager' ); ?></a>
        </div>
    </div>
	<?php
} // mdjm_event_metabox_venue_select_row
add_action( 'mdjm_event_venue_fields', 'mdjm_event_metabox_venue_select_row', 10 );

/**
 * Output the event venue details row
 *
 * @since	1.3.7
 * @global	obj		$mdjm_event			MDJM_Event class object
 * @global	bool	$mdjm_event_update	True if this event is being updated, false if new.
 * @param	int		$event_id			The event ID.
 * @return	str
 */
function mdjm_event_metabox_venue_details_row( $event_id )	{

	global $mdjm_event, $mdjm_event_update;

	mdjm_do_venue_details_table( $mdjm_event->get_venue_id(), $event_id );

} // mdjm_event_metabox_venue_details_row
add_action( 'mdjm_event_venue_fields', 'mdjm_event_metabox_venue_details_row', 20 );

/**
 * Output the event add new venue table
 *
 * @since	1.3.7
 * @global	obj		$mdjm_event			MDJM_Event class object
 * @global	bool	$mdjm_event_update	True if this event is being updated, false if new.
 * @param	int		$event_id			The event ID.
 * @return	str
 */
function mdjm_event_metabox_venue_add_new_table( $event_id )	{

	global $mdjm_event, $mdjm_event_update;

	$venue_name     = mdjm_get_event_venue_meta( $event_id, 'name' );
	$venue_contact  = mdjm_get_event_venue_meta( $event_id, 'contact' );
	$venue_email    = mdjm_get_event_venue_meta( $event_id, 'email' );
	$venue_address1 = mdjm_get_event_venue_meta( $event_id, 'address1' );
	$venue_address2 = mdjm_get_event_venue_meta( $event_id, 'address2' );
	$venue_town     = mdjm_get_event_venue_meta( $event_id, 'town' );
	$venue_county   = mdjm_get_event_venue_meta( $event_id, 'county' );
	$venue_postcode = mdjm_get_event_venue_meta( $event_id, 'postcode' );
	$venue_phone    = mdjm_get_event_venue_meta( $event_id, 'phone' );
	$employee_id    = ! empty( $mdjm_event->employee_id ) ? $mdjm_event->employee_id : '';

	$venue_address  = array( $venue_address1, $venue_address2, $venue_town, $venue_county, $venue_postcode );

	?>
    <div id="mdjm-event-add-new-venue-fields" class="mdjm-hidden">
    	<table class="widefat mdjm_event_add_venue_table mdjm_form_fields">
        	<thead>
            	<tr>
                	<th colspan="3"><?php _e( 'Venue Details', 'mobile-dj-manager' ); ?></th>
                </tr>
            </thead>
        	<tbody>
                <tr>
                    <td><label for="venue_name"><?php _e( 'Venue Name:', 'mobile-dj-manager' ); ?></label><br />
                        <?php echo MDJM()->html->text( array(
							'name'  => 'venue_name',
							'class' => '',
							'value' => ! empty( $venue_name ) ? $venue_name : ''
						) ); ?></td>

                    <td><label for="venue_contact"><?php _e( 'Contact Name:', 'mobile-dj-manager' ); ?></label><br />
                         <?php echo MDJM()->html->text( array(
							'name'        => 'venue_contact',
							'class'       => '',
							'value'       => ! empty( $venue_contact ) ? $venue_contact : '',
							'placeholder' => __( 'Optional', 'mobile-dj-manager' )
						) ); ?></td>

                    <td><label for="venue_email"><?php _e( 'Contact Email:', 'mobile-dj-manager' ); ?></label><br />
                        <?php echo MDJM()->html->text( array(
							'name'        => 'venue_email',
							'class'       => '',
							'type'        => 'email',
							'value'       => ! empty( $venue_email ) ? $venue_email : '',
							'placeholder' => __( 'Optional', 'mobile-dj-manager' )
						) ); ?></td>
                </tr>
                
                <tr>
                	<td><label for="venue_address1"><?php _e( 'Address Line 1:', 'mobile-dj-manager' ); ?></label><br />
                        <?php echo MDJM()->html->text( array(
							'name'        => 'venue_address1',
							'class'       => '',
							'value'       => ! empty( $venue_address1 ) ? $venue_address1 : '',
							'placeholder' => __( 'Optional', 'mobile-dj-manager' )
						) ); ?></td>

					<td><label for="venue_address2"><?php _e( 'Address Line 2:', 'mobile-dj-manager' ); ?></label><br />
                         <?php echo MDJM()->html->text( array(
							'name'        => 'venue_address2',
							'class'       => '',
							'value'       => ! empty( $venue_address2 ) ? $venue_address2 : '',
							'placeholder' => __( 'Optional', 'mobile-dj-manager' )
						) ); ?></td>
                    <td><label for="venue_town"><?php _e( 'Town:', 'mobile-dj-manager' ); ?></label><br />
                         <?php echo MDJM()->html->text( array(
							'name'        => 'venue_town',
							'class'       => '',
							'value'       => ! empty( $venue_town ) ? $venue_town : '',
							'placeholder' => __( 'Optional', 'mobile-dj-manager' )
						) ); ?></td>
                </tr>

				<tr>
                	<td><label for="venue_county"><?php _e( 'County:', 'mobile-dj-manager' ); ?></label><br />
                        <?php echo MDJM()->html->text( array(
							'name'        => 'venue_county',
							'class'       => '',
							'value'       => ! empty( $venue_county ) ? $venue_county : '',
							'placeholder' => __( 'Optional', 'mobile-dj-manager' )
						) ); ?></td>

					<td><label for="venue_postcode"><?php _e( 'Postcode:', 'mobile-dj-manager' ); ?></label><br />
                         <?php echo MDJM()->html->text( array(
							'name'        => 'venue_postcode',
							'class'       => '',
							'value'       => ! empty( $venue_postcode ) ? $venue_postcode : '',
							'placeholder' => __( 'Optional', 'mobile-dj-manager' )
						) ); ?></td>
                    <td><label for="venue_phone"><?php _e( 'Phone:', 'mobile-dj-manager' ); ?></label><br />
                         <?php echo MDJM()->html->text( array(
							'name'        => 'venue_phone',
							'class'       => '',
							'value'       => ! empty( $venue_phone ) ? $venue_phone : '',
							'placeholder' => __( 'Optional', 'mobile-dj-manager' )
						) ); ?></td>
                </tr>

				<?php if ( mdjm_employee_can( 'add_venues' ) ) : ?>
                    <tr id="mdjm-save-venue-button-row">
                        <td colspan="3">
                            <a id="mdjm-save-venue" class="button button-primary button-small"><?php _e( 'Save Venue', 'mobile-dj-manager' ); ?></a>
                        </td>
                    </tr>
                <?php endif; ?>

				<?php do_action( 'mdjm_venue_details_table_after_save', $event_id ); ?>
                <?php do_action( 'mdjm_venue_details_travel_data', $venue_address, $employee_id ); ?>

            </tbody>
        </table>
    </div>
    <?php
} // mdjm_event_metabox_venue_add_new_table
add_action( 'mdjm_event_venue_fields', 'mdjm_event_metabox_venue_add_new_table', 30 );

/**
 * Output the event travel costs hidden fields
 *
 * @since	1.4
 * @global	obj		$mdjm_event			MDJM_Event class object
 * @global	bool	$mdjm_event_update	True if this event is being updated, false if new.
 * @param	int		$event_id			The event ID.
 * @return	str
 */
function mdjm_event_metabox_travel_costs_fields( $event_id )	{

	global $mdjm_event, $mdjm_event_update;

	$travel_fields = mdjm_get_event_travel_fields();

	foreach( $travel_fields as $field ) : ?>
    	<?php $travel_data = mdjm_get_event_travel_data( $event_id, $field ); ?>
    	<?php $value = ! empty( $travel_data ) ? $travel_data : ''; ?>
		<input type="hidden" name="travel_<?php echo $field; ?>" id="mdjm_travel_<?php echo $field; ?>" value="<?php echo $value; ?>" />
    <?php endforeach;

} // mdjm_event_metabox_travel_costs_fields
add_action( 'mdjm_event_venue_fields', 'mdjm_event_metabox_travel_costs_fields', 40 );

/**
 * Output the event enquiry source row
 *
 * @since	1.3.7
 * @global	obj		$mdjm_event			MDJM_Event class object
 * @global	bool	$mdjm_event_update	True if this event is being updated, false if new.
 * @param	int		$event_id			The event ID.
 * @return	str
 */
function mdjm_event_metabox_admin_enquiry_source_row( $event_id )	{

	global $mdjm_event, $mdjm_event_update;

	$enquiry_source = mdjm_get_enquiry_source( $event_id );

	?>
	<div class="mdjm_field_wrap mdjm_form_fields">
    	<div class="mdjm_col">
            <label for="mdjm_enquiry_source"><?php _e( 'Enquiry Source:', 'mobile-dj-manager' ); ?></label>
            <?php echo MDJM()->html->enquiry_source_dropdown(
                'mdjm_enquiry_source',
                $enquiry_source ? $enquiry_source->term_id : ''
            ); ?>
        </div>
    </div>
	<?php
} // mdjm_event_metabox_admin_enquiry_source_row
add_action( 'mdjm_event_admin_fields', 'mdjm_event_metabox_admin_enquiry_source_row', 10 );

/**
 * Output the employee setup row
 *
 * @since	1.3.7
 * @global	obj		$mdjm_event			MDJM_Event class object
 * @global	bool	$mdjm_event_update	True if this event is being updated, false if new.
 * @param	int		$event_id			The event ID.
 * @return	str
 */
function mdjm_event_metabox_admin_dj_setup_row( $event_id )	{

	global $mdjm_event, $mdjm_event_update;

	mdjm_insert_datepicker(
		array(
			'id'       => 'dj_setup_date',
			'altfield' => '_mdjm_event_djsetup'
		)
	);

	$setup_date = $mdjm_event->get_setup_date();
	$setup_time = $mdjm_event->get_setup_time();
	$format = mdjm_get_option( 'time_format', 'H:i' );

	?>
	<div class="mdjm_field_wrap mdjm_form_fields">
    	<div class="mdjm_col col2">
            <label for="dj_setup_date"><?php _e( 'Setup Date:', 'mobile-dj-manager' ); ?></label><br />
            <?php echo MDJM()->html->text( array(
                'name'  => 'dj_setup_date',
                'class' => 'mdjm_setup_date',
                'value' => $setup_date ? mdjm_format_short_date( $setup_date ) : ''
            ) ); ?>

            <?php echo MDJM()->html->hidden( array(
                'name'  => '_mdjm_event_djsetup',
                'value' => $setup_date ? $setup_date : ''
            ) ); ?>
        </div>

		<div class="mdjm_col col2">
        	<label for="dj_setup_hr"><?php _e( 'Setup Time:', 'mobile-dj-manager' ); ?></label><br />
			<?php echo MDJM()->html->time_hour_select( array(
                'name'     => 'dj_setup_hr',
                'selected' => ! empty( $setup_time ) ? date( $format[0], strtotime( $setup_time ) ) : ''
            ) ); ?> 
            <?php echo MDJM()->html->time_minute_select( array(
                'name'     => 'dj_setup_min',
                'selected' => ! empty( $setup_time ) ? date( $format[2], strtotime( $setup_time ) ) : ''
            ) ); ?> 
            <?php if ( 'H:i' != $format ) : ?>
                <?php echo MDJM()->html->time_period_select( array(
                    'name'     => 'dj_setup_period',
                    'selected' => ! empty( $setup_time ) ? date( 'A', strtotime( $setup_time ) ) : ''
                ) ); ?>
            <?php endif; ?>
		</div>
    </div>

	<?php
} // mdjm_event_metabox_admin_dj_setup_row
add_action( 'mdjm_event_admin_fields', 'mdjm_event_metabox_admin_dj_setup_row', 20 );

/**
 * Output the employee notes row
 *
 * @since	1.3.7
 * @global	obj		$mdjm_event			MDJM_Event class object
 * @global	bool	$mdjm_event_update	True if this event is being updated, false if new.
 * @param	int		$event_id			The event ID.
 * @return	str
 */
function mdjm_event_metabox_admin_employee_notes_row( $event_id )	{

	global $mdjm_event, $mdjm_event_update;

	?>
	<div class="mdjm_field_wrap mdjm_form_fields">
		<?php echo MDJM()->html->textarea( array(
			'label'       => sprintf( __( '%s Notes:', 'mobile-dj-manager' ), mdjm_get_option( 'artist' ) ),
			'name'        => '_mdjm_event_dj_notes',
			'placeholder' => __( 'This information is not visible to clients', 'mobile-dj-manager' ),
			'value'       => get_post_meta( $event_id, '_mdjm_event_dj_notes', true )
		) ); ?>
    </div>

	<?php
} // mdjm_event_metabox_admin_employee_notes_row
add_action( 'mdjm_event_admin_fields', 'mdjm_event_metabox_admin_employee_notes_row', 30 );

/**
 * Output the admin notes row
 *
 * @since	1.3.7
 * @global	obj		$mdjm_event			MDJM_Event class object
 * @global	bool	$mdjm_event_update	True if this event is being updated, false if new.
 * @param	int		$event_id			The event ID.
 * @return	str
 */
function mdjm_event_metabox_admin_notes_row( $event_id )	{

	global $mdjm_event, $mdjm_event_update;

	if ( ! mdjm_is_admin() )	{
		return;
	}

	?>
	<div class="mdjm_field_wrap mdjm_form_fields">
		<?php echo MDJM()->html->textarea( array(
			'label'       => __( 'Admin Notes:', 'mobile-dj-manager' ),
			'name'        => '_mdjm_event_admin_notes',
			'placeholder' => __( 'This information is only visible to admins', 'mobile-dj-manager' ),
			'value'       => get_post_meta( $event_id, '_mdjm_event_admin_notes', true )
		) ); ?>
    </div>

	<?php
} // mdjm_event_metabox_admin_notes_row
add_action( 'mdjm_event_admin_fields', 'mdjm_event_metabox_admin_notes_row', 40 );

/**
 * Output the event transaction list table
 *
 * @since	1.3.7
 * @global	obj		$mdjm_event			MDJM_Event class object
 * @global	bool	$mdjm_event_update	True if this event is being updated, false if new.
 * @param	int		$event_id			The event ID.
 * @return	str
 */
function mdjm_event_metabox_txn_list_table( $event_id )	{

	global $mdjm_event, $mdjm_event_update;

	?>
	<p><strong><?php _e( 'All Transactions', 'mobile-dj-manager' ); ?></strong> <span class="mdjm-small">(<a id="mdjm_txn_toggle" class="mdjm-fake"><?php _e( 'toggle', 'mobile-dj-manager' ); ?></a>)</span></p>
	<div id="mdjm_event_txn_table" class="mdjm_meta_table_wrap">
        <?php mdjm_do_event_txn_table( $event_id ); ?>
	</div>
	<?php
} // mdjm_event_metabox_txn_list_table
add_action( 'mdjm_event_txn_fields', 'mdjm_event_metabox_txn_list_table', 10 );

/**
 * Output the event transaction list table
 *
 * @since	1.3.7
 * @global	obj		$mdjm_event			MDJM_Event class object
 * @global	bool	$mdjm_event_update	True if this event is being updated, false if new.
 * @param	int		$event_id			The event ID.
 * @return	str
 */
function mdjm_event_metabox_txn_add_new_row( $event_id )	{

	global $mdjm_event, $mdjm_event_update;
	
	mdjm_insert_datepicker(
		array(
			'id'		=> 'mdjm_txn_display_date',
			'altfield'	=> 'mdjm_txn_date',
			'maxdate'	=> 'today'
		)
	);

	?>

	<div id="mdjm-event-add-txn-table">
        <table id="mdjm_event_add_txn_table" class="widefat mdjm_event_add_txn_table mdjm_form_fields">
        	<thead>
            	<tr>
            		<th colspan="3"><?php _e( 'Add Transaction', 'mobile-dj-manager' ); ?> <a id="toggle_add_txn_fields" class="mdjm-small mdjm-fake"><?php _e( 'show form', 'mobile-dj-manager' ); ?></a></th>
                </tr>
            </thead>

			<tbody class="mdjm-hidden">
            	<tr>
                	<td><label for="mdjm_txn_amount"><?php _e( 'Amount:', 'mobile-dj-manager' ); ?></label><br />
                    	<?php echo mdjm_currency_symbol() .
						MDJM()->html->text( array(
							'name'        => 'mdjm_txn_amount',
							'class'       => 'mdjm-input-currency',
							'placeholder' => mdjm_sanitize_amount( '10' )
						) ); ?></td>

					<td><label for="mdjm_txn_display_date"><?php _e( 'Date:', 'mobile-dj-manager' ); ?></label><br />
						<?php echo MDJM()->html->text( array(
							'name'  => 'mdjm_txn_display_date',
							'class' => ''
						) ) .
						MDJM()->html->hidden( array(
							'name' => 'mdjm_txn_date'
						) ); ?></td>

					<td><label for="mdjm_txn_amount"><?php _e( 'Direction:', 'mobile-dj-manager' ); ?></label><br />
                    	<?php echo MDJM()->html->select( array(
							'name'        => 'mdjm_txn_direction',
							'options'     => array(
								'In'      => __( 'Incoming', 'mobile-dj-manager' ),
								'Out'     => __( 'Outgoing', 'mobile-dj-manager' )
							),
							'show_option_all'  => false,
							'show_option_none' => false
						) ); ?></td>
                </tr>

				<tr>
                	<td><span id="mdjm_txn_from_container"><label for="mdjm_txn_from"><?php _e( 'From:', 'mobile-dj-manager' ); ?></label><br />
                    	<?php echo MDJM()->html->text( array(
							'name'        => 'mdjm_txn_from',
							'class'       => '',
							'placeholder' => __( 'Leave empty if client', 'mobile-dj-manager' )
						) ); ?></span>
                        <span id="mdjm_txn_to_container" class="mdjm-hidden"><label for="mdjm_txn_to"><?php _e( 'To:', 'mobile-dj-manager' ); ?></label><br />
                    	<?php echo MDJM()->html->text( array(
							'name'        => 'mdjm_txn_to',
							'class'       => '',
							'placeholder' => __( 'Leave empty if client', 'mobile-dj-manager' )
						) ); ?></span></td>

					<td><label for="mdjm_txn_for"><?php _e( 'For:', 'mobile-dj-manager' ); ?></label><br />
						<?php echo MDJM()->html->txn_type_dropdown(); ?></td>

					<td><label for="mdjm_txn_src"><?php _e( 'Paid via:', 'mobile-dj-manager' ); ?></label><br />
                    	<?php echo MDJM()->html->select( array(
							'name'             => 'mdjm_txn_src',
							'options'          => mdjm_get_txn_source(),
							'selected'         => mdjm_get_option( 'default_type', 'Cash' ),
							'show_option_all'  => false,
							'show_option_none' => false
						) ); ?></td>
                </tr>

				<?php if ( mdjm_get_option( 'manual_payment_cfm_template' ) ) : ?>

                    <tr id="mdjm-txn-email">
                        <td colspan="3"><?php echo MDJM()->html->checkbox( array( 
                            'name'     => 'mdjm_manual_txn_email',
                            'current'  => mdjm_get_option( 'manual_payment_cfm_template' ) ? true : false,
                            'class'    => 'mdjm-checkbox'
                            ) ); ?>
                            <?php _e( 'Send manual payment confirmation email?', 'mobile-dj-manager' ); ?></td>
                    </tr>

				<?php endif; ?>

            </tbody>
        </table>

    </div>
    
    <p id="save-event-txn" class="mdjm-hidden"><a id="save_transaction" class="button button-primary button-small"><?php _e( 'Add Transaction', 'mobile-dj-manager' ); ?></a></p>
	<?php
} // mdjm_event_metabox_txn_add_new_row
add_action( 'mdjm_event_txn_fields', 'mdjm_event_metabox_txn_add_new_row', 20 );

/**
 * Output the event journal table
 *
 * @since	1.3.7
 * @global	obj		$mdjm_event			MDJM_Event class object
 * @global	bool	$mdjm_event_update	True if this event is being updated, false if new.
 * @param	int		$event_id			The event ID.
 * @return	str
 */
function mdjm_event_metabox_history_journal_table( $event_id )	{

	global $mdjm_event, $mdjm_event_update;

	$journals = mdjm_get_journal_entries( $event_id );

	$count  = count( $journals );
	$i      = 0;

	?>
	<div id="mdjm-event-journal-table">
    	<strong><?php _e( 'Recent Journal Entries', 'mobile-dj-manager' ); ?></strong> 
        <table class="widefat mdjm_event_journal_table mdjm_form_fields">
        	<thead>
                <tr>
                	<th style="width: 20%"><?php _e( 'Date', 'mobile-dj-manager' ); ?></th>
                    <th><?php _e( 'Excerpt', 'mobile-dj-manager' ); ?></th>
                </tr>
            </thead>

			<tbody>
            	<?php if ( $journals ) : ?>
                	<?php foreach( $journals as $journal ) : ?>
                        <tr>
                            <td><a href="<?php echo get_edit_comment_link( $journal->comment_ID ); ?>"><?php echo date( mdjm_get_option( 'time_format' ) . ' ' . mdjm_get_option( 'short_date_format' ), strtotime( $journal->comment_date ) ); ?></a></td>
                            <td><?php echo substr( $journal->comment_content, 0, 250 ); ?></td>
                        </tr>
						<?php $i++; ?>
                        
                        <?php if ( $i >= 3 ) break; ?>
                        
                    <?php endforeach; ?>
				<?php else : ?>
                <tr>
                    <td colspan="2"><?php printf( __( 'There are no journal entries associated with this %s', 'mobile-dj-manager' ), mdjm_get_label_singular( true ) ); ?></td>
                </tr>
                <?php endif; ?>

            </tbody>

			<?php if ( $journals ) : ?>
                <tfoot>
                	<tr>
                    	<td colspan="2"><span class="description">(<?php printf( __( 'Displaying the most recent %d entries of <a href="%s">%d total', 'mobile-dj-manager' ), ( $count >= 3 ) ? 3 : $count, add_query_arg( array( 'p' => $event_id ), admin_url( 'edit-comments.php?p=5636' ) ), $count ); ?>)</span></td>
                    </tr>
                </tfoot>
            <?php endif; ?>

        </table>
    </div>
	<?php
} // mdjm_event_metabox_history_journal_table
add_action( 'mdjm_event_history_fields', 'mdjm_event_metabox_history_journal_table', 10 );

/**
 * Output the event emails table
 *
 * @since	1.3.7
 * @global	obj		$mdjm_event			MDJM_Event class object
 * @global	bool	$mdjm_event_update	True if this event is being updated, false if new.
 * @param	int		$event_id			The event ID.
 * @return	str
 */
function mdjm_event_metabox_history_emails_table( $event_id )	{

	global $mdjm_event, $mdjm_event_update;

	if ( ! mdjm_get_option( 'track_client_emails' ) )	{
		return;
	}

	$emails = mdjm_event_get_emails( $event_id );
	$count  = count( $emails );
	$i      = 0;

	?>
	<div id="mdjm-event-emails-table">
    	<strong><?php _e( 'Associated Emails', 'mobile-dj-manager' ); ?></strong> 
        <table class="widefat mdjm_event_emails_table mdjm_form_fields">
        	<thead>
                <tr>
                	<th><?php _e( 'Date', 'mobile-dj-manager' ); ?></th>
                    <th><?php _e( 'Subject', 'mobile-dj-manager' ); ?></th>
                    <th><?php _e( 'Status', 'mobile-dj-manager' ); ?></th>
                </tr>
            </thead>

			<tbody>
            	<?php if ( $emails ) : ?>
                	<?php foreach( $emails as $email ) : ?>
                        <tr>
                            <td><?php echo date( mdjm_get_option( 'time_format' ) . ' ' . mdjm_get_option( 'short_date_format' ), strtotime( $email->post_date ) ); ?></td>
                            <td><a href="<?php echo get_edit_post_link( $email->ID ); ?>"><?php echo get_the_title( $email->ID ); ?></a></td>
                            <td><?php
                            echo get_post_status_object( $email->post_status )->label;
             
                            if ( ! empty( $email->post_modified ) && 'opened' == $email->post_status )	: ?>
                                <?php echo '<br />'; ?>
                                <span class="description"><?php echo date( mdjm_get_option( 'time_format', 'H:i' ) . ' ' . mdjm_get_option( 'short_date_format', 'd/m/Y' ), strtotime( $email->post_modified ) ); ?></span>
                            <?php endif; ?></td>
                        </tr>
						<?php $i++; ?>
                        
                        <?php if ( $i >= 3 ) break; ?>
                        
                    <?php endforeach; ?>
				<?php else : ?>
                <tr>
                    <td colspan="3"><?php printf( __( 'There are no emails associated with this %s', 'mobile-dj-manager' ), mdjm_get_label_singular( true ) ); ?></td>
                </tr>
                <?php endif; ?>

            </tbody>
            
            <?php if ( $emails ) : ?>
                <tfoot>
                	<tr>
                    	<td colspan="3"><span class="description">(<?php printf( __( 'Displaying the most recent %d emails of %d total', 'mobile-dj-manager' ), ( $count >= 3 ) ? 3 : $count, $count ); ?>)</span></td>
                    </tr>
                </tfoot>
            <?php endif; ?>
            
        </table>
    </div>
	<?php
} // mdjm_event_metabox_emails_table
add_action( 'mdjm_event_history_fields', 'mdjm_event_metabox_history_emails_table', 20 );
