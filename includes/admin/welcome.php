<?php
/**
 * Weclome Page Class
 *
 * @package     MDJM
 * @subpackage  Admin/Welcome
 * @copyright   Copyright (c) 2016, Mike Howard
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.3
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) )
	exit;

/**
 * MDJM_Welcome Class
 *
 * A general class for About and Credits page.
 *
 * @since	1.3
 */
class MDJM_Welcome {

	/**
	 * @var string The capability users should have to view the page
	 */
	public $minimum_capability = 'manage_options';

	/**
	 * Get things started
	 *
	 * @since	1.3
	 */
	public function __construct() {
		add_action( 'admin_menu', array( $this, 'admin_menus') );
		add_action( 'admin_head', array( $this, 'admin_head' ) );
		add_action( 'admin_init', array( $this, 'welcome'    ) );
	}

	/**
	 * Register the Dashboard Pages which are later hidden but these pages
	 * are used to render the Welcome and Credits pages.
	 *
	 * @access public
	 * @since 1.3
	 * @return void
	 */
	public function admin_menus() {
		// About Page
		add_dashboard_page(
			__( 'Welcome to MDJM Event Management', 'mobile-dj-manager' ),
			__( 'Welcome to MDJM Event Management', 'mobile-dj-manager' ),
			$this->minimum_capability,
			'mdjm-about',
			array( $this, 'about_screen' )
		);

		// Changelog Page
		add_dashboard_page(
			__( 'EMDJM Event Management Changelog', 'mobile-dj-manager' ),
			__( 'MDJM Event Management Changelog', 'mobile-dj-manager' ),
			$this->minimum_capability,
			'mdjm-changelog',
			array( $this, 'changelog_screen' )
		);

		// Getting Started Page
		add_dashboard_page(
			__( 'Getting started with MDJM Event Managements', 'mobile-dj-manager' ),
			__( 'Getting started with MDJM Event Management', 'mobile-dj-manager' ),
			$this->minimum_capability,
			'mdjm-getting-started',
			array( $this, 'getting_started_screen' )
		);

		// Now remove them from the menus so plugins that allow customizing the admin menu don't show them
		remove_submenu_page( 'index.php', 'mdjm-about' );
		remove_submenu_page( 'index.php', 'mdjm-changelog' );
		remove_submenu_page( 'index.php', 'mdjm-getting-started' );

	} // admin_menus

