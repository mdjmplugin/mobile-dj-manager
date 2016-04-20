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

		// Credits Page
		add_dashboard_page(
			__( 'The people that build Easy Digital Downloads', 'mobile-dj-manager' ),
			__( 'The people that build Easy Digital Downloads', 'mobile-dj-manager' ),
			$this->minimum_capability,
			'mdjm-credits',
			array( $this, 'credits_screen' )
		);

		// Now remove them from the menus so plugins that allow customizing the admin menu don't show them
		remove_submenu_page( 'index.php', 'mdjm-about' );
		remove_submenu_page( 'index.php', 'mdjm-changelog' );
		remove_submenu_page( 'index.php', 'mdjm-getting-started' );
		remove_submenu_page( 'index.php', 'mdjm-credits' );
	}

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
	}

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
	}

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
			<a class="nav-tab <?php echo $selected == 'mdjm-credits' ? 'nav-tab-active' : ''; ?>" href="<?php echo esc_url( admin_url( add_query_arg( array( 'page' => 'mdjm-credits' ), 'index.php' ) ) ); ?>">
				<?php _e( 'Credits', 'mobile-dj-manager' ); ?>
			</a>
		</h1>
		<?php
	}

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
	}

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
	}

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
			<p class="about-description"><?php _e( 'Use the tips below to get started using Easy Digital Downloads. You will be up and running in no time!', 'mobile-dj-manager' ); ?></p>

			<div class="changelog">
				<h3><?php _e( 'Creating Your First Download Product', 'mobile-dj-manager' );?></h3>
				<div class="feature-section">
					<div class="feature-section-media">
						<img src="<?php echo MDJM_PLUGIN_URL . 'assets/images/screenshots/edit-download.png'; ?>" class="mdjm-welcome-screenshots"/>
					</div>
					<div class="feature-section-content">
						<h4><a href="<?php echo admin_url( 'post-new.php?post_type=download' ) ?>"><?php printf( __( '%s &rarr; Add New', 'mobile-dj-manager' ), mdjm_get_label_plural() ); ?></a></h4>
						<p><?php printf( __( 'The %s menu is your access point for all aspects of your Easy Digital Downloads product creation and setup. To create your first product, simply click Add New and then fill out the product details.', 'mobile-dj-manager' ), mdjm_get_label_plural() ); ?></p>


						<h4><?php _e( 'Download Files', 'mobile-dj-manager' );?></h4>
						<p><?php _e( 'Uploading the downloadable files is simple. Click <em>Upload File</em> in the Download Files section and choose your download file. To add more than one file, simply click the <em>Add New</em> button.', 'mobile-dj-manager' );?></p>
					</div>
				</div>
			</div>

			<div class="changelog">
				<h3><?php _e( 'Display a Product Grid', 'mobile-dj-manager' );?></h3>
				<div class="feature-section">
					<div class="feature-section-media">
						<img src="<?php echo MDJM_PLUGIN_URL . 'assets/images/screenshots/grid.png'; ?>"/>
					</div>
					<div class="feature-section-content">
						<h4><?php _e( 'Flexible Product Grids','mobile-dj-manager' );?></h4>
						<p><?php _e( 'The [downloads] shortcode will display a product grid that works with any theme, no matter the size. It is even responsive!', 'mobile-dj-manager' );?></p>

						<h4><?php _e( 'Change the Number of Columns', 'mobile-dj-manager' );?></h4>
						<p><?php _e( 'You can easily change the number of columns by adding the columns="x" parameter:', 'mobile-dj-manager' );?></p>
						<p><pre>[downloads columns="4"]</pre></p>

						<h4><?php _e( 'Additional Display Options', 'mobile-dj-manager' ); ?></h4>
						<p><?php printf( __( 'The product grids can be customized in any way you wish and there is <a href="%s">extensive documentation</a> to assist you.', 'mobile-dj-manager' ), 'http://docs.easydigitaldownloads.com/' ); ?></p>
					</div>
				</div>
			</div>

			<div class="changelog">
				<h3><?php _e( 'Purchase Buttons Anywhere', 'mobile-dj-manager' );?></h3>
				<div class="feature-section">
					<div class="feature-section-media">
						<img src="<?php echo MDJM_PLUGIN_URL . 'assets/images/screenshots/purchase-link.png'; ?>"/>
					</div>
					<div class="feature-section-content">
						<h4><?php _e( 'The <em>[purchase_link]</em> Shortcode','mobile-dj-manager' );?></h4>
						<p><?php _e( 'With easily accessible shortcodes to display purchase buttons, you can add a Buy Now or Add to Cart button for any product anywhere on your site in seconds.', 'mobile-dj-manager' );?></p>

						<h4><?php _e( 'Buy Now Buttons', 'mobile-dj-manager' );?></h4>
						<p><?php _e( 'Purchase buttons can behave as either Add to Cart or Buy Now buttons. With Buy Now buttons customers are taken straight to PayPal, giving them the most frictionless purchasing experience possible.', 'mobile-dj-manager' );?></p>
					</div>
				</div>
			</div>

			<div class="changelog">
				<h3><?php _e( 'Need Help?', 'mobile-dj-manager' );?></h3>
				<div class="feature-section two-col">
					<div class="col">
						<h4><?php _e( 'Phenomenal Support','mobile-dj-manager' );?></h4>
						<p><?php _e( 'We do our best to provide the best support we can. If you encounter a problem or have a question, simply open a ticket using our <a href="https://easydigitaldownloads.com/support">support form</a>.', 'mobile-dj-manager' );?></p>
					</div>
					<div class="col">
						<h4><?php _e( 'Need Even Faster Support?', 'mobile-dj-manager' );?></h4>
						<p><?php _e( 'Our <a href="https://easydigitaldownloads.com/support/pricing/">Priority Support</a> system is there for customers that need faster and/or more in-depth assistance.', 'mobile-dj-manager' );?></p>
					</div>
				</div>
			</div>

			<div class="changelog">
				<h3><?php _e( 'Stay Up to Date', 'mobile-dj-manager' );?></h3>
				<div class="feature-section two-col">
					<div class="col">
						<h4><?php _e( 'Get Notified of Extension Releases','mobile-dj-manager' );?></h4>
						<p><?php _e( 'New extensions that make Easy Digital Downloads even more powerful are released nearly every single week. Subscribe to the newsletter to stay up to date with our latest releases. <a href="http://eepurl.com/kaerz" target="_blank">Sign up now</a> to ensure you do not miss a release!', 'mobile-dj-manager' );?></p>
					</div>
					<div class="col">
						<h4><?php _e( 'Get Alerted About New Tutorials', 'mobile-dj-manager' );?></h4>
						<p><?php _e( '<a href="http://eepurl.com/kaerz" target="_blank">Sign up now</a> to hear about the latest tutorial releases that explain how to take Easy Digital Downloads further.', 'mobile-dj-manager' );?></p>
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
	}

	/**
	 * Render Credits Screen
	 *
	 * @access public
	 * @since 1.4
	 * @return void
	 */
	public function credits_screen() {
		?>
		<div class="wrap about-wrap mdjm-about-wrap">
			<?php
				// load welcome message and content tabs
				$this->welcome_message();
				$this->tabs();
			?>
			<p class="about-description"><?php _e( 'MDJM Event Management is created by developers who aim to provide the #1 platform for managing events with WordPress.', 'mobile-dj-manager' ); ?></p>

			<?php echo $this->contributors(); ?>
		</div>
		<?php
	}


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
	}


	/**
	 * Render Contributors List
	 *
	 * @since 1.4
	 * @uses MDJM_Welcome::get_contributors()
	 * @return string $contributor_list HTML formatted list of all the contributors for MDJM
	 */
	public function contributors() {
		$contributors = $this->get_contributors();

		if ( empty( $contributors ) )
			return '';

		$contributor_list = '<ul class="wp-people-group">';

		foreach ( $contributors as $contributor ) {
			$contributor_list .= '<li class="wp-person">';
			$contributor_list .= sprintf( '<a href="%s" title="%s">',
				esc_url( 'https://github.com/' . $contributor->login ),
				esc_html( sprintf( __( 'View %s', 'mobile-dj-manager' ), $contributor->login ) )
			);
			$contributor_list .= sprintf( '<img src="%s" width="64" height="64" class="gravatar" alt="%s" />', esc_url( $contributor->avatar_url ), esc_html( $contributor->login ) );
			$contributor_list .= '</a>';
			$contributor_list .= sprintf( '<a class="web" href="%s">%s</a>', esc_url( 'https://github.com/' . $contributor->login ), esc_html( $contributor->login ) );
			$contributor_list .= '</a>';
			$contributor_list .= '</li>';
		}

		$contributor_list .= '</ul>';

		return $contributor_list;
	}

	/**
	 * Retreive list of contributors from GitHub.
	 *
	 * @access public
	 * @since 1.4
	 * @return array $contributors List of contributors
	 */
	public function get_contributors() {
		$contributors = get_transient( 'mdjm_contributors' );

		if ( false !== $contributors )
			return $contributors;

		$response = wp_remote_get( 'https://api.github.com/repos/mydjplanner/mobile-dj-manager/contributors?per_page=999', array( 'sslverify' => false ) );

		if ( is_wp_error( $response ) || 200 != wp_remote_retrieve_response_code( $response ) )
			return array();

		$contributors = json_decode( wp_remote_retrieve_body( $response ) );

		if ( ! is_array( $contributors ) )
			return array();

		set_transient( 'mdjm_contributors', $contributors, 3600 );

		return $contributors;
	}

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
		if ( ! get_transient( '_mdjm_activation_redirect' ) )
			return;

		// Delete the redirect transient
		delete_transient( '_mdjm_activation_redirect' );

		// Bail if activating from network, or bulk
		if ( is_network_admin() || isset( $_GET['activate-multi'] ) )
			return;

		$upgrade = get_option( 'mdjm_version_upgraded_from' );

		if( ! $upgrade ) { // First time install
			wp_safe_redirect( admin_url( 'index.php?page=mdjm-getting-started' ) ); exit;
		} else { // Update
			wp_safe_redirect( admin_url( 'index.php?page=mdjm-about' ) ); exit;
		}
	}
}
new MDJM_Welcome();
