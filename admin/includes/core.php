<?php

/*
 * core.php
 * 18/03/2015
 * @since 1.1.3
 * Manipulate WP for non-WP functions
 */

	if( !class_exists( 'MDJM_WP' ) ) :
		class MDJM_WP	{
			/*
			 * __construct
			 * defines the params used within the class
			 *
			 *
			 */
			public function init()	{
				global $wpdb, $mdjm_post_types;
			
				/* -- Plugin data -- */
				add_filter( 'plugin_action_links_' . MDJM_PLUGIN_BASENAME, array( __CLASS__, 'mdjm_plugin_action_links' ) );
				add_filter( 'plugin_row_meta', array( __CLASS__, 'mdjm_plugin_meta' ), 10, 2 );
			} // __construct
			
			/*
			 * mdjm_plugin_action_links
			 * Set the action links for the plugin display page
			 *
			 */
			function mdjm_plugin_action_links( $links ) {
				$mdjm_plugin_links = array(
						'<a href="' . admin_url( 'admin.php?page=mdjm-dashboard' ) . '">' . __( 'Dashboard' ) . '</a>',
						'<a href="' . admin_url( 'admin.php?page=mdjm-settings' ) . '">' . __( 'Settings' ) . '</a>',
					);
						
				return array_merge( $mdjm_plugin_links, $links );
			}  // mdjm_action_links
			
			/*
			 * mdjm_plugin_meta
			 * Add meta links to the plugins page
			 *
			 */
			function mdjm_plugin_meta( $links, $file ) {
				if( strpos( $file, 'mobile-dj-manager.php' ) === false )
					return $links;
					
				$mdjm_links[] = '<a href="http://www.mydjplanner.co.uk/support/" target="_blank">' . __( 'Support' ) . '</a>';
				$mdjm_links[] = '<a href="http://www.mydjplanner.co.uk/donate/" target="_blank">' . __( 'Donate' ) . '</a>';
				$mdjm_links[] = '<a href="http://www.mydjplanner.co.uk/product-category/mdjm/premium-add-ons/" target="_blank">' . __( 'Extensions' ) . '</a>';
									
				$links = array_merge( $links, $mdjm_links );
		
				return $links;
			}
		}
	endif;
// Insantiate the class & register the activation/deactivation hooks
	MDJM_WP::init();
?>