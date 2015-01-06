<?php
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
				<td><span class="code">[MDJM function='Contact Form' slug="<?php echo $forms['slug']; ?>]</span></td>
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
		<th class="row-title">Action</th>
		</tfoot>
		</table>
		</td>
		<td width="25%">
		<form name="add_contact_form" id="add_contact_form" method="post" action="">
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
        <td><input type="text" name="form_name" id="form_name" class="regular-text" value="<?php if( isset($_POST['form_name'] ) ) echo $_POST['form_name']; ?>" /></td>
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
		$mdjm_forms = get_option( 'mdjm_contact_forms' );
		?>
        <script type="text/javascript">
		function showDiv(elem){
			if(elem.value == 'date')	{
				document.getElementById('datepicker_row').style.display = "block";
			}
			else	{
				document.getElementById('datepicker_row').style.display = "none";   
			}
			if(elem.value == 'select' || elem.value == 'select_multi')	{
				document.getElementById('select_options_row').style.display = "block";
			}
			else	{
				document.getElementById('select_options_row').style.display = "none";   
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
        <form name="edit_form_fields" id="edit_form_fields" method="post" action="">
        <input type="hidden" name="form_slug" id="form_slug" value="<?php echo $form_slug; ?>" />
        <table class="widefat">
        <thead>
        <th class="row-title">Name</th>
        <th class="row-title">Type</th>
        <th class="row-title">Values</th>
        <th class="row-title">Required?</th>
        </thead>
        <?php
		if( isset( $mdjm_forms[$form_slug]['fields'] ) && !empty( $mdjm_forms[$form_slug]['fields'] ) )	{
			foreach( $mdjm_forms[$form_slug]['fields'] as $fields )	{
				?>
				<tr>
				<td><?php echo $fields['name']; ?></th>
				<td><?php echo $fields['type']; ?></td>
				</tr>
				<?php	
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
        <th class="row-title">Name</th>
        <th class="row-title">Type</th>
        <th class="row-title">Values</th>
        <th class="row-title">Required?</th>
        </tfoot>
        </table>
        </td>
        <td valign="top">
        <table class="widefat" class="alternate">
        <tr>
        <td colspan="2" style="font-size:14px; font-weight:bold">Create Fields</td>
        </tr>
        <tr class="alternate">
        <td colspan="2"><p>Label:<br />
        &nbsp;&nbsp;&nbsp;<input type="text" name="field_name" id="field_name" /></p>
        <p>Type:<br />
        &nbsp;&nbsp;&nbsp;<select  name="field_type" id="field_type" onchange="showDiv(this)">
        <option value="text">Text Field</option>
        <option value="date">Date Field</option>
        <option value="email">Email Field</option>
        <option value="select">Select List</option>
        <option value="select_multi">Select List (Multiple Select)</option>
        <option value="checkbox">Checkbox</option>
        <option value="radio">Radio Button</option>
        <option value="textarea">Textarea</option>
        <option value="telephone">Telephone Number</option>
        <option value="url">URL</option>
        <option value="captcha">CAPTCHA</option>
        <option value="submit">Submit Button</option>
        </select></p>
        <div id="datepicker_row" style="display: none; font-size:10px">
        <p>Use Datepicker?&nbsp;&nbsp;&nbsp;<input type="checkbox" name="datepicker" id="datepicker" value="Y" checked="checked" /></p>
        </div>
        <div id="select_options_row" style="display: none; font-size:10px">
        <p>Selectable Options: (one per line)<br />
		&nbsp;&nbsp;&nbsp;<textarea name="select_options" id="select_options" class="all-options" rows="5"></textarea></p>
        </div>
        <p>Required?&nbsp;&nbsp;&nbsp;<input type="checkbox" name="required" id="required" value="Y" /></p>
        </td>
        </tr>
        <tr class="alternate">
        <td colspan="2">&nbsp;&nbsp;&nbsp;<?php submit_button( 'Add Field', 'primary small', 'submit', false, '' ); ?></td>
        </table>
        </td>
        </tr>
        </table>
        </div>
        <?php
	} // f_mdjm_edit_contact_form
	
	if( isset( $_POST ) && !empty( $_POST ) )	{
		if( isset( $_POST['submit'] ) && !empty( $_POST['submit'] ) )	{
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
			elseif( $_POST['submit'] == 'Begin Creating Form' )	{
				$mdjm_forms = get_option( 'mdjm_contact_forms' );
				$form_slug = sanitize_text_field( strtolower( str_replace( ' ', '-', $_POST['form_name'] ) ) );
				if( $mdjm_forms[$form_slug] )
					$form_slug = sanitize_text_field( strtolower( str_replace( ' ', '-', $_POST['form_name'] ) ) ) . '_';
				
				$mdjm_forms[$form_slug] = $form_slug;
				$mdjm_forms[$form_slug] = array();
				$mdjm_forms[$form_slug]['slug'] = $form_slug;
				$mdjm_forms[$form_slug]['name'] = $_POST['form_name'];	
				update_option( 'mdjm_contact_forms', $mdjm_forms );
				f_mdjm_edit_contact_form( $form_slug );
				exit;
			}
			elseif( $_POST['submit'] == 'Add Field' )	{
				if( !isset( $_POST['field_name'] ) || empty( $_POST['field_name'] ) )	{
					f_mdjm_update_notice( 'error', 'ERROR: No field name entered' );
					f_mdjm_edit_contact_form( $_POST['form_slug'] );
					exit;
				}
				else	{
					$mdjm_forms = get_option( 'mdjm_contact_forms' );
					$field_name = sanitize_text_field( $_POST['field_name'] );
					$field_slug = strtolower( str_replace( ' ', '-', $_POST['field_name'] ) );
					$mdjm_forms[$_POST['form_slug']]['fields'][$field_slug] = array(
																	'name'    => sanitize_text_field( $_POST['field_name'] ),
																	'type'    => sanitize_text_field( $_POST['field_type'] ),
																	);
					if( $_POST['field_type'] == 'date' && isset( $_POST['datepicker'] ) && $_POST['datepicker'] == 'Y' )	{
						$mdjm_forms[$_POST['form_slug']]['fields'][$field_slug]['datepicker'] = 'Y';
					}
					if( isset( $_POST['select_options'] ) && $_POST['select_options'] == 'Y' )	{
						$mdjm_forms[$_POST['form_slug']]['fields'][$field_slug]['options'] = $_POST['select_options'];
					}
					if( isset( $_POST['required'] ) && $_POST['required'] == 'Y' )	{
						$mdjm_forms[$_POST['form_slug']]['fields'][$field_slug]['required'] = 'Y';
					}
					update_option( 'mdjm_contact_forms', $mdjm_forms );
				}
			}
		}
	}
	if( isset( $_GET['action'] ) && !empty( $_GET['action'] ) )	{
		if( $_GET['action'] == 'edit_contact_form' )	{
			$func = 'f_mdjm_' . $_GET['action'];
			$func( $_GET['form_id'] );
		}
	}
	
	else	{
		f_mdjm_show_forms();
	}