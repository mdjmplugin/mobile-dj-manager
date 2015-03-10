<?php
/**
 * class-mdjm-contact-form-admin.php
 * 03/03/2015
 * @since 1.1.1
 * The class for MDJM Contact Forms Administration
 *
 * @version 1.0
 */

	class MDJM_ContactForms	{
		
		function __construct()	{
			$this->mdjm_options = get_option( WPMDJM_SETTINGS_KEY );
			$this->forms = get_option( 'mdjm_contact_forms' );
		} // __construct
		
		/**
		 * set_slug
		 * 03/03/2015
		 * @since 1.1.1
		 * Sets the slug for the form or field
		 */
		function set_slug( $name )	{
			
			$f_name = sanitize_text_field( stripslashes( $name ) );
			$slug = preg_replace( '/[^a-zA-Z0-9_-]$/s', '', $f_name );
			
			$slug = 'mdjm_' . strtolower( str_replace( array( ' ', '.' ), array( '_', '' ), $slug ) );
			
			if( $this->forms[$slug] )
					$slug = strtolower( str_replace( ' ', '_', $slug ) ) . '_';
			
			return $slug;
		} // set_slug
		
		/**
		 * display_forms
		 * 03/03/2015
		 * @since 1.1.1
		 * Display the list of configured forms
		 */
		function display_forms()	{

		}
		
		/**
		 * new_form
		 * 03/03/2015
		 * @since 1.1.1
		 * Display the field to add a new form
		 */
		function new_form()	{
			?>
			<h2>Add Contact Form</h2>
			<hr />
			<form name="add_contact_form" id="add_contact_form" method="post" action="">
			<table class="form-table">
			<tr>
			<th scope="row-title">Form Name:</th>
			<td><input type="text" name="form_name" id="form_name" class="regular-text" value="<?php if( isset( $_POST['form_name'] ) ) echo $_POST['form_name']; ?>" /></td>
			</tr>
			<tr>
			<td>&nbsp;</td>
			<td><?php submit_button( 'Begin Creating Contact Form', 'primary', 'submit', false, '' ); ?></td>
			</tr>
			</table>
			</form>
            <?php	
		} // new_form
		
		/**
		 * add_form_options
		 * 03/03/2015
		 * @since 1.1.1
		 * Add the options for the new form
		 */
		function add_form_options()	{
			$mdjm_forms = $this->forms;
			$mdjm_forms[$form_slug] = $this->set_slug( $_POST['form_name'] );
			$mdjm_forms[$form_slug] = array();
			$mdjm_forms[$form_slug]['slug'] = $mdjm_forms[$form_slug];
			$mdjm_forms[$form_slug]['name'] = sanitize_text_field( stripslashes( $_POST['form_name'] ) );
			
			/* Form Options */
			$mdjm_forms[$form_slug]['config'] = array(
													'email_from'			=> $this->mdjm_options['system_email'],
													'email_from_name'	   => WPMDJM_CO_NAME,
													'email_to'			  => $this->mdjm_options['system_email'],
													'reply_to'			  => 'Y',
													'email_subject'		 => stripslashes( $mdjm_forms[$form_slug]['name'] ) . ' form submission from ' . WPMDJM_CO_NAME . ' website',
													'copy_sender'		   => 'N',
													'create_enquiry'		=> 'N',
													'send_template' 		 => '',
													'update_user'		   => 'Y',
													'redirect'			  => 'no_redirect',
													'display_message'	   => 'N',
													'display_message_text'  => '<strong>Thank you</strong> for getting in touch with {COMPANY_NAME}.' . "\r\n\r\n" . '

We have received your message and will respond as soon as possible.',
													'required_field_text'   => stripslashes( '{FIELD_NAME} is a required field. Please try again.' ),
													'error_text_color'	  => 'FF0000',
													'layout'				=> '0_column',
													'row_height'			=> '',
													);
			
			if( update_option( 'mdjm_contact_forms', $mdjm_forms ) )	{
				f_mdjm_update_notice( 'updated', '<strong>' . stripslashes( $_POST['form_name'] ) . '</strong> contact form created successfully. Begin adding fields below' );	
			}
			$this->display_fields( $mdjm_forms[$form_slug] );
		} // add_form_options
		
		/**
		 * display_fields
		 * 03/03/2015
		 * @since 1.1.1
		 * Display the form fields
		 *
		 * @param: $form - the slug of the form name
		 */
		function display_fields( $form )	{
			
			
		} // display_fields
		
		/**
		 * add_field_options
		 * 03/03/2015
		 * @since 1.1.1
		 * Display options for adding a new field
		 *
		 * @param: $form - the slug of the form name
		 */
		function add_field_options( $field_types, $mappings )	{
			
	
		} // add_field_options

	} // MDJM_ContactForms