<?php

/**
 * Contains all metabox functions for the mdjm-venue post type
 *
 * @package		MDJM
 * @subpackage	Templates
 * @since		1.3
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) )
	exit;

/**
 * Define and add the metaboxes for the contract post type.
 * Apply the `mdjm_contract_add_metaboxes` filter to allow for filtering of metaboxes and settings.
 * Uses function_exists to verify the callback function exists.
 *
 * @since	1.3
 * @param
 * @return
 */
function mdjm_add_contract_meta_boxes( $post )	{
	$metaboxes = apply_filters(
		'mdjm_contract_add_metaboxes',
		array(
			array(
				'id'			=> 'mdjm-contract-details',
				'title'			=> sprintf( __( 'Contract Details', 'mobile-dj-manager' ), get_post_type_object( 'contract' )->labels->singular_name ),
				'callback'		=> 'mdjm_contract_details_metabox',
				'context'		=> 'side',
				'priority'		=> 'default',
				'args'			=> array(),
				'dependancy'	=> '',
				'permission'	=> ''
			)
		)
	);
	// Runs before metabox output
	do_action( 'mdjm_contract_before_metaboxes' );
	
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
			'contract',
			$metabox['context'],
			$metabox['priority'],
			$metabox['args']
		);
	}
	
	// Runs after metabox output
	do_action( 'mdjm_contract_after_metaboxes' );
	
} // mdjm_add_communication_meta_boxes
add_action( 'add_meta_boxes_contract', 'mdjm_add_contract_meta_boxes' );

/**
 * Output for the Contract Details meta box.
 *
 * @since	1.3
 * @param	obj		$post		The post object (WP_Post).
 * @return
 */
function mdjm_contract_details_metabox( $post )	{
	
	do_action( 'mdjm_pre_contract_details_metabox', $post );
	
	wp_nonce_field( basename( __FILE__ ), 'mdjm-contract' . '_nonce' );
	
	$contract_events = get_posts(
		array(
			'post_type'			=> 'mdjm-event',
			'posts_per_page'	=> -1,
			'meta_key'			=> '_mdjm_event_contract',
			'meta_value_num'	=> $post->ID,
			'post_status'		=> 'any'
		)
	);

	$event_count = count( $contract_events );
		
	$total_events = sprintf( _n( ' %s', ' %s', $event_count, 'mobile-dj-manager' ),
						mdjm_get_label_singular(), mdjm_get_label_plural()
					);
	
	$default_contract = mdjm_get_option( 'default_contract' ) == $post->ID ? __( 'Yes', 'mobile-dj-manager' ) : __( 'No', 'mobile-dj-manager' );
			
	?>
	<script type="text/javascript">
	document.getElementById("title").className += " required";
	document.getElementById("content").className += " required";
	</script>
	
	<p><?php printf( __( '<strong>Author</strong>: <a href="%s">%s</a>', 'mobile-dj-manager' ),
				admin_url( "user-edit.php?user_id={$post->post_author}" ),
				get_the_author_meta( 'display_name', $post->post_author ) ); ?>
	</p>
	
	<p><?php _e( '<strong>Default</strong>?', 'mobile-dj-manager' );
		echo ' ' . $default_contract; ?>
    </p>
	
	<p><?php _e( '<strong>Assigned To</strong>: ', 'mobile-dj-manager' );
				printf( _n( $event_count . ' %1$s', $event_count . ' %2$s', $event_count, 'mobile-dj-manager' ),
						mdjm_get_label_singular(), mdjm_get_label_plural() ); ?>
    </p>
	
	<p><?php _e( '<strong>Description</strong>: <span class="description">(optional)</span>', 'mobile-dj-manager' ); ?>
    	<br />
        <input type="hidden" name="mdjm_update_custom_post" id="mdjm_update_custom_post" value="mdjm_update" />
        <textarea name="contract_description" id="contract_description" class="widefat" rows="5" placeholder="<?php _e( 'i.e To be used for Pubs/Clubs', 'mobile-dj-manager' ); ?>"><?php echo esc_attr( get_post_meta( $post->ID, '_contract_description', true ) ); ?></textarea>
    </p>
	
	<?php
	
	do_action( 'mdjm_post_contract_details_metabox', $post );
	
} // mdjm_contract_details_metabox
