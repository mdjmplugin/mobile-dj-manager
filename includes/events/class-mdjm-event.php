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
	 * The client ID
	 *
	 * @since	1.3
	 */
	private $client_id;
	
	/**
	 * The client object
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
	 * The primary employee object
	 *
	 * @since	1.3
	 */
	private $employee;
	
	/**
	 * The event employees
	 *
	 * @since	1.3
	 */
	private $employees;
	
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

		return true;
	} // setup_event
}