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
 * Return all registered currencies.
 *
 * @since	1.3
 * @param
 * @return	arr		Array of MDJM registered currencies
 */
function mdjm_get_currencies()	{
	return apply_filters( 'mdjm_currencies',
				array(
					'GBP'  => __( 'Pounds Sterling (&pound;)', 'mobile-dj-manager' ),
					'USD'  => __( 'US Dollars (&#36;)', 'mobile-dj-manager' ),
					'EUR'  => __( 'Euros (&euro;)', 'mobile-dj-manager' ),
					'AUD'  => __( 'Australian Dollars (&#36;)', 'mobile-dj-manager' ),
					'BRL'  => __( 'Brazilian Real (R&#36;)', 'mobile-dj-manager' ),
					'CAD'  => __( 'Canadian Dollars (&#36;)', 'mobile-dj-manager' ),
					'CZK'  => __( 'Czech Koruna', 'mobile-dj-manager' ),
					'DKK'  => __( 'Danish Krone', 'mobile-dj-manager' ),
					'HKD'  => __( 'Hong Kong Dollar (&#36;)', 'mobile-dj-manager' ),
					'HUF'  => __( 'Hungarian Forint', 'mobile-dj-manager' ),
					'ILS'  => __( 'Israeli Shekel (&#8362;)', 'mobile-dj-manager' ),
					'JPY'  => __( 'Japanese Yen (&yen;)', 'mobile-dj-manager' ),
					'MYR'  => __( 'Malaysian Ringgits', 'mobile-dj-manager' ),
					'MXN'  => __( 'Mexican Peso (&#36;)', 'mobile-dj-manager' ),
					'NZD'  => __( 'New Zealand Dollar (&#36;)', 'mobile-dj-manager' ),
					'NOK'  => __( 'Norwegian Krone', 'mobile-dj-manager' ),
					'PHP'  => __( 'Philippine Pesos', 'mobile-dj-manager' ),
					'PLN'  => __( 'Polish Zloty', 'mobile-dj-manager' ),
					'SGD'  => __( 'Singapore Dollar (&#36;)', 'mobile-dj-manager' ),
					'ZAR'  => __( 'South African Rand', 'mobile-dj-manager' ),
					'SEK'  => __( 'Swedish Krona', 'mobile-dj-manager' ),
					'CHF'  => __( 'Swiss Franc', 'mobile-dj-manager' ),
					'TWD'  => __( 'Taiwan New Dollars', 'mobile-dj-manager' ),
					'THB'  => __( 'Thai Baht (&#3647;)', 'mobile-dj-manager' ),
					'INR'  => __( 'Indian Rupee (&#8377;)', 'mobile-dj-manager' ),
					'TRY'  => __( 'Turkish Lira (&#8378;)', 'mobile-dj-manager' ),
					'RIAL' => __( 'Iranian Rial (&#65020;)', 'mobile-dj-manager' ),
					'RUB'  => __( 'Russian Rubles', 'mobile-dj-manager' )
				)
			);
} // mdjm_get_currencies

/**
 * Get the set currency
 *
 * @since 1.3
 * @return string The currency code
 */
function mdjm_get_currency() {
	$currency = mdjm_get_option( 'currency', 'GBP' );
	return apply_filters( 'mdjm_currency', $currency );
} // mdjm_get_currency

/**
 * Given a currency determine the symbol to use. If no currency given, site default is used.
 * If no symbol is determine, the currency string is returned.
 *
 * @since 	1.3
 * @param	str		$currency	The currency string
 * @return	str		The symbol to use for the currency
 */
function mdjm_currency_symbol( $currency = '' ) {
	if ( empty( $currency ) ) {
		$currency = mdjm_get_currency();
	}

	switch ( $currency ) :
		case "GBP" :
			$symbol = '&pound;';
			break;
		case "BRL" :
			$symbol = 'R&#36;';
			break;
		case "EUR" :
			$symbol = '&euro;';
			break;
		case "USD" :
		case "AUD" :
		case "NZD" :
		case "CAD" :
		case "HKD" :
		case "MXN" :
		case "SGD" :
			$symbol = '&#36;';
			break;
		case "JPY" :
			$symbol = '&yen;';
			break;
		default :
			$symbol = $currency;
			break;
	endswitch;

	return apply_filters( 'mdjm_currency_symbol', $symbol, $currency );
} // mdjm_currency_symbol

/**
 * Get the name of a currency
 *
 * @since	1.3
 * @param	str		$code	The currency code
 * @return	str		The currency's name
 */
function mdjm_get_currency_name( $code = 'USD' ) {
	$currencies = mdjm_get_currencies();
	$name       = isset( $currencies[ $code ] ) ? $currencies[ $code ] : $code;
	
	return apply_filters( 'mdjm_currency_name', $name );
} // mdjm_get_currency_name

/**
 * Get the label used for deposits.
 *
 * @since	1.3
 * @param
 * @return	str		The label set for deposits
 */
function mdjm_get_deposit_label() {
	return mdjm_get_option( 'deposit_label', __( 'Deposit', 'mobile-dj-manager' ) );
} // mdjm_get_deposit_label

/**
 * Get the label used for balances.
 *
 * @since	1.3
 * @param
 * @return	str		The label set for balances
 */
function mdjm_get_balance_label() {
	return mdjm_get_option( 'balance_label', __( 'Balance', 'mobile-dj-manager' ) );
} // mdjm_get_balance_label

/**
 * Get the label used for custom payment amounts.
 *
 * @since	1.3
 * @param
 * @return	str		The label set for the other_amount_label option
 */
function mdjm_get_other_amount_label() {
	return mdjm_get_option( 'other_amount_label', __( 'Other Amount', 'mobile-dj-manager' ) );
} // mdjm_get_other_amount_label

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