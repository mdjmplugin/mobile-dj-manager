<?php
	defined( 'ABSPATH' ) or die( "Direct access to this page is disabled!!!" );
	
/**
 * Class Name: MDJM_Reports
 * Reporting class for MDJM
 *
 *
 *
 */
if( !class_exists( 'MDJM_Reports' ) ) : 
	class MDJM_Reports	{
		public function __construct()	{
			add_action( 'admin_enqueue_scripts', array( &$this, 'enqueue_scripts' ) );
		} // __construct
		
		/**
		 * Class controller
		 *
		 *
		 *
		 *
		 */
		function init()	{
				
		} // init
		
		/**
		 * Enqueue scripts for Google Charts
		 *
		 *
		 *
		 */
		function enqueue_scripts()	{
			wp_register_script(
				'mdjm_google_api',
				'https://www.google.com/jsapi', 
                array( 'jquery' ),
				null,
				false );
			
			wp_register_script(
				'mdjm_chart_script',
				MDJM_PLUGIN_URL . '/admin/includes/js/chart.js' );
			
			wp_enqueue_script( 'mdjm_google_api' );
			wp_enqueue_script( 'mdjm_chart_script' );
		} // enqueue_scripts
		
		/**
		 * Page header
		 *
		 *
		 *
		 *
		 */
		function page_header()	{
			?>
            <div class="wrap">
				<div id="icon-themes" class="icon32"></div>
                <h1 class="nav-tab-wrapper">
                    <a href="financials" class="nav-tab"><?php _e( 'Financials', 'mobile-dj-manager' ); ?></a>
                    <a href="events" class="nav-tab"><?php _e( 'Events', 'mobile-dj-manager' ); ?></a>
                </h2>
            <?php
		} // page_header
		
		/**
		 * Page footer
		 *
		 *
		 *
		 */
		function page_footer()	{
			?>
			</div>
            <?php
		} // page_footer
	} // class MDJM_Reports
endif;