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
				
				post_title:		 		" Enter a Subject",
								
				content:					" The template must have content",
				
			}
			
		} ); // Validate
	} ); // function