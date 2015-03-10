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

	// If recently updated, display the release notes
	f_mdjm_has_updated();

/**
 * Check for any form submissions that take place outside the 
 * Bulk Actions and process
 *
 * @param $_POST
 *
 * @since 1.0
*/
	if( isset( $_POST['update_packages' ] ) )	{
		if( isset( $_POST['update_packages'] ) && $_POST['update_packages'] == 'Update Package' )	{
			$eventinfo = f_mdjm_get_eventinfo_by_id( $_POST['event_id'] );
			if( $_POST['event_package'] != $eventinfo->event_package )	{ // Update the package
				// Get new package details
				$packages = get_option( 'mdjm_packages' );
				
				// Remove existing package cost
				$event_cost = $eventinfo->cost - $packages[$eventinfo->event_package]['cost'];
				
				// Remove cost of addons
				$event_addons = explode( ',', $eventinfo->event_addons );
				$equipment = get_option( 'mdjm_equipment' );
				foreach( $event_addons as $addon )	{
					$event_cost = $event_cost - $equipment[$addon][7];
				}
				
				// Add new package cost
				if( $_POST['event_package'] != '0' )	{
					$event_cost = $event_cost + $packages[$_POST['event_package']]['cost'];
				}
				
				// Update event
				f_mdjm_update_event_package( $_POST['event_id'], $event_cost, $_POST['event_package'], $eventinfo->user_id );
				wp_redirect( wp_get_referer() . '&updated=2' );
				exit;
			}
		}
		if( isset( $_POST['update_packages' ] ) && $_POST['update_packages' ] == 'Update Add-Ons' )	{
			$eventinfo = f_mdjm_get_eventinfo_by_id( $_POST['event_id'] );
			// Remove old addon costs from event
			$c_addons = explode( ',', $eventinfo->event_addons );
			$equipment = get_option( 'mdjm_equipment' );
			foreach( $c_addons as $addon )	{
				$event_cost = $eventinfo->cost - $equipment[$addon][7];
			}
			// Calculate cost of new addons
			if( !is_array( $_POST['event_addons'] ) ) $_POST['event_addons'] = array( $_POST['event_addons'] );
			foreach( $_POST['event_addons'] as $addons )	{
				$event_cost = $event_cost + $equipment[$addons][7];	
			}
			$new_addons = implode( ',', $_POST['event_addons'] );
			f_mdjm_update_event_addons( $_POST['event_id'], $event_cost, $new_addons, $eventinfo->user_id );
			wp_redirect( wp_get_referer() . '&updated=3' );
			exit;
		}
	}
	
	if( isset( $_POST['submit'] ) )	{
		if( $_POST['submit'] == 'Next' )	{
			f_mdjm_add_event_form();
		}
		else	{
			if( $_POST['action'] == 'respond_event' )	{
				$_POST['action'] = 'add_event';
			}
			
			$func = 'f_mdjm_' . $_POST['action'];
			if( function_exists( $func ) ) $func( $_POST );
		}
	}
	
	if( isset( $_GET['updated'] ) )	{
		if( $_GET['updated'] == 1 )	{
			$class = "updated";
			$message = "The selected events have been updated successfully.";
			f_mdjm_update_notice( $class, $message );
		}
		if( $_GET['updated'] == 2 )	{
			$class = "updated";
			$message = "The event package has been updated successfully.";
			f_mdjm_update_notice( $class, $message );
		}
		if( $_GET['updated'] == 3 )	{
			$class = "updated";
			$message = "The event add-ons have been updated successfully.";
			f_mdjm_update_notice( $class, $message );
		}
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
	
		if( !class_exists( 'MDJM_Events_Table' ) ) {
			require_once( WPMDJM_PLUGIN_DIR . '/admin/includes/class/class-mdjm-event-table.php' );
		}
		$events_table = new MDJM_Events_Table();
		
		/* Availability Check */
		if( isset( $_GET['availability'] ) && !empty( $_GET['availability'] ) )	{
			if( is_dj() )	{
				$dj_avail = f_mdjm_available( $_GET['availability'], get_current_user_id() );
			}
			else	{
				$dj_avail = f_mdjm_available( $_GET['availability'] );
			}
			
			/* Print the availability result */
			if( isset( $dj_avail ) )	{
				/* Check all DJ's */
				if ( $dj_avail !== false && current_user_can( 'administrator' ) )	{
					if( count( $dj_avail ) != 1 )	{
						$avail_message = count( $dj_avail ) . ' DJ\'s available on ' . date( 'l, jS F Y', strtotime( $_GET['availability'] ) );
					}
					else	{
						$avail_message = count( $dj_avail ) . ' DJ available on ' . date( 'l, jS F Y', strtotime( $_GET['availability'] ) );
					}
					$class = 'updated';
					?><ui><?php
					foreach( $dj_avail as $dj_detail )	{
						$dj = get_userdata( $dj_detail );
						$avail_message .= '<li>' . $dj->display_name . '<a href="' . admin_url( 'admin.php?page=mdjm-events&action=add_event_form&event_id=' . $_GET['e_id'] . '&dj=' . $dj->ID ) . '"> Assign &amp; Respond to Enquiry</a><br /></li>';
					}
					?></ui><?php
				}
				/* Single DJ Check */
				elseif ( $dj_avail !== false && !current_user_can( 'administrator' ) )	{
					$dj = get_userdata( get_current_user_id() );
					$class = 'updated';
					$avail_message = $dj->display_name . ' is available on ' . date( 'l, jS F Y', strtotime( $_GET['availability'] ) ) . '<a href="' . admin_url( 'admin.php?page=mdjm-events&action=add_event_form&event_id=' . $_GET['e_id'] . '&dj=' . $dj->ID ) . '"> Assign &amp; Respond to Enquiry</a><br />';
				}
				else	{
					$class = 'error';
					if( current_user_can( 'administrator' ) )	{
						$avail_message = 'No DJ\'s available on ' . date( 'l, jS F Y', strtotime( $_GET['availability'] ) );
					}
					else	{
						$dj = get_userdata( get_current_user_id() );
						$avail_message = $dj->display_name . ' is not available on ' . date( 'l, jS F Y', strtotime( $_GET['availability'] ) );
					}
				}
				f_mdjm_update_notice( $class, $avail_message );
			}
		}
		
		?>
		</pre><div class="wrap"><h2>Events <?php if( current_user_can( 'administrator' ) || dj_can( 'add_event' ) )	echo '<a href="' . admin_url() . 'admin.php?page=mdjm-events&action=add_event_form" class="add-new-h2">Add New</a></h2>';
		
		if( isset( $_POST['s'] ) )	{
			$events_table->prepare_items( $_POST['s'] );
		}
		else	{
			$events_table->prepare_items();
		}
		/*
		?>
		<form method="post" name="mdjm_event" id="mdjm_event">
		<input type="hidden" name="page" value="mdjm-events">
		<?php $events_table->search_box( 'Search Events', 'mdjm-events' ); ?>
        </form>
		
		<?php */$events_table->display(); ?>
        
        </div>
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
	
		if( !class_exists( 'MDJM_PlayList_Table' ) ) {
			require_once( WPMDJM_PLUGIN_DIR . '/admin/includes/class/class-mdjm-playlist-table.php' );
		}
		
		$playlist_table = new MDJM_PlayList_Table();
		
		// Email the playlist
		if( isset( $_POST['email_pl'] ) && $_POST['email_pl'] == 'Email me this List' )	{
			$playlist_table->send_to_email( $_POST, $_GET );	
		}
				
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
        </form>
        <a class="button-secondary" href="<?php echo $_SERVER['HTTP_REFERER']; ?>" title="<?php _e( 'Back' ); ?>"><?php _e( 'Back' ); ?></a>
        </div>
        <?php 
	} // f_mdjm_render_playlist_table
	
