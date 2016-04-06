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

function mdjm_format_datepicker_date()	{
	$date_format = mdjm_get_option( 'short_date_format', 'd/m/Y' );
	
	$search = array( 'd', 'm', 'Y' );
	$replace = array( 'dd', 'mm', 'yy' );
	
	$date_format = str_replace( $search, $replace, $date_format );
			
	return apply_filters( 'mdjm_format_datepicker_date', $date_format );
} // mdjm_format_datepicker_date

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
	
	?>
    <script type="text/javascript">
	jQuery(document).ready( function($)	{
		$(".<?php echo $args['class']; ?>").datepicker({
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
	
	$notice = '<div class="mdjm-' . $message['class'] . '"><span>' . $message['title'] . ': </span>' . $message['message'] . '</div>';

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
			'missing_event'	=> array(
				'class'		=> 'error',
				'title'		=> __( 'Error', 'mobile-dj-manager' ),
				'message'	=> __( 'We could not locate the details of your event.', 'mobile-dj-manager' )
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
				'message'	=> __( 'You have successfully signed your event contract. Confirmation will be sent to you via email in the next few minutes.', 'mobile-dj-manager' )
			),
			'contract_not_signed'	=> array(
				'class'		=> 'error',
				'title'		=> __( 'Error', 'mobile-dj-manager' ),
				'message'	=> __( 'Unable to sign event contract.', 'mobile-dj-manager' )
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
				'class'		=> 'mdjm_available',
				'title'		=> __( 'Good News', 'mobile-dj-manager' ),
				'message'	=> __( 'The date you selected is available.', 'mobile-dj-manager' )
			),
			'not_available'	=> array(
				'class'		=> 'mdjm_notavailable',
				'title'		=> __( 'Sorry', 'mobile-dj-manager' ),
				'message'	=> __( "We're not available on the selected date.", 'mobile-dj-manager' )
			),
			'missing_date'	=> array(
				'class'		=> 'error',
				'title'		=> __( 'Ooops', 'mobile-dj-manager' ),
				'message'	=> __( 'You forgot to enter a date.', 'mobile-dj-manager' )
			),
			'missing_event'   => array(
				'class'		=> 'error',
				'title'		=> __( 'Sorry', 'mobile-dj-manager' ),
				'message'	=> __( 'We seem to be missing the event details.', 'mobile-dj-manager' )
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
			)
		)
	);
	
	// Return a single message
	if( isset( $key ) && array_key_exists( $key, $messages ) )	{
		return $messages[ $key ];
	}
	
	// Return all messages
	return $messages;
} // mdjm_messages