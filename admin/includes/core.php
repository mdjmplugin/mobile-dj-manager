<?php

/*
 * core.php
 * 18/03/2015
 * @since 1.1.3
 * Manipulate WP for non-WP functions
 */

	if( !class_exists( 'MDJM_WP' ) )	{
		class MDJM_WP	{
			/*
			 * __construct
			 * defines the params used within the class
			 *
			 *
			 */
			public function __construct()	{
				global $wpdb, $mdjm_post_types;
			
				/* -- Plugin data -- */
				add_filter( 'plugin_action_links_' . MDJM_PLUGIN_BASENAME, array( &$this, 'mdjm_plugin_action_links' ) );
				add_filter( 'plugin_row_meta', array( &$this, 'mdjm_plugin_meta' ), 10, 2 );
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
				$lic_info = do_reg_check ('check' );
				if( !$lic_info || $lic_info['type'] == 'trial' )
					$mdjm_plugin_links[] = '<a href="http://www.mydjplanner.co.uk/shop/mobile-dj-manager-for-wordpress-plugin/"><font style="color:#F90">' . __( 'Buy License' ) . '</font></a>';
						
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
					
				$lic_info = do_reg_check ('check' );
				$mdjm_links[] = '<a href="http://www.mydjplanner.co.uk/support/" target="_blank">' . __( 'Support' ) . '</a>';
				
				if( !$lic_info || $lic_info['key'] == 'XXXX' )
					$mdjm_links[] = '<a href="http://www.mydjplanner.co.uk/shop/mobile-dj-manager-for-wordpress-plugin/" target="_blank"><font style="color: #F90;">' . __( 'Buy Now' ) . '</font></a>';
					
				$links = array_merge( $links, $mdjm_links );
		
				return $links;
			}
		}
	}
/* -- Insantiate the class & register the activation/deactivation hooks -- */
	if( class_exists( 'MDJM_WP' ) )	{	
		/* -- Instantiate the plugin class -- */
		$mdjm_wp = new MDJM_WP();
	}
?>