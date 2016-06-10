<?php
/**
 * MDJM Email Class
 *
 * @package     MDJM
 * @subpackage  Classes/Emails
 * @copyright   Copyright (c) 2016, Mike Howard
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.3
*/

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * MDJM_Emails Class
 *
 * @since	1.3
 */
class MDJM_Emails {
	
	/**
	 * Holds the from address
	 *
	 * @since	1.3
	 */
	private $from_address;

	/**
	 * Holds the from name
	 *
	 * @since	1.3
	 */
	private $from_name;
	
	/**
	 * Holds the from address
	 *
	 * @since	1.3
	 */
	private $from_email;
	
	/**
	 * Holds the addresses that should receive a copy of the email
	 *
	 * @since	1.3
	 */
	private $copy_to = false;

	/**
	 * Holds the email content type
	 *
	 * @since	1.3
	 */
	private $content_type;
	
	/**
	 * Holds the email headers
	 *
	 * @since	1.3
	 */
	private $headers;

	/**
	 * Whether to send email in HTML
	 *
	 * @since	1.3
	 */
	private $html = true;

	/**
	 * The email template to use
	 *
	 * @since	1.3
	 */
	private $template;
	
	/**
	 * Whether to track emails
	 *
	 * @since	1.3
	 */
	private $track = true;
	
	/**
	 * Post ID of the tracking post
	 *
	 * @since	1.3
	 */
	public $tracking_id = 0;
	
	/**
	 * The event to which the email is associated
	 *
	 * @since	1.3
	 */
	public $event_id = 0;
	
	/**
	 * Get things going
	 *
	 * @since	1.3
	 */
	public function __construct() {

		if ( 'none' === $this->get_template() ) {
			$this->html = false;
		}

		if( ! mdjm_get_option( 'track_client_emails', false ) )	{
			$this->track = false;
		}

		add_action( 'mdjm_email_send_before', array( $this, 'send_before' ) );
		add_action( 'mdjm_email_send_after', array( $this, 'send_after' ) );

	} // __construct
	
	/**
	 * Set a property
	 *
	 * @since	1.3
	 */
	public function __set( $key, $value ) {
		$this->$key = $value;
	} // __set

	/**
	 * Get the email from name
	 *
	 * @since	1.3
	 */
	public function get_from_name() {
		if ( ! $this->from_name ) {
			$this->from_name = mdjm_get_option( 'from_name', get_bloginfo( 'name' ) );
		}

		return apply_filters( 'mdjm_email_from_name', wp_specialchars_decode( $this->from_name ), $this );
	} // get_from_name

	/**
	 * Get the email from address
	 *
	 * @since	1.3
	 */
	public function get_from_address() {
		if ( ! $this->from_address ) {
			$this->from_address = mdjm_get_option( 'system_email', get_option( 'admin_email' ) );
		}

		return apply_filters( 'mdjm_email_from_address', $this->from_address, $this );
	} // get_from_address

	/**
	 * Get the email content type
	 *
	 * @since	1.3
	 */
	public function get_content_type()	{
		if ( ! $this->content_type && $this->html ) {
			$this->content_type = apply_filters( 'mdjm_email_default_content_type', 'text/html', $this );
		}
		elseif ( ! $this->html ) {
			$this->content_type = 'text/plain';
		}

		return apply_filters( 'mdjm_email_content_type', $this->content_type, $this );
	} // get_content_type

	/**
	 * Get the email headers
	 *
	 * @since	1.3
	 */
	public function get_headers() {
		if ( ! $this->headers ) {
			$this->headers  = "From: {$this->from_name} <{$this->from_address}>\r\n";
			$this->headers .= "Reply-To: {$this->from_address}\r\n";
			$this->headers .= "Content-Type: {$this->get_content_type()}; charset=utf-8\r\n";
			$this->headers .= 'X-Mailer: ' . sprintf( __( 'MDJM Event Management version %s (http://mdjm.co.uk)', 'mobile-dj-manager' ), MDJM_VERSION_NUM );
		}

		return apply_filters( 'mdjm_email_headers', $this->headers, $this );
	} // get_headers

