<?php
/**
 * Upgrade Functions
 *
 * @package     MDJM
 * @subpackage  Admin/Upgrades
 * @copyright   Copyright (c) 2016, Mike Howard
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.4
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) )
	exit;

/**
 * Display a notice if an upgrade is required.
 *
 * @since	1.4
 */
function mdjm_show_upgrade_notice()	{

	if ( isset( $_GET['page'] ) && $_GET['page'] == 'mdjm-upgrades' )	{
		return;
	}

	$mdjm_version = get_option( 'mdjm_version' );

	$mdjm_version = preg_replace( '/[^0-9.].*/', '', $mdjm_version );

	// Check if there is an incomplete upgrade routine.
	$resume_upgrade = mdjm_maybe_resume_upgrade();

	if ( ! empty( $resume_upgrade ) )	{

		$resume_url = add_query_arg( $resume_upgrade, admin_url( 'index.php' ) );
		printf(
			'<div class="error"><p>' . __( 'MDJM Event Management needs to complete an upgrade that was previously started. Click <a href="%s">here</a> to resume the upgrade.', 'mobile-dj-manager' ) . '</p></div>',
			esc_url( $resume_url )
		);

	} else {

		if ( version_compare( $mdjm_version, '1.4', '<' ) || ! mdjm_has_upgrade_completed( 'upgrade_packages_to_posts' ) )	{
			printf(
				'<div class="updated"><p>' . __( 'MDJM Event Management needs to perform an upgrade to the Packages and Add-ons. Click <a href="%s">here</a> to start the upgrade.', 'mobile-dj-manager' ) . '</p></div>',
				esc_url( admin_url( 'index.php?page=mdjm-upgrades&mdjm-upgrade=upgrade_packages_to_posts' ) )
			);
		}

	}

} // mdjm_show_upgrade_notice
add_action( 'admin_notices', 'mdjm_show_upgrade_notice' );

/**
 * Triggers all upgrade functions.
 *
 * This function is usually triggered via AJAX.
 *
 * @since	1.4
 * @return	void
*/
function mdjm_trigger_upgrades() {

	if( ! mdjm_employee_can( 'manage_mdjm' ) ) {
		wp_die( __( 'You do not have permission to do perform MDJM upgrades', 'mobile-dj-manager' ), __( 'Error', 'mobile-dj-manager' ), array( 'response' => 403 ) );
	}

	$mdjm_version = get_option( 'mdjm_version' );

	if ( version_compare( $mdjm_version, '1.4', '<' ) )	{
		mdjm_v14_upgrades();
	}

	update_option( 'mdjm_version', MDJM_VERSION_NUM );

	if ( DOING_AJAX )	{
		die( 'complete' ); // Let AJAX know that the upgrade is complete
	}

} // mdjm_trigger_upgrades
add_action( 'wp_ajax_mdjm_trigger_upgrades', 'mdjm_trigger_upgrades' );

/**
 * For use when doing 'stepped' upgrade routines, to see if we need to start somewhere in the middle.
 *
 * @since	1.4
 * @return	mixed	When nothing to resume returns false, otherwise starts the upgrade where it left off.
 */
function mdjm_maybe_resume_upgrade() {

	$doing_upgrade = get_option( 'mdjm_doing_upgrade', false );

	if ( empty( $doing_upgrade ) ) {
		return false;
	}

	return $doing_upgrade;

} // mdjm_maybe_resume_upgrade

/**
 * Adds an upgrade action to the completed upgrades array.
 *
 * @since	1.4
 * @param	str		$upgrade_action		The action to add to the copmleted upgrades array.
 * @return	bool	If the function was successfully added.
 */
function mdjm_set_upgrade_complete( $upgrade_action = '' ) {

	if ( empty( $upgrade_action ) ) {
		return false;
	}

	$completed_upgrades   = mdjm_get_completed_upgrades();
	$completed_upgrades[] = $upgrade_action;

	// Remove any blanks, and only show uniques
	$completed_upgrades = array_unique( array_values( $completed_upgrades ) );

	return update_option( 'mdjm_completed_upgrades', $completed_upgrades );
} // mdjm_set_upgrade_complete

/**
 * Migrates event packages and addons from the options table to posts.
 *
 * @since	1.4
 * @return	void
 */
function mdjm_v14_upgrade_packages_to_posts()	{

	if( ! mdjm_employee_can( 'manage_mdjm' ) ) {
		wp_die( __( 'You do not have permission to do perform MDJM upgrades', 'mobile-dj-manager' ), __( 'Error', 'mobile-dj-manager' ), array( 'response' => 403 ) );
	}

	ignore_user_abort( true );

	$step   = isset( $_GET['step'] ) ? absint( $_GET['step'] ) : 1;
	$number = 50;
	$offset = $step == 1 ? 0 : ( $step - 1 ) * $number;

	

} // mdjm_v14_upgrade_packages_to_posts
add_action( 'mdjm_upgrade_packages_to_posts', 'mdjm_v14_upgrade_packages_to_posts' );
