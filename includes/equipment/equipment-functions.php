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
 * Retrieve a single package.
 *
 * @since	1.4
 * @param	int		$package_id	The ID of the package
 * @return	WP_Post object.
 */
function mdjm_get_package( $package_id )	{
	return mdjm_get_package_by( 'id', $package_id );
} // mdjm_get_package

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

	if ( empty( $field ) || empty( $value ) )	{
		return false;
	}

	switch( strtolower( $field ) )	{

		case 'id':
			$package = get_post( $value );

			if ( get_post_type( $package ) != 'mdjm-package' ) {
				return false;
			}

			break;

		case 'slug':
		case 'name':
			$package = get_posts( array(
				'post_type'      => 'mdjm-package',
				'name'           => $value,
				'posts_per_page' => 1,
				'post_status'    => 'any'
			) );

			if( $package ) {
				$package = $package[0];
			}

			break;

		case 'event':
			$event_package = get_post_meta( $value, '_mdjm_event_package', true );

			if ( ! $event_package )	{
				return false;
			}

			$package = get_post( $event_package );

			if ( get_post_type( $package ) != 'mdjm-package' ) {
				return false;
			}

			break;

	}
	
	return $package;

} // mdjm_get_package_by

/**
 * Retrieve a package name.
 *
 * @since	1.4
 * @param	int		$package_id		ID of the package.
 * @return	str		The package title.
 */
function mdjm_get_package_name( $package_id )	{
	$title = get_the_title( $package_id );

	return apply_filters( 'mdjm_package_name', $title, $addon_id );
} // mdjm_get_package_name

/**
 * Retrieve all employees with package.
 *
 * @since	1.4
 * @param	int			$addon_id	ID of the package.
 * @return	arr|false	Array of employee ID's with the package or false if none.
 */
function mdjm_get_employees_with_package( $package_id )	{
	$employees = get_post_meta( $package_id, '_package_employees', true );

	return apply_filters( 'mdjm_employees_with_addon', $employees, $package_id );
} // mdjm_get_employees_with_package

/**
 * Whether or not this package is restricted by date.
 *
 * @since	1.4
 * @param	int		$package_id	ID of the package.
 * @return	bool	True if restricted, otherwise false.
 */
function mdjm_package_is_restricted_by_date( $package_id )	{
	$restricted = get_post_meta( $package_id, '_package_restrict_date', true );

	// If the package is restricted, there needs to be months set for availability
	if ( $restricted )	{
		$months = mdjm_addon_get_months_available( $package_id );

		if ( ! $months )	{
			$restricted = false;
		}
	}

	return apply_filters( 'mdjm_package_is_restricted_by_date', $restricted, $package_id );

} // mdjm_package_is_restricted_by_date

/**
 * Retrieve the months the package is available.
 *
 * @since	1.4
 * @param	int			$package_id	ID of the package.
 * @return	arr|false	Array of month numbers this package is available, otherwise false.
 */
function mdjm_package_get_months_available( $package_id )	{
	$months = get_post_meta( $package_id, '_package_months', true );

	return apply_filters( 'mdjm_package_months_available', $months, $package_id );
} // mdjm_package_get_months_available

/**
 * Retrieve the price of the package.
 *
 * @since	1.4
 * @param	int		$package_id	ID of the package.
 * @return	str		The cost of the package.
 */
function mdjm_package_get_price( $package_id )	{
	$price = get_post_meta( $package_id, '_package_price', true );

	return apply_filters( 'mdjm_package_price', $price, $package_id );
} // mdjm_package_get_price

/**
 * Whether or not the package has monthly prices.
 *
 * @since	1.4
 * @param	int		$addon_id	ID of the addon.
 * @return	bool	True if variable pricing is enabled.
 */
function mdjm_package_has_variable_prices( $package_id )	{
	$variable_pricing = get_post_meta( $package_id, '_package_variable_pricing', true );

	return apply_filters( 'mdjm_package_has_variable_pricing', $variable_pricing, $package_id );
} // mdjm_package_has_variable_prices

