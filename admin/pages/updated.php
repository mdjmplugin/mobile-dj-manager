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
        <div id="fb-root"></div>
		<script>(function(d, s, id) {
			  var js, fjs = d.getElementsByTagName(s)[0];
			  if (d.getElementById(id)) return;
			  js = d.createElement(s); js.id = id;
			  js.src = "//connect.facebook.net/en_GB/sdk.js#xfbml=1&appId=832846726735750&version=v2.0";
			  fjs.parentNode.insertBefore(js, fjs);
        }	(document, 'script', 'facebook-jssdk'));
        </script>
        <div class="wrap">
        <table class="widefat" width="100%">
        <tr>
        <td align="center"><img src="<?php echo WPMDJM_PLUGIN_URL . '/admin/images/banner-772x250.png'; ?>" width="772" height="250" /></td>
        </tr>
        <tr>
        <td align="center" style="font-size:24px; font-weight:bold; color:#F90">Welcome to Mobile DJ Manager version <?php echo WPMDJM_VERSION_NUM; ?></td>
        </tr>
        </table>
        <table>
        <tr valign="top">
        <td>
        <table class="widefat" width="100%">
        <?php
		$lic_info = do_reg_check( 'check' );
		if( !do_reg_check( 'check' ) || $lic_info[0] == 'XXXX' )	{
			?>
            <tr>
            <td style="background-color:#F90; font-size:16px; color:#FFF; font-weight:bold">Licensing</td>
            </tr>
            <tr>
            <td>You are currently running Mobile DJ Manager for WordPress in trial mode. Once your trial period expires, functionality will be restricted.<br /><br />
            To avoid this, <a href="http://www.mydjplanner.co.uk/shop/" title="Request New Feature" target="_blank"> click here to purchase your license now</a></td>
            </tr>
            <?php	
		}
		?>
        <tr>
        <td style="background-color:#F90; font-size:16px; color:#FFF; font-weight:bold">So... What's new?</font></td>
        </tr>
        <?php	
	} // f_mdjm_updated_header
	
	function f_mdjm_updated_footer()	{
		?>
        <td width="30%" valign="top">
        <table class="widefat" width="100%">
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
        <tr>
        <td><a href="http://twitter.com/mobiledjmanager" class="twitter-follow-button" data-show-count="false">Follow @mobiledjmanager</a>
<script src="http://platform.twitter.com/widgets.js" type="text/javascript"></script></td>
        </tr>
        <tr>
        <td><div class="fb-like" data-href="https://www.facebook.com/pages/Mobile-DJ-Manager-for-WordPress/544353295709781?ref=bookmarks" data-layout="button" data-action="like" data-show-faces="false" data-share="false"></div></td>
        </tr>
        <tr>
        <td style="background-color:#F90; font-size:16px; color:#FFF; font-weight:bold">Ratings &amp; Reviews</td>
        </tr>
        <tr>
        <td>Not rated <a href="<?php f_mdjm_admin_page( 'mydjplanner' ); ?>" target="_blank">Mobile DJ Manager for WordPress</a> yet?<br /><br />
        We'd really appreciate your support by submitting your rating and comments for our plugin. <a href="https://wordpress.org/support/view/plugin-reviews/mobile-dj-manager/" title="Rate Mobile DJ Manager" target="_blank">Click Here</a> to submit your review now - Thanks :)</td>
        </tr>
        <tr>
        <td style="background-color:#F90; font-size:16px; color:#FFF; font-weight:bold">Latest News & Topics</td>
        </tr>
        <tr>
        <td><span style="font-size:14px; color:#FF9900; font-weight:bold">News:</span>
        <?php wp_widget_rss_output( 'http://www.mydjplanner.co.uk/category/news/feed/rss2/', $args = array( 'show_author' => 0, 'show_date' => 0, 'show_summary' => 0, 'items' => 3 ) ); ?>
        <span style="font-size:14px; color:#FF9900; font-weight:bold">Topics:</span>
        <?php wp_widget_rss_output( 'http://www.mydjplanner.co.uk/forums/feed/', $args = array( 'show_author' => 0, 'show_date' => 0, 'show_summary' => 0, 'items' => 3 ) ); ?>
        </td>
        </table>
        </td>
        </tr>
        </table>
        </div>
        <?php
	} // f_mdjm_updated_footer
	
