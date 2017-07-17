<?php

/**
 * Contains misc functions.
 *
 * @package		MDJM
 * @subpackage	Functions
 * @since		1.3
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) )
	exit;

/**
 * Set the correct date format for the jQuery date picker
 *
 * @since	1.3
 * @param
 * @return
 */
function mdjm_format_datepicker_date()	{
	$date_format = mdjm_get_option( 'short_date_format', 'd/m/Y' );
	
	$search = array( 'd', 'm', 'Y' );
	$replace = array( 'dd', 'mm', 'yy' );
	
	$date_format = str_replace( $search, $replace, $date_format );
			
	return apply_filters( 'mdjm_format_datepicker_date', $date_format );
} // mdjm_format_datepicker_date

/**
 * Add the MDJM MCE Shortcode button.
 * 
 * @since	1.3
 * @param
 * @return
 */
function mdjm_display_shortcode_button()	{    
    
	// Define the post types & screens within which the MCE button should be displayed
    $post_types = array( 'email_template', 'contract', 'page' );
    
	$screens = array( 
        'mdjm-event_page_mdjm-comms',
        'mdjm-event_page_mdjm-settings' );
    
    // Add the MDJM TinyMCE buttons
    $current_screen = get_current_screen();
    
	if( in_array( get_post_type(), $post_types ) || in_array( $current_screen->id, $screens ) )	{
		
		if ( 'true' == get_user_option( 'rich_editing' ) ) {
			add_filter( 'mce_external_plugins', 'mdjm_register_mce_plugin' );
			add_filter( 'mce_buttons', 'mdjm_register_mce_buttons' );
		}
	}  
	  
} // mdjm_display_shortcode_button
add_action( 'admin_head', 'mdjm_display_shortcode_button' );

/**
 * Get File Extension
 *
 * Returns the file extension of a filename.
 *
 * @since    1.5
 *
 * @param    unknown     $str    File name
 * @return   mixed       File extension
 */
function mdjm_get_file_extension( $str ) {
	$parts = explode( '.', $str );
	return end( $parts );
} // mdjm_get_file_extension

/**
 * Given an object or array of objects, convert them to arrays
 *
 * @since   1.5
 * @param   object|array   $object An object or an array of objects
 * @return  arr            An array or array of arrays, converted from the provided object(s)
 */
function mdjm_object_to_array( $object = array() ) {

	if ( empty( $object ) || ( ! is_object( $object ) && ! is_array( $object ) ) ) {
		return $object;
	}

	if ( is_array( $object ) ) {
		$return = array();
		foreach ( $object as $item ) {
			if ( is_a( $object, 'MDJM_Event' ) ) {
				$return[] = $object->array_convert();
			} else {
				$return[] = mdjm_object_to_array( $item );
			}

		}
	} else {
		if ( is_a( $object, 'MDJM_Event' ) ) {
			$return = $object->array_convert();
		} else {
			$return = get_object_vars( $object );

			// Now look at the items that came back and convert any nested objects to arrays
			foreach ( $return as $key => $value ) {
				$value = ( is_array( $value ) || is_object( $value ) ) ? mdjm_object_to_array( $value ) : $value;
				$return[ $key ] = $value;
			}
		}
	}

	return $return;

} // mdjm_object_to_array

/**
 * Register the script that inserts ths MDJM Shortcodes into the content
 * when the MDJM Shortcode button is used
 *
 * @since	1.3
 * @param	arr		$plugin_array	Array of registered MCE plugins
 * @return	arr		$plugin_array	Filtered array of registered MCE plugins
 *
 */
function mdjm_register_mce_plugin( $plugin_array ) {
	
	$plugin_array['mdjm_shortcodes_btn'] = MDJM_PLUGIN_URL . '/assets/js/mdjm-tinymce-shortcodes.js';
	
	return $plugin_array;
	
} // mdjm_register_mce_plugin

/*
 * Register the MDJM Shortcode button within the TinyMCE interface
 * 
 * @since	1.3
 * @params	arr		$buttons	Array of registered MCE buttons
 * @return	arr		$buttons	Filtered array of registered MCE buttons
 */
function mdjm_register_mce_buttons( $buttons ) {
	
	array_push( $buttons, 'mdjm_shortcodes_btn' );
	
	return $buttons;
} // mdjm_register_mce_buttons

/**
 * Datepicker.
 *
 * @since	1.3
 * @param	arr		$args	Datepicker field serttings.
 * @return	void
 */
