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

	function f_mdjm_updated_header( $ver )	{
		global $mdjm;
		wp_enqueue_script( 'youtube-subscribe' );
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
        <td align="center"><span style="font-size:24px; font-weight:bold; color:#FF9900">Welcome to Mobile DJ Manager version <?php echo str_replace( '_', '.', $ver ); ?></span><br />
<a href="<?php echo mdjm_get_admin_page( 'settings' ); ?>">Click here to proceed to Mobile DJ Manager Settings</a></td>
        </tr>
        </table>
        <table>
        <tr valign="top">
        <td>
        <table class="widefat" width="100%">
        <?php
		$lic_info = $mdjm->_mdjm_validation();
		if( empty( $lic_info ) || $lic_info['type'] == 'XXXX' )	{
			?>
            <tr>
            <td style="background-color:#F90; font-size:16px; color:#FFF; font-weight:bold">Licensing</td>
            </tr>
            <tr>
            <td>You are currently running Mobile DJ Manager for WordPress in trial mode. Once your trial period expires, functionality will be restricted.<br /><br />
            To avoid this, <a href="http://www.mydjplanner.co.uk/shop/" title="Request New Feature" target="_blank"> click here to purchase your license now</a><br /><br />
			If you are seeing this message after upgrading to version 1.2 and you have purchased a license, your license state will be restored momentarily</td>
            </tr>
            <?php	
		}
		?>
        <tr>
        <td style="background-color:#F90; font-size:16px; color:#FFF; font-weight:bold">So... What's new in version <?php echo str_replace( '_', '.', $ver ); ?>?</td>
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
        <td><a href="https://www.facebook.com/groups/mobiledjmanager">Join our Facebook Group</a><br /></td>
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
        <td><div class="g-ytsubscribe" data-channelid="UCaD6icd6OZ8haoTBc5YjJrw" data-layout="default" data-count="hidden"></div></td>
        </tr>
        <tr>
        <td style="background-color:#F90; font-size:16px; color:#FFF; font-weight:bold">Ratings &amp; Reviews</td>
        </tr>
        <tr>
        <td>Not rated <a href="<?php echo mdjm_get_admin_page( 'mydjplanner' ); ?>" target="_blank">Mobile DJ Manager for WordPress</a> yet?<br /><br />
        We'd really appreciate your support by submitting your rating and comments for our plugin. <a href="https://wordpress.org/support/view/plugin-reviews/mobile-dj-manager?rate=5#postform" title="Rate Mobile DJ Manager" target="_blank">Click Here</a> to submit your review now - Thanks :)</td>
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
				VERSION 1.2.3.6
**************************************************/
	function f_mdjm_updated_to_1_2_3_6()	{
		global $mdjm;
		
		?>
        <tr>
        <td><font style="font-size:14px; font-weight:bold; color:#F90">PayPal Payments:</font><br />
		The <a href="http://www.mydjplanner.co.uk/paypal-integration/" target="_blank">PayPal form</a> has been updated and now accepts custom payment amounts as specified by the client as well as the deposit and balance amounts.<br /><br />
        <ui>
        	<li>Now utilises radio buttons rather than the old style drop down box</li>
            <li>Ability to use standard HTML submit button instead of PayPal button</li>
            <li>Define your own label for the <code>Other amount</code> radio button</li>
        </ui><br />
        <strong>Note</strong>: We recommend you review your <a href="<?php echo mdjm_get_admin_page( 'payment_settings' ); ?>">Payment Settings</a> as the radio buttons look better vertically rather than horizontally.
        </td>
        </tr>
        <tr>
        <td style="background-color:#F90; font-size:16px; color:#FFF; font-weight:bold">And... What's fixed or improved?</td>
        </tr>
        <tr>
        <td>
            <ui>
                <li><span class="mdjm-new">New</span>: Added ZAR currency for South Africa</li>
                <li><span class="mdjm-new">New</span>: <a href="<?php echo mdjm_get_admin_page( 'payment_settings' ); ?>">Payment Setting</a> added <code>Label for Other Amount</code></li>
                <li><span class="mdjm-new">New</span>: <a href="<?php echo mdjm_get_admin_page( 'payment_settings' ); ?>&section=mdjm_paypal_settings">PayPal Button Option</a> added <code>Use standard HTML submit button</code> with option to customise the text</li>
            </ui>
        </td>
        </tr>
        </table>
        </td>
        <?php
	} // f_mdjm_updated_to_1_2_3_6

	
/**************************************************
				VERSION 1.2.3.5
**************************************************/
	function f_mdjm_updated_to_1_2_3_5()	{
		global $mdjm;
		
		?>
        <tr>
        <td>Version 1.2.3.5 is a minor update to resolve a few bugs...<br /><br />
        <ui>
            <li><span class="mdjm-bug">Bug Fix</span>: Completed event automated task sets event as completed incorrectly</li>
            <li><span class="mdjm-bug">Bug Fix</span>: Adding event transaction hung when the "Paid From" field was populated with a value</li>
            <li><span class="mdjm-bug">Bug Fix</span>: 12hr time format was not registering event time from the Dynamic Contact Form or Events page</li>
            <li><span class="mdjm-bug">Bug Fix</span>: Some themes displayed comments in footer of client zone page. Addition of action hook to ensure none are displayed</li>
            <li><span class="mdjm-bug">Bug Fix</span>: Redirect may not have worked when signing of contract was completed</li>
            <li><span class="mdjm-general">General</span>: Remove page/post edit link from Client Zone pages for clients and DJ's</li>
            <li><span class="mdjm-general">General</span>: Added Balance Due to <a href="<?php echo mdjm_get_admin_page( 'events' ); ?>">event listing</a> screen</li>
            <li><span class="mdjm-general">General</span>: More translation preparation</li>
        </ui>
        <br />
		<p><font style="font-size:14px; font-weight:bold; color:#F90">Important Information re Licensing:</font><br />
		We have recently had to change the way in which we validate the MDJM license. From this version licenses are no longer automatically validated daily as with previous versions.<br /><br />
		From now on, anytime you change your license, whether through initial purchase after trial, or renewal after your license expires, you will need to click the Update License link 
        displayed upon the <a href="<?php echo mdjm_get_admin_page( 'settings' ); ?>">Settings</a> screen. Your license will then be automatically applied and will last until the expiry date.</p>
        <p>Whilst the option to update license remains on the settings screen, you do not need to do so again unless you purchase a new/renewal license, or are asked to do so by the 
        #MDJM support team</p>
		The version 1.2.3.4 and 1.2.3.3 release notes are displayed below...</td>
        </tr>
        <tr>
        <td>Version 1.2.3.4 is a very minor update to resolve an issue preventing plugin availability within the <a href="https://wordpress.org">WordPress</a>
        plugin repository.<br />
        Additionally, correct the display of the <code>Online Quote Template</code> option available even if 
        <code>Email Quote to Client</code> is not selected on the <a href="<?php echo mdjm_get_admin_page( 'add_event' ); ?>">Events page</a><br /><br />
		The version 1.2.3.3 release notes are displayed below...</td>
        </tr>
        <tr>
        <td><font style="font-size:14px; font-weight:bold; color:#F90">Online Event Quotes</font><br />
		We have introduced the ability to provide event quotes online via your website as well as via email as has been the possibility historically.<br /><br />
        You now have the possibility to direct your clients to a page on your website by use of the new shortcode <code>{QUOTES_URL}</code> where they will be able to review the details of their requested event together with the cost and any other associated information you choose to include within the fully customisable template.<br />
		From there, they can immediately accept the enquiry and book the event.<br /><br />
		Furthermore, you are able to see if the client has accessed their quote.<br /><br />
		A new <a href="<?php echo mdjm_get_admin_page( 'mydjplanner' ); ?>/online-quotes/" target="_blank">user guide</a> has been published providing a full overview of this feature.<br /><br />
		To get you started we have created the <a href="<?php echo get_edit_post_link( MDJM_QUOTES_PAGE ); ?>">Quote Page</a> for you and also a <code><a href="<?php echo mdjm_get_admin_page( 'email_template' ); ?>&s=Default+Online+Quote">Default Online Quote</a></code> Template that you can modify as required.
        </td>
        </tr>
        <tr>
        <td style="background-color:#F90; font-size:16px; color:#FFF; font-weight:bold">And... What's fixed or improved?</td>
        </tr>
        <tr>
        <td>
            <ui>
            	<li><span class="mdjm-new">New</span>: Online quotes are now available in addition to email quotes. Clients can view quotes online and via a fully customisable button, accept the quote and book the event</li>
                <li><span class="mdjm-new">New</span>: <a href="=<?php echo mdjm_get_admin_page( 'settings' ); ?>">Setting</a> added to Client Zone tab enabling you to choose whether or not to display package & add-on prices within Client Zone</li>
                <li><span class="mdjm-new">New</span>: <a href="=<?php echo mdjm_get_admin_page( 'settings' ); ?>">Setting</a> added to support the online quotes feature</li>
                <li><span class="mdjm-new">New</span>: <a href="http://www.mydjplanner.co.uk/shortcodes/" target="_blank">Shortcode</a> <code>{QUOTES_URL}</code> added to support the online quotes feature</li>
                <li><span class="mdjm-general">General</span>: Updated WP Admin header tags per 4.3 release</li>
                <li><span class="mdjm-general">General</span>: Support for long field names in Contact Form for validation and Dynamic addon updates</li>
                <li><span class="mdjm-bug">Bug Fix</span>: If event venue was entered manually, fields were not displayed on the screen until you changed the dropdown selection</li>
                <li><span class="mdjm-bug">Bug Fix</span>: Saving playlist entries failed</li>
                <li><span class="mdjm-bug">Bug Fix</span>: Removed updating of email address via dynamic contact form as potential problems with login</li>
                <li><span class="mdjm-bug">Bug Fix</span>: Events not displaying on Clients page when filtered</li>
                <li><span class="mdjm-bug">Bug Fix</span>: Default transaction type was not displaying all options</li>
                <li><span class="mdjm-bug">Bug Fix</span>: Transaction source was not displaying all options on Events screen</li>
                <li><span class="mdjm-bug">Bug Fix</span>: Is Default? column was not populated within Contract Template screen. May have generated on screen error</li>
                <li><span class="mdjm-bug">Bug Fix</span>: Warning may have been displayed on Client Login screen and some admin screens dependant on PHP/WP settings</li>
            </ui>
        </td>
        </tr>
        </table>
        </td>
        <?php
		
	} // f_mdjm_updated_to_1_2_3_5

/**************************************************
				VERSION 1.2.3.4
**************************************************/
	function f_mdjm_updated_to_1_2_3_4()	{
		global $mdjm;
		
		?>
        <tr>
        <td>Version 1.2.3.4 is a very minor update to resolve an issue preventing plugin availability within the <a href="https://wordpress.org">WordPress</a>
        plugin repository.<br />
        Additionally, correct the display of the <code>Online Quote Template</code> option available even if 
        <code>Email Quote to Client</code> is not selected on the <a href="<?php echo mdjm_get_admin_page( 'add_event' ); ?>">Events page</a><br /><br />
		The version 1.2.3.3 release notes are displayed below...</td>
        </tr>
        <tr>
        <td><font style="font-size:14px; font-weight:bold; color:#F90">Online Event Quotes</font><br />
		We have introduced the ability to provide event quotes online via your website as well as via email as has been the possibility historically.<br /><br />
        You now have the possibility to direct your clients to a page on your website by use of the new shortcode <code>{QUOTES_URL}</code> where they will be able to review the details of their requested event together with the cost and any other associated information you choose to include within the fully customisable template.<br />
		From there, they can immediately accept the enquiry and book the event.<br /><br />
		Furthermore, you are able to see if the client has accessed their quote.<br /><br />
		A new <a href="<?php echo mdjm_get_admin_page( 'mydjplanner' ); ?>/online-quotes/" target="_blank">user guide</a> has been published providing a full overview of this feature.<br /><br />
		To get you started we have created the <a href="<?php echo get_edit_post_link( MDJM_QUOTES_PAGE ); ?>">Quote Page</a> for you and also a <code><a href="<?php echo mdjm_get_admin_page( 'email_template' ); ?>&s=Default+Online+Quote">Default Online Quote</a></code> Template that you can modify as required.
        </td>
        </tr>
        <tr>
        <td style="background-color:#F90; font-size:16px; color:#FFF; font-weight:bold">And... What's fixed or improved?</td>
        </tr>
        <tr>
        <td>
            <ui>
            	<li><span class="mdjm-new">New</span>: Online quotes are now available in addition to email quotes. Clients can view quotes online and via a fully customisable button, accept the quote and book the event</li>
                <li><span class="mdjm-new">New</span>: <a href="=<?php echo mdjm_get_admin_page( 'settings' ); ?>">Setting</a> added to Client Zone tab enabling you to choose whether or not to display package & add-on prices within Client Zone</li>
                <li><span class="mdjm-new">New</span>: <a href="=<?php echo mdjm_get_admin_page( 'settings' ); ?>">Setting</a> added to support the online quotes feature</li>
                <li><span class="mdjm-new">New</span>: <a href="http://www.mydjplanner.co.uk/shortcodes/" target="_blank">Shortcode</a> <code>{QUOTES_URL}</code> added to support the online quotes feature</li>
                <li><span class="mdjm-general">General</span>: Updated WP Admin header tags per 4.3 release</li>
                <li><span class="mdjm-general">General</span>: Support for long field names in Contact Form for validation and Dynamic addon updates</li>
                <li><span class="mdjm-bug">Bug Fix</span>: If event venue was entered manually, fields were not displayed on the screen until you changed the dropdown selection</li>
                <li><span class="mdjm-bug">Bug Fix</span>: Saving playlist entries failed</li>
                <li><span class="mdjm-bug">Bug Fix</span>: Removed updating of email address via dynamic contact form as potential problems with login</li>
                <li><span class="mdjm-bug">Bug Fix</span>: Events not displaying on Clients page when filtered</li>
                <li><span class="mdjm-bug">Bug Fix</span>: Default transaction type was not displaying all options</li>
                <li><span class="mdjm-bug">Bug Fix</span>: Transaction source was not displaying all options on Events screen</li>
                <li><span class="mdjm-bug">Bug Fix</span>: Is Default? column was not populated within Contract Template screen. May have generated on screen error</li>
                <li><span class="mdjm-bug">Bug Fix</span>: Warning may have been displayed on Client Login screen and some admin screens dependant on PHP/WP settings</li>
            </ui>
        </td>
        </tr>
        </table>
        </td>
        <?php
		
	} // f_mdjm_updated_to_1_2_3_4
	
/**************************************************
				VERSION 1.2.3.3
**************************************************/
	function f_mdjm_updated_to_1_2_3_3()	{
		global $mdjm;
		
		?>
        <tr>
        <td><font style="font-size:14px; font-weight:bold; color:#F90">Online Event Quotes</font><br />
		We have introduced the ability to provide event quotes online via your website as well as via email as has been the possibility historically.<br /><br />
        You now have the possibility to direct your clients to a page on your website by use of the new shortcode <code>{QUOTES_URL}</code> where they will be able to review the details of their requested event together with the cost and any other associated information you choose to include within the fully customisable template.<br />
		From there, they can immediately accept the enquiry and book the event.<br /><br />
		Furthermore, you are able to see if the client has accessed their quote.<br /><br />
		A new <a href="<?php echo mdjm_get_admin_page( 'mydjplanner' ); ?>/online-quotes/" target="_blank">user guide</a> has been published providing a full overview of this feature.<br /><br />
		To get you started we have created the <a href="<?php echo get_edit_post_link( MDJM_QUOTES_PAGE ); ?>">Quote Page</a> for you and also a <code><a href="<?php echo mdjm_get_admin_page( 'email_template' ); ?>&s=Default+Online+Quote">Default Online Quote</a></code> Template that you can modify as required.
        </td>
        </tr>
        <tr>
        <td style="background-color:#F90; font-size:16px; color:#FFF; font-weight:bold">And... What's fixed or improved?</td>
        </tr>
        <tr>
        <td>
            <ui>
            	<li><span class="mdjm-new">New</span>: Online quotes are now available in addition to email quotes. Clients can view quotes online and via a fully customisable button, accept the quote and book the event</li>
                <li><span class="mdjm-new">New</span>: <a href="=<?php echo mdjm_get_admin_page( 'settings' ); ?>">Setting</a> added to Client Zone tab enabling you to choose whether or not to display package & add-on prices within Client Zone</li>
                <li><span class="mdjm-new">New</span>: <a href="=<?php echo mdjm_get_admin_page( 'settings' ); ?>">Setting</a> added to support the online quotes feature</li>
                <li><span class="mdjm-new">New</span>: <a href="http://www.mydjplanner.co.uk/shortcodes/" target="_blank">Shortcode</a> <code>{QUOTES_URL}</code> added to support the online quotes feature</li>
                <li><span class="mdjm-general">General</span>: Updated WP Admin header tags per 4.3 release</li>
                <li><span class="mdjm-general">General</span>: Support for long field names in Contact Form for validation and Dynamic addon updates</li>
                <li><span class="mdjm-bug">Bug Fix</span>: If event venue was entered manually, fields were not displayed on the screen until you changed the dropdown selection</li>
                <li><span class="mdjm-bug">Bug Fix</span>: Saving playlist entries failed</li>
                <li><span class="mdjm-bug">Bug Fix</span>: Removed updating of email address via dynamic contact form as potential problems with login</li>
                <li><span class="mdjm-bug">Bug Fix</span>: Events not displaying on Clients page when filtered</li>
                <li><span class="mdjm-bug">Bug Fix</span>: Default transaction type was not displaying all options</li>
                <li><span class="mdjm-bug">Bug Fix</span>: Transaction source was not displaying all options on Events screen</li>
                <li><span class="mdjm-bug">Bug Fix</span>: Is Default? column was not populated within Contract Template screen. May have generated on screen error</li>
                <li><span class="mdjm-bug">Bug Fix</span>: Warning may have been displayed on Client Login screen and some admin screens dependant on PHP/WP settings</li>
            </ui>
        </td>
        </tr>
        </table>
        </td>
        <?php
		
	} // f_mdjm_updated_to_1_2_3_3

/**************************************************
				VERSION 1.2.3.2
**************************************************/
	function f_mdjm_updated_to_1_2_3_2()	{
		global $mdjm;
		
		?>
        <tr>
        <td><font style="font-size:14px; font-weight:bold; color:#F90">Version 1.2.3.2</font><br />
		Addresses a number of bug fixes and introduces some minor enhancements...<br /><br />
        <ui>
        	<li><span class="mdjm-new">New</span>: <a href="=<?php echo mdjm_get_admin_page( 'settings' ); ?>">Settings</a> added to the <a href="=<?php echo mdjm_get_admin_page( 'payment_settings' ); ?>">Payments</a> tab to configure default event deposit based on fixed rate or % of event value</li>
            <li><span class="mdjm-new">New</span>: Define initially selected <a href="=<?php echo mdjm_get_admin_page( 'equipment' ); ?>">package</a> within a dynamic <a href="=<?php echo mdjm_get_admin_page( 'contact_forms' ); ?>">Contact Form</a></li>
            <li><span class="mdjm-new">New</span>: Venue list has been added to <a href="=<?php echo mdjm_get_admin_page( 'equipment' ); ?>">Contact Forms</a></li>
            <li><span class="mdjm-general">General</span>: Optimized Dynamic <a href="=<?php echo mdjm_get_admin_page( 'contact_forms' ); ?>">Contact Forms</a> front end coding. Slight enhancement to load time</li>
            <li><span class="mdjm-general">General</span>: Refresh available <a href="=<?php echo mdjm_get_admin_page( 'equipment' ); ?>">Packages & Add-ons</a> when <?php echo MDJM_DJ; ?> selection changes on <a href="=<?php echo mdjm_get_admin_page( 'events' ); ?>">Event Management</a> screen</li>
            <li><span class="mdjm-general">General</span>: Updated WP Admin header tags per 4.3 release</li>
            <li><span class="mdjm-general">General</span>: Updated jQuery version for validation. Now works with IE versions < 11</li>
            <li><span class="mdjm-bug">Bug Fix</span>: Restored missing folder which was causing custom DB table backups to fail since version 1.2.3</li>
            <li><span class="mdjm-bug">Bug Fix</span>: <a href="=<?php echo mdjm_get_admin_page( 'contact_forms' ); ?>">Contact Form</a> creation did not always correctly define default behaviours correctly</li>
            <li><span class="mdjm-bug">Bug Fix</span>: Error displayed when deleting <a href="=<?php echo mdjm_get_admin_page( 'contact_forms' ); ?>">Contact Form</a> field</li>
            <li><span class="mdjm-bug">Bug Fix</span>: Error displayed upon <a href="=<?php echo mdjm_get_admin_page( 'contact_forms' ); ?>">Contact Form</a> creation</li>
            <li><span class="mdjm-bug">Bug Fix</span>: Depending on PHP/WP config an unwanted notice may have been displayed on client screen</li>
            <li><span class="mdjm-bug">Bug Fix</span>: Only obtain event data when an event with the given ID exists. Unnecessary PHP notice logging</li>
            <li><span class="mdjm-bug">Bug Fix</span>: Removed the random "r" character from the top of <a href="=<?php echo mdjm_get_admin_page( 'contact_forms' ); ?>">Contact Forms</a> with layout set as table</li>
            <li><span class="mdjm-new">New</span>: Preliminary translation work</li>
        </ui>
        </td>
        </tr>
        <tr>
        <tr>
        <td><font style="font-size:14px; font-weight:bold; color:#F90">Version 1.3.2.1</font><br />
		The release of version 1.2.3 on 19th August 2015 seemed to trigger a validation error when setting a date field as "required". The release of version 1.2.3.1 addresses that issue
        </td>
        </tr>
        <tr>
        <td><font style="font-size:14px; font-weight:bold; color:#F90">Packages &amp; Equipment</font><br />
		We've made some enhancements to the Packages &amp; Equipment functionality within the Event's screen, as well as on the <?php echo MDJM_APP; ?> event overview screen.<br /><br />
        <ui>
        	<li>Packages &amp; Addons are now displayed on the <?php echo MDJM_APP; ?> event overview for clients. When they hover their mouse over the name, the descrption and price will be displayed</li>
            <li>The Addons list no longer shows empty categories</li>
            <li>Setting or changing the Event Package, dynamically updates the list of available addons</li>
            <li>Price updates for existing events when adjusting Packages or Addons is vastly improved</li>
        </ui>
        </td>
        </tr>
        <tr>
        <td><font style="font-size:14px; font-weight:bold; color:#F90">Dynamic Contact Forms</font><br />
		Further to the enhancements of Packages &amp; Equipment as described above, you can now add Package Lists and Addons Lists to your contact forms allowing prospective clients to select the pre-configured event packages, and addons as required<br /><br />
        <ui>
        	<li>Price can be displayed next to packages and addons as required</li>
            <li>When a client hovers the mouse over a package or an addon item, the description is displayed</li>
            <li>When a client selects a package, the addons list is dynamically updated removing items that are included within the selected package</li>
            <li>If the <code>Create Enquiry</code> option is enabled within the Contact Form configuration, the accumulated price of any selected package and addons, is automatically added to the new event</li>
        </ui>
        </td>
        </tr>
        <tr>
        <td style="background-color:#F90; font-size:16px; color:#FFF; font-weight:bold">And... What's fixed or improved?</td>
        </tr>
        <tr>
        <td>
            <ui>
            	<li><span class="mdjm-general">General</span>: Full support for <a href="https://wordpress.org/news/2015/08/billie/" target="_blank">WordPress 4.3 &quot;Billie&quot;</a></li>
            	<li><span class="mdjm-new">New</span>: Updating the package for an Event in the Events Management screen, now dynamically updates the addons available for selection</li>
                <li><span class="mdjm-new">New</span>: Packages and Addons now displayed within Event Overview on the <?php echo MDJM_APP; ?> screen. When a client hovers over the package or addon, the description and price is displayed</li>
                <li><span class="mdjm-new">New</span>: New settings added to the Plugin Removal settings screen so you can manipulate what data to/not to delete during deletion of plugin</li>
                <li><span class="mdjm-new">New</span>: Enhanced the installation script</li>
            	<li><span class="mdjm-bug">Bug Fix</span>: Resolved coding conflict which <em>may</em> have interferred with other plugins Ajax requests</li>
            	<li><span class="mdjm-bug">Bug Fix</span>: A required date field within Contact Form resulted in verification failure</li>
                <li><span class="mdjm-bug">Bug Fix</span>: Empty equipment add-on categories no longer display</li>
                <li><span class="mdjm-bug">Bug Fix</span>: Playlist upload to #MDJM error</li>
                <li><span class="mdjm-bug">Bug Fix</span>: Changing Packages &amp; Addons for existing events now correctly re-calculates the event cost</li>
                <li><span class="mdjm-bug">Bug Fix</span>: Playlist entries are now successfully submitted to the MDJM servers when songs and artists contain apostraphe's</li>
                <li><span class="mdjm-bug">Bug Fix</span>: No longer restricts re-installation if trial is expired</li>
                <li><span class="mdjm-general">General</span>: Enhanced the shortcode replacement procedure to make it cleaner and faster</li>
                <li><span class="mdjm-general">General</span>: Log files are now auto-purged regardless of the admin page you are visiting. Previously only auto-purged whilst viewing Debug Settings</li>
                <li><span class="mdjm-general">General</span>: Refreshed and cleaned up the uninstallation script</li>
            </ui>
        </td>
        </tr>
        </table>
        </td>
        <?php
		
	} // f_mdjm_updated_to_1_2_3_2

/**************************************************
				VERSION 1.2.3.1
**************************************************/
	function f_mdjm_updated_to_1_2_3_1()	{
		global $mdjm;
		
		?>
        <tr>
        <td><font style="font-size:14px; font-weight:bold; color:#F90">Bug Fix</font><br />
		The release of version 1.2.3 on 19th August 2015 seemed to trigger a validation error when setting a date field as "required". The release of version 1.2.3.1 addresses that issue
        </td>
        </tr>
        <tr>
        <td><font style="font-size:14px; font-weight:bold; color:#F90">Packages &amp; Equipment</font><br />
		We've made some enhancements to the Packages &amp; Equipment functionality within the Event's screen, as well as on the <?php echo MDJM_APP; ?> event overview screen.<br /><br />
        <ui>
        	<li>Packages &amp; Addons are now displayed on the <?php echo MDJM_APP; ?> event overview for clients. When they hover their mouse over the name, the descrption and price will be displayed</li>
            <li>The Addons list no longer shows empty categories</li>
            <li>Setting or changing the Event Package, dynamically updates the list of available addons</li>
            <li>Price updates for existing events when adjusting Packages or Addons is vastly improved</li>
        </ui>
        </td>
        </tr>
        <tr>
        <td><font style="font-size:14px; font-weight:bold; color:#F90">Dynamic Contact Forms</font><br />
		Further to the enhancements of Packages &amp; Equipment as described above, you can now add Package Lists and Addons Lists to your contact forms allowing prospective clients to select the pre-configured event packages, and addons as required<br /><br />
        <ui>
        	<li>Price can be displayed next to packages and addons as required</li>
            <li>When a client hovers the mouse over a package or an addon item, the description is displayed</li>
            <li>When a client selects a package, the addons list is dynamically updated removing items that are included within the selected package</li>
            <li>If the <code>Create Enquiry</code> option is enabled within the Contact Form configuration, the accumulated price of any selected package and addons, is automatically added to the new event</li>
        </ui>
        </td>
        </tr>
        <tr>
        <td style="background-color:#F90; font-size:16px; color:#FFF; font-weight:bold">And... What's fixed or improved?</td>
        </tr>
        <tr>
        <td>
            <ui>
            	<li><span class="mdjm-general">General</span>: Full support for <a href="https://wordpress.org/news/2015/08/billie/" target="_blank">WordPress 4.3 &quot;Billie&quot;</a></li>
            	<li><span class="mdjm-new">New</span>: Updating the package for an Event in the Events Management screen, now dynamically updates the addons available for selection</li>
                <li><span class="mdjm-new">New</span>: Packages and Addons now displayed within Event Overview on the <?php echo MDJM_APP; ?> screen. When a client hovers over the package or addon, the description and price is displayed</li>
                <li><span class="mdjm-new">New</span>: New settings added to the Plugin Removal settings screen so you can manipulate what data to/not to delete during deletion of plugin</li>
                <li><span class="mdjm-new">New</span>: Enhanced the installation script</li>
            	<li><span class="mdjm-bug">Bug Fix</span>: Resolved coding conflict which <em>may</em> have interferred with other plugins Ajax requests</li>
            	<li><span class="mdjm-bug">Bug Fix</span>: A required date field within Contact Form resulted in verification failure</li>
                <li><span class="mdjm-bug">Bug Fix</span>: Empty equipment add-on categories no longer display</li>
                <li><span class="mdjm-bug">Bug Fix</span>: Playlist upload to #MDJM error</li>
                <li><span class="mdjm-bug">Bug Fix</span>: Changing Packages &amp; Addons for existing events now correctly re-calculates the event cost</li>
                <li><span class="mdjm-bug">Bug Fix</span>: Playlist entries are now successfully submitted to the MDJM servers when songs and artists contain apostraphe's</li>
                <li><span class="mdjm-bug">Bug Fix</span>: No longer restricts re-installation if trial is expired</li>
                <li><span class="mdjm-general">General</span>: Enhanced the shortcode replacement procedure to make it cleaner and faster</li>
                <li><span class="mdjm-general">General</span>: Log files are now auto-purged regardless of the admin page you are visiting. Previously only auto-purged whilst viewing Debug Settings</li>
                <li><span class="mdjm-general">General</span>: Refreshed and cleaned up the uninstallation script</li>
            </ui>
        </td>
        </tr>
        </table>
        </td>
        <?php
		
	} // f_mdjm_updated_to_1_2_3_1

/**************************************************
				VERSION 1.2.3
**************************************************/
	function f_mdjm_updated_to_1_2_3()	{
		global $mdjm;
		
		?>
        <tr>
        <td><font style="font-size:14px; font-weight:bold; color:#F90">Bug Fix</font><br />
		The release of version 1.2.3 on 19th August 2015 seemed to trigger a validation error when setting a date field as "required". The release of version 1.2.3.1 addresses that issue
        </td>
        </tr>
        <tr>
        <td><font style="font-size:14px; font-weight:bold; color:#F90">Packages &amp; Equipment</font><br />
		We've made some enhancements to the Packages &amp; Equipment functionality within the Event's screen, as well as on the <?php echo MDJM_APP; ?> event overview screen.<br /><br />
        <ui>
        	<li>Packages &amp; Addons are now displayed on the <?php echo MDJM_APP; ?> event overview for clients. When they hover their mouse over the name, the descrption and price will be displayed</li>
            <li>The Addons list no longer shows empty categories</li>
            <li>Setting or changing the Event Package, dynamically updates the list of available addons</li>
            <li>Price updates for existing events when adjusting Packages or Addons is vastly improved</li>
        </ui>
        </td>
        </tr>
        <tr>
        <td><font style="font-size:14px; font-weight:bold; color:#F90">Dynamic Contact Forms</font><br />
		Further to the enhancements of Packages &amp; Equipment as described above, you can now add Package Lists and Addons Lists to your contact forms allowing prospective clients to select the pre-configured event packages, and addons as required<br /><br />
        <ui>
        	<li>Price can be displayed next to packages and addons as required</li>
            <li>When a client hovers the mouse over a package or an addon item, the description is displayed</li>
            <li>When a client selects a package, the addons list is dynamically updated removing items that are included within the selected package</li>
            <li>If the <code>Create Enquiry</code> option is enabled within the Contact Form configuration, the accumulated price of any selected package and addons, is automatically added to the new event</li>
        </ui>
        </td>
        </tr>
        <tr>
        <td style="background-color:#F90; font-size:16px; color:#FFF; font-weight:bold">And... What's fixed or improved?</td>
        </tr>
        <tr>
        <td>
            <ui>
            	<li><span class="mdjm-general">General</span>: Full support for <a href="https://wordpress.org/news/2015/08/billie/" target="_blank">WordPress 4.3 &quot;Billie&quot;</a></li>
            	<li><span class="mdjm-new">New</span>: Updating the package for an Event in the Events Management screen, now dynamically updates the addons available for selection</li>
                <li><span class="mdjm-new">New</span>: Packages and Addons now displayed within Event Overview on the <?php echo MDJM_APP; ?> screen. When a client hovers over the package or addon, the description and price is displayed</li>
                <li><span class="mdjm-new">New</span>: New settings added to the Plugin Removal settings screen so you can manipulate what data to/not to delete during deletion of plugin</li>
                <li><span class="mdjm-new">New</span>: Enhanced the installation script</li>
            	<li><span class="mdjm-bug">Bug Fix</span>: Resolved coding conflict which <em>may</em> have interferred with other plugins Ajax requests</li>
            	<li><span class="mdjm-bug">Bug Fix</span>: A required date field within Contact Form resulted in verification failure</li>
                <li><span class="mdjm-bug">Bug Fix</span>: Empty equipment add-on categories no longer display</li>
                <li><span class="mdjm-bug">Bug Fix</span>: Playlist upload to #MDJM error</li>
                <li><span class="mdjm-bug">Bug Fix</span>: Changing Packages &amp; Addons for existing events now correctly re-calculates the event cost</li>
                <li><span class="mdjm-bug">Bug Fix</span>: Playlist entries are now successfully submitted to the MDJM servers when songs and artists contain apostraphe's</li>
                <li><span class="mdjm-bug">Bug Fix</span>: No longer restricts re-installation if trial is expired</li>
                <li><span class="mdjm-general">General</span>: Enhanced the shortcode replacement procedure to make it cleaner and faster</li>
                <li><span class="mdjm-general">General</span>: Log files are now auto-purged regardless of the admin page you are visiting. Previously only auto-purged whilst viewing Debug Settings</li>
                <li><span class="mdjm-general">General</span>: Refreshed and cleaned up the uninstallation script</li>
            </ui>
        </td>
        </tr>
        </table>
        </td>
        <?php
		
	} // f_mdjm_updated_to_1_2_3

/**************************************************
				VERSION 1.2.2
**************************************************/
	function f_mdjm_updated_to_1_2_2()	{
		global $mdjm;
		
		?>
        <tr>
        <td><font style="font-size:14px; font-weight:bold; color:#F90">Bug Fix Release</font><br />
            <p>MDJM Version 1.2.2 is a bug fix release to address a few issues that have arisen since the release of 1.2.1 last week.</p>
        	<ui>
                <li><span class="mdjm-bug">Bug Fix</span>: Addons available within Events screen when <code>Available as Addon</code> setting was not selected</li>
                <li><span class="mdjm-bug">Bug Fix</span>: <a href="<?php echo mdjm_get_admin_page( 'debugging' ); ?>">Debugging</a> was stuck on/off depending on your setting prior to the 1.2.1 upgrade</li>
                <li><span class="mdjm-bug">Bug Fix</span>: Unable to toggle the <code>PayPal Enabled</code> <a href="<?php echo mdjm_get_admin_page( 'payment_settings' ); ?>&section=mdjm_paypal_settings">setting</a> since upgrade to 1.2.1</li>
                <li><span class="mdjm-bug">Bug Fix</span>: Cleared an error that may display if WP Debugging is enabled, whilst adding new equipment and/or package</li>
                <li><span class="mdjm-bug">Bug Fix</span>: No more comment approval requests caused by journaling</li>
                <li><span class="mdjm-general">General</span>: Slight adjustment to codebase for debugging as a tidy up</li>
            </ui>
            <p>These issues appear to impact new installations more than existing due to the fact that the settings are set correctly, but not adjustable. However we <strong>recommend</strong> checking that both the <a href="<?php echo mdjm_get_admin_page( 'payment_settings' ); ?>&section=mdjm_paypal_settings"><code>Enable PayPal?</code></a> and  <a href="<?php echo mdjm_get_admin_page( 'debugging' ); ?>"><code>Enable Debugging?</code></a> settings are set as expected.</p>
        </td>
        </tr>
        <tr>
        </tr>
        </table>
        </td>
        <?php
		
	} // f_mdjm_updated_to_1_2_2

/**************************************************
				VERSION 1.2.1
**************************************************/
	function f_mdjm_updated_to_1_2_1()	{
		global $mdjm;
		
		?>
        <tr>
        <td><font style="font-size:14px; font-weight:bold; color:#F90">Updated Settings</font><br />
		We have given the <a href="<?php echo mdjm_get_admin_page( 'settings'); ?>">Settings</a> page an overhaul. The number of settings increases with almost every new update and we felt it was time to re-organise to make each setting a little easier to find. The <a href="http://www.mydjplanner.co.uk/settings-overview/" target="_blank">Settings Overview User Guide</a> has also been updated.<br /><br />
        <strong>New Payments Settings</strong>
        <ui>
            <li>Additional currencies added</li>
            <li><code>Display Currency Symbol</code> Choose where and how the currency symbol is displayed</li>
            <li><code>Decimal Point</code> Choose whether the decimal is displayed as a dot, comma or dash</li>
            <li><code>Thousands Seperator</code> Choose whether to use a dot or comma as the thousands seperator</li>
        </ui>
        We've also added some additional Custom Text options allowing you to specify the text displayed on the Contract page during digital signing
        </td>
        </tr>
        <tr>
        <td><font style="font-size:14px; font-weight:bold; color:#F90">Client Fields &amp; <?php echo MDJM_APP; ?> Profile Page</font><br />
		The <a href="<?php echo mdjm_get_admin_page( 'client_fields'); ?>">Client Fields</a> admin page has been updated to enable the following additional functionality...
        <ui>
        	<li>Drag &amp; drop to re-order the fields. The order specified will be how these fields are displayed on the 
            <a href="<?php $mdjm->get_link( MDJM_PROFILE_PAGE, false ); ?>" target="_blank"><?php echo MDJM_APP; ?> Profile Page</a> when a client is updating their profile</li>
            <li>You choose which fields are displayed and which are required to be filled in. Exceptions are First Name, Last Name and Email Address, all of which are always enabled and required</li>
            <li>You can re-label any of the fields. The label you choose will be the label displayed to the client. i.e Change <code>Last Name</code> to <code>Surname</code> if you wish</li>
        </ui>
        In addition we have updated the <a href="<?php $mdjm->get_link( MDJM_PROFILE_PAGE, false ); ?>" target="_blank"><?php echo MDJM_APP; ?> Profile Page</a> to be more efficient
        as well as HTML5 &amp; CSS3 compatible.
        </td>
        </tr>
        <tr>
        <td><font style="font-size:14px; font-weight:bold; color:#F90">New Shortcodes for Equipment &amp; Packages</font><br />
		We've added some new shortcodes for you...
        <ui>
        	<li><code>{AVAILABLE_PACKAGES}</code> - Display a list of packages that are currently available. If this shortcode is used with reference to an event that has a <?php echo MDJM_DJ; ?> assigned, only the packages that <?php echo MDJM_DJ; ?> can provide are displayed</li>
            <li><code>{AVAILABLE_PACKAGES_COST}</code> - Same as <code>{AVAILABLE_PACKAGES}</code> but additionally includes the cost of the package</li>
            <li><code>{EVENT_PACKAGE}</code> - Display the package that is currently assigned to the event. If no package is assigned, <code>No package is assigned to this event</code> is returned</li>
            <li><code>{EVENT_PACKAGE_COST}</code> - Same as <code>{EVENT_PACKAGE}</code> but additionally includes the cost of the package</li>
            <li><code>{AVAILABLE_ADDONS}</code> - Display a list of equipment add-ons that are currently available. If this shortcode is used with reference to an event that has a <?php echo MDJM_DJ; ?> assigned, only the addons that <?php echo MDJM_DJ; ?> can provide are displayed</li>
            <li><code>{AVAILABLE_ADDONS_COST}</code> - Same as <code>{AVAILABLE_ADDONS}</code> but additionally includes the cost of the add-on</li>
            <li><code>{EVENT_ADDONS}</code> - Display the add-ons that are currently assigned to the event. If no add-on is assigned, <code>No addons are assigned to this event</code> is returned</li>
            <li><code>EVENT_ADDONS_COST}</code> - Same as <code>{EVENT_ADDONS}</code> but additionally includes the cost of the add-on</li>
        </ui>
        </td>
        </tr>
        <tr>
        <td style="background-color:#F90; font-size:16px; color:#FFF; font-weight:bold">And... What's fixed or improved?</td>
        </tr>
        <tr>
        <td>
            <ui>
            	<li><span class="mdjm-new">New</span>: IP address captured during client contract signing and displayed in contract view</li>
                <li><span class="mdjm-new">New</span>: <a href="<?php echo mdjm_get_admin_page( 'payment_settings' ); ?>">Payment settings</a> to select custom decimal and 
                thousands seperator for currency as well dictating whether the currency symbol appears before of after the price</li>
                <li><span class="mdjm-general">General</span>: Refreshed <?php echo MDJM_APP; ?> code for HTML5 &amp; CSS3 compatibility</li>
                <li><span class="mdjm-general">General</span>: Additional currency symbols added</li>
                <li><span class="mdjm-general">General</span>: More improvements to debugging</li>
                <li><span class="mdjm-bug">Bug Fix</span>: Addressed Email Tracking reliability</li>
                <li><span class="mdjm-bug">Bug Fix</span>: <code>{EVENT_TYPE}</code> shortcode was returning ID rather than name</li>
                <li><span class="mdjm-bug">Bug Fix</span>: Playlist submission to MDJM date error</li>
                <li><span class="mdjm-bug">Bug Fix</span>: Broken event link when reviewing sent communication</li>
            </ui>
        </td>
        </tr>
        </table>
        </td>
        <?php
		
	} // f_mdjm_updated_to_1_2_1

