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
	} // __construct
	
	/**
	 * Pass required variables to the jQuery script.
	 *
	 * @since	1.3
	 * @param
	 * @return 	void
	 */
	public function ajax( $args, $instance )	{
		
		if( $instance['available_action'] != 'text' )	{
			$pass_redirect = true;
		}
			
		if( $instance['unavailable_action'] != 'text' )	{
			$fail_redirect = true;
		}
		
		?>
		<script type="text/javascript">
		jQuery(document).ready(function($) 	{
			$('#mdjm-widget-availability-check').submit(function(event)	{
				if( !$("#widget_check_date").val() )	{
					return false;
				}
				event.preventDefault ? event.preventDefault() : (event.returnValue = false);
				var check_date = $("#widget_check_date").val();
				var avail = "<?php echo $instance['available_text']; ?>";
				var unavail = "<?php echo $instance['unavailable_text']; ?>";
				$.ajax({
					type: "POST",
					dataType: "json",
					url: "<?php echo admin_url( 'admin-ajax.php' ); ?>",
					data: {
						check_date : check_date,
						avail_text: avail,
						unavail_text : unavail,
						action : "mdjm_do_availability_check"
					},
					beforeSend: function()	{
						$('input[type="submit"]').prop('disabled', true);
						$("#widget_pleasewait").show();
					},
					success: function(response)	{
						if(response.result == "available") {
							<?php
							if( !empty( $pass_redirect ) )	{
								?>
								window.location.href = '<?php echo mdjm_get_formatted_url( $instance['available_action'] ); ?>mdjm_avail_date=' + check_date;
								<?php
							} else	{
								?>
								$("#widget_avail_intro").replaceWith('<div id="widget_avail_intro">' + response.message + '</div>');
								$("#mdjm_widget_avail_submit").fadeTo("slow", 1);
								$("#mdjm_widget_avail_submit").removeClass( "mdjm-updating" );
								$("#widget_pleasewait").hide();
								<?php
							}
							?>
							$('input[type="submit"]').prop('disabled', false);
						}
						else	{
							<?php
							if( ! empty( $fail_redirect ) )	{
								?>
								window.location.href = '<?php echo mdjm_get_formatted_url( $instance['unavailable_action'] ); ?>';
								<?php
							} else	{
								?>
								$("#widget_avail_intro").replaceWith('<div id="widget_avail_intro">' + response.message + '</div>');
								$("#mdjm_widget_avail_submit").fadeTo("slow", 1);
								$("#mdjm_widget_avail_submit").removeClass( "mdjm-updating" );
								$("#widget_pleasewait").hide();
								<?php
							}
							?>
							$('input[type="submit"]').prop('disabled', false);
						}
					}
				});
			});
		});
		</script>
		<?php

	} // ajax
	
	/**
	 * Front-end display of widget.
	 *
	 * @see		WP_Widget::widget()
	 *
	 * @param	arr		$args		Widget arguments.
	 * @param	arr		$instance	Saved values from database.
	 */
	public function widget( $args, $instance )	{
		if( !empty( $instance['ajax'] ) )	{
			self::ajax( $args, $instance );
		}
		
		echo $args['before_widget'];
		
		if ( !empty( $instance['title'] ) )	{
			echo $args['before_title'] . apply_filters( 'widget_title', $instance['title'] ) . $args['after_title'];
		}
		
		/* Check for form submission & process */
		if( isset( $_POST['mdjm_widget_avail_submit'] ) && $_POST['mdjm_widget_avail_submit'] == $instance['submit_text'] )	{
			$dj_avail = dj_available( '', $_POST['widget_check_date'] );
			
			if( isset( $dj_avail ) )	{
				if ( !empty( $dj_avail['available'] ) )	{
					if( isset( $instance['available_action'] ) && $instance['available_action'] != 'text' )	{
						?>
						<script type="text/javascript">
						window.location = '<?php echo mdjm_get_formatted_url( $instance['available_action'] ) . 'mdjm_avail=1&mdjm_avail_date=' . $_POST['widget_check_date']; ?>';
						</script>
						<?php
					}
				} else	{
					if( isset( $instance['unavailable_action'] ) && $instance['unavailable_action'] != 'text' )	{
						?>
						<script type="text/javascript">
						window.location = '<?php echo mdjm_get_formatted_url( $instance['unavailable_action'] ); ?>';
						</script>
						<?php	
					}
				}
			} // if( isset( $dj_avail ) )
		} // if( isset( $_POST['mdjm_avail_submit'] ) ...
		
		/* We need the jQuery Calendar */
		wp_enqueue_script('jquery-ui-datepicker');
		wp_enqueue_style('jquery-ui-css', '//ajax.googleapis.com/ajax/libs/jqueryui/1.8.2/themes/smoothness/jquery-ui.css');
		
		mdjm_insert_datepicker(
			array(
				'class'		=> 'mdjm_widget_date',
				'altfield'	 => 'widget_check_date',
				'mindate'	  => '1'
			)
		);
		
		if( isset( $instance['intro'] ) && !empty( $instance['intro'] ) )	{
			if( isset( $_POST['mdjm_widget_avail_submit'] ) && $_POST['mdjm_widget_avail_submit'] == $instance['submit_text'] )	{
				$search = array( '{EVENT_DATE}', '{EVENT_DATE_SHORT}' );
				$replace = array( date( 'l, jS F Y', strtotime( $_POST['widget_check_date'] ) ), 
								mdjm_format_short_date( $_POST['widget_check_date'] ) );
			}
			if( !isset( $_POST['mdjm_widget_avail_submit'] ) || $_POST['mdjm_widget_avail_submit'] != $instance['submit_text'] )	{
				echo '<div id="widget_avail_intro">' . $instance['intro'] . '</div>';
			} else	{
				if( !empty( $instance['ajax'] ) )	{
					?>
					<div id="widget_availability_result"></div>
					<?php
				} else	{
					if( ! empty( $dj_avail['available'] ) && $instance['available_action'] == 'text' && !empty( $instance['available_text'] ) )	{
						echo str_replace( $search, $replace, $instance['available_text'] );
					} else	{
						echo str_replace( $search, $replace, $instance['unavailable_text'] );	
					}
				}
			}
		}
		?>
		<form name="mdjm-widget-availability-check" id="mdjm-widget-availability-check" method="post">
		<label for="widget_avail_date"><?php echo $instance['label']; ?></label>
		<input type="text" name="widget_avail_date" id="widget_avail_date" class="mdjm_widget_date" style="z-index:99;" placeholder="<?php echo mdjm_format_datepicker_date(); ?>" />
		<input type="hidden" name="widget_check_date" id="widget_check_date" value="" />
		<p<?php echo ( isset( $instance['submit_centre'] ) && $instance['submit_centre'] == 'Y' ? ' style="text-align:center"' : '' ); ?>>
		<input type="submit" name="mdjm_widget_avail_submit" id="mdjm_widget_avail_submit" value="<?php echo $instance['submit_text']; ?>" />
		<div id="widget_pleasewait" class="page-content" style="display: none;"><?php _e( 'Please wait...', 'mobile-dj-manager' ); ?><img src="<?php echo MDJM_PLUGIN_URL; ?>/assets/images/loading.gif" alt="<?php _e( 'Please wait...', 'mobile-dj-manager' ); ?>" /></div>

		</form>
		<script type="text/javascript">
		jQuery(document).ready(function($){
			// Configure the field validator
			$('#mdjm-widget-availability-check').validate({
					rules:	{
						widget_avail_date: {
							required: true,
						},
					},
					messages: {
						widget_avail_date: {
							required: "<?php _e( 'Please enter a date', 'mobile-dj-manager' ); ?>",
						},
					},
					errorClass: "mdjm-form-error",
					validClass: "mdjm-form-valid",
				}
			);
		});
		</script>
		<?php
		
		echo $args['after_widget'];

	} // widget
	
	/**
	 * Back-end widget form.
	 *
	 * @see		WP_Widget::form()
	 *
	 * @param	arr		$instance	Previously saved values from database.
	 */
	public function form( $instance ) {							
		$defaults = array( 
					'title'              => __( 'Availability Checker', 'mobile-dj-manager' ),
					'ajax'			   => true,
					'intro'              => sprintf( __( 'Check my availability for your %s by entering the date below', 'mobile-dj-manager' ), mdjm_get_label_singular( true ) ),
					'label'              => __( 'Select Date:', 'mobile-dj-manager' ),
					'submit_text'        => __( 'Check Availability', 'mobile-dj-manager' ),
					'submit_centre'      => 'Y',
					'available_action'   => 'text',
					'available_text'     => __( 'Good news, we are available on {event_date}. Please contact us now', 'mobile-dj-manager' ),
					'unavailable_action' => 'text',
					'unavailable_text'   => __( 'Unfortunately we do not appear to be available on {event_date}. Why not try another date below...', 'mobile-dj-manager' ),
					
					);
		$instance = wp_parse_args( (array) $instance, $defaults );
		?>
		<p>
		<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title', 'mobile-dj-manager' ); ?>:</label>
		<input id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" value="<?php echo $instance['title']; ?>" style="width:100%;" />
		</p>
		
		<p>
		<input type="checkbox" id="<?php echo $this->get_field_id( 'ajax' ); ?>" name="<?php echo $this->get_field_name( 'ajax' ); ?>" value="1"<?php checked( $instance['ajax'], 1 ); ?> />
		<label for="<?php echo $this->get_field_id( 'ajax' ); ?>"><?php _e( 'Use Ajax?', 'mobile-dj-manager' ); ?>:</label>
		</p>
		
		<p>
		<label for="<?php echo $this->get_field_id( 'intro' ); ?>"><?php _e( 'Intro Text', 'mobile-dj-manager' ); ?>:</label>
		<textarea id="<?php echo $this->get_field_id( 'intro' ); ?>" name="<?php echo $this->get_field_name( 'intro' ); ?>" style="width:100%;"><?php echo $instance['intro']; ?></textarea>
		</p>
		
		<p>
		<label for="<?php echo $this->get_field_id( 'label' ); ?>"><?php _e( 'Field Label', 'mobile-dj-manager' ); ?>:</label>
		<input id="<?php echo $this->get_field_id( 'label' ); ?>" name="<?php echo $this->get_field_name( 'label' ); ?>" value="<?php echo $instance['label']; ?>" style="width:100%;" />
		</p>
		
		<p>
		<label for="<?php echo $this->get_field_id( 'submit_text' ); ?>"><?php _e( 'Submit Button Label', 'mobile-dj-manager' ); ?>:</label>
		<input id="<?php echo $this->get_field_id( 'submit_text' ); ?>" name="<?php echo $this->get_field_name( 'submit_text' ); ?>" value="<?php echo $instance['submit_text']; ?>" style="width:100%;" />
		</p>
		
		<p>
		<label for="<?php echo $this->get_field_id( 'submit_centre' ); ?>"><?php _e( 'Centre Submit Button', 'mobile-dj-manager' ); ?>?</label>
		<input type="checkbox" id="<?php echo $this->get_field_id( 'submit_centre' ); ?>" name="<?php echo $this->get_field_name( 'submit_centre' ); ?>" value="Y"<?php checked( 'Y', $instance['submit_centre'] ); ?> />
		</p>
		
		<p>
		<label for="<?php echo $this->get_field_id( 'available_action' ); ?>"><?php _e( 'Redirect on Available', 'mobile-dj-manager' ); ?>:</label>
		<?php 
		wp_dropdown_pages(
			array(
				'selected'          => $instance['available_action'],
				'name'              => $this->get_field_name( 'available_action' ),
				'id'                => $this->get_field_id( 'available_action' ),
				'show_option_none'  => __( 'NO REDIRECT - USE TEXT', 'mobile-dj-manager' ),
				'option_none_value' => 'text',
			 )
		);
		?>
		</p>
		
		<p>
		<label for="<?php echo $this->get_field_id( 'available_text' ); ?>"><?php _e( 'Available Text', 'mobile-dj-manager' ); ?>:</label>
		<textarea id="<?php echo $this->get_field_id( 'available_text' ); ?>" name="<?php echo $this->get_field_name( 'available_text' ); ?>" style="width:100%;"><?php echo $instance['available_text']; ?></textarea>
		</p>
		
		<p>
		<label for="<?php echo $this->get_field_id( 'unavailable_action' ); ?>"><?php _e( 'Redirect on Unavailable', 'mobile-dj-manager' ); ?>:</label>
		<?php 
		wp_dropdown_pages(
			array(
				'selected'          => $instance['unavailable_action'],
				'name'              => $this->get_field_name( 'unavailable_action' ),
				'id'                => $this->get_field_id( 'unavailable_action' ),
				'show_option_none'  => __( 'NO REDIRECT - USE TEXT', 'mobile-dj-manager' ),
				'option_none_value' => 'text',
			 )
		);
		?>
		</p>
		
		<p>
		<label for="<?php echo $this->get_field_id( 'unavailable_text' ); ?>"><?php _e( 'Unavailable Text', 'mobile-dj-manager' ); ?>:</label>
		<textarea id="<?php echo $this->get_field_id( 'unavailable_text' ); ?>" name="<?php echo $this->get_field_name( 'unavailable_text' ); ?>" style="width:100%;"><?php echo $instance['unavailable_text']; ?></textarea>
		</p>
		
		<?php 
	} // form
	
	/**
	 * Sanitize widget form values as they are saved.
	 *
	 * @see		WP_Widget::update()
	 *
	 * @param	arr		$new_instance	Values just sent to be saved.
	 * @param	arr		$old_instance	Previously saved values from database.
	 *
	 * @return	arr		Updated safe values to be saved.
	 */
	public function update( $new_instance, $old_instance ) {
		$instance = array();
		$instance['title']				 = ( !empty( $new_instance['title'] ) )				 ? strip_tags( $new_instance['title'] )				 : '';
		$instance['ajax']				  = ( !empty( $new_instance['ajax'] ) )				  ? true												 : false;
		$instance['intro']				 = ( !empty( $new_instance['intro'] ) )				 ? strip_tags( $new_instance['intro'] )				 : '';
		$instance['label']				 = ( !empty( $new_instance['label'] ) )				 ? strip_tags( $new_instance['label'] )				 : '';
		$instance['submit_text']		   = ( !empty( $new_instance['submit_text'] ) )		   ? strip_tags( $new_instance['submit_text'] )		   : '';
		$instance['submit_centre']		 = ( !empty( $new_instance['submit_centre'] ) )		 ? $new_instance['submit_centre']					   : '';
		$instance['available_action']	  = ( !empty( $new_instance['available_action'] ) )	  ? strip_tags( $new_instance['available_action'] )	  : '';
		$instance['available_text']		= ( !empty( $new_instance['available_text'] ) )		? strip_tags( $new_instance['available_text'] )		: '';
		$instance['unavailable_action']	= ( !empty( $new_instance['unavailable_action'] ) )	? strip_tags( $new_instance['unavailable_action'] )	: '';
		$instance['unavailable_text']	  = ( !empty( $new_instance['unavailable_text'] ) )	  ? strip_tags( $new_instance['unavailable_text'] )	  : '';

		return $instance;
	} // update

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
