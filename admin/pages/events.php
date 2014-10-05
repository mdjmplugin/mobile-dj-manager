<?php
	defined('ABSPATH') or die("Direct access to this page is disabled!!!");
	if ( !current_user_can( 'manage_options' ) && !current_user_can( 'manage_mdjm' ) )  {
		wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
	}

/**
 * * * * * * * * * * * * * * * MDJM * * * * * * * * * * * * * * *
 * events.php
 *
 * Displays table of events & enables adding new / editing existing
 *
 * Calls: class-mdjm-event-table.php
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
	if( isset ( $_POST['action'] ) )	{
		$func = 'f_mdjm_' . $_POST['action'];
		if( function_exists( $func ) ) $func( $_POST );
	}

/**
 * Display the events within the Admin UI
 * 
 * Calls: class-wp-list-table.php; class-mdjm-event-table.php
 *
 * @since 1.0
*/
	function f_mdjm_render_events_table()	{
		if( !class_exists( 'WP_List_Table' ) ){
			require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
		}
	
		if( ! class_exists( 'MDJM_Events_Table' ) ) {
			require_once( WPMDJM_PLUGIN_DIR . '/admin/includes/class-mdjm-event-table.php' );
		}
		$events_table = new MDJM_Events_Table();
		?>
		</pre><div class="wrap"><h2>Events <?php if( current_user_can( 'administrator' ) || dj_can( 'add_event' ) )	echo '<a href="' . admin_url() . 'admin.php?page=mdjm-events&action=add_event_form" class="add-new-h2">Add New</a></h2>';
		
		$events_table->prepare_items();
		?>
		<form method="post" name="mdjm_event" id="mdjm_event">
		<input type="hidden" name="page" value="mdjm-events">
		<?php
		$events_table->search_box( 'Search Events', 'search_id' );
		
		$events_table->display(); 
		?>
        </form></div>
        <?php 
	} // f_mdjm_render_events_table

/**
 * Display playlist entries for event within the Admin UI
 * 
 * Calls: class-wp-list-table.php; class-wp-list-table.php
 *
 * @since 1.0
*/	
	function f_mdjm_render_playlist_table()	{
		if( !class_exists( 'WP_List_Table' ) ){
			require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
		}
	
		if( ! class_exists( 'MDJM_PlayList_Table' ) ) {
			require_once( WPMDJM_PLUGIN_DIR . '/admin/includes/class-mdjm-playlist-table.php' );
		}
		
		$playlist_table = new MDJM_PlayList_Table();
		?>
		</pre><div class="wrap"><h2>Event Playlist</h2>
		<?php
		$playlist_table->prepare_items();
		?>
		<form method="post" name="mdjm_playlist" id="mdjm_playlist">
		<input type="hidden" name="page" value="mdjm-playlist">
		<?php
		$playlist_table->display(); 
		?>
        </form></div>
        <?php 
	} // f_mdjm_render_playlist_table
	
