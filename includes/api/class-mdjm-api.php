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
if ( ! defined( 'ABSPATH' ) )
	exit;

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
	 * Is this a valid user?
	 *
	 * @var		bool
	 * @access	private
	 * @since	1.4
	 */
	private $is_valid_user = false;

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
		

		add_action( 'rest_api_init',         array( $this, 'register_endpoints' ) );
		add_action( 'mdjm-process_api_key',  array( $this, 'process_api_key'    ) );
		add_action( '/mdjm/v1/availability', array( $this, 'availability_check' ) );
		add_action( '/mdjm/v1/client',       array( $this, 'get_client'         ) );
		add_action( '/mdjm/v1/employee',     array( $this, 'get_employee'       ) );
		add_action( '/mdjm/v1/event',        array( $this, 'get_event'          ) );
		add_action( '/mdjm/v1/events',       array( $this, 'list_events'        ) );
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
				'methods'      => array( WP_REST_Server::READABLE ),
				'callback'     => array( $this, 'process_request' ),
				'require_auth' => false
			),
			// Single client
			'/client/' => array(
				'methods'      => array( WP_REST_Server::READABLE, WP_REST_Server::CREATABLE ),
				'callback'     => array( $this, 'process_request' ),
				'require_auth' => true
			),
			// Single employee
			'/employee/' => array(
				'methods'      => array( WP_REST_Server::READABLE, WP_REST_Server::CREATABLE ),
				'callback'     => array( $this, 'process_request' ),
				'require_auth' => true
			),
			// Single event
			'/event/' => array(
				'methods'      => array( WP_REST_Server::READABLE, WP_REST_Server::CREATABLE ),
				'callback'     => array( $this, 'process_request' ),
				//'require_auth' => true
			),
			// Multiple events
			'/events/' => array(
				'methods'      => array( WP_REST_Server::READABLE, WP_REST_Server::CREATABLE ),
				'callback'     => array( $this, 'process_request' ),
				//'require_auth' => true
			)
		);

		return apply_filters( 'mdjm_api_endpoints', $endpoints );

	} // define_endpoints

	/**
	 * Validate the current user.
	 *
	 * @access	private
	 * @since	1.4
	 * @return	void
	 */
	private function validate_user()	{

		$endpoints = $this->define_endpoints();
		$endpoint  = trailingslashit( str_replace( '/' . $this->namespace, '', $this->request->get_route() ) );

		if ( array_key_exists( 'require_auth', $endpoints[ $endpoint ] ) && false === $endpoints[ $endpoint ]['require_auth'] )	{

			$this->is_valid_user = true;

		} elseif ( empty( $this->request['api_key'] ) || empty( $this->request['token'] ) )	{

			$this->missing_auth();

		} elseif ( ! ( $user = $this->get_user() ) )	{

			$this->invalid_key();

		} else	{

			$public = $this->request->get_param( 'api_key' );
			$token  = $this->request->get_param( 'token' );
			$secret = $this->get_user_secret_key( $user );

			if ( hash_equals( md5( $secret . $public ), $token ) ) {
				$this->is_valid_user = true;
			} else {
				$this->invalid_auth();
			}

		}

	} // validate_user

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
			$user = $wpdb->get_var( $wpdb->prepare( "SELECT user_id FROM $wpdb->usermeta WHERE meta_key = %s LIMIT 1", $key ) );

			set_transient( md5( 'mdjm_api_user_' . $key ) , $user, DAY_IN_SECONDS );
		}

		if ( $user != NULL ) {
			$this->user_id = $user;
			return $user;
		}

		return false;

	} // get_user

	/**
	 * Retrieve a user's public key.
	 *
	 * @since	1.4
	 * @global	obj		$wpdb
	 * @param	int		$user_id	User ID.
	 * @return	str
	 */
	public function get_user_public_key( $user_id = 0 )	{

		global $wpdb;

		if ( empty( $user_id ) ) {
			return '';
		}

		$cache_key       = md5( 'mdjm_api_user_public_key' . $user_id );
		$user_public_key = get_transient( $cache_key );

		if ( empty( $user_public_key ) )	{

			$user_public_key = $wpdb->get_var( $wpdb->prepare( "SELECT meta_key FROM $wpdb->usermeta WHERE meta_value = 'mdjm_user_public_key' AND user_id = %d", $user_id ) );

			set_transient( $cache_key, $user_public_key, HOUR_IN_SECONDS );

		}

		return $user_public_key;

	} // get_user_public_key

	/**
	 * Retrieve a user's secret key.
	 *
	 * @since	1.4
	 * @global	obj		$wpdb
	 * @param	int		$user_id	User ID.
	 * @return	str
	 */
	public function get_user_secret_key( $user_id = 0 )	{

		global $wpdb;

		if ( empty( $user_id ) ) {
			return '';
		}

		$cache_key       = md5( 'mdjm_api_user_secret_key' . $user_id );
		$user_secret_key = get_transient( $cache_key );

		if ( empty( $user_secret_key ) )	{

			$user_secret_key = $wpdb->get_var( $wpdb->prepare( "SELECT meta_key FROM $wpdb->usermeta WHERE meta_value = 'mdjm_user_secret_key' AND user_id = %d", $user_id ) );

			set_transient( $cache_key, $user_secret_key, HOUR_IN_SECONDS );

		}

		return $user_secret_key;

	} // get_user_secret_key

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
		$error['error'] = __( 'No API and/or token key provided.', 'mobile-dj-manager' );

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

	/**
	 * Displays a missing parameters error if required paramaters are not provided.
	 *
	 * @since	1.4
	 * @access	private
	 * @uses	MDJM_API::output()
	 * @param	str|arr		$params		Required parameters.
	 * @return	void
	 */
	private function missing_params( $params ) {
		$error = array();
		$error['error'] = sprintf( 
			__( 'Not all required parameters were provided. Missing: %s', 'mobile-dj-manager' ),
			is_array( $params ) ? implode( ', ', $params ) : $params
		);

		$this->data = $error;
		$this->output( 401 );
	} // missing_params

	/**
	 * Displays a permissions error if required permissions are not set.
	 *
	 * @since	1.4
	 * @access	private
	 * @uses	MDJM_API::output()
	 * @return	void
	 */
	private function no_permsission() {
		$error = array();
		$error['error'] = __( 'You do not have appropriate permissions to perform this action', 'mobile-dj-manager' );

		$this->data = $error;
		$this->output( 403 );
	} // no_permsission

	/**
	 * Process an API key generation/revocation
	 *
	 * @access	public
	 * @since	1.4
	 * @param	arr		$args
	 * @return	void
	 */
	public function process_api_key( $args ) {

		if( ! isset ( $_REQUEST['api_nonce'] ) || ! wp_verify_nonce( $_REQUEST['api_nonce'], 'mdjm-api-nonce' ) ) {

			wp_die( __( 'Nonce verification failed', 'mobile-dj-manager' ), __( 'Error', 'mobile-dj-manager' ), array( 'response' => 403 ) );

		}

		if ( empty( $args['user_id'] ) ) {
			wp_die( sprintf( __( 'User ID Required', 'mobile-dj-manager' ), $process ), __( 'Error', 'mobile-dj-manager' ), array( 'response' => 401 ) );
		}

		if ( is_numeric( $args['user_id'] ) ) {
			$user_id    = isset( $args['user_id'] ) ? absint( $args['user_id'] ) : get_current_user_id();
		} else {
			$userdata   = get_user_by( 'login', $args['user_id'] );
			$user_id    = $userdata->ID;
		}

		$process = isset( $args['mdjm_api_process'] ) ? strtolower( $args['mdjm_api_process'] ) : false;

		if ( ! mdjm_employee_can( 'manage_mdjm' ) )	{

			wp_die( sprintf( __( 'You do not have permission to %s API keys for this user', 'mobile-dj-manager' ), $process ), __( 'Error', 'mobile-dj-manager' ), array( 'response' => 403 ) );

		}

		switch( $process )	{

			case 'generate':

				if ( $this->generate_api_key( $user_id ) )	{

					delete_transient( 'mdjm-total-api-keys' );

					wp_redirect( add_query_arg(
						array( 'mdjm-message' => 'api-key-generated' ),
						'edit.php?post_type=mdjm-event&page=mdjm-tools&tab=api_keys'
					) );
					exit();

				} else {

					wp_redirect( add_query_arg(
						array( 'mdjm-message', 'api-key-failed' ),
						'edit.php?post_type=mdjm-event&page=mdjm-tools&tab=api_keys'
					) );
					exit();

				}

				break;

			case 'regenerate':
				$this->generate_api_key( $user_id, true );
				delete_transient( 'mdjm-total-api-keys' );

				wp_redirect( add_query_arg( 
					array( 'mdjm-message', 'api-key-regenerated' ),
					'edit.php?post_type=mdjm-event&page=mdjm-tools&tab=api_keys'
				) );
				exit();
				break;

			case 'revoke':
				$this->revoke_api_key( $user_id );
				delete_transient( 'mdjm-total-api-keys' );

				wp_redirect( add_query_arg(
					array( 'mdjm-message', 'api-key-revoked' ),
					'edit.php?post_type=mdjm-event&page=mdjm-tools&tab=api_keys'
				) );
				exit();
				break;

			default;
				break;

		}

	} // process_api_key

	/**
	 * Generate new API keys for a user.
	 *
	 * @access	public
	 * @since	1.4
	 * @param	int		$user_id	User ID the key is being generated for
	 * @param	bool	$regenerate	Regenerate the key for the user
	 * @return	bool	True if (re)generated succesfully, false otherwise.
	 */
	public function generate_api_key( $user_id = 0, $regenerate = false ) {

		if( empty( $user_id ) ) {
			return false;
		}

		$user = get_userdata( $user_id );

		if( ! $user ) {
			return false;
		}

		$public_key = $this->get_user_public_key( $user_id );
		$secret_key = $this->get_user_secret_key( $user_id );

		if ( empty( $public_key ) || $regenerate == true )	{

			$new_public_key = $this->generate_public_key( $user->user_email );
			$new_secret_key = $this->generate_private_key( $user->ID );

		} else {
			return false;
		}

		if ( $regenerate == true ) {
			$this->revoke_api_key( $user->ID );
		}

		update_user_meta( $user_id, $new_public_key, 'mdjm_user_public_key' );
		update_user_meta( $user_id, $new_secret_key, 'mdjm_user_secret_key' );

		return true;

	} // generate_api_key

	/**
	 * Revoke a users API keys.
	 *
	 * @access	public
	 * @since	1.4
	 * @param	int		$user_id	User ID of user to revoke key for
	 * @return	str
	 */
	public function revoke_api_key( $user_id = 0 ) {

		if( empty( $user_id ) ) {
			return false;
		}

		$user = get_userdata( $user_id );

		if( ! $user ) {
			return false;
		}

		$public_key = $this->get_user_public_key( $user_id );
		$secret_key = $this->get_user_secret_key( $user_id );

		if ( ! empty( $public_key ) )	{

			delete_transient( md5( 'mdjm_api_user_' . $public_key ) );
			delete_transient( md5( 'mdjm_api_user_public_key' . $user_id ) );
			delete_transient( md5( 'mdjm_api_user_secret_key' . $user_id ) );
			delete_user_meta( $user_id, $public_key );
			delete_user_meta( $user_id, $secret_key );

		} else {
			return false;
		}

		return true;

	} // revoke_api_key

	/**
	 * Generate and Save API key
	 *
	 * Generates the key requested by user_key_field and stores it in the database
	 *
	 * @access	public
	 * @since	1.4
	 * @param	int		$user_id
	 * @return	void
	 */
	public function update_key( $user_id ) {
		MDJM()->users->update_user_api_key( $user_id );
	} // update_key

	/**
	 * Generate the public key for a user
	 *
	 * @access	public
	 * @since	1.4
	 * @param	str		$user_email
	 * @return	str
	 */
	public function generate_public_key( $user_email = '' )	{

		$auth_key = defined( 'AUTH_KEY' ) ? AUTH_KEY : '';
		$public   = hash( 'md5', $user_email . $auth_key . date( 'U' ) );

		return $public;

	} // generate_public_key

	/**
	 * Generate the secret key for a user
	 *
	 * @access	public
	 * @since	1.4
	 * @param	int		$user_id
	 * @return	str
	 */
	public function generate_private_key( $user_id = 0 )	{

		$auth_key = defined( 'AUTH_KEY' ) ? AUTH_KEY : '';
		$secret   = hash( 'md5', $user_id . $auth_key . date( 'U' ) );

		return $secret;

	} // generate_private_key

	/**
	 * Retrieve the user's token
	 *
	 * @access	public
	 * @since	1.4
	 * @param	int		$user_id
	 * @return	str
	 */
	public function get_token( $user_id = 0 )	{
		return hash( 'md5', $this->get_user_secret_key( $user_id ) . $this->get_user_public_key( $user_id ) );
	} // get_token

	/**
	 * Retrieve the API version.
	 *
	 * @since	1.4
	 * @return	int
	 */
	public function get_version() {
		return self::VERSION;
	} // get_version

	/**
	 * Process API requests.
	 *
	 * @since	1.4
	 * @param	arr		$request	API Request data.
	 * @return	arr
	 */
	public function process_request( WP_REST_Request $request )	{

		$start = microtime( true ); // Start time for logging.
		$route = $request->get_route();

		$this->request = $request; // The request parameters.

		$this->validate_user();

		if ( $this->is_valid_user )	{
			$status_code = 200;
			$this->data = do_action( $route, $this );
		}

		return $this->data;

	} // process_request

	/**
	 * Sends a response to the API request.
	 *
	 * @since	1.4
	 * @param	int		$status_code	Status code.
	 * @return	void
	 */
	public function output( $status_code = 200 )	{

		$response = new WP_REST_Response( array( 'result' => true ) );
		$response->set_status( $status_code );
		$response->header( 'Content-type', 'application/json' );
		$response->set_data( $this->data );
		
		echo wp_json_encode( $response );
		
		die();
	} // output

	/**
	 * Prepare and execute an availability check.
	 *
	 * @since	1.0
	 * @return	void
	 */
	public function availability_check()	{

		$result   = false;
		$response = array();

		if ( ! isset( $this->request['date'] ) )	{
			$this->missing_params( 'date' );
		} else	{

			do_action( 'mdjm_before_api_availability_check', $this );

			$date      = $this->request['date'];
			$employees = isset ( $this->request['employees'] ) ? explode( ',', $this->request['employees'] ) : '';
			$roles     = isset ( $this->request['roles'] )     ? explode( ',', $this->request['roles'] )     : '';
		
			$result = mdjm_do_availability_check( $date, $employees, $roles );
			
		}
	
		if ( $result )	{

			if( ! empty( $result['available'] ) )	{
				$response['availability'] = array(
					'date'      => $date,
					'response'  => 'available',
					'employees' => $result['available']
				);
			} else	{
				$response['availability'] = array(
					'date'      => $date,
					'response'  => 'unavailable',
					'employees' => ''
				);
			}

		}

		do_action( 'mdjm_after_api_availability_check', $this );

		$this->data = array_merge( $this->data, $response );
		$this->output();

	} // availability_check

	/**
	 * Retrieve a client.
	 *
	 * @since	1.0
	 * @return	void
	 */
	public function get_client()	{

		$response = array();

		if ( ! isset( $this->request['client_id'] ) && ! isset( $this->request['client_email'] ) )	{
			$this->missing_params( 'client_id or client_email' );
		}

		do_action( 'mdjm_before_api_get_client', $this );

		if ( isset( $this->request['client_email'] ) && ! isset( $this->request['client_id'] ) )	{
			$field = 'email';
			$value = $this->request['client_email'];
		} else	{
			$field = 'id';
			$value = $this->request['client_id'];
		}

		$client = get_user_by( $field, $value );

		if ( ! user_can( $client->ID, 'client' ) && ! user_can( $client->ID, 'inactive_client' ) )	{
			$response['error'] = __( 'Error retrieving client.', 'mobile-dj-manager' );
			
			$this->data = array_merge( $response, $this->data );
			$this->output();
		}

		if ( ! $client )	{
			$response['error'] = __( 'Client could not be found.', 'mobile-dj-manager' );
			
			$this->data = array_merge( $response, $this->data );
			$this->output();
		}

		$events        = array();
		$client_events = mdjm_get_client_events( $client->ID );
		$next_event    = mdjm_get_clients_next_event( $client->ID );

		if ( $client_events )	{
			foreach( $client_events as $event )	{
				$events[ $event->ID ] = get_post_meta( $event->ID, '_mdjm_event_date', true );
			}
		}

		$response['client'] = array(
			'ID'         => $client->ID,
			'first_name' => $client->first_name,
			'last_name'  => $client->last_name,
			'email'      => $client->user_email,
			'last_login' => $client->last_login,
			'events'     => $events,
			'next_event' => array(
				'id'         => ! empty( $next_event ) ? $next_event[0]->ID : '',
				'date'       => ! empty( $next_event ) ? get_post_meta( $next_event[0]->ID, '_mdjm_event_date', true ) : '',
			)
		);

		$this->data = array_merge( $this->data, $response );

		do_action( 'mdjm_after_api_get_client', $this );

		$this->output();

	} // get_client

	/**
	 * Retrieve an employee.
	 *
	 * @since	1.0
	 * @return	void
	 */
	public function get_employee()	{

		global $wp_roles;

		if ( ! isset( $this->request['employee_id'] ) && ! isset( $this->request['employee_email'] ) )	{
			$this->missing_params( 'employee_id or employee_email' );
		}

		do_action( 'mdjm_before_api_get_employee', $this );

		if ( isset( $this->request['employee_email'] ) && ! isset( $this->request['employee_id'] ) )	{
			$field = 'email';
			$value = $this->request['employee_email'];
		} else	{
			$field = 'id';
			$value = $this->request['employee_id'];
		}

		$employee = get_user_by( $field, $value );

		if ( ! $employee )	{
			$response['error'] = __( 'Employee could not be found.', 'mobile-dj-manager' );
			
			$this->data = array_merge( $response, $this->data );
			$this->output();
		}

		if ( ! mdjm_is_employee( $employee->ID ) )	{
			$response['error'] = __( 'Error retrieving employee.', 'mobile-dj-manager' );
			
			$this->data = array_merge( $response, $this->data );
			$this->output();
		}

		$events          = array();
		$roles           = array();
		$mdjm_roles      = MDJM()->roles->get_roles();
		$employee_events = mdjm_get_employee_events( $employee->ID );
		$next_event      = mdjm_get_employees_next_event( $employee->ID );
		$i = 0;

		if ( $employee_events )	{
			foreach( $employee_events as $event )	{
				$events[ $event->ID ] = get_post_meta( $event->ID, '_mdjm_event_date', true );
				$i++;
			}
		}

		if( ! empty( $employee->roles ) )	{
			
			foreach( $employee->roles as $role )	{
				if( array_key_exists( $role, $mdjm_roles ) )	{
					$roles[ $role ] = $mdjm_roles[ $role ];
				}
				
			}
			
		}

		$response['employee'] = array(
			'ID'           => $employee->ID,
			'first_name'   => $employee->first_name,
			'last_name'    => $employee->last_name,
			'email'        => $employee->user_email,
			'roles'        => $roles,
			'last_login'   => $employee->last_login,
			'events'       => $events,
			'next_event'   => array(
				'id'           => ! empty( $next_event ) ? $next_event->ID : '',
				'date'         => ! empty( $next_event ) ? get_post_meta( $next_event->ID, '_mdjm_event_date', true ) : '',
			),
			'total_events' => $i
		);

		$this->data = array_merge( $this->data, $response );

		do_action( 'mdjm_after_api_get_employee', $this );

		$this->output();

	} // get_employee

	/**
	 * Retrieve a single event by id.
	 *
	 * @since	1.0
	 * @return	void
	 */
	public function get_event()	{

		$response   = array();

		if ( ! isset( $this->request['event_id'] ) )	{
			$this->missing_params( 'event_id' );
		}

		if ( ! mdjm_employee_can( 'read_events', $this->user_id ) )	{
			$this->no_permsission();
		}

		do_action( 'mdjm_before_api_get_event', $this );

		$mdjm_event = mdjm_get_event( $this->request['event_id'] );

		if ( ! $mdjm_event )	{
			$error = array();
			$error['error'] = sprintf( __( '%s does not exist.', 'mobile-dj-manager' ), mdjm_get_label_singular() );
			
			$this->data = $error;
			$this->output();
		}

		$response['event'] = mdjm_get_event_data( $mdjm_event );

		$response['event'] = array_merge( array( 'id' => $mdjm_event->ID ), $response['event'] );

		$this->data = array_merge( $this->data, $response );

		do_action( 'mdjm_after_api_get_event', $this );

		$this->output();

	} // get_event

	/**
	 * Retrieve events filtered by employee, client, date or status.
	 *
	 * @since	1.0
	 * @return	void
	 */
	public function list_events()	{

		$response       = array();

		if ( ! mdjm_employee_can( 'read_events', $this->user_id ) )	{
			$this->no_permsission();
		}

		if ( ! isset( $this->request['employee_id'] ) && ! mdjm_employee_can( 'read_events_all', $this->user_id ) )	{
			$this->no_permsission();
		}

		do_action( 'mdjm_before_api_event_list', $this );

		if ( isset( $this->request['employee_id'] ) )	{
			$events = mdjm_get_employee_events( $this->request['employee_id'] );
		} elseif ( isset( $this->request['client_id'] ) )	{
			$events = mdjm_get_client_events( $this->request['client_id'] );
		} elseif ( isset( $this->request['date'] ) )	{
			$events = mdjm_get_events_by_date( $this->request['date'] );
		} elseif ( isset( $this->request['status'] ) )	{
			$events = mdjm_get_events_by_status( $this->request['status'] );
		} else	{
			$events = mdjm_get_events();
		}

		if ( ! $events )	{
			$error = array();
			$error['error'] = sprintf( __( 'No %s found.', 'mobile-dj-manager' ), mdjm_get_label_plural( true ) );

			$this->data = $error;
			$this->output();
		}

		$response['events'] = array();
		$i = 0;

		foreach ( $events as $event )	{
			$response['events'][ $event->ID ] = mdjm_get_event_data( $event->ID );
			$i++;
		}

		$response['count'] = $i;

		$this->data = array_merge( $this->data, $response );

		do_action( 'mdjm_after_api_event_list', $this );

		$this->output();

	} // list_events

} // MDJM_API
