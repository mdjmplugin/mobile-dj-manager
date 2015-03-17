<?php

/*
 * Meta box layout and data
 */
	/*
	 * mdjm_venue_post_main_metabox
	 * The layout and data for the venue details meta box
	 *
	 * @since 1.1.2
	 * @params: $post => array
	 */
	function mdjm_venue_post_main_metabox( $post )	{
		wp_nonce_field( basename( __FILE__ ), MDJM_VENUE_POSTS . '_nonce' );
		?>
        <p><label for="venue_contact"><strong><?php _e( 'Contact Name: ' ); ?></strong></label><br />
        <input type="text" name="venue_contact" id="venue_contact" class="regular-text" value="<?php echo esc_attr( get_post_meta( $post->ID, 'venue_contact', true ) ); ?>"></p>
        <p><label for="venue_phone"><strong><?php _e( 'Contact Phone:' ); ?></strong></label><br />
        <input type="text" id="venue_phone" class="regular-text" name="venue" value="<?php echo esc_attr( get_post_meta( $post->ID, 'venue_phone', true ) ); ?>" /></p>
        <p><label for="venue_email"><strong><?php _e( 'Contact Name: ' ); ?></strong></label><br />
        <input type="text" name="venue_email" id="venue_email" class="regular-text" value="<?php echo esc_attr( get_post_meta( $post->ID, 'venue_email', true ) ); ?>"></p>
        <p><label for="venue_address1"><strong><?php _e( 'Address Line 1:' ); ?></strong></label><br />
        <input type="text" name="venue_address1" id="venue_address1" class="regular-text" value="<?php echo esc_attr( get_post_meta( $post->ID, 'venue_address1', true ) ); ?>" /></p>
        <p><label for="venue_address2"><strong><?php _e( 'Address Line 2:' ); ?></strong></label><br />
        <input type="text" name="venue_address2" id="venue_address2" class="regular-text" value="<?php echo esc_attr( get_post_meta( $post->ID, 'venue_address2', true ) ); ?>" /></p>
        <p><label for="venue_town"><strong><?php _e( 'Town:' ); ?></strong></label><br />
        <input type="text" name="venue_town" id="venue_town" class="regular-text" value="<?php echo esc_attr( get_post_meta( $post->ID, 'venue_town', true ) ); ?>" /></p>
        <p><label for="venue_county"><strong><?php _e( 'County:' ); ?></strong></label><br />
        <input type="text" name="venue_county" id="venue_county" class="regular-text" value="<?php echo esc_attr( get_post_meta( $post->ID, 'venue_county', true ) ); ?>" /></p>
        <p><label for="venue_postcode"><strong><?php _e( 'Post Code:' ); ?></strong></label><br />
        <input type="text" name="venue_postcode" id="venue_postcode" class="regular-text" value="<?php echo esc_attr( get_post_meta( $post->ID, 'venue_postcode', true ) ); ?>" /></p>
        <p><label for="venue_info"><strong><?php _e( 'Information:' ); ?></strong></label><br />
        <textarea name="venue_info" id="venue_info" class="widefat" cols="30" rows="5"><?php echo esc_attr( get_post_meta( $post->ID, 'venue_info', true ) ); ?></textarea></p>
        <?php
	} // mdjm_venue_post_main_metabox

/*
 * Post Save Actions
 */
 	/*
	 * mdjm_venue_post_main_metabox_save
	 * The save actions for the venue details meta box
	 *
	 * @since 1.1.2
	 * @params: $post_id, $post => array
	 */
 	add_action( 'edit_post', 'mdjm_venue_post_main_metabox_save', 10, 2 );
	function mdjm_venue_post_main_metabox_save( $post_id, $post )	{
		error_log( 'Got here' );
		/* -- Security Verification via nonce -- */
		if( !isset( $_POST[MDJM_VENUE_POSTS . '_nonce'] ) || !wp_verify_nonce( $_POST[MDJM_VENUE_POSTS . '_nonce'], basename( __FILE__ ) ) )
			return $post_id;
		
		/* -- Do not save if this is an autosave -- */
		if( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}
			
		/* -- Permission Check -- */
		if( !current_user_can( 'administrator' ) && !dj_can( 'add_venue' ) )
			return $post_id;
			
		/* -- Loop through all fields sanitizing and updating as required -- */	
		foreach( $_POST as $meta_key => $new_meta_value )	{
			/* -- We're only interested in 'venue_' fields -- */
			if( substr( $meta_key, 0, 6 ) == 'venue_' )	{
				$current_meta_value = get_post_meta( $post_id, $meta_key, true );
				
				if( $meta_key == 'venue_email' )
					$meta_key = sanitize_email( $meta_key );
					
				else
					$meta_key = sanitize_text_field( $meta_key );
				
				/* -- If we have a value and the key did not exist previously, add it -- */
				if ( $new_meta_value && '' == $current_meta_value )
					add_post_meta( $post_id, $meta_key, $new_meta_value, true );
				
				/* -- If a value existed, but has changed, update it -- */
				elseif ( $new_meta_value && $new_meta_value != $current_meta_value )
					update_post_meta( $post_id, $meta_key, $new_meta_value );
					
				/* If there is no new meta value but an old value exists, delete it. */
				elseif ( '' == $new_meta_value && $current_meta_value )
					delete_post_meta( $post_id, $meta_key, $meta_value );
			}
		}
	} // mdjm_venue_post_main_metabox_save
?>