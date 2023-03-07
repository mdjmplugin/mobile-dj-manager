<?php


/**
 * Scripts
 *
 * @package MDJM
 * @subpackage Functions
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 1.3
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Load Scripts
 *
 * Enqueues the required scripts.
 *
 * @since 1.3
 * @global $post
 * @return void
 */
function mdjm_load_scripts() {

	$js_dir        = MDJM_PLUGIN_URL . '/assets/js/';
	$suffix        = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '' : '.min';
	$is_payment    = mdjm_is_payment() ? '1' : '0';
	$agree_privacy = mdjm_get_option( 'show_agree_to_privacy_policy', false );
	$privacy_page  = mdjm_get_privacy_page();
	$privacy       = false;
	$agree_terms   = mdjm_get_option( 'show_agree_to_terms', false );
	$terms_text    = mdjm_get_option( 'agree_terms_text', false );
	$terms_label   = mdjm_get_option( 'agree_terms_label', false );
	$terms         = false;
	$thickbox      = false;
	$privacy_error = mdjm_messages( 'agree_to_policy' );
	$privacy_error = $privacy_error['message'];
	$terms_error   = mdjm_messages( 'agree_to_terms' );
	$terms_error   = $terms_error['message'];

	if ( ! empty( $agree_privacy ) && ! empty( $privacy_page ) ) {
		$privacy = true;

		if ( 'thickbox' === mdjm_get_option( 'show_agree_policy_type' ) ) {
			$thickbox = true;
		}
	}

	if ( ! empty( $agree_terms ) && ! empty( $terms_text ) && ! empty( $terms_label ) ) {
		$terms    = true;
		$thickbox = true;
	}

	if ( $is_payment && $thickbox && ( $privacy || $terms ) ) {
		add_thickbox();
	}

	wp_register_script( 'mdjm-ajax', $js_dir . 'mdjm-ajax.min.js', array( 'jquery' ), MDJM_VERSION_NUM );
	wp_enqueue_script( 'mdjm-ajax' );

	wp_localize_script(
		'mdjm-ajax',
		'mdjm_vars',
		apply_filters(
			'mdjm_script_vars',
			array(
				'ajaxurl'                   => mdjm_get_ajax_url(),
				'ajax_loader'               => MDJM_PLUGIN_URL . '/assets/images/loading.gif',
				'availability_ajax'         => mdjm_get_option( 'avail_ajax', false ),
				'available_redirect'        => mdjm_get_option( 'availability_check_pass_page', 'text' ) !== 'text' ? mdjm_get_formatted_url( mdjm_get_option( 'availability_check_pass_page' ) ) : 'text',
				'available_text'            => mdjm_get_option( 'availability_check_pass_text', false ),
				'complete_payment'          => mdjm_get_payment_button_text(),
				'date_format'               => mdjm_format_datepicker_date(),
				'default_gateway'           => mdjm_get_default_gateway(),
				'default_playlist_category' => mdjm_get_option( 'playlist_default_cat' ),
				'first_day'                 => get_option( 'start_of_week' ),
				'guest_playlist_closed'     => sprintf( esc_html__( 'The playlist for this %s is now closed and not accepting suggestions', 'mobile-dj-manager' ), esc_html( mdjm_get_label_singular( true ) ) ),
				'is_payment'                => $is_payment,
				'no_card_name'              => __( 'Enter the name printed on your card', 'mobile-dj-manager' ),
				'no_payment_amount'         => __( 'Select Payment Amount', 'mobile-dj-manager' ),
				'payment_loading'           => __( 'Please Wait...', 'mobile-dj-manager' ),
				'playlist_page'             => mdjm_get_formatted_url( mdjm_get_option( 'playlist_page' ) ),
				'playlist_updated'          => __( 'Your entry was added successfully', 'mobile-dj-manager' ),
				'privacy_error'             => esc_html( $privacy_error ),
				'profile_page'              => mdjm_get_formatted_url( mdjm_get_option( 'profile_page' ) ),
				'profile_updated'           => __( 'Your details have been updated', 'mobile-dj-manager' ),
				'required_date_message'     => __( 'Please select a date', 'mobile-dj-manager' ),
				'require_privacy'           => $privacy,
				'require_terms'             => $terms,
				'rest_url'                  => esc_url_raw( rest_url( 'mdjm/v1/' ) ),
				'submit_client_profile'     => __( 'Update Details', 'mobile-dj-manager' ),
				'submit_guest_playlist'     => __( 'Suggest Song', 'mobile-dj-manager' ),
				'submit_playlist'           => __( 'Add to Playlist', 'mobile-dj-manager' ),
				'submit_playlist_loading'   => __( 'Please Wait...', 'mobile-dj-manager' ),
				'submit_profile_loading'    => __( 'Please Wait...', 'mobile-dj-manager' ),
				'unavailable_redirect'      => mdjm_get_option( 'availability_check_fail_page', 'text' ) !== 'text' ? mdjm_get_formatted_url( mdjm_get_option( 'availability_check_fail_page' ) ) : 'text',
				'terms_error'               => esc_html( $terms_error ),
				'unavailable_text'          => mdjm_get_option( 'availability_check_fail_text', false ),
				'payment_event_id'          => isset( $_GET['event_id'] ) ? esc_html( $_GET['event_id'] ) : '',
			)
		)
	);

	wp_register_script( 'jquery-validation-plugin', MDJM_PLUGIN_URL . '/assets/libs/jquery-validate/jquery.validate.min.js', false );

	wp_enqueue_script( 'jquery-validation-plugin' );

	wp_enqueue_script( 'jquery-ui-datepicker', array( 'jquery' ), '0', true );

} // mdjm_load_scripts
add_action( 'wp_enqueue_scripts', 'mdjm_load_scripts' );