/**
 * Display a form for adding new events
 * 
 *
 * @since 1.0
*/
	function f_mdjm_add_event_form()	{
		if( !current_user_can( 'administrator' ) && !dj_can( 'add_event' ) )	wp_die( 'You do not have permissions to perform this action. Contact your <a href="mailto:' . get_bloginfo( 'admin_email' ) . '">administrator</a> for assistance.' );
		
		wp_enqueue_script('jquery-ui-datepicker');
		wp_enqueue_style('jquery-ui-css', 'http://ajax.googleapis.com/ajax/libs/jqueryui/1.8.2/themes/smoothness/jquery-ui.css');
		?>
		<script type="text/javascript">
		jQuery(document).ready(function($) {
			$('.custom_date').datepicker({
			dateFormat : 'dd/mm/yy'
			});
        });
        </script>
        <div class="wrap">
        <h2>Create New Event</h2>
        <p>Enter the event details into the fields below to create a new event enquiry.</p>
        <form method="post" action="<?php echo admin_url() . 'admin.php?page=mdjm-events&display=enquiries'; ?>">
        <input type="hidden" name="action" value="add_event" />
        <?php wp_nonce_field( 'mdjm_add_event_verify' ); ?>
        <table class="form-table">
         <tr>
        <th scope="row"><label for="enquiry_source">Source of Enquiry</label></th>
        <td colspan="3"><select name="enquiry_source" id="enquiry_source">
        	<option value="" <?php if( empty( $_POST['client'] ) ) echo 'selected'; ?>>--- Select ---</option>
            <option value="Website" <?php if( $_POST['enquiry_source'] == 'Website' ) echo 'selected'; ?>>Website</option>
            <option value="AMP DJ" <?php if( $_POST['enquiry_source'] == 'AMP DJ' ) echo 'selected'; ?>>AMP DJ</option>
            <option value="Facebook" <?php if( $_POST['enquiry_source'] == 'Facebook' ) echo 'selected'; ?>>Facebook</option>
            <option value="Direct Email" <?php if( $_POST['enquiry_source'] == 'Direct Email' ) echo 'selected'; ?>>Email</option>
            <option value="Telephone" <?php if( $_POST['enquiry_source'] == 'Telephone' ) echo 'selected'; ?>>Telephone</option>
            <option value="Other" <?php if( $_POST['enquiry_source'] == 'Other' ) echo 'selected'; ?>>Other</option>
        	</select>
        </td>
        </tr>
        <tr>
        <th scope="row"><label for="email_enquiry">Email Client Enquiry?</label></th>
        <td colspan="3"><input type="checkbox" id="email_enquiry" name="email_enquiry" value="Y" /> <p class="description">Select this option to have a quote emailed to the client once the enquiry is loaded</p></td>
        </tr>
        </table>
        <hr />
        <table class="form-table">
        <tr>
        <th scope="row"><label for="user_id">Client:</label></th>
        <td><select name="user_id" id="user_id">
        	<option value="">--- Select Client ---</option>
        <?php
		$user_args = array(
						'role' => 'client',
						'orderby' => 'display_name',
						'order' => 'ASC',
						);
		$client_list = get_users( $user_args );
        foreach( $client_list as $client )	{
			?>
			<option value="<?php echo $client->ID; ?>" <?php selected( $_POST['client'], $client->ID ); ?>><?php echo $client->display_name; ?></option>';	
            <?php
		}
		?></select> <?php if( current_user_can( 'administrator' ) || dj_can( 'add_client' ) )	echo '<a href="' . admin_url() . 'user-new.php" class="add-new-h2">Add New</a>'; ?></td>
        <th scope="row"><label for="event_date">Event Date:</label></th>
        <td><input type="text" class="custom_date" name="event_date" value="<?php echo $_POST['event_date']; ?>" /></td>
        </tr>
        <tr>
        <th scope="row"><label for="event_dj">DJ:</label></th>
        <?php 
		if( current_user_can( 'administrator' ) )	{
			$djs = f_mdjm_get_djs(); ?>
			<td><select name="event_dj">
				<option value="" <?php if( empty( $_POST['event_dj'] ) ) echo ' selected'; ?>>--- Select a DJ ---</option>
				<?php
				foreach( $djs as $dj )	{
					?>
					<option value="<?php echo $dj->ID; ?>" <?php if( $_POST['event_dj'] == $dj->ID ) echo ' selected'; ?>><?php echo $dj->display_name; ?></option>
					<?php
				}
				?>
				</select>
                <?php
		}
		else	{
			$dj = wp_get_current_user();
			?>
            <td><?php echo $dj->display_name; ?></td>
            <?php 	
		}
		?>
        </td>
        <th scope="row"><label for="event_type">Event Type:</label></th>
        <td><select name="event_type" id="event_type">
        	<?php
				$raw_events = get_option( WPMDJM_SETTINGS_KEY );
				$events = explode( "\n", $raw_events['event_types'] );
				foreach( $events as $event )	{
					?>
					<option value="<?php echo str_replace( "\r\n", "", $event ); ?>" <?php selected( $_POST['event_type'], str_replace( "\r\n", "", $event ) ); ?>><?php echo str_replace( "\r\n", "", $event ); ?></option>
					<?php	
				}
			?>
            </select>
		</td>
        </tr>
        <tr>
        <th scope="row"><label for="event_start">Start Time:</label></th>
        <td><input type="text" id="event_start" class="regular-text" name="event_start" placeholder="19:00" value="<?php echo $_POST['event_start']; ?>" /></td>
        <th scope="row"><label for="event_finish">End Time:</label></th>
        <td><input type="text" name="event_finish" id="event_finish" class="regular-text" placeholder="00:00" value="<?php echo $_POST['event_finish']; ?>"></td>
        </tr>
        <tr>
        <th scope="row"><label for="event_description">Description:</label></th>
        <td colspan="3"><textarea cols="100" rows="4" id="event_description" name="event_description"><?php echo $_POST['event_description']; ?></textarea></td>
        </tr>
        <tr>
        <th scope="row"><label for="cost">Total Cost: &pound;</label></th>
        <td><input type="text" id="cost" class="regular-text" name="cost" value="<?php echo $_POST['cost']; ?>" /></td>
        <th scope="row"><label for="despoit">Deposit Amount: &pound;</label></th>
        <td><input type="text" name="deposit" id="deposit" class="regular-text" value="<?php echo $_POST['deposit']; ?>"></td>
        </tr>
        <tr>
        <th scope="row"><label for="deposit_status">Deposit Paid?</label></th>
        <td><input type="checkbox" id="deposit_status" name="deposit_status" value="Paid" <?php if( $_POST['deposit_status'] == 'Paid' ) echo 'checked'; ?> /></td>
        <th scope="row"><label for="contract_id">Contract:</label></th>
        <td><select name="contract" id="contract">
        <?php
		$contract_query = new WP_Query( array( 'post_type' => 'contract' ) );
		if ( $contract_query->have_posts() ) {
			while ( $contract_query->have_posts() ) {
				$contract_query->the_post();
				echo '<option value="' . get_the_id() . '">' . get_the_title() . '</option>';	
			}
		}
		wp_reset_postdata();
		?>
        </select>
        </td>
        </tr>
        </table>
        <hr />
        <h3>Venue Details</h3>
        <table class="form-table">
        <tr>
        <th scope="row"><label for="venue">Venue:</label></th>
        <td><input type="text" id="venue" class="regular-text" name="venue" value="<?php echo $_POST['venue']; ?>" /></td>
        <th scope="row"><label for="venue_contact">Venue Contact:</label></th>
        <td><input type="text" name="venue_contact" id="venue_contact" class="regular-text" value="<?php echo $_POST['venue_contact']; ?>"></td>
        </tr>
        <tr>
        <th scope="row"><label for="venue_addr1">Venue Address Line 1:</label></th>
        <td><input type="text" id="venue_addr1" class="regular-text" name="venue_addr1" value="<?php echo $_POST['venue_addr1']; ?>" /></td>
        <th scope="row"><label for="venue_phone">Venue Phone:</label></th>
        <td><input type="text" id="venue_phone" class="regular-text" name="venue_phone" value="<?php echo $_POST['venue_phone']; ?>" /></td>
        </tr>
        <tr>
        <th scope="row"><label for="venue_addr2">Venue Address Line 2:</label></th>
        <td><input type="text" id="venue_addr2" class="regular-text" name="venue_addr2" value="<?php echo $_POST['venue_addr2']; ?>" /></td>
        <th scope="row"><label for="venue_email">Venue Email:</label></th>
        <td colspan="3"><input type="text" id="venue_email" class="regular-text" name="venue_email" value="<?php echo $_POST['venue_email']; ?>" /></td>
        </tr>
        <tr>
        <th scope="row"><label for="venue_city">Venue Town/City:</label></th>
        <td colspan="3"><input type="text" id="venue_city" class="regular-text" name="venue_city" value="<?php echo $_POST['venue_city']; ?>" /></td>
        </tr>
        <tr>
        <th scope="row"><label for="venue_state">Venue County:</label></th>
        <td colspan="3"><input type="text" id="venue_state" class="regular-text" name="venue_state" value="<?php echo $_POST['venue_state']; ?>" /></td>
        </tr>
        <tr>
        <th scope="row"><label for="venue_zip">Venue Post Code:</label></th>
        <td colspan="3"><input type="text" id="venue_zip" class="regular-text" name="venue_zip" value="<?php echo $_POST['venue_zip']; ?>" /></td>
        </tr>
        </table>
        <?php
		if( do_reg_check( 'check' ) ) submit_button( 'Add Event' ); ?>
        </form>
        </div>
        <?php
	} // f_mdjm_add_event
	