/**************************************************
				VERSION 0.9.9.4
**************************************************/
	function f_mdjm_updated_to_0_9_9_4()	{
		global $mdjm_options;
		?>
        <tr>
        <td><font style="font-size:14px; font-weight:bold; color:#F90">Communications</font><br>
		We have now enabled the sending of contracts via email to clients from within the <a href="http://www.mydjplanner.co.uk/using-communication-feature/" target="_blank">Communication Feature</a>.<br /><br />
        The dropdown containing the templates is now split into Email Templates and Contracts to make them easily identifiable for selection.<br /><br />
        If you do not want your employees to see the contracts, you can configure that within the <span class="code">Disabled Templates for DJ's</span> <a href="<?php f_mdjm_admin_page( 'settings' ); ?>">setting</a>
        </td>
        </tr>
        <tr>
        <td><font style="font-size:14px; font-weight:bold; color:#F90">Cleanup Client List</font><br>
		The Client list could grow quite quickly making it difficult to browse and select a client when creating a new event, and generally just looking untidy.<br /><br />
        Therefore we have now added functionality within the <a href="<?php f_mdjm_admin_page( 'clients' ); ?>">Client view screen</a> to mark clients as inactive. Inactive clients are not displayed within the create event screen in the <span class="code">Select Client</span> drop down menu.<br /><br />
        To use the feature, for a single Client, hover over their name and select the appropriate action, or for multiple Clients, check the checkboxes next to the Client names and then select <strong>Mark Inactive</strong> from the <strong>Bulk Actions</strong> drop down menu and click <strong>Apply</strong>.<br /><br />
        To mark a Client as active again, client the <a href="<?php f_mdjm_admin_page( 'inactive_clients' ); ?>">Inactive Clients</a> link on the <a href="<?php f_mdjm_admin_page( 'clients' ); ?>">Client view screen</a> and follow the same process as above, but select <strong>Mark Active</strong> from the <strong>Bulk Actions</strong> drop down menu and click <strong>Apply</strong>.
        </td>
        </tr>
        <tr>
        <td style="background-color:#F90; font-size:16px; color:#FFF; font-weight:bold">And... What's fixed or improved?</td>
        </tr>
        <tr>
        <td>
            <ui>
            	<li>New <span class="code">Items per Page</span> <a href="<?php f_mdjm_admin_page( 'settings' ); ?>">Setting</a> added to determine the number of results displayed on the <a href="<?php f_mdjm_admin_page( 'clients' ); ?>">Clients</a>, <a href="<?php f_mdjm_admin_page( 'events' ); ?>">Events</a> and the <a href="<?php f_mdjm_admin_page( 'venues' ); ?>">Venues</a> list pages. It is currently set to <?php echo get_option( 'posts_per_page' ); ?> items per page, but you can customise it <a href="<?php f_mdjm_admin_page( 'settings' ); ?>">here.</a></li>
            	<li>Enabled pagination on the <a href="<?php f_mdjm_admin_page( 'events' ); ?>">Events</a> and the <a href="<?php f_mdjm_admin_page( 'venues' ); ?>">Venues</a> list pages.</li>
            	<li>Enabled searching within the <a href="<?php f_mdjm_admin_page( 'events' ); ?>">Event list screen</a>, the <a href="<?php f_mdjm_admin_page( 'clients' ); ?>">Client list screen</a> and the <a href="<?php f_mdjm_admin_page( 'venues' ); ?>">Venue list screen</a>. <a href="<?php f_mdjm_admin_page( 'events' ); ?>">Events</a> and <a href="<?php f_mdjm_admin_page( 'clients' ); ?>">Clients</a> can be searched based upon a <a href="<?php f_mdjm_admin_page( 'clients' ); ?>">Client's</a> email address, URL, WordPress ID or username (this does not currently include display name due to a restriction within WordPress). <a href="<?php f_mdjm_admin_page( 'venues' ); ?>">Venues</a> are searched upon any part of the <a href="<?php f_mdjm_admin_page( 'venues' ); ?>">Venue</a> name.</li>
                <li>Big Fix: When a Client booked an event via the <a href="<?php echo get_permalink( WPMDJM_CLIENT_HOME_PAGE ); ?>" target="_blank"><?php echo WPMDJM_APP_NAME; ?></a> an email was sent to them event if the <span class="code">Contract link to client?</span> <a href="<?php f_mdjm_admin_page( 'settings' ); ?>">setting</a> was disabled.</li>
                <li>Main dashboard indicated DJ was working today even if the event status was not Approved</li>
                <li>Confirmation message displayed to a client when they book an event or approve their contract via the <a href="<?php echo get_permalink( WPMDJM_CLIENT_HOME_PAGE ); ?>" target="_blank"><?php echo WPMDJM_APP_NAME; ?></a> now only displays the <strong>You will receive confirmation via email shortly</strong> message if you have configured emails to be sent in <a href="<?php f_mdjm_admin_page( 'settings' ); ?>">settings</a>.</li>
            </ui>
        </td>
        </tr>
        </table>
        </td>
       <?php
	} // f_mdjm_updated_to_0_9_9_4
	
