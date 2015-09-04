jQuery(document).ready(function($) 	{
	$('#save_transaction').click(function()	{
		event.preventDefault();
		var event_id = $("#post_ID").val();
		var client = $("#client_name").val();
		var trans_amount = $("#transaction_amount").val();
		var trans_date = $("#transaction_date").val();
		var trans_direction = $("#transaction_direction").val();
		var trans_from = $("#transaction_from").val();
		var trans_to = $("#transaction_to").val();
		var trans_for = $("#transaction_for").val();
		var trans_src = $("#transaction_src").val();
						
		$.ajax({
			type: "POST",
			dataType: "json",
			url: posttrans.ajax_url,
			data: {
				amount : trans_amount,
				date : trans_date,
				direction : trans_direction,
				from : trans_from,
				to : trans_to,
				for : trans_for,
				src : trans_src,
				event_id : event_id,
				client : client,
				action : "add_event_transaction"
			},
			beforeSend: function()	{
				jQuery("#transaction").replaceWith('<div class="page-content" id="loader" style="color:#F90">Loading Transactions...<img src="/wp-admin/images/loading.gif" /></div>');
			},
			success: function(response)	{
				if(response.type == "success") {
					jQuery("#loader").replaceWith('<div id="transaction">' + response.transactions + '</div>')
					if(response.key == "deposit")	{
						jQuery("#deposit_paid").prop('checked', true );	
					}
					if(response.key == "balance")	{
						jQuery("#balance_paid").prop('checked', true );	
					}
				}
				else	{
					alert(response.msg)
					jQuery("#loader").replaceWith('<div id="transaction">' + response.transactions + '</div>')
				}
			}
		});
	});
});