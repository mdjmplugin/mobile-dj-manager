<?php
/**
 * Contains all shortcode related functions
 *
 * @package		MDJM
 * @subpackage	Shortcodes
 * @since		1.3
 */

/**
 * The 'MDJM' shortcode replacements.
 * Used for pages and functions.
 *
 * THIS FUNCTION AND SHORTCODE ARE DEPRECATED SINCE 1.3.
 * Maintained for backwards compatibility.
 * @return	string
 */
function shortcode_mdjm( $atts )	{
	// Array mapping the args to the pages/functions
	$pairs = array(
				'Home'			=> MDJM_CLIENTZONE . '/pages/mdjm-home.php',
				'Profile'		 => MDJM_CLIENTZONE . '/pages/mdjm-profile.php',
				'Playlist'		=> MDJM_CLIENTZONE . '/pages/mdjm-playlist.php',
				'Contract'		=> MDJM_CLIENTZONE . '/pages/mdjm-contract.php',
				'Availability'	=> 'f_mdjm_availability_form',
				'Online Quote'	=> MDJM_CLIENTZONE . '/pages/mdjm-onlinequote.php' );
	
	$pairs = apply_filters( 'mdjm_filter_shortcode_pairs', $pairs );
					
	$args = shortcode_atts( $pairs, $atts, 'MDJM' );
	
	if( isset( $atts['page'] ) && !array_key_exists( $atts['page'], $pairs ) )
		$output = __( 'ERROR: Unknown Page', 'mobile-dj-manager' );
	
	else	{
	/* Process pages */
		if( !empty( $atts['page'] ) )	{
			ob_start();
			include_once( $args[$atts['page']] );
			if( $atts['page'] == 'Contact Form' )
				do_action( 'mdjm_dcf_execute_shortcode', $atts );

			$output = ob_get_clean();
		}
		/* Process Functions */
		elseif( !empty( $atts['function'] ) )	{
			$func = $args[$atts['function']];
			if( function_exists( $func ) )	{
				ob_start();
				$func( $atts );
				$output = ob_get_clean();
			}
			else	{
				wp_die( __( 'An error has occurred', 'mobile-dj-manager' ) );	
			}
		}
		else
			return;
	}
	return $output;
} // shortcode_mdjm
add_shortcode( 'MDJM', 'shortcode_mdjm' );

/**
 * MDJM Home Shortcode.
 *
 * Displays the Client Zone home page which will render event details if the client only has a single event
 * or a list of events if they have multiple events in the system.
 * 
 * @since	1.3
 *
 * @return	string
 */
function mdjm_shortcode_home( $atts )	{
	if ( is_user_logged_in() ) {
		global $mdjm_event;
		
		$mdjm_event = '';
		
		ob_start();
		
		$output = '';
		
		$client_id = get_current_user_id();
		
		if( isset( $_GET['event_id'] ) )	{
			$mdjm_event = mdjm_get_event( $_GET['event_id'] );
			
			if( ! empty( $mdjm_event->ID ) )	{
				ob_start();
				mdjm_get_template_part( 'event', 'single' );
				$output .= mdjm_do_content_tags( ob_get_contents(), $mdjm_event->ID, $client_id );
				ob_get_clean();
			}
			else	{
				ob_start();
				mdjm_get_template_part( 'event', 'none' );
				$output .= mdjm_do_content_tags( ob_get_contents(), '', $client_id );
				ob_get_clean();
			}
		}
		else	{
			$client_events = mdjm_get_client_events( $client_id, mdjm_active_event_statuses() );
			
			if( $client_events )	{
				$slug = 'single';
				
				if( count ( $client_events ) > 1 )	{
					$slug = 'loop';
					
					ob_start();
					mdjm_get_template_part( 'event', 'loop-header' );
					$output .= mdjm_do_content_tags( ob_get_contents(), '', $client_id );
					
					do_action( 'mdjm_pre_event_loop' );
					?><div id="mdjm-event-loop"><?php
					ob_get_clean();
				}
				
				foreach( $client_events as $event )	{
					$mdjm_event = new MDJM_Event( $event->ID );
					
					ob_start();
					mdjm_get_template_part( 'event', $slug );
					$output .= mdjm_do_content_tags( ob_get_contents(), $mdjm_event->ID, $client_id );
					ob_get_clean();
				}
				
				
				if( $slug == 'loop' )	{
					ob_start();
					mdjm_get_template_part( 'event', 'loop-footer' );
					$output .= mdjm_do_content_tags( ob_get_contents(), '', $client_id );
					?></div><?php
					do_action( 'mdjm_post_event_loop', $client_events );
					ob_get_clean();
				}
			}
			// No events
			else	{
				mdjm_get_template_part( 'event', 'none' );
				$output .= mdjm_do_content_tags( ob_get_contents(), '', $client_id );
				ob_get_clean();
			}
		}
		$mdjm_event = '';
		
		return $output;
	}
	else	{
		echo mdjm_login_form();
	}
} // mdjm_shortcode_home
add_shortcode( 'mdjm-home', 'mdjm_shortcode_home' );

