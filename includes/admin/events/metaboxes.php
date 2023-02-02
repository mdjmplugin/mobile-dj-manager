<?php
/**
 * @author: Mike Howard, Jack Mawhinney, Dan Porter
 *
 * Contains all metabox functions for the mdjm-event post type
 *
 * @package     MDJM
 * @subpackage  Events
 * @since       1.3
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Generate the client details action links.
 *
 * @since   1.5
 * @param   int  $event_id           The event ID.
 * @param   obj  $mdjm_event         The event object.
 * @param   bool $mdjm_event_update  Whether an event is being updated.
 * @return  arr     Array of action links
 */
function mdjm_client_details_get_action_links( $event_id, $mdjm_event, $mdjm_event_update ) {

	$actions = array();

	if ( ! empty( $mdjm_event->client ) && mdjm_employee_can( 'view_clients_list' ) ) {
		$actions['view_client'] = '<a href="#" class="toggle-client-details-option-section">' . __( 'Show client details', 'mobile-dj-manager' ) . '</a>';
	}

	$actions['add_client'] = '<a id="add-client-action" href="#" class="toggle-client-add-option-section">' . __( 'Show client form', 'mobile-dj-manager' ) . '</a>';

	$actions = apply_filters( 'mdjm_event_metabox_client_details_actions', $actions, $event_id, $mdjm_event, $mdjm_event_update );
	return $actions;
} // mdjm_client_details_get_action_links

/**
 * Generate the event details action links.
 *
 * @since   1.5
 * @param   int  $event_id           The event ID.
 * @param   obj  $mdjm_event         The event object.
 * @param   bool $mdjm_event_update  Whether an event is being updated.
 * @return  arr     Array of action links
 */
function mdjm_event_details_get_action_links( $event_id, $mdjm_event, $mdjm_event_update ) {

	$venue_id = $mdjm_event->get_venue_id();
	$actions  = array(
		/* translators: %s Venue */
		'event_options' => '<a href="#" class="toggle-event-options-section">' . sprintf( __( 'Show %s options', 'mobile-dj-manager' ), mdjm_get_label_singular( true ) ) . '</a>',
	);

	// Event workers.
	if ( mdjm_is_employer() ) {
		/* translators: %s Event */
		$actions['event_workers'] = '<a href="#" class="toggle-add-worker-section">' . sprintf( __( 'Show %s workers', 'mobile-dj-manager' ), mdjm_get_label_singular( true ) ) . '</a>';
	}

	// New event type.
	if ( mdjm_is_admin() ) {
		/* translators: %s Event */
		$actions['event_type'] = '<a href="#" class="toggle-event-type-option-section">' . sprintf( __( 'Add %s type', 'mobile-dj-manager' ), mdjm_get_label_singular( true ) ) . '</a>';
	}

	// Venues.
	$actions['view_venue'] = '<a href="#" class="toggle-event-view-venue-option-section">' . __( 'Show venue', 'mobile-dj-manager' ) . '</a>';
	$actions['add_venue']  = '<a href="#" class="toggle-event-add-venue-option-section">' . __( 'Add venue', 'mobile-dj-manager' ) . '</a>';

	$actions = apply_filters( 'mdjm_event_metabox_event_details_actions', $actions, $event_id, $mdjm_event, $mdjm_event_update );
	return $actions;
} // mdjm_event_details_get_action_links

/**
 * Generate the event pricing action links.
 *
 * @since   1.5
 * @param   int  $event_id           The event ID.
 * @param   obj  $mdjm_event         The event object.
 * @param   bool $mdjm_event_update  Whether an event is being updated.
 * @return  arr     Array of action links
 */
function mdjm_event_pricing_get_action_links( $event_id, $mdjm_event, $mdjm_event_update ) {

	$actions = array();

	$actions = apply_filters( 'mdjm_event_pricing_actions', $actions, $event_id, $mdjm_event, $mdjm_event_update );
	return $actions;
} // mdjm_event_pricing_get_action_links

/**
 * Remove unwanted metaboxes to for the mdjm-event post type.
 * Apply the `mdjm_event_remove_metaboxes` filter to allow for filtering of metaboxes to be removed.
 *
 * @since   1.3
 */
function mdjm_remove_event_meta_boxes() {
	$metaboxes = apply_filters(
		'mdjm_event_remove_metaboxes',
		array(
			array( 'submitdiv', 'mdjm-event', 'side' ),
			array( 'event-typesdiv', 'mdjm-event', 'side' ),
			array( 'tagsdiv-enquiry-source', 'mdjm-event', 'side' ),
			array( 'commentsdiv', 'mdjm-event', 'normal' ),
		)
	);

	foreach ( $metaboxes as $metabox ) {
		remove_meta_box( $metabox[0], $metabox[1], $metabox[2] );
	}
} // mdjm_remove_event_meta_boxes
add_action( 'admin_head', 'mdjm_remove_event_meta_boxes' );

/**
 * Define and add the metaboxes for the mdjm-event post type.
 * Apply the `mdjm_event_add_metaboxes` filter to allow for filtering of metaboxes and settings.
 * Uses function_exists to verify the callback function exists.
 *
 * @since   1.3
 * @param var $post Post Event Details.
 */
