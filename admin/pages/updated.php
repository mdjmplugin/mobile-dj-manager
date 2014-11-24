<?php
	defined('ABSPATH') or die("Direct access to this page is disabled!!!");
	if ( !current_user_can( 'manage_options' ) && !current_user_can( 'manage_mdjm' ) )  {
		wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
	}
/*
* updated.php
* 12/11/2014
* since 0.9.3
* Displays overview of changes in updated version
*/
	function f_mdjm_updated_header()	{
		?>
        <div class="wrap">
        <table class="widefat">
        <tr>
        <td align="center"><img src="<?php echo WPMDJM_PLUGIN_URL . '/admin/images/banner-772x250.png'; ?>" width="772" height="250" /></td>
        </tr>
        <tr>
        <td align="center" style="font-size:24px; font-weight:bold; color:#F90">Welcome to Mobile DJ Manager version <?php echo WPMDJM_VERSION_NUM; ?></td>
        </tr>
        </table>
        <table>
        <tr>
        <td>
        <table class="widefat">
        <tr>
        <td style="background-color:#F90; font-size:16px; color:#FFF; font-weight:bold">So... What's new?</font></td>
        </tr>
        <?php	
	} // f_mdjm_updated_header
	
	function f_mdjm_updated_footer()	{
		?>
        <td width="30%" valign="top">
        <table class="widefat">
        <tr>
        <td style="background-color:#F90; font-size:16px; color:#FFF; font-weight:bold">Help &amp; Support</td>
        </tr>
        <tr>
        <td><a href="http://mydjplanner.co.uk/support/user-guides/" title="Mobile DJ Manager User Guides" target="_blank">View the User Guides</a></td>
        </tr>
        <tr>
        <td><a href="http://mydjplanner.co.uk/forums/" title="Mobile DJ Manager Support Forums" target="_blank">Visit the Support Forums</a></td>
        </tr>
        <tr>
        <td><a href="http://www.mydjplanner.co.uk/forums/forum/feature-requests/" title="Request New Feature" target="_blank">Request a new Feature</a></td>
        </tr>
        </table>
        </td>
        </tr>
        </table>
        </div>
        <?php
	} // f_mdjm_updated_footer
	
/**************************************************
				VERSION 0.9.3
**************************************************/
	function f_mdjm_updated_to_0_9_3()	{
		?>
        <tr>
        <td><font style="font-size:14px; font-weight:bold; color:#F90">Email Templates</font><br>
		Since the first release of MDJM only 4 email templates were available for you to use. Even though these were fully custimisable, it was clear that more were needed so in this version, we changed the way in which we provide templates...<br>
		From version 0.9.3 templates are managed as posts in the same way in which Contracts have always been managed. A new menu option called <a href="<?php echo admin_url( 'edit.php?post_type=email_template' ); ?>" title="Email Templates">Email Templates</a> has been added.<br>
		You can now edit existing templates as well as create new templates of your own. Give them a name, add the content, including MDJM shortcodes, and then head over to the all new Scheduler section (also introduced in 0.9.3) to dictate when they are used.<br>
		Don't worry... we imported the any customisations you may have made to the old email templates but we do recommend you <a href="<?php echo admin_url( 'edit.php?post_type=email_template' ); ?>" title="Email Templates">review them</a> and make sure they are as you left them.</td>
        </tr>
        <tr>
        <td><font style="font-size:14px; font-weight:bold; color:#F90">Scheduler</font><br>
		Many users have been asking for this for a while now, so we have delivered!<br>
        Let Mobile DJ Manager manage your business so you don't have to. Whilst you're sleeping, out DJ'ing, spending time with your family, or doing whatever it is you do... Mobile DJ Manager keeps on working;<br />
        <ui>
        <li>Request payments from customers</li>
        <li>Ask clients for feedback once their event is complete</li>
        <li>Close enquiries that have been outstanding for a while</li>
        <li>and more</li>
        </ui>
        All scheduler tasks can be managed and customised within the <a href="<?php echo admin_url( 'admin.php?page=mdjm-settings&tab=scheduler' ); ?>" title="MDJM Scheduler">Settings</a> page.<br />
		</td>
        </tr>
        <tr>
        <td><font style="font-size:14px; font-weight:bold; color:#F90">Playlist Uploads</font><br>
		The setting has been there for a while but it didn't do anything! Now if you enable the Upload Playlists settings
        option within the <a href="<?php echo admin_url( 'admin.php?page=mdjm-settings' ); ?>" title="MDJM Settings">Settings</a> page, your clients playlist choices will be sent back to the MDJM servers and consolidated with all other Mobile DJ Manager users' playlists. Once our database has been populated with a reasonable amount of data from this information, we'll begin freely sharing with all.<br>
        You'll be able to see the most popular song choices per month, per year etc.<br />
        We've turned the Upload Playlists setting on as part of the update.
		</td>
        </tr>
        <tr>
        <td style="background-color:#F90; font-size:16px; color:#FFF; font-weight:bold">And... What's fixed or improved?</td>
        </tr>
        <tr>
        <td>
            <ui>
                <li>This update notice! For all future updates, this page will provide you with an overview of changes</li>
                <li>Added option to mark balance as paid in event editor view</li>
                <li>Added the MDJM menu icon <img src="<?php echo WPMDJM_PLUGIN_URL . '/admin/images/mdjm-icon-20x20.jpg'; ?>" width="20" height="19"> to Contracts &amp; Email Templates menu items to make them easier to identify</li>
                <li>Dashboard figures were sometimes slightly inaccurate.</li>
                <li>In event edit view, Update Add-ons button was displayed even if no Add-ons were configured.</li>
                <li>If no venues had been saved, there was no possibility to enter venue information whilst creating a new event.</li>
            </ui>
        </td>
        </tr>
        </table>
        </td>
        <?php
	}
	
	if( isset( $_GET['ver'] ) || isset( $_GET['updated'] ) )	{
		if( isset( $_GET['updated'] ) && $_GET['updated'] == 1 )	{
			$func = 'f_mdjm_updated_to_' . str_replace( '.', '_', WPMDJM_VERSION_NUM );
		}
		else	{
			$func = 'f_mdjm_updated_to_' . $_GET['ver'];
		}
		if( function_exists( $func ) )	{
			f_mdjm_updated_header();
			$func();
			f_mdjm_updated_footer();
			update_option( 'mdjm_updated', '0' );
		}
		else	{
			echo '<h2>Page not found</h2>';
			echo '<a href="' . admin_url( 'admin.php?page=mdjm-dashboard' ) . '">Click here to continue</a>';
		}
	}
	
	
?>