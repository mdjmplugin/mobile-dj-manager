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
 * @param
 * @return	bool	True if packages are enabled, false if they are not
 */
function mdjm_packages_enabled()	{
	return mdjm_get_option( 'enable_packages', false );
} // mdjm_packages_enabled

/**
 * Retrieve all packages
 *
 *
 *
 *
 */
function mdjm_get_packages()	{
	return get_option( 'mdjm_packages' );
} // mdjm_get_packages

/**
 * Retrieve the package from the given slug
 *
 * @param	str			$slug		The slug to search for
 *
 * @return	arr|bool	$packages	The package details
 */
function mdjm_get_package_by_slug( $slug )	{
	$packages = mdjm_get_packages();
	
	$package = !empty( $packages ) && !empty( $packages[$slug] ) ? $packages[$slug] : false;
		
	return $package;
} // mdjm_get_package_by_slug

/**
 * Retrieve the package by name
 *
 * @param	str			$name		The name to search for
 *
 * @return	arr|bool	$packages	The package details
 */
function mdjm_get_package_by_name( $name )	{
	$packages = mdjm_get_packages();
	
	if( empty( $packages ) )
		return false;
		
	foreach( $packages as $pack )	{
		// Set everything to uppercase so we're not relying on case sensitive user entry
		if( strtoupper( $pack['name'] ) == strtoupper( $name ) )
			$package = $pack;
	}
		
	return !empty( $package ) ? $package : false;
} // mdjm_get_package_by_name
 
/*
 * Get the package information for the given event
 *
 * @param	int			$event_id	The event ID
 * @return
 */
function get_event_package( $event_id, $price=false )	{
	
	if( ! mdjm_packages_enabled() )	{
		return 'N/A';
	}
	
	// Event package
	$event_package = get_post_meta( $event_id, '_mdjm_event_package', true );
	
	if( empty( $event_package ) )
		return 'No package is assigned to this event';
	
	// All packages
	$packages = mdjm_get_packages();
					
	
	return stripslashes( esc_attr( $packages[$event_package]['name'] ) ) . ' ' . ! empty( $price ) ? mdjm_currency_filter( mdjm_format_amount( $packages[$event_package]['cost'] ) ) : '';
			
} // get_event_package

/*
 * Get the package information
 *
 * @param	int			$dj			Optional: The user ID of the DJ
 * @return
 */
function get_available_packages( $dj='', $price=false )	{
	
	if( ! mdjm_packages_enabled() )	{
		return 'N/A';
	}
	
	// All packages
	$packages = mdjm_get_packages();
	
	if( !empty( $packages ) )	{
		foreach( $packages as $package )	{
			if( !isset( $package['enabled'] ) || $package['enabled'] != 'Y' )
				continue;
			
			if( !empty( $dj ) )	{
				if( in_array( $dj, explode( ',', $package['djs'] ) ) )
					$available[] = stripslashes( esc_attr( $package['name'] ) ) . ' ' . ! empty( $price ) ? mdjm_currency_filter( mdjm_format_amount( $package['cost'] ) ) : '';
			}
			else
				$available[] = stripslashes( esc_attr( $package['name'] ) ) . ' ' . ! empty( $price ) ? mdjm_currency_filter( mdjm_format_amount( $package['cost'] ) ) : '';
		}
		$i = 1;
		$the_packages = '';
		if( !empty( $available ) )	{
			foreach( $available as $avail )	{
				$the_packages .= $avail . ( $i < count( $available ) ? '<br />' : '' );
				$i++;
			}
		}
	}
	return ( !empty( $the_packages ) ? $the_packages : __( 'No packages available', 'mobile-dj-manager' ) );
			
} // get_available_packages

/*
 * Get the addon information for the given event
 *
 * @param	int			$event_id	The event ID
 * @return	arr|bool	$addons		array with package details, or false if no package assigned
 */
