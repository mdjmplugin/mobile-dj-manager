var mdjm_calendar_vars;
jQuery(document).ready(function ($) {

    // Render the calendar
	$('#mdjm-calendar').fullCalendar({
		defaultView: 'month',
		displayEventTime: false,
		header: {
			left: 'month,agendaWeek,agendaDay',
			center: 'title',
			right: 'today prev,next'
		},
		eventLimit: true,
		firstDay: mdjm_calendar_vars.first_day,
		navLinks: true,
		themeSystem: 'jquery-ui',
		timeFormat: mdjm_calendar_vars.time_format,
		eventRender: function(event, element) {
			element.popover({
				animation: true,
				container: 'body',
				content: event.description,
				delay: {
					show: 0,
					hide: 100
				},
				placement: 'top',
				title: event.tipTitle,
				trigger: 'click',
                html: true
			});
		},
		events: function(start, end, timezone, callback) {
			$.ajax({
				type: 'POST',
				url: ajaxurl,
				dataType: 'json',
				data: {
					start: start.unix(),
					end: end.unix(),
					action: 'mdjm_calendar_activity'
				},
				success: function(doc) {
					var events = [];
					$(doc).each(function() {
						events.push({
							allDay: $(this).attr('allDay'),
							backgroundColor: $(this).attr('backgroundColor'),
							borderColor: $(this).attr('borderColor'),
							description: $(this).attr('notes'),
							end: $(this).attr('end'),
							start: $(this).attr('start'),
							textColor: $(this).attr('textColor'),
							tipTitle: $(this).attr('tipTitle'),
							title: $(this).attr('title')
						});
					});
					callback(events);
				}
			}).fail(function (data) {
				if ( window.console && window.console.log ) {
					console.log( data );
				}
			});
		}
	});
});