/**
 * Display a form for adding new events
 * 
 *
 * @since 1.0
*/
	function f_mdjm_add_event_form()	{
		global $mdjm_options;
		f_mdjm_add_event_header();
		if( !isset( $_POST['step'] ) || $_POST['step'] == 1 )	{
			$submit = 'Next';
			f_mdjm_add_event_step_1();
		}
		elseif( $_POST['step'] == 2 )	{
			if( isset( $mdjm_options['enable_packages'] ) && $mdjm_options['enable_packages'] == 'Y' )	{
				$submit = 'Next';
				f_mdjm_add_event_step_2();
			}
			else	{
				if( !isset( $_POST['quote_event_id'] ) )	{
					$submit = 'Create Enquiry';
				}
				else	{
					$submit = 'Respond to Enquiry';	
				}
				f_mdjm_add_event_review();
			}
		}
		elseif( $_POST['step'] == 3 && $mdjm_options['enable_packages'] == 'Y' )	{
			if( isset( $mdjm_options['enable_packages'] ) && $mdjm_options['enable_packages'] == 'Y' ) 	{
				$submit = 'Next';
				f_mdjm_add_event_step_3();
			}
			else	{
				if( !isset( $_POST['quote_event_id'] ) )	{
					$submit = 'Create Enquiry';
				}
				else	{
					$submit = 'Respond to Enquiry';	
				}
				f_mdjm_add_event_review();
			}
		}
		else	{
			if( !isset( $_POST['quote_event_id'] ) )	{
				$submit = 'Create Enquiry';
			}
			else	{
				$submit = 'Respond to Enquiry';	
			}
			f_mdjm_add_event_review();	
		}
		f_mdjm_add_event_footer( $submit );
	} // f_mdjm_add_event_form

	function f_mdjm_add_event_header()	{
		if( !current_user_can( 'administrator' ) && !dj_can( 'add_event' ) )	wp_die( 'You do not have permissions to perform this action. Contact your <a href="mailto:' . $mdjm_options['system_email'] . '">administrator</a> for assistance.' );
		wp_enqueue_script('jquery-ui-datepicker');
		wp_enqueue_style('jquery-ui-css', 'http://ajax.googleapis.com/ajax/libs/jqueryui/1.8.2/themes/smoothness/jquery-ui.css');
		?>
		<script type="text/javascript">
		jQuery(document).ready(function($) {
			$('.custom_date').datepicker({
			dateFormat : 'dd/mm/yy',
			firstDay: <?php echo get_option( 'start_of_week' ); ?>,
			changeYear: true,
			changeMonth: true
			});
        });
        </script>
        <div class="wrap">
        <h2>Create New Event</h2>
        <form method="post" name="add_event" action="<?php echo admin_url( 'admin.php?page=mdjm-events' ); ?>">
        <?php
		if( isset( $_POST ) )	{
			foreach( $_POST as $key => $value )	{
				if( $key != 'step' && $key != 'submit' && $key != '_wp_http_referer' && $key != '_wpnonce' )	{
					if( is_array( $value ) )	{
						$value = implode( ',', $value );
					}
						?><input type="hidden" name="<?php echo $key; ?>" value="<?php echo $value; ?>" /><?php
				}
			}
		}
		if( !isset( $_POST['submit'] ) || $_POST['submit'] == 'Next' )	{
			if( !isset( $_POST['step'] ) ) $step = 1;
			else( $step = $_POST['step'] )
			?><input type="hidden" name="step" value="<?php echo $step + 1; ?>" /><?php
		}
		else	{
			?><input type="hidden" name="action" value="add_event" /><?php
		}
		?>
        <?php wp_nonce_field( 'mdjm_add_event_verify' );
	} // f_mdjm_add_event_header
	
	function f_mdjm_add_event_step_1()	{
		global $mdjm_options;
		$have_venue = false;
		if( isset( $_GET['event_id'] ) )	{
			?><input type="hidden" name="quote_event_id" value="<?php echo $_GET['event_id']; ?>" /><?php
			
			$eventinfo = f_mdjm_get_eventinfo_by_id( $_GET['event_id'] );
			$_POST['client'] = $eventinfo->user_id;
			if( isset( $_GET['dj'] ) && !empty( $_GET['dj'] ) )	{
				$_POST['event_dj'] = $_GET['dj'];	
			}
			if( isset( $eventinfo->event_dj ) && !empty( $eventinfo->event_dj ) )	{
				$_POST['event_dj'] = $eventinfo->event_dj;
			}
			if( isset( $eventinfo->referrer ) && !empty( $eventinfo->referrer ) )	{
				$_POST['enquiry_source'] = $eventinfo->referrer;
			}
			if( isset( $eventinfo->event_date ) && !empty( $eventinfo->event_date ) )	{
				$_POST['event_date'] = date( 'd/m/Y', strtotime( $eventinfo->event_date ) );
			}
			if( isset( $eventinfo->event_start ) && !empty( $eventinfo->event_start ) )	{
				$_POST['event_start'] = date( 'H:i:s', strtotime( $eventinfo->event_start ) );
			}
			if( isset( $eventinfo->event_finish ) && !empty( $eventinfo->event_finish ) )	{
				$_POST['event_finish'] = date( 'H:i:s', strtotime( $eventinfo->event_finish ) );
			}
			if( isset( $eventinfo->event_type ) && !empty( $eventinfo->event_type ) )	{
				$_POST['event_type'] = $eventinfo->event_type;
			}
			if( isset( $eventinfo->event_description ) && !empty( $eventinfo->event_description ) )	{
				$_POST['event_description'] = $eventinfo->event_description;
			}
			if( isset( $eventinfo->venue ) && !empty( $eventinfo->venue ) )	{
				$_POST['venue'] = $eventinfo->venue;
				$have_venue = true;
			}
			if( isset( $eventinfo->venue_city ) && !empty( $eventinfo->venue_city ) )	{
				$_POST['venue_city'] = $eventinfo->venue_city;
				$have_venue = true;
			}
			if( isset( $eventinfo->venue_state ) && !empty( $eventinfo->venue_state ) )	{
				$_POST['venue_state'] = $eventinfo->venue_state;
				$have_venue = true;
			}
		}
		?>
        <h3>Client Details</h3>
        <hr />
        <table class="form-table">
        <tr>
        <th scope="row"><label for="user_id">Select Client:</label></th>
        <td><select name="user_id" id="user_id" onchange="displayClientFields();">
        	<option value="">--- Select Client ---</option>
            <?php
			if( !isset( $_GET['event_id'] ) )	{
				?>
            	<option value="add_new">--- Add New Client ---</option>
                <?php
			}
			?>
        <?php
		$client_list = f_mdjm_get_clients( 'client', 'display_name', 'ASC' );
        foreach( $client_list as $client )	{
			?>
			<option value="<?php echo $client->ID; ?>" <?php if( isset( $_POST['client'] ) ) selected( $_POST['client'], $client->ID ); ?>><?php echo $client->display_name; ?></option>';	
            <?php
		}
		?></select></td>
        </tr>
        </table>
        <style>
		#client_fields	{
			display:none;
		}
		</style>
  		<div id="client_fields">
        <script type="text/javascript">
		function displayClientFields() {
			var user = document.getElementById("user_id");
			var user_val = user.options[user.selectedIndex].value;
			var client_div =  document.getElementById("client_fields");
		
			  if (user_val == 'add_new') {
			   client_div.style.display = "block";
		
			  }
			  else {
			  client_div.style.display = "none";
			  }  
		} 
		</script>
        <table class="form-table">
        <tr>
        <th scope="row"><label for="client_first_name">First Name:</label></th>
        <td><input type="text" id="client_first_name" name="client_first_name" value="<?php echo $_POST['client_first_name']; ?>" /></td>
        <th scope="row"><label for="client_last_name">Last Name:&nbsp;<span class="description">(optional)</label></th>
        <td><input type="text" name="client_last_name" id="client_last_name" value="<?php echo $_POST['client_last_name']; ?>"></td>
        </tr>
        <tr>
        <th scope="row"><label for="client_email">Email:</label></th>
        <td><input type="text" id="client_email" name="client_email" value="<?php echo $_POST['client_email']; ?>" /></td>
        <th scope="row"><label for="client_phone">Phone:&nbsp;<span class="description">(optional)</span></label></th>
        <td><input type="text" name="client_phone" id="client_phone" value="<?php echo $_POST['client_last_name']; ?>"></td>
        </tr>
        </table>
        </div>
        <h3>Event Details</h3>
        <hr />
        <table class="form-table">
        <th scope="row"><label for="event_dj">Select DJ:</label></th>
        <?php 
		if( current_user_can( 'administrator' ) )	{
			$djs = f_mdjm_get_djs(); ?>
			<td colspan="3"><select name="event_dj">
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
        </tr>
        <tr>
        <th scope="row"><label for="contract">Contract:</label></th>
        <td><select name="contract" id="contract">
        <?php
		$contract_args = array(
							'post_type' => 'contract',
							'orderby' => 'name',
							'order' => 'ASC',
							);
		$contract_query = new WP_Query( $contract_args );
		if ( $contract_query->have_posts() ) {
			while ( $contract_query->have_posts() ) {
				$contract_query->the_post();
				echo '<option value="' . get_the_id() . '"';
				if( $mdjm_options['default_contract'] == get_the_id() )	{
					echo ' selected="selected"';
				}
				echo '>' . get_the_title() . '</option>' . "\n";
			}
		}
		wp_reset_postdata();
		?>
		</select>
        </td>
        <th scope="row"><label for="enquiry_source">Enquiry Source</label></th>
        <td><select name="enquiry_source" id="enquiry_source">
        	<option value="" <?php if( !isset( $_POST['enquiry_source'] ) || empty( $_POST['enquiry_source'] ) ) echo 'selected'; ?>>--- Select ---</option>
			<?php
			if( isset( $_POST['enquiry_source'] ) && !empty( $_POST['enquiry_source'] ) )	{
					?><option value="<?php echo $_POST['enquiry_source']; ?>" selected="selected"><?php echo $_POST['enquiry_source']; ?></option><?php	
				}
            $sources = explode( "\n", $mdjm_options['enquiry_sources'] );
			asort( $sources );
			foreach( $sources as $source )	{
				?>	
            <option value="<?php echo $source; ?>" <?php selected( $_POST['enquiry_source'], $source ); ?>><?php echo $source; ?></option>
            	<?php
			}
			?>
        	</select>
        </td>
        </tr>
        <tr>
        <th scope="row"><label for="event_date">Event Date:</label></th>
        <td><input type="text" class="custom_date" name="event_date" value="<?php echo $_POST['event_date']; ?>" /></td>
        <th scope="row"><label for="event_type">Event Type:</label></th>
        <td><select name="event_type" id="event_type">
        	<?php
				if( isset( $_POST['event_type'] ) && !empty( $_POST['event_type'] ) )	{
					?><option value="<?php echo $_POST['event_type']; ?>" selected="selected"><?php echo $_POST['event_type']; ?></option><?php	
				}
				$raw_events = get_option( WPMDJM_SETTINGS_KEY );
				$events = explode( "\n", $raw_events['event_types'] );
				asort( $events );
				foreach( $events as $event )	{
					?>
					<option value="<?php echo str_replace( "\r\n", "", $event ); ?>"<?php selected( $_POST['event_type'], str_replace( "\r\n", "", $event ) ); ?>><?php echo str_replace( "\r\n", "", $event ); ?></option>
					<?php	
				}
			?>
            </select>
		</td>
        </tr>
        <tr>
        <th scope="row"><label for="event_start_hr">Start Time:</label></th>
        <td>
        <select name="event_start_hr" id="event_start_hr">
        <?php
		$minutes = array( '00', '15', '30', '45' );
		if( $mdjm_options['time_format'] == 'H:i' )	{
			$i = '00';
			$x = '23';
			$comp = 'H';
		}
		else	{
			$i = '1';
			$x = '12';
			$comp = 'g';	
		}
		while( $i <= $x )	{
			?>
            <option value="<?php echo $i; ?>"<?php if( isset( $_POST['event_start'] ) ) { selected( date( $comp, strtotime( $eventinfo->event_start ) ), $i ); } ?>><?php echo $i; ?></option>
            <?php
			$i++;
		}
		?>
		</select>&nbsp;
        <select name="event_start_min" id="event_start_min">
        <?php
		foreach( $minutes as $minute )	{
			?>
            <option value="<?php echo $minute; ?>"<?php if( isset( $_POST['event_start'] ) ) { selected( date( 'i', strtotime( $eventinfo->event_start ) ), $minute ); } ?>><?php echo $minute; ?></option>
            <?php	
		}
		?>
        </select>
        <?php
		if( $mdjm_options['time_format'] != 'H:i' )	{
			?>
            &nbsp;<select name="event_start_period" id="event_start_period">
            <option value="AM"<?php if( isset( $_POST['event_start'] ) ) { selected( date( 'A', strtotime( $eventinfo->event_start ) ), 'AM' ); } ?>>AM</option>
            <option value="PM"<?php if( isset( $_POST['event_start'] ) ) { selected( date( 'A', strtotime( $eventinfo->event_start ) ), 'PM' ); } ?>>PM</option>
            </select>
            <?php	
		}
		?>
        </td>
        <th scope="row"><label for="event_finish">End Time:</label></th>
        <td>
        <select name="event_finish_hr" id="event_finish_hr">
        <?php
		$minutes = array( '00', '15', '30', '45' );
		if( $mdjm_options['time_format'] == 'H:i' )	{
			$i = '00';
			$x = '23';
		}
		else	{
			$i = '1';
			$x = '12';	
		}
		while( $i <= $x )	{
			?>
            <option value="<?php echo $i; ?>"<?php if( isset( $_POST['event_finish'] ) ) { selected( date( $comp, strtotime( $eventinfo->event_finish ) ), $i ); } ?>><?php echo $i; ?></option>
            <?php
			$i++;
		}
		?>
		</select>&nbsp;
        <select name="event_finish_min" id="event_finish_min">
        <?php
		foreach( $minutes as $minute )	{
			?>
            <option value="<?php echo $minute; ?>"<?php if( isset( $_POST['event_finish'] ) ) { selected( date( 'i', strtotime( $eventinfo->event_finish ) ), $minute ); } ?>><?php echo $minute; ?></option>
            <?php	
		}
		?>
        </select>
        <?php
		if( $mdjm_options['time_format'] != 'H:i' )	{
			?>
            &nbsp;<select name="event_finish_period" id="event_finish_period">
            <option value="AM"<?php if( isset( $_POST['event_finish'] ) ) { selected( date( 'A', strtotime( $eventinfo->event_finish ) ), 'AM' ); } ?>>AM</option>
            <option value="PM"<?php if( isset( $_POST['event_finish'] ) ) { selected( date( 'A', strtotime( $eventinfo->event_finish ) ), 'PM' ); } ?>>PM</option>
            </select>
            <?php	
		}
		?>
        </td>
        </tr>
        <tr>
        <th scope="row"><label for="event_cost">Cost:</label></th>
        <td colspan="3"><?php echo f_mdjm_currency(); ?><input type="text" name="event_cost" id="event_cost" class="small-text" value="<?php echo $_POST['event_cost']; ?>" /> <span class="description">No currency symbol needed. Package &amp; add-on costs (if enabled) will be added automatically</span></td>
        </tr>
        <tr>
        <th scope="row"><label for="event_description">Description:</label></th>
        <td colspan="3"><textarea cols="100" rows="4" id="event_description" name="event_description"><?php echo $_POST['event_description']; ?></textarea></td>
        </tr>
        <?php
		$venueinfo = f_mdjm_get_venueinfo();
		?>
        </table>
        <h3>Venue Details</h3>
        <hr />
        <table class="form-table">
        <tr>
        <th scope="row"><label for="event_venue">Event Venue</label></th>
        <td colspan="3"><select name="event_venue" id="event_venue" onChange="displayVenue();">
        <option value=""<?php if( empty( $_POST['event_venue'] ) ) echo ' selected="selected"'; ?>>--- Select Venue ---</option>
        <option value="manual"<?php if( $_POST['event_venue'] == 'manual' || !$venueinfo || $have_venue ) echo ' selected="selected"'; ?>>Enter Manually</option>
        <?php
		if( $venueinfo )	{
            foreach( $venueinfo as $venue )	{
				?>
				<option value="<?php echo $venue->venue_id; ?>" <?php selected( $venue->venue_id, $_POST['event_venue'] ); ?>><?php echo $venue->venue_name; ?></option>
				<?php
			}
		}
		?>
        </select> <span class="description">Select event venue, or enter details manually below</span>
        </td>
        </tr>
        </table>
        <style>
		#venue_fields	{
			<?php
			if( !$venueinfo || $have_venue )	{
				echo 'display:block;';	
			}
			else	{
				echo 'display:none;';	
			}
			?>
		}
		</style>
  		<div id="venue_fields">
        <script type="text/javascript">
		function displayVenue() {
			var event_venue  =  document.getElementById("event_venue");
			var event_venue_val = event_venue.options[event_venue.selectedIndex].value;
			var venue_div =  document.getElementById("venue_fields");
		
			  if (event_venue_val == 'manual') {
			   venue_div.style.display = "block";
		
			  }
			  else {
			  venue_div.style.display = "none";
			  }  
		} 
		</script>
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
        <tr>
        <th scope="row"><label for="save_venue">Save Venue?</label></th>
        <td colspan="3"><input type="checkbox" id="save_venue" name="save_venue" value="Y" /> <span class="description">Select this option to save the venue details to the database</span></td>
        </tr>
        </table>
        </div>
        <?php
	} // f_mdjm_add_event_step_1
	
	function f_mdjm_add_event_step_2()	{
		global $mdjm_options;
		
		/* Validation checks */
		f_mdjm_event_validate( $_POST );
		
		?>
        <table class="form-table">
		<?php
		if( isset( $mdjm_options['enable_packages'] ) && $mdjm_options['enable_packages'] == 'Y' )	{
			$packages = get_option( 'mdjm_packages' );
			if( $packages )	{
			asort( $packages );
			?>
				<tr>
				<th scope="row"><label for="event_package">Choose a Package to Add:</label></th>
				<td colspan="3"><select name="event_package" id="event_package">
				<option value="0">None</option>
				<?php
				foreach( $packages as $package )	{
					if( !empty( $_POST['event_dj'] ) )	{ /* DJ Selected so only offer their packages */
						$djs_with_package = explode( ',', $package['djs'] );
						foreach( $djs_with_package as $dj_with_package )	{
							if( $_POST['event_dj'] == $dj_with_package )	{
								echo '<option value="' . $package['slug'] . '">' . $package['name'] . '</option>';
							}
						}
					}
					else	{ /* No DJ assigned, offer all packages */
						echo '<option value="' . $package['slug'] . '">' . $package['name'] . '</option>';	
					}
				}
				?>
				</select>
				</td>
				</tr>
			<?php
			}
		}
		?>
        </table>
		<?php	
	} // f_mdjm_add_event_step_2
	
	function f_mdjm_add_event_step_3()	{
		global $mdjm_options;
		
		/* Validation checks */
		f_mdjm_event_validate( $_POST );
		
		?>
		<table class="form-table">
        <?php
		if( isset( $mdjm_options['enable_packages'] ) && $mdjm_options['enable_packages'] == 'Y' )	{
			$equipment = get_option( 'mdjm_equipment' );
			$packages = get_option( 'mdjm_packages' );
			/* Remove add on items included in selected package */
			$equipment_in_package = explode( ',', $packages[$_POST['event_package']]['equipment'] );
			foreach( $equipment_in_package as $equip_in_package )	{
				unset( $equipment[$equip_in_package] );
			}
			if( count( $equipment > 0 ) )	{
				$cats = get_option( 'mdjm_cats' );
				if( isset( $cats ) && !empty( $cats ) && is_array( $cats ) )	{
					asort( $cats );
				}
				?>
				<tr>
				<th scope="row" valign="top"><label for="event_addons">Select Add-ons (if required):</label></th>
				<td colspan="3" valign="top">
				<?php
				foreach( $cats as $cat_key => $cat_value )	{
					echo '<strong>' . $cat_value . '</strong>';
					echo '<br />';
					foreach( $equipment as $equip_list )	{
						if( $equip_list[5] == $cat_key )	{
							?><input type="checkbox" name="event_addons[]" id="event_addons[]" value="<?php echo $equip_list[1]; ?>" /><?php
							echo esc_attr( $equip_list[0] );
	
							if( esc_attr( $equip_list[2] ) > 1 )
								echo ' x ' . esc_attr( $equip_list[2] );
							echo '<br />';
						}
					}
					echo '<br />';
				}
				?>
				</td>
				</tr>
				<?php
			}
		}
		?>
        </table>
        <?php
	} // f_mdjm_add_event_step_3
	
	function f_mdjm_add_event_review()	{
		global $mdjm_options, $mdjm_client_text;
		
		/* Validation checks */
		f_mdjm_event_validate( $_POST );
		
		if( isset( $_POST['event_cost'] ) && !empty( $_POST['event_cost'] ) )	{
			$total_cost = $_POST['event_cost'];
		}
		else	{
			$total_cost = '0';
		}
		/* Add package costs */
		if( isset( $mdjm_options['enable_packages'] ) && $mdjm_options['enable_packages'] == 'Y' && !empty( $_POST['event_package'] ) )	{
			$packages = get_option( 'mdjm_packages' );
			asort( $packages );
			if( $packages )	{
				foreach( $packages as $package )	{
					if( $package['slug'] == $_POST['event_package'] )	{
						$total_cost = $total_cost + $package['cost'];	
					}
				}	
			}
		}
		/* Add costs of add-ons */
		if( isset( $mdjm_options['enable_packages'] ) && $mdjm_options['enable_packages'] == 'Y' && !empty( $_POST['event_addons'] ) )	{
			$equipment = get_option( 'mdjm_equipment' );
			if( !is_array( $_POST['event_addons'] ) ) $_POST['event_addons'] = array( $_POST['event_addons'] );
			foreach( $_POST['event_addons'] as $addon )	{
				foreach( $equipment as $equip_list )	{
					if( $addon == $equip_list[1] )	{
						$total_cost = $total_cost + $equip_list[7];	
					}
				}
			}
		}
		?>
        <script type="text/javascript">
		function showTemplateDiv(elem){
			if(elem.checked == 1)	{
				document.getElementById('enquiry_template_row').style.display = "block";
			}
			else	{
				document.getElementById('enquiry_template_row').style.display = "none";   
			}
		}
		</script>
        <p>Finally, review the cost information below and select whether or not to email the quote to your client and/or reset their password...</p>
        <?php
			if( !isset( $_POST['quote_event_id'] ) )	{
				?><input type="hidden" name="action" value="add_event" /><?php
			}
			else	{
				?><input type="hidden" name="action" value="respond_event" /><?php
			}
		?>
        <table class="form-table">
        <tr>
        <th scope="row" width="20%"><label for="total_cost">Total Event Cost:</label></th>
        <td colspan="3"><?php echo f_mdjm_currency(); ?><input type="text" name="total_cost" id="total_cost" value="<?php echo number_format( $total_cost, 2 ); ?>" /> <span class="description">Includes cost of packages and addons. Adjust if required.</span></td>
        </tr>
        <tr>
        <th scope="row" width="20%"><label for="deposit"><?php echo $mdjm_client_text['deposit_label']; ?>:</label></th>
        <td colspan="3"><?php echo f_mdjm_currency(); ?><input type="text" name="deposit" id="deposit" value="<?php if( isset( $_POST['deposit'] ) ) echo number_format( $_POST['deposit'] ); ?>" /> <span class="description">If you require a <?php echo $mdjm_client_text['deposit_label']; ?> to be paid upon booking, enter the amount here</span></td>
        </tr>
        <?php
		if( current_user_can( 'administrator' ) || dj_can( 'add_client' ) )	{
			?>
			<tr>
			<th scope="row"><label for="set_client_password">Reset Client Password?</label></th>
			<td colspan="3"><input type="checkbox" id="set_client_password" name="set_client_password" value="Y" /> <span class="description">Select this option to reset your client's password. You can use Shortcodes in your <a href="<?php echo admin_url( 'post.php?post=' . $mdjm_options['email_enquiry'] . '&action=edit' ); ?>">Email Template</a> to advise the client of their new password. Only processes if the <span class="code">Email Quote</span> check box is also selected</span> </td>
			</tr>
            <?php
		}
		?>
        <tr>
        <th scope="row"><label for="email_enquiry">Email Quote?</label></th>
        <td colspan="3"><input type="checkbox" id="email_enquiry" name="email_enquiry" value="Y" onclick="showTemplateDiv(this)" /> <span class="description">Select this option to email the quote to the client once created</span> </td>
        </tr>
        </table>
        <div id="enquiry_template_row" style="display: none;">
        <table class="form-table">
        <tr>
        <th scope="row"><label for="quote_email_template">Select email Template to Use:</label></th>
        <td colspan="3"><select name="quote_email_template" id="quote_email_template">
        <?php
        $email_args = array(
								'post_type' => 'email_template',
								'orderby' => 'name',
								'order' => 'ASC',
								);
		if( is_dj() )	{ // Check templates that DJ's cannot use
			if( !isset( $mdjm_permissions ) )	{
				$mdjm_permissions = get_option( 'mdjm_plugin_permissions' );
			}
			if( isset( $mdjm_permissions['dj_disable_template'] ) && !empty( $mdjm_permissions['dj_disable_template'] ) )	{
				if( !is_array( $mdjm_permissions['dj_disable_template'] ) )	{
					$mdjm_permissions['dj_disable_template'] = array( $mdjm_permissions['dj_disable_template'] );	
				}
				$email_args['post__not_in'] = $mdjm_permissions['dj_disable_template'];
			}	
		}
		$email_query = new WP_Query( $email_args );
			if ( $email_query->have_posts() ) {
				while ( $email_query->have_posts() ) {
					$email_query->the_post();
					?>
					<option value="<?php echo get_the_id(); ?>"<?php if( isset( $mdjm_options['email_enquiry'] ) ) { selected( get_the_id(), $mdjm_options['email_enquiry'] ); } ?>><?php echo get_the_title(); ?></option>
                    <?php
				}
			}
			wp_reset_postdata();
		?>
        </select>
        </td>
        </tr>
        </table>
        </div>
        <hr />
        <h3>Administration</h3>
        <table class="form-table">
        <tr>
        <th scope="row"><label for="dj_setup_hr">Setup Time:</label></th>
        <td>
        <select name="dj_setup_hr" id="dj_setup_hr">
        <?php
		$minutes = array( '00', '15', '30', '45' );
		if( $mdjm_options['time_format'] == 'H:i' )	{
			$i = '00';
			$x = '23';
			$comp = 'H';
		}
		else	{
			$i = '1';
			$x = '12';
			$comp = 'g';
		}
		while( $i <= $x )	{
			?>
            <option value="<?php echo $i; ?>"<?php selected( $i, $_POST['event_start_hr'] ); ?>><?php echo $i; ?></option>
            <?php
			$i++;
		}
		?>
		</select>&nbsp;
        <select name="dj_setup_min" id="dj_setup_min">
        <?php
		foreach( $minutes as $minute )	{
			?>
            <option value="<?php echo $minute; ?>"<?php selected( $minute, $_POST['event_start_min'] ); ?>><?php echo $minute; ?></option>
            <?php	
		}
		?>
        </select>
        <?php
		if( $mdjm_options['time_format'] != 'H:i' )	{
			?>
            &nbsp;<select name="dj_setup_period" id="dj_setup_period">
            <option value="AM"<?php selected( 'AM', $_POST['event_start_period'] ); ?>>AM</option>
            <option value="PM"<?php selected( 'AM', $_POST['event_start_period'] ); ?>>PM</option>
            </select>
            <?php	
		}
		?>
        &nbsp;<strong>Date: </strong><input type="text" class="custom_date" name="dj_setup_date" value="<?php echo $_POST['event_date']; ?>" />
        </td>
        </tr>
        <tr>
        <th scope="row"><label for="dj_notes">DJ Notes:</label></th>
        <td><textarea name="dj_notes" id="dj_notes" cols="60" rows="5"></textarea><br />
<span class="description">Notes entered here can be seen by the Event DJ and Admins only. Clients will not see this information</span></td>
        </tr>
        <?php
		if( current_user_can( 'administrator' ) )	{
			?>
			<tr>
			<th scope="row"><label for="admin_notes">Admin Notes:</label></th>
			<td><textarea name="admin_notes" id="admin_notes" cols="60" rows="5"></textarea><br />
	<span class="description">Notes entered here can be seen by Admins only. DJ's &amp; Clients will not see this information</span></td>
			</tr>
			<?php
		}
		?>
        </table>
        <?php
	}
	
	function f_mdjm_add_event_footer( $submit )	{
		global $event_error;
		?>
        <hr />
        <table class="form-table">
        <tr>
        <th scope="row">&nbsp;</th>
        <td>
		<?php 
		if( do_reg_check( 'check' ) )	{
			if( !isset( $event_error ) || $event_error == '0' )	{
				submit_button( $submit, 'primary', 'submit', false );	
			}
		}
		?>
        </td>
        <td colspan="2" align="left"><a class="button-secondary" title="<?php _e( 'Go Back' ); ?>" onclick="window.history.go(-1)"><?php _e( 'Back' ); ?></a></td>
        </tr>
        </table>
        </form>
        </div>
        <?php
	} // f_mdjm_add_event_footer
	
