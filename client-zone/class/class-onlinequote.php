<?php
/*
 * class-onlinequote.php
 * 08/04/2015
 * @since 1.2.3.3
 * The ClientZone_Quote class
 * Also acts as the controller for all front end activity
 */
	defined( 'ABSPATH' ) or die( 'Direct access to this page is disabled!!!' );
	
	/* -- Build the ClientZone class -- */
	if( !class_exists( 'ClientZone_Quote' ) )	{
		require_once( 'class-clientzone.php' );
		class ClientZone_Quote extends ClientZone	{
						
			/*
			 * __construct
			 * defines the params used within the class
			 *
			 * @params		arr		$args		
			 *				bool	'book_link'		Optional: true to display, false (default) not to display
			 *				str		'button_loc'	Optional: Where to display the button before|after|both. Default both
			 *				str		'button_align'	Optional: Button alignment. left|center|right Default centre
			 *				str		'button_text'	Optional: Text to display on button. Default "Book this Event"
			 *
			 */
			function __construct( $args='' )	{
				global $mdjm, $clientzone_loaded, $mdjm_settings;
				
				mdjm_page_visit( MDJM_APP . ' Online Quotes' );
				
				// Must be authenticated
				if( !is_user_logged_in() )
					parent::login();
					
				// Option must be enabled
				elseif( empty( $mdjm_settings['templates']['online_enquiry'] ) )
					parent::display_notice( 4, sprintf( __( 'This option is disabled for your event. Please %scontact us%s for assistance', 'mobile-dj-manager' ),
						'<a href="' . $mdjm->get_link( MDJM_CONTACT_PAGE, false ) . '">',
						'</a>' ) );
				
				// Check if the user is allowed here and that we have the data to provide the quote
				// If not, display an error	
				elseif( !$this->should_i_be_here() ) // do validation
					parent::display_notice( 4, sprintf( __( 'An error is stopping your access to this quotation. Please %scontact us%s for assistance', 'mobile-dj-manager' ),
						'<a href="' . $mdjm->get_link( MDJM_CONTACT_PAGE, false ) . '">',
						'</a>' ) );
				
				// Display the quote
				else	{
					if( isset( $_GET['message'], $_GET['class'] ) )
						parent::display_message( $_GET['message'], $_GET['class'] );
					
					// Possible button settings
					$button_loc = array( 'before', 'after', 'both' );
					$button_align = array( 'left', 'center', 'right' );
					
					// Define button settings
					$this->book_button = ( !empty( $args['book_button'] ) ? true : false );
					$this->button_loc = ( !empty( $args['button_loc'] ) && in_array( $button_loc ) 
						? $args['button_loc'] : 'both' );
						
					$this->button_align = ( !empty( $args['button_align'] ) && in_array( $button_loc ) 
						? $args['button_align'] : 'center' );
												
					$this->button_text = ( !empty( $args['button_text'] ) ? $args['button_text'] : 
						__( 'Book this Event', 'mobile-dj-manager' ) );
						
					$this->button_class = ( !empty( $args['button_class'] ) ? $args['button_class'] : false );
					
					$this->display_quote( $_GET['event_id'] );
				}
					
			} // __construct
			
			/*
			 * Validate the users presence at this page
			 * Only users that 
			 *
			 *
			 */
			function should_i_be_here()	{
				global $mdjm, $current_user;
				
				$passed = false;
				
				// Make sure we have an event passed
				if( !isset( $_GET['event_id'] ) || empty( $_GET['event_id'] ) )	{
					if( MDJM_DEBUG == true )
						$GLOBALS['mdjm_debug']->log_it( 'Missing event ID', true );
						
					$passed = false;
				}
				// Make sure the passed event belongs to the logged in user
				elseif( !$mdjm->mdjm_events->is_my_event( $_GET['event_id'] ) )	{
					if( MDJM_DEBUG == true )
						$GLOBALS['mdjm_debug']->log_it( 'Event does not belong to this user. Event: ' . $_GET['event_id'] . 
							' User: ' . $current_user->ID, true );
						
					$passed = false;	
				}
				// Make sure the passed event exists
				elseif( !$GLOBALS['mdjm_posts']->post_exists( $_GET['event_id'] ) )	{
					if( MDJM_DEBUG == true )
						$GLOBALS['mdjm_debug']->log_it( 'Event does not exist ' . $_GET['event_id'], true );
						
					$passed = false;	
				}
				// Make sure the quote has been generated
				elseif( get_post_status( $_GET['event_id'] ) != 'mdjm-enquiry' )	{
					if( MDJM_DEBUG == true )
						$GLOBALS['mdjm_debug']->log_it( 'Event is not in enquiry status ' . $_GET['event_id'], true );
						
					$passed = false;	
				}
				
				else
					$passed = true;
				
				return $passed;
			} // should_i_be_here
			
			/*
			 * echo the HTML to display the button to accept quote and book
			 *
			 *
			 * @param:	int		$event_id		Required: The ID of the event to book
			 */
			function display_book_button( $event_id )	{
				global $mdjm;
				
				if( empty( $this->book_button ) ) // Do not display unless setting dictates we do
					return;
					
				if ( get_post_status ( $event_id ) != 'mdjm-enquiry' )	{
					$GLOBALS['mdjm_debug']->log_it( 'We don\'t display the book button on quotes when the event status ' . 
						'is not Enquiry. This event is ' . get_post_status ( $event_id ), true );
					
					return;
				}
					
				// Button alignment
				echo '<p style="text-align: ' . $this->button_align . '">';
				
				// Button with text
				echo '<button type="reset" ' . 
					( !empty( $this->button_class ) ? 'class="' . $this->button_class . '" ' : '' ) . 
					'onclick="location.href=\'' . wp_nonce_url( $mdjm->get_link( MDJM_HOME, true ) . 
					'action=accept_enquiry&amp;event_id=' . $event_id, 'book_event', '__mdjm_verify' ) . '\'">' . 
					$this->button_text . '</button>';
					 
				echo '</p>' . "\r\n";
			} // book_button

			/*
			 * Display the event quote to the client so long as 
			 * a template is specified
			 *
			 *
			 */
			function display_quote( $event_id )	{
				global $mdjm, $mdjm_posts, $my_mdjm;
				
				$online_template = $mdjm->mdjm_events->retrieve_quote( $event_id );
				
				if( empty( $online_template ) || !$mdjm_posts->post_exists( $online_template ) )	{
					if( MDJM_DEBUG == true )
						$GLOBALS['mdjm_debug']->log_it( 'The online template associated with event with ID ' . $event_id . ' could not be found in ' . __METHOD__, true );
					
					parent::display_notice( 4, sprintf( __( 'Unable to process request. Please %scontact us%s for assistance', 'mobile-dj-manager' ),
						'<a href="' . $mdjm->get_link( MDJM_CONTACT_PAGE, false ) . '">',
						'</a>' ) );
						
					return;
				}
				
				if(!$this->update_event_quote( $event_id, $online_template ) )	{
					if( MDJM_DEBUG == true )
						$GLOBALS['mdjm_debug']->log_it( 'Unable to update quote post for event with ID ' . $event_id . ' in ' . __METHOD__, true );	
				}
				
				$template = get_post( $online_template );
				
				// Make sure we have the template
				if( !is_object( $template ) )	{
					if( MDJM_DEBUG == true )
						$GLOBALS['mdjm_debug']->log_it( 'The online template with ID ' . $online_template . ' could not be retrieved in ' . __METHOD__, true );
					
					parent::display_notice( 4, sprintf( __( 'Unable to process request. Please %scontact us%s for assistance', 'mobile-dj-manager' ),
						'<a href="' . $mdjm->get_link( MDJM_CONTACT_PAGE, false ) . '">',
						'</a>' ) );
					
					return;
				}
				
				$eventinfo = $GLOBALS['mdjm']->mdjm_events->event_detail( $event_id );
				
				// Make sure we have the event info
				if( empty( $eventinfo ) )	{
					if( MDJM_DEBUG == true )
						$GLOBALS['mdjm_debug']->log_it( 'Could not retrieve event information for event ' . $event_id . ' in ' . __METHOD__, true );
						
					parent::display_notice( 4, sprintf( __( 'Unable to process request. Please %scontact us%s for assistance', 'mobile-dj-manager' ),
						'<a href="' . $mdjm->get_link( MDJM_CONTACT_PAGE, false ) . '">',
						'</a>' ) );
					
					return;
				}
				
				// Display the online quotation
				/* -- Retrieve the quote content -- */
				$content = $template->post_content;
				$content = apply_filters( 'the_content', $content );
				$content = str_replace( ']]>', ']]&gt;', $content );
				
				/* -- Shortcode replacements -- */
				$content = $mdjm->filter_content(
									$my_mdjm['me']->ID,
									$event_id,
									$content
									);
				
				if( !empty( $content ) )	{
					if( $this->button_loc == 'before' || $this->button_loc == 'both' )
						$this->display_book_button( $event_id );
					
					print( $content );
					
					if( $this->button_loc == 'after' || $this->button_loc == 'both' )
						$this->display_book_button( $event_id );
					
				}
				
				// No content so return an error
				else
					parent::display_notice( 4, sprintf( __( 'An error has occured, please %scontact us%s for assistance', 'mobile-dj-manager' ),
						'<a href="' . $mdjm->get_link( MDJM_CONTACT_PAGE, false ) . '">',
						'</a>' ) );
					
			} // display_quote
			
			/*
			 * Update the quote status to viewed and register the time of viewing
			 *
			 *
			 *
			 */
			function update_event_quote( $event_id, $quote_id )	{
				global $mdjm_posts;
				
				// If the current user is not the client, do not log
				if( get_post_meta( $event_id, '_mdjm_event_client', true ) != get_current_user_id() )
					return;
					
				/* -- Prevent loops whilst updating -- */
				remove_action( 'save_post', array( $mdjm_posts, 'save_custom_post' ), 10, 2 );
				
				/* -- Initiate actions for status change -- */
				wp_transition_post_status( 'mdjm-rejected', $event->post_status, $event );
				
				if( MDJM_DEBUG == true )
					$GLOBALS['mdjm_debug']->log_it( 'Setting quote to viewed status for quote ' . $quote_id . ' event ' . $event_id, true );
				
				/* -- Update the post status -- */
				if( wp_update_post( array( 'ID' => $quote_id, 'post_status' => 'mdjm-quote-viewed' ) ) )	{
					if( MDJM_DEBUG == true )
						$GLOBALS['mdjm_debug']->log_it( 'Quote status updated successfully', false );
						
					$result = true;
				}
				
				/* -- Set post meta for read time and update the number of client views -- */
				if( $result == true )	{
					if( MDJM_DEBUG == true )
						$GLOBALS['mdjm_debug']->log_it( 'Updating quote view count', false );
					
					$view_count = get_post_meta( $quote_id, '_mdjm_quote_viewed_count', true );
					
					if( !empty( $view_count ) )
						$view_count++;
						
					else
						$view_count = 1;
						
					update_post_meta( $quote_id, '_mdjm_quote_viewed_count', $view_count );
					
					// Only update the view date if this is the first viewing
					if( $view_count == 1 )	{
						if( MDJM_DEBUG == true )
							$GLOBALS['mdjm_debug']->log_it( 'Updating quote viewed time', false );
							
						update_post_meta( $quote_id, '_mdjm_quote_viewed_date', current_time( 'mysql' ) );
					}
						
				}
					
				/* -- Re-add custom post save action -- */
				add_action( 'save_post', array( $mdjm_posts, 'save_custom_post' ), 10, 2 );
				
				return ( !empty( $result ) ? true : false );
					
			} // update_event_quote

		} // class
	} // if( !class_exists( 'ClientZone_Quote' ) )
	
	/* -- Insantiate the ClientZone_Quote class -- */
	$mdjm_quote = new ClientZone_Quote( $atts );