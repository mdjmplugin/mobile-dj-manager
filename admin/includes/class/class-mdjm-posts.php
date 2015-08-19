<?php
/*
 * class-mdjm-posts.php
 * 10/03/2015
 * @since 1.1.2
 * The MDJM Post class
 */
	
	/* -- Build the MDJM_Posts class -- */
	if( !class_exists( 'MDJM_Posts' ) )	{
		class MDJM_Posts	{
			
			/**
			 * The Constructor
			 */
			public function __construct()	{
				global $mdjm_post_types;

				/* -- Register actions -- */
				add_action( 'init', array( &$this, 'mdjm_post_init' ) ); // Custom post registration
				add_action( 'manage_posts_custom_column', array( &$this, 'define_custom_post_column_data' ), 10, 1 ); // Data displayed in post columns
				add_action( 'edit_form_top', array( &$this, 'check_user_permission' ) ); // Permissions
				add_action( 'admin_head', array( &$this, 'mdjm_admin_head' ) ); // Execute the admin_head hook
				add_action( 'edit_form_after_title', array( &$this, 'set_post_title' ) ); // Set the post title for Custom posts
				add_action( 'admin_footer', array( &$this, 'event_rows' ) ); // Unattended event row colour
				add_action( 'contextual_help', array( &$this, 'help_text' ), 10, 3 ); // Contextual help
				add_action( 'restrict_manage_posts', array( &$this, 'post_filter_list' ) ); // Filter dropdown boxes
				
				/* -- Register filters -- */
				foreach( $mdjm_post_types as $mdjm_post_type )	{ // Post columns
					if( method_exists( $this, 'define_' . str_replace( '-', '_', $mdjm_post_type ) . '_post_columns' ) )
						add_filter( 'manage_' . $mdjm_post_type . '_posts_columns' , array( &$this, 'define_' . str_replace( '-', '_', $mdjm_post_type ) . '_post_columns' ) );
				}
				
				/* -- Bulk Actions -- */
				add_filter( 'bulk_actions-edit-mdjm_communication', array( &$this, 'define_mdjm_communication_bulk_action_list' ) );
				add_filter( 'bulk_actions-edit-mdjm-event', array( &$this, 'define_mdjm_event_bulk_action_list' ) );
				add_filter( 'bulk_actions-edit-mdjm-venue', array( &$this, 'define_mdjm_venue_bulk_action_list' ) );
				
				/* -- Column Sorting -- */
				if( !empty( $post ) )	{
					add_filter( 'manage_edit-mdjm-event_sortable_columns', array( &$this, 'column_sorting' ) ); // Defines which columns are sortable for Events
					add_filter( 'manage_edit-mdjm-venue_sortable_columns', array( &$this, 'column_sorting' ) ); // Defines which columns are sortable for Venues
				}
				
				if( is_admin() )	{
					add_action( 'pre_get_posts', array( &$this, 'pre_post' ) ); // Actions for pre_get_posts
					add_filter( 'parse_query', array( &$this, 'custom_post_filter' ) ); // Actions for filtered queries
					
					add_filter( 'post_row_actions', array( &$this, 'define_custom_post_row_actions' ), 10, 2 ); // Row actions
					add_filter( 'post_updated_messages', array( &$this, 'custom_post_status_messages' ) ); // Status messages
					add_filter( 'gettext', array( &$this, 'rename_publish_button' ), 10, 2 ); // Set the value of the submit button
					add_filter( 'enter_title_here', array( &$this, 'title_placeholder' ) ); // Set the title placeholder text
				}

			} // __construct()

/*
 * -- Custom Post Settings
 */
			/*
			 * Register custom post types and other post settings
			 *
			 * @since	1.1.3
			 */
			public function mdjm_post_init()	{
				$this->register_custom_post_types();
				$this->register_custom_status();
				$this->register_custom_taxonomies();
				
			/* -- This is our custom post save hook. Needs $_POST['mdjm_update_custom_post'] == 'mdjm_update' -- */
				if( !empty( $_POST['mdjm_update_custom_post'] ) && $_POST['mdjm_update_custom_post'] == 'mdjm_update' )
					add_action( 'save_post', array( &$this, 'save_custom_post' ), 10, 2 );

				
			} // mdjm_post_init
			
			/**
			 * Register the custom post types and settings
			 *
			 * @since	1.1.3
			 */
			public function register_custom_post_types()	{
				global $mdjm_post_types;
				
			/*-- Communications -- */
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
						'register_meta_box_cb'   => array( &$this, 'define_metabox' ),
						);
			
			/* -- Contact Forms -- */
				$template_labels[MDJM_CONTACT_FORM_POSTS] = array(
						'name'               => 'Contact Forms',
						'singular_name'      => 'Contact Form',
						'menu_name'          => 'Contact Forms',
						'name_admin_bar'     => 'Contact Form',
						'add_new'            => 'Add Contact Form',
						'add_new_item'       => 'Add New Contact Form',
						'new_item'           => 'New Contact Form',
						'edit_item'          => 'Edit Contact Form',
						'view_item'          => 'View Contact Form',
						'all_items'          => 'All Contact Forms',
						'search_items'       => 'Search Contact Forms',
						'not_found'          => 'No contact forms found.',
						'not_found_in_trash' => 'No contact forms found in Trash.',
					);
				$post_args[MDJM_CONTACT_FORM_POSTS] = array(
						'labels'			 	 => $template_labels[MDJM_CONTACT_FORM_POSTS],
						'description'			=> 'Contact forms used by the MDJM plugin',
						'public'			 	 => false,
						'publicly_queryable' 	 => true,
						'show_ui'				=> false,
						'show_in_menu'	   	   => 'edit.php?post_type=' . MDJM_CONTACT_FORM_POSTS,
						'query_var'		  	  => false,
						'rewrite'            	=> array( 'slug' => 'mdjm-contact-form' ),
						'capability_type'    	=> 'post',
						'has_archive'        	=> false,
						'hierarchical'       	   => false,
						'menu_position'      	  => 5,
						'supports'           	   => array( 'title' ),
						'menu_icon'		  	  => plugins_url( 'mobile-dj-manager/admin/images/mdjm-icon-20x20.jpg' ),
						'register_meta_box_cb'   => array( &$this, 'define_metabox' ),
					);
					
			/* -- Contact Form Fields -- */
				$template_labels[MDJM_CONTACT_FIELD_POSTS] = array(
						'name'               => 'Contact Form Fields',
						'singular_name'      => 'Contact Form Field',
						'menu_name'          => 'Contact Form Fields',
						'name_admin_bar'     => 'Contact Form Field',
						'add_new'            => 'Add Contact Form Field',
						'add_new_item'       => 'Add New Contact Form Field',
						'new_item'           => 'New Contact Form Field',
						'edit_item'          => 'Edit Contact Form Field',
						'view_item'          => 'View Contact Form Field',
						'all_items'          => 'All Contact Forms Field',
						'search_items'       => 'Search Contact Form Fields',
						'not_found'          => 'No contact form fields found.',
						'not_found_in_trash' => 'No contact forms fields found in Trash.',
					);
				$post_args[MDJM_CONTACT_FIELD_POSTS] = array(
						'labels'			 	 => $template_labels[MDJM_CONTACT_FIELD_POSTS],
						'description'			=> 'Contact form fields used by the MDJM plugin',
						'public'			 	 => false,
						'publicly_queryable' 	 => true,
						'show_ui'				=> false,
						'show_in_menu'	   	   => false,
						'query_var'		  	  => false,
						'rewrite'            	=> array( 'slug' => 'mdjm-contact-field' ),
						'capability_type'    	=> 'post',
						'has_archive'        	=> false,
						'hierarchical'       	   => false,
						'menu_position'      	  => 5,
						'supports'           	   => array( 'title', 'page-attributes' ),
						'menu_icon'		  	  => plugins_url( 'mobile-dj-manager/admin/images/mdjm-icon-20x20.jpg' ),
					);
			
			/* -- Contract Templates -- */
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
						'public'			 	 => false,
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
						'register_meta_box_cb'   => array( &$this, 'define_metabox' ),
					);
					
			/* -- Signed Contract Templates -- */
				$template_labels[MDJM_SIGNED_CONTRACT_POSTS] = array(
						'name'               => 'Signed Contracts',
						'singular_name'      => 'Signed Contract',
						'menu_name'          => 'Signed Contracts',
						'name_admin_bar'     => 'Signed Contract',
						'add_new'            => 'Add Signed Contract',
						'add_new_item'       => 'Add New Signed Contract',
						'new_item'           => 'New Signed Contract',
						'edit_item'          => 'Edit Signed Contract',
						'view_item'          => 'View Signed Contract',
						'all_items'          => 'All Signed Contracts',
						'search_items'       => 'Search Signed Contracts',
						'not_found'          => 'No signed contracts found.',
						'not_found_in_trash' => 'No signed contracts found in Trash.',
					);
				$post_args[MDJM_SIGNED_CONTRACT_POSTS] = array(
						'labels'			 	 => $template_labels[MDJM_SIGNED_CONTRACT_POSTS],
						'description'			=> 'Signed Contracts used by the MDJM plugin',
						'public'			 	 => true,
						'publicly_queryable' 	 => true,
						'show_ui'				=> false,
						'show_in_menu'	   	   => false,
						'query_var'		  	  => true,
						'rewrite'            	=> array( 'slug' => 'mdjm-signed-contract' ),
						'capability_type'    	=> array( 'mdjm_signed_contract', 'mdjm_signed_contracts' ),
						'map_meta_cap'		   => true,
						'has_archive'        	=> true,
						'hierarchical'       	   => false,
						'menu_position'      	  => 5,
						'supports'           	   => array( 'title' ),
						'menu_icon'		  	  => plugins_url( 'mobile-dj-manager/admin/images/mdjm-icon-20x20.jpg' ),
						'register_meta_box_cb'   => array( &$this, 'define_metabox' ),
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
						'register_meta_box_cb'   => array( &$this, 'define_metabox' ),
						);
						
			/* -- Events -- */
				$template_labels[MDJM_EVENT_POSTS] = array(
						'name'               => 'Events',
						'singular_name'      => 'Event',
						'menu_name'          => 'Events',
						'name_admin_bar'     => 'Event',
						'add_new'            => 'Create Event',
						'add_new_item'       => 'Create New Event',
						'new_item'           => 'New Event',
						'edit_item'          => 'Edit Event',
						'view_item'          => 'View Event',
						'all_items'          => 'All Events',
						'search_items'       => 'Search Events',
						'not_found'          => 'No events found.',
						'not_found_in_trash' => 'No events found in Trash.',
					);
				$post_args[MDJM_EVENT_POSTS] = array(
						'labels'			 	 => $template_labels[MDJM_EVENT_POSTS],
						'description'			=> 'Mobile DJ Manager Events',
						'public'			 	 => false,
						'exclude_from_search'	=> false,
						'publicly_queryable' 	 => true,
						'show_ui'				=> true,
						'show_in_menu'		   => 'edit.php?post_type=' . MDJM_EVENT_POSTS,
						'show_in_admin_bar'	  => true,
						'query_var'		 	  => true,
						'rewrite'            	=> array( 'slug' => 'mdjm-event' ),
						'capability_type'    	=> array( 'mdjm_manage_event', 'mdjm_manage_events' ),
						'map_meta_cap'		   => true,
						'has_archive'        	=> true,
						'hierarchical'       	   => false,
						'menu_position'     	  => 5,
						'supports'			   => array( 'title' ),
						'menu_icon'			  => plugins_url( 'mobile-dj-manager/admin/images/mdjm-icon-20x20.jpg' ),
						'taxonomies'			 => array( MDJM_EVENT_POSTS ),
						'register_meta_box_cb'   => array( &$this, 'define_metabox' ),
						);
			
			/* -- Transactions -- */
				$template_labels[MDJM_TRANS_POSTS] = array(
						'name'               => 'Transactions',
						'singular_name'      => 'Transaction',
						'menu_name'          => 'Transactions',
						'name_admin_bar'     => 'Transaction',
						'add_new'            => 'Add Transaction',
						'add_new_item'       => 'Add New Transaction',
						'new_item'           => 'New Transaction',
						'edit_item'          => 'Edit Transaction',
						'view_item'          => 'View Transaction',
						'all_items'          => 'All Transactions',
						'search_items'       => 'Search Transactions',
						'not_found'          => 'No Transactions found.',
						'not_found_in_trash' => 'No Transactions found in Trash.',
					);
				$post_args[MDJM_TRANS_POSTS] = array(
						'labels'			 	 => $template_labels[MDJM_TRANS_POSTS],
						'description'			=> 'Transactions for the Mobile DJ Manager plugin',
						'public'			 	 => false,
						'exclude_from_search'	=> false,
						'publicly_queryable' 	 => true,
						'show_ui'				=> true,
						'show_in_menu'		   => 'edit.php?post_type=' . MDJM_TRANS_POSTS,
						'show_in_admin_bar'	  => true,
						'rewrite' 			  	=> array( 'slug' => 'mdjm-transaction'),
						'query_var'		 	  => true,
						'capability_type'    	=> array( 'mdjm_manage_transaction', 'mdjm_manage_transactions' ),
						'map_meta_cap'		   => true,
						'has_archive'        	=> true,
						'hierarchical'       	   => false,
						'menu_position'     	  => 5,
						'supports'			   => array( 'title' ),
						'menu_icon'			  => plugins_url( 'mobile-dj-manager/admin/images/mdjm-icon-20x20.jpg' ),
						'taxonomies'			 => array( MDJM_TRANS_POSTS ),
						'register_meta_box_cb'   => array( &$this, 'define_metabox' ),
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
						'capability_type'    	=> array( 'mdjm_manage_venue', 'mdjm_manage_venues' ),
						'map_meta_cap'		   => true,
						'has_archive'        	=> true,
						'hierarchical'       	   => false,
						'menu_position'     	  => 5,
						'supports'			   => array( 'title' ),
						'menu_icon'			  => plugins_url( 'mobile-dj-manager/admin/images/mdjm-icon-20x20.jpg' ),
						'taxonomies'			 => array( MDJM_VENUE_POSTS ),
						'register_meta_box_cb'   => array( &$this, 'define_metabox' ),
					);
			
				/* Now register the new post type */
				foreach( $mdjm_post_types as $mdjm_post_type )	{
					if( !post_type_exists( $mdjm_post_type ) )
						register_post_type( $mdjm_post_type, $post_args[$mdjm_post_type] );
				}
			} // define_custom_post_types
			
			/**
			 * Register the custom post statuses
			 *
			 * @since	1.1.3
			 */
			public function register_custom_status()	{
			/* -- Event Statuses -- */
				register_post_status( 'mdjm-unattended', array(
										'label'                     => _x( 'Unattended Enquiry', 'mdjm-event' ),
										'public'                    => true,
										'exclude_from_search'       => false,
										'show_in_admin_all_list'    => true,
										'show_in_admin_status_list' => true,
										'label_count'               => _n_noop( 'Unattended Enquiry <span class="count">(%s)</span>', 'Unattended Enquiries <span class="count">(%s)</span>' ),
										) );
				register_post_status( 'mdjm-enquiry', array(
										'label'                     => _x( 'Enquiry', 'mdjm-event' ),
										'public'                    => true,
										'exclude_from_search'       => false,
										'show_in_admin_all_list'    => true,
										'show_in_admin_status_list' => true,
										'label_count'               => _n_noop( 'Enquiry <span class="count">(%s)</span>', 'Enquiries <span class="count">(%s)</span>' ),
									) );
				register_post_status( 'mdjm-approved', array(
										'label'                     => _x( 'Approved', 'mdjm-event' ),
										'public'                    => true,
										'exclude_from_search'       => false,
										'show_in_admin_all_list'    => true,
										'show_in_admin_status_list' => true,
										'label_count'               => _n_noop( 'Approved <span class="count">(%s)</span>', 'Approved <span class="count">(%s)</span>' ),
									) );
				register_post_status( 'mdjm-contract', array(
										'label'                     => _x( 'Awaiting Contract', 'mdjm-event' ),
										'public'                    => true,
										'exclude_from_search'       => false,
										'show_in_admin_all_list'    => true,
										'show_in_admin_status_list' => true,
										'label_count'               => _n_noop( 'Awaiting Contract <span class="count">(%s)</span>', 'Awaiting Contracts <span class="count">(%s)</span>' ),
									) );
				register_post_status( 'mdjm-completed', array(
										'label'                     => _x( 'Completed', 'mdjm-event' ),
										'public'                    => true,
										'exclude_from_search'       => false,
										'show_in_admin_all_list'    => true,
										'show_in_admin_status_list' => true,
										'label_count'               => _n_noop( 'Completed <span class="count">(%s)</span>', 'Completed <span class="count">(%s)</span>' ),
									) );
				register_post_status( 'mdjm-cancelled', array(
										'label'                     => _x( 'Cancelled', 'mdjm-event' ),
										'public'                    => true,
										'exclude_from_search'       => false,
										'show_in_admin_all_list'    => true,
										'show_in_admin_status_list' => true,
										'label_count'               => _n_noop( 'Cancelled <span class="count">(%s)</span>', 'Cancelled <span class="count">(%s)</span>' ),
									) );
				register_post_status( 'mdjm-rejected', array(
										'label'                     => _x( 'Rejected Enquiry', 'mdjm-event' ),
										'public'                    => true,
										'exclude_from_search'       => false,
										'show_in_admin_all_list'    => true,
										'show_in_admin_status_list' => true,
										'label_count'               => _n_noop( 'Rejected Enquiry <span class="count">(%s)</span>', 'Rejected Enquiries <span class="count">(%s)</span>' ),
									) );
				register_post_status( 'mdjm-failed', array(
										'label'                     => _x( 'Failed Enquiry', 'mdjm-event' ),
										'public'                    => true,
										'exclude_from_search'       => false,
										'show_in_admin_all_list'    => true,
										'show_in_admin_status_list' => true,
										'label_count'               => _n_noop( 'Failed Enquiry <span class="count">(%s)</span>', 'Failed Enquiries <span class="count">(%s)</span>' ),
									) );
			/* -- Communication Statuses -- */
				register_post_status( 'ready to send', array(
										'label'                     => _x( 'Ready to Send', MDJM_COMM_POSTS ),
										'public'                    => true,
										'exclude_from_search'       => false,
										'show_in_admin_all_list'    => true,
										'show_in_admin_status_list' => true,
										'label_count'               => _n_noop( 'Ready to Send <span class="count">(%s)</span>', 'Ready to Send <span class="count">(%s)</span>' ),
									) );
				register_post_status( 'sent', array(
										'label'                     => _x( 'Sent', MDJM_COMM_POSTS ),
										'public'                    => true,
										'exclude_from_search'       => false,
										'show_in_admin_all_list'    => true,
										'show_in_admin_status_list' => true,
										'label_count'               => _n_noop( 'Sent <span class="count">(%s)</span>', 'Sent <span class="count">(%s)</span>' ),
									) );
				register_post_status( 'opened', array(
										'label'                     => _x( 'Opened', MDJM_COMM_POSTS ),
										'public'                    => true,
										'exclude_from_search'       => false,
										'show_in_admin_all_list'    => true,
										'show_in_admin_status_list' => true,
										'label_count'               => _n_noop( 'Opened <span class="count">(%s)</span>', 'Opened <span class="count">(%s)</span>' ),
									) );
				register_post_status( 'failed', array(
										'label'                     => _x( 'Failed', MDJM_COMM_POSTS ),
										'public'                    => true,
										'exclude_from_search'       => false,
										'show_in_admin_all_list'    => true,
										'show_in_admin_status_list' => true,
										'label_count'               => _n_noop( 'Failed <span class="count">(%s)</span>', 'Failed <span class="count">(%s)</span>' ),
									) );
			/* -- Transaction Statuses -- */
				register_post_status( 'mdjm-income', array(
										'label'                     => _x( 'Income', 'mdjm-transaction' ),
										'public'                    => true,
										'exclude_from_search'       => false,
										'show_in_admin_all_list'    => true,
										'show_in_admin_status_list' => true,
										'label_count'               => _n_noop( 'Received Payment <span class="count">(%s)</span>', 'Received Payments <span class="count">(%s)</span>' ),
										) );
				register_post_status( 'mdjm-expenditure', array(
										'label'                     => _x( 'Expenditure', 'mdjm-transaction' ),
										'public'                    => true,
										'exclude_from_search'       => false,
										'show_in_admin_all_list'    => true,
										'show_in_admin_status_list' => true,
										'label_count'               => _n_noop( 'Ougoing Payment <span class="count">(%s)</span>', 'Ougoing Payments <span class="count">(%s)</span>' ),
										) );
			} // register_custom_status
			
			/**
			 * Register the custom taxonomies
			 *
			 * @since	1.1.3
			 */
			public function register_custom_taxonomies()	{
			/* -- Event Types -- */
				if( !get_taxonomy( 'event-types' ) )	{
					$tax_labels[MDJM_EVENT_POSTS] = array(
									'name'              		   => _x( 'Event Type', 'taxonomy general name' ),
									'singular_name'     		  => _x( 'Event Type', 'taxonomy singular name' ),
									'search_items'      		   => __( 'Search Event Types' ),
									'all_items'         		  => __( 'All Event Types' ),
									'edit_item'        		  => __( 'Edit Event Type' ),
									'update_item'       			=> __( 'Update Event Type' ),
									'add_new_item'      		   => __( 'Add New Event Type' ),
									'new_item_name'     		  => __( 'New Event Type' ),
									'menu_name'         		  => __( 'Event Types' ),
									'separate_items_with_commas' => NULL,
									'choose_from_most_used'	  => __( 'Choose from the most popular Event Types' ),
									'not_found'				  => __( 'No event types found' ),
									);
					$tax_args[MDJM_EVENT_POSTS] = array(
									'hierarchical'      	   => true,
									'labels'            	 => $tax_labels[MDJM_EVENT_POSTS],
									'show_ui'           		=> true,
									'show_admin_column' 	  => false,
									'query_var'         	  => true,
									'rewrite'           		=> array( 'slug' => 'event-types' ),
									'update_count_callback'      => '_update_generic_term_count',
								);
					register_taxonomy( 'event-types', MDJM_EVENT_POSTS, $tax_args[MDJM_EVENT_POSTS] );
				}

				/* -- Transaction Types -- */
				if( !get_taxonomy( 'transaction-types' ) )	{
					$tax_labels[MDJM_TRANS_POSTS] = array(
									'name'              		   => _x( 'Transaction Type', 'taxonomy general name' ),
									'singular_name'     		  => _x( 'Transaction Type', 'taxonomy singular name' ),
									'search_items'      		   => __( 'Search Transaction Types' ),
									'all_items'         		  => __( 'All Transaction Types' ),
									'edit_item'        		  => __( 'Edit Transaction Type' ),
									'update_item'       			=> __( 'Update Transaction Type' ),
									'add_new_item'      		   => __( 'Add New Transaction Type' ),
									'new_item_name'     		  => __( 'New Transaction Type' ),
									'menu_name'         		  => __( 'Transaction Types' ),
									'separate_items_with_commas' => NULL,
									'choose_from_most_used'	  => __( 'Choose from the most popular Transaction Types' ),
									'not_found'				  => __( 'No transaction types found' ),
									);
					$tax_args[MDJM_TRANS_POSTS] = array(
									'hierarchical'      	   => true,
									'labels'            	 => $tax_labels[MDJM_TRANS_POSTS],
									'show_ui'           		=> true,
									'show_admin_column' 	  => false,
									'query_var'         	  => true,
									'rewrite'           		=> array( 'slug' => 'transaction-types' ),
									'update_count_callback'      => '_update_generic_term_count',
								);
					register_taxonomy( 'transaction-types', MDJM_TRANS_POSTS, $tax_args[MDJM_TRANS_POSTS] );
				}
			/* -- Venue Details -- */
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
			} // register_custom_taxonomies
			
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
					$event_data['_mdjm_event_client'] = $_POST['client_name'] != 'add_new' ? $_POST['client_name'] : $mdjm->mdjm_events->mdjm_add_client();
					
					if( $new_post === false && $_POST['client_name'] != $current_meta['_mdjm_event_client'][0] )
						$field_updates[] = '     | Client changed from ' . $current_meta['_mdjm_event_client'][0] . ' to ' . $_POST['client_name'];
					
					if( empty( $_POST['client_name'] ) )	{
						if( MDJM_DEBUG == true )
							 $this->debug_logger( '	-- No content passed for filtering ' );
					}
					
					if( !empty( $_POST['mdjm_reset_pw'] ) )	{
						if( MDJM_DEBUG == true )
							$GLOBALS['mdjm_debug']->log_it( '	-- User ' . $event_data['_mdjm_event_client'] . ' flagged for password reset' );
							
						update_user_meta( $event_data['_mdjm_event_client'], 'mdjm_pass_action', wp_generate_password( $mdjm_settings['clientzone']['pass_length'] ) );
					}
										
					/* -- Get the Venue ID -- */
					$event_data['_mdjm_event_venue_id'] = $_POST['venue_id'] != 'manual' ? $_POST['venue_id'] : '';
					if( $new_post === false && isset( $current_meta['_mdjm_event_venue_id'][0] ) && $_POST['venue_id'] != $current_meta['_mdjm_event_venue_id'][0] )	{
						$field_updates[] = 'Venue changed from ' . ( $current_meta['_mdjm_event_venue_id'][0] != 'Manual' ?
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
							debug_logger( '	-- New venue to be created' );
						remove_action( 'save_post', array( &$this, 'save_custom_post' ), 10, 2 );
						$event_data['_mdjm_event_venue_id'] = $mdjm->mdjm_events->mdjm_add_venue( 
																					array( 'venue_name' => $_POST['venue_name'] ), 
																					$venue_meta );
						add_action( 'save_post', array( &$this, 'save_custom_post' ), 10, 2 );
					}
					/* -- Manual venue, set event fields -- */
					else	{
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
					
					/* -- Prepare the remaining event fields -- */
					$event_data['_mdjm_event_last_updated_by'] = $current_user->ID;
					
					// Event name
					$_POST['_mdjm_event_name'] = ( !empty( $_POST['_mdjm_event_name'] ) ? $_POST['_mdjm_event_name'] : 
						get_term( $_POST['mdjm_event_type'], 'event-types' )->name );
					
					// Playlist
					if( $new_post == true || empty( $current_meta['_mdjm_event_playlist_access'][0] ) )
						$event_data['_mdjm_event_playlist_access'] = $mdjm->mdjm_events->playlist_ref();
					
					foreach( $_POST as $key => $value )	{
						if( substr( $key, 0, 12 ) == '_mdjm_event_' )
							$event_data[$key] = $value;	
					}
					/* -- Set the event & dj setup times -- */
						/* -- Start time -- */
						$event_data['_mdjm_event_start'] = MDJM_TIME_FORMAT == 'H:i' ? 
							date( 'H:i:s', strtotime( $_POST['event_start_hr'] . ':' . $_POST['event_start_min'] ) ) : 
							date( 'H:i:s', strtotime( $_POST['event_start_hr'] . ':' . $_POST['event_start_min'] . isset( $_POST['event_start_period'] ) ? $_POST['event_start_period'] : '' ) );
						
						/* -- Finish time -- */	
						$event_data['_mdjm_event_finish'] = MDJM_TIME_FORMAT == 'H:i' ? 
							date( 'H:i:s', strtotime( $_POST['event_finish_hr'] . ':' . $_POST['event_finish_min'] ) ) : 
							date( 'H:i:s', strtotime( $_POST['event_finish_hr'] . ':' . $_POST['event_finish_min'] . isset( $_POST['event_finish_period'] ) ? $_POST['event_finish_period'] : '' ) );
							
						/* -- DJ Setup time -- */
						$event_data['_mdjm_event_djsetup_time'] = !empty( $_POST['dj_setup_hr'] ) && MDJM_TIME_FORMAT == 'H:i' ? 
							date( 'H:i:s', strtotime( $_POST['dj_setup_hr'] . ':' . $_POST['dj_setup_min'] ) ) : 
							date( 'H:i:s', strtotime( $_POST['dj_setup_hr'] . ':' . $_POST['dj_setup_min'] . isset( $_POST['dj_setup_period'] ) ? $_POST['dj_setup_period'] : '' ) );
						
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
							$field_updates[] = 'Event Type changed from ' . $existing_event_type[0]->name . ' to ' . get_term( $_POST['mdjm_event_type'], 'event-types' )->name;
						
						$mdjm->mdjm_events->mdjm_assign_event_type( $_POST['mdjm_event_type'] );
						
						/* -- Add the meta -- */
						if( MDJM_DEBUG == true )
							 $GLOBALS['mdjm_debug']->log_it( '	-- Beginning Meta Updates' );
							 
						foreach( $event_data as $event_meta_key => $event_meta_value )	{
							
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
							if ( $event_meta_value && '' == $current_meta[$event_meta_key][0] )	{
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
								implode( "\r\n" . '     | ', $field_updates ) );
								
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
						/* -- Check for manual payment received -- */
						if( $deposit_payment == true || $balance_payment == true )	{
							if( $balance_payment == true )
								$type = MDJM_BALANCE_LABEL;
								
							else
								$type = MDJM_DEPOSIT_LABEL;
							/* -- Initiate transactions class -- */
							if( !class_exists( 'MDJM_Transactions' ) )
								require( MDJM_PLUGIN_DIR . '/admin/includes/class/class-mdjm-transactions.php' );
								
							$mdjm_trans = new MDJM_Transactions();
							$mdjm_trans->manual_event_payment( $type, $post->ID );
						}
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
				
				/* -- Define queries for sorting columns -- */
				$this->column_sort( $query );
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
			
			/*
			 * Define the table columns that are displayed for
			 * communication posts
			 * 
			 * @since 1.1.2
			 * @params: columns => array
			 * @return: $columns
			 */
			/* -- Communication Columns -- */
			public function define_mdjm_communication_post_columns( $columns ) {
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
			public function define_contract_post_columns( $columns ) {
				$columns = array(
						'cb'			   => '<input type="checkbox" />',
						'title' 			=> __( 'Contract Name' ),
						'default'		  => __( 'Is Default?' ),
						'assigned'		 => __( 'Assigned To' ),
						'author'		   => __( 'Created By' ),
						'date' 			 => __( 'Date' ),
					);
				return $columns;
			} // define_contract_post_columns
			
			/* -- Event Columns -- */
			public function define_mdjm_event_post_columns( $columns ) {
				$columns = array(
						'cb'			   => '<input type="checkbox" />',
						'title'			=> __( 'Event ID' ),
						'event_date'   	   => __( 'Date' ),
						'client'		  => __( 'Client' ),
						'dj'		   => __( MDJM_DJ ),
						'event_status'	=> __( 'Status' ),
						'event_type' 	=> __( 'Event Type' ),
						'value'			=> __( 'Value' ),
						'playlist'		=> __( 'Playlist' ),
						'journal'		=> __( 'Journal' ),
					);
				return $columns;
			} // define_mdjm_event_post_columns
			
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
			
			/* -- Transaction Columns -- */
			public function define_mdjm_transaction_post_columns( $columns ) {
				$columns = array(
						'cb'			   => '<input type="checkbox" />',
						'title' 	 		=> __( 'ID' ),
						'tdate'				=> __( 'Date' ),
						'direction'			=> __( 'In/Out' ),
						'detail'			=> __( 'Details' ),
						'event'		  => __( 'Event' ),
						'value'		  => __( 'Value' ),
					);
				return $columns;
			} // define_mdjm_transaction_post_columns
			
			/* -- Venue Columns -- */
			public function define_mdjm_venue_post_columns( $columns ) {
				$columns = array(
						'cb'			   => '<input type="checkbox" />',
						'title' 	 		=> __( 'Venue' ),
						'contact'		  => __( 'Contact' ),
						'phone'		    => __( 'Phone' ),
						'town' 			 => __( 'Town' ),
						'county'   		   => __( 'County' ),
						'event_count'	 => __( 'Events' ),
						'info'		     => __( 'Information' ),
						'details'	      => __( 'Details' ),
					);
				return $columns;
			} // define_venue_post_columns
			
			/*
			 * define_custom_post_column_data
			 * Define  data that is displayed in each column for the custom post types
			 * 
			 * @since 1.1.2
			 * @params: $column
			 */
			public function define_custom_post_column_data( $column )	{
				global $post, $mdjm, $mdjm_settings, $mdjm_post_types, $wpdb;
				
				if( !in_array( $post->post_type, $mdjm_post_types ) )
					return;
				
				/* -- mdjm-communication -- */
				elseif( $post->post_type == MDJM_COMM_POSTS )	{
					switch ( $column ) {
						/* -- Date Sent -- */
						case 'date_sent':
							echo date( MDJM_TIME_FORMAT . ' ' . MDJM_SHORTDATE_FORMAT, get_post_meta( $post->ID, '_date_sent', true ) );
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
							
							echo ( !empty( $event ) ? '<a href="'. get_edit_post_link( $event ) . '">' . MDJM_EVENT_PREFIX . $event . '</a>' : 'N/A' );
							
							break;
						
						/* -- Status -- */
						case 'current_status':							
							$change_date = !empty( $post->post_modified ) && $post->post_status == 'opened' ? date( MDJM_TIME_FORMAT . ' ' . MDJM_SHORTDATE_FORMAT, strtotime( $post->post_modified ) ) : '';
							$open_count = !empty( $count ) && $post->post_status == 'opened' ? ' (' . $count . ')' : '';
							
							echo ucwords( $post->post_status ) . ' ' . 
							( !empty( $post->post_modified ) && $post->post_status == 'opened' ? 
							date( MDJM_TIME_FORMAT . ' ' . MDJM_SHORTDATE_FORMAT, strtotime( $post->post_modified ) ) : '' );
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
							echo $post->ID == $mdjm_settings['events']['default_contract'] ? 'Yes' : 'No';
							break;
						/* -- Assigned To -- */
						case 'assigned':
							$contract_events = get_posts(
								array(
									'post_type'		=> MDJM_EVENT_POSTS,
									'posts_per_page'   => -1,
									'meta_key'	 	 => '_mdjm_event_contract',
									'meta_value'   	   => $post->ID,
									'post_status'  	  => 'any',
									)
								);
				
							echo count( $contract_events ) . _n( ' Event', ' Events', count( $contract_events ) );
							break;	
					} // switch
				}
				
				/* -- mdjm-event -- */
				elseif( $post->post_type == MDJM_EVENT_POSTS )	{
					switch ( $column ) {
						/* -- Event Date -- */
						case 'event_date':
							echo sprintf( '<a href="' . admin_url( 'post.php?post=%s&action=edit' ) . '">%s</a>', 
								$post->ID, date( 'd M Y', strtotime( get_post_meta( $post->ID, '_mdjm_event_date', true ) ) ) );
							break;
						/* -- Client -- */
						case 'client':
							$client = get_userdata( get_post_meta( $post->ID, '_mdjm_event_client', true ) );
							echo ( !empty( $client ) ? $client->display_name : '<span class="mdjm-form-error">Not Assigned</span>' );
							break;
						/* -- DJ -- */
						case 'dj':
							$dj = get_userdata( get_post_meta( $post->ID, '_mdjm_event_dj', true ) );
							echo ( !empty( $dj ) ? $dj->display_name : '<span class="mdjm-form-error">Not Assigned</span>' );
							break;
						/* -- Status -- */
						case 'event_status':
							echo get_post_status_object( $post->post_status )->label;
							if( isset( $_GET['availability'] ) && $post->ID == $_GET['e_id'] )	{
								if( is_dj() )	{
									$dj_avail = $this->availability_check( $_GET['availability'], $current_user->ID );
								}
								else	{
									$dj_avail = $this->availability_check( $_GET['availability'] );
								}
							}
							break;
						/* -- Event Type -- */
						case 'event_type':
							$event_types = get_the_terms( $post->ID, 'event-types' );
							if( is_array( $event_types ) )	{
								foreach( $event_types as $key => $event_type ) {
									$event_types[$key] = $event_type->name;
								}
								echo implode( "<br/>", $event_types );
							}
							break;
						/* -- Status -- */
						case 'value':
							$value = get_post_meta( $post->ID, '_mdjm_event_cost', true );
							echo ( !empty( $value ) ? display_price( $value ) : '<span class="mdjm-form-error">' . display_price( '0.00' ) . '</span>' );
							break;
						/* -- Playlist -- */
						case 'playlist':
							$playlist = $mdjm->mdjm_events->count_playlist_entries( $post->ID );
							echo '<a href="' . mdjm_get_admin_page( 'playlists' ) . $post->ID . '">' . $playlist . _n( ' Song', ' Songs', $playlist ) . '</a>' . "\r\n";
							break;
						/* -- Journal -- */
						case 'journal':
							echo '<a href="' . admin_url( '/edit-comments.php?p=' . $post->ID ) . '">' . 
							wp_count_comments( $post->ID )->approved . _n( ' Entry', ' Entries', wp_count_comments( $post->ID )->approved ) .
							'</a>' . "\r\n";
							break;
					} // switch
				}
				
				/* -- mdjm-transaction -- */
				elseif( $post->post_type == MDJM_TRANS_POSTS )	{
					switch ( $column ) {	
						/* -- Details -- */
						case 'detail':
							$trans_types = get_the_terms( $post->ID, 'transaction-types' );
							if( is_array( $trans_types ) )	{
								foreach( $trans_types as $key => $trans_type ) {
									$trans_types[$key] = $trans_type->name;
								}
								echo implode( "<br/>", $trans_types );
							}
							break;	
						/* -- Date -- */
						case 'tdate':
							echo get_post_time( 'd M Y' );					
							break;
						/* -- Direction -- */
						case 'direction':
							echo ( $post->post_status == 'mdjm-income' ? 
								'<span style="color:green">In</span>' : 
								'<span style="color:red">&nbsp;&nbsp;&nbsp;&nbsp;Out</span>' );					
							break;		
						/* -- Event -- */
						case 'event':
							echo ( wp_get_post_parent_id( $post->ID ) ? 
								'<a href="' . admin_url( '/post.php?post=' . wp_get_post_parent_id( $post->ID ) . '&action=edit' ) . '">' . 
								MDJM_EVENT_PREFIX . wp_get_post_parent_id( $post->ID ) . '</a>' : 
								'N/A' );					
							break;
						/* -- Value -- */
						case 'value':
							echo display_price( get_post_meta( $post->ID, '_mdjm_txn_total', true ) );
							break;
					}
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
						
						case 'event_count':
							$events_at_venue = get_posts( array(
														'post_type'	=> MDJM_EVENT_POSTS,
														'meta_key'	 => '_mdjm_event_venue_id',
														'meta_value'   => $post->ID,
														'post_status'  => array( 'mdjm-approved', 'mdjm-contract', 'mdjm-completed', 'mdjm-enquiry', 'mdjm-unattended' ),
														) );
							echo( !empty( $events_at_venue ) ? count( $events_at_venue ) : '0' );
							break;	
						/* -- Information -- */
						case 'info':
							echo stripslashes( get_post_meta( $post->ID, '_venue_information', true ) );
							break;
						
						/* -- Details -- */
						case 'details':
							$venue_terms = get_the_terms( $post->ID, 'venue-details' );
							$venue_term = '';
							if( !empty( $venue_terms ) )	{
								$venue_term .= '<ul class="details">' . "\r\n";
								foreach( $venue_terms as $v_term )	{
									$venue_term .= '<li>' . $v_term->name . '</li>' . "\r\n";	
								}
								$venue_term .= '</ul>' . "\r\n";
							}
							echo ( !empty( $venue_term ) ? $venue_term : '' );
							break;
					} // switch
				}
				
				else	{
					return;	
				}
			} // define_custom_post_columns

/**
 * -- POST COLUMN SORTING
 */
			/**
			 * column_sorting
			 * Sets the sortable columns for custom post types
			 * 
			 * 
			 * @since 1.1.3
			 * @params: $column
			 * @return: $column
			 */
			public function column_sorting( $columns )	{
				global $post, $mdjm_post_types;
				
				if( !in_array( $post->post_type, $mdjm_post_types ) )
					return; 
				
				/* -- Events sortable columns -- */
				if( $post->post_type == MDJM_EVENT_POSTS )	{
					$columns['event_date'] = 'event_date';
					$columns['event_status'] = 'event_status';
					$columns['event_type'] = 'event_type';
					$columns['value'] = 'value';
				}
				
				/* -- Venues sortable columns -- */
				if( $post->post_type == MDJM_VENUE_POSTS )	{
					$columns['town'] = 'town';
					$columns['county'] = 'county';
				}
				return $columns;
			} // column_sorting
			
			/**
			 * column_sort
			 * The queries used to sort columns
			 * 
			 * 
			 * @since 1.1.3
			 * @params: $query
			 * @return:
			 */
			public function column_sort( $query )	{
				
				if( !is_admin() )
					return;
				
				$orderby = $query->get( 'orderby' );
				
				/* -- Event Sorting -- */
				if ( 'event_date' == 'orderby' )	{
					$query->set( 'meta_key', '_mdjm_event_date' );
					$query->set( 'orderby', 'meta_value_num' );
				}
				if ( 'event_status' == 'orderby' )	{
					$query->set( 'orderby', 'post_status' );
				}
				if ( 'event_type' == 'orderby' )	{
					$query->set( 'orderby', 'category_name' );
				}
				if ( 'value' == 'orderby' )	{
					$query->set( 'meta_key', '_mdjm_event_cost' );
					$query->set( 'orderby', 'meta_value_num' );
				}
				
				/* -- Venue Sorting -- */
				if ( 'town' == 'orderby' )	{
					$query->set( 'meta_key', '_venue_town' );
					$query->set( 'orderby', 'meta_value_num' );
				}
				if ( 'county' == 'orderby' )	{
					$query->set( 'meta_key', '_venue_county' );
					$query->set( 'orderby', 'meta_value_num' );
				}
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
			 * define_mdjm_communication_bulk_action_list
			 * Define which options are available within the 
			 * bulk actions drop down list for each custom post type
			 *
			 * @since 1.1.3
			 * @params: $actions
			 * @return: $actions
			 */
			/* -- Remove Edit from Communication Bulk Actions -- */
			public function define_mdjm_communication_bulk_action_list( $actions )	{
				unset( $actions['edit'] );
				return $actions;
			} // define_mdjm_communication_bulk_action_list
						
			/*
			 * define_mdjm_event_bulk_action_list
			 * Define which options are available within the 
			 * bulk actions drop down list for each custom post type
			 *
			 * @since 1.1.3
			 * @params: $actions
			 * @return: $actions
			 */
			/* -- Remove Move to Trash from Event Bulk Actions -- */
			public function define_mdjm_event_bulk_action_list( $actions )	{
				unset( $actions['edit'] );
				//unset( $actions['trash'] );
				
				return $actions;
			} // define_mdjm_event_bulk_action_list
						
			/*
			 * define_mdjm_venue_bulk_action_list
			 * Define which options are available within the 
			 * bulk actions drop down list for each custom post type
			 *
			 * @since 1.1.3
			 * @params: $actions
			 * @return: $actions
			 */
			/* -- Remove Edit from Venue Bulk Actions -- */
			public function define_mdjm_venue_bulk_action_list( $actions )	{
				unset( $actions['edit'] );
				return $actions;
			} // define_mdjm_venue_bulk_action_list

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
					if( isset( $actions['edit'] ) )
						unset( $actions['edit'] );	
					if( isset( $actions['inline hide-if-no-js'] ) )
						unset( $actions['inline hide-if-no-js'] );
					
				/* -- Unattended Event Row Actions -- */
					if( $post->post_status == 'mdjm-unattended' )	{
						// Quote for event
						$actions['quote'] = sprintf( '<a href="' . admin_url( 'post.php?post=%s&action=%s&mdjm_action=%s' ) . 
						'">Quote</a>', $post->ID, 'edit', 'respond' );
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
				if (isset($_GET['post_type']))
					$type = $_GET['post_type'];
				
				if( MDJM_EVENT_POSTS == $type )	{
					$this->event_date_filter_dropdown();
					$this->event_type_filter_dropdown();
					if( MDJM_MULTI == true )
						$this->event_dj_filter_dropdown();
					$this->event_client_filter_dropdown();	
				}
				if( MDJM_TRANS_POSTS == $type )
					$this->transaction_type_filter_dropdown();

			} // post_filter_list
			
			/*
			 * Filter dropdown for Event Month
			 * Display the drop down list to enable user to select event month/year
			 * to display
			 *
			 */
			public function event_date_filter_dropdown()	{
				global $wpdb, $wp_locale;
				
				$type = '';
				if (isset($_GET['post_type'])) {
					$type = $_GET['post_type'];
				}
				
				if( MDJM_EVENT_POSTS == $type )	{
					$month_query = "SELECT DISTINCT YEAR( meta_value ) as year, MONTH( meta_value ) as month 
						FROM `" . $wpdb->postmeta . "` WHERE `meta_key` = '_mdjm_event_date'";
																	
					$months = $wpdb->get_results( $month_query );
						
					$month_count = count( $months );
					
					if ( !$month_count || ( 1 == $month_count && 0 == $months[0]->month ) )
						return;
		
					$m = isset( $_GET['mdjm_filter_date'] ) ? (int) $_GET['mdjm_filter_date'] : 0;
					
					?>
					<label for="filter-by-date" class="screen-reader-text">Filter by Date</label>
					<select name="mdjm_filter_date" id="filter-by-date">
						<option value="0"><?php _e( 'All Dates' ); ?></option>
					<?php
					foreach ( $months as $arc_row ) {
						if ( 0 == $arc_row->year )
							continue;
			
						$month = zeroise( $arc_row->month, 2 );
						$year = $arc_row->year;
			
						printf( "<option %s value='%s'>%s</option>\n",
							selected( $m, $year . $month, false ),
							esc_attr( $arc_row->year . $month ),
							/* translators: 1: month name, 2: 4-digit year */
							sprintf( __( '%1$s %2$d' ), $wp_locale->get_month( $month ), $year )
						);
					}
					?>
                    </select>
                    <?php
				}
				
			} // event_date_filter_dropdown
			
			/*
			 * Filter dropdown for Event DJ
			 * Display the drop down list to enable user to select event month/year
			 * to display
			 *
			 */
			public function event_dj_filter_dropdown()	{
				global $wpdb, $current_user;
				
				if( MDJM_MULTI != true )
					return;
				
				$type = '';
				if (isset($_GET['post_type'])) {
					$type = $_GET['post_type'];
				}
				
				if( MDJM_EVENT_POSTS == $type )	{
					$dj_query = "SELECT DISTINCT meta_value FROM `" . $wpdb->postmeta . 
						"` WHERE `meta_key` = '_mdjm_event_dj'";
											
					$djs = $wpdb->get_results( $dj_query );
					$dj_count = count( $djs );
					
					if ( !$dj_count || 1 == $dj_count )
						return;
		
					$artist = isset( $_GET['mdjm_filter_dj'] ) ? (int) $_GET['mdjm_filter_dj'] : 0;
					
					?>
					<label for="filter-by-dj" class="screen-reader-text">Filter by <?php echo __( MDJM_DJ ); ?></label>
					<select name="mdjm_filter_dj" id="filter-by-dj">
						<option value="0"<?php selected( $artist, 0, false ); ?>><?php _e( 'All ' . MDJM_DJ . '\'s' ); ?></option>
					<?php
					foreach( $djs as $dj ) {
						$djinfo = get_userdata( $dj->meta_value );
						if( empty( $djinfo->display_name ) )
							continue;
							
						printf( "<option %s value='%s'>%s</option>\n",
							selected( $artist, $dj->meta_value, false ),
							$dj->meta_value,
							$djinfo->display_name
						);
					}
					?>
                    </select>
                    <?php
				}
				
			} // event_dj_filter_dropdown
			
			/*
			 * Filter dropdown for Event Client
			 * Display the drop down list to enable user to select event month/year
			 * to display
			 *
			 */
			public function event_client_filter_dropdown()	{
				global $wpdb, $current_user;
								
				$type = '';
				if (isset($_GET['post_type']))
					$type = $_GET['post_type'];
				
				if( MDJM_EVENT_POSTS == $type )	{
					$client_query = "SELECT DISTINCT meta_value FROM `" . $wpdb->postmeta . 
						"` WHERE `meta_key` = '_mdjm_event_client'";
											
					$clients = $wpdb->get_results( $client_query );
					$client_count = count( $clients );
					
					if ( !$client_count || 1 == $client_count )
						return;
		
					$c = isset( $_GET['mdjm_filter_client'] ) ? (int) $_GET['mdjm_filter_client'] : 0;
					
					?>
					<label for="filter-by-client" class="screen-reader-text">Filter by <?php echo __( 'Client' ); ?></label>
					<select name="mdjm_filter_client" id="mdjm_filter_client-by-dj">
						<option value="0"<?php selected( $c, 0, false ); ?>><?php _e( 'All Client\'s' ); ?></option>
					<?php
					foreach( $clients as $client ) {
						$clientinfo = get_userdata( $client->meta_value );
						if( empty( $clientinfo->display_name ) )
							continue;
						
						printf( "<option %s value='%s'>%s</option>\n",
							selected( $c, $client->meta_value, false ),
							$client->meta_value,
							$clientinfo->display_name
						);
					}
					?>
                    </select>
                    <?php
				}
				
			} // event_client_filter_dropdown
			
			/*
			 * Filter dropdown for Event Types
			 * Display the drop down list to enable user to select event type
			 * to display
			 *
			 */
			public function event_type_filter_dropdown()	{
				global $mdjm;
				
				$type = '';
				if (isset($_GET['post_type'])) {
					$type = $_GET['post_type'];
				}
				
				if( MDJM_EVENT_POSTS == $type )	{
					$event_types = get_categories( array(
												'type'			  => MDJM_EVENT_POSTS,
												'taxonomy'		  => 'event-types',
												'pad_counts'		=> false,
												'hide_empty'		=> true,
												'orderby'		  => 'name',
												) );
					foreach( $event_types as $event_type )	{
						$values[$event_type->term_id] = $event_type->name;
					}
					?>
					<select name="mdjm_filter_type">
					<option value=""><?php echo __( 'All Event Types' ); ?></option>
					<?php
						$current_v = isset( $_GET['mdjm_filter_type'] ) ? $_GET['mdjm_filter_type'] : '';
						
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
					?>
					</select>
					<?php
				}
			} // event_type_filter_dropdown
			
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
				$screens = array( 'dj-manager_page_mdjm-comms', 'dj-manager_page_mdjm-settings' );
				
				/* -- Add the MDJM TinyMCE buttons -- */
				$screen = get_current_screen();
				if( in_array( get_post_type(), $post_types ) || in_array( $screen->id, $screens ) )
					$mdjm->mce_shortcode_button();
				
				/* -- Edit styles for given page as required -- */
				$this->define_styles();
			} // mdjm_admin_head
			
			/*
			 * define_styles
			 * Define styles for each custom post type
			 * 
			 * @since 1.1.2
			 * 
			 */
			public function define_styles() {
				global $mdjm, $mdjm_post_types;
				
				/* -- No Add New for Communications -- */
				if( MDJM_COMM_POSTS == get_post_type() )	{
					// Remove the Add New post button
					echo '<style type="text/css">' . "\r\n" . 
					'#favorite-actions {' . "\r\n" . 
					' 	display:none;' . "\r\n" . 
					'}' . "\r\n" . 
					'.add-new-h2{' . "\r\n" . 
					' 	display:none;' . "\r\n" . 
					'}' . "\r\n" . 
					'</style>' . "\r\n";
				}
				/* -- Remove the date filter where not neeeded -- */
				if( in_array( get_post_type(), $mdjm_post_types ) )	{
					if( MDJM_COMM_POSTS != get_post_type() && MDJM_TRANS_POSTS != get_post_type() )
						add_filter( 'months_dropdown_results', '__return_empty_array' );
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
			public function rename_publish_button( $translation, $text )	{
				global $post;
				
				if( MDJM_CONTRACT_POSTS == get_post_type() )	{
					if( $text == 'Publish' )
						return __( 'Save Contract' );
					elseif( $text == 'Update' )
						return __( 'Update Contract' );
				}
				if( MDJM_EMAIL_POSTS == get_post_type() )	{
					if( $text == 'Publish' )
						return __( 'Save Template' );
					elseif( $text == 'Update' )
						return __( 'Update Template' );
				}
				if( MDJM_EVENT_POSTS == get_post_type() )	{
		
					$event_stati = get_event_stati();
					
					if( $text == 'Publish' && isset( $event_stati[$post->post_status] ) )
						return __( 'Update Event' );
					elseif( $text == 'Publish' )
						return __( 'Create Event' );
					elseif( $text == 'Update' )
						return __( 'Update Event' );
				}
				if( MDJM_TRANS_POSTS == get_post_type() )	{
					if( $text == 'Publish' )
						return __( 'Save Transaction' );
					elseif( $text == 'Update' )
						return __( 'Update Transaction' );	
				}
				if( MDJM_VENUE_POSTS == get_post_type() )	{
					if( $text == 'Publish' )
						return __( 'Save Venue' );
					elseif( $text == 'Update' )
						return __( 'Update Venue' );	
				}
				return $translation;
			} // rename_publish_button
			
			/*
			 * set_post_title
			 * Set the title of a custom post upon new creation
			 * & make it readonly
			 * 
			 * @since 1.1.2
			 * @params:
			 * @return:
			 */
			public function set_post_title( $post ) {
				global $mdjm_settings, $pagenow;
				
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
			 * title_placeholder
			 * Set the placeholder (title) text for custom post types
			 * 
			 *
			 * @param    str	$title
			 *
			 * @return   str	$title
			 * @since    1.1.3
			 */
			public function title_placeholder( $title )	{
				global $mdjm_post_types, $post;
				
				if( empty( $post ) || !in_array( $post->post_type, $mdjm_post_types ) )
					return;
				
				if( $post->post_type == MDJM_CONTRACT_POSTS )	{
					$title = 'Enter the Contract name here...';	
				}
				elseif( $post->post_type == MDJM_EMAIL_POSTS )	{
					$title = 'Enter the Template name here. Used as email subject, shortcodes allowed';	
				}
				elseif( $post->post_type == MDJM_VENUE_POSTS )	{
					$title = 'Enter the Venue name here...';	
				}
				
				return $title;
				
			} // title_placeholder
			
			/**
			 * event_rows
			 * Colour the table rows for event lists
			 * 
			 *
			 * @param
			 *
			 * @return
			 * @since    1.1.3
			 */
			public function event_rows()	{
				global $post, $mdjm_post_types;
				
				if( !isset( $post ) )
					return;
				
				if( !in_array( $post->post_type, $mdjm_post_types ) )
					return;
					
				if( $post->post_type == MDJM_EVENT_POSTS )	{
					?>
					<style>
					/* Color by post Status */
					.status-mdjm-unattended	{
						background: #FFEBE8 !important;
					}
					</style>
					<?php
				}
			} // event_rows

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
					add_meta_box( 'mdjm-email-details', __( 'Details', 'textdomain' ), MDJM_COMM_POSTS . '_post_details_metabox', MDJM_COMM_POSTS, 'side', 'high' );
					
					/* -- Main Body -- */
					add_meta_box( 'mdjm-email-review', __( 'Email Content', 'textdomain' ), str_replace( '-', '_', MDJM_COMM_POSTS ) . '_post_output_metabox', MDJM_COMM_POSTS, 'normal', 'high' );
				}
			/* -- Contract Templates -- */
				if( $post->post_type == MDJM_CONTRACT_POSTS )	{
					/* -- Main Body -- */
					add_meta_box( 'mdjm-contract-details', __( 'Contract Details', 'textdomain' ), str_replace( '-', '_', MDJM_CONTRACT_POSTS ) . '_post_details_metabox', MDJM_CONTRACT_POSTS, 'side' );
				}
			/* -- Events -- */
				if( $post->post_type == MDJM_EVENT_POSTS )	{
					$event_stati = get_event_stati();
					/* -- Main Body -- */
					remove_meta_box( 'submitdiv', MDJM_EVENT_POSTS, 'side' );
					remove_meta_box( 'event-typesdiv', MDJM_EVENT_POSTS, 'side' );
					add_meta_box( 'mdjm-event-client', __( 'Client Details', 'textdomain' ), str_replace( '-', '_', MDJM_EVENT_POSTS ) . '_post_client_metabox', MDJM_EVENT_POSTS, 'normal', 'high' );
					add_meta_box( 'mdjm-event-details', __( 'Event Details', 'textdomain' ), str_replace( '-', '_', MDJM_EVENT_POSTS ) . '_post_event_metabox', MDJM_EVENT_POSTS, 'normal', 'high' );
					add_meta_box( 'mdjm-event-venue', __( 'Venue Details', 'textdomain' ), str_replace( '-', '_', MDJM_EVENT_POSTS ) . '_post_venue_metabox', MDJM_EVENT_POSTS, 'normal', '' );
					add_meta_box( 'mdjm-event-admin', __( 'Administration', 'textdomain' ), str_replace( '-', '_', MDJM_EVENT_POSTS ) . '_post_admin_metabox', MDJM_EVENT_POSTS, 'normal', 'low' );
					
					if( MDJM_PAYMENTS == true && array_key_exists( $post->post_status, $event_stati ) && current_user_can( 'administrator' ) )
						add_meta_box( 'mdjm-event-transactions', __( 'Transactions', 'textdomain' ), 
							str_replace( '-', '_', MDJM_EVENT_POSTS ) . '_post_transactions_metabox', MDJM_EVENT_POSTS, 'normal', 'low' );
					
					if( current_user_can( 'administrator' ) && array_key_exists( $post->post_status, $event_stati ) )
						add_meta_box( 'mdjm-event-email-history', __( 'Event History', 'textdomain' ), 
							str_replace( '-', '_', MDJM_EVENT_POSTS ) . '_post_history_metabox', MDJM_EVENT_POSTS, 'normal', 'low' );
					
					/* -- Side -- */
					add_meta_box( 'mdjm-event-options', __( 'Event Options', 'textdomain' ), str_replace( '-', '_', MDJM_EVENT_POSTS ) . '_post_options_metabox', MDJM_EVENT_POSTS, 'side', 'low' );
				}
			/* -- Transactions -- */
				if( $post->post_type == MDJM_TRANS_POSTS )	{
					remove_meta_box( 'submitdiv', MDJM_TRANS_POSTS, 'side' );
					remove_meta_box( 'transaction-typesdiv', MDJM_TRANS_POSTS, 'side' );
					/* -- Side -- */
					add_meta_box( 'mdjm-trans-save', __( 'Save Transaction', 'textdomain' ), str_replace( '-', '_', MDJM_TRANS_POSTS ) . '_post_save_metabox', MDJM_TRANS_POSTS, 'side', 'high' );
					/* -- Main -- */
					add_meta_box( 'mdjm-trans-details', __( 'Transaction Details', 'textdomain' ), str_replace( '-', '_', MDJM_TRANS_POSTS ) . '_post_details_metabox', MDJM_TRANS_POSTS, 'normal' );
				}
			/* -- Venues -- */
				if( $post->post_type == MDJM_VENUE_POSTS )	{
					/* -- Main Body -- */
					add_meta_box( 'mdjm-venue-details', __( 'Venue Details', 'textdomain' ), str_replace( '-', '_', MDJM_VENUE_POSTS ) . '_post_main_metabox', MDJM_VENUE_POSTS, 'normal', 'high' );
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
				
				/* -- If user is admin, or not a custom post type -- */
				if( current_user_can( 'administrator' ) || !in_array( $post->post_type, $mdjm_post_types ) )
					return;
				
				/* -- Event posts -- */
				if( $post->post_type == MDJM_EVENT_POSTS )	{	
					/* -- Add event permissions -- */
					if( is_dj() && !dj_can( 'dj_add_event' ) && $pagenow == 'post-new.php' )
						wp_die( 'Your administrator has restricted you from creating new Events. Please contact them for assistance.<br /><br />
							<a class="button-secondary" href="' . $_SERVER['HTTP_REFERER'] .'" title="' . __( 'Back' ) .'">' . __( 'Back' ) . '</a>' );
					/* -- Edit event permissions - -*/
					if( is_dj() && $pagenow == 'post.php' && !$mdjm->mdjm_events->is_my_event( $post->ID ) )	{
						wp_die( 'You can only view and edit your own events!<br /><br />
							<a class="button-secondary" href="' . $_SERVER['HTTP_REFERER'] .'" title="' . __( 'Back' ) .'">' . __( 'Back' ) . '</a>' );	
					}
				}
				if( $post->post_type == MDJM_VENUE_POSTS )	{	
					/* -- Add venue permissions -- */
					if( is_dj() && !dj_can( 'add_venue' ) )
						wp_die( 'You administrator has restricted you from creating new Venues. Please contact them for assistance.<br /><br />
							<a class="button-secondary" href="' . $_SERVER['HTTP_REFERER'] .'" title="' . __( 'Back' ) .'">' . __( 'Back' ) . '</a>' );
				}
				
			} // check_user_permission
		} // class
	}