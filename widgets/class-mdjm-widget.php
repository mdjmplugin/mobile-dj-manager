<?php
/*
* class-mdjm-widget.php
* 28/12/2014
* @since 0.9.9
* MDJM Widgets
*/

/*
* MDJM_Availability_Widget
* 28/12/2014
* @since 0.9.9
* Display the Availability widget
*/

	class MDJM_Availability_Widget extends WP_Widget {
		/**
		 * The availability array
		 */
		private static $dj_avail;
		/* Register the Widget */
		function __construct() {
			parent::__construct(
				'mdjm_availability_widget', /* Base ID */
				__( 'MDJM Availability Checker', 'mobile-dj-manager' ), /* Name */
				array( 'description' => __( 'Enables clients to check your availability', 'mobile-dj-manager' ), ) /* Args */
			);
			add_action( 'wp_head', array( &$this, 'datepicker' ) );
			add_filter( 'mdjm_ajax_script_vars', array( &$this, 'ajax' ) );
		}
		
		
		/**
		 * Pass required variables to the jQuery script.
		 *
		 * @since	1.3
		 * @param
		 * @return 	void
		 */
		public function ajax( $vars )	{
			$widget_options_all = get_option($this->option_name);
			$instance = $widget_options_all[ $this->number ];
			
			if( empty( $instance['ajax'] ) )	{
				return;
			}
			
			$availability_vars = array(
				'pass_redirect'           => $instance['available_action'] != 'text' ? mdjm_get_formatted_url( $instance['available_action'], true ) . 'mdjm_avail_date=' : '',
				'fail_redirect'           => $instance['unavailable_action'] != 'text' ? mdjm_get_formatted_url( $instance['unavailable_action'], true ) : '',
				'required_date_widget'	=> __( 'Please select a date', 'mobile-dj-manager' )
			);
			
			return array_merge( $vars, $availability_vars );
		} // ajax
		
		/**
		 *Insert the datepicker.
		 *
		 * @since	1.3
		 * @param
		 * @return 	void
		 */
		public function datepicker()	{
			mdjm_insert_datepicker(
				array(
					'class'		=> 'mdjm_datepicker_widget',
					'altfield'	 => 'mdjm_enquiry_date_widget',
					'mindate'	  => 'today'
				)
			);
		} // datepicker
						
		/**
		 * Front-end display of widget.
		 *
		 * @see WP_Widget::widget()
		 *
		 * @param array $args     Widget arguments.
		 * @param array $instance Saved values from database.
		 */
		public function widget( $args, $instance )	{
			global $mdjm_settings, $mdjm;
									
			if( !empty( $instance['ajax'] ) )
				self::ajax( $args, $instance );
			
			echo $args['before_widget'];
			
			if ( !empty( $instance['title'] ) )
				echo $args['before_title'] . apply_filters( 'widget_title', $instance['title'] ) . $args['after_title'];
			
			/* Check for form submission & process */
			if( isset( $_POST['mdjm_widget_avail_submit'] ) && $_POST['mdjm_widget_avail_submit'] == $instance['submit_text'] )	{
				$dj_avail = dj_available( '', '', $_POST['widget_check_date'] );
				
				if( isset( $dj_avail ) )	{
					if ( !empty( $dj_avail['available'] ) )	{
						if( isset( $instance['available_action'] ) && $instance['available_action'] != 'text' )	{
							?>
							<script type="text/javascript">
							window.location = '<?php echo mdjm_get_formatted_url( $instance['available_action'], true ) . 'mdjm_avail=1&mdjm_avail_date=' . $_POST['widget_check_date']; ?>';
							</script>
							<?php
						}
					}
					else	{
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
			
			if( isset( $instance['intro'] ) && !empty( $instance['intro'] ) )	{
				if( isset( $_POST['mdjm_widget_avail_submit'] ) && $_POST['mdjm_widget_avail_submit'] == $instance['submit_text'] )	{
					$search = array( '{EVENT_DATE}', '{EVENT_DATE_SHORT}' );
					$replace = array( date( 'l, jS F Y', strtotime( $_POST['widget_check_date'] ) ), 
									date( MDJM_SHORTDATE_FORMAT, strtotime( $_POST['widget_check_date'] ) ) );
				}
				if( !isset( $_POST['mdjm_widget_avail_submit'] ) || $_POST['mdjm_widget_avail_submit'] != $instance['submit_text'] )	{
					echo '<div id="widget_avail_intro">' . $instance['intro'] . '</div>';
				}
				else	{
					if( !empty( $instance['ajax'] ) )	{
						?>
						<div id="widget_availability_result"></div>
						<?php
					}
					else	{
						if( !empty( $dj_avail['available'] ) && $instance['available_action'] == 'text' && !empty( $instance['available_text'] ) )	{
							echo str_replace( $search,
											  $replace,
											  $instance['available_text'] );
						}
						else	{
							echo str_replace( $search,
												   $replace,
												   $instance['unavailable_text'] );	
						}
					}
				}
			}
			
			$widget_template = mdjm_get_template_part( 'availability', 'widget', false );
			include_once( $widget_template );
						
			echo $args['after_widget'];
		}
	
		/**
		 * Back-end widget form.
		 *
		 * @see WP_Widget::form()
		 *
		 * @param array $instance Previously saved values from database.
		 */
		public function form( $instance ) {							
			$defaults = array( 
						'title'              => __( 'Availability Checker', 'mobile-dj-manager' ),
						'ajax'			   => true,
						'intro'              => __( 'Check my availability for your event by entering the date below', 'mobile-dj-manager' ),
						'label'              => __( 'Select Date:', 'mobile-dj-manager' ),
						'submit_text'        => __( 'Check Availability', 'mobile-dj-manager' ),
						'submit_centre'      => 'Y',
						'available_action'   => 'text',
						'available_text'     => __( 'Good news, we are available on {EVENT_DATE}. Please contact us now', 'mobile-dj-manager' ),
						'unavailable_action' => 'text',
						'unavailable_text'   => __( 'Unfortunately we do not appear to be available on {EVENT_DATE}. Why not try another date below...', 'mobile-dj-manager' ),
						
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
			wp_dropdown_pages( array(
									'selected'          => $instance['available_action'],
									'name'              => $this->get_field_name( 'available_action' ),
									'id'                => $this->get_field_id( 'available_action' ),
									'show_option_none'  => __( 'NO REDIRECT - USE TEXT', 'mobile-dj-manager' ),
									'option_none_value' => 'text',
									 ) );
			?>
            </p>
            
            <p>
			<label for="<?php echo $this->get_field_id( 'available_text' ); ?>"><?php _e( 'Available Text', 'mobile-dj-manager' ); ?>:</label>
			<textarea id="<?php echo $this->get_field_id( 'available_text' ); ?>" name="<?php echo $this->get_field_name( 'available_text' ); ?>" style="width:100%;"><?php echo $instance['available_text']; ?></textarea>
            </p>
            
            <p>
			<label for="<?php echo $this->get_field_id( 'unavailable_action' ); ?>"><?php _e( 'Redirect on Unavailable', 'mobile-dj-manager' ); ?>:</label>
            <?php 
			wp_dropdown_pages( array(
									'selected'          => $instance['unavailable_action'],
									'name'              => $this->get_field_name( 'unavailable_action' ),
									'id'                => $this->get_field_id( 'unavailable_action' ),
									'show_option_none'  => __( 'NO REDIRECT - USE TEXT', 'mobile-dj-manager' ),
									'option_none_value' => 'text',
									 ) );
			?>
            </p>
            
            <p>
			<label for="<?php echo $this->get_field_id( 'unavailable_text' ); ?>"><?php _e( 'Unavailable Text', 'mobile-dj-manager' ); ?>:</label>
			<textarea id="<?php echo $this->get_field_id( 'unavailable_text' ); ?>" name="<?php echo $this->get_field_name( 'unavailable_text' ); ?>" style="width:100%;"><?php echo $instance['unavailable_text']; ?></textarea>
            </p>
            
			<?php 
		}
	
		/**
		 * Sanitize widget form values as they are saved.
		 *
		 * @see WP_Widget::update()
		 *
		 * @param array $new_instance Values just sent to be saved.
		 * @param array $old_instance Previously saved values from database.
		 *
		 * @return array Updated safe values to be saved.
		 */
		public function update( $new_instance, $old_instance ) {
			$instance = array();
			$instance['title'] = ( !empty( $new_instance['title'] ) ) ? strip_tags( $new_instance['title'] ) : '';
			$instance['ajax'] = ( !empty( $new_instance['ajax'] ) ) ? true : false;
			$instance['intro'] = ( !empty( $new_instance['intro'] ) ) ? strip_tags( $new_instance['intro'] ) : '';
			$instance['label'] = ( !empty( $new_instance['label'] ) ) ? strip_tags( $new_instance['label'] ) : '';
			$instance['submit_text'] = ( !empty( $new_instance['submit_text'] ) ) ? strip_tags( $new_instance['submit_text'] ) : '';
			$instance['submit_centre'] = ( !empty( $new_instance['submit_centre'] ) ) ? $new_instance['submit_centre'] : '';
			$instance['available_action'] = ( !empty( $new_instance['available_action'] ) ) ? strip_tags( $new_instance['available_action'] ) : '';
			$instance['available_text'] = ( !empty( $new_instance['available_text'] ) ) ? strip_tags( $new_instance['available_text'] ) : '';
			$instance['unavailable_action'] = ( !empty( $new_instance['unavailable_action'] ) ) ? strip_tags( $new_instance['unavailable_action'] ) : '';
			$instance['unavailable_text'] = ( !empty( $new_instance['unavailable_text'] ) ) ? strip_tags( $new_instance['unavailable_text'] ) : '';
	
			return $instance;
		}
	} // class MDJM_Availability_Widget
/**
 * Register the Availability widget for the use within WP
 *
 *
 *
 */
function mdjm_register_widgets()	{
	register_widget( 'MDJM_Availability_Widget' );
} // register_widgets
add_action( 'widgets_init', 'mdjm_register_widgets' );