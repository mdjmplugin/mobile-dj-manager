<?php
/**
 * Event Object
 *
 * @package     MDJM
 * @subpackage  Classes/Events
 * @copyright   Copyright (c) 2016, Mike Howard
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.3
*/

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * MDJM_Event Class
 *
 * @since	1.3
 */
class MDJM_Event {

	/**
	 * The event ID
	 *
	 * @since	1.3
	 */
	public $ID = 0;

	/**
	 * The event date
	 *
	 * @since	1.3
	 */
	private $date;
	
	/**
	 * The event short date
	 *
	 * @since	1.3
	 */
	private $short_date;
	
	/**
	 * The client ID
	 *
	 * @since	1.3
	 */
	private $client;
	
	/**
	 * The primary employee ID
	 *
	 * @since	1.3
	 */
	private $employee_id;
	
	/**
	 * The event employees
	 *
	 * @since	1.3
	 */
	private $employees;
	
	/**
	 * The event guest playlist code
	 *
	 * @since	1.3
	 */
	private $playlist_code;
		
	/**
	 * Declare the default properities in WP_Post as we can't extend it
	 * Anything we've delcared above has been removed.
	 */
	public $post_author = 0;
	public $post_date = '0000-00-00 00:00:00';
	public $post_date_gmt = '0000-00-00 00:00:00';
	public $post_content = '';
	public $post_title = '';
	public $post_excerpt = '';
	public $post_status = 'mdjm-enquiry';
	public $comment_status = 'closed';
	public $ping_status = 'closed';
	public $post_password = '';
	public $post_name = '';
	public $to_ping = '';
	public $pinged = '';
	public $post_modified = '0000-00-00 00:00:00';
	public $post_modified_gmt = '0000-00-00 00:00:00';
	public $post_content_filtered = '';
	public $post_parent = 0;
	public $guid = '';
	public $menu_order = 0;
	public $post_mime_type = '';
	public $comment_count = 0;
	public $filter;
	
	/**
	 * Get things going
	 *
	 * @since	1.3
	 */
	public function __construct( $_id = false, $_args = array() ) {
		$event = WP_Post::get_instance( $_id );
		
		return $this->setup_event( $event );
	} // __construct

	/**
	 * Given the event data, let's set the variables
	 *
	 * @since	1.3
	 * @param 	obj		$event	The Event Object
	 * @return	bool			If the setup was successful or not
	 */
	private function setup_event( $event ) {
		if( ! is_object( $event ) ) {
			return false;
		}

		if( ! is_a( $event, 'WP_Post' ) ) {
			return false;
		}

		if( 'mdjm-event' !== $event->post_type ) {
			return false;
		}
		
		foreach ( $event as $key => $value ) {
			switch ( $key ) {
				default:
					$this->$key = $value;
					break;
			}
		}
		
		$this->get_client();
		$this->get_date();
		
		return true;
	} // setup_event
	
	/**
	 * Magic __get function to dispatch a call to retrieve a private property
	 *
	 * @since	1.3
	 */
	public function __get( $key ) {
		if( method_exists( $this, 'get_' . $key ) ) {
			return call_user_func( array( $this, 'get_' . $key ) );
		}
		else {
			return new WP_Error( 'mdjm-event-invalid-property', sprintf( __( "Can't get property %s", 'mobile-dj-manager' ), $key ) );
		}
	} // __get
	
	/**
	 * Creates an event
	 *
	 * @since 	1.3
	 * @param 	arr		$data Array of attributes for an event
	 * @return	mixed	false if data isn't passed and class not instantiated for creation, or New Event ID
	 */
	public function create( $data = array() ) {
		if ( $this->id != 0 ) {
			return false;
		}

		$defaults = array(
			'post_type'   => 'mdjm-event',
			'post_status' => 'mdjm-enquiry',
			'post_title'  => __( 'New Event', 'mobile-dj-manager' )
		);

		$args = wp_parse_args( $data, $defaults );

		do_action( 'mdjm_event_pre_create', $args );

		$id	= wp_insert_post( $args, true );

		$event = WP_Post::get_instance( $id );

		do_action( 'mdjm_event_post_create', $id, $args );

		return $this->setup_event( $event );
	} // create
	
	/**
	 * Retrieve the ID
	 *
	 * @since	1.3
	 * @return	int
	 */
	public function get_ID() {
		return $this->ID;
	} // get_ID
	
