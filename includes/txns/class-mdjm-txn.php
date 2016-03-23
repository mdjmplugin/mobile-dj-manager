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
	private $date;
		
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
	 * @param 	arr		$data Array of attributes for a transaction
	 * @return	mixed	false if data isn't passed and class not instantiated for creation, or New Transaction ID
	 */
	public function create( $data = array() ) {
		if ( $this->id != 0 ) {
			return false;
		}

		$defaults = array(
			'post_type'   => 'mdjm-transaction',
			'post_status' => 'mdjm-income',
			'post_title'  => __( 'New Transaction', 'mobile-dj-manager' )
		);

		$args = wp_parse_args( $data, $defaults );

		do_action( 'mdjm_txn_pre_create', $args );

		$id = wp_insert_post( $args, true );

		$txn = WP_Post::get_instance( $id );

		do_action( 'mdjm_txn_post_create', $id, $args );

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
	
	
	
} // class MDJM_Txn