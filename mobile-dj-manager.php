<?php
	defined( 'ABSPATH' ) or die( "Direct access to this page is disabled!!!" );
/*
Plugin Name: Mobile DJ Manager
Description: Management interface for mobile DJ's.
Version: 0.8.1
Date: 06 October 2014
Author: Mobile DJ Manager <contact@mdjm.co.uk>
Author URI: http://www.mdjm.co.uk
*/

/*  Copyright 2014  Mobile DJ Manager  (email : contact@mdjm.co.uk)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as 
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/
	global $wpdb, $mdjm_options, $pagenow, $mdjm_db_version;
	$mdjm_db_version = '1.2';
	
	define ( 'WPMDJM_NAME', 'Mobile DJ Manager for Wordpress');
	define ( 'WPMDJM_VERSION_KEY', 'version');
	define ( 'WPMDJM_VERSION_NUM', '0.8.1' );
	define ( 'WPMDJM_REQUIRED_WP_VERSION', '3.9' );
	define ( 'WPMDJM_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );
	define ( 'WPMDJM_PLUGIN_NAME', trim( dirname( WPMDJM_PLUGIN_BASENAME ), '/' ) );
	define ( 'WPMDJM_PLUGIN_DIR', untrailingslashit( dirname( __FILE__ ) ) );
	define ( 'WPMDJM_PLUGIN_URL', untrailingslashit( plugins_url( '', __FILE__ ) ) );
	define ( 'WPMDJM_SETTINGS_KEY', 'mdjm_plugin_settings' );
	
	require_once WPMDJM_PLUGIN_DIR . '/admin/admin-functions.php';
	
	f_mdjm_init();
	$mdjm_options = f_mdjm_get_options();
	
	/* What to do when the plugin is activated? */
	register_activation_hook( __FILE__, 'f_mdjm_install' );
	register_activation_hook( __FILE__, 'f_mdjm_db_install' );

	/* What to do when the plugin is deactivated? */
	register_deactivation_hook( __FILE__, 'f_mdjm_deactivate' );
	
	/* Actions for admin */
	if ( is_admin() )	{
		require_once WPMDJM_PLUGIN_DIR . '/admin/admin.php';
		/* Add the Settings link to the plugin */
		add_filter( 'plugin_action_links_' . plugin_basename(__FILE__), 'add_action_links' );
		add_filter( 'plugin_row_meta', 'mdjm_plugin_meta', 10, 2 );
		add_action( 'admin_init', 'f_mdjm_reg_init' );
		
		if( $pagenow == 'index.php' && isset( $mdjm_options['show_dashboard'] ) && $mdjm_options['show_dashboard'] == 'Y' )	{
			/* Activate widgets */
			require_once WPMDJM_PLUGIN_DIR . '/admin/includes/widgets.php';	
			add_action( 'wp_dashboard_setup', 'f_mdjm_add_wp_dashboard_widgets' );
		}
	}
	/* Actions for users */
	else	{
		require_once WPMDJM_PLUGIN_DIR . '/includes/functions.php';
		require_once WPMDJM_PLUGIN_DIR . '/includes/shortcodes.php';
		add_action( 'wp_head','f_mdjm_insert_head' );
		add_shortcode( 'MDJM', 'f_mdjm_shortcode' );
	}
?>