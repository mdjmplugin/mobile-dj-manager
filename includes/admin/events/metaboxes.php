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
			array( 'tagsdiv-enquiry-source', 'mdjm-event', 'side' )
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

	global $mdjm_event, $mdjm_event_update;

	$save              = __( 'Create', 'kb-support' );
	$mdjm_event_update = false;
	$mdjm_event        = new MDJM_Event( $post->ID );
	
	if ( 'draft' != $post->post_status && 'auto-draft' != $post->post_status )	{
		$mdjm_event_update = true;
	}

	$metaboxes = apply_filters( 'mdjm_event_add_metaboxes',
		array(
			array(
				'id'         => 'mdjm-event-client',
				'title'      => __( 'Client Details', 'mobile-dj-manager' ),
				'callback'   => 'mdjm_event_metabox_client_callback',
				'context'    => 'normal',
				'priority'   => 'high',
				'args'       => array(),
				'dependancy' => '',
				'permission' => ''
			),
			array(
				'id'         => 'mdjm-event-details',
				'title'      => sprintf( __( '%s Details', 'mobile-dj-manager' ), mdjm_get_label_singular() ),
				'callback'   => 'mdjm_event_metabox_details_callback',
				'context'    => 'normal',
				'priority'   => 'high',
				'args'       => array(),
				'dependancy' => '',
				'permission' => ''
			),
			array(
				'id'         => 'mdjm-event-employees',
				'title'      => sprintf( __( '%s Employees', 'mobile-dj-manager' ), mdjm_get_label_singular() ),
				'callback'   => 'mdjm_event_metabox_event_employees',
				'context'    => 'normal',
				'priority'   => '',
				'args'       => array(),
				'dependancy' => '',
				'permission' => ''
			),
			array(
				'id'         => 'mdjm-event-venue',
				'title'      => __( 'Venue Details', 'mobile-dj-manager' ),
				'callback'   => 'mdjm_event_metabox_venue_callback',
				'context'    => 'normal',
				'priority'   => '',
				'args'       => array(),
				'dependancy' => '',
				'permission' => ''
			),
			array(
				'id'         => 'mdjm-event-admin',
				'title'      => __( 'Administration', 'mobile-dj-manager' ),
				'callback'   => 'mdjm_event_metabox_admin_callback',
				'context'    => 'normal',
				'priority'   => 'low',
				'args'       => array(),
				'dependancy' => '',
				'permission' => ''
			),
			array(
				'id'         => 'mdjm-event-transactions',
				'title'      => __( 'Transactions', 'mobile-dj-manager' ),
				'callback'   => 'mdjm_event_metabox_transactions_callback',
				'context'    => 'normal',
				'priority'   => 'low',
				'args'       => array(),
				'dependancy' => '',
				'permission' => 'edit_txns'
			),
			array(
				'id'         => 'mdjm-event-history',
				'title'      => sprintf( __( '%s History', 'mobile-dj-manager' ), mdjm_get_label_singular() ),
				'callback'   => 'mdjm_event_metabox_history_callback',
				'context'    => 'normal',
				'priority'   => 'low',
				'args'       => array(),
				'dependancy' => '',
				'permission' => 'manage_mdjm'
			),
			array(
				'id'         => 'mdjm-event-options',
				'title'      => sprintf( __( '%s Options', 'mobile-dj-manager' ), mdjm_get_label_singular() ),
				'callback'   => 'mdjm_event_metabox_event_options',
				'context'    => 'side',
				'priority'   => 'high',
				'args'       => array(),
				'dependancy' => '',
				'permission' => ''
			)
		)
	);
	// Runs before metabox output
	do_action( 'mdjm_event_before_metaboxes' );
	
	// Begin metaboxes
	foreach( $metaboxes as $metabox )	{
		// Dependancy check
		if( ! empty( $metabox['dependancy'] ) && $metabox['dependancy'] === false )	{
			continue;
		}
		
		// Permission check
		if( ! empty( $metabox['permission'] ) && ! mdjm_employee_can( $metabox['permission'] ) )	{
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
			$metabox['context'],
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
 * @param	obj		$post	The post object (WP_Post).
 * @return
 */
function mdjm_event_metabox_client_callback( $post )	{

	global $post, $mdjm_event, $mdjm_event_update;

	wp_nonce_field( basename( __FILE__ ), 'mdjm-event' . '_nonce' );

	/*
	 * Output the items for the client metabox
	 * @since	1.3.7
	 * @param	int	$post_id	The Event post ID
	 */
	do_action( 'mdjm_event_client_fields', $post->ID );

} // mdjm_event_metabox_client_callback

/**
 * Output for the Event Details meta box.
 *
 * @since	1.3
 * @param	obj		$post	The post object (WP_Post).
 * @return
 */
function mdjm_event_metabox_details_callback( $post )	{

	global $post, $mdjm_event, $mdjm_event_update;

	/*
	 * Output the items for the event details metabox
	 * @since	1.3.7
	 * @param	int	$post_id	The Event post ID
	 */
	do_action( 'mdjm_event_details_fields', $post->ID );

} // mdjm_event_metabox_details_callback

/**
 * Output for the Event Venue meta box.
 *
 * @since	1.3
 * @param	obj		$post	The post object (WP_Post).
 * @return
 */
function mdjm_event_metabox_venue_callback( $post )	{

	global $post, $mdjm_event, $mdjm_event_update;

	/*
	 * Output the items for the event venue metabox
	 * @since	1.3.7
	 * @param	int	$post_id	The Event post ID
	 */
	do_action( 'mdjm_event_venue_fields', $post->ID );

} // mdjm_event_metabox_venue_callback

/**
 * Output for the Event Administration meta box.
 *
 * @since	1.3
 * @param	obj		$post	The post object (WP_Post).
 * @return
 */
function mdjm_event_metabox_admin_callback( $post )	{

	global $post, $mdjm_event, $mdjm_event_update;

	/*
	 * Output the items for the event admin metabox
	 * @since	1.3.7
	 * @param	int	$post_id	The Event post ID
	 */
	do_action( 'mdjm_event_admin_fields', $post->ID );

} // mdjm_event_metabox_admin_callback

/**
 * Output for the Event Details meta box.
 *
 * @since	1.3
 * @param	obj		$post	The post object (WP_Post).
 * @return
 */
function mdjm_event_metabox_history_callback( $post )	{

	global $post, $mdjm_event, $mdjm_event_update;

	/*
	 * Output the items for the event history metabox
	 * @since	1.3.7
	 * @param	int	$post_id	The Event post ID
	 */
	do_action( 'mdjm_event_history_fields', $post->ID );

} // mdjm_event_metabox_history_callback

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
			$venues = get_posts( array( 'post_type' => 'mdjm-venue', 'orderby' => 'post_title', 'order' => 'ASC', 'numberposts' => -1, ) );
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
			<label for="venue_name" class="mdjm-label"><?php _e( 'Venue Name:', 'mobile-dj-manager' ); ?></label><br />
			<input type="text" id="venue_name" name="venue_name" value="<?php echo esc_attr( get_post_meta( $post->ID, '_mdjm_event_venue_name', true ) ); ?>" />
		</div>
		<div class="mdjm-post-last-2column">
			<label for="venue_contact" class="mdjm-label"><?php _e( 'Contact Name:', 'mobile-dj-manager' ); ?></label><br />
			<input type="text" name="venue_contact" id="venue_contact" value="<?php echo esc_attr( get_post_meta( $post->ID, '_mdjm_event_venue_contact', true ) ); ?>" />
		</div>
	</div>
	<!-- End of second row -->
	<!-- Start of third row -->
	<div class="mdjm-post-row">
		<div class="mdjm-post-2column">
			<label for="venue_phone" class="mdjm-label"><?php _e( 'Contact Phone:', 'mobile-dj-manager' ); ?></label><br />
			<input type="text" name="venue_phone" id="venue_phone" value="<?php echo esc_attr( get_post_meta( $post->ID, '_mdjm_event_venue_phone', true ) ); ?>" />
		</div>
		<div class="mdjm-post-last-2column">
			<label for="venue_email" class="mdjm-label"><?php _e( 'Contact Email:', 'mobile-dj-manager' ); ?></label><br />
			<input type="text" id="venue_email" name="venue_email" value="<?php echo esc_attr( get_post_meta( $post->ID, '_mdjm_event_venue_email', true ) ); ?>" />
		</div>
	</div>
	<!-- End of third row -->            
	<!-- Start of fourth row -->
	<div class="mdjm-post-row">
		<div class="mdjm-post-2column">
			<label for="venue_address1" class="mdjm-label"><?php _e( 'Address Line 1:', 'mobile-dj-manager' ); ?></label><br />
			<input type="text" name="venue_address1" id="venue_address1" value="<?php echo esc_attr( get_post_meta( $post->ID, '_mdjm_event_venue_address1', true ) ); ?>" />
		</div>
		<div class="mdjm-post-last-2column">
			<label for="venue_address2" class="mdjm-label"><?php _e( 'Address Line 2:', 'mobile-dj-manager' ); ?></label><br />
			<input type="text" name="venue_address2" id="venue_address2" value="<?php echo esc_attr( get_post_meta( $post->ID, '_mdjm_event_venue_address2', true ) ); ?>" />
		</div>
	</div>
	<!-- End of fourth row -->
	<!-- Start of fifth row -->
	<div class="mdjm-post-row">
		<div class="mdjm-post-2column">
			<label for="venue_town" class="mdjm-label"><?php _e( 'Town:', 'mobile-dj-manager' ); ?></label><br />
			<input type="text" name="venue_town" id="venue_town" value="<?php echo esc_attr( get_post_meta( $post->ID, '_mdjm_event_venue_town', true ) ); ?>" />
		</div>
		<div class="mdjm-post-last-2column">
			<label for="venue_county" class="mdjm-label"><?php _e( 'County:', 'mobile-dj-manager' ); ?></label><br />
			<input type="text" name="venue_county" id="venue_county" value="<?php echo esc_attr( get_post_meta( $post->ID, '_mdjm_event_venue_county', true ) ); ?>" />
		</div>
	</div><!-- mdjm-post-row -->
	<!-- End of fifth row -->
	<!-- Start of sixth row -->
	<div class="mdjm-post-row-single">
		<div class="mdjm-post-1column">
			<label for="venue_postcode" class="mdjm-label"><?php _e( 'Postcode:', 'mobile-dj-manager' ); ?></label><br />
			<input type="text" name="venue_postcode" id="venue_postcode" value="<?php echo esc_attr( get_post_meta( $post->ID, '_mdjm_event_venue_postcode', true ) ); ?>" />
		</div>
	</div>
	<!-- End of sixth row -->
	<?php 
	if( mdjm_employee_can( 'add_venues' ) )	{
		?>
		<!-- Start of seventh row -->
		<div class="mdjm-post-row-single">
			<div class="mdjm-post-1column">
				<input type="checkbox" name="save_venue" id="save_venue" value="Y" /><label for="save_venue" class="mdjm-label"><?php _e( 'Save this venue?', 'mobile-dj-manager' ); ?></label>
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
 * Output for the Event Transactions meta box.
 *
 * @since	1.3
 * @global	obj		$post				WP_Post object
 * @global	obj		$mdjm_event			MDJM_Ticket class object
 * @global	bool	$mdjm_event_update	True if this event is being updated, false if new
 * @param	obj		$post				The post object (WP_Post).
 * @return
 */
function mdjm_event_metabox_transactions_callback( $post )	{

	global $post, $mdjm_event, $mdjm_event_update;

	/*
	 * Output the items for the event transactions metabox
	 * @since	1.3.7
	 * @param	int	$post_id	The Event post ID
	 */
	do_action( 'mdjm_event_txn_fields', $post->ID );
									
} // mdjm_event_metabox_transactions_callback

/**
 * Output for the Event Options meta box.
 *
 * @since	1.3
 * @param	obj		$post		Required: The post object (WP_Post).
 * @return
 */
function mdjm_event_metabox_event_options( $post )	{
			
	$existing_event = ( $post->post_status == 'unattended' || $post->post_status == 'enquiry' || $post->post_status == 'auto-draft' ? false : true );
	
	?>
	<div class="mdjm-meta-row" style="height: 50px !important">
		<div class="mdjm-rightt-col">
			<label for="mdjm_event_status" class="mdjm-label"><?php printf( __( '%s Status:', 'mobile-dj-manager' ), mdjm_get_label_singular() ); ?></label><br />
			<?php
			mdjm_event_status_dropdown(
				array(
					'selected'	=> $post->post_status == 'auto-draft' ? 'mdjm-unattended' : $post->post_status,
					'small' => true
				)
			);
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
				<label for="mdjm_event_type" class="mdjm-label"><?php printf( __( '%s Type:', 'mobile-dj-manager' ), mdjm_get_label_singular() ); ?></label><br />
		<div id="event_types">
			<?php
			/* -- Display the drop down selection -- */    
			wp_dropdown_categories( 
				array( 
					'taxonomy' 			=> 'event-types',
					'hide_empty' 		  => 0,
					'name' 				=> 'mdjm_event_type',
					'id' 				  => 'mdjm_event_type',
					'selected' 			=> ( isset( $existing_event_type[0]->term_id ) ? $existing_event_type[0]->term_id : mdjm_get_option( 'event_type_default', '' ) ),
					'orderby' 			 => 'name',
					'hierarchical' 		=> 0,
					'show_option_all'     => sprintf( __( 'Select %s Type', 'mobile-dj-manager' ), mdjm_get_label_singular() ),
					'class'			   => 'mdjm-meta required'
				)
			);
											
			if( mdjm_employee_can( 'manage_all_events' ) )	{
				?>
				<a id="new_event_type" class="button button-secondary button-small side-meta"><?php _e( 'Add New', 'mobile-dj-manager' ); ?></a>
				<?php
			}
			?>
		</div>
			</div>
		</div>
		<div id="new_event_type_div">
			<div class="mdjm-meta-row" style="height: 50px !important">
				<div class="mdjm-left-col"><br />
						<input type="text" name="event_type_name" id="event_type_name" class="mdjm-meta" placeholder="<?php printf( __( '%s Type Name', 'mobile-dj-manager' ), mdjm_get_label_singular() ); ?>" />&nbsp;
							<a id="add_event_type" class="button button-primary button-small"><?php _e( 'Add', 'mobile-dj-manager' ); ?></a>
				</div>
			</div>
		</div>
	<script type="text/javascript">
	jQuery("#mdjm_event_type option:first").val(null);
	</script>
	<div class="mdjm-meta-row" style="height: 50px !important">
		<div class="mdjm-left-col">
			<label for="_mdjm_event_contract" class="mdjm-label"><?php printf( __( 'Event Contract:' ), mdjm_get_label_singular() ); ?></label><br />
			<select name="_mdjm_event_contract" id="_mdjm_event_contract" class="mdjm-meta">
			<?php
			$contract_templates = get_posts(
				array(
					'post_type' => 'contract',
					'orderby' => 'post_title',
					'order' => 'ASC',
					'numberposts' => -1,
					'exclude' => is_dj() && ( mdjm_get_option( 'dj_disable_template' ) ) ? mdjm_get_option( 'dj_disable_template' ) : '',
				)
			);
			
			foreach( $contract_templates as $contract_template )	{
				echo '<option value="' . $contract_template->ID . '"';
				if( $event_contract = get_post_meta( $post->ID, '_mdjm_event_contract', true ) )	{
					selected( $event_contract, $contract_template->ID );
				}
				else	{
					selected( mdjm_get_option( 'default_contract' ), $contract_template->ID );
				}
				/* -- If the event is past enquiry we only display the contract that is selected
						and add a link to view it -- */
				echo ( $post->post_status != 'auto-draft' && $post->post_status != 'mdjm-unattended' && $post->post_status != 'mdjm-enquiry' && $post->post_status != 'mdjm-contract' && $event_contract != $contract_template->ID ? ' disabled="disabled"' : '' );
				
				echo '>' . $contract_template->post_title . '</option>' . "\r\n";
			}
			?>
			</select>
		</div>
	</div>
    <?php if( mdjm_employee_can( 'manage_events' ) && mdjm_contract_is_signed( $post->ID ) ) : ?>
		<div class="mdjm-meta-row">
			<div class="mdjm-left-col">
		<?php
		echo '<a id="view_contract" class="side-meta" href="' . esc_url( add_query_arg( array( 'mdjm_action' => 'review_contract', 'event_id' => $post->ID ), home_url() ) ) . '" target="_blank">View Signed Contract</a>';
		?>
			</div>
		</div>
    <?php endif; ?>
	<div class="mdjm-meta-row">
		<div class="mdjm-left-col">
		<?php
		echo '<input type="checkbox" name="mdjm_block_emails" id="mdjm_block_emails" value="1"';
		
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
        <div class="mdjm-meta-row">
            <div class="mdjm-left-col">
            <input type="checkbox" name="mdjm_reset_pw" id="mdjm_reset_pw" value="Y" />
            </div>
            <div class="mdjm-right-col"><?php _e( 'Reset Client Password?', 'mobile-dj-manager' ); ?></div>
        </div>
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
			<div class="mdjm-meta-row" style="height: 60px !important">
				Email Quote Template:<br />
				<select name="mdjm_email_template" id="mdjm_email_template" class="mdjm-meta" style="width: 200px;">
				<?php
				$email_templates = get_posts(
					array(
						'post_type' => 'email_template',
						'orderby' => 'post_title',
						'order' => 'ASC',
						'numberposts' => -1,
						'exclude' => is_dj() && ( mdjm_get_option( 'dj_disable_template' ) ) ? mdjm_get_option( 'dj_disable_template' ) : '',
					)
				);
				
				foreach( $email_templates as $email_template )	{
					echo '<option value="' . $email_template->ID . '"';
					selected( mdjm_get_option( 'enquiry' ), $email_template->ID );
					echo '>' . $email_template->post_title . '</option>' . "\r\n";	
				}
				?>
				</select>
			</div>				
		</div>
		<?php
		if( mdjm_get_option( 'online_enquiry', false ) )	{
			?>
			<div class="mdjm-meta-row" style="height: 60px !important">
				Online Quote Template:<br />
				<select name="mdjm_online_quote" id="mdjm_online_quote" class="mdjm-meta" style="width: 200px;">
				<?php
				foreach( $email_templates as $email_template )	{
					echo '<option value="' . $email_template->ID . '"';
					selected( mdjm_get_option( 'online_enquiry' ), $email_template->ID );
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
				if( mdjm_get_option( 'enable_playlists', true ) == true )	{
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
		<div class="mdjm-right-col"><?php printf( __( 'Enable %s Playlist?', 'mobile-dj-manager' ), mdjm_get_label_singular() ); ?></div>
	</div>
	
	<?php 
		// Execute actions before the update/save event button
		do_action( 'mdjm_event_options_meta_content_last', $post );
	?>
	
	<div class="mdjm-meta-row" style="height: <?php echo ( $post->post_status == 'mdjm-unattended' || $post->post_status == 'auto-draft' ? 
												'60px' : '40px' ); ?> !important;">
		<div class="mdjm-left-col">
		<?php
		if( mdjm_employee_can( 'manage_events' ) )	{
			submit_button( 
						( $post->post_status == 'auto-draft' ? sprintf( __( 'Add %s', 'mobile-dj-manager' ), mdjm_get_label_singular() ) : sprintf( __( 'Update %s', 'mobile-dj-manager' ), mdjm_get_label_singular() ) ),
						'primary',
						'save',
						false,
						array( 'id' => 'save-post' ) );
		}
													
		if( $post->post_status == 'mdjm-unattended' || $post->post_status == 'auto-draft' && mdjm_employee_can( 'manage_all_events' ) )	{
			echo '<br />' . 
			'<br />' .
			'<a style="color:#a00" href="' . get_delete_post_link( $post->ID ) . '">' . sprintf( __( 'Delete this event', 'mobile-dj-manager' ), mdjm_get_label_singular() ) . '</a>' . 
			"\r\n";
		}
		?>
		</div>
	</div>
	<?php
} // mdjm_event_metabox_event_options

/**
 * Output the event client row
 *
 * @since	1.3.7
 * @global	obj		$mdjm_event			MDJM_Event class object
 * @global	bool	$mdjm_event_update	True if this event is being updated, false if new.
 * @param	int		$event_id			The event ID.
 * @return	str
 */
function mdjm_event_metabox_client_select_row( $event_id )	{

	global $mdjm_event, $mdjm_event_update;

	$clients = mdjm_get_clients( 'client' );

	?>
	<div id="mdjm-event-client" class="mdjm_form_fields">
        <label for="client_name"><?php _e(' Select Client:' ); ?></label> 
        <?php echo MDJM()->html->client_dropdown( array(
			'selected'         => $mdjm_event->client,
			'class'            => '',
			'roles'            => array( 'client' ),
			'chosen'           => true,
			'placeholder'      => __( 'Select a Client', 'mobile-dj-manager' ),
			'null_value'       => array( '' => __( 'Select a Client', 'mobile-dj-manager' ) ),
			'add_new'          => empty( $mdjm_event->client ) ? true : false,
			'show_option_all'  => false,
			'show_option_none' => false
		) ); ?>
        <?php if ( mdjm_employee_can( 'view_clients_list' ) && $mdjm_event_update && $mdjm_event->client ) : ?>
            <a id="toggle_client_details" class="mdjm-small mdjm-fake"><?php _e( 'Toggle Client Details', 'mobile-dj-manager' ); ?></a>
        <?php endif; ?>
	</div>
	<?php

} // mdjm_event_metabox_client_select_row
add_action( 'mdjm_event_client_fields', 'mdjm_event_metabox_client_select_row', 10 );

/**
 * Output the event add new client row
 *
 * @since	1.3.7
 * @global	obj		$mdjm_event			MDJM_Event class object
 * @global	bool	$mdjm_event_update	True if this event is being updated, false if new.
 * @param	int		$event_id			The event ID.
 * @return	str
 */
function mdjm_event_metabox_client_add_new_row( $event_id )	{

	global $mdjm_event, $mdjm_event_update;

	if( ! mdjm_employee_can( 'view_clients_list' ) )	{
		return;
	}

	?>
    <div id="mdjm-event-add-new-client-fields" class="mdjm-hidden">
    	<table class="widefat mdjm_event_add_client_table mdjm_form_fields">
        	<thead>
            	<tr>
                	<th colspan="3"><?php _e( 'New Client Details', 'mobile-dj-manager' ); ?></th>
                </tr>
            </thead>
        	<tbody>
                <tr>
                    <td><label for="client_firstname"><?php _e( 'First Name:', 'mobile-dj-manager' ); ?></label><br />
                        <?php echo MDJM()->html->text( array(
							'name'  => 'client_firstname',
							'class' => ''
						) ); ?></td>

                    <td><label for="client_lastname"><?php _e( 'Last Name:', 'mobile-dj-manager' ); ?></label><br />
                         <?php echo MDJM()->html->text( array(
							'name'        => 'client_lastname',
							'class'       => '',
							'placeholder' => __( 'Optional', 'mobile-dj-manager' )
						) ); ?></td>

                    <td><label for="client_email"><?php _e( 'Email:', 'mobile-dj-manager' ); ?></label><br />
                        <?php echo MDJM()->html->text( array(
							'name'  => 'client_email',
							'class' => '',
							'type'  => 'email'
						) ); ?></td>
                </tr>
                
                <tr>
                	<td><label for="client_phone"><?php _e( 'Phone:', 'mobile-dj-manager' ); ?></label><br />
                        <?php echo MDJM()->html->text( array(
							'name'        => 'client_phone',
							'class'       => '',
							'placeholder' => __( 'Optional', 'mobile-dj-manager' )
						) ); ?></td>

					<td><label for="client_phone2"><?php _e( 'Alt. Phone:', 'mobile-dj-manager' ); ?></label><br />
                         <?php echo MDJM()->html->text( array(
							'name'        => 'client_phone2',
							'class'       => '',
							'placeholder' => __( 'Optional', 'mobile-dj-manager' )
						) ); ?></td>
                    <td></td>
                </tr>
                
                <tr>
                	<td colspan="3">
                    	<a id="mdjm-add-client" class="button button-primary button-small"><?php _e( 'Add Client', 'mobile-dj-manager' ); ?></a>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
    <?php

} // mdjm_event_metabox_client_add_new_row
add_action( 'mdjm_event_client_fields', 'mdjm_event_metabox_client_add_new_row', 15 );

/**
 * Output the event client details row
 *
 * @since	1.3.7
 * @global	obj		$mdjm_event			MDJM_Event class object
 * @global	bool	$mdjm_event_update	True if this event is being updated, false if new.
 * @param	int		$event_id			The event ID.
 * @return	str
 */
function mdjm_event_metabox_client_details_row( $event_id )	{

	global $mdjm_event, $mdjm_event_update;

	if( ! mdjm_employee_can( 'view_clients_list' ) )	{
		return;
	}

	mdjm_do_client_details_table( $mdjm_event->client, $event_id );

} // mdjm_event_metabox_client_details_row
add_action( 'mdjm_event_client_fields', 'mdjm_event_metabox_client_details_row', 20 );

/**
 * Output the event details row
 *
 * @since	1.3.7
 * @global	obj		$mdjm_event			MDJM_Event class object
 * @global	bool	$mdjm_event_update	True if this event is being updated, false if new.
 * @param	int		$event_id			The event ID.
 * @return	str
 */
function mdjm_event_metabox_details_data_table( $event_id )	{

	global $mdjm_event, $mdjm_event_update;
	
	mdjm_insert_datepicker();

	?>
    <div id="mdjm_event_details_table">
        <table class="widefat mdjm_form_fields">
            <tbody>
    
                <?php do_action( 'mdjm_event_metabox_event_details_table_pre_date_field', $event_id ); ?>
    
                <tr>
                    <td><label for="display_event_date"><?php printf( __( '%s Date:', 'mobile-dj-manager' ), mdjm_get_label_singular() ); ?></label><br />
                        <?php echo MDJM()->html->text( array(
                            'name'     => 'display_event_date',
                            'class'    => 'mdjm_date',
                            'required' => true,
                            'value'    => ! empty( $mdjm_event->date ) ? mdjm_format_short_date( $mdjm_event->date ) : ''
                        ) ); ?>
                        <?php echo MDJM()->html->hidden( array(
                            'name'  => '_mdjm_event_date',
                            'value' => ! empty( $mdjm_event->date ) ? $mdjm_event->date : ''
                        ) ); ?></td>
                    
                    <td><label for="_mdjm_event_name"><?php printf( __( '%s Name:', 'mobile-dj-manager' ), mdjm_get_label_singular() ); ?></label><br />
                        <?php echo MDJM()->html->text( array(
                            'name'        => '_mdjm_event_name',
                            'class'       => '',
                            'value'       => esc_attr( $mdjm_event->get_name() ),
                            'placeholder' => sprintf( __( 'Optional: Display name in %s', 'mobile-dj-manager' ), mdjm_get_option( 'app_name', __( 'Client Zone', 'mobile-dj-manager' ) ) )
                        ) ); ?></td>
                </tr>
    
                <?php do_action( 'mdjm_event_metabox_event_details_table_pre_time_fields', $event_id ); ?>
    
                <?php
                    $start  = $mdjm_event->get_start_time();
                    $finish = $mdjm_event->get_finish_time();
                    $format = mdjm_get_option( 'time_format', 'H:i' );
                ?>
    
                <tr>
                    <td><label for="event_start_hr"><?php _e( 'Start Time:', 'mobile-dj-manager' ); ?></label><br />
                        <?php echo MDJM()->html->time_hour_select( array(
                            'selected' => ! empty( $start ) ? date( $format[0], strtotime( $start ) ) : ''
                        ) ); ?> 
                        <?php echo MDJM()->html->time_minute_select( array(
                            'selected' => ! empty( $start ) ? date( $format[2], strtotime( $start ) ) : ''
                        ) ); ?> 
                        <?php if ( 'H:i' != $format ) : ?>
                            <?php echo MDJM()->html->time_period_select( array(
                                'selected' => ! empty( $start ) ? date( 'A', strtotime( $start ) ) : ''
                            ) ); ?>
                        <?php endif; ?></td>
                    
                    <td><label for="event_finish_hr"><?php _e( 'End Time:', 'mobile-dj-manager' ); ?></label><br />
                        <?php echo MDJM()->html->time_hour_select( array(
                            'name'     => 'event_finish_hr',
                            'selected' => ! empty( $finish ) ? date( $format[0], strtotime( $finish ) ) : ''
                        ) ); ?> 
                        <?php echo MDJM()->html->time_minute_select( array(
                            'name'     => 'event_finish_min',
                            'selected' => ! empty( $finish ) ? date( $format[2], strtotime( $finish ) ) : ''
                        ) ); ?> 
                        <?php if ( 'H:i' != $format ) : ?>
                            <?php echo MDJM()->html->time_period_select( array(
                                'name'     => 'event_finish_period',
                                'selected' => ! empty( $finish ) ? date( 'A', strtotime( $finish ) ) : ''
                            ) ); ?>
                        <?php endif; ?></td>
                </tr>
    
                <?php if( mdjm_employee_can( 'edit_txns' ) ) : ?>
    
                    <?php do_action( 'mdjm_event_metabox_event_details_table_pre_cost_fields', $event_id ); ?>
        
                    <tr>
                        <td><label for="_mdjm_event_deposit"><?php _e( 'Total Cost:', 'mobile-dj-manager' ); ?></label><br />
                        <?php echo mdjm_currency_symbol() . MDJM()->html->text( array(
                                'name'        => '_mdjm_event_cost',
                                'class'       => 'mdjm-currency',
                                'placeholder' => mdjm_sanitize_amount( '0.00' ),
                                'value'       => ! empty( $mdjm_event->price ) ? mdjm_sanitize_amount( $mdjm_event->price ) : ''
                            ) ); ?></td>
                        <td><label for="_mdjm_event_deposit"><?php _e( 'Deposit:', 'mobile-dj-manager' ); ?></label><br />
                            <?php echo mdjm_currency_symbol() . MDJM()->html->text( array(
                                'name'        => '_mdjm_event_deposit',
                                'class'       => 'mdjm-currency',
                                'placeholder' => mdjm_sanitize_amount( '0.00' ),
                                'value'       => $mdjm_event_update ? mdjm_sanitize_amount( $mdjm_event->deposit ) : mdjm_calculate_deposit( $mdjm_event->price )
                            ) ); ?></td>
                    </tr>
    
                <?php else : ?>
    
                    <?php echo MDJM()->html->hidden( array(
                        'name'  => '_mdjm_event_cost',
                        'value' => ! empty( $mdjm_event->price ) ? mdjm_sanitize_amount( $mdjm_event->price ) : ''
                    ) ); ?>
    
                    <?php echo MDJM()->html->hidden( array(
                        'name'  => '_mdjm_event_deposit',
                        'value' => $mdjm_event_update ? mdjm_sanitize_amount( $mdjm_event->deposit ) : ''
                    ) ); ?>
    
                <?php endif; ?>
    
                <?php do_action( 'mdjm_event_metabox_event_details_table_pre_notes_field', $event_id ); ?>
                    
                <tr>
                    <td colspan="2"><?php echo MDJM()->html->textarea( array(
                            'label'       => __( 'Notes:', 'mobile-dj-manager' ),
                            'name'        => '_mdjm_event_notes',
                            'placeholder' => __( 'Information entered here is visible by employees and clients', 'mobile-dj-manager' ),
                            'value'       => get_post_meta( $mdjm_event->ID, '_mdjm_event_notes', true )
                        ) ); ?></td>
                </tr>
    
            </tbody>
        </table>
    </div>
    <?php

} // mdjm_event_metabox_details_data_table
add_action( 'mdjm_event_details_fields', 'mdjm_event_metabox_details_data_table', 10 );

/**
 * Output the event packages row
 *
 * @since	1.3.7
 * @global	obj		$mdjm_event			MDJM_Event class object
 * @global	bool	$mdjm_event_update	True if this event is being updated, false if new.
 * @param	int		$event_id			The event ID.
 * @return	str
 */
function mdjm_event_metabox_details_packages_table( $event_id )	{

	global $mdjm_event, $mdjm_event_update;

	if( ! mdjm_packages_enabled() )	{
		return;
	}

	$package  = $mdjm_event->get_package();
	$addons   = $mdjm_event->get_addons();
	$employee = ! empty( $mdjm_event->employee_id ) ? $mdjm_event->employee_id : get_current_user_id();

	?>
    <div id="mdjm_event_packages_table">
        <table class="widefat mdjm_form_fields">
            <thead>
                <tr>
                    <th colspan="2"><?php printf( __( '%s Package and Equipment Add-ons', 'mobile-dj-manager' ), mdjm_get_label_singular() ); ?></th>
                </tr>
            </thead>
            <tbody>
    
                <tr>
                    <td><label for="_mdjm_event_package"><?php printf( __( 'Select an %s Package:' ), mdjm_get_label_singular() ); ?></label><br />
                        <?php echo MDJM()->html->packages_dropdown( array(
                            'employee' => $employee,
                            'selected' => $package
                        ) ); ?></td>
                    
                    <td><label for="event_addons"><?php _e( 'Select Add-ons:', 'mobile-dj-manager' ); ?></label><br />
                        <?php echo MDJM()->html->addons_dropdown( array(
							'selected'         => $addons,
							'show_option_none' => false,
							'show_option_all'  => false,
							'employee'         => $employee,
							'package'          => $package,
							'cost'             => true,
							'data'             => array()
						) ); ?></td>
                </tr>
    
            </tbody>
        </table>
    </div>
    <?php

} // mdjm_event_metabox_details_packages_table
add_action( 'mdjm_event_details_fields', 'mdjm_event_metabox_details_packages_table', 20 );

/**
 * Output the event transaction list table
 *
 * @since	1.3.7
 * @global	obj		$mdjm_event			MDJM_Event class object
 * @global	bool	$mdjm_event_update	True if this event is being updated, false if new.
 * @param	int		$event_id			The event ID.
 * @return	str
 */
function mdjm_event_metabox_txn_list_table( $event_id )	{

	global $mdjm_event, $mdjm_event_update;

	?>
	<p><strong><?php _e( 'All Transactions', 'mobile-dj-manager' ); ?></strong> <span class="mdjm-small">(<a id="mdjm_txn_toggle" class="mdjm-fake"><?php _e( 'toggle', 'mobile-dj-manager' ); ?></a>)</span></p>
	<div id="mdjm_event_txn_table" class="mdjm_meta_table_wrap">
        <?php mdjm_do_event_txn_table( $event_id ); ?>
	</div>
	<?php
} // mdjm_event_metabox_txn_list_table
add_action( 'mdjm_event_txn_fields', 'mdjm_event_metabox_txn_list_table', 10 );

/**
 * Output the event transaction list table
 *
 * @since	1.3.7
 * @global	obj		$mdjm_event			MDJM_Event class object
 * @global	bool	$mdjm_event_update	True if this event is being updated, false if new.
 * @param	int		$event_id			The event ID.
 * @return	str
 */
function mdjm_event_metabox_txn_add_new_row( $event_id )	{

	global $mdjm_event, $mdjm_event_update;
	
	mdjm_insert_datepicker(
		array(
			'id'		=> 'mdjm_txn_display_date',
			'altfield'	=> 'mdjm_txn_date',
			'maxdate'	=> 'today'
		)
	);

	?>

	<div id="mdjm-event-add-txn-table">
        <table class="widefat mdjm_event_add_txn_table mdjm_form_fields">
        	<thead>
            	<tr>
            		<th colspan="3"><?php _e( 'Add Transaction', 'mobile-dj-manager' ); ?></th>
                </tr>
            </thead>

			<tbody>
            	<tr>
                	<td><label for="mdjm_txn_amount"><?php _e( 'Amount:', 'mobile-dj-manager' ); ?></label><br />
                    	<?php echo mdjm_currency_symbol() .
						MDJM()->html->text( array(
							'name'        => 'mdjm_txn_amount',
							'class'       => 'mdjm-input-currency',
							'placeholder' => mdjm_sanitize_amount( '10' )
						) ); ?></td>

					<td><label for="mdjm_txn_display_date"><?php _e( 'Date:', 'mobile-dj-manager' ); ?></label><br />
						<?php echo MDJM()->html->text( array(
							'name'  => 'mdjm_txn_display_date',
							'class' => ''
						) ) .
						MDJM()->html->hidden( array(
							'name' => 'mdjm_txn_date'
						) ); ?></td>

					<td><label for="mdjm_txn_amount"><?php _e( 'Direction:', 'mobile-dj-manager' ); ?></label><br />
                    	<?php echo MDJM()->html->select( array(
							'name'        => 'mdjm_txn_direction',
							'options'     => array(
								'In'      => __( 'Incoming', 'mobile-dj-manager' ),
								'Out'     => __( 'Outgoing', 'mobile-dj-manager' )
							),
							'show_option_all'  => false,
							'show_option_none' => false
						) ); ?></td>
                </tr>

				<tr>
                	<td><span id="mdjm_txn_from_container"><label for="mdjm_txn_from"><?php _e( 'From:', 'mobile-dj-manager' ); ?></label><br />
                    	<?php echo MDJM()->html->text( array(
							'name'        => 'mdjm_txn_from',
							'class'       => '',
							'placeholder' => __( 'Leave empty if client', 'mobile-dj-manager' )
						) ); ?></span>
                        <span id="mdjm_txn_to_container" class="mdjm-hidden"><label for="mdjm_txn_to"><?php _e( 'To:', 'mobile-dj-manager' ); ?></label><br />
                    	<?php echo MDJM()->html->text( array(
							'name'        => 'mdjm_txn_to',
							'class'       => '',
							'placeholder' => __( 'Leave empty if client', 'mobile-dj-manager' )
						) ); ?></span></td>

					<td><label for="mdjm_txn_for"><?php _e( 'For:', 'mobile-dj-manager' ); ?></label><br />
						<?php echo MDJM()->html->txn_type_dropdown(); ?></td>

					<td><label for="mdjm_txn_src"><?php _e( 'Paid via:', 'mobile-dj-manager' ); ?></label><br />
                    	<?php echo MDJM()->html->select( array(
							'name'             => 'mdjm_txn_src',
							'options'          => mdjm_get_txn_source(),
							'selected'         => mdjm_get_option( 'default_type', 'Cash' ),
							'show_option_all'  => false,
							'show_option_none' => false
						) ); ?></td>
                </tr>

				<?php if ( mdjm_get_option( 'manual_payment_cfm_template' ) ) : ?>

                    <tr id="mdjm-txn-email">
                        <td colspan="3"><?php echo MDJM()->html->checkbox( array( 
                            'name'     => 'mdjm_manual_txn_email',
                            'current'  => mdjm_get_option( 'manual_payment_cfm_template' ) ? true : false,
                            'class'    => 'mdjm-checkbox'
                            ) ); ?>
                            <?php _e( 'Send manual payment confirmation email?', 'mobile-dj-manager' ); ?></td>
                    </tr>

				<?php endif; ?>

            </tbody>
        </table>

    </div>
    
    <p><a id="save_transaction" class="button button-secondary button-small"><?php _e( 'Add Transaction', 'mobile-dj-manager' ); ?></a></p>
	<?php
} // mdjm_event_metabox_txn_add_new_row
add_action( 'mdjm_event_txn_fields', 'mdjm_event_metabox_txn_add_new_row', 20 );

/**
 * Output the event venue select row
 *
 * @since	1.3.7
 * @global	obj		$mdjm_event			MDJM_Event class object
 * @global	bool	$mdjm_event_update	True if this event is being updated, false if new.
 * @param	int		$event_id			The event ID.
 * @return	str
 */
function mdjm_event_metabox_venue_select_row( $event_id )	{

	global $mdjm_event, $mdjm_event_update;

	?>
	<div id="mdjm-event-venue" class="mdjm_form_fields">
    	<label for="venue_id"><?php _e(' Select Venue:' ); ?></label> 
        <?php echo MDJM()->html->venue_dropdown( array(
			'name'        => 'venue_id',
			'selected'    => strtolower( $mdjm_event->get_venue_id() ),
			'placeholder' => __( 'Select a Venue', 'mobile-dj-manager' ),
			'chosen'      => true
		) ); ?> 
        <?php if ( $mdjm_event_update && $mdjm_event->venue_id ) : ?>
            <a id="toggle_venue_details" class="mdjm-small mdjm-fake mdjm-hidden"><?php _e( 'Toggle Venue Details', 'mobile-dj-manager' ); ?></a>
        <?php endif; ?>
    </div>
	<?php
} // mdjm_event_metabox_venue_select_row
add_action( 'mdjm_event_venue_fields', 'mdjm_event_metabox_venue_select_row', 10 );

/**
 * Output the event client details row
 *
 * @since	1.3.7
 * @global	obj		$mdjm_event			MDJM_Event class object
 * @global	bool	$mdjm_event_update	True if this event is being updated, false if new.
 * @param	int		$event_id			The event ID.
 * @return	str
 */
function mdjm_event_metabox_venue_details_row( $event_id )	{

	global $mdjm_event, $mdjm_event_update;

	mdjm_do_venue_details_table( $mdjm_event->ID, $mdjm_event->venue_id );

} // mdjm_event_metabox_venue_details_row
add_action( 'mdjm_event_venue_fields', 'mdjm_event_metabox_venue_details_row', 20 );

/**
 * Output the event add new venue table
 *
 * @since	1.3.7
 * @global	obj		$mdjm_event			MDJM_Event class object
 * @global	bool	$mdjm_event_update	True if this event is being updated, false if new.
 * @param	int		$event_id			The event ID.
 * @return	str
 */
function mdjm_event_metabox_venue_add_new_table( $event_id )	{

	global $mdjm_event, $mdjm_event_update;

	$venue_id = $mdjm_event->get_venue_id();

	?>
    <div id="mdjm-event-add-new-venue-fields" class="mdjm-hidden">
    	<table class="widefat mdjm_event_add_venue_table mdjm_form_fields">
        	<thead>
            	<tr>
                	<th colspan="3"><?php _e( 'Venue Details', 'mobile-dj-manager' ); ?></th>
                </tr>
            </thead>
        	<tbody>
                <tr>
                    <td><label for="venue_name"><?php _e( 'Venue Name:', 'mobile-dj-manager' ); ?></label><br />
                        <?php echo MDJM()->html->text( array(
							'name'  => 'venue_name',
							'class' => '',
							'value' => mdjm_get_event_venue_meta( $event_id, 'name' )
						) ); ?></td>

                    <td><label for="venue_contact"><?php _e( 'Contact Name:', 'mobile-dj-manager' ); ?></label><br />
                         <?php echo MDJM()->html->text( array(
							'name'        => 'venue_contact',
							'class'       => '',
							'placeholder' => __( 'Optional', 'mobile-dj-manager' )
						) ); ?></td>

                    <td><label for="venue_email"><?php _e( 'Contact Email:', 'mobile-dj-manager' ); ?></label><br />
                        <?php echo MDJM()->html->text( array(
							'name'        => 'venue_email',
							'class'       => '',
							'type'        => 'email',
							'placeholder' => __( 'Optional', 'mobile-dj-manager' )
						) ); ?></td>
                </tr>
                
                <tr>
                	<td><label for="venue_address1"><?php _e( 'Address Line 1:', 'mobile-dj-manager' ); ?></label><br />
                        <?php echo MDJM()->html->text( array(
							'name'        => 'venue_address1',
							'class'       => '',
							'placeholder' => __( 'Optional', 'mobile-dj-manager' )
						) ); ?></td>

					<td><label for="venue_address2"><?php _e( 'Address Line 2:', 'mobile-dj-manager' ); ?></label><br />
                         <?php echo MDJM()->html->text( array(
							'name'        => 'venue_address2',
							'class'       => '',
							'placeholder' => __( 'Optional', 'mobile-dj-manager' )
						) ); ?></td>
                    <td><label for="venue_town"><?php _e( 'Town:', 'mobile-dj-manager' ); ?></label><br />
                         <?php echo MDJM()->html->text( array(
							'name'        => 'venue_town',
							'class'       => '',
							'placeholder' => __( 'Optional', 'mobile-dj-manager' )
						) ); ?></td>
                </tr>

				<tr>
                	<td><label for="venue_county"><?php _e( 'County:', 'mobile-dj-manager' ); ?></label><br />
                        <?php echo MDJM()->html->text( array(
							'name'        => 'venue_county',
							'class'       => '',
							'placeholder' => __( 'Optional', 'mobile-dj-manager' )
						) ); ?></td>

					<td><label for="venue_postcode"><?php _e( 'Postcode:', 'mobile-dj-manager' ); ?></label><br />
                         <?php echo MDJM()->html->text( array(
							'name'        => 'venue_postcode',
							'class'       => '',
							'placeholder' => __( 'Optional', 'mobile-dj-manager' )
						) ); ?></td>
                    <td><label for="venue_phone"><?php _e( 'Phone:', 'mobile-dj-manager' ); ?></label><br />
                         <?php echo MDJM()->html->text( array(
							'name'        => 'venue_phone',
							'class'       => '',
							'placeholder' => __( 'Optional', 'mobile-dj-manager' )
						) ); ?></td>
                </tr>

                <tr id="mdjm-save-venue-button-row">
                	<td colspan="3">
                    	<a id="mdjm-save-venue" class="button button-primary button-small"><?php _e( 'Save Venue', 'mobile-dj-manager' ); ?></a>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
    <?php
} // mdjm_event_metabox_venue_add_new_table
add_action( 'mdjm_event_venue_fields', 'mdjm_event_metabox_venue_add_new_table', 30 );

/**
 * Output the event enquiry source row
 *
 * @since	1.3.7
 * @global	obj		$mdjm_event			MDJM_Event class object
 * @global	bool	$mdjm_event_update	True if this event is being updated, false if new.
 * @param	int		$event_id			The event ID.
 * @return	str
 */
function mdjm_event_metabox_admin_enquiry_source_row( $event_id )	{

	global $mdjm_event, $mdjm_event_update;

	$enquiry_source = mdjm_get_enquiry_source( $event_id );

	?>
	<div id="mdjm-event-enquiry-source-row" class="mdjm_form_fields">
		<p><label for="mdjm_enquiry_source"><?php _e( 'Enquiry Source:', 'mobile-dj-manager' ); ?></label><br />
        <?php echo MDJM()->html->enquiry_source_dropdown(
			'mdjm_enquiry_source',
			$enquiry_source ? $enquiry_source->term_id : ''
		); ?></p>
    </div>
	<?php
} // mdjm_event_metabox_admin_enquiry_source_row
add_action( 'mdjm_event_admin_fields', 'mdjm_event_metabox_admin_enquiry_source_row', 10 );

/**
 * Output the employee setup row
 *
 * @since	1.3.7
 * @global	obj		$mdjm_event			MDJM_Event class object
 * @global	bool	$mdjm_event_update	True if this event is being updated, false if new.
 * @param	int		$event_id			The event ID.
 * @return	str
 */
function mdjm_event_metabox_admin_dj_setup_row( $event_id )	{

	global $mdjm_event, $mdjm_event_update;

	mdjm_insert_datepicker(
		array(
			'id'       => 'dj_setup_date',
			'altfield' => '_mdjm_event_djsetup'
		)
	);

	$setup_date = $mdjm_event->get_setup_date();
	$setup_time = $mdjm_event->get_setup_time();
	$format = mdjm_get_option( 'time_format', 'H:i' );

	?>
	<div id="mdjm-event-employee-setup-row" class="mdjm_form_fields">
		<p><label for="dj_setup_date"><?php printf( __( '%s Setup:', 'mobile-dj-manager' ), mdjm_get_option( 'artist' ) ); ?></label><br />
        <?php echo MDJM()->html->text( array(
			'name'  => 'dj_setup_date',
			'class' => 'mdjm_setup_date',
			'value' => $setup_date ? mdjm_format_short_date( $setup_date ) : ''
		) ); ?>
        
        <?php echo MDJM()->html->hidden( array(
			'name'  => '_mdjm_event_djsetup',
			'value' => $setup_date ? $setup_date : ''
		) ); ?>

        <?php echo MDJM()->html->time_hour_select( array(
			'name'     => 'dj_setup_hr',
			'selected' => ! empty( $setup_time ) ? date( $format[0], strtotime( $setup_time ) ) : ''
		) ); ?> 
		<?php echo MDJM()->html->time_minute_select( array(
			'name'     => 'dj_setup_min',
			'selected' => ! empty( $setup_time ) ? date( $format[2], strtotime( $setup_time ) ) : ''
		) ); ?> 
		<?php if ( 'H:i' != $format ) : ?>
			<?php echo MDJM()->html->time_period_select( array(
				'name'     => 'dj_setup_period',
				'selected' => ! empty( $setup_time ) ? date( 'A', strtotime( $setup_time ) ) : ''
			) ); ?>
		<?php endif; ?></p>

    </div>
	<?php
} // mdjm_event_metabox_admin_dj_setup_row
add_action( 'mdjm_event_admin_fields', 'mdjm_event_metabox_admin_dj_setup_row', 20 );

/**
 * Output the employee notes row
 *
 * @since	1.3.7
 * @global	obj		$mdjm_event			MDJM_Event class object
 * @global	bool	$mdjm_event_update	True if this event is being updated, false if new.
 * @param	int		$event_id			The event ID.
 * @return	str
 */
function mdjm_event_metabox_admin_employee_notes_row( $event_id )	{

	global $mdjm_event, $mdjm_event_update;

	?>
	<div id="mdjm-event-employee-notes-row" class="mdjm_form_fields">
		<p><?php echo MDJM()->html->textarea( array(
			'label'       => sprintf( __( '%s Notes:', 'mobile-dj-manager' ), mdjm_get_option( 'artist' ) ),
			'name'        => '_mdjm_event_dj_notes',
			'placeholder' => __( 'This information is not visible to clients', 'mobile-dj-manager' ),
			'value'       => get_post_meta( $event_id, '_mdjm_event_dj_notes', true )
		) ); ?></p>
    </div>

	<?php
} // mdjm_event_metabox_admin_employee_notes_row
add_action( 'mdjm_event_admin_fields', 'mdjm_event_metabox_admin_employee_notes_row', 30 );

/**
 * Output the admin notes row
 *
 * @since	1.3.7
 * @global	obj		$mdjm_event			MDJM_Event class object
 * @global	bool	$mdjm_event_update	True if this event is being updated, false if new.
 * @param	int		$event_id			The event ID.
 * @return	str
 */
function mdjm_event_metabox_admin_notes_row( $event_id )	{

	global $mdjm_event, $mdjm_event_update;

	if ( ! mdjm_is_admin() )	{
		return;
	}

	?>
	<div id="mdjm-event-admin-notes-row" class="mdjm_form_fields">
		<p><?php echo MDJM()->html->textarea( array(
			'label'       => __( 'Admin Notes:', 'mobile-dj-manager' ),
			'name'        => '_mdjm_event_admin_notes',
			'placeholder' => __( 'This information is only visible to admins', 'mobile-dj-manager' ),
			'value'       => get_post_meta( $event_id, '_mdjm_event_admin_notes', true )
		) ); ?></p>
    </div>

	<?php
} // mdjm_event_metabox_admin_notes_row
add_action( 'mdjm_event_admin_fields', 'mdjm_event_metabox_admin_notes_row', 40 );

/**
 * Output the event journal table
 *
 * @since	1.3.7
 * @global	obj		$mdjm_event			MDJM_Event class object
 * @global	bool	$mdjm_event_update	True if this event is being updated, false if new.
 * @param	int		$event_id			The event ID.
 * @return	str
 */
function mdjm_event_metabox_history_journal_table( $event_id )	{

	global $mdjm_event, $mdjm_event_update;

	$journals = mdjm_get_journal_entries( $event_id );

	$count  = count( $journals );
	$i      = 0;

	?>
	<div id="mdjm-event-journal-table">
    	<strong><?php _e( 'Recent Journal Entries', 'mobile-dj-manager' ); ?></strong> 
        <table class="widefat mdjm_event_journal_table mdjm_form_fields">
        	<thead>
                <tr>
                	<th style="width: 20%"><?php _e( 'Date', 'mobile-dj-manager' ); ?></th>
                    <th><?php _e( 'Excerpt', 'mobile-dj-manager' ); ?></th>
                </tr>
            </thead>

			<tbody>
            	<?php if ( $journals ) : ?>
                	<?php foreach( $journals as $journal ) : ?>
                        <tr>
                            <td><a href="<?php echo get_edit_comment_link( $journal->comment_ID ); ?>"><?php echo date( mdjm_get_option( 'time_format' ) . ' ' . mdjm_get_option( 'short_date_format' ), strtotime( $journal->comment_date ) ); ?></a></td>
                            <td><?php echo substr( $journal->comment_content, 0, 250 ); ?></td>
                        </tr>
						<?php $i++; ?>
                        
                        <?php if ( $i >= 3 ) break; ?>
                        
                    <?php endforeach; ?>
				<?php else : ?>
                <tr>
                    <td colspan="2"><?php printf( __( 'There are no journal entries associated with this %s', 'mobile-dj-manager' ), mdjm_get_label_singular( true ) ); ?></td>
                </tr>
                <?php endif; ?>

            </tbody>

			<?php if ( $journals ) : ?>
                <tfoot>
                	<tr>
                    	<td colspan="2"><span class="description">(<?php printf( __( 'Displaying the most recent %d entries of <a href="%s">%d total', 'mobile-dj-manager' ), ( $count >= 3 ) ? 3 : $count, add_query_arg( array( 'p' => $event_id ), admin_url( 'edit-comments.php?p=5636' ) ), $count ); ?>)</span></td>
                    </tr>
                </tfoot>
            <?php endif; ?>

        </table>
    </div>
	<?php
} // mdjm_event_metabox_history_journal_table
add_action( 'mdjm_event_history_fields', 'mdjm_event_metabox_history_journal_table', 10 );

/**
 * Output the event emails table
 *
 * @since	1.3.7
 * @global	obj		$mdjm_event			MDJM_Event class object
 * @global	bool	$mdjm_event_update	True if this event is being updated, false if new.
 * @param	int		$event_id			The event ID.
 * @return	str
 */
function mdjm_event_metabox_history_emails_table( $event_id )	{

	global $mdjm_event, $mdjm_event_update;

	if ( ! mdjm_get_option( 'track_client_emails' ) )	{
		return;
	}

	$emails = mdjm_event_get_emails( $event_id );
	$count  = count( $emails );
	$i      = 0;

	?>
	<div id="mdjm-event-emails-table">
    	<strong><?php _e( 'Associated Emails', 'mobile-dj-manager' ); ?></strong> 
        <table class="widefat mdjm_event_emails_table mdjm_form_fields">
        	<thead>
                <tr>
                	<th><?php _e( 'Date', 'mobile-dj-manager' ); ?></th>
                    <th><?php _e( 'Subject', 'mobile-dj-manager' ); ?></th>
                    <th><?php _e( 'Status', 'mobile-dj-manager' ); ?></th>
                </tr>
            </thead>

			<tbody>
            	<?php if ( $emails ) : ?>
                	<?php foreach( $emails as $email ) : ?>
                        <tr>
                            <td><?php echo date( mdjm_get_option( 'time_format' ) . ' ' . mdjm_get_option( 'short_date_format' ), strtotime( $email->post_date ) ); ?></td>
                            <td><a href="<?php echo get_edit_post_link( $email->ID ); ?>"><?php echo get_the_title( $email->ID ); ?></a></td>
                            <td><?php
                            echo get_post_status_object( $email->post_status )->label;
             
                            if ( ! empty( $email->post_modified ) && 'opened' == $email->post_status )	: ?>
                                <?php echo '<br />'; ?>
                                <span class="description"><?php echo date( mdjm_get_option( 'time_format', 'H:i' ) . ' ' . mdjm_get_option( 'short_date_format', 'd/m/Y' ), strtotime( $email->post_modified ) ); ?></span>
                            <?php endif; ?></td>
                        </tr>
						<?php $i++; ?>
                        
                        <?php if ( $i >= 3 ) break; ?>
                        
                    <?php endforeach; ?>
				<?php else : ?>
                <tr>
                    <td colspan="3"><?php printf( __( 'There are no emails associated with this %s', 'mobile-dj-manager' ), mdjm_get_label_singular( true ) ); ?></td>
                </tr>
                <?php endif; ?>

            </tbody>
            
            <?php if ( $emails ) : ?>
                <tfoot>
                	<tr>
                    	<td colspan="3"><span class="description">(<?php printf( __( 'Displaying the most recent %d emails of %d total', 'mobile-dj-manager' ), ( $count >= 3 ) ? 3 : $count, $count ); ?>)</span></td>
                    </tr>
                </tfoot>
            <?php endif; ?>
            
        </table>
    </div>
	<?php
} // mdjm_event_metabox_emails_table
add_action( 'mdjm_event_history_fields', 'mdjm_event_metabox_history_emails_table', 20 );
