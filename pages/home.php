<?php
/*
* home.php
* 04/10/2014
* @since 0.8
* Displays the Client Zone frontend home page & functions
* Last Updated: 25/11/2014
* Now supports multuiple events
*/
	defined('ABSPATH') or die("Direct access to this page is disabled!!!");
	
/*
* f_mdjm_client_home
* 25/11/2014
* @since 0.9.4
* Displays the Client Zone frontend event list
*/
	function f_mdjm_client_home()	{
		global $wpdb, $current_user, $mdjm_options, $mdjm_client_text;
		
		include( WPMDJM_PLUGIN_DIR . '/includes/config.inc.php' );
		
		$mdjm_pp_options = get_option( 'mdjm_pp_options' );
		
		get_currentuserinfo();
		$eventinfo = f_mdjm_get_client_events( $db_tbl, $current_user );
?>
		<p>
        <?php
        if( isset( $mdjm_client_text['custom_client_text'] ) && $mdjm_client_text['custom_client_text'] == 'Y' )	{
			f_mdjm_client_text( 'home_welcome' );
		}
		else	{
	        echo 'Hello ' . $current_user->first_name . ' and welcome to the <a href="' . site_url() . '">' . WPMDJM_CO_NAME . '</a> ' . WPMDJM_APP_NAME . '.';
		}
		?>
        </p>

<?php	if( !$eventinfo )	{
			?>
			<p>
			<?php
			if( isset( $mdjm_client_text['custom_client_text'] ) && $mdjm_client_text['custom_client_text'] == 'Y' )	{
				f_mdjm_client_text( 'home_noevents' );
			}
			else	{
				echo 'You currently have no upcoming events. Please <a title="Contact ' . get_bloginfo( 'name' ) . '" href="' . get_permalink( WPMDJM_CONTACT_PAGE ) . '">contact me</a> now to start planning your next disco.';
			}
			?>
			</p>
		<?php
		}
		else	{
			/* Multiple events for client */
			if( count( $eventinfo ) > 1 )	{
				?>
                <p>Below are the details of all your events. Click the date of an event listed below to view further details or select an action from the drop down list.</p>
                <table width="100%" border="0" cellspacing="0" cellpadding="0">
                <thead>
                <tr>
                <th align="left" width="15%">Date</th>
                <th align="left" width="20%">Type</th>
                <th align="left" width="10%">Cost</th>
                <th align="left" width="15%">Status</th>
                <th align="left" width="40%">Actions</th>
                </tr>
                </thead>
                <?php
				if ( get_option('permalink_structure') )	{
					$sep = '?';
				}
				else	{
					$sep = '&amp;';
				}
				/* Loop through events and display details */
				foreach( $eventinfo as $event )	{
					?>
                    <form name="client_event_actions_<?php echo $event->event_id; ?>" action="" method="post">
                    <input type="hidden" name="action" value="view_event" />
                    <input type="hidden" name="event_id" value="<?php echo $event->event_id; ?>" />
                    <tr>
                    <td height="30" align="left"><a href="<?php echo get_permalink( WPMDJM_CLIENT_HOME_PAGE ); ?><?php echo $sep; ?>action=view_event&event_id=<?php echo $event->event_id; ?>"><?php echo date( $mdjm_options['short_date_format'], strtotime( $event->event_date ) ); ?></a></td>
                    <td align="left"><?php echo $event->event_type; ?></td>
                    <td align="left"><?php echo f_mdjm_currency() . $event->cost; ?></td>
                    <td align="left"><?php echo $event->contract_status; ?></td>
                    <td align="left">
                    <select name="event_action">
                    <option value="0">--- Select Action ---</option>
                    <option value="view_event">View Details</option>
                    <?php
					if( $event->contract_status != 'Failed Enquiry' 
						&& $event->contract_status != 'Completed' 
						&& $event->contract_status != 'Cancelled' 
						&& $event->contract_status != 'Approved' )	{
					}
					if( $event->contract_status == 'Enquiry' )	{
						?>
						<option value="accept_enquiry">Book Event</option>
                        <?php
					}
					if( $event->contract_status == 'Pending' )	{
						?>
						<option value="sign_contract">Sign Contract</option>
                        <?php
					}
					if( $event->contract_status == 'Approved' )	{
						if( isset( $mdjm_pp_options['pp_enable'] ) && $mdjm_pp_options['pp_enable'] == 'Y' )	{
							if( $event->deposit_status != 'Paid' || $event->balance_status != 'Paid' )	{
								?>
								<option value="payment">Make a Payment</option>
								<?php	
							}
						}
						?>
						<option value="view_contract">View Contract</option>
                        <option value="edit_playlist">Edit Playlist</option>
                        <?php
					}
					if( isset( $mdjm_options['client_can_addon'] ) 
						&& $mdjm_options['client_can_addon'] == 'Y'
						&& $event->contract_status != 'Completed' 
						&& $event->contract_status != 'Cancelled' 
						&& $event->contract_status != 'Approved' )	{
						?>
                        <option value="view_addons">Select Add-ons</option>
                        <?php	
					}
					if( $event->contract_status != 'Cancelled'
						&& $event->contract_status != 'Completed'
						&& $event->contract_status != 'Approved' )	{
						?>
	                    <option value="cancel_event">Cancel Event</option>
                    	<?php
					}
					?>
                    </select>
                    &nbsp;
                    &nbsp;
                    <input type="submit" name="submit" value="Go" />
                    </td>
                    </tr>
                    </form>
                    <?php	
				} // foreach( $eventinfo as $event )
				?>
                </table> 
                <?php	
			} // if( count( $eventinfo > 1 ) )
			else	{ // Single event so display the details
				f_mdjm_view_event( $eventinfo[0]->event_id );
			}
		} // else if( !$eventinfo )
	} // f_mdjm_client_home

