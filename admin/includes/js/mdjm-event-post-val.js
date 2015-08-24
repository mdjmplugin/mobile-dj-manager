	/*
	 * Validate event form input data
	 *
	 *
	 *
	 *
	 */
	jQuery().ready(function()	{
		jQuery("#post").validate(	{
			
			/* -- Classes -- */
			errorClass: "mdjm-form-error",
			validClass: "mdjm-form-valid",
			focusInvalid: false,
			
			/* -- Rules -- */
			rules:	{
				_mdjm_event_cost:	{
					number: true	
				},
				_mdjm_event_deposit:	{
					number: true	
				},
			}, // End rules
			
			messages:	{
				client_name:				" Select a Client",
				
				client_firstname:		" Enter client's name",
				
				client_email:	{
					required:			" Enter client's email",
					email:				" Enter a valid email address",	
				},
				
				_mdjm_event_dj: 		" No DJ selected",
				
				display_event_date:	{
					required:		 	" A date is required",
					date:				" Must be a valid date (use date picker)",
				},
				
				_mdjm_event_cost:	{
					required:			" Enter a cost",
					number:				" Accepted format example 10.00",
				},
				
				_mdjm_event_deposit:	{
					number:				" Accepted format example 10.00",
				},
				
				mdjm_event_type:		" Required",
				
				venue_id:			 	" Select a venue",
				
				venue_name:				" Enter a venue name",
				
				venue_address1:			" An address is needed",
				
				venue_town:				" A town is needed",
				
				venue_county:			" A county is needed",								
			}
			
		} ); // Validate
	} ); // function
	
	/*
	 * When the user clicks to add a new event type
	 * Display the input field for the Event Type name
	 *
	 *
	 *
	 */
	jQuery(document).ready(function($) 	{
		$('#new_event_type').click(function()	{
			$("#new_event_type_div").fadeToggle('fast');
		});
	});
	
	/*
	 * When a user has clicked to create a new Event Type
	 *
	 *
	 *
	 *
	 */
	jQuery(document).ready(function($) 	{
		$('#add_event_type').click(function()	{
			event.preventDefault();
			var new_event_type = $("#event_type_name").val();
			var selected = $('#mdjm_event_type').val();

			$.ajax({
				type: "POST",
				dataType: "json",
				url: event_type.ajax_url,
				data: {
					type : new_event_type,
					current : selected,
					action : "add_event_type"
				},
				beforeSend: function()	{
					jQuery("#event_types").replaceWith('<div class="page-content" id="loader" style="color:#F90">Updating Event Types...<img src="/wp-admin/images/loading.gif" /></div>');
				},
				success: function(response)	{
					if(response.type == "success") {
						$("#new_event_type_div").fadeToggle('fast');
						jQuery("#loader").replaceWith('<div id="event_types">' + response.event_types + '<a id="new_event_type" class="side-meta" href="#">Add New</a></div>');
						$('#new_event_type').click(function()	{
							$("#new_event_type_div").fadeToggle('fast');
						});
					}
					else	{
						alert(response.msg)
						jQuery("#loader").replaceWith('<div id="event_types">' + response.event_types + '<a id="new_event_type" class="side-meta" href="#">Add New</a></div>');
						$('#new_event_type').click(function()	{
							$("#new_event_type_div").fadeToggle('fast');
						});
					}
				}
			});
			
		});
	});
	
	
	
	/*
	 * Update the event cost when the package changes
	 *
	 *
	 *
	 */
	jQuery(document).ready(function($) 	{
		$('#_mdjm_event_package').on('change', '', function()	{
			/*var package = $("#_mdjm_event_package option:selected").val(); // Selected package
			var event_id = $("#post_ID").val(); // We need the event ID
			var cost = $("#_mdjm_event_cost"); // The id of the cost input
			var current_cost = $("#_mdjm_event_cost").val(); // Current event cost
			//var addons = $("#event_addons"); // 

			$.ajax({
				type: "POST",
				dataType: "json",
				url: mdjmeventcost.ajax_url,
				data: {
					package : package,
					event_id : event_id,
					current_cost : current_cost,
					action : "update_event_cost_from_package"
				},
				beforeSend: function()	{
					$("#_mdjm_event_cost").addClass( "mdjm-updating" );
					cost.fadeTo("fast", 0.5);
				},
				success: function(response)	{
					if(response.type == "success") {
						cost.val(response.cost);
						cost.fadeTo("fast", 1);
						$("#_mdjm_event_cost").removeClass( "mdjm-updating" );
					}
					else	{
						alert(response.msg);
						cost.val(current_cost);
						cost.fadeTo("fast", 1);
						$("#_mdjm_event_cost").removeClass( "mdjm-updating" );
					}
				}
			});*/
			update_event_cost($);
		});
	});
	
	/*
	 * Update the event cost when the addons change
	 *
	 *
	 *
	 */
	jQuery(document).ready(function($) 	{
		$('#event_addons').on('change', '', function()	{
			/*var addons = $("#event_addons").val() || []; // Selected addons
			var package = $("#_mdjm_event_package option:selected").val(); // Selected package
			var event_id = $("#post_ID").val(); // We need the event ID
			var cost = $("#_mdjm_event_cost"); // The id of the cost input
			var current_cost = $("#_mdjm_event_cost").val(); // Current event cost
			//var addons = $("#event_addons"); // 
			$.ajax({
				type: "POST",
				dataType: "json",
				url: mdjmeventcost.ajax_url,
				data: {
					addons : addons,
					package : package,
					event_id : event_id,
					current_cost : current_cost,
					action : "update_event_cost_from_addons"
				},
				beforeSend: function()	{
					cost.fadeTo("fast", 0.5);
					$("#_mdjm_event_cost").addClass( "mdjm-updating" );
				},
				success: function(response)	{
					if(response.type == "success") {
						cost.val(response.cost);
						cost.fadeTo("fast", 1);
						$("#_mdjm_event_cost").removeClass( "mdjm-updating" );
					}
					else	{
						alert(response.msg);
						cost.val(current_cost);
						cost.fadeTo("fast", 1);
						$("#_mdjm_event_cost").removeClass( "mdjm-updating" );
					}
				}
			});*/
			update_event_cost($);
		});
	});
	
	/*
	 * Re-populate the packages and addons select options when the event DJ is changed
	 *
	 *
	 *
	 */
	jQuery(document).ready(function($) 	{
		$('#_mdjm_event_dj').on('change', '', function()	{
			var addons = $("#event_addons").val() || []; // Selected addons
			var package = $("#_mdjm_event_package option:selected").val(); // Selected package
			var dj = $("#_mdjm_event_dj").val();
			$.ajax({
				type: "POST",
				dataType: "json",
				url: mdjmdjpackages.ajax_url,
				data: {
					package : package,
					addons : addons,
					dj : dj,
					action : "mdjm_update_dj_package_options"
				},
				beforeSend: function()	{
					$("#_mdjm_event_package").addClass( "mdjm-updating" );
					$("#_mdjm_event_package").fadeTo("slow", 0.5);
					$("#event_addons").addClass( "mdjm-updating" );
					$("#event_addons").fadeTo("slow", 0.5);
				},
				success: function(response)	{
					if(response.type == "success") {
						$("#_mdjm_event_package").empty(); // Remove existing package options
						$("#_mdjm_event_package").append(response.packages);
						$("#_mdjm_event_package").fadeTo("slow", 1);
						
						$("#event_addons").empty(); // Remove existing addon options
						$("#event_addons").append(response.addons);
						$("#event_addons").fadeTo("slow", 1);
						
						$("#_mdjm_event_package").removeClass( "mdjm-updating" );
						$("#event_addons").removeClass( "mdjm-updating" );
						update_event_cost($);
					}
					else	{
						alert(response.msg);
						$("#_mdjm_event_package").fadeTo("slow", 1);
						$("#_mdjm_event_package").removeClass( "mdjm-updating" );
						
						$("#event_addons").fadeTo("slow", 1);
						$("#event_addons").removeClass( "mdjm-updating" );
					}
				}
			});
		});
	});
	
