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
	 * mdjm_get_admin_page
	 * 18/03/2015
	 * Return the requested page URL
	 *
	 *	@since: 1.1.3
	 *	@called: Only from the admin UI
	 *	@params: $mdjm_page, $action (echo or str )
	 * 	@returns: $mdjm_page - str or echo
	 */
	function mdjm_get_admin_page( $mdjm_page, $action='' )	{
		if( empty( $mdjm_page ) )
			return;
		/* -- Always assume str -- */
		if( empty( $action ) )
			$action = 'str';
		
		$mydjplanner = array( 'mydjplanner', 'user_guides', 'mdjm_support', 'mdjm_forums' );
		$mdjm_pages = array(
						'wp_dashboard'          => 'index.php',
						'dashboard'             => 'admin.php?page=mdjm-dashboard',
						'settings'              => 'admin.php?page=mdjm-settings',
						'payment_settings'      => 'admin.php?page=mdjm-settings&tab=payments',
						'clientzone_settings'   => 'admin.php?page=mdjm-settings&tab=client-zone',
						'clients'               => 'admin.php?page=mdjm-clients',
						'inactive_clients'      => 'admin.php?page=mdjm-clients&display=inactive_client',
						'add_client'            => 'user-new.php',
						'edit_client'           => 'user-edit.php?user_id=',
						'comms'                 => 'admin.php?page=mdjm-comms',
						'email_history'         => 'edit.php?post_type=' . MDJM_COMM_POSTS,
						'contract'              => 'edit.php?post_type=' . MDJM_CONTRACT_POSTS,
						'signed_contract'		=> 'edit.php?post_type=' . MDJM_SIGNED_CONTRACT_POSTS,
						'add_contract'          => 'post-new.php?post_type=' . MDJM_CONTRACT_POSTS,
						'djs'                   => 'admin.php?page=mdjm-djs',
						'inactive_djs'          => 'admin.php?page=mdjm-djs&display=inactive_dj',
						'email_template'        => 'edit.php?post_type=' . MDJM_EMAIL_POSTS,
						'add_email_template'    => 'post-new.php?post_type=' . MDJM_EMAIL_POSTS,
						'equipment'             => 'admin.php?page=mdjm-packages',
						'events'                => 'edit.php?post_type=' . MDJM_EVENT_POSTS,
						'add_event'             => 'post-new.php?post_type=' . MDJM_EVENT_POSTS,
						'enquiries'             => 'edit.php?post_status=mdjm-enquiry&post_type=' . MDJM_EVENT_POSTS,
						'unattended'            => 'edit.php?post_status=mdjm-unattended&post_type=' . MDJM_EVENT_POSTS,
						'playlists'             => 'admin.php?page=mdjm-playlists&event_id=',
						'music_library'         => 'admin.php?page=mdjm-music',
						'venues'                => 'edit.php?post_type=' . MDJM_VENUE_POSTS,
						'add_venue'             => 'post-new.php?post_type=' . MDJM_VENUE_POSTS,
						'tasks'                 => 'admin.php?page=mdjm-tasks',
						'client_text'           => 'admin.php?page=mdjm-settings&tab=client-zone&section=mdjm_app_text',
						'client_fields'         => 'admin.php?page=mdjm-settings&tab=client-zone&section=mdjm_client_field_settings',
						'availability'          => 'admin.php?page=mdjm-availability',
						'debugging'             => 'admin.php?page=mdjm-settings&tab=general&section=mdjm_app_debugging',
						'contact_forms'         => 'admin.php?page=mdjm-contact-forms',
						'transactions'		  => 'edit.php?post_type=' . MDJM_TRANS_POSTS,
						'updated'			   => 'admin.php?page=mdjm-updated',
						'mydjplanner'           => 'http://www.mydjplanner.co.uk',
						'user_guides'           => 'http://www.mydjplanner.co.uk/support/user-guides',
						'mdjm_support'          => 'http://www.mydjplanner.co.uk/support',
						'mdjm_forums'           => 'http://www.mydjplanner.co.uk/forums',
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
	
	/**
	 * Log the access to the specified page
	 *
	 * @param	str		Required: $page 	Name of the page
	 *			
	 * @return
	 * @since	1.2.3.5
	 * 
	 */
	function mdjm_page_visit( $page )	{
		global $current_user;
		
		if( MDJM_DEBUG == false )
			return;
		
		if( empty( $page ) || !is_user_logged_in() )
			return;
		
		$content = "\r\n";
		$content .= '------------------------------------------------------' . "\r\n";
		$content .= date( 'd/m/Y  H:i:s', current_time( 'timestamp' ) ) . ' : ';
		$content .= $page . ' accessed by ' . $current_user->display_name . ' (' . $current_user->ID . ')' . "\r\n";
		$content .= '------------------------------------------------------' . "\r\n";
		
		$GLOBALS['mdjm_debug']->log_it( $content );
		
	} // mdjm_page_visit
	
	/*
	 * Display update notice within Admin UI
	 *
	 *
	 *
	 */
	function mdjm_update_notice( $class, $message, $dismiss='' )	{
		$dismiss = ( !empty( $dismiss ) ? ' notice is-dismissible' : '' );
		
		echo '<div id="message" class="' . $class . $dismiss . '">';
		echo '<p>' . __( $message ) . '</p>';
		echo '</div>';
	} // mdjm_update_notice
	
	/*
	* mdjm_jquery_short_date
	* 19/03/2015
	* Transform the preferred date format into jQuery format
	* 
	*	@since: 1.1.3
	*	@called:
	*	@params:
	*	@returns: $date_format
	*/
	function mdjm_jquery_short_date()	{
		global $mdjm_settings;
		
		$date_format = isset( $mdjm_settings['main']['short_date_format'] ) ? $mdjm_settings['main']['short_date_format'] : 'd/m/Y';
		
		$search = array( 'd', 'm', 'Y' );
		$replace = array( 'dd', 'mm', 'yy' );
		
		$date_format = str_replace( $search, $replace, $date_format );
				
		return $date_format;
		
	} // mdjm_jquery_short_date
	
	/*
	* mdjm_jquery_datepicker_script
	* 19/03/2015
	* Insert the datepicker jQuery code
	* 
	*	@since: 1.1.3
	*	@called:
	*	@params: 	$args =>array
	*			 	[0] = class name
	*			 	[1] = alternative field name (hidden)
	*				[2] = maximum # days from today which can be selected
	*
	*	@defaults:	[0] = mdjm_date
	*				[1] = _mdjm_event_date
	*				[2] none
	*
	*	@returns:
	*/
	function mdjm_jquery_datepicker_script( $args='' )	{
		$class = !empty ( $args[0] ) ? $args[0] : 'mdjm_date';
		$altfield = !empty( $args[1] ) ? $args[1] : '_mdjm_event_date';
		$maxdate = !empty( $args[2] ) ? $args[2] : '';
		
		if( empty( $class ) || empty( $altfield ) )
			return;
		
		echo "jQuery(document).ready(function($) {\r\n" . 
		"	$('." . $class . "').datepicker({\r\n" . 
		"	dateFormat : '" . mdjm_jquery_short_date() . "',\r\n" . 
		"   altField : '#" . $altfield . "',\r\n" . 
		"	altFormat : 'yy-mm-dd',\r\n" . 
		"   firstDay: " . get_option( 'start_of_week' ) . ",\r\n" . 
		"   changeYear: true,\r\n" . 
		"   changeMonth: true,\r\n" . 
		"   " . ( !empty( $maxdate ) ? "maxDate: '" . ( $maxdate == 'today' ? '0' : $maxdate ) . "',\r\n" : '' ) . 
		"	});" . "\r\n" . 
		"});" . "\r\n";
	} // mdjm_jquery_datepicker_script
	
	/*
	* mdjm_set_currency
	* 19/03/2015
	* The currency symbol in use
	* 
	*	@since: 1.1.3
	*	@called:
	*	@params: 	$currency (ISO Code)
	*	@returns:	$symbols
	*/
	function mdjm_set_currency( $currency )	{
		$currency = !empty( $currency ) ? $currency : 'GBP';
		$symbols = array(
						'EUR' => '&euro;',
						'GBP' => '&pound;',
						'USD' => '$',
						'BRL' => '&#x52;&#x24;',
						'CHF' => 'CHF',
						'CZK' => '&#x4b;&#x10d;',
						'DKK' => 'kr',
						'ZAR' => 'R',
						'TRL' => '&#x20a4;'
						);
		return $symbols[$currency];	
	} // mdjm_set_currency
	
	/*
	 * Displays the price in the selected format per settings
	 * basically determining where the currency symbol is displayed
	 *
	 * @param	str		$amount		The price to to display
	 * 			bool	$symbol		true to display currency symbol (default)
	 * @return	str					The formatted price with currency symbol
	 */
	function display_price( $amount, $symbol=true )	{
		global $mdjm_settings;
		
		if( empty( $amount ) || !is_numeric( $amount ) )
			$amount = '0.00';
		
		$symbol = ( isset( $symbol ) ? $symbol : true );
		
		$dec = $mdjm_settings['payments']['decimal'];
		$tho = $mdjm_settings['payments']['thousands_seperator'];
		
		// Currency before price
		if( $mdjm_settings['payments']['currency_format'] == 'before' )
			return ( !empty( $symbol ) ? MDJM_CURRENCY : '' ) . number_format( $amount, 2, $dec, $tho );
		
		// Currency before price with space
		elseif( $mdjm_settings['payments']['currency_format'] == 'before with space' )
			return ( !empty( $symbol ) ? MDJM_CURRENCY . ' ' : '' ) . number_format( $amount, 2, $dec, $tho );
			
		// Currency after price
		elseif( $mdjm_settings['payments']['currency_format'] == 'after' )
			return number_format( $amount, 2, $dec, $tho ) . ( !empty( $symbol ) ? MDJM_CURRENCY : '' );
			
		// Currency after price with space
		elseif( $mdjm_settings['payments']['currency_format'] == 'after with space' )
			return number_format( $amount, 2, $dec, $tho ) . ' ' . ( !empty( $symbol ) ? MDJM_CURRENCY : '' );
		
		// Default	
		return ( !empty( $symbol ) ? MDJM_CURRENCY : '' ) . number_format( $amount, 2, $dec, $tho );
		
	} // display_price

/*
 * -- START GENERAL POST FUNCTIONS
 */
	/*
	 * recent_posts
	 * 25/03/2015
	 * Returns the specified number of recent posts for the current event
	 *
	 *	@since: 1.1.3
	 *	@called: Inside the loop
	 *	@params: $type - the post type we are searching
	 * 			 $num => int how many to retrieve DEFAULT 3
	 * 	@returns: $recent => array of posts data
	 */
	function recent_posts( $type='', $num = '' )	{
		global $post;
		
		if( empty( $post ) || empty( $type ) )
			return;
			
		$recent = wp_get_recent_posts( array( 
										'numberposts'	=> ( !empty( $num ) ? $num : 3 ),
										'orderby'		=> 'post_date',
										'order'		  => 'DESC',
										'meta_key'	   => '_event',
										'meta_value'	 => $post->ID,
										'post_type'	  => $type,
										'suppress_filters' => true,
										)
									);
		return $recent;
	} // recent_posts
/*
 * -- END GENERAL POST FUNCTIONS
 */
 
/*
 * -- START EVENT FUNCTIONS
 */
 	/*
	* mdjm_event_by_id
	* 17/03/2015
	* Get the event details from the given ID
	* 
	*	@since: 1.1.3
	*	@called: Only from within the MDJM_Events class
	*	@params: $event_id
	*	@returns: $event_details => object
	*/
	function mdjm_event_by_id( $event_id )	{
		if( empty( $event_id ) )
			return;
			
		/* -- Utilise the MDJM_Events class -- */
		$event_details = mdjm_event_by( 'ID', $event_id );
		
		return $event_details;
	} // mdjm_event_by_id
	
	/*
	* get_event_stati
	* 19/03/2015
	* Returns the possible event statuses together with their associated post status
	* 
	*	@since: 1.1.3
	*	@called: 
	*	@params: 
	*	@returns: $event_stati => object sorted aphabetically
	*/
	function get_event_stati()	{
		$mdjm_event_stati = array( 'mdjm-unattended',
								   'mdjm-enquiry',
								   'mdjm-approved',
								   'mdjm-contract',
								   'mdjm-completed',
								   'mdjm-cancelled',
								   'mdjm-rejected',
								   'mdjm-failed' );
		
		foreach( $mdjm_event_stati as $status )	{
			$event_stati[$status] = get_post_status_object( $status )->label;
		}
		asort( $event_stati );
		
		return $event_stati;
	}
	/*
	* event_stati_dropdown
	* 19/03/2015
	* Displays a drop down list of all possible event statuses
	* 
	*	@since: 1.1.3
	*	@called:
	*	@params: $args => array
	*				(Required)
	*				'name' = the name of the select list
	*				(Optional)
	*				'id' = the id of the select list (default to name')
	*				'selected' = the item to be selected
	*				'first_entry' = the first entry in the drop down list
	*				'first_entry_value' = the value of the first entry in the drop down list
	*				'small' = true: small font style
	*				'return_type' = list: return the outputted select list | str: return as an array (default to list)
	*	@returns: $event_stati_dropdown = HTML for the select list
	*/
	function event_stati_dropdown( $args='' )	{
		global $mdjm, $post;
				
		if( empty( $args['name'] ) )	{
			if( MDJM_DEBUG == true )
				 $mdjm->debug_logger( 'The `name` argument does not exist ' . __FUNCTION__, true );
			
			return false;
		}
		
		$event_stati = get_event_stati();
		if( empty( $event_stati ) )	{
			if( MDJM_DEBUG == true )
				 $mdjm->debug_logger( 'No statuses returned ' . __FUNCTION__, true );
			
			return false;
		}
		if( !empty( $post->ID ) && array_key_exists( $post->post_status, $event_stati ) )
			$current_status = $post->post_status;
					
		$select_id = !empty( $args['id'] ) ? $args['id'] : $args['name'];
		$selected = !empty( $args['selected'] ) ? $args['selected'] : '';

		$first_entry = !empty( $args['first_entry'] ) ? $args['first_entry'] : '';
		$first_entry_value = !empty( $args['first_entry_value'] ) ? $args['first_entry_value'] : '';
		$return_type = !empty( $args['return_type'] ) ? $args['return_type'] : 'list';
		
			
		$selected = !empty( $current_status ) ? $current_status : 'mdjm-unattended';
		
		$event_stati_dropdown = '<select name="' . $args['name'] . '" id="' . $select_id . '"';
		$event_stati_dropdown .= ( !empty( $args['small'] ) ? ' style="font-size: 11px;"' : '' );
		$event_stati_dropdown .= '>' . "\r\n";
		
		if( !empty( $first_entry ) )
			$event_stati_dropdown .= '<option value="' . $first_entry_value . '">' . $first_entry . '</option>' . "\r\n";
		
		foreach( $event_stati as $slug => $label )	{
			$event_stati_dropdown .= '<option value="' . $slug . '"';
			$event_stati_dropdown .= !empty( $selected ) && $selected == $slug ? ' selected="selected"' : '';
			$event_stati_dropdown .= '>' . $label . '</option>' . "\r\n";	
		}
		
		$event_stati_dropdown .= '</select>' . "\r\n";
		
		if( $return_type == 'list' )
			echo $event_stati_dropdown;

		return $event_stati_dropdown;
	} // event_stati_dropdown
	
	/*
	 * Determine the event deposit value based upon event cost and
	 * payment settings
	 *
	 * @param:		cost	str		Required: Current cost of event
	 */
	function get_deposit( $cost='' )	{
		global $mdjm_settings;
		
		// If no event cost is provided then we return 0
		if( empty( $cost ) )	{
			if( MDJM_DEBUG == true )
				$GLOBALS['mdjm_debug']->log_it( 'No cost provided for event in ' . __FUNCTION__, true );
			$deposit = '0.00';
		}
		
		// If we don't need a deposit per settings, return 0
		if( empty( $mdjm_settings['payments']['deposit_type'] ) )
			$deposit = '0.00';
		
		// Set fixed deposit amount
		elseif( $mdjm_settings['payments']['deposit_type'] == 'fixed' )
			$deposit = number_format( $mdjm_settings['payments']['deposit_amount'], 2 );
		
		// Set deposit based on % of total cost
		elseif( $mdjm_settings['payments']['deposit_type'] == 'percentage' )	{
			$percentage = $mdjm_settings['payments']['deposit_amount']; // The % to apply
			
			$deposit = ( !empty( $cost ) && $cost > 0 ? round( $percentage * ( $cost / 100 ), 2 ) : '0.00' );
		}
		
		return $deposit;
		
	} // get_deposit
	
/*
 * -- END EVENT FUNCTIONS
 */
 
/*
 * -- START CLIENT FUNCTIONS
 */
 
/*
 * -- END CLIENT FUNCTIONS
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
	function mdjm_get_djs()	{
		$admin_arg = array( 'role' => 'administrator',
							'orderby' => 'display_name',
							'order' => 'ASC'
						);
		$admin = get_users( $admin_arg );
		
		if( MDJM_MULTI == true )	{
			$dj_arg = array(	'role' => 'dj',
								'orderby' => 'display_name',
								'order' => 'ASC'
							);
			$dj = get_users( $dj_arg );
			$djs = array_merge( $admin, $dj );
		}
		else	{
			$djs = $admin;	
		}
		
		return $djs;
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
	
	/*
	 * Check the availability of the DJ('s) on the given date (Y-m-d)
	 *
	 * @param	int			$dj		The user ID of the DJ, if empty we'll check all
	 *			str|arr		$date	The date (Y-m-d) to check
	 * @return	arr			$status	array of user id's (['available'] | ['unavailable']
	 */
	function dj_available( $dj='', $date='' )	{
		global $mdjm;
		
		$mdjm->debug_logger( 'Check availability for ' . $date, true );
		
		$dj = !empty( $dj ) ? $dj : mdjm_get_djs();
		
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
			if( $mdjm->mdjm_events->employee_bookings( $dj, $date ) || is_on_holiday( $dj, $date ) )	{ // Unavailable
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
		global $wpdb;
		
		global $current_user;
		
		$dj = !empty( $dj ) ? $dj : $curren_user->ID;
		
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
		
		$date_range = f_mdjm_all_dates_in_range( $first_day, $last_day );
		
		$event_args = array(
						'posts_per_page'	=> -1,
						'post_type'			=> MDJM_EVENT_POSTS,
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
			if( current_user_can( 'administrator' ) )	{
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
				$event_stati = get_event_stati();
				$have_result = true;
				?>
				<tr class="alternate">
				<td colspan="2"><strong><font class="code"><?php echo date( 'l, jS F Y', strtotime( $day->format( 'Y-m-d' ) ) ); ?></font></strong></td>
				</tr>
                <?php
			}
			if( count( $work_result ) > 0 )	{
				foreach( $work_result as $event )	{
					$eventinfo = $mdjm->mdjm_events->event_detail( $event->ID );
					//$dj = get_userdata( $event->event_dj );
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
					<td><?php if( $month == 0 && $year == 0 ) echo '<font style="font-size:12px">'; ?>Unavailable<?php if( isset( $holiday->notes ) && !empty( $holiday->notes ) &&$month != 0 && $year != 0 ) echo ' - ' . $holiday->notes; ?><?php if( $month == 0 && $year == 0 ) echo '</font>'; ?> <a style="color: #F00;" href="<?php mdjm_get_admin_page( 'availability', 'echo' ); ?>&action=del_entry&entry=<?php echo $holiday->id; ?>">Delete Entry</a></td>
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
 * -- START PACKAGE/ADDON FUNCTIONS
 */
	/*
	 * Get the package information for the given event
	 *
	 * @param	int			$event_id	The event ID
	 * @return
	 */
	function get_event_package( $event_id, $price=false )	{
		if( MDJM_PACKAGES != true )
			return 'N/A';
		
		// Event package
		$event_package = get_post_meta( $event_id, '_mdjm_event_package', true );
		
		if( empty( $event_package ) )
			return 'No package is assigned to this event';
		
		// All packages
		$packages = get_option( 'mdjm_packages' );
						
		
		return stripslashes( esc_attr( $packages[$event_package]['name'] ) ) . ( !empty( $price ) ? ' ' . 
			display_price( $packages[$event_package]['cost'], true ) : '' );
				
	} // get_event_package
	
	/*
	 * Get the package information
	 *
	 * @param	int			$dj			Optional: The user ID of the DJ
	 * @return
	 */
	function get_available_packages( $dj='', $price=false )	{
		if( MDJM_PACKAGES != true )
			return 'N/A';
		
		// All packages
		$packages = get_option( 'mdjm_packages' );
		
		if( !empty( $packages ) )	{
			foreach( $packages as $package )	{
				if( !isset( $package['enabled'] ) || $package['enabled'] != 'Y' )
					continue;
				
				if( !empty( $dj ) )	{
					if( in_array( $dj, explode( ',', $package['djs'] ) ) )
						$available[] = stripslashes( esc_attr( $package['name'] ) ). ( !empty( $price ) ? ' ' . display_price( $package['cost'], true ) : '' );
				}
				else
					$available[] = stripslashes( esc_attr( $package['name'] ) ) . ( !empty( $price ) ? ' ' . display_price( $package['cost'], true ) : '' );
			}
			$i = 1;
			$the_packages = '';
			if( !empty( $available ) )	{
				foreach( $available as $avail )	{
					$the_packages .= $avail . ( $i < count( $available ) ? '<br />' : '' );
					$i++;
				}
			}
		}
		return ( !empty( $the_packages ) ? $the_packages : __( 'No packages available', 'mobile-dj-manager' ) );
				
	} // get_available_packages
	
	/*
	 * Get the addon information for the given event
	 *
	 * @param	int			$event_id	The event ID
	 * @return	arr|bool	$addons		array with package details, or false if no package assigned
	 */
	function get_event_addons( $event_id, $price=false )	{
		if( MDJM_PACKAGES != true )
			return 'N/A';
		
		// Event Addons
		$event_addons = get_post_meta( $event_id, '_mdjm_event_addons', true );
				
		if( empty( $event_addons ) )
			return __( 'No addons are assigned to this event', 'mobile-dj-manager' );
			
		// All addons
		$all_addons = get_option( 'mdjm_equipment' );
		
		$addons = '';
		$i = 1;
		
		foreach( $event_addons as $event_addon )	{
			$addons .= stripslashes( esc_attr( $all_addons[$event_addon][0] ) ) . ( !empty( $price ) ? ' ' . 
				display_price( $all_addons[$event_addon][7], true ) : '' ) . ( $i < count( $event_addons ) ? 
				'<br />' : '' );
			$i++;
		}
										
		return $addons;
				
	} // get_event_addons
	
	/*
	 * Get the addons available
	 *
	 *
	 * @param	int			$dj			Optional: The user ID of the DJ
	 *			str			$package	Optional: The slug of a package where the package contents need to be excluded
	 * @return
	 */
	function get_available_addons( $dj='', $package='' )	{
		if( MDJM_PACKAGES != true )
			return 'N/A';
									
		// All addons
		$all_addons = get_option( 'mdjm_equipment' );
		
		if( empty( $all_addons ) )
			return __( 'No addons are available', 'mobile-dj-manager' );
		
		$addons = array();
		
		foreach( $all_addons as $all_addon )	{
			// If the addon is not enabled, do not display
			if( !isset( $all_addon[6] ) || $all_addon[6] != 'Y' )
				continue;
			
			// If a package is parsed, remove the package items from the available addons
			if( !empty( $package ) )	{
				$packages = get_option( 'mdjm_packages' );
				$current_items = explode( ',', $packages[$package]['equipment'] );
				
				if( !empty( $current_items ) && in_array( $all_addon[1], $current_items ) )
					continue;
			}
			
			// If a DJ is parsed, only show their available addons
			if( !empty( $dj ) && !in_array( $dj, explode( ',', $all_addon[8] ) ) )
				continue;

			$addons[$all_addon[1]]['cat'] = '';
			$addons[$all_addon[1]]['slug'] = $all_addon[1];
			$addons[$all_addon[1]]['name'] = stripslashes( esc_attr( $all_addon[0] ) );
			$addons[$all_addon[1]]['cost'] = $all_addon[7];
			$addons[$all_addon[1]]['desc'] = stripslashes( esc_textarea( $all_addon[4] ) );
		}
										
		return $addons;
				
	} // get_available_addons
	
	/*
	 * Retrieve the package name
	 *
	 * @param	str		$slug		Slug name of the package
	 * @return	str		$package	The display name of the package	
	 *
	 */
	function get_package_name( $slug )	{
		if( empty( $slug ) )
			return false;
		
		$packages = get_option( 'mdjm_packages' );
		
		if( empty( $packages[$slug] ) || empty( $packages[$slug]['name'] ) )
			return false;
		
		$package = stripslashes( esc_attr( $packages[$slug]['name'] ) );
		
		return $package;
		
	} // get_package_name
	
	/*
	 * Retrieve the package name, description, cost
	 *
	 * @param	str		$slug		Slug name of the package
	 *		
	 *
	 */
	function get_package_details( $slug )	{
		if( empty( $slug ) )
			return false;
		
		$packages = get_option( 'mdjm_packages' );
		
		if( empty( $packages[$slug] ) )
			return false;
		
		$package['slug'] = $slug;
		$package['name'] = stripslashes( esc_attr( $packages[$slug]['name'] ) );
		$package['desc'] = stripslashes( esc_textarea( $packages[$slug]['desc'] ) );
		$package['equipment'] = $packages[$slug]['equipment'];
		$package['cost'] = $packages[$slug]['cost'];
		
		return $package;
		
	} // get_package_details
	
	/*
	 * Output HTML code for Package dropdown
	 *
	 * @param	arr		$settings		Settings for the dropdown
	 *									'name'				Optional: The name of the input. Defaults to '_mdjm_event_package'
	 *									'id'				Optional: ID for the field (uses name if not present)
	 *									'class'				Optional: Class of the input field
	 *									'selected'			Optional: Initially selected option
	 *									'first_entry'		Optional: First entry to be displayed (default none)
	 *									'first_entry_val'	Optional: First entry value
	 *									'dj'				Optional: The ID of the DJ to present package for (default current user)
	 *									'title'				Optional: Add package description to the title element of each option
	 *									'cost'				Optional: Display the price of the package (default true)
	 *					$structure		bool				true create the select list, false just return values
	 * @ return	HTML output for select field
	 */
	function mdjm_package_dropdown( $settings='', $structure=true )	{
		global $current_user;
		
		$packages = get_option( 'mdjm_packages' );
				
		// Set the values based on the array passed
		$select_name = isset( $settings['name'] ) ? $settings['name'] : '_mdjm_event_package';
		$select_id = isset( $settings['id'] ) ? $settings['id'] : $select_name;
		$select_dj = ( !empty( $settings['dj'] ) ? $settings['dj'] : ( is_user_logged_in() ? $current_user->ID : '' ) );
		$select_cost = ( isset( $settings['cost'] ) ? $settings['cost'] : true );
		
		$mdjm_select = '';
		
		if( $structure == true )	{
			$mdjm_select = '<select name="' . $select_name . '" id="' . $select_id . '"';
			$mdjm_select .= isset( $settings['class'] ) ? ' class="' . $settings['class'] . '"' : '';
			$mdjm_select .= '>' . "\r\n";
		}
		
		// First entry
		$mdjm_select .= isset( $settings['first_entry'] ) && !empty( $settings['first_entry'] ) ? 
			'<option value="' . ( isset( $settings['first_entry_val'] ) ? $settings['first_entry_val'] : '0' ) . '">' . 
			$settings['first_entry'] . '</option>' . "\r\n" : '';
			
		$packages = get_option( 'mdjm_packages' );
		
		if( empty( $packages ) )
			$mdjm_select .= '<option value="0">' . __( 'No Packages Available', 'mobile-dj-manager' ) . '</option>' . "\r\n";
		
		else	{
		// All packages
			foreach( $packages as $package )	{
				// If the package is not enabled, do not show it
				if( empty( $package['enabled'] ) || $package['enabled'] != 'Y' )
					continue;
				
				// If the specified DJ does not have the package, do not show it
				if( !empty( $select_dj ) )	{	
					$djs_have = explode( ',', $package['djs'] );
					
					if( !in_array( $select_dj, $djs_have ) )
						continue;
				}
				
				$mdjm_select .= '<option value="' . $package['slug'] . '"';
				$mdjm_select .= ( !empty( $settings['title'] ) && !empty( $package['desc'] ) ? ' title="' . stripslashes( esc_textarea( $package['desc'] ) ) . '"' : '' );
				$mdjm_select .= ( isset( $settings['selected'] ) ? selected( $settings['selected'], $package['slug'], false ) . '>' : '>' ) ;
				$mdjm_select .= stripslashes( esc_attr( $package['name'] ) ) . 
					( $select_cost == true ? ' - ' . display_price( $package['cost'] ) : '' ) . '</option>' . "\r\n";
			}
		}
		
		if( $structure == true )
			$mdjm_select .= '</select>' . "\r\n";
		
		return $mdjm_select;
			
	} // mdjm_package_dropdown
	
	/*
	 * Retrieve the addon name
	 *
	 * @param	str		$slug	The slug name of the addon
	 * @return	str		$addon	The display name of the addon
	 */
	function get_addon_name( $slug )	{
		if( empty( $slug ) )
			return false;
				
		$equipment = get_option( 'mdjm_equipment' );
		
		if( empty( $equipment[$slug] ) || empty( $equipment[$slug][0] ) )
			return false;
			
		$addon = stripslashes( esc_attr( $equipment[$slug][0] ) );
		
		return $addon;
		
	} // get_addon_name
	
	/*
	 * Retrieve the addon category, name, decription & cost
	 *
	 *
	 *
	 */
	function get_addon_details( $slug )	{
		if( empty( $slug ) )
			return false;
			
		$cats = get_option( 'mdjm_cats' );
		
		$equipment = get_option( 'mdjm_equipment' );
		
		if( empty( $equipment[$slug] ) )
			return false;
			
		$addon['slug'] = $slug;
		$addon['cat'] = stripslashes( esc_attr( $cats[$equipment[$slug][5]] ) );
		$addon['name'] = stripslashes( esc_attr( $equipment[$slug][0] ) );
		$addon['desc'] = stripslashes( esc_textarea( $equipment[$slug][4] ) );
		$addon['cost'] = $equipment[$slug][7];
		
		return $addon;
		
	} // get_addon_details
	
	/*
	 * Output HTML code for Addons multiple select dropdown
	 *
	 * @param	arr		$settings		Settings for the dropdown
	 *									'name'				Optional: The name of the input. Defaults to 'event_addons'
	 *									'id'				Optional: ID for the field (uses name if not present)
	 *									'class'				Optional: Class of the input field
	 *									'selected'			Optional: ARRAY of initially selected option
	 *									'first_entry'		Optional: First entry to be displayed (default none)
	 *									'first_entry_val'	Optional: First entry value
	 *									'dj'				Optional: The ID of the DJ to present package for (default current user)
	 *									'package'			Optional: Package slug for which to exclude addons if they exist in that package
	 *									'title'				Optional: Add addon description to the title element of each option
	 *									'cost'				Optional: Display the price of the package (default true)
	 *					$structure		bool				true create the select list, false just return values
	 * @ return	HTML output for select field
	 */
	function mdjm_addons_dropdown( $settings='', $structure=true )	{
		global $current_user;
		
		// Set the values based on the array passed
		$select_name = isset( $settings['name'] ) ? $settings['name'] : 'event_addons';
		$select_id = isset( $settings['id'] ) ? $settings['id'] : $select_name;
		$select_dj = ( !empty( $settings['dj'] ) ? $settings['dj'] : ( is_user_logged_in() ? $current_user->ID : '' ) );
		$select_cost = isset( $settings['cost'] ) ? $settings['cost'] : true;
		
		$mdjm_select = '';
		
		if( $structure == true )	{
			$mdjm_select .= '<select name="' . $select_name . '[]" id="' . $select_id . '"';
			$mdjm_select .= isset( $settings['class'] ) ? ' class="' . $settings['class'] . '"' : '';
			$mdjm_select .= ' multiple="multiple">' . "\r\n";
		}
		
		// First entry
		$mdjm_select .= isset( $settings['first_entry'] ) ? 
			'<option value="' . isset( $settings['first_entry_val'] ) ? $settings['first_entry_val'] : '0' . '">' . 
			$settings['first_entry'] . '</option>' . "\r\n" : '';
		
		$equipment = get_option( 'mdjm_equipment' );
		
		if( empty( $equipment ) )
			$mdjm_select .= '<option value="0">' . __( 'No Addons Available', 'mobile-dj-manager' ) . '</option>' . "\r\n";
		
		else	{
			asort( $equipment );
		// All addons
			$cats = get_option( 'mdjm_cats' );
			if( !empty( $cats ) )
				asort( $cats );
			
			foreach( $cats as $cat_key => $cat_value )	{
				if( !empty( $header ) )
					$mdjm_select .= '</optgroup>' . "\r\n";
				
				$header = false;
				
				// Create an array of options grouped by category
				foreach( $equipment as $item )	{
					// If the addon is not enabled, do not show it
					if( empty( $item[6] ) || $item[6] != 'Y' )
						continue;
						
					// If the addon is part of an assigned package, exlude it
					if( !empty( $settings['package'] ) )	{
						$packages = get_option( 'mdjm_packages' );
						$package_items = explode( ',', $packages[$settings['package']]['equipment'] );
						
						if( !empty( $package_items ) && in_array( $item[1], $package_items ) )
							continue;	
					}
					
					// If the specified DJ does not have the addon, do not show it	
					if( !empty( $select_dj ) )	{
						$djs_have = explode( ',', $item[8] );
						
						if( !in_array( $select_dj, $djs_have ) )
							continue;
					}
					
					if( $item[5] == $cat_key )	{
						if( empty( $header ) )	{
							$mdjm_select .= '<optgroup label="' . $cat_value . '">' . "\r\n";
							$header = true;
						}
							
							$mdjm_select .= '<option value="' . $item[1] . '"';
							$mdjm_select .= ( !empty( $settings['title'] ) && !empty( $item[4] ) ? ' title="' . stripslashes( esc_textarea( $item[4] ) ) . '"' : '' );
							
							if( !empty( $settings['selected'] ) && in_array( $item[1], $settings['selected'] ) )
								$mdjm_select .= ' selected="selected"';
							
							$mdjm_select .= '>' . stripslashes( esc_attr( $item[0] ) ) . 
								( $select_cost == true ? ' - ' . display_price( $item[7] ) : '' ) . '</option>' . "\r\n";
					}
					
				}
			}
		}
		
		if( $structure == true )
			$mdjm_select .= '</select>' . "\r\n";
		
		return $mdjm_select;
			
	} // mdjm_addons_dropdown
	
	/*
	 * Output HTML code for Addons checkbox list
	 *
	 * @param	arr		$settings		Settings for the dropdown
	 *									'name'				Optional: The name of the input. Defaults to 'event_addons'
	 *									'class'				Optional: Class of the input field
	 *									'checked'			Optional: ARRAY of initially checked options
	 *									'dj'				Optional: The ID of the DJ to present package for (default current user)
	 *									'package'			Optional: Package slug for which to exclude addons if they exist in that package
	 *									'title'				Optional: Add addon description to the title element of each option
	 *									'cost'				Optional: Display the price of the package (default true)
	 * @ return	HTML output for select field
	 */
	function mdjm_addons_checkboxes( $settings='' )	{
		global $current_user;
		
		// Set the values based on the array passed
		$check_name = isset( $settings['name'] ) ? $settings['name'] : 'event_addons';
		$check_id = isset( $settings['id'] ) ? $settings['id'] : $check_name;
		$check_dj = ( !empty( $settings['dj'] ) ? $settings['dj'] : ( is_user_logged_in() ? $current_user->ID : '' ) );
		$check_cost = isset( $settings['cost'] ) ? $settings['cost'] : false;
		
		$mdjm_check = '';
		
		$equipment = get_option( 'mdjm_equipment' );
		
		if( empty( $equipment ) )
			$mdjm_check .= __( 'No Addons Available', 'mobile-dj-manager' ) . "\r\n";
			
		else	{
			asort( $equipment );
		// All addons
			$cats = get_option( 'mdjm_cats' );
			if( !empty( $cats ) )
				asort( $cats );
			
			foreach( $cats as $cat_key => $cat_value )	{				
				$header = false;
				
				// Create an array of options grouped by category
				foreach( $equipment as $item )	{
					// If the addon is not enabled, do not show it
					if( empty( $item[6] ) || $item[6] != 'Y' )
						continue;
						
					// If the addon is part of an assigned package, exlude it
					if( !empty( $settings['package'] ) )	{
						$packages = get_option( 'mdjm_packages' );
						$package_items = explode( ',', $packages[$settings['package']]['equipment'] );
						
						if( !empty( $package_items ) && in_array( $item[1], $package_items ) )
							continue;	
					}
					
					// If the specified DJ does not have the addon, do not show it	
					if( !empty( $select_dj ) )	{
						$djs_have = explode( ',', $item[8] );
						
						if( !in_array( $select_dj, $djs_have ) )
							continue;
					}
					
					if( $item[5] == $cat_key )	{
						if( empty( $header ) )	{
							$mdjm_check .= '<span class="font-weight: bold;">' . stripslashes( $cat_value ) . '</span><br />' . "\r\n";
							$header = true;
						}
							
							$mdjm_check .= '<input type="checkbox" name="' . $check_name . '[]" ';
							$mdjm_check .= 'id="' . $check_name . '_' . stripslashes( esc_attr( $item[1] ) ) . '"';
							$mdjm_check .= ( !empty( $settings['class'] ) ? 
									' class="' . $settings['class'] . '"' : '' );
									
							$mdjm_check .= ' value="' . stripslashes( esc_attr( $item[1] ) ) . '"';
							
							if( !empty( $settings['checked'] ) && in_array( $item[1], $settings['checked'] ) )
								$mdjm_check .= ' checked="checked"';
							
							$mdjm_check .= ' />&nbsp;' . "\r\n";
							
							$mdjm_check .= ( !empty( $settings['title'] ) && !empty( $item[4] ) ? 
								'<span title="' . stripslashes( $item[4] ) . '">' : '' );
							
							$mdjm_check .= '<label for="' . $check_name . '_' . stripslashes( esc_attr( $item[1] ) ) . '">' . stripslashes( $item[0] );
							
							$mdjm_check .= ( $check_cost == true ? ' - ' . display_price( $item[7] ) : '' );
							
							$mdjm_check .= '</label>' . ( !empty( $settings['title'] ) && !empty( $item[4] ) ? '</span>' : '' ) . '<br />' .  "\r\n";
					}
					
				}
			}
		}
		
		return $mdjm_check;
		
	} // mdjm_addons_checkboxes

/*
 * -- END PACKAGE/ADDON FUNCTIONS
 */

/*
 * -- START TRANSACTION FUNCTIONS
 */

	/*
	 * get_transaction_types
	 * Retrieve all possible transaction types (taxonomy)
	 *
	 * @params:		$hide_empty		bool	false (default) to show all, true only those in use
	 *				
	 * @return: 	$trans_types	arr		Transaction type objects
	 */
	function get_transaction_types( $hide_empty=false )	{
		$hide_empty = $hide_empty == false ? 0 : 1;
		$trans_types = get_categories( array(
										'type'		=> MDJM_TRANS_POSTS,
										'taxonomy'	=> 'transaction-types',
										'order_by'	=> 'name',
										'order'	   => 'ASC',
										'hide_empty'  => $hide_empty,
										) );
		return $trans_types;
	} // get_transaction_types
	
	/*
	 * get_transaction_source
	 * Retrieve all possible transaction types (taxonomy)
	 *
	 * @params:		
	 *				
	 * @return: 	$trans_src	arr		Transaction sources
	 */
	function get_transaction_source()	{
		global $mdjm_settings;
		
		$trans_src = explode( "\r\n", $mdjm_settings['payments']['payment_sources'] );
			
		asort( $trans_src );
		
		return $trans_src;
	} // get_transaction_source
 
/*
 * -- END TRANSACTION FUNCTIONS
 */
 
/*
 * -- START MUSIC LIBRARY FUNCTIONS
 */
	/*
	 * Check if any music libraries exist
	 *
	 * @params:
	 * @return		bool	true if we have libraries, otherwise false
	 */
	function ml_exists()	{
		
		
	} // ml_exists
	
/*
 * -- END MUSIC LIBRARY FUNCTIONS
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
 
/*
 * -- END USER FUNCTIONS
 */

?>