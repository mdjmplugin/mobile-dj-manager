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
		ob_start();
		
		$client_id = get_current_user_id();
		$events    = mdjm_get_client_events( $client_id, mdjm_active_event_statuses() );
		$output    = '';
		
		if( $events->have_posts() )	{
			if( $events->post_count == 1 )	{
				ob_start();
				mdjm_get_template_part( 'event', 'single' );
				$output .= mdjm_do_content_tags( ob_get_contents(), '', $client_id );
				ob_get_clean();
			}
			else	{
				ob_start();
				mdjm_get_template_part( 'event', 'loop-header' );
				$output .= mdjm_do_content_tags( ob_get_contents(), '', $client_id );
				ob_get_clean();
				
				do_action( 'mdjm_event_loop_before_loop' );
				?>
                <div id="mdjm-event-loop">
                <?php
				
				while( $events->have_posts() )	{
					ob_start();
					$events->the_post();
					mdjm_get_template_part( 'event', 'loop' );
					$output .= mdjm_do_content_tags( ob_get_contents(), get_the_ID(), $client_id );
					ob_get_clean();
				}
				
				?>
                </div>
                <?php
				do_action( 'mdjm_event_loop_after_loop' );
				ob_start();
				mdjm_get_template_part( 'event', 'loop-footer' );
				$output .= mdjm_do_content_tags( ob_get_contents(), '', $client_id );
				ob_get_clean();
			}
			wp_reset_postdata();
		} 
		else	{
			mdjm_get_template_part( 'event', 'none' );
			$output .= mdjm_do_content_tags( ob_get_contents(), '', $client_id );
			ob_get_clean();
		}
		
		return $output;
	}
} // mdjm_shortcode_home
add_shortcode( 'mdjm-home', 'mdjm_shortcode_home' );

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
	MDJM_Availability_Checker::availability_form( $args );
	
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
				
				$output .= '&nbsp;&ndash;&nbsp;' . display_price( $item[7] );
				
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
?>