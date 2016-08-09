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
 * Retrieve all event types for which the package is available.
 *
 * @since	1.4
 * @param	int		$addon_id	ID of the package.
 * @return	arr		Array of event types the package is available.
 */
function mdjm_get_package_event_types( $package_id )	{
	$event_types = get_post_meta( $package_id, '_package_event_types', true );

	return apply_filters( 'mdjm_package_event_types', $event_types, $package_id );
} // mdjm_get_package_event_types

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
		$months = mdjm_get_package_months_available( $package_id );

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
function mdjm_get_package_months_available( $package_id )	{
	$months = get_post_meta( $package_id, '_package_months', true );

	return apply_filters( 'mdjm_package_months_available', $months, $package_id );
} // mdjm_get_package_months_available

/**
 * Retrieve the price of the package.
 *
 * @since	1.4
 * @param	int		$package_id	ID of the package.
 * @return	str		The cost of the package.
 */
function mdjm_get_package_price( $package_id )	{
	$price = get_post_meta( $package_id, '_package_price', true );

	return apply_filters( 'mdjm_package_price', $price, $package_id );
} // mdjm_get_package_price

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
function mdjm_get_package_variable_prices( $package_id )	{
	$variable_pricing = get_post_meta( $package_id, '_package_variable_prices', true );

	return apply_filters( 'mdjm_package_monthly_pricing', $variable_pricing, $package_id );
} // mdjm_get_package_variable_prices

/**
 * Retrieve a packages price range.
 *
 * @since	1.4
 * @param	int		$package_id	ID of the package.
 * @return	arr		Array of low and high prices.
 */
function mdjm_get_package_price_range( $package_id )	{

	if ( ! mdjm_package_has_variable_prices( $package_id ) )	{
		return;
	}

	$range = array();

	$prices = mdjm_get_package_variable_prices( $package_id );

	foreach ( $prices as $price )	{

		if ( empty( $range['low'] ) || $price['amount'] < $range['low'] )	{
			$range['low'] = $price['amount'];
		}

		if ( empty( $range['high'] ) || $price['amount'] > $range['high'] )	{
			$range['high'] = $price['amount'];
		}

	}

	return apply_filters( 'mdjm_package_price_range', $range, $package_id );

} // mdjm_get_package_price_range

/**
 * Retrieve the items in a package.
 *
 * @since	1.4
 * @param	int			$package_id		The package ID.
 * @return	arr|false	Array of addon ID's in this package, or false if none.		
 */
function mdjm_get_package_items( $package_id )	{
	$items = get_post_meta( $package_id, '_package_items', true );

	return apply_filters( 'mdjm_package_items', $items, $package_id );
} // mdjm_get_package_items

/**
 * Retrieve all packages with the given addon(s).
 *
 * @since	1.4
 * @param	int|arr		$addon_ids		ID(s) of addons to look for.
 * @return	mixed		Array of WP_Post objects or false.
 */
function mdjm_get_packages_with_addons( $addon_ids )	{

	if ( ! is_array( $addon_ids ) )	{
		$addon_ids = array( $addon_ids );
	}

	return mdjm_get_packages( array(
		'meta_query'  => array(
			'key'     => '_package_items',
			'value'   => $addons,
			'compare' => 'IN'
		)
	) );
	
} // mdjm_get_packages_with_addons

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
 * Whether or not an employee has the package.
 *
 * @since	1.4
 * @param	int		$package_id		The package ID.
 * @param	int		$employee_id	The employee ID to check.
 * @return	bool	True if the employee has the package, or false if not.
 */
function mdjm_employee_has_package( $package_id, $employee_id = 0 )	{

	$employees = mdjm_get_employees_with_package( $package_id );

	if ( empty( $employee_id ) )	{
		$employee_id = get_current_user_id();
	}

	if ( $employees )	{
		if ( in_array( 'all', $employees ) || in_array( $employee_id, $employees ) )	{
			return true;
		}
	}

	return false;

} // mdjm_employee_has_package

