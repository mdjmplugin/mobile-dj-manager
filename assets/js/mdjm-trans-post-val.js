/**
 * When the user clicks to add a new transaction type
 * Display the input field for the Transaction Type name
 *
 * @package MDJM
 */

	jQuery( document ).ready(
		function($) 	{
			$( '#new_transaction_type' ).click(
				function()	{
					$( "#new_transaction_type_div" ).fadeToggle( 'fast' );
				}
			);
		}
	);

	/*
	 * When a user has clicked to create a new transaction Type
	 *
	 *
	 *
	 *
	 */
	jQuery( document ).ready(
		function($) 	{
			$( '#add_transaction_type' ).click(
				function()	{
					event.preventDefault();
					var new_transaction_type = $( "#transaction_type_name" ).val();
					var selected             = $( '#mdjm_transaction_type' ).val();

					$.ajax(
						{
							type: "POST",
							dataType: "json",
							url: transaction_type.ajax_url,
							data: {
								type : new_transaction_type,
								current : selected,
								action : "add_transaction_type"
							},
							beforeSend: function()	{
								jQuery( "#transaction_types" ).replaceWith( '<div class="page-content" id="loader" style="color:#F90">Updating Transaction Types...</div>' );
							},
							success: function(response)	{
								if (response.type == "success") {
									$( "#new_transaction_type_div" ).fadeToggle( 'fast' );
									jQuery( "#loader" ).replaceWith( '<div id="transaction_types">' + response.transaction_types + '<a id="new_transaction_type" class="side-meta" href="#">Add New</a></div>' );
									$( '#new_transaction_type' ).click(
										function()	{
											$( "#new_transaction_type_div" ).fadeToggle( 'fast' );
										}
									);
								} else {
									alert( response.msg )
									jQuery( "#loader" ).replaceWith( '<div id="transaction_types">' + response.transaction_types + '<a id="new_transaction_type" class="side-meta" href="#">Add New</a></div>' );
									$( '#new_transaction_type' ).click(
										function()	{
											$( "#new_transaction_type_div" ).fadeToggle( 'fast' );
										}
									);
								}
							}
						}
					);

				}
			);
		}
	);
