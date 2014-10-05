<?php
	require_once WPMDJM_PLUGIN_DIR . '/admin/includes/functions.php';
/**************************************************************
-	Display the admin menu
**************************************************************/
	add_action( 'admin_menu', 'f_mdjm_admin_menu' );
	
/**************************************************************
-	Initialise the admin sections and fields
**************************************************************/
	function f_mdjm_settings_init()	{
		global $mdjm_options;
		$admin_settings_field = array(  'company_name',
										'app_name',
										'show_dashboard',
										'journaling',
										'multiple_dj',
										'event_types',
										'bcc_dj_to_client',
										'bcc_admin_to_client',
										'contract_to_client',
										'playlist_when',
										'playlist_close',
										'upload_playlists',
										'uninst_remove_db',
										'show_credits',
										'app_home_page',
										'contact_page',
										'contracts_page',
										'playlist_page',
										'dj_see_wp_dash',
										'dj_add_client',
										'dj_add_event',
										'dj_see_deposit',
									);
		$admin_fields = array();

/* GENERAL TAB */
		$admin_fields['company_name'] = array(
									'display' => 'Company Name',
									'key' => 'mdjm_plugin_settings',
									'type' => 'text',
									'class' => 'regular-text',
									'value' => $mdjm_options['company_name'],
									'text' => '',
									'desc' => 'Enter your company name',
									'section' => 'general',
									'page' => 'settings',
									); // company_name
																
		$admin_fields['app_name'] = array(
									'display' => 'Application Name',
									'key' => 'mdjm_plugin_settings',
									'type' => 'text',
									'class' => 'regular-text',
									'value' => $mdjm_options['app_name'],
									'text' => 'Default is <strong>Client Zone</strong>',
									'desc' => 'Choose your own name for the application. It\'s recommended you give the top level menu item linking to the application the same name.',
									'section' => 'general',
									'page' => 'settings',
									); // app_name
		
		$admin_fields['show_dashboard'] = array(
									'display' => 'Show Dashboard Widget?',
									'key' => 'mdjm_plugin_settings',
									'type' => 'checkbox',
									'class' => 'code',
									'value' => $mdjm_options['show_dashboard'],
									'text' => '',
									'desc' => 'Displays the MDJM widget on the main Wordpress Admin Dashboard',
									'section' => 'general',
									'page' => 'settings',
									); // show_dashboard
									
		$admin_fields['event_types'] = array(
									'display' => 'Event Types',
									'key' => 'mdjm_plugin_settings',
									'type' => 'textarea',
									'class' => '',
									'value' => str_replace( ",", "\n", $mdjm_options['event_types'] ),
									'text' => '',
									'desc' => 'The types of events that you provide. One per line.',
									'section' => 'general',
									'page' => 'settings',
									); // event_types
									
		$admin_fields['bcc_dj_to_client'] = array(
									'display' => 'Copy DJ in Client Emails',
									'key' => 'mdjm_plugin_settings',
									'type' => 'checkbox',
									'class' => 'code',
									'value' => $mdjm_options['bcc_dj_to_client'],
									'text' => '',
									'desc' => 'Blind copy DJ in emails to client',
									'section' => 'email',
									'page' => 'settings',
									); // bcc_dj_to_client
									
		$admin_fields['bcc_admin_to_client'] = array(
									'display' => 'Copy Admin in Client Emails',
									'key' => 'mdjm_plugin_settings',
									'type' => 'checkbox',
									'class' => 'code',
									'value' => $mdjm_options['bcc_admin_to_client'],
									'text' => '',
									'desc' => 'Blind copy the WP admin in emails to client',
									'section' => 'email',
									'page' => 'settings',
									); // bcc_admin_to_client
									
		$admin_fields['contract_to_client'] = array(
									'display' => 'Email contract link to client?',
									'key' => 'mdjm_plugin_settings',
									'type' => 'checkbox',
									'class' => 'code',
									'value' => $mdjm_options['contract_to_client'],
									'text' => '',
									'desc' => 'Email client with contract details when an enquiry is converted and the event status changes to Pending',
									'section' => 'email',
									'page' => 'settings',
									); // contract_to_client
									
		$admin_fields['journaling'] = array(
									'display' => 'Enable Journaling?',
									'key' => 'mdjm_plugin_settings',
									'type' => 'checkbox',
									'class' => 'code',
									'value' => $mdjm_options['journaling'],
									'text' => '',
									'desc' => 'Log and track all client &amp; event actions (recommended)',
									'section' => 'general',
									'page' => 'settings',
									); // journaling
									
		$admin_fields['multiple_dj'] = array(
									'display' => 'Multiple DJ\'s',
									'key' => 'mdjm_plugin_settings',
									'type' => 'checkbox',
									'class' => 'code',
									'value' => $mdjm_options['multiple_dj'],
									'text' => '',
									'desc' => 'Check this if you employ DJ\'s',
									'section' => 'general',
									'page' => 'settings',
									); // multiple_dj
									
		$admin_fields['playlist_when'] = array(
									'display' => 'Playlist Song Options',
									'key' => 'mdjm_plugin_settings',
									'type' => 'textarea',
									'class' => '',
									'value' => str_replace( ",", "\n", $mdjm_options['playlist_when'] ),
									'text' => '',
									'desc' => 'The options clients can select for when songs are to be played when adding to the playlist. One per line.',
									'section' => 'playlist',
									'page' => 'settings',
									); // playlist_when
									
		$admin_fields['playlist_close'] = array(
									'display' => 'Close the Playlist',
									'key' => 'mdjm_plugin_settings',
									'type' => 'text',
									'class' => 'small-text',
									'value' => $mdjm_options['playlist_close'],
									'text' => 'Enter 0 to never close',
									'desc' => 'Days before the event should the playlist be closed.',
									'section' => 'playlist',
									'page' => 'settings',
									); // playlist_close
									
		$admin_fields['uninst_remove_db'] = array(
									'display' => 'Remove Database Tables?',
									'key' => 'mdjm_plugin_settings',
									'type' => 'checkbox',
									'class' => 'code',
									'value' => $mdjm_options['uninst_remove_db'],
									'text' => '',
									'desc' => 'Should the database tables and data be removed when uninstalling the plugin? Cannot be recovered unless you or your host have a backup solution in place.',
									'section' => 'uninstall',
									'page' => 'settings',
									); // uninst_remove_db
									
		$admin_fields['show_credits'] = array(
									'display' => 'Display Credits?',
									'key' => 'mdjm_plugin_settings',
									'type' => 'checkbox',
									'class' => 'code',
									'value' => $mdjm_options['show_credits'],
									'text' => '',
									'desc' => 'Whether or not to display the <font size="-1"; color="#F90">Powered by ' . WPMDJM_NAME . ', version ' . WPMDJM_VERSION_NUM . '</font> text at the footer of the application pages.',
									'section' => 'credits',
									'page' => 'settings',
									); // show_credits
									
		$admin_fields['upload_playlists'] = array(
									'display' => 'Upload Playlists?',
									'key' => 'mdjm_plugin_settings',
									'type' => 'checkbox',
									'class' => 'code',
									'value' => $mdjm_options['upload_playlists'],
									'text' => '',
									'desc' => 'With this option checked, your playlist information may occasionally be transmitted back to the MDJM authors to help build an information library. The consolidated list of playlist songs will be freely shared. Only song, artist and the event type information is transmitted.',
									'section' => 'playlist',
									'page' => 'settings',
									); // upload_playlists
		
/* PAGES TAB */
		$admin_fields['app_home_page'] = array(
									'display' => WPMDJM_APP_NAME . ' Home Page',
									'key' => 'mdjm_plugin_pages',
									'type' => 'custom_dropdown',
									'class' => 'regular-text',
									'value' => $mdjm_options['app_home_page'],
									'text' => '<a href="' . admin_url() . 'post-new.php?post_type=page" class="add-new-h2">Add New</a>',
									'desc' => 'Select the home page for the ' . WPMDJM_APP_NAME . ' application  - the one where you added the shortcode <code>[MDJM page=Home]</code>',
									'custom_args' => array (
														'name' =>  'mdjm_plugin_pages[app_home_page]',
														'sort_order' => 'ASC',
														'selected' => $mdjm_options['app_home_page']
														),
									'section' => 'pages',
									'page' => 'pages',
									); // app_home_page

		$admin_fields['contact_page'] = array(
									'display' => 'Contact Page',
									'key' => 'mdjm_plugin_pages',
									'type' => 'custom_dropdown',
									'class' => 'regular-text',
									'value' => $mdjm_options['contact_page'],
									'text' => '<a href="' . admin_url() . 'post-new.php?post_type=page" class="add-new-h2">Add New</a>',
									'desc' => 'Select your website\'s contact page so we can correctly direct visitors.',
									'custom_args' => array (
														'name' =>  'mdjm_plugin_pages[contact_page]',
														'sort_order' => 'ASC',
														'selected' => $mdjm_options['contact_page']
														),
									'section' => 'pages',
									'page' => 'pages',
									); // contact_page
									
		$admin_fields['contracts_page'] = array(
									'display' => 'Contracts Page',
									'key' => 'mdjm_plugin_pages',
									'type' => 'custom_dropdown',
									'class' => 'regular-text',
									'value' => $mdjm_options['contracts_page'],
									'text' => '<a href="' . admin_url() . 'post-new.php?post_type=page" class="add-new-h2">Add New</a>',
									'desc' => 'Select your website\'s contracts page - the one where you added the shortcode <code>[MDJM page=Contract]</code>',
									'custom_args' => array (
														'name' =>  'mdjm_plugin_pages[contracts_page]',
														'sort_order' => 'ASC',
														'selected' => $mdjm_options['contracts_page']
														),
									'section' => 'pages',
									'page' => 'pages',
									); // contracts_page
									
		$admin_fields['playlist_page'] = array(
									'display' => 'Playlist Page',
									'key' => 'mdjm_plugin_pages',
									'type' => 'custom_dropdown',
									'class' => 'regular-text',
									'value' => $key['playlist_page'],
									'text' => '<a href="' . admin_url() . 'post-new.php?post_type=page" class="add-new-h2">Add New</a>',
									'desc' => 'Select your website\'s playlist page - the one where you added the shortcode <code>[MDJM page=Playlist]</code>',
									'custom_args' => array (
														'name' =>  'mdjm_plugin_pages[playlist_page]',
														'sort_order' => 'ASC',
														'selected' => $mdjm_options['playlist_page']
														),
									'section' => 'pages',
									'page' => 'pages',
									); // playlist_page

/* PERMISSIONS TAB */
		$admin_fields['dj_see_wp_dash'] = array(
									'display' => 'DJ\'s see WP Dashboard?',
									'key' => 'mdjm_plugin_permissions',
									'type' => 'checkbox',
									'class' => 'code',
									'value' => $mdjm_options['dj_see_wp_dash'],
									'text' => 'If checked your DJ\'s will be able to see the main WordPress Dashboard page',
									'desc' => '',
									'section' => 'permissions',
									'page' => 'permissions',
									); // dj_see_wp_dash

		$admin_fields['dj_add_client'] = array(
									'display' => 'DJ\'s can Add New Clients?',
									'key' => 'mdjm_plugin_permissions',
									'type' => 'checkbox',
									'class' => 'code',
									'value' => $mdjm_options['dj_add_client'],
									'text' => '',
									'desc' => '',
									'section' => 'permissions',
									'page' => 'permissions',
									); // dj_add_client
									
		$admin_fields['dj_add_event'] = array(
									'display' => 'DJ\'s Can Add New Events?',
									'key' => 'mdjm_plugin_permissions',
									'type' => 'checkbox',
									'class' => 'code',
									'value' => $mdjm_options['dj_add_event'],
									'text' => '',
									'desc' => '',
									'section' => 'permissions',
									'page' => 'permissions',
									); // dj_add_event
									
		$admin_fields['dj_see_deposit'] = array(
									'display' => 'DJ\'s Can See Deposit Info?',
									'key' => 'mdjm_plugin_permissions',
									'type' => 'checkbox',
									'class' => 'code',
									'value' => $mdjm_options['dj_see_deposit'],
									'text' => '',
									'desc' => '',
									'section' => 'permissions',
									'page' => 'permissions',
									); // dj_add_event
		
		add_settings_section( 'mdjm_general_settings',
							  '',
							  'f_mdjm_desc',
							  'mdjm-settings'
							);
		add_settings_section( 'mdjm_email_settings',
							  'Email Options <hr />',
							  'f_mdjm_desc',
							  'mdjm-settings'
							);
		add_settings_section( 'mdjm_playlist_settings',
							  'Playlist Options <hr />',
							  'f_mdjm_desc',
							  'mdjm-settings'
							);
		add_settings_section( 'mdjm_uninstall_settings',
							  'Uninstall Options <hr />',
							  'f_mdjm_desc',
							  'mdjm-settings'
							);
		add_settings_section( 'mdjm_credits_settings',
							  'Credits &amp; Feedback <hr />',
							  'f_mdjm_desc',
							  'mdjm-settings'
							);
		add_settings_section( 'mdjm_pages_settings',
							  '',
							  'f_mdjm_desc',
							  'mdjm-pages'
							);
		add_settings_section( 'mdjm_permissions_settings',
							  '',
							  'f_mdjm_desc',
							  'mdjm-permissions'
							);
		
		foreach( $admin_settings_field as $settings_field )	{
			if( isset( $admin_fields[$settings_field]['custom_args'] ) && !empty( $admin_fields[$settings_field]['custom_args'] ) ) $custom_args = $admin_fields[$settings_field]['custom_args'];
			add_settings_field( $settings_field,
							'<label for="' . $settings_field . '">' . $admin_fields[$settings_field]['display'] . '</label>',
							'f_mdjm_general_settings_callback',
							'mdjm-' . $admin_fields[$settings_field]['page'],
							'mdjm_' . $admin_fields[$settings_field]['section'] . '_settings',
							array( 
								'field' => $settings_field,
								'label for' => $settings_field,
								'key' => $admin_fields[$settings_field]['key'],
								'type' => $admin_fields[$settings_field]['type'],
								'class' => $admin_fields[$settings_field]['class'],
								'value' => $admin_fields[$settings_field]['value'],
								'text' => $admin_fields[$settings_field]['text'],
								'desc' => $admin_fields[$settings_field]['desc'],
								'custom_args' => $custom_args
								)
						);
		} // foreach
		register_setting( 'mdjm-settings', WPMDJM_SETTINGS_KEY );
		register_setting( 'mdjm-permissions', 'mdjm_plugin_permissions' );
		register_setting( 'mdjm-pages', 'mdjm_plugin_pages' );
	} // f_mdjm_settings_init
	
	add_action( 'admin_init', 'f_mdjm_settings_init' );

