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
<a href="<?php wp_get_referer(); ?>">Click here to proceed to the requested page</a></td>
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
        <td style="background-color:#F90; font-size:16px; color:#FFF; font-weight:bold">Ratings &amp; Reviews</td>
        </tr>
        <tr>
        <td>Not rated <a href="<?php f_mdjm_admin_page( 'mydjplanner' ); ?>" target="_blank">Mobile DJ Manager for WordPress</a> yet?<br /><br />
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
				VERSION 1.2
**************************************************/
	function f_mdjm_updated_to_1_2()	{
		global $mdjm;
		
		/* -- Complete the upgrade procedures for version 1.2 -- */
		
		$update_status = get_option( 'mdjm_update' );
		
		if( !empty( $update_status ) && $update_status == '1.2' )	{
		
			if( !class_exists( 'MDJM_Upgrade' ) )
				require_once( MDJM_PLUGIN_DIR . '/admin/includes/procedures/mdjm-upgrade.php' );
			
			$mdjm_upgrade = new MDJM_Upgrade();
			
			$mdjm_upgrade->update_to_1_2();
			
			if( file_exists( MDJM_PLUGIN_DIR . '/admin/includes/mdjm-templates.php' ) )
				unlink( MDJM_PLUGIN_DIR . '/admin/includes/mdjm-templates.php' );
			
			delete_option( 'mdjm_update' );
		}
		
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

/**************************************************
				VERSION 1.1.1
**************************************************/
	function f_mdjm_updated_to_1_1_1()	{
		global $mdjm_options;
		?>
        <tr>
        <td><font style="font-size:14px; font-weight:bold; color:#F90">Transactions</font><br />
		We've made a few more improvements to the <a href="<?php f_mdjm_admin_page( 'transactions' ); ?>">Transactions</a> feature. The main change here is that when editing an event, you can now view all transactions associated with that event, together with the amount of profit made from the event. You can also add new transactions from here.<br /><br />
        We've also added two new <a href="<?php f_mdjm_admin_page( 'settings' ); ?>">Settings</a> under the <a href="<?php f_mdjm_admin_page( 'settings' ); ?>&tab=payments">Payments</a> tab. These are utilised when adding new transactions manually<br />
    <ul>
        <li><code>Payment Types:</code> Add in any methods of payment you accept</li>
        <li><code>Transaction Types:</code> Add in a list of possible reasons for an expense or payment receipt</li>
    </ul>
		There are many more improvements coming to the <a href="<?php f_mdjm_admin_page( 'transactions' ); ?>">Transactions</a> feature, watch our for them in future releases of <a style="color: #F90" href="<?php f_mdjm_admin_page( 'mydjplanner' ); ?>" target="_blank" title="My DJ Planner: the home of Mobile DJ Manager for WordPress">Mobile DJ Manager for WordPress</a>
        
        </td>
        </tr>
        <tr>
        <td><font style="font-size:14px; font-weight:bold; color:#F90">Events Table</font><br />
        Further improvements to the display and navigation of the <a href="<?php f_mdjm_admin_page( 'events' ); ?>">Events</a> page. You can now filter by Event Month, Type, DJ and Client.
        </td>
        </tr>
        <tr>
        <td style="background-color:#F90; font-size:16px; color:#FFF; font-weight:bold">And... What's fixed or improved?</td>
        </tr>
        <tr>
        <td>
            <ul>
            	<li><strong>Bug Fix</strong>: <code>Manage Playlist</code> link was missing on the client home page when viewing a single event</li>
            	<li><strong>Bug Fix</strong>: Mapped field not removed from <a href="<?php f_mdjm_admin_page( 'contact_forms' ); ?>">Contact Form</a> list if already assigned to field</li>
                <li><strong>Bug Fix</strong>: DJ's only see their own events and clients within the <a href="<?php f_mdjm_admin_page( 'events' ); ?>">Events</a> page</li>
                <li><strong>Bug Fix</strong>: Emails sent via the <a href="<?php f_mdjm_admin_page( 'comms' ); ?>">Communication Feature</a> without a template failed</li>
            	<li><strong>General</strong>: Client first and last names always have a capital letter when created via new event or contact form</li>
                <li><strong>General</strong>: <a href="<?php f_mdjm_admin_page( 'events' ); ?>">Events</a> table defaults to sorted by event date</li>
                <li><strong>General</strong>: Added colour picker when setting error text colour for <a href="<?php f_mdjm_admin_page( 'contact_forms' ); ?>">Contact Forms</a></li>
                <li><strong>General</strong>: Custom verification messages for <a href="<?php f_mdjm_admin_page( 'contract' ); ?>">Contract</a> and <a href="<?php f_mdjm_admin_page( 'email_template' ); ?>">Email</a> Template updates</li>
                <li><strong>General</strong>: Further improvements to the <a href="<?php f_mdjm_admin_page( 'debugging' ); ?>">Debugging</a> system</li>
                <li><strong>General</strong>: Updated the uninstallation script</li>
            </ul>
        </td>
        </tr>
        </table>
        </td>
       <?php
	} // f_mdjm_updated_to_1_1_1

/**************************************************
				VERSION 1.1
**************************************************/
	function f_mdjm_updated_to_1_1()	{
		global $mdjm_options;
		?>
        <tr>
        <td><font style="font-size:14px; font-weight:bold; color:#F90"><!-- PayPal Logo --><a href="https://www.paypal.com/uk/webapps/mpp/paypal-popup" title="How PayPal Works" onclick="javascript:window.open('https://www.paypal.com/uk/webapps/mpp/paypal-popup','WIPaypal','toolbar=no, location=no, directories=no, status=no, menubar=no, scrollbars=yes, resizable=yes, width=1060, height=700'); return false;"><img src="https://www.paypalobjects.com/webstatic/mktg/Logo/pp-logo-100px.png" border="0" alt="PayPal Logo"></a><!-- PayPal Logo --> Integration</font><br />
       <a href="<?php f_mdjm_admin_page( 'mydjplanner' ); ?>" target="_blank" title="My DJ Planner: the home of Mobile DJ Manager for WordPress">Mobile DJ Manager for WordPress</a> is now fully integrated with PayPal enabling you to take online payments securely via your website.
        <ui>
        	<li>No PayPal account is needed by Clients in order to make payments</li>
            <li>Accepts payments from all major credit cards, as well as funds within the Client's PayPal account</li>
            <li>Clients can choose to pay the Booking Fee/Deposit, or the full event balance</li>
            <li>Full PayPal integration means the MDJM application receives information from the <a href="https://www.paypal.com/uk/cgi-bin/webscr?cmd=p/acc/ipn-info-outside" target="_blank" title="Instant Payment Notification (IPN)">PayPal IPN</a> API system and updates the booking and journal automatically after verifying payment is completed</li>
            <li>Automatically sends your client an email based on a template of your choosing when payment is verified</li>
            <li>Ability to apply taxes</li>
            <li>Multi-Currency support for GBP, EUR, &amp; USD</li>
            <li>Supports customised PayPal checkout pages</li>
            <li>Customise the display of the payment form</li>
            <li>Immediate notifications in the Admin interface when you have new "Unattended" enquiries</li>
            <li>Supports the PayPal sandbox environment so full testing can take place without real payments</li>
        </ui>
        <br />
        Make sure you check out the <a href="<?php f_mdjm_admin_page( 'mydjplanner' ); ?>/paypal-integration/" target="_blank">PayPal Integration User Guide</a> for detailed information and guidance before implementing online payments.
        </td>
        </tr>
        <tr>
        <td><font style="font-size:14px; font-weight:bold; color:#F90">Transactions Page</font><br />
        A new <a href="<?php f_mdjm_admin_page( 'transactions' ); ?>">Transactions</a> page has been added to the MDJM system and is available via the Mobile DJ Manager menu's within the WordPress admin interface.<br /><br />
		This page is only available to Admins and if the <a href="<?php f_mdjm_admin_page( 'settings' ); ?>&tab=payments">Payment</a> features is enabled and has been introduced to compliment the new online payments system as described above.<br /><br />
		For now, the page simply lists any transactions that have been processed via PayPal and any other data relevant to that transaction. We will continue to develop this feature in up and coming versions. If you have ideas/requirements for this page, please <a href="<?php f_mdjm_admin_page( 'mdjm_forums' ); ?>/forum/feature-requests/" target="_blank">let us know</a>.
        </td>
        </tr>
         <tr>
        <td><font style="font-size:14px; font-weight:bold; color:#F90">Events Table</font><br />
        The <a href="<?php f_mdjm_admin_page( 'events' ); ?>">Events</a> page has been updated slightly to be a little more intuitive. Unattended enquiries are also now listed as priority and with a red background.<br /><br />
		The majority of updates to this page were to do with better, cleaner coding resulting in faster loading times and more efficient lookups.
        </td>
        </tr>
        <tr>
        <td style="background-color:#F90; font-size:16px; color:#FFF; font-weight:bold">And... What's fixed or improved?</td>
        </tr>
        <tr>
        <td>
            <ul>
            	<li><strong>New</strong>: Added <code>Make A Payment</code> link to the <?php echo WPMDJM_APP_NAME; ?> home page if PayPal is enabled for Client events that are due a deposit of balance payment</li>
                <li><strong>New</strong>: Enabled the <code>Add Media</code> button within the <a href="<?php f_mdjm_admin_page( 'comms' ); ?>">Communications</a> page. You can now include images in your Client Communications</li>
                <li><strong>New</strong>: Added buttons in Playlist view to email the event playlist to yourself or print it</li>
            	<li><strong>New</strong>: <code>Payments</code> tab added to the <a href="<?php f_mdjm_admin_page( 'settings' ); ?>">Settings</a> to support the new <a href="https://www.paypal.com/" target="_blank" title="PayPal">PayPal</a> online payments feaure</li>
                <li><strong>New</strong>: Added sub-menu items to the admin toolbar <a href="<?php f_mdjm_admin_page( 'settings' ); ?>">Settings</a> item</li>
                <li><strong>New</strong>: <code>{CONTACT_URL}</code> <a href="http://www.mydjplanner.co.uk/shortcodes/" target="_blank">Shortcode</a> added</li>
                <li><strong>New</strong>: <a href="http://www.mydjplanner.co.uk/shortcodes/" target="_blank">Shortcodes</a> added to support the new online payments system. To be used within the verification email template</li>
                <ul>
                	<li>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<code>{PAYMENT_AMOUNT}</code>: Inserts the amount received by the payment</li>
                	<li>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<code>{PAYMENT_DATE}</code>: Inserts the date payment was received as determined by PayPal</li>
                    <li>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<code>{PAYMENT_FOR}</code>: Inserts <strong>Deposit</strong> or <strong>Balance</strong> depending on the payment received</li>
                </ul>
                <li><strong>New</strong>: <a href="<?php f_mdjm_admin_page( 'settings' ); ?>">Setting</a> added <code>Deposit Label</code> enabling you to change the terminology used on both the front end and backend of your website. Some people prefer <code>Booking Fee</code> for example. Defaults to <code>Deposit</code>. Remember to update any email or contract templates as necessary</li>
                 <li><strong>New</strong>: <a href="<?php f_mdjm_admin_page( 'settings' ); ?>">Setting</a> added <code>Balance Label</code> enabling you to change the terminology used on both the front end and backend of your website. Defaults to <code>Balance</code>. Remember to update any email or contract templates as necessary</li>
                <li><strong>Bug Fix</strong>: Slight adjustment to the <a href="<?php f_mdjm_admin_page( 'contact_forms' ); ?>">Contact Forms</a> validation scripts. In some instances determined during testing (no bug reports) the jQuery validation did not work correctly</li>
                <li><strong>Bug Fix</strong>: WordPress "reserves" some form field names such as <code>name</code> so if you used this field name within the <a href="<?php f_mdjm_admin_page( 'contact_forms' ); ?>">MDJM Contact Forms</a>, the form did not submit correctly. This is rectified</li>
                <li><strong>General</strong>: Updated the uninstall script</li>
                <li><strong>General</strong>: Added the <code>Date Added</code> column to the playlist table admin view. List is sorted by this column as default</li>
                <li><strong>TODO</strong>: Editing and ordering of <a href="<?php f_mdjm_admin_page( 'contact_forms' ); ?>">Contact Form</a> fields</li>
            </ul>
        </td>
        </tr>
        </table>
        </td>
       <?php
	} // f_mdjm_updated_to_1_1

/**************************************************
				VERSION 1.0
**************************************************/
	function f_mdjm_updated_to_1_0()	{
		global $mdjm_options;
		?>
        <tr>
        <td><font style="font-size:14px; font-weight:bold; color:#F90">MDJM Contact Forms</font><br>
		The new MDJM Contact Forms enable you to create fully customisable <a href="<?php f_mdjm_admin_page( 'contact_forms' ); ?>">Contact Forms</a> for use either within a single page of your website, or on multiple pages by using the new MDJM <a href="<?php f_mdjm_admin_page( 'contact_forms' ); ?>">Contact Form</a> Widget which we've also included in this release.<br /><br />By using MDJM <a href="<?php f_mdjm_admin_page( 'contact_forms' ); ?>">Contact Forms</a>, MDJM can now fully manage every stage of an event from enquiry through to completion.<br /><br />Some of the key features of the <a href="<?php f_mdjm_admin_page( 'contact_forms' ); ?>">Contact Forms</a> are;
        <ul>
            <li>Fully Customisable forms and settings</li>
            <li>Configure your forms to create users and event enquiries when submitted</li>
            <li>Immediate responses to the client (if configured)</li>
            <li>3 Layouts to choose from</li>
            <li>Secure your form with a CAPTCHA field (required the <a href="<?php echo admin_url( 'plugin-install.php?tab=search&s=really+simple+captcha' ); ?>">Really Simple CAPTCHA</a> plugin to be installed)</li>
            <li>Availability status included in your notification email (not for client)</li>
            <li>Immediate notifications in the Admin interface when you have new "Unattended" enquiries</li>
            <li>From your <a href="<?php f_mdjm_admin_page( 'contact_forms' ); ?>">Events</a> page quickly check availability, assign DJ, finalise quote, or send notification of Unavailability to Client</li>
        </ul>
        <br />
        Make sure you check out the <a href="<?php f_mdjm_admin_page( 'mydjplanner' ); ?>/contact-forms/">Contact Forms User Guide </a> for more information and guidance when setting up your forms.<br /><br /><a href="<?php f_mdjm_admin_page( 'contact_forms' ); ?>">Contact Forms</a> are classed as BETA currently, however we do highly recommend you try them out and in the unlikely event that you encounter any problems, make sure you head over to our <a href="<?php f_mdjm_admin_page( 'mydjplanner' ); ?>/forums/forum/bugs/">Support Forums</a> and let us know.

        </td>
        </tr>
        <tr>
        <td style="background-color:#F90; font-size:16px; color:#FFF; font-weight:bold">And... What's fixed or improved?</td>
        </tr>
        <tr>
        <td>
            <ui>
            	<li><strong>New</strong>: Create Clients directly from the <a href="<?php f_mdjm_admin_page( 'add_event' ); ?>">Add New Event</a> screen as part of the event creation process</li>
            	<li><strong>New</strong>: MDJM Contact Form Widget enabling you to add your MDJM <a href="<?php f_mdjm_admin_page( 'contact_forms' ); ?>">Contact Form</a> to multiple web pages quickly and easily</li>
            	<li><strong>New</strong>: <a href="<?php f_mdjm_admin_page( 'settings' ); ?>">Setting</a> added <code>New Enquiry Notifications</code>. When checked, a notification will be displayed at the top of the WP Admin pages if there are new <code>Unattended Enquiries</code> that need attention. These notifications are only displayed to Administrators. The <a href="<?php f_mdjm_admin_page( 'settings' ); ?>">Setting</a> is enabled by default</li>
                <li><strong>New</strong>: Once an event is Approved, you can now click on the status within the <a href="<?php f_mdjm_admin_page( 'events' ); ?>">Event Listing</a> page and view the Client's signed contract</li>
                <li><strong>Bug Fix</strong>: The Year drop down list within the <a href="<?php f_mdjm_admin_page( 'availability' ); ?>">Availability</a> page was showing blank instead of 2015</li>
                <li><strong>Bug Fix</strong>: If you had your <a href="http://codex.wordpress.org/Using_Permalinks" target="_blank">WordPress Permalink Settings</a> set to the default of <strong>Default</strong> (also referred to as "Ugly") the <?php echo WPMDJM_APP_NAME; ?> links did not work correctly for Clients when logged in</li>
                <li><strong>Bug Fix</strong>: In some instances the links within the <?php echo WPMDJM_APP_NAME; ?> did not work correctly due to a conflict in configuration</li>
            </ui>
        </td>
        </tr>
        </table>
        </td>
       <?php
	} // f_mdjm_updated_to_1_0
	
/**************************************************
				VERSION 0.9.9.8
**************************************************/
	function f_mdjm_updated_to_0_9_9_8()	{
		global $mdjm_options;
		?>
        <tr>
        <td>This is a bug fix release only. See below for details.
        </td>
        </tr>
        <tr>
        <td style="background-color:#F90; font-size:16px; color:#FFF; font-weight:bold">And... What's fixed or improved?</td>
        </tr>
        <tr>
        <td>
            <ui>
            	<li>Bug: Event table was not being created correctly during installation</li>
                <li>Bug Fix: Slashes (/) were displayed if apostrophe's (') or other non-HTML characters were used in event description and other free text fields</li>
            </ui>
        </td>
        </tr>
        <tr>
        <td style="background-color:#F90; font-size:16px; color:#FFF; font-weight:bold">&nbsp;</td>
        </tr>
        <tr>
        <td>
            <ui>
            	<li>New <strong>Mobile DJ Manager Availability</strong> Widget added to the main <a href="<?php f_mdjm_admin_page( 'wp_dashboard' ); ?>">WP Dashboard</a> which displays an instant 7 day overview for all your staff and provides the ability for you to perform a quick <a href="<?php f_mdjm_admin_page( 'availability' ); ?>">availability</a> lookup as soon as you have logged into your WordPress Admin interface</</li>
                <li>New: We have now added functionality within the <a href="<?php f_mdjm_admin_page( 'djs' ); ?>">DJ view screen</a> to mark DJ's as inactive. Inactive DJ's are not displayed within the create event screen in the <code>Select DJ</code> drop down menu. Use this function in the same way as the Inactive Client's which was introduced in <a href="<?php f_mdjm_admin_page( 'dashboard' ); ?>&ver=0_9_9_4">version 0.9.9.4</a></li>
            	<li>Quick Availability Check added to main <a href="<?php f_mdjm_admin_page( 'dashboard' ); ?>">MDJM Dashboard</a></</li>
                <li>New <a href="<?php f_mdjm_admin_page( 'settings' ); ?>">Setting</a> <code>Unavailability Email Template</code> which enables you to define a template to be used as default when advising clients of unavailability... more on this soon ;)</li>
            	<li>Event listing is now alphabetical within the Create Event and Edit Event pages</li>
                <li>Removed Bulk Actions drop down and associated checkboxes from the <a href="<?php f_mdjm_admin_page( 'events' ); ?>">Event List</a> pages. Two main reasons for this. There was/is a bug whereby multiple Journal entries were created when some actions where processed. We're not sure if we will re-introduce the Bulk Actions for these pages. If you find them useful, <a href="<?php f_mdjm_admin_page( 'mydjplanner' ); ?>/forums/general-support/">let us know</a>.</li>
                <li>Bug Fix: If you had more than 10 venues, you could not edit venues with an ID greater than 9</li>
                <li>Further enhancements to debugging</li>
                <li>Venue Listing drop down lists are now alphabetical</li>
                <li>Bug Fix: <a href="<?php f_mdjm_admin_page( 'tasks' ); ?>">Automated Task</a> <strong>Complete Events</strong> marked events as completed on the day of the event if the event finish time was after midnight</li>
                <li>Bug Fix: Entries Uploaded count was not displaying when editing the <a href="<?php f_mdjm_admin_page( 'tasks' ); ?>"><strong>Upload Playlist</strong> Automated Task</a></li>
                <li>Bug Fix: Playlist entries were not uploading to the MDJM servers reliably</li>
                <li>Front end Availability form validation improvements - no longer using annoying pop-up alert if no date is entered, but instead using jQuery</li>
            </ui>
        </td>
        </tr>
        </table>
        </td>
       <?php
	} // f_mdjm_updated_to_0_9_9_8
	
