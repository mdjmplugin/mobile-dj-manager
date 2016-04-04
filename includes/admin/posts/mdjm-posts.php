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
															
			if( is_admin() )	{
				add_filter( 'posts_clauses', array( &$this, 'sort_post_by_column' ), 1, 2 );
				
				add_filter( 'post_row_actions', array( &$this, 'define_custom_post_row_actions' ), 10, 2 ); // Row actions
				add_filter( 'post_updated_messages', array( &$this, 'custom_post_status_messages' ) ); // Status messages
			}

		} // __construct()
		
		/**
		 * Call include files for custom post types
		 *
		 *
		 *
		 */
		function includes()	{
			include_once( 'mdjm-communications-posts.php' );
			include_once( 'mdjm-contract-posts.php' );
			include_once( 'mdjm-email-template-posts.php' );
			include_once( 'mdjm-quote-posts.php' );
			include_once( 'mdjm-transaction-posts.php' );
			include_once( 'mdjm-venue-posts.php' );
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
							
		} // save_custom_post		
																	
/**
* -- POST COLUMN SORTING
*/		
		/**
		 * The queries used to sort posts by selected column
		 * 
		 * 
		 * @params: $query
		 * @return:
		 */
		public function sort_post_by_column( $pieces, $query )	{
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
					 * Quote sorting
					 */	
					// Order by quote view date
					case 'quote_view_date':
						$pieces[ 'join' ] .= " LEFT JOIN $wpdb->postmeta mdjm_q ON mdjm_q.post_id = {$wpdb->posts}.ID AND mdjm_q.meta_key = '_mdjm_quote_viewed_date'";
						
						$pieces[ 'orderby' ] = "STR_TO_DATE( mdjm_q.meta_value,'%Y-%m-%d' ) $order, " . $pieces[ 'orderby' ];
					
					break;
					
					// Order by quote value	
					case 'quote_value':
						$pieces[ 'join' ] .= " LEFT JOIN $wpdb->postmeta mdjm_cost ON mdjm_cost.post_id = {$wpdb->posts}.post_parent AND mdjm_cost.meta_key = '_mdjm_event_cost'";
						
						$pieces[ 'orderby' ] = "cast(mdjm_cost.meta_value as unsigned) $order, " . $pieces[ 'orderby' ];
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
						
						$pieces[ 'orderby' ] = "cast(mdjm_cost.meta_value as unsigned) $order, " . $pieces[ 'orderby' ];
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
		} // sort_post_by_column
		
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
			
			if( isset( $custom_messages[$post_type] ) )
				$messages[$post_type] = array_replace( $messages[$post_type], $custom_messages[$post_type] );
	
			return $messages;
		} // custom_post_status_messages
												
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
			if( $post->post_type == 'mdjm-event' || !in_array( $post->post_type, $mdjm_post_types ) )
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
			require_once( 'mdjm-metaboxes.php' );
			
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
				$event_stati = mdjm_all_event_status();
				/* -- Main Body -- */
				remove_meta_box( 'submitdiv', MDJM_EVENT_POSTS, 'side' );
				remove_meta_box( 'event-typesdiv', MDJM_EVENT_POSTS, 'side' );
				
				add_meta_box(
					'mdjm-event-details',
					__( 'Event Details', 'mobile-dj-manager' ),
					str_replace( '-', '_', MDJM_EVENT_POSTS ) . '_post_event_metabox',
					MDJM_EVENT_POSTS,
					'normal',
					'high'
				);
				
				add_meta_box(
					'mdjm-event-employees',
					__( 'Event Employees', 'mobile-dj-manager' ),
					'mdjm_event_employee_mb',
					MDJM_EVENT_POSTS,
					'normal',
					''
				);
				
				add_meta_box(
					'mdjm-event-venue',
					__( 'Venue Details', 'mobile-dj-manager' ),
					str_replace( '-', '_', MDJM_EVENT_POSTS ) . '_post_venue_metabox',
					MDJM_EVENT_POSTS,
					'normal',
					''
				);
				
				add_meta_box(
					'mdjm-event-admin',
					__( 'Administration', 'mobile-dj-manager' ),
					str_replace( '-', '_', MDJM_EVENT_POSTS ) . '_post_admin_metabox',
					MDJM_EVENT_POSTS,
					'normal',
					'low'
				);
				
				if( MDJM_PAYMENTS == true && array_key_exists( $post->post_status, $event_stati ) && mdjm_employee_can( 'edit_txns' ) )	{
					add_meta_box(
						'mdjm-event-transactions',
						__( 'Transactions', 'mobile-dj-manager' ), 
						str_replace( '-', '_', MDJM_EVENT_POSTS ) . '_post_transactions_metabox',
						MDJM_EVENT_POSTS,
						'normal',
						'low'
					);	
				}
				
				if( current_user_can( 'manage_mdjm' ) && array_key_exists( $post->post_status, $event_stati ) )	{
					add_meta_box(
						'mdjm-event-email-history',
						__( 'Event History', 'mobile-dj-manager' ), 
						str_replace( '-', '_', MDJM_EVENT_POSTS ) . '_post_history_metabox',
						MDJM_EVENT_POSTS,
						'normal',
						'low'
					);
				}
				
				/* -- Side -- */
				add_meta_box(
					'mdjm-event-options',
					__( 'Event Options', 'mobile-dj-manager' ),
					str_replace( '-', '_', MDJM_EVENT_POSTS ) . '_post_options_metabox',
					MDJM_EVENT_POSTS,
					'side',
					'low'
				);
				
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
	} // class
endif;