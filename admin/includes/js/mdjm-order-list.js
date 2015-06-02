jQuery(document).ready(function($) 	{
	$('.mdjm-list-item').sortable({
		items: '.mdjm-list-item',
		opacity: 0.6,
		cursor: 'move',
		axis: 'y',
		update: function()	{
			var order = $(this).sortable('serialize') + '&action=mdjm_update_field_order';
			$.post(ajaxurl, order, function(response)	{
				// Success
			});
		}
	});
});