	/**
	 * Retrieve the event client
	 *
	 * @since	1.3
	 * @return	int
	 */
	public function get_client() {
		if( ! isset( $this->client ) )	{
			$this->client = get_post_meta( $this->ID, '_mdjm_event_client', true );
		}
	} // get_client
	
	/**
	 * Retrieve the event contract
	 *
	 * @since	1.3
	 * @return	int
	 */
	public function get_contract() {
		$contract = get_post_meta( $this->ID, '_mdjm_event_contract', true );
		
		return apply_filters( 'mdjm_event_contract', $return, $this->ID );
	} // get_contract
	
	/**
	 * Retrieve the event date
	 *
	 * @since	1.3
	 * @return	str
	 */
	public function get_date() {
		if( ! isset( $this->date ) )	{
			$this->date = get_post_meta( $this->ID, '_mdjm_event_date', true );
		}
	} // get_date
	
	/**
	 * Retrieve the event date in long format
	 *
	 * @since	1.3
	 * @return	str
	 */
	public function get_long_date() {
		if( ! isset( $this->date ) )	{
			$this->get_date();			
		}
		
		if( empty( $this->date ) )	{
			$return = '';
		}
		else	{
			$return = date( 'l, jS F Y', strtotime( $this->date ) );
		}
		
		return apply_filters( 'mdjm_event_long_date', $return, $this->date, $this->ID );
	} // get_long_date
	
	/**
	 * Retrieve the event date in short format
	 *
	 * @since	1.3
	 * @return	str
	 */
	public function get_short_date() {
		if( ! isset( $this->date ) )	{
			$this->get_date();			
		}
		
		if( empty( $this->date ) )	{
			$return = '';
		}
		else	{
			$return = date( mdjm_get_option( 'short_date_format', 'd/m/Y' ), strtotime( $this->date ) );
		}
		
		return apply_filters( 'mdjm_event_short_date', $return, $this->date, $this->ID );
	} // get_short_date
	
	/**
	 * Retrieve the event status.
	 *
	 * @since	1.3
	 * @return	bool
	 */
	public function get_status() {
		// Current event status
		$status = $this->post_status;
		
		$return = get_post_status_object( $this->post_status )->label;
					
		return apply_filters( 'mdjm_event_status', $return, $this->ID );
	} // get_status
	
	/**
	 * Retrieve the event type.
	 *
	 * @since	1.3
	 * @return	bool
	 */
	public function get_type() {
		$types = wp_get_object_terms( $this->ID, 'event-types' );
			
		if( !empty( $types ) )	{
			$return = $types[0]->name;
		}
		else	{
			$return = __( 'No event type set', 'mobile-dj-manager' );
		}
					
		return apply_filters( 'mdjm_event_type', $return, $this->ID );
	} // get_type
	
	/**
	 * Retrieve the guest playlist access code.
	 *
	 * @since	1.3
	 * @return	str
	 */
	public function get_playlist_code() {
		if ( ! isset( $this->playlist_code ) ) {
			$this->playlist_code = get_post_meta( $this->ID, '_mdjm_playlist_access', true );
		}
		
		return apply_filters( 'mdjm_guest_playlist_code', $this->playlist_code, $this->ID );
	} // get_playlist_code
	
	/**
	 * Determine if the playlist is enabled.
	 *
	 * @since	1.3
	 * @return	bool
	 */
	public function playlist_is_enabled() {
		$return = false;
		
		if ( 'Y' == get_post_meta( $this->ID, '_mdjm_event_playlist', true ) )	{
			$return = true;
		}
					
		return apply_filters( 'mdjm_playlist_status', $return, $this->ID );
	} // is_playlist_enabled
	
	/**
	 * Determine if the playlist is open.
	 *
	 * @since	1.3
	 * @return	bool
	 */
	public function playlist_is_open() {
		// Playlist disabled for this event
		if( ! $this->playlist_is_enabled() )	{
			return false;
		}
		
		$close = mdjm_get_option( 'close', false );
		
		// Playlist never closes
		if( empty( $close ) )	{
			return true;
		}
		
		return time() > ( $date - ( $close * DAY_IN_SECONDS ) ) ? false : true;
	} // is_playlist_open
	
} // class MDJM_Event