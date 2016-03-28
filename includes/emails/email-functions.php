<?php
/**
 * Contains all event related functions
 *
 * @package		MDJM
 * @subpackage	Emails
 * @since		1.3
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) )
	exit;

/**
 * Sends an email with generic content.
 *
 * @since	1.3
 * @param	arr		$args		Array of arguments for the email. See $defaults.
 * @return	bool	True if the email is sent, or false.
 */
function mdjm_send_email_content( $args )	{
	global $current_user;
	
	$defaults = array(
		'to_email'		=> $current_user->user_email,
		'from_name'		=> mdjm_get_option( 'company_name' ),
		'from_email'	=> mdjm_get_option( 'system_email' ),
		'event_id'		=> 0,
		'client_id'		=> 0,
		'subject'		=> sprintf( __( 'Email from %s', 'mobile-dj-manager' ), mdjm_get_option( 'company_name' ) ),
		'attachments'	=> array(),
		'message'		=> '',
		'track'			=> false
		
	);
	
	$args = wp_parse_args( $args, $defaults );
	
	$event_id = ! empty( $args['event_id'] ) ? $args['event_id'] : 0;

	$from_name    = $args['from_name'];
	$from_name    = apply_filters( 'mdjm_email_from_name', $from_name, 'generic', $event_id );

	$from_email   = $args['from_email'];
	$from_email   = apply_filters( 'mdjm_email_from_address', $from_email, 'generic', $event_id );

	$client		  = ! empty( $args['client_id'] ) ? get_userdata( $args['client_id'] ) : 0;
	$to_email     = $args['to_email'];

	$subject      = $args['subject'];
	$subject      = apply_filters( 'mdjm_generic_email_subject', wp_strip_all_tags( $subject ) );
	$subject      = mdjm_do_content_tags( $subject, $event_id, $args['client_id'] );

	$attachments  = apply_filters( 'mdjm_generic_email_attachments', array(), $event_id );
	
	$message	  = $args['message'];
	$message      = mdjm_do_content_tags( $message, $event_id, $args['client_id'] );

	$emails = MDJM()->emails;

	$emails->__set( 'event_id', $event_id );
	$emails->__set( 'from_name', $from_name );
	$emails->__set( 'from_address', $from_email );

	$headers = apply_filters( 'mdjm_generic_headers', $emails->get_headers(), $event_id, $args['client_id'] );
	$emails->__set( 'headers', $headers );

	$emails->__set( 'track', $args['track'] );

	$sent = $emails->send( $to_email, $subject, $message, $attachments, '' );
	
	// Send a copy of this email to the primary employee
	//if ( mdjm_get_option( 'booking_conf_to_dj', false ) )	{
		//do_action( 'mdjm_employee_booking_conf_notice', $event_id );
	//}
	
	return $sent;
} // mdjm_send_email_content

/**
 * Email the contract email to the client from a customisable email template.
 *
 * @since	1.3
 * @param	int		$event_id		The event ID
 * @return	void
 */
function mdjm_email_enquiry_accepted( $event_id )	{
	$event = mdjm_get_event( $event_id );

	$from_name    = mdjm_email_set_from_name( 'contract', $event );
	$from_name    = apply_filters( 'mdjm_email_from_name', $from_name, 'contract', $event );

	$from_email   = mdjm_email_set_from_address( 'contract', $event );
	$from_email   = apply_filters( 'mdjm_email_from_address', $from_email, 'contract', $event );

	$client		  = get_userdata( $event->client );
	$to_email     = $client->user_email;

	$subject      = mdjm_email_set_subject( mdjm_get_option( 'contract', false ), 'contract' );
	$subject      = apply_filters( 'mdjm_contract_subject', wp_strip_all_tags( $subject ) );
	$subject      = mdjm_do_content_tags( $subject, $event_id, $event->client );

	$attachments  = apply_filters( 'mdjm_contract_attachments', array(), $event );
	
	$message	  = mdjm_get_email_template_content( mdjm_get_option( 'contract', false ), 'contract' );
	$message      = mdjm_do_content_tags( $message, $event_id, $event->client );

	$emails = MDJM()->emails;

	$emails->__set( 'event_id', $event->ID );
	$emails->__set( 'from_name', $from_name );
	$emails->__set( 'from_address', $from_email );
	
	$headers = apply_filters( 'mdjm_contract_headers', $emails->get_headers(), $event_id, $event->client );
	$emails->__set( 'headers', $headers );

	$emails->send( $to_email, $subject, $message, $attachments, sprintf( __( 'Enquiry accepted and Event Status set to %s', 'mobile-dj-manager' ), mdjm_get_post_status_label( $event->post_status ) ) );
	
	
} // mdjm_email_enquiry_accepted
	
