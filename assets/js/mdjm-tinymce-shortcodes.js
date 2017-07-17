(function () {
    tinymce.PluginManager.add('mdjm_shortcodes_btn', function (editor, url) {
        editor.addButton('mdjm_shortcodes_btn', {
            text: 'MDJM',
            icon: false,
            type: 'menubutton',
			tooltip: 'Insert MDJM shortcodes and content tags into your document',
            menu: [{
				text: 'Content Tags',
				menu:[{
					text: 'Application Content Tags',
					menu: [{
						text: 'Admin URL',
						onclick: function () {
							editor.insertContent('{admin_url}');
						}
					}, {
						text: 'Application Home Page',
						onclick: function () {
							editor.insertContent('{application_home}');
						}
					}, {
						text: 'Application Contracts Page',
						onclick: function () {
							editor.insertContent('{contract_url}');
						}
					}, {
						text: 'Application Payments Page',
						onclick: function () {
							editor.insertContent('{payment_url}');
						}
					}, {
						text: 'Application Quotes Page',
						onclick: function () {
							editor.insertContent('{quotes_url}');
						}
					}, {
						text: 'Application Name',
						onclick: function () {
							editor.insertContent('{application_name}');
						}
					}, {
						text: 'Company Name',
						onclick: function () {
							editor.insertContent('{company_name}');
						}
					}, {
						text: 'Contact Page',
						onclick: function () {
							editor.insertContent('{contact_page}');
						}
					}, {
						text: 'Website Home Page',
						onclick: function () {
							editor.insertContent('{website_url}');
						}
					}]
				}, {
					text: 'Client Content Tags',
					menu: [{
						text: 'Client Firstname',
						onclick: function () {
							editor.insertContent('{client_firstname}');
						}
					}, {
						text: 'Client Lastname',
						onclick: function () {
							editor.insertContent('{client_lastname}');
						}
					}, {
						text: 'Client Fullname',
						onclick: function () {
							editor.insertContent('{client_fullname}');
						}
					}, {
						text: 'Client Username',
						onclick: function () {
							editor.insertContent('{client_username}');
						}
					}, {
						text: 'Client Password',
						onclick: function () {
							editor.insertContent('{client_password}');
						}
					}, {
						text: 'Client Full Address',
						onclick: function () {
							editor.insertContent('{client_full_address}');
						}
					}, {
						text: 'Client Email',
						onclick: function () {
							editor.insertContent('{client_email}');
						}
					}, {
						text: 'Client Primary Phone',
						onclick: function () {
							editor.insertContent('{client_primary_phone}');
						}
					}, {
						text: 'Payment History',
						onclick: function () {
							editor.insertContent('{payment_history}');
						}
					}]
				}, {
					text: 'DJ Content Tags',
					menu: [{
						text: 'DJ Firstname',
						onclick: function () {
							editor.insertContent('{dj_firstname}');
						}
					}, {
						text: 'DJ Fullname',
						onclick: function () {
							editor.insertContent('{dj_fullname}');
						}
					}, {
						text: 'DJ Email',
						onclick: function () {
							editor.insertContent('{dj_email}');
						}
					}, {
						text: 'DJ Phone',
						onclick: function () {
							editor.insertContent('{dj_primary_phone}');
						}
					}]
				}, {
					text: 'Equipment Content Tags',
					menu: [{
						text: 'Available Addons',
						onclick: function () {
							editor.insertContent('{available_addons}');
						}
					}, {
						text: 'Available Addons with Cost',
						onclick: function () {
							editor.insertContent('{available_addons_cost}');
						}
					}, {
						text: 'Available Packages',
						onclick: function () {
							editor.insertContent('{available_packages}');
						}
					}, {
						text: 'Available Packages with Cost',
						onclick: function () {
							editor.insertContent('{available_packages_cost}');
						}
					}, {
						text: 'Event Addons',
						onclick: function () {
							editor.insertContent('{event_addons}');
						}
					}, {
						text: 'Event Addons with Cost',
						onclick: function () {
							editor.insertContent('{event_addons_cost}');
						}
					}, {
						text: 'Event Package',
						onclick: function () {
							editor.insertContent('{event_package}');
						}
					}, {
						text: 'Event Package with Cost',
						onclick: function () {
							editor.insertContent('{event_package_cost}');
						}
					}, {
						text: 'Event Package Description',
						onclick: function () {
							editor.insertContent('{event_package_description}');
						}
					}]
				}, {
					text: 'Event Content Tags',
					menu: [{
						text: 'Event Name',
						onclick: function () {
							editor.insertContent('{event_name}');
						}
					}, {
						text: 'Contract Date',
						onclick: function () {
							editor.insertContent('{contract_date}');
						}
					}, {
						text: 'Contract ID',
						onclick: function () {
							editor.insertContent('{contract_id}');
						}
					}, {
						text: 'Event Date (Long)',
						onclick: function () {
							editor.insertContent('{event_date}');
						}
					}, {
						text: 'Event Date (DD/MM/YYYY)',
						onclick: function () {
							editor.insertContent('{event_date_short}');
						}
					}, {
						text: 'Event Start Time',
						onclick: function () {
							editor.insertContent('{start_time}');
						}
					}, {
						text: 'Event End Date (DD/MM/YYYY)',
						onclick: function () {
							editor.insertContent('{end_date}');
						}
					}, 
					{
						text: 'Event End Time',
						onclick: function () {
							editor.insertContent('{end_time}');
						}
					}, {
						text: 'Event Setup Time',
						onclick: function () {
							editor.insertContent('{dj_setup_time}');
						}
					}, {
						text: 'Event Setup Date',
						onclick: function () {
							editor.insertContent('{dj_setup_date}');
						}
					}, {
						text: 'Event Type',
						onclick: function () {
							editor.insertContent('{event_type}');
						}
					}, {
						text: 'Event Description',
						onclick: function () {
							editor.insertContent('{event_description}');
						}
					}, {
						text: 'DJ Notes',
						onclick: function () {
							editor.insertContent('{dj_notes}');
						}
					}, {
						text: 'Admin Notes',
						onclick: function () {
							editor.insertContent('{admin_notes}');
						}
					}, {
						text: 'Playlist Closes',
						onclick: function () {
							editor.insertContent('{playlist_close}');
						}
					}, {
						text: 'Playlist URL',
						onclick: function () {
							editor.insertContent('{playlist_url}');
						}
					}, {
						text: 'Guest Playlist URL',
						onclick: function () {
							editor.insertContent('{guest_playlist_url}');
						}
					}, {
						text: 'Total Cost',
						onclick: function () {
							editor.insertContent('{total_cost}');
						}
					}, {
						text: 'Final Balance',
						onclick: function () {
							editor.insertContent('{final_balance}');
						}
					}, {
						text: 'Deposit Amount',
						onclick: function () {
							editor.insertContent('{deposit}');
						}
					}, {
						text: 'Deposit Status',
						onclick: function () {
							editor.insertContent('{deposit_status}');
						}
					}, {
						text: 'Balance Owed',
						onclick: function () {
							editor.insertContent('{balance}');
						}
					}, {
						text: 'Travel Cost',
						onclick: function () {
							editor.insertContent('{travel_cost}');
						}
					}, {
						text: 'Travel Directions',
						onclick: function () {
							editor.insertContent('{travel_directions}');
						}
					}, {
						text: 'Travel Distance',
						onclick: function () {
							editor.insertContent('{travel_distance}');
						}
					}, {
						text: 'Travel Time',
						onclick: function () {
							editor.insertContent('{travel_time}');
						}
					}, {
						text: 'Venue',
						onclick: function () {
							editor.insertContent('{venue}');
						}
					}, {
						text: 'Venue Contact',
						onclick: function () {
							editor.insertContent('{venue_contact}');
						}
					}, {
						text: 'Venue Full Address',
						onclick: function () {
							editor.insertContent('{venue_full_address}');
						}
					}, {
						text: 'Venue Phone',
						onclick: function () {
							editor.insertContent('{venue_telephone}');
						}
					}, {
						text: 'Venue Email',
						onclick: function () {
							editor.insertContent('{venue_email}');
						}
					}]
				}, {
					text: 'Online Payment Content Tags',
					menu: [{
						text: 'Payment Amount',
						onclick: function () {
							editor.insertContent('{payment_amount}');
						}
					}, {
						text: 'Payment Date',
						onclick: function () {
							editor.insertContent('{payment_date}');
						}
					}, {
						text: 'Payment For',
						onclick: function () {
							editor.insertContent('{payment_for}');
						}
					}]
				}, {
					text: 'General Content Tags',
					menu: [{
						text: 'Date DD/MM/YYYY',
						onclick: function () {
							editor.insertContent('{ddmmyyyy}');
						}
					}]
				}]
			}, {
				text: 'Content Shortcodes',
				menu: [{
					text: 'Addons List',
					onclick: function() {
						editor.insertContent('[mdjm-addons filter_by="false" filter_value="false" list="p" desc="false" cost="true" addon_class="false" desc_class="false" cost_class="false"]');
					}
				}, {
					text: 'Availability Checker',
					onclick: function() {
						editor.insertContent('[mdjm-availability label="Select Date" label_wrap="false" label_class="false" field_wrap="false" field_class="false" submit_text="Check Date" submit_wrap="true" submit_class="false" please_wait_text="Please Wait..." please_wait_class="false"]');
					}
				}]
			}, {
				text: 'Pages Shortcodes',
				menu: [{
					text: 'Client Zone Home',
					onclick: function () {
						editor.insertContent('[mdjm-home]');
					}
				}, {
					text: 'Client Contract',
					onclick: function () {
						editor.insertContent('[mdjm-contract]');
					}
				}, {
					text: 'Client Payments',
					onclick: function () {
						editor.insertContent('[mdjm-payments]');
					}
				}, {
					text: 'Client Playlist',
					onclick: function () {
						editor.insertContent('[mdjm-playlist]');
					}
				}, {
					text: 'Client Profile',
					onclick: function () {
						editor.insertContent('[mdjm-profile]');
					}
				}]
			}, {
				text: 'MDJM to PDF',
				menu: [{
					text: 'Download PDF',
					onclick: function() {
						editor.insertContent('[mdjm-pdf-download type="text" text="Download"]');
					}
				}, {
					text: 'Email PDF',
					onclick: function() {
						editor.insertContent('[mdjm-pdf-email type="text" text="Email PDF"]');
					}
				}, {
					text: 'Print PDF',
					onclick: function() {
						editor.insertContent('[mdjm-pdf-print type="text" text="Print"]');
					}
				}, {
					text: 'PDF Page Break',
					onclick: function() {
						editor.insertContent('{pdf_pagebreak}');
					}
				}]
			}]
        });
    });
})();