/**************************************************************
-	Callbacks for sections, fields & validation
**************************************************************/	
	function f_mdjm_desc()	{
		// Intentionally blank
	} // f_mdjm_desc

/* Validate the fields */	
	function f_mdjm_validate_settings( $input )	{
		$valid = array();
		/* Check for incomplete fields */
		if( empty( $input['company_name'] ) )	{
			add_settings_error(
				'mdjm_company_name',
				'mdjm_company_name_texterror',
				'Company Name cannot be empty',
				'error'
			);
		}
		
		
		$valid['company_name'] = sanitize_text_field( $input['company_name'] );

		if( $valid['company_name'] != $input['company_name'] ) {
			add_settings_error(
				'mdjm_company_name',           // setting title
				'mdjm_company_name_texterror',            // error ID
				'Inavlid entry for Company Name',   // error message
				'error'                        // type of message
			);		
	}
		return $valid;
	} // f_mdjm_validate_settings

/* Process the fields to be displayed */
	function f_mdjm_general_settings_callback( $args )	{
		if( $args['type'] == 'custom_dropdown' )	{
			wp_dropdown_pages( $args['custom_args'] );
		}
		elseif( $args['type'] == 'textarea' )	{
			echo '<textarea id="' . $args['field'] . '" name="' . $args['key'] . '[' . $args['field'] . ']" cols="30" rows="6">' . $args['value'] . '</textarea>';
		}
		elseif( $args['type'] == 'checkbox' )	{
			echo '<input name="' . $args['key'] . '[' . $args['field'] . ']" id="' . $args['field'] . '" type="' . $args['type'] . '" value="Y" class="' . $args['class']  . '" ' . 
			checked( $args['value'], 'Y', false ) . ' />';
		}
		else	{
			echo '<input name="' . $args['key'] . '[' . $args['field'] . ']" id="' . $args['field'] . '" type="' . $args['type'] . '" class="' . $args['class'] . '" value="' . $args['value'] . '" />';
		}
		if( isset( $args['text'] ) && !empty( $args['text'] ) ) echo '<label for="' . $args['field'] . '"> ' . $args['text'] . '</label>';
		if( isset( $args['desc'] ) && !empty( $args['desc'] ) ) echo '<p class="description">' . $args['desc'] . '</p>';
	} // f_mdjm_general_settings_callback

	function f_mdjm_app_validate()	{
		$lic_check = do_reg_check( 'check' );
		if( !$lic_check )	{
			echo '<div class="error">';
			echo '<p>Your Mobile DJ Manager license has expired. Please visit <a href="http://www.mdjm.co.uk" target="_blank">http://www.mdjm.co.uk</a> to renew.</p>';
			echo '<p>Functionality will be restricted until your license is renewed.</p>';
			echo '</div>';
		}
	}
	add_action( 'admin_notices', 'f_mdjm_app_validate' );
	
	/* Validation */
	function f_mdjm_reg_init()	{ /* Update the status if required */
		include WPMDJM_PLUGIN_DIR . '/admin/includes/config.inc.php';
		if( false === ( $reg_check = get_transient( $t_query[0] ) ) )
			do_reg_check( 'set' );
	}
	
	function do_reg_check( $t_action )	{
		include WPMDJM_PLUGIN_DIR . '/admin/includes/config.inc.php';
		if( $t_action == 'set' )	{
			set_transient( $t_query[0], wp_remote_retrieve_body( wp_remote_get( $t_query[1] . $t_query[2] . $t_query[3] . $t_query[4] ) ), DAY_IN_SECONDS );
		}
		
		if( $t_action == 'check' )	{
			$reg_check = get_transient( $t_query[0] ); /* Get the value */
			
			if( $reg_check !== false && $reg_check != 'License key is invalid' )	{
				$lic = explode( '|', $reg_check );
				if( time() <= strtotime( $lic[2] ) )	{
					$lic_info = $lic;
				}
				else	{
					$is_beta = get_transient( 'mdjm_is_beta' );
					if( $is_beta !== false )	{
						$lic = explode( '|', $is_beta );
						if( time() <= strtotime( $lic[2] ) )	{
							$lic_info = $lic;
						}
						else	{
							$lic_info === false;	
						}
					}
				}
			}
			else	{ // Trans was false
				$is_beta = get_transient( 'mdjm_is_beta' );
				if( $is_beta !== false )	{
					$lic = explode( '|', $is_beta );
					if( time() <= strtotime( $lic[2] ) )	{
						$lic_info = $lic;
					}
					else	{
						$lic_info === false;	
					}
				}
				
				else	{ 
					$lic_info === false;
				}
			}
			return $lic_info;
		}
	}	
?>
