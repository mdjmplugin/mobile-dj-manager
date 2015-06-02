	jQuery().ready(function()	{
		jQuery("#post").validate(	{
			
			/* -- Classes -- */
			errorClass: "mdjm-form-error",
			validClass: "mdjm-form-valid",
			focusInvalid: false,
			
			/* -- Rules -- */
			rules:	{
			}, // End rules
			
			messages:	{
				
				post_title:		 		" Enter a Venue name",
								
				venue_address1:			" An address is needed",
				
				venue_town:				" A town is needed",
				
				venue_county:			" A county is needed",								
			}
			
		} ); // Validate
	} ); // function