	/**
	 * Hide Individual Dashboard Pages
	 *
	 * @access	public
	 * @since	1.3
	 * @return	void
	 */
	public function admin_head() {
		?>
		<style type="text/css" media="screen">
			/*<![CDATA[*/
			.mdjm-about-wrap .mdjm-badge { float: right; border-radius: 4px; margin: 0 0 15px 15px; max-width: 200px; }
			.mdjm-about-wrap #mdjm-header { margin-bottom: 15px; }
			.mdjm-about-wrap #mdjm-header h1 { margin-bottom: 15px !important; }
			.mdjm-about-wrap .about-text { margin: 0 0 15px; max-width: 670px; }
			.mdjm-about-wrap .feature-section { margin-top: 20px; }
			.mdjm-about-wrap .feature-section-content,
			.mdjm-about-wrap .feature-section-media { width: 50%; box-sizing: border-box; }
			.mdjm-about-wrap .feature-section-content { float: left; padding-right: 50px; }
			.mdjm-about-wrap .feature-section-content h4 { margin: 0 0 1em; }
			.mdjm-about-wrap .feature-section-media { float: right; text-align: right; margin-bottom: 20px; }
			.mdjm-about-wrap .feature-section-media img { border: 1px solid #ddd; }
			.mdjm-about-wrap .feature-section:not(.under-the-hood) .col { margin-top: 0; }
			/* responsive */
			@media all and ( max-width: 782px ) {
				.mdjm-about-wrap .feature-section-content,
				.mdjm-about-wrap .feature-section-media { float: none; padding-right: 0; width: 100%; text-align: left; }
				.mdjm-about-wrap .feature-section-media img { float: none; margin: 0 0 20px; }
			}
			/*]]>*/
		</style>
		<?php
	} // admin_head

	/**
	 * Welcome message
	 *
	 * @access public
	 * @since 2.5
	 * @return void
	 */
	public function welcome_message() {
		list( $display_version ) = explode( '-', MDJM_VERSION_NUM );
		?>
		<div id="mdjm-header">
			<img class="mdjm-badge" src="<?php echo MDJM_PLUGIN_URL . '/assets/images/mdjm_web_header.png'; ?>" alt="<?php _e( 'MDJM Event Management', 'mobile-dj-manager' ); ?>" / >
			<h1><?php printf( __( 'Welcome to MDJM Event Management %s', 'mobile-dj-manager' ), $display_version ); ?></h1>
			<p class="about-text">
				<?php _e( 'Thank you for updating to the latest version!', 'mobile-dj-manager' ); ?>
                <br />
                <?php printf( __( 'MDJM Event Management %s is ready to make your events business even more efficient!', 'mobile-dj-manager' ), $display_version ); ?>
			</p>
		</div>
		<?php
	} // welcome_message

	/**
	 * Navigation tabs
	 *
	 * @access	public
	 * @since	1.3
	 * @return	void
	 */
	public function tabs() {
		$selected = isset( $_GET['page'] ) ? $_GET['page'] : 'mdjm-about';
		?>
		<h1 class="nav-tab-wrapper">
			<a class="nav-tab <?php echo $selected == 'mdjm-about' ? 'nav-tab-active' : ''; ?>" href="<?php echo esc_url( admin_url( add_query_arg( array( 'page' => 'mdjm-about' ), 'index.php' ) ) ); ?>">
				<?php _e( "What's New", 'mobile-dj-manager' ); ?>
			</a>
			<a class="nav-tab <?php echo $selected == 'mdjm-getting-started' ? 'nav-tab-active' : ''; ?>" href="<?php echo esc_url( admin_url( add_query_arg( array( 'page' => 'mdjm-getting-started' ), 'index.php' ) ) ); ?>">
				<?php _e( 'Getting Started', 'mobile-dj-manager' ); ?>
			</a>
		</h1>
		<?php
	} // tabs

	/**
	 * Render About Screen
	 *
	 * @access public
	 * @since 1.4
	 * @return void
	 */
	public function about_screen() {
		?>
		<div class="wrap about-wrap mdjm-about-wrap">
			<?php
				// load welcome message and content tabs
				$this->welcome_message();
				$this->tabs();
			?>
			<div class="changelog">
				<h3><?php _e( 'Employee Management', 'mobile-dj-manager' );?></h3>
				<div class="feature-section">
					<div class="feature-section-media">
						<img src="<?php echo MDJM_PLUGIN_URL . '/assets/images/screenshots/13-employee-list.png'; ?>"/>
					</div>
					<div class="feature-section-content">
						<p><?php _e( 'With MDJM Event Management version 1.3, you now have greater management of your employees.', 'mobile-dj-manager' );?></p>

						<h4><?php _e( 'An Improved Interface', 'mobile-dj-manager' );?></h4>
						<p><?php _e( 'The intuitive employee interface enables you to easily manage your employees, add  new or remove existing, change their role, or assign additional roles.', 'mobile-dj-manager' );?></p>

						<h4><?php _e( 'Granular Permission Management', 'mobile-dj-manager' );?></h4>
						<p><?php _e( 'Determine which actions each employee role is able to fulfill. For example, maybe you have an admin who needs to be able to view all events and employees to help with logistics, but not edit them. Or you want your accountant to be able to read all transactions but not see anything else.', 'mobile-dj-manager' );?></p>
                        <p><?php _e( 'Additionally, users assigned the <em>Administrator</em> role are no longer assumed to be employees unless you specifically specify that they are.', 'mobile-dj-manager' );?></p>

					</div>
				</div>
			</div>

			<div class="changelog">
				<h3><?php _e( 'Theme Templates', 'mobile-dj-manager' );?></h3>
				<div class="feature-section">
					<div class="feature-section-media">
						<img src="<?php echo MDJM_PLUGIN_URL . '/assets/images/screenshots/13-mdjm-templates.png'; ?>"/>
					</div>
					<div class="feature-section-content">
						<p><?php printf( __( 'MDJM Event Management version 1.3 enables greater customisation of %s pages.', 'mobile-dj-manager' ), mdjm_get_option( 'app_name', __( 'Client Zone', 'mobile-dj-manager' ) ) ); ?></p>

						<p><?php _e( 'The settings options which only allowed text customisations have been removed. Instead, you can now copy template files to your [child] theme directory and fully customise their layout and content as much as you need to in order to make them fit in better with the design of your website.', 'mobile-dj-manager' ); ?></p>
                        <p><?php _e( "Use a child theme and you won't have to worry about your changes being overwritten when the MDJM plugin is update or your currently active theme is updated.", 'mobile-dj-manager' ); ?></p>
                        <p><?php _e( 'To make customisations even easier, all MDJM content tags are fully supported and if you use child themes, you can ensure that any changes you make are never overwritten with plugin or theme updates.', 'mobile-dj-manager' ); ?></p>
					</div>
				</div>
			</div>

			<div class="changelog">
				<h3><?php _e( 'Easily Accessible Statistics', 'mobile-dj-manager' );?></h3>
				<div class="feature-section">
					<div class="feature-section-media">
						<img src="<?php echo MDJM_PLUGIN_URL . '/assets/images/screenshots/13-dashboard-overview-widget.png'; ?>" class="mdjm-welcome-screenshots"/>
					</div>
					<div class="feature-section-content">
						<p><?php _e( 'From the moment you login you have important statistics visible to advise you how you are performing Month to Date, Year to Date and in comparison to the previous year.', 'mobile-dj-manager' );?></p>
                        <p><?php _e( 'The intuitive dashboard widget displays the number of enquiries received and converted as well as the number of events completed within these timeframes and in addition, the amount your business has earned is readily available to you.', 'mobile-dj-manager' );?></p>
					</div>
				</div>
			</div>

			<div class="changelog">
				<h3><?php _e( 'Additional Updates', 'mobile-dj-manager' );?></h3>
                <hr />
				<div class="feature-section three-col">
					<div class="col">
						<h4><?php _e( 'More Content Tags', 'mobile-dj-manager' );?></h4>
						<p><?php _e( 'Even more content tags added to make displaying dynamic content quick and easy on any page.', 'mobile-dj-manager' );?></p>
					</div>
                    <div class="col">
						<h4><?php _e( 'Better use of WordPress Taxonomies', 'mobile-dj-manager' );?></h4>
						<p><?php _e( 'Playlist Categories and Enquiry Sources are now custom taxonomies enabling better reporting.' ,'mobile-dj-manager' );?></p>
					</div>
					<div class="col">
						<h4><?php _e( 'Developer Friendly Code', 'mobile-dj-manager' );?></h4>
						<p><?php _e( 'Well formatted and documented code with a large number of hooks to enable developer customisations and integrations.', 'mobile-dj-manager' );?></p>
					</div>
					<div class="clear">
						<div class="col">
							<h4><?php _e( 'Settings API', 'mobile-dj-manager' );?></h4>
							<p><?php _e( 'Hook into our settings API to easily add settings for your MDJM extension with just a few lines of code.', 'mobile-dj-manager' );?></p>
						</div>
						<div class="col">
							<h4><?php _e( 'Playlist Entries are Posts', 'mobile-dj-manager' );?></h4>
							<p><?php _e( "We've removed the custom database table that stored playlist entries and imported all your entries as WordPress posts for better integration.", 'mobile-dj-manager' );?></p>
						</div>
						<div class="col">
							<h4><?php _e( 'Improved Event Filters', 'mobile-dj-manager' );?></h4>
							<p><?php _e( 'Easily filter your events listings by date, type, client, and employee.' ,'mobile-dj-manager' );?></p>
						</div>
					</div>
				</div>
			</div>

			<div class="return-to-dashboard">
				<a href="<?php echo esc_url( admin_url( add_query_arg( array( 'page' => 'mdjm-settings' ), 'admin.php' ) ) ); ?>"><?php _e( 'Go to MDJM Event Management Settings', 'mobile-dj-manager' ); ?></a> &middot;
				<a href="<?php echo esc_url( admin_url( add_query_arg( array( 'page' => 'mdjm-changelog' ), 'index.php' ) ) ); ?>"><?php _e( 'View the Full Changelog', 'mobile-dj-manager' ); ?></a>
			</div>
		</div>
		<?php
	} // about_screen

	/**
	 * Render Changelog Screen
	 *
	 * @access public
	 * @since 2.0.3
	 * @return void
	 */
	public function changelog_screen() {
		?>
		<div class="wrap about-wrap mdjm-about-wrap">
			<?php
				// load welcome message and content tabs
				$this->welcome_message();
				$this->tabs();
			?>
			<div class="changelog">
				<h3><?php _e( 'Full Changelog', 'mobile-dj-manager' );?></h3>

				<div class="feature-section">
					<?php echo $this->parse_readme(); ?>
				</div>
			</div>

			<div class="return-to-dashboard">
				<a href="<?php echo esc_url( admin_url( add_query_arg( array( 'post_type' => 'mdjm-event', 'page' => 'mdjm-settings' ), 'edit.php' ) ) ); ?>"><?php _e( 'Go to MDJM Event Management Settings', 'mobile-dj-manager' ); ?></a>
			</div>
		</div>
		<?php
	} // changelog_screen

	/**
	 * Render Getting Started Screen
	 *
	 * @access public
	 * @since 1.9
	 * @return void
	 */
	public function getting_started_screen() {
		?>
		<div class="wrap about-wrap mdjm-about-wrap">
			<?php
				// load welcome message and content tabs
				$this->welcome_message();
				$this->tabs();
			?>
			<p class="about-description"><?php _e( "Now that MDJM Event Management is installed, you're ready to get started. It works out of the box, but there are some customisations you can configure to match your business needs.", 'mobile-dj-manager' ); ?></p>

			<div class="changelog">
				<h3><?php printf( __( 'Creating Your First %s', 'mobile-dj-manager' ), mdjm_get_label_singular() );?></h3>
				<div class="feature-section">
					<div class="feature-section-media">
						<img src="<?php echo MDJM_PLUGIN_URL . '/assets/images/screenshots/mdjm-first-event.png'; ?>" class="mdjm-welcome-screenshots"/>
					</div>
					<div class="feature-section-content">
						<h4><a href="<?php echo admin_url( 'post-new.php?post_type=mdjm-event' ); ?>"><?php printf( __( 'MDJM %s &rarr; Create %s', 'mobile-dj-manager' ), mdjm_get_label_plural(), mdjm_get_label_singular() ); ?></a></h4>
						<p><?php printf( __( 'The MDJM %1$s menu is your access point to all aspects of your %2$s creation and setup. To create your first %2$s, simply click Add New and then fill out the %2$s details.', 'mobile-dj-manager' ), mdjm_get_label_plural(), mdjm_get_label_singular( true ) ); ?></p>

						<h4><?php _e( 'Create a Client', 'mobile-dj-manager' ); ?></h4>
						<p><?php printf( __( 'Create clients directly from the %s screen by selected <strong>Add New Client</strong> from the <em>Select Client</em> dropdown to reveal a few additional fields', 'mobile-dj-manager' ), mdjm_get_label_singular() ); ?>

						<h4><?php printf( __( 'Add %s Types', 'mobile-dj-manager' ), mdjm_get_label_singular() ); ?></h4>
						<p><?php printf( __( 'If the %1$s type does not exist for the %1$s you are creating, click the <em>Add New</em> link next to the %2$s Type dropdown, enter the %2$s Type name in the text box that is revealed and click <em>Add</em>.<br />To manage all %2$s Types, go to <a href="%4$s">MDJM %3$s &rarr; %2$s Types</a>.', 'mobile-dj-manager' ), mdjm_get_label_singular( true ), mdjm_get_label_singular(), mdjm_get_label_plural(), admin_url( 'edit-tags.php?taxonomy=event-types&post_type=mdjm-event' ) ); ?></p>
					</div>
				</div>
			</div>

			<div class="changelog">
				<h3><?php _e( 'Setup Templates for Complete Automation', 'mobile-dj-manager' );?></h3>
				<div class="feature-section">
					<div class="feature-section-media">
						<img src="<?php echo MDJM_PLUGIN_URL . '/assets/images/screenshots/mdjm-edit-template.png'; ?>"/>
					</div>
					<div class="feature-section-content">
						<h4><?php _e( 'Email Templates','mobile-dj-manager' ); ?></h4>
						<p><?php printf( __( 'Email templates can be configured to be sent automatically during an %1$s status change. Supporting our vast collection of <a href="%2$s" target="_blank">content tags</a> each email can be completley customised and tailored to the %1$s and client details.', 'mobile-dj-manager' ), mdjm_get_label_singular( true ), 'http://mdjm.co.uk/docs/content-tags/' ); ?></p>
                        <p><?php _e( 'With email tracking enabled, you can even be sure that your client received your email and know when they have read it.', 'mobile-dj-manager' ); ?></p>

						<h4><?php _e( 'Contract Templates', 'mobile-dj-manager' ); ?></h4>
						<p><?php printf( __( 'Create contract templates that be assigned to your %s. Clients will be able to review and <strong>Digitally Sign</strong> the contract via the %2$s.', 'mobile-dj-manager' ), mdjm_get_label_singular(), mdjm_get_option( 'app_name', __( 'Client Zone', 'mobile-dj-manager' ) ) ); ?></p>
					</div>
				</div>
			</div>

			<div class="changelog">
				<h3><?php printf( __( 'Create %s Packages &amp; Add-ons', 'mobile-dj-manager' ), mdjm_get_label_singular() );?></h3>
				<div class="feature-section">
					<div class="feature-section-media">
						<img src="<?php echo MDJM_PLUGIN_URL . '/assets/images/screenshots/mdjm-edit-packages.png'; ?>"/>
					</div>
					<div class="feature-section-content">
						<h4><?php printf( __( '%1$s Packages','mobile-dj-manager' ), mdjm_get_label_singular() ); ?></h4>
						<p><?php printf( __( 'Packages are a pre-defined collection of add-ons that you can offer to your clients for their %s. Define a price for the package and upon selection, the %1$s %2$s and %3$s will be automatically and dynamically re-calculated. Add-ons included within the package, will no longer be available for selection within the add-ons list for this %2$s.', 'mobile-dj-manager' ), mdjm_get_label_singular( true ), mdjm_get_balance_label(), mdjm_get_deposit_label() ); ?></p>

						<h4><?php _e( 'Add-ons', 'mobile-dj-manager' );?></h4>
						<p><?php printf( __( 'Add-ons are additional equipment items that can be selected for an %1$s. Each add-on is assigned an individual price and when selected the %1$s %2$s and %3$s are automatically and dynamically re-calculated.', 'mobile-dj-manager' ), mdjm_get_label_singular( true ), mdjm_get_balance_label(), mdjm_get_deposit_label() );?></p>
                        <p><?php printf( __( 'Once you have enabled %1$s Packages &amp; Add-ons within the <a href="%3$s">MDJM %2$s Settings page</a>, manage them within <a href="%4$s">MDJM %2$s &rarr; Equipment Packages</a>.', 'mobile-dj-manager' ), mdjm_get_label_singular( true ), mdjm_get_label_plural(), admin_url( 'admin.php?page=mdjm-settings&tab=events' ), admin_url( 'admin.php?&page=mdjm-packages' ) ); ?></p>
					</div>
				</div>
			</div>

			<div class="changelog">
				<h3><?php _e( 'Event More Features', 'mobile-dj-manager' );?></h3>
				<div class="feature-section two-col">
					<div class="col">
						<h4><?php _e( 'Integrated Client Portal','mobile-dj-manager' );?></h4>
						<p><?php printf( __( 'Known as the <em>Client Zone</em> by default, a password protected portal is available to your clients where they can review their %1$s, view and accept your quote, digitally sign their contract, and manage their %1$s playlist. All %2$s pages use a template system and are fully customisable.', 'mobile-dj-manager' ), mdjm_get_label_singular( true ), mdjm_get_option( 'app_name', __( 'Client Zone', 'mobile-dj-manager' ) ) ); ?></p>
					</div>
					<div class="col">
						<h4><?php _e( 'Digitally Sign Contracts', 'mobile-dj-manager' );?></h4>
						<p><?php printf( __( 'Via the %s, clients are able to review and digitally sign their %s contract. Signing requires confirmation of their name and password for verification to maintain security.', 'mobile-dj-manager' ), mdjm_get_option( 'app_name', __( 'Client Zone', 'mobile-dj-manager' ) ), mdjm_get_label_singular() );?></p>
					</div>
				</div>
                <div class="feature-section two-col">
					<div class="col">
						<h4><?php _e( 'Transaction Logging','mobile-dj-manager' );?></h4>
						<p><?php printf( __( 'Log all payments your business receives and all expenses you have with the MDJM Event Management Transactions system. Instantly know how profitable your %s are as well as how much money your company has made over differing periods of time.', 'mobile-dj-manager' ), mdjm_get_label_plural() ); ?></p>
					</div>
					<div class="col">
						<h4><?php _e( 'Multi Employee Aware', 'mobile-dj-manager' );?></h4>
						<p><?php printf( __( 'MDJM Event Management supports as many employees as you need at no additional cost. Easily create new employees, set permissions for them to ensure they only have access to what they need, and then assign as many employees to an %s as you need.', 'mobile-dj-manager' ), mdjm_get_label_singular( true ) );?></p>
					</div>
				</div>
			</div>

			<div class="changelog">
				<h3><?php _e( 'Need Help?', 'mobile-dj-manager' );?></h3>
				<div class="feature-section two-col">
					<div class="col">
						<h4><?php _e( 'Excellent Support','mobile-dj-manager' );?></h4>
						<p><?php _e( 'We pride ourselves on our level of support and excellent response times. If you are experiencing an issue, submit a support ticket and we will respond quickly.', 'mobile-dj-manager' );?></p>
					</div>
					<div class="col">
						<h4><?php _e( 'Join our Facebook User Group', 'mobile-dj-manager' );?></h4>
						<p><?php _e( 'Our <a href="https://www.facebook.com/groups/mobile-dj-manager/" target="_blank">MDJM Facebook User Group</a> is a great way to exchange knowledge with other users and gain tips for use.', 'mobile-dj-manager' );?></p>
					</div>
				</div>
			</div>

			<div class="changelog">
				<h3><?php _e( 'Stay Up to Date', 'mobile-dj-manager' );?></h3>
				<div class="feature-section two-col">
					<div class="col">
						<h4><?php _e( 'Get Notified of Add-on Releases','mobile-dj-manager' );?></h4>
						<p><?php _e( 'New add-ons make MDJM Event Management even more powerful. Subscribe to the newsletter to stay up to date with our latest releases. <a href="http://eepurl.com/bTRkZj" target="_blank">Sign up now</a> to ensure you do not miss a release!', 'mobile-dj-manager' );?></p>
					</div>
					<div class="col">
						<h4><?php _e( 'Get Alerted About New Tutorials', 'mobile-dj-manager' );?></h4>
						<p><?php _e( '<a href="http://eepurl.com/bTRkZj" target="_blank">Sign up now</a> to hear about the latest tutorial releases that explain how to take MDJM Event Management further.', 'mobile-dj-manager' );?></p>
					</div>
				</div>
			</div>

			<div class="changelog">
				<h3><?php _e( 'Extensions', 'mobile-dj-manager' );?></h3>
				<div class="feature-section two-col">
					<div class="col">
						<h4><?php _e( 'A Growing List of Add-ons','mobile-dj-manager' );?></h4>
						<p><?php _e( 'Add-on plugins are available that greatly extend the default functionality of MDJM Event Management. There are extensions to further automate MDJM Event Management, payment processing and calendar syncronisation.', 'mobile-dj-manager' );?></p>
					</div>
					<div class="col">
						<h4><?php _e( 'Visit the Add-ons Store', 'mobile-dj-manager' );?></h4>
						<p><?php printf( __( '<a href="%s" target="_blank">The Add-ons store</a> has a list of all available extensions, including convenient category filters so you can find exactly what you are looking for.', 'mobile-dj-manager' ), 'http://mdjm.co.uk/add-ons' ); ?></p>
					</div>
				</div>
			</div>
		</div>
		<?php
	} // getting_started_screen

	/**
	 * Parse the MDJM readme.txt file
	 *
	 * @since 2.0.3
	 * @return string $readme HTML formatted readme file
	 */
	public function parse_readme() {
		$file = file_exists( MDJM_PLUGIN_DIR . '/readme.txt' ) ? MDJM_PLUGIN_DIR . '/readme.txt' : null;

		if ( ! $file ) {
			$readme = '<p>' . __( 'No valid changelog was found.', 'mobile-dj-manager' ) . '</p>';
		} else {
			$readme = file_get_contents( $file );
			$readme = nl2br( esc_html( $readme ) );
			$readme = explode( '== Changelog ==', $readme );
			$readme = end( $readme );

			$readme = preg_replace( '/`(.*?)`/', '<code>\\1</code>', $readme );
			$readme = preg_replace( '/[\040]\*\*(.*?)\*\*/', ' <strong>\\1</strong>', $readme );
			$readme = preg_replace( '/[\040]\*(.*?)\*/', ' <em>\\1</em>', $readme );
			$readme = preg_replace( '/= (.*?) =/', '<h4>\\1</h4>', $readme );
			$readme = preg_replace( '/\[(.*?)\]\((.*?)\)/', '<a href="\\2">\\1</a>', $readme );
		}

		return $readme;
	} // parse_readme

	/**
	 * Sends user to the Welcome page on first activation of MDJM as well as each
	 * time MDJM is upgraded to a new version
	 *
	 * @access public
	 * @since 1.3
	 * @return void
	 */
	public function welcome() {
		// Bail if no activation redirect
		if ( ! get_transient( '_mdjm_activation_redirect' ) )	{
			return;
		}

		// Delete the redirect transient
		delete_transient( '_mdjm_activation_redirect' );

		// Bail if activating from network, or bulk
		if ( is_network_admin() || isset( $_GET['activate-multi'] ) )	{
			return;
		}

		$upgrade = get_option( 'mdjm_version_upgraded_from' );

		if( ! $upgrade ) { // First time install
			wp_safe_redirect( admin_url( 'index.php?page=mdjm-getting-started' ) ); exit;
		} else { // Update
			wp_safe_redirect( admin_url( 'index.php?page=mdjm-about' ) ); exit;
		}
	} // welcome
}
new MDJM_Welcome();