/* --
 * FUNCTIONS
 -- */
	/*
	 * Update the cost of the event based upon package and addon selections
	 *
	 *
	 */
	function update_event_cost($)	{
		var addons = $("#event_addons").val() || []; // Selected addons
		var package = $("#_mdjm_event_package option:selected").val(); // Selected package
		var event_id = $("#post_ID").val(); // We need the event ID
		var cost = $("#_mdjm_event_cost"); // The id of the cost input
		var current_cost = $("#_mdjm_event_cost").val(); // Current event cost
		var update_deposit = $("#mdjm_update_deposit").val(); // Current event cost
		//var addons = $("#event_addons"); // 
		$.ajax({
			type: "POST",
			dataType: "json",
			url: mdjmeventcost.ajax_url,
			data: {
				addons : addons,
				package : package,
				event_id : event_id,
				current_cost : current_cost,
				action : "update_event_cost_from_addons"
			},
			beforeSend: function()	{
				cost.fadeTo("fast", 0.5);
				$("#_mdjm_event_cost").addClass( "mdjm-updating" );
			},
			success: function(response)	{
				if(response.type == "success") {
					cost.val(response.cost);
					cost.fadeTo("fast", 1);
					$("#_mdjm_event_cost").removeClass( "mdjm-updating" );
					if( update_deposit == 1 )	{
						set_deposit($);
					}
				}
				else	{
					alert(response.msg);
					cost.val(current_cost);
					cost.fadeTo("fast", 1);
					$("#_mdjm_event_cost").removeClass( "mdjm-updating" );
				}
			}
		});
	} // update_event_cost
	
	/*
	 * Update the event deposit amount based upon event cost and deposit settings
	 *
	 *
	 */
	function set_deposit($)	{
		var current_cost = $("#_mdjm_event_cost").val(); // Current event cost
		var deposit_field = $("#_mdjm_event_deposit"); // The id of the cost input
		var current_deposit = $("#_mdjm_event_deposit").val(); // Current event cost
		
		$.ajax({
			type: "POST",
			dataType: "json",
			url: mdjmsetdeposit.ajax_url,
			data: {
				current_cost : current_cost,
				action : "update_event_deposit"
			},
			beforeSend: function()	{
				deposit_field.fadeTo("fast", 0.5);
				deposit_field.addClass( "mdjm-updating" );
			},
			success: function(response)	{
				if(response.type == "success") {
					deposit_field.val(response.deposit);
					deposit_field.fadeTo("fast", 1);
					deposit_field.removeClass( "mdjm-updating" );
				}
				else	{
					alert(response.msg);
					deposit_field.val(current_deposit);
					deposit_field.fadeTo("fast", 1);
					deposit_field.removeClass( "mdjm-updating" );
				}
			}
		});
		
	} // set_deposit