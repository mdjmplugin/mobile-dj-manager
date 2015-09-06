=== Mobile DJ Manager ===
Contributors: mikeyhoward1977
Tags: DJ, Mobile DJ, DJ Planning, Event Planning, CRM, Event Planner, DJ Event Planner, DJ Agency, DJ Tool, Playlist Management, Contact Forms, Mobile Disco, Disco, Event Management, DJ Manager, Mobile DJ Manager, DJ Management
Requires at least: 3.9.1
Tested up to: 4.4
Stable tag: 1.2.3.5
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Mobile DJ Manager is an interface to fully manage your DJ or Agency business efficiently.

== Description ==

Mobile DJ Manager is the solution for Mobile DJ's who work on their own or businesses that employ multiple DJ's.

It is an event planning solution built specifically for websites running on WordPress ensuring the users are familiar with the Administration settings and look and feel.

Mobile DJ Manager allows you to manage your events from enquiry stage through to completion. Add your DJ's, your clients and then create an event.

As a site admin you will be able to view all information, but as a DJ, depending on the permissions set by the Administrator, you will only see those events that are specific to you.

Email automation is also built in, depending on your chosen settings. Quotes can be automatically sent to clients when an event is created, contracts can be issued automatically and digitally signed, and booking confirmations also emailed to both clients and DJ's alike.

As a client, you gain access to a number of features via the main website once you login. These include;
*	Profile management ensuring that the DJ has all the relevant contact details at all times
*	Playlist management
*	Secure online payments via PayPal
*	Digitally sign event contracts
*	Invite guests to add songs to the playlist via a unique URL. Clients can remove songs they do not deem appropriate

The main dashboard provides a complete overview of your business detailing the number of events and earnings over the month and year.

All in all Mobile DJ Manager helps you to run your DJ business efficiently.

== Installation ==

<strong>Automated Installation</strong>

1. Login to your WordPress administration screen and select "Plugins" -> "Add New" from the menu
3. Enter "Mobile DJ Manager" into the Search Plugins text box and hit Enter
4. Click "Install Now" within the Mobile DJ Manager plugin box
5. Activate the plugin once installation is completed

<strong>Manual Installation</strong>

Once you have downloaded the plugin zip file, follow these simple instructions to get going;

1. Login to your WordPress administration screen and select the "Plugins" -> "Add New" from the menu
2. Select "Upload Plugin" from the top of the main page
3. Click "Choose File" and select the mobile-dj-manager.zip file you downloaded
4. Click "Install Now"
5. Once installation has finished, select "Activate Plugin"
6. Once activation has completed, click the "Settings" link under the Mobile DJ Manager plugin
7. Installation has now completed. Next you need to <a title="Initial Configuration" href="http://www.mydjplanner.co.uk/initial-configuration/">configure MDJM</a>.

== Frequently Asked Questions ==

= Is any support provided? =

Yes. Support can be obtained via our online Support Forums at http://www.mydjplanner.co.uk/support/ or by emailing contact@mydjplanner.co.uk. We'll answer all queries as quickly as we can.

= Is there a Pro version with additional features? =

This plugin is fully functional and free to use for a period of 30 days after which the ability to adjust settings and add new information is restricted. To list these restrictions, you must purchase a license from http://www.mydjplanner.co.uk

== Screenshots ==

1. An overview of the Mobile DJ Manager Dashboard screen as seen by an Administrator when they are logged into the WordPress Admin UI
2. The Dashboard as seen by a DJ (employee) when they are logged in
3. A view of the settings page (not seen by DJs)
4. Permissions can be set to determine what a DJ can see/do within the application
5. Event listing within the Admin UI
6. The client's homepage on the website front end
7. The playlist feature as utilised by clients

== Licensing ==
Mobile DJ Manager for WordPress ships as a 30 day fully functional trial for you to fully experience the benefits of the plugin. 
After 30 days, functionality will be restricted in that you will no longer be able to add new events, or make adjustments to the plugins settings. After you purchase a license from <a href="http://www.mydjplanner.co.uk" target="_blank">http://www.mydjplanner.co.uk</a> full functionality will be restored.
Note: It can take up to 24 hours for your new license to be applied and restrictions to be lifted so to ensure you are not impacted, we recommend you purchase your license in advance of the trial expiring.

