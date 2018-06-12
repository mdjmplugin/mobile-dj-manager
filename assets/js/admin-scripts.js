var mdjm_admin_vars;
jQuery(document).ready(function ($) {

    // Setup Chosen menus
	$('.mdjm-select-chosen').chosen({
		inherit_select_classes: true,
		placeholder_text_single: mdjm_admin_vars.one_option,
		placeholder_text_multiple: mdjm_admin_vars.one_or_more_option
	});

	$('.mdjm-select-chosen .chosen-search input').each( function() {
		var selectElem = $(this).parent().parent().parent().prev('select.mdjm-select-chosen'),
			placeholder = selectElem.data('search-placeholder');
		$(this).attr( 'placeholder', placeholder );
	});

	// Add placeholders for Chosen input fields
	$( '.chosen-choices' ).on( 'click', function () {
		var placeholder = $(this).parent().prev().data('search-placeholder');
		if ( typeof placeholder === 'undefined' ) {
			placeholder = mdjm_admin_vars.type_to_search;
		}
		$(this).children('li').children('input').attr( 'placeholder', placeholder );
	});

	// Dismiss admin notices
    $( document ).on( 'click', '.notice-mdjm-dismiss .notice-dismiss', function () {
        var notice = $( this ).closest( '.notice-mdjm-dismiss' ).data( 'notice' );

        var postData         = {
            notice    : notice,
            action       : 'mdjm_dismiss_notice'
        };

        $.ajax({
            type: 'POST',
            dataType: 'json',
            data: postData,
            url: ajaxurl
        });
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
				$('#_mdjm_event_deposit').attr('readonly', true );
				$('#mdjm-event-pricing-detail').addClass('mdjm-mute');
			},
			success: function (response) {
				if(response.type === 'success') {
					$('#_mdjm_event_deposit').val(response.deposit);
				} else	{
					alert(response.msg);
					$('#_mdjm_event_deposit').val(current_deposit);
				}
				$('#mdjm-event-pricing-detail').removeClass('mdjm-mute');
				$('#_mdjm_event_deposit').attr('readonly', false );				
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
		var venue;

		if ( 'manual' === $('#venue_id').val() || 'client' === $('#venue_id').val() )	{
			venue = [
				$('#venue_address1').val(),
				$('#venue_address2').val(),
				$('#venue_town').val(),
				$('#venue_county').val(),
				$('#venue_postcode').val(),
			];
		} else	{
			venue = $('#venue_id').val();
		}

		var postData     = {
			addons          : $('#event_addons').val() || [],
			package         : $('#_mdjm_event_package option:selected').val(),
			event_id        : $('#post_ID').val(),
			current_cost    : $('#_mdjm_event_cost').val(),
			event_date      : $('#_mdjm_event_date').val(),
			venue           : venue,
			employee_id     : $('#_mdjm_event_dj').val(),
            additional      : $('#_mdjm_event_additional_cost').val(),
            discount        : $('#_mdjm_event_discount').val(),
			action          : 'mdjm_update_event_cost'
		};

		$.ajax({
			type       : 'POST',
			dataType   : 'json',
			data       : postData,
			url        : ajaxurl,
			beforeSend : function()	{
                $('#mdjm-event-pricing-detail').addClass('mdjm-mute');
			},
			success: function (response) {
				if(response.type === 'success') {
					$('#_mdjm_event_package_cost').val(response.package_cost);
                    $('#_mdjm_event_addons_cost').val(response.addons_cost);
                    $('#_mdjm_event_travel_cost').val(response.travel_cost);
                    $('#_mdjm_event_additional_cost').val(response.additional_cost);
                    $('#_mdjm_event_discount').val(response.discount);

                    var value = Number(response.package_cost) + Number(response.addons_cost) + Number(response.travel_cost) + Number(response.additional_cost);
                    value = Number(value) - Number(response.discount);
                    value = mdjmFormatNumberStd( value );

                    $('#_mdjm_event_cost').val(value);

					if( mdjm_admin_vars.update_deposit )	{
						setDeposit();
					}

				} else	{
					$('#_mdjm_event_cost').val(current_cost);
				}

                $('#mdjm-event-pricing-detail').removeClass('mdjm-mute');
			}
		}).fail(function (data) {
			if ( window.console && window.console.log ) {
				console.log( data );
			}
		});

	};

	// Set travel data for event
	var setTravelData = function()	{
	var venue;
	if ( 'manual' === $('#venue_id').val() || 'client' === $('#venue_id').val() )	{
		venue = [
			$('#venue_address1').val(),
			$('#venue_address2').val(),
			$('#venue_town').val(),
			$('#venue_county').val(),
			$('#venue_postcode').val(),
		];
	} else	{
		venue = $('#venue_id').val();
	}
	var postData = {
		employee_id : $('#_mdjm_event_dj').val(),
		venue : venue,
		action  : 'mdjm_update_travel_data'
	};

	$.ajax({
		type       : 'POST',
		dataType   : 'json',
		data       : postData,
		url        : ajaxurl,
		success: function (response) {
			if(response.type === 'success') {
				$('.mdjm-travel-distance').parents('tr').show();
				$('.mdjm-travel-directions').parents('tr').show();
				$('.mdjm-travel-distance').html(response.distance);
				$('.mdjm-travel-time').html(response.time);
				$('.mdjm-travel-cost').html(response.cost);
				$('#travel_directions').attr('href', response.directions_url);
				$('#mdjm_travel_distance').val(response.distance);
				$('#mdjm_travel_time').val(response.time);
				$('#mdjm_travel_cost').val(response.raw_cost);
				$('#mdjm_travel_directions_url').val(response.directions_url);
			} else	{
				$('.mdjm-travel-distance').parents('tr').hide();
				$('#travel-directions').attr('href', '' );
				$('.mdjm-travel-directions').parents('tr').hide();
				$('#mdjm_travel_distance').val('');
				$('#mdjm_travel_time').val('');
				$('#mdjm_travel_cost').val('');
				$('#mdjm_travel_directions_url').val('');
			}
		}
	}).fail(function (data) {
		if ( window.console && window.console.log ) {
			console.log( data );
		}
	});
};

	/**
	 * General Settings Screens JS
	 */
	var MDJM_Settings = {
		init : function()	{
			if ( 'admin_page_mdjm-custom-event-fields' === mdjm_admin_vars.current_page || 'admin_page_mdjm-custom-client-fields' === mdjm_admin_vars.current_page )	{
				this.custom_fields();
			}
		},
		
		custom_fields : function()	{
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
						$.post(ajaxurl, order, function()	{
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
				update: function()	{
					var order = $(this).sortable('serialize') + '&action=order_custom_event_fields';
					$.post(ajaxurl, order, function()	{
						// Success
					});
				}
			});
		}
	};
	MDJM_Settings.init();

	/**
	 * Availability screen JS
	 */
	var MDJM_Availability = {
		init : function()	{
			this.options();
			this.checker();
		},

		options : function()	{
			// Toggle display of availability checker
            $( document.body ).on( 'click', '.toggle-availability-checker-section', function(e) {
                e.preventDefault();
                var show = $(this).html() === mdjm_admin_vars.show_avail_form ? true : false;

                if ( show ) {
                    $(this).html( mdjm_admin_vars.hide_avail_form );
                } else {
                    $(this).html( mdjm_admin_vars.show_avail_form );
                }

                $('.mdjm-availability-checker-fields').slideToggle();
            });
		},

		checker : function()	{
			$( document.body ).on( 'click', '#check-availability', function()	{
				var date         = $('#check_date'),
					display_date = $('#display_date'),
					employees    = $('#display_date'),
					roles        = $('#check_roles'),
					notice       = $('#mdjm-pending-notice');

				if ( ! date.val() || ! display_date.val() )	{
					display_date.addClass( 'mdjm-form-error' );
					return;
				}

				var postData = {
					date     : date,
					employees : employees,
					roles     : roles,
					action : 'do_availability_check_ajax'
				};

				$.ajax({
					type       : 'POST',
					dataType   : 'json',
					data       : postData,
					url        : ajaxurl,
					beforeSend : function()	{
                        $('#mdjm_availability_fields').addClass('mdjm-mute');
					},
					success: function (response) {
						$('#mdjm_availability_fields').removeClass('mdjm-mute');
					}
				}).fail(function (data) {
					if ( window.console && window.console.log ) {
						console.log( data );
					}
				});

			});
		}
	};
	MDJM_Availability.init();

	/**
	 * Events screen JS
	 */
	var MDJM_Events = {
		
		init : function()	{
			this.options();
			this.client();
			this.employee();
			this.equipment();
			this.playlist();
            this.tasks();
			this.time();
			this.travel();
			this.type();
			this.txns();
			this.venue();
		},

		options : function()	{
			// Toggle display of event options
            $( document.body ).on( 'click', '.toggle-event-options-section', function(e) {
                e.preventDefault();
                var show = $(this).html() === mdjm_admin_vars.show_event_options ? true : false;

                if ( show ) {
                    $(this).html( mdjm_admin_vars.hide_event_options );
                } else {
                    $(this).html( mdjm_admin_vars.show_event_options );
                }

                $('.mdjm-event-options-sections-wrap').slideToggle();
            });
		},

		client : function()	{
            // Toggle display of new client fields section for an event
            $( document.body ).on( 'click', '.toggle-client-add-option-section', function(e) {
                e.preventDefault();
                var show = $(this).html() === mdjm_admin_vars.show_client_form ? true : false;

                if ( show ) {
                    $(this).html( mdjm_admin_vars.hide_client_form );
                } else {
                    $(this).html( mdjm_admin_vars.show_client_form );
                }

                var header = $(this).parents('.mdjm-client-row-header');
                header.siblings('.mdjm-client-add-event-sections-wrap').slideToggle();

                var first_input;
                if ( show ) {
                    first_input = $(':input:not(input[type=button],input[type=submit],button):visible:first', header.siblings('.mdjm-client-add-event-sections-wrap'));
                } else {
                    first_input = $(':input:not(input[type=button],input[type=submit],button):visible:first', header.siblings('.mdjm-repeatable-row-standard-fields'));
                }
                first_input.focus();
            });

            // Toggle display of client details section for an event
            $( document.body ).on( 'click', '.toggle-client-details-option-section', function(e) {
                e.preventDefault();
                var show = $(this).html() === mdjm_admin_vars.show_client_details ? true : false;

                if ( show ) {
                    $(this).html( mdjm_admin_vars.hide_client_details );
                } else {
                    $(this).html( mdjm_admin_vars.show_client_details );
                }

                var header = $(this).parents('.mdjm-client-row-header');
                header.siblings('.mdjm-client-details-event-sections-wrap').slideToggle();

                var first_input;
                if ( ! show ) {
                    first_input = $(':input:not(input[type=button],input[type=submit],button):visible:first', header.siblings('.mdjm-repeatable-row-standard-fields'));
                    first_input.focus();
                }
            });

			// Display client details
			$( document.body ).on( 'click', '#toggle_client_details', function() {
				$('#mdjm-event-client-details').toggle('slow');
			});
			
			// Update the client details when the client selection changes
			$( document.body ).on( 'change', '#client_name', function(event) {
				
				event.preventDefault();
				
				if ( '' === $('#client_name').val() )	{
					$('#mdjm-event-add-new-client-fields').hide('slow');
					return;
				} else if ( 'mdjm_add_client' === $('#client_name').val() )	{
					$('#mdjm-event-add-new-client-fields').show('slow');
				} else	{

					$('#mdjm-event-add-new-client-fields').hide('slow');

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
					$('#client_firstname').addClass('mdjm-form-error');
					return;
				}
				if ( $('#client_email').val().length < 1 )	{
					$('#client_email').addClass('mdjm-form-error');
					return;
				}

				var postData = {
					client_firstname : $('#client_firstname').val(),
					client_lastname  : $('#client_lastname').val(),
					client_email     : $('#client_email').val(),
					client_address1  : $('#client_address1').val(),
					client_address2  : $('#client_address2').val(),
					client_town      : $('#client_town').val(),
					client_county    : $('#client_county').val(),
					client_postcode  : $('#client_postcode').val(),
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
                        $('.mdjm-client-option-fields').addClass('mdjm-mute');
                        $('#mdjm-add-client').hide('fast');
					},
					success: function (response) {
						$('#mdjm-add-client-fields').slideToggle();
						$('#add-client-action').html( mdjm_admin_vars.show_client_form );
						$('#client_name').empty();
						$('#client_firstname').val('');
						$('#client_lastname').val('');
						$('#client_email').val('');
                        $('#client_address1').val('');
                        $('#client_address2').val('');
                        $('#client_town').val('');
                        $('#client_county').val('');
                        $('#client_postcode').val('');
                        $('#client_phone').val('');
						$('#client_phone2').val('');
						$('#client_name').append(response.client_list);
						$('#mdjm-add-client').show('fast');
						$('#_mdjm_event_block_emails').prop('checked', false );
						$('#mdjm_reset_pw').prop('checked', true );
						$('#client_name').trigger('chosen:updated');

                        $('.mdjm-client-option-fields').removeClass('mdjm-mute');

						if ( response.type === 'error' )	{
							alert(response.message);
						}

					}
				}).fail(function (data) {
					if ( window.console && window.console.log ) {
						console.log( data );
					}
				});

			});
			
			// Display custom client fields
			$( document.body ).on( 'click', '#toggle_custom_client_fields', function() {
				$('#mdjm_event_custom_client_fields').toggle('fast');
			});
			// Display custom event fields
			$( document.body ).on( 'click', '#toggle_custom_event_fields', function() {
				$('#mdjm_event_custom_event_fields').toggle('fast');
			});
			// Display custom venue fields
			$( document.body ).on( 'click', '#toggle_custom_venue_fields', function() {
				$('#mdjm_event_custom_venue_fields').toggle('fast');
			});
			
		},

		employee : function()	{

			// Add a new employee role
			$( document.body ).on( 'click', '#new_mdjm_role', function(e) {
				e.preventDefault();

				if ( $('#add_mdjm_role').hasClass('mdjm-form-error') )	{
					$('#add_mdjm_role').removeClass('mdjm-form-error');
				}

				if ( $('#add_mdjm_role').val().length < 1 )	{
					$('#add_mdjm_role').addClass('mdjm-form-error');
					return;
				}

				var postData  = {
					role_name : $('#add_mdjm_role').val(),
					action    : 'mdjm_add_role'
				};
						
				$.ajax({
					type: 'POST',
					dataType: 'json',
					data: postData,
					url: ajaxurl,
					beforeSend: function()	{
						$('input[type="submit"]').prop('disabled', true);
						$('#new_mdjm_role').hide();
						$('#pleasewait').show();
						$('#all_roles').addClass( 'mdjm-mute' );
						$('#employee_role').addClass( 'mdjm-mute' );
						$('#all_roles').fadeTo('slow', 0.5);
						$('#employee_role').fadeTo('slow', 0.5);
					},
					success: function(response)	{
						if(response.type === 'success') {
							$('#all_roles').empty(); // Remove existing options
							$('#employee_role').empty();
							$('#all_roles').append(response.options);
							$('#employee_role').append(response.options);
							$('#add_mdjm_role').val('');
							$('#all_roles').fadeTo('slow', 1);
							$('#all_roles').removeClass( 'mdjm-mute' );
							$('#employee_role').fadeTo('slow', 1);
							$('#employee_role').removeClass( 'mdjm-mute' );
							$('input[type="submit"]').prop('disabled', false);
							$('#pleasewait').hide();
							$('#new_mdjm_role').show();
						}
						else	{
							alert(response.msg);
							$('#all_roles').fadeTo('slow', 1);
							$('#all_roles').removeClass( 'mdjm-mute' );
							$('#employee_role').fadeTo('slow', 1);
							$('#employee_role').removeClass( 'mdjm-mute' );
							$('input[type="submit"]').prop('disabled', false);
							$('#pleasewait').hide();
							$('#new_mdjm_role').show();
						}
					}
				});
			});

			// Reveal the input field to add a new event worker
            $( document.body ).on( 'click', '.toggle-add-worker-section', function(e) {
                e.preventDefault();

                var show = $(this).html() === mdjm_admin_vars.show_workers ? true : false;

                if ( show ) {
                    $(this).html( mdjm_admin_vars.hide_workers );
                } else {
                    $(this).html( mdjm_admin_vars.show_workers );
                }

                $('.mdjm-event-workers-sections-wrap').slideToggle();
				if ( $('.mdjm-event-workers-sections-wrap').is(':visible') )	{
					$('#event_new_employee_role').focus();
				}
            });

            // Currency format for new employee wage
            $( document.body ).on( 'change', '#event_new_employee_wage', function() {
                var value = $(this).val();

                if( value.length > 0)   {
                    $(this).val(mdjmFormatNumberStd( value ));
                }
            });

			// Add an employee to the event
			$( document.body ).on( 'click', '#add_event_employee', function(event) {

				event.preventDefault();

				var postData    = {
					event_id      : $('#post_ID').val(),
					employee_id   : $('#event_new_employee').val(),
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
                        $('.mdjm-event-workers-sections-wrap').addClass('mdjm-mute');
                        $('#mdjm-event-employee-list').slideToggle();
					},
					success: function (response) {
						if(response.type !== 'success') {
							alert(response.msg);
						}
                        $('#mdjm-event-employee-list').html(response.employees);
                        $('#mdjm-event-employee-list').slideToggle();
                        $('.mdjm-event-workers-sections-wrap').removeClass('mdjm-mute');
                        $('#event_new_employee_role').val( '' ).trigger('chosen:updated');
                        $('#event_new_employee').val( '' ).trigger('chosen:updated');
                        $('#event_new_employee_wage').val( '' );

					}
				}).fail(function (data) {
                    $('#mdjm-event-employee-list').slideToggle();
                    $('.mdjm-event-workers-sections-wrap').removeClass('mdjm-mute');

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
						$('.mdjm-event-workers-sections-wrap').addClass('mdjm-mute');
                        $('#mdjm-event-employee-list').slideToggle();
					},
					success: function(response) {
						if(response.type !== 'success') {
							alert('Error');
						}
						$('#mdjm-event-employee-list').html(response.employees);
                        $('#mdjm-event-employee-list').slideToggle();
                        $('.mdjm-event-workers-sections-wrap').removeClass('mdjm-mute');

					}
				}).fail(function (data) {
                    $('#mdjm-event-employee-list').slideToggle();
                    $('.mdjm-event-workers-sections-wrap').removeClass('mdjm-mute');

					if ( window.console && window.console.log ) {
						console.log( data );
					}
				});
				
			});
		},
		
		equipment : function()	{

			$( document.body ).on( 'change', '#_mdjm_event_package,#event_addons,#_mdjm_event_additional_cost,#_mdjm_event_discount', function() {
				setCost();
			});
			
			$( document.body ).on( 'focusout', '#_mdjm_event_cost', function() {
				if( mdjm_admin_vars.deposit_is_pct )	{
					setDeposit();
				}
			});

			// Update package and add-on options when the event type, date or primary employee are updated.
			$( document.body ).on( 'change', '#_mdjm_event_dj,#mdjm_event_type,#display_event_date', function(event) {
				event.preventDefault();
				var current_deposit = $('#_mdjm_event_deposit').val();
				var postData        = {
					package    : $('#_mdjm_event_package option:selected').val(),
					addons     : $('#event_addons').val() || [],
					employee   : $('#_mdjm_event_dj').val(),
					event_type : $('#mdjm_event_type').val(),
					event_date : $('#_mdjm_event_date').val(),
					action     : 'refresh_event_package_options'
				};

				$.ajax({
					type       : 'POST',
					dataType   : 'json',
					data       : postData,
					url        : ajaxurl,
					beforeSend : function()	{
						$('#mdjm-event-equipment-row').hide();
						$('#mdjm-equipment-loader').show();
					},
					success: function (response) {
						if(response.type === 'success') {
							$('#_mdjm_event_package').empty(); // Remove existing package options
							$('#_mdjm_event_package').append(response.packages);
							$('#_mdjm_event_package').trigger('chosen:updated');
							
							$('#event_addons').empty(); // Remove existing addon options
							$('#event_addons').append(response.addons);
							$('#event_addons').trigger('chosen:updated');

							$('#mdjm-equipment-loader').hide();
							$('#mdjm-event-equipment-row').show();

							setCost();
						} else	{
							alert(response.msg);
						}						

						$('#mdjm-equipment-loader').hide();
						$('#mdjm-event-equipment-row').show();

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
					package  : $('#_mdjm_event_package option:selected').val(),
					employee : $('#_mdjm_event_dj').val(),
					event_type : $('#mdjm_event_type').val(),
					event_date : $('#_mdjm_event_date').val(),
					selected : $('#event_addons').val() || [],
					action   : 'refresh_event_addon_options'
				};

				$.ajax({
					type       : 'POST',
					dataType   : 'json',
					data       : postData,
					url        : ajaxurl,
					beforeSend : function()	{
						$('#mdjm-event-equipment-row').hide();
						$('#mdjm-equipment-loader').show();
					},
					success: function (response) {
						if(response.type === 'success') {
							$('#event_addons').empty();
							$('#event_addons').append(response.addons);
							$('#event_addons').trigger('chosen:updated');
                            setCost();

							$('#mdjm-equipment-loader').hide();
							$('#mdjm-event-equipment-row').show();
						} else	{
							alert(response.msg);
						}	

						$('#mdjm-equipment-loader').hide();
						$('#mdjm-event-equipment-row').show();
					}
				}).fail(function (data) {
					if ( window.console && window.console.log ) {
						console.log( data );
					}
				});

			});

		},

		playlist : function()	{
			$( document.body ).on( 'change', '#_mdjm_event_playlist', function() {
				$('#mdjm-playlist-limit').toggle('fast');
			});
		},

        tasks : function()	{
            // Render the run task button when an event task is selected
			$( document.body ).on( 'change', '#mdjm_event_task', function() {
                var task = $(this).val();

                if ( '0' === task ) {
                    $('#mdjm-event-task-run').addClass('mdjm-hidden');
                } else  {
                    $('#mdjm-event-task-run').removeClass('mdjm-hidden');
                }
			});

            // Execute the selected event task
            $( document.body ).on( 'click', '#mdjm-run-task', function(e)   {
                e.preventDefault();

                $('#mdjm-event-tasks').addClass('mdjm-mute');

                var task     = $('#mdjm_event_task').val(),
                    event_id = $('#post_ID').val();

                if ( 'reject-enquiry' === task )    {
                    var client = $('#client_name').val(),
                        params = { page:'mdjm-comms', recipient:client, template:mdjm_admin_vars.unavailable_template, event_id:event_id, 'mdjm-action':'respond_unavailable' },
                        url    = mdjm_admin_vars.admin_url + 'admin.php?';

                    window.location.href = url + $.param( params );
                    return;
                }

                var postData = {
                    event_id : event_id,
                    task     : task,
                    action   : 'mdjm_execute_event_task'
                };
                $.ajax({
                    type       : 'POST',
                    dataType   : 'json',
                    data       : postData,
                    url        : ajaxurl,
                    beforeSend : function() {
                        $('#mdjm-run-task').addClass('mdjm-hidden');
                        $('#mdjm-spinner').css('visibility', 'visible');
                    },
                    complete : function()	{
						$('#mdjm-event-tasks').removeClass('mdjm-mute');
					},
                    success: function (response) {
                        if ( response.success ) {
                            $('#mdjm-event-task-run').addClass('mdjm-hidden');
                            $('#mdjm_event_status').val(response.data.status);
                            $('#mdjm_event_status').trigger('chosen:updated');
                            $('.task-history-items').html(response.data.history);
                            $('.task-history-items').removeClass('description');
                            $('#mdjm-run-task').removeClass('mdjm-hidden');
                            $('#mdjm_event_task').val('0');
                            $('#mdjm_event_task').trigger('chosen:updated');
                            $('#mdjm-spinner').css('visibility', 'hidden');
                        } else  {
                            alert('Error');
                        }
                    }
                }).fail(function (data) {
                    if ( window.console && window.console.log ) {
                        console.log( data );
                    }
                });
            });
		},

		time : function()	{
			// Set the setup date
			$( document.body ).on( 'change', '#display_event_date', function() {
				if( $('#dj_setup_date').val().length < 1 )	{
					$('#dj_setup_date').val($('#display_event_date').val());
				}
			});

            // Set the setup time
            if ( mdjm_admin_vars.setup_time_interval )  {
                $( document.body ).on( 'change', '#event_start_hr, #event_start_min, #event_start_period', function() {
                    var hour       = $('#event_start_hr').val();
                    var minute     = $('#event_start_min').val();
                    var meridiem   = '';
                    var date       = $('#_mdjm_event_date').val();

                    if ( 'H:i' !== mdjm_admin_vars.time_format )    {
                        meridiem = ' ' + $('#event_start_period').val();
                    }

                    var time = hour + ':' + minute + meridiem;

                    var postData = {
                        time   : time,
                        date   : date,
                        action : 'mdjm_event_setup_time'
                    };
                    $.ajax({
                        type       : 'POST',
                        dataType   : 'json',
                        data       : postData,
                        url        : ajaxurl,
                        success: function (response) {
                            $('#dj_setup_hr').val(response.data.hour);
                            $('#dj_setup_min').val(response.data.minute);
                            if ( 'H:i' !== mdjm_admin_vars.time_format )    {
                                $('#dj_setup_period').val(response.data.meridiem);
                            }
                            if( $('#dj_setup_date').val().length < 1 || $('#dj_setup_date').val() !== response.data.date )   {
                                $('#dj_setup_date').val(response.data.date);
                                $('#_mdjm_event_djsetup').val(response.data.datepicker);
                            }
                        }
                    }).fail(function (data) {
                        if ( window.console && window.console.log ) {
                            console.log( data );
                        }
                    });
                });
            }
		},

		travel : function()	{
			$( document.body ).on( 'change', '#venue_id', function()	{
				if( 'client' === $('#venue_id').val() )	{
					setClientAddress();
				}
			});
			// Update the travel data when the primary employee or venue fields are updated
			$( document.body ).on( 'change', '#_mdjm_event_dj,#venue_address1,#venue_address2,#venue_town,#venue_county,#venue_postcode', function() {
				setTravelData();
				$('#_mdjm_event_package').trigger('change');
			});

			var setClientAddress = function(){
				if( $('#client_name').length )	{
					var client = $('#client_name').val();
					var postData = {
						client_id : client,
						action    : 'mdjm_set_client_venue'
					};
					$.ajax({
						type       : 'POST',
						dataType   : 'json',
						data       : postData,
						url        : ajaxurl,
						success: function (response) {
							if(response.address1)	{
								$('#venue_address1').val(response.address1);
							}
							if(response.address2)	{
								$('#venue_address2').val(response.address2);
							}
							if(response.town)	{
								$('#venue_town').val(response.town);
							}
							if(response.county)	{
								$('#venue_county').val(response.county);
							}
							if(response.postcode)	{
								$('#venue_postcode').val(response.postcode);
							}
							setTimeout(function(){
								setTravelData();
							}, 1000);
							setTimeout(function(){
								setCost();
							}, 1750);
						}
					}).fail(function (data) {
						if ( window.console && window.console.log ) {
							console.log( data );
						}
					});
				}
			};

		},

		type : function()	{
			// Reveal the input field to add a new event type
            $( document.body ).on( 'click', '.toggle-event-type-option-section', function(e) {
                e.preventDefault();

                $('.mdjm-add-event-type-sections-wrap').slideToggle();
				if ( $('.mdjm-add-event-type-sections-wrap').is(':visible') )	{
					$('#event_type_name').focus();
				}
            });

			// Save a new event type
			$( document.body ).on( 'click', '#mdjm-add-event-type', function(event) {
				
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
                        $('.mdjm-event-option-fields').addClass('mdjm-mute');
						$('#mdjm-add-event-type').hide('fast');
					},
					success: function (response) {
						if(response) {
                            if ( 'success' !== response.data.msg )  {
                                $('#event_type_name').addClass('mdjm-form-error');
								$('#mdjm-add-event-type').show('fast');
                                return;
                            }
                            $('#event_type_name').val('');
                            $('.mdjm-add-event-type-sections-wrap').slideToggle();
                            $('#mdjm_event_type').empty();
                            $('#mdjm_event_type').append(response.data.event_types);
                            $('#mdjm_event_type').trigger('chosen:updated');
							$('#mdjm-add-event-type').show('fast');

                            $('.mdjm-event-option-fields').removeClass('mdjm-mute');
						} else	{
							alert(response.data.msg);
							$('#mdjm-add-event-type').show('fast');
						}
					}
				}).fail(function (data) {
					if ( window.console && window.console.log ) {
						console.log( data );
					}
				});
		
			});

			// Show/Hide the email templates when the disable emails checkbox is toggled
			$( document.body ).on( 'click', '#mdjm_block_emails', function() {
				$('.mdjm-quote-template').toggle('fast');
			});

		},
		
		txns : function()	{

			// Show/Hide transaction table
			$( document.body ).on( 'click', '#mdjm_txn_toggle', function() {
				$('#mdjm_event_txn_table').toggle('slow');
			});

			// Show/Hide transaction table
			$( document.body ).on( 'click', '#toggle_add_txn_fields', function() {
				$('#mdjm_event_add_txn_table tbody').toggle('slow');
				$('#save-event-txn').toggle('fast');
				if ( 'show form' === $('#toggle_add_txn_fields').text() )	{
					$('#toggle_add_txn_fields').text('hide form');
				} else	{
					$('#toggle_add_txn_fields').text('show form');
				}
			});
			
			// Transaction direction
			$( document.body ).on( 'change', '#mdjm_txn_direction', function() {
				if ( 'In' === $('#mdjm_txn_direction').val() )	{
					$('#mdjm_txn_from_container').removeClass('mdjm-hidden');
					$('#mdjm_txn_to_container').addClass('mdjm-hidden');
					$('#mdjm-txn-email').removeClass('mdjm-hidden');
				}
				if ( 'Out' === $('#mdjm_txn_direction').val() || '' === $('#mdjm_txn_direction').val() )	{
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
						if(response.type === 'success') {
							if(response.deposit_paid === 'Y')	{
								$('#deposit_paid').prop('checked', true );	
							}
							if(response.balance_paid === 'Y')	{
								$('#balance_paid').prop('checked', true );	
							}
							if( response.event_status !== $('#mdjm_event_status').val() ) {
								$('#mdjm_event_status').val(response.event_status);
							}
						} else	{
							alert(response.msg);
						}
						$('#mdjm-loading').replaceWith('<div id="mdjm_event_txn_table">' + response.transactions + '</div>');
					}
				}).fail(function (data) {
					$('#mdjm-loading').replaceWith('<div id="mdjm_event_txn_table">' + response.transactions + '</div>');
					if ( window.console && window.console.log ) {
						console.log( data );
					}
				});
			});
			
		},
		
		venue : function()	{
			// Show manual venue details if pre-selected on event load
			if ( mdjm_admin_vars.current_page === 'post.php' )	{
				if ( 'manual' === $('#venue_id').val() )	{
					$('.mdjm-add-event-venue-sections-wrap').show();
				}
			}

            // Reveal the venue details on the event screen
            $( document.body ).on( 'click', '.toggle-event-view-venue-option-section', function(e) {
                e.preventDefault();

                var show = $(this).html() === mdjm_admin_vars.show_venue_details ? true : false;

                if ( show ) {
                    $(this).html( mdjm_admin_vars.hide_venue_details );
                } else {
                    $(this).html( mdjm_admin_vars.show_venue_details );
                }

                $('.mdjm-event-venue-details-sections-wrap').slideToggle();
            });

			// Reveal the input field to add a new venue from events screen
            $( document.body ).on( 'click', '.toggle-event-add-venue-option-section', function(e) {
                e.preventDefault();

                $('.mdjm-add-event-venue-sections-wrap').slideToggle();
				if ( $('.mdjm-add-event-venue-sections-wrap').is(':visible') )	{
					$('#venue_name').focus();
				}
            });
			
			// Update the venue details when the venue selection changes
			$( document.body ).on( 'change', '#venue_id', function(event) {
				
				event.preventDefault();
				
				refreshEventVenueDetails();
			});

			// Add a new venue from the event screen
			$( document.body ).on( 'click', '#mdjm-add-venue', function(event) {
				
				event.preventDefault();
				
				if ( $('#venue_name').val().length < 1 )	{
					$('#venue_name').addClass('mdjm-form-error');
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
                        $('.mdjm-event-option-fields').addClass('mdjm-mute');
						$('#mdjm-add-venue').hide('fast');
					},
					success: function (response) {
						$('.mdjm-add-event-venue-sections-wrap').slideToggle();
						$('#venue_id').empty();
						$('#venue_id').append(response.venue_list);
						$('#mdjm-add-venue').show();
						$('#venue_id').trigger('chosen:updated');
                        refreshEventVenueDetails();

                        $('.mdjm-event-option-fields').removeClass('mdjm-mute');

						if ( response.type === 'error' )	{
							alert(response.message);
							$('#mdjm-add-venue').show('fast');
						}

					}
				}).fail(function (data) {
					$('#mdjm-add-venue').show('fast');

					if ( window.console && window.console.log ) {
						console.log( data );
					}
				});
			});
			
		}
		
	};
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
			var highest = 1;
			var key = highest;
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

				if( typeof id !== 'undefined' ) {

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
			});

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

			$('.mdjm_repeatable_table tbody').sortable({
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
				$('#mdjm-package-month-selection').toggle('fast');
			});

			$( document.body ).on( 'click', '#_addon_restrict_date', function() {
				$('#mdjm-addon-month-selection').toggle('fast');
			});

			$( document.body ).on( 'click', '#_package_variable_pricing', function()	{
				$('#mdjm-package-variable-price-fields').toggle('fast');
			});

			$( document.body ).on( 'click', '#_addon_variable_pricing', function()	{
				$('#mdjm-addon-variable-price-fields').toggle('fast');
			});

		}
	};
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

				var recipient = $('#mdjm_email_to').val();
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
						if(response.type === 'success') {
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

	};
	MDJM_Comms.init();

	/**
	 * Tasks screen JS
	 */
	var MDJM_Tasks = {

		init : function()	{
			this.template_select();
		},

		template_select: function()	{
			// Update event list when recipient changes
			$( document.body ).on( 'change', '#mdjm_task_email_template', function(event) {
				event.preventDefault();

				var postData         = {
					template : $('#mdjm_task_email_template').val(),
					action   : 'mdjm_get_template_title'
				};

				$.ajax({
					type       : 'POST',
					dataType   : 'json',
					data       : postData,
					url        : ajaxurl,
					beforeSend : function()	{
						$('#mdjm-task-email-subject').addClass('mdjm-updating');
						$('#mdjm-task-email-subject').fadeTo('slow', 0.5);
					},
					success: function (response) {
						if(response.title) {
							$('#mdjm-task-email-subject').val(response.title);
						}
						$('#mdjm-task-email-subject').fadeTo('slow', 1);
						$('#mdjm-task-email-subject').removeClass('mdjm-updating');
					}
				}).fail(function (data) {
					if ( window.console && window.console.log ) {
						console.log( data );
					}
				});

			});
		}
	};
	MDJM_Tasks.init();

	/**
	 * Reports / Exports screen JS
	 */
	var MDJM_Reports = {

		init : function() {
			this.date_options();
		},

		date_options : function() {

			// Show hide extended date options
			$( '#mdjm-graphs-date-options' ).change( function() {
				var $this = $(this),
					date_range_options = $( '#mdjm-date-range-options' );

				if ( 'other' === $this.val() ) {
					date_range_options.show();
				} else {
					date_range_options.hide();
				}
			});

		}

	};
	MDJM_Reports.init();

	/**
	 * Export screen JS
	 */
	var MDJM_Export = {

		init : function() {
			this.submit();
			this.dismiss_message();
		},

		submit : function() {

			var self = this;

			$( document.body ).on( 'submit', '.mdjm-export-form', function(e) {
				e.preventDefault();

				var submitButton = $(this).find( 'input[type="submit"]' );

				if ( ! submitButton.hasClass( 'button-disabled' ) ) {

					var data = $(this).serialize();

					submitButton.addClass( 'button-disabled' );
					$(this).find('.notice-wrap').remove();
					$(this).append( '<div class="notice-wrap"><span class="spinner is-active"></span><div class="mdjm-progress"><div></div></div></div>' );

					// start the process
					self.process_step( 1, data, self );

				}

			});
		},

		process_step : function( step, data, self ) {

			$.ajax({
				type: 'POST',
				url: ajaxurl,
				data: {
					form: data,
					action: 'mdjm_do_ajax_export',
					step: step,
				},
				dataType: 'json',
				success: function( response ) {
					if( 'done' === response.step || response.error || response.success ) {

						// We need to get the actual in progress form, not all forms on the page
						var export_form    = $('.mdjm-export-form').find('.mdjm-progress').parent().parent();
						var notice_wrap    = export_form.find('.notice-wrap');

						export_form.find('.button-disabled').removeClass('button-disabled');

						if ( response.error ) {

							var error_message = response.message;
							notice_wrap.html('<div class="updated error"><p>' + error_message + '</p></div>');

						} else if ( response.success ) {

							var success_message = response.message;
							notice_wrap.html('<div id="mdjm-batch-success" class="updated notice is-dismissible"><p>' + success_message + '<span class="notice-dismiss"></span></p></div>');

						} else {

							notice_wrap.remove();
							window.location = response.url;

						}

					} else {
						$('.mdjm-progress div').animate({
							width: response.percentage + '%',
						}, 50, function() {
							// Animation complete.
						});
						self.process_step( parseInt( response.step ), data, self );
					}

				}
			}).fail(function (response) {
				if ( window.console && window.console.log ) {
					console.log( response );
				}
			});

		},

		dismiss_message : function() {
			$('body').on( 'click', '#mdjm-batch-success .notice-dismiss', function() {
				$('#mdjm-batch-success').parent().slideUp('fast');
			});
		}

	};
	MDJM_Export.init();

    function refreshEventVenueDetails() {
        // Update the venue details when the venue selection changes
        if ( 'manual' === $('#venue_id').val() || 'client' === $('#venue_id').val() )	{

            $('.mdjm-event-venue-details-sections-wrap').hide('fast');
            $('.mdjm-add-event-venue-sections-wrap').show('fast');
        } else	{
            $('.mdjm-add-event-venue-sections-wrap').hide('fast');

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
                success: function (response) {
                    $('#mdjm-venue-details-fields').html(response.data.venue);
                    $('.mdjm-event-venue-details-sections-wrap').show('fast');

                    var show = $('.toggle-event-view-venue-option-section').html() === mdjm_admin_vars.show_venue_details ? true : false;

                    if ( show ) {
                        $('.toggle-event-view-venue-option-section').html( mdjm_admin_vars.hide_venue_details );
                    }

                }
            }).fail(function (data) {
                if ( window.console && window.console.log ) {
                    console.log( data );
                }
            });
            setTravelData();
        }
        $('#_mdjm_event_package').trigger('change');
    }