/*
* f_mdjm_event_validate
* 21/12/2015
* @since 0.9.9
* Validates field entries
*/
	function f_mdjm_event_validate( $fields )	{
		global $event_error;
		
		$event_error = '0';
		$error_msg = array();
		/* Check event date is not in the past */
		if( isset( $fields['event_date'] ) && !empty( $fields['event_date'] ) )	{
			$event_date = explode( '/', $fields['event_date'] );
			$event_date = $event_date[2] . '-' . $event_date[1] . '-' . $event_date[0];
			if( strtotime( $event_date ) < time() )	{
				$event_error = '1';
				$error_msg[] = 'Warning: The event date ' . date( 'd-m-Y' ) . ' is in the past. Click <a onclick="window.history.go(-1)">Back</a> to amend';	
			}
		}
		/* Check event date is set */
		elseif(! isset( $fields['event_date'] ) || empty( $fields['event_date'] ) )	{
			$event_error = '1';
			$error_msg[] = 'Warning: You have not entered a date for the event. Click <a onclick="window.history.go(-1)">Back</a> to do so';
		}
		if( isset( $fields['user_id'] ) && $fields['user_id'] == 'add_new' )	{
			if( !isset( $fields['client_first_name'] ) || empty( $fields['client_first_name'] ) )	{
				$event_error = '1';
				$error_msg[] = 'Warning: You have not entered the new Client\'s First Name. Click <a onclick="window.history.go(-1)">Back</a> to do so';
			}
			if( !isset( $fields['client_email'] ) || empty( $fields['client_email'] ) )	{
				$event_error = '1';
				$error_msg[] = 'Warning: You have not entered the new Client\'s Email Address. Click <a onclick="window.history.go(-1)">Back</a> to do so';
			}
			if( isset( $fields['client_email'] ) && !empty( $fields['client_email'] ) )	{
				if( !filter_var( $fields['client_email'], FILTER_VALIDATE_EMAIL ) )	{
					$event_error = '1';
					$error_msg[] = 'Warning: The Client\'s Email Address (' . $fields['client_email'] . ') does not appear to be valid. Click <a onclick="window.history.go(-1)">Back</a> to check it';
				}
				if( email_exists( $fields['client_email'] ) )	{
					$event_error = '1';
					$exist_user = get_user_by( 'email', $fields['client_email'] );
					$error_msg[] = 'Warning: The Client\'s Email Address you entered (' . $fields['client_email'] . ') already exists for Client ' . $exist_user->display_name . '. A client email address must be unique. Click <a onclick="window.history.go(-1)">Back</a> to change';	
				}
			}
		}
		
		if( $event_error == 1 )	{
			if( count( $error_msg ) > 1 )	{
				$errors = '<ui>';
				foreach( $error_msg as $msg )	{
					$errors .= '<li>' . $msg . '</li>';	
				}
				$errors .= '<ui>';	
			}
			else	{
				$errors = $error_msg[0];	
			}
			f_mdjm_update_notice( 'error', $errors );	
		}
	} // f_mdjm_event_validate
	