/**
 * Retrieve a packages monthly prices.
 *
 * @since	1.4
 * @param	int		$addon_id	ID of the package.
 * @return	bool	True if variable pricing is enabled.
 */
function mdjm_package_get_variable_prices( $package_id )	{
	$variable_pricing = get_post_meta( $package_id, '_package_variable_prices', true );

	return apply_filters( 'mdjm_package_monthly_pricing', $variable_pricing, $package_id );
} // mdjm_package_get_variable_prices

/**
 * Retrieve a packages price range.
 *
 * @since	1.4
 * @param	int		$package_id	ID of the package.
 * @return	arr		Array of low and high prices.
 */
function mdjm_package_get_price_range( $package_id )	{

	if ( ! mdjm_package_has_variable_prices( $package_id ) )	{
		return;
	}

	$range = array();

	$prices = mdjm_package_get_variable_prices( $package_id );

	foreach ( $prices as $price )	{

		if ( empty( $range['low'] ) || $price['amount'] < $range['low'] )	{
			$range['low'] = $price['amount'];
		}

		if ( empty( $range['high'] ) || $price['amount'] > $range['high'] )	{
			$range['high'] = $price['amount'];
		}

	}

	return apply_filters( 'mdjm_package_price_range', $range, $package_id );

} // mdjm_package_get_price_range

/**
 * Retrieve the items in a package.
 *
 * @since	1.4
 * @param	int			$package_id		The package ID.
 * @return	arr|false	Array of addon ID's in this package, or false if none.		
 */
function mdjm_package_get_items( $package_id )	{
	$items = get_post_meta( $package_id, '_package_items', true );

	return apply_filters( 'mdjm_package_items', $items, $package_id );
} // mdjm_package_get_items

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
 * Retrieve an events package.
 *
 * @since	1.4
 * @param	int			$event_id	The event ID.
 * @return	int|false	The event package or false if no package.
 */
function mdjm_get_event_package( $event_id )	{
	$package = get_post_meta( $event_id, '_mdjm_event_package', true );

	return apply_filters( 'mdjm_event_package', $package );
} // mdjm_get_event_package

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
			$output .= ' ' . mdjm_package_get_price( $package->ID );
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

	$addon_args = wp_parse_args( $args, $defaults );

	return apply_filters( 'mdjm_get_addons', get_posts( $addon_args ) );

} // mdjm_get_addons

/**
 * Retrieve a single addon.
 *
 * @since	1.4
 * @param	int		$addon_id	The ID of the addon
 * @return	WP_Post object.
 */
function mdjm_get_addon( $addon_id )	{
	return mdjm_get_addon_by( 'id', $addon_id );
} // mdjm_get_addon

/**
 * Get addon by
 *
 * Retrieve an addon by the given field.
 *
 * @since	1.4
 * @param	str			$field		The field by which to retrieve.
 * @param	mixed		$value		The value of the field to match.
 * @return	obj			The WP Post object.		
 */
function mdjm_get_addon_by( $field, $value )	{

	if ( empty( $field ) || empty( $value ) )	{
		return false;
	}

	switch( strtolower( $field ) )	{

		case 'id':
			$addon = get_post( $value );

			if ( get_post_type( $addon ) != 'mdjm-addon' ) {
				return false;
			}

			break;

		case 'slug':
		case 'name':
			$addon = get_posts( array(
				'post_type'      => 'mdjm-addon',
				'name'           => $value,
				'posts_per_page' => 1,
				'post_status'    => 'any'
			) );

			if( $addon ) {
				$addon = $addon[0];
			}

			break;

	}
	
	return $addon;

} // mdjm_get_addon_by

/**
 * Retrieve all add-ons in the given category.
 *
 * @since	1.4
 * @param	int|arr		$terms	The category ID.
 * @return	arr|bool	Addons.
 */
function mdjm_get_addons_in_category_id( $term_id )	{

	$args = array(
		'tax_query' => array(
			'taxonomy'         => 'addon-category',
			'terms'            => $term_id,
			'include_children' => false
		)
	);

	return mdjm_get_addons( $args );

} // mdjm_get_addons_in_category_id

