=== Mobile DJ Manager ===
Contributors: mikeyhoward1977
Tags: DJ, Mobile DJ, DJ Planning, Event Planning, CRM, Event Planner, Mobile Disco, Disco, Event Management, DJ Manager, Mobile DJ Manager
Requires at least: 3.9.1
Tested up to: 4.1
Stable tag: 0.9.9.7
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Mobile DJ Manager is an interface allowing mobile DJ's and businesses to manage their events and employees as well as interact with their clients easily. Automating many of your day to day tasks, Mobile DJ Manager for WordPress is the ultimate tool for any Mobile DJ Business.

== Description ==

Mobile DJ Manager is the solution for Mobile DJ's who work on their own or businesses that employ multiple DJ's.

It is an event planning solution built specifically for websites running on WordPress ensuring the users are familiar with the Administration settings and look and feel.

Mobile DJ Manager allows you to manage your events from enquiry stage through to completion. Add your DJ's, your clients and then create an event.

As a site admin you will be able to view all information, but as a DJ, depending on the permissions set by the Administrator, you will only see those events that are specific to you.

Email automation is also built in, depending on your chosen settings. Quotes can be automatically sent to clients when an event is created, contracts can be issued automatically and digitally signed, and booking confirmations also emailed to both clients and DJ's alike.

As a client, you gain access to a number of features via the main website once you login. These include;
*	Profile management ensuring that the DJ has all the relevant contact details at all times
*	Playlist management
*	Invite guests to add songs to the playlist via a unique URL. Clients can remove songs they do not deem appropriate

The main dashboard provides a complete overview of your business detailing the number of events and earnings over the month and year.

All in all Mobile DJ Manager helps you to run your DJ business efficiently.

== Installation ==

Automated Installation

1. Login to your WordPress administration screen and select the "Plugins" -> "Add New" from the menu
3. Enter "Mobile DJ Manager" into the Search Plugins text box and hit Enter
4. Click "Install Now" within the Mobile DJ Manager plugin box
5. Activate the plugin once installation is completed

Manual Installation

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

1. An overview fo the Mobile DJ Manager Dashboard screen as seen by an Administrator when they are logged into the WordPress Admin UI
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

= 0.9.9.7 =
As this release comes shortly after 0.9.9.6 please see those release notes below also...
<h2>Bug Fixes &amp; Minor Enhancements</h2>
<ui>
	<li>Bug: Event quotes were using template set in settings even if you selected an alternative during event creation</li>
	<li>Bug: jQuery bug on main WP Dashboard resolvedstopping availability datepicker from showing resolved.</li>
	<li>New Setting added <span class="code">New Enquiry Notifications</span>. When checked, a notification will be displayed at the top of the WP Admin pages if there are new <span class="code">Unattended Enquiries</span> that need attention. These notifications are only displayed to Administrators. The Setting is enabled by default. <strong>Note that this feature will not be active until the next major release</strong></li>
</ui>

= 0.9.9.6 =
<h2>Bug Fixes &amp; Minor Enhancements</h2>
<ui>
	<li>New <strong>Mobile DJ Manager Availability</strong> Widget added to the main WP Dashboard which displays an instant 7 day overview for all your staff and provides the ability for you to perform a quick availability lookup as soon as you have logged into your WordPress Admin interface</li>
	<li>New: We have now added functionality within the DJ view screen to mark DJ's as inactive. Inactive DJ's are not displayed within the create event screen in the <span class="code">Select DJ</span> drop down menu. Use this function in the same way as the Inactive Client's which was introduced in version 0.9.9.4</li>
	<li>Quick Availability Check added to main MDJM Dashboard</</li>
	<li>New Setting <span class="code">Unavailability Email Template</span> which enables you to define a template to be used as default when advising clients of unavailability... more on this soon ;)</li>
	<li>Event listing is now alphabetical within the Create Event and Edit Event pages</li>
	<li>Removed Bulk Actions drop down and associated checkboxes from the Event List pages. Two main reasons for this. There was/is a bug whereby multiple Journal entries were created when some actions where processed. We're not sure if we will re-introduce the Bulk Actions for these pages. If you find them useful, <a href="http://mydjplanner.co.uk/forums/general-support/">let us know</a>.</li>
	<li>Bug Fix: If you had more than 10 venues, you could not edit venues with an ID greater than 9</li>
	<li>Further enhancements to debugging</li>
	<li>Venue Listing drop down lists are now alphabetical</li>
	<li>Bug Fix: Automated Task <strong>Complete Events</strong> marked events as completed on the day of the event if the event finish time was after midnight</li>
	<li>Bug Fix: Entries Uploaded count was not displaying when editing the <strong>Upload Playlist</strong> Automated Task</li>
	<li>Bug Fix: Playlist entries were not uploading to the MDJM servers reliably</li>
	<li>Front end Availability form validation improvements - no longer using annoying pop-up alert if no date is entered, but instead using jQuery</li>
