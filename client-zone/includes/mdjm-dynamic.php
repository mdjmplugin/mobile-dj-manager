<?php
/*
Page Name: mdjm-dynamic.php
Description: Handles all fron end Ajax requests
Since Version: 1.2.3
Date: 07 July 2015
Author: My DJ Planner <contact@mydjplanner.co.uk>
Author URI: http://www.mydjplanner.co.uk
*/
 
	/*
	 * Update the Addons select list based on Package selections
	 *
	 * @param	str		$package	Optional: The slug of the current package. If empty (default), no current package
	 *								Otherwise query package for it's items and remove those from available addons
	 *			str		$dj			The DJ for the event
	 * @return	arr		$addons		array of 
	 */
	function update_addon_options()	{
		$dj = $_POST['dj'];
		$event_package = $_POST['package'];
		$addons = mdjm_addons_dropdown( array( 
											'name'		=> 'event_addons',
											'dj'		=> !empty( $dj ) ? $dj : '',
											'package'	=> !empty( $event_package ) ? $event_package : '',
											), false );
				
		if( !empty( $addons ) )	{
			$result['type'] = 'success';
			$result['addons'] = $addons;
		}
		else	{
			$result['type'] = 'error';
			$result['msg'] = 'No addons available';
		}
		echo json_encode( $result );
		
		die();
	} // update_addon_options
	add_action( 'wp_ajax_mdjm_update_addon_options', 'update_addon_options' );
	add_action( 'wp_ajax_nopriv_mdjm_update_addon_options', 'update_addon_options' );
	
	/*
	 * Update the Addons select list based on Package selections within dynamic contact forms
	 *
	 * @param	str		$package	Optional: The slug of the current package. If empty (default), no current package
	 *								Otherwise query package for it's items and remove those from available addons
	 * @return	arr		$addons		array of 
	 */
	function update_contact_form_addon_options()	{
		$event_package = $_POST['package'];
		$addons = mdjm_addons_dropdown( array( 
											'name'		=> $_POST['addons_field'],
											'package'	=> !empty( $event_package ) ? $event_package : '',
											), false );
				
		if( !empty( $addons ) )	{
			$result['type'] = 'success';
			$result['addons'] = $addons;
		}
		else	{
			$result['type'] = 'error';
			$result['msg'] = 'No addons available';
		}
		
		echo json_encode( $result );
		
		die();
	} // update_contact_form_addon_options
	add_action( 'wp_ajax_mdjm_update_contact_form_addon_options', 'update_contact_form_addon_options' );
	add_action( 'wp_ajax_nopriv_mdjm_update_contact_form_addon_options', 'update_contact_form_addon_options' );