/**************************************************
				VERSION 1.2
**************************************************/
	function f_mdjm_updated_to_1_2()	{
		global $mdjm;
		
		/* -- Display the update notes -- */
		?>
        <tr>
        <td>Version <?php echo MDJM_VERSION_NUM; ?> has been a long time coming due to the extensive changes we have made to the core coding which will enable us to bring
        you many more advanced features in the future.<br />
		As with any major changes, there is a chance that a few bugs exist. We are on stand by to address these immediately should you encounter any. Of course, we have performed
        extensive testing ourselves, but if you do find a bug please <a href="" target="_blank">Log it within our Support Forums</a> or post it within our 
        <a href="" target="_blank">Facebook Group</a> so that we can address it for you, and also to make other users aware.<br /><br />
        </td>
        </tr>
        <tr>
        <td><font style="font-size:14px; font-weight:bold; color:#F90">New Event Interface</font><br />
		The original Event interface has been revamped resulting in a single page for all single event actions;
            <ui>
                <li>Adding Event Packages and Add-ons auto updates the total event cost</li>
                <li>Create and select new Event Types</li>
                <li>Event Types have been moved and no longer reside within settings</li>
                <li>Delete events that are set as Unattended Enquiries</li>
                <li>Add event transaction entries without leaving the page</li>
                <li>View recent communications and journal entries</li>
            </ui>
        </td>
        </tr>
        <tr>
        <td style="background-color:#F90; font-size:16px; color:#FFF; font-weight:bold">And... What's fixed or improved?</td>
        </tr>
        <tr>
        <td>
            <ui>
            	<li><span class="mdjm-new">New</span>: Drag &amp; drop your Contact Form fields to re-order them easily</li>
                <li><span class="mdjm-new">New</span>: Edit field settings without having to delete and re-create</li>
                <li><span class="mdjm-new">New</span>: All transactions are now logged, whether automated via PayPal or manually entered by the Admin</li>
                <li><span class="mdjm-new">New</span>: Notifications to clients when payments are entered manually for events</li>
                <li><span class="mdjm-new">New</span>: Event Transaction overview is displayed on each event page</li>
                <li><span class="mdjm-new">New</span>: Transaction Types have been moved and no longer reside within settings</li>
                <li><span class="mdjm-general">General</span>: Email tracking accuracy has been improved. If it says it has been opened, 
                	you can be sure that the Client has received and opened the email</li>
                <li><span class="mdjm-bug">Bug Fix</span>: Unreliable email tracking resolved</li>
                <li><span class="mdjm-bug">Bug Fix</span>: Printing playlist no longer shows menu</li>
                <li><span class="mdjm-bug">Bug Fix</span>: Email playlist corrections</li>
                <li><span class="mdjm-general">General</span>: All outbound emails are sent from the defined system address. If your settings dictate that emails come from DJ's,
                	the DJ's name will be displayed and the reply-to address will be set to that of the DJ too. This also addresses an issue whereby
                    DJ's who have email addresses that do not end in the same domain name as the website where MDJM is installed, cannot send emails
                    due to security controls</li>
                <li><span class="mdjm-general">General</span>: Digital contract signing now requires the client to re-enter their password as an additional verification step</li>
                <li><span class="mdjm-bug">Bug Fix</span>: Strange actions if the Availability widget was displayed at the same time as an Availability form within the main content</li>
                <li><span class="mdjm-general">General</span>: Begun updating <?php echo MDJM_APP; ?> pages for HTML5 &amp; CSS3 compliance. Not yet completed</li>
                <li><span class="mdjm-new">New</span>: Create backups of the MDJM database tables and download within the debugging screen</li>
                <li><span class="mdjm-general">General</span>: Significant improvements to the application debugging. No annoying notification when debugging
                	is enabled, however we still only recommend to enable when you are experiencing an issue</li>
                
                
            </ui>
        </td>
        </tr>
        </table>
        </td>
       <?php
	};

