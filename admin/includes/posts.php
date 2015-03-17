<?php

/*
 * posts.php
 * 15/03/2015
 * @since 1.1.2
 * Manages customisation of custom post types
 */
	global $mdjm_post_types;
/*
 * Register the custom post types
 */
 	
	/*
	 * define_custom_post_types
	 * Configure & register each custom post type
	 * together with post status', taxonomies & terms
	 * 
	 * @since 1.1.2
	 */
	add_action( 'init', 'define_custom_post_types' );
	function define_custom_post_types()	{
		global $mdjm_post_types;
							
		require_once( WPMDJM_PLUGIN_DIR . '/admin/admin.php' );
		$lic_info = do_reg_check( 'check' );
		if( $lic_info )	{
		/* Build out the required arguments and register the post type */
			/* -- Communication -- */
			$template_labels[MDJM_COMM_POSTS] = array(
						'name'               => 'Email History',
						'singular_name'      => 'Email History',
						'menu_name'          => 'Email History',
						'name_admin_bar'     => 'Email History',
						'add_new'            => 'Add Communication',
						'add_new_item'       => 'Add New Communication',
						'new_item'           => 'New Communication',
						'edit_item'          => 'Review Email',
						'view_item'          => 'View Email',
						'all_items'          => 'All Emails',
						'search_items'       => 'Search Emails',
						'not_found'          => 'No Emails found.',
						'not_found_in_trash' => 'No Emails found in Trash.',
					);
			$post_args[MDJM_COMM_POSTS] = array(
						'labels'			 	 => $template_labels[MDJM_COMM_POSTS],
						'description'			=> 'Communication used by the Mobile DJ Manager for WordPress plugin',
						'public'			 	 => false,
						'exclude_from_search'	=> false,
						'publicly_queryable' 	 => true,
						'show_ui'				=> true,
						'show_in_menu'		   => 'edit.php?post_type=' . MDJM_COMM_POSTS,
						'show_in_admin_bar'	  => false,
						'rewrite' 			    => array( 'slug' => 'mdjm-communications'),
						'query_var'		 	  => true,
						'capability_type'    	=> 'post',
						'has_archive'        	=> true,
						'hierarchical'       	   => false,
						'menu_position'     	  => 5,
						'supports'			   => array( 'title' ),
						'menu_icon'			  => plugins_url( 'mobile-dj-manager/admin/images/mdjm-icon-20x20.jpg' ),
						'register_meta_box_cb'   => 'define_metabox',
						);
			
			/* -- Contracts -- */
			$template_labels[MDJM_CONTRACT_POSTS] = array(
						'name'               => 'Contract Templates',
						'singular_name'      => 'Contract Template',
						'menu_name'          => 'Contract Templates',
						'name_admin_bar'     => 'Contract Template',
						'add_new'            => 'Add Contract Template',
						'add_new_item'       => 'Add New Contract Template',
						'new_item'           => 'New Contract Template',
						'edit_item'          => 'Edit Contract Template',
						'view_item'          => 'View Contract Template',
						'all_items'          => 'All Contract Templates',
						'search_items'       => 'Search Contract Templates',
						'not_found'          => 'No contract templates found.',
						'not_found_in_trash' => 'No contract templates found in Trash.',
					);
			$post_args[MDJM_CONTRACT_POSTS] = array(
						'labels'			 	 => $template_labels[MDJM_CONTRACT_POSTS],
						'description'			=> 'Contracts used by the MDJM plugin',
						'public'			 	 => true,
						'publicly_queryable' 	 => true,
						'show_ui'				=> true,
						'show_in_menu'	   	   => 'edit.php?post_type=' . MDJM_CONTRACT_POSTS,
						'query_var'		  	  => true,
						'rewrite'            	=> array( 'slug' => 'contract' ),
						'capability_type'    	=> 'post',
						'has_archive'        	=> true,
						'hierarchical'       	   => false,
						'menu_position'      	  => 5,
						'supports'           	   => array( 'title', 'editor', 'revisions' ),
						'menu_icon'		  	  => plugins_url( 'mobile-dj-manager/admin/images/mdjm-icon-20x20.jpg' ),
						'register_meta_box_cb'   => 'define_metabox',
					);
			
			/* -- Email Templates -- */
			$template_labels[MDJM_EMAIL_POSTS] = array(
						'name'               => 'Email Templates',
						'singular_name'      => 'Email Template',
						'menu_name'          => 'Email Templates',
						'name_admin_bar'     => 'Email Template',
						'add_new'            => 'Add Template',
						'add_new_item'       => 'Add New Template',
						'new_item'           => 'New Template',
						'edit_item'          => 'Edit Template',
						'view_item'          => 'View Template',
						'all_items'          => 'All Templates',
						'search_items'       => 'Search Templates',
						'not_found'          => 'No templates found.',
						'not_found_in_trash' => 'No templates found in Trash.',
					);
			$post_args[MDJM_EMAIL_POSTS] = array(
						'labels'			 	 => $template_labels[MDJM_EMAIL_POSTS],
						'description'			=> 'Email Templates for the Mobile DJ Manager plugin',
						'public'			 	 => false,
						'exclude_from_search'	=> false,
						'publicly_queryable' 	 => true,
						'show_ui'				=> true,
						'show_in_menu'		   => 'edit.php?post_type=' . MDJM_EMAIL_POSTS,
						'show_in_admin_bar'	  => true,
						'query_var'		 	  => true,
						'rewrite'            	=> array( 'slug' => 'email-template' ),
						'capability_type'    	=> 'post',
						'has_archive'        	=> true,
						'hierarchical'       	   => false,
						'menu_position'     	  => 5,
						'supports'			   => array( 'title', 'editor', 'revisions' ),
						'menu_icon'			  => plugins_url( 'mobile-dj-manager/admin/images/mdjm-icon-20x20.jpg' ),
						'register_meta_box_cb'   => 'define_metabox',
						);
			
			/* -- Venues -- */
			$template_labels[MDJM_VENUE_POSTS] = array(
						'name'               => 'Venues',
						'singular_name'      => 'Venue',
						'menu_name'          => 'Venues',
						'name_admin_bar'     => 'Venue',
						'add_new'            => 'Add Venue',
						'add_new_item'       => 'Add New Venue',
						'new_item'           => 'New Venue',
						'edit_item'          => 'Edit Venue',
						'view_item'          => 'View Venue',
						'all_items'          => 'All Venues',
						'search_items'       => 'Search Venues',
						'not_found'          => 'No Venues found.',
						'not_found_in_trash' => 'No Venues found in Trash.',
					);
			$post_args[MDJM_VENUE_POSTS] = array(
						'labels'			 	 => $template_labels[MDJM_VENUE_POSTS],
						'description'			=> 'Venues stored for the Mobile DJ Manager plugin',
						'public'			 	 => false,
						'exclude_from_search'	=> false,
						'publicly_queryable' 	 => true,
						'show_ui'				=> true,
						'show_in_menu'		   => 'edit.php?post_type=' . MDJM_VENUE_POSTS,
						'show_in_admin_bar'	  => true,
						'rewrite' 			  	=> array( 'slug' => 'mdjm-venue'),
						'query_var'		 	  => true,
						'capability_type'    	=> 'post',
						'has_archive'        	=> true,
						'hierarchical'       	   => false,
						'menu_position'     	  => 5,
						'supports'			   => array( 'title' ),
						'menu_icon'			  => plugins_url( 'mobile-dj-manager/admin/images/mdjm-icon-20x20.jpg' ),
						'taxonomies'			 => array( MDJM_VENUE_POSTS ),
						'register_meta_box_cb'   => 'define_metabox',
					);
			
				/* Now register the new post type */
				foreach( $mdjm_post_types as $mdjm_post_type )	{
					if( !post_type_exists( $mdjm_post_type ) )
						register_post_type( $mdjm_post_type, $post_args[$mdjm_post_type] );
				}
				/* -- And the post status' -- */
				register_post_status( 'ready to send', array(
										'label'                     => _x( 'Ready to Send', 'post' ),
										'public'                    => true,
										'exclude_from_search'       => false,
										'show_in_admin_all_list'    => true,
										'show_in_admin_status_list' => true,
										'label_count'               => _n_noop( 'Ready to Send <span class="count">(%s)</span>', 'Ready to Send <span class="count">(%s)</span>' ),
									) );
				register_post_status( 'sent', array(
										'label'                     => _x( 'Sent', 'post' ),
										'public'                    => true,
										'exclude_from_search'       => false,
										'show_in_admin_all_list'    => true,
										'show_in_admin_status_list' => true,
										'label_count'               => _n_noop( 'Sent <span class="count">(%s)</span>', 'Sent <span class="count">(%s)</span>' ),
									) );
				register_post_status( 'opened', array(
										'label'                     => _x( 'Opened', 'post' ),
										'public'                    => true,
										'exclude_from_search'       => false,
										'show_in_admin_all_list'    => true,
										'show_in_admin_status_list' => true,
										'label_count'               => _n_noop( 'Opened <span class="count">(%s)</span>', 'Opened <span class="count">(%s)</span>' ),
									) );
				/* -- Register the Venue Taxonomy & Terms -- */
				if( !get_taxonomy( 'venue-details' ) )	{
					$tax_labels[MDJM_VENUE_POSTS] = array(
									'name'              		   => _x( 'Venue Details', 'taxonomy general name' ),
									'singular_name'     		  => _x( 'Venue Detail', 'taxonomy singular name' ),
									'search_items'      		   => __( 'Search Venue Details' ),
									'all_items'         		  => __( 'All Venue Details' ),
									'edit_item'        		  => __( 'Edit Venue Detail' ),
									'update_item'       			=> __( 'Update Venue Detail' ),
									'add_new_item'      		   => __( 'Add New Venue Detail' ),
									'new_item_name'     		  => __( 'New Venue Detail' ),
									'menu_name'         		  => __( 'Venue Details' ),
									'separate_items_with_commas' => NULL,
									'choose_from_most_used'	  => __( 'Choose from the most popular Venue Details' ),
									'not_found'				  => __( 'No details found' ),
									);
					$tax_args[MDJM_VENUE_POSTS] = array(
									'hierarchical'      => true,
									'labels'            => $tax_labels[MDJM_VENUE_POSTS],
									'show_ui'           => true,
									'show_admin_column' => true,
									'query_var'         => true,
									'rewrite'           => array( 'slug' => 'venue-details' ),
								);
					register_taxonomy( 'venue-details', MDJM_VENUE_POSTS, $tax_args[MDJM_VENUE_POSTS] );
				}
		}
	}