/**
 * Load Frontend Styles
 *
 * Enqueues the required styles for the frontend.
 *
 * @since 1.3
 * @return void
 */
function mdjm_register_styles() {
	global $post;

	$templates_dir = mdjm_get_theme_template_dir_name();
	$css_dir       = MDJM_PLUGIN_URL . '/assets/css/';
	$suffix        = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '' : '.min';
	$file          = 'mdjm.css';

	$child_theme_style_sheet  = trailingslashit( get_stylesheet_directory() ) . $templates_dir . $file;
	$parent_theme_style_sheet = trailingslashit( get_template_directory() ) . $templates_dir . $file;
	$mdjm_plugin_style_sheet  = trailingslashit( mdjm_get_templates_dir() ) . $file;

	// Look in the child theme, followed by the parent theme, and finally the MDJM template DIR.
	// Allows users to copy the MDJM stylesheet to their theme DIR and customise.
	if ( file_exists( $child_theme_style_sheet ) ) {
		$url = trailingslashit( get_stylesheet_directory_uri() ) . $templates_dir . $file;
	} elseif ( file_exists( $parent_theme_style_sheet ) ) {
		$url = trailingslashit( get_template_directory_uri() ) . $templates_dir . $file;
	} elseif ( file_exists( $mdjm_plugin_style_sheet ) || file_exists( $mdjm_plugin_style_sheet ) ) {
		$url = trailingslashit( mdjm_get_templates_url() ) . $file;
	}

	wp_register_style( 'mdjm-styles', $url, array(), MDJM_VERSION_NUM );
	wp_enqueue_style( 'mdjm-styles' );

	// Only load if Unload FontAwesomecheckbox is unchecked in Settings
	if ( ! mdjm_get_option( 'unload_fontawesome', false ) ) {
		wp_register_style( 'mdjm-font-awesome', MDJM_PLUGIN_URL . '/assets/libs/font-awesome/font-awesome.min.css' );
		wp_enqueue_style( 'mdjm-font-awesome' );
	}

	if ( ! empty( $post ) ) {
		if ( is_a( $post, 'WP_Post' ) && has_shortcode( $post->post_content, 'mdjm-availability' ) ) {
			wp_register_style( 'jquery-ui-css', $css_dir . 'jquery-ui.min.css', array(), MDJM_VERSION_NUM );
		}
	}

	if ( mdjm_is_payment( true ) ) {

		wp_enqueue_style( 'dashicons' );
	}

} // mdjm_register_styles
add_action( 'wp_enqueue_scripts', 'mdjm_register_styles', PHP_INT_MAX );

/**
 * Load Admin Styles
 *
 * Enqueues the required styles for admin.
 *
 * @since 1.3
 * @return void
 */
