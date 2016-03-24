<?php

/**
 * Contains all metabox functions for the mdjm-event post type
 *
 * @package		MDJM
 * @subpackage	Events
 * @since		1.3
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) )
	exit;

/**
 * Remove unwanted metaboxes to for the mdjm-event post type.
 * Apply the `mdjm_event_remove_metaboxes` filter to allow for filtering of metaboxes to be removed.
 *
 * @since	1.3
 * @param
 * @return
 */
function mdjm_remove_event_meta_boxes()	{
	$metaboxes = apply_filters( 'mdjm_event_remove_metaboxes',
		array(
			array( 'submitdiv', 'mdjm-event', 'side' ),
			array( 'event-typesdiv', 'mdjm-event', 'side' ),
		)
	);
	
	foreach( $metaboxes as $metabox )	{
		remove_meta_box( $metabox[0], $metabox[1], $metabox[2] );
	}
} // mdjm_remove_event_meta_boxes
add_action( 'admin_head', 'mdjm_remove_event_meta_boxes' );

/**
 * Define and add the metaboxes for the mdjm-event post type.
 * Apply the `mdjm_event_add_metaboxes` filter to allow for filtering of metaboxes and settings.
 * Uses function_exists to verify the callback function exists.
 *
 * @since	1.3
 * @param
 * @return
 */
function mdjm_add_event_meta_boxes( $post )	{
	$metaboxes = apply_filters( 'mdjm_event_add_metaboxes',
		array(
			array(
				'id'		  => 'mdjm-event-client',
				'title'	   => __( 'Client Details', 'mobile-dj-manager' ),
				'callback'	=> 'mdjm_event_metabox_client_details',
				'context'	 => 'normal',
				'priority'	=> 'high',
				'args'		=> array(),
				'dependancy'  => '',
				'permission'  => ''
			),
			array(
				'id'		  => 'mdjm-event-details',
				'title'	   => __( 'Event Details', 'mobile-dj-manager' ),
				'callback'	=> 'mdjm_event_metabox_event_details',
				'context'	 => 'normal',
				'priority'	=> 'high',
				'args'		=> array(),
				'dependancy'  => '',
				'permission'  => ''
			),
			array(
				'id'		  => 'mdjm-event-employees',
				'title'	   => __( 'Event Employees', 'mobile-dj-manager' ),
				'callback'	=> 'mdjm_event_metabox_event_employees',
				'context'	 => 'normal',
				'priority'	=> '',
				'args'		=> array(),
				'dependancy'  => '',
				'permission'  => ''
			),
			array(
				'id'		  => 'mdjm-event-venue',
				'title'	   => __( 'Venue Details', 'mobile-dj-manager' ),
				'callback'	=> 'mdjm_event_metabox_venue_details',
				'context'	 => 'normal',
				'priority'	=> '',
				'args'		=> array(),
				'dependancy'  => '',
				'permission'  => ''
			),
			array(
				'id'		  => 'mdjm-event-admin',
				'title'	   => __( 'Administration', 'mobile-dj-manager' ),
				'callback'	=> 'mdjm_event_metabox_administration',
				'context'	 => 'normal',
				'priority'	=> 'low',
				'args'		=> array(),
				'dependancy'  => '',
				'permission'  => ''
			),
			array(
				'id'		  => 'mdjm-event-transactions',
				'title'	   => __( 'Transactions', 'mobile-dj-manager' ),
				'callback'	=> 'mdjm_event_metabox_transactions',
				'context'	 => 'normal',
				'priority'	=> 'low',
				'args'		=> array(),
				'dependancy'  => MDJM_PAYMENTS,
				'permission'  => ''
			),
			array(
				'id'		  => 'mdjm-event-history',
				'title'	   => __( 'Event History', 'mobile-dj-manager' ),
				'callback'	=> 'mdjm_event_metabox_event_history',
				'context'	 => 'normal',
				'priority'	=> 'low',
				'args'		=> array(),
				'dependancy'  => '',
				'permission'  => ''
			),
			array(
				'id'		  => 'mdjm-event-options',
				'title'	   => __( 'Event Options', 'mobile-dj-manager' ),
				'callback'	=> 'mdjm_event_metabox_event_options',
				'context'	 => 'side',
				'priority'	=> 'low',
				'args'		=> array(),
				'dependancy'  => '',
				'permission'  => ''
			)
		)
	);
	// Runs before metabox output
	do_action( 'mdjm_event_before_metaboxes' );
	
	// Begin metaboxes
	foreach( $metaboxes as $metabox )	{
		// Dependancy check
		if( isset( $metabox['dependancy'] ) && $metabox['dependancy'] === false )	{
			continue;
		}
		
		// Permission check
		if( ! empty( $metabox['permission'] ) && !MDJM()->permissions->employee_can( $metabox['permission'] ) )	{
			continue;
		}
		
		// Callback check
		if( ! is_callable( $metabox['callback'] ) )	{
			continue;
		}
		add_meta_box(
			$metabox['id'],
			$metabox['title'],
			$metabox['callback'],
			'mdjm-event',
			$metabox['context'].
			$metabox['priority'],
			$metabox['args']
		);
	}
	
	// Runs after metabox output
	do_action( 'mdjm_event_after_metaboxes' );
} // mdjm_add_event_meta_boxes
add_action( 'add_meta_boxes_mdjm-event', 'mdjm_add_event_meta_boxes' );

/**
 * Output for the Client Details meta box.
 *
 * @since	1.3
 * @param	obj		$post		Required: The post object (WP_Post).
 * @return
 */