/**************************************************
				VERSION 0.9.9.1
**************************************************/
	function f_mdjm_updated_to_0_9_9_1()	{
		global $mdjm_options;
		?>
        <tr>
        <td>This is a Minor Release primarily to address bugs with a few enhancements...<br>
        </td>
        </tr>
        <tr>
        <td style="background-color:#F90; font-size:16px; color:#FFF; font-weight:bold">What's fixed or improved?</td>
        </tr>
        <tr>
        <td>
            <ui>
                <li>Event date check returned odd results sometimes</li>
                <li>Added <a href="<?php f_mdjm_admin_page( 'settings' ); ?>">setting</a> to disable the incomplete profile warning displayed to clients when they logged into the <a href="<?php echo get_permalink( WPMDJM_CLIENT_HOME_PAGE ); ?>" target="_blank"><?php echo WPMDJM_APP_NAME; ?></a> if key information is missing. This setting is within the Client Dialogue tab</li>
                <li>New <a href="<?php f_mdjm_admin_page( 'settings' ); ?>">setting</a> to choose whether or not the <strong>Client</strong> receives the Booking Confirmation email once contract is signed / event status changes to Approved</li>
                <li>New <a href="<?php f_mdjm_admin_page( 'settings' ); ?>">setting</a> to choose whether or not the <strong>DJ</strong> receives the Booking Confirmation email once contract is signed / event status changes to Approved</li>
                
            </ui>
        </td>
        </tr>
        </table>
        </td>
        <?php
	} // f_mdjm_updated_to_0_9_9_1
	
