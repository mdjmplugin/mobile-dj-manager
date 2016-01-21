<?php
	defined( 'ABSPATH' ) or die( "Direct access to this page is disabled!!!" );
	
/**
 * Manage the Transaction posts
 *
 *
 *
 */
		
/**
 * Define the columns to be displayed for transaction posts
 *
 * @params	arr		$columns	Array of column names
 *
 * @return	arr		$columns	Filtered array of column names
 */
function mdjm_transaction_post_columns( $columns ) {
	$columns = array(
			'cb'			   => '<input type="checkbox" />',
			'title' 	 		=> __( 'ID', 'mobile-dj-manager' ),
			'txn_date'		 => __( 'Date', 'mobile-dj-manager' ),
			'direction'		=> __( 'In/Out', 'mobile-dj-manager' ),
			'payee'			=> __( 'To/From', 'mobile-dj-manager' ),
			'txn_status'	   => __( 'Status', 'mobile-dj-manager' ),
			'detail'		   => __( 'Details', 'mobile-dj-manager' ),
			'event'			=> __( 'Event', 'mobile-dj-manager' ),
			'txn_value'		=> __( 'Value', 'mobile-dj-manager' ) );
	
	return $columns;
} // mdjm_transaction_post_columns
add_filter( 'manage_mdjm-transaction_posts_columns' , 'mdjm_transaction_post_columns' );
		
/**
 * Define which columns are sortable for transaction posts
 *
 * @params	arr		$sortable_columns	Array of event post sortable columns
 *
 * @return	arr		$sortable_columns	Filtered Array of event post sortable columns
 */
function mdjm_transaction_post_sortable_columns( $sortable_columns )	{
	$sortable_columns['txn_date'] = 'txn_date';
	$sortable_columns['txn_status'] = 'txn_status';
	$sortable_columns['txn_value'] = 'txn_value';
	
	return $sortable_columns;
} // mdjm_transaction_post_sortable_columns
add_filter( 'manage_edit-mdjm-transaction_sortable_columns', 'mdjm_transaction_post_sortable_columns' );
		
/**
 * Define the data to be displayed in each of the custom columns for the transaction post types
 *
 * @param	str		$column_name	The name of the column to display
 *			int		$post_id		The current post ID
 * 
 *
 */
function mdjm_transaction_posts_custom_column( $column_name, $post_id )	{
	global $post;
					
	switch( $column_name ) {	
		// Details
		case 'detail':
			$trans_types = get_the_terms( $post_id, 'transaction-types' );
			if( is_array( $trans_types ) )	{
				foreach( $trans_types as $key => $trans_type ) {
					$trans_types[$key] = $trans_type->name;
				}
				echo implode( "<br/>", $trans_types );
			}
			break;
			
		// Date
		case 'txn_date':
			echo get_post_time( 'd M Y' );					
			break;
			
		// Direction
		case 'direction':
			echo ( $post->post_status == 'mdjm-income' ? 
				'<span style="color:green">' . __( 'In', 'mobile-dj-manager' ) . '</span>' : 
				'<span style="color:red">&nbsp;&nbsp;&nbsp;&nbsp;' . __( 'Out', 'mobile-dj-manager' ) . '</span>' );					
			break;
			
		// Source
		case 'payee':
			echo ( $post->post_status == 'mdjm-income' ? 
				get_post_meta( $post_id, '_mdjm_payment_from', true ) : 
				get_post_meta( $post_id, '_mdjm_payment_to', true ) );
			
			break;
				
		// Event
		case 'event':
			echo ( wp_get_post_parent_id( $post_id ) ? 
				'<a href="' . admin_url( '/post.php?post=' . wp_get_post_parent_id( $post_id ) . '&action=edit' ) . '">' . 
				MDJM_EVENT_PREFIX . wp_get_post_parent_id( $post_id ) . '</a>' : 
				'N/A' );					
			break;
		// Value
		case 'txn_value':
			echo display_price( get_post_meta( $post_id, '_mdjm_txn_total', true ) );
			break;
			
		// Status
		case 'txn_status':
			echo get_post_meta( $post_id, '_mdjm_txn_status', true );
		break;
	} // switch( $column_name )
} // mdjm_transaction_posts_custom_column
add_action( 'manage_mdjm-transaction_posts_custom_column' , 'mdjm_transaction_posts_custom_column', 10, 2 );
		
