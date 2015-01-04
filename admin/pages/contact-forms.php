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
	$mdjm_forms = get_option( 'mdjm_contact_forms' );
	?>
	<div class="wrap">
	<div id="icon-themes" class="icon32"></div>
	<h2>Contact Forms <a href="<?php echo admin_url( 'admin.php?page=mdjm-contact-forms&action=add_contact_form' ); ?>" class="add-new-h2">Add New</a></h2>
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
	if( !isset( $mdjm_contact_forms ) )	{
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
            <td><?php echo $forms['slug']['name']; ?></td>
            <td><span class="code">[MDJM function='Contact Form' slug="<?php echo $forms['slug']['slug']; ?>]</span></td>
            <td><a href="<?php echo admin_url( 'admin.php?page=mdjm-contact-forms&action=edit_contact_form&form_id=' . $forms['slug']['slug'] ); ?>" class="add-new-h2">Edit</a>&nbsp;&nbsp;&nbsp;<a href="<?php echo admin_url( 'admin.php?page=mdjm-contact-forms&action=del_contact_form&form_id=' . $forms['slug']['slug'] ); ?>" class="add-new-h2">Delete</a></td>
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
    <form name="add_contact_form" id="add_contact_form" method="post" action="add-contact-form">
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
