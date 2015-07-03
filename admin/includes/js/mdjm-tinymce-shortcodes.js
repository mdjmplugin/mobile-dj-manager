(function() {
	tinymce.PluginManager.add('mdjm_shortcodes_btn', function( editor, url ) {
		editor.addButton( 'mdjm_shortcodes_btn', {
			text: 'MDJM Shortcodes',
			icon: false,
			type: 'menubutton',
			menu: [
				{
					text: 'Application Shortcodes',
					menu: [
						{
							text: 'Admin URL',
							onclick: function() {
								editor.insertContent('{ADMIN_URL}');
							}
						},
						{
							text: 'Application Home Page',
							onclick: function() {
								editor.insertContent('{APPLICATION_HOME}');
							}
						},
						{
							text: 'Application Contracts Page',
							onclick: function() {
								editor.insertContent('{CONTRACT_URL}');
							}
						},
						{
							text: 'Application Payments Page',
							onclick: function() {
								editor.insertContent('{PAYMENT_URL}');
							}
						},
						{
							text: 'Application Name',
							onclick: function() {
								editor.insertContent('{APPLICATION_NAME}');
							}
						},
						{
							text: 'Company Name',
							onclick: function() {
								editor.insertContent('{COMPANY_NAME}');
							}
						},
						{
							text: 'Contact Page',
							onclick: function() {
								editor.insertContent('{CONTACT_PAGE}');
							}
						},
						{
							text: 'Website Home Page',
							onclick: function() {
								editor.insertContent('{WEBSITE_URL}');
							}
						}
					]
				},
				{
					text: 'Client Shortcodes',
					menu: [
						{
							text: 'Client Firstname',
							onclick: function() {
								editor.insertContent('{CLIENT_FIRSTNAME}');
							}
						},
						{
							text: 'Client Lastname',
							onclick: function() {
								editor.insertContent('{CLIENT_LASTNAME}');
							}
						},
						{
							text: 'Client Fullname',
							onclick: function() {
								editor.insertContent('{CLIENT_FULLNAME}');
							}
						},
						{
							text: 'Client Username',
							onclick: function() {
								editor.insertContent('{CLIENT_USERNAME}');
							}
						},
						{
							text: 'Client Password',
							onclick: function() {
								editor.insertContent('{CLIENT_PASSWORD}');
							}
						},
						{
							text: 'Client Full Address',
							onclick: function() {
								editor.insertContent('{CLIENT_FULL_ADDRESS}');
							}
						},
						{
							text: 'Client Email',
							onclick: function() {
								editor.insertContent('{CLIENT_EMAIL}');
							}
						},
						{
							text: 'Client Primary Phone',
							onclick: function() {
								editor.insertContent('{CLIENT_PRIMARY_PHONE}');
							}
						}
					]
				},
				{
					text: 'DJ Shortcodes',
					menu: [
						{
							text: 'DJ Firstname',
							onclick: function() {
								editor.insertContent('{DJ_FIRSTNAME}');
							}
						},
						{
							text: 'DJ Fullname',
							onclick: function() {
								editor.insertContent('{DJ_FULLNAME}');
							}
						},
						{
							text: 'DJ Email',
							onclick: function() {
								editor.insertContent('{DJ_EMAIL}');
							}
						},
						{
							text: 'DJ Phone',
							onclick: function() {
								editor.insertContent('{DJ_PRIMARY_PHONE}');
							}
						}
					]
				},
				{
					text: 'Equipment Shortcodes',
					menu: [
						{
							text: 'Available Addons',
							onclick: function() {
								editor.insertContent('{AVAILABLE_ADDONS}');
							}
						},
						{
							text: 'Available Addons with Cost',
							onclick: function() {
								editor.insertContent('{AVAILABLE_ADDONS_COST}');
							}
						},
						{
							text: 'Available Packages',
							onclick: function() {
								editor.insertContent('{AVAILABLE_PACKAGES}');
							}
						},
						{
							text: 'Available Packages with Cost',
							onclick: function() {
								editor.insertContent('{AVAILABLE_PACKAGES_COST}');
							}
						},
						{
							text: 'Event Addons',
							onclick: function() {
								editor.insertContent('{EVENT_ADDONS}');
							}
						},
						{
							text: 'Event Addons with Cost',
							onclick: function() {
								editor.insertContent('{EVENT_ADDONS_COST}');
							}
						},
						{
							text: 'Event Package',
							onclick: function() {
								editor.insertContent('{EVENT_PACKAGE}');
							}
						},
						{
							text: 'Event Package with Cost',
							onclick: function() {
								editor.insertContent('{EVENT_PACKAGE_COST}');
							}
						},
					]
				},
				{
					text: 'Event Shortcodes',
					menu: [
						{
							text: 'Event Name',
							onclick: function() {
								editor.insertContent('{EVENT_NAME}');
							}
						},
						{
							text: 'Contract Date',
							onclick: function() {
								editor.insertContent('{CONTRACT_DATE}');
							}
						},
						{
							text: 'Contract ID',
							onclick: function() {
								editor.insertContent('{CONTRACT_ID}');
							}
						},
						{
							text: 'Event Date (Long)',
							onclick: function() {
								editor.insertContent('{EVENT_DATE}');
							}
						},
						{
							text: 'Event Date (DD/MM/YYYY)',
							onclick: function() {
								editor.insertContent('{EVENT_DATE_SHORT}');
							}
						},
						{
							text: 'Event Start Time',
							onclick: function() {
								editor.insertContent('{START_TIME}');
							}
						},
						{
							text: 'Event End Time',
							onclick: function() {
								editor.insertContent('{END_TIME}');
							}
						},
						{
							text: 'Event Setup Time',
							onclick: function() {
								editor.insertContent('{DJ_SETUP_TIME}');
							}
						},
						{
							text: 'Event Setup Date',
							onclick: function() {
								editor.insertContent('{DJ_SETUP_DATE}');
							}
						},
						{
							text: 'Event Type',
							onclick: function() {
								editor.insertContent('{EVENT_TYPE}');
							}
						},
						{
							text: 'Event Description',
							onclick: function() {
								editor.insertContent('{EVENT_DESCRIPTION}');
							}
						},
						{
							text: 'DJ Notes',
							onclick: function() {
								editor.insertContent('{DJ_NOTES}');
							}
						},
						{
							text: 'Admin Notes',
							onclick: function() {
								editor.insertContent('{ADMIN_NOTES}');
							}
						},
						{
							text: 'Playlist Closes',
							onclick: function() {
								editor.insertContent('{PLAYLIST_CLOSE}');
							}
						},
						{
							text: 'Playlist URL',
							onclick: function() {
								editor.insertContent('{PLAYLIST_URL}');
							}
						},
						{
							text: 'Total Cost',
							onclick: function() {
								editor.insertContent('{TOTAL_COST}');
							}
						},
						{
							text: 'Deposit Amount',
							onclick: function() {
								editor.insertContent('{DEPOSIT}');
							}
						},
						{
							text: 'Deposit Status',
							onclick: function() {
								editor.insertContent('{DEPOSIT_STATUS}');
							}
						},
						{
							text: 'Balance Owed',
							onclick: function() {
								editor.insertContent('{BALANCE}');
							}
						},
						{
							text: 'Venue',
							onclick: function() {
								editor.insertContent('{VENUE}');
							}
						},
						{
							text: 'Venue Contact',
							onclick: function() {
								editor.insertContent('{VENUE_CONTACT}');
							}
						},
						{
							text: 'Venue Full Address',
							onclick: function() {
								editor.insertContent('{VENUE_FULL_ADDRESS}');
							}
						},
						{
							text: 'Venue Phone',
							onclick: function() {
								editor.insertContent('{VENUE_TELEPHONE}');
							}
						},
						{
							text: 'Venue Email',
							onclick: function() {
								editor.insertContent('{VENUE_EMAIL}');
							}
						},
					]
				},
				{
					text: 'Online Payment Shortcodes',
					menu: [
						{
							text: 'Payment Amount',
							onclick: function() {
								editor.insertContent('{PAYMENT_AMOUNT}');
							}
						},
						{
							text: 'Payment Date',
							onclick: function() {
								editor.insertContent('{PAYMENT_DATE}');
							}
						},
						{
							text: 'Payment For',
							onclick: function() {
								editor.insertContent('{PAYMENT_FOR}');
							}
						},
					]
				},
				{
					text: 'General Shortcodes',
					menu: [
						{
							text: 'Date DD/MM/YYYY',
							onclick: function() {
								editor.insertContent('{DDMMYYYY}');
							}
						},
					]
				},
			]
		});
	});
})();