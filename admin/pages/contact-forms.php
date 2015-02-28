<?php
/* DEV NOTES */
/* The Edit Field capability is currently hidden */
/*
* contact-forms.php
* 30/12/2014
* @since 1.0
* The contact forms settings page
*/
	defined('ABSPATH') or die("Direct access to this page is disabled!!!");
	if ( !current_user_can( 'manage_options' ) )  {
		wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
	}
	
/* If recently updated, display the release notes */
	f_mdjm_has_updated();

/* Display the main content */
	function f_mdjm_show_forms()	{
		$mdjm_forms = get_option( 'mdjm_contact_forms' );
		?>
		<div class="wrap">
		<div id="icon-themes" class="icon32"></div>
		<h2>Contact Forms <a href="<?php echo admin_url( 'admin.php?page=mdjm-contact-forms&action=show_add_contact_form' ); ?>" class="add-new-h2">Add New</a></h2>
		<hr />
		<table class="widefat" width="100%">
		<tr valign="top">
		<td width="75%">
		<table width="100%" class="widefat">
		<tr>
		<td colspan="3" class="alternate"><strong>Existing Contact Forms</strong></td>
		</tr>
		</table>
		<table width="100%" class="widefat">
		<thead>
		<th class="row-title">Form Name</th>
		<th class="row-title">Shortcode</th>
        <th class="row-title">Fields</th>
		<th class="row-title">Action</th>
		</thead>
		<?php
		/* No contact forms configured */
		if( !$mdjm_forms )	{
			?>
			<tr class="form-invalid">
			<td colspan="3">No Contact Forms exist yet. <label for="form_name">Add one now</label></td>
			</tr>
			<?php	
		} // if( !isset( $mdjm_contact_forms ) )
		
		/* Loop through the contact forms */
		else	{
			$rowclass = ' class="alternate"';
			foreach( $mdjm_forms as $forms )	{
				?>
				<tr<?php echo $rowclass; ?>>
				<td><?php echo $forms['name']; ?></td>
				<td><code>[MDJM page="Contact Form" slug="<?php echo $forms['slug']; ?>"]</code></td>
                <td><?php echo count( $forms['fields'] ); ?></td>
				<td><a href="<?php echo admin_url( 'admin.php?page=mdjm-contact-forms&action=edit_contact_form&form_id=' . $forms['slug'] ); ?>" class="add-new-h2">Edit</a>&nbsp;&nbsp;&nbsp;<a href="<?php echo admin_url( 'admin.php?page=mdjm-contact-forms&action=del_contact_form&form_id=' . $forms['slug'] ); ?>" class="add-new-h2">Delete</a></td>
				</tr>
				<?php
				if( $rowclass == ' class="alternate"' ) $rowclass = ''; else $rowclass = ' class="alternate"';
			} // foreach( $mdjm_forms as $forms )
			
		} // else
		?>
		<tfoot>
		<th class="row-title">Form Name</th>
		<th class="row-title">Shortcode</th>
        <th class="row-title">Fields</th>
		<th class="row-title">Action</th>
		</tfoot>
		</table>
		</td>
		<td width="25%">
		<form name="add_contact_form" id="add_contact_form" method="post" action="<?php f_mdjm_admin_page( 'contact_forms'); ?>">
		<table width="100%" class="widefat">
		<tr>
		<td colspan="2" class="alternate"><strong>Add New Contact Form</strong></td>
		</tr>
		<tr>
		<th class="row-title"><label for="form_name">Name:</label></th>
		<td><input type="text" name="form_name" id="form_name" /></td>
		</tr>
		<td colspan="2" align="center"><?php submit_button( 'Create Contact Form', 'primary small', 'submit', false, '' ); ?></td>
		</table>
		</form>
		</td>
		</tr>
		</table>
		</div>
        <?php
	}
	
	function f_mdjm_show_add_contact_form()	{
		?>
        <div class="wrap">
		<div id="icon-themes" class="icon32"></div>
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
        <td><?php submit_button( 'Begin Creating Form', 'primary', 'submit', false, '' ); ?></td>
        </tr>
        </table>
        </form>
        </div>
		<?php
	}
	
	function f_mdjm_edit_contact_form( $form_slug )	{
		global $mdjm_options;
		$mdjm_forms = get_option( 'mdjm_contact_forms' );
		$field_types = array(
							'text'         => 'Text Field',
							'date'         => 'Date Field',
							'time'         => 'Time Field',
							'email'        => 'Email Field',
							'select'       => 'Select List',
							'select_multi' => 'Select List (Multiple Select)',
							'event_list'   => 'Event Type List',
							'checkbox'     => 'Checkbox',
							'textarea'     => 'Textarea',
							'tel'          => 'Telephone Number',
							'url'          => 'URL',
							'captcha'      => 'CAPTCHA',
							'submit'       => 'Submit Button',
							);
							
		$mappings_client = array(
							'first_name'           => 'Client First Name',
							'last_name'            => 'Client Last Name',
							'user_email'           => 'Client Email Address',
							);
		$client_fields = get_option( WPMDJM_CLIENT_FIELDS );
		foreach( $client_fields as $client_field )	{
			if( $client_field['display'] == 'Y' )	{
				$mappings_client[$client_field['id']] = 'Client ' . $client_field['label'];
			}
		}
		$mappings_event = array(
							'event_date'           => 'Event Date',
							'event_type'           => 'Event Type',
							'event_start'          => 'Event Start',
							'event_finish'         => 'Event End',
							'event_description'    => 'Event Description',
							'venue'                => 'Event Venue Name',
							'venue_city'           => 'Event Venue Town/City',
							'venue_state'          => 'Event County (State)'
							);
							
		$mappings = array_merge( $mappings_client, $mappings_event );
							
		/* Process field deletions */
		if( isset( $_GET['del'], $_GET['field'] ) && $_GET['del'] == 'Y' )	{
			$name = $mdjm_forms[$form_slug]['fields'][$_GET['field']]['name'];
			unset( $mdjm_forms[$form_slug]['fields'][$_GET['field']] );
			if( update_option( 'mdjm_contact_forms', $mdjm_forms ) )	{
				f_mdjm_update_notice( 'updated', 'The <strong>' . $name . '</strong> field was deleted' );
			}
		}
		/* Process field edits */
		if( isset( $_POST['submit'], $_POST['form_slug'] ) && $_POST['submit'] == 'Edit Field' )	{
			$field_name = sanitize_text_field( $_POST['field_name'] );
			$field_slug = preg_replace( '/[^a-zA-Z0-9_-]$/s', '', $field_name );
			$field_slug = 'mdjm_' . strtolower( str_replace( array( ' ', '.' ), array( '_', '' ), $field_slug ) );
			
			if( $mdjm_forms[$_POST['form_slug']]['fields'][$field_slug] )
				$field_slug = strtolower( str_replace( ' ', '_', $field_slug ) ) . '_';
				
			$pos = $mdjm_forms[$_POST['form_slug']]['fields'][$field_slug]['position'];
			if( $_POST['field_type'] == 'captcha' )
				$pos = 98;
			if( $_POST['field_type'] == 'submit' )
				$pos = 99;
				
			unset( $mdjm_forms[$_POST['form_slug']]['fields'][$_POST['field_to_edit']] );
				
			$mdjm_forms[$_POST['form_slug']]['fields'][$field_slug] = array(
															'slug'     => $field_slug,
															'name'     => sanitize_text_field( $_POST['field_name'] ),
															'type'     => sanitize_text_field( $_POST['field_type'] ),
															'config'   => array(),
															'position' => $pos,
															);
			/* Classes */
			if( isset( $_POST['label_class'] ) && !empty( $_POST['label_class'] ) )	{
				$mdjm_forms[$_POST['form_slug']]['fields'][$field_slug]['config']['label_class'] = $_POST['label_class'];
			}
			if( isset( $_POST['input_class'] ) && !empty( $_POST['input_class'] ) )	{
				$mdjm_forms[$_POST['form_slug']]['fields'][$field_slug]['config']['input_class'] = $_POST['input_class'];
			}
			
			/* Size */
			if( isset( $_POST['width'] ) && !empty( $_POST['width'] ) )	{
				$mdjm_forms[$_POST['form_slug']]['fields'][$field_slug]['config']['width'] = $_POST['width'];
			}
			if( isset( $_POST['height'] ) && !empty( $_POST['height'] ) )	{
				$mdjm_forms[$_POST['form_slug']]['fields'][$field_slug]['config']['height'] = $_POST['height'];
			}
			
			/* Field Mapping */
			$mdjm_forms[$_POST['form_slug']]['fields'][$field_slug]['config']['mapping'] = $_POST['mapping'];
			if( $_POST['field_type'] == 'email' )	{
				$mdjm_forms[$_POST['form_slug']]['fields'][$field_slug]['config']['mapping'] = 'user_email';
			}
			
			/* Date Fields */
			if( $_POST['field_type'] == 'date' && isset( $_POST['datepicker'] ) && $_POST['datepicker'] == 'Y' )	{
				$mdjm_forms[$_POST['form_slug']]['fields'][$field_slug]['config']['datepicker'] = 'Y';
			}
			/* Checkbox Fields */
			if( $_POST['field_type'] == 'checkbox' )	{
				if( isset( $_POST['is_checked'] ) && $_POST['is_checked'] == 'Y' )	{
					$mdjm_forms[$_POST['form_slug']]['fields'][$field_slug]['config']['is_checked'] = 'Y';
				}
				if( isset( $_POST['checked_value'] ) && !empty( $_POST['checked_value'] ) )	{
					$mdjm_forms[$_POST['form_slug']]['fields'][$field_slug]['config']['checked_value'] = sanitize_text_field( $_POST['checked_value'] );
				}
				else	{
					$mdjm_forms[$_POST['form_slug']]['fields'][$field_slug]['config']['checked_value'] = 'Y';	
				}
			}
			
			/* Select List Fields */
			if( $_POST['field_type'] == 'select' || $_POST['field_type'] == 'select_multi' )	{
				$mdjm_forms[$_POST['form_slug']]['fields'][$field_slug]['config']['options'] = $_POST['select_options'];
			}
			
			/* Event List First Entry */
			if( $_POST['field_type'] == 'event_list' && !empty( $_POST['event_list_first_entry'] ) )	{
				$mdjm_forms[$_POST['form_slug']]['fields'][$field_slug]['config']['event_list_first_entry'] = sanitize_text_field( $_POST['event_list_first_entry'] );
			}
			
			/* Required Field */
			if( isset( $_POST['required'] ) && $_POST['required'] == 'Y' )	{
				$mdjm_forms[$_POST['form_slug']]['fields'][$field_slug]['config']['required'] = 'Y';
			}
			
			/* Placeholder Text */
			if( isset( $_POST['placeholder'] ) && !empty( $_POST['placeholder'] ) )	{
				$mdjm_forms[$_POST['form_slug']]['fields'][$field_slug]['config']['placeholder'] = sanitize_text_field( $_POST['placeholder'] );
			}
			
			/* Submit Button */
			if( isset( $_POST['submit_align'] ) && $_POST['submit_align'] != '' )	{
				$mdjm_forms[$_POST['form_slug']]['fields'][$field_slug]['config']['submit_align'] = $_POST['submit_align'];
			}
			
			update_option( 'mdjm_contact_forms', $mdjm_forms );
			f_mdjm_update_notice( 'updated', 'The <strong>' . sanitize_text_field( $_POST['field_name'] ) . '</strong> field edits were successfully completed' );
		}
		
		/* Editing field */
		if( isset( $_GET['edit'], $_GET['field'] ) && $_GET['edit'] == 'Y' && !isset( $_POST['Edit Field'] ) )	{
			$name = $mdjm_forms[$form_slug]['fields'][$_GET['field']]['name'];
			if( !empty( $mdjm_forms[$form_slug]['fields'][$_GET['field']]['config']['placeholder'] ) )	{
				$placeholder_text = $mdjm_forms[$form_slug]['fields'][$_GET['field']]['config']['placeholder'];
			}
			if( !empty( $mdjm_forms[$form_slug]['fields'][$_GET['field']]['config']['width'] ) )	{
				$field_width = $mdjm_forms[$form_slug]['fields'][$_GET['field']]['config']['width'];
			}
			if( !empty( $mdjm_forms[$form_slug]['fields'][$_GET['field']]['config']['height'] ) )	{
				$field_height = $mdjm_forms[$form_slug]['fields'][$_GET['field']]['config']['height'];
			}
			if( !empty( $mdjm_forms[$form_slug]['fields'][$_GET['field']]['config']['checked_value'] ) )	{
				$checked_value = $mdjm_forms[$form_slug]['fields'][$_GET['field']]['config']['checked_value'];
			}
			if( !empty( $mdjm_forms[$form_slug]['fields'][$_GET['field']]['config']['label_class'] ) )	{
				$label_class = $mdjm_forms[$form_slug]['fields'][$_GET['field']]['config']['label_class'];
			}
			if( !empty( $mdjm_forms[$form_slug]['fields'][$_GET['field']]['config']['input_class'] ) )	{
				$input_class = $mdjm_forms[$form_slug]['fields'][$_GET['field']]['config']['input_class'];
			}
			if( !empty( $mdjm_forms[$form_slug]['fields'][$_GET['field']]['config']['mapping'] ) )	{
				$field_mapping = $mdjm_forms[$form_slug]['fields'][$_GET['field']]['config']['mapping'];
			}
			if( !empty( $mdjm_forms[$form_slug]['fields'][$_GET['field']]['config']['event_list_first_entry'] ) )	{
				$first_entry = $mdjm_forms[$form_slug]['fields'][$_GET['field']]['config']['event_list_first_entry'];
			}
			if( !empty( $mdjm_forms[$form_slug]['fields'][$_GET['field']]['config']['datepicker'] ) )	{
				$datepicker = $mdjm_forms[$form_slug]['fields'][$_GET['field']]['config']['datepicker'];
			}
			if( $mdjm_forms[$form_slug]['fields'][$_GET['field']]['type'] == 'select' 
				|| $mdjm_forms[$form_slug]['fields'][$_GET['field']]['type'] == 'multi_select' )	{
				$select_options = $mdjm_forms[$form_slug]['fields'][$_GET['field']]['config']['options'];
			}
			if( !empty( $mdjm_forms[$form_slug]['fields'][$_GET['field']]['type']['submit'] ) )	{
				$submit_align = $mdjm_forms[$form_slug]['fields'][$_GET['field']]['config']['submit_align'];
			}
			f_mdjm_update_notice( 'update-nag', 'You are currently editing the field <strong>' . $name . '</strong>' );
			
			/* Put edits here */			
		}
		?>
        <script type="text/javascript">
		function showDiv(elem){
			if(elem.value == 'text' || elem.value == 'textarea' || elem.value == 'tel' || elem.value == 'email' || elem.value == 'url')	{
				document.getElementById('placeholder_row').style.display = "block";
			}
			else	{
				document.getElementById('placeholder_row').style.display = "none";   
			}
			if(elem.value == 'text' || elem.value == 'textarea' )	{
				document.getElementById('width_row').style.display = "block";
			}
			else	{
				document.getElementById('width_row').style.display = "none";   
			}
			if(elem.value == 'textarea' )	{
				document.getElementById('height_row').style.display = "block";
			}
			else	{
				document.getElementById('height_row').style.display = "none";   
			}
			if(elem.value == 'date')	{
				document.getElementById('datepicker_row').style.display = "block";
			}
			else	{
				document.getElementById('datepicker_row').style.display = "none";   
			}
			if(elem.value == 'checkbox')	{
				document.getElementById('checkbox_row').style.display = "block";
			}
			else	{
				document.getElementById('checkbox_row').style.display = "none";   
			}
			if(elem.value == 'select' || elem.value == 'select_multi')	{
				document.getElementById('select_options_row').style.display = "block";
			}
			else	{
				document.getElementById('select_options_row').style.display = "none";   
			}
			if(elem.value == 'event_list')	{
				document.getElementById('event_list_first_entry_row').style.display = "block";
			}
			else	{
				document.getElementById('event_list_first_entry_row').style.display = "none";   
			}
			if(elem.value == 'submit')	{
				document.getElementById('align_submit_row').style.display = "block";
			}
			else	{
				document.getElementById('align_submit_row').style.display = "none";   
			}
		}
		function showExample(opt){
			if(opt.value == '4_column')	{
				document.getElementById('4_column_example').style.display = "block";
				document.getElementById('no_example').style.display = "none";
			}
			else	{
				document.getElementById('4_column_example').style.display = "none";   
			}
			if(opt.value == '2_column')	{
				document.getElementById('2_column_example').style.display = "block";
				document.getElementById('no_example').style.display = "none";
			}
			else	{
				document.getElementById('2_column_example').style.display = "none";
				document.getElementById('no_example').style.display = "none";
			}
			if(opt.value == '0_column')	{
				document.getElementById('0_column_example').style.display = "block";
				document.getElementById('no_example').style.display = "none";
			}
			else	{
				document.getElementById('0_column_example').style.display = "none";   
			}
			if(opt.value == 'not_set')	{
				document.getElementById('no_example').style.display = "block";
				document.getElementById('0_column_example').style.display = "none";
				document.getElementById('2_column_example').style.display = "none";
				document.getElementById('4_column_example').style.display = "none";
			}
			else	{
				document.getElementById('no_example').style.display = "none";
			}
		}
		function showDisplayText(){
			if (display_message.checked == 1){
				document.getElementById('success_message_row').style.display = "block";
			}
			else{
				document.getElementById('success_message_row').style.display = "none";	
			}
		}
		</script>
        <div class="wrap">
		<div id="icon-themes" class="icon32"></div>
		<h2>Edit Contact Form - <?php echo $mdjm_forms[$form_slug]['name']; ?></h2>
		<hr />
        <table width="100%">
        <tr valign="top">
        <td width="65%">
        <form name="edit_form_fields" id="edit_form_fields" method="post" action="<?php echo admin_url( 'admin.php?page=mdjm-contact-forms' ) . '&action&action=edit_contact_form&form_id=' . $form_slug; ?>">
        <input type="hidden" name="form_slug" id="form_slug" value="<?php echo $form_slug; ?>" />
        <table class="widefat">
        <thead>
        <th class="row-title"><strong>Name</strong></th>
        <th class="row-title"><strong>Type</strong></th>
        <th class="row-title"><strong>Settings</strong></th>
        <th class="row-title">&nbsp;</th>
        </thead>
        <?php
		if( isset( $mdjm_forms[$form_slug]['fields'] ) && !empty( $mdjm_forms[$form_slug]['fields'] ) )	{
			$i = 0;
			foreach( $mdjm_forms[$form_slug]['fields'] as $fields )	{
				if( $fields['type'] == 'captcha' && !is_plugin_active( 'really-simple-captcha/really-simple-captcha.php' ) )	{
					f_mdjm_update_notice( 'error', 'ERROR: The CAPTCHA field type requires that you have the <strong>Really Simple CAPTCHA</strong> plugin installed and activated. <a href="' . admin_url( 'plugin-install.php?tab=search&s=really+simple+captcha' ) . '"> Download &amp; install the plugin here</a>' );	
				}
				?>
				<tr<?php if( $fields['type'] == 'captcha' && !is_plugin_active( 'really-simple-captcha/really-simple-captcha.php' ) ) echo ' class="form-invalid" title="You do not have the Really Simple CAPTCHA plugin installed. This field will not work"'; elseif( $i == 0 ) echo ' class="alternate"'; ?>>
				<td><?php echo $fields['name']; ?></th>
				<td><?php echo $field_types[$fields['type']]; ?></td>
                <td><?php f_mdjm_contact_form_icons( $fields ); ?></td>
                <td><?php /*<a href="<?php echo admin_url( 'admin.php?page=mdjm-contact-forms&action=edit_contact_form&form_id=' . $mdjm_forms[$form_slug]['slug'] . '&edit=Y&field=' . $fields['slug'] ); ?>" class="add-new-h2">Edit</a>&nbsp;&nbsp;&nbsp;*/?><a href="<?php echo admin_url( 'admin.php?page=mdjm-contact-forms&action=edit_contact_form&form_id=' . $mdjm_forms[$form_slug]['slug'] . '&del=Y&field=' . $fields['slug'] ); ?>" class="add-new-h2">Delete</a></td>
				</tr>
				<?php	
				$i++;
				if( $i == 2 )
					$i = 0;
				/* Only one email/event list//captcha/submit field type allowed */
				if( !isset( $_GET['edit'] ) || $_GET['edit'] != 'Y' )	{
					if( $fields['type'] == 'email' || $fields['type'] == 'event_list' || $fields['type'] == 'captcha' || $fields['type'] == 'submit' )	{
						unset( $field_types[$fields['type']] );	
					}
				}
				/* If mapping in use, do not display again */
				if( isset( $fields['config']['mapping'] ) && !empty( $fields['config']['mapping'] ) && isset( $_GET['edit'] ) && $_GET['edit'] != 'Y' )	{
					unset( $mappings[$fields['config']['mapping']] );
				}
			}
		}
		else	{
		?>
            <tr class="form-invalid">
            <td colspan="4">No fields have been added to this form yet</th>
            </tr>
			<?php
		}
		?>
        <tfoot>
        <th class="row-title"><strong>Name</strong></th>
        <th class="row-title"><strong>Type</strong></th>
        <th class="row-title"><strong>Settings</strong></th>
        <th class="row-title">&nbsp;</th>
        </tfoot>
        </table>
        </td>
        <td valign="top">
<?php /* Create Field Options */ ?>
		<?php 
		if( isset( $_GET['edit'], $_GET['field'] ) || $_GET['edit'] != 'Y' )	{
			echo '<input type="hidden" name="field_to_edit" id="field_to_edit" value="' . $_GET['field'] . '" />';
		}
		?>
        <table class="widefat" class="alternate">
        <?php 
		if( isset( $_GET['edit'], $_GET['field'] ) && $_GET['edit'] == 'Y' )	{
			?>
            <input type="hidden" name="field_slug" value="<?php echo $_GET['field']; ?>" />
            <?php	
		}
		?>
        <tr>
        <td colspan="2" style="font-size:14px; font-weight:bold"><?php if( !isset( $_GET['edit'], $_GET['field'] ) || $_GET['edit'] != 'Y' ) echo 'Create Fields'; else echo 'Edit ' . $name . ' Field'; ?></td>
        </tr>
        <tr class="alternate">
        <td colspan="2"><p>Label:<br />
        &nbsp;&nbsp;&nbsp;<input type="text" name="field_name" id="field_name"<?php if( isset( $_GET['edit'], $_GET['field'] ) && $_GET['edit'] == 'Y' ) echo ' value="' . $name . '"'; ?> /></p>
        <p>Type:<br />
        &nbsp;&nbsp;&nbsp;<select name="field_type" id="field_type" onchange="showDiv(this)">
        <option value="">Select Field Type</option>
        <?php
			foreach( $field_types as $field_label => $field_name )	{
				if( isset( $_GET['edit'], $_GET['field'] ) && $_GET['edit'] == 'Y' )	{
					?><option value="<?php echo $field_label; ?>"<?php selected( $field_label, $mdjm_forms[$form_slug]['fields'][$_GET['field']]['type'] ); ?>><?php echo $field_name; ?></option><?php
				}
				elseif( isset( $_POST['field_type'] ) && !empty( $_POST['field_type'] ) )	{
					?><option value="<?php echo $field_label; ?>"<?php selected( $field_label, $_POST['field_type'] ); ?>><?php echo $field_name; ?></option><?php
				}
				else	{
					?><option value="<?php echo $field_label; ?>"><?php echo $field_name; ?></option><?php
				}
			}
		?>
        </select></p>

<?php
/*********************************
		HIDDEN DIVS
*********************************/
?>
        
<?php /* Placeholder */ ?>
        <div id="placeholder_row" style="display: <?php if( !empty( $placeholder_text ) ) { echo 'block;'; } else { echo 'none;'; } ?> font-size:10px">
        <p>Placeholder text:&nbsp;&nbsp;&nbsp;<input type="text" name="placeholder" id="placeholder" class="regular-text" placeholder="(optional) Placeholder text is displayed like thi  s"<?php if( !empty( $placeholder_text ) ) { echo ' value="' . $placeholder_text . '"'; } ?> /></p>
        </div>
<?php /* End Placeholder */ ?>

<?php /* Width */ ?>
        <div id="width_row" style="display: <?php if( !empty( $field_width ) ) { echo 'block;'; } else { echo 'none;'; } ?> font-size:10px">
        <p>Field Width: (optional)&nbsp;&nbsp;&nbsp;<input type="text" name="width" id="width" class="small-text"<?php if( !empty( $field_width ) ) { echo ' value="' . $field_width . '"'; } ?> /></p>
        </div>
<?php /* End Width */ ?>

<?php /* Height */ ?>
        <div id="height_row" style="display: <?php if( !empty( $field_height ) ) { echo 'block;'; } else { echo 'none;'; } ?> font-size:10px">
        <p>Field Height: (optional)&nbsp;&nbsp;&nbsp;<input type="text" name="height" id="height" class="small-text"<?php if( !empty( $field_height ) ) { echo ' value="' . $field_height . '"'; } ?> /></p>
        </div>
<?php /* End Height */ ?>

<?php /* Datepicker */ ?>
        <div id="datepicker_row" style="display: <?php if( isset( $datepicker ) && $datepicker == 'Y' ) { echo 'block;'; } else { echo 'none;'; } ?> font-size:10px">
        <p>Use Datepicker?&nbsp;&nbsp;&nbsp;<input type="checkbox" name="datepicker" id="datepicker" value="Y" <?php if( isset( $datepicker ) ) { checked( $datepicker, 'Y' ); } else echo 'checked="checked"'; ?> /></p>
        </div>
<?php /* End Datepicker */ ?>

<?php /* Checkbox Options */ ?>
        <div id="checkbox_row" style="display: <?php if( $mdjm_forms[$form_slug]['fields'][$_GET['field']]['type'] == 'checkbox' ) { echo 'block;'; } else { echo 'none;'; } ?>  font-size:10px">
        <p>Checked Value:<br />
		&nbsp;&nbsp;&nbsp;<input type="text" name="checked_value" id="checked_value" class="small-text" placeholder="Y"<?php if( !empty( $checked_value ) ) { echo ' value="' . $checked_value . '"'; } ?> /></p>
        <p>Checked?&nbsp;&nbsp;&nbsp;<input type="checkbox" name="is_checked" id="is_checked" value="Y"<?php if( $mdjm_forms[$form_slug]['fields'][$_GET['field']]['type'] == 'checkbox' ) { checked( 'Y', $mdjm_forms[$form_slug]['fields'][$_GET['field']]['config']['is_checked'] ); } ?> /></p>
        </div>
<?php /* End Checkbox Options */ ?>

<?php /* Select Options */ ?>
        <div id="select_options_row" style="display: <?php if( isset( $select_options ) && !empty( $select_options ) ) { echo 'block;'; } else { echo 'none;'; } ?> font-size:10px">
        <p>Selectable Options:<br />
		&nbsp;&nbsp;&nbsp;<textarea name="select_options" id="select_options" class="all-options" rows="5" placeholder="One per line"><?php if( isset( $select_options ) && !empty( $select_options ) ) { echo $select_options; } ?></textarea></p>
        </div>
<?php /* End Select Options */ ?>

<?php /* Event List First Entry */ ?>
        <div id="event_list_first_entry_row" style="display: <?php if( !empty( $first_entry ) ) {echo 'block;';} else {echo 'none;';} ?> font-size:10px">
        <p>Event List First Entry:<br />
		&nbsp;&nbsp;&nbsp;<input type="text" name="event_list_first_entry" id="event_list_first_entry" class="regular-text" placeholder="i.e. Select Event Type"<?php if( isset( $_GET['edit'], $_GET['field'] ) && $_GET['edit'] == 'Y' && !empty( $first_entry ) ) echo ' value="' . $first_entry . '"'; ?> /></p>
        </div>
<?php /* End Event List First Entry */ ?>

<?php /* Submit Align */ ?>
        <div id="align_submit_row" style="display: <?php if( !empty( $submit_align ) ) {echo 'block;';} else {echo 'none;';} ?> font-size:10px">
        <p>Submit Button Alignment:<br />
		&nbsp;&nbsp;&nbsp;<select name="submit_align" id="submit_align">
        <option value=""<?php if( !empty( $submit_align ) ) selected( $submit_align, '' ); ?>>None</option>
        <option value="left"<?php if( !empty( $submit_align ) ) selected( $submit_align, 'left' ); ?>>Left</option>
        <option value="center"<?php if( !empty( $submit_align ) ) selected( $submit_align, 'center' ); ?>>Centre</option>
        <option value="right"<?php if( !empty( $submit_align ) ) selected( $submit_align, 'right' ); ?>>Right</option>
        </select></p>
        </div>
<?php /* End Submit Align */ ?>

<?php
/*********************************
		END OF HIDDEN DIVS
*********************************/
		if( isset( $_GET['edit'], $_GET['field'] ) && $_GET['edit'] == 'Y' )	{
			$selected = true;
			$req = $mdjm_forms[$form_slug]['fields'][$_GET['field']]['config']['required'];
		}
		elseif( isset( $_POST['required'] ) && $_POST['required'] == 'Y' )	{
			$selected = true;
			$req = $_POST['required'];
		}
		else	{
			$selected = false;	
		}
?>
        <p>Required?&nbsp;&nbsp;&nbsp;<input type="checkbox" name="required" id="required" value="Y"<?php if( $selected ) checked( 'Y', $req ); ?> /></p>
        <p>Label CSS Class: (optional)<br />
		&nbsp;&nbsp;&nbsp;<input type="text" name="label_class" id="label_class"<?php if( isset( $_GET['edit'], $_GET['field'] ) && $_GET['edit'] == 'Y' && !empty( $class ) ) echo ' value="' . $label_class . '"'; ?> /></p>
        <p>Input Field CSS Class: (optional)<br />
		&nbsp;&nbsp;&nbsp;<input type="text" name="input_class" id="input_class"<?php if( isset( $_GET['edit'], $_GET['field'] ) && $_GET['edit'] == 'Y' && !empty( $class ) ) echo ' value="' . $input_class . '"'; ?> /></p>
         <p>Map to Field:<br />&nbsp;&nbsp;&nbsp;
         <select name="mapping" id="mapping">
         <option value="none">No Mapping</option>
         <?php
         foreach( $mappings as $mapping => $mapping_name )	{
         	?><option value="<?php echo $mapping; ?>"<?php if( isset( $_GET['edit'], $_GET['field'] ) && $_GET['edit'] == 'Y' && !empty( $field_mapping ) ) { selected( $mapping, $field_mapping ); } ?>><?php echo $mapping_name; ?></option><?php
         }
		 ?>
         </select>
        </td>
        </tr>
        <tr class="alternate">
        <td colspan="2">&nbsp;&nbsp;&nbsp;
		<?php
        if( !isset( $_GET['edit'], $_GET['field'] ) || $_GET['edit'] != 'Y' ) { 
			submit_button( 'Add Field', 'primary small', 'submit', false, '' );
		}
		else	{
			submit_button( 'Edit Field', 'primary small', 'submit', false, '' ); 
			?>
			&nbsp;&nbsp;&nbsp;<a class="button button-secondary button-small" href="<?php echo admin_url( 'admin.php?page=mdjm-contact-forms&action=edit_contact_form&form_id=' . $_GET['form_id'] ); ?>" class="add-new-h2">Cancel</a>
            <?php
		}
		?>
        </td>
<?php /* End Create Field Options */ ?>

<?php /* Example display */ ?>
        <tr>
        <td style="font-size:14px; font-weight:bold">Layout Example</td>
        </tr>
        <tr class="alternate">
        <td>
        
        <?php /* No Example */ ?>
        <div id="no_example" style="display: <?php if( !isset( $mdjm_forms[$form_slug]['config']['layout'] ) ) echo 'block;'; else echo 'none;'; ?> font-size:10px">
        <table>
        <tr>
        <td colspan="4">No display type is set</td>
        </tr>
        </table>
        </div>
        <?php /* End No Example */ ?>
        
		<?php /* 4 Column Example */ ?>
        <div id="4_column_example" style="display: <?php if( isset( $mdjm_forms[$form_slug]['config']['layout'] ) && $mdjm_forms[$form_slug]['config']['layout'] == '4_column' ) echo 'block;'; else echo 'none;'; ?> font-size:10px">
        <table>
        <tr>
        <td style="font-size:12px">First Name:</td>
        <td><input type="text" value="John" style="font-size:12px" /></td>
        <td style="font-size:12px">Last Name:</td>
        <td><input type="text" value="Smith" style="font-size:12px" /></td>
        </tr>
        <tr>
        <td style="font-size:12px">Email:</td>
        <td><input type="email" value="John@domain.com" style="font-size:12px" /></td>
        <td style="font-size:12px">Telephone:</td>
        <td><input type="tel" value="01234 567890" style="font-size:12px" /></td>
        </tr>
        <tr>
        <td colspan="4"><input type="button" value="Submit" style="font-size:12px" class="button-primary" /></td>
        </tr>
        <tr>
        <td style="font-size:10px" colspan="4">Ignore Font and form input size &amp; styling as these will match your theme's css</td>
        </tr>
        </table>
        </div>
        <?php /* End 4 Column Example */ ?>
        
        <?php /* 2 Column Example */ ?>
        <div id="2_column_example" style="display: <?php if( isset( $mdjm_forms[$form_slug]['config']['layout'] ) && $mdjm_forms[$form_slug]['config']['layout'] == '2_column' ) echo 'block;'; else echo 'none;'; ?> font-size:10px">
        <table>
        <tr>
        <td style="font-size:12px">First Name:</td>
        <td ><input type="text" value="John" style="font-size:12px" /></td>
        </tr>
        <tr>
        <td style="font-size:12px">Last Name:</td>
        <td><input type="text" value="Smith" style="font-size:12px" /></td>
        </tr>
        <tr>
        <td style="font-size:12px">Email:</td>
        <td><input type="email" value="John@domain.com" style="font-size:12px" /></td>
        </tr>
        <tr>
        <td style="font-size:12px">Telephone:</td>
        <td><input type="tel" value="01234 567890" style="font-size:12px" /></td>
        </tr>
        <tr>
        <td colspan="2"><input type="button" value="Submit" style="font-size:12px" class="button-primary" /></td>
        </tr>
        <tr>
        <td style="font-size:10px" colspan="2">Ignore Font and form input size &amp; styling as these will match your theme's css</td>
        </tr>
        </table>
        </div>
        <?php /* End 2 Column Example */ ?>
        
        <?php /* 0 Column Example */ ?>
        <div id="0_column_example" style="display: <?php if( isset( $mdjm_forms[$form_slug]['config']['layout'] ) && $mdjm_forms[$form_slug]['config']['layout'] == '0_column' ) echo 'block;'; else echo 'none;'; ?> font-size:10px">
        <p style="font-size:12px">First Name:<br />
        <input type="text" value="John" style="font-size:12px" /></p>
        <p style="font-size:12px">Last Name:<br />
        <input type="text" value="Smith" style="font-size:12px" /></p>
        <p style="font-size:12px">Email:<br />
        <input type="email" value="John@domain.com" style="font-size:12px" /></p>
        <p style="font-size:12px">Telephone:<br />
        <input type="tel" value="01234 567890" style="font-size:12px" /></p>
        <p style="font-size:10px"><input type="button" value="Submit" style="font-size:12px" class="button-primary" /></p>
        <p style="font-size:10px">Ignore Font and form input size &amp; styling as these will match your theme's css</p>
        </div>
        <?php /* End 0 Column Example */ ?>
        
        </td>
        </tr>
        </table>
        </td>
        </tr>
        </table>
        </form>
<?php /* End Example Display */ ?>
<?php /* Configuration Options */ ?>
        <hr />
        <h2>Configuration</h2>
        <form name="form_config" id="form_config" method="post" action="<?php echo admin_url( 'admin.php?page=mdjm-contact-forms' ) . '&action&action=edit_contact_form&form_id=' . $form_slug; ?>">
        <input type="hidden" name="form_slug" id="form_slug" value="<?php echo $form_slug; ?>" />
        <table class="form-table">
        <tr>
        <th scope="row"><label for="email_from_name">Email From Name:</label></th>
        <td><input type="text" name="email_from_name" id="email_from_name" class="regular-text" value="<?php if( isset( $mdjm_forms[$form_slug]['config']['email_from_name'] ) ) echo $mdjm_forms[$form_slug]['config']['email_from_name']; else echo WPMDJM_CO_NAME; ?>" /><span class="description">The display name you want to use in the email From field</span></td>
        </tr>
        <tr>
        <th scope="row"><label for="email_from">Email From Address:</label></th>
        <td><input type="email" name="email_from" id="email_from" class="regular-text" value="<?php if( isset( $mdjm_forms[$form_slug]['config']['email_from'] ) ) echo $mdjm_forms[$form_slug]['config']['email_from']; else echo $mdjm_options['system_email']; ?>" /><span class="description">The email address that the email should be sent from. Should be a valid address</span></td>
        </tr>
        <tr>
        <th scope="row"><label for="email_to">Email To:</label></th>
        <td><input type="email" name="email_to" id="email_to" class="regular-text" value="<?php if( isset( $mdjm_forms[$form_slug]['config']['email_to'] ) ) echo $mdjm_forms[$form_slug]['config']['email_to']; else echo $mdjm_options['system_email']; ?>" /><span class="description">The email address to which the enquiry should be sent</span></td>
        </tr>
        <tr>
        <th scope="row"><label for="email_subject">Email Subject:</label></th>
        <td><input type="text" name="email_subject" id="email_subject" class="regular-text" value="<?php if( isset( $mdjm_forms[$form_slug]['config']['email_subject'] ) ) echo $mdjm_forms[$form_slug]['config']['email_subject']; else echo $mdjm_forms[$form_slug]['name'] . ' form submission from ' . WPMDJM_CO_NAME . ' website'; ?>" /><span class="description">The subject to be used in the email</span></td>
        </tr>
        <tr>
        <th scope="row"><label for="reply_to">Reply to sender?</label></th>
        <td>
        <?php
		if( isset( $mdjm_forms[$form_slug]['config']['reply_to'] ) && $mdjm_forms[$form_slug]['config']['reply_to'] == 'Y' )	{
			$check = 'Y';
		}
		elseif ( $mdjm_forms[$form_slug]['config']['reply_to'] != 'Y' )	{
			$check = 'N';	
		}
		else	{
			$check = 'N';	
		}
		?>
        <input type="checkbox" name="reply_to" id="reply_to" value="Y"<?php checked( 'Y', $check ); ?> /><span class="description">Do you want to be able to reply to the sender by clicking Reply within the email?</span></td>
        </tr>
        <tr>
        <th scope="row"><label for="copy_sender">Copy Sender?</label></th>
        <td><input type="checkbox" name="copy_sender" id="copy_sender" value="Y"<?php if( isset( $mdjm_forms[$form_slug]['config']['copy_sender'] ) ) checked( 'Y', $mdjm_forms[$form_slug]['config']['copy_sender'] ); ?> /><span class="description">Send a copy of the message to the sender. If you select a template below, they will receive the template. Otherwise, they will receive a copy of their form</span></td>
        </tr>
        <th scope="row">On Submit:</th>
        <td>
        <table class="form-table">
        <tr>
        <th scope="row"><label for="create_enquiry">Create Enquiry?</label></th>
        <td><input type="checkbox" name="create_enquiry" id="create_enquiry" value="Y"<?php if( isset( $mdjm_forms[$form_slug]['config']['create_enquiry'] ) ) checked( 'Y', $mdjm_forms[$form_slug]['config']['create_enquiry'] ); ?> /><span class="description">Creates a new event enquiry</span></td>
        </tr>
        <?php
		$template_args = array(
							'post_type' => 'email_template',
							'orderby' => 'name',
							'order' => 'ASC',
							);
		?>
        <tr>
        <th scope="row"><label for="send_template">Reply with Template?</label></th>
        <td><select name="send_template" id="send_template">
        <option value=""<?php if( !isset( $mdjm_forms[$form_slug]['config']['send_template'] ) || empty( $mdjm_forms[$form_slug]['config']['send_template'] ) ) { echo ' selected="selected"'; } ?>>No</option>
		<?php
			$template_query = new WP_Query( $template_args );
			if ( $template_query->have_posts() ) {
				?>
                <option value="" disabled="disabled">EMAIL TEMPLATES</option>
				<?php
				while( $template_query->have_posts() ) {
					$template_query->the_post();
					?>
					<option value="<?php echo get_the_id(); ?>"<?php if( isset( $mdjm_forms[$form_slug]['config']['send_template'] ) && !empty( $mdjm_forms[$form_slug]['config']['send_template'] ) ) { selected( get_the_id(), $mdjm_forms[$form_slug]['config']['send_template'] ); } ?>><?php echo get_the_title(); ?></option>
                    <?php
				}
			}
			wp_reset_postdata();
		?>
        </select><span class="description"> Select a template if you want an instant response to the client to be generated on form submission. <strong>No Shortcodes</strong></span></td>
        </tr>
        <th scope="row"><label for="update_user">Update Existing Users?</label></th>
        <td><input type="checkbox" name="update_user" id="update_user" value="Y"<?php if( isset( $mdjm_forms[$form_slug]['config']['update_user'] ) ) checked( 'Y', $mdjm_forms[$form_slug]['config']['update_user'] ); ?> /><span class="description">If the user exists (based on email address) update their information with any mapped fields</span></td>
        </tr>
        <tr>
        <th scope="row"><label for="redirect">Redirect User?</label></th>
        <?php
		$args = array(
					'name'              => 'redirect',
					'id'                => 'redirect',
					'sort_order'        => 'ASC',
					'post_type'         => 'page',
					'show_option_none'  => 'No Redirect',
					'option_none_value' => 'no_redirect', 
					);
		if( isset( $mdjm_forms[$form_slug]['config']['redirect'] ) )	{
			$args['selected'] = $mdjm_forms[$form_slug]['config']['redirect'];
		}
		?>
        <td><?php wp_dropdown_pages( $args ); ?><span class="description">Redirects user to selected page on successful form submission. Overides <span class="code">Display Message</span></span></td>
        </tr>
        <tr>
        <th scope="row"><label for="display_message">Display Message?</label></th>
        <td><input type="checkbox" name="display_message" id="display_message" value="Y" onclick="showDisplayText()"<?php if( isset( $mdjm_forms[$form_slug]['config']['display_message'] ) ) checked( 'Y', $mdjm_forms[$form_slug]['config']['display_message'] ); ?> /><span class="description">Text to be displayed to the user when the form is successfully submitted. Only valid if <span class="code">Redirect User</span> is not selected</span></td>
        </tr>
        </table>
<?php
/*********************************
		HIDDEN DIVS
*********************************/
?>
        
<?php /* Display Message */ ?>
		<?php
		if( isset( $mdjm_forms[$form_slug]['config']['display_message'] ) && $mdjm_forms[$form_slug]['config']['display_message'] == 'Y' )	{
			$display = 'block';
		}
		else	{
			$display = 'none';	
		}
		?>
        <div id="success_message_row" style="display: <?php echo $display; ?>; font-size:10px">
        <?php
		$mce_settings = array(
							'textarea_rows' => 6,
							'media_buttons' => false,
							'textarea_name' => 'display_message_text',
							'teeny'         => false,
							);
		$content = '';
		if( isset( $mdjm_forms[$form_slug]['config']['display_message_text'] ) )	{
			$content = $mdjm_forms[$form_slug]['config']['display_message_text'];	
		}
		?>
        <table class="form-table">
        <tr>
        <th scope="row"><label for="display_message_text">Message:</label></th>
        <td><?php wp_editor( html_entity_decode( $content ), 'display_message_text', $mce_settings ); ?></td>
        </tr>
        </table>
        </div>
<?php /* End Display Message */ ?>       
        </td>
        </tr>
        <tr>
        <th scope="row"><label for="required_field_text">Error Message:</label></th>
        <?php
		if( isset( $mdjm_forms[$form_slug]['config']['required_field_text'] ) && !empty( $mdjm_forms[$form_slug]['config']['required_field_text'] ) )	{
			$value = $mdjm_forms[$form_slug]['config']['required_field_text'];
		}
		elseif( isset( $_POST['required_field_text'] ) )	{
			$value = $_POST['required_field_text'];
		}
		else	{
			$value = '{FIELD_NAME} is a required field. Please try again.';
		}
		?>
        <td><input type="text" name="required_field_text" id="required_field_text" class="regular-text" value="<?php echo $value; ?>" /> <span class="description">Text to be displayed if a required field is not completed. Use the <span class="code">{FIELD_NAME}</span> shortcode to output the missing field name</span></td>
        </tr>
        <tr>
        <th scope="row"><label for="error_text_color">Error Text Colour:</label></th>
        <?php
		if( isset( $mdjm_forms[$form_slug]['config']['error_text_color'] ) && !empty( $mdjm_forms[$form_slug]['config']['error_text_color'] ) )	{
			$value = $mdjm_forms[$form_slug]['config']['error_text_color'];
		}
		elseif( isset( $_POST['error_text_color'] ) )	{
			$value = $_POST['error_text_color'];
		}
		else	{
			$value = 'FF0000';
		}
		?>
        <td><input type="text" name="error_text_color" id="error_text_color" class="regular-text" value="<?php echo $value; ?>" /> <span class="description">The colour in which error message text should be displayed. Default is <span class="code">FF0000</span> <font style="color:#FF0000">(Red)</font></span></td>
        </tr>
        <tr>
        <th scope="row"><label for="layout">Form Layout:</label></th>
        <td><select name="layout" id="layout" onchange="showExample(this)" />
        <option value="not_set"<?php if( isset( $mdjm_forms[$form_slug]['config']['layout'] ) ) selected( 'not_set', $mdjm_forms[$form_slug]['config']['layout'] ); ?>>Not Set</option>
        <option value="4_column"<?php if( isset( $mdjm_forms[$form_slug]['config']['layout'] ) ) selected( '4_column', $mdjm_forms[$form_slug]['config']['layout'] ); ?>>4 Column Table</option>
        <option value="2_column"<?php if( isset( $mdjm_forms[$form_slug]['config']['layout'] ) ) selected( '2_column', $mdjm_forms[$form_slug]['config']['layout'] ); ?>>2 Column Table</option>
        <option value="0_column"<?php if( isset( $mdjm_forms[$form_slug]['config']['layout'] ) ) selected( '0_column', $mdjm_forms[$form_slug]['config']['layout'] ); ?>>No Table</option>
        </select> <span class="description">Select how you want the form to be displayed on your page. <span class="code">Not Set</span> will default to <span class="code">4 Column Table</span> layout</span>
        </td>
        </tr>
        <tr>
        <th scope="row"><label for="row_height">Table Row Height:</label></th>
        <td><input type="text" name="row_height" id="row_height" class="small-text" value="<?php if( !empty( $mdjm_forms[$form_slug]['config']['row_height'] ) ) echo $mdjm_forms[$form_slug]['config']['row_height']; ?>" /> <span class="description">Adjust the table row height as required (optional - applies to table layout only)</span></td>
        </tr>
        <tr>
        <td colspan="2"><?php submit_button( 'Save Config', 'primary', 'submit', false, '' ); ?></td>
        </tr>
        </table>
        </form>
<?php /* End Configuration Options */ ?>
        </div>
        <?php
	} // f_mdjm_edit_contact_form

