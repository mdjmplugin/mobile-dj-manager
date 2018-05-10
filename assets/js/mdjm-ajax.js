var mdjm_vars;
jQuery(document).ready(function ($) {

	/* = Datepicker
	====================================================================================== */
	var mdjm_datepicker = $( '.mdjm_datepicker' );
	if ( mdjm_datepicker.length > 0 ) {
		var dateFormat = mdjm_vars.date_format;
        var firstDay   = mdjm_vars.first_day;
        mdjm_datepicker.datepicker({
			dateFormat  : dateFormat,
            altfield    : '#_mdjm_event_date',
            altformat   : 'yy-mm-dd',
            firstday    : firstDay,
            changeyear  : true,
            changemonth : true
		});
	}

	/*=Payments Form
	---------------------------------------------------- */
	// Load the fields for the selected payment method
	$('select#mdjm-gateway, input.mdjm-gateway').change( function () {

		var payment_mode = $('#mdjm-gateway option:selected, input.mdjm-gateway:checked').val();

		if( payment_mode === '0' )	{
			return false;
		}

		mdjm_load_gateway( payment_mode );

		return false;
	});

	// Auto load first payment gateway
	if( mdjm_vars.is_payment === '1' && $('select#mdjm-gateway, input.mdjm-gateway').length ) {
		setTimeout( function() {
			mdjm_load_gateway( mdjm_vars.default_gateway );
		}, 200);
	}

	$( document.body ).on( 'click', '#mdjm-payment-part', function() {
		$('#mdjm-payment-custom').show('fast');
	});

	$( document.body ).on( 'click', '#mdjm-payment-deposit, #mdjm-payment-balance', function() {
		$('#mdjm-payment-custom').hide('fast');
	});

	$(document).on('click', '#mdjm_payment_form #mdjm_payment_submit input[type=submit]', function(e) {
		var mdjmPurchaseform = document.getElementById('mdjm_payment_form');

		if( typeof mdjmPurchaseform.checkValidity === 'function' && false === mdjmPurchaseform.checkValidity() ) {
			return;
		}

		e.preventDefault();

		$(this).val(mdjm_vars.payment_loading);
		$(this).prop('disabled', true);
		$(this).after('<span class="mdjm-payment-ajax"><i class="mdjm-icon-spinner mdjm-icon-spin"></i></span>');

		var valid = mdjm_validate_payment_form(mdjmPurchaseform);

		if ( valid.type === 'success' )	{
			$(mdjmPurchaseform).find('.mdjm-alert').hide('fast');
			$(mdjmPurchaseform).find('.error').removeClass('error');
			$(mdjmPurchaseform).submit();
		} else	{
			$(mdjmPurchaseform).find('.mdjm-alert').show('fast');
			$(mdjmPurchaseform).find('.mdjm-alert').text(valid.msg);

			if ( valid.field )	{
				$('#' + valid.field).addClass('error');
			}

			$(this).val(mdjm_vars.complete_payment);
			$(this).prop('disabled', false);
		}

	});

	/* = Playlist form validation and submission
	====================================================================================== */
	$(document).on('click', '#mdjm_playlist_form #playlist_entry_submit', function(e) {
		var mdjmPlaylistForm = document.getElementById('mdjm_playlist_form');

		if( typeof mdjmPlaylistForm.checkValidity === 'function' && false === mdjmPlaylistForm.checkValidity() ) {
			return;
		}

		e.preventDefault();
		$(this).val(mdjm_vars.submit_playlist_loading);
		$(this).prop('disabled', true);
		$('#mdjm_playlist_form_fields').addClass('mdjm_mute');
		$(this).after(' <span id="mdjm-loading" class="mdjm-loader"><img src="' + mdjm_vars.ajax_loader + '" /></span>');
		$('input').removeClass('error');

		var $form        = $('#mdjm_guest_playlist_form');
		var playlistData = $('#mdjm_guest_playlist_form').serialize();

        $form.find('.mdjm-alert').hide('fast');

		$.ajax({
			type       : 'POST',
			dataType   : 'json',
			data       : playlistData,
			url        : mdjm_vars.ajaxurl,
			success    : function (response) {
				if ( response.error )	{
					$form.find('.mdjm-alert-error').show('fast');
					$form.find('.mdjm-alert-error').html(response.error);
                    $form.find('#' + response.field).addClass('error');
				} else	{
					$('#mdjm_artist').val('');
					$('#mdjm_song').val('');
					$('#mdjm_notes').val('');
					$('#mdjm_artist').focus();

					if( $('#playlist-entries').hasClass('mdjm-hidden') )	{
						$('#playlist-entries').removeClass('mdjm-hidden');
					}

					$('#playlist-entries').append(response.entry);

					if( response.closed )	{
						$('#mdjm-playlist-input-fields').addClass('mdjm-hidden');
						$('#playlist-entries').append('<div class="mdjm-alert mdjm-alert-info">' + mdjm_vars.playlist_closed + '</div>');
					}

				}

				$('#playlist_entry_submit').prop('disabled', false);
				$('#mdjm_playlist_form_fields').find('#mdjm-loading').remove();
				$('#playlist_entry_submit').val(mdjm_vars.submit_playlist);
				$('#mdjm_guest_playlist_form_fields').removeClass('mdjm_mute');

			}
		}).fail(function (data) {
			if ( window.console && window.console.log ) {
				console.log( data );
			}
		});
	});


	/* = Guest playlist form validation and submission
	====================================================================================== */
	$(document).on('click', '#mdjm_guest_playlist_form #entry_guest_submit', function(e) {
		var mdjmGuestPlaylistForm = document.getElementById('mdjm_guest_playlist_form');

		if( typeof mdjmGuestPlaylistForm.checkValidity === 'function' && false === mdjmGuestPlaylistForm.checkValidity() ) {
			return;
		}

		e.preventDefault();
		$(this).val(mdjm_vars.submit_playlist_loading);
		$(this).prop('disabled', true);
		$('#mdjm_guest_playlist_form_fields').addClass('mdjm_mute');
		$(this).after(' <span id="mdjm-loading" class="mdjm-loader"><img src="' + mdjm_vars.ajax_loader + '" /></span>');
		$('input').removeClass('error');

		var $form        = $('#mdjm_guest_playlist_form');
		var playlistData = $('#mdjm_guest_playlist_form').serialize();

        $form.find('.mdjm-alert').hide('fast');

		$.ajax({
			type       : 'POST',
			dataType   : 'json',
			data       : playlistData,
			url        : mdjm_vars.ajaxurl,
			success    : function (response) {
				if ( response.error )	{
					$form.find('.mdjm-alert-error').show('fast');
					$form.find('.mdjm-alert-error').html(response.error);
                    $form.find('#' + response.field).addClass('error');
				} else	{
					if( $('.mdjm_guest_name_field').is(':visible') )	{
						$('.mdjm_guest_name_field').slideToggle('fast');
					}

					$('#mdjm-guest-artist').val('');
					$('#mdjm-guest-song').val('');
					$('#mdjm-guest-artist').focus();

					if( $('#guest-playlist-entries').hasClass('mdjm-hidden') )	{
						$('#guest-playlist-entries').removeClass('mdjm-hidden');
					}

					$('#guest-playlist-entries').append(response.entry);

					if( response.closed )	{
						$('#mdjm-guest-playlist-input-fields').addClass('mdjm-hidden');
						$('#guest-playlist-entries').append('<div class="mdjm-alert mdjm-alert-info">' + mdjm_vars.guest_playlist_closed + '</div>');
					}

				}

				$('#entry_guest_submit').prop('disabled', false);
				$('#mdjm_guest_playlist_form_fields').find('#mdjm-loading').remove();
				$('#entry_guest_submit').val(mdjm_vars.submit_guest_playlist);
				$('#mdjm_guest_playlist_form_fields').removeClass('mdjm_mute');

			}
		}).fail(function (data) {
			if ( window.console && window.console.log ) {
				console.log( data );
			}
		});
	});

    /* = Client profile form validation and submission
	====================================================================================== */
    $(document).on('click', '#mdjm_client_profile_form #update_profile_submit', function(e) {
		var mdjmClientProfileForm = document.getElementById('mdjm_client_profile_form');

		if( typeof mdjmClientProfileForm.checkValidity === 'function' && false === mdjmClientProfileForm.checkValidity() ) {
			return;
		}

		e.preventDefault();
        $(this).val(mdjm_vars.submit_profile_loading);
		$(this).prop('disabled', true);
		$('#mdjm_client_profile_form_fields').addClass('mdjm_mute');
		$(this).after(' <span id="mdjm-loading" class="mdjm-loader"><img src="' + mdjm_vars.ajax_loader + '" /></span>');
		$('input').removeClass('error');

        var $form       = $('#mdjm_client_profile_form');
		var profileData = $('#mdjm_client_profile_form').serialize();

        $form.find('.mdjm-alert').hide('fast');

        $.ajax({
			type       : 'POST',
			dataType   : 'json',
			data       : profileData,
			url        : mdjm_vars.ajaxurl,
			success    : function (response) {
				if ( response.error )	{
					$form.find('.mdjm-alert-error').show('fast');
					$form.find('.mdjm-alert-error').html(response.error);
                    $form.find('#' + response.field).addClass('error');
                    $form.find('#' + response.field).focus();
				} else	{
					$form.find('.mdjm-alert-success').show('fast');
					$form.find('.mdjm-alert-success').html(mdjm_vars.profile_updated);

                    $('html, body').animate({
                        scrollTop: $('.mdjm-alert-success').offset().top
                    }, 500);
				}

                if ( response.password )    {
                    window.location.href = mdjm_vars.profile_page;
                } else  {
                    $('#update_profile_submit').prop('disabled', false);
                    $('#mdjm_client_profile_form_fields').find('#mdjm-loading').remove();
                    $('#update_profile_submit').val(mdjm_vars.submit_client_profile);
                    $('#mdjm_client_profile_form_fields').removeClass('mdjm_mute');
                }

			}
		}).fail(function (data) {
			if ( window.console && window.console.log ) {
				console.log( data );
			}
		});
    });

	/*=Availability Checker
	---------------------------------------------------- */
	if( mdjm_vars.availability_ajax )	{
		$('#mdjm-availability-check').submit(function(event)	{
			if( !$('#availability_check_date').val() )	{
				return false;
			}
			event.preventDefault ? event.preventDefault() : false;
			var date = $('#availability_check_date').val();
			var postURL = mdjm_vars.rest_url;
			postURL += 'availability/';
			postURL += '?date=' + date;
			$.ajax({
				type: 'GET',
				dataType: 'json',
				url:  postURL,
				beforeSend: function()	{
					$('input[type="submit"]').hide();
					$('#pleasewait').show();
				},
				success: function(response)	{
					var availability = response.data.availability;
					if(availability.response === 'available') {
						if( mdjm_vars.available_redirect !== 'text' )	{
							window.location.href = mdjm_vars.available_redirect + 'mdjm_avail_date=' + date;
						} else	{
							$('#mdjm-availability-result').replaceWith('<div id="mdjm-availability-result">' + availability.message + '</div>');
							$('#mdjm-submit-availability').fadeTo('slow', 1);
							$('#pleasewait').hide();
						}
						$('input[type="submit"]').prop('disabled', false);
					} else	{
						if( mdjm_vars.unavailable_redirect !== 'text' )	{
							window.location.href = mdjm_vars.unavailable_redirect + 'mdjm_avail_date=' + date;
						} else	{
							$('#mdjm-availability-result').replaceWith('<div id="mdjm-availability-result">' + availability.message + '</div>');
							$('#mdjm-submit-availability').fadeTo('slow', 1);
							$('#pleasewait').hide();
						}
						
						$('input[type="submit"]').prop('disabled', false);
					}
				}
			});
		});
	}

	$('#mdjm-availability-check').validate({
		rules: {
			'mdjm-availability-datepicker' : {
				required: true,
			},
		},
		messages: {
			'mdjm-availability-datepicker': {
				required: mdjm_vars.required_date_message,
			},
		},
	
		errorClass: 'mdjm_form_error',
		validClass: 'mdjm_form_valid',
	});
});

