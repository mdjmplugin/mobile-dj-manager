<?php
/*
 * class-mdjm-settings.php
 * 03/06/2015
 * @since 1.2.1
 * The MDJM Settings class
 */
	defined( 'ABSPATH' ) or die( 'Direct access to this page is disabled!!!' );
	
	if ( !current_user_can( 'manage_options' ) && !current_user_can( 'manage_mdjm' ) ) 
		wp_die( __( 'You do not have sufficient permissions to access this page.' ) );

	/* -- Build the MDJM Settings class -- */
	if( !class_exists( 'MDJM_Settings' ) )	{
		class MDJM_Settings	{
			
			function __construct()	{
				$this->settings_register();
				
				add_action( 'contextual_help', array( &$this, 'help_text' ), 10, 3 ); // Contextual help
				
			} // __construct
			
			/*
			 * Register the MDJM settings
			 *
			 *
			 *
			 */
			function settings_register()	{	
				global $mdjm_settings, $mdjm_debug;
							
				/* -- Get the array of settings -- */
				include( 'settings.php' );
				
				$this->sections = $all_sections;
				$this->settings = $all_settings;
				
				$this->add_sections();
				$this->add_fields();
								
				/* -- Register the settings -- */
				register_setting( 'mdjm-settings', MDJM_SETTINGS_KEY );
				register_setting( 'mdjm-permissions', MDJM_PERMISSIONS_KEY );
				register_setting( 'mdjm-debugging-files', MDJM_DEBUG_SETTINGS_KEY );
				register_setting( 'mdjm-events', MDJM_EVENT_SETTINGS_KEY );
				register_setting( 'mdjm-playlists', MDJM_PLAYLIST_SETTINGS_KEY );
				register_setting( 'mdjm-email', MDJM_EMAIL_SETTINGS_KEY );
				register_setting( 'mdjm-email-templates', MDJM_TEMPLATES_SETTINGS_KEY );
				register_setting( 'mdjm-clientzone', MDJM_CLIENTZONE_SETTINGS_KEY );
				register_setting( 'mdjm-pages', MDJM_PAGES_KEY );
				register_setting( 'mdjm-availability', MDJM_AVAILABILITY_SETTINGS_KEY );
				register_setting( 'mdjm-client-text', MDJM_CUSTOM_TEXT_KEY );
				register_setting( 'mdjm-payments', MDJM_PAYMENTS_KEY );
				register_setting( 'mdjm-paypal', MDJM_PAYPAL_KEY );
			} // settings_register
			
			/*
			 * Validate the settings field entries
			 *
			 * @param	arr		$input		array of all option values on the page
			 *
			 */
			function settings_validate( $input )	{
				$output = $input;
				
				print_r( $input );
				exit;
				
				foreach( $input as $key => $value )	{
					if( $input == 'system_email' )	{
						if( !is_email( $input ) )	{
							add_settings_error( $key, esc_attr( 'settings_updated' ), 'Not an email address', 'error' );	
						}
					}
				}
				
				return $output;
			} // settings_validate
			
			/*
			 * Add the settings sections
			 *
			 *
			 *
			 */
			function add_sections()	{
				if( empty( $this->sections ) )
					return;
					
				foreach( $this->sections as $name => $args )	{
					add_settings_section( $name, $args['title'], array( &$this, 'section_content' ), $args['page']  );	
				}
			} // add_sections
			
			/*
			 * Add section content if required
			 *
			 *
			 */
			function section_content( $args )	{
				if( $args['id'] == 'mdjm_debugging_settings' )
					echo __( '<p>The settings below enable the MDJM support team to identify any problems ' . 
					'you may be experiencing.</p>' . 
					'<p>With debugging enabled, much of the activity that is executed as you browse around ' . 
					'pages and utilise features within the MDJM application and the ' . MDJM_APP . ' is logged ' . 
					'and this can lead to slightly slower load times for your pages. It is therefore recommended ' . 
					'that you leave debugging turned off unless you are experiencing an issue and the MDJM support ' . 
					'team have asked you to enable this setting to aid in identifying the problem.</p>' );
					
				if( $args['id'] == 'mdjm_debugging_files_settings' )
					echo __( '<p>The following settings only apply if debugging is enabled</p>' );
			} // section_content
			
			/*
			 * Add the settings field
			 *
			 *
			 *
			 */
			function add_fields()	{
				if( empty( $this->settings ) )
					return;
									
				foreach( $this->settings as $setting => $options )	{
					add_settings_field(
									$setting, // The name of the setting
									$options['label'], // The field label
									array( &$this, 'display_field' ), // The content
									'mdjm-' . $options['page'], // Which settings page to display on
									'mdjm_' . $options['section'] . '_settings', // Which section on the page
									array(  // Additional args
										'field' => $setting,
										'label_for' => $setting,
										'key' => ( !empty( $options['key'] ) ? $options['key'] : '' ),
										'type' => $options['type'],
										'class' => ( !empty( $options['class'] ) ? $options['class'] : '' ),
										'value' => $options['value'],
										'text' => ( !empty( $options['text'] ) ? $options['text'] : '' ),
										'desc' => ( !empty( $options['desc'] ) ? $options['desc'] : '' ),
										'size' => ( !empty( $options['size'] ) ? $options['size'] : '' ),
										'custom_args' => ( !empty( $options['custom_args'] ) ? $options['custom_args'] : '' ),
									) );
				}
					
			} // add_fields
			
			/*
			 * Determine the type of field to display and then call the
			 * appropriate method to display it
			 *
			 *
			 */
			function display_field( $args )	{
				switch( $args['type'] )	{
					/* -- Checkbox Field -- */
					case 'checkbox':
						$this->show_checkbox_field( $args );
					break;
					
					/* -- Radio Field -- */
					case 'radio':
						$this->show_radio_field( $args );
					break;
					
					/* -- Custom Dropdown Field -- */
					case 'custom_dropdown':
						$this->show_select_field( $args );
					break;
					
					/* -- Milti Select Field -- */
					case 'multiple_select':
						$this->show_select_field( $args );
					break;
					
					/* -- Text Field -- */
					case 'text':
						$this->show_text_field( $args );
					break;
					/* -- Email Field -- */
					case 'email':
						$this->show_text_field( $args );
					break;
					/* -- Textarea Field -- */
					case 'textarea':
						$this->show_textarea_field( $args );
					break;
					/* -- Textarea TinyMCE field -- */
					case 'mce_textarea':
						$this->show_mce_textarea_field( $args );
					break;
				} // Switch
					
				echo ( !empty( $args['text'] ) ? __( $args['text'] ) : '' ) . 
				( !empty( $args['desc'] ) ? '<p class="description">' . __( $args['desc'] ) . '</p>' : '' ) . "\r\n";
					
			} // display_field
					
			/*
			 * Define & declare the current location (tab/section)
			 *
			 *
			 *
			 */
			function set_loc()	{
				$this->current_tab = ( isset( $_GET['tab'] ) ? $_GET['tab'] : 'general' );
				
				switch( $this->current_tab )	{
					case 'general':
						$this->current_section = ( isset( $_GET['section'] ) ? $_GET['section'] : 'mdjm_app_settings' );
					break;
					case 'events':
						$this->current_section = ( isset( $_GET['section'] ) ? $_GET['section'] : 'mdjm_events_settings' );
					break;
					case 'emails':
						$this->current_section = ( isset( $_GET['section'] ) ? $_GET['section'] : 'mdjm_email_settings' );
					break;
					case 'client-zone':
						$this->current_section = ( isset( $_GET['section'] ) ? $_GET['section'] : 'mdjm_app_general' );
					break;
					case 'payments':
						$this->current_section = ( isset( $_GET['section'] ) ? $_GET['section'] : 'mdjm_payment_settings' );
					break;
				} // switch
			} // set_loc
			
			/*
			 * Start the page HTML and display the navigation tabs and links
			 *
			 * @param
			 *
			 */
			function page_header()	{
				global $mdjm_debug;
				
				$this->set_loc();
				
				echo '<div class="wrap">' . "\r\n" . 
				'<div id="icon-themes" class="icon32"></div>' . "\r\n";
				
				settings_errors(); // Prints the saved and error messages
				
				echo '<h2 class="nav-tab-wrapper">' . "\r\n" .
				 
				'<a href="' . $this->tab_url( 'general' ) . '" class="nav-tab' . 
					$this->active_tab( 'general' ) . '">' . __( 'General' ) . '</a>' . "\r\n" . 
				
				'<a href="' . $this->tab_url( 'events' ) . '" class="nav-tab' . 
					$this->active_tab( 'events' ) . '">' . __( 'Events' ) . '</a>' . "\r\n" . 
					
				'<a href="' . $this->tab_url( 'emails' ) . '" class="nav-tab' . 
					$this->active_tab( 'emails' ) . '">' . __( 'Email Settings' ) . '</a>' . "\r\n" . 
					
				'<a href="' . $this->tab_url( 'client-zone' ) . '" class="nav-tab' . 
					$this->active_tab( 'client-zone' ) . '">' . __( MDJM_APP ) . '</a>' . "\r\n" . 
					
				'<a href="' . $this->tab_url( 'payments' ) . '" class="nav-tab' . 
					$this->active_tab( 'payments' ) . '">' . __( 'Payment Settings' ) . '</a>' . "\r\n" . 
										
				'</h2>' . "\r\n";
				
				$this->print_nav_links();
				
				$this->exclude = array( 'client_text', 'mdjm_client_field_settings', 'mdjm_db_backups' );
				
				if( $this->current_section == 'mdjm_app_debugging' )
					$mdjm_debug->log_file_check();
					
				if( !in_array( $this->current_section, $this->exclude ) )
					echo '<form method="post" action="options.php">' . "\r\n";
					
			} // page_header
			
			/*
			 * End the page
			 *
			 *
			 *
			 */
			function page_footer()	{
				global $mdjm, $mdjm_debug;
				
				/* -- Don't display the save button for aitomated tasks page -- */
				if( isset( $_GET['task_action'] ) )
					return;
				
				if( !in_array( $this->current_section, $this->exclude ) )	{	
					if( current_user_can( 'manage_options' ) && $mdjm->_mdjm_validation() )
						submit_button();
						
					else
						echo '<p><a style="color:#a00" target="_blank" href="' . mdjm_get_admin_page( 'mydjplanner', 'str' ) . '">License Expired</a>.<br>' . 
						'You cannot update your settings without a valid MDJM License</p>' . "\r\n";
					
					echo '</form>' . "\r\n";
				}
				
				/* -- This is where we can display any additional fields. Will not be saved as options -- */
				if( $this->current_section == 'mdjm_app_debugging' )
					$mdjm_debug->submit_files_button();
			
				/* -- End the wrap div -- */
				echo '</div>' . "\r\n";
			} // page_footer
			
			/*
			 * Determine if the given tab is active
			 * if so, echo the CSS style
			 *
			 * @param	str		$tab	Required: The tab to query
			 */
			function active_tab( $tab )	{
				if( $tab == $this->current_tab )
					return ' nav-tab-active';
			} // active_tab
			
			/*
			 * Determine the link for the given tab
			 * 
			 *
			 * @param	str		$tab	Required: The tab to query
			 */
			function tab_url( $tab, $section='' )	{
				$section = !empty( $section ) ? '&section=' . $section : '';
				
				return admin_url( 'admin.php?page=mdjm-settings&tab=' . $tab . $section );
			} // tab_url
			
			/*
			 * Print out the section links within each tab
			 *
			 *
			 *
			 */
			function print_nav_links()	{				
				$links = array(
							'general'		=> array(
												'Application Settings'   => 'mdjm_app_settings',
												'Permissions'			=> 'mdjm_app_permissions',
												'Debugging'			  => 'mdjm_app_debugging',
												'Backups'				=> 'mdjm_db_backups',
												'Plugin Removal'		 => 'mdjm_app_uninstall',
												),
							'events'		=> array(
												'Event Settings' 		=> 'mdjm_events_settings',
												'Playlist Settings'	 => 'mdjm_playlist_settings',
												//'Event Staff'		   => 'mdjm_event_staff',
												),
							'emails'		=> array(
												'General Email Settings'	=> 'mdjm_email_settings',
												'Event Templates'		   => 'mdjm_email_templates_settings',
												
												),
							'client-zone'	=> array(
													MDJM_APP . ' General Settings' => 'mdjm_app_general',
													'Pages'						=> 'mdjm_app_pages',
													'Customised Text'			  => 'mdjm_app_text',
													'Client Fields'				=> 'mdjm_client_field_settings',
													'Availability Checker'		 => 'mdjm_availability_settings',
													),
							'payments'	=> array(
													'Payment Settings' 		=> 'mdjm_payment_settings',
													'PayPal Configuration'	=> 'mdjm_paypal_settings',
													),
							);
				if( !array_key_exists( $this->current_tab, $links ) )
					return;
					
				echo '<ul class="subsubsub">' . "\r\n"; 
				
				$sections = count( $links[$this->current_tab] );
				$i = 1;
				
				foreach( $links[$this->current_tab] as $name => $slug )	{
					echo '<li><a href="' . $this->tab_url( $this->current_tab, $slug ) . '"' . 
					( $slug == $this->current_section ? ' class="current"' : '' ) . 
					'>' . __( $name ) . '</a>' . ( $i < $sections ? ' | ' : '' ) . '</li>' . "\r\n";
					
					$i++;
				}
				echo '</ul>' . "\r\n";
				echo '<br class="clear">' . "\r\n";
			} // tab_links
			
/* ---------------------------------------------------------
		This is where we display the settings fields
--------------------------------------------------------- */
			/*
			 * Display the setting field as a hidden input
			 * These are used to hide settings that should not be displayed
			 * witihn the select nav link, but share the same option key
			 * to maintain their values
			 */
			function show_hidden_field( $args )	{
				echo '<input type="hidden" name="' . ( !empty( $args['key'] ) ? $args['key'] . '[' . $args['field'] . ']' 
					: $args['field'] ) . '" id="' . $args['field'] . '" ' . 
				'value="' . $args['value'] . '" /> ' . "\r\n";
			} // show_hidden_field
			
			/*
			 * Display the setting field as a text input
			 *
			 *
			 *
			 */
			function show_text_field( $args )	{
				echo '<input type="text" name="' . ( !empty( $args['key'] ) ? $args['key'] . '[' . $args['field'] . ']' 
					: $args['field'] ) . '" id="' . $args['field'] . '" ' . 
				( !empty( $args['class'] ) ? 'class="' . $args['class'] . '" ' : '' ) . 
				'value="' . esc_attr( $args['value'] ) . '" /> ' . "\r\n";
			} // show_text_field
			
			/*
			 * Display the setting field as a select input
			 *
			 *
			 *
			 */
			function show_select_field( $args )	{
				global $mdjm_settings;
				
				if( $args['custom_args']['list_type'] == 'page' ) // Pages
					wp_dropdown_pages( $args['custom_args'] );
					
				else	{
					echo '<select name="' . ( !empty( $args['key'] ) ? $args['key'] . '[' . $args['field'] . ']' 
					: $args['field'] ) . '" id="' . $args['field'] . '"' . 
					( !empty( $args['class'] ) ? ' class="' . $args['class'] . '"' : '' ) . 
					( $args['type'] == 'multiple_select' ? ' multiple="multiple"' : '' ) . 
					( !empty( $args['size'] ) ? ' size="' . $args['size'] . '"' : '' ) . 
					'>' . "\r\n";
					
					/* -- Select list with values passed via array -- */
					if( $args['custom_args']['list_type'] == 'defined' )	{
						foreach( $args['custom_args']['list_values'] as $key => $value )	{
							echo '<option value="' . $key . '"' . 
							selected( $args['value'], $key, false ) . 
							'>' . $value . '</option>' . "\n";
						}	
					}
					
					/* -- Shortcode Select List -- */
					elseif( $args['custom_args']['list_type'] == 'shortcode' )	{
						foreach( $args['custom_args']['list_values'] as $shortcode )	{
							echo '<option value="' . $shortcode . '"' . 
							( in_array( $shortcode, $mdjm_settings['permissions']['dj_disable_shortcode'] ) ? ' selected="selected"' : '' ) . 
							'>' . $shortcode . '</option>' . "\r\n";
						}
					}
					
					/* -- Contract Select List -- */
					elseif( $args['custom_args']['list_type'] == 'contract' )	{										
						$template_args = array(
										'post_type' 	  => MDJM_CONTRACT_POSTS,
										'posts_per_page' => -1,
										'orderby' 		=> 'name',
										'order' 		  => $args['custom_args']['sort_order'],
										);
						$templates = get_posts( $template_args );
						if( $templates )	{							
							foreach( $templates as $template )	{
								echo '<option value="' . $template->ID . '"' . 
								selected( $args['value'], $template->ID, false ) . 
								'>' . get_the_title( $template->ID ) . '</option>' . "\n";
							}
						}
					}
					
					/* -- Email Template Select List -- */
					elseif( $args['custom_args']['list_type'] == 'email_template' )	{										
						$template_args = array(
										'post_type' 	  => MDJM_EMAIL_POSTS,
										'posts_per_page' => -1,
										'orderby' 		=> 'name',
										'order' 		  => $args['custom_args']['sort_order'],
										);
						$templates = get_posts( $template_args );
						if( !empty( $args['custom_args']['first_entry'] ) )	{
							echo '<option value="0"' . 
							selected( $args['value'], '0', false ) . 
							'>Do Not Email</option>' . "\r\n";
						}
						if( $templates )	{							
							foreach( $templates as $template )	{
								echo '<option value="' . $template->ID . '"' . 
								selected( $args['value'], $template->ID, false ) . 
								'>' . get_the_title( $template->ID ) . '</option>' . "\n";
							}
						}
					}
					
					/* -- Template Select List -- */
					elseif( $args['custom_args']['list_type'] == 'templates' )	{
						$template_types = array(
										MDJM_EMAIL_POSTS	=> 'EMAIL TEMPLATES',
										MDJM_CONTRACT_POSTS => 'CONTRACT TEMPLATES',
										);
										
						foreach( $template_types as $template_type => $template_name )	{
							$template_args = array(
											'post_type' 	  => $template_type,
											'posts_per_page' => -1,
											'orderby' 		=> 'name',
											'order' 		  => 'ASC',
											);
							$templates = get_posts( $template_args );
							if( $templates )	{
								echo '<option value="--- ' . $template_type . ' ---" disabled>--- ' . $template_name . ' ---</option>' . "\r\n";
								
								foreach( $templates as $template )	{
									echo '<option value="' . $template->ID . '"';
									if( in_array( $template->ID, $mdjm_settings['permissions']['dj_disable_template'] ) )
										echo ' selected="selected"';
									echo '>' . get_the_title( $template->ID ) . '</option>' . "\n";
								}
							}
						}
					}
					
					echo '</select>' . "\r\n";
				}
			} // show_select_field
			
			/*
			 * Display the setting field as a checkbox input
			 *
			 *
			 *
			 */
			function show_checkbox_field( $args )	{
				global $mdjm;
				
				if( $args['field'] == 'show_credits' )
					$status = $mdjm->_mdjm_validation();
				
				$true_vals = array(
								'show_dashboard', 'show_credits', 'enable', 'warn', 'auto_purge', 'employer', 
								'enable_packages', 'journaling', 'track_client_emails', 'bcc_dj_to_client', 
								'bcc_admin_to_client', 'contract_to_client', 'booking_conf_to_client', 
								'booking_conf_to_dj', 'notify_profile', 'update_event', 'custom_client_text', 'enable_tax',
								'enable_paypal', 'enable_sandbox', 'paypal_debug', 'dj_see_wp_dash', 'dj_add_client',
								'dj_add_event', 'dj_view_enquiry', 'dj_add_venue', 'dj_see_deposit', 'upload_playlists'
								);
				
				$value = ( in_array( $args['field'], $true_vals ) ? '1' : 'Y' );
				
				echo '<input type="checkbox" name="' . ( !empty( $args['key'] ) ? $args['key'] . '[' . $args['field'] . ']' 
					: $args['field'] ) . '" id="' . $args['field'] . '"' . 
				checked( $args['value'], $value, false ) . 
				' value="' . $value . '"' . 
				
				/* -- For trial versions and expired licenses the credits cannot be removed -- */
				( $args['field'] == 'show_credits' && ( empty( $status ) || $status['type'] == 'trial' ) ? ' disabled="disabled"' : '' ) . 
				' />' . 
				
				( $args['field'] == 'show_credits' && ( empty( $status ) || $status['type'] == 'trial' ) ? 
					' This option can only be turned off once the MDJM plugin has been purchased' : '' ) . "\r\n"; 
			} // show_checkbox_field
			
			/*
			 * Display the setting field as a radio input
			 *
			 *
			 *
			 */
			function show_radio_field( $args )	{
				global $mdjm;
												
				$i = 0;
				foreach( $args['custom_args']['values'] as $radio )	{
					echo '<label>' . "\n";
					echo '<input type="radio" name="' . $args['key'] . '[' . $args['field'] . ']" value="' . $radio . '" id="' . $radio . '" ' . 
					checked( $args['value'], $radio, false ) . ' />' . "\n";
					echo '<img src="https://www.paypalobjects.com/en_GB/i/btn/' . $radio . '">';
					echo '</label>' . "\n";
					$i++;
					if( $i != count( $args['custom_args']['values'] ) )	{
						echo '<br />' . "\n";	
					}
				}
			} // show_radio_field
			
			/*
			 * Display the setting field as a textarea input
			 *
			 *
			 *
			 */
			function show_textarea_field( $args )	{
				echo '<textarea name="' . ( !empty( $args['key'] ) ? $args['key'] . '[' . $args['field'] . ']' 
					: $args['field'] ) . '" id="' . $args['field'] . '" ' . 
				'cols="80" rows="6" ' . 
				( !empty( $args['class'] ) ? 'class="' . $args['class'] . '" ' : '' ) . 
				'>' . $args['value'] . '</textarea>' . "\r\n";
			} // show_textarea_field
			
			/*
			 * Display the setting field as a textarea input with tinyMCE
			 *
			 *
			 *
			 *
			 */
			function show_mce_textarea_field( $args )	{
				wp_editor( $args['value'], $args['field'], $args['custom_args']['mce_settings'] );
			} // show_mce_textarea_field
			
			/*
			 * Help text for settings pages
			 *
			 *
			 *
			 */
			function help_text( $contextual_help, $screen_id, $screen )	{
				$current = ( isset( $_GET['section'] ) ? $_GET['section'] : '' );
				
				switch( $current )	{
					case 'mdjm_client_field_settings':
						$contextual_help = 
						'<p>' . __( 'By managing Client Fields, you can determine which information you capture and store for each of your clients. ' .
						'Each field listed below, whether default or create by you, will be displayed on the ' . MDJM_APP . ' profile page ' . 
						'when visited by a client. As long as it is enabled.<br />' . 
						'For further assistance, refer to our <a href="' . mdjm_get_admin_page( 'user_guides' ) . '" target="_blank">User Guides</a>' .
						' or visit the <a href="' . mdjm_get_admin_page( 'mydjplanner' ) . '" target="_blank">' . MDJM_NAME . '</a> ' . 
						'<a href="' . mdjm_get_admin_page( 'mdjm_forums' ) . '" target="_blank">Support Forums' ) . '</a></p>' . "\r\n";
					break;
					
					default:
						$contextual_help = 
						'<p>' . __( 'For assistance, refer to our <a href="' . mdjm_get_admin_page( 'user_guides' ) . '" target="_blank">User Guides</a>' .
						' or visit the <a href="' . mdjm_get_admin_page( 'mydjplanner' ) . '" target="_blank">' . MDJM_NAME . '</a> ' . 
						'<a href="' . mdjm_get_admin_page( 'mdjm_forums' ) . '" target="_blank">Support Forums' ) . '</a></p>' . "\r\n";
					break;
					
				} // switch
				
				return $contextual_help;
			} // help_text
		} // Class MDJM_Settings
	}	