function mdjm_register_admin_styles( $hook ) {

	$ui_style = ( 'classic' === get_user_option( 'admin_color' ) ) ? 'classic' : 'fresh';
	$css_dir  = MDJM_PLUGIN_URL . '/assets/css/';
	$libs_dir = MDJM_PLUGIN_URL . '/assets/libs/';
	$suffix   = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '' : '.min';
	$file     = 'mdjm-admin.css';

	wp_register_style( 'jquery-chosen', $css_dir . 'chosen.min.css', array(), MDJM_PLUGIN_URL );
	wp_register_style( 'jquery-ui-css', $css_dir . 'jquery-ui-' . $ui_style . '.min.css' );

	// Only load if Unload FontAwesomecheckbox is unchecked in Settings
	if ( ! mdjm_get_option( 'unload_fontawesome', false ) ) {
		wp_register_style( 'mdjm-font-awesome', MDJM_PLUGIN_URL . '/assets/libs/font-awesome/font-awesome.min.css' );
		wp_enqueue_style( 'mdjm-font-awesome' );
	}

	wp_enqueue_style( 'jquery-chosen' );
	wp_enqueue_style( 'jquery-ui-css' );

	// Settings page color picker.
	if ( 'mdjm-event_page_mdjm-settings' === $hook ) {
		wp_enqueue_style( 'wp-color-picker' );
	}

	// Availability calendar
	if ( 'mdjm-event_page_mdjm-availability' === $hook ) {

		wp_register_style( MDJM_PLUGIN_URL . '/assets/libs/bootstrap/bootstrap.min.css', array(), MDJM_VERSION_NUM );
		wp_register_style(
			'mdjm-fullcalendar-css',
			$libs_dir . 'fullcalendar/fullcalendar.min.css',
			array(),
			MDJM_VERSION_NUM
		);

		wp_enqueue_style( 'mdjm-fullcalendar-css' );
		wp_enqueue_style( 'mdjm-bootstrap-css' );
	}

	wp_register_style( 'mdjm-admin', $css_dir . $file, '', MDJM_VERSION_NUM );
	wp_enqueue_style( 'mdjm-admin' );

} // mdjm_register_styles
add_action( 'admin_enqueue_scripts', 'mdjm_register_admin_styles', PHP_INT_MAX );

/**
 * Load Admin Scripts
 *
 * Enqueues the required scripts for admin.
 *
 * @since 1.3
 * @return void
 */