function mdjm_event_metabox_client_details( $post )	{
	wp_nonce_field( basename( __FILE__ ), MDJM_EVENT_POSTS . '_nonce' );
		
	$existing_event = ( $post->post_status == 'unattended' || $post->post_status == 'enquiry' || $post->post_status == 'auto-draft' ? false : true );
	
	$clients = get_users( array(
							'role' => 'client',
							'orderby' => 'display_name',
							'order' => 'ASC',
							) );
	$client_id = get_post_meta( $post->ID, '_mdjm_event_client', true );
	
	?>
	<div class="mdjm-post-row-single">
		<div class="mdjm-post-1column">
			<input type="hidden" name="mdjm_update_custom_post" id="mdjm_update_custom_post" value="mdjm_update" />
			<label for="client_name" class="mdjm-label"><?php _e(' Select Client:' ); ?></label><br />
			<select name="client_name" id="client_name" class="required" onchange="displayClientFields();">
			<?php
				/* -- Build the drop down box -- */
				echo ( !empty( $clients ) && $existing_event == false ? '<option value="">--- Select Client ---</option>' . "\r\n" : '' );
				echo ( $existing_event == false && ( MDJM()->permissions->employee_can( 'list_all_clients' ) ) ? '<option value="add_new">--- Add New Client ---</option>' . "\r\n" : '' );
				foreach( $clients as $client )	{
					echo '<option value="' . $client->ID . '"';
					selected( $client->ID, $client_id );
					echo '>' . $client->display_name . '</option>' . "\r\n";
				}
			?>
			</select>
			<?php
			if( MDJM()->permissions->employee_can( 'view_clients_list' ) && $post->post_status != 'auto-draft' && !empty( $client_id ) )
				echo '<a style="font-size: 11px;" id="client_details_show" href="#">' . __( 'Display Client Details', 'mobile-dj-manager' ) . '</a>';
			?>
		</div>
	</div>
	<style>
	#client_fields	{
		display: <?php echo ( empty( $clients ) ? 'block;' : 'none;' ); ?>
	}
	#client_details	{
		display: none;
	}
	</style>
	<div id="client_fields">
	<script type="text/javascript">
	function displayClientFields() {
		var user = document.getElementById("client_name");
		var user_val = user.options[user.selectedIndex].value;
		var client_div =  document.getElementById("client_fields");
		<?php
		if( $existing_event == false )	{
			?>
			var block_emails = document.getElementById("mdjm_block_emails");
			var reset_pw = document.getElementById("mdjm_reset_pw");
			<?php
		}
		?>
		var client_firstname = document.getElementById("client_firstname");
		var client_email = document.getElementById("client_email");
	
		if (user_val == 'add_new') {
			client_div.style.display = "block";
			<?php
			if( $existing_event == false )	{
				?>
				block_emails.checked = false;
				reset_pw.checked = true;
				<?php
			}
			?>
			client_firstname.className = client_firstname.className +("required");
			client_email.className = client_email.className +("required");
			}
		else {
			client_div.style.display = "none";
			<?php
			if( $existing_event == false )	{
				?>
				reset_pw.checked = false;
				<?php
			}
			?>
			client_firstname.className = "";
			client_email.className = "";
		}
		<?php
		if( $existing_event == false )	{
			?>
			showTemplateOptions();
			<?php
		}
		?>
	}
	</script>
		<div class="mdjm-post-row">
			<div class="mdjm-post-2column">
				<label for="client_firstname" class="mdjm-label"><?php _e( 'First Name:' ); ?></label><br />
				<input type="text" id="client_firstname" name="client_firstname" value="" />
			</div>
			<div class="mdjm-post-last-2column">
				<label for="client_last_name" class="mdjm-label"><?php _e( 'Last Name:' ); ?>&nbsp;<span class="description"><?php _e( '(optional)' ); ?></span></label><br />
				<input type="text" name="client_lastname" id="client_lastname" value="" />
			</div>
		</div>
		<div class="mdjm-post-row">
			<div class="mdjm-post-2column">
				<label for="client_email" class="mdjm-label"><?php _e( 'Email:' ); ?></label><br />
				<input type="text" id="client_email" name="client_email" value="" />
			</div>
			<div class="mdjm-post-last-2column">
			<label for="client_phone" class="mdjm-label"><?php _e( 'Phone:' ); ?>&nbsp;<span class="description"><?php _e( '(optional)' ); ?></span></label><br />
			<input type="text" name="client_phone" id="client_phone" value="" />
			</div>
		</div><!-- mdjm-post-row -->
	</div><!-- client_fields -->
	
	<?php
		$last_login = get_user_meta( $client_id, 'last_login', true );
	?>
	
	<div id="client_details">
		<div class="mdjm-post-row" style="height: 80px;">
			<div class="mdjm-post-2column">
				<p><span class="mdjm-label"><?php printf( __( 'Last Login to %s', 'mobile-dj-manager' ), MDJM_APP ); ?></span>:<br />
					<?php echo ( !empty( $last_login ) ? date( 'H:i d M Y', strtotime( $last_login ) ) : 'Never' ); ?> </p>
			</div>
			<div class="mdjm-post-last-2column">
				<?php
				if( MDJM()->permissions->employee_can( 'send_comms' ) )	{
					?>
					<p><span class="mdjm-label"><?php _e( 'Communicate', 'mobile-dj-manager' ); ?></span>:<br />
						<?php echo '<a id="contact_client" href="' . mdjm_get_admin_page( 'comms' ) . '&to_user=' . $client_id . '&event_id=' . $post->ID . '">' . __( 'Contact', 'mobile-dj-manager' ) . '</a>'; ?></p>
					<?php
				}
				?>
			</div>
		</div>
		<?php
		if( MDJM()->permissions->employee_can( 'list_own_quotes' ) )	{
			$quote = MDJM()->events->retrieve_quote( $post->ID );
			if( !empty( $quote ) )	{
				?>
				<div class="mdjm-post-row-single" style="height: 80px;">
					<div class="mdjm-post-1column">
						<p><span class="mdjm-label"><?php _e( 'Online Quote Status', 'mobile-dj-manager' ); ?></span>:<br />
							<?php 
							if( get_post_status( $quote ) == 'mdjm-quote-viewed' )
								echo __( 'Viewed', 'mobile-dj-manager' ) . ' ' . date( MDJM_TIME_FORMAT . ' ' . MDJM_SHORTDATE_FORMAT, strtotime( get_post_meta( $quote, '_mdjm_quote_viewed_date', true ) ) );
								
							else
								echo __( 'Not viewed yet', 'mobile-dj-manager' );
							?>
							</p>
					</div>
				</div>
				<?php
			}
		}
		?>
	</div>
	
	<?php
	do_action( 'mdjm_events_client_metabox_last', $post, $client_id );
} // mdjm_event_metabox_client_details

/**
 * Output for the Event Details meta box.
 *
 * @since	1.3
 * @param	obj		$post		Required: The post object (WP_Post).
 * @return
 */