== Changelog ==
= 1.2.3.6 =
<strong>Released Friday 4th September, 2015</strong>
<ul>
	<li>General: Added custom payment amount option to the PayPal form</li>
	<li>General: PayPal form now uses radio buttons rather than select list</li>
	<li>General: Added setting to use standard HTML submit button with customised text for PayPal form</li>
	<li>General: Added ZAR currency for South Africa</li>
</ul>

= 1.2.3.5 =
<strong>Released Friday 4th September, 2015</strong>
<ul>
	<li>Bug Fix: Completed event automated task sets event as completed incorrectly</li>
	<li>Bug Fix: Adding event transaction hung when the "Paid From" field was populated with a value</li>
	<li>Bug Fix: 12hr time format was not registering event time from the Dynamic Contact Form or Events page</li>
	<li>Bug Fix: Some themes displayed comments in footer of client zone page. Addition of action hook to ensure none are displayed</li>
	<li>Bug Fix: Redirect "may" not have worked when signing of contract was completed</li>
	<li>General: Remove page/post edit link from Client Zone pages for clients and DJ's</li>
	<li>General: Added Balance Due to event listing screen</li>
	<li>General: More translation preparation</li>
</ul>

= 1.2.3.4 =
<strong>Released 31st August, 2015</strong>
<ul>
	<li>Bug Fix: Address an issue impacting availability within WordPress plugin repository</li>
	<li>Bug Fix: Option to select Online Quote Template is now available even if Email Quote to Client is not selected on the event screen</li>
</ul>

= 1.2.3.3 =
<strong>Released Monday 24th August, 2015</strong>
<ul>
	<li>New: Online quotes are now available in addition to email quotes. Clients can view quotes online and via a fully customisable button, accept the quote and book the event</li>
	<li>New: Setting added to Client Zone tab enabling you to choose whether or not to display package & add-on prices within Client Zone</li>
	<li>General: Updated WP Admin header tags per 4.3 release</li>
	<li>General: Support for long field names in Contact Form for validation and Dynamic addon updates</li>
	<li>Bug Fix: If event venue was entered manually, fields were not displayed on the screen until you changed the dropdown selection</li>
	<li>Bug Fix: Saving playlist entries failed</li>
	<li>Bug Fix: Removed updating of email address via dynamic contact form as potential problems with login</li>
	<li>Bug Fix: Events not displaying on Clients page when filtered</li>
	<li>Bug Fix: Default transaction type was not displaying all options</li>
	<li>Bug Fix: Transaction source was not displaying all options on Events screen</li>
	<li>Bug Fix: Is Default? column was not populated within Contract Template screen. May have generated on screen error</li>
	<li>Bug Fix: Warning may have been displayed on Client Login screen and some admin screens dependant on PHP/WP settings</li>
</ul>

= 1.2.3.2 =
<strong>Released Monday 24th August, 2015</strong>
<ul>
	<li>New: Settings added to the Payments tab to configure default event deposit based on fixed rate or % of event value</li>
	<li>New: Define initially selected package within a dynamic contact form</li>
	<li>New: Venue list has been added to contact forms</li>
	<li>General: Optimized Dynamic Contact Form front end coding. Slight enhancement to load time</li>
	<li>General: Refresh available packages & Add-ons when DJ selection changes on Event Management screen</li>
	<li>General: Updated WP Admin header tags per 4.3 release</li>
	<li>General: Updated jQuery version for validation. Now works with IE versions < 11</li>
	<li>Bug Fix: Restored missing folder which was causing custom DB table backups to fail since version 1.2.3</li>
	<li>Bug Fix: Contact form creation did not always correctly define default behaviours correctly</li>
	<li>Bug Fix: Error displayed when deleting Contact Form field</li>
	<li>Bug Fix: Error displayed upon Contact Form creation</li>
	<li>Bug Fix: Depending on PHP/WP config an unwanted notice may have been displayed on client screen</li>
	<li>Bug Fix: Only obtain event data when an event with the given ID exists. Unnecessary PHP notice logging</li>
	<li>Bug Fix: Removed the random "r" character from the top of contact forms with layout set as table</li>
	<li>New: Preliminary translation work</li>
