<?php
/**
 * Contains the communication page for sending manual emails.
 *
 * @package		MDJM
 * @subpackage	Comms
 * @since		1.3
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) )
	exit;

/**
 * Display the send email form on the communications page.
 *
 * @since	1.3
 * @param
 * @return	Outputs the page content
 */
function mdjm_comms_page()	{
	
	if( ! mdjm_employee_can( 'send_comms' ) )  {
		wp_die(
			'<h1>' . __( 'Cheatin&#8217; uh?', 'mobile-dj-manager' ) . '</h1>' .
			'<p>' . __( 'You do not have permission to access this page.', 'mobile-dj-manager' ) . '</p>',
			403
		);
	}
	
	global $current_user;
		
	if ( mdjm_employee_can( 'list_all_clients' ) )	{
		$clients = mdjm_get_clients();
	} else	{
		$clients = mdjm_get_employee_clients();
	}
	
	if ( mdjm_employee_can( 'mdjm_employee_edit' ) )	{
		$employees = mdjm_get_employees();
	}

	?>
    <div class="wrap">
		<h1><?php _e( 'Client and Employee Communications', 'mobile-dj-manager' ); ?></h1>
        <form name="mdjm_form_send_comms" id="mdjm_form_send_comms" method="post" enctype="multipart/form-data">
        	<?php wp_nonce_field( 'send_comm_email', 'mdjm_nonce', true, true ); ?>
			<?php mdjm_admin_action_field( 'send_comm_email' ); ?>
            <input type="hidden" name="mdjm_email_from_address" id="mdjm_email_from_address" value="<?php echo $current_user->user_email; ?>" />
            <input type="hidden" name="mdjm_email_from_name" id="mdjm_email_from_name" value="<?php echo $current_user->display_name; ?>" />
            <?php do_action( 'mdjm_pre_comms_table' ); ?>
            <table class="form-table">
                <?php do_action( 'mdjm_add_comms_fields_before_recipient' ); ?>
                <tr>
                	<th scope="row"><label for="mdjm_email_to"><?php _e( 'Select a Recipient', 'mobile-dj-manager' ); ?></label></th>
                    <td>
                    	<select name="mdjm_email_to" id="mdjm_email_to">
                        	<option value=""><?php _e( 'Select a Recipient', 'mobile-dj-manager' ); ?></option>
                        	<optgroup label="<?php _e( 'Clients', 'mobile-dj-manager' ); ?>">
                            	<?php
								if ( empty( $clients ) )	{
									echo '<option disabled="disabled">' . __( 'No Clients Found', 'mobile-dj-manager' ) . '</option>';
								} else	{
									foreach( $clients as $client )	{
										echo '<option value="' . $client->ID . '">' . $client->display_name . '</option>';
									}
								}
								?>
                            </optgroup>
                            <?php
							if ( ! empty( $employees ) )	{
								
								echo '<optgroup label="' . __( 'Employees', 'mobile-dj-manager' ) . '">';
								
								foreach( $employees as $employee )	{
									echo '<option value="' . $employee->ID . '">' . $employee->display_name . '</option>';
								}
								
								echo '</optgroup>';
							}
							?>
                        </select>
                    </td>
                </tr>
                <?php do_action( 'mdjm_add_comms_fields_before_subject' ); ?>
                <tr>
                	<th scope="row"><label for="mdjm_email_subject"><?php _e( 'Subject', 'mobile-dj-manager' ); ?></label></th>
                    <td><input type="text" name="mdjm_email_subject" id="mdjm_email_subject" class="regular-text" value="<?php echo isset( $_GET['template'] ) ? esc_attr( get_the_title( $_GET['template'] ) ) : ''; ?>" /></td>
                </tr>
                <tr>
                	<th scope="row"><label for="mdjm_email_copy_to"><?php _e( 'Copy Yourself?', 'mobile-dj-manager' ); ?></label></th>
                    <td><input type="checkbox" name="mdjm_email_copy_to" id="mdjm_email_copy_to" value="<?php echo $current_user->user_email; ?>" /> <span class="description"><?php _e( 'Settings may dictate that additional email copies are also sent', 'mobile-dj-manager' ); ?></span></td>
                </tr>
                <?php do_action( 'mdjm_add_comms_fields_before_template' ); ?>
            	<tr>
                	<th scope="row"><label for="mdjm_email_template"><?php _e( 'Select a Template', 'mobile-dj-manager' ); ?></label></th>
                    <td>
                    	<select name="mdjm_email_template" id="mdjm_email_template">
                        	<option value="0"><?php _e( 'No Template', 'mobile-dj-manager' ); ?></option>
                            <?php $template = isset( $_GET['template'] ) ? $_GET['template'] : 0; ?>
                        	<?php echo mdjm_comms_template_options( $template ); ?>
                        </select>
                    </td>
                </tr>
                <?php do_action( 'mdjm_add_comms_fields_before_event' ); ?>
                <tr>
                    <th scope="row"><label for="mdjm_email_event"><?php printf( __( 'Associated %s', 'mobile-dj-manager' ), mdjm_get_label_singular() ); ?></label></th>
                    <td>
                    	<?php if ( isset( $_GET['event_id'] ) || ( isset( $_GET['mdjm-action'] ) && $_GET['mdjm-action'] == 'respond_unavailable' ) )	: ?>
                        	<?php
							$value = mdjm_get_event_date( $_GET['event_id'] ) . ' ';
							$value .= __( 'from', 'mobile-dj-manager' ) . ' ';
							$value .= mdjm_get_event_start( $_GET['event_id'] ) . ' ';
							$value .= '(' . mdjm_get_event_status( $_GET['event_id'] ) . ')';
							?>
							<input type="text" name="mdjm_email_event_show" id="mdjm_email_event_show" value="<?php echo $value; ?>" readonly size="50" />
                            <input type="hidden" name="mdjm_email_event" id="mdjm_email_event" value="<?php echo $_GET['event_id']; ?>" />
                        <?php else : ?>
                            <select name="mdjm_email_event" id="mdjm_email_event">
                            <option value="0"><?php _e( 'Select an Event', 'mobile-dj-manager' ); ?></option>
                            </select>
                        <?php endif; ?>
                        <p class="description"><?php printf( __( 'If no %s is selected <code>{event_*}</code> content tags may not be used', 'mobile-dj-manager' ), mdjm_get_label_singular( true ) ); ?></p>
                    </td>
                </tr>
                <?php do_action( 'mdjm_add_comms_fields_before_file' ); ?>
                <tr>
                	<th scope="row"><label for="mdjm_email_upload_file"><?php _e( 'Attach a File', 'mobile-dj-manager' ); ?></label></th>
                    <td><input type="file" name="mdjm_email_upload_file" id="mdjm_email_upload_file" class="regular-text" value="" />
                    	<p class="description"><?php printf( __( 'Max file size %dMB. Change php.ini <code>post_max_size</code> to increase', 'mobile-dj-manager' ), ini_get( 'post_max_size' ) ); ?></p>
                    </td>
                </tr>
                <?php do_action( 'mdjm_add_comms_fields_before_content' ); ?>
                <tr>
                    <td colspan="2">
                        <?php
							$content = isset( $_GET['template'] ) ? mdjm_get_email_template_content( $_GET['template'] ) : '';
							
                            wp_editor(
                                $content,
                                'mdjm_email_content',
                                array(
                                    'media_buttons' => true,
                                    'textarea_rows' => '10',
									'editor_class'  => 'required'
                                )
                            );
                        ?>
                    </td>
                </tr>
            </table>
            <?php do_action( 'mdjm_post_comms_table' ); ?>
            <?php submit_button( __( 'Send Email', 'mobile-dj-manager' ), 'primary', 'submit', true ); ?>
        </form>
    </div>
    <?php
	
} // mdjm_comms_page

