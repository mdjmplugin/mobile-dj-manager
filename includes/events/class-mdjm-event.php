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
	 * The total income
	 *
	 * @since	1.3
	 */
	private $income;
	
	/**
	 * The total outgoings
	 *
	 * @since	1.3
	 */
	private $outgoings;
	
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
	 * @param 	arr		$data Array of attributes for an event. See $defaults.
	 * @return	mixed	false if data isn't passed and class not instantiated for creation, or New Event ID
	 */
	public function create( $data = array(), $meta = array() ) {

		if ( $this->id != 0 ) {
			return false;
		}

		remove_action( 'save_post_mdjm-event', 'mdjm_save_event_post', 10, 3 );

		$defaults = array(
			'post_type'    => 'mdjm-event',
			'post_author'  => 1,
			'post_content' => '',
			'post_status'  => 'mdjm-enquiry',
			'post_title'   => __( 'New Event', 'mobile-dj-manager' ),
		);
		
		$default_meta = array(
			'_mdjm_event_date'               => date( 'Y-m-d' ),
			'_mdjm_event_dj'                 => ! mdjm_get_option( 'employer' ) ? 1 : 0,
			'_mdjm_event_playlist_access'    => mdjm_generate_playlist_guest_code(),
			'_mdjm_event_playlist'           => mdjm_get_option( 'enable_playlists' ) ? 'Y' : 'N',
			'_mdjm_event_contract'           => mdjm_get_default_event_contract(),
			'_mdjm_event_cost'               => 0,
			'_mdjm_event_deposit'            => 0,
			'_mdjm_event_deposit_status'     => __( 'Due', 'mobile-dj-manager' ),
			'_mdjm_event_balance_status'     => __( 'Due', 'mobile-dj-manager' ),
			'mdjm_event_type'                => mdjm_get_option( 'event_type_default' ),
			'mdjm_enquiry_source'            => mdjm_get_option( 'enquiry_source_default' )
		);

		$data = wp_parse_args( $data, $defaults );
		$meta = wp_parse_args( $meta, $default_meta );

		do_action( 'mdjm_event_pre_create', $data, $meta );		

		$id	= wp_insert_post( $data, true );

		$event = WP_Post::get_instance( $id );

		if ( $event )	{
			
			if ( ! empty( $meta['mdjm_event_type'] ) )	{
				mdjm_set_event_type( $event->ID, $meta['mdjm_event_type'] );
				$meta['_mdjm_event_name'] = get_term( $meta['mdjm_event_type'], 'event-types' )->name;
				$meta['_mdjm_event_name'] = apply_filters( 'mdjm_event_name', $meta['_mdjm_event_name'], $id );
			}
			
			if ( ! empty( $meta['mdjm_enquiry_source'] ) )	{
				mdjm_set_enquiry_source( $event->ID, $meta['mdjm_enquiry_source'] );
			}
						
			if ( ! empty( $meta['_mdjm_event_start'] ) && ! empty( $meta['_mdjm_event_finish'] ) )	{
				
				if( date( 'H', strtotime( $meta['_mdjm_event_finish'] ) ) > date( 'H', strtotime( $meta['_mdjm_event_start'] ) ) )	{
					$meta['_mdjm_event_end_date'] = $meta['_mdjm_event_date'];
				} else	{
					$meta['_mdjm_event_end_date'] = date( 'Y-m-d', strtotime( '+1 day', strtotime( $meta['_mdjm_event_date'] ) ) );
				}
			}
			
			if ( ! empty( $meta['_mdjm_event_package'] ) )	{
				$meta['_mdjm_event_cost'] += mdjm_get_package_cost( $meta['_mdjm_event_package'] );
			}
			
			if ( ! empty( $meta['_mdjm_event_addons'] ) )	{
				foreach( $meta['_mdjm_event_addons'] as $addon )	{
					$meta['_mdjm_event_cost'] += mdjm_get_addon_cost( $addon );
				}
			}
			
			if ( empty( $meta['_mdjm_event_deposit'] ) )	{
				$meta['_mdjm_event_deposit'] = mdjm_calculate_deposit( $meta['_mdjm_event_cost'] );
			}
			
			mdjm_update_event_meta( $event->ID, $meta );
			
			wp_update_post(
				array(
					'ID'         => $id,
					'post_title' => mdjm_get_event_contract_id( $id ),
					'post_name'  => mdjm_get_event_contract_id( $id )
				)
			);
			
		}

		do_action( 'mdjm_event_post_create', $id, $data );
		
		add_action( 'save_post_mdjm-event', 'mdjm_save_event_post', 10, 3 );

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
	 * Retrieve all the events employees
	 *
	 * @since	1.3
	 * @return	arr		Event employee user ID's, role and wages.
	 */
	public function get_all_employees() {
		global $wp_roles;
		
		if ( ! isset( $this->employees ) )	{
		
			if( ! isset( $this->employee_id ) )	{
				$this->get_employee();
			}
			
			$employees = array();
			
			if ( ! empty( $this->employee_id ) )	{
				
				$payment_status = get_post_meta( $this->ID, '_mdjm_event_dj_payment_status', true );
				
				$employees[ $this->employee_id ] = array( 
					'role_slug'	  => 'dj',
					'role'		   => translate_user_role( $wp_roles->roles['dj']['name'] ),
					'wage'		   => mdjm_format_amount( get_post_meta( $this->ID, '_mdjm_event_dj_wage', true ) ),
					'payment_status' => $payment_status,
					'txn_id'         => ! empty( $payment_status['txn_id'] ) ? $payment_status['txn_id'] : ''
				);
				
			}
			
			$employees_data = get_post_meta( $this->ID, '_mdjm_event_employees_data', true );
			
			if ( ! empty( $employees_data ) )	{
				
				foreach( $employees_data as $employee_data )	{
					
					$employees[ $employee_data['id'] ] = array(
						'role_slug'      => $employee_data['role'],
						'role'           => translate_user_role( $wp_roles->roles[ $employee_data['role'] ]['name'] ),
						'wage'           => ! empty( $employee_data['wage'] ) ? $employee_data['wage'] : 0,
						'payment_status' => ! empty( $employee_data['payment_status'] ) ? $employee_data['payment_status'] : 'unpaid',
						'txn_id'         => ! empty( $employee_data['txn_id'] ) ? $employee_data['txn_id'] : ''
					);
					
				}
				
			}
			
		}

		$this->employees = $employees;
		
		return $this->employees;
	} // get_all_employees
	
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
			$contract = get_post_meta( $this->ID, '_mdjm_event_signed_contract', true );
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
			$signed_contract_id = get_post_meta( $this->ID, '_mdjm_event_signed_contract', true );
			
			if( ! empty( $signed_contract_id ) && mdjm_contract_exists( $signed_contract_id ) && ( $this->post_status == 'mdjm-approved' || $this->post_status == 'mdjm-completed' ) )	{
				
				return apply_filters( 'mdjm_get_contract_status', $signed_contract_id, $this->ID );
				
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
		
		/**
		 * Override the event date.
		 *
		 * @since	1.3
		 *
		 * @param	str		$date The event price.
		 * @param	str		$date The event date.
		 */
		return apply_filters( 'mdjm_get_event_date', $this->date, $this->ID );
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
	 * Retrieve the event name
	 *
	 * @since	1.3.7
	 * @return	str
	 */
	public function get_name() {
		$name = get_post_meta( $this->ID, '_mdjm_event_name', true );
		
		/**
		 * Override the event date.
		 *
		 * @since	1.3
		 *
		 * @param	str		$date The event price.
		 * @param	str		$date The event date.
		 */
		return apply_filters( 'mdjm_get_event_name', $name, $this->ID );
	} // get_name

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

				$this->deposit = $this->deposit;

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
	 * Retrieve the remaining event deposit
	 *
	 * @since 	1.3
	 * @return	str
	 */
	public function get_remaining_deposit()	{
		
		$income = $this->get_total_income();
		
		if ( $income >= $this->get_deposit() )	{
			$return = '0';
		} else	{
			$return = ( $this->get_deposit() - $income );
		}
		
		return apply_filters( 'mdjm_get_remaining_deposit', $return, $this->ID );
		
	} // get_remaining_deposit
	
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
			
				if ( empty( $this->deposit ) || $this->deposit == '0.00' )	{
					$this->deposit_status = __( 'Paid', 'mobile-dj-manager' );
				} else	{
					$this->deposit_status = __( 'Due', 'mobile-dj-manager' );
				}
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
				
				$this->balance = ( $this->get_price() - $this->income );
				
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
				
				$this->income = mdjm_sanitize_amount( $rcvd );
				
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
	 * Retrieve the total outgoings for this event
	 *
	 * @since 	1.3
	 * @return	str
	 */
	public function get_total_outgoings()	{
		if ( ! isset( $this->outgoings ) )	{
			
			$out = MDJM()->txns->get_transactions( $this->ID, 'mdjm-expenditure' );
			
			if ( ! empty ( $out ) )	{
				
				$this->outgoings = $out;
				
			} else	{
				
				$this->outgoings = '0.00';
				
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
		return apply_filters( 'get_event_outgoings', $this->outgoings, $this->ID );
	} // get_total_outgoings
	
	/**
	 * Retrieve the total profit for this event
	 *
	 * @since 	1.3
	 * @return	str
	 */
	public function get_total_profit()	{

		$profit = ( $this->get_total_income() - $this->get_total_outgoings() );
		
		/**
		 * Override the profit for this event.
		 *
		 * @since	1.3
		 *
		 * @param	str		$profit		The profit for the event.
		 * @param	str|int	$id			The event ID.
		 */
		return apply_filters( 'get_event_profit', $profit, $this->ID );
	} // get_total_profit
	
	/**
	 * Retrieve the total wages payable event
	 *
	 * @since 	1.3
	 * @return	str
	 */
	public function get_wages_total()	{
		
		if ( ! isset( $this->employees ) )	{
			$this->get_all_employees();
		}
		
		$wages = mdjm_format_amount( 0 );
		
		if( ! empty( $this->employees ) )	{
			foreach( $this->employees as $employee => $employee_data )	{
				$wages += $employee_data['wage'];
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
		return apply_filters( 'get_wages_total', mdjm_format_amount( $wages ), $this->ID, $this->employees );
	} // get_wages_total
	
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
		
		$date = get_post_meta( $this->ID, '_mdjm_event_date', true );
			
		return time() > ( strtotime( $date ) - ( $close * DAY_IN_SECONDS ) ) ? false : true;
	} // is_playlist_open
	
} // class MDJM_Event