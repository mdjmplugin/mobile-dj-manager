<?php

/**
 * Contains all HTML related functions
 *
 * @package		MDJM
 * @subpackage	HTML
 * @since		1.3
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) )
	exit;

/**
 * Generate an MDJM URL based upon the sites permalink settings.
 *
 * @since	1.3
 * @param	int		$page_id	Required: The WordPress page ID.
 * @param	bool	$permalink	Optional: Whether or not to include the permalink structure.
 * @param	bool	$echo		Optional: True to echo the URL or false (default) to return it.
 * @return	str		HTML formatted URL.
 */
function mdjm_get_formatted_url( $page_id, $permalink=true, $echo=false )	{
	// The URL
	$return = get_permalink( $page_id );
	
	if( !empty( $permalink ) )	{
		if( get_option( 'permalink_structure', false ) )	{
			$return .= '?';
		}
		else	{
			$return .= '&amp;';	
		}
	}
	
	if( !empty( $echo ) )	{
		echo $return;	
	}
	else	{
		return $return;
	}
} // mdjm_get_formatted_url
?>