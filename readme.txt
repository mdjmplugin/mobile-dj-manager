=== MDJM Event Management ===
Contributors: mikeyhoward1977
Tags: MDJM, MDJM Event Management, Mobile DJ Manager, DJ, Mobile DJ, DJ Planning, Event Planning, CRM, Event Planner, DJ Event Planner, DJ Agency, DJ Tool, Playlist Management, Contact Forms, Mobile Disco, Disco, Event Management, DJ Manager, DJ Management, Music, Playlist, Music Playlist
Requires at least: 4.4
Tested up to: 4.6
Stable tag: 1.3.7.8
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html
Donate link: http://mdjm.co.uk/support-our-work/

MDJM Event Management is an interface to fully manage your DJ/Events or Agency business efficiently.

== Description ==

MDJM Event Management is the solution for Mobile DJ's who work on their own or businesses that employ multiple DJ's.

It is an event planning solution built specifically for websites running on WordPress ensuring the users are familiar with the Administration settings and look and feel.

MDJM Event Management allows you to manage your events from enquiry stage through to completion. Add your DJ's, your clients and then create an event.

As a site admin you will be able to view all information, but as a DJ, depending on the permissions set by the Administrator, you will only see those events that are specific to you.

Email automation is also built in, depending on your chosen settings. Quotes can be automatically sent to clients when an event is created, contracts can be issued automatically and digitally signed, and booking confirmations also emailed to both clients and DJ's alike.

As a client, you gain access to a number of features via the main website once you login. These include;
*	Profile management ensuring that the DJ has all the relevant contact details at all times
*	Playlist management
*	Secure online payments via PayPal
*	Digitally sign event contracts
*	Invite guests to add songs to the playlist via a unique URL. Clients can remove songs they do not deem appropriate

The main dashboard provides a complete overview of your business detailing the number of events and earnings over the month and year.

All in all MDJM Event Management helps you to run your DJ business efficiently.

== Installation ==

**Automated Installation**

1. Login to your WordPress administration screen and select "Plugins" -> "Add New" from the menu
1. Enter "MDJM Event Management" into the Search Plugins text box and hit Enter
1. Click "Install Now" within the MDJM Event Management plugin box
1. Activate the plugin once installation is completed

**Manual Installation**

Once you have downloaded the plugin zip file, follow these simple instructions to get going;

1. Login to your WordPress administration screen and select the "Plugins" -> "Add New" from the menu
1. Select "Upload Plugin" from the top of the main page
1. Click "Choose File" and select the mobile-dj-manager.zip file you downloaded
1. Click "Install Now"
1. Once installation has finished, select "Activate Plugin"
1. Once activation has completed, click the "Settings" link under the MDJM Event Management plugin
1. Installation has now completed. Next you need to <a title="Initial Configuration" href="http://mdjm.co.uk/docs/installation/">configure MDJM</a>.

== Frequently Asked Questions ==

= Is any support provided? =

