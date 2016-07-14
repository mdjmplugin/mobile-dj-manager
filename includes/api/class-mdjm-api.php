<?php
/**
 * MDJM Rest API
 *
 * This class provides a front-facing JSON API that makes it possible to
 * query data from the business.
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
class MDJM_API	{

	/**
	 * Latest API Version
	 */
	const VERSION = 1;

	/**
	 * Namespace
	 */
	const NAME_SPACE = 'mdjm/v';

	/**
	 * Log API requests?
	 *
	 * @var		bool
	 * @access	private
	 * @since	1.4
	 */
	public $log_requests = true;

	/**
	 * Is this a valid request?
	 *
	 * @var		bool
	 * @access	private
	 * @since	1.4
	 */
	private $is_valid_request = false;

	/**
	 * User ID Performing the API Request
	 *
	 * @var		int
	 * @access	private
	 * @since	1.4
	 */
	public $user_id = 0;

	/**
	 * Setup the MDJM API
	 *
	 * @since	1.4
	 */
	public function __construct()	{
		add_action( 'rest_api_init', array( $this, 'register_endpoints' ) );
	} // __construct

	/**
	 * Register the API endpoints.
	 *
	 * @since	1.4
	 * @return	arr
	 */
	public function register_endpoints()	{

		$namespace = self::NAME_SPACE . self::VERSION;
		$endpoints = $this->define_endpoints();

		if ( $endpoints )	{

			foreach( $endpoints as $base => $args )	{

				$register_endpoint = register_rest_route(
					$namespace,
					$base,
					$args
				);

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
			'/affiliate/(?P<api_key>[\w-]+)' => array(
				'methods'  => WP_REST_Server::READABLE,
				'callback' => 'mdjm_aff_authenticate_api',
				'args'     => array(
					'api_key' => array(
						'required'          => true,
						'validate_callback' => 'mdjm_aff_validate_date'
					)
				)
			),
			'/availability/(?P<date>[\w-]+)' => array(
				'methods'  => array( WP_REST_Server::READABLE, WP_REST_Server::CREATABLE ),
				'callback' => array( $this, 'availability_check' ),
				'args'     => array(
					'date' => array(
						'required'          => true
					)
				)
			)
		);

		return apply_filters( 'mdjm_api_endpoints', $endpoints );

	} // define_endpoints

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

} // MDJM_API
