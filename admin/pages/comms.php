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
	
	// If recently updated, display the release notes
	f_mdjm_has_updated();
	
	global $mdjm_options;
	
	if( isset( $_POST['submit'] ) )	{
		/* Validation */
		$mdjm_permissions = get_option( 'mdjm_plugin_permissions' );
		if( is_dj() && isset( $mdjm_permissions['dj_disable_shortcode'] ) && !empty( $mdjm_permissions['dj_disable_shortcode'] ) )	{ // Check shortcodes that DJ's cannot use
			if( !is_array( $mdjm_permissions['dj_disable_shortcode'] ) )	{
				$mdjm_permissions['dj_disable_shortcode'] = array( $mdjm_permissions['dj_disable_shortcode'] );	
			}
				foreach( $mdjm_permissions['dj_disable_shortcode'] as $disabled_shortcode )	{	
					if( strpos( $_POST['email_content'], $disabled_shortcode ) !== false ) {
						$disabled_shortcodes = $disabled_shortcodes . ' ' . $disabled_shortcode;
					}
				}
		}
		if( isset( $disabled_shortcodes ) )	{
			$class = 'error';
			$message = '<strong>ERROR</strong>: Your Administrator has disabled the use of the Shortcodes <strong>' . $disabled_shortcodes . '</strong>. Please adjust your content and try again';
			f_mdjm_update_notice( $class, $message );	
		}
		elseif( !isset( $_POST['email_to'] ) || $_POST['email_to'] == '' )	{
			$class = 'error';
			$message = '<strong>ERROR</strong>: No email recipient specified. Your email was not sent';
			f_mdjm_update_notice( $class, $message );
		}
		elseif( !is_email( $_POST['user_addr'] ) ) {
			$class = 'error';
			$message = '<strong>ERROR</strong>: The email address ' . $_POST['user_addr'] . ' appears to be invalid. Your email was not sent';
			f_mdjm_update_notice( $class, $message );
		}
		elseif( !isset( $_POST['email_content'] ) || empty( $_POST['email_content'] ) ) {
			$class = 'error';
			$message = '<strong>ERROR</strong>: There is no content in your email. Your email was not sent';
			f_mdjm_update_notice( $class, $message );
		}
		/* Process */
		else	{
			$current_user = wp_get_current_user();
			$info['recipient'] = get_userdata( $_POST['email_to'] );
			if( isset( $_POST['event'] ) && !empty( $_POST['event'] ) && $_POST['event'] != 'general' )	{
				$eventinfo = f_mdjm_get_eventinfo_by_id( $_POST['event'] );
				$info['client'] = get_userdata( $eventinfo->user_id );
				$dj = get_userdata( $eventinfo->event_dj );
			}
			elseif( f_mdjm_is_client( $_POST['email_to'] ) )	{
				$info['client'] = get_userdata( $_POST['email_to'] );
			}
			else	{
				$info['client'] = '';	
			}
			$email_headers = 'MIME-Version: 1.0' . "\r\n";
			$email_headers .= 'Content-type: text/html; charset=UTF-8' . "\r\n";
			$email_headers .= 'From: ' . $current_user->display_name . ' <' . $current_user->user_email . '>' . "\r\n";
			
			/* Check for sender copy */
			if( isset( $_POST['copy_sender'] ) && $_POST['copy_sender'] == 'Y' )	{
				$email_headers .= 'Cc: ' . $current_user->user_email . "\r\n";
			}
			
			/* Check for BCC */
			if( isset( $mdjm_options['bcc_admin_to_client'] ) && $mdjm_options['bcc_admin_to_client'] == 'Y'
				|| isset( $mdjm_options['bcc_dj_to_client'] ) && $mdjm_options['bcc_dj_to_client'] == 'Y' )	{
				
				if( isset( $mdjm_options['bcc_dj_to_client'] ) && $mdjm_options['bcc_dj_to_client'] == 'Y' )
					$bcc[] = $dj->user_email;
	
				if( isset( $mdjm_options['bcc_admin_to_client'] ) && $mdjm_options['bcc_admin_to_client'] == 'Y' )
					$bcc[] = $mdjm_options['system_email'];
				
				$email_headers .= 'Bcc: ' . implode( ",", $bcc ) . "\r\n";
			}
			
			$email_content = nl2br( html_entity_decode( stripcslashes( $_POST['email_content'] ) ) );
			$info['content'] = '<html><body>';
			if( $eventinfo )	{
				include( WPMDJM_PLUGIN_DIR . '/admin/includes/config.inc.php' );
				$info['content'] .= str_replace( $shortcode_content_search, $shortcode_content_replace, $email_content );
				$subject .= str_replace( $shortcode_content_search, $shortcode_content_replace, $_POST['subject'] );
			}
			else	{
				$subject = stripslashes( $_POST['subject'] );
			}
			$info['content'] .= '</html></body>';
			
			if( wp_mail( $info['recipient']->user_email, $subject, $info['content'], $email_headers ) ) 	{
				$class = 'updated';
				$message = 'Your email has been sent successfully';
			}
			if( WPDJM_JOURNAL == 'Y' )	{
				if( $eventinfo && f_mdjm_is_client( $_POST['email_to'] ) )	{
					$j_args = array (
						'client'   => $eventinfo->user_id,
						'event'    => $eventinfo->event_id,
						'author'   => get_current_user_id(),
						'type'     => 'Email Client',
						'source'   => 'Admin',
						'entry'    => 'Email sent to client with subject "' . $_POST['subject'] . '"'
						);
				}
				elseif( $eventinfo && !f_mdjm_is_client( $_POST['email_to'] ) )	{
					$j_args = array (
						'client'   => $eventinfo->user_id,
						'event'    => $eventinfo->event_id,
						'author'   => get_current_user_id(),
						'type'     => 'Email DJ',
						'source'   => 'Admin',
						'entry'    => 'Email sent to DJ with subject "' . $_POST['subject'] . '"'
						);
				}
				else	{
					$j_args = array (
						'client'   => $_POST['email_to'],
						'event'    => '',
						'author'   => get_current_user_id(),
						'type'     => 'Email',
						'source'   => 'Admin',
						'entry'    => 'Email sent with subject "' . $_POST['subject'] . '"'
						);	
				}
				f_mdjm_do_journal( $j_args );
			}
			f_mdjm_update_notice( $class, $message );
		}
	}
	
	function f_mdjm_render_comms( $mdjm_options )	{
		if( isset( $_GET['template'] ) && !empty( $_GET['template'] ) )	{
			$template_query = new WP_Query( array( 'post_type' => array( 'email_template', 'contract' ), 'p' => $_GET['template'] ) );
			if ( $template_query->have_posts() ) {
				while ( $template_query->have_posts() ) {
					$template_query->the_post();
					$content = get_the_content();
					$content = apply_filters( 'the_content', $content );
					$content = str_replace(']]>', ']]&gt;', $content);
					$subject = get_the_title();
				}
			}
		}
		elseif( isset( $_POST['email_content'] ) )	{
			$content = $_POST['email_content'];
		}
		else	{
			$content = '';	
		}
		if( !isset( $subject ) || empty( $subject ) )	{
			if( isset( $_POST['subject'] ) )	{
				$subject = $_POST['subject'];
			}
			else	{
				$subject = '';	
			}
		}
		
		?>
		<div class="wrap">
		<h2>Client Communications</h2>
		<?php
		include( WPMDJM_PLUGIN_DIR . '/admin/includes/config.inc.php' );
		$settings = array(  'media_buttons' => false,
							'textarea_rows' => '10',
						 );
		$clientinfo = f_mdjm_get_clients( 'client', 'display_name', 'ASC' );
		$djinfo = f_mdjm_get_djs();
		?>
		<script type="text/javascript">
			function MM_jumpMenu(targ,selObj,restore){ //v3.0
			  eval(targ+".location='"+selObj.options[selObj.selectedIndex].value+"'");
			  if (restore) selObj.selectedIndex=0;
			}
		</script>
		<form name="form-email-template" id="form-email-template" method="post">
		<table class="form-table">
		<tr>
		<td width="20%"><label for="email_template">Select a template for content, or write your own:</label></td>
		<td><select name="email_template" id="email_template" onChange="MM_jumpMenu('parent',this,0)">
		<option value="<?php echo add_query_arg( array( 'template' => '0' ) ); ?>" <?php if( !isset( $_GET['template'] ) || $_GET['template'] == '0' ) echo ' selected'; ?>>Do not use Template</option>
        <?php
		$email_args = array(
								'post_type' => 'email_template',
								'orderby' => 'name',
								'order' => 'ASC',
								);
		$contract_args = array(
								'post_type' => 'contract',
								'orderby' => 'name',
								'order' => 'ASC',
								);
		if( is_dj() )	{ // Check templates that DJ's cannot use
			if( !isset( $mdjm_permissions ) )	{
				$mdjm_permissions = get_option( 'mdjm_plugin_permissions' );
			}
			if( isset( $mdjm_permissions['dj_disable_template'] ) && !empty( $mdjm_permissions['dj_disable_template'] ) )	{
				if( !is_array( $mdjm_permissions['dj_disable_template'] ) )	{
					$mdjm_permissions['dj_disable_template'] = array( $mdjm_permissions['dj_disable_template'] );	
				}
				$email_args['post__not_in'] = $mdjm_permissions['dj_disable_template'];
				$contract_args['post__not_in'] = $mdjm_permissions['dj_disable_template'];
			}
			
		}
			$email_query = new WP_Query( $email_args );
			if ( $email_query->have_posts() ) {
				?><option value="email_templates" disabled>--- EMAIL TEMPLATES ---</option><?php
				while ( $email_query->have_posts() ) {
					$email_query->the_post();
					?>
					<option value="<?php echo add_query_arg( array( 'template' => get_the_id() ) ); ?>"<?php if( isset( $_GET['template'] ) ) { selected( get_the_id(), $_GET['template'] ); } ?>><?php echo get_the_title(); ?></option>
                    <?php
				}
			}
			wp_reset_postdata();
			$contract_query = new WP_Query( $contract_args );
			if ( $contract_query->have_posts() ) {
				?><option value="contracts" disabled>--- CONTRACTS ---</option><?php
				while ( $contract_query->have_posts() ) {
					$contract_query->the_post();
					?>
					<option value="<?php echo add_query_arg( array( 'template' => get_the_id() ) ); ?>"<?php if( isset( $_GET['template'] ) ) { selected( get_the_id(), $_GET['template'] ); } ?>><?php echo get_the_title(); ?></option>
                    <?php
				}
			}
			wp_reset_postdata();
			
		?>
		</select></td>
		</tr>
		</table>
		<hr />
		<?php wp_nonce_field( 'send-email' ); ?>
		<table width="100%" border="0" cellspacing="0" cellpadding="0" class="widefat">
		<tr>
		<td width="60%"><table width="100%" border="0" cellspacing="0" cellpadding="0" class="widefat">
		<tr class="alternate">
		<th class="row-title" align="left"><label for="to">Send email to:</label></th>
		<td><select name="to" id="to" onChange="MM_jumpMenu('parent',this,0)">
			<option value="">Select a Recipient</option>
		<?php
		foreach( $clientinfo as $client )	{
			if( current_user_can( 'administrator' ) || f_mdjm_client_is_mine( $client->ID ) )	{ // Non-Admins only see their own clients
				?>
				<option value="<?php echo add_query_arg( array( 'to_user' => $client->ID ) ); ?>"<?php if( isset( $_GET['to_user'] ) ) { selected( $client->ID, $_GET['to_user'] ); } ?>>[CLIENT]: <?php echo $client->display_name; ?></option>
                <?php
			}
		}
		if( current_user_can( 'administrator' ) )	{ // Admins see DJ's too
			foreach( $djinfo as $dj )	{
				?>
                <option value="<?php echo add_query_arg( array( 'to_user' => $dj->ID ) ); ?>"<?php if( isset( $_GET['to_user'] ) ) { selected( $dj->ID, $_GET['to_user'] ); } ?>>[DJ]: <?php echo $dj->display_name; ?></option>
                <?php	
			}
		}
		?>
		</select>
        </td>
        </tr>
        <?php
		if( isset( $_GET['to_user'] ) && $_GET['to_user'] != '' )	{
			echo '<input type="hidden" name="email_to" value="' . $_GET['to_user'] . '" />';	
		}
		?>
        <tr class="alternate">
        <th class="row-title">&nbsp;</th>
        <td>
        <?php
		/* Get this users info */
		if( isset( $_GET['to_user'] ) && $_GET['to_user'] != '' )	{
			$userinfo = get_user_by( 'id', $_GET['to_user'] );	
		}
		?>
		<input type="email" name="user_addr" id="user_addr" value="<?php if( isset( $userinfo ) && $userinfo != '' ) echo $userinfo->user_email; ?>" class="regular-text" readonly="readonly" /> </td>
        </tr>
        <tr class="alternate">
        <th class="row-title"><label for="copy_sender">Copy yourself?</label></th>
        <td><input type="checkbox" name="copy_sender" id="copy_sender" value="Y" checked="checked" /> <span class="description">Depending on your <a href="<?php f_mdjm_admin_page( 'settings' ); ?>">settings</a>, the DJ and Admin may also receive a copy</span></td>
        </tr>
        <?php
		if( isset( $_GET['to_user'] ) )	{
			if( user_can( $_GET['to_user'], 'dj' ) )	{ // Selected user is a DJ
				$events = f_mdjm_get_dj_events( $_GET['to_user'] );
			}
			else	{
				$events = f_mdjm_admin_get_client_events( $_GET['to_user'] );
			}
		}
		?>
        <tr class="alternate">
		<th class="row-title" align="left"><label for="event">Regarding Event:</label></th>
		<td>
        <?php
		if( !isset( $events ) || !$events )	{
			?>
            <input type="text" name="event" class="regular-text" value="No Event (General Message)" disabled="disabled" />
            <?php	
		}
		else	{
			?>
			<select name="event" id="event">
			<option value="general"<?php if( isset( $_POST['event'] ) ) { selected( $_POST['event'], 'general' ); } ?>>No Event (General Message)</option>
            <?php
			foreach( $events as $event )	{
				?>
				<option value="<?php echo $event->event_id; ?>"<?php if( isset( $_POST['event'] ) ) { selected( $_POST['event'], $event->event_id ); } ?>><?php echo date( 'd/m/Y', strtotime( $event->event_date ) ) . ' from ' . date( $mdjm_options['time_format'], strtotime( $event->event_start ) ) . ' (' . $event->contract_status . ')'; ?></option>
				<?php
			}
			echo '</select>';
		}
		?>
        <br />
        <span class="description">Note: If no event is selected you cannot use MDJM Shortcodes in your email</span>
        </td>
		</tr>
        <?php
		?>
		<tr class="alternate">
		<th class="row-title" align="left"><label for="subject">Subject:</label></th>
		<td><input type="text" name="subject" id="subject" class="regular-text" value="<?php echo $subject; ?>" /></td>
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