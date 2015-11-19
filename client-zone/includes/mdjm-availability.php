<?php
/**
 * Class: MDJM_Availability_Checker
 * Description: The Availability checker class for the front end
 * 
 * 
 * 
 */
if( !class_exists( 'MDJM_Availability_Checker' ) ) :
	class MDJM_Availability_Checker	{
		/**
		 * Ajax
		 */
		private static $ajax;
		/**
		 * The availability array
		 */
		private static $dj_avail;
		/**
		 * Initialise the class
		 *
		 *
		 *
		 *
		 */
		function __construct()	{
			global $mdjm_settings;
			
			self::$ajax = !empty( $mdjm_settings['availability']['avail_ajax'] ) ? true : false;
			
			add_action( 'wp_head', array( __CLASS__, 'ajax_in_head' ) );
			
			add_action( 'template_redirect', array( __CLASS__, 'check_availability' ) );
			
			add_action( 'wp_enqueue_scripts', array( __CLASS__, 'enqueue_scripts' ) );		
		} // __construct
		
		/**
		 * Enqueue the scripts we need for availability checker
		 *
		 * @params
		 *
		 * @return
		 */
		function enqueue_scripts()	{
			wp_enqueue_script( 'jquery-ui-datepicker' );
			wp_enqueue_style(
				'jquery-ui-css',
				'http://ajax.googleapis.com/ajax/libs/jqueryui/1.8.2/themes/smoothness/jquery-ui.css' );		
		} // enqueue_scripts
		
		/**
		 * Inserts the jQuery for AJAX lookups into the <head> tag
		 *
		 *
		 *
		 *
		 */
		function ajax_in_head()	{
			global $mdjm, $mdjm_settings;
				
			if( $mdjm_settings['availability']['availability_check_pass_page'] != 'text' )
				$pass_redirect = true;
				
			if( $mdjm_settings['availability']['availability_check_fail_page'] != 'text' )
				$fail_redirect = true;
											
			?>
            <script type="text/javascript">
			jQuery(document).ready(function($) 	{
				$('#mdjm-availability-check').submit(function()	{
					if( !$("#check_date").val() )	{
						return false;
					}
					event.preventDefault ? event.preventDefault() : (event.returnValue = false);
					var check_date = $("#check_date").val();
					$.ajax({
						type: "POST",
						dataType: "json",
						url: "<?php echo admin_url( 'admin-ajax.php' ); ?>",
						data: {
							check_date : check_date,
							action : "mdjm_availability_by_ajax"
						},
						beforeSend: function()	{
							$('input[type="submit"]').prop('disabled', true);
							$("#pleasewait").show();
						},
						success: function(response)	{
							if(response.result == "available") {
								<?php
								if( !empty( $pass_redirect ) )	{
									?>
									window.location.href = '<?php echo $mdjm->get_link( $mdjm_settings['availability']['availability_check_pass_page'], true ); ?>';
									<?php
								}
								else	{
									?>
									$("#availability_result").replaceWith('<div id="availability_result">' + response.message + '</div>');
									$("#mdjm_avail_submit").fadeTo("slow", 1);
									$("#mdjm_avail_submit").removeClass( "mdjm-updating" );
									$("#pleasewait").hide();
									<?php
								}
								?>
								$('input[type="submit"]').prop('disabled', false);
							}
							else	{
								<?php
								if( !empty( $fail_redirect ) )	{
									?>
									window.location.href = '<?php echo $mdjm->get_link( $mdjm_settings['availability']['availability_check_fail_page'], true ); ?>';
									<?php
								}
								else	{
									?>
									$("#availability_result").replaceWith('<div id="availability_result">' + response.message + '</div>');
									$("#mdjm_avail_submit").fadeTo("slow", 1);
									$("#mdjm_avail_submit").removeClass( "mdjm-updating" );
									$("#pleasewait").hide();
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
		} // ajax_in_head
		
		/**
		 * Execute the availability check
		 *
		 * @params
		 *
		 * @return
		 */
		function check_availability()	{
			global $mdjm, $mdjm_settings;
			$GLOBALS['mdjm_debug']->log_it( 'Availability check' );
			if( !isset( $_POST['mdjm_avail_submit'] ) || !isset( $_POST['check_date'] ) )
				return;
				
			self::$dj_avail = dj_available( '', $_POST['check_date'] );
			
			if( isset( self::$dj_avail ) )	{
				// Available & redirect activatated
				if( !empty( self::$dj_avail['available'] ) && 
					isset( $mdjm_settings['availability']['availability_check_pass_page'] ) && 
					$mdjm_settings['availability']['availability_check_pass_page'] != 'text' )	{
					
					wp_redirect( $mdjm->get_link( $mdjm_settings['availability']['availability_check_pass_page'] ) . 'mdjm_avail=1&mdjm_avail_date=' . $_POST['check_date'] );
					exit;	
				}
				
				// Unavailable & redirect activatated
				else	{
					if( isset( $mdjm_settings['availability']['availability_check_fail_page'] ) && 
					$mdjm_settings['availability']['availability_check_fail_page'] != 'text' )	{
						wp_redirect( $mdjm->get_link( $mdjm_settings['availability']['availability_check_fail_page'] ) );
						exit;
					}
				}
			}
		} // check_availability
		
		/**
		 * The Availability checker form
		 *
		 * @params
		 *
		 * @return
		 */
		function availability_form()	{
			global $mdjm_settings;
			
			/**
			 * Initialise the datepicker script
			 */
			?>
            <script type="text/javascript">
            <?php mdjm_jquery_datepicker_script( array( 'custom_date', 'check_date', '', 'today' ) ); ?>
			</script>
			<?php
			echo '<!-- ' . __( 'MDJM Availability Checker', 'mobile-dj-manager' ) . ' (' . MDJM_VERSION_NUM . ') -->';
			
			/**
			 * If we are not using AJAX and a check has been performed and we're displaying text results
			 */
			if( self::$ajax == true )	{
				?>
                <div id="availability_result"></div>
                <?php
			}
			else
				self::display_result();
			
			/**
			 * Now display the availability checker form
			 */
			?>
            <form name="mdjm-availability-check" id="mdjm-availability-check" method="post">
			<p><?php _e( 'Select Date:', 'mobile-dj-manager' ); ?><br>
			<input type="text" name="avail_date" id="avail_date" class="custom_date" placeholder="<?php mdjm_jquery_short_date(); ?>" readonly required /><br>
            <input type="hidden" name="check_date" id="check_date" />
		
            <input type="submit" name="mdjm_avail_submit" id="mdjm_avail_submit" value="<?php _e( 'Check Date', 'mobile-dj-manager' ); ?>" />
            <span id="pleasewait" class="page-content" id="loader" style="display: none;"><?php _e( 'Please wait...', 'mobile-dj-manager' ); ?><img src="/wp-admin/images/loading.gif" /></span>
            </form>
			
			<?php
			self::validate();
			echo '<!-- ' . __( 'MDJM Availability Checker', 'mobile-dj-manager' ) . ' (' . MDJM_VERSION_NUM . ') -->';
		} // availability_form
		
		/**
		 * Insert the validation script
		 *
		 * @param
		 *
		 * @return
		 */
		function validate()	{
			?>
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
									required: "<?php _e( 'Please enter a date', 'mobile-dj-manager' ); ?>",
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
		} // validate
		
		/**
		 * Display the availability result by text
		 *
		 * @params
		 *
		 * @return
		 */
		function display_result()	{
			global $mdjm_settings;
			
			if( !empty( $_POST['mdjm_avail_submit'] ) )	{
				$search = array( '{EVENT_DATE}', '{EVENT_DATE_SHORT}' );
				$replace = array( 
					date( 'l, jS F Y', strtotime( $_POST['check_date'] ) ), 
					date( MDJM_SHORTDATE_FORMAT, strtotime( $_POST['check_date'] ) ) );
				
				/**
				 * Check executed, availability, display text
				 */
				if( !empty( self::$dj_avail['available'] ) && 
					$mdjm_settings['availability']['availability_check_pass_page'] == 'text' && 
					!empty( $mdjm_settings['availability']['availability_check_pass_text'] ) )	{	
					
					echo '<p>';
					echo str_replace( 
							$search,
							$replace,
							$mdjm_settings['availability']['availability_check_pass_text'] );
					echo '</p>';
				}
				/**
				 * Check executed, no availability, display text
				 */
				if( empty( self::$dj_avail['available'] ) && 
					$mdjm_settings['availability']['availability_check_fail_page'] == 'text' && 
					!empty( $mdjm_settings['availability']['availability_check_fail_text'] ) )	{
					
					echo '<p>';
					echo str_replace(
							$search,
							$replace,
							$mdjm_settings['availability']['availability_check_fail_text'] );
					echo '</p>';
				}
			}
		} // display_result
	} // class MDJM_Availability_Checker
endif;
	new MDJM_Availability_Checker();