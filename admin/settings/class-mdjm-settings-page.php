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
				
				case 'mdjm_payfast_settings':
					settings_fields( 'mdjm-payfast' );
					do_settings_sections( 'mdjm-payfast' );
				break;
				
				case 'mdjm_client_field_settings':
					include_once( 'class-mdjm-settings-client-fields.php' );
				break;
								
				case 'mdjm_addon_settings':
					settings_fields( 'mdjm-addons' );
					do_settings_sections( 'mdjm-addons' );
				break;
				
				case 'mdjm_gcal_settings':
					settings_fields( 'mdjm-gcal' );
					do_settings_sections( 'mdjm-gcal' );
				break;
				
				default:
					return;
			} // switch
		} // display_sections	
	} // class
	
	$mdjm_plugin_settings_page = new MDJM_Settings_Page();