/*
 * Define the update messages for custom post types
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
 	add_filter( 'post_updated_messages', 'custom_post_status_messages' );
	function custom_post_status_messages( $messages )	{
		global $post, $mdjm_post_types;
		
		$post_id = $post->ID;
		$post_type = get_post_type( $post_id );
		
		if( !in_array( $post_type, $mdjm_post_types ) )	{
			return $messages;	
		}
		
		if( $post_type === MDJM_COMM_POSTS )	{
			$singular = 'Email History';
		}
		elseif( $post_type === MDJM_CONTRACT_POSTS )	{
			$singular = 'Contract Template';
		}
		elseif( $post_type === MDJM_EMAIL_POSTS )	{
			$singular = 'Email Template';
		}
		elseif( $post_type === MDJM_VENUE_POSTS )	{
			$singular = 'Venue';
		}

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

        return $messages;
	} // custom_post_status_messages
/*
 * Define the row actions for each custom post type
 */
	/*
	 * define_custom_post_row_actions
	 * Dictate which row action links are displayed for
	 * each custom post type
	 * 
	 * @since 1.1.2
	 * @params: $actions, $post => array
	 * @return: $actions
	 */
 	add_filter( 'post_row_actions', 'define_custom_post_row_actions', 10, 2 );
	function define_custom_post_row_actions( $actions, $post ) {
		global $mdjm_post_types;
		
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
		
		elseif( $post->post_type == MDJM_VENUE_POSTS )	{
			if( isset( $actions['view'] ) )
				unset( $actions['view'] );
			
			if( isset( $actions['inline hide-if-no-js'] ) )
				unset( $actions['inline hide-if-no-js'] );
		}
		
		return $actions;
	} // define_custom_post_row_actions