function get_event_addons( $event_id, $price=false )	{
	
	if( ! mdjm_packages_enabled() )	{
		return 'N/A';
	}
	
	// Event Addons
	$event_addons = get_post_meta( $event_id, '_mdjm_event_addons', true );
			
	if( empty( $event_addons ) )
		return __( 'No addons are assigned to this event', 'mobile-dj-manager' );
		
	// All addons
	$all_addons = mdjm_get_addons();
	
	$addons = '';
	$i = 1;
	
	foreach( $event_addons as $event_addon )	{
		$addons .= stripslashes( esc_attr( $all_addons[$event_addon][0] ) ) . ' ' . ! empty( $price ) ? 
			mdjm_currency_filter( mdjm_format_amount( $all_addons[$event_addon][7] ) ) : '' . $i < count( $event_addons ) ? 
			'<br />' : '';
		$i++;
	}
									
	return $addons;
			
} // get_event_addons

/*
 * Get the addons available
 *
 *
 * @param	int			$dj			Optional: The user ID of the DJ
 *			str			$package	Optional: The slug of a package where the package contents need to be excluded
 * @return
 */
function get_available_addons( $dj='', $package='' )	{
	
	if( ! mdjm_packages_enabled() )	{
		return 'N/A';
	}
								
	// All addons
	$all_addons = mdjm_get_addons();
	
	if( empty( $all_addons ) )
		return __( 'No addons are available', 'mobile-dj-manager' );
	
	$addons = array();
	
	foreach( $all_addons as $all_addon )	{
		// If the addon is not enabled, do not display
		if( !isset( $all_addon[6] ) || $all_addon[6] != 'Y' )
			continue;
		
		// If a package is parsed, remove the package items from the available addons
		if( !empty( $package ) )	{
			$packages = mdjm_get_packages();
			$current_items = explode( ',', $packages[$package]['equipment'] );
			
			if( !empty( $current_items ) && in_array( $all_addon[1], $current_items ) )
				continue;
		}
		
		// If a DJ is parsed, only show their available addons
		if( !empty( $dj ) && !in_array( $dj, explode( ',', $all_addon[8] ) ) )
			continue;

		$addons[$all_addon[1]]['cat'] = '';
		$addons[$all_addon[1]]['slug'] = $all_addon[1];
		$addons[$all_addon[1]]['name'] = stripslashes( esc_attr( $all_addon[0] ) );
		$addons[$all_addon[1]]['cost'] = $all_addon[7];
		$addons[$all_addon[1]]['desc'] = stripslashes( esc_textarea( $all_addon[4] ) );
	}
									
	return $addons;
			
} // get_available_addons

/*
 * Retrieve the package name
 *
 * @param	str		$slug		Slug name of the package
 * @return	str		$package	The display name of the package	
 *
 */
function get_package_name( $slug )	{
	if( empty( $slug ) )
		return false;
	
	$packages = mdjm_get_packages();
	
	if( empty( $packages[$slug] ) || empty( $packages[$slug]['name'] ) )
		return false;
	
	$package = stripslashes( esc_attr( $packages[$slug]['name'] ) );
	
	return $package;
	
} // get_package_name

/*
 * Retrieve the package name, description, cost
 *
 * @param	str		$slug		Slug name of the package
 *		
 *
 */
function get_package_details( $slug )	{
	if( empty( $slug ) )
		return false;
	
	$packages = mdjm_get_packages();
	
	if( empty( $packages[$slug] ) )
		return false;
	
	$package['slug'] = $slug;
	$package['name'] = stripslashes( esc_attr( $packages[$slug]['name'] ) );
	$package['desc'] = stripslashes( esc_textarea( $packages[$slug]['desc'] ) );
	$package['equipment'] = $packages[$slug]['equipment'];
	$package['cost'] = $packages[$slug]['cost'];
	
	return $package;
	
} // get_package_details

/*
 * Output HTML code for Package dropdown
 *
 * @param	arr		$settings		Settings for the dropdown
 *									'name'				Optional: The name of the input. Defaults to '_mdjm_event_package'
 *									'id'				Optional: ID for the field (uses name if not present)
 *									'class'				Optional: Class of the input field
 *									'selected'			Optional: Initially selected option
 *									'first_entry'		Optional: First entry to be displayed (default none)
 *									'first_entry_val'	Optional: First entry value
 *									'dj'				Optional: The ID of the DJ to present package for (default current user)
 *									'title'				Optional: Add package description to the title element of each option
 *									'cost'				Optional: Display the price of the package (default true)
 *					$structure		bool				true create the select list, false just return values
 * @ return	HTML output for select field
 */
