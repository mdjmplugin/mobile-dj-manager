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
 * Load Scripts
 *
 * Enqueues the required scripts.
 *
 * @since	1.3
 * @global	$post
 * @return	void
 */
function mdjm_load_scripts()	{
	
	$js_dir = MDJM_PLUGIN_URL . '/assets/js/';
	
	wp_register_script( 'mdjm-ajax', $js_dir . 'mdjm-ajax.js', array( 'jquery' ), MDJM_VERSION_NUM );
	wp_enqueue_script( 'mdjm-ajax' );

	wp_localize_script(
		'mdjm-ajax',
		'mdjm_vars',
		apply_filters(
			'mdjm_script_vars',
			array(
				'ajaxurl'               => mdjm_get_ajax_url(),
				'ajax_loader'           => MDJM_PLUGIN_URL . '/assets/images/loading.gif',
				'required_date_message' => __( 'Please select a date', 'mobile-dj-manager' ),
				'availability_ajax'     => mdjm_get_option( 'avail_ajax', false ),
				'available_redirect'    => mdjm_get_option( 'availability_check_pass_page', 'text' ) != 'text' ? mdjm_get_formatted_url( mdjm_get_option( 'availability_check_pass_page' ) ) : 'text',
				'available_text'        => mdjm_get_option( 'availability_check_pass_text', false ),
				'unavailable_redirect'  => mdjm_get_option( 'availability_check_fail_page', 'text' ),
				'unavailable_text'      => mdjm_get_option( 'availability_check_fail_text', false ),
			)
		)
	);
		
	wp_register_script( 'jquery-validation-plugin', '//ajax.aspnetcdn.com/ajax/jquery.validate/1.14.0/jquery.validate.min.js', array( 'jquery' ) );
	wp_enqueue_script( 'jquery-validation-plugin');
	
	wp_enqueue_script( 'jquery-ui-datepicker', array( 'jquery' ) );
	
} // mdjm_load_scripts
add_action( 'wp_enqueue_scripts', 'mdjm_load_scripts' );

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
	$css_dir = MDJM_PLUGIN_URL . '/assets/css/';
	
	$child_theme_style_sheet    = trailingslashit( get_stylesheet_directory() ) . $templates_dir . $file;
	$parent_theme_style_sheet   = trailingslashit( get_template_directory()   ) . $templates_dir . $file;
	$mdjm_plugin_style_sheet    = trailingslashit( mdjm_get_templates_dir()    ) . $file;
	
	// Look in the child theme, followed by the parent theme, and finally the MDJM template DIR.
	// Allows users to copy the MDJM stylesheet to their theme DIR and customise.
	if ( file_exists( $child_theme_style_sheet ) )	{
		$url = trailingslashit( get_stylesheet_directory_uri() ) . $templates_dir . $file;
	} elseif ( file_exists( $parent_theme_style_sheet ) )	{
		$url = trailingslashit( get_template_directory_uri() ) . $templates_dir . $file;
	} elseif	( file_exists( $mdjm_plugin_style_sheet ) || file_exists( $mdjm_plugin_style_sheet ) )	{
		$url = trailingslashit( mdjm_get_templates_url() ) . $file;
	}
	
	wp_register_style( 'mdjm-styles', $url, array(), MDJM_VERSION_NUM );
	wp_enqueue_style( 'mdjm-styles' );
	
	wp_register_style( 'font-awesome', '//maxcdn.bootstrapcdn.com/font-awesome/4.6.1/css/font-awesome.min.css' );	
	wp_enqueue_style( 'font-awesome' );
	
	wp_register_style('jquery-ui', '//ajax.googleapis.com/ajax/libs/jqueryui/1.8/themes/base/jquery-ui.css');
  	wp_enqueue_style( 'jquery-ui' );

} // mdjm_register_styles
add_action( 'wp_enqueue_scripts', 'mdjm_register_styles' );

/**
 * Load Admin Styles
 *
 * Enqueues the required styles for admin.
 *
 * @since	1.3
 * @return	void
 */
function mdjm_register_admin_styles( $hook )	{

	$file          = 'mdjm-admin.css';
	$css_dir = MDJM_PLUGIN_URL . '/assets/css/';
	
	wp_register_style( 'mdjm-admin', $css_dir . $file, '', MDJM_VERSION_NUM );
	wp_enqueue_style( 'mdjm-admin' );
	
	wp_register_style('jquery-ui', '//ajax.googleapis.com/ajax/libs/jqueryui/1.8/themes/base/jquery-ui.css');
	wp_register_style( 'font-awesome', '//maxcdn.bootstrapcdn.com/font-awesome/4.6.1/css/font-awesome.min.css' );
	
  	wp_enqueue_style( 'jquery-ui' );
	wp_enqueue_style( 'font-awesome' );
		
} // mdjm_register_styles
add_action( 'admin_enqueue_scripts', 'mdjm_register_admin_styles' );

/**
 * Load Admin Scripts
 *
 * Enqueues the required scripts for admin.
 *
 * @since	1.3
 * @return	void
 */
function mdjm_register_admin_scripts( $hook )	{

	$js_dir = MDJM_PLUGIN_URL . '/assets/js/';

	wp_enqueue_script( 'jquery-ui-datepicker', array( 'jquery' ) );
		
	if( strpos( $hook, 'mdjm' ) )	{
		wp_enqueue_script( 'jquery' );
		
	}
	
	$require_validation = array( 'mdjm-event_page_mdjm-comms' );
	
	if ( in_array( $hook, $require_validation ) )	{
		
		wp_register_script( 'jquery-validation-plugin', 'https://ajax.aspnetcdn.com/ajax/jquery.validate/1.11.1/jquery.validate.min.js', false );
		wp_enqueue_script( 'jquery-validation-plugin' );

	}
	
	wp_register_script( 'mdjm-admin-ajax', $js_dir . 'mdjm-admin-ajax.js', array( 'jquery' ), MDJM_VERSION_NUM );
	wp_enqueue_script( 'mdjm-admin-ajax' );
	
	wp_register_script( 'mdjm-admin-scripts', $js_dir . 'admin-scripts.js', array( 'jquery' ), MDJM_VERSION_NUM );
	wp_enqueue_script( 'mdjm-admin-scripts' );

	wp_localize_script(
		'mdjm-admin-ajax',
		'mdjm_admin_vars',
		apply_filters(
			'mdjm_admin_script_vars',
			array(
				'ajaxurl'        => mdjm_get_ajax_url(),
				'curpage'        => $hook,
				'load_recipient' => isset( $_GET['recipient'] ) ? $_GET['recipient'] : false
			)
		)
	);
	
	wp_localize_script(
		'mdjm-admin-scripts',
		'mdjm_admin_vars',
		apply_filters(
			'mdjm_admin_script_vars',
			array(
				'ajaxurl'        => mdjm_get_ajax_url(),
				'curpage'        => $hook,
				'load_recipient' => isset( $_GET['recipient'] ) ? $_GET['recipient'] : false,
				'ajax_loader'    => MDJM_PLUGIN_URL . '/assets/images/loading.gif'
			)
		)
	);

} // mdjm_register_styles
add_action( 'admin_enqueue_scripts', 'mdjm_register_admin_scripts' );