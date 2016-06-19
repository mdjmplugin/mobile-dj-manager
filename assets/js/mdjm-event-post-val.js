	/*
	 * Validate event form input data
	 *
	 *
	 *
	 *
	 */
	jQuery().ready(function($)	{
		$("#post").validate(	{
			errorClass: "mdjm-form-error",
			validClass: "mdjm-form-valid",
			focusInvalid: false,
			rules:	{
				client_name         : { required : true, minlength : 1 },
				_mdjm_event_cost    : { number   : true },
				_mdjm_event_deposit : { number   : true }
			},
			messages:	{
				client_name         : " ",
				client_firstname    :	 " ",
				client_email        :	{
					required        : " ",
					email           : " "
				},
				_mdjm_event_dj      : " ",
				display_event_date  :	{
					required        : " ",
					date            : " "
				},
				_mdjm_event_cost    :	{
					required        : " ",
					number          : " "
				},
				_mdjm_event_deposit :	{
					number          : " "
				},
				mdjm_event_type     : " ",
				venue_id            : " ",
				venue_name          : " ",
				venue_address1      : " ",
				venue_town          : " ",
				venue_county        : " "								
			}
		} );
	} );