function mdjm_event_metabox_event_details( $post )	{
	global $current_user, $mdjm_settings, $pagenow;
	
	wp_enqueue_style( 'jquery-ui-css', 'http://ajax.googleapis.com/ajax/libs/jqueryui/1.8.2/themes/smoothness/jquery-ui.css' );
	wp_enqueue_script( 'jquery-ui-datepicker' );
	
	?>
	<?php mdjm_insert_datepicker(); ?>
	<!-- Start of first row -->
	<div class="mdjm-post-row-single">
		<div class="mdjm-post-1column">
		<?php
		echo '<label for="_mdjm_event_name" class="mdjm-label">' . __( 'Event Name:' ) . '</label><br />' . "\r\n" . 
		'<input type="text" name="_mdjm_event_name" id="_mdjm_event_name" value="' . 
			esc_attr( get_post_meta( $post->ID, '_mdjm_event_name', true ) ) . '" size="50" /> <span class="mdjm-description">' . 
			__( 'Optional: Display name in ' . MDJM_APP ) . '</span>' . "\r\n";
		?>
		</div>
	</div>
	<div class="mdjm-post-row">
		<div class="mdjm-post-2column">
			<label for="_mdjm_event_dj" class="mdjm-label"><?php printf( __( 'Select Primary %s', 'mobile-dj-manager' ), MDJM_DJ ); ?>:</label><br />
			<?php
			/**
			 * If a Multi Employee business, display dropdown of all employees.
			 * But only if the user is permitted to view all employees.
			 */
			if( MDJM_MULTI == true && MDJM()->permissions->employee_can( 'manage_employees' ) )	{
				mdjm_employee_dropdown( 
					array(
						'name'            => '_mdjm_event_dj',
						'class'           => 'required',
						'first_entry'     => '--- ' . sprintf( __( 'Select a %s', 'mobile-dj-manager' ), MDJM_DJ ) . ' ---',
						'selected'        => get_post_meta( $post->ID, '_mdjm_event_dj', true ),
						'group'           => true,
					)
				);
				echo '<input type="hidden" name="event_dj" id="event_dj" value="';
				echo ( $pagenow == 'post-new.php' ? 
					get_current_user_id() : 
					get_post_meta( $post->ID, '_mdjm_event_dj', true ) );
				echo '" />' . "\r\n";
			}
			/**
			 * Otherwise the current user is the DJ
			 */
			else	{
				echo '<input type="hidden" name="_mdjm_event_dj" id="_mdjm_event_dj" value="' . get_current_user_id() . '" />' . "\r\n";
				echo '<input type="hidden" name="event_dj" id="event_dj" value="' . get_current_user_id() . '" />' . "\r\n";
				echo '<input type="text" value="';
				
				if( '' != get_post_meta( $post->ID, '_mdjm_event_dj', true ) )
					echo get_post_meta( $post->ID, '_mdjm_event_dj', true );
				
				else
					echo $current_user->display_name;
				
				echo '" readonly="readonly" />' . "\r\n";	
			}
			?>
		</div>
		<div class="mdjm-post-last-2column">
			<label for="display_event_date" class="mdjm-label"><?php _e( 'Event Date:' ); ?></label><br />
			<input type="text" class="mdjm_date required" name="display_event_date" id="display_event_date" 
				value="<?php echo( get_post_meta( $post->ID, '_mdjm_event_date', true ) ? date( MDJM_SHORTDATE_FORMAT, strtotime( get_post_meta( $post->ID, '_mdjm_event_date', true ) ) ) : '' ); ?>" />
			<input type="hidden" name="_mdjm_event_date" id="_mdjm_event_date" value="<?php echo ( get_post_meta( $post->ID, '_mdjm_event_date', true ) ? get_post_meta( $post->ID, '_mdjm_event_date', true ) : '' ); ?>" />
		</div>
	</div>
	<!-- End of first row -->
	<!-- Start of second row -->
	<div class="mdjm-post-row">
		<div class="mdjm-post-2column">
			<label for="event_start_hr" class="mdjm-label"><?php _e( 'Start Time:' ); ?></label><br />
			<select name="event_start_hr" id="event_start_hr">
			<?php
			$minutes = array( '00', '15', '30', '45' );
			if( MDJM_TIME_FORMAT == 'H:i' )	{
				$i = '00';
				$x = '23';
				$comp = 'H';
			}
			else	{
				$i = '1';
				$x = '12';
				$comp = 'g';	
			}
			while( $i <= $x )	{
				if( $i != 0 && $i < 10 && $comp == 'H' )
					$i = '0' . $i;
				echo '<option value="' . $i . '"';
				selected( date( $comp, strtotime( get_post_meta( $post->ID, '_mdjm_event_start', true ) ) ), $i );
				echo '>' . $i . '</option>' . "\r\n";
				$i++;
			}
			?>
			</select>
			<select name="event_start_min" id="event_start_min">
			<?php
			foreach( $minutes as $minute )	{
				echo '<option value="' . $minute . '"';
				selected( date( 'i', strtotime( get_post_meta( $post->ID, '_mdjm_event_start', true ) ) ), $minute );
				echo '>' . $minute . '</option>' . "\r\n";
			}
			?>
			</select>
			<?php
			if( MDJM_TIME_FORMAT != 'H:i' )	{
				echo '&nbsp;<select name="event_start_period" id="event_start_period">' . "\r\n";
				echo '<option value="AM"';
				selected( date( 'A', strtotime( get_post_meta( $post->ID, '_mdjm_event_start', true ) ) ), 'AM' );
				echo '>AM</option>' . "\r\n";
				echo '<option value="PM"';
				selected( date( 'A', strtotime( get_post_meta( $post->ID, '_mdjm_event_start', true ) ) ), 'PM' );
				echo '>PM</option>' . "\r\n";
				echo '</select>' . "\r\n";
			}
			?>
		</div>
		<div class="mdjm-post-last-2column">
			<label for="event_finish_hr" class="mdjm-label"><?php _e( 'End Time:' ); ?></label><br />
			<select name="event_finish_hr" id="event_finish_hr">
			<?php
			$minutes = array( '00', '15', '30', '45' );
			if( MDJM_TIME_FORMAT == 'H:i' )	{
				$i = '00';
				$x = '23';
				$comp = 'H';
			}
			else	{
				$i = '1';
				$x = '12';
				$comp = 'g';	
			}
			while( $i <= $x )	{
				if( $i != 0 && $i < 10 && $comp == 'H' )
					$i = '0' . $i;
				echo '<option value="' . $i . '"';
				selected( date( $comp, strtotime( get_post_meta( $post->ID, '_mdjm_event_finish', true ) ) ), $i );
				echo '>' . $i . '</option>' . "\r\n";
				$i++;
			}
			?>
			</select>
			<select name="event_finish_min" id="event_finish_min">
			<?php
			foreach( $minutes as $minute )	{
				echo '<option value="' . $minute . '"';
				selected( date( 'i', strtotime( get_post_meta( $post->ID, '_mdjm_event_finish', true ) ) ), $minute );
				echo '>' . $minute . '</option>' . "\r\n";
			}
			?>
			</select>
			<?php
			if( MDJM_TIME_FORMAT != 'H:i' )	{
				echo '&nbsp;<select name="event_finish_period" id="event_finish_period">' . "\r\n";
				echo '<option value="AM"';
				selected( date( 'A', strtotime( get_post_meta( $post->ID, '_mdjm_event_finish', true ) ) ), 'AM' );
				echo '>AM</option>' . "\r\n";
				echo '<option value="PM"';
				selected( date( 'A', strtotime( get_post_meta( $post->ID, '_mdjm_event_finish', true ) ) ), 'PM' );
				echo '>PM</option>' . "\r\n";
				echo '</select>' . "\r\n";
			}
			?>
		</div>
	</div><!-- mdjm-post-row -->
	<!-- End of second row -->
	<!-- Start of third row -->
	<?php
	if( empty( $existing_event ) )	{
		if( $mdjm_settings['payments']['deposit_type'] == 'fixed' )
			$deposit = number_format( $mdjm_settings['payments']['deposit_amount'], 2 );
			
		else
			$deposit = '0.00';	
	}
	else
		$deposit = esc_attr( get_post_meta( $post->ID, '_mdjm_event_deposit', true ) );
	
	if( MDJM()->permissions->employee_can( 'edit_txns' ) )	{
		?>
		<div class="mdjm-post-row">
			<div class="mdjm-post-2column">
			<label for="_mdjm_event_cost" class="mdjm-label"><?php _e( 'Total Cost:' ); ?></label><br />
			<?php echo mdjm_currency_symbol(); ?><input type="text" name="_mdjm_event_cost" id="_mdjm_event_cost" class="mdjm-input-currency required" 
				value="<?php echo get_post_meta( $post->ID, '_mdjm_event_cost', true ); ?>" placeholder="<?php echo mdjm_format_amount( '0' ); ?>" /> 
				<span class="mdjm-description">No <?php echo mdjm_currency_symbol(); ?> symbol needed.</span>
			</div>
			<?php
			
			// Determine whether or not the deposit needs to dynamically update
			echo '<input type="hidden" name="mdjm_update_deposit" id="mdjm_update_deposit" value="' . 
				( !empty( $mdjm_settings['payments']['deposit_type'] ) && $mdjm_settings['payments']['deposit_type'] == 'percentage' ? 
					'1' : '0' ) . '" />' . "\r\n";
			
			?>
			<div class="mdjm-post-last-2column">
			<label for="_mdjm_event_deposit" class="mdjm-label"><?php _e( mdjm_get_deposit_label() . ':' ); ?></label><br />
			<?php echo mdjm_currency_symbol(); ?><input type="text" name="_mdjm_event_deposit" id="_mdjm_event_deposit" class="mdjm-input-currency" 
				value="<?php echo ( !empty( $deposit ) ? $deposit : '' ); ?>" placeholder="<?php echo mdjm_format_amount( '0' ); ?>" /> 
				<span class="mdjm-description">No <?php echo mdjm_currency_symbol(); ?> symbol needed</span>
			</div>
		</div><!-- mdjm-post-row -->
		<!-- End of third row -->
		<?php
	}
	else	{
		?>
			<input type="hidden" name="_mdjm_event_cost" id="_mdjm_event_cost" value="<?php echo get_post_meta( $post->ID, '_mdjm_event_cost', true ); ?>" />
			<input type="hidden" name="_mdjm_event_deposit" id="_mdjm_event_deposit" value="<?php echo ( !empty( $deposit ) ? $deposit : '' ); ?>" />
		<?php
	}
	?>
	<!-- Start of fourth row -->
	<?php
	/* -- Equipment Packages & Add-ons -- */
	if( MDJM_PACKAGES == true )	{
		/* -- Retrieve packages and sort -- */
		$packages = get_option( 'mdjm_packages' );
		if( $packages )	{
			asort( $packages );
			?>
			<div class="mdjm-post-row" style="height: auto !important;">
				<div class="mdjm-post-2column">
				<label for="_mdjm_event_package" class="mdjm-label"><?php _e( 'Select an Event Package:' ); ?> <span class="description">(Optional)</span></label><br />
				<select name="_mdjm_event_package" id="_mdjm_event_package">
				<option value="" data-price="0.00">No Package</option>
				
				<?php
				/* -- Loop through packages to create the select options -- */
				foreach( $packages as $package )	{
					/* -- If a DJ is assigned, only show packages available to them -- */
					if( get_post_meta( $post->ID, '_mdjm_event_dj', true ) )	{
						$djs_with_package = explode( ',', $package['djs'] );
						foreach( $djs_with_package as $dj_with_package )	{
							if( get_post_meta( $post->ID, '_mdjm_event_dj', true ) == $dj_with_package )	{
								echo '<option value="' . $package['slug'] . '" data-price="' . number_format( $package['cost'], 2 ) . '"';
								selected( $package['slug'], get_post_meta( $post->ID, '_mdjm_event_package', true ) );
								echo '>' . esc_attr( $package['name'] ) . '</option>' . "\r\n";
							}	
						}
					}
					/* -- Otherwise, display all packages -- */
					else	{
						echo '<option value="' . $package['slug'] . '" data-price="' . number_format( $package['cost'], 2 ) . '"';
						selected( $package['slug'], get_post_meta( $post->ID, '_mdjm_event_package', true ) );
						echo '>' . esc_attr( $package['name'] ) . '</option>' . "\r\n";	
					}
				}
				?>
				</select>
				</div>
				<div class="mdjm-post-last-2column">
				<?php
				/* -- Display the possible add-ons -- */
				$equipment = get_option( 'mdjm_equipment' );
				$event_package = get_post_meta( $post->ID, '_mdjm_event_package', true );
				/* Remove add on items included in selected package */
				if( $event_package )	{
					$equipment_in_package = explode( ',', $packages[get_post_meta( $post->ID, '_mdjm_event_package', true )]['equipment'] );
					foreach( $equipment_in_package as $equip_in_package )	{
						unset( $equipment[$equip_in_package] );
					}
				}
				// If we have addons, display them
				if( count( $equipment > 0 ) )	{
					$cats = get_option( 'mdjm_cats' );
					if( !empty( $cats ) && is_array( $cats ) )	{
						asort( $cats );
					}
					?>
					<label for="event_addons" class="mdjm-label"><?php _e( 'Select Add-ons:' ); ?> <span class="description">(Optional)</span></label><br />
					<?php
					$current_addons = get_post_meta( $post->ID, '_mdjm_event_addons', true );
					$dj = get_post_meta( $post->ID, '_mdjm_event_dj', true );
					$package = get_post_meta( $post->ID, '_mdjm_event_package', true );
					
					if( !empty( $current_addons ) )
						$args['selected'] = $current_addons;
						
					if( !empty( $dj ) )
						$args['dj'] = $dj;
						
					if( !empty( $package ) )
						$args['package'] = $package;
					
					echo mdjm_addons_dropdown( !empty( $args ) ? $args : '' );
				}
				?>
				</div>
			</div>
			<?php
		}
	}
	?>
	<!-- End of fourth row -->
	<!-- Start of fifth row -->
	 <div class="mdjm-post-row-single-textarea">
		<div class="mdjm-post-1column">
			<label for="_mdjm_event_notes" class="mdjm-label"><?php _e( 'Notes:' ); ?></label><br />
			<textarea name="_mdjm_event_notes" id="_mdjm_event_notes" class="widefat" rows="3" placeholder="<?php _e( 'Enter any information you feel relevant' ); ?>"><?php echo esc_attr( get_post_meta( $post->ID, '_mdjm_event_notes', true ) ); ?></textarea>
		</div>
	</div>
	<!-- End of fifth row -->
	<?php
	// Update event deposit when manually updating cost field
	if( !empty( $mdjm_settings['payments']['deposit_type'] ) && $mdjm_settings['payments']['deposit_type'] == 'percentage' )	{
		?>
		<script type="text/javascript">
		jQuery(document).ready(function($) 	{
			$('#_mdjm_event_cost').on('focusout', '', function()	{
				var current_cost = $("#_mdjm_event_cost").val(); // Current event cost
				
				set_deposit($);
			});
		});
		</script>
		<?php
	}
	do_action( 'mdjm_events_metabox_last', $post );
} // mdjm_event_metabox_event_details