function mdjm_package_dropdown( $settings='', $structure=true )	{
	global $current_user;
	
	$packages = mdjm_get_packages();
	// Set the values based on the array passed
	$select_name = isset( $settings['name'] ) ? $settings['name'] : '_mdjm_event_package';
	$select_id = isset( $settings['id'] ) ? $settings['id'] : $select_name;
	$select_dj = ( !empty( $settings['dj'] ) ? $settings['dj'] : ( is_user_logged_in() && !current_user_can( 'client' ) ? $current_user->ID : '' ) );
	$select_cost = ( isset( $settings['cost'] ) ? $settings['cost'] : true );
	
	$mdjm_select = '';
	
	if( $structure == true )	{
		$mdjm_select = '<select name="' . $select_name . '" id="' . $select_id . '"';
		$mdjm_select .= isset( $settings['class'] ) ? ' class="' . $settings['class'] . '"' : '';
		$mdjm_select .= '>' . "\r\n";
	}
	
	// First entry
	$mdjm_select .= isset( $settings['first_entry'] ) && !empty( $settings['first_entry'] ) ? 
		'<option value="' . ( isset( $settings['first_entry_val'] ) ? $settings['first_entry_val'] : '' ) . '">' . 
		$settings['first_entry'] . '</option>' . "\r\n" : '';
		
	$packages = mdjm_get_packages();
	
	if( empty( $packages ) )
		$mdjm_select .= '<option value="0">' . __( 'No Packages Available', 'mobile-dj-manager' ) . '</option>' . "\r\n";
	
	else	{
	// All packages
		foreach( $packages as $package )	{
			// If the package is not enabled, do not show it
			if( empty( $package['enabled'] ) || $package['enabled'] != 'Y' )
				continue;
			
			// If the specified DJ does not have the package, do not show it
			if( !empty( $select_dj ) )	{	
				$djs_have = explode( ',', $package['djs'] );
				
				if( !in_array( $select_dj, $djs_have ) )
					continue;
			}
			
			$mdjm_select .= '<option value="' . $package['slug'] . '"';
			$mdjm_select .= ( !empty( $settings['title'] ) && !empty( $package['desc'] ) ? ' title="' . stripslashes( esc_textarea( $package['desc'] ) ) . '"' : '' );
			$mdjm_select .= ( isset( $settings['selected'] ) ? selected( $settings['selected'], $package['slug'], false ) . '>' : '>' ) ;
			$mdjm_select .= stripslashes( esc_attr( $package['name'] ) ) . 
				$select_cost == true ? ' - ' . mdjm_currency_filter( mdjm_format_amount( $package['cost'] ) ) : '' . '</option>' . "\r\n";
		}
	}
	
	if( $structure == true )
		$mdjm_select .= '</select>' . "\r\n";
	
	return $mdjm_select;
		
} // mdjm_package_dropdown

/**
 * Retrieve all addons
 *
 * @param
 *
 * @return	arr		$addons		Array of all addons
 */
function mdjm_get_addons()	{
	return get_option( 'mdjm_equipment' );
} // mdjm_get_addons

/**
 * Retrieve all addons by dj
 *
 * @param	int|arr	$user_id	Required: User ID of DJ, or array of DJ User ID's
 *
 * @return	arr		$addons		Array of all addons
 */
function mdjm_addons_by_dj( $user_id )	{
	// We work with an array
	if( !is_array( $user_id ) )
		$users = array( $user_id );
		
	$equipment = mdjm_get_addons();
	
	// No addons, return false
	if( empty( $equipment ) )
		return false;
		
	asort( $equipment );
	
	// Loop through the addons and filter for the given user(s)
	foreach( $equipment as $addon )	{
		$users_have = explode( ',', $addon[8] );
		
		foreach( $users as $user )	{			
			if( !in_array( $user, $users_have ) )
				continue 2; // Continue from the foreach( $equipment as $addon ) loop
		}
			
		$addons[] = $addon;
	}
	// Return the results, or false if none
	return !empty( $addons ) ? $addons : false;
} // mdjm_addons_by_dj

