<?php
/*
 * Meta box layout and data
 */
/* -- Communication Post Meta Boxes -- */
 	/*
	 * mdjm_communication_post_details_metabox
	 * The layout and data for the email details meta box
	 *
	 * @since 1.1.2
	 * @params: $post => array
	 */
	function mdjm_communication_post_details_metabox( $post )	{
		global $mdjm_options;
		
		$from = get_userdata( $post->post_author );
		$recipient = get_userdata( get_post_meta( $post->ID, '_recipient', true ) );
		
		?>
        <p><strong>Date</strong>: <?php echo date( $mdjm_options['time_format'] . ' ' . $mdjm_options['short_date_format'], get_post_meta( $post->ID, '_date_sent', true ) ); ?></p>
        <p><strong>From</strong>: <a href="<?php echo admin_url( '/user-edit.php?user_id=' . $from->ID ); ?>"><?php echo $from->display_name; ?></a></p>
        <p><strong>Recipient</strong>: <a href="<?php echo admin_url( '/user-edit.php?user_id=' . $recipient->ID ); ?>"><?php echo $recipient->display_name; ?></a></p>
        <p><strong>Status</strong>: <?php echo ucfirst( $post->post_status ); ?></p>
        <p><strong>Event</strong>: <a href="<?php f_mdjm_admin_page( 'events' ); ?>&action=view_event_form&event_id=<?php echo get_post_meta( $post->ID, '_event', true ); ?>"><?php echo $mdjm_options['id_prefix'] . stripslashes( get_post_meta( $post->ID, '_event', true ) ); ?></a></p>
        
        <a class="button-secondary" href="<?php echo $_SERVER['HTTP_REFERER']; ?>" title="<?php _e( 'Back to List' ); ?>"><?php _e( 'Back' ); ?></a>
        
        <?php
	} // comm_post_details_metabox
	
	/*
	 * mdjm_communication_post_output_metabox
	 * Print out the email content within the Email Content meta box
	 *
	 * @since 1.1.2
	 * @params: $post => array
	 */
	function mdjm_communication_post_output_metabox( $post )	{
		echo $post->post_content;
	} // mdjm_communication_post_output_metabox

/* -- Contract Post Meta Boxes -- */
 	/*
	 * contract_post_details_metabox
	 * The layout and data for contract details meta box
	 *
	 * @since 1.1.2
	 * @params: $post => array
	 */
	function contract_post_details_metabox( $post )	{
		global $wpdb, $db_tables, $mdjm_options;
		$count = $wpdb->get_var( "SELECT COUNT(*) FROM `" . $db_tables['events'] . "` WHERE `contract` = '" . $post->ID . "'" );
		
		wp_nonce_field( basename( __FILE__ ), MDJM_CONTRACT_POSTS . '_nonce' );
		
		?>
        <p><strong>Author</strong>: <?php echo sprintf( '<a href="' . admin_url( 'user-edit.php?user_id=%s' ) . '">%s</a>', $post->post_author, the_author_meta( 'display_name', $post->post_author ) ); ?></p>
        <p><strong>Default Contract</strong>: <?php echo $post->ID == $mdjm_options['default_contract'] ? 'Yes' : 'No'; ?></p>
        <p><strong>Assigned to</strong>: <?php echo $count . _n( ' Event', ' Events', $count ); ?></p>
        
        <p><strong>Description</strong>: <span class="description">(optional)</span><br />
        <input type="hidden" name="mdjm_update_custom_post" id="mdjm_update_custom_post" value="mdjm_update" />
        <textarea name="contract_description" id="contract_description" class="widefat" rows="5" placeholder="i.e To be used for Pubs/Clubs"><?php echo esc_attr( get_post_meta( $post->ID, '_contract_description', true ) ); ?></textarea></p>
        
        <?php
	} // contract_post_details_metabox
	