/**
 * Whether or not a package is available for the event type.
 *
 * @since	1.4
 * @param	int		$package_id			The package ID.
 * @param	int|str	$event_type_term	The event type term ID's to check.
 * @return	bool	True if the package is available for the event type, or false if not.
 */
function mdjm_package_is_available_for_event_type( $package_id, $event_type_term = '' )	{

	if ( empty( $event_type_term ) )	{
		return true;
	}

	$event_types = mdjm_get_package_event_types( $package_id );

	if ( $event_types )	{
		if ( in_array( 'all', $event_types ) || in_array( $event_type_term, $event_types ) )	{
			return true;
		}
	}

	return false;

} // mdjm_package_is_available_for_event_type

/**
 * Whether or not a package is available for the event date.
 *
 * @since	1.4
 * @param	int		$package_id		The package ID.
 * @param	str		$event_date		The event date.
 * @return	bool	True if the package is available for the event date, or false if not.
 */
function mdjm_package_is_available_for_event_date( $package_id, $event_date = '' )	{

	if ( empty( $event_date ) )	{
		return true;
	}

	if ( ! mdjm_package_is_restricted_by_date( $package_id ) )	{
		return true;
	}

	$event_months = mdjm_get_package_months_available( $package_id );
	$event_month  = date( 'n', strtotime( $event_date ) );

	if ( in_array( $event_month, $event_months ) )	{
		return true;
	}

	return false;

} // mdjm_package_is_available_for_event_date

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

/*
 * Retrieve the available packages.
 *
 * Availability can be dependant on an employee, month and event type.
 *
 * @param	arr			$args	Arguments for package retrieval. See @defaults.
 * @return	arr|false	Array of WP_Post objects.
 */
function mdjm_get_available_packages( $args )	{

	if( ! mdjm_packages_enabled() )	{
		return false;
	}

	$defaults = array(
		'employee'   => false,
		'date'       => false,
		'event_type' => false
	);

	$args         = wp_parse_args( $args, $defaults );
	$package_args = array();

	if ( ! empty( $args['employee'] ) )	{
		$package_args['meta_query'] = array(
			'key'     => '_mdjm_employees',
			'value'   => array( $args['employee'] ),
			'compare' => 'IN'
		);
	}

	$packages = mdjm_get_packages( $package_args );

	return apply_filters( 'mdjm_available_packages', $packages );

} // mdjm_get_available_packages

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
	
	$return = array();
	
	foreach( $packages as $package )	{
		$package_price = '';

		if( $price )	{
			$package_price = ' ' . mdjm_currency_filter( mdjm_format_amount( mdjm_get_package_price( $package->ID ) ) );
		}
	
		$return[] = get_the_title( $package->ID ) . '' . $package_price;
	}

	$return = apply_filters( 'mdjm_list_packages', $return, $price );

	return implode( '<br />', $return );

} // mdjm_list_available_packages

/**
 * Retrieve a packages excerpt.
 *
 * @since	1.4
 * @param	int		$package_id	The ID of the package.
 * @param	int		$length		The length of the excerpt.
 * @return	str
 */
function mdjm_get_package_excerpt( $package_id, $length = 0 )	{

	if ( empty( $length ) )	{
		$length = mdjm_get_option( 'package_excerpt_length', 55 );
	}

	if ( has_excerpt( $package_id ) )	{
		$description = get_post_field( 'post_excerpt', $package_id );
	} else	{
		$description = get_post_field( 'post_content', $package_id );
	}

	if ( ! empty( $length ) )	{
		$description = wp_trim_words( $description, $length );
	}

	return apply_filters( 'mdjm_package_excerpt', $description );

} // mdjm_get_package_excerpt

/**
 * Remove items from packages.
 *
 * @since	1.4
 * @param	int|arr		$addon_ids	ID (or array of IDs) of the addon(s) to remove.
 * @return	void
 */