function mdjm_add_event_meta_boxes( $post ) {

	global $mdjm_event, $mdjm_event_update;

	$save              = __( 'Create', 'mobile-dj-manager' );
	$mdjm_event_update = false;
	$mdjm_event        = new MDJM_Event( $post->ID );
	$mdjm_event->get_event_data();

	if ( 'draft' !== $post->post_status && 'auto-draft' !== $post->post_status ) {
		$mdjm_event_update = true;
	}

	$metaboxes = apply_filters(
		'mdjm_event_add_metaboxes',
		array(
			array(
				'id'         => 'mdjm-event-save-mb',
				/* translators: %s Event */
				'title'      => sprintf( __( '%1$s #%2$s', 'mobile-dj-manager' ), mdjm_get_label_singular(), $mdjm_event->data['contract_id'] ),
				'callback'   => 'mdjm_event_metabox_save_callback',
				'context'    => 'side',
				'priority'   => 'high',
				'args'       => array(),
				'dependancy' => '',
				'permission' => '',
			),
			array(
				'id'         => 'mdjm-event-tasks-mb',
				'title'      => __( 'Tasks', 'mobile-dj-manager' ),
				'callback'   => 'mdjm_event_metabox_tasks_callback',
				'context'    => 'side',
				'priority'   => 'default',
				'args'       => array(),
				'dependancy' => '',
				'permission' => '',
			),
			array(
				'id'         => 'mdjm-event-overview-mb',
				/* translators: %s Event */
				'title'      => sprintf( __( '%s Overview', 'mobile-dj-manager' ), mdjm_get_label_singular() ),
				'callback'   => 'mdjm_event_metabox_overview_callback',
				'context'    => 'normal',
				'priority'   => 'high',
				'args'       => array(),
				'dependancy' => '',
				'permission' => '',
			),
			array(
				'id'         => 'mdjm-event-admin-mb',
				'title'      => __( 'Administration', 'mobile-dj-manager' ),
				'callback'   => 'mdjm_event_metabox_admin_callback',
				'context'    => 'normal',
				'priority'   => 'low',
				'args'       => array(),
				'dependancy' => '',
				'permission' => '',
			),
			array(
				'id'         => 'mdjm-event-transactions-mb',
				'title'      => __( 'Transactions', 'mobile-dj-manager' ),
				'callback'   => 'mdjm_event_metabox_transactions_callback',
				'context'    => 'normal',
				'priority'   => 'low',
				'args'       => array(),
				'dependancy' => '',
				'permission' => 'edit_txns',
			),
			array(
				'id'         => 'mdjm-event-history-mb',
				/* translators: %s Event */
				'title'      => sprintf( __( '%s History', 'mobile-dj-manager' ), mdjm_get_label_singular() ),
				'callback'   => 'mdjm_event_metabox_history_callback',
				'context'    => 'normal',
				'priority'   => 'low',
				'args'       => array(),
				'dependancy' => '',
				'permission' => 'manage_mdjm',
			),
		)
	);
	// Runs before metabox output.
	do_action( 'mdjm_event_before_metaboxes' );

	// Begin metaboxes.
	foreach ( $metaboxes as $metabox ) {
		// Dependancy check.
		if ( ! empty( $metabox['dependancy'] ) && false === $metabox['dependancy'] ) {
			continue;
		}

		// Permission check.
		if ( ! empty( $metabox['permission'] ) && ! mdjm_employee_can( $metabox['permission'] ) ) {
			continue;
		}

		// Callback check.
		if ( ! is_callable( $metabox['callback'] ) ) {
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

	// Runs after metabox output.
	do_action( 'mdjm_event_after_metaboxes' );
} // mdjm_add_event_meta_boxes.
add_action( 'add_meta_boxes_mdjm-event', 'mdjm_add_event_meta_boxes' );

/**
 * Output for the Event Options meta box.
 *
 * @since   1.3
 * @param   obj $post   The post object (WP_Post).
 */
function mdjm_event_metabox_save_callback( $post ) {

	global $post, $mdjm_event, $mdjm_event_update;

	?>

	<div class="submitbox" id="submitpost">
		<div id="minor-publishing">
			<div id="mdjm-event-actions">

				<?php
				/*
				 * Output the items for the options metabox
				 * These items go inside the mdjm-event-actions div
				 * @since	1.3.7
				 * @param	int	$post_id	The Event post ID.
				 */
				do_action( 'mdjm_event_options_fields', $post->ID );
				?>
			</div><!-- #mdjm-event-actions -->
		</div><!-- #minor-publishing -->
	</div><!-- #submitpost -->
	<?php
	do_action( 'mdjm_event_options_fields_save', $post->ID );

} // mdjm_event_metabox_save_callback

/**
 * Output for the Pre Event planning meta box.
 *
 * @since   1.3
 * @param   obj $post   The post object (WP_Post).
 * @return
 */
/**
 * Output for the Event Overview meta box.
 *
 * @since   1.5
 * @param   obj $post   The post object (WP_Post).
 */
function mdjm_event_metabox_overview_callback( $post ) {

	global $post, $mdjm_event, $mdjm_event_update;

	/*
	 * Output the items for the event overview metabox
	 * @since	1.5
	 * @param	int	$post_id	The Event post ID
	 */
	do_action( 'mdjm_event_overview_fields', $post->ID );

} // mdjm_event_metabox_overview_callback

/**
 * Output for the Event Administration meta box.
 *
 * @since   1.3
 * @param   obj $post   The post object (WP_Post).
 */
function mdjm_event_metabox_admin_callback( $post ) {

	global $post, $mdjm_event, $mdjm_event_update;

	/*
	 * Output the items for the event admin metabox
	 * @since	1.3.7
	 * @param	int	$post_id	The Event post ID
	 */
	do_action( 'mdjm_event_admin_fields', $post->ID );

} // mdjm_event_metabox_admin_callback

/**
 * Output for the Event History meta box.
 *
 * @since   1.3
 * @param   obj $post   The post object (WP_Post).
 */
function mdjm_event_metabox_history_callback( $post ) {

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
 * @since   1.3
 * @global  obj     $post               WP_Post object
 * @global  obj     $mdjm_event         MDJM_Event class object
 * @global  bool    $mdjm_event_update  True if this event is being updated, false if new
 * @param   obj $post               The post object (WP_Post).
 */
function mdjm_event_metabox_transactions_callback( $post ) {

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
 * @since   1.3.7
 * @global  obj     $mdjm_event         MDJM_Event class object
 * @global  bool    $mdjm_event_update  True if this event is being updated, false if new.
 * @param   int $event_id           The event ID.
 */
function mdjm_event_metabox_options_status_row( $event_id ) {

	global $mdjm_event, $mdjm_event_update;

	$contract        = $mdjm_event->get_contract();
	$contract_status = $mdjm_event->get_contract_status();

	echo MDJM()->html->event_status_dropdown( 'mdjm_event_status', $mdjm_event->post_status ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

} // mdjm_event_metabox_options_status_row
add_action( 'mdjm_event_options_fields', 'mdjm_event_metabox_options_status_row', 10 );

/**
 * Output the event options payments row
 *
 * @since   1.3.7
 * @global  obj     $mdjm_event         MDJM_Event class object
 * @global  bool    $mdjm_event_update  True if this event is being updated, false if new.
 * @param   int $event_id           The event ID.
 * @return  str
 */
function mdjm_event_metabox_options_payments_row( $event_id ) {

	global $mdjm_event, $mdjm_event_update;

	if ( ! mdjm_employee_can( 'edit_txns' ) ) {
		return;
	}

	$deposit_status = __( 'Due', 'mobile-dj-manager' );
	$balance_status = __( 'Due', 'mobile-dj-manager' );

	if ( $mdjm_event_update && 'mdjm-unattended' !== $mdjm_event->post_status ) {
		$deposit_status = $mdjm_event->get_deposit_status();
		$balance_status = $mdjm_event->get_balance_status();
	}

	?>

	<p><strong><?php esc_html_e( 'Payments', 'mobile-dj-manager' ); ?></strong></p>

	<p>
	<?php
	echo MDJM()->html->checkbox( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		array(
			'name'    => 'deposit_paid',
			'value'   => 'Paid',
			'current' => esc_html( $deposit_status ),
		)
	);
	?>
		<?php /* translators: %s Deposit */ ?>
		<?php printf( esc_html__( '%s Paid?', 'mobile-dj-manager' ), esc_html( mdjm_get_deposit_label() ) ); ?></p>

	<p>
	<?php
	echo MDJM()->html->checkbox( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		array(
			'name'    => 'balance_paid',
			'value'   => 'Paid',
			'current' => esc_html( $balance_status ),
		)
	);
	?>
		<?php /* translators: %s Balance */ ?>
		<?php printf( esc_html__( '%s Paid?', 'mobile-dj-manager' ), esc_html( mdjm_get_balance_label() ) ); ?></p>
	<p>
		<?php printf( esc_html__( 'Balance Remaining: %s', 'mobile-dj-manager' ), mdjm_currency_filter( mdjm_format_amount( mdjm_get_event_balance( $event_id ) ) ) ); ?>
		
		</p>

	<?php

} // mdjm_event_metabox_options_payments_row
add_action( 'mdjm_event_options_fields', 'mdjm_event_metabox_options_payments_row', 30 );

/**
 * Output the event options save row
 *
 * @since   1.3.7
 * @global  obj     $mdjm_event         MDJM_Event class object
 * @global  bool    $mdjm_event_update  True if this event is being updated, false if new.
 * @param   int $event_id           The event ID.
 * @return  str
 */
function mdjm_event_metabox_options_save_row( $event_id ) {

	global $mdjm_event, $mdjm_event_update;

	if ( ! mdjm_employee_can( 'manage_events' ) ) {
		return;
	}
	/* translators: %s Event */
	$button_text = __( 'Add %s', 'mobile-dj-manager' );

	if ( $mdjm_event_update ) {
		/* translators: %s Event */
		$button_text = __( 'Update %s', 'mobile-dj-manager' );
	}

	$class = '';
	$url   = add_query_arg( array( 'post_type' => 'mdjm-event' ), admin_url( 'edit.php' ) );
	/* translators: %s Events */
	$a = sprintf( __( 'Back to %s', 'mobile-dj-manager' ), mdjm_get_label_plural() );

	if ( mdjm_employee_can( 'manage_all_events' ) && ( ! $mdjm_event_update || 'mdjm-unattended' === $mdjm_event->post_status ) ) {
		$class = 'mdjm-delete';
		$url   = wp_nonce_url(
			add_query_arg(
				array(
					'post'   => $event_id,
					'action' => 'trash',
				),
				admin_url( 'post.php' )
			),
			'trash-post_' . $event_id
		);
		/* translators: %s Event */
		$a = sprintf( __( 'Delete %s', 'mobile-dj-manager' ), mdjm_get_label_singular() );
	}

	?>
	<div id="major-publishing-actions">
		<div id="delete-action">
			<a class="<?php echo esc_attr( $class ); ?>" href="<?php echo esc_url( $url ); ?>"><?php echo esc_html( $a ); ?></a>
		</div>

		<div id="publishing-action">
			<?php
			submit_button(
				sprintf( $button_text, mdjm_get_label_singular() ),
				'primary',
				'save',
				false,
				array( 'id' => 'save-post' )
			);
			?>
		</div>
		<div class="clear"></div>
	</div>

	<?php

} // mdjm_event_metabox_options_save_row
add_action( 'mdjm_event_options_fields_save', 'mdjm_event_metabox_options_save_row', 40 );

/**
 * Output the event tasks row
 *
 * @since   1.5
 * @global  obj     $mdjm_event         MDJM_Event class object
 * @global  bool    $mdjm_event_update  True if this event is being updated, false if new.
 * @param   int $event_id           The event ID.
 */
function mdjm_event_metabox_tasks_row( $event_id ) {

	global $mdjm_event, $mdjm_event_update;

	$completed_tasks = $mdjm_event->get_tasks();
	$tasks_history   = array();
	$tasks           = mdjm_get_tasks_for_event( $event_id );

	foreach ( $completed_tasks as $task_slug => $run_time ) {
		if ( ! array_key_exists( $task_slug, $tasks ) ) {
			continue;
		}

		$tasks_history[] = sprintf(
			'%s: %s',
			mdjm_get_task_name( $task_slug ),
			date( mdjm_get_option( 'short_date_format' ), $run_time )
		);
	}

	if ( ! empty( $tasks_history ) ) {
		$history_class = '';
		$history       = implode( '<br>', $tasks_history );
	} else {
		$history_class = ' description';
		/* translators: %s Event */
		$history = sprintf( __( 'None of the available tasks have been executed for this %s', 'mobile-dj-manager' ), mdjm_get_label_singular( true ) );
	}

	?>
	<div id="mdjm-event-tasks">
		<?php if ( ! $mdjm_event_update || empty( $tasks ) ) : ?>
			<span class="description">
				<?php /* translators: %s Events */ ?>
				<?php printf( esc_html__( 'No tasks are available for this %s.', 'mobile-dj-manager' ), esc_html( mdjm_get_label_singular( true ) ) ); ?>
			</span>
			<?php
		else :

			foreach ( $tasks as $id => $name ) :
				?>
				<?php $options[ $id ] = $name; ?>
			<?php endforeach; ?>

			<?php
			echo MDJM()->html->select( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				array(
					'options'          => $options,
					'name'             => 'mdjm_event_task',
					'id'               => 'mdjm-event-task',
					'chosen'           => true,
					'placeholder'      => esc_html__( 'Select a Task', 'mobile-dj-manager' ),
					'show_option_none' => esc_html__( 'Select a Task', 'mobile-dj-manager' ),
				)
			);
			?>

			<div id="mdjm-event-task-run" class="mdjm-hidden">
				<p class="mdjm-execute-event-task">
				<?php
				submit_button(
					__( 'Run Task', 'mobile-dj-manager' ),
					array( 'secondary', 'mdjm-run-event-task' ),
					'mdjm-run-task',
					false
				);
				?>
				</p>
				<span id="mdjm-spinner" class="spinner mdjm-execute-event-task"></span>
			</div>

			<p><strong><?php esc_html_e( 'Completed Tasks', 'mobile-dj-manager' ); ?></strong></p>
			<span class="task-history-items<?php echo esc_attr( $history_class ); ?>"><?php echo esc_html( $history ); ?></span>
		<?php endif; ?>
	</div>

	<?php

} // mdjm_event_metabox_tasks_row
add_action( 'mdjm_event_tasks_fields', 'mdjm_event_metabox_tasks_row', 10 );

/**
 * Output the event client sections
 *
 * @since   1.5
 * @global  obj     $mdjm_event         MDJM_Event class object
 * @global  bool    $mdjm_event_update  True if this event is being updated, false if new.
 * @param   int $event_id           The event ID.
 */
function mdjm_event_overview_metabox_client_sections( $event_id ) {

	global $mdjm_event, $mdjm_event_update;

	?>
	<div id="mdjm_event_overview_client_fields" class="mdjm_meta_table_wrap">

		<div class="widefat mdjm_repeatable_table">

			<div class="mdjm-client-option-fields mdjm-repeatables-wrap">

				<div class="mdjm_event_overview_wrapper">

					<div class="mdjm-client-row-header">
						<span class="mdjm-repeatable-row-title">
							<?php esc_html_e( 'Client Details', 'mobile-dj-manager' ); ?>
						</span>

						<?php
						$actions = mdjm_client_details_get_action_links( $event_id, $mdjm_event, $mdjm_event_update );
						?>

						<span class="mdjm-repeatable-row-actions">
							<?php echo implode( '&nbsp;&#124;&nbsp;', $actions ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
						</span>
					</div>

					<div class="mdjm-repeatable-row-standard-fields">
						<?php do_action( 'mdjm_event_overview_standard_client_sections', $event_id ); ?>
					</div>
					<?php do_action( 'mdjm_event_overview_custom_client_sections', $event_id ); ?>
				</div>

			</div>

		</div>

	</div>
	<?php

} // mdjm_event_overview_metabox_client_sections
add_action( 'mdjm_event_overview_fields', 'mdjm_event_overview_metabox_client_sections', 10 );

/**
 * Output the event sections
 *
 * @since   1.5
 * @global  obj     $mdjm_event         MDJM_Event class object
 * @global  bool    $mdjm_event_update  True if this event is being updated, false if new.
 * @param   int $event_id           The event ID.
 */
function mdjm_event_overview_metabox_event_sections( $event_id ) {

	global $mdjm_event, $mdjm_event_update;

	$singular = mdjm_get_label_singular();

	?>
	<div id="mdjm_event_overview_event_fields" class="mdjm_meta_table_wrap">

		<div class="widefat mdjm_repeatable_table">
			<div class="mdjm-event-option-fields mdjm-repeatables-wrap">
				<div class="mdjm_event_overview_wrapper">
					<div class="mdjm-event-row-header">
						<span class="mdjm-repeatable-row-title">
							<?php /* translators: %s Events */ ?>
							<?php printf( esc_html__( '%s Details', 'mobile-dj-manager' ), esc_html( mdjm_get_label_singular() ) ); ?>
						</span>

						<?php
						$actions = mdjm_event_details_get_action_links( $event_id, $mdjm_event, $mdjm_event_update );
						?>

						<span class="mdjm-repeatable-row-actions">
							<?php echo implode( '&nbsp;&#124;&nbsp;', $actions ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
						</span>
					</div>

					<div class="mdjm-repeatable-row-standard-fields">
						<?php do_action( 'mdjm_event_overview_standard_event_sections', $event_id ); ?>
					</div>
					<?php do_action( 'mdjm_event_overview_custom_event_sections', $event_id ); ?>
				</div>
			</div>
		</div>
	</div>
	<?php

} // mdjm_event_overview_metabox_event_sections
add_action( 'mdjm_event_overview_fields', 'mdjm_event_overview_metabox_event_sections', 20 );

/**
 * Output the event price sections
 *
 * @since   1.5
 * @global  obj     $mdjm_event         MDJM_Event class object
 * @global  bool    $mdjm_event_update  True if this event is being updated, false if new.
 * @param   int $event_id           The event ID.
 */
function mdjm_event_overview_metabox_event_price_sections( $event_id ) {

	global $mdjm_event, $mdjm_event_update;

	$singular = mdjm_get_label_singular();

	if ( mdjm_employee_can( 'edit_txns' ) ) :
		?>

		<div id="mdjm_event_overview_event_price_fields" class="mdjm_meta_table_wrap">

			<div class="widefat mdjm_repeatable_table">
				<div class="mdjm-event-option-fields mdjm-repeatables-wrap">
					<div class="mdjm_event_overview_wrapper">
						<div class="mdjm-event-row-header">
							<span class="mdjm-repeatable-row-title">
								<?php esc_html_e( 'Pricing', 'mobile-dj-manager' ); ?>
							</span>

							<?php
							$actions = mdjm_event_pricing_get_action_links( $event_id, $mdjm_event, $mdjm_event_update );
							?>

							<span class="mdjm-repeatable-row-actions">
								<?php echo implode( '&nbsp;&#124;&nbsp;', $actions ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
							</span>
						</div>

						<div id="mdjm-event-pricing-detail" class="mdjm-repeatable-row-standard-fields">
							<?php do_action( 'mdjm_event_overview_standard_event_price_sections', $event_id ); ?>
						</div>
						<?php do_action( 'mdjm_event_overview_custom_event_price_sections', $event_id ); ?>
					</div>
				</div>
			</div>
		</div>

	<?php else : ?>

		<?php
		echo MDJM()->html->hidden( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			array(
				'name'  => '_mdjm_event_package_cost',
				'value' => esc_attr( ! empty( $mdjm_event->package_price ) ? mdjm_sanitize_amount( $mdjm_event->package_price ) : '' ),
			)
		);
		?>

		<?php
		echo MDJM()->html->hidden( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			array(
				'name'  => '_mdjm_event_addons_cost',
				'value' => esc_attr( ! empty( $mdjm_event->addons_price ) ? mdjm_sanitize_amount( $mdjm_event->addons_price ) : '' ),
			)
		);
		?>

		<?php
		echo MDJM()->html->hidden( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			array(
				'name'  => '_mdjm_event_travel_cost',
				'value' => esc_attr( ! empty( $mdjm_event->travel_cost ) ? mdjm_sanitize_amount( $mdjm_event->travel_cost ) : '' ),
			)
		);
		?>

		<?php
		echo MDJM()->html->hidden( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			array(
				'name'  => '_mdjm_event_additional_cost',
				'value' => esc_attr( ! empty( $mdjm_event->additional_cost ) ? mdjm_sanitize_amount( $mdjm_event->additional_cost ) : '' ),
			)
		);
		?>

		<?php
		echo MDJM()->html->hidden( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			array(
				'name'  => '_mdjm_event_discount',
				'value' => esc_attr( ! empty( $mdjm_event->discount ) ? mdjm_sanitize_amount( $mdjm_event->discount ) : '' ),
			)
		);
		?>

		<?php
		echo MDJM()->html->hidden( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			array(
				'name'  => '_mdjm_event_deposit',
				'value' => esc_attr( $mdjm_event_update ? mdjm_sanitize_amount( $mdjm_event->deposit ) : '' ),
			)
		);
		?>

		<?php
		echo MDJM()->html->hidden( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			array(
				'name'  => '_mdjm_event_cost',
				'value' => esc_attr( ! empty( $mdjm_event->price ) ? mdjm_sanitize_amount( $mdjm_event->price ) : '' ),
			)
		);
		?>

		<?php
	endif;

} // mdjm_event_overview_metabox_event_price_sections
add_action( 'mdjm_event_overview_fields', 'mdjm_event_overview_metabox_event_price_sections', 30 );

/**
 * Output the client name row
 *
 * @since   1.5
 * @global  obj     $mdjm_event         MDJM_Event class object
 * @global  bool    $mdjm_event_update  True if this event is being updated, false if new.
 * @param   int $event_id           The event ID.
 */
function mdjm_event_overview_metabox_client_name_row( $event_id ) {

	global $mdjm_event, $mdjm_event_update;

	$block_emails = false;

	if ( 'mdjm-enquiry' === $mdjm_event->post_status && ! mdjm_get_option( 'contract_to_client' ) ) {
		$block_emails = true;
	}

	if ( 'mdjm-enquiry' === $mdjm_event->post_status && ! mdjm_get_option( 'booking_conf_to_client' ) ) {
		$block_emails = true;
	}
	?>

   <div class="mdjm-client-name">
	<span class="mdjm-repeatable-row-setting-label"><?php esc_html_e( 'Client', 'mobile-dj-manager' ); ?></span>
		<?php if ( mdjm_event_is_active( $event_id ) ) : ?>

			<?php $clients = mdjm_get_clients( 'client' ); ?>

			<?php
			echo MDJM()->html->client_dropdown( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				array(
					'selected'         => $mdjm_event->client,
					'roles'            => array( 'client' ),
					'chosen'           => true,
					'placeholder'      => esc_html__( 'Select a Client', 'mobile-dj-manager' ),
					'null_value'       => array( '' => esc_html__( 'Select a Client', 'mobile-dj-manager' ) ),
					'show_option_all'  => false,
					'show_option_none' => false,
				)
			);
			?>

		<?php else : ?>

			<?php
			echo MDJM()->html->text( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				array(
					'name'     => 'client_name_display',
					'value'    => esc_attr( mdjm_get_client_display_name( $mdjm_event->client ) ),
					'readonly' => true,
				)
			);
			?>

			<?php
			echo MDJM()->html->text( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				array(
					'name'  => 'client_name',
					'value' => esc_attr( $mdjm_event->client ),
				)
			);
			?>

		<?php endif; ?>
	</div>
	<?php if ( mdjm_event_is_active( $mdjm_event->ID ) && 'mdjm-completed' !== $mdjm_event->post_status && 'mdjm-approved' !== $mdjm_event->post_status ) : ?>
		<div class="mdjm-repeatable-option mdjm_repeatable_default_wrapper">

			<span class="mdjm-repeatable-row-setting-label"><?php esc_html_e( 'Disable Emails?', 'mobile-dj-manager' ); ?></span>
			<label class="mdjm-block-email">
				<?php
				echo MDJM()->html->checkbox( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
					array(
						'name'    => 'mdjm_block_emails',
						'current' => esc_attr( $block_emails ),
					)
				);
				?>
				<?php /* translators: %s Events */ ?>
				<span class="screen-reader-text"><?php printf( esc_html__( 'Block update emails for this %s to the client', 'mobile-dj-manager' ), esc_html( mdjm_get_label_singular( true ) ) ); ?></span>
			</label>

		</div>
	<?php endif; ?>

	<?php if ( ! $mdjm_event_update || 'mdjm-approved' === $mdjm_event->post_status ) : ?>
		<div class="mdjm-repeatable-option mdjm_repeatable_default_wrapper">

			<span class="mdjm-repeatable-row-setting-label"><?php esc_html_e( 'Reset Password?', 'mobile-dj-manager' ); ?></span>
			<label class="mdjm-reset-password">
				<?php
				echo MDJM()->html->checkbox( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
					array(
						'name'  => 'mdjm_reset_pw',
						'value' => 'Y',
					)
				);
				?>
				<?php /* translators: %s Events */ ?>
				<span class="screen-reader-text"><?php printf( esc_html__( 'Click to reset the client password during %s update', 'mobile-dj-manager' ), esc_html( mdjm_get_label_singular( true ) ) ); ?></span>
			</label>

		</div>

	
		<?php
	endif;

} // mdjm_event_overview_metabox_client_name_row
add_action( 'mdjm_event_overview_standard_client_sections', 'mdjm_event_overview_metabox_client_name_row', 10 );

/**
 * Output the client name row
 *
 * @since   1.5
 * @global  obj     $mdjm_event         MDJM_Event class object
 * @global  bool    $mdjm_event_update  True if this event is being updated, false if new.
 * @param   int $event_id           The event ID.
 */
function mdjm_event_overview_metabox_client_templates_row( $event_id ) {

	global $mdjm_event, $mdjm_event_update;
	?>

	<?php if ( ! $mdjm_event_update || 'mdjm-unattended' === $mdjm_event->post_status ) : ?>
		<div class="mdjm-client-template-fields">
			<div class="mdjm-quote-template">
				<span class="mdjm-repeatable-row-setting-label"><?php esc_html_e( 'Email Quote Template', 'mobile-dj-manager' ); ?></span>
				<?php
				echo MDJM()->html->select( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
					array(
						'name'   	  => 'mdjm_email_template',
						'description' => printf('<i>This is the email template used when you set the status to Enquiry</i>'),
						'options' 	  => mdjm_list_templates( 'email_template' ), // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
					'selected'    => mdjm_get_option( 'enquiry' ), // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
					'chosen'      => true,
					'data'        => array(
						'search-type'        => 'template',
						'search-placeholder' => esc_html__( 'Type to search all templates', 'mobile-dj-manager' ),
							),
					)
				);
				?>
			</div>

			<?php if ( mdjm_get_option( 'online_enquiry', false ) ) : ?>

				<div class="mdjm-online-template">
					<span class="mdjm-repeatable-row-setting-label"><?php esc_html_e( 'Online Quote Template', 'mobile-dj-manager' ); ?></span>
					<?php
					echo MDJM()->html->select( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
						array(
							'name'    => 'mdjm_online_quote',
							'description' => printf('<i>This is the template used in the Client Portal</i>'),
							'options' => mdjm_list_templates( 'email_template' ), // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
						'selected'    => mdjm_get_option( 'online_enquiry' ), // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
						'chosen'      => true,
						'data'        => array(
							'search-type'        => 'template',
							'search-placeholder' => esc_html__( 'Type to search all templates', 'mobile-dj-manager' ),
							),
						)
					);
					?>
				</div>

			<?php endif; ?>
		</div>

		<?php
	endif;

} // mdjm_event_overview_metabox_client_templates_row
add_action( 'mdjm_event_overview_standard_client_sections', 'mdjm_event_overview_metabox_client_templates_row', 20 );

/**
 * Output the add client section
 *
 * @since   1.5
 * @global  obj     $mdjm_event         MDJM_Event class object
 * @global  bool    $mdjm_event_update  True if this event is being updated, false if new.
 * @param   int $event_id           The event ID.
 */
function mdjm_event_overview_metabox_add_client_section( $event_id ) {

	global $mdjm_event, $mdjm_event_update;
	?>

	<div id="mdjm-add-client-fields" class="mdjm-client-add-event-sections-wrap">
		<div class="mdjm-custom-event-sections">
			<div class="mdjm-custom-event-section">
				<span class="mdjm-custom-event-section-title"><?php esc_html_e( 'Add a New Client', 'mobile-dj-manager' ); ?></span>

				<span class="mdjm-client-new-first">
					<label class="mdjm-client-first">
						<?php esc_html_e( 'First Name', 'mobile-dj-manager' ); ?>
					</label>
					<?php
					echo MDJM()->html->text( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
						array(
							'name'  => 'client_firstname',
							'class' => 'mdjm-name-field large-text',
						)
					);
					?>
				</span>

				<span class="mdjm-client-new-last">
					<label class="mdjm-client-last">
						<?php esc_html_e( 'Last Name', 'mobile-dj-manager' ); ?>
					</label>
					<?php
					echo MDJM()->html->text( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
						array(
							'name'        => 'client_lastname',
							'class'       => 'mdjm-name-field large-text',
							'placeholder' => esc_html__( '(optional)', 'mobile-dj-manager' ),
						)
					);
					?>
				</span>

				<span class="mdjm-client-new-email">
					<label class="mdjm-client-email">
						<?php esc_html_e( 'Email Address', 'mobile-dj-manager' ); ?>
					</label>
					<?php
					echo MDJM()->html->text( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
						array(
							'name'  => 'client_email',
							'class' => 'mdjm-name-field large-text',
						)
					);
					?>
				</span>

				<span class="mdjm-client-new-address1">
					<label class="mdjm-client-address1">
						<?php esc_html_e( 'Address Line 1', 'mobile-dj-manager' ); ?>
					</label>
					<?php
					echo MDJM()->html->text( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
						array(
							'name'        => 'client_address1',
							'class'       => 'mdjm-name-field large-text',
							'placeholder' => esc_html__( '(optional)', 'mobile-dj-manager' ),
						)
					);
					?>
				</span>

				<span class="mdjm-client-new-address2">
					<label class="mdjm-client-address2">
						<?php esc_html_e( 'Address Line 2', 'mobile-dj-manager' ); ?>
					</label>
					<?php
					echo MDJM()->html->text( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
						array(
							'name'        => 'client_address2',
							'class'       => 'mdjm-name-field large-text',
							'placeholder' => esc_html__( '(optional)', 'mobile-dj-manager' ),
						)
					);
					?>
				</span>

				<span class="mdjm-client-new-town">
					<label class="mdjm-client-town">
						<?php esc_html_e( 'Town', 'mobile-dj-manager' ); ?>
					</label>
					<?php
					echo MDJM()->html->text( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
						array(
							'name'        => 'client_town',
							'class'       => 'mdjm-name-field large-text',
							'placeholder' => esc_html__( '(optional)', 'mobile-dj-manager' ),
						)
					);
					?>
				</span>

				<span class="mdjm-client-new-county">
					<label class="mdjm-client-county">
						<?php esc_html_e( 'County', 'mobile-dj-manager' ); ?>
					</label>
					<?php
					echo MDJM()->html->text( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
						array(
							'name'        => 'client_county',
							'class'       => 'mdjm-name-field large-text',
							'placeholder' => esc_html__( '(optional)', 'mobile-dj-manager' ),
						)
					);
					?>
				</span>

				<span class="mdjm-client-new-postcode">
					<label class="mdjm-client-postcode">
						<?php esc_html_e( 'Postal Code', 'mobile-dj-manager' ); ?>
					</label>
					<?php
					echo MDJM()->html->text( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
						array(
							'name'        => 'client_postcode',
							'class'       => 'mdjm-name-field large-text',
							'placeholder' => esc_html__( '(optional)', 'mobile-dj-manager' ),
						)
					);
					?>
				</span>

				<span class="mdjm-client-new-phone">
					<label class="mdjm-client-phone">
						<?php esc_html_e( 'Primary Phone', 'mobile-dj-manager' ); ?>
					</label>
					<?php
					echo MDJM()->html->text( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
						array(
							'name'        => 'client_phone',
							'class'       => 'mdjm-name-field large-text',
							'placeholder' => esc_html__( '(optional)', 'mobile-dj-manager' ),
						)
					);
					?>
				</span>

				<span class="mdjm-client-new-alt-phone">
					<label class="mdjm-client-phone">
						<?php esc_html_e( 'Alternative Phone', 'mobile-dj-manager' ); ?>
					</label>
					<?php
					echo MDJM()->html->text( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
						array(
							'name'        => 'client_phone2',
							'class'       => 'mdjm-name-field large-text',
							'placeholder' => esc_html__( '(optional)', 'mobile-dj-manager' ),
						)
					);
					?>
				</span>

				<span class="mdjm-add-client-action">
					<label>&nbsp;</label>
					<?php
					submit_button(
						esc_html__( 'Add Client', 'mobile-dj-manager' ),
						array( 'secondary', 'mdjm-add-client' ),
						'mdjm-add-client',
						false
					);
					?>
				</span>

			</div>
		</div>
	</div>
	<?php

} // mdjm_event_overview_metabox_add_client_section
add_action( 'mdjm_event_overview_custom_client_sections', 'mdjm_event_overview_metabox_add_client_section', 10 );

/**
 * Output the client details section
 *
 * @since   1.5
 * @global  obj     $mdjm_event         MDJM_Event class object
 * @global  bool    $mdjm_event_update  True if this event is being updated, false if new.
 * @param   int $event_id           The event ID.
 * @return  str
 */
function mdjm_event_overview_metabox_client_details_section( $event_id ) {

	global $mdjm_event, $mdjm_event_update;

	if ( empty( $mdjm_event->client ) || ! mdjm_employee_can( 'view_clients_list' ) ) {
		return;
	}

	$client = get_userdata( $mdjm_event->data['client'] );

	if ( ! $client ) {
		return;
	}

	$phone_numbers = array();
	if ( ! empty( $client->phone1 ) ) {
		$phone_numbers[] = $client->phone1;
	}
	if ( ! empty( $client->phone2 ) ) {
		$phone_numbers[] = $client->phone2;
	}

	?>
	 <div class="mdjm-client-details-event-sections-wrap">
		<div class="mdjm-custom-event-sections">
			<div class="mdjm-custom-event-section">
				<?php /* translators: %s Client Name */ ?>
				<span class="mdjm-custom-event-section-title"><?php printf( esc_html__( 'Contact Details for %s', 'mobile-dj-manager' ), esc_html( $client->display_name ) ); ?></span>

				<?php if ( ! empty( $phone_numbers ) ) : ?>
					<span class="mdjm-client-telephone">
						<i class="fas fa-phone" aria-hidden="true" title="<?php esc_attr_e( 'Phone', 'mobile-dj-manager' ); ?>"></i> <?php echo esc_html( implode( '&nbsp;&#124;&nbsp;', $phone_numbers ) ); ?>
					</span>
				<?php endif; ?>

				<span class="mdjm-client-email">
					<i class="fas fa-envelope-open" aria-hidden="true" title="<?php esc_attr_e( 'Email', 'mobile-dj-manager' ); ?>"></i>&nbsp;
					<a href="
					<?php
					echo esc_url(
						add_query_arg(
							array(
								'recipient' => $client->ID,
								'event_id'  => $event_id,
							),
							admin_url( 'admin.php?page=mdjm-comms' )
						)
					);
					?>
								">
						<?php echo esc_html( $client->user_email ); ?>
					</a>
				</span>

				<span class="mdjm-client-address">
					<?php echo mdjm_get_client_full_address( $client->ID ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
				</span>

				<span class="mdjm-client-login">
					<i class="fas fa-sign-in-alt" aria-hidden="true" title="<?php esc_attr_e( 'Last Login', 'mobile-dj-manager' ); ?>"></i> <?php echo esc_html( mdjm_get_client_last_login( $client->ID ) ); ?>
				</span>
			</div>
		</div>
	</div>
	<?php

} // mdjm_event_overview_metabox_client_details_section
add_action( 'mdjm_event_overview_custom_client_sections', 'mdjm_event_overview_metabox_client_details_section', 20 );

/**
 * Output the primary employee row
 *
 * @since   1.5
 * @global  obj     $mdjm_event         MDJM_Event class object
 * @global  bool    $mdjm_event_update  True if this event is being updated, false if new.
 * @param   int $event_id           The event ID.
 */
function mdjm_event_overview_metabox_primary_employee_row( $event_id ) {

	global $mdjm_event, $mdjm_event_update;

	$employee_id    = $mdjm_event->data['employee_id'] ? $mdjm_event->data['employee_id'] : get_current_user_id();
	$payment_status = $mdjm_event->data['primary_employee_payment_status'];
	$artist         = esc_attr( mdjm_get_option( 'artist' ) );

	if ( isset( $_GET['primary_employee'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification
		$employee_id = absint( wp_unslash( $_GET['primary_employee'] ) ); // phpcs:ignore WordPress.Security.NonceVerification	
	}

	?>
	<div class="mdjm-event-employee-fields">
		<div class="mdjm-event-primary-employee">
			<span class="mdjm-repeatable-row-setting-label">
				<?php printf( '%s', esc_html( $artist ) ); ?>
			</span>

			<?php
			echo MDJM()->html->employee_dropdown( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				array(
					'selected' => $employee_id,
					'group'    => mdjm_is_employer() ? true : false, // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				'chosen'       => true,
					/* translators: %s Artiste type */
				'placeholder'  => sprintf( esc_html__( 'Select %s', 'mobile-dj-manager' ), esc_html( $artist ) ),
				)
			);
			?>
		</div>

		<?php if ( mdjm_get_option( 'enable_employee_payments' ) && mdjm_employee_can( 'edit_txns' ) ) : ?>

			<?php $wage = mdjm_get_employees_event_wage( $event_id, $employee_id ); ?>

			<div class="mdjm-event-employee-wage">
				<span class="mdjm-repeatable-row-setting-label">
					<?php
					esc_html_e( 'Wage', 'mobile-dj-manager' );
					echo ' ' . esc_html( mdjm_currency_symbol() );
					?>
				</span>

				<?php
				echo MDJM()->html->text( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
					array(
						'name'        => '_mdjm_event_dj_wage',
						'class'       => 'mdjm-currency',
						'value'       => esc_attr( ! empty( $wage ) ? $wage : '' ),
						'placeholder' => esc_attr( mdjm_sanitize_amount( '0' ) ),
						'readonly'    => $payment_status ? true : false, // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
					)
				);
				?>
			</div>

		<?php endif; ?>
	</div>
	<?php

} // mdjm_event_overview_metabox_primary_employee_row
add_action( 'mdjm_event_overview_standard_event_sections', 'mdjm_event_overview_metabox_primary_employee_row', 5 );

/**
 * Output the event type, contract and venue row
 *
 * @since   1.5
 * @global  obj     $mdjm_event         MDJM_Event class object
 * @global  bool    $mdjm_event_update  True if this event is being updated, false if new.
 * @param   int $event_id           The event ID.
 */
function mdjm_event_overview_metabox_event_type_contract_venue_row( $event_id ) {

	global $mdjm_event, $mdjm_event_update;

	$event_type      = mdjm_get_event_type( $event_id, true );
	$contract        = $mdjm_event->data['contract'];
	$contract_status = $mdjm_event->data['contract_status'];
	$venue_id        = $mdjm_event->data['venue_id'];

	if ( ! $event_type ) {
		$event_type = mdjm_get_option( 'event_type_default', '' );
	}

	if ( ! empty( $venue_id ) && $venue_id === $event_id ) {
		$venue_id = 'manual';
	}

	?>
	<div class="mdjm-event-type-fields">
		<div class="mdjm-event-type">
			<span class="mdjm-repeatable-row-setting-label">
				<?php /* translators: %s Event */ ?>
				<?php printf( esc_html__( '%s Type', 'mobile-dj-manager' ), esc_html( mdjm_get_label_singular() ) ); ?>
			</span>

			<?php
			echo MDJM()->html->event_type_dropdown( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				array(
					'name'     => 'mdjm_event_type',
					'chosen'   => true,
					'selected' => esc_attr( $event_type ),
				)
			);
			?>
		</div>

		<div class="mdjm-event-contract">
			<span class="mdjm-repeatable-row-setting-label">
				<?php esc_html_e( 'Contract', 'mobile-dj-manager' ); ?>
			</span>

			<?php if ( ! $contract_status ) : ?>

				<?php
				echo MDJM()->html->select( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
					array(
						'name'    => '_mdjm_event_contract',
						'options' => mdjm_list_templates( 'contract' ), // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
					'chosen'      => true,
					'selected'    => ! empty( $contract ) ? $contract : mdjm_get_option( 'default_contract' ), // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
					'data'        => array(
						'search-type'        => 'contract',
						'search-placeholder' => esc_html__( 'Type to search all contracts', 'mobile-dj-manager' ),
							),
					)
				);
				?>

			<?php else : ?>

				<?php if ( mdjm_employee_can( 'read_events' ) ) : ?>
					<a id="view_contract" href="
					<?php
					echo esc_url(
						add_query_arg(
							array(
								'page'     => 'mdjm-view-contract',
								'event_id' => $event_id,
							),
							admin_url( 'admin.php' )
						)
					);
					?>
												" target="_blank"><?php esc_html_e( 'View signed contract', 'mobile-dj-manager' ); ?></a>
				<?php else : ?>
					<?php esc_html_e( 'Contract is Signed', 'mobile-dj-manager' ); ?>
				<?php endif; ?>

			<?php endif; ?>
		</div>

		<div class="mdjm-event-venue">
			<span class="mdjm-repeatable-row-setting-label">
				<?php esc_html_e( 'Venue', 'mobile-dj-manager' ); ?>
			</span>

			<?php
			echo MDJM()->html->venue_dropdown( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				array(
					'name'        => 'venue_id',
					'selected'    => esc_attr( $venue_id ),
					'placeholder' => esc_html__( 'Select a Venue', 'mobile-dj-manager' ),
					'chosen'      => true,
				)
			);
			?>
		</div>
	</div>

	<?php
} // mdjm_event_overview_metabox_event_type_contract_venue_row
add_action( 'mdjm_event_overview_standard_event_sections', 'mdjm_event_overview_metabox_event_type_contract_venue_row', 15 );

/**
 * Output the event date rows
 *
 * @since   1.5
 * @global  obj     $mdjm_event         MDJM_Event class object
 * @global  bool    $mdjm_event_update  True if this event is being updated, false if new.
 * @param   int $event_id           The event ID.
 */
function mdjm_event_overview_metabox_event_dates_row( $event_id ) {

	global $mdjm_event, $mdjm_event_update;

	$finish_date = $mdjm_event->data['finish_date'];
	$setup_date  = $mdjm_event->data['setup_date'];

	mdjm_insert_datepicker(
		array(
			'id' => 'display_event_date',
		)
	);

	mdjm_insert_datepicker(
		array(
			'id'       => 'display_event_finish_date',
			'altfield' => '_mdjm_event_end_date',
		)
	);

	mdjm_insert_datepicker(
		array(
			'id'       => 'dj_setup_date',
			'altfield' => '_mdjm_event_djsetup',
		)
	);

	?>
	<div class="mdjm-event-date-fields">
		<div class="mdjm-event-date">
			<span class="mdjm-repeatable-row-setting-label">
				<?php esc_html_e( 'Date', 'mobile-dj-manager' ); ?>
			</span>

			<?php
			echo MDJM()->html->text( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				array(
					'name'     => 'display_event_date',
					'class'    => 'mdjm_date',
					'required' => true,
					'value'    => esc_attr( ! empty( $mdjm_event->data['date'] ) ? mdjm_format_short_date( $mdjm_event->data['date'] ) : '' ),
				)
			);
			?>
			<?php
			echo MDJM()->html->hidden( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				array(
					'name'  => '_mdjm_event_date',
					'value' => esc_attr( ! empty( $mdjm_event->data['date'] ) ? $mdjm_event->data['date'] : '' ),
				)
			);
			?>
		</div>

		<div class="mdjm-event-finish-date">
			<span class="mdjm-repeatable-row-setting-label">
				<?php esc_html_e( 'End Date', 'mobile-dj-manager' ); ?>
			</span>

			<?php
			echo MDJM()->html->text( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				array(
					'name'     => 'display_event_finish_date',
					'class'    => 'mdjm_date',
					'required' => false,
					'value'    => esc_attr( ! empty( $finish_date ) ? mdjm_format_short_date( $finish_date ) : '' ),
				)
			);
			?>
			<?php
			echo MDJM()->html->hidden( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				array(
					'name'  => '_mdjm_event_end_date',
					'value' => esc_attr( ! empty( $finish_date ) ? $finish_date : '' ),
				)
			);
			?>
		</div>

		<div class="mdjm-event-setup-date">
			<span class="mdjm-repeatable-row-setting-label">
				<?php esc_html_e( 'Setup', 'mobile-dj-manager' ); ?>
			</span>

			<?php
			echo MDJM()->html->text( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				array(
					'name'  => 'dj_setup_date',
					'class' => 'mdjm_setup_date',
					'value' => esc_attr( $setup_date ? mdjm_format_short_date( $setup_date ) : '' ),
				)
			);
			?>
			<?php
			echo MDJM()->html->hidden( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				array(
					'name'  => '_mdjm_event_djsetup',
					'value' => esc_attr( $setup_date ? $setup_date : '' ),
				)
			);
			?>
		</div>
	</div>
	<?php

} // mdjm_event_overview_metabox_event_dates_row
add_action( 'mdjm_event_overview_standard_event_sections', 'mdjm_event_overview_metabox_event_dates_row', 20 );

/**
 * Output the event times row
 *
 * @since   1.5
 * @global  obj     $mdjm_event         MDJM_Event class object
 * @global  bool    $mdjm_event_update  True if this event is being updated, false if new.
 * @param   int $event_id           The event ID.
 */
function mdjm_event_overview_metabox_event_times_row( $event_id ) {

	global $mdjm_event, $mdjm_event_update;

	$start      = $mdjm_event->data['start_time'];
	$finish     = $mdjm_event->data['finish_time'];
	$setup_time = $mdjm_event->data['setup_time'];
	$format     = mdjm_get_option( 'time_format', 'H:i' );
	?>

	<div class="mdjm-event-date-fields">
		<div class="mdjm-event-start-time">
			<span class="mdjm-repeatable-row-setting-label">
				<?php esc_html_e( 'Start', 'mobile-dj-manager' ); ?>
			</span>

			<?php
			echo MDJM()->html->time_hour_select( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				array(
					'selected' => esc_attr( ! empty( $start ) ? date( $format[0], esc_html( strtotime( $start ) ) ) : '' ),
				)
			);
			?>
			<?php
			echo MDJM()->html->time_minute_select( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				array(
					'selected' => esc_attr( ! empty( $start ) ? date( $format[2], esc_html( strtotime( $start ) ) ) : '' ),
				)
			);
			?>
			<?php if ( 'H:i' !== $format ) : ?>
				<?php
				echo MDJM()->html->time_period_select( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
					array(
						'selected' => esc_attr( ! empty( $start ) ? date( 'A', esc_html( strtotime( $start ) ) ) : '' ),
					)
				);
				?>
			<?php endif; ?>
		</div>

		<div class="mdjm-event-end-time">
			<span class="mdjm-repeatable-row-setting-label">
				<?php esc_html_e( 'End Time', 'mobile-dj-manager' ); ?>
			</span>

			<?php
			echo MDJM()->html->time_hour_select( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				array(
					'name'     => 'event_finish_hr',
					'selected' => esc_attr( ! empty( $finish ) ? date( $format[0], strtotime( $finish ) ) : '' ),
				)
			);
			?>
			<?php
			echo MDJM()->html->time_minute_select( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				array(
					'name'     => 'event_finish_min',
					'selected' => esc_attr( ! empty( $finish ) ? date( $format[2], strtotime( $finish ) ) : '' ),
				)
			);
			?>
			<?php if ( 'H:i' !== $format ) : ?>
				<?php
				echo MDJM()->html->time_period_select( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
					array(
						'name'     => 'event_finish_period',
						'selected' => esc_attr( ! empty( $finish ) ? date( 'A', strtotime( $finish ) ) : '' ),
					)
				);
				?>
			<?php endif; ?>
		</div>

		<div class="mdjm-event-setup-time">
			<span class="mdjm-repeatable-row-setting-label">
				<?php esc_html_e( 'Setup', 'mobile-dj-manager' ); ?>
			</span>

			<?php
			echo MDJM()->html->time_hour_select( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				array(
					'name'        => 'dj_setup_hr',
					'selected'    => esc_attr( ! empty( $setup_time ) ? date( $format[0], strtotime( $setup_time ) ) : '' ),
					'blank_first' => true,
				)
			);
			?>
			<?php
			echo MDJM()->html->time_minute_select( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				array(
					'name'        => 'dj_setup_min',
					'selected'    => esc_attr( ! empty( $setup_time ) ? date( $format[2], strtotime( $setup_time ) ) : '' ),
					'blank_first' => true,
				)
			);
			?>
			<?php if ( 'H:i' !== $format ) : ?>
				<?php
				echo MDJM()->html->time_period_select( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
					array(
						'name'        => 'dj_setup_period',
						'selected'    => esc_attr( ! empty( $setup_time ) ? date( 'A', strtotime( $setup_time ) ) : '' ),
						'blank_first' => true,
					)
				);
				?>
			<?php endif; ?>
		</div>
	</div>
	<?php

} // mdjm_event_overview_metabox_event_times_row
add_action( 'mdjm_event_overview_standard_event_sections', 'mdjm_event_overview_metabox_event_times_row', 20 );

/**
 * Output the event package row
 *
 * @since   1.5
 * @global  obj     $mdjm_event         MDJM_Event class object
 * @global  bool    $mdjm_event_update  True if this event is being updated, false if new.
 * @param   int $event_id           The event ID.
 * @return  str
 */
function mdjm_event_overview_metabox_event_packages_row( $event_id ) {

	if ( ! mdjm_packages_enabled() ) {
		return;
	}

	global $mdjm_event, $mdjm_event_update;

	$package    = $mdjm_event->data['package'];
	$addons     = $mdjm_event->data['addons'];
	$employee   = $mdjm_event->data['employee_id'] ? $mdjm_event->data['employee_id'] : get_current_user_id();
	$event_type = mdjm_get_event_type( $event_id, true );
	$event_date = $mdjm_event->data['date'] ? $mdjm_event->data['date'] : false;

	if ( ! $event_type ) {
		$event_type = mdjm_get_option( 'event_type_default', '' );
	}

	?>

	<div class="mdjm-event-package-fields">
		<div class="mdjm-event-package">
			<span class="mdjm-repeatable-row-setting-label">
				<?php esc_html_e( 'Package', 'mobile-dj-manager' ); ?>
			</span>

			<?php
			echo MDJM()->html->packages_dropdown( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				array(
					'employee'   => $employee,
					'event_type' => esc_attr( $event_type ),
					'event_date' => esc_attr( $event_date ),
					'selected'   => esc_attr( $package ),
					'chosen'     => true,
				)
			);
			?>
		</div>

		<div class="mdjm-event-addons">
			<span class="mdjm-repeatable-row-setting-label">
				<?php esc_html_e( 'Addons', 'mobile-dj-manager' ); ?>
			</span>

			<?php
			echo MDJM()->html->addons_dropdown( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				array(
					'selected'         => $addons,
					'show_option_none' => false,
					'employee'         => esc_attr( $employee ),
					'event_type'       => esc_attr( $event_type ),
					'event_date'       => esc_attr( $event_date ),
					'package'          => esc_attr( $package ),
					'placeholder'      => esc_html__( 'Select Add-ons', 'mobile-dj-manager' ),
					'chosen'           => true,
				)
			);
			?>
		</div>
	</div>
	<?php

} // mdjm_event_overview_metabox_event_packages_row
add_action( 'mdjm_event_overview_standard_event_sections', 'mdjm_event_overview_metabox_event_packages_row', 30 );

/**
 * Output the event client notes row
 *
 * @since   1.5
 * @global  obj     $mdjm_event         MDJM_Event class object
 * @global  bool    $mdjm_event_update  True if this event is being updated, false if new.
 * @param   int $event_id           The event ID.
 */
function mdjm_event_overview_metabox_event_client_notes_row( $event_id ) {

	global $mdjm_event, $mdjm_event_update;

	?>

	<div class="mdjm-event-client-notes-fields">
		<div class="mdjm-event-notes">
			<span class="mdjm-repeatable-row-setting-label">
				<?php esc_html_e( 'Client Notes', 'mobile-dj-manager' ); ?>
			</span>

			<?php
			echo MDJM()->html->textarea( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				array(
					'name'        => '_mdjm_event_notes',
					'placeholder' => esc_html__( 'Information entered here is visible by both clients and employees', 'mobile-dj-manager' ),
					'value'       => esc_attr( $mdjm_event->data['notes'] ),
				)
			);
			?>
		</div>
	</div>
	<?php

} // mdjm_event_overview_metabox_event_client_notes_row
add_action( 'mdjm_event_overview_standard_event_sections', 'mdjm_event_overview_metabox_event_client_notes_row', 50 );

/**
 * Output the playlist options section
 *
 * @since   1.5
 * @global  obj     $mdjm_event         MDJM_Event class object
 * @global  bool    $mdjm_event_update  True if this event is being updated, false if new.
 * @param   int $event_id           The event ID.
 */
function mdjm_event_overview_metabox_event_playlist_options_row( $event_id ) {
	global $mdjm_event, $mdjm_event_update;

	$enable_playlist = mdjm_get_option( 'enable_playlists', true );
	$limit_class     = '';

	if ( ! $mdjm_event_update || 'mdjm-unattended' === $mdjm_event->data['status'] ) {
		$playlist_limit = mdjm_playlist_global_limit();
	} else {
		$playlist_limit  = $mdjm_event->data['playlist_limit'];
		$enable_playlist = $mdjm_event->data['playlist_enabled'];
	}

	if ( ! $enable_playlist ) {
		$limit_class = ' style="display: none;"';
	}

	if ( ! $playlist_limit ) {
		$playlist_limit = 0;
	}

	?>
	<div id="mdjm-event-options-fields" class="mdjm-event-options-sections-wrap">
		<div class="mdjm-custom-event-sections">
			<?php do_action( 'mdjm_event_overview_options_top', $event_id ); ?>
			<div class="mdjm-custom-event-section">
				<?php /* translators: %s Events */ ?>
				<span class="mdjm-custom-event-section-title"><?php printf( esc_html__( '%s Options', 'mobile-dj-manager' ), esc_html( mdjm_get_label_singular() ) ); ?></span>

				<div class="mdjm-repeatable-option">
					<span class="mdjm-enable-playlist-option">
						<label class="mdjm-enable-playlist">
							<?php esc_html_e( 'Enable Playlist?', 'mobile-dj-manager' ); ?>
						</label>
						<?php
						echo MDJM()->html->checkbox( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
							array(
								'name'    => '_mdjm_event_playlist',
								'value'   => 'Y',
								'current' => esc_attr( $enable_playlist ? 'Y' : 0 ),
							)
						);
						?>
					</span>
				</div>

				<span id="mdjm-playlist-limit" class="mdjm-playlist-limit-option" <?php echo $limit_class; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
					<label class="mdjm-playlist-limit">
						<?php esc_html_e( 'Song Limit', 'mobile-dj-manager' ); ?>
					</label>
					<?php
					echo MDJM()->html->number( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
						array(
							'name'  => '_mdjm_event_playlist_limit',
							'value' => esc_attr( $playlist_limit ),
						)
					);
					?>
				</span>

				<?php if ( mdjm_event_has_playlist( $event_id ) ) : ?>
					<?php $songs = mdjm_count_playlist_entries( $event_id ); ?>
					<?php
					$url = add_query_arg(
						array(
							'page'     => 'mdjm-playlists',
							'event_id' => $event_id,
						),
						admin_url( 'admin.php' )
					);
					?>

					<span id="mdjm-playlist-view" class="mdjm-playlist-view-option">
						<label class="mdjm-playlist-view">
							<?php /* translators: %s Number of Songs */ ?>
							<?php printf( esc_html( _n( '%s Song in Playlist', '%s Songs in Playlist', $songs, 'mobile-dj-manager' ) ), esc_html( $songs ) ); ?>
						</label>
						<a href="<?php echo esc_url( $url ); ?>"><?php esc_html_e( 'View', 'mobile-dj-manager' ); ?></a>
					</span>
				<?php endif; ?>

			</div>
			
			<?php do_action( 'mdjm_event_overview_options', $event_id ); ?>
		</div>
	</div>

	<?php

} // mdjm_event_overview_metabox_event_playlist_options_row
add_action( 'mdjm_event_overview_custom_event_sections', 'mdjm_event_overview_metabox_event_playlist_options_row', 5 );

/**
 * Output the event workers section
 *
 * @since   1.5
 * @global  obj     $mdjm_event         MDJM_Event class object
 * @global  bool    $mdjm_event_update  True if this event is being updated, false if new.
 * @param   int $event_id           The event ID.
 * @return  str
 */
function mdjm_event_overview_metabox_event_workers_row( $event_id ) {
	global $mdjm_event, $mdjm_event_update;

	if ( ! mdjm_is_employer() ) {
		return;
	}

	$exclude = false;

	if ( ! empty( $mdjm_event->data['employees'] ) ) {
		foreach ( $mdjm_event->data['employees'] as $employee_id => $employee_data ) {
			$exclude[] = $employee_id;
		}
	}

	?>
	<a id="mdjm-event-workers"></a>
	<div id="mdjm-event-workers-fields" class="mdjm-event-workers-sections-wrap">
		<div class="mdjm-custom-event-sections">
			<?php do_action( 'mdjm_event_overview_workers_top', $event_id ); ?>
			<div class="mdjm-custom-event-section">
				<?php /* translators: %s Event */ ?>
				<span class="mdjm-custom-event-section-title"><?php printf( esc_html__( '%s Workers', 'mobile-dj-manager' ), esc_html( mdjm_get_label_singular() ) ); ?></span>

				<span class="mdjm-event-workers-role">
					<?php
					echo MDJM()->html->roles_dropdown( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
						array(
							'name'   => 'event_new_employee_role',
							'chosen' => true,
						)
					);
					?>
				</span>

				<span class="mdjm-event-workers-employee">
					<?php
					echo MDJM()->html->employee_dropdown( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
						array(
							'name'    => 'event_new_employee',
							'exclude' => $exclude, // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
						'group'       => true,
						'chosen'      => true,
						'placeholder' => esc_html__( 'Select an Employee', 'mobile-dj-manager' ),
						)
					);
					?>
				</span>

				<?php if ( mdjm_get_option( 'enable_employee_payments' ) && mdjm_employee_can( 'manage_txns' ) ) : ?>
					<span class="mdjm-event-workers-wage">
						<?php
						echo MDJM()->html->text( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
							array(
								'name'        => 'event_new_employee_wage',
								'class'       => 'mdjm-currency',
								'placeholder' => esc_attr( mdjm_sanitize_amount( '0' ) ),
							)
						);
						?>
					</span>
				<?php endif; ?>

				<br />
				<span class="mdjm-event-worker-add">
					<a id="add_event_employee" class="button button-secondary button-small"><?php esc_html_e( 'Add', 'mobile-dj-manager' ); ?></a>
				</span>

			</div>

			<?php do_action( 'mdjm_event_overview_workers', $event_id ); ?>
		</div>

		<div id="mdjm-event-employee-list">
			<?php mdjm_do_event_employees_list_table( $event_id ); ?>
		</div>

		<?php if ( mdjm_get_option( 'enable_employee_payments' ) && in_array( $mdjm_event->post_status, mdjm_get_option( 'employee_pay_status' ) ) && mdjm_employee_can( 'manage_txns' ) && ! mdjm_event_employees_paid( $event_id ) ) : ?>

			<div class="mdjm_field_wrap mdjm_form_fields">
				<p><a href="
				<?php
				echo esc_url(
					wp_nonce_url(
						add_query_arg(
							array(
								'mdjm-action' => 'pay_event_employees',
								'event_id'    => $event_id,
							),
							admin_url( 'admin.php' )
						),
						'pay_event_employees',
						'mdjm_nonce'
					)
				);
				?>
							" id="pay_event_employees" class="button button-primary button-small"> <?php /* translators: %s Event */ ?><?php printf( esc_html__( 'Pay %s Employees', 'mobile-dj-manager' ), esc_html( mdjm_get_label_singular() ) ); ?></a></p>
			</div>

		<?php endif; ?>

	</div>

	<?php

} // mdjm_event_overview_metabox_event_workers_row
add_action( 'mdjm_event_overview_custom_event_sections', 'mdjm_event_overview_metabox_event_workers_row', 10 );

/**
 * Output the add event type section
 *
 * @since   1.5
 * @global  obj     $mdjm_event         MDJM_Event class object
 * @global  bool    $mdjm_event_update  True if this event is being updated, false if new.
 * @param   int $event_id           The event ID.
 */
function mdjm_event_overview_metabox_add_event_type_section( $event_id ) {

	global $mdjm_event, $mdjm_event_update;
	?>

	<div id="mdjm-add-event-type-fields" class="mdjm-add-event-type-sections-wrap">
		<div class="mdjm-custom-event-sections">
			<div class="mdjm-custom-event-section">
				<?php /* translators: %s Event */ ?>
				<span class="mdjm-custom-event-section-title"><?php printf( esc_html__( 'New %s Type', 'mobile-dj-manager' ), esc_html( mdjm_get_label_singular() ) ); ?></span>

				<span class="mdjm-new-event-type">
					<label class="mdjm-event-type">
						<?php /* translators: %s Event */ ?>
						<?php printf( esc_html__( '%s Type', 'mobile-dj-manager' ), esc_html( mdjm_get_label_singular() ) ); ?>
					</label>
					<?php
					echo MDJM()->html->text( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
						array(
							'name'  => 'event_type_name',
							'class' => 'mdjm-name-field large-text',
						)
					);
					?>
				</span>

				<span class="mdjm-add-event-type-action">
					<label>&nbsp;</label>
					<?php
					submit_button(
						/* translators: %s Event */
						sprintf( esc_html__( 'Add %s Type', 'mobile-dj-manager' ), esc_html( mdjm_get_label_singular() ) ),
						array( 'secondary', 'mdjm-add-event-type' ),
						'mdjm-add-event-type',
						false
					);
					?>
				</span>
			</div>
		</div>
	</div>
	<?php
} // mdjm_event_overview_metabox_add_event_type_section
add_action( 'mdjm_event_overview_custom_event_sections', 'mdjm_event_overview_metabox_add_event_type_section', 15 );

/**
 * Output the venue details section
 *
 * @since   1.5
 * @global  obj     $mdjm_event         MDJM_Event class object
 * @global  bool    $mdjm_event_update  True if this event is being updated, false if new.
 * @param   int $event_id           The event ID.
 */
function mdjm_event_overview_metabox_venue_details_section( $event_id ) {

	global $mdjm_event, $mdjm_event_update;

	$venue_id = $mdjm_event->get_venue_id();

	echo mdjm_do_venue_details_table( $venue_id, $event_id ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

} // mdjm_event_overview_metabox_venue_details_section
add_action( 'mdjm_event_overview_custom_event_sections', 'mdjm_event_overview_metabox_venue_details_section', 20 );

/**
 * Output the add venue section
 *
 * @since   1.5
 * @global  obj     $mdjm_event         MDJM_Event class object
 * @global  bool    $mdjm_event_update  True if this event is being updated, false if new.
 * @param   int $event_id           The event ID.
 */
function mdjm_event_overview_metabox_add_venue_section( $event_id ) {

	global $mdjm_event, $mdjm_event_update;

	$venue_id = $mdjm_event->data['venue_id'];

	if ( 'Manual' === $venue_id ) {
		$venue_id = $event_id;
	}

	$venue_name     = mdjm_get_event_venue_meta( $venue_id, 'name' );
	$venue_contact  = mdjm_get_event_venue_meta( $venue_id, 'contact' );
	$venue_email    = mdjm_get_event_venue_meta( $venue_id, 'email' );
	$venue_address1 = mdjm_get_event_venue_meta( $venue_id, 'address1' );
	$venue_address2 = mdjm_get_event_venue_meta( $venue_id, 'address2' );
	$venue_town     = mdjm_get_event_venue_meta( $venue_id, 'town' );
	$venue_county   = mdjm_get_event_venue_meta( $venue_id, 'county' );
	$venue_postcode = mdjm_get_event_venue_meta( $venue_id, 'postcode' );
	$venue_phone    = mdjm_get_event_venue_meta( $venue_id, 'phone' );
	$venue_address  = array( $venue_address1, $venue_address2, $venue_town, $venue_county, $venue_postcode );
	$section_title  = __( 'Add a New Venue', 'mobile-dj-manager' );
	$add_save_label = __( 'Add', 'mobile-dj-manager' );
	$employee_id    = $mdjm_event->data['employee_id'] ? $mdjm_event->data['employee_id'] : get_current_user_id();

	if ( $mdjm_event->ID === $venue_id ) {
		$section_title  = __( 'Manual Venue', 'mobile-dj-manager' );
		$add_save_label = __( 'Save', 'mobile-dj-manager' );
	}

	?>

	<div id="mdjm-add-venue-fields" class="mdjm-add-event-venue-sections-wrap">
		<div class="mdjm-custom-event-sections">
			<div class="mdjm-custom-event-section">
				<span class="mdjm-custom-event-section-title"><?php echo esc_html( $section_title ); ?></span>

				<span class="mdjm-add-venue-name">
					<label class="mdjm-venue-name">
						<?php esc_html_e( 'Venue', 'mobile-dj-manager' ); ?>
					</label>
					<?php
					echo MDJM()->html->text( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
						array(
							'name'  => 'venue_name',
							'value' => esc_attr( $venue_name ),
							'class' => 'mdjm-name-field large-text',
						)
					);
					?>
				</span>

				<span class="mdjm-add-venue-contact">
					<label class="mdjm-venue-contact">
						<?php esc_html_e( 'Contact', 'mobile-dj-manager' ); ?>
					</label>
					<?php
					echo MDJM()->html->text( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
						array(
							'name'        => 'venue_contact',
							'class'       => 'mdjm-name-field large-text',
							'value'       => esc_attr( ! empty( $venue_contact ) ? esc_attr( $venue_contact ) : '' ),
							'placeholder' => esc_html__( '(optional)', 'mobile-dj-manager' ),
						)
					);
					?>
				</span>

				<span class="mdjm-add-venue-email">
					<label class="mdjm-venue-email">
						<?php esc_html_e( 'Email Address', 'mobile-dj-manager' ); ?>
					</label>
					<?php
					echo MDJM()->html->text( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
						array(
							'name'        => 'venue_email',
							'class'       => 'mdjm-name-field large-text',
							'type'        => 'email',
							'value'       => esc_attr( ! empty( $venue_email ) ? esc_attr( $venue_email ) : '' ),
							'placeholder' => esc_html__( '(optional)', 'mobile-dj-manager' ),
						)
					);
					?>
				</span>

				<span class="mdjm-add-venue-phone">
					<label class="mdjm-venue-phone">
						<?php esc_html_e( 'Phone', 'mobile-dj-manager' ); ?>
					</label>
					<?php
					echo MDJM()->html->text( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
						array(
							'name'        => 'venue_phone',
							'class'       => 'mdjm-name-field large-text',
							'value'       => esc_attr( ! empty( $venue_phone ) ? esc_attr( $venue_phone ) : '' ),
							'placeholder' => esc_html__( '(optional)', 'mobile-dj-manager' ),
						)
					);
					?>
				</span>

				<span class="mdjm-add-venue-address1">
					<label class="mdjm-venue-address1">
						<?php esc_html_e( 'Address Line 1', 'mobile-dj-manager' ); ?>
					</label>
					<?php
					echo MDJM()->html->text( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
						array(
							'name'        => 'venue_address1',
							'class'       => 'mdjm-name-field large-text',
							'value'       => esc_attr( ! empty( $venue_address1 ) ? esc_attr( $venue_address1 ) : '' ),
							'placeholder' => esc_html__( '(optional)', 'mobile-dj-manager' ),
						)
					);
					?>
				</span>

				<span class="mdjm-add-venue-address2">
					<label class="mdjm-venue-address2">
						<?php esc_html_e( 'Address Line 2', 'mobile-dj-manager' ); ?>
					</label>
					<?php
					echo MDJM()->html->text( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
						array(
							'name'        => 'venue_address2',
							'class'       => 'mdjm-name-field large-text',
							'value'       => esc_attr( ! empty( $venue_address2 ) ? esc_attr( $venue_address2 ) : '' ),
							'placeholder' => esc_html__( '(optional)', 'mobile-dj-manager' ),
						)
					);
					?>
				</span>

				<span class="mdjm-add-venue-town">
					<label class="mdjm-venue-town">
						<?php esc_html_e( 'Town', 'mobile-dj-manager' ); ?>
					</label>
					<?php
					echo MDJM()->html->text( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
						array(
							'name'        => 'venue_town',
							'class'       => 'mdjm-name-field large-text',
							'value'       => esc_attr( ! empty( $venue_town ) ? esc_attr( $venue_town ) : '' ),
							'placeholder' => esc_html__( '(optional)', 'mobile-dj-manager' ),
						)
					);
					?>
				</span>

				<span class="mdjm-add-venue-county">
					<label class="mdjm-venue-county">
						<?php esc_html_e( 'County', 'mobile-dj-manager' ); ?>
					</label>
					<?php
					echo MDJM()->html->text( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
						array(
							'name'        => 'venue_county',
							'class'       => 'mdjm-name-field large-text',
							'value'       => esc_attr( ! empty( $venue_county ) ? esc_attr( $venue_county ) : '' ),
							'placeholder' => esc_html__( '(optional)', 'mobile-dj-manager' ),
						)
					);
					?>
				</span>

				<span class="mdjm-add-venue-postcode">
					<label class="mdjm-venue-postcode">
						<?php esc_html_e( 'Post Code', 'mobile-dj-manager' ); ?>
					</label>
					<?php
					echo MDJM()->html->text( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
						array(
							'name'        => 'venue_postcode',
							'class'       => 'mdjm-name-field large-text',
							'value'       => esc_attr( ! empty( $venue_postcode ) ? esc_attr( $venue_postcode ) : '' ),
							'placeholder' => esc_html__( '(optional)', 'mobile-dj-manager' ),
						)
					);
					?>
				</span>

				<span class="mdjm-add-venue-action">
					<label>&nbsp;</label>
					<?php
					submit_button(
						/* translators: %s Add */
						sprintf( esc_html__( '%s Venue', 'mobile-dj-manager' ), $add_save_label ),
						array( 'secondary', 'mdjm-add-venue' ),
						'mdjm-add-venue',
						false
					);
					?>
				</span>

				<?php do_action( 'mdjm_venue_details_table_after_save', $event_id ); ?>
				<?php do_action( 'mdjm_venue_details_travel_data', $venue_address, $employee_id ); ?>

			</div>
		</div>
	</div>
	<?php
} // mdjm_event_overview_metabox_add_venue_section
add_action( 'mdjm_event_overview_custom_event_sections', 'mdjm_event_overview_metabox_add_venue_section', 25 );

/**
 * Output the event travel costs hidden fields
 *
 * @since   1.4
 * @global  obj     $mdjm_event         MDJM_Event class object
 * @global  bool    $mdjm_event_update  True if this event is being updated, false if new.
 * @param   int $event_id           The event ID.
 */
function mdjm_event_overview_metabox_venue_travel_section( $event_id ) {
	global $mdjm_event, $mdjm_event_update;

	$travel_fields = mdjm_get_event_travel_fields();

	foreach ( $travel_fields as $field ) :
		?>
		<?php $travel_data = mdjm_get_event_travel_data( $event_id, $field ); ?>
		<?php $value = ! empty( $travel_data ) ? $travel_data : ''; ?>
		<input type="hidden" name="travel_<?php echo esc_attr( $field ); ?>" id="mdjm_travel_<?php echo esc_attr( $field ); ?>" value="<?php echo esc_attr( $value ); ?>" />
		<?php
	endforeach;

} // mdjm_event_overview_metabox_venue_travel_section
add_action( 'mdjm_event_overview_custom_event_sections', 'mdjm_event_overview_metabox_venue_travel_section', 30 );

/**
 * Output the event equipment and travel costs row
 *
 * @since   1.5
 * @global  obj     $mdjm_event         MDJM_Event class object
 * @global  bool    $mdjm_event_update  True if this event is being updated, false if new.
 * @param   int $event_id           The event ID.
 */
function mdjm_event_overview_metabox_event_equipment_travel_costs_row( $event_id ) {

	global $mdjm_event, $mdjm_event_update;

	?>

	<div class="mdjm-event-equipment-price-fields">

		<?php if ( mdjm_packages_enabled() ) : ?>

			<div class="mdjm-event-package-cost">
				<span class="mdjm-repeatable-row-setting-label">
					<?php
					esc_html_e( 'Package Costs', 'mobile-dj-manager' );
					echo ' ' . esc_html( mdjm_currency_symbol() );
					?>
				</span>

				<?php
				echo MDJM()->html->text( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
					array(
						'name'        => '_mdjm_event_package_cost',
						'class'       => 'mdjm-currency',
						'placeholder' => esc_attr( mdjm_sanitize_amount( '0.00' ) ),
						'value'       => esc_attr( mdjm_sanitize_amount( $mdjm_event->data['package_price'] ) ),
						'readonly'    => true,
					)
				);
				?>
			</div>

			<div class="mdjm-event-addons-cost">
				<span class="mdjm-repeatable-row-setting-label">
					<?php
					esc_html_e( 'Addons Cost', 'mobile-dj-manager' );
					echo ' ' . esc_html( mdjm_currency_symbol() );
					?>
				</span>

				<?php
				echo MDJM()->html->text( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
					array(
						'name'        => '_mdjm_event_addons_cost',
						'class'       => 'mdjm-currency',
						'placeholder' => esc_attr( mdjm_sanitize_amount( '0.0000' ) ),
						'value'       => esc_attr( mdjm_sanitize_amount( $mdjm_event->data['addons_price'] ) ),
						'readonly'    => true,
					)
				);
				?>
			</div>

		<?php endif; ?>

		<?php if ( mdjm_get_option( 'travel_add_cost', false ) ) : ?>

			<div class="mdjm-event-travel-cost">
				<span class="mdjm-repeatable-row-setting-label">
					<?php
					esc_html_e( 'Travel Cost', 'mobile-dj-manager' );
					echo ' ' . esc_html( mdjm_currency_symbol() );
					?>
				</span>

				<?php
				echo MDJM()->html->text( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
					array(
						'name'        => '_mdjm_event_travel_cost',
						'class'       => 'mdjm-currency',
						'placeholder' => esc_attr( mdjm_sanitize_amount( '0.00' ) ),
						'value'       => esc_attr( mdjm_sanitize_amount( $mdjm_event->data['travel_cost'] ) ),
						'readonly'    => true,
					)
				);
				?>
			</div>

		<?php endif; ?>

	</div>
	<?php

} // mdjm_event_overview_metabox_event_equipment_travel_costs_row
add_action( 'mdjm_event_overview_standard_event_price_sections', 'mdjm_event_overview_metabox_event_equipment_travel_costs_row', 10 );

/**
 * Output the event discount and deposit row
 *
 * @since   1.5
 * @global  obj     $mdjm_event         MDJM_Event class object
 * @global  bool    $mdjm_event_update  True if this event is being updated, false if new.
 * @param   int $event_id           The event ID.
 */
function mdjm_event_overview_metabox_event_discount_deposit_row( $event_id ) {

	global $mdjm_event, $mdjm_event_update;

	?>

	<div class="mdjm-event-price-fields">
		<div class="mdjm-event-additional-cost">
			<span class="mdjm-repeatable-row-setting-label">
				<?php
				esc_html_e( 'Additional Costs', 'mobile-dj-manager' );
				echo ' ' . esc_html( mdjm_currency_symbol() );
				?>
			</span>

			<?php
			echo MDJM()->html->text( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				array(
					'name'        => '_mdjm_event_additional_cost',
					'class'       => 'mdjm-currency',
					'placeholder' => esc_attr( mdjm_sanitize_amount( '0.00' ) ),
					'value'       => esc_attr( mdjm_sanitize_amount( $mdjm_event->data['additional_cost'] ) ),
				)
			);
			?>
		</div>

		<div class="mdjm-event-discount">
			<span class="mdjm-repeatable-row-setting-label">
				<?php
				esc_html_e( 'Discount', 'mobile-dj-manager' );
				echo ' ' . esc_html( mdjm_currency_symbol() );
				?>
			</span>

			<?php
			echo MDJM()->html->text( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				array(
					'name'        => '_mdjm_event_discount',
					'class'       => 'mdjm-currency',
					'placeholder' => esc_attr( mdjm_sanitize_amount( '0.00' ) ),
					'value'       => esc_attr( mdjm_sanitize_amount( $mdjm_event->data['discount'] ) ),
				)
			);
			?>
		</div>

		<div class="mdjm-event-deposit">
			<span class="mdjm-repeatable-row-setting-label">
				<?php
				esc_html_e( 'Deposit', 'mobile-dj-manager' );
				echo ' ' . esc_html( mdjm_currency_symbol() );
				?>
			</span>

			<?php
			echo MDJM()->html->text( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				array(
					'name'        => '_mdjm_event_deposit',
					'class'       => 'mdjm-currency',
					'placeholder' => esc_attr( mdjm_sanitize_amount( '0.00' ) ),
					'value'       => esc_attr( $mdjm_event_update ? mdjm_sanitize_amount( $mdjm_event->deposit ) : mdjm_calculate_deposit( $mdjm_event->price ) ),
				)
			);
			?>
		</div>

	</div>
	<?php

} // mdjm_event_overview_metabox_event_discount_deposit_row
add_action( 'mdjm_event_overview_standard_event_price_sections', 'mdjm_event_overview_metabox_event_discount_deposit_row', 20 );

/**
 * Output the event price row
 *
 * @since   1.5
 * @global  obj     $mdjm_event         MDJM_Event class object
 * @global  bool    $mdjm_event_update  True if this event is being updated, false if new.
 * @param   int $event_id           The event ID.
 */
function mdjm_event_overview_metabox_event_price_row( $event_id ) {

	global $mdjm_event, $mdjm_event_update;

	?>

	<div class="mdjm-event-price-fields">
		<div class="mdjm-event-cost">
			<span class="mdjm-repeatable-row-setting-label">
				<?php
				esc_html_e( 'Total Cost', 'mobile-dj-manager' );
				echo ' ' . esc_html( mdjm_currency_symbol() );
				?>
			</span>

			<?php
			echo MDJM()->html->text( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				array(
					'name'        => '_mdjm_event_cost',
					'class'       => 'mdjm-currency',
					'placeholder' => esc_attr( mdjm_sanitize_amount( '0.00' ) ),
					'value'       => esc_attr( ! empty( $mdjm_event->price ) ? mdjm_sanitize_amount( $mdjm_event->price ) : '' ),
					'readonly'    => true,
				)
			);
			?>
		</div>

	</div>
	<?php

} // mdjm_event_overview_metabox_event_price_row
add_action( 'mdjm_event_overview_standard_event_price_sections', 'mdjm_event_overview_metabox_event_price_row', 30 );

/**
 * Output the event enquiry source row
 *
 * @since   1.3.7
 * @global  obj     $mdjm_event         MDJM_Event class object
 * @global  bool    $mdjm_event_update  True if this event is being updated, false if new.
 * @param   int $event_id           The event ID.
 */
function mdjm_event_metabox_admin_enquiry_source_row( $event_id ) {

	global $mdjm_event, $mdjm_event_update;

	$enquiry_source = mdjm_get_enquiry_source( $event_id );

	?>
	<div class="mdjm_field_wrap mdjm_form_fields">
		<div class="mdjm_col">
			<label for="mdjm_enquiry_source"><?php esc_html_e( 'Enquiry Source:', 'mobile-dj-manager' ); ?></label>
			<?php
			echo MDJM()->html->enquiry_source_dropdown( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				'mdjm_enquiry_source',
				$enquiry_source ? $enquiry_source->term_id : ''
			);
			?>
		</div>
	</div>
	<?php
} // mdjm_event_metabox_admin_enquiry_source_row
add_action( 'mdjm_event_admin_fields', 'mdjm_event_metabox_admin_enquiry_source_row', 10 );

/**
 * Output the employee notes row
 *
 * @since   1.3.7
 * @global  obj     $mdjm_event         MDJM_Event class object
 * @global  bool    $mdjm_event_update  True if this event is being updated, false if new.
 * @param   int $event_id           The event ID.
 */
function mdjm_event_metabox_admin_employee_notes_row( $event_id ) {

	global $mdjm_event, $mdjm_event_update;

	?>
	<div class="mdjm_field_wrap mdjm_form_fields">
		<?php
		echo MDJM()->html->textarea( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			array(
				/* translators: %s artiste */
				'label'       => sprintf( esc_html__( '%s Notes:', 'mobile-dj-manager' ), esc_html( mdjm_get_option( 'artist' ) ) ),
				'name'        => '_mdjm_event_dj_notes',
				'placeholder' => esc_html__( 'This information is not visible to clients', 'mobile-dj-manager' ),
				'value'       => esc_attr( get_post_meta( $event_id, '_mdjm_event_dj_notes', true ) ),
			)
		);
		?>
	</div>

	<?php
} // mdjm_event_metabox_admin_employee_notes_row
add_action( 'mdjm_event_admin_fields', 'mdjm_event_metabox_admin_employee_notes_row', 30 );

/**
 * Output the admin notes row
 *
 * @since   1.3.7
 * @global  obj     $mdjm_event         MDJM_Event class object
 * @global  bool    $mdjm_event_update  True if this event is being updated, false if new.
 * @param   int $event_id           The event ID.
 * @return  str
 */
function mdjm_event_metabox_admin_notes_row( $event_id ) {

	global $mdjm_event, $mdjm_event_update;

	if ( ! mdjm_is_admin() ) {
		return;
	}

	?>
	<div class="mdjm_field_wrap mdjm_form_fields">
		<?php
		echo MDJM()->html->textarea( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			array(
				'label'       => esc_html__( 'Admin Notes:', 'mobile-dj-manager' ),
				'name'        => '_mdjm_event_admin_notes',
				'placeholder' => esc_html__( 'This information is only visible to admins', 'mobile-dj-manager' ),
				'value'       => esc_attr( get_post_meta( $event_id, '_mdjm_event_admin_notes', true ) ),
			)
		);
		?>
	</div>

	<?php
} // mdjm_event_metabox_admin_notes_row
add_action( 'mdjm_event_admin_fields', 'mdjm_event_metabox_admin_notes_row', 40 );

/**
 * Output the event transaction list table
 *
 * @since   1.3.7
 * @global  obj     $mdjm_event         MDJM_Event class object
 * @global  bool    $mdjm_event_update  True if this event is being updated, false if new.
 * @param   int $event_id           The event ID.
 */
function mdjm_event_metabox_txn_list_table( $event_id ) {

	global $mdjm_event, $mdjm_event_update;

	?>
	<p><strong><?php esc_html_e( 'All Transactions', 'mobile-dj-manager' ); ?></strong> <span class="mdjm-small">(<a id="mdjm_txn_toggle" class="mdjm-fake"><?php esc_html_e( 'toggle', 'mobile-dj-manager' ); ?></a>)</span></p>
	<div id="mdjm_event_txn_table" class="mdjm_meta_table_wrap">
		<?php mdjm_do_event_txn_table( $event_id ); ?>
	</div>
	<?php
} // mdjm_event_metabox_txn_list_table
add_action( 'mdjm_event_txn_fields', 'mdjm_event_metabox_txn_list_table', 10 );

/**
 * Output the event transaction list table
 *
 * @since   1.3.7
 * @global  obj     $mdjm_event         MDJM_Event class object
 * @global  bool    $mdjm_event_update  True if this event is being updated, false if new.
 * @param   int $event_id           The event ID.
 */
function mdjm_event_metabox_txn_add_new_row( $event_id ) {

	global $mdjm_event, $mdjm_event_update;

	mdjm_insert_datepicker(
		array(
			'id'       => 'mdjm_txn_display_date',
			'altfield' => 'mdjm_txn_date',
			'maxdate'  => 'today',
		)
	);

	?>

	<div id="mdjm-event-add-txn-table">
		<table id="mdjm_event_add_txn_table" class="widefat mdjm_event_add_txn_table mdjm_form_fields">
			<thead>
				<tr>
					<th colspan="3"><?php esc_html_e( 'Add Transaction', 'mobile-dj-manager' ); ?> <a id="toggle_add_txn_fields" class="mdjm-small mdjm-fake"><?php esc_html_e( 'show form', 'mobile-dj-manager' ); ?></a></th>
				</tr>
			</thead>

			<tbody class="mdjm-hidden">
				<tr>
					<td><label for="mdjm_txn_amount"><?php esc_html_e( 'Amount:', 'mobile-dj-manager' ); ?></label><br />
						<?php
						echo esc_html( mdjm_currency_symbol() ) .
						MDJM()->html->text( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
							array(
								'name'        => 'mdjm_txn_amount',
								'class'       => 'mdjm-input-currency',
								'placeholder' => esc_attr( mdjm_sanitize_amount( '10' ) ),
							)
						);
						?>
						</td>

					<td><label for="mdjm_txn_display_date"><?php esc_html_e( 'Date:', 'mobile-dj-manager' ); ?></label><br />
						<?php
						echo MDJM()->html->text( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
							array(
								'name'  => 'mdjm_txn_display_date',
								'class' => '',
							)
						) .
						MDJM()->html->hidden( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
							array(
								'name' => 'mdjm_txn_date',
							)
						);
						?>
						</td>

					<td><label for="mdjm_txn_amount"><?php esc_html_e( 'Direction:', 'mobile-dj-manager' ); ?></label><br />
						<?php
						echo MDJM()->html->select( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
							array(
								'name'             => 'mdjm_txn_direction',
								'options'          => array(
									'In'  => esc_html__( 'Incoming', 'mobile-dj-manager' ),
									'Out' => esc_html__( 'Outgoing', 'mobile-dj-manager' ),
								),
								'show_option_all'  => false,
								'show_option_none' => false,
							)
						);
						?>
						</td>
				</tr>

				<tr>
					<td><span id="mdjm_txn_from_container"><label for="mdjm_txn_from"><?php esc_html_e( 'From:', 'mobile-dj-manager' ); ?></label><br />
						<?php
						echo MDJM()->html->text( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
							array(
								'name'        => 'mdjm_txn_from',
								'class'       => '',
								'placeholder' => esc_html__( 'Leave empty if client', 'mobile-dj-manager' ),
							)
						);
						?>
						</span>
						<span id="mdjm_txn_to_container" class="mdjm-hidden"><label for="mdjm_txn_to"><?php esc_html_e( 'To:', 'mobile-dj-manager' ); ?></label><br />
						<?php
						echo MDJM()->html->text( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
							array(
								'name'        => 'mdjm_txn_to',
								'class'       => '',
								'placeholder' => esc_html__( 'Leave empty if client', 'mobile-dj-manager' ),
							)
						);
						?>
						</span></td>

					<td><label for="mdjm_txn_for"><?php esc_html_e( 'For:', 'mobile-dj-manager' ); ?></label><br />
						<?php echo MDJM()->html->txn_type_dropdown(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></td>

					<td><label for="mdjm_txn_src"><?php esc_html_e( 'Paid via:', 'mobile-dj-manager' ); ?></label><br />
						<?php
						echo MDJM()->html->select( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
							array(
								'name'         => 'mdjm_txn_src',
								'options'      => mdjm_get_txn_source(), // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
							'selected'         => mdjm_get_option( 'default_type', 'Cash' ), // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
							'show_option_all'  => false,
							'show_option_none' => false,
							)
						);
						?>
						</td>
				</tr>

				<?php if ( mdjm_get_option( 'manual_payment_cfm_template' ) ) : ?>

					<tr id="mdjm-txn-email">
						<td colspan="3">
						<?php
						echo MDJM()->html->checkbox( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
							array(
								'name'    => 'mdjm_manual_txn_email',
								'current' => esc_attr( mdjm_get_option( 'manual_payment_cfm_template' ) ? true : false ),
								'class'   => 'mdjm-checkbox',
							)
						);
						?>
							<?php esc_html_e( 'Send manual payment confirmation email?', 'mobile-dj-manager' ); ?></td>
					</tr>

				<?php endif; ?>

			</tbody>
		</table>

	</div>

	<p id="save-event-txn" class="mdjm-hidden"><a id="save_transaction" class="button button-primary button-small"><?php esc_html_e( 'Add Transaction', 'mobile-dj-manager' ); ?></a></p>
	<?php
} // mdjm_event_metabox_txn_add_new_row
add_action( 'mdjm_event_txn_fields', 'mdjm_event_metabox_txn_add_new_row', 20 );

/**
 * Output the event journal table
 *
 * @since   1.3.7
 * @global  obj     $mdjm_event         MDJM_Event class object
 * @global  bool    $mdjm_event_update  True if this event is being updated, false if new.
 * @param   int $event_id           The event ID.
 */
function mdjm_event_metabox_history_journal_table( $event_id ) {

	global $mdjm_event, $mdjm_event_update;

	$journals = mdjm_get_journal_entries( $event_id );

	$count = count( $journals );
	$i     = 0;

	?>
	<div id="mdjm-event-journal-table">
		<strong><?php esc_html_e( 'Recent Journal Entries', 'mobile-dj-manager' ); ?></strong>
		<table class="widefat mdjm_event_journal_table mdjm_form_fields">
			<thead>
				<tr>
					<th style="width: 20%"><?php esc_html_e( 'Date', 'mobile-dj-manager' ); ?></th>
					<th><?php esc_html_e( 'Excerpt', 'mobile-dj-manager' ); ?></th>
				</tr>
			</thead>

			<tbody>
				<?php if ( $journals ) : ?>
					<?php foreach ( $journals as $journal ) : ?>
						<tr>
							<td><a href="<?php echo esc_url( get_edit_comment_link( $journal->comment_ID ) ); ?>"><?php echo esc_html( date( mdjm_get_option( 'time_format' ) . ' ' . mdjm_get_option( 'short_date_format' ), strtotime( $journal->comment_date ) ) ); ?></a></td>
							<td><?php echo substr( $journal->comment_content, 0, 250 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></td>
						</tr>
						<?php $i++; ?>

						<?php
						if ( $i >= 3 ) {
							break;}
						?>

					<?php endforeach; ?>
				<?php else : ?>
				<tr>
					<?php /* translators: %s Event */ ?>
					<td colspan="2"><?php printf( esc_html__( 'There are no journal entries associated with this %s', 'mobile-dj-manager' ), esc_html( mdjm_get_label_singular( true ) ) ); ?></td>
				</tr>
				<?php endif; ?>

			</tbody>

			<?php if ( $journals ) : ?>
				<tfoot>
					<tr>
						<?php /* translators: %s Journal */ ?>
						<td colspan="2"><span class="description">(<?php printf( __( 'Displaying the most recent %1$d entries of <a href="%2$s">%3$d total', 'mobile-dj-manager' ), ( $count >= 3 ) ? 3 : $count, add_query_arg( array( 'p' => $event_id ), admin_url( 'edit-comments.php?p=5636' ) ), $count ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>)</span></td>
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
 * @since   1.3.7
 * @global  obj     $mdjm_event         MDJM_Event class object
 * @global  bool    $mdjm_event_update  True if this event is being updated, false if new.
 * @param   int $event_id           The event ID.
 * @return  str
 */
function mdjm_event_metabox_history_emails_table( $event_id ) {

	global $mdjm_event, $mdjm_event_update;

	if ( ! mdjm_get_option( 'track_client_emails' ) ) {
		return;
	}

	$emails = mdjm_event_get_emails( $event_id );
	$count  = count( $emails );
	$i      = 0;

	?>
	<div id="mdjm-event-emails-table">
		<strong><?php esc_html_e( 'Associated Emails', 'mobile-dj-manager' ); ?></strong>
		<table class="widefat mdjm_event_emails_table mdjm_form_fields">
			<thead>
				<tr>
					<th><?php esc_html_e( 'Date', 'mobile-dj-manager' ); ?></th>
					<th><?php esc_html_e( 'Subject', 'mobile-dj-manager' ); ?></th>
					<th><?php esc_html_e( 'Status', 'mobile-dj-manager' ); ?></th>
				</tr>
			</thead>

			<tbody>
				<?php if ( $emails ) : ?>
					<?php foreach ( $emails as $email ) : ?>
						<tr>
							<td><?php echo esc_html( date( mdjm_get_option( 'time_format', 'H:i' ) . ' ' . mdjm_get_option( 'short_date_format' ), strtotime( $email->post_date ) ) ); ?></td>
							<td><a href="<?php echo esc_url( get_edit_post_link( $email->ID ) ); ?>"><?php echo esc_html( get_the_title( $email->ID ) ); ?></a></td>
							<td>
							<?php
							echo esc_html( get_post_status_object( $email->post_status )->label );

							if ( ! empty( $email->post_modified ) && 'opened' === $email->post_status ) :
								?>
								<?php echo '<br />'; ?>
								<span class="description"><?php echo esc_html( date( mdjm_get_option( 'time_format', 'H:i' ) . ' ' . mdjm_get_option( 'short_date_format', 'd/m/Y' ), strtotime( $email->post_modified ) ) ); ?></span>
							<?php endif; ?></td>
						</tr>
						<?php $i++; ?>

						<?php
						if ( $i >= 3 ) {
							break;}
						?>

					<?php endforeach; ?>
				<?php else : ?>
				<tr>
					<?php /* translators: %s Event */ ?>
					<td colspan="3"><?php printf( esc_html__( 'There are no emails associated with this %s', 'mobile-dj-manager' ), esc_html( mdjm_get_label_singular( true ) ) ); ?></td>
				</tr>
				<?php endif; ?>

			</tbody>

			<?php if ( $emails ) : ?>
				<tfoot>
					<tr>
						<?php /* translators: %1 %2 Event Details */ ?>
						<td colspan="3"><span class="description">(<?php printf( esc_html__( 'Displaying the most recent %1$d emails of %2$d total', 'mobile-dj-manager' ), ( $count >= 3 ) ? 3 : esc_html( $count ), esc_html( $count ) ); ?>)</span></td>
					</tr>
				</tfoot>
			<?php endif; ?>

		</table>
	</div>
	<?php
} // mdjm_event_metabox_emails_table
add_action( 'mdjm_event_history_fields', 'mdjm_event_metabox_history_emails_table', 20 );
