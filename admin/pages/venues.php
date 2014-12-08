<?php
	defined('ABSPATH') or die("Direct access to this page is disabled!!!");
	if ( !current_user_can( 'manage_options' ) && !current_user_can( 'manage_mdjm' ) )  {
		wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
	}
	
	// If recently updated, display the release notes
	f_mdjm_has_updated();

/**
* * * * * * * * * * * * * * * MDJM * * * * * * * * * * * * * * *
* venues.php
*
* Displays table of venues & enables adding new / editing existing
*
* Calls: class-mdjm-venue-table.php
*
* @since 1.0
*
*/

/**
 * Check for any form submissions that take place outside the 
 * Bulk Actions and process
 *
 * @param $_POST
 *
 * @since 1.0
*/
	if( isset( $_POST['action'] ) )	{
		$func = 'f_mdjm_' . $_POST['action'];
		if( function_exists( $func ) ) $func( $_POST );
	}
	
/**
 * Display the venues within the Admin UI
 * 
 * Calls: class-wp-list-table.php; class-mdjm-venue-table.php
 *
 * @since 1.0
*/
	function f_mdjm_render_venues_table()	{
		if( !class_exists( 'WP_List_Table' ) ){
			require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
		}
	
		if( ! class_exists( 'MDJM_Venues_Table' ) ) {
			require_once( WPMDJM_PLUGIN_DIR . '/admin/includes/class-mdjm-venue-table.php' );
		}
		$venues_table = new MDJM_Venues_Table();
		?>
		</pre><div class="wrap"><h2>Venues <?php if( current_user_can( 'administrator' ) || dj_can( 'add_venue' ) )	echo '<a href="' . admin_url() . 'admin.php?page=mdjm-venues&action=add_venue_form" class="add-new-h2">Add New</a></h2>';
		
		$venues_table->prepare_items();
		?>
		<form method="post" name="mdjm_venue" id="mdjm_venue">
		<input type="hidden" name="page" value="mdjm-venues">
		<?php
		$venues_table->search_box( 'Search Venues', 'search_id' );
		
		$venues_table->display(); 
		?>
        </form></div>
        <?php 
	} // f_mdjm_render_venues_table

/**
 * Display a form for adding a new venue
 * 
 *
 * @since 1.0
*/
	function f_mdjm_add_venue_form()	{
		if( !current_user_can( 'administrator' ) && !dj_can( 'add_venue' ) )	wp_die( 'You do not have permissions to perform this action. Contact your <a href="mailto:' . $mdjm_options['system_email'] . '">administrator</a> for assistance.' );
		?>
		<div class="wrap">
        <h2>Add Venue</h2>
        <form method="post" action="<?php echo f_mdjm_admin_page( 'venues' ); ?>">
		<input type="hidden" name="action" value="add_venue" />
       	<?php wp_nonce_field( 'mdjm_add_venue_verify' ); ?>
        <table class="form-table">
        <tr>
        <th scope="row"><label for="venue_name">Venue Name:</label></th>
        <td>
        <input type="text" name="venue_name" id="venue_name" class="regular-text" value="<?php echo $_POST['venue_name']; ?>" /></td>
        <th scope="row"><label for="venue_contact">Contact Name:</label></th>
        <td><input type="text" name="venue_contact" id="venue_contact" class="regular-text" value="<?php echo $_POST['venue_contact']; ?>" /></td>
        </tr>
        <tr>
        <th scope="row"><label for="venue_phone">Contact Phone:</label></th>
        <td><input type="tel" name="venue_phone" id="venue_phonee" class="regular-text" value="<?php echo $_POST['venue_phone']; ?>" /></td>
        <th scope="row"><label for="venue_email">Contact Email:</label></th>
        <td><input type="email" name="venue_email" id="venue_email" class="regular-text" value="<?php echo $_POST['venue_email']; ?>" /></td>
        </tr>
		<tr>
        <th scope="row"><label for="venue_address1">Address Line 1:</label></th>
        <td><input type="text" name="venue_address1" id="venue_address1" class="regular-text" value="<?php echo $_POST['venue_address1']; ?>" /></td>
        <th scope="row"><label for="venue_address2">Address Line 2:</label></th>
        <td><input type="text" name="venue_address2" id="venue_address2" class="regular-text" value="<?php echo $_POST['venue_address2']; ?>" /></td>
        </tr>
        <tr>
        <th scope="row"><label for="venue_town">Town:</label></th>
        <td><input type="text" name="venue_town" id="venue_town" class="regular-text" value="<?php echo $_POST['venue_town']; ?>" /></td>
        <th scope="row"><label for="venue_county">County:</label></th>
        <td><input type="text" name="venue_county" id="venue_county" class="regular-text" value="<?php echo $_POST['venue_county']; ?>" /></td>
        </tr>
		<tr>
        <th scope="row"><label for="venue_postcode">Post Code:</label></th>
        <td colspan="3"><input type="text" name="venue_postcode" id="venue_postcode" class="regular-text" value="<?php echo $_POST['venue_postcode']; ?>" /></td>
        </tr>
		<tr>
        <th scope="row"><label for="venue_information">Information:</label></th>
        <td colspan="3"><textarea name="venue_information" id="venue_information" cols="80" rows="10"><?php echo $_POST['venue_information']; ?></textarea></td>
        </tr>
        <tr>
        <th scope="row"><?php if( do_reg_check( 'check' ) ) submit_button( 'Add Venue' ); ?></th>
        <td colspan="3" align="left"><a class="button-secondary" href="<?php echo $_SERVER['HTTP_REFERER']; ?>" title="<?php _e( 'Go Back' ); ?>"><?php _e( 'Cancel' ); ?></a></td>
        </tr> 
        </table>
        </form>
        </div>
        <?php
		
	} // f_mdjm_add_venue_form