/**************************************************
				VERSION 0.9.9
**************************************************/
	function f_mdjm_updated_to_0_9_9()	{
		global $mdjm_options;
		?>
        <tr>
        <td><font style="font-size:14px; font-weight:bold; color:#F90">Availability Checking &amp; Management</font><br>
        We have now added an <a href="<?php f_mdjm_admin_page( 'availability' ); ?>">availability</a> system that is usable both from the <a href="<?php echo admin_url(); ?>">admin backend</a> for management of DJ's and from the <a href="<?php echo get_permalink( WPMDJM_CLIENT_HOME_PAGE ); ?>" target="_blank">website frontend</a> enabling your clients to check if you have availability on their chosen event date.<br /><br />
        For Admins, from the <a href="<?php f_mdjm_admin_page( 'availability' ); ?>">availability</a> system you have an instant overview of all your employee's activity for the given month. Additionally...
        <ui>
            <li>Add absence information for all employees</li>
            <li>Check availability of either all or individual employees on a selected date</li>
        </ui><br />
        DJ's have an instant overview of all their own activity for the given month. Additionally...
        <ui>
            <li>Add absence information for themselves</li>
			<li>Check their own availability on a selected date</li>
        </ui><br />
        Clients can now check your availability for their chosen date from the front end of your website. The actions taken upon you being available, or not, are configured by you, the Admin.<br /><br />
        An <a href="<?php f_mdjm_admin_page( 'mydjplanner' ); ?>/availability-checker/" target="_blank">Availability Checker User Guide</a> has now been added which we highly recommend you review before implementing.
        </td>
        </tr>
        <tr>
        <td><font style="font-size:14px; font-weight:bold; color:#F90">Internationalization</font><br>
		<ui>
        	<li>Added support for EUR (&euro;) and USD ($) as well as the default GBP (&pound;). Change within <a href="<?php f_mdjm_admin_page( 'settings' ); ?>">Settings</a> if required</li>
            <li>Added <a href="<?php f_mdjm_admin_page( 'settings' ); ?>">Setting</a> enabling you to change the format in which the short date is displayed - applies to admin and client screens aswell as shortcodes.<br />
<strong>Note</strong>: Does not currently apply when to <strong>event date</strong> when creating and editing an event in the admin screen. This field is displayed as dd/mm/yyyy</li>
            <li>You can now change the display name of the default <a href="<?php f_mdjm_admin_page( 'client_fields' ); ?>">Client Fields</a> to suit your locality i.e. change County to State or Post Code to Zip Code. Exceptions are First Name, Last Name and E-mail fields as these are defaut WordPress fields and not added by the MDJM plugin</li>
        </ui>
        </td>
        </tr>
        <tr>
        <td><font style="font-size:14px; font-weight:bold; color:#F90">Client Dialogue Text</font><br>
        With the release of <a href="<?php f_mdjm_admin_page( 'dashboard' ); ?>&ver=0_9_7">0.9.7</a> we introduced the ability for you to manipulate the text displayed to clients when they logged into the frontend of your website to access the <a href="<?php echo get_permalink( WPMDJM_CLIENT_HOME_PAGE ); ?>" target="_blank"><?php echo WPMDJM_APP_NAME; ?></a>.<br /><br />
        Text could only be changed on the <a href="<?php echo get_permalink( WPMDJM_CLIENT_HOME_PAGE ); ?>" target="_blank">home page</a> and the log in page, but we have now added the ability to also manipulate the text on the <a href="<?php echo get_permalink( WPMDJM_CLIENT_PLAYLIST_PAGE ); ?>" target="_blank">Playlist page</a>.<br /><br />
        Additionally, we have added the text editor features to each of the textboxes so you can now fully customise the text appearance by changing font colours, making text bold, adding links etc.
        </td>
        </tr>
        <tr>
        <td><font style="font-size:14px; font-weight:bold; color:#F90">System Emails</font><br>
		Within the <a href="<?php f_mdjm_admin_page( 'settings' ); ?>">Settings</a> page you can now choose who system generated emails are sent from.<br /><br />
        Select either <strong>Admin</strong> to have these emails sent with a from address of <?php echo $mdjm_options['company_name']; ?> &lt;<?php echo $mdjm_options['system_email']; ?>&gt; or <strong>Event DJ</strong> to have emails sent as DJ NAME &lt;dj's email address&gt;
        </td>
        </tr>
        <tr>
        <td style="background-color:#F90; font-size:16px; color:#FFF; font-weight:bold">And... What's fixed or improved?</td>
        </tr>
        <tr>
        <td>
            <ui>
            	<li>Officially supporting WordPress 4.1</li>
            	<li>We have added the TinyMCE editor to the Settings textarea's where you can manipulate text displayed to your clients on your website enabling you to format text, add links etc. with ease</li>
                <li>Automated Task "Complete Events" now checks the end time of the event as well as the date (<a href="http://www.mydjplanner.co.uk/forums/topic/completed-items/" target="_blank">bug report</a>)</li>
                <li>Added <a href="<?php f_mdjm_admin_page( 'debugging' ); ?>">Debugging</a> option to the <a href="<?php f_mdjm_admin_page( 'debugging' ); ?>">Settings</a> page. Not recommended for use unless the Mobile DJ Manager for WordPress support team ask you to enable it</li>
                <li>Validate event date during event creation to ensure it is present and not in the past</li>
                <li>Added Created Date to Edit Event screen to display the date the event was first loaded</li>
                <li>Added Last Login time to the <a href="<?php f_mdjm_admin_page( 'djs' ); ?>">DJ List</a></li>
                <li>Date selectors now include drop downs to change month &amp; year and also start on the day of the week configured within your WordPress settings (was previously always Sunday)</li>
                <li>Improved uninstallation procedures</li>
                <li>The Mobile DJ Manager widget on the main WP Dashboard no longer includes Failed Enquiries in Today's status</li>
            </ui>
        </td>
        </tr>
        </table>
        </td>
        <?php
	} // f_mdjm_updated_to_0_9_9
	
/**************************************************
				VERSION 0.9.8
**************************************************/
	function f_mdjm_updated_to_0_9_8()	{
		?>
        <tr>
        <td><font style="font-size:14px; font-weight:bold; color:#F90">Bug Fix Release only</font><br>
        </td>
        </tr>
        <tr>
        <td style="background-color:#F90; font-size:16px; color:#FFF; font-weight:bold">And... What's fixed or improved?</td>
        </tr>
        <tr>
        <td>
            <ui>
                <li>Resetting client password during event creation was not always successful</li>
            </ui>
        </td>
        </tr>
        </table>
        </td>
        <?php
	} // f_mdjm_updated_to_0_9_8
	
