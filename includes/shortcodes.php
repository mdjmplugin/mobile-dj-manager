<?php


/**
 * Contains all shortcode related functions
 *
 * @package     MDJM
 * @subpackage  Shortcodes
 * @since       1.3
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * The 'MDJM' shortcode replacements.
 * Used for pages and functions.
 *
 * THIS FUNCTION AND SHORTCODE ARE DEPRECATED SINCE 1.3.
 * Maintained for backwards compatibility.
 *
 * @return  string
 */
function shortcode_mdjm( $atts ) {
	// Array mapping the args to the pages/functions
	$pairs = array(
		'Home'         => 'mdjm_shortcode_home',
		'Profile'      => 'mdjm_shortcode_profile',
		'Playlist'     => 'mdjm_shortcode_playlist',
		'Contract'     => 'mdjm_shortcode_contract',
		'Availability' => 'f_mdjm_availability_form',
		'Online Quote' => 'mdjm_shortcode_quote',
	);

	$pairs = apply_filters( 'mdjm_filter_shortcode_pairs', $pairs );

	$args = shortcode_atts( $pairs, $atts, 'MDJM' );

	if ( isset( $atts['page'] ) && ! array_key_exists( $atts['page'], $pairs ) ) {
		$output = __( 'ERROR: Unknown Page', 'mobile-dj-manager' );
	} else {
		/* Process pages */
		if ( ! empty( $atts['page'] ) ) {
			ob_start();

			if ( function_exists( $args[ $atts['page'] ] ) ) {
				$func = $args[ $atts['page'] ];
				return $func( $atts );
			} else {
				include_once $args[ $atts['page'] ];
				if ( $atts['page'] == 'Contact Form' ) {
					do_action( 'mdjm_dcf_execute_shortcode', $atts );
				}

				$output = ob_get_clean();
			}
		}
		/* Process Functions */
		elseif ( ! empty( $atts['function'] ) ) {
			$func = $args[ $atts['function'] ];
			if ( function_exists( $func ) ) {
				ob_start();
				$func( $atts );
				$output = ob_get_clean();
			} else {
				return __( 'An error has occurred', 'mobile-dj-manager' );
			}
		} else {
			return;
		}
	}
	return $output;
} // shortcode_mdjm
add_shortcode( 'MDJM', 'shortcode_mdjm' );

/**
 * MDJM Home Shortcode.
 *
 * Displays the Client Portal home page which will render event details if the client only has a single event
 * or a list of events if they have multiple events in the system.
 *
 * @since   1.3
 *
 * @return  string
 */
