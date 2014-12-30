<?php
/*
* contract.php
* 27/11/2014
* @since 0.8
* Display client contract and enable digital signing
*/
	defined('ABSPATH') or die("Direct access to this page is disabled!!!");

	global $wpdb, $mdjm_options;
	
/* Check if user is logged in */
	if( is_user_logged_in() )	{ // Yes
		if( !isset( $_GET['event_id'] ) || empty( $_GET['event_id'] ) )	{
			echo '<p>You do not have permission to view this page. Please contact the <a href="mailto:' . $mdjm_options['system_email'] . '">website administrator</a> or <a href="' . get_permalink( WPMDJM_CLIENT_HOME_PAGE ) . '">Click here to return to the ' . WPMDJM_CO_NAME . ' ' . WPMDJM_APP_NAME . ' home page.';
		}
		else	{ // Process and Display the contract
			/* Check for form submission & process */
			if( isset( $_POST['submit'] ) )	{ // Form submitted
				$eventinfo = f_mdjm_client_event_by_id( $_GET['event_id'] );
				if( !isset( $_POST['contract_named'], $_POST['contract_accept'] ) || $_POST['contract_named'] != 'Y' || $_POST['contract_accept'] != 'Y' )	{ // Cannot approve
					echo '<p><strong>ERROR: Your contract was not approved. Please ensure you check both the boxes and try again</strong></p>';
				}
				elseif( !isset( $_POST['approver'] ) || empty( $_POST['approver'] ) )	{
					echo '<p><strong>ERROR: An error has occured. Please contact the <a href="mailto:' . $mdjm_options['system_email'] . '">website administrator</a></strong></p>';
				}
				else	{ /* Approve the contract */
					f_mdjm_client_approve_contract( $eventinfo, $_POST );
					
					require_once( WPMDJM_PLUGIN_DIR . '/admin/includes/functions.php' );
					$email_headers = f_mdjm_client_email_headers( $eventinfo, $mdjm_options['contract_email_from'] );
					$info = f_mdjm_prepare_email( $eventinfo, 'email_client_confirm' );
					if( isset( $info['subject'] ) && !empty( $info['subject'] ) && isset( $mdjm_options['title_as_subject'] ) && $mdjm_options['title_as_subject'] == 'Y' )	{
						$subject = $info['subject'];	
					}
					else	{
						$subject = 'Booking Confirmation';	
					}
					if( isset( $mdjm_options['boooking_conf_to_client'] ) && $mdjm_options['boooking_conf_to_client'] == 'Y' )	{
						if( wp_mail( $info['client']->user_email, $subject, $info['content'], $email_headers ) ) 	{
							$j_args = array (
								'client'   => $eventinfo->user_id,
								'event'    => $eventinfo->event_id,
								'author'   => get_current_user_id(),
								'type'     => 'Email Client',
								'source'   => 'Admin',
								'entry'    => 'Booking confirmation email sent to client'
								);
							if( WPDJM_JOURNAL == 'Y' ) f_mdjm_do_journal( $j_args );
							?>
							<p>Your booking confirmation email is on it's way!</p>
							<?php
						} // if( wp_mail ...
					} // if( isset( $mdjm_options['boooking_conf_to_client'] )...
					if( isset( $mdjm_options['boooking_conf_to_dj'] ) && $mdjm_options['boooking_conf_to_dj'] == 'Y' )	{
						$email_headers = f_mdjm_dj_email_headers( $eventinfo->event_dj );
						$info = f_mdjm_prepare_email( $eventinfo, 'email_dj_confirm' );
						wp_mail( $info['dj'], 'DJ Booking Confirmed', $info['content'], $email_headers );
					} // if( isset( $mdjm_options['boooking_conf_to_dj'] )...
				} // else	{ /* Approve the contract */
			} // if( isset( $_POST['submit'] ) )
			
			/* Event information */
			$eventinfo = f_mdjm_client_event_by_id( $_GET['event_id'] );
			if( $eventinfo )	{
				/* Make sure it's the client's contract! */
				$this_user = get_current_user_id();
				if( $eventinfo->user_id != $this_user)
					wp_die( 'Access Denied: An error has occured. Please contact the <a href="mailto:' . $mdjm_options['system_email'] . '">website administrator</a>' );
		
				/* If the event does not have a contract assigned, error */
				if( is_null( $eventinfo->contract ) ) 
					wp_die( 'An error has occured. Please contact the <a href="mailto:' . $mdjm_options['system_email'] . '">website administrator</a>' );
				
				$info['client'] = get_userdata( $eventinfo->user_id );
				$dj = get_userdata( $eventinfo->event_dj );
				
				include( WPMDJM_PLUGIN_DIR . '/admin/includes/config.inc.php' );
				
				if( $eventinfo->contract_status != 'Approved' )	{
					f_mdjm_accept_contract_form( $eventinfo->event_id, true );
				}
				else	{
					f_mdjm_contract_is_signed( $eventinfo );	
				}
				
				/* Contract */
				$contract_query = new WP_Query( array( 'post_type' => 'contract', 'post__in' => array( $eventinfo->contract ) ) );
				if( $contract_query->have_posts() ) {
					while ( $contract_query->have_posts() ) {
						$contract_query->the_post();
						$content = get_the_content();
						$content = apply_filters( 'the_content', $content );
						$content = str_replace(']]>', ']]&gt;', $content);
						$content = str_replace( $shortcode_content_search, $shortcode_content_replace, $content );
						print( $content );
					}
				}
				else	{
					wp_die( 'An error has occured. Please contact the <a href="mailto:' . $mdjm_options['system_email'] . '">website administrator</a>' );	
				}
				if( $eventinfo->contract_status != 'Approved' )	{
					f_mdjm_accept_contract_form( $eventinfo->event_id, true );
				}
			} // if( $eventinfo )
			else	{
				echo '<p>You do not have permission to view this page. Please contact the <a href="mailto:' . $mdjm_options['system_email'] . '">website administrator</a> or <a href="' . get_permalink( WPMDJM_CLIENT_HOME_PAGE ) . '">Click here to return to the ' . WPMDJM_CO_NAME . ' ' . WPMDJM_APP_NAME . ' home page.';	
			}
		}
		
	} // if( is_user_logged_in() )
	else	{
		f_mdjm_show_user_login_form();	
	}
	
	function f_mdjm_accept_contract_form( $event_id, $header )	{
		global $current_user;
		
		$above_below = 'below';
		if( $header == false )	{
			$above_below = 'above';
			echo '<hr>';
		}
		if( $header == true )	{
			echo '<p>Please read and check the contract below including its terms and then check the acceptance box and click Sign Contract to confirm your acceptance.</p>';
		}
		echo '<form name="form-accept-contract" id="form-accept-contract" method="post">';
		echo '<input type="hidden" name="event_id" value="' . $event_id . '">';
		echo '<input type="hidden" name="approver" value="' . $current_user->display_name . '">';
		echo '<p><input type="checkbox" name="contract_named" value="Y">I hereby confirm that I am the person named within the ' . $above_below . ' contract</p>';
		echo '<p><input type="checkbox" name="contract_accept" value="Y">By checking this box I confirm that the contract details are correct and that I accept the terms and conditions within it</p>';
		echo '<p><input type="checkbox" name="deposit" value="Paid">Check this box if you have sent your deposit already. Otherwise if you will be paying shortly, leave it unchecked</p>';
		
		echo '<p><input type="submit" name="submit" value="Accept Contract"></p>';
		echo '</form>';
		if( $header == true ) echo '<hr>';
	} // f_mdjm_accept_contract_form
	
	function f_mdjm_contract_is_signed( $eventinfo )	{
		echo '<p>Your contract has already been signed and accepted. A copy is printed below for your records.</p>';
		echo '<p>Contract signed on ' . date( 'l, jS F Y', strtotime( $eventinfo->contract_approved_date ) ) . ' by ' . $eventinfo->contract_approver . '.</p>';
	}

	/* Print the credit if set */
	add_action( 'wp_footer', 'f_wpmdjm_print_credit' );	
?>