<?php
/**
 * * * * * * * * * * * * * * * MDJM * * * * * * * * * * * * * * *
 * Functions that are used mainly within the frontend
 * may also be called from the backend
 *
 *
 * @since 1.0
 *
 */
 
/****************************************************************************************************
--	GENERAL FUNCTIONS
****************************************************************************************************/
/**
 * f_mdjm_insert_head
 * Adds header content to all pages on the frontend
 * 
 * 
 * Called from: plugin main file
 * @since 1.0
*/
	function f_mdjm_insert_head()	{
        echo "<!-- Start Mobile DJ Manager Header Content -->\r\n";
		echo "<!-- End Mobile DJ Manager Header Content -->\r\n";
	} // f_mdjm_insert_head
	
/**
 * f_wpmdjm_print_credit
 * Prints author & application credit to the foot of every application
 * page on the frontend
 * 
 * 
 * Called from: all frontend pages
 * @since 1.0
*/
	function f_wpmdjm_print_credit()	{
		if ( WPMDJM_CREDITS == 'Y' )	{
			?>
			<p align="center" style="font-size:9px; color:#F90">Powered by <a style="font-size:9px; color:#F90" href="http://www.mydjplanner.co.uk" target="_blank"><?php echo WPMDJM_NAME; ?></a>, version <?php echo WPMDJM_VERSION_NUM; ?></p>
			<?php
		}
	} // f_wpmdjm_print_credit
	
/**
 * f_mdjm_get_options
 * Retrieves general MDJM meta settings
 * 
 * 
 * Called from: plugin main file & made global to both
 * frontend and backend
 * @since 1.0
*/
	function f_mdjm_get_options()	{
		$mdjm_options = get_option ( WPMDJM_SETTINGS_KEY );
		$mdjm_permissions = get_option ( 'mdjm_plugin_permissions' );
		$mdjm_pages = get_option ( 'mdjm_plugin_pages' );
		if( $mdjm_pages )	{
			foreach( $mdjm_pages as $key => $value )	{
				$mdjm_options[$key] = $value;
			}
		}
		if( $mdjm_permissions )	{
			foreach( $mdjm_permissions as $key => $value )	{
				$mdjm_options[$key] = $value;
			}
		}
		return $mdjm_options;
	} // f_get_mdjm_options

/****************************************************************************************************
--	USER FUNCTIONS
****************************************************************************************************/
/**
 * f_mdjm_show_user_login_form
 * Show the user login form on the frontend
 * 
 * 
 * Called from: all frontend pages
 * frontend
 * @since 1.0
*/
	function f_mdjm_show_user_login_form()	{
		print("<p>You must be logged in to enter this area of the website. Please enter your username and password below to continue, or use the menu items above to navigate to another area of our website.</p>\n");
		wp_login_form();
	} // f_mdjm_show_user_login_form

/**
 * f_mdjm_add_lost_password_link
 * Show the lost password link on the login form
 * 
 * 
 * Called from: add_action
 * frontend
 * @since 1.0
*/
	add_action( 'login_form_middle', 'f_mdjm_add_lost_password_link' );
	function f_mdjm_add_lost_password_link() {
		return '<a href="/wp-login.php?action=lostpassword">Lost Password?</a>';
	}