function mdjm_remove_items_from_packages( $addon_ids )	{

	if ( ! is_array( $addon_ids ) )	{
		$addon_ids = array( $addon_ids );
	}

	$packages = mdjm_get_packages_with_addons( $addon_ids );

	if ( $packages )	{
		foreach ( $packages as $package )	{
			foreach ( $addon_ids as $addon_id )	{
				mdjm_remove_item_from_package( $package->ID, $addon_id );
			}
		}
	}

} // mdjm_remove_items_from_packages

/**
 * Removes an item from a package.
 *
 * @since	1.4
 * @param	int		$package_id		The package ID from which to remove the addon.
 * @param	int		$addon_id		The ID of the addon to remove
 */
function mdjm_remove_item_from_package( $package_id, $addon_id )	{

	$addons  = mdjm_get_package_items( $package_id );
	$items   = array();

	if ( $addons )	{
		foreach ( $addons as $addon )	{
			if ( $addon_id != $addon )	{
				$items[] = $addon;
			}
		}
		update_post_meta( $package_id, '_package_items', $items );
	}

} // mdjm_remove_item_from_package

/**
 * Renders HTML code for Package dropdown.
 *
 * @param	arr		$settings		See @$defaults
 * @param	bool	$structure		True returns the select input structure, false just return values
 * @return	HTML output for select field
 */
function mdjm_package_dropdown( $args = array(), $structure = true )	{

	global $current_user;
	
	$defaults = array(
		'name'            => '_mdjm_event_package',
		'id'              => '',
		'class'           => '',
		'selected'        => '',
		'first_entry'     => '',
		'first_entry_val' => '',
		'employee'        => ( is_user_logged_in() && ! current_user_can( 'client' ) ) ? $current_user->ID : '',
		'title'           => false,
		'cost'            => true,
		'required'        => false
	);

	// For backwards compatibility
	if ( isset( $args['dj'] ) )	{
		$args['employee'] = $args['dj'];
	}

	$args = wp_parse_args( $args, $defaults );
	
	$args['required'] = ! empty( $args['required'] ) ? ' required' : '';
	$args['id']       = ! empty( $args['id'] )       ? $args['id'] : $args['name'];
	
	$packages = mdjm_get_packages();
	
	$mdjm_select = '';
	
	if( $structure == true )	{
		$mdjm_select = sprintf( '<select name="%s" id="%s" class="%s"%s>', $args['name'], $args['id'], $args['class'], $args['required'] ) . "\n";
	}
	
	// First entry
	$mdjm_select .= ( ! empty( $args['first_entry'] ) ) ? '<option value="' . ( ! empty( $args['first_entry_val'] ) ? $args['first_entry_val'] : '' ) . '">' . $args['first_entry'] . '</option>' . "\r\n" : '';
		
	$packages = mdjm_get_packages();
	
	if( ! $packages )	{
		$mdjm_select .= '<option value="">' . __( 'No Packages Available', 'mobile-dj-manager' ) . '</option>' . "\r\n";
	} else	{

		foreach( $packages as $package )	{
			
			// If the specified employee does not have the package, do not show it
			if( ! empty( $args['employee'] ) && ! mdjm_employee_has_package( $package->ID, $args['employee'] ) )	{
				continue;
			}

			$package_desc = mdjm_get_package_excerpt( $package->ID );

			$mdjm_select .= '<option value="' . $package->ID . '"';
			$mdjm_select .= ( ! empty( $args['title'] ) && ! empty( $package_desc ) ) ? ' title="' . $package_desc . '"' : '';
			$mdjm_select .= ( ! empty( $args['selected'] ) ) ? selected( $args['selected'], $package->ID, false ) . '>' : '>' ;
			$mdjm_select .= $package->post_title;
			
			if( $args['cost'] == true )	{
				$mdjm_select .= ' - ' . mdjm_currency_filter( mdjm_format_amount(  mdjm_get_package_price( $package->ID ) ) ) ;
			}

			$mdjm_select .= '</option>' . "\r\n";

		}

	}
	
	if( $structure == true )	{
		$mdjm_select .= '</select>' . "\r\n";
	}
	
	return $mdjm_select;
		
} // mdjm_package_dropdown

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
 * Retrieve all event types for which the addon is available.
 *
 * @since	1.4
 * @param	int		$addon_id	ID of the addon.
 * @return	arr		Array of event types the addon is available.
 */
