<?php
/**
 * Contains all equipment and package related functions
 *
 * @package		MDJM
 * @subpackage	Venues
 * @since		1.3
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) )
	exit;

/**
 * Whether or not packages are enabled.
 *
 * @since	1.3
 * @return	bool	True if packages are enabled, false if they are not
 */
function mdjm_packages_enabled()	{
	return mdjm_get_option( 'enable_packages', false );
} // mdjm_packages_enabled

/**
 * Get all packages.
 *
 * @since	1.4
 * @param	arr			$args	Array of arguments. See @get_posts.
 * @return	arr|bool	Package details, or false if none.
 */
function mdjm_get_packages( $args = array() )	{

	$defaults = array(
		'posts_per_page' => -1,
		'orderby'        => 'post_title',
		'order'          => 'DESC',
		'post_type'      => 'mdjm-package',
		'post_status'    => 'publish'
	);

	$package_args = wp_parse_args( $args, $defaults );

	return apply_filters( 'mdjm_get_packages', get_posts( $package_args ) );

} // mdjm_get_packages

/**
 * Get package by
 *
 * Retrieve a package by the given field.
 *
 * @since	1.4
 * @param	str			$field		The field by which to retrieve.
 * @param	mixed		$value		The value of the field to match.
 * @return	obj			The WP Post object.		
 */
function mdjm_get_package_by( $field, $value )	{

	if ( 'ID' == $field )	{
		$field = 'id';
	}

	switch( $field )	{
		case 'id':
		default:
			$package = get_post( $value );
			break;
		
		case 'name':
			$package = get_page_by_title( $value, 'OBJECT', 'mdjm-package' );
			break;
		
		case 'slug':
			$package = get_page_by_path( $value, 'OBJECT', 'mdjm-package' );
			break;
	}
	
	return $package;

} // mdjm_get_package_by

/**
 * Get all packages for the given employee.
 *
 * @since	1.3
 * @param	int			$employee_id	The employee ID whose packages we want.
 * @param	bool		$enabled		True returns only enabled packages, false returns all.
 * @return	obj			The WP Post objects for the employee's packages.		
 */
function mdjm_get_packages_by_employee( $employee_id = 0, $enabled = true )	{

	if ( empty( $employee_id ) )	{
		$employee_id = get_current_user_id();
	}

	$employee_meta_query = array(
		'key'     => '_mdjm_employees',
		'value'   => array( $employee_id ),
		'compare' => 'IN'
	);

	$meta_query = array( $employee_meta_query );

	$args = array(
		'posts_per_page' => -1,
		'orderby'        => 'post_title',
		'order'          => 'DESC',
		'post_type'      => 'mdjm-package',
		'post_status'    => $enabled ? 'publish' : 'any',
		'meta_query'     => array( $meta_query )
	);

	$packages = get_posts( $args );

	return apply_filters( 'mdjm_get_packages_by_employee', $packages );

} // mdjm_get_packages_by_employee

/**
 * Retrieve the cost of the given package.
 *
 * @since	1.3
 * @param	int		$package_id		The post ID of the package.
 * @return	str		Cost of the package.
 */
function mdjm_get_package_price( $package_id )	{
	$cost = get_post_meta( $package_id, '_package_cost', true );

	return apply_filters( 'mdjm_get_package_price', $cost );
} // mdjm_get_package_price

/**
 * List all available packages. If an employee ID is provided, list what that 
 * employee can provide only.
 *
 * @since	1.3
 * @param	int		$employee_id	An employee user ID, otherwise query current user.
 * @param	bool	$price			True to display the formatted package price
 * @return	str		HTML formatted string listing package information
 */
function mdjm_list_available_packages( $employee_id = 0, $price = false )	{

	if( ! mdjm_packages_enabled() )	{
		return __( 'No packages available', 'mobile-dj-manager' );
	}
	
	if( ! empty( $employee_id ) )	{
		$packages = mdjm_get_packages_by_employee( $employee_id, false );
	} else	{
		$packages = mdjm_get_packages();
	}
	
	if( empty( $packages ) )	{
		return __( 'No packages available', 'mobile-dj-manager' );
	}
	
	$output = '';
	$i = 0;
	
	foreach( $packages as $package )	{
		if( $i > 0 )	{
			$output .= '<br>';
		}
		
		$output .= get_the_title( $package->ID );
		
		if( $price )	{
			$output .= ' ' . mdjm_get_package_price( $package->ID );
		}
		
		$i++;
	}
	
	return $output;

} // mdjm_list_available_packages

/***********************************************************
 * Addon Functions
 **********************************************************/

/**
 * Retrieve all addons.
 *
 * @since	1.4
 * @param	arr			$args	Array of arguments. See @get_posts.
 * @return	arr|bool	Addons.
 */
function mdjm_get_addons( $args = array() )	{

	$defaults = array(
		'posts_per_page' => -1,
		'orderby'        => 'post_title',
		'order'          => 'DESC',
		'post_type'      => 'mdjm-addon',
		'post_status'    => 'publish'
	);

	$package_args = wp_parse_args( $args, $defaults );

	return apply_filters( 'mdjm_get_addons', get_posts( $package_args ) );

} // mdjm_get_addons

/**
 * Retrieve all employees with addon.
 *
 * @since	1.4
 * @param	int			$addon_id	ID of the addon.
 * @return	arr|false	Array of employee ID's with the addon or false if none.
 */
function mdjm_get_employees_with_addon( $addon_id )	{
	$employees = get_post_meta( $addon_id, '_addon_employees', true );

	return apply_filters( 'mdjm_employees_with_addon', $employees, $addon_id );
} // mdjm_get_employees_with_addon

/**
 * Whether or not this addon is restricted by date.
 *
 * @since	1.4
 * @param	int		$addon_id	ID of the addon.
 * @return	bool	True if restricted, otherwise false.
 */
function mdjm_addon_is_restricted_by_date( $addon_id )	{
	$restricted = get_post_meta( $addon_id, '_addon_restrict_date', true );

	// If the addon is restricted, there needs to be months set for availability
	if ( $restricted )	{
		$months = get_post_meta( $addon_id, '_addon_months_available', true );

		if ( ! $months )	{
			$restricted = false;
		}
	}

	return apply_filters( 'mdjm_addon_is_restricted_by_date', $restricted, $addon_id );

} // mdjm_addon_is_restricted_by_date