</ui>
			
= 0.9.9.5 =
<h2>New Features</h2>
We have added a drop down field to the event creation process that becomes visible if you select the option to Email Quote?. The drop down list Select email Template to Use enables you to select any of your email templates to use when emailing the Client with their quotation. By default, the option you have set within Settings is selected.
This provides the flexibilty for you to create different templates for use with different event types should you wish to.
If you have Permissions configured to allow DJ's to create events, the Disabled Templates for DJ's setting applies.

<h2>Bug Fixes &amp; Minor Enhancements</h2>
<ui>
	<li>AdjustedCommunications page. Send Email to: dropdown now seperates Clients &amp; DJ's in a better format</li>
	<li>An additional debugging option has been added to enable more in-depth debugging by the MDJM Support Team. Clicking the Submit Debug Files button, sends over information regarding your MDJM Settings to the Support Staff.</li>
</ui>

= 0.9.9.4 =
<h2>New Features</h2>
<ui>
	<li>Enabled the sending of contracts via email to clients from within the Communication Feature</li>
	<li>Added ability to mark Clients as inactive to avoid buys Client list when creating an Event</li>
</ui>
<h2>Bug Fixes &amp; Minor Enhancements</h2>
<ui>
	<li>New Setting added to determine the number of results displayed on the Clients, Events and the Venues list pages</li>
	<li>Enabled pagination on the Events and the Venues list pages.</li>
	<li>Enabled searching within the Event list screen, the Client list screen and the Venue list screen.</li>
	<li>Big Fix: When a Client booked an event via the Client Zone an email was sent to them event if the Contract link to client? setting was disabled.</li>
	<li>Main dashboard indicated DJ was working today even if the event status was not Approved</li>
	<li>Confirmation message displayed to a client when they book an event or approve their contract via the Client Zone now only displays the <strong>You will receive confirmation via email shortly</strong> message if you have configured emails to be sent in settings.</li>
</ui>

If you have an idea for a new feature, or an improvement to an existing one, <a href="http://www.mydjplanner.co.uk" target="_blank">let us know here.</a>

= 0.9.9.1 =
<h2>Minor Release with a few enhancements</h2>
<ui>
	<li>Bug fix: Event date check returned odd results sometimes</li>
	<li>New Setting: Added setting to disable the incomplete profile warning displayed to clients when they logged into the Client Zone if key information is missing</li>
	<li>New Setting: To choose whether or not the <strong>Client</strong> receives the Booking Confirmation email once contract is signed / event status changes to Approved</li>
	<li>New Setting: To choose whether or not the <strong>DJ</strong> receives the Booking Confirmation email once contract is signed / event status changes to Approved</li>
</ui>