/**
 * Add the filter dropdowns to the transaction post list
 *
 * @params
 *
 * @return
 */
function mdjm_transaction_post_filter_list()	{
	if( !isset( $_GET['post_type'] ) || $_GET['post_type'] != MDJM_TRANS_POSTS )
		return;
	
	mdjm_transaction_type_filter_dropdown();
} // mdjm_transaction_post_filter_list
add_action( 'restrict_manage_posts', 'mdjm_transaction_post_filter_list' );
		
/**
 * Display the filter drop down list to enable user to select and filter transactions by type
 * 
 * @params
 *
 * @return
 */
function mdjm_transaction_type_filter_dropdown()	{			
	$transaction_types = get_categories( 
								array(
									'type'			  => MDJM_TRANS_POSTS,
									'taxonomy'		  => 'transaction-types',
									'pad_counts'		=> false,
									'hide_empty'		=> true,
									'orderby'		  => 'name' ) );
									
	foreach( $transaction_types as $transaction_type )	{
		$values[$transaction_type->term_id] = $transaction_type->name;
	}
	?>
	<select name="mdjm_filter_type">
	<option value=""><?php echo __( 'All Transaction Types', 'mobile-dj-manager' ); ?></option>
	<?php
		$current_v = isset( $_GET['mdjm_filter_type'] ) ? $_GET['mdjm_filter_type'] : '';
		
		if( !empty( $values ) )	{
			foreach( $values as $value => $label ) {
				printf(
					'<option value="%s"%s>%s (%s)</option>',
					$value,
					$value == $current_v ? ' selected="selected"' : '',
					$label,
					$label );
			}
		}
	?>
	</select>
	<?php
} // mdjm_transaction_type_filter_dropdown
		
/**
 * Ensure that built-in terms cannot be deleted by removing the 
 * delete, edit and quick edit options from the hover menu
 * 
 * @param	arr		$actions		The array of actions in the hover menu
 * 			obj		$tag			The object array for the term
 * 
 * @return	arr		$actions		The filtered array of actions in the hover menu
 */
function mdjm_transaction_term_row_actions( $actions, $tag )	{
	$protected_terms = array(
						__( 'Merchant Fees', 'mobile-dj-manager' ),
						MDJM_DEPOSIT_LABEL,
						MDJM_BALANCE_LABEL,
						$GLOBALS['mdjm_settings']['payments']['other_amount_label'] );
						
	if ( in_array( $tag->name, $protected_terms ) ) 
		unset( $actions['delete'], $actions['edit'], $actions['inline hide-if-no-js'], $actions['view'] );
		
	return $actions;
} // mdjm_transaction_term_row_actions
add_filter( 'transaction-types_row_actions', 'mdjm_transaction_term_row_actions', 10, 2 );
		
/**
 * Ensure that built-in terms cannot be deleted by removing the 
 * bulk action checkboxes
 * 
 * @param
 *
 * @return
 */
function mdjm_transaction_term_checkboxes()	{
	if ( !isset( $_GET['taxonomy'] ) || $_GET['taxonomy'] != 'transaction-types' )
		return;
	
	// Create an array with all terms that we should protect from deletion	
	$protected_terms = array(
						__( 'Merchant Fees', 'mobile-dj-manager' ),
						MDJM_DEPOSIT_LABEL,
						MDJM_BALANCE_LABEL,
						$GLOBALS['mdjm_settings']['payments']['other_amount_label'] );
	?>
	<script type="text/javascript">
	jQuery(document).ready(function($) {
		<?php
		foreach( $protected_terms as $term_name )	{
			$obj_term = get_term_by( 'name', $term_name, 'transaction-types' );
			if( !empty( $obj_term ) )	{
				?>
				$('input#cb-select-<?php echo $obj_term->term_id; ?>').prop('disabled', true).hide();
				<?php
			}
		}
		?>
	});
	</script>
	<?php
} // mdjm_transaction_term_checkboxes
add_action( 'admin_footer-edit-tags.php', 'mdjm_transaction_term_checkboxes' );

/**
 * Save the meta data for the transaction
 *
 * @called	save_post_mdjm-transaction
 *
 * @param	int		$ID				The current post ID.
 *			obj		$post			The current post object (WP_Post).
 *			bool	$update			Whether this is an existing post being updated or not.
 * 
 * @return	void
 */