/**
 * Email the booking confirmation to the client from a customisable email template.
 *
 * @since	1.3
 * @param	int		$event_id		The event ID
 * @return	void
 */
function mdjm_email_booking_confirmation( $event_id )	{
	$event = mdjm_get_event( $event_id );

	$from_name    = mdjm_email_set_from_name( 'booking_conf', $event );
	$from_name    = apply_filters( 'mdjm_email_from_name', $from_name, 'booking_conf', $event );

	$from_email   = mdjm_email_set_from_address( 'booking_conf', $event );
	$from_email   = apply_filters( 'mdjm_email_from_address', $from_email, 'booking_conf', $event );

	$client		  = get_userdata( $event->client );
	$to_email     = $client->user_email;

	$subject      = mdjm_email_set_subject( mdjm_get_option( 'booking_conf_client', false ), 'booking_conf' );
	$subject      = apply_filters( 'mdjm_booking_conf_subject', wp_strip_all_tags( $subject ) );
	$subject      = mdjm_do_content_tags( $subject, $event_id, $event->client );

	$attachments  = apply_filters( 'mdjm_booking_conf_attachments', array(), $event );
	
	$message	  = mdjm_get_email_template_content( mdjm_get_option( 'booking_conf_client', false ), 'booking_conf' );
	$message      = mdjm_do_content_tags( $message, $event_id, $event->client );

	$emails = MDJM()->emails;

	$emails->__set( 'event_id', $event->ID );
	$emails->__set( 'from_name', $from_name );
	$emails->__set( 'from_address', $from_email );
	
	$headers = apply_filters( 'mdjm_booking_conf_headers', $emails->get_headers(), $event_id, $event->client );
	$emails->__set( 'headers', $headers );

	$emails->send( $to_email, $subject, $message, $attachments, sprintf( __( 'Contract Signed and Event Status set to %s', 'mobile-dj-manager' ), mdjm_get_post_status_label( $event->post_status ) ) );
	
	// Send a copy of this email to the primary employee
	if ( mdjm_get_option( 'booking_conf_to_dj', false ) )	{
		do_action( 'mdjm_employee_booking_conf_notice', $event );
	}
} // mdjm_email_booking_confirmation

/**
 * Retrieve the email subject for the given template.
 *
 * @since	1.3
 * @param	int			$template_id	The post ID of the email template.
 * @param	str|bool	$email_type		The type of email.
 * @return	str			$subject		The subject (title) of the template.
 */
function mdjm_email_set_subject( $template_id, $email_type = false )	{
	$subject = get_the_title( $template_id );
	
	if( ! empty( $email_type ) )	{
		return apply_filters( 'mdjm_email_subject_{$email_type}', $subject, $template_id );
	}
	
	return apply_filters( 'mdjm_email_subject', $subject, $template_id );
} // mdjm_email_set_subject

/**
 * Set the email from name.
 *
 * @since	1.3
 * @param	str		$email_type		The type of email we're sending.
 * @param	obj		$event			MDJM_Event class object
 * @return	str		The from name to use in the email
 */
function mdjm_email_set_from_name( $email_type, $event )	{
	$from_name = mdjm_get_option( 'company_name', wp_specialchars_decode( get_bloginfo( 'name' ), ENT_QUOTES ) );
	
	$setting = mdjm_get_option( $email_type . '_from', 'admin' );
	
	if ( $setting && $setting == 'dj' )	{
		$employee_data = get_userdata( $event->employee_id );
		
		if( $employee_data )	{
			$from_name = $employee_data->display_name;
		}
	}
	
	return apply_filters( 'mdjm_email_{$email_type}_from_name', $from_name, $event );
} // mdjm_email_set_from_name

/**
 * Set the email from address.
 *
 * @since	1.3
 * @param	str		$event_type		The type of email we're sending.
 * @param	obj		$event			MDJM_Event class object
 * @return	str		The from address to use in the email
 */
function mdjm_email_set_from_address( $email_type, $event )	{
	$from_address = mdjm_get_option( 'system_email', get_bloginfo( 'admin_email' ) );
	
	$setting = mdjm_get_option( $email_type . '_from', 'admin' );
	
	if ( $setting && $setting == 'dj' )	{
		$employee_data = get_userdata( $event->employee_id );
		
		if( $employee_data )	{
			$from_address = $employee_data->user_email;
		}
	}
	
	return apply_filters( 'mdjm_email_{$email_type}_from_address', $from_address, $event );
} // mdjm_email_set_from_address

/**
 * Retrieve the email content for the given template.
 *
 * @since	1.3
 * @param	int			$template_id	The post ID of the email template.
 * @param	str|bool	$email_type		The type of email.
 * @return	str			$content		The content from the template.
 */