/**************************************************
				VERSION 0.9.9.7
**************************************************/
	function f_mdjm_updated_to_0_9_9_7()	{
		global $mdjm_options;
		?>
        <tr>
        <td>This is a bug fix release only. See below for details.
        </td>
        </tr>
        <tr>
        <td style="background-color:#F90; font-size:16px; color:#FFF; font-weight:bold">And... What's fixed or improved?</td>
        </tr>
        <tr>
        <td>
            <ui>
            	<li>Bug: Event quotes were using template set in settings even if you selected an alternative during event creation</li>
                <li>Bug: jQuery bug on main WP Dashboard stopping availability datepicker from showing resolved.</li>
                <li>New <a href="<?php f_mdjm_admin_page( 'settings' ); ?>">Setting</a> added <code>New Enquiry Notifications</code>. When checked, a notification will be displayed at the top of the WP Admin pages if there are new <code>Unattended Enquiries</code> that need attention. These notifications are only displayed to Administrators. The <a href="<?php f_mdjm_admin_page( 'settings' ); ?>">Setting</a> is enabled by default. <strong>Note that this feature will not be active until the next major release</strong></li>
            </ui>
        </td>
        </tr>
        <tr>
        <td style="background-color:#F90; font-size:16px; color:#FFF; font-weight:bold">Version 0.9.9.6 was only released a few hours ago... here are the release notes</td>
        </tr>
        <tr>
        <td>
            <ui>
            	<li>New <strong>Mobile DJ Manager Availability</strong> Widget added to the main <a href="<?php f_mdjm_admin_page( 'wp_dashboard' ); ?>">WP Dashboard</a> which displays an instant 7 day overview for all your staff and provides the ability for you to perform a quick <a href="<?php f_mdjm_admin_page( 'availability' ); ?>">availability</a> lookup as soon as you have logged into your WordPress Admin interface</</li>
                <li>New: We have now added functionality within the <a href="<?php f_mdjm_admin_page( 'djs' ); ?>">DJ view screen</a> to mark DJ's as inactive. Inactive DJ's are not displayed within the create event screen in the <code>Select DJ</code> drop down menu. Use this function in the same way as the Inactive Client's which was introduced in <a href="<?php f_mdjm_admin_page( 'dashboard' ); ?>&ver=0_9_9_4">version 0.9.9.4</a></li>
            	<li>Quick Availability Check added to main <a href="<?php f_mdjm_admin_page( 'dashboard' ); ?>">MDJM Dashboard</a></</li>
                <li>New <a href="<?php f_mdjm_admin_page( 'settings' ); ?>">Setting</a> <code>Unavailability Email Template</code> which enables you to define a template to be used as default when advising clients of unavailability... more on this soon ;)</li>
            	<li>Event listing is now alphabetical within the Create Event and Edit Event pages</li>
                <li>Removed Bulk Actions drop down and associated checkboxes from the <a href="<?php f_mdjm_admin_page( 'events' ); ?>">Event List</a> pages. Two main reasons for this. There was/is a bug whereby multiple Journal entries were created when some actions where processed. We're not sure if we will re-introduce the Bulk Actions for these pages. If you find them useful, <a href="<?php f_mdjm_admin_page( 'mydjplanner' ); ?>/forums/general-support/">let us know</a>.</li>
                <li>Bug Fix: If you had more than 10 venues, you could not edit venues with an ID greater than 9</li>
                <li>Further enhancements to debugging</li>
                <li>Venue Listing drop down lists are now alphabetical</li>
                <li>Bug Fix: <a href="<?php f_mdjm_admin_page( 'tasks' ); ?>">Automated Task</a> <strong>Complete Events</strong> marked events as completed on the day of the event if the event finish time was after midnight</li>
                <li>Bug Fix: Entries Uploaded count was not displaying when editing the <a href="<?php f_mdjm_admin_page( 'tasks' ); ?>"><strong>Upload Playlist</strong> Automated Task</a></li>
                <li>Bug Fix: Playlist entries were not uploading to the MDJM servers reliably</li>
                <li>Front end Availability form validation improvements - no longer using annoying pop-up alert if no date is entered, but instead using jQuery</li>
            </ui>
        </td>
        </tr>
        </table>
        </td>
       <?php
	} // f_mdjm_updated_to_0_9_9_7