/**
 * Retrieve an addons name.
 *
 * @since	1.4
 * @param	int		$addon_id	ID of the addon.
 * @return	str		The addon title.
 */
function mdjm_get_addon_name( $addon_id )	{
	$title = get_the_title( $addon_id );

	return apply_filters( 'mdjm_addon_name', $title, $addon_id );
} // mdjm_get_addon_name

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
		$months = mdjm_addon_get_months_available( $addon_id );

		if ( ! $months )	{
			$restricted = false;
		}
	}

	return apply_filters( 'mdjm_addon_is_restricted_by_date', $restricted, $addon_id );

} // mdjm_addon_is_restricted_by_date

/**
 * Retrieve the months the addon is available.
 *
 * @since	1.4
 * @param	int			$addon_id	ID of the addon.
 * @return	arr|false	Array of month numbers this addon is available, otherwise false.
 */
function mdjm_addon_get_months_available( $addon_id )	{
	$months = get_post_meta( $addon_id, '_addon_months', true );

	return apply_filters( 'mdjm_addon_months_available', $months, $addon_id );
} // mdjm_addon_get_months_available

/**
 * Retrieve the price of the addon.
 *
 * @since	1.4
 * @param	int		$addon_id	ID of the addon.
 * @return	str		The cost of the addon.
 */
function mdjm_addon_get_price( $addon_id )	{
	$price = get_post_meta( $addon_id, '_addon_price', true );

	return apply_filters( 'mdjm_addon_price', $price, $addon_id );
} // mdjm_addon_get_price

/**
 * Whether or not the addon has monthly prices.
 *
 * @since	1.4
 * @param	int		$addon_id	ID of the addon.
 * @return	bool	True if variable pricing is enabled.
 */
function mdjm_addon_has_variable_prices( $addon_id )	{
	$variable_pricing = get_post_meta( $addon_id, '_addon_variable_pricing', true );

	return apply_filters( 'mdjm_addon_has_variable_pricing', $variable_pricing, $addon_id );
} // mdjm_addon_has_variable_prices

/**
 * Retrieve an add-ons monthly prices.
 *
 * @since	1.4
 * @param	int		$addon_id	ID of the addon.
 * @return	bool	True if variable pricing is enabled.
 */
function mdjm_addon_get_variable_prices( $addon_id )	{
	$variable_pricing = get_post_meta( $addon_id, '_addon_variable_prices', true );

	return apply_filters( 'mdjm_addon_monthly_pricing', $variable_pricing, $addon_id );
} // mdjm_addon_get_variable_prices

/**
 * Retrieve an add-ons price range.
 *
 * @since	1.4
 * @param	int		$addon_id	ID of the addon.
 * @return	arr		Array of low and high prices.
 */
function mdjm_addon_get_price_range( $addon_id )	{

	if ( ! mdjm_addon_has_variable_prices( $addon_id ) )	{
		return;
	}

	$range = array();

	$prices = mdjm_addon_get_variable_prices( $addon_id );

	foreach ( $prices as $price )	{

		if ( empty( $range['low'] ) || $price['amount'] < $range['low'] )	{
			$range['low'] = $price['amount'];
		}

		if ( empty( $range['high'] ) || $price['amount'] > $range['high'] )	{
			$range['high'] = $price['amount'];
		}

	}

	return apply_filters( 'mdjm_addon_price_range', $range, $addon_id );

} // mdjm_addon_get_price_range

/**
 * Get all packages for the given employee.
 *
 * @since	1.4
 * @param	int			$employee_id	The employee ID whose addons we want.
 * @param	bool		$enabled		True returns only enabled addons, false returns all.
 * @return	obj			The WP Post objects for the employee's addons.		
 */
function mdjm_get_addons_by_employee( $employee_id = 0, $enabled = true )	{

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
		'post_type'      => 'mdjm-addon',
		'post_status'    => $enabled ? 'publish' : 'any',
		'meta_query'     => array( $meta_query )
	);

	$addons = get_posts( $args );

	return apply_filters( 'mdjm_get_addons_by_employee', $addons );

} // mdjm_get_addons_by_employee