/*
* f_mdjm_view_event
* 25/11/2014
* @since 0.9.4
* Display single event info (no edit)
*/
	function f_mdjm_view_event( $event_id )	{
		global $wpdb, $current_user, $mdjm_options, $mdjm_client_text;
		
		include( WPMDJM_PLUGIN_DIR . '/includes/config.inc.php' );
		
		if ( get_option('permalink_structure') )	{
			$sep = '?';
		}
		else	{
			$sep = '&amp;';
		}
		
		$mdjm_pp_options = get_option( 'mdjm_pp_options' );
		
		$eventinfo = f_mdjm_get_event_by_id( $db_tbl, $event_id );
		
		$djinfo = f_mdjm_get_djinfo( $db_tbl, $eventinfo );
		
		$clientinfo = get_user_by( 'id', $eventinfo->user_id );
		
		if( $eventinfo )	{ 
			$days_to_go = time() - strtotime( $eventinfo->event_date ); // Days until the event
			$fdate = date( "l, jS F Y", strtotime( $eventinfo->event_date ) );
			$duration = strtotime( $eventinfo->event_finish ) - strtotime( $eventinfo->event_start ); // Duration of event
			
			if( $eventinfo->contract_status == 'Failed Enquiry'
				|| $eventinfo->contract_status == 'Cancelled'
				|| $eventinfo->contract_status == 'Completed' )	{
				
				if( isset( $mdjm_client_text['custom_client_text'] ) && $mdjm_client_text['custom_client_text'] == 'Y' )	{
					f_mdjm_client_text( 'home_notactive' );
				}
				else	{
					echo '<p>The selected event is no longer active. <a href="' . get_permalink( WPMDJM_CONTACT_PAGE ) . '" title="Begin planning your next event with us">Contact us now</a> begin planning your next event.</p>';
				}
			}
			else	{
				?>
                <p>Below are details of your upcoming event on <?php echo $fdate; ?>.</p>
                <p>If any of the event details are incorrect, please <a href="mailto:<?php echo $mdjm_options['system_email']; ?>?subject=Event ID <?php echo $eventinfo->event_id; ?> || Incorrect Event Details">contact me now</a>.</p>
				<?php
			}
			/* Make sure client profile is complete */
			if( isset( $mdjm_client_text['warn_incomplete_profile'] ) && $mdjm_client_text['warn_incomplete_profile'] == 'Y' && !f_mdjm_profile_complete( $clientinfo->ID ) )	{
				?>
                <p style="font-weight:bold">IMPORTANT: Your <a href="<?php echo get_permalink( WPMDJM_CLIENT_PROFILE_PAGE ); ?>">profile</a> appears to be incomplete. Please <a href="<?php echo get_permalink( WPMDJM_CLIENT_PROFILE_PAGE ); ?>">click here</a> to update it now. Incorrect <a href="<?php echo get_permalink( WPMDJM_CLIENT_PROFILE_PAGE ); ?>">profile</a> information can cause problems with your booking.</p>
                <?php	
			}
			
			if( $eventinfo->contract_status != 'Failed Enquiry'
				&& $eventinfo->contract_status != 'Cancelled'
				&& $eventinfo->contract_status != 'Completed' )	{
				echo '<p><strong>Actions</strong>: ';		
			
				if( $eventinfo->contract_status == 'Enquiry' )	{
					if ( get_option('permalink_structure') )	{
						echo '<a href="' . get_permalink( WPMDJM_CLIENT_HOME_PAGE ) . '?action=accept_enquiry&event_id=' . $eventinfo->event_id . '" title="Book This Event">Book This Event</a>';
					}
					else	{
						echo '<a href="' . get_permalink( WPMDJM_CLIENT_HOME_PAGE ) . '&action=accept_enquiry&event_id=' . $eventinfo->event_id . '" title="Book This Event">Book This Event</a>';	
					}
				}
				if( $eventinfo->contract_status == 'Pending' )	{
					if ( get_option('permalink_structure') )	{
						echo '<a href="' . get_permalink( WPMDJM_CLIENT_CONTRACT_PAGE ) . '?event_id=' . $eventinfo->event_id . '" title="Approve Contract">Review &amp; Approve Contract</a>';
					}
					else	{
						echo '<a href="' . get_permalink( WPMDJM_CLIENT_CONTRACT_PAGE ) . '&event_id=' . $eventinfo->event_id . '" title="Approve Contract">Review &amp; Approve Contract</a>';
					}
				}
				if( $eventinfo->contract_status == 'Approved' )	{
					
					echo '<a href="' . get_permalink( WPMDJM_CLIENT_CONTRACT_PAGE ) . $sep . 'event_id=' . $eventinfo->event_id . '" title="View Event Contract">View Contract</a>';
				}
				if( isset( $mdjm_pp_options['pp_enable'] ) && $mdjm_pp_options['pp_enable'] == 'Y' )	{
					if( $eventinfo->deposit_status != 'Paid' || $eventinfo->balance_status != 'Paid' )	{
						echo ' | <a href="' . get_permalink( WPMDJM_CLIENT_PAYMENT_PAGE ) . $sep . 'event_id=' . $eventinfo->event_id . '" title="Make Payment">Make a Payment</a>';	
					}
				}
				if( $eventinfo->contract_status == 'Approved' || $eventinfo->contract_status == 'Pending' )	{
					if ( get_option('permalink_structure') )	{
						echo ' | <a href="' . get_permalink( WPMDJM_CLIENT_PLAYLIST_PAGE ) . $sep . 'mdjmeventid=' . $eventinfo->event_id . '" title="Manage Playlist">Manage Playlist</a>';
					}
					else	{
						echo ' | <a href="' . get_permalink( WPMDJM_CLIENT_PLAYLIST_PAGE ) . $sep . 'mdjmeventid=' . $eventinfo->event_id . '" title="Manage Playlist">Manage Playlist</a>';
					}	
				}
				
				echo ' | <a href="' . get_permalink( WPMDJM_CLIENT_PROFILE_PAGE ) . '" title="Edit your details">Edit Your Profile</a>';	
				
				echo ' | <a href="' . get_permalink( WPMDJM_CONTACT_PAGE ) . '" title="Begin planning your next event with us">Book Another Event</a>';
				
				echo '</p>';		
			}
			
			?>
            <hr />
            <table width="100%" border="0" cellspacing="0" cellpadding="0">
               <tr>
                <?php
				$event_status = $eventinfo->contract_status;
				if( $event_status == 'Approved' )	{
					$event_status = 'Confirmed';
					$action_item = date( $mdjm_options['short_date_format'], strtotime( $eventinfo->contract_approved_date ) );
				}
				if( $event_status == 'Enquiry' )	{
					if ( get_option('permalink_structure') )	{
						$action_item = '<a href="' . get_permalink( WPMDJM_CLIENT_HOME_PAGE ) . '?action=accept_enquiry&event_id=' . $eventinfo->event_id . '" title="Book This Event">Book this Event</a>';
					}
					else	{
						$action_item = '<a href="' . get_permalink( WPMDJM_CLIENT_HOME_PAGE ) . '&action=accept_enquiry&event_id=' . $eventinfo->event_id . '" title="Book This Event">Book this Event</a>';	
					}
				}
				if( $event_status == 'Pending' )	{
					if ( get_option('permalink_structure') )	{
						$action_item = '<a href="' . get_permalink( WPMDJM_CLIENT_CONTRACT_PAGE ) . '?event_id=' . $eventinfo->event_id . '" title="Review &amp; Sign Event Contract">Review &amp; Sign Contract</a>';
					}
					else	{
						$action_item = '<a href="' . get_permalink( WPMDJM_CLIENT_CONTRACT_PAGE ) . '&event_id=' . $eventinfo->event_id . '" title="Review &amp; Sign Event Contract">Review &amp; Sign Contract</a>';
					}
				}
				?>
                <td width="15%" style="font-weight:bold">Booking Status:</td>
                <td width="35%"><?php echo $event_status;  ?> <?php if( isset( $action_item ) ) echo '(' . $action_item . ')'; ?></td>
                <td width="15%" style="font-weight:bold">Your DJ:</td>
                <td width="35%"><?php echo $djinfo->display_name; ?></td>
               </tr>
               <tr>
                <td width="15%" style="font-weight:bold">Date of Event:</td>
                <td width="35%"><?php echo date( $mdjm_options['short_date_format'], strtotime( $eventinfo->event_date ) )." (".substr( floor( $days_to_go / ( 60*60*24 ) ) ,1 )." days to go!)"; ?></td>
                <td width="15%" style="font-weight:bold">Type of Event:</td>
                <td width="35%"><?php echo $eventinfo->event_type; ?></td>
               </tr>
               <tr>
                <td style="font-weight:bold">Start Time:</td>
                <td><?php echo date( $mdjm_options['time_format'], strtotime( $eventinfo->event_start ) ); ?></td>
                <td style="font-weight:bold">Finish Time:</td>
                <td><?php echo date( $mdjm_options['time_format'], strtotime( $eventinfo->event_finish ) )." (".date( "g", $duration )." hours ".date( "i", $duration )." minutes)"; ?></td>
              </tr>
              <?php 
			  if( isset( $eventinfo->event_description ) && !empty( $eventinfo->event_description ) )	{ 
					?>
					<tr>
					<td style="font-weight:bold">Event Information:</td>
					<td colspan="3"><?php echo $eventinfo->event_description; ?></td>
					</tr>
					<?php 
				} 
				?>
              <tr>
               <td colspan="4">&nbsp;</td>
              </tr>
              <tr>
                <td style="font-weight:bold">Contact Name:</td>
                <td><?php echo $clientinfo->first_name." ".$clientinfo->last_name; ?></td>
                <td style="font-weight:bold">Contact Phone:</td>
                <td><?php echo $clientinfo->phone1; if( isset( $clientinfo->phone2 ) && !empty( $clientinfo->phone2 ) ) { echo " or ".$clientinfo->phone2; } ?></td>
              </tr>
              <tr valign="top">
                <td style="font-weight:bold">Contact Email:</td>
                <td><?php echo $clientinfo->user_email; ?></td>
                <td style="font-weight:bold">Contact Address:</td>
                <td><?php if( isset( $clientinfo->address1 ) && !empty( $clientinfo->address1 ) ) echo $clientinfo->address1; ?>
                <?php if( isset( $clientinfo->address2 ) && !empty( $clientinfo->address2 ) ) echo ', ' . $clientinfo->address2; ?>
                <?php if( isset( $clientinfo->town ) && !empty( $clientinfo->town ) ) echo '<br />' . $clientinfo->town; ?>
                <?php if( isset( $clientinfo->county ) && !empty( $clientinfo->county ) ) echo ', ' . $clientinfo->county; ?>
                <?php if( isset( $clientinfo->postcode ) && !empty( $clientinfo->postcode ) ) echo '<br />' . $clientinfo->postcode; ?> 
                </td>
               </tr>
               <tr>
                <td colspan="4">&nbsp;</td>
              </tr>
              <tr valign="top">
                <td width="15%" style="font-weight:bold">Venue:</td>
                <td width="35%"><?php echo $eventinfo->venue; ?></td>
                <td width="15%" style="font-weight:bold">Venue Address:</td>
                <td width="35%"><?php echo $eventinfo->venue_addr1; ?>
                <?php if( isset( $eventinfo->venue_addr2 ) && !empty( $eventinfo->venue_addr2 ) ) { echo ', ' . $eventinfo->venue_addr2; } ?>
                <?php if( isset( $eventinfo->venue_city ) && !empty( $eventinfo->venue_city ) ) echo '<br />' . $eventinfo->venue_city; ?>
                <?php if( isset( $eventinfo->venue_state ) && !empty( $eventinfo->venue_state ) ) echo ', ' . $eventinfo->venue_state; ?>
                <?php if( isset( $eventinfo->venue_zip ) && !empty( $eventinfo->venue_zip ) ) echo '<br />' . $eventinfo->venue_zip; ?></td>
              </tr>
              <tr>
              <td width="15%" style="font-weight:bold">Venue Contact:</td>
              <td width="35%"><?php if( isset( $eventinfo->venue_contact ) ) echo $eventinfo->venue_contact; ?></td>
              <td width="15%" style="font-weight:bold">Venue Phone:</td>
              <td width="35%"><?php if( isset( $eventinfo->venue_phone ) ) echo $eventinfo->venue_phone; ?></td>
              </tr>
              <tr>
              <td width="15%" style="font-weight:bold">Venue Email:</td>
              <td width="35%"><?php if( isset( $eventinfo->venue_email ) ) echo $eventinfo->venue_email; ?></td>
              <td width="15%" style="font-weight:bold">&nbsp;</td>
              <td width="35%">&nbsp;</td>
              </tr>
            </table>
            <hr />
             <table width="100%" border="0" cellspacing="0" cellpadding="0">
              <tr>
                <td width="10%" style="font-weight:bold">Total Price:</td>
                <td width="23%"><?php echo f_mdjm_currency() . $eventinfo->cost; ?></td>
                <td width="11%" style="font-weight:bold"><?php echo $mdjm_client_text['deposit_label']; ?>:</td>
                <td width="22%"><?php echo f_mdjm_currency() . $eventinfo->deposit . ' (' . $eventinfo->deposit_status . ')'; ?></td>
                <td width="15%" style="font-weight:bold">Remaining:</td>
                <td>
				<?php
                if( $eventinfo->deposit_status == "Paid" )	{
					echo f_mdjm_currency() . number_format( $eventinfo->cost - $eventinfo->deposit, 2 );
				}
				else	{
					echo f_mdjm_currency() . number_format( $eventinfo->cost, 2 );	
				}
				?>
				</td>
              </tr>
            </table>
			<?php
		}
		else	{ // There is an error
			echo '<p>We\'re sorry but an error has occured and your event details cannot be displayed at this time.</p>' . "\n";
			echo '<p>Please <a href="' . get_permalink( WPMDJM_CONTACT_PAGE ) . '">contact us</a> for assistance.</p>' . "\n";	
		}
	} // f_mdjm_view_event
	
