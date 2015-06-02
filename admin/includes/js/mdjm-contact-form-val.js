	jQuery().ready(function()	{
		jQuery("#edit_form_fields").validate(	{
			
			/* -- Classes -- */
			errorClass: "mdjm-form-error",
			validClass: "mdjm-form-valid",
			focusInvalid: false,
			
			/* -- Rules -- */
			rules:	{
			}, // End rules
			
			messages:	{
				field_name:				 " Enter a Label for your field",
			} // End messages
			
		} ); // Validate
	} ); // function