/*
 * Validation Rules
 ******************************************/
	// Comms page
	if ( 'mdjm-event_page_mdjm-comms' === mdjm_admin_vars.current_page )	{
		$('#mdjm_form_send_comms').validate({
			errorClass: 'mdjm-form-error',
			validClass: 'mdjm-form-valid',
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
			errorClass: 'mdjm-form-error',
			validClass: 'mdjm-form-valid',
			focusInvalid: false,
			
			rules:	{
				client_name : { required: true, minlength : 1 },
				display_event_date : { required: true },
				_mdjm_event_cost   : {
                    number: true
                },
				_mdjm_event_deposit : { number: true }
			},
			
			messages:	{
				client_name      : null,
				display_event_date : null,
				_mdjm_event_cost : null,
				_mdjm_event_deposit : null
			}
		});

		$( document.body ).on( 'click', '#save-post', function() {
			if ( $('#_mdjm_event_cost').val() < '0.01' )	{
				return confirm(mdjm_admin_vars.zero_cost);
			}
		});
	}
	
});

var mdjmFormatNumberStd = function (value)	{
	// Convert the value to a floating point number in case it arrives as a string.
	var numeric = parseFloat(value);
	// Specify the local currency.
	var eventCurrency = mdjm_admin_vars.currency;
	var decimalPlaces = mdjm_admin_vars.currency_decimals;
	return numeric.toLocaleString(eventCurrency, { style: 'decimal', currency: eventCurrency, minimumFractionDigits: decimalPlaces, maximumFractionDigits: decimalPlaces });
}