/**************************************************
				VERSION 0.9.9.6
**************************************************/
	function f_mdjm_updated_to_0_9_9_6()	{
		global $mdjm_options;
		?>
        <tr>
        <td>No major new features withiin this release but see below for the list of improvements, and bug fixes.<br /><br />Our next release will include some major new features which are currently going through testing.<br /><br />Watch this space for more news, but we think you'll be pleased with what's coming...
        </td>
        </tr>
        <tr>
        <td style="background-color:#F90; font-size:16px; color:#FFF; font-weight:bold">And... What's fixed or improved?</td>
        </tr>
        <tr>
        <td>
            <ui>
            	<li>New <strong>Mobile DJ Manager Availability</strong> Widget added to the main <a href="<?php f_mdjm_admin_page( 'wp_dashboard' ); ?>">WP Dashboard</a> which displays an instant 7 day overview for all your staff and provides the ability for you to perform a quick <a href="<?php f_mdjm_admin_page( 'availability' ); ?>">availability</a> lookup as soon as you have logged into your WordPress Admin interface</</li>
                <li>New: We have now added functionality within the <a href="<?php f_mdjm_admin_page( 'djs' ); ?>">DJ view screen</a> to mark DJ's as inactive. Inactive DJ's are not displayed within the create event screen in the <code>Select DJ</code> drop down menu. Use this function in the same way as the Inactive Client's which was introduced in <a href="<?php f_mdjm_admin_page( 'dashboard' ); ?>&ver=0_9_9_4">version 0.9.9.4</a></li>
            	<li>Quick Availability Check added to main <a href="<?php f_mdjm_admin_page( 'dashboard' ); ?>">MDJM Dashboard</a></</li>
                <li>New <a href="<?php f_mdjm_admin_page( 'settings' ); ?>">Setting</a> <code>Unavailability Email Template</code> which enables you to define a template to be used as default when advising clients of unavailability... more on this soon ;)</li>
            	<li>Event listing is now alphabetical within the Create Event and Edit Event pages</li>
                <li>Removed Bulk Actions drop down and associated checkboxes from the <a href="<?php f_mdjm_admin_page( 'events' ); ?>">Event List</a> pages. Two main reasons for this. There was/is a bug whereby multiple Journal entries were created when some actions where processed. We're not sure if we will re-introduce the Bulk Actions for these pages. If you find them useful, <a href="<?php f_mdjm_admin_page( 'mydjplanner' ); ?>/forums/general-support/">let us know</a>.</li>
                <li>Bug Fix: If you had more than 10 venues, you could not edit venues with an ID greater than 9</li>
                <li>Further enhancements to debugging</li>
                <li>Venue Listing drop down lists are now alphabetical</li>
                <li>Bug Fix: <a href="<?php f_mdjm_admin_page( 'tasks' ); ?>">Automated Task</a> <strong>Complete Events</strong> marked events as completed on the day of the event if the event finish time was after midnight</li>
                <li>Bug Fix: Entries Uploaded count was not displaying when editing the <a href="<?php f_mdjm_admin_page( 'tasks' ); ?>"><strong>Upload Playlist</strong> Automated Task</a></li>
                <li>Bug Fix: Playlist entries were not uploading to the MDJM servers reliably</li>
                <li>Front end Availability form validation improvements - no longer using annoying pop-up alert if no date is entered, but instead using jQuery</li>
            </ui>
        </td>
        </tr>
        </table>
        </td>
       <?php
	} // f_mdjm_updated_to_0_9_9_6

