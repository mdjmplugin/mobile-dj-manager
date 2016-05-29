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