/**
 * MDJM Contract Shortcode.
 *
 * Displays the MDJM contract page to allow the client to review and sign their event contract.
 * 
 * @since	1.3
 *
 * @return	string
 */
function mdjm_shortcode_contract( $atts )	{
	if( isset( $_GET['event_id'] ) && mdjm_event_exists( $_GET['event_id'] ) )	{
		if( is_user_logged_in() )	{
			global $mdjm_event;
			
			$mdjm_event = new MDJM_Event( $_GET['event_id'] );
			
			$status = ! $mdjm_event->get_contract_status() ? '' : 'signed';
			
			if( $mdjm_event )	{
				ob_start();
				mdjm_get_template_part( 'contract', $status );
				
				// Do not replace tags in a signed contract
				if( $status == 'signed' )	{
					$output = mdjm_do_content_tags( ob_get_contents(), $mdjm_event->ID, $mdjm_event->client );
				}
				else	{
					$output = mdjm_do_content_tags( ob_get_contents(), $mdjm_event->ID, $mdjm_event->client );
				}
				ob_get_clean();
			}
			else	{
				wp_die( __( "Ooops! There seems to be a slight issue and we've been unable to find your event", 'mobile-dj-manager' ) );
			}
			
			// Reset global var
			$mdjm_event = '';
			
			return $output;
		}
		else	{
			echo mdjm_login_form();
		}
	}
	else	{
		wp_die( __( "Ooops! There seems to be a slight issue and we've been unable to find your event", 'mobile-dj-manager' ) );
	}
	
} // mdjm_shortcode_contract
add_shortcode( 'mdjm-contract', 'mdjm_shortcode_contract' );

/**
 * MDJM Playlist Shortcode.
 *
 * Displays the MDJM playlist management system which will render a client interface for clients
 * or a guest interface for event guests with the access URL.
 * 
 * @since	1.3
 *
 * @return	string
 */
function mdjm_shortcode_playlist( $atts )	{
	global $mdjm_event;
	
	$visitor = isset( $_GET['guest_playlist'] ) ? 'guest' : 'client';
	$output  = '';
	
	if( ! isset( $_GET['event_id'] ) && ! isset( $_GET['guest_playlist'] ) )	{
		wp_die( __( 'Sorry an error occured. Please try again.', 'mobile-dj-manager' ) );
	}
	
	$mdjm_event = ( $visitor == 'client' ? mdjm_get_event( $_GET['event_id'] ) : mdjm_get_event_by_playlist_code( $_GET['guest_playlist'] ) );
	
	if( $visitor == 'client' )	{
		if( ! is_user_logged_in() )	{
			echo mdjm_login_form( add_query_arg( 'event_id', $_GET['event_id'], mdjm_get_formatted_url( mdjm_get_option( 'playlist_page' ) ) ) );
		}
	}
	
	if( $mdjm_event )	{
		ob_start();
		mdjm_get_template_part( 'playlist', $visitor );
		$output .= mdjm_do_content_tags( ob_get_contents(), $mdjm_event->ID, $mdjm_event->client );
		ob_get_clean();		
	}
	else	{
		wp_die( __( 'Sorry an error occured. Please try again.', 'mobile-dj-manager' ) );
	}
	
	// Reset global var
	$mdjm_event = '';
	
	return $output;
} // mdjm_shortcode_playlist
add_shortcode( 'mdjm-playlist', 'mdjm_shortcode_playlist' );

/**
 * Display the MDJM availability checker.
 * 
 * @since	1.3
 *
 * @return	string
 */
function mdjm_shortcode_availability( $atts )	{
	$args = shortcode_atts( 
		array( // These are our default values
			'label'			  => __( 'Select Date', 'mobile-dj-manager' ),
			'label_wrap'		 => true,
			'label_class'		=> false,
			'field_wrap'		 => true,
			'field_class'		=> false,
			'submit_text'		=> __( 'Check Date', 'mobile-dj-manager' ),
			'submit_wrap'		 => true,
			'submit_class'	   => false,
			'please_wait_text'   => __( 'Please wait...', 'mobile-dj-manager' ),
			'please_wait_class'  => false
		),
		$atts,
		'mdjm-availability'
	);
	
	ob_start();
	//MDJM_Availability_Checker::availability_form( $args );
	
	return ob_get_clean();
} // mdjm_shortcode_availability
add_shortcode( 'mdjm-availability', 'mdjm_shortcode_availability' );