/**************************************************
				VERSION 0.9.7
**************************************************/
	function f_mdjm_updated_to_0_9_7()	{
		?>
        <tr>
        <td><font style="font-size:14px; font-weight:bold; color:#F90">Client Password Management</font><br>
		During the event creation process we have added an additional field on the review screen <strong>Reset Client Password</strong>. If both this option and the <strong>Email Quote</strong> option are selected, whilst the event is being created, a random password will be created for the client. With the use of <a href="http://www.mydjplanner.co.uk/shortcodes/" target="_blank">Shortcodes</a>, you can then add the Client's username and password information into the quotation email that is sent to them if you wish to do so.<br /><br />
        An additional <a href="http://www.mydjplanner.co.uk/settings-overview/" target="_blank">setting</a>, <strong>Default Password Length</strong> has also been added to allow you to specify the length of this new password.
        </td>
        </tr>
        <tr>
        <td><font style="font-size:14px; font-weight:bold; color:#F90">Shortcode Updates</font><br>
        <ui>
            <li><span class="code">{EVENT_DATE_SHORT}</span>: Inserts the Event date in short format (DD/MM/YYYY). <span class="code">{EVENT_DATE}</span> still adds long format</li>
            <li><span class="code">{CLIENT_USERNAME}</span>: Inserts the client's username for logging into the <a href="<?php echo get_permalink( WPMDJM_CLIENT_HOME_PAGE ); ?>" target="_blank"><?php echo WPMDJM_CO_NAME; ?> <?php echo WPMDJM_APP_NAME; ?></a></li>
            <li><span class="code">{CLIENT_PASSWORD}</span>: Inserts the client's password for logging into the front end of your website</li>
        </ui>
        <strong>Note</strong>: We have found that the shortcodes seem to be stored in the cache of many browsers and therefore if you do not see the new shortcodes immediately, try holding down the shift key on your keyboard whilst refreshing your browser page<br /><br />
        The <a href="http://www.mydjplanner.co.uk/shortcodes" target="_blank">Shortcodes User Guide</a> has been updated with the new options
        </td>
        </tr>
        <tr>
        <td><font style="font-size:14px; font-weight:bold; color:#F90">Updated Permissions</font><br>
        <ui>
        	<li><strong>DJ Can View Enquiry</strong>: Whether or not your employees can see outstanding (or failed) enquiries where they have been listed as the DJ. If this is not selected, the relevant information is also removed from the WP Dashboard and the MDJM Dashboard</li>
            <li><strong>Disabled Shortcodes for DJ's</strong>: Select which <a href="http://www.mydjplanner.co.uk/shortcodes" target="_blank">Shortcodes</a> your DJ's cannot use within the <a href="<?php f_mdjm_admin_page( 'comms' ); ?>">Communications</a> feature. Whilst the <a href="http://www.mydjplanner.co.uk/shortcodes" target="_blank">Shortcodes</a> will still be visible, if a DJ tries to send an email with the disabled <a href="http://www.mydjplanner.co.uk/shortcodes" target="_blank">shortcodes</a> within the content, it will fail</li>
            <li><strong>Disabled Email Templates for DJ's</strong>: The Email Templates you select here will not be visible to DJ's when they are using the <a href="<?php f_mdjm_admin_page( 'comms' ); ?>">Communications</a> feature</li>
        </ui>
        </td>
        </tr>
        <tr>
        <td><font style="font-size:14px; font-weight:bold; color:#F90">Scheduled Tasks</font><br>
        We have given <a href="<?php f_mdjm_admin_page( 'tasks' ); ?>">Scheduled Tasks</a> a new home by removing the tab from the <a href="<?php f_mdjm_admin_page( 'settings' ); ?>">Settings page</a>. and creating an <a href="<?php f_mdjm_admin_page( 'tasks' ); ?>">Automated Tasks</a> menu link in both the side bar and toolbar - DJ Manager > <a href="<?php f_mdjm_admin_page( 'tasks' ); ?>">Automated Tasks</a>
        </td>
        </tr>
        <tr>
        <td><font style="font-size:14px; font-weight:bold; color:#F90">Client Dialogue Text</font><br>
        Located within the <a href="<?php f_mdjm_admin_page( 'settings' ); ?>">Settings Page</a>, a new <a href="<?php f_mdjm_admin_page( 'client_text' ); ?>">Client Dialogue</a> tab has been added. Within this area, you can adjust the default text that is displayed to Clients when they login to the frontend of your website to access the <a href="<?php echo get_permalink( WPMDJM_CLIENT_HOME_PAGE ); ?>" target="_blank"><?php echo WPMDJM_APP_NAME; ?></a>.<br /><br />
        Currently only applies to Client login text, and text displayed on the <a href="<?php echo get_permalink( WPMDJM_CLIENT_HOME_PAGE ); ?>" target="_blank"><?php echo WPMDJM_APP_NAME; ?> home page</a> however if you like this feature, let us know via our <a href="http://www.mydjplanner.co.uk/forums/" target="_blank">Support Forums</a> and we will add options for the other pages too. 
        </td>
        </tr>
        <tr>
        <td style="background-color:#F90; font-size:16px; color:#FFF; font-weight:bold">And... What's fixed or improved?</td>
        </tr>
        <tr>
        <td>
            <ui>
                <li>Contract review emails were generated and sent even if the <a href="http://www.mydjplanner.co.uk/settings-overview/" target="_blank">setting</a> was not enabled</li>
                <li>DJ's should only see their own Clients within the <a href="<?php f_mdjm_admin_page( 'comms' ); ?>">Communication feature</a></li>
                <li>DJ's now only see contact information for Clients when clicking their name on the <a href="<?php f_mdjm_admin_page( 'clients' ); ?>">Client screen</a>, unless they have permission to add new clients</li>
                <li>Clicking on a Clients email address now directs you to the <a href="<?php f_mdjm_admin_page( 'comms' ); ?>">Communication Feature</a> with the client auto selected</li>
                <li>DJ setup time now defaults to event start time</li>
                <li>Tighter Security: If you do not provide DJ's with the permission to Add Clients, they cannot Edit Clients either and the Add New button is no longer displayed within the Client Details page</li>
                <li>If you have not enabled DJ's to add venues, they cannot view them either except in the event detail screen</li>
                <li>Edit Venue button removed for DJ's that if they do not have permission to add a venue</li>
                <li>As reported in <a href="http://www.mydjplanner.co.uk/forums/topic/error-message2/">this bug</a> depending on the PHP configuration of your web server, a warning message may have been displayed when Adding, Editing, or Deleting a venue. This did not affect functionality.</li>
            </ui>
        </td>
        </tr>
        </table>
        </td>
        <?php
	} // f_mdjm_updated_to_0_9_7
	
