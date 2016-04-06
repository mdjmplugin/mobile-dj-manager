<?php
/*
 * Meta box layout, data & save actions
 */
	
/* -- Contract Post Meta Boxes -- */
 	/*
	 * contract_post_details_metabox
	 * The layout and data for contract details meta box
	 *
	 * @since 1.1.2
	 * @params: $post => array
	 */
	function contract_post_details_metabox( $post )	{
		global $mdjm_settings;
		
		$contract_events = get_posts(
								array(
									'post_type'		=> MDJM_EVENT_POSTS,
									'posts_per_page'   => -1,
									'meta_key'	 	 => '_mdjm_event_contract',
									'meta_value'   	   => $post->ID,
									'post_status'  	  => 'any',
									)
								);
				
		$count = count( $contract_events );
		
		wp_nonce_field( basename( __FILE__ ), MDJM_CONTRACT_POSTS . '_nonce' );
		
		?>
        <script type="text/javascript">
		document.getElementById("title").className += " required";
		document.getElementById("content").className += " required";
		</script>
        <p><strong>Author</strong>: <?php echo sprintf( '<a href="' . admin_url( 'user-edit.php?user_id=%s' ) . '">%s</a>', $post->post_author, the_author_meta( 'display_name', $post->post_author ) ); ?></p>
        <p><strong>Default Contract</strong>: <?php echo $post->ID == $mdjm_settings['events']['default_contract'] ? 'Yes' : 'No'; ?></p>
        <p><strong>Assigned To</strong>: <?php echo $count . _n( ' Event', ' Events', $count ); ?></p>
        
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
        <script type="text/javascript">
		document.getElementById("title").className += " required";
		</script>
        <input type="hidden" name="mdjm_update_custom_post" id="mdjm_update_custom_post" value="mdjm_update" />
        <!-- Start first row -->
        <div class="mdjm-post-row-single">
        	<div class="mdjm-post-1column">
                <label for="venue_contact" class="mdjm-label"><strong><?php _e( 'Contact Name: ' ); ?></strong></label><br />
                <input type="text" name="venue_contact" id="venue_contact" class="regular-text" value="<?php echo esc_attr( get_post_meta( $post->ID, '_venue_contact', true ) ); ?>">
            </div>
        </div>
        <!-- End first row -->
        <!-- Start second row -->
        <div class="mdjm-post-row-single">
        	<div class="mdjm-post-1column">
                <label for="venue_phone" class="mdjm-label"><strong><?php _e( 'Contact Phone:' ); ?></strong></label><br />
                <input type="text" name="venue_phone" id="venue_phone" class="regular-text" value="<?php echo esc_attr( get_post_meta( $post->ID, '_venue_phone', true ) ); ?>" />
            </div>
        </div>
        <!-- End second row -->
        <!-- Start third row -->
        <div class="mdjm-post-row-single">
        	<div class="mdjm-post-1column">
                <label for="venue_email" class="mdjm-label"><strong><?php _e( 'Contact Email: ' ); ?></strong></label><br />
                <input type="text" name="venue_email" id="venue_email" class="regular-text" value="<?php echo esc_attr( get_post_meta( $post->ID, '_venue_email', true ) ); ?>">
            </div>
        </div>
        <!-- End third row -->
        <!-- Start fourth row -->
        <div class="mdjm-post-row">
            <!-- Start first coloumn -->
        	<div class="mdjm-post-2column">
                <label for="venue_address1" class="mdjm-label"><strong><?php _e( 'Address Line 1:' ); ?></strong></label><br />
                <input type="text" name="venue_address1" id="venue_address1" class="regular-text required" value="<?php echo esc_attr( get_post_meta( $post->ID, '_venue_address1', true ) ); ?>" />
            </div>
            <!-- End first coloumn -->
            <!-- Start second coloumn -->
            <div class="mdjm-post-last-2column">
                <label for="venue_address2" class="mdjm-label"><strong><?php _e( 'Address Line 2:' ); ?></strong></label><br />
                <input type="text" name="venue_address2" id="venue_address2" class="regular-text" value="<?php echo esc_attr( get_post_meta( $post->ID, '_venue_address2', true ) ); ?>" />
            </div>
            <!-- End second coloumn -->
        </div>
        <!-- End fourth row -->
        <!-- Start fifth row -->
        <div class="mdjm-post-row">
            <!-- Start first coloumn -->
        	<div class="mdjm-post-2column">
                <label for="venue_town" class="mdjm-label"><strong><?php _e( 'Town:' ); ?></strong></label><br />
                <input type="text" name="venue_town" id="venue_town" class="regular-text required" value="<?php echo esc_attr( get_post_meta( $post->ID, '_venue_town', true ) ); ?>" />
            </div>
            <!-- End first coloumn -->
            <!-- Start second coloumn -->
            <div class="mdjm-post-last-2column">
                <label for="venue_county" class="mdjm-label"><strong><?php _e( 'County:' ); ?></strong></label><br />
                <input type="text" name="venue_county" id="venue_county" class="regular-text required" value="<?php echo esc_attr( get_post_meta( $post->ID, '_venue_county', true ) ); ?>" />
            </div>
            <!-- End second coloumn -->
        </div>
        <!-- End fifth row -->
        <!-- Start sixth row -->
        <div class="mdjm-post-row-single">
        	<div class="mdjm-post-1column">
                <label for="venue_postcode" class="mdjm-label"><strong><?php _e( 'Post Code:' ); ?></strong></label><br />
                <input type="text" name="venue_postcode" id="venue_postcode" class="regular-text" value="<?php echo esc_attr( get_post_meta( $post->ID, '_venue_postcode', true ) ); ?>" />
            </div>
        </div>
        <!-- End sixth row -->
        <!-- Start seventh row -->
        <div class="mdjm-post-row-single-textarea">
        	<div class="mdjm-post-1column">
                <label for="venue_information" class="mdjm-label"><strong><?php _e( 'General Information:' ); ?></strong></label><br />
                <textarea name="venue_information" id="venue_information" class="widefat" cols="30" rows="3" placeholder="Enter any information you feel relevant for the venue here. Consider adding or selecting venue details where possible via the 'Venue Details' side box"><?php echo esc_attr( get_post_meta( $post->ID, '_venue_information', true ) ); ?></textarea>
            </div>
        </div>
        <!-- End seventh row -->
        <?php
	} // mdjm_venue_post_main_metabox
?>