</ul>

= 1.2.3.1 =
<strong>Released Thursday 20th August, 2015</strong>
<ul>
	<li>Bug Fix: issue with validation of the date field when used with #MDJM Contact Forms</li>
</ul>

= 1.2.3 =
<strong>Released Wednesday 19th August, 2015</strong>
<ul>
	<li>General: Full support for WordPress 4.3</li>
	<li>New: Updating the package for an Event in the Events Management screen, now dynamically updates the addons available for selection</li>
	<li>New: Packages and Addons now displayed within Event Overview on the Client Zone screen. When a client hovers over the package or addon, the description and price is displayed</li>
	<li>New: New settings added to the Plugin Removal settings screen so you can manipulate what data to/not to delete during deletion of plugin</li>
	<li>Bug Fix: Resolved coding conflict which <em>may</em> have interferred with other plugins Ajax requests</li>
	<li>Bug Fix: Empty equipment add-on categories no longer display</li>
	<li>Bug Fix: Playlist upload to #MDJM error</li>
	<li>Bug Fix: Changing Packages &amp; Addons for existing events now correctly re-calculates the event cost</li>
	<li>Bug Fix: Playlist entries are now successfully submitted to the MDJM servers when songs and artists contain apostraphe's</li>
	<li>General: Enhanced the shortcode replacement procedure to make it cleaner and faster</li>
	<li>General: Log files are now auto-purged regardless of the admin page you are visiting. Previously only auto-purged whilst viewing Debug Settings</li>
	<li>General: Refreshed and cleaned up the uninstallation script</li>
</ul>
= 1.2.2 =
<strong>Released Friday 3rd July, 2015</strong>
<ul>
	<li>Bug Fix: Addons available within Events screen when Available as Addon setting was not selected</li>
	<li>Bug Fix: Debugging was stuck on/off depending on your setting prior to the 1.2.1 upgrade</li>
	<li>Bug Fix: Unable to toggle the PayPal Enabled setting since upgrade to 1.2.1</li>
	<li>Bug Fix: Cleared an error that may display if WP Debugging is enabled, whilst adding new equipment and/or package</li>
	<li>Bug Fix: No more comment approval requests caused by journaling</li>
	<li>General: Slight adjustment to codebase for debugging as a tidy up</li>
</ul>
These issues appear to impact new installations more than existing due to the fact that the settings are set correctly, but not adjustable. However we recommend checking that both the Enable PayPal? and Enable Debugging? settings are set as expected.
= 1.2.1 =
<strong>Released Sunday 28th June, 2015</strong>
<ul>
	<li>Refreshed the Settings Options layout</li>
	<li>HTML5/CSS3 compatibility on all front end pages</li>
	<li>Updated Client fields</li>
	<li><strong>New</strong>: Additional shortcodes for equipment & packages</li>
	<li><strong>New</strong>: IP address captured during client contract signing and displayed in contract view</li>
	<li><strong>General</strong>: More improvements to debugging</li>
	<li><strong>Bug Fix</strong>: Addressed Email Tracking reliability</li>
	<li><strong>Bug Fix</strong>: {EVENT_TYPE} shortcode was returning ID rather than name</li>
	<li><strong>Bug Fix</strong>: Playlist submission to MDJM date error</li>
	<li><strong>Bug Fix</strong>: Broken event link when reviewing sent communication</li>
</ul>

