<?php
	defined( 'ABSPATH' ) or die( "Direct access to this page is disabled!!!" );
	
/**
 * Class Name: MDJM_Posts
 * Manage generic post functions within the MDJM application.
 * 
 *
 *
 */
if( !class_exists( 'MDJM_Posts' ) )	:
	class MDJM_Posts	{
		
		/**
		 * The Constructor
		 */
		public function __construct()	{
			global $mdjm_post_types;

			// Include custom post files
			$this->includes();

			/* -- Register actions -- */
			add_action( 'manage_posts_custom_column', array( &$this, 'define_custom_post_column_data' ), 10, 1 ); // Data displayed in post columns
			add_action( 'edit_form_top', array( &$this, 'check_user_permission' ) ); // Permissions
			add_action( 'admin_head', array( &$this, 'mdjm_admin_head' ) ); // Execute the admin_head hook
			add_action( 'edit_form_after_title', array( &$this, 'set_post_title' ) ); // Set the post title for Custom posts
			add_action( 'contextual_help', array( &$this, 'help_text' ), 10, 3 ); // Contextual help
			add_action( 'restrict_manage_posts', array( &$this, 'post_filter_list' ) ); // Filter dropdown boxes
			
			/* -- Register filters -- */
			foreach( $mdjm_post_types as $mdjm_post_type )	{ // Post columns
				if( method_exists( $this, 'define_' . str_replace( '-', '_', $mdjm_post_type ) . '_post_columns' ) )
					add_filter( 'manage_' . $mdjm_post_type . '_posts_columns' , array( &$this, 'define_' . str_replace( '-', '_', $mdjm_post_type ) . '_post_columns' ) );
			}
			
			/* -- Bulk Actions -- */
			add_filter( 'bulk_actions-edit-mdjm-quotes', array( &$this, 'define_mdjm_quotes_bulk_action_list' ) );
						
			if( is_admin() )	{
				add_filter( 'posts_clauses', array( &$this, 'column_sort' ), 1, 2 );
				add_action( 'pre_get_posts', array( &$this, 'pre_post' ) ); // Actions for pre_get_posts
				add_filter( 'parse_query', array( &$this, 'custom_post_filter' ) ); // Actions for filtered queries
				
				add_filter( 'post_row_actions', array( &$this, 'define_custom_post_row_actions' ), 10, 2 ); // Row actions
				add_filter( 'post_updated_messages', array( &$this, 'custom_post_status_messages' ) ); // Status messages
				add_filter( 'gettext', array( &$this, 'rename_publish_button' ), 10, 2 ); // Set the value of the submit button
				add_filter( 'enter_title_here', array( &$this, 'title_placeholder' ) ); // Set the title placeholder text
			}

		} // __construct()
		
		/**
		 * Call include files for custom post types
		 *
		 *
		 *
		 */
		function includes()	{
			include_once( 'mdjm-contract-posts.php' );
			include_once( 'mdjm-event-posts.php' );
			include_once( 'mdjm-transaction-posts.php' );
			include_once( 'mdjm-venue-posts.php' );
			include_once( 'mdjm-communications-posts.php' );
		} // includes

/**
* -- POST SAVES
*/
		/*
		 * save_custom_post
		 * Launched as a post is saved, or edited
		 * Calls mdjm_custom_post_save
		 *
		 */
		public function save_custom_post( $post_id, $post )	{
			global $mdjm, $mdjm_post_types, $mdjm_settings, $current_user;
			
		/* -- Only for MDJM custom posts -- */
			if( !in_array( $post->post_type, $mdjm_post_types ) )
				return;
				
		/* -- Do not save if this is an autosave -- */
			if( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE )
				return;
				
			if( MDJM_DEBUG == true )
				 $GLOBALS['mdjm_debug']->log_it( '*** Starting Custom Post Type Save ***' . "\r\n", true );
										
		/* -- Security Verification -- */
			if( !isset( $_POST['mdjm_update_custom_post'] ) || $_POST['mdjm_update_custom_post'] != 'mdjm_update' )	{
				if( MDJM_DEBUG == true )
					 $GLOBALS['mdjm_debug']->log_it( '	ERROR: MDJM fields not defined' );
				return $post_id;
			}
			
		/* -- The saves -- */
			switch( $post->post_type )	{
		/* -- Contract Post Saves -- */
			case MDJM_CONTRACT_POSTS:
				if( MDJM_DEBUG == true )
					 $GLOBALS['mdjm_debug']->log_it( 'POST TYPE: ' . strtoupper( MDJM_CONTRACT_POSTS ) );
				/* -- Permission Check -- */
				if( !current_user_can( 'administrator' ) )
					return $post_id;
				
				/* -- The Save -- */
				$current_meta_value = get_post_meta( $post_id, '_contract_description', true );
				
				/* -- If we have a value and the key did not exist previously, add it -- */
				if ( $_POST['contract_description'] && '' == $current_meta_value )
					add_post_meta( $post_id, '_contract_description', $_POST['contract_description'], true );
				
				/* -- If a value existed, but has changed, update it -- */
				elseif ( $_POST['contract_description'] && $_POST['contract_description'] != $current_meta_value )
					update_post_meta( $post_id, '_contract_description', $_POST['contract_description'] );
					
				/* If there is no new meta value but an old value exists, delete it. */
				elseif ( '' == $_POST['contract_description'] && $current_meta_value )
					delete_post_meta( $post_id, '_contract_description', $meta_value );
			
		/* -- Venue Post Saves -- */
			case MDJM_VENUE_POSTS:
				if( MDJM_DEBUG == true )
					 $GLOBALS['mdjm_debug']->log_it( 'POST TYPE: ' . strtoupper( MDJM_VENUE_POSTS ) );
				/* -- Permission Check -- */
				if( !current_user_can( 'administrator' ) && !dj_can( 'add_venue' ) )
					return $post_id;
					
				/* -- Loop through all fields sanitizing and updating as required -- */	
				foreach( $_POST as $meta_key => $new_meta_value )	{
					/* -- We're only interested in 'venue_' fields -- */
					if( substr( $meta_key, 0, 6 ) == 'venue_' )	{
						$current_meta_value = get_post_meta( $post_id, '_' . $meta_key, true );
						
						if( $meta_key == 'venue_postcode' && !empty( $new_meta_value ) )
							$new_meta_value = strtoupper( $new_meta_value );
						
						if( $meta_key == 'venue_email' && !empty( $new_meta_value ) )
							$new_meta_value = sanitize_email( $new_meta_value );
							
						else
							$new_meta_value = sanitize_text_field( ucwords( $new_meta_value ) );
						
						/* -- If we have a value and the key did not exist previously, add it -- */
						if ( $new_meta_value && '' == $current_meta_value )
							add_post_meta( $post_id, '_' . $meta_key, $new_meta_value, true );
						
						/* -- If a value existed, but has changed, update it -- */
						elseif ( $new_meta_value && $new_meta_value != $current_meta_value )
							update_post_meta( $post_id, '_' . $meta_key, $new_meta_value );
							
						/* If there is no new meta value but an old value exists, delete it. */
						elseif ( '' == $new_meta_value && $current_meta_value )
							delete_post_meta( $post_id, '_' . $meta_key, $meta_value );
					}
				}
				break;
			
			/* Transaction Post Saves -- */
			case MDJM_TRANS_POSTS:
				if( MDJM_DEBUG == true )
					 $GLOBALS['mdjm_debug']->log_it( 'POST TYPE: ' . strtoupper( MDJM_TRANS_POSTS ) );
				/* -- Permission Check -- */
				if( !current_user_can( 'administrator' ) )
					return $post_id;
							
				$trans_type = get_term( $_POST['mdjm_transaction_type'], 'transaction-types' );
				
				/* -- Post Data -- */
				$trans_data['ID'] = $post->ID;
				$trans_data['post_status'] = ( $_POST['transaction_direction'] == 'Out' ? 'mdjm-expenditure' : 'mdjm-income' );
				$trans_data['post_date'] = date( 'Y-m-d H:i:s', strtotime( $_POST['transaction_date'] ) );
				$trans_data['edit_date'] = true;
					
				$trans_data['post_author'] = $current_user->ID;
				$trans_data['post_type'] = MDJM_TRANS_POSTS;
				$trans_data['post_category'] = array( $_POST['mdjm_transaction_type'] );
				
				/* -- Post Meta -- */
				$trans_meta['_mdjm_txn_status'] = sanitize_text_field( $_POST['transaction_status'] );
				$trans_meta['_mdjm_txn_source'] = sanitize_text_field( $_POST['transaction_src'] );
				$trans_meta['_mdjm_txn_total'] = number_format( $_POST['transaction_amount'], 2 );
				$trans_meta['_mdjm_txn_notes'] = sanitize_text_field( $_POST['transaction_description'] );
				
				if( $_POST['transaction_direction'] == 'In' )
					$trans_meta['_mdjm_payment_from'] = sanitize_text_field( $_POST['transaction_payee'] );
				elseif( $_POST['transaction_direction'] == 'Out' )
					$trans_meta['_mdjm_payment_to'] = sanitize_text_field( $_POST['transaction_payee'] );
												
				$trans_meta['_mdjm_txn_currency'] = $mdjm_settings['payments']['currency'];
				
				/* -- Create the transaction post -- */
				if( MDJM_DEBUG == true )
					 $GLOBALS['mdjm_debug']->log_it( 'Updating the post' );
				remove_action( 'save_post', array( &$this, 'save_custom_post' ), 10, 2 );
				wp_update_post( $trans_data );
				
				/* -- Set the transaction Type -- */
				if( MDJM_DEBUG == true )
					 $GLOBALS['mdjm_debug']->log_it( 'Setting the transaction type' );													
				wp_set_post_terms( $post->ID, $_POST['mdjm_transaction_type'], 'transaction-types' );
				
				/* -- Add the meta data -- */
				if( MDJM_DEBUG == true )
					 $GLOBALS['mdjm_debug']->log_it( 'Updating the post meta' );
				foreach( $trans_meta as $meta_key => $new_meta_value )	{
					$current_meta_value = get_post_meta( $post_id, $meta_key, true );
					
					/* -- If we have a value and the key did not exist previously, add it -- */
					if ( $new_meta_value && '' == $current_meta_value )
						add_post_meta( $post_id, $meta_key, $new_meta_value, true );
					
					/* -- If a value existed, but has changed, update it -- */
					elseif ( $new_meta_value && $new_meta_value != $current_meta_value )
						update_post_meta( $post_id, $meta_key, $new_meta_value );
						
					/* If there is no new meta value but an old value exists, delete it. */
					elseif ( '' == $new_meta_value && $current_meta_value )
						delete_post_meta( $post_id, $meta_key, $new_meta_value );
				}
				add_action( 'save_post', array( &$this, 'save_custom_post' ), 10, 2 );
				break;
		
		/* Event Post Saves -- */
			case MDJM_EVENT_POSTS:
				if( MDJM_DEBUG == true )
					 $GLOBALS['mdjm_debug']->log_it( 'POST TYPE: ' . strtoupper( MDJM_EVENT_POSTS ) );
				
				/* -- Permission Check -- */
				if( !current_user_can( 'administrator' ) || dj_can( 'dj_add_event' ) )
					return $post_id;
				
				/* Check if this is a new or existing post */
				$new_post = $post->post_status == 'auto-draft' ? true : false;
				
				/* -- Use this to capture changes for existing posts for the journal -- */
				$current_meta = get_post_meta( $post->ID );
				
				/* -- Get the Client ID -- */
				$event_data['_mdjm_event_client'] = ( $_POST['client_name'] != 'add_new' ? 
					$_POST['client_name'] : $mdjm->mdjm_events->mdjm_add_client() );
					
				if( $new_post != true && $_POST['client_name'] != $current_meta['_mdjm_event_client'][0] )
					$field_updates[] = '     | Client changed from ' . $current_meta['_mdjm_event_client'][0] . ' to ' . $_POST['client_name'];
				
				if( empty( $_POST['client_name'] ) )	{
					if( MDJM_DEBUG == true )
						 $GLOBALS['mdjm_debug']->log_it( '	-- No content passed for filtering ' );
				}
				
				/**
				 * For new events we run the 'mdjm_add_new_event' action
				 *
				 *
				 *
				 */
				if( $new_post == true )
					do_action( 'mdjm_add_new_event', $post );
				
				if( !empty( $_POST['mdjm_reset_pw'] ) )	{
					if( MDJM_DEBUG == true )
						$GLOBALS['mdjm_debug']->log_it( '	-- User ' . $event_data['_mdjm_event_client'] . ' flagged for password reset' );
						
					update_user_meta( $event_data['_mdjm_event_client'], 'mdjm_pass_action', wp_generate_password( $mdjm_settings['clientzone']['pass_length'] ) );
				}
									
				/* -- Get the Venue ID -- */
				$event_data['_mdjm_event_venue_id'] = ( $_POST['venue_id'] != 'manual' && $_POST['venue_id'] != 'client' ? 
					$_POST['venue_id'] : ( !empty( $_POST['_mdjm_event_venue_id'] ) && $_POST['_mdjm_event_venue_id'] == 'client' ? 'client' : 'manual' ) );
					
				if( $new_post === false && isset( $current_meta['_mdjm_event_venue_id'][0] ) && $_POST['venue_id'] != $current_meta['_mdjm_event_venue_id'][0] )	{
					$field_updates[] = 'Venue changed from ' . ( $current_meta['_mdjm_event_venue_id'][0] != 'manual' ?
									   get_the_title( $current_meta['_mdjm_event_venue_id'][0] ) : $current_meta['_mdjm_event_venue_name'][0] ) .
									   ' to ' . ( is_numeric( $_POST['venue_id'] ) && $this->post_exists( $_POST['venue_id'] ) ?
									   get_the_title( $_POST['venue_id'] ) : $_POST['venue_id'] );
				}
				
				/* -- Create a new venue -- */
				if( $_POST['venue_id'] == 'manual' && !empty( $_POST['save_venue'] ) )	{
					foreach( $_POST as $venue_key => $venue_value )	{
						if( substr( $venue_key, 0, 6 ) == 'venue_' )	{
							$venue_meta[$venue_key] = $venue_value;
							
							if( $venue_key == 'venue_postcode' && !empty( $venue_value ) )
								$venue_meta[$venue_key] = strtoupper( $venue_value );
							
							if( $venue_key == 'venue_email' && !empty( $venue_value ) )
								$venue_meta[$venue_key] = sanitize_email( $venue_value );
								
							else
								$venue_meta[$venue_key] = sanitize_text_field( ucwords( $venue_value ) );
						}
					}
					/* -- Create the venue -- */
					if( MDJM_DEBUG == true )
						$GLOBALS['mdjm_debug']->log_it( '	-- New venue to be created' );
					remove_action( 'save_post', array( &$this, 'save_custom_post' ), 10, 2 );
					$event_data['_mdjm_event_venue_id'] = $mdjm->mdjm_events->mdjm_add_venue( 
																				array( 'venue_name' => $_POST['venue_name'] ), 
																				$venue_meta );
					add_action( 'save_post', array( &$this, 'save_custom_post' ), 10, 2 );
				}
				/* -- Manual venue, set event fields -- */
				else	{
					if( $_POST['venue_id'] != 'client' )	{ // Don't use client address
						$event_data['_mdjm_event_venue_name'] = sanitize_text_field( ucwords( $_POST['venue_name'] ) );
						$event_data['_mdjm_event_venue_contact'] = sanitize_text_field( ucwords( $_POST['venue_contact'] ) );
						$event_data['_mdjm_event_venue_phone'] = sanitize_text_field( $_POST['venue_phone'] );
						$event_data['_mdjm_event_venue_email'] = sanitize_email( strtolower( $_POST['venue_email'] ) );
						$event_data['_mdjm_event_venue_address1'] = sanitize_text_field( ucwords( $_POST['venue_address1'] ) );
						$event_data['_mdjm_event_venue_address2'] = sanitize_text_field( ucwords( $_POST['venue_address2'] ) );
						$event_data['_mdjm_event_venue_town'] = sanitize_text_field( ucwords( $_POST['venue_town'] ) );
						$event_data['_mdjm_event_venue_county'] = sanitize_text_field( ucwords( $_POST['venue_county'] ) );
						$event_data['_mdjm_event_venue_postcode'] = strtoupper( sanitize_text_field( $_POST['venue_postcode'] ) );
					}
					else	{
						$client_data = get_userdata( $event_data['_mdjm_event_client'] );
						$event_data['_mdjm_event_venue_name'] = __( 'Client Address', 'mobile-dj-manager' );
						$event_data['_mdjm_event_venue_contact'] = !empty( $client_data->first_name ) ? sanitize_text_field( $client_data->first_name ) : '';
						$event_data['_mdjm_event_venue_contact'] .= ' ' . !empty( $client_data->last_name ) ? sanitize_text_field( $client_data->last_name ) : '';
						$event_data['_mdjm_event_venue_phone'] = !empty( $client_data->phone1 ) ? $client_data->phone1 : '';
						$event_data['_mdjm_event_venue_email'] = !empty( $client_data->user_email ) ? $client_data->user_email : '';
						$event_data['_mdjm_event_venue_address1'] = !empty( $client_data->address1 ) ? $client_data->address1 : '';
						$event_data['_mdjm_event_venue_address2'] = !empty( $client_data->address2 ) ? $client_data->address2 : '';
						$event_data['_mdjm_event_venue_town'] = !empty( $client_data->town ) ? $client_data->town : '';
						$event_data['_mdjm_event_venue_county'] = !empty( $client_data->county ) ? $client_data->county : '';
						$event_data['_mdjm_event_venue_postcode'] = !empty( $client_data->postcode ) ? $client_data->postcode : '';
					}
				}
				
				/* -- Prepare the remaining event fields -- */
				$event_data['_mdjm_event_last_updated_by'] = $current_user->ID;
				
				// Event name
				$_POST['_mdjm_event_name'] = ( !empty( $_POST['_mdjm_event_name'] ) ? $_POST['_mdjm_event_name'] : 
					get_term( $_POST['mdjm_event_type'], 'event-types' )->name );
										
				if( $new_post == true || empty( $current_meta['_mdjm_event_playlist_access'][0] ) )
					$event_data['_mdjm_event_playlist_access'] = $mdjm->mdjm_events->playlist_ref();
				
				// Playlist Enabled
				$event_data['_mdjm_event_playlist'] = !empty( $_POST['enable_playlist'] ) ? $_POST['enable_playlist'] : 'N';
				
				foreach( $_POST as $key => $value )	{
					if( substr( $key, 0, 12 ) == '_mdjm_event_' )
						$event_data[$key] = $value;	
				}
				/* -- Set the event & dj setup times -- */
					/* -- 24 hour format -- */
					if( MDJM_TIME_FORMAT == 'H:i' )	{
						$event_data['_mdjm_event_start'] = date( 'H:i:s', strtotime( $_POST['event_start_hr'] . ':' . $_POST['event_start_min'] ) ); 
						$event_data['_mdjm_event_finish'] = date( 'H:i:s', strtotime( $_POST['event_finish_hr'] . ':' . $_POST['event_finish_min'] ) );
						$event_data['_mdjm_event_djsetup_time'] = date( 'H:i:s', strtotime( $_POST['dj_setup_hr'] . ':' . $_POST['dj_setup_min'] ) );
					}
					/* -- 12 hour format -- */
					else	{
						$event_data['_mdjm_event_start'] = date( 'H:i:s', strtotime( $_POST['event_start_hr'] . ':' . $_POST['event_start_min'] . $_POST['event_start_period'] ) );
						$event_data['_mdjm_event_finish'] = date( 'H:i:s', strtotime( $_POST['event_finish_hr'] . ':' . $_POST['event_finish_min'] . $_POST['event_finish_period'] ) );
						$event_data['_mdjm_event_djsetup_time'] = date( 'H:i:s', strtotime( $_POST['dj_setup_hr'] . ':' . $_POST['dj_setup_min'] . $_POST['dj_setup_period'] ) );
					}
					
					// Set the event end date. If the finish time is less than the start time, assume following day
					if( date( 'H', strtotime( $event_data['_mdjm_event_finish'] ) ) > date( 'H', strtotime( $event_data['_mdjm_event_start'] ) ) )
						$event_data['_mdjm_event_end_date'] = $_POST['_mdjm_event_date'];
						
					else
						$event_data['_mdjm_event_end_date'] = date( 'Y-m-d', strtotime( '+1 day', strtotime( $_POST['_mdjm_event_date'] ) ) );
					
					/* -- Deposit & Balance -- */
					$event_data['_mdjm_event_deposit_status'] = !empty( $_POST['deposit_paid'] ) ? $_POST['deposit_paid'] : 'Due';
					$event_data['_mdjm_event_balance_status'] = !empty( $_POST['balance_paid'] ) ? $_POST['balance_paid'] : 'Due';
					
					$deposit_payment = $event_data['_mdjm_event_deposit_status'] == 'Paid' && $current_meta['_mdjm_event_deposit_status'][0] != 'Paid' ? true : false;
					$balance_payment = $event_data['_mdjm_event_balance_status'] == 'Paid' && $current_meta['_mdjm_event_deposit_status'][0] != 'Paid' ? true : false;
					
					/* -- Add-Ons -- */
					if( MDJM_PACKAGES == true )
						$event_data['_mdjm_event_addons'] = !empty( $_POST['event_addons'] ) ? $_POST['event_addons'] : '';
					
					/* -- Assign the event type -- */
					$existing_event_type = wp_get_object_terms( $post->ID, 'event-types' );
					if( !isset( $existing_event_type[0] ) || $existing_event_type[0]->term_id != $_POST['mdjm_event_type'] )
						$field_updates[] = 'Event Type changed to ' . get_term( $_POST['mdjm_event_type'], 'event-types' )->name;
					
					$mdjm->mdjm_events->mdjm_assign_event_type( $_POST['mdjm_event_type'] );
					
					/* -- Add the meta -- */
					if( MDJM_DEBUG == true )
						 $GLOBALS['mdjm_debug']->log_it( '	-- Beginning Meta Updates' );
						 
					foreach( $event_data as $event_meta_key => $event_meta_value )	{
						// If the field value is empty, skip it
						if( empty( $event_meta_value ) )
							continue;
						
						if( $event_meta_key == '_mdjm_event_cost' || $event_meta_key == '_mdjm_event_deposit' )
							$event_meta_value = $event_meta_value;
						
						if( $event_meta_key == 'venue_postcode' && !empty( $event_meta_value ) )
							$event_meta_value = strtoupper( $event_meta_value );
							
						if( $event_meta_key == 'venue_email' && !empty( $event_meta_value ) )
							$event_meta_value = strtolower( $event_meta_value );
														
						if( $event_meta_key == '_mdjm_event_package' )
							$event_meta_value = sanitize_text_field( strtolower( $event_meta_value ) );	
							
						elseif( $event_meta_key == '_mdjm_event_addons' )
							$event_meta_value = $event_meta_value;
							
						elseif( !strpos( $event_meta_key, 'notes' ) )
							$event_meta_value = sanitize_text_field( ucwords( $event_meta_value ) );
							
						else
							$event_meta_value = sanitize_text_field( ucfirst( $event_meta_value ) );
						
						/* -- If we have a value and the key did not exist previously, add it -- */
						if ( $event_meta_value && ( empty( $current_meta[$event_meta_key] ) || '' == $current_meta[$event_meta_key][0] ) )	{
							add_post_meta( $post->ID, $event_meta_key, $event_meta_value );
							if( $new_post === false )
								$field_updates[] = 'Field ' . $event_meta_key . ' added: ' . $event_meta_value;
						}
						/* -- If a value existed, but has changed, update it -- */
						elseif ( $event_meta_value && $event_meta_value != $current_meta[$event_meta_key][0] )	{
							update_post_meta( $post->ID, $event_meta_key, $event_meta_value );
							if( $new_post === false )
								$field_updates[] = 'Field ' . $event_meta_key . ' updated: ' . $current_meta[$event_meta_key][0] . ' replaced with ' . $event_meta_value;
						}
							
						/* If there is no new meta value but an old value exists, delete it. */
						elseif ( '' == $event_meta_value && $current_meta[$event_meta_key][0] )	{
							delete_post_meta( $post->ID, $event_meta_key, $event_meta_value );
							if( $new_post === false )
								$field_updates[] = 'Field ' . $event_meta_key . ' updated: ' . $current_meta[$event_meta_key][0] . ' removed';
						}
					}
					if( MDJM_DEBUG == true )
						$GLOBALS['mdjm_debug']->log_it( '	-- Meta Updates Completed     ' . "\r\n" . '| ' .
							( !empty( $field_updates ) ? implode( "\r\n" . '     | ', $field_updates ) : '' )  );
					
					/**
					 * Check for manual payment received & process
					 * This needs to be completed before we send any emails to ensure shortcodes are correct
					 */
					if( $deposit_payment == true || $balance_payment == true )	{
						if( $balance_payment == true )
							$type = MDJM_BALANCE_LABEL;
							
						else
							$type = MDJM_DEPOSIT_LABEL;
						/* -- Initiate transactions class -- */
						if( !class_exists( 'MDJM_Transactions' ) )
							require( MDJM_PLUGIN_DIR . '/admin/includes/transactions/mdjm-transactions.php' );
							
						$mdjm_trans = new MDJM_Transactions();
						$mdjm_trans->manual_event_payment( $type, $post->ID );
					}
							
					/* -- Set the status & initiate the specific event type tasks -- */
					if( $_POST['original_post_status'] != $_POST['mdjm_event_status'] )	{
						$event_stati = get_event_stati();
						$field_updates[] = 'Event status ' . 
											( isset( $event_stati[$_POST['original_post_status']] ) ? 'set ' : 'changed from ' . $event_stati[$_POST['original_post_status']] ) . 
											' to ' . $event_stati[$_POST['mdjm_event_status']];
						
						remove_action( 'save_post', array( &$this, 'save_custom_post' ), 10, 2 );
						wp_transition_post_status( $_POST['mdjm_event_status'], $_POST['original_post_status'], $post );
						wp_update_post( array( 'ID' => $post->ID, 'post_status' => $_POST['mdjm_event_status'] ) );
						$method = 'status_' . substr( $_POST['mdjm_event_status'], 5 );
						
						if( method_exists( $mdjm->mdjm_events, $method ) )
							$mdjm->mdjm_events->$method( $post_id, $post, $event_data, $field_updates );
						
						add_action( 'save_post', array( &$this, 'save_custom_post' ), 10, 2 );
					} // if( $_POST['original_post_status'] != $_POST['mdjm_event_status'] )
					else	{		
						/* -- Update Journal with event updates -- */
						if( MDJM_JOURNAL == true )	{
							if( MDJM_DEBUG == true )
								$GLOBALS['mdjm_debug']->log_it( '	-- Adding journal entry' );
								
							$mdjm->mdjm_events->add_journal( array(
										'user' 			=> get_current_user_id(),
										'comment_content' => 'Event ' . ( !empty( $new_post ) ? 'created' : 'updated' ) . ' via Admin <br /><br />' .
															 ( isset( $field_updates ) ? implode( '<br />', $field_updates ) : '' ) . '<br />(' . time() . ')',
										'comment_type' 	=> 'mdjm-journal',
										),
										array(
											'type' 		  => 'create-event',
											'visibility'	=> '1',
										) );
						}
						else	{
							if( MDJM_DEBUG == true )
								$GLOBALS['mdjm_debug']->log_it( '	-- Journalling is disabled' );	
						}
					}
					/**
					 * For all events we run the 'mdjm_save_event' action
					 *
					 *
					 *
					 */
					do_action( 'mdjm_save_event', $post, $_POST['mdjm_event_status'] );
				break;
			} // switch

		} // save_custom_post

/**
* -- POST DATA
*/
		/*
		 * pre_post
		 * Actions during the pre_get_posts hook
		 * 
		 * @since 1.1.3
		 * @params: 
		 * @return: 
		 */ 
		public function pre_post( $query )	{
			global $mdjm, $current_user;
			
			if( !is_user_logged_in() )
				return;
			
			/* -- Set query for DJ's to see only their own events -- */
			if( !current_user_can( 'administrator' ) || isset( $_GET['dj'] ) )
				$this->dj_events_filter( $query );
							
			/* -- Filter events by Client -- */
			if( isset( $_GET['client'] ) )	{
				if( !current_user_can( 'administrator' ) && !$mdjm->mdjm_events->is_my_client( $_GET['client'] ) )
					wp_die( 'Tut tut... you can only search your own Clients' );
				
				$this->client_events_filter( $query );	
			}
			
			/* -- Filter posts by Type -- */
			if( isset( $_GET['mdjm_filter_type'] ) )
				$this->post_types_query( $query );
							
		} // pre_post
		
		function custom_post_filter( $query ){
			global $pagenow;
			
			if( !isset( $_GET['mdjm_filter_type'] ) || !isset( $_GET['mdjm_filter_date'] ) )
				return;
			
			$type = 'post';
			
			if( isset( $_GET['post_type'] ) )
				$type = $_GET['post_type'];
			
			if( MDJM_EVENT_POSTS == $type && is_admin() && $pagenow=='edit.php' )	{
				if( isset( $_GET['mdjm_filter_date'] ) && $_GET['mdjm_filter_date'] != '' && $_GET['mdjm_filter_date'] != '0' )	{
					$start = date( 'Y-m-d', strtotime( substr( $_GET['mdjm_filter_date'], 0, 4 ) . '-' . substr( $_GET['mdjm_filter_date'], -2 ) . '-01' ) );
					$end = date( 'Y-m-t', strtotime( $start ) );
					$query->query_vars['meta_query'] = array(
														array(
															  'key' => '_mdjm_event_date',
															  'value' => array( $start, 
																				$end ),
															  'compare' => 'BETWEEN',
														) );
				}
				if( isset( $_GET['mdjm_filter_dj'] ) && $_GET['mdjm_filter_dj'] != '0' )	{
					$query->query_vars['meta_query'] = array(
														array(
															  'key' => '_mdjm_event_dj',
															  'value' => $_GET['mdjm_filter_dj'],
															  'compare' => '==',
														) );
				}
				if( isset( $_GET['mdjm_filter_client'] ) && $_GET['mdjm_filter_client'] != '0' )	{
					$query->query_vars['meta_query'] = array(
														array(
															  'key' => '_mdjm_event_client',
															  'value' => $_GET['mdjm_filter_client'],
															  'compare' => '==',
														) );
				}
			}
		} // custom_post_filter
		
		/*
		 * availability_check
		 * Check DJ Availability for given date
		 * 
		 * @since 1.1.3
		 * @params: 	
		 * @return: 
		 */
		public function availability_check( $date='', $dj='' )	{
			global $mdjm;
			
			$date = !empty( $date ) ? $date : date( 'Y-m-d' );
			
			/* Availability Check */
			$dj_avail = ( is_dj() ) ? dj_available( $dj, $date ) : dj_available( '', $date );
			
			/* Print the availability result */
			if( isset( $dj_avail ) )	{
				$GLOBALS['mdjm_debug']->log_it( 'DJ Availability check returns availability for ' . $date );
				/* Check all DJ's */
				if ( !empty( $dj_avail['available'] ) && current_user_can( 'administrator' ) )	{
					$avail_message = count( $dj_avail['available'] ) . ' ' . _n( MDJM_DJ, MDJM_DJ . '\'s', count( $dj_avail['available'] ) ) . ' available on ' . date( 'l, jS F Y', strtotime( $date ) );
				$class = 'updated';
					?><ui><?php
					foreach( $dj_avail['available'] as $dj_detail )	{
						$dj = get_userdata( $dj_detail );
						$avail_message .= '<li>' . $dj->display_name . 
						'<a href="' . get_edit_post_link( $_GET['e_id'] ) . '&dj=' . $dj->ID . 
						'"> Assign &amp; Respond to Enquiry</a><br /></li>';
					}
					?></ui><?php
				}
				/* Single DJ Check */
				elseif ( !empty( $dj_avail['available'] ) && !current_user_can( 'administrator' ) )	{
					$dj = get_userdata( get_current_user_id() );
					$class = 'updated';
					$avail_message = $dj->display_name . ' is available on ' . date( 'l, jS F Y', strtotime( $date ) ) . '<a href="' . admin_url( 'admin.php?page=mdjm-events&action=add_event_form&event_id=' . $_GET['e_id'] . '&dj=' . $dj->ID ) . '"> Assign &amp; Respond to Enquiry</a><br />';
				}
				else	{
					$class = 'error';
					if( current_user_can( 'administrator' ) )	{
						$avail_message = 'No ' . MDJM_DJ . '\'s available on ' . date( 'l, jS F Y', strtotime( $date ) );
					}
					else	{
						$dj = get_userdata( get_current_user_id() );
						$avail_message = $dj->display_name . ' is not available on ' . date( 'l, jS F Y', strtotime( $date ) );
					}
				}
				mdjm_update_notice( $class, $avail_message );
			}
			else	{
				$GLOBALS['mdjm_debug']->log_it( 'DJ Availability check returns no availability for ' . $date );
			}
		} // availability_check
		
		/*
		 * client_events_filter
		 * Adjust the post query for only this DJ's events
		 * 
		 * @since 1.1.3
		 * @params: 
		 * @return: 
		 */
		public function client_events_filter( $query )	{
			global $current_user;
			
			if( $query->is_main_query() ) {
				$query->set( 'meta_query', array(
											'relation'	=> 'AND',
												array(
												'key'		=> '_mdjm_event_client',
												'value'  	  => $_GET['client'],
												'compare'	=> '=='
												),
											) );
			}
		} // client_events_filter
		
		/*
		 * dj_events_filter
		 * Adjust the post query for only this DJ's events
		 * 
		 * @since 1.1.3
		 * @params: 
		 * @return: 
		 */
		public function dj_events_filter( $query )	{
			global $current_user;
					
			$dj = isset( $_GET['dj'] ) ? $_GET['dj'] : $current_user->ID;	
					
			if( $query->is_main_query() ) {
				$query->set( 'meta_query', array(
											'relation'	=> 'AND',
												array(
												'key'		=> '_mdjm_event_dj',
												'value'  	  => $dj,
												'compare'	=> '=='
												),
											) );
			}
		} // dj_events_filter
		
		/*
		 * Run the query filter to display on the specified post types (terms)
		 *
		 *
		 *
		 */
		public function post_types_query( $query )	{
			$type = isset( $_GET['mdjm_filter_type'] ) ? $_GET['mdjm_filter_type'] : '';
			
			if( !empty( $type ) ) {
				$query->set( 'tax_query', array(
													array(
													'taxonomy'		=> $_GET['post_type'] == MDJM_EVENT_POSTS ? 'event-types' : 'transaction-types',
													'field'		   => 'term_id',
													'terms'		   => $type,
													'operator'		=> 'IN'
													), ) );
			}
			
		} // post_types_query
					
		/* -- Email Template Columns -- */
		public function define_email_template_post_columns( $columns ) {
			$columns = array(
					'cb'			   => '<input type="checkbox" />',
					'title' 			=> __( 'Email Subject' ),
					'author'		   => __( 'Created By' ),
					'date' 			 => __( 'Date' ),
				);
			return $columns;
		} // define_email_template_post_columns
		
		/* -- Event Quote Columns -- */
		public function define_mdjm_quotes_post_columns( $columns ) {
			$columns = array(
					'cb'			   => '<input type="checkbox" />',
					'quote_date'   	   => __( 'Date Generated', 'mobile-dj-manager' ),
					'event' 			=> __( 'Event ID', 'mobile-dj-manager' ),
					'client'		   => __( 'Client', 'mobile-dj-manager' ),
					'value'			=> __( 'Quote Value', 'mobile-dj-manager' ),
					'view_date'		=> __( 'Date Viewed', 'mobile-dj-manager' ),
					'view_count'	   => __( 'View Count', 'mobile-dj-manager' ),
				);
			return $columns;
		} // define_mdjm_quotes_post_columns
						
		/*
		 * define_custom_post_column_data
		 * Define  data that is displayed in each column for the custom post types
		 * 
		 * @since 1.1.2
		 * @params: $column
		 */
		public function define_custom_post_column_data( $column )	{
			global $post, $mdjm, $mdjm_settings, $mdjm_post_types, $wpdb;
			
			if( $post->post_type == 'mdjm_communication' || !in_array( $post->post_type, $mdjm_post_types ) )
				return;
						
			/* -- mdjm-quotes -- */
			elseif( $post->post_type == MDJM_QUOTE_POSTS )	{
				$parent = wp_get_post_parent_id( $post->ID );
				
				switch( $column )	{
					/* -- Quote Date -- */
					case 'quote_date':
						echo date( 'd M Y H:i:s', strtotime( $post->post_date ) );
					break;
					
					/* -- Event -- */
					case 'event':
						echo ( !empty( $parent ) ? '<a href="' . admin_url( '/post.php?post=' . $parent . 
							'&action=edit' ) . '">' . MDJM_EVENT_PREFIX . $parent . '</a><br />' . 
							date( MDJM_SHORTDATE_FORMAT, strtotime( get_post_meta( $parent, '_mdjm_event_date', true ) ) ) : 
							'N/A' );
					break;
					
					/* -- Event -- */
					case 'client':
						echo '<a href="' . admin_url( 'admin.php?page=mdjm-clients&action=view_client&client_id=' . $post->post_author ) . '">' . get_the_author() . '</a>';
					break;
					
					/* -- Cost -- */
					case 'value':
						echo display_price( get_post_meta( $parent, '_mdjm_event_cost', true ) );
					break;
					
					/* -- Date Viewed -- */
					case 'view_date':
						echo ( $post->post_status == 'mdjm-quote-viewed' ? 
							date( 'd M Y H:i:s', strtotime( get_post_meta( $post->ID, '_mdjm_quote_viewed_date', true ) ) ) : 'N/A' );
					break;
					/* -- View Count -- */
					case 'view_count':
						$count = get_post_meta( $post->ID, '_mdjm_quote_viewed_count', true );
						if( empty( $count ) )
							$count = 0;
							
						echo $count . ' ' . _n( 'time', 'times', $count, 'mobile-dj-manager' );
					break;
				} // switch
			}
			else
				return;
		} // define_custom_post_columns

/**
* -- POST COLUMN SORTING
*/		
		/**
		 * column_sort
		 * The queries used to sort columns
		 * 
		 * 
		 * @since 1.1.3
		 * @params: $query
		 * @return:
		 */
		public function column_sort( $pieces, $query )	{
			global $wpdb;
			
			if( !is_admin() )
				return;
			
			/**
			 * We only want our code to run in the main WP query
			 * AND if an orderby query variable is designated.
			 */
			
			if( $query->is_main_query() && ( $orderby = $query->get( 'orderby' ) ) )	{
				$order = strtoupper( $query->get( 'order' ) );
				
				if( !in_array( $order, array( 'ASC', 'DESC' ) ) )
					$order = 'ASC';
					
				switch( $orderby )	{
					/**
					 * Event sorting
					 */
					// Order by event date
					case 'event_date':
						$pieces[ 'join' ] .= " LEFT JOIN $wpdb->postmeta mdjm_ed ON mdjm_ed.post_id = {$wpdb->posts}.ID AND mdjm_ed.meta_key = '_mdjm_event_date'";
						
						$pieces[ 'orderby' ] = "STR_TO_DATE( mdjm_ed.meta_value,'%Y-%m-%d' ) $order, " . $pieces[ 'orderby' ];
					break;
					
					// Order by event cost	
					case 'value':
						$pieces[ 'join' ] .= " LEFT JOIN $wpdb->postmeta mdjm_cost ON mdjm_cost.post_id = {$wpdb->posts}.ID AND mdjm_cost.meta_key = '_mdjm_event_cost'";
						
						$pieces[ 'orderby' ] = "mdjm_cost.meta_value $order, " . $pieces[ 'orderby' ];
					break;
					
					/**
					 * Transaction sorting
					 */										
					// Order by transaction status
					case 'txn_status':
						$pieces[ 'join' ] .= " LEFT JOIN $wpdb->postmeta mdjm_status ON mdjm_status.post_id = {$wpdb->posts}.ID AND mdjm_status.meta_key = '_mdjm_txn_status'";
						
						$pieces[ 'orderby' ] = "mdjm_status.meta_value $order, " . $pieces[ 'orderby' ];
					break;
					
					// Order by transaction value
					case 'txn_value':
						$pieces[ 'join' ] .= " LEFT JOIN $wpdb->postmeta mdjm_cost ON mdjm_cost.post_id = {$wpdb->posts}.ID AND mdjm_cost.meta_key = '_mdjm_txn_total'";
						
						$pieces[ 'orderby' ] = "mdjm_cost.meta_value $order, " . $pieces[ 'orderby' ];
					break;
					
					/**
					 * Venue sorting
					 */
					
					// Order by Venue town
					case 'town':
						$pieces[ 'join' ] .= " LEFT JOIN $wpdb->postmeta mdjm_town ON mdjm_town.post_id = {$wpdb->posts}.ID AND mdjm_town.meta_key = '_venue_town'";
						
						$pieces[ 'orderby' ] = "mdjm_town.meta_value $order, " . $pieces[ 'orderby' ];
					break;
					
					// Order by Venue county
					case 'county':
						$pieces[ 'join' ] .= " LEFT JOIN $wpdb->postmeta mdjm_county ON mdjm_county.post_id = {$wpdb->posts}.ID AND mdjm_county.meta_key = '_venue_county'";
						
						$pieces[ 'orderby' ] = "mdjm_county.meta_value $order, " . $pieces[ 'orderby' ];
					break;
					
				} // switch
			}
			
			return $pieces;
		} // column_sort
		
/**
* -- STYLES & CUSTOMISATIONS
*/
		/*
		 * custom_post_status_messages
		 * Set the messages displayed when updates are made
		 * to the custom posts
		 * 
		 * @since 1.1.2
		 * @params: $messages
		 * @return: $messages
		 */
		public function custom_post_status_messages( $messages )	{
			global $post, $mdjm_post_types;
					
			$post_id = $post->ID;
			$post_type = get_post_type( $post_id );
			
			if( !in_array( $post_type, $mdjm_post_types ) )
				return $messages;
			
			$singular = get_post_type_object( $post_type )->labels->singular_name;
			
			$messages[$post_type] = array(
					0 => '', // Unused. Messages start at index 1.
					1 => sprintf( __( '%s updated.' ), $singular ),
					2 => __( 'Custom field updated.', 'mdjm' ),
					3 => __( 'Custom field deleted.', 'mdjm' ),
					4 => sprintf( __( '%s updated.', 'mdjm' ), $singular ),
					5 => isset( $_GET['revision']) ? sprintf( __('%2$s restored to revision from %1$s', 'maxson' ), wp_post_revision_title( (int) $_GET['revision'], false ), $singular ) : false,
					6 => sprintf( __( '%s published.' ), $singular ),
					7 => sprintf( __( '%s saved.', 'mdjm' ), esc_attr( $singular ) ),
					8 => sprintf( __( '%s submitted.' ), $singular ),
					9 => sprintf( __( '%s scheduled for: <strong>%s</strong>. <a href="%s" target="_blank">Preview %s</a>' ), $singular, date_i18n( __( 'M j, Y @ G:i' ), strtotime( $post->post_date ) ), esc_url( get_permalink( $post_id ) ), 'Template' ),
					10 => sprintf( __( '%s draft updated.' ), $singular )
			);
			
			$custom_messages[MDJM_EVENT_POSTS] = array(
					1 	 => __( 'Event updated successfully. <a href="' . admin_url( 'edit.php?post_type=mdjm-event' ) . '">Return to Events list</a>' ),
					4 	 => __( 'Event updated successfully. <a href="' . admin_url( 'edit.php?post_type=mdjm-event' ) . '">Return to Events list</a>' ),
					6 	 => __( 'Event updated successfully. <a href="' . admin_url( 'edit.php?post_type=mdjm-event' ) . '">Return to Events list</a>' ),
					7 	 => __( 'Event updated successfully. <a href="' . admin_url( 'edit.php?post_type=mdjm-event' ) . '">Return to Events list</a>' ),
			);
			$custom_messages[MDJM_TRANS_POSTS] = array(
					1 	 => __( 'Transaction updated successfully. <a href="' . admin_url( 'edit.php?post_type=mdjm-transaction' ) . '">Return to Transactions list</a>' ),
					4 	 => __( 'Transaction updated successfully. <a href="' . admin_url( 'edit.php?post_type=mdjm-transaction' ) . '">Return to Transactions list</a>' ),
					6 	 => __( 'Transaction updated successfully. <a href="' . admin_url( 'edit.php?post_type=mdjm-transaction' ) . '">Return to Transactions list</a>' ),
					7 	 => __( 'Transaction updated successfully. <a href="' . admin_url( 'edit.php?post_type=mdjm-transaction' ) . '">Return to Transactions list</a>' ),
			);
			
			if( isset( $custom_messages[$post_type] ) )
				$messages[$post_type] = array_replace( $messages[$post_type], $custom_messages[$post_type] );
	
			return $messages;
		} // custom_post_status_messages
					
		/*
		 * define_mdjm_quote_bulk_action_list
		 * Define which options are available within the 
		 * bulk actions drop down list for each custom post type
		 *
		 * @since 1.1.3
		 * @params: $actions
		 * @return: $actions
		 */
		/* -- Remove Move to Trash from Event Bulk Actions -- */
		public function define_mdjm_quotes_bulk_action_list( $actions )	{
			unset( $actions['edit'] );
			//unset( $actions['trash'] );
			
			return $actions;
		} // define_mdjm_event_bulk_action_list
							
		/*
		 * define_custom_post_row_actions
		 * Dictate which row action links are displayed for
		 * each custom post type
		 * 
		 * @since 1.1.3
		 * @params: $actions, $post => array
		 * @return: $actions
		 */
		public function define_custom_post_row_actions( $actions, $post ) {
			global $mdjm_settings, $mdjm_post_types;
			
			/* -- No row actions for non custom post types -- */
			if( !in_array( $post->post_type, $mdjm_post_types ) )
				return $actions;
				
			elseif( $post->post_type == MDJM_COMM_POSTS )
				return $actions = array();
							
			elseif( $post->post_type == MDJM_CONTRACT_POSTS )	{			
				if( isset( $actions['inline hide-if-no-js'] ) )
					unset( $actions['inline hide-if-no-js'] );
			}
			
			elseif( $post->post_type == MDJM_EMAIL_POSTS )	{			
				if( isset( $actions['inline hide-if-no-js'] ) )
					unset( $actions['inline hide-if-no-js'] );
			}
			
			elseif( $post->post_type == MDJM_EVENT_POSTS )	{
				if( isset( $actions['trash'] ) )
					unset( $actions['trash'] );
				if( isset( $actions['view'] ) )
					unset( $actions['view'] );
				if( isset( $actions['edit'] ) )
					unset( $actions['edit'] );	
				if( isset( $actions['inline hide-if-no-js'] ) )
					unset( $actions['inline hide-if-no-js'] );
				
			/* -- Unattended Event Row Actions -- */
				if( $post->post_status == 'mdjm-unattended' )	{
					// Quote for event
					$actions['quote'] = sprintf( 
											'<a href="' . admin_url( 'post.php?post=%s&action=%s&mdjm_action=%s' ) . 
											'">' . __( 'Quote', 'mobile-dj-manager' ) . '</a>', 
											$post->ID, 'edit', 'respond' );
					// Check availability
					$actions['availability'] = sprintf( '<a href="%s&availability=%s&e_id=%s' . 
						'">Availability</a>', mdjm_get_admin_page( 'events' ), date( 'Y-m-d', ( strtotime( get_post_meta( $post->ID, '_mdjm_event_date', true ) ) ) ), 
						$post->ID );
					// Respond Unavailable
					$actions['respond_unavailable'] = sprintf( '<span class="trash"><a href="' . 
						admin_url( 'admin.php?page=%s&template=%s&to_user=%s&event_id=%s&action=%s' ) . 
						'">Unavailable</a></span>', 'mdjm-comms', $mdjm_settings['templates']['unavailable'], 
						get_post_meta( $post->ID, '_mdjm_event_client', true ), $post->ID, 'respond_unavailable' );	
				}
			}
			
			elseif( $post->post_type == MDJM_QUOTE_POSTS )	{			
				if( isset( $actions['inline hide-if-no-js'] ) )
					unset( $actions['inline hide-if-no-js'] );
					
				if( isset( $actions['edit'] ) )
					unset( $actions['edit'] );
			}
			
			elseif( $post->post_type == MDJM_TRANS_POSTS )	{			
				if( isset( $actions['inline hide-if-no-js'] ) )
					unset( $actions['inline hide-if-no-js'] );
			}
							
			elseif( $post->post_type == MDJM_VENUE_POSTS )	{
				if( isset( $actions['view'] ) )
					unset( $actions['view'] );
				
				if( isset( $actions['inline hide-if-no-js'] ) )
					unset( $actions['inline hide-if-no-js'] );
			}
			
			return $actions;
		} // define_custom_post_row_actions
		
/*
* EVENT POST FILTERED DROPDOWNS
*/
		/*
		 * Call functions to display posts filter drop downs
		 *
		 *
		 */
		public function post_filter_list()	{
			$type = '';
			if( isset($_GET['post_type'] ) )
				$type = $_GET['post_type'];
			
			if( MDJM_TRANS_POSTS == $type )
				$this->transaction_type_filter_dropdown();

		} // post_filter_list
				
		/*
		 * Filter dropdown for Transaction Types
		 * Display the drop down list to enable user to select transaction type
		 * to display
		 *
		 */
		public function transaction_type_filter_dropdown()	{
			global $mdjm;
			
			$type = '';
			if (isset($_GET['post_type']))
				$type = $_GET['post_type'];
			
			if( MDJM_TRANS_POSTS == $type )	{
				$transaction_types = get_categories( array(
											'type'			  => MDJM_TRANS_POSTS,
											'taxonomy'		  => 'transaction-types',
											'pad_counts'		=> false,
											'hide_empty'		=> true,
											'orderby'		  => 'name',
											) );
				foreach( $transaction_types as $transaction_type )	{
					$values[$transaction_type->term_id] = $transaction_type->name;
				}
				?>
				<select name="mdjm_filter_type">
				<option value=""><?php echo __( 'All Transaction Types' ); ?></option>
				<?php
					$current_v = isset( $_GET['mdjm_filter_type'] ) ? $_GET['mdjm_filter_type'] : '';
					
					if( !empty( $values ) )	{
						foreach( $values as $value => $label ) {
							printf
								(
									'<option value="%s"%s>%s (%s)</option>',
									$value,
									$value == $current_v ? ' selected="selected"' : '',
									$label,
									$label
								);
							}
					}
				?>
				</select>
				<?php
			}
		} // transaction_type_filter_dropdown
		
		/*
		 * Actions to be run within the admin_head hook
		 *
		 *
		 *
		 */
		public function mdjm_admin_head()	{
			global $mdjm;
			
			/* -- Define the post types & screens within which the MCE button should be displayed -- */
			$post_types = array( 'email_template', 'contract', 'page' );
			$screens = array( 
				'mdjm-events_page_mdjm-comms',
				'mdjm-events_page_mdjm-settings' );
			
			/* -- Add the MDJM TinyMCE buttons -- */
			$screen = get_current_screen();
			if( in_array( get_post_type(), $post_types ) || in_array( $screen->id, $screens ) )
				$mdjm->mce_shortcode_button();
			
			/* -- Edit styles for given page as required -- */
			$this->custom_post_edit_display();
		} // mdjm_admin_head
		
		/*
		 * Customise the post screen
		 * 
		 * @params
		 * 
		 * @return
		 */
		public function custom_post_edit_display() {
			if( !isset( $_GET['post_type'] ) )
				return;
			
			/**
			 * Remove the Add New button from the post lists display for all posts within the 
			 * $no_add_new array
			 */
			$no_add_new = array( MDJM_COMM_POSTS, MDJM_QUOTE_POSTS );
			
			if( in_array( $_GET['post_type'], $no_add_new ) )	{
				?>
				<style type="text/css">
					.page-title-action	{
						display: none;	
					}
				</style>
				<?php
			}
			
			/**
			 * Remove the date filter from the post lists display for all posts within the 
			 * $no_date_filter array
			 */
			$no_date_filter = array( MDJM_EVENT_POSTS );
			if( in_array( $_GET['post_type'], $no_date_filter ) )
				add_filter('months_dropdown_results', '__return_empty_array');
			
			/**
			 * Remove all filter from the post lists display for all posts within the 
			 * $no_filter array
			 */
			$no_filter = array( MDJM_VENUE_POSTS );
			
			if( in_array( $_GET['post_type'], $no_filter ) )	{
				?>
				<style type="text/css">
					#posts-filter .tablenav select[name=m],
					#posts-filter .tablenav select[name=cat],
					#posts-filter .tablenav #post-query-submit{
						display:none;
					}
				</style>
				<?php	
			}
		} // custom_post_edit_display			
		
		/*
		 * rename_publish_button
		 * Sets the name for the publish button for each
		 * custom post type
		 * 
		 * @since 1.1.2
		 * 
		 */
		public function rename_publish_button( $translation, $text )	{
			global $post;
			
			if( MDJM_CONTRACT_POSTS == get_post_type() )	{
				if( $text == 'Publish' )
					return __( 'Save Contract', 'mobile-dj-manager' );
				elseif( $text == 'Update' )
					return __( 'Update Contract', 'mobile-dj-manager' );
			}
			if( MDJM_EMAIL_POSTS == get_post_type() )	{
				if( $text == 'Publish' )
					return __( 'Save Template', 'mobile-dj-manager' );
				elseif( $text == 'Update' )
					return __( 'Update Template', 'mobile-dj-manager' );
			}
			if( MDJM_EVENT_POSTS == get_post_type() )	{
	
				$event_stati = get_event_stati();
				
				if( $text == 'Publish' && isset( $event_stati[$post->post_status] ) )
					return __( 'Update Event', 'mobile-dj-manager' );
				elseif( $text == 'Publish' )
					return __( 'Create Event', 'mobile-dj-manager' );
				elseif( $text == 'Update' )
					return __( 'Update Event', 'mobile-dj-manager' );
			}
			if( MDJM_TRANS_POSTS == get_post_type() )	{
				if( $text == 'Publish' )
					return __( 'Save Transaction', 'mobile-dj-manager' );
				elseif( $text == 'Update' )
					return __( 'Update Transaction', 'mobile-dj-manager' );	
			}
			if( MDJM_VENUE_POSTS == get_post_type() )	{
				if( $text == 'Publish' )
					return __( 'Save Venue', 'mobile-dj-manager' );
				elseif( $text == 'Update' )
					return __( 'Update Venue', 'mobile-dj-manager' );	
			}
			return $translation;
		} // rename_publish_button
		
		/**
		 * Set the title of a custom post upon new creation & make it readonly
		 * 
		 * @params	obj		$post		The post object
		 *
		 * @return
		 */
		public function set_post_title( $post ) {
			// Only apply to events and transactions
			if( get_post_type() != MDJM_EVENT_POSTS && get_post_type() != MDJM_TRANS_POSTS )
				return;
			
			?>
			<script type="text/javascript">
				jQuery(document).ready(function($) {
					$("#title").val("<?php echo MDJM_EVENT_PREFIX . $post->ID; ?>");
					$("#title").prop("readonly", true);
				});
			</script>
			<?php
		} // set_post_title
		
		/**
		 * Set the post title placeholder for custom post types
		 * 
		 *
		 * @param    str	$title
		 *
		 * @return   str	$title
		 */
		public function title_placeholder( $title )	{
			global $mdjm_post_types, $post;
			
			if( empty( $post ) || !in_array( $post->post_type, $mdjm_post_types ) )
				return;
			
			if( $post->post_type == MDJM_CONTRACT_POSTS )
				$title = __( 'Enter the Contract name here...', 'mobile-dj-manager' );	
			
			elseif( $post->post_type == MDJM_EMAIL_POSTS )
				$title = __( 'Enter the Template name here. Used as email subject, shortcodes allowed', 'mobile-dj-manager' );
				
			elseif( $post->post_type == MDJM_VENUE_POSTS )	{
				$title = __( 'Enter the Venue name here...', 'mobile-dj-manager' );
			}
			
			return $title;
		} // title_placeholder
		
