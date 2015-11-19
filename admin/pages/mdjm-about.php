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
			.site-title	{
				color: #FF9900;	
			}
			.site-title img	{
				display: block; max-width: 100%; max-height: 60px; height: auto; padding: 0; margin: 0 auto; -webkit-border-radius: 0; border-radius: 0;	
			}
			table { border-spacing: 0.5rem; }
			td {padding-left: 0.5rem; padding-right: 0.5rem; }
			
			</style>
            <div class="wrap">
            <a href="http://www.mydjplanner.co.uk/" target="_blank"><img style="max-height: 80px; height: auto;" src="<?php echo MDJM_PLUGIN_URL . '../images/mdjm_web_header.png'; ?>" alt="<?php _e( 'MDJM Event Management', 'mobile-dj-manager' ); ?>" title="<?php _e( 'MDJM Event Management', 'mobile-dj-manager' ); ?>" /></a>
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
            <h3><?php _e( 'Have you tried the MDJM Event Management extensions', 'mobile-dj-manager' ); ?>?</h3>
            <p><?php _e( 'Extensions enhance the features of the MDJM Event Management Plugin. All paid extensions are provided with a full years updates and support', 'mobile-dj-manager' ); ?>.</p>
            <table>
            <tr>
            <td><a href="<?php echo admin_url( 'plugin-install.php?tab=search&s=mdjm-to-pdf' ); ?>"><img src="http://www.mydjplanner.co.uk/wp-content/uploads/2014/11/MDJM_to_PDF_Product.jpg" alt="MDJM to PDF" title="MDJM to PDF" /></a></td>
            <td><a href="http://www.mydjplanner.co.uk/portfolio/dynamic-contact-forms/" target="_blank"><img src="http://www.mydjplanner.co.uk/wp-content/uploads/2015/09/MDJM_DCF_Product.jpg" alt="MDJM Dynamic Contact Forms" title="MDJM Dynamic Contact Forms" /></a></td>
            <td><a href="http://www.mydjplanner.co.uk/portfolio/google-calendar-sync/" target="_blank"><img src="http://www.mydjplanner.co.uk/wp-content/uploads/2015/10/MDJM_Google_Cal_Product.jpg" alt="MDJM Google Calendar Sync" title="MDJM Google Calendar Sync" /></a></td>
            </tr>
            <tr>
            <td style="text-align:center"><a href="<?php echo admin_url( 'plugin-install.php?tab=search&s=mdjm-to-pdf' ); ?>" class="button secondary">Install now</a><br>
            <strong>FREE</strong>
            </td>
            <td style="text-align:center"><a href="http://www.mydjplanner.co.uk/portfolio/dynamic-contact-forms/" target="_blank" class="button secondary">Buy now</a><br>
            <strong>&pound;35.00</strong>
            </td>
            <td style="text-align:center"><a href="http://www.mydjplanner.co.uk/portfolio/google-calendar-sync/" target="_blank" class="button secondary">Buy now</a><br>
            <strong>&pound;25.00</strong>
            </td>
            </tr>
            <tr>
            <td><a href="http://www.mydjplanner.co.uk/portfolio/payments/" target="_blank"><img src="http://www.mydjplanner.co.uk/wp-content/uploads/2015/10/MDJM_Payments_Product.jpg" alt="MDJM Google Calendar Sync" title="MDJM Google Calendar Sync" /></a></td>
            <td>&nbsp;</td>
            <td>&nbsp;</td>
            </tr>
            <tr>
            <td style="text-align:center"><a href="http://www.mydjplanner.co.uk/portfolio/payments/" target="_blank" class="button secondary">Buy now</a><br>
            <strong>&pound;25.00</strong>
            </td>
            <td style="text-align:center">&nbsp;</td>
            <td style="text-align:center">&nbsp;</td>
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
            <h3 class="site-title">Version 1.2.6.1 - XXXXXXX 2015</h3>
            <ui>
            	<li><strong>New</strong>: Display name for <?php _e( MDJM_DJ ); ?> is now updated within user roles</li>
                <li><strong>New</strong>: Development hooks added to event post metaboxes</li>
                <li><strong>General</strong>: Client fields settings page is now translation ready</li>
                <li><strong>General</strong>: Updated the uninstallation procedure</li>
                <li><strong>Bug Fix</strong>: Adjusted the order in which the deposit and balance status' are updated for events so as to ensure manual payments are captured during manual event update</li>
                <li><strong>Bug Fix</strong>: Depending on PHP notice display settings, warning may be displayed on front end when client clicks <code>Book this Event</code></li>
            </ui>
            <h3 class="site-title">Version 1.2.6 - 31st October 2015</h3>
            <ui>
            	<li><strong>New</strong>: <code>{PAYMENT_HISTORY}</code> client shortcode added. Displays a simple list of client payments for the current event</li>
                <li><strong>New</strong>: Click the <code>Details</code> button on the event screen to reveal additional information</li>
                <li><strong>General</strong>: Added Domain Path for translations</li> 
                <li><strong>General</strong>: Removed deprecated journal DB table</li>
                <li><strong>General</strong>: Preparation for MDJM to PDF</li>
            	<li><strong>General</strong>: Rebranded to <code>MDJM Event Management</code> on the plugin screen</li>
                <li><strong>General</strong>: Rebranded to <code>MDJM Events</code> on the menu and admin bar</li>
                <li><strong>Bug Fix</strong>: <?php echo MDJM_APP; ?> playlist now displays guest entries and which guest added</li>
                <li><strong>Bug Fix</strong>: <?php echo MDJM_APP; ?> playlist now displays content from the <code>info</code></li>
                <li><strong>Bug Fix</strong>: Removed blank line after Event End Date shortcode in list of shortcodes</li>
                <li><strong>Bug Fix</strong>: DB Backup time was always 00:00</li>
                <li><strong>Bug Fix</strong>: <?php echo MDJM_APP; ?> was logging an error when booking was accepted</li>
                <li><strong>Bug Fix</strong>: Scheduled task was logging an error in the log file due to missing variable</li>
                <li><strong>Bug Fix</strong>: If no events exist, it was possible an error would be written to the log file relating to the <code>Event Type</code> filter</li>
                <li><strong>Bug Fix</strong>: Installation was trying to create a DB table that is no longer required and could possibly generate an on screen warning notification</li>
            </ui>
            <?php
			$this->page_footer();
		} // page_content
		
	} // class MDJM_About
endif;

new MDJM_About();