= 1.2 =
<strong>Released Tuesday 2nd June, 2015</strong>
A complete revamp of how Events, Transactions, and Venues work plus much much more.
Join our Facebook group for all the latest discussions, news and more - https://www.facebook.com/groups/mobiledjmanager
<ul>
	<li><strong>New</strong>: Drag &amp; drop your Contact Form fields to re-order them easily</li>
	<li><strong>New</strong>: Edit field settings without having to delete and re-create</li>
	<li><strong>New</strong>: All transactions are now logged, whether automated via PayPal or manually entered by the Admin</li>
	<li><strong>New</strong>: Notifications to clients when payments are entered manually for events</li>
	<li><strong>New</strong>: Event Transaction overview is displayed on each event page</li>
	<li><strong>New</strong>: Transaction Types have been moved and no longer reside within settings</li>
	<li><strong>General</strong>: Email tracking accuracy has been improved. If it says it has been opened, 
		you can be sure that the Client has received and opened the email</li>
	<li><strong>Bug Fix</strong>: Printing playlist no longer shows menu</li>
	<li><strong>Bug Fix</strong>: Email playlist corrections</li>
	<li><strong>General</strong>: All outbound emails are sent from the defined system address. If your settings dictate that emails come from DJ's,
		the DJ's name will be displayed and the reply-to address will be set to that of the DJ too. This also addresses an issue whereby
		DJ's who have email addresses that do not end in the same domain name as the website where MDJM is installed, cannot send emails
		due to security controls</li>
	<li><strong>General</strong>: Digital contract signing now requires the client to re-enter their password as an additional verification step</li>
	<li><strong>Bug Fix</strong>: Strange actions if the Availability widget was displayed at the same time as an Availability form within the main content</li>
	<li><strong>General</strong>: Begun updating <?php echo MDJM_APP; ?> pages for HTML5 &amp; CSS3 compliance. Not yet completed</li>
	<li><strong>New</strong>: Create backups of the MDJM database tables and download within the debugging screen</li>
	<li><strong>General</strong>: Significant improvements to the application debugging. No annoying notification when debugging
		is enabled, however we still only recommend to enable when you are experiencing an issue</li>
</ul>

= 1.1.3.3 =
<strong>Released Thursday 14th May, 2015</strong>
<ul>
	<li><strong>General</strong>: You can now add custom content above MDJM shortcode content within Client Zone pages</li>
        <li><strong>General</strong>: Support for WordPress 4.2.3 (currently in alpha)</li>
</ul>

= 1.1.3.2 =
<strong>Released Friday 8th May, 2015</strong>
<ul>
	<li><strong>General</strong>: Full support for WordPress version 4.2.2</li>
        <li><strong>Bug Fix</strong>: Missing space within Client Zone playlist management page...&quot;Your playlist currently has ...entries&quot;</li>
        <li><strong>Bug Fix</strong>: CSS Correction within Availability widget</li>
        <li><strong>Bug Fix</strong>: Error when sending playlist via email</li>
</ul>

= 1.1.3.1 =
<strong>Released Monday, 27th April 2015</strong>
This is a bug-fix release only
<ul>
	<li><strong>Bug Fix</strong>: In certain circumstances, if you do not have events in the <code>Approved</code> status, no events were displayed in the events list</li>
	<li><strong>Bug Fix</strong>: Relating to the above, the status links did not work in the events list</li>
</ul>
Watch out for the next major release of MDJM...coming soon to include a re-designed Event interface, greater email tracking, faster response times and much more!

= 1.1.3 =
<strong>Released Tuesday, 21st April 2015</strong>
<ul>
	<li><strong>New</strong>: Officially supporting WordPress 4.2</li>
	<li><strong>General</strong>: Removed Add New option from Automated Tasks - this feature is still in development</li>
</ul>
Watch out for the next major release of MDJM...coming soon to include a re-designed Event interface, greater email tracking, faster response times and much more!

= 1.1.2 =
<strong>Released Tuesday, 17th March 2015</strong>
<ul>
	<li><strong>New</strong>: All emails sent via the system to clients and DJ's are logged</li>
	<li><strong>New</strong>: Track your clients opening of emails</li>
	<li><strong>New</strong>: Re-designed the Venues feature and added additional functionality</li>
	<li><strong>Bug Fix</strong>: If your web theme utilises white text some playlist entries where not visible within the front end</li>
	<li><strong>General</strong>: Cleaner Email and Contract Template tables</li>
	<li><strong>General</strong>: Code improvements, efficiency and cleanliness</li>
