<?php
	defined( 'ABSPATH' ) or die( "Direct access to this page is disabled!!!" );
	if ( !current_user_can( 'manage_options' ) )  {
		wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
	}
	
	// If recently updated, display the release notes
	f_mdjm_has_updated();

/*
* settings-debugging.php 
* 18/12/2014
* since 0.9.9
* Manage debugging
*/

	global $mdjm_options;
	
/* Check for form submission */
	if( isset( $_POST['submit'] ) )	{
		if( $_POST['submit'] == 'Save Changes' )	{
			
			/* Update the options table */
			if( !isset( $_POST['debugging'] ) || $_POST['debugging'] != '1' )	{
				$_POST['debugging'] = '0';
			}
			else	{
				$_POST['debugging'] = true;	
			}
			update_option( 'mdjm_debug', $_POST['debugging'] );
			
			$class = 'updated';
			$message = 'Settings updated';
			f_mdjm_update_notice( $class, $message );
		}
	}
	?>

    <div class="wrap">
    <div id="icon-themes" class="icon32"></div>
    <h2>Debugging</h2>
    <p><strong>Important Note:</strong> It is not recommended that you enable debugging unless the <a href="<?php f_mdjm_admin_page( 'mydjplanner' ); ?>">Mobile DJ Manager for WordPress</a> support team have asked you to do so</p>
    <form name="form-debug" id="form-debug" method="post" action="">
    <table class="form-table">
    <tr>
    <th>Enable Debugging?</th>
    <td><input type="checkbox" name="debugging" id="debugging" value="1" <?php checked( '1', get_option( 'mdjm_debug' ) ); ?> /></td>
    </tr>
    </table>
    </div>
    <?php
	
?>