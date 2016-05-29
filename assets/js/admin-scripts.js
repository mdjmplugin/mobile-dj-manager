jQuery(document).ready(function ($) {

	/**
	 * Events screen JS
	 */
	var MDJM_Events = {
		
		init : function()	{
			this.client();
			this.employee();
			this.equipment();
			this.type();
			this.txns();
		},
		
		client : function()	{
			// Display client details
			$( document.body ).on( 'click', '#client_details_show', function() {
				$('#client_details').toggle();
			});
			
			
		},
		
		employee : function()	{
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
						$('#event_employee_list').replaceWith('<div id="mdjm-loading" class="mdjm-loader"><img src="' + mdjm_admin_vars.ajax_loader + '" /></div>');
					},
					success: function (response) {
						if(response.type != 'success') {
							alert('Error')
						}
						$('#mdjm-loading').replaceWith('<div id="event_employee_list">' + response.employees + '</div>');

					}
				}).fail(function (data) {
					$('#event_employee_list').replaceWith('<div id="mdjm-loading" class="mdjm-loader"><img src="' + mdjm_admin_vars.ajax_loader + '" /></div>');

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
						$('#event_employee_list').replaceWith('<div id="mdjm-loading" class="mdjm-loader"><img src="' + mdjm_admin_vars.ajax_loader + '" /></div>');
					},
					success: function (response) {
						if(response.type != 'success') {
							alert('Error');
						}
						$('#mdjm-loading').replaceWith('<div id="event_employee_list">' + response.employees + '</div>');

					}
				}).fail(function (data) {
					$('#mdjm-loading').replaceWith('<div id="event_employee_list">' + response.employees + '</div>');

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

							if( '1' == $('#mdjm_update_deposit').val() )	{
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
					dj       : $("#_mdjm_event_dj").val(),
					action   : 'mdjm_update_dj_package_options'
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
						$('#event_addons').fadeTo('§slow', 1);
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
					action   : 'mdjm_update_addon_options'
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
							$("#event_addons").fadeTo("slow", 1);
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
		
		type : function()	{
			// Display the text input for a new event type
			$( document.body ).on( 'click', '#new_event_type', function() {
				$('#new_event_type_div').fadeToggle('fast');
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
						$('#event_types').replaceWith('<div id="mdjm-loading" class="mdjm-loader"><img src="' + mdjm_admin_vars.ajax_loader + '" /></div>');
					},
					success: function (response) {
						if(response.type == 'success') {
							$('#event_type_name').val('');
							$('#new_event_type_div').fadeToggle('fast');
						} else	{
							alert(response.msg)
						}
						
						$('#mdjm-loading').replaceWith('<div id="event_types">' + response.event_types + '<a id="new_event_type" class="side-meta" href="#">Add New</a></div>');
						
					}
				}).fail(function (data) {
					$('#mdjm-loading').replaceWith('<div id="event_types">' + response.event_types + '<a id="new_event_type" class="side-meta" href="#">Add New</a></div>');
					if ( window.console && window.console.log ) {
						console.log( data );
					}
				});
				
			});
			
		},
		
		txns : function()	{
			
			// Save an event transation
			$( document.body ).on( 'click', '#save_transaction', function(event) {
				
				event.preventDefault();
				
				var postData         = {
					event_id        : $('#post_ID').val(),
					client          : $('#client_name').val(),
					amount          : $('#transaction_amount').val(),
					date            : $('#transaction_date').val(),
					direction       : $('#transaction_direction').val(),
					from            : $('#transaction_from').val(),
					to              : $('#transaction_to').val(),
					for             : $('#transaction_for').val(),
					src             : $('#transaction_src').val(),
					action          : "add_event_transaction"
				};
				
				$.ajax({
					type       : 'POST',
					dataType   : 'json',
					data       : postData,
					url        : ajaxurl,
					beforeSend : function()	{
						$('#transaction').replaceWith('<div id="mdjm-loading" class="mdjm-loader"><img src="' + mdjm_admin_vars.ajax_loader + '" /></div>');
					},
					success: function (response) {
						if(response.type == "success") {
							if(response.key == "deposit")	{
								$('#deposit_paid').prop('checked', true );	
							}
							if(response.key == 'balance')	{
								$('#balance_paid').prop('checked', true );	
							}
						} else	{
							alert(response.msg)
						}
						$("#mdjm-loading").replaceWith('<div id="transaction">' + response.transactions + '</div>')
					}
				}).fail(function (data) {
					$("#mdjm-loading").replaceWith('<div id="transaction">' + response.transactions + '</div>')
					if ( window.console && window.console.log ) {
						console.log( data );
					}
				});
			});
			
		}
		
	}
	MDJM_Events.init();
});