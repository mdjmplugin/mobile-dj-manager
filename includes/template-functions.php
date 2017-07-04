<?php

if ( ! defined( 'ABSPATH' ) )
	exit;

/**
 * Contains all template related functions
 *
 * @package		MDJM
 * @subpackage	Templates
 * @since		1.3
 */

/**
 * Retrieve page ID's.
 *
 * Used for the Client Zone pages.
 *
 * @since	1.4.8
 * @param	str		$page	The page to retrieve the ID for
 * @return	int		The page ID
 */
function mdjm_get_page_id( $page )	{
	$page = apply_filters( 'mdjm_get_' . $page . '_page_id', mdjm_get_option( $page . '_page' ) );

	return $page ? absint( $page ) : -1;
} // mdjm_get_page_id

/**
 * Append Enquire Link to Packages.
 *
 * Automatically appends the enquiry link to package content, if enabled.
 *
 * @since	1.4
 * @param	int		package_id	Package ID
 * @return	void
 */

function mdjm_append_package_enquiry_link( $package_id ) {
	if ( mdjm_get_option( 'package_contact_btn', false ) ) {
		echo mdjm_get_enquire_now_button( array( 'id' => $package_id ) );
	}
} // mdjm_append_package_enquiry_link
add_action( 'mdjm_after_package_content', 'mdjm_append_package_enquiry_link' );

/**
 * Append Enquire Link to Addons.
 *
 * Automatically appends the enquiry link to addon content, if enabled.
 *
 * @since	1.4
 * @param	int		addon_id	Addon ID
 * @return	void
 */

function mdjm_append_addon_enquiry_link( $addon_id ) {
	if ( mdjm_get_option( 'package_contact_btn', false ) ) {
		echo mdjm_get_enquire_now_button( array( 'type' => 'addon', 'id' => $addon_id ) );
	}
} // mdjm_append_addon_enquiry_link
add_action( 'mdjm_after_addon_content', 'mdjm_append_addon_enquiry_link' );

/**
 * Generates an enquire now button.
 *
 * @since	1.4
 * @param	arr		Array of arguments. See @defaults.
 * @return	str		Enquire Now HTML button
 */
function mdjm_get_enquire_now_button( $args )	{

	$defaults = array(
		'type' => 'package',
		'id'   => 0
	);

	$args = wp_parse_args( $args, $defaults );

	$label = esc_html( mdjm_get_option( 'package_contact_btn_text', __( 'Enquire Now', 'mobile-dj-manager' ) ) );
	$label = apply_filters( 'mdjm_enquire_now_' . $args['type'] . '_label', $label );
	$name  = 'mdjm-' . $args['type'] . '-enquiry-button';
	$class = 'mdjm_' . $args['type'] . '_enquiry_button';
	$value = 'test';

	ob_start();
	?>
    <a href="<?php echo mdjm_get_formatted_url( mdjm_get_option( 'contact_page' ) ) . $args['type'] . '=' . $args['id']; ?>">
        <button type="button" name="<?php echo $name; ?>" class="<?php echo $class; ?>" formmethod="get" value="test"><?php echo $label; ?></button>
    </a>
    <?php
	$enquire_link = ob_get_clean();

	return apply_filters( 'mdjm_enquire_now_' . $args['type'] . '_button', $enquire_link, $args );
} // mdjm_get_enquire_now_button

/**
 * Returns the path to the MDJM templates directory
 *
 * @since	1.3
 * @return	str
 */
function mdjm_get_templates_dir() {
	return MDJM_PLUGIN_DIR . '/templates';
} // mdjm_get_templates_dir

/**
 * Returns the URL to the MDJM templates directory
 *
 * @since	1.3
 * @return	str
 */
function mdjm_get_templates_url() {
	return MDJM_PLUGIN_URL . '/templates';
} // mdjm_get_templates_url

/**
 * Returns the MDJM template files.
 *
 * @since	1.3
 * @return	arr
 */
function mdjm_get_template_files() {
	
	$template_files = array(
		'availability' => array(
			'availability-horizontal.php',
			'availability-vertical.php'
		),
		'contract' => array(
			'contract.php',
			'contract-signed.php',
		),
		'contract' => array(
			'contract.php',
			'contract-signed.php',
		),
		'email' => array(
			'email-body.php',
			'email-footer.php',
			'email-header.php'
		),
		'event' => array(
			'event-loop-footer.php',
			'event-loop-header.php',
			'event-loop.php',
			'event-none.php',
			'event-single.php'
		),
		'login' => array(
			'login-form.php'
		),
		'payments' => array(
			'payments-cc.php',
			'payments-items.php'
		),
		'playlist' => array(
			'playlist-client.php',
			'playlist-guest.php',
			'playlist-noevent.php'
		),
		'quote' => array(
			'quote-noevent.php',
			'quote.php'
		)
	);
	
	return apply_filters( 'mdjm_template_files', $template_files );
	
} // mdjm_get_template_files

/**
 * Retrieves a template part
 *
 * @since	1.3
 *
 * @param	str	$slug
 * @param	str $name Optional. Default null
 * @param	bool   $load
 *
 * @return	str
 *
 * @uses	mdjm_locate_template()
 * @uses	load_template()
 * @uses	get_template_part()
 */
function mdjm_get_template_part( $slug, $name = null, $load = true ) {
	
	// Execute code for this part
	do_action( 'get_template_part_' . $slug, $slug, $name );

	// Setup possible parts
	$templates = array();
	
	if ( isset( $name ) )	{
		$templates[] = $slug . '/' . $slug . '-' . $name . '.php';
	}
	
	$templates[] = $slug . '/' . $slug . '.php';

	// Allow template parts to be filtered
	$templates = apply_filters( 'mdjm_get_template_part', $templates, $slug, $name );

	// Return the part that is found
	return mdjm_locate_template( $templates, $load, false );

} // mdjm_get_template_part

