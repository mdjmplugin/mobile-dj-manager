<?php
/*
 * Update procedures for version 1.2.3.3
 *
 *
 *
 */
	class MDJM_Upgrade_to_1_2_3_3	{
		function __construct()	{
			$this->update_settings();
			$this->create_pages();
			$this->create_template();
		}
		
		/*
		 * Update MDJM settings
		 *
		 *
		 *
		 */
		function update_settings()	{
			
			$GLOBALS['mdjm_debug']->log_it( 'Updating Settings for version 1.2.3.3' );
			
			if( !get_option( 'm_d_j_m_has_initiated' ) )
				add_option( 'm_d_j_m_has_initiated', current_time( 'timestamp' ) );
			
			$templates = get_option( 'mdjm_templates_settings' );
			$clientzone = get_option( 'mdjm_clientzone_settings' );
			$mdjm_page_settings = get_option( 'mdjm_plugin_pages' );
			
			$clientzone['package_prices'] = true; // Enable option to display package price within client zone
			$templates['online_enquiry'] = '0'; // Option to use online enquiries
			$mdjm_page_settings['quotes_page'] = 'N';
			
			update_option( 'mdjm_plugin_pages', $mdjm_page_settings );
			update_option( 'mdjm_clientzone_settings', $clientzone );
			update_option( 'mdjm_templates_settings', $templates );
			
			$GLOBALS['mdjm_debug']->log_it( 'Settings Updated' );
						
		} // update_settings
		
		/*
		 * Create the new page for online quotations and populate
		 *
		 *
		 *
		 */
		function create_pages()	{
			/* -- Grab the existing page settings so we can update with page ID's -- */
			if( empty( $mdjm_page_settings ) );
				$mdjm_page_settings = get_option( 'mdjm_plugin_pages' );
			
			/* -- Defaults for all pages -- */
			$mdjm_page_defaults = array(
									'post_title'		=> 'Event Quotes',
									'post_name'		 => 'client-quotes',
									'post_type'	 	 => 'page',
									'post_status'   	   => 'publish',
									'post_content'	  => '[MDJM page="Online Quote"]',
									'post_parent'	   => $mdjm_page_settings['app_home_page'],
									'ping_status'   	   => 'closed',
									'comment_status'	=> 'closed',
									);
			
			/* -- Insert the page & return the page ID -- */
			$GLOBALS['mdjm_debug']->log_it( 'Creating quote page for version 1.2.3.3' );
			$mdjm_page_id = wp_insert_post( $mdjm_page_defaults );
			
			if( $mdjm_page_id )
				$GLOBALS['mdjm_debug']->log_it( 'Page Client Quotes created successfully' );
				
			/* -- Add the setting for the page -- */
			$mdjm_page_settings['quotes_page'] = $mdjm_page_id;
			update_option( 'mdjm_plugin_pages', $mdjm_page_settings );
			
			$GLOBALS['mdjm_debug']->log_it( 'Quote page created' );
				
		} // create_pages
		
		/*
		 * Create the new default template for online quotations and populate
		 *
		 *
		 *
		 */
		function create_template()	{
			
			$GLOBALS['mdjm_debug']->log_it( 'Adding quote template for version 1.2.3.3' );
			
			include( MDJM_PLUGIN_DIR . '/admin/includes/mdjm-templates.php' );
			
			if( wp_insert_post( $online_quote_template_args ) )
				$GLOBALS['mdjm_debug']->log_it( ' Online Quote template created successfully' );
					
			else
				$GLOBALS['mdjm_debug']->log_it( ' Online Quote template was not created' );		
			
			$GLOBALS['mdjm_debug']->log_it( 'Quote template added' );
			
		} // create_template
		
	} // class MDJM_Upgrade_to_1_2_3_3
	
	new MDJM_Upgrade_to_1_2_3_3();