<?php


class MDJM_Events {
	public function __construct() {} // __construct

	/*
	* Event functions
	*/
	/**
	List employee bookings by given date
	 * Only checks for the "active" statuses
	 *
	 * @param int $dj User ID of DJ to check, default to all.
	 * str $date The date to check (Y-m-d)
	 * @return
	 */
	public function employee_bookings( $dj = '', $date = '' ) {
		global $mdjm_settings;

		$date = ! empty( $date ) ? $date : date( 'Y-m-d' );
		$dj   = ! empty( $dj ) ? $dj : mdjm_get_employees();

		if ( is_array( $dj ) ) {
			foreach ( $dj as $employee ) {
				$user[] = $employee->ID;
			}
		} else {
			$user[] = $dj;
		}

		$status = $mdjm_settings['availability']['availability_status'];

		$args = array(
			'orderby'     => '_mdjm_event_dj',
			'order'       => 'ASC',
			'post_status' => $status,
			'date'        => $date,
		);

		$events = mdjm_get_employee_events( $user, '_mdjm_event_dj', 'ASC', $status, $date );

		return $events;
	}

	/**
	 * Get the current events key details and place them into an array
	 *
	 * @param int $post_id The event's ID.
	 * @return obj $eventinfo The event meta information
	 */
	public function event_detail( $post_id ) {
		global $mdjm;

		if ( empty( $post_id ) || ! is_string( get_post_status( $post_id ) ) ) {
			return;
		}

		$event_stati = mdjm_all_event_status();

		$name            = get_post_meta( $post_id, '_mdjm_event_name', true );
		$date            = get_post_meta( $post_id, '_mdjm_event_date', true );
		$end_date        = get_post_meta( $post_id, '_mdjm_event_end_date', true );
		$client          = get_post_meta( $post_id, '_mdjm_event_client', true );
		$dj              = get_post_meta( $post_id, '_mdjm_event_dj', true );
		$dj_wage         = get_post_meta( $post_id, '_mdjm_event_dj_wage', true );
		$cost            = get_post_meta( $post_id, '_mdjm_event_cost', true );
		$deposit         = get_post_meta( $post_id, '_mdjm_event_deposit', true );
		$deposit_status  = get_post_meta( $post_id, '_mdjm_event_deposit_status', true );
		$paid            = MDJM()->txns->get_transactions( $post_id, 'mdjm-income' );
		$balance_status  = get_post_meta( $post_id, '_mdjm_event_balance_status', true );
		$start           = get_post_meta( $post_id, '_mdjm_event_start', true );
		$finish          = get_post_meta( $post_id, '_mdjm_event_finish', true );
		$status          = ! empty( $event_stati[ get_post_status( $post_id ) ] ) ? $event_stati[ get_post_status( $post_id ) ] : '';
		$setup_date      = get_post_meta( $post_id, '_mdjm_event_djsetup', true );
		$setup_time      = get_post_meta( $post_id, '_mdjm_event_djsetup_time', true );
		$contract        = get_post_meta( $post_id, '_mdjm_event_contract', true );
		$contract_date   = get_post_meta( $post_id, '_mdjm_event_contract_approved', true );
		$signed_contract = get_post_meta( $post_id, '_mdjm_event_signed_contract', true );
		$notes           = get_post_meta( $post_id, '_mdjm_event_notes', true );
		$dj_notes        = get_post_meta( $post_id, '_mdjm_event_dj_notes', true );
		$admin_notes     = get_post_meta( $post_id, '_mdjm_event_admin_notes', true );
		$package         = get_post_meta( $post_id, '_mdjm_event_package', true );
		$addons          = get_post_meta( $post_id, '_mdjm_event_addons', true );
		$online_quote    = get_post_meta( $post_id, '_mdjm_online_quote', true );
		$guest_playlist  = get_post_meta( $post_id, '_mdjm_event_playlist_access', true );

		$eventinfo = array(
			'name'            => ( ! empty( $name ) ? $name : '' ),
			'date'            => ( ! empty( $date ) && is_int( strtotime( $date ) ) ?
				strtotime( $date ) : __( 'Not Specified', 'mobile-dj-manager' ) ),
			'end_date'        => ( ! empty( $end_date ) && is_int( strtotime( $end_date ) ) ?
				strtotime( $end_date ) : __( 'Not Specified', 'mobile-dj-manager' ) ),
			'client'          => ( ! empty( $client ) ? get_userdata( $client ) : '' ),
			'dj'              => ( ! empty( $dj ) ? get_userdata( $dj ) : __( 'Not Assigned', 'mobile-dj-manager' ) ),
			'dj_wage'         => ( ! empty( $dj_wage ) ? mdjm_currency_filter( mdjm_sanitize_amount( $dj_wage ) ) : __( 'Not Specified', 'mobile-dj-manager' ) ),
			'start'           => ( ! empty( $start ) ? date( mdjm_get_option( 'time_format' ), strtotime( $start ) ) : __( 'Not Specified', 'mobile-dj-manager' ) ),
			'finish'          => ( ! empty( $finish ) ? date( mdjm_get_option( 'time_format' ), strtotime( $finish ) ) : __( 'Not Specified', 'mobile-dj-manager' ) ),
			'status'          => ( ! empty( $status ) ? $status : '' ),
			'setup_date'      => ( ! empty( $setup_date ) ? strtotime( $setup_date ) : __( 'Not Specified', 'mobile-dj-manager' ) ),
			'setup_time'      => ( ! empty( $setup_time ) ? date( mdjm_get_option( 'time_format' ), strtotime( $setup_time ) ) : __( 'Not Specified', 'mobile-dj-manager' ) ),
			'cost'            => ( ! empty( $cost ) ? mdjm_currency_filter( mdjm_sanitize_amount( $cost ) ) : __( 'Not Specified', 'mobile-dj-manager' ) ),
			'deposit'         => ( ! empty( $deposit ) ? mdjm_currency_filter( mdjm_sanitize_amount( $deposit ) ) : '0.00' ),
			'balance'         => ( ! empty( $paid ) && '0.00' !== $paid && ! empty( $cost ) ?
				mdjm_currency_filter( mdjm_sanitize_amount( ( $cost - $paid ) ) ) : mdjm_currency_filter( mdjm_sanitize_amount( $cost ) ) ),
			'deposit_status'  => ( ! empty( $deposit_status ) ? $deposit_status : __( 'Due', 'mobile-dj-manager' ) ),
			'balance_status'  => ( ! empty( $balance_status ) ? $balance_status : __( 'Due', 'mobile-dj-manager' ) ),
			'payment_history' => MDJM()->txns->list_event_transactions( $post_id ),
			'type'            => mdjm_get_event_type( $post_id, true ),
			'online_quote'    => mdjm_get_option( 'online_enquiry', false ) && ! empty( $online_quote ) ? $online_quote : '',
			'contract'        => ( ! empty( $contract ) ? $contract : '' ),
			'contract_date'   => ( ! empty( $contract_date ) ? date( mdjm_get_option( 'short_date_format' ), strtotime( $contract_date ) ) :
				date( mdjm_get_option( 'short_date_format' ) ) ),

			'signed_contract' => ( ! empty( $signed_contract ) ? $signed_contract : '' ),
			'notes'           => ( ! empty( $notes ) ? $notes : '' ),
			'dj_notes'        => ( ! empty( $dj_notes ) ? $dj_notes : '' ),
			'admin_notes'     => ( ! empty( $admin_notes ) ? $admin_notes : '' ),
			'package'         => ( ! empty( $package ) ? $package : '' ),
			'addons'          => ( ! empty( $addons ) ? implode( "\n", $addons ) : '' ),
			'guest_playlist'  => ( ! empty( $guest_playlist ) ?
				mdjm_get_formatted_url( mdjm_get_option( 'playlist_page' ) ) . 'mdjmeventid=' . $guest_playlist : '' ),
		);

		// Allow the $eventinfo array to be filtered.
		$eventinfo = apply_filters( 'mdjm_event_info', $eventinfo );

		return $eventinfo;
	} // event_detail