function mdjm_get_addon_event_types( $addon_id )	{
	$event_types = get_post_meta( $addon_id, '_addon_event_types', true );

	return apply_filters( 'mdjm_addon_event_types', $event_types, $addon_id );
} // mdjm_get_addon_event_types

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
		$months = mdjm_get_addon_months_available( $addon_id );

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
function mdjm_get_addon_months_available( $addon_id )	{
	$months = get_post_meta( $addon_id, '_addon_months', true );

	return apply_filters( 'mdjm_addon_months_available', $months, $addon_id );
} // mdjm_get_addon_months_available

/**
 * Retrieve the price of the addon.
 *
 * @since	1.4
 * @param	int		$addon_id	ID of the addon.
 * @return	str		The cost of the addon.
 */
function mdjm_get_addon_price( $addon_id )	{
	$price = get_post_meta( $addon_id, '_addon_price', true );

	return apply_filters( 'mdjm_addon_price', $price, $addon_id );
} // mdjm_get_addon_price

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
function mdjm_get_addon_variable_prices( $addon_id )	{
	$variable_pricing = get_post_meta( $addon_id, '_addon_variable_prices', true );

	return apply_filters( 'mdjm_addon_monthly_pricing', $variable_pricing, $addon_id );
} // mdjm_get_addon_variable_prices

/**
 * Retrieve an add-ons price range.
 *
 * @since	1.4
 * @param	int		$addon_id	ID of the addon.
 * @return	arr		Array of low and high prices.
 */
function mdjm_get_addon_price_range( $addon_id )	{

	if ( ! mdjm_addon_has_variable_prices( $addon_id ) )	{
		return;
	}

	$range = array();

	$prices = mdjm_get_addon_variable_prices( $addon_id );

	foreach ( $prices as $price )	{

		if ( empty( $range['low'] ) || $price['amount'] < $range['low'] )	{
			$range['low'] = $price['amount'];
		}

		if ( empty( $range['high'] ) || $price['amount'] > $range['high'] )	{
			$range['high'] = $price['amount'];
		}

	}

	return apply_filters( 'mdjm_addon_price_range', $range, $addon_id );

} // mdjm_get_addon_price_range

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
 * Whether or not an employee has the addon.
 *
 * @since	1.4
 * @param	int		$addon_id		The addon ID.
 * @param	int		$employee_id	The employee ID to check.
 * @return	bool	True if the employee has the addon, or false if not.
 */
function mdjm_employee_has_addon( $addon_id, $employee_id = 0 )	{

	$employees = mdjm_get_employees_with_addon( $addon_id );

	if ( empty( $employee_id ) )	{
		$employee_id = get_current_user_id();
	}

	if ( $employees )	{
		if ( in_array( 'all', $employees ) || in_array( $employee_id, $employees ) )	{
			return true;
		}
	}

	return false;

} // mdjm_employee_has_addon

/**
 * Whether or not an addon is available for the event type.
 *
 * @since	1.4
 * @param	int		$addon_id			The package ID.
 * @param	int|str	$event_type_terms	The event type term ID's to check.
 * @return	bool	True if the addon is available for the event type, or false if not.
 */
function mdjm_addon_is_available_for_event_type( $addon_id, $event_type_term = '' )	{

	if ( empty( $event_type_term ) )	{
		return true;
	}

	$event_types = mdjm_get_addon_event_types( $addon_id );

	if ( $event_types )	{
		if ( in_array( 'all', $event_types ) || in_array( $event_type_term, $event_types ) )	{
			return true;
		}
	}

	return false;

} // mdjm_addon_is_available_for_event_type