= 0.9.9 =
<h2>Enhancements</h2>
<li>Introduction of the Availability Checker & Management feature</li>
<li>Added support for the EUR and USD currencies</li>
<li>Added support for changing short date format</li>
<li>Change the display name of default client fields</li>
<li>Customise frontend text within the Playlist client zone pages</li>
<li>Select who each of the system emails are from</li>
<li>We have added the TinyMCE editor to the Settings textarea's where you can manipulate text displayed to your clients on your website enabling you to format text, add links etc. with ease</li>
<li>Automated Task "Complete Events" now checks the end time of the event as well as the date</li>
<li>Added Debugging option to the Settings page. Not recommended for use unless the Mobile DJ Manager for WordPress support team ask you to enable it</li>
<li>Validate event date during event creation to ensure it is present and not in the past</li>
<li>Added Created Date to Edit Event screen to display the date the event was first loaded</li>
<li>Added Last Login time to the DJ List</li>
<li>Date selectors now include drop downs to change month & year and also start on the day of the week configured within your WordPress settings (was previously always Sunday)</li>
<li>Improved uninstallation procedures</li>
<li>The Mobile DJ Manager widget on the main WP Dashboard no longer includes Failed Enquiries in Today's status</li>
<li>Official support for WordPress 4.1</li>

= 0.9.8 =
<h2>Bug Fixes</h2>
<li>Resetting Client Password during event creation was not always successful</li>

= 0.9.7 =
<a href="http://www.mydjplanner.co.uk/version-0-9-7-released/" target="_blank">Read the detailed release notes here</a>
<h2>Bug Fixes</h2>
<ui>
	<li>Contract review emails were generated and sent even if the <a href="http://www.mydjplanner.co.uk/settings-overview/" target="_blank">setting</a> was not enabled</li>
	<li>DJ's should only see their own Clients within the Communication feature</li>
	<li>DJ's now only see contact information for Clients when clicking their name on the Client screen, unless they have permission to add new clients</li>
	<li>Clicking on a Clients email address now directs you to the Communication Feature with the client auto selected</li>
	<li>DJ setup time now defaults to event start time</li>
	<li>Tighter Security: If you do not provide DJ's with the permission to Add Clients, they cannot Edit Clients either and the Add New button is no longer displayed within the Client Details page</li>
	<li>If you have not enabled DJ's to add venues, they cannot view them either except in the event detail screen</li>
	<li>Edit Venue button removed for DJ's that if they do not have permission to add a venue</li>
	<li>As reported in <a href="http://www.mydjplanner.co.uk/forums/topic/error-message2/">this bug</a> depending on the PHP configuration of your web server, a warning message may have been displayed when Adding, Editing, or Deleting a venue. This did not affect functionality.</li>
</ul>

<h2>New & Enhanced Features</h2>
<ui>
	<li>Client password's can now be reset as part of the event creation process. Shortcodes also added to enable you to include this information within your quotation emails if you want to</li>
	<li>{EVENT_DATE_SHORT} shortcode added to enable you to display date as DD/MM/YYYY within emails</li>
	<li>Updated Permissions</li>
	<ui>
		<li><strong>DJ Can View Enquiry</strong>: Whether or not your employees can see outstanding (or failed) enquiries where they have been listed as the DJ. If this is not selected, the relevant information is also removed from the WP Dashboard and the MDJM Dashboard</li>
		<li><strong>Disabled Shortcodes for DJ's</strong>: Select which <a href="http://www.mydjplanner.co.uk/shortcodes" target="_blank">Shortcodes</a> your DJ's cannot use within the Communications feature. Whilst the <a href="http://www.mydjplanner.co.uk/shortcodes" target="_blank">Shortcodes</a> will still be visible, if a DJ tries to send an email with the disabled <a href="http://www.mydjplanner.co.uk/shortcodes" target="_blank">shortcodes</a> within the content, it will fail</li>
		<li><strong>Disabled Email Templates for DJ's</strong>: The Email Templates you select here will not be visible to DJ's when they are using the Communications feature</li>
	</ui>
	<li>Scheduled Tasks now have a new home. Removed from the Settings page and given their own menu item - Automated Tasks</li>
	<li>Added Setting for Client Dialogue text enabling you to specify your own text that will be displayed to clients within your website front end</li>
</ui>