function mdjm_save_txn_post( $ID, $post, $update )	{
	global $mdjm_settings;
	
	if( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE )
		return;
	
	if( empty( $update ) )
		return;
		
	// Permission Check
	if( !MDJM()->permissions->employee_can( 'edit_txns' ) )	{
		if( MDJM_DEBUG == true )
			MDJM()->debug->log_it( 'PERMISSION ERROR: User ' . get_current_user_id() . ' is not allowed to edit transactions' );
		 
		return;
	}
	
	// Remove the save post action to avoid loops
	remove_action( 'save_post_mdjm-transaction', 'mdjm_save_txn_post', 10, 3 );
	
	// Fire our pre-save hook
	do_action( 'mdjm_before_txn_save', $ID, $post, $update );
	
	$trans_type = get_term( $_POST['mdjm_transaction_type'], 'transaction-types' );
				
	// Set the post data
	$trans_data['ID'] = $ID;
	$trans_data['post_status'] = ( $_POST['transaction_direction'] == 'Out' ? 'mdjm-expenditure' : 'mdjm-income' );
	$trans_data['post_date'] = date( 'Y-m-d H:i:s', strtotime( $_POST['transaction_date'] ) );
	$trans_data['edit_date'] = true;
		
	$trans_data['post_author'] = get_current_user_id();
	$trans_data['post_type'] = MDJM_TRANS_POSTS;
	$trans_data['post_category'] = array( $_POST['mdjm_transaction_type'] );	
	
	// Set the post meta		
	$trans_meta['_mdjm_txn_status'] = sanitize_text_field( $_POST['transaction_status'] );
	$trans_meta['_mdjm_txn_source'] = sanitize_text_field( $_POST['transaction_src'] );
	$trans_meta['_mdjm_txn_total'] = number_format( $_POST['transaction_amount'], 2 );
	$trans_meta['_mdjm_txn_notes'] = sanitize_text_field( $_POST['transaction_description'] );
	
	if( $_POST['transaction_direction'] == 'In' )
		$trans_meta['_mdjm_payment_from'] = sanitize_text_field( $_POST['transaction_payee'] );
		
	elseif( $_POST['transaction_direction'] == 'Out' )
		$trans_meta['_mdjm_payment_to'] = sanitize_text_field( $_POST['transaction_payee'] );
									
	$trans_meta['_mdjm_txn_currency'] = $mdjm_settings['payments']['currency'];
	
	// Update the post
	if( MDJM_DEBUG == true )
		 MDJM()->debug->log_it( 'Updating the transaction' );
	
	wp_update_post( $trans_data );
	
	// Set the transaction Type
	if( MDJM_DEBUG == true )
		 MDJM()->debug->log_it( 'Setting the transaction type' );													
	
	wp_set_post_terms( $ID, $_POST['mdjm_transaction_type'], 'transaction-types' );
	
	// Add the meta data
	if( MDJM_DEBUG == true )
		 MDJM()->debug->log_it( 'Updating the transaction post meta' );
	
	// Loop through the post meta and add/update/delete the meta keys. 
	foreach( $trans_meta as $meta_key => $new_meta_value )	{
		$current_meta_value = get_post_meta( $ID, $meta_key, true );
		
		// If we have a value and the key did not exist previously, add it.
		if ( !empty( $new_meta_value ) && empty( $current_meta_value ) )
			add_post_meta( $ID, $meta_key, $new_meta_value, true );
		
		// If a value existed, but has changed, update it.
		elseif ( !empty( $new_meta_value ) && $new_meta_value != $current_meta_value )
			update_post_meta( $ID, $meta_key, $new_meta_value );
			
		// If there is no new meta value but an old value exists, delete it.
		elseif ( empty( $new_meta_value ) && !empty( $current_meta_value ) )
			delete_post_meta( $ID, $meta_key, $new_meta_value );
	}
	
	// Fire our post save hook
	do_action( 'mdjm_after_txn_save', $ID, $post, $update );
	
	// Re-add the save post action to avoid loops
	add_action( 'save_post_mdjm-transaction', 'mdjm_save_txn_post', 10, 3 );
	
} // mdjm_save_txn_post
add_action( 'save_post_mdjm-transaction', 'mdjm_save_txn_post', 10, 3 );
?>