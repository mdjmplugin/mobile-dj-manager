<?php
/*
* f_mdjm_shortcodes
* 04/10/2014
* @since 0.8
* Processes the MDJM shortcodes
*/
	function f_mdjm_shortcode( $atts )	{
		$args = shortcode_atts( array(
			'Home'         => 'home',
			'Payments'	 => 'payment',
			'Profile'      => 'profile',
			'Playlist'     => 'playlist',
			'Contract'     => 'contract',
			'Availability' => 'f_mdjm_availability_form',
			'Contact Form' => 'contact-form',
		), $atts, 'MDJM' );
		
		/* Process pages */
		if( isset( $atts['page'] ) && $atts['page'] == 'Contact Form' )	{
			ob_start();
			include_once WPMDJM_PLUGIN_DIR . '/pages/' . $args[$atts['page']] . '.php';
			$output = ob_get_clean();
			return $output;
		}
		if( isset( $atts['page'] ) && !empty( $atts['page'] ) )	{
			ob_start();
			include_once WPMDJM_PLUGIN_DIR . '/pages/' . $args[$atts['page']] . '.php';
			$output = ob_get_clean();
			return $output;
		}
		
		/* Process Functions */
		else	{
			$func = $args[$atts['function']];
			if( function_exists( $func ) )	{
				ob_start();
				$func( $atts );
				$output = ob_get_clean();
				return $output;
			}
			else	{
				wp_die( 'An error has occurred' );	
			}
		}
	}
?>