/**
* -- META BOXES
*/
		/*
		 * define_metabox
		 * Dictate which meta boxes are displayed for each custom post type
		 * Actual layouts, sanitization and save actions are stored in their own files
		 * @since 1.1.2
		 */
		public function define_metabox()	{
			global $mdjm_post_types, $post;
			
			if( !in_array( $post->post_type, $mdjm_post_types ) )
				return;
			
			/* -- Our meta box functions -- */
			require_once( MDJM_PLUGIN_DIR . '/admin/includes/metabox.php' );
			
		/* -- Communications -- */
			if( $post->post_type == MDJM_COMM_POSTS )	{
				/* -- Sidebar -- */
				remove_meta_box( 'submitdiv', MDJM_COMM_POSTS, 'side' );
				add_meta_box( 'mdjm-email-details', __( 'Details', 'mobile-dj-manager' ), MDJM_COMM_POSTS . '_post_details_metabox', MDJM_COMM_POSTS, 'side', 'high' );
				
				/* -- Main Body -- */
				add_meta_box( 'mdjm-email-review', __( 'Email Content', 'mobile-dj-manager' ), str_replace( '-', '_', MDJM_COMM_POSTS ) . '_post_output_metabox', MDJM_COMM_POSTS, 'normal', 'high' );
			}
		/* -- Contract Templates -- */
			if( $post->post_type == MDJM_CONTRACT_POSTS )	{
				/* -- Main Body -- */
				add_meta_box( 'mdjm-contract-details', __( 'Contract Details', 'mobile-dj-manager' ), str_replace( '-', '_', MDJM_CONTRACT_POSTS ) . '_post_details_metabox', MDJM_CONTRACT_POSTS, 'side' );
			}
		/* -- Events -- */
			if( $post->post_type == MDJM_EVENT_POSTS )	{
				$event_stati = get_event_stati();
				/* -- Main Body -- */
				remove_meta_box( 'submitdiv', MDJM_EVENT_POSTS, 'side' );
				remove_meta_box( 'event-typesdiv', MDJM_EVENT_POSTS, 'side' );
				add_meta_box( 'mdjm-event-client', __( 'Client Details', 'mobile-dj-manager' ), str_replace( '-', '_', MDJM_EVENT_POSTS ) . '_post_client_metabox', MDJM_EVENT_POSTS, 'normal', 'high' );
				add_meta_box( 'mdjm-event-details', __( 'Event Details', 'mobile-dj-manager' ), str_replace( '-', '_', MDJM_EVENT_POSTS ) . '_post_event_metabox', MDJM_EVENT_POSTS, 'normal', 'high' );
				add_meta_box( 'mdjm-event-venue', __( 'Venue Details', 'mobile-dj-manager' ), str_replace( '-', '_', MDJM_EVENT_POSTS ) . '_post_venue_metabox', MDJM_EVENT_POSTS, 'normal', '' );
				add_meta_box( 'mdjm-event-admin', __( 'Administration', 'mobile-dj-manager' ), str_replace( '-', '_', MDJM_EVENT_POSTS ) . '_post_admin_metabox', MDJM_EVENT_POSTS, 'normal', 'low' );
				
				if( MDJM_PAYMENTS == true && array_key_exists( $post->post_status, $event_stati ) && current_user_can( 'administrator' ) )
					add_meta_box( 'mdjm-event-transactions', __( 'Transactions', 'mobile-dj-manager' ), 
						str_replace( '-', '_', MDJM_EVENT_POSTS ) . '_post_transactions_metabox', MDJM_EVENT_POSTS, 'normal', 'low' );
				
				if( current_user_can( 'administrator' ) && array_key_exists( $post->post_status, $event_stati ) )
					add_meta_box( 'mdjm-event-email-history', __( 'Event History', 'mobile-dj-manager' ), 
						str_replace( '-', '_', MDJM_EVENT_POSTS ) . '_post_history_metabox', MDJM_EVENT_POSTS, 'normal', 'low' );
				
				/* -- Side -- */
				add_meta_box( 'mdjm-event-options', __( 'Event Options', 'mobile-dj-manager' ), str_replace( '-', '_', MDJM_EVENT_POSTS ) . '_post_options_metabox', MDJM_EVENT_POSTS, 'side', 'low' );
				
				// Run action hook for mdjm_event_metabox
				do_action( 'mdjm_event_metaboxes', $post );
			}
		/* -- Transactions -- */
			if( $post->post_type == MDJM_TRANS_POSTS )	{
				remove_meta_box( 'submitdiv', MDJM_TRANS_POSTS, 'side' );
				remove_meta_box( 'transaction-typesdiv', MDJM_TRANS_POSTS, 'side' );
				/* -- Side -- */
				add_meta_box( 'mdjm-trans-save', __( 'Save Transaction', 'mobile-dj-manager' ), str_replace( '-', '_', MDJM_TRANS_POSTS ) . '_post_save_metabox', MDJM_TRANS_POSTS, 'side', 'high' );
				/* -- Main -- */
				add_meta_box( 'mdjm-trans-details', __( 'Transaction Details', 'mobile-dj-manager' ), str_replace( '-', '_', MDJM_TRANS_POSTS ) . '_post_details_metabox', MDJM_TRANS_POSTS, 'normal' );
			}
		/* -- Venues -- */
			if( $post->post_type == MDJM_VENUE_POSTS )	{
				/* -- Main Body -- */
				add_meta_box(
					'mdjm-venue-details',
					__( 'Venue Details', 'mobile-dj-manager' ),
					str_replace( '-', '_', MDJM_VENUE_POSTS ) . '_post_main_metabox',
					MDJM_VENUE_POSTS,
					'normal',
					'high' );
			}
		} // define_metabox