/**
 * Retrieve all addons within the given category
 *
 * @param	str		$cat		Required: Slug of the category for which to search
 *
 * @return	arr		$addons		Array of all addons
 */
function mdjm_addons_by_cat( $cat )	{
	$equipment = mdjm_get_addons();
	
	// No addons, return false
	if( empty( $equipment ) )
		return false;
		
	asort( $equipment );
	
	// Loop through the addons and filter for the given category
	foreach( $equipment as $addon )	{
		if( $addon[5] != $cat )
			continue;
		
		$addons[] = $addon;	
	}
	// Return the results, or false if none
	return !empty( $addons ) ? $addons : false;
} // mdjm_addons_by_cat

/**
 * Retrieve all addons within the given package slug
 *
 * @param	str		$slug		Required: Slug of the package for which to search
 *
 * @return	arr		$addons		Array of all addons
 */
function mdjm_addons_by_package_slug( $slug )	{
	$package = mdjm_get_package_by_slug( strtolower( $slug ) );
	
	// No package or the package has no addons, return false
	if( empty( $package ) || empty( $package['equipment'] ) )
		return false;
	
	$package_items = explode( ',', $package['equipment'] );
	$equipment = mdjm_get_addons();
	
	// No addons, return false
	if( empty( $equipment ) )
		return false;
	
	foreach( $equipment as $addon )	{
		if( !in_array( $addon[1], $package_items ) )
			continue;
			
		$addons[] = $addon;	
	}
	
	// Return the results, or false if none
	return !empty( $addons ) ? $addons : false;
} // mdjm_addons_by_package_slug

/**
 * Retrieve all addons within the given package
 *
 * @param	str		$name		Required: Name of the package for which to search
 *
 * @return	arr		$addons		Array of all addons
 */
function mdjm_addons_by_package_name( $name )	{
	$package = mdjm_get_package_by_name( $name );
	
	// No package or the package has no addons, return false
	if( empty( $package ) || empty( $package['equipment'] ) )
		return false;
	
	$package_items = explode( ',', $package['equipment'] );
	$equipment = mdjm_get_addons();
	
	// No addons, return false
	if( empty( $equipment ) )
		return false;
	
	foreach( $equipment as $addon )	{
		if( !in_array( $addon[1], $package_items ) )
			continue;
			
		$addons[] = $addon;	
	}
	
	// Return the results, or false if none
	return !empty( $addons ) ? $addons : false;
} // mdjm_addons_by_package_name

/*
 * Retrieve the addon name
 *
 * @param	str		$slug	The slug name of the addon
 * @return	str		$addon	The display name of the addon
 */
function get_addon_name( $slug )	{
	if( empty( $slug ) )
		return false;
			
	$equipment = mdjm_get_addons();
	
	if( empty( $equipment[$slug] ) || empty( $equipment[$slug][0] ) )
		return false;
		
	$addon = stripslashes( esc_attr( $equipment[$slug][0] ) );
	
	return $addon;
	
} // get_addon_name

/*
 * Retrieve the addon category, name, decription & cost
 *
 *
 *
 */
function get_addon_details( $slug )	{
	if( empty( $slug ) )
		return false;
		
	$cats = get_option( 'mdjm_cats' );
	
	$equipment = mdjm_get_addons();
	
	if( empty( $equipment[$slug] ) )
		return false;
		
	$addon['slug'] = $slug;
	$addon['cat'] = stripslashes( esc_attr( $cats[$equipment[$slug][5]] ) );
	$addon['name'] = stripslashes( esc_attr( $equipment[$slug][0] ) );
	$addon['desc'] = stripslashes( esc_textarea( $equipment[$slug][4] ) );
	$addon['cost'] = $equipment[$slug][7];
	
	return $addon;
	
} // get_addon_details

