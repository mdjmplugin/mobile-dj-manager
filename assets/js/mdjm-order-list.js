/**
 * Ordering of client fields
 *
 *
 *
 */
jQuery(document).ready(function($) 	{
	$('.mdjm-client-list-item').sortable({
		items: '.mdjm-client-list-item',
		opacity: 0.6,
		cursor: 'move',
		axis: 'y',
		update: function()	{
			var order = $(this).sortable('serialize', { expression: /(.+)=(.+)/ } ) + '&action=mdjm_update_client_field_order';
			$.post(ajaxurl, order, function(response)	{
				// Success
			});
		}
	});
});

/**
 * Ordering of custom event fields for clients
 *
 *
 *
 */
jQuery(document).ready(function($) 	{
	$('.mdjm-custom-client-list-item').sortable({
		items: '.mdjm-custom-client-list-item',
		opacity: 0.6,
		cursor: 'move',
		axis: 'y',
		update: function()	{
			var order = $(this).sortable('serialize') + '&action=mdjm_update_custom_field_client_order';
			$.post(ajaxurl, order, function(response)	{
				// Success
			});
		}
	});
});

/**
 * Ordering of custom event fields for events
 *
 *
 *
 */
jQuery(document).ready(function($) 	{
	$('.mdjm-custom-event-list-item').sortable({
		items: '.mdjm-custom-event-list-item',
		opacity: 0.6,
		cursor: 'move',
		axis: 'y',
		update: function()	{
			var order = $(this).sortable('serialize') + '&action=mdjm_update_custom_field_event_order';
			$.post(ajaxurl, order, function(response)	{
				// Success
			});
		}
	});
});

/**
 * Ordering of custom event fields for venues
 *
 *
 *
 */
jQuery(document).ready(function($) 	{
	$('.mdjm-custom-venue-list-item').sortable({
		items: '.mdjm-custom-venue-list-item',
		opacity: 0.6,
		cursor: 'move',
		axis: 'y',
		update: function()	{
			var order = $(this).sortable('serialize') + '&action=mdjm_update_custom_field_venue_order';
			$.post(ajaxurl, order, function(response)	{
				// Success
			});
		}
	});
});