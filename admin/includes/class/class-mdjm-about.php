<?php
	defined( 'ABSPATH' ) or die( "Direct access to this page is disabled!!!" );
	
/**
 * Class Name: MDJM_About
 * Displays information regarding the plugins current release version
 *
 *
 *
 */
if( !class_exists( 'MDJM_About' ) ) : 
	class MDJM_About	{
		public function __construct()	{
			$this->enqueue();
			
			$this->page_content();
		} // __construct
		
		/**
		 * Enqueue page specific scripts and styles
		 *
		 *
		 *
		 */
		public function enqueue()	{
			wp_enqueue_script( 'youtube-subscribe' );
		}
		
		/**
		 * The header content for the page. This generally remains static
		 *
		 *
		 *
		 */
		public function page_header()	{
			?>
            <style>
			.site-title img	{
				display: block; max-width: 100%; max-height: 60px; height: auto; padding: 0; margin: 0 auto; -webkit-border-radius: 0; border-radius: 0;	
			}
			table { border-spacing: 0.5rem; }
			td {padding-left: 0.5rem; padding-right: 0.5rem; }
			
			</style>
            <div class="wrap">
            <a href="http://www.mydjplanner.co.uk/" target="_blank"><img style="max-height: 80px; height: auto;" src="<?php echo MDJM_PLUGIN_URL . '/admin/images/mdjm_web_header.png'; ?>" alt="<?php _e( 'MDJM Event Management', 'mobile-dj-manager' ); ?>" title="<?php _e( 'MDJM Event Management', 'mobile-dj-manager' ); ?>" /></a>
            <h1><?php printf( __( 'Welcome to MDJM Event Management version %s', 'mobile-dj-manager' ), MDJM_VERSION_NUM ); ?></h1>
            <hr>
            <?php
		} // page_header
		
		/**
		 * The footer content for the page. This generally remains static
		 *
		 *
		 *
		 */
		public function page_footer()	{
			?>
            <hr>
            <h3>Enhance MDJM Event Management</h3>
            <p>Our Premium Plugins enhance the features of the MDJM Event Management Plugin. All premium plugins are provided with a full years updates and support</p>
            <table>
            <tr>
            <td><a href="http://www.mydjplanner.co.uk/shop/mdjm-dynamic-contact-forms/" target="_blank"><img src="http://www.mydjplanner.co.uk/wp-content/uploads/2015/09/MDJM_DCF_Product.jpg" alt="MDJM Dynamic Contact Forms" title="MDJM Dynamic Contact Forms" /></a></td>
            <td><a href="http://www.mydjplanner.co.uk/shop/mdjm-payments/" target="_blank"><img src="http://www.mydjplanner.co.uk/wp-content/uploads/2015/10/MDJM_Payments_Product.jpg" alt="MDJM Google Calendar Sync" title="MDJM Google Calendar Sync" /></td></a>
            <td><a href="http://www.mydjplanner.co.uk/shop/mdjm-google-calendar-sync/" target="_blank"><img src="http://www.mydjplanner.co.uk/wp-content/uploads/2015/10/MDJM_Google_Cal_Product.jpg" alt="MDJM Google Calendar Sync" title="MDJM Google Calendar Sync" /></td></a>
            </tr>
            <tr>
            <td style="text-align:center"><a href="http://www.mydjplanner.co.uk/shop/mdjm-dynamic-contact-forms/" target="_blank" class="button secondary">Buy now</a><br>
				<strong>&pound;35.00</strong>
            </td>
            <td style="text-align:center"><a href="http://www.mydjplanner.co.uk/shop/mdjm-payments/" target="_blank" class="button secondary">Buy now</a><br>
				<strong>&pound;25.00</strong>
            </td>
            <td style="text-align:center"><a href="http://www.mydjplanner.co.uk/shop/mdjm-google-calendar-sync/" target="_blank" class="button secondary">Buy now</a><br>
				<strong>&pound;25.00</strong>
			</td>
            </tr>
            </table>
            <p style="text-align:center"><a class="button-primary" href="<?php echo mdjm_get_admin_page( 'settings' ); ?>">Go to MDJM Settings</a></p>
            </div>
            <?php	
		} // page_footer
		
		/**
		 * The body for the page.
		 *
		 *
		 *
		 */
		public function page_content()	{
			$this->page_header();
			?>
            <ui>
            	<li><strong>New</strong>: Added new currencies for AUS, CAD, NZD and SGD</li>
                <li><strong>New</strong>: Order your event listings by ID, Date, or Value by clicking on the relevant column header</li>
                <li><strong>New</strong>: Order your venue listings by Name, Town or County by clicking on the relevant column header</li>
                <li><strong>New</strong>: Support for MDJM Google Calendar Sync add-on</li>
                <li><strong>New</strong>: Added a few developer hooks and filters</li>
                <li><strong>New</strong>: Added new shortcode {END_DATE} which will display the date on which the event completes in short date format</li>
                <li><strong>New</strong>: Adjusted branding</li>
                <li><strong>Bug Fix</strong>: Fixed availability checker function on MDJM Dashboard</li>
                <li><strong>Bug Fix</strong>: <code>{DJ_NOTES}</code> shortcode was displaying event notes</li>
            </ui>
            <?php
			$this->page_footer();
		} // page_content
		
	} // class MDJM_About
endif;

new MDJM_About();