/**
 * Output for the Event Employees meta box.
 *
 * @since	1.3
 * @param	obj		$post		Required: The post object (WP_Post).
 * @return
 */
function mdjm_event_metabox_event_employees( $post )	{
	?>
    <div id="event_employee_list">
        <?php echo mdjm_list_event_employees( $post->ID ); ?>
    </div>
    <hr />
    <?php
    /**
     * Add the row which enables us to add a new employee to the event
     * if the current user is allowed to manage users
     */
     
    if( MDJM()->permissions->employee_can( 'manage_employees' ) )	{
        ?>
        <div class="mdjm-post-row">
            <div class="mdjm-post-3column">
                <label for="event_new_employee" class="mdjm-label"><?php _e( 'Add Employee to Event', 'mobile-dj-manager' ); ?>:</label><br />
                <?php
                
                $event_employees = get_post_meta( $post->ID, '_mdjm_event_employees', true );
                
                if( !empty( $event_employees ) )	{
                    foreach( $event_employees as $employee )	{
                        $exclude[] = $employee['id'];
                    }
                }
                
                $mdjm_roles = mdjm_get_roles();
                
                mdjm_employee_dropdown(
                    array(
                        'name'				=> 'event_new_employee',
                        'first_entry'		=> __( 'Add Employee', 'mobile-dj-manager' ),
                        'first_entry_val'	=> '',
                        'group'				=> true,
                        'structure'			=> true,
                        'exclude'			=> !empty( $exclude ) ? $exclude : '',
                        'echo'				=> true
                    )
                );
                ?>
            </div>
            
            <div class="mdjm-post-3column">
                <label for="event_new_employee_role" class="mdjm-label"><?php _e( 'Role', 'mobile-dj-manager' ); ?>:</label><br />
                <select name="event_new_employee_role" id="event_new_employee_role">
                <?php									
                foreach( $mdjm_roles as $role_id => $role_name )	{						
                    echo '<option value="' . $role_id . '">' . $role_name . '</option>' . "\r\n";
                }
                
                ?>
                </select>
            </div>
            
            <div class="mdjm-post-last-3column">
                <?php
                if( MDJM_PAYMENTS == true && MDJM()->permissions->employee_can( 'manage_txns' ) )	{
                    ?>
                    <label for="event_new_employee_wage" class="mdjm-label"><?php _e( 'Wage', 'mobile-dj-manager' ); ?>:</label><br />
                    <?php echo mdjm_currency_symbol(); ?><input type="text" name="event_new_employee_wage" id="event_new_employee_wage" class="mdjm-input-currency" 
                    value="" placeholder="<?php echo mdjm_format_amount( '0' ); ?>" />
                    <?php
                }
                ?>
            </div>
        </div>
        <div class="mdjm-post-row-single">
            <div class="mdjm-post-1column">
                <a href="#" id="add_event_employee" class="button button-secondary button-small"><?php _e( 'Add', 'mobile-dj-manager' ); ?></a>
            </div>
        </div>
    
    <?php
    }
} // mdjm_event_metabox_event_employees

/**
 * Output for the Event Venue meta box.
 *
 * @since	1.3
 * @param	obj		$post		Required: The post object (WP_Post).
 * @return
 */