/* -- Venue Post Meta Boxes -- */
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
        <input type="hidden" name="mdjm_update_custom_post" id="mdjm_update_custom_post" value="mdjm_update" />
        <p><label for="venue_contact"><strong><?php _e( 'Contact Name: ' ); ?></strong></label><br />
        <input type="text" name="venue_contact" id="venue_contact" class="regular-text" value="<?php echo esc_attr( get_post_meta( $post->ID, '_venue_contact', true ) ); ?>"></p>
        <p><label for="venue_phone"><strong><?php _e( 'Contact Phone:' ); ?></strong></label><br />
        <input type="text" name="venue_phone" id="venue_phone" class="regular-text" value="<?php echo esc_attr( get_post_meta( $post->ID, '_venue_phone', true ) ); ?>" /></p>
        <p><label for="venue_email"><strong><?php _e( 'Contact Email: ' ); ?></strong></label><br />
        <input type="text" name="venue_email" id="venue_email" class="regular-text" value="<?php echo esc_attr( get_post_meta( $post->ID, '_venue_email', true ) ); ?>"></p>
        <p><label for="venue_address1"><strong><?php _e( 'Address Line 1:' ); ?></strong></label><br />
        <input type="text" name="venue_address1" id="venue_address1" class="regular-text" value="<?php echo esc_attr( get_post_meta( $post->ID, '_venue_address1', true ) ); ?>" /></p>
        <p><label for="venue_address2"><strong><?php _e( 'Address Line 2:' ); ?></strong></label><br />
        <input type="text" name="venue_address2" id="venue_address2" class="regular-text" value="<?php echo esc_attr( get_post_meta( $post->ID, '_venue_address2', true ) ); ?>" /></p>
        <p><label for="venue_town"><strong><?php _e( 'Town:' ); ?></strong></label><br />
        <input type="text" name="venue_town" id="venue_town" class="regular-text" value="<?php echo esc_attr( get_post_meta( $post->ID, '_venue_town', true ) ); ?>" /></p>
        <p><label for="venue_county"><strong><?php _e( 'County:' ); ?></strong></label><br />
        <input type="text" name="venue_county" id="venue_county" class="regular-text" value="<?php echo esc_attr( get_post_meta( $post->ID, '_venue_county', true ) ); ?>" /></p>
        <p><label for="venue_postcode"><strong><?php _e( 'Post Code:' ); ?></strong></label><br />
        <input type="text" name="venue_postcode" id="venue_postcode" class="regular-text" value="<?php echo esc_attr( get_post_meta( $post->ID, '_venue_postcode', true ) ); ?>" /></p>
        <p><label for="venue_information"><strong><?php _e( 'General Information:' ); ?></strong></label><br />
        <textarea name="venue_information" id="venue_information" class="widefat" cols="30" rows="5" placeholder="Enter any information you feel relevant for the venue here. Consider adding or selecting venue details where possible via the 'Venue Details' side box"><?php echo esc_attr( get_post_meta( $post->ID, '_venue_information', true ) ); ?></textarea></p>
        <?php
	} // mdjm_venue_post_main_metabox
	
/*
 * Post Save Actions
 */
/* -- Custom Post Save Actions -- */
 	/*
	 * mdjm_custom_post_metabox_save
	 * The save actions for the custom post meta boxes
	 *
	 * @since 1.1.2
	 * @params: $post_id, $post => array
	 */
	function mdjm_custom_post_metabox_save( $post_id, $post )	{
		global $mdjm_post_types;
		/* -- Only process for custom post types -- */
		if( !in_array( $post->post_type, $mdjm_post_types ) )	{
			error_log( 'no array' );
			return;
		}
				
		/* -- Security Verification via nonce -- */
		/*if( !isset( $_POST[$post->post_type . '_nonce'] ) || !wp_verify_nonce( $_POST[$post->post_type . '_nonce'], basename( __FILE__ ) ) )	{
			return $post_id;
		}*/
		
		/* -- Do not save if this is an autosave -- */
		if( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}
		switch( $post->post_type )	{
		/* -- Contract Post Saves -- */
			case MDJM_CONTRACT_POSTS:
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
		} // switch
	} // mdjm_custom_post_metabox_save
?>