/**
 * f_mdjm_update_user_profile
 * Validate fields & update user profile at the frontend
 * 
 * 
 * Called from: edit profile frontend
 * 
 * @since 1.0
*/
	function f_mdjm_update_user_profile()	{
		global $_POST, $current_user;
		$profile_update_fields = array ( 'ID' => $current_user->ID );
		$profile_update_fields_meta = array ();
		if ( !empty ( $_POST['first_name'] ) && $_POST['first_name'] != $current_user->first_name )	{
			$profile_update_fields['first_name'] = sanitize_text_field( $_POST['first_name'] );
		}
		if ( !empty ( $_POST['last_name'] ) && $_POST['last_name'] != $current_user->last_name )	{
			$profile_update_fields['last_name'] = sanitize_text_field( $_POST['last_name'] );
		}
		if ( !empty ( $_POST['phone1'] ) && $_POST['phone1'] != $current_user->phone1 )	{
			$profile_update_fields_meta['phone1'] = sanitize_text_field( $_POST['phone1'] );
		}
		if ( !empty ( $_POST['phone2'] ) && $_POST['phone2'] != $current_user->phone2 )	{
			$profile_update_fields_meta['phone2'] = sanitize_text_field( $_POST['phone2'] );
		}
		if ( !empty ( $_POST['user_email'] ) && $_POST['user_email'] != $current_user->user_email )	{
			$profile_update_fields['user_email'] = sanitize_email( $_POST['user_email'] );
		}
		if ( !empty ( $_POST['address1'] ) && $_POST['address1'] != $current_user->address1 )	{
			$profile_update_fields_meta['address1'] = sanitize_text_field( $_POST['address1'] );
		}
		if ( !empty ( $_POST['address2'] ) && $_POST['address2'] != $current_user->address2 )	{
			$profile_update_fields_meta['address2'] = sanitize_text_field( $_POST['address2'] );
		}
		if ( !empty ( $_POST['town'] ) && $_POST['town'] != $current_user->town )	{
			$profile_update_fields_meta['town'] = sanitize_text_field( $_POST['town'] );
		}
		if ( !empty ( $_POST['county'] ) && $_POST['county'] != $current_user->county )	{
			$profile_update_fields_meta['county'] = sanitize_text_field( $_POST['county'] );
		}
		if ( !empty ( $_POST['postcode'] ) && $_POST['postcode'] != $current_user->postcode )	{
			$profile_update_fields_meta['postcode'] = sanitize_text_field( $_POST['postcode'] );
		}
		if ( !empty ( $_POST['birthday'] ) && $_POST['birthday'] != $current_user->birthday && $_POST['birthday'] != 'empty' )	{
			$profile_update_fields_meta['birthday'] = sanitize_text_field( $_POST['birthday'] );
		}
		if ( !empty ( $_POST['new_password'] ) &&  $_POST['new_password'] != $_POST['new_password_confirm'] )	{
			$pass_error = true;
		}
		if ( !empty ( $_POST['new_password'] ) &&  $_POST['new_password'] == $_POST['new_password_confirm'] )	{
			$profile_update_fields['user_pass'] = $_POST['new_password'];
		}
		
		$profile_update_fields_meta['marketing'] = $_POST['marketing'];
		
		/* Process any custom fields that have been added */
		$custom_fields = get_option( WPMDJM_CLIENT_FIELDS );
		foreach ( $custom_fields as $custom_field )	{
			if( $custom_field['default'] == false && $custom_field['display'] == 'Y' )	{
				$profile_update_fields_meta[$custom_field['id']] = $_POST[$custom_field['id']];
			}
		}
		$user_update = wp_update_user ( $profile_update_fields );
		foreach ( $profile_update_fields_meta as $meta_key => $meta_value ) {
			$user_update_meta = update_user_meta ( $current_user->ID, $meta_key, $meta_value );
		}

		if( isset ( $profile_update_fields['user_pass'] ) )	{
			wp_logout();
			wp_redirect( get_permalink() );
		}

		if( is_wp_error( $user_update ) ) {
			print ("<p style=\"color:#F93; font-size:11px;\">Unable to update your profile. ".$user_update->get_error_message()."</p>\n");
		}
		else {
			print ("<p style=\"color:#F93; font-size:11px;\">Your profile has been updated successfully</p>\n");
		}
		if( $pass_error == true )	{
			print ("<p style=\"color:#F93; font-size:11px;\">Unable to change password. Check the password's you entered match!</p>\n");
		}
	} // f_mdjm_update_user_profile

/****************************************************************************************************
--	EVENT FUNCTIONS
****************************************************************************************************/
/**
 * f_mdjm_get_eventinfo
 * Retrieve event info for current user
 * 
 * 
 * Called from: frontend application pages
 * 
 * @since 1.0
*/
	function f_mdjm_get_eventinfo( $db_tbl, $current_user )	{
		global $wpdb, $eventinfo;
		$eventinfo = $wpdb->get_row("SELECT * FROM `".$db_tbl['events']."` WHERE `user_id` = '".$current_user->ID."' AND `event_date` >= DATE(NOW()) AND `contract_status` = 'Approved'");
		return $eventinfo;
	} // f_mdjm_get_eventinfo

/**
 * f_mdjm_get_event_by_id
 * Retrieve event info by given ID
 * 
 * 
 * Called from: frontend application pages
 * 
 * @since 1.0
*/
	function f_mdjm_get_event_by_id( $db_tbl, $event_id )	{
		global $wpdb;
		
		$eventinfo = $wpdb->get_row("SELECT * FROM `".$db_tbl['events']."` WHERE `event_id` = '" . $event_id . "'");
		
		return $eventinfo;
	} // f_mdjm_get_event_by_id
	
