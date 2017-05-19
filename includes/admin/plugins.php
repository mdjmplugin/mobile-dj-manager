<?php

/**
 * Plugin functions.
 *
 * @package     MDJM
 * @subpackage	Admin/Plugins
 * @copyright   Copyright (c) 2016, Mike Howard
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.3
 */
			
/**
 * Customise the MDJM plugin action links on the plugins page.
 *
 * @since	1.1.3
 * @param	arr		$links	Pre-filtered links.
 * @return	arr		$links	Post-filtered links.
 */
function mdjm_plugin_action_links( $links ) {
	
	$mdjm_plugin_links = array(
		'<a href="' . admin_url( 'admin.php?page=mdjm-settings' ) . '">' . __( 'Settings', 'mobile-dj-manager' ) . '</a>'
	);
			
	return array_merge( $links, $mdjm_plugin_links );

}  // mdjm_action_links
add_filter( 'plugin_action_links_' . MDJM_PLUGIN_BASENAME, 'mdjm_plugin_action_links' );
			
/**
 * Add custom links to the MDJM plugin row on the plugins page.
 *
 * @since	1.1.3
 * @param	arr		$links	Pre-filtered links.	
 * @param	arr		$file	Current plugin file being displayed.	
 */
function mdjm_plugin_row_meta( $links, $file ) {
	
	if( strpos( $file, 'mobile-dj-manager.php' ) === false )	{
		return $links;
	}
		
	$mdjm_links[] = '<a href="http://mdjm.co.uk/support/" target="_blank">' . __( 'Support Docs', 'mobile-dj-manager' ) . '</a>';
	$mdjm_links[] = '<a href="http://mdjm.co.uk/donate/" target="_blank">' . __( 'Donate', 'mobile-dj-manager' ) . '</a>';
	$mdjm_links[] = '<a href="http://http://mdjm.co.uk/add-ons/" target="_blank">' . __( 'Extensions', 'mobile-dj-manager' ) . '</a>';
						
	return array_merge( $links, $mdjm_links );

}
add_filter( 'plugin_row_meta', 'mdjm_plugin_row_meta', 10, 2 );