/**
 * Display a form for editing events
 *
 * @param $event_id
 *
 * @since 1.0
*/
	
	function f_mdjm_view_event_form( $event_id )	{
		$eventinfo = f_mdjm_get_eventinfo_by_id( $event_id );
		if( !current_user_can( 'manage_options' ) && $eventinfo->event_dj != get_current_user_id() ) 
			wp_die( 'You cannot edit an event that is not yours unless you are an Administrator! <a href="' . admin_url() . 'admin.php?page=mdjm-events">Click here to return to your Events List</a>' );
		wp_enqueue_script('jquery-ui-datepicker');
		wp_enqueue_style('jquery-ui-css', 'http://ajax.googleapis.com/ajax/libs/jqueryui/1.8.2/themes/smoothness/jquery-ui.css');
		?>
		<script type="text/javascript">
		jQuery(document).ready(function($) {
			$('.custom_date').datepicker({
			dateFormat : 'dd/mm/yy'
			});
        });
        </script>
		<div class="wrap">
        <h2>Edit Event</h2>
        <form name="mdjm-edit-event" id="mdjm-edit-event" method="post"  action="<?php echo admin_url() . 'admin.php?page=mdjm-events'; ?>">
        <input type="hidden" name="action" value="edit_event" />
        <input type="hidden" name="event_id" value="<?php echo $eventinfo->event_id; ?>" />
        <?php wp_nonce_field( 'mdjm_edit_event_verify' ); ?>
        <table class="form-table">
        <tr>
        <th scope="row"><label for="user_id">Client:</label></th>
        <td>
        <?php 
		if( current_user_can( 'administrator' ) || dj_can( 'add_client' ) )	{
			?>
        	<select name="user_id" id="user_id">
            <option value="" <?php selected( $eventinfo->user_id, '' ); ?>>--- Select Client ---</option>
        	<?php
			$user_args = array(
							'role' => 'client',
							'orderby' => 'display_name',
							'order' => 'ASC',
							);
			$client_list = get_users( $user_args );
			foreach( $client_list as $client )	{
				?>
				<option value="<?php echo $client->ID; ?>" <?php selected( $eventinfo->user_id, $client->ID ); ?>><?php echo $client->display_name; ?></option>
				<?php	
			}
			?></select>
            <?php
		}
		else	{
			$client = get_userdata( $eventinfo->user_id );
			?>
            <?php echo $client->display_name; ?>
            <?php
		}
		?>
		</td>
        <th scope="row"><label for="event_date">Event Date:</label></th>
        <td><input type="text" class="custom_date" name="event_date" id="event_date" value="<?php echo date( 'd/m/Y', strtotime( $eventinfo->event_date ) ); ?>" /></td>
        </tr>
        <tr>
        <th scope="row"><label for="event_dj">DJ:</label></th>
        <?php 
		if( current_user_can( 'administrator' ) )	{
			$djs = f_mdjm_get_djs(); ?>
			<td><select name="event_dj">
				<option value="" <?php if( empty( $_POST['event_dj'] ) ) echo ' selected'; ?>>--- Select a DJ ---</option>
				<?php
				foreach( $djs as $dj )	{
					?>
					<option value="<?php echo $dj->ID; ?>" <?php selected( $eventinfo->event_dj, $dj->ID ); ?>><?php echo $dj->display_name; ?></option>
					<?php
				}
				?>
				</select></td>
                <?php
		}
		else	{
			$dj = wp_get_current_user();
			?>
            <td><?php echo $dj->display_name; ?></td>
            <?php 	
		}
		?>
        <th scope="row"><label for="event_type">Event Type:</label></th>
        <td><select name="event_type" id="event_type">
        	
        	<?php
				$raw_events = get_option( WPMDJM_SETTINGS_KEY );
				$events = explode( "\r\n", $raw_events['event_types'] );
				foreach( $events as $event )	{
					?>
					<option value="<?php echo $event; ?>" <?php selected( str_replace( "\r\n", "", $eventinfo->event_type ), $event ); ?>><?php echo $event; ?></option>
					<?php	
				}
			?>
            </select>
		</td>
        </tr>
        <tr>
        <th scope="row"><label for="event_start">Start Time:</label></th>
        <td><input type="text" id="event_start" class="regular-text" name="event_start" value="<?php echo $eventinfo->event_start; ?>" /></td>
        <th scope="row"><label for="event_finish">End Time:</label></th>
        <td><input type="text" name="event_finish" id="event_finish" class="regular-text" value="<?php echo $eventinfo->event_finish; ?>"></td>
        </tr>
        <tr>
        <th scope="row"><label for="event_description">Description:</label></th>
        <td colspan="3"><textarea cols="100" rows="4" id="event_description" name="event_description"><?php echo stripslashes( $eventinfo->event_description ); ?></textarea></td>
        </tr>
        <?php
		if( current_user_can( 'administrator' ) || dj_can( 'see_deposit' ) )	{
		?>
            <tr>
            <th scope="row"><label for="cost">Total Cost: &pound;</label></th>
            <td><input type="text" id="cost" class="regular-text" name="cost" value="<?php echo $eventinfo->cost; ?>" /></td>
            <th scope="row"><label for="despoit">Deposit Amount: &pound;</label></th>
            <td><input type="text" name="deposit" id="deposit" class="regular-text" value="<?php echo $eventinfo->deposit; ?>"></td>
            </tr>
            <tr>
            <th scope="row"><label for="deposit_status">Deposit Paid?</label></th>
            <td><input type="checkbox" id="deposit_status" name="deposit_status" value="Paid" <?php if( $eventinfo->deposit_status == 'Paid' ) echo ' checked'; ?> /></td>
             <th scope="row"><label for="contract_id">Contract:</label></th>
        <td><select name="contract" id="contract">
        <?php
		$contract_query = new WP_Query( array( 'post_type' => 'contract' ) );
		if ( $contract_query->have_posts() ) {
			while ( $contract_query->have_posts() ) {
				$contract_query->the_post();
				$contract_id = get_the_id();
				?>
				<option value="<?php echo get_the_id(); ?>"<?php selected( $eventinfo->contract, $contract_id ); ?>><?php echo get_the_title(); ?></option>
                <?php
			}
		}
		wp_reset_postdata();
		?>
        </select>
        </td>
        </tr>
        <?php
		}
		else	{
			?>
			<tr>
            <th scope="row">Balance Due:</th>
            <td colspan="3">&pound; <?php echo $eventinfo->cost - $eventinfo->deposit; ?></td>
            </tr>	
            <?php
		}
		?>
        </table>
        <hr />
        <h3>Venue Details</h3>
        <table class="form-table">
        <tr>
        <th scope="row"><label for="venue">Venue:</label></th>
        <td><input type="text" id="venue" class="regular-text" name="venue" value="<?php echo $eventinfo->venue; ?>" /></td>
        <th scope="row"><label for="venue_contact">Venue Contact:</label></th>
        <td><input type="text" name="venue_contact" id="venue_contact" class="regular-text" value="<?php echo $eventinfo->venue_contact; ?>"></td>
        </tr>
        <tr>
        <th scope="row"><label for="venue_addr1">Venue Address Line 1:</label></th>
        <td><input type="text" id="venue_addr1" class="regular-text" name="venue_addr1" value="<?php echo $eventinfo->venue_addr1; ?>" /></td>
        <th scope="row"><label for="venue_phone">Venue Phone:</label></th>
        <td><input type="text" id="venue_phone" class="regular-text" name="venue_phone" value="<?php echo $eventinfo->venue_phone; ?>" /></td>
        </tr>
        <tr>
        <th scope="row"><label for="venue_addr2">Venue Address Line 2:</label></th>
        <td><input type="text" id="venue_addr2" class="regular-text" name="venue_addr2" value="<?php echo $eventinfo->venue_addr2; ?>" /></td>
        <th scope="row"><label for="venue_email">Venue Email:</label></th>
        <td colspan="3"><input type="text" id="venue_email" class="regular-text" name="venue_email" value="<?php echo $eventinfo->venue_email; ?>" /></td>
        </tr>
        <tr>
        <th scope="row"><label for="venue_city">Venue Town/City:</label></th>
        <td colspan="3"><input type="text" id="venue_city" class="regular-text" name="venue_city" value="<?php echo $eventinfo->venue_city; ?>" /></td>
        </tr>
        <tr>
        <th scope="row"><label for="venue_state">Venue County:</label></th>
        <td colspan="3"><input type="text" id="venue_state" class="regular-text" name="venue_state" value="<?php echo $eventinfo->venue_state; ?>" /></td>
        </tr>
        <tr>
        <th scope="row"><label for="venue_zip">Venue Post Code:</label></th>
        <td colspan="3"><input type="text" id="venue_zip" class="regular-text" name="venue_zip" value="<?php echo $eventinfo->venue_zip; ?>" /></td>
        </tr>
        </table>
        <hr />
        <h3>Contract Details</h3>
        <table class="form-table">
        <tr>
        <th scope="row"><label for="contract_status">Contract Status:</label></th>
        <td><select id="contract_status" name="contract_status">
        		<option value="Enquiry"<?php selected( $eventinfo->contract_status, 'Enquiry' ); ?>>Enquiry</option>
        		<option value="Pending"<?php selected( $eventinfo->contract_status, 'Pending' ); ?>>Pending</option>
                <option value="Approved"<?php selected( $eventinfo->contract_status, 'Approved' ); ?>>Approved</option>
        	</select>
        </td>
        <th scope="row"><label for="contract_approved_date">Approved Date:</label></th>
        <td><input type="text" class="custom_date" name="contract_approved_date" id="contract_approved_date" value="<?php if(! empty( $eventinfo->contract_approved_date ) ) echo date( 'd/m/Y', strtotime( $eventinfo->contract_approved_date ) ); ?>"></td>
        </tr>
        </table>
        <?php
        	if ( date( 'Y-m-d' ) < date( 'Y-m-d', strtotime( $eventinfo->event_date ) ) )	{
				submit_button( 'Edit Event' );
			}
		?>
        </form>
        </div>
        <?php
	} // f_mdjm_view_event_form