/**
 * f_mdjm_get_guest_eventinfo
 * Retrieve event info guest visitors
 * 
 * 
 * Called from: frontend playlist page
 * 
 * @since 1.0
*/
	function f_mdjm_get_guest_eventinfo( $db_tbl, $event_id )	{
		global $wpdb, $eventinfo;
		$eventinfo = $wpdb->get_row("SELECT * FROM `".$db_tbl['events']."` WHERE `event_guest_call` = '".$event_id."'");
	} // f_mdjm_get_guest_eventinfo

/****************************************************************************************************
--	CONTRACT FUNCTIONS
****************************************************************************************************/
/**
 * f_mdjm_client_approve_contract
 * Mark the contract as signed
 * 
 * 
 * Called from: frontend contract page
 * 
 * @since 1.0
*/
	function f_mdjm_client_approve_contract( $eventinfo, $input, $table )	{
		global $wpdb;
		if( !( $eventinfo ) || !( $input ) )	{
			wp_die( 'An error has occured. Please contact the <a href="mailto:' . get_bloginfo( 'admin_email' ) . '">website administrator</a><br />' . $wpdb->print_error() );
		}
		$approver = get_user_by( 'display_name', $input['approver'] );
		$update = array(  
							'contract_status' => 'Approved',
							'contract_approved_date' => date( 'Y-m-d' ),
							'contract_approver' => $input['approver'],
							'deposit_status' => $input['deposit'],
							'last_updated_by' => get_current_user_id(),
							'last_updated' => date( 'Y-m-d H:i:s' ),
						);
		if( $wpdb->update( $table, $update, array( 'event_id' => $eventinfo->event_id ) ) )	{
			$j_args = array (
					'client' => $eventinfo->user_id,
					'event' => $eventinfo->event_id,
					'author' => get_current_user_id(),
					'type' => 'Update Event',
					'source' => 'Website',
					'entry' => 'The Event contract has been signed'
				);
			if( WPDJM_JOURNAL == 'Y' ) f_mdjm_do_journal( $j_args );
			echo '<p><strong>You have successfully signed the contract. Thank you.</strong></p>';										
		}
		else	{
			wp_die( 'An error has occured. Please contact the <a href="mailto:' . get_bloginfo( 'admin_email' ) . '">website administrator</a><br />' . $wpdb->print_error() );
		}
	} // f_mdjm_client_approve_contract

/****************************************************************************************************
--	DJ FUNCTIONS
****************************************************************************************************/
/**
 * f_mdjm_get_dj_info
 * Retrieve dj info from event
 * 
 * 
 * Called from: frontend
 * 
 * @since 1.0
*/
	function f_mdjm_get_djinfo( $db_tbl, $eventinfo )	{
		global $wpdb;
		$djinfo = get_user_by( 'id', $eventinfo->event_dj );
		return $djinfo;
	} // f_mdjm_get_djinfo

/****************************************************************************************************
--	PLAYLIST FUNCTIONS
****************************************************************************************************/
/**
 * f_mdjm_get_playlist
 * Retrieve playlist entries for event
 * 
 * 
 * Called from: frontend
 * 
 * @since 1.0
*/
	function f_mdjm_get_playlist( $db_tbl, $eventinfo )	{
		global $wpdb, $eventinfo, $playlist_result;
		$playlist_result = $wpdb->get_results("SELECT * FROM `".$db_tbl['playlists']."` WHERE `event_id` = '".$eventinfo->event_id."' ORDER BY `play_when`, `artist`");
	} // f_mdjm_get_playlist

/**
 * f_mdjm_remove_playlistsong
 * Delete song from playlist
 * 
 * 
 * Called from: playlist
 * 
 * @since 1.0
*/	
	function f_mdjm_remove_playlistsong( $db_tbl, $song_id )	{
		global $wpdb;
		$songinfo = $wpdb->get_row( "SELECT * FROM " . $db_tbl['playlists'] . " WHERE `id` = '" . $song_id . "'");
		$eventinfo = $wpdb->get_row( "SELECT * FROM " . $db_tbl['events'] . " WHERE `event_id` = '" . $song_id->event_id . "'");
		$playlist_remove = $wpdb->delete($db_tbl['playlists'], array( 'id' => $song_id ));	
			if($playlist_remove > 0)	{
				print( '<p style="color:#F93">The song ' . $songinfo->song . ' by ' . $songinfo->artist  . ' has been successfully removed from the playlist</p>' );
			}
		$j_args = array (
						'client' => $eventinfo->user_id,
						'event' => $songinfo->event_id,
						'author' => get_current_user_id(),
						'type' => 'Playlist',
						'source' => 'Website',
						'entry' => 'Song ' . $songinfo->song . ' by ' . $songinfo->artist  . ' removed from playlist'
					);
		if( WPDJM_JOURNAL == 'Y' ) f_mdjm_do_journal( $j_args );
	} // f_mdjm_remove_playlistsong