/**************************************************
				VERSION 1.1.3.3
**************************************************/
	function f_mdjm_updated_to_1_1_3_3()	{
		global $mdjm_options;
				
		?>
        </tr>
        <tr>
        <td><font style="font-size:14px; font-weight:bold; color:#F90">Page Content (Shortcode) Placement</font><br />
		On all MDJM Client Zone pages except the contact form, no matter where the shortcode was placed, the MDJM content was always moved to the top above any custom content you added.<br /><br />
		We have now adjusted this so that you can add your own custom content above the MDJM shortcode and it will be displayed on the screen above the MDJM content as intended.
        </td>
        </tr>
        <tr>
        <td style="background-color:#F90; font-size:16px; color:#FFF; font-weight:bold">And... What's fixed or improved?</td>
        </tr>
        <tr>
        <td>
            <ui>
                <li><strong>General</strong>: Support for WordPress 4.2.3</li>
            </ui>
        </td>
        </tr>
        </table>
        </td>
        </tr>
        <tr>
        <td>
    	
        </td>
        </tr>
         <tr>
        <td style="background-color:#F90; font-size:16px; color:#FFF; font-weight:bold">Next Major Release (Coming Soon)</td>
        </tr>
        <tr>
        <td>We're close to releasing our next major update, just applying the finishing touches and completing testing. It is our hope that this will be available within the next 2-3 weeks so watch this space!<br />
			Within this release we have made many changes to the Admin interface...<br />
			<ui>
            	<li><strong>General</strong>: Many code efficiencies introducing speed enhancements, better CSS styling and moving more towards a class based plugin</li>
                <li><strong>General</strong>: Better integration with WordPress. Less reliance upon custom database tables</li>
                <li><strong>Events</strong>: A new and improved Event Listing screen. Create new events from a single screen, including equipment packages and add-ons</li>
                <li><strong>Events</strong>: Dynamic Event price updates and Ajax based transactions</li>
                <li><strong>Events</strong>: More ways to filter your event lists - By Dj, Client, Date, Event Type etc.</li>
                <li><strong>Contact Forms</strong>: Drag &amp; Drop interface to re-order your fields. You can now also edit fields rather than having to delete &amp; re-create</li>
                <li><strong>Emails</strong>: Much improved email class, faster to process, better logging and a much improved shortcode filtering process</li>
                <li><strong>Email Tracking</strong>: Improved email tracking functionality now only tracks the main recipient</li>
                <li><strong>Digital Contract Signing</strong>: Contract signing now requires clients to re-enter their password to verify it is them. Signing details are appended to the end of the contract</li>
                <li><strong>Digital Contract Signing</strong>: All signed contracts are easily visible to Admins via the admin interface and Clients via the Client Zone (not editable once signed)</li>
                <li><strong>Client Profiles</strong>: Choose which custom client fields are required</li>
                <li><strong>Journalling</strong>: Improved journalling, cleaner and much simpler to review all event and client interactions</li>
                <li><strong>Debugging</strong>: Many enhancements to the built-in debugging to enable faster resolution to support cases</li>
                <li><strong>General</strong>: Choose how to refer to your Artistes/performers. No longer have to be &quot;DJ's&quot;</li>
            </ui>
        </td>
        </tr>
        </table>
        </td>
       <?php
	} // f_mdjm_updated_to_1_1_3_3