/**
 * Whether or not an addon is available for the event date.
 *
 * @since	1.4
 * @param	int		$addon_id		The addon ID.
 * @param	str		$event_date		The event date.
 * @return	bool	True if the addon is available for the event date, or false if not.
 */
function mdjm_addon_is_available_for_event_date( $addon_id, $event_date = '' )	{

	if ( empty( $event_date ) )	{
		return true;
	}

	if ( ! mdjm_addon_is_restricted_by_date( $addon_id ) )	{
		return true;
	}

	$event_months = mdjm_get_addon_months_available( $addon_id );
	$event_month  = date( 'n', strtotime( $event_date ) );

	if ( in_array( $event_month, $event_months ) )	{
		return true;
	}

	return false;

} // mdjm_addon_is_available_for_event_date

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
 * Lists an events addons.
 *
 * @since	1.4
 * @param	int			$event_id	The event ID.
 * @param	bool		$price		True to include the addon price.
 * @return	int|false	The event addons or false if no addons.
 */
function mdjm_list_event_addons( $event_id, $price = false )	{

	$output = __( 'No addons are assigned to this event', 'mobile-dj-manager' );

	if( ! mdjm_packages_enabled() )	{
		return $output;
	}

	$event_addons = mdjm_get_event_addons( $event_id );

	if ( $event_addons )	{
		$addons = array();

		foreach ( $event_addons as $event_addon )	{
			$addon_price = '';

			if ( $price )	{
				$addon_price = ' ' . mdjm_currency_filter( mdjm_format_amount( mdjm_get_package_price( $event_addon->ID ) ) );
			}

			$addons[] = $event_addon->post_title . '' . $addon_price;
		}

		$output = implode( '<br />', $addons );

	}

	return apply_filters( 'mdjm_list_event_addons', $output );

} // mdjm_list_event_addons

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
			$package_items = mdjm_get_package_items( $args['package_id'] );

			if ( $package_items )	{
				if ( ! empty( $addon_args['post__not_in'] ) )	{
					$addon_args['post__not_in'] = array_merge( $addon_args['post__not_in'], $package_items );
				}
			}
		}
	}

	$addons = mdjm_get_addons( $addon_args );

	return apply_filters( 'mdjm_available_addons', $addons ); 

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
	
	$return = array();
	
	foreach( $addons as $addon )	{
		$addon_price = '';

		if ( $price )	{
			$addon_price = ' ' . mdjm_currency_filter( mdjm_format_amount( mdjm_get_package_price( $addon->ID ) ) );
		}

		$return[] = get_the_title( $addon->ID ) . '' . $addon_price;	
	}

	$return = apply_filters( 'mdjm_list_available_addons', $return, $price );

	return implode( '<br />', $return );

} // mdjm_list_available_addons

/**
 * Retrieve an addons excerpt.
 *
 * @since	1.4
 * @param	int		$addon_id	The ID of the addon.
 * @param	int		$length		The length of the excerpt.
 * @return	str
 */
