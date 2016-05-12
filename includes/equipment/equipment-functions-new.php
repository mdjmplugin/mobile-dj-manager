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
 * Get all packages.
 * 
 *
 * @since	1.3
 * @param
 * @param
 * @return	arr|bool		Package details, or false if none.
 */
function mdjm_get_packages( $args=array( '' ) )	{
	// The default args can be filtered
	$defaults = array(
		'posts_per_page'   => -1,
		'orderby'          => 'post_title',
		'order'            => 'DESC',
		'post_type'        => 'mdjm-package',
		'post_status'      => 'publish'
	);
	
	$package_args = wp_parse_args( $args, $defaults );
	
	return get_posts( $package_args );
} // mdjm_get_packages

/**
 * Get package by
 *
 * Retrieve a package by the given field.
 *
 * @since	1.3
 * @param	str			$field		The field by which to retrieve.
 * @param	mixed		$value		The value of the field to match.
 * @return	obj						The WP Post object.		
 */
function mdjm_get_package_by( $field, $value )	{
	switch( $field )	{
		case 'id' :
			$package = get_post( $value );
		break;
		
		case 'name' :
			$package = get_page_by_title( $value, 'OBJECT', 'mdjm-package' );
		break;
		
		case 'slug' :
			$package = get_page_by_path( $value, 'OBJECT', 'mdjm-package' );
		break;
	}
	
	return $package;
} // mdjm_get_package_by

/**
 * Get all packages for the given employee.
 *
 * @since	1.3
 * @param	int			$employee_id	Required: The employee ID whose packages we want.
 * @param	bool		$all			Optional: True returns all the packages, false only those that are enabled.
 * @return	obj			The WP Post objects for the employee's packages.		
 */
function mdjm_get_packages_by_employee( $employee_id, $all=true )	{
	
	$employee_meta_query = array(
		'key'     => '_mdjm_employees',
		'value'   => array( $employee_id ),
		'compare' => 'IN'
	);
	
	$meta_query = array( $employee_meta_query );
	
	if( ! empty( $all ) )	{
		$meta_query = array(
			'relation'	=> 'AND',
			array(
				'key'     => '_mdjm_status',
				'value'   => 'enabled',
				'compare' => '='
			),
			$employee_meta_query
		);
	}
	
	return get_posts(
		array(
			'posts_per_page'	=> -1,
			'orderby'			=> 'post_title',
			'order'				=> 'DESC',
			'post_type'			=> 'mdjm-package',
			'post_status'		=> 'publish',
			'meta_query'		=> array( $meta_query )
		)
	);
} // mdjm_get_packages_by_employee

/**
 * Retrieve the cost of the given package.
 *
 * @since	1.3
 * @param	int		$package_id		Required: The post ID of the package.
 * @return	str		Formatted cost of the package.
 */
function mdjm_get_package_price( $package_id )	{
	$cost = get_post_meta( $package_id, '_mdjm_cost', true );
	
	return mdjm_currency_filter( mdjm_sanitize_amount( $cost ) );
} // mdjm_get_package_price

/**
 * List all available packages. If an employee ID is provided, list what that 
 * employee can provide only.
 *
 * @since	1.3
 * @param	int		$employee_id	Optional: An employee user ID, otherwise query current user.
 * @param	bool	$show_price		Optional: True to display the formatted package price
 * @return	str		HTML formatted string listing package information
 */
function mdjm_list_available_packages( $employee_id='', $show_price=false )	{
	//If packages are not enabled
	if( ! mdjm_packages_enabled() )	{
		return __( 'No packages available', 'mobile-dj-manager' );
	}
	
	// Get the packages
	if( ! empty( $employee_id ) )	{
		$packages = mdjm_get_packages_by_employee( $employee_id, false );
	}
	else	{
		$packages = mdjm_get_packages();
	}
	
	// No packages
	if( empty( $packages ) )	{
		return __( 'No packages available', 'mobile-dj-manager' );
	}
	
	$return = '';
	$i = 0;
	
	foreach( $packages as $package )	{
		if( $i > 0 )	{
			$return .= '<br>';
		}
		
		$return .= get_the_title( $package->ID );
		
		if( ! empty( $show_price ) )	{
			$return .= ' ' . mdjm_get_package_price( $package->ID );
		}
		
		$i++;
	}
	
	return $return;
} // mdjm_list_available_packages