</ul>

= 1.1.1 =
<u>Released Tuesday, 10th March 2015</u>
<ul>
	<li>New Settings options added - Payment Types & Transaction Types. Used for adding Event transactions</li>
	<li><strong>Bug Fix</strong>: Manage Playlist link was missing on the client home page when viewing a single event</li>
	<li><strong>Bug Fix</strong>: Mapped field not removed from Contact Form list if already assigned to field</li>
	<li><strong>Bug Fix</strong>: DJ's only see their own events and clients within the Events page</li>
	<li><strong>Bug Fix</strong>: Emails sent via the Communication Feature without a template failed</li>
	<li><strong>General</strong>: Client first and last names always have a capital letter when created via new event or contact form</li>
	<li><strong>General</strong>: Events table defaults to sorted by event date</li>
	<li><strong>General</strong>: Added colour picker when setting error text colour for Contact Forms</li>
	<li><strong>General</strong>: Custom verification messages for Contract and Email Template updates</li>
	<li><strong>General</strong>: Further improvements to the Debugging system</li>
	<li><strong>General</strong>: Updated the uninstallation script</li>
</ul>

= 1.1 =
<strong>New Features</strong>
<strong>PayPal Integration</strong> for online Client Payments via your website<br />
Mobile DJ Manager for WordPress is now fully integrated with PayPal enabling you to take online payments securely via your website.
<ul>
	<li>No PayPal account is needed by Clients in order to make payments</li>
	<li>Accepts payments from all major credit cards, as well as funds within the Client's PayPal account</li>
	<li>Clients can choose to pay the Booking Fee/Deposit, or the full event balance</li>
	<li>Full PayPal integration means the MDJM application receives information from the PayPal IPN API system and updates the booking and journal automatically after verifying payment is completed</li>
	<li>Automatically sends your client an email based on a template of your choosing when payment is verified</li>
	<li>Ability to apply taxes</li>
	<li>Multi-Currency support for GBP, EUR, & USD</li>
	<li>Supports customised PayPal checkout pages</li>
	<li>Customise the display of the payment form</li>
	<li>Immediate notifications in the Admin interface when you have new "Unattended" enquiries</li>
	<li>Supports the PayPal sandbox environment so full testing can take place without real payments</li>
</ul>
<strong>Transaction Page</strong><br />
A new Transactions page has been added to the MDJM system and is available via the Mobile DJ Manager menu's within the WordPress admin interface.

This page is only available to Admins and if the Payment features is enabled and has been introduced to compliment the new online payments system as described above.

For now, the page simply lists any transactions that have been processed via PayPal and any other data relevant to that transaction. We will continue to develop this feature in up and coming versions.

<strong>Events Table</strong><br />
The Events page has been updated slightly to be a little more intuitive. Unattended enquiries are now listed as priority and with a red background.

The majority of updates to this page were to do with better, cleaner coding resulting in faster loading times and more efficient lookups.

