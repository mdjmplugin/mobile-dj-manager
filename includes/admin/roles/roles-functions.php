<?php

/**
 * Contains all role functions.
 *
 * @package		MDJM
 * @subpackage	Roles
 * @since		1.3
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) )
	exit;

/**
 * Return all registered MDJM roles.
 *
 * @since	1.3
 * @global	$wp_roles
 * @param	str|arr		$which_roles	Which roles to retrieve
 * @return	arr			$mdjm_roles		Array of MDJM registered roles
 */
function mdjm_get_roles( $which_roles = array() )	{
	global $wp_roles;
	
	// Retrieve all roles within this WP instance
	$roles      = $wp_roles->get_names();
	$mdjm_roles = array();

	if ( ! empty( $which_roles ) && ! is_array( $which_roles ) )	{
		$which_roles = array( $which_roles );
	}
				
	// Loop through the $raw_roles and filter for mdjm specific roles
	foreach( $roles as $role_id => $role_name )	{
		
		if( ! empty( $which_roles ) && ! in_array( $role_id, $which_roles ) )	{
			continue;
		}
				
		if( $role_id == 'dj' || strpos( $role_id, 'mdjm-' ) !== false )	{			
			$mdjm_roles[ $role_id ] = $role_name;
		}
		
	}
		
	// Filter the roles
	return apply_filters( 'mdjm_user_roles', $mdjm_roles );
} // mdjm_get_roles