Support can be obtained via our online [Support Forums](http://www.mydjplanner.co.uk/support/ "MDJM Support Forums") at or via our [Facebook User Group](https://www.facebook.com/groups/mobiledjmanager "MDJM Facebook User Group").
Support is provided on a best-endeavours basis. For premium support you must be an active license holder for on of the [MDJM Event Management add-ons](http://mdjm.co.uk/add-ons/ "MDJM Event Management add-ons")

= Is there a Pro version with additional features? =

Premium addons are available to enhance the plugin at http://mdjm.co.uk/add-ons/

== Screenshots ==

1. An overview of the MDJM Event Management Dashboard screen as seen by an Administrator when they are logged into the WordPress Admin UI
2. The Dashboard as seen by a DJ (employee) when they are logged in
3. A view of the settings page (not seen by DJs)
4. Permissions can be set to determine what a DJ can see/do within the application
5. Event listing within the Admin UI
6. The client's homepage on the website front end
7. The playlist feature as utilised by clients

== Changelog ==

= 1.3.7.8 =

**Released Sunday 3rd July, 2016**

**Bug Fixes**
* Need to sanitize deposit / balance payment to correctly identify if paid

**Tweaks**
* Added new mdjm-alert css classes to be introduced across the system in due course

= 1.3.7.7 =

**Released Saturday 2nd July, 2016**

**Bug Fixes**
* Do not save event cost as a formatted value. Causes issues with some currencies. Issue [#105(https://github.com/mdjm/mobile-dj-manager/issues/105/ "Issue #105")

**Tweaks**
* Added function `mdjm_get_event_txns()`
* Added function `mdjm_list_event_txns()`
* Use `mdjm_get_event_txns()` when calculating total event income
* Use `mdjm_list_event_txns()` for `{payment_history}` content tag
* Better sanitization of currency values
* Comment corrections

= 1.3.7.6 =

**Released, Sunday 26th June 2016**
**Bug Fixes**

* Issue [#100(https://github.com/mdjm/mobile-dj-manager/issues/100/ "Issue #100") PLaylist count was not displayed correctly when using view filters
* Unattended events should not have the deposit/balance checkboxes checked
* Hold the selection when the Event Type filter is used
* Remove possibility of PHP notice generation when no client events are within filter
* Spelling correction in `$mdjm_allowed_taxonomies` variable

= 1.3.7.5 =

**Released, Saturday 25th June 2016**
**Bug Fixes**
* Issue [#101(https://github.com/mdjm/mobile-dj-manager/issues/101/ "Issue #101") Corrected {guest_playlist_url} output and redirect visits to incorrect URL

= 1.3.7.4 =

**Released, Friday 24th June 2016**
**Bug Fixes**
* Issue [#97(https://github.com/mdjm/mobile-dj-manager/issues/97/ "Issue #97") Process deposit/balance payments after meta updates in case their values have changed
* Issue [#96(https://github.com/mdjm/mobile-dj-manager/issues/96/ "Issue #96") Corrected display of un-saved event address fields and ensure that the correct value is initially selected for the venue list

**Tweaks**
* When creating a new event default to ` - Select Venue - `


= 1.3.7.3 =

**Released, Friday 24th June 2016**
**Bug Fixes**
* Issue [#97(https://github.com/mdjm/mobile-dj-manager/issues/97/ "Issue #97") Process deposit/balance payments after meta updates in case their values have changed
* Issue [#96(https://github.com/mdjm/mobile-dj-manager/issues/96/ "Issue #96") Corrected display of un-saved event address fields and ensure that the correct value is initially selected for the venue list

= 1.3.7.2 =

**Released, Thursday 23rd June 2016**
**New**
* Select how you want events to be sorted on the admin screen. `Default Order By` and `Default Order` setting options added within MDJM->Settings->Events

**Bug Fixes**
* Issue [#94(https://github.com/mdjm/mobile-dj-manager/issues/94/ "Issue #94") Venue content tags not returning correct values
* Issue [#84(https://github.com/mdjm/mobile-dj-manager/issues/84/ "Issue #84") `Enable Playlist` not remaining checked
* Issue [#93(https://github.com/mdjm/mobile-dj-manager/issues/93/ "Issue #93") Event type filter should display event count for each event type
* Issue [#92(https://github.com/mdjm/mobile-dj-manager/issues/92/ "Issue #92") Maintain current event status view when using event filters
* Issue [#90(https://github.com/mdjm/mobile-dj-manager/issues/90/ "Issue #90") Save the `DJ` setup date value when after it is auto populated
* Issues [#88(https://github.com/mdjm/mobile-dj-manager/issues/88/ "Issue #88") and [#83(https://github.com/mdjm/mobile-dj-manager/issues/83/ "Issue #83") Deposit Paid should not be checked when creating a new event. Ensure that the Balance Paid checkbox holds is checked status

**Tweaks**
* Remove deprecated use of `MDJM_MULTI` from the `mdjm_event_post_filter_list()` function
* Added `mdjm_get_employee_address()` function
* Added `mdjm_get_employee_post_code()` function

= 1.3.7.1 =

**Released, Monday 20th June 2016**
**Bug Fixes**
* Make sure we catch custom event fields that are set as checkboxes during event save
* Possible PHP notice generated during event save if no venue address defined

**Tweaks**
* Display venue information field on event screen

= 1.3.7 =

**Released, Sunday 19th June 2016**

**New**
* Event screen metabox facelift
* Client list is searchable on event screen
* Checking the deposit or balance paid checkboxes on the events screen now creates transactions for the remaining amounts
* Checking the balance paid checkbox auto sets the deposit to the paid status
* Manual event payments from the events screen now determine if the deposit and/or balance for the event is paid and mark as appropriate
* Manual event payments from the events screen now generate an email using the selected `Manual Payment Template`
* Added content tag `{deposit_remaining}`
* New setting `Set Client Inactive?`. When enabled, a client will be set as inactive when their event is cancelled, failed, or rejected and they have no further upcoming events
* Determine if a client needs re-activating when an event is recovered
* Request [#76](https://github.com/mdjm/mobile-dj-manager/issues/76/ "Request #76") Added content tag `{client_alt_phone}` to display the clients additional phone # if provided
* Request [#79](https://github.com/mdjm/mobile-dj-manager/issues/79/ "Request #79") Form validation on communication page
* Add a new venue directly from the event screen
* Toggle details for clients, venues, and transactions on event screen

**Tweaks**
* Client list is readonly if event is no longer active
* Pre-load $employee_id within the MDJM_Event class
* Added function `mdjm_get_clients_next_event()` to replace method in deprecated MDJM_Events class
* Don't display view contract link if contract not digitally signed. Applies to both admin and Client Zone
* Make sure client is shown in dropdown when viewing a non-active event and the client is set as inactive
* New class `MDJM_HTML_Elements` for easier display of input fields
* Custom client fields are now displayed two fields per row
* Custom event fields are now displayed two fields per row
* Added function `mdjm_get_event_remaining_deposit()`
* Added functions `mdjm_get_taxonomy_labels()` and `mdjm_get_post_statuses()`
* Added function `mdjm_month_num_to_name()`
* Added function `mdjm_sanitize_key()`
* Added function `mdjm_get_event_deposit_type()`
* Added `get_start_time()`, `get_finish_time(`, `get_package()`, `get_addons()` to MDJM_Event class
* Added method `get_remaining_deposit()` to MDJM_Event class
* Make sure all ajax functions are prefixed `mdjm_` and suffixed `_ajax`
* Better formatting of currencies
* Added functions `mdjm_get_enquiry_sources()` and `mdjm_enquiry_sources_dropdown()`
* Added client functions `mdjm_get_client_full_address()`, `mdjm_get_client_last_login()`, `mdjm_do_client_details_table`
* Removed deprecated methods from soon to be deprecated MDJM_Txns class
* Correctly sanitize venue details for display

**Bug Fixes**
* Events should not be publicy queryable
* If deposit type is set to percentage of total cost, a JS error ocurred when focusing out of the total cost field
* Issue [#70](https://github.com/mdjm/mobile-dj-manager/issues/70/ "Issue 70") Client phone number does not save when created on the events screen
* Issue [#72](https://github.com/mdjm/mobile-dj-manager/issues/72/ "Issue 72") Use remove_query_arg for redirect link on login page
* Issue [#73](https://github.com/mdjm/mobile-dj-manager/issues/73/ "Issue 73") Admin is unable to review signed contracts
* Issue [#75](https://github.com/mdjm/mobile-dj-manager/issues/75/ "Issue 75") `Unattended Enquiry` and `Enquiry` admin bar links had extra whitespace causing link to malfunction
* Issue [#77](https://github.com/mdjm/mobile-dj-manager/issues/77/ "Issue 77") `{application_home}` content tag not correctly returning URL

= 1.3.6 =

* **New**    : MDJM now uses internal login functions for the Client Zone login process
* **New**    : Added `[mdjm-login]` shortcode to present a login form on any page
* **New**    : Added `{event_employees}` and `{event_employees_roles}` content tags
* **Bug Fix**: Issue [#68](https://github.com/mdjm/mobile-dj-manager/issues/68/ "Issue 68") `{guest_playlist_url}` is missing part of the string

= 1.3.5.5 =

* **Bug Fix**: Issue [#66](https://github.com/mdjm/mobile-dj-manager/issues/66/ "Issue 66") Template content does not load when a recipient is auto loaded into the communication page
* **Bug Fix**: Missing `</div>` tag from single-event template file
* **Tweak**  : Added `None` option to `Manual Payment Confirmation Template` setting

= 1.3.5.4 =

* **Bug Fix**: Issue [#63](https://github.com/mdjm/mobile-dj-manager/issues/63/ "Issue 63") Incorrect formatting caused transactions over 999.99 to be displayed as 1
* **Bug Fix**: Adding event transaction ajax function now works in Firefox browser
* **Bug Fix**: Correctly manage arrays within the $copy_to var in the communication page
* **Tweak**  : All admin ajax functions re-written and consolidated into single file except for soon to be deprecated validation
* **Tweak**  : Added `Added method get_remaining_deposit()` to MDJM_Event class
* **Tweak**  : Changed ajax loader image from spinner
* **Tweak**  : Added button class to Add New link for Event Types on events screen
* **Tweak**  : Added button class to Remove Employee link events screen
* **Tweak**  : Moved `MDJM_Employee_Table` class to start of file 
* **Tweak**  : Removed New -> Quote from admin bar

= 1.3.5.3 =

* **Tweak**: Login form is now aligned and responsive
* **Bug Fix**: Spelling correction in function name

= 1.3.5.2 =

* **Bug Fix**: Corrected output for contract signed date
* **Bug Fix**: Issue [#60](https://github.com/mdjm/mobile-dj-manager/issues/60/ "Issue 60") Random echo statement causes PHP notice on employee permission update
* **Tweak**: Added content tag `{event_package_description}`
* **Tweak**: Better formatting for event price
* **Tweak**: Client filter is sorted alphabetically
* **Tweak**: GitHub links now direct to organisation


= 1.3.5.1 =

* **Bug Fix**:  Issue [#53](https://github.com/mdjm/mobile-dj-manager/issues/53/ "Issue 53") Do not double format the event deposit cost
* **Bug Fix**:  Issue [#54](https://github.com/mdjm/mobile-dj-manager/issues/50/ "Issue 54") Display action buttons even when no event ID is passed
* **Tweak**: Allow filtering of the number of action buttons being displayed via `add_filter( 'mdjm_action_buttons_in_row', '*your_function*' );`. Default is 4
* **Tweak**: Reduced font on action buttons to 12pt
* **Tweak**: Updated translation POT file

= 1.3.5 =

* **New**: Event action buttons within Client Zone are now responsive on the single event page. **`event-single.php` copies need updating**
* **New**: Added Font Awesome icons to event action buttons within Client Zone
* **New**: Added Quick Reject to Unattended Events view Bulk Actions
* **New**: Added Deposit status icon to event list due column. Icon indicates balance has been paid
* **Bug Fix**: Issue [#50](https://github.com/mdjm/mobile-dj-manager/issues/50/ "Issue 50"). Extra div close tag on quotes page
* **Bug Fix**: Issue [#51](https://github.com/mdjm/mobile-dj-manager/issues/51/ "Issue 51"). Availability widget does not redirect with Ajax turned on
* **Bug Fix**: Issue [#49](https://github.com/mdjm/mobile-dj-manager/issues/49/ "Issue 49"). Correctly decode html entities within email subject
* **Bug Fix**: Issue [#47](https://github.com/mdjm/mobile-dj-manager/issues/47/ "Issue 47"). `{quotes_url}` content tag missing event ID
* **Bug Fix**: Issue [#46](https://github.com/mdjm/mobile-dj-manager/issues/46/ "Issue 46"). Fixed deposits not inserted if event has zero cost
* **Bug Fix**: Re-added package and add-on pricing to events edit screen
* **Tweak**: Added translation to bulk action entry `Add Role`
* **Tweak**: Removed deprecated constant MDJM_PAYMENTS
* **Tweak**: Added `mdjm_compare_template_version()` and `mdjm_get_template_files()` functions

= 1.3.4.1 =

* **Bug Fix**: Issue [#41](https://github.com/mdjm/mobile-dj-manager/issues/41/ "Issue 41"). Rejected enquiries need to have status updated even if no reject reason is provided.
* **Bug Fix**: Issue [#42](https://github.com/mdjm/mobile-dj-manager/issues/41/ "Issue 42"). `{contract_url}` generating incorrect output.

= 1.3.4 =

* **New**: Record employee event payments feature. See [Employee Event Payments](http://mdjm.co.uk/docs/employee-event-payments/ "MDJM Employee Event Payments")
* **New**: Added setting **Communicate Active Events Only**. If checked, only active events will be displayed on the communication screen
* **New**: Added `{available_addons}` and `{available_addons_cost}` content tags. See [Content Tags](http://mdjm.co.uk/docs/content-tags/ "Content Tags Available with MDJM Event Management")
* **New**: Added `mdjm_get_clients_next_event()` function
* **Bug Fix**: Issue #36. Saving venue within event can generate fatal error
* **Bug Fix**: Issue #30. Content tag `{event_addons_cost}` missing
* **Bug Fix**: Issue #31. Content tag `{event_package_cost}` missing
* **Bug Fix**: Issue #32. Bulk sending transactions to trash generates error
* **Bug Fix**: Missing close tag in event-none template file
* **Bug Fix**: Issue #33. Playlist page throws an error by design if no event_id is present
* **Bug Fix**: Issue #35. Shortcodes shouldn't use wp_die()
* **Bug Fix**: Issue #37. Content tags `{payment_for}` `{payment_amount}` `{payment_date}` missing for manual transactions
* **Bug Fix**: Issue #38. Content tags `{event_description}` `{admin_notes}` not generating output
* **Bug Fix**: Writing client email address instead of display_name to post meta
* **Bug Fix**: Issue #39. Check for `mdjmeventid` on guest playlist access to maintain backwards compatibility for existing events
* **Tweak**: Improved license handler class
* **Tweak**: Renamed core.php to plugins.php
* **Tweak**: Removed use of deprecated `MDJM_EVENT_POSTS` constant
* **Tweak**: Stripslashes from subject of communication page emails

= 1.3.3 =

* **Bug Fix**: Client contact link should auto set recipient on communication page
* **Bug Fix**: Issue #23. Datepicker css styling not being enqueued
* **Bug Fix**: Issue #24. Select historic dates but not future within manual transactions
* **Bug Fix**: Issue #25. Unable to filter by Transaction Type
* **Bug Fix**: Issue #26. Re-added auto population of communication fields and ability to click to communicate with employees and clients
* **Bug Fix**: Issue #27. Custom Event fields with a `\` in the name do not get processed. Re-check your custom content tags at *MDJM -> Events -> Custom Event Fields*
* **Bug Fix**: Issue #28. Restored availability checker within unattended events row actions
* **Tweak**: Restored Communication History to menu
* **Tweak**: Moved Employees menu item above availability
* **Tweak**: Do not restrict loading of jquery-ui.css to non SSL
* **Tweak**: Added ability to enter reject reason to journal
* **Tweak**: Removed deprecated file mdjm-functions-admin.php

= 1.3.2.1 =

* **Bug Fix**: Issue #22. The `{available_packages}` and `{available_packages_cost}` content tags result in a fatal error due to an incorrect function call

= 1.3.2 =

* **Bug Fix**: Issue #17. Venue details not displayed for saved venues when using content tags - `{venue_*}`
* **Bug Fix**: Using incorrect vairable name whilst setting venue admin notices
* **Bug Fix**: Issue #13. Incorrect spelling of *outgoing* for Transaction view
* **Bug Fix**: Issue #18. Event count not displaying against venues
* **Bug Fix**: Issue #19. Employee search returned zero results. Needed wildcard
* **Bug Fix**: Issue #12. Client search returned zero results. Needed wildcard
* **Bug Fix**: Issue #12. Client view `all` was empty
* **Bug Fix**: Issue #12. Client view not ordered. Default to display name ascending
* **Bug Fix**: Issue #16. Removed WP_Query pagination to ensure playlist count on event screen is correct
* **Tweak**: Removed deprecated `send_to_email()` method within the `MDJM_PlayList_Table` class

= 1.3.1 =

* **Bug Fix**: Ensure scheduled tasks are re-registered when re-activating the plugin

= 1.3 =

**Released Thursday 12th May, 2016**

* **New**: Use fully customisable templates for all MDJM Client Zone pages
* **New**: Employee interface replaces the old "DJ List"
* **New**: Updated Client interface replaces the old "Client List"
* **New**: Create custom employee roles and assign permissions to each role
* **New**: Assign multiple roles to each employee
* **New**: Assign multiple employees to events
* **New**: Added Event Staff checkbox on user profile screen for administrators. Check to tell MDJM that the admin is an MDJM employee otherwise they have no MDJM permissions
* **New**: Admin availability checker can check for availability by role
* **New**: Added availability checker setting. Specify which roles need to be available on the given date for you to be available
* **New**: Content tags re-written and made into an API which developers can hook into when creating extensions or customising
* **New**: Emails class making it easier to send emails and for developers to hook into
* **New**: Settings API which developers can hook into
* **New**: Added contextual help to setings and events page
* **New**: Added Playlist Categories menu option to admin tool bar
* **New**: Added `mdjm_event_action_buttons` filter to re-order event action buttons
* **New**: Added Styles setting within Client Zone settings to set colour of Event Action Buttons
* **New**: Added mdjm_get_txns() to retrieve all transactions
* **New**: Enquiry sources are now post categories. Manage via the *Enquiry Sources* menu option
* **New**: Added MDJM_Stats class
* **Tweak**: Removed setting option for deposit and balance labels. Use Transaction Types instead
* **Tweak**: Delete an entire range of an employee's holiday rather than only a single day
* **Tweak**: Custom post meta box functions re-written to enable cleaner hooks for developers
* **Tweak**: Event meta boxes are now only loaded if current user has been assigned a role with relevant permissions
* **Tweak**: Shortcodes have been re-written and cleaned up
* **Tweak**: *MDJM Overview* dashboard widget has been updated and now displays event and earnings overviews for Month to Date, Year to Date and the previous year
* **Tweak**: Display names of employees who received a copy of a tracked email within the Communication history page
* **Tweak**: Main MDJM class is now a singleton class
* **Tweak**: Post actions and filters no longer in classes
* **Tweak**: Replaced get_link method with mdjm_get_formatted_url function
* **Tweak**: New mdjm.css file. Can be customised for front end
* **Tweak**: Added content filters for emailing and printing the event playlist
* **Tweak**: Cleaner files and directory structure
* **Tweak**: Don't restrict access to JetPack and don't hide WP menus for MDJM roles. Caused some conflicts with other plugins
* **Tweak**: Event posts are now ordered by event date by default
* **Tweak**: Event posts first column changed to date for easier viewing on mobile devices
* **Tweak**: Removed debug option to backup DB tables. Other plugins are available for this task
* **Tweak**: Removed debug option to submit debug files. No longer required
* **TODO**: Availability checker on events list screen is currently missing


= 1.2.7.5 =

**Released 22nd January, 2016**

* **New**: Attach files from computer to email composed via communication feature
* **New**: DJ / Admin access to the Client Zone is now blocked. Use the Admin area. For testing create a test client account and log in with that
* **General**: List multiple attachments on communication history
* **Bug Fix**: Custom event fields output if the field name contained spaces
* **Bug Fix**: Venue contact name missing a space if venue is set to client address

= 1.2.7.4 =

**Released 19th January, 2016**

* **Bug Fix**: Custom event fields did not display on the event screen if your deposit type was not set as percentage
* **Bug Fix**:  No MDJM data should be returned from a front end search
* **Bug Fix**:  Removed duplicate fields from client profile on admin profile page
* **Bug Fix**: Redirecting to contact page from availability widget should pre-populate event date field if present
* **Bug Fix**: Contract sign notification email to admin did not display client name. Filter content before passing to send_email method.

= 1.2.7.3 =

**Released 25th November, 2015**

	* **Bug Fix**: Missing number_format param was causing payment gateway API to not record merchant fees
	* **Tweak**: Accomodate changes in other MDJM plugins
	* **Tweak**: Update playlist task via update_option_{$option_name} when setting changes
	* **Tweak**: get_event_types now accepts args


= 1.2.7.2 =

**Released 25th November, 2015**

	* **Bug Fix**: Availability checker ajax scripts did not work if using a Firefox web browser
	* **Bug Fix**: Field wrap now functions as expected for Availability Checker
	* **Bug Fix**: PHP Notice written to log file if WP debugging enabled when saving event that has empty fields
	* **Bug Fix**: Unattended event availability check now calls correct function and does not generate error
	* **Bug Fix**: Backwards compatibility issue with front end availability checker
	* **Bug Fix**: Put availability checker fields on their own line if field wrap is true
	* **Bug Fix**: Redirect failed after client password change
	* **Bug Fix**: Image now displays on about page
	* **Tweak**: Ignore communication posts during custom post type save
	* **Tweak**: Removed custom text playlist setting for No Active Event
	* **Tweak**: Do not write to log file if no client fields are set as required
	* **Tweak**: Adjust folder structure within client zone
	* **New**: Added submit_wrap option for availability shortcode

= 1.2.7.1 =

**Released 22nd November, 2015**

	* **New**: Shortcodes added for Addons List and Availability checker
	* **New**: Add your own custom fields to Client, Event, and Venue Details metaboxes within the events screen
	* **New**: Text replacement shortcodes available for custom fields
	* **New**: Option to use AJAX for Availability Checker to avoid page refresh
	* **New**: New setting added Unavailable Statuses within Availability Settings so you now dictate which event status' should report as unavailable. By default we have set Enquiry, Awaiting Contract and Approved
	* **New**: Display name for DJ is now updated within user roles
	* **New**: Development hooks added to event post metaboxes
	* **Tweak**: Availability checker re-write
	* **Tweak**: MDJM Shortcodes button renamed to MDJM and new structure and options added
	* **Tweak**: Client fields settings page is now translation ready
	* **Tweak**: Updated the uninstallation procedure
	* **Tweak**: Added column ordering to transactions
	* **Tweak**: Added column ordering to quotes
	* **Tweak**: Replace Mobile DJ Manager with MDJM in WP dashboard widgets
	* **Tweak**: Change title to MDJM Event Management in MDJM dashboard
	* **Bug Fix**: User roles should only register during install
	* **Bug Fix**: WP Dashboard MDJM Overview now has correct edit URL
	* **Bug Fix**: Ordering by event value column in event list now accurate
	* **Bug Fix**: Adjusted the order in which the deposit and balance status' are updated for events so as to ensure manual payments are captured during manual event update
	* **Bug Fix**: Depending on PHP notice display settings, warning may be displayed on front end when client clicks Book this Event

= 1.2.7 =

**Released 22nd November, 2015**

	* **New**: Shortcodes added for Addons List and Availability checker
	* **New**: Add your own custom fields to Client, Event, and Venue Details metaboxes within the events screen
	* **New**: Text replacement shortcodes available for custom fields
	* **New**: Option to use AJAX for Availability Checker to avoid page refresh
	* **New**: New setting added Unavailable Statuses within Availability Settings so you now dictate which event status' should report as unavailable. By default we have set Enquiry, Awaiting Contract and Approved
	* **New**: Display name for DJ is now updated within user roles
	* **New**: Development hooks added to event post metaboxes
	* **Tweak**: Availability checker re-write
	* **Tweak**: MDJM Shortcodes button renamed to MDJM and new structure and options added
	* **Tweak**: Client fields settings page is now translation ready
	* **Tweak**: Updated the uninstallation procedure
	* **Tweak**: Added column ordering to transactions
	* **Tweak**: Added column ordering to quotes
	* **Tweak**: Replace Mobile DJ Manager with MDJM in WP dashboard widgets
	* **Tweak**: Change title to MDJM Event Management in MDJM dashboard
	* **Bug Fix**: User roles should only register during install
	* **Bug Fix**: WP Dashboard MDJM Overview now has correct edit URL
	* **Bug Fix**: Ordering by event value column in event list now accurate
	* **Bug Fix**: Adjusted the order in which the deposit and balance status' are updated for events so as to ensure manual payments are captured during manual event update
	* **Bug Fix**: Depending on PHP notice display settings, warning may be displayed on front end when client clicks Book this Event


= 1.2.6 =

**Released 31st October, 2015**

	* **New**: {PAYMENT_HISTORY} client shortcode added. Displays a simple list of client payments for the current event
	* **New**: Click the Details button on the event screen to reveal additional information
	* **Tweak**: Added Domain Path for translations 
	* **Tweak**: Removed deprecated journal DB table
	* **Tweak**: Preparation for MDJM to PDF extension
	* **Tweak**: Rebranded to MDJM Event Management on the plugin screen
	* **Tweak**: Rebranded to MDJM Events on the menu and admin bar
	* **Bug Fix**: Client Zone playlist now displays guest entries and which guest added
	* **Bug Fix**: Client Zone playlist now displays content from the info
	* **Bug Fix**: Removed blank line after Event End Date shortcode in list of shortcodes
	* **Bug Fix**: DB Backup time was always 00:00
	* **Bug Fix**: Client Zone was logging an error when booking was accepted
	* **Bug Fix**: Scheduled task was logging an error in the log file due to missing variable
	* **Bug Fix**: If no events exist, it was possible an error would be written to the log file relating to the Event Type filter
	* **Bug Fix**: Installation was trying to create a DB table that is no longer required and could possibly generate an on screen warning notification


= 1.2.5.3 =

**Released 25th October, 2015**

	* **New**: Added setting to enable event playlist (enabled by default)
    * **New**: Event playlists can now be controlled per event. If not enabled, the Manage Playlist action button is not displayed within the Client Zone
	* **New**: Option to select Client Address as event venue
	* **New**: On event screen added <code>Contact</code> link next to client list. Click to immediately contact regarding event
	* **New**: On event listing screen click the Client's or DJ's name to contact them regarding that event
	* **New**: Added `mdjm_event_metaboxes` developer action hook
	* **Bug Fix**: Depending on WP Debug settings, error may be displayed on client login screen (unlikely)
	* **Bug Fix**: In event listing error may be displayed if no events exist for the current status
	* **Bug Fix**: Unable to set Client's and DJ's as Active/Inactive

= 1.2.5.2 =

**Released 22nd October, 2015**

	* **New**: Added new currencies for AUS, CAD, NZD and SGD
	* **New**: Order your event listings by ID, Date, or Value by clicking on the relevant column header
	* **New**: Order your venue listings by Name, Town or County by clicking on the relevant column header
	* **New**: Support for MDJM Google Calendar Sync add-on
	* **New**: Added a few developer hooks and filters
	* **New**: Added new shortcode {END_DATE} which will display the date on which the event completes in short date format
	* **New**: Adjusted branding
	* **Bug Fix**: Fixed availability checker function on MDJM Dashboard
	* **Bug Fix**: {DJ_NOTES} shortcode was displaying event notes

= 1.2.5.1 =

**Released 10 October, 2015**

	* **Bug Fix**: Added 'stripslashes' to communication content and subject to ensure ' is not represented as \'s

= 1.2.5 =

**Released 09 October, 2015**

	* **Bug Fix**: DJ & Client admin pages were referring to a deprecated function which generated an error
	* **Bug Fix**: Hosted JS files are now loaded via HTTPS
	* **Bug Fix**: Enquiry email template saved correctly, but did not correctly display which enquiry was default
	* **New**: <code>Premium Addons</code> tab added to the Settings screen. If you have purchased Premium addons, enter your API key here

= 1.2.4.1 =

**Released Wednesday, 23rd September, 2015**

	* **New**: Section headings introduced for Contact Forms
	* **New**: Horizontal rules introduced for Contact Forms
	* **New**: Custom CSS introduced for Contact Forms
	* **New**: Event addons now available as checkbox list in dynamic contact forms
	* **New**: Contact form submission now also adds the deposit amount
	* **Bug Fix**: Packages, Equipment and categories now support special characters - quotes, double quotes etc.
	* **Bug Fix**: Online quote template was not changed on selection in event screen
	* **Bug Fix**: Contact form settings may not save as expected
	* **Bug Fix**: To address conflicts with the WP reserved names, added prefix to all contact form field slugs
	* **Bug Fix**: Client Zone re-directs did not work in some cases
	* **Bug Fix**: DJ list was not showing users that were assigned the DJ role
	* **Bug Fix**: DJ list may have displayed an on screen error if no active events were in the system
	* **Bug Fix**: Contact Form configuration settings were not always saving correctly
	* **Bug Fix**: Do not update a users profile upon Contact Form submssion if the user is logged in
	* **Bug Fix**: Venue fields now display by default if Unattended enquiry has venue details entered manually
	* **Tweak**: Dynamic addons list not longer displays on screen alert for "No addons available" and instead displays, "No addons available" within select list as a disabled option
	* **Tweak**: Added Extensions row to the application settings screen. More on this soon!
	* **Tweak**: Removed the colon (:) which was displayed after the "Other Label" on the PayPal form
	* **Tweak**: Added OOP code for MDJM extensions - coming soon
	* **Tweak**: /includes/config.inc.php is deprecated
	* **Tweak**: Lots of old code removed

= 1.2.4 =

**Released Saturday 12th September, 2015**

	* **New**: Introduction of PayFast as a payment gateway to accept online payments using the ZAR currency
	* **New**: Addition of new built-in Transaction Type Merchant Fees
	* **New**: When your client makes an online payment, if the Payment Gateway charges for the transaction, that charge is recorded within MDJM
	* **New**: Removed obvious deletion links from the required transaction types - Merchant Fees, Deposit, Balance, and Other Amount
	* **New**: Payment confirmation email to admin now includes the remaining balance owed for the booking plus additional information relating to the transaction
	* **New**: Using Transaction Post ID as the invoice number for online transactions (PayPal only)
	* **New**: Payment confirmation email to admin now includes the remaining balance for the booking
	* **New**: Transactions list now includes To/From column to identify the payer/payee
	* **New**: Events list now includes a Due column displaying the balance owed on the event
	* **New**: Notify Admin?<a href="<?php echo mdjm_get_admin_page( 'clientzone_settings' ); ?>">Setting</a> added and enabled by default. With selected, admin will receive email notification when a client accepts a quotation, or signs a contract via the <?php echo MDJM_APP; ?>
	* **Bug Fix**: PayPal API now correctly processes non deposit/balance payments
	* **Bug Fix**: Dynamic coding did not update values under certain circumstances

= 1.2.3.6 =

**Released Friday 4th September, 2015**

	* **Tweak**: Added custom payment amount option to the PayPal form
	* **Tweak**: PayPal form now uses radio buttons rather than select list
	* **Tweak**: Added setting to use standard HTML submit button with customised text for PayPal form
	* **Tweak**: Added ZAR currency for South Africa


= 1.2.3.5 =

**Released Friday 4th September, 2015**

	* **Bug Fix**: Completed event automated task sets event as completed incorrectly
	* **Bug Fix**: Adding event transaction hung when the "Paid From" field was populated with a value
	* **Bug Fix**: 12hr time format was not registering event time from the Dynamic Contact Form or Events page
	* **Bug Fix**: Some themes displayed comments in footer of client zone page. Addition of action hook to ensure none are displayed
	* **Bug Fix**: Redirect "may" not have worked when signing of contract was completed
	* **Tweak**: Remove page/post edit link from Client Zone pages for clients and DJ's
	* **Tweak**: Added Balance Due to event listing screen
	* **Tweak**: More translation preparation


= 1.2.3.4 =

**Released 31st August, 2015**

	* **Bug Fix**: Address an issue impacting availability within WordPress plugin repository
	* **Bug Fix**: Option to select Online Quote Template is now available even if Email Quote to Client is not selected on the event screen


= 1.2.3.3 =

**Released Monday 24th August, 2015**

	* **New**: Online quotes are now available in addition to email quotes. Clients can view quotes online and via a fully customisable button, accept the quote and book the event
	* **New**: Setting added to Client Zone tab enabling you to choose whether or not to display package & add-on prices within Client Zone
	* **Tweak**: Updated WP Admin header tags per 4.3 release
	* **Tweak**: Support for long field names in Contact Form for validation and Dynamic addon updates
	* **Bug Fix**: If event venue was entered manually, fields were not displayed on the screen until you changed the dropdown selection
	* **Bug Fix**: Saving playlist entries failed
	* **Bug Fix**: Removed updating of email address via dynamic contact form as potential problems with login
	* **Bug Fix**: Events not displaying on Clients page when filtered
	* **Bug Fix**: Default transaction type was not displaying all options
	* **Bug Fix**: Transaction source was not displaying all options on Events screen
	* **Bug Fix**: Is Default? column was not populated within Contract Template screen. May have generated on screen error
	* **Bug Fix**: Warning may have been displayed on Client Login screen and some admin screens dependant on PHP/WP settings


= 1.2.3.2 =

**Released Monday 24th August, 2015**

	* **New**: Settings added to the Payments tab to configure default event deposit based on fixed rate or % of event value
	* **New**: Define initially selected package within a dynamic contact form
	* **New**: Venue list has been added to contact forms
	* **Tweak**: Optimized Dynamic Contact Form front end coding. Slight enhancement to load time
	* **Tweak**: Refresh available packages & Add-ons when DJ selection changes on Event Management screen
	* **Tweak**: Updated WP Admin header tags per 4.3 release
	* **Tweak**: Updated jQuery version for validation. Now works with IE versions < 11
	* **Bug Fix**: Restored missing folder which was causing custom DB table backups to fail since version 1.2.3
	* **Bug Fix**: Contact form creation did not always correctly define default behaviours correctly
	* **Bug Fix**: Error displayed when deleting Contact Form field
	* **Bug Fix**: Error displayed upon Contact Form creation
	* **Bug Fix**: Depending on PHP/WP config an unwanted notice may have been displayed on client screen
	* **Bug Fix**: Only obtain event data when an event with the given ID exists. Unnecessary PHP notice logging
	* **Bug Fix**: Removed the random "r" character from the top of contact forms with layout set as table
	* **New**: Preliminary translation work


= 1.2.3.1 =

**Released Thursday 20th August, 2015**

	* **Bug Fix**: issue with validation of the date field when used with #MDJM Contact Forms


= 1.2.3 =

**Released Wednesday 19th August, 2015**

	* **Tweak**: Full support for WordPress 4.3
	* **New**: Updating the package for an Event in the Events Management screen, now dynamically updates the addons available for selection
	* **New**: Packages and Addons now displayed within Event Overview on the Client Zone screen. When a client hovers over the package or addon, the description and price is displayed
	* **New**: New settings added to the Plugin Removal settings screen so you can manipulate what data to/not to delete during deletion of plugin
	* **Bug Fix**: Resolved coding conflict which <em>may</em> have interferred with other plugins Ajax requests
	* **Bug Fix**: Empty equipment add-on categories no longer display
	* **Bug Fix**: Playlist upload to #MDJM error
	* **Bug Fix**: Changing Packages &amp; Addons for existing events now correctly re-calculates the event cost
	* **Bug Fix**: Playlist entries are now successfully submitted to the MDJM servers when songs and artists contain apostraphe's
	* **Tweak**: Enhanced the shortcode replacement procedure to make it cleaner and faster
	* **Tweak**: Log files are now auto-purged regardless of the admin page you are visiting. Previously only auto-purged whilst viewing Debug Settings
	* **Tweak**: Refreshed and cleaned up the uninstallation script

= 1.2.2 =

**Released Friday 3rd July, 2015**

	* **Bug Fix**: Addons available within Events screen when Available as Addon setting was not selected
	* **Bug Fix**: Debugging was stuck on/off depending on your setting prior to the 1.2.1 upgrade
	* **Bug Fix**: Unable to toggle the PayPal Enabled setting since upgrade to 1.2.1
	* **Bug Fix**: Cleared an error that may display if WP Debugging is enabled, whilst adding new equipment and/or package
	* **Bug Fix**: No more comment approval requests caused by journaling
	* **Tweak**: Slight adjustment to codebase for debugging as a tidy up

These issues appear to impact new installations more than existing due to the fact that the settings are set correctly, but not adjustable. However we recommend checking that both the Enable PayPal? and Enable Debugging? settings are set as expected.

= 1.2.1 =

**Released Sunday 28th June, 2015**

	* Refreshed the Settings Options layout
	* HTML5/CSS3 compatibility on all front end pages
	* Updated Client fields
	* **New**: Additional shortcodes for equipment & packages
	* **New**: IP address captured during client contract signing and displayed in contract view
	* **Tweak**: More improvements to debugging
	* **Bug Fix**: Addressed Email Tracking reliability
	* **Bug Fix**: {EVENT_TYPE} shortcode was returning ID rather than name
	* **Bug Fix**: Playlist submission to MDJM date error
	* **Bug Fix**: Broken event link when reviewing sent communication


= 1.2 =

**Released Tuesday 2nd June, 2015**
A complete revamp of how Events, Transactions, and Venues work plus much much more.
Join our Facebook group for all the latest discussions, news and more - https://www.facebook.com/groups/mobiledjmanager

	* **New**: Drag &amp; drop your Contact Form fields to re-order them easily
	* **New**: Edit field settings without having to delete and re-create
	* **New**: All transactions are now logged, whether automated via PayPal or manually entered by the Admin
	* **New**: Notifications to clients when payments are entered manually for events
	* **New**: Event Transaction overview is displayed on each event page
	* **New**: Transaction Types have been moved and no longer reside within settings
	* **Tweak**: Email tracking accuracy has been improved. If it says it has been opened, 
		you can be sure that the Client has received and opened the email
	* **Bug Fix**: Printing playlist no longer shows menu
	* **Bug Fix**: Email playlist corrections
	* **Tweak**: All outbound emails are sent from the defined system address. If your settings dictate that emails come from DJ's,
		the DJ's name will be displayed and the reply-to address will be set to that of the DJ too. This also addresses an issue whereby
		DJ's who have email addresses that do not end in the same domain name as the website where MDJM is installed, cannot send emails
		due to security controls
	* **Tweak**: Digital contract signing now requires the client to re-enter their password as an additional verification step
	* **Bug Fix**: Strange actions if the Availability widget was displayed at the same time as an Availability form within the main content
	* **Tweak**: Begun updating <?php echo MDJM_APP; ?> pages for HTML5 &amp; CSS3 compliance. Not yet completed
	* **New**: Create backups of the MDJM database tables and download within the debugging screen
	* **Tweak**: Significant improvements to the application debugging. No annoying notification when debugging
		is enabled, however we still only recommend to enable when you are experiencing an issue


= 1.1.3.3 =

**Released Thursday 14th May, 2015**

	* **Tweak**: You can now add custom content above MDJM shortcode content within Client Zone pages
        * **Tweak**: Support for WordPress 4.2.3 (currently in alpha)


= 1.1.3.2 =

**Released Friday 8th May, 2015**

	* **Tweak**: Full support for WordPress version 4.2.2
        * **Bug Fix**: Missing space within Client Zone playlist management page...&quot;Your playlist currently has ...entries&quot;
        * **Bug Fix**: CSS Correction within Availability widget
        * **Bug Fix**: Error when sending playlist via email


= 1.1.3.1 =

**Released Monday, 27th April 2015**

This is a bug-fix release only

	* **Bug Fix**: In certain circumstances, if you do not have events in the <code>Approved</code> status, no events were displayed in the events list
	* **Bug Fix**: Relating to the above, the status links did not work in the events list

Watch out for the next major release of MDJM...coming soon to include a re-designed Event interface, greater email tracking, faster response times and much more!

= 1.1.3 =

**Released Tuesday, 21st April 2015**

	* **New**: Officially supporting WordPress 4.2
	* **Tweak**: Removed Add New option from Automated Tasks - this feature is still in development

Watch out for the next major release of MDJM...coming soon to include a re-designed Event interface, greater email tracking, faster response times and much more!

= 1.1.2 =

**Released Tuesday, 17th March 2015**

	* **New**: All emails sent via the system to clients and DJ's are logged
	* **New**: Track your clients opening of emails
	* **New**: Re-designed the Venues feature and added additional functionality
	* **Bug Fix**: If your web theme utilises white text some playlist entries where not visible within the front end
	* **Tweak**: Cleaner Email and Contract Template tables
	* **Tweak**: Code improvements, efficiency and cleanliness


= 1.1.1 =

<u>Released Tuesday, 10th March 2015</u>

	* New Settings options added - Payment Types & Transaction Types. Used for adding Event transactions
	* **Bug Fix**: Manage Playlist link was missing on the client home page when viewing a single event
	* **Bug Fix**: Mapped field not removed from Contact Form list if already assigned to field
	* **Bug Fix**: DJ's only see their own events and clients within the Events page
	* **Bug Fix**: Emails sent via the Communication Feature without a template failed
	* **Tweak**: Client first and last names always have a capital letter when created via new event or contact form
	* **Tweak**: Events table defaults to sorted by event date
	* **Tweak**: Added colour picker when setting error text colour for Contact Forms
	* **Tweak**: Custom verification messages for Contract and Email Template updates
	* **Tweak**: Further improvements to the Debugging system
	* **Tweak**: Updated the uninstallation script


= 1.1 =

**New Features**
**PayPal Integration** for online Client Payments via your website<br />
MDJM Event Management for WordPress is now fully integrated with PayPal enabling you to take online payments securely via your website.

	* No PayPal account is needed by Clients in order to make payments
	* Accepts payments from all major credit cards, as well as funds within the Client's PayPal account
	* Clients can choose to pay the Booking Fee/Deposit, or the full event balance
	* Full PayPal integration means the MDJM application receives information from the PayPal IPN API system and updates the booking and journal automatically after verifying payment is completed
	* Automatically sends your client an email based on a template of your choosing when payment is verified
	* Ability to apply taxes
	* Multi-Currency support for GBP, EUR, & USD
	* Supports customised PayPal checkout pages
	* Customise the display of the payment form
	* Immediate notifications in the Admin interface when you have new "Unattended" enquiries
	* Supports the PayPal sandbox environment so full testing can take place without real payments

**Transaction Page**
A new Transactions page has been added to the MDJM system and is available via the MDJM Event Management menu's within the WordPress admin interface.

This page is only available to Admins and if the Payment features is enabled and has been introduced to compliment the new online payments system as described above.

For now, the page simply lists any transactions that have been processed via PayPal and any other data relevant to that transaction. We will continue to develop this feature in up and coming versions.

**Events Table**<br />
The Events page has been updated slightly to be a little more intuitive. Unattended enquiries are now listed as priority and with a red background.

The majority of updates to this page were to do with better, cleaner coding resulting in faster loading times and more efficient lookups.

**Bug Fixes &amp; Minor Enhancements**
	
		* **New**: Added <code>Make A Payment</code> link to the Client home page if PayPal is enabled for Client events that are due a deposit of balance payment
		* **New**: Enabled the **Add Media** button within the Communications page. You can now include images in your Client Communications
		* **New**: Added buttons in Playlist view to email the event playlist to yourself or print it
		* **New**: **Payments** tab added to the Settings page to support the new <a href="https://www.paypal.com/" target="_blank" title="PayPal">PayPal</a> online payments feaure
		* **New**: Added sub-menu items to the admin toolbar Settings item
		* **New**: **{CONTACT_URL}** <a href="http://www.mydjplanner.co.uk/shortcodes/" target="_blank">Shortcode</a> added
		* **New**: <a href="http://www.mydjplanner.co.uk/shortcodes/" target="_blank">Shortcodes</a> added to support the new online payments system. To be used within the verification email template
		
			* &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;**{PAYMENT_AMOUNT}**: Inserts the amount received by the payment
			* &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;**{PAYMENT_DATE}**: Inserts the date payment was received as determined by PayPal
			* &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;**{PAYMENT_FOR}**: Inserts **Deposit** or **Balance** depending on the payment received
		
		* **New**: Setting added **Deposit Label** enabling you to change the terminology used on both the front end and backend of your website. Some people prefer <code>Booking Fee</code> for example. Defaults to **Deposit**. Remember to update any email or contract templates as necessary
		 * **New**: Setting added **Balance Label** enabling you to change the terminology used on both the front end and backend of your website. Defaults to <code>Balance</code>. Remember to update any email or contract templates as necessary
		* **Bug Fix**: Slight adjustment to the Contact Forms validation scripts. In some instances determined during testing (no bug reports) the jQuery validation did not work correctly
		* **Bug Fix**: WordPress "reserves" some form field names such as **name** so if you used this field name within the MDJM Contact Forms, the form did not submit correctly. This is rectified
		* **Tweak**: Updated the uninstall script
		* **Tweak**: Added the <code>Date Added</code> column to the playlist table admin view. List is sorted by this column as default
		* **TODO**: Editing and ordering of Contact Form fields
	

= 1.0 =

**New Features**
Our fully customisable Contact Forms enable full management of events from the initial client enquiry all the way through to the completion of the event.


	* Create as many Contact Forms as you need and display them either on a single page in amongst your own contact, or via a widget that is displayed on multiple pages
	* Configure each individual form to meet your requirements
	* Map form fields directly to Client or Event fields
	* Create clients and event enquiries when the form is submitted
	* Immediately respond to the client once they submit the form with a pre-defined template
	* Customise each individual field as necessary
	* *
		
			* Include a date picker
			* Specify which fields are required to be completed before submission
			* Specify your own CSS class
			* Include placeholder hints
			* & more
		
	
	* Include text fields, free text areas, checkboxes, date fields, select (drop down) fields & more
	* jQuery Validation
	* Point successful Availability Checks to your MDJM Contact Form page for additional functionality


**Bug Fixes &amp; Minor Enhancements**

	* **New**: Create Clients directly from the Add New Event screen as part of the event creation process
	* **New**: MDJM Contact Form Widget enabling you to add your MDJM Contact Form to multiple web pages quickly and easily
	* **New**: Setting added **New Enquiry Notifications**. When checked, a notification will be displayed at the top of the WP Admin pages if there are new **Unattended Enquiries** that need attention. These notifications are only displayed to Administrators. The Setting is enabled by default
	* **New**: Once an event is Approved, you can now click on the status within the Event Listing page and view the Client's signed contract
	* **Bug Fix**: The Year drop down list within the Availability page was showing blank instead of 2015
	* **Bug Fix**: If you had your WordPress Permalink Settings set to the default of **Default** (also referred to as "Ugly") the Client Zone links did not work correctly for Clients when logged in
	* **Bug Fix**: In some instances the links within the Client Zone did not work correctly due to a conflict in configuration

== Upgrade Notice ==

= 1.3.7.2 =
Fixes issue with venue content tags not always returning the correct (or any) data. Upgrade ASAP.

= 1.3.7 =
Enhanced the manual event payments feature. See changelog for details.

= 1.3.4.1 =
This version addresses an issue that may result in a fatal error when selecting to save a venue during event updates. The `{contract_url}` content tag was generating incorrect output.

Update immediately.