/* Process any form submission */	
	if( isset( $_POST ) && !empty( $_POST ) )	{
		if( isset( $_POST['submit'] ) && !empty( $_POST['submit'] ) )	{
			/* New Form */
			if( $_POST['submit'] == 'Create Contact Form' )	{
				$func = f_mdjm_show_add_contact_form;
				if( isset( $_POST['form_name'] ) && !empty( $_POST['form_name'] ) )	{
					$func( $_POST['form_name'] );	
				}
				else	{
					$func();	
				}
				exit;
			}
			/* Form Name */
			elseif( $_POST['submit'] == 'Begin Creating Form' )	{
				$mdjm_forms = get_option( 'mdjm_contact_forms' );
				$form_name = sanitize_text_field( $_POST['form_name'] );
				$form_slug = preg_replace( '/[^a-zA-Z0-9_-]$/s', '', $form_name );
				$form_slug = 'mdjm_' . strtolower( str_replace( array( ' ', '.' ), array( '_', '' ), $form_slug ) );
				if( $mdjm_forms[$form_slug] )
					$form_slug = strtolower( str_replace( ' ', '_', $form_name ) ) . '_';
				
				$mdjm_forms[$form_slug] = $form_slug;
				$mdjm_forms[$form_slug] = array();
				$mdjm_forms[$form_slug]['slug'] = $form_slug;
				$mdjm_forms[$form_slug]['name'] = sanitize_text_field( $_POST['form_name'] );
				
				/* Form Options */
				$mdjm_forms[$form_slug]['config']['email_from'] = $mdjm_options['system_email'];
				$mdjm_forms[$form_slug]['config']['email_from_name'] = WPMDJM_CO_NAME;
				$mdjm_forms[$form_slug]['config']['email_to'] = $mdjm_options['system_email'];
				$mdjm_forms[$form_slug]['config']['reply_to'] = 'Y';
				$mdjm_forms[$form_slug]['config']['email_subject'] = $mdjm_forms[$form_slug]['name'] . ' form submission from ' . WPMDJM_CO_NAME . ' website';
				$mdjm_forms[$form_slug]['config']['copy_sender'] = 'N';
				$mdjm_forms[$form_slug]['config']['update_user'] = 'Y';
				$mdjm_forms[$form_slug]['config']['required_field_text'] = '{FIELD_NAME} is a required field. Please try again.';
				$mdjm_forms[$form_slug]['config']['error_text_color'] = 'FF0000';
				$mdjm_forms[$form_slug]['config']['layout'] = '0_column';
				
				
				if( update_option( 'mdjm_contact_forms', $mdjm_forms ) )	{
					f_mdjm_update_notice( 'updated', '<strong>' . $_POST['form_name'] . '</strong> contact form created successfully. Begin adding fields' );	
				}
				f_mdjm_edit_contact_form( $form_slug );
				exit;
			}
			/* New Field */
			elseif( $_POST['submit'] == 'Add Field' )	{
				/* Remove $_GET['del'] && $_GET['edit'] if set */
				if( isset( $_GET['del'] ) ) unset( $_GET['del'] );
				if( isset( $_GET['edit'] ) ) unset( $_GET['edit'] );
				
				/* Validation */
				if( !isset( $_POST['field_name'] ) || empty( $_POST['field_name'] ) )	{
					f_mdjm_update_notice( 'error', 'ERROR: No field name entered' );
					f_mdjm_edit_contact_form( $_POST['form_slug'] );
					exit;
				}
				elseif( !isset( $_POST['field_type'] ) || empty( $_POST['field_type'] ) )	{
					f_mdjm_update_notice( 'error', 'ERROR: No field type selected' );
					f_mdjm_edit_contact_form( $_POST['form_slug'] );
					exit;
				}
				elseif( $_POST['field_type'] == 'select' && empty( $_POST['select_options'] ) )	{
					f_mdjm_update_notice( 'error', 'ERROR: When choosing a Select List field, you must enter some Selectable Options' );
					f_mdjm_edit_contact_form( $_POST['form_slug'] );
					exit;	
				}
				elseif( $_POST['field_type'] == 'select_multi' && empty( $_POST['select_options'] ) )	{
					f_mdjm_update_notice( 'error', 'ERROR: When choosing a Select List field, you must enter some Selectable Options' );
					f_mdjm_edit_contact_form( $_POST['form_slug'] );
					exit;	
				}
				elseif( $_POST['field_type'] == 'captcha' && !is_plugin_active( 'really-simple-captcha/really-simple-captcha.php' ) )	{
					f_mdjm_update_notice( 'error', 'ERROR: The CAPTCHA field type requires that you have the <strong>Really Simple CAPTCHA</strong> plugin installed and activated. <a href="' . admin_url( 'plugin-install.php?tab=search&s=really+simple+captcha' ) . '"> Download &amp; install the plugin here</a>' );
					f_mdjm_edit_contact_form( $_POST['form_slug'] );
					exit;	
				}
				/* Add the new field */
				else	{
					$mdjm_forms = get_option( 'mdjm_contact_forms' );
					$field_name = sanitize_text_field( $_POST['field_name'] );
					$field_slug = preg_replace( '/[^a-zA-Z0-9_-]$/s', '', $field_name );
					$field_slug = 'mdjm_' . strtolower( str_replace( array( ' ', '.' ), array( '_', '' ), $field_slug ) );
					
					if( $mdjm_forms[$_POST['form_slug']]['fields'][$field_slug] )
						$field_slug = strtolower( str_replace( ' ', '_', $field_slug ) ) . '_';
						
					$pos = count( $mdjm_forms[$_POST['form_slug']]['fields'] );
					if( $_POST['field_type'] == 'captcha' )
						$pos = 98;
					if( $_POST['field_type'] == 'submit' )
						$pos = 99;
						
					$mdjm_forms[$_POST['form_slug']]['fields'][$field_slug] = array(
																	'slug'     => $field_slug,
																	'name'     => sanitize_text_field( $_POST['field_name'] ),
																	'type'     => sanitize_text_field( $_POST['field_type'] ),
																	'config'   => array(),
																	'position' => $pos,
																	);
					/* Classes */
					if( isset( $_POST['label_class'] ) && !empty( $_POST['label_class'] ) )	{
						$mdjm_forms[$_POST['form_slug']]['fields'][$field_slug]['config']['label_class'] = $_POST['label_class'];
					}
					if( isset( $_POST['input_class'] ) && !empty( $_POST['input_class'] ) )	{
						$mdjm_forms[$_POST['form_slug']]['fields'][$field_slug]['config']['input_class'] = $_POST['input_class'];
					}
					
					/* Size */
					if( isset( $_POST['width'] ) && !empty( $_POST['width'] ) )	{
						$mdjm_forms[$_POST['form_slug']]['fields'][$field_slug]['config']['width'] = $_POST['width'];
					}
					if( isset( $_POST['height'] ) && !empty( $_POST['height'] ) )	{
						$mdjm_forms[$_POST['form_slug']]['fields'][$field_slug]['config']['height'] = $_POST['height'];
					}
					
					/* Field Mapping */
					$mdjm_forms[$_POST['form_slug']]['fields'][$field_slug]['config']['mapping'] = $_POST['mapping'];
					if( $_POST['field_type'] == 'email' )	{
						$mdjm_forms[$_POST['form_slug']]['fields'][$field_slug]['config']['mapping'] = 'user_email';
					}
					
					/* Date Fields */
					if( $_POST['field_type'] == 'date' && isset( $_POST['datepicker'] ) && $_POST['datepicker'] == 'Y' )	{
						$mdjm_forms[$_POST['form_slug']]['fields'][$field_slug]['config']['datepicker'] = 'Y';
					}
					/* Checkbox Fields */
					if( $_POST['field_type'] == 'checkbox' )	{
						if( isset( $_POST['is_checked'] ) && $_POST['is_checked'] == 'Y' )	{
							$mdjm_forms[$_POST['form_slug']]['fields'][$field_slug]['config']['is_checked'] = 'Y';
						}
						if( isset( $_POST['checked_value'] ) && !empty( $_POST['checked_value'] ) )	{
							$mdjm_forms[$_POST['form_slug']]['fields'][$field_slug]['config']['checked_value'] = sanitize_text_field( $_POST['checked_value'] );
						}
						else	{
							$mdjm_forms[$_POST['form_slug']]['fields'][$field_slug]['config']['checked_value'] = 'Y';	
						}
					}
					
					/* Select List Fields */
					if( $_POST['field_type'] == 'select' || $_POST['field_type'] == 'select_multi' )	{
						$mdjm_forms[$_POST['form_slug']]['fields'][$field_slug]['config']['options'] = $_POST['select_options'];
					}
					
					/* Event List First Entry */
					if( $_POST['field_type'] == 'event_list' && !empty( $_POST['event_list_first_entry'] ) )	{
						$mdjm_forms[$_POST['form_slug']]['fields'][$field_slug]['config']['event_list_first_entry'] = sanitize_text_field( $_POST['event_list_first_entry'] );
					}
					
					/* Required Field */
					if( isset( $_POST['required'] ) && $_POST['required'] == 'Y' )	{
						$mdjm_forms[$_POST['form_slug']]['fields'][$field_slug]['config']['required'] = 'Y';
					}
					
					/* Placeholder Text */
					if( isset( $_POST['placeholder'] ) && !empty( $_POST['placeholder'] ) )	{
						$mdjm_forms[$_POST['form_slug']]['fields'][$field_slug]['config']['placeholder'] = sanitize_text_field( $_POST['placeholder'] );
					}
					
					/* Submit Button */
					if( isset( $_POST['submit_align'] ) && $_POST['submit_align'] != '' )	{
						$mdjm_forms[$_POST['form_slug']]['fields'][$field_slug]['config']['submit_align'] = $_POST['submit_align'];
					}
					
					update_option( 'mdjm_contact_forms', $mdjm_forms );
					f_mdjm_update_notice( 'updated', 'The <strong>' . sanitize_text_field( $_POST['field_name'] ) . '</strong> field was added' );
				}
			}
			/* Save Configuration */
			elseif( $_POST['submit'] == 'Save Config' )	{
				/* Validate */
				if( !isset( $_POST['email_from'] ) || empty( $_POST['email_from'] ) )	{
					f_mdjm_update_notice( 'error', 'ERROR: Configuration needs a From email address' );
				}
				if( !isset( $_POST['email_from_name'] ) || empty( $_POST['email_from_name'] ) )	{
					f_mdjm_update_notice( 'error', 'ERROR: Configuration needs a From email display name' );
				}
				if( !isset( $_POST['email_to'] ) || empty( $_POST['email_to'] ) )	{
					f_mdjm_update_notice( 'error', 'ERROR: Configuration needs a To email address' );
				}
				elseif( !filter_var( $_POST['email_from'], FILTER_VALIDATE_EMAIL ) ) {
					f_mdjm_update_notice( 'error', 'ERROR: Invalid From email address format' );
				}
				elseif( !filter_var( $_POST['email_to'], FILTER_VALIDATE_EMAIL ) ) {
					f_mdjm_update_notice( 'error', 'ERROR: Invalid To email address format' );
				}
				elseif( !isset( $_POST['email_subject'] ) || empty( $_POST['email_subject'] ) )	{
					f_mdjm_update_notice( 'error', 'ERROR: Enter a subject to be used in the email' );	
				}
				elseif( !isset( $_POST['required_field_text'] ) || empty( $_POST['required_field_text'] ) )	{
					f_mdjm_update_notice( 'error', 'ERROR: Enter an error message to be displayed if a required field is not populated' );
				}
				elseif( isset( $_POST['display_message'] ) && $_POST['display_message'] == 'Y' && empty( $_POST['display_message_text'] ) )	{
					f_mdjm_update_notice( 'error', 'ERROR: You selected <strong>Display Message</strong> but did not enter a message to display to the user' );	
				}
				else	{
					$mdjm_forms = get_option( 'mdjm_contact_forms' );
					$this_form = $mdjm_forms[$_POST['form_slug']];
					
					$mdjm_forms[$_POST['form_slug']]['config']['email_from'] = $_POST['email_from'];
					$mdjm_forms[$_POST['form_slug']]['config']['email_from_name'] = $_POST['email_from_name'];
					$mdjm_forms[$_POST['form_slug']]['config']['email_to'] = $_POST['email_to'];
					$mdjm_forms[$_POST['form_slug']]['config']['email_subject'] = $_POST['email_subject'];
					if( isset( $_POST['reply_to'] ) && $_POST['reply_to'] == 'Y' )	{
						$mdjm_forms[$_POST['form_slug']]['config']['reply_to'] = $_POST['reply_to'];
					}
					$mdjm_forms[$_POST['form_slug']]['config']['copy_sender'] = $_POST['copy_sender'];
					
					if( isset( $_POST['create_enquiry'] ) && $_POST['create_enquiry'] == 'Y' )	{
						$mdjm_forms[$_POST['form_slug']]['config']['create_enquiry'] = $_POST['create_enquiry'];
					}
					else	{
						$mdjm_forms[$_POST['form_slug']]['config']['create_enquiry'] = false;	
					}
					if( isset( $_POST['send_template'] ) && !empty( $_POST['send_template'] ) )	{
						$mdjm_forms[$_POST['form_slug']]['config']['send_template'] = $_POST['send_template'];
					}
					if( isset( $_POST['update_user'] ) && $_POST['update_user'] == 'Y' )	{
						$mdjm_forms[$_POST['form_slug']]['config']['update_user'] = $_POST['update_user'];
					}
					else	{
						$mdjm_forms[$_POST['form_slug']]['config']['update_user'] = false;	
					}
					if( isset( $_POST['redirect'] ) && $_POST['redirect'] != 'no_redirect' )	{
						$mdjm_forms[$_POST['form_slug']]['config']['redirect'] = $_POST['redirect'];
					}
					else	{
						$mdjm_forms[$_POST['form_slug']]['config']['redirect'] = false;	
					}
					if( isset( $_POST['display_message'] ) && $_POST['display_message'] == 'Y' )	{
						$mdjm_forms[$_POST['form_slug']]['config']['display_message'] = $_POST['display_message'];
						$mdjm_forms[$_POST['form_slug']]['config']['display_message_text'] = htmlentities( stripslashes( $_POST['display_message_text'] ) );
					}
					else	{
						$mdjm_forms[$_POST['form_slug']]['config']['display_message'] = false;	
					}
					
					$mdjm_forms[$_POST['form_slug']]['config']['required_field_text'] = $_POST['required_field_text'];
					
					if( !isset( $_POST['error_text_color'] ) )	{
						$mdjm_forms[$_POST['form_slug']]['config']['error_text_color'] = 'FF0000';
					}
					else	{
						$mdjm_forms[$_POST['form_slug']]['config']['error_text_color'] = $_POST['error_text_color'];	
					}
					
					if( isset( $_POST['layout'] ) && $_POST['layout'] == 'not_set' )	{
						$_POST['layout'] = '0_column';
					}
					$mdjm_forms[$_POST['form_slug']]['config']['layout'] = $_POST['layout'];
					
					if( isset( $_POST['row_height'] ) && !empty( $_POST['row_height'] ) )	{
						$mdjm_forms[$_POST['form_slug']]['config']['row_height'] = $_POST['row_height'];
					}
					
					if( update_option( 'mdjm_contact_forms', $mdjm_forms ) )	{
						f_mdjm_update_notice( 'updated', 'Configuration Saved' );
					}
				}
			}
		}
	}
	/* Process any GET actions */
	if( isset( $_GET['action'] ) && !empty( $_GET['action'] ) )	{
		if( $_GET['action'] == 'edit_contact_form' )	{
			$func = 'f_mdjm_' . $_GET['action'];
			$func( $_GET['form_id'] );
		}
		if( $_GET['action'] == 'show_add_contact_form' )	{
			$func = 'f_mdjm_' . $_GET['action'];
			$func();
		}
		if( $_GET['action'] == 'del_contact_form' )	{
			$mdjm_forms = get_option( 'mdjm_contact_forms' );
			unset( $mdjm_forms[$_GET['form_id']] );
			if( update_option( 'mdjm_contact_forms', $mdjm_forms ) )	{
				f_mdjm_update_notice( 'updated', 'Contact Form deleted Successfully' );
				f_mdjm_show_forms();
			}
			else	{
				f_mdjm_update_notice( 'updated', 'The Contact Form could not be deleted' );
				f_mdjm_show_forms();
			}
		}		
	}
	
	else	{
		f_mdjm_show_forms();
	}
?>