function mdjm_validate_payment_form() {

	var msg = false;

	// Make sure an amount is selected
	var payment = jQuery('input[type="radio"][name="mdjm_payment_amount"]:checked');

	if ( payment.length === 0 ) {
		return( {msg:mdjm_vars.no_payment_amount} );
	}

	// If part payment, make sure the value is greater than 0
	if ( 'part_payment' === payment.val() )	{
		var amount = jQuery('#part-payment').val();

		if ( ! jQuery.isNumeric(amount) )	{
			return( {type:'error', field:'part-payment', msg:mdjm_vars.no_payment_amount} );
		}
	} 

	return( {type:'success'} );

}

function mdjm_load_gateway( payment_mode ) {

	// Show the ajax loader
	jQuery('.mdjm-payment-ajax').show();
	jQuery('#mdjm_payment_form_wrap').html('<img src="' + mdjm_vars.ajax_loader + '"/>');

	var url = mdjm_vars.ajaxurl;

	if ( url.indexOf( '?' ) > 0 ) {
		url = url + '&';
	} else {
		url = url + '?';
	}

	url = url + 'payment-mode=' + payment_mode;

	jQuery.post(url, { action: 'mdjm_load_gateway', mdjm_payment_mode: payment_mode },
		function(response){
			jQuery('#mdjm_payment_form_wrap').html(response);
			jQuery('.mdjm-no-js').hide();
			jQuery('.mdjm-payment-ajax').hide();
		}
	);

}