/**
 * Display a form for editing events
 *
 * @param $event_id
 *
 * @since 1.0
*/
	function f_mdjm_view_event_form( $event_id )	{
		global $mdjm_options, $mdjm_client_text, $wpdb;
		$eventinfo = f_mdjm_get_eventinfo_by_id( $event_id );
		if( !current_user_can( 'manage_options' ) && $eventinfo->event_dj != get_current_user_id() ) 
			wp_die( 'You cannot edit an event that is not yours unless you are an Administrator! <a href="' . admin_url() . 'admin.php?page=mdjm-events">Click here to return to your Events List</a>' );
		wp_enqueue_script('jquery-ui-datepicker');
		wp_enqueue_style('jquery-ui-css', 'http://ajax.googleapis.com/ajax/libs/jqueryui/1.8.2/themes/smoothness/jquery-ui.css');
		if( isset( $mdjm_options['id_prefix'] ) ) {
			$contract_id = $mdjm_options['id_prefix'] . $event_id;
		}
		else	{
			$contract_id = $event_id;	
		}
		/* -- Transactions -- */
		if( !class_exists( 'WP_List_Table' ) )	{
			require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
		}
		if( !class_exists( 'MDJM_Transactions' ) )	{
			require_once( WPMDJM_PLUGIN_DIR . '/admin/includes/class/class-mdjm-transactions.php' );
		}
		$mdjm_transactions = new MDJM_Transactions();
		
		/* -- Add new transactions -- */
		if( isset( $_POST['action'], $_POST['event_id'], $_POST['submit'] ) )	{
			if( $_POST['action'] == 'add_transaction' && $_POST['submit'] == 'Enter Transaction' )	{
				/* -- Security Check -- */
				check_admin_referer( 'add_event_transaction' );
				$mdjm_transactions->add_transaction( $_POST );	
			}
		}
		
		$transactions = $mdjm_transactions->single_event_transactions( $event_id );
		?>
		<script type="text/javascript">
		jQuery(document).ready(function($) {
			$('.custom_date').datepicker({
			dateFormat : 'dd/mm/yy',
			firstDay: <?php echo get_option( 'start_of_week' ); ?>,
			changeYear: true,
			changeMonth: true
			});
        });
        </script>
		<div class="wrap">
        <h2>Edit Event</h2>
        <p><a href="#transactions" title="View Event Transactions">View Transactions (<?php echo count( $transactions ); ?>)</a></p>
        <form name="mdjm-edit-event" id="mdjm-edit-event" method="post"  action="<?php echo admin_url() . 'admin.php?page=mdjm-events'; ?>">
        <input type="hidden" name="action" value="edit_event" />
        <input type="hidden" name="event_id" value="<?php echo $eventinfo->event_id; ?>" />
        <?php wp_nonce_field( 'mdjm_edit_event_verify' ); ?>
        <table class="form-table">
        <tr>
        <th scope="row">Event ID:</th>
        <td><?php echo $contract_id; ?></td>
        <th scope="row">Created:</th>
        <td><?php echo date( $mdjm_options['short_date_format'], strtotime( $eventinfo->date_added ) ); ?></td>
        </tr>
        <tr>
        <th scope="row"><label for="user_id">Client:</label></th>
        <td>
        <?php 
		if( current_user_can( 'administrator' ) || dj_can( 'add_client' ) )	{
			?>
        	<select name="user_id" id="user_id">
            <option value="" <?php selected( $eventinfo->user_id, '' ); ?>>--- Select Client ---</option>
        	<?php
			$client_list = f_mdjm_get_clients( 'client', 'display_name', 'ASC' );
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
				asort( $events );
				foreach( $events as $event )	{
					?>
					<option value="<?php echo $event; ?>"<?php selected( str_replace( "\r\n", "", $eventinfo->event_type ), $event ); ?>><?php echo $event; ?></option>
					<?php	
				}
			?>
            </select>
		</td>
        </tr>
        <tr>
        <th scope="row"><label for="event_start_hr">Start Time:</label></th>
        <td>
        <select name="event_start_hr" id="event_start_hr">
        <?php
		$minutes = array( '00', '15', '30', '45' );
		if( $mdjm_options['time_format'] == 'H:i' )	{
			$i = '00';
			$x = '23';
			$comp = 'H';
		}
		else	{
			$i = '1';
			$x = '12';
			$comp = 'g';	
		}
		while( $i <= $x )	{
			?>
            <option value="<?php echo $i; ?>"<?php selected( date( $comp, strtotime( $eventinfo->event_start ) ), $i ); ?>><?php echo $i; ?></option>
            <?php
			$i++;
		}
		?>
		</select>&nbsp;
        <select name="event_start_min" id="event_start_min">
        <?php
		foreach( $minutes as $minute )	{
			?>
            <option value="<?php echo $minute; ?>"<?php selected( date('i', strtotime( $eventinfo->event_start ) ), $minute ); ?>><?php echo $minute; ?></option>
            <?php	
		}
		?>
        </select>
        <?php
		if( $mdjm_options['time_format'] != 'H:i' )	{
			?>
            &nbsp;<select name="event_start_period" id="event_start_period">
            <option value="AM"<?php selected( date('A', strtotime( $eventinfo->event_start ) ), 'AM' ); ?>>AM</option>
            <option value="PM"<?php selected( date('A', strtotime( $eventinfo->event_start ) ), 'PM' ); ?>>PM</option>
            </select>
            <?php	
		}
		?>
        </td>
        <th scope="row"><label for="event_finish_hr">Finish Time:</label></th>
        <td>
        <select name="event_finish_hr" id="event_finish_hr">
        <?php
		$minutes = array( '00', '15', '30', '45' );
		if( $mdjm_options['time_format'] == 'H:i' )	{
			$i = '00';
			$x = '23';
			$comp = 'H';
		}
		else	{
			$i = '1';
			$x = '12';
			$comp = 'g';	
		}
		while( $i <= $x )	{
			?>
            <option value="<?php echo $i; ?>"<?php selected( date( $comp, strtotime( $eventinfo->event_finish ) ), $i ); ?>><?php echo $i; ?></option>
            <?php
			$i++;
		}
		?>
		</select>&nbsp;
        <select name="event_finish_min" id="event_finish_min">
        <?php
		foreach( $minutes as $minute )	{
			?>
            <option value="<?php echo $minute; ?>"<?php selected( date('i', strtotime( $eventinfo->event_finish ) ), $minute ); ?>><?php echo $minute; ?></option>
            <?php	
		}
		?>
        </select>
        <?php
		if( $mdjm_options['time_format'] != 'H:i' )	{
			?>
            &nbsp;<select name="event_finish_period" id="event_finish_period">
            <option value="AM"<?php selected( date('A', strtotime( $eventinfo->event_finish ) ), 'AM' ); ?>>AM</option>
            <option value="PM"<?php selected( date('A', strtotime( $eventinfo->event_finish ) ), 'PM' ); ?>>PM</option>
            </select>
            <?php	
		}
		?>
        </td>
        </tr>
        <tr>
        <th scope="row"><label for="event_description">Description:</label></th>
        <td colspan="3"><textarea cols="100" rows="4" id="event_description" name="event_description"><?php echo stripslashes( $eventinfo->event_description ); ?></textarea></td>
           <?php
        if( isset( $mdjm_options['enable_packages'] ) && $mdjm_options['enable_packages'] == 'Y' )	{
			?>
            <tr>
            <th scope="row" colspan="4">Note: Packages and add-on updates must be performed seperately and do not update other event details</th>
            </tr>
            </tr>
            <?php
			$packages = get_option( 'mdjm_packages' );
			asort( $packages );
            if( $packages )	{
                ?>
                <tr>
                <th scope="row"><label for="event_package">Event Package:</label></th>
                <td colspan="3"><select name="event_package" id="event_package">
                <option value="0">None</option>
                <?php
                foreach( $packages as $package )	{
                    if( !empty( $eventinfo->event_dj ) )	{ /* DJ Selected so only offer their packages */
                        $djs_with_package = explode( ',', $package['djs'] );
                        foreach( $djs_with_package as $dj_with_package )	{
                            if( $eventinfo->event_dj == $dj_with_package )	{
                                ?>
                                <option value="<?php echo $package['slug']; ?>" <?php selected( $eventinfo->event_package, $package['slug']  ); ?>><?php echo $package['name']; ?></option>
                                <?php
                            }
                        }
                    }
                    else	{ /* No DJ assigned, offer all packages */
                        echo '<option value="' . $package['slug'] . '">' . $package['name'] . '</option>';	
                    }
                }
                ?>
                </select>
                <?php
                if ( date( 'Y-m-d' ) < date( 'Y-m-d', strtotime( $eventinfo->event_date ) ) )	{
					submit_button( 'Update Package', 'primary', 'update_packages', false );
					echo ' <span class="description">Updating the package will reset all addons</span>';
				}
				else	{
					echo '&nbsp;';	
				}
            	?>
                </td>
                </tr>
                <?php
            }
		}
		if( isset( $mdjm_options['enable_packages'] ) && $mdjm_options['enable_packages'] == 'Y' )	{
			$equipment = get_option( 'mdjm_equipment' );
			if ($equipment )	{
				$packages = get_option( 'mdjm_packages' );
				/* Remove add on items included in selected package */
				$equipment_in_package = explode( ',', $packages[$eventinfo->event_package]['equipment'] );
				foreach( $equipment_in_package as $equip_in_package )	{
					unset( $equipment[$equip_in_package] );
				}
				$current_addons = explode( ',', $eventinfo->event_addons );
				if( count( $equipment > 0 ) )	{
					$cats = get_option( 'mdjm_cats' );
					asort( $cats );
					?>
					<tr>
					<th scope="row" valign="top"><label for="event_addons">Select Add-ons (if required):</label></th>
					<td colspan="3" valign="top">
					<?php
					foreach( $cats as $cat_key => $cat_value )	{
						echo '<strong>' . $cat_value . '</strong>';
						echo '<br />';
						foreach( $equipment as $equip_list )	{
							if( $equip_list[5] == $cat_key )	{
								?><input type="checkbox" name="event_addons[]" id="event_addons[]" value="<?php echo $equip_list[1]; ?>"<?php if( in_array( $equip_list[1], $current_addons ) ) echo ' checked="checked"'; ?>  /><?php
								echo esc_attr( $equip_list[0] );
		
								if( esc_attr( $equip_list[2] ) > 1 )
									echo ' x ' . esc_attr( $equip_list[2] );
								echo '<br />';
							}
						}
						echo '<br />';
					}
					if ( date( 'Y-m-d' ) < date( 'Y-m-d', strtotime( $eventinfo->event_date ) ) )	{
						submit_button( 'Update Add-Ons', 'primary', 'update_packages', false );
					}
					else	{
						echo '&nbsp;';	
					}
					?>
					</td>
					</tr>
					<?php
				}
			}
		}
        
		if( current_user_can( 'administrator' ) || dj_can( 'see_deposit' ) )	{
		?>
            <tr>
            <th scope="row"><label for="cost">Total Cost: <?php echo f_mdjm_currency(); ?></label></th>
            <td><input type="text" id="cost" class="regular-text" name="cost" value="<?php echo $eventinfo->cost; ?>" /></td>
            <th scope="row"><label for="deposit"><?php echo $mdjm_client_text['deposit_label']; ?>: <?php echo f_mdjm_currency(); ?></label></th>
            <td><input type="text" name="deposit" id="deposit" class="regular-text" value="<?php echo $eventinfo->deposit; ?>"></td>
            </tr>
            <tr>
            <th scope="row"><label for="deposit_status"><?php echo $mdjm_client_text['deposit_label']; ?> Paid?</label></th>
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
            <th scope="row"><?php echo $mdjm_client_text['balance_label']; ?> Due:</th>
            <td colspan="3"><?php echo f_mdjm_currency() . $eventinfo->cost - $eventinfo->deposit; ?></td>
            </tr>	
            <?php
		}
		?>
        <tr>
        <th scope="row"><label for="contract_status">Contract Status:</label></th>
        <td><select id="contract_status" name="contract_status">
            <option value="Enquiry"<?php selected( $eventinfo->contract_status, 'Enquiry' ); ?>>Enquiry</option>
            <option value="Pending"<?php selected( $eventinfo->contract_status, 'Pending' ); ?>>Pending</option>
            <option value="Approved"<?php selected( $eventinfo->contract_status, 'Approved' ); ?>>Approved</option>
            <option value="Failed Enquiry"<?php selected( $eventinfo->contract_status, 'Failed Enquiry' ); ?>>Failed Enquiry</option>
            <option value="Cancelled"<?php selected( $eventinfo->contract_status, 'Cancelled' ); ?>>Cancelled</option>
        	</select>
        </td>
        <th scope="row"><label for="contract_approved_date">Approved Date:</label></th>
        <td><input type="text" class="custom_date" name="contract_approved_date" id="contract_approved_date" value="<?php if( !empty( $eventinfo->contract_approved_date ) && $eventinfo->contract_status == 'Approved' ) echo date( 'd/m/Y', strtotime( $eventinfo->contract_approved_date ) ); ?>" disabled="disabled"></td>
        </tr>
        <tr>
        <th scope="row"><label for="balance_status"><?php echo $mdjm_client_text['balance_label']; ?> Paid?</label></th>
        <td colspan="3"><input type="checkbox" id="balance_status" name="balance_status" value="Paid" <?php if( $eventinfo->balance_status == 'Paid' ) echo ' checked'; ?> /></td>
        </tr>
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
        <h3>Administration</h3>
        <hr />
        <table class="form-table">
        <tr>
        <th class="row-title"><label for="dj_setup_hr">Setup Time:</label></th>
        <td>
        <select name="dj_setup_hr" id="dj_setup_hr">
        <?php
		if( !isset( $eventinfo->dj_setup_time ) || empty( $eventinfo->dj_setup_time ) || $eventinfo->dj_setup_time == 'NULL' || $eventinfo->dj_setup_time == '' )	{
			?>
            <option value="" selected="selected"></option>
            <?php	
		}
		$minutes = array( '00', '15', '30', '45' );
		if( $mdjm_options['time_format'] == 'H:i' )	{
			$i = '00';
			$x = '23';
			$comp = 'H';
		}
		else	{
			$i = '1';
			$x = '12';
			$comp = 'g';	
		}
		while( $i <= $x )	{
			?>
            <option value="<?php echo $i; ?>"<?php selected( date( $comp, strtotime( $eventinfo->dj_setup_time ) ), $i ); ?>><?php echo $i; ?></option>
            <?php
			$i++;
		}
		?>
		</select>&nbsp;
        <select name="dj_setup_min" id="dj_setup_min">
        <?php
		if( !isset( $eventinfo->dj_setup_time ) || empty( $eventinfo->dj_setup_time ) || $eventinfo->dj_setup_time == 'NULL' || $eventinfo->dj_setup_time == '' )	{
			?>
            <option value="" selected="selected"></option>
            <?php	
		}
		foreach( $minutes as $minute )	{
			?>
            <option value="<?php echo $minute; ?>"<?php selected( date( 'i', strtotime( $eventinfo->dj_setup_time ) ), $minute ); ?>><?php echo $minute; ?></option>
            <?php	
		}
		?>
        </select>
        <?php
		if( $mdjm_options['time_format'] != 'H:i' )	{
			?>
            &nbsp;<select name="dj_setup_period" id="dj_setup_period">
            <?php
			if( !isset( $eventinfo->dj_setup_time ) || empty( $eventinfo->dj_setup_time ) || $eventinfo->dj_setup_time == 'NULL' || $eventinfo->dj_setup_time == '' )	{
				?>
				<option value="" selected="selected"></option>
				<?php	
			}
			?>
            <option value="AM"<?php selected( date('A', strtotime( $eventinfo->dj_setup_time ) ), 'AM' ); ?>>AM</option>
            <option value="PM"<?php selected( date('A', strtotime( $eventinfo->dj_setup_time ) ), 'PM' ); ?>>PM</option>
            </select>
            <?php	
		}
		if( !isset( $eventinfo->dj_setup_date ) || empty( $eventinfo->dj_setup_date ) || $eventinfo->dj_setup_date == 'NULL' || $eventinfo->dj_setup_date == '0000-00-00' || $eventinfo->dj_setup_date == '' )	{
			$setup_date = $eventinfo->event_date;
		}
		else	{
			$setup_date = $eventinfo->dj_setup_date;
		}
		?>
        &nbsp;<strong>Date: </strong><input type="text" class="custom_date" name="dj_setup_date" value="<?php echo date( 'd/m/Y', strtotime( $setup_date ) ); ?>" />
        </td>
        </tr>
        <tr>
        <th class="row-title">DJ Notes:</th>
        <td><textarea name="dj_notes" id="dj_notes" cols="60" rows="5"><?php echo stripslashes( $eventinfo->dj_notes ); ?></textarea><br />
        <span class="description">Notes entered here can be seen by the Event DJ and Admins only. Clients will not see this information</span></td>
        </tr>
        <?php
        if( current_user_can( 'administrator' ) )	{
			?>
            <tr>
            <th class="row-title">Admin Notes:</th>
             <td><textarea name="admin_notes" id="admin_notes" cols="60" rows="5"><?php echo stripslashes( $eventinfo->admin_notes ); ?></textarea><br />
            <span class="description">Notes entered here can be seen by Admins only. DJ's & Clients will not see this information</span></td>
            </tr>
            <?php
		}
		?>
        <tr>
        <th scope="row"><?php
		if( date( 'Y-m-d' ) < date( 'Y-m-d', strtotime( $eventinfo->event_date ) ) )	{
			submit_button( 'Edit Event', 'primary', 'submit', false );
		}
		else	{
			echo '&nbsp;';	
		}
		?></th>
        <td align="center"><a class="button-secondary" href="<?php echo $_SERVER['HTTP_REFERER']; ?>" title="<?php _e( 'Cancel Changes' ); ?>"><?php _e( 'Back' ); ?></a></td>
        </tr>
        </table>
        </form>
        <?php
		if( current_user_can( 'administrator' ) )	{
			?>
            <a id="transactions"></a><h3>Event Transactions</h3>
            <hr />
            <p>The following transactions are associated with this event</p>
            <table class="widefat">
            <thead>
            <tr>
            <th>Date</th>
            <th>In</th>
            <th>Out</th>
            <th>Details</th>
            </tr>
            </thead>
            <tbody>
            
            <?php
            if( !$transactions )	{
                echo '<tr>' . "\n";
                echo '<td colspan="4">No transactions exist for this event</td>' . "\n";
                echo '</tr>' . "\n";	
            }
            else	{
                $t = 0;
                $total_in = '0.00';
                $total_out = '0.00';
                foreach( $transactions as $transaction )	{			
                    echo '<tr';
                    if( $t == 0 ) { echo ' class="alternate"'; }
                    echo '>' . "\n";
                    echo '<td>' . date( $mdjm_options['short_date_format'], strtotime( $transaction->payment_date ) ) . '</td>';
                    echo '<td>';
                    if( $transaction->direction == 'In' )	{
                        $total_in += $transaction->payment_gross;
                        echo f_mdjm_currency() . number_format( $transaction->payment_gross, 2 );
                    }
                    else	{
                        echo '-';	
                    }
                    echo '</td>' . "\n";
                    echo '<td>';
                    if( $transaction->direction == 'Out' )	{
                        $total_out += $transaction->payment_gross;
                        echo f_mdjm_currency() . number_format( $transaction->payment_gross, 2 );
                    }
                    else	{
                        echo '-';	
                    }
                    echo '</td>' . "\n";
                    echo '<td>' . stripslashes( $transaction->payment_for ) . '</td>' . "\n";
                    echo '</tr>' . "\n";
                    $t++;
                    if( $t == 2 ) $t = 0;
                }
            }
            ?>
            </tbody>
            <tfoot>
            <tr>
            <th>&nbsp;</th>
            <th><strong><?php echo f_mdjm_currency() . number_format( $total_in, 2 ); ?></strong></th>
            <th><strong><?php echo f_mdjm_currency() . number_format( $total_out, 2 ); ?></strong></th>
            <th><strong>Event Earnings: <?php echo f_mdjm_currency() . number_format( $total_in - $total_out, 2 ); ?></strong></th>
            </tr>
            </tfoot>
            </table>
            <script type="text/javascript">
            jQuery(document).ready(function($) {
                $('.transaction_date').datepicker({
                dateFormat : '<?php f_mdjm_short_date_jquery(); ?>',
                altField  : '#payment_date',
                altFormat : 'yy-mm-dd',
                firstDay: <?php echo get_option( 'start_of_week' ); ?>
                });
            });
            </script>
            <h3>Add Event Transaction</h3>
            <hr />
            <form name="add_event_transaction" id="add_event_transaction" method="post" action="">
            <?php wp_nonce_field( 'add_event_transaction' ); ?>
            <input type="hidden" name="action" id="action" value="add_transaction" />
            <input type="hidden" name="event_id" id="event_id" value="<?php echo $event_id; ?>" />
            <table class="form-table">
            <tr>
            <th scope="row"><label for="payment_gross">Amount:</label></th>
            <td><?php echo f_mdjm_currency(); ?><input type="text" name="payment_gross" id="payment_gross" class="small-text" placeholder="10.00" /></td>
            </tr>
            <tr>
            <th scope="row"><label for="trans_date">Date:</label></th>
            <td><input type="text" name="trans_date" id="trans_date" class="transaction_date" /></td>
            <input type="hidden" name="payment_date" id="payment_date" />
            </tr>
            <tr>
            <th scope="row"><label for="direction">Direction:</label></th>
            <td><select name="direction" id="direction" class="regular-text" onChange="displayPaid();">
            <option value="In">Incoming</option>
            <option value="Out">Outgoing</option>
            </select>
            </td>
            </tr>
            </table>
            <style>
                #paid_to_field	{
                    display: none;
                }
				#paid_from_field	{
                    display: block;
                }
            }
            </style>
            <script type="text/javascript">
            function displayPaid() {
                var direction  =  document.getElementById("direction");
                var direction_val = direction.options[direction.selectedIndex].value;
				var paid_from_div =  document.getElementById("paid_from_field");
                var paid_to_div =  document.getElementById("paid_to_field");
            
              if (direction_val == 'Out') {
				  paid_from_div.style.display = "none";
                  paid_to_div.style.display = "block";
              }
              else {
				  paid_from_div.style.display = "block";
                  paid_to_div.style.display = "none";
              }  
            } 
            </script>
            <div id="paid_from_field">
            <table class="form-table">
            <tr>
            <th scope="row"><label for="payment_from">Paid From:</label></th>
            <td><input type="text" name="payment_from" id="payment_from" class="regular_text" /> <span class="description">Leave empty if payment from client</span></td>
            </tr>
            </table>
            </div>
            <div id="paid_to_field">
            <table class="form-table">
            <tr>
            <th scope="row"><label for="payment_to">Paid To:</label></th>
            <td><input type="text" name="payment_to" id="payment_to" class="regular_text" /> <span class="description">Leave empty if payment to client</span></td>
            </tr>
            </table>
            </div>
            <table class="form-table">
            <tr>
            <th scope="row"><label for="payment_for">Details:</label></th>
            <td><?php $mdjm_transactions->drop_transaction_types(); ?></td>
            </tr>
            <tr>
            <th scope="row"><label for="payment_src">Source:</label></th>
            <td><?php $mdjm_transactions->drop_payment_source(); ?></td>
            </tr>
            <tr>
            <th scope="row">&nbsp;</th>
            <td><?php submit_button( 'Enter Transaction', 'primary', 'submit', false ); ?></td>
            </tr>
            </table>
            </form>
            <?php
		}
		?>
        </div>
        <?php
	} // f_mdjm_view_event_form

