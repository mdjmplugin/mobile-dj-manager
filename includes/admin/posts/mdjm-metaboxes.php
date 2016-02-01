<?php
/*
 * Meta box layout, data & save actions
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
		global $mdjm_settings;
		
		$from = get_userdata( $post->post_author );
		$recipient = get_userdata( get_post_meta( $post->ID, '_recipient', true ) );
		
		$attachments = get_children( 
			array(
				'post_parent' 	=> $post->ID,
				'post_type'	  	=> 'attachment',
				'number_posts'	=> -1,
				'post_status'	=> 'any' ) );
		
		?>
        <p><strong>Date Sent</strong>: <?php echo date( MDJM_TIME_FORMAT . ' ' . MDJM_SHORTDATE_FORMAT, get_post_meta( $post->ID, '_date_sent', true ) ); ?></p>
        <p><strong>From</strong>: <a href="<?php echo admin_url( '/user-edit.php?user_id=' . $from->ID ); ?>"><?php echo $from->display_name; ?></a></p>
        <p><strong>Recipient</strong>: <a href="<?php echo admin_url( '/user-edit.php?user_id=' . $recipient->ID ); ?>"><?php echo $recipient->display_name; ?></a></p>
        <p><strong>Status</strong>: <?php echo ucfirst( $post->post_status ) . 
		( $post->post_status == 'opened' ? ' ' . date( MDJM_TIME_FORMAT . ' ' . MDJM_SHORTDATE_FORMAT, strtotime( $post->post_modified ) ) : '' );
		; ?></p>
        <p><strong>Event</strong>: <a href="<?php echo get_edit_post_link( get_post_meta( $post->ID, '_event', true ) ); ?>"><?php echo MDJM_EVENT_PREFIX . stripslashes( get_post_meta( $post->ID, '_event', true ) ); ?></a></p>
        
        <?php
		if( !empty( $attachments ) )	{
			$i = 1;
			?>
            <p><strong>Attachments</strong>:<br />
            	<?php
				foreach( $attachments as $attachment )	{
					echo '<a style="font-size: 11px;" href="' . wp_get_attachment_url( $attachment->ID ) . '">';
					echo basename( get_attached_file( $attachment->ID ) );
					echo '</a>';
					echo ( $i < count( $attachments ) ? '<br />' : '' );
					$i++;	
				}
				?>
            </p>
            <?php	
		}
		?>
        
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
	

/* -- Transaction Post Meta Boxes -- */	
	/*
	 * mdjm_transaction_post_save_metabox
	 * The transaction save metabox
	 *
	 * @params: arr		$post
	 */
	function mdjm_transaction_post_save_metabox( $post )	{
		?>
        <div id="new_transaction_type_div">
            <div class="mdjm-meta-row" style="height: 60px !important">
                <div class="mdjm-left-col">
                        <label class="mdjm-label" for="transaction_type_name">New Transaction Type:</label><br />
                        <input type="text" name="transaction_type_name" id="transaction_type_name" class="mdjm-meta" placeholder="Transaction Type Name" />&nbsp;
                            <a href="#" id="add_transaction_type" class="button button-primary button-small">Add</a>
                </div>
            </div>
        </div>

        <div class="mdjm-meta-row">
        	<div class="mdjm-left-col">
             <?php 
			submit_button( 
						( $post->post_status == 'auto-draft' ? 'Add Transaction' : 'Update Transaction' ),
						'primary',
						'save',
						false,
						array( 'id' => 'save-post' ) );
			?>
            </div>
        </div>
        <?php
	} // mdjm_transaction_post_save_metabox
	
	/*
	 * mdjm_transaction_post_details_metabox
	 * The main transaction details metabox
	 *
	 * @params: arr		$post
	 */
	function mdjm_transaction_post_details_metabox( $post )	{
		wp_nonce_field( basename( __FILE__ ), MDJM_TRANS_POSTS . '_nonce' );
		wp_enqueue_style( 'jquery-ui-css', 'http://ajax.googleapis.com/ajax/libs/jqueryui/1.8.2/themes/smoothness/jquery-ui.css' );
		wp_enqueue_script( 'jquery-ui-datepicker' );
		?>
        <input type="hidden" name="mdjm_update_custom_post" id="mdjm_update_custom_post" value="mdjm_update" />
        <!-- Start first row -->
        <div class="mdjm-post-row-single">
        	<div class="mdjm-post-1column">
                <?php echo __( 'Go to the <a href="' . mdjm_get_admin_page( 'events', 'str' ) . '">Event Management Interface</a> to add a transaction associated to an event.' ); ?>
            </div>
        </div>
        <!-- End first row -->
        <?php
        echo '<script type="text/javascript">' . "\r\n";
		mdjm_jquery_datepicker_script( array( 'trans_date', 'transaction_date', 'today' ) );
		echo '</script>' . "\r\n";
		
		echo '<div class="mdjm-post-row">' . "\r\n";
			echo '<div class="mdjm-post-3column">' . "\r\n";
				echo '<label class="mdjm-label" for="transaction_amount">Amount:</label><br />' . 
					MDJM_CURRENCY . '<input type="text" name="transaction_amount" id="transaction_amount" class="small-text required" placeholder="' . 
					display_price( '10', false ) . '" value="' . get_post_meta( $post->ID, '_mdjm_txn_total', true ) . '" />' . "\r\n";
			echo '</div>' . "\r\n";
		
			echo '<div class="mdjm-post-3column">' . "\r\n";
				echo '<label class="mdjm-label" for="transaction_display_date">Date:</label><br />' . 
				'<input type="text" name="transaction_display_date" id="transaction_display_date" class="trans_date required" value="' . date( MDJM_SHORTDATE_FORMAT, strtotime( $post->post_date ) ) . '" />' .
				'<input type="hidden" name="transaction_date" id="transaction_date" value="' . date( 'Y-m-d', strtotime( $post->post_date ) ) . '" />' . "\r\n";
			echo '</div>' . "\r\n";
		
			echo '<div class="mdjm-post-last-3column">' . "\r\n";
				echo '<label class="mdjm-label" for="transaction_direction">Direction:</label><br />' . 
				'<select name="transaction_direction" id="transaction_direction" onChange="displayPaid();">' . "\r\n" . 
				'<option value="In"' . selected( 'mdjm-income', $post->post_status, false ) . '>Incoming</option>' . "\r\n" . 
				'<option value="Out"' . selected( 'mdjm-expenditure', $post->post_status, false ) . '>Outgoing</option>' . "\r\n" . 
				'</select>' . "\r\n";
			echo '</div>' . "\r\n";
		echo '</div>' . "\r\n";	
		?>
        <style>
		#paid_from_field	{
			display: <?php echo ( $post->post_status != 'mdjm-expenditure' ? 'block' : 'none' ); ?>;	
		}
		#paid_to_field	{
			display: <?php echo ( $post->post_status == 'mdjm-expenditure' ? 'block' : 'none' ); ?>;	
		}
		</style>
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
					echo '<label class="mdjm-label" for="transaction_from">Paid From:</label><br />';
				echo '</div>' . "\r\n";
				
				echo '<div id="paid_to_field">' . "\r\n";
					echo '<label class="mdjm-label" for="transaction_to">Paid To:</label><br />';
				echo '</div>' . "\r\n";
				echo '<input type="text" name="transaction_payee" id="transaction_payee" class="regular_text" value="' 
					. ( $post->post_status == 'mdjm-income' ? 
					get_post_meta( $post->ID, '_mdjm_payment_from', true ) :
					get_post_meta( $post->ID, '_mdjm_payment_to', true ) )
					. '" />';
			echo '</div>' . "\r\n";
			
			/* -- The current transaction type -- */
			$existing_transaction_type = wp_get_object_terms( $post->ID, 'transaction-types' );
			echo '<div class="mdjm-post-3column">' . "\r\n";
				echo '<label class="mdjm-label" for="transaction_for">Type:</label><br />';
					echo '<div id="transaction_types">' . "\r\n";
						/* -- Display the drop down selection -- */    
						wp_dropdown_categories( array( 'taxonomy' 			=> 'transaction-types',
													   'hide_empty' 		=> 0,
													   'name' 				=> 'mdjm_transaction_type',
													   'id' 				=> 'mdjm_transaction_type',
													   'selected' 			=> ( isset( $existing_transaction_type[0]->term_id ) ? $existing_transaction_type[0]->term_id : '' ),
													   'orderby' 			=> 'name',
													   'hierarchical' 		=> 0,
													   'show_option_none' 	=> __( 'Select Transaction Type' ),
													   'class'				=> 'required',
														) );
							echo '<a id="new_transaction_type" class="side-meta" href="#">Add New</a>' . "\r\n";
					echo '</div>' . "\r\n";
			echo '</div>' . "\r\n";
			echo '<script type="text/javascript">' . "\r\n" . 
			'jQuery("#mdjm_transaction_type option:first").val(null);' . "\r\n" . 
			'</script>' . "\r\n";
			$sources = get_transaction_source();
			echo '<div class="mdjm-post-last-3column">' . "\r\n";
				echo '<label class="mdjm-label" for="transaction_src">Source:</label><br />' . "\r\n" . 
					'<select name="transaction_src" id="transaction_src" class="required">' . "\r\n" . 
					'<option value="">--- Select ---</option>' . "\r\n";
					foreach( $sources as $source )	{
						echo '<option value="' . $source . '"' . selected( $source, get_post_meta( $post->ID, '_mdjm_txn_source', true ) ) . '>' . $source . '</option>' . "\r\n";	
					}
					echo '</select>' . "\r\n";
			echo '</div>' . "\r\n";
			
		echo '</div>' . "\r\n";
		?>
        <script type="text/javascript">
		jQuery("#mdjm_event_type option:first").val(null);
		</script>
        <div class="mdjm-post-row-single">
        	<div class="mdjm-post-1column">
                <label for="transaction_status" class="mdjm-label">Status:</label><br />
				<select name="transaction_status" id="transaction_status" class="required">
                <option value="">--- Select ---</option>
                <option value="Completed"<?php selected( 'Completed', get_post_meta( $post->ID, '_mdjm_txn_status', true ) ); ?>>Completed</option>
                <option value="Pending"<?php selected( 'Pending', get_post_meta( $post->ID, '_mdjm_txn_status', true ) ); ?>>Pending</option>
                <option value="Refunded"<?php selected( 'Refunded', get_post_meta( $post->ID, '_mdjm_txn_status', true ) ); ?>>Refunded</option>
                <option value="Cancelled"<?php selected( 'Cancelled', get_post_meta( $post->ID, '_mdjm_txn_status', true ) ); ?>>Cancelled</option>
                </select>
            </div>
        </div>
        <div class="mdjm-post-row-single-textarea">
        	<div class="mdjm-post-1column">
                <label for="transaction_description" class="mdjm-label">Description:</label><br />
				<textarea name="transaction_description" id="transaction_description" class="widefat" cols="30" rows="3" placeholder="Enter any optional information here..."><?php echo esc_attr( get_post_meta( $post->ID, '_mdjm_txn_notes', true ) ); ?></textarea>
            </div>
        </div>
        <?php
	} // mdjm_transaction_post_details_metabox

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