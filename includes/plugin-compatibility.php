<?php
/**
 * Contains all functions to address compatibility issues with other plugins and/or themes.
 *
 * @package		MDJM
 * @subpackage	Functions
 * @since		1.4.3
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) )
	exit;
	
/**
 * CloudFlare uses Rocket Loader which has a tendancy to disrupt some JavaScript.
 *
 * Adding the [data-cfasync="false"] attribute to MDJM scripts instructs CloudFlare to ignore.
 *
 * @since	1.4.3
 * @param	str		$tag	The <script> tag for the enqueued script.
 * @param	str		$handle	The script's registered handle.
 */
function mdjm_cloudflare_rocketscript_ignore( $tag, $handle )	{
	if ( false === strpos( $handle, 'mdjm-' ) )	{
		return $tag;
	}

	$tag = str_replace( ' src', ' data-cfasync="false" src', $tag );

	return $tag;

} // mdjm_cloudflare_rocketscript_ignore
add_filter( 'script_loader_tag', 'mdjm_cloudflare_rocketscript_ignore', 10, 2 );