/*
 * Define the columns and column data for the custom post types
 */
 	/*
	 * Define the table columns that are displayed for
	 * communication posts
	 * 
	 * @since 1.1.2
	 * @params: columns => array
	 * @return: $columns
	 */
 	foreach( $mdjm_post_types as $mdjm_post_type )	{
		if( function_exists( 'define_' . str_replace( '-', '_', $mdjm_post_type ) . '_post_columns' ) )
			add_filter( 'manage_' . $mdjm_post_type . '_posts_columns' , 'define_' . str_replace( '-', '_', $mdjm_post_type ) . '_post_columns' );
	}
	/* -- Communication Columns -- */
 	function define_mdjm_communication_post_columns( $columns ) {
		$columns = array(
				'cb'			   => '<input type="checkbox" />',
				'date_sent' 		=> __( 'Date Sent' ),
				'title' 	 		=> __( 'Email Subject' ),
				'from'		   	 => __( 'From' ),
				'recipient' 		=> __( 'Recipient' ),
				'event'			=> __( 'Associated Event' ),
				'current_status'   => __( 'Status' ),
				'source'		   => __( 'Source' ),
			);
		return $columns;
	} // define_communication_post_columns
	
	/* -- Contract Columns -- */
	function define_contract_post_columns( $columns ) {
		$columns = array(
				'cb'			   => '<input type="checkbox" />',
				'title' 			=> __( 'Contract Name' ),
				'default'		  => __( 'Is Default?' ),
				'author'		   => __( 'Created By' ),
				'date' 			 => __( 'Date' ),
			);
		return $columns;
	} // define_contract_post_columns
	
	/* -- Email Template Columns -- */
	function define_email_template_post_columns( $columns ) {
		$columns = array(
				'cb'			   => '<input type="checkbox" />',
				'title' 			=> __( 'Email Subject' ),
				'author'		   => __( 'Created By' ),
				'date' 			 => __( 'Date' ),
			);
		return $columns;
	} // define_email_template_post_columns
	
	/* -- Venue Columns -- */
  	function define_mdjm_venue_post_columns( $columns ) {
		$columns = array(
				'cb'			   => '<input type="checkbox" />',
				'title' 	 		=> __( 'Venue' ),
				'contact'		  => __( 'Contact' ),
				'phone'		    => __( 'Phone' ),
				'town' 			 => __( 'Town' ),
				'county'   		   => __( 'County' ),
				'info'		     => __( 'Information' ),
				'added_by'	     => __( 'Added By' ),
			);
		return $columns;
	} // define_venue_post_columns
 	
