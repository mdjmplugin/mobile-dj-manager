<?php
/*
 * mdjm-functions.php
 * 17/03/2015
 * Contains all main MDJM functions used in front & back end
 * 
 */

/*
 * START GENERAL FUNCTIONS
 */
 	/*
	 * Return the admin URL for the given page
	 *
	 *
	 * 
	 * @params 	STR		$mdjm_page	Required: The page for which we want the URL
	 * 			str		$action		Optional: Whether to return as string (Default) or echo the URL.
	 * @returns $mdjm_page - str or echo
	 */
	function mdjm_get_admin_page( $mdjm_page, $action='str' )	{
		if( empty( $mdjm_page ) )
			return;
		
		$mydjplanner = array( 'mydjplanner', 'user_guides', 'mdjm_support', 'mdjm_forums' );
		$mdjm_pages = array(
						'wp_dashboard'          => 'index.php',
						'dashboard'             => 'admin.php?page=mdjm-dashboard',
						'settings'              => 'admin.php?page=mdjm-settings',
						'payment_settings'      => 'admin.php?page=mdjm-settings&tab=payments',
						'clientzone_settings'   => 'admin.php?page=mdjm-settings&tab=client-zone',
						'clients'               => 'admin.php?page=mdjm-clients',
						'employees'             => 'admin.php?page=mdjm-employees',
						'permissions'           => 'admin.php?page=mdjm-employees&tab=permissions',
						'inactive_clients'      => 'admin.php?page=mdjm-clients&display=inactive_client',
						'add_client'            => 'user-new.php',
						'edit_client'           => 'user-edit.php?user_id=',
						'comms'                 => 'admin.php?page=mdjm-comms',
						'email_history'         => 'edit.php?post_type=mdjm_communication',
						'contract'              => 'edit.php?post_type=contract',
						'signed_contract'	   => 'edit.php?post_type=mdjm-signed-contract',
						'add_contract'          => 'post-new.php?post_type=contract',
						'djs'                   => 'admin.php?page=mdjm-djs',
						'inactive_djs'          => 'admin.php?page=mdjm-djs&display=inactive_dj',
						'email_template'        => 'edit.php?post_type=email_template',
						'add_email_template'    => 'post-new.php?post_type=email_template',
						'equipment'             => 'admin.php?page=mdjm-packages',
						'events'                => 'edit.php?post_type=mdjm-event',
						'add_event'             => 'post-new.php?post_type=mdjm-event',
						'enquiries'             => 'edit.php?post_status=mdjm-enquiry&post_type=mdjm-event',
						'unattended'            => 'edit.php?post_status=mdjm-unattended&post_type=mdjm-event',
						'playlists'             => 'admin.php?page=mdjm-playlists&event_id=',
						'custom_event_fields'   => 'admin.php?page=mdjm-custom-event-fields',
						'venues'                => 'edit.php?post_type=mdjm-venue',
						'add_venue'             => 'post-new.php?post_type=mdjm-venue',
						'tasks'                 => 'admin.php?page=mdjm-tasks',
						'client_text'           => 'admin.php?page=mdjm-settings&tab=client-zone&section=mdjm_app_text',
						'client_fields'         => 'admin.php?page=mdjm-custom-client-fields',
						'availability'          => 'admin.php?page=mdjm-availability',
						'debugging'             => 'admin.php?page=mdjm-settings&tab=general&section=mdjm_app_debugging',
						'contact_forms'         => 'admin.php?page=mdjm-contact-forms',
						'transactions'		  => 'edit.php?post_type=mdjm-transaction',
						'updated'			   => 'admin.php?page=mdjm-updated',
						'about'			     => 'admin.php?page=mdjm-about',
						'mydjplanner'           => 'http://mdjm.co.uk',
						'user_guides'           => 'http://mdjm.co.uk/support/user-guides',
						'mdjm_support'          => 'http://mdjm.co.uk/support',
						'mdjm_forums'           => 'http://mdjm.co.uk/forums',
						);
		if( in_array( $mdjm_page, $mydjplanner ) )	{
			$mdjm_page = $mdjm_pages[$mdjm_page];	
		}
		else	{
			$mdjm_page = admin_url( $mdjm_pages[$mdjm_page] );
		}
		if( $action == 'str' )	{
			return $mdjm_page;	
		}
		else	{
			echo $mdjm_page;
			return;
		}
	} // mdjm_get_admin_page
	
	/*
	 * Display update notice within Admin UI
	 *
	 * @param	str		$class		Required: The admin notice class - updated | update-nag | error
	 *			str		$message	Required: Translated notice message
	 *			bool	$dismiss	Optional: true will make the notice dismissable. Default false.
	 *
	 */
	function mdjm_update_notice( $class, $message, $dismiss='' )	{
		$dismiss = ( !empty( $dismiss ) ? ' notice is-dismissible' : '' );
		
		echo '<div id="message" class="' . $class . $dismiss . '">';
		echo '<p>' . __( $message, 'mobile-dj-manager' ) . '</p>';
		echo '</div>';
	} // mdjm_update_notice

