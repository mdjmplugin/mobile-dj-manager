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
		
	global $mdjm, $mdjm_posts, $mdjm_settings, $current_user;
	
	f_mdjm_has_updated();
	
	if( isset( $_POST['submit'] ) )	{
		/* Validation */
		if( is_dj() && isset( $mdjm_settings['permissions']['dj_disable_shortcode'] ) && !empty( $mdjm_settings['permissions']['dj_disable_shortcode'] ) )	{ // Check shortcodes that DJ's cannot use
			if( !is_array( $mdjm_settings['permissions']['dj_disable_shortcode'] ) )	{
				$mdjm_settings['permissions']['dj_disable_shortcode'] = array( $mdjm_settings['permissions']['dj_disable_shortcode'] );	
			}
			foreach( $mdjm_settings['permissions']['dj_disable_shortcode'] as $disabled_shortcode )	{	
				if( strpos( $_POST['email_content'], $disabled_shortcode ) !== false ) {
					$disabled_shortcodes = $disabled_shortcodes . ' ' . $disabled_shortcode;
				}
			}
		}
		if( isset( $disabled_shortcodes ) )	{
			$class = 'error';
			$message = '<strong>ERROR</strong>: Your Administrator has disabled the use of the Shortcodes <strong>' . $disabled_shortcodes . '</strong>. Please adjust your content and try again';
			mdjm_update_notice( $class, $message );	
		}
		elseif( !isset( $_POST['email_to'] ) || $_POST['email_to'] == '' )	{
			$class = 'error';
			$message = '<strong>ERROR</strong>: No email recipient specified. Your email was not sent';
			mdjm_update_notice( $class, $message );
		}
		elseif( !is_email( $_POST['user_addr'] ) ) {
			$class = 'error';
			$message = '<strong>ERROR</strong>: The email address ' . $_POST['user_addr'] . ' appears to be invalid. Your email was not sent';
			mdjm_update_notice( $class, $message );
		}
		elseif( !isset( $_POST['email_content'] ) || empty( $_POST['email_content'] ) ) {
			$class = 'error';
			$message = '<strong>ERROR</strong>: There is no content in your email. Your email was not sent';
			mdjm_update_notice( $class, $message );
		}
		/* Process */
		else	{
			/* -- Build the email arguments -- */
			$email_args = array(
							'content'	=> nl2br( str_replace( ']]>', ']]&gt;', $_POST['email_content'] ) ),
							'to'		=> $_POST['email_to'],
							'subject'	=> $_POST['subject'],
							'from'		=> $current_user->ID,
							'source'	=> 'Communication Feature',
							'html'		=> true,
							);
			if( !empty( $_POST['event'] ) )	{
				$email_args['event_id'] = $_POST['event'];
				$email_args['journal'] = ( user_can( $_POST['email_to'], 'client' ) || user_can( $_POST['email_to'], 'inactive_client' ) ? 'email-client' : 'email-dj' );
			}
				
			if( is_dj() && $_POST['copy_sender'] == 'Y' )
				$email_args['cc_dj'] = true;
				
			if( current_user_can( 'administrator' ) && $_POST['copy_sender'] == 'Y' )
				$email_args['cc_admin'] = true;
			
			// Send the email					
			$success = $mdjm->send_email( $email_args );
			
			if( !empty( $success ) )	{
				$class = 'updated';
				
				$recipient = get_userdata( $_POST['email_to'] );
				
				$message = 'Message successfully sent to ' . $recipient->display_name . '. ' .  
				'<a href="' . get_edit_post_link( $success ) . '">View message' . '</a>';
			}
			else	{
				$class = 'error';
				$message = 'There was an error sending your message. Please try again';	
			}
			
			mdjm_update_notice( $class, $message );
			
			/* -- Process Unavailability Reponses -- */
			if( isset( $_POST['respond_unavailable'] ) && !empty( $_POST['respond_unavailable'] ) )	{
				if( $mdjm->mdjm_events->reject_event( $_POST['event'], $current_user->ID, 'Unavailable' ) )	{
					$class = 'updated';
					$message = 'The selected enquiry has been marked as rejected due to unavailability. ' . 
					'<a href="' . mdjm_get_admin_page( 'enquiries' ) . '">View Enquiries</a>';
				}
				else	{
					$class = 'error';
					$message = 'Unable mark event as rejected due to unavailability';
				}
				mdjm_update_notice( $class, $message );
			}
		}
	}
	
	function f_mdjm_render_comms()	{
		global $mdjm, $mdjm_settings;
		if( isset( $_GET['template'] ) && !empty( $_GET['template'] ) )	{
			$template_query = get_post( $_GET['template'] );
			
			if( $template_query ) {
				$content = $template_query->post_content;
				$content = apply_filters( 'the_content', $content );
				$content = str_replace(']]>', ']]&gt;', $content);
				$subject = get_the_title( $_GET['template'] );
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
		<h1>Client Communications</h1>
		<?php
		$settings = array(  'media_buttons' => true,
							'textarea_rows' => '10',
						 );
		$clientinfo = $mdjm->mdjm_events->get_clients();
		$djinfo = mdjm_get_djs();
		?>
		<script type="text/javascript">
			function MM_jumpMenu(targ,selObj,restore){ //v3.0
			  eval(targ+".location='"+selObj.options[selObj.selectedIndex].value+"'");
			  if (restore) selObj.selectedIndex=0;
			}
		</script>
		<form name="form-email-template" id="form-email-template" method="post">
        <?php
		if( isset( $_GET['action'] ) && $_GET['action'] == 'respond_unavailable' )	{
			?><input type="hidden" name="respond_unavailable" id="respond_unavailable" value="<?php echo $_GET['event_id']; ?>" /><?php	
		}
		?>
		<table class="form-table">
		<tr>
		<td width="20%"><label for="email_template">Select a template for content, or write your own:</label></td>
		<td><select name="email_template" id="email_template" onChange="MM_jumpMenu('parent',this,0)">
		<option value="<?php echo add_query_arg( 'template', 0 ); ?>" <?php if( !isset( $_GET['template'] ) || $_GET['template'] == '0' ) echo ' selected'; ?>>Do not use Template</option>
        <?php
		$email_args = array(
								'posts_per_page'	=> -1,
								'post_type'			=> MDJM_EMAIL_POSTS,
								'orderby'			=> 'name',
								'order'				=> 'ASC',
								);
		$contract_args = array(
								'post_type'			=> MDJM_CONTRACT_POSTS,
								'posts_per_page'	=> -1,
								'orderby'			=> 'name',
								'order'				=> 'ASC',
								);
		if( is_dj() )	{ // Check templates that DJ's cannot use
			if( !isset( $mdjm_settings['permissions'] ) )	{
				$mdjm_settings['permissions'] = get_option( MDJM_PERMISSIONS_KEY );
			}
			if( isset( $mdjm_settings['permissions']['dj_disable_template'] ) && !empty( $mdjm_settings['permissions']['dj_disable_template'] ) )	{
				if( !is_array( $mdjm_settings['permissions']['dj_disable_template'] ) )	{
					$mdjm_settings['permissions']['dj_disable_template'] = array( $mdjm_settings['permissions']['dj_disable_template'] );	
				}
				$email_args['post__not_in'] = $mdjm_settings['permissions']['dj_disable_template'];
				$contract_args['post__not_in'] = $mdjm_settings['permissions']['dj_disable_template'];
			}
			
		}
			$email_query = get_posts( $email_args );
			if( $email_query ) {
				?><optgroup label="EMAIL TEMPLATES"><?php
				foreach( $email_query as $email_template ) {
					?>
					<option value="<?php echo add_query_arg( 'template', $email_template->ID ); ?>"<?php if( isset( $_GET['template'] ) ) { selected( $email_template->ID, $_GET['template'] ); } ?>><?php echo get_the_title( $email_template->ID ); ?></option>
                    <?php
				}
				?>
                </optgroup>
                <?php
			}
			$contract_query = get_posts( $contract_args );
			if( $contract_query ) {
				?><optgroup label="CONTRACTS"><?php
				foreach ( $contract_query as $contract_template ) {
					?>
					<option value="<?php echo add_query_arg( 'template', $contract_template->ID ); ?>"<?php if( isset( $_GET['template'] ) ) { selected( $contract_template->ID, $_GET['template'] ); } ?>><?php echo get_the_title( $contract_template->ID ); ?></option>
                    <?php
				}
				?>
                </optgroup>
                <?php
			}
			
		?>
		</select></td>
		</tr>
		</table>
		<hr />
		<?php wp_nonce_field( 'send-email', '__mdjm_send_email' ); ?>
		<table width="100%" border="0" cellspacing="0" cellpadding="0" class="widefat">
		<tr>
		<td width="60%"><table width="100%" border="0" cellspacing="0" cellpadding="0" class="widefat">
		<tr class="alternate">
		<th class="row-title" align="left"><label for="to">Send email to:</label></th>
		<td><select name="to" id="to" onChange="MM_jumpMenu('parent',this,0)">
			<option value="">Select a Recipient</option>
            <optgroup label="CLIENTS">
		<?php
		foreach( $clientinfo as $client )	{
			if( current_user_can( 'administrator' ) || $mdjm->mdjm_events->is_my_client( $client->ID ) )	{ // Non-Admins only see their own clients
				?>
				<option value="<?php echo add_query_arg( array( 'to_user' => $client->ID ) ); ?>"<?php if( isset( $_GET['to_user'] ) ) { selected( $client->ID, $_GET['to_user'] ); } ?>><?php echo $client->display_name; ?></option>
                <?php
			}
		}
		?>
        </optgroup>
        <?php
		if( current_user_can( 'administrator' ) )	{ // Admins see DJ's too
			 ?><optgroup label="<?php echo MDJM_DJ; ?>'s"><?php
			foreach( $djinfo as $dj )	{
				?>
                <option value="<?php echo add_query_arg( array( 'to_user' => $dj->ID ) ); ?>"<?php if( isset( $_GET['to_user'] ) ) { selected( $dj->ID, $_GET['to_user'] ); } ?>><?php echo $dj->display_name; ?></option>
                <?php	
			}
		}
		?>
        </optgroup>
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
        <td><input type="checkbox" name="copy_sender" id="copy_sender" value="Y" checked="checked" /> <span class="description">Depending on your <a href="<?php echo mdjm_get_admin_page( 'settings' ); ?>">settings</a>, the DJ and Admin may also receive a copy</span></td>
        </tr>
        <?php
		if( isset( $_GET['to_user'] ) )	{
			if( user_can( $_GET['to_user'], 'dj' ) )	{ // Selected user is a DJ
				$events = $mdjm->mdjm_events->dj_events( $_GET['to_user'], '', $order='DESC' );
			}
			else	{
				$events = $mdjm->mdjm_events->client_events( $_GET['to_user'], '', $order='DESC' );
			}
		}
		?>
        <tr class="alternate">
		<th class="row-title" align="left"><label for="event">Regarding Event:</label></th>
		<td>
        <?php
		if( empty( $events ) )	{
			?>
            <input type="text" name="event" class="regular-text" value="No Event (General Message)" disabled="disabled" />
            <?php	
		}
		else	{
			$event_stati = get_event_stati();
			?>
			<select name="event" id="event">
			<option value="">No Event (General Message)</option>
            <?php
			foreach( $events as $event )	{
				?>
				<option value="<?php echo $event->ID; ?>"<?php if( isset( $_POST['event'] ) ) { selected( $_POST['event'], $event->ID ); } elseif( isset( $_GET['event_id'] ) ) { selected( $_GET['event_id'], $event->ID ); }  ?>><?php echo date( MDJM_SHORTDATE_FORMAT, strtotime( get_post_meta( $event->ID, '_mdjm_event_date', true ) ) ) . ' from ' . date( MDJM_TIME_FORMAT, strtotime( get_post_meta( $event->ID, '_mdjm_event_start', true ) ) ) . ' (' . $event_stati[$event->post_status] . ')'; ?></option>
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
		<td colspan="2">
		<?php
		if( $mdjm->_mdjm_validation( 'check' ) )
			submit_button( 'Send Email', 'primary', 'submit', true );
		else
			echo '<a style="color:#a00" target="_blank" href="' . mdjm_get_admin_page( 'mydjplanner', 'str' ) . '">License Expired</a>';
		?>
        </td>
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
	
	f_mdjm_render_comms();