	/**
	 * Retrieve email templates
	 *
	 * @since	1.3
	 */
	public function get_templates() {
		$template_posts = get_posts(
			array(
				'post_type'        => 'email_template',
				'post_status'      => 'publish',
				'posts_per_page'   => -1,
				'orderby'          => 'post_title',
				'order'            => 'ASC'
			)
		);
		
		$templates = array();
		
		$templates[0] = __( 'Disable', 'mobile-dj-manager' );
		
		foreach( $template_posts as $template )	{
			$templates[ $template->ID ] = $template->post_title;
		}

		return apply_filters( 'mdjm_email_templates', $templates );
	} // get_templates

	/**
	 * Get the relevant email template
	 *
	 * @since	1.3
	 *
	 * @return	str|null
	 */
	public function get_template() {
		if ( ! $this->template ) {
			$this->template = '';
		}

		return apply_filters( 'mdjm_email_template', $this->template );
	} // get_template

	/**
	 * Build the final email
	 *
	 * @since	1.3
	 * @param	str	$to
	 * @param	str	$subject
	 * @param	str	$message
	 * @param	str	$attachments
	 * @param	str	$source
	 *
	 * @return	str
	 */
	public function build_email( $to, $subject, $message, $attachments, $source ) {

		if ( false === $this->html ) {
			return apply_filters( 'mdjm_email_message', wp_strip_all_tags( $message ), $this );
		}

		$message = $this->text_to_html( $message );
		
		$message = $this->log_email( $to, $subject, $message, $attachments, $source );
		
		ob_start();

		mdjm_get_template_part( 'email', 'header', true );

		/**
		 * Hooks into the email header
		 *
		 * @since	1.3
		 */
		do_action( 'mdjm_email_header', $this );

		if ( has_action( 'mdjm_email_template_' . $this->get_template() ) ) {
			/**
			 * Hooks into the template of the email
			 *
			 * @param string $this->template Gets the enabled email template
			 * @since	1.3
			 */
			do_action( 'mdjm_email_template_' . $this->get_template() );
		} else {
			mdjm_get_template_part( 'email', 'body', true );
		}
		
		/**
		 * Hooks into the body of the email
		 *
		 * @since	1.3
		 */
		do_action( 'mdjm_email_body', $this );

		mdjm_get_template_part( 'email', 'footer', true );

		/**
		 * Hooks into the footer of the email
		 *
		 * @since 2.1
		 */
		do_action( 'mdjm_email_footer', $this );

		$body    = ob_get_clean();
		$message = str_replace( '{email}', $message, $body );

		return apply_filters( 'mdjm_email_message', $message, $this );

	} // build_email

	/**
	 * Send the email
	 * @param	str		$to				The To address to send to.
	 * @param	str		$subject		The subject line of the email to send.
	 * @param	str		$message		The body of the email to send.
	 * @param	str|arr	$attachments	Attachments to the email in a format supported by wp_mail()
	 * @since	1.3
	 */
	public function send( $to, $subject, $message, $attachments = '', $source = '' ) {
		
		if ( ! did_action( 'init' ) && ! did_action( 'admin_init' ) ) {
			_doing_it_wrong( __FUNCTION__, __( 'You cannot send email with MDJM_Emails until init/admin_init has been reached', 'mobile-dj-manager' ), null );
			return false;
		}

		/**
		 * Hooks before the email is sent
		 *
		 * @since	1.3
		 */
		do_action( 'mdjm_email_send_before', $this );

		$message = $this->build_email( $to, $subject, $message, $attachments, $source );

		$attachments = apply_filters( 'mdjm_email_attachments', $attachments, $this );
		
		$sent = wp_mail( $to, $subject, $message, $this->get_headers(), $attachments );
				
		if ( ! empty( $this->copy_to ) )	{

			$subject = empty( $this->tracking_id ) ? $subject : '';
			$message = empty( $this->tracking_id ) ? $message : '';

			$this->send_copy( $subject, $message, $attachments );

		}

		/**
		 * Hooks after the email is sent
		 *
		 * @since	1.3
		 */
		do_action( 'mdjm_email_send_after', $this );
				
		return $sent;

	} // send

	/**
	 * Add filters / actions before the email is sent
	 *
	 * @since	1.3
	 */
	public function send_before() {
		add_filter( 'wp_mail_from', array( $this, 'get_from_address' ) );
		add_filter( 'wp_mail_from_name', array( $this, 'get_from_name' ) );
		add_filter( 'wp_mail_content_type', array( $this, 'get_content_type' ) );
	} // send_before

