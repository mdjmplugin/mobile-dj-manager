<?php
/**
 * * * * * * * * * * * * * * * MDJM * * * * * * * * * * * * * * *
 * comms.php
 *
 * Enables Admins & DJ's to communication with their clients
 *
 * @since 1.0
 *
 */
	defined('ABSPATH') or die("Direct access to this page is disabled!!!");
	if ( !current_user_can( 'manage_mdjm' ) )  {
		wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
	}
	global $mdjm_options;
	
	function print_notice( $class, $notice )	{
		echo '<div id="message" class="' . $class . '">';
		echo '<p><strong>' . _e( $notice ) . '</strong></p>';
		echo '</div>';
	}
	
	if( isset( $_POST['submit'] ) )	{
		global $current_user;
		get_currentuserinfo();
		if( $_POST['to_field'] == '' )	{
			print_notice( 'error', 'ERROR: No email recipient specified. Your email was not sent' );
		}
		elseif ( !is_email( $_POST['to_field'] ) ) {
			print_notice( 'error', 'ERROR: The email address ' . $_POST['to_field'] . ' appears to be invalid. Your email was not sent' );	
		}
		elseif ( empty( $_POST['email_content'] ) ) {
			print_notice( 'error', 'ERROR: There is no content in your email. Your email was not sent' );	
		}
		else	{
			$info['client'] = get_user_by( 'email', $_POST['to_field'] );
			$info = f_mdjm_client_get_events( $info['client']->ID );
			$info['client'] = get_user_by( 'email', $_POST['to_field'] );
			$eventinfo = f_mdjm_get_eventinfo_by_id( $info['event_id'] );
			$dj = get_userdata( $eventinfo->event_dj );
			$info['dj'] = $dj->user_email;
			
			$email_headers = 'MIME-Version: 1.0'  . "\r\n";
			$email_headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
			$email_headers .= 'From: ' . $dj->display_name . ' <' . get_bloginfo( 'admin_email' ) . '>' . "\r\n";
			$email_headers .= 'Reply-To: ' . $info['dj'] . "\r\n";
			
			if( $_POST['copy_sender'] == 'Y' || $mdjm_options['bcc_admin_to_client'] )	{
				$email_headers .= 'Bcc: ';
				
				if( $_POST['copy_sender'] =='Y' )
					$email_headers .= $current_user->user_email;
				if( $_POST['copy_sender'] =='Y' && $mdjm_options['bcc_admin_to_client'] )	{
					$email_headers .= ', ';	
				}
				if( $mdjm_options['bcc_admin_to_client'] )
					$email_headers .= get_bloginfo( 'admin_email' );
					
				$email_headers .= "\r\n";
			}
			include( WPMDJM_PLUGIN_DIR . '/admin/includes/config.inc.php' );
			$email_content = nl2br( html_entity_decode( stripcslashes( $_POST['email_content'] ) ) );
			$info['content'] = str_replace( $shortcode_content_search, $shortcode_content_replace, $email_content );
			
			if ( wp_mail( $_POST['to_field'], $_POST['subject'], $info['content'], $email_headers ) ) 	{
				$j_args = array (
					'client' => $eventinfo->user_id,
					'event' => $info['event_id'],
					'author' => get_current_user_id(),
					'type' => 'Email Client',
					'source' => 'Admin',
					'entry' => 'Email sent to client with subject "' . $_POST['subject'] . '"'
					);
				if( WPDJM_JOURNAL == 'Y' ) f_mdjm_do_journal( $j_args );
				print_notice( 'updated', 'Your email has been sent successfully' );
			}
		}
	}
	
	function f_mdjm_render_comms( $mdjm_options )	{
		
		if( isset( $_GET['template'] ) && !empty( $_GET['template'] ) )	{
			$content = get_option( 'mdjm_plugin_email_template_' . $_GET['template'] );
		}
		elseif( isset( $_POST['content'] ) )	{
			$content = $_POST['content'];	
		}
		else	{
			$content = '';	
		}
		
		?>
		<div class="wrap">
		<h2>Client Communications</h2>
		<?php
		include( WPMDJM_PLUGIN_DIR . '/admin/includes/config.inc.php' );
		$settings = array(  'media_buttons' => false,
							'textarea_rows' => '10',
						 );
		$clientinfo = f_mdjm_get_clients( 'display_name', 'ASC' );
		?>
		<script type="text/javascript">
			function MM_jumpMenu(targ,selObj,restore){ //v3.0
			  eval(targ+".location='"+selObj.options[selObj.selectedIndex].value+"'");
			  if (restore) selObj.selectedIndex=0;
			}
		</script>
        <script type="text/javascript">
		function mdjm_selection_change()
		{
			var e = document.getElementById("to_drop");
			var str = e.options[e.selectedIndex].value;
		
			document.getElementById('to_field').value = str;
		}
		</script>
		<form name="form-email-template" id="form-email-template" method="post">
		<table class="form-table">
		<tr>
		<td width="20%"><label for="email_template">Select a template for content, or write your own:</label></td>
		<td><select name="email_template" id="email_template" onChange="MM_jumpMenu('parent',this,0)">
		<option value="<?php echo admin_url( 'admin.php?page=mdjm-comms' ); ?>" <?php if( !$_GET['template'] ) echo ' selected'; ?>>Do not use Template</option>
		<option value="<?php echo admin_url( 'admin.php?page=mdjm-comms&template=enquiry' ); ?>" <?php if( $_GET['template'] == 'enquiry' ) echo ' selected'; ?>>Enquiry</option>
		<option value="<?php echo admin_url( 'admin.php?page=mdjm-comms&template=contract_review' ); ?>" <?php if( $_GET['template'] == 'contract_review' ) echo ' selected'; ?>>Contract Review</option>
		<option value="<?php echo admin_url( 'admin.php?page=mdjm-comms&template=client_booking_confirm' ); ?>" <?php if( $_GET['template'] == 'client_booking_confirm' ) echo ' selected'; ?>>Client Booking Confirmation</option>
		<option value="<?php echo admin_url( 'admin.php?page=mdjm-comms&template=dj_booking_confirm' ); ?>" <?php if( $_GET['template'] == 'dj_booking_confirm' ) echo ' selected'; ?>>DJ Booking Confirmation</option>
		</select></td>
		</tr>
		</table>
		<hr />
		<?php wp_nonce_field( 'send-email' ); ?>
		<table width="100%" border="0" cellspacing="0" cellpadding="0" class="widefat">
		<tr>
		<td width="60%"><table width="100%" border="0" cellspacing="0" cellpadding="0" class="widefat">
		<tr class="alternate">
		<th scope="row" align="left"><label for="to_field">Send email to:</label></th>
		<td><select name="to_drop" id="to_drop" onchange="mdjm_selection_change();">
			<option value="">Select a Recipient</option>
		<?php
		foreach( $clientinfo as $client )	{
			if( !current_user_can( 'administrator' ) )	{ // Non-Admins only see their own clients
				if( f_mdjm_client_is_mine( $client->ID ) )	{
					$info = f_mdjm_client_get_events( $client->ID );
				}
			}
			else	{
				$info = f_mdjm_client_get_events( $client->ID );
			}
			?>
			<option value="<?php echo $client->user_email; ?>"<?php selected( $_POST['to_field'], $client->user_email ); ?>><?php echo $client->display_name; ?></option>
			<?php
		}
		?>
		</select><br />
		<input type="email" name="to_field" id="to_field" value="" class="regular-text" readonly="readonly" /> <label for="copy_sender">and copy yourself?</label> <input type="checkbox" name="copy_sender" id="copy_sender" value="Y" <?php checked( 'Y', $mdjm_options['bcc_dj_to_client'] ); ?> /></td>
		</tr>
		<tr class="alternate">
		<th scope="row" align="left"><label for="subject">Subject:</label></th>
		<td><input type="text" name="subject" id="subject" class="regular-text" value="<?php echo $_POST['subject']; ?>" /></td>
		</tr>
		<tr>
		<td colspan="2"><?php wp_editor( html_entity_decode( stripcslashes( $content ) ), 'email_content', $settings ); ?></td>
		</tr>
		<tr>
		<td colspan="2"><?php submit_button( 'Send Email', 'primary', 'submit', true ); ?></td>
		</tr>
		</table></td>
		<td valign="top"><table width="100%" border="0" cellspacing="0" cellpadding="0" class="widefat">
		<tr>
		<td align="center" class="alternate"><strong>To do List</strong></td>
		</tr>
		<tr>
		<td>Coming soon...!</td>
		</tr>
		</table></td>
		</tr>
		</table>
		 </form>
		</div>
	<?php
	} // f_mdjm_render_comms
	
	f_mdjm_render_comms( $mdjm_options );