/**************************************************
				VERSION 1.1.3.2
**************************************************/
	function f_mdjm_updated_to_1_1_3_2()	{
		global $mdjm_options;
				
		?>
        </tr>
        <tr>
        <td style="background-color:#F90; font-size:16px; color:#FFF; font-weight:bold">Bug Fixes & General Updates</td>
        </tr>
        <tr>
        <td>
            <ui>
            	<li><strong>General</strong>: Full support for WordPress version 4.2.2</li>
                <li><strong>Bug Fix</strong>: Missing space within Client Zone playlist management page...&quot;Your playlist currently has ...entries&quot;</li>
                <li><strong>Bug Fix</strong>: CSS Correction within Availability widget</li>
                <li><strong>Bug Fix</strong>: Error when sending playlist via email</li>
            </ui>
        </td>
        </tr>
         <tr>
        <td style="background-color:#F90; font-size:16px; color:#FFF; font-weight:bold">Next Major Release (Coming Soon)</td>
        </tr>
        <tr>
        <td>We're close to releasing our next major update, just applying the finishing touches and completing testing. It is our hope that this will be available within the next 2-3 weeks so watch this space!<br />
			Within this release we have made many changes to the Admin interface...<br />
			<ui>
            	<li><strong>General</strong>: Many code efficiencies introducing speed enhancements, better CSS styling and moving more towards a class based plugin</li>
                <li><strong>General</strong>: Better integration with WordPress. Less reliance upon custom database tables</li>
                <li><strong>Events</strong>: A new and improved Event Listing screen. Create new events from a single screen, including equipment packages and add-ons</li>
                <li><strong>Events</strong>: Dynamic Event price updates and Ajax based transactions</li>
                <li><strong>Events</strong>: More ways to filter your event lists - By Dj, Client, Date, Event Type etc.</li>
                <li><strong>Contact Forms</strong>: Drag &amp; Drop interface to re-order your fields. You can now also edit fields rather than having to delete &amp; re-create</li>
                <li><strong>Emails</strong>: Much improved email class, faster to process, better logging and a much improved shortcode filtering process</li>
                <li><strong>Email Tracking</strong>: Improved email tracking functionality now only tracks the main recipient</li>
                <li><strong>Digital Contract Signing</strong>: Contract signing now requires clients to re-enter their password to verify it is them. Signing details are appended to the end of the contract</li>
                <li><strong>Digital Contract Signing</strong>: All signed contracts are easily visible to Admins via the admin interface and Clients via the Client Zone (not editable once signed)</li>
                <li><strong>Client Profiles</strong>: Choose which custom client fields are required</li>
                <li><strong>Journalling</strong>: Improved journalling, cleaner and much simpler to review all event and client interactions</li>
                <li><strong>Debugging</strong>: Many enhancements to the built-in debugging to enable faster resolution to support cases</li>
                <li><strong>General</strong>: Choose how to refer to your Artistes/performers. No longer have to be &quot;DJ's&quot;</li>
            </ui>
        </td>
        </tr>
        </table>
        </td>
       <?php
	} // f_mdjm_updated_to_1_1_3_2