function mdjm_get_email_template_content( $template_id, $email_type = false )	{
	$template = get_post( $template_id );
	
	if( ! $template || 'email_template' != $template->post_type )	{
		return false;
	}
	
	$content = apply_filters( 'the_content', $template->post_content );
	$content = str_replace( ']]>', ']]&gt;', $content );
	
	if( ! empty( $email_type ) )	{
		return apply_filters( 'mdjm_email_content_{$email_type}', $content, $template_id );
	}
	
	return apply_filters( 'mdjm_email_content', $content, $template_id );
} // mdjm_get_email_template_content

/**
 * Replicate the email content to a post and store for tracking.
 *
 * @since	1.3
 * @param	int			$to				The recipient email address
 * @param	str			$subject		The email subject
 * @param	str			$message		The email message content
 * @param	arr			$attachments	The email attachments
 * @param	obj			$mdjm_email		MDJM_Emails class instance
 * @return	int|bool	The post ID on success, or false.
 */
function mdjm_email_insert_tracking_post( $to, $subject, $message, $attachments, $mdjm_email, $source = '' )	{
	$args = apply_filters( 'mdjm_email_tracking_post_args',
		array(
			'post_title'		=> $subject,
			'post_content'		=> $message,
			'post_status'		=> 'ready to send',
			'post_author'		=> ! empty( $from ) ? $from : 1,
			'post_type'			=> 'mdjm_communication',
			'ping_status'		=> false,
			'comment_status'	=> 'closed'
		)
	);
	
	$recipient = get_user_by( 'email', $to );
	
	$meta = apply_filters( 'mdjm_email_tracking_post_meta',
		array(
			'date_sent'	=> current_time( 'timestamp' ),
			'recipient'	=> ! empty( $recipient ) ? $recipient->ID : '',
			'source'	=> $source,
			'event'		=> $mdjm_email->event_id
		)
	);
	
	$tracking_id = wp_insert_post( $args );
	
	if( $tracking_id )	{
		
		foreach( $meta as $key => $value )	{
			add_post_meta( $tracking_id, '_' . $key, $value );	
		}
		
		if( ! empty( $attachments ) && is_array( $attachments ) )	{
			
			foreach( $attachments as $file )	{
				if( ! file_exists( $file ) )	{							
					continue;
				}
				
				$file_type = wp_check_filetype( basename( $file ), null );
				
				$upload_dir = wp_upload_dir();
				
				$attachment = array(
					'guid'           => $upload_dir['url'] . '/' . basename( $file ), 
					'post_mime_type' => $file_type['type'],
					'post_title'     => preg_replace( '/\.[^.]+$/', '', basename( $file ) ),
					'post_content'   => '',
					'post_status'    => 'inherit' );
					
				$attach_id = wp_insert_attachment( $attachment, $file, $tracking_id );
				
				// Make sure that this file is included, as wp_generate_attachment_metadata() depends on it.
				require_once( ABSPATH . 'wp-admin/includes/image.php' );
				
				// Generate the metadata for the attachment, and update the database record.
				$attach_data = wp_generate_attachment_metadata( $attach_id, $file );
				wp_update_attachment_metadata( $attach_id, $attach_data );
			}
			
		}
		
		return $tracking_id;
	}
	
} // mdjm_email_insert_tracking_post

/**
 * Insert a tracking image into the email body.
 *
 * @since	1.3
 * @param	str			$message		The email message.
 * @param	obj			$mdjm_email		MDJM_Emails class instance.
 * @return	str			$message		The email message with the tracking image included.
 */
function mdjm_email_insert_tracking_image( $message, $mdjm_email )	{
	$image = sprintf(
		'<img alt="" src="%s/?mdjm-api=%s&post=%s&action=%s" border="0" height="3" width="37" />',
		home_url(),
		'MDJM_EMAIL_RCPT',
		$mdjm_email->tracking_id,
		'open_email'
	);
	
	$image = apply_filters( 'mdjm_tracking_image', $image );
	
	return $message . $image;
} // mdjm_email_insert_tracking_image

/**
 * Change the status of a tracked email.
 *
 * @since	1.3
 * @param	int			$tracker_id		The tracked email ID.
 * @param	str			$status			The new status.
 * @return	void
 */
function mdjm_email_set_tracking_status( $tracking_id, $status )	{
	do_action( 'mdjm_email_pre_set_tracking_status', $tracking_id, $status );
	
	wp_update_post(
		array(
			'ID'			=> $tracking_id,
			'post_status'	=> $status
		)
	);
	update_post_meta( $tracker_id, '_status_change', current_time( 'timestamp' ) );
	
	do_action( 'mdjm_email_post_set_tracking_status', $tracker_id, $status );
} // mdjm_email_set_tracking_status