/**
 * Show printable playlist
 * 
 *
 * @since 1.0
*/
	function f_mdjm_print_playlist( $post_data )	{
		global $wpdb;
		
		if( !isset( $db_tbl ) )	{
			include( WPMDJM_PLUGIN_DIR . '/includes/config.inc.php' );
		}
		
		$print_query = 'SELECT * FROM `' . $db_tbl['playlists'] . '` WHERE `event_id` = ' . $post_data['event_id'] . ' ORDER BY `' . $post_data['order_pl_by'] . '` ASC';
		$print_result = $wpdb->get_results( $print_query );
		$pl_ttl = $wpdb->num_rows;
		
		if( !isset( $post_data['repeat_headers'] ) || empty( $post_data['repeat_headers'] ) || $post_data['repeat_headers'] == 0 )	{
			$repeat = 0;
		}
		else	{
			$repeat = $post_data['repeat_headers'];
		}
		
		$i = 0;
		
		$eventinfo = $wpdb->get_row('SELECT * FROM ' . $db_tbl['events'] . ' WHERE `event_id` = ' . $post_data['event_id']);
		$client = get_userdata( $eventinfo->user_id );
		
		?>
        <script type="text/javascript">
		window.onload = function() { window.print(); }
		</script>
        <style>
		body { 
			background:white;
			color:black;
			margin:0;
			width:auto
		}
		#adminmenu {
			display: none !important
		}
		#wpadminbar {
			display: none !important
		}
		#wpheader {
			display: none !important;
		}
		#wpcontent {
			margin-left:0; 
			float:none; 
			width:auto }
		}
		#wpcomments {
			display: none !important;
		}
		#message {
			display: none !important;
		}
		#wpsidebar {
			display: none !important;
		}
		#wpfooter {
			display: none !important;
		}
		</style>
        <p>Client Name: <?php echo $client->first_name . ' ' . $client->last_name; ?><br />
        Event Date: <?php echo date( "l, jS F Y", strtotime( $eventinfo->event_date ) ); ?><br />
        Event Type: <?php echo $eventinfo->event_type; ?><br />
        No. Songs in Playlist: <?php echo $pl_ttl; ?><br /></p>
        <hr />
        <table border="1" cellpadding="0" cellspacing="0" width="90%" align="center">
        <tr height="30">
        <th width="15%">Artist</th>
        <th width="15%">Song</th>
        <th width="15%">When to Play</th>
        <th width="40%">Info</th>
        <th width="15%">Added By</th>
        </tr>
        <?php
		foreach( $print_result as $playlist )	{
			if( $repeat > 0 && $i == $repeat )	{
					?>
                    </table>
                    <p style="page-break-after:always;">&nbsp;</p>
                    <table border="1" cellpadding="0" cellspacing="0" width="90%" align="center">
                    <tr height="30">
                    <th width="15%">Artist</th>
                    <th width="15%">Song</th>
                    <th width="15%">When to Play</th>
                    <th width="40%">Info</th>
                    <th width="15%">Added By</th>
                    </tr>
					<?php
					$i = 0;
				}
			?>
            <tr height="30">
            <td><?php echo stripslashes( $playlist->artist ); ?></td>
            <td><?php echo stripslashes( $playlist->song ); ?></td>
            <td><?php echo stripslashes( $playlist->play_when ); ?></td>
            <td><?php echo stripslashes( $playlist->info ); ?></td>
            <td><?php echo stripslashes( $playlist->added_by ); ?></td>
            </tr>
            <?php
			$i++;	
		}
        ?>
        </table>
        <p align="center">Powered by Mobile DJ Manager for Wordpress, version <?php echo WPMDJM_VERSION_NUM; ?></p>
        <?php
	} // f_mdjm_print_playlist
	
	function f_mdjm_show_journal()	{
		include( WPMDJM_PLUGIN_DIR . '/admin/pages/show-journal.php' );
		exit;
	}
	
	function f_mdjm_test()	{
		global $wpdb, $mdjm_options;
		
		include( WPMDJM_PLUGIN_DIR . '/includes/config.inc.php' );
		
		$mdjm_pp_options = get_option( 'mdjm_pp_options' );
				
				/* -- Add the new payment options -- */
				$mdjm_pp_options['pp_payment_sources'] = "BACS\r\nCash\r\nCheque\r\nPayPal\r\nOther";
				$mdjm_pp_options['pp_transaction_types'] = "Certifications\r\nHardware\r\nInsurance\r\nMaintenance\r\nMusic\r\nParking\r\nPetrol\r\nSoftware\r\nVehicle";
		update_option( 'mdjm_pp_options', $mdjm_pp_options );		
		
		exit;
	}
	
/**
 * Process actions submitted via $_GET or show the main events page
 * 
 *
 * @since 1.0
*/
	if( isset( $_POST['print_pl'] ) && $_POST['print_pl'] == 'Print this List' )	{
		f_mdjm_print_playlist( $_POST );
	}
	
	elseif( isset( $_GET['action'] ) && $_GET['action'] == 'show_journal' )	{
		$func = 'f_mdjm_' . $_GET['action'];
		$func();
		exit;
	}
	elseif( isset( $_GET['action'] ) )	{ // Action to process
		$func = 'f_mdjm_' . $_GET['action'];
		if( function_exists( $func ) ) $func( $_GET['event_id'] );
	}
	
	else	{ // Display the Events table
		if( !isset( $_POST['submit'] ) || $_POST['submit'] != 'Next' )	{
			f_mdjm_render_events_table();
		}
		elseif( isset( $_GET['action'] ) && $_GET['action'] == 'convert_event' )	{
			f_mdjm_render_events_table();
		}
	}
	
?> 