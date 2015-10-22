<?php
/**
 * * * * * * * * * * * * * * * MDJM * * * * * * * * * * * * * * *
 * Functions that are used mainly within the frontend
 * may also be called from the backend
 *
 *
 * @since 1.0
 *
 */
/****************************************************************************************************
--	AVAILABILITY
****************************************************************************************************/
/**
* f_mdjm_availability_form
* 27/12/2014
* @since 0.9.9
* Displays the availability checker form
*/
	function f_mdjm_availability_form( $args )	{
		global $mdjm, $mdjm_settings;
		
		if( isset( $_POST['mdjm_avail_submit'] ) && !empty( $_POST['mdjm_avail_submit'] ) )	{
			$dj_avail = dj_available( '', $_POST['check_date'] );
			
			if( isset( $dj_avail ) )	{
				if( !empty( $dj_avail['available'] ) )	{
					if( isset( $mdjm_settings['availability']['availability_check_pass_page'] ) && $mdjm_settings['availability']['availability_check_pass_page'] != 'text' )	{
						?>
						<script type="text/javascript">
						window.location = '<?php echo $mdjm->get_link( $mdjm_settings['availability']['availability_check_pass_page'], true ) . 'mdjm_avail=1&mdjm_avail_date=' . $_POST['check_date']; ?>';
						</script>
                        <p>Please wait...</p>
						<?php
						exit;
					}
				}
				else	{
					if( isset( $mdjm_settings['availability']['availability_check_fail_page'] ) && $mdjm_settings['availability']['availability_check_fail_page'] != 'text' )	{
						?>
						<script type="text/javascript">
						window.location = '<?php echo $mdjm->get_link( $mdjm_settings['availability']['availability_check_fail_page'], false ); ?>';
						</script>
						<?php
						exit;
					}	
				}
			} // if( isset( $dj_avail ) )
		}
		
		/* We need the jQuery Calendar */
		wp_enqueue_script('jquery-ui-datepicker');
		wp_enqueue_style('jquery-ui-css', 'http://ajax.googleapis.com/ajax/libs/jqueryui/1.8.2/themes/smoothness/jquery-ui.css');
		?>
		<script type="text/javascript">
		<?php
		mdjm_jquery_datepicker_script( array( 'custom_date', 'check_date' ) );
		?>
        </script>
        <?php
		/* Create the table */
		?>
        <!-- Start of MDJM Availability Checker -->
        <form name="mdjm-availability-check" id="mdjm-availability-check" method="post">
        <?php
        if( isset( $_POST['mdjm_avail_submit'] ) && !empty( $_POST['mdjm_avail_submit'] ) )	{
			$search = array( '{EVENT_DATE}', '{EVENT_DATE_SHORT}' );
			$replace = array( date( 'l, jS F Y', strtotime( $_POST['check_date'] ) ), 
							date( MDJM_SHORTDATE_FORMAT, strtotime( $_POST['check_date'] ) ) );
			if( !empty( $dj_avail['available'] ) && $mdjm_settings['availability']['availability_check_pass_page'] == 'text' && !empty( $mdjm_settings['availability']['availability_check_pass_page'] ) )	{
				echo '<p>' . str_replace( $search,
										  $replace,
										  $mdjm_settings['availability']['availability_check_pass_text'] ) . '</p>';
			}
			if( empty( $dj_avail['available'] ) && $mdjm_settings['availability']['availability_check_fail_page'] == 'text' && !empty( $mdjm_settings['availability']['availability_check_fail_page'] ) )	{
				echo '<p>' . str_replace( $search,
										  $replace,
										  $mdjm_settings['availability']['availability_check_fail_text'] ) . '</p>';
			}
			
		}
		?>
        <p>
        <?php
        if( !isset( $args['label'] ) || empty( $args['label'] ) )	{
			echo 'Select Date:';
			if( isset( $args['label_wrap'] ) && $args['label_wrap'] == 'true' )	{
				echo '<br />';	
			}
		}
		else	{
			echo $args['label'];
			if( isset( $args['label_wrap'] ) && $args['label_wrap'] == 'true' )	{
				echo '<br />';	
			}	
		}
		if( !isset( $args['submit_text'] ) || empty( $args['submit_text'] ) )	{
			$submit_text = 'Check Date';
		}
		else	{
			$submit_text = $args['submit_text'];
		}
		?>
        <input type="text" name="avail_date" id="avail_date" class="custom_date" placeholder="<?php mdjm_jquery_short_date(); ?>" required />
        <?php
		if( isset( $args['field_wrap'] ) && $args['field_wrap'] == 'true' )	{
				echo '<br />';	
			}
		?>
        <input type="hidden" name="check_date" id="check_date" />
		
        <input type="submit" name="mdjm_avail_submit" id="mdjm_avail_submit" value="<?php echo $submit_text; ?>" />
        </form>
        <script type="text/javascript">
        jQuery(document).ready(function($){
			// Configure the field validator
            $('#mdjm-availability-check').validate(
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
        <!-- End of MDJM Availability Checker -->
        <?php
	} // f_mdjm_availability_form
?>