/**
 * Retrieve the templates
 *
 * @since	1.3
 * @param	str|arr		$type	The type of template to retrieve
 * @return	obj|bool	WP_Query object or false if none found
 */
function mdjm_get_templates( $type = array( 'contract', 'email_template' ) )	{
	
	$templates = get_posts(
		array(
			'post_type'      => $type,
			'posts_per_page' => -1,
			'post_status'    => 'publish'
		)
	);
	
	return $templates;
	
} // mdjm_get_templates

/**
 * Generates the options for a select list of templates grouped by post type
 *
 * @since	1.3
 * @param	int		$selected	The ID of the template that should be initially selected.
 * @return	str		HTML Output for the select options.
 */
function mdjm_comms_template_options( $selected = 0 )	{
	
	$templates = mdjm_get_templates();

	$output    = '';
	
	if ( ! $templates )	{
		$output .= '<option disabled>' . __( 'No Templates Found', 'mobile-dj-manager' ) . '</option>';
	} else	{
		
		foreach( $templates as $template )	{
			
			$comms_templates[ $template->post_type ][ $template->ID ] = $template->post_title;
			
		}
		
		foreach( $comms_templates as $group => $comms_template )	{
			$output .= '<optgroup label="' . strtoupper( get_post_type_object( $group )->label ) . '">';
			
			foreach( $comms_template as $template_id => $template_name )	{
				
				$output .= '<option value="' . $template_id . '"' . selected( $selected, $template_id, false ) . '>' . $template_name . '</option>';
			}
			
			$output .= '</optgroup>';
		}
		
	}
	
	return $output;
	
} // mdjm_comms_template_options

