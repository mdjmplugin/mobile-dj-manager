<?php
/**
 * Contains all transaction related functions
 *
 * @package		MDJM
 * @subpackage	Transactions
 * @since		1.3
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) )
	exit;

/**
 * Get the label used for deposits.
 *
 * @since	1.3
 * @param
 * @return	str		The label set for deposits
 */
function mdjm_get_deposit_label() {
	
	$term = get_term_by( 'slug', 'mdjm-deposit-payments', 'transaction-types' );
	
	if ( empty( $term ) )	{
		return __( 'Deposit', 'mobile-dj-manager' );
	}
	
	return $term->name;
	
} // mdjm_get_deposit_label

/**
 * Get the label used for balances.
 *
 * @since	1.3
 * @param
 * @return	str		The label set for balances
 */
function mdjm_get_balance_label() {
	
	$term = get_term_by( 'slug', 'mdjm-balance-payments', 'transaction-types' );
	
	if ( empty( $term ) )	{
		return __( 'Balance', 'mobile-dj-manager' );
	}
	
	return $term->name;
	
} // mdjm_get_balance_label

/**
 * Get the label used for merchant fees.
 *
 * @since	1.3
 * @param
 * @return	str		The label set for merchant fees
 */
function mdjm_get_merchant_fees_label() {
	
	$term = get_term_by( 'slug', 'mdjm-merchant-fees', 'transaction-types' );
	
	if ( empty( $term ) )	{
		return __( 'Term not found', 'mobile-dj-manager' );
	}
	
	return $term->name;
	
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
 * Get the label used for employee wages.
 *
 * @since	1.3
 * @param
 * @return	str		The label set for merchant fees
 */
function mdjm_get_employee_wages_label() {
	
	$term = get_term_by( 'slug', 'mdjm-employee-wages', 'transaction-types' );
	
	if ( empty( $term ) )	{
		return __( 'Term not found', 'mobile-dj-manager' );
	}
	
	return $term->name;
	
} // mdjm_get_employee_wages_label

/**
 * Get the category ID for the term.
 *
 * @since	1.3
 * @param	str		$slug	The slug of the term for which we want the ID
 * @return	int		The term ID
 */
function mdjm_get_txn_cat_id( $field = 'slug', $slug ) {
	
	$term = get_term_by( $field, $slug, 'transaction-types' );
	
	if ( empty( $term ) )	{
		return __( 'Term not found', 'mobile-dj-manager' );
	}
	
	$id = $term->term_id;
	(int)$id;
	
	return $id;
	
} // mdjm_get_txn_cat_id

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
function mdjm_get_currency_name( $code = 'GBP' ) {
	$currencies = mdjm_get_currencies();
	$name       = isset( $currencies[ $code ] ) ? $currencies[ $code ] : $code;
	
	return apply_filters( 'mdjm_currency_name', $name );
} // mdjm_get_currency_name

/**
 * Retrieve a transaction.
 *
 * @since	1.3
 * @param	int		$txn_id	The transaction ID.
 * @return	obj		$txn	The transaction WP_Post object
 */
function mdjm_get_txn( $txn_id )	{
	return mdjm_get_txn_by_id( $txn_id );
} // mdjm_get_txn

/**
 * Retrieve a transaction by ID.
 *
 * @param	int		$txn_id		The WP post ID for the transaction.
 *
 * @return	mixed	$txn		WP_Query object or false.
 */
function mdjm_get_txn_by_id( $txn_id )	{
	$txn = new MDJM_Txn( $txn_id );
	
	return ( !empty( $txn->ID ) ? $txn : false );
} // mdjm_get_txn_by_id

/**
 * Retrieve the transactions.
 *
 * @since	1.3
 * @param	arr		$args			Array of possible arguments. See @get_posts.
 * @return	mixed	$txns			False if no txns, otherwise an object array of all events.
 */
function mdjm_get_txns( $args = array() )	{
		
	$defaults = array(
		'post_type'         => 'mdjm-transaction',
		'post_status'       => 'any',
		'posts_per_page'	=> -1,
	);
		
	$args = wp_parse_args( $args, $defaults );
		
	$txns = get_posts( $args );
	
	// Return the results
	if ( $txns )	{
		return $txns;
	} else	{
		return false;
	}
	
} // mdjm_get_txns

/**
 * Return the type of transaction.
 *
 * @since	1.3
 * @param	int		$txn_id		ID of the current transaction.
 * @return	str		Transaction type.
 */
function mdjm_get_txn_type( $txn_id )	{
	$txn = new MDJM_Txn( $txn_id );
	
	// Return the label for the status
	return $txn->get_type();
} // mdjm_get_txn_type

/**
 * Return all possible types of transaction.
 *
 * @since	1.3
 * @param
 * @return	obj		Transaction type term objects.
 */
function mdjm_get_txn_types( $hide_empty = false )	{
	
	$txn_types = get_categories( array(
		'type'		=> 'mdjm-transaction',
		'taxonomy'	=> 'transaction-types',
		'order_by'	=> 'name',
		'order'	   => 'ASC',
		'hide_empty'  => $hide_empty,
	) );
	
	return $txn_types;

} // mdjm_get_txn_types

/**
 * Set the transaction type for the transaction.
 *
 * @since	1.3
 * @param	int			$txn_id		Transaction ID.
 * @param	int|arr		$type		The term ID of the category to set for the transaction.
 * @return	bool		True on success, or false.
 */
function mdjm_set_txn_type( $txn_id, $type )	{
	
	if ( ! is_array( $type ) )	{
		$type = array( $type );
	}
	
	$type = array_map( 'intval', $type );
	$type = array_unique( $type );
	
	(int)$txn_id;
	
	$set_txn_terms = wp_set_object_terms( $txn_id, $type, 'transaction-types', false );
	
	if( is_wp_error( $set_txn_terms ) )	{
		MDJM()->debug->log_it( sprintf( 'Unable to assign term ID %d to Transaction %d: %s', $type, $txn_id, $set_txn_terms->get_error_message() ), true );
	}
	
	return;

} // mdjm_set_event_type

/*
 * Retrieve all possible transaction sources
 *
 * @since	1.3
 * @param		
 * @return	arr		$txn_src	Transaction sources
 */
function mdjm_get_txn_source()	{
	
	$src = array();
	
	$src = mdjm_get_option( 'payment_sources' );
	
	$txn_src = explode( "\r\n", $src );
		
	asort( $txn_src );
	
	return $txn_src;

} // mdjm_get_txn_source

/**
 * Returns the date for a transaction in short format.
 *
 * @since	1.3
 * @param	int		$txn_id		The transaction ID.
 * @return	str					The date of the transaction.
 */
function mdjm_get_txn_date( $txn_id='' )	{
	if( empty( $txn_id ) )	{
		return false;
	}

	$txn = new MDJM_Txn( $txn_id );
	
	return mdjm_format_short_date( $txn->get_date() );
} // mdjm_get_txn_date

/**
 * Retrieve the transaction price.
 *
 * @since	1.3
 * @param	int		$txn_id		The transaction ID
 * @return	str		The price of the transaction.
 */
function mdjm_get_txn_price( $txn_id )	{
	$mdjm_txn = new MDJM_Txn( $txn_id );
	
	return $mdjm_txn->price;
} // mdjm_get_txn_price

/**
 * Retrieve the transaction recipient ID.
 *
 * @since	1.3
 * @param	int		$txn_id		The transaction ID
 * @return	int		The recipient of the transaction.
 */
function mdjm_get_txn_recipient_id( $txn_id )	{
	$mdjm_txn = new MDJM_Txn( $txn_id );
	
	return $mdjm_txn->recipient_id;
} // mdjm_get_txn_recipient_id

/**
 * Retrieve the transaction recipient name.
 *
 * @since	1.3
 * @param	int		$txn_id		The transaction ID
 * @return	str		The recipient of the transaction.
 */
function mdjm_get_txn_recipient_name( $txn_id )	{
	$recipient_id = mdjm_get_txn_recipient_id( $txn_id );
	$recipient    = __( 'N/A', 'mobile-dj-manager' );
	
	if ( ! empty( $recipient_id ) )	{
				
		if ( is_numeric( $recipient_id ) )	{
			
			$user = get_userdata( $recipient_id );
			
			$recipient = $user->display_name;
			
		} else	{
			$recipient = $recipient_id;
		}
		
	}

	return $recipient;
} // mdjm_get_txn_recipient_name

/**
 * Calculate the total wages payable for an event.
 *
 * @since	1.3
 * @param	int		$event_id		The event ID
 * @return	str		Total wages amount for the event.
 */
function mdjm_get_event_total_wages( $event_id )	{
	
	$event = mdjm_get_event( $event_id );
	
	return $event->get_wages_total();
	
} // mdjm_get_event_total_wages

/**
 * Registers a new transaction or updates an existing.
 *
 * @since	1.3
 * @param	arr			$data		Array of transaction post data.
 * @return	int|bool				The new transaction ID or false on failure.
 */
function mdjm_add_txn( $data )	{
	
	$post_defaults = apply_filters( 
		'mdjm_add_txn_defaults',
		array(
			'ID'			=> isset ( $data['invoice'] ) ? $data['invoice'] : '',
			'post_title'	=> isset ( $data['invoice'] ) ? mdjm_get_option( 'event_prefix' ) . $data['invoice'] : '',
			'post_status' 	=> 'mdjm-income',
			'post_date'		=> date( 'Y-m-d H:i:s', current_time( 'timestamp' ) ),
			'edit_date'		=> true,
			'post_author'	=> isset ( $data['item_number'] ) ? get_post_meta( $data['item_number'], '_mdjm_event_client', true ) : 1,
			'post_type'		=> 'mdjm-transaction',
			'post_category'	=> ( !empty( $data['txn_type'] ) ? array( $data['txn_type'] ) : '' ),
			'post_parent'	=> isset( $data['event_id'] ) ? $data['event_id'] : '',
			'post_modified'	=> date( 'Y-m-d H:i:s', current_time( 'timestamp' ) )
		)
	);
	
	$txn_data = wp_parse_args( $data, $post_defaults );
	
	do_action( 'mdjm_pre_add_txn', $txn_data );
	
	$txn_id = wp_insert_post( $txn_data );
	
	// Failed
	if ( $txn_id == 0 )	{
		return false;
	}
	
	// Set the transaction type (category)
	if ( ! empty( $txn_data['post_category'] ) )	{
		wp_set_post_terms( $txn_id, $txn_data['post_category'], 'transaction-types' );
	}
	
	do_action( 'mdjm_post_add_txn', $txn_id, $txn_data );
	
	return $txn_id;
	
} // mdjm_add_txn

/**
 * Add or Update transaction meta data.
 *
 * We don't currently delete empty meta keys or values, instead we update with an empty value
 * if an empty value is passed to the function.
 *
 * @since	1.3
 * @param	int		$txn_id		The transaction ID.
 * @param	arr		$data		Array of transaction post meta data.
 * @return	void
 */
function mdjm_update_txn_meta( $txn_id, $data )	{
	
	$meta = get_post_meta( $txn_id, '_mdjm_txn_data', true );
	
	foreach( $data as $key => $value )	{
		
		if( $key == 'mdjm_nonce' || $key == 'mdjm_action' ) {
			continue;
		}
		
		// For backwards comaptibility
		update_post_meta( $txn_id, $key, $value );
		
		$meta[ $key ] = $value;
		
	}
	
	$update = update_post_meta( $txn_id, '_mdjm_txn_data', $meta );
	
	return $update;
		
} // mdjm_update_txn_meta

/**
 * Mark event employees salaries as paid.
 *
 * @since	1.3
 * @param	int		$event_id		The event ID.
 * @param	int		$_employee_id	User ID of employee to pay.
 * @param	str		$amount			Amount to pay.
 * @return	mixed	Array of 'success' and 'failed' payments or if individual employee, true or false.
 */
function mdjm_pay_event_employees( $event_id, $_employee_id = 0, $amount = 0 )	{
	
	if ( ! mdjm_get_option( 'enable_employee_payments' ) )	{
		return;
	}
	
	$mdjm_event = mdjm_get_event( $event_id );
	
	if ( ! $mdjm_event )	{
		return false;
	}
	
	$employees = $mdjm_event->get_all_employees();
	
	if ( ! $employees )	{
		return false;
	}

	do_action( 'mdjm_pre_pay_event_employees', $event_id, $_employee_id, $mdjm_event );
	
	foreach( $employees as $employee_id => $employee_data )	{
		
		if ( $employee_data['payment_status'] == 'paid' )	{
			MDJM()->debug->log_it( sprintf( 'Skipping payment to %s. Employee already paid.', mdjm_get_employee_display_name( $employee_id ) ) );
		}
		
		$mdjm_txn = new MDJM_Txn( $employee_data['txn_id'] );
		
		if ( ! $mdjm_txn )	{
			return false;
		}
		
		MDJM()->debug->log_it( sprintf( 'Starting payment to %s for %s',
			mdjm_get_employee_display_name( $employee_id ), mdjm_currency_filter( mdjm_format_amount( $mdjm_txn->price ) ) ), true );
			
		if ( ! mdjm_set_employee_paid( $employee_id, $event_id, $mdjm_txn->ID ) )	{
			MDJM()->debug->log_it( sprintf( 'Payment to %s failed', mdjm_get_employee_display_name( $employee_id ) ) );
			
			if ( ! empty( $_employee_id ) )	{
				$return = false;
			} else	{
				$return['failed'] = $employee_id;
			}
			
		} else	{
			MDJM()->debug->log_it( sprintf( '%s successfully paid %s',
				mdjm_get_employee_display_name( $employee_id ), mdjm_currency_filter( mdjm_format_amount( $mdjm_txn->price ) ) ) );
				
			mdjm_update_txn_meta( $mdjm_txn->ID, array( '_mdjm_txn_status' => 'Completed' ) );
			
			if ( ! empty( $_employee_id ) )	{
				$return = true;
			} else	{
				$return['success'] = $employee_id;
			}
			
		}
		
	}
	
	do_action( 'mdjm_post_pay_event_employees', $event_id, $_employee_id, $mdjm_event, $mdjm_txn->ID );
	
	return $return;
	
} // mdjm_pay_event_employees

/**
 * Remove the post save action whilst adding or updating transactions.
 *
 * @since	1.3
 * @param
 * @return	void
 */
function mdjm_remove_txn_save_post_action()	{
	remove_action( 'save_post_mdjm-transaction', 'mdjm_save_txn_post', 10, 3 );
} // mdjm_remove_txn_save_post_action
add_action( 'mdjm_pre_add_txn', 'mdjm_remove_txn_save_post_action' );
add_action( 'mdjm_pre_update_txn', 'mdjm_remove_txn_save_post_action' );

/**
 * Add the post save action after adding or updating transactions.
 *
 * @since	1.3
 * @param
 * @return	void
 */
function mdjm_add_txn_save_post_action()	{
	add_action( 'save_post_mdjm-transaction', 'mdjm_save_txn_post', 10, 3 );
} // mdjm_add_txn_save_post_action
add_action( 'mdjm_post_add_txn', 'mdjm_add_txn_save_post_action' );
add_action( 'mdjm_post_update_txn', 'mdjm_add_txn_save_post_action' );