/**
 * Retrieve an events addons.
 *
 * @since	1.4
 * @param	int			$event_id	The event ID.
 * @return	int|false	The event addons or false if no addons.
 */
function mdjm_get_event_addons( $event_id )	{
	$addon = get_post_meta( $event_id, '_mdjm_event_addons', true );

	return apply_filters( 'mdjm_event_addons', $addon );
} // mdjm_get_event_addons

/**
 * Retrieve a list of available addons.
 *
 * @param	arr		$args	Array of arguments. See @defaults
 * @return	arr		Array of WP_Post objects for available addons.
 */
function mdjm_get_available_addons( $args = array() )	{

	if ( ! mdjm_packages_enabled() )	{
		return __( 'No addons are available', 'mobile-dj-manager' );
	}

	$defaults = array(
		'employee' => 0,
		'event_id' => 0,
		'package'  => 0
	);

	$args = wp_parse_args( $args, $defaults );

	$addon_args = array();

	if ( ! empty( $args['employee'] ) )	{
		$addon_args['meta_query'] = array(
			'key'     => '_mdjm_employees',
			'value'   => array( $args['employee'] ),
			'compare' => 'IN'
		);
	}

	if ( ! empty( $args['event_id'] ) )	{
		$event_addons = mdjm_get_event_addons( $args['event_id'] );

		if ( $event_addons )	{
			$addon_args['post__not_in'] = $event_addons;
		}
	}

	if ( ! empty( $args['package'] ) )	{
		if ( 'mdjm-package' == get_post_type( $args['package_id'] ) )	{
			$package_items = mdjm_package_get_items( $args['package_id'] );

			if ( $package_items )	{
				if ( ! empty( $addon_args['post__not_in'] ) )	{
					$addon_args['post__not_in'] = array_merge( $addon_args['post__not_in'], $package_items );
				}
			}
		}
	}

	return mdjm_get_addons( $addon_args );

} // mdjm_get_available_addons

/**
 * List all available addons. If an employee ID is provided, list what that 
 * employee can provide only.
 *
 * @since	1.4
 * @param	int		$employee_id	An employee user ID, otherwise query current user.
 * @param	bool	$price			True to display the formatted package price
 * @return	str		HTML formatted string listing package information
 */
function mdjm_list_available_addons( $employee_id = 0, $price = false )	{

	if ( ! mdjm_packages_enabled() )	{
		return __( 'No addons available', 'mobile-dj-manager' );
	}
	
	if ( ! empty( $employee_id ) )	{
		$addons = mdjm_get_addons_by_employee( $employee_id, false );
	} else	{
		$addons = mdjm_get_addons();
	}
	
	if ( ! $addons )	{
		return __( 'No addons available', 'mobile-dj-manager' );
	}
	
	$output = '';
	$i = 0;
	
	foreach( $addons as $addon )	{
		if ( $i > 0 )	{
			$output .= '<br>';
		}
		
		$output .= get_the_title( $addon->ID );
		
		if ( $price )	{
			$output .= ' ' . mdjm_package_get_price( $addon->ID );
		}
		
		$i++;
	}
	
	return $output;

} // mdjm_list_available_addons

/**
 * Retrieve an addons excerpt.
 *
 * @since	1.4
 * @param	int		$addon_id	The ID of the addon.
 * @return	str
 */
function mdjm_get_addon_excerpt( $addon_id, $length = 0 )	{

	if ( empty( $length ) )	{
		$length = mdjm_get_option( 'package_excerpt_length', 50 );
	}

	if ( has_excerpt( $addon_id ) )	{
		$description = get_post_field( 'post_excerpt', $addon_id );
	} else	{
		$description = get_post_field( 'post_content', $addon_id );
	}

	if ( ! empty( $length ) )	{
		$description = wp_trim_words( $description, $length );
	}

	return apply_filters( 'mdjm_addon_excerpt', $description );

} // mdjm_get_addon_excerpt
