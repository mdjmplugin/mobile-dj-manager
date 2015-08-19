/*
Page Name: mdjm-dynamic.js
Description: Processes all front end Ajax processes
Since Version: 1.2.3
Date: 07 July 2015
Author: My DJ Planner <contact@mydjplanner.co.uk>
Author URI: http://www.mydjplanner.co.uk
*/
	/*
	 * Re-populate the addons select options when the package is changed
	 *
	 *
	 *
	 */
	jQuery(document).ready(function($) 	{
		$('#_mdjm_event_package').on('change', '', function()	{
			
			var package = $("#_mdjm_event_package option:selected").val();
			var dj = $("#event_dj").val();
			var addons = $("#event_addons");
			$.ajax({
				type: "POST",
				dataType: "json",
				url: mdjmaddons.ajax_url,
				data: {
					package : package,
					dj : dj,
					action : "mdjm_update_addon_options"
				},
				beforeSend: function()	{
					$("#event_addons").addClass( "mdjm-updating" );
					$("#event_addons").fadeTo("slow", 0.5);
				},
				success: function(response)	{
					if(response.type == "success") {
						addons.empty(); // Remove existing options
						addons.append(response.addons);
						$("#event_addons").fadeTo("slow", 1);
						
						$("#event_addons").removeClass( "mdjm-updating" );
					}
					else	{
						alert(response.msg);
						$("#event_addons").fadeTo("slow", 1);
						$("#event_addons").removeClass( "mdjm-updating" );
					}
				}
			});
		});
	});