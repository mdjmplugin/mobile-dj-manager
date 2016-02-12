<?php
/**
 * Widgets
 *
 * @package     MDJM
 * @subpackage  Widgets
 * @copyright   Copyright (c) 2016, Mike Howard
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.3
*/

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/*
|--------------------------------------------------------------------------
| FRONT-END WIDGETS
|--------------------------------------------------------------------------
|
| - Availability Widget
| - Categories / Tags Widget
|
*/

/**
 * Availability Widget
 *
 * Availability widget class.
 *
 * @sinc	1.0
 * @return	void
*/
class mdjm_availability_widget extends WP_Widget {
	/** Constructor */
	public function __construct() {
		parent::__construct(
			'mdjm_availability_widget',
			__( 'MDJM Availability Checker', 'mobile-dj-manager' ),
			array( 'description' => __( 'Enables clients to check your availability', 'mobile-dj-manager' ) )
		);
		
		add_filter( 'mdjm_ajax_script_vars', array( &$this, 'ajax' ) );
	} // __construct
	
	/**
	 * Set the vars within our Ajax script
	 *
	 *
	 *
	 *
	 */
	public function ajax()	{
		$widget_options_all = get_option($this->option_name);
		$instance = $widget_options_all[ $this->number ];
		
		if( empty( $instance['ajax'] ) )	{
			return;
		}
		
		$availability_vars = array(
			'pass_redirect'	=> $instance['available_action'] != 'text' ? mdjm_get_formatted_url( $instance['available_action'], true ) . 'mdjm_avail_date=' : '',
			'fail_redirect'	=> $instance['unavailable_action'] != 'text' ? mdjm_get_formatted_url( $instance['unavailable_action'], true ) : ''
		);
		
		return array_merge( $vars, $availability_vars );
	} // ajax
	
	/** @see WP_Widget::widget */
	public function widget( $args, $instance )	{
		echo $args['before_widget'];
	} // widget

} // class mdjm_availability_widget

/**
 * Register Widgets
 *
 * Registers the MDJM Widgets.
 *
 * @since	1.3
 * @return	void
 */
function mdjm_register_widgets() {
	register_widget( 'mdjm_availability_widget' );
}
add_action( 'widgets_init', 'mdjm_register_widgets' );