/*
 * Define the data that is displayed in each column for the custom post types
 */
 	/*
	 * define_custom_post_column_data
	 * Define  data that is displayed in each column for the custom post types
	 * 
	 * @since 1.1.2
	 * @params: $column
	 */
	add_action( 'manage_posts_custom_column', 'define_custom_post_column_data', 10, 1 );
	function define_custom_post_column_data( $column )	{
		global $post, $mdjm_options, $mdjm_post_types;
		
		if( !in_array( $post->post_type, $mdjm_post_types ) )
			return;
		
		/* -- mdjm-communication -- */
		elseif( $post->post_type == MDJM_COMM_POSTS )	{
			switch ( $column ) {
				/* -- Date Sent -- */
				case 'date_sent':
					echo date( $mdjm_options['time_format'] . ' ' . $mdjm_options['short_date_format'], get_post_meta( $post->ID, '_date_sent', true ) );
					break;
				
				/* -- From -- */	
				case 'from':
					if( $author = get_userdata( $post->post_author ) )	{
						echo sprintf( '<a href="' . admin_url( 'user-edit.php?user_id=%s' ) . '">%s</a>', $author->ID, ucwords( $author->display_name ) );
					}
					else	{
						echo get_post_meta( $post->ID, '_recipient' );	
					}
					break;
				
				/* -- Recipient -- */	
				case 'recipient':
					if( $client = get_userdata( get_post_meta( $post->ID, '_recipient', true ) ) )	{
						echo sprintf( '<a href="' . admin_url( 'user-edit.php?user_id=%s' ) . '">%s</a>', $client->ID, ucwords( $client->display_name ) );
					}
					else	{
						echo get_post_meta( $post->ID, '_recipient' );	
					}
					break;
					
				/* -- Associated Event -- */	
				case 'event':
					$event = get_post_meta( $post->ID, '_event', true );
					
					echo ( !empty( $event ) ? '<a href="'. admin_url( 'admin.php?page=mdjm-events&action=view_event_form&event_id=' . $event ) . '">' . $mdjm_options['id_prefix'] . $event . '</a>' : 'N/A' );
					
					break;
				
				/* -- Status -- */
				case 'current_status':
					$count = get_post_meta( $post->ID, '_open_count', true );
					$last_change = $post->post_modified;
					
					$change_date = !empty( $last_change ) && $post->post_status == 'opened' ? date( $mdjm_options['time_format'] . ' ' . $mdjm_options['short_date_format'], strtotime( $last_change ) ) : '';
					$open_count = !empty( $count ) && $post->post_status == 'opened' ? ' (' . $count . ')' : '';
					
					echo ucwords( $post->post_status ) . ' ' . $change_date . $open_count;
					break;
				
				/* -- Source -- */
				case 'source':
					echo stripslashes( get_post_meta( $post->ID, '_source', true ) );
					break;
			} // switch
		}
		
		/* -- contract -- */
		elseif( $post->post_type == MDJM_CONTRACT_POSTS )	{
			switch ( $column ) {
				/* -- Is Default? -- */
				case 'default':
					echo $post->ID == $mdjm_options['default_contract'] ? 'Yes' : 'No';
					break;	
			} // switch
		}
		
		/* -- mdjm-venue -- */
		elseif( $post->post_type == MDJM_VENUE_POSTS )	{
			switch ( $column ) {				
				/* -- Contact -- */
				case 'contact':
					echo sprintf( '<a href="mailto:%s">%s</a>', get_post_meta( $post->ID, '_venue_email', true ), stripslashes( get_post_meta( $post->ID, '_venue_contact', true ) ) );					
					break;
				
				/* -- Phone -- */
				case 'phone':
					echo get_post_meta( $post->ID, '_venue_phone', true );
					break;
				
				/* -- Town -- */
				case 'town':
					echo $town = get_post_meta( $post->ID, '_venue_town', true );
					break;
					
				/* -- County -- */
				case 'county':
					echo get_post_meta( $post->ID, '_venue_county', true );
					break;
					
				/* -- Information -- */
				case 'info':
					echo stripslashes( get_post_meta( $post->ID, '_venue_information', true ) );
					break;
				
				/* -- Added By -- */
				case 'added_by':
					if( $author = get_userdata( $post->post_author ) )	{
						echo sprintf( '<a href="' . admin_url( 'user-edit.php?user_id=%s' ) . '">%s</a>', $author->ID, ucwords( $author->display_name ) );
					}
					else	{
						echo get_post_meta( $post->ID, '_recipient' );	
					}
					break;
			} // switch
		}
		
		else	{
			return;	
		}
	} // define_custom_post_columns