	/**
	 * Remove filters / actions after the email is sent
	 *
	 * @since	1.3
	 */
	public function send_after() {
		remove_filter( 'wp_mail_from', array( $this, 'get_from_address' ) );
		remove_filter( 'wp_mail_from_name', array( $this, 'get_from_name' ) );
		remove_filter( 'wp_mail_content_type', array( $this, 'get_content_type' ) );
		
		$this->update_tracking_status();
	} // send_after

	/**
	 * Converts text to formatted HTML. This is primarily for turning line breaks into <p> and <br/> tags.
	 *
	 * @since	1.3
	 */
	public function text_to_html( $message ) {
		if ( 'text/html' == $this->content_type || true === $this->html ) {
			$message = wpautop( $message );
		}

		return $message;
	} // text_to_html
	
	/**
	 * Send a copy of the email.
	 *
	 * @since	1.3
	 * @param	str		$subject		The email subject. If omitted, we'll use the title of the tracking post.
	 * @param	str		$content		The email content. If omitted, we'll use the tracking ID.
	 * @param	arr		$attachments	The email attachments.
	 */
	public function send_copy( $subject = '', $content = '', $attachments = array() )	{
		
		if ( empty( $this->copy_to ) )	{
			return;
		}
		
		if ( empty ( $content ) )	{
			$tracker = get_post( $this->tracking_id );
			
			if ( ! $tracker )	{
				return;
			}
			
			$content = $tracker->post_content;
			$content = apply_filters( 'the_content', $content );
			$content = str_replace( ']]>', ']]&gt;', $content );
			
			if ( empty( $content ) )	{
				return;
			}
			
			$subject           = html_entity_decode( get_the_title( $tracker->ID ) );
			
		}
		
		$copies = is_array( $this->copy_to ) ? array_unique( $this->copy_to ) : $this->copy_to;
		
		$args = array(
			'to_email'       => $copies,
			'from_name'      => $this->from_name,
			'from_email'     => $this->from_address,
			'event_id'       => $this->event_id,
			'client_id'      => ! empty( $this->event_id ) ? mdjm_get_event_client_id( $this->event_id ) : '',
			'subject'        => ! empty( $subject ) ? $subject : sprintf( __( 'Copy of an email recently sent via %s', 'mobile-dj-manager' ), '{application_name}' ),
			'attachments'    => $attachments,
			'message'        => mdjm_email_set_copy_text() . $content,
			'track'          => false
		);
		
		if ( mdjm_send_email_content( $args ) )	{
			update_post_meta( $tracker->ID, '_mdjm_copy_to', $copies );
		}
			
	} // send_copy
	
	/**
	 * Store the communication and insert the image which enables tracking of the email via our API.
	 *
	 * @since	1.3
	 */
	public function log_email( $to, $subject, $message, $attachments, $source ) {
		
		if ( ! empty( $this->track ) && ( 'text/html' == $this->content_type || true === $this->html ) ) {
			
			MDJM()->debug->log_it( 'Adding tracking image to email' );
			
			$this->add_tracking_post( $to, $subject, $message, $attachments, $source );
			
			if( ! empty( $this->tracking_id ) )	{
				$message = $this->add_tracking_image( $message );
			}

		}

		return $message;

	} // log_email
	
	/**
	 * Store the communication.
	 *
	 * @since	1.3
	 */
	public function add_tracking_post( $to, $subject, $message, $attachments, $source )	{
		$this->tracking_id = mdjm_email_insert_tracking_post( $to, $subject, $message, $attachments, $this, $source );
	} // add_tracking_post
	
	/**
	 * Add the tracking image.
	 *
	 * @since	1.3
	 */
	public function add_tracking_image( $message )	{
		return mdjm_email_insert_tracking_image( $message, $this );
	} // add_tracking_image
	
	/**
	 * Store the communication.
	 *
	 * @since	1.3
	 */
	public function update_tracking_status()	{
		
		if ( ! empty( $this->tracking_id ) )	{
			mdjm_email_set_tracking_status( $this->tracking_id, 'sent' );
		}
		
	} // update_tracking_status
	
} // MDJM_Emails class
