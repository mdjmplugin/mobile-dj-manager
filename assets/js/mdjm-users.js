jQuery(document).ready(function($) 	{
	/**
	 * Add a new role within the User management interface
	 *
	 *
	 *
	 */
	$('#new_mdjm_role').click(function()	{
		event.preventDefault();
		var role_name = $("#add_mdjm_role").val();
						
		$.ajax({
			type: "POST",
			dataType: "json",
			url: ajaxurl,
			data: {
				role_name : role_name,
				action : "mdjm_add_role"
			},
			beforeSend: function()	{
				$('input[type="submit"]').prop('disabled', true);
				$("#new_mdjm_role").hide();
				$("#pleasewait").show();
				$("#all_roles").addClass( "mdjm-updating" );
				$("#employee_role").addClass( "mdjm-updating" );
				$("#all_roles").fadeTo("slow", 0.5);
				$("#employee_role").fadeTo("slow", 0.5);
			},
			success: function(response)	{
				if(response.type == "success") {
					$("#all_roles").empty(); // Remove existing options
					$("#employee_role").empty();
					$("#all_roles").append(response.options);
					$("#employee_role").append(response.options);
					$("#add_mdjm_role").val('');
					$("#all_roles").fadeTo("slow", 1);
					$("#all_roles").removeClass( "mdjm-updating" );
					$("#employee_role").fadeTo("slow", 1);
					$("#employee_role").removeClass( "mdjm-updating" );
					$('input[type="submit"]').prop('disabled', false);
					$("#pleasewait").hide();
					$("#new_mdjm_role").show();
				}
				else	{
					alert(response.msg)
					$("#all_roles").fadeTo("slow", 1);
					$("#all_roles").removeClass( "mdjm-updating" );
					$("#employee_role").fadeTo("slow", 1);
					$("#employee_role").removeClass( "mdjm-updating" );
					$('input[type="submit"]').prop('disabled', false);
					$("#pleasewait").hide();
					$("#new_mdjm_role").show();
				}
			}
		});
	});
	/**
	 * Field validation for adding a new employee
	 *
	 *
	 *
	 */	
	$("#mdjm_employee_add").validate(	{
		// Classes
		errorClass: "mdjm-form-error",
		validClass: "mdjm-form-valid",
		focusInvalid: false,
		
		// Rule
		rules:	{
		}, // End rules
		
		messages:	{								
		},
		// Suppress error messages
		errorPlacement: function()	{
            return false;  // suppresses error message text
        }
	} ); // Validate
});
