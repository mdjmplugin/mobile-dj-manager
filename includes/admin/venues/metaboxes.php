<?php

/**
 * Contains all metabox functions for the mdjm-venue post type
 *
 * @package		MDJM
 * @subpackage	Venues
 * @since		1.3
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) )
	exit;

/**
 * Define and add the metaboxes for the mdjm-venue post type.
 * Apply the `mdjm_venue_add_metaboxes` filter to allow for filtering of metaboxes and settings.
 * Uses function_exists to verify the callback function exists.
 *
 * @since	1.3
 * @param
 * @return
 */
function mdjm_add_venue_meta_boxes( $post )	{
	$metaboxes = apply_filters(
		'mdjm_venue_add_metaboxes',
		array(
			array(
				'id'		  => 'mdjm-venue-details',
				'title'	   => __( 'Venue Details', 'mobile-dj-manager' ),
				'callback'	=> 'mdjm_venue_details_metabox',
				'context'	 => 'normal',
				'priority'	=> 'high',
				'args'		=> array(),
				'dependancy'  => '',
				'permission'  => ''
			)
		)
	);
	// Runs before metabox output
	do_action( 'mdjm_venue_before_metaboxes' );
	
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
			'mdjm-venue',
			$metabox['context'],
			$metabox['priority'],
			$metabox['args']
		);
	}
	
	// Runs after metabox output
	do_action( 'mdjm_venue_after_metaboxes' );
} // mdjm_add_venue_meta_boxes
add_action( 'add_meta_boxes_mdjm-venue', 'mdjm_add_venue_meta_boxes' );

/**
 * Output for the Venue Details meta box.
 *
 * @since	1.3
 * @param	obj		$post		The post object (WP_Post).
 * @return
 */
function mdjm_venue_details_metabox( $post )	{
	
	do_action( 'mdjm_pre_venue_details_metabox', $post );
	
	wp_nonce_field( basename( __FILE__ ), 'mdjm-venue' . '_nonce' );
		
	?>
    <script type="text/javascript">
	document.getElementById("title").className += " required";
	</script>
	<input type="hidden" name="mdjm_update_custom_post" id="mdjm_update_custom_post" value="mdjm_update" />
	<!-- Start first row -->
	<div class="mdjm-post-row-single">
		<div class="mdjm-post-1column">
			<label for="venue_contact" class="mdjm-label"><strong><?php _e( 'Contact Name: ', 'mobile-dj-manager' ); ?></strong></label><br />
			<input type="text" name="venue_contact" id="venue_contact" class="regular-text" value="<?php echo esc_attr( get_post_meta( $post->ID, '_venue_contact', true ) ); ?>">
		</div>
	</div>
	<!-- End first row -->
	<!-- Start second row -->
	<div class="mdjm-post-row-single">
		<div class="mdjm-post-1column">
			<label for="venue_phone" class="mdjm-label"><strong><?php _e( 'Contact Phone:', 'mobile-dj-manager' ); ?></strong></label><br />
			<input type="text" name="venue_phone" id="venue_phone" class="regular-text" value="<?php echo esc_attr( get_post_meta( $post->ID, '_venue_phone', true ) ); ?>" />
		</div>
	</div>
	<!-- End second row -->
	<!-- Start third row -->
	<div class="mdjm-post-row-single">
		<div class="mdjm-post-1column">
			<label for="venue_email" class="mdjm-label"><strong><?php _e( 'Contact Email: ', 'mobile-dj-manager' ); ?></strong></label><br />
			<input type="text" name="venue_email" id="venue_email" class="regular-text" value="<?php echo esc_attr( get_post_meta( $post->ID, '_venue_email', true ) ); ?>">
		</div>
	</div>
	<!-- End third row -->
	<!-- Start fourth row -->
	<div class="mdjm-post-row">
		<!-- Start first coloumn -->
		<div class="mdjm-post-2column">
			<label for="venue_address1" class="mdjm-label"><strong><?php _e( 'Address Line 1:', 'mobile-dj-manager' ); ?></strong></label><br />
			<input type="text" name="venue_address1" id="venue_address1" class="regular-text required" value="<?php echo esc_attr( get_post_meta( $post->ID, '_venue_address1', true ) ); ?>" />
		</div>
		<!-- End first coloumn -->
		<!-- Start second coloumn -->
		<div class="mdjm-post-last-2column">
			<label for="venue_address2" class="mdjm-label"><strong><?php _e( 'Address Line 2:', 'mobile-dj-manager' ); ?></strong></label><br />
			<input type="text" name="venue_address2" id="venue_address2" class="regular-text" value="<?php echo esc_attr( get_post_meta( $post->ID, '_venue_address2', true ) ); ?>" />
		</div>
		<!-- End second coloumn -->
	</div>
	<!-- End fourth row -->
	<!-- Start fifth row -->
	<div class="mdjm-post-row">
		<!-- Start first coloumn -->
		<div class="mdjm-post-2column">
			<label for="venue_town" class="mdjm-label"><strong><?php _e( 'Town:', 'mobile-dj-manager' ); ?></strong></label><br />
			<input type="text" name="venue_town" id="venue_town" class="regular-text required" value="<?php echo esc_attr( get_post_meta( $post->ID, '_venue_town', true ) ); ?>" />
		</div>
		<!-- End first coloumn -->
		<!-- Start second coloumn -->
		<div class="mdjm-post-last-2column">
			<label for="venue_county" class="mdjm-label"><strong><?php _e( 'County:', 'mobile-dj-manager' ); ?></strong></label><br />
			<input type="text" name="venue_county" id="venue_county" class="regular-text required" value="<?php echo esc_attr( get_post_meta( $post->ID, '_venue_county', true ) ); ?>" />
		</div>
		<!-- End second coloumn -->
	</div>
	<!-- End fifth row -->
	<!-- Start sixth row -->
	<div class="mdjm-post-row-single">
		<div class="mdjm-post-1column">
			<label for="venue_postcode" class="mdjm-label"><strong><?php _e( 'Post Code:', 'mobile-dj-manager' ); ?></strong></label><br />
			<input type="text" name="venue_postcode" id="venue_postcode" class="regular-text" value="<?php echo esc_attr( get_post_meta( $post->ID, '_venue_postcode', true ) ); ?>" />
		</div>
	</div>
	<!-- End sixth row -->
	<!-- Start seventh row -->
	<div class="mdjm-post-row-single-textarea">
		<div class="mdjm-post-1column">
			<label for="venue_information" class="mdjm-label"><strong><?php _e( 'General Information:', 'mobile-dj-manager' ); ?></strong></label><br />
			<textarea name="venue_information" id="venue_information" class="widefat" cols="30" rows="3" placeholder="Enter any information you feel relevant for the venue here. Consider adding or selecting venue details where possible via the 'Venue Details' side box"><?php echo esc_attr( get_post_meta( $post->ID, '_venue_information', true ) ); ?></textarea>
		</div>
	</div>
	<!-- End seventh row -->
    <?php
	
	do_action( 'mdjm_post_venue_details_metabox', $post );
	
} // mdjm_venue_details_metabox
