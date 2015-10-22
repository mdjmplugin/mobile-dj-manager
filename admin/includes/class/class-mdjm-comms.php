<?php
	defined('ABSPATH') or die("Direct access to this page is disabled!!!");
	
/**
 * Class MDJM_Comms
 * Manage MDJM email communications between admin, dj, client.
 * Execute shortcode filters before sending
 *
 *
 */
	class MDJM_Comms	{
		function __construct()	{
			
		}
		
		/**
		 * Display the Admin communication GUI enabling staff to communicate with clients
		 *
		 *
		 *
		 *
		 */
		function compose_mail()	{
			global $mdjm, $mdjm_settings;
			
			// If we're using an email template lets load it and set the subject
			if( !empty( $_GET['template'] ) )	{
				$template_query = get_post( $_GET['template'] );
				
				if( $template_query ) {
					$content = $template_query->post_content;
					$content = apply_filters( 'the_content', $content );
					$content = str_replace(']]>', ']]&gt;', $content);
					$subject = get_the_title( $_GET['template'] );
				}
			}
			// Otherwise check of the content and subject exist in the $_POST
			elseif( !empty( $_POST['email_content'] ) )
				$content = $_POST['email_content'];

			else
				$content = '';	

			if( empty( $subject ) )	{
				if( isset( $_POST['subject'] ) )
					$subject = $_POST['subject'];
				
				else
					$subject = '';	
				
			}
			
			// Start the web layout
			?>
			<div class="wrap">
            <h1><?php _e( 'Communications', 'mobile-dj-manager' ); ?></h1>
            
			<script type="text/javascript">
                function MM_jumpMenu(targ,selObj,restore){ //v3.0
                  eval(targ+".location='"+selObj.options[selObj.selectedIndex].value+"'");
                  if (restore) selObj.selectedIndex=0;
                }
            </script>
            
            <form name="mdjm-comms-composer" id="mdjm-comms-composer" method="post">
            <?php
			
			// If called from the 'respond unavailable' action, set hidden field so we can refer upon submit
			if( isset( $_GET['action'] ) && $_GET['action'] == 'respond_unavailable' )	{
				?>
                <input type="hidden" name="respond_unavailable" id="respond_unavailable" value="<?php echo $_GET['event_id']; ?>" />
				<?php
			}
			?>
            <table class="widefat">
                <tr class="alternate">
                    <th style="width: 20%; font-weight: bold; text-align: left;"><label for="email_template"><?php _e( 'Select a template', 'mobile-dj-manager' ); ?>:</label></th>
                    <td><?php echo $this->load_template_list(); ?></td>
                </tr>
                
                <tr class="alternate">
                    <th style="width: 20%; font-weight: bold; text-align: left;"><label for="to"><?php _e( 'Send email to', 'mobile-dj-manager' ); ?>:</label></th>
                    <td><?php echo $this->list_recipients(); ?></td>
                </tr>
            
            	<tr class="alternate">
                    <th style="width: 20%; font-weight: bold; text-align: left;"><label for="copy_sender"><?php _e( 'Copy yourself?', 'mobile-dj-manager' ); ?></label></th>
                    <td><input type="checkbox" name="copy_sender" id="copy_sender" value="1" checked="checked" /> <span class="description">
						<?php echo sprintf( 
							__( 'Depending on your %ssettings%s, the %s and Admin may also receive a copy', 
							'mobile-dj-manager' ),
							'<a href="' . mdjm_get_admin_page( 'settings' ) . '">',
							'</a>',
							MDJM_DJ ); ?></span>
					</td>
                </tr>
                
                <tr class="alternate">
                    <th style="width: 20%; font-weight: bold; text-align: left;"><label for="subject"><?php _e( 'Subject', 'mobile-dj-manager' ); ?>:</label></th>
                    <td><input type="text" name="subject" id="subject" class="regular-text" value="<?php echo $subject; ?>" /></td>
                </tr>
            </table>
            <?php
			// Settings for the tinymce editor
            $settings = array(
				'media_buttons' => true,
                'textarea_rows' => '10' );
			
			wp_editor( html_entity_decode( stripcslashes( $content ) ), 'email_content', $settings );
			
			submit_button( __( 'Send Email', 'mobile-dj-manager' ), 'primary', 'submit', true );
		} // compose_mail
		
		/**
		 * Build a select list with all clients and staff.
		 * If current user is not an admin, filter for own clients only
		 *
		 * @param
		 * @return	str		$output		The HTML source for the select list
		 */
		function list_recipients()	{
			global $mdjm;
			
			$output = '<select name="to" id="to">' . "\r\n";					
			$output .= '<option value="0">' . __( 'Select a Recipient', 'mobile-dj-manager' ) . '</option>' . "\r\n";
            $output .= '<optgroup label="' . __( 'CLIENTS', 'mobile-dj-manager' ) . '">' . "\r\n";
			
			foreach( $mdjm->mdjm_events->get_clients() as $client )	{
				if( current_user_can( 'administrator' ) || $mdjm->mdjm_events->is_my_client( $client->ID ) )
					$output .= '<option value="' . $client->ID . '">' . $client->display_name . '</option>' . "\r\n";

			}
			$output .= '</optgroup>' . "\r\n";
			
			if( current_user_can( 'administrator' ) )	{ // Admins see DJ's too
				$output .= '<optgroup label="' . MDJM_DJ . '\'s">' . "\r\n";
				foreach( mdjm_get_djs() as $dj )	{
					$output .= '<option value="' . $dj->ID . '">' . $dj->display_name . '</option>' . "\r\n";
				}
				$output .= '</optgroup>' . "\r\n";
			}
			$output .= '</select>' . "\r\n";
			
			return $output;
		} // list_recipients
		
		/**
		 * Build a select list with all templates available.
		 * Verify the logged in user can use the template and if not, remove it.
		 *
		 * @param
		 * @return	str		$output		The HTML source for the select list
		 */
		function load_template_list()	{
			$output = '<select name="email_template" id="email_template">' . "\r\n";
			$output .= '<option value="0">' . __( 'Do not use Template', 'mobile-dj-manager' ) . '</option>' . "\r\n";
			
			// List the email templates
			$email_templates = get_posts( 
								array(
									'posts_per_page'	=> -1,
									'post_type'			=> MDJM_EMAIL_POSTS,
									'orderby'			=> 'name',
									'order'				=> 'ASC',
									'post__not_in'		=> is_dj() ? $mdjm_settings['permissions']['dj_disable_template'] : '' ) );
									
			if( $email_templates )	{
				$output .= '<optgroup label="' . __( 'EMAIL TEMPLATES', 'mobile-dj-manager' ) . '">' . "\r\n";
				foreach( $email_templates as $template )	{
					$output .= '<option value="' . $template->ID . '">' . get_the_title( $template->ID ) . '</option>' . "\r\n";
				}
				$output .= '</optgroup>' . "\r\n";
			} // if( $email_templates )
			
			// List the Contract Templates
			$contract_templates = get_posts( 
								array(
									'posts_per_page'	=> -1,
									'post_type'			=> MDJM_CONTRACT_POSTS,
									'orderby'			=> 'name',
									'order'				=> 'ASC',
									'post__not_in'		=> is_dj() ? $mdjm_settings['permissions']['dj_disable_template'] : '' ) );
									
			if( $contract_templates )	{
				$output .= '<optgroup label="' . __( 'CONTRACT TEMPLATES', 'mobile-dj-manager' ) . '">' . "\r\n";
				foreach( $contract_templates as $template )	{
					$output .= '<option value="' . $template->ID . '">' . get_the_title( $template->ID ) . '</option>' . "\r\n";
				}
				$output .= '</optgroup>' . "\r\n";
			} // if( $contract_templates )
			
			$output .= '</select>' . "\r\n";
			
			return $output;
			
		} // load_template_list
	} // class MDJM_Comms