function mdjm_register_admin_scripts( $hook ) {

	$js_dir             = MDJM_PLUGIN_URL . '/assets/js/';
	$libs_dir           = MDJM_PLUGIN_URL . '/assets/libs/';
	$suffix             = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '' : '.min';
	$file               = 'admin-scripts.min.js';
	$dashboard          = 'index.php' === $hook ? true : false;
	$editing_event      = false;
	$require_validation = array( 'mdjm-event_page_mdjm-comms' );
	$sortable           = array(
		'admin_page_mdjm-custom-event-fields',
		'admin_page_mdjm-custom-client-fields',
	);

	wp_register_script( 'jquery-chosen', $js_dir . 'chosen.jquery.min.js', array( 'jquery' ), MDJM_VERSION_NUM );
	wp_enqueue_script( 'jquery-chosen' );

	wp_enqueue_script( 'jquery-ui-datepicker', array( 'jquery' ), '0', true );

	if ( strpos( $hook, 'mdjm' ) ) {
		wp_enqueue_script( 'jquery' );
	}

	if ( 'post.php' === $hook || 'post-new.php' === $hook ) {
		if ( isset( $_GET['post'] ) && 'mdjm-addon' !== get_post_type( sanitize_text_field( wp_unslash( $_GET['post'] ) ) ) ) {
			$sortable[] = 'post.php';
			$sortable[] = 'post-new.php';
		}

		if ( isset( $_GET['post'] ) && 'mdjm-event' === get_post_type( sanitize_text_field( wp_unslash( $_GET['post'] ) ) ) ) {
			$editing_event = true;
		}

		if ( isset( $_GET['post_type'] ) && 'mdjm-event' === $_GET['post_type'] ) {
			$editing_event = true;
		}

		if ( $editing_event ) {
			$require_validation[] = 'post.php';
			$require_validation[] = 'post-new.php';
		}

		if ( isset( $_GET['post'] ) && 'mdjm-transaction' !== get_post_type( sanitize_text_field( wp_unslash( $_GET['post'] ) ) ) ) {
			wp_register_script( 'mdjm-trans-js', MDJM_PLUGIN_URL . '/assets/js/mdjm-trans-post-val.js', array( 'jquery' ), MDJM_VERSION_NUM );
			wp_enqueue_script( 'mdjm-trans-js' );
			wp_localize_script( 'mdjm-trans-js', 'transaction_type', array( 'ajax_url' => admin_url( 'admin-ajax.php' ) ) );
		}
	}

	if ( in_array( $hook, $require_validation ) ) {
		wp_register_script( 'jquery-validation-plugin', MDJM_PLUGIN_URL . '/assets/libs/jquery-validate/jquery.validate.min.js', false );
		wp_enqueue_script( 'jquery-validation-plugin' );
	}

	if ( in_array( $hook, $sortable ) ) {
		wp_enqueue_script( 'jquery-ui-sortable' );
	}

	// Settings page color picker.
	if ( 'mdjm-event_page_mdjm-settings' === $hook ) {
		wp_enqueue_script( 'wp-color-picker' );
	}

	// Availability calendar.
	if ( 'mdjm-event_page_mdjm-availability' === $hook ) {
		wp_register_script(
			'mdjm-moment-js',
			$libs_dir . 'moment/moment-with-locales.min.js',
			array( 'jquery' ),
			MDJM_VERSION_NUM
		);
		wp_register_script(
			'mdjm-fullcalendar-js',
			$libs_dir . 'fullcalendar/fullcalendar.min.js',
			array( 'jquery', 'mdjm-moment-js' ),
			MDJM_VERSION_NUM
		);

		wp_register_script( 'jquery-validation-plugin', MDJM_PLUGIN_URL . '/assets/libs/popper/popper.min.js', false );

		wp_register_script(
			'mdjm-popper-js',
			MDJM_PLUGIN_URL . '/assets/libs/popper/popper.min.js',
			array( 'jquery' ),
			MDJM_VERSION_NUM
		);
		wp_register_script(
			'mdjm-bootstrap-js',
			MDJM_PLUGIN_URL . '/assets/libs/bootstrap/bootstrap.min.js',
			array( 'jquery' ),
			MDJM_VERSION_NUM
		);
		wp_register_script(
			'mdjm-availability-scripts-js',
			$js_dir . 'availability-scripts.min.js',
			array( 'jquery', 'mdjm-moment-js', 'mdjm-fullcalendar-js' ),
			MDJM_VERSION_NUM
		);

		wp_enqueue_script( 'mdjm-moment-js' );
		wp_enqueue_script( 'mdjm-fullcalendar-js' );
		wp_enqueue_script( 'mdjm-popper-js' );
		wp_enqueue_script( 'mdjm-bootstrap-js' );
		wp_enqueue_script( 'mdjm-availability-scripts-js' );

		wp_localize_script(
			'mdjm-availability-scripts-js',
			'mdjm_calendar_vars',
			apply_filters(
				'mdjm_calendar_vars',
				array(
					'default_view' => mdjm_get_calendar_view( $dashboard ),
					'first_day'    => get_option( 'start_of_week' ),
					'time_format'  => mdjm_format_calendar_time(),
				)
			)
		);
	}

	wp_register_script( 'mdjm-admin-scripts', $js_dir . $file, array( 'jquery' ), MDJM_VERSION_NUM );
	wp_enqueue_script( 'mdjm-admin-scripts' );

	wp_localize_script(
		'mdjm-admin-scripts',
		'mdjm_admin_vars',
		apply_filters(
			'mdjm_admin_script_vars',
			array(
				'admin_url'            => ! is_multisite() ? admin_url() : network_admin_url(),
				'ajax_loader'          => MDJM_PLUGIN_URL . '/assets/images/loading.gif',
				'ajaxurl'              => mdjm_get_ajax_url(),
				'currency'             => mdjm_get_currency(),
				'currency_decimals'    => mdjm_currency_decimal_filter(),
				'currency_position'    => mdjm_get_option( 'currency_format', 'before' ),
				'currency_sign'        => mdjm_currency_filter( '' ),
				'currency_symbol'      => mdjm_currency_symbol(),
				'current_page'         => $hook,
				'deposit_is_pct'       => ( 'percentage' === mdjm_get_event_deposit_type() ) ? true : false,
				'editing_event'        => $editing_event,
				'load_recipient'       => isset( $_GET['recipient'] ) ? sanitize_text_field( wp_unslash( $_GET['recipient'] ) ) : false,
				'min_travel_distance'  => mdjm_get_option( 'travel_min_distance' ),
				'no_client_email'      => __( 'Enter an email address for the client', 'mobile-dj-manager' ),
				'no_client_first_name' => __( 'Enter a first name for the client', 'mobile-dj-manager' ),
				'no_txn_amount'        => __( 'Enter a transaction value', 'mobile-dj-manager' ),
				'no_txn_date'          => __( 'Enter a transaction date', 'mobile-dj-manager' ),
				'no_txn_for'           => __( 'What is the transaction for?', 'mobile-dj-manager' ),
				'no_txn_src'           => __( 'Enter a transaction source', 'mobile-dj-manager' ),
				'no_venue_name'        => __( 'Enter a name for the venue', 'mobile-dj-manager' ),
				'one_month_min'        => __( 'You must have a pricing option for at least one month', 'mobile-dj-manager' ),
				'one_item_min'         => __( 'Select at least one Add-on', 'mobile-dj-manager' ),
				'select_months'        => __( 'Select Months', 'mobile-dj-manager' ),
				'time_format'          => mdjm_get_option( 'time_format' ),
				'update_deposit'       => ( 'percentage' === mdjm_get_event_deposit_type() ) ? true : false,
				'update_travel_cost'   => mdjm_get_option( 'travel_add_cost', false ),
				'zero_cost'            => sprintf( esc_html__( 'Are you sure you want to save this %1$s with a total cost of %2$s?', 'mobile-dj-manager' ), esc_html( mdjm_get_label_singular( true ) ), mdjm_currency_filter( mdjm_format_amount( '0.00' ) ) ),
				'setup_time_change'    => __( 'Do you want to auto set the setup time?', 'mobile-dj-manager' ),
				'setup_time_interval'  => mdjm_get_option( 'setup_time', false ),
				'show_absence_form'    => __( 'Show absence form', 'mobile-dj-manager' ),
				'hide_absence_form'    => __( 'Hide absence form', 'mobile-dj-manager' ),
				'show_avail_form'      => __( 'Show availability checker', 'mobile-dj-manager' ),
				'hide_avail_form'      => __( 'Hide availability checker', 'mobile-dj-manager' ),
				'show_client_form'     => __( 'Show client form', 'mobile-dj-manager' ),
				'hide_client_form'     => __( 'Hide client form', 'mobile-dj-manager' ),
				'show_client_details'  => __( 'Show client details', 'mobile-dj-manager' ),
				'hide_client_details'  => __( 'Hide client details', 'mobile-dj-manager' ),
				'show_event_options'   => sprintf( esc_html__( 'Show %s options', 'mobile-dj-manager' ), esc_html( mdjm_get_label_singular( true ) ) ),
				'hide_event_options'   => sprintf( esc_html__( 'Hide %s options', 'mobile-dj-manager' ), esc_html( mdjm_get_label_singular( true ) ) ),
				'show_workers'         => sprintf( esc_html__( 'Show %s workers', 'mobile-dj-manager' ), esc_html( mdjm_get_label_singular( true ) ) ),
				'hide_workers'         => sprintf( esc_html__( 'Hide %s workers', 'mobile-dj-manager' ), esc_html( mdjm_get_label_singular( true ) ) ),
				'show_venue_details'   => __( 'Show venue', 'mobile-dj-manager' ),
				'hide_venue_details'   => __( 'Hide venue', 'mobile-dj-manager' ),
				'one_option'           => __( 'Choose an option', 'mobile-dj-manager' ),
				'one_or_more_option'   => __( 'Choose one or more options', 'mobile-dj-manager' ),
				'search_placeholder'   => __( 'Type to search all options', 'mobile-dj-manager' ),
				'task_completed'       => __( 'Task executed successfully', 'mobile-dj-manager' ),
				'type_to_search'       => __( 'Type to search', 'mobile-dj-manager' ),
				'unavailable_template' => mdjm_get_option( 'unavailable' ),
			)
		)
	);

	wp_register_script( 'jquery-flot', $js_dir . 'jquery.flot.min.js' );
	wp_enqueue_script( 'jquery-flot' );

} // mdjm_register_admin_scripts
add_action( 'admin_enqueue_scripts', 'mdjm_register_admin_scripts' );
