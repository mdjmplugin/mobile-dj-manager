/*
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