/**
 * f_mdjm_add_playlistsong
 * Add song to playlist
 * 
 * 
 * Called from: playlist
 * 
 * @since 1.0
*/
	function f_mdjm_add_playlistsong( $db_tbl, $eventinfo, $playlist_array )	{
		global $wpdb;
		// Form validation
		if ( empty ( $playlist_array['playlist_artist'] ) || empty ( $playlist_array['playlist_song'] ) )	{
			print("<p style=\"color:#FF0000\">ERROR: You need to complete both the Artist and Song fields.</p>\n");
		}
		elseif ( isset ( $_POST['first_name'] ) && empty ( $_POST ['first_name'] ) )	{
			print("<p style=\"color:#FF0000\">ERROR: Please enter your first name.</p>\n");	
		}
		elseif ( isset ( $_POST['last_name'] ) && empty ( $_POST ['last_name'] ) )	{
			print("<p style=\"color:#FF0000\">ERROR: Please enter your last name.</p>\n");	
		}
		elseif ( $playlist_array['playlist_when'] == "Other" && empty ( $playlist_array['playlist_info'] ) )	{
			print("<p style=\"color:#FF0000\">ERROR: As you selected \"Other\" from the When to Play field, you must enter some additional information into the Info field.</p>\n");
		}
		else	{ // Insert the record
			if( !isset( $playlist_array['playlist_when'] ) || $playlist_array['playlist_when'] == '' ) $playlist_array['playlist_when'] = 'General';
			if( $wpdb->insert( $db_tbl['playlists'],
												array(
													'id' =>	'',
													'event_id' => $eventinfo->event_id,
													'artist' => $playlist_array['playlist_artist'],
													'song' => $playlist_array['playlist_song'],
													'play_when' => $playlist_array['playlist_when'],
													'info' => $playlist_array['playlist_info'],
													'added_by' => $playlist_array['added_by'],
													'date_added' => date( 'Y-m-d' ),
												) ) ) {
				$c_msg = 'The song has been successfully added to your playlist';
				if( !is_user_logged_in() ) $c_msg = 'Thank you. The song ' . $playlist_array['playlist_song'] . ' by ' . $playlist_array['playlist_artist'] . ' has been successfully added to the playlist.';
				print('<p style="color:#F93">' . $c_msg . '</p>');	
			}
			else	{
				die( $wpdb->print_error() );	
			}
			$j_args = array (
							'client' => $eventinfo->user_id,
							'event' => $eventinfo->event_id,
							'author' => get_current_user_id(),
							'type' => 'Playlist',
							'source' => 'Website',
							'entry' => 'Song ' . $playlist_array['playlist_song'] . ' by artist ' . $playlist_array['playlist_artist'] . ' added to playlist by ' . $playlist_array['added_by']
						);
			if( WPDJM_JOURNAL == 'Y' ) f_mdjm_do_journal( $j_args );
		}
	} // f_mdjm_add_playlistsong
	
/****************************************************************************************************
--	Contact	--
****************************************************************************************************/
/**
 * f_mdjm_contact
 * Create link for email contact
 * 
 * 
 * Called from: playlist
 * 
 * @since 1.0
*/
	function f_mdjm_contact( $subject )	{
		print("<a href=\"mailto:". get_bloginfo('admin_email') ."?subject=".$subject."\">". get_bloginfo('admin_email') ."</a>");
	} // f_mdjm_contact
	
/****************************************************************************************************
--	JOURNALING
****************************************************************************************************/
/**
 * f_mdjm_do_journal
 * Update the journal entry
 * 
 * 
 * Called from: frontend & backend
 * 
 * @since 1.0
*/
	function f_mdjm_do_journal( $args )	{
		global $wpdb;
		include( WPMDJM_PLUGIN_DIR . '/includes/config.inc.php' );
		
		$args['id'] = '';
		$args['timestamp'] = time();
		if( !$wpdb->insert( $db_tbl['journal'], $args ) ) die( $wpdb->print_error() );	
	} // f_mdjm_do_journal
	
?>