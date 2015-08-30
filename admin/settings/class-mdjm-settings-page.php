<?php
/*
 * class-mdjm.php
 * 03/06/2015
 * @since 1.2.1
 * The MDJM_Settings_Page class
 */
	
	/* -- Build the MDJM_Settings_Page class -- */

	if( !class_exists( 'MDJM_Settings' ) )
		require_once( 'class-mdjm-settings.php' );
	
	class MDJM_Settings_Page extends MDJM_Settings	{
		/*
		 * Determine the page to display
		 *
		 *
		 *
		 */
		function __construct()	{
			global $mdjm_settings_page;
			
			parent::__construct();
			
			parent::set_loc();
				
			$screen = get_current_screen();
			
			if( !empty( $screen ) && $screen->id == $mdjm_settings_page )
				$this->display_settings_page();
		} // __construct
		
		/*
		 * Display the relevant settings page
		 *
		 *
		 *
		 */
		function display_settings_page()	{
			parent::page_header();
			$this->display_sections();
			parent::page_footer();
		} // display_settings_page();
		
		/*
		 * Display the settings sections and fields
		 *
		 *
		 *
		 */
		function display_sections()	{	
			global $mdjm_debug;
					
			if( empty( $this->current_section ) )
				return;
						
			switch( $this->current_section )	{
				/* -- General Tab Settings -- */
				case 'mdjm_app_settings':
					$this->current_status();
					settings_fields( 'mdjm-settings' );
					do_settings_sections( 'mdjm-settings' );
				break;
				case 'mdjm_app_permissions':
					settings_fields( 'mdjm-permissions' );
					do_settings_sections( 'mdjm-permissions' );
				break;
				case 'mdjm_app_debugging':
					settings_fields( 'mdjm-debugging' );
					do_settings_sections( 'mdjm-debugging' );
					settings_fields( 'mdjm-debugging-files' );
					do_settings_sections( 'mdjm-debugging-files' );
				break;
				case 'mdjm_db_backups':
					$mdjm_debug->db_backup_form();
				break;
				case 'mdjm_app_uninstall':
					settings_fields( 'mdjm-uninstall' );
					do_settings_sections( 'mdjm-uninstall' );
				break;
				
				/* -- Event Tab Settings -- */
				case 'mdjm_events_settings':
					settings_fields( 'mdjm-events' );
					do_settings_sections( 'mdjm-events' );
				break;
				
				case 'mdjm_playlist_settings':
					settings_fields( 'mdjm-playlists' );
					do_settings_sections( 'mdjm-playlists' );
				break;
				
				case 'mdjm_event_staff':
					settings_fields( 'mdjm-staff' );
					do_settings_sections( 'mdjm-staff' );
				break;
				
				/* -- Email Tab Settings -- */
				case 'mdjm_email_settings':
					settings_fields( 'mdjm-email' );
					do_settings_sections( 'mdjm-email' );
				break;
				case 'mdjm_email_templates_settings':
					settings_fields( 'mdjm-email-templates' );
					do_settings_sections( 'mdjm-email-templates' );
				break;
				
				/* -- Client Zone Tab Settings -- */
				case 'mdjm_app_general':
					settings_fields( 'mdjm-clientzone' );
					do_settings_sections( 'mdjm-clientzone' );
				break;
				
				case 'mdjm_app_pages':
					settings_fields( 'mdjm-pages' );
					do_settings_sections( 'mdjm-clientzone-page' );
				break;
				
				case 'mdjm_app_text':
					settings_fields( 'mdjm-client-text' );
					do_settings_sections( 'mdjm-clientzone-text' );
				break;
				
				case 'mdjm_availability_settings':
					settings_fields( 'mdjm-availability' );
					do_settings_sections( 'mdjm-availability' );
				break;
				
				case 'mdjm_payment_settings':
					settings_fields( 'mdjm-payments' );
					do_settings_sections( 'mdjm-payments' );
				break;
				
				case 'mdjm_paypal_settings':
					settings_fields( 'mdjm-paypal' );
					do_settings_sections( 'mdjm-paypal' );
				break;
				
				case 'mdjm_client_field_settings':
					include_once( 'class-mdjm-settings-client-fields.php' );
				break;
				
				default:
					return;
			} // switch
			
		} // display_sections
		
		/*
		 * Display current validility status
		 *
		 *
		 *
		 */
		function current_status()	{
			global $mdjm;
			
			$status = $mdjm->_mdjm_validation();
			
			if( !empty( $status ) )
				/* -- Calculate days remaining -- */
				$diff = strtotime( $status['expire'] ) - time();
				if( $diff < 0 )
					$diff = 0;
					
				$remaining = floor( $diff / 60 / 60 / 24 );
				if( $remaining < 0 )
					$remaining = 0;
					
				if( floor( $diff / 60 / 60 / 24 ) < 30 )	{
					$box_class = 'mdjm-warning';
					$msg = sprintf( __( '%sYour license will expire in %s. ' .
					'Visit %s ' . 'to renew your license now%s', 'mobile-dj-manager' ), 
					'<span style="color: red; font-weight: bold">', $remaining . _n( ' day', ' days', $remaining ),
					'<a href="' . mdjm_get_admin_page( 'mydjplanner' ) . '" target="_blank">' . mdjm_get_admin_page( 'mydjplanner' ) . '</a>',
					'</span>' );
				}
				
				/* -- Trial or license expired -- */
				if( !empty( $status['expire'] ) && time() >= strtotime( $status['expire'] ) )	{
					$box_class = 'mdjm-error';
					$msg = sprintf( __( 'Visit %s to purchase your license.	Functionality will remain restricted until a new license is acquired', 'mobile-dj-manager' ),
					'<a href="' . mdjm_get_admin_page( 'mydjplanner' ) . '" target="_blank">' . mdjm_get_admin_page( 'mydjplanner' ) . '</a>' );	
				}
				
				/* -- Running in trial mode -- */
				else
					$box_class = 'mdjm-success';
			
			/* -- Print the license status -- */
			echo '<div class="' . $box_class . '">' . "\r\n"; 
			
			echo sprintf( __( '%sLicense Key%s: %s', 'mobile-dj-manager' ),
				'<strong>', '</strong>', ( $status['key'] != 'XXXX' ? $status['key'] : 'TRIAL' ) ) . '<br />' . "\r\n"; 
			
			echo '<strong>' . __( 'Expires', 'mobile-dj-manager' ) . '</strong>: ' . date( 'l, jS F Y', strtotime( $status['expire'] ) ) . ' <br />' . "\r\n"; 
			
			echo '<strong>' . __( 'Licensed To', 'mobile-dj-manager' ) . '</strong>: ' . ( !empty( $status['url'] ) ? '<a href="' . strtolower( $status['url'] ) . '" target="_blank">' . 
				strtolower( $status['url'] ) . '</a>' : '' ) . '<br />' . "\r\n"; 
				
			echo '<strong>' . __( 'Last Updated', 'mobile-dj-manager' ) . '</strong>: ' . date( MDJM_TIME_FORMAT . ' \o\n ' . MDJM_SHORTDATE_FORMAT, strtotime( $status['last_auth'] ) ) . '<br />' . 
			( !empty( $msg ) ? $msg : '' ) . "\r\n"; 
			
			echo '</div>' . "\r\n";
			
		} // current_status
	} // class
	
	$mdjm_plugin_settings_page = new MDJM_Settings_Page();

