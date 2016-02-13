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
| 
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
		
		add_action( 'wp_head', 'datepicker' );
		add_filter( 'mdjm_ajax_script_vars', array( &$this, 'ajax' ) );
	} // __construct
	
	/**
	 * Pass required variables to the jQuery script.
	 *
	 * @since	1.3
	 * @param
	 * @return 	void
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
	
	/**
	 * Pass required variables to the jQuery script.
	 *
	 * @since	1.3
	 * @param
	 * @return 	void
	 */
	public function datepicker()	{
		mdjm_insert_datepicker(
			array(
				'class'		=> 'mdjm_widget_date',
				'altfield'	=> 'mdjm_enquiry_date_hidden_widget',
				'mindate'	=> 'today'
			)
		);
	} // datepicker
	
	/**
	 * Front-end display of widget.
	 *
	 * @see WP_Widget::widget()
	 *
	 * @param	arr		$args		Widget arguments.
	 * @param	arr		$instance	Saved values from database.
	 */
	public function widget( $args, $instance )	{
		$instance['title'] = ( isset( $instance['title'] ) ) ? $instance['title'] : '';
		
		$title = apply_filters( 'widget_title', $instance['title'], $instance, $args['id'] );
		
		echo $args['before_widget'];
		
		if ( $title ) {
			echo $args['before_title'] . $title . $args['after_title'];
		}
		
		do_action( 'mdjm_before_availability_widget' );
		
		mdjm_locate_template( 'availability-widget.php', true );
		
		do_action( 'mdjm_after_availability_widget' );
		
		echo $args['after_widget'];
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