<?php
/**
 * Scripts
 *
 * @package     MDJM
 * @subpackage  Functions
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.3
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) )
	exit;

/**
 * Load Frontend Styles
 *
 * Enqueues the required styles for the frontend.
 *
 * @since	1.3
 * @return	void
 */
function mdjm_register_styles()	{
	$file          = 'mdjm.css';
	$templates_dir = mdjm_get_theme_template_dir_name();
	
	$child_theme_style_sheet    = trailingslashit( get_stylesheet_directory() ) . $templates_dir . $file;
	$parent_theme_style_sheet   = trailingslashit( get_template_directory()   ) . $templates_dir . $file;
	$mdjm_plugin_style_sheet    = trailingslashit( mdjm_get_templates_dir()    ) . $file;
	
	// Look in the child theme, followed by the parent theme, and finally the MDJM template DIR.
	// Allows users to copy the MDJM stylesheet to their theme DIR and customise.
	if ( file_exists( $child_theme_style_sheet ) )	{
		$url = trailingslashit( get_stylesheet_directory_uri() ) . $templates_dir . $file;
	}
	elseif ( file_exists( $parent_theme_style_sheet ) )	{
		$url = trailingslashit( get_template_directory_uri() ) . $templates_dir . $file;
	}
	elseif	( file_exists( $mdjm_plugin_style_sheet ) || file_exists( $mdjm_plugin_style_sheet ) )	{
		$url = trailingslashit( mdjm_get_templates_url() ) . $file;
	}
	
	wp_register_style( 'mdjm-styles', $url, array(), MDJM_VERSION_NUM );
	wp_enqueue_style( 'mdjm-styles' );
} // mdjm_register_styles
add_action( 'wp_enqueue_scripts', 'mdjm_register_styles' );