/*
 * Styling
 */
 	/*
	 * define_styles
	 * Define styles for each custom post type
	 * 
	 * @since 1.1.2
	 * 
	 */
	add_action( 'admin_head', 'define_styles' );
	function define_styles() {
		global $mdjm_post_types;
		/* -- No Add New for Communications -- */
		if( MDJM_COMM_POSTS == get_post_type() )	{
			echo '<style type="text/css">' . "\r\n";
			echo '#favorite-actions {' . "\r\n";
			echo ' 	display:none;' . "\r\n";
			echo '}' . "\r\n";
			echo '.add-new-h2{' . "\r\n";
			echo ' 	display:none;' . "\r\n";
			echo '}' . "\r\n";
			echo '</style>' . "\r\n";
		}
		/* -- No Publishing Actions -- */
		if( in_array( get_post_type(), $mdjm_post_types ) )	{
			echo '<style type="text/css">' . "\r\n";
			echo '#misc-publishing-actions, #minor-publishing-actions {' . "\r\n";
			echo '	display:none;' . "\r\n";
			echo '}' . "\r\n";
            echo '</style>' . "\r\n";
		}
	} // define_styles
	
	/*
	 * rename_publish_button
	 * Sets the name for the publish button for each
	 * custom post type
	 * 
	 * @since 1.1.2
	 * 
	 */
	add_filter( 'gettext', 'rename_publish_button', 10, 2 );
	function rename_publish_button( $translation, $text )	{
		if( MDJM_CONTRACT_POSTS == get_post_type() )	{
			if( $text == 'Publish' )
				return 'Save Contract';	
		}
		if( MDJM_EMAIL_POSTS == get_post_type() )	{
			if( $text == 'Publish' )
				return 'Save Email Template';	
		}
		if( MDJM_VENUE_POSTS == get_post_type() )	{
			if( $text == 'Publish' )
				return 'Save Venue';	
		}
		return $translation;
	} // rename_publish_button

	/*
	 * define_mdjm_communication_bulk_action_list
	 * Define which options are available within the 
	 * bulk actions drop down list for each custom post type
	 *
	 * @since 1.1.2
	 * @params: $actions
	 * @return: $actions
	 */
	foreach( $mdjm_post_types as $mdjm_post_type )	{
	 	if( function_exists( 'define_' . str_replace( '-', '_', $mdjm_post_type ) . '_bulk_action_list' ) )
			add_filter( 'bulk_actions-edit-' . str_replace( '-', '_', $mdjm_post_type ), 'define_' . str_replace( '-', '_', $mdjm_post_type ) . '_bulk_action_list' );
	}
	/* -- Remove Edit from Communication Bulk Actions -- */
	function define_mdjm_communication_bulk_action_list( $actions )	{
		unset( $actions['edit'] );
		return $actions;
	} // define_mdjm_communication_bulk_action_list
