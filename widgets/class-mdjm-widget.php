<?php
/*
* class-mdjm-availability-widget.php
* 28/12/2014
* @since 0.9.9
* The Availability widget
*/

	class MDJM_Availability_Widget extends WP_Widget {
		/* Register the Widget */
		function __construct() {
			parent::__construct(
				'mdjm_availability_widget', /* Base ID */
				__( 'MDJM Availability Checker', 'text_domain' ), /* Name */
				array( 'description' => __( 'Enables clients to check your availability', 'text_domain' ), ) /* Args */
			);
		}
	
		/**
		 * Front-end display of widget.
		 *
		 * @see WP_Widget::widget()
		 *
		 * @param array $args     Widget arguments.
		 * @param array $instance Saved values from database.
		 */
		public function widget( $args, $instance )	{
			global $mdjm_options;
			
			echo $args['before_widget'];
			
			if ( !empty( $instance['title'] ) ) {
				echo $args['before_title'] . apply_filters( 'widget_title', $instance['title'] ). $args['after_title'];
			}
			
			/* Check for form submission & process */
			if( isset( $_POST['mdjm_avail_submit'] ) && $_POST['mdjm_avail_submit'] == $instance['submit_text'] )	{
				$mdjm_pages = get_option( 'mdjm_plugin_pages' );
				$dj_avail = f_mdjm_available( $_POST['check_date'] );
				
				if( isset( $dj_avail ) )	{
					if ( $dj_avail !== false )	{
						if( isset( $instance['available_action'] ) && $instance['available_action'] != 'text' )	{
							?>
							<script type="text/javascript">
							window.location = '<?php echo get_permalink( $instance['available_action'] ); ?>';
							</script>
							<?php
						}
					}
					else	{
						if( isset( $instance['unavailable_action'] ) && $instance['unavailable_action'] != 'text' )	{
							?>
							<script type="text/javascript">
							window.location = '<?php echo get_permalink( $instance['unavailable_action'] ); ?>';
							</script>
							<?php	
						}
					}
				} // if( isset( $dj_avail ) )
			} // if( isset( $_POST['mdjm_avail_submit'] ) ...
			
            /* We need the jQuery Calendar */
            wp_enqueue_script('jquery-ui-datepicker');
            wp_enqueue_style('jquery-ui-css', 'http://ajax.googleapis.com/ajax/libs/jqueryui/1.8.2/themes/smoothness/jquery-ui.css');
            ?>
            <script type="text/javascript">
            jQuery(document).ready(function($) {
                $('.custom_date').datepicker({
                dateFormat : '<?php f_mdjm_short_date_jquery(); ?>',
                altField  : '#check_date',
                altFormat : 'yy-mm-dd',
                firstDay: <?php echo get_option( 'start_of_week' ); ?>,
                changeYear: true,
                changeMonth: true
                });
            });
            function check_avail_validation() {
                /* Check the Date field for blank submission*/
                var avail_date = document.forms["mdjm-availability-check"]["avail_date"].value;
                if (avail_date == "" || avail_date == null) {
                    alert("Please enter a date to check for availability");
                    return false;
                }
            }
            </script>
            <form name="mdjm-availability-check" method="post" onsubmit="check_avail_validation()">
            <p>
            <?php
			if( isset( $instance['intro'] ) && !empty( $instance['intro'] ) )	{
				if( !isset( $_POST['mdjm_avail_submit'] ) || $_POST['mdjm_avail_submit'] != $instance['submit_text'] )	{
					echo $instance['intro'] . '</p><p>';
				}
				elseif( $dj_avail !== false && $instance['available_action'] == 'text' && !empty( $instance['available_text'] ) )	{
					echo $instance['available_text'] . '</p><p>';
				}
				else	{
					echo $instance['unavailable_text'] . '</p><p>';	
				}
			}
			?>
            <label for="avail_date"><?php echo $instance['label']; ?></label>
            <input type="text" name="avail_date" id="avail_date" class="custom_date" placeholder="<?php f_mdjm_short_date_jquery(); ?>" />
            <input type="hidden" name="check_date" id="check_date" value="" /></p>
            <p<?php if( isset( $instance['submit_centre'] ) && $instance['submit_centre'] == 'Y' ) echo ' align="center"'; ?>>
            <input type="submit" name="mdjm_avail_submit" id="mdjm_avail_submit" value="<?php echo $instance['submit_text']; ?>" />
            </p>
            </form>
            <?php
			
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
						'title'              => 'Availability Checker',
						'intro'              => 'Check my availability for your event by entering the date below',
						'label'              => 'Select Date:',
						'submit_text'        => 'Check Availability',
						'submit_centre'      => 'Y',
						'available_action'   => 'text',
						'available_text'     => 'Good news, we are available on the date you entered. Please contact us now',
						'unavailable_action' => 'text',
						'unavailable_text'   => 'Unfortunately we do not appear to be available on the date you selected. Why not try another date below...',
						
						);
			$instance = wp_parse_args( (array) $instance, $defaults );
			?>
			<p>
			<label for="<?php echo $this->get_field_id( 'title' ); ?>">Title:</label>
			<input id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" value="<?php echo $instance['title']; ?>" style="width:100%;" />
            </p>
            
            <p>
			<label for="<?php echo $this->get_field_id( 'intro' ); ?>">Intro Text:</label>
			<textarea id="<?php echo $this->get_field_id( 'intro' ); ?>" name="<?php echo $this->get_field_name( 'intro' ); ?>" style="width:100%;"><?php echo $instance['intro']; ?></textarea>
            </p>
            
            <p>
			<label for="<?php echo $this->get_field_id( 'label' ); ?>">Field Label:</label>
			<input id="<?php echo $this->get_field_id( 'label' ); ?>" name="<?php echo $this->get_field_name( 'label' ); ?>" value="<?php echo $instance['label']; ?>" style="width:100%;" />
            </p>
            
            <p>
			<label for="<?php echo $this->get_field_id( 'submit_text' ); ?>">Submit Button Label:</label>
			<input id="<?php echo $this->get_field_id( 'submit_text' ); ?>" name="<?php echo $this->get_field_name( 'submit_text' ); ?>" value="<?php echo $instance['submit_text']; ?>" style="width:100%;" />
            </p>
            
            <p>
			<label for="<?php echo $this->get_field_id( 'submit_centre' ); ?>">Centre Submit Button?</label>
			<input type="checkbox" id="<?php echo $this->get_field_id( 'submit_centre' ); ?>" name="<?php echo $this->get_field_name( 'submit_centre' ); ?>" value="Y"<?php checked( 'Y', $instance['submit_centre'] ); ?> />
            </p>
            
            <p>
			<label for="<?php echo $this->get_field_id( 'available_action' ); ?>">Redirect on Available:</label>
            <?php 
			wp_dropdown_pages( array(
									'selected'          => $instance['available_action'],
									'name'              => $this->get_field_name( 'available_action' ),
									'id'                => $this->get_field_id( 'available_action' ),
									'show_option_none'  => 'NO REDIRECT - USE TEXT',
									'option_none_value' => 'text',
									 ) );
			?>
            </p>
            
            <p>
			<label for="<?php echo $this->get_field_id( 'available_text' ); ?>">Available Text:</label>
			<textarea id="<?php echo $this->get_field_id( 'available_text' ); ?>" name="<?php echo $this->get_field_name( 'available_text' ); ?>" style="width:100%;"><?php echo $instance['available_text']; ?></textarea>
            </p>
            
            <p>
			<label for="<?php echo $this->get_field_id( 'unavailable_action' ); ?>">Redirect on Unavailable:</label>
            <?php 
			wp_dropdown_pages( array(
									'selected'          => $instance['unavailable_action'],
									'name'              => $this->get_field_name( 'unavailable_action' ),
									'id'                => $this->get_field_id( 'unavailable_action' ),
									'show_option_none'  => 'NO REDIRECT - USE TEXT',
									'option_none_value' => 'text',
									 ) );
			?>
            </p>
            
            <p>
			<label for="<?php echo $this->get_field_id( 'unavailable_text' ); ?>">Unavailable Text:</label>
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
?>