/*
 * Output HTML code for Addons multiple select dropdown
 *
 * @param	arr		$settings		Settings for the dropdown
 *									'name'				Optional: The name of the input. Defaults to 'event_addons'
 *									'id'				Optional: ID for the field (uses name if not present)
 *									'class'				Optional: Class of the input field
 *									'selected'			Optional: ARRAY of initially selected option
 *									'first_entry'		Optional: First entry to be displayed (default none)
 *									'first_entry_val'	Optional: First entry value
 *									'dj'				Optional: The ID of the DJ to present package for (default current user)
 *									'package'			Optional: Package slug for which to exclude addons if they exist in that package
 *									'title'				Optional: Add addon description to the title element of each option
 *									'cost'				Optional: Display the price of the package (default true)
 *					$structure		bool				true create the select list, false just return values
 * @ return	HTML output for select field
 */
function mdjm_addons_dropdown( $settings='', $structure=true )	{
	global $current_user;
	
	// Set the values based on the array passed
	$select_name = isset( $settings['name'] ) ? $settings['name'] : 'event_addons';
	$select_id = isset( $settings['id'] ) ? $settings['id'] : $select_name;
	$select_dj = ( !empty( $settings['dj'] ) ? $settings['dj'] : ( is_user_logged_in() && !current_user_can( 'client' ) ? $current_user->ID : '' ) );
	$select_cost = isset( $settings['cost'] ) ? $settings['cost'] : true;
	
	$mdjm_select = '';
	
	if( $structure == true )	{
		$mdjm_select .= '<select name="' . $select_name . '[]" id="' . $select_id . '"';
		$mdjm_select .= isset( $settings['class'] ) ? ' class="' . $settings['class'] . '"' : '';
		$mdjm_select .= ' multiple="multiple">' . "\r\n";
	}
	
	// First entry
	$mdjm_select .= isset( $settings['first_entry'] ) ? 
		'<option value="' . isset( $settings['first_entry_val'] ) ? $settings['first_entry_val'] : '0' . '">' . 
		$settings['first_entry'] . '</option>' . "\r\n" : '';
	
	$equipment = mdjm_get_addons();
	
	if( empty( $equipment ) )
		$mdjm_select .= '<option value="0">' . __( 'No Addons Available', 'mobile-dj-manager' ) . '</option>' . "\r\n";
	
	else	{
		asort( $equipment );
	// All addons
		$cats = get_option( 'mdjm_cats' );
		if( !empty( $cats ) )
			asort( $cats );
		
		foreach( $cats as $cat_key => $cat_value )	{
			if( !empty( $header ) )
				$mdjm_select .= '</optgroup>' . "\r\n";
			
			$header = false;
			
			// Create an array of options grouped by category
			foreach( $equipment as $item )	{
				// If the addon is not enabled, do not show it
				if( empty( $item[6] ) || $item[6] != 'Y' )
					continue;
					
				// If the addon is part of an assigned package, exclude it
				if( !empty( $settings['package'] ) )	{
					$packages = mdjm_get_packages();
					$package_items = explode( ',', $packages[$settings['package']]['equipment'] );
					
					if( !empty( $package_items ) && in_array( $item[1], $package_items ) )
						continue;	
				}
				
				// If the specified DJ does not have the addon, do not show it	
				if( !empty( $select_dj ) )	{
					$djs_have = explode( ',', $item[8] );
					
					if( !in_array( $select_dj, $djs_have ) )
						continue;
				}
				
				if( $item[5] == $cat_key )	{
					if( empty( $header ) )	{
						$mdjm_select .= '<optgroup label="' . $cat_value . '">' . "\r\n";
						$header = true;
					}
						
						$mdjm_select .= '<option value="' . $item[1] . '"';
						$mdjm_select .= ( !empty( $settings['title'] ) && !empty( $item[4] ) ? ' title="' . stripslashes( esc_textarea( $item[4] ) ) . '"' : '' );
						
						if( !empty( $settings['selected'] ) && in_array( $item[1], $settings['selected'] ) )
							$mdjm_select .= ' selected="selected"';
						
						$mdjm_select .= '>' . stripslashes( esc_attr( $item[0] ) ) . 
							$select_cost == true ? ' - ' . mdjm_currency_filter( mdjm_format_amount( $item[7] ) ) : '' . '</option>' . "\r\n";
				}
				
			}
		}
	}
	
	if( $structure == true )
		$mdjm_select .= '</select>' . "\r\n";
	
	return $mdjm_select;
		
} // mdjm_addons_dropdown