/**
* -- HELP PAGES
*/
		/**
		 * Contextual help messages
		 *
		 * @param   str		$contextual_help
		 *			str		$screen_id
		 *			str 	$screen
		 *
		 * @return   str	$contextual_help	The contextual help messages
		 * @since    1.1.3
		 */
		public function help_text( $contextual_help, $screen_id, $screen )	{
			global $mdjm_post_types;
			
			if( !in_array( $screen->post_type, $mdjm_post_types ) )
				return $contextual_help;
			
			if( $screen->post_type == MDJM_EVENT_POSTS )	{
				$contextual_help = 
					'<p>' . __( 'For assistance, refer to our <a href="' . mdjm_get_admin_page( 'user_guides' ) . '" target="_blank">User Guides</a>' .
					' or visit the <a href="' . mdjm_get_admin_page( 'mydjplanner' ) . '" target="_blank">' . MDJM_NAME . '</a> ' . 
					'<a href="' . mdjm_get_admin_page( 'mdjm_forums' ) . '" target="_blank">Support Forums' ) . '</a></p>' . "\r\n";
			}
			
			return $contextual_help;
		}


/**
* -- GENERAL POST FUNCTIONS
*/
		
		/**
		 * Determines if a post, identified by the specified ID, exist
		 * within the WordPress database.
		 * 
		 *
		 * @param    int    $id    The ID of the post to check
		 * @return   bool          True if the post exists; otherwise, false.
		 * @since    1.1.1
		 */
		public function post_exists( $id )	{
			return is_string( get_post_status( $id ) );	
		} // post_exists
		
		/*
		 * check_user_permission
		 * Determine if the user is allowed to carry out the custom post task
		 * 
		 * @since 1.1.3
		 * @params:
		 */
		public function check_user_permission( $post ) {
			global $pagenow, $mdjm, $mdjm_post_types;
			
			// If user is admin, or not a custom post type
			if( current_user_can( 'administrator' ) || !in_array( $post->post_type, $mdjm_post_types ) )
				return;
			
			/* -- Event posts -- */
			if( $post->post_type == MDJM_EVENT_POSTS )	{	
				// Add event permissions
				if( is_dj() && !dj_can( 'dj_add_event' ) && $pagenow == 'post-new.php' )
					wp_die( 'Your administrator has restricted you from creating new Events. Please contact them for assistance.<br /><br />
						<a class="button-secondary" href="' . $_SERVER['HTTP_REFERER'] .'" title="' . __( 'Back' ) .'">' . __( 'Back' ) . '</a>' );
				// Edit event permissions
				if( is_dj() && $pagenow == 'post.php' && !$mdjm->mdjm_events->is_my_event( $post->ID ) )	{
					wp_die( 'You can only view and edit your own events!<br /><br />
						<a class="button-secondary" href="' . $_SERVER['HTTP_REFERER'] .'" title="' . __( 'Back' ) .'">' . __( 'Back' ) . '</a>' );	
				}
			}
			if( $post->post_type == MDJM_VENUE_POSTS )	{	
				// Add venue permissions
				if( is_dj() && !dj_can( 'add_venue' ) )
					wp_die( 'You administrator has restricted you from creating new Venues. Please contact them for assistance.<br /><br />
						<a class="button-secondary" href="' . $_SERVER['HTTP_REFERER'] .'" title="' . __( 'Back' ) .'">' . __( 'Back' ) . '</a>' );
			}
			
		} // check_user_permission
	} // class
endif;