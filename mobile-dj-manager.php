<?php
	defined( 'ABSPATH' ) or die( "Direct access to this page is disabled!!!" );
/*
Plugin Name: Mobile DJ Manager
Description: Mobile DJ Manager is an interface allowing mobile DJ's and businesses to manage their events and employees as well as interact with their clients easily. Automating many of your day to day tasks, Mobile DJ Manager for WordPress is the ultimate tool for any Mobile DJ Business.
Version: 1.2.1
Date: 17 March 2015
Author: My DJ Planner <contact@mydjplanner.co.uk>
Author URI: http://www.mydjplanner.co.uk
*/

/*  Copyright 2014  Mobile DJ Manager  (email : contact@mydjplanner.co.uk)
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
	$mdjm_db_version = '2.4'; // Used to determine if the DB Tables need updating

	define( 'MDJM_PLUGIN_DIR', untrailingslashit( dirname( __FILE__ ) ) );
	define( 'MDJM_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );
	
	/* -- These will be deprecated soon -- */
	define( 'WPMDJM_NAME', 'Mobile DJ Manager for Wordpress' );
	define( 'WPMDJM_VERSION_KEY', 'version' );
	define( 'WPMDJM_VERSION_NUM', '1.2.1' );
	define( 'WPMDJM_REQUIRED_WP_VERSION', '3.9' );
	define( 'WPMDJM_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );
	define( 'WPMDJM_PLUGIN_NAME', trim( dirname( WPMDJM_PLUGIN_BASENAME ), '/' ) );
	define( 'WPMDJM_PLUGIN_DIR', untrailingslashit( dirname( __FILE__ ) ) );
	define( 'WPMDJM_PLUGIN_URL', untrailingslashit( plugins_url( '', __FILE__ ) ) );
	define( 'WPMDJM_SETTINGS_KEY', 'mdjm_plugin_settings' );
	define( 'WPMDJM_FETEXT_SETTINGS_KEY', 'mdjm_frontend_text' );
	define( 'WPMDJM_PAYMENTS_KEY', 'mdjm_pp_options' );
	
	$mdjm_client_text = get_option( WPMDJM_FETEXT_SETTINGS_KEY );
	
	include( WPMDJM_PLUGIN_DIR . '/admin/includes/class/class-mdjm.php' );
	
	require_once( WPMDJM_PLUGIN_DIR . '/admin/admin-functions.php' );
	
	//f_mdjm_init(); // DEPRECATE FROM 1.2.2

	//$mdjm_options = f_mdjm_get_options(); // DEPRECATE FROM 1.2.2

	/* What to do when the plugin is activated or de-activated */
	register_activation_hook( __FILE__, array( 'MDJM', 'mdjm_activate' ) );
	register_deactivation_hook(__FILE__, array( 'MDJM', 'mdjm_deactivate' ) );
	//register_activation_hook( __FILE__, 'f_mdjm_db_install' );
		
	/* Actions for admin */
	if ( is_admin() )	{
		/* -- Define Custom Post Types -- */
		
		require_once WPMDJM_PLUGIN_DIR . '/admin/admin.php';
		/* Upgrade procedures */
		add_action( 'plugins_loaded', 'f_mdjm_upgrade' );
		
		/* Add the Settings link to the plugin */
		
		/* Initialise and go! */
		//add_action( 'admin_init', 'f_mdjm_reg_init' );

		if( $pagenow == 'index.php' && isset( $mdjm_options['show_dashboard'] ) && $mdjm_options['show_dashboard'] == 'Y' )	{
			/* Activate widgets */
			require_once WPMDJM_PLUGIN_DIR . '/admin/includes/widgets.php';	
			add_action( 'wp_dashboard_setup', 'f_mdjm_add_wp_dashboard_widgets' );
		}
	}
	/* Actions for users */
	else	{
		//global $mdjm_client_text;
		require_once WPMDJM_PLUGIN_DIR . '/includes/functions.php';
		require_once WPMDJM_PLUGIN_DIR . '/admin/includes/functions.php';
		require_once WPMDJM_PLUGIN_DIR . '/admin/admin.php';
		//require_once WPMDJM_PLUGIN_DIR . '/includes/shortcodes.php';
		//$mdjm_client_text = get_option( WPMDJM_FETEXT_SETTINGS_KEY );
		//add_action( 'wp_head','f_mdjm_insert_head' );
		//add_shortcode( 'MDJM', 'f_mdjm_shortcode' );
	}
?>