/*
 * Output HTML code for Addons checkbox list
 *
 * @param	arr		$settings		Settings for the dropdown
 *									'name'				Optional: The name of the input. Defaults to 'event_addons'
 *									'class'				Optional: Class of the input field
 *									'checked'			Optional: ARRAY of initially checked options
 *									'dj'				Optional: The ID of the DJ to present package for (default current user)
 *									'package'			Optional: Package slug for which to exclude addons if they exist in that package
 *									'title'				Optional: Add addon description to the title element of each option
 *									'cost'				Optional: Display the price of the package (default true)
 * @ return	HTML output for select field
 */
function mdjm_addons_checkboxes( $settings='' )	{
	global $current_user;
	
	// Set the values based on the array passed
	$check_name = isset( $settings['name'] ) ? $settings['name'] : 'event_addons';
	$check_id = isset( $settings['id'] ) ? $settings['id'] : $check_name;
	$check_dj = ( !empty( $settings['dj'] ) ? $settings['dj'] : ( is_user_logged_in() ? $current_user->ID : '' ) );
	$check_cost = isset( $settings['cost'] ) ? $settings['cost'] : false;
	
	$mdjm_check = '';
	
	$equipment = mdjm_get_addons();
	
	if( empty( $equipment ) )
		$mdjm_check .= __( 'No Addons Available', 'mobile-dj-manager' ) . "\r\n";
		
	else	{
		asort( $equipment );
	// All addons
		$cats = get_option( 'mdjm_cats' );
		if( !empty( $cats ) )
			asort( $cats );
		
		foreach( $cats as $cat_key => $cat_value )	{				
			$header = false;
			
			// Create an array of options grouped by category
			foreach( $equipment as $item )	{
				// If the addon is not enabled, do not show it
				if( empty( $item[6] ) || $item[6] != 'Y' )
					continue;
					
				// If the addon is part of an assigned package, exlude it
				if( !empty( $settings['package'] ) )	{
					$packages = mdjm_get_packages();
					$package_items = explode( ',', $packages[$settings['package']]['equipment'] );
					
					if( !empty( $package_items ) && in_array( $item[1], $package_items ) )
						continue;	
				}
				
				// If the specified DJ does not have the addon, do not show it	
				if( !empty( $select_dj ) )	{
					$djs_have = explode( ',', $item[8] );
					
					if( !in_array( $select_dj, $djs_have ) )
						continue;
				}
				
				if( $item[5] == $cat_key )	{
					if( empty( $header ) )	{
						$mdjm_check .= '<span class="font-weight: bold;">' . stripslashes( $cat_value ) . '</span><br />' . "\r\n";
						$header = true;
					}
						
						$mdjm_check .= '<input type="checkbox" name="' . $check_name . '[]" ';
						$mdjm_check .= 'id="' . $check_name . '_' . stripslashes( esc_attr( $item[1] ) ) . '"';
						$mdjm_check .= ( !empty( $settings['class'] ) ? 
								' class="' . $settings['class'] . '"' : '' );
								
						$mdjm_check .= ' value="' . stripslashes( esc_attr( $item[1] ) ) . '"';
						
						if( !empty( $settings['checked'] ) && in_array( $item[1], $settings['checked'] ) )
							$mdjm_check .= ' checked="checked"';
						
						$mdjm_check .= ' />&nbsp;' . "\r\n";
						
						$mdjm_check .= ( !empty( $settings['title'] ) && !empty( $item[4] ) ? 
							'<span title="' . stripslashes( $item[4] ) . '">' : '' );
						
						$mdjm_check .= '<label for="' . $check_name . '_' . stripslashes( esc_attr( $item[1] ) ) . '">' . stripslashes( $item[0] );
						
						$mdjm_check .= $check_cost == true ? ' - ' . mdjm_currency_filter( mdjm_format_amount( $item[7] ) ) : '';
						
						$mdjm_check .= '</label>' . ( !empty( $settings['title'] ) && !empty( $item[4] ) ? '</span>' : '' ) . '<br />' .  "\r\n";
				}
				
			}
		}
	}
	
	return $mdjm_check;
	
} // mdjm_addons_checkboxes