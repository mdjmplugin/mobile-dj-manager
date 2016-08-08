<?php

/**
 * Run the update procedures.
 *
 * @version	1.4
 * @return	void
 */
function mdjm_run_update_14()	{
	$items = mdjm_import_addons_as_posts_14();

	$packages = mdjm_import_packages_as_posts_14( $items );

	if ( ! empty( $items ) || ! empty( $packages ) )	{
		mdjm_convert_event_packages_to_post_ids_14( $packages, $items );
	}

}
add_action( 'init', 'mdjm_run_update_14', 15 );

/**
 * Import addons from the options table as posts.
 *
 * Loop through all entries in the options table, create posts for them and assign the terms.
 *
 * @since	1.4
 * @return	void
 */
function mdjm_import_addons_as_posts_14()	{

	if ( get_option( 'mdjm_upgrade_14_import_addons' ) )	{
		return;
	}

	$items          = array();
	$existing_items = get_option( 'mdjm_equipment' );
	$existing_cats  = get_option( 'mdjm_cats' );

	if ( ! $existing_items )	{
		return false;
	}

	foreach( $existing_items as $slug => $existing_item )	{

		$employees = array( 'all' );

		if ( ! empty( $existing_item[8] ) )	{
			$employees = explode( ',', $existing_item[8] );
		}

		if( ! term_exists( $existing_cats[ $existing_item[5] ], 'addon-category' ) )	{
			wp_insert_term( $existing_cats[ $existing_item[5] ], 'addon-category', array( 'slug' => $existing_item[5] ) );
		}

		$category = get_term_by( 'name', $existing_cats[ $existing_item[5] ], 'addon-category' );

		$args = array(
			'post_type'     => 'mdjm-addon',
			'post_content'  => ! empty( $existing_item[4] ) ? stripslashes( $existing_item[4] ) : '',
			'post_title'    => $existing_item[0],
			'post_status'   => 'publish',
			'post_name'     => stripslashes( $slug ),
			'post_category' => ! empty( $category ) ? array( $category->term_id ) : '',
			'meta_input'    => array(
				'_addon_employees'        => $employees,
				'_addon_event_types'      => array( 'all' ),
				'_addon_restrict_date'    => false,
				'_addon_months'           => array(),
				'_addon_price'            => ! empty( $existing_item[7] ) ? mdjm_format_amount( $existing_item[7] ) : mdjm_format_amount( '0' ),
				'_addon_variable_pricing' => false,
				'_addon_variable_prices'  => false
			)
		);

		$addon_id = wp_insert_post( $args );

		if ( $addon_id )	{
			if( ! empty( $category ) )	{
				mdjm_set_addon_category( $addon_id, $category->term_id );
			}

			$items[ $slug ] = $addon_id;
		}

	}

	update_option( 'mdjm_upgrade_14_import_addons', true );

	return $items;

} // mdjm_import_addons_as_posts_14

/**
 * Import packages as posts.
 *
 * Create a new post for each existing package.
 *
 * @since	1.4
 * @param	arr		$items	Mapped array of item slugs to new post ID.
 * @return	void
 */
function mdjm_import_packages_as_posts_14( $items )	{

	if ( get_option( 'mdjm_upgrade_14_import_packages' ) )	{
		return;
	}

	$existing_packages = get_option( 'mdjm_packages' );

	if ( ! $existing_packages )	{
		return false;
	}

	$packages  = array();

	foreach ( $existing_packages as $slug => $existing_package )	{

		$addons = array();

		$employees = array( 'all' );

		if ( ! empty( $existing_package['djs'] ) )	{
			$employees = explode( ',', $existing_package['djs'] );
		}

		if ( ! empty( $existing_package['equipment'] ) )	{
			$equipment = explode( ',', $existing_package['equipment'] );
		}

		if ( ! empty( $equipment ) )	{
			foreach( $equipment as $item )	{
				if ( ! empty( $items[ $item ] ) )	{
					$addons[] = $items[ $item ];
				}
			}
		} else	{
			$addons = array();
		}

		$args = array(
			'post_type'     => 'mdjm-package',
			'post_content'  => ! empty( $existing_package['desc'] ) ? stripslashes( $existing_package['desc'] ) : '',
			'post_title'    => ! empty( $existing_package['name'] ) ? stripslashes( $existing_package['name'] ) : '',
			'post_status'   => 'publish',
			'post_name'     => stripslashes( $slug ),
			'meta_input'    => array(
				'_package_employees'        => $employees,
				'_package_event_types'      => array( 'all' ),
				'_package_restrict_date'    => false,
				'_package_months'           => array(),
				'_package_items'            => array_unique( $addons ),
				'_package_price'            => ! empty( $existing_package['cost'] ) ? mdjm_format_amount( $existing_package['cost'] ) : mdjm_format_amount( '0' ),
				'_package_variable_pricing' => false,
				'_package_variable_prices'  => false
			)
		);

		$package_id = wp_insert_post( $args );

		if ( $package_id )	{
			$packages[ $slug ] = $package_id;
		}

	}

	update_option( 'mdjm_upgrade_14_import_packages', true );

	return $packages;

} // mdjm_import_packages_as_posts_14

/**
 * Update all event packages and addons to post ID.
 *
 * @since	1.4
 * @param	arr		$packages	Array of package slugs to post ID.
 * @param	arr		$items		Array of addon slugs to post ID.
 */
function mdjm_convert_event_packages_to_post_ids_14( $packages, $items )	{

	if ( get_option( 'mdjm_upgrade_14_convert_events' ) )	{
		return;
	}

	$events = mdjm_get_events( array(
		'meta_query' => array(
			'relation' => 'OR',
			array(
				'key'     => '_mdjm_event_package',
				'value'   => '',
				'compare' => '!='
			),
			array(
				'key'     => '_mdjm_event_addons',
				'value'   => '',
				'compare' => '!='
			)
		)
	) );

	if ( $events )	{
		foreach ( $events as $event )	{
			$addons = array();

			$current_package = get_post_meta( $event->ID, '_mdjm_event_package', true );
			$current_items   = get_post_meta( $event->ID, '_mdjm_event_addons', true );

			if ( $current_package )	{
				update_post_meta( $event->ID, '_mdjm_event_package_pre_14', $current_package );
				if ( array_key_exists( $current_package, $packages ) )	{
					update_post_meta( $event->ID, '_mdjm_event_package', $packages[ $current_package ] );
				}
			}

			if ( $current_items )	{
				update_post_meta( $event->ID, '_mdjm_event_addons_pre_14', $current_items );
				$addons = array();
				foreach ( $current_items as $current_item )	{
					if ( array_key_exists( $current_item, $items ) )	{
						$addons[] = $items[ $current_item ];
					}
				}

				if ( ! empty( $addons ) )	{
					update_post_meta( $event->ID, '_mdjm_event_addons', $addons );
				}

			}

		}
	}

	update_option( 'mdjm_upgrade_14_convert_events', true );

} // mdjm_convert_event_packages_to_post_ids_14