/**
 * Process the sending of the email
 *
 * @since	1.3
 * @param	arr		$data	Super global $_POST array
 * @return	void
 */
function mdjm_send_comm_email( $data )	{

	$url = remove_query_arg( array( 'mdjm-message', 'event_id', 'template', 'recipient', 'mdjm-action' ) );
		
	if ( ! wp_verify_nonce( $data['mdjm_nonce'], 'send_comm_email' ) )	{
		$message = 'nonce_fail';
	} elseif ( empty( $data['mdjm_email_to'] ) || empty( $data['mdjm_email_subject'] ) || empty( $data['mdjm_email_content'] ) )	{
		$message = 'comm_missing_content';
	} else	{
		
		if( isset( $_FILES['mdjm_email_upload_file'] ) && '' !== $_FILES['mdjm_email_upload_file']['name'] )	{
			$upload_dir = wp_upload_dir();
			
			$file_name  = $_FILES['mdjm_email_upload_file']['name'];
			$file_path  = $upload_dir['path'] . '/' . $file_name;
			$tmp_path   = $_FILES['mdjm_email_upload_file']['tmp_name'];
			
			if( move_uploaded_file( $tmp_path, $file_path ) )	{
				$attachments[] = $file_path;
			}
		}
		
		if ( empty( $attachments ) )	{
			$attachments = array();
		}

		$attachments = apply_filters( 'mdjm_send_comm_email_attachments', $attachments, $data );
        $client_id   = $data['mdjm_email_to'];
        
        if ( ! empty( $data['mdjm_email_event'] ) )  {
            $event     = new MDJM_Event( $data['mdjm_email_event'] );
            $client_id = $event->client;
        }


		$email_args = array(
			'to_email'       => mdjm_get_client_email( $data['mdjm_email_to'] ),
			'from_name'      => $data['mdjm_email_from_name'],
			'from_email'     => $data['mdjm_email_from_address'],
			'event_id'       => $data['mdjm_email_event'],
			'client_id'      => $client_id,
			'subject'        => stripslashes( $data['mdjm_email_subject'] ),
			'attachments'    => ! empty( $attachments ) ? $attachments : array(),
			'message'        => stripslashes( $data['mdjm_email_content'] ),
			'track'          => true,
			'copy_to'        => ! empty( $data['mdjm_email_copy_to'] ) ? array( $data['mdjm_email_copy_to'] ) : array(),
			'source'         => __( 'Communication Feature', 'mobile-dj-manager' )
		);
		
		if ( mdjm_send_email_content( $email_args ) )	{
			$message = 'comm_sent';
			
			if ( ! empty( $data['mdjm_event_reject'] ) )	{
								
				$args = array(
					'reject_reason' => ! empty( $data['mdjm_email_reject_reason'] ) ? $data['mdjm_email_reject_reason'] : __( 'No reason specified', 'mobile-dj-manager' )
				);
				
				mdjm_update_event_status( $email_args['event_id'], 'mdjm-rejected', get_post_status( $email_args['event_id'] ), $args );
			}
			
		} else	{
			$message = 'comm_not_sent';
		}
		
	}
	
	wp_redirect( add_query_arg( 'mdjm-message', $message, $url ) );
	
	die();
	
} // mdjm_send_comm_email
add_action( 'mdjm-send_comm_email', 'mdjm_send_comm_email' );

/**
 * Add the comment field to record why an event is being rejected.
 *
 * @since	1.3.3
 * @param
 * @return
 */
function mdjm_add_reject_reason_field()	{
    
	if ( ! isset( $_GET['mdjm-action'] ) || $_GET['mdjm-action'] != 'respond_unavailable' )	{
		return;
	}
	
	$output = '<tr>';
    $output .= '<th scope="row"><label for="mdjm_email_reject_reason">' . __( 'Rejection Reason', 'mobile-dj-manager' ) . '</label></th>';
	$output .= '<td><textarea name="mdjm_email_reject_reason" id="mdjm_email_reject_reason" cols="50" rows="3" clas="class="large-text code"></textarea>';
	$output .= '<p class="description">' . __( 'Optional. If completed, this entry will be added to the event journal.', 'mobile-dj-manager' ) . '</p>';
	$output .= '<input type="hidden" name="mdjm_event_reject" id="mdjm_event_reject" value="1" />';
	$output .= '</td>';
    $output .= '</tr>';
	
	echo apply_filters( 'mdjm_add_reject_reason_field', $output );
	
} // mdjm_add_reject_reason_field
add_action( 'mdjm_add_comms_fields_before_file', 'mdjm_add_reject_reason_field' );
