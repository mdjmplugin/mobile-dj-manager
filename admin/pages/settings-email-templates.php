<?php
	defined( 'ABSPATH' ) or die( "Direct access to this page is disabled!!!" );
	if ( !current_user_can( 'manage_options' ) && !current_user_can( 'manage_mdjm' ) )  {
		wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
	}
	
	/* Check for plugin update */
	f_mdjm_has_updated();

/*******************************************************************************
					THIS PAGE IS DEPRECATED SINCE 0.9.3
					TO BE REMOVED
*******************************************************************************/

	$class = 'error';
	$message = '<p style="font-size:14px">This page has been removed since version 0.9.3 as detailed in <a href="' . admin_url( 'admin.php?page=mdjm-dashboard&ver=0_9_3' ) . '" title="Version 0.9.3 upgrade notice">this upgrade notice</a>. The tab will be removed from the Settings section in a soon to be released version.</p>';
	$message .= '<p style="font-size:14px">To manage your email templates, go to the <a href="' . admin_url( 'edit.php?post_type=email_template' ) .'" title="Email Templates">Email Templates</a> menu option</p>';

	f_mdjm_update_notice( $class, $message );

	/*if( isset( $_GET['template'] ) && !empty( $_GET['template'] ) )	{
		$template = $_GET['template'];
	}
	else	{
		$template = 'enquiry';	
	}

	if( isset( $_POST['email_template'] ) && $_POST['email_template'] = 'update	' )	{
		if( update_option( 'mdjm_plugin_email_template_' . $_POST['option_key'], $_POST['email_content'] ) )	{
			?>
			<div id="message" class="updated">
			<p><strong><?php _e('Settings saved.') ?></strong></p>
			</div>
            <?php
		}
	}
*/
	/*function f_mdjm_email_template( $template )	{
		include( WPMDJM_PLUGIN_DIR . '/admin/includes/config.inc.php' );
		$content = get_option( 'mdjm_plugin_email_template_' . $template );
		$settings = array( 'media_buttons' => false,
							'textarea_rows' => '30',
						 );
		?>
		<script type="text/javascript">
        function MM_jumpMenu(targ,selObj,restore){ //v3.0
          eval(targ+".location='"+selObj.options[selObj.selectedIndex].value+"'");
          if (restore) selObj.selectedIndex=0;
        }
                </script>
        
        <form name="email-jump" id="email-jump">
          <table class="form-table">
            <tr>
              <th scope="row">Select the Template to edit:</th>
              <td><select name="jumpMenu" id="jumpMenu" onChange="MM_jumpMenu('parent',this,0)">
                  <option value="<?php echo admin_url( 'admin.php?page=mdjm-settings&tab=email_templates&template=enquiry' ); ?>" <?php if( $template == 'enquiry' ) echo ' selected'; ?>>Enquiry</option>
                  <option value="<?php echo admin_url( 'admin.php?page=mdjm-settings&tab=email_templates&template=contract_review' ); ?>" <?php if( $template == 'contract_review' ) echo ' selected'; ?>>Contract Review</option>
                  <option value="<?php echo admin_url( 'admin.php?page=mdjm-settings&tab=email_templates&template=client_booking_confirm' ); ?>" <?php if( $template == 'client_booking_confirm' ) echo ' selected'; ?>>Client Booking Confirmation</option>
                  <option value="<?php echo admin_url( 'admin.php?page=mdjm-settings&tab=email_templates&template=dj_booking_confirm' ); ?>" <?php if( $template == 'dj_booking_confirm' ) echo ' selected'; ?>>DJ Booking Confirmation</option>
              </select></td>
            </tr>
          </table>
        </form>
        <hr />
        <h2><?php echo $email_templates[$template]['name']; ?></h2>
        <p><?php echo $email_templates[$template]['description']; ?></p>
        <form name="form-email-template" id="form-email-template" method="post">
        <input type="hidden" name="email_template" value="update">
        <input type="hidden" name="option_key" value="<?php echo $template; ?>">
        <table class="form-table">
        <tr>
        <td><?php wp_editor( html_entity_decode( stripcslashes( $content ) ), 'email_content', $settings ); ?></td>
        </tr>
        </table>
	}
	
	f_mdjm_email_template( $template ); */
	
?>