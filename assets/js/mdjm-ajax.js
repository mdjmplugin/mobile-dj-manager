var mdjm_scripts;
jQuery(document).ready(function ($) {
	/** Availability Widget */
	$('#mdjm_availability_check_widget_ajax').submit(function(event)	{
		if( !$("#mdjm_enquiry_date_widget").val() )	{
			return false;
		}
		event.preventDefault ? event.preventDefault() : (event.returnValue = false);
		var check_date = $("#mdjm_enquiry_date_widget").val();
		var avail = "";
		var unavail = "";
		
		$.ajax({
			type: "POST",
			dataType: "json",
			url: mdjm_scripts.ajaxurl,
			data: {
				check_date : check_date,
				avail_text: avail,
				unavail_text : unavail,
				action : "mdjm_availability_by_ajax"
			},
			beforeSend: function()	{
				$('input[type="submit"]').prop('disabled', true);
				$("#mdjm_availability_widget .mdjm_pleasewait").show();
			},
			success: function(response)	{
				if(response.result == "available") {
					if( mdjm_scripts.pass_redirect != '' )	{
						window.location.href = mdjm_scripts.pass_redirect + check_date;
					}
					else	{
						$("#mdjm_availability_widget_intro").replaceWith('<div id="mdjm_availability_response_widget"><p class="mdjm_available">' + response.message + '</p></div>');
						$("#mdjm_availability_widget .mdjm_submit").fadeTo("slow", 1);
						$("#mdjm_availability_widget .mdjm_pleasewait").hide();
					}
					$('input[type="submit"]').prop('disabled', false);
				}
				else	{
					if( mdjm_scripts.fail_redirect != '' )	{
						window.location.href = mdjm_scripts.fail_redirect;
					}
					else	{
						$("#mdjm_availability_widget_intro").replaceWith('<div id="mdjm_availability_response_widget"><p class="mdjm_notavailable">' + response.message + '</p></div>');
						$("#mdjm_availability_widget .mdjm_submit").fadeTo("slow", 1);
						$("#mdjm_availability_widget .mdjm_pleasewait").hide();
					}
					$('input[type="submit"]').prop('disabled', false);
				}
			}
		});
	});
	/** End of Availability Widget */
	
	/** End of Availability Widget Validation */
	$('#mdjm_availability_check_widget').validate(
		{
			rules:
			{
				mdjm_show_date_widget: {
					required: true,
				},
			}, // End rules
			messages:
			{
				mdjm_show_date_widget: {
						required: mdjm_scripts.required_date_widget,
						},
			}, // End messages
			// Classes
			errorClass: "mdjm_form_error",
			validClass: "mdjm_form_valid",
		} // End validate
	); // Close validate
});