= 0.9.6 =
<h2>Bug Fixes</h2>
<ui>
	<li>As reported in <a href="http://www.mydjplanner.co.uk/forums/topic/error-message/">this bug</a> depending on the PHP configuration of your web server, a warning message may have been displayed when Converting, Completing, Failing, or Recovering and event. This did not affect any functionality</li>
    <li>The Communication feature was unreliable if a client had multiple events in the system and also regarding copying in Admin/DJ. The overhaul described above addresses these bugs</li>
</ul>

<h2>New & Enhanced Features</h2>
<ui>
	<li>MDJM Shortcodes are supported within email subjects</li>
	<li>Communication Feature Updated</li>
	<ul>
		<li>Admins can now communicate with DJ's as well as all clients in the MDJM system. DJ's can only communicate with their own clients</li>
		<li>Once you have selected a recipient, you can select the event regarding which you are communicating with them, if you wish to do so. For clients, once selected you can select all events they have in your system. For DJ's, you can select all events at which they have, or will, be DJ'ing at.</li>
	</ul>
	<li>Additional fields added to the Event creation and editing process</li>
	<ul>
		<li><strong>DJ Setup Time</strong>: Enables you to enter a setup time for the event</li>
        <li><strong>DJ Setup Date</strong>: Just in case :)</li>
        <li><strong>DJ Notes</strong>: The ability for you to enter notes that only the Admins and event DJ will see</li>
        <li><strong>DJ Notes</strong>: The ability for you to enter notes that only the Admins will see</li>
	</ul>
	<li>New Shortcodes added to support new event fields</li>
	<ul>
        <li><span class="code">{DJ_SETUP_TIME}</span>: Inserts the setup time specified during event creation</li>
        <li><span class="code">{DJ_SETUP_DATE}</span>: Inserts the setup date specified during event creation</li>
        <li><span class="code">{DJ_NOTES}</span>: Inserts the information entered into the events DJ Notes field</li>
        <li><span class="code">{ADMIN_NOTES}</span>: Inserts the information entered into the events Admin Notes field</li>
	</ul>
</ul>

= 0.9.5 =
<h2>Bug Fixes</h2>
<ul>
	<li>As reported in <a href="http://www.mydjplanner.co.uk/forums/topic/emails-issues/">this bug</a> the From address of emails was not defaulting back to the WordPress Admin email address if unset</li>
	<li>Also reported in <a href="http://www.mydjplanner.co.uk/forums/topic/emails-issues/">this bug</a> Admins were copied in client emails even if the setting was not enabled</li>
    <li>Contract Date was always todays date, even when signed. Now shows date of signature if signed</li>
	<li>The Complete Events scheduled task was sending emails with subject of "0"</li>
	<li>Some scheduled tasks were sending notification emails to admin even when no actions taken</li>
</ul>

<h2>New & Enhanced Features</h2>
<ul>
	<li>You can now select how times are displayed. Within the DJ Manager > Settings page, set to either a 24 hour or 12 hour format
	<li>Added new option to the DJ Manager > Settings page enabling you to preifx the unique contracts ID if required. This prefix will also apply to invoices in a future release.</li>
	<li>Added shortcode <span class="code">{CONTRACT_DATE}</span>: Inserts the date of the contract. If the contract has been signed the date of signing is entered, otherwise it defaults to today</li>
    <li>Added shortcode <span class="code">{CONTRACT_ID}</span>: Inserts the unique ID of the contract. If a prefix has been set within <a href="<?php echo admin_url( 'admin.php?page=mdjm-settings' ); ?>">DJ Manager > Settings</a>, the prefix is also displayed</li>
</ul>

= 0.9.4 =
<h2>Bug Fixes</h2>
<ul>
	<li>Early adoptors of 0.9.3 may have experienced issues with saving venues into the venues database during event creation due to an incorrectly set DB attribute</li>
	<li>Some templated emails were not formatted correctly (too much spacing and/or invalid characters)</li>
	<li>Admin was not blind copied into emails even if setting was set</li>
	<li>Client contract page was sometimes denying access if accessed via direct URL</li>
	<li>Client Playlist page no longer shows odd date if the event has passed</li>
	<li>Removed the Email Templates tab from the Settings pages as this is no longer used since version 0.9.3</li>
	<li>Displays the number of playlist entries uploaded to MDJM in the Upload Playlists edit view of the scheduler</li>
