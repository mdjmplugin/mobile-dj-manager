<?php
/*
 * Contains recently deprecated functions for backwards compatibility
 *
 *
 *
 */
	/*
	 * License check
	 *
	 * @from: admin.php
	 * @since: 1.2.1
	 */
	function do_reg_check( $action )	{
		global $mdjm;
		
		$mdjm->debug_logger( 'DEPRECATED function in use ' . __FUNCTION__, true );
		
		return $mdjm->_mdjm_validation( $action );
	}
	
	/*
	* Print out the correct currency symbol
	* 
	* @from: /admin/includes/functions.php
	* @since: 1.2.1
	*/
	function f_mdjm_currency()	{
		global $mdjm, $mdjm_settings;
		
		$mdjm->debug_logger( 'WARNING: Use of deprecated function ' . __FUNCTION__, true );
		
		if( !isset( $mdjm_currency ) )
			include( WPMDJM_PLUGIN_DIR . '/includes/config.inc.php' );
		
		echo $mdjm_currency[$mdjm_settings['payments']['currency']];
	} // f_mdjm_currency
	
	/*
	* Print out the page URL
	* 
	* @from: /admin/includes/functions.php
	* @since: 1.2.1
	*/
	function f_mdjm_admin_page( $mdjm_page )	{
		global $mdjm;
		
		$mdjm->debug_logger( 'WARNING: Use of deprecated function ' . __FUNCTION__, true );
		
		$mydjplanner = array( 'mydjplanner', 'user_guides', 'mdjm_support', 'mdjm_forums' );
		$mdjm_pages = array(
						'wp_dashboard'          => 'index.php',
						'dashboard'             => 'admin.php?page=mdjm-dashboard',
						'settings'              => 'admin.php?page=mdjm-settings',
						'clients'               => 'admin.php?page=mdjm-clients',
						'inactive_clients'      => 'admin.php?page=mdjm-clients&display=inactive_client',
						'add_client'            => 'user-new.php',
						'edit_client'           => 'user-edit.php?user_id=',
						'comms'                 => 'admin.php?page=mdjm-comms',
						'email_history'         => 'edit.php?post_type=' . MDJM_COMM_POSTS,
						'contract'              => 'edit.php?post_type=' . MDJM_CONTRACT_POSTS,
						'add_contract'          => 'post-new.php?post_type=' . MDJM_CONTRACT_POSTS,
						'djs'                   => 'admin.php?page=mdjm-djs',
						'inactive_djs'          => 'admin.php?page=mdjm-djs&display=inactive_dj',
						'email_template'        => 'edit.php?post_type=' . MDJM_EMAIL_POSTS,
						'add_email_template'    => 'post-new.php?post_type=' . MDJM_EMAIL_POSTS,
						'equipment'             => 'admin.php?page=mdjm-packages',
						'events'                => 'admin.php?page=mdjm-events',
						'add_event'             => 'admin.php?page=mdjm-events&action=add_event_form',
						'enquiries'             => 'admin.php?page=mdjm-events&status=Enquiry',
						'venues'                => 'edit.php?post_type=' . MDJM_VENUE_POSTS,
						'add_venue'             => 'post-new.php?post_type=' . MDJM_VENUE_POSTS,
						'tasks'                 => 'admin.php?page=mdjm-tasks',
						'client_text'           => 'admin.php?page=mdjm-settings&tab=client_text',
						'client_fields'         => 'admin.php?page=mdjm-settings&tab=client_fields',
						'availability'          => 'admin.php?page=mdjm-availability',
						'debugging'             => 'admin.php?page=mdjm-settings&tab=debugging',
						'contact_forms'         => 'admin.php?page=mdjm-contact-forms',
						'transactions'		  => 'admin.php?page=mdjm-transactions',
						'mydjplanner'           => 'http://www.mydjplanner.co.uk',
						'user_guides'           => 'http://www.mydjplanner.co.uk/support/user-guides',
						'mdjm_support'          => 'http://www.mydjplanner.co.uk/support',
						'mdjm_forums'           => 'http://www.mydjplanner.co.uk/forums',
						);
		if( in_array( $mdjm_page, $mydjplanner ) )	{
			echo $mdjm_pages[$mdjm_page];	
		}
		else	{
			echo admin_url( $mdjm_pages[$mdjm_page] );
		}
	}
	
	/*
	* Print out the an update notice
	* 
	* @from: /admin/includes/functions.php
	* @since: 1.2.1
	*/
	function f_mdjm_admin_update_notice( $message_no )	{
		global $mdjm;
		
		$mdjm->debug_logger( 'WARNING: Use of deprecated function ' . __FUNCTION__, true );
		
		if ( $message_no == 0 )	{ // Success
			$class = "updated";
			$message = "Your settings have been saved successfully";
		}
		else	{ // Failure
			$class = "error";
			$message = "Sorry there was an issue and your settings could not be saved. Please try again.";
		}
		?>
		<div id="message" class="<?php echo $class; ?>">
		<p><?php _e( $message, 'my-text-domain' ); ?></p>
		</div>
		<?php
	} // f_mdjm_admin_update_notice
	
	/*
	* Print out the an update notice
	* 
	* @from: /admin/includes/functions.php
	* @since: 1.2.1
	*/
	function f_mdjm_update_notice( $class, $message )	{
		global $mdjm;
		
		$mdjm->debug_logger( 'WARNING: Use of deprecated function ' . __FUNCTION__, true );
		
		echo '<div id="message" class="' . $class . '">';
		echo '<p>' . $message . '</p>';
		echo '</div>';
	} // f_mdjm_update_notice
	
	/*
	* Return all dates within the given range
	* 
	* @from: /admin/includes/functions.php
	* @since: 1.2.1
	*/
	function f_mdjm_all_dates_in_range( $from_date, $to_date )	{
		$from_date = \DateTime::createFromFormat( 'Y-m-d', $from_date );
		$to_date = \DateTime::createFromFormat( 'Y-m-d', $to_date );
		return new \DatePeriod(
			$from_date,
			new \DateInterval( 'P1D' ),
			$to_date->modify( '+1 day' )
		);
	} // f_mdjm_all_dates_in_range
?>