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
			global $mdjm_settings, $mdjm;
			
			echo $args['before_widget'];
			
			if ( !empty( $instance['title'] ) ) {
				echo $args['before_title'] . apply_filters( 'widget_title', $instance['title'] ). $args['after_title'];
			}
			
			/* Check for form submission & process */
			if( isset( $_POST['mdjm_avail_submit'] ) && $_POST['mdjm_avail_submit'] == $instance['submit_text'] )	{
				$dj_avail = dj_available( '', $_POST['widget_check_date'] );
				
				if( isset( $dj_avail ) )	{
					if ( !empty( $dj_avail['available'] ) )	{
						if( isset( $instance['available_action'] ) && $instance['available_action'] != 'text' )	{
							?>
							<script type="text/javascript">
							window.location = '<?php echo $mdjm->get_link( $instance['available_action'], true ) . 'mdjm_avail=1&mdjm_avail_date=' . $_POST['widget_check_date']; ?>';
							</script>
							<?php
						}
					}
					else	{
						if( isset( $instance['unavailable_action'] ) && $instance['unavailable_action'] != 'text' )	{
							?>
							<script type="text/javascript">
							window.location = '<?php echo $mdjm->get_link( $instance['unavailable_action'] ); ?>';
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
			<?php
			mdjm_jquery_datepicker_script( array( '', 'widget_check_date' ) );
			?>
            </script>
            <form name="mdjm-widget-availability-check" id="mdjm-widget-availability-check" method="post">
            <p>
            <?php
			if( isset( $instance['intro'] ) && !empty( $instance['intro'] ) )	{
				if( isset( $_POST['mdjm_avail_submit'] ) && $_POST['mdjm_avail_submit'] == $instance['submit_text'] )	{
					$search = array( '{EVENT_DATE}', '{EVENT_DATE_SHORT}' );
					$replace = array( date( 'l, jS F Y', strtotime( $_POST['widget_check_date'] ) ), 
									date( MDJM_SHORTDATE_FORMAT, strtotime( $_POST['widget_check_date'] ) ) );
				}
				if( !isset( $_POST['mdjm_avail_submit'] ) || $_POST['mdjm_avail_submit'] != $instance['submit_text'] )	{
					echo $instance['intro'] . '</p><p>';
				}
				elseif( !empty( $dj_avail['available'] ) && $instance['available_action'] == 'text' && !empty( $instance['available_text'] ) )	{
					echo str_replace( $search,
									  $replace,
									  $instance['available_text'] . '</p><p>' );
				}
				else	{
					echo str_replace( $search,
									  	   $replace,
									  	   $instance['unavailable_text'] . '</p><p>' );	
				}
			}
			?>
            <label for="avail_date"><?php echo $instance['label']; ?></label>
            <input type="text" name="avail_date" id="avail_date" class="mdjm_date" placeholder="<?php f_mdjm_short_date_jquery(); ?>" />
            <input type="hidden" name="widget_check_date" id="widget_check_date" value="" /></p>
            <p<?php echo ( isset( $instance['submit_centre'] ) && $instance['submit_centre'] == 'Y' ? ' style="text-align:center"' : '' ); ?>>
            <input type="submit" name="mdjm_avail_submit" id="mdjm_avail_submit" value="<?php echo $instance['submit_text']; ?>" />
            </p>
            </form>
            <script type="text/javascript">
			jQuery(document).ready(function($){
				// Configure the field validator
				$('#mdjm-widget-availability-check').validate(
					{
						rules:
						{
							avail_date: {
								required: true,
							},
						}, // End rules
						messages:
						{
							avail_date: {
									required: "Please enter a date",
									},
						}, // End messages
						// Classes
						errorClass: "mdjm-form-error",
						validClass: "mdjm-form-valid",
					} // End validate
				); // Close validate
			});
			</script>
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
						'available_text'     => 'Good news, we are available on {EVENT_DATE}. Please contact us now',
						'unavailable_action' => 'text',
						'unavailable_text'   => 'Unfortunately we do not appear to be available on {EVENT_DATE}. Why not try another date below...',
						
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

/****************************************************************
* CONTACT FORM WIDGET
****************************************************************/	
/*
* MDJM_ContactForms_Widget
* 08/01/2015
* @since 1.0
* Displays the contact form
*/
	class MDJM_ContactForms_Widget extends WP_Widget {
		/* Register the Widget */
		function __construct() {
			parent::__construct(
				'mdjm_contact_form_widget', /* Base ID */
				__( 'MDJM Contact Form', 'text_domain' ), /* Name */
				array( 'description' => __( 'Displays the specified contact form', 'text_domain' ), ) /* Args */
			);
		}
	
		/**
		 * Front-end display of Contact Form widget.
		 *
		 * @see WP_Widget::widget()
		 *
		 * @param array $args     Widget arguments.
		 * @param array $instance Saved values from database.
		 */
		public function widget( $args, $instance )	{
			
			echo $args['before_widget'];
			if ( !empty( $instance['title'] ) ) {
				echo $args['before_title'] . apply_filters( 'widget_title', $instance['title'] ). $args['after_title'];
			}
			
			/* -- This is the format the MDJM_ContactForm class needs -- */
			$atts['slug'] = $instance['contact_form'];
			$atts['layout'] = '0';
						
			ob_start();
			include_once WPMDJM_PLUGIN_DIR . '/client-zone/class/class-contactform.php';
			$output = ob_get_clean();
			
			echo $output;
			
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
			$mdjm_forms = get_posts( array(
										'posts_per_page'		=> -1,
										'orderby'				=> 'post_date',
										'order'            		=> 'ASC',
										'post_type'				=> MDJM_CONTACT_FORM_POSTS,
									) );
			$defaults = array( 
						'title'              => MDJM_COMPANY . ' Contact Form',
						'intro'              => 'Contact us using the form below',
						'contact_form'       => '--- Select a Form ---',						
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
			<label for="<?php echo $this->get_field_id( 'contact_form' ); ?>">Show Contact Form:</label>
			<select id="<?php echo $this->get_field_id( 'contact_form' ); ?>" name="<?php echo $this->get_field_name( 'contact_form' ); ?>" style="width:100%;">
            <?php
            if( !isset( $instance['contact_form'] ) || empty( $instance['contact_form'] ) )	{
				?><option value="">--- Select a Form ---</option><?php	
			}
            foreach( $mdjm_forms as $form )	{
				?><option value="<?php echo $form->post_name; ?>"<?php selected( $instance['contact_form'], $form->post_name ); ?>><?php echo $form->post_title; ?></option><?php
			}
			?>
            </select>
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
			$instance['contact_form'] = ( !empty( $new_instance['contact_form'] ) ) ? strip_tags( $new_instance['contact_form'] ) : '';
	
			return $instance;
		}
	
	} // class MDJM_ContactForms_Widget
?>