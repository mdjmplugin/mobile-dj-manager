<?php
/**
 * Exports Functions
 *
 * These are functions are used for exporting data from MDJM Event Management.
 *
 * @package     MDJM
 * @subpackage  Admin/Export
 * @copyright   Copyright (c) 2016, Mike Howard
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) )
	exit;

require_once( MDJM_PLUGIN_DIR . '/includes/admin/reporting/class-mdjm-export.php' );
require_once( MDJM_PLUGIN_DIR . '/includes/admin/reporting/export/export-actions.php' );

/**
 * Exports earnings for a specified time period
 * MDJM_Earnings_Export class.
 *
 * @since	1.4
 * @return	void
 */
function mdjm_export_earnings() {
	require_once( MDJM_PLUGIN_DIR . '/includes/admin/reporting/class-mdjm-export-earnings.php' );

	$earnings_export = new MDJM_Earnings_Export();

	$earnings_export->export();
} // mdjm_export_earnings
add_action( 'mdjm-earnings_export', 'mdjm_export_earnings' );

/**
 * Process batch exports via ajax
 *
 * @since	1.4
 * @return	void
 */
function mdjm_do_ajax_export() {

	require_once( MDJM_PLUGIN_DIR . '/includes/admin/reporting/export/class-batch-export.php' );

	parse_str( $_POST['form'], $form );

	$_REQUEST = $form = (array) $form;

	if ( ! wp_verify_nonce( $_REQUEST['mdjm_ajax_export'], 'mdjm_ajax_export' ) ) {
		die( '-2' );
	}

	do_action( 'mdjm_batch_export_class_include', $form['mdjm-export-class'] );

	$step     = absint( $_POST['step'] );
	$class    = sanitize_text_field( $form['mdjm-export-class'] );
	$export   = new $class( $step );

	if( ! $export->can_export() ) {
		die( '-1' );
	}

	if ( ! $export->is_writable ) {
		echo json_encode( array( 'error' => true, 'message' => __( 'Export location or file not writable', 'mobile-dj-manager' ) ) ); exit;
	}

	$export->set_properties( $_REQUEST );

	// Allow a bulk processor to pre-fetch some data to speed up the remaining steps and cache data
	$export->pre_fetch();

	$ret = $export->process_step( $step );

	$percentage = $export->get_percentage_complete();

	if( $ret ) {

		$step += 1;
		echo json_encode( array( 'step' => $step, 'percentage' => $percentage ) ); exit;

	} elseif ( true === $export->is_empty ) {

		echo json_encode( array( 'error' => true, 'message' => __( 'No data found for export parameters', 'mobile-dj-manager' ) ) ); exit;

	} elseif ( true === $export->done && true === $export->is_void ) {

		$message = ! empty( $export->message ) ? $export->message : __( 'Batch Processing Complete', 'mobile-dj-manager' );
		echo json_encode( array( 'success' => true, 'message' => $message ) ); exit;

	} else {

		$args = array_merge( $_REQUEST, array(
			'step'       => $step,
			'class'      => $class,
			'nonce'      => wp_create_nonce( 'mdjm-batch-export' ),
			'mdjm_action' => 'download_batch_export',
		) );

		$event_url = add_query_arg( $args, admin_url() );

		echo json_encode( array( 'step' => 'done', 'url' => $event_url ) ); exit;

	}
} // mdjm_do_ajax_export
add_action( 'wp_ajax_mdjm_do_ajax_export', 'mdjm_do_ajax_export' );