function mdjm_insert_datepicker( $args = array() )	{
	$defaults = array(
		'class'			=> 'mdjm_date',
		'altfield'		=> '_mdjm_event_date',
		'altformat'		=> 'yy-mm-dd',
		'firstday'		=> get_option( 'start_of_week' ),
		'changeyear'	=> 'true',
		'changemonth'	=> 'true'		
	);

	$args = wp_parse_args( $args, $defaults );

	if ( ! empty( $args['id'] ) )	{
		$field = '#' . $args['id'];
	} else	{
		$field = '.' . $args['class'];
	}

	wp_enqueue_style( 'jquery-ui-css' );

	?>
    <script type="text/javascript">
	jQuery(document).ready( function($)	{
		$("<?php echo $field; ?>").datepicker({
			dateFormat  : "<?php echo mdjm_format_datepicker_date(); ?>",
			altField    : "#<?php echo $args['altfield']; ?>",
			altFormat   : "<?php echo $args['altformat']; ?>",
			firstDay    : "<?php echo $args['firstday']; ?>",
			changeYear  : "<?php echo $args['changeyear']; ?>",
			changeMonth : "<?php echo $args['changemonth']; ?>",
			minDate     : "<?php echo ( isset( $args['mindate'] ) ) ? $args['mindate'] : '' ; ?>",
			maxDate     : "<?php echo ( isset( $args['maxdate'] ) ) ? $args['maxdate'] : '' ; ?>"
		});
	});
	</script>
    <?php
} // mdjm_insert_datepicker

/**
 * Month Num To Name
 *
 * Takes a month number and returns the name three letter name of it.
 *
 * @since	1.0
 *
 * @param	int		$n
 * @param	bool	$full	True to output the full month name
 * @return	str		Short month name
 */
function mdjm_month_num_to_name( $n, $full = false ) {
	$timestamp = mktime( 0, 0, 0, $n, 1, 2005 );

	$output = $full ? 'F' : 'M';

	return date_i18n( $output, $timestamp );
} // mdjm_month_num_to_name

/**
 * Get PHP Arg Separator Output
 *
 * @since	1.3.8
 * @return	str		Arg separator output
 */
function mdjm_get_php_arg_separator_output() {
	return ini_get( 'arg_separator.output' );
} // mdjm_get_php_arg_separator_output

/**
 * Checks whether function is disabled.
 *
 * @since	1.4
 *
 * @param	str		$function	Name of the function.
 * @return	bool	Whether or not function is disabled.
 */
function mdjm_is_func_disabled( $function ) {
	$disabled = explode( ',',  ini_get( 'disable_functions' ) );

	return in_array( $function, $disabled );
} // mdjm_is_func_disabled

/**
 * Get the current page URL
 *
 * @since	1.3
 * @param
 * @return	str		$page_url	Current page URL
 */
function mdjm_get_current_page_url() {
	$scheme = is_ssl() ? 'https' : 'http';
	$uri    = esc_url( site_url( $_SERVER['REQUEST_URI'], $scheme ) );

	if ( is_front_page() )	{
		$uri = home_url();
	}

	$uri = apply_filters( 'mdjm_get_current_page_url', $uri );

	return $uri;
} // mdjm_get_current_page_url

/**
 * Retrieve the visitors IP address.
 *
 * @since	1.3.8
 * @return	str
 */
function mdjm_get_user_ip()	{
	if ( ! empty( $_SERVER['HTTP_CLIENT_IP'] ) )	{
		$ip_address = $_SERVER['HTTP_CLIENT_IP'];
	} elseif ( ! empty( $_SERVER['HTTP_X_FORWARDED_FOR'] ) )	{
		$ip_address = $_SERVER['HTTP_X_FORWARDED_FOR'];
	} else {
		$ip_address = $_SERVER['REMOTE_ADDR'];
	}

	return apply_filters( 'mdjm_get_user_ip', $ip_address );
} // mdjm_get_user_ip

/**
 * Display a Notice.
 *
 * Display a notice on the front end.
 *
 * @since	1.3
 * @param	int		$m		The notice message key.
 * @return	str		The HTML string for the notice
 */
function mdjm_display_notice( $m )	{	
	$message = mdjm_messages( $m );
	
	$notice = '<div class="mdjm-alert mdjm-alert-' . $message['class'] . '">';
	$notice .= ! empty( $message['title'] ) ? '<span><strong>' . $message['title'] . '</strong></span><br />' : '';
	$notice .= $message['message'] . '</div>';

	return apply_filters( 'mdjm_display_notice', $notice, $m );
} // mdjm_display_notice

/**
 * Display notice on front end.
 *
 * Check for super global $_GET['mdjm-message'] key and return message if set.
 *
 * @since	1.3
 * @param
 * @return	str		Out the relevant message to the browser.
 */
