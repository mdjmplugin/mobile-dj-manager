/*
 *
 * Validation code for Client Zone pages
 *
 *
 */
	jQuery().ready(function()	{
		/*
		 *
		 * Validation for client playlist
		 *
		 */
		jQuery("#client-playlist").validate(	{
			/* -- Classes -- */
			errorClass: "mdjm-form-error",
			validClass: "mdjm-form-valid",
			focusInvalid: false,
			
			/* -- Rules -- */
			rules:	{
				
			}, // End rules
			
			messages:	{
				
				playlist_song:		 " Song Name is required",
				
				playlist_artist:	   " Artist is required",
				
			}
		} );
		
		/*
		 *
		 * Validation for guest playlist
		 *
		 */
		jQuery("#guest-playlist").validate(	{
			/* -- Classes -- */
			errorClass: "mdjm-form-error",
			validClass: "mdjm-form-valid",
			focusInvalid: false,
			
			/* -- Rules -- */
			rules:	{
				
			}, // End rules
			
			messages:	{
				
				first_name:		 	" First Name is required",
				
				last_name:		 	 " Last Name is required",
				
				playlist_song:		 " Song Name is required",
				
				playlist_artist:	   " Artist is required",
				
			}
		} );
		
		/*
		 *
		 * Validation for contract signing
		 *
		 */
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