var mdjm_admin_scripts;
jQuery(document).ready(function ($) {
	
	/*= Communication form functions
	****************************************************************/
	/**
	 * Field Validation
	 */
	$('#mdjm_form_send_comms').validate(
		{
			rules:
			{
				mdjm_email_to: {
					required: true,
				},
				mdjm_email_subject: {
					required: true,
				},
				mdjm_email_content: {
					required: true,
				},
			},
			errorClass: "mdjm-form-error",
			validClass: "mdjm-form-valid",
		}
	); // Close validate
	/**
	 * Update email content
	 */
	$('#mdjm_email_template').on('change', '', function()	{
		var email_template = $("#mdjm_email_template").val();
		var email_content = $("#mdjm_email_content").val();
		
		$.ajax({
			type: "POST",
			dataType: "json",
			url: mdjm_admin_scripts.ajaxurl,
			data: {
				template : email_template,
				action : "mdjm_set_email_content"
			},
			beforeSend: function()	{
				$("#mdjm_email_subject").addClass( "mdjm-updating" );
				$("#mdjm_email_subject").fadeTo("slow", 0.5);
				$("#mdjm_email_content").addClass( "mdjm-updating" );
				$("#mdjm_email_content").fadeTo("slow", 0.5);
				$("#mdjm_email_template").addClass( "mdjm-updating" );
				$("#mdjm_email_template").fadeTo("slow", 0.5);
				tinymce.execCommand('mceToggleEditor',false,'mdjm_email_content');
			},
			success: function(response)	{
				if(response.type == "success") {
					$("#mdjm_email_content").empty();
					tinyMCE.activeEditor.setContent('');
					$("#mdjm_email_subject").fadeTo("slow", 1);
					$("#mdjm_email_subject").removeClass( "mdjm-updating" );
					$("#mdjm_email_subject").val( response.updated_subject );
					$("#mdjm_email_content").fadeTo("slow", 1);
					$("#mdjm_email_content").removeClass( "mdjm-updating" );
					tinyMCE.activeEditor.setContent( response.updated_content );
					$("#mdjm_email_content").val( response.updated_content );
					$("#mdjm_email_template").removeClass( "mdjm-updating" );
					$("#mdjm_email_template").fadeTo("slow", 1);
					tinymce.execCommand('mceToggleEditor',false,'mdjm_email_content');
				}
				else	{
					alert(response.msg);
					$("#mdjm_email_subject").fadeTo("slow", 1);
					$("#mdjm_email_subject").removeClass( "mdjm-updating" );
					$("#mdjm_email_content").fadeTo("slow", 1);
					$("#mdjm_email_content").removeClass( "mdjm-updating" );
					$("#mdjm_email_template").fadeTo("slow", 1);
					$("#mdjm_email_template").removeClass( "mdjm-updating" );
					tinymce.execCommand('mceToggleEditor',false,'mdjm_email_content');
				}
			}
		});
	});
	/**
	 * Update email content
	 */
	$('#mdjm_email_to').on('change', '', function()	{
		var recipient = $("#mdjm_email_to").val();
		
		$.ajax({
			type: "POST",
			dataType: "json",
			url: mdjm_admin_scripts.ajaxurl,
			data: {
				recipient : recipient,
				action : "mdjm_user_events_dropdown"
			},
			beforeSend: function()	{
				$("#mdjm_email_event").addClass( "mdjm-updating" );
				$("#mdjm_email_event").fadeTo("slow", 0.5);
			},
			success: function(response)	{
				if(response.type == "success") {
					$("#mdjm_email_event").empty();
					$("#mdjm_email_event").append(response.event_list);
					$("#mdjm_email_event").fadeTo("slow", 1);
					$("#mdjm_email_event").removeClass( "mdjm-updating" );
				}
				else	{
					$("#mdjm_email_event").empty();
					$("#mdjm_email_event").append(response.event_list);
					$("#mdjm_email_event").fadeTo("slow", 1);
					$("#mdjm_email_event").removeClass( "mdjm-updating" );
				}
			}
		});
	});
});