/**
 * The mdjm-addons shortcode replacement.
 * 
 * @params	str		$filter_by			Optional: category, package or user. Default false (all).
 *			str|int	$filter_value		Optional: The value to which to filter $filter_by. Default false (all).
 *			str		$list				Optional: List type to display. li for bulleted. Default p.
 *			bool	$cost				Optional: Whether or not display the price. Default false.
 *
 *
 */
function mdjm_shortcode_addons_list( $atts )	{
	$args = shortcode_atts( 
		array( // These are our default values
			'filter_by'	   => false,
			'filter_value'	=> false,
			'list'			=> 'p',
			'desc'			=> false,
			'cost'			=> false,
			'addon_class'	 => false,
			'cost_class'	 => false,
			'desc_class'	=> false
		),
		$atts,
		'mdjm-addons'
	);
	
	ob_start();
	$output = '';
	
	if( isset( $args['filter_by'], $args['filter_value'] ) && 
		$args['filter_by'] != 'false' && $args['filter_value'] != 'false' )	{
		
		// Filter addons by user	
		if( $args['filter_by'] == 'category' )
			$equipment = mdjm_addons_by_cat( $args['filter_value'] );
		
		elseif( $args['filter_by'] == 'package' )	{
			$equipment = mdjm_addons_by_package_slug( $args['filter_value'] );
			
			// If package not found by slug, try name
			if( empty( $equipment ) )
				$equipment = mdjm_addons_by_package_name( $args['filter_value'] );
		}
		
		elseif( $args['filter_by'] == 'user' )
			$equipment = mdjm_addons_by_dj( $args['filter_value'] );
		
	}
	else
		$equipment = mdjm_get_addons();
	
	/**
	 * Output the results
	 */
	if( empty( $equipment ) )
		$output .= '<p>' . __( 'No addons available', 'mobile-dj-manager' ) . '</p>';
	
	else	{
		// Check to start bullet list
		if( $args['list'] == 'li' )
			$output .= '<ul>';
			
		foreach( $equipment as $item )	{
			// If the addon is not enabled, do not show it
			if( empty( $item[6] ) || $item[6] != 'Y' )
				continue;
								
			// Output the remaining addons
			if( !empty( $args['list'] ) )
				$output .= '<' . $args['list'] . '>';
			
			if( !empty( $args['addon_class'] ) && $args['addon_class'] != 'false' )
				$output = '<span class="' . $args['addon_class'] . '">';
			
			$output .= stripslashes( esc_textarea( $item[0] ) );
			
			if( !empty( $args['addon_class'] ) && $args['addon_class'] != 'false' )
				$output = '</span>';
			
			if( !empty( $args['cost'] ) && $args['cost'] != 'false' && !empty( $item[7] ) )	{
				if( !empty( $args['cost_class'] ) && $args['cost_class'] != 'false' )
					$output = '<span class="' . $args['cost_class'] . '">';
				
				$output .= '&nbsp;&ndash;&nbsp;' . mdjm_currency_filter( mdjm_sanitize_amount( $item[7] ) );
				
				if( !empty( $args['cost_class'] ) && $args['cost_class'] != 'false' )
					$output = '</span>';
				
			}
			
			if( !empty( $atts['desc'] ) && $atts['desc'] != 'false' && !empty( $item[4] ) )	{
				$output .= '<br />';
				
				if( !empty( $args['desc_class'] ) && $args['desc_class'] != 'false' )
					$output = '<span class="' . $args['desc_class'] . '">';
				else	
					$output .= '<span style="font-style: italic; font-size: smaller;">';
				
				$output .= stripslashes( esc_textarea( $item[4] ) );
				$output .= '</span>';	
			}
				
			if( !empty( $args['list'] ) )
				$output .= '</' . $args['list'] . '>';							
		}
		
		// Check to end bullet list	
		if( $args['list'] == 'li' )
			$output .= '</ul>';
	}
	
	echo $output;
		
	return ob_get_clean();
} // mdjm_shortcode_addons_list
add_shortcode( 'mdjm-addons', 'mdjm_shortcode_addons_list' );

/**
 * MDJM Login Shortcode.
 *
 * Displays a login form for the front end of the website.
 * 
 * @since	1.3
 *
 * @return	string
 */
function mdjm_shortcode_login( $atts )	{
	extract( shortcode_atts( array(
			'redirect' => '',
		), $atts, 'mdjm-login' )
	);
	
	return mdjm_login_form( $redirect );
} // mdjm_shortcode_home
add_shortcode( 'mdjm-login', 'mdjm_shortcode_login' );
?>