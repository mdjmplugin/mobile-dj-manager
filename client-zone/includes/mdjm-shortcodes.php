<?php
/**
 * Class: MDJM_Shortcodes
 * Description: Manage MDJM Shortcodes for the front end
 * 
 * 
 * 
 */
if( !class_exists( 'MDJM_Shortcodes' ) ) :
	class MDJM_Shortcodes	{
		/**
		 * Class constructor
		 *
		 *
		 *
		 */
		function __construct()	{
			add_action( 'init', array( &$this, 'register_shortcodes' ) );
		} // construct
		
		/**
		 * Register the shortcodes for use within MDJM
		 *
		 *
		 *
		 */
		function register_shortcodes()	{
			// MDJM shortcode used for pages
			add_shortcode( 'MDJM', array( &$this, 'shortcode_mdjm' ) );
			
			// Availability shortcode used for pages
			add_shortcode( 'mdjm-availability', array( &$this, 'shortcode_availability' ) );
		} // register_shortcodes
		
		/**
		 * The mdjm-availability shortcode replacement.
		 * 
		 *
		 *
		 *
		 */
		function shortcode_availability( $atts )	{
			ob_start();
			MDJM_Availability_Checker::availability_form();
			$output = ob_get_clean();
			
			return $output;
		} // shortcode_availability
		
		/**
		 * The 'MDJM' shortcode replacements.
		 * Used for pages and functions
		 *
		 *
		 *
		 */
		function shortcode_mdjm( $atts )	{
			// Array mapping the args to the pages/functions
			$pairs = array(
						'Home'			=> MDJM_CLIENTZONE . '/class/class-home.php',
						'Profile'		 => MDJM_CLIENTZONE . '/class/class-profile.php',
						'Playlist'		=> MDJM_CLIENTZONE . '/class/class-playlist.php',
						'Contract'		=> MDJM_CLIENTZONE . '/class/class-contract.php',
						'Availability'	=> 'f_mdjm_availability_form',
						'Online Quote'	=> MDJM_CLIENTZONE . '/class/class-onlinequote.php' );
			
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
	} // class MDJM_Shortcodes
endif;
	new MDJM_Shortcodes();