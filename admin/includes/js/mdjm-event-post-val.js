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