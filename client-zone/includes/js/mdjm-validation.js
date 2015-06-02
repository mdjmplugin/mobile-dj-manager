	jQuery().ready(function()	{
		jQuery("#user-profile").validate(	{
			
			/* -- Classes -- */
			errorClass: "mdjm-form-error",
			validClass: "mdjm-form-valid",
			focusInvalid: false,
			
			/* -- Rules -- */
			rules:	{
				
			}, // End rules
			
			messages:	{
				
				first_name:		 		" Required",
								
				last_name:				" Required",
				
				phone1:					" Required",
				
				user_email:				" Required",
				
				address1:				" Required",
				
				town:					" Required",
				
				county:					" Required",
				
				postcode:				" Required",							
			}
			
		} ); // Validate Contract
		jQuery("#mdjm_sign_contract").validate(	{
			
			/* -- Classes -- */
			errorClass: "mdjm-form-error",
			validClass: "mdjm-form-valid",
			focusInvalid: false,
			
			/* -- Rules -- */
			rules:	{
				
			}, // End rules
			
			messages:	{
				
				sign_first_name:		 	" First Name must be entered",
				
				sign_last_name:		 		" Last Name must be entered",
				
				sign_acceptance:			" Indicate you accept the contract before signing",
				
				sign_is_me:					" Confirm the details within the contract are yours",
				
				sign_pass_confirm:			" Confirm your password",
			}
		} );
	} ); // function