/*
 * -- START EVENT FUNCTIONS
 */
		
	
/*
 * -- END EVENT FUNCTIONS
 */
  
/*
 * -- START DJ FUNCTIONS
 */
	/*
	* mdjm_get_djs
	* 19/03/2015
	* Retrieve a list of all DJ's
	* 
	*	@since: 1.1.3
	*	@params:
	*	@returns: $djs => object
	*/
	function mdjm_get_djs( $role = 'dj' )	{
		return mdjm_get_employees(
			$role == 'dj' ? array( 'administrator', $role ) : $role
		);
	} // mdjm_get_djs
	
	/*
	* dj_can
	* 19/03/2015
	* Determine if the DJ is allowed to carry out the current action
	* 
	*	@since: 1.1.3
	*	@params: $task
	*	@returns: true : false
	*/
	function dj_can( $task )	{
		global $mdjm_settings;
		
		return isset( $mdjm_settings['permissions']['dj_' . $task] ) ? true : false;
	}
	
	/*
	* Determine if the current user is a DJ
	* 
	* @since: 	1.1.3
	* @params: 
	* @returns:	bool	true : false
	*/
	function is_dj( $user='' )	{
		if( !empty( $user ) && user_can( $user, 'dj' ) )
			return true;			
		
		if( current_user_can( 'dj' ) )
			return true;
		
		return false;
	} // is_dj
	
	/**
	 * Return all dates within the given range
	 * 
	 * @param	$str	$from_date		The start date Y-m-d
	 *			$str	$to_date		The end date Y-m-d
	 * 
	 * @return all dates between 2 given dates as an array
	 */
	function mdjm_all_dates_in_range( $from_date, $to_date )	{
		$from_date = \DateTime::createFromFormat( 'Y-m-d', $from_date );
		$to_date   = \DateTime::createFromFormat( 'Y-m-d', $to_date );
		return new \DatePeriod(
			$from_date,
			new \DateInterval( 'P1D' ),
			$to_date->modify( '+1 day' )
		);
	} // mdjm_all_dates_in_range
	
	/**
	 * Insert an employee holiday into the database
	 * 
	 * 
	 * @param	arr		$args	An array of information regarding the holiday
	 *							'from_date' Y-m-d
	 *							'to_date' Y-m-d
	 *							'employee' UserID
	 *							'notes' String with information re holiday
	 * 
	 */
	function mdjm_add_holiday( $args )	{
		global $wpdb;
		
		$date_range  = mdjm_all_dates_in_range( $args['from_date'], $args['to_date'] );
		$insert_args = array(
			'id'         => '',
			'user_id'    => $args['employee'],
			'entry_id'   => get_current_user_id() . '_' . time(),
			'date_from'  => $the_date->format( 'Y-m-d' ),
			'date_to'    => $args['to_date'],
			'notes'      => $args['notes'],
		);
		$insert_args = apply_filters( 'mdjm_add_holiday_args', $insert_args );

		do_action( 'mdjm_before_added_holiday', $args, $insert_args );

		foreach( $date_range as $the_date )	{
			$wpdb->insert( 
				MDJM_HOLIDAY_TABLE,
				$insert_args
			);
		}

		do_action( 'mdjm_added_holiday', $args, $insert_args );

		mdjm_update_notice( 'updated', 'The entry was added successfully' );	
	} // mdjm_add_holiday
	
	/**
	 * Remove an employee holiday entry from the database
	 * 
	 * 
	 * @param	int		$entry	The database ID for the entry
	 *
	 * 
	 */
	function mdjm_remove_holiday( $entry_id )	{
		global $wpdb;

		if ( empty( $entry_id ) )	{
			return mdjm_update_notice( 'error', 'Could not remove entry' );	
		}

		do_action( 'mdjm_before_remove_holiday', $entry_id );

		if ( $wpdb->delete( MDJM_HOLIDAY_TABLE, array( 'entry_id' => $entry_id, ) ) )	{
			do_action( 'mdjm_remove_holiday', $entry_id );
			mdjm_update_notice( 'updated', 'The entry was <strong>deleted</strong> successfully' );					
		} else	{
			mdjm_update_notice( 'error', 'Could not remove entry' );	
		}
	} // mdjm_remove_holiday
	
	/*
	 * Check the availability of the DJ('s) on the given date (Y-m-d)
	 *
	 * @param	int			$dj		Optional: The user ID of the DJ, if empty we'll check all
	 *			arr			$roles	Optional: If no $dj is set, we can check an array of role names
	 *			str|arr		$date	The date (Y-m-d) to check
	 *
	 * @return	arr			$status	array of user id's (['available'] | ['unavailable']
	 */
	function dj_available( $dj='', $roles='', $date='' )	{
		global $mdjm;
		
		MDJM()->debug->log_it( 'Check availability for ' . $date, true );
		
		$required_roles = array( 'administrator' );
		
		if( !empty( $GLOBALS['mdjm_settings']['availability']['availability_roles'] ) )
			$required_roles = array_merge( $required_roles, $GLOBALS['mdjm_settings']['availability']['availability_roles'] );
		
		// If no DJ is specified but roles are, retrieve all employees for the roles
		if( empty( $dj ) && !empty( $roles ) )
			$dj = mdjm_get_employees( $roles );
		
		$dj = !empty( $dj ) ? $dj : mdjm_get_employees( $required_roles );

		$date = !empty( $date ) ? $date : date( 'Y-m-d' );
		
		if( is_array( $dj ) )	{
			foreach( $dj as $employee )	{						
				$user[] = $employee->ID;
			}
		}
		else	{
			$user[] = $dj;
		}
		
		foreach( $user as $dj )	{
			if( MDJM()->events->employee_bookings( $dj, $date ) || is_on_holiday( $dj, $date ) )	{ // Unavailable
				$status['unavailable'][] = $dj;
			}
			else	{
				$status['available'][] = $dj;	
			}
		}
		return $status;
	} // dj_available
	
	/*
	 * Check if the DJ is on holiday during the given date
	 *
	 * @param	int			$dj			Optional: User ID of the DJ. Default to current user
	 *			str			$date		Optional: Date to check (Y-m-d), default to today
	 * @return	bool		true|false	false if the DJ is available, true if they are not
	 */
	function is_on_holiday( $dj='', $date='' )	{
		global $wpdb, $current_user;
		
		$dj = !empty( $dj ) ? $dj : $current_user->ID;
		
		$date = !empty( $date ) ? $date : date( 'Y-m-d' );
	
		$result = $wpdb->get_results( "SELECT * FROM " . MDJM_HOLIDAY_TABLE . " 
										WHERE DATE(date_from) = '" . $date . "' AND `user_id` = '" . $dj . "'" );
												
		if( !$result )
			return false; // DJ is available
			
		return true;
		
	} // is_on_holiday
	
	/*
	 * Retrieve Activity for the given period
	 *
	 * 
	 *
	 */
	function get_availability_activity( $month, $year )	{
		global $wpdb, $mdjm, $mdjm_settings, $current_user;
		
		if( $month == '12' )	{
			$next_month = '1';
			$mk_year = $year + 1;
		}
		else	{
			$next_month = $month + 1;
			$mk_year = $year;
		}
		if( date( 'Y-m', strtotime( $year . '-' . $month ) ) == date( 'Y-m' ) )	{
			$first_day = date( 'Y-m-d' );
			$last_day = date( 'Y-m-d', strtotime( '+1 month' ) );
		}
		else	{
			$first_day = date( 'Y-m-d', strtotime( $year . '-' . $month . '-01' ) );
			$last_day = date( 'Y-m-t', mktime( 0, 0, 0, $next_month, 0, $mk_year ) );
		}
		/* 7 Day Checker for the WP Widget */
		if( $month == 0 && $year == 0 )	{
			$first_day = date( 'Y-m-d' );
			$last_day = date( 'Y-m-d', strtotime( '+1 week' ) );
		}
		
		$date_range = mdjm_all_dates_in_range( $first_day, $last_day );
		
		$event_args = array(
						'posts_per_page'	=> -1,
						'post_type'			=> 'mdjm-event',
						'post_status'		=> array( 'mdjm-unattended',
													  'mdjm-enquiry',
													  'mdjm-contract',
													  'mdjm-approved',
													  'mdjm-completed' ),
						'orderby'			=> 'meta_value',
						'order'				=> 'ASC',
						);
		
		/* Loop through the days */
		foreach( $date_range as $day )	{
			if( mdjm_is_admin() )	{
				$event_args['meta_query'] = array(
												array( 
													'key'		=> '_mdjm_event_date',
													'value'  	=> $day->format( 'Y-m-d' ),
													'compare'	=> '=',
													'type'		=> 'date',
													),
												);
				
				$hol_query = "SELECT * FROM " . MDJM_HOLIDAY_TABLE . " WHERE DATE(date_from) = '" . $day->format( 'Y-m-d' ) . "'";
			}
			else	{
				$event_args['meta_query'] = array(
												'relation'	=> 'AND',
												array(
													'key'		=> '_mdjm_event_date',
													'value'  	=> $day->format( 'Y-m-d' ),
													'compare'	=> '=',
													'type'		=> 'date',
												),
												array( 
													'key'		=> '_mdjm_event_dj',
													'value'  	  => $current_user->ID,
													'compare'	=> '=',
												),
											);
				
				$hol_query = "SELECT * FROM " . MDJM_HOLIDAY_TABLE . " WHERE DATE(date_from) = '" . $day->format( 'Y-m-d' ) . "' AND `user_id` = '" . get_current_user_id() . "'";
			}
			/* Work Query */
			$work_result = get_posts( $event_args );
			
			/* Holiday Query */
			$hol_result = $wpdb->get_results( $hol_query );
			/* Print results */
			$result_array = array();
			if( count( $work_result ) > 0 || $hol_result )	{
				$event_stati = mdjm_all_event_status();
				$have_result = true;
				?>
				<tr class="alternate">
				<td colspan="2"><strong><font class="code"><?php echo date( 'l, jS F Y', strtotime( $day->format( 'Y-m-d' ) ) ); ?></font></strong></td>
				</tr>
                <?php
			}
			if( count( $work_result ) > 0 )	{
				foreach( $work_result as $event )	{
					$eventinfo = MDJM()->events->event_detail( $event->ID );
					?>
					<tr>
                    <td width="25%">
						<?php if( $month == 0 && $year == 0 ) echo '<font style="font-size:12px">'; ?>
                        <strong><?php echo ( !empty( $eventinfo['dj']->display_name ) ? $eventinfo['dj']->display_name : 'DJ ' . $eventinfo['dj'] ); ?></strong>
						<?php if( $month == 0 && $year == 0 ) echo '</font>'; ?></td>
					<td><?php if( $month == 0 && $year == 0 ) echo '<font style="font-size:12px">'; ?><a href="<?php echo get_edit_post_link( $event->ID ); ?>">Event ID <?php echo $event->ID . '</a> (' . $event_stati[$event->post_status] . ')'; ?> from <?php echo $eventinfo['start']; ?><?php if( $month != 0 && $year != 0 ) { ?> to <?php echo $eventinfo['finish']; } ?><?php if( $month == 0 && $year == 0 ) echo '</font>'; ?></td>
                    </tr>
                    <?php
				}
			}
			if( $hol_result )	{
				foreach( $hol_result as $holiday )	{
					$dj = get_userdata( $holiday->user_id );
					?>
					<tr>
                    <td width="25%"><?php if( $month == 0 && $year == 0 ) echo '<font style="font-size:12px">'; ?><strong><?php echo $dj->display_name; ?></strong><?php if( $month == 0 && $year == 0 ) echo '</font>'; ?></td>
					<td><?php if( $month == 0 && $year == 0 ) echo '<font style="font-size:12px">'; ?>Unavailable<?php if( isset( $holiday->notes ) && !empty( $holiday->notes ) &&$month != 0 && $year != 0 ) echo ' - ' . $holiday->notes; ?><?php if( $month == 0 && $year == 0 ) echo '</font>'; ?> <a style="color: #F00;" href="<?php mdjm_get_admin_page( 'availability', 'echo' ); ?>&action=del_entry&entry_id=<?php echo $holiday->entry_id; ?>">Delete Entry</a></td>
                    </tr>
                    <?php
				}
			}
		} // foreach( $date_range as $day )
		if( !isset( $have_result ) )	{
			if( $month != 0 && $year != 0 )	{
				?>
				<tr class="alternate">
				<td colspan="2"><strong>There is currently no activity during <?php echo date( 'F Y', strtotime( $year. '-' . $month . '-01' ) ); ?></strong></td>
				</tr>
				<?php
			}
			else	{
				?>
				<tr >
				<td colspan="2">There is currently no activity within the next 7 days</td>
				</tr>
				<?php
			}
		}
		
	} // get_availability_activity
	
/*
 * -- END DJ FUNCTIONS
 */
  
/*
 * -- START USER FUNCTIONS
 */
	/*
	 * Check if the given user has the given role
	 *
	 * @param	int		$user	Optional: User ID to check. Default to current user
	 *			str		$role	Required: The role to determine if the user has
	 *
	 * @return	bool			true if the user has the role, otherwise false
	 */
	function user_is( $user='', $role )	{
		if( !empty( $user ) && user_can( $user, $role ) )
			return true;			
		
		if( current_user_can( $role ) )
			return true;
		
		return false;
	} // user_is
	
	/**
	 * Toggle the users role between active/inactive
	 * This can be called via the link on the Client/DJ screen, or by a bulk action
	 *
	 * @param	str|arr		$users		If string, will be converted to array or User ID's to process
	 *			str			$role		The role to convert the user to
	 * @return
	 */
	function set_user_role( $users, $role )	{
		// If $users is not an array, make it one
		if( !is_array( $users ) ) 
			$users = array( $users );
			
		// Array of role names
		$role_name = array(
			'client'          => 'Active',
			'inactive_client' => 'Inactive',
			'dj'              => 'Active',
			'inactive_dj'     => 'Inactive' );
			
		$i = 0; // Counter
		// Loop through the $users array and adjust the roles
		foreach( $users as $user )	{
			$user_id = wp_update_user( array( 'ID' => $user, 'role' => $role ) );
			
			if ( is_wp_error( $user_id ) ) // Failed
				$user_error = true;
			else // Success
				$i++;
		}
		
		// Define the admin notice class and content
		if( !empty( $user_error ) && $i == 0 )	{
			$class = 'error';
			$message = sprintf( __( 'ERROR: %s were set as %s.%sContact 
				%sMDJM Support%s with details of any errors that are displayed on your screen.', 'mobile-dj-manager' ),
					_n( $i . ' user ', ' ' . $i . ' users ', $i, 'mobile-dj-manager' ),
					$role_name[$role],
					'<br />',
					'<a href="http://mdjm.co.uk/forums/forum/bugs/" target="_blank" title="Report this bug">',
					'</a>' );
		}
		elseif( !empty( $user_error ) && $i < $user_count )	{
			$class = 'update-nag';
			$message = sprintf( __( 'WARNING: Some errors occured and only %s out of %s %s were set as %s.', 'mobile-dj-manager' ),
				$i,
				count( $users ),
				_n( 'user', 'users', $i, 'mobile-dj-manager' ),
				$role_name[$role] );
		}
		else	{
			$class = 'updated';
			$message = sprintf( __( '%s %s successfully marked as %s.', 'mobile-dj-manager' ),
			$i,
			_n( 'user', 'users', $i, 'mobile-dj-manager' ),
			$role_name[$role] );
		}
		
		mdjm_update_notice( $class, $message, true );
	} // set_user_role
	
	/*
	 * Check if the given user is a client
	 *
	 * @param	int		$client Optional: User ID to check. Default to current user
	 *
	 */
	function is_client( $user='' )	{
		if( user_is( $user, 'client' ) )
			return true;
			
		return false;
	} // is_client
 
/**
 * -- END USER FUNCTIONS
 */
 
/**
 * -- START CUSTOM FIELD FUNCTIONS
 */
	/**
	 * Retrieve all custom fields for the relevant section of the event
	 *
	 * @param	str		$section	Optional: The section for which to retrieve the fields. If empty retrieve all
	 *			str		$orderby	Optional. Which field to order by. Default to menu order
	 *			str		$order		Optional. ASC or DESC. Default ASC
	 *			int		$limit		Optional: The number of results to return. Default -1 (all)
	 *
	 * @return	arr		$fields		The custom event fields
	 */
	function mdjm_get_custom_fields( $section = '', $orderby = 'menu_order', $order = 'ASC', $limit = -1 )	{
		// Retrieve fields for given $section and return as object
		if( !empty( $section ) )
			$custom_fields = new WP_Query(
								array(
									'posts_per_page'	=> $limit,
									'post_type'		 => 'mdjm-custom-fields',
									'post_status'  	   => 'publish',
									'meta_query'		=> array(
										'field_clause'	=> array(
											'key'	   => '_mdjm_field_section',
											'value'	 => $section ) ),
									'orderby'		   => array( 'field_clause' => $order, $orderby => $order ),
									'order'			 => $order ) );
		
		// Retrieve fields for all custom event fields return as object
		else	{
			$custom_fields = new WP_Query(
								array(
									'posts_per_page'	=> $limit,
									'post_type'		 => 'mdjm-custom-fields',
									'post_status'  	   => 'publish',
									'meta_query'		=> array(
										'field_clause'	=> array(
											'key'	   => '_mdjm_field_section' ) ),
									'orderby'		   => array( 'field_clause' => $order, $orderby => $order ),
									'order'			 => $order ) );
		
		}
		
		return $custom_fields;
	} // mdjm_get_custom_fields
	
/**
 * -- END CUSTOM FIELD FUNCTIONS
 */
