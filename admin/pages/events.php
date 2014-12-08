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
	
		if( !class_exists( 'MDJM_PlayList_Table' ) ) {
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
				$submit = 'Create Enquiry';
				f_mdjm_add_event_review();
			}
		}
		elseif( $_POST['step'] == 3 && $mdjm_options['enable_packages'] == 'Y' )	{
			if( isset( $mdjm_options['enable_packages'] ) && $mdjm_options['enable_packages'] == 'Y' ) 	{
				$submit = 'Next';
				f_mdjm_add_event_step_3();
			}
			else	{
				$submit = 'Create Enquiry';
				f_mdjm_add_event_review();
			}
		}
		else	{
			$submit = 'Create Enquiry';
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
			dateFormat : 'dd/mm/yy'
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
		?>
        <table class="form-table">
        <tr>
        <th scope="row"><label for="user_id">Select Client:</label></th>
        <td><select name="user_id" id="user_id">
        	<option value="">--- Select Client ---</option>
        <?php
		$client_list = f_mdjm_get_clients( 'client', 'display_name', 'ASC' );
        foreach( $client_list as $client )	{
			?>
			<option value="<?php echo $client->ID; ?>" <?php selected( $_POST['client'], $client->ID ); ?>><?php echo $client->display_name; ?></option>';	
            <?php
		}
		?></select> <?php if( current_user_can( 'administrator' ) || dj_can( 'add_client' ) )	echo '<a href="' . admin_url() . 'user-new.php" class="add-new-h2">Add New</a>'; ?></td>
        <th scope="row"><label for="event_dj">Select DJ:</label></th>
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
        	<option value="" <?php if( !isset( $_POST['client'] ) || empty( $_POST['client'] ) ) echo 'selected'; ?>>--- Select ---</option>
			<?php
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
        <th scope="row"><label for="event_start_hr">Start Time:</label></th>
        <td>
        <select name="event_start_hr" id="event_start_hr">
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
            <option value="<?php echo $i; ?>"><?php echo $i; ?></option>
            <?php
			$i++;
		}
		?>
		</select>&nbsp;
        <select name="event_start_min" id="event_start_min">
        <?php
		foreach( $minutes as $minute )	{
			?>
            <option value="<?php echo $minute; ?>"><?php echo $minute; ?></option>
            <?php	
		}
		?>
        </select>
        <?php
		if( $mdjm_options['time_format'] != 'H:i' )	{
			?>
            &nbsp;<select name="event_start_period" id="event_start_period">
            <option value="AM">AM</option>
            <option value="PM">PM</option>
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
            <option value="<?php echo $i; ?>"><?php echo $i; ?></option>
            <?php
			$i++;
		}
		?>
		</select>&nbsp;
        <select name="event_finish_min" id="event_finish_min">
        <?php
		foreach( $minutes as $minute )	{
			?>
            <option value="<?php echo $minute; ?>"><?php echo $minute; ?></option>
            <?php	
		}
		?>
        </select>
        <?php
		if( $mdjm_options['time_format'] != 'H:i' )	{
			?>
            &nbsp;<select name="event_finish_period" id="event_finish_period">
            <option value="AM">AM</option>
            <option value="PM">PM</option>
            </select>
            <?php	
		}
		?>
        </td>
        </tr>
        <tr>
        <th scope="row"><label for="event_cost">Cost:</label></th>
        <td colspan="3">&pound;<input type="text" name="event_cost" id="event_cost" class="small-text" value="<?php echo $_POST['event_cost']; ?>" /> <span class="description">No currency symbol needed. Package &amp; add-on costs (if enabled) will be added automatically</span></td>
        </tr>
        <tr>
        <th scope="row"><label for="event_description">Description:</label></th>
        <td colspan="3"><textarea cols="100" rows="4" id="event_description" name="event_description"><?php echo $_POST['event_description']; ?></textarea></td>
        </tr>
        <?php
		$venueinfo = f_mdjm_get_venueinfo();
		?>
        <tr>
        <th scope="row"><label for="event_venue">Event Venue</label></th>
        <td colspan="3"><select name="event_venue" id="event_venue" onChange="displayVenue();">
        <option value=""<?php if( empty( $_POST['event_venue'] ) ) echo ' selected="selected"'; ?>>--- Select Venue ---</option>
        <option value="manual"<?php if( $_POST['event_venue'] == 'manual' || !$venueinfo ) echo ' selected="selected"'; ?>>Enter Manually</option>
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
			if( !$venueinfo )	{
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
		global $mdjm_options;
		$total_cost = $_POST['event_cost'];
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
        <p>Finally, review the cost information below and select whether or not to email the quote to your client and/or reset their password...</p>
        <input type="hidden" name="action" value="add_event" />
        <table class="form-table">
        <tr>
        <th scope="row" width="20%"><label for="total_cost">Total Event Cost:</label></th>
        <td colspan="3">&pound;<input type="text" name="total_cost" id="total_cost" value="<?php echo number_format( $total_cost, 2 ); ?>" /> <span class="description">Includes cost of packages and addons. Adjust if required.</span></td>
        </tr>
        <tr>
        <th scope="row" width="20%"><label for="deposit">Deposit:</label></th>
        <td colspan="3">&pound;<input type="text" name="deposit" id="deposit" value="<?php echo number_format( $_POST['deposit'] ); ?>" /> <span class="description">If you require a deposit to be paid upon booking, enter the amount here</span></td>
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
        <td colspan="3"><input type="checkbox" id="email_enquiry" name="email_enquiry" value="Y" /> <span class="description">Select this option to email the quote to the client once created</span> </td>
        </tr>
        </table>
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
        <th scope="row">DJ Notes:</th>
        <td><textarea name="dj_notes" id="dj_notes" cols="60" rows="5"></textarea><br />
<span class="description">Notes entered here can be seen by the Event DJ and Admins only. Clients will not see this information</span></td>
        </tr>
        <?php
		if( current_user_can( 'administrator' ) )	{
			?>
			<tr>
			<th scope="row">Admin Notes:</th>
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
		?>
        <table class="form-table">
        <tr>
        <th scope="row">&nbsp;</th>
        <td>
		<?php if( do_reg_check( 'check' ) )	{
			submit_button( $submit, 'primary', 'submit', false );	
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
	
/**
 * Display a form for editing events
 *
 * @param $event_id
 *
 * @since 1.0
*/
	
	function f_mdjm_view_event_form( $event_id )	{
		global $mdjm_options;
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
        <th scope="row">Event ID:</th>
        <td colspan="3"><?php echo $contract_id; ?></td>
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
        <tr>
        <th scope="row" colspan="4">Note: Packages and add-on updates must be performed seperately and do not update other event details</th>
        </tr>
        </tr>
           <?php
        if( isset( $mdjm_options['enable_packages'] ) && $mdjm_options['enable_packages'] == 'Y' )	{
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
        <td><input type="text" class="custom_date" name="contract_approved_date" id="contract_approved_date" value="<?php if(! empty( $eventinfo->contract_approved_date ) ) echo date( 'd/m/Y', strtotime( $eventinfo->contract_approved_date ) ); ?>" disabled="disabled"></td>
        </tr>
        <tr>
        <th scope="row"><label for="balance_status">Balance Paid?</label></th>
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
        <hr />
        <h3>Administration</h3>
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
	
	function f_mdjm_test()	{
		global $mdjm_options;
		/* Access the cron functions */
		//echo date( 'H:i d M Y', wp_next_scheduled( 'hook_mdjm_hourly_schedule' ) );
		//require_once( WPMDJM_PLUGIN_DIR . '/admin/includes/mdjm-cron.php' );
		//f_mdjm_cron_balance_reminder();
		echo $mdjm_options['system_email'];
		
		exit;
	}
	
/**
 * Process actions submitted via $_GET or show the main events page
 * 
 *
 * @since 1.0
*/
	if( isset( $_GET['action'] ) && $_GET['action'] == 'show_journal' )	{
		$func = 'f_mdjm_' . $_GET['action'];
		$func();
		exit;
	}
	if( isset( $_GET['action'] ) )	{ // Action to process
		$func = 'f_mdjm_' . $_GET['action'];
		if( function_exists( $func ) ) $func( $_GET['event_id'] );
	}
	
	else	{ // Display the Events table
		if( $_POST['submit'] != 'Next' || $_GET['action'] == 'convert_event' )	{
			f_mdjm_render_events_table();
		}
	}
	//if( $_GET['action'] == 'convert_event' || $_GET['action'] == 'cancel_event' || $_GET['action'] == 'recover_event' ||
	//	|| $_GET['action'] == 'fail_enquiry' )
	
?> 