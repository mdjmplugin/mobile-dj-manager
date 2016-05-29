var mdjm_vars;
jQuery(document).ready(function ($) {
	/*=Availability Checker
	---------------------------------------------------- */
	if( mdjm_vars.availability_ajax )	{
		$('#mdjm-availability-check').submit(function(event)	{
			if( !$("#availability_check_date").val() )	{
				return false;
			}
			event.preventDefault ? event.preventDefault() : (event.returnValue = false);
			var check_date = $("#availability_check_date").val();
			$.ajax({
				type: "POST",
				dataType: "json",
				url:  mdjm_vars.ajaxurl,
				data: {
					check_date : check_date,
					action : "mdjm_do_availability_check"
				},
				beforeSend: function()	{
					$('input[type="submit"]').hide();//prop('disabled', true);
					$("#pleasewait").show();
				},
				success: function(response)	{
					if(response.result == "available") {
						if( mdjm_vars.available_redirect != 'text' )	{
							window.location.href = mdjm_vars.available_redirect + 'mdjm_avail_date=' + check_date;
						} else	{
							$("#mdjm-availability-result").replaceWith('<div id="mdjm-availability-result">' + response.message + '</div>');
							$("#mdjm-submit-availability").fadeTo("slow", 1);
							$("#mdjm-submit-availability").removeClass( "mdjm-updating" );
							$("#pleasewait").hide();
						}
						$('input[type="submit"]').prop('disabled', false);
					} else	{
						if( mdjm_vars.unavailable_redirect != 'text' )	{
							window.location.href = mdjm_vars.unavailable_redirect + 'mdjm_avail_date=' + check_date;
						} else	{
							$("#mdjm-availability-result").replaceWith('<div id="mdjm-availability-result">' + response.message + '</div>');
							$("#mdjm-submit-availability").fadeTo("slow", 1);
							$("#mdjm-submit-availability").removeClass( "mdjm-updating" );
							$("#pleasewait").hide();
						}
						
						$('input[type="submit"]').prop('disabled', false);
					}
				}
			});
		});
	}

	$('#mdjm-availability-check').validate({
		rules: {
			'mdjm-availability-datepicker' : {
				required: true,
			},
		},
		messages: {
			'mdjm-availability-datepicker': {
				required: mdjm_vars.required_date_message,
			},
		},
	
		errorClass: "mdjm_form_error",
		validClass: "mdjm_form_valid",
	});
});