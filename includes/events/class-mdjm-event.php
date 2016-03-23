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
	 * The event price
	 *
	 * @since	1.3
	 */
	private $price;
	
	/**
	 * The event deposit
	 *
	 * @since	1.3
	 */
	private $deposit;
	
	/**
	 * The deposit status
	 *
	 * @since	1.3
	 */
	private $deposit_status;
	
	/**
	 * The event balance
	 *
	 * @since	1.3
	 */
	private $balance;
	
	/**
	 * The balance status
	 *
	 * @since	1.3
	 */
	private $balance_status;
		
	/**
	 * The remaining balance
	 *
	 * @since	1.3
	 */
	private $income;
	
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
		
		return $this->client;
	} // get_client
	
	/**
	 * Retrieve the events primary employee
	 *
	 * @since	1.3
	 * @return	int
	 */
	public function get_employee() {
		if( ! isset( $this->employee_id ) )	{
			$this->employee_id = get_post_meta( $this->ID, '_mdjm_event_dj', true );
		}
		
		return $this->employee_id;
	} // get_employee
	
	/**
	 * Retrieve the event contract.
	 *
	 * @since	1.3
	 * @return	int
	 */
	public function get_contract() {
		$status = $this->get_contract_status();
		
		if( ! $status || empty( $status ) )	{
			$contract = get_post_meta( $this->ID, '_mdjm_event_contract', true );
		}
		else	{
			$contract = get_post_meta( $this->ID, '_mdjm_signed_contract', true );
		}
		
		return apply_filters( 'mdjm_event_contract', $contract, $this->ID );
	} // get_contract
	
	/**
	 * Retrieve the event contract status.
	 *
	 * @since	1.3
	 * @return	int|bool
	 */
	public function get_contract_status() {		
		if( isset( $this->ID ) )	{
			$signed_contract_id = get_post_meta( $this->ID, '_mdjm_signed_contract', true );
			
			if( $signed_contract_id && mdjm_contract_exists( $signed_contract_id ) && ( $this->post_status == 'mdjm-approved' || $this->post_status == 'mdjm-completed' ) )	{
				
				apply_filters( 'mdjm_get_contract_status', $signed_contract_id, $this->ID );
				
			} else	{
				
				return false;
				
			}
		}
		
		return false;
	} // get_contract_status
	
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
		
		return $this->date;
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
	 * Retrieve the event price
	 *
	 * @since 	1.3
	 * @return	float
	 */
	public function get_price() {

		if ( ! isset( $this->price ) ) {

			$this->price = get_post_meta( $this->ID, '_mdjm_event_cost', true );

			if ( $this->price ) {

				$this->price = mdjm_sanitize_amount( $this->price );

			} else {

				$this->price = 0;

			}
			
		}

		/**
		 * Override the event price.
		 *
		 * @since	1.3
		 *
		 * @param	str		$price The event price.
		 * @param	str|int	$id The event ID.
		 */
		return apply_filters( 'mdjm_get_event_price', $this->price, $this->ID );
	} // get_price
	
	/**
	 * Retrieve the event deposit
	 *
	 * @since 	1.3
	 * @return	float
	 */
	public function get_deposit() {

		if ( ! isset( $this->deposit ) ) {

			$this->deposit = get_post_meta( $this->ID, '_mdjm_event_deposit', true );

			if ( $this->deposit ) {

				$this->deposit = mdjm_sanitize_amount( $this->deposit );

			} else {

				$this->deposit = 0;

			}
			
		}

		/**
		 * Override the event deposit.
		 *
		 * @since	1.3
		 *
		 * @param	str		$deposit	The event deposit.
		 * @param	str|int	$id			The event ID.
		 */
		return apply_filters( 'mdjm_get_event_deposit', $this->deposit, $this->ID );
	} // get_deposit
	
	/**
	 * Retrieve the event deposit status
	 *
	 * @since 	1.3
	 * @return	str
	 */
	public function get_deposit_status() {

		if ( ! isset( $this->deposit_status ) ) {

			$this->deposit_status = get_post_meta( $this->ID, '_mdjm_event_deposit_status', true );

			if ( ! $this->deposit_status || $this->deposit_status != 'Paid' || $this->get_deposit() > 0 ) {

				$this->deposit_status = __( 'Due', 'mobile-dj-manager' );
				
				if ( $this->get_total_income() >= $this->get_deposit() )	{
					
					$this->deposit_status = __( 'Paid', 'mobile-dj-manager' );
					
				}

			} else	{
			
				$this->deposit_status = __( 'Due', 'mobile-dj-manager' );
				
			}
			
		}

		/**
		 * Override the event deposit status.
		 *
		 * @since	1.3
		 *
		 * @param	str		$deposit_status	The event deposit_status.
		 * @param	str|int	$id				The event ID.
		 */
		return apply_filters( 'mdjm_get_event_deposit_status', $this->deposit_status, $this->ID );
	} // get_deposit_status
	
	/**
	 * Retrieve the event balance status
	 *
	 * @since 	1.3
	 * @return	str
	 */
	public function get_balance_status() {

		if ( ! isset( $this->balance_status ) ) {

			$this->balance_status = get_post_meta( $this->ID, '_mdjm_event_balance_status', true );

			if ( ! $this->balance_status || $this->balance_status != 'Paid' || $this->get_price() > 0 ) {

				$this->balance_status = __( 'Due', 'mobile-dj-manager' );

			} else	{
			
				if ( $this->get_total_income() >= $this->get_price() )	{
					
					$this->balance_status = __( 'Paid', 'mobile-dj-manager' );
					
				} else	{
			
					$this->balance_status = __( 'Due', 'mobile-dj-manager' );
					
				}
				
			}
			
		}

		/**
		 * Override the event balance status.
		 *
		 * @since	1.3
		 *
		 * @param	str		$balance_status	The event balance_status.
		 * @param	str|int	$id				The event ID.
		 */
		return apply_filters( 'mdjm_get_event_balance_status', $this->balance_status, $this->ID );
	} // get_balance_status
	
	/**
	 * Retrieve the event balance
	 *
	 * @since 	1.3
	 * @return	str
	 */
	public function get_balance() {

		if ( ! isset( $this->balance ) ) {
			
			$income = $this->get_total_income();
			
			if ( ! empty( $this->income ) && $this->income != '0.00' )	{
				
				$this->balance = mdjm_sanitize_amount( ( $this->get_price() - $this->income ) );
				
			} else	{
				
				$this->balance = $this->get_price();
				
			}
			
		}

		/**
		 * Override the event balance.
		 *
		 * @since	1.3
		 *
		 * @param	str		$income		The event balance.
		 * @param	str|int	$id			The event ID.
		 */
		return apply_filters( 'mdjm_get_event_balance', $this->balance, $this->ID );
	} // get_balance
	
	/**
	 * Retrieve the total income for this event
	 *
	 * @since 	1.3
	 * @return	str
	 */
	public function get_total_income()	{
		if ( ! isset( $this->income ) )	{
			
			$rcvd = MDJM()->txns->get_transactions( $this->ID, 'mdjm-income' );
			
			if ( ! empty ( $rcvd ) )	{
				
				$this->income = $rcvd;
				
			} else	{
				
				$this->income = '0.00';
				
			}
		}
		
		/**
		 * Override the income for this event.
		 *
		 * @since	1.3
		 *
		 * @param	str		$income		The income for the event.
		 * @param	str|int	$id			The event ID.
		 */
		return apply_filters( 'get_event_income', $this->income, $this->ID );
	} // get_total_income
	
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