function mdjm_event_metabox_venue_details( $post )	{
	$existing_event = ( $post->post_status == 'unattended' || $post->post_status == 'enquiry' || $post->post_status == 'auto-draft' ? false : true );
	
	?>
	<!-- Start of first row -->
	<div class="mdjm-post-row-single">
		<div class="mdjm-post-1column">
			<?php $current_venue = get_post_meta( $post->ID, '_mdjm_event_venue_id', true ); ?>
			<label for="venue_id" class="mdjm-label"><?php _e(' Select Venue:' ); ?></label><br />
			<select name="venue_id" id="venue_id" class="required" onchange="displayVenueFields();">
			<?php
			if( empty( $current_venue ) )
				echo '<option value="">--- Select a Venue ---</option>' . "\r\n";
			?>
			<option value="manual"<?php selected( 'manual', $current_venue ); ?>>--- <?php _e( 'Enter Manually', 'mobile-dj-manager' ); ?> ---</option>
			<option value="client"<?php selected( 'client', $current_venue ); ?>>--- <?php _e( 'Use Client Address', 'mobile-dj-manager' ); ?> ---</option>
			<?php
			/* -- Build the drop down box -- */
			$venues = get_posts( array( 'post_type' => MDJM_VENUE_POSTS, 'orderby' => 'post_title', 'order' => 'ASC', 'numberposts' => -1, ) );
			foreach( $venues as $venue )	{
				echo '<option value="' . $venue->ID . '"';
				selected( $current_venue, $venue->ID );
				echo '>' . $venue->post_title . ' (' . get_post_meta( $venue->ID, '_venue_town', true ) . ')</option>' . "\r\n";
			}
			?>
			</select>
		</div>
	</div>
	<!-- End of first row -->
	<style>
	#venue_fields	{
		display: <?php echo ( $current_venue == 'manual' || !is_numeric( $current_venue ) && $current_venue != 'client' ? 'block;' : 'none;' ); ?>
	}
	</style>
	<div id="venue_fields">
	<script type="text/javascript">
	function displayVenueFields() {
		var venue = document.getElementById("venue_id");
		var venue_val = venue.options[venue.selectedIndex].value;
		var venue_div =  document.getElementById("venue_fields");
		var venue_name = document.getElementById("venue_name");
		var venue_address1 = document.getElementById("venue_address1");
		var venue_town = document.getElementById("venue_town");
		var venue_county = document.getElementById("venue_county");
		var venue_town = document.getElementById("venue_town");
	
		if (venue_val == 'manual') {
			venue_div.style.display = "block";
			venue_name.className = venue.className +("required");
		}
		else {
			venue_div.style.display = "none";
			venue_name.className = "";
		}  
	} 
	</script>
	<!-- Start of second row -->
	<div class="mdjm-post-row">
		<div class="mdjm-post-2column">
			<label for="venue_name" class="mdjm-label"><?php _e( 'Venue Name:' ); ?></label><br />
			<input type="text" id="venue_name" name="venue_name" value="<?php echo esc_attr( get_post_meta( $post->ID, '_mdjm_event_venue_name', true ) ); ?>" />
		</div>
		<div class="mdjm-post-last-2column">
			<label for="venue_contact" class="mdjm-label"><?php _e( 'Contact Name:' ); ?></label><br />
			<input type="text" name="venue_contact" id="venue_contact" value="<?php echo esc_attr( get_post_meta( $post->ID, '_mdjm_event_venue_contact', true ) ); ?>" />
		</div>
	</div>
	<!-- End of second row -->
	<!-- Start of third row -->
	<div class="mdjm-post-row">
		<div class="mdjm-post-2column">
			<label for="venue_phone" class="mdjm-label"><?php _e( 'Contact Phone:' ); ?></label><br />
			<input type="text" name="venue_phone" id="venue_phone" value="<?php echo esc_attr( get_post_meta( $post->ID, '_mdjm_event_venue_phone', true ) ); ?>" />
		</div>
		<div class="mdjm-post-last-2column">
			<label for="venue_email" class="mdjm-label"><?php _e( 'Contact Email:' ); ?></label><br />
			<input type="text" id="venue_email" name="venue_email" value="<?php echo esc_attr( get_post_meta( $post->ID, '_mdjm_event_venue_email', true ) ); ?>" />
		</div>
	</div>
	<!-- End of third row -->            
	<!-- Start of fourth row -->
	<div class="mdjm-post-row">
		<div class="mdjm-post-2column">
			<label for="venue_address1" class="mdjm-label"><?php _e( 'Address Line 1:' ); ?></label><br />
			<input type="text" name="venue_address1" id="venue_address1" value="<?php echo esc_attr( get_post_meta( $post->ID, '_mdjm_event_venue_address1', true ) ); ?>" />
		</div>
		<div class="mdjm-post-last-2column">
			<label for="venue_address2" class="mdjm-label"><?php _e( 'Address Line 2:' ); ?></label><br />
			<input type="text" name="venue_address2" id="venue_address2" value="<?php echo esc_attr( get_post_meta( $post->ID, '_mdjm_event_venue_address2', true ) ); ?>" />
		</div>
	</div>
	<!-- End of fourth row -->
	<!-- Start of fifth row -->
	<div class="mdjm-post-row">
		<div class="mdjm-post-2column">
			<label for="venue_town" class="mdjm-label"><?php _e( 'Town:' ); ?></label><br />
			<input type="text" name="venue_town" id="venue_town" value="<?php echo esc_attr( get_post_meta( $post->ID, '_mdjm_event_venue_town', true ) ); ?>" />
		</div>
		<div class="mdjm-post-last-2column">
			<label for="venue_county" class="mdjm-label"><?php _e( 'County:' ); ?></label><br />
			<input type="text" name="venue_county" id="venue_county" value="<?php echo esc_attr( get_post_meta( $post->ID, '_mdjm_event_venue_county', true ) ); ?>" />
		</div>
	</div><!-- mdjm-post-row -->
	<!-- End of fifth row -->
	<!-- Start of sixth row -->
	<div class="mdjm-post-row-single">
		<div class="mdjm-post-1column">
			<label for="venue_postcode" class="mdjm-label"><?php _e( 'Postcode:' ); ?></label><br />
			<input type="text" name="venue_postcode" id="venue_postcode" value="<?php echo esc_attr( get_post_meta( $post->ID, '_mdjm_event_venue_postcode', true ) ); ?>" />
		</div>
	</div>
	<!-- End of sixth row -->
	<?php 
	if( MDJM()->permissions->employee_can( 'add_venues' ) )	{
		?>
		<!-- Start of seventh row -->
		<div class="mdjm-post-row-single">
			<div class="mdjm-post-1column">
				<input type="checkbox" name="save_venue" id="save_venue" value="Y" /><label for="save_venue" class="mdjm-label">Save this venue?</label>
			</div>
		</div>
		<!-- End of seventh row -->
	<?php
	}
	?>
	</div><!-- End Venue Div -->
	<?php
	do_action( 'mdjm_events_venue_metabox_last', $post );
} // mdjm_event_metabox_venue_details

/**
 * Output for the Event Administration meta box.
 *
 * @since	1.3
 * @param	obj		$post		Required: The post object (WP_Post).
 * @return
 */