function mdjm_shortcode_home( $atts ) {

	if ( is_user_logged_in() ) {

		global $mdjm_event;

		$mdjm_event = '';

		ob_start();

		$output = '';

		$client_id = get_current_user_id();

		mdjm_add_content_tag(
			'event_action_buttons',
			sprintf( __( '%1$s action buttons within %2$s', 'mobile-dj-manager' ), mdjm_get_label_singular(), mdjm_get_application_name() ),
			'mdjm_do_action_buttons'
		);

		if ( isset( $_GET['event_id'] ) ) {
			$mdjm_event = mdjm_get_event( sanitize_key( wp_unslash( $_GET['event_id'] ) ) );

			if ( ! empty( $mdjm_event->ID ) && absint( $mdjm_event->client ) === $client_id ) {
				ob_start();

				mdjm_get_template_part( 'event', 'single' );
				$output .= mdjm_do_content_tags( ob_get_contents(), $mdjm_event->ID, $client_id );
				ob_get_clean();
			} else {
				ob_start();
				mdjm_get_template_part( 'event', 'none' );
				$output .= mdjm_do_content_tags( ob_get_contents(), '', $client_id );
				ob_get_clean();
			}
		} else {
			$client_events = mdjm_get_client_events( $client_id, mdjm_active_event_statuses() );

			if ( $client_events ) {

				$slug = 'single';

				if ( count( $client_events ) > 1 ) {
					$slug = 'loop';

					ob_start();
					mdjm_get_template_part( 'event', 'loop-header' );
					$output .= mdjm_do_content_tags( ob_get_contents(), '', $client_id );

					do_action( 'mdjm_pre_event_loop' );
					?><div id="mdjm-event-loop">
					<?php
					ob_get_clean();
				}

				foreach ( $client_events as $event ) {
					$mdjm_event = new MDJM_Event( $event->ID );

					ob_start();

					mdjm_get_template_part( 'event', $slug );
					$output .= mdjm_do_content_tags( ob_get_contents(), $mdjm_event->ID, $client_id );
					ob_get_clean();
				}

				if ( $slug == 'loop' ) {
					ob_start();
					mdjm_get_template_part( 'event', 'loop-footer' );
					$output .= mdjm_do_content_tags( ob_get_contents(), '', $client_id );
					?>
					</div>
					<?php
					do_action( 'mdjm_post_event_loop', $client_events );
					ob_get_clean();
				}
			} else {
				mdjm_get_template_part( 'event', 'none' );
				$output .= mdjm_do_content_tags( ob_get_contents(), '', $client_id );
				ob_get_clean();
			}
		}
		$mdjm_event = '';

		return $output;
	} else {
		echo mdjm_login_form( mdjm_get_current_page_url() ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}

} // mdjm_shortcode_home
add_shortcode( 'mdjm-home', 'mdjm_shortcode_home' );

/**
 * MDJM Contract Shortcode.
 *
 * Displays the MDJM contract page to allow the client to review and sign their event contract.
 *
 * @since   1.3
 *
 * @return  string
 */
function mdjm_shortcode_contract( $atts ) {

	if ( isset( $_GET['event_id'] ) && mdjm_event_exists( sanitize_key( wp_unslash( $_GET['event_id'] ) ) ) ) {
		if ( is_user_logged_in() ) {
			global $mdjm_event;

			$mdjm_event = new MDJM_Event( sanitize_key( wp_unslash( $_GET['event_id'] ) ) );

			$status = ! $mdjm_event->get_contract_status() ? '' : 'signed';

			if ( $mdjm_event ) {
				ob_start();
				mdjm_get_template_part( 'contract', $status );

				// Do not replace tags in a signed contract
				if ( $status == 'signed' ) {
					$output = mdjm_do_content_tags( ob_get_contents(), $mdjm_event->ID, $mdjm_event->client );
				} else {
					$output = mdjm_do_content_tags( ob_get_contents(), $mdjm_event->ID, $mdjm_event->client );
				}
				ob_get_clean();
			} else {
				return sprintf( __( "Ooops! There seems to be a slight issue and we've been unable to find your %s.", 'mobile-dj-manager' ), mdjm_get_label_singular( true ) );
			}

			// Reset global var
			$mdjm_event = '';

			return $output;
		} else {
			echo mdjm_login_form( mdjm_get_current_page_url() ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		}
	} else {
		return sprintf( __( "Ooops! There seems to be a slight issue and we've been unable to find your %s.", 'mobile-dj-manager' ), mdjm_get_label_singular( true ) );
	}

} // mdjm_shortcode_contract
add_shortcode( 'mdjm-contract', 'mdjm_shortcode_contract' );

/**
 * Payment Form shortcode.
 *
 * Displays the payment form to collect event payments.
 *
 * @since   1.3.8
 * @param   arr $atts
 * @return  string
 */
function mdjm_shortcode_payment( $atts ) {

	if ( is_user_logged_in() ) {

		global $mdjm_event;

		if ( isset( $_GET['event_id'] ) ) {
			$event_id = sanitize_key( wp_unslash( $_GET['event_id'] ) );
		} else {
			$next_event = mdjm_get_clients_next_event( get_current_user_id() );

			if ( $next_event ) {
				$event_id = $next_event[0]->ID;
			}
		}

		if ( ! isset( $event_id ) ) {
			return __( "Ooops! There seems to be a slight issue and we've been unable to find your event", 'mobile-dj-manager' );
		}

		$mdjm_event = new MDJM_Event( $event_id );

		if ( $mdjm_event ) {

			return mdjm_payment_form();

		} else {
			return __( "Ooops! There seems to be a slight issue and we've been unable to find your event", 'mobile-dj-manager' );
		}

		// Reset global var
		$mdjm_event = '';

	} else {
		echo mdjm_login_form(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}

} // mdjm_shortcode_payment
add_shortcode( 'mdjm-payments', 'mdjm_shortcode_payment' );

/**
 * MDJM Profile Shortcode.
 *
 * Displays the MDJM user Profile page.
 *
 * @since   1.3
 * @param   arr $atts
 * @return  string
 */
function mdjm_shortcode_profile( $atts ) {
	ob_start();

	mdjm_get_template_part( 'profile', 'client' );

	return ob_get_clean() . '&nbsp;';
} // mdjm_shortcode_profile
add_shortcode( 'mdjm-profile', 'mdjm_shortcode_profile' );

/**
 * MDJM Playlist Shortcode.
 *
 * Displays the MDJM playlist management system which will render a client interface for clients
 * or a guest interface for event guests with the access URL.
 *
 * @since   1.3
 *
 * @return  string
 */
function mdjm_shortcode_playlist( $atts ) {

	global $mdjm_event;

	if ( isset( $_GET['mdjmeventid'] ) ) {
		$_GET['guest_playlist'] = absint( $_GET['mdjmeventid'] );
	}

	$visitor  = isset( $_GET['guest_playlist'] ) ? 'guest' : 'client';
	$output   = '';
	$event_id = '';

	if ( ! empty( $_GET['event_id'] ) ) {
		$event_id = sanitize_key( wp_unslash( $_GET['event_id'] ) );
	} else {
		$next_event = mdjm_get_clients_next_event( get_current_user_id() );

		if ( $next_event ) {
			$event_id = $next_event[0]->ID;
		}
	}

	if ( ! isset( $event_id ) && ! isset( $_GET['guest_playlist'] ) ) {
		ob_start();
		mdjm_get_template_part( 'playlist', 'noevent' );
		$output .= mdjm_do_content_tags( ob_get_contents(), '', get_current_user_id() );
	} else {

		$mdjm_event = $visitor == 'client' ? mdjm_get_event( $event_id ) : mdjm_get_event_by_playlist_code( sanitize_key( wp_unslash( $_GET['guest_playlist'] ) ) );

		ob_start();

		if ( $visitor == 'client' && ! is_user_logged_in() ) {
			$output .= mdjm_login_form( add_query_arg( 'event_id', $event_id, mdjm_get_formatted_url( mdjm_get_option( 'playlist_page' ) ) ) );
		} elseif ( $mdjm_event ) {
			mdjm_get_template_part( 'playlist', $visitor );
			$output .= mdjm_do_content_tags( ob_get_contents(), $mdjm_event->ID, $mdjm_event->client );
		} else {
			mdjm_get_template_part( 'playlist', 'noevent' );
			$output .= mdjm_do_content_tags( ob_get_contents(), '', get_current_user_id() );
		}
	}

	ob_get_clean();

	// Reset global var
	$mdjm_event = '';

	return apply_filters( 'mdjm_playlist_form', $output );

} // mdjm_shortcode_playlist
add_shortcode( 'mdjm-playlist', 'mdjm_shortcode_playlist' );

/**
 * MDJM Quote Shortcode.
 *
 * Displays the online quotation to the client.
 *
 * @since   1.3
 * @param   arr $atts   Arguments passed with the shortcode
 * @return  string
 */
function mdjm_shortcode_quote( $atts ) {

	$atts = shortcode_atts(
		array( // These are our default values
			'button_text' => sprintf( __( 'Book this %s', 'mobile-dj-manager' ), mdjm_get_label_singular() ),
		),
		$atts,
		'mdjm-quote'
	);

	$event_id = '';

	if ( ! empty( $_GET['event_id'] ) ) {
		$event_id = sanitize_key( wp_unslash( $_GET['event_id'] ) );
	} else {
		$next_event = mdjm_get_clients_next_event( get_current_user_id() );

		if ( $next_event ) {
			$event_id = $next_event[0]->ID;
		}
	}

	if ( isset( $event_id ) && mdjm_event_exists( $event_id ) ) {

		if ( is_user_logged_in() ) {

			global $mdjm_event, $mdjm_quote_button_atts;

			$mdjm_quote_button_atts = $atts;

			$mdjm_event = new MDJM_Event( $event_id );

			ob_start();

			if ( $mdjm_event ) {

				// Some verification
				if ( get_current_user_id() != $mdjm_event->client ) {
					mdjm_get_template_part( 'quote', 'noevent' );
				} else {
					mdjm_get_template_part( 'quote' );
				}

				$output = mdjm_do_content_tags( ob_get_contents(), $mdjm_event->ID, $mdjm_event->client );

			} else {
				mdjm_get_template_part( 'quote', 'noevent' );

				$output = mdjm_do_content_tags( ob_get_contents(), '', get_current_user_id() );
			}

			ob_get_clean();

			// Reset global var
			$mdjm_event = '';

			return $output;

		} else {
			echo mdjm_login_form( mdjm_get_current_page_url() ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		}
	} else {
		ob_start();
		mdjm_get_template_part( 'quote', 'noevent' );
		$output = mdjm_do_content_tags( ob_get_contents(), '', get_current_user_id() );
		ob_get_clean();
	}

} // mdjm_shortcode_quote
add_shortcode( 'mdjm-quote', 'mdjm_shortcode_quote' );

/**
 * MDJM Availability Checker Shortcode.
 *
 * Displays the MDJM Availability Checker form which allows clients to determine if you are
 * available on their chosen event date.
 *
 * @since   1.3
 *
 * @return  string
 */
function mdjm_shortcode_availability( $atts ) {

	$atts = shortcode_atts(
		array( // These are our default values
			'label'             => __( 'Select Date', 'mobile-dj-manager' ) . ':',
			'label_class'       => 'mdjm-label',
			'field_class'       => '',
			'submit_text'       => __( 'Check Availability', 'mobile-dj-manager' ),
			'submit_class'      => '',
			'please_wait_text'  => __( 'Please wait...', 'mobile-dj-manager' ),
			'please_wait_class' => '',
			'display'           => 'horizontal',
		),
		$atts,
		'mdjm-availability'
	);

	$field_id = 'mdjm-availability-datepicker';

	$search  = array( '{label}', '{label_class}', '{field}', '{field_class}', '{submit_text}', '{submit_class}', '{please_wait_text}', '{please_wait_class}' );
	$replace = array(
		$atts['label'],
		$atts['label_class'],
		$field_id,
		$atts['field_class'],
		$atts['submit_text'],
		$atts['submit_class'],
		$atts['please_wait_text'],
		$atts['please_wait_class'],
	);

	ob_start();

	mdjm_insert_datepicker(
		array(
			'class'    => '',
			'id'       => $field_id,
			'altfield' => 'availability_check_date',
			'mindate'  => '1',
		)
	);

	echo '<!-- ' . esc_html( 'MDJM Availability Checker', 'mobile-dj-manager' ) . ' (' . esc_html( MDJM_VERSION_NUM ) . ') -->';
	echo '<form name="mdjm-availability-check" id="mdjm-availability-check" method="post">';
	wp_nonce_field( 'do_availability_check', 'mdjm_nonce', true, true );
	mdjm_action_field( 'do_availability_check' );
	echo '<input type="hidden" name="availability_check_date" id="availability_check_date" />';
	mdjm_get_template_part( 'availability', $atts['display'], true );
	echo '</form>';

	$output  = ob_get_clean();
	$output  = str_replace( $search, $replace, $output );
	$output  = mdjm_do_content_tags( $output );
	$output .= '<!-- ' . __( 'MDJM Availability Checker', 'mobile-dj-manager' ) . ' (' . MDJM_VERSION_NUM . ') -->';

	return apply_filters( 'mdjm_availability_form', $output );

} // mdjm_shortcode_availability
add_shortcode( 'mdjm-availability', 'mdjm_shortcode_availability' );

/**
 * Addons List Shortcode.
 *
 * @param   arr     $atts       Shortcode attributes. See $atts.
 * @param   str|int $filter_value   The value to which to filter $filter_by. Default false (all).
 * @param   str     $list           List type to display. li for bulleted. Default p.
 * @param   bool    $cost           Whether or not display the price. Default false.
 */
function mdjm_shortcode_addons_list( $atts ) {

	global $post;

	$atts = shortcode_atts(
		array( // These are our default values
			'filter_by'    => false,
			'filter_value' => false,
			'list'         => 'p',
			'desc'         => false,
			'desc_length'  => mdjm_get_option( 'package_excerpt_length', 55 ),
			'cost'         => false,
			'addon_class'  => false,
			'cost_class'   => false,
			'desc_class'   => false,
		),
		$atts,
		'mdjm-addons'
	);

	ob_start();
	$output = '';

	if ( ! empty( $post ) && 'mdjm-package' == get_post_type( $post->ID ) ) {
		$package_addons = mdjm_get_package_addons( $post->ID );

		$addons = array();
		foreach ( $package_addons as $package ) {
			$addons[] = mdjm_get_addon( $package );
		}
	} elseif ( ! empty( $atts['filter_by'] ) && ! empty( $atts['filter_value'] ) && $atts['filter_by'] != 'false' && $atts['filter_value'] != 'false' ) {

		// Filter addons by user
		if ( $atts['filter_by'] == 'category' ) {

			$addons = mdjm_get_addons_in_category( $atts['filter_value'] );

		} elseif ( $atts['filter_by'] == 'package' ) {
			if ( ! is_numeric( $atts['filter_value'] ) ) { // For backwards compatibility
				$package = mdjm_get_package_by( 'slug', $atts['filter_value'] );
				if ( $package ) {
					$atts['filter_value'] = $package->ID;
				}
			}

			$package_addons = mdjm_get_package_addons( $atts['filter_value'] );

			$addons = array();
			foreach ( $package_addons as $package ) {
				$addons[] = mdjm_get_addon( $package );
			}
		} elseif ( $atts['filter_by'] == 'user' ) {
			$addons = mdjm_get_addons_by_employee( $atts['filter_value'] );
		}
	} else {
		$addons = mdjm_get_addons();
	}

	/**
	 * Output the results
	 */
	if ( ! $addons ) {
		$output .= '<p>' . __( 'No addons available', 'mobile-dj-manager' ) . '</p>';
	} else {

		// Check to start bullet list
		if ( $atts['list'] == 'li' ) {
			$output .= '<ul>';
		}

		foreach ( $addons as $addon ) {

			// Output the remaining addons
			if ( ! empty( $atts['list'] ) ) {
				$output .= '<' . $atts['list'] . '>';
			}

			if ( ! empty( $atts['addon_class'] ) && $atts['addon_class'] != 'false' ) {
				$output = '<span class="' . $atts['addon_class'] . '">';
			}

			$output .= $addon->post_title;

			if ( ! empty( $atts['addon_class'] ) && $atts['addon_class'] != 'false' ) {
				$output = '</span>';
			}

			$cost = mdjm_get_addon_price( $addon->ID );
			if ( ! empty( $atts['cost'] ) && $atts['cost'] != 'false' && ! empty( $cost ) ) {

				if ( ! empty( $atts['cost_class'] ) && $atts['cost_class'] != 'false' ) {
					$output = '<span class="' . $atts['cost_class'] . '">';
				}

				$output .= '&nbsp;&ndash;&nbsp;' . mdjm_currency_filter( mdjm_format_amount( $cost ) );

				if ( ! empty( $atts['cost_class'] ) && $atts['cost_class'] != 'false' ) {
					$output = '</span>';
				}
			}

			$desc = mdjm_get_addon_excerpt( $addon->ID, $atts['desc_length'] );
			if ( ! empty( $atts['desc'] ) && $atts['desc'] != 'false' && ! empty( $desc ) ) {

				$output .= '<br />';

				if ( ! empty( $atts['desc_class'] ) && $atts['desc_class'] != 'false' ) {
					$output = '<span class="' . $atts['desc_class'] . '">';
				} else {
					$output .= '<span style="font-style: italic; font-size: smaller;">';
				}

				$output .= $desc;
				$output .= '</span>';

			}

			if ( ! empty( $atts['list'] ) ) {
				$output .= '</' . $atts['list'] . '>';
			}
		}

		// Check to end bullet list
		if ( $atts['list'] == 'li' ) {
			$output .= '</ul>';
		}
	}

	echo esc_attr( apply_filters( 'mdjm_shortcode_addons_list', $output ) );

	return ob_get_clean();

} // mdjm_shortcode_addons_list
add_shortcode( 'mdjm-addons', 'mdjm_shortcode_addons_list' );

/**
 * MDJM Login Shortcode.
 *
 * Displays a login form for the front end of the website.
 *
 * @since   1.3
 *
 * @return  string
 */
function mdjm_shortcode_login( $atts ) {

	extract(
		shortcode_atts(
			array(
				'redirect' => '',
			),
			$atts,
			'mdjm-login'
		)
	);

	return mdjm_login_form( $redirect );

} // mdjm_shortcode_home
add_shortcode( 'mdjm-login', 'mdjm_shortcode_login' );