/**
 * Retrieve the name of the highest priority template file that exists.
 *
 * Searches in the STYLESHEETPATH before TEMPLATEPATH so that themes which
 * inherit from a parent theme can just overload one file. If the template is
 * not found in either of those, it looks in the theme-compat folder last.
 *
 * Taken from bbPress
 *
 * @since	1.3
 *
 * @param	str|arr	$template_names Template file(s) to search for, in order.
 * @param	bool	$load If true the template file will be loaded if it is found.
 * @param	bool	$require_once Whether to require_once or require. Default true.
 *   Has no effect if $load is false.
 * @return	str		The template filename if one is located.
 */
function mdjm_locate_template( $template_names, $load = false, $require_once = true ) {
	
	// No file found yet
	$located = false;

	// Try to find a template file
	foreach ( (array) $template_names as $template_name ) {

		// Continue if template is empty
		if ( empty( $template_name ) )	{
			continue;
		}

		// Trim off any slashes from the template name
		$template_name = ltrim( $template_name, '/' );

		// try locating this template file by looping through the template paths
		foreach( mdjm_get_theme_template_paths() as $template_path ) {

			if( file_exists( $template_path . $template_name ) ) {
				$located = $template_path . $template_name;
				break;
			}
		}

		if( $located ) {
			break;
		}
	}

	if ( ( true == $load ) && ! empty( $located ) )	{
		load_template( $located, $require_once );
	}

	return $located;

} // mdjm_locate_template

/**
 * Returns a list of paths to check for template locations
 *
 * @since	1.3
 * @return	mixed|void
 */
function mdjm_get_theme_template_paths() {

	$template_dir = mdjm_get_theme_template_dir_name();

	$file_paths = array(
		1        => trailingslashit( get_stylesheet_directory() ) . $template_dir,
		10       => trailingslashit( get_template_directory() ) . $template_dir,
		100      => mdjm_get_templates_dir()
	);

	$file_paths = apply_filters( 'mdjm_template_paths', $file_paths );

	// sort the file paths based on priority
	ksort( $file_paths, SORT_NUMERIC );

	return array_map( 'trailingslashit', $file_paths );

} // mdjm_get_theme_template_paths

/**
 * Returns the template directory name.
 *
 * Themes can filter this by using the mdjm_templates_dir filter.
 *
 * @since	1.3
 * @return	str
*/
function mdjm_get_theme_template_dir_name() {
	return trailingslashit( apply_filters( 'mdjm_templates_dir', 'mdjm-templates' ) );
} // mdjm_get_theme_template_dir_name

/**
 * Before Package Content
 *
 * Adds an action to the beginning of a packages post content that can be hooked to
 * by other functions.
 *
 * @since	1.4
 * @global	$post
 * @param	$content	The the_content field of the package object
 * @return	str			The content with any additional data attached
 */
function mdjm_before_package_content( $content ) {
	global $post;

	if ( $post && $post->post_type == 'mdjm-package' && is_singular( 'mdjm-package' ) && is_main_query() && ! post_password_required() ) {
		ob_start();
		do_action( 'mdjm_before_package_content', $post->ID );
		$content = ob_get_clean() . $content;
	}

	return $content;
}
add_filter( 'the_content', 'mdjm_before_package_content' );

/**
 * After Package Content
 *
 * Adds an action to the end of a packages post content that can be hooked to by
 * other functions.
 *
 * @since	1.4
 * @global	$post
 * @param	$content	The the_content field of the package object
 * @return	str			The content with any additional data attached
 */
function mdjm_after_package_content( $content ) {
	global $post;

	if ( $post && $post->post_type == 'mdjm-package' && is_singular( 'mdjm-package' ) && is_main_query() && ! post_password_required() ) {
		ob_start();
		do_action( 'mdjm_after_package_content', $post->ID );
		$content .= ob_get_clean();
	}

	return $content;
}
add_filter( 'the_content', 'mdjm_after_package_content' );

/**
 * Before Addon Content
 *
 * Adds an action to the beginning of an addona post content that can be hooked to
 * by other functions.
 *
 * @since	1.4
 * @global	$post
 * @param	$content	The the_content field of the addon object
 * @return	str			The content with any additional data attached
 */
function mdjm_before_addon_content( $content ) {
	global $post;

	if ( $post && $post->post_type == 'mdjm-addon' && is_singular( 'mdjm-addon' ) && is_main_query() && ! post_password_required() ) {
		ob_start();
		do_action( 'mdjm_before_addon_content', $post->ID );
		$content = ob_get_clean() . $content;
	}

	return $content;
}
add_filter( 'the_content', 'mdjm_before_addon_content' );

/**
 * After Addon Content
 *
 * Adds an action to the end of an addons post content that can be hooked to by
 * other functions.
 *
 * @since	1.4
 * @global	$post
 * @param	$content	The the_content field of the addon object
 * @return	str			The content with any additional data attached
 */
function mdjm_after_addon_content( $content ) {
	global $post;

	if ( $post && $post->post_type == 'mdjm-addon' && is_singular( 'mdjm-addon' ) && is_main_query() && ! post_password_required() ) {
		ob_start();
		do_action( 'mdjm_after_addon_content', $post->ID );
		$content .= ob_get_clean();
	}

	return $content;
}
add_filter( 'the_content', 'mdjm_after_addon_content' );