/**
 * Display a form for editing a new venue
 * 
 *
 * @since 1.0
*/
	function f_mdjm_view_venue_form( $venue )	{
		$venueinfo = f_mdjm_get_venue_by_id( $venue['venue_id'] );
		?>
		<div class="wrap">
        <h2>Add Venue</h2>
        <form method="post" action="<?php f_mdjm_admin_page( 'venues' ); ?>">
		<input type="hidden" name="action" value="edit_venue" />
        <input type="hidden" name="venue_id" value="<?php echo $venue['venue_id']; ?>" />
       	<?php wp_nonce_field( 'mdjm_edit_venue_verify' ); ?>
        <table class="form-table">
        <tr>
        <th scope="row"><label for="venue_name">Venue Name:</label></th>
        <td>
        <input type="text" name="venue_name" id="venue_name" class="regular-text" value="<?php echo esc_attr( $venueinfo->venue_name ); ?>" /></td>
        <th scope="row"><label for="venue_contact">Contact Name:</label></th>
        <td><input type="text" name="venue_contact" id="venue_contact" class="regular-text" value="<?php echo esc_attr( $venueinfo->venue_contact ); ?>" /></td>
        </tr>
        <tr>
        <th scope="row"><label for="venue_phone">Contact Phone:</label></th>
        <td><input type="tel" name="venue_phone" id="venue_phonee" class="regular-text" value="<?php echo esc_attr( $venueinfo->venue_phone ); ?>" /></td>
        <th scope="row"><label for="venue_email">Contact Email:</label></th>
        <td><input type="email" name="venue_email" id="venue_email" class="regular-text" value="<?php echo esc_attr( $venueinfo->venue_email ); ?>" /></td>
        </tr>
		<tr>
        <th scope="row"><label for="venue_address1">Address Line 1:</label></th>
        <td><input type="text" name="venue_address1" id="venue_address1" class="regular-text" value="<?php echo esc_attr( $venueinfo->venue_address1 ); ?>" /></td>
        <th scope="row"><label for="venue_address2">Address Line 2:</label></th>
        <td><input type="text" name="venue_address2" id="venue_address2" class="regular-text" value="<?php echo esc_attr( $venueinfo->venue_address2 ); ?>" /></td>
        </tr>
        <tr>
        <th scope="row"><label for="venue_town">Town:</label></th>
        <td><input type="text" name="venue_town" id="venue_town" class="regular-text" value="<?php echo esc_attr( $venueinfo->venue_town ); ?>" /></td>
        <th scope="row"><label for="venue_county">County:</label></th>
        <td><input type="text" name="venue_county" id="venue_county" class="regular-text" value="<?php echo esc_attr( $venueinfo->venue_county ); ?>" /></td>
        </tr>
		<tr>
        <th scope="row"><label for="venue_postcode">Post Code:</label></th>
        <td colspan="3"><input type="text" name="venue_postcode" id="venue_postcode" class="regular-text" value="<?php echo esc_attr( $venueinfo->venue_postcode ); ?>" /></td>
        </tr>
		<tr>
        <th scope="row"><label for="venue_information">Information:</label></th>
        <td colspan="3"><textarea name="venue_information" id="venue_information" cols="80" rows="10"><?php echo esc_attr( $venueinfo->venue_information ); ?></textarea></td>
        </tr>
        <tr>
        <th scope="row">
		<?php 
		if( do_reg_check( 'check' ) )	{
			if( current_user_can( 'administrator' ) || dj_can( 'add_venue' ) )	{
				submit_button( 'Edit Venue' );
			}
		}
		?>
        </th>
        <td colspan="3" align="left"><a class="button-secondary" href="<?php echo $_SERVER['HTTP_REFERER']; ?>" title="<?php _e( 'Go Back' ); ?>"><?php _e( 'Cancel' ); ?></a></td>
        </tr> 
        </table>
        </form>
        </div>
        <?php
		
	} // f_mdjm_view_venue_form

/**
 * Process actions submitted via $_GET or show the main venues page
 * 
 *
 * @since 1.0
*/
	if( isset( $_GET['action'] ) )	{ // Action to process
		$func = 'f_mdjm_' . $_GET['action'];
		if( function_exists( $func ) ) $func( $_GET['venue_id'] );
	}
	
	else	{ // Display the Events table
		f_mdjm_render_venues_table();
	}

?>