function mdjm_print_notices()	{
	if( ! isset( $_GET, $_GET['mdjm_message'] ) )	{
		return;
	}
	
	if ( isset( $_GET['event_id'] ) )	{
		
		$mdjm_event = new MDJM_Event( $_GET['event_id'] );
		
		echo mdjm_do_content_tags( mdjm_display_notice( $_GET['mdjm_message'] ), $mdjm_event->ID, $mdjm_event->client );
	
	} else	{
		echo mdjm_display_notice( $_GET['mdjm_message'] );
	}
} // mdjm_print_notices
add_action( 'mdjm_print_notices', 'mdjm_print_notices' );

/**
 * Messages.
 *
 * Messages that are used on the front end.
 *
 * @since	1.3
 * @param	str		$key		Array key of notice to retrieve. All by default.
 * @return	arr		Array containing message text, title and class.
 */
function mdjm_messages( $key )	{
	
	$messages = apply_filters(
		'mdjm_messages',
		array(
			'password_incorrect'	=> array(
				'class'		=> 'error',
				'title'		=> __( 'Error', 'mobile-dj-manager' ),
				'message'	=> __( 'Incorrect password.', 'mobile-dj-manager' )
			),
			'username_incorrect'	=> array(
				'class'		=> 'error',
				'title'		=> __( 'Error', 'mobile-dj-manager' ),
				'message'	=> __( 'Unknown username.', 'mobile-dj-manager' )
			),
			'missing_event'	=> array(
				'class'		=> 'error',
				'title'		=> __( 'Error', 'mobile-dj-manager' ),
				'message'	=> sprintf( __( 'We could not locate the details of your %s.', 'mobile-dj-manager' ), mdjm_get_label_singular( true ) )
			),
			'enquiry_accepted'	=> array(
				'class'		=> 'success',
				'title'		=> __( 'Thanks', 'mobile-dj-manager' ),
				'message'	=> __( 'You have accepted our quote and details of your contract are now on their way to you via email.', 'mobile-dj-manager' )
			),
			'enquiry_accept_fail'	=> array(
				'class'		=> 'error',
				'title'		=> __( 'Sorry', 'mobile-dj-manager' ),
				'message'	=> __( 'We could not process your request.', 'mobile-dj-manager' )
			),
			'contract_signed'	=> array(
				'class'		=> 'success',
				'title'		=> __( 'Done', 'mobile-dj-manager' ),
				'message'	=> sprintf( __( 'You have successfully signed your %s contract. Confirmation will be sent to you via email in the next few minutes.', 'mobile-dj-manager' ), mdjm_get_label_singular( true ) )
			),
			'contract_not_signed'	=> array(
				'class'		=> 'error',
				'title'		=> __( 'Error', 'mobile-dj-manager' ),
				'message'	=> sprintf( __( 'Unable to sign %s contract.', 'mobile-dj-manager' ), mdjm_get_label_singular( true ) )
			),
			'contract_data_missing'	=> array(
				'class'		=> 'error',
				'title'		=> __( 'Data missing', 'mobile-dj-manager' ),
				'message'	=> __( 'Please ensure all fields have been completed, you have accepted the terms, confirmed your identity and re-entered your password.', 'mobile-dj-manager' )
			),
			'playlist_added'	=> array(
				'class'		=> 'success',
				'title'		=> __( 'Done', 'mobile-dj-manager' ),
				'message'	=> __( 'Playlist entry added.', 'mobile-dj-manager' )
			),
			'playlist_not_added'	=> array(
				'class'		=> 'error',
				'title'		=> __( 'Error', 'mobile-dj-manager' ),
				'message'	=> __( 'Unable to add playlist entry.', 'mobile-dj-manager' )
			),
			'playlist_data_missing'	=> array(
				'class'		=> 'error',
				'title'		=> __( 'Data missing', 'mobile-dj-manager' ),
				'message'	=> __( 'Please provide at least a song and an artist for this entry.', 'mobile-dj-manager' )
			),
			'playlist_removed'	=> array(
				'class'		=> 'success',
				'title'		=> __( 'Done', 'mobile-dj-manager' ),
				'message'	=> __( 'Playlist entry removed.', 'mobile-dj-manager' )
			),
			'playlist_not_removed'	=> array(
				'class'		=> 'error',
				'title'		=> __( 'Error', 'mobile-dj-manager' ),
				'message'	  => __( 'Unable to remove playlist entry.', 'mobile-dj-manager' )
			),
			'playlist_not_selected'	=> array(
				'class'		=> 'error',
				'title'		=> __( 'Error', 'mobile-dj-manager' ),
				'message'	=> __( 'No playlist entry selected.', 'mobile-dj-manager' )
			),
			'playlist_guest_added'	=> array(
				'class'		=> 'success',
				'title'		=> __( 'Done', 'mobile-dj-manager' ),
				'message'	=> __( 'Playlist suggestion submitted.', 'mobile-dj-manager' )
			),
			'playlist_guest_error'	=> array(
				'class'		=> 'error',
				'title'		=> __( 'Error', 'mobile-dj-manager' ),
				'message'	=> __( 'Unable to add playlist suggestion.', 'mobile-dj-manager' )
			),
			'playlist_guest_data_missing'	=> array(
				'class'		=> 'error',
				'title'		=> __( 'Data missing', 'mobile-dj-manager' ),
				'message'	=> __( 'Please provide at least a song and an artist for this entry.', 'mobile-dj-manager' )
			),
			'available'	=> array(
				'class'        => 'mdjm_available',
				'message'      => mdjm_get_option( 'availability_check_pass_text', __( 'The date you selected is available.', 'mobile-dj-manager' ) )
			),
			'not_available'	=> array(
				'class'		=> 'mdjm_notavailable',
				'message'	=>  mdjm_get_option( 'availability_check_fail_text', __( "We're not available on the selected date.", 'mobile-dj-manager' ) )
			),
			'missing_date'	=> array(
				'class'		=> 'error',
				'title'		=> __( 'Ooops', 'mobile-dj-manager' ),
				'message'	=> __( 'You forgot to enter a date.', 'mobile-dj-manager' )
			),
			'missing_event'   => array(
				'class'		=> 'error',
				'title'		=> __( 'Sorry', 'mobile-dj-manager' ),
				'message'	=> sprintf( __( 'We seem to be missing the %s details.', 'mobile-dj-manager' ), mdjm_get_label_singular( true ) )
			),
			'password_error'   => array(
				'class'		=> 'error',
				'title'		=> __( 'Password Error', 'mobile-dj-manager' ),
				'message'	=> __( 'An incorrect password was entered', 'mobile-dj-manager' )
			),
			'nonce_fail'   => array(
				'class'		=> 'error',
				'title'		=> __( 'Error', 'mobile-dj-manager' ),
				'message'	=> __( 'Security verification failed.', 'mobile-dj-manager' )
			),
			'payment_success' => array(
				'class'   => 'success',
				'title'   => __( 'Thank you', 'mobile-dj-manager' ),
				'message' => __( 'Your payment has completed successfully', 'mobile-dj-manager' )
			),
			'payment_failed' => array(
				'class'   => 'error',
				'title'   => __( 'There was an error processing your payment', 'mobile-dj-manager' ),
				'message' => __( 'To process your payment again, please follow the steps below', 'mobile-dj-manager' )
			)
		)
	);
	
	// Return a single message
	if ( isset( $key ) && array_key_exists( $key, $messages ) )	{
		return $messages[ $key ];
	} elseif ( isset( $key ) && ! array_key_exists( $key, $messages ) )	{
		return;
	}
	
	// Return all messages
	return $messages;
} // mdjm_messages