/**************************************************
				VERSION 1.1.3.1
**************************************************/
	function f_mdjm_updated_to_1_1_3_1()	{
		global $mdjm_options;
				
		?>
        </tr>
        <tr>
        <td style="background-color:#F90; font-size:16px; color:#FFF; font-weight:bold">Bug Fixes</td>
        </tr>
        <tr>
        <td>
            <ui>
                <li><strong>Bug Fix</strong>: In certain circumstances, if you do not have events in the <code>Approved</code> status, no events were displayed in the events list</li>
                <li><strong>Bug Fix</strong>: Relating to the above, the status links did not work in the events list</li>
            </ui>
        </td>
        </tr>
        </table>
        </td>
       <?php
	} // f_mdjm_updated_to_1_1_3_1

/**************************************************
				VERSION 1.1.3
**************************************************/
	function f_mdjm_updated_to_1_1_3()	{
		global $mdjm_options;
				
		?>
        <tr>
        <td><font style="font-size:14px; font-weight:bold; color:#F90">Support for WordPress 4.2</font><br />
		WordPress 4.2 is due for release tomorrow, 22nd April 2015. We've been testing our plugin with this new version and can now confirm full compatibility.
        </td>
        </tr>
        <tr>
        <td><font style="font-size:14px; font-weight:bold; color:#F90">Coming Soon</font><br />
		You may have noticed that we have recently slowed down our release schedules. This does not mean we haven't been working on further improvements...far from it! In fact, our next major release will include a number of major enhancements including a re-designed Event interface, better support for Event Packages and Add-ons, improved email tracking and much much more.<br />
<br />
		As always, thank you for your continued support :)
        </td>
        </tr>
        <tr>
        <td style="background-color:#F90; font-size:16px; color:#FFF; font-weight:bold">And... What's fixed or improved?</td>
        </tr>
        <tr>
        <td>
            <ui>
                <li><strong>General</strong>: Removed the <code>Add New</code> button from <a href="<?php f_mdjm_admin_page( 'tasks' ); ?>">Automated Tasks</a>. This feature is still in development</li>
            </ui>
        </td>
        </tr>
        </table>
        </td>
       <?php
	} // f_mdjm_updated_to_1_1_3