/**
 * Show printable playlist
 * 
 *
 * @since 1.0
*/
	function f_mdjm_print_playlist( $event )	{
		global $wpdb;
		$query = 'SELECT * FROM `'.$db_tbl['playlists'].'` WHERE `event_id` = ' . $event . ' ORDER BY `artist` ASC';
		$playlistinfo = $wpdb->get_results( $query );
		?>
        <table border="0" cellpadding="0" cellspacing="0">
        <tr>
            <th>Artist</th>
            <th>Song</th>
            <th>When to Play</th>
            <th>Info</th>
            <th>Added By</th>
       </tr>
        <?php
		foreach( $playlistinfo as $playlist )	{
			?>
            <tr>
            <td><?php echo $playlist->artist; ?></td>
            <td><?php echo $playlist->song; ?></td>
            <td><?php echo $playlist->when; ?></td>
            <td><?php echo $playlist->info; ?></td>
            <td><?php echo $playlist->added_by; ?></td>
            </tr>
            <?php	
		}
        ?>
        </table>
        <?php
	}
	
	function f_mdjm_show_journal()	{
		include( WPMDJM_PLUGIN_DIR . '/admin/pages/show-journal.php' );
		exit;
	}
	
/**
 * Process actions submitted via $_GET or show the main events page
 * 
 *
 * @since 1.0
*/
	if( $_GET['action'] == 'show_journal' )	{
		$func = 'f_mdjm_' . $_GET['action'];
		$func();
		exit;
	}
	if( isset ( $_GET['action'] ) )	{ // Action to process
		$func = 'f_mdjm_' . $_GET['action'];
		if( function_exists( $func ) ) $func( $_GET['event_id'] );
	}
	
	else	{ // Display the Events table
		f_mdjm_render_events_table();
	}
	
?> 