<strong>Bug Fixes &amp; Minor Enhancements</strong>
	<ul>
		<li><strong>New</strong>: Added <code>Make A Payment</code> link to the Client home page if PayPal is enabled for Client events that are due a deposit of balance payment</li>
		<li><strong>New</strong>: Enabled the <strong>Add Media</strong> button within the Communications page. You can now include images in your Client Communications</li>
		<li><strong>New</strong>: Added buttons in Playlist view to email the event playlist to yourself or print it</li>
		<li><strong>New</strong>: <strong>Payments</strong> tab added to the Settings page to support the new <a href="https://www.paypal.com/" target="_blank" title="PayPal">PayPal</a> online payments feaure</li>
		<li><strong>New</strong>: Added sub-menu items to the admin toolbar Settings item</li>
		<li><strong>New</strong>: <strong>{CONTACT_URL}</strong> <a href="http://www.mydjplanner.co.uk/shortcodes/" target="_blank">Shortcode</a> added</li>
		<li><strong>New</strong>: <a href="http://www.mydjplanner.co.uk/shortcodes/" target="_blank">Shortcodes</a> added to support the new online payments system. To be used within the verification email template</li>
		<ul>
			<li>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<strong>{PAYMENT_AMOUNT}</strong>: Inserts the amount received by the payment</li>
			<li>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<strong>{PAYMENT_DATE}</strong>: Inserts the date payment was received as determined by PayPal</li>
			<li>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<strong>{PAYMENT_FOR}</strong>: Inserts <strong>Deposit</strong> or <strong>Balance</strong> depending on the payment received</li>
		</ul>
		<li><strong>New</strong>: Setting added <strong>Deposit Label</strong> enabling you to change the terminology used on both the front end and backend of your website. Some people prefer <code>Booking Fee</code> for example. Defaults to <strong>Deposit</strong>. Remember to update any email or contract templates as necessary</li>
		 <li><strong>New</strong>: Setting added <strong>Balance Label</strong> enabling you to change the terminology used on both the front end and backend of your website. Defaults to <code>Balance</code>. Remember to update any email or contract templates as necessary</li>
		<li><strong>Bug Fix</strong>: Slight adjustment to the Contact Forms validation scripts. In some instances determined during testing (no bug reports) the jQuery validation did not work correctly</li>
		<li><strong>Bug Fix</strong>: WordPress "reserves" some form field names such as <strong>name</strong> so if you used this field name within the MDJM Contact Forms, the form did not submit correctly. This is rectified</li>
		<li><strong>General</strong>: Updated the uninstall script</li>
		<li><strong>General</strong>: Added the <code>Date Added</code> column to the playlist table admin view. List is sorted by this column as default</li>
		<li><strong>TODO</strong>: Editing and ordering of Contact Form fields</li>
	</ul>

= 1.0 =
<h2>New Features</h2>
Our fully customisable Contact Forms enable full management of events from the initial client enquiry all the way through to the completion of the event.

<ul>
	<li>Create as many Contact Forms as you need and display them either on a single page in amongst your own contact, or via a widget that is displayed on multiple pages</li>
	<li>Configure each individual form to meet your requirements</li>
	<li>Map form fields directly to Client or Event fields</li>
	<li>Create clients and event enquiries when the form is submitted</li>
	<li>Immediately respond to the client once they submit the form with a pre-defined template</li>
	<li>Customise each individual field as necessary</li>
	<li>
		<ul>
			<li>Include a date picker</li>
			<li>Specify which fields are required to be completed before submission</li>
			<li>Specify your own CSS class</li>
			<li>Include placeholder hints</li>
			<li>& more</li>
		</ul>
	</li>
	<li>Include text fields, free text areas, checkboxes, date fields, select (drop down) fields & more</li>
	<li>jQuery Validation</li>
	<li>Point successful Availability Checks to your MDJM Contact Form page for additional functionality</li>
</ul>

<h2>Bug Fixes &amp; Minor Enhancements</h2>
<ul>
	<li><strong>New</strong>: Create Clients directly from the Add New Event screen as part of the event creation process</li>
	<li><strong>New</strong>: MDJM Contact Form Widget enabling you to add your MDJM Contact Form to multiple web pages quickly and easily</li>
	<li><strong>New</strong>: Setting added <strong>New Enquiry Notifications</strong>. When checked, a notification will be displayed at the top of the WP Admin pages if there are new <strong>Unattended Enquiries</strong> that need attention. These notifications are only displayed to Administrators. The Setting is enabled by default</li>
	<li><strong>New</strong>: Once an event is Approved, you can now click on the status within the Event Listing page and view the Client's signed contract</li>
	<li><strong>Bug Fix</strong>: The Year drop down list within the Availability page was showing blank instead of 2015</li>
	<li><strong>Bug Fix</strong>: If you had your WordPress Permalink Settings set to the default of <strong>Default</strong> (also referred to as "Ugly") the Client Zone links did not work correctly for Clients when logged in</li>
	<li><strong>Bug Fix</strong>: In some instances the links within the Client Zone did not work correctly due to a conflict in configuration</li>
</ul>