function mdjm_event_metabox_administration( $post )	{
	global $mdjm_settings;
		
	$existing_event = ( $post->post_status == 'unattended' || $post->post_status == 'enquiry' || $post->post_status == 'auto-draft' ? false : true );
			
	?>
	<?php mdjm_insert_datepicker(
		array(
			'class'		=> 'mdjm_setup_date',
			'altfield'	=> '_mdjm_event_djsetup'
		)
	); ?>
	<!-- Start of first row -->
	<div class="mdjm-post-row-single">
		<div class="mdjm-post-1column">
		<label for="_mdjm_event_enquiry_source" class="mdjm-label"><?php _e( 'Enquiry Source:' ); ?></label><br />
		<select name="_mdjm_event_enquiry_source" id="_mdjm_event_enquiry_source">
		<?php
		$sources = explode( "\r\n", $mdjm_settings['events']['enquiry_sources'] );
		asort( $sources );
		foreach( $sources as $enquiry_source )	{
			echo '<option value="' . $enquiry_source . '"';
			selected( $enquiry_source, get_post_meta( $post->ID, '_mdjm_event_enquiry_source', true ) );
			echo '>' . $enquiry_source . '</option>' . "\r\n";
		}
		?>
		</select>
		</div>
	</div>
	<!-- End of first row -->
	<div class="mdjm-post-row">
		<div class="mdjm-post-2column">
			<label for="dj_setup_date" class="mdjm-label"><?php _e( MDJM_DJ . ' Setup:' ); ?></label><br />
			<input type="text" class="mdjm_setup_date" name="dj_setup_date" id="dj_setup_date" 
				value="<?php echo( get_post_meta( $post->ID, '_mdjm_event_djsetup', true ) ? date( MDJM_SHORTDATE_FORMAT, 
				strtotime( get_post_meta( $post->ID, '_mdjm_event_djsetup', true ) ) ) : '' ); ?>" />
			<input type="hidden" name="_mdjm_event_djsetup" id="_mdjm_event_djsetup" value="<?php echo ( get_post_meta( $post->ID, '_mdjm_event_date', true ) ? 
				get_post_meta( $post->ID, '_mdjm_event_djsetup', true ) : '' ); ?>" />
		</div>
		<div class="mdjm-post-last-2column">
			<label for="dj_setup_hr" class="mdjm-label"><?php _e( 'Setup Time:' ); ?></label><br />
			<select name="dj_setup_hr" id="dj_setup_hr">
			<?php
			$minutes = array( '00', '15', '30', '45' );
			if( MDJM_TIME_FORMAT == 'H:i' )	{
				$i = '00';
				$x = '23';
				$comp = 'H';
			}
			else	{
				$i = '1';
				$x = '12';
				$comp = 'g';	
			}
			while( $i <= $x )	{
				if( $i != 0 && $i < 10 && $comp == 'H' )
					$i = '0' . $i;
				echo '<option value="' . $i . '"';
				selected( date( $comp, strtotime( get_post_meta( $post->ID, '_mdjm_event_djsetup_time', true ) ) ), $i );
				echo '>' . $i . '</option>' . "\r\n";
				$i++;
			}
			?>
			</select>
			<select name="dj_setup_min" id="dj_setup_min">
			<?php
			foreach( $minutes as $minute )	{
				echo '<option value="' . $minute . '"';
				selected( date( 'i', strtotime( get_post_meta( $post->ID, '_mdjm_event_djsetup_time', true ) ) ), $minute );
				echo '>' . $minute . '</option>' . "\r\n";
			}
			?>
			</select>
			<?php
			if( MDJM_TIME_FORMAT != 'H:i' )	{
				echo '&nbsp;<select name="dj_setup_period" id="dj_setup_period">' . "\r\n";
				echo '<option value="AM"';
				selected( date( 'A', strtotime( get_post_meta( $post->ID, '_mdjm_event_djsetup_time', true ) ) ), 'AM' );
				echo '>AM</option>' . "\r\n";
				echo '<option value="PM"';
				selected( date( 'A', strtotime( get_post_meta( $post->ID, '_mdjm_event_djsetup_time', true ) ) ), 'PM' );
				echo '>PM</option>' . "\r\n";
				echo '</select>' . "\r\n";
			}
			?>
		</div>
	</div>
	<!-- End of first row -->
	<!-- Start of second row -->
	<div class="mdjm-post-row-single-textarea">
		<div class="mdjm-post-1column">
			<label for="_mdjm_event_dj_notes" class="mdjm-label"><?php _e( MDJM_DJ . ' Notes:' ); ?></label><br />
			<textarea name="_mdjm_event_dj_notes" id="_mdjm_event_dj_notes" rows="3" class="widefat" placeholder="<?php _e( 'Notes entered here can be seen by the Event ' . MDJM_DJ . ' and Admins only. Clients will not see this information' ); ?>"><?php echo esc_attr( get_post_meta( $post->ID, '_mdjm_event_dj_notes', true ) ); ?></textarea>
		</div>
	</div>
	<!-- End of second row -->
	<!-- Start of third row -->
	<?php
	if( current_user_can( 'administrator' ) )	{
		?>
	<div class="mdjm-post-row-single-textarea">
		<div class="mdjm-post-1column">
			<label for="_mdjm_event_admin_notes" class="mdjm-label"><?php _e( 'Admin Notes:' ); ?></label><br />
			<textarea name="_mdjm_event_admin_notes" id="_mdjm_event_admin_notes" rows="3" class="widefat" placeholder="<?php _e( 'Notes entered here can be seen by Admins only. Clients &amp; ' . MDJM_DJ . '\'s will not see this information' ); ?>"><?php echo esc_attr( get_post_meta( $post->ID, '_mdjm_event_admin_notes', true ) ); ?></textarea>
		</div>
	</div>
		<?php
	}
	?>
	<!-- End of third row -->
	<?php
} // mdjm_event_metabox_administration

/**
 * Output for the Event Transactions meta box.
 *
 * @since	1.3
 * @param	obj		$post		Required: The post object (WP_Post).
 * @return
 */
function mdjm_event_metabox_transactions( $post )	{
	wp_enqueue_script( 'event-trans', MDJM_PLUGIN_URL . '/assets/js/mdjm-save-transaction.js' );
	wp_localize_script( 'event-trans', 'posttrans', array( 'ajax_url' => admin_url( 'admin-ajax.php' ) ) );
											
	echo '<div id="transaction">' . "\r\n";
	
	$transactions = MDJM()->txns->show_event_transactions( $post->ID );
	echo $transactions;
	echo '</div>' . "\r\n";
	
	/* -- Display New Transaction Form -- */
	echo '<hr size="1" />' . "\r\n";
	mdjm_insert_datepicker(
		array(
			'class'		=> 'trans_date',
			'altfield'	=> 'transaction_date',
			'maxdate'	=> 'today'
		)
	);
	
	echo '<div class="mdjm-post-row">' . "\r\n";
		echo '<div class="mdjm-post-3column">' . "\r\n";
			echo '<label class="mdjm-label" for="transaction_amount">Amount:</label><br />' . 
				mdjm_currency_symbol() . '<input type="text" name="transaction_amount" id="transaction_amount" class="mdjm-input-currency" placeholder="' . 
					mdjm_format_amount( '10' ) . '" />' . "\r\n";
		echo '</div>' . "\r\n";
	
		echo '<div class="mdjm-post-3column">' . "\r\n";
			echo '<label class="mdjm-label" for="transaction_display_date">Date:</label><br />' . 
			'<input type="text" name="transaction_display_date" id="transaction_display_date" class="trans_date" />' .
			'<input type="hidden" name="transaction_date" id="transaction_date" />' . "\r\n";
		echo '</div>' . "\r\n";
	
		echo '<div class="mdjm-post-last-3column">' . "\r\n";
			echo '<label class="mdjm-label" for="transaction_direction">Direction:</label><br />' . 
			'<select name="transaction_direction" id="transaction_direction" onChange="displayPaid();">' . "\r\n" . 
			'<option value="In">Incoming</option>' . "\r\n" . 
			'<option value="Out">Outgoing</option>' . "\r\n" . 
			'</select>' . "\r\n";
		echo '</div>' . "\r\n";
	echo '</div>' . "\r\n";	
	?>
	<script type="text/javascript">
	function displayPaid() {
		var direction  =  document.getElementById("transaction_direction");
		var direction_val = direction.options[direction.selectedIndex].value;
		var paid_from_div =  document.getElementById("paid_from_field");
		var paid_to_div =  document.getElementById("paid_to_field");
	
	  if (direction_val == 'Out') {
		  paid_from_div.style.display = "none";
		  paid_to_div.style.display = "block";
	  }
	  else {
		  paid_from_div.style.display = "block";
		  paid_to_div.style.display = "none";
	  }  
	} 
	</script>
	<?php
	echo '<div class="mdjm-post-row">' . "\r\n";
		echo '<div class="mdjm-post-3column">' . "\r\n";
			echo '<div id="paid_from_field">' . "\r\n";
				echo '<label class="mdjm-label" for="transaction_from">Paid From:</label><br />' . 
					'<input type="text" name="transaction_from" id="transaction_from" class="regular_text" /> ' . 
					'<span class="description">Leave empty if from client</span>';	
			echo '</div>' . "\r\n";
			
			echo '<div id="paid_to_field">' . "\r\n";
				echo '<label class="mdjm-label" for="transaction_to">Paid To:</label><br />' . 
					'<input type="text" name="transaction_to" id="transaction_to" class="regular_text" /> ' . 
					'<span class="description">Leave empty if to client</span>';
			echo '</div>' . "\r\n";
		echo '</div>' . "\r\n";
		
		$types = get_transaction_types();
		echo '<div class="mdjm-post-3column">' . "\r\n";
			echo '<label class="mdjm-label" for="transaction_for">Details:</label><br />' . 
				'<select name"transaction_for" id="transaction_for">' . 
				'<option value="">--- Select ---</option>' . "\r\n";
				foreach( $types as $type )	{
					echo '<option value="' . $type->term_id . '">' . $type->name . '</option>' . "\r\n";	
				}
				echo '</select>' . "\r\n";
		echo '</div>' . "\r\n";
		
		$sources = get_transaction_source();
		echo '<div class="mdjm-post-last-3column">' . "\r\n";
			echo '<label class="mdjm-label" for="transaction_src">Source:</label><br />' . "\r\n" . 
				'<select name="transaction_src" id="transaction_src">' . "\r\n" . 
				'<option value="">--- Select ---</option>' . "\r\n";
				foreach( $sources as $source )	{
					echo '<option value="' . $source . '"' . 
						selected( $GLOBALS['mdjm_settings']['payments']['default_type'], $source, false ) . 
						'>' . $source . '</option>' . "\r\n";	
				}
				echo '</select>' . "\r\n";
		echo '</div>' . "\r\n";
		echo '</div>' . "\r\n";
	echo '<p><input type="button" name="save_transaction" id="save_transaction" class="button button-secondary button-small" value="Add Transaction" /></p>' . "\r\n";
} // mdjm_event_metabox_transactions

