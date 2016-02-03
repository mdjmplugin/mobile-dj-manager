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
 * @param	bool	$exclude_admin	True exlucdes the Administrator role from the results. False includes.
 * @return	arr		$mdjm_roles		Array of MDJM registered roles
 */
function mdjm_get_roles( $exclude_admin=true )	{
	global $wp_roles;
	
	// Retrieve all roles within this WP instance
	$roles = $wp_roles->get_names();
				
	// Loop through the $raw_roles and filter for mdjm specific roles
	foreach( $roles as $role_id => $role_name )	{
		if( $role_id == 'dj' || strpos( $role_id, 'mdjm-' ) !== false )	{
			// Ignore administrators if $exclude_admin is true
			if( $role_id == 'administrator' && empty( $exclude_admin ) )	{
				continue;
			}
			
			$mdjm_roles[$role_id] = $role_name;
		}
	}
		
	// Filter the roles
	return apply_filters( 'mdjm_user_roles', $mdjm_roles );
} // mdjm_get_roles