/**************************************************
				VERSION 0.9.6
**************************************************/
	function f_mdjm_updated_to_0_9_6()	{
		?>
        <tr>
        <td><font style="font-size:14px; font-weight:bold; color:#F90">Communication Feature Update</font><br>
		The <a href="<?php f_mdjm_admin_page( 'comms' ); ?>">Communication Feature</a> has been given an overhaul.<br />
On the face of it, it looks pretty much the same as it did previously, but we have made some quite significant changes behind the scenes to improve it's functionality and reliability.
        <ui>
        <li>Admins can now communicate with DJ's as well as all clients in the MDJM system. DJ's can only communicate with their own clients</li>
        <li>Once you have selected a recipient, you can select the event regarding which you are communicating with them, if you wish to do so. For clients, once selected you can select all events they have in your system. For DJ's, you can select all events at which they have, or will, be DJ'ing at.</li>
        </ui>
        A new <a href="http://www.mydjplanner.co.uk/using-communication-feature/" target="_blank">User Guide</a> has been published for the <a href="<?php f_mdjm_admin_page( 'comms' ); ?>">Communication Feature</a>
        </td>
        </tr>
        <tr>
        <td><font style="font-size:14px; font-weight:bold; color:#F90">Event Updates</font><br>
        We've added some new fields to the event creation process...
        <ui>
        <li><strong>DJ Setup Time</strong>: Enables you to enter a setup time for the event</li>
        <li><strong>DJ Setup Date</strong>: Just in case :)</li>
        <li><strong>DJ Notes</strong>: The ability for you to enter notes that only the Admins and event DJ will see</li>
        <li><strong>Admin Notes</strong>: The ability for you to enter notes that only the Admins will see</li>
        <li>All new fields are available in both the event creation and edit event screens although for existing events, the DJ Setup time will default to midnight. The DJ Setup Date will always default to the event date</li>
        </ui>
        </td>
        </tr>
        <tr>
        <td><font style="font-size:14px; font-weight:bold; color:#F90">Shortcode Updates</font><br>
        <a href="http://www.mydjplanner.co.uk/shortcodes" target="_blank">Shortcodes</a> are now supported in email subjects. You can use shortcodes in Email Template post titles (if you have titles as subject set in <a href="<?php f_mdjm_admin_page( 'settings' ); ?>">settings</a>) as well as in the <a href="<?php f_mdjm_admin_page( 'comms' ); ?>">Communication Feature</a> (if an event is selected).<br /><br />
        Also, to support the new event fields, more <a href="http://www.mydjplanner.co.uk/shortcodes" target="_blank">Shortcode options</a> have been added...<br />
        <ui>
        <li><span class="code">{DJ_SETUP_TIME}</span>: Inserts the setup time specified during event creation</li>
        <li><span class="code">{DJ_SETUP_DATE}</span>: Inserts the setup date specified during event creation</li>
        <li><span class="code">{DJ_NOTES}</span>: Inserts the information entered into the events DJ Notes field</li>
        <li><span class="code">{ADMIN_NOTES}</span>: Inserts the information entered into the events Admin Notes field</li>
        </ui>
        All new <a href="http://www.mydjplanner.co.uk/shortcodes">shortcodes</a> are accessible via the MDJM Shortcode button > Event Shortcodes.<br />
        <strong>Note</strong>: We have found that the shortcodes seem to be stored in the cache of many browsers and therefore if you do not see the new shortcodes immediately, try holding down the shift key on your keyboard whilst refreshing your browser page<br />
        The <a href="http://www.mydjplanner.co.uk/shortcodes" target="_blank">Shortcodes User Guide</a> has been updated with the new options
        </td>
        </tr>
        <tr>
        <td style="background-color:#F90; font-size:16px; color:#FFF; font-weight:bold">And... What's fixed or improved?</td>
        </tr>
        <tr>
        <td>
            <ui>
                <li>As reported in <a href="http://www.mydjplanner.co.uk/forums/topic/error-message/">this bug</a> depending on the PHP configuration of your web server, a warning message may have been displayed when Converting, Completing, Failing, or Recovering an event. This did not affect any functionality</li>
                <li>The Communication feature was unreliable if a client had multiple events in the system and also regarding copying in Admin/DJ. The overhaul described above addresses these bugs</li>
            </ui>
        </td>
        </tr>
        </table>
        </td>
        <?php
	} // f_mdjm_updated_to_0_9_6
	
