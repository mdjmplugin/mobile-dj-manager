<?php
	defined( 'ABSPATH' ) or die( "Direct access to this page is disabled!!!" );
	
/**
 * Class Name: MDJM_Emails
 * Manage the Email processes and content within MDJM
 *
 *
 *
 */
if( !class_exists( 'MDJM_Emails' ) ) : 
	class MDJM_Emails	{
		// MDJM email settings can be accessed via MDJM()->email->settings
		public $settings;
		
		// MDJM email templates can be accessed via MDJM()->email->templates
		public $templates;
		
		// Email args
		private $email_args;
		private $recipient;
		private $subject;
		private $message;
		
		/*
		 * Class constructor
		 */
		public function __construct()	{
			$this->settings = $GLOBALS['mdjm_settings']['email'];
			$this->templates = $GLOBALS['mdjm_settings']['templates'];
		} // __construct
				
		/**
		 * Prepare email content and send.
		 * Depending on the $args, we may track this email, log it, filter the content and/or add attachments.
		 *
		 * @params  arr		$args
		 *			Required: content 		int|str		Post ID or email content as str
		 *			Required: to	 		str|int		email address or user ID of recipient
		 *			Optional: subject		str			required if content is not a post ID
		 *			Optional: from			int			user ID of sender, defaults to first admin (1)
		 *			Optional: attachments	arr			files to attach
		 *			Optional: journal		str|bool	The journal entry type or false not to log this action, default to 'email-client'
		 *			Optional: event_id		int			event ID
		 *			Optional: html			bool		true sends html (Default) false plain text
		 *			Optional: cc_dj			bool		true sends copy to DJ false does not, only applicable if we have event. Default per settings
		 *			Optional: cc_admin		bool		true sends copy to Admin false does not. Default per settings
		 *			Optional: source		str			what initiated the email - i.e. Event Enquiry (Default)
		 *			Optional: filter		bool		true (Default) filters subject and content for shortcode
		 *			Optional: add_filters	arr			An array with $search->$replace key val for additional filters
		 *			Optional: log_comm		bool		true (Default) logs the email, false does not
		 *
		 * @return	int|bool	$comm_id	The communication post ID if the email was successfully sent, or false.
		 */
		public function send_email( $args )	{
			if( MDJM_DEBUG == true )
				MDJM()->debug->log_it( 'Starting the MDJM send email process', true );		
			
			/**
			 * Default arg settings.
			 * Apply the `mdjm_send_email_default_args` filter to allow for custom defaults.
			 */
			$defaults = apply_filters( 
				'mdjm_send_email_default_args', 
				array(
					'content'		=> '',
					'to'			=> '',
					'subject'		=> '',
					'from'			=> 1,
					'attachments'	=> '',
					'journal'		=> 'email-client',
					'event_id'		=> '',
					'html'			=> true,
					'cc_dj'			=> isset( $this->settings['bcc_dj_to_client'] ) ? true : false,
					'cc_admin'		=> isset( $this->settings['bcc_admin_to_client'] ) ? true : false,
					'source'		=> __( 'Event Enquiry', 'mobile-dj-manager' ),
					'filter'		=> true,
					'add_filters'	=> '',
					'log_comm'		=> MDJM_TRACK_EMAILS
				)
			);
			
			$this->email_args = wp_parse_args( $args, $defaults );
			
			// Make sure we have a recipient and that the recipient exists within MDJM.
			if( empty( $this->email_args['to'] ) )	{
				if( MDJM_DEBUG == true )
					 MDJM()->debug->log_it( 'ERROR: Missing recipient' );
					 
				return false;	
			}
			else	{
				$this->recipient = is_numeric( $this->email_args['to'] ) ? get_userdata( $this->email_args['to'] ) : get_user_by( 'email', $this->email_args['to'] );
				
				if( empty( $this->recipient ) )	{
					if( MDJM_DEBUG == true )
						 MDJM()->debug->log_it( 'ERROR: User `' . $this->email_args['to'] . '` does not exist' );
					
					return false;
				}
			}

			// Make sure we have content.
			if( empty( $this->email_args['content'] ) )	{
				if( MDJM_DEBUG == true )
					 MDJM()->debug->log_it( 'ERROR: Missing `content` argument' );
					 
				return false;
			}
			else	{
				$content = $this->email_args['content'];
			}
			
			// Make sure we have a subject if we are not sending a post template.
			if( !is_numeric( $this->email_args['content'] ) && empty( $this->email_args['subject'] ) )	{
				if( MDJM_DEBUG == true )
					MDJM()->debug->log_it( 'ERROR: Missing `subject` argument' );
					 
				return false;
			}
			else	{
				// The subject is the post title if a template is being used.
				$this->subject = !is_numeric( $content ) ? $this->email_args['subject'] : get_the_title( $content );
			}
			
			// If we are working with an event, set the ID and retrieve the DJ.
			if( !empty( $this->email_args['event_id'] ) && is_numeric( $this->email_args['event_id'] ) )	{
				$event = $this->email_args['event_id'];
				
				$employee = get_user_by( 'ID', get_post_meta( $event->ID, '_mdjm_event_dj', true ) );
			}
			
			/**
			 * Set the message content.
			 * If we're working with a template, we need to filter the post content.
			 * From here, the content will be stored in $message.
			 */
			if( is_numeric( $content ) )	{
				// If the template does not exist exit and return false.
				if( !is_string( get_post_status( $content ) ) )	{
					if( MDJM_DEBUG == true )
						 MDJM()->debug->log_it( 'ERROR: Specified email template `' . $content . '` does not exist' );
						 
					return false;	
				}
				
				$p = get_post( $content );
				$this->message = $p->post_content;
			}
			// If its not a template, its a string
			else	{
				$this->message = $content;
			}
			
			// Run the subject and content through the filter engine to process text replacement shortcodes
			if( $this->email_args['filter'] == true )	{
				// Filter the message subject for text replacements
				$the_subject = $GLOBALS['mdjm']->filter_content(
					$this->recipient->ID,
					!empty( $event ) ? $event : '',
					$this->subject
				);
				
				// Filter the message content for text replacements
				$the_content = $GLOBALS['mdjm']->filter_content(
					$this->recipient->ID,
					!empty( $event ) ? $event : '',
					$this->message
				);
				
				// Process additional filters passed via `add_filters`
				if( !empty( $this->email_args['add_filters'] ) && is_array( $this->email_args['add_filters'] ) )	{
					if( MDJM_DEBUG == true )
						MDJM()->debug->log_it( 'Additional content filtering requested...' );
					
					foreach( $this->email_args['add_filters'] as $key => $value )	{
						$search[] = $key;
						$replace[] = $value;	
					}
					
					$the_subject = str_replace( $search, $replace, $the_subject );
					$the_content = str_replace( $search, $replace, $the_content );
				}
			}
			// Otherwise filtering is disabled
			else	{
				$the_subject = $this->subject;
				$the_content = $this->message;
			}
			
			// Fire the filter hook for customisation of the subject
			$this->subject = apply_filters( 'mdjm_email_subject', $the_subject, $this->email_args );
			
			// Fire the filter hook for customisation of the content
			$this->content = apply_filters( 'mdjm_email_subject', $the_content, $this->email_args );
			
			// Finalise HTML content
			if( $this->email_args['html'] !== false )	{
				$plain = wp_strip_all_tags( $this->content );
				$html = apply_filters( 'the_content', $this->content );
				$html = str_replace( ']]>', ']]&gt;', $this->content );
				$html = $this->finalise_html( $this->content );
			}
				
			else	{
				$plain = wp_strip_all_tags( $this->content );
			}
			
			/**
			 * Define the email headers
			 */
			$this->headers = $this->set_headers( $this->email_args );
			
			// Set the email type
			if( $this->email_args['html'] !== false )	{
				if( empty( $this->settings['multipart'] ) )	{
					$this->headers[] = 'Content-type: text/html; charset=UTF-8' . "\r\n";
				}
				else	{
					$this->boundary = uniqid( 'mdjm_' );
					$this->headers[] = 'Content-Type: multipart/alternative;boundary=' . $this->boundary . "\r\n";
					// Plain text message
					$this->message = $plain;
					$this->message .= "\r\n\r\n--" . $this->boundary . "\r\n";
					// HTML message
					$this->message .= "Content-type: text/html;charset=utf-8\r\n\r\n";
					$this->message .= $html;
					$this->message .= "\r\n\r\n--" . $this->boundary . "--";
					
				}
			}
			else	{
				$this->headers[] = 'Content-type: text/plain; charset=UTF-8' . "\r\n";
				$this->message = $plain;
			}
			
			// File attachments
			if( !empty( $this->email_args['attachments'] ) && is_array( $this->email_args['attachments'] ) )
				$files = $this->email_args['attachments'];
			else
				$files = array();
			
			// Apply filter to attach (additional) files to an email
			$files = apply_filters( 'mdjm_attach_files_to_email', $files );
			
		} // send_email
		
		/**
		 * Finalise the HTML content by ensuring we have the <html> and <body> tags within
		 * the content as WordPress does not include these tags in post content.
		 *
		 * @params	str		$content	Required: The content (message) to be sent via email.
		 *
		 * @return	str		$message	The amended content.
		 */		
		public function finalise_html( $content )	{
			// If <body> does not exist, add it
			if( !preg_match( '/<body>/' , $content ) )
				$content = '<body>' . "\r\n" . $content . "\r\n" . '</body>' . "\r\n";
			
			// If <html> does not exist, add it
			if( !preg_match( '/<html>/' , $a ) )
				$content = '<html>' . "\r\n" . $content . "\r\n" . '</html>' . "\r\n";
			
			return $content;
		} // finalise_html
		
		/**
		 * Define the email headers.
		 *
		 * @params
		 *
		 * @return
		 */		
		public function set_headers()	{
			$this->headers[] = 'MIME-Version: 1.0' . "\r\n";
			
			// Set the from address.
			// Get the user details for the sender.
			$sender = get_userdata( $this->email_args['from'] );
			
			// Set the from value which we'll use in the header.
			if( !empty( $sender ) )
				$this->headers[] = "From: " . $sender_data->display_name . " <" . $this->settings['system_email'] . ">";
				
			else
				$from = "From: " . MDJM_COMPANY . " <" . $this->settings['system_email'] . ">";
							
			$this->headers[] = 'X-Mailer: ' . MDJM_NAME . ' version ' . MDJM_VERSION_NUM . ' (http://www.mydjplanner.co.uk)' . "\r\n";		
		} // set_headers
			
	} // class MDJM_Emails
endif;