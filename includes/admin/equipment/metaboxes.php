<?php

/**
 * Contains all metaboxe functions for the mdjm-package post type
 *
 * @package		MDJM
 * @subpackage	Equipment
 * @since		1.3
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) )
	exit;

/**
 * Remove unwanted metaboxes to for the mdjm-package post type.
 *
 * @since	1.3
 * @param
 * @return
 */
function mdjm_remove_package_meta_boxes()	{
	remove_meta_box( 'authordiv','mdjm-package','normal' );
	remove_meta_box( 'commentstatusdiv','mdjm-package','normal' );
	remove_meta_box( 'commentsdiv','mdjm-package','normal' );
	remove_meta_box( 'postcustom','mdjm-package','normal' );
	remove_meta_box( 'postexcerpt','mdjm-package','normal' );
	remove_meta_box( 'revisionsdiv','mdjm-package','normal' );
	remove_meta_box( 'slugdiv','mdjm-package','normal' );
	remove_meta_box( 'trackbacksdiv','mdjm-package','normal' );
} // mdjm_remove_package_meta_boxes
add_action( 'admin_head', 'mdjm_remove_package_meta_boxes' );

/**
 * Add the metaboxes for the mdjm-package post type.
 *
 * @since	1.3
 * @param	int		$post		Required: The post object (WP_Post).
 * @return
 */
function mdjm_add_package_meta_boxes( $post )	{
	
} // mdjm_add_package_meta_boxes
add_action( 'add_meta_boxes_mdjm-package', 'mdjm_add_package_meta_boxes' );