/* ------------------------------------------------------------------------- */

/* Determine the correct page set to display */	
	if( is_user_logged_in() )	{
		
		/* Client wants to book */
		if( isset( $_GET['action'] ) && $_GET['action'] == 'accept_enquiry' ||
			isset( $_POST['event_action'] ) && $_POST['event_action'] == 'accept_enquiry' )	{
			
			if( $_GET['action'] == 'accept_enquiry' ) $event_id = $_GET['event_id'];
			if( $_POST['event_action'] == 'accept_enquiry' ) $event_id = $_POST['event_id'];
			
			f_mdjm_change_event_status( 'Pending', $event_id );
		}
		
		/* Client wants to cancel */
		if( isset( $_GET['action'] ) && $_GET['action'] == 'cancel_event' ||
			isset( $_POST['event_action'] ) && $_POST['event_action'] == 'cancel_event' )	{
			
			if( $_GET['action'] == 'cancel_event' ) $event_id = $_GET['event_id'];
			if( $_POST['event_action'] == 'cancel_event' ) $event_id = $_POST['event_id'];
			
			f_mdjm_change_event_status( 'Cancelled', $event_id );
		}
		
		/* Go to playlist */
		if( isset( $_POST['event_action'] ) && $_POST['event_action'] == 'edit_playlist' )	{
			if ( get_option('permalink_structure') )	{
				?>
				<script type="text/javascript">
				window.location = '<?php echo get_permalink( WPMDJM_CLIENT_PLAYLIST_PAGE ) . '?mdjmeventid=' . $_POST['event_id']; ?>';
				</script>
                <?php
			}
			else	{
				?>
				<script type="text/javascript">
				window.location = '<?php echo get_permalink( WPMDJM_CLIENT_PLAYLIST_PAGE ) . '&mdjmeventid=' . $_POST['event_id']; ?>';
				</script>
                <?php
			}
			exit;	
		}
		
		/* Client wants to view or sign contract */
		if( isset( $_POST['event_action'] ) )	{
			if( $_POST['event_action'] == 'view_contract' || $_POST['event_action'] == 'sign_contract' )	{
				if ( get_option('permalink_structure') )	{
					?>
					<script type="text/javascript">
                    window.location = '<?php echo get_permalink( WPMDJM_CLIENT_CONTRACT_PAGE ) . '?event_id=' . $_POST['event_id']; ?>';
                    </script>
                    <?php
				}
				else	{
					?>
					<script type="text/javascript">
                    window.location = '<?php echo get_permalink( WPMDJM_CLIENT_CONTRACT_PAGE ) . '&event_id=' . $_POST['event_id']; ?>';
                    </script>
                    <?php
				}				
				exit;
			}
			if( $_POST['event_action'] == 'payment' )	{
				if ( get_option('permalink_structure') )	{
					?>
					<script type="text/javascript">
                    window.location = '<?php echo get_permalink( WPMDJM_CLIENT_PAYMENT_PAGE ) . '?event_id=' . $_POST['event_id']; ?>';
                    </script>
                    <?php
				}
				else	{
					?>
					<script type="text/javascript">
                    window.location = '<?php echo get_permalink( WPMDJM_CLIENT_PAYMENT_PAGE ) . '&event_id=' . $_POST['event_id']; ?>';
                    </script>
                    <?php
				}				
				exit;
			}
		}
		
		
		/* Display page */
		if( !isset( $_GET['action'] ) && !isset( $_POST ['action'] ) )	{
			f_mdjm_client_home();
		}
		elseif( isset( $_GET['action'] ) || isset( $_POST['action'] ) )	{
			if( $_GET['action'] == 'view_event' || $_GET['action'] == 'accept_enquiry' )	{
				f_mdjm_view_event( $_GET['event_id'] );
			}
			elseif( $_POST['action'] == 'view_event' )	{
				f_mdjm_view_event( $_POST['event_id'] );
			}
		}
	}
	else	{ /* Display login form only if not logged in */
		f_mdjm_show_user_login_form();
	}
	
	/* Print the credit if set */
	add_action( 'wp_footer', 'f_wpmdjm_print_credit' );
?>