function mdjm_get_addon_excerpt( $addon_id, $length = 0 )	{

	if ( empty( $length ) )	{
		$length = mdjm_get_option( 'package_excerpt_length', 55 );
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

/**
 * Renders the HTML code for Addons multiple select dropdown.
 *
 * @param	arr		$args		Settings for the dropdown. See @$defaults.
 * @param	bool	$structure	True creates the select list, false just return values
 * @return	HTML output for select field
 */
function mdjm_addons_dropdown( $args = array(), $structure = true )	{
	global $current_user;
	
	$defaults = array(
		'name'            => 'event_addons',
		'id'              => '',
		'class'           => '',
 		'selected'        => '',
 		'first_entry'     => '',
 		'first_entry_val' => '',
		'employee'        => is_user_logged_in() && ! current_user_can( 'client' ) ? $current_user->ID : '',
 		'package'         => '',
 		'title'           => '',
 		'cost'            => true
	);

	// For backwards compatibility
	if ( isset( $args['dj'] ) )	{
		$args['employee'] = $args['dj'];
	}

	$args = wp_parse_args( $args, $defaults );

	if ( empty ( $args['id'] ) )	{
		$args['id'] = $args['name'];
	}

	$mdjm_select = '';

	if( $structure == true )	{
		$mdjm_select .= '<select name="' . $args['name'] . '[]" id="' . $args['id'] . '"';
		$mdjm_select .= ! empty( $args['class'] ) ? ' class="' . $args['class'] . '"' : '';
		$mdjm_select .= ' multiple="multiple">' . "\r\n";
	}

	// First entry
	$mdjm_select .= ( ! empty( $args['first_entry'] ) ) ? '<option value="' . ! empty( $args['first_entry_val'] ) ? $args['first_entry_val'] : '0' . '">' . $args['first_entry'] . '</option>' . "\r\n" : '';
	
	$items      = mdjm_get_addons();
	$categories = get_terms( 'addon-category', array( 'hide_empty' => true ) );

	if( ! $items )	{
		$mdjm_select .= '<option value="0" disabled="disabled">' . __( 'No Addons Available', 'mobile-dj-manager' ) . '</option>' . "\r\n";
	} else	{
		foreach( $categories as $category )	{
			if( ! empty( $header ) )	{
				$mdjm_select .= '</optgroup>' . "\r\n";
			}
			
			$header = false;
			
			// Create an array of options grouped by category
			foreach( $items as $item )	{					
				// If the addon is part of an assigned package, exclude it
				if( ! empty( $args['package'] ) )	{

					if ( ! is_numeric( $args['package'] ) )	{
						$package    = mdjm_get_package_by( 'slug', $args['package'] );
						$package_id = $package->ID;
					} else	{
						$package_id = $args['package'];
					}

					$package_items = mdjm_get_package_items( $package_id );

					if ( $package_items && in_array( $item->ID, $package_items ) )	{
						continue;
					}

				}
				
				// If the specified Employee does not have the addon, do not show it	
				if( ! empty( $args['employee'] ) && ! mdjm_employee_has_addon( $item->ID, $args['employee'] ) )	{
					continue;
				}
				
				if( has_term( $category->name, 'addon-category', $item->ID ) )	{

					if( empty( $header ) )	{
						$mdjm_select .= '<optgroup label="' . $category->name . '">' . "\r\n";
						$header = true;
					}

					$mdjm_select .= '<option value="' . $item->ID . '"';

					$item_desc = mdjm_get_addon_excerpt( $item->ID );

					$mdjm_select .= ( ! empty( $args['title'] ) && ! empty( $item_desc ) ) ? ' title="' . $item_desc . '"' : '';

					if( ! empty( $args['selected'] ) && in_array( $item->ID, $args['selected'] ) )	{
						$mdjm_select .= ' selected="selected"';
					}

					$mdjm_select .= '>';
					$mdjm_select .= $item->post_title;

					if ( ! empty( $args['cost'] ) )	{
						$mdjm_select .= ' - ' . mdjm_currency_filter( mdjm_format_amount( mdjm_get_addon_price( $item->ID ) ) );
					}

					$mdjm_select .= '</option>' . "\r\n";

				}
				
			}
		}
	}
	
	if( $structure == true )	{
		$mdjm_select .= '</select>' . "\r\n";
	}
	
	return $mdjm_select;
		
} // mdjm_addons_dropdown

/**
 * Set the addon category.
 *
 * @since	1.4
 * @param	int		$addon_id	The addon ID.
 * @param	int		$term_id	The category term ID.
 * @return	bool	True on success, otherwise false.
 */
function mdjm_set_addon_category( $addon_id, $term_id )	{
	$set_entry_type = wp_set_post_terms( $addon_id, $term_id, 'addon-category' );
	
	if ( is_wp_error( $set_entry_type ) )	{
		return false;
	}

	return true;
} // mdjm_set_addon_category