/*
 * Meta boxes
 */
 	/*
	 * define_metabox
	 * Dictate which meta boxes are displayed for each custom post type
	 * Actual layouts, sanitization and save actions are stored in their own files
	 * @since 1.1.2
	 */
	function define_metabox()	{
		global $mdjm_post_types, $post;
		if( !in_array( $post->post_type, $mdjm_post_types ) )
			return;
		
		/* -- Our meta box functions -- */
		require_once( 'metabox.php' );
		
		if( $post->post_type == MDJM_COMM_POSTS )	{
			/* -- Sidebar -- */
			remove_meta_box( 'submitdiv', MDJM_COMM_POSTS, 'side' );
			add_meta_box( 'mdjm-email-details', __( 'Details', 'textdomain' ), MDJM_COMM_POSTS . '_post_details_metabox', MDJM_COMM_POSTS, 'side', 'high' );
			
			/* -- Main Body -- */
			add_meta_box( 'mdjm-email-review', __( 'Email Content', 'textdomain' ), str_replace( '-', '_', MDJM_COMM_POSTS ) . '_post_output_metabox', MDJM_COMM_POSTS, 'normal', 'high' );
		}
		
		if( $post->post_type == MDJM_CONTRACT_POSTS )	{
			/* -- Main Body -- */
			add_meta_box( 'mdjm-contract-details', __( 'Contract Details', 'textdomain' ), str_replace( '-', '_', MDJM_CONTRACT_POSTS ) . '_post_details_metabox', MDJM_CONTRACT_POSTS, 'side' );
		}
		
		if( $post->post_type == MDJM_VENUE_POSTS )	{
			/* -- Main Body -- */
			add_meta_box( 'mdjm-venue-details', __( 'Venue Details', 'textdomain' ), str_replace( '-', '_', MDJM_VENUE_POSTS ) . '_post_main_metabox', MDJM_VENUE_POSTS, 'normal', 'high' );
		}
	} // define_metabox
?>