/**************************************************
				VERSION 0.9.5
**************************************************/
	function f_mdjm_updated_to_0_9_5()	{
		?>
        <tr>
        <td>This is a minor update to address some reported bugs, although some slight enhancements are included</td>
        </tr>
        <tr>
        <td><font style="font-size:14px; font-weight:bold; color:#F90">Contract Updates</font><br>
		<strong>Contract / Invoice Prefix</strong>: Added new option to the <a href="<?php echo admin_url( 'admin.php?page=mdjm-settings' ); ?>">DJ Manager > Settings</a> page enabling you to preifx the unique contracts ID if required. This prefix will also apply to invoices in a future release.
        </td>
        </tr>
        <tr>
        <td><font style="font-size:14px; font-weight:bold; color:#F90">Shortcode Updates</font><br>
        We have added a couple of new Event <a href="http://www.mydjplanner.co.uk/shortcodes">Shortcode options</a><br />
        <ui>
        <li><span class="code">{CONTRACT_DATE}</span>: Inserts the date of the contract. If the contract has been signed the date of signing is entered, otherwise it defaults to today</li>
        <li><span class="code">{CONTRACT_ID}</span>: Inserts the unique ID of the contract. If a prefix has been set within <a href="<?php echo admin_url( 'admin.php?page=mdjm-settings' ); ?>">DJ Manager > Settings</a>, the prefix is also displayed</li>
        </ui>
        </td>
        </tr>
        <tr>
        <td><font style="font-size:14px; font-weight:bold; color:#F90">Time Formatting</font><br>
        You can now select how times are displayed. Within the <a href="<?php echo admin_url( 'admin.php?page=mdjm-settings' ); ?>">DJ Manager > Settings</a> page, set to either a 24 hour or 12 hour format
        </td>
        </tr>
        <tr>
        <td style="background-color:#F90; font-size:16px; color:#FFF; font-weight:bold">And... What's fixed or improved?</td>
        </tr>
        <tr>
        <td>
            <ui>
                <li>As reported in <a href="http://www.mydjplanner.co.uk/forums/topic/emails-issues/">this bug</a> the From address of emails was not defaulting back to the WordPress Admin email address if unset</li>
                <li>Also reported in <a href="http://www.mydjplanner.co.uk/forums/topic/emails-issues/">this bug</a> Admins were copied in client emails even if the setting was not enabled</li>
                <li>Contract Date was always todays date, even when signed. Now shows date of signature if signed</li>
                <li>The <strong>Complete Events</strong> scheduled task was sending emails with subject of "0"</li>
                <li>Some scheduled tasks were sending notification emails to admin even when no actions taken</li>
            </ui>
        </td>
        </tr>
        </table>
        </td>
        <?php
	} // f_mdjm_updated_to_0_9_5
	
