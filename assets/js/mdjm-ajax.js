var mdjm_scripts;
jQuery(document).ready(function ($) {
	$('#mdjm-widget-availability-check').submit(function(event)	{
		if( !$("#widget_check_date").val() )	{
			return false;
		}
		event.preventDefault ? event.preventDefault() : (event.returnValue = false);
		var check_date = $("#widget_check_date").val();
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
				$("#widget_pleasewait").show();
			},
			success: function(response)	{
				if(response.result == "available") {
					if( mdjm_scripts.pass_redirect != '' )	{
						window.location.href = mdjm_scripts.pass_redirect + check_date;
					}
					else	{
						$("#widget_avail_intro").replaceWith('<div id="widget_avail_intro">' + response.message + '</div>');
						$("#mdjm_widget_avail_submit").fadeTo("slow", 1);
						$("#mdjm_widget_avail_submit").removeClass( "mdjm-updating" );
						$("#widget_pleasewait").hide();
					}
					$('input[type="submit"]').prop('disabled', false);
				}
				else	{
					if( mdjm_scripts.fail_redirect != '' )	{
						window.location.href = mdjm_scripts.fail_redirect;
					}
					else	{
						$("#widget_avail_intro").replaceWith('<div id="widget_avail_intro">' + response.message + '</div>');
						$("#mdjm_widget_avail_submit").fadeTo("slow", 1);
						$("#mdjm_widget_avail_submit").removeClass( "mdjm-updating" );
						$("#widget_pleasewait").hide();
					}
					$('input[type="submit"]').prop('disabled', false);
				}
			}
		});
	});
	/** End of Availability widget */
});