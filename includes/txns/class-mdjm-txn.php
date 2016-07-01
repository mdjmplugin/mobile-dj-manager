<?php
/**
 * Transaction Object
 *
 * @package     MDJM
 * @subpackage  Transactions
 * @copyright   Copyright (c) 2016, Mike Howard
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.3
*/

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) )
	exit;

/**
 * MDJM_Txn Class
 *
 * @since	1.3
 */
class MDJM_Txn {

	/**
	 * The transaction ID
	 *
	 * @since	1.3
	 */
	public $ID = 0;

	/**
	 * The transaction date
	 *
	 * @since	1.3
	 */
	public $date;
	
	/**
	 * The transaction price
	 *
	 * @since	1.3
	 */
	public $price = 0;
	
	/**
	 * The transaction recipient
	 *
	 * @since	1.3
	 */
	public $recipient_id = 0;
		
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
	public $post_status = 'mdjm-income';
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
		$txn = WP_Post::get_instance( $_id );
		
		return $this->setup_txn( $txn );
	} // __construct

	/**
	 * Given the event data, let's set the variables
	 *
	 * @since	1.3
	 * @param 	obj		$event	The Event Object
	 * @return	bool			If the setup was successful or not
	 */
	private function setup_txn( $txn ) {
		if( ! is_object( $txn ) ) {
			return false;
		}

		if( ! is_a( $txn, 'WP_Post' ) ) {
			return false;
		}

		if( 'mdjm-transaction' !== $txn->post_type ) {
			return false;
		}
		
		foreach ( $txn as $key => $value ) {
			switch ( $key ) {
				default:
					$this->$key = $value;
					break;
			}
		}
		
		$this->get_price();
		$this->get_recipient_id();
				
		return true;
	} // setup_txn
	
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
			return new WP_Error( 'mdjm-txn-invalid-property', sprintf( __( "Can't get property %s", 'mobile-dj-manager' ), $key ) );
		}
	} // __get
	
	/**
	 * Creates a transaction
	 *
	 * @since 	1.3
	 * @param 	arr		$data Array of attributes for a transaction. See $defaults.
	 * @return	mixed	false if data isn't passed and class not instantiated for creation, or New Transaction ID
	 */
	public function create( $data = array(), $meta = array() ) {
		
		if ( $this->id != 0 ) {
			return false;
		}

		remove_action( 'save_post_mdjm-transaction', 'mdjm_save_txn_post', 10, 3 );

		$default_data = array(
			'post_type'      => 'mdjm-transaction',
			'post_status'    => 'mdjm-income',
			'post_title'     => __( 'New Transaction', 'mobile-dj-manager' ),
			'post_content'   => ''
		);

		$default_meta = array(
			'_mdjm_txn_source'     => mdjm_get_option( 'default_type', __( 'Cash' ) ),
			'_mdjm_txn_currency'   => mdjm_get_currency(),
			'_mdjm_txn_status'     => 'Pending'
		);
		
		$data = wp_parse_args( $data, $default_data );
		$meta = wp_parse_args( $meta, $default_meta );

		do_action( 'mdjm_pre_txn_create', $data, $meta );

		$id = wp_insert_post( $data, true );
		
		if ( is_wp_error( $id ) )	{
			MDJM()->debug->log_it( 'ERROR: ' . $id->get_error_message() );
		}

		$txn = WP_Post::get_instance( $id );
		
		if ( $txn )	{
			
			mdjm_update_txn_meta( $txn->ID, $meta );
			
			wp_update_post(
				array(
					'ID'         => $id,
					'post_title' => mdjm_get_option( 'event_prefix' ) . $id,
					'post_name'  => mdjm_get_option( 'event_prefix' ) . $id
				)
			);
		}

		do_action( 'mdjm_post_txn_create', $id, $data, $meta );

		add_action( 'save_post_mdjm-transaction', 'mdjm_save_txn_post', 10, 3 );

		return $this->setup_txn( $txn );

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
	 * Retrieve the transaction date
	 *
	 * @since	1.3
	 * @return	str Y-m-d H:i:s
	 */
	public function get_date() {
		return $this->post_date;
	} // get_date
	
	/**
	 * Retrieve the transaction price
	 *
	 * @since	1.3
	 * @return	str Y-m-d H:i:s
	 */
	public function get_price() {
		if ( empty( $this->price ) )	{
			$this->price = get_post_meta( $this->ID, '_mdjm_txn_total', true );
		}
		
		return $this->price;
	} // get_price
	
	/**
	 * Retrieve the transaction recipient
	 *
	 * @since	1.3
	 * @return	str Y-m-d H:i:s
	 */
	public function get_recipient_id() {

		if ( empty( $this->recipient_id ) )	{
		
			if ( 'mdjm-income' == get_post_status( $this->ID ) )	{
				$this->recipient_id = get_post_meta( $this->ID, '_mdjm_payment_from', true );
			} else	{
				$this->recipient_id = get_post_meta( $this->ID, '_mdjm_payment_to', true );
			}

		}
		
		return apply_filters( 'mdjm_get_recipient_id', $this->recipient_id );
	} // get_recipient_id
	
	/**
	 * Retrieve the transaction type.
	 *
	 * @since	1.3
	 * @return	bool
	 */
	public function get_type() {
		$types = wp_get_object_terms( $this->ID, 'transaction-types' );
			
		if( !empty( $types ) )	{
			$return = $types[0]->name;
		}
		else	{
			$return = __( 'No transaction type set', 'mobile-dj-manager' );
		}
					
		return apply_filters( 'mdjm_transaction_type', $return, $this->ID );
	} // get_type
	
} // class MDJM_Txn
