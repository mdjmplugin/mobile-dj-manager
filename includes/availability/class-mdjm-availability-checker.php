<?php
/**
 * Availability
 *
 * @package     MDJM
 * @subpackage  Classes/Availability Checker
 * @copyright   Copyright (c) 2016, Mike Howard
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.3
*/

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * MDJM_Availability_Checker Class
 *
 * @since	1.3
 */
class MDJM_Availability_Checker {

	/**
	 * The date to check
	 *
	 * @since	1.3
	 */
	public $date = 0;
	
	/**
	 * The employees to check
	 *
	 * @since	1.3
	 */
	public $employees = array();
	
	/**
	 * The event status to report as unavailable
	 *
	 * @since	1.3
	 */
	public $status = array();
	
	/**
	 * Get things going
	 *
	 * @since	1.3
	 */
	public function __construct( $date = false, $_employees = array() ) {		
		return $this->setup_check( $date );
	} // __construct
	
	/**
	 * Setup the availability checker.
	 *
	 * @since	1.3
	 * @param	str		$date	The date to check
	 * @return	bool
	 */
	public function setup_check( $date )	{
		if( empty( $date ) )	{
			return false;
		}
		
		$this->date		 = strtotime( $date );
		$this->employees	= mdjm_get_employees( mdjm_get_option( 'availability_roles' ), '' );
		$this->status	   = mdjm_get_option( 'availability_status', 'any' );
		
		return true;
	} // setup_check
	
	/**
	 * Perform the availability lookup.
	 *
	 * @since	1.3
	 * @param
	 * @return	bool
	 */
	public function perform_lookup()	{
		
	} // perform_lookup
} // class MDJM_Availability_Checker