jQuery(document).ready(function ($) {

	// Setup Chosen menus
	$('.mdjm-select-chosen').chosen({
		inherit_select_classes: true
	});

	/**
	 * General Settings Screens JS
	 */
	var MDJM_Settings = {
		init : function()	{
			if ( 'admin_page_mdjm-custom-event-fields' == mdjm_admin_vars.current_page || 'admin_page_mdjm-custom-client-fields' == mdjm_admin_vars.current_page )	{
				this.custom_fields();
			}
		},
		
		custom_fields : function(event)	{
			// Sortable Client Fields
			jQuery(document).ready(function($) 	{
				$('.mdjm-client-list-item').sortable({
					handle: '.mdjm_draghandle',
					items: '.mdjm_sortable_row',
					opacity: 0.6,
					cursor: 'move',
					axis: 'y',
					update: function()	{
						var order = $(this).sortable('serialize', { expression: /(.+)=(.+)/ } ) + '&action=mdjm_update_client_field_order';
						$.post(ajaxurl, order, function(response)	{
							// Success
						});
					}
				});
			});

			// Sortable Custom Event Fields
			$('.mdjm-custom-client-list-item,.mdjm-custom-event-list-item,.mdjm-custom-venue-list-item').sortable({
				
				handle: '.mdjm_draghandle',
				items: '.mdjm_sortable_row',
				opacity: 0.6,
				cursor: 'move',
				axis: 'y',
				update: function(event)	{
					var order = $(this).sortable('serialize') + '&action=order_custom_event_fields';
					$.post(ajaxurl, order, function(response)	{
						// Success
					});
				}
			});
		}
	}
	MDJM_Settings.init();

	/**
	 * Events screen JS
	 */
	var MDJM_Events = {
		
		init : function()	{
			this.client();
			this.employee();
			this.equipment();
			this.time();
			this.type();
			this.txns();
			this.venue();
		},
		
		client : function()	{
			// Display client details
			$( document.body ).on( 'click', '#toggle_client_details', function() {
				$('#mdjm-event-client-details').toggle("slow");
			});
			
			// Update the client details when the client selection changes
			$( document.body ).on( 'change', '#client_name', function(event) {
				
				event.preventDefault();
				
				if ( '' == $('#client_name').val() )	{
					$('#mdjm-event-add-new-client-fields').hide("slow");
					return;
				} else if ( 'mdjm_add_client' == $('#client_name').val() )	{
					$('#mdjm-event-add-new-client-fields').show("slow");
				} else	{

					$('#mdjm-event-add-new-client-fields').hide("slow");

					var postData = {
						client_id  : $('#client_name').val(),
						event_id   : $('#post_ID').val(),
						action     : 'mdjm_refresh_client_details'
					};
	
					$.ajax({
						type       : 'POST',
						dataType   : 'json',
						data       : postData,
						url        : ajaxurl,
						beforeSend : function()	{
							$('#mdjm-event-client-details').replaceWith('<div id="mdjm-loading" class="mdjm-loader"><img src="' + mdjm_admin_vars.ajax_loader + '" /></div>');
						},
						success: function (response) {
							$('#mdjm-loading').replaceWith(response.client_details);
	
						}
					}).fail(function (data) {
						$('#mdjm-event-client-details').replaceWith('<div id="mdjm-loading" class="mdjm-loader"><img src="' + mdjm_admin_vars.ajax_loader + '" /></div>');
	
						if ( window.console && window.console.log ) {
							console.log( data );
						}
					});

				}

			});

			// Add a new client from the event screen
			$( document.body ).on( 'click', '#mdjm-add-client', function(event) {
				
				event.preventDefault();
				
				if ( $('#client_firstname').val().length < 1 )	{
					alert(mdjm_admin_vars.no_client_first_name);
					return;
				}
				if ( $('#client_email').val().length < 1 )	{
					alert(mdjm_admin_vars.no_client_email);
					return;
				}
				
				var postData = {
					client_firstname : $('#client_firstname').val(),
					client_lastname  : $('#client_lastname').val(),
					client_email     : $('#client_email').val(),
					client_phone     : $('#client_phone').val(),
					client_phone2    : $('#client_phone2').val(),
					action           : 'mdjm_event_add_client'
				};

				$.ajax({
					type       : 'POST',
					dataType   : 'json',
					data       : postData,
					url        : ajaxurl,
					beforeSend : function()	{
						$('#mdjm-add-client').hide();
						$('#mdjm-event-add-new-client-fields').replaceWith('<div id="mdjm-loading" class="mdjm-loader"><img src="' + mdjm_admin_vars.ajax_loader + '" /></div>');
					},
					success: function (response) {
						$('#client_name').empty();
						$('#client_name').append(response.client_list);
						$('#mdjm-add-client').show();
						$('#mdjm-loading').remove();
						$('#_mdjm_event_block_emails').prop('checked', false );
						$('#mdjm_reset_pw').prop('checked', true );
						$("#client_name").trigger("chosen:updated");

						if ( response.type == 'error' )	{
							alert(response.message);
						}

					}
				}).fail(function (data) {
					$('#mdjm-loading').remove();

					if ( window.console && window.console.log ) {
						console.log( data );
					}
				});

			});
			
			// Display custom client fields
			$( document.body ).on( 'click', '#toggle_custom_client_fields', function() {
				$('#mdjm_event_custom_client_fields').toggle("fast");
			});
			// Display custom event fields
			$( document.body ).on( 'click', '#toggle_custom_event_fields', function() {
				$('#mdjm_event_custom_event_fields').toggle("fast");
			});
			// Display custom venue fields
			$( document.body ).on( 'click', '#toggle_custom_venue_fields', function() {
				$('#mdjm_event_custom_venue_fields').toggle("fast");
			});
			
		},
		
		employee : function()	{
			// Display form to add event employee
			$( document.body ).on( 'click', '#toggle_add_employee_fields', function() {
				$('#mdjm_event_add_employee_table tbody').toggle("slow");
				if ( 'show form' == $('#toggle_add_employee_fields').text() )	{
					$('#toggle_add_employee_fields').text('hide form');
				} else	{
					$('#toggle_add_employee_fields').text('show form');
				}
			});

			// Add an employee to the event
			$( document.body ).on( 'click', '#add_event_employee', function(event) {
				
				event.preventDefault();
				
				var postData    = {
					event_id      : $('#post_ID').val(),
					employee_id   : $("#event_new_employee").val(),
					employee_role : $('#event_new_employee_role').val(),
					employee_wage : $('#event_new_employee_wage').val(),
					action        : 'add_employee_to_event'
				};
				
				$.ajax({
					type       : 'POST',
					dataType   : 'json',
					data       : postData,
					url        : ajaxurl,
					beforeSend : function()	{
						$('#mdjm-event-employee-list').replaceWith('<div id="mdjm-loading-employees" class="mdjm-loader"><img src="' + mdjm_admin_vars.ajax_loader + '" /></div>');
					},
					success: function (response) {
						if(response.type != 'success') {
							alert(response.msg);
						}
						$('#mdjm-loading-employees').replaceWith('<div id="mdjm-event-employee-list">' + response.employees + '</div>');

					}
				}).fail(function (data) {
					$('#mdjm-loading-employees').replaceWith('<div id="mdjm-event-employee-list">' + response.employees + '</div>');

					if ( window.console && window.console.log ) {
						console.log( data );
					}
				});
				
			});
			
			// Remove an employee from the event
			$( document.body ).on( 'click', '.remove_event_employee', function(event) {
				
				event.preventDefault();
				
				var postData    = {
					event_id    : $('#post_ID').val(),
					employee_id : $(this).data('employee_id'),
					action      : 'remove_employee_from_event'
				};
				
				$.ajax({
					type       : 'POST',
					dataType   : 'json',
					data       : postData,
					url        : ajaxurl,
					beforeSend : function()	{
						$('#mdjm-event-employee-list').replaceWith('<div id="mdjm-loading-employees" class="mdjm-loader"><img src="' + mdjm_admin_vars.ajax_loader + '" /></div>');
					},
					success: function (response) {
						if(response.type != 'success') {
							alert('Error');
						}
						$('#mdjm-loading-employees').replaceWith('<div id="mdjm-event-employee-list">' + response.employees + '</div>');

					}
				}).fail(function (data) {
					$('#mdjm-loading-employees').replaceWith('<div id="mdjm-event-employee-list">' + response.employees + '</div>');

					if ( window.console && window.console.log ) {
						console.log( data );
					}
				});
				
			});
		},
		
		equipment : function()	{

			$( document.body ).on( 'change', '#_mdjm_event_package,#event_addons', function(event) {
				setCost();
			});
			
			$( document.body ).on( 'focusout', '#_mdjm_event_cost', function(event) {
				if( mdjm_admin_vars.deposit_is_pct )	{
					setDeposit();
				}
			});

			// Set the deposit value for the event
			var setDeposit = function()	{
				var current_deposit = $('#_mdjm_event_deposit').val();
				var postData        = {
					current_cost : $('#_mdjm_event_cost').val(),
					action       : 'update_event_deposit'
				};
				
				$.ajax({
					type       : 'POST',
					dataType   : 'json',
					data       : postData,
					url        : ajaxurl,
					beforeSend : function()	{
						$('#_mdjm_event_deposit').fadeTo('fast', 0.5);
						$('#_mdjm_event_deposit').addClass('mdjm-updating');
					},
					success: function (response) {
						if(response.type == 'success') {
							$('#_mdjm_event_deposit').val(response.deposit);
						} else	{
							alert(response.msg);
							$('#_mdjm_event_deposit').val(current_deposit);
						}
						$('#_mdjm_event_deposit').fadeTo('fast', 1);
						$('#_mdjm_event_deposit').removeClass('mdjm-updating');						
					}
				}).fail(function (data) {
					if ( window.console && window.console.log ) {
						console.log( data );
					}
					$('#_mdjm_event_deposit').val(current_deposit);
				});
			};
			
			// Set the event cost.
			var setCost = function()	{

				var current_cost = $('#_mdjm_event_cost').val();
				var postData     = {
					addons       : $('#event_addons').val() || [],
					package      : $('#_mdjm_event_package option:selected').val(),
					event_id     : $('#post_ID').val(),
					current_cost : $('#_mdjm_event_cost').val(),
					action       : 'update_event_cost_from_addons'
				};

				$.ajax({
					type       : 'POST',
					dataType   : 'json',
					data       : postData,
					url        : ajaxurl,
					beforeSend : function()	{
						$('#_mdjm_event_cost').fadeTo('fast', 0.5);
						$('#_mdjm_event_cost').addClass( 'mdjm-updating' );
					},
					success: function (response) {
						if(response.type == "success") {
							$('#_mdjm_event_cost').val(response.cost);

							if( mdjm_admin_vars.update_deposit )	{
								setDeposit();
							}

						} else	{
							alert(response.msg);
							$('#_mdjm_event_cost').val(current_cost);
						}

						$('#_mdjm_event_cost').fadeTo('fast', 1);
						$('#_mdjm_event_cost').removeClass('mdjm-updating');
					}
				}).fail(function (data) {
					if ( window.console && window.console.log ) {
						console.log( data );
					}
				});

			};

			// Update package and add-on options when the primary employee is updated.
			$( document.body ).on( 'change', '#_mdjm_event_dj', function(event) {
				
				event.preventDefault();
				var current_deposit = $('#_mdjm_event_deposit').val();
				var postData        = {
					package  : $("#_mdjm_event_package option:selected").val(),
					addons   : $("#event_addons").val() || [],
					employee : $("#_mdjm_event_dj").val(),
					action   : 'refresh_employee_package_options'
				};

				$.ajax({
					type       : 'POST',
					dataType   : 'json',
					data       : postData,
					url        : ajaxurl,
					beforeSend : function()	{
						$('#_mdjm_event_package').addClass( 'mdjm-updating' );
						$('#_mdjm_event_package').fadeTo('slow', 0.5);
						$('#event_addons').addClass('mdjm-updating');
						$('#event_addons').fadeTo('slow', 0.5);
					},
					success: function (response) {
						if(response.type == "success") {
							$('#_mdjm_event_package').empty(); // Remove existing package options
							$('#_mdjm_event_package').append(response.packages);
							
							$('#event_addons').empty(); // Remove existing addon options
							$('#event_addons').append(response.addons);
							setCost();
						} else	{
							alert(response.msg);
						}						

						$('#_mdjm_event_package').fadeTo('slow', 1);
						$('#_mdjm_event_package').removeClass('mdjm-updating');
						$('#event_addons').fadeTo('slow', 1);
						$('#event_addons').removeClass('mdjm-updating');

					}
				}).fail(function (data) {
					if ( window.console && window.console.log ) {
						console.log( data );
					}
					$('#_mdjm_event_deposit').val(current_deposit);
				});

			});

			// Refresh the add-ons when the package is updated
			$( document.body ).on( 'change', '#_mdjm_event_package', function(event) {
				
				event.preventDefault();

				var postData        = {
					package  : $("#_mdjm_event_package option:selected").val(),
					dj       : $("#_mdjm_event_dj").val(),
					action   : 'refresh_addon_options'
				};

				$.ajax({
					type       : 'POST',
					dataType   : 'json',
					data       : postData,
					url        : ajaxurl,
					beforeSend : function()	{
						$('#event_addons').addClass('mdjm-updating');
						$('#event_addons').fadeTo('slow', 0.5);
					},
					success: function (response) {
						if(response.type == "success") {
							$('#event_addons').empty();
							$('#event_addons').append(response.addons);
							$("#event_addons").fadeTo('slow', 1);
						} else	{
							alert(response.msg);
						}	
						
						$('#event_addons').fadeTo('slow', 1);
						$('#event_addons').removeClass('mdjm-updating', 1);

					}
				}).fail(function (data) {
					if ( window.console && window.console.log ) {
						console.log( data );
					}
				});

			});

		},
		
		time : function()	{
			// Set the DJ Setup Date
			$( document.body ).on( 'change', '#display_event_date', function() {
				if( $('#dj_setup_date').val().length < 1 )	{
					$('#dj_setup_date').val($('#display_event_date').val());
				}
			});
		},
		
		type : function()	{
			// Reveal the input fields to add a new event type
			$( document.body ).on( 'click', '#event-type-add', function(event) {
				$('#mdjm-new-event-type-row').toggle("fast");
			});
			
			// Save a new event type
			$( document.body ).on( 'click', '#add_event_type', function(event) {
				
				event.preventDefault();
				
				var postData = {
					type    : $('#event_type_name').val(),
					current : $('#mdjm_event_type').val(),
					action  : 'add_event_type'
				};
				
				$.ajax({
					type       : 'POST',
					dataType   : 'json',
					data       : postData,
					url        : ajaxurl,
					beforeSend : function()	{
						$('#add_event_type').hide();
						$('#mdjm-event-type-loader').show();
					},
					success: function (response) {
						if(response.type == 'success') {
							$('#event_type_name').val('');
							$('#mdjm-new-event-type-row').toggle("fast");
							$('#mdjm_event_type').show();
							$('#mdjm_event_type').replaceWith(response.event_types);
						} else	{
							alert(response.msg)
						}

						$('#mdjm-event-type-loader').hide();
						$('#add_event_type').show();

					}
				}).fail(function (data) {
					$('#mdjm_event_type').show();
					$('#mdjm-new-event-type-row').toggle("fast");
					$('#mdjm-event-type-loader').hide();
					$('#add_event_type').show();

					if ( window.console && window.console.log ) {
						console.log( data );
					}
				});
		
			});

			// Show/Hide the email templates when the disable emails checkbox is toggles
			$( document.body ).on( 'click', '#mdjm_block_emails', function(event) {
				$('#mdjm-event-email-templates').toggle("fast");
			});

		},
		
		txns : function()	{

			// Show/Hide transaction table
			$( document.body ).on( 'click', '#mdjm_txn_toggle', function(event) {
				$('#mdjm_event_txn_table').toggle("slow");
			});

			// Show/Hide transaction table
			$( document.body ).on( 'click', '#toggle_add_txn_fields', function(event) {
				$('#mdjm_event_add_txn_table tbody').toggle("slow");
				$('#save-event-txn').toggle("fast");
				if ( 'show form' == $('#toggle_add_txn_fields').text() )	{
					$('#toggle_add_txn_fields').text('hide form');
				} else	{
					$('#toggle_add_txn_fields').text('show form');
				}
			});
			
			// Transaction direction
			$( document.body ).on( 'change', '#mdjm_txn_direction', function(event) {
				if ( 'In' == $('#mdjm_txn_direction').val() )	{
					$('#mdjm_txn_from_container').removeClass('mdjm-hidden');
					$('#mdjm_txn_to_container').addClass('mdjm-hidden');
					$('#mdjm-txn-email').removeClass('mdjm-hidden');
				}
				if ( 'Out' == $('#mdjm_txn_direction').val() || '' == $('#mdjm_txn_direction').val() )	{
					$('#mdjm_txn_to_container').removeClass('mdjm-hidden');
					$('#mdjm_txn_from_container').addClass('mdjm-hidden');
					$('#mdjm-txn-email').addClass('mdjm-hidden');
				}
			});

			// Save an event transation
			$( document.body ).on( 'click', '#save_transaction', function(event) {
				
				event.preventDefault();

				if ( $('#mdjm_txn_amount').val().length < 1 )	{
					alert( mdjm_admin_vars.no_txn_amount );
					return false;
				}
				if ( $('#mdjm_txn_date').val().length < 1 )	{
					alert( mdjm_admin_vars.no_txn_date );
					return false;
				}
				if ( $('#mdjm_txn_for').val().length < 1 )	{
					alert( mdjm_admin_vars.no_txn_for );
					return false;
				}
				if ( $('#mdjm_txn_src').val().length < 1 )	{
					alert( mdjm_admin_vars.no_txn_src );
					return false;
				}
				
				var postData         = {
					event_id        : $('#post_ID').val(),
					client          : $('#client_name').val(),
					amount          : $('#mdjm_txn_amount').val(),
					date            : $('#mdjm_txn_date').val(),
					direction       : $('#mdjm_txn_direction').val(),
					from            : $('#mdjm_txn_from').val(),
					to              : $('#mdjm_txn_to').val(),
					for             : $('#mdjm_txn_for').val(),
					src             : $('#mdjm_txn_src').val(),
					send_notice     : ( $('#mdjm_manual_txn_email').is(':checked') ) ? 1 : 0,
					action          : 'add_event_transaction'
				};
				
				$.ajax({
					type       : 'POST',
					dataType   : 'json',
					data       : postData,
					url        : ajaxurl,
					beforeSend : function()	{
						$('#mdjm_event_txn_table').replaceWith('<div id="mdjm-loading" class="mdjm-loader"><img src="' + mdjm_admin_vars.ajax_loader + '" /></div>');
					},
					success: function (response) {
						if(response.type == "success") {
							if(response.deposit_paid == "Y")	{
								$('#deposit_paid').prop('checked', true );	
							}
							if(response.balance_paid == 'Y')	{
								$('#balance_paid').prop('checked', true );	
							}
						} else	{
							alert(response.msg)
						}
						$('#mdjm-loading').replaceWith('<div id="mdjm_event_txn_table">' + response.transactions + '</div>')
					}
				}).fail(function (data) {
					$('#mdjm-loading').replaceWith('<div id="mdjm_event_txn_table">' + response.transactions + '</div>')
					if ( window.console && window.console.log ) {
						console.log( data );
					}
				});
			});
			
		},
		
		venue : function()	{
			// Show manual venue details if pre-selected on event load
			if ( mdjm_admin_vars.current_page == 'post.php' )	{
				if ( 'manual' == $('#venue_id').val() )	{
					$('#mdjm-event-add-new-venue-fields').show();
					$('#mdjm-save-venue-button-row').removeClass('mdjm-hidden');
					$('#toggle_venue_details').addClass('mdjm-hidden');
				} else	{
					if ( '0' != $('#venue_id').val() && '' != $('#venue_id').val() && 'client' != $('#venue_id').val() )	{
						$('#mdjm-save-venue-button-row').addClass('mdjm-hidden');
						$('#toggle_venue_details').removeClass('mdjm-hidden');
					}
				}

				
			}
			// Display Venue Details
			$( document.body ).on( 'click', '#toggle_venue_details', function() {
				$('#mdjm-event-venue-details').toggle("slow");
			});
			
			// Update the venue details when the venue selection changes
			$( document.body ).on( 'change', '#venue_id', function(event) {
				
				event.preventDefault();
				
				if ( 'manual' == $('#venue_id').val() )	{
					$('#mdjm-event-venue-details').hide("slow");
					$('#mdjm-event-add-new-venue-fields').show("slow");
					$('#mdjm-save-venue').removeClass('mdjm-hidden');
					$('#toggle_venue_details').addClass('mdjm-hidden');
				} else	{
					$('#mdjm-save-venue').addClass('mdjm-hidden');
					$('#mdjm-event-add-new-venue-fields').hide("slow");
					
					if(  '0' != $('#venue_id').val() && 'client' != $('#venue_id').val() )	{
						$('#toggle_venue_details').removeClass('mdjm-hidden');
					} else	{
						$('#toggle_venue_details').addClass('mdjm-hidden');
					}

					var postData = {
						venue_id   : $('#venue_id').val(),
						event_id   : $('#post_ID').val(),
						action     : 'mdjm_refresh_venue_details'
					};

					$.ajax({
						type       : 'POST',
						dataType   : 'json',
						data       : postData,
						url        : ajaxurl,
						beforeSend : function()	{
							if ( $('#mdjm-event-venue-details').hasClass("mdjm-hidden") )	{
								$.needClass = false;
							}
							$('#mdjm-event-venue-details').replaceWith('<div id="mdjm-loading" class="mdjm-loader"><img src="' + mdjm_admin_vars.ajax_loader + '" /></div>');
						},
						success: function (response) {
							$('#mdjm-loading').replaceWith(response.venue_details);
						}
					}).fail(function (data) {
						$('#mdjm-event-venue-details').replaceWith('<div id="mdjm-loading" class="mdjm-loader"><img src="' + mdjm_admin_vars.ajax_loader + '" /></div>');
	
						if ( window.console && window.console.log ) {
							console.log( data );
						}
					});

				}

			});

			// Add a new venue from the event screen
			$( document.body ).on( 'click', '#mdjm-save-venue', function(event) {
				
				event.preventDefault();
				
				if ( $('#venue_name').val().length < 1 )	{
					alert(mdjm_admin_vars.no_venue_name);
					return;
				}
				
				var postData = {
					venue_name        : $('#venue_name').val(),
					venue_contact     : $('#venue_contact').val(),
					venue_email       : $('#venue_email').val(),
					venue_address1    : $('#venue_address1').val(),
					venue_address2    : $('#venue_address2').val(),
					venue_town        : $('#venue_town').val(),
					venue_county      : $('#venue_county').val(),
					venue_postcode    : $('#venue_postcode').val(),
					venue_phone       : $('#venue_phone').val(),
					action            : 'mdjm_add_venue'
				};

				$.ajax({
					type       : 'POST',
					dataType   : 'json',
					data       : postData,
					url        : ajaxurl,
					beforeSend : function()	{
						$('#mdjm-add-venue').hide();
						$('#mdjm-event-add-new-venue-fields').replaceWith('<div id="mdjm-loading" class="mdjm-loader"><img src="' + mdjm_admin_vars.ajax_loader + '" /></div>');
					},
					success: function (response) {
						$('#venue_id').empty();
						$('#venue_id').append(response.venue_list);
						$('#mdjm-add-venue').show();
						$('#mdjm-loading').remove();
						$("#venue_id").trigger("chosen:updated");

						if ( response.type == 'error' )	{
							alert(response.message);
						}

					}
				}).fail(function (data) {
					$('#mdjm-loading').remove();

					if ( window.console && window.console.log ) {
						console.log( data );
					}
				});

			});
			
		}
		
	}
	MDJM_Events.init();

	/**
	 * Packages & Addons screen JS
	 */
	var MDJM_Equipment = {

		init : function()	{
			this.add();
			this.remove();
			this.price();
		},

		clone_repeatable : function(row) {

			// Retrieve the highest current key
			var key = highest = 1;
			row.parent().find( 'tr.mdjm_repeatable_row' ).each(function() {
				var current = $(this).data( 'key' );
				if( parseInt( current ) > highest ) {
					highest = current;
				}
			});
			key = highest += 1;

			clone = row.clone();

			/** manually update any select box values */
			clone.find( 'select' ).each(function() {
				$( this ).val( row.find( 'select[name="' + $( this ).attr( 'name' ) + '"]' ).val() );
			});

			clone.removeClass( 'mdjm_add_blank' );

			clone.attr( 'data-key', key );
			clone.find( 'td input, td select, textarea' ).val( '' );
			clone.find( 'input, select, textarea' ).each(function() {
				var name = $( this ).attr( 'name' );
				var id   = $( this ).attr( 'id' );

				if( name ) {

					name = name.replace( /\[(\d+)\]/, '[' + parseInt( key ) + ']');
					$( this ).attr( 'name', name );

				}

				if( typeof id != 'undefined' ) {

					id = id.replace( /(\d+)/, parseInt( key ) );
					$( this ).attr( 'id', id );

				}

			});

			clone.find( 'span.mdjm_price_id' ).each(function() {
				$( this ).text( parseInt( key ) );
			});

			clone.find( 'span.mdjm_file_id' ).each(function() {
				$( this ).text( parseInt( key ) );
			});

			clone.find( '.mdjm_repeatable_default_input' ).each( function() {
				$( this ).val( parseInt( key ) ).removeAttr('checked');
			})

			// Remove Chosen elements
			clone.find( '.search-choice' ).remove();
			clone.find( '.chosen-container' ).remove();

			return clone;
		},

		add : function() {
			$( document.body ).on( 'click', '.submit .mdjm_add_repeatable', function(e) {
				e.preventDefault();
				var button = $( this ),
				row = button.parent().parent().prev( 'tr' ),
				clone = MDJM_Equipment.clone_repeatable(row);

				clone.insertAfter( row ).find('input, textarea, select').filter(':visible').eq(0).focus();

				// Setup chosen fields again if they exist
				clone.find('.mdjm-select-chosen').chosen({
					inherit_select_classes: true,
					placeholder_text_multiple: mdjm_admin_vars.select_months
				});
				clone.find( '.package-items' ).css( 'width', '100%' );
			});
		},

		move : function() {

			$(".mdjm_repeatable_table tbody").sortable({
				handle: '.mdjm_draghandle', items: '.mdjm_repeatable_row', opacity: 0.6, cursor: 'move', axis: 'y', update: function() {
					var count  = 0;
					$(this).find( 'tr' ).each(function() {
						$(this).find( 'input.mdjm_repeatable_index' ).each(function() {
							$( this ).val( count );
						});
						count++;
					});
				}
			});

		},

		remove : function() {
			$( document.body ).on( 'click', '.mdjm_remove_repeatable', function(e) {
				e.preventDefault();

				var row   = $(this).parent().parent( 'tr' ),
					count = row.parent().find( 'tr' ).length - 1,
					type  = $(this).data('type'),
					repeatable = 'tr.mdjm_repeatable_' + type + 's';

				if ( type === 'price' ) {
					var price_row_id = row.data('key');
					/** remove from price condition */
					$( '.mdjm_repeatable_condition_field option[value="' + price_row_id + '"]' ).remove();
				}

				if( count > 1 ) {
					$( 'input, select', row ).val( '' );
					row.fadeOut( 'fast' ).remove();
				} else {
					switch( type ) {
						case 'price':
							alert( mdjm_admin_vars.one_month_min );
							break;
						case 'item':
							alert( mdjm_admin_vars.one_item_min );
							break;
						default:
							alert( mdjm_admin_vars.one_month_min );
							break;
					}
				}

				/* re-index after deleting */
				$(repeatable).each( function( rowIndex ) {
					$(this).find( 'input, select' ).each(function() {
						var name = $( this ).attr( 'name' );
						name = name.replace( /\[(\d+)\]/, '[' + rowIndex+ ']');
						$( this ).attr( 'name', name ).attr( 'id', name );
					});
				});
			});
		},

		price : function()	{
			$( document.body ).on( 'click', '#_package_restrict_date', function() {
				$('#mdjm-package-month-selection').toggle("fast");
			});

			$( document.body ).on( 'click', '#_addon_restrict_date', function() {
				$('#mdjm-addon-month-selection').toggle("fast");
			});

			$( document.body ).on( 'click', '#_package_variable_pricing', function()	{
				$('#mdjm-package-variable-price-fields').toggle("fast");
			});

			$( document.body ).on( 'click', '#_addon_variable_pricing', function()	{
				$('#mdjm-addon-variable-price-fields').toggle("fast");
			});

		}
	}
	MDJM_Equipment.init();

	/**
	 * Communications screen JS
	 */
	var MDJM_Comms = {

		init : function()	{
			this.content();
		},

		content: function()	{

			// Refresh the events list for the current recipient
			var loadEvents = function(recipient)	{
				var postData         = {
					recipient : recipient,
					action    : 'mdjm_user_events_dropdown'
				};
				
				$.ajax({
					type       : 'POST',
					dataType   : 'json',
					data       : postData,
					url        : ajaxurl,
					beforeSend : function()	{
						$('#mdjm_email_event').addClass('mdjm-updating');
						$('#mdjm_email_event').fadeTo('slow', 0.5);
					},
					success: function (response) {
						$('#mdjm_email_event').empty();
						$('#mdjm_email_event').append(response.event_list);
						$('#mdjm_email_event').fadeTo('slow', 1);
						$('#mdjm_email_event').removeClass('mdjm-updating');
					}
				}).fail(function (data) {
					if ( window.console && window.console.log ) {
						console.log( data );
					}
				});

			};
			
			// Set initial event list when page loads
			if( mdjm_admin_vars.load_recipient )	{
				$('#mdjm_email_to').val(mdjm_admin_vars.load_recipient);
				loadEvents(mdjm_admin_vars.load_recipient);
			}

			// Update event list when recipient changes
			$( document.body ).on( 'change', '#mdjm_email_to', function(event) {

				event.preventDefault();

				var recipient = $("#mdjm_email_to").val();
				loadEvents(recipient);

			});

			// Update event list when recipient changes
			$( document.body ).on( 'change', '#mdjm_email_template', function(event) {

				event.preventDefault();

				var postData         = {
					template : $('#mdjm_email_template').val(),
					action   : 'mdjm_set_email_content'
				};

				$.ajax({
					type       : 'POST',
					dataType   : 'json',
					data       : postData,
					url        : ajaxurl,
					beforeSend : function()	{
						$('#mdjm_email_subject').addClass('mdjm-updating');
						$('#mdjm_email_subject').fadeTo('slow', 0.5);
						$('#mdjm_email_content').addClass('mdjm-updating');
						$('#mdjm_email_content').fadeTo('slow', 0.5);
						$('#mdjm_email_template').addClass('mdjm-updating');
						$('#mdjm_email_template').fadeTo('slow', 0.5);
						tinymce.execCommand('mceToggleEditor',false,'mdjm_email_content');
					},
					success: function (response) {
						if(response.type == 'success') {
							$('#mdjm_email_content').empty();
							tinyMCE.activeEditor.setContent('');
							$('#mdjm_email_subject').val(response.updated_subject);
							tinyMCE.activeEditor.setContent(response.updated_content);
							$('#mdjm_email_content').val(response.updated_content);
						} else	{
							alert(response.msg);
						}
						$('#mdjm_email_subject').fadeTo('slow', 1);
						$('#mdjm_email_subject').removeClass('mdjm-updating');
						$('#mdjm_email_content').fadeTo('slow', 1);
						$('#mdjm_email_content').removeClass('mdjm-updating');
						$('#mdjm_email_template').removeClass('mdjm-updating');
						$('#mdjm_email_template').fadeTo('slow', 1);
						tinymce.execCommand('mceToggleEditor',false,'mdjm_email_content');
					}
				}).fail(function (data) {
					if ( window.console && window.console.log ) {
						console.log( data );
					}
				});

			});

		}

	}
	MDJM_Comms.init();

/*
 * Validation Rules
 ******************************************/
	// Comms page
	if ( 'mdjm-event_page_mdjm-comms' == mdjm_admin_vars.current_page )	{
		$('#mdjm_form_send_comms').validate({
			errorClass: "mdjm-form-error",
			validClass: "mdjm-form-valid",
			focusInvalid: false,
			
			rules:	{
			},
			
			messages:	{
				mdjm_email_to      : null,
				mdjm_email_subject : null,
				mdjm_email_content : null
			}
		});
	}
	
	// Events page
	if ( mdjm_admin_vars.editing_event )	{
		$('#post').validate({
			errorClass: "mdjm-form-error",
			validClass: "mdjm-form-valid",
			focusInvalid: false,
			
			rules:	{
				client_name : { required: true, minlength : 1 },
				display_event_date : { required: true },
				_mdjm_event_cost : { number: true },
				_mdjm_event_deposit : { number: true }
			},
			
			messages:	{
				client_name      : null,
				display_event_date : null,
				_mdjm_event_cost : null,
				_mdjm_event_deposit : null
			}
		});
	}
	
});