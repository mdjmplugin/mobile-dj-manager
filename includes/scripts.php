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
				'is_payment'            => mdjm_is_payment() ? '1' : '0',
				'default_gateway'       => mdjm_get_default_gateway(),
				'payment_loading'       => __( 'Please Wait...', 'mobile-dj-manager' ),
				'no_payment_amount'     => __( 'Select Payment Amount', 'mobile-dj-manager' ),
				'complete_payment'      => mdjm_get_payment_button_text()
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

	if ( mdjm_is_payment( true ) ) {
		wp_enqueue_style( 'dashicons' );
	}

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

	wp_register_style( 'jquery-chosen', $css_dir . 'chosen.css', array(), MDJM_PLUGIN_URL );

	wp_register_style( 'mdjm-admin', $css_dir . $file, '', MDJM_VERSION_NUM );
	wp_enqueue_style( 'mdjm-admin' );
	
	wp_register_style('jquery-ui', '//ajax.googleapis.com/ajax/libs/jqueryui/1.8/themes/base/jquery-ui.css');
	wp_register_style( 'font-awesome', '//maxcdn.bootstrapcdn.com/font-awesome/4.6.1/css/font-awesome.min.css' );

	wp_enqueue_style( 'jquery-chosen' );
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

	wp_register_script( 'jquery-chosen', $js_dir . 'chosen.jquery.js', array( 'jquery' ), MDJM_PLUGIN_URL );
	wp_enqueue_script( 'jquery-chosen' );

	wp_enqueue_script( 'jquery-ui-datepicker', array( 'jquery' ) );
		
	if( strpos( $hook, 'mdjm' ) )	{
		wp_enqueue_script( 'jquery' );
		
	}

	$editing_event      = false;
	$require_validation = array( 'mdjm-event_page_mdjm-comms' );
	$sortable           = array( 'admin_page_mdjm-custom-event-fields', 'admin_page_mdjm-custom-client-fields' );

	if ( 'post.php' == $hook || 'post-new.php' == $hook )	{
		
		if ( isset( $_GET['post'] ) && 'mdjm-event' == get_post_type( $_GET['post'] ) )	{
			$editing_event = true;
		}
		if ( isset( $_GET['post_type'] ) && 'mdjm-event' == $_GET['post_type'] )	{
			$editing_event = true;
		}
		
		if ( $editing_event )	{
			$require_validation[] = 'post.php';
			$require_validation[] = 'post-new.php';
		}
	}
	
	if ( in_array( $hook, $require_validation ) )	{
		
		wp_register_script( 'jquery-validation-plugin', '//ajax.aspnetcdn.com/ajax/jquery.validate/1.11.1/jquery.validate.min.js', false );
		wp_enqueue_script( 'jquery-validation-plugin' );

	}

	if ( in_array( $hook, $sortable ) )	{
		wp_enqueue_script( 'jquery-ui-sortable' );
	}
	
	wp_register_script( 'mdjm-admin-scripts', $js_dir . 'admin-scripts.js', array( 'jquery' ), MDJM_VERSION_NUM );
	wp_enqueue_script( 'mdjm-admin-scripts' );

	wp_localize_script(
		'mdjm-admin-scripts',
		'mdjm_admin_vars',
		apply_filters(
			'mdjm_admin_script_vars',
			array(
				'ajaxurl'              => mdjm_get_ajax_url(),
				'current_page'         => $hook,
				'editing_event'        => $editing_event,
				'load_recipient'       => isset( $_GET['recipient'] ) ? $_GET['recipient'] : false,
				'ajax_loader'          => MDJM_PLUGIN_URL . '/assets/images/loading.gif',
				'no_client_first_name' => __( 'Enter a first name for the client', 'mobile-dj-manager' ),
				'no_client_email'      => __( 'Enter an email address for the client', 'mobile-dj-manager' ),
				'no_txn_amount'        => __( 'Enter a transaction value', 'mobile-dj-manager' ),
				'no_txn_date'          => __( 'Enter a transaction date', 'mobile-dj-manager' ),
				'no_txn_for'           => __( 'What is the transaction for?', 'mobile-dj-manager' ),
				'no_txn_src'           => __( 'Enter a transaction source', 'mobile-dj-manager' ),
				'no_venue_name'        => __( 'Enter a name for the venue', 'mobile-dj-manager' ),
				'currency_symbol'      => mdjm_currency_symbol(),
				'deposit_is_pct'       => ( 'percentage' == mdjm_get_event_deposit_type() ) ? true : false,
				'update_deposit'       => ( 'percentage' == mdjm_get_event_deposit_type() ) ? true : false
			)
		)
	);

} // mdjm_register_styles
add_action( 'admin_enqueue_scripts', 'mdjm_register_admin_scripts' );