/**************************************************
				VERSION 1.1.2
**************************************************/
	function f_mdjm_updated_to_1_1_2()	{
		global $mdjm_options;
		
		/* -- Complete the upgrade to 1.1.2 -- */
		/* -- Register the Venue Taxonomy & Terms -- */
		if( !get_taxonomy( 'venue-details' ) )	{
			$tax_labels[MDJM_VENUE_POSTS] = array(
							'name'              		   => _x( 'Venue Details', 'taxonomy general name' ),
							'singular_name'     		  => _x( 'Venue Detail', 'taxonomy singular name' ),
							'search_items'      		   => __( 'Search Venue Details' ),
							'all_items'         		  => __( 'All Venue Details' ),
							'edit_item'        		  => __( 'Edit Venue Detail' ),
							'update_item'       			=> __( 'Update Venue Detail' ),
							'add_new_item'      		   => __( 'Add New Venue Detail' ),
							'new_item_name'     		  => __( 'New Venue Detail' ),
							'menu_name'         		  => __( 'Venue Details' ),
							'separate_items_with_commas' => __( 'Separate venue details with commas' ),
							'choose_from_most_used'	  => __( 'Choose from the most popular Venue Details' ),
							'not_found'				  => __( 'No details found' ),
							);
			$tax_args[MDJM_VENUE_POSTS] = array(
							'hierarchical'      => true,
							'labels'            => $tax_labels[MDJM_VENUE_POSTS],
							'show_ui'           => true,
							'show_admin_column' => true,
							'query_var'         => true,
							'rewrite'           => array( 'slug' => 'venue-details' ),
						);
		
			register_taxonomy( 'venue-details', MDJM_VENUE_POSTS, $tax_args[MDJM_VENUE_POSTS] );
		}
		wp_insert_term( 'Low Ceiling', 'venue-details', array( 'description' => 'Venue has a low ceiling' ) );
		wp_insert_term( 'PAT Required', 'venue-details', array( 'description' => 'Venue requires a copy of the PAT certificate' ) );
		wp_insert_term( 'PLI Required', 'venue-details', array( 'description' => 'Venue requires proof of PLI' ) );
		wp_insert_term( 'Smoke/Fog Allowed', 'venue-details', array( 'description' => 'Venue allows the use of Smoke/Fog/Haze' ) );
		wp_insert_term( 'Sound Limiter', 'venue-details', array( 'description' => 'Venue has a sound limiter' ) );
		wp_insert_term( 'Via Stairs', 'venue-details', array( 'description' => 'Access to this Venue is via stairs' ) );
		
		?>
        <tr>
        <td><font style="font-size:14px; font-weight:bold; color:#F90">Email History &amp; Tracking</font><br />
		Mobile DJ Manager for WordPress now stores every email that is sent via the system to your clients and your DJ's.<br />
		A new menu item <a href="<?php f_mdjm_admin_page( 'email_history' ); ?>">Email History</a> has been added. This is where the emails are stored allowing you to quickly verify that your clients have been sent the emails as expected.<br />You can be certain that the email has been sent if the Status column shows <code>Sent</code> as this status is only set if the mail send command has been executed successfully. If for any reason the mail send command fails, the status would be set as <code>Ready to Send</code><br /><br />
		Furthermore, we have added a new <a href="<?php f_mdjm_admin_page( 'settings' ); ?>">Setting</a> <code>Track Client Emails?</code>. If selected, our email tracking API will be activated and when your clients open an email that has been sent via the system, a notification will be received by MDJM and the Status will be adjusted to <code>Opened</code> and additionally the time the message was viewed will be displayed, together with the total number of times the message has been opened.<br /><br />
		There are a few things to note with this feature...
    <ui>
        <li>We recommend that if you leave this option enabled, you disable the settings that dictate admins and/DJ's are copied into the emails. Otherwise, when an Admin/DJ who has received a copy of the email opens it, that will be recorded within the API and the status will be set to Opened. We added a counter to show how many times the email has been opened to try and help with this</li>
        <li>For the API to work, the client needs to have an active internet connection when they open the email. Otherwise the API callback cannot be performed and we cannot capture the message being opened</li>
        <li>Whilst we are using a very common means of tracking emails, not all clients will support it (although most do). We cannot therefore guarantee 100% accuracy with the tracking</li>
        <li>If an email client blocks images from your address, tracking will not function. Encourage your clients to add your email address to their safe-senders list</li>
    </ui>
        </td>
        </tr>
        <tr>
        <td><font style="font-size:14px; font-weight:bold; color:#F90">Venues</font><br />
		Over the next few releases you will see some changes to the Admin UI of the Mobile DJ Manager plugin, to improve functionality, speed, coding and integration with WordPress. We started with <a href="<?php f_mdjm_admin_page( 'venues' ); ?>">Venues</a>.<br /><br />
		The new <a href="<?php f_mdjm_admin_page( 'venues' ); ?>">venues</a> interface allows you to attach details to venues, such as Sound Limiter, Access via Stairs, or whatever is necessary. Add your own details. We have provided a few to get you started, you can remove these if you wish.<br /><br />
		All venues you added previously have been copied across automatically
        </td>
        </tr>
        <tr>
        <td><font style="font-size:14px; font-weight:bold; color:#F90">Contract &amp; Email Template's have Moved</font><br />
        To keep all MDJM links together, we have moved the <a href="<?php f_mdjm_admin_page( 'contract' ); ?>">Contract</a> and <a href="<?php f_mdjm_admin_page( 'email_template' ); ?>">Email Template</a> menu links to be under the DJ Manager menu rather than listed under the Posts section.<br /><br />
		We've also added additional information to both of these screens and with <a href="<?php f_mdjm_admin_page( 'contract' ); ?>">Contracts</a> you can now add some additional information to help describe the contract and where it should be used.
        </td>
        </tr>
        <tr>
        <td style="background-color:#F90; font-size:16px; color:#FFF; font-weight:bold">And... What's fixed or improved?</td>
        </tr>
        <tr>
        <td>
            <ui>
            	<li><strong>Bug Fix</strong>: If your web theme utilises white text some playlist entries where not visible within the front end</li>
                <li><strong>General</strong>: Cleaner Email and Contract Template tables</li>
                <li><strong>General</strong>: Code improvements, efficiency and cleanliness</li>
            </ui>
        </td>
        </tr>
        </table>
        </td>
       <?php
	} // f_mdjm_updated_to_1_1_2
	
	// Check for additional updates and execute
	$update = get_option( 'mdjm_update_me' );
	
	if( !empty( $update ) )
		include( MDJM_PLUGIN_DIR . '/admin/includes/procedures/mdjm-upgrade.php' );
	
	if( isset( $_GET['ver'] ) || isset( $_GET['updated'] ) )	{
		if( isset( $_GET['updated'] ) && $_GET['updated'] == 1 )	{
			$ver = str_replace( '.', '_', MDJM_VERSION_NUM );
			$func = 'f_mdjm_updated_to_' . $ver;
		}
		else	{
			$ver = $_GET['ver'];
			$func = 'f_mdjm_updated_to_' . $_GET['ver'];
		}
		if( function_exists( $func ) )	{
			f_mdjm_updated_header( $ver );
			$func();
			f_mdjm_updated_footer();
		}
		else	{
			echo '<h2>Page not found</h2>';
			echo '<a href="' . admin_url( 'admin.php?page=mdjm-dashboard' ) . '">Click here to continue</a>';
		}
	}
	
	
?>