var mdjmFormatCurrency = function (value) {
	// Convert the value to a floating point number in case it arrives as a string.
	var numeric = parseFloat(value);
	// Specify the local currency.
	var eventCurrency = mdjm_admin_vars.currency;
	var decimalPlaces = mdjm_admin_vars.currency_decimals;
	return numeric.toLocaleString(eventCurrency, { style: 'currency', currency: eventCurrency, minimumFractionDigits: decimalPlaces, maximumFractionDigits: decimalPlaces });
};

var mdjmFormatNumber = function(value) {
	// Convert the value to a floating point number in case it arrives as a string.
	var numeric = parseFloat(value);
	// Specify the local currency.
	var eventCurrency = mdjm_admin_vars.currency;
	return numeric.toLocaleString(eventCurrency, { style: 'decimal', minimumFractionDigits: 0, maximumFractionDigits: 0 });
};

var mdjmLabelFormatter = function (label) {
	return '<div style="font-size:12px; text-align:center; padding:2px">' + label + '</div>';
};

var mdjmLegendFormatterSources = function (label, series) {
	var slug  = label.toLowerCase().replace(/\s/g, '-');
	var color = '<div class="mdjm-legend-color" style="background-color: ' + series.color + '"></div>';
	var value = '<div class="mdjm-pie-legend-item">' + label + ': ' + Math.round(series.percent) + '% (' + mdjmFormatNumber(series.data[0][1]) + ')</div>';
	var item = '<div id="' + series.mdjm_vars.id + slug + '" class="mdjm-legend-item-wrapper">' + color + value + '</div>';

	jQuery('#mdjm-pie-legend-' + series.mdjm_vars.id).append( item );
	return item;
};

var mdjmLegendFormatterEarnings = function (label, series) {
	var slug  = label.toLowerCase().replace(/\s/g, '-');
	var color = '<div class="mdjm-legend-color" style="background-color: ' + series.color + '"></div>';
	var value = '<div class="mdjm-pie-legend-item">' + label + ': ' + Math.round(series.percent) + '% (' + mdjmFormatCurrency(series.data[0][1]) + ')</div>';
	var item = '<div id="' + series.mdjm_vars.id + slug + '" class="mdjm-legend-item-wrapper">' + color + value + '</div>';

	jQuery('#mdjm-pie-legend-' + series.mdjm_vars.id).append( item );
	return item;
};
