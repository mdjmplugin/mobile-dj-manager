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
			$result['type'] = 'success';
			$result['addons'] = '<option value="0" disabled="disabled">' .
				 __( 'No addons available', 'mobile-dj-manager' ) . '</option>';
			//$result['type'] = 'error';
			//$result['msg'] = 'No addons available';
		}
		echo json_encode( $result );
		
		die();
	} // update_addon_options
	add_action( 'wp_ajax_mdjm_update_addon_options', 'update_addon_options' );
	add_action( 'wp_ajax_nopriv_mdjm_update_addon_options', 'update_addon_options' );
		
	/*
	 * create a new post so that the ID can be used
	 * Generally used for the Payments page for creating an Invoice ID
	 *
	 * @param:	str		$type			Required: The type of post to create
	 *			
	 * @return	The post ID
	 */
	function mdjm_create_post()	{
		$transaction = get_default_post_to_edit( $_POST['mdjm_post_type'], true );
		
		if( empty( $transaction ) )
			return $result['type'] = 'error';
		
		$response['type'] = 'success';
		$response['id'] = $transaction->ID;
		
		echo json_encode( $response );
		
		die();
			
	} // mdjm_create_post
	add_action( 'wp_ajax_mdjm_create_post', 'mdjm_create_post' );
	add_action( 'wp_ajax_nopriv_mdjm_create_post', 'mdjm_create_post' );