/**************************************************
				VERSION 0.9.4
**************************************************/
	function f_mdjm_updated_to_0_9_4()	{
		?>
        <tr>
        <td><font style="font-size:14px; font-weight:bold; color:#F90">Customised Email Subjects</font><br>
		You can now configure the client Enquiry, Contract and Booking Confirmation emails to have customised email subjects.<br /><br />
If you enable the option <strong>Template Title is Subject</strong> within the <a href="<?php echo admin_url( 'admin.php?page=mdjm-settings' ); ?>">DJ Manager > Settings</a> page, these emails will use the title of the templates as the email subject. If the setting remains turned off the default email subjects will be used...<br />
<ui>
<li><strong>Event Quote:</strong> DJ Enquiry</li>
<li><strong>Contract:</strong> Your DJ Booking</li>
<li><strong>Client Booking Confirmation:</strong> Booking Confirmation</li>
</ui><br />
<strong>Note</strong>: The subject of DJ Booking confirmation email which is sent to the event DJ when a client has signed their contract, or an event is set to "Approved" cannot be changed and is set to <strong>DJ Booking Confirmed</strong><br /><br />
Remember to change the titles of your email templates if you enable this option!<br /><br />
<strong>Note</strong>: Subjects for emails sent via the scheduler are set within the scheduler. Edit the task and set the subject as required.
        </td>
        </tr>
        <tr>
        <td><font style="font-size:14px; font-weight:bold; color:#F90">Client Pages</font><br>
        We have updated the Client Pages to enable your client to login as soon as an enquiry is created and begin managing their event.<br /><br />
You can now adjust your email template used for sending enquiries to tell your clients about this and enable them to go through the entire booking process, and beyond, easily online.
		<ui>
        	<li>Now supports multiple events per client</li>
            <li>All event's are displayed to client - previously only confirmed (approved events)</li>
            <li>Clients can accept quotations, sign contracts, decline quotes</li>
        </ui>
        </td>
        </tr>
        <tr>
        <td><font style="font-size:14px; font-weight:bold; color:#F90">Default Email Address</font><br>
		Previously the default email address used to send system generated emails was the email address specified under the WordPress <a href="<?php echo admin_url( 'options-general.php' ); ?>">Settings > General</a> option. We have now introduced a new <strong>Default Email Address</strong> field under the <a href="<?php echo admin_url( 'admin.php?page=mdjm-settings' ); ?>">DJ Manager > Settings</a> page enabling you to change this email address should you wish to do so.<br /><br />

The address specified here is used as the Admin email address. We recommend using the business owner's email address.<br /><br />

If the Setting <strong>Copy Admin in Client Emails</strong> is set, the email address entered in this new field is the one that is copied.<br /><br />

Additionally, in some system generated emails (enquiry etc.) this address is used in the from field. Make sure the address entered is a real mailbox that is capable of receiving emails.
        </td>
        </tr>
        <tr>
        <td style="background-color:#F90; font-size:16px; color:#FFF; font-weight:bold">And... What's fixed or improved?</td>
        </tr>
        <tr>
        <td>
            <ui>
                <li>Early adoptors of 0.9.3 may have experienced issues with saving venues into the venues database during event creation due to an incorrectly set DB attribute</li>
                <li>Some templated emails were not formatted correctly (too much spacing and/or invalid characters)</li>
                <li>Admin was not blind copied into emails even if setting was set</li>
                <li>Client contract page was sometimes denying access if accessed directly</li>
                <li>Client Playlist page no longer shows odd date if the event has passed</li>
                <li>Removed the Email Templates tab from the Settings pages as this is no longer used since version 0.9.3</li>
                <li>Displays the number of playlist entries uploaded to MDJM in the Upload Playlists edit view within the scheduler</li>
            </ui>
        </td>
        </tr>
        </table>
        </td>
        <?php
	} // f_mdjm_updated_to_0_9_4
	
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
                <li>Added Last Login column to the Clients table</li>
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
	} // f_mdjm_updated_to_0_9_3
	
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