</ul>

<h2>New & Enhanced Features</h2>
<ul>
	<li>Customise the subject of emails for Enquiries, Contracts, and Booking Confirmations</li>
	<li>Customise the Admin email address</li>
	<li>Enhanced the Client pages</li>
	<li>Now supports multiple events per client</li>
	<li>All event's are displayed to client - previously only confirmed (approved events)</li>
	<li>Clients can accept quotations, sign contracts, decline quotes</li>
</ul>

= 0.9.3 =
<h2>Bug Fixes</h2>
<ul>
<li>Occasionally odd characters were appearing in emails generated by the application</li>
<li>Occasional incorrect figures on the Dashboard have been corrected</li>
<li>In event edit view, Update Add-ons button was displayed even if no Add-ons were configured.</li>
<li>If no venues had been saved, there was no possibility to enter venue information whilst creating a new event.</li>
</ul>

<h2>New & Enhanced Features</h2>
<ul>
<li>Email templates are now managed like posts. You can now add your own templates as well as customise the default ones</li>
<li>Scheduler - Let MDJM work for your business even while you are not!
<ui>
<li>Request payments from customers</li>
<li>Ask clients for feedback once their event is complete</li>
<li>Close enquiries that have been outstanding for a while</li>
<li>and more - fully customisable</li>
</ui>
</li>
<li>Added option to mark balance as paid in event editor view</li>
<li>Added the MDJM menu icon to Contracts &amp; Email Templates menu items to make them easier to identify</li>
</ul>

= 0.9.2 =
<ul>
<li>Bug fix with communication feature - missing info when sending email</li>
</ul>

= 0.9 =
* Lots of new features in this release so we have reverted all trials back to 30 days
<h2>Bug Fixes</h2>
<ul>
<li>Fixed issue with contract page where users could see contract even if not logged in</li>
<li>Fixed issue DJ not being blind copied in emails when setting is set</li>
<li>When an event is marked as completed/converted/failed/recovered you are now returned to the event list rather than a blank page with success message</li>
<li>Some general improvements to browsing/navigation in the Admin UI</li>
<li>Fixed navigational links in table views</li>
</ul>

<h2>New & Enhanced Features</h2>
<ul>
<li>Added "Mobile DJ Manager" menu items to the Admin toolbar for both frontend and backend when logged in</li>
<li>Added "Communications" - You can now communicate with clients directly from the MDJM Admin interface</li>
<li>Equipment packages have been introduced (activate in settings) - Add pre-defined packages to your events</li>
<li>Inventorise all your equipment</li>
<li>Add-ons are available for events</li>
<li>Lost enquiries can now be viewed and recovered</li>
<li>Enquiry Sources are now fully custmisable</li>
</ul>


= 0.8.1 =
<ul>
<li>The warning notice that was sometimes displayed after adding a new event has been resolved</li>
<li>Bulk action processes are now all working for Events</li>
<li>Convert, Fail, Cancel links are now functioning correctly</li>
</ul>

= 0.8 =
* Added MDJM shortcodes to TinyMCE to be used within email templates
* Finalised Client Fields settings page
* Added contracts - contracts are now stored in the application and clients can approved via the frontend
* Contracts can now be automatically sent to client when enquiries are converted or events status is changed to Pending
* New email template for client contract notification
* Added setting feature to auto email client contract
* Tidied up the install and uninstall procedures

= 0.7.2 =
* Added ability to view journal entries in the Admin UI
* New Events are now loaded as Enquiries
* Plugin update checker is now live
* Added email templates

== Upgrade Notice ==

= 0.8.1 =
* This upgrade is required to address some minor bugs