/**
 * Output for the Event History meta box.
 *
 * @since	1.3
 * @param	obj		$post		Required: The post object (WP_Post).
 * @return
 */
function mdjm_event_metabox_event_history( $post )	{
	$existing_event = ( $post->post_status == 'unattended' || $post->post_status == 'enquiry' || $post->post_status == 'auto-draft' ? false : true );
		
	$comms = get_posts( array(
								'post_type'		 => MDJM_COMM_POSTS,
								'meta_key'	 	  => '_event',
								'meta_value'   		=> $post->ID,
								'order_by'  		  => 'post_date',
								'order'			 => 'DESC',
								'posts_per_page'	=> 3,
								'post_status'	 => 'any',
								) );
								
	$total_comms = get_posts( array(
								'post_type'		 => MDJM_COMM_POSTS,
								'meta_key'	 	  => '_event',
								'meta_value'   		=> $post->ID,
								'posts_per_page'	=> -1,
								'post_status'	 => 'any',
								) );
	if( !empty( $total_comms ) )	{	
	?>
	<div class="mdjm-post-row-single" style="height: 80px !important">
		<div class="mdjm-post-1column">
		<span class="mdjm-meta-title">Emails <span class="count">(<?php echo ( !empty( $total_comms ) ? count( $total_comms ) : '0' ); ?> total)</span></span><br />
	<?php
	$i = 1;
	foreach( $comms as $recent )	{
		echo date( 'd M', get_post_meta( $recent->ID, '_date_sent', true ) ) . 
		' | '; ?> <?php edit_post_link( rtrim( $recent->post_title ), '', ' | ' . 
		ucfirst( $recent->post_status ), $recent->ID ); ?> <?php echo ' on ' . 
		date( MDJM_SHORTDATE_FORMAT . ' \a\t ' . MDJM_TIME_FORMAT, strtotime( $recent->post_modified ) ) . 
		( $i < 3 && $i != count( $comms ) ? '<br />' : '' );
		$i++;	 
	}
	?>
		</div>
	</div>
	<?php
	}
	/* -- Journal Entries -- */
	$journal = get_comments( array(
							'post_id'		=> $post->ID,
							'number'		=> '3',
							'order'			=> 'DESC',
							'comment_type'	=> 'mdjm-journal',
							) );
	?>
	<div class="mdjm-post-row-single" style="height: 80px !important">
		<div class="mdjm-post-1column">
		<span class="mdjm-meta-title">Journal Entries <span class="count"><a href="<?php echo admin_url( 'edit-comments.php?p=' . $post->ID ); ?>">(<?php echo wp_count_comments( $post->ID )->approved; ?> total)</a></span></span><br />
	<?php
	$i = 1;
	foreach( $journal as $j )	{
		$extract = explode('|', $j->comment_content );
		echo date( 'd M', strtotime( $j->comment_date ) ) . 
		' | ' . 
		trim( str_replace( '<br />', ' ', preg_replace('/\s*\([^)]*\)/', '', $extract[0] ) ) ) . 
		( $i < 3 && $i != count( $journal ) ? '<br />' : '' );
		$i++;
	}
	?>
		</div>
	</div>
	<?php
} // mdjm_event_metabox_event_history

/**
 * Output for the Event Options meta box.
 *
 * @since	1.3
 * @param	obj		$post		Required: The post object (WP_Post).
 * @return
 */