/**
 * Validate the form honeypot to protect against bots.
 *
 * @since	1.4.7
 * @param	arr		$data	Form post data
 * @return	void
 */
function mdjm_do_honeypot_check( $data )	{
	if ( ! empty( $data['mdjm_honeypot'] ) )	{
		wp_die( __( "Ha! I don't think so little honey bee. No bots allowed in this Honey Pot!", 'mobile-dj-manager' ) );
	}
	
	return;
} // mdjm_do_honeypot_check

/**
 * Check if the upgrade routine has been run for a specific action
 *
 * @since  2.3
 * @param  string $upgrade_action The upgrade action to check completion for
 * @return bool                   If the action has been added to the copmleted actions array
 */
function mdjm_has_upgrade_completed( $upgrade_action = '' )	{

	if ( empty( $upgrade_action ) )	{
		return false;
	}

	$completed_upgrades = mdjm_get_completed_upgrades();

	return in_array( $upgrade_action, $completed_upgrades );

} // mdjm_has_upgrade_completed

/**
 * Retrieve the array of completed upgrade actions.
 *
 * @since 	1.4
 * @return	arr		The array of completed upgrades.
 */
function mdjm_get_completed_upgrades()	{

	$completed_upgrades = get_option( 'mdjm_completed_upgrades' );

	if ( false === $completed_upgrades ) {
		$completed_upgrades = array();
	}

	return $completed_upgrades;

} // mdjm_get_completed_upgrades
