<?php
/**
 * MDJM Rest API
 *
 * Provides an API REST interface.
 *
 * The primary purpose of this class is for availability checking and
 * event queries.
 *
 * @package     MDJM
 * @subpackage  Classes/API
 * @copyright   Copyright (c) 2016, Mike Howard
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since		1.4
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * MDJM_API Class
 *
 * Renders API returns as a JSON array
 *
 * @since	1.4
 */
class MDJM_API 	{

	/**
	 * Latest API Version
	 */
	const VERSION = 1;

	/**
	 * Namespace.
	 */
	public $namespace;

	/**
	 * Log API requests?
	 *
	 * @var		bool
	 * @access	public
	 * @since	1.4
	 */
	public $log_requests = true;

	/**
	 * Request data.
	 *
	 * @var		arr
	 * @access	public
	 * @since	1.4
	 */
	private $request = array();

	/**
	 * Is this a valid request?
	 *
	 * @var		bool
	 * @access	private
	 * @since	1.4
	 */
	private $is_valid_request = false;

	/**
	 * User ID Performing the API Request.
	 *
	 * @var		int
	 * @access	private
	 * @since	1.4
	 */
	public $user_id = 0;

	/**
	 * Response data to return
	 *
	 * @var		array
	 * @access	private
	 * @since	1.4
	 */
	private $data = array();

	/**
	 * Setup the MDJM API.
	 *
	 * @since	1.4
	 */
	public function __construct()	{
		$this->namespace = 'mdjm/v' . self::VERSION;
		add_action( 'rest_api_init', array( $this, 'register_endpoints' ) );
	} // __construct

	/**
	 * Register the API endpoints.
	 *
	 * @since	1.4
	 * @return	arr
	 */
	public function register_endpoints()	{

		$endpoints = $this->define_endpoints();

		if ( $endpoints )	{

			foreach( $endpoints as $base => $args )	{
				register_rest_route( $this->namespace, $base, $args, false );
			}

		}

	} // register_endpoints

	/**
	 * Define the API endpoints.
	 *
	 * @since	1.4
	 * @return	arr
	 */
	public function define_endpoints()	{

		$endpoints = array(
			// For checking agency availability
			'/availability/' => array(
				'methods'  => array( WP_REST_Server::READABLE ),
				'callback' => array( $this, 'process_request' ),
			),
			// For retrieving and managing affiliated events
			'/events/' => array(
				'methods'  => array( WP_REST_Server::READABLE ),
				'callback' => array( $this, 'process_request' ),
			)
		);

		return apply_filters( 'mdjm_api_endpoints', $endpoints );

	} // define_endpoints

	/**
	 * Process API requests.
	 *
	 * @since	1.4
	 * @param	arr		$request	API Request data.
	 * @return	void
	 */
	public function process_request( WP_REST_Request $request )	{

		$start = microtime( true ); // Start time for logging.

		$this->request = $request;

		$this->validate_request();

	} // process_request

	/**
	 * Validate the current request.
	 *
	 * @access	private
	 * @since	1.4
	 * @return	void
	 */
	private function validate_request()	{

		if ( empty( $this->request['api_key'] ) || empty( $this->request['token'] ) )	{

			$this->missing_auth();

		} elseif ( ! ( $user = $this->get_user() ) )	{

			$this->invalid_key();

		} else	{

			$public = $this->request>get_param( 'api_key' );
			$token  = $this->request->get_param( 'token' );
			$secret = $this->get_user_secret_key( $user );

			if ( hash_equals( md5( $secret . $public ), $token ) ) {
				$this->is_valid_request = true;
			} else {
				$this->invalid_auth();
			}

		}

	} // validate_request

	/**
	 * Sends a response to the API request.
	 *
	 * @since	1.4
	 * @param	int		$status_code	Status code.
	 * @return	void
	 */
	public function output( $status_code = 200 )	{

		$response = new WP_REST_Response( $this->data );
		$response->set_status( $status_code );
		$response->header( 'Content-type', 'application/json' );
		
		return $response;
	} // output

	/**
	 * Retrieve a user ID from the API key provided.
	 *
	 * @since	1.4
	 * @global	obj		$wpdb
	 * @param	str		$api_key	The API from which to retrieve the user.
	 * @return	void
	 */
	public function get_user()	{
		global $wpdb;

		if ( empty( $key ) ) {
			$key = $this->request->get_param( 'api_key' );
		}

		if ( empty( $key ) ) {
			return false;
		}

		$user = get_transient( md5( 'mdjm_api_user_' . $key ) );

		if ( false === $user ) {
			$user = $wpdb->get_var( $wpdb->prepare( "SELECT user_id FROM $wpdb->usermeta WHERE meta_key = 'mdjm_user_public_key' AND meta_value = %s LIMIT 1", $key ) );

			set_transient( md5( 'mdjm_api_user_' . $key ) , $user, DAY_IN_SECONDS );
		}

		if ( $user != NULL ) {
			$this->user_id = $user;
			return $user;
		}

		return false;

	} // get_user

	/**
	 * Prepare and execute an availability check.
	 *
	 * @since	1.0
	 * @param	arr		$request	API Request data.
	 * @return	bool	True if this agency is available, otherwise false.
	 */
	public function availability_check( WP_REST_Request $request )	{
	
		$return = array(
			'result' => 'error',
			'data'   => array(
				'availability' => false,
				'date' => $request['date']
			)
		);
	
		$date      = $request['date'];
		$employees = isset ( $request['employees'] ) ? $request['employees'] : '';
		$roles     = isset ( $request['roles'] )     ? $request['roles']     : '';
	
		$result = mdjm_do_availability_check( $date, $employees, $roles );
	
		if ( $result )	{
			$return['result'] = 'success';
	
			if( ! empty( $result['available'] ) )	{
				$return['data']['availability'] = true;
			}
		}
	
		$response = new WP_REST_Response( $return );
		$response->header( 'X-Prev-MDJM-Availability', $date );
	
		return $response;
	
	} // availability_check

	/**
	 * Displays an authentication error if api key is invalid.
	 *
	 * @since	1.4
	 * @access	private
	 * @uses	MDJM_API::output()
	 * @return	void
	 */
	private function invalid_key() {
		$error = array();
		$error['error'] = __( 'Invalid API key.', 'mobile-dj-manager' );

		$this->data = $error;
		$this->output( 403 );
	} // invalid_key

	/**
	 * Displays a missing authentication error if required paramaters are not provided.
	 *
	 * @since	1.4
	 * @access	private
	 * @uses	MDJM_API::output()
	 * @return	void
	 */
	private function missing_auth() {
		$error = array();
		$error['error'] = __( 'No API key provided.', 'mobile-dj-manager' );

		$this->data = $error;
		$this->output( 401 );
	} // missing_auth

	/**
	 * Displays an authentication error if credentials are invalid.
	 *
	 * @since	1.4
	 * @access	private
	 * @uses	MDJM_API::output()
	 * @return	void
	 */
	private function invalid_auth() {
		$error = array();
		$error['error'] = __( 'Authentication failed.', 'mobile-dj-manager' );

		$this->data = $error;
		$this->output( 403 );
	} // invalid_auth

} // MDJM_API