function mdjm_event_metabox_event_options( $post )	{
	global $mdjm_settings;
		
	$existing_event = ( $post->post_status == 'unattended' || $post->post_status == 'enquiry' || $post->post_status == 'auto-draft' ? false : true );
	
	?>
	<div class="mdjm-meta-row" style="height: 50px !important">
		<div class="mdjm-rightt-col">
			<label for="mdjm_event_status" class="mdjm-label"><?php _e( 'Event Status:' ); ?></label><br />
			<?php
			event_stati_dropdown( array( 'name' => 'mdjm_event_status', 'small' => true ) );
			?>
		</div>
	</div>
	<?php
	
	/* -- The current event type -- */
	$existing_event_type = wp_get_object_terms( $post->ID, 'event-types' );
	
	/* -- Catch empty selections -- */
	?>
	<input type="hidden" name="mdjm_event_type" value="0" />
		<div class="mdjm-meta-row" style="height: 50px !important">
			<div class="mdjm-left-col">
				<label for="mdjm_event_type" class="mdjm-label"><?php _e( 'Event Type:' ); ?></label><br />
		<div id="event_types">
			<?php
			/* -- Display the drop down selection -- */    
			wp_dropdown_categories( 
				array( 
					'taxonomy' 			=> 'event-types',
					'hide_empty' 		  => 0,
					'name' 				=> 'mdjm_event_type',
					'id' 				  => 'mdjm_event_type',
					'selected' 			=> ( isset( $existing_event_type[0]->term_id ) ? $existing_event_type[0]->term_id : '' ),
					'orderby' 			 => 'name',
					'hierarchical' 		=> 0,
					'show_option_none' 	=> __( 'Select Event Type' ),
					'class'			   => 'mdjm-meta required'
				)
			);
											
			if( MDJM()->permissions->employee_can( 'manage_all_events' ) )	{
				?>
				<a id="new_event_type" class="side-meta" href="#">Add New</a>
				<?php
			}
			?>
		</div>
			</div>
		</div>
		<div id="new_event_type_div">
			<div class="mdjm-meta-row" style="height: 50px !important">
				<div class="mdjm-left-col"><br />
						<input type="text" name="event_type_name" id="event_type_name" class="mdjm-meta" placeholder="Event Type Name" />&nbsp;
							<a href="#" id="add_event_type" class="button button-primary button-small">Add</a>
				</div>
			</div>
		</div>
	<script type="text/javascript">
	jQuery("#mdjm_event_type option:first").val(null);
	</script>
	<div class="mdjm-meta-row" style="height: 50px !important">
		<div class="mdjm-left-col">
			<label for="_mdjm_event_contract" class="mdjm-label"><?php _e( 'Event Contract:' ); ?></label><br />
			<select name="_mdjm_event_contract" id="_mdjm_event_contract" class="mdjm-meta">
			<?php
			$contract_templates = get_posts( array( 'post_type' => MDJM_CONTRACT_POSTS,
													'orderby' => 'post_title',
													'order' => 'ASC',
													'numberposts' => -1,
													'exclude' => is_dj() && isset( $mdjm_settings['permissions']['dj_disable_template'] ) ? $mdjm_settings['permissions']['dj_disable_template'] : '',
													) );
			foreach( $contract_templates as $contract_template )	{
				echo '<option value="' . $contract_template->ID . '"';
				if( $event_contract = get_post_meta( $post->ID, '_mdjm_event_contract', true ) )	{
					selected( $event_contract, $contract_template->ID );
				}
				else	{
					selected( $mdjm_settings['events']['default_contract'], $contract_template->ID );
				}
				/* -- If the event is past enquiry we only display the contract that is selected
						and add a link to view it -- */
				echo ( $post->post_status != 'auto-draft' && $post->post_status != 'mdjm-unattended' && $post->post_status != 'mdjm-enquiry' && $post->post_status != 'mdjm-contract' && $event_contract != $contract_template->ID ? ' disabled="disabled"' : '' );
				
				echo '>' . $contract_template->post_title . '</option>' . "\r\n";
			}
			?>
			</select>
			<?php
			if( $post->post_status == 'mdjm-approved' || $post->post_status == 'mdjm-completed' )
				echo '<a id="view_contract" class="side-meta" href="' . mdjm_get_formatted_url( get_post_meta( $post->ID, '_mdjm_signed_contract', true ), false ) . '" target="_blank">View Signed Contract</a>';
			?>
		</div>
	</div>
	<div class="mdjm-meta-row">
		<div class="mdjm-left-col">
		<?php
		echo '<input type="checkbox" name="mdjm_block_emails" id="mdjm_block_emails" value="Y"';
		
		if( $post->post_status == 'mdjm-unattended' )
			echo ' onclick="showTemplateOptions();"';
			
		if( $post->post_status == 'mdjm-enquiry' )
			checked( 'contract_to_client', false );
			
		if( $post->post_status == 'mdjm-enquiry' )
			checked( 'booking_conf_to_client', false );
		
		echo ' />' . "\r\n";
		?>
		</div>
		<div class="mdjm-right-col"><?php _e( 'Disable Client Update Emails', 'mobile-dj-manager' ) . '?'; ?></div>
	</div>
	<?php
	if( $post->post_status == 'mdjm-unattended' || $post->post_status == 'auto-draft' )	{
		?>
		<div id="email_template_fields">
			<script type="text/javascript">
			function showTemplateOptions(){
				if (mdjm_block_emails.checked == 1)	{
					document.getElementById('email_template_fields').style.display = "none";
				}
				else	{
					document.getElementById('email_template_fields').style.display = "block";	
				}
			}
			</script>
			<div class="mdjm-meta-row">
				<div class="mdjm-left-col">
				<input type="checkbox" name="mdjm_reset_pw" id="mdjm_reset_pw" value="Y" />
				</div>
				<div class="mdjm-right-col"><?php _e( 'Reset Client Password?' ); ?></div>
			</div>
			<div class="mdjm-meta-row" style="height: 60px !important">
				Email Quote Template:<br />
				<select name="mdjm_email_template" id="mdjm_email_template" class="mdjm-meta" style="width: 200px;">
				<?php
				$email_templates = get_posts( array( 'post_type' => MDJM_EMAIL_POSTS,
													'orderby' => 'post_title',
													'order' => 'ASC',
													'numberposts' => -1,
													'exclude' => is_dj() && isset( $mdjm_settings['permissions']['dj_disable_template'] ) ? 
														$mdjm_settings['permissions']['dj_disable_template'] : '',
													) );
				foreach( $email_templates as $email_template )	{
					echo '<option value="' . $email_template->ID . '"';
					selected( $mdjm_settings['templates']['enquiry'], $email_template->ID );
					echo '>' . $email_template->post_title . '</option>' . "\r\n";	
				}
				?>
				</select>
			</div>				
		</div>
		<?php
		if( MDJM_ONLINE_QUOTES == true )	{
			?>
			<div class="mdjm-meta-row" style="height: 60px !important">
				Online Quote Template:<br />
				<select name="mdjm_online_quote" id="mdjm_online_quote" class="mdjm-meta" style="width: 200px;">
				<?php
				foreach( $email_templates as $email_template )	{
					echo '<option value="' . $email_template->ID . '"';
					selected( $mdjm_settings['templates']['online_enquiry'], $email_template->ID );
					echo '>' . $email_template->post_title . '</option>' . "\r\n";	
				}
				?>
				</select>
			</div>
			<?php
		}
	}
	?>
	<div class="mdjm-meta-row">
		<div class="mdjm-left-col">
		<input type="checkbox" name="deposit_paid" id="deposit_paid" value="Paid"<?php checked( get_post_meta( $post->ID, '_mdjm_event_deposit_status', true ), 'Paid' ); ?> />
		</div>
		<div class="mdjm-right-col"><?php _e( mdjm_get_deposit_label() . ' paid?' ); ?></div>
	</div>
	<div class="mdjm-meta-row">
		<div class="mdjm-left-col">
		<input type="checkbox" name="balance_paid" id="balance_paid" value="Paid"<?php checked( get_post_meta( $post->ID, '_mdjm_event_balance_status', true ), 'Paid' ); ?> />
		</div>
		<div class="mdjm-right-col"><?php _e( mdjm_get_balance_label() . ' paid?' ); ?></div>
	</div>
	
	<div class="mdjm-meta-row">
		<div class="mdjm-left-col">
		<input type="checkbox" name="_mdjm_event_playlist" id="_mdjm_event_playlist" value="Y"<?php 
			if( $existing_event == false )	{
				if( MDJM_PLAYLIST_ENABLE == true )	{
					echo ' checked = "checked"';
				}
			}
			else	{	
				if( get_post_meta( $post->ID, '_mdjm_event_playlist', true ) == 'Y' )	{
					echo ' checked = "checked"';
				}
			}
			?> />
		</div>
		<div class="mdjm-right-col"><?php _e( 'Enable Event Playlist?', 'mobile-dj-manager' ); ?></div>
	</div>
	
	<?php 
		// Execute actions before the update/save event button
		do_action( 'mdjm_event_options_meta_content_last', $post );
	?>
	
	<div class="mdjm-meta-row" style="height: <?php echo ( $post->post_status == 'mdjm-unattended' || $post->post_status == 'auto-draft' ? 
												'60px' : '40px' ); ?> !important;">
		<div class="mdjm-left-col">
		<?php
		if( MDJM()->permissions->employee_can( 'manage_events' ) )	{
			submit_button( 
						( $post->post_status == 'auto-draft' ? 'Add Event' : 'Update Event' ),
						'primary',
						'save',
						false,
						array( 'id' => 'save-post' ) );
		}
													
		if( $post->post_status == 'mdjm-unattended' || $post->post_status == 'auto-draft' && MDJM()->permissions->employee_can( 'manage_all_events' ) )	{
			echo '<br />' . 
			'<br />' .
			'<a style="color:#a00" href="' . get_delete_post_link( $post->ID ) . '">Delete this event</a>' . 
			"\r\n";
		}
		?>
		</div>
	</div>
	<?php
} // mdjm_event_metabox_event_options
?>