	/**
	 * Mdjm_get_venue_details
	 * Retrieve all venue meta
	 */
	function mdjm_get_venue_details( $venue_post_id = '', $event_id = '' ) {
		/*
		* @param venue_post_id
		* @return: $venue_meta => array
		*/

		if ( empty( $venue_post_id ) && empty( $event_id ) ) {
			return;
		}

		/* -- No post means we use the event database */
		if ( false === get_post_status( $venue_post_id ) || ! is_numeric( $venue_post_id ) ) {
			$event_details = mdjm_get_event_by_id( $event_id );
			if ( ! $event_details ) {
				return;
			}

			$venue_details['name']           = get_post_meta( $event_id, '_mdjm_event_venue_name', true );
			$venue_details['venue_contact']  = get_post_meta( $event_id, '_mdjm_event_venue_contact', true );
			$venue_details['venue_phone']    = get_post_meta( $event_id, '_mdjm_event_venue_phone', true );
			$venue_details['venue_email']    = get_post_meta( $event_id, '_mdjm_event_venue_email', true );
			$venue_details['venue_address1'] = get_post_meta( $event_id, '_mdjm_event_venue_address1', true );
			$venue_details['venue_address2'] = get_post_meta( $event_id, '_mdjm_event_venue_address2', true );
			$venue_details['venue_town']     = get_post_meta( $event_id, '_mdjm_event_venue_town', true );
			$venue_details['venue_county']   = get_post_meta( $event_id, '_mdjm_event_venue_county', true );
			$venue_details['venue_postcode'] = get_post_meta( $event_id, '_mdjm_event_venue_postcode', true );
		}
		/* -- The venue post exists -- */
		else {
			$venue_keys = array(
				'_venue_contact',
				'_venue_phone',
				'_venue_email',
				'_venue_address1',
				'_venue_address2',
				'_venue_town',
				'_venue_county',
				'_venue_postcode',
				'_venue_information',
			);
			$venue_name = get_the_title( $venue_post_id );
			$all_meta   = get_post_meta( $venue_post_id );
			if ( empty( $all_meta ) ) {
				return;
			}

			$venue_details['name'] = ( ! empty( $venue_name ) ? $venue_name : '' );
			foreach ( $venue_keys as $key ) {
				$venue_details[ substr( $key, 1 ) ] = ! empty( $all_meta[ $key ][0] ) ? $all_meta[ $key ][0] : '';
			}

			// Venue details.
			$details = wp_get_object_terms( $venue_post_id, 'venue-details' );

			foreach ( $details as $detail ) {
				$venue_details['details'][] = $detail->name;
			}
		}
		// Full address.
		if ( ! empty( $venue_details['venue_address1'] ) ) {
			$venue_details['full_address'][] = $venue_details['venue_address1'];
		}

		if ( ! empty( $venue_details['venue_address2'] ) ) {
			$venue_details['full_address'][] = $venue_details['venue_address2'];
		}

		if ( ! empty( $venue_details['venue_town'] ) ) {
			$venue_details['full_address'][] = $venue_details['venue_town'];
		}

		if ( ! empty( $venue_details['venue_county'] ) ) {
			$venue_details['full_address'][] = $venue_details['venue_county'];
		}

		if ( ! empty( $venue_details['venue_postcode'] ) ) {
			$venue_details['full_address'][] = $venue_details['venue_postcode'];
		}

		if ( ! empty( $venue_details['venue_contact'] ) ) {
			$venue_details['full_address'][] = $venue_details['venue_contact'];
		}

		if ( ! empty( $venue_details['venue_phone'] ) ) {
			$venue_details['full_address'][] = $venue_details['venue_phone'];
		}

		if ( ! empty( $venue_details['venue_email'] ) ) {
			$venue_details['full_address'][] = $venue_details['venue_email'];
		}

		return $venue_details;
	} // mdjm_get_venue_details

} // class