/**************************************************
				VERSION 0.9.9.5
**************************************************/
	function f_mdjm_updated_to_0_9_9_5()	{
		global $mdjm_options;
		?>
        <tr>
        <td><font style="font-size:14px; font-weight:bold; color:#F90">Event Enquiry Email Template</font><br>
		We have added a drop down field to the event creation process that becomes visible if you select the option to <code>Email Quote?</code>. The drop down list <code>Select email Template to Use</code> enables you to select any of your email templates to use when emailing the Client with their quotation. By default, the option you have set within <a href="<?php f_mdjm_admin_page( 'settings' ); ?>">Settings</a> is selected.<br /><br />
		This provides the flexibilty for you to create different templates for use with different event types should you wish to.<br /><br />
		If you have <a href="<?php f_mdjm_admin_page( 'settings' ); ?>&tab=permissions">Permissions</a> configured to allow DJ's to create events, the <code>Disabled Templates for DJ's</code> setting applies.
        </td>
        </tr>
        <tr>
        <td style="background-color:#F90; font-size:16px; color:#FFF; font-weight:bold">And... What's fixed or improved?</td>
        </tr>
        <tr>
        <td>
            <ui>
            	<li>Adjusted <a href="<?php f_mdjm_admin_page( 'comms' ); ?>">Communications</a> page. <code>Send Email to:</code> dropdown now seperates Clients &amp; DJ's in a better format</li>
                <li>An additional  <a href="<?php f_mdjm_admin_page( 'debugging' ); ?>">debugging</a> option has been added to enable more in-depth debugging by the MDJM Support Team. Clicking the Submit Debug Files button, sends over information regarding your MDJM Settings to the Support Staff.</li>
            </ui>
        </td>
        </tr>
        </table>
        </td>
       <?php
	} // f_mdjm_updated_to_0_9_9_5
	
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
        If you do not want your employees to see the contracts, you can configure that within the <code>Disabled Templates for DJ's</code> <a href="<?php f_mdjm_admin_page( 'settings' ); ?>">setting</a>
        </td>
        </tr>
        <tr>
        <td><font style="font-size:14px; font-weight:bold; color:#F90">Cleanup Client List</font><br>
		The Client list could grow quite quickly making it difficult to browse and select a client when creating a new event, and generally just looking untidy.<br /><br />
        Therefore we have now added functionality within the <a href="<?php f_mdjm_admin_page( 'clients' ); ?>">Client view screen</a> to mark clients as inactive. Inactive clients are not displayed within the create event screen in the <code>Select Client</code> drop down menu.<br /><br />
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
            	<li>New <code>Items per Page</code> <a href="<?php f_mdjm_admin_page( 'settings' ); ?>">Setting</a> added to determine the number of results displayed on the <a href="<?php f_mdjm_admin_page( 'clients' ); ?>">Clients</a>, <a href="<?php f_mdjm_admin_page( 'events' ); ?>">Events</a> and the <a href="<?php f_mdjm_admin_page( 'venues' ); ?>">Venues</a> list pages. It is currently set to <?php echo get_option( 'posts_per_page' ); ?> items per page, but you can customise it <a href="<?php f_mdjm_admin_page( 'settings' ); ?>">here.</a></li>
            	<li>Enabled pagination on the <a href="<?php f_mdjm_admin_page( 'events' ); ?>">Events</a> and the <a href="<?php f_mdjm_admin_page( 'venues' ); ?>">Venues</a> list pages.</li>
            	<li>Enabled searching within the <a href="<?php f_mdjm_admin_page( 'events' ); ?>">Event list screen</a>, the <a href="<?php f_mdjm_admin_page( 'clients' ); ?>">Client list screen</a> and the <a href="<?php f_mdjm_admin_page( 'venues' ); ?>">Venue list screen</a>. <a href="<?php f_mdjm_admin_page( 'events' ); ?>">Events</a> and <a href="<?php f_mdjm_admin_page( 'clients' ); ?>">Clients</a> can be searched based upon a <a href="<?php f_mdjm_admin_page( 'clients' ); ?>">Client's</a> email address, URL, WordPress ID or username (this does not currently include display name due to a restriction within WordPress). <a href="<?php f_mdjm_admin_page( 'venues' ); ?>">Venues</a> are searched upon any part of the <a href="<?php f_mdjm_admin_page( 'venues' ); ?>">Venue</a> name.</li>
                <li>Big Fix: When a Client booked an event via the <a href="<?php echo get_permalink( WPMDJM_CLIENT_HOME_PAGE ); ?>" target="_blank"><?php echo WPMDJM_APP_NAME; ?></a> an email was sent to them event if the <code>Contract link to client?</code> <a href="<?php f_mdjm_admin_page( 'settings' ); ?>">setting</a> was disabled.</li>
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
            <li><code>{EVENT_DATE_SHORT}</code>: Inserts the Event date in short format (DD/MM/YYYY). <code>{EVENT_DATE}</code> still adds long format</li>
            <li><code>{CLIENT_USERNAME}</code>: Inserts the client's username for logging into the <a href="<?php echo get_permalink( WPMDJM_CLIENT_HOME_PAGE ); ?>" target="_blank"><?php echo WPMDJM_CO_NAME; ?> <?php echo WPMDJM_APP_NAME; ?></a></li>
            <li><code>{CLIENT_PASSWORD}</code>: Inserts the client's password for logging into the front end of your website</li>
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
        <li><code>{DJ_SETUP_TIME}</code>: Inserts the setup time specified during event creation</li>
        <li><code>{DJ_SETUP_DATE}</code>: Inserts the setup date specified during event creation</li>
        <li><code>{DJ_NOTES}</code>: Inserts the information entered into the events DJ Notes field</li>
        <li><code>{ADMIN_NOTES}</code>: Inserts the information entered into the events Admin Notes field</li>
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
        <li><code>{CONTRACT_DATE}</code>: Inserts the date of the contract. If the contract has been signed the date of signing is entered, otherwise it defaults to today</li>
        <li><code>{CONTRACT_ID}</code>: Inserts the unique ID of the contract. If a prefix has been set within <a href="<?php echo admin_url( 'admin.php?page=mdjm-settings' ); ?>">DJ Manager > Settings</a>, the prefix is also displayed</li>
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
			$ver = str_replace( '.', '_', WPMDJM_VERSION_NUM );
			$func = 'f_mdjm_updated_to_' . str_replace( '.', '_', WPMDJM_VERSION_NUM );
		}
		else	{
			$ver = $_GET['ver'];
			$func = 'f_mdjm_updated_to_' . $_GET['ver'];
